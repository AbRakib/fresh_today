<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(): Response
    {
        $transactions = Transaction::query()
            ->with('account:id,name,account_number')
            ->where('deleted', 0)
            ->latest('date')
            ->latest('id')
            ->get()
            ->map(fn (Transaction $transaction) => [
                'id' => $transaction->id,
                'transaction_no' => $transaction->transaction_no,
                'date' => $transaction->date?->format('Y-m-d'),
                'account_name' => $transaction->account?->name,
                'account_number' => $transaction->account?->account_number,
                'payment_type' => $transaction->payment_type,
                'transaction_type' => $transaction->transaction_type,
                'reference_type' => $transaction->reference_type,
                'reference_description' => $transaction->reference_description,
                'description' => $transaction->description,
                'total_amount' => $transaction->total_amount,
                'notes' => $transaction->notes,
                'reviewed' => $transaction->reviewed,
                'created_at' => $transaction->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('backend/transactions/Index', [
            'transactions' => $transactions,
        ]);
    }
}
