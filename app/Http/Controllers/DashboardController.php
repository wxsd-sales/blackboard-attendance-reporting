<?php

namespace App\Http\Controllers;

use App\Models\Blackboard\BlackboardUser;
use App\Models\Cisco\WebexMeeting;
use App\Models\Cisco\WebexUser;
use DateTimeInterface;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index()
    {
        return view('dashboard');
    }

    public function getBlackboardUserCourses()
    {
        return BlackboardUser::find(Auth::user()->blackboard_user_id)
            ->courses()
            ->where('role_id', '=', 'Instructor')
            ->orderBy('term_id')
            ->get()
            ->toArray();
    }

    public function getWebexMeetings(Request $request)
    {
        try {
            $request->validate([
                'from' => ['required'], //TODO
                'to' => ['required'],   //TODO
            ]);

            return WebexUser::find(Auth::user()->webex_user_id)
                ->meetings()
                ->where([
                    ['start', '>=', Carbon::createFromFormat(DateTimeInterface::ISO8601, $request->get('from'))],
                    ['end', '<=', Carbon::createFromFormat(DateTimeInterface::ISO8601, $request->get('to'))],
                ])
                ->get()
                ->toArray();
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['status' => 'error'], 400);
        }
    }

    public function getWebexScheduledMeetings(Request $request)
    {
        try {
            $request->validate([
                'from' => ['required'], //TODO
                'to' => ['required'],   //TODO
            ]);

            return WebexUser::find(Auth::user()->webex_user_id)
                ->scheduledMeetings()
                ->where([
                    ['start', '>=', Carbon::createFromFormat(DateTimeInterface::ISO8601, $request->get('from'))],
                    ['end', '<=', Carbon::createFromFormat(DateTimeInterface::ISO8601, $request->get('to'))],
                ])
                ->get()
                ->toArray();
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['status' => 'error'], 400);
        }
    }

    public function postAddMapping(Request $request)
    {
        try {
            $request->validate([
                'meetingId' => ['required', 'string', 'exists:webex_meetings,id'],
                'courseId' => ['required', 'string', 'exists:blackboard_courses,course_id'],
            ]);

            $course = BlackboardUser::find(Auth::user()->blackboard_user_id)
                ->courses()
                ->where([
                    ['role_id', '=', 'Instructor'],
                    ['course_id', '=', $request->get('courseId')],
                ])
                ->firstOrFail();
            $meeting = WebexUser::find(Auth::user()->webex_user_id)
                ->meetings()
                ->whereNull('course_id')
                ->where('id', '=', $request->get('meetingId'))
                ->firstOrFail();
            $meeting->course_id = $course->course_id;

            return $meeting->save();
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['status' => 'error'], 400);
        }
    }
}
