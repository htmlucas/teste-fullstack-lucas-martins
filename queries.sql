/* 
Query A — Ranking de afiliados por receita

Objetivo:
Listar os 10 afiliados com maior receita líquida considerando apenas pedidos
approved e refunded.

Estratégia:
1. Agrupar os pedidos por afiliado em uma CTE.
2. Calcular receita bruta, valor reembolsado e receita líquida.
3. Usar RANK() OVER para adicionar a posição do afiliado no ranking.
*/

WITH affiliate_summary AS (
    SELECT 
        a.username,
        COUNT(o.id) AS total_orders,
        SUM(o.total) AS gross_revenue,
        SUM(
            CASE
                WHEN o.status = 'refunded'
                THEN o.total
                ELSE 0
            END
        ) AS refunded_amount,
        SUM(
            CASE
                WHEN o.status = 'approved'
                THEN o.total
                ELSE -o.total
            END
        ) AS net_revenue
    FROM orders o
    JOIN affiliates a
        ON a.id = o.affiliate_id
    WHERE o.status IN ('approved', 'refunded')
    GROUP BY a.id, a.username
)

SELECT
    username,
    total_orders,
    gross_revenue,
    refunded_amount,
    net_revenue,
    RANK() OVER (
        ORDER BY net_revenue DESC
    ) AS ranking
FROM affiliate_summary
ORDER BY ranking
LIMIT 10;

/* Query B */

WITH recursive months AS (
  SELECT 
    DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01') AS month_start

  UNION ALL

  SELECT
    DATE_ADD(month_start, INTERVAL 1 MONTH)
  FROM months
  where month_start < DATE_FORMAT(CURDATE(), '%Y-%m-01')
),
  
orders_summary AS (
  select
    DATE_FORMAT(o.ordered_at, '%Y-%m-01') AS month_start,
    count (o.id) as total_pedidos,
    SUM (
      case
        when o.status = 'approved'
        then 1
        else 0
      end
    ) as pedidos_aprovados,
    SUM (
      case
        when o.status = 'cancelled'
        then 1
        else 0
      end
    ) as pedidos_cancelados
  from orders o
  WHERE o.ordered_at >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
  group by DATE_FORMAT(o.ordered_at, '%Y-%m-01')
)

SELECT
    DATE_FORMAT(m.month_start, '%Y-%m') AS month,
    COALESCE(os.total_pedidos, 0) AS total_orders,
    COALESCE(os.pedidos_aprovados, 0) AS approved_orders,
    COALESCE(os.pedidos_cancelados, 0) AS cancelled_orders,
    COALESCE(
        os.pedidos_aprovados * 100.0 / NULLIF(os.total_pedidos, 0),
        0
    ) AS approval_rate
FROM months m
LEFT JOIN orders_summary os
    ON os.month_start = m.month_start
ORDER BY m.month_start;

/* Query C */

WITH order_totals AS (
    SELECT
        o.id AS order_id,
        o.affiliate_id,
        DATE(o.created_at) AS order_day,
        SUM(oi.quantity * oi.unit_price) AS order_total
    FROM orders o
    JOIN order_items oi
        ON oi.order_id = o.id
    GROUP BY
        o.id,
        o.affiliate_id,
        DATE(o.created_at)
)

SELECT
    affiliate_id,
    order_day,
    order_total AS duplicated_value,
    GROUP_CONCAT(order_id ORDER BY order_id) AS order_ids,
    COUNT(*) AS duplicated_count
FROM order_totals
GROUP BY
    affiliate_id,
    order_day,
    order_total
HAVING COUNT(*) > 1;

/* Query D */

WITH product_sales AS (
    SELECT
        o.affiliate_id,
        oi.product_id,
        SUM(oi.quantity) AS total_quantity,
        SUM(oi.quantity * oi.unit_price) AS total_value
    FROM orders o
    JOIN order_items oi
        ON oi.order_id = o.id
    GROUP BY
        o.affiliate_id,
        oi.product_id
),
ranked_products AS (
    SELECT
        affiliate_id,
        product_id,
        total_quantity,
        total_value,
        ROW_NUMBER() OVER (
            PARTITION BY affiliate_id
            ORDER BY total_quantity DESC, total_value DESC
        ) AS ranking
    FROM product_sales
)

SELECT
    affiliate_id,
    product_id,
    total_quantity,
    total_value
FROM ranked_products
WHERE ranking = 1
ORDER BY affiliate_id;




/* Query E */

/*
Problemas da query original:
1. Usa DATE(o.created_at), o que aplica função na coluna e pode impedir o uso eficiente
   de índice em created_at.
2. Usa IN com subquery para filtrar afiliados ativos; um JOIN é mais direto e tende a gerar
   um plano de execução melhor.
3. Usa subquery correlacionada para calcular o total dos itens pedido por pedido. Com muitos
   pedidos, isso pode recalcular agregações repetidamente.
4. Usa SELECT *, trazendo colunas desnecessárias e aumentando I/O.

Otimização:
- Filtra orders por data usando comparação direta em created_at.
- Usa JOIN com affiliates.
- Agrega order_items uma vez por pedido em uma CTE.
- Filtra total do pedido com WHERE sobre o resultado já agregado.
*/

WITH order_totals AS (
    SELECT
        oi.order_id,
        SUM(oi.quantity * oi.unit_price) AS order_total
    FROM order_items oi
    GROUP BY oi.order_id
)

SELECT
    o.id,
    o.external_id,
    o.affiliate_id,
    o.status,
    o.total,
    o.created_at,
    o.updated_at,
    ot.order_total
FROM orders o
JOIN affiliates a
    ON a.id = o.affiliate_id
JOIN order_totals ot
    ON ot.order_id = o.id
WHERE a.status = 'active'
  AND o.created_at >= '2024-01-01'
  AND ot.order_total > 100
ORDER BY o.created_at DESC;

