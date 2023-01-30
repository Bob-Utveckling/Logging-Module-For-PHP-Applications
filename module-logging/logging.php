<!doctype HTML>

<?php

	global $conn;
		
	$log_table_fields = "`system_date`,`date`,`what_table`,`what_record`,`describe_action`,`note`,`IP_or_general_location_description`,`username`,`system_user_is_using`";	

	$system_date = date("Y-m-d h:i:sa") ;
	//$system_date = "CURRENT_TIMESTAMP" ;
	$date = "N/A";
	$what_table = "N/A";
	$what_record = "N/A";
	$describe_action = "N/A";
	$note = "N/A";
	$IP_or_general_location_description = $_SERVER['REMOTE_ADDR'];
	$username = "admin";
	$system_user_is_using = "N/A"; //find Windows way to get it, perhaps via JS

	if (!function_exists('return_list_of_names_and_values')) {
		function return_list_of_names_and_values() {
			return "system_date='$system_date', date='$date',
				what_table='$what_table', what_record='$what_record',
				type_of_operation='$type_of_operation', note='$note',
				IP='$IP', username='$username',
				system_user_is_using='$system_user_is_using'";
		}
	}
			
	if (!function_exists('what_was_the_sql_command_type')) {
		function what_was_the_sql_command_type($getSqlQuery) {
			$charsInsert = substr($getSqlQuery, 0, 11);
			$charsUpdate = substr($getSqlQuery,0, 6);
			if ($charsInsert == "INSERT INTO") {
				return "insert";
			} else if ($charsUpdate == "UPDATE") {
				return "update";
			} else {
				return "N/A";
			}
		}
	}
	

	if (!function_exists('what_table_is_mentioned_in_the_sql_command')) {	
		function what_table_is_mentioned_in_the_sql_command($getSqlQuery) {
			$explodedString = explode(" ", $getSqlQuery);
			$piece = "";
			if (what_was_the_sql_command_type($getSqlQuery)=="insert") {
				$piece = $explodedString[2]; //insert into TABLENAME
				$piece = str_replace("'","",$piece); 
				$piece = str_replace('"',"",$piece); 
				$piece = str_replace("`","",$piece); 
			} else if (what_was_the_sql_command_type($getSqlQuery)=="update") {
				$piece = $explodedString[1]; //update TABLENAME
				$piece = str_replace("'","",$piece); 
				$piece = str_replace('"',"",$piece); 
				$piece = str_replace("`","",$piece); 			
			}
			//echo "<h1>Piece: " . $piece . "</h1>";
			return $piece;
		}
	}

	if (!function_exists('what_record_is_mentioned_in_the_sql_command')) {		
		function what_record_is_mentioned_in_the_sql_command($getSqlQuery) {
			global $conn;
			if (what_was_the_sql_command_type($getSqlQuery) == "update") {
				$pos = strrpos($getSqlQuery,"id");
				$id_piece = substr($getSqlQuery, $pos);
				$id_piece = str_replace(";","",$id_piece); 
				return ($id_piece);
			} else if (what_was_the_sql_command_type($getSqlQuery) == "insert") {
				//get the last record that was inserted into the specific table
				$tableName = what_table_is_mentioned_in_the_sql_command($getSqlQuery);
				$sql="SELECT id from $tableName WHERE id IS NOT NULL order by id desc LIMIT 0,1";
				$result = mysqli_query($conn, $sql);
				$row = mysqli_fetch_row($result);
				return ("id=" . $row[0]);
			}		
		}
	}
	
	if (!function_exists('log_this_change_to_database')) {		
		function log_this_change_to_database($getSqlQuery) {		
			global $conn;
			global $show_how_many_log_entries;
			global $log_table;
			global $log_table_fields;

			global $system_date;
			global $date;
			global $what_table;
			global $what_record;
			global $describe_action;
			global $note;
			global $IP_or_general_location_description;
			global $username;
			global $system_user_is_using;
			
			//echo ("logging...<br>");
			//echo ("<b>received Sql:</b>" . $getSqlQuery . "<br>");
			
			//prepare - extract date from sql command
			//implement
			
			//prepare - extract what_table from sql command
			$what_table = what_table_is_mentioned_in_the_sql_command($getSqlQuery);
			
			//prepare - extract what_record from sql command
			$what_record = what_record_is_mentioned_in_the_sql_command($getSqlQuery);
			
			//prepare - extract action - describe action from sql command
			switch (what_was_the_sql_command_type($getSqlQuery)) {
				case "insert":		
					$describe_action = "Inserting";
					echo "<p>action: " . $describe_action;
					break;
				case "update":
					$describe_action = "Updating";								
					break;
			}
			
			//prepare - set a note for $note
			
			//prepare - set value of IP_or_general_location_description
			
			//prepare - extract username
			
			//prepare - extract system_user_is_using
			
			$sqlStatement = "INSERT INTO `$log_table` ($log_table_fields) VALUES (".
					"'$system_date','$date','$what_table','$what_record','$describe_action','$note',".
					"'$IP_or_general_location_description','$username','$system_user_is_using'".
					")";
			//echo "<p><b>apply SQL:</b> $sqlStatement <br><br>";
			if (mysqli_query($conn, $sqlStatement)) {	
				//echo "Logged to the log table";
			}				
			else {
				echo "<br>Error: " . $sqlStatement . "<br>" . mysqli_error($conn);
			}
			
		show_logs($show_how_many_log_entries);
		}
	}
?>