# Sharedhosting Backup Job 1.0

###  Goal
task take backup for shared hosting with cronjob supported

###  Features
* support backup database on shared hosting.
* auto create backup dirs.
* can configure time to run backup in php code.(ask job timer less than configured_time).
* clear old backup files via config.

###  How to use?
- copy file backup_job.php to home directory of shared hosting or any dir in home.
- need config to connect to database


###  Config ?
edit file : backup_job.php and change below lines.

```sh
//backup config
define('CURRENT_PATH', dirname(__FILE__) );
define('BACKUP_PATH', CURRENT_PATH.'/backups' );
define('TIME_TO_BACKUP_AGAIN', 259200 ); // 3days: 259200
define('TIME_BUFFER_FOR_ONE_BACKUP', 300 ); // 5 minutes: 300
define('NOW', round(microtime(true)) );
define('NUMBER_BACKUP_WILL_KEEP', 5 ); // keep 5 last backup file

// for database connect config
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'tandat'); 
```


###  Author
- Name: D.Nobita
- Email: tidusvn05@gmail.com

###  License
----

MIT





