# ForkBB rev.77 Alpha Readme

## About

ForkBB is a free and open source forum software. The project is based on [FluxBB_by_Visman](https://github.com/MioVisman/FluxBB_by_Visman)

## Requirements

* PHP 8.0+
* PHP extensions: pdo, intl, json, mbstring, fileinfo
* PHP extensions (suggests): imagick or gd (for upload avatars and other images), openssl (for send email via smtp server using SSL/TLS), curl (for OAuth)
* A database such as MySQL 5.5.3+ (an extension using the mysqlnd driver must be enabled), SQLite 3.25+, PostgreSQL 10+

## Install

Topic [Установка (Installation)](https://forkbb.ru/topic/28/ustanovka-installation)

### For Apache:

Apache must have **mod_rewrite** and **mod_headers** enabled. Also, the **AllowOverride** directive must be set to **All**.

Two options
1. Document Root != **public** folder (shared hosting):
    * Rename **.dist.htaccess** to **.htaccess**,
    * Rename **index.dist.php** to **index.php**.
2. Document Root == **public** folder (recommended if you have access to Apache configuration):
    * Rename public/**.dist.htaccess** to public/**.htaccess**,
    * Rename public/**index.dist.php** to public/**index.php**;

**Note**

To determine which of these two options is yours, then immediately after uploading the engine to your site (before these changes), make two requests:
1. your.site/public/robots.txt
2. your.site/robots.txt

On one of the requests, you should see the content of the robots.txt file. Similar to:
```
User-agent: *
Disallow: /adm
Disallow: /log
Disallow: /mod
Disallow: /reg
Disallow: /search
Disallow: /post
Disallow: /forum/scroll
Disallow: /forum/*/new/topic
Disallow: /topic/*/new/reply
Disallow: /userlist/*/DESC/
Disallow: /userlist/*/ASC/
```
On which option you see the contents of the file, choose the option for changing the file names above.
P.S. If you see the contents of the file in both cases, then something went wrong or you have already changed the names of the files **.dist.htaccess** and **index.dist.php**.

### For NGINX:

* [Example](https://github.com/forkbb/forkbb/blob/master/nginx.dist.conf) nginx configuration.
* Note: Root must point to the [**public/**](https://github.com/forkbb/forkbb/tree/master/public) directory.
* Note: The **index.dist.php** file does not need to be renamed.

## Favicon
* You can rename public/favicon48.ico file to public/favicon.ico
* You can create your own favicon file and copy it to public/favicon.ico

## Links

* Homepage: https://forkbb.ru/
* GitHub: https://github.com/forkbb/forkbb

## License

This project is under MIT license. Please see the [license file](LICENSE) for details.
