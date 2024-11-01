<?php
/* 
	Copyright 2008 www.starsrate.com

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

error_reporting (E_ALL ^ E_NOTICE);
ini_set ('display_errors', 0);
ini_set ('log_errors', 1);

if(!current_user_can('manage_ratings')) {
	die('Access Denied');
}

$base_name = plugin_basename('wp-starsratebox/wp-starsratebox-settings.php');
$base_page = 'admin.php?page=' . $base_name;

global $wpdb;

if (!empty($_POST) && $_POST['action'] == "update")
{
    $query = "UPDATE wp_starsrate_options SET rate_msg = '".$_POST['rate_msg']."', op_msg = '".$_POST['op_msg']."', op_thx_msg = '".$_POST['op_thx_msg']."', skin = '".$_POST['skin']."', start_pos = '".$_POST['start_pos']."', send_title = '".$_POST['send_title']."', end_pos = '".$_POST['end_pos']."'";
    $wpdb->query($query);
    
    echo "<div id=\"message\" class=\"updated fade\"><p><strong>Settings saved.</strong></p></div>";
}

$options = $wpdb->get_results("SELECT * FROM wp_starsrate_options");
$opt = $options[0];
$opt->rate_msg = preg_replace("/\"/", "&quot;", $opt->rate_msg);
$opt->op_msg = preg_replace("/\"/", "&quot;", $opt->op_msg);
$opt->op_thx_msg = preg_replace("/\"/", "&quot;", $opt->op_thx_msg);
$opt->send_title = preg_replace("/\"/", "&quot;", $opt->send_title);

?>

<div class="wrap">
<form method="post" action="<?php echo $base_page; ?>">
<h2><?php _e('Settings'); ?></h2>

<table class="form-table">
<tr valign="top">
<th scope="row"><label for="rate_msg">Rate Message</label></th>
<td><input name="rate_msg" type="text" id="rate_msg" value="<?php echo $opt->rate_msg; ?>" size="60" /></td>
</tr>

<tr valign="top">
<th scope="row"><label for="op_msg">Opinion Message</label></th>
<td><input name="op_msg" type="text" id="op_msg" value="<?php echo $opt->op_msg; ?>" size="60" />
</td>
</tr>

<tr valign="top">
<th scope="row"><label for="op_thx_msg">Opinion Thanks Message</label></th>

<td><input name="op_thx_msg" type="text" id="op_thx_msg" value="<?php echo $opt->op_thx_msg; ?>" size="60" /></td>
</tr>

<tr valign="top">
<th scope="row"><label for="send_title">Send Link Title</label></th>

<td><input name="send_title" type="text" id="send_title" value="<?php echo $opt->send_title; ?>" size="60" /></td>
</tr>

<tr valign="top">
<th scope="row"><label for="skin">Skin</label></th>
<td><select name="skin" id="skin" size="1" />
<option value="bubble"<?php if ($opt->skin=='bubble') echo " selected=\"selected\"";?>>Bubble</option>
<option value="classic"<?php if ($opt->skin=='classic') echo " selected=\"selected\"";?>>Classic</option>
<option value="default"<?php if ($opt->skin=='default') echo " selected=\"selected\"";?>>Default</option>
<option value="dotted"<?php if ($opt->skin=='dotted') echo " selected=\"selected\"";?>>Dotted</option>
<option value="motion"<?php if ($opt->skin=='motion') echo " selected=\"selected\"";?>>Motion</option>
<option value="stylish"<?php if ($opt->skin=='stylish') echo " selected=\"selected\"";?>>Stylish</option>
</select>
</td>
</tr>

<tr valign="top">
<th scope="row"><label for="start_pos">Stars Before Rating</label></th>
<td><select name="start_pos" id="start_pos" size="1" />
<option value="1"<?php if ($opt->start_pos==1) echo " selected=\"selected\"";?>>Highlight current rank</option>
<option value="2"<?php if ($opt->start_pos==2) echo " selected=\"selected\"";?>>Don't highlight current rank</option>
</select>
<br>
Set "Don't highlight current rank" if you don't want to suggest
</td>
</tr>

<tr valign="top">
<th scope="row"><label for="end_pos">Stars After Rating</label></th>
<td><select name="end_pos" id="end_pos" size="1" />
<option value="1"<?php if ($opt->end_pos==1) echo " selected=\"selected\"";?>>Highlight current rank</option>
<option value="2"<?php if ($opt->end_pos==2) echo " selected=\"selected\"";?>>Highlight selected value</option>
</select>
<br>
Set "Highlight selected value" if you don't want to show current rank
</td>
</tr>

</table>

<p class="submit"><input type="submit" name="Submit" value="Save Changes" />
<input type="hidden" name="action" value="update" />
</p>
</form>

</div>

