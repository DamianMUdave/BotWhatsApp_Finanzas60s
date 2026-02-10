<?php

declare(strict_types=1);

class UserRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function findOrCreateByPhone(string $phone): array
    {
        $phone = $this->normalizePhone($phone);

        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE telefono = :telefono LIMIT 1');
        $stmt->execute(['telefono' => $phone]);
        $user = $stmt->fetch();

        if ($user) {
            $user['__created'] = false;
            return $user;
        }

        $insert = $this->pdo->prepare('INSERT INTO usuarios (telefono, estado, paso_tip) VALUES (:telefono, :estado, :paso_tip)');
        $insert->execute([
            'telefono' => $phone,
            'estado' => 'inicio',
            'paso_tip' => 1,
        ]);

        $newUser = $this->findById((int)$this->pdo->lastInsertId()) ?? [];
        $newUser['__created'] = true;

        return $newUser;
    }

    public function findById(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function updateState(int $userId, string $estado): void
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET estado = :estado WHERE id = :id');
        $stmt->execute(['estado' => $estado, 'id' => $userId]);
    }

    public function updateName(int $userId, string $name): void
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET nombre = :nombre WHERE id = :id');
        $stmt->execute(['nombre' => $name, 'id' => $userId]);
    }

    public function updateEmail(int $userId, string $email): void
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET email = :email WHERE id = :id');
        $stmt->execute(['email' => $email, 'id' => $userId]);
    }

    public function updateTipStep(int $userId, int $step): void
    {
        $stmt = $this->pdo->prepare('UPDATE usuarios SET paso_tip = :paso_tip WHERE id = :id');
        $stmt->execute(['paso_tip' => $step, 'id' => $userId]);
    }

    public function addLabel(int $userId, string $labelName): void
    {
        $labelName = trim(mb_strtolower($labelName, 'UTF-8'));
        if ($labelName === '') {
            return;
        }

        $insertLabel = $this->pdo->prepare('INSERT INTO etiquetas (nombre) VALUES (:nombre) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)');
        $insertLabel->execute(['nombre' => $labelName]);
        $labelId = (int)$this->pdo->lastInsertId();

        $link = $this->pdo->prepare('INSERT IGNORE INTO usuario_etiquetas (usuario_id, etiqueta_id) VALUES (:usuario_id, :etiqueta_id)');
        $link->execute([
            'usuario_id' => $userId,
            'etiqueta_id' => $labelId,
        ]);
    }

    /** @return string[] */
    public function getLabels(int $userId): array
    {
        $stmt = $this->pdo->prepare('SELECT e.nombre FROM etiquetas e INNER JOIN usuario_etiquetas ue ON ue.etiqueta_id = e.id WHERE ue.usuario_id = :usuario_id ORDER BY e.nombre ASC');
        $stmt->execute(['usuario_id' => $userId]);

        return array_map(static fn(array $row): string => (string)$row['nombre'], $stmt->fetchAll());
    }

    public function ensurePlan(string $name, int $durationDays, float $price): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM planes WHERE nombre = :nombre LIMIT 1');
        $stmt->execute(['nombre' => $name]);
        $existing = $stmt->fetch();

        if ($existing) {
            return (int)$existing['id'];
        }

        $insert = $this->pdo->prepare('INSERT INTO planes (nombre, duracion_dias, precio) VALUES (:nombre, :duracion_dias, :precio)');
        $insert->execute([
            'nombre' => $name,
            'duracion_dias' => $durationDays,
            'precio' => $price,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function assignSubscription(int $userId, string $planName, int $durationDays, float $price): void
    {
        $active = $this->getActiveSubscription($userId);
        if ($active) {
            return;
        }

        $planId = $this->ensurePlan($planName, $durationDays, $price);
        $start = new DateTimeImmutable('now');
        $end = $start->modify(sprintf('+%d days', $durationDays));

        $insert = $this->pdo->prepare(
            'INSERT INTO suscripciones (usuario_id, plan_id, fecha_inicio, fecha_fin, estado)
             VALUES (:usuario_id, :plan_id, :fecha_inicio, :fecha_fin, :estado)'
        );
        $insert->execute([
            'usuario_id' => $userId,
            'plan_id' => $planId,
            'fecha_inicio' => $start->format('Y-m-d'),
            'fecha_fin' => $end->format('Y-m-d'),
            'estado' => 'activa',
        ]);
    }

    public function replaceWithMonthlySubscription(int $userId): void
    {
        $this->closeActiveSubscription($userId, 'cancelada');
        $planId = $this->ensurePlan('Mensual', 30, 99.00);

        $start = new DateTimeImmutable('now');
        $end = $start->modify('+30 days');

        $insert = $this->pdo->prepare(
            'INSERT INTO suscripciones (usuario_id, plan_id, fecha_inicio, fecha_fin, estado)
             VALUES (:usuario_id, :plan_id, :fecha_inicio, :fecha_fin, :estado)'
        );
        $insert->execute([
            'usuario_id' => $userId,
            'plan_id' => $planId,
            'fecha_inicio' => $start->format('Y-m-d'),
            'fecha_fin' => $end->format('Y-m-d'),
            'estado' => 'activa',
        ]);
    }

    public function closeActiveSubscription(int $userId, string $status = 'cancelada'): void
    {
        $stmt = $this->pdo->prepare("UPDATE suscripciones SET estado = :estado WHERE usuario_id = :usuario_id AND estado = 'activa'");
        $stmt->execute([
            'estado' => $status,
            'usuario_id' => $userId,
        ]);
    }

    public function getActiveSubscription(int $userId): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT s.*, p.nombre AS plan_nombre, p.precio, p.duracion_dias
             FROM suscripciones s
             INNER JOIN planes p ON p.id = s.plan_id
             WHERE s.usuario_id = :usuario_id AND s.estado = 'activa'
             ORDER BY s.fecha_fin DESC
             LIMIT 1"
        );
        $stmt->execute(['usuario_id' => $userId]);
        $subscription = $stmt->fetch();

        return $subscription ?: null;
    }

    private function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        if (str_starts_with($phone, 'whatsapp:')) {
            return substr($phone, 9);
        }

        return $phone;
    }
}
