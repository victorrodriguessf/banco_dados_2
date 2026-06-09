-- ==============================================
-- CLIENTE - 200 registros
-- ==============================================

INSERT INTO oficina.cliente (
    id,
    nome_completo,
    cpf,
    cnpj,
    telefone,
    email
)
SELECT
    gs,
    CASE
        WHEN gs <= 120 THEN 'Cliente PF ' || gs
        ELSE 'Empresa PJ ' || gs
    END,
    CASE
        WHEN gs <= 120 THEN LPAD(gs::TEXT, 11, '0')
        ELSE NULL
    END,
    CASE
        WHEN gs > 120 THEN LPAD(gs::TEXT, 14, '0')
        ELSE NULL
    END,
    '(84) 9' || LPAD(gs::TEXT, 8, '0'),
    'cliente' || gs || '@email.com'
FROM generate_series(1, 200) gs;

-- ==============================================
-- VEICULO - 500 registros
-- ==============================================

INSERT INTO oficina.veiculo (
    id,
    id_cliente,
    marca,
    modelo,
    ano_fabricacao,
    ano_modelo,
    placa,
    motorizacao
)
SELECT
    gs,
    ((gs - 1) % 200) + 1,
    CASE (gs % 8)
        WHEN 0 THEN 'Toyota'
        WHEN 1 THEN 'Honda'
        WHEN 2 THEN 'Volkswagen'
        WHEN 3 THEN 'Fiat'
        WHEN 4 THEN 'Chevrolet'
        WHEN 5 THEN 'Hyundai'
        WHEN 6 THEN 'Renault'
        ELSE 'Ford'
    END,
    'Modelo ' || gs,
    2010 + (gs % 15),
    2011 + (gs % 15),
    'ABC' || LPAD(gs::TEXT, 4, '0'),
    CASE (gs % 5)
        WHEN 0 THEN '1.0 Flex'
        WHEN 1 THEN '1.6 Flex'
        WHEN 2 THEN '2.0 Flex'
        WHEN 3 THEN 'Diesel'
        ELSE 'Elétrico'
    END
FROM generate_series(1, 500) gs;

-- ==============================================
-- FUNCIONARIO - 50 registros
-- Cargo/especialidade incluídos no nome, pois não existem colunas próprias
-- ==============================================

INSERT INTO oficina.funcionario (
    id,
    matricula,
    nome_completo,
    dt_nascimento,
    telefone,
    email
)
SELECT
    gs,
    'MAT' || LPAD(gs::TEXT, 4, '0'),
    'Funcionário ' || gs || ' - ' ||
    CASE (gs % 5)
        WHEN 0 THEN 'Mecânico'
        WHEN 1 THEN 'Eletricista automotivo'
        WHEN 2 THEN 'Consultor técnico'
        WHEN 3 THEN 'Funileiro'
        ELSE 'Gerente de oficina'
    END || ' - Especialidade: ' ||
    CASE (gs % 6)
        WHEN 0 THEN 'Motor'
        WHEN 1 THEN 'Suspensão'
        WHEN 2 THEN 'Freios'
        WHEN 3 THEN 'Injeção eletrônica'
        WHEN 4 THEN 'Ar-condicionado'
        ELSE 'Diagnóstico eletrônico'
    END,
    DATE '1980-01-01' + (gs * INTERVAL '120 days'),
    '(84) 8' || LPAD(gs::TEXT, 8, '0'),
    'funcionario' || gs || '@oficina.com'
FROM generate_series(1, 50) gs;

-- ==============================================
-- TIPO_SERVICO - 20 registros
-- ==============================================

INSERT INTO oficina.tipo_servico (
    id,
    descricao,
    tempo_medio_execucao
)
VALUES
(1,  'Troca de óleo', 40),
(2,  'Alinhamento', 60),
(3,  'Balanceamento', 50),
(4,  'Revisão preventiva', 180),
(5,  'Troca de pastilhas de freio', 90),
(6,  'Diagnóstico eletrônico', 60),
(7,  'Troca de bateria', 30),
(8,  'Higienização de ar-condicionado', 70),
(9,  'Troca de correia dentada', 240),
(10, 'Suspensão dianteira', 180),
(11, 'Suspensão traseira', 180),
(12, 'Troca de velas', 60),
(13, 'Limpeza de bicos', 90),
(14, 'Troca de pneus', 80),
(15, 'Reparo elétrico', 120),
(16, 'Troca de amortecedores', 150),
(17, 'Revisão de freios', 120),
(18, 'Troca de filtros', 50),
(19, 'Serviço de funilaria', 300),
(20, 'Pintura parcial', 360);

-- ==============================================
-- PECA - 40 registros
-- Alguns itens abaixo do estoque mínimo
-- ==============================================

INSERT INTO oficina.peca (
    id,
    fabricante,
    descricao,
    quantidade_em_estoque,
    quantidade_minima
)
SELECT
    gs,
    CASE (gs % 5)
        WHEN 0 THEN 'Bosch'
        WHEN 1 THEN 'Moura'
        WHEN 2 THEN 'Magneti Marelli'
        WHEN 3 THEN 'NGK'
        ELSE 'Cofap'
    END,
    CASE (gs % 10)
        WHEN 0 THEN 'Filtro de óleo'
        WHEN 1 THEN 'Pastilha de freio'
        WHEN 2 THEN 'Vela de ignição'
        WHEN 3 THEN 'Correia dentada'
        WHEN 4 THEN 'Amortecedor'
        WHEN 5 THEN 'Bateria'
        WHEN 6 THEN 'Filtro de ar'
        WHEN 7 THEN 'Disco de freio'
        WHEN 8 THEN 'Sensor eletrônico'
        ELSE 'Lâmpada automotiva'
    END || ' ' || gs,
    CASE
        WHEN gs % 7 = 0 THEN 2
        ELSE 10 + (gs % 30)
    END,
    5 + (gs % 10)
FROM generate_series(1, 40) gs;

-- ==============================================
-- FORMA_PAGAMENTO
-- ==============================================

INSERT INTO oficina.forma_pagamento (
    id,
    descricao
)
VALUES
(1, 'Dinheiro'),
(2, 'Pix'),
(3, 'Cartão de crédito'),
(4, 'Cartão de débito'),
(5, 'Boleto'),
(6, 'Transferência bancária');

-- ==============================================
-- AGENDAMENTO - 3.500 registros
-- O schema original não possui coluna status
-- ==============================================

INSERT INTO oficina.agendamento (
    id,
    id_cliente,
    id_veiculo,
    id_tipo_servico,
    dt_hora_agendamento
)
SELECT
    gs,
    ((gs - 1) % 200) + 1,
    ((gs - 1) % 500) + 1,
    ((gs - 1) % 20) + 1,
    CURRENT_TIMESTAMP - (gs || ' hours')::INTERVAL
FROM generate_series(1, 3500) gs;

-- ==============================================
-- ORDEM_SERVICO - 3.000 registros
-- Consideradas como OS concluídas para pagamento e avaliação
-- ==============================================

INSERT INTO oficina.ordem_servico (
    id,
    id_cliente,
    hodometro_inicial,
    hodometro_final,
    status,
    id_agendamento
)
SELECT
    gs,
    ((gs - 1) % 200) + 1,
    10000 + (gs * 12),
    10000 + (gs * 12) + 35,
    'concluida',
    ((gs - 1) % 20) + 1
FROM generate_series(1, 3000) gs;

-- ==============================================
-- ITEM_SERVICO - 7.000 registros
-- ==============================================

INSERT INTO oficina.item_servico (
    id,
    id_tipo_servico,
    id_os,
    id_funcionario,
    valor_mao_de_obra,
    observacao
)
SELECT
    gs,
    ((gs - 1) % 20) + 1,
    ((gs - 1) % 3000) + 1,
    ((gs - 1) % 50) + 1,
    80.00 + (gs % 400),
    CASE
        WHEN gs % 5 = 0 THEN 'Serviço concluído sem intercorrências.'
        WHEN gs % 5 = 1 THEN 'Serviço realizado após diagnóstico técnico.'
        WHEN gs % 5 = 2 THEN 'Cliente autorizado antes da execução.'
        WHEN gs % 5 = 3 THEN 'Peças solicitadas ao estoque.'
        ELSE 'Serviço executado conforme padrão da oficina.'
    END
FROM generate_series(1, 7000) gs;

-- ==============================================
-- ITEM_PECA - 5.000 registros
-- ==============================================

INSERT INTO oficina.item_peca (
    id,
    id_peca,
    id_item_servico,
    quantidade,
    dt_hora_solicitacao,
    devolucao
)
SELECT
    gs,
    ((gs - 1) % 40) + 1,
    ((gs - 1) % 7000) + 1,
    1 + (gs % 4),
    CURRENT_TIMESTAMP - (gs || ' minutes')::INTERVAL,
    CASE
        WHEN gs % 17 = 0 THEN TRUE
        ELSE FALSE
    END
FROM generate_series(1, 5000) gs;

-- ==============================================
-- PAGAMENTO - 3.000 registros
-- Apenas para OS consideradas concluídas
-- ==============================================

INSERT INTO oficina.pagamento (
    id,
    id_forma_pagamento,
    id_cliente,
    id_os,
    valor_parcial,
    valor_pago
)
SELECT
    gs,
    ((gs - 1) % 6) + 1,
    ((gs - 1) % 200) + 1,
    gs,
    CASE
        WHEN gs % 3 = 0 THEN 50.00
        ELSE 0.00
    END,
    150.00 + (gs % 500)
FROM generate_series(1, 3000) gs;

-- ==============================================
-- AVALIACAO - 2.200 registros
-- Sobre itens de serviço vinculados às OS existentes
-- ==============================================

INSERT INTO oficina.avaliacao (
    id,
    id_item_servico,
    nota,
    comentario
)
SELECT
    gs,
    gs,
    1 + (gs % 5),
    CASE
        WHEN gs % 5 = 0 THEN 'Excelente atendimento.'
        WHEN gs % 5 = 1 THEN 'Serviço realizado dentro do esperado.'
        WHEN gs % 5 = 2 THEN 'Bom custo-benefício.'
        WHEN gs % 5 = 3 THEN 'Atendimento regular.'
        ELSE 'Poderia melhorar o prazo.'
    END
FROM generate_series(1, 2200) gs;
