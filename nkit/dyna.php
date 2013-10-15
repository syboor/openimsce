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
        if any of these changes the other probably has to change as well
  */

  include (getenv("DOCUMENT_ROOT") . "/nkit/safer.php");

  $dynastart = doubleval(preg_replace('!^0\.([0-9]*) ([0-9]*)$!','\\2.\\1',microtime()));

  // ini_set ("memory_limit", "512M");
  set_time_limit (4*3600); // 4 hours
  ignore_user_abort (1); // make sure we will reach N_Exit();
  setlocale (LC_ALL, "us_US"); // make sure all settings are us based (e.g. decimal separator)
  header ("Content-Type: text/html; charset=ISO-8859-1"); // make sure UTF-8 is not used

  global $activesupergroupname, $knownsupergroupname, $disableflex;
  $activesupergroupname = "unknown";
  $knownsupergroupname = true;
  $disableflex ="yes";

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

  if ($debug=="profile") {
    $debug = "";
    $flush = "no";
    $profiling = "yes";
  }

  // load machine related configuration data
  global $myconfig, $myconfig_md5;
  $myconfig["coredirs"] = array ("", "hsobjects", "nkit", "nusoap", "cmis", "openims", "private", "rsa", "whois", "fasted", "kit");
  $myconfig["coreskiprootfiles"] = array ("myconfig.php", ".htaccess", "robots.txt");
  $myconfig["coreskiprootfilescontaining"] = array ("sitemap.xml");
  $myconfig["coretables"] = array ("multilang");
  $myconfig["localdirs"] = array ("_private", "dfc", "logs", "metabase", "searchindex", "stats", "tmp", "usage", "config_*", "cgi-bin", "hscache", "phpMyAdmin", "backups", "mrtg", "_vti*", "images");
  $myconfig["localtables"] = array ("*scedule_*", "temp", "test", "urpc_packages", "encoded_objects", "config_servers");
  include (getenv("DOCUMENT_ROOT") . "/myconfig.php");

  // Missing on purpose (in nkitloader but not in dyna) is this logic:   if ($myconfig["allowarraysinrequest"] == "no") {  ...

  if($myconfig["memory_limit"]) {
    ini_set ("memory_limit", $myconfig["memory_limit"]);
  } else {
    ini_set ("memory_limit", "1024M");
  }

  if ($myconfig["htmlcompression"]=="yes") {
    // Site to test this: http://leknor.com/code/gziped.php
    $uri = $REQUEST_URI;
    $compress = false;
    global $mode;
    if ($uri=="/") { // homepage
      $compress = true;
    } else if (preg_match ("'^/[^./?]*_(com|nl)/[^./?]*.php'", $uri)) { // CMS web page
      $compress = true;
    } else if (strpos (" ".$uri,"/portal") || strpos (" ".$uri,"/portaal")) {
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
      N_Log ("errors", $subject, $body);
      $reporting_error = false;
    }
  } 

  function N_Die ($message)
  {
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
    if (!$error_handling) return;
    if ($errno==8 && $ims_shownotices!="yes") return;
    if ($errno==8 && !strpos ($filename, "eval()'d")) return; // Only show Notices for none core code

    if ($errno!=2048 && ($errno!=8) && ($errno != 8192) ) {
      N_Debug ("userErrorHandler ($errno, $errmsg, $filename, $linenum, $vars)");
    }
    if (strpos ($errmsg, "annot add header information")) return;
    if (strpos ($errmsg, "nvalid argument supplied for foreach")) return;
    if (!$disabledebugging) {
      if (($ims_showerrors=="yes") && ($errno!=2048) && ($errno != 8192)) {
        echo "<b>$errortype[$errno]: $filename ($linenum): </b> $errmsg<br>";
      }
    } else {
      if (($ims_showerrors=="yes") && ($errno!=2048) && ($errno != 8192)) {
        echo "<b>$errortype[$errno]</b> (see log)<br>";
      }
    }

    if ($errno != E_NOTICE && $errno!=2048 && $errno != 8192) { // Keep notices out of the log for lack of de-duplication
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
    if ($errno!=8) {
      userErrorHandler ($errno, $errmsg, $filename, $linenum, $vars);
    }
  }

  set_error_handler("userErrorHandlerFilter");

  if (function_exists (set_exception_handler)) {
    function userExceptionHandler($exception)
    {
      N_DIE ("FATAL EXCEPTION (PHP5) ".$exception->__toString());
    } 
    set_exception_handler ("userExceptionHandler");
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
  if (is_array ($myconfig["allowdebuggingfrom"])) {
    $disabledebugging = true;
    $myconfig["allowdebuggingfrom"][] = "127.0.0.1";
    $myconfig["allowdebuggingfrom"][] = $_SERVER["SERVER_ADDR"]; 
    foreach ($myconfig["allowdebuggingfrom"] as $cidr) {
      if (N_IPRangeCheck($cidr, $_SERVER["REMOTE_ADDR"])) $disabledebugging = false;
    }
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

  uuse ("dyna");
  uuse ("flex");
  DYNA_ProcessRequest ();

  if ($debug=="EXIT COMPLETED") echo "<br>DYNA Elapsed: ".(int)(1000*(doubleval(preg_replace('!^0\.([0-9]*) ([0-9]*)$!','\\2.\\1',microtime())) - $dynastart))." ms<br>";
  if ($profiling=="yes") N_Exit();
?>