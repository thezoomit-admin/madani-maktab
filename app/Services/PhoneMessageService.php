<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class PhoneMessageService
{
    protected $apiUrl;
    protected $apiKey;
    protected $secretKey;
    protected $senderId;

    public function __construct()
    {
        $this->apiUrl = env('AJURATECH_BASE_URL');
        $this->apiKey = env('AJURATECH_API_KEY');
        $this->secretKey = env('AJURATECH_SECRET_KEY');
        $this->senderId = env('AJURATECH_SENDER_ID');
    }

    public function sendMessage($phone, $message)
    {
        $queryParams = [
            'apikey' => $this->apiKey,
            'secretkey' => $this->secretKey,
            'callerID' => $this->senderId,
            'toUser' => $phone,
            'messageContent' => $message,
        ];

        $url = $this->apiUrl . '?' . http_build_query($queryParams);

        try {
            $response = Http::get($url);

            if ($response->successful()) {
                return $response->body();
            } else {
                throw new Exception('SMS sending failed. Status code: ' . $response->status());
            }
        } catch (Exception $e) {
            throw new Exception('Error while sending SMS: ' . $e->getMessage());
        }
    }
}
