DROP VIEW IF EXISTS view_mailQueue_transactions;
CREATE VIEW view_mailQueue_transactions
AS
SELECT mq.id, date_create, date_to_be_sent, date_sent, t.id AS transaction_id, transaction_time, 
transaction_type, transaction_by, user_name, transaction_verbal_log
FROM mailQueue mq
INNER JOIN view_transactions t ON (t.table_name = 'mailQueue' AND t.id_table_name = mq.id)
ORDER BY mq.id ASC, t.id ASC;
