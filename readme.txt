Setup a local instance:

Requirements:
 - MySQL server
 - mail server
 - web server with valid HTTPS

1. Setup the DB
 - execute /db/db.sql in your local MySQL instance

2. Setup the mail server
 - setup an address in the mail server (can be anything), note the address/username and password

2. Setup the configuration
 - change /web/php/db.conf.php - set the values from your MySQL server
 - change /web/php/mail.php - set the values from your mail server

3. Setup the web
 - copy /web to your web server root
