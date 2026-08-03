<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Infrastructure\Security;

use App\Modules\Knowledge\Infrastructure\Security\Contracts\AntivirusScannerInterface;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class AntivirusPipeline
{
    public function __construct(
        protected AntivirusScannerInterface $scanner
    ) {}

    public function scan(UploadedFile $file): bool
    {
        if (! $this->scanner->scan($file)) {
            throw new InvalidArgumentException("Antivirus security threat detected in file: [{$file->getClientOriginalName()}]");
        }

        return true;
    }
}
