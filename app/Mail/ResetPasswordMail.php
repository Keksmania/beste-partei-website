<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $resetUrl;

    /**
     * Create a new message instance.
     *
     * @param string $name
     * @param string $resetUrl
     */
    public function __construct($name, $resetUrl)
    {
        $this->name = $name;
        $this->resetUrl = $resetUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->view('emails.reset-password') // HTML version of the email
            ->text('emails.reset-password-text') // Plain text version
            ->with([
                'name' => $this->name,
                'reset_url' => $this->resetUrl,
            ]);
    }
}
