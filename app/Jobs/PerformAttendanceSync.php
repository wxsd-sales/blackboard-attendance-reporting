<?php

namespace App\Jobs;

use App\Models\Blackboard\BlackboardCourse;
use App\Models\Blackboard\BlackboardCourseMeeting;
use App\Models\Cisco\WebexMeeting;
use App\Models\OAuth;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class PerformAttendanceSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var User
     */
    private $user;

    /**
     * @var WebexMeeting
     */
    private $meeting;

    /**
     * @var BlackboardCourse
     */
    private $course;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user, WebexMeeting $meeting, BlackboardCourse $course)
    {
        $this->user = $user;
        $this->meeting = $meeting;
        $this->course = $course;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws RequestException
     */
    public function handle()
    {
        $blackboard_access_token = OAuth::find($this->user->blackboard_user_id)->access_token;
        $blackboard_api_base_url = env('BLACKBOARD_API_URL');
        $blackboard_client = Http::baseUrl($blackboard_api_base_url)->withToken($blackboard_access_token);

        $webex_meeting_participants = $this->meeting->participants()
            ->get(['email']);
        $blackboard_course_students = $this->course->users()
            ->where('role_id', '=', 'Student')
            ->get(['blackboard_users.id', 'email']);

        $blackboard_attendance_records = [];

        foreach ($blackboard_course_students as $blackboard_course_student) {
            if ($webex_meeting_participants->contains('email', $blackboard_course_student['email'])) {
                array_push($blackboard_attendance_records, [
                    'userId' => $blackboard_course_student->id,
                    'status' => "Present",
                ]);
            } else {
                array_push($blackboard_attendance_records, [
                    'userId' => $blackboard_course_student->id,
                    'status' => "Absent",
                ]);
            }
        }

        $blackboard_course_meeting = $this->postMeeting($blackboard_client);
        $this->postAttendance($blackboard_client, $blackboard_course_meeting, $blackboard_attendance_records);

        $blackboard_course_meeting->webex_meeting_id = $this->meeting->id;
        $blackboard_course_meeting->save();
        $this->meeting->course_id = $this->course->course_id;
        $this->meeting->save();
    }

    /**
     * @param PendingRequest $blackboard_client
     *
     * @return BlackboardCourseMeeting|Model
     * @throws RequestException
     */
    public function postMeeting(PendingRequest $blackboard_client)
    {
        $blackboard_api_resource = "/v1/courses/{$this->course->id}/meetings";
        $response = $blackboard_client->post($blackboard_api_resource, [
            'courseId' => $this->course->id,
            'title' => $this->meeting->title,
            'description' => env('APP_NAME') . " " . now()->timestamp,
            'start' => $this->meeting->start->toIso8601ZuluString(),
            'end' => $this->meeting->end->toIso8601ZuluString(),
            'externalLink' => $this->meeting->web_link,
        ]);

        $date = $response->header('date');
        $timestamp = Carbon::createFromFormat(DateTimeInterface::RFC7231, $date);

        $response->throw();

        return BlackboardCourseMeeting::updateOrCreate(['id' => $response['id']], [
            'course_id' => $response['courseId'],
            'webex_meeting_id' => $this->meeting->id,
            'synced_at' => $timestamp,
        ]);
    }

    private function postAttendance(PendingRequest          $blackboard_client,
                                    BlackboardCourseMeeting $blackboard_course_meeting,
                                    array                   $blackboard_attendance_records)
    {
        $blackboard_api_resource = "/v1/courses/{$this->course->id}/meetings/$blackboard_course_meeting->id/users";

        foreach ($blackboard_attendance_records as $blackboard_attendance_record) {
            $blackboard_client->post($blackboard_api_resource, [
                'meetingId' => $blackboard_course_meeting->id,
                'userId' => $blackboard_attendance_record['userId'],
                'status' => $blackboard_attendance_record['status'],
            ]);
        }
    }
}
