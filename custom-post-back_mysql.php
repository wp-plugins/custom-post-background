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
	
	//check if it has been installed
	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
	{
		$sql = "CREATE TABLE ".$table_name." ( 
		id bigint(20) NOT NULL AUTO_INCREMENT,
		postid bigint(20) NULL,
		url text NULL,
		rep VARCHAR(5) NULL DEFAULT 'none',
		color VARCHAR(25) NULL,
		css TEXT NULL DEFAULT '',
		displaytype TINYINT(1) NULL DEFAULT '3',
		UNIQUE KEY id(id)
		);";
		$wpdb->query($sql);
		
		//excecute the query
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
		add_option("custpostback_db_version", $custpostback_db_version);
	}
	//update query - uncomment the code upon upgrade to newer version
/*
	$installed_ver = get_option( "custpostback_db_version" );

	if( $installed_ver != $custpostback_db_version) {

	  $sql = "CREATE TABLE ".$table_name." ( 
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		name text NOT NULL,
		url text NULL,
		repeat VARCHAR(5) NULL DEFAULT 'none',
		color VARCHAR(25) NULL DEFAULT '',
		css TEXT NULL DEFAULT '',
		UNIQUE KEY id(id)
		);";

	  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	  dbDelta($sql);

	  update_option( "custpostback_db_version", $custpostback_db_version);
	}*/

}


?>