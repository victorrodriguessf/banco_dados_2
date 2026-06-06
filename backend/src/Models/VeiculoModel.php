<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class VeiculoModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function clienteExiste(int $idCliente): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM oficina.cliente WHERE id = :id'
        );
        $stmt->execute([':id' => $idCliente]);

        return (bool) $stmt->fetchColumn();
    }

    public function placaEmUso(string $placa): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM oficina.veiculo WHERE placa = :placa'
        );
        $stmt->execute([':placa' => $placa]);

        return (bool) $stmt->fetchColumn();
    }

    public function buscarPorPlaca(string $placa): array|false
    {
        $sql = "
            SELECT
                v.id,
                v.marca,
                v.modelo,
                v.ano_fabricacao,
                v.ano_modelo,
                v.placa,
                v.motorizacao,
                v.id_cliente,
                a.id                  AS agendamento_id,
                a.dt_hora_agendamento,
                ts.descricao          AS tipo_servico
            FROM oficina.veiculo v
            LEFT JOIN oficina.agendamento a  ON a.id_veiculo = v.id
            LEFT JOIN oficina.tipo_servico ts ON ts.id = a.id_tipo_servico
            WHERE v.placa = :placa
            ORDER BY a.dt_hora_agendamento DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':placa' => strtoupper(trim($placa))]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return false;
        }

        $veiculo = [
            'id'             => $rows[0]['id'],
            'id_cliente'     => $rows[0]['id_cliente'],
            'marca'          => $rows[0]['marca'],
            'modelo'         => $rows[0]['modelo'],
            'ano_fabricacao' => $rows[0]['ano_fabricacao'],
            'ano_modelo'     => $rows[0]['ano_modelo'],
            'placa'          => $rows[0]['placa'],
            'motorizacao'    => $rows[0]['motorizacao'],
            'agendamentos'   => [],
        ];

        foreach ($rows as $row) {
            if ($row['agendamento_id'] !== null) {
                $veiculo['agendamentos'][] = [
                    'id'                  => $row['agendamento_id'],
                    'dt_hora_agendamento' => $row['dt_hora_agendamento'],
                    'tipo_servico'        => $row['tipo_servico'],
                ];
            }
        }

        return $veiculo;
    }

    public function cadastrar(array $dados): array
    {
        $sql = "
            INSERT INTO oficina.veiculo
                (id, id_cliente, marca, modelo, ano_fabricacao, ano_modelo, placa, motorizacao)
            VALUES
                (COALESCE((SELECT MAX(id) FROM oficina.veiculo), 0) + 1,
                 :id_cliente, :marca, :modelo, :ano_fabricacao, :ano_modelo, :placa, :motorizacao)
            RETURNING *
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_cliente'     => $dados['id_cliente'],
            ':marca'          => $dados['marca']          ?? null,
            ':modelo'         => $dados['modelo']         ?? null,
            ':ano_fabricacao' => $dados['ano_fabricacao'],
            ':ano_modelo'     => $dados['ano_modelo'],
            ':placa'          => strtoupper(trim($dados['placa'])),
            ':motorizacao'    => $dados['motorizacao']    ?? null,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
