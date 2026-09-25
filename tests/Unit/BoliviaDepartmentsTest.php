<?php

namespace Tests\Unit;

use App\Support\BoliviaDepartments;
use Tests\TestCase;

class BoliviaDepartmentsTest extends TestCase
{
    public function test_catalog_has_the_nine_unique_official_departments(): void
    {
        $catalog = BoliviaDepartments::all();

        $this->assertCount(9, $catalog);
        $this->assertCount(9, array_unique(array_column($catalog, 'codigo')));
        $this->assertCount(9, array_unique(array_column($catalog, 'nombre')));
        $this->assertSame('Chuquisaca', $catalog[0]['nombre']);
        $this->assertSame('Pando', $catalog[8]['nombre']);
    }

    public function test_normalization_accepts_case_spaces_and_missing_accents(): void
    {
        $this->assertSame('Santa Cruz', BoliviaDepartments::normalize('  santa   cruz '));
        $this->assertSame('Potosí', BoliviaDepartments::normalize('POTOSI'));
        $this->assertSame('La Paz', BoliviaDepartments::normalize('la paz'));
        $this->assertSame('Departamento inventado', BoliviaDepartments::normalize(' Departamento inventado '));
        $this->assertNull(BoliviaDepartments::normalize('  '));
    }
}
