<?php

namespace Vektor\Shop\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendDiscountCode extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $discount_code;

    /**
     * Create a new message instance.
     *
     * @param mixed $discount_code
     */
    public function __construct($discount_code)
    {
        $this->discount_code = $discount_code;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Send Discount Code',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'shop::emails.send_discount_code',
            with: [
                'discount_code' => $this->discount_code,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
