<?php 
	// xmlgraph themes
	global $themes;


	$themes["cappuccino"] = Array(
		"name" => "Cappuccino",
		"font.face" => "Arial",
		"border.width" => 1,
		"border.color" => "#663322",
		"node.font.size" => 14,
		"node.font.color" => "#000000",
		"node.count.color" => "#7c744c",
		"node.bg.color" => "#ccc4ac",
		"node.padding" => 2,
		"attr.font.size" => 12,
		"attr.font.color" => "#000000",
		"attr.count.color" => "#7c744c",
		"attr.bg.color" => "#ece4cc",
		"attr.padding" => 2,
		"val.font.size" => 12,
		"val.font.color" => "#000000",
		"val.count.color" => "#aca47c",
		"val.bg.color" => "#f8f4e0",
		"arrow.size" => 1.4,
		"arrow.color" => "#000000",
		"arrow.head" => "normal",
		"arrow.tail" => "none",
	);


	$themes["gray"]["name"] = "Gray";
	$themes["gray"]["base"] = "cappuccino";
	$themes["gray"]["border.color"] = "#333333";
	$themes["gray"]["node.font.color"] = "#000000";
	$themes["gray"]["node.count.color"] = "#666666";
	$themes["gray"]["node.bg.color"] = "#cccccc";
	$themes["gray"]["attr.font.color"] = "#000000";
	$themes["gray"]["attr.count.color"] = "#777777";
	$themes["gray"]["attr.bg.color"] = "#eeeeee";
	$themes["gray"]["val.font.color"] = "#000000";
	$themes["gray"]["val.count.color"] = "#aaaaaa";
	$themes["gray"]["val.bg.color"] = "#f8f8f8";
	$themes["gray"]["arrow.color"] = "#000000";


	$themes["blue"]["name"] = "Blue";
	$themes["blue"]["base"] = "cappuccino";
	$themes["blue"]["border.color"] = "#000044";
	$themes["blue"]["node.font.color"] = "#000088";
	$themes["blue"]["node.count.color"] = "#000066";
	$themes["blue"]["node.bg.color"] = "#ccccff";
	$themes["blue"]["attr.font.color"] = "#222288";
	$themes["blue"]["attr.count.color"] = "#aaaaff";
	$themes["blue"]["attr.bg.color"] = "#eeeeff";
	$themes["blue"]["val.font.color"] = "#4444cc";
	$themes["blue"]["val.count.color"] = "#ccccff";
	$themes["blue"]["val.bg.color"] = "#f8f8ff";
	$themes["blue"]["arrow.color"] = "#004488";


	$themes["small"]["node.font.size"] = 8;
	$themes["small"]["attr.font.size"] = 8;
	$themes["small"]["val.font.size"] = 8;
	$themes["small"]["arrow.size"] = 0.7;

	$themes["cappuccino.s"]["base"] = "cappuccino,small";
	$themes["cappuccino.s"]["name"] = "Cappuccino Small";
	$themes["gray.s"]["base"] = "gray,small";
	$themes["gray.s"]["name"] = "Gray Small";
	$themes["blue.s"]["base"] = "blue,small";
	$themes["blue.s"]["name"] = "Blue Small";
		
?>
