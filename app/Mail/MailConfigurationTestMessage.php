<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MailConfigurationTestMessage extends Mailable
{
    use Queueable;

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Prueba de configuración de correo');
    }

    public function content(): Content
    {
        return new Content(text: 'emails.mail-configuration-test');
    }
}
