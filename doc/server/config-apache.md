### Configuration of the Apache Server

This file gives a very short introduction in how the apage server is set upf for the different experiments.

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
