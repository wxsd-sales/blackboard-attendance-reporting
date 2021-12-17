<?php

namespace App\Subscribers;

use App\Events\SetupDone as SetupDoneEvent;
use App\Notifications\SetupDone as SetupDoneNotification;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Support\Facades\Log;

class SetupDone
{

    public function handleSetupDone(SetupDoneEvent $event)
    {
        Log::info($event);
        $event->user->notify(new SetupDoneNotification((string)$event));
    }

    public function subscribe($events)
    {
        return [
            SetupDoneEvent::class => 'handleSetupDone',
        ];
    }
}
