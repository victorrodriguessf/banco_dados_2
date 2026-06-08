<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class AgendamentoModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function clienteExiste(int $idCliente): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM oficina.cliente WHERE id = :id');
        $stmt->execute([':id' => $idCliente]);

        return (bool) $stmt->fetchColumn();
    }

    public function veiculoPertenceAoCliente(int $idVeiculo, int $idCliente): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM oficina.veiculo WHERE id = :id_veiculo AND id_cliente = :id_cliente'
        );
        $stmt->execute([
            ':id_veiculo' => $idVeiculo,
            ':id_cliente' => $idCliente,
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function tipoServicoExiste(int $idTipoServico): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM oficina.tipo_servico WHERE id = :id');
        $stmt->execute([':id' => $idTipoServico]);

        return (bool) $stmt->fetchColumn();
    }

    public function abrirOrdemServico(array $dados): array
    {
        $this->pdo->beginTransaction();

        try {
            $this->pdo->exec('LOCK TABLE oficina.agendamento, oficina.ordem_servico IN EXCLUSIVE MODE');

            $stmtAgendamentoId = $this->pdo->query(
                'SELECT COALESCE(MAX(id), 0) + 1 AS id FROM oficina.agendamento'
            );
            $idAgendamento = (int) $stmtAgendamentoId->fetchColumn();

            $stmtOrdemServicoId = $this->pdo->query(
                'SELECT COALESCE(MAX(id), 0) + 1 AS id FROM oficina.ordem_servico'
            );
            $idOrdemServico = (int) $stmtOrdemServicoId->fetchColumn();

            $stmtAgendamento = $this->pdo->prepare("
                INSERT INTO oficina.agendamento (
                    id,
                    id_cliente,
                    id_veiculo,
                    id_tipo_servico,
                    dt_hora_agendamento
                )
                VALUES (
                    :id,
                    :id_cliente,
                    :id_veiculo,
                    :id_tipo_servico,
                    :dt_hora_agendamento
                )
                RETURNING id, id_cliente, id_veiculo, id_tipo_servico, dt_hora_agendamento
            ");
            $stmtAgendamento->execute([
                ':id' => $idAgendamento,
                ':id_cliente' => $dados['id_cliente'],
                ':id_veiculo' => $dados['id_veiculo'],
                ':id_tipo_servico' => $dados['id_tipo_servico'],
                ':dt_hora_agendamento' => $dados['dt_hora_agendamento'],
            ]);
            $agendamento = $stmtAgendamento->fetch(PDO::FETCH_ASSOC);

            $stmtOrdemServico = $this->pdo->prepare("
                INSERT INTO oficina.ordem_servico (
                    id,
                    id_cliente,
                    hodometro_inicial,
                    hodometro_final,
                    status,
                    id_agendamento
                )
                VALUES (
                    :id,
                    :id_cliente,
                    :hodometro_inicial,
                    :hodometro_final,
                    :status,
                    :id_agendamento
                )
                RETURNING id, id_cliente, hodometro_inicial, hodometro_final, status, id_agendamento
            ");
            $stmtOrdemServico->execute([
                ':id' => $idOrdemServico,
                ':id_cliente' => $dados['id_cliente'],
                ':hodometro_inicial' => $dados['hodometro_inicial'],
                ':hodometro_final' => $dados['hodometro_final'],
                ':status' => $dados['status'] ?? 'aberta',
                ':id_agendamento' => $idAgendamento,
            ]);
            $ordemServico = $stmtOrdemServico->fetch(PDO::FETCH_ASSOC);

            $this->pdo->commit();

            return [
                'agendamento' => $agendamento,
                'ordem_servico' => $ordemServico,
            ];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function buscarOrdemServicoPorAgendamento(int $idAgendamento): array|false
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                id_cliente,
                hodometro_inicial,
                hodometro_final,
                status,
                id_agendamento
            FROM oficina.ordem_servico
            WHERE id_agendamento = :id_agendamento
        ");
        $stmt->execute([':id_agendamento' => $idAgendamento]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizarStatusOrdemServico(int $idAgendamento, string $status): array|false
    {
        $stmt = $this->pdo->prepare("
            UPDATE oficina.ordem_servico
            SET status = :status
            WHERE id_agendamento = :id_agendamento
            RETURNING id, id_cliente, hodometro_inicial, hodometro_final, status, id_agendamento
        ");
        $stmt->execute([
            ':id_agendamento' => $idAgendamento,
            ':status' => $status,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
