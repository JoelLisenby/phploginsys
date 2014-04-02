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

$words = array(

/**
* Status messages
**/

// Universal
"status_spam" => "Too many attempts. Try again in ".self::$minutes_left." minute".(self::$minutes_left == 1 ? "" : "s").".",
"username_req" => "Username must be a valid email address",
"password_req" => "Password must be at least 8 characters long and contain a lower case letter, upper case letter, number, and a special character (@#$%!).",
"sessions_req" => "Error! Authentication requires sessions.",

// login()
"status_login_success" => "Login successful",
"status_login_incorrect" => "Username or password incorrect, please try again.",
"status_login_inactive" => "Please activate your account. <a href=\"?".ACTION_VAR."=resend_activation\">Resend Activation Email</a>",


// register()
"status_register_activate" => "Almost done! Please check your email to complete your registration.",
"status_register_activated" => "Your account is now active.",
"status_register_fail_taken" => "Username taken, try a different username.",

// resendActivation()
"status_resendactivation_activate" => "We have resent your activation link! Please check your email to complete your registration.",

// updateProfile()
"status_profile_success" => "Profile updated.",

// updatePassword()
"status_password_success" => "Password updated.",

// lostPassword()
"status_lost_sent" => "Password reset email has been sent.",
"status_lost_noexist" => "User does not exist.",

/**
* Error Messages
**/
"error_register_token" => "",
"error_register_add_query" => "",

/**
* Form
**/

// Register
"form_register_title" => "Create an Account",
"form_register_field_username" => "Username (email)",
"form_register_field_pw" => "Password",
"form_register_field_repeat_pw" => "Re-enter Password",
"form_register_submit" => "Create Account",

// Update Profile
"form_profile_title" => "Update your profile details",
"form_profile_field_username" => "Username (email)",
"form_profile_field_pw" => "Password",
"form_profile_field_pw_repeat" => "Re-Enter Password",
"form_profile_submit" => "Update",

// Lost Password
"form_lostpw_title" => "Password recovery",
"form_lostpw_field_username" => "Username (email)",
"form_lostpw_submit" => "Send Request",

// Resend Activation
"form_resendactivation_title" => "Resend activation email",
"form_resendactivation_field_username" => "Username (email)",
"form_resendactivation_submit" => "Resend Activation Email",


// Password
"form_password_title" => "Update your password",
"form_password_field_pw" => "Password",
"form_password_field_pw_repeat" => "Re-Enter Password",
"form_password_submit" => "Update",

// Login
"form_login_title" => "Please sign in",
"form_login_field_username" => "Username (email)",
"form_login_field_password" => "Password",
"form_login_forgot_password" => "Forgot password",
"form_login_submit" => "Sign in",

// Welcome
"form_welcome_title" => "Welcome!",
"form_welcome_signin" => "Sign in",
"form_welcome_create" => "Create an Account"

);
?>
