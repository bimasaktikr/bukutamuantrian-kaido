<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    protected $hostUrl;

    public function __construct()
    {
        // Set the base URL of the Node.js server
        $this->hostUrl = 'http://localhost:3000'; // Change this to your Node.js server URL and port if necessary
    }

    /**
     * Sends a message via WhatsApp using the Node.js server.
     *
     * @param string $number The recipient's phone number.
     * @param string $message The message to send.
     * @return array The response from the Node.js server.
     */
    // public function sendMessage($number, $message)
    public function sendMessage(array $data)
    {
        try {

            // Mengambil data dari array
            $number = $data['number'];
            $message = $data['message'];
            // Send HTTP POST request to Node.js server
            $response = Http::post("{$this->hostUrl}/send-message", [
                'number' => $number,
                'message' => $message,
            ]);

            Log::info ($response);
            // Check if the request was successful
            $responseBody = $response->body();
            $decodedResponse = json_decode($responseBody, true);  // Decode to array
        
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("JSON Decode Error: " . json_last_error_msg());
                throw new \Exception('Failed to decode JSON response from Node.js');
            }
        
            if ($response->successful()) {
                Log::info("Message sent successfully: " . json_encode($decodedResponse));
                return $decodedResponse;
            } else {
                Log::error("Failed to send message: " . $responseBody);
                return response()->json(['error' => 'Failed to send message', 'details' => $responseBody], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error sending WhatsApp message: ' . $e->getMessage());
            throw new \Exception('Failed to send WhatsApp message');
        }
    }
}
