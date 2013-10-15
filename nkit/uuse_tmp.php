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



function TMP_Slot()
{
  global $tmpslot, $knowntmpslot;
  if (!$knowntmpslot) {
    $knowntmpslot = true;
    $tmpslot = (int)(time()/(3600*24)) % 14;
  }
  return $tmpslot;
}

// Return a directory which will be automatically deleted in a few days
function TMP_Directory ()
{
  $dir = TMP_DIR()."/".N_GUID();
  N_MkDir ($dir);
  return $dir;
}

function TMP_FindDirectory ($id)
{
  $dir = TMP_DIR()."/".$id;
  if (file_exists($dir) && is_dir($dir)) {
    return $dir;
  } else {
    for ($i=0; $i<14; $i++) {
      $dir = TMP_DIR($i)."/".$id;
      if (file_exists($dir) && is_dir($dir)) {
        return $dir;
      }
    }
  }
  return false;
}

function TMP_DIR($slot="fasdlfhdlskfjhldaskf")
{
  global $myconfig;
  if ($slot=="fasdlfhdlskfjhldaskf") $slot = TMP_Slot();
  $tmproot = N_CleanPath ($myconfig["tmp"]."\\$slot");
  if (!file_exists ($tmproot."/tmpobjects")) {
    N_MkDir ($tmproot);
    N_MkDir ($tmproot."/dfc");
    N_MkDir ($tmproot."/locks");
    N_MkDir ($tmproot."/encoded");
    N_MkDir ($tmproot."/tmpobjects");
  }
  return $tmproot;
}

function TMP_CleanUp($all=false)
{
  global $myconfig;// JG - DZ-191
  if ($all == "all") {  // 20101108 KvD Clean ALL
    $dir = getenv("DOCUMENT_ROOT")."/tmp";
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($dir, array("logging","import"));
    if ($myconfig["tmp"])  // also the other tmp if available
      MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(N_CleanPath ($myconfig["tmp"]), array("logging","import"));
    N_WriteFile (getenv("DOCUMENT_ROOT")."/tmp/locks/dummy.txt", "dummy");
    N_WriteFile ("html::/tmp/logging/.htaccess", "deny from all".chr(13).chr(10)); 
    N_WriteFile ("html::/tmp/flexcache/.htaccess", "deny from all".chr(13).chr(10)); 
    N_WriteFile ("html::/tmp/myconfig/.htaccess", "deny from all".chr(13).chr(10)); 
  } else {
//    global $myconfig;
    $oldslot = (int)(time()/(3600*24)+2) % 14;
    $oldtmproot = N_CleanPath ($myconfig["tmp"]."\\$oldslot");
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure ($oldtmproot);
  } 
}

function TMP_SaveObject ($id, $object, $class="default")
{
  global $tmpcache;
  $id = $class.md5($id);
  for ($i=0; $i<14; $i++) {
    if (file_exists (TMP_DIR($i)."/tmpobjects/".$id.".phpdat")) {
      unlink (TMP_DIR($i)."/tmpobjects/".$id.".phpdat");
    }      
  }
  N_QuickWriteFile (TMP_DIR()."/tmpobjects/".$id.".phpdat", serialize ($object)); // qqq
  $tmpcache[$id] = $object;
  if (count($tmpcache)>100) $tmpcache = array(); // limit memory usage
  return $object;
}


function TMP_LoadObject ($id, $nocache=false, $class="default")
{
  global $tmpcache;
  $id = $class.md5($id);
  if (!$nocache && $tmpcache[$id]) return $tmpcache[$id];
  $object = null;
  if (file_exists (TMP_DIR()."/tmpobjects/".$id.".phpdat")) {
    $object = unserialize (N_ReadFile (TMP_DIR()."/tmpobjects/".$id.".phpdat"));
  } else {
    for ($i=0; $i<14; $i++) {
      if (file_exists (TMP_DIR($i)."/tmpobjects/".$id.".phpdat")) {
        $object = unserialize (N_ReadFile (TMP_DIR($i)."/tmpobjects/".$id.".phpdat"));
      }
    }
  }
  $tmpcache[$id] = $object;
  if (count($tmpcache)>100) $tmpcache = array(); // limit memory usage
  return $object;
}

?>