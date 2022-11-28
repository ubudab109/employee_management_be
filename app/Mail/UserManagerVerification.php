<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UserManagerVerification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $user, $type;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $type)
    {
        $this->user = $user;
        $this->type = $type;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        try {
            if ($this->type == USER_MANAGER_TYPE) {
                return $this->subject('Email Verification '.allCompanySetting('company_name'))
                ->view('email.verifyWeb');
            } 

            if ($this->type == USER_EMPLOYEE_TYPE) {
                return $this->subject('Email Verification '.allCompanySetting('company_name'))
                ->view('email.verifyEmployee');
            }
        } catch (\Exception $err) {
            Log::info('Error',[
                $err->getMessage()
            ]);
        }
    }
}
