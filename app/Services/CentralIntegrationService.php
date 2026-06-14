<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class CentralIntegrationService
{
    /**
     * Mengambil Token M2M menggunakan API Key.
     */
    public static function getM2mToken()
    {
        $response = Http::post('https://iae-sso.virtualfri.id/api/v1/auth/token', [
            'api_key' => config('services.central.api_key', 'KEY-MHS-50')
        ]);

        if ($response->failed()) {
            throw new Exception('Gagal mendapatkan token M2M dari SSO Server Cloud. Status: ' . $response->status());
        }

        $resData = $response->json();
        $token = $resData['token'] ?? $resData['access_token'] ?? null;
        if (!$token) {
            $responseBody = json_encode($resData) ?: $response->body();
            throw new Exception('Gagal mendapatkan access_token dari respon SSO. Respon Server: ' . $responseBody);
        }

        return $token;
    }

    /**
     * SOAP XML Client (Mengirim Log Audit & Mem-parsing ReceiptNumber)
     */
    public static function sendSoapAudit($orderId, array $orderData)
    {
        $token = self::getM2mToken();
        if (!$token) {
            throw new Exception('Otorisasi token M2M kosong.');
        }

        $teamId = 'TEAM-03'; 
        $activityName = 'OrderProcessed';
        $logContentJson = json_encode($orderData);

        // XML Envelope Kaku untuk SOAP
        $xmlPayload = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
  <soap:Body>
    <iae:AuditRequest>
      <iae:TeamID>{$teamId}</iae:TeamID>
      <iae:ActivityName>{$activityName}</iae:ActivityName>
      <iae:LogContent><![CDATA[{$logContentJson}]]></iae:LogContent>
    </iae:AuditRequest>
  </soap:Body>
</soap:Envelope>
XML;

        // POST Request ke SOAP API Endpoint Dosen
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'text/xml; charset=utf-8',
        ])->send('POST', 'https://iae-sso.virtualfri.id/soap/v1/audit', [
            'body' => $xmlPayload
        ]);

        if ($response->failed()) {
            throw new Exception('Request SOAP audit gagal terkirim ke server Cloud.');
        }

        $xmlResult = $response->body();

        // XML Parsing untuk mengambil ReceiptNumber
        try {
            $cleanXml = str_ireplace(['soap:', 'iae:'], '', $xmlResult);
            $xmlElement = simplexml_load_string($cleanXml);
            $receiptNumber = (string) $xmlElement->Body->AuditResponse->ReceiptNumber;

            if (empty($receiptNumber)) {
                if (preg_match('/<ReceiptNumber>(.*?)<\/ReceiptNumber>/s', $xmlResult, $matches)) {
                    $receiptNumber = $matches[1];
                }
            }

            if (empty($receiptNumber)) {
                throw new Exception('ReceiptNumber tidak ditemukan di dalam XML respon.');
            }

            return $receiptNumber;
        } catch (Exception $e) {
            throw new Exception('Gagal mem-parsing XML SOAP Response: ' . $e->getMessage());
        }
    }

    /**
     * AMQP Publisher (Publish JSON Event ke RabbitMQ Dosen)
     */
    public static function publishAmqpEvent($routingKey, array $payload)
    {
        $token = self::getM2mToken();
        if (!$token) {
            throw new Exception('Otorisasi token M2M kosong.');
        }

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json'
        ])->post('https://iae-sso.virtualfri.id/api/v1/messages/publish', [
            'exchange' => 'iae.central.exchange',
            'routing_key' => $routingKey,
            'payload' => $payload   
        ]);

        if ($response->failed()) {
            throw new Exception('Gagal mengirim event ke RabbitMQ: ' . $response->body());
        }

        return true;
    }
}