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
        Schema::create('status_approvals', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_status_approvals', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('status_approvals');
        Schema::dropIfExists('_history_status_approvals');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('name', 'status_approvals_name_idx');
        }

        $table->string('name')->nullable();
        $table->string('color')->default("#3d98fc");
        $table->string('text_color')->default("#ffffff");
        $table->boolean('is_trigger_done')->default(false)->comment('Menyebabkan Selesainya Persetujuan');
        $table->boolean('is_trigger_cancel')->default(false)->comment('Menyebabkan Batalnya Persetujuan');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
