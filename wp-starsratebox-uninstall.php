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

$base_name = plugin_basename('wp-starsratebox/wp-starsratebox-uninstall.php');
$base_page = 'admin.php?page=' . $base_name;

global $wpdb;

if (!empty($_POST['uninstall']))
{
$wpdb->query("DROP TABLE wp_starsrate_opinions");
$wpdb->query("DROP TABLE wp_starsrate_options");
$wpdb->query("DROP TABLE wp_starsrate_pages");
$wpdb->query("DROP TABLE wp_starsrate_votes");

$deactivate_url = 'plugins.php?action=deactivate&amp;plugin=wp-starsratebox/wp-starsratebox.php';
if(function_exists('wp_nonce_url')) { 
$deactivate_url = wp_nonce_url($deactivate_url, 'deactivate-plugin_wp-starsratebox/wp-starsratebox.php');
}

?>
<div class="wrap">
<h2><?php _e('Uninstall WP-StarsRateBox', 'wp-starsratebox'); ?></h2>
<div id="message" class="updated fade">
<p><strong>Done.</strong> Don't forget to remove <code>&lt;?php starsratebox(); ?&gt;</code> from your template.</p>
</div>

<br>

Here you can <a href="<?php echo $deactivate_url ?>">Deactivate this plugin automatically</a>
</div>

<?php
}
else
{
?>
<form action="<?php echo $base_page; ?>" method="post">
<div class="wrap">
	<h2><?php _e('Uninstall WP-StarsRateBox', 'wp-starsratebox'); ?></h2>
	<p style="text-align: left;">
		<?php _e('Deactivating WP-StarsRateBox plugin does not remove any data that may have been created. To completely remove this plugin, you can uninstall it here.', 'wp-starsratebox'); ?>
	</p>
	<p style="text-align: left; color: red">
		<strong><?php _e('WARNING:', 'wp-starsratebox'); ?></strong><br />
		<?php _e('Once uninstalled, this cannot be undone. You should use a Database Backup plugin of WordPress to back up all the data first.', 'wp-starsratebox'); ?>
	</p>
	<p>&nbsp;</p>
	<p style="text-align: center;">
		<input type="submit" name="uninstall" value="<?php _e('UNINSTALL', 'wp-starsratebox'); ?>" class="button" />
	</p>
</div>
</form>

<?php
}
?>
