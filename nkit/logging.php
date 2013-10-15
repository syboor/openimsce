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
  html::tmp/logging/$class/$date.log
  html::tmp/logging/$class/short/$date/$guid.txt
  html::tmp/logging/$class/long/$date/$guid.txt
*/

function N_TotalStateLog ($class, $line, $longdesc=null)
{
  global $REMOTE_ADDR, $REQUEST_METHOD, $encspec, $PHP_AUTH_USER;

  $details .= "<b>USER:</b> ".$PHP_AUTH_USER . " ";
  if ($_COOKIE["loguser"]) $details .= " (" . $_COOKIE["loguser"] . ") ";

  $details .= "<b>HOST:</b> ".getenv("HTTP_HOST")." ";
  $details .= "<b>SCRIPT:</b> ".getenv("SCRIPT_NAME")." ";
  $details .= "<b>REMOTE:</b> ".$REMOTE_ADDR." ";

  if ($encspec) {
    $es = SHIELD_Decode($encspec, true);
    if ($es && $es["title"]) $details .= "<b>ENCTITLE:</b> ".$es["title"]." ";
  }      

  $details .= "<br>"; 

  $details .= "<b>REQUEST:</b><br>" . substr (serialize ($_REQUEST), 0, 2000)."<br>";
  $details .= "<b>STACK:</b><br>" . N_Stack2String ();
  $details .= "<b>LOGLINE:</b> " . $line . "<br>";
  $details .= "<b>DETAILS:</b><br>" . $longdesc;

  N_Log ($class, $line, $details);
}

function N_PMLog ($class, $line, $shortdesc="", $longdesc="", $timeOut="") 
//gv 22-8-2012 timeOut staat default (met een lege str) op 60 sec en $timeOut wordt daarbij opgeteld, zie N_ProcessPMLogs()
{
  global $myconfig, $pmdeep;  
  global $pmlogkey;
  if ($myconfig["pmlogging"]!="yes") return;
  $pmdeep++;
  if ($pmdeep==1) {
    if (!$pmlogkey) $pmlogkey = N_GUID();
    $time = time();
    if ( is_numeric($timeOut)) {
    	$time += $timeOut;
    }
    MB_REP_Save ("local_pmlogs", $pmlogkey, array (
      "class" => $class,
      "line" => $line,
      "shortdesc" => $shortdesc,
      "longdesc" => $longdesc,
      "time" => $time
    ));
  }
  $pmdeep--;
}


function N_CleanupPMLog ()
{
  global $pmlogkey;
  if ($pmlogkey) MB_REP_Delete ("local_pmlogs", $pmlogkey);
}

function N_ProcessPMLogs ()
{
  $list = MB_MultiQuery ("local_pmlogs");
  foreach ($list as $key) {
    $object = MB_REP_Load ("local_pmlogs", $key);
    if (time() - $object["time"] > 60) {
      N_Log ($object["class"], "PMLOG: ".$object["line"], $object["shortdesc"], $object["longdesc"], $object["time"]);
      MB_REP_Delete ("local_pmlogs", $key);
    }
  }
}

function N_VisibleLog ($class)
{
  global $N_VisibleLog_Classes;
  $N_VisibleLog_Classes[$class] = $class;
}


// 20110630 KvD Translate any type to string
function N_TranslateType($x)
{
    //!! is_object($x) can be false but gettype can return "object"
    if (is_array($x) || is_object($x) || gettype($x) === "object")
      $x = var_export($x, true);
    else if (is_resource($x))
      $x = '' . $x;
    else if ($x === NULL)
      $x = '<NULL>';
    else if ($x === false)
      $x = '<false>';
    else if ($x === true)
      $x = '<true>';
    else if ($x === 0)
      $x = '0';
 return $x;
}


// log caller of N_Log by file, line and function
function N_LogW ($class, $line, $shortdesc="", $longdesc="", $time="", $level=0)
{
  $trace = debug_backtrace();
  $line = $trace[$level]['file'] . ' ' . $trace[$level]['line'] . ' ' . $trace[$level+1]['function'] . "()\n". $line;
  return N_Log ($class, $line, $shortdesc, $longdesc, $time);
}


// 20110630 KvD
// $shortdesc and $longdesc can be arrays or other types now
// $class == "-": log to stdout instead of file
function N_Log ($class, $line, $shortdesc="", $longdesc="", $time="")
{ 
  global $N_VisibleLog_Classes;
  global $busy_loging, $activesupergroupname, $PHP_AUTH_USER;
  if ($N_VisibleLog_Classes[$class]) { 
    echo "N_Log: $class: $line<br>";
  }
  if (!$busy_loging) {
    $busy_loging = true;

    if ($PHP_AUTH_USER && $_COOKIE["loguser"]) {
      global $ims_disableflex;
      $ims_disableflex_old = $ims_disableflex;
      $ims_disableflex = "yes";
      $user_id = $PHP_AUTH_USER;
      $line = str_replace($user_id, $user_id . " (" . $_COOKIE["loguser"] . ")", $line);
      if ($activesupergroupname) {
        $username = MB_Fetch("shield_".$activesupergroupname."_users", $user_id, "name");
        if ($username) {
          $line = str_replace($username, $username . " (" . $_COOKIE["loguser"] . ")", $line);
        }
      }
      $ims_disableflex = $ims_disableflex_old;
    }

    $class = str_replace ("/", "_", $class);
    $class = str_replace ("\\", "_", $class);
    $class = str_replace ("__", "_", $class);
    if (!$time) $time = time();

    if ($shortdesc !== "") $shortdesc = N_TranslateType($shortdesc);
    if ($longdesc !== "") $longdesc =  N_TranslateType($longdesc);

    if (function_exists("N_Log_Filter")) {
      global $busy_logfilter;
      if (!$busy_logfilter) {
        $busy_logfilter = true;
        $line = N_Log_Filter($line);
        if ($shortdesc) $shortdesc = N_Log_Filter($shortdesc);
        if ($longdesc) $shortdesc = N_Log_Filter($longdesc);
        $busy_logfilter = false;        
      }
    }

    $guid = N_GUID();
    $date = date ("Ymd", $time);
    $line = str_replace ("<br>", " ", $line);
    $line = str_replace (chr(10), " ", $line);
    $line = str_replace (chr(13), " ", $line);
    $line = str_replace ("  ", " ", $line);
    $line = str_replace ("  ", " ", $line);
    $line = str_replace ("  ", " ", $line);
    if (strlen($line)>200) {
      if (!$shortdesc) {
        $shortdesc = "COMPLETE LINE: ".$line;
      } else if (!$longdesc) {   
        $longdesc = "COMPLETE LINE: ".$line;
      }
      $line = substr ($line, 0, 200)."...";
    }
    global $NKIT_StartTime;

    $line = "[".date ("H:i:s", $NKIT_StartTime)."] ".$line;
   
    // Allow logging to stdout rather than a file
    if ($class == "-" || trim($class) == "") {
      if ($shortdesc || $longdesc) {
          echo date ("His", $time)." ".$line;
          if ($shortdesc) echo "<pre>" . htmlentities($shortdesc) . "</pre>" ;
          if ($longdesc)  echo "<pre>" . htmlentities($longdesc) . "</pre>";
      }
    ///
    } else {
      N_AppendFile ("html::tmp/logging/$class/$date.log", $guid." ".date ("His", $time)." ".$line.chr(13).chr(10));
      if ($shortdesc || $longdesc) {
        if (N_DiskSpace () > 100000000) { // 100 MB
          if ($shortdesc) N_WriteFile ("html::tmp/logging/$class/short/$date/$guid.txt", $shortdesc);
          if ($longdesc) N_WriteFile ("html::tmp/logging/$class/long/$date/$guid.txt", $longdesc);
        }
      }
    }     
        
    $busy_loging = false;
  }
}

function N_CleanupLog ()
{
  $classes = N_Dirs ("html::tmp/logging/");
  foreach ($classes as $class => $dummy) {
    if ($class!="flexedit" && $class!="search" && $class!="myconfig") {
      $longs = N_Dirs ("html::tmp/logging/$class/long");
      foreach ($longs as $long) {
        $age = (time()-  N_BuildDate (substr ($long, 0, 4), substr ($long, 4, 2), substr ($long, 6, 2))) / 86400;
        if ($age > 7) {
          MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(getenv("DOCUMENT_ROOT")."/tmp/logging/$class/long/$long");
          rmdir (N_CleanPath (getenv("DOCUMENT_ROOT")."/tmp/logging/$class/long/$long"));
        }
      }
      $shorts = N_Dirs ("html::tmp/logging/$class/short");
      foreach ($shorts as $short) {
        $age = (time()-  N_BuildDate (substr ($short, 0, 4), substr ($short, 4, 2), substr ($short, 6, 2))) / 86400;
        if ($age > 14) {
          MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(getenv("DOCUMENT_ROOT")."/tmp/logging/$class/short/$short");
          rmdir (N_CleanPath (getenv("DOCUMENT_ROOT")."/tmp/logging/$class/short/$short"));
        }
      }
    }
  }
}

?>
