<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
|--------------------------------------------------------------------------
| Custom Expectations for CSR Platform
|--------------------------------------------------------------------------
*/

// Authentication helpers
function actingAsUser(): Tests\TestCase
{
    return test()->actingAs(\App\Models\User::factory()->create());
}

function actingAsAdmin(): Tests\TestCase
{
    return test()->actingAs(\App\Models\User::factory()->admin()->create());
}

// API testing helpers
function postJson(string $uri, array $data = []): \Illuminate\Testing\TestResponse
{
    return test()->postJson($uri, $data);
}

function getJson(string $uri): \Illuminate\Testing\TestResponse
{
    return test()->getJson($uri);
}

// Campaign testing helpers
function createCampaign(array $attributes = []): \App\Models\Campaign
{
    return \App\Models\Campaign::factory()->create($attributes);
}

function createDonation(array $attributes = []): \App\Models\Donation
{
    return \App\Models\Donation::factory()->create($attributes);
}

// Custom expectations
expect()->extend('toHaveApiResponse', function () {
    return $this->toHaveKeys(['data', 'message'])->toBeArray();
});

expect()->extend('toBeValidJwtToken', function () {
    return $this->toMatch('/^[A-Za-z0-9-_=]+\.[A-Za-z0-9-_=]+\.?[A-Za-z0-9-_.+\/=]*$/');
});

expect()->extend('toBeValidUuid', function () {
    return $this->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i');
});

expect()->extend('toBeValidEmail', function () {
    return $this->toMatch('/^[^\s@]+@[^\s@]+\.[^\s@]+$/');
});

expect()->extend('toBePositiveAmount', function () {
    return $this->toBeFloat()->toBeGreaterThan(0);
});

/*
|--------------------------------------------------------------------------
| Database Testing Helpers
|--------------------------------------------------------------------------
*/

function refreshDatabase(): void
{
    test()->refreshDatabase();
}

function seedDatabase(): void
{
    test()->artisan('db:seed');
} 