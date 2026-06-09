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

    public function tipoServicoExiste(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM oficina.tipo_servico WHERE id = :id');
        $stmt->execute([':id' => $id]);
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
}
