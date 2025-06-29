<?php

namespace App\Http\Middleware;

use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful as Middleware;

class AllowAllStatefulDomains extends Middleware
{
    protected function isStateful($request)
    {
        return true; // allow all
    }
}
