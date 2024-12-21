<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $verificationUrl;

    public function __construct($name, $verificationUrl)
    {
        $this->name = $name;
        $this->verificationUrl = $verificationUrl;
    }

    public function build()
    {
        return $this
            ->view('emails.verify-email')     // HTML template
            ->text('emails.verify-email-text') // Plain text version
            ->with([
                'name' => $this->name,
                'verification_url' => $this->verificationUrl,
            ]);
    }
}
