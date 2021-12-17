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

class RefreshWebexTokens implements ShouldQueue, ShouldBeUnique
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
        $webex_api_base_url = env('WEBEX_API_URL');
        $webex_api_resource = '/access_token';
        $webex_client = Http::baseUrl($webex_api_base_url)
            ->asForm();
        $webex_oauths = OAuth::where('provider', '=', 'webex')
            ->orderBy('expires_at')
            ->get();

        foreach ($webex_oauths as $webex_oauth) {
            $response = $webex_client
                ->retry(3, 1000)
                ->post($webex_api_resource, [
                    'client_id' => config('services.webex.client_id'),
                    'refresh_token' => $webex_oauth->refresh_token,
                    'grant_type' => 'refresh_token',
                    'client_secret' => config('services.webex.client_secret')
                ]);

            $webex_oauth->refresh_token = $response['refresh_token'];
            $webex_oauth->access_token = $response['access_token'];
            $webex_oauth->expires_at = now()->timestamp + $response['expires_in'];

            $webex_oauth->save();
        }
    }
}

//TODO: Refresh only those tokens that are close to expiry.
//TODO: Use server response date as timestamp.
