<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Exception;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2; // Maximum number of attempts
    public $backoff = 10; // Retry delay in seconds

    protected $email;
    protected $mailable;

    /**
     * Create a new job instance.
     *
     * @param string $email
     * @param Mailable $mailable
     */
    public function __construct($email, $mailable)
    {
        $this->email = $email;
        $this->mailable = $mailable;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Mail::to($this->email)->send($this->mailable);
        } catch (Exception $e) {
            // Log the error or handle it as needed
            throw $e; // Rethrow the exception to trigger the retry
        }
    }
}