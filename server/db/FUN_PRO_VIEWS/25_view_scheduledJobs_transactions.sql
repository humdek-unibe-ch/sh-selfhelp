DROP VIEW IF EXISTS view_scheduledJobs_transactions;
CREATE VIEW view_scheduledJobs_transactions
AS
SELECT sj.id, sj.date_create, date_to_be_executed, date_executed, t.id AS transaction_id, transaction_time, 
transaction_type, transaction_by, user_name, transaction_verbal_log
FROM scheduledJobs sj
INNER JOIN view_transactions t ON (t.table_name = 'scheduledJobs' AND t.id_table_name = sj.id)
ORDER BY sj.id ASC, t.id ASC;
