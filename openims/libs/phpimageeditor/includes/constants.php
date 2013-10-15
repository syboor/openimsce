<?php
    
    /*
    Copyright 2008, 2009 Patrik Hultgren
    
    YOUR PROJECT MUST ALSO BE OPEN SOURCE IN ORDER TO USE PHP IMAGE EDITOR.
    OR ELSE YOU NEED TO BUY THE COMMERCIAL VERSION AT:
    http://www.shareit.com/product.html?productid=300296445&backlink=http%3A%2F%2Fwww.phpimageeditor.se%2F
    
    This file is part of PHP Image Editor.

    PHP Image Editor is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    PHP Image Editor is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with PHP Image Editor.  If not, see <http://www.gnu.org/licenses/>.
    */

    //ericd 131109 set OpenIMS tmp dirs
	
    include (getenv ("DOCUMENT_ROOT"). "/nkit/nkit.php");
	
    function setEditDirsPIEforOpenIMS ($theDir) {

       //empty file to force a dir to be set
       $filename = 'empty.txt';
       $filecontent = '';

      //pie (should) exist in installdrive/openims/openims/libs/phpimageeditor/
      $dir = "../../../tmp/phpimageeditor/".$theDir;
      $dirEnv = getenv ("DOCUMENT_ROOT").'/tmp/phpimageeditor/'.$theDir;
      N_WriteFile ($dirEnv.$filename, $filecontent);
		
      return $dir;
    }

	define("IMAGE_ORIGINAL_PATH", setEditDirsPIEforOpenIMS ("editimagesoriginal/"));
	define("IMAGE_WORK_WITH_PATH", setEditDirsPIEforOpenIMS ("editimagesworkwith/"));
	define("IMAGE_PNG_PATH", setEditDirsPIEforOpenIMS ("editimagespng/"));


    define("PHP_VERSION_MINIMUM", "5");
	define("GD_VERSION_MINIMUM", "2.0.28");
	//define("IMAGE_ORIGINAL_PATH", "editimagesoriginal/");
	//define("IMAGE_WORK_WITH_PATH", "editimagesworkwith/");
	//define("IMAGE_PNG_PATH", "editimagespng/");
	define("MENU_RESIZE", "0");
	define("MENU_ROTATE", "1");
	define("MENU_CROP", "2");
	define("MENU_EFFECTS", "3");
?>