<style>
	#logDiv {
		border: 1px solid green;
		padding: 5px;
		background-color: lightgreen;
	}
	.nameInfo {
		background-color:f6f6cb;
		margin-top: 5px;
	}
	
	.wrapper {
	  padding: 5px;
	  background: #eaeaea;
	  max-width: 600px;
	  max-height: 250px;
	  overflow: scroll;
	  /*overflow: hidden;*/ /* add this to contain floated children */
	}
	
	.field {
	  font-size:12px;
	  margin: 4px;
	  overflow: hidden;
	  white-space: nowrap;
	  text-overflow: ellipsis;
	  max-width: 250px;
	  max-height: 100px;
	  float: left;
	  border: 1px dashed green;
	}
</style>
<script>
	function showHideLogs() {		
		logDiv = document.getElementById("logs");
		myButton = document.getElementById("show_hide_button");		
		if (myButton.innerText=="Hide -") {
			//logDiv.style.visibility = "hidden";
			logDiv.style.display = "none";
			myButton.innerHTML = "Show + ";
		} else if (myButton.innerText=="Show +") {
			logDiv.style.visibility = "visible";
			logDiv.style.display = "inline";
			myButton.innerHTML = "Hide -";
		}
	}
</script>
<?php

global $conn;
global $log_table;

if (!function_exists('get_resultset')) {	
	function get_resultset($amount) {
		global $conn;
		global $log_table;
		$sql = "SELECT * FROM $log_table order by id desc limit 0,$amount";
		if ($result = mysqli_query($conn, $sql)) {
			return $result;
		} else {
			echo "BL Error: Could not retrieve log data from log table";
		}
		return $result;
	}
}


if (!function_exists('update_array_TablenameId_Count')) {	
	function update_array_TablenameId_Count($result) {
		$array_TablenameId_Count = [];
		while ($row = mysqli_fetch_array($result)) {
			$id = explode("=",$row["what_record"])[1];
			$key = $row["what_table"] . "_" . $id;			
			//echo "<br>KEY: " . $key;
			if (array_key_exists($key, $array_TablenameId_Count)) {
				$array_TablenameId_Count[$key] += 1;
			} else {
				$array_TablenameId_Count[$key] = 1;
			}
		}
		return $array_TablenameId_Count;
	}
}

if (!function_exists('fill_array')) {	
	function fill_array($howmany) {
		$round = 0;
		while (true) {
			$round += 1;
			$amount= $howmany * $round;
			$rs = get_resultset($amount);
			$dict = update_array_TablenameId_Count($rs);
			if (sizeof($dict) >=  $howmany) {
				//has enough entries in dict. exit loop
				break;
			} //else load a new set: round times howmany
		}
		
		/*foreach ($dict as $x=>$y) {
			echo "<br>$x = $y";
		}*/
		
		//now limit $dict since it might have more entries
		$dict1 = [];
		//echo "<br>----- final: -----";
		$i = 0;
		foreach ($dict as $x=>$y) {
			$dict1[$x] = $y;
			$i++;
			if ($i >= $howmany) { break; }
		}
		/*foreach ($dict1 as $x=>$y) {
			echo "<br>$x = $y";
		}*/
		return ($dict1);
	}
}

if (!function_exists('getTableFieldNames')) {	
	function getTableFieldNames($tableName) {
		//sql statement to get field names from the tableName
		global $conn;
		$sql = "SHOW COLUMNS FROM `" . $tableName . "`;";
		$result = $conn->query($sql);
		$i = -1;
		$fieldNames = array();
		$fieldNames_str = "";
		//going through field names...
		while ($row = mysqli_fetch_array($result)) {
			 //if ($row['Field'] != "id") {
			 $i ++;
			 $fieldNames[$i] = $row['Field'];			 
			 //}
		}
		return $fieldNames;
	}
}

if (!function_exists('createNeatPresentation')) {	
	function createNeatPresentation($tableName, $Id, $numOfUpdates, $systemDate) {
		global $conn;
		
		/*
		//Another way to present data using HTML Tables:
		//echo the field names row, i.e. the titles
		echo "<Table><TR>";
		$fieldNames = getTableFieldNames($tableName);
		for ($k=0; $k<sizeof($fieldNames); $k++) { echo "<TD>$fieldNames[$k]</TD>"; }
		echo "</TR>";
		
		//echo the field contents for this row of the table
		echo "<TR>";
		$sql = "SELECT * FROM $tableName WHERE id=$Id";
		if ($rs = mysqli_query($conn, $sql)) {
			while ($row = $rs -> fetch_row()) {
				for ($j=0; $j<sizeof($row); $j++) {		
					echo "<TD>$row[$j]</TD>";
					//<span>$fieldNames[$j] : $row[$j] </div> | ";
				}
			}			
		}
		echo "</TR>";
		
		echo "</TABLE>";
		*/


		$fieldNames = getTableFieldNames($tableName);
		$sql = "SELECT * FROM $tableName WHERE id=$Id";
		if ($rs = mysqli_query($conn, $sql)) {
			while ($row = $rs -> fetch_row()) {
				echo "<div class='nameInfo'>
						<a href=\"./structuredSet_db_editRow_showForm.php?tableName=$tableName&rowId=$Id\">Edit</a> 
						@ <b>$tableName</b>
						-- # of Updates: <b>$numOfUpdates</b>
						-- Last Update: <b>$systemDate</b>
						</div>";
				echo "<div class='wrapper'>";
				for ($j=0; $j<sizeof($row); $j++) {		
					echo "	<span class='field'>
								<p style='font-size:10px; background-color:#ADD8E6;'>
								<strong>
								<i>
								$fieldNames[$j] : 
								</p>
								</i>
								</strong>
							
							
								$row[$j] </span>";
				}
				echo "</div>";
			}
		}
		echo "</TR>";
		
		echo "</TABLE>";






		
	}
}

if (!function_exists('show_logs')) {	
	function show_logs($howmany) {
		global $conn;
		global $log_table;		
		$sqlQuery = "SELECT DISTINCT what_table, what_record FROM $log_table";
		$result = mysqli_query($conn, $sqlQuery);
		if ($result) {
			$num = mysqli_num_rows($result);
			if ($num<$howmany) {$howmany=$num; }
				
			echo "<div style='border: 1px solid orange; width:600px; background-color:#f6f6cb; border-radius:25px; padding; 50px;'>";
			echo "<span id='show_hide_button' style='font-size:10px; cursor:pointer; text-decoration: underline;' onClick='showHideLogs();'>Hide -</span>";
			echo "<h3>Log of the last $howmany Changes (Inserts & Updates)</h3>";
			  echo "<div id='logs'>";
				$rs_array_TablenameID_Count = fill_array($howmany);
				foreach ($rs_array_TablenameID_Count as $TablenameId=>$Count) {
					$Id = substr($TablenameId, strrpos($TablenameId, '_')+1);
					//echo "<br>$Id<br>";
					$indexOfId = strrpos($TablenameId,"_$Id"); //last occurence of _id eg. _11
					//echo "-- index: $indexOfId <br>";
					$Tablename = substr($TablenameId, 0, $indexOfId); //substring from pos 0 to length i.e. pos of _id
					//echo "<br>$Tablename<br>";
					
					//get the last date for this logged insert/update entry
					$sqlQuery = "SELECT id,system_date FROM $log_table WHERE `what_record`=\"id=$Id\" Order By id Desc LIMIT 0,1";			
					if ($rs=mysqli_query($conn, $sqlQuery)) {
					 while ($row = mysqli_fetch_array($rs)){
						$systemDate = $row["system_date"];
					 }
					}
					
					//echo "<h1>System Date: $systemDate</h1>";
					createNeatPresentation($Tablename, $Id, $Count, $systemDate);
				}
			  echo "</div>";	
			echo "</div>";
		} else { echo "No result from table"; }
	}
}
?>