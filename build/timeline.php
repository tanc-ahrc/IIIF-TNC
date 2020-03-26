<?php

// visualise with: https://mermaidjs.github.io/mermaid-live-editor test

if (!file_exists("timeline.json"))
	{die("ERROR: timeline.json missing\n");}
else
	{
	$dets = getRemoteJsonDetails("timeline.json", false, true);

	if (!isset($dets["start date"]))
		{die("ERROR: timeline.json format problems - 'start date' not found\n");}
		
	$start = $dets["start date"];

	$prefs = array_keys($dets["groups"]);
	$first = $prefs[0];

	if (!isset($dets["project"])) {$dets["project"] = "Please add a project title";}
	if (!isset($dets["margin"])) {$dets["margin"] = -3;}
		
	array_unshift($dets["groups"][$first]["stages"],
		array("Add as a margin", "", $dets["margin"], $dets["margin"]));
		
	$str = "";
	foreach ($dets["groups"] as $pref => $ga)
		{
		$str .= "\tsection $ga[title]\n";
		$no = 0;
		foreach ($ga["stages"] as $k => $a)
			{
			if ($a[1]) {$a[1] = "$a[1], ";}
			$str .= "\t\t".$a[0]." :$a[1]$pref$no, ".dA($a[2]).
				", ".dA($a[3])."\n";
			$no++;
			}
		}

	//use to hide the label used for the first line which is just in place to provide a margin/padding on the left.
	$extracss = "#".$first."0-text {display:none;}";

	ob_start();
	echo <<<END
gantt
       dateFormat  YYYY-MM-DD
       title $dets[project]	
       $str
END;
	$defs = ob_get_contents();
	ob_end_clean(); // Don't send output to client	

	//Stuff
	ob_start();
	echo <<<END
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="https://mermaidjs.github.io/mermaid-live-editor/src.96cd87af.css">
  <style>
		g a 
			{color:inherit;}
		$extracss
  </style>
</head>
<body>
  <div class="mermaid">
  $defs
  </div>
  <script src="https://cdn.jsdelivr.net/npm/mermaid@8.4.0/dist/mermaid.min.js"></script>
  <script>
  mermaid.ganttConfig = {
    titleTopMargin:25,
    barHeight:20,
    barGap:4,
    topPadding:50,
    sidePadding:50
		}
console.log(mermaid.render);
  mermaid.initialize({startOnLoad:true, flowchart: { 
    curve: 'basis' 
  }});</script>
</body>
</html>
END;
	$html = ob_get_contents();
	ob_end_clean(); // Don't send output to client	

	if (file_exists("../docs/timeline.html"))
		{unlink ("../docs/timeline.html");}
	
	$myfile = fopen("../docs/timeline.html", "w");
	fwrite($myfile, $html);
	fclose($myfile);
	}
	
function dA ($v)
	{
	global $start;
	$a = explode(",", $v);
	$m = intval($a[0]);
	if(isset($a[1]))
		{$d = intval($a[1]);}
	else
		{$d = 0;}
	$date=new DateTime($start); // date object created.

	$invert = 0;
	if ($m < 0 or $d < 0)
		{$invert = 1;
		 $m = abs($m);
		 $d = abs($d);}
	$di = new DateInterval('P'.$m.'M'.$d.'D');
	$di->invert = $invert;
	$date->add($di); // inerval of 1 year 3 months added
	$new = $date->format('Y-m-d'); // Output is 2020-Aug-30
	return($new);
	}

function getRemoteJsonDetails ($uri, $format=false, $decode=false)
	{if ($format) {$uri = $uri.".".$format;}
	 $fc = file_get_contents($uri);
	 if ($decode)
		{$output = json_decode($fc, true);}
	 else
		{$output = $fc;}
	 return ($output);}
	
?>
