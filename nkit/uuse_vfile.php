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



function VFILE_Signal ($file)
{
  if (strpos ($file, "local_sites\\vfiles")) {
    $id = N_KeepBefore (N_KeepAfter ($file, "vfiles\\"), "\\");
    N_Log ("vfile", "VFILE_Signal: $id");
    $specs = MB_Load ("local_vfiles", $id);
    if ($specs["file"]) {
      N_Eval ($specs["handleupdate"], $ser = array ("input"=>$specs["input"], "file"=>$specs["file"], "dir"=>N_CleanPath ("html::\\local_sites\\vfiles\\$id\\")));
    }
    return true;
  }
  return false;
}

function VFILE_Dir ($dir, $file)
{
  if (strpos ($dir, "local_sites\\vfiles")) {
    $id = N_KeepBefore (N_KeepAfter ($dir, "vfiles\\"), "\\");
    N_Log ("vfile", "VFILE_Dir: $id");
    $specs = &MB_Ref ("local_vfiles", $id);
    if ($specs["file"] && !$specs["madefile"]) {
      $specs["madefile"] = time();
      MB_Flush();      
      N_Eval ($specs["filldirectory"], $ser = array ("input"=>$specs["input"], "file"=>$specs["file"], "dir"=>N_CleanPath ("html::$dir")));
      N_Log ("vfile", "filldirectory ".serialize ($ser));
    }
  }
}

function VFILE_Cleanup ()
{
  $list = MB_TurboMultiQuery ("local_vfiles", array (
    "range" => array ('$record["madelink"]', 0, time()-24*3600)
  ));
  foreach ($list as $id) {
    N_log ("vfile", "REMOVE $id");
    MB_Delete ("local_vfiles", $id);
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure (N_CleanPath ("html::\\local_sites\\vfiles\\$id\\"));
    @rmdir (N_CleanPath ("html::\\local_sites\\vfiles\\$id\\"));
  }
}

function VFILE_Create ($specs, $includesubs=false, $editmode=true)
{
  $specs["madelink"] = time();
  MB_Save ("local_vfiles", $guid = N_GUID(), $specs);
  $editor = "auto";
  $ext = strtolower(strrev(N_KeepBefore (strrev($specs["file"]), ".")));
  if ($ext=="doc") {
    $editor = "winword.exe";
  } else if ($ext=="xls") {
    $editor = "excel.exe";
  } else if ($ext=="htm" || $ext=="html") {
    $editor = "winword.exe";
  } else if ($ext=="ppt") {
    $editor = "powerpnt.exe";
  }
  return IMS_GenerateTransferURL  ("\\local_sites\\vfiles\\$guid\\", $specs["file"], $editor, $includesubs, $editmode);
}

function VFILE_Test ()
{
  $specs["file"] = "test.txt";
  $specs["filldirectory"] = '
    N_WriteFile ($dir.$file, "ABC");
  ';  
  $specs["handleupdate"] = '
    N_Log ("vfile", "READ FILE: ".N_ReadFile ($dir.$file));
  ';
  $url = VFILE_Create ($specs);
  echo "<a href=\"".$url."\">vfile test</a> $url";
}

?>