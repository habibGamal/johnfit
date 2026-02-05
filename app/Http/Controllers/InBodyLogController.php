<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInBodyLogRequest;
use App\Models\InBodyLog;
use App\Services\InBodyAnalysisService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InBodyLogController extends Controller
{
    public function __construct(
        private readonly InBodyAnalysisService $analysisService
    ) {}

    /**
     * Display a listing of the resource (InBody Dashboard).
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $analysis = $this->analysisService->getAnalysis($user);

        return Inertia::render('InBody/Index', [
            'analysis' => $analysis,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInBodyLogRequest $request): RedirectResponse
    {
        $user = $request->user();

        $log = InBodyLog::create([
            'user_id' => $user->id,
            ...$request->validated(),
        ]);

        return redirect()->route('inbody.index')
            ->with('success', 'InBody measurement recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InBodyLog $inbody): Response
    {
        $this->authorize('view', $inbody);

        $previousLog = $inbody->getPreviousLog();
        $delta = $previousLog
            ? $this->analysisService->calculateDelta($inbody, $previousLog)
            : null;
        $analysis = $previousLog
            ? $this->analysisService->analyzeBodyComposition($inbody, $previousLog)
            : null;

        return Inertia::render('InBody/Show', [
            'log' => $inbody,
            'previousLog' => $previousLog,
            'delta' => $delta,
            'analysis' => $analysis,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreInBodyLogRequest $request, InBodyLog $inbody): RedirectResponse
    {
        $this->authorize('update', $inbody);

        $inbody->update($request->validated());

        return redirect()->route('inbody.index')
            ->with('success', 'InBody measurement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InBodyLog $inbody): RedirectResponse
    {
        $this->authorize('delete', $inbody);

        $inbody->delete();

        return redirect()->route('inbody.index')
            ->with('success', 'InBody measurement deleted successfully.');
    }

    /**
     * Get analysis data via API (for AJAX requests).
     */
    public function analysis(Request $request): array
    {
        $user = $request->user();

        return $this->analysisService->getAnalysis($user);
    }
}
