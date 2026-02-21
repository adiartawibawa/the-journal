<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Semester: string implements HasLabel
{
    case Ganjil = '1';
    case Genap = '2';

    // Helper untuk mendapatkan label di UI
    public function getLabel(): string
    {
        return match ($this) {
            self::Ganjil => 'Ganjil',
            self::Genap => 'Genap',
        };
    }
}
