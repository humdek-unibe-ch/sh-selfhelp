### Setting up the Email communication

These are the steps I took to send emails:

##### Installing sendmail

sudo apt update
sudo apt install sendmail

##### Setting the host name

In the file `/etc/hosts` changed the first line to

```
127.0.0.1   selfhelp.psy.unibe.ch   localhost.localdomain  localhost   selfhelp
```

In the file `/etc/mail/local-host-names` removed `localhost` and added

```
selfhelp
selfhelp.psy.unibe.ch
```

Created the file `/etc/mail/relay-domains` and added

```
selfhelp.psy.unibe.ch
psy.unibe.ch
```

As root cd to `/etc/mail` and run

```
makemap hash access < access
```

##### Configuring sendmail

In the file `/etc/mail/sendmail.mc` add the lines

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

As root cd to `/etc/mail` and run

```
service sendmail start
make
service sendmail restart
```
