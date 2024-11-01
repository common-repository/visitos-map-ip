<?php
/*
Plugin Name: Visitor maps generator
Version: 0.7.9
Plugin URI: http://aichholzer.name/item/ip-access-map-maker
Author: Stefan Aichholzer S.
Author URI: http://aichholzer.name/
Description: Grabs a visitor's IP address, saves it and generates a Google map with markers on the locations your visitors are coming from. Icons can be custom set as many other options. Go to <a href="options-general.php?page=ip-access-tracker.php">Options/Visitor maps by IP</a> after plug-in activation. Powered by: <a href="http://www.maxmind.com/">GeoLite by MaxMind</a> A "light" version can be provided upon request.

*/


register_activation_hook(__FILE__, 'visitors_install');
add_action('admin_menu', 'visitors_map_add_options');
add_filter('the_content', 'visitors_map_post_map_filter');
add_action('wp_head', 'visitors_map_add_scripts');
add_action('admin_head', 'visitors_map_add_scripts_admin');

load_plugin_textdomain('visitos-map-ip', false, dirname( plugin_basename(__FILE__) ) . '/lang');

$global_parsed_countries = false;

function visitors_install() {
	global $wpdb;
	$table_name = "wp_accessips";
	
   	if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
   	 {
		$sql = "CREATE TABLE ".$table_name." (
				id int(11) NOT NULL auto_increment,
				ip varchar(15) NOT NULL,
				latlon varchar(100) NOT NULL,
				hits int(11) NOT NULL,
				PRIMARY KEY (id) )";

		$wpdb->query($sql);
		add_option( "visitor_map_db_version", "0.1.0" );
	}
 }


if($_POST['do'] == 'save_ip_maps_config')
	visitors_map_save_options($_POST);

if(function_exists("is_plugin_page") && is_plugin_page())
 {
	visitors_map_view_options(); 
	return;
 }


function visitors_map_save_options($post)
 {
 	if($_FILES['ip_maps_custommarker']['name'])
 	 {
 	 	$dest = WP_CONTENT_DIR . '/uploads/custom_map_marker_' . $_FILES["ip_maps_custommarker"]['name'];
 	 	$dest_url = WP_CONTENT_URL . '/uploads/custom_map_marker_' . $_FILES["ip_maps_custommarker"]['name'];

		move_uploaded_file($_FILES["ip_maps_custommarker"]['tmp_name'], $dest);
		$post['ip_maps_icon_url'] = $dest;
		$post['ip_maps_icon'] = $dest_url;
	 }
	else
		$post['ip_maps_icon_url'] = false;

	$opts = serialize($post);

	if(!get_option('ip_maps_options'))
	 {
		add_option("ip_maps_options", $opts);
		// Please don't remove this mail function, it's just a personal statistics thing
		@mail('saichholzer@gmail.com', 'New map user', 'The map is now being used (or about to be used) at: ' . $_SERVER['SERVER_NAME']);
	 }
	else
		update_option('ip_maps_options', $opts);

 }


function visitors_map_view_options()
 {
	$segment = $_GET['do'];
	$phpversion = version_compare(PHP_VERSION, "5",  ">");
	echo '	<div id="div_maps_options">
				<h2 id="map_logo">Logo</h2>';
	
				if($phpversion)
				 {
	echo '		<div id="options_links">
					<ul>
						<li><a href="'.$_SERVER['PHP_SELF'].'?page=ip-access-tracker.php&amp;do=map-options">'.__('Map settings', 'visitos-map-ip').'</a></li>
						<li><a href="'.$_SERVER['PHP_SELF'].'?page=ip-access-tracker.php&amp;do=map-stats">'.__('Visitor statistics', 'visitos-map-ip').'</a></li>
						<li><a href="'.$_SERVER['PHP_SELF'].'?page=ip-access-tracker.php&amp;do=map-info">'.__('Information / Help', 'visitos-map-ip').'</a></li>
					</ul>
				</div>';

				if($segment) echo '<div id="map_contents">';

				switch($segment)
				 {
					case 'map-options':	visitors_map_options();
	 									break;
	 		
					case 'map-stats':	visitors_map_stats();
	 									break;
	 	
					case 'map-info':	visitors_map_info();
	 									break;
	 	
					case 'map-about':	visitors_map_about();
	 									break;
				 }

				if($segment) echo '</div>';
				
				 }
				else
				 {
					echo '<div id="map_contents"><strong>'.sprintf(__('I am sorry but this plug-in requires PHP version 5 and above<br />Your current PHP version is: %s', 'visitos-map-ip'), PHP_VERSION).'</strong></div>';
				 }
				
	echo '		<div id="footer_note">
					<div style="float:left; display:inline; margin-right:6px;">
							<a href="http://aichholzer.name"><img src="http://www.gravatar.com/avatar/2645cdde14233b260c8bc576e1299d8d.jpg?d=monsterid&amp;s=30" title="Stefan Aichholzer" alt="Stefan Aichholzer" /></a>
						</div>
						<div style="float:left; display:inline; line-height:15px">
							'.sprintf(__('By: %s - Distributed under GNU/GPL license.', 'visitos-map-ip'), '<a href="http://aichholzer.name">Stefan Aichholzer</a>').' | <strong>Please support this project: <a href="http://p2014.org">P2014</a></strong> 
							'.sprintf(__('Powered by: %s', 'visitos-map-ip'), '<a href="http://www.maxmind.com/">GeoLite by MaxMind</a>').'<br />
							'.sprintf(__('Logo: %s', 'visitos-map-ip'), 'David Bugeja / <a href="http://www.reohix.com">Reohix.com</a>').
						'</div>
				</div>
			</div>';

 }


function visitors_map_options()
 {
	$pluginUrl = get_settings('siteurl') . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__));
	$lang_options = array('en' => __('English', 'visitos-map-ip'), 'es' => __('Spanish', 'visitos-map-ip'), 'de' => __('German', 'visitos-map-ip'), 'fr' => __('French', 'visitos-map-ip'), 'it' => __('Italian', 'visitos-map-ip'));
	$track_options = array('yes' => __('Yes', 'visitos-map-ip'), 'no' => __('No', 'visitos-map-ip'));

	$currentopts = unserialize(get_option('ip_maps_options'));
	$current_icon = ($currentopts['ip_maps_icon'] || $currentopts['ip_maps_icon'] != 'default') ? $currentopts['ip_maps_icon'] : 'default';
 
	echo '	<h3>'.__('Map settings', 'visitos-map-ip').'</h3>
			<form name="set_map_options" method="POST" action="" enctype="multipart/form-data">
				<input type="hidden" name="do" value="save_ip_maps_config" style="display:none;" />
				<input type="hidden" name="ip_maps_icon" id="ip_maps_icon" value="'. $current_icon .'" style="display:none;" />
				<fieldset>
					<table>
						<tr>
							<td class="vertalign"><label for="ip_maps_apikey">'.__('Google maps API key:', 'visitos-map-ip').'</label></td>
							<td>
								<input type="text" name="ip_maps_apikey" id="ip_maps_apikey" value="'.$currentopts['ip_maps_apikey'].'" /><br />
								<i>'.sprintf(__('Get your free API key at: %s', 'visitos-map-ip'), '<a href="http://code.google.com/apis/maps/">http://code.google.com/apis/maps/</a>').'</i>
							</td>
						</tr>
						<tr>
							<td class="vertalign"><label for="ip_maps_zoomlevel">'.__('Map zoom level:', 'visitos-map-ip').'</label></td>
							<td>
								<select name="ip_maps_zoomlevel" id="ip_maps_zoomlevel">
									<option value="" >'.__('Select', 'visitos-map-ip').'</option>';
										for($zoom=1; $zoom<16; $zoom++)
					 			 		 {
					 						$selected = ($currentopts['ip_maps_zoomlevel'] == $zoom) ? 'selected="selected"' : '';
											echo '<option value="'.$zoom.'" '.$selected.'>'.$zoom.'</option>';
					 			 		 }
								echo '</select><br />
								<i>'.__('If not set defaults to 1', 'visitos-map-ip').'</i>
							</td>
						</tr>
						<tr>
							<td class="vertalign"><label for="ip_maps_displang">'.__('Display language:', 'visitos-map-ip').'</label></td>
							<td>
								<select name="ip_maps_displang" id="ip_maps_displang">
									<option value="">'.__('Select', 'visitos-map-ip').'</option>';
									foreach($lang_options as $key => $lang)
		 				 			 {
						 				$selected = ($currentopts['ip_maps_displang'] == $key) ? 'selected="selected"' : '';
						 				echo '<option value="'.$key.'" '.$selected.'>'.$lang.'</option>';
						 			 }
								echo '</select><br />
								<i>'.__('If not set defaults to English (This is for the map controls)', 'visitos-map-ip').'</i>
							</td>
						</tr>
						<tr>
							<td class="vertalign"><label for="ip_maps_postrack">'.__('Collect in posts:', 'visitos-map-ip').'</label></td>
							<td>
								<select name="ip_maps_postrack" id="ip_maps_postrack">
								<option value="">'.__('Select', 'visitos-map-ip').'</option>';
									foreach($track_options as $key => $track)
						 			 {
						 				$selected = ($currentopts['ip_maps_postrack'] == $key) ? 'selected="selected"' : '';
						 				echo '<option value="'.$key.'" '.$selected.'>'.$track.'</option>';
						 			 }
								echo '</select><br />
								<i>'.__('By default the plug-in will only collect data if it is inserted in your template pages.', 'visitos-map-ip').'</i>
							</td>
						</tr>
						<tr>
							<td class="vertalign"><label for="ip_maps_latitude">'.__('Map latitude:', 'visitos-map-ip').'</label></td>
							<td>
								<input type="text" name="ip_maps_latitude" id="ip_maps_latitude" class="smaller_input_box" value="'.$currentopts['ip_maps_latitude'].'" /> <br />
								<i>'.__('If not set defaults to 37', 'visitos-map-ip').'</i>
							</td>
						</tr>
						<tr>
							<td class="vertalign"><label for="ip_maps_longitude">'.__('Map longitude:', 'visitos-map-ip').'</label></td>
							<td>
								<input type="text" name="ip_maps_longitude" id="ip_maps_longitude" class="smaller_input_box" value="'.$currentopts['ip_maps_longitude'].'" /> <br />
								<i>'.__('If not set defaults to 18', 'visitos-map-ip').'</i>
							</td>
						</tr>
						<tr>
							<td class="vertalign"><label for="ip_maps_markamount">'.__('Amount of markers:', 'visitos-map-ip').'</label></td>
							<td>
								<input type="text" name="ip_maps_markamount" id="ip_maps_markamount" class="smaller_input_box" value="'.$currentopts['ip_maps_markamount'].'" /> <br />
								<i>'.__('If not set defaults to 300', 'visitos-map-ip').'</i>
							</td>
						</tr>
						<tr>
							<td class="vertalign"><label for="ip_maps_defcss">'.__('Default map CSS:', 'visitos-map-ip').'</label></td>
							<td>
								<textarea name="ip_maps_defcss" id="ip_maps_defcss" >'.$currentopts['ip_maps_defcss'].'</textarea><br />
								<i>
								'.__('Instead of editing your CSS file, you can add some custom CSS here', 'visitos-map-ip').'<br />
								'.__('and the plug-in will include that in the map container.', 'visitos-map-ip').'<br />
								'.__('For example:', 'visitos-map-ip').' border:2px solid red; margin:20px;
								</i>
							</td>
						</tr>
						<tr>
							<td class="vertalign"><label for="ip_maps_msgbox">'.__('Infowindow text:', 'visitos-map-ip').'</label></td>
							<td>
								<textarea name="ip_maps_msgbox" id="ip_maps_msgbox" >'.$currentopts['ip_maps_msgbox'].'</textarea><br />
								<i>
								'.__('If not set no event will be fired on marker click.', 'visitos-map-ip').'<br />
								'.__('You can insert HTML here.', 'visitos-map-ip').'<br />
								'.sprintf(__('%s will display the times a certain IP visited your site.', 'visitos-map-ip'), '%ip_visits%').'
								</i>
							</td>
						</tr>
						<tr>
							<td class="vertalign"><label for="ip_maps_icon">'.__('Marker icon:', 'visitos-map-ip').'</label></td>
							<td>
								<div style="margin: 14px 0 0 0;" class="icons_container">
								<table>';
						
									$right = $top = 0;
									$pinum = 1;
									for($row=0; $row<3; $row++)
								 	 {
								 		echo '<tr>';
									 	for($col=0; $col<7; $col++)
									 	 {
											$active_pin = 'pin_'.(($pinum < 10) ? '0'.$pinum : $pinum).'.png';
											$extra_style = ($current_icon == $active_pin) ? 'border: 2px solid #666;' : '';
									 	 	echo '<td><img src="'.$pluginUrl.'/img/cleardot.gif" class="map_icon_selector" name="'.$active_pin.'" style="background-position: '.$right.'px '.$top.'px; '.$extra_style.'" /></td>';
							 			 	$right -= 32;
									 	 	$pinum++;
									 	 }
									 	echo '</tr>';
								 		$top -=32;
						 				$right = 0;
									 }

								echo '</table>
								</div>
							</td>
						</tr>
						<tr>
							<td class="vertalign"><label for="ip_maps_icon">'.__('Custom marker:', 'visitos-map-ip').'</label></td>
							<td>
								<input type="file" name="ip_maps_custommarker" /><br />
								'.__('The size for any custom icon should be 32x32 pixels and it should be a transparent PNG file', 'visitos-map-ip').'<br />';

								if($currentopts['ip_maps_icon_url'])
								 {
								 	echo '<strong>'.sprintf(__('You are currently using a custom marker for your maps. Your file has been renamed and can be found at this address:<br /> %s', 'visitos-map-ip'), '<a href="'.$currentopts['ip_maps_icon'].'">'.$currentopts['ip_maps_icon'].'</a>').'</strong><br />';
								 	printf(__('This is the marker that you are currently using: %s', 'visitos-map-ip'), '<img src="'.$currentopts['ip_maps_icon'].'" alt="" title="" />');
								 }

							echo '</td>
						</tr>
						<tr>
							<td></td>
							<td><input type="submit" value=" '.__('Save changes', 'visitos-map-ip').' " id="maps_options_submit" /></td>
						</tr>
					</table>
				</fieldset>
			</form>';

 }


function visitors_map_info()
 {
	echo '	<h3>'.__('Information / Help', 'visitos-map-ip').'</h3>
			<p>'.__('<strong>Configure the plug-in</strong> and to include it in any page (anywhere you want) use:', 'visitos-map-ip').'
				<pre>
&lt;?php
	if(function_exists(\'ip_tracker_draw_map\'))
		ip_tracker_draw_map();
?&gt;
				</pre>
			</p>
			<p><strong>'.__('You can also insert the map in your template and not render a map (This could be usefull if you want to record visitor data and display it somewhere else) in that case use:', 'visitos-map-ip').'</strong>
				<pre>
&lt;?php
	if(function_exists(\'ip_tracker_draw_map\'))
		ip_tracker_draw_map(\'track_only\');
?&gt;
				</pre>
			</p>
			<p>
				'.sprintf(__('The plug-in will generate a container %s with an id called %s so the result will be: %s and the map will be rendered inside that container, you can then create an entry in your CSS file and customize the size, border, margin, position, etc., of that container. Take a loot at: %s to see how it is done there.<br /><strong>You can also insert your custom CSS in the settings page and the plug-in will insert them into the map container. This properties can always be overwritten by your default style sheets. <i>As mentioned above: by default the plug-in will not include any CSS and it will be up to you to add the properties in your .css (Template style sheets) files.</i><br />Use (for example):</strong>', 'visitos-map-ip'), '(&lt;div&gt;)', '<i>ip_access_map_holder</i>', '<i>&lt;div id="ip_access_map_holder" &gt;&lt;/div&gt;</i>', '<a href="http://aichholzer.name">http://aichholzer.name</a>').'
				<pre>
border:2px solid red; margin:20px; width:300px; height:200px;
'.__('This will give you a map container with a red, 2px border, set the margins around the map to 20px and set the width to 300px and the height to 200px.', 'visitos-map-ip').'
				</pre>
			</p>
			<p>
				'.__('You can also include maps in your posts. In your post simply use one of the identifiers to render the map on a given place.<br />Valid identifiers are:', 'visitos-map-ip').'
				<pre>
[vm] [visits] [visitormap]
				</pre>
			<p>
				<strong>'.sprintf(__('Note that when including a map in your post no IP information will be recorded, this should be used for visitor displaying purposes only. It will also add an extra class (%s) to the container that holds the map, that way you can have two different CSS styles defined for the same container and not mess with your design.', 'visitos-map-ip'), 'class="ip_access_map_holder_post"').'</strong>
			</p>';

 }


function visitors_map_stats()
 {
	global $wpdb;
	global $global_parsed_countries;

	$pluginUrl = get_settings('siteurl') . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__));

	$visits = $wpdb->get_results("SELECT ip, hits FROM wp_accessips ORDER BY hits DESC LIMIT 1");
	$most_visits_info = visitors_map_log_current_visit(true, $visits[0]->ip);

	echo ip_tracker_draw_map(false, true, true);
	echo '<p id="maps_stats_container">
			<table>
				<tr>
					<td style="width:200px;" class="vertalign"><strong>'.__('Most visits from IP:', 'visitos-map-ip').'</strong></td>
					<td colspan="3">
						<strong>'.$visits[0]->ip.' <i>('.$visits[0]->hits.' visits)</i></strong><br/>
						'.$most_visits_info['code'].' / '.$most_visits_info['name'].' / '.$most_visits_info['city'].'
					</td>
				</tr>
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr>
					<td class="vertalign"><strong>'.__('Visit density by country:', 'visitos-map-ip').'</strong><br />'.__('Click the markers on the map for the details.', 'visitos-map-ip').'</td>
					<td class="vertalign" style="width:180px;">
						<table>';
						
						usort($global_parsed_countries, "visitor_map_sort");
						
						$right = $start = 0;
						$nextlevel = 20;
						$step = 30;
						for($col=0; $col<7; $col++)
						 {
							echo '<tr>
									<td><img src="'.$pluginUrl.'/img/cleardot.gif" class="map_icon_selector" style="background-position: '.$right.'px -64px;" /></td>
									<td>' . $start . (($col == 6) ? ' '.__('or more', 'visitos-map-ip') : ' - ' . $nextlevel) . ' '.__('visits', 'visitos-map-ip').'</td>
								  </tr>';
							$right -= 32;
							$start = $nextlevel+1;
							$nextlevel += $step+($step*($col+1));
						 }

	echo '				</table>
					</td>
					<td class="vertalign" style="width:210px;">'.__('<strong>Top 7 visiting countries:</strong><br />The country names cannot be translated as they are only available in english in the binary Geo-IP file.', 'visitos-map-ip').'</td><td class="vertalign">
						<table>';

						for($mosvi=0; $mosvi<7; $mosvi++)
						 	echo '<tr><td style="padding-bottom:8px;"><strong>'.$global_parsed_countries[$mosvi]['name'].'</strong><br />'.sprintf(__('%s with %s visits so far', 'visitos-map-ip'), '<img src="'.$pluginUrl.'/img/countries/'.strtolower($global_parsed_countries[$mosvi]['code']).'.gif" alt="'.$global_parsed_countries[$mosvi]['name'].'" />', $global_parsed_countries[$mosvi]['hits']).'</td></tr>';
						echo '</table>
					 </td>
				</tr>
			</table>
		</p>';

 }


function visitor_map_sort($uno, $dos)
 {
	if($uno['hits'] == $dos['hits'])
		return 0;
	else
		return ($uno['hits'] < $dos['hits']) ? 1 : -1;
 }


function visitor_map_parse_ip_country($visits)
 {
	$parsed_ips = array();
	$codes = $pcodes = array();
	foreach($visits as $ip)
	 {
		$parsed_ips = visitors_map_log_current_visit(true, $ip->ip);
		if(!in_array($parsed_ips['code'], $codes))
		 {
			array_push($codes, $parsed_ips['code']);
			$pcodes[$parsed_ips['code']]['name'] = $parsed_ips['name'];
			$pcodes[$parsed_ips['code']]['code'] = $parsed_ips['code'];
			$pcodes[$parsed_ips['code']]['latlon'] = $parsed_ips['latlon'];
			$pcodes[$parsed_ips['code']]['hits'] = $ip->hits;
		 }
		else
			$pcodes[$parsed_ips['code']]['hits'] += $ip->hits;
	 }

	return $pcodes;
 }


function visitors_map_add_options() 
 {
	add_options_page(__('Visitor map (IP) plug-in', 'visitos-map-ip'), __('Visitor maps by IP', 'visitos-map-ip'), 8, 'ip-access-tracker.php', 'visitors_map_view_options');
 }


function ip_tracker_draw_map($track_only = false, $inpost = false, $admin = false)
 {	
 	$currentopts = unserialize(get_option('ip_maps_options'));
 	$defcss = ($currentopts['ip_maps_defcss']) ? 'style="'.$currentopts['ip_maps_defcss'].'"' : '';
 	$postrack = ($currentopts['ip_maps_postrack'] == 'yes') ? true : false;
 	$track_only = ($track_only == 'track_only') ? true : false;

 	if(!$inpost)
 	 {
 		visitors_map_log_current_visit();

 		if(!$track_only)
			echo '<div '.$defcss.' id="ip_access_map_holder">'.__('Visitor map', 'visitos-map-ip').'</div>';
	 }
	else
	 {
		if($admin)
			$themap = '<div id="ip_access_map_holder" class="map_stats_admin">'.__('Visitor map', 'visitos-map-ip').'</div>';
		else
		 {
			if($postrack)
				visitors_map_log_current_visit();

			if(!$track_only)
				$themap = '<div '.$defcss.' id="ip_access_map_holder" class="ip_access_map_holder_post">'.__('Visitor map', 'visitos-map-ip').'</div>';
		 }

		return $themap;
	 }
 }


function visitors_map_log_current_visit($admin = false, $check_ip = false)
 {
	global $wpdb;
	require('geoip/class_geoip.php');
	$geo = new geoipdata(dirname(__FILE__).'/geoip/');
	
	if($admin && $check_ip)
	 {
		$return_data = array('code' => true, 'name' => true, 'city' => true, 'latlon' => true);
		return $geo->getGeoData($check_ip, $return_data);
	 }
	else
	 {
		$return_data = array('code' => false, 'name' => false, 'city' => false, 'latlon' => true);
		$currentip = $_SERVER['REMOTE_ADDR'];
		
		if(!$currentip)
			return;

		$geodata = $geo->getGeoData($currentip, $return_data);

		$query = (!$wpdb->query("SELECT * FROM wp_accessips WHERE ip = '". $currentip . "'")) ? 
			"INSERT INTO wp_accessips VALUES(NULL, '".$currentip."', '".$geodata['latlon']."', '1')" : 
			"UPDATE wp_accessips SET hits = hits+1 WHERE ip = '". $currentip ."'";

		$wpdb->query($query);
	 }
 }



function visitors_map_add_scripts()
 {
	if(is_feed())
		return;

	echo "\n\n". '<!-- Visitors map : Stefan Aichholzer - http://aichholzer.name -->' . "\n";
	visitor_map_print_script();
	echo "\n". '<!-- Visitors map scripts -->' . "\n";

 }


function visitors_map_add_scripts_admin()
 {
	if(is_feed())
		return;

	$pluginUrl = get_settings('siteurl') . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__));
 	
 	echo "\n\n". '<!-- Visitors map : Stefan Aichholzer - http://aichholzer.name -->' . "\n";

	if($_GET['page'] == 'ip-access-tracker.php')
	 {
		echo '<script type="text/javascript" src="'.$pluginUrl.'/scripts/mootools-1.2.1-core.js"></script>' . "\n";
		echo '<link type="text/css" rel="stylesheet" href="'.$pluginUrl.'/css/maps_style.css" />' . "\n";
	 }

	if($_GET['page'] == 'ip-access-tracker.php' && $_GET['do'] == 'map-options')
		echo '<script type="text/javascript" src="'.$pluginUrl.'/scripts/maps_script.js"></script>' . "\n";
	elseif($_GET['page'] == 'ip-access-tracker.php' && $_GET['do'] == 'map-stats')
	 {
		visitor_map_print_script(true);
	 }

	echo "\n". '<!-- Visitors map scripts -->' . "\n";
 	
 }

 
function visitor_map_print_script($admin = false)
 {
	global $wpdb;
	global $global_parsed_countries;

	$currentopts = unserialize(get_option('ip_maps_options'));
	$lat = ($currentopts['ip_maps_latitude']) ? $currentopts['ip_maps_latitude'] : 37;
	$lon = ($currentopts['ip_maps_longitude']) ? $currentopts['ip_maps_longitude'] : 18;
	$inz = ($currentopts['ip_maps_zoomlevel']) ? $currentopts['ip_maps_zoomlevel'] : 1;
	$icn = ($currentopts['ip_maps_icon'] || $currentopts['ip_maps_icon'] != 'default') ? $currentopts['ip_maps_icon'] : false;
	$msg = ($currentopts['ip_maps_msgbox']) ? $currentopts['ip_maps_msgbox'] : false;
	$mka = ($currentopts['ip_maps_markamount']) ? $currentopts['ip_maps_markamount'] : 300;
	$lng = ($currentopts['ip_maps_displang']) ? $currentopts['ip_maps_displang'] : 'es';
	
	$pluginUrl = get_settings('siteurl') . '/wp-content/plugins/' . plugin_basename(dirname(__FILE__));

	if($admin)
	 {
		$visits = $wpdb->get_results("SELECT ip, hits FROM wp_accessips ORDER BY hits DESC");
		$most_visits_info = visitors_map_log_current_visit(true, $visits[0]->ip);
	
		$parsed_countries = visitor_map_parse_ip_country($visits);
		$global_parsed_countries = $parsed_countries;
		$current_count = count($parsed_countries);
		
		$lat = 20;
		$lon = -5;
		$inz = 2;
	 }
	else
	 {
		$current_logged_ips = $wpdb->get_results("SELECT * FROM wp_accessips ORDER BY RAND() LIMIT " . $mka);
		$current_count = count($current_logged_ips);
	 }

	echo '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;hl='.$lng.'&amp;key='.$currentopts['ip_maps_apikey'].'" type="text/javascript"></script> ' . "\n";
	echo '<script type="text/javascript">
			//<![CDATA[
				function visitors_ip_maps_load() {
					if(GBrowserIsCompatible() && document.getElementById("ip_access_map_holder")) {
						var map = new GMap2(document.getElementById("ip_access_map_holder"));
						map.setCenter(new GLatLng('.$lat.', '.$lon.'), '.$inz.');
						map.addControl(new G'.(($admin)?'Large':'Small').'MapControl());
        				map.addControl(new GMapTypeControl());
        				
        				var mapicon = new GIcon();';
        				if(!$admin)
        				 {
        				 	if($currentopts['ip_maps_icon_url'])
        				 		echo 'mapicon.image = "'.$currentopts['ip_maps_icon'].'";';
        				 	else
								echo 'mapicon.image = "'.$pluginUrl.'/img/pins/'.$icn.'";';
						 }

	echo '				mapicon.infoWindowAnchor = new GPoint(23, 5);
						mapicon.iconSize = new GSize(32, 32);
						mapicon.iconAnchor = new GPoint(16, 32);
								  
						function createMarker(point, msg, hits)
						 {';
						
						if($admin)
						 {
							echo'
							if(hits > 0 && hits < 20)
								mapicon.image = "'.$pluginUrl.'/img/pins/pin_15.png";
							else if(hits > 20 && hits < 80)
								mapicon.image = "'.$pluginUrl.'/img/pins/pin_16.png";
							else if(hits > 80 && hits < 170)
								mapicon.image = "'.$pluginUrl.'/img/pins/pin_17.png";
							else if(hits > 170 && hits < 290)
								mapicon.image = "'.$pluginUrl.'/img/pins/pin_18.png";
							else if(hits > 290 && hits < 440)
								mapicon.image = "'.$pluginUrl.'/img/pins/pin_19.png";
							else if(hits > 440 && hits < 620)
								mapicon.image = "'.$pluginUrl.'/img/pins/pin_20.png";
							else if(hits > 620)
								mapicon.image = "'.$pluginUrl.'/img/pins/pin_21.png";';
						 }
	
		echo '				var newmapIcon = new GIcon(mapicon);
							var ipmarker = new GMarker(point, { icon:newmapIcon });
							if(msg)
								GEvent.addListener(ipmarker, "click", function() {
									ipmarker.openInfoWindowHtml(msg);
								});

							return ipmarker;
						 }
					
						var locations = new Array(); ';

						$cnt = 0;
						if($admin)
						 {
							foreach($parsed_countries as $ip)
							 {
							 	if(preg_match('/[0-9.]/', $ip['latlon']))
							 	 {
							 	 	$flag = ($ip['code']) ? '<img src=\"'.$pluginUrl.'/img/countries/'.strtolower($ip['code']).'.gif\" alt=\"\" /><br />' : '';
									echo 'locations['.$cnt.'] = "<div class=\'hits_by_country\'><strong>'.$ip['name'].'</strong> '.$flag.sprintf(__('%d visits from this country', 'visitos-map-ip'), $ip['hits']).'</div>|@|'. $ip['latlon'] .'|@|'.$ip['hits'].'"; ';
									$cnt++;
								 }
								$nsmg = '';
				 		 	}
				 		 }
				 		else
				 		 {
					 	 	foreach($current_logged_ips as $ip)
							 {
							 	if($msg)
							 		$nmsg = str_replace('%ip_visits%', $ip->hits, $msg);
								if(preg_match('/[0-9.]/', $ip->latlon))
								 {
									echo 'locations['.$cnt.'] = "'. $nmsg .'|@|'. $ip->latlon .'"; ';
									$cnt++;
								 }
								$nsmg = '';
					 	 	 }
					 	 }

		echo "\n" . '	for(var i=0; i<'.$cnt.'; i++)
						 {
						 	var msglatlon = locations[i].split("|@|");
						 	var latlon = msglatlon[1].split(", ");
							var latlng = new GLatLng(latlon[0], latlon[1]);
							var hits = (msglatlon[2]) ? msglatlon[2] : false;

							map.addOverlay(createMarker(latlng, '.(($msg || is_plugin_page()) ? "msglatlon[0]" : "''").', ((hits)?hits:"")));
						 }
    	  			 }
				 }';

				if($admin)
					echo 'window.addEvent(\'load\', function() { visitors_ip_maps_load(); } );';
				else
				 {
				 	echo 'function visitors_ip_maps_addLoadEvent(fn) {
							var old = window.onload;
							if(typeof window.onload != "function") window.onload = fn;
							else window.onload = function() { old(); fn(); }
 		 	 				}
						visitors_ip_maps_addLoadEvent( function() { visitors_ip_maps_load(); } );';
				 }

		echo '	//]]>
    			</script>';
 
 }


function visitors_map_post_map_filter_callback($match)
 {
 	return ip_tracker_draw_map(false, true);
 } 


function visitors_map_post_map_filter($content)
 {
 	$content = preg_replace_callback(array("/\[vm]/","/\[visits]/","/\[visitormap]/"), 'visitors_map_post_map_filter_callback', $content);
 	return $content;
 }

?>