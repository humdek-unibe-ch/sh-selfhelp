### Configuration of the Apache Server

This file gives a very short introduction in how the apache server is set up for the different experiments.

##### SSL Settings

The SSL settings for all projects are defiend in `/home/www/ssl.conf`.
The symbolic link `/etc/apache2/conf-available/ssl.conf` points to this file.
Check the content of the configuration file to locate the keys if it is ever required to replace or update them.
The SSL configuration is enabled as follows: 

```
cd /etc/apache2/conf-enabled
sudo ln -s ../conf-available/ssl.conf .
sudo service apache2 restart
```

##### Experiment Settings

The source files of an experiment `__experiment_name__` should be saved in a seperate folder in the home directory of user `www`, i.e. `/home/www/__experiment_name__`.
The apache configuration file for this specific experiment is located in `/home/www/__experiment_name__/server/apache.conf`.
To enable the experiment run the following commands:

``` 
cd /etc/apache2/sites-available
sudo ln -s /home/www/__experiment_name__/server/apache.conf __experiment_name__.conf
cd ../sites-enabled
sudo ln -s ../sites-available/__experiment_name__.conf .
```

##### PHP Modules

`sudo apt install php7.2-mbstring`

##### PHP Settings

In order to allow to upload large media files the file `/etc/php/7.2/apache2/php.ini` must be modified:

```
; Maximum allowed size for uploaded files.
upload_max_filesize = 1G

; Must be greater than or equal to upload_max_filesize
post_max_size = 1G
```

For the changes to take effect the server must be restarted:
```
sudo service apache2 restart
```
