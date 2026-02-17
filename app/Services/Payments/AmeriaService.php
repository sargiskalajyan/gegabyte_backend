<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;

class AmeriaService
{

    /**
     * @var string
     */
    protected string $baseUrl;


    /**
     *
     */
    public function __construct()
    {
        $this->baseUrl = config('services.ameria.test_mode')
            ? 'https://servicestest.ameriabank.am/VPOS'
            : 'https://services.ameriabank.am/VPOS';
    }


    /**
     * @param array $data
     * @return array
     */
    public function initPayment(array $data): array
    {
        $payload = [
            "ClientID"    => config('services.ameria.client_id'),
            "Username"    => config('services.ameria.username'),
            "Password"    => config('services.ameria.password'),
            "Amount"      => $data['amount'],
            "OrderID"     => $data['order_id'],
            "Description" => $data['description'],
            "Currency"    => "051",
            "BackURL"     => $data['back_url'],
        ];

        if (!empty($data['opaque'])) {
            $payload['Opaque'] = $data['opaque'];
        }

        $response = Http::post(
            "{$this->baseUrl}/api/VPOS/InitPayment",
            $payload
        );

        return $response->json();
    }


    /**
     * @param string $paymentId
     * @return array
     */
    public function getPaymentDetails(string $paymentId): array
    {
        $response = Http::post(
            "{$this->baseUrl}/api/VPOS/GetPaymentDetails",
            [
                "PaymentID" => $paymentId,
                "Username"  => config('services.ameria.username'),
                "Password"  => config('services.ameria.password'),
            ]
        );

        return $response->json();
    }


    /**
     * @param string $paymentId
     * @return string
     */
    public function paymentRedirectUrl(string $paymentId): string
    {
        $lang = app()->getLocale();

        $langMap = [
            'hy' => 'am',
            'en' => 'en',
            'ru' => 'ru',
        ];

        $lang = $langMap[$lang] ?? 'en';

        return "{$this->baseUrl}/Payments/Pay?id={$paymentId}&lang={$lang}";
    }



    /**
     * @param string $paymentId
     * @param float $amount
     * @return array|mixed
     */
    public function refund(string $paymentId, float $amount)
    {
        return Http::post("{$this->baseUrl}/api/VPOS/RefundPayment", [
            "PaymentID" => $paymentId,
            "Username"  => config('services.ameria.username'),
            "Password"  => config('services.ameria.password'),
            "Amount"    => $amount,
        ])->json();
    }


    /**
     * @param string $paymentId
     * @return array|mixed
     */
    public function cancel(string $paymentId)
    {
        return Http::post("{$this->baseUrl}/api/VPOS/CancelPayment", [
            "PaymentID" => $paymentId,
            "Username"  => config('services.ameria.username'),
            "Password"  => config('services.ameria.password'),
        ])->json();
    }
}
