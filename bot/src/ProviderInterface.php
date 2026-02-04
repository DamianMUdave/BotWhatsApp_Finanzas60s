<?php

declare(strict_types=1);

interface ProviderInterface
{
    /** @return array{from:string,body:string,raw:array} */
    public function parseIncoming(array $request): array;

    public function sendMessage(string $to, string $message): bool;

    public function formatWebhookResponse(?string $replyMessage): void;
}
