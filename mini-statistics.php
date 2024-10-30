<?php
/*
Plugin Name: Mini Statistics
Plugin URI: https://wordpress.org/plugins/mini-statistics/
Description: This plugin is a small and simple Users Statistics and Comments Statistics plugin for WordPress.
Version: 1.0.3
Author: Ayan Debnath
Author URI: http://about.me/ayandebnath
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: mini-statistics
*/

/*
	This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

add_action( 'admin_menu', 'add_ministat_menu' );
add_action( 'admin_enqueue_scripts', 'ministat_enqueue' );

function add_ministat_menu() {
	
	add_menu_page( 'Statistics', 'Statistics', 'manage_options', 'ministatistics', 'ministat_page', 'dashicons-chart-area', 100 );
	
	add_submenu_page( 'users.php', 'Users Statistics', 'Users Statistics', 'list_users', 'users-ministat', 'users_ministat_report' );
	add_submenu_page( 'ministatistics', 'Users Statistics', 'Users Statistics', 'list_users', 'users-ministat', 'users_ministat_report' );
	
	add_submenu_page( 'edit-comments.php', 'Comments Statistics', 'Comments Statistics', 'moderate_comments', 'comments-ministat', 'comments_ministat_report' );
	add_submenu_page( 'ministatistics', 'Comments Statistics', 'Comments Statistics', 'moderate_comments', 'comments-ministat', 'comments_ministat_report' );
}

function ministat_enqueue($hook) {
	if ( 'users_page_users-ministat' != $hook && 'comments_page_comments-ministat' != $hook) {return;}
    wp_enqueue_script( 'users_ministat_custom_script', '//www.google.com/jsapi' );
}

/////////////////////////////////////////////////////////////////////////////////////
function ministat_page() {
	global $wpdb;
	
	echo '<div class="wrap"><div id="icon-tools" class="icon32 dashicons dashicons-chart-area"></div>';
		echo '<h2>Statistics</h2>';
	?>
	<div class="have-key">
		<div id="wpcom-stats-meta-box-container" class="metabox-holder">
	
			<div class="postbox-container" style="width:45%;margin:10px;">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<div id="referrers" class="postbox">
						<h3 class="hndle"><span class="dashicons dashicons-admin-users"></span> <span>Users Statistics</span></h3>
						<div class="inside">
							View statistical data of user registration in the website.
						</div>
						<div id="major-publishing-actions">
							<div id="publishing-action">
								<a class="button button-primary" href="<?php echo get_option( 'siteurl' ); ?>/wp-admin/users.php?page=users-ministat">View Statistics</a>
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="postbox-container" style="width:45%;margin:10px;">
				<div id="normal-sortables" class="meta-box-sortables ui-sortable">
					<div id="referrers" class="postbox">
						<h3 class="hndle"><span class="dashicons dashicons-admin-comments"></span> <span>Comments Statistics</span></h3>
						<div class="inside">
							View statistical data of comments in the website.
						</div>
						<div id="major-publishing-actions">
							<div id="publishing-action">
								<a class="button button-primary" href="<?php echo get_option( 'siteurl' ); ?>/wp-admin/edit-comments.php?page=comments-ministat">View Statistics</a>
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="clear"><hr /></div>
			
			
			<!-- MORE IN NEAR FUTURE -->
	
		</div>
	</div>
	
	<div class="clear"></div>
	
	<div><p><small>In future, we will add more statistics and provide APIs to implement your own custom Statistics pages.</small></p></div>
	
	<?php
	echo '</div>';
}
/////////////////////////////////////////////////////////////////////////////////////
function comments_ministat_report() {
	global $wpdb;
	
	$max_c = 0;
	$data1 = '';
	$data2 = '';
	$data_table = array();
	
	echo '<div class="wrap"><div id="icon-tools" class="icon32 dashicons dashicons-chart-area"></div>';
		echo '<h2>Comments Statistics</h2>';
	
	$start_date = intval($wpdb->get_var( 
		"
		SELECT year(comment_date) FROM $wpdb->comments
			WHERE comment_approved = 1
			ORDER BY comment_date
			LIMIT 1
		"
	));
	
	$flag1=false;
	$flag2=true;
	$data1 = "['Time', 'Comments'],";
	
	$tmp = '';
	for ( $y=$start_date; $y<=intval(date('Y')); $y++ ) {
		$tmp .= ',' . "'$y'";
		for ($m=1; $m<=12; $m++) {
			$c = intval($wpdb->get_var( 
				$wpdb->prepare( 
					"
					SELECT count(*) C FROM $wpdb->comments
						WHERE comment_approved = 1
							AND MONTH(comment_date) = %d 
							AND YEAR(comment_date) = %d
					" 
				, $m, $y)
			));
			
			if($c>0) $flag1=true;
			
			if($flag1 && $flag2) {
				$mn=strftime("%b", mktime(0, 0, 0, $m));
				$data1 .= "['{$mn}-{$y}' , {$c}],";
			}
			
			if( $c > $max_c ) $max_c = $c;
			$data_table[date('M', mktime(0, 0, 0, $m, 10))][$y] = $c;
		}
	}
	
	$data2 = "['Month' $tmp],";
	
	$tmp = '';
	foreach ($data_table as $k1 => $y) {
		$tmp = "'$k1'";
		foreach ($y as $k2 => $d) {
			$tmp .= ','. $d;
		}
		$data2 .= "[$tmp],";
	}
	
	$data1 = trim(trim($data1, ','));
	$data2 = trim(trim($data2, ','));
	?>
	<div class="ministat_nav" style="text-align:right;margin:-30px 10px 15px 0;">
		<button class="button button-primary" id="timeline">Overview</button>
		<button class="button" id="line">Line Chart</button>
		<button class="button" id="column">Column Chart</button>
	</div>
	
	<div id="chart_div" style="width:98%; height: 450px;"></div>
	<script type="text/javascript">
	var chartType = 'timeline';
	
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);

	function drawChart() {
		var data1 = google.visualization.arrayToDataTable([<?php echo $data1;?>]);
		var data2 = google.visualization.arrayToDataTable([<?php echo $data2;?>]);
		var options = {
			title: '',
			vAxis: { 
				viewWindow:{
					max:<?php echo $max_c;?>, min:0
				},
				format: '0'
			},
			curveType: 'function',
			
			_vAxis: {format: '0'},
			_hAxis: {title: 'Month', titleTextStyle: {color: 'red'}}
		};
		
		if(chartType == 'timeline') {
			var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
			chart.draw(data1, options);
		}
		
		if(chartType == 'column') {
			var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
			chart.draw(data2, options);
		}
		
		if(chartType == 'line') {
			var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
			chart.draw(data2, options);
		}
	}
	
	jQuery(document).ready(function($) {
		
		$('.ministat_nav button#timeline').click(function(){
			chartType = 'timeline';
			drawChart();
			
			$(".ministat_nav button").removeClass('button-primary');
			$(this).addClass('button-primary');
		});

		$('.ministat_nav button#column').click(function(){
			chartType = 'column';
			drawChart();
			
			$(".ministat_nav button").removeClass('button-primary');
			$(this).addClass('button-primary');
		});
		
		$('.ministat_nav button#line').click(function(){
			chartType = 'line';
			drawChart();
			
			$(".ministat_nav button").removeClass('button-primary');
			$(this).addClass('button-primary');
		});
	});
	</script>
	<?php
	echo '</div>';
}
/////////////////////////////////////////////////////////////////////////////////////
function users_ministat_report() {
	global $wpdb;
	
	$max_c = 0;
	$data1 = '';
	$data2 = '';
	$data_table = array();
	
	echo '<div class="wrap"><div id="icon-tools" class="icon32 dashicons dashicons-chart-area"></div>';
		echo '<h2>Users Statistics</h2>';
	
	$start_date = intval($wpdb->get_var( 
		"
		SELECT year(user_registered) FROM $wpdb->users
			ORDER BY user_registered
			LIMIT 1
		"
	));
	
	$flag1=false;
	$flag2=true;
	$data1 = "['Time', 'Registrations'],";
	
	$tmp = '';
	for ( $y=$start_date; $y<=intval(date('Y')); $y++ ) {
		$tmp .= ',' . "'$y'";
		for ($m=1; $m<=12; $m++) {
			$c = intval($wpdb->get_var( 
				$wpdb->prepare( 
					"
					SELECT count(*) C FROM $wpdb->users
						WHERE MONTH(user_registered) = %d 
							AND YEAR(user_registered) = %d
					" 
				, $m, $y)
			));
			
			if($c>0) $flag1=true;
			if($flag1 && $flag2) {
				$mn=strftime("%b", mktime(0, 0, 0, $m));
				$data1 .= "['{$mn}-{$y}' , {$c}],";
			}
			
			if( $c > $max_c ) $max_c = $c;
			$data_table[date('M', mktime(0, 0, 0, $m, 10))][$y] = $c;
		}
	}
	$data2 = "['Month' $tmp],";
	
	$tmp = '';
	foreach ($data_table as $k1 => $y) {
		$tmp = "'$k1'";
		foreach ($y as $k2 => $d) {
			$tmp .= ','. $d;
		}
		$data2 .= "[$tmp],";
	}
	
	$data1 = trim(trim($data1, ','));
	$data2 = trim(trim($data2, ','));
	?>
	<div class="ministat_nav" style="text-align:right;margin:-30px 10px 15px 0;">
		<button class="button button-primary" id="timeline">Overview</button>
		<button class="button" id="line">Line Chart</button>
		<button class="button" id="column">Column Chart</button>
	</div>
	
	<div id="chart_div" style="width:98%; height: 450px;"></div>
	<script type="text/javascript">
	var chartType = 'timeline';
	
	google.load("visualization", "1", {packages:["corechart"]});
	google.setOnLoadCallback(drawChart);

	function drawChart() {
		var data1 = google.visualization.arrayToDataTable([<?php echo $data1;?>]);
		var data2 = google.visualization.arrayToDataTable([<?php echo $data2;?>]);
		var options = {
			title: '',
			vAxis: { 
				viewWindow:{
					max:<?php echo $max_c;?>, min:0
				},
				format: '0'
			},
			curveType: 'function',
			
			_vAxis: {format: '0'},
			_hAxis: {title: 'Month', titleTextStyle: {color: 'red'}}
		};
		
		if(chartType == 'timeline') {
			var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
			chart.draw(data1, options);
		}
		
		if(chartType == 'column') {
			var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
			chart.draw(data2, options);
		}
		
		if(chartType == 'line') {
			var chart = new google.visualization.AreaChart(document.getElementById('chart_div'));
			chart.draw(data2, options);
		}
	}
	
	jQuery(document).ready(function($) {
		
		$('.ministat_nav button#timeline').click(function(){
			chartType = 'timeline';
			drawChart();
			
			$(".ministat_nav button").removeClass('button-primary');
			$(this).addClass('button-primary');
		});
		
		$('.ministat_nav button#column').click(function(){
			chartType = 'column';
			drawChart();
			
			$(".ministat_nav button").removeClass('button-primary');
			$(this).addClass('button-primary');
		});
		
		$('.ministat_nav button#line').click(function(){
			chartType = 'line';
			drawChart();
			
			$(".ministat_nav button").removeClass('button-primary');
			$(this).addClass('button-primary');
		});
	});
	</script>
	<?php
	echo '</div>';
}
?>