<?php

declare(strict_types=1);

require_once __DIR__ . '/ProviderInterface.php';

class TwilioProvider implements ProviderInterface
{
    private string $accountSid;
    private string $authToken;
    private string $from;

    public function __construct(array $config)
    {
        $this->accountSid = $config['account_sid'] ?? '';
        $this->authToken = $config['auth_token'] ?? '';
        $this->from = $config['from'] ?? '';
    }

    public function parseIncoming(array $request): array
    {
        $body = trim((string)($request['Body'] ?? ''));
        $from = trim((string)($request['From'] ?? ''));

        return [
            'from' => $from,
            'body' => $body,
            'raw' => $request,
        ];
    }

    public function sendMessage(string $to, string $message): bool
    {
        if ($this->accountSid === '' || $this->authToken === '' || $this->from === '') {
            return false;
        }

        $url = sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', $this->accountSid);
        $payload = http_build_query([
            'From' => $this->from,
            'To' => $to,
            'Body' => $message,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $this->accountSid . ':' . $this->authToken,
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        ]);

        curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status >= 200 && $status < 300;
    }

    public function formatWebhookResponse(?string $replyMessage): void
    {
        header('Content-Type: text/xml; charset=utf-8');

        if ($replyMessage === null || $replyMessage === '') {
            echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response />";
            return;
        }

        $escaped = htmlspecialchars($replyMessage, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response><Message>{$escaped}</Message></Response>";
    }
}
