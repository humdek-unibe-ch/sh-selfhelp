-- set DB version
UPDATE version
SET version = 'v6.11.0';

UPDATE libraries
SET `name` = '[Altorouter](https://github.com/dannyvankooten/AltoRouter)', comments = '[License Details](https://github.com/dannyvankooten/AltoRouter?tab=MIT-1-ov-file#readme)'
WHERE `name` = '[Altorouter](http://altorouter.com/)';