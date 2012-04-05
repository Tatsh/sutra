# Note

This branch is intended to separate classes that require database (sDatabase,
sConfiguration) or web server (sCore) away from the ones that do not.

This will be Sutra 1.2 when complete.

# What is this?

Sutra is a PHP framework based on Flourish, and uses Moor for routing.

# How do I use it?

## Set up your web root

Example for nginx on Gentoo:

```bash
sudo gpasswd -a $USER nginx
cd /var/www
mkdir my-new-site
chmod -R 0770 my-new-site
chown -R nginx:nginx my-new-site
```

## Set up where Sutra and dependencies will live

Example here being $HOME/php:

```bash
mkdir ~/php
cd ~/php
git clone -b separated git://github.com/tatsh/sutra.git sutra

# Get Flourish and patch it
svn co http://svn.flourishlib.com/trunk/classes flourish
for i in sutra/patches/flourish*.patch; do patch -p0 < "$i"; done

# Get my fork of Moor
git clone git://github.com/tatsh/moor.git moor

# Where templates will live (as in, not in the web root)
mkdir -p ~/sutra-tpl/default
touch ~/sutra-tpl/default/page.tpl.php
mkdir ~/sutra-tpl/template-name
touch ~/sutra-tpl/template-name/page.tpl.php
touch ~/sutra-tpl/template-name/login-form.tpl.php
```

## Continue setting up the web root

```bash
cd /var/www/my-new-site
mkdir controllers
mkdir files
touch files/my-code.js
touch files/my-code.min.js
touch files/my-style.css
touch files/my-print-style.css
```

## Basic index.php with Moor routing and templating

nginx needs some tweaking (as root). Here is a sample configuration file for use with Moor:

```nginx
server {
        server_name mynewsite.mydomain.com;
        access_log /var/log/nginx/mynewsite.mydomain.com.access_log main;
        error_log /var/log/nginx/mynewsite.mydomain.com.error_log debug_http;

        root /var/www/my-new-site;
        index index.php;

        location = /robots.txt {
                allow all;
                log_not_found off;
                access_log off;
        }

        # The trick for Moor; pass EVERYTHING to /index.php
        location ~* ^/index.php.*$ {
              fastcgi_pass 127.0.0.1:9000;
              include fastcgi.conf;
        }

        location / {
                try_files $uri @sutra;
        }

        # Nice things
        location ~* \.(js|css|png|jpg|jpeg|gif|ico)$ {
                add_header Vary "Accept-Encoding";
                expires max;
                try_files $uri @sutra;
                tcp_nodelay off;
                tcp_nopush on;
        }

        # Security
        location ~* \.(svn|git|patch|htaccess|log|route|plist|inc|pl|po|sh|ini|sample|kdev4|sql|php)$ {
                deny all;
        }

        location @sutra {
                rewrite ^/.*$ /index.php;
        }
}
```

```bash
# After making any configuration changes
/etc/init.d/nginx reload
```

### index.php

```php
<?php
// Where '/home/myname/php' is where you decided to put these libraries
set_include_path('/home/myname/php');

// To append to the include path instead
// set_include_path(get_include_path().PATH_SEPARATOR.'/home/myname/php');

require 'flourish/fLoader.php';
require 'sutra/classes/sLoader.php';
require 'moor/Moor.php';

// Load the classes (including Flourish ones)
sLoader::best();

$cache = new fCache('apc');

sTemplate::setTemplatesPath('/home/myname/sutra-tpl');
sTemplate::setProductionModeTemplatesPath('/home/myname/sutra-tpl');
sTemplate::setCache($cache);
sTemplate::setActiveTemplate('template-name'); // must be in the templates path, both regular and production
sTemplate::setSiteName('My cool site');

// Add some JavaScript
sTemplate::addJavaScriptFile('files/my-code.js'); // Goes in <body>
sTemplate::addJavaScriptFile('files/modernizr.js', 'head'); // Needs to be in <head> if you're using the html5shiv of course

// Production-mode only JavaScript
sTemplate::addMinifiedJavaScriptFile('files/my-code.min.js');

// Add some CSS
sTemplate::addCSSFile('files/my-style.css'); // media="all"
sTemplate::addCSSFile('files/my-print-style.css', 'print'); // media="print"

// Switch the mode
// This mode minifies CSS and outputs only the minified JavaScript files
// sTemplate::setMode('production');

Moor::route('/', 'FrontActionController::index');
Moor::run();
```

### controllers/FrontActionController.php:

```php
<?php
class FrontActionController extends MoorActionController {
  /**
   * Handles standard (non-AJAX) requests to the front page.
   *
   * @return void
   */
  public function index() {
    // 'content' and 'title' are required keys
    $variables = array(
      'csrf' => fRequest::generateCSRFToken('/login/post'), // Security is good
    );
    $content = fAuthorization::checkLoggedIn() ? sTemplate::buffer('login-form', $vars) : '';

    // Renders page.tpl.php
    sTemplate::render(array(
      'content' => $content,
      'title' => 'Welcome',
    ));
  }
}
```

### sutra-tpl/template-name/login-form.tpl.php:

```php
<form id="login-form" method="post" action="/login/post">
  <div class="form-textfield-container">
    <input type="text" class="form-textfield" name="name" value="<?php print fRequest::encode('name', 'string', ''); ?>">
  </div>
  <div class="form-password-container">
    <input type="text" class="form-textfield" name="user_password">
  </div>
  <div class="form-ops-container">
    <input type="submit" class="form-submit" value="<?php print fHTML::encode('Login'); ?>">
  </div>
  <input type="hidden" value="<?php print $csrf; ?>" name="csrf">
</form>
```

### sutra-tpl/template-name/page.tpl.php:

```php
<!DOCTYPE html>
<html>
  <head>
    <title><?php print $title.' | '.$site_name; ?></title>
    <?php print $css; ?>
    <?php print $head_js; ?>
  </head>
  <body class="<?php print $body_classes; ?>">
    <?php print $content; ?>
    <?php print $body_js; ?>
  </body>
</html>
```
