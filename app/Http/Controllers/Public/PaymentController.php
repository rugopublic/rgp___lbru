<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\CheckoutRequest;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\PaymentIntentRepositoryInterface;
use App\Services\Public\PaymentFulfillmentService;
use App\Services\Public\PaymentService;
use App\Services\Public\YooKassaClient;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;
use Symfony\Component\HttpFoundation\IpUtils;

final class PaymentController extends Controller
{

    /**
     * Initializes the payment controller instance and wires its required dependencies.
     *
     * @param CourseRepositoryInterface $courses
     * @param ProductRepositoryInterface $products
     * @param PaymentIntentRepositoryInterface $intents
     * @param PaymentService $payments
     * @param PaymentFulfillmentService $fulfillment
     * @param YooKassaClient $yookassa
     * @return void
     */
    public function __construct(
        private readonly CourseRepositoryInterface $courses,
        private readonly ProductRepositoryInterface $products,
        private readonly PaymentIntentRepositoryInterface $intents,
        private readonly PaymentService $payments,
        private readonly PaymentFulfillmentService $fulfillment,
        private readonly YooKassaClient $yookassa,
    ) {}

    /**
     * Handles the checkout course HTTP action for the payment controller resource.
     *
     * @param string $course
     * @return View
     */
    public function checkoutCourse(string $course): View
    {
        $item=$this->courses->findPublished($course); abort_unless($item,404);
        return view('frontend.checkout.index',['item'=>$item,'type'=>'course','price'=>$this->checkoutPrice($item)]);
    }

    /**
     * Handles the checkout product HTTP action for the payment controller resource.
     *
     * @param string $product
     * @return View
     */
    public function checkoutProduct(string $product): View
    {
        $item=$this->products->findSellable($product); abort_unless($item,404);
        return view('frontend.checkout.index',['item'=>$item,'type'=>'product','price'=>$this->checkoutPrice($item)]);
    }

    /**
     * Uses the locked amount of a still-pending payment when resuming checkout.
     *
     * @param Model $item
     * @return string|float|int
     */
    private function checkoutPrice(Model $item): string|float|int
    {
        if (!auth()->check()) {
            return $item->price;
        }

        $intent = $this->intents->findReusableForUserItem(
            (int) auth()->id(),
            $item::class,
            (int) $item->id,
        );

        return $intent?->amount ?? $item->price;
    }

    /**
     * Handles the pay HTTP action for the payment controller resource.
     *
     * @param CheckoutRequest $request
     * @param string $type
     * @param string $id
     * @return RedirectResponse
     */
    public function pay(CheckoutRequest $request,string $type,string $id): RedirectResponse
    {
        $item=match($type){'course'=>$this->courses->findPublished($id),'product'=>$this->products->findSellable($id),default=>null};
        abort_unless($item,404);
        try { $intent=$this->payments->createFor($item,(int)$request->user()->id); }
        catch(Throwable $e){ report($e); return back()->withErrors(['payment'=>'Не удалось создать платёж. Проверьте настройки оплаты и попробуйте ещё раз.']); }
        return redirect()->away((string)$intent->confirmation_url);
    }

    /**
     * Handles the webhook HTTP action for the payment controller resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function webhook(Request $request): JsonResponse
    {
        if ((bool) config('services.yookassa.webhook_ip_validation', true) && !IpUtils::checkIp((string) $request->ip(), (array) config('services.yookassa.webhook_ips', []))) {
            return response()->json(['ok' => false], 403);
        }
        $paymentId=(string)$request->input('object.id');
        if($paymentId==='') return response()->json(['ok'=>true]);
        try { $intent=$this->intents->findByProviderPaymentId($paymentId); if(!$intent) return response()->json(['ok'=>true]); $payment=$this->yookassa->getPayment($paymentId); $this->fulfillment->syncPayment($paymentId,$payment); return response()->json(['ok'=>true]); }
        catch(Throwable $e){ report($e); return response()->json(['ok'=>false],502); }
    }

    /**
     * Handles the result HTTP action for the payment controller resource.
     *
     * @param int $intent
     * @return View
     */
    public function result(int $intent): View
    {
        $paymentIntent=$this->intents->findForUser($intent,(int)auth()->id());
        if($paymentIntent->provider_payment_id && $paymentIntent->status!=='succeeded'){
            try{$payment=$this->yookassa->getPayment((string)$paymentIntent->provider_payment_id);$this->fulfillment->syncPayment((string)$paymentIntent->provider_payment_id,$payment);$paymentIntent=$this->intents->findForUser($intent,(int)auth()->id());}catch(Throwable $e){report($e);}
        }
        return view('frontend.checkout.success',['intent'=>$paymentIntent]);
    }
}
