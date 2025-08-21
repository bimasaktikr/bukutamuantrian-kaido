<?php

namespace App\Services;

use App\Models\Whatsapp;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected $hostUrl;
    protected $APIKey;

    public function __construct()
    {
        // Set the base URL of the Node.js server
        $this->hostUrl = Whatsapp::latest()->value('server_host_url');// Change this to your Node.js server URL and port if necessary
        $this->APIKey = Whatsapp::latest()->value('key');// Change this to your Node.js server URL and port if necessary
    }

    /**
     * Sends a message via WhatsApp using the Node.js server.
     *
     * @param string $number The recipient's phone number.
     * @param string $message The message to send.
     * @return array The response from the Node.js server.
     */
    // public function sendMessage($number, $message)

    protected function toChatId(string $number): string
    {
        // keep digits only
        $n = preg_replace('/\D+/', '', $number ?? '');

        // normalize to Indonesian MSISDN (adjust to your rules)
        if (str_starts_with($n, '0')) {
            $n = '62' . substr($n, 1);
        } elseif (str_starts_with($n, '620')) {
            $n = '62' . substr($n, 2);
        } elseif (str_starts_with($n, '62')) {
            // ok
        } elseif (str_starts_with($n, '8')) {
            $n = '62' . $n;
        }

        return $n . '@c.us'; // for 1:1 chats; use '@g.us' for groups
    }

    public function sendMessage(array $data)
{
    try {
        $number  = $data['number']  ?? null;
        $message = $data['message'] ?? null;
        if (! $number || ! $message) {
            throw new \InvalidArgumentException('number and message are required');
        }

        // session resolve
        $settings = \App\Models\Whatsapp::latest()->first();
        $session  = $settings?->session_name;

        Log::info($session);

        $payload = [
            'chatId'  => $this->toChatId($number),
            'text'    => $message,
            'session' => $session,
        ];

        $url = rtrim(config('services.waha.host') ?? $this->hostUrl, '/') . '/api/sendText';

        $response = \Illuminate\Support\Facades\Http::withHeaders([
                'x-api-key' => config('services.waha.api_key') ?? $this->APIKey,
            ])
            ->acceptJson()
            ->asJson()
            ->post($url, $payload);

        \Log::info('WAHA sendMessage response', [
            'status'  => $response->status(),
            'body'    => $response->json(),
            'payload' => $payload,
        ]);

        $response->throw();

        return $response->json();
    } catch (\Throwable $e) {
        \Log::error('WAHA sendMessage error: ' . $e->getMessage());
        throw new \Exception('Failed to send WhatsApp message');
    }
}

}
