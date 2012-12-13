<?php
class Template {
	private static $title;
	private static $meta;
	private static $content;
	private static $action_var;

	public function __construct($title,$content,$meta = null) {
		self::$title = $title;
		self::$meta = $meta;
		self::$content = $content;
	}
	
	public function code() {
		$code = self::head();
		$code .= self::body();
		$code .= self::foot();
		
		return $code;
	}
	
	private function head() {
		ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo self::$title; ?></title>
<link rel="stylesheet" type="text/css" href="authentication/css/authentication.css" />
<?php echo self::$meta; ?>
</head>
<?php
		return ob_get_clean();
	}
	
	private function body() {
		ob_start();
?>
<body>

<?php echo self::$content; ?>

</body>
<?php
		return ob_get_clean();
	}
	
	private function foot() {
		ob_start();
?>
</html>
<?php
		return ob_get_clean();
	}
}
?>