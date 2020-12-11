### Module Mail Configuration

##### Mail Queue

Add cron job:
```
* * * * * php home/__os_user_name__/__experiment_name__/server/cronjobs/MailQueue.php
```

The Queue will be checked every minute and if a mail is scheduled it will be sent.
