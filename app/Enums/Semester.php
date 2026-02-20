<?php

namespace App\Enums;

enum Semester: string
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
