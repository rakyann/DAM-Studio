<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asset_versions', function (Blueprint $table) {
            $table->renameColumn('file_path', 'master_zip_path');
        });

        Schema::table('asset_versions', function (Blueprint $table) {
            $table->string('viewer_glb_path')->nullable()->after('master_zip_path');
            $table->string('thumbnail_path')->nullable()->after('viewer_glb_path');
            $table->integer('polygon_count')->nullable()->after('file_size');
            $table->integer('vertex_count')->nullable()->after('polygon_count');
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued')->after('vertex_count');
            $table->text('error_log')->nullable()->after('status');
        });

        // Ensure version_number is unsigned and unique per asset_id
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'sqlite') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE asset_versions MODIFY COLUMN version_number INT UNSIGNED NOT NULL");
        }
        Schema::table('asset_versions', function (Blueprint $table) {
            $table->unique(['asset_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::table('asset_versions', function (Blueprint $table) {
            $table->dropUnique(['asset_id', 'version_number']);
        });

        Schema::table('asset_versions', function (Blueprint $table) {
            $table->dropColumn(['viewer_glb_path', 'thumbnail_path', 'polygon_count', 'vertex_count', 'status', 'error_log']);
            $table->renameColumn('master_zip_path', 'file_path');
        });
    }
};
