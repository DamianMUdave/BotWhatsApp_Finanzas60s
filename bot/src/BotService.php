<?php

declare(strict_types=1);

require_once __DIR__ . '/ProviderInterface.php';

class BotService
{
    private ProviderInterface $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function handleIncoming(array $request): void
    {
        $incoming = $this->provider->parseIncoming($request);
        $reply = $this->runBotLogic($incoming['from'], $incoming['body']);
        $this->provider->formatWebhookResponse($reply);
    }

    public function sendMessage(string $to, string $message): bool
    {
        return $this->provider->sendMessage($to, $message);
    }

    private function runBotLogic(string $from, string $body): ?string
    {
        if ($body === '') {
            return 'Hola, envíame un mensaje para empezar.';
        }

        $bodyLower = mb_strtolower($body, 'UTF-8');

        if (str_contains($bodyLower, 'hola')) {
            return '¡Hola! Soy tu bot financiero. ¿Quieres tips, recordatorios o consultar saldo?';
        }

        if (str_contains($bodyLower, 'tip')) {
            return 'Tip rápido: separa tus gastos en 50/30/20 para mantener equilibrio.';
        }

        return sprintf('Recibido de %s: %s', $from !== '' ? $from : 'tu número', $body);
    }
}
