<?php
/*
Plugin Name: WP-StarsRateBox
Plugin URI: http://www.starsrate.com
Description: An AJAX Stars Rate Box with slide down effect and opinions collect system for your WordPress blog's page. To show off your StarsRateBox just put <code>&lt;?php starsratebox(); ?&gt;</code> in your template.
Version: 1.1
Author: www.starsrate.com
Author URI: http://www.starsrate.com
*/

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

### Load WP-Config File If This File Is Called Directly
if (!function_exists('add_action'))
{
	$wp_root = '../../..';
	if (file_exists($wp_root.'/wp-load.php')) {
		require_once($wp_root.'/wp-load.php');
	} else {
		require_once($wp_root.'/wp-config.php');
	}
}

### Use WordPress 2.6 Constants
if (!defined('WP_CONTENT_DIR')) {
	define( 'WP_CONTENT_DIR', ABSPATH.'wp-content');
}
if (!defined('WP_CONTENT_URL')) {
	define('WP_CONTENT_URL', get_option('siteurl').'/wp-content');
}
if (!defined('WP_PLUGIN_DIR')) {
	define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');
}
if (!defined('WP_PLUGIN_URL')) {
	define('WP_PLUGIN_URL', WP_CONTENT_URL.'/plugins');
}
if (!defined('WP_STARSRATEBOX_URL')) {
define("WP_STARSRATEBOX_URL", WP_PLUGIN_URL . '/wp-starsratebox');
}
if (!defined('WP_STARSRATEBOX_DIR')) {
define("WP_STARSRATEBOX_DIR", WP_PLUGIN_DIR . '/wp-starsratebox');
}

ini_set ('error_log', WP_STARSRATEBOX_DIR . "/log/" . date('Y-m-d') . '.err.log');

if (!isset($_COOKIE['StarsRateBoxSessionId']))
    setcookie("StarsRateBoxSessionId", md5(rand(1000, 9999)), time() + (60*60*24), COOKIEPATH);

add_action('admin_init', 'starsratebox_init');

function starsratebox_init() {

	if (function_exists('add_menu_page')) {
		add_menu_page(
        __('StarsRateBox', 'wp-starsratebox'), 
        __('StarsRateBox', 'wp-starsratebox'), 
        'manage_ratings', 
        'wp-starsratebox/wp-starsratebox-mgr.php'
        );
	}
	
    if (function_exists('add_submenu_page')) {
		add_submenu_page('wp-starsratebox/wp-starsratebox-mgr.php',
         __('Ratings', 'wp-starsratebox'), 
         __('Ratings', 'wp-starsratebox'), 
         'manage_ratings', 'wp-starsratebox/wp-starsratebox-mgr.php');
	}
    
    if (function_exists('add_submenu_page')) {
		add_submenu_page('wp-starsratebox/wp-starsratebox-mgr.php',
         __('Settings', 'wp-starsratebox'), 
         __('Settings', 'wp-starsratebox'), 
         'manage_ratings', 'wp-starsratebox/wp-starsratebox-settings.php');
	}
    
    if (function_exists('add_submenu_page')) {
		add_submenu_page('wp-starsratebox/wp-starsratebox-mgr.php',
         __('Uninstall', 'wp-starsratebox'), 
         __('Uninstall', 'wp-starsratebox'), 
         'manage_ratings', 'wp-starsratebox/wp-starsratebox-uninstall.php');
	}     
}

add_action('activate_wp-starsratebox/wp-starsratebox.php', 'create_starsratebox_tables');
function create_starsratebox_tables() {
	global $wpdb;
	if(@is_file(ABSPATH.'/wp-admin/upgrade-functions.php')) {
		include_once(ABSPATH.'/wp-admin/upgrade-functions.php');
	} elseif(@is_file(ABSPATH.'/wp-admin/includes/upgrade.php')) {
		include_once(ABSPATH.'/wp-admin/includes/upgrade.php');
	} else {
		die('We have problem finding your \'/wp-admin/upgrade-functions.php\' and \'/wp-admin/includes/upgrade.php\'');
	}
    
    $query = "CREATE TABLE `wp_starsrate_opinions` (
  `id` int(11) NOT NULL auto_increment,
  `id_page` int(11) NOT NULL,
  `value` text NOT NULL,
  `IP` varchar(32) NOT NULL,
  `addtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `id_page` (`id_page`,`IP`,`addtime`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
    maybe_create_table($wpdb->wp_starsrate_opinions, $query);

    
    $query = "CREATE TABLE `wp_starsrate_options` (
  `start_pos` tinyint(4) NOT NULL default '1',
  `end_pos` tinyint(4) NOT NULL default '1',
  `skin` varchar(16) NOT NULL default '1',
  `rate_msg` varchar(60) NOT NULL,
  `op_msg` varchar(255) NOT NULL,
  `op_thx_msg` varchar(255) NOT NULL,
  `send_title` varchar(20) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    maybe_create_table($wpdb->wp_starsrate_options, $query);
    
    $query = "INSERT INTO `wp_starsrate_options` (`start_pos`, `end_pos`, `skin`, `rate_msg`, `op_msg`, `op_thx_msg`, `send_title`) VALUES
(1, 1, 'default', 'Rate this', 'Share your opinion about this page with author: [click]', 'Thanks!', 'Click to Send');";
    $wpdb->query($query);
    
    $query = "CREATE TABLE `wp_starsrate_pages` (
  `id` int(11) NOT NULL auto_increment,
  `id_page` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL,
  `total_votes` int(11) unsigned NOT NULL default '0',
  `total_value` int(11) unsigned NOT NULL default '0',
  `rank` float NOT NULL default '0',
  `displays` bigint(20) NOT NULL default '0',
  `opinions` int(10) unsigned NOT NULL default '0',
  `addtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `displays` (`displays`),
  KEY `id_page` (`id_page`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
    maybe_create_table($wpdb->wp_starsrate_pages, $query);
    
    $query = "CREATE TABLE `wp_starsrate_votes` (
  `id` int(11) NOT NULL auto_increment,
  `id_page` int(11) NOT NULL,
  `value` smallint(6) NOT NULL,
  `IP` varchar(32) NOT NULL,
  `addtime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `session_id` varchar(40) default NULL,
  PRIMARY KEY  (`id`),
  KEY `id_page` (`id_page`,`IP`,`addtime`),
  KEY `session_id` (`session_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";
    maybe_create_table($wpdb->wp_starsrate_votes, $query);
}

define('STARWIDTH',20);
define('TOTALSTARS',5);

function srb_get_id_page($wpdb, $request)
{
    $url = parse_url($request['l']);
    
    if (preg_match("/page_id=(\d+)/", $url['query'], $match))
        $page_id = $match[1];
    else
        $page_id = 0;
       
    $idp = $wpdb->get_var("SELECT id FROM wp_starsrate_pages WHERE id_page = $page_id");
    
    if (!$idp)
    {
        $wpdb->get_var("INSERT INTO wp_starsrate_pages(id_page) values ($page_id)");
        $idp = $wpdb->get_var("SELECT id FROM wp_starsrate_pages WHERE id_page = $page_id");
    }
    
    return $idp;
}

function srb_check_vote($wpdb, $request)
{
    $idp = srb_get_id_page($wpdb, $request);
    $wpdb->query("UPDATE wp_starsrate_pages SET displays = displays + 1 WHERE id = $idp");
    $rank = $wpdb->get_var("SELECT rank FROM wp_starsrate_pages WHERE id = $idp");
    $start_pos = $wpdb->get_var("SELECT start_pos FROM wp_starsrate_options");
    
    if ($start_pos == 2)
        $rank = 0;
        
    $currentRating = @number_format($rank, 2) * STARWIDTH;
    
    $ratingString = $request['q'] .  '|<ul class="unit-rating">
<li class="current-rating" style="width:'.$currentRating.'px;">Currently '.$currentRating.'; ?>/ TOTALSTARS </li>';

    for ($ncount = 1; $ncount <= TOTALSTARS; $ncount++) { 
        $ratingString .= '<li><a href="javascript:void(0);" title="'.$ncount.'" class="r'.$ncount.'-unit" onclick="javascript:starsrate_sndVote(\'' . $request['p'] . '\', \''.$ncount.'\',\'' . $request['q'] . '\', \'' . $request['sk'] . '\')">'.$ncount.'</a></li>';
                
    }
    $ratingString .= "</ul>";       
     
    return $ratingString;
}

function srb_vote($wpdb, $request)
{
    $idp = srb_get_id_page($wpdb, $request);
    
    $voted = $wpdb->get_var("SELECT id FROM wp_starsrate_votes WHERE id_page = $idp AND session_id = '" . $_COOKIE['StarsRateBoxSessionId'] . "' LIMIT 1");
    if (!$voted)
    {
        $wpdb->query("INSERT INTO wp_starsrate_votes (id_page, value, IP, session_id) VALUES ($idp, {$request['j']}, '', '" . $_COOKIE['StarsRateBoxSessionId'] . "')");
        $wpdb->query("UPDATE wp_starsrate_pages SET total_votes = total_votes + 1, total_value = total_value + {$request['j']}, rank = total_value / total_votes WHERE id = $idp");
    }
    
    $rank = $wpdb->get_var("SELECT rank FROM wp_starsrate_pages WHERE id = $idp");
    
    $end_pos = $wpdb->get_var("SELECT end_pos FROM wp_starsrate_options");
    
    if ($end_pos == 2)
        $rank = $request['j'];
        
    $currentRating = @number_format($rank, 2) * STARWIDTH;
    
    $output = $_REQUEST['q']. "|<ul class=\"unit-rating\"><li class=\"current-rating\" style=\"width:{$currentRating}px;\">Currently $currentRating</li><li class=\"r1-unit\">1</li><li class=\"r2-unit\">2</li><li class=\"r3-unit\">3</li><li class=\"r4-unit\">4</li><li class=\"r5-unit\">5</li></ul>";
    return $output;
}

function srb_check_opinion($wpdb, $request)
{
    $options = $wpdb->get_results("SELECT * FROM wp_starsrate_options LIMIT 1");
    $op = $options[0];
    
    $output = $request['q'] .  '|<form>
    <textarea style="border-width:0; width:100%;" name="opinion" onclick="if (this.value==\'' . $op->op_msg . '\') this.value=\'\'">' . $op->op_msg . '</textarea>
                <div align="right">
                    <input type="image" src="'. WP_STARSRATEBOX_URL. '/img/send_en.gif" style="border: solid 0px; padding: 0px 2px 0px 0px;" onclick=" starsrate_sndOpinion(\'' . $request['p'] . '\', \''.$request['q'].'\', this.form.opinion.value); return false;" title="'.$op->send_title.'">
                </div>
            </form>';
                
    return $output;
}

function srb_opinion($wpdb, $request)
{
    $idp = srb_get_id_page($wpdb, $request);
    $wpdb->query("INSERT INTO wp_starsrate_opinions (id_page, value) VALUES ($idp, '".$request['opinion']."')");    
    $wpdb->query("UPDATE wp_starsrate_pages SET opinions = opinions + 1 WHERE id = $idp");
    
    $msg = $wpdb->get_var("SELECT op_thx_msg FROM wp_starsrate_options");
    
    $output = $request['q'] .  '|<div style="font-size: 12px; font-weight: bold; text-align: center;padding-top: 10px">';
     $output .= $msg;
    $output .= '</div>';
    
    return $output;
}

if (!empty($_REQUEST['check']))
    echo $res = srb_check_vote($wpdb, $_REQUEST);

elseif (!empty($_REQUEST['j']))
    echo $res = srb_vote($wpdb, $_REQUEST);

elseif (!empty($_REQUEST['opinion_check']))
    echo $res = srb_check_opinion($wpdb, $_REQUEST);
    
elseif (!empty($_REQUEST['opinion']))
    echo $res = srb_opinion($wpdb, $_REQUEST);
    
else
{

function starsratebox()
{
    global $wpdb;       
    
    $options = $wpdb->get_results("SELECT * FROM wp_starsrate_options LIMIT 1");
    $op = $options[0];
    
    ?>
    <script type="text/javascript" src="<?php echo WP_STARSRATEBOX_URL; ?>/starsrate.js"></script>
    <link rel="stylesheet" type="text/css" href="<?php echo WP_STARSRATEBOX_URL; ?>/skins/<?php echo $op->skin; ?>/starsrate.css" />

<div class="STARSRATE" style="width: 160px; height: 22px;">

    <div id="STARSRATE" class="<?php echo $op->skin; ?>" onmouseover="srb_mouse_over(this, event, 'RATEPAGE_OP');" 
onmouseout="srb_mouse_out(this, event, 'RATEPAGE_OP')">
        <table class="starsratebox">
        <tr>
            <td><a href="http://www.starsrate.com"><?php echo $op->rate_msg; ?></a></td>
            <td>
            
                <div id="RATEPAGE">
                <script type="text/javascript">
                <!--
                starsrate_sndCheckResult('<?php echo WP_STARSRATEBOX_URL; ?>/wp-starsratebox.php', 'RATEPAGE', '<?php echo $op->skin; ?>');
                //-->
                </script>
                </div>
            </td>
        </tr>
        </table>
        
        <div id="RATEPAGE_OP" style="display:none; overflow:hidden; width: 158px; height: 60px;">
        </div>
    
        <script type="text/javascript">
        <!--
        starsrate_sndCheckOpinion('<?php echo WP_STARSRATEBOX_URL; ?>/wp-starsratebox.php', 'RATEPAGE_OP', '<?php echo $op->skin; ?>');
        //-->
        </script>
        
    </div>
    
</div>
    
<?php
    }  
}
?>