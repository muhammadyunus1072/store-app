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
        Schema::create('approval_users', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_approval_users', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_users');
        Schema::dropIfExists('_history_approval_users');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('approval_id', 'approval_users_approval_id_idx');
            $table->index('user_id', 'approval_users_user_id_idx');
        }

        $table->unsignedBigInteger("approval_id")->comment('Approval ID');
        $table->unsignedBigInteger("user_id")->comment('User ID');
        $table->integer('position')->comment('Urutan Persetujuan');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
