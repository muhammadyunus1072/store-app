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
        Schema::create('approval_config_users', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_approval_config_users', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_config_users');
        Schema::dropIfExists('_history_approval_config_users');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('approval_config_id', 'acu_approval_config_id_idx');
            $table->index('status_approval_id', 'acu_status_approval_id_idx');
            $table->index('user_id', 'acu_user_id_idx');
        }

        $table->unsignedBigInteger("approval_config_id")->comment('Approval Config ID');
        $table->unsignedBigInteger("user_id")->comment('User ID');
        $table->unsignedBigInteger("status_approval_id")->nullable()->comment('Status Approval ID');

        $table->double('position')->comment('Urutan Persetujuan');
        $table->boolean('is_trigger_done')->default(false)->comment('Penanda Selesai Persetujuan');
        $table->boolean('is_can_cancel')->default(false)->comment('Dapat Membatalkan');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
