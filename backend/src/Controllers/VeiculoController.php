<?php

namespace App\Controllers;

use App\Models\VeiculoModel;

class VeiculoController
{
    private VeiculoModel $veiculoModel;

    public function __construct()
    {
        $this->veiculoModel = new VeiculoModel();
    }

    public function buscar(string $placa): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->autenticar();

        $veiculo = $this->veiculoModel->buscarPorPlaca($placa);

        if (!$veiculo) {
            http_response_code(404);
            echo json_encode(['erro' => 'Veiculo nao encontrado.']);
            exit();
        }

        echo json_encode($veiculo);
        exit();
    }

    public function cadastrar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->autenticar();

        $dados = $this->obterDadosRequisicao();

        $idCliente    = isset($dados['id_cliente'])     ? (int) $dados['id_cliente']     : null;
        $anoFabricao  = isset($dados['ano_fabricacao']) ? (int) $dados['ano_fabricacao'] : null;
        $anoModelo    = isset($dados['ano_modelo'])     ? (int) $dados['ano_modelo']     : null;
        $placa        = isset($dados['placa'])          ? trim($dados['placa'])          : '';

        if ($idCliente === null || $anoFabricao === null || $anoModelo === null || $placa === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'Campos obrigatorios: id_cliente, ano_fabricacao, ano_modelo, placa.']);
            exit();
        }

        if (!$this->veiculoModel->clienteExiste($idCliente)) {
            http_response_code(422);
            echo json_encode(['erro' => 'Cliente nao encontrado.']);
            exit();
        }

        if ($this->veiculoModel->placaEmUso(strtoupper($placa))) {
            http_response_code(409);
            echo json_encode(['erro' => 'Placa ja cadastrada.']);
            exit();
        }

        $veiculo = $this->veiculoModel->cadastrar([
            'id_cliente'     => $idCliente,
            'marca'          => $dados['marca']       ?? null,
            'modelo'         => $dados['modelo']      ?? null,
            'ano_fabricacao' => $anoFabricao,
            'ano_modelo'     => $anoModelo,
            'placa'          => $placa,
            'motorizacao'    => $dados['motorizacao'] ?? null,
        ]);

        http_response_code(201);
        echo json_encode(['mensagem' => 'Veiculo cadastrado com sucesso.', 'veiculo' => $veiculo]);
        exit();
    }

    private function autenticar(): void
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if ($authHeader === '' && function_exists('getallheaders')) {
            $headers = array_change_key_case(getallheaders(), CASE_LOWER);
            $authHeader = $headers['authorization'] ?? '';
        }

        if (!str_starts_with($authHeader, 'Bearer ')) {
            http_response_code(401);
            echo json_encode(['erro' => 'Token de acesso nao informado.']);
            exit();
        }

        $token = substr($authHeader, 7);
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
}
