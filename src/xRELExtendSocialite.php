<?php
namespace SocialiteProviders\xREL;

use SocialiteProviders\Manager\SocialiteWasCalled;

class xRELExtendSocialite
{
    /**
     * Execute the provider.
     *
     * @param \SocialiteProviders\Manager\SocialiteWasCalled $socialiteWasCalled
     */
    public function handle(SocialiteWasCalled $socialiteWasCalled)
    {
        $socialiteWasCalled->extendSocialite(
            'xrel', __NAMESPACE__.'\Provider', __NAMESPACE__.'\Server'
        );
    }
}
