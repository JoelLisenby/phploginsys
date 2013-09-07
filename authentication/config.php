<?php
/*
This file is part of phploginsys.

phploginsys is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

phploginsys is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with phploginsys.  If not, see <http://www.gnu.org/licenses/>.
*/

define('SITE_LANG','EN');
define('SITE_NAME','Auth');
define('SITE_EMAIL','noreply@your-domain.com'); // Used as the "from" email for mail sent.
define('MAX_SESSION_LENGTH',7200); // max login time in seconds. (user is logged out on browser/tab close)
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
