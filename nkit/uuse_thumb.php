<?
/***.-*.-..*.-..***-.--*---*..-*.-.***-...*.-*...*.***.-*.-.*.***-...*.*.-..*---*-.*--.***-*---***..-*...*.-.-.-***
 *                                                                                                                * 
 *       This sourcecode file is part of OpenIMS CE (Community Edition).                                          *
 *       OpenIMS CE (Community Edition) is a program developed by OpenSesame ICT B.V.                             *
 *       Copyright (C) 2001-2011 OpenSesame ICT B.V. Meerwal 13, NL-3432ZV, Nieuwegein.                           *
 *                                                                                                                *
 *       This program is free software; you can redistribute it and/or modify it under                            *
 *       the terms of the GNU General Public License version 3 as published by the                                *
 *       Free Software Foundation with the addition of the following permission added                             *
 *       to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK                             *
 *       IN WHICH THE COPYRIGHT IS OWNED BY OpenSesame ICT, OpenSesame ICT DISCLAIMS                              *
 *       THE WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.                                                  *
 *                                                                                                                *
 *       This program is distributed in the hope that it will be useful, but WITHOUT                              *
 *       ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS                            *
 *       FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more                                   *
 *       details.                                                                                                 *
 *                                                                                                                *
 *       You should have received a copy of the GNU General Public License along with                             *
 *       this program; if not, see http://www.gnu.org/licenses or write to the Free                               *
 *       Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA                                   *
 *       02110-1301 USA.                                                                                          *
 *                                                                                                                *
 *       You can contact OpenSesame ICT B.V. at Meerwal 13, NL-3432 ZV, Nieuwegein                                *
 *       or at e-mail address info@osict.com.                                                                     *
 *                                                                                                                *
 *       The interactive user interfaces in modified source and object code versions                              *
 *       of this program must display Appropriate Legal Notices, as required under                                *
 *       Section 5 of the GNU General Public License version 3.                                                   *
 *                                                                                                                *
 *       In accordance with Section 7(b) of the GNU General Public License version 3,                             *
 *       these Appropriate Legal Notices must retain the display of the "OpenIMS" logo.                           *
 *       If the display of the logo is not reasonably feasible for technical reasons, the                         *
 *       Appropriate Legal Notices must display the words "Powered by OpenIMS".                                   *
 *                                                                                                                *
 *       Please note the OpenIMS EE (Enterprise Edition) license explicitly forbids                               *
 *       transfer of code or concepts from OpenIMS EE to OpenIMS CE.                                              *
 *                                                                                                                * 
 ***.-*.-..*.-..***-.--*---*..-*.-.***-...*.-*...*.***.-*.-.*.***-...*.*.-..*---*-.*--.***-*---***..-*...*.-.-.-***/



// $bg is the color for filling in transparant parts of gif and png images
// not a parameter in ufc link (also see FILES_ThumbLocation)
function THUMB_toThumbnail($image, $max_width = 100, $max_height = 100, $bg = array(255,255,255)) {
   if(!($width = @imagesx($image)) || !($height = @imagesy($image)))     //get size
       return false;
   
   if($width <= $max_width && $height <= $max_height){         //if size < max then new size = size
       $new_width  = $width;
       $new_height = $height;
   }else{
       $scale = max($width/$max_width, $height/$max_height);//calculate scale
       $new_width = round($width / $scale);                   //scale width and height
       $new_width  = ($new_width < 1) ? 1 : $new_width;  //make sure the width and height arent smaller than 1px
       $new_height = round($height / $scale);
       $new_height  = ($new_height < 1) ? 1 : $new_height;
   }

   if(($thumb = @imagecreatetruecolor($new_width, $new_height)) === false)    //create new image
       return false;

   imagefilledrectangle($thumb, 0, 0, $new_width, $new_height, imagecolorallocate($thumb, $bg[0],$bg[1],$bg[2]));     //draw background
   if(!@imagecopyresampled($thumb, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height))  //draw resized image
   return false;

   return $thumb;
}

// takes absolute paths to files
// no openims convention $filename = "C:/openims/sjors_sites/objects/7e31ce4f47eb0ada5febdf96d186e8ac/1.jpg"
function THUMB_loadImage($filename){
   $doctype = str_replace ("/", "_", N_KeepAfter($filename, ".", true));
   $tmpfile = N_CleanPath ("html::/tmp/".N_GUID()."__thumb.$doctype");
   N_CopyFile ($tmpfile, $filename);
   $filename = $tmpfile;

   N_ErrorHandling (false);
   if(!($image_info = @getImageSize($filename))) {              //not a valid image
       N_ErrorHandling (true);
       return false;
   }
   N_ErrorHandling (true);

   if(substr($image_info['mime'], 0, 6) != 'image/')      //not an image
   {
       N_DeleteFile ($tmpfile);
       return false;
   }

   $f = "imagecreatefrom" . substr($image_info['mime'], 6);//get image type and construct imagecreate function
   if(!function_exists($f))                                    //not supported
   {
       N_DeleteFile ($tmpfile);
       return false;
   }

   if(!($image = $f($filename)))                               //load image
   {
       N_DeleteFile ($tmpfile);
       return false;
   }

   N_DeleteFile ($tmpfile);
   return $image;
}

function THUMB_supportedImages(){
  return array('jpeg','gif','png') ;
}

function THUMB_imagesSupported(){
   foreach(THUMB_supportedImages() as $type){
       if(!function_exists("imagecreatefrom".$type))
            return false;
   }
   if(!function_exists("imagecreatetruecolor"))
       return false;
   if(!function_exists("imagecolorallocate"))
       return false;
   if(!function_exists("imagecopyresampled"))
       return false;
   if(!function_exists("imagejpeg"))
       return false;
   return true;
}

function THUMB_pdfToThumbnail($pdffile, $max_width = 100, $max_height = 100) {
  global $myconfig;
  if(isset($myconfig["convert"]))
  {
    $tmplocation = TMP_Directory();
    $max_width = intval($max_width);
    $max_height = intval($max_height);
    $command = $myconfig["convert"]." background white -flatten -thumbnail ".$max_width."x".$max_height." -colorspace RGB ".escapeshellarg($pdffile."[0]")." ".$tmplocation."/thumb.jpg";
    `$command`;
    return N_Readfile($tmplocation."/thumb.jpg");
  }
}
?>