<?php
//
// Portable PHP password hashing framework.
//
// Version 0.6 / secure.
//
// Original written by Solar Designer <solar at openwall.com> in 2004-2006 and placed in
// the public domain. Revised in subsequent years, still public domain.
//
// Updated in 2025 to support Argon2id using PHP's password_hash for enhanced security,
// while preserving compatibility with legacy hashes.
//
// There's absolutely no warranty.
//
// The homepage URL for this framework is:
//
//	https://www.openwall.com/phpass/
//
// Please be sure to update the Version line if you edit this file in any way.
// It is suggested that you leave the main version number intact, but indicate
// your project name (after the slash) and add your own revision information.
//
// Feel free to switch to a different hash type identifier if needed, but maintain
// compatibility with existing systems where possible.
//
class PasswordHash {
	public $itoa64;
	public $iteration_count_log2;
	public $portable_hashes;
	public $random_state;

	function __construct($iteration_count_log2, $portable_hashes) {
		$this->itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

		if ($iteration_count_log2 < 4 || $iteration_count_log2 > 31)
			$iteration_count_log2 = 8;
		$this->iteration_count_log2 = $iteration_count_log2;

		$this->portable_hashes = $portable_hashes;

		$this->random_state = microtime();
		if (function_exists('getmypid'))
			$this->random_state .= getmypid();
	}

	function get_random_bytes($count) {
		try {
			return random_bytes($count);
		} catch (Exception $e) {
			$output = '';
			for ($i = 0; $i < $count; $i += 32) {
				$this->random_state = hash('sha256', microtime() . $this->random_state, true);
				$output .= hash('sha256', $this->random_state, true);
			}
			return substr($output, 0, $count);
		}
	}

	function encode64($input, $count) {
		$output = '';
		$i = 0;
		do {
			$value = ord($input[$i++]);
			$output .= $this->itoa64[$value & 0x3f];
			if ($i < $count)
				$value |= ord($input[$i]) << 8;
			$output .= $this->itoa64[($value >> 6) & 0x3f];
			if ($i++ >= $count)
				break;
			if ($i < $count)
				$value |= ord($input[$i]) << 16;
			$output .= $this->itoa64[($value >> 12) & 0x3f];
			if ($i++ >= $count)
				break;
			$output .= $this->itoa64[($value >> 18) & 0x3f];
		} while ($i < $count);

		return $output;
	}

	function gensalt_private($input) {
		$output = '$P$';
		$output .= $this->itoa64[min($this->iteration_count_log2 +
			((PHP_VERSION >= '5') ? 5 : 3), 30)];
		$output .= $this->encode64($input, 6);

		return $output;
	}

	function crypt_private($password, $setting) {
		$output = '*0';
		if (substr($setting, 0, 2) === $output)
			$output = '*1';

		$id = substr($setting, 0, 3);
		// We use "$P$", phpBB3 uses "$H$" for the same thing
		if ($id !== '$P$' && $id !== '$H$')
			return $output;

		$count_log2 = strpos($this->itoa64, $setting[3]);
		if ($count_log2 < 7 || $count_log2 > 30)
			return $output;

		$count = 1 << $count_log2;

		$salt = substr($setting, 4, 8);
		if (strlen($salt) !== 8)
			return $output;

		// Using MD5 for legacy portable hashes.
		$hash = md5($salt . $password, true);
		do {
			$hash = md5($hash . $password, true);
		} while (--$count);

		$output = substr($setting, 0, 12);
		$output .= $this->encode64($hash, 16);

		return $output;
	}

	function gensalt_blowfish($input) {
		// This one needs to use a different order of characters and a
		// different encoding scheme from the one in encode64() above.
		// We care because the last character in our encoded string will
		// only represent 2 bits. While two known implementations of
		// bcrypt will happily accept and correct a salt string which
		// has the 4 unused bits set to non-zero, we do not want to take
		// chances and we also do not want to waste an additional byte
		// of entropy.
		$itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

		$output = '$2a$';
		$output .= chr(ord('0') + $this->iteration_count_log2 / 10);
		$output .= chr(ord('0') + $this->iteration_count_log2 % 10);
		$output .= '$';

		$i = 0;
		do {
			$c1 = ord($input[$i++]);
			$output .= $itoa64[$c1 >> 2];
			$c1 = ($c1 & 0x03) << 4;
			if ($i >= 16) {
				$output .= $itoa64[$c1];
				break;
			}

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 4;
			$output .= $itoa64[$c1];
			$c1 = ($c2 & 0x0f) << 2;

			$c2 = ord($input[$i++]);
			$c1 |= $c2 >> 6;
			$output .= $itoa64[$c1];
			$output .= $itoa64[$c2 & 0x3f];
		} while (1);

		return $output;
	}

	function HashPassword($password) {
		if (in_array('argon2id', password_algos()) && !$this->portable_hashes) {
			$time_cost = max(3, $this->iteration_count_log2 - 4);
			$options = [
				'time_cost' => $time_cost,
				'memory_cost' => 65536,
				'threads' => 1,
			];
			return password_hash($password, PASSWORD_ARGON2ID, $options);
		}

		if (CRYPT_BLOWFISH === 1 && !$this->portable_hashes) {
			$random = $this->get_random_bytes(16);
			$hash = crypt($password, $this->gensalt_blowfish($random));
			if (strlen($hash) === 60)
				return $hash;
		}

		$random = $this->get_random_bytes(6);
		$hash = $this->crypt_private($password, $this->gensalt_private($random));
		if (strlen($hash) === 34)
			return $hash;

		// Returning '*' on error is safe here, but would _not_ be safe
		// in a crypt(3)-like function used _both_ for generating new
		// hashes and for validating passwords against existing hashes.
		return '*';
	}

	function CheckPassword($password, $stored_hash) {
		if (password_verify($password, $stored_hash)) {
			return true;
		}

		$hash = $this->crypt_private($password, $stored_hash);
		if ($hash[0] === '*')
			$hash = crypt($password, $stored_hash);

		// This is not constant-time. In order to keep the code simple,
		// for timing safety we currently rely on the salts being
		// unpredictable, which they are at least in the non-fallback
		// cases (that is, when we use /dev/urandom and bcrypt).
		return $hash === $stored_hash;
	}
}

?>