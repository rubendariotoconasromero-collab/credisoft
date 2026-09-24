<?php

namespace Tests\Feature;

use App\Services\PrivateImages;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class PrivateImageMigrationTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = storage_path('app/diagnostics/migrate-images-'.Str::uuid());
        File::ensureDirectoryExists($this->root.'/public/img/cliente');
        $this->app->usePublicPath($this->root.'/public');
        config(['filesystems.disks.expedientes.root' => $this->root.'/private']);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->root);
        parent::tearDown();
    }

    public function test_dry_run_then_verified_migration_is_repeatable(): void
    {
        $source = public_path('img/cliente/photo.png');
        file_put_contents($source, 'historical-bytes');
        file_put_contents(public_path('img/cliente/default.png'), 'generic-placeholder');
        $this->artisan('expedientes:privatizar')->assertSuccessful();
        $this->assertFileExists($source);
        $this->assertFileDoesNotExist(PrivateImages::path('cliente', 'photo.png'));
        $this->artisan('expedientes:privatizar', ['--apply' => true])->assertSuccessful();
        $this->assertFileDoesNotExist($source);
        $this->assertSame('historical-bytes', file_get_contents(PrivateImages::path('cliente', 'img/cliente/photo.png')));
        $this->assertFileExists(public_path('img/cliente/default.png'));
        $this->artisan('expedientes:privatizar', ['--apply' => true])->assertSuccessful();
        $this->assertCount(1, file($this->root.'/private/migration.jsonl'));
    }

    public function test_collision_preserves_both_files(): void
    {
        File::ensureDirectoryExists($this->root.'/private/cliente');
        file_put_contents(public_path('img/cliente/photo.png'), 'public-original');
        file_put_contents(PrivateImages::path('cliente', 'photo.png'), 'private-original');
        $this->artisan('expedientes:privatizar', ['--apply' => true])->assertFailed();
        $this->assertSame('public-original', file_get_contents(public_path('img/cliente/photo.png')));
        $this->assertSame('private-original', file_get_contents(PrivateImages::path('cliente', 'photo.png')));
    }

    public function test_path_cannot_escape_managed_directory(): void
    {
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        PrivateImages::path('cliente', '../other/secret.png');
    }
}
