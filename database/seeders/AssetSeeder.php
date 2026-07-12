<?php

namespace Database\Seeders;

use App\Enums\AssetStatus;
use App\Enums\AssetVisibility;
use App\Models\Asset;
use App\Models\AssetVersion;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@damstudio.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $tag1 = Tag::firstOrCreate(['slug' => '3d-model'], ['name' => '3D Model']);
        $tag2 = Tag::firstOrCreate(['slug' => 'featured'], ['name' => 'Featured']);

        $asset = Asset::create([
            'user_id' => $user->id,
            'title' => 'Dummy Featured Asset',
            'slug' => 'dummy-featured-asset',
            'original_extension' => 'blend',
            'visibility' => AssetVisibility::PUBLIC->value,
            'status' => AssetStatus::COMPLETED->value,
            'is_staff_pick' => true,
            'master_zip_path' => 'dummy.zip',
<<<<<<< Updated upstream
            'viewer_glb_path' => null, // Just UI placeholder test since no actual GLB file exists
            'original_extension' => 'blend',
=======
            // 'viewer_glb_path' => null, // Just UI placeholder test since no actual GLB file exists
>>>>>>> Stashed changes
        ]);

        $asset->tags()->sync([$tag1->id, $tag2->id]);

        AssetVersion::create([
            'asset_id' => $asset->id,
            'version_number' => 1,
            'master_zip_path' => 'dummy.zip',
            'status' => AssetStatus::COMPLETED->value,
        ]);
    }
}
