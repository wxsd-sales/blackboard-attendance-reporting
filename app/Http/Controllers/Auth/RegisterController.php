<?php

namespace App\Http\Controllers\Auth;

use App\Events\SetupDone;
use App\Http\Controllers\Controller;
use App\Models\Blackboard\BlackboardUser;
use App\Models\Cisco\WebexUser;
use App\Models\OAuth;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    public static $blackboardScopes = ['read', 'write', 'offline'];
    public static $webexScopes = [
        'spark:people_read', 'meeting:schedules_read', 'meeting:participants_read', 'spark:kms'
    ];
    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * @return Application|Factory|View
     */
    public function showRegistrationForm()
    {
        return view('auth.setup', ['url' => [
            'setup' => route('setup', [], false),
            'login' => route('login', [], false),
            'reset' => route('reset', [], false),
            'email' => route('auth.email', [], false),
            'blackboard' => route('auth.blackboard', [], false),
            'webex' => route('auth.webex', [], false),
        ]]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function emailOauthRedirect(Request $request)
    {
        abort_if($request->session()->exists('email'), 403);

        $email_validation = $this->emailValidator($request->all());
        if ($email_validation->fails())
            return redirect()
                ->route('setup')
                ->withErrors($email_validation->errors())
                ->withInput();

        session(['email' => $email_validation->validated()['email']]);

        return redirect()->route('setup');
    }

    /**
     * @param array $input
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function emailValidator(array $input)
    {
        return Validator::make($input, ['email' => [
            'required', 'string', 'email', 'max:255'
        ]])
            ->stopOnFirstFailure();
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function blackboardOauthRedirect(Request $request)
    {
        abort_if($request->session()->missing('email'), 403);
        abort_if($request->session()->exists('blackboard'), 403);

        return Socialite::driver('blackboard')
            ->setScopes(self::$blackboardScopes)
            ->redirect();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function webexOauthRedirect(Request $request)
    {
        abort_if($request->session()->missing('email'), 403);
        abort_if($request->session()->exists('webex'), 403);

        return Socialite::driver('webex')
            ->setScopes(self::$webexScopes)
            ->redirect();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function blackboardOauthCallback(Request $request)
    {
        abort_if($request->session()->missing('email'), 403);
        abort_if($request->session()->exists('blackboard'), 403);

        $timestamp = now()->timestamp;
        $email = $request->session()->get('email');
        $blackboard_provider = Socialite::driver('blackboard');

        $blackboard_callback_validation = $this->oauthCallbackValidator($email, $blackboard_provider);
        if ($blackboard_callback_validation->fails()) {
            $message = "Could not retrieve Blackboard OAuth access code as $email";
            Log::info($message);

            return redirect()
                ->route('setup')
                ->withErrors(['blackboard' => $message]);
        }

        $validated_blackboard_callback = $blackboard_callback_validation->validated();

        $blackboard_oauth_identity = [
            'access_token' => encrypt($validated_blackboard_callback['token']),
            'email' => $validated_blackboard_callback['email'],
            'expires_at' => $validated_blackboard_callback['expiresIn'] + $timestamp,
            'id' => $validated_blackboard_callback['id'],
            'refresh_token' => encrypt($validated_blackboard_callback['refreshToken']),
            'timestamp' => now()->timestamp
        ];
        session(['blackboard' => $blackboard_oauth_identity]);

        return redirect()->route('setup');
    }

    /**
     * @param string $email
     * @param Provider $provider
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function oauthCallbackValidator(string $email, Provider $provider)
    {
        $identity = [];

        try {
            $identity = (array)$provider->user();
        } catch (Exception $e) {
            Log::error($e);
        }

        return Validator::make($identity, [
            'email' => ['required', 'string', 'email', 'max:255', "in:$email"],
            'expiresIn' => ['required', 'integer'],
            'id' => ['required'],
            'refreshToken' => ['required'],
            'token' => ['required']
        ])
            ->stopOnFirstFailure();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     *
     * @throws ValidationException
     */
    public function webexOauthCallback(Request $request)
    {
        abort_if($request->session()->missing('email'), 403);
        abort_if($request->session()->exists('webex'), 403);

        $timestamp = now()->timestamp;
        $email = $request->session()->get('email');
        $webex_provider = Socialite::driver('webex');

        $webex_callback_validation = $this->oauthCallbackValidator($email, $webex_provider);
        if ($webex_callback_validation->fails()) {
            $message = "Could not retrieve WebexChannel OAuth access code as $email";
            Log::info($message);

            return redirect()
                ->route('setup')
                ->withErrors(['webex' => $message]);
        }

        $validated_webex_callback = $webex_callback_validation->validated();
        $webex_oauth_identity = [
            'access_token' => encrypt($validated_webex_callback['token']),
            'email' => $validated_webex_callback['email'],
            'expires_at' => $validated_webex_callback['expiresIn'] + $timestamp,
            'id' => $validated_webex_callback['id'],
            'refresh_token' => encrypt($validated_webex_callback['refreshToken']),
            'timestamp' => now()->timestamp
        ];
        session(['webex' => $webex_oauth_identity]);

        return redirect()->route('setup');
    }

    /**
     * @param Request $request
     * @return Application|JsonResponse|Redirector|RedirectResponse
     *
     * @throws Throwable
     */
    public function register(Request $request)
    {
        abort_if($request->session()->missing(['email', 'blackboard', 'webex']), 403);

        $session_data = $request->session()->only(['email', 'blackboard', 'webex']);
        $email = $session_data['email'];
        $blackboard_oauth_identity = $session_data['blackboard'];
        $webex_oauth_identity = $session_data['webex'];

        abort_if((function ($email, $blackboard_oauth_identity, $webex_oauth_identity) {

            if ($email !== $blackboard_oauth_identity['email'] || $email !== $webex_oauth_identity['email']) {
                return true;
            }

            if ($blackboard_oauth_identity['expires_at'] <= now()->timestamp + 1000 ||
                $webex_oauth_identity['expires_at'] <= now()->timestamp + 1000) {
                return true;
            }

            return false;
        })($email, $blackboard_oauth_identity, $webex_oauth_identity), 403);

        event(new Registered($user = $this->upsert($email, $blackboard_oauth_identity, $webex_oauth_identity)));

        $this->guard()->login($user);

        $this->registered($request, $user);

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect($this->redirectPath());
    }

    /**
     * @param $email
     * @param $blackboard_identity
     * @param $webex_identity
     * @return User
     *
     * @throws Throwable
     */
    protected function upsert($email, $blackboard_identity, $webex_identity)
    {
        function upsertBlackboardUser(array $blackboard_identity)
        {
            return BlackboardUser::updateOrCreate(['id' => $blackboard_identity['id']], [
                'email' => $blackboard_identity['email'],
                'name' => $blackboard_identity['name'] ?? null,
                'synced_at' => $blackboard_identity['timestamp']
            ]);
        }

        function upsertWebexUser(array $webex_identity)
        {
            return WebexUser::updateOrCreate(['id' => $webex_identity['id']], [
                'email' => $webex_identity['email'],
                'name' => $webex_identity['name'] ?? null,
                'synced_at' => $webex_identity['timestamp']
            ]);
        }

        function upsertUser($email, BlackboardUser $blackboard_user, WebexUser $webex_user)
        {
            return User::updateOrCreate(['email' => $email], [
                'blackboard_user_id' => $blackboard_user->id,
                'webex_user_id' => $webex_user->id
            ]);
        }

        function upsertOauthIdentity(string $driver, array $identity, User $user)
        {
            return OAuth::updateOrCreate([
                'provider' => $driver,
                'id' => $identity['id']
            ], [
                'access_token' => $identity["access_token"],
                'email' => $identity["email"],
                'expires_at' => $identity["expires_at"],
                'refresh_token' => $identity["refresh_token"],
                'user_id' => $user->id
            ]);
        }

        return DB::transaction(function () use ($email, $blackboard_identity, $webex_identity) {
            $blackboard_user = upsertBlackboardUser($blackboard_identity);
            $webex_user = upsertWebexUser($webex_identity);
            $user = upsertUser($email, $blackboard_user, $webex_user);
            upsertOauthIdentity('blackboard', $blackboard_identity, $user);
            upsertOauthIdentity('webex', $webex_identity, $user);

            return $user;
        });
    }

    protected function registered(Request $request, $user)
    {
        event(new SetupDone($request, $user));
    }
}

// TODO: Refactor class.
