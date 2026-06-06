<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class UsuarioModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function buscarPorCredenciais(string $usuario, string $senha): array|false
    {
        $sql = "
            SELECT *
            FROM oficina.usuario
            WHERE usuario = :usuario
            AND senha = :senha
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':usuario' => $usuario, ':senha' => $senha]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function salvarSessao(int $usuarioId, string $token): void
    {
        $sql = "
            INSERT INTO oficina.sessao (usuario_id, token)
            VALUES (:usuario_id, :token)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':usuario_id' => $usuarioId, ':token' => $token]);
    }

    public function buscarPorRefreshToken(string $token): array|false
    {
        $sql = "
            SELECT u.*
            FROM oficina.sessao s
            INNER JOIN oficina.usuario u ON u.id = s.usuario_id
            WHERE s.token = :token
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':token' => $token]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarRefreshToken(string $tokenAtual, string $novoToken): void
    {
        $sql = "
            UPDATE oficina.sessao
            SET token = :novo_token
            WHERE token = :token_atual
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':novo_token' => $novoToken,
            ':token_atual' => $tokenAtual,
        ]);
    }
}
