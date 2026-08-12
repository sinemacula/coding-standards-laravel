<?php

namespace ForeignIdFor;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCredentialsTable extends Migration
{
    public function up(): void
    {
        Schema::create('credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('organization_id');
            $table->foreignId('user_id')->constrained();
            $table->foreignUlid('audit_log_entry_id')->nullable();

            $table->foreignIdFor(Credential::class);
            $table->string('name');
            $table->uuid('external_reference');
            $table->uuid('legacy_id');
            $table->string('vendor_id');
            $table->foreignId($column);
            $table->foreignUuid('_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credentials');
    }
}

class NotAMigration
{
    public function up(Blueprint $table): void
    {
        $table->foreignUuid('organization_id');
    }
}
