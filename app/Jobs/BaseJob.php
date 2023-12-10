<?php

namespace App\Jobs;

use Illuminate\Support\Facades\Log;

class BaseJob
{
    protected $startTime;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->startTime = now();
    }

    /**
     * Handle a job failure.
     *
     * @param  $exception
     * @return void
     */
    public function failed($exception)
    {
        throw $exception;
    }
}
