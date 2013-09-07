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

require_once "config.php";

class InstallAuthentication {
	// MySQL DB configuration
	private static $db_host = DB_HOST;
	private static $db_user = DB_USER;
	private static $db_pass = DB_PASS;
	private static $db_name = DB_NAME;
	private static $db_table = DB_TABLE;
	
	private static $link;

	function __construct() {
		self::$link = new mysqli(self::$db_host,self::$db_user,self::$db_pass,self::$db_name);
		
		if(self::$link->connect_errno) {
			echo "Failed to connect. Check DB configuration in config.php. ".self::$link->connect_error;
			exit;
		}
		
		if(!self::$link->query(self::createTableQuery())) {
			echo "Failed to create table. Installation failed. ".self::$link->error;
			exit;
		} else {
			echo "Table created successfully. Installation complete. <span style=\"color: red;\">DELETE install.php</span>";
		}
		
		self::$link->close();
	}

	function createTableQuery() {
		$sql_create_table =
		"CREATE TABLE IF NOT EXISTS ".self::$db_table."  (
			user_id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
			email varchar(254),
			password varchar(60),
			auth_level int(10),
			token varchar(60),
			active boolean,
			last_login datetime";
		if(!empty(self::$custom_cols)) {
			$i = 0;
			$sql_create_table .= ",\n";
			foreach(self::$custom_cols as $col) {
				$i++;
				$sql_create_table .= "\t\t\t".$col.($i == count(self::$custom_cols) ? "\n" : ",\n");
			}
		}
		$sql_create_table .= " );";
		
		return $sql_create_table;
	}
}

$install = new InstallAuthentication();
?>
