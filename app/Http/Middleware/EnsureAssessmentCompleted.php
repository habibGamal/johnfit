<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAssessmentCompleted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->isAdmin() && ! $user->hasCompletedAssessment()) {
            if (! $request->routeIs('assessment.*')) {
                return redirect()->route('assessment.index');
            }
        }

        return $next($request);
    }
}
