-- show groups in the chat
update pages
set url = '/kontakt/[i:chrid]?/[i:gid]?/[i:uid]?'
where keyword = 'contact';

alter table chatRoom
add column title varchar(200);