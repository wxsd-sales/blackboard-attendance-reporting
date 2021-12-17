<?php

namespace App\Jobs;

use App\Models\Cisco\WebexScheduledMeeting;
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

class RetrieveWebexScheduledMeetings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Carbon
     */
    private $from;

    /**
     * @var Carbon
     */
    private $to;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, Carbon $from, Carbon $to)
    {
        $this->user = $user;
        $this->from = $from;
        $this->to = $to;
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
        $webex_api_resource = "/meetings?" . http_build_query([
                'meetingType' => 'scheduledMeeting',
                'from' => $this->from->toIso8601String(),
                'to' => $this->to->toIso8601String(),
            ]);
        $webex_client = Http::baseUrl($webex_api_base_url)->withToken($webex_access_token);

        do {
            $response = $webex_client->get($webex_api_resource);
            if ($response->successful()) {
                $date = $response->header('date');
                $link = $response->header('link');
                $timestamp = Carbon::createFromFormat(DateTimeInterface::RFC7231, $date);
                $scheduled_meeting_items = $response['items'] ?? [];

                $meetings = array_map(function ($scheduled_meeting_item) use ($timestamp) {
                    return [
                        'id' => $scheduled_meeting_item['id'],
                        'meeting_series_id' => $scheduled_meeting_item['meetingSeriesId'],
                        'title' => $scheduled_meeting_item['title'],
                        'state' => $scheduled_meeting_item['state'],
                        'is_modified' => $scheduled_meeting_item['isModified'],
                        'start' => Carbon::createFromFormat(
                            DateTimeInterface::ISO8601,
                            $scheduled_meeting_item['start']
                        ),
                        'end' => Carbon::createFromFormat(
                            DateTimeInterface::ISO8601,
                            $scheduled_meeting_item['end']
                        ),
                        'host_user_id' => $scheduled_meeting_item['hostUserId'],
                        'host_email' => $scheduled_meeting_item['hostEmail'],
                        'web_link' => $scheduled_meeting_item['webLink'],
                        'synced_at' => $timestamp
                    ];
                }, $scheduled_meeting_items);

                WebexScheduledMeeting::upsert($meetings, ['id'], [
                    'meeting_series_id',
                    'title',
                    'state',
                    'is_modified',
                    'start',
                    'end',
                    'host_user_id',
                    'host_email',
                    'web_link',
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

//TODO: Deleted meetings.
