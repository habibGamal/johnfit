<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\UserAssessmentAnswer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AssessmentController extends Controller
{
    public function index(): Response
    {
        $assessments = Assessment::orderBy('order')->get()->map(fn (Assessment $a) => [
            'id' => $a->id,
            'question' => $a->question,
            'image' => $a->image ? asset('storage/'.$a->image) : null,
            'type' => $a->type->value,
            'options' => $a->options,
            'order' => $a->order,
        ]);

        return Inertia::render('Assessment/Index', [
            'assessments' => $assessments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $assessments = Assessment::all();

        $request->validate(
            $assessments->mapWithKeys(fn (Assessment $a) => [
                "answers.{$a->id}" => 'required',
            ])->toArray()
        );

        $user = Auth::user();

        foreach ($request->input('answers', []) as $assessmentId => $answer) {
            UserAssessmentAnswer::updateOrCreate(
                ['user_id' => $user->id, 'assessment_id' => $assessmentId],
                ['answer' => is_array($answer) ? $answer : [$answer]]
            );
        }

        $user->update(['assessment_completed_at' => now()]);

        return redirect()->route('dashboard');
    }
}
