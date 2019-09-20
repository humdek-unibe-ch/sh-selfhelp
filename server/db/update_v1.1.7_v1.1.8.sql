update pages
set url = '/admin/export/[user_input|user_activity|validation_codes|user_input_form:selector]/[all|used|open:option]?/[i:id]?'
where keyword = 'exportData';

update pages
set protocol = 'GET|POST'
where keyword = 'export';