<?php
	// xmlgraph/library.php by by ern0@bitklub.hu
  // lmdate: 2009.02.12


	srand((double)microtime() * 4200055);


	function sqlConnect($config) {
		global $sqlConfig;

		if (!strlen($config)) $config = $sqlConfig["!"];
		if (!is_array($sqlConfig[$config]	)) foreach ($sqlConfig as $config => $x) break;

		$sqlConfig[$config]["handle"] = mysql_connect($sqlConfig[$config]["host"],$sqlConfig[$config]["user"],$sqlConfig[$config]["password"]);

		if (!$sqlConfig[$config]["handle"]) {
	  	$error = "SQL ERROR: " . mysql_error() . "<br/><br/>\n";
			echo($error);

			$sqlConfig[$config]["password"] = "****";
			pri($sqlConfig[$config]);
		}
		
		performSqlStatement("set names utf8",$config);
		
		$sqlConfig["!"] = $config;
		
	} // sqlConnect()


	function sqlClose($config = "") {
		global $sqlConfig;

		if (!strlen($config)) $config = $sqlConfig["!"];
		if (!is_array($sqlConfig[$config]	)) foreach ($sqlConfig as $config => $x) break;

		if (!$sqlConfig[$config]["handle"]) {
			$error = "SQL ERROR: " . mysql_error() . "\n";
			echo($error);
		}

		$sqlConfig["!"] = $config;

	} // sqlClose()


	function performSqlStatement($select,$config = "") {
		global $sqlConfig;

		if (!strlen($config)) $config = $sqlConfig["!"];
		if (!is_array($sqlConfig[$config]	)) foreach ($sqlConfig as $config => $x) break;

		if (is_array($select)) {
			$sqlQuery  = "SELECT " . $select["select"];
			if (!empty($select["from"])) $sqlQuery .= " FROM " . $select["from"];
			if (!empty($select["where"])) $sqlQuery .= " WHERE " . $select["where"];
			if (!empty($select["group by"])) $sqlQuery .= " GROUP BY " . $select["group by"];
			if (!empty($select["group"])) $sqlQuery .= " GROUP BY " . $select["group"];
			if (!empty($select["order by"])) $sqlQuery .= " ORDER BY " . $select["order by"];
			if (!empty($select["order"])) $sqlQuery .= " ORDER BY " . $select["order"];
			if (!empty($select["limit"])) $sqlQuery .= " LIMIT " . $select["limit"];
		} else {
			$sqlQuery = $select;
		}
		
		$sqlQuery = str_replace("#_",$sqlConfig[$config]["prefix"],$sqlQuery);

		$sqlResult = mysql_db_query($sqlConfig[$config]["database"],$sqlQuery,$sqlConfig[$config]["handle"]);

		if(!$sqlResult) {
			if (substr($sqlQuery,0,1) == " ") return;  // silent
	    $error = "SQL ERROR: " . mysql_error() . "\n";
			echo($error . "<br><br>" . $sqlQuery);
			$sqlConfig[$config]["password"] = "****";
			pri($sqlConfig[$config]);
			exit();
		}

		$list = array();
		while ($row = @mysql_fetch_array($sqlResult)) $list[] = $row;

		$sqlConfig["!"] = $config;

		return $list;
	} // performSqlStatement()


	function pri($x,$tab = "",$supr = 0,$notab = 0) {  // tmp debug funct
	  $tab2 = $tab . "&nbsp;&nbsp;";
		if (is_object($x)) $x = $x->toString();
		if (is_array($x)) {
		  pri("Array(",$tab,0,1);
			foreach ($x as $index => $data) {
				pri('"' . $index . '" => ',$tab2,1);
				if (is_object($data)) $data = $data->toString();
				if (is_array($data)) {
				  pri($data,$tab2);
				} else {
					$data = trim($data);
				  pri('"' . $data . '"',$tab2,1,1);
				}
			  pri(",");
			}
			pri(")",$tab,1,0);
			if ($tab == "") pri(";","",0);
		} else {
		  $x = plain2html($x);
			$x = str_replace(" ","&nbsp;",$x);
			$f = "<font color=\"#006633\" face=\"Fixedsys\" size=\"1\">";
			echo($f);
			if (!$notab) {
				echo($tab);
			}
			$x = str_replace("<br>","<br>" . $tab ,$x);
			if ($notab) echo("<font color=\"#330066\">");
			echo($x);
			if (!$supr) {
			  echo("<br>\n");
			}
		}
	} // pri()


	function remx($x) {
		$x = explode("'",$x);

		$inside = 0;
		$r = "";
		foreach ($x as $part) {
			if ($inside) {
				$r .= "'*'";
				$inside = 0;
			} else {
				$r .= $part;
				$inside = 1;
			}
		}
		return $r;
	} // remx()


	function redirect($x,$color,$hard) {
		global $params;

		if ($rnd) {
			if (strstr($x,'?')) {
				$x .= '&';
			} else {
				$x .= '?';
			}
			$x .= 'rnd=' . date("Ymdhis") . rand(99999);
		}


		if ($hard) {
			header("Location: $x");
		} else {
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			echo('<html><head><meta http-equiv="refresh" content="0;URL=');
			echo($x);
			echo('"></head><body bgcolor="$color"></body></html>');
		}
		exit();

	} // redirect()

	function txtHeader() {
		header("Pragma: public"); // required
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private",false); // required for certain browsers
//		header("Content-Type: text/html");
		header('Content-Type: text/html; charset=utf-8');
	} // txtHeader()


	function htmlHeader() {
		header("Content-type: text/html;charset=UTF-8");
	} // htmlHeader()


	function noCacheHeader() {
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	} // noCacheHeader()


	function wapHeader() {
		header("Content-type: text/vnd.wap.wml");
		echo('<' . '?xml version="1.0"?' . '>' . "\r\n");
		echo('<!DOCTYPE wml PUBLIC "-//WAPFORUM//DTD WML 1.1//EN" "http://www.wapforum.org/DTD/wml_1.1.xml">' . "\r\n");
	} // wapHeader()


	function numf($x) {
		if (strlen($x) > 3) {
			$p = strlen($x) - 3;
			$x = substr($x,0,$p) . "&nbsp;" . substr($x,$p,3);
		}
		if ($x == 0) $x = "-";
		return $x;
	} // numf()


	function dform($x,$supr = 0) {
		$x = str_replace("-",".",$x);
		$x = str_replace("/",".",$x);
		if ($supr == 1) {
			$x = str_replace(" 00:00:00","",$x);
		}
		if ($supr == 2) {
			$x = str_replace(" 23:59:59","",$x);
		}
		$x = substr($x,0,16);
		return $x;
	} // $dform


	function haircut($x) {
		$x = strtolower($x);
		$x = str_replace("á","a",$x);
		$x = str_replace("Á","a",$x);
		$x = str_replace("é","e",$x);
		$x = str_replace("ë","e",$x);
		$x = str_replace("É","e",$x);
		$x = str_replace("í","i",$x);
		$x = str_replace("Í","i",$x);
		$x = str_replace("ó","o",$x);
		$x = str_replace("Ó","o",$x);
		$x = str_replace("ö","o",$x);
		$x = str_replace("Ö","o",$x);
		$x = str_replace("õ","o",$x);
		$x = str_replace("Õ","o",$x);
		$x = str_replace("ú","u",$x);
		$x = str_replace("Ú","u",$x);
		$x = str_replace("ü","u",$x);
		$x = str_replace("Ü","u",$x);
		$x = str_replace("û","u",$x);
		$x = str_replace("Û","u",$x);

		return $x;
	} // haircut()


	function ftconv($x) {
		$x = str_replace("\r","",$x);
		$x = str_replace("'","",$x);
		$x = str_replace('"',"",$x);
		$x = str_replace("!","",$x);
		$x = str_replace(".","",$x);
		$x = str_replace(",","",$x);
		$x = str_replace("{","",$x);
		$x = str_replace("}","",$x);
		$x = str_replace("(","",$x);
		$x = str_replace(")","",$x);
		$x = str_replace("[","",$x);
		$x = str_replace("]","",$x);
		$x = str_replace("\\","",$x);
		$x = str_replace("%","",$x);

		$x = str_replace("+"," ",$x);
//		$x = str_replace("_"," ",$x);
		$x = str_replace(";"," ",$x);
		$x = str_replace(","," ",$x);
		$x = str_replace("\n"," ",$x);
		$x = str_replace(" - "," ",$x);

		$x = haircut($x);

		return $x;
	} // ftconv()


	function plain2html($txt) {

		$txt = str_replace("\r","",$txt);
		$txt = str_replace("&","&amp;",$txt);
		$txt = str_replace("<","&lt;",$txt);
		$txt = str_replace(">","&gt;",$txt);
		$txt = str_replace("{","<b>",$txt);
		$txt = str_replace("}","</b>",$txt);
//		$txt = str_replace("_","&nbsp;",$txt);
		$txt = str_replace("\n","<br>",$txt);
		$txt = str_replace('\\"','"',$txt);

		return $txt;
	} // plain2html()


	function esc($x) {
		$x = str_replace("\\","\\\\",$x);
		$x = str_replace('"','\\"',$x);
		return $x;
	} // esc()


	function trickyPercent($done,$all,$ok,$test) {

		if ($all == 0) return "n.a.";
		if ($all < $done) return $test;
		if ($all == $done) return $ok;

		$x = 1 + (98 * $done / $all);
		$x = round($x);

		return $x . "%";
	} // trickyPercent()



	function nudeDate($date = "",$cut = 0)	{
		if (($date == "") && (!$cut)) $date = date("Ymdhis");
		if (!$cut) $date .= "00000000000000";
		$date = str_replace(" ","",$date);
		$date = str_replace(".","",$date);
		$date = str_replace(",","",$date);
		$date = str_replace(";","",$date);
		$date = str_replace(":","",$date);
		$date = str_replace("-","",$date);
		$date = str_replace("/","",$date);
		$date = str_replace("(","",$date);
		$date = str_replace(")","",$date);
		$date = substr($date,0,14);
		return $date;
	} // nudeDate()


	function lyrics($f) {
		global $lyrics;

		$lyrics["x"] = $f;

	  $fd = fopen($f,"r");
	  $x = fread($fd,filesize($f));
	  fclose($fd);

		$x = explode("[",$x);
		foreach ($x as $y) {
			$a = explode("]",$y);
			if (!strlen($a[0])) continue;
			$txt = $a[1];

			$txt = str_replace("\r","",$txt);
			if (substr($txt,0,1) == "\n") {
				$txt = substr($txt,1);
			}
			while (1) {
				if (substr($txt,strlen($txt) -1) == "\n") {
					$txt = substr($txt,0,strlen($txt) - 1);
				} else {
					break;
				}
			} // while trim

			$lyrics[$a[0]] = trim($txt);
		} // foreach txt

	} // lyrics()


	function lyr($x,$y = "") {
		global $lyrics;

		$r = $lyrics[$x];

		if (strchr($r,'*')) return "";

		if (!strlen($r)) {
			$r = "[" . $lyrics["x"] ."::$x]";
		}
		$r = str_replace("#",$y,$r);
		return $r;
	} // lyr()


	function maxWordLen($text) {

		$text = trim($text);

		$text = str_replace("&","^&",$text);
		$text = str_replace(";",";^",$text);

		$parts = explode("^",$text);
		for ($n = 0; $n < sizeof($parts); $n++) {
			if (substr($parts[$n],0,1) == "&") $parts[$n] = "x";
		}
		$text = join("",$parts);

		$result = 0;
		$words = explode(" ",$text);
		foreach ($words as $word) {
			if (strlen($word) > $result) $result = strlen($word);
		}

		return $result;
	} // maxWordLen()


	function parseSectFile($fnam) {
		$ini = Array();

		$file = join("",file($fnam));
		$file = str_replace("\r","",$file);

		$sections = explode("[",$file);
		foreach ($sections as $section) {
			$x = explode("]",$section);
			$snam = $x[0];
			if (!strlen($snam)) continue;
			$ini[$snam] = trim($x[1]);
		} // foreach sections

		return $ini;
	} // parseSectFile()


	function fillArrayGaps($x) {
		$n = 0;
		foreach ($x as $elem) {
			$r[$n] = $elem;
			$n++;
		} // foreach elem

		return $r;
	} // fillArrayGaps()


	function parseIniFile($fnam) {
		$ini = Array();

		$file = join("",file($fnam));
		$file = str_replace("\r","",$file);

		$sections = explode("[",$file);
		foreach ($sections as $section) {
			$x = explode("]",$section);
			$snam = $x[0];
			if (!strlen($snam)) continue;

			$lines = explode("\n",$x[1]);
			foreach ($lines as $line) {
				if (!strlen($line)) continue;
				$y = explode("=",$line);
				$var = trim($y[0]);
				unset($y[0]);
//				$val = trim(join("=",$y));
				$val = join("=",$y);
				$ini[$snam][$var] = $val;
			} // foreach lines

		} // foreach sections

		return $ini;
	} // parseIniFile()


	function shrinkText($text,$len,$etc = "...",$etcLen = -1) {
		
		if ($len == 0) return "";
		
		if ($etcLen == -1) $etcLen = strlen($etc);
		$len = $len - $etcLen;
		$a = explode(" ",$text);
		$r = "";
		
		foreach ($a as $x) {
			if (strlen($r) + strlen($x) > $len) {
				$r .= $etc;
				break;
			} // if exceeds
			$r .= " " . $x;
		} // foreach word
		
		return $r;
	} // shrinkText()
	
?>
