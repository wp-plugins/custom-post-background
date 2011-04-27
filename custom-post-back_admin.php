<?php
/*
	This file contains all the admin data needed to make the admin page
*/

// Updated: 14/04/2011 - Malcolm Shergold - Admin screen now uses meta-box interface

// WP 3.0+
add_action('admin_menu', 'postback_add_custom_box'); 
add_action('save_post', 'save_custom_data', 1, 2);
add_action('delete_post', 'delete_custom_data' );

function save_custom_data($post_id, $post) {
	global $wpdb;
	
  if (!$post_id) $post_id = $_POST['post_ID'];
  if (!$post_id) return $post;
	
  // verify this came from the our screen and with proper authorization,
  // because save_post can be triggered at other times
  // MJS - Added !isset($_POST['postback_noncename'])
  if (!isset($_POST['postback_noncename']) || !wp_verify_nonce( $_POST['postback_noncename'], plugin_basename(__FILE__) ) )
  {			
      return $post;
	}
	
	//check to see if it needs to be edited
	if (isset($_POST['custBack_img_source']))
	{
		//then it is a postback, check what the Background Source is set as
		if($_POST['custBack_img_source'] == 0) //image page
		{
			$url = $_POST['custBack_img_Database'];
		}
		else if($_POST['custBack_img_source'] == 1) //from url
		{
			$url = $_POST['custBack_url_edit'];
		}
		else //it's disabled
		{
			$url = "";
		}
		
		$table_name = $wpdb->prefix . constant("custPostBack_dbtable");			
		if ($_POST['custBack_id_edit'] != 0)
		{
			//now update the data
			$update = "UPDATE ".$table_name." SET url='".$url."',
				rep='".$_POST['custBack_repeat_edit']."', color='".$_POST['custBack_color_edit']."', css='".$_POST['custBack_css_edit']."',
				displaytype = '".$_POST['custBack_displaytype_edit']."'
				WHERE id='".$_POST['custBack_id_edit']."'";
		}
		else
		{
			//now add the data
			$update  = "INSERT INTO ".$table_name." (postid,url,rep,color,css,displaytype)"; 
			$update .= " VALUES('".$post_id."', '".$url."', '".$_POST['custBack_repeat_edit']."', '".$_POST['custBack_color_edit']."', '".$_POST['custBack_css_edit']."', '".$_POST['custBack_displaytype_edit']."')";
		}
		

		$wpdb->query($update);
		
		$msg  = "Save Background Settings SQL:\n";
		$msg .= $update . "\n";
	}	
}

function delete_custom_data($post_id) {
	global $wpdb;
	
	$table_name = $wpdb->prefix . constant("custPostBack_dbtable");			
	
	// delete the link to the post in the custBack table
	$deleteBack = "DELETE FROM $table_name WHERE postid='".$post_id."' LIMIT 1";
	$wpdb->query($deleteBack);
}

function postback_add_custom_box() { 
	if( function_exists( 'add_meta_box' )) {		
		add_meta_box( 'custPostBack', __( 'Page Background' ), 'custPostBack_metabox', 'post', 'normal', 'core' );
		add_meta_box( 'custPostBack', __( 'Page Background' ), 'custPostBack_metabox', 'page', 'normal', 'core' );
	} 
}

//here is the page that contains the options that are going to be used for the plugin
function custPostBack_metabox($post, $custPostBack_callbackargs)
{
	global $wpdb;
	global $post_id;
	
	//get the table names
	$table_name = $wpdb->prefix . constant("custPostBack_dbtable");	
	$table_post = $wpdb->posts;

	//code to display data
	echo '<div class="wrap">';
	
	//the links section
	echo '<p><a href="http://blogtap.net/software.shtml" target="_blank">More Software</a> | <a href="http://blogtap.net/custom_post_background_plugin.shtml" target="_blank">Donate</a> | <a href="http://blogtap.net/custom_post_background_plugin.shtml" target="_blank">Information</a> | <a href="http://blogtap.net/contact.shtml" target="_blank">Contact Us</a></p>';
	
	//check to see if this post has a background entry in the DB
	$queryEditOne = "SELECT * FROM ".$table_name." WHERE postid='".$post_id."' LIMIT 1";
	
	$rEditBack = $wpdb->get_row($queryEditOne);
	
	if(!$rEditBack)
	{
		// Set-up defaults
		$rEditBack->id = 0;	// 0 = No entry for this Post
		$rEditBack->postid = $post_id;
		$rEditBack->url = '';
		$rEditBack->rep = "none";
		$rEditBack->color = '';
		$rEditBack->css = '';
		$rEditBack->displaytype = "0";
	}
		
	//figure out how the background source will be displayed
	$linkId = "";
	if(strlen($rEditBack->url) > 0)
	{
		//check if the posts table contains the link to the image
		$linkId = $wpdb->get_var("SELECT id FROM ".$wpdb->posts." WHERE guid='".$rEditBack->url."' LIMIT 1");
	}
	$resultImages = $wpdb->get_results("SELECT id, guid, post_title, post_name FROM ".$wpdb->posts." WHERE post_mime_type LIKE 'image%'");

	// Use nonce for verification
	wp_nonce_field( plugin_basename(__FILE__), 'postback_noncename' );

	//echo '<h3>Edit Background: '.$resultPosts->post_title.'</h3>';

	echo '
		<input type="hidden" name="custBack_id_edit" value="'.$rEditBack->id.'" />
			
		<div id="postcustomstuff">
		<p>
		<table>
			<tr>
				<td width="25%" style="text-align: right; vertical-align: middle;">'.__('Source').':</td>
				<td width="75%" >
				<select name="custBack_img_source" id="custBack_img_source"
				onchange="
					if(this.options[this.selectedIndex].value == 0)
					{
						document.getElementById(\'rowMedia\').style.visibility = \'visible\';
						document.getElementById(\'rowMedia\').style.display = \'\';
						document.getElementById(\'rowUrl\').style.visibility = \'hidden\';
						document.getElementById(\'rowUrl\').style.display = \'none\';
					}
					else if(this.options[this.selectedIndex].value == 1)
					{
						document.getElementById(\'rowMedia\').style.visibility = \'hidden\';
						document.getElementById(\'rowMedia\').style.display = \'none\';
						document.getElementById(\'rowUrl\').style.visibility = \'visible\';
						document.getElementById(\'rowUrl\').style.display = \'\';
					}
					else if(this.options[this.selectedIndex].value == 2)
					{
						document.getElementById(\'rowMedia\').style.visibility = \'hidden\';
						document.getElementById(\'rowMedia\').style.display = \'none\';
						document.getElementById(\'rowUrl\').style.visibility = \'hidden\';
						document.getElementById(\'rowUrl\').style.display = \'none\';
					}
				">';
					
	//make sure the properly selected option is selected
	$isMediaImage = ($linkId != "");
	$isImageURL = (($linkId == "") && (strlen($rEditBack->url) > 0));
	
	if (count($resultImages) >= 1)				
		echo '<option value="0" '.($isMediaImage ? 'selected' : '').'>'.__('Image From Media').' &nbsp</option>';			
	echo '<option value="1" '.($isImageURL ? 'selected' : '').'>'.__('Image From URL').'</option>';
	echo '<option value="2" '.((!$isMediaImage && !$isImageURL) ? 'selected' : '').'>'.__('Disable').'</option>';					
	echo '
				</select>
				</td>
			</tr>
			';
			
	echo '<tr id="rowMedia" style="visibility:'.($isMediaImage ? 'visible;' : 'hidden; display:none;').'">
				<td style="text-align: right; vertical-align: middle;">Image From Media:</td>
				<td><select name="custBack_img_Database">';
	foreach($resultImages as $images)
	{
		//display the file
		echo '<option value="'.$images->guid.'" '.($images->id == $linkId ? 'selected' : '').'>'.$images->post_title.' ('.$images->post_name.')</option>';
	}
	echo '</select></td>
			</tr>
			';
			
	echo '<tr id="rowUrl" style="visibility:'.($isImageURL ? 'visible;' : 'hidden; display:none;').'">
				<td style="text-align: right; vertical-align: middle;">URL:</td>
				<td><input type="text" name="custBack_url_edit"
				value="';
				
	if($linkId == "") echo $rEditBack->url; //if the link is not in the posts table, then output the url here
				
	echo '" size="40" class="widefat" />
				</td>
			</tr>
			<tr>
				<td style="text-align: right; vertical-align: middle;">'.__('Repeat').':</td>
				<td><select name="custBack_repeat_edit">';
			
	//area for selecting repeat type
	echo '<option value="none" '.($rEditBack->rep == "none" ? 'selected' : '').'>'.__('None').'</option>';			
	echo '<option value="x" '.($rEditBack->rep == "x" ? 'selected' : '').'>'.__('Repeat X').'</option>';
	echo '<option value="y" '.($rEditBack->rep == "y" ? 'selected' : '').'>'.__('Repeat Y').'</option>';
	echo '<option value="both" '.($rEditBack->rep == "both" ? 'selected' : '').'>'.__('Both').'</option>';
	echo '</select></td>
			</tr>
			
			<tr>
				<td style="text-align: right; vertical-align: middle;">'.__('Background Color').':</td><td><input type="text" name="custBack_color_edit" value="'.$rEditBack->color.'" size="40" /></td>
			</tr>
			<tr>
				<td style="text-align: right; vertical-align: middle;">CSS:</td><td><textarea name="custBack_css_edit" rows="4" cols="40">'.$rEditBack->css.'</textarea></td>
			</tr>
			<tr>
				<td style="text-align: right; vertical-align: middle;">'.__('Display Type').':</td>
			<td>';
			
	//area for selecting display type
	echo '
			<select name="custBack_displaytype_edit">
				<option value="0" '.($rEditBack->displaytype == "0" ? 'selected' : '').'>'.__('Only on Post Page').'</option>
				<option value="1" '.($rEditBack->displaytype == "1" ? 'selected' : '').'>'.__('Only on Main/Archives Page').'</option>
				<option value="2" '.($rEditBack->displaytype == "2" ? 'selected' : '').'>'.__('On both pages').'</option>
				<option value="3" '.($rEditBack->displaytype == "3" ? 'selected' : '').'>'.__('Post Page background').'</option>
				<option value="4" '.($rEditBack->displaytype == "4" ? 'selected' : '').'>'.__('Disable - Do Not display').'</option>
			</select>
			</td>

			</tr>
		</table>
		</p>
		</div>			
			';
	
	echo '</div>';	
}


?>