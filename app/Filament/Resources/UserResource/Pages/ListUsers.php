<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Users'),

            'action_required' => Tab::make('Action Required')
                ->badge(fn () => User::whereActionRequired(3)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereActionRequired(3)),

            'needs_plans' => Tab::make('Needs Plan Assignment')
                ->badge(fn () => User::whereNeedsPlans()->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNeedsPlans()),

            'expiring_soon' => Tab::make('Plans Expiring Soon')
                ->badge(fn () => User::wherePlansExpiringSoon(3)->count())
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->wherePlansExpiringSoon(3)),

            'missing_workout' => Tab::make('Needs Workout Plan')
                ->badge(fn () => User::whereNeedsWorkoutPlan()->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNeedsWorkoutPlan()),

            'missing_meal' => Tab::make('Needs Meal Plan')
                ->badge(fn () => User::whereNeedsMealPlan()->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNeedsMealPlan()),

            'fully_covered' => Tab::make('Fully Covered')
                ->badge(fn () => User::whereFullyCovered(3)->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereFullyCovered(3)),
        ];
    }
}
