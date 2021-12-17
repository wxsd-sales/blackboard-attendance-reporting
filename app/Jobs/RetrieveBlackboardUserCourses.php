<?php

namespace App\Jobs;

use App\Models\Blackboard\BlackboardCourse;
use App\Models\Blackboard\BlackboardCourseUser;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RetrieveBlackboardUserCourses implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;

    /**
     * @var int
     */
    private $limit;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, int $limit = 10)
    {
        $this->user = $user;
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
        $blackboard_api_resource = "/v1/users/{$this->user->blackboard_user_id}/courses?" . http_build_query([
                'fields' => 'id,course.id,course.courseId,course.name,course.termId,course.availability,courseRoleId',
                'limit' => $this->limit,
                'sort' => 'created(desc)',
            ]);
        $blackboard_client = Http::baseUrl($blackboard_api_base_url)->withToken($blackboard_access_token);

        do {
            $response = $blackboard_client->get($blackboard_api_resource);
            if ($response->successful()) {
                $date = $response->header('date');
                $link = Arr::get($response, 'paging.nextPage');
                $timestamp = Carbon::createFromFormat(DateTimeInterface::RFC7231, $date);
                $results = $response['results'];

                DB::transaction(function () use ($timestamp, $results) {
                    $this->upsertBlackboardCourses($timestamp, $results);
                    $this->upsertBlackboardCourseUsers($timestamp, $results);
                });

                if ($link) {
                    $blackboard_api_resource = Str::after($link, '/learn/api/public');
                } else {
                    $blackboard_api_resource = null;
                }
            }
        } while ($response->successful() && $blackboard_api_resource);
    }

    /**
     * @param $timestamp
     * @param $results
     *
     * @return int
     */
    protected function upsertBlackboardCourses($timestamp, $results): int
    {
        $rows = array_map(function ($result) use ($timestamp) {
            return [
                'id' => $result['course']['id'],
                'course_id' => $result['course']['courseId'],
                'name' => $result['course']['name'] ?? null,
                'term_id' => $result['course']['termId'] ?? null,
                'availability' => json_encode($result['course']['availability']),
                'synced_at' => $timestamp
            ];
        }, $results);

        return BlackboardCourse::upsert($rows, ['id'], [
            'course_id', 'name', 'term_id', 'availability', 'synced_at'
        ]);
    }

    /**
     * @param $timestamp
     * @param $results
     * @return int
     */
    protected function upsertBlackboardCourseUsers($timestamp, $results): int
    {
        $rows = array_map(function ($result) use ($timestamp) {
            return [
                'id' => $result['id'],
                'blackboard_course_id' => $result['course']['id'],
                'blackboard_user_id' => $this->user->blackboard_user_id,
                'role_id' => $result['courseRoleId'],
                'synced_at' => $timestamp
            ];
        }, $results);

        return BlackboardCourseUser::upsert($rows, ['id'], [
            'blackboard_course_id', 'blackboard_user_id', 'role_id', 'synced_at'
        ]);
    }
}

// TODO: Handle deleted courses.
