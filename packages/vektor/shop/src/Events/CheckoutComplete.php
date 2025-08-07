<?php

namespace Vektor\Shop\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Vektor\Shop\Models\DiscountCode;

class CheckoutComplete
{
    use Dispatchable;
    use SerializesModels;

    public $request;

    /**
     * Create a new event instance.
     *
     * @param mixed $request
     */
    public function __construct($request)
    {
        $this->request = $request;

        $discount_code = DiscountCode::where('code', data_get($this->request, 'discount_code'))->first();
        if ($discount_code) {
            $discount_code->is_used = 1;
            $discount_code->save();
        }
    }
}
