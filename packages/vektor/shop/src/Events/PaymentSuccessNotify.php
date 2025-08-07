<?php

namespace Vektor\Shop\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessNotify
{
    use Dispatchable;
    use SerializesModels;

    public $request = [];
    public $cart = [];
    public $authed_user;
    public $notification_email;
    public $order_number;

    /**
     * Create a new event instance.
     */
    public function __construct(PaymentSuccess $event)
    {
        $this->request = $event->request;
        $this->cart = $event->cart;
        $this->authed_user = $event->authed_user;
        if (!empty($event->notification_email)) {
            $this->notification_email = $event->notification_email;
        }
        $this->order_number = null;
        if (!empty($event->order_number)) {
            $this->order_number = $event->order_number;
        }
    }
}
