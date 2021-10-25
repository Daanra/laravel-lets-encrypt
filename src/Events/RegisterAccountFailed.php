<?php

namespace Daanra\LaravelLetsEncrypt\Events;

use Daanra\LaravelLetsEncrypt\Interfaces\NotifiableEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RegisterAccountFailed implements NotifiableEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }
}
