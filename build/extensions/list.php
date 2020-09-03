<?php

$extensionList["list"] = "extensionCards";
$blank = array("groups" => array(), "ptitle" => "",
		"stitle" => "",  "comment" => "", "image" => "", "link" => "");
		
// Still to do an optional table of contents for groups - model on https://github.com/IIIF/awesome-iiif

function buildContents ($groups)
	{
	$html = "<ul>";

	foreach ($groups as $gnm => $ga)
		{$tag = urlencode(strtolower($gnm));
 		 $html .= "<li>".
			"<a id=\"$tag-TBC\" class=\"offsetanchor\"></a>".
			"<a href=\"#$tag\">$gnm</a></li>";}
	
	$html .= "</ul>";

	return ($html);
	}

/*

<svg class="octicon octicon-link" viewBox="0 0 16 16" version="1.1" width="16" height="16" aria-hidden="true"><path fill-rule="evenodd" d="M7.775 3.275a.75.75 0 001.06 1.06l1.25-1.25a2 2 0 112.83 2.83l-2.5 2.5a2 2 0 01-2.83 0 .75.75 0 00-1.06 1.06 3.5 3.5 0 004.95 0l2.5-2.5a3.5 3.5 0 00-4.95-4.95l-1.25 1.25zm-4.69 9.64a2 2 0 010-2.83l2.5-2.5a2 2 0 012.83 0 .75.75 0 001.06-1.06 3.5 3.5 0 00-4.95 0l-2.5 2.5a3.5 3.5 0 004.95 4.95l1.25-1.25a.75.75 0 00-1.06-1.06l-1.25 1.25a2 2 0 01-2.83 0z"></path></svg>
* 
 <h2><a id="user-content-standards" class="anchor" aria-hidden="true" href="#standards"></a>Standards</h2>
 
 */
	
function extensionCards ($d, $pd)
  {
	global $blank;
  $gcontent = "";
		
	if (isset($d["file"]) and file_exists($d["file"]))
		{
		$dets = getRemoteJsonDetails($d["file"], false, true);

		//prg(0, $dets["groups"]);
		
		foreach ($dets["list"] as $lno => $la)
			{
			//ensure each of the currently required fields a re present.
			$la = array_merge($blank, $la);

			// Testing
			//$la["comment"] = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.";

			//if (!$la["groups"])
			//	{prg(1, $la);}
			
			foreach ($la["groups"] as $k => $gnm)
					{
					$c = false;
					if (preg_match("/^Digital.+$/", $gnm, $m))
						{$c = true;
							//echo "CCCCCCCCCCCCCCCCC\n"; prg(0, $gnm);
							}
					else
						{//echo "@@@".$gnm."@@@\n";
							}
										
					if (!isset($dets["groups"] [$gnm] ))
						{$dets["groups"] [$gnm]  = array(
							"comment" => "",
							"card" => "list",
							"config" => array(),
							);}
														
					if (!isset($dets["groups"] [$gnm] ["card"]))
						{$dets["groups"] [$gnm] ["card"] = "list";}

					if (!function_exists("build".ucfirst($dets["groups"] [$gnm] ["card"])."Card"))
						{$cfn = "buildSimpleCard";}
					else
						{$cfn = "build".ucfirst($dets["groups"] [$gnm] ["card"])."Card";}
					
					if (!isset($dets["groups"] [$gnm] ["html"]))
						{//echo "########$gnm###############\n";
							$dets["groups"] [$gnm] ["html"] = startGroupHtml (
							$gnm, $dets["groups"] [$gnm] ["comment"],
							$dets["groups"] [$gnm] ["card"], $dets["tableofcontents"]);

							if ($c) {prg(0, $dets["groups"] [$gnm] );}
			
						}
						
					$dets["groups"] [$gnm] ["html"] .= call_user_func_array($cfn, array($la));					
					}
			}

		foreach ($dets["groups"] as $gnm => $ga)
				{
				/*if (!isset($ga["html"]))
					{prg(0, $gnm);
					 prg(0, $dets["groups"][$gnm]);}*/
				 if (in_array($dets["groups"] [$gnm] ["card"], array("list")))
					{$gcontent .= $ga ["html"]."</ul><br/>";}
				else
					{$gcontent .= $ga ["html"]."</div><br/>";}
				}
			
		$pd["extra_css"] .= "
.card-img {
	width: auto;
  max-width: 100%;
  max-height:200px;
  display: block;
  margin-left: auto;
  margin-right: auto;
  padding: 10px;
}

.card-img-top {
	width: auto;
  max-width: 100%;    
  max-height:128px;
  display: block;
  margin-left: auto;
  margin-right: auto;
  padding: 10px;
}

.nodec:link, .nodec:visited, .nodec:hover, .nodec:active {
	text-decoration: none;
	color: inherit;
	}

.card-hov:hover {
	opacity: 0.7;
}

.offsetanchor {
	position: relative;
  top: -75px;
}

";

		// Check if a table of contents should be added.
		if (!isset($dets["tableofcontents"])) {$dets["tableofcontents"] = false;}
		if ($dets["tableofcontents"])
			{$tb = buildContents ($dets["groups"]);}
		else
			{$tb = "";}
			
    $d["content"] = positionExtraContent ($d["content"], $tb.$gcontent);
		}

  return (array("d" => $d, "pd" => $pd));
  }

 function buildFullCard ($la)
		{	 
		if ($la["link"])
				{$ltop= "<a href=\"$la[link]\" class=\"stretched-link nodec\">";
					$lbottom = "</a>";}
		else
				{$ltop= "";
					$lbottom = "";}
				
		ob_start();			
		echo <<<END

<div class="card mb-3 card-hov " style="width: 100%;">
  $ltop<div class="row no-gutters">
    <div class="col-md-4  my-auto" >
      <img src="$la[image]" class="card-img" alt="$la[ptitle]">
    </div>
    <div class="col-md-8">
      <div class="card-body">
        <h4 class="card-title">$la[ptitle]</h4>
        <h5 class="card-title">$la[stitle]</h5>
        <p class="card-text">$la[comment]</p>
      </div>
    </div>
  </div>$lbottom
</div>

END;
		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client

		return ($html);
		}

 function buildSimpleCard ($la) {			
		if ($la["link"])
				{$ltop= "<a href=\"$la[link]\" class=\"stretched-link nodec\">";
					$lbottom = "</a>"	;}
		else
				{$ltop= "";
					$lbottom = "";}
				
		ob_start();			
		echo <<<END
		
  <div class="col mb-4">
    <div class="card" title="$la[ptitle]">
			$ltop
      <img class="card-img-top" src="$la[image]" alt="$la[ptitle]">
      $lbottom
      <div class="card-body">
        <h5 class="card-title">$la[ptitle]</h5>
        <p class="card-text">$la[comment]</p>
      </div>
    </div>
  </div>

END;
		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client

		return ($html);
		}
		
 function buildImageCard ($la) {			
		if ($la["link"])
				{$ltop= "<a href=\"$la[link]\" class=\"stretched-link nodec\">";
					$lbottom = "</a>"	;}
		else
				{$ltop= "";
					$lbottom = "";}
				
		ob_start();			
		echo <<<END
		
  <div class="col mb-4">
    <div class="card" title="$la[ptitle]">
			$ltop
      <img class="card-img-top" src="$la[image]" alt="$la[ptitle]">
      $lbottom
    </div>
  </div>

END;
		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client

		return ($html);
		}

 function buildListCard ($la) {
	 	if ($la["link"])
				{$ltop= "<a href=\"$la[link]\" class=\"\">";
					$lbottom = "</a>"	;}
		else
				{$ltop= "";
					$lbottom = "";}

		if ($la["comment"])
			{$la["comment"] = " - ".$la["comment"];}
				
		ob_start();			
		echo <<<END
<li>$ltop$la[ptitle]$lbottom$la[comment]</li>
END;
		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client

		return ($html);
		}

function startGroupHtml ($gnm, $comment, $card, $tbc)
	{
	$html = "";
	$tag = urlencode(strtolower($gnm));

	if ($tbc)
		{$anchor = "<a id=\"$tag\" class=\"anchor offsetanchor\" ".
				"aria-hidden=\"true\" href=\"#${tag}-TBC\"></a>";
		 $alink = "<a class=\"anchor nodec\" aria-hidden=\"true\" ".
				"href=\"#${tag}-TBC\">$gnm</a>";}
	else
		{$anchor = "";
		 $alink = "$gnm";}

	$gtop = "<h3>$anchor$alink</h3><p>".$comment."</p>";

	if (in_array($card, array("image")))
		{$html  = "$gtop<div class=\"row row-cols-1 ".
			"row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5\">";}						
	else if (in_array($card, array("list")))
		{$html = "$gtop<ul>";}							
	else if (in_array($card, array("full")))
		{$html = "$gtop<div class=\"card-column\">";}
	else //if (in_array($card, array("simple"))) or anything else
		{$html = "$gtop<div class=\"card-deck\">";}

	return ($html);
	}
    
?>
