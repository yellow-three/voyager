<?php

namespace YellowThree\Voyager\Tests;

use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\DataProvider;

class AssetsTest extends TestCase
{
    protected $prefix = '/voyager-assets?path=';

    public function setUp(): void
    {
        parent::setUp();

        Auth::loginUsingId(1);
    }

    public function testCanOpenFileInAssets()
    {
        $url = route('voyager.dashboard').$this->prefix.'css/app.css';

        $response = $this->call('GET', $url);
        $this->assertEquals(200, $response->status(), $url.' did not return a 200');
    }

    public static function urlProvider()
    {
        return [
            'forward slash parent' => ['../dummy_content/pages/page1.jpg'],
            'multiple dots forward' => ['..../dummy_content/pages/page1.jpg'],
            'nested parent' => ['images/../../dummy_content/pages/page1.jpg'],
            'double slash' => ['....//dummy_content/pages/page1.jpg'],
            'backslash parent' => ['..\dummy_content/pages/page1.jpg'],
            'multiple dots backslash' => ['....\dummy_content/pages/page1.jpg'],
            'nested backslash' => ['images/..\..\dummy_content/pages/page1.jpg'],
            'multiple backslash' => ['images/....\\....\\dummy_content/pages/page1.jpg'],
        ];
    }

    #[DataProvider('urlProvider')]
    public function testCannotOpenFileOutsideAssets($url)
    {
        $response = $this->call('GET', route('voyager.dashboard').$this->prefix.$url);
        $this->assertContains($response->status(), [404, 500], $url.' did not return a 404 or 500');
    }
}
