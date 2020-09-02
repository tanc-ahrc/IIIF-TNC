<?php

$extensionList["list"] = "extensionCards";

// Still to do an optional table of contents for groups - model on https://github.com/IIIF/awesome-iiif

function extensionCards ($d, $pd)
  {
  $gcontent = "";
		
	if (isset($d["file"]) and file_exists($d["file"]))
		{
		$dets = getRemoteJsonDetails($d["file"], false, true);

		foreach ($dets["list"] as $lno => $la)
			{
			//ensure each of the currently required fields a re present.
			$la = array_merge(
					array("groups" => array(), "ptitle" => "", "stitle" => "",  "comment" => "",
						"image" => "", "link" => ""), $la);

			// Testing
			$la["comment"] = "Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.";

			foreach ($la["groups"] as $k => $gnm)
					{					
					if (!isset($dets["groups"] [$gnm] ["card"]))
						{$dets["groups"] [$gnm] ["card"] = "simple";}

					if (!function_exists("build".ucfirst($dets["groups"] [$gnm] ["card"])."Card"))
						{$cfn = "buildSimpleCard";}
					else
						{$cfn = "build".ucfirst($dets["groups"] [$gnm] ["card"])."Card";}
					
					if (!isset($dets["groups"] [$gnm] ["html"]))
						{
						$gtop = "<h3>$gnm</h3><p>".$dets["groups"] [$gnm] ["comment"]."</p>";
						if (in_array($dets["groups"] [$gnm] ["card"], array("image")))
							{$dets["groups"] [$gnm] ["html"] = "$gtop<div class=\"row row-cols-1 ".
								"row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5\">";}
						else if (in_array($dets["groups"] [$gnm] ["card"], array("simple")))
							{$dets["groups"] [$gnm] ["html"] = "$gtop<div class=\"card-deck\">";}
						else if (in_array($dets["groups"] [$gnm] ["card"], array("list")))
							{$dets["groups"] [$gnm] ["html"] = "$gtop<ul>";}							
						else
							{$dets["groups"] [$gnm] ["html"] = "$gtop<div class=\"card-column\">";}
						}
						
					$dets["groups"] [$gnm] ["html"] .= call_user_func_array($cfn, array($la));					
					}
			}

		foreach ($dets["groups"] as $gnm => $ga)
				{
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
";

    $d["content"] = positionExtraContent ($d["content"], $gcontent);
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
        <!-- <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p> -->
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
				
		ob_start();			
		echo <<<END
<li>$ltop$la[ptitle]$lbottom - $la[comment].</li>
END;
		$html = ob_get_contents();
		ob_end_clean(); // Don't send output to client

		return ($html);
		}
    
?>
