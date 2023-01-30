############################
Logging Module For PHP Database Applications
This Module enables logging of Inserts/Updates in Database and
it makes it possible to read the logged data by issuing a "show_log" command.
#############################

Follow the 5 steps below to enable the Module in your application which works as a helper to log all insert/update SQL queries:


1. Create the Logging Table in your database by running the following SQL Query:

 	CREATE TABLE `log_table` (
	 `id` int(11) NOT NULL AUTO_INCREMENT,
	 `system_date` text COLLATE utf8_bin,
	 `date` text COLLATE utf8_bin,
	 `what_table` text COLLATE utf8_bin,
	 `what_record` text COLLATE utf8_bin,
	 `describe_action` text COLLATE utf8_bin,
	 `note` text COLLATE utf8_bin,
	 `IP_or_general_location_description` text COLLATE utf8_bin,
	 `username` text COLLATE utf8_bin,
	 `system_user_is_using` text COLLATE utf8_bin,
	 PRIMARY KEY (`id`)
	) ENGINE=MyISAM AUTO_INCREMENT=160 DEFAULT CHARSET=utf8 COLLATE=utf8_bin



2. Add the folder "module-logging" to the root path of your application.

3. Insert these 5 lines of code in your main PHP Script to make logging possible:

	$log_table = "log_table";
	
	$show_how_many_log_entries=10;
	
	date_default_timezone_set('Europe/Stockholm');
	

	include ("./module-logging/logging.php");
	
	include ("./module-logging/logging-front-end.php");
	

4. Whereever there is an SQL Execution code in your application add the statement:

	log_this_change_to_database($sqlStatement);

5. In order to see an interface to the logged data, add the below two statements:

	global $show_how_many_log_entries;
	show_logs($show_how_many_log_entries);


Screenshot of the module showing last 5 operations:

![Screenshot- Data Logging Module](https://user-images.githubusercontent.com/42844572/215530089-f4f805aa-73b4-4c93-a833-161dbe184880.png)
