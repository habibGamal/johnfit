<?php

namespace App\Enums;

enum AssessmentType: string
{
    case Text = 'text';
    case Select = 'select';
    case MultipleSelect = 'multiple_select';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Text Input',
            self::Select => 'Select List',
            self::MultipleSelect => 'Multiple Select',
        };
    }
}
