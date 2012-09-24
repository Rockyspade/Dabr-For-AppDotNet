<?php
require_once ("common/advert.php");

$current_theme = false;

function theme() 
{
	global $current_theme;
	$args = func_get_args();
	$function = array_shift($args);
	$function = 'theme_'.$function;

	if ($current_theme) {
		$custom_function = $current_theme.'_'.$function;
		if (function_exists($custom_function))
		$function = $custom_function;
	} else {
		if (!function_exists($function))
		return "<p>Error: theme function <b>$function</b> not found.</p>";
	}
	return call_user_func_array($function, $args);
}

function theme_csv($headers, $rows) {
	$out = implode(',', $headers)."\n";
	foreach ($rows as $row) {
		$out .= implode(',', $row)."\n";
	}
	return $out;
}

function theme_list($items, $attributes) {
	if (!is_array($items) || count($items) == 0) {
		return '';
	}
	$output = '<ul'.theme_attributes($attributes).'>';
	foreach ($items as $item) {
		$output .= "<li>$item</li>\n";
	}
	$output .= "</ul>\n";
	return $output;
}

function theme_options($options, $selected = NULL) {
	if (count($options) == 0) return '';
	$output = '';
	foreach($options as $value => $name) {
		if (is_array($name)) {
			$output .= '<optgroup label="'.$value.'">';
			$output .= theme('options', $name, $selected);
			$output .= '</optgroup>';
		} else {
			$output .= '<option value="'.$value.'"'.($selected == $value ? ' selected="selected"' : '').'>'.$name."</option>\n";
		}
	}
	return $output;
}


function theme_radio($options, $name, $selected = NULL) 
{
	if (count($options) == 0) return '';
	$output = '';
	foreach($options as $value => $description) 
	{
		$output .= '<label for="'.$value.'">
						<input 
							type="radio" 
							name="'.$name.'"
							id="'.$value.'" 
							value="'.$value.'" '.($selected == $value ? 'checked="checked"' : '').' />'; 
		$output .= ' ' . $description . '</label>
		<br>';
	}
	return $output;
}

function theme_info($info) {
	$rows = array();
	foreach ($info as $name => $value) {
		$rows[] = array($name, $value);
	}
	return theme('table', array(), $rows);
}

function theme_table($headers, $rows, $attributes = NULL) {
	$out = '<div'.theme_attributes($attributes).'>';
	if (count($headers) > 0) {
		$out .= '<thead><tr>';
		foreach ($headers as $cell) {
			$out .= theme_table_cell($cell, TRUE);
		}
		$out .= '</tr></thead>';
	}
	if (count($rows) > 0) {
		$out .= theme('table_rows', $rows);
	}
	$out .= '</div>';
	return $out;
}

function theme_table_rows($rows) {
	$i = 0;
	foreach ($rows as $row) {
		if ($row['data']) {
			$cells = $row['data'];
			unset($row['data']);
			$attributes = $row;
		} else {
			$cells = $row;
			$attributes = FALSE;
		}
		$attributes['class'] .= ($attributes['class'] ? ' ' : '') . ($i++ %2 ? 'even' : 'odd');
		$out .= '<div'.theme_attributes($attributes).'>';
		foreach ($cells as $cell) {
			$out .= theme_table_cell($cell);
		}
		$out .= "</div>\n";
	}
	return $out;
}

function theme_attributes($attributes) {
	if (!$attributes) return;
	foreach ($attributes as $name => $value) {
		$out .= " $name=\"$value\"";
	}
	return $out;
}

function theme_table_cell($contents, $header = FALSE) {
	$celltype = $header ? 'th' : 'td';
	if (is_array($contents)) {
		$value = $contents['data'];
		unset($contents['data']);
		$attributes = $contents;
	} else {
		$value = $contents;
		$attributes = false;
	}
	return "<span".theme_attributes($attributes).">$value</span>";
}


function theme_error($message) {
	theme_page('Error', $message);
}


function theme_header($title)
{
	switch ($title) {
		case 'muted':
			$header  .= "<h3>" . theme_get_logo(57) . "This is everyone you have muted</h3>";
			break;
		case 'friends':
			$header  .= "<h3>" . theme_get_logo(57) . "Wow! You follow some really interesting folk!</h3>";
			break;
		case 'followers':
			$header  .= "<h3>" . theme_get_logo(57) . "These lovely people hang on your every word</h3>";
			break;
		case 'Search':
			$header  .= "<h3>" . theme_get_logo(57) . "Let's go exploring!</h3>";
			break;
		case 'Starrers':
			$header  .= "<h3>" . theme_get_logo(57) . "People who starred that post</h3>";
			break;
		case 'Reposters':
			$header  .= "<h3>" . theme_get_logo(57) . "They came, they saw, they reposted</h3>";
			break;
		default :
			$header ="";
	}

	if (strpos($title, "Posts Starred") !== false)
	{
		$header  .= "<h3>" . theme_get_logo(57) . "OMG! It's full of stars!</h3>";
	}

	return $header;
		
}

function theme_page($title, $content) {
	$body = theme('menu_top');
	$body .= theme_header($title)  . "<section>" . $content . "</section>";
	$body .= theme('menu_bottom');
	$body .= theme('google_analytics');
	if (DEBUG_MODE == 'ON') 
	{
		global $dabr_start, $api_time, $services_time, $rate_limit;
		$time = microtime(1) - $dabr_start;
		$body .= '<p>
					Processed in '.
					round($time, 4).' seconds ('.
					round(($time - $api_time - $services_time) / $time * 100).'% Dabr, '.
					round($api_time / $time * 100).'% app.net API, '.
					round($services_time / $time * 100).'% other services). '.
					$rate_limit.
				'.</p>';
	}
	if ($title == 'Login') 
	{
		$title = 'Dabr - mobile app.net Login';
		$meta = '<meta name="description" content="Free open source alternative to mobile App.net, bringing you the complete AppDotNet experience to your phone." />';
	}
	ob_start('ob_gzhandler');
	header('Content-Type: text/html; charset=utf-8');
	echo	'<!DOCTYPE html>
				<html>
					<head>
						<meta charset="utf-8" />
						<meta name="viewport" content="width=device-width; initial-scale=1.0;" />
						<title>Dabr - ' . $title . '</title>
						<base href="',BASE_URL,'" />
						<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
						<link rel="apple-touch-icon" href="images/dabr-57.png" />
						<link rel="apple-touch-icon" sizes="72x72" href="images/dabr-72.png" />
						<link rel="apple-touch-icon" sizes="114x114" href="images/dabr-114.png" />
						'.$meta.theme('css').'
					</head>
					<body>';
	//echo 				"<div id=\"advert\">" . show_advert() . "</div>";
	echo 				$body;
	if (setting_fetch('colours') == null)
	{
		//	If the cookies haven't been set, remind the user that they can set how Dabr looks
		echo			'<p>Think Dabr looks ugly? <a href="settings">Change the colours!</a></p>';
	}
	echo 			'</body>
				</html>';
	exit();
}

function theme_colours() {
	$info = $GLOBALS['colour_schemes'][setting_fetch('colours', 0)];
	list($name, $bits) = explode('|', $info);
	$colours = explode(',', $bits);
	return (object) array(
		'links'		=> $colours[0],
		'bodybg'	=> $colours[1],
		'bodyt'		=> $colours[2],
		'small'		=> $colours[3],
		'odd'		=> $colours[4],
		'even'		=> $colours[5],
		'replyodd'	=> $colours[6],
		'replyeven'	=> $colours[7],
		'menubg'	=> $colours[8],
		'menut'		=> $colours[9],
		'menua'		=> $colours[10],
	);
}

function theme_css() {
	$c = theme('colours');
	return "
	<style type='text/css'>
		nav{}
		a{color:#{$c->links}}
		small,small a{color:#{$c->small}}
		body
		{	
			background:#{$c->bodybg};
			color:#{$c->bodyt};
			margin:0px;
			font:90% sans-serif
		}

		section {
			clear: both; }

		.odd{background:#{$c->odd}}
		.even{background:#{$c->even}}
		.reply{background:#{$c->replyodd}}
		.reply.even{background: #{$c->replyeven}}
		.tweet
		{
			padding:.5em;
			min-height:50px;
		}
		
		.features
		{
			padding:.5em;
			min-height:25px;
		}

		.date
		{
			padding:.5em;
			font-size:0.8em;
			font-weight:bold;
			color:#{$c->small}
		}
		.about,.time
		{
			font-size:0.75em;
			color:#{$c->small}
		}
		.avatar
		{
			height:auto; 
			width:auto;
			float:left;
			margin-right: 10px; 
		}
		.embed
		{
			left:0px; 
			display:block;
		}
		.embeded
		{
			left:0px; 
		}
		.status{
			word-wrap:break-word;
		}
		nav ul {
			padding: 0; 
			overflow: auto;
		}

		nav li {
			list-style: none;
			position: relative;
			display: block;
			float: left;
			margin: 0px 0 0 0;
			background:#{$c->menubg};
		}

		nav a {
			display: block; 
			padding: 0.5em; 
			text-decoration: none; 
			border-right: 1px solid #fff; 
			font-size: 110%; 
			color:#{$c->menua};
		}

		nav .current {
			font-weight: bold; 
		}

		.prio-beta,
		.prio-gamma,
		.show-nav-less {
			display: none; 
		}

		#prio:target .prio-beta,
		#prio:target .prio-gamma,
		#prio:target .show-nav-less {
			display: block; 
		}

		#prio:target .show-nav-more {
			display: none; 
		}

		@media all and (min-width: 31em) {
		.prio-beta {
			display: block; } 
		}

		@media all and (min-width: 70em) {
			.prio-gamma {
				display: block; }

			.show-nav-more,
			#prio:target .show-nav-less {
				display: none; } 
		}
		
		.logo{float:left;margin-right:15px;}
	</style>";
}

function theme_google_analytics() {
	global $GA_ACCOUNT;
	if (!$GA_ACCOUNT) return '';
	$googleAnalyticsImageUrl = googleAnalyticsGetImageUrl();
	return "<img src='{$googleAnalyticsImageUrl}' />";
}

function theme_get_logo($size = 128)
{
	return '<img src="images/dabr-'.$size.'.png" height="'.$size.'" width="'.$size.'" alt="The Dabr Bunny" class="logo" />';
}