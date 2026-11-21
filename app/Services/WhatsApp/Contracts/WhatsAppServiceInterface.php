<?php

namespace App\Services\WhatsApp\Contracts;

interface WhatsAppServiceInterface
{
    /**
     * Send a raw text message via WhatsApp.
     *
     * @param string $to Normalized recipient phone number (e.g. 6281234567890)
     * @param string $message Text content
     * @return array{success: bool, message_id: ?string, error: ?string, raw: array}
     */
    public function sendMessage(string $to, string $message): array;

    /**
     * Send a pre-approved template message via WhatsApp.
     *
     * @param string $to Normalized recipient phone number
     * @param string $templateName Meta template name
     * @param array<int, string> $parameters Template variable values
     * @param string $language Template language code (e.g. 'id')
     * @return array{success: bool, message_id: ?string, error: ?string, raw: array}
     */
    public function sendTemplate(string $to, string $templateName, array $parameters = [], string $language = 'id'): array;
}

