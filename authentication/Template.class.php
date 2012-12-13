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
