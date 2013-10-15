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



uuse ("flex");
uuse ("ufc");

global $files_testmode;
// $files_testmode = "yes";

// the existing show shortcuts does not check for $record["objecttype"]=="shortcut" so we also don't so no new index will be created
function FILES_documentShortcuts( $sgn , $object_id )
{
  $list = MB_TurboSelectQuery ("ims_".$sgn."_objects", '$record["source"]', $object_id);
  return $list;
}

function FILES_IsShortcut ($supergroupname, $object_id)
{
  $object = &IMS_AccessObject ($supergroupname, $object_id);  
  return $object["objecttype"]=="shortcut";
}

function FILES_IsPermalink ($supergroupname, $object_id)
{
  $object = &IMS_AccessObject ($supergroupname, $object_id);  
  return $object["versionshortcut"]=="yes";
}

function FILES_IsIndependentShortcut ($supergroupname, $object_id) {
  // An independent shortcut has some metadata of its own (not just the standard metadata of the shortcut) and mimics a document object

  $object = IMS_AccessObject ($supergroupname, $object_id);  
  if ($object["objecttype"] != "shortcut") return false;
  if ($object["versionshortcut"] == "yes") return false;

  if ($object["workflow"]) {
    $workflow = MB_Load("shield_{$supergroupname}_workflows", $object["workflow"]);
    if ($workflow["shortcutmeta"]) {
      return true;
    }
  }

  return false;
}

function FILES_Base ($supergroupname, $object_id)
{
  $object = &IMS_AccessObject ($supergroupname, $object_id);  
  if ($object["objecttype"]=="shortcut") {
    return $object["source"];
  }
}

function FILES_Exists ($supergroupname, $object_id)
{
  $object = &IMS_AccessObject ($supergroupname, $object_id);
  if ($object["published"]=="yes") return true;
  if ($object["preview"]=="yes") return true;
  return false;
}

function FILES_RawFileName ($object, $version = "preview")
// returns very raw internal filename for the indicated version (preview / published / specific history version)
{
  if ($version == "published") {
    $filename = ($object["pub"]["filename"] ? $object["pub"]["filename"] : $object["filename"]);
  } elseif ($version == "preview") {
    $filename = $object["filename"];
  } else { // specific history version
    $filename = ($object["history"][$version]["filename"] ? $object["history"][$version]["filename"] : $object["filename"]);
  }
  return $filename;
}

function FILES_RawExecutable ($object, $version = "preview")
// returns very raw internal executable for the current "version"
{
  if ($version == "published") {
    $executable = ($object["pub"]["executable"] ? $object["pub"]["executable"] : $object["executable"]);
  } elseif ($version == "preview") {
    $executable = $object["executable"];
  } else { // specific history version
    $executable = ($object["history"][$version]["executable"] ? $object["history"][$version]["executable"] : $object["executable"]);
  }
  return $executable;
}

// return internal file name (e.g. for transfer agent purposes)
function FILES_TrueFileName ($supergroupname, $object_id, $version = "preview")
{
  global $files_testmode;
  if ($files_testmode == "yes") {
    return "FILES_TrueFileName ($supergroupname, $object_id, $version)";
  }

  $object = &IMS_AccessObject ($supergroupname, $object_id);

  $executable = FILES_RawExecutable ($object, $version);
  $filename = FILES_RawFilename ($object, $version);
  
  if ($executable=="winword.exe") {
    $doc = "document.doc";
  } else if ($executable=="excel.exe") {
    $doc = "document.xls";
  } else if ($executable=="powerpnt.exe") {
    $doc = "document.ppt";
  } 
  if ($filename) $doc = $filename;
  return $doc;
}

// return visible filename (e.g. for hyperlinks)
function FILES_VisibleFileName ($supergroupname, $object_id, $pdf=false, $version = "preview")
{
  global $files_testmode;
  if ($files_testmode == "yes") {
    return "FILES_VisibleFileName ($supergroupname, $object_id, $pdf, $version)";
  }

  $object = &IMS_AccessObject ($supergroupname, $object_id);
  
  if (FILES_IsPermalink($supergroupname, $object_id)) {
        $title = str_replace ("PL: ", "", $object["base_shorttitle"]);
  	$object_id = $object["source"];
  } else
  	$title = $object["shorttitle"];
  
  if ($pdf) {
    if ($pdf==="html") $ext=".html"; else $ext=".pdf";
//    switch($pdf) {
//    case "html":
//      $ext = ".html";
//      break;
//    default:
//      $ext = ".pdf";
//    }
    return N_preg_replace ("'[^A-Za-z0-9.]'i", "_", FORMS_ML_Filter($title)).$ext;
  } else {
    return N_preg_replace ("'[^A-Za-z0-9.]'i", "_", FORMS_ML_Filter($title)).".".FILES_FileType ($supergroupname, $object_id, $version);
  }
}

function FILES_FileExt ($supergroupname, $object_id, $version = "preview")
{
  global $files_testmode;
  if ($files_testmode == "yes") {
    return "FILES_FileExt ($supergroupname, $object_id, $version)";
  }

  $ext=""; 
  $object = &IMS_AccessObject ($supergroupname, $object_id);
  $executable = FILES_RawExecutable ($object, $version);
  if ($executable=="winword.exe") {
  } else if ($executable=="excel.exe") {
  } else if ($executable=="powerpnt.exe") {
  } else {
    $ext = ".".FILES_FileType ($supergroupname, $object_id, $version);
  }
  if (strpos (FILES_TrueFileName ($supergroupname, $object_id, $version), ".imsctn.txt")) {
    $ext = "";
  }
  if (strpos (FILES_TrueFileName ($supergroupname, $object_id, $version), ".pdf")) {
    $ext = "";
  }
  return $ext;
}

function FILES_FileType ($supergroupname, $object_id="", $version = "preview")
{
  if (!$object_id) { // filename mode
    $filename = $supergroupname;
    if (strpos ($filename, ".imsctn.txt")) {
      return "imsctn.txt";
    }
    return strtolower(strrev(N_KeepBefore (strrev($filename), ".")));
  } else {
    if (strpos (FILES_TrueFileName ($supergroupname, $object_id, $version), ".imsctn.txt")) {
      return "imsctn.txt";
    }
    return strtolower(strrev(N_KeepBefore (strrev(FILES_TrueFileName ($supergroupname, $object_id, $version)), ".")));
  }
}

function FILES_IconExists ($thedoctype, $shortcut=false)
{
  if ($shortcut) {
    global $FILES_SIconExists_checked, $FILES_SIconExists_result;
    if (!$FILES_SIconExists_checked[$thedoctype]) {
      $FILES_SIconExists_checked[$thedoctype] = true;
      $FILES_SIconExists_result[$thedoctype] = file_exists (getenv("DOCUMENT_ROOT")."/openims/lnk_ico_$thedoctype.gif");
    }
    return $FILES_SIconExists_result[$thedoctype];
  } else {
    global $FILES_IconExists_checked, $FILES_IconExists_result;
    if (!$FILES_IconExists_checked[$thedoctype]) {
      $FILES_IconExists_checked[$thedoctype] = true;
      $FILES_IconExists_result[$thedoctype] = file_exists (getenv("DOCUMENT_ROOT")."/openims/ico_$thedoctype.gif");
    }
    return $FILES_IconExists_result[$thedoctype];
  }
} 

function FILES_Icon ($supergroupname, $object_id, $shortcut=false, $version = "preview")
{
  global $files_testmode;
  if ($files_testmode == "yes") {
    return "/ufc/rapid/openims/down_small.gif";
  }
  $object = &IMS_AccessObject ($supergroupname, $object_id);  
  $executable = FILES_RawExecutable ($object, $version);
  if ($shortcut) {
    if ($executable=="winword.exe") {
      $image = "/ufc/rapid/openims/lnk_word_small.gif";
    } else if ($executable=="excel.exe") {
      $image = "/ufc/rapid/openims/lnk_excel_small.gif";
    } else if ($executable=="powerpnt.exe") {
      $image = "/ufc/rapid/openims/lnk_powerpoint_small.gif";
    } else {
      $image = "/ufc/rapid/openims/shortcut_small.gif";
    } 
    $thedoctype = FILES_FileType ($supergroupname, $object_id, $version);
    if (FILES_IconExists ($thedoctype, true)) {
      $image = "/ufc/rapid/openims/lnk_ico_$thedoctype.gif";
    }
    if (strpos (FILES_TrueFileName ($supergroupname, $object_id, $version), ".imsctn.txt")) {
      $image = "/ufc/rapid/openims/lnk_container.gif";
    }
  } else {
      if ($executable=="winword.exe") {
      $image = "/ufc/rapid/openims/word_small.gif";
    } else if ($executable=="excel.exe") {
      $image = "/ufc/rapid/openims/excel_small.gif";
    } else if ($executable=="powerpnt.exe") {
      $image = "/ufc/rapid/openims/powerpoint_small.gif";
    } else {
      $image = "/ufc/rapid/openims/file_small.gif";
    } 
    $thedoctype = FILES_FileType ($supergroupname, $object_id, $version);
    // patch if executable is auto, and type is doc, xls, ppt
    if(($executable=="auto") && (in_array($thedoctype, array("doc","xls","ppt")))) {
      switch($thedoctype) {
      case "doc": $image = "/ufc/rapid/openims/word_small.gif"; break;
      case "xls": $image = "/ufc/rapid/openims/excel_small.gif"; break;
      case "ppt": $image = "/ufc/rapid/openims/powerpoint_small.gif"; break;
      }
    } else {
      if (FILES_IconExists ($thedoctype)) {
        $image = "/ufc/rapid/openims/ico_$thedoctype.gif";
      }
    }
    if (strpos (FILES_TrueFileName ($supergroupname, $object_id, $version), ".imsctn.txt")) {
      $image = "/ufc/rapid/openims/container.gif";
    }
  }

  if ($object["doctopdf"] == "yes")
    $image = "/ufc/rapid/openims/ico_pdf.gif";
  return $image;
}

function FILES_DMSURL ($supergroupname, $object_id)
{
  global $files_testmode;
  if ($files_testmode == "yes") {
    return "FILES_DMSURL ($supergroupname, $object_id)";
  }

  $object = &IMS_AccessObject ($supergroupname, $object_id);
  $url = "/openims/openims.php?mode=dms&cfolder=".$object["directory"]."&cobject=".$object_id; // qqq
  return $url;
}

function FILES_TransViewHistoryURL ($supergroupname, $object_id, $v)
{
  global $files_testmode;
  if ($files_testmode == "yes") {
    return "FILES_TransViewHistoryURL ($supergroupname, $object_id, $v)";
  }
  $thepath = $supergroupname."\\objects\\history\\".$object_id."\\".$v."\\";
  if (strpos (FILES_TrueFileName ($supergroupname, $object_id, $v), ".imsctn.txt")) {
    if(N_UseAdvancedTransferAgent()) {
      $object = &IMS_AccessObject ($supergroupname, $object_id);
      $doc = FILES_TrueFileName ($supergroupname, $object_id, $v);
      $path = "\\".$thepath;
      $temppath = "temp::".$thepath;
      global $myconfig;
      if ($myconfig[IMS_SuperGroupName()]["imstrans"]) {
        $imstrans = $myconfig[IMS_SuperGroupName()]["imstrans"];
        if (substr ($imstrans, strlen($imstrans)-1, 1)=="\\") {
          $imstrans = substr ($imstrans, 0, strlen($imstrans)-1);
        }
      } else {
        $imstrans = "c:\\imstrans";
      }
      $tag = N_KeepAfter ($imstrans, "\\");
      $commands = array (
        "1_command" => "deletedir",
        "1_dir" => "$imstrans",
    
        "2_command" => "download",
        "2_dir" => $path,
        "2_file" => $doc,
        "2_subs" => "true",
    
        "3_command" => "copydir",
        "3_from" => $temppath,
        "3_to" => $imstrans,
    
        "4_command" => "start",
        "4_doc" => $imstrans,
        "4_title1" => $tag,
      );
      $url = IMS_GenerateAdvancedTransferURL ($commands);
    } else {
      $url = FORMS_URL (array ("formtemplate"=>'
              <table>
              <tr><td><font face="arial" size=2><b>'.ML("Containers worden op dit platform niet ondersteund", "Containers are not supported on this platform").'</b></font></td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
              </table>
            '));
    }
  } else if (strpos (FILES_TrueFileName ($supergroupname, $object_id, $v)."#@!", ".fm#@!")) {
    $object = &IMS_AccessObject ($supergroupname, $object_id);
    $doc = FILES_TrueFileName ($supergroupname, $object_id, $v);
    $url = IMS_GenerateViewURL ("\\".$thepath, $doc, "FrameMaker.exe", $includesubs=false);
  } else {
    $object = &IMS_AccessObject ($supergroupname, $object_id);
    $doc = FILES_TrueFileName ($supergroupname, $object_id,$v);
    $url = IMS_GenerateViewURL ("\\".$thepath, $doc, FILES_ViewExecutable ($supergroupname, $object_id, $v), $includesubs=false);
  }
  return $url;
}

function FILES_EditExecutable ($supergroupname, $object_id)
{
  // No $version param, since editing ALWAYS applies to the preview version

  $object = &IMS_AccessObject ($supergroupname, $object_id);
  $executable = FILES_RawExecutable ($object, "preview");
  $filename = FILES_RawFilename ($object, "preview");
  
  if ($executable=="psp.exe") $executable = "auto";

  global $myconfig; 
  $ext = strrev(N_Keepbefore(strrev($filename), "."));
  if ($myconfig[$supergroupname]["alterexecutables"][$ext]["edit"]) {
    $executable = $myconfig[$supergroupname]["alterexecutables"][$ext]["edit"];
  }

  return $executable;
}

function FILES_ViewExecutable ($supergroupname, $object_id, $version = "preview")
{
  $object = &IMS_AccessObject ($supergroupname, $object_id);
  $executable = FILES_RawExecutable ($object, $version);
  $filename = FILES_RawFilename ($object, $version);
  if ($executable=="psp.exe") $executable = "auto";

  global $myconfig;  
  $ext = strrev(N_Keepbefore(strrev($filename), "."));
  if ($myconfig[$supergroupname]["alterexecutables"][$ext]["view"]) {
    $executable = $myconfig[$supergroupname]["alterexecutables"][$ext]["view"];
  }
  return $executable;
}


//20110928 KVD CORE-23
// Gebruik GEEN TA voor bekijken
// hetzij voor alle bestanden ("yes")
// of alleen bepaalde extensies (array( extensies zonder punten ervoor) )
function FILES_FilenameURL($supergroupname, $object_id, $preview)
{
  $notrans = @$GLOBALS["myconfig"][$supergroupname]["viewdocnotrans"];

  if ($ob["objecttype"]=="shortcut"|| $ob["objecttype"]=="permalink") {
    $ob = $ob["source"];
  }

  if ($notrans=="yes") {
    if ($preview)
      return FILES_DocPreviewURL($supergroupname, $object_id);
    else
      return FILES_DocPublishedURL($supergroupname, $object_id);
  } else if (is_array($notrans)) {
    
    $ob = MB_Ref("ims_${supergroupname}_objects", $object_id);
    $filename = FILES_RawFilename ($ob, ($preview ? "preview" : "published"));

    if ($filename) {
      $extn = strrchr($filename, ".");
      if ($extn && strlen($extn) < strlen($filename)) 
        $extn = strtolower(substr($extn,1));
      else
        $extn = "";        
    }
    if ($extn && (in_array($extn, $notrans) || in_array(".".$extn, $notrans))) {
      if ($preview)
        return FILES_DocPreviewURL($supergroupname, $object_id);
      else
        return FILES_DocPublishedURL($supergroupname, $object_id);
    }    
  }
  return false;
}
///

function FILES_TransViewPreviewURL ($supergroupname, $object_id)
{
  global $files_testmode;

  if ($files_testmode == "yes") {
    return "FILES_TransViewPreviewURL ($supergroupname, $object_id)";
  }

//20110928 KVD CORE-23
  if (@$GLOBALS["myconfig"][$supergroupname]["viewdocnotrans"]) {
    $url = FILES_FilenameURL($supergroupname, $object_id, true);
    if ($url) return $url;
  }
///
  $thepath = $supergroupname."\\preview\\objects\\".$object_id."\\";
  if (strpos (FILES_TrueFileName ($supergroupname, $object_id, "preview"), ".imsctn.txt")) {
    if(N_UseAdvancedTransferAgent()) {
      $object = &IMS_AccessObject ($supergroupname, $object_id);
      $doc = FILES_TrueFileName ($supergroupname, $object_id, "preview");
      $path = "\\".$thepath;
      $temppath = "temp::".$thepath;
      global $myconfig;
      if ($myconfig[IMS_SuperGroupName()]["imstrans"]) {
        $imstrans = $myconfig[IMS_SuperGroupName()]["imstrans"];
        if (substr ($imstrans, strlen($imstrans)-1, 1)=="\\") {
          $imstrans = substr ($imstrans, 0, strlen($imstrans)-1);
        }
      } else {
        $imstrans = "c:\\imstrans";
      }
      $tag = N_KeepAfter ($imstrans, "\\");
      $commands = array (
        "1_command" => "deletedir",
        "1_dir" => "$imstrans",
    
        "2_command" => "download",
        "2_dir" => $path,
        "2_file" => $doc,
        "2_subs" => "true",
    
        "3_command" => "copydir",
        "3_from" => $temppath,
        "3_to" => $imstrans,
    
        "4_command" => "start",
        "4_doc" => $imstrans,
        "4_title1" => $tag,
      );
      $url = IMS_GenerateAdvancedTransferURL ($commands);
    } else {
      $url = FORMS_URL (array ("formtemplate"=>'
              <table>
              <tr><td><font face="arial" size=2><b>'.ML("Containers worden op dit platform niet ondersteund", "Containers are not supported on this platform").'</b></font></td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
              </table>
            '));    
    }
  } else if (strpos (FILES_TrueFileName ($supergroupname, $object_id, "preview")."#@!", ".fm#@!")) {
    $object = &IMS_AccessObject ($supergroupname, $object_id);
    $doc = FILES_TrueFileName ($supergroupname, $object_id, "preview");
    $url = IMS_GenerateViewURL ("\\".$thepath, $doc, "FrameMaker.exe", $includesubs=false);
  } else {
    $object = &IMS_AccessObject ($supergroupname, $object_id);
    if ($object["doctopdf"] == "yes")
    {
      $url = FILES_DocPreviewUrl($supergroupname, $object_id, true);
    }
    else
    {
      $doc = FILES_TrueFileName ($supergroupname, $object_id, "preview");
      $url = IMS_GenerateViewURL ("\\".$thepath, $doc, FILES_ViewExecutable ($supergroupname, $object_id, "preview"), $includesubs=false);
    }
  }
  return $url;
}

function FILES_TransViewPublishedURL ($supergroupname, $object_id)
{
  global $files_testmode;
  if ($files_testmode == "yes") {
    return "FILES_TransViewPublishedURL ($supergroupname, $object_id)";
  }

//20110928 KVD CORE-23
  if (@$GLOBALS["myconfig"][$supergroupname]["viewdocnotrans"]) {
    $url = FILES_FilenameURL($supergroupname, $object_id, false);
    if ($url) return $url;
  }
///
  $object = &IMS_AccessObject ($supergroupname, $object_id);
  if ($object["published"]!="yes") return null;
  $thepath = $supergroupname."\\objects\\".$object_id."\\";
  if (strpos (FILES_TrueFileName ($supergroupname, $object_id, "published"), ".imsctn.txt")) {
    if(N_UseAdvancedTransferAgent()) {
      $object = &IMS_AccessObject ($supergroupname, $object_id);
      $doc = FILES_TrueFileName ($supergroupname, $object_id, "published");
      $path = "\\".$thepath;
      $temppath = "temp::".$thepath;
      global $myconfig;
      if ($myconfig[IMS_SuperGroupName()]["imstrans"]) {
        $imstrans = $myconfig[IMS_SuperGroupName()]["imstrans"];
        if (substr ($imstrans, strlen($imstrans)-1, 1)=="\\") {
          $imstrans = substr ($imstrans, 0, strlen($imstrans)-1);
        }
      } else {
        $imstrans = "c:\\imstrans";
      }
      $tag = N_KeepAfter ($imstrans, "\\");
      $commands = array (
        "1_command" => "deletedir",
        "1_dir" => "$imstrans",
    
        "2_command" => "download",
        "2_dir" => $path,
        "2_file" => $doc,
        "2_subs" => "true",
    
        "3_command" => "copydir",
        "3_from" => $temppath,
        "3_to" => $imstrans,
    
        "4_command" => "start",
        "4_doc" => $imstrans,
        "4_title1" => $tag,
      );
      $url = IMS_GenerateAdvancedTransferURL ($commands);
    } else {
      $url = FORMS_URL (array ("formtemplate"=>'
              <table>
              <tr><td><font face="arial" size=2><b>'.ML("Containers worden op dit platform niet ondersteund", "Containers are not supported on this platform").'</b></font></td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
              </table>
            '));
    }
  } else if (strpos (FILES_TrueFileName ($supergroupname, $object_id, "published")."#@!", ".fm#@!")) {
    $object = &IMS_AccessObject ($supergroupname, $object_id);
    $doc = FILES_TrueFileName ($supergroupname, $object_id, "published");
    $url = IMS_GenerateViewURL ("\\".$thepath, $doc, "FrameMaker.exe", $includesubs=false);
  } else {
    $object = &IMS_AccessObject ($supergroupname, $object_id);
    $doc = FILES_TrueFileName ($supergroupname, $object_id, "published");
    $url = IMS_GenerateViewURL ("\\".$thepath, $doc, FILES_ViewExecutable ($supergroupname, $object_id, "published"), $includesubs=false);
  }
  return $url;
}

function FILES_TransEditURL ($supergroupname, $object_id)
{
  global $files_testmode;
  if ($files_testmode == "yes") {
    return "FILES_TransEditURL ($supergroupname, $object_id)";
  }
  $thepath = $supergroupname."\\preview\\objects\\".$object_id."\\";
  if (strpos (FILES_TrueFileName ($supergroupname, $object_id, "preview"), ".imsctn.txt")) {
    if(N_UseAdvancedTransferAgent()) {
      $object = &IMS_AccessObject ($supergroupname, $object_id);
      $doc = FILES_TrueFileName ($supergroupname, $object_id, "preview");
      $path = "\\".$thepath;
      $temppath = "temp::".$thepath;
      global $myconfig;
      if ($myconfig[IMS_SuperGroupName()]["imstrans"]) {
        $imstrans = $myconfig[IMS_SuperGroupName()]["imstrans"];
        if (substr ($imstrans, strlen($imstrans)-1, 1)=="\\") {
          $imstrans = substr ($imstrans, 0, strlen($imstrans)-1);
        }
      } else {
        $imstrans = "c:\\imstrans";
      }
      $tag = N_KeepAfter ($imstrans, "\\"); // qqq
      $commands = array (
        "1_command" => "download",
        "1_dir" => $path,
        "1_file" => $doc,
        "1_subs" => "false",

        "2_command" => "lock",
        "2_dir" => $path,

        "3_command" => "download",
        "3_dir" => $path,
        "3_file" => $doc,
        "3_subs" => "true",

        "4_command" => "deletedir",
        "4_dir" => "$imstrans",
    
        "5_command" => "copydir",
        "5_from" => $temppath,
        "5_to" => $imstrans,
    
        "6_command" => "start",
        "6_doc" => $imstrans,
        "6_title1" => $tag,

        "7_command" => "showmessage",
        "7_message" => ML("Druk op OK als u klaar bent met de container","Press OK if you are ready with the container"),
    
        "8_command" => "deletedir",
        "8_dir" => $temppath,
    
        "9_command" => "copydir",
        "9_to" => $temppath,
        "9_from" => $imstrans,
    
        "10_command" => "upload",
        "10_dir" => $path,
        "10_file" => $doc,
        "10_subs" => "true",

        "11_command" => "unlock",
        "11_dir" => $path,
      );
      $url = IMS_GenerateAdvancedTransferURL ($commands);
    } else {
      $url = FORMS_URL (array ("formtemplate"=>'
              <table>
              <tr><td><font face="arial" size=2><b>'.ML("Containers worden op dit platform niet ondersteund", "Containers are not supported on this platform").'</b></font></td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
              </table>
            '));
    }
  } else if (strpos (FILES_TrueFileName ($supergroupname, $object_id, "preview")."#@!", ".fm#@!")) {
    $object = &IMS_AccessObject ($supergroupname, $object_id);
    $doc = FILES_TrueFileName ($supergroupname, $object_id, "preview");
    $url = IMS_GenerateEditURL ("\\".$thepath, $doc, "FrameMaker.exe", $includesubs=false);
  } else {
    $object = &IMS_AccessObject ($supergroupname, $object_id);
    $doc = FILES_TrueFileName ($supergroupname, $object_id, "preview");
    $includesubs = false;
    if (FILES_AllowedFiletypes(FILES_FileType($supergroupname, $object_id, "preview"), true)) {
      // if the current file type (e.g. "doc") is allowed to be replaced with different file types, tell
      // the transfer agent to upload (and also download) the entire directory, so that we can detect if 
      // the document is saved in a different file type.
      // Note that $includesubs includes files as well as subdirectories.
      $includesubs = true;
    }
    $url = IMS_GenerateEditURL ("\\".$thepath, $doc, FILES_EditExecutable ($supergroupname, $object_id), $includesubs);   
  }
  return $url;
}

function FILES_Comparable ($supergroupname, $object_id)
{
  FLEX_LoadSupportFunctions (IMS_SuperGroupName());

  if (!function_exists ("FILES_SpecialCompare")) {
   // DMS special files (default)
   $internal_component = FLEX_LoadImportableComponent ("support", "f56996e35ef98d2f15f2310e62cc75a8");
   $internal_code = $internal_component["code"];
   eval ($internal_code);
  }

  global $myconfig;
  $thedoctype = FILES_FileType ($supergroupname, $object_id, "preview"); // We can't know at this point which versions the user will compare. Show the interface anyway, and show an error later if two versions in different file formats are being compared.
  // Check that doc / ppt text versions originate from proper tools and not from SEARCH_Any2Text, because
  // SEARCH_Any2Text output is useless for diff'ing.
  if (($thedoctype=="doc" && $myconfig["antiword"]) || ($thedoctype=="ppt" && $myconfig["ppthtml"]) || $thedoctype=="pdf" || $thedoctype=="txt" || $thedoctype=="xml" ||
      $thedoctype=="odt" || $thedoctype=="ods" || $thedoctype=="odp" || $thedoctype=="php" || $thedoctype=="css" || $thedoctype=="js" || 
      $thedoctype=="rtf" || $thedoctype=="docx" || $thedoctype=="pptx"
      ) {
    return true;
  }
  if (FILES_SpecialCompare ($supergroupname, $object_id)) {
    return true;
  }
  return false;
}

function FILES_DocHistoryURL ($supergroupname, $object_id, $v)
{
  global $files_testmode;
  if ($files_testmode == "yes") {
    return "FILES_DocHistoryURL ($supergroupname, $object_id, $v)";
  }  

  if (strpos (FILES_TrueFileName ($supergroupname, $object_id, $v), ".imsctn.txt")) {
    return null;
  }
  
  $keyForFileName = $object_id;
  
  if (FILES_IsPermalink($supergroupname, $object_id)) {
  	$object = IMS_AccessObject ($supergroupname, $object_id);
	// verandert de object_id naar de key van het bron bestand i.p.v. de key van de permalink
  	$object_id = $object["source"];
  }
  
  $object = &IMS_AccessObject ($supergroupname, $object_id);
  if ($object["preview"]=="yes" || $object["published"]=="yes") { 
    $doc = FILES_VisibleFileName ($supergroupname, $keyForFileName, false, $v);
    global $myconfig;
    if($myconfig["useoldstyleufc"]=="yes") { // use old style ufc without username
      return UFC_MakeSecure ("/ufc/file/$supergroupname/$object_id/$v/$doc");
    } else {
      return UFC_MakeSecure ("/ufc/file2/$supergroupname/".SHIELD_CurrentUser()."/$object_id/$v/$doc");
    }
  } else {
    return null;
  }
}


// might return "", e.g. if object consists of multiple files
function FILES_DocPreviewURL ($supergroupname, $object_id, $pdf=false)
{
  global $files_testmode;
  if ($files_testmode == "yes") {
    return "FILES_DocPreviewURL ($supergroupname, $object_id)";
  }

  if (strpos (FILES_TrueFileName ($supergroupname, $object_id, "preview"), ".imsctn.txt")) {
    return null;
  }
  $object = &IMS_AccessObject ($supergroupname, $object_id);
  if ($object["preview"]=="yes" || $object["published"]=="yes") { 
    global $myconfig;
    if($myconfig["useoldstyleufc"]=="yes") { // use old style ufc without username
      if ($pdf) {
        $doc = FILES_VisibleFileName ($supergroupname, $object_id, true, "preview");
        return "/ufc/file/$supergroupname/$object_id/prpdf/$doc";
      } else {
        $doc = FILES_VisibleFileName ($supergroupname, $object_id, false, "preview");
        return "/ufc/file/$supergroupname/$object_id/pr/$doc";
      }
    } else {
      if ($pdf) {
        $doc = FILES_VisibleFileName ($supergroupname, $object_id, true, "preview");
        if ($pdf==="html") {
          $mid = "prhtml";
          $doc = "page.html";
        } else {
          $mid = "prpdf";
        }
//        switch($pdf) {
//        case 1:
//          $mid = "prpdf";
//          break;
//        case "html":
//          $mid = "prhtml";
//          $doc = "page.html";
//          break;
//        default:
//          $mid = "prpdf";
//          break;
//        }
        return UFC_MakeSecure ("/ufc/file2/$supergroupname/".SHIELD_CurrentUser()."/$object_id/" . $mid . "/$doc");
      } else {
        $doc = FILES_VisibleFileName ($supergroupname, $object_id, false, "preview");
        return UFC_MakeSecure ("/ufc/file2/$supergroupname/".SHIELD_CurrentUser()."/$object_id/pr/$doc");
      }
    }
  } else {
    return null;
  }
}

// might return "", e.g. if object consists of multiple files
function FILES_DocPublishedURL ($supergroupname, $object_id, $pdf=false, $skippublishedcheck=false)
{
  global $files_testmode;
  if ($files_testmode == "yes") {
    return "FILES_DocPublishedURL ($supergroupname, $object_id)";
  }

  if (strpos (FILES_TrueFileName ($supergroupname, $object_id, "published"), ".imsctn.txt")) {
    return null;
  }
  $object = &IMS_AccessObject ($supergroupname, $object_id);
  if (($object["published"]=="yes") || ($skippublishedcheck==true)) {  
    global $myconfig;
    if($myconfig["useoldstyleufc"]=="yes") { // use old style ufc without username
      if ($pdf) {
        $doc = FILES_VisibleFileName ($supergroupname, $object_id, true, "published");
        return "/ufc/file/$supergroupname/$object_id/pupdf/$doc";
      } else {
        $doc = FILES_VisibleFileName ($supergroupname, $object_id, false, "published");
        return "/ufc/file/$supergroupname/$object_id/pu/$doc";
      }
    } else {
      if ($pdf) {
        $doc = FILES_VisibleFileName ($supergroupname, $object_id, true, "published");

        if ($pdf==="html") {
          $mid = "puhtml";
          $doc = "page.html";
        } else {
          $mid = "pupdf";
        }
//        switch($pdf) {
//        case 1:
//          $mid = "pupdf";
//          break;
//        case "html":
//          $mid = "puhtml";
//          $doc = "page.html";
//          break;
//        default:
//          $mid = "pupdf";
//          break;
//        }
        return UFC_MakeSecure ("/ufc/file2/$supergroupname/".SHIELD_CurrentUser()."/$object_id/" . $mid . "/$doc");
      } else {
        $doc = FILES_VisibleFileName ($supergroupname, $object_id, false, "published");
        return UFC_MakeSecure ("/ufc/file2/$supergroupname/".SHIELD_CurrentUser()."/$object_id/pu/$doc");
      }
    }
  } else {
    return null;
  }
}

function FILES_PublishedFilelocation ($supergroupname, $object_id)
{
  $object = &IMS_AccessObject ($supergroupname, $object_id);

  if ($object["published"]=="yes") {
    $thepath = $supergroupname."\\objects\\".$object_id."\\";
  } else {
    return "";
  }

  $doc = FILES_TrueFileName ($supergroupname, $object_id, "published");
  $path = "\\".$thepath;

  return N_CleanPath ("html::"."\\".$path. "\\" . $doc);
}

function FILES_Filelocation ($supergroupname, $object_id)
{
  $object = &IMS_AccessObject ($supergroupname, $object_id);

  if ($object["preview"]=="yes") {
    $thepath = $supergroupname."\\preview\\objects\\".$object_id."\\";
    $doc = FILES_TrueFileName ($supergroupname, $object_id, "preview");
  } else {
    $thepath = $supergroupname."\\objects\\".$object_id."\\";
    $doc = FILES_TrueFileName ($supergroupname, $object_id, "published");
  }

  $path = "\\".$thepath;

  return N_CleanPath ("html::".$path. "\\" . $doc);
}

function FILES_PreviewFilelocation ($supergroupname, $object_id)
{
  $thepath = $supergroupname."\\preview\\objects\\".$object_id."\\";

  $doc = FILES_TrueFileName ($supergroupname, $object_id, "preview");
  $path = "\\".$thepath;

  return N_CleanPath ("html::".$path. "\\" . $doc);
}

function FILES_Test ($supergroupname="", $object_id="")
{
  FLEX_LoadSupportFunctions (IMS_SuperGroupName());

  if (!function_exists ("FILES_SpecialCompare")) {
    // DMS special files (default)
    $internal_component = FLEX_LoadImportableComponent ("support", "f56996e35ef98d2f15f2310e62cc75a8");
    $internal_code = $internal_component["code"];
    eval ($internal_code);
  }

  global $files_testmode;
  $files_testmode = "no";
  if (!$supergroupname) {
    FILES_Test ("demo_sites", "f82789628d85da456c216d00f0eb5bad");
    FILES_Test ("demo_sites", "9653b641f0acd45c9443fc4ead57e576");
    FILES_Test ("demo_sites", "64d85b1bb54ce118e1a28adda0d52218");
  } else {
    echo "FILES_Test ($supergroupname, $object_id)<br>";
    echo "FILES_TrueFileName: ".N_XML2HTML (FILES_TrueFileName ($supergroupname, $object_id))."<br>";
    echo "FILES_VisibleFileName: ".N_XML2HTML (FILES_VisibleFileName ($supergroupname, $object_id))."<br>";
    echo "FILES_FileType: ".N_XML2HTML (FILES_FileType ($supergroupname, $object_id))."<br>";
    echo "FILES_FileExt: ".N_XML2HTML (FILES_FileExt ($supergroupname, $object_id))."<br>";
    echo "FILES_Icon: ".N_XML2HTML (FILES_Icon ($supergroupname, $object_id))."<br>";
    echo "FILES_DMSURL: ".N_XML2HTML (FILES_DMSURL ($supergroupname, $object_id))."<br>";
    echo "FILES_TransViewPreviewURL: ".N_XML2HTML (FILES_TransViewPreviewURL ($supergroupname, $object_id))."<br>";
    echo "FILES_TransViewPublishedURL: ".N_XML2HTML (FILES_TransViewPublishedURL ($supergroupname, $object_id))."<br>";
    echo "FILES_TransEditURL: ".N_XML2HTML (FILES_TransEditURL($supergroupname, $object_id))."<br>";
    echo "FILES_DocPreviewURL: ".N_XML2HTML (FILES_DocPreviewURL($supergroupname, $object_id))."<br>";
    echo "FILES_DocPublishedURL: ".N_XML2HTML (FILES_DocPublishedURL($supergroupname, $object_id))."<br>";
    echo "FILES_Comparable: ".N_XML2HTML (FILES_Comparable ($supergroupname, $object_id))."<br>";
    echo "FILES_SpecialCompare:<br>";
    N_EO (FILES_SpecialCompare ($supergroupname, $object_id, "versie1", "versie2"));
    echo "FILES_SpecialView:<br>";
    N_EO (FILES_SpecialView ($supergroupname, $object_id));
    echo "FILES_SpecialEdit:<br>";
    N_EO (FILES_SpecialEdit ($supergroupname, $object_id));
  }
}

function FILES_TransDirectAccess ($supergroupname, $object_id)
{
  $thepath = $supergroupname."\\preview\\objects\\".$object_id."\\";
  $path = "\\".$thepath;
  $temppath = "temp::".$thepath;
  $object = &IMS_AccessObject ($supergroupname, $object_id);
  $doc = FILES_TrueFileName ($supergroupname, $object_id, "preview");
  $url = IMS_GenerateEditURL ("\\".$thepath, $doc, FILES_EditExecutable ($supergroupname, $object_id), $includesubs=false);

  global $myconfig;
  if ($myconfig[IMS_SuperGroupName()]["imstrans"]) {
    $imstrans = $myconfig[IMS_SuperGroupName()]["imstrans"];
    if (substr ($imstrans, strlen($imstrans)-1, 1)=="\\") {
      $imstrans = substr ($imstrans, 0, strlen($imstrans)-1);
    }
  } else {
    $imstrans = "c:\\imstrans";
  }
  $tag = N_KeepAfter ($imstrans, "\\");
  $commands = array (
    "1_command" => "deletedir",
    "1_dir" => "$imstrans",
  
    "2_command" => "download",
    "2_dir" => $path,
    "2_file" => $doc,
    "2_subs" => "true",
  
    "3_command" => "copydir",
    "3_from" => $temppath,
    "3_to" => $imstrans,
    
    "4_command" => "start",
    "4_doc" => $imstrans,
    "4_title1" => $tag,

    "5_command" => "showmessage",
    "5_message" => ML("Druk op OK als de folder klaar is voor het uploaden","Press OK if you are ready to upload the folder"),
    
    "6_command" => "deletedir",
    "6_dir" => $temppath,
    
    "7_command" => "copydir",
    "7_to" => $temppath,
    "7_from" => $imstrans,
    
    "8_command" => "upload",
    "8_dir" => $path,
    "8_file" => $doc,
    "8_subs" => "true"
  );
  $url = IMS_GenerateAdvancedTransferURL ($commands);
  return $url;
}

function FILES_TransAssetDIRURL ($supergroupname, $asset_id)
{
  $thepath = $supergroupname."\\assets\\".$asset_id."\\";
  $doc = "keepme.imsctn.txt";
  $path = "\\".$thepath;
  $temppath = "temp::".$thepath;
  global $myconfig;
  if ($myconfig[IMS_SuperGroupName()]["imstrans"]) {
    $imstrans = $myconfig[IMS_SuperGroupName()]["imstrans"];
    if (substr ($imstrans, strlen($imstrans)-1, 1)=="\\") {
      $imstrans = substr ($imstrans, 0, strlen($imstrans)-1);
    }
  } else {
    $imstrans = "c:\\imstrans";
  }
  $tag = N_KeepAfter ($imstrans, "\\");
  $commands = array (
    "1_command" => "deletedir",
    "1_dir" => "$imstrans",
  
    "2_command" => "download",
    "2_dir" => $path,
    "2_file" => $doc,
    "2_subs" => "true",
  
    "3_command" => "copydir",
    "3_from" => $temppath,
    "3_to" => $imstrans,
    
    "4_command" => "start",
    "4_doc" => $imstrans,
    "4_title1" => $tag,

    "5_command" => "showmessage",
    "5_message" => ML("Druk op OK als de folder klaar is voor het uploaden","Press OK if you are ready to upload the folder"),
    
    "6_command" => "deletedir",
    "6_dir" => $temppath,
    
    "7_command" => "copydir",
    "7_to" => $temppath,
    "7_from" => $imstrans,
    
    "8_command" => "upload",
    "8_dir" => $path,
    "8_file" => $doc,
    "8_subs" => "true"
  );
  $url = IMS_GenerateAdvancedTransferURL ($commands);
  return $url;
}

function FILES_ThumbLocation($supergroupname,$object_id,$xmax=100,$ymax=100)
{
  global $myconfig;
  if ($myconfig[$supergroupname]["nostaticthumbs"] == "yes") {
    return "/ufc/thumb/$supergroupname/$object_id/$xmax/$ymax/image.jpg";
  } else {
    // Add timestamp to static thumb url (so that, if an image changes, 
    // so does the url, so that a new static thumb will be generated automatically)
    $object = MB_Ref("ims_".$supergroupname."_objects", $object_id);
    $time = QRY_DMS_Changed_v1($object);
    return "/ufc/static/$time/thumb/$supergroupname/$object_id/$xmax/$ymax/image.jpg";
  }
}

function FILES_HistoryVersionExistsOnDisk($sgn,$object_id,$history_id) {
    $thepath = "html::/".$sgn."/objects/history/".$object_id."/".$history_id."/";
    $thedoc =  FILES_TrueFileName ($sgn, $object_id,$history_id);
    return N_FileExists($thepath.$thedoc);
}

function FILES_TemplateHistoryVersionExistsOnDisk($sgn,$template_id,$history_id) {  
    $thepath = "html::/".$sgn."/templates/history/".$template_id."/".$history_id."/";  
    $thedoc = "template.html";  
    return N_FileExists($thepath.$thedoc);  
}



function FILES_AllowedFiletypes($currentfiletype, $transferagent = false) {
  // Returns array of filetypes that are allowed substitutions for $currentfiletype. Return value does not include $currentfiletype
  global $myconfig;
  if ($myconfig[IMS_SuperGroupName()]["allowofficedoctypechange"] == "yes") {
    if ($transferagent) {
      /* TRANSFER AGENT PROBLEMS: 
         The following failures occur: (transfer agent becomes active prematurely and uploads a 0 byte file)
           Word 2003: if another instance of Word was already active (which can be running inside Outlook)
           Excel 2007: always fails
           Powerpoint 2007: fails if another instance of Word was already active
         The following have been testen succesfully;
           Word 2007
           Word 2010
         These problems can not be fixed server side, but they might be fixable by adding extra lock-checking functionality to the transfer agent.

         Conclusion: only allow doc to docx/docm conversion (and no others). In upload dialogue, allow all the other conversions.

       */
      if ($currentfiletype == "doc") {
        return array("docx", "docm");
      } else {
        return array();
      }
    } else {
      $clusters = array(
        array("doc", "docx", "docm"),
        array("xls", "xlsx", "xlsm"),
        array("ppt", "pptx", "pptm"),
      );

      foreach ($clusters as $cluster) {
        if (in_array($currentfiletype, $cluster)) {
          return array_values(array_diff($cluster, array($currentfiletype)));    
        }
      }
    }
  }
  return array();
}

function FILES_HandleFilenameChange($sgn, $object_id, $newfilename) {
  // This function should be called AFTER the new file has been placed in the preview directory,
  // before archiving the new version.
  // The document may have been created with an older version of IMS_PublishObject or IMS_ArchiveObject
  // that does not keep track of filename or executable. In such cases, the historic/published state
  // is assumed to be identical to the preview state. This function, by changing the preview state, violates
  // that assumption, therefore it needs to reconstruct and do what IMS_PublishObject/IMS_ArchiveObject do.

  $object = MB_Load("ims_{$sgn}_objects", $object_id);

  // Add the old filename to all history entries without filename that point to an actual file on disk
  foreach ($object["history"] as $huid => $hdata) {
    if (($hdata["type"]=="" || $hdata["type"]=="edit" || $hdata["type"]=="new") && !$hdata["filename"]) {
      $object["history"][$huid]["filename"] = $object["filename"];
      $object["history"][$huid]["executable"] = $object["executable"];
    }
  }

  // Save old filename for published version
  if ($object["published"] == "yes" && !$object["pub"]["filename"]) {
    $object["pub"]["filename"] = $object["filename"];
    $object["pub"]["executable"] = $object["executable"];
  }
  
  // Set new executable.
  $ext = FILES_FileType($newfilename);
  if ($ext=="doc") {
    $object["executable"]="winword.exe";
  } else if ($ext=="xls") {
    $object["executable"]="excel.exe";
  } else if ($ext=="ppt") {
    $object["executable"]="powerpnt.exe";
  } else {
    $object["executable"] = "auto"; // let windows determine the proper executable
  }

  // Change (preview) filename
  $object["filename"] = $newfilename;
  
  // Do NOT delete the old filename from the preview dir. The transfer agent will just keep reuploading it from local cache.

  // Save changes
  MB_Save("ims_{$sgn}_objects", $object_id, $object);
}

function FILES_DocPermanentVersionURL($supergroupname, $object_id) {
// gv 17-08-2012
// this will produce a URL to the current (and latest) version of a document (lets say v0.15) 
// and will forever link to that version (v0.15) of the document.

  $object = MB_Ref("ims_".$supergroupname."_objects", $object_id);
  reset ($object["history"]);
  $goodkey = "";
  while (list($verkey, $data)=each($object["history"])) {
    if ($data["type"]=="new" || $data["type"]=="edit") {
      $goodkey = $verkey;
    }
  }
  return FILES_DocHistoryURL ($supergroupname, $object_id, $goodkey);
}



?>