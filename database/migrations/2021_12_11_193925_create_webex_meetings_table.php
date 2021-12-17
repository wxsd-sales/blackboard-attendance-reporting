<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebexMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webex_meetings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('meeting_series_id');
            $table->string('scheduled_meeting_id');
            $table->string('title');
            $table->string('state');
            $table->timestamp('start');
            $table->timestamp('end');
            $table->string('host_user_id');
            $table->string('host_email');
            $table->string('web_link');
            $table->string('course_id')->nullable();
            $table->timestamp('synced_at');
            $table->timestamps();

            $table->foreign('host_user_id')->references('id')->on('webex_users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('course_id')->references('course_id')->on('blackboard_courses')
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
        Schema::dropIfExists('webex_meetings');
    }
}
