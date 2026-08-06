<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $messageContent;

    public function __construct($data)
    {
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->messageContent = $data['message'];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Contact Message from ' . $this->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message',
        );
    }
}