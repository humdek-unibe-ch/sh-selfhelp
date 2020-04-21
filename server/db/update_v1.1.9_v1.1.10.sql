-- remove dots and spaces from the page name
-- php Dots and spaces in variable names are converted to underscores from php documentation
update pages
set keyword = REPLACE(keyword, '.', '_')
where keyword like ('%.%');

update pages
set keyword = REPLACE(keyword, ' ', '_')
where keyword like ('% %');