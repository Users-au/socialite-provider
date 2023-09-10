<?php

namespace SocialiteProviders\Slj;

use SocialiteProviders\Manager\SocialiteWasCalled;

class SljExtendSocialite
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
