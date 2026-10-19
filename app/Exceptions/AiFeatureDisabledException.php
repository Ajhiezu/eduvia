<?php

namespace App\Exceptions;

use Exception;

class AiFeatureDisabledException extends Exception
{
    public function __construct(string $message = 'Fitur Asisten AI belum diaktifkan untuk sekolah ini. Administrator yayasan/sekolah dapat mengaktifkannya melalui menu pengaturan sistem.')
    {
        parent::__construct($message, 403);
    }
}
