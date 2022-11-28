<?php

namespace App\Jobs;

use App\Mail\UserManagerVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $dataEmail, $type;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($dataEmail, $type)
    {
        $this->dataEmail = $dataEmail;
        $this->type = $type;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mail = new UserManagerVerification($this->dataEmail, $this->type);
        Mail::to($this->dataEmail['email'])->send($mail);
    }
}
