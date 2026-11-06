<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class ImageSecurityTest extends TestCase
{
    use RefreshDatabase;
    private string $temporaryPublic;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.seed_demo_data' => true]);
        $this->seed(DatabaseSeeder::class);
        $this->temporaryPublic = storage_path('app/diagnostics/image-test-'.Str::uuid());
        File::makeDirectory($this->temporaryPublic.'/img/cliente', 0755, true);
        config(['filesystems.disks.expedientes.root' => $this->temporaryPublic.'/img']);
    }

    protected function tearDown(): void
    {
        if (isset($this->temporaryPublic)) File::deleteDirectory($this->temporaryPublic);
        parent::tearDown();
    }

    public function test_upload_uses_detected_extension_and_download_requires_permission(): void
    {
        $admin = User::where('name', 'administrador')->firstOrFail();
        $id = DB::table('cliente')->value('id');
        $this->actingAs($admin);
        $result = $this->postJson('/fotoCliente', ['id_cliente' => $id, 'imagen' => $this->imageUpload('payload.php.png')])->assertOk();
        $filename = $result->json('imagen');
        $this->assertMatchesRegularExpression('/^[a-f0-9-]+\.(png|jpg|jpeg)$/', $filename);
        $this->assertFileExists(\App\Services\PrivateImages::path('cliente', $filename));
        $this->assertFileDoesNotExist(public_path('img/cliente/'.$filename));
        $this->get('/img/cliente/'.$filename)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $pdf = $this->get('/clientes_pdf?id_cliente='.$id)->assertOk();
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        $this->post('/administracion');
        $this->getJson('/img/cliente/'.$filename)->assertUnauthorized();
        $this->actingAs(User::where('name', 'lucia.rivera')->firstOrFail());
        $this->get('/img/cliente/'.$filename)->assertOk(); // Shared by the cash collection screen.
        DB::table('permiso_rol')->where('id_rol', $this->app['auth']->user()->id_rol)->delete();
        $this->getJson('/img/cliente/'.$filename)->assertForbidden();
    }

    public function test_failed_database_update_preserves_previous_file_and_removes_new_upload(): void
    {
        $id = DB::table('cliente')->value('id');
        $previous = UploadedFile::fake()->image('previous.png');
        $previous->move(\App\Services\PrivateImages::directory('cliente'), 'previous.png');
        DB::table('cliente')->where('id', $id)->update(['imagen' => 'previous.png']);
        DB::unprepared("CREATE TRIGGER reject_photo BEFORE UPDATE ON cliente BEGIN SELECT RAISE(ABORT, 'simulated failure'); END");
        $this->actingAs(User::where('name', 'administrador')->firstOrFail());
        $this->postJson('/fotoCliente', ['id_cliente' => $id, 'imagen' => $this->imageUpload('new.png')])->assertStatus(500);
        $this->assertDatabaseHas('cliente', ['id' => $id, 'imagen' => 'previous.png']);
        $this->assertFileExists(\App\Services\PrivateImages::path('cliente', 'previous.png'));
        $this->assertCount(1, File::files(\App\Services\PrivateImages::directory('cliente')));
    }

    public function test_guarantee_image_is_only_visible_to_its_assigned_official(): void
    {
        $official = User::where('name', 'camila.ortiz')->firstOrFail();
        $this->actingAs($official);
        foreach ([true, false] as $own) {
            $guarantee = DB::table('garantia')->join('solicitud', 'solicitud.id', '=', 'garantia.id_solicitud')
                ->where('solicitud.id_usuario', $own ? '=' : '!=', $official->id)->value('garantia.id');
            $this->assertNotNull($guarantee);
            $filename = ($own ? 'own' : 'foreign').'.png';
            $this->imageUpload($filename)->move(\App\Services\PrivateImages::directory('garantia'), $filename);
            DB::table('imagen')->insert(['id_garantia' => $guarantee, 'imagen' => 'img/garantia/'.$filename]);
            $response = $this->get('/img/garantia/'.$filename);
            $own ? $response->assertOk() : $response->assertNotFound();
        }
        $this->actingAs(User::where('name', 'administrador')->firstOrFail());
        $this->get('/img/garantia/foreign.png')->assertOk();
        $this->get('/img/garantia/unreferenced.png')->assertNotFound();
    }

    private function imageUpload(string $name): UploadedFile
    {
        $source = UploadedFile::fake()->image('source.png');
        $path = $this->temporaryPublic.'/upload-'.Str::uuid();
        file_put_contents($path, file_get_contents($source->getRealPath()));
        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
