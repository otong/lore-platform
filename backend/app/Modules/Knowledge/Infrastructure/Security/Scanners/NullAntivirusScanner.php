<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Infrastructure\Security\Scanners;

use App\Modules\Knowledge\Infrastructure\Security\Contracts\AntivirusScannerInterface;
use Illuminate\Http\UploadedFile;

class NullAntivirusScanner implements AntivirusScannerInterface
{
    public function scan(UploadedFile $file): bool
    {
        return true;
    }
}
