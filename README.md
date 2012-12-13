phploginsys
===========

A simple PHP user account login and registration system.

Utilizes the public domain phpass password hashing framework for secure password hashing.
phpass - http://www.openwall.com/phpass/

See index.php for usage example:

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