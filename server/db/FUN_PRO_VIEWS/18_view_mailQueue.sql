DROP VIEW IF EXISTS view_mailQueue;
CREATE VIEW view_mailQueue
AS
SELECT mq.id AS id, l_status.lookup_code AS status_code, l_status.lookup_value AS status, date_create, date_to_be_sent, date_sent, from_email, from_name,
reply_to, recipient_emails, cc_emails, bcc_emails, subject, body, is_html
FROM mailQueue mq
INNER JOIN lookups l_status ON (l_status.id = mq.id_mailQueueStatus);
