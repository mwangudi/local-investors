<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestSms extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'sms:test {phone : The phone number to send to} {message? : The message to send}';

    /**
     * The console command description.
     */
    protected $description = 'Test Infobip SMS integration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $phone = $this->argument('phone');
        $message = $this->argument('message') ?? 'This is a test SMS from Local Investors platform.';

        $this->info("Sending test SMS to: {$phone}");
        $this->info("Message: {$message}");

        $baseUrl = config('infobip.base_url');
        $apiKey = config('infobip.api_key');
        $senderId = config('infobip.sender_id');

        $this->info("Base URL: {$baseUrl}");

        if (empty($apiKey) || empty($baseUrl)) {
            $this->error("Infobip credentials are not configured in .env file!");
            $this->error("Please set INFOBIP_BASE_URL and INFOBIP_API_KEY");
            return Command::FAILURE;
        }

        // Format phone number
        $formattedPhone = $this->formatPhoneNumber($phone);
        $this->info("Formatted phone: {$formattedPhone}");

        // Build the endpoint URL
        $endpoint = "https://{$baseUrl}/sms/2/text/advanced";
        $this->info("Endpoint: {$endpoint}");

        // Build the request payload
        $payload = [
            'messages' => [
                [
                    'destinations' => [
                        ['to' => $formattedPhone]
                    ],
                    'text' => $message,
                ]
            ]
        ];

        // Add sender ID if configured
        if (!empty($senderId)) {
            $payload['messages'][0]['from'] = $senderId;
            $this->info("Sender ID: {$senderId}");
        }

        $this->newLine();
        $this->info("Payload:");
        $this->line(json_encode($payload, JSON_PRETTY_PRINT));

        try {
            $response = Http::withHeaders([
                'Authorization' => 'App ' . $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($endpoint, $payload);

            $this->newLine();
            $this->info("HTTP Status: " . $response->status());
            $this->info("Response Body:");
            $this->line(json_encode($response->json(), JSON_PRETTY_PRINT));

            if ($response->successful()) {
                $data = $response->json();
                $this->newLine();
                $this->info("✅ SMS sent successfully!");

                if (isset($data['messages'])) {
                    foreach ($data['messages'] as $msg) {
                        $this->table(
                            ['Field', 'Value'],
                            [
                                ['To', $msg['to'] ?? 'N/A'],
                                ['Status', $msg['status']['name'] ?? 'N/A'],
                                ['Description', $msg['status']['description'] ?? 'N/A'],
                                ['Message ID', $msg['messageId'] ?? 'N/A'],
                            ]
                        );
                    }
                }

                return Command::SUCCESS;
            } else {
                $this->error("❌ SMS sending failed!");
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error("Exception: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Format phone number to international format (without +).
     */
    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[\s\-]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        }

        if (str_starts_with($phone, '+')) {
            $phone = substr($phone, 1);
        }

        return $phone;
    }
}
