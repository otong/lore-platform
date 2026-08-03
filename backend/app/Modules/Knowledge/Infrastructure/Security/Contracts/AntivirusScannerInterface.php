<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Infrastructure\Security\Contracts;

use Illuminate\Http\UploadedFile;

interface AntivirusScannerInterface
{
    public function scan(UploadedFile $file): bool;
}
