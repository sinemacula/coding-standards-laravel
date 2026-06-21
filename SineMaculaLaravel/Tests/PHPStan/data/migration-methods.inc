<?php

namespace SineMaculaLaravel\Tests\PHPStan\Data;

use Illuminate\Database\Migrations\Migration;

class CompleteMigration extends Migration
{
    public function up(): void
    {
    }

    public function down(): void
    {
    }
}

class MissingDownMigration extends Migration
{
    public function up(): void
    {
    }
}

class MissingUpMigration extends Migration
{
    public function down(): void
    {
    }
}

class NotAMigration
{
    public function up(): void
    {
    }
}
