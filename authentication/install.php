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

require_once "config.php";

class InstallAuthentication {
	// Custom columns (if any), declare as empty array by default
	private static $custom_cols = [];

	private $pdo;

	function __construct() {
		try {
			$this->pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASS);
			$this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$this->pdo->exec(self::createTableQuery());

			echo "Table created successfully. Installation complete. <span style=\"color: red;\">DELETE install.php</span>";
		} catch (PDOException $e) {
			echo "Failed to connect or create table. Check DB configuration in config.php. " . $e->getMessage();
			exit;
		}
	}

	function createTableQuery() {
		$sql_create_table = 
		"CREATE TABLE IF NOT EXISTS " . DB_TABLE . " (
			user_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
			email VARCHAR(254),
			password VARCHAR(60),
			auth_level INT(10),
			token VARCHAR(60),
			active TINYINT(1),
			disabled TINYINT(1),
			date_created DATETIME,
			date_last_login DATETIME";

		if (!empty(self::$custom_cols)) {
			$sql_create_table .= ",";
			foreach (self::$custom_cols as $col) {
				$sql_create_table .= "\n\t" . $col . ",";
			}
			// Remove the trailing comma after the last custom column
			$sql_create_table = rtrim($sql_create_table, ',');
		}

		$sql_create_table .= "\n);";

		return $sql_create_table;
	}
}

$install = new InstallAuthentication();
?>