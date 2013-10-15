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



/*

*** VVFILE: File I/O virtualisation layer, used for delayed copy / delayed modification functionality ***

Convention:
   There is always a placeholder (e.g. DelayedCopy and Alter(?) create one).
   Timestamp of placeholder is always correct (e.g. AlterFile immediately changes it, and MakeUp2Date keeps it unchanged). 

Function usage:
   VVFILE_ForgetFile: used right before WriteFile/DeleteFile, target voor copy etc.
   VVFILE_MakeUp2Date: used right before ReadFile etc.   

Locking:
  N_LockFile, N_UnLockFile (nestbaar, 0.79ms)
     N_TrueAppendFile (not quick)
  N_Lock, N_UnLock (niet nestbaar, lock+unlock 0.44ms)
    N_TrueReadFile (retry on empty file only)
    N_DoWriteFile (uselocking is true, default) om aanroep truerename heen

Problem scenarios:
  Switch from regular file to vfile while executing, VVFILE_MakeVRef:
    N_AppendFile
    N_WriteFilePart (wellicht trans met tijdelijke folder en met signal copieren naar goede plek)
    Switch from regular file to vfile (covers copyfile), VVFILE_MakeVRef
    VVFILE_ReadFile
    VVFILE_ReadFilePart
    VVFILE_FileMD5
    VVFILE_FileSize
    VVFILE_FileMD5B
    VVFILE_FilenameForRead
    VVFILE_AlterFile (if it switches very rapidly)
  Switch from vfile to refular file, or replace vfile with regular file while executing:
    Switch from vfile to refular file (e.g. 2 processes simul executing alters)
    Appending extra change to vfile (VVFILE_AlterFile) d.m.v. N_NowAppendFile
    VVFILE_FilenameForRead   
  Remarks
    N_DoWriteFile does use locking
    VVFILE_DeleteFile (solved by append/filepart solution)
    VVFILE_CopyFile (solved by switch from regular to vfile, VVFILE_MakeVRef)
    VVFILE_IsVRef, VVFILE_IsModifiedVRef returns temp answer
    Lock bij maken vref is te kort voor lock readmechanisme
    Uitzoeken of locking nestbaar is
  Solutions V1:
    voor alle read functies check of status ongewijzigd is zoniet recursie
    After make vref check if immutable is vref if yes solve (low level copy vref) keep solving until immutable file or overload
    VVFILE_MakeVRef moet atomair worden (zelf locking als rename)
    low level append check if vref after fopen has taken place (can be other way around)
    low level write file part check if vref after fopen has taken place
    vref -> regular middels tmp file
    VVFILE_MakeUp2Date middels read gehele vref ontwijken aanpasisngen aan vref, middels temp file ontwijken wijzigingen aan output
    VVFILE_MakeUp2Date extra param voor alters
  Solutions V2:
x   N_Lock nestbaar maken
x   N_Lock op ALLE (dus ook delete) write operaties binnen vvfile
x   Bij read status vooraf en status achteraf vergelijken, bij veschil of lege data locken en operatie herhalen (recursief)
x   Goed volgorde locks in de gaten houden bij copyfile ivm deadlocks
?   Append dmv read+write voor vreffable files
x   Check file change: size + first 64 bytes
x   vref -> regular middels tmp file
x   alter op bestaand bestand zonder vref middels tijdelijke rename
x   low level read file part locking als bestand leeg is o.i.d. (testen!!!)

Full list:
  N_WriteFile          OK forget
  N_DeleteFile         OK forget
  N_AppendFile         OK up2date
  N_WriteFilePart      OK up2date
  N_ReadFile           OK up2date, or simulate 
  N_ReadFilePart       OK up2date, or simulate
  N_FileSize           OK up2date, or simulate
  N_QuickFileSize      OK determine size without applying pending updates
  N_FileMD5            OK up2date, or simulate 
  N_FileInfo           NVT up2date, or simulate
  N_FileTime           NVT direct on dummy file
  N_CopyFile           OK replace with Delayed...
  N_Touch              OK unchanged
  MARKER_...           analyze, rewrite, etc.

Tricks:
   Delay md5 determination as much as possible (e.g. reduce fileinfo calls).
   2 versions of determining file length.
   Internal path is always normalized to OpenIMS internal format with N_InternalPath (html::...) 
     -> not applicable, immutable file objects do not refer to paths, no database etc.
   Subdirectory of metabase is used for storage (vvfile/3xHex/3xHex/26xHex.dat).
   ID of file in storage is NOT md5hash of content, but it is random (to speed up move operation).
   Operations only work for files in ..._sites directories.
   Perhaps determine minimum filesize for special stuff to happen.
   Check aanroepen van N_Tree om te kijken of het ook N_QuickTree mag worden.
   Check aanroepen van N_Filesize of het ook N_QuickFileSize mag zijn worden.
   MARKER_Load moet op een of andere manier niet het bestand uitlezen maar de VRef met eerdere marker? (ANders zowel bij historie als publiceren: marker load)
   Niet vergeten DMS_MouseOver op dev (QuickFileSize).
   Bij verwijderen van een document nog een IMS_SignalDataChange die de marker aanpast. Die zou weg kunnen.
   Full text indexeren: misschien de marker niet indexeren (N_QuickReadFile), metadata gebeurt toch al.

   Log stacktrace van MakeUp2Date om voorbarige aanroepen te troubleshooten.
   Maybe enforce maximum length of modifications specs, so filesize of vref's has upper bound (useful for finding them...)
   Always call functions, config in functions here.
   Alle fopen dingen checken.
     ?? ML_ImportCsv
     OK N_GetPage  url
     OK N_OneTimeRandom
     OK N_Lock / N_LockFile
     OK N_Gunzip  html::/tmp
     OK [metabase] backup create / restore  html::/backups
     OK [context]  N_DNS  url
     OK [events]   TZEB_Debug
     ?? BPMSUIF_ImportExportBlock  upload
     OK [cb] /dev/shm
     TODO webdav / webdavconvert
     OK [sys] backupspel  html::/backups
     OK [stats] /proc/stat
   TODO: ook GD functie-aanroepen checken (uuse_thumb)

   Check for remote::.

Internal file contents:
   Normal (1:1 data)
   Reference (reference to immutable normal file (storage ID based) and optional specification of change (code string))

TODO: iets slims met indexeren

*/

global $VRefMarker, $VRefMinimumFileSize, $VRefMaximumVRefSize;
$VRefMarker = "1f70bee424cb6a75"."a93b2472e082ff51";

$VRefMinimumFileSize = 20000;  // Minimum file size (for smaller files, using VRef's is not worth it)
$VRefMaximumVRefSize = 64000;  // Maximum vref size (helps to find them faster?)

function VVFILE_Checksum ($file)
{
  return "c".(0+N_NowFileSize($file)).N_NowReadFilePart($file, 0, 64);
}

function VVFILE_Lock ($file) // compatible with N_DoWriteFile locking
{
  N_Lock (N_CleanPath ($file));
}

function VVFILE_Unlock ($file) // compatible with N_DoWriteFile locking
{
  N_Unlock (N_CleanPath ($file));
}

function VVFILE_FilenameForRead ($file, $ignoremodifications = false) { // make sure lock is on or changes are detected while this function is called
  /* Returns the file suitable for reading operations (this may be the underlying immutable file object).
   * Side-effect: may make the file up to date (unless $ignoremodifications is true).
   */

  if ($immid = VVFILE_IsVRef($file)) {
    if (!$ignoremodifications && VVFILE_IsModifiedVRef($file)) {
      VVFILE_MakeUp2Date($file);
      return $file;
    } else {
      $immfile = "html::/metabase/vvfile/". substr($immid, 0, 3) . "/" . substr($immid, 3) . ".dat";
      return $immfile;
    }
  } else {
    return $file;
  }  
}

function VVFILE_QuickReadFile ($file)
{
  return N_NowReadFile(VVFILE_FilenameForRead($file, true));
}

function VVFILE_ReadFile ($file)
{
  VVFILE_FilenameForRead($file);
  $check1 = VVFILE_Checksum ($file);
  $result = N_NowReadFile(VVFILE_FilenameForRead($file));
  $check2 = VVFILE_Checksum ($file);
  if ($check1 != $check2) {
    VVFILE_Lock ($file);
    $result = N_NowReadFile(VVFILE_FilenameForRead($file));
    VVFILE_Unlock ($file);
  }
  return $result;
}

function VVFILE_ReadFilePart ($file, $offset, $size)
{
  VVFILE_FilenameForRead($file);
  $check1 = VVFILE_Checksum ($file);
  $result = N_NowReadFilePart(VVFILE_FilenameForRead($file), $offset, $size);
  $check2 = VVFILE_Checksum ($file);
  if ($check1 != $check2 || !strlen($result)) {
    VVFILE_Lock ($file);
    $result = N_NowReadFilePart(VVFILE_FilenameForRead($file), $offset, $size);
    VVFILE_Unlock ($file);
  }
  return $result;
}

function VVFILE_FileMD5 ($file)
{
  VVFILE_FilenameForRead($file);
  $check1 = VVFILE_Checksum ($file);
  $result = N_NowFileMD5(VVFILE_FilenameForRead($file));
  $check2 = VVFILE_Checksum ($file);
  if ($check1 != $check2 || $result=="d41d8cd98f00b204e9800998ecf8427e") { // md5 of empty file
    VVFILE_Lock ($file);
    $result = N_NowFileMD5(VVFILE_FilenameForRead($file));
    VVFILE_Unlock ($file);
  }
  return $result;
}

function VVFILE_FileSize ($file, $quick = false)
{
 if(is_dir(N_CleanPath("html::/metabase/vvfile"))){
   VVFILE_FilenameForRead($file, $quick);
   $check1 = VVFILE_Checksum ($file);
   $result = N_NowFileSize(VVFILE_FilenameForRead($file, $quick));
   $check2 = VVFILE_Checksum ($file);
  if ($check1 != $check2 || $result==0) {
    VVFILE_Lock ($file);
    $result = N_NowFileSize(VVFILE_FilenameForRead($file, $quick));
    VVFILE_Unlock ($file);
   }
    return $result;
  }else {
    return N_NowFileSize($file,$quick);
 }
}


function VVFILE_FileMD5B ($file)
{
  VVFILE_FilenameForRead($file);
  $check1 = VVFILE_Checksum ($file);
  $result = N_NowFileMD5B(VVFILE_FilenameForRead($file));
  $check2 = VVFILE_Checksum ($file);
  if ($check1 != $check2 || $result=="d41d8cd98f00b204e9800998ecf8427e_0") { // md5b of empty file
    VVFILE_Lock ($file);
    $result = N_NowFileMD5B(VVFILE_FilenameForRead($file));
    VVFILE_Unlock ($file);
  }
  return $result;
}

function VVFILE_WriteFile ($file, $content)
{
  VVFILE_Lock ($file);
  if (VVFILE_IsVRef($file)) VVFILE_ForgetFile($file);
  $result = N_NowWriteFile($file, $content);
  VVFILE_Unlock ($file);
  return $result;
}

function VVFILE_WriteFilePart ($file, $offset, $content)
{
  VVFILE_Lock ($file);
  VVFILE_MakeUp2Date ($file);
  $result = N_NowWriteFilePart($file, $offset, $content);
  VVFILE_Unlock ($file);
  return $result;
}

function VVFILE_AppendFile ($file, $content, $quick=false)
{
  // Never log here to prevent detonation
  if (!$quick) VVFILE_Lock ($file);
  VVFILE_MakeUp2Date ($file);
  $result = N_NowAppendFile ($file, $content, $quick);
  if (!$quick) VVFILE_Unlock ($file);
  return $result;
}

function VVFILE_DeleteFile ($file)
{
  VVFILE_Lock ($file);
  if (VVFILE_IsVRef($file)) VVFILE_ForgetFile($file);
  $result = N_NowDeleteFile ($file);
  VVFILE_Unlock ($file);
  return $result;
}

function VVFILE_CopyFile ($dest, $src) 
{
  global $myconfig;

  if (N_CleanPath ($dest) < N_CleanPath ($src)) { // prevent deadlocks with ordering
    VVFILE_Lock ($dest); VVFILE_Lock ($src);
  } else {
    VVFILE_Lock ($src); VVFILE_Lock ($dest);
  }

  // is vvfile switched on?
  if ($myconfig["delayedfilewrite"] == "yes") {
    VVFILE_MakeVRef($src); // Only works if other conditions (not already a vref, in xxx_sites etc.) are met
  } else {
    VVFILE_MakeUp2Date($src);
  }

  if (VVFILE_IsVRef($dest)) VVFILE_ForgetFile($dest);

  if (VVFILE_IsVRef($src) && !VVFILE_CanBecomeVRef($dest, false)) {
    $result = N_NowCopyFile($dest, VVFILE_FilenameForRead ($src)); 
  } else {
    $result = N_NowCopyFile($dest, $src); // if it is a VRef, just copy the raw VRef, not the immutable file behind it
  }

  VVFILE_Unlock ($dest);
  VVFILE_Unlock ($src);
  return $result;
}


function VVFILE_AlterFile ($file, $code, $input = array(), $keepoldtimestamp = false)
{
  /* $code will be evaluated with $input and $file available.
   * $code should be fully deterministic, so no SHIELD_CurrentUser, no time(), etc.
   * $code should uuse() all the required modules.
   * $input should only contain stuff that is really necessarry (no get_defined_vars() please!!!)
   */
  global $myconfig;
  global $VRefMaximumVRefSize;

  VVFILE_Lock ($file);

  $file = N_InternalPath($file);
  $sercode = '$input=unserialize(base64_decode("'.base64_encode(serialize($input)).'"));'.$code;

  // is vvfile switched on?
  if ($myconfig["delayedfilewrite"] == "yes") {
    if (strlen($sercode) >= ($VRefMaximumVRefSize - 64)) {
      VVFILE_MakeUp2Date($file);
    } elseif (VVFILE_IsVRef($file) && strlen($sercode) >= ($VRefMaximumVRefSize - N_NowFileSize($file))) {
      VVFILE_MakeUp2Date($file);
    } else {
      // if $file is not a reference, move it and make a reference out of it
      VVFILE_MakeVRef($file); // Only works if other conditions (not already a vref, in xxx_sites etc.) are met
    }
  } else {
    VVFILE_MakeUp2Date($file);
  }

  if (VVFILE_IsVRef($file)) {
    // append changespec to existing changespec (N_ReallyAppendFile)
    N_NowAppendFile($file, $sercode);
    if (!$keepoldtimestamp) N_Touch($file, time());
  } else {
    $doctype = str_replace ("/", "_", N_KeepAfter($file, ".", true));
    $tmpfile = N_CleanPath ("html::/tmp/".N_GUID()."_vvf.$doctype");
    N_CopyFile ($tmpfile, $file);
    VVFILE_MakeUp2Date ($tmpfile);
    N_Eval($code, array("input" => $input, "file" => N_InternalPath($tmpfile)));
    N_CopyFile ($file, $tmpfile);
    N_DeleteFile ($tmpfile);
  }

  VVFILE_Unlock ($file);

}

function VVFILE_ForgetFile ($file)
{
  #N_Log("vvfile", "VVFILE_ForgetFile($file)");
  // could be useful for future alternate storage systems
}

function VVFILE_MakeUp2Date ($file) //always works even if vvfile is off
{
  // if file is reference replace it with copy of base file and apply changes, keep timestamp unchanged
  VVFILE_Lock ($file);

  $doctype = str_replace ("/", "_", N_KeepAfter($file, ".", true));
  $tmpfile = $file . "_vvf_t3mp_".rand(1000,9999) . ($doctype ? ".".$doctype : "");

  $result = true;

  if ($immid = VVFILE_IsVRef($file)) {
    // TODO: remove this stuff
    #ob_start();
    #N_ShowStack();
    #N_Log("vvfile", "VVFILE_MakeUp2Date: updating $file", ob_get_clean());
    //N_ShowStack();

    $filetime = N_FileTime($file);
    $code = VVFILE_IsModifiedVRef($file);
    $immfile = "html::/metabase/vvfile/". substr($immid, 0, 3) . "/" . substr($immid, 3) . ".dat";


    if ($code) {
      $result = N_NowCopyFile($tmpfile, $immfile);
      global $myconfig;
      $delayedfilewrite = $myconfig["delayedfilewrite"];
      $myconfig["delayedfilewrite"] = "no"; // if $code calls a "delayed" operation, do it immediately

      N_Eval($code, array("file" => N_InternalPath($tmpfile)));

      $myconfig["delayedfilewrite"] = $delayedfilewrite; // restore setting

      N_TrueRename ($tmpfile, $file); // make sure entire operation is atomic

      N_Touch($file, $filetime);
      N_FileInfo($file, true); // refresh fileinfo
    } else {
      $result = N_NowCopyFile($file, $immfile);
      N_Touch($file, $filetime);
      N_FileInfo($file); // refresh fileinfo
    }
  } 

  VVFILE_Unlock ($file);

  return $result;
}

function VVFILE_CollectGarbage ($test = false)
{
  // Delete immutable files that no longer have any vref's to them.
  // This is mostly useful for customers who use vvfile to save space.
  // Existing vref's and their immutable files are not modified (so nothing is made up to date).
  // 
  // When space is not an issue, making all vrefs up to date and then
  // deleting all immutable files (provided that delayedfilewrite is disabled)
  // is probably better.

  // Note: delayedfilewrite must be disabled in the machine config while running this.
  // TODO: some other mechanism to disable and enable delayedfilewrite (without editing machine config)

  global $myconfig;
  if ($myconfig["delayedfilewrite"] == "yes") {
    N_Die("VVFILE_CollectGarbage: delayedfilewrite must be disabled in myconfig.php before running VVFILE_CollectGarbage, and can be re-enabled afterwards");
  }

  VVFILE_FindVrefs ('VVFILE_CollectGarbage_Helper($result, "iamveryverysure", $input["test"]);', array("test" => $test));

}

function VVFILE_CollectGarbage_Helper($vrefs, $sure = false, $test = false)
{
  if ($sure != "iamveryverysure") N_Die("VVFILE_CollectGarbage_Helper: incorrect 2nd parameter, see source code. DANGER!!! An incorrect or empty 1st parameter will result in irrecoverable LOSS OF DATA.");

  $tspecs["title"] = "VVFILE_CollectGarbage";
  $tspecs["input"]["immfiles_in_use"] = array_flip($vrefs);
  $tspecs["input"]["guidbase"] = N_GUID();
  $tspecs["input"]["test"] = $test;
  $tspecs["init_code"] = '
    $data["handle"] = TERRA_InitMultiDir (array ("html::metabase/vvfile"));
  ';
  $tspecs["step_code"] = '
    $file = TERRA_MultiDir ($data["handle"]);
    if ($file) {
      $immfile = str_replace("/", "", N_KeepBefore(N_KeepAfter($file, "metabase/vvfile/"), ".dat"));
      if ($immfile && !$input["immfiles_in_use"][$immfile]) {
        if ($input["test"]) {
          N_Log("garbage_collector", "VVFILE: [test] deleting immfile (has no vrefs) $file");
        } else {
          N_Log("garbage_collector", "VVFILE: deleting immfile (has no vrefs) $file");
          N_DeleteFile($file);
        }
      }
    } else {
      N_Log("garbage_collector", "VVFILE: completed");
      $completed = true;
    }
  ';
  TERRA_MultiStep($tspecs);
}

function VVFILE_MakeEverythingUp2Date()
{
  // This is especially useful if you have stopped using delayedfilewrite and want
  // everything back to normal.
  VVFILE_FindVRefs('
    $tspecs["title"] = "VVFILE_MakeEverythingUp2Date";
    $tspecs["list"] = $result;
    $tspecs["step_code"] = \'
      N_Log("vvfile", "VVFILE_MakeEverythingUp2Date: $index");
      VVFILE_MakeUp2Date($index);
    \';
    $tspecs["exit_code"] = \'
      N_Log("vvfile", "VVFILE_MakeEverythingUp2Date completed");
    \';
    TERRA_MultiList($tspecs);
  ');

}

function VVFILE_SpeedUp ($timewindow=6048000 /* 7*24*3600 */ )
{
  // walk all _files directories and MakeUp2Date where there are changes, heuristics on size and timestamp?
  // Problem: timestamp heuristic doesnt work because MARKER fakes the "old" timestamp
}

function VVFILE_FindVRefs ($exit_code, $input = array()) { 
  // Find all VRefs. When finished (may be in another request), call $exitcode with $result an $input, 
  // where $result is an associate array of vref => immfile pairs)
  $root = N_CleanRoot();
  // Test if we can use the command line utility "find"
  $findtest = `find {$root}nkit -name vvfile.php`;
  if ($findtest == N_CleanRoot() . "nkit/vvfile.php" . "\n") {
    $findok = true;
  } else {
    $findok = false;
  }

  $vrefs = array();
  if ($findok) {
    $tspecs["title"] = "VVFILE_FindVRefs";
    $tspecs["list"] = glob($root . "*_sites");
    $tspecs["input"]["my_input"] = $input;
    $tspecs["input"]["my_exit_code"] = $exit_code;
    $tspecs["init_code"] = '
      N_Log("vvfile", "VVFILE_FindVRefs started");
    ';
    $tspecs["step_code"] = '
      global $VRefMaximumVRefSize;
      $dir = $value;
      $handle = popen("find {$dir} -size -{$VRefMaximumVRefSize}c", "r");
      while (($line = fgets($handle)) !== false) {
        $vreffile = trim($line);
        $immfile = VVFILE_IsVref($vreffile);
        if ($immfile) $data["result"][$vreffile] = $immfile;
      }
      pclose($handle);
    ';
    $tspecs["exit_code"] = '
      N_Log("vvfile", "VVFILE_FindVRefs found " . count($data["result"]) . " vrefs, executing exit_code.", $input["my_exit_code"]);
      N_Eval($input["my_exit_code"], array("result" => $data["result"], "input" => $input["my_input"]));
      N_Log("vvfile", "VVFILE_FindVRefs exit code completed");

    ';
    TERRA_MultiList ($tspecs);
  } else {
    // TODO: some other efficient way to find VRefs
    N_Die("VVFILE_FindVRefs: Not implemented");
  }
}


function VVFILE_IsVRef ($file) // make sure lock is on or changes are detected while this function is called
{
  // Return false if $file is not a VRef
  // Return the immutable file object id if $file is a VRef
  global $VRefMarker;
  if (is_dir(N_CleanPath("html::/metabase/vvfile"))) {
    $test = N_NowReadFilePart($file, 0, 64);
  }else {
   return false; 
  }

  if (substr($test, 0, 32) == $VRefMarker) {
    return substr($test, 32, 32);
  } else {
    return false;
  }
}


function VVFILE_IsModifiedVRef ($file) // make sure lock is on or changes are detected while this function is called
{
  // Return false if $file is not a modified VRef
  // Return the modification specs if $file is a modified VRef
  if (VVFILE_IsVRef($file) && (N_NowFileSize($file) > 64)) {
    $vref = N_NowReadFile($file);
    $code = substr($vref, 64);
    return $code;
  } else {
    return false;
  }
}

function VVFILE_CanBecomeVRef ($file, $shouldexist = true) // make sure lock is on or changes are detected while this function is called
{
  // Return true if $file can become a VRef.
  // If you want to check a file path but you havent created the file yet, set $shouldexist to false.
  // TODO: think about images in templates
  // TODO: check foto album (images)

  global $myconfig;
  if ($myconfig["delayedfilewrite"] == "yes") {
    $file = N_InternalPath($file);

    if ($shouldexist && !N_FileExists($file)) return false;

    // Check nog wat condities, return false als er niet aan voldaan wordt
    $firstpart = N_KeepBefore($file, "/");
    if (!$firstpart) return false;
    if (substr($firstpart, 0, 6) != "html::") return false;  // Only paths in the DOCUMENT_ROOT
    if (strpos ($file, "mp/vvb2/")) return true;             // Special path for benchmarking
    if (substr($firstpart, -6) != "_sites") return false;    // Only paths in a xxx_sites-directory
    global $VRefMinimumFileSize;
    if ($shouldexist && (N_NowFileSize($file) < $VRefMinimumFileSize)) return false;

    return true;
  }
  return false;

}

function VVFILE_MakeVRef ($file)
{
  VVFILE_Lock ($file);
  if (VVFILE_IsVRef($file)) {
    VVFILE_Unlock ($file);
    return true;
  }

  if (VVFILE_CanBecomeVRef($file)) {
    #ob_start();
    #N_ShowStack();
    #N_Log("vvfile", "VVFILE_MakeVref($file) creating VRef", ob_get_clean());

    // Do it
    $immid = N_Guid();
    $immfile = "html::/metabase/vvfile/". substr($immid, 0, 3) . "/" . substr($immid, 3) . ".dat";

    // get timestamp
    $filetime = N_FileTime($file);
    // rename file
    $result = @rename (N_CleanPath($file), N_CleanPath($immfile));
    // if error, create directories and try again
    if (!$result) {
      @mkdir(N_CleanPath("html::/metabase/vvfile")); 
      @mkdir(N_CleanPath("html::/metabase/vvfile/" . substr($immid, 0, 3))); 
      // No N_MkDir, because the cygwin chmod is rather expensive (shell command), it doesn't work 
      // with Cecil's install kit, and nothing should break if there is a problem with write permissions of immutable files.
      $result = N_TrueRename ($file, $immfile);
    }

    // create placeholder file
    global $VRefMarker;
    $content = $VRefMarker . $immid;
    N_NowWriteFile($file, $content);

    // change timestamp
    N_Touch($file, $filetime);

    VVFILE_Unlock ($file);
    return true;
  }

  VVFILE_Unlock ($file);
  return false;
}

?>