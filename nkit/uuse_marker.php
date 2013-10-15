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



/* OPENIMSCE EXLUDE START */          

/*
  The appended marker format (The OpenIMS marker data is appended to the file):

  << RAW FILE >>
  << 10 bytes chr (0) >>
  << 10 bytes " " >>
  << the marker data (variable length) >>
  << 10 bytes specifying the length of the data (e.g. "00000032" for 32 bytes) >>
  << 14 bytes containing "OpenIMS_Marker" >>

  The embedded marker format (embeded inside the VBA object that is embeded in the document):

  << IMSVBACOMDAT:<< 3 digit index>>[<<960 bytes of hex data>>]<< 3 digit index>>:IMSVBACOMDAT >>
  Repeated up to 999 times (468k data).

*/

function MARKER_Encode ($value)
{
  if (is_array($value)) return "";
  $value = str_replace ("#",    "#A", $value);
  $value = str_replace (chr(0), "#B", $value);
  $value = str_replace ("*",    "#C", $value);
  $value = str_replace ("!",    "#D", $value);
  return $value;
}

function MARKER_Decode ($value)
{
  $tmp = N_GUID();
  $value = str_replace ("#A", $tmp,   $value);
  $value = str_replace ("#B", chr(0), $value);
  $value = str_replace ("#C", "*",    $value);
  $value = str_replace ("#D", "!",    $value);
  $value = str_replace ($tmp, "#",    $value);
  return $value;
}

function MARKER_Filter ($content)
{
  if (substr ($content, strlen($content)-14, 14)=="OpenIMS_Marker") {
    $len = 0 + substr ($content, strlen($content)-24, 10);
    $content= substr ($content, 0, strlen($content) - (10 + 10 + $len + 10 + 14));
  }
  return $content;
}
/* OPENIMSCE EXLUDE END */          

function MARKER_Remove ($filename)
{
/* OPENIMSCE EXLUDE START */          
  global $myconfig; 
  
  if($myconfig["filesizelimitforindex"]) { 
    $upperlimit = $myconfig["filesizelimitforindex"]; 
  } else { 
    $upperlimit = 20000000; 
  } 

  if (N_QuickFileSize ($filename) > $upperlimit) return; 

  $filetime = N_FileTime ($filename);
  $filedata = N_ReadFile ($filename);
  if (substr ($filedata, strlen($filedata)-14, 14)=="OpenIMS_Marker") {
    $len = 0 + substr ($filedata, strlen($filedata)-24, 10);
    N_WriteFile ($filename, substr ($filedata, 0, strlen($filedata) - (10 + 10 + $len + 10 + 14)));
  }
  N_Touch ($filename, $filetime);
/* OPENIMSCE EXLUDE END */          
}

function MARKER_CanHaveMarker($ext)
{
  /* OPENIMSCE EXLUDE START */          
  global $myconfig;

  // Microsoft Office (2003) documents can always have a marker
  if ($ext == "doc" || $ext == "xls" || $ext == "ppt" || $ext == "vsd") return true;

  // Autocad documents can have a marker by default, but this can be disabled
  if ($ext == "dwg" && $myconfig[IMS_SuperGroupName()]["allowdwgmeta"] != "no") return true;

  // Openoffice documents can have marker, as long as zip/unzip is available
  // odp not supported (yet)
  if (($ext == "odt" || $ext == "ods") && $myconfig["zip"] && $myconfig["unzip"]) return true;

  // OpenXML (Office 2007) docx documents can have marker, as long as zip/unzip is available
  //if (($ext == "docx") && ($myconfig[IMS_SuperGroupName()]["experimentalopenxmlmangling"] == "yes") && $myconfig["zip"] && $myconfig["unzip"]) return true;
  uuse("openxml");
  if(OPENXML_IsSupported() && OPENXML_IsOpenXml($ext)) { return true; }

  // All other documents: no marker
  /* OPENIMSCE EXLUDE END */          
  return false;
}

function MARKER_CanReadMarker($ext)
{
  /* OPENIMSCE EXLUDE START */          
  if ($ext == "odt" || $ext == "ods" || $ext == "odp") return false; // no openoffice
  /* OPENIMSCE EXLUDE END */          
  return MARKER_CanHaveMarker($ext);
}

function MARKER_Save ($filename, $data)
{
  /* OPENIMSCE EXLUDE START */          
  global $myconfig; 
  
  uuse("openxml");

  if($myconfig["filesizelimitforindex"]) { 
    $upperlimit = $myconfig["filesizelimitforindex"]; 
  } else { 
    $upperlimit = 20000000; 
  } 

  if (N_QuickFileSize ($filename) > $upperlimit) return; 

  $ext = FILES_FileType($filename);

  if ($ext == "odt" || $ext == "ods") { // odp not supported yet
//    N_LOG("opendocreplace","inhoud data van $filename binnen aanroep MARKER_Save","".print_r($data,1)); // qqq
    $filetime = N_FileTime ($filename);

    uuse("opendocmetareplace");
    global $docs, $replacements;
    $docs = 0; $replacements = 0; $bytes = 0;

    $meta = array();

    foreach ($data as $key=>$value) {
      $meta[substr($key,4,strlen($key))] = $value;
    
      // dvb 18-04-2008 openoffice produces corrupt XML on empty fields
      if ($value."" == "") $meta[substr($key,4,strlen($key))] = " ";
    }

    OpenDoc_MetaReplacer::staticReplace(N_ShellPath($filename), $meta);

    N_Touch ($filename, $filetime); // make sure replication does ignore this update (stealth)
    N_FileInfo ($filename, true); // undo side efects of stealth update
  } elseif (OPENXML_IsSupported() && OPENXML_IsOpenXML($ext)) {
    
    $filetime = N_FileTime ($filename);
    OPENXML_UpdateMetaData($filename, $data);
    
    N_Touch ($filename, $filetime); // make sure replication does ignore this update (stealth)
    N_FileInfo ($filename, true); // undo side efects of stealth update
    
  } else {
    $data["markerversion"] = 2016;
    if (MARKER_Load($filename)!=$data) { 
      $filetime = N_FileTime ($filename);  
      MARKER_Remove ($filename);
      MARKER_Remove ($filename);

      // appended marker
      foreach ($data as $key => $value) {
        $list .= MARKER_Encode ($key)."*".MARKER_Encode($value)."*";
      }
      $list.="!";
      $sizestr = str_repeat ("0", 10 - strlen(strlen($list))) . strlen($list);
      $package = str_repeat (chr(0), 10).str_repeat (" ", 10).$list.$sizestr."OpenIMS_Marker";
      N_WriteFile ($filename, N_ReadFile ($filename).$package);

      // embedded marker
      foreach ($data as $key => $value) {
        $rawpackage .= MARKER_Encode ($key)."*".MARKER_Encode($value)."*";
      }
      $rawpackage .= "!";
      $package = "";
      for ($i=0; $i<strlen($rawpackage); $i++) {
        $package .= str_repeat ("0", 2-strlen(dechex(ord(substr($rawpackage, $i, 1))))).dechex(ord(substr($rawpackage, $i, 1)));
      }
    
      $content = N_ReadFile ($filename);
      if (MARKER_EmbededSpace ($content)) {
        if (MARKER_EmbededSpace ($content)<strlen($package)) $package="21!"; // not enough space
        while (strlen ($package)) {
          $index++;
          $number = str_repeat ("0", 3 - strlen($index)) . $index;
          $start = "IMSVBACOMDAT:$number"."[";
          $end = "]$number:IMSVBACOMDAT";
          $oldblock = MARKER_FetchCompleteEmbededBlock ($content, $index);
          $len = strlen ($oldblock)-34;
          $newblock = $start.substr (substr ($package, 0, $len).str_repeat ("!", $len), 0, $len).$end;
          $content = str_replace ($oldblock, $newblock, $content);
          $package = substr ($package, $len);
        }
        N_WriteFile ($filename, $content);
      }

      N_Touch ($filename, $filetime); // make sure replication does ignore this update (stealth)
      N_FileInfo ($filename, true); // undo side efects of stealth update
    }
  }
  /* OPENIMSCE EXLUDE END */          
}

function MARKER_Load ($filename)
{
  /* OPENIMSCE EXLUDE START */          
  global $myconfig; 
  
  if($myconfig["filesizelimitforindex"]) { 
    $upperlimit = $myconfig["filesizelimitforindex"]; 
  } else { 
    $upperlimit = 20000000; 
  } 
  if (N_QuickFileSize ($filename) > $upperlimit) return array(); 

  $ext = FILES_FileType($filename);
  uuse("openxml");

  if (OPENXML_IsSupported() && OPENXML_IsOpenXml($ext)) {
    $result = OPENXML_ReadMetaData($filename);
  } else {
    $code = VVFILE_IsModifiedVRef($filename);
    if ($code && substr($code, -47) == '"));uuse("marker"); MARKER_Save($file, $input);') {
      // If the file is a modified VRef, and if the *last* modification is a MARKER_Save, read the
      // contents of the marker from the modification code (instead of reading the contents of the file)
      $code = substr($code, 0, strlen($code) - 47);
      $data = N_KeepAfter($code, '"', true);
      $result = unserialize(base64_decode($data));
    } else {
      $result = array();
      $filelen = N_FileSize($filename);
      $check = N_ReadFilePart($filename, $filelen - 24, 24);
      if (substr($check, 10) == "OpenIMS_Marker") {
        $len = 0 + substr($check, 0, 10);
        $list = N_ReadFilePart($filename, $filelen-24-$len, $len);
        $list = str_replace ("*!", "", $list);
        $array = explode ("*", $list);
        for ($i=0; $i<(count($array)/2); $i++) {
          $result [$array [2*$i]] = $array[2*$i+1];
        }  
      }
    }
  }
  return $result;
  /* OPENIMSCE EXLUDE END */          
}

/* OPENIMSCE EXLUDE START */          
function MARKER_EmbededSpace ($data)
{
  if (!MARKER_FetchCompleteEmbededBlock ($data, 1)) {
    return 0;
  }
  $ctr = 1;
  while ($block = MARKER_FetchCompleteEmbededBlock ($data, $ctr))
  {
    $size += strlen($block)-34;
    $ctr++;
  }
  return $size;
}

function MARKER_FetchCompleteEmbededBlock ($data, $index)
{
  $number = str_repeat ("0", 3 - strlen($index)) . $index;
  $start = "IMSVBACOMDAT:$number"."[";
  $end = "]$number:IMSVBACOMDAT";
  $startpos = strpos ($data, $start);
  if (!$startpos) return "";
  $endpos = strpos ($data, $end);
  return substr ($data, $startpos, $endpos-$startpos+17);
}

function MARKER_FetchEmbededBlock ($data, $index)
{
  $complete = MARKER_FetchCompleteEmbededBlock ($data, $index);
  if ($complete) {
    return substr ($complete, 17, strlen($complete)-34);
  } 
}
/* OPENIMSCE EXLUDE END */          


?>