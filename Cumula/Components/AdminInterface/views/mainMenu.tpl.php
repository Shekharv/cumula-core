<?php
function buildMenu($m, $class = '') {
	$output = "<ul class='$class'>";
	foreach($m as $title => $path) {
		if(is_array($path))
			$output .= "<li><span>$title</span><div>".buildMenu($path, 'sub')."</div></li>";
		else
			$output .= "<li><a href='$path'>$title</a></li>";
	}
	$output .= "</ul>";
	return $output;
}

echo buildMenu($menus);