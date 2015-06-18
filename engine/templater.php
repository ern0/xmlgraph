<?php
	// xmlgraph/templater.php by ern0@linkbroker.hu
	// created: 2009.01.27
	// lmdate: 2009.02.02
	

	function template($tpl,$file = "") {
		global $templates;
		global $lastFile;
		
		if (!strlen($file)) $file = $lastFile;

		if (!sizeof($templates[$file])) {
		
			$lastFile = $file;
			$f = join("",file($file));
			$f = str_replace("\r","",$f);
			$f = str_replace("\n\t","\n",$f);
			$f = str_replace("\n\n","\n",$f);
			$a = explode("----[",$f);

			for ($n = 1; $n < sizeof($a); $n++) {
				$x = explode("]",$a[$n]);
				$t = $x[0];
				$x = explode("\n",$a[$n]);
				unset($x[0]);
				$x = join("\n",$x);
				$templates[$file][$t] = $x;
			} // for n
			
		} // if template loaded
		
		return $templates[$file][$tpl];
	} // template()
	
	
?>
