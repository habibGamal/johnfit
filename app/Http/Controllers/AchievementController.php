<?php

namespace App\Http\Controllers;

use App\Services\BadgeService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AchievementController extends Controller
{
    public function __construct(
        protected BadgeService $badgeService
    ) {}

    /**
     * Display the user's achievement journey.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Advance streaks and award any newly earned badges before rendering.
        $this->badgeService->refresh($user);

        return Inertia::render('Achievements/Index', [
            'journey' => $this->badgeService->getJourney($user),
        ]);
    }

    /**
     * Re-evaluate the journey on demand (pull-to-refresh from the client).
     */
    public function sync(Request $request): RedirectResponse
    {
        $this->badgeService->refresh($request->user());

        return back();
    }
}
