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
        $this->sandbox = filter_var(env('SSLC_SANDBOX', true), FILTER_VALIDATE_BOOLEAN);
        $this->host = $this->sandbox
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }

    public function initiatePayment(array $data)
    {
        $post_data = [
            'store_id' => $this->store_id,
            'store_passwd' => $this->store_password,
            'total_amount' => (float)$data['amount'],
            'currency' => 'BDT',
            'tran_id' => $data['trx_id'],
            'success_url' => url('api/payment/success'),
            'fail_url' => url('api/payment/fail'),
            'cancel_url' => url('api/payment/cancel'),

            // Customer Info
            'cus_name' => $data['cus_name'] ?? 'Guest',
            'cus_email' => $data['cus_email'] ?? 'test@test.com',
            'cus_add1' => 'Dhaka',
            'cus_add2' => '',
            'cus_city' => 'Dhaka',
            'cus_postcode' => '1000',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $data['cus_phone'] ?? '01700000000',

            // Shipping Info
            'shipping_method' => 'NO',
            'ship_name' => $data['cus_name'] ?? 'Guest',
            'ship_add1' => 'Dhaka',
            'ship_add2' => '',
            'ship_city' => 'Dhaka',
            'ship_postcode' => '1000',
            'ship_country' => 'Bangladesh',

            // Product Info
            'product_name' => 'Fees Payment',
            'product_category' => 'Service',
            'product_profile' => 'general',
        ];

        try {
            $response = Http::asForm()->post($this->host . '/gwprocess/v4/api.php', $post_data);
            $result = $response->json();

            if (isset($result['status']) && strtoupper($result['status']) === 'SUCCESS') {
                return [
                    'status' => 'success',
                    'url' => $result['GatewayPageURL']
                ];
            }

            $message = $result['failedreason'] ?? $result['failedReason'] ?? 'Unknown Gateway Error';
            return [
                'status' => 'error',
                'message' => $message
            ];

        } catch (\Exception $e) {
            Log::error('SSLCommerz Init Error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Connection Error'
            ];
        }
    }

    public function validatePayment($val_id, $expected_tran_id = null, $expected_amount = null)
    {
        try {
            $response = Http::get($this->host . '/validator/api/validationserverAPI.php', [
                'val_id' => $val_id,
                'store_id' => $this->store_id,
                'store_passwd' => $this->store_password,
                'format' => 'json'
            ]);

            $result = $response->json();

            if (!isset($result['status'])) return false;

            if (in_array(strtoupper($result['status']), ['VALID', 'VALIDATED'])) {
                // Optional extra validation
                if ($expected_tran_id && $result['tran_id'] !== $expected_tran_id) return false;
                if ($expected_amount && ((float)$result['amount'] !== (float)$expected_amount)) return false;

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('SSLCommerz Validation Error: ' . $e->getMessage());
            return false;
        }
    }
}
