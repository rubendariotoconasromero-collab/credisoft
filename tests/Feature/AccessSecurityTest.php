<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccessSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.seed_demo_data' => true]);
        $this->seed(DatabaseSeeder::class);
    }

    public function test_disabled_user_cannot_start_a_session(): void
    {
        DB::table('users')->where('name', 'administrador')->update(['estado' => 0]);
        $this->post('/login_process', ['name' => 'administrador', 'password' => 'admin123'])->assertRedirect('/');
        $this->assertGuest();
        $this->getJson('/me')->assertUnauthorized();
    }

    public function test_disabling_user_revokes_existing_session_on_next_request(): void
    {
        $user = User::where('name', 'administrador')->firstOrFail();
        $this->actingAs($user)->getJson('/me')->assertOk();
        DB::table('users')->where('id', $user->id)->update(['estado' => 0]);
        $this->getJson('/me')->assertUnauthorized();
        $this->assertGuest();
    }

    public function test_inactive_role_cannot_login_or_keep_session(): void
    {
        $user = User::where('name', 'administrador')->firstOrFail();
        DB::table('rol')->where('id', $user->id_rol)->update(['estado' => 0]);
        $this->post('/login_process', ['name' => $user->name, 'password' => 'admin123'])->assertRedirect('/');
        $this->assertGuest();
        $this->actingAs($user)->getJson('/me')->assertUnauthorized();
    }

    public function test_disabled_permission_is_denied_in_backend_and_session(): void
    {
        $user = User::where('name', 'administrador')->firstOrFail();
        DB::table('permiso')->where('nombre', 'cliente')->update(['estado' => 0]);
        $this->assertFalse($user->tienePermiso('cliente'));
        $this->actingAs($user)->postJson('/save_cliente', [])->assertForbidden();
        $this->assertNotContains('cliente', $this->getJson('/me')->assertOk()->json('permisos'));
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login_process', ['name' => 'administrador', 'password' => 'incorrecta'])->assertRedirect('/');
        }
        $this->postJson('/login_process', ['name' => 'administrador', 'password' => 'admin123'])->assertStatus(429);
        $this->assertGuest();
    }

    public function test_logout_ends_session(): void
    {
        $this->post('/login_process', ['name' => 'administrador', 'password' => 'admin123'])->assertRedirect('/administracion');
        $this->assertAuthenticated();
        $this->post('/administracion')->assertRedirect('/');
        $this->getJson('/me')->assertUnauthorized();
    }

    public function test_reads_and_exports_require_module_permission(): void
    {
        $user = User::where('name', 'cajero_user')->firstOrFail();
        $this->actingAs($user);
        foreach (['/get_clientes_paginate', '/clientes_pdf', '/get_usuarios', '/libro-mayor', '/reportes/libro-mayor/excel', '/get_permisos'] as $url) {
            $this->getJson($url)->assertForbidden();
        }
        $this->getJson('/get_motivos_ingresos_activos?tipo=caja')->assertOk();
    }

    public function test_only_owner_can_operate_or_close_cash_even_as_administrator(): void
    {
        $cash = DB::table('caja')->where('estado', 1)->first();
        $balance = DB::table('boveda')->value('saldo_actual');
        foreach (['administrador', 'cajero2_user', 'encargado_user'] as $login) {
            $this->actingAs(User::where('name', $login)->firstOrFail());
            foreach (['/close_caja', '/actualizar_desembolso', '/guardar_pago_adm', '/save_ingreso', '/save_gasto'] as $url) {
                $this->postJson($url, ['id_caja' => $cash->id])->assertForbidden();
            }
        }
        $this->assertEquals($balance, DB::table('boveda')->value('saldo_actual'));
        $this->assertDatabaseHas('caja', ['id' => $cash->id, 'estado' => 1]);
    }

    public function test_cash_selection_uses_current_user_and_refuses_closed_box(): void
    {
        $user = User::where('name', 'cajero2_user')->firstOrFail();
        $cash = DB::table('caja')->where('estado', 1)->where('id_usuario', $user->id)->first();
        $this->actingAs($user)->getJson('/caja/saldo-actual')->assertOk()->assertJsonPath('id_caja', $cash->id);
        $this->getJson('/caja_abierta')->assertOk()->assertJsonPath('usuario_actual', 1);
        $this->postJson('/save_ingreso', ['monto' => 12, 'descripcion' => 'Prueba propietario'])->assertSuccessful();
        $this->assertDatabaseHas('ingreso', ['descripcion' => 'Prueba propietario', 'id_caja' => $cash->id]);
        DB::table('caja')->where('id', $cash->id)->update(['estado' => 0]);
        $this->postJson('/save_ingreso', ['monto' => 12, 'descripcion' => 'No autorizado'])->assertStatus(422);
        $this->postJson('/close_caja', ['id_caja' => $cash->id])->assertStatus(422);
        $this->assertDatabaseMissing('ingreso', ['descripcion' => 'No autorizado']);
    }

    public function test_official_cannot_read_or_modify_another_portfolio(): void
    {
        $user = User::where('name', 'oficial_user')->firstOrFail();
        $other = DB::table('solicitud')->where('id_usuario', '!=', $user->id)->first();
        $plan = DB::table('plan_pago')->where('id_solicitud', $other->id)->first();
        if (!$plan) {
            $plan = DB::table('plan_pago')->join('solicitud', 'solicitud.id', '=', 'plan_pago.id_solicitud')
                ->where('solicitud.id_usuario', '!=', $user->id)->select('plan_pago.*')->first();
        }
        $this->actingAs($user);
        $this->getJson('/solicitud/detalle-completo/'.$other->id)->assertForbidden();
        $this->postJson('/desactivar_solicitud', ['id_solicitud' => $other->id])->assertForbidden();
        $this->getJson('/listar_amortizaciones_cuotas?id_plan_pago='.$plan->id)->assertForbidden();
        $this->getJson('/reprogramacion/calcular/'.$plan->id)->assertForbidden();
        $this->postJson('/solicitud/registrar-especial', ['plan_pago' => ['id_solicitud' => $other->id]])->assertForbidden();
        $response = $this->getJson('/get_solicitudes?criterio=cliente.nombre&buscar=&estado=todos&fecha_inicial=2000-01-01&fecha_final=2100-01-01')->assertOk();
        $this->assertNotEmpty($response->json('data'));
        foreach ($response->json('data') as $row) $this->assertEquals($user->id, $row['id_usuario']);
    }

    public function test_invalid_uploads_are_rejected_before_changing_photos(): void
    {
        $this->actingAs(User::where('name', 'administrador')->firstOrFail());
        foreach ([['/fotoCliente', 'cliente', 'id_cliente'], ['/fotoCodeudor', 'codeudor', 'id_codeudor']] as [$url, $table, $field]) {
            $record = DB::table($table)->first();
            $this->postJson($url, [$field => $record->id, 'imagen' => \Illuminate\Http\UploadedFile::fake()->createWithContent('evil.php', '<?php echo 1;')])->assertStatus(422);
            $this->assertDatabaseHas($table, ['id' => $record->id, 'imagen' => $record->imagen]);
            $this->postJson($url, [$field => 999999])->assertStatus(422);
        }
    }

    public function test_user_catalog_does_not_expose_password_hashes_or_remember_tokens(): void
    {
        $this->actingAs(User::where('name', 'administrador')->firstOrFail());
        foreach ($this->getJson('/get_usuarios_sin')->assertOk()->json() as $user) {
            $this->assertArrayNotHasKey('password', $user);
            $this->assertArrayNotHasKey('remember_token', $user);
        }
    }

    public function test_data_endpoints_have_explicit_permissions(): void
    {
        $exceptions = ['LoginController', 'SesionController', 'PerfilController', 'PrivateImageController'];
        foreach (app('router')->getRoutes() as $route) {
            $action = $route->getActionName();
            if (!str_starts_with($action, 'App\\Http\\Controllers\\')) continue;
            $controller = class_basename(explode('@', $action)[0]);
            if (in_array($controller, $exceptions, true) || $route->uri() === 'get_mi_empresa') continue;
            $permissions = array_filter($route->gatherMiddleware(), fn ($middleware) => str_starts_with($middleware, 'permiso:'));
            $this->assertNotEmpty($permissions, $route->uri().' requires explicit module permission.');
        }
    }

    public function test_production_seed_requires_an_explicit_bootstrap_credential(): void
    {
        $this->app->instance('env', 'production');
        config(['app.bootstrap_admin_password' => null]);
        DB::table('users')->where('name', 'administrador')->update(['name' => 'old_admin']);
        $this->expectException(\LogicException::class);
        $this->seed(\Database\Seeders\UserSeeder::class);
    }
}
