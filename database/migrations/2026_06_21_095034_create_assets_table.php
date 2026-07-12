<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('assets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('name');
        $table->string('slug')->unique();
        $table->string('category')->nullable();
        $table->enum('original_extension', ['blend', 'fbx', 'obj']);
        $table->string('converted_file_path')->nullable();
        $table->string('thumbnail_path')->nullable();
        $table->integer('version')->default(1);
        $table->bigInteger('file_size')->default(0);
        $table->integer('poly_count')->nullable();
        $table->json('tags')->nullable();
        $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('assets');
}
};
