# Steps to Create a new Demo Page

In the following the expression `<version>` will be used as a placeholder for the version number.
Use the format `v<maj>_<min>_<rev>` for the version string.
Only create a new demo page if `<maj>` or `<min>` change as changes in `<rev>` are unrelated to the database.

### 1. Connect to the Server

```
ssh <user>@tpf.philhum.unibe.ch
```
where `<user>` is a linux user on the server machine with `sudo` permissions.

### 1. Clone SelfHelp from GitLab

Clone the repo:

```
sudo -u www git clone gh@tpf.fluido.as:SLP/SLP-sleep_coach.git /home/www/selfhelp_demo_<version>
```

### 2. Install the Demo Page

```
sudo /home/www/selfhelp_demo_<version>/install_demo.sh -v <version> -p <db_password>
```
where `<db_password>` is any password which will be used to connect to the selfhelp DB.

### 3. Produce Source Code Documentaion

```
sudo -u www cd /home/www/selfhelp_demo_<version> && doxygen .doxygen
```
