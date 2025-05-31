# Users.au OAuth2 Provider for Laravel Socialite

[![Latest Version on Packagist](https://img.shields.io/packagist/v/users-au/socialite-provider.svg?style=flat-square)](https://packagist.org/packages/users-au/socialite-provider)
[![Total Downloads](https://img.shields.io/packagist/dt/users-au/socialite-provider.svg?style=flat-square)](https://packagist.org/packages/users-au/socialite-provider)
[![Tests](https://img.shields.io/github/actions/workflow/status/users-au/socialite-provider/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/users-au/socialite-provider/actions/workflows/tests.yml)
[![Code Coverage](https://img.shields.io/codecov/c/github/users-au/socialite-provider?style=flat-square)](https://codecov.io/gh/users-au/socialite-provider)
[![PHP Version Require](https://img.shields.io/packagist/php-v/users-au/socialite-provider?style=flat-square)](https://packagist.org/packages/users-au/socialite-provider)
[![License](https://img.shields.io/packagist/l/users-au/socialite-provider.svg?style=flat-square)](https://packagist.org/packages/users-au/socialite-provider)

A Laravel Socialite OAuth2 provider for Users.au authentication service. This package allows you to easily integrate Users.au OAuth2 authentication into your Laravel applications using the Socialite package.

## Overview

This package provides:

- **OAuth2 Authentication**: Seamless integration with Users.au OAuth2 service
- **Flexible Configuration**: Configurable endpoints and user field mappings
- **Standard Socialite Interface**: Uses Laravel Socialite's familiar API
- **Customizable User Mapping**: Map Users.au user data to your application's user model

### Features

- Support for OAuth2 authorization code flow
- Configurable host and endpoint URLs
- Custom user field mapping (id, nickname, name, email, avatar)
- Bearer token authentication for API requests
- Compatible with Laravel Socialite Manager

### How it Works

1. **OAuth2 Flow**: Implements the standard OAuth2 authorization code flow
2. **User Authentication**: Redirects users to Users.au for authentication
3. **Token Exchange**: Exchanges authorization code for access token
4. **User Data Retrieval**: Fetches user information using the access token
5. **User Mapping**: Maps Users.au user data to Laravel Socialite User object

## API Endpoints

The Provider makes calls to three main Users.au OAuth2 endpoints:

### 1. Authorization Endpoint

**URL**: `GET {host}/oauth/authorize`

**Purpose**: Redirects user to Users.au for authentication

**Query Parameters**:
```
client_id={client_id}
redirect_uri={redirect_uri}
response_type=code
scope={scopes}
state={state}
```

**Response**: Redirects to your `redirect_uri` with authorization code
```
{redirect_uri}?code={authorization_code}&state={state}
```

### 2. Token Endpoint

**URL**: `POST {host}/oauth/token`

**Purpose**: Exchange authorization code for access token

**Request Headers**:
```
Content-Type: application/x-www-form-urlencoded
```

**Request Payload**:
```
grant_type=authorization_code
client_id={client_id}
client_secret={client_secret}
code={authorization_code}
redirect_uri={redirect_uri}
```

**Expected Response**:
```json
{
  "access_token": "your_access_token",
  "token_type": "Bearer",
  "expires_in": 3600,
  "refresh_token": "your_refresh_token",
  "scope": "..."
}
```

### 3. User Info Endpoint

**URL**: `GET {host}/api/user`

**Purpose**: Retrieve authenticated user's information

**Request Headers**:
```
Authorization: Bearer {access_token}
```

**Expected Response**:
```json
{
  "id": "user_unique_id",
  "nickname": "user_nickname", 
  "name": "User Full Name",
  "email": "user@example.com",
  "avatar": "https://example.com/avatar.jpg"
}
```

**Note**: The user data structure can be customized using the `userinfo_key` configuration and custom field mappings (`user_id`, `user_nickname`, `user_name`, `user_email`, `user_avatar`).

## Installation

```bash
composer require users-au/socialite-provider
```

## Installation & Basic Usage

Please see the [Base Installation Guide](https://socialiteproviders.com/usage/), then follow the provider specific instructions below.

### Add configuration to `config/services.php`

```php
'usersau' => [    
  'client_id' => env('USERSAU_CLIENT_ID'),  
  'client_secret' => env('USERSAU_CLIENT_SECRET'),  
  'redirect' => env('USERSAU_REDIRECT_URI'),
  'host' => env('USERSAU_HOST'),
],
```

### Add provider event listener

Configure the package's listener to listen for `SocialiteWasCalled` events.

Add the event to your `listen[]` array in `app/Providers/EventServiceProvider`. See the [Base Installation Guide](https://socialiteproviders.com/usage/) for detailed instructions.

```php
protected $listen = [
    \SocialiteProviders\Manager\SocialiteWasCalled::class => [
        // ... other providers
        \SocialiteProviders\UsersAu\UsersauExtendSocialite::class.'@handle',
    ],
];
```

### Usage

You should now be able to use the provider like you would regularly use Socialite (assuming you have the facade installed):

```php
return Socialite::driver('usersau')->redirect();
```

### Returned User fields

- ``id``
- ``nickname``
- ``name``
- ``email``
- ``avatar``

## Testing

This package includes comprehensive unit tests to ensure reliability and maintainability.

### Running Tests

To run the test suite:

```bash
# Install dependencies
composer install

# Run all tests
composer test

# Or run PHPUnit directly
./vendor/bin/phpunit

# Run tests with documentation format
./vendor/bin/phpunit --testdox

# Run static analysis
composer analyse

# Run both tests and analysis
composer check
```

### Continuous Integration

This package uses GitHub Actions for continuous integration with the following workflows:

- **Tests**: Runs unit tests across PHP 7.4, 8.0, 8.1, 8.2, and 8.3
- **Code Coverage**: Generates and uploads coverage reports to Codecov
- **Static Analysis**: Runs PHPStan for type checking and code quality
- **Code Style**: Validates PHP syntax and PSR-4 compliance

All workflows run automatically on:
- Push to `main` or `master` branches
- Pull requests to `main` or `master` branches

### Test Coverage

The test suite covers:

- **Provider Configuration**: Tests for all configuration options and defaults
- **OAuth2 Flow**: Tests for authorization URL generation and token exchange
- **User Data Mapping**: Tests for user data retrieval and field mapping
- **Custom Configuration**: Tests for custom endpoints and field mappings
- **URL Handling**: Tests for proper URL construction with various configurations
- **Extension Registration**: Tests for Socialite provider registration

### Test Structure

```
tests/
├── ProviderTest.php           # Main provider functionality tests
└── UsersauExtendSocialiteTest.php  # Extension registration tests
```

The tests use PHPUnit and Mockery for mocking dependencies, ensuring isolated unit tests without external dependencies.

## Development

### Requirements

- PHP 7.4 or higher
- Composer
- PHPUnit (for testing)

### Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

### Code Quality

This package follows PSR-4 autoloading standards and includes:

- Comprehensive unit tests
- Type hints and return types
- Proper error handling
- Documentation for all public methods
