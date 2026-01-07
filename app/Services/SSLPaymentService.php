<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SSLPaymentService
{
    protected $store_id;
    protected $store_password;
    protected $sandbox;
    protected $host;

    public function __construct()
    {
        $this->store_id = env('SSLC_STORE_ID');
        $this->store_password = env('SSLC_STORE_PASSWORD');
        $this->sandbox = env('SSLC_SANDBOX', true);
        $this->host = $this->sandbox ? 'https://sandbox.sslcommerz.com' : 'https://securepay.sslcommerz.com';
    }

    public function initiatePayment($data)
    {
        $post_data = [
            'store_id' => $this->store_id,
            'store_passwd' => $this->store_password,
            'total_amount' => $data['amount'],
            'currency' => 'BDT',
            'tran_id' => $data['trx_id'],
            'success_url' => url('api/payment/success'),
            'fail_url' => url('api/payment/fail'),
            'cancel_url' => url('api/payment/cancel'),
            'cus_name' => $data['cus_name'] ?? 'Guest',
            'cus_email' => $data['cus_email'] ?? 'test@test.com',
            'cus_add1' => 'Dhaka',
            'cus_city' => 'Dhaka',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $data['cus_phone'] ?? '01700000000',
            'shipping_method' => 'NO',
            'product_name' => 'Fees Payment',
            'product_category' => 'Service',
            'product_profile' => 'general',
        ];

        try {
            $response = Http::asForm()->post($this->host . '/gwprocess/v4/api.php', $post_data);
            $result = $response->json();
            
            if (isset($result['status']) && $result['status'] == 'SUCCESS') {
                return [
                    'status' => 'success',
                    'url' => $result['GatewayPageURL']
                ];
            }

            return [
                'status' => 'error',
                'message' => $result['failedreason'] ?? 'Unknown Gateway Error'
            ];

        } catch (\Exception $e) {
            Log::error('SSLCommerz Init Error: ' . $e->getMessage());
             return [
                'status' => 'error',
                'message' => 'Connection Error'
            ];
        }
    }

    public function validatePayment($val_id)
    {
        try {
            $response = Http::get($this->host . '/validator/api/validationserverAPI.php', [
                'val_id' => $val_id,
                'store_id' => $this->store_id,
                'store_passwd' => $this->store_password,
                'format' => 'json'
            ]);

            $result = $response->json();

            if ($result['status'] == 'VALID' || $result['status'] == 'VALIDATED') {
                return true;
            }
            return false;

        } catch (\Exception $e) {
             Log::error('SSLCommerz Validation Error: ' . $e->getMessage());
             return false;
        }
    }
}
