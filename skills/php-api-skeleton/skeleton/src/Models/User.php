<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\Database;
use PDO;

final class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->pdo();
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT id, email, name, role, active FROM users WHERE id = :id LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $email, string $passwordHash, ?string $name, string $role = 'user'): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (email, password, name, role) VALUES (:email, :password, :name, :role)'
        );
        $stmt->execute([
            ':email'    => $email,
            ':password' => $passwordHash,
            ':name'     => $name ?: null,
            ':role'     => $role,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function all(): array
    {
        $stmt = $this->db->query(
            'SELECT id, email, name, role, active, created_at FROM users ORDER BY id ASC'
        );
        return $stmt->fetchAll();
    }

    public function update(int $id, array $fields): bool
    {
        $allowed = ['name', 'role', 'active'];
        $set     = [];
        $params  = [':id' => $id];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $fields)) {
                $set[]               = "{$field} = :{$field}";
                $params[":{$field}"] = $fields[$field];
            }
        }

        if (empty($set)) return false;

        $stmt = $this->db->prepare(
            'UPDATE users SET ' . implode(', ', $set) . ' WHERE id = :id'
        );
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
