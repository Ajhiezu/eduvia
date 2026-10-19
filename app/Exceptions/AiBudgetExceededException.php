<?php

namespace App\Exceptions;

use Exception;

class AiBudgetExceededException extends Exception
{
    public function __construct(string $message = 'Batas kuota token atau anggaran bulanan AI untuk sekolah ini telah tercapai. Hubungi administrator sekolah/yayasan untuk peningkatan alokasi.')
    {
        parent::__construct($message, 429);
    }
}
