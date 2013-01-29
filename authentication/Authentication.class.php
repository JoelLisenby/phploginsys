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

/*** Authentication Class

secure page example:
********************

$auth = new Authentication();

if($auth->authenticated) {
	echo "Logged in.";
} else {
	echo $authentication->form();
}

****/
class Authentication {
	public $authenticated = false;		// logged in?
	
	private static $db;
	private static $language;
	private static $action;
	private static $message;
	private static $errors;
	private static $attempts = 0;		// Maximum attempts
	private static $minutes_left;

	public function __construct() {
		require_once "translations/". SITE_LANG .".php";
		
		self::$language = $words;
		
		if(session_start()) {
			if(!self::spam()) {
				self::$action = (isset($_GET[ACTION_VAR]) ? $_GET[ACTION_VAR] : array());
				
				if(!isset($_SESSION['signed_in'])) { // User not logged in
					switch(self::$action) {
						case "register": // register a new account
							self::register();
							break;
						case "lostpassword": // lost password, need a new password
							self::lostPassword();
							break;
						case "password": // lost password
							self::updatePassword();
							break;
						case "resend_activation":
							self::resendActivation();
							break;
						default: // login
							self::login();
							break;
					}
				} else { // User authenticated
					$this->authenticated = true;
					
					switch(self::$action) {
						case "logout":
							self::logout();
							break;
						case "profile":
							self::updateProfile();
							break;
					}
				}
			} else {
				self::$message = self::$language["status_spam"];
			}
		} else {
			$_SESSION['message'] = self::$language["sessions_req"];
		}
	}
	
	public function isLevel($level = null) {
		if($this->authenticated && !empty($level)) {
			if(self::getAccessLevel($_SESSION['username']) === $level) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	public function echoContentIfAuth($access_level,$title,$content,$meta = null) {
		if($this->authenticated && !empty($_GET[ACTION_VAR])) {
			// Logged in with actions ... display actions
			if(self::getAccessLevel($_SESSION['username']) >= $access_level) {
				switch($_GET[ACTION_VAR]) {
					case "profile":
						$this->form();
						break;
					default: // invalid action, remove action and redirect.
						$url = substr($this->curPageURL(), 0, strpos($this->curPageURL(), '?'));
						header("Location: ".$url);
				}
			}
		} else if($this->authenticated && empty($_GET[ACTION_VAR])) {
			// Logged in, display whatever secure content you want here!
			if(self::getAccessLevel($_SESSION['username']) >= $access_level) {
				$template = new Template($title,$content,$meta);
				echo $template->code();
			}
		} else {
			// User not logged in, display form
			$this->form();
		}
	}

	public function form($width = "100%", $height = "100%", $fontsize = "1em") {
		switch(self::$action) {
			case "register": // Registration form
				if(!$this->authenticated) {
					ob_start();
?>
<?php echo "<div id=\"authform\" style=\"width: ".$width."; height: ".$height."; font-size: ".$fontsize.";\">\n"; ?>
<form method="post" action="<?php echo $this->curPageURL(); ?>">

<h1><?php echo self::$language["form_register_title"]; ?></h1>

<?php
if(!empty(self::$message)) {
	echo '<div id="authform_message">'. self::$message .'</div>';
}
?>

<div id="authform_user">
<label for="username"><?php echo self::$language["form_register_field_username"]; ?></label>
<input type="email" name="username" id="username" required />
</div><br /><div id="authform_pass">
<label for="password"><?php echo self::$language["form_register_field_pw"]; ?></label>
<input type="password" name="password" id="password" required />
<label for="repeat_password"><?php echo self::$language["form_register_field_repeat_pw"]; ?></label>
<input type="password" name="repeat_password" id="repeat_password" required />
</div><br />
<br />
<div id="authform_submit">
<?php echo '<input type="submit" value="'.self::$language["form_register_submit"].'" class="auth_button" />'."\n";?>
</div>

</form>

</div><!-- authform -->
<?php
					$template = new Template(self::$language["form_register_title"],ob_get_clean());
					echo $template->code();
				} else {
					// redirect back to main page.
					header("Location: ".$this->remove_querystring_var($this->curPageURL(),ACTION_VAR));
				}
				break;
			case "profile": // Update profile form
				if($this->authenticated) {
					ob_start();
?>
<?php echo "<div id=\"authform\" style=\"width: ".$width."; height: ".$height."; font-size: ".$fontsize.";\">\n"; ?>
<form method="post" action="<?php echo $this->curPageURL(); ?>">

<h1><?php echo self::$language["form_profile_title"]; ?></h1>

<?php
if(!empty(self::$message)) {
	echo '<div id="authform_message">'. self::$message .'</div>';
}
?>

<div id="authform_user">
<label for="username">Username (Email)</label>
<p id="username"><?php echo $_SESSION['username']; ?><p>
</div><br /><div id="authform_pass">
<label for="password"><?php echo self::$language["form_profile_field_pw"]; ?></label>
<input type="password" name="password" id="password" required />
<label for="repeat_password"><?php echo self::$language["form_profile_field_pw_repeat"]; ?></label>
<input type="password" name="repeat_password" id="repeat_password" required />
</div><br />
<br />
<div id="authform_submit">
<?php echo '<input type="submit" value="'.self::$language["form_profile_submit"].'" class="auth_button" />'."\n"; ?>
</div>

</form>

</div><!-- authform -->
<?php
					$template = new Template(self::$language["form_profile_title"],ob_get_clean());
					echo $template->code();
				} else {
					// redirect back to main page.
					header("Location: ".$this->remove_querystring_var($this->curPageURL(),ACTION_VAR));
				}
				break;
			case "lostpassword": // Lost password form
				if(!$this->authenticated) {
					ob_start();
?>
<?php echo "<div id=\"authform\" style=\"width: ".$width."; height: ".$height."; font-size: ".$fontsize.";\">\n"; ?>
<form method="post" action="<?php echo $this->curPageURL(); ?>">

<h1><?php echo self::$language["form_lostpw_title"]; ?></h1>

<?php
if(!empty(self::$message)) {
	echo '<div id="authform_message">'. self::$message .'</div>';
}
?>

<div id="authform_user">
<label for="username"><?php echo self::$language["form_lostpw_field_username"]; ?></label>
<input type="email" name="username" id="username" required />
</div><!--

--><div id="authform_submit">
<?php echo '<input type="submit" value="'.self::$language["form_lostpw_submit"].'" class="auth_button" />'."\n"; ?>
</div>

</form>

</div><!-- authform -->
<?php
					$template = new Template(self::$language["form_lostpw_title"],ob_get_clean());
					echo $template->code();
				} else {
					// redirect back to main page.
					header("Location: ".$this->remove_querystring_var($this->curPageURL(),ACTION_VAR));
				}
				break;
			case "password": // forgot password / after email
				if(!$this->authenticated && self::authToken($_GET['uid'],$_GET['token'])) {
					ob_start();
?>
<?php echo "<div id=\"authform\" style=\"width: ".$width."; height: ".$height."; font-size: ".$fontsize.";\">\n"; ?>
<form method="post" action="<?php echo $this->curPageURL(); ?>">

<h1><?php echo self::$language["form_password_title"]; ?></h1>

<?php
if(!empty(self::$message)) {
	echo '<div id="authform_message">'. self::$message .'</div>';
}
?>

<div id="authform_user">
<label for="username">Username (Email)</label>
<p id="username"><?php echo self::getUsername($_GET["uid"]); ?><p>
</div><br /><div id="authform_pass">
<label for="password"><?php echo self::$language["form_password_field_pw"]; ?></label>
<input type="password" name="password" id="password" required />
<label for="repeat_password"><?php echo self::$language["form_password_field_pw_repeat"]; ?></label>
<input type="password" name="repeat_password" id="repeat_password" required />
</div><br />
<br />
<div id="authform_submit">
<?php echo '<input type="submit" value="'.self::$language["form_password_submit"].'" class="auth_button" />'."\n"; ?>
</div>

</form>

</div><!-- authform -->
<?php
					$template = new Template(self::$language["form_password_title"],ob_get_clean());
					echo $template->code();
				} else {
					// redirect back to main page.
					$url = $this->remove_querystring_var($this->curPageURL(),ACTION_VAR);
					$url = $this->remove_querystring_var($url,"uid");
					$url = $this->remove_querystring_var($url,"token");
					header("Location: ".$url);
				}
				break;
			case "resend_activation": // Lost password form
				if(!$this->authenticated) {
					ob_start();
?>
<?php echo "<div id=\"authform\" style=\"width: ".$width."; height: ".$height."; font-size: ".$fontsize.";\">\n"; ?>
<form method="post" action="<?php echo $this->curPageURL(); ?>">

<h1><?php echo self::$language["form_resendactivation_title"]; ?></h1>

<?php
if(!empty(self::$message)) {
	echo '<div id="authform_message">'. self::$message .'</div>';
}
?>

<div id="authform_user">
<label for="username"><?php echo self::$language["form_resendactivation_field_username"]; ?></label>
<input type="email" name="username" id="username" required />
</div><!--

--><div id="authform_submit">
<?php echo '<input type="submit" value="'.self::$language["form_resendactivation_submit"].'" class="auth_button" />'."\n"; ?>
</div>

</form>

</div><!-- authform -->
<?php
					$template = new Template(self::$language["form_resendactivation_title"],ob_get_clean());
					echo $template->code();
				} else {
					// redirect back to main page.
					header("Location: ".$this->remove_querystring_var($this->curPageURL(),ACTION_VAR));
				}
				break;
			case "login":
				if(!$this->authenticated) {
					ob_start();
?>
<?php echo "<div id=\"authform\" style=\"width: ".$width."; height: ".$height."; font-size: ".$fontsize.";\">\n"; ?>
<form method="post" action="<?php echo $this->curPageURL(); ?>">

<h1><?php echo self::$language["form_login_title"]; ?></h1>

<?php
if(!empty(self::$message)) {
	echo '<div id="authform_message">'. self::$message .'</div>';
}
?>

<div id="authform_user">
<label for="username"><?php echo self::$language["form_login_field_username"]; ?></label>
<input type="email" name="username" id="username" required />
</div><!--
--><div id="authform_pass">
<label for="password"><?php echo self::$language["form_login_field_password"] ." <small>(<a href=\"".self::add_querystring_var(self::curPageURL(),ACTION_VAR,"lostpassword")."\">".self::$language["form_login_forgot_password"]."</a>)</small>"; ?></label>
<input type="password" name="password" id="password" required />
</div><!--
--><div id="authform_submit">
<?php echo '<input type="submit" value="'.self::$language["form_login_submit"].'" class="auth_button" />'."\n";?>
</div>

</form>

</div><!-- authform -->
<?php
					$template = new Template(self::$language["form_login_title"],ob_get_clean());
					echo $template->code();
				} else {
					// redirect back to main page.
					header("Location: ".$this->remove_querystring_var($this->curPageURL(),ACTION_VAR));
				}
				break;
			default: // login or Create Account
				if(!$this->authenticated) {
					ob_start();
?>
<?php echo "<div id=\"authform\" style=\"width: ".$width."; height: ".$height."; font-size: ".$fontsize.";\">\n"; ?>

<h1><?php echo self::$language["form_login_title"]; ?></h1>

<?php
if(!empty($_SESSION['message'])) {
	echo '<div id="authform_message">'. $_SESSION['message'] .'</div>';
}
?>

<div class="login_or_create">

<?php
echo "<a href=\"?".ACTION_VAR."=login\">".self::$language["form_welcome_signin"]."</a>";
echo " or ";
echo "<a href=\"?".ACTION_VAR."=register\" class=\"auth_button\">".self::$language["form_welcome_create"]."</a>\n";
?>

</div><!-- .login_or_create -->
<?php
					$template = new Template(self::$language["form_welcome_title"],ob_get_clean());
					echo $template->code();
				} else {
					// redirect back to main page.
					header("Location: ".$this->remove_querystring_var($this->curPageURL(),ACTION_VAR));
				}
				break;
		}
	}
	
	private function login() {
		if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["username"]) && isset($_POST["password"])) {
			if(self::exists("email",$_POST["username"])) {
				if(self::active($_POST["username"])) {
					if(self::auth($_POST['username'], $_POST['password'])) {
						// logged in
						unset($_SESSION['message']);
					} else {
						self::$message = self::$language["status_login_incorrect"];
					}
				} else {
					self::$message = self::$language["status_login_inactive"];
				}
			} else {
				self::$message = self::$language["status_login_incorrect"];
			}
		}
	}
	
	private function logout() {
		$this->authenticated = false;
		session_unset();
		session_destroy();
		header("Location: ".$this->remove_querystring_var($this->curPageURL(),ACTION_VAR));
	}
	
	private function register() {
		if(isset($_GET["uid"]) && isset($_GET["token"])) {
			if(self::authToken($_GET["uid"],$_GET["token"])) {
				if(self::activate($_GET["uid"])) {
					self::removeToken(self::getUsername($_GET["uid"]));
					$_SESSION['message'] = self::$language["status_register_activated"];
					$url = $this->remove_querystring_var($this->curPageURL(),ACTION_VAR);
					$url = $this->remove_querystring_var($url,"uid");
					$url = $this->remove_querystring_var($url,"token");
					header("Location: ".$url);
				}
			}
		} else if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["repeat_password"])) {
			if(self::valid("username",$_POST["username"]) && self::valid("password",$_POST["password"],$_POST["repeat_password"])) {
				if(self::exists('email',$_POST["username"])) { // check for duplicate username
					self::$message = self::$language["status_register_fail_taken"];
				} else { // continue registration
					if(self::addUser()) {
						if(self::setPassword($_POST["username"],$_POST["password"])) {
							if(self::sendActivation($_POST["username"])) {
								// Registration successful, (still need activation)
								$_SESSION['message'] = self::$language["status_register_activate"];
								header("Location: ".$this->remove_querystring_var($this->curPageURL(),ACTION_VAR));
							}
						}
					}
				}
			}
		}
	}
	
	private function sendActivation($username) {
		$token = self::newToken($username);
		if(self::sendEmail("register_confirm",$username,$token)) {
			// activation email sent
			return true;
		}
	}
	
	private function resendActivation() {
		if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["username"])) {
			if(self::sendActivation($_POST["username"])) {
				$_SESSION['message'] = self::$language["status_resendactivation_activate"];
				header("Location: ".$this->remove_querystring_var($this->curPageURL(),ACTION_VAR));
				return true;
			}
		}
	}
	
	private function updateProfile() {
		if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["password"]) && isset($_POST["repeat_password"])) {
			if(self::valid("username",$_SESSION['username']) && self::valid("password",$_POST["password"],$_POST["repeat_password"])) {
				if(self::exists("email",$_SESSION['username'])) {
					if(self::setPassword($_SESSION['username'],$_POST["password"])) {
						if(self::sendEmail("profile_updated",$_SESSION['username'])) {
							// profile update successfull
							self::$message = self::$language["status_profile_success"];
						}
					}
				}
			}
		}
	}
	
	// update password for users who have lost password using uid and token authentication.
	private function updatePassword() {
		if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["password"]) && isset($_POST["repeat_password"])) {
			if(self::valid("password",$_POST["password"],$_POST["repeat_password"])) {
				if(self::exists("email",self::getUsername($_GET["uid"]))) {
					if(self::setPassword(self::getUsername($_GET['uid']),$_POST["password"])) {
						if(self::sendEmail("password_updated",self::getUsername($_GET['uid']))) {
							// password update successfull
							self::removeToken(self::getUsername($_GET['uid']));
							$url = $this->remove_querystring_var($this->curPageURL(),ACTION_VAR);
							$url = $this->remove_querystring_var($url,"uid");
							$url = $this->remove_querystring_var($url,"token");
							header("Location: ".$url);
							$_SESSION['message'] = self::$language["status_password_success"];
						}
					}
				}
			}
		}
	}
	
	// send reset email to user
	private function lostPassword() {
		if($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST["username"])) {
			if(self::valid("username",$_POST['username'])) {
				if(self::exists('email',$_POST['username'])) {
					$token = self::newToken($_POST["username"]);
					if(self::sendEmail("password_reset",$_POST['username'],$token)) {
						$_SESSION['message'] = self::$language["status_lost_sent"];
					}
				} else {
					self::$message = self::$language["status_lost_noexist"];
				}
			}
		}
	}
	
	// add user to database
	private function addUser() {
		self::$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$query = "INSERT INTO ". DB_TABLE ." SET email = '". self::$db->real_escape_string($_POST["username"]) ."', auth_level = 0;";
		if(self::$db->query($query)) {
			self::$db->close();
			return true;
		} else {
			self::$db->close();
			return false;
		}
	}
	
	private function setPassword($email,$p) {
		$password = $p;
		$hasher = new PasswordHash(HASH_COST_LOG2, HASH_PORTABLE);
		$hash = $hasher->HashPassword($password);
		
		if(strlen($hash) >= 20) {
			self::$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);

			if(self::$db->query("UPDATE ".DB_TABLE." SET password = '".self::$db->real_escape_string($hash)."' WHERE email = '". self::$db->real_escape_string($email) ."';")) {
				self::$db->close();
				return $password;
			} else {
				self::$db->close();
				return false;
			}
		} else {
			return false;
		}
	}
	
	private function sendEmail($type,$to,$token = null) {
		switch($type) {
			case "register_confirm":
				$subject = "Thank you for registering!";
				ob_start();
?>
Someone (maybe you) has registered at <?php echo $this->remove_querystring_var($this->curPageURL(),ACTION_VAR); ?> using your email address.

Please click the link below to activate your new account. If you did not submit this request please disregard this message.

<?php echo $this->remove_querystring_var($this->curPageURL(),ACTION_VAR)."?".ACTION_VAR."=register&uid=".urlencode(self::getUid($to))."&token=".urlencode($token); ?>
<?php
				$body = ob_get_clean();
				break;
			case "password_reset":
				if($token) {
					$subject = "New Password Request";
					ob_start();
?>
Someone (maybe you) has requested a password change for your account at <?php echo $this->remove_querystring_var($this->curPageURL(),ACTION_VAR); ?>.

Please click the link below to change your password. If you did not submit this request please disregard this message.

<?php echo $this->remove_querystring_var($this->curPageURL(),ACTION_VAR)."?".ACTION_VAR."=password&uid=".urlencode(self::getUid($to))."&token=".urlencode($token); ?>
<?php
					$body = ob_get_clean();
				}
				break;
			case "profile_updated":
				$subject = "Profile Updated";
				ob_start();
?>
Your profile has been updated.
<?php
				$body = ob_get_clean();
				break;
			case "password_updated":
				$subject = "Password changed";
				ob_start();
?>
Your password has been changed.
<?php
				$body = ob_get_clean();
				break;
		}
		
		$header = "From: ". SITE_NAME ." <". SITE_EMAIL .">\n";
		if(!empty($subject) && !empty($body)) {
			if(mail($to,$subject,$body,$header)) {
				return true;
			}
		}
	}
	
	private function exists($field,$val) {
		self::$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$query = "SELECT email FROM ".DB_TABLE." WHERE email = '". self::$db->real_escape_string($val) ."'";
		
		if($result = self::$db->query($query)) {
			if($result->num_rows > 0) {
				self::$db->close();
				return true;
			} else {
				return false;
			}
		} else {
			self::$db->close();
		}
	}
	
	private function newToken($email) {
		$token = uniqid();
		
		self::$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$result = self::$db->query("UPDATE ".DB_TABLE." SET token = '".self::$db->real_escape_string($token)."' WHERE email = '".self::$db->real_escape_string($email)."'");
		if($result) {
			self::$db->close();
			return $token;
		} else {
			self::$db->close();
			return false;
		}
	}
	
	private function removeToken($email) {
		self::$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$result = self::$db->query("UPDATE ".DB_TABLE." SET token = NULL WHERE email = '".self::$db->real_escape_string($email)."'");
		if($result) {
			self::$db->close();
			return true;
		} else {
			self::$db->close();
			return false;
		}
	}

	private function auth($u,$p) {
		$hasher = new PasswordHash(HASH_COST_LOG2, HASH_PORTABLE);
		self::$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$result = self::$db->query("SELECT password,active FROM ".DB_TABLE." WHERE email = '".self::$db->real_escape_string($u)."';");
		if($row = $result->fetch_array(MYSQLI_ASSOC)) {
			if($hasher->CheckPassword($p,$row["password"])) {
				// logged in
				$_SESSION['signed_in'] = "TRUE";
				$_SESSION['username'] = $u;
				
				header("Location: ".$this->remove_querystring_var($this->curPageURL(),ACTION_VAR));
				self::$db->close();
				return true;
			} else {
				self::$db->close();
				return false;
			}
		} else {
			self::$db->close();
			return false;
		}
	}
	
	private function authToken($id,$token) {
		$token = urldecode($token);
	
		self::$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$result = self::$db->query("SELECT token FROM ".DB_TABLE." WHERE user_id = '".self::$db->real_escape_string($id)."';");
		if($row = $result->fetch_array(MYSQLI_ASSOC)) {
			if($token == $row["token"]) {
				// valid token
				self::$db->close();
				return true;
			} else {
				self::$db->close();
			}
		} else {
			self::$db->close();
		}
	}
	
	private function activate($id) {
		$id = urldecode($id);
		self::$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$result = self::$db->query("UPDATE ".DB_TABLE." SET active = 1 WHERE user_id = '".self::$db->real_escape_string($id)."'");
		if($result) {
			self::$db->close();
			return true;
		} else {
			self::$db->close();
			return false;
		}
	}
	
	private function active($email) {
		self::$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$result = self::$db->query("SELECT active FROM ".DB_TABLE." WHERE email = '".self::$db->real_escape_string($email)."'");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		if($row["active"] == 1) {
			self::$db->close();
			return true;
		} else {
			self::$db->close();
			return false;
		}
	}
	
	private function valid($type,$d1 = null,$d2 = null) {
		$valid = true;
		
		switch($type) {
			case "username":
				if(!preg_match(USERNAME_REGEX,$d1)) {
						$valid = false;
						self::$message = self::$langauge["username_req"];
				}
				break;
			case "password":
				if($d1 !== $d2) {
					$valid = false;
					self::$message = "Passwords do not match.";
				} else {
					if(!preg_match(PASSWORD_REGEX,$d1)) {
						$valid = false;
						self::$message = self::$language["password_req"];
					}
				}
				break;
		}
		
		return $valid;
	}
	
	private function getAccessLevel($email) {
		self::$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$result = self::$db->query("SELECT auth_level FROM ".DB_TABLE." WHERE email = '".self::$db->real_escape_string($email)."'");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		if(!empty($row)) {
			self::$db->close();
			return (int) $row["auth_level"];
		} else {
			self::$db->close();
			return 0;
		}
	}
	
	private function getUid($email) {
		self::$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$result = self::$db->query("SELECT user_id FROM ".DB_TABLE." WHERE email = '".self::$db->real_escape_string($email)."'");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		if(!empty($row)) {
			self::$db->close();
			return $row["user_id"];
		} else {
			self::$db->close();
			return false;
		}
	}
	
	private function getUsername($uid) {
		self::$db = new mysqli(DB_HOST,DB_USER,DB_PASS,DB_NAME);
		$result = self::$db->query("SELECT email FROM ".DB_TABLE." WHERE user_id = '".self::$db->real_escape_string($uid)."'");
		$row = $result->fetch_array(MYSQLI_ASSOC);
		if(!empty($row)) {
			self::$db->close();
			return $row["email"];
		} else {
			self::$db->close();
			return false;
		}
	}
	
	private function spam() {

		if(isset($_SESSION['last_attempt']) && isset($_SESSION['attempts'])) {
			if(!empty($_SESSION['last_attempt']) && (int)$_SESSION['attempts'] >= MAX_ATTEMPTS) {
				$current_time = new DateTime();
				$last_time = $_SESSION['last_attempt'];
				$interval = $last_time->diff($current_time);
				$minutes_since = (int)$interval->format('%R%i');
				self::$minutes_left = ATTEMPT_DELAY - $minutes_since;
				
				if(self::$minutes_left <= 0) {
					// Times up - reset form submission attempts to 0 to allow visitor to try again.
					$_SESSION['attempts'] = 0;
				}
			}
		}
		
		if(isset($_SESSION['attempts'])) {
			if(MAX_ATTEMPTS > $_SESSION['attempts']) {
				if(!$this->authenticated && $_SERVER['REQUEST_METHOD'] == "POST") {
					$_SESSION['last_attempt'] = new DateTime();
					$_SESSION['attempts']++;
					self::$attempts = $_SESSION['attempts'];
				}
				return false;
			} else {
				return true;
			}
		}
	}
	
	public function logoutLink() {
		return "<a href=\"?".ACTION_VAR."=logout\">logout</a>";
	}
	
	public function updateProfileLink() {
		return "<a href=\"?".ACTION_VAR."=profile\">update profile</a>";
	}
	
	/***	
	Utility functions Below
	***/
	
	function add_querystring_var($url, $key, $value) {
		$url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
		$url = substr($url, 0, -1);
		if (strpos($url, '?') === false) {
			return ($url . '?' . $key . '=' . $value);
		} else {
			return ($url . '&' . $key . '=' . $value);
		}
	}

	private function remove_querystring_var($url, $key) {
		$url = preg_replace('/(.*)(\?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
		$url = substr($url, 0, -1);
		return $url;
	}
	
	private function curPageURL() {
		$pageURL = 'http';
		if(!empty($_SERVER["HTTPS"])) {
			if($_SERVER["HTTPS"] == "on") {
				$pageURL .= "s";
			}
		}
		
		$pageURL .= "://";
		
		if($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		
		return $pageURL;
	}
}
?>
