<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlackboardCourseBlackboardUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blackboard_course_blackboard_user', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('blackboard_course_id');
            $table->string('blackboard_user_id');
            $table->string('role_id');
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->foreign('blackboard_course_id')->references('id')->on('blackboard_courses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('blackboard_user_id')->references('id')->on('blackboard_users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blackboard_course_blackboard_user');
    }
}
