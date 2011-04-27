<?php
/*
Plugin Name: Custom Post Background
Plugin URI: http://blogtap.net/custom_post_background_plugin.shtml
Description: Allow each post or page to have a custom background. Has the ability to edit the style of each post individually.
Version: 1.3.0.0
Author: David Sherret
Author URI: http://blogtap.net
*/
define("custPostBack_name", "Custom Post Background");
define("custPostBack_dbtable", "customBack");
/*  Copyright 2009  David Sherret (email : admin@davidsherret.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//install the plugin
require_once('custom-post-back_mysql.php');
register_activation_hook(__FILE__,'custompostback_install');
//get the includes needed
require_once('custom-post-back_admin.php');
require_once('custom-post-back_post.php');
?>