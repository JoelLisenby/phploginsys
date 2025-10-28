<?php
/*
This file is part of phploginsys.

Copyright 2012-2025 Joel Lisenby

https://github.com/JoelLisenby/phploginsys

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

define('URI','https://www.yourdomain.com');
define('RELDOMAIN','//www.yourdomain.com');
define('PUB_DIR','/');
define('SITE_LANG','EN');
define('SITE_NAME','Your Site Name');
define('SITE_EMAIL','your@emailaddress.com');
define('DB_HOST','localhost');
define('DB_USER','user');
define('DB_PASS','pass');
define('DB_NAME','databasename');
define('DB_TABLE','users');

// querystring action var
define('ACTION_VAR','a');

// Authorization level names
define('AUTH_NAME_0','User');
define('AUTH_NAME_1','Admin');

// session variables
define('SESSION_LENGTH',7200); // length of session in seconds

// anti-flood variables
define('MAX_ATTEMPTS',5); // max attempts
define('ATTEMPT_DELAY',5); // delay before attempts resets (minutes)

// Validation regular expressions
define('USERNAME_REGEX','/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i');
define('PASSWORD_REGEX','/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%!]).{8,}$/');

// Base-2 logarithm of the iteration count used for password stretching
define('HASH_COST_LOG2',8);
// Do we require the hashes to be portable to older systems (less secure)?
define('HASH_PORTABLE',false);
?>
