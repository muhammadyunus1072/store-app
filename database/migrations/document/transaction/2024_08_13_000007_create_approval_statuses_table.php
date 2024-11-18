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
        Schema::create('approval_statuses', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_approval_statuses', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_statuses');
        Schema::dropIfExists('_history_approval_statuses');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('approval_id', 'approval_statuses_approval_id_idx');
            $table->index('approval_user_id', 'approval_statuses_approval_user_id_idx');
            $table->index('user_id', 'approval_statuses_user_id_idx');
            $table->index('status_approval_id', 'approval_statuses_status_approval_id_idx');
        }

        $table->unsignedBigInteger("approval_id")->comment('Approval ID');
        $table->unsignedBigInteger("approval_user_id")->comment('Approval User ID');
        $table->unsignedBigInteger("user_id")->comment('User ID');

        $table->unsignedBigInteger("status_approval_id")->comment('Status Approval ID');
        $table->string('status_approval_name')->nullable();
        $table->string('status_approval_color')->nullable();
        $table->string('status_approval_text_color')->nullable();
        $table->boolean('status_approval_is_trigger_done')->nullable();
        $table->boolean('status_approval_is_trigger_cancel')->nullable();

        $table->text('note')->nullable()->comment('Catatan');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
