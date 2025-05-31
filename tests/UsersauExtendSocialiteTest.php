<?php

namespace SocialiteProviders\Usersau\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Usersau\Provider;
use SocialiteProviders\Usersau\UsersauExtendSocialite;

class UsersauExtendSocialiteTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testHandle()
    {
        $socialiteWasCalled = m::mock(SocialiteWasCalled::class);
        $socialiteWasCalled->shouldReceive('extendSocialite')
            ->once()
            ->with('usersau', Provider::class);

        $extendSocialite = new UsersauExtendSocialite();
        $extendSocialite->handle($socialiteWasCalled);
        
        // Verify that the expectations were met
        $this->assertTrue(true); // This assertion shows the test completed successfully
    }
} 