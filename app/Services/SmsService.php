<?php

namespace App\Services;

class SmsService
{

    /**
     * @param string $phone
     * @param string $message
     * @return true
     */
    public function sendSms(string $phone, string $message)
    {
        // Format phone to Armenian style "374XXXXXXXX"
        $phone = "374".substr(preg_replace('/[\/(\/)\- ]/', '', $phone), 1);

        // Replace spaces with "+" for URL encoding
        $body = preg_replace('/ /', '+', $message);

        // Simple GET API call (Mobipace v2.0)
        $url = 'https://www.mobipace.com/API_2_0/HTTP_API.aspx?function=send'
            . '&username=' . urlencode(env('MOBIPACE_USER'))
            . '&password=' . urlencode(env('MOBIPACE_PASS'))
            . '&sender=' . env('APP_NAME')
            . '&recipient=' . $phone
            . '&body=' . $body;

        // Fire request
        @get_headers($url);
        return true;
    }
}
