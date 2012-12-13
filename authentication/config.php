<?php
define('SITE_LANG','EN');
define('SITE_NAME','Auth');
define('SITE_EMAIL','noreply@your-domain.com'); // Used as the "from" email for mail sent.
define('DB_HOST','localhost');
define('DB_USER','');
define('DB_PASS','');
define('DB_NAME','');
define('DB_TABLE','users');
define('ACTION_VAR','a'); // querystring action var

// anti-flood variables
define('MAX_ATTEMPTS',5); // max attempts
define('ATTEMPT_DELAY',5); // delay before attempts resets (minutes)

// Validation regular expressions
define('USERNAME_REGEX','/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i'); // Restrict usernames with a regex (see translations for description)
define('PASSWORD_REGEX','/^((?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%!]).{8,})$/'); // restrict passwords with a regex (see translations for description)

// Base-2 logarithm of the iteration count used for password stretching
define('HASH_COST_LOG2',8);
// Do we require the hashes to be portable to older systems (less secure)?
define('HASH_PORTABLE',false);
?>