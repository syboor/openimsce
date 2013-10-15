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



uuse ("tables");
uuse ("terra");

function SYNC_CleanupBackups ()
{
  N_WriteFile ("html::/backups/place.txt", "holder");
  $d = dir(N_CleanPath ("html::/backups/"));
  while (false !== ($entry = $d->read())) {
    if ($entry!="." && $entry!="..") {
      $list[$entry] = filemtime (N_CleanPath ("html::/backups/".$entry));
    }
  }
  arsort ($list);
  foreach ($list as $entry => $dummy) {
    $count++;
    if ($count>3) {
      N_DeleteFile (N_CleanPath ("html::/backups/".$entry));
    }
  }
}

function SYNC_CreateBackup()
{
//  N_Mail ("", "reports@osict.com", N_CurrentServer().": SYNC_CreateBackup BEFORE", N_VisualDate (time(), true));
  SYNC_CleanupBackups (); // delete all but last 3 backups
  SYNC_BackupEverything (N_CurrentServer()."_".date("Ymd_Gis_").N_GUID());
//  N_Mail ("", "reports@osict.com", N_CurrentServer().": SYNC_CreateBackup AFTER", N_VisualDate (time(), true));
}

function SYNC_BackupDirs()
{
  $list = " ";
  $exclude = array (".", "..", "usage", "_private", "dfc", "ufc", "tmp", "backups", "searchindex", "cgi-bin");
  $d = dir(N_CleanPath ("html::/".$dir));
  while (false !== ($entry = $d->read())) {
    $doit = true;
    reset ($exclude);
    foreach ($exclude as $item) if ($item==$entry) $doit=false;
    if ($doit) $list .= "\"$entry\" ";
  }
  return $list;
}

function SYNC_BackupEverything ($spec)
{
  global $myconfig;
  N_WriteFile ("html::/backups/readme.txt", "*** DO NOT REMOVE BACKUP FILES !!! ***"); // also creates the directorie
  $tofile = N_CleanPath ("html::/backups/$spec.tgz");
  N_WriteFile ($tofile, ""); // create needed directorie(s)
  if (N_Windows()) {
    $command = str_replace ("/", "\\", "cd \"".getenv("DOCUMENT_ROOT")."\"\n");
    $command .= $myconfig["tarcommand"]." --create --mode=a+rwx ";
    $command .= SYNC_BackupDirs();
    $command .= " | ";
    $command .= $myconfig["gzipcommand"]." > ";
    $command .= str_replace ("/", "\\", str_replace("\\\\", "\\", "\"$tofile\"\n"));
    $tmp = str_replace ("/", "\\", str_replace("\\\\", "\\", getenv("DOCUMENT_ROOT")."\\tmp\\".N_GUID().".bat"));    
    N_WriteFile ($tmp, $command);
    $system = getenv ("COMSPEC")." /c \"".$tmp."\"";
    `$system`;
    N_DeleteFile ($tmp);
  } else {
    $command = "cd \"".getenv("DOCUMENT_ROOT")."\"\n";
    $command .= $myconfig["tarcommand"]."  --create --mode=a+rwx ";
    $command .= SYNC_BackupDirs();
    $command .= " | ";
    $command .= $myconfig["gzipcommand"]." > ";
    $command .= "\"$tofile\"\n";
    $command .= "chmod 0".decoct($myconfig["chmod"])." \"$tofile\"\n";  
    $tmp = getenv("DOCUMENT_ROOT")."/tmp/".N_GUID().".sh";
    N_WriteFile ($tmp, $command);
    exec("nohup $tmp > /dev/null 2>&1 &");
  }
}

function SYNC_RestoreEverything ($spec)
{ // gunzip -cd tmp/test.tgz | tar xf -    
  global $myconfig;
  $fromfile = N_CleanPath ("html::/backups/$spec.tgz");
  if (N_Windows()) {
    $command = str_replace ("/", "\\", "cd \"".getenv("DOCUMENT_ROOT")."\"\n");
    $command .= $myconfig["gunzipcommand"]." -cd ";
    $command .= str_replace ("/", "\\", str_replace("\\\\", "\\", "\"$fromfile\""));
    $command .= " | ".$myconfig["tarcommand"]." --extract --no-same-owner --same-permissions --file=-";
    $tmp = str_replace ("/", "\\", str_replace("\\\\", "\\", getenv("DOCUMENT_ROOT")."\\tmp\\".N_GUID().".bat"));    
    N_WriteFile ($tmp, $command);
    $system = getenv ("COMSPEC")." /c \"".$tmp."\"";
    `$system`;
    N_DeleteFile ($tmp);
  } else {
    $command = "cd \"".getenv("DOCUMENT_ROOT")."\"\n";
    $command .= $myconfig["gunzipcommand"]." -cd ";
    $command .= "\"$fromfile\" ";
    $command .= " | tar  --extract --no-same-owner --same-permissions --file=-";
    $tmp = getenv("DOCUMENT_ROOT")."/tmp/".N_GUID().".sh";
    N_WriteFile ($tmp, $command);
    exec("nohup $tmp > /dev/null 2>&1 &");
  }
}

function SYNC_Upgrade ()
{
  N_Lock ("SYNC_Upgrade");
  global $myconfig;
  echo N_VisualDate (time(), true).": UPGRADE STARTED<br>"; N_Flush(1);
  echo N_VisualDate (time(), true).": Current server: ".N_CurrentServer()."<br>"; N_Flush(1);
  $master = $myconfig["codemaster"];
  if (!$master) $master = $myconfig["master"];
  $mastername = N_Name ($master);
  if (!$mastername) {
    N_DIE ("OpenIMS master server can not be reached (name), please try again in 10 minutes");
  }
  uuse ("grid");
  if (!GRID_DetermineIP ($mastername)) {
    N_DIE ("OpenIMS master server can not be reached (ping), please try again in 10 minutes");
  }
  echo N_VisualDate (time(), true).": Master server: ".$master." (".$mastername.")<br>"; N_Flush(1);  
  if (N_CurrentServer() == N_Name ($master)) {
    echo N_VisualDate (time(), true).": Current server is master, no upgrade necessary<br>"; N_Flush(1);
  } else {
    SYNC_DoSyncDirs_OLD (N_CurrentServer(), $mastername, $myconfig["coredirs"], 1000000000);
  }
  echo N_VisualDate (time(), true).": UPGRADE COMPLETED<br>"; N_Flush(1);
//  N_Mail ("", "reports@osict.com", N_CurrentServer().": SYNC_Upgrade COMPLETED", N_VisualDate (time(), true));
  N_Unlock ("SYNC_Upgrade");
}

function SYNC_DoSyncFile ($toserver, $fromserver, $filename, $remotespecs)
{
//  N_Log ("test", "SYNC_DoSyncFile ($toserver, $fromserver, $filename, ".$remotespecs["md5b"].") START");
  $localspecs = SYNC_FileInfo (getenv("DOCUMENT_ROOT")."/".$filename);
  if (($remotespecs["md5b"] != $localspecs["md5b"]) && ($remotespecs["age"] <= $localspecs["age"])) {
    $msg = N_CurrentServer()." Localage: ".$localspecs["age"]." Remoteage: ".$remotespecs["age"]." Toserver: $toserver Fromserver: $fromserver Filename: $filename";
//    N_Mail ("", "nevries@xs4all.nl", $msg);
    $content = URPC ($fromserver, '$output=N_ReadFile (getenv("DOCUMENT_ROOT")."/".$input);', $filename);
    N_WriteFile (getenv("DOCUMENT_ROOT")."/".$filename."_t3mp", $content);
    $newlocalspecs = SYNC_FileInfo (getenv("DOCUMENT_ROOT")."/".$filename."_t3mp");
    N_Log ("test", "SYNC_DoSyncFile ".$remotespecs["md5b"]." - ".$newlocalspecs["md5b"]);
    if ($remotespecs["md5b"] == $newlocalspecs["md5b"]) {
      if (is_file (N_CleanPath ("html::/".$filename))) N_DeleteFile (N_CleanPath ("html::/".$filename));
      rename (N_CleanPath ("html::/".$filename."_t3mp"), N_CleanPath ("html::/".$filename));
      touch (N_CleanPath ("html::/".$filename), time()-$remotespecs["age"]); // make age of copy approximately equal to age of original
      echo N_VisualDate (time(), true).": Copy from ".$fromserver." to ".$toserver." file ".$filename." ...<br>"; N_Flush(1);
    } else {
      N_DIE ("SYNC_DoSyncDirs($toserver, $fromserver, ..., $timeframe) $filename remote:".$remotespecs["md5b"]." newlocal:".$newlocalspecs["md5b"]);
    }
  }
//  N_Log ("test", "SYNC_DoSyncFile ($toserver, $fromserver, $filename, ".$remotespecs["md5b"].") END");
}

function SYNC_DoSyncDirs ($toserver, $fromserver, $dirs, $timeframe)
{
  if (!is_array($dirs)) return;
  if (!count ($dirs)) return;
  $thedirs = array();
  foreach ($dirs as $dir) {
    array_push ($thedirs, "html::$dir");
  }  
  TERRA_CreateBackgroundProcess ("SendFiles", array("from"=>$fromserver, "to"=>$toserver, "dirs"=>$thedirs), "feedback");
} 

function SYNC_DoSyncDirs_OLD ($toserver, $fromserver, $dirs, $timeframe)
{
  if (!is_array($dirs)) return;
  if (!count ($dirs)) return;
  if ($toserver!=N_CurrentServer()) {
    if (is_array ($dirs)) foreach ($dirs as $dir) {
      echo N_VisualDate (time(), true).": Sending files to ".$toserver." ($dir)...<br>"; N_Flush(1);
      $input["toserver"]=$toserver;
      $input["fromserver"]=$fromserver;
      $input["dirs"] = array($dir=>$dir);
      $input["timeframe"]=$timeframe;
      URPC ($toserver, 'uuse ("sync"); SYNC_DoSyncDirs_OLD ($input["toserver"], $input["fromserver"], $input["dirs"], $input["timeframe"]);', $input);
    }
  } else { // $toserver == N_CurrentServer()
    if (is_array ($dirs)) foreach ($dirs as $dir) {
      echo N_VisualDate (time(), true).": Receiving files from ".$fromserver." ($dir)...<br>"; N_Flush(1);
      $remotedir = SYNC_Dir ($fromserver, $dir, $timeframe);
      if (is_array ($remotedir)) foreach ($remotedir as $filename => $remotespecs) {
        SYNC_DoSyncFile ($toserver, $fromserver, $filename, $remotespecs);  
      }
    }
  }
}

function SYNC_FileInfo ($path)
{
  return N_Fileinfo ($path);
}

function SYNC_DoDir ($dir, $maxage) 
{
  global $myconfig;
  $result = array();
  if ($maxage > 2*24*7*3600) return SYNC_SlowDir ($dir, $maxage);
  $from = (int)((time()-$maxage)/3600);
  $to = (int)(time()/3600);
  for ($i=$from; $i<=$to; $i++) {
    $content = N_ReadFile ("html::logs/".$i."_$dir.filelog");
    $list = explode ("\n", $content);
    if (is_array ($list)) foreach ($list as $item) {
      if ($item) {
        $item = str_replace ("_t3mp", "", $item); // compensate for old general.php bug, can be removed after 31/4/2003
        $all[$item] = "*";
      }
    }
  }
  if (is_array ($all)) foreach ($all as $entry => $dummy) {
    $doit = true;
    if (!$dir) foreach ($myconfig["coreskiprootfiles"] as $check) if ($check==$entry) $doit=false;
    if (!$dir) foreach ($myconfig["coreskiprootfilescontaining"] as $check) if (strpos(N_HTMLPath($entry), $check) !==false) $doit=false;
    if (strpos (" ".$entry, "t3mp")) $doit = false;
    if ($doit) {
      $fi = SYNC_FileInfo (getenv("DOCUMENT_ROOT")."/".$entry);
      if ($fi["age"] <= $maxage) $result[$entry] = $fi;
    }
  }  
  return $result;
}

function SYNC_SlowDir ($dir, $maxage)
{
  global $myconfig;
  $result = array();
  if (is_dir (N_CleanPath ("html::/".$dir))) {
    $d = dir(N_CleanPath ("html::/".$dir));
    while (false !== ($entry = $d->read())) {
      if ($dir && $entry!="." && $entry!=".." && is_dir (N_CleanPath ("html::/".$dir."/".$entry))) {
        $result = N_array_merge ($result, SYNC_SlowDir ($dir."/".$entry, $maxage));
      } else if (is_file (N_CleanPath ("html::/".$dir."/".$entry))) {
        $doit = true;
        if (!$dir) foreach ($myconfig["coreskiprootfiles"] as $check) if ($check==$entry) $doit=false;
        if (!$dir) foreach ($myconfig["coreskiprootfilescontaining"] as $check) if (strpos(N_HTMLPath($entry), $check) !==false) $doit=false;
        if (strpos (" ".$entry, "t3mp")) $doit = false;                
        if ($doit) {
          $fi = SYNC_FileInfo (getenv("DOCUMENT_ROOT")."/".$dir."/".$entry);
          if ($fi["age"] <= $maxage) $result[$dir."/".$entry] = $fi;
        }
      }
    }
    $d->close();
  }
  return $result;
}

function SYNC_Dir ($server, $dir, $maxage=1000000000)
{
  if ($server!=N_CurrentServer()) {
    return URPC ($server, 'uuse ("sync"); $output = SYNC_Dir ($input["server"], $input["dir"], $input["maxage"]);', array ("server"=>$server, "dir"=>$dir, "maxage" => $maxage));
  } else {
    $ret = SYNC_DoDir ($dir, $maxage);
    return $ret;
  }
}

function SYNC_DoSyncTables ($masterserver, $slaveserver, $send, $receive, $tables, $timeframe)
{
  N_Debug ("SYNC_DoSyncTables ($masterserver, $slaveserver, $send, $receive, $tables, $timeframe)");
  if ($send=="yes") {
    TERRA_CreateBackgroundProcess ("SendData", array("from"=>$masterserver, "to"=>$slaveserver, "tables"=>$tables), "feedback");
  }
  if ($receive=="yes") {
    TERRA_CreateBackgroundProcess ("SendData", array("from"=>$slaveserver, "to"=>$masterserver, "tables"=>$tables), "feedback");
  }
} 

function SYNC_DoSyncTables_OLD ($masterserver, $slaveserver, $send, $receive, $tables, $timeframe)
{
  if (!is_array($tables)) return;
  if (!count ($tables)) return;
  if ($masterserver!=N_CurrentServer()) {
    $input["masterserver"]=$masterserver;
    $input["slaveserver"]=$slaveserver;
    $input["send"]=$send;
    $input["receive"]=$receive;
    $input["tables"]=$tables;
    URPC ($masterserver, 'uuse ("sync"); SYNC_DoSyncTables ($input["masterserver"], $input["slaveserver"], $input["send"], $input["receive"], $input["tables"])', $input);
  } else {
    if ($send) {
      $data = MB_MUL_Export ($tables, $timeframe);
      URPC ($slaveserver, 'MB_MUL_Import ($input);', $data);
    }
    if ($receive) {
      $data = URPC ($slaveserver, '$output = MB_MUL_Export ($input, '.$timeframe.');', $tables);
      MB_MUL_Import ($data);
      MB_Flush();
    }
  }
}

function SYNC_PrettyXML_Helper($xml) {
  // 20120320: Because we fixed this bug ages ago in N_EO instead of in N_PrettyXML, N_PrettyXML should remain unmodified.
  // Which means that if N_PrettyXML is called not through N_EO (which happens here), the same fix is needed.
  if (N_PHP5()) {
    // 200903013: Bug found by Jack, workaround by LF.
    // Starting from PHP 5, the input encoding is automatically detected, so the ONLY way to set it is through a 
    // xml preamble (or http headers in some situations, but not when parsing strings...).
    // It also appears that instead of just mangling the characters a little, PHP5's xml parser has decided to CRASH 
    // when non-us-ascii iso-8859-1 characters appear in the input.
    // The output encoding defaults to utf8 starting from PHP 5.0.2 (this can be overruled by a parser option,
    // somewhere deep down in nkit/Xpath.class...)
    return utf8_decode(N_PrettyXML ('<?xml version="1.0" encoding="ISO-8859-1"?>'.$xml));
  } else {
    return N_PrettyXML($xml);
  }
}

function SYNC_Periodical ($moment) // MANUAL, NIGHT OR HOUR
{
  global $enginemetadata, $myconfig;  
  N_Log ("sync",$moment.": ".N_CurrentServer().": SYNC_Periodical STARTED");
  echo N_VisualDate (time(), true).": SYNC STARTED<br>"; N_Flush(1);
  echo N_VisualDate (time(), true).": Scanning local and remote configuration...<br>"; N_Flush(1);       
  $allconfigs = N_AllConfigs();
  $enginemetadata["newreplication"] = array();
  $timeframe = 1000000000;
  if ($moment=="hour") $timeframe = 3600*2; // 2 hours
  if ($moment=="night") $timeframe = 3600*24*3; // 3 days
  if ($moment=="manual") $timeframe = 3600*24*10; // 10 days
  if ($moment=="full") {
    $timeframe = 1000000000; // full scan
    $moment = "manual";
  }
  // receive eveything
  if (is_array($myconfig["replication"])) foreach ($myconfig["replication"] as $dummy => $repspecs) {
    echo N_VisualDate (time(), true).": Interpreting replication specification #$dummy (myconfig.php)<br>"; N_Flush(1);
    if ($moment=="manual" || ($moment=="night" && $repspecs["moment"]!="manual") || ($moment=="hour" && $repspecs["moment"]!="manual" && $repspecs["moment"]!="night")) {
      if ($repspecs["receive"]=="yes") {
        echo N_VisualDate (time(), true).": Scanning local and remote directory structures...<br>"; N_Flush(1);       
        $all = SYNC_AllDirs (N_CurrentServer());
        $alldirs = array();
        if (is_array($all)) foreach ($all as $element) $alldirs[$element] = $element;
        if (is_array ($repspecs["servers"])) foreach ($repspecs["servers"] as $servername) {
          $all = SYNC_AllDirs ($servername);
          if (is_array($all)) foreach ($all as $element) $alldirs[$element] = $element;
        }
        N_Log ("sync", $moment." receive: detected all dirs from local/$servername", SYNC_PrettyXML_Helper (N_Object2XML($alldirs)));
        $dirs = SYNC_IncludedMulti ($repspecs["dirs"], $repspecs["excludedirs"], $alldirs);
        if (is_array($repspecs["servers"])) foreach ($repspecs["servers"] as $servername) {
          echo N_VisualDate (time(), true).": START PROCESS Receiving files from ".$servername."...<br>"; N_Flush(1); 
          N_Log ("sync", $moment." receive: to receive dirs from $servername", SYNC_PrettyXML_Helper (N_Object2XML($dirs)));
          SYNC_DoSyncDirs (N_CurrentServer(), $servername, $dirs, $timeframe);
        }
        echo N_VisualDate (time(), true).": Scanning local and remote database structures...<br>"; N_Flush(1);       
        $all = SYNC_AllTables (N_CurrentServer());
        $alltables = array();
        foreach ($all as $element) $alltables[$element] = $element;
        foreach ($repspecs["servers"] as $servername) {
          $all = SYNC_AllTables ($servername);
          foreach ($all as $element) $alltables[$element] = $element;
        }
        N_Log ("sync", $moment." receive: detected all tables from local/$servername", SYNC_PrettyXML_Helper (N_Object2XML($alltables)));
        $tables = SYNC_IncludedMulti ($repspecs["tables"], $repspecs["excludetables"], $alltables);
        foreach ($repspecs["servers"] as $servername) {
          echo N_VisualDate (time(), true).": START PROCESS Receiving data from ".$servername." ...<br>"; N_Flush(1);       
          N_Log ("sync", $moment." receive: to receive tables from $servername", SYNC_PrettyXML_Helper (N_Object2XML($tables)));
          SYNC_DoSyncTables (N_CurrentServer(), $servername, "no", $repspecs["receive"], $tables, $timeframe);
        }
      }
    }
  }
  // send everything
  if (is_array($myconfig["replication"])) foreach ($myconfig["replication"] as $dummy => $repspecs) {
    echo N_VisualDate (time(), true).": Interpreting replication specification #$dummy (myconfig.php)<br>"; N_Flush(1);
    if ($moment=="manual" || ($moment=="night" && $repspecs["moment"]!="manual") || ($moment=="hour" && $repspecs["moment"]!="manual" && $repspecs["moment"]!="night")) {
      if ($repspecs["send"]=="yes") {
        echo N_VisualDate (time(), true).": Scanning local and remote directory structures...<br>"; N_Flush(1);       
        $all = SYNC_AllDirs (N_CurrentServer());
        $alldirs = array();
        foreach ($all as $element) $alldirs[$element] = $element;
        foreach ($repspecs["servers"] as $servername) {
          $all = SYNC_AllDirs ($servername);
          foreach ($all as $element) $alldirs[$element] = $element;
        }
        N_Log ("sync", $moment." send: detected all dirs from local/$servername", SYNC_PrettyXML_Helper (N_Object2XML($alldirs)));
        $dirs = SYNC_IncludedMulti ($repspecs["dirs"], $repspecs["excludedirs"], $alldirs);
        foreach ($repspecs["servers"] as $servername) {
          echo N_VisualDate (time(), true).": START PROCESS Sending files to ".$servername."...<br>"; N_Flush(1);
          N_Log ("sync", $moment." send: to send dirs to $servername", SYNC_PrettyXML_Helper (N_Object2XML($dirs)));
          SYNC_DoSyncDirs ($servername, N_CurrentServer(), $dirs, $timeframe);
        }
        echo N_VisualDate (time(), true).": Scanning local and remote database structures...<br>"; N_Flush(1);       
        $all = SYNC_AllTables (N_CurrentServer());
        $alltables = array();
        foreach ($all as $element) $alltables[$element] = $element;
        foreach ($repspecs["servers"] as $servername) {
          $all = SYNC_AllTables ($servername);
          foreach ($all as $element) $alltables[$element] = $element;
        }
        N_Log ("sync", $moment." send: detected all tables from local/$servername", SYNC_PrettyXML_Helper (N_Object2XML($alltables)));
        $tables = SYNC_IncludedMulti ($repspecs["tables"], $repspecs["excludetables"], $alltables);
        foreach ($repspecs["servers"] as $servername) {
          echo N_VisualDate (time(), true).": START PROCESS Sending data to ".$servername." ...<br>"; N_Flush(1);
          N_Log ("sync", $moment." send: to send tables to $servername", SYNC_PrettyXML_Helper (N_Object2XML($tables)));
          SYNC_DoSyncTables (N_CurrentServer(), $servername, $repspecs["send"], "no", $tables, $timeframe);
          N_Flush(1);
        }
      }
    }
  }
  echo N_VisualDate (time(), true).": Writing in-memory data cache to disk<br>"; N_Flush(1);
  MB_Flush();
  N_Log ("sync", $moment.": ".N_CurrentServer().": SYNC_Periodical INITIATION COMPLETED");
  echo N_VisualDate (time(), true).": SYNC COMPLETED<br>"; N_Flush(1);
}


function SYNC_Periodical_OLD ($moment) // MANUAL, NIGHT OR HOUR
{
  global $enginemetadata, $myconfig;  
//  N_Mail ("", "reports@osict.com", $moment.": ".N_CurrentServer().": SYNC_Periodical STARTED", N_VisualDate (time(), true));
  echo N_VisualDate (time(), true).": SYNC STARTED (OLD)<br>"; N_Flush(1);
  echo N_VisualDate (time(), true).": Scanning local and remote configuration...<br>"; N_Flush(1);       
  $allconfigs = N_AllConfigs();
  $enginemetadata["newreplication"] = array();
  $timeframe = 1000000000;
  if ($moment=="hour") $timeframe = 3600*2; // 2 hours
  if ($moment=="night") $timeframe = 3600*24*3; // 3 days
  if ($moment=="manual") $timeframe = 3600*24*10; // 10 days
  if ($moment=="full") {
    $timeframe = 1000000000; // full scan
    $moment = "manual";
  }
  // receive eveything
  if (is_array($myconfig["replication"])) foreach ($myconfig["replication"] as $dummy => $repspecs) {
    echo N_VisualDate (time(), true).": Interpreting replication specification #$dummy (myconfig.php)<br>"; N_Flush(1);
    if ($moment=="manual" || ($moment=="night" && $repspecs["moment"]!="manual") || ($moment=="hour" && $repspecs["moment"]!="manual" && $repspecs["moment"]!="night")) {
      if ($repspecs["receive"]=="yes") {
        echo N_VisualDate (time(), true).": Scanning local and remote directory structures...<br>"; N_Flush(1);       
        $all = SYNC_AllDirs (N_CurrentServer());
        $alldirs = array();
        if (is_array($all)) foreach ($all as $element) $alldirs[$element] = $element;
        if (is_array ($repspecs["servers"])) foreach ($repspecs["servers"] as $servername) {
          $all = SYNC_AllDirs ($servername);
          if (is_array($all)) foreach ($all as $element) $alldirs[$element] = $element;
        }
        $dirs = SYNC_IncludedMulti ($repspecs["dirs"], $repspecs["excludedirs"], $alldirs);
        if (is_array($repspecs["servers"])) foreach ($repspecs["servers"] as $servername) {
          echo N_VisualDate (time(), true).": Receiving files from ".$servername."...<br>"; N_Flush(1);
          SYNC_DoSyncDirs_OLD (N_CurrentServer(), $servername, $dirs, $timeframe);
        }
        echo N_VisualDate (time(), true).": Scanning local and remote database structures...<br>"; N_Flush(1);       
        $all = SYNC_AllTables (N_CurrentServer());

        $alltables = array();
        foreach ($all as $element) $alltables[$element] = $element;
        foreach ($repspecs["servers"] as $servername) {
          $all = SYNC_AllTables ($servername);
          foreach ($all as $element) $alltables[$element] = $element;
        }
        $tables = SYNC_IncludedMulti ($repspecs["tables"], $repspecs["excludetables"], $alltables);
        foreach ($repspecs["servers"] as $servername) {
          echo N_VisualDate (time(), true).": Receiving data from ".$servername." ...<br>"; N_Flush(1);       
          SYNC_DoSyncTables_OLD (N_CurrentServer(), $servername, "no", $repspecs["receive"], $tables, $timeframe);
        }
      }
    }
  }
  // send everything
  if (is_array($myconfig["replication"])) foreach ($myconfig["replication"] as $dummy => $repspecs) {
    echo N_VisualDate (time(), true).": Interpreting replication specification #$dummy (myconfig.php)<br>"; N_Flush(1);
    if ($moment=="manual" || ($moment=="night" && $repspecs["moment"]!="manual") || ($moment=="hour" && $repspecs["moment"]!="manual" && $repspecs["moment"]!="night")) {
      if ($repspecs["send"]=="yes") {
        echo N_VisualDate (time(), true).": Scanning local and remote directory structures...<br>"; N_Flush(1);       
        $all = SYNC_AllDirs (N_CurrentServer());
        $alldirs = array();
        foreach ($all as $element) $alldirs[$element] = $element;
        foreach ($repspecs["servers"] as $servername) {
          $all = SYNC_AllDirs ($servername);
          foreach ($all as $element) $alldirs[$element] = $element;
        }
        $dirs = SYNC_IncludedMulti ($repspecs["dirs"], $repspecs["excludedirs"], $alldirs);
        foreach ($repspecs["servers"] as $servername) {
          echo N_VisualDate (time(), true).": Sending files to ".$servername."...<br>"; N_Flush(1);
          SYNC_DoSyncDirs_OLD ($servername, N_CurrentServer(), $dirs, $timeframe);
        }
        echo N_VisualDate (time(), true).": Scanning local and remote database structures...<br>"; N_Flush(1);       
        $all = SYNC_AllTables (N_CurrentServer());
        $alltables = array();
        foreach ($all as $element) $alltables[$element] = $element;
        foreach ($repspecs["servers"] as $servername) {
          $all = SYNC_AllTables ($servername);
          foreach ($all as $element) $alltables[$element] = $element;
        }
        $tables = SYNC_IncludedMulti ($repspecs["tables"], $repspecs["excludetables"], $alltables);
        foreach ($repspecs["servers"] as $servername) {
          echo N_VisualDate (time(), true).": Sending data to ".$servername." ...<br>"; N_Flush(1);
          SYNC_DoSyncTables_OLD (N_CurrentServer(), $servername, $repspecs["send"], "no", $tables, $timeframe);
          N_Flush(1);
        }
      }
    }
  }
  echo N_VisualDate (time(), true).": Writing in-memory data cache to disk<br>"; N_Flush(1);
  MB_Flush();
//  N_Mail ("", "reports@osict.com", $moment.": ".N_CurrentServer().": SYNC_Periodical COMPLETED", N_VisualDate (time(), true));
  echo N_VisualDate (time(), true).": SYNC COMPLETED<br>"; N_Flush(1);
}

function SYNC_IncludedMulti ($inclist, $excllist, $fulllist)
{
  $result = array();
  // include explicitely named elements (even if they do not exist, yet)
  if (is_array($inclist)) foreach ($inclist as $inc) {
    if (!strpos (" ".$inc, "*") && !SYNC_Included ($excllist, $inc)) $result[$inc] = $inc;
  }
  if (is_array($fulllist)) foreach ($fulllist as $element) {
    if (SYNC_Included ($inclist, $element) && !SYNC_Included ($excllist, $element)) $result[$element] = $element;
  }
  return $result;
}

function SYNC_Included ($list, $item)
{
  if (is_array($list)) {
    foreach ($list as $element) {
      $element = preg_quote ($element, "/");
      $element = str_replace ("\*", ".*", $element);
      if (preg_match ("/^$element\$/", $item)) return true;
    }    
  }
  return false;
}

function SYNC_Status ($which="all")
{
  $allconfigs = N_AllConfigs();
  if (!$which || $which=="all") $which = "online urpc tables dirs";
  if (strpos (" ".$which, "online")) {
    $allservers = N_AllServers();
    echo "Servers and their status<br>";
    T_Start("ims", array ("nobr" => "yes"));
    echo "Server";
    T_Next();
    echo "Status";
    T_Next();
    echo "IP";
    T_NewRow();
    foreach ($allservers as $servername) {
      echo $servername;
      T_Next();
      echo N_ServerStatus ($servername);
      T_Next();
      echo N_ServerAddress ($servername);
      T_NewRow();
    }
    TE_End();
    N_Flush(1);
  }

  echo "<br>";
  uuse ("maint");
  T_Start("ims", array ("nobr" => "yes"));
    echo "Server";
    T_Next();
    echo "Build";
    T_Next();
    echo "URPC";
    T_Next();
    echo "BIG URPC";
    T_Next();
    echo "CURRENTSERVER";
    T_Next();
    echo "GB";
    T_Next();
    echo "CALLMEOFTEN";
    T_Next();
    echo "SECSAGO";
    T_Next();
    echo "UPTIME";
    T_Next();
    echo "LOAD";
    T_Next();
    echo "TIME";
    T_Next();
    echo "TIMESTRING";
    T_NewRow();
    $allservers = N_AllOnlineServers ();
    foreach ($allservers as $server) {

  $code .= '$rec = &MB_Ref ("globalvars", N_CurrentServer ()."::batchdata");';
  $code .= '$output["size"] = strlen($input);';
  $code .= 'global $imsbuild; $output["build"] = $imsbuild;';
  $code .= '$output["currentserver"] = N_CurrentServer ();';
  $code .= '$output["secsago"] = time()-$rec["lastupdate"];';
  $code .= '$output["callmeoften"] = $rec["callmeoften"];';
  $code .= '$output["gb"] = (int)(N_DiskSpace ()/1000000000);';
  $code .= '$output["uptime"] = `uptime`;';
  $code .= '
    $netstat = `net statistics server`;

    if (N_RegExp ($netstat, "Statistics since ([0-9]*)/([0-9]*)/([0-9][0-9][0-9][0-9])")) {
      $date = N_BuildDateTime (
        N_RegExp ($netstat, "Statistics since ([0-9]*)/([0-9]*)/([0-9][0-9][0-9][0-9])", 3),
        N_RegExp ($netstat, "Statistics since ([0-9]*)/([0-9]*)/([0-9][0-9][0-9][0-9])", 1),
        N_RegExp ($netstat, "Statistics since ([0-9]*)/([0-9]*)/([0-9][0-9][0-9][0-9])", 2)
      );
      $output["uptime"] = (int)((time()-$date)/(24*3600))." days";
    } else if (N_RegExp ($netstat, "Statistieken vanaf ([0-9]*)/([0-9]*)/([0-9][0-9][0-9][0-9])")) {
      $date = N_BuildDateTime (
        N_RegExp ($netstat, "Statistieken vanaf ([0-9]*)/([0-9]*)/([0-9][0-9][0-9][0-9])", 3),
        N_RegExp ($netstat, "Statistieken vanaf ([0-9]*)/([0-9]*)/([0-9][0-9][0-9][0-9])", 1),
        N_RegExp ($netstat, "Statistieken vanaf ([0-9]*)/([0-9]*)/([0-9][0-9][0-9][0-9])", 2)
      );
      $output["uptime"] = (int)((time()-$date)/(24*3600))." days";
    }
  ';
  $code .= '$output["time"] = time();';
  $code .= '$output["timestring"] = N_VisualDate (time(), true);';

  $smallinput = "**********";
  $biginput = "";
  for ($i=0;$i<1000;$i++) $biginput.="**********";

  $outputsmall = URPC ($server, $code, $smallinput);
  $outputbig = URPC ($server, $code, $biginput);
  echo ucfirst($server); 
  T_Next();
  echo $outputsmall["build"]; 
  T_Next();
  echo N_IF (($outputsmall && $outputsmall["size"]==10), "OK", "ERROR");
  T_Next();
  echo N_IF (($outputbig && $outputbig["size"]==10000), "OK", "ERROR");
  T_Next();
  if ($outputsmall && $outputsmall["size"]==10) {
    echo $outputsmall["currentserver"];
  T_Next();
    echo $outputsmall["gb"];
  T_Next();
    echo $outputsmall["callmeoften"]." (".((int)($outputsmall["callmeoften"]/(24*60)))." days)";
  T_Next();
    echo $outputsmall["secsago"];
  T_Next();
    echo $outputsmall["uptime"]." ";
  T_Next();
    echo N_Load ($server);
  T_Next();
    echo $outputsmall["time"];
  T_Next();
    echo $outputsmall["timestring"];
  } else {
  }

   T_NewRow(); 
   }
    TE_End();
    N_Flush(1);

  if (strpos (" ".$which, "urpc")) {
    $allservers = N_AllOnlineServers ();
    echo "<br>URPC testing</br>";
    N_Flush(1);
    foreach ($allservers as $servername) {
      foreach ($allservers as $servername2) {
        $result = URPC ($servername, '
          $t1 = N_MicroTime(); 
          $output = URPC ("'.$servername2.'", "\$output=\'ok\';"); 
          $t2 = N_MicroTime(); 
          if ($output) $output = $t2 - $t1;
        ');
        echo "From $servername to $servername2 ";  
        if ($result) {
          echo (int)($result*1000)." ms";
        } else {
          echo "error";
        }
        echo "<br>";
        N_Flush(1);  
      }
    }
  }
  if (strpos (" ".$which, "tables")) {
    $allservers = N_AllOnlineServers ();
    $combined = array();
    echo "<br>Tables</br>";
    foreach ($allservers as $servername) {
      $all[$servername] = SYNC_AllTables ($servername);
      if (is_array ($all[$servername])) foreach ($all[$servername] as $element) $combined[$element] = $element;
    }
    ksort ($combined);
    T_Start("ims", array ("nobr" => "yes"));
    echo "Table \ Server";
    T_Next();
    foreach ($allservers as $servername) {
      echo $servername;    
      T_Next();
    }
    T_NewRow();
    foreach ($combined as $element) {
      echo "<b>$element</b>";    
      T_Next();
      foreach ($allservers as $servername) { 
        if ($all[$servername][$element]) {
          $nothing = true;
          if (SYNC_Included ($allconfigs[$servername]["config"]["coretables"], $element)) {
            echo "coretables ";
            $nothing = false;
          }
          if (SYNC_Included ($allconfigs[$servername]["config"]["localtables"], $element)) {
            echo "localtables ";
            $nothing = false;
          }
          foreach ($allservers as $name) {
            if (is_array ($allconfigs[$name]["config"]["replication"])) foreach ($allconfigs[$name]["config"]["replication"] as $dummy => $specs) {
              if (SYNC_Included ($specs["tables"], $element)  && !SYNC_Included ($specs["excludetables"], $element) && ($name==$servername || SYNC_Included ($specs["servers"],$servername))) {
                echo "M[";
                if ($specs["send"]=="yes") echo "s";
                if ($specs["receive"]=="yes") echo "r";
                echo "]:$name S:";
                foreach ($specs["servers"] as $slave) echo "$slave ";
                echo "(".$specs["moment"].") ";
                $nothing = false;
              }
            }
          }
          if ($nothing) echo "<b>UNKNOWN</b>";
        } else {
          echo "not present";
        }
        T_Next();
      }    
      T_NewRow();
    }    
    TE_End();
    N_Flush(1);
  }
  if (strpos (" ".$which, "dirs")) {
    $allservers = N_AllOnlineServers ();
    $combined = array();
    echo "<br>Directories</br>";
    foreach ($allservers as $servername) {
      $all[$servername] = SYNC_AllDirs ($servername);
      if (is_array ($all[$servername])) foreach ($all[$servername] as $element) $combined[$element] = $element;
    }
    ksort ($combined);
    T_Start("ims", array ("nobr" => "yes"));
    echo "Directory \ Server";
    T_Next();
    foreach ($allservers as $servername) {
      echo $servername;
      T_Next();
    }
    T_NewRow();
    foreach ($combined as $element) {
      echo "<b>$element</b>";    
      T_Next();
      foreach ($allservers as $servername) { 
        if ($all[$servername][$element]) {
          $nothing = true;
          if (SYNC_Included ($allconfigs[$servername]["config"]["coredirs"], $element)) {
            echo "coredirs ";
            $nothing = false;
          }
          if (SYNC_Included ($allconfigs[$servername]["config"]["localdirs"], $element)) {
            echo "localdirs ";
            $nothing = false;
          }
          foreach ($allservers as $name) {
            if (is_array ($allconfigs[$name]["config"]["replication"])) foreach ($allconfigs[$name]["config"]["replication"] as $dummy => $specs) {
              if (SYNC_Included ($specs["dirs"], $element)  && !SYNC_Included ($specs["excludedirs"], $element) && ($name==$servername || SYNC_Included ($specs["servers"],$servername))) {
                echo "M[";
                if ($specs["send"]=="yes") echo "s";
                if ($specs["receive"]=="yes") echo "r";
                echo "]:$name S:";
                foreach ($specs["servers"] as $slave) echo "$slave ";
                echo "(".$specs["moment"].") ";
                $nothing = false;
              }
            }
          }
          if ($nothing) echo "<b>UNKNOWN</b>";
        } else {
          echo "not present";
        }
        T_Next();
      }
      T_NewRow();
    }    
    TE_End();
    N_Flush(1);
  }
}

function SYNC_AllDirs ($servername="")
{
  if (!$servername || $servername==N_CurrentServer()) {
    $d = dir(getenv("DOCUMENT_ROOT"));
    while (false !== ($entry = $d->read())) {
      if ($entry!="." && $entry!=".." && is_dir(getenv("DOCUMENT_ROOT")."/".$entry)) $result[$entry] = $entry;       
    }
    return $result;
  } else {
    return URPC ($servername, 'uuse("sync");$output=SYNC_AllDirs();');
  }
}

function SYNC_AllTables ($servername="")
{
  if (!$servername || $servername==N_CurrentServer()) {
    return MB_AllTables();
  } else {
    return URPC ($servername, 'uuse("sync");$output=SYNC_AllTables();');
  }
}

function SYNC_Overview ()
{
}

function SYNC_Export ($filter)
{
  $alltables = MB_AllTables();
  $copytables = array ("ims_fields", "ims_sitecollections", "ims_sites", "ims_trees", "shield_supergroups");
  foreach ($alltables as $table) {
    if (strpos (" ".$table, $filter)) {
      array_push ($copytables, $table);
    }
  }

  $copydirs = array("config");
  $d = dir(getenv("DOCUMENT_ROOT"));
  while (false !== ($entry = $d->read())) {
    if ($entry!="." && $entry!=".." && is_dir (getenv("DOCUMENT_ROOT")."/$entry")) {
      if (strpos (" ".$entry, $filter)) {
        array_push ($copydirs, $entry);
      }
    }
  }

  foreach ($copytables as $dummy => $table)
  {
    $table = str_replace ("_", "_5f", $table);
    echo "N_CopyDir (\"c:/export/$filter/metabase/ultra/$table\", \"html::metabase/ultra/$table\");<br>"; N_Flush(1);
    N_CopyDir ("c:/export/$filter/metabase/ultra/$table", "html::metabase/ultra/$table");
  }

  foreach ($copydirs as $dummy => $dir) {
    echo  "N_CopyDir (\"c:/export/$filter/$dir\", \"html::$dir\");<br>"; N_Flush(1);
    N_CopyDir ("c:/export/$filter/$dir", "html::$dir");
  }
}

function SYNC_SendObjectAndFiles($to, $sgn, $object_id) {
  if (!$sgn) $sgn = IMS_SuperGroupName();

  $scedulekey = "sendobjectandfiles#$sgn#$object_id";
  $when = time() + 10;
  $input = array(
    "from" => N_CurrentServer(),
    "to" => $to,
    "sgn" => $sgn,
    "key" => $object_id
  );
  $what = '
    uuse("sys");
    global $runandcapture;
    $runandcapture = true;
    ob_start();
    SYS_ReplicateObjectAndFiles ($input["to"], $input["from"], $input["sgn"], $input["key"], true);
    $output = ob_get_contents();
    ob_end_clean();
    N_Log("sync", "single object: " . $input["sgn"] . " " . $input["key"], $output);
  ';

  N_AddModifyScedule($scedulekey, $when, $what, $input);

  return $scedulekey;
}

?>