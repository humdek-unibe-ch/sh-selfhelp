# SelfHelp WebApp

The SelfHelp WebApp is a tool that allows to create a web application that serves as a platform for research experiments.
The basic concept is as follows:

Pages are organized as a collection of sections that are rendered on the page, one below the other.
Sections have different `styles` which define the appearance of the sections.
Depending on the style of a section, the section has different `fields` which define the content of the section.
The value of a field can be a simple plaintext or a collection of child sections which have their own styles and children.

Currently available styles include, but are not limited to, alert boxes, buttons, card containers, forms, media elements, tabs, lists, and support for markdown texts.

 - For information about documentation refer to [DOCUMENTS](DOCUMENTS.md)
 - For information about recent changes refer to [CHANGELOG](CHANGELOG.md)

# Installation
## Install Dependencies
  - `sudo apt update`
  - `sudo apt install apache2`  
  - `sudo apt install mysql-server`
  - `sudo apt-get install php php-fpm php-mysql libapache2-mod-php libapache2-mod-fcgid php-apcu php-uopz php-mbstring php8-intl -y`
## Install SelfHelp
  - `sudo git clone https://github.com/humdek-unibe-ch/sh-selfhelp.git` __project_name__
  - `cd ` __project_name__
  - `git checkout ` __latest_release__
  - `cd server/utils/`
  - `sudo su`
  - `sudo ./install.sh -n __project_name__ -p __db_password__`
  - `sudo chown -R www-data.www-data` project folder
## Configure Apache for SelfHelp
 - Create Apache site for SelfHelp. Create `/etc/apache2/sites-available/selfhelp.conf` with following lines where you adjust `__project_name__`, `__project_path__` and `__cert_folder__` for the SSL files:  
 ```
 Define PROJECT_NAME __project_name__
 Define PROJECT_PATH __project_path__
 Define PROJECT_PATH_SERVER ${PROJECT_PATH}/server

<VirtualHost *:80>
    ServerName example.com
    ServerAlias www.example.com
    Redirect permanent / https://example.com/home
</VirtualHost>

 <VirtualHost *:443>
    ServerName example.com
    ServerAlias www.example.com
    DocumentRoot ${PROJECT_PATH}
    <Directory "${PROJECT_PATH}">
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    <Directory "${PROJECT_PATH_SERVER}">
        Require all denied
        <Files ~ "\.css$">
            Require all granted
        </Files>
        <Files ~ "\.js$">
            Require all granted
        </Files>
    </Directory>
    <Directory "${PROJECT_PATH_SERVER}">    
        <Files ~ "\.json$">
            Require all granted
        </Files>    
    </Directory>
    # enable HTTP/2, if available
    Protocols h2 http/1.1

    # HTTP Strict Transport Security (mod_headers is required) (63072000 seconds)
    Header always set Strict-Transport-Security "max-age=63072000"

    # X-Content-Type-Options header
    Header set X-Content-Type-Options nosniff

    # intermediate configuration
    SSLProtocol             all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite          ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-CHACHA20-POLY1305:ECDHE-RSA-CHACHA20-POLY1305:DHE-RSA-AES128-GCM-SHA256:DHE-RSA-AES256-GCM-SHA384
    SSLHonorCipherOrder     off
    SSLSessionTickets       off    

    ErrorLog ${APACHE_LOG_DIR}/error-${PROJECT_NAME}.log
    CustomLog ${APACHE_LOG_DIR}/access-${PROJECT_NAME}.log combined

    RedirectMatch 301 ^/$ https://example.com/home

    SSLCertificateFile /etc/letsencrypt/live/__cert_folder__/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/__cert_folder__/privkey.pem
    Include /etc/letsencrypt/options-ssl-apache.conf

</VirtualHost>

SSLUseStapling On
SSLStaplingCache "shmcb:logs/ssl_stapling(32768)"
```
 - Enable the site with: `sudo a2ensite selfhelp`
 - Enable URL rewriting with: `sudo a2enmod rewrite`
 - Finally, reload apache2 to apply all these changes: `sudo service apache2 reload`

## Update
 - run the `sql` files in the correct order from folder `server/db/update_scripts`.
 - after update clear the `cache`. Now as a workaround it could be done from page `cms->preferences`,  on save of this page the cache is cleared


## Code check
 - for code check use [PHPStan](https://phpstan.org)