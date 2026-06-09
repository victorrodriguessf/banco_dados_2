<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use PDOException;

class ClienteController
{
    private ClienteModel $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new ClienteModel();
    }

    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->autenticar();

        $dados = $this->obterDadosRequisicao();

        $nomeCompleto = trim($dados['nome_completo'] ?? '');
        $telefone = trim($dados['telefone'] ?? '');
        $email = $this->normalizarTextoOpcional($dados['email'] ?? null);
        $cpf = $this->somenteNumeros($dados['cpf'] ?? '');
        $cnpj = $this->somenteNumeros($dados['cnpj'] ?? '');

        if ($nomeCompleto === '' || $telefone === '') {
            $this->responderErro(400, 'Informe nome_completo e telefone.');
        }

        if (($cpf === '' && $cnpj === '') || ($cpf !== '' && $cnpj !== '')) {
            $this->responderErro(400, 'Informe CPF ou CNPJ, mas nao ambos.');
        }

        if ($cpf !== '' && strlen($cpf) !== 11) {
            $this->responderErro(400, 'CPF deve conter 11 digitos.');
        }

        if ($cnpj !== '' && strlen($cnpj) !== 14) {
            $this->responderErro(400, 'CNPJ deve conter 14 digitos.');
        }

        try {
            $cliente = $this->clienteModel->criar([
                'nome_completo' => $nomeCompleto,
                'cpf' => $cpf !== '' ? $cpf : null,
                'cnpj' => $cnpj !== '' ? $cnpj : null,
                'telefone' => $telefone,
                'email' => $email,
            ]);

            http_response_code(201);
            echo json_encode($cliente);
            exit();
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') {
                $this->responderErro(409, 'CPF ou CNPJ ja cadastrado.');
            }

            $this->responderErro(500, 'Erro ao cadastrar cliente.');
        }
    }

    public function buscarPorId(int $id): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->autenticar();

        if ($id <= 0) {
            $this->responderErro(400, 'ID do cliente invalido.');
        }

        try {
            $cliente = $this->clienteModel->buscarPorIdComVeiculos($id);

            if ($cliente === null) {
                $this->responderErro(404, 'Cliente nao encontrado.');
            }

            echo json_encode($cliente);
            exit();
        } catch (PDOException $e) {
            $this->responderErro(500, 'Erro ao buscar cliente.');
        }
    }

    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $this->autenticar();

        $nome = $this->normalizarTextoOpcional($_GET['nome'] ?? null);
        $tipo = $this->normalizarTipoPessoa($_GET['tipo'] ?? null);

        if ($tipo === false) {
            $this->responderErro(400, 'Tipo de pessoa invalido. Use fisica ou juridica.');
        }

        try {
            echo json_encode($this->clienteModel->listar([
                'nome' => $nome,
                'tipo' => $tipo,
            ]));
            exit();
        } catch (PDOException $e) {
            $this->responderErro(500, 'Erro ao listar clientes.');
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

    private function base64UrlEncode(string $dados): string
    {
        return rtrim(strtr(base64_encode($dados), '+/', '-_'), '=');
    }

    private function normalizarTextoOpcional(?string $valor): ?string
    {
        $valor = trim((string) $valor);

        return $valor !== '' ? $valor : null;
    }

    private function somenteNumeros(?string $valor): string
    {
        return preg_replace('/\D/', '', (string) $valor);
    }

    private function normalizarTipoPessoa(?string $valor): string|false|null
    {
        $valor = strtolower(trim((string) $valor));

        if ($valor === '') {
            return null;
        }

        $tipos = [
            'fisica' => ['fisica', 'pf', 'cpf', 'pessoa_fisica'],
            'juridica' => ['juridica', 'pj', 'cnpj', 'pessoa_juridica'],
        ];

        foreach ($tipos as $tipo => $aliases) {
            if (in_array($valor, $aliases, true)) {
                return $tipo;
            }
        }

        return false;
    }

    private function responderErro(int $statusCode, string $mensagem): void
    {
        http_response_code($statusCode);
        echo json_encode(['erro' => $mensagem]);
        exit();
    }
}
