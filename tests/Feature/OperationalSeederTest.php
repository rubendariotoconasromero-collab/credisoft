<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Database\Seeders\SeedProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Tests\TestCase;

class OperationalSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'app.seed_profile' => 'operativo',
            'app.seed_demo_data' => true,
            'app.bootstrap_admin_password' => 'Clave-operativa-segura-2026',
        ]);
    }

    public function test_operational_profile_seeds_only_the_initial_configuration(): void
    {
        $this->assertSame('operativo', SeedProfile::resolve());
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('mi_empresa', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('rol', 4);
        $this->assertDatabaseCount('permiso', 19);
        $this->assertGreaterThan(0, DB::table('actividades')->count());
        $this->assertGreaterThan(0, DB::table('motivo_ingreso')->where('estado', 0)->count());
        $this->assertGreaterThan(0, DB::table('motivo_gasto')->where('estado', 0)->count());

        $company = DB::table('mi_empresa')->first();
        $admin = DB::table('users')->first();
        $this->assertSame(config('app.seed_operational.company.nombre'), $company->nombre);
        $this->assertSame(config('app.seed_operational.admin.personal'), $admin->personal);
        $this->assertStringNotContainsString('demo', mb_strtolower(json_encode([$company, $admin])));
        $this->assertTrue(Hash::check('Clave-operativa-segura-2026', $admin->password));
        $this->assertSame('Administrador', DB::table('rol')->where('id', $admin->id_rol)->value('nombre'));
        $this->assertSame(19, DB::table('permiso_rol')->where('id_rol', $admin->id_rol)->count());

        foreach (['cliente', 'codeudor', 'solicitud', 'plan_pago', 'cuota', 'pago',
            'caja', 'boveda', 'socios', 'ingreso', 'egreso', 'desembolso',
            'movimientos_caja', 'movimientos_boveda', 'operaciones_financieras'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }

    public function test_operational_profile_requires_a_strong_explicit_password(): void
    {
        config(['app.bootstrap_admin_password' => null]);
        try {
            $this->seed(DatabaseSeeder::class);
            $this->fail('The operational profile must require a bootstrap password.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString('BOOTSTRAP_ADMIN_PASSWORD', $exception->getMessage());
        }
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('mi_empresa', 0);
    }

    public function test_operational_profile_rejects_reseeding_live_data(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('requiere una base sin datos');
        $this->seed(DatabaseSeeder::class);
    }

    public function test_explicit_demo_profile_keeps_the_existing_scenarios(): void
    {
        config(['app.seed_profile' => 'demo', 'app.seed_demo_data' => false]);
        $this->seed(DatabaseSeeder::class);
        $this->assertDatabaseCount('cliente', 16);
        $this->assertDatabaseCount('users', 6);
        $this->assertSame('Financiera Horizonte Andino S.R.L.', DB::table('mi_empresa')->value('nombre'));
    }

    public function test_invalid_profile_fails_before_inserting_data(): void
    {
        config(['app.seed_profile' => 'desconocido']);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('SEED_PROFILE');
        $this->seed(DatabaseSeeder::class);
    }

    public function test_demo_profile_is_rejected_in_production(): void
    {
        $this->app->instance('env', 'production');
        config(['app.seed_profile' => 'demo']);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('solo se permite');
        app(DatabaseSeeder::class)->run();
    }
}
