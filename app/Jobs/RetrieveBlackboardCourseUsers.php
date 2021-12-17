<?php

namespace App\Jobs;

use App\Models\Blackboard\BlackboardCourse;
use App\Models\Blackboard\BlackboardCourseUser;
use App\Models\Blackboard\BlackboardUser;
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

class RetrieveBlackboardCourseUsers implements ShouldQueue
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
        $blackboard_api_resource = "/v1/courses/{$this->course->id}/users?" . http_build_query([
                'fields' => 'id,courseRoleId,user.id,user.name,user.contact.email',
                'limit' => $this->limit,
                'sort' => 'modified(desc)',
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
                    $this->upsertBlackboardUsers($timestamp, $results);
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
    protected function upsertBlackboardUsers($timestamp, $results): int
    {
        $rows = array_map(function ($result) use ($timestamp) {
            return [
                'id' => $result['user']['id'],
                'email' => $result['user']['contact']['email'],
                'synced_at' => $timestamp
            ];
        }, $results);

        return BlackboardUser::upsert($rows, ['id'], ['email']);
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
                'blackboard_course_id' => $this->course->id,
                'blackboard_user_id' => $result['user']['id'],
                'role_id' => $result['courseRoleId'],
                'synced_at' => $timestamp
            ];
        }, $results);

        return BlackboardCourseUser::upsert($rows, ['id'], [
            'blackboard_course_id', 'blackboard_user_id', 'role_id', 'synced_at'
        ]);
    }
}

// TODO: Handle deleted course users.
