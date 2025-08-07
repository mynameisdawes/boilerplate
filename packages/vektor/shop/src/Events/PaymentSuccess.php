<?php

namespace Vektor\Shop\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Vektor\Shop\Utilities;

class PaymentSuccess
{
    use Dispatchable;
    use SerializesModels;

    public $request = [];
    public $cart = [];
    public $authed_user;

    /**
     * Create a new event instance.
     *
     * @param mixed $request
     */
    public function __construct($request)
    {
        $options['customisations'] = ['exclude' => ['preview']];

        $this->request = collect($request->all());

        if (data_get($this->request, 'from_model') && data_get($this->request, 'from_id')) {
            $instance = data_get($this->request, 'from_id');

            \Cart::instance($instance);
            $options['instance'] = $instance;
        }

        $this->cart = Utilities::cart($options);
        $this->authed_user = \Auth::user();

        \Cart::destroy();
    }
}
