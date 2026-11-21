<?php

namespace App\Services\WhatsApp\Adapters;

use App\Services\WhatsApp\Contracts\WhatsAppServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MetaWhatsAppService implements WhatsAppServiceInterface
{
    protected string $version;
    protected ?string $phoneNumberId;
    protected ?string $accessToken;

    public function __construct()
    {
        $this->version = config('services.whatsapp.version', 'v21.0');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->accessToken = config('services.whatsapp.access_token');
    }

    /**
     * Send a raw text message.
     */
    public function sendMessage(string $to, string $message): array
    {
        $url = "https://graph.facebook.com/{$this->version}/{$this->phoneNumberId}/messages";

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => false,
                'body' => $message,
            ],
        ];

        return $this->sendRequest($url, $payload);
    }

    /**
     * Send a pre-approved template message.
     */
    public function sendTemplate(string $to, string $templateName, array $parameters = [], string $language = 'id'): array
    {
        $url = "https://graph.facebook.com/{$this->version}/{$this->phoneNumberId}/messages";

        $templateParams = array_map(fn ($p) => ['type' => 'text', 'text' => (string) $p], $parameters);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $language],
                'components' => !empty($templateParams) ? [
                    [
                        'type' => 'body',
                        'parameters' => $templateParams,
                    ],
                ] : [],
            ],
        ];

        return $this->sendRequest($url, $payload);
    }

    /**
     * Execute HTTP request to Meta API.
     */
    protected function sendRequest(string $url, array $payload): array
    {
        if (empty($this->phoneNumberId) || empty($this->accessToken)) {
            Log::warning('WhatsApp Meta credentials missing in environment.');
            return [
                'success' => false,
                'message_id' => null,
                'error' => 'WhatsApp Meta credentials are not properly configured.',
                'raw' => [],
            ];
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->timeout(10)
                ->asJson()
                ->post($url, $payload);

            $data = $response->json() ?? [];

            if ($response->successful()) {
                $messageId = $data['messages'][0]['id'] ?? null;
                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'error' => null,
                    'raw' => $data,
                ];
            }

            $errorMessage = $data['error']['message'] ?? ('HTTP error ' . $response->status());
            Log::error('Meta WhatsApp API request failed: ' . $errorMessage, ['response' => $data]);

            return [
                'success' => false,
                'message_id' => null,
                'error' => $errorMessage,
                'raw' => $data,
            ];
        } catch (Throwable $e) {
            Log::error('Meta WhatsApp API exception: ' . $e->getMessage());

            return [
                'success' => false,
                'message_id' => null,
                'error' => $e->getMessage(),
                'raw' => [],
            ];
        }
    }
}

