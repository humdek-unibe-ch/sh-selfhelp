-- set DB version
UPDATE version
SET version = 'v5.10.0';

UPDATE pages
SET id_pageAccessTypes = (SELECT id FROM lookups WHERE lookup_code = 'web')
WHERE keyword = 'sh_globals';

UPDATE pages
SET id_pageAccessTypes = (SELECT id FROM lookups WHERE lookup_code = 'web')
WHERE keyword = 'sh_modules';

SET @id_globals_page = (SELECT id FROM pages WHERE keyword = 'sh_globals');
SET @id_modules_page = (SELECT id FROM pages WHERE keyword = 'sh_modules');

UPDATE pages
SET id_pageAccessTypes = (SELECT id FROM lookups WHERE lookup_code = 'web')
WHERE parent = @id_globals_page OR parent = @id_modules_page;

