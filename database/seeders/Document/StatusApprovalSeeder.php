<?php

namespace Database\Seeders\Document;

use Illuminate\Database\Seeder;
use App\Models\Document\Master\StatusApproval;

class StatusApprovalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StatusApproval::create([
            'name' => 'Setuju',
            'color' => '#3d98fc',
            'text_color' => '#ffffff',
            'is_trigger_done' => false,
            'is_trigger_cancel' => false,
        ]);

        StatusApproval::create([
            'name' => 'Selesai',
            'color' => '#4cc983',
            'text_color' => '#ffffff',
            'is_trigger_done' => true,
            'is_trigger_cancel' => false,
        ]);

        StatusApproval::create([
            'name' => 'Batal',
            'color' => '#f4406e',
            'text_color' => '#ffffff',
            'is_trigger_done' => false,
            'is_trigger_cancel' => true,
        ]);
    }
}
