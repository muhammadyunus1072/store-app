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
            'is_trigger_done' => false,
            'is_trigger_cancel' => false,
        ]);

        StatusApproval::create([
            'name' => 'Selesai',
            'is_trigger_done' => true,
            'is_trigger_cancel' => false,
        ]);

        StatusApproval::create([
            'name' => 'Batal',
            'is_trigger_done' => false,
            'is_trigger_cancel' => true,
        ]);
    }
}
