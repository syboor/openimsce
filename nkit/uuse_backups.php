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



function BACKUPS_LatestBackupAge ()
{
  $list = N_QuickTree ("html::backups");
  $max = 1000000000;
  if (is_array ($list)) {
    foreach ($list as $name => $specs)
    {
      if (strpos ($name, "auto") && !strpos ($name, "tmp")) $max = min ($specs["age"], $max);
    }
  }
  return $max;
}

function BACKUPS_LatestBackupSize ()
{
  $themax = BACKUPS_LatestBackupAge ();
  $list = N_QuickTree ("html::backups");
  if (is_array ($list)) {
    foreach ($list as $name => $specs)
    {
      if (abs($specs["age"]-$themax) < 10) {
        return N_FileSize ("html::backups/".$specs["filename"]);
      }
    }
  }
  return 0;
}


// 20100519 KvD added backups

// Backup Core
function BACKUPS_OpenIMSCore($fromcore=false)
{
  uuse("sys");
  SYS_BuildOpenIMSZIP("", "html::/backups/",$fromcore);

  N_Deletefile("html::/backups/openims.zip");
  global $imsbuild;
  $backuppath = "/backups/openims_$imsbuild-".date("Y-m-d-H-i").".zip";
  $zipfile = N_Cleanpath("html::$backuppath");
  rename( N_Cleanpath( "html::/backups/openims_$imsbuild.zip"), $zipfile);
  N_Deletefile("html::/backups/build.php");
  N_Deletefile("html::/backups/build.js");
  N_Deletefile("html::/backups/termsofuse.txt");
  if ($fromcore) {
    echo ML("Het backup bestand is", " The backup file is") . " <a href='$backuppath'>" . $zipfile . "</a>.<br />";
  }
}

// Backup Flex + siteconfig.php
function BACKUPS_ConfigFlex($fromcore=false)
{
  $siteinfo = IMS_Siteinfo();

  $backuppath = "/backups/config-".$siteinfo["sitecollection"] . "-" . date("Y-m-d-H-i") .".zip";
  $path1 = N_Cleanpath("html::$backuppath");
  $path2 = N_Cleanpath("html::config/".$siteinfo["sitecollection"]."/*");

  $zip = $GLOBALS['myconfig']["zip"];

  $cmd1 = "$zip -r $path1 $path2";
  $cmd = `$cmd1`;
  echo "<pre>$cmd</pre>";
  if ($fromcore) {
    echo ML("Het backup bestand is", " The backup file is") . " <a href='$backuppath'>" . $path1 . "</a>.<br />";
  }
}

///

function BACKUPS_CreateManual ()
{
  $filename = "manual_".strtolower(str_replace (":",".",str_replace (" ", "_", N_VisualDate (time(), true, true))))."_".N_GUID().".xml.gz";
  echo "Backup $filename<br>";
  N_WriteFile ("html::backups/".$filename, "");
  MB_MUL_FullBackup ("html::backups/".$filename, true);
}

function BACKUPS_CreateAutomatic ()
{
  $filename = "auto_".strtolower(str_replace (":",".",str_replace (" ", "_", N_VisualDate (time(), true, true))))."_".N_GUID().".xml.gz";
  N_WriteFile ("html::backups/".$filename, "");
  MB_MUL_FullBackup ("html::backups/".$filename);
  $list = N_QuickTree ("html::backups");
  if (is_array ($list)) {
    foreach ($list as $name => $specs)
    {
      if (strpos ($name, "auto")) $auto[$name] = $specs["age"];
    }
    asort ($auto);
    foreach ($auto as $name => $dummy)
    {
      if (++$ctr>16) {
        N_DeleteFile ($name);
      }
    }
  }
}

function BACKUPS_Restore ($filename)
{
  echo "Restore $filename<br>";
  MB_MUL_FullRestore ($filename, true);
}

?>