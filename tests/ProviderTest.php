<?php

namespace SocialiteProviders\Usersau\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use SocialiteProviders\Manager\Contracts\ConfigInterface;
use SocialiteProviders\Manager\OAuth2\User;
use SocialiteProviders\Usersau\Provider;

class TestableProvider extends Provider
{
    public function getAuthUrl($state)
    {
        return parent::getAuthUrl($state);
    }
    
    public function getTokenUrl()
    {
        return parent::getTokenUrl();
    }
    
    public function getUserByToken($token)
    {
        return parent::getUserByToken($token);
    }
    
    public function mapUserToObject(array $user)
    {
        return parent::mapUserToObject($user);
    }
    
    public function getTokenFields($code)
    {
        return parent::getTokenFields($code);
    }
    
    public function getUsersauUrl($type)
    {
        return parent::getUsersauUrl($type);
    }
    
    public function getUserData($user, $key)
    {
        return parent::getUserData($user, $key);
    }
    
    public function setHttpClient($client)
    {
        $this->httpClient = $client;
    }
}

class MockConfig implements ConfigInterface
{
    private $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function get()
    {
        return $this->config;
    }
}

class ProviderTest extends TestCase
{
    protected $provider;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->provider = new TestableProvider(
            m::mock('Illuminate\Http\Request'),
            'client_id',
            'client_secret',
            'redirect_uri'
        );
        
        $config = new MockConfig([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect' => 'redirect_uri',
            'host' => 'https://your-company.users.au',
            'authorize_uri' => 'oauth/authorize',
            'token_uri' => 'oauth/token',
            'userinfo_uri' => 'api/user',
        ]);
        
        $this->provider->setConfig($config);
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testIdentifier()
    {
        $this->assertEquals('USERSAU', Provider::IDENTIFIER);
    }

    public function testAdditionalConfigKeys()
    {
        $expectedKeys = [
            'host',
            'authorize_uri',
            'token_uri',
            'userinfo_uri',
            'userinfo_key',
            'user_id',
            'user_nickname',
            'user_name',
            'user_email',
            'user_avatar',
            'guzzle',
        ];
        
        $this->assertEquals($expectedKeys, Provider::additionalConfigKeys());
    }

    public function testGetAuthUrl()
    {
        $state = 'random_state_string';
        $authUrl = $this->provider->getAuthUrl($state);
        
        $this->assertStringContainsString('https://your-company.users.au/oauth/authorize', $authUrl);
        $this->assertStringContainsString('client_id=client_id', $authUrl);
        $this->assertStringContainsString('redirect_uri=redirect_uri', $authUrl);
        $this->assertStringContainsString('state=' . $state, $authUrl);
        $this->assertStringContainsString('response_type=code', $authUrl);
    }

    public function testGetTokenUrl()
    {
        $tokenUrl = $this->provider->getTokenUrl();
        $this->assertEquals('https://your-company.users.au/oauth/token', $tokenUrl);
    }

    public function testGetUserByToken()
    {
        $userData = [
            'id' => '123',
            'nickname' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'avatar' => 'https://example.com/avatar.jpg'
        ];

        $response = new Response(200, [], json_encode($userData));
        
        $client = m::mock(Client::class);
        $client->shouldReceive('get')
            ->once()
            ->with('https://your-company.users.au/api/user', [
                'headers' => [
                    'Authorization' => 'Bearer access_token'
                ]
            ])
            ->andReturn($response);

        $this->provider->setHttpClient($client);
        $result = $this->provider->getUserByToken('access_token');

        $this->assertEquals($userData, $result);
    }

    public function testMapUserToObject()
    {
        $userData = [
            'id' => '456',
            'nickname' => 'anotheruser',
            'name' => 'Another User',
            'email' => 'another@example.com',
            'avatar' => 'https://example.com/another-avatar.jpg'
        ];

        $user = $this->provider->mapUserToObject($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('456', $user->getId());
        $this->assertEquals('anotheruser', $user->getNickname());
        $this->assertEquals('Another User', $user->getName());
        $this->assertEquals('another@example.com', $user->getEmail());
        $this->assertEquals('https://example.com/another-avatar.jpg', $user->getAvatar());
        $this->assertEquals($userData, $user->getRaw());
    }

    public function testMapUserToObjectWithUserinfoKey()
    {
        $config = new MockConfig([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect' => 'redirect_uri',
            'host' => 'https://your-company.users.au',
            'userinfo_key' => 'data'
        ]);
        
        $this->provider->setConfig($config);

        $responseData = [
            'status' => 'success',
            'data' => [
                'id' => '789',
                'nickname' => 'keyuser',
                'name' => 'Key User',
                'email' => 'key@example.com',
                'avatar' => 'https://example.com/key-avatar.jpg'
            ]
        ];

        $user = $this->provider->mapUserToObject($responseData);

        $this->assertEquals('789', $user->getId());
        $this->assertEquals('keyuser', $user->getNickname());
        $this->assertEquals('Key User', $user->getName());
        $this->assertEquals('key@example.com', $user->getEmail());
        $this->assertEquals('https://example.com/key-avatar.jpg', $user->getAvatar());
    }

    public function testMapUserToObjectWithCustomFieldMapping()
    {
        $config = new MockConfig([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect' => 'redirect_uri',
            'host' => 'https://your-company.users.au',
            'user_id' => 'user_id',
            'user_nickname' => 'display_name',
            'user_name' => 'full_name',
            'user_email' => 'email_address',
            'user_avatar' => 'profile_picture'
        ]);
        
        $this->provider->setConfig($config);

        $userData = [
            'user_id' => '999',
            'display_name' => 'customuser',
            'full_name' => 'Custom User',
            'email_address' => 'custom@example.com',
            'profile_picture' => 'https://example.com/custom-avatar.jpg'
        ];

        $user = $this->provider->mapUserToObject($userData);

        $this->assertEquals('999', $user->getId());
        $this->assertEquals('customuser', $user->getNickname());
        $this->assertEquals('Custom User', $user->getName());
        $this->assertEquals('custom@example.com', $user->getEmail());
        $this->assertEquals('https://example.com/custom-avatar.jpg', $user->getAvatar());
    }

    public function testGetTokenFields()
    {
        $code = 'authorization_code';
        $tokenFields = $this->provider->getTokenFields($code);

        $this->assertArrayHasKey('grant_type', $tokenFields);
        $this->assertEquals('authorization_code', $tokenFields['grant_type']);
        $this->assertArrayHasKey('client_id', $tokenFields);
        $this->assertArrayHasKey('client_secret', $tokenFields);
        $this->assertArrayHasKey('code', $tokenFields);
        $this->assertArrayHasKey('redirect_uri', $tokenFields);
    }

    public function testGetUsersauUrlWithDefaults()
    {
        $this->assertEquals('https://your-company.users.au/oauth/authorize', $this->provider->getUsersauUrl('authorize_uri'));
        $this->assertEquals('https://your-company.users.au/oauth/token', $this->provider->getUsersauUrl('token_uri'));
        $this->assertEquals('https://your-company.users.au/api/user', $this->provider->getUsersauUrl('userinfo_uri'));
    }

    public function testGetUsersauUrlWithCustomConfig()
    {
        $config = new MockConfig([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect' => 'redirect_uri',
            'host' => 'https://custom.users.au',
            'authorize_uri' => 'custom/auth',
            'token_uri' => 'custom/token',
            'userinfo_uri' => 'custom/user'
        ]);
        
        $this->provider->setConfig($config);

        $this->assertEquals('https://custom.users.au/custom/auth', $this->provider->getUsersauUrl('authorize_uri'));
        $this->assertEquals('https://custom.users.au/custom/token', $this->provider->getUsersauUrl('token_uri'));
        $this->assertEquals('https://custom.users.au/custom/user', $this->provider->getUsersauUrl('userinfo_uri'));
    }

    public function testGetUsersauUrlWithTrailingSlashes()
    {
        $config = new MockConfig([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect' => 'redirect_uri',
            'host' => 'https://your-company.users.au/',
            'authorize_uri' => '/oauth/authorize/',
            'token_uri' => '/oauth/token/',
            'userinfo_uri' => '/api/user/'
        ]);
        
        $this->provider->setConfig($config);

        $this->assertEquals('https://your-company.users.au/oauth/authorize/', $this->provider->getUsersauUrl('authorize_uri'));
        $this->assertEquals('https://your-company.users.au/oauth/token/', $this->provider->getUsersauUrl('token_uri'));
        $this->assertEquals('https://your-company.users.au/api/user/', $this->provider->getUsersauUrl('userinfo_uri'));
    }

    public function testGetUserData()
    {
        $userData = [
            'id' => '123',
            'nickname' => 'testuser',
            'name' => 'Test User'
        ];

        $this->assertEquals('123', $this->provider->getUserData($userData, 'id'));
        $this->assertEquals('testuser', $this->provider->getUserData($userData, 'nickname'));
        $this->assertEquals('Test User', $this->provider->getUserData($userData, 'name'));
        $this->assertNull($this->provider->getUserData($userData, 'nonexistent'));
    }

    public function testGetUserDataWithCustomMapping()
    {
        $config = new MockConfig([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'redirect' => 'redirect_uri',
            'host' => 'https://your-company.users.au',
            'user_id' => 'user_identifier',
            'user_nickname' => 'display_name'
        ]);
        
        $this->provider->setConfig($config);

        $userData = [
            'user_identifier' => '456',
            'display_name' => 'customuser'
        ];

        $this->assertEquals('456', $this->provider->getUserData($userData, 'id'));
        $this->assertEquals('customuser', $this->provider->getUserData($userData, 'nickname'));
    }
} 