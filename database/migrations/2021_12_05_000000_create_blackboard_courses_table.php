<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBlackboardCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blackboard_courses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('course_id')->unique();
            $table->string('name')->nullable();
            $table->string('term_id')->nullable();
            $table->json('availability')->nullable();
            $table->timestamp('synced_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('blackboard_courses');
    }
}
