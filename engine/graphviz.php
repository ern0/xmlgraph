<?php
	// xmlgraph/graphviz.php by ern0@linkbroker.hu
	// created: 2009.01.27
	// lmdate: 2009.02.17


	global $graphviz;	
	$graphviz = "/usr/bin/dot";

	
	function renderDotMap($x) {
		global $graphviz;	
	
		$descriptorspec = Array(
			0 => array("pipe","r"),
			1 => array("pipe","w"),
			2 => array("file","/dev/null","a")
		);
		$process = proc_open(
			$graphviz . " -Tcmapx",
			$descriptorspec,
			$pipes,
			"/tmp",
			Array()
		);
		fwrite($pipes[0],$x);
		fclose($pipes[0]);
	
	  $r = (stream_get_contents($pipes[1]));
	
	  fclose($pipes[1]);
		proc_close($process);

		return $r;
	} // renderDotMap()
	
	
	function paintDotImage($x,$format="png",$name="graphviz") {
		$graphviz = "/usr/bin/dot";
	
		$descriptorspec = Array(
			0 => array("pipe","r"),
			1 => array("pipe","w"),
			2 => array("file","/dev/null","a")
		);
		$process = proc_open(
			$graphviz . " -T" . $format,
			$descriptorspec,
			$pipes,
			"/tmp",
			Array()
		);
		fwrite($pipes[0],$x);
		fclose($pipes[0]);
	
		header("Content-type: image/" . $format);
		header("Content-Disposition: inline; filename=" . $name . "." . $format . ";"); 
		
	  echo(stream_get_contents($pipes[1]));
	
	  fclose($pipes[1]);
		proc_close($process);
		
	} // paintDotImage()

?>
