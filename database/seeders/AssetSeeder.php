<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = \App\Models\User::firstOrCreate(
            ['email' => 'admin@damstudio.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password'), 'role' => 'admin']
        );

        $tag1 = \App\Models\Tag::firstOrCreate(['slug' => '3d-model'], ['name' => '3D Model']);
        $tag2 = \App\Models\Tag::firstOrCreate(['slug' => 'featured'], ['name' => 'Featured']);

        $asset = \App\Models\Asset::create([
            'user_id' => $user->id,
            'title' => 'Dummy Featured Asset',
            'slug' => 'dummy-featured-asset',
            'visibility' => \App\Enums\AssetVisibility::PUBLIC->value,
            'status' => \App\Enums\AssetStatus::COMPLETED->value,
            'is_staff_pick' => true,
            'master_zip_path' => 'dummy.zip',
            'viewer_glb_path' => null, // Just UI placeholder test since no actual GLB file exists
        ]);

        $asset->tags()->sync([$tag1->id, $tag2->id]);
        
        \App\Models\AssetVersion::create([
            'asset_id' => $asset->id,
            'version_number' => 1,
            'master_zip_path' => 'dummy.zip',
            'status' => \App\Enums\AssetStatus::COMPLETED->value,
        ]);
    }
}
