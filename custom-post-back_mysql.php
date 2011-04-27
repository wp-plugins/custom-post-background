<?php
/*
	This file contains the installation functions
*/

$custpostback_db_version = "1.1";

function custompostback_install()
{
	global $wpdb;
	global $custpostback_db_version;
	
	$table_name = $wpdb->prefix . constant("custPostBack_dbtable");
	
	$installed_ver = get_option( "custpostback_db_version" );

	//check if it has not been installed or database needs update
	if ( ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
		|| ($installed_ver != $custpostback_db_version) )
	{
		// MJS - Default not allowed on TEXT fields - removed
		// MJS - dbDelta() needs a space in UNIQUE KEY def between keiId and ( 
		$sql = "CREATE TABLE ".$table_name." ( 
		id bigint(20) NOT NULL AUTO_INCREMENT,
		postid bigint(20) NULL,
		url text NULL,
		rep VARCHAR(5) NULL DEFAULT 'none',
		color VARCHAR(25) NULL,
		css TEXT NULL,
		displaytype TINYINT(1) NULL DEFAULT '3',
		UNIQUE KEY id (id)
		);";
		
		//excecute the query
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		update_option("custpostback_db_version", $custpostback_db_version);
	}
	
	$rowsPerPage = get_option('custBack_resultspp');
	//if the amount of rows per page is less than the minimum 10, then update it
	if($rowsPerPage < 10)
	{
		update_option('custBack_resultspp', 10);
	}
}


?>