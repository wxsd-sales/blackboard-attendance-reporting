<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebexMeetingParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webex_meeting_participants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->boolean('host');
            $table->boolean('co_host');
            $table->boolean('space_moderator');
            $table->string('email');
            $table->string('display_name');
            $table->boolean('invitee');
            $table->string('state');
            $table->string('joined_time');
            $table->string('left_time');
            $table->string('meeting_id');
            $table->string('host_email');
            $table->json('devices');
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
        Schema::dropIfExists('webex_meeting_participants');
    }
}
