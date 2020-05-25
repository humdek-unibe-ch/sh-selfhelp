DROP VIEW IF EXISTS view_mailQueue;
CREATE VIEW view_mailQueue
AS
SELECT mq.id AS id, l_status.lookup_value AS status, l_sent_by.lookup_value AS sent_by, date_create, date_to_be_sent, date_sent, from_email, from_name,
reply_to, recipient_emails, cc_emails, bcc_emails, subject, body, is_html, u.name AS sent_by_user
FROM mailQueue mq
LEFT JOIN users u ON (u.id = mq.id_users)
INNER JOIN lookups l_status ON (l_status.id = mq.id_mailQueueStatus)
LEFT JOIN lookups l_sent_by ON (l_sent_by.id = mq.id_mailSentBy)