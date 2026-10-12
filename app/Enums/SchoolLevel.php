<?php

namespace App\Enums;

enum SchoolLevel: string
{
    case SD = 'sd';
    case SMP = 'smp';
    case SMA = 'sma';
    case SMK = 'smk';

    /**
     * Get official school level title.
     */
    public function label(): string
    {
        return match ($this) {
            self::SD => 'Sekolah Dasar / Madrasah Ibtidaiyah (SD/MI)',
            self::SMP => 'Sekolah Menengah Pertama / MTs (SMP/MTs)',
            self::SMA => 'Sekolah Menengah Atas / MA (SMA/MA)',
            self::SMK => 'Sekolah Menengah Kejuruan / MAK (SMK/MAK)',
        };
    }

    /**
     * Get short abbreviation.
     */
    public function code(): string
    {
        return strtoupper($this->value);
    }

    /**
     * Get nominal grade levels for this school type.
     *
     * @return array<int>
     */
    public function grades(): array
    {
        return match ($this) {
            self::SD => [1, 2, 3, 4, 5, 6],
            self::SMP => [7, 8, 9],
            self::SMA, self::SMK => [10, 11, 12],
        };
    }
}
