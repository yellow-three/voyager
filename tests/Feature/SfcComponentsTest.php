<?php

namespace YellowThree\Voyager\Tests\Feature;

use Illuminate\Support\Facades\Auth;
use YellowThree\Voyager\Tests\TestCase;

class SfcComponentsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Auth::loginUsingId(1);
    }

    public function test_compass_sfc_renders(): void
    {
        $this->app['config']->set('voyager.compass_in_production', true);

        $response = $this->call('GET', route('voyager.compass.index'));

        $response->assertStatus(200);
        $response->assertSee('Voyager Compass');
    }

    public function test_dashboard_sfc_renders(): void
    {
        $response = $this->call('GET', route('voyager.dashboard'));

        $response->assertStatus(200);
    }
}
