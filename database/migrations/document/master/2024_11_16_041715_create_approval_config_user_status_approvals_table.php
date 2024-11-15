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
        Schema::create('approval_config_user_status_approvals', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_approval_config_user_status_approvals', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_config_user_status_approvals');
        Schema::dropIfExists('_history_approval_config_user_status_approvals');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('approval_config_user_id', 'acusa_approval_config_user_id_idx');
            $table->index('status_approval_id', 'acusa_status_approval_id_idx');
        }

        $table->unsignedBigInteger("approval_config_user_id")->comment('Approval Config User ID');
        $table->unsignedBigInteger("status_approval_id")->comment('Status Approval ID');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
