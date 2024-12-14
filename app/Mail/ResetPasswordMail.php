<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use MailerSend\Helpers\Builder\Personalization;
use MailerSend\LaravelDriver\MailerSendTrait;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels, MailerSendTrait;

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
        // Assuming that $this->to is an array of recipients. If not, you'll need to set the recipient explicitly.
        $to = Arr::get($this->to, '0.address');
        
        // Build the email
        return $this
            ->view('emails.reset-password') // HTML version of the email
            ->with([
                'name' => $this->name,
                'reset_url' => $this->resetUrl, // Pass reset_url here
            ])
            ->text('emails.reset-password-text') // Plain text version
            ->with([
                'name' => $this->name,
                'reset_url' => $this->resetUrl, // Pass reset_url here
            ])
            ->mailersend(
                template_id: null, // Optional: Use predefined MailerSend templates if available
                tags: ['password-reset'],
                personalization: [
                    new Personalization($to, [
                        'name' => $this->name,
                        'reset_url' => $this->resetUrl,
                    ])
                ],
                precedenceBulkHeader: false // Not a bulk email
            );
    }
}
