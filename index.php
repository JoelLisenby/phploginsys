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