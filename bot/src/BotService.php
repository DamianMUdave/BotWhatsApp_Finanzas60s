<?php

declare(strict_types=1);

require_once __DIR__ . '/ProviderInterface.php';
require_once __DIR__ . '/UserRepository.php';

class BotService
{
    // Para pruebas: espera de 5 minutos entre tips (antes era 1 día en producción).
    private const TIP_WAIT_SECONDS = 300;

    public function __construct(
        private ProviderInterface $provider,
        private UserRepository $users
    ) {
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
        // ===== INICIO DEL FLUJO PRINCIPAL DEL BOT =====
        $user = $this->users->findOrCreateByPhone($from);
        $userId = (int)($user['id'] ?? 0);
        $state = (string)($user['estado'] ?? 'inicio');
        $tipStep = (int)($user['paso_tip'] ?? 1);

        $body = trim($body);
        $lower = mb_strtolower($body, 'UTF-8');

        if (($user['__created'] ?? false) === true) {
            $this->users->assignSubscription($userId, 'Gratuita', 7, 0.00);
            $this->users->updateState($userId, 'esperando_nombre');
            return "¡Bienvenido/a a Finanzas60s! 🎉\n"
                . "Ya tienes activada la suscripción *Gratuita* por 7 días.\n"
                . "Para comenzar, dime tu *nombre*.";
        }

        if ($lower === 'menu') {
            return "Menú:\n- Escribe tu nombre\n- Escribe tu correo\n- Responde 1, 2 o 3\n- Escribe *continuar* para recibir el siguiente tip";
        }

        if ($state === 'esperando_nombre') {
            if ($body === '') {
                return 'Necesito tu nombre para continuar. ✍️';
            }

            $this->users->updateName($userId, $body);
            $this->users->updateState($userId, 'esperando_email');
            return '¡Gracias! Ahora compárteme tu correo electrónico.';
        }

        if ($state === 'esperando_email') {
            if (!filter_var($body, FILTER_VALIDATE_EMAIL)) {
                return 'El correo no parece válido. Inténtalo de nuevo (ejemplo: nombre@correo.com).';
            }

            $this->users->updateEmail($userId, $body);
            $this->users->updateState($userId, 'esperando_nivel');

            return "Perfecto.\n"
                . "Dime cuál te describe mejor:\n"
                . "1.- Estoy empezando\n"
                . "2.- Conozco lo básico\n"
                . "3.- Tengo conocimientos y quiero mejorar\n"
                . "(Responde solo con 1, 2 o 3)";
        }

        if ($state === 'esperando_nivel') {
            if (!in_array($lower, ['1', '2', '3'], true)) {
                return "Opción no válida.\n"
                    . "Responde solo con:\n1\n2\n3";
            }

            $messagesByLevel = [
                '1' => "Perfecto.\nTe mandare tips simples, claros y sin palabras complicadas.",
                '2' => "Genial.\nAqui vas a ordenar lo que ya sabes y hacerlo práctico.",
                '3' => "Excelente.\nVamos directo a estrategias que si muevan el dinero.",
            ];
            $labelsByLevel = ['1' => 'frio', '2' => 'tibio', '3' => 'caliente'];

            $this->users->addLabel($userId, $labelsByLevel[$lower]);
            $this->users->updateTipStep($userId, 1);

            // Estado con timestamp para controlar la siguiente entrega de tip.
            $this->users->updateState($userId, 'tips_wait:' . (string)(time() + self::TIP_WAIT_SECONDS));

            return $messagesByLevel[$lower]
                . "\n\nComenzamos con tu contenido diario.\n"
                . $this->buildTipMessage(1)
                . "\n\nEscribe *continuar* para pedir el siguiente tip.";
        }

        if (str_starts_with($state, 'tips_wait:')) {
            if ($lower !== 'continuar') {
                return 'Para avanzar en los tips, escribe *continuar*. ✅';
            }

            $nextAllowedAt = $this->extractWaitTimestamp($state);
            // AQUÍ se realiza la espera entre tips (5 minutos para pruebas).
            if (time() < $nextAllowedAt) {
                $remaining = max(1, (int)ceil(($nextAllowedAt - time()) / 60));
                return "Aún no toca el siguiente tip. ⏳\nIntenta de nuevo en {$remaining} minuto(s).";
            }

            $nextTip = $tipStep + 1;

            if ($nextTip <= 7) {
                $this->users->updateTipStep($userId, $nextTip);
                $this->users->updateState($userId, 'tips_wait:' . (string)(time() + self::TIP_WAIT_SECONDS));

                return $this->buildTipMessage($nextTip)
                    . "\n\nCuando quieras el siguiente, escribe *continuar*.";
            }

            $this->users->closeActiveSubscription($userId, 'vencida');
            $this->users->updateState($userId, 'esperando_compra');

            return "Ya terminaste los 7 días de la suscripción Gratuita. 🎉\n"
                . "¿Quieres comprar una suscripción?\n"
                . "Responde: *Confirmar* o *Cancelar*.";
        }

        if ($state === 'esperando_compra') {
            if ($lower === 'confirmar') {
                $this->users->replaceWithMonthlySubscription($userId);
                $this->users->updateState($userId, 'fin');
                return "¡Excelente decisión! ✅\n"
                    . "Te activé el plan *Mensual* por 30 días.\n"
                    . "Fin del flujo de prueba.";
            }

            if ($lower === 'cancelar') {
                $this->users->closeActiveSubscription($userId, 'cancelada');
                $this->users->updateState($userId, 'fin');
                return "Entendido. Tu suscripción quedó cancelada.\n"
                    . "Gracias por probar Finanzas60s. ¡Hasta pronto! 👋\n"
                    . "Fin del flujo de prueba.";
            }

            return 'Respuesta no válida. Escribe *Confirmar* o *Cancelar*.';
        }

        if ($state === 'fin') {
            return 'El flujo de prueba ya terminó para este usuario. Si deseas reiniciar, cambia su estado en base de datos.';
        }

        return 'No entendí tu mensaje. Escribe *menu* para ver opciones.';
        // ===== FIN DEL FLUJO PRINCIPAL DEL BOT =====
    }

    private function buildTipMessage(int $tipNumber): string
    {
        return 'Tip' . $tipNumber;
    }

    private function extractWaitTimestamp(string $state): int
    {
        $parts = explode(':', $state, 2);
        if (count($parts) !== 2) {
            return time();
        }

        return (int)$parts[1];
    }
}
