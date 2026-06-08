ALTER TABLE oficina.ordem_servico
ADD COLUMN IF NOT EXISTS status TEXT NOT NULL DEFAULT 'aberta';

UPDATE oficina.ordem_servico
SET status = 'concluida'
WHERE status = 'aberta'
  AND id IN (
      SELECT os.id
      FROM oficina.ordem_servico os
      JOIN oficina.pagamento p ON p.id_os = os.id
  );
