<?php

namespace Vektor\Shop;

use Illuminate\Http\Request;

class QuoteCheckout
{
    public function handle(Request $request)
    {
        return [
            'success' => true,
            'success_message' => 'Your quote was processed successfully.',
        ];
    }
}
