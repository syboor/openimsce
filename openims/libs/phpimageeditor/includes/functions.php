<?php
    
    /*
    Copyright 2008, 2009 Patrik Hultgren
    
    This file is part of PHP Image Editor.

    PHP Image Editor is a commercial software. 
    In order the use this application in a NONE OPEN SOURCE project you have to purchased a license at:
    http://www.shareit.com/product.html?productid=300296445&backlink=http%3A%2F%2Fwww.phpimageeditor.se%2F
    
    When using this application you must follow the license restrictions at:
    http://www.phpimageeditor.se/commercial-license.php
    */


    function PIE_GetTexts($filePath)
	{
		$texts = array();
		$lines = file($filePath);
		
		foreach($lines as $line_num => $line)
		{
			if (substr_count($line, "#") == 0)
			{
				$keyAndText = explode("=", trim($line));
				$texts[$keyAndText[0]] = $keyAndText[1];
			}
		}
		
		return $texts;
	}
	
	function PIE_Echo($text)
	{
		echo $text;
		//echo utf8_encode($text);
	}	
?>