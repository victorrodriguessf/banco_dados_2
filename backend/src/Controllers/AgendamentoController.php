<?php

namespace App\Controllers;

use App\Models\AgendamentoModel;
use PDOException;

class AgendamentoController
{
    private const STATUS_PERMITIDOS = [
        'aberta',
        'em_andamento',
        'aguardando_peca',
        'concluida',
        'cancelada',
    ];

    private const TRANSICOES_STATUS = [
        'aberta' => ['em_andamento', 'cancelada'],
        'em_andamento' => ['aguardando_peca', 'concluida', 'cancelada'],
        'aguardando_peca' => ['em_andamento', 'cancelada'],
        'concluida' => [],
        'cancelada' => [],
    ];

    private AgendamentoModel $agendamentoModel;

    public function __construct()
    {
        $this->agendamentoModel = new AgendamentoModel();
    }

    public function abrirOrdemServico(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->autenticar();

        $dados = $this->obterDadosRequisicao();

        $idCliente = isset($dados['id_cliente']) ? (int) $dados['id_cliente'] : null;
        $idVeiculo = isset($dados['id_veiculo']) ? (int) $dados['id_veiculo'] : null;
        $idTipoServico = isset($dados['id_tipo_servico']) ? (int) $dados['id_tipo_servico'] : null;
        $dtHoraAgendamento = trim((string) ($dados['dt_hora_agendamento'] ?? ''));
        $hodometroInicial = $this->normalizarNumero($dados['hodometro_inicial'] ?? null);
        $hodometroFinal = $this->normalizarNumero($dados['hodometro_final'] ?? $hodometroInicial);

        if (
            $idCliente === null
            || $idVeiculo === null
            || $idTipoServico === null
            || $dtHoraAgendamento === ''
            || $hodometroInicial === null
            || $hodometroFinal === null
        ) {
            $this->responderErro(
                400,
                'Campos obrigatorios: id_cliente, id_veiculo, id_tipo_servico, dt_hora_agendamento, hodometro_inicial.'
            );
        }

        if ($idCliente <= 0 || $idVeiculo <= 0 || $idTipoServico <= 0) {
            $this->responderErro(400, 'IDs informados devem ser maiores que zero.');
        }

        if (!$this->dataHoraValida($dtHoraAgendamento)) {
            $this->responderErro(400, 'dt_hora_agendamento invalido. Use um formato de data e hora valido.');
        }

        if (!$this->agendamentoModel->clienteExiste($idCliente)) {
            $this->responderErro(422, 'Cliente nao encontrado.');
        }

        if (!$this->agendamentoModel->veiculoPertenceAoCliente($idVeiculo, $idCliente)) {
            $this->responderErro(422, 'Veiculo nao encontrado para o cliente informado.');
        }

        if (!$this->agendamentoModel->tipoServicoExiste($idTipoServico)) {
            $this->responderErro(422, 'Tipo de servico nao encontrado.');
        }

        try {
            $resultado = $this->agendamentoModel->abrirOrdemServico([
                'id_cliente' => $idCliente,
                'id_veiculo' => $idVeiculo,
                'id_tipo_servico' => $idTipoServico,
                'dt_hora_agendamento' => $dtHoraAgendamento,
                'hodometro_inicial' => $hodometroInicial,
                'hodometro_final' => $hodometroFinal,
                'status' => 'aberta',
            ]);

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Ordem de servico aberta com sucesso.',
                'agendamento' => $resultado['agendamento'],
                'ordem_servico' => $resultado['ordem_servico'],
            ]);
            exit();
        } catch (PDOException $e) {
            $this->responderErro(500, 'Erro ao abrir ordem de servico.');
        }
    }

    public function atualizarStatusOrdemServico(int $idAgendamento): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->autenticar();

        if ($idAgendamento <= 0) {
            $this->responderErro(400, 'ID do agendamento invalido.');
        }

        $dados = $this->obterDadosRequisicao();
        $status = $this->normalizarStatus($dados['status'] ?? '');

        if ($status === '') {
            $this->responderErro(400, 'Informe o status da ordem de servico.');
        }

        if (!in_array($status, self::STATUS_PERMITIDOS, true)) {
            $this->responderErro(
                400,
                'Status invalido. Use: aberta, em_andamento, aguardando_peca, concluida ou cancelada.'
            );
        }

        try {
            $ordemServico = $this->agendamentoModel->buscarOrdemServicoPorAgendamento($idAgendamento);

            if (!$ordemServico) {
                $this->responderErro(404, 'Ordem de servico nao encontrada para o agendamento informado.');
            }

            $statusAtual = $ordemServico['status'] ?? 'aberta';

            if ($statusAtual === $status) {
                echo json_encode([
                    'mensagem' => 'Status da ordem de servico mantido.',
                    'ordem_servico' => $ordemServico,
                ]);
                exit();
            }

            if (!in_array($status, self::TRANSICOES_STATUS[$statusAtual] ?? [], true)) {
                $this->responderErro(
                    422,
                    "Transicao de status invalida: {$statusAtual} para {$status}."
                );
            }

            $ordemServicoAtualizada = $this->agendamentoModel->atualizarStatusOrdemServico($idAgendamento, $status);

            echo json_encode([
                'mensagem' => 'Status da ordem de servico atualizado com sucesso.',
                'ordem_servico' => $ordemServicoAtualizada,
            ]);
            exit();
        } catch (PDOException $e) {
            $this->responderErro(500, 'Erro ao atualizar status da ordem de servico.');
        }
    }

    private function autenticar(): void
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if ($authHeader === '' && function_exists('getallheaders')) {
            $headers = array_change_key_case(getallheaders(), CASE_LOWER);
            $authHeader = $headers['authorization'] ?? '';
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            $this->responderErro(401, 'Token de acesso nao informado.');
        }

        $token = substr($authHeader, 7);
        $partes = explode('.', $token);

        if (count($partes) !== 3) {
            $this->responderErro(401, 'Token invalido.');
        }

        [$base64Header, $base64Payload, $base64Assinatura] = $partes;

        $assinaturaEsperada = $this->base64UrlEncode(
            hash_hmac('sha256', "$base64Header.$base64Payload", $_ENV['JWT_SECRET'] ?? 'dev-secret-change-me', true)
        );

        if (!hash_equals($assinaturaEsperada, $base64Assinatura)) {
            $this->responderErro(401, 'Token invalido.');
        }

        $payload = json_decode(base64_decode(strtr($base64Payload, '-_', '+/')), true);

        if (!is_array($payload) || ($payload['exp'] ?? 0) < time()) {
            $this->responderErro(401, 'Token expirado.');
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

    private function normalizarNumero(mixed $valor): float|int|null
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (!is_numeric($valor)) {
            return null;
        }

        return $valor + 0;
    }

    private function dataHoraValida(string $valor): bool
    {
        return strtotime($valor) !== false;
    }

    private function normalizarStatus(string $valor): string
    {
        return strtolower(trim(str_replace('-', '_', $valor)));
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
