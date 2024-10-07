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
        Schema::create('approval_histories', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_approval_histories', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_histories');
        Schema::dropIfExists('_history_approval_histories');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('approval_id', 'approval_histories_approval_id_idx');
            $table->index('user_id', 'approval_histories_user_id_idx');
            $table->index('status_id', 'approval_histories_status_id_idx');
        }

        $table->bigInteger("approval_id")->unsigned()->comment('Approval ID');
        $table->bigInteger("user_id")->unsigned()->comment('User ID');
        $table->bigInteger("status_id")->unsigned()->comment('StatusApproval ID');
        $table->double('position')->comment('Urutan Persetujuan');
        $table->text('note')->nullable()->comment('Catatan');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
