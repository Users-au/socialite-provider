<?php

namespace SocialiteProviders\Usersau;

use SocialiteProviders\Manager\SocialiteWasCalled;

class UsersauExtendSocialite
{
    /**
     * Register the provider.
     *
     * @param \SocialiteProviders\Manager\SocialiteWasCalled $socialiteWasCalled
     * 
     * @return void
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled): void
    {
        $socialiteWasCalled->extendSocialite('usersau', Provider::class);
    }
}
