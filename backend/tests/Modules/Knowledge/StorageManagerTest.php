<?php

declare(strict_types=1);

namespace Tests\Modules\Knowledge;

use App\Modules\Knowledge\Infrastructure\Storage\Drivers\LocalStorageDriver;
use App\Modules\Knowledge\Infrastructure\Storage\StorageManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

class StorageManagerTest extends TestCase
{
    protected StorageManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->manager = new StorageManager([
            'local' => new LocalStorageDriver,
        ]);
    }

    public function test_storage_manager_delegates_to_driver_without_switch_or_if_else(): void
    {
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $result = $this->manager->store($file, 'attachments/1/uuid-123', 'local');

        $this->assertEquals('test.pdf', $result['file_name']);
        $this->assertEquals('local', $result['storage_disk']);
        $this->assertNotEmpty($result['storage_path']);
        Storage::disk('local')->assertExists($result['storage_path']);
    }

    public function test_throws_exception_on_missing_storage_driver(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing or unsupported storage driver: [invalid_driver]');

        $this->manager->driver('invalid_driver');
    }
}
