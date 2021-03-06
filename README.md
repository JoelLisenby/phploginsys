This file is part of phploginsys.

Copyright 2012 Joel Lisenby

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

phploginsys
===========

A simple PHP user account login and registration system.

Utilizes the public domain phpass password hashing framework for secure password hashing.
phpass - http://www.openwall.com/phpass/

See [`index.php`](https://github.com/JoelLisenby/phploginsys/blob/master/index.php) for usage example:

    <?php
    require_once "authentication/auth.php";
    $auth = new Authentication();

    $title = "Welcome!";

    ob_start(); // EXTRA META DATA ?>
    <?php // EXTRA META DATA END
    $meta = ob_get_clean();
    ob_start(); // SECURE CONTENT START ?>

    <p>Secure Content <?php echo $auth->logoutLink(); ?> / <?php echo $auth->updateProfileLink(); ?></p>

    <?php // SECURE CONTENT END
    $content = ob_get_clean();

    $auth->echoContentIfAuth(0, $title, $content, $meta);
    ?>
