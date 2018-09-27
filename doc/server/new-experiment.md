# Steps to Create a new Experiment

In the following `__experiment_name__` will be used as a placeholder for the experiment name.
**Do not use underline in this name as it causes issues with the database name**

### 1. Clone the latest release from GitLab 

```
sudo su www
cd
git clone gh@tpf.fluido.as:SLP/SLP-sleep_coach.git __experiment_name__
cd __experiment_name__
```

Checkout the latest relaese.
The expression `__latest_release__` is used as a placeholder for the latest version number.
To list all releases use the command `git tags`.

```
git checkout v__latest_release__
```

Set the global variables of the experiemnt

```
cp server/service/globals_untracked.default.php server/service/globals_untracked.php
```

Edit the file as follows:

- set `BASE_PATH` to `/__experiment_name__`
- set `DBNAME` to `__experiment_name__`
- set `DBUSER` to `__experiment_name__`
- set `DBPW` to `__password__`

where `__password__` is a secure password with

- length >= 8
- at least one upper case and one lower case letter
- at least one number
- at least one special character

Prepare the database script

```
cp server/db/privileges.default.sql server/db/privileges.sql
```

Edit the file as follows:

- set the variable `@db_name` to `__experiment_name__`
- set the variable `@user_name` to `__experiment_name__`

### 2. Set up the db
Log out as `www` user with `ctrl-d` and login to mysql with

```
sudo mysql
```

Once the mysql console is open run the commands

```
CREATE USER '__experiment_name__'@'localhost' IDENTIFIED BY '__password__'
CREATE DATABASE __experiment_name__ CHARACTER SET utf8 COLLATE utf8_general_ci;
USE __experiment_name__;
source server/db/selfhelp_initial.sql;
source server/db/privileges.sql;
```

Quit mysql with `ctrl-d`.

### 3. Set up apache configuration
Login as `www` user

```
sudo su www
```

Prepare the apache configuration file

```
cp server/apache.conf.default server/apache.conf
```

Modify the configuration such that it suits your needs.
What is required is to set the correct project path:

```
Define PROJECT_PATH /home/www/__experiment_name__
```

and an alias which defines how the page will be accessed by url

```
Alias /__experiment_name__ ${PROJECT_PATH}
```

Log out as `www` user with `ctrl-d` and enable the experiment with

``` 
cd /etc/apache2/sites-available
sudo ln -s /home/www/__experiment_name__/server/apache.conf __experiment_name__.conf
cd ../sites-enabled
sudo ln -s ../sites-available/__experiment_name__.conf .
```

Restart the apache server and the page should be accessible at `https://selfhelp.psy.unibe.ch/__experiment_name__`

```
sudo service apache2 restart
```
