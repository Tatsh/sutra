#!/bin/bash
git clone git://github.com/Tatsh/flourish-classes.git flourish

psql -c "DROP DATABASE IF EXISTS sutra_tests;" -U postgres
psql -c "CREATE DATABASE IF NOT EXISTS sutra_tests;" -U postgres
mysql -e "DROP DATABASE IF EXISTS sutra_tests; CREATE DATABASE IF NOT EXISTS sutra_tests;" -u root

mkdir -p template/default
mkdir -p template/custom
mkdir mincss
echo '<?php print $abc; ?>' > template/default/something.tpl.php
echo '<?php print $abc; ?>' > template/custom/something2.tpl.php
echo '<!DOCTYPE html>
<html lang="<?php print $lang; ?>" dir="<?php print $dir; ?>">
<head>
  <title><?php print $title; ?> | <?php print $site_name; ?></title>
  <?php print $css; ?>
  <?php print $head_js; ?>
</head>
<body class="<?php print $body_class; ?>">
<div id="site-slogan">
<?php print $site_slogan; ?>
</div>
<?php print $content; ?>
<?php print $body_js; ?>
</body>
</html>' > template/custom/page.tpl.php
echo '* {margin:0;padding:0}' > test.css
echo '* {margin:0;padding:0}' > test2.css
echo '* {margin:0;padding:0}' > test3.css

cp -f resources/backup/* resources
rm -f resources/*_copy*

chmod 0700 non-writable-directory &> /dev/null
rmdir non-writable-directory &> /dev/null

touch test.cache

chmod 0700 non-writable-directory &> /dev/null
rmdir non-writable-directory &> /dev/null

rm -rf flourish__* template mincss test*.css
rm -f resources/*_copy*
rm -f test.cache
git checkout resources/db.sqlite3

echo