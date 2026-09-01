-- DS-6320 - Fin de promocion de lanzamiento del Pase VIP Digital Trends
--
-- Ejecutar manualmente en el ambiente donde se aplique el cutover.
-- El deploy del codigo no modifica el precio efectivo del checkout por si solo.

SELECT event_key, ticket_code, name, price, currency, is_active
FROM payment_tickets
WHERE event_key = 'DIGITALTRENDS'
  AND ticket_code = 'VIP';

UPDATE payment_tickets
SET price = 9.99
WHERE event_key = 'DIGITALTRENDS'
  AND ticket_code = 'VIP'
  AND is_active = 1;

SELECT event_key, ticket_code, name, price, currency, is_active
FROM payment_tickets
WHERE event_key = 'DIGITALTRENDS'
  AND ticket_code = 'VIP';
