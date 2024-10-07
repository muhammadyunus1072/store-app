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
        ]);
        StatusApproval::create([
            'name' => 'Batal',
        ]);
    }
}
