<?php

namespace App\Actions;

use App\Models\SalePayment;

class CreateSalePayment
{
    public function handle(
        string $transactionId,
        int $processedBy,
        string $branchId,
        string $paymentMethod,
        string $paymentReference,
        float $amountPaid,        
    ){
        SalePayment::create([
            'sale_transaction_id'  => $transactionId,
            'processed_by'         => $processedBy,
            'branch_id'            => $branchId,
            'payment_method'       => $paymentMethod,
            'payment_reference'    => $paymentReference,
            'amount_paid'          => $amountPaid,
            'date_paid'            => now(),
        ]);
    }
}
