<?php

use App\Enums\AssessmentType;
use App\Models\Assessment;
use App\Models\User;
use App\Models\UserAssessmentAnswer;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->user = User::factory()->create(['role' => 'user', 'assessment_completed_at' => null]);
});

describe('Assessment Middleware', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('assessment.index'))->assertRedirect(route('login'));
    });

    it('redirects users without completed assessment to assessment page', function () {
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertRedirect(route('assessment.index'));
    });

    it('allows admins to skip the assessment', function () {
        $this->actingAs($this->admin)
            ->get(route('dashboard'))
            ->assertOk();
    });

    it('allows users with completed assessment to access dashboard', function () {
        $this->user->update(['assessment_completed_at' => now()]);

        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertOk();
    });
});

describe('Assessment Index', function () {
    it('shows the assessment page for an authenticated user', function () {
        Assessment::factory()->create([
            'question' => 'How old are you?',
            'type' => AssessmentType::Text,
            'order' => 1,
        ]);

        $this->actingAs($this->user)
            ->get(route('assessment.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Assessment/Index')
                ->has('assessments', 1)
            );
    });

    it('includes question, type, options and order in the page props', function () {
        $assessment = Assessment::factory()->create([
            'question' => 'What is your goal?',
            'type' => AssessmentType::Select,
            'options' => [['label' => 'Lose weight'], ['label' => 'Build muscle']],
            'order' => 1,
        ]);

        $this->actingAs($this->user)
            ->get(route('assessment.index'))
            ->assertInertia(fn ($page) => $page
                ->has('assessments.0', fn ($q) => $q
                    ->where('id', $assessment->id)
                    ->where('question', 'What is your goal?')
                    ->where('type', 'select')
                    ->has('options')
                    ->etc()
                )
            );
    });
});

describe('Assessment Store', function () {
    it('saves answers and marks the assessment as completed', function () {
        $q1 = Assessment::factory()->create(['type' => AssessmentType::Select, 'order' => 1]);
        $q2 = Assessment::factory()->create(['type' => AssessmentType::Text, 'order' => 2]);

        $this->actingAs($this->user)
            ->post(route('assessment.store'), [
                'answers' => [
                    $q1->id => 'Lose weight',
                    $q2->id => '75',
                ],
            ])
            ->assertRedirect(route('dashboard'));

        expect($this->user->fresh()->hasCompletedAssessment())->toBeTrue();

        $this->assertDatabaseHas('user_assessment_answers', [
            'user_id' => $this->user->id,
            'assessment_id' => $q1->id,
        ]);
        $this->assertDatabaseHas('user_assessment_answers', [
            'user_id' => $this->user->id,
            'assessment_id' => $q2->id,
        ]);
    });

    it('stores multiple_select answers as an array', function () {
        $q = Assessment::factory()->create([
            'type' => AssessmentType::MultipleSelect,
            'options' => [['label' => 'Chest'], ['label' => 'Slim legs']],
            'order' => 1,
        ]);

        $this->actingAs($this->user)
            ->post(route('assessment.store'), [
                'answers' => [
                    $q->id => ['Chest', 'Slim legs'],
                ],
            ])
            ->assertRedirect(route('dashboard'));

        $answer = UserAssessmentAnswer::where('user_id', $this->user->id)
            ->where('assessment_id', $q->id)
            ->first();

        expect($answer->answer)->toBe(['Chest', 'Slim legs']);
    });

    it('requires all questions to be answered', function () {
        $q1 = Assessment::factory()->create(['type' => AssessmentType::Select, 'order' => 1]);
        $q2 = Assessment::factory()->create(['type' => AssessmentType::Text, 'order' => 2]);

        $this->actingAs($this->user)
            ->post(route('assessment.store'), [
                'answers' => [
                    $q1->id => 'Lose weight',
                    // $q2 intentionally missing
                ],
            ])
            ->assertSessionHasErrors(["answers.{$q2->id}"]);
    });

    it('updates existing answer when user re-submits', function () {
        $q = Assessment::factory()->create(['type' => AssessmentType::Select, 'order' => 1]);

        UserAssessmentAnswer::create([
            'user_id' => $this->user->id,
            'assessment_id' => $q->id,
            'answer' => ['Old answer'],
        ]);

        $this->actingAs($this->user)
            ->post(route('assessment.store'), [
                'answers' => [$q->id => 'New answer'],
            ])
            ->assertRedirect(route('dashboard'));

        expect(
            UserAssessmentAnswer::where('user_id', $this->user->id)
                ->where('assessment_id', $q->id)
                ->count()
        )->toBe(1);

        $this->assertDatabaseHas('user_assessment_answers', [
            'user_id' => $this->user->id,
            'assessment_id' => $q->id,
        ]);
    });
});
