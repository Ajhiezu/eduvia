<?php

namespace App\Enums;

enum AiDraftStatus: string
{
    case DRAFT = 'draft';
    case ACCEPTED = 'accepted';
    case DISCARDED = 'discarded';

    /**
     * Get user-friendly label.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf AI (Perlu Verifikasi)',
            self::ACCEPTED => 'Diterima & Disetujui Guru',
            self::DISCARDED => 'Dibuang Guru',
        };
    }

    /**
     * Get badge visual variant.
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::DRAFT => 'warning',
            self::ACCEPTED => 'success',
            self::DISCARDED => 'neutral',
        };
    }
}
