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
        $balanceUrl = "https://smpp.ajuratech.com/portal/sms/smsConfiguration/smsClientBalance.jsp?client=MMadinah";
        try {

            // balance check 
            $balanceResponse = Http::get($balanceUrl); 
            if ($balanceResponse->successful()) {
                $balanceData = json_decode($balanceResponse->body(), true); 
                if (isset($balanceData['Balance']) && $balanceData['Balance'] < 3) {
                    throw new Exception('Insufficient balance. Please recharge to continue sending SMS.'); 
                }
            }

            // send message 
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
