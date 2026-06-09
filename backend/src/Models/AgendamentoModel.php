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
                    id_agendamento
                )
                VALUES (
                    :id,
                    :id_cliente,
                    :hodometro_inicial,
                    :hodometro_final,
                    :id_agendamento
                )
                RETURNING id, id_cliente, hodometro_inicial, hodometro_final, id_agendamento
            ");
            $stmtOrdemServico->execute([
                ':id' => $idOrdemServico,
                ':id_cliente' => $dados['id_cliente'],
                ':hodometro_inicial' => $dados['hodometro_inicial'],
                ':hodometro_final' => $dados['hodometro_final'],
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

    public function agendamentoExiste(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM oficina.agendamento WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function buscarOsPorAgendamento(int $idAgendamento): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM oficina.ordem_servico WHERE id_agendamento = :id_agendamento'
        );
        $stmt->execute([':id_agendamento' => $idAgendamento]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: false;
    }

    public function osPossuiPagamento(int $idOs): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM oficina.pagamento WHERE id_os = :id_os');
        $stmt->execute([':id_os' => $idOs]);
        return (bool) $stmt->fetchColumn();
    }

    public function funcionarioExiste(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM oficina.funcionario WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function adicionarItemServico(array $dados): array
    {
        $sql = "
            INSERT INTO oficina.item_servico
                (id, id_tipo_servico, id_os, id_funcionario, valor_mao_de_obra, observacao)
            VALUES
                (COALESCE((SELECT MAX(id) FROM oficina.item_servico), 0) + 1,
                 :id_tipo_servico, :id_os, :id_funcionario, :valor_mao_de_obra, :observacao)
            RETURNING *
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_tipo_servico'   => $dados['id_tipo_servico'],
            ':id_os'             => $dados['id_os'],
            ':id_funcionario'    => $dados['id_funcionario'] ?? null,
            ':valor_mao_de_obra' => $dados['valor_mao_de_obra'] ?? null,
            ':observacao'        => $dados['observacao'] ?? null,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function pecaExiste(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM oficina.peca WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function pecaTemEstoque(int $idPeca, float $quantidade): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT quantidade_em_estoque FROM oficina.peca WHERE id = :id'
        );
        $stmt->execute([':id' => $idPeca]);
        $estoque = $stmt->fetchColumn();
        return $estoque !== false && $estoque >= $quantidade;
    }

    public function itemServicoExiste(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM oficina.item_servico WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function itemServicoPertenceAOs(int $idItemServico, int $idOs): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM oficina.item_servico WHERE id = :id AND id_os = :id_os'
        );
        $stmt->execute([':id' => $idItemServico, ':id_os' => $idOs]);
        return (bool) $stmt->fetchColumn();
    }

    public function adicionarItemPeca(array $dados): array
    {
        $sql = "
            INSERT INTO oficina.item_peca
                (id, id_peca, id_item_servico, quantidade, devolucao)
            VALUES
                (COALESCE((SELECT MAX(id) FROM oficina.item_peca), 0) + 1,
                 :id_peca, :id_item_servico, :quantidade, :devolucao)
            RETURNING *
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_peca'         => $dados['id_peca'],
            ':id_item_servico' => $dados['id_item_servico'],
            ':quantidade'      => $dados['quantidade'],
            ':devolucao'       => $dados['devolucao'] ? 'true' : 'false',
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function formaPagamentoExiste(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM oficina.forma_pagamento WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return (bool) $stmt->fetchColumn();
    }

    public function registrarPagamento(array $dados): array
    {
        $sql = "
            INSERT INTO oficina.pagamento
                (id, id_forma_pagamento, id_cliente, id_os, valor_parcial, valor_pago)
            VALUES
                (COALESCE((SELECT MAX(id) FROM oficina.pagamento), 0) + 1,
                 :id_forma_pagamento, :id_cliente, :id_os, :valor_parcial, :valor_pago)
            RETURNING *
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_forma_pagamento' => $dados['id_forma_pagamento'],
            ':id_cliente'         => $dados['id_cliente'],
            ':id_os'              => $dados['id_os'],
            ':valor_parcial'      => $dados['valor_parcial'] ?? 0,
            ':valor_pago'         => $dados['valor_pago'],
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrarAvaliacao(array $dados): array
    {
        $sql = "
            INSERT INTO oficina.avaliacao
                (id, id_item_servico, nota, comentario)
            VALUES
                (COALESCE((SELECT MAX(id) FROM oficina.avaliacao), 0) + 1,
                 :id_item_servico, :nota, :comentario)
            RETURNING *
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id_item_servico' => $dados['id_item_servico'],
            ':nota'            => $dados['nota'],
            ':comentario'      => $dados['comentario'] ?? null,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function listarAgendamentosDoCliente(int $idCliente): array
    {
        $sql = "
            SELECT
                a.id,
                a.id_cliente,
                a.id_veiculo,
                a.id_tipo_servico,
                a.dt_hora_agendamento,
                v.marca,
                v.modelo,
                v.placa,
                ts.descricao as tipo_servico_descricao,
                os.id as ordem_servico_id,
                os.hodometro_inicial,
                os.hodometro_final
            FROM oficina.agendamento a
            LEFT JOIN oficina.veiculo v ON a.id_veiculo = v.id
            LEFT JOIN oficina.tipo_servico ts ON a.id_tipo_servico = ts.id
            LEFT JOIN oficina.ordem_servico os ON a.id = os.id_agendamento
            WHERE a.id_cliente = :id_cliente
            ORDER BY a.dt_hora_agendamento DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id_cliente' => $idCliente]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}