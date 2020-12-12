# Steps to Create a new Demo Page

In the following the expression `<version>` will be used as a placeholder for the version number.
Use the format `v<maj>_<min>_<rev>` for the version string.
Only create a new demo page if `<maj>` or `<min>` change as changes in `<rev>` are unrelated to the database.

### 1. Document the new Features

In a fist step all new Features need to be documented. It is recommended to do this locally and the development machine.
The fetch the Database from the latest slefhelp demo instance use the following command:

```
ssh <user>@tpf.philhum.unibe.ch "mysqldump -f selfhelp_demo_<old_version>" > server/db/selfhelp_demo.sql
```
where `<user>` is a linux user on the server machine and a DB user with `SELECT` permission on the target database via UNIX socket authentication.
The flag `-f` will skip the exporting of views (due to missing permissions) and generate warnings but this is intended as views will be installed separately by the install script.

To setup a DB user use the following commands in mysql prompt (this will create a DB user `<user>` with `SELECT` permissions on all databases via UNIX socket authentication):
```
CREATE USER '<user>'@'localhost' IDENTIFIED WITH auth_socket;
GRANT SELECT ON *.* TO '<user>'@'localhost';
```

Once the documentation is done locally it is time to create a new instance of selfhelp to host the demo page of the new version.

### 2. Connect to the Server

Connect to the server with `ssh`

```
ssh <user>@tpf.philhum.unibe.ch
```
where `<user>` is a linux user on the server machine with `sudo` permissions.

### 3. Clone SelfHelp from GitLab

Clone the repo:

```
sudo -u www git clone gh@tpf.fluido.as:SLP/SLP-sleep_coach.git /home/www/selfhelp_demo_<version>
```

### 4. Install the Demo Page

```
sudo /home/www/selfhelp_demo_<version>/install_demo.sh -v <version> -p <db_password>
```
where `<db_password>` is any password which will be used to connect to the selfhelp DB.
