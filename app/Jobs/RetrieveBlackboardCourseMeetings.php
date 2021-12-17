<?php

namespace App\Jobs;

use App\Models\Blackboard\BlackboardCourse;
use App\Models\Blackboard\BlackboardCourseMeeting;
use App\Models\OAuth;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RetrieveBlackboardCourseMeetings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;

    /**
     * @var BlackboardCourse
     */
    private $course;

    /**
     * @var int
     */
    private $limit;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, BlackboardCourse $course, int $limit = 100)
    {
        $this->user = $user;
        $this->course = $course;
        $this->limit = $limit;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $blackboard_access_token = OAuth::find($this->user->blackboard_user_id)->access_token;
        $blackboard_api_base_url = env('BLACKBOARD_API_URL');
        $blackboard_api_resource = "/v1/courses/{$this->course->id}/meetings?" . http_build_query([
                'fields' => 'id,courseId,externalLink',
                'limit' => $this->limit,
                'sort' => 'description',
            ]);
        $blackboard_client = Http::baseUrl($blackboard_api_base_url)->withToken($blackboard_access_token);

        do {
            $response = $blackboard_client->get($blackboard_api_resource);
            if ($response->successful()) {
                $date = $response->header('date');
                $link = Arr::get($response, 'paging.nextPage');
                $timestamp = Carbon::createFromFormat(DateTimeInterface::RFC7231, $date);
                $results = $response['results'];

                $course_meetings = array_map(function ($result) use ($timestamp) {
                    return [
                        'id' => $result['id'],
                        'course_id' => $result['courseId'],
                        'external_link' => $result['externalLink'],
                        'synced_at' => $timestamp
                    ];
                }, $results);

                BlackboardCourseMeeting::upsert($course_meetings, ['id'], ['course_id', 'external_link', 'synced_at']);

                if ($link) {
                    $blackboard_api_resource = Str::after($link, '/learn/api/public');
                } else {
                    $blackboard_api_resource = null;
                }
            }
        } while ($response->successful() && $blackboard_api_resource);
    }
}
