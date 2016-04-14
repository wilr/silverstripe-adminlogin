silverstripe-adminlogin
=======================
[![Scrutinizer](https://img.shields.io/scrutinizer/g/axyr/silverstripe-adminlogin.svg)](https://scrutinizer-ci.com/g/axyr/silverstripe-adminlogin/)
[![Travis](https://img.shields.io/travis/axyr/silverstripe-adminlogin.svg)](https://travis-ci.org/axyr/silverstripe-adminlogin)
[![Packagist](https://img.shields.io/packagist/dt/axyr/silverstripe-adminlogin.svg)](https://packagist.org/packages/axyr/silverstripe-adminlogin)
[![Packagist](https://img.shields.io/packagist/v/axyr/silverstripe-adminlogin.svg)](https://packagist.org/packages/axyr/silverstripe-adminlogin)

##Installation
Run ```composer require axyr/silverstripe-adminlogin```

Use a custom login screen when log in to the admin section.

You can also limit the admin/cms section by ip address (ranges).

The following formats are supported :

* 192.168.1.101
* 192.168.1.100-200
* 192.168.1.0/24
* 192.168.1.*

## Use theme styles/scripts
If you want to use your theme styles/scripts you can do the following:

* Add the following to a config file (e.g. ```mysite/_config/_config.yml```):
```
AdminLogin:
  UseTheme: true
```
* Optionally copy ```templates/AdminLogin.ss``` to your theme folder (& edit it to your liking).

## Screenshot
![Screenshot](https://raw.github.com/axyr/silverstripe-adminlogin/master/images/screenshot.png)

## Known issues
When you are logged in, but don't have admin permissions, the normal site theme will be used.
