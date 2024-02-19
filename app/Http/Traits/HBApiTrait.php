<?php

namespace App\Http\Traits;

use App\Http\Enums\RequestTypes;
use App\Models\Hotelbeds_credential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait HBApiTrait
{
    public function cpGetHBApiCredentialsByClientID($pClientID)
    {
        return Hotelbeds_credential::select('hb_api_key', 'hb_api_secret')->where('client_id', $pClientID)->first();
    }

    public function cpGetHBApiKeyByClientID($pClientID)
    {
        $mHBRecord = Hotelbeds_credential::select('hb_api_key')->where('client_id', $pClientID)->first();
        return $mHBRecord['hb_api_key'];
    }

    public function cpGetHBApiSecretByClientID($pClientID)
    {
        return Hotelbeds_credential::select('hb_api_secret')->where('client_id', $pClientID)->first();
    }

    public function cpGetXSignature($pClientID)
    {
        $mHBCredentials = $this->cpGetHBApiCredentialsByClientID($pClientID);
        $signature = hash("sha256", $mHBCredentials["hb_api_key"] . $mHBCredentials["hb_api_secret"] . time());
        return $signature;
    }

    public function cpSendApiRequest($pPath, $pRequestType, $pClientID, $pParams = [], $pFrom = 1, $pTo = 100)
    {
        $mBaseURL = "https://api.test.hotelbeds.com/";
        $response = [];
        try {
            if ($pRequestType == RequestTypes::GET) {
                $mParams = "";
                if (!count($pParams)) {
                    $mParams = "?fields=all&language=ENG&from=1&to=100&useSecondaryLanguage=True";
                } else {
                    foreach ($pParams as $key => $value) {
                        if (empty($mParams)) {
                            $mParams = "?" . $key . "=" . $value;
                        } else {
                            $mParams = $mParams."&" . $key . "=" . $value;
                        }
                    }
                }
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Accept-Encoding' => 'gzip',
                    'API-Key' => $this->cpGetHBApiKeyByClientID($pClientID),
                    'X-Signature' => $this->cpGetXSignature($pClientID)
                ])->get($mBaseURL . $pPath . $mParams);
            } else if($pRequestType == RequestTypes::POST) {
                $response = Http::withBody(json_encode($pParams), 'application/json')->withHeaders([
                    'Accept' => 'application/json',
                    'Accept-Encoding' => 'gzip',
                    'Api-key' => $this->cpGetHBApiKeyByClientID($pClientID),
                    'X-Signature' => $this->cpGetXSignature($pClientID)
                ])->post($mBaseURL . $pPath);
            }
            else if($pRequestType == RequestTypes::DELETE){
                $mParams = "";
                if (count($pParams)) {
                    foreach ($pParams as $key => $value) {
                        if (empty($mParams)) {
                            $mParams = "?" . $key . "=" . $value;
                        } else {
                            $mParams = $mParams."&" . $key . "=" . $value;
                        }
                    }
                }
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Accept-Encoding' => 'gzip',
                    'API-Key' => $this->cpGetHBApiKeyByClientID($pClientID),
                    'X-Signature' => $this->cpGetXSignature($pClientID)
                ])->delete($mBaseURL . $pPath . $mParams);
            }
            // Determine if the status code is >= 400...
            $mFailed = $response->failed();
            // Determine if the response has a 400 level status code...
            $mClientError = $response->clientError();

            // Determine if the response has a 500 level status code...
            $mServerError = $response->serverError();
            if ($mFailed or $mClientError or $mServerError) {
                $response->throw();
            }
            return $response;
        } catch (\Exception $e) {
            Log::info("exception");
            Log::info($e);
            // handle other exceptions such as ConnectionException
            return $response;
        }
    }
}
