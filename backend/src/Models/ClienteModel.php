<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class ClienteModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function criar(array $cliente): array
    {
        $this->pdo->beginTransaction();

        try {
            $this->pdo->exec('LOCK TABLE oficina.cliente IN EXCLUSIVE MODE');

            $stmtId = $this->pdo->query('SELECT COALESCE(MAX(id), 0) + 1 AS id FROM oficina.cliente');
            $id = (int) $stmtId->fetchColumn();

            $sql = "
                INSERT INTO oficina.cliente (
                    id,
                    nome_completo,
                    cpf,
                    cnpj,
                    telefone,
                    email
                )
                VALUES (
                    :id,
                    :nome_completo,
                    :cpf,
                    :cnpj,
                    :telefone,
                    :email
                )
                RETURNING id, nome_completo, cpf, cnpj, dt_cadastro, telefone, email
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':id' => $id,
                ':nome_completo' => $cliente['nome_completo'],
                ':cpf' => $cliente['cpf'],
                ':cnpj' => $cliente['cnpj'],
                ':telefone' => $cliente['telefone'],
                ':email' => $cliente['email'],
            ]);

            $novoCliente = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->pdo->commit();

            return $novoCliente;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function listar(array $filtros = []): array
    {
        $condicoes = [];
        $parametros = [];

        if (!empty($filtros['nome'])) {
            $condicoes[] = 'nome_completo ILIKE :nome';
            $parametros[':nome'] = '%' . $filtros['nome'] . '%';
        }

        if (($filtros['tipo'] ?? null) === 'fisica') {
            $condicoes[] = 'cpf IS NOT NULL';
        }

        if (($filtros['tipo'] ?? null) === 'juridica') {
            $condicoes[] = 'cnpj IS NOT NULL';
        }

        $where = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

        $sql = "
            SELECT
                id,
                nome_completo,
                cpf,
                cnpj,
                CASE
                    WHEN cpf IS NOT NULL THEN 'fisica'
                    WHEN cnpj IS NOT NULL THEN 'juridica'
                END AS tipo_pessoa,
                dt_cadastro,
                telefone,
                email
            FROM oficina.cliente
            {$where}
            ORDER BY nome_completo, id
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($parametros);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorIdComVeiculos(int $id): ?array
    {
        $sqlCliente = "
            SELECT
                id,
                nome_completo,
                cpf,
                cnpj,
                dt_cadastro,
                telefone,
                email
            FROM oficina.cliente
            WHERE id = :id
        ";

        $stmtCliente = $this->pdo->prepare($sqlCliente);
        $stmtCliente->execute([':id' => $id]);
        $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

        if (!$cliente) {
            return null;
        }

        $sqlVeiculos = "
            SELECT
                id,
                id_cliente,
                marca,
                modelo,
                ano_fabricacao,
                ano_modelo,
                placa,
                motorizacao
            FROM oficina.veiculo
            WHERE id_cliente = :id_cliente
            ORDER BY id
        ";

        $stmtVeiculos = $this->pdo->prepare($sqlVeiculos);
        $stmtVeiculos->execute([':id_cliente' => $id]);

        $cliente['veiculos'] = $stmtVeiculos->fetchAll(PDO::FETCH_ASSOC);

        return $cliente;
    }
}
