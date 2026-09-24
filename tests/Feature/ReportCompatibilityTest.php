<?php

namespace Tests\Feature;

use App\Exports\PlanPagoExport;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ReportCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-09-24 15:00:00');
        config(['app.seed_demo_data' => true]);
        $this->seed(DatabaseSeeder::class);
        $this->actingAs(User::where('name', 'administrador')->firstOrFail());
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_actual_ticket_and_person_reports_generate_pdf(): void
    {
        $code = DB::table('pago')->whereNotNull('codigo_transaccion')->value('codigo_transaccion');
        $this->assertNotEmpty($code);
        foreach ([
            '/imprimir/recibo/'.$code,
            '/clientes_pdf?id_cliente='.DB::table('cliente')->value('id'),
            '/codeudores_pdf?id_codeudor='.DB::table('codeudor')->value('id'),
        ] as $url) {
            $response = $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $response->getContent(), $url);
            $this->assertGreaterThan(1000, strlen($response->getContent()));
        }
    }

    public function test_real_credit_export_produces_readable_excel(): void
    {
        $bytes = Excel::raw(new PlanPagoExport(), \Maatwebsite\Excel\Excel::XLSX);
        $file = tempnam(sys_get_temp_dir(), 'credit-export-');
        try {
            file_put_contents($file, $bytes);
            $book = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $this->assertSame(DB::table('plan_pago')->count() + 1, $book->getActiveSheet()->getHighestDataRow());
            $this->assertNotEmpty($book->getActiveSheet()->getCell('A2')->getValue());
            $book->disconnectWorksheets();
        } finally { unlink($file); }
    }

    public function test_legacy_pdf_alias_uses_the_updated_wrapper(): void
    {
        $pdf = \PDF::loadHTML('<h1>Recibo de prueba</h1>')->output();
        $this->assertStringStartsWith('%PDF-', $pdf);
        $this->assertFalse(app('dompdf')->getOptions()->getIsPhpEnabled());
        $this->assertFalse(app('dompdf')->getOptions()->getIsRemoteEnabled());
    }
}
