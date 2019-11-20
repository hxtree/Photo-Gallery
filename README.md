# Photo Gallery
This was an old exercise to create a photo gallery with thumbnails, LDAP, machine learning tags, etc. The project was retired. 

It serves as a learning tool to show growth. If I had to do this again I would:
+ use a more OOP approach,
+ a design pattern,
+ not close php files ?>
+ not echo every line (templating engine, like PXP perhaps), 
+ use an SPL autoloader,
+ a package manager (composer), etc. for Bootstrap
+ KISS
+ Adapt the unix philosophy
+ Consider screen reader
+ etc. etc ;-)

# How it Works
+ All web pages are stored in the pages folder
+ A page must be added to the database and a php file to the pages folder before it will be live
+ Authentication works through LDAP server amd occurs in index.php
+ Configurations are stored in resources/config.
+ Photo processing is completed using a python script need to add cron job to python script in lib, crontab -e
> */5 * * * * python /var/www/photos/lib/process-files.py

# Requirements:
## General
>sudo apt-get install apache2 php php-zip mysql-server php-mysql

## Photo processing:
>apt-get install python python-pip imagemagick libmagickwand-dev libmysqlclient-dev
>pip install MySQL-python configparser wand tendo
