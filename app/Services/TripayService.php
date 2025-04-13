<?php

namespace App\Services;

use GuzzleHttp\Client;

class TripayService
{
    protected $apiKey;
    protected $privateKey;
    protected $merchantCode;
    protected $mode;
    protected $client;

    public function __construct()
    {
        $this->apiKey = config('services.tripay.api_key');
        $this->privateKey = config('services.tripay.private_key');
        $this->merchantCode = config('services.tripay.merchant_code');
        $this->mode = config('services.tripay.mode');
        $this->client = new Client([
            'base_uri' => $this->mode === 'production' 
                ? 'https://tripay.co.id/api/' 
                : 'https://tripay.co.id/api-sandbox/',
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => 'application/json',
            ]
        ]);
    }

    public function createTransaction(array $data)
    {
        $payload = [
            'method' => $data['method'],
            'merchant_ref' => $data['merchant_ref'],
            'amount' => $data['amount'],
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'order_items' => $data['order_items'],
            'expired_time' => $data['expired_time'],
            'signature' => hash_hmac('sha256', $this->merchantCode.$data['merchant_ref'].$data['amount'], $this->privateKey)
        ];

        try {
            $response = $this->client->post('transaction/create', [
                'json' => $payload
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            throw new \Exception("Tripay API Error: ".$e->getMessage());
        }
    }
}