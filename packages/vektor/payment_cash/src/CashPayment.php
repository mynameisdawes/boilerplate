<?php

namespace Vektor\Cash;

use Illuminate\Http\Request;

class CashPayment
{
    public function handle(Request $request)
    {
        return [
            'success' => true,
            'success_message' => $request->input('success_message', 'Your transaction was processed successfully'),
        ];
    }
}
