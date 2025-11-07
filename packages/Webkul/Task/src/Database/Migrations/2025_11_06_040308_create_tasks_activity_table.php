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
        if (Schema::hasTable('task_activities')) {
            return;
        }

        Schema::create('task_activities', function (Blueprint $table) {
            $table->increments('id');

            // Liên kết với task
            $table->integer('task_id')->unsigned();
            $table->foreign('task_id')
                ->references('id')->on('tasks')
                ->onDelete('cascade');

            // Liên kết với activity
            $table->integer('activity_id')->unsigned();
            $table->foreign('activity_id')
                ->references('id')->on('activities')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_activities');
    }
};
