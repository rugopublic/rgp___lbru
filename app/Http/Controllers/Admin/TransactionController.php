<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Http\Request;

final class TransactionController extends Controller
{

    /**
     * Initializes the transaction controller instance and wires its required dependencies.
     *
     * @param TransactionRepositoryInterface $transactions
     * @return void
     */
    public function __construct(private readonly TransactionRepositoryInterface $transactions) {}

    /**
     * Builds the listing response and loads selector/filter data.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        $transactions = $this->transactions->paginateForAdmin($request->only(['search','status']),25);
        return view('admin.transactions.index', compact('transactions'));
    }

    /**
     * Builds the detail response for the requested resource.
     *
     * @param string $transaction
     * @return mixed
     */
    public function show(string $transaction)
    {
        $transaction = $this->transactions->find($transaction);
        return view('admin.transactions.show', compact('transaction'));
    }
}
