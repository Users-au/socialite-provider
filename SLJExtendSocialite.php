<?php

namespace SocialiteProviders\SLJ;

use SocialiteProviders\Manager\SocialiteWasCalled;

class SLJExtendSocialite
{
    /**
     * Register the provider.
     *
     * @param \SocialiteProviders\Manager\SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite('slj', Provider::class);
    }
}
