<?php

namespace App\Jobs;

use App\Models\OAuth;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class RefreshBlackboardTokens implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var float|int|string
     */
    private $timestamp;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->timestamp = now()->timestamp;
    }

    public function uniqueId()
    {
        return $this->timestamp;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $blackboard_client_token = base64_encode(env('BLACKBOARD_CLIENT_ID') . ':' . env('BLACKBOARD_CLIENT_SECRET'));
        $blackboard_api_base_url = env('BLACKBOARD_API_URL');
        $blackboard_api_resource = '/v1/oauth2/token';
        $blackboard_client = Http::baseUrl($blackboard_api_base_url)
            ->withToken($blackboard_client_token, 'Basic')
            ->asForm();
        $blackboard_oauths = OAuth::where('provider', '=', 'blackboard')
            ->orderBy('expires_at')
            ->get();

        foreach ($blackboard_oauths as $blackboard_oauth) {
            $response = $blackboard_client
                ->retry(3, 1000)
                ->post($blackboard_api_resource, [
                    'refresh_token' => $blackboard_oauth->refresh_token,
                    'grant_type' => 'refresh_token',
                    'redirect_uri' => route('auth.blackboard'),
                ]);

            $blackboard_oauth->refresh_token = $response['refresh_token'];
            $blackboard_oauth->access_token = $response['access_token'];
            $blackboard_oauth->expires_at = now()->timestamp + $response['expires_in'];

            $blackboard_oauth->save();
        }
    }
}

//TODO: Refresh only those tokens that are close to expiry.
//TODO: Use server response date as timestamp.
