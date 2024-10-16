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
        Schema::create('approvals', function (Blueprint $table) {
            $this->scheme($table, false);
        });

        Schema::create('_history_approvals', function (Blueprint $table) {
            $this->scheme($table, true);
        });
    }

    public function down()
    {
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('_history_approvals');
    }

    private function scheme(Blueprint $table, $is_history = false)
    {
        $table->id();

        if ($is_history) {
            $table->bigInteger('obj_id')->unsigned();
        } else {
            $table->index('number', 'approvals_number_idx');
            $table->index('done_at', 'approvals_done_at_idx');
            $table->index('cancel_at', 'approvals_cancel_at_idx');
            $table->index('remarks_id', 'approvals_remarks_id_idx');
            $table->index('remarks_type', 'approvals_remarks_type_idx');

            $table->index('approval_config_id', 'approvals_approval_config_id_idx');
        }

        $table->string('number')->nullable()->comment('Nomor');
        $table->text('note')->nullable()->comment('Catatan');
        $table->boolean('is_sequentially')->default(false)->comment('Penentu harus berurutan');

        $table->timestamp('done_at')->nullable()->comment('Timestamp Done');
        $table->unsignedBigInteger('done_by_id')->nullable()->comment('User Done');

        $table->timestamp('cancel_at')->nullable()->comment('Timestamp Cancel');
        $table->unsignedBigInteger('cancel_by_id')->nullable()->comment('User Cancel');
        $table->text('cancel_reason')->nullable()->comment('Cancel Reason');

        // Source
        $table->unsignedBigInteger("remarks_id")->nullable()->comment('FK Polimorfik');
        $table->string('remarks_type')->nullable()->comment('Jenis Polimorfik');

        // Auto Create By Config
        $table->unsignedBigInteger("approval_config_id")->nullable()->comment('FK Approval Config');

        $table->bigInteger("created_by")->unsigned()->nullable();
        $table->bigInteger("updated_by")->unsigned()->nullable();
        $table->bigInteger("deleted_by")->unsigned()->nullable()->default(null);
        $table->softDeletes();
        $table->timestamps();
    }
};
