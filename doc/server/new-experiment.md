# Steps to Create a new Experiment

In the following the expression `__experiment_name__` will be used as a placeholder for the experiment name.

**Do not use underline in this name as it causes issues with the database name!**

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

### 2. Prepare the asset folder

```
chmod 777 assets
```

### 3. Set the global variables of the experiment

```
cp server/service/globals_untracked.default.php server/service/globals_untracked.php
```

Edit the file as follows:

- set `PROJECT_NAME` to `/__experiment_name__`
- set `DBNAME` to `__experiment_name__`
- set `DBUSER` to `__experiment_name__`
- set `DBPW` to `__db_password__`

where `__db_password__` is a secure password with

- length >= 8
- at least one upper case and one lower case letter
- at least one number
- at least one special character

### 4. Prepare the database script

```
cp server/db/privileges.default.sql server/db/privileges.sql
```

Edit the file as follows:

- set the variable `@db_name` to `__experiment_name__`
- set the variable `@user_name` to `__experiment_name__`

### 5. Set up the db

Log out as `www` user with `ctrl-d` and login to mysql with

```
sudo mysql
```

Once the mysql console is open run the commands

```
CREATE USER '__experiment_name__'@'localhost' IDENTIFIED BY '__db_password__';
CREATE DATABASE __experiment_name__ CHARACTER SET utf8 COLLATE utf8_general_ci;
USE __experiment_name__;
source /home/www/__experiment_name__/server/db/selfhelp_initial.sql;
source /home/www/__experiment_name__/server/db/privileges.sql;
```

Quit mysql with `ctrl-d`.

### 6. Set up apache configuration

Login as `www` user and cd to the project

```
sudo su www
cd /home/www/__experiment_name__
```

Prepare the apache configuration file

```
cp server/apache.default.conf server/apache.conf
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

### 7. Enable User Reminder

The server periodically checks whether a user was inactive for more than a specified amount of time and can be configured to send a reminder email if this is the case.
This feature can be enabled and disabled individually for each experiment.

As user `www` do

```
cd
cd __experiment_name__
cp /home/www/selfhelp_reminder/db/reminder_db_script.default.sql server/db/reminder_db_script.sql
```

and edit the file as follows:

- set the variable `@db_name` to `__experiment_name__`

Log out as `www` user with `ctrl-d` and login to mysql with

```
sudo mysql
```

Once the mysql console is open run the commands

```
USE __experiment_name__;
source /home/www/__experiment_name__/server/db/reminder_db_script.sql;
```

Note that the filed `days` can be used to specify the number of days a user must be inactive to receive a reminder email.
