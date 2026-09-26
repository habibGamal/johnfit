<?php

namespace App\Enums;

enum BadgeTier: string
{
    case Bronze = 'bronze';
    case Silver = 'silver';
    case Gold = 'gold';
    case Platinum = 'platinum';
    case Diamond = 'diamond';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Bronze->value => 'Bronze',
            self::Silver->value => 'Silver',
            self::Gold->value => 'Gold',
            self::Platinum->value => 'Platinum',
            self::Diamond->value => 'Diamond',
        ];
    }

    /**
     * Filament badge color for the tier.
     */
    public function color(): string
    {
        return match ($this) {
            self::Bronze => 'warning',
            self::Silver => 'gray',
            self::Gold => 'success',
            self::Platinum => 'info',
            self::Diamond => 'primary',
        };
    }
}
