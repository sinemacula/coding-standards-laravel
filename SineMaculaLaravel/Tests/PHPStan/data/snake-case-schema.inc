<?php

namespace SineMaculaLaravel\Tests\PHPStan\Data;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('set foreign_key_checks = 0');

        Schema::hasTable('blog_posts');

        Schema::create('blogPosts', function (Blueprint $table): void {
            $table->id();
            $table->uuid();
            $table->string('firstName');
            $table->string('email');
            $table->unsignedBigInteger('authorId');
            $table->string('address_line_1');
            $table->enum('status', ['Active', 'Inactive']);
            $table->string('title')->default('Hello World');
            $table->index(['authorId', 'created_at']);

            $audit = 'updated_at';
            $table->index(['user_id', $audit]);
        });

        Schema::rename('oldName', 'new_name');

        Schema::table('comments', function (Blueprint $table): void {
            $table->boolean('is_approved');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blogPosts');
    }
};

class NotAMigration
{
    public function run(): void
    {
        Schema::create('CamelTable', function (Blueprint $table): void {
            $table->string('camelColumn');
        });
    }
}
