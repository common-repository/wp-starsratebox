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

$base_name = plugin_basename('wp-starsratebox/wp-starsratebox-mgr.php');
$base_page = 'admin.php?page=' . $base_name;

global $wpdb;
?>

<div class="wrap">
<form id="posts-filter" action="" method="get">

<?php
if (isset($_GET['pid']) && $_GET['act'] != 'page_del')
{
    if (!empty($_GET['act']) && $_GET['act'] == 'op_del' && !empty($_GET['oid']))
    {
        $wpdb->query("DELETE FROM wp_starsrate_opinions WHERE id = " . $_GET['oid']);
        $wpdb->query("UPDATE wp_starsrate_pages SET opinions = opinions - 1 WHERE id = " . $_GET['pid']);
    }
        
    $page_title = $wpdb->get_var("SELECT post_title FROM wp_starsrate_pages as a left join wp_posts as b ON a.id_page = b.ID AND b.post_type='page' WHERE a.id = ".$_GET['pid']);

    if (empty($page_title))
        $page_title = "Main Page";
    
    $opinions = $wpdb->get_results("SELECT a.* FROM wp_starsrate_opinions as a left join wp_starsrate_pages as b on a.id_page = b.id where b.id = ".$_GET['pid'] . " ORDER BY addtime desc");

?>
    <h2><?php _e('Opinions about'); echo " \"$page_title\""; ?></h2>
    <table class="widefat">
<thead>
  <tr>
    <th scope="col"><?php _e('Opinion') ?></th>
    <th scope="col"><?php _e('Date') ?></th>
    <th scope="col"><?php _e('Actions') ?></th>
  </tr>
</thead>
<tbody id="the-comment-list" class="list:comment">

<?php
if ($opinions)
{
$i = 0;
foreach($opinions as $op)
        {
if($i%2 == 0) {
	$style = 'class="alternate"';
}  else {
	$style = '';
}
echo "<tr $style>\n";
            echo "<td>" . $op->value . "</td>\n";
            echo "<td>" . $op->addtime . "</td>\n";
            echo "<td><a href=\"$base_page&pid=".$_GET['pid']."&oid=" .$op->id."&act=op_del\" onClick=\"javascript:return confirm('Are you sure?');\">Delete</a> </td>\n";
            echo "</tr>";
            $i++;
     }
}
else
{
    echo "<tr><td>No results</td></tr>";
}
?>
</tbody>
</table>
</form>
</div>
<br>
<div align="right" style="padding-right:20px">
<a href="<?php echo $base_page; ?>">Back</a>
</div>
<?php
    
}
else
{

if (isset($_GET['pid']) && !empty($_GET['act']) && $_GET['act'] == 'page_del')
{
    $wpdb->query("DELETE FROM wp_starsrate_opinions WHERE id_page = " . $_GET['pid']);
    $wpdb->query("DELETE FROM wp_starsrate_votes WHERE id_page = " . $_GET['pid']);
    $wpdb->query("DELETE FROM wp_starsrate_pages WHERE id = " . $_GET['pid']);
}

if (!empty($_GET['sort']) && in_array($_GET['sort'], array('page', 'displays', 'votes', 'rank', 'opinions')))
{
    if ($_GET['sort'] == 'page')
        $sort = 'id asc';
        
    elseif($_GET['sort'] == 'votes')
        $sort = 'total_votes desc';
        
    else
        $sort = $_GET['sort']. " desc";
    
    $pages = $wpdb->get_results("SELECT post_title, a.* FROM wp_starsrate_pages as a left join wp_posts as b ON a.id_page = b.ID AND b.post_type='page' ORDER by a." . $sort . ", a.displays desc");
}
else
{
    $pages = $wpdb->get_results("SELECT post_title, a.* FROM wp_starsrate_pages as a left join wp_posts as b ON a.id_page = b.ID AND b.post_type='page' ORDER by a.rank desc, a.displays desc");
}

if (!$pages)
{
    echo "<div id=\"message\" class=\"updated fade\"><p>To show off your StarsRateBox just put <strong><code>&lt;?php starsratebox(); ?&gt;</code></strong> in your template.</p></div>";
}
?>
<h2><?php _e('Ratings'); ?></h2>
<table class="widefat">
<thead>
  <tr>
    <th scope="col"><a href="<?php echo $base_page; ?>&sort=page" style="color: #D7D7D7"><?php _e('Page') ?></a></th>
    <th scope="col"><a href="<?php echo $base_page; ?>&sort=displays" style="color: #D7D7D7"><?php _e('Displays') ?></a></th>
    <th scope="col"><a href="<?php echo $base_page; ?>&sort=votes" style="color: #D7D7D7"><?php _e('Votes') ?></a></th>
    <th scope="col"><a href="<?php echo $base_page; ?>&sort=rank" style="color: #D7D7D7"><?php _e('User Rank') ?></a></th>
    <th scope="col"><a href="<?php echo $base_page; ?>&sort=opinions" style="color: #D7D7D7"><?php _e('Opinions') ?></a></th>
    <th scope="col"><?php _e('Actions') ?></th>
  </tr>
</thead>
<tbody id="the-comment-list" class="list:comment">
<?php
if ($pages)
{
	$i = 0;
foreach($pages as $page)
        {
if($i%2 == 0) {
	$style = 'class="alternate"';
}  else {
	$style = '';
}
echo "<tr $style>\n";
echo "<td>" . (!empty($page->post_title)?$page->post_title:"Main Page") . "</td>\n";
echo "<td>" . $page->displays . "</td>\n";
            echo "<td>" . $page->total_votes . "</td>\n";
            echo "<td>" . sprintf("%.2f", round($page->rank, 2)) . "</td>\n";
            echo "<td>" . $page->opinions . "</td>\n";
            echo "<td><a href=\"$base_page&pid=".$page->id."\">Edit</a>  | <a href=\"$base_page&pid=".$page->id."&act=page_del\" onClick=\"javascript:return confirm('Are you sure?');\">Delete</a> </td>\n";
            echo "</tr>";
            $i++;
     }
}
else
{
    echo "<tr><td>No results</td></tr>";
}
?>
</tbody>
</table>
</form>
</div>

<?php

}

?>
