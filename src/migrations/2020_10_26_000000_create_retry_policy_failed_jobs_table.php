<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRetryPolicyFailedJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retry_policy_failed_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('requeue_count')->default(0);
            $table->unsignedInteger('requeue_delay')->nullable();
            $table->unsignedInteger('max_requeue')->nullable();
            $table->string('connection');
            $table->string('queue');
            $table->longText('exception');
            $table->longText('payload');
            $table->timestamp('failed_at')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('retry_policy_failed_jobs');
    }
}
