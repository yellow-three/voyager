<?php

namespace YellowThree\Voyager\Plugins\Contracts;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface AuthenticationPlugin
{
    public function login(Request $request): Response;
    public function logout(Request $request): Response;
    public function forgotPassword(Request $request): Response;
}
