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
        Schema::table('assets', function (Blueprint $table) {
            $table->renameColumn('name', 'title');
            $table->renameColumn('converted_file_path', 'viewer_glb_path');
            $table->renameColumn('poly_count', 'polygon_count');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->enum('visibility', ['private', 'public'])->default('private')->after('title');
            $table->boolean('is_staff_pick')->default(false)->after('visibility');
            $table->string('master_zip_path')->default('')->after('original_file_path');
            $table->integer('vertex_count')->nullable()->after('polygon_count');
            $table->text('error_log')->nullable()->after('status');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->renameColumn('title', 'name');
            $table->renameColumn('viewer_glb_path', 'converted_file_path');
            $table->renameColumn('polygon_count', 'poly_count');
            $table->dropColumn(['visibility', 'is_staff_pick', 'master_zip_path', 'vertex_count', 'error_log']);
        });
    }
};
