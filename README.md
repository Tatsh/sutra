This branch is intended to separate classes that require database (sDatabase,
sConfiguration) or web server (sCore) away from the ones that do not.

WHAT IS THIS?
Sutra is a PHP framework based on Flourish, and uses Moor for routing.

Flourish - http://flourishlib.com/
Moor - https://github.com/jeffturcotte/moor

Needed:
git
svn

SET AN INCLUDE PATH IN PHP.INI (recommended)
include_path = ".:/usr/share/php5:/usr/share/php:/home/myname/phpincs"

MAKE A SITE ROOT
Copy the site directory to somewhere where your site root will be.

cp /the-repo/site /var/www/newsite
cd /var/www/newsite

If you did not set an include path in php.ini or cannot, then add one to the
the top of global.php:
set_include_path(get_include_path().PATH_SEPARATOR.realpath('/home/myname/phpincs'));

SET UP THE INCLUDES PATH
cd /home/myname/phpincs
mkdir sutra
cp -R /the-repo/classes /the-repo/model /the-repo/routers sutra

So now you should have:
sutra
  classes
  model
  routers

GETTING FLOURISH
cd /home/myname/phpincs
svn co http://svn.flourishlib.com/trunk/classes flourish

PATCH FLOURISH
cd /home/myname/phpincs
cp /the-repo/patches .
for i in patches/flourish*.patch; do patch -p0 < "$i"; done

sutra
  classes
  flourish
  model
  routers

GETTING MOOR
cd /home/myname/phpincs
git clone git://github.com/jeffturcotte/moor.git
cd moor
for i in ../patches/moor*.patch; do patch -p0 < "$i"; done
cd ..

sutra
  classes
  flourish
  model
  moor
  routers

SET UP THE SITE
Create a database. Create a user that has all necessary privileges to write to
it (if applicable). You can choose between 'mysql' and 'sqlite' (tested) or
'db2', 'mssql', 'postgresql', or 'oracle' (not tested).

Copy the .sample.ini files to ones that do not have .sample.ini.
cd config
for i in *.sample.ini; do cp "$i" "${i/\.sample/}"; done

Edit them to the desired site settings.

SECURITY
Block access to EVERYTHING except index.php and files.

To be continued...