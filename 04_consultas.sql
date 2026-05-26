-- ============================================================
-- BANCO DE DADOS II — Consultas Analíticas (Entrega 4)
-- Schema: oficina
-- ============================================================


-- ==============================================
-- Q1 — Contagem de registros por tabela
-- Verifica se os volumes mínimos foram atingidos
-- ==============================================

SELECT 'cliente'        AS tabela, COUNT(*) AS total FROM oficina.cliente
UNION ALL
SELECT 'veiculo',                  COUNT(*) FROM oficina.veiculo
UNION ALL
SELECT 'funcionario',              COUNT(*) FROM oficina.funcionario
UNION ALL
SELECT 'tipo_servico',             COUNT(*) FROM oficina.tipo_servico
UNION ALL
SELECT 'peca',                     COUNT(*) FROM oficina.peca
UNION ALL
SELECT 'forma_pagamento',          COUNT(*) FROM oficina.forma_pagamento
UNION ALL
SELECT 'agendamento',              COUNT(*) FROM oficina.agendamento
UNION ALL
SELECT 'ordem_servico',            COUNT(*) FROM oficina.ordem_servico
UNION ALL
SELECT 'item_servico',             COUNT(*) FROM oficina.item_servico
UNION ALL
SELECT 'item_peca',                COUNT(*) FROM oficina.item_peca
UNION ALL
SELECT 'pagamento',                COUNT(*) FROM oficina.pagamento
UNION ALL
SELECT 'avaliacao',                COUNT(*) FROM oficina.avaliacao
ORDER BY tabela;


-- ==============================================
-- Q2 — Receita total e ticket médio por mês
-- Considera apenas OS que possuem pagamento registrado
-- (equivalente funcional a status 'Concluído', dado o schema atual)
-- Janela: últimos 12 meses a partir da data corrente
-- ==============================================

SELECT
    TO_CHAR(DATE_TRUNC('month', a.dt_hora_agendamento), 'YYYY-MM') AS mes,
    COUNT(DISTINCT os.id)                                           AS qtd_os,
    SUM(p.valor_pago)                                               AS receita_total,
    ROUND(AVG(p.valor_pago), 2)                                     AS ticket_medio
FROM oficina.agendamento      a
JOIN oficina.ordem_servico    os ON os.id_agendamento = a.id
JOIN oficina.pagamento        p  ON p.id_os = os.id
WHERE a.dt_hora_agendamento >= DATE_TRUNC('month', CURRENT_DATE) - INTERVAL '11 months'
GROUP BY DATE_TRUNC('month', a.dt_hora_agendamento)
ORDER BY mes;


-- ==============================================
-- Q3 — Ranking dos 10 tipos de serviço mais realizados
-- Ordenado por quantidade de execuções e faturamento acumulado
-- ==============================================

SELECT
    ts.descricao                        AS tipo_servico,
    COUNT(ise.id)                       AS qtd_execucoes,
    SUM(ise.valor_mao_de_obra)          AS faturamento_total
FROM oficina.item_servico  ise
JOIN oficina.tipo_servico  ts  ON ts.id = ise.id_tipo_servico
GROUP BY ts.id, ts.descricao
ORDER BY qtd_execucoes DESC, faturamento_total DESC
LIMIT 10;


-- ==============================================
-- Q4 — Ranking de funcionários por faturamento gerado
-- Considera item_servico vinculados a OS que possuem pagamento
-- Exibe também a quantidade de OS distintas atendidas
-- ==============================================

SELECT
    f.nome_completo                     AS funcionario,
    f.matricula,
    COUNT(DISTINCT ise.id_os)           AS qtd_os_atendidas,
    SUM(ise.valor_mao_de_obra)          AS faturamento_gerado
FROM oficina.item_servico  ise
JOIN oficina.funcionario   f   ON f.id = ise.id_funcionario
JOIN oficina.ordem_servico os  ON os.id = ise.id_os
JOIN oficina.pagamento     p   ON p.id_os = os.id
GROUP BY f.id, f.nome_completo, f.matricula
ORDER BY faturamento_gerado DESC;


-- ==============================================
-- Q5 — 20 clientes com maior gasto acumulado
-- Distingue pessoa física (CPF preenchido) de jurídica (CNPJ preenchido)
-- ==============================================

SELECT
    c.nome_completo                             AS cliente,
    CASE
        WHEN c.cpf  IS NOT NULL THEN 'Pessoa Física'
        WHEN c.cnpj IS NOT NULL THEN 'Pessoa Jurídica'
        ELSE 'Indefinido'
    END                                         AS tipo_pessoa,
    COALESCE(c.cpf, c.cnpj)                     AS documento,
    COUNT(DISTINCT p.id)                        AS qtd_pagamentos,
    SUM(p.valor_pago)                           AS gasto_acumulado
FROM oficina.cliente       c
JOIN oficina.pagamento     p  ON p.id_cliente = c.id
GROUP BY c.id, c.nome_completo, c.cpf, c.cnpj
ORDER BY gasto_acumulado DESC
LIMIT 20;


-- ==============================================
-- Q6 — Distribuição percentual das formas de pagamento
-- Quantidade de transações e valor total por modalidade
-- ==============================================

SELECT
    fp.descricao                                        AS forma_pagamento,
    COUNT(p.id)                                         AS qtd_transacoes,
    SUM(p.valor_pago)                                   AS valor_total,
    ROUND(
        COUNT(p.id) * 100.0 / SUM(COUNT(p.id)) OVER (),
        2
    )                                                   AS pct_quantidade,
    ROUND(
        SUM(p.valor_pago) * 100.0 / SUM(SUM(p.valor_pago)) OVER (),
        2
    )                                                   AS pct_valor
FROM oficina.pagamento       p
JOIN oficina.forma_pagamento fp ON fp.id = p.id_forma_pagamento
GROUP BY fp.id, fp.descricao
ORDER BY qtd_transacoes DESC;


-- ==============================================
-- Q7 — Peças com estoque abaixo do mínimo
-- Exibe déficit calculado e fabricante (campo "fornecedor" do schema)
-- ==============================================

SELECT
    p.descricao                                             AS peca,
    p.fabricante                                            AS fornecedor,
    p.quantidade_minima                                     AS estoque_minimo,
    p.quantidade_em_estoque                                 AS estoque_atual,
    (p.quantidade_minima - p.quantidade_em_estoque)         AS deficit
FROM oficina.peca p
WHERE p.quantidade_em_estoque < p.quantidade_minima
ORDER BY deficit DESC;


-- ==============================================
-- Q8 — Nota média de avaliação por funcionário
-- Restringe a funcionários com ao menos 5 avaliações registradas
-- Caminho: avaliacao → item_servico → funcionario
-- ==============================================

SELECT
    f.nome_completo                         AS funcionario,
    f.matricula,
    COUNT(av.id)                            AS total_avaliacoes,
    ROUND(AVG(av.nota), 2)                  AS nota_media
FROM oficina.avaliacao     av
JOIN oficina.item_servico  ise ON ise.id = av.id_item_servico
JOIN oficina.funcionario   f   ON f.id  = ise.id_funcionario
GROUP BY f.id, f.nome_completo, f.matricula
HAVING COUNT(av.id) >= 5
ORDER BY nota_media DESC;
