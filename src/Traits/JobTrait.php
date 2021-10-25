<?php

namespace Daanra\LaravelLetsEncrypt\Traits;

trait JobTrait
{

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $retryAfter;

    /**
     * The list of seconds to wait before retrying the job.
     *
     * @var int[]
     */
    public $retryList;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     * In the $retryList property, you must pass a list of seconds to wait before retrying the job.
     * @return int
     */
    public function retryAfter()
    {
        return (!empty($this->retryList)) ? $this->retryList[$this->attempts() - 1] : 0;
    }
}
