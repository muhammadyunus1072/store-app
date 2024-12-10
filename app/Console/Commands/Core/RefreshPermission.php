<?php

namespace App\Console\Commands\Core;

use Illuminate\Console\Command;

class RefreshPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:refresh-permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call("db:seed", ['class' => 'Database\Seeders\Core\PermissionSeeder']);
    }
}
