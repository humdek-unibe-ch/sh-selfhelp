### Setting up the Email communication

These are the steps that were taken to enable the sending of emails.

##### Installing sendmail

```
sudo apt update
sudo apt install sendmail
```

##### Setting the host name

In the file `/etc/hosts` the first line was changed to

```
127.0.0.1   selfhelp.psy.unibe.ch   localhost.localdomain  localhost   selfhelp
```

In the file `/etc/mail/local-host-names` the line `localhost` was removed and the following was  added

```
selfhelp
selfhelp.psy.unibe.ch
```

The file `/etc/mail/relay-domains` was created and the following lines were added

```
selfhelp.psy.unibe.ch
psy.unibe.ch
```

In the file `/etc/mail/access` the following lines were added

```
smtp.sendgrid.net       OK
GreetPause:localhost    0
```

In the folder `/etc/mail` as user root the following commands were run

```
makemap hash access < access
```

##### Configuring sendmail

In the file `/etc/mail/sendmail.mc` the following lines were added

```
dnl define(`SMART_HOST`, `smtp.unibe.ch`)

dnl SET OUTBOUND DOMAIN
MASQUERADE_AS(`psy.unibe.ch')
MASQUERADE_DOMAIN(`psy.unibe.ch')
FEATURE(masquerade_envelope)
FEATURE(masquerade_entire_domain)

dnl SMART HOST CONFIG
define(`SMART_HOST', `smtp.unibe.ch')dnl
```

In the folder `/etc/mail` as user root the following commands were run

```
service sendmail start
make
service sendmail restart
```
