<?php
	// xmlgraph options
	global $options;
	
	
	// graphviz parameter "resolution"
	
	$options["res"] = Array(
		48 => "48 dpi",
		60 => "60 dpi",
		72 => "72 dpi",
		84 => "84 dpi",
		96 => "96 dpi",
		108 => "108 dpi",
		120 => "120 dpi",
		132 => "132 dpi",
		144 => "144 dpi"
	);
	
	
	// graphviz parameter "rankdir"
	
	$options["dir"] = Array(
		"UD" => "up-to-down",
		"LR" => "left-to-right",
		"RL" => "right-to-left"
	);


	$options["depth"] = Array(
		1 => "current node only",
		2 => "2 levels depth",
		3 => "3 levels depth",
		4 => "4 levels depth",
		5 => "5 levels depth",
		-1 => "full depth"
	);
	
	
	// options for show attribute value
	
	$options["value"] = Array(
		"0" => "hide values",
		"1" => "show recent values",
		"2" => "hide orphan values",
		"3" => "complete value list"
	);	


	// options for show node, attribute and value count
	
	$options["count"] = Array(
		"0" => "hide count",
		"1" => "show count"
	);
	
?>
