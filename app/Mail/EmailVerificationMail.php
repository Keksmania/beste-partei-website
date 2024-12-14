<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\LaravelDriver\MailerSendTrait;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

    public $name;
    public $verificationUrl;

    public function __construct($name, $verificationUrl)
    {
        $this->name = $name;
        $this->verificationUrl = $verificationUrl;
    }

    public function build()
    {
        // Ensure proper recipient structure
        $to = Arr::get($this->to, '0.address');

        return $this
            ->view('emails.verify-email')     // HTML template
            ->text('emails.verify-email-text') // Plain text version
            ->mailersend(
                template_id: null, // Optional: Set if you use predefined MailerSend templates
                tags: ['email-verification'],
                personalization: [
                    new Personalization($to, [
                        'name' => $this->name,
                        'verification_url' => $this->verificationUrl,
                    ])
                ],
                precedenceBulkHeader: false // False since this is not a bulk email
            );
    }
}
