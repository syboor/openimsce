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



function SELFTEST_TestFullDirectoryAccessAll ()
{
  global $myconfig;

  echo '<font face="arial,helvetica" size="3">'.ML("Toegangsrechten Apache","Access rights Apache").' ';
  $result = false;
  N_FLush(1);

  if (($r=SELFTEST_TestFullDirectoryAccess ("html::"))!="ok") {
    echo '<font color="FF0000"><b>ERROR</b> (Apache root '.$r.')</font><br>';
  } else if (($r=SELFTEST_TestFullDirectoryAccess ("html::tmp"))!="ok") {
    echo '<font color="FF0000"><b>ERROR</b> (Apache root + /tmp '.$r.')</font><br>';
  } else if (($r=SELFTEST_TestFullDirectoryAccess ($myconfig["tmp"]))!="ok") {
    echo '<font color="FF0000"><b>ERROR</b> ($myconfig["tmp"] '.$r.')</font><br>';
  } else {
    echo '<font color="008000"><b>OK</b></font><br>';
  }

  N_FLush(1);
}

function SELFTEST_TestFullDirectoryAccess ($dir)
{
  N_ErrorHandling (false);
  $result = SELFTEST_DoTestFullDirectoryAccess ($dir);
  return $result;
  N_ErrorHandling (true);
}

//20120727 KvD CORE-34
function SELFTEST_Googlesuggestions ()
{
  global $myconfig;

  if ($myconfig[IMS_SuperGroupName()]["searchsuggestspelling"]=="yes") {
    echo '<font face="arial,helvetica" size="3">Google Search suggestions ';
    $result = false;
    N_FLush(1);
  
    $e = error_reporting(0);
    if ($res = file_get_contents("http://www.google.com")) { // file_get_contents for NuSoap compatibility
      echo '<font color="008000"><b>OK</b></font><br>';
    } else {
      echo '<font color="FF0000"><b>ERROR</b></font><br>';
    }
    error_reporting($e);
    N_Flush(1);
  }
}
///
// 20130115 KVD CORE-43 Selftest checks overruled IP address when master
function SELFTEST_Conversion ()
{
  global $myconfig;
  uuse("grid");

  if (GRID_Master() == "master" && ($conversion = trim($myconfig["wordserver"])) && $conversion != $myconfig["myname"])
  {
    echo '<font face="arial,helvetica" size="3">Conversion overruledetermineip ';
    $result = false;
    N_FLush(1);

    if (!$myconfig["overruledetermineip"][$conversion])
    {
      echo '<font color="FF8000"><b>WARNING</b></font><br>';
    } else {
      echo '<font color="008000"><b>OK</b></font><br>';
    }
    N_FLush(1);
  }
}


function SELFTEST_WordserverGridTest($server, $advstatuscheck = false) {
  uuse ("grid");

  // Note that the actual grid call may fail due to grid security.
  // $advstatuscheck only used when using multiple conversion servers
  
  $ip = GRID_DetermineIP ($server);
  if (!$ip) {
    echo '<font color="FF0000"><b>ERROR cannot determine IP</b></font><br>';
    return;
  }

  // Thanks to overruledetermineip, GRID_DetermineIP can return while ping doesnt work or reaches the wrong server
  $ping = GRID_SimpleCall($ip, "ping");
  if (!$ping["name"]) {
    echo '<font color="FF0000"><b>ERROR (IP: '.$ip.')</b></font><br>';
    return;
  }
  if ($ping["name"] != $server) { 
    echo '<font color="FF0000"><b>ERROR: received response from wrong server: ' . $ping["name"] . '</b></font><br>';
    return;
  }
  $offset = abs($ping["time"] - time());
  if ($offset > GRID_ServerDeadTimeout()) {
    echo '<font color="FF0000"><b>ERROR: clock mismatch: ' . $offset . '</b></font><br>';
    return;
  } elseif ($offset * 2 > GRID_ServerDeadTimeout()) {
    echo '<font color="FF8000"><b>WARNING: clock mismatch: ' . $offset . '</b></font> ';
    // Falling through. It should still work, it's only a warning that if the clock keeps drifting, it might stop working.
  }

  $test = GRID_RPC ($server, '$output = N_CurrentServer() . $input;', '123');
  if (substr($test, -3) != '123') {
    echo '<font color="FF0000"><b>ERROR (GRID security?)</b></font><br>';
    return;
  }

  if ($advstatuscheck) {
    uuse("word");
    $status = WORD_GetServerStatus($server);
    if ($status == "ERROR") {
      echo '<font color="FF0000"><b>ERROR: statuscheck reports ERROR</b></font><br>';
      return;
    } elseif ($status != "OK") {
      echo '<font color="FF0000"><b>ERROR: statuscheck missing or no response</b></font><br>';
      return;
    }
  }

  echo '<font color="008000"><b>OK</b></font><br>';
}

///
//20120809 KvD IMS-18
function SELFTEST_imagemagick ()
{
  global $myconfig;

  if ($myconfig["convert"]) {
    N_FLush(1);

    if (N_Windows() && $myconfig["ghostscript"]) {
      ob_start();
      echo N_XML2HTML(`gswin32c --help 2<&1`);
      $buf = ob_get_clean();
      ob_end_clean();
      echo '<font face="arial,helvetica" size="3">Ghostscript ';
      if (stripos($buf, "ghostscript") !== false) {
        echo '<font color="008000"><b>OK</b></font><br>';
      } else {
        echo '<font color="FF0000"><b>ERROR</b></font><br>';
      }
      N_FLush(1);
    }
    ob_start();
    $tmpjpeg = getenv("DOCUMENT_ROOT")."/tmp/checkup.jpg";
    // Gooi eventueel eerder gegenereerde jpeg weg
    if (is_file($tmpjpeg))
      unlink($tmpjpeg);
    $xx = $myconfig["convert"] . " " . getenv("DOCUMENT_ROOT")."/openims/checkup.pdf $tmpjpeg";
    echo `$xx`;
    $buf = ob_get_clean();
    
    echo '<font face="arial,helvetica" size="3">Imagemagick ';
    // Minimaal 2K groot
    if (@filesize($tmpjpeg) > 2048) {
      echo '<font color="008000"><b>OK</b></font><br>';
    } else {
      echo '<font color="FF0000"><b>ERROR</b></font><br>';
    }
    @unlink($tmpjpeg);
    N_FLush(1);
  }
}
///

function SELFTEST_DoTestFullDirectoryAccess ($dir)
{
  $file = N_CleanPath ($dir."/"."selftest_".N_GUID().".txt");
  $dir = N_CleanPath ($dir."/"."selftest_".N_GUID());

  N_MkDir ($dir);
  if (!file_exists ($dir)) return "mkdir failed";

  N_WriteFile ($file, "123");
  if (N_ReadFile ($file) != "123") return "write failed";
  N_WriteFile ($file, "456");
  if (N_ReadFile ($file) != "456") return "rewrite failed";
  N_DeleteFile ($file);
  if (file_exists ($file)) return "delete file failed"; 

  $file = N_CleanPath ($dir."/"."selftest_".N_GUID().".txt");

  N_WriteFile ($file, "123");
  if (N_ReadFile ($file) != "123") return "write to new dir failed";
  N_WriteFile ($file, "456");
  if (N_ReadFile ($file) != "456") return "rewrite to new dir failed";
  N_DeleteFile ($file);
  if (file_exists ($file)) return "delete file failed"; 

  rmdir ($dir);
  if (file_exists ($dir)) return "rmdir failed";

  return "ok";
}

function SELFTEST_CheckConfig ($file) 
{
  $error = false;
  $content = N_ReadFile ($file);
  for ($i=145; $i<=148; $i++) if (!$error && strpos (" $content", chr($i))) $error = true;
  if ($error) echo " $file ";
  return $error;
}

function SELFTEST_TestAll ()
{
  global $myconfig;

  echo '<br><font face="arial,helvetica" size="3"><b>'.ML("Controle configuratie OpenIMS","OpenIMS configuration check").'</b><br>';
  echo ML("WAARSCHUWING: Controleer of 'Zelftest voltooid' verschijnt!","WARNING: Check if 'Selftest completed' appears")."<br><br>";

  SELFTEST_TestFullDirectoryAccessAll ();

  echo '<font face="arial,helvetica" size="3">'.ML("Data compressie geheugen (gzip / gunzip)","Data compression memory (gzip / gunzip)").' ';
  $result = false;
  if (N_Gunzip (N_Gzip ("test"))=="test") $result = true;
  N_FLush(1);
  if ($result) {
    echo '<font color="008000"><b>OK</b></font><br>';
  } else {
    echo '<font color="FF0000"><b>ERROR</b></font><br>';
  }
  N_FLush(1);

  echo '<font face="arial,helvetica" size="3">'.ML("Data compressie bestand (gzip / gunzip)","Data compression file (gzip / gunzip)").' ';
  $result = false;
  N_WriteFile ("html::tmp/testfile.txt", "hello world");
  N_GzipFile ("html::tmp/testfile.txt.gz", "html::tmp/testfile.txt");
  N_WriteFile ("html::tmp/testfile.txt", "");
  N_GunzipFile ("html::tmp/testfile.txt", "html::tmp/testfile.txt.gz");
  if (N_ReadFile ("html::tmp/testfile.txt") == "hello world") $result = true;
  N_FLush(1);
  if ($result) {
    echo '<font color="008000"><b>OK</b></font><br>';
  } else {
    echo '<font color="FF0000"><b>ERROR</b></font><br>';
  }
  N_FLush(1);

  echo '<font face="arial,helvetica" size="3">'.ML("Data compressie (gzcompress / gzuncompress)","Data compression (gzcompress / gzuncompress)").' ';
  $result = false;
  if ($myconfig["hasgzcompress"] == "yes" && gzuncompress (gzcompress ("test"))=="test") $result = true;
  // LF20080122: if gzcompress function doesnt exist, fatal error leads to termination of selftest, and that sucks
  N_FLush(1);
  if ($result) {
    echo '<font color="008000"><b>OK</b></font><br>';
  } else {
   if ($myconfig["hasgzcompress"] == "yes") {
     echo '<font color="FF0000"><b>ERROR</b></font><br>';
   } else {
     echo '<font color="FF8800"><b>WARNING</b></font><br>';
   }
  }
  N_FLush(1);

  echo '<font face="arial,helvetica" size="3">'.ML("Data compressie (unzip / zip)","Data compression (unzip / zip)").' ';
  global $myconfig;
  if(!$myconfig["unzip"]) {
    echo '<font color="FF0000"><b>ERROR (' . ML("unzip is niet ingesteld in de machine configuratie","unzip has not been configured in machine configuration") . ')</b></font><br>';
  } 
  if (!$myconfig["zip"]) {
    echo '<font color="FF0000"><b>ERROR (' . ML("zip is niet ingesteld in de machine configuratie","zip has not been configured in machine configuration") . ')</b></font><br>';
  }

  if ($myconfig["unzip"]) {
    $exe = $myconfig["unzip"] . " -t " . getenv("DOCUMENT_ROOT")."/openims/checkup.zip";
    $result =`$exe`;
    if(strpos(" " . $result, "No errors detected in compressed data of "))
      $result = true;
    else 
      $result = false;
    if ($result) {
      echo '<font color="008000"><b>OK</b></font><br>';
    } else {
      echo '<font color="FF0000"><b>ERROR</b></font><br>';
    }
    N_FLush(1);
  }
  if (!N_OpenIMSCE() || $myconfig["antiword"]) {
    // Antiword is considered optional for CE, but required for EE
    echo '<font face="arial,helvetica" size="3">'.ML("Conversie","Conversion").' (doc -> txt) ';
    $result = false;
    DFC_Disable();
    if (strpos (" ".IMS_DoDoc2Text (getenv("DOCUMENT_ROOT")."/openims/checkup.doc", "doc"), "Test 1 2 3")) $result = true;
    N_FLush(1);
    if ($result) {
      echo '<font color="008000"><b>OK</b></font><br>';
    } else {
      echo '<font color="FF0000"><b>ERROR</b></font><br>';
    }
    N_FLush(1);
  }

  echo '<font face="arial,helvetica" size="3">'.ML("Archivering (tar)","Archiving (tar)").' ';
  $result1 = $result2 = false;
  $tar = $myconfig["tarcommand"];
  $file = getenv("DOCUMENT_ROOT")."/openims/checkup.tar"; // checkup.tar now contains checkup.txt
  $output = `$tar -t < $file`; // gives file name
  $output .= `$tar -x -O < $file`; // gives file contents
  if (strpos (" ".$output, "checkup.txt") && strpos (" ".$output, "Test") && strpos (" ".$output, "123")) $result1 = true;
  if (!N_Windows() || $myconfig["maxbackupsegmentsize"]) {
    $file = getenv("DOCUMENT_ROOT")."/openims/checkup.tgz";
    $output = `$tar -t -z < $file`; // gives file name
    $output .= `$tar -x -z -O < $file`; // gives file contents
    if (strpos (" ".$output, "checkup.txt") && strpos (" ".$output, "Test") && strpos (" ".$output, "123")) $result2 = true;
  } else {
    $result2 = true;
  }
  if ($result1 && $result2) {
    echo '<font color="008000"><b>OK</b></font><br>';
  } elseif ($result1) {
    echo '<font color="FF8000"><b>WARNING</b></font><br>';
  } else  {
    echo '<font color="FF0000"><b>ERROR</b></font><br>';
  }
  N_FLush(1);

  echo '<font face="arial,helvetica" size="3">'.ML("WDDX (PHP bibliotheek)","WDDX (PHP library)").' ';
  $result = N_Proper_wddx_serialize_vars ();
  if ($result) {
    echo '<font color="008000"><b>OK</b></font><br>';
  } else {
    if (function_exists ("wddx_serialize_value")) {
      echo '<font color="FF0000"><b>ERROR (BUG IN PHP)</b></font><br>';
    } else {
      echo '<font color="FF0000"><b>ERROR</b></font><br>';
    }
  }
  N_FLush(1);

  echo '<font face="arial,helvetica" size="3">'.ML("IMAP (PHP bibliotheek)","IMAP (PHP library)").' ';
  $result = function_exists ("imap_open");
  if ($result) {
    echo '<font color="008000"><b>OK</b></font><br>';
  } else {
    echo '<font color="FF8000"><b>WARNING</b></font><br>';
  }
  N_FLush(1);

  echo '<font face="arial,helvetica" size="3">'.ML("GD (PHP bibliotheek)","GD (PHP library)").' ';
  $result = function_exists ("imagejpeg");
  if ($result) {
    echo '<font color="008000"><b>OK</b></font><br>';
  } else {
    echo '<font color="FF8000"><b>WARNING</b></font><br>';
  }
  N_FLush(1);

  echo '<font face="arial,helvetica" size="3">'.ML("XML Parser (PHP bibliotheek)","XML Parser (PHP library)").' ';
  $result = function_exists ("xml_parser_create");
  if ($result) {
    echo '<font color="008000"><b>OK</b></font><br>';
  } else {
    echo '<font color="FF8000"><b>WARNING</b></font><br>';
  }
  N_FLush(1);

  echo '<font face="arial,helvetica" size="3">'.ML("PHP instellingen","PHP settings").' ';
  $result = true;
  if (ini_get ("safe_mode")) $result = false;
  if (ini_get ("max_execution_time") < 900) $result = false;
  if (ini_get ("max_input_time") < 900) $result = false;
  if (str_replace ("M", "000000", ini_get ("upload_max_filesize")) < 10000000) $result = false;
  if (str_replace ("M", "000000", ini_get ("post_max_size")) < 10000000) $result = false;
  if ($result) {
    echo '<font color="008000"><b>OK</b></font><br>';
  } else {
    echo '<font color="FF0000"><b>ERROR</b></font><br>';
  }
  N_FLush(1);

  echo '<font face="arial,helvetica" size="3">'.ML("HTTP ophalen","HTTP retrieval").' ';
  $result = false;
  global $VBrowser4GetPage;
  $VBrowser4GetPage->timeout = 15; 
  if (strpos (N_GetPage ("http://master.openims.com/openims/termsofuse.txt"),"OpenIMS")) $result = true;
  N_FLush(1);
  if ($result) {
    echo '<font color="008000"><b>OK</b></font><br>';
  } else {
    echo '<font color="FF8000"><b>WARNING (network restrictions?)</b></font><br>';
  }
  N_FLush(1);

  echo '<font face="arial,helvetica" size="3">'.ML("Config smartquotes check", "Config smartquotes check").' ';
  N_FLush(1);
  $error = false;
  if (SELFTEST_CheckConfig (getenv("DOCUMENT_ROOT") . "/myconfig.php")) $error = true;
  if (file_exists (getenv("DOCUMENT_ROOT")."/config/")) $d = dir(getenv("DOCUMENT_ROOT")."/config/");
  if ($d) while (false !== ($entry = $d->read())) { // load all site related configuration data files
      if ($entry!="." && $entry!=".." && $entry!="flex") {
        if (file_exists (getenv("DOCUMENT_ROOT") . "/config/$entry/siteconfig.php")) {
          if (SELFTEST_CheckConfig (getenv("DOCUMENT_ROOT") . "/config/$entry/siteconfig.php")) $error = true;
        }
      }
  }
  if ($error) {
    echo '<font color="FF0000"><b>ERROR</b></font><br>';
  } else {
    echo '<font color="008000"><b>OK</b></font><br>';  
  }
  N_FLush(1); 



  if (N_OpenIMSCE()) {
    $reqmem = 100;
  } else {
    $reqmem = 400;
  }
  echo '<font face="arial,helvetica" size="3">'.ML("$reqmem MB geheugen gebruiken","Use $reqmem MB of memory").' ';
  N_FLush(1);
  for ($i=1; $i<=($reqmem*100); $i++) $a[] = str_repeat ("*", 10000);
  unset ($a);
  echo '<font color="008000"><b>OK</b></font><br>';
  N_FLush(1); 



  N_FLush(1); 
  echo '<font face="arial,helvetica" size="3">'.ML("11 MB object naar XML database schrijven (laatste test)","Write 11 MB object to XML database (last test)").' ';
  $result = false;
  MB_REP_Save ("test", "test", str_repeat ("*", 11000000));
  if (11000000==strlen (MB_REP_Load ("test", "test"))) $result = true;
  N_FLush(1);
  if ($result) {
    if ("MYSQL" == MB_MUL_Engine("test")) {
      echo '<font color="008000"><b>OK</b></font><br>';
    } else {
      echo '<font color="FF8000"><b>WARNING: not using MySQL engine</b></font><br>';
    }
  } else {
    echo '<font color="FF0000"><b>ERROR</b></font><br>';
  }
  N_FLush(1); 

  echo '<font face="arial,helvetica" size="3">'.ML("Zelftest voltooid","Selftest completed").' ';
  echo '<font color="008000"><b>OK</b></font><br>';

  echo '<br>'; 

}
?>