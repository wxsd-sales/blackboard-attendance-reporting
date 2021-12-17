<?php

namespace App\Http\Controllers;

use App\Jobs\PerformAttendanceSync;
use App\Jobs\RefreshBlackboardTokens;
use App\Jobs\RefreshWebexTokens;
use App\Jobs\RetrieveBlackboardCourseUsers;
use App\Jobs\RetrieveBlackboardUserCourses;
use App\Jobs\RetrieveWebexMeetingParticipants;
use App\Jobs\RetrieveWebexMeetings;
use App\Jobs\RetrieveWebexScheduledMeetings;
use App\Models\Blackboard\BlackboardUser;
use App\Models\Cisco\WebexUser;
use DateTimeInterface;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class JobsController extends Controller
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

    public function refreshBlackboardTokens()
    {
        Log::info('[JobsController] refreshBlackboardToken');
        try {
            RefreshBlackboardTokens::dispatchSync();
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['status' => 'error'], 400);
        }

        return response()->json(['status' => 'success']);
    }

    public function refreshWebexTokens()
    {
        Log::info('[JobsController] refreshWebexToken');
        try {
            RefreshWebexTokens::dispatchSync();
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['status' => 'error'], 400);
        }

        return response()->json(['status' => 'success']);
    }

    public function retrieveBlackboardUserCourses()
    {
        Log::info('[JobsController] retrieveBlackboardUserCourses');
        try {
            RetrieveBlackboardUserCourses::dispatchSync(Auth::user());
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['status' => 'error'], 400);
        }

        return response()->json(['status' => 'success']);
    }

    public function retrieveBlackboardCourseUsers(Request $request)
    {
        Log::info('[JobsController] retrieveBlackboardCourseUsers');
        try {
            $request->validate([
                'courseId' => ['required'], //TODO
            ]);
            RetrieveBlackboardCourseUsers::dispatchSync(
                Auth::user(),
                BlackboardUser::find(Auth::user()->blackboard_user_id)
                    ->courses()
                    ->where('course_id', $request->get('courseId'))
                    ->first()
        );
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['status' => 'error'], 400);
        }

        return response()->json(['status' => 'success']);
    }

    public function retrieveWebexMeetings(Request $request)
    {
        Log::info('[JobsController] retrieveWebexMeetings');
        try {
            $request->validate([
                'from' => ['required'], //TODO
                'to' => ['required'],   //TODO
            ]);

            RetrieveWebexMeetings::dispatchSync(
                Auth::user(),
                Carbon::createFromFormat(DateTimeInterface::ISO8601, $request->get('from')),
                Carbon::createFromFormat(DateTimeInterface::ISO8601, $request->get('to'))
            );
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['status' => 'error'], 400);
        }

        return response()->json(['status' => 'success']);
    }

    public function retrieveWebexScheduledMeetings(Request $request)
    {
        Log::info('[JobsController] retrieveWebexScheduledMeetings');
        try {
            $request->validate([
                'from' => ['required'], //TODO
                'to' => ['required'],   //TODO
            ]);

            RetrieveWebexScheduledMeetings::dispatchSync(
                Auth::user(),
                Carbon::createFromFormat(DateTimeInterface::ISO8601, $request->get('from')),
                Carbon::createFromFormat(DateTimeInterface::ISO8601, $request->get('to'))
            );
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['status' => 'error'], 400);
        }

        return response()->json(['status' => 'success']);
    }

    public function retrieveWebexMeetingParticipants(Request $request)
    {
        Log::info('[JobsController] retrieveWebexMeetingParticipants');
        try {
            $request->validate([
                'meetingId' => ['required'], //TODO
            ]);

            RetrieveWebexMeetingParticipants::dispatchSync(
                Auth::user(),
                WebexUser::find(Auth::user()->webex_user_id)
                    ->meetings()
                    ->find($request->get('meetingId'))
            );
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['status' => 'error'], 400);
        }

        return response()->json(['status' => 'success']);
    }

    public function performAttendanceSync(Request $request)
    {
        Log::info('[JobsController] performAttendanceSync');
        try {
            $request->validate([
                'meetingId' => ['required'], //TODO
                'courseId' => ['required'], //TODO
            ]);

            PerformAttendanceSync::dispatchSync(
                Auth::user(),
                WebexUser::find(Auth::user()->webex_user_id)
                    ->meetings()
                    ->find($request->get('meetingId')),
                BlackboardUser::find(Auth::user()->blackboard_user_id)
                    ->courses()
                    ->where('course_id', $request->get('courseId'))
                    ->first()
            );
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['status' => 'error'], 400);
        }

        return response()->json(['status' => 'success']);
    }
}
