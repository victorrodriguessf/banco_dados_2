-- ==============================================
-- VERIFICAÇÃO DOS VOLUMES
-- ==============================================

SELECT 'cliente' AS tabela, COUNT(*) AS total FROM oficina.cliente
UNION ALL
SELECT 'veiculo', COUNT(*) FROM oficina.veiculo
UNION ALL
SELECT 'funcionario', COUNT(*) FROM oficina.funcionario
UNION ALL
SELECT 'tipo_servico', COUNT(*) FROM oficina.tipo_servico
UNION ALL
SELECT 'peca', COUNT(*) FROM oficina.peca
UNION ALL
SELECT 'forma_pagamento', COUNT(*) FROM oficina.forma_pagamento
UNION ALL
SELECT 'agendamento', COUNT(*) FROM oficina.agendamento
UNION ALL
SELECT 'ordem_servico', COUNT(*) FROM oficina.ordem_servico
UNION ALL
SELECT 'item_servico', COUNT(*) FROM oficina.item_servico
UNION ALL
SELECT 'item_peca', COUNT(*) FROM oficina.item_peca
UNION ALL
SELECT 'pagamento', COUNT(*) FROM oficina.pagamento
UNION ALL
SELECT 'avaliacao', COUNT(*) FROM oficina.avaliacao;

-- ==============================================
-- TICKET MÉDIO
-- ==============================================

select
    TO_CHAR(DATE_TRUNC('month', oficina.agendamento.dt_hora_agendamento), 'YYYY-MM') AS mes,
    COUNT(DISTINCT oficina.ordem_servico.id)                                         AS qtd_os,
    SUM(oficina.pagamento.valor_pago)                                               AS receita_total,
    ROUND(AVG(oficina.pagamento.valor_pago), 2)                                     AS ticket_medio
from 
	oficina.agendamento
	join oficina.ordem_servico on agendamento.id = oficina.ordem_servico.id_agendamento
	left join oficina.pagamento on oficina.ordem_servico.id = oficina.pagamento.id_os
where
	oficina.pagamento.valor_pago is not null
group by
	DATE_TRUNC('month', oficina.agendamento.dt_hora_agendamento)
order by 
	mes;

-- ==============================================
-- 10 tipos de serviços mais executados
-- ==============================================

select
	ts.descricao,
	count(*) as qtd,
	SUM(ise.valor_mao_de_obra) as valor
from
	oficina.item_servico ise
	join oficina.tipo_servico ts on ise.id_tipo_servico = ts.id
group by 
	ts.descricao
order by 
	qtd desc,
	ts.descricao ASC
limit 10

-- ==============================================
-- Funcionários que mais atendem
-- ==============================================

select
	oficina.funcionario.nome_completo,
	SUM(ise.valor_mao_de_obra) as valor
from
	oficina.item_servico ise
	join oficina.funcionario fun on fun.id = ise.id_funcionario 
group by 
	oficina.funcionario.nome_completo
order by 
	qtd desc,
	ts.descricao ASC
limit 10