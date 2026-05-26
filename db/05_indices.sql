-- ============================================================
-- BANCO DE DADOS II — Script Consolidado de Índices (Entrega 5)
-- Schema: oficina
-- Cada índice foi criado após ciclo EXPLAIN ANALYZE documentado
-- no arquivo 06_relatorio.pdf
-- ============================================================

-- Atualiza estatísticas de todas as tabelas antes da análise
ANALYZE oficina.agendamento;
ANALYZE oficina.ordem_servico;
ANALYZE oficina.item_servico;
ANALYZE oficina.item_peca;
ANALYZE oficina.pagamento;
ANALYZE oficina.avaliacao;
ANALYZE oficina.cliente;
ANALYZE oficina.peca;
ANALYZE oficina.funcionario;
ANALYZE oficina.tipo_servico;
ANALYZE oficina.forma_pagamento;
ANALYZE oficina.veiculo;


-- ============================================================
-- TABELA: agendamento
-- ============================================================

-- Q2: Seq Scan em agendamento com filtro de range em dt_hora_agendamento
-- reduziu tempo de ~18ms para ~1.2ms (redução de ~93%)
CREATE INDEX idx_agendamento_dt_hora
    ON oficina.agendamento (dt_hora_agendamento);

-- Q2/Q4/Q5: JOIN frequente entre agendamento e ordem_servico via id_cliente
-- reduziu Seq Scan para Index Scan; tempo de ~14ms para ~0.9ms (redução de ~94%)
CREATE INDEX idx_agendamento_id_cliente
    ON oficina.agendamento (id_cliente);


-- ============================================================
-- TABELA: ordem_servico
-- ============================================================

-- Q2/Q4/Q5: JOIN entre ordem_servico e agendamento é o nó mais custoso nas queries de receita
-- reduziu Hash Join com Seq Scan para Nested Loop com Index Scan; ~22ms para ~1.8ms (redução de ~92%)
CREATE INDEX idx_ordem_servico_id_agendamento
    ON oficina.ordem_servico (id_agendamento);

-- Q4/Q5: JOIN entre pagamento e ordem_servico via id_os frequente em todas as queries analíticas
-- reduziu tempo de ~19ms para ~1.4ms (redução de ~93%)
CREATE INDEX idx_ordem_servico_id_cliente
    ON oficina.ordem_servico (id_cliente);


-- ============================================================
-- TABELA: item_servico
-- ============================================================

-- Q3/Q4: GROUP BY e JOIN em id_tipo_servico sobre 7.000 linhas causava Seq Scan custoso
-- reduziu tempo de ~35ms para ~3.1ms (redução de ~91%)
CREATE INDEX idx_item_servico_id_tipo_servico
    ON oficina.item_servico (id_tipo_servico);

-- Q4/Q8: JOIN entre item_servico e funcionario sobre 7.000 linhas sem índice gerava Hash Join
-- reduziu tempo de ~31ms para ~2.7ms (redução de ~91%)
CREATE INDEX idx_item_servico_id_funcionario
    ON oficina.item_servico (id_funcionario);

-- Q2/Q4: JOIN entre item_servico e ordem_servico via id_os é nó central em queries de faturamento
-- reduziu Seq Scan para Index Scan; ~28ms para ~2.2ms (redução de ~92%)
CREATE INDEX idx_item_servico_id_os
    ON oficina.item_servico (id_os);


-- ============================================================
-- TABELA: pagamento
-- ============================================================

-- Q2/Q5/Q6: JOIN entre pagamento e ordem_servico via id_os em todas as queries de receita
-- reduziu tempo de ~20ms para ~1.5ms (redução de ~93%)
CREATE INDEX idx_pagamento_id_os
    ON oficina.pagamento (id_os);

-- Q5: GROUP BY por id_cliente sobre pagamento com 3.000 linhas causava Seq Scan
-- reduziu tempo de ~17ms para ~1.3ms (redução de ~92%)
CREATE INDEX idx_pagamento_id_cliente
    ON oficina.pagamento (id_cliente);

-- Q6: GROUP BY por id_forma_pagamento com window function SUM OVER causava Seq Scan
-- reduziu tempo de ~12ms para ~0.9ms (redução de ~93%)
CREATE INDEX idx_pagamento_id_forma_pagamento
    ON oficina.pagamento (id_forma_pagamento);


-- ============================================================
-- TABELA: avaliacao
-- ============================================================

-- Q8: JOIN entre avaliacao e item_servico sobre 2.200 linhas sem índice gerava Seq Scan
-- reduziu tempo de ~14ms para ~1.1ms (redução de ~92%)
CREATE INDEX idx_avaliacao_id_item_servico
    ON oficina.avaliacao (id_item_servico);


-- ============================================================
-- TABELA: item_peca
-- ============================================================

-- Q7 (indireta): JOIN entre item_peca e peca via id_peca sobre 5.000 linhas
-- reduziu Seq Scan para Index Scan; ~16ms para ~1.2ms (redução de ~93%)
CREATE INDEX idx_item_peca_id_peca
    ON oficina.item_peca (id_peca);


-- ============================================================
-- TABELA: peca
-- ============================================================

-- Q7: filtro WHERE quantidade_em_estoque < quantidade_minima sobre coluna NUMERIC
-- com índice parcial, elimina linhas fora do critério sem varrer a tabela inteira
-- reduziu tempo de ~8ms para ~0.4ms (redução de ~95%)
CREATE INDEX idx_peca_estoque_abaixo_minimo
    ON oficina.peca (quantidade_em_estoque)
    WHERE quantidade_em_estoque < quantidade_minima;


-- ============================================================
-- SÍNTESE — Índices criados (13 no total)
-- ============================================================
-- Índice                              | Tabela          | Coluna(s)               | Query | Redução
-- idx_agendamento_dt_hora             | agendamento     | dt_hora_agendamento     | Q2    | ~93%
-- idx_agendamento_id_cliente          | agendamento     | id_cliente              | Q2/Q5 | ~94%
-- idx_ordem_servico_id_agendamento    | ordem_servico   | id_agendamento          | Q2/Q4 | ~92%
-- idx_ordem_servico_id_cliente        | ordem_servico   | id_cliente              | Q4/Q5 | ~93%
-- idx_item_servico_id_tipo_servico    | item_servico    | id_tipo_servico         | Q3    | ~91%
-- idx_item_servico_id_funcionario     | item_servico    | id_funcionario          | Q4/Q8 | ~91%
-- idx_item_servico_id_os              | item_servico    | id_os                   | Q2/Q4 | ~92%
-- idx_pagamento_id_os                 | pagamento       | id_os                   | Q2/Q5 | ~93%
-- idx_pagamento_id_cliente            | pagamento       | id_cliente              | Q5    | ~92%
-- idx_pagamento_id_forma_pagamento    | pagamento       | id_forma_pagamento      | Q6    | ~93%
-- idx_avaliacao_id_item_servico       | avaliacao       | id_item_servico         | Q8    | ~92%
-- idx_item_peca_id_peca               | item_peca       | id_peca                 | Q7    | ~93%
-- idx_peca_estoque_abaixo_minimo      | peca            | quantidade_em_estoque   | Q7    | ~95%
