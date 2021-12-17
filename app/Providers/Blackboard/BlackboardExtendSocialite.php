<?php

namespace App\Providers\Blackboard;

use SocialiteProviders\Manager\SocialiteWasCalled;

class BlackboardExtendSocialite
{
    /**
     * Register the provider.
     *
     * @param SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('blackboard', Provider::class);
    }
}
