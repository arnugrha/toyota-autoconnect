<?php

namespace App\Services\WhatsApp\Adapters;

use App\Services\WhatsApp\Contracts\WhatsAppServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MockWhatsAppService implements WhatsAppServiceInterface
{
    /**
     * Send a simulated text message.
     */
    public function sendMessage(string $to, string $message): array
    {
        $mockId = 'mock_wamid_' . Str::random(24);
        Log::info("[Mock WhatsApp] Message dispatched to {$to}: {$message}");

        return [
            'success' => true,
            'message_id' => $mockId,
            'error' => null,
            'raw' => [
                'messaging_product' => 'whatsapp',
                'contacts' => [['input' => $to, 'wa_id' => $to]],
                'messages' => [['id' => $mockId, 'message_status' => 'accepted']],
            ],
        ];
    }

    /**
     * Send a simulated template message.
     */
    public function sendTemplate(string $to, string $templateName, array $parameters = [], string $language = 'id'): array
    {
        $mockId = 'mock_wamid_tpl_' . Str::random(22);
        Log::info("[Mock WhatsApp] Template '{$templateName}' sent to {$to} with params: " . json_encode($parameters));

        return [
            'success' => true,
            'message_id' => $mockId,
            'error' => null,
            'raw' => [
                'messaging_product' => 'whatsapp',
                'contacts' => [['input' => $to, 'wa_id' => $to]],
                'messages' => [['id' => $mockId, 'message_status' => 'accepted']],
            ],
        ];
    }
}

