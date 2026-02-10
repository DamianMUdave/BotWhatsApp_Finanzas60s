<?php

declare(strict_types=1);

require_once __DIR__ . '/ProviderInterface.php';

class MetaWhatsAppProvider implements ProviderInterface
{
    private string $accessToken;
    private string $phoneNumberId;
    private string $apiBase;

    public function __construct(array $config)
    {
        $this->accessToken = $config['access_token'] ?? '';
        $this->phoneNumberId = $config['phone_number_id'] ?? '';
        $this->apiBase = rtrim($config['api_base'] ?? 'https://graph.facebook.com/v20.0', '/');
    }

    public function parseIncoming(array $request): array
    {
        $body = '';
        $from = '';

        if (isset($request['entry'][0]['changes'][0]['value']['messages'][0])) {
            $message = $request['entry'][0]['changes'][0]['value']['messages'][0];
            $body = trim((string)($message['text']['body'] ?? ''));
            $from = trim((string)($message['from'] ?? ''));
        }

        return [
            'from' => $from,
            'body' => $body,
            'raw' => $request,
        ];
    }

    public function sendMessage(string $to, string $message): bool
    {
        if ($this->accessToken === '' || $this->phoneNumberId === '') {
            return false;
        }

        $url = sprintf('%s/%s/messages', $this->apiBase, $this->phoneNumberId);
        $payload = json_encode([
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->accessToken,
            ],
        ]);

        curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status >= 200 && $status < 300;
    }

    public function formatWebhookResponse(?string $replyMessage): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'ok', 'reply' => $replyMessage]);
    }
}
