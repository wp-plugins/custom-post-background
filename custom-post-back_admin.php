<?php
/*
	This file contains all the admin data needed to make the admin page
*/

//create a function to get the posts and display the information

add_action('admin_menu', 'custPostBack_plugin_menu');
function custPostBack_plugin_menu()
{
	add_options_page('Settings', constant('custPostBack_name'), 8, __FILE__, 'custPostBack_options');
}

/* custPostBack_createPostItems(): This function gets all the posts on the wordpress blog and creates a corresponding item in */
function custPostBack_createPostItems()
{
	global $wpdb;
	$table_post = $wpdb->posts;
	$table_back = $wpdb->prefix . constant("custPostBack_dbtable");	
	$selectPosts = "SELECT id, post_title FROM $table_post WHERE post_status='publish'";
	$posts = $wpdb->get_results($selectPosts);
	foreach($posts as $post)
	{
		$selectBacks = "SELECT id FROM $table_back WHERE postid=".$post->id;
		$backs = $wpdb->get_results($selectBacks);
		//if the post does not exist
		if(!$backs)
		{
			$createBack = "INSERT INTO ".$table_back." VALUES(NULL, '".$post->id."',NULL,'none',NULL,NULL,'3')";
			$result = $wpdb->query($createBack);
		}
	}
}

function admin_menu_parent() {
	global $wp_version;
	if (version_compare($wp_version, '2.7', '>='))
		return 'options-general.php';
	else
		return 'edit.php';
}

function getPageURL() {
	if (function_exists('admin_url')) {
		$base_url = admin_url(admin_menu_parent());
	} else {
		$base_url = get_option('siteurl') . '/wp-admin/' . admin_menu_parent();
	}
	$page = plugin_basename(__FILE__);
	return $base_url.'?page=' . $page;
}

//here is the page that contains the options that are going to be used for the plugin
function custPostBack_options()
{
	global $wpdb;
	$case = 0;
	//get the table name
	$table_name = $wpdb->prefix . constant("custPostBack_dbtable");	
	//check to see if it needs to be edited
	if($_POST['custBack_hidden_edit'] == 'Y')
	{
		//then it is a postback, now update the data
		$update = "UPDATE ".$table_name." SET url='".$_POST['custBack_url_edit']."',
		rep='".$_POST['custBack_repeat_edit']."', color='".$_POST['custBack_color_edit']."', css='".$_POST['custBack_css_edit']."',
		displaytype = '".$_POST['custBack_displaytype_edit']."'
		WHERE id='".$_POST['custBack_id_edit']."'";
		$wpdb->query($update);
		$case = 1;
	}
	
	//check to see if a drop down item was selected
	if(isset($_POST['custBack_resultspp']) && strlen($_POST['custBack_resultspp']) > 0)
	{
		update_option('custBack_resultspp', $_POST['custBack_resultspp']);
	}
	
	//make sure the database is up-to-date and make sure all the backgrounds are created for each post
	custPostBack_createPostItems();
	
	//code to display data
	echo '<div class="wrap">';
	echo '<h2>'.constant('custPostBack_name').'</h2>';
	echo '<p />';
	
	//the links section
	echo '<p><a href="http://blogtap.net/software.shtml" target="_blank">More Software</a> | <a href="http://blogtap.net/custom_post_background_plugin.shtml" target="_blank">Donate</a> | <a href="http://blogtap.net/custom_post_background_plugin.shtml" target="_blank">Information</a> | <a href="http://blogtap.net/contact.shtml" target="_blank">Contact Us</a></p>';
	
	//do all of the paging information:
	$rowsPerPage = get_option('custBack_resultspp');

	//get the pagenumber
	$pageNum = htmlspecialchars($_GET['pg']);
	if(!isset($_GET['pg']))
	{
		$pageNum = 1;
	}
	
	$offset = ($pageNum - 1) * $rowsPerPage;
	
	$queryBacks = "SELECT * FROM ".$table_name." ORDER BY id DESC LIMIT $offset, $rowsPerPage";
	$results = $wpdb->get_results($queryBacks);
	
	if($case == 1)
	{
		echo '<p style="color: blue;">The background has been updated.</p>';
	}
	
	//Get the results and display them
	if($results)
	{
		$table_post = $wpdb->posts;
		echo '<table style="width:75%;" cellspacing="0">';
		echo '<tr>
		<td colspan="6" align="right"><form action="'.getPageURL().'" method="POST" id="custompagebackground_pageresults" enctype="multipart/form-data">
		Results Per Page:<select name="custBack_resultspp" onchange="document.forms[\'custompagebackground_pageresults\'].submit();">';
		//set up the drop down box so it displays everything properly
		if($rowsPerPage == 10 || strlen($rowsPerPage) <= 0) echo '<option selected>10</option>';
		else echo '<option>10</option>';
		if($rowsPerPage == 25) echo '<option selected>25</option>';
		else echo '<option>25</option>';
		if($rowsPerPage == 50) echo '<option selected>50</option>';
		else echo '<option>50</option>';
		if($rowsPerPage == 75) echo '<option selected>75</option>';
		else echo '<option>75</option>';
		if($rowsPerPage == 100) echo '<option selected>100</option>';
		else echo '<option>100</option>';
		
		echo '</select>
		</form></td>
		</tr>';
		echo '<tr><td><b>Name</b></td><td><b>URL</b></td><td><b>Repeat</b></td><td><b>Color</b></td><td><b>Display</b></td><td></td>';
		
		$alt = 0;
		foreach($results as $back)
		{
			$queryPosts = "SELECT id, post_title FROM $table_post WHERE id='".$back->postid."'";
			
			$resultPosts = $wpdb->get_row($queryPosts);
			
			//if a post doesn't show up then you should delete the link to it in the custBack table
			if(!$resultPosts)
			{
				$deleteBack = "DELETE FROM $table_name WHERE id='".$back->id."' LIMIT 1";
				$wpdb->query($deleteBack);
				echo 'done';
				continue; //don't go any further, but continue processing the other items
			}
			
			//rotates between the two different rows
			$alt++;
			if($alt % 2 == 1)
			{
				echo '<tr>';
			}
			else
			{
				echo '<tr style="background-color: #eee;">';
			}
			echo '<td>'.$resultPosts->post_title.'</td>';
			echo '<td>';
			if(strlen($back->url) > 0)
			{
				echo substr(str_replace("http://","",$back->url),0,20).'...';
			}
			echo '</td>';
			if($back->rep == 'y' || $back->rep == 'x') echo '<td>repeat-'.$back->rep.'</td>';
			else echo '<td>none</td>';
			echo '<td>'.$back->color.'</td>';
			
			if($back->displaytype == "0") echo '<td>Post Page</td>';
			else if($back->displaytype == "1") echo '<td>Main Page</td>';
			else if($back->displaytype == "2") echo '<td>Both</td>';
			else if($back->displaytype == "3") echo '<td>Post Page Background</td>';
			else if($back->displaytype == "4") echo '<td>Disabled</td>';
			else echo '<td></td>';
			
			echo '<td><a href="'.getPageURL().'&edit='.$back->id.'">Edit</a></td>';
			echo '</tr>';
		}
		echo '<tr><td colspan="6" align="center" style="padding-top: 10px">';
		
		//now echo the pages
		$queryCount = "SELECT COUNT(id) FROM ".$table_name;
		$numRows = $wpdb->get_var($queryCount);
		$maxPage = ceil($numRows/$rowsPerPage);
		$nav = "";
		if($pageNum > 1)
		{
			$prevPage = $pageNum - 1;
			echo '&laquo;<a href="'.getPageUrl().'&pg='.$prevPage.'">Previous</a> ';
		}
		//if there is only one possible page, then don't display anything... otherwise display something
		if($maxPage > 1)
		{
			for($i = 1; $i <= $maxPage; $i++)
			{
				if($i == $pageNum)
				{
					echo $i." ";
				}
				else
				{
					echo '<a href="'.getPageURL().'&pg='.$i.'">'.$i.'</a> ';
				}
			}
		}
		if ($pageNum < $maxPage)
		{
			$nextPage = $pageNum + 1;
			echo '<a href="'.getPageURL().'&pg='.$nextPage.'">Next</a>&raquo;';
		}
		echo '</td></tr>';
		echo '</table>';
	}
	else
	{
		echo 'There are currently no backgrounds. Please add one below.<br />';
	}
	
	//check to see if the edit background is set
	if(isset($_GET['edit']))
	{
		$queryEditOne = "SELECT * FROM ".$table_name." WHERE id='".$_GET['edit']."' LIMIT 1";
		$rEditBack = $wpdb->get_row($queryEditOne);
		if($rEditBack)
		{
			$queryPosts = "SELECT id, post_title FROM $table_post WHERE id='".$rEditBack->postid."'";
			$resultPosts = $wpdb->get_row($queryPosts);
			
			//if a post doesn't show up then you should delete the link to it in the custBack table
			if(!$resultPosts)
			{
				$deleteBack = "DELETE FROM $table_name WHERE id='".$rEditBack->id."' LIMIT 1";
				$wpdb->query($deleteBack);
				continue; //don't go any further, but continue processing the other items
			}
			
			echo '<h3>Edit Background: '.$resultPosts->post_title.'</h3>';

			echo '<form action="'.getPageURL().'" method="POST" id="custompagebackground_edit" enctype="multipart/form-data">
			<input type="hidden" name="custBack_hidden_edit" value="Y" />
			<input type="hidden" name="custBack_id_edit" value="'.$rEditBack->id.'" />
			<table>
			<tr>
			<td style="text-align: right; vertical-align: middle;">
			Name:</td><td><input type="text" name="custBack_name_edit" readonly value="'.$resultPosts->post_title.'" size="40" /></td>
			</tr>
			<tr>
			<td style="text-align: right; vertical-align: middle;">Image Url:</td><td><input type="text" name="custBack_url_edit"
			value="'.$rEditBack->url.'" size="40" /></td>
			</tr>
			<tr>
			<td style="text-align: right; vertical-align: middle;">Repeat:</td>
			<td><select name="custBack_repeat_edit">';
			//area for selecting repeat type
			if($rEditBack->rep == "none") echo '<option value="none" selected>None</option>';
			else echo '<option value="none">None</option>';
			if ($rEditBack->rep == "x") echo '<option value="x" selected>Repeat X</option>';
			else echo '<option value="x">Repeat X</option>';
			if ($rEditBack->rep == "y") echo '<option value="y" selected>Repeat Y</option>';
			else echo '<option value="y">Repeat Y</option>';
			if ($rEditBack->rep == "both") echo '<option value="both" selected>Both</option>';
			else echo '<option value="both">Both</option>';
			echo '</select></td>
			</tr>
			<tr>
			<td style="text-align: right; vertical-align: middle;">Color:</td><td><input type="text" name="custBack_color_edit" value="'.$rEditBack->color.'" size="40" /></td>
			</tr>
			<tr>
			<td style="text-align: right; vertical-align: middle;">CSS:</td><td><textarea name="custBack_css_edit" rows="4" cols="40">'.$rEditBack->css.'</textarea></td>
			</tr>
			<tr>
			<td style="text-align: right; vertical-align: middle;">Display Type</td>
			<td>';
			if($rEditBack->displaytype == "0") echo '<input type="radio" name="custBack_displaytype_edit" value="0" checked /> Display only on Post Page.<br />';
			else echo '<input type="radio" name="custBack_displaytype_edit" value="0" /> Display only on Post Page.<br />';
			if($rEditBack->displaytype == "1") echo '<input type="radio" name="custBack_displaytype_edit" value="1" checked /> Display only on Main/Archives Page.<br />';
			else echo '<input type="radio" name="custBack_displaytype_edit" value="1" /> Display only on Main/Archives Page.<br />';
			if($rEditBack->displaytype == "2") echo '<input type="radio" name="custBack_displaytype_edit" value="2" checked /> Display on both pages.<br />';
			else echo '<input type="radio" name="custBack_displaytype_edit" value="2" /> Display on both pages.<br />';
			if($rEditBack->displaytype == "3") echo '<input type="radio" name="custBack_displaytype_edit" value="3" checked /> Display as page background on post page.<br />';
			else echo '<input type="radio" name="custBack_displaytype_edit" value="3" /> Display as page background on post page.<br />';
			if($rEditBack->displaytype == "4") echo '<input type="radio" name="custBack_displaytype_edit" value="4" checked /> Disable - Do Not display.';
			else echo '<input type="radio" name="custBack_displaytype_edit" value="4" /> Disable - Do Not display.';
			
			echo '</td>
			</tr>
			<tr>
			<td style="text-align: right; vertical-align: top; padding-top: 5px;" colspan="2"><a href="'.getPageURL().'">Cancel</a> - <span class="submit"><input type="submit" class="button-primary" value="Edit Background" /></span></td>
			</tr>
			</table>
			</form>
			';
		}
	}
	
	echo '</div>';
}

?>