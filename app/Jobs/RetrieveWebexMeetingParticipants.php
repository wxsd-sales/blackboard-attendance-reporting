<?php

namespace App\Jobs;

use App\Models\Cisco\WebexMeeting;
use App\Models\Cisco\WebexMeetingParticipant;
use App\Models\OAuth;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RetrieveWebexMeetingParticipants implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;

    /**
     * @var WebexMeeting
     */
    private $webex_meeting;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, WebexMeeting $webex_meeting)
    {
        $this->user = $user;
        $this->webex_meeting = $webex_meeting;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $webex_access_token = OAuth::find($this->user->webex_user_id)->access_token;
        $webex_api_base_url = env('WEBEX_API_URL');
        $webex_api_resource = "/meetingParticipants?" . http_build_query([
                'meetingId' => $this->webex_meeting->id,
            ]);
        $webex_client = Http::baseUrl($webex_api_base_url)->withToken($webex_access_token);

        do {
            $response = $webex_client->get($webex_api_resource);
            if ($response->successful()) {
                $date = $response->header('date');
                $link = $response->header('link');
                $timestamp = Carbon::createFromFormat(DateTimeInterface::RFC7231, $date);
                $meeting_participant_items = $response['items'] ?? [];

                $meeting_participants = array_map(function ($meeting_participant_item) use ($timestamp) {
                    return [
                        'id' => $meeting_participant_item['id'],
                        'host' => $meeting_participant_item['host'],
                        'co_host' => $meeting_participant_item['coHost'],
                        'space_moderator' => $meeting_participant_item['spaceModerator'],
                        'email' => $meeting_participant_item['email'],
                        'display_name' => $meeting_participant_item['displayName'],
                        'invitee' => $meeting_participant_item['invitee'],
                        'state' => $meeting_participant_item['state'],
                        'joined_time' => $meeting_participant_item['joinedTime'],
                        'left_time' => $meeting_participant_item['leftTime'],
                        'meeting_id' => $meeting_participant_item['meetingId'],
                        'host_email' => $meeting_participant_item['hostEmail'],
                        'devices' => json_encode($meeting_participant_item['devices']),
                        'synced_at' => $timestamp,
                    ];
                }, $meeting_participant_items);

                WebexMeetingParticipant::upsert($meeting_participants, ['id'], [
                    'host',
                    'co_host',
                    'space_moderator',
                    'email',
                    'display_name',
                    'invitee',
                    'state',
                    'joined_time',
                    'left_time',
                    'meeting_id',
                    'host_email',
                    'devices',
                    'synced_at',
                ]);

                if ($link) {
                    $webex_api_resource = Str::between($link, "<$webex_api_base_url", '>;');
                } else {
                    $webex_api_resource = null;
                }
            }
        } while ($response->successful() && $webex_api_resource);
    }
}
