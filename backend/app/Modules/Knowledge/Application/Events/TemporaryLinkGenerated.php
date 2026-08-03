<?php

declare(strict_types=1);

namespace App\Modules\Knowledge\Application\Events;

use App\Modules\Knowledge\Infrastructure\Persistence\Models\Attachment;
use Illuminate\Foundation\Events\Dispatchable;

class TemporaryLinkGenerated
{
    use Dispatchable;

    public function __construct(
        public Attachment $attachment,
        public string $temporaryUrl,
        public int $expiresInMinutes
    ) {}
}
