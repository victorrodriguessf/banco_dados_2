<?php

namespace App\Controllers;

use App\Models\AgendamentoModel;
use PDOException;

class AgendamentoController
{
    private AgendamentoModel $agendamentoModel;

    public function __construct()
    {
        $this->agendamentoModel = new AgendamentoModel();
    }

    public function adicionarItemServico(int $idAgendamento): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->autenticar();

        $os = $this->resolverOsAberta($idAgendamento);

        $dados = $this->obterDadosRequisicao();

        $idTipoServico  = isset($dados['id_tipo_servico'])  ? (int) $dados['id_tipo_servico']  : null;
        $idFuncionario  = isset($dados['id_funcionario'])   ? (int) $dados['id_funcionario']   : null;
        $valorMaoDeObra = isset($dados['valor_mao_de_obra']) ? (float) $dados['valor_mao_de_obra'] : null;
        $observacao     = isset($dados['observacao'])        ? trim($dados['observacao'])        : null;

        if ($idTipoServico === null) {
            $this->responderErro(400, 'Campo obrigatorio: id_tipo_servico.');
        }

        if (!$this->agendamentoModel->tipoServicoExiste($idTipoServico)) {
            $this->responderErro(422, 'Tipo de servico nao encontrado.');
        }

        if ($idFuncionario !== null && !$this->agendamentoModel->funcionarioExiste($idFuncionario)) {
            $this->responderErro(422, 'Funcionario nao encontrado.');
        }

        try {
            $item = $this->agendamentoModel->adicionarItemServico([
                'id_tipo_servico'   => $idTipoServico,
                'id_os'             => $os['id'],
                'id_funcionario'    => $idFuncionario,
                'valor_mao_de_obra' => $valorMaoDeObra,
                'observacao'        => $observacao !== '' ? $observacao : null,
            ]);

            http_response_code(201);
            echo json_encode(['mensagem' => 'Item de servico adicionado com sucesso.', 'item_servico' => $item]);
            exit();
        } catch (PDOException) {
            $this->responderErro(500, 'Erro ao adicionar item de servico.');
        }
    }

    public function adicionarItemPeca(int $idAgendamento): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->autenticar();

        $os = $this->resolverOsAberta($idAgendamento);

        $dados = $this->obterDadosRequisicao();

        $idPeca         = isset($dados['id_peca'])         ? (int)   $dados['id_peca']         : null;
        $idItemServico  = isset($dados['id_item_servico']) ? (int)   $dados['id_item_servico'] : null;
        $quantidade     = isset($dados['quantidade'])      ? (float) $dados['quantidade']      : null;
        $devolucao      = filter_var($dados['devolucao'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if ($idPeca === null || $idItemServico === null || $quantidade === null) {
            $this->responderErro(400, 'Campos obrigatorios: id_peca, id_item_servico, quantidade.');
        }

        if ($quantidade <= 0) {
            $this->responderErro(400, 'A quantidade deve ser maior que zero.');
        }

        if (!$this->agendamentoModel->pecaExiste($idPeca)) {
            $this->responderErro(422, 'Peca nao encontrada.');
        }

        if (!$this->agendamentoModel->itemServicoExiste($idItemServico)) {
            $this->responderErro(422, 'Item de servico nao encontrado.');
        }

        if (!$this->agendamentoModel->itemServicoPertenceAOs($idItemServico, $os['id'])) {
            $this->responderErro(422, 'Item de servico nao pertence a esta OS.');
        }

        if (!$devolucao && !$this->agendamentoModel->pecaTemEstoque($idPeca, $quantidade)) {
            $this->responderErro(422, 'Estoque insuficiente para a quantidade solicitada.');
        }

        try {
            $item = $this->agendamentoModel->adicionarItemPeca([
                'id_peca'         => $idPeca,
                'id_item_servico' => $idItemServico,
                'quantidade'      => $quantidade,
                'devolucao'       => $devolucao,
            ]);

            http_response_code(201);
            echo json_encode(['mensagem' => 'Peca adicionada com sucesso.', 'item_peca' => $item]);
            exit();
        } catch (PDOException) {
            $this->responderErro(500, 'Erro ao adicionar peca.');
        }
    }

    public function registrarPagamento(int $idAgendamento): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->autenticar();

        if (!$this->agendamentoModel->agendamentoExiste($idAgendamento)) {
            $this->responderErro(404, 'Agendamento nao encontrado.');
        }

        $os = $this->agendamentoModel->buscarOsPorAgendamento($idAgendamento);

        if ($os === false) {
            $this->responderErro(422, 'Nenhuma OS vinculada a este agendamento.');
        }

        $dados = $this->obterDadosRequisicao();

        $idFormaPagamento = isset($dados['id_forma_pagamento']) ? (int)   $dados['id_forma_pagamento'] : null;
        $valorPago        = isset($dados['valor_pago'])         ? (float) $dados['valor_pago']         : null;
        $valorParcial     = isset($dados['valor_parcial'])      ? (float) $dados['valor_parcial']      : 0;

        if ($idFormaPagamento === null || $valorPago === null) {
            $this->responderErro(400, 'Campos obrigatorios: id_forma_pagamento, valor_pago.');
        }

        if ($valorPago <= 0) {
            $this->responderErro(400, 'O valor pago deve ser maior que zero.');
        }

        if (!$this->agendamentoModel->formaPagamentoExiste($idFormaPagamento)) {
            $this->responderErro(422, 'Forma de pagamento nao encontrada.');
        }

        try {
            $pagamento = $this->agendamentoModel->registrarPagamento([
                'id_forma_pagamento' => $idFormaPagamento,
                'id_cliente'         => $os['id_cliente'],
                'id_os'              => $os['id'],
                'valor_parcial'      => $valorParcial,
                'valor_pago'         => $valorPago,
            ]);

            http_response_code(201);
            echo json_encode(['mensagem' => 'Pagamento registrado com sucesso.', 'pagamento' => $pagamento]);
            exit();
        } catch (PDOException) {
            $this->responderErro(500, 'Erro ao registrar pagamento.');
        }
    }

    public function registrarAvaliacao(int $idAgendamento): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->autenticar();

        if (!$this->agendamentoModel->agendamentoExiste($idAgendamento)) {
            $this->responderErro(404, 'Agendamento nao encontrado.');
        }

        $os = $this->agendamentoModel->buscarOsPorAgendamento($idAgendamento);

        if ($os === false) {
            $this->responderErro(422, 'Nenhuma OS vinculada a este agendamento.');
        }

        $dados = $this->obterDadosRequisicao();

        $idItemServico = isset($dados['id_item_servico']) ? (int) $dados['id_item_servico'] : null;
        $nota          = isset($dados['nota'])            ? (int) $dados['nota']            : null;
        $comentario    = isset($dados['comentario'])      ? trim($dados['comentario'])       : null;

        if ($idItemServico === null || $nota === null) {
            $this->responderErro(400, 'Campos obrigatorios: id_item_servico, nota.');
        }

        if ($nota < 1 || $nota > 5) {
            $this->responderErro(400, 'A nota deve ser um valor entre 1 e 5.');
        }

        if (!$this->agendamentoModel->itemServicoExiste($idItemServico)) {
            $this->responderErro(422, 'Item de servico nao encontrado.');
        }

        if (!$this->agendamentoModel->itemServicoPertenceAOs($idItemServico, $os['id'])) {
            $this->responderErro(422, 'Item de servico nao pertence a esta OS.');
        }

        try {
            $avaliacao = $this->agendamentoModel->registrarAvaliacao([
                'id_item_servico' => $idItemServico,
                'nota'            => $nota,
                'comentario'      => $comentario !== '' ? $comentario : null,
            ]);

            http_response_code(201);
            echo json_encode(['mensagem' => 'Avaliacao registrada com sucesso.', 'avaliacao' => $avaliacao]);
            exit();
        } catch (PDOException) {
            $this->responderErro(500, 'Erro ao registrar avaliacao.');
        }
    }

    private function resolverOsAberta(int $idAgendamento): array
    {
        if (!$this->agendamentoModel->agendamentoExiste($idAgendamento)) {
            $this->responderErro(404, 'Agendamento nao encontrado.');
        }

        $os = $this->agendamentoModel->buscarOsPorAgendamento($idAgendamento);

        if ($os === false) {
            $this->responderErro(422, 'Nenhuma OS vinculada a este agendamento.');
        }

        if ($this->agendamentoModel->osPossuiPagamento($os['id'])) {
            $this->responderErro(422, 'A OS ja possui pagamento registrado e nao pode ser alterada.');
        }

        return $os;
    }

    private function autenticar(): void
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if ($authHeader === '' && function_exists('getallheaders')) {
            $headers    = array_change_key_case(getallheaders(), CASE_LOWER);
            $authHeader = $headers['authorization'] ?? '';
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['erro' => 'Token de acesso nao informado.']);
            exit();
        }

        $token  = substr($authHeader, 7);
        $partes = explode('.', $token);

        if (count($partes) !== 3) {
            http_response_code(401);
            echo json_encode(['erro' => 'Token invalido.']);
            exit();
        }

        [$base64Header, $base64Payload, $base64Assinatura] = $partes;

        $assinaturaEsperada = $this->base64UrlEncode(
            hash_hmac('sha256', "$base64Header.$base64Payload", $_ENV['JWT_SECRET'] ?? 'dev-secret-change-me', true)
        );

        if (!hash_equals($assinaturaEsperada, $base64Assinatura)) {
            http_response_code(401);
            echo json_encode(['erro' => 'Token invalido.']);
            exit();
        }

        $payload = json_decode(base64_decode(strtr($base64Payload, '-_', '+/')), true);

        if (!is_array($payload) || ($payload['exp'] ?? 0) < time()) {
            http_response_code(401);
            echo json_encode(['erro' => 'Token expirado.']);
            exit();
        }
    }

    private function obterDadosRequisicao(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $json = json_decode(file_get_contents('php://input'), true);

        return is_array($json) ? $json : [];
    }

    private function base64UrlEncode(string $dados): string
    {
        return rtrim(strtr(base64_encode($dados), '+/', '-_'), '=');
    }

    private function responderErro(int $statusCode, string $mensagem): void
    {
        http_response_code($statusCode);
        echo json_encode(['erro' => $mensagem]);
        exit();
    }
}
