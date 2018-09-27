### Linux user configuration

The web applications are run in the home directory of the user `www`.

Administrators should have their own user to ssh to.
This way each person connecting to the server can use ther personal configuration files.
To update the web app repository switch to user `www` with

```
sudo su www
```
