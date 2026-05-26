-- ==============================================
-- SCHEMA: Sistema de Gestão de Oficina
-- ==============================================
CREATE SCHEMA oficina;

-- 1. cliente
CREATE TABLE oficina.cliente (
    id            INTEGER PRIMARY KEY,
    nome_completo TEXT NOT NULL,
    cpf           VARCHAR(11) UNIQUE,
    cnpj          VARCHAR(14) UNIQUE,
    dt_cadastro   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    telefone      TEXT NOT NULL,
    email         TEXT
);

-- 2. veiculo
CREATE TABLE oficina.veiculo (
    id             INTEGER PRIMARY KEY,
    id_cliente     INTEGER NOT NULL REFERENCES oficina.cliente(id),
    marca          TEXT,
    modelo         TEXT,
    ano_fabricacao INTEGER NOT NULL,
    ano_modelo     INTEGER NOT NULL,
    placa          TEXT NOT NULL UNIQUE,
    motorizacao    TEXT
);

-- 3. funcionario
CREATE TABLE oficina.funcionario (
    id            INTEGER PRIMARY KEY,
    matricula     TEXT NOT NULL UNIQUE,
    nome_completo TEXT,
    dt_nascimento DATE,
    telefone      TEXT NOT NULL,
    email         TEXT
);

-- 4. tipo_servico
CREATE TABLE oficina.tipo_servico (
    id                   INTEGER PRIMARY KEY,
    descricao            TEXT NOT NULL,
    tempo_medio_execucao INTEGER
);

-- 5. peca
CREATE TABLE oficina.peca (
    id                    INTEGER PRIMARY KEY,
    fabricante            TEXT,
    descricao             TEXT NOT NULL,
    quantidade_em_estoque NUMERIC NOT NULL,
    quantidade_minima     NUMERIC NOT NULL
);

-- 6. forma_pagamento
CREATE TABLE oficina.forma_pagamento (
    id        INTEGER PRIMARY KEY,
    descricao TEXT NOT NULL
);

-- 7. agendamento
CREATE TABLE oficina.agendamento (
    id                  INTEGER PRIMARY KEY,
    id_cliente          INTEGER NOT NULL REFERENCES oficina.cliente(id),
    id_veiculo          INTEGER NOT NULL REFERENCES oficina.veiculo(id),
    id_tipo_servico     INTEGER NOT NULL REFERENCES oficina.tipo_servico(id),
    dt_hora_agendamento TIMESTAMP NOT NULL
);

-- 8. ordem_servico
CREATE TABLE oficina.ordem_servico (
    id               INTEGER PRIMARY KEY,
    id_cliente       INTEGER NOT NULL REFERENCES oficina.cliente(id),
    hodometro_inicial NUMERIC NOT NULL,
    hodometro_final   NUMERIC NOT NULL,
    id_agendamento   INTEGER NOT NULL REFERENCES oficina.agendamento(id)
);

-- 9. item_servico
CREATE TABLE oficina.item_servico (
    id               INTEGER PRIMARY KEY,
    id_tipo_servico  INTEGER REFERENCES oficina.tipo_servico(id),
    id_os            INTEGER REFERENCES oficina.ordem_servico(id),
    id_funcionario   INTEGER REFERENCES oficina.funcionario(id),
    valor_mao_de_obra NUMERIC,
    observacao       TEXT
);

-- 10. item_peca
CREATE TABLE oficina.item_peca (
    id                 INTEGER PRIMARY KEY,
    id_peca            INTEGER NOT NULL REFERENCES oficina.peca(id),
    id_item_servico    INTEGER NOT NULL REFERENCES oficina.item_servico(id),
    quantidade         NUMERIC NOT NULL,
    dt_hora_solicitacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    devolucao          BOOLEAN NOT NULL DEFAULT false
);

-- 11. pagamento
CREATE TABLE oficina.pagamento (
    id               INTEGER PRIMARY KEY,
    id_forma_pagamento INTEGER NOT NULL REFERENCES oficina.forma_pagamento(id),
    id_cliente       INTEGER NOT NULL REFERENCES oficina.cliente(id),
    id_os            INTEGER NOT NULL REFERENCES oficina.ordem_servico(id),
    valor_parcial    NUMERIC NOT NULL DEFAULT 0,
    valor_pago       NUMERIC NOT NULL
);

-- 12. avaliacao
CREATE TABLE oficina.avaliacao (
    id              INTEGER PRIMARY KEY,
    id_item_servico INTEGER NOT NULL REFERENCES oficina.item_servico(id),
    nota            INTEGER NOT NULL,
    comentario      TEXT
);