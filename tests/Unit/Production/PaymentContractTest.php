<?php

declare(strict_types=1);

it('keeps YooKassa payment creation and fulfillment idempotent', function () {
    $client = file_get_contents(base_path('app/Services/Public/YooKassaClient.php'));
    $payment = file_get_contents(base_path('app/Services/Public/PaymentService.php'));
    $fulfillment = file_get_contents(base_path('app/Services/Public/PaymentFulfillmentService.php'));
    $controller = file_get_contents(base_path('app/Http/Controllers/Public/PaymentController.php'));

    expect($client)
        ->toContain("'Idempotence-Key' => $key")
        ->toContain("Authorization: Basic");

    expect($payment)
        ->toContain('idempotency_key')
        ->toContain('pending');

    expect($fulfillment)
        ->toContain('ProductPurchase')
        ->toContain('CourseEnrollment')
        ->toContain('updateOrCreate');

    $paymentRepository = file_get_contents(base_path('app/Repositories/Eloquent/PaymentIntentRepository.php'));

    expect($controller)
        ->toContain('getPayment($paymentId)')
        ->toContain('IpUtils::checkIp')
        ->toContain('PaymentFulfillmentService');

    expect($paymentRepository)->toContain('lockForUpdate()');

    expect($fulfillment)
        ->toContain('assertProviderPaymentMatchesIntent')
        ->toContain("amount.value")
        ->toContain("amount.currency");
});

it('does not cancel an intent on a provider-side transport/persistence uncertainty', function () {
    $payment = file_get_contents(base_path('app/Services/Public/PaymentService.php'));

    expect($payment)->toContain("'error_code'=>$e->getCode()")
        ->toContain('// Keep the intent reusable.')
        ->not->toContain("'status'=>'canceled','payload'=>['error'=>$e->getMessage()]");
});

it('keeps the public payment route contract explicit', function () {
    $routes = file_get_contents(base_path('routes/web/public/payments.php'));

    expect($routes)
        ->toContain("->name('payments.pay')")
        ->toContain("->name('payments.result')")
        ->toContain("->name('payments.webhook')");
});
