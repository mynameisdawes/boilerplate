<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteReminder extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $quote;

    /**
     * Create a new message instance.
     *
     * @param mixed $quote
     */
    public function __construct($quote)
    {
        $this->quote = $quote;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = config('app.company.name').' - Quote Awaiting Approval Reminder';
        if (isset($this->quote['quote_number'])) {
            $subject = $subject." [{$this->quote['quote_number']}]";
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.quotes.reminder',
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
