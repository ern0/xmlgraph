<?php
	// xmlgraph/xmlgraph.php by ern0@linkbroker.hu
	// created: 2009.02.05
	// lmdate: 2009.08.24


	require("engine/library.php");
	require("engine/graphviz.php");
	require("engine/templater.php");
	require("design/themes.php");
	require("design/options.php");
	umask(0);

	function main() {
		global $theme;

		$theme = constructTheme($_GET["theme"]);
		if (sizeof($_FILES["file"])) {
			procUpload();
		} else if (strlen($_GET["img"])) {
			paintImage();
		} else if (strlen($_GET["txt"])) {
			paintXml();
		} else {
			paintPage();
		}

	} // main()


	function paintXml() {
		global $theme;

		$x = loadXmlText($_GET["txt"]);
		$x = str_replace("<","&lt;",$x);
		$x = str_replace(">","&gt;",$x);
		$x = str_replace("\t","&nbsp;&nbsp;",$x);

		echo("<pre>");
		echo("<font color=\"" . $theme["node.font.color"] . "\">");
		echo("$x</pre>");

	} // paintXml()


	function constructTheme($themeIndex) {
		global $themes;

		if (!strlen($themeIndex)) $themeIndex = "n.a.";
		if (!is_array($themes[$themeIndex])) foreach ($themes as $themeIndex => $nil) break;

		$effectiveTheme = $themes[$themeIndex];

		if (strlen($effectiveTheme["base"])) {
			$baseThemeIndexes = explode(",",$effectiveTheme["base"]);

			foreach ($baseThemeIndexes as $baseThemeIndex) {
				foreach ($themes[$baseThemeIndex] as $var => $val) {
					$r[$var] = $val;
				} // foreach theme var
			} // foreach base

			foreach ($effectiveTheme as $var => $val) {
				$r[$var] = $val;
			} // foreach effective theme

		} else {
			$r = $effectiveTheme;
		} // if base

		return $r;
	} // constructTheme()


	function loadXmlText($f) {

		if (substr($f,0,1) == "/") $f = substr($f,1);
		$text = @join("",@file($f));
		if (!strlen($text)) return loadXmlText("/about.xml");

		$text = utf8_encode($text);
		// this hack is required by simple_xml, dirty but works
		$text = str_replace("&amp;","&amp;amp;",$text);

		return $text;
	} // loadXmlText()


	function paintImage() {
		global $xml;
		global $theme;

		$text = loadXmlText($_GET["img"]);

		if (!strlen($text)) {
			$x = "digraph x {";
			foreach (Array("file","depth","count","value","theme","dir","res") as $spec) {
				$x .= " specify->" . $spec;
			}
			paintDotImage($x,"png","error");
			die();
		} // if no file

		procNode(simplexml_load_string($text));

		$r = template("main","design/template.dot");
		$r = str_replace("#FONT",$theme["font.face"],$r);

		$dir = $_GET["dir"];
		if (!strlen($dir)) $dir = "LR";
		$r = str_replace("#RANKDIR",$dir,$r);

		$res = $_GET["res"];
		if (!strlen($res)) $res = 96;
		$r = str_replace("#RESOLUTION",$res,$r);

		$node = $_GET["node"];
		if (!strlen($node)) foreach ($xml["dom"] as $node => $x) break;
		$node = $xml["nam"][$node];

		$depth = $_GET["depth"];
		if (!strlen($depth)) $depth = -1;

		$x = paintNode($node,$depth);
		$r = str_replace("#STUFF",$x,$r);

		$name = $_GET["img"];
		$name = explode("/",$name);
		$name = $name[sizeof($name) - 1];
		$name = explode(".",$name);
		unset($name[sizeof($name) - 1]);
		$name = join(".",$name);

		paintDotImage($r,"png",$name);

	} // paintImage()


	function procNode($node,$parentPath = "") {
		global $xml;

		$name = $node->getName();
		$name = str_replace("-","_",$name);
		if ($parentPath == "") {
			$path = $name;
		} else {
			$path = $parentPath . "__" . $name;
		}

		for ($n = 1; $n < 42; $n++) {
			if ($n == 1) {
				$uniqueName = $name;
			} else {
				$uniqueName = $name . $n;
			}

			if ( is_array($xml["dom"][$path]) ) break;
			if ( !strlen($xml["nam"][$uniqueName]) ) {
				$xml["nam"][$uniqueName] = $path;
				break;
			}
		} // for unique name

		$xml["dom"][$path]["u"] = $uniqueName;
		$xml["dom"][$path]["i"] = $name;
		$xml["dom"][$path]["n"]++;

		unset($a);
		foreach ($node->attributes() as $attrName => $attrValue) {
			$a[] = $attrName . "^" . $attrValue;
		}
		krsort($a);

		foreach ($a as $x) {
			$x = explode("^",$x);
			$attrName = $x[0];
			$attrValue = $x[1];
			$xml["dom"][$path]["a"][$attrName]++;
			$xml["dom"][$path]["v"][$attrName]["!" . $attrValue]++;
			$n++;
		} // foreach attributes

		$attrValue = trim((string)$node);
		if (strlen($attrValue)) {
			$attrName = "(text)";
			$xml["dom"][$path]["a"][$attrName]++;

			$x = str_replace("\r","",$attrValue);
			$x = str_replace("\n"," ",$x);
			$x = str_replace(", ","",$x);
			$x = str_replace("; ","",$x);
			$x = str_replace(". ","",$x);
			$x = str_replace("- ","",$x);
			$x = str_replace(" -","",$x);
			$x = str_replace("+ ","",$x);
			$x = str_replace(" +	","",$x);

			$x = explode(" ",$x);
			if ( (sizeof($x) > 2) && (strlen($attrValue) > 12) ) {
				$xml["dom"][$path]["v"][$attrName]["#"]++;
			} else {
				$xml["dom"][$path]["v"][$attrName]["@" . $attrValue]++;
			}
		} // if text node

		foreach ($node->children() as $subNode) {
			$subName = $subNode->getName();
			$xml["dom"][$path]["s"][$subName]++;
			procNode($subNode,$path);
		} // foreach subnodes

	} // procNode()


	function paintNode($path,$depth) {
		global $xml;
		global $theme;

		if ($depth == 0) return;

		$node = &$xml["dom"][$path];

		if ($_GET["count"]) {
			$x = template("nodewnum");
			$x = str_replace("#NUM",$node["n"],$x);
			$x = str_replace("#COUNTCOLOR",$theme["node.count.color"],$x);
		} else {
			$x = template("nodewonum");
		}
		$x = str_replace("#NAME",$node["i"],$x);
		$x = str_replace("#FONTSIZE",$theme["node.font.size"],$x);
		$x = str_replace("#NODECOLOR",$theme["node.font.color"],$x);

		$r = template("node");
		$r = str_replace("#PATH",$path,$r);
		$r = str_replace("#NODEFORMAT",$x,$r);
		$r = str_replace("#BGCOLOR",$theme["node.bg.color"],$r);
		$r = str_replace("#PADDING",$theme["node.padding"],$r);


		// hide text "attribute" if no values shown
		if (!$_GET["value"]) {
			unset($node["a"]["(text)"]);
			unset($node["v"]["(text)"]);
		}	 // if hide text "attrib"


		// render attributes and values
		if (sizeof($node["a"])) {

			arsort($node["a"]);

			$a = "";
			foreach ($node["a"] as $attr => $attrCount) {

				$a .= trim(template("attrib"));

				if ($_GET["count"]) {
					$x = template("attribwnum");
					$x = str_replace("#NUM",$attrCount,$x);
					$x = str_replace("#COUNTCOLOR",$theme["attr.count.color"],$x);
				} else {
					$x = template("attribwonum");
				}

				$x = str_replace("#ATTRIB",$attr,$x);
				$x = str_replace("#FONTSIZE",$theme["attr.font.size"],$x);
				$x = str_replace("#BGCOLOR",$theme["attr.bg.color"],$x);
				$x = str_replace("#ATTRCOLOR",$theme["attr.font.color"],$x);

				// construct v, the dot code of the value
				do {
					$v = "";

					if (!$_GET["value"]) break;  // hide all values
					if (!sizeof($node["v"][$attr])) break;

					arsort($node["v"][$attr]);

					$valuesToHide = Array();
					if ($_GET["value"] == 2) {  // hide orphan values
						$valueLimit = 1;
 					} else {  // 1: show recent values
 						$valueLimit = 4;
 					}

 					// create array of values to hide (valuesToHide)
					do {
						if ($_GET["value"] == 3) break;  // complete value list
						if ($attrCount == 1) break;  // all values are recent
						if (sizeof($node["v"][$attr]) <= 5) break;  // show all, if the list is short

						foreach ($node["v"][$attr] as $value => $valueCount) {
							if ($valueCount <= $valueLimit) $valuesToHide[$value] = 1;
						}

						// show non-recent and orphan values, if there are only less than 3 of them
						// worst cases:
						//  - 4*2 extra lines of non-recent in recent-only mode
						//  - 2 extra lines of orphans in hide-orphans mode
						if (sizeof($valuesToHide) < 3) $valuesToHide = Array();

						if (sizeof($valuesToHide)) {
							$noOfValuesToHide = 0;
							foreach ($valuesToHide as $value => $nil) {
								$noOfValuesToHide += $node["v"][$attr][$value];
								unset($node["v"][$attr][$value]);
							}
							$node["v"][$attr]["%"] = $noOfValuesToHide;
						} // if values to hide

					} while (false); // do values to hide

					foreach ($node["v"][$attr] as $value => $valueCount) {

						if ($_GET["count"]) {
							$v .= trim(template("valuewnum"));
						} else {
							$v .= trim(template("valuewonum"));
						}

						switch (substr($value,0,1)) {
						case "!":  // attrib value
							$valueTemplate = "valueattr";
							break;
						case "@":  // short text
							$valueTemplate = "valuetext";

							// shorten and append "..." to value, but avoid "." + "..."
							if (strlen($value) > 20) {
								$value = substr($value,0,18) . "[.DOTZ.]";
								$value = str_replace(".[.DOTZ.]","[.DOTZ.]",$value);
								$value = str_replace("[.DOTZ.]","...",$value);
							} // if shorten

							break;
						case "#":  // long text
							$valueTemplate = "valuelong";
							break;
						case "%":  // hidden items
							$valueTemplate = "valuehidden";
							break;
						} // switch value type

						$f = trim(template($valueTemplate));
						$f = str_replace("#VALUE",substr($value,1),$f);
						$v = str_replace("#VALUE",$f,$v);
						$v = str_replace("#NODE",$node["i"],$v);

						$v = str_replace("#NUM",$valueCount,$v);
						$v = str_replace("#DARKCOLOR",$theme["attr.bg.color"],$v);
						$v = str_replace("#BGCOLOR",$theme["val.bg.color"],$v);
						$v = str_replace("#FONTSIZE",$theme["val.font.size"],$v);
						$v = str_replace("#VALCOLOR",$theme["val.font.color"],$v);
						$v = str_replace("#COUNTCOLOR",$theme["val.count.color"],$v);

					} // foreach values
				} while (false);  // do set v

				if (strlen($v)) {
					$a = str_replace(
						"#VALUES",
						str_replace("#VALUES",$v,template("values")),
						$a
					);
				} else {
					$a = str_replace("#VALUES","",$a);
				}

				$a = str_replace("#ATTRIBFORMAT",$x,$a);

			} // foreach attrib

			$r = str_replace(
				"#ATTRIBS",
				str_replace("#ATTRIBS",$a,template("attribs")),
				$r
			);

		} else {

			// do not insert attrs/values (skip frame template)
			$r = str_replace("#ATTRIBS","",$r);

		} // if attribs

		$r = str_replace("#BGCOLOR",$theme["attr.bg.color"],$r);
		$r = str_replace("#PADDING",$theme["attr.padding"],$r);

		if (is_array($node["s"]) && ($depth != 1)) {

			foreach ($node["s"] as $sub => $count) {
				$subPath = $path . "__" . $sub;
				$r .= paintNode($subPath,$depth - 1);
				$x = template("edge");
				$x = str_replace("#ARROWSIZE",$theme["arrow.size"],$x);
				$x = str_replace("#ARROWCOLOR",$theme["arrow.color"],$x);
				$x = str_replace("#ARROWHEAD",$theme["arrow.head"],$x);
				$x = str_replace("#ARROWTAIL",$theme["arrow.tail"],$x);
				$x = str_replace("#A",$path,$x);
				$x = str_replace("#B",$subPath,$x);
				$r .= $x;
			} // foreach sub

		} // if sub

		return $r;
	} // paintNode()


	function getCookie($name = "xmlgraph") {

		$c = $_COOKIE[$name];
		if (strlen($c)) return $c;

		$c = date("YmdHis_") . rand(1000,9999);
		setcookie($name,$c,time() + (3600 * 24 * 365) );

		return $c;
	} // getCookie()


	function scanFolder(&$opt,$folder) {

		$folder .= "/";
		$folder = str_replace("//","/",$folder);

		if (!is_dir($folder)) return;

		$h = opendir($folder);
		while (true) {
			$file = readdir($h);
			if ($file == false) break;
			$x = str_replace(".","",$file);
			if (!strlen($x)) continue;

			$opt[$folder . $file] = $file;
		} // while
		closedir($h);

	} // scanFolder()


	function paintPage() {
		global $xml;
		global $themes;
		global $theme;
		global $options;


		$text = loadXmlText($_GET["file"]);
		procNode(simplexml_load_string($text));


		// paint theme (css)

		$r = template("main","design/frontend.html");

		$r = str_replace("#FONT",$theme["font.face"],$r);
		$r = str_replace("#BG",$theme["node.bg.color"],$r);
		$r = str_replace("#FG",$theme["node.font.color"],$r);
		$r = str_replace("#BORDER",$theme["border.color"],$r);
		$r = str_replace("#BW",$theme["border.width"],$r);


		// starting node

		$opts["node"] = $_GET["node"];
		if (!strlen($opts["node"])) foreach ($xml["dom"] as $opts["node"] => $nil) break;
		foreach ($xml["nam"] as $uniqueName => $path) {
			$x = explode("__",$path);
			for ($n = 0; $n < sizeof($x) - 1; $n++) $x[$n] = "&nbsp;&nbsp;";
			$title = join("",$x);
			if (sizeof($x) == 1) {
				$t = template("pulldownroot");
			} else {
				$t = template("pulldownsub");
			}
			$t = str_replace("#TITLE",$title,$t);
			$options["node"][$uniqueName] = trim($t);
		} // foreach fill options

		// default options (and default xml)

		$opts["file"] = $_GET["file"];
		if (strlen($opts["file"])) {

			// default options
			$opts["theme"] = $_GET["theme"];
			if (!is_array($themes[$opts["theme"]])) foreach ($themes as $actual => $nil) break;
			$opts["res"] = $_GET["res"];
			if (!strlen($opts["res"])) $opts["res"] = 96;
			$opts["dir"] = $_GET["dir"];
			if (!strlen($opts["dir"])) $opts["dir"] = "UD";
			$opts["value"] = $_GET["value"];
			if (!strlen($opts["value"])) $opts["value"] = 0;
			$opts["count"] = $_GET["count"];
			if (!strlen($opts["count"])) $opts["dir"] = 0;
			$opts["depth"] = $_GET["depth"];
			if (!strlen($opts["depth"])) $opts["level"] = -1;

		} else {

			// about.xml options
			$opts["file"] = "about.xml";
			$opts["theme"] = "cappuccino";
			$opts["res"] = 72;
			$opts["dir"] = "LR";
			$opts["value"] = 3;
			$opts["count"] = 0;
			$opts["depth"] = -1;

		} // if file specified


		// load file list

		$options["file"]["/about.xml"] = "&lt; about xmlgraph &gt;";
		scanFolder($options["file"],"xml/public");
		scanFolder($options["file"],"xml/private_" . getCookie());

		$r = str_replace("#FILEPARM",$opts["file"],$r);

		// horizontal bar position

		$hpos = $_GET["bh"];
		if (!strlen($hpos)) $hpos = "r";
		$r = str_replace("#HPOS",$hpos,$r);
		$opts["bh"] = $hpos;

		$vpos = $_GET["bv"];
		if (!strlen($vpos)) $vpos = "b";
		$r = str_replace("#VPOS",$vpos,$r);
		$opts["bv"] = $vpos;


		// theme pulldown

		$actual = $_GET["theme"];
		if (!is_array($themes[$actual])) foreach ($themes as $actual => $nil) break;
		$opts["theme"] = $actual;

		$x = "";
		foreach ($themes as $id => $tx) {
			if (!strlen($tx["name"])) continue;

			if ($opts["theme"] == $id) {
				$x .= template("themeselected");
			} else {
				$x .= template("themeoption");
			}
			$t = constructTheme($id);
			$x = str_replace("#VALUE",$id,$x);
			$x = str_replace("#TITLE",$t["name"],$x);
			$x = str_replace("#BG",$t["attr.bg.color"],$x);
			$x = str_replace("#FG",$t["attr.font.color"],$x);
		} // foreach themes
		$r = str_replace("#THEMES",$x,$r);


		// counstruct pulldowns

		foreach (Array("file","node","depth","res","dir","value","count") as $item) {

			$x = "";
			foreach ($options[$item] as $opt => $title) {
				if ($opt == $opts[$item]) {
					$x .= template("selected");
				} else {
					$x .= template("option");
				}
				$x = str_replace("#VALUE",$opt,$x);
				$x = str_replace("#TITLE",$title,$x);
			} // forech opt
			$r = str_replace("#" . strtoupper($item),$x,$r);

		} // foreach item


		// consruct URL

		$x = "";
		foreach ($opts as $item => $opt) {
			$x .= "&" . $item . "=" . $opt;
		} // foreach options
		$r = str_replace("#IMGPARMS",$x,$r);
		$r = str_replace("#THEMEPARM","&theme=" . $opts["theme"],$r);

		// finally, paint the page

		htmlHeader();
		echo($r);

	}	// paintPage()


	function procUpload() {

		$tmp = $_FILES["file"]["tmp_name"];
		$fnam = basename($_FILES["file"]["name"]);
		$fnam = str_replace(".php",".xml",$fnam);
		$path = "xml/private_" . getCookie();
		mkdir($path,0777);
		$full = $path . "/" . $fnam;
		move_uploaded_file($tmp,$full);

		$x = $_SERVER["REQUEST_URI"];
		$x = str_replace("php?","php&",$x);
		$x = explode("&",$x);

		for ($n = 1; $n < sizeof($x); $n++) {
			$a = explode("=",$x[$n]);
			if ($a[0] == "file") unset($x[$n]);
			if ($a[0] == "node") unset($x[$n]);
			if ($a[0] == "bh") unset($x[$n]);
			if ($a[0] == "bv") unset($x[$n]);
		} // for url

		$x[] = "file=" . $full;
		$x = join("&",$x);
		$x = str_replace(".php&",".php?",$x);
		$x .= "&bh=" . $_POST["bh"];
		$x .= "&bv=" . $_POST["bv"];
		$x = "http://" . $_SERVER["SERVER_NAME"] . $x;

		header("Location: " . $x);
		die();
	} // procUpload()
?>
