<?php

namespace YellowThree\Voyager\Tests;

use Illuminate\Support\Facades\Auth;
use YellowThree\Voyager\Models\Permission;
use YellowThree\Voyager\Models\Role;

class PermissionTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        Auth::loginUsingId(1);
        $this->user = Auth::user();
    }

    public function testNotHavingPermission()
    {
        Permission::create(['key' => 'test']);

        $this->assertFalse($this->user->hasPermission('test'));
    }

    public function testHavingPermission()
    {
        $role = Role::find(1)
            ->permissions()
            ->create(['key' => 'test']);

        $this->assertTrue($this->user->hasPermission('test'));
    }
}
