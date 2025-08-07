<?php

namespace Vektor\Shop\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuoteSuccessNotify
{
    use Dispatchable;
    use SerializesModels;

    public $request = [];
    public $cart = [];
    public $authed_user;
    public $notification_email;
    public $order_number;
    public $quote_number;
    public $quote_id;

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
        $this->quote_number = null;
        if (!empty($event->quote_number)) {
            $this->quote_number = $event->quote_number;
        }
        $this->quote_id = null;
        if (!empty($event->quote_id)) {
            $this->quote_id = $event->quote_id;
        }
    }
}
