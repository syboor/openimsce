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


 
/*  **** WARNING !!! ***
        nkitloader.php and dyna.php are almost similar, 
        if any of those changes the other probably has to change as well
  */

  include (getenv("DOCUMENT_ROOT") . "/nkit/safer.php");

  if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
  }


  $NKIT_StartTime = time();
  error_reporting (0);
  if ($ims_showerrors=="yes") {
    ini_set ("display_errors", true);
  } else {
    ini_set ("display_errors", false);
  }
  ini_set ("log_errors", false);
  // ini_set ("memory_limit", "512M");
  set_time_limit (4*3600); // 4 hours
  ini_set('pcre.backtrack_limit', 10000000); // prevent problems in some configurations
  ini_set('pcre.recursion_limit', 10000000); // prevent problems in some configurations
  ignore_user_abort (1); // make sure we will reach N_Exit();
  setlocale (LC_ALL, "us_US"); // make sure all settings are us based (e.g. decimal separator)
  header ("Content-Type: text/html; charset=ISO-8859-1"); // make sure UTF-8 is not used

  global $ims_speed;

  if (get_magic_quotes_runtime()) die ("Configuration error: magic_quotes_runtime setting in php.ini is on (should be off), please contact your system administrator!");

  $debugcommand = $debug;
 
  if ($debug=="tune") {
    $debug = "yes";
    $flush = "no";
  }

  if ($debug=="speed") {
    $debug = "EXIT COMPLETED";
    $flush = "no";
  }

  if (!$debug && $ims_speed=="yes") {
    $debug = "EXIT COMPLETED";
    $flush = "no";
  }

  if ($debug=="profile") {
    $debug = "";
    $flush = "no";
    $profiling = "yes";
  }

  // Constants for use in myconfig.php
  define("ML_WIZARDS", 1);
  define("ML_FIELDS", 2);
  define("ML_WORKFLOWS", 4);
  define("ML_DMSMETA", 8);
  define("ML_FOLDERS", 16);
  define("ML_CMSMETA", 32); // Automatic, based on "sitelanguages" setting. No need to set this.
  define("ML_ALL", 127);

  // load machine related configuration data
  global $myconfig, $myconfig_md5;
  $myconfig["coredirs"] = array ("", "nkit", "nusoap", "cmis", "openims", "private", "rsa", "kit");
  $myconfig["coreskiprootfiles"] = array ("myconfig.php", ".htaccess", "robots.txt");
  $myconfig["coreskiprootfilescontaining"] = array ("sitemap.xml");
  $myconfig["coretables"] = array ("multilang");
  $myconfig["localdirs"] = array ("_private", "dfc", "logs", "metabase", "searchindex", "stats", "tmp", "usage", "config_*", "cgi-bin", "hscache", "phpMyAdmin", "backups", "mrtg", "_vti*", "images");
  $myconfig["localtables"] = array ("*scedule_*", "temp", "test", "urpc_packages", "encoded_objects", "config_servers");

  include (getenv("DOCUMENT_ROOT") . "/myconfig.php");
  if ($myconfig["allowarraysinrequest"] == "no") {  
    foreach ($_REQUEST as $var => $val) {  
      if (is_array ($val)) {  
        if (($var == "folders") || ($var == "field_postsoort")) continue;   // gv 14-8-2009 field_postsoort is voor Email registrie 
        if (strpos($var, "array") !== false) continue; // all variables that have "array" in the name, are allowed
        if (strpos(getenv("SCRIPT_NAME"), "doku.php") !== false) continue; // dokuwiki  
        if (substr(getenv("SCRIPT_NAME"), 0, 22) == "/openims/libs/tinymce/") continue; // tinymce ibrowser 
        if (substr(getenv("SCRIPT_NAME"), 0, 28) == "/openims/libs/tinymcejquery/") continue; // tinymce ibrowser 
        if (substr(getenv("SCRIPT_NAME"), 0, 12) == "/private/php") continue; 
        die ("nope"); // keep Liesbeth away  
      }  
    }  
  }

  if (file_exists (getenv("DOCUMENT_ROOT")."/config/")) $d = dir(getenv("DOCUMENT_ROOT")."/config/");
  if ($d) while (false !== ($entry = $d->read())) { // load all site related configuration data files
      if ($entry!="." && $entry!=".." && $entry!="flex") {
        if (file_exists (getenv("DOCUMENT_ROOT") . "/config/$entry/siteconfig.php")) {
          include (getenv("DOCUMENT_ROOT") . "/config/$entry/siteconfig.php");
        }
      }
  }

  if ($d) $d->close();
  $myconfig_md5 = md5 (serialize ($myconfig));

  if($myconfig["memory_limit"]) {
    ini_set ("memory_limit", $myconfig["memory_limit"]);
  } else {
    ini_set ("memory_limit", "1024M");
  }

  if (ob_get_level()) ob_end_clean(); // kill useless attempts by php.ini and friends to do output buffering
  // Do not use ob_end_flush(). It flushes the http headers, even if no output has been produced yet (!)

  if ($myconfig["htmlcompression"]=="yes") {
    // Site to test this: http://leknor.com/code/gziped.php
    $uri = $REQUEST_URI;
    $compress = false;
    global $mode;
    if ($uri=="/") { // homepage
      $compress = true;
    } else if (preg_match ("'^/[^./?]*_(com|nl)/[^./?]*.php'", $uri)) { // CMS web page
      $compress = true;
    } else if ((strpos (" ".$uri,"/portal") || strpos (" ".$uri,"/portaal")) && !strpos(" ".$uri, "edit.php")) {
      $compress = true;
    } else if ($mode=="cms") {
      $compress = true;
    } else if ($mode=="dms") {
      $compress = true;
    } else if ($mode=="bpms") {
      $compress = true;
    }
    if ($compress) {
      global $runandcapture;
      $runandcapture = true; // disable N_Flush();
      ob_start("ob_gzhandler");
    } else {
      ob_start();
    }
  } else {
    ob_start();
  }

  if ($myconfig["chmod"]) {
    $umask = (0777 & ~$myconfig["chmod"]);
    umask($umask);
  } else {
    umask(0);
  }

  function N_Flush ($mode=0)
  // 0            no extra's (default)
  // 1            70000 spaces (apparant size of CGI/FastCGI buffer space)
  // -1 .. -999   70000 spaces for each elapsed x seconds (invisible in JavaScript code and most HTML)
  // N            70000 spaces wrapped in a "font" tag (invisible in HTML)
  {
    global $runandcapture;
    if ($runandcapture) return;
    if ($mode < 0) {
      $current = doubleval(preg_replace('!^0\.([0-9]*) ([0-9]*)$!','\\2.\\1',microtime()));
      global $last;
      if (!$last) {
        $last = $current;
        $mode = 70000 ;
      } else {
        $mode = 70000 * ($current-$last) / (-$mode);
        if ($mode > 70000 ) $mode = 70000 ;
        if ($mode < 64) $mode = 64;
        $last = $current;
      }
      echo str_repeat (" ", $mode);
    } else if ($mode == 1) {
      echo str_repeat (" ", 70000);
    } else if (("".$mode == "N") || ("".$mode == "n")) {
      echo "<font".str_repeat (" ", 70000)."></font>";
    } else if (("".$mode == "BR") || ("".$mode == "br")) {
      echo "<br>"; // needed for firefox
      echo str_repeat (" ", 70000); // needed for explorer
    }

    flush();
    ob_end_flush();
    flush();

    global $N_Flush_Outputfilter;
    if ($N_Flush_Outputfilter) {
      ob_start($N_Flush_Outputfilter);
    } else {
      ob_start();    
    }
  } 

  if ($ims_showerrors=="yes") {
    error_reporting (E_ALL ^ E_NOTICE ^ 8192); // show everything except for NOTICEs (8192 = E_DEPRECATED is new in php5.3 )
  } else {
    error_reporting (0); // ignore ALL errors
  }

  include (getenv("DOCUMENT_ROOT") . "/nkit/context.php");

  function N_Trace ($log, $line, $body2)
  {
    global $REMOTE_ADDR, $REMOTE_HOST;
    $body .= "When: ".date("l F j, Y, G:i:s O")."\n";
    $body .= "Server: ".N_CurrentServer ()."\n";
    $body .= "HTTP host: ".getenv("HTTP_HOST")."\n";
    $body .= "Script name: ".getenv("SCRIPT_NAME")."\n";
    $body .= "Query string: ".getenv("QUERY_STRING")."\n";
    $body .= "Remote address: ".$REMOTE_ADDR."\n";
    $body .= "Remote host: ".gethostbyaddr ($REMOTE_ADDR)."\n";
    $body .= "Stack trace:\n";
    if (function_exists ("debug_backtrace")) {
      $stack = debug_backtrace();
       foreach ($stack as $dummy => $specs) {
         $body .= "   * ".$specs["function"]." called from ".$specs["file"]." line ".$specs["line"]."\n";
         if (is_array ($specs["args"])) foreach ($specs["args"] as $arg) {
           $body .= "      - ";
           $body .= "[".gettype ($arg)."] ";
           if (is_scalar ($arg)) {
             $arg = str_replace (chr(10), " ", $arg);
             $arg = str_replace (chr(13), " ", $arg);
             if (strlen($arg) > 80) {
               $body .= substr ($arg, 0, 80)."...";
             } else {
               $body .= $arg;
             }
           }
           $body .= "\n";
         }
      }  
    } else {
      $body .= "N/A\n";         
    }
    N_Log ($log, $line, $body, $body2);
  }

  function N_ReportError ($where, $what, $details="")
  {
    global $reporting_error;
    if (!$reporting_error) {
      $reporting_error = true;
      global $REMOTE_ADDR, $REMOTE_HOST;
      $subject = $where.": ".$what;
      $body .= "Where: ".$where."\n";
      $body .= "What: ".$what."\n";
      $body .= "When: ".date("l F j, Y, G:i:s O")."\n";
      $body .= "Server: ".N_CurrentServer ()."\n";
      $body .= "HTTP host: ".getenv("HTTP_HOST")."\n";
      $body .= "Script name: ".getenv("SCRIPT_NAME")."\n";
      $body .= "Query string: ".getenv("QUERY_STRING")."\n";
      $body .= "Remote address: ".$REMOTE_ADDR."\n";
      $body .= "Remote host: ".gethostbyaddr ($REMOTE_ADDR)."\n";
      $body .= "Stack trace:\n";
      if (function_exists ("debug_backtrace")) {
        $fullstack = debug_backtrace();
        $stack = array();
        // Find 'userErrorHandlerFilter' etc and delete everything 'after' that
        foreach (array_reverse($fullstack) as $dummy => $specs) {
          if ($specs["function"] == "userErrorHandlerFilter" || $specs["function"] == "userErrorHandler" || $specs["function"] == "N_ReportError" || $specs["function"] == "trigger_error") {
            break;            
          }
          array_unshift($stack, $specs);
        }
        foreach ($stack as $dummy => $specs) {
           $body .= "   * ".$specs["function"]." called from ".$specs["file"]." line ".$specs["line"]."\n";
           if (is_array ($specs["args"])) foreach ($specs["args"] as $arg) {
             $body .= "      - ";
             $body .= "[".gettype ($arg)."] ";
             if (is_scalar ($arg)) {
               $arg = str_replace (chr(10), " ", $arg);
               $arg = str_replace (chr(13), " ", $arg);
               if (strlen($arg) > 80) {
                 $body .= substr ($arg, 0, 80)."...";
               } else {
                 $body .= $arg;
               }
             }
             $body .= "\n";
           }
        }  
      } else {
        $body .= "N/A\n";         
      }
      N_Log ("errors", $subject, $body);
      $reporting_error = false;
    }
  }

  function N_Die ($message)
  {
    N_ErrorHandling (true); //LF20120220
    echo "<br><b>FATAL ERROR / FATALE FOUT</b><br>";
    flush();
    ob_end_flush();
    flush();
    userErrorHandler (1, "DIE: $message", "", "", "");
    die("<br>Execution terminated<br>");
  }

  function N_ShowError ($message)
  {
    userErrorHandler (1, "ERROR: $message", "", "", "");
  }

  $error_handling = true;

  function N_ErrorHandling ($mode)
  {
    global $error_handling;
    $error_handling = $mode;
  }

  function N_Trace4Errors ($line)
  {
    global $debuglog, $lines;
    $debuglog[++$lines] = substr ($line, 0, 500)."\n";
    if ($lines>420) unset ($debuglog[$lines-210]);
  }

  function userErrorHandler ($errno, $errmsg, $filename, $linenum, $vars) {
    global $ims_showerrors, $ims_shownotices, $disabledebugging, $doing, $error_handling, $debuglog, $reportvars, $lines, $debuglogtotal;

    $errortype = array (
              1   =>  "Error",
              2   =>  "Warning",
              4   =>  "Parsing Error",
              8   =>  "Notice",
              16  =>  "Core Error",
              32  =>  "Core Warning",
              64  =>  "Compile Error",
              128 =>  "Compile Warning",
              256 =>  "User Error",
              512 =>  "User Warning",
              1024=>  "User Notice");

    if (!$error_handling) {
      //if (!headers_sent()) {
      //  header('HTTP/1.0 200'); // For some reason this becomes 500, we want 200 e.g. to keep the Transfer Agent happy
      //  header('Status: 200'); // fastcgi
      //}
      return;
    }

    // determine if @ has been used, see also http://www.php.net/manual/en/language.operators.errorcontrol.php
    $backup = error_reporting(); 
    error_reporting ($backup);
    set_error_handler("userErrorHandlerFilter");
    if (!$backup) return;

    if ($errno==8 && $ims_shownotices!="yes") return;
    if ($errno==8 && !strpos ($filename, "eval()'d")) return; // Only show Notices for none core code

    if ($errno!=2048 && ($errno!=8) && ($errno != 8192) ) {
      N_Debug ("userErrorHandler ($errno, $errmsg, $filename, $linenum, $vars)");
    }
    if (strpos ($errmsg, "annot add header information")) return;
    if (strpos ($errmsg, "nvalid argument supplied for foreach")) return;

    global $errorcounter;
    $errorcounter[$errmsg][$filename][$linenum]++;
    if ($errorcounter[$errmsg][$filename][$linenum]>3) return;
    if ($errorcounter[$errmsg][$filename][$linenum]==3) $errmsg .= " (will ignore these from now on)";

    if (($ims_showerrors=="yes") && ($errno!=2048) && ($errno != 8192) ) {
      if (!$disabledebugging) {
        if (strlen ($errmsg)>2000) {
          echo "<b>$errortype[$errno]: $filename ($linenum): </b> ".substr ($errmsg, 0, 2000)."...<br>";
        } else {
          echo "<b>$errortype[$errno]: $filename ($linenum): </b> $errmsg<br>";
        }
      } else {
        echo "<b>$errortype[$errno]</b> (see log)<br>";
      }
    } else if (($errno!=2048) && ($errno != 8192)) {
      if (!$disabledebugging) {
        global $capturederrors;
        if (strlen ($errmsg)>2000) {
          $capturederrors .= "<b>$errortype[$errno]: $filename ($linenum): </b> ".substr ($errmsg, 0, 2000)."...<br>";
        } else {
          $capturederrors .= "<b>$errortype[$errno]: $filename ($linenum): </b> $errmsg<br>";
        }
      } else {
        $capturederrors .= "<b>$errortype[$errno]</b> (see log on {$myconfig["myname"]}<br>)";
      }
    }

    if ($errno != E_NOTICE && $errno!=2048 && ($errno != 8192) ) {  // 2048 = E_STRICT, 8192 = E_DEPRECATED
      $err = "<errorentry>\n";
      $err .= "\t\t\t<errornum>".$errno."</errornum>\n";
      $err .= "\t\t\t<errortype>".$errortype[$errno]."</errortype>\n";
      $err .= "\t\t\t<errormsg>".$errmsg."</errormsg>\n";
      $err .= "\t\t\t<scriptname>".$filename."</scriptname>\n";
      $err .= "\t\t\t<scriptlinenum>".$linenum."</scriptlinenum>\n";
      if ($lines < 200) {
        for ($i=1; $i<=$lines; $i++) $debuglogtotal .= $i.": ".$debuglog[$i];
      } else {
        for ($i=1; $i<=100; $i++) $debuglogtotal .= $i.": ".$debuglog[$i];
        for ($i=$lines-100; $i<=$lines; $i++) $debuglogtotal .= $i.": ".$debuglog[$i];
      }
      $err .= "\t\t\t<debuglog>\n$debuglogtotal\t\t\t</debuglog>\n";
      if ($reportvars=="yes") {
        $err .= "\t\t\t<vartrace>".N_PrettyXML(N_Object2XML ($vars))."</vartrace>\n";
      }
      $err .= "\t\t</errorentry>\n";
      N_ReportError ("$filename ($linenum)", $errmsg, $err);
    }
  }

  $safe_mode = false;

  function N_SafeMode()
  {
    global $safe_mode;
    $safe_mode = true;
  }

  include (getenv("DOCUMENT_ROOT") . "/nkit/build.php");
  include_once (getenv("DOCUMENT_ROOT") . "/openims/libs/adodb/adodb-time.inc.php");
  include (getenv("DOCUMENT_ROOT") . "/nkit/general.php");
  include (getenv("DOCUMENT_ROOT") . "/nkit/vvfile.php");
  include (getenv("DOCUMENT_ROOT") . "/nkit/logging.php");
  include (getenv("DOCUMENT_ROOT") . "/nkit/metabase.php");
   
  include (getenv("DOCUMENT_ROOT") . "/nkit/urpc.php");
  include (getenv("DOCUMENT_ROOT") . "/nkit/dfc.php");
     


  // Zend WinEnabler, seems to like this, it reduces memory leaks
  function userErrorHandlerFilter ($errno, $errmsg, $filename, $linenum, $vars) {
    global $ims_shownotices;
    if ($errno==8 && $ims_shownotices!="yes") return;
    userErrorHandler ($errno, $errmsg, $filename, $linenum, $vars);
  }

  error_reporting (E_ALL); // show everything
  set_error_handler("userErrorHandlerFilter");

  if (function_exists (set_exception_handler)) {
    function userExceptionHandler($exception)
    {
      N_DIE ("FATAL EXCEPTION (PHP5) ".$exception->__toString());
    } 
    set_exception_handler ("userExceptionHandler");
  }

  if (function_exists ("error_get_last")) {
    register_shutdown_function('check_last_error');
  }

  function check_last_error() {
    if ($e = error_get_last()) {
      // Only report errors that can not be caught with set_error_handler
      $noncatchableerrors = E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING;
      if ($e["type"] & $noncatchableerrors) {
        N_Log("errors", "Non-reportable error: " . $e["message"], print_r($e, 1));
        // Note: since these errors are NOT fatal, there is no guarantee that we will be able to catch them
        // in the shutdown handler. Only if NO OTHER ERRORS occur (not even stupid notices) will we be able
        // to catch them.
      } 
    }
  }

  // Defined extremely early to override design flaw in skin managment
  function SKIN_ExtraBar ($menu) { 
    if ($_REQUEST["mode"]=="dbm") {
      uuse ("dbmuif");
      return DBMUIF_ExtraBar ();
    } else {
      return $menu["html_title"];    
    }
  }

  function UUSE_DO ($code) {
    global $uuse_code_stack, $uuse_call_depth;
    if ($uuse_call_depth) {
      $uuse_code_stack .= $code; // store for delayed execution
    } else {
      eval ($code); // direct execution
    }
  }
  
  function UUSE () {
    global $uuse_code_stack, $uuse_call_depth;
    $list = func_get_args();
    reset($list);
    while (list(,$module)=each($list)) {
      $module = strtolower($module);
      global $UUSE_Modules;
      if ($UUSE_Modules[$module]!="loaded")
      {
        $UUSE_Modules[$module]="loaded";
        N_Debug ("UUSE Loading $module");
        $uuse_call_depth++;
        if (N_OpenIMSCE()) {
          @include (getenv("DOCUMENT_ROOT") . "/nkit/uuse_" . $module . ".php");
        } else {
          include (getenv("DOCUMENT_ROOT") . "/nkit/uuse_" . $module . ".php");
        }
        $uuse_call_depth--;
      }
    }
    if (!$uuse_call_depth) {
      if ($uuse_code_stack) {
        $tmp = $uuse_code_stack;
        $uuse_code_stack = ""; 
        eval ($tmp); // execute delayed code
      }
    }
  }

  function N_IPRangeCheck($cidr, $ip) {
    /* Returns true if $ip is within the IP range indicated by $cidr.
       $cidr can be either <<<IP>>>/<<<prefix_size>>> or just <<<IP>>>

       // Test code:
       foreach (array('192.168.220.0/24', '192.168.220.0', '192.168.0.0/16', '127.0.0.1/32') as $cidr) {
         foreach (array('192.168.220.130', '192.168.221.130', '192.168.220.0') as $ip) {
         $result =  N_IPRangeCheck($cidr, $ip);
           echo "CIDR: $cidr, IP: $ip, result: " . ($result ? "YES" : "NO") . "<br/>";
         }
       }

       The bitwise operators work fine with IP's that become negative numbers on 32 bit platforms.
       The < and > operators will NOT work correctly, so be careful if you want to support <<<BEGIN_IP>>>-<<<END_IP>>> notation.

     */

    $checkip = ip2long($ip);
    if (!$checkip) return false;

    if ($pos = strpos($cidr, '/')) {
      $netip = ip2long(substr($cidr, 0, $pos));
      $bits = substr($cidr, $pos+1);
      // Sanity check that $bits is a number (prefix size) and not a netmask.
      if ($bits != intval($bits)) return false;
      if ($bits > 32) return false;
      if (($bits == 0) && ($bits !== '0')) return false; // Allow IP/0, but do not allow IP/invalid_string_that_happens_to_cast_to_0
      if ($bits < 0) return false;
    } else {
      $netip = ip2long($cidr);
      $bits = 32;
    }
    if (!$netip) return false;
    $netmask = bindec(str_pad('', $bits, '1') . str_pad('', 32-$bits, '0') );

    if (($netip & $netmask) == ($checkip & $netmask)) {
      return true;
    }

    return false;

  }

  global $disabledebugging;
  $disabledebugging = true;
  if (is_array ($myconfig["allowdebuggingfrom"])) { // Disable with: $myconfig["allowdebuggingfrom"] = array("1.0.0.0/0");
    $disabledebugging = true;
    $myconfig["allowdebuggingfrom"][] = "127.0.0.1";
    $myconfig["allowdebuggingfrom"][] = $_SERVER["SERVER_ADDR"]; 
    foreach ($myconfig["allowdebuggingfrom"] as $cidr) {
      if (N_IPRangeCheck($cidr, $_SERVER["REMOTE_ADDR"])) $disabledebugging = false;
    }
  }

  global $disableflex;
  if ($disableflex!="yes") {
    uuse ("flex");
    FLEX_LoadAllLowLevelFunctions ();
  }

  global $ims_disableflex; // cookie
  if ($ims_disableflex=="yes") {
    // LF20091201: Disable cookielogin (because a fancy cookielogin page tends to give fatal "function xxx doesnt exist" errors when you use disableflex)
    foreach ($myconfig as $myconfig_sgn => $myconfig_specs) {
      if (is_array($myconfig_specs) && $myconfig_specs["cookielogin"]) unset($myconfig[$myconfig_sgn]["cookielogin"]);
    }
  }

  foreach ($myconfig as $myconfig_sgn => $myconfig_specs) {
    if (is_array($myconfig_specs) && is_array($myconfig_specs["ml"]["sitelanguages"])) $myconfig[$myconfig_sgn]["ml"]["metadatalevel"] = $myconfig[$myconfig_sgn]["ml"]["metadatalevel"] | ML_CMSMETA;
  }

  $host = getenv ("HTTP_HOST");
  global $myconfig;
  if ($myconfig["redirect"][$host]) {
    $url = N_MyVeryFullURL();
    $parts = N_ExplodeURL ($url);
    $parts["host"] = $myconfig["redirect"][$host];
    $url = N_ImplodeURL ($parts);
    N_Redirect ($url, 301);
  }

  if (strpos (N_MyFullURL (), "AUTOGUIDPARAM")) {
    N_Redirect (str_replace ("AUTOGUIDPARAM", N_GUID(), N_MyFullURL ()));
  }

  if ($myconfig["specialnonfunctioncode"]) {
    eval ($myconfig["specialnonfunctioncode"]);
  }

  // added to implement md-logic for bpms-forms in cms
  global $newcase, $thecase;
  if (!$newcase && !$thecase) $newcase = N_GUID();

  uuse ("flex");
?>