Build Instructions
========

## Requirements
 
* SQLServer 2008 or higher (or free SQL Express)
* PHP 5.6.x
* Composer

It was developed on Windows and using IIS and SQLServer. If you find any issues on other OSs, web servers or DBs, 
please report it!

## Running demo/development server

1. `composer install`
2. Make the below changes to the less.php code
3. `phing build`
4. Create table schema, see **Create Database Schema** below
5. `phing devserver` (or `phing devserver:debug`)
6. if it doesn't open for you, Open browser to [http://localhost:8000](http://localhost:8000)

## Changes to less.php

You cannot disable relative url rewriting in lessc provided by less.php. 
Bug report: https://github.com/Less-PHP/less.php/issues/19

Apply `lessc.patch` to work around this. Windows command line below, assumes `patch` is installed and in path

```
patch vendor\less.php\less.php\bin\lessc < lessc.patch
```

## Exporting/Importing Data

You can export all the data in the database and import it. This can serve as a makeshift backup solution, **but you should
not rely on it!**

The *export* option is available on the menu to all users. Selecting 'all' for both "year" and "Filter" will export
all SLO submissions in the system. This is a plain text csv file or tsv file.

The *import* option is only available on the admin menu. It expects exports in csv format and ANSI encoding.
 
**For testing only** you can use an export/import to move data around. A *reset* is provided to clear all data, but only
if enabled in the config. **Do not leave this on in production!**

## Create Database Schema

**This will clear all data used by the selected configuration. Do not run this command without a backup!**

You can use `phing db` to drop and create the tables used by this application. It will use `config.ini` (and `cli.ini`)
by default, but you can specify extra config files to load using `phing db -Dconfig=<file 1>;<file2>...`. You will be
shown the resulting settings and be asked to confirm the `phing db` command.

## Running tests

To run tests, you need the following installed on the local machine:

* SQLEXPRESS

Then do the following:

1. `phing db:test`
2. `phing test`

To debug tests:

1. update the xdebug part of `tests\php.ini` to point to your IDE. It is pre-configured for default PhpStorm settings.
2. `phing test:debug`