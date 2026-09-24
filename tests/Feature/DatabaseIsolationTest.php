<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseIsolationTest extends TestCase
{
    public function test_only_in_memory_database_is_available(): void
    {
        $this->assertTrue(app()->environment('testing'));
        $this->assertSame(['sqlite'], array_keys(config('database.connections')));
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame('', DB::selectOne('PRAGMA database_list')->file);
        DB::statement('CREATE TABLE isolation_probe (id INTEGER PRIMARY KEY)');
        DB::table('isolation_probe')->insert(['id' => 1]);
        $this->assertSame(1, DB::table('isolation_probe')->count());
    }
}
