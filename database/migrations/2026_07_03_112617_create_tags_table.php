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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('asset_tag', function (Blueprint $table) {
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['asset_id', 'tag_id']);
        });

        // Migrate existing JSON tags data to relational structure
        $assets = \Illuminate\Support\Facades\DB::table('assets')->whereNotNull('tags')->get();
        foreach ($assets as $asset) {
            if (empty($asset->tags)) continue;
            
            $tagsArray = json_decode($asset->tags, true);
            if (!is_array($tagsArray)) continue;

            foreach ($tagsArray as $tagName) {
                $tagName = trim($tagName);
                if (empty($tagName)) continue;

                $slug = \Illuminate\Support\Str::slug($tagName);
                
                // Get or create tag
                $tagId = \Illuminate\Support\Facades\DB::table('tags')->where('slug', $slug)->value('id');
                if (!$tagId) {
                    $tagId = \Illuminate\Support\Facades\DB::table('tags')->insertGetId([
                        'name' => $tagName,
                        'slug' => $slug,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Attach to asset
                \Illuminate\Support\Facades\DB::table('asset_tag')->insertOrIgnore([
                    'asset_id' => $asset->id,
                    'tag_id' => $tagId,
                ]);
            }
        }

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('tags');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->json('tags')->nullable();
        });

        Schema::dropIfExists('asset_tag');
        Schema::dropIfExists('tags');
    }
};
