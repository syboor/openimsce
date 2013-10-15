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



//////////////////////
  // NKit::General is a collection of useful functions
  ////////////////////// 

  ignore_user_abort (1); // make sure we will reach N_Exit(); 

  function N_BackTicks ($cmd , $extralogging = "" )
  {
    N_PMLog ("pmlog_backticks", "N_BackTicks", $cmd . " " . $extralogging );
    return `$cmd`;
  }

  function N_unzip( $from_file , $to_dir , $extralogging = "No extra logging provided" )
  {
    global $myconfig;
    $cmd = $myconfig["unzip"] . ' -P dontaskforapassword ' . escapeshellarg(N_ShellPath($from_file)) . " -d " . escapeshellarg(N_ShellPath($to_dir));
    return N_BackTicks( $cmd , $extralogging );
  }

  function N_QLog ($line, $object=null)
  {
    MB_REP_Save ("local_qlog", N_GUID(), array (
      "time" => N_MicroTime(),
      "line" => $line,
      "object" => $object
    ));
    $list = MB_MultiQuery ("local_qlog", array (
      "range" => array ('$record["time"]', 0, time()-7*24*3600)
    ));
    if ($list) {
      foreach ($list as $key) MB_REP_Delete ("local_qlog", $key);
    }
  }

  function N_FetchQLog ($after=0)
  {
    $result = MB_MultiQuery ("local_qlog", array (
      "range" => array ('$record["time"]', $after+0.000001, 9999999999.99),
      "sort" => '-$record["time"]',
      "value" => '$record',
    ));
    return $result;
  }

  function N_Clone ($me) 
  {
    return unserialize (serialize ($me));
  }
 
  function N_Eval ($code_squoink, $inputvars_squoink=array(), $outputvar_squoink=null)
  {
    // Try to allow any variable name in the code.
    extract ($inputvars_squoink, EXTR_SKIP);
    N_PMLog ("pmlog_eval", "N_Eval", $code_squoink);
    $eval_return_value_squoink = eval ($code_squoink);
    if ($eval_return_value_squoink === false && $code_squoink && strpos($code_squoink, 'return') === false) {
      trigger_error("N_Eval detected syntax error in code: \n\n" . htmlspecialchars($code_squoink) . "\n\n", E_USER_WARNING);
    }
    return $$outputvar_squoink;
  }

  function N_GeneratePreEvalCleanupCode ()
  {
    // Created with: echo N_XML2HTML (N_GeneratePostEvalCleanupCode (N_ReadFile ("html::openims/openims.php")));
    return '
      $squoink = $group; unset ($group); $group = $squoink;
      $squoink = $object; unset ($object); $object = $squoink;
      $squoink = $userobj; unset ($userobj); $userobj = $squoink;
      $squoink = $o; unset ($o); $o = $squoink;
      $squoink = $obj; unset ($obj); $obj = $squoink;
      $squoink = $tree; unset ($tree); $tree = $squoink;
      $squoink = $folderobject; unset ($folderobject); $folderobject = $squoink;
      $squoink = $workflow; unset ($workflow); $workflow = $squoink;
      $squoink = $portlet; unset ($portlet); $portlet = $squoink;
      $squoink = $portletdata; unset ($portletdata); $portletdata = $squoink;
      $squoink = $mainportal; unset ($mainportal); $mainportal = $squoink;
      $squoink = $portal; unset ($portal); $portal = $squoink;
      $squoink = $rec; unset ($rec); $rec = $squoink;
      $squoink = $user; unset ($user); $user = $squoink;
      $squoink = $sortparams; unset ($sortparams); $sortparams = $squoink;
      $squoink = $template; unset ($template); $template = $squoink;
      $squoink = $fromtemplate; unset ($fromtemplate); $fromtemplate = $squoink;
      $squoink = $totemplate; unset ($totemplate); $totemplate = $squoink;
      $squoink = $shorturl; unset ($shorturl); $shorturl = $squoink;
      $squoink = $process; unset ($process); $process = $squoink;
      $squoink = $newtemplate; unset ($newtemplate); $newtemplate = $squoink;
      $squoink = $workfrom; unset ($workfrom); $workfrom = $squoink;
      $squoink = $workto; unset ($workto); $workto = $squoink;
      $squoink = $permissions; unset ($permissions); $permissions = $squoink;
      $squoink = $shortcutobject; unset ($shortcutobject); $shortcutobject = $squoink;
      $squoink = $sourceobject; unset ($sourceobject); $sourceobject = $squoink;
      $squoink = $multi; unset ($multi); $multi = $squoink;
    ';
  }

  function N_GeneratePostEvalCleanupCode ($code)
  {    
    if (preg_match_all ('#\$([a-z0-9_]*)[\s]*\=[\s]*\&#i', str_replace (chr(13)," ", (str_replace (chr(10)," ",$code))), $res)) {
      foreach ($res[1] as $dummy => $varname) {
        $list[$varname] = $varname;
      }
      foreach ($list as $varname) {
        $result .= "\$squ_oink87_5634 = \$$varname; unset (\$$varname); \$$varname = \$squ_oink87_5634;\r\n";
      }
    }
    return $result;
 }

  function N_FeedbackTime ($seconds=1)
  {
    global $N_FeedbackTime_last;
    if (!$N_FeedbackTime_last) $N_FeedbackTime_last = N_Elapsed();
    $now = N_Elapsed();
    if ($N_FeedbackTime_last+$seconds < $now) {
      $N_FeedbackTime_last = $now;
      return true;
    } else {
      return false;
    }
  }

/* UTF test script
function dump ($s) { echo "<nobr><b>$s</b></nobr>"."<br>"; for ($i=0; $i<strlen($s); $i++) { $c = ord (substr ($s, $i, 1)); echo "$c: [".chr($c)."]<br>"; } }

$s1 = "hallo".utf8_encode (chr(230).chr(231).chr(232).chr(254)).N_HTML2UTF (" &#32593;&#31449;&#23548;&#33322; &#200; &#300; &#400; &#500; &#600; &#1000; &#1100; &#10000; &#11000; &#100000; &#1000000; &#2000000;");

$s = $s1;

echo "<table style=\"border: 1px\"><tr valign=\"top\"><td>";
  dump ($s);
  echo "</td><td>&nbsp;&nbsp;&nbsp;</td><td>";     
    $s = N_UTF2HTML ($s); dump ($s);
  echo "</td><td>&nbsp;&nbsp;&nbsp;</td><td>";
    $s = N_HTML2UTF ($s); echo "{".($s1==$s)."}"; dump ($s);
echo "</td></tr></table>";
*/

  function N_Code2UTF ($num)
  {
    // Should be: return html_entity_decode('&#'.$num.';',ENT_NOQUOTES,'UTF-8'); but that has bugs in PHP 4.
    if ($num < 128) return chr($num);
    if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
    if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    return '';
  }

  function N_HTML2UTF ($html)
  {
    return preg_replace_callback ('/&#0*([0-9]*);/', create_function('$matches', 'return N_Code2UTF($matches[1]);'), utf8_encode ($html));
  }

  function N_Utf8_decode($source) { // plain utf8_decode, but recursive (both keys and values are decoded
    if (is_string($source)) {
      return utf8_decode($source);
    } elseif (is_array($source) && count($source)) {
      foreach ($source as $key => $value) {
        $newarray[utf8_decode($key)] = N_Utf8_decode($value);
      }
      return $newarray;
    } else {
      return $source;
    }
  }

  function N_UTF2HTML ($source, $trueasciihtml=false)   
  // <form method="GET" action="" accept-charset="utf-8">
  {
    if (is_array($source)) {
      foreach ($source as $key => $value) {
        $newarray[$key] = N_UTF2HTML($value);
      }
      return $newarray;
    }

    $decrement[4] = 240;
    $decrement[3] = 224;
    $decrement[2] = 192;
    $decrement[1] = 0;
    $shift[1][0] = 0;
    $shift[2][0] = 6;
    $shift[2][1] = 0;
    $shift[3][0] = 12;
    $shift[3][1] = 6;
    $shift[3][2] = 0;
    $shift[4][0] = 18;
    $shift[4][1] = 12;
    $shift[4][2] = 6;
    $shift[4][3] = 0;
    $pos = 0;
    $len = strlen ($source);
    $encodedString = '';
    while ($pos < $len) {
       $asciiPos = ord (substr ($source, $pos, 1));
       if (($asciiPos >= 240) && ($asciiPos <= 255)) {
           $thisLetter = substr ($source, $pos, 4);
           $pos += 4;
       }
       else if (($asciiPos >= 224) && ($asciiPos <= 239)) {
           $thisLetter = substr ($source, $pos, 3);
           $pos += 3;
       }
       else if (($asciiPos >= 192) && ($asciiPos <= 223)) {
           $thisLetter = substr ($source, $pos, 2);
           $pos += 2;
       }
       else {
           $thisLetter = substr ($source, $pos, 1);
           $pos += 1;
       }
       $thisLen = strlen ($thisLetter);
       $thisPos = 0;
       $decimalCode = 0;
       while ($thisPos < $thisLen) {
           $thisCharOrd = ord (substr ($thisLetter, $thisPos, 1));
           if ($thisPos == 0) {
               $charNum = intval ($thisCharOrd - $decrement[$thisLen]);
               $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
           }
           else {
               $charNum = intval ($thisCharOrd - 128);
               $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
           }

           $thisPos++;
       }
       if ($thisLen == 1)
           $encodedLetter = "&#". str_pad($decimalCode, 3, "0", STR_PAD_LEFT) . ';';
       else
           $encodedLetter = "&#". str_pad($decimalCode, 5, "0", STR_PAD_LEFT) . ';';
       if ($decimalCode < 128 || (!$trueasciihtml && $decimalCode < 256)) {
         $encodedLetter = chr ($decimalCode);
       }

       $encodedString .= $encodedLetter;
    }
    return $encodedString;
  }

  function N_CHMod ($path)
  {
    // LF20111102: Is chmod'ing  still necessary, now that we call umask() at the start of the request?
    // Even so, $myconfig["chmod"] = 0777 shouldnt be necessary. Permissions for group/other only matter when using 
    // suExec instead of mod_php (scripts versus static files are served by a different user), and even then, I assume 
    // that whatever problem we were trying to fix with chmod was caused by a umask(077) and could be solved 
    // with a umask(022) (chmod 755).
    // Note: removing 0777 from mkdir()-calls is not necessary, since mkdir always substracts the current umask.
    global $myconfig;
    $path = N_CleanPath ($path);
    @chmod ($path, ($myconfig["chmod"] ? $myconfig["chmod"] : 0777));
    //if (N_Windows()) {
    //  `\\cygwin\\bin\\chmod a+rwx $path`;
    //  `c:\\cygwin\\bin\\chmod a+rwx $path`;
    //}
  }

  function N_FileExists ($path) {
    $path = N_CleanPath ($path);
    return file_exists ($path);
  }

  function N_MkDir ($path) 
  {
    N_Debug ("N_MkDir ($path)", "N_MkDir");
    $path = N_CleanPath ($path);
    N_ErrorHandling (false);
    if (!file_exists ($path)) {
      mkdir ($path);
      N_Chmod ($path);
    } 
    N_ErrorHandling (true);    
  }

  function N_MicroTime ()
  {
    global $last_mt, $last_t;
    $mt = doubleval(preg_replace('#^0\.([0-9]*) ([0-9]*)$#','\\2.\\1',microtime()));
    $t = time();
    if ($last_mt) {
      for ($i=0; $i<3; $i++) { // Fix mysterious 512 second offset bug on some systems
        if ((($last_mt - $mt) - ($last_t - $t)) < -500) $mt -= 512;
        if ((($last_mt - $mt) - ($last_t - $t)) > 500) $mt += 512;
      }
    }
    if ($last_mt > $mt) $mt = $last_mt;
    $last_mt = $mt;
    $last_t = $t;
    return $mt;
  }

  function N_MicroTime_old ()
  {
    return doubleval(preg_replace('#^0\.([0-9]*) ([0-9]*)$#','\\2.\\1',microtime()));
  }

  $N_Start = N_MicroTime();
  $N_Last = $N_Start;

  function N_StartTimer ($name)
  {
    global $n_timers;
    $n_timers[$name]["started"] = N_Elapsed();
  }

  function N_StopTimer ($name)
  {
    global $n_timers;
    $n_timers[$name]["total"] += (N_Elapsed()-$n_timers[$name]["started"]);
  }

  function N_TimerTotal ($name)
  {
    global $n_timers;
    return $n_timers[$name]["total"];
  }

  function N_Elapsed ()
  {
    global $N_Start;
    return N_MicroTime() - $N_Start;
  }

  function N_Progress ($message)
  {
    global $progress, $flush, $N_Start, $N_Last;
    if ($progress) {
      if ($progress=="yes") {
        echo "PROGRESS: " . $message . "<br>";
        flush();
      }
    }
  }

  function N_Debug ($message, $profile="") 
  {
    global $debug, $disabledebugging, $debugcommand, $flush, $N_Start, $N_Last, $debuglog, $logs, $myconfig, $lines, $profiling, $profilingdata, $profileme;
    if (!$debug && !$profiling) return;
    if ($disabledebugging) {
      if ($debug) {
        global $disabledebugging_debugerrorshown;
        if (!$disabledebugging_debugerrorshown) echo "DEBUG: NOPE<br>";
        $disabledebugging_debugerrorshown = true;
      }
      return;
    }

    if ($profiling=="yes") { 
      $profilingdata["counters"]["N_Debug"]++;
      if ($profile) {
        $profilingdata["counters"][$profile]++;  
        if ($profileme==$profile) {
          if ($profilingdata["tracecount"] < 100) { 
            if (function_exists ("debug_backtrace")) $profilingdata["trace"][++$profilingdata["tracecount"]]["stack"] = debug_backtrace();
            $profilingdata["trace"][$profilingdata["tracecount"]]["time"] = N_MicroTime();
            $profilingdata["trace"][$profilingdata["tracecount"]]["message"] = $message;
          }
        }
      }
    }

    N_Trace4Errors ($message);
    if ($debug=="yes") {
      $N_Current = N_MicroTime(); 
      if ($debugcommand == "tune") {
        if ((int)(($N_Current-$N_Last) * 1000) == 0) { 
          echo '<color="808080">';
          echo "DEBUG: " . $message . " (Total: " . (int)(($N_Current-$N_Start) * 1000) . "ms, since last:" . (int)(($N_Current-$N_Last) * 1000) . "ms) <br>";
        } else if ((int)(($N_Current-$N_Last) * 1000) > 9) { 
          echo '<color="ff0000">';
          echo "<b>DEBUG: " . $message . " (Total: " . (int)(($N_Current-$N_Start) * 1000) . "ms, since last:" . (int)(($N_Current-$N_Last) * 1000) . "ms)</b> <br>";
        } else { 
          echo '<color="000000">';
          echo "DEBUG: " . $message . " (Total: " . (int)(($N_Current-$N_Start) * 1000) . "ms, since last:<b>" . (int)(($N_Current-$N_Last) * 1000) . "</b>ms) <br>";
        }
        echo '<font>';
      } else {
        echo "DEBUG: " . $message . " (Total: " . (int)(($N_Current-$N_Start) * 1000) . "ms, since last:<b>" . (int)(($N_Current-$N_Last) * 1000) . "</b>ms) <br>";
      }
      if ($flush!="no") N_Flush();
      $N_Last = $N_Current;
    } else if ($debug) {
      // If a message contains the url (because MB_REP_Save added the url to every record passing through it...), mangle the debug-parameter in the url to prevent excessive matching
      $message = str_replace("&debug=$debug", "&debug=...", $message);
      $message = str_replace("?debug=$debug", "?debug=...", $message);
      if (strpos (" ".$message, $debug)) {
        $N_Current = N_MicroTime(); 
        echo "DEBUG: " . $message . " (Total: " . (int)(($N_Current-$N_Start) * 1000) . "ms, since last:<b>" . (int)(($N_Current-$N_Last) * 1000) . "</b>ms) <br>";
        if ($flush!="no") N_FLush();
        $N_Last = $N_Current;
      }
    }
  }

  function N_UseTransferAgent()
  {
    global $myconfig;
    if ($myconfig[IMS_SuperGroupName()]["trans"] == "no" || $_COOKIE['ims_notrans'] == "yes" || N_iOS() || N_Android()) return false; /// 20111003 Android added KVD 

    return true;
  }

  function N_UseAdvancedTransferAgent()
  {
    return (N_UseTransferAgent() && !N_UseJavaTransferAgent());
  }

  function N_UseJavaTransferAgent()
  {
    global $myconfig;
    if ( (N_Macintosh()) || 
         ($myconfig[IMS_SuperGroupName()]["javatrans"] == "yes") ||
         (N_Linux())
       ) {
      $ret = true;
    } else {
      $ret = false;
    }
    return $ret;
  }

  function N_Linux() {
    global $HTTP_USER_AGENT;
    // Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.7.8) Gecko/20050511 Firefox/1.0.4 SUSE/1.0.4-0.3
    // Mozilla/5.0 (compatible; Konqueror/3.2; Linux) (KHTML, like Gecko)
    $ret = false;
    if (strpos ($HTTP_USER_AGENT, "Linux")) {
      $ret = true;
    }
    return $ret;
  }

  function N_Macintosh()
  {
    global $HTTP_USER_AGENT;
    // Safari:
    // Mozilla/5.0 (Macintosh; U; PPC Mac OS X; nl-nl) AppleWebKit/312.1 (KHTML, like Gecko) Safari/312
    // Firefox:
    // Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; nl-NL; rv:1.7.7) Gecko/20050414 Firefox/1.0.3
    // ie: .... Mac_PowerPC
    $ret = false;
    if (strpos ($HTTP_USER_AGENT, "Macintosh")) {
      $ret = true;
    } else {
      if (strpos ($HTTP_USER_AGENT, "Mac_PowerPC")) {
        $ret = true;
      }
    }
    return $ret;
  }

  function N_IEQuirks ()
  {
    global $IMS_HasHtmlDoctype, $HTTP_USER_AGENT;
    if ($IMS_HasHtmlDoctype) {
      return (strpos ($HTTP_USER_AGENT, "MSIE 5") || strpos ($HTTP_USER_AGENT, "MSIE 4"));
    } else {
      return N_IE();
    }
  }

  function N_IE ()
  {
    global $HTTP_USER_AGENT;
    // Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)
    // Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)
    return (strpos ($HTTP_USER_AGENT, "MSIE") && !strpos ($HTTP_USER_AGENT, "Opera"));
  }

  function N_Mozilla ()
  {
    global $HTTP_USER_AGENT;
    // Firefox:    Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7) Gecko/20040707 Firefox/0.9.2 
    // Mozilla:    Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.1) Gecko/20040707
    // Netscape 8: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20050512 Netscape/8.0
    return (strpos ($HTTP_USER_AGENT, "Gecko/"));
  }

  function N_Opera ()
  {
    global $HTTP_USER_AGENT;
    // Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1) Opera 7.51 [en]
    // Mozilla/5.0 (Windows NT 5.1; U; en) Opera 8.51
    // Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; en) Opera 8.51
    // Opera/8.51 (Windows NT 5.1; U; en)
    return (strpos (" " . $HTTP_USER_AGENT, "Opera"));
  }

  function N_Safari()
  {
    global $HTTP_USER_AGENT;
    // Mozilla/5.0 (Macintosh; U; PPC Mac OS X; nl-nl) AppleWebKit/312.1 (KHTML, like Gecko) Safari/312
    return (strpos ($HTTP_USER_AGENT, "Safari"));
  }

  function N_Konqueror()
  {
    global $HTTP_USER_AGENT;
    // Mozilla/5.0 (compatible; Konqueror/3.4; Linux) KHTML/3.4.0 (like Gecko)
    return (strpos ($HTTP_USER_AGENT, "Konqueror"));
  }

//20110727 KvD added  
  function N_iOS()
  {
    $useragent = $_SERVER["HTTP_USER_AGENT"];
    return (strpos ($useragent, "iPad") || strpos ($useragent, "iPhone"));
  }

//20111003 KvD added  
  function N_Android()
  {
    $useragent = $_SERVER["HTTP_USER_AGENT"];
    return (strpos ($useragent, "Android") || strpos ($useragent, "Nexus"));
  }

  function N_EOL() {
    // returns the system dependent CR or CRLF or LF
    if (N_Macintosh()) {
      $ret = chr(13);
    } else {
      $ret = chr(13).chr(10);
    }
    return $ret;
  }

  function N_AbsoluteUrl($url)
  {
    // Make url absolute (needed when using an url in http headers or meta-refresh headers)
    // Use only for for real url's, not for javacript or "closeme" or other stuff.
    ereg ("(https{0,1}://)?(.*)", $url, $ereg);    
    if (!$ereg[1]) {
      if (substr($url, 0, 1) == "/") {
        $url = N_CurrentProtocol() . $_SERVER["HTTP_HOST"] . str_replace ("//", "/", "/$url");
      } elseif (substr($url, 0, 1) == "?") {
        // Handle url's starting with ? the same way the browser would do it (different from what Redirect1 used to do).
        // This code:  (/private/eval.php) ?arg1=1 -> http::/dev.openims.com/private/eval.php?arg1=1
        // Redirect1:  (/private/eval.php) ?arg1=1 -> http::/dev.openims.com/?arg1=1
        // I can not imagine any callers relying on Redirect1's behaviour.
        $url = N_MyBareUrl() . $url;
      } elseif (strpos ($url, ".com") || strpos ($url, ".net") || strpos ($url, ".nl")) {
        // (/private/eval.php) www.google.com -> http::/www.google.com
        // Different from browser behaviour, same as Redirect1 (but rather inconsistent across TLD's)
        $url = N_CurrentProtocol().str_replace ("//", "/", "$url");
      } else {
        // $currenturl = N_MyBareURL () ;
        // $scriptname = N_KeepAfter($currenturl, "/", 1);
        // $url = substr($currenturl, 0, strlen($currenturl)-strlen($scriptname)) . $url;
        $url = N_CurrentProtocol() . $_SERVER["HTTP_HOST"] . str_replace ("//", "/", "/$url");
      }
    }
    return $url;
  }

  function N_Redirect1_Header ($url, $httpstatuscode = false)
  {
    // Do a http header redirect. Returns true if it actually did a redirect.
    $origurl = $url; // debugging

    if (!$httpstatuscode) return false; // Dont do a header redirect if no header was given
    if ($httpstatuscode == 200) return false;

    // Check if real url (closeme, nowhere, imstoolkit, javascript)
    if (substr($url, 0, 7) == "closeme" || $url == "nowhere" || strtolower(substr($url, 0, 10)) == "javascript" || strpos($url, "imstoolkit") !== false) return false;

    if ($_COOKIE['ims_noredirect'] == "yes") return false;

    if (headers_sent()) { // Too late
      N_Debug("N_Redirect0($url, $httpstatuscode): no header redirect because headers already sent");
      return false; 
    }

    $url = N_AbsoluteUrl($url);

    // If statuscode = 3xx, set location header
    if (substr($httpstatuscode, 0, 1) == "3") {
      header ("Location: $url", true, $httpstatuscode);
      return true; // We actually did the redirect (skip N_Redirect1 and go directly to N_Redirect2 for exit stuff)
    } elseif ($httpstatuscode == "404") {
      global $keep404error;
      $keep404error = true;
      Header ("HTTP/1.1 404"); // Needed to make link checker understand what it should do
      Header ("Status: 404");  // fastcgi
      return false; // Use N_Redirect1 to do the actual redirect
    } else {
      trigger_error("N_Redirect0: Unfamiliar header type: $httpstatuscode $url", E_USER_WARNING);
      return false;
    }
  }

  function N_Redirect1 ($url, $httpstatuscode=false)
  {
    MB_Flush();
    MB_Flush();
    uuse ("shield");
    SHIELD_FlushEncoded();
    global $redirectonce;
    if ($redirectonce) return;
    $redirectonce = true;
    if (!N_Redirect1_Header ($url, $httpstatuscode)) { // Try a http header redirect
      N_Redirect1_Body ($url, $httpstatuscode); // If header redirect did not indicate succes, do a body (javascript + meta refresh) redirect. Pass on $httpstatuscode for debugging when "no_redirect" cookie is used
    }
  }

  function N_Redirect1_Body ($url, $httpstatuscode)
  {
    uuse("dhtml");

    // no redirect on: show hyperlink, return here (continue with N_Redirect2 for exit stuff)
    if ($_COOKIE['ims_noredirect'] == "yes") {
      if (substr($url, 0, 7) == "closeme" || $url == "nowhere") {
        echo "<body><p>N_Redirect: $httpstatuscode $url</p>";
      } else {
        echo "<body><p>N_Redirect: $httpstatuscode <a href=\"{$url}\">" . N_HtmlEntities($url) . "</a></p>";
      }
      return;
    }

    // Should match output of DHTML_PopupURL
    if (strpos ($url, "avascript:function function")) {      
      $url = N_RegExp ($url, "URL = ['](.*)['];var cmd");
    }
    N_ErrorHandling (false);
    global $keep404error;
    if (!$keep404error) {
      Header ("HTTP/1.1 200");
      Header ("Status: 200"); // fastcgi
    }
    N_ErrorHandling (true);

    echo ("<META http-equiv=\"Pragma\" content=\"no-cache\">");
    echo str_repeat (" ", 5000); // Make this work in IE with a 404 header
    N_FLush();
    if ($url=="closeme") {
      echo ("<SCRIPT LANGUAGE=\"JavaScript\">window.close();</SCRIPT>");
      N_FLush();
    } if ($url=="closeme&refreshparent") { 
      echo ("<SCRIPT LANGUAGE=\"JavaScript\">function handleError() {return true;} window.onerror = handleError;</SCRIPT>");
      echo ("<SCRIPT LANGUAGE=\"JavaScript\">
              var url;
              url = window.opener.location.href+'#';
              pos = url.indexOf('#',url);
              url = url.substring(0,pos);
              window.opener.location.href = url;
            </SCRIPT>");
      echo ("<SCRIPT LANGUAGE=\"JavaScript\">window.close();</SCRIPT>");
    } if (substr($url, 0, 26) =="closeme&parentgoto:mailto:") {
      $url = substr ($url, 19);
      echo ("<SCRIPT LANGUAGE=\"JavaScript\">function handleError() {return true;} window.onerror = handleError;</SCRIPT>");

// 20120727 KvD OSICT-57 workaround Safari iOS bug
      if (N_iOS() && N_Safari()) {
        echo ("<SCRIPT LANGUAGE=\"JavaScript\">window.name=\"$wname\"; var ww=window; window.opener.location.href = '" . DHTML_EncodeJsString($url) . "';</SCRIPT>");
        echo ("<SCRIPT LANGUAGE=\"JavaScript\">ww.close();</SCRIPT>");
      } else {
///          
        echo ("<SCRIPT LANGUAGE=\"JavaScript\">window.opener.location.href = '" . DHTML_EncodeJsString($url) . "';</SCRIPT>");
        echo ("<SCRIPT LANGUAGE=\"JavaScript\">window.close();</SCRIPT>");
      }
    } if (substr($url, 0, 19) =="closeme&parentgoto:") { 
      $url = substr($url, 19);
      $url = N_AbsoluteUrl($url);
      echo ("<SCRIPT LANGUAGE=\"JavaScript\">function handleError() {return true;} window.onerror = handleError;</SCRIPT>");
// 20120727 KvD OSICT-57 workaround Safari iOS bug
      if (N_iOS() && N_Safari()) {
        echo ("<SCRIPT LANGUAGE=\"JavaScript\">window.name=\"$wname\"; var ww=window; window.opener.location.href = '" . DHTML_EncodeJsString($url) . "';</SCRIPT>");
        echo ("<SCRIPT LANGUAGE=\"JavaScript\">ww.close();</SCRIPT>");
      } else {
///          
        echo ("<SCRIPT LANGUAGE=\"JavaScript\">window.opener.location.href = '" . DHTML_EncodeJsString($url) . "';</SCRIPT>");
        echo ("<SCRIPT LANGUAGE=\"JavaScript\">window.close();</SCRIPT>");
      }
    } if ($url=="nowhere") { 
      return; // continue execution
    } else if (strpos ($url, "imstoolkit")) {
      uuse ("dhtml");
      echo DHTML_EmbedJavascript (DHTML_OpenNow ($url));
      N_Flush();
      echo ("<SCRIPT LANGUAGE=\"JavaScript\">window.close();</SCRIPT>");
    } else {
      $url = N_AbsoluteUrl($url);
      echo "<script language=\"javascript\">window.location='" . DHTML_EncodeJsString($url) . "'</script>";
      N_Flush();
      echo ('<meta http-equiv="refresh" content="0; url=' . N_HtmlEntities($url) . '">');
      N_Flush();
    }
  }

  function N_Redirect2 ($url)
  {
    if (strpos ($url, "avascript:function function")) {
      $url = N_RegExp ($url, "URL = ['](.*)['];var cmd");
    }
    MB_Flush();
    if ($url != "nowhere") {
      // Prevent people from reading this error in the log: IMS_CaptureHtmlHeaders() called and N_Exit() was reached without merging captured header
      // (and prevent people from tampering with calls to IMS_CaptureHtmlHeaders as a result).
      // Use the globals instead of IMS_MergeHtmlHeaders, because if we used IMS_MergeHtmlHeaders, we might have to do it more than once.
      global $IMS_CapturedHtmlHeaders, $IMS_CanCaptureHtmlHeaders;
      $IMS_CapturedHtmlHeaders = array();
      $IMS_CanCaptureHtmlHeaders = false;
    }
    N_Exit();

    if ($url=="closeme") {
      die("");
    } if ($url=="closeme&refreshparent") { 
      if (!getenv("REDIRECT_ERROR_NOTES")) die("");
    } if (substr($url, 0, 19) =="closeme&parentgoto:") { 
      die("");      
    } if ($url=="nowhere") { 
      return; // continue execution
    } else {
      die("");
    }
  }

  function N_Redirect($url, $httpstatuscode = false)
  {
    if ($url) {
      N_Redirect1 ($url, $httpstatuscode);
      N_Redirect2 ($url); // Exit stuff
    }
  }

  function N_CheckPassword ($dir, $id, $password)
  {
    $file = N_ReadFile (N_CleanPath ("html::/" . $dir . "/.htpasswd"));
    if (eregi ("\n" . preg_replace ("/(.)/", "[\\1]", $id) . ":(.{13})", "\n" . $file, $ereg)) {
      if (crypt ($password, $ereg[1]) == $ereg[1]) return -1;
    }
    return 0;
  }

  function N_SortByRev ($array, $expression)
  {
    if (!is_array($array)) return $array;
    if (count($array)==0) return $array;
    foreach ($array as $key => $value) {
      $record = $value;
      $object = $value;
      eval ("\$sortvalue = $expression;");
      $tmp [$key] = $sortvalue;
    }
    arsort ($tmp);
    foreach ($tmp as $key => $dummy) {
      $result [$key] = $array[$key];
    }
    return $result;
  }

  function N_SortBy ($array, $expression)
  {
    if (!is_array($array)) return $array;
    if (count($array)==0) return $array;
    foreach ($array as $key => $value) {
      $record = $value;
      $object = $value;
      eval ("\$sortvalue = $expression;");
      $tmp [$key] = $sortvalue;
    }
    asort ($tmp);
    foreach ($tmp as $key => $dummy) {
      $result [$key] = $array[$key];
    }
    return $result;
  }

  function N_BubbleSort ($elements, $initcode, $swapwhen, $swapcode)
  {
    N_Debug ("N_BubbleSort (...)", "N_BubbleSort");
    eval ($initcode);
    $done = 0;
    while (!$done) {
      $done = 1;
      for ($i=0; $i<$elements-1; $i++)
      {
        $a = $i;
        $b = $i+1;
        eval ('$swap = ' . $swapwhen . ";");
        if ($swap) {
          eval ($swapcode);
          $done = 0;
        }
      }
    }
  }

  function N_Regexp_Expand ($string, $patern)
  { 
    $dfc = DFC_Key ($string, $patern);
    if (DFC_Exists ($dfc)) {
      return DFC_Read ($dfc);
    } else {
      $result="";
      N_ErrorHandling (false);
      for ($i=1; $i<1000; $i++) {
        if (eregi ("(".$patern.")", $string, $ereg)){
          eregi ($patern, $string, $ereg2);
          for ($j=1; $j<100; $j++) {
            $result[$i][$j] = "" . $ereg2[$j];
          }
          $string = N_KeepAfter ($string, $ereg[1]);
        } else {
          $i = 9999;
        }
      }
      N_ErrorHandling (true);
      DFC_Write ($dfc, $result);
      return $result;
    }
  }

  function N_Regexp ($string, $patern, $index=1, $totalindex=1)
  {   
    N_Debug ("N_Regexp (...)", "N_Regexp");
    if ($totalindex==1) { 
//      $dfc = DFC_Key ($string, $patern, "1");
//      if (DFC_Exists ($dfc)) {
//        $result = DFC_Read ($dfc, "1");
//      } else {
        eregi ($patern, $string, $ereg2);
        $result[1] = $ereg2;
//        DFC_Write ($dfc, $result, "1");
//      }      
    } else {
      $result = N_Regexp_Expand ($string, $patern);
    }
    return $result[$totalindex][$index];
  }

  global $VBrowser4GetPage;
  $VBrowser4GetPage = New N_VBrowser();


  function N_GetPage ($url, $useragent = "")
  {
    if ($url == "") return "";

    global $VBrowser4GetPage;

    $method="vbrowser";

    $url = str_replace (chr(10),"",$url);
    $url = str_replace (chr(13),"",$url);

    N_Debug ("N_GetPage ($url)");

    if ($method=="fopen") {

      ereg ("(http://)?(.*)", $url, $ereg);
      $url = "http://".$ereg[2];
      N_ErrorHandling (false);
      $fd = fopen($url, 'r');
      N_ErrorHandling (true);
      $content = "";
      if ($fd) {
        $len = 1;
        while (!feof($fd) AND $len) {
          $read = fread ($fd, 1000000);
          $content .= $read;
          $len = strlen ($read);
        }
        fclose($fd);
      }

    } else if ($method=="webfetch") {

      $exec = N_CleanPath ("html::"."/webfetch")." ";
      $cmd = $exec . escapeshellarg($url);
      $content = `$cmd`;

    } else if ($method=="vbrowser") {

      ereg ("(http://)?(.*)", $url, $ereg);
      $url = $ereg[2];

      if (strpos ($url, "/")) {
        $site = N_KeepBefore ($url, "/");
        $path = N_KeepAfter ($url, "/");
      } else {
        $site = $url;
        $path = "";
      }

      $elems = explode ('\?', $path);
      $path = "/".$elems [0];

      $params="";

      if ($elems [1]) {
        $a = explode('&', $elems [1]);
        $i = 0;
        while ($i < count($a)) {
            $b = explode('=', $a[$i]);


            $params[htmlspecialchars(urldecode($b[0]))] = htmlspecialchars(urldecode($b[1]));
            $i++;
        }
      }

      N_Debug ("SITE = " . $site);
      N_Debug ("PATH = " . $path);
      N_Debug (serialize ($params));

      $VBrowser4GetPage->setSite ($site);
      if ("".$useragent!="") {  //gv 07-10-2011 BIBL-32 RSSfeed server need useragent
        $VBrowser4GetPage->setUserAgent($useragent);
      }
      $content = $VBrowser4GetPage->GET ($path, $params);

    }
    return $content;
  }

  function N_GetPageSize ($url)
  {
    global $VBrowser4GetPage;

    $method="vbrowser";

    N_Debug ("N_GetPage ($url)");

    ereg ("(http://)?(.*)", $url, $ereg);
    $url = $ereg[2];

    if (strpos ($url, "/")) {
      $site = N_KeepBefore ($url, "/");
      $path = N_KeepAfter ($url, "/");
    } else {
      $site = $url;
      $path = "";
    }

    $elems = explode ('\?', $path);
    $path = "/".$elems [0];

    $params="";

    if ($elems [1]) {
      $a = explode('&', $elems [1]);
      $i = 0;
      while ($i < count($a)) {
          $b = explode('=', $a[$i]);
          $params[htmlspecialchars(urldecode($b[0]))] = htmlspecialchars(urldecode($b[1]));
          $i++;
      }
    }

    $VBrowser4GetPage->setSite ($site);
    $size = $VBrowser4GetPage->GetSize ($path, $params);
    if (!$size) {
      $data = N_GetPage ($url);
      $size = strlen ($data);
    }
    return $size;
  }

  function N_GetPageURL ($url)
  {
    $getpage = DFC_Key ("GetPageURL".$url);
    if (DFC_Exists ($getpage)) {
      return DFC_Read ($getpage);
    }

    global $VBrowser4GetPage;

    $method="vbrowser";

    N_Debug ("N_GetPageURL ($url)");

    ereg ("(http://)?(.*)", $url, $ereg);
    $url = $ereg[2];

    if (strpos ($url, "/")) {
      $site = N_KeepBefore ($url, "/");
      $path = N_KeepAfter ($url, "/");
    } else {
      $site = $url;
      $path = "";
    }

    $elems = explode ('\?', $path);
    $path = "/".$elems [0];

    $params="";

    if ($elems [1]) {
      $a = explode('&', $elems [1]);
      $i = 0;
      while ($i < count($a)) {
          $b = explode('=', $a[$i]);
          $params[htmlspecialchars(urldecode($b[0]))] = htmlspecialchars(urldecode($b[1]));
          $i++;
      }
    }

    $VBrowser4GetPage->setSite ($site);
    $baseurl = "http://".$url;
    $url = $VBrowser4GetPage->GetURL ($path, $params);
    if (!$url) $url = $baseurl;
    DFC_Write ($getpage, $url, 7*24); // cache 7*24 hours
    return $url;
  }

  function N_FastGetPage ($url) 
  {
    $getpage = DFC_Key ("GetPage_v3".$url);
    if (DFC_Exists ($getpage)) {
      return DFC_Read ($getpage);
    }
    $content = N_GetPage ($url);
    DFC_Write ($getpage, $content, 24); // cache 24 hours
    return $content;
  }

  function N_KeepAfter ($body, $marker, $last=false)
  {
    if ("".$marker=="") return $body;
    if ($last) {
      if (strrpos ($body, $marker)===false) {
        return "";
      } else {
        return substr ($body, strrpos ($body, $marker) + strlen ($marker));
      }
    } else {
      if (strpos ($body, $marker)===false) {
        return "";
      } else {
        return substr ($body, strpos ($body, $marker) + strlen ($marker));
      }
    }
  }

  function N_KeepBefore ($body, $marker)
  {
    if ("".$marker=="") return $body;
    if (strpos ($body, $marker)===false) {
      return "";
    } else {
      return substr ($body, 0, strpos ($body, $marker));
    }
  }

  function N_DiskSpace ()
  {
    global $busy_diskspace;
    if (!$busy_diskspace) {
      $busy_diskspace = true;
      N_Log ("diskspace", "Start");
      $result=diskfreespace(N_CleanPath ("html::/"));
      N_Log ("diskspace", "End $result");
      $busy_diskspace = false;
    }
    return $result; 
  }

  function N_BigNumber ($me, $before=0)
  {
    return number_format ($me, 0);
    if ($me < 1000) {
      if ($before) {
        if ($me<10) {
          return "00" . $me;
        } else if ($me<100) {
          return "0" . $me;
        } else {
          return "" . $me;
        }
      }
      return "" . $me;
    } else {
      return N_BigNumber ((int)($me/1000)) . "," . N_BigNumber ($me%1000, 1);
    }
  }

// ############

  function N_SendMail ($from, $to, $subject, $body="", $attachmentname="", $attachmentcontent="", $convertascii=false, $additionalheaders="")
  {
    global $myconfig;

    // 20100712 KvD Extra logging
    // 2012-05-19 DD Added log tekst ["nosendmail"]="yes" in 'sendmail' only if this setting is in the myconfig.php
    N_Log("sendmail","N_Sendmail: " . ($myconfig["nosendmail"]=="yes"?"\$myconfig[\"nosendmail\"]=\"yes\" ":"") . " from=[$from], to=[$to], subject=[$subject], attachmentname=[$attachmentname], additionalheaders=[$additionalheaders]", $body);

    // DVG: "$additional" headers toegevoegd om bijvoorbeeld CC mails en/of bcc mails mogelijk te maken
    // Dit kan bijvoorbeeld als volgt gevuld worden voor een cc mail: "Cc: dimitri@osict.nl\n"
    N_Debug ("N_SendMail ($from, $to, $subject, ...");

    if (function_exists('FORMS_ML_Filter')) { // no uuse("forms")
      $subject = FORMS_ML_Filter($subject);
      $body = FORMS_ML_Filter($body);
    }

    if ($myconfig["nosendmail"]=="yes") return; // for demo laptops

    if (!$from) $from=$to;
// DvG / KvD 20100419 ENTREA-67 Niet meerdere email adressen in 'from'
    if (strpos($from, ",") !== false) $from=N_KeepBefore($to.",", ",");
///
    if (!$from) $from="mailer@osict.com";

    if ($convertascii) {
       if (is_array($attachmentcontent)) {
          for ($i=0; $i<count($attachmentcontent); $i++) {
            $attachmentcontent[$i] = str_replace (chr(10), chr(13).chr(10), $attachmentcontent[$i]);
          }
       } else {
          $attachmentcontent= str_replace (chr(10), chr(13).chr(10), $attachmentcontent);
       }
    }
    
    $convertascii = false;

    if ($myconfig["localsendmail"]!="yes") {
      return URPC ($myconfig["usetosendmail"], 'N_SendMail ($input["from"], $input["to"], $input["subject"], $input["body"], 
                                           $input["attachmentname"], $input["attachmentcontent"], $input["convertascii"],
                                           $input["additionalheaders"]);',
                   array("from"=>$from, "to"=>$to, "subject"=>$subject, "body"=>$body, 
                         "attachmentname"=>$attachmentname, "attachmentcontent"=>$attachmentcontent,  "convertascii"=>$convertascii,
                         "additionalheaders"=>$additionalheaders));
    }

    $mailheaders  = "From: $from\n";
    $mailheaders .= "Reply-To: $from\n";
    $mailheaders .= $additionalheaders;
    $msg_body = stripslashes($body);

    if (is_array($attachmentname)) {

       for ($i=0; $i<count($attachmentname); $i++) {
          if ($i==0) {
             $mailheaders .= "MIME-version: 1.0\n";
             $mailheaders .= "Content-type: multipart/mixed; ";
             $mailheaders .= "boundary=\"Message-Boundary\"\n";
             $mailheaders .= "Content-transfer-encoding: 7BIT\n";
             
             $mailheaders .= "X-attachments: ";
             for ($j=0; $j<count($attachmentname); $j++) {   
                $mailheaders .= $attachmentname[$j];
                if ($j < count($attachmentname)-1) $mailheaders .= ",";
             }

             $body_top = "--Message-Boundary\n";
             $body_top .= "Content-type: text/plain; charset=US-ASCII\n";
             $body_top .= "Content-transfer-encoding: 7BIT\n";
             $body_top .= "Content-description: Mail message body\n\n";

             $msg_body = $body_top  . $msg_body ;
          }
          $attach_size = strlen ($attachmentcontent[$i]);
          $encoded_attach = chunk_split(base64_encode($attachmentcontent[$i]));
          $msg_body .= "\n\n--Message-Boundary\n";
          $msg_body .= "Content-type: ".N_GetMimeType($attachmentname[$i])."; name=\"".$attachmentname[$i]."\"\n";
          $msg_body .= "Content-Transfer-Encoding: BASE64\n";
          $msg_body .= "Content-disposition: attachment; filename=\"".$attachmentname[$i]."\"\n\n";
          $msg_body .= "$encoded_attach\n";
       }
 
       if (count($attachmentname) > 0) {
          $msg_body .= "--Message-Boundary--\n";
       }

    } else {

       if ($attachmentname != "")
       {
          $attach_size = strlen ($atachmentcontent);
          $encoded_attach = chunk_split(base64_encode($attachmentcontent));

          $mailheaders .= "MIME-version: 1.0\n";
          $mailheaders .= "Content-type: multipart/mixed; ";
          $mailheaders .= "boundary=\"Message-Boundary\"\n";
          $mailheaders .= "Content-transfer-encoding: 7BIT\n";
          $mailheaders .= "X-attachments: $attachmentname";

          $body_top = "--Message-Boundary\n";
          $body_top .= "Content-type: text/plain; charset=US-ASCII\n";
          $body_top .= "Content-transfer-encoding: 7BIT\n";
          $body_top .= "Content-description: Mail message body\n\n";

          $msg_body = $body_top . $msg_body;

          $msg_body .= "\n\n--Message-Boundary\n";
          $msg_body .= "Content-type: text; name=\"$attachmentname\"\n";
          $msg_body .= "Content-Transfer-Encoding: BASE64\n";
          $msg_body .= "Content-disposition: attachment; filename=\"$attachmentname\"\n\n";
          $msg_body .= "$encoded_attach\n";
          $msg_body .= "--Message-Boundary--\n";
       }
    }

      //20100712 KvD : Log what you pass to mail() and return return values      
      N_Log("sendmail","N_Sendmail calls mail(): from=[$from], to=[$to], subject=[$subject], mailheaders=[$mailheaders]", $msg_body);
      if($myconfig["mailreturnpath"]) { 
        return mail($to, stripslashes($subject), $msg_body, $mailheaders, "-f".$myconfig["mailreturnpath"]);
      } else {
        return mail($to, stripslashes($subject), $msg_body, $mailheaders);
      }

//    }
  }

  function N_Mail ($from, $to, $subject, $body="", $realfilename="", $visiblefilename="") // obsolete
  {
// 20100712 KvD Extra logging
    N_Log("sendmail","N_Mail: from=[$from], to=[$to], subject=[$subject], realfilename=[$realfilename], visiblefilename=[$visiblefilename]", $body);

    if ($realfilename) {
      $content = N_ReadFile ("html::$visiblefilename");
      return N_SendMail ($from, $to, $subject, $body, $content, $visiblefilename);
    } else {
      return N_SendMail ($from, $to, $subject, $body);
    }
  }

  function J_SendMail ($from, $to, $subject, $body="", $attachmentname="", $attachmentcontent="", $convertascii=false, $headers="")
  {
    N_Debug ("J_SendMail ($from, $to, $subject, ...");

    global $myconfig;

    if ($myconfig["nosendmail"]=="yes") return; // for demo laptops

    if (!$from) $from=$to;
    if (!$from) $from="mailer@osict.com";

    if ($convertascii) {
       if (is_array($attachmentcontent)) {
          for ($i=0; $i<count($attachmentcontent); $i++) {
            $attachmentcontent[$i] = str_replace (chr(10), chr(13).chr(10), $attachmentcontent[$i]);
          }
       } else {
          $attachmentcontent= str_replace (chr(10), chr(13).chr(10), $attachmentcontent);
       }
    }
    
    $convertascii = false;
    if ($myconfig["localsendmail"]!="yes") {
      return URPC ($myconfig["usetosendmail"], 'J_SendMail ($input["from"], $input["to"], $input["subject"], $input["body"], 
                                           $input["attachmentname"], $input["attachmentcontent"], $input["convertascii"],
                                           $input["headers"]);',
                   array("from"=>$from, "to"=>$to, "subject"=>$subject, "body"=>$body, 
                         "attachmentname"=>$attachmentname, "attachmentcontent"=>$attachmentcontent, "convertascii"=>$convertascii,
                         "headers"=>$headers));
    }

    $body = stripslashes($body);
  
    mail($to, stripslashes($subject), $body, $headers);
  }

  function N_BeginningOfHour ($time)
  {
    $date = adodb_getdate($time);
    return $time - $date[minutes]*60 - $date[seconds] + 1;
  }

  function N_BeginningOfDay ($time)
  {
    $date = adodb_getdate($time);
    return $time - $date[hours]*3600 - $date[minutes]*60 - $date[seconds] + 1;
  }

  function N_BeginningOfWeek ($time)
  {
    $date = adodb_getdate($time);
    return $time - $date[wday] * 3600 * 24 - $date[hours]*3600 - $date[minutes]*60 - $date[seconds] + 1;
  }

  function N_AbsoluteHour ($time)
  {
    return (int)((N_BeginningOfHour($time) - 33519601) / 3600);
  }

  function N_AbsoluteDay ($time)
  {
    return (int)((N_BeginningOfDay($time) - 33519601) / (24*3600));
  }

  function N_AbsoluteWeek ($time)
  {
    return (int)((N_BeginningOfWeek($time) - 33519601) / (7*24*3600));
  }

  function N_AbsoluteMonth ($time)
  {
    $date = adodb_getdate($time);
    return $date[year]*12 + $date[mon];
  }

  function N_DateDiffDays ($old, $new)
  {
    return (int)(N_AbsoluteDay ($new) - N_AbsoluteDay ($old) + 0.5);
  }

  function N_DateDiffWeeks ($old, $new)
  {
    return (int)(N_AbsoluteWeek ($new) - N_AbsoluteWeek ($old) + 0.5);
  }

  function N_DateDiffMonths ($old, $new)
  {
    return (int)(N_AbsoluteMonth ($new) - N_AbsoluteMonth ($old) + 0.5);
  }


  function N_Sleep ($milliseconds) {
    global $myconfig;
    if (function_exists ("usleep")) { // Linux PHP all version or Windows with PHP 5+
      usleep ($milliseconds*1000);
    } else {
      $start = N_MicroTime();
      sleep ((int)($milliseconds/1000));
      while (N_MicroTime() - $start < $milliseconds/1000.0);
    }
  }

/*** Path substition and multi-tier caching logic. ***

EXAMPLE CONFIG:
  $myconfig["pathsubstitution"]["/openims/demo_sites"] = "/ims_demo_sites";
  $myconfig["mtcache"] = "/tmp/mtcache";

TEST CODE #1:
  $path = "html::/demo_com/some/subdir/test.txt";
  echo "\$path: $path<br>";
  echo "N_CleanPath: ".N_CleanPath ($path)."<br>";
  echo "N_VirtualCleanPath: ".N_CleanPath ($path)."<br>";
  echo "N_MTCacheActive: [".N_MTCacheActive ($path)."]<br>";
  echo "N_MTCacheInfo: "; N_EO (N_MTCacheInfo($path));
  $path = "html::/demo_sites/some/other/subdir/test.txt";
  echo "\$path: $path<br>";
  echo "N_CleanPath: ".N_CleanPath ($path)."<br>";
  echo "N_VirtualCleanPath: ".N_CleanPath ($path)."<br>";
  echo "N_MTCacheActive: [".N_MTCacheActive ($path)."]<br>";
  echo "N_MTCacheInfo: "; N_EO (N_MTCacheInfo($path));

TEST CODE #2:
  $path = "html::/test_sites/testdir/testfile.txt";
  $path2 = "html::/test_sites/testdir/testfile2.txt";
  echo ($guid = N_Guid())."<br>";
  N_WriteFile ($path, "Test 1 2 3 $guid<br>");
  echo N_ReadFile ($path);
  echo N_ReadFilePart  ($path, 5, 1)."<br>";
  N_WriteFilePart ($path, 5, "X");
  echo N_ReadFilePart  ($path, 5, 1)."<br>";
  N_WriteFile ($path, "Test 1 2 3 $guid plus<br>");
  echo N_ReadFilePart  ($path, 5, 1)."<br>";
  N_WriteFilePart ($path, 7, "Y");
  N_WriteFilePart ($path, 9, "Z");
  echo N_ReadFile ($path);
  N_AppendFile ($path, "Test 4 5 6<br>");
  echo N_ReadFile ($path);
  N_CopyFile ($path2, $path);
  echo N_ReadFile ($path2);

*/

  function N_MTCacheActive ($path) 
  {
    global $myconfig;
    if ($myconfig["pathsubstitution"] && $myconfig["mtcache"]) {
      return N_CleanPath ($path) <> N_VirtualCleanPath ($path);
    }
  }

  function N_MTCacheInfo ($path)
  {
    global $myconfig;
    if (N_MTCacheActive ($path)) {      
      // truepath contains all files, but some of those might be incomplete (scheduled updates should be present)
      // cachepath contains only complete files but does not contain all files 
      $rec["truepath"] = N_CleanPath ($path);
      $rec["virtualpath"] = N_VirtualCleanPath ($path);
      $rec["cachepath"] = N_VirtualCleanPath ($path);
      global $myconfig;
      foreach ($myconfig["pathsubstitution"] as $old => $new) {
        if (substr ($rec["cachepath"], 0, strlen($old))==$old) {
          $rec["cachepath"] = $myconfig["mtcache"]."/".md5($new).substr ($rec["cachepath"], strlen($old));
        }
      }
      return $rec;
    } else {
      return null;
    }
  }

  function N_CLeanRoot()
  {
    $root = getenv("DOCUMENT_ROOT")."/";
    return N_DoCleanPath($root);
  }

  function N_VirtualCleanPath ($item) 
  {
    return N_Cleanpath (N_InternalPath (N_CleanPath ($item)),1);
  }

  function N_CleanPath ($item, $virtual=false)
  {
    return N_DoCleanPath (N_InternalPath (N_DoCleanPath ($item)), $virtual);
  }

 function N_OneTimeRandom ()
  {
    global $onetimerandom;
    if (!$onetimerandom) {
      global $myconfig;
      $file = getenv("DOCUMENT_ROOT")."/local_rnd/".$myconfig["myname"].".txt";
      if (file_exists ($file)) {
        @$fd = fopen($file, 'rb');
        if ($fd) {
          $rnd = fread ($fd, 1000);
          fclose ($fd);
        }
        return $onetimerandom=$rnd."x";
      }
      @mkdir (getenv("DOCUMENT_ROOT")."/local_rnd/");
      N_Chmod (getenv("DOCUMENT_ROOT")."/local_rnd/");
      @$fd = fopen($file, 'wb');
      fwrite ($fd, N_GUID());
      fclose ($fd);    
      @$fd = fopen($file, 'rb');
      if ($fd) {
        $rnd = fread ($fd, 1000);
        fclose ($fd);
      }
      return $onetimerandom=$rnd."x";
    } else { 
      return $onetimerandom;
    }
  }

  function N_DoCleanPath ($item, $virtual=false) 
  {
    if (strpos($item, "\0") !== false) N_Die("N_DoCleanPath: null byte in path $item");
    $item = str_replace ("html:://", "html::/", $item);
    $item = str_replace ("html:://", "html::/", $item);
    $item = str_replace ("html:://", "html::/", $item);
    $item = str_replace ("\\","/", $item);
    if (strpos($item, "RCK") !== false) $item = str_replace ("/RCK".N_OneTimeRandom()."RCK", "", $item); // break recursive loop between N_OneTimeRandom -> N_Chmod -> N_DoCleanPath -> N_OneTimeRandom
    $marker = "5c6207c2df7295cea1ce8a99d4231685";
    $item = str_replace ("//", $marker, $item);
    if (strpos(" $item", "html::")) $item = str_replace ("html::", str_replace("//", $marker, N_CleanRoot()), $item);
    $item = str_replace ("//","/", $item);
    $item = str_replace ($marker, "//", $item);
    $i1 = substr ($item, 0, 1);
    $i2 = str_replace ("//","/",substr ($item, 1));
    $item = $i1.$i2;
    if (!$virtual) {
      global $myconfig;
      if ($myconfig["pathsubstitution"]) {
        foreach ($myconfig["pathsubstitution"] as $old => $new) {
           if (substr ($item, 0, strlen($old))==$old) {
             $item = $new.substr ($item, strlen($old));
           }
         }
      }
      if ($myconfig["32kfix"]) {
        $item = N_32kFix_Transform ($item);
      }
    }
    return $item;
  }

  function N_32kFix_UnTransform ($thepath)
  {
    return str_replace ("/32k/", "", $thepath);
  }

  function N_32kFix_Transform ($thepath)
  {
    if (strpos ($thepath, "/32k/")) return $thepath;
    if ($p = strpos ($thepath, "_sites/objects/history/")) {
      $found = N_32kFix_Exists ($thepath);
      if (!$found) $thepath = substr ($thepath, 0, $p+23) . substr ($thepath, $p+23, 3). "/32k/" . substr ($thepath, $p+26);
    } else if ($p = strpos ($thepath, "_sites/objects/")) {
      $found = N_32kFix_Exists ($thepath);
      if (!$found) $thepath = substr ($thepath, 0, $p+15) . substr ($thepath, $p+15, 3). "/32k/" . substr ($thepath, $p+18);

































    } else if ($p = strpos ($thepath, "_sites/preview/objects/")) {
      $found = N_32kFix_Exists ($thepath);
      if (!$found) $thepath = substr ($thepath, 0, $p+23) . substr ($thepath, $p+23, 3). "/32k/" . substr ($thepath, $p+26);
    }
    return $thepath;
  }

  function N_32kFix_Exists ($thepath)
  {
    $found = true;
    $file = str_replace ("\\", "/", $thepath);
    $prefix = "";
    if (substr ($file,1,1)==":") { // windows drive letter
      $prefix = substr ($file, 0, 2);
      $file = substr ($file, 2);
    }
    if (substr($file,0,1)=="/") {
      $prefix = $prefix."/";
      $file = substr ($file, 1);      
    }
    $array = explode ("/", $file); 
    for ($i=0; $i<sizeof($array)-1; $i++)
    {
      if ($i) $path.="/".$array[$i]; else $path=$array[0];
      if (!file_exists ($prefix.$path)) { // checked on windows and linux, it works for direcories
        $found = false;
      }
    }
    return $found;
  }

  function N_ShellPath ($item)
  {
    $item = N_CleanPath ($item);
    if (N_Windows()) {
      $item = str_replace ("/", "\\", $item);
    }
    return $item;
  }

  function N_InternalPath ($item)
  {
    $marker = "ea1ce8a99d45c6207c2df7231685295c";
    $item = N_DoCleanPath ($item);
    global $myconfig;
    if ($myconfig["pathsubstitution"]) {
      foreach ($myconfig["pathsubstitution"] as $old => $new) {
         if (substr ($item, 0, strlen($new))==$new) {
           $item = $old.substr ($item, strlen($new));
         }
      }
    }

    if ($myconfig["32kfix"]) {
      $item = N_32kFix_UnTransform ($item);
    }

    $item = $marker.$item;
    $root = getenv("DOCUMENT_ROOT");    
    $item = str_replace ($marker.$root, $marker."html::", $item);
    $item = str_replace ($marker.$root, $marker."html::", $item);
    $root = str_replace ("//","/", $root);
    $item = str_replace ($marker.$root, $marker."html::", $item);
    $item = str_replace ($marker."html::/", $marker."html::", $item);
    $item = str_replace ($marker, "", $item);
    return $item;
  }

  function N_VeryRawInternalPath ($item)
  {
    $marker = "ea1ce8a99d45c6207c2df7231685295c";
    $item = N_DoCleanPath ($item);
    global $myconfig;
    if ($myconfig["pathsubstitution"]) {
      foreach ($myconfig["pathsubstitution"] as $old => $new) {
         if (substr ($item, 0, strlen($new))==$new) {
           $item = $old.substr ($item, strlen($new));
         }
      }
    }

    $item = $marker.$item;
    $root = getenv("DOCUMENT_ROOT");    
    $item = str_replace ($marker.$root, $marker."html::", $item);
    $item = str_replace ($marker.$root, $marker."html::", $item);
    $root = str_replace ("//","/", $root);
    $item = str_replace ($marker.$root, $marker."html::", $item);
    $item = str_replace ($marker."html::/", $marker."html::", $item);
    $item = str_replace ($marker, "", $item);
    $item = str_replace ("html::", "/", $item);
    $item = str_replace ("//","/", $item);
    return $item;
  }

  function N_HTMLPath ($item)
  {
    return str_replace ("html::", "/", N_InternalPath ($item));
  }


  function N_LockFile ($filename, $waitseconds=15)
  {
    global $myconfig;
    $filename = N_CleanPath ($filename);
    $filename = $filename . "_lock_t3mp";
    N_Debug ("LOCK START $filename");
    global $LockedFiles;
    global $LockHandlers;
    if ((0+("0".$LockedFiles [$filename])) == 0) {
      $LockHandlers[$filename] = 0;
      for ($i=0; $i<500; $i++) { // try for 5 seconds
        if (!$LockHandlers[$filename]) {
          N_ErrorHandling (false);
          @$LockHandlers[$filename] = fopen($filename, 'a');
          N_ErrorHandling (true);
          if (!$LockHandlers[$filename]) {
            N_Sleep (10);
          }
        }
      }
      if ($LockHandlers[$filename]) {
        $flock = flock($LockHandlers[$filename], LOCK_NB + LOCK_EX);
        $waiting = 0;
        $start=time();
        while ((!$flock) and ((time() - $start) < $waitseconds)) {
          N_Sleep (10);
          $flock = flock($LockHandlers[$filename], LOCK_NB + LOCK_EX);
          $waiting = $waiting + 1;

        }

        if (! $flock) {
          N_DIE ("N_LockFile ($filename) - flock");
        }
      } else {
        N_DIE ("N_LockFile ($filename) - fopen");
      }
    }
    $LockedFiles [$filename] = 1 + ("0".$LockedFiles [$filename]);
    N_Debug ("LOCK COMPLETED $filename");
  }

  function N_UnlockFile ($filename)
  {
    $filename = N_CleanPath ($filename);
    $filename = $filename . "_lock_t3mp";
    N_Debug ("UNLOCK START $filename");
    global $LockedFiles;
    global $LockHandlers;
    $LockedFiles [$filename] = ("0".$LockedFiles [$filename]) - 1;
    if ((0+("0".$LockedFiles [$filename])) == 0) {
      if ($LockHandlers[$filename]) {
        flock ($LockHandlers[$filename], LOCK_UN);
        fclose ($LockHandlers[$filename]);
      }
      N_ErrorHandling (false);
      @unlink ($filename);
      N_ErrorHandling (true);
    }
    N_Debug ("UNLOCK COMPLETED $filename");
  }

  function N_QuickFileSize ($filename)
  {
    return VVFILE_FileSize ($filename, true);
  }

  function N_FileSize ($filename)
  {
    clearstatcache();
    return VVFILE_FileSize ($filename);
  }

  function N_NowFileSize ($filename)
  { // #MT#
    $ci = N_MTCacheInfo ($filename);
    if ($ci) {
      if (file_exists ($ci["cachepath"])) {
        return N_TrueFileSize ($ci["cachepath"]);
      } else {
        return N_TrueFileSize ($ci["truepath"]);
      }
    } else {
      return N_TrueFileSize ($filename);
    }
  }

  function N_TrueFileSize ($filename)
  {
    set_magic_quotes_runtime(0);
    if (substr ($filename, 0, 8) == "remote::") {
      $filename = substr ($filename, 8);
      $pos = strpos ($filename, "::");
      if ($pos) {
        $system = substr ($filename, 0, $pos);
        $filename = substr ($filename, $pos+2);
        $remote_command = "\$output = N_ReadFile ('$filename');"; // keep it safe
        return URPC ($system, $remote_command);
      }
    }

    $filename = N_CleanPath($filename);
    if (file_exists($filename)) return filesize ($filename);
    return 0;
  }

  function N_NiceSize($size, $decimals = 1)
  {
   // returns the "nice" formatting of a number (filesize).
   // 1024 --> 1kB etc

   // Fix for filesize() returning a signed 32 bit that should be unsigned. No idea what happens with files > 4GB.
   if ($size < 0 && $size >= -2147483648) $size += 4294967295;

   uuse ("multilang");
   for($si = 0; $size >= 1024; $size /= 1024, $si++);
   $ret = round($size, $decimals)." ".substr(' kMGT', $si, 1)."B";
   switch(ML_GetLanguage()) {
     case "en":
       $ret = str_replace(",", ".", $ret);
     break;
     case "nl":
       $ret = str_replace(".", ",", $ret);
     break;
   }
   return $ret;
  }

  function N_DeleteFile ($filename)
  {
    return VVFILE_DeleteFile ($filename);
  }

  function N_NowDeleteFile ($filename)
  { // #MT#
    $ci = N_MTCacheInfo ($filename);
    if ($ci) {
      if (file_exists ($ci["cachepath"])) {
        N_TrueDeleteFile ($ci["cachepath"]);
      }
      N_TrueDeleteFile ($ci["truepath"]);
      // make sure it stays deleted
      N_AddModifyPreciseScedule ("MT".$ci["virtualpath"], time()+1, 'N_TrueDeleteFile ($input);', $ci["truepath"]);
    } else {
      return N_TrueDeleteFile ($filename);
    }
  }

  function N_TrueDeleteFile ($filename)
  {
    $filename = N_CleanPath ($filename);
    N_ErrorHandling (false);
    @unlink ($filename);
    N_ErrorHandling (true);
  }

  function N_WriteFilePart ($filename, $offset, $content)
  {
    return VVFILE_WriteFilePart ($filename, $offset, $content);
  }

  function N_NowWriteFilePart ($filename, $offset, $content)
  { // #MT#
    $ci = N_MTCacheInfo ($filename);
    if ($ci) {
      $result = N_TrueWriteFilePart ($ci["cachepath"], $offset, $content);
      if (!file_exists ($ci["truepath"])) N_TrueWriteFile ($ci["truepath"], "*"); // dummy file
      N_AddModifyPreciseScedule ("MT".$ci["virtualpath"], time()+1, 'N_TrueCopyFile ($input["truepath"], $input["cachepath"]);', $ci);
      return $result;
    } else {
      return N_TrueWriteFilePart ($filename, $offset, $content);
    }
  }

  function N_TrueWriteFilePart ($filename, $offset, $content)
  {
    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StartTimer ("write");
    $filename = N_CleanPath ($filename);
    N_ErrorHandling (false);
    @$fdsafe = fopen($filename, 'r+b');
    // TODO: check that the file is not a vref, if it is, call N_WriteFilePart.
    N_ErrorHandling (true);
    if (!$fdsafe) {
      N_WriteFile ($filename, "");
      N_ErrorHandling (false);
      @$fdsafe = fopen($filename, 'r+b');
      N_ErrorHandling (true);
      if (!$fdsafe) {
        N_DIE ("N_WriteFile ($filename) ", 'fopen failed');
      }
    }
    fseek ($fdsafe, $offset);
    $result = fwrite($fdsafe, $content);
    if ($result==false)
    {
      N_DIE ("N_WriteFilePart ($filename) ", 'fwrite failed');
    }
    fclose($fdsafe);
    N_LogWriteFile ($filename);
    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StopTimer ("write");
  } 

  function N_ReadFilePart ($filename, $offset, $size)
  {
    return VVFILE_ReadFilePart ($filename, $offset, $size);
  }

  function N_NowReadFilePart ($filename, $offset, $size)
  { // #MT#
    $ci = N_MTCacheInfo ($filename);
    if ($ci) {
      if (!file_exists ($ci["cachepath"])) {
        N_TrueCopyFile ($ci["cachepath"], $ci["truepath"]);
      }
      return N_TrueReadFilePart ($ci["cachepath"], $offset, $size);
    } else {
      return N_TrueReadFilePart ($filename, $offset, $size);
    }
  }

  function N_TrueReadFilePart ($filename, $offset, $size)
  {

    $filename = N_CleanPath ($filename);
    N_ErrorHandling (false);
    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StartTimer ("read");
    @$fdsafe = fopen($filename, 'rb');
    N_ErrorHandling (true);
    if ($fdsafe) {
      fseek ($fdsafe, $offset);
      $content= fread ($fdsafe, $size);
      fclose($fdsafe);
      global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StopTimer ("read");
      return $content;
    }
    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StopTimer ("read");
  }

  function N_FileMD5 ($filename)
  {
    return VVFILE_FileMD5 ($filename);
  }

  function N_NowFileMD5 ($filename)
  { // #MT#
    $ci = N_MTCacheInfo ($filename);
    if ($ci) {
      if (!file_exists ($ci["cachepath"])) {
        N_TrueCopyFile ($ci["cachepath"], $ci["truepath"]);
      }
      return N_TrueFileMD5 ($ci["cachepath"]);
    } else {
      return N_TrueFileMD5 (N_CleanPath ($filename));
    }
  }

  function N_TrueFileMD5 ($filename)
  {
    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StartTimer ("read");
    if (!function_exists ("md5_file")) {
      $result = md5 (N_TrueReadFile ($filename));
    } else {
      if (N_TrueFileSize ($filename)) {
        $result = md5_file ($filename);
       } else {
        $result = md5 ("");
      }
    }
    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StopTimer ("read");
    return $result;
  }

  function N_ReadFile ($filename)
  {
    return VVFILE_ReadFile($filename);
  }

  function N_NowReadFile ($filename)
  { #MT#
    $ci = N_MTCacheInfo ($filename);
    if ($ci) {
      if (!file_exists ($ci["cachepath"])) {
        N_TrueCopyFile ($ci["cachepath"], $ci["truepath"]);
      }
      return N_TrueReadFile ($ci["cachepath"]);
    } else {
      return N_TrueReadFile ($filename);
    }
  }

  function N_SlowReadFile ($filename)
  { #MT#
    $ci = N_MTCacheInfo ($filename);
    if ($ci) {
      if (file_exists ($ci["cachepath"])) {
      return N_TrueReadFile ($ci["cachepath"]);
      }
      return N_TrueReadFile ($ci["truepath"]);
    } else {
      return N_ReadFile ($filename);
    }
  }

  function N_TrueReadFile ($filename)
  {
    if (!$filename) return "";
    N_Debug ("N_ReadFile START ($filename)", "N_ReadFile");
    $content = N_DoReadFile ($filename);
    if (!strlen($content)) {
      // make sure someone is not updating this file
      N_Lock (N_CleanPath ($filename));
      $content = N_DoReadFile ($filename);     
      N_Unlock (N_CleanPath ($filename));
    }
    N_Debug ("N_ReadFile END ($filename) bytes:".strlen($content));
    return $content;
  } 

  function N_DoReadFile ($filename)
  {
    set_magic_quotes_runtime(0);

    if (substr ($filename, 0, 8) == "remote::") {
      $filename = substr ($filename, 8);
      $pos = strpos ($filename, "::");
      if ($pos) {
        $system = substr ($filename, 0, $pos);
        $filename = substr ($filename, $pos+2);
        $remote_command = "\$output = N_ReadFile ('$filename');"; // keep it safe
        return URPC ($system, $remote_command);
      }
    }

    $filename = N_CleanPath ($filename);
    N_Debug ("READ START $filename");

    $content = "";
    N_ErrorHandling (false);    
    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StartTimer ("read");
    @$fdsafe = fopen($filename, 'rb');
    N_ErrorHandling (true);    
    $chk = 1;
    if ($fdsafe) {
      while (!feof($fdsafe) AND $chk) {
        $block = fread ($fdsafe, 1000000);
        $content .= $block;
        $chk = strlen ($block);
      }
      fclose($fdsafe);
    }
    N_Debug ("READ ".strlen($block)." bytes");
    N_Debug ("READ COMPLETED $filename");
    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StopTimer ("read");
    return $content;
  }

  function N_QuickReadFile ($filename)
  {
    return N_ReadFile ($filename);

  }

  function N_LogWriteFile ($filename)
  {
    return;
    $filename = N_InternalPath ($filename);
    $filename = str_replace ("html::", "", $filename);
    if (substr($filename, 0, 1)=="/") $filename = substr ($filename, 1);
    $logfile = (int)(time()/3600);
    $path = str_replace (":", "__", N_KeepBefore ($filename, "/"));
    if ($path!="logs" && $path!="searchindex" && $path!="dfc" && $path!="tmp" && $path!="metabase") {
      N_AppendFile ('html::/logs/'.$logfile.'_'.$path.'.filelog', "$filename\n");
    }
  }

  function N_SamePathI($a, $b) { // case insensitive path compare !NB: for linux: case sensitive!
    global $myconfig;
    if($myconfig["linux"]=="yes") { // to solve Newdoc problem when template has same filename as new document but different case.
      $a1 = N_CleanPath($a);
      $b1 = N_CleanPath($b); 
    } else {
      $a1 = strtolower(N_CleanPath($a));
      $b1 = strtolower(N_CleanPath($b));
    }

    $la1 = strlen($a1);
    $lb1 = strlen($b1);

    // remove trailing / (if found)
    if($la1>1) {
      if(substr($a1,$la1-1)=="/") $a1 = substr($a1,0,$la1-1);
    }
    if($lb1>1) {
      if(substr($b1,$lb1-1)=="/") $b1 = substr($b1,0,$lb1-1);
    }

    if( $a1 == $b1 ) {
      return true; 
    } else {
      return false; 
    }
  }

  function N_Rename ($old, $new) 
  {
    if(!N_SamePathI($old, $new)) {
      if (N_MTCacheActive ($old) || N_MTCacheActive ($new)) {
        N_CopyFile ($new, $old);
        N_DeleteFile ($old);
      } else {
        N_TrueRename ($old, $new);
      }
    }
  } 

  function N_TrueRename ($old, $new) // simulate linux atomic rename in Windows
  {
    if(!N_SamePathI($old, $new)) {
      N_ErrorHandling (false);
      $old = N_CleanPath ($old);
      $new = N_CleanPath ($new);
      rename ($old, $new);
      clearstatcache();
      if (is_file ($old)) { // rename failed
        $retry = 20; // 5 seconds
        while (--$retry && is_file ($old)) {
          $tmp = N_GUID();
          // manually delete the old file (conveniently named "$new"), needed in Windows
          unlink ($new);
          rename ($old, $new);
          clearstatcache();
          if (file_exists ($old)) { // removing file failed, try special Windows trick: rename it instead
            rename ($new, $new.$tmp."_old_t3mp");
            unlink ($new.$tmp."_old_t3mp");
            rename ($old, $new);
            clearstatcache();
          }
          // try to rename again
          if (is_file ($old)) N_Sleep (100+N_Random(300));
        }
        clearstatcache();
        if (is_file ($old)) {
          // failure is not an option
          N_DIE ("FAILED N_TrueRename ($old, $new)");
        }
      }
      N_ErrorHandling (true);
    }
  }

  function N_FileTime ($filename)
  {
    $filename = N_CleanPath ($filename);
    $ci = N_MTCacheInfo ($filename);
    if ($ci) {
      if (file_exists ($ci["cachepath"])) {
        return @filemtime ($ci["cachepath"]);
      } else {
        return @filemtime ($ci["truepath"]);
      }
    } else {
      return @filemtime ($filename);
    }
  }

  function N_Touch ($filename, $time)
  {
    $filename = N_CleanPath ($filename);
    $ci = N_MTCacheInfo ($filename);
    if ($ci) {
      if (file_exists ($ci["cachepath"])) {
        return touch ($ci["cachepath"], $time);
      } else {
        return touch ($ci["truepath"], $time);
      }
    } else {
      return touch ($filename, $time);
    }
  }

  function N_QuickWriteFile ($filename, $content)
  {
    N_WriteFile ($filename, $content);
  }

  function N_WriteFile ($filename, $content)
  {
    return VVFILE_WriteFile ($filename, $content);
  }


  function N_NowWriteFile ($filename, $content)
  { // #MT# 
    $ci = N_MTCacheInfo ($filename);
    if ($ci) {
      $result =  N_TrueWriteFile ($ci["cachepath"], $content);
      if (!file_exists ($ci["truepath"])) N_TrueWriteFile ($ci["truepath"], "*"); // dummy file
      N_AddModifyPreciseScedule ("MT".$ci["virtualpath"], time()+1, 'N_TrueCopyFile ($input["truepath"], $input["cachepath"]);', $ci);
    } else {
      $result = N_TrueWriteFile ($filename, $content);
    }
    return $result;
  }

  function N_TrueWriteFile ($filename, $content)
  {
    global $safe_mode;
    if ($safe_mode) {
      N_ReportError ("N_WriteFile ($filename)", "ignored due to safe mode");
      return;
    }
    N_Debug ("N_WriteFile START ($filename, ...) bytes:".strlen ($content), "N_WriteFile");

// gv 25-11-2009 return nr of bytes written
    N_Debug ("N_WriteFile START #0");

    $fwriteresult = N_DoWriteFile ($filename, $content);

    $content = (string) $content; // It becomes a sting anyway when writing to disk. Do it here too to prevent false alarm in the check below (for weird values of $content)
    if ($content != N_DoReadFile ($filename)) {
      N_Log ("failedwritefile", $filename);
      N_Sleep (20+N_Random(80));
      N_DoWriteFile ($filename, $content);
      if ($content != N_DoReadFile ($filename)) {
        N_Sleep (20+N_Random(80));
        N_DoWriteFile ($filename, $content);
        if ($content != N_DoReadFile ($filename)) {
          N_Sleep (20+N_Random(80));
          N_DoWriteFile ($filename, $content);
           $readcontent = N_DoReadFile ($filename);
           if ($content != $readcontent) {
             N_DIE ("Failed to write to file ".$filename." contentsize: ".strlen($content)." content: ".base64_encode($content)." read: ".base64_encode ($readcontent));
           }
        }
      }
    }
    global $triggerwritefile_nesting;
    $triggerwritefile_nesting++;
    if ($triggerwritefile_nesting==1) {
      uuse ("flex");
      $custom = FLEX_LocalComponents (IMS_SuperGroupname(), "triggerwritefile");
      if ($custom) {
        foreach ($custom as $id => $specs) {
          $filename = N_InternalPath ($filename);
          eval ($specs["code_trigger"]);
        }
      }
    }
    $triggerwritefile_nesting--;
    N_Debug ("N_WriteFile END ($filename, ...) bytes:".strlen ($content));
    return $fwriteresult;
  }

  function N_DoWriteFile ($filename, $content, $uselocking=true)
  {
    $writeresult = -1;
    global $safe_mode, $myconfig;
    N_Debug ("WRITE START #1");
    if ($safe_mode) {
      N_ReportError ("N_WriteFile ($filename)", "ignored due to safe mode");
      return;
    }

    N_Debug ("WRITE START #2");
    set_magic_quotes_runtime(0);

    N_Debug ("WRITE START #3");
    if (substr ($filename, 0, 8) == "remote::") {
      $filename = substr ($filename, 8);
      $pos = strpos ($filename, "::");
      if ($pos) {
        $system = substr ($filename, 0, $pos);
        $filename = substr ($filename, $pos+2);
        $remote_command = "\$output = N_WriteFile ('$filename', \$input);";
        return URPC ($system, $remote_command, $content);
      }
    }    

    N_Debug ("WRITE START #4");
    $filename = N_CleanPath ($filename);
    N_Debug ("WRITE START $filename");

    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StartTimer ("write");
    $postfix = "_".N_Random (100000)."_new_t3mp";
    N_ErrorHandling (false);
    @$fdsafe = fopen($filename.$postfix, 'wb');
    N_ErrorHandling (true); 

    if (!$fdsafe) {
      // create needed directories
      $file = str_replace ("\\", "/", $filename);
      $prefix = "";
      if (substr ($file,1,1)==":") { // windows drive letter
        $prefix = substr ($file, 0, 2);
        $file = substr ($file, 2);
      }
      if (substr($file,0,1)=="/") {
        $prefix = $prefix."/";
        $file = substr ($file, 1);      
      }
      $array = explode ("/", $file); 
      for ($i=0; $i<sizeof($array)-1; $i++)
      {
        N_ErrorHandling (false);
        if ($i) $path.="/".$array[$i]; else $path=$array[0];
        if (!file_exists ($prefix.$path)) { // checked on windows and linux, it works for direcories
          mkdir ($prefix.$path);
          // LF20090625: only do chmod on directories just created, not on all the directories above it
          N_Chmod ($prefix.$path);
        }
        N_ErrorHandling (true);
      }

      // try again
      N_ErrorHandling (false);
      $fdsafe = fopen($filename.$postfix, 'wb');
      N_ErrorHandling (true);

      if (!$fdsafe) {
        N_DIE ("N_WriteFile ($filename) fopen failed");
      }
    }

    if (strlen($content)) {
      $writeresult = fwrite($fdsafe, $content);
      if ($writeresult==false)
      {
        N_DIE ("N_WriteFile ($filename) ", 'fwrite failed');
      }
    }

    $result = fclose($fdsafe);
    if (!$result)
    {
      N_DIE ("N_WriteFile ($filename) ", 'fclose failed');
    }

    N_ErrorHandling (false);
    N_Chmod ($filename.$postfix);
    N_ErrorHandling (true);

    if ($uselocking) N_Lock (N_CleanPath ($filename)); // lock file as briefly as is needed
    N_TrueRename ($filename.$postfix, $filename);
    if ($uselocking) N_Unlock (N_CleanPath ($filename));

    N_LogWriteFile ($filename);
    N_Debug ("WRITE COMPLETED $filename");

    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StopTimer ("write");
    return $writeresult;
  }

  function N_CreateFile ($filename, $content)
  {
    N_WriteFile ($filename, $content);
  }

  function N_SuperQuickAppendFile ($filename, $content)
  {
//    N_Debug ("N_SuperQuickAppendFile ($filename, ...)"); 
    global $appendqueue, $appendsize;
    $appendqueue [$filename].=$content;
    $appendsize += strlen($content);
    if ($appendsize > 10000000) {
      N_FlushAppendQueue ();
    }
  }

  function N_FlushAppendQueue ()
  {
    N_Debug ("N_FlushAppendQueue ()");
    global $appendqueue, $appendsize;
    if (is_array ($appendqueue)) foreach ($appendqueue as $filename => $content) {
      N_QuickAppendFile ($filename, $content);
    }
    $appendqueue = array();
    $appendsize = 0;
  }

  function N_QuickAppendFile ($filename, $content)
  { 
    N_AppendFile ($filename, $content, true);
  }

  function N_AppendFile ($filename, $content, $quick=false)
  {
    return VVFILE_AppendFile ($filename, $content, $quick);
  }

  function N_NowAppendFile ($filename, $content, $quick=false)
  { // #MT#
    $ci = N_MTCacheInfo ($filename);
    if ($ci) {
      $result = N_TrueAppendFile ($ci["cachepath"], $content, $quick);
      if (!file_exists ($ci["truepath"])) N_TrueWriteFile ($ci["truepath"], "*"); // dummy file
      N_AddModifyPreciseScedule ("MT".$ci["virtualpath"], time()+1, 'N_TrueCopyFile ($input["truepath"], $input["cachepath"]);', $ci);
      return $result;
    } else {
      return N_TrueAppendFile ($filename, $content, $quick);
    }
  }

  function N_TrueAppendFile ($filename, $content, $quick=false)
  {
    global $safe_mode;
    if ($safe_mode) {
      N_ReportError ("N_WriteFile ($filename)", "ignored due to safe mode");
      return;
    }

    if (substr ($filename, 0, 8) == "remote::") {
      N_WriteFile ($filename, N_ReadFile ($filename).$content);
      return;
    }    

    $filename = N_CleanPath ($filename);
    N_Debug ("APPEND START $filename");

    if (!is_file ($filename)) {
      N_WriteFile ($filename, N_ReadFile ($filename).$content);
      return;
    }

    if (!$quick) N_LockFile ($filename);

    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StartTimer ("write");
    $fdsafe = fopen($filename, 'ab');
    // TODO: check that the file is not a vref, if it is, call N_AppendFile
    // TODO2: AppendFile is also used to write delayed modifications to vref's, so in that case, it's ok if it a vref
    if (!$fdsafe)
    {
      N_ReportError ("N_AppendFile ($filename) ", 'fopen failed');
    }
    fwrite($fdsafe, $content);
    fclose($fdsafe);

    if (!$quick) N_UnlockFile ($filename);

    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StopTimer ("write");
    N_Debug ("APPEND COMPLETED $filename");
  }

  function N_CheckCallMeOften ($allowloop = false)
  {

  }

  function N_Exit ()
  {
    global $profiling, $profilingdata, $profileme, $N_Start; 
    N_Debug ("EXIT START");  
    MB_Flush();
    N_ExitQuickScedule();
    MB_Flush();
    N_FlushAppendQueue ();
    N_CheckCallMeOften ();
    N_CleanupPMLog ();
    N_Debug ("EXIT COMPLETED");

    if (IMS_MergeHtmlHeaders("")) trigger_error("IMS_CaptureHtmlHeaders() called and N_Exit() was reached without merging captured headers. Some headers have been discarded.", E_USER_WARNING);

    if ($profiling=="yes") {
      echo "<br>&nbsp;<font face=\"arial\" size=\"2\"><b>PROFILING DATA</b><br>";
      $N_Current = N_MicroTime(); 
      echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>TOTAL elapsed time</b>: " . (int)(($N_Current-$N_Start) * 1000) . "ms<br>";
      if (function_exists('memory_get_peak_usage')) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>PEAK mem usage</b>: " . round(memory_get_peak_usage() / 1048576) . "MB<br><br>";
      } else {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>CURRENT mem usage</b>: " . round(memory_get_usage() / 1048576) . "MB<br>";
      }

      $pd = $profilingdata;
      ksort ($pd ["counters"]);
      foreach ($pd ["counters"] as $name => $counter) {
        $url = N_AlterURL (N_MyVeryFullURL(), "profileme", $name);
        echo "&nbsp;&nbsp;&nbsp;&nbsp;<b>$name</b>: $counter calls <a href=\"$url\">analyze</a><br>";
      }      
      echo "<br>";
      if ($profileme) {
        echo "&nbsp;<font face=\"arial\" size=\"2\"><b>RESULT OF ANALYZING $profileme</b><br><br>";        
        for ($i=1; $i<=$pd ["tracecount"]; $i++) {
          echo "&nbsp;<font face=\"arial\" size=\"2\"><b>Elapsed time</b>: " . (int)(($pd ["trace"][$i]["time"]-$N_Start) * 1000) . "ms (call $i/".$pd ["counters"][$profileme].")";
          echo "<br>&nbsp;<font face=\"arial\" size=\"2\"><b>Message</b>: " . $pd ["trace"][$i]["message"];
          N_PrintStack ($pd ["trace"][$i]["stack"], true);
          SHIELD_FlushEncoded();
          N_Flush();
        }
      }
    }

    // HERE TO LOGGING OF CONVERSION
    global $N_EXIT_LOGGING;   
    if ( is_array( $N_EXIT_LOGGING ) )
    {
      foreach( $N_EXIT_LOGGING AS $logbook => $logabouts )
      {
        foreach( $logabouts AS $logabout => $str )
        {
          $ELAPSED = round(N_elapsed(),2);
          $str = $logabout . ':n_exit='.($ELAPSED > 5?'<b>'.$ELAPSED.'</b>':$ELAPSED).'s ' . $str;
          N_log( $logbook , $str );
        }
      }
    }

    global $myconfig, $PHP_AUTH_USER, $REQUEST_METHOD, $encspec;
    if ($myconfig["performancelogging"] == "yes") {
      $uri = getenv("REQUEST_URI");

      $scr = getenv("SCRIPT_NAME");
      if (strpos ($scr, "index.php") && !strpos ($uri, "index.php")) {
        $logto = "elapsed_404";
      } else if (strpos ($uri, "_com")) {
        $logto = "elapsed_sites";
      } else if (strpos ($uri, "openims.php") || strpos ($uri, "img.php") || strpos ($uri, "form.php") || strpos ($uri, "action.php")) {
        $logto = "elapsed_uif";
      } else if (strpos ($uri, "callmeoften.php") || strpos ($uri, "grid.php") || strpos ($uri, "master.php")) {
        $logto = "elapsed_back";
      } else if (strpos ($uri, "private")) {
        $logto = "elapsed_dev";
      } else {
        $logto = "elapsed_other";
      }
      $entry = (int)((N_Elapsed()) * 1000) . "ms"; 
// Does no work as planned, unfortunately.
//      $entry = (int)((N_Elapsed()) * 1000) . "ms" . "/W:";
//      $entry .= (int)(( N_TimerTotal("write")) * 1000) . "ms" . "/R:";
//      $entry .= (int)(( N_TimerTotal("read")) * 1000) . "ms" . "/S:";
//      $entry .= (int)(( N_TimerTotal("sql")) * 1000) . "ms";
      if ($PHP_AUTH_USER) {
        $entry .= " (".$PHP_AUTH_USER.")";
      } else {
        global $REMOTE_ADDR;
        $entry .= " ($REMOTE_ADDR)";
      }
      $entry .= " ".getenv("REQUEST_URI");
      if ($REQUEST_METHOD=="POST")
      {
        $entry .= " POST";
        foreach ($_POST as $arg => $val) {

          //ericd 120912 stripcslashes: a string as input
          if(!is_array($val)) 
            $entry .= " ".$arg."=".stripcslashes($val); 
          else 
            $entry .= " ".$arg."=".$val;

        }
      }   
      if ($encspec) {
        $es = SHIELD_Decode ($encspec);
        $entry .= " TITLE=".$es["title"];
      }           
      N_Log ($logto, $entry);
    }
  }

  function N_GUID()
  {
    global $HTTP_REFERER, $REMOTE_USER, $REMOTE_PORT, $HTTP_USER_AGENT, $REMOTE_ADDR;
    global $LastGUID;
    $LastGUID = md5(getmypid() . time() . microtime() . $HTTP_REFERER . $REMOTE_USER . $REMOTE_PORT . $HTTP_USER_AGENT . $REMOTE_ADDR . $LastGUID);
    return $LastGUID;
  }

  function N_If ($me, $true, $false="")
  {
    if ($me) return $true; else return $false;
  }

  function N_Counter ($name, $atleast=0) {
    $counter = MB_Fetch ("internalcounters", $name, "value");
    $counter = $counter + 1;
    if ($counter<$atleast) $counter=$atleast;
    MB_Store ("internalcounters", $name, "value", $counter);

    return $counter;
  }

  function N_AgeByDate ($iDay, $iMonth, $iYear) {
    $iTimeStamp = (mktime() - 86400) - mktime(0, 0, 0, $iMonth, $iDay, $iYear);
    $iDays = $iTimeStamp / 86400;
    $iYears = floor($iDays / 365.25);

    return $iYears;
  }

  function N_Year () {
    $today   = adodb_getdate();

    return $today[year];
  }

  function N_Date2Year ($time)
  {
    $record = adodb_getdate ($time);
    return $record [year];
  }

  function N_Date2Month ($time)
  {
    $record = adodb_getdate ($time);
    return $record [mon];

  }

  function N_Date2Day ($time)
  {
    $record = adodb_getdate ($time);
    return $record [mday];
  }

  function N_Time2Hour ($time)
  {
    $record = adodb_getdate ($time);
    return $record [hours];
  }

  function N_Time2Minute ($time)
  {
    $record = adodb_getdate ($time);
    return $record [minutes];
  }

  function N_Time2Second ($time)
  {
    $record = adodb_getdate ($time);
    return $record [seconds];
  }

  function N_BuildDate ($year, $month, $day)
  {
    return N_BuildDateTime ($year, $month, $day, 12, 0, 0);
  }

  function N_BuildTime ($hour, $minute=0, $second=0)
  {
    return N_BuildDateTime (1980, 1, 1, $hour, $minute, $second);
  }

  function N_BuildDateTime ($year, $month, $day, $hour=0, $minute=0, $second=0)
  {
    return adodb_mktime ($hour, $minute, $second, $month, $day, $year);
  }

  function N_mktime($hour, $minute, $second, $month, $day, $year)
  {
    return adodb_mktime ($hour, $minute, $second, $month, $day, $year);
  }

/* Call it like this:
 *    function N_Date ($format)
 *    function N_Date ($format, $time)
 *    function N_Date ($format_nl, $format_en)
 *    function N_Date ($format_nl, $format_en, $time)
 *    function N_Date ($format_nl, $format_en, $time, array("de" => $format_de));
 * 
 * $format is a standard PHP date format (such as can be used with date()),
 *    but "named months" and "named weekdays" are intercepted by this function 
 *    so that they can be handled multilingually. The rest of $format is handled by adodb_date().
 * Other languages than NL and EN: Usually you do not need to use the 4th argument.
 *    If you omit the 4th argument, or if the 4th argument does not handle the current language,
 *    $format_nl will be treated as a generic "day-month-year" specification,
 *    $format_en will be treated as a generic "month-day-year" specification.
 * Currently supported languages are: nl, en, fr (fallback: nl), de (fallback: nl)
 */
  function N_Date ($p1, $p2=-1, $p3=-1, $p4 = array()) 
  {
    uuse ("multilang");
    $lang = ML_GetLanguage();
    
    if (is_numeric ($p2)) {
      $format = $p1;
      $time = $p2;
    } else {
      $time = $p3;
      if (is_array($p4) && $p4[$lang]) {
        $format = $p4[$lang];
      } else {
        if ($lang == "nl" || $lang == "fr" || $lang == "de") { // Languages that use day-month-year order
          $format = $p1;
        } else {
          $format = $p2;
        }      
      }
    }

    if ($time==-1) $time = time();

    global $recordedtimestamp;

    $recordedtimestamp = $time;

    $today   = adodb_getdate($time);
    $weekday = $today[wday];
    $day     = $today[mday];
    $month   = $today[mon];
    $year    = $today[year];

    if ($lang == "nl") {
      if ($weekday == 0) $weekday1 = "zo";
      if ($weekday == 1) $weekday1 = "ma";
      if ($weekday == 2) $weekday1 = "di";
      if ($weekday == 3) $weekday1 = "wo";
      if ($weekday == 4) $weekday1 = "do";
      if ($weekday == 5) $weekday1 = "vr";
      if ($weekday == 6) $weekday1 = "za";
      if ($weekday == 7) $weekday1 = "zo";

      if ($weekday == 0) $weekday2 = "zondag";
      if ($weekday == 1) $weekday2 = "maandag";
      if ($weekday == 2) $weekday2 = "dinsdag";
      if ($weekday == 3) $weekday2 = "woensdag";
      if ($weekday == 4) $weekday2 = "donderdag";
      if ($weekday == 5) $weekday2 = "vrijdag";
      if ($weekday == 6) $weekday2 = "zaterdag";
      if ($weekday == 7) $weekday2 = "zondag";

      if ($month == 1)  $month1 = "jan";
      if ($month == 2)  $month1 = "feb";
      if ($month == 3)  $month1 = "mrt";
      if ($month == 4)  $month1 = "apr";
      if ($month == 5)  $month1 = "mei";
      if ($month == 6)  $month1 = "jun";
      if ($month == 7)  $month1 = "jul";
      if ($month == 8)  $month1 = "aug";
      if ($month == 9)  $month1 = "sep";
      if ($month == 10) $month1 = "okt";
      if ($month == 11) $month1 = "nov";
      if ($month == 12) $month1 = "dec";

      if ($month == 1)  $month2 = "januari";
      if ($month == 2)  $month2 = "februari";
      if ($month == 3)  $month2 = "maart";
      if ($month == 4)  $month2 = "april";
      if ($month == 5)  $month2 = "mei";
      if ($month == 6)  $month2 = "juni";
      if ($month == 7)  $month2 = "juli";
      if ($month == 8)  $month2 = "augustus";
      if ($month == 9)  $month2 = "september";
      if ($month == 10) $month2 = "oktober";
      if ($month == 11) $month2 = "november";
      if ($month == 12) $month2 = "december";    
    } elseif ($lang == "fr") {
      if ($weekday == 0) $weekday1 = "dim";
      if ($weekday == 1) $weekday1 = "lun";
      if ($weekday == 2) $weekday1 = "mar";
      if ($weekday == 3) $weekday1 = "mer";
      if ($weekday == 4) $weekday1 = "jeu";
      if ($weekday == 5) $weekday1 = "ven";
      if ($weekday == 6) $weekday1 = "sam";
      if ($weekday == 7) $weekday1 = "dim";
      if ($weekday == 0) $weekday2 = "dimanche";
      if ($weekday == 1) $weekday2 = "lundi";
      if ($weekday == 2) $weekday2 = "mardi";
      if ($weekday == 3) $weekday2 = "mercredi";
      if ($weekday == 4) $weekday2 = "jeudi";
      if ($weekday == 5) $weekday2 = "vendredi";
      if ($weekday == 6) $weekday2 = "samedi";
      if ($weekday == 7) $weekday2 = "dimanche";
      if ($month == 1) $month1 = "jan";
      if ($month == 2) $month1 = "f".chr(233)."v";
      if ($month == 3) $month1 = "mar";
      if ($month == 4) $month1 = "avr";
      if ($month == 5) $month1 = "mai";
      if ($month == 6) $month1 = "jun";
      if ($month == 7) $month1 = "jui";
      if ($month == 8) $month1 = "ao".chr(251);
      if ($month == 9) $month1 = "sep";
      if ($month == 10) $month1 = "oct";
      if ($month == 11) $month1 = "nov";
      if ($month == 12) $month1 = "d".chr(233)."c";
      if ($month == 1) $month2 = "janvier";
      if ($month == 2) $month2 = "f".chr(233)."vrier";
      if ($month == 3) $month2 = "mars";
      if ($month == 4) $month2 = "avril";
      if ($month == 5) $month2 = "mai";
      if ($month == 6) $month2 = "juin";
      if ($month == 7) $month2 = "juillet";
      if ($month == 8) $month2 = "ao".chr(251)."t";
      if ($month == 9) $month2 = "septembre";
      if ($month == 10) $month2 = "octobre";
      if ($month == 11) $month2 = "novembre";
      if ($month == 12) $month2 = "d".chr(233)."cembre";
    } elseif ($lang == "de") {
      if ($weekday == 0) $weekday1 = "So";
      if ($weekday == 1) $weekday1 = "Mo";
      if ($weekday == 2) $weekday1 = "Di";
      if ($weekday == 3) $weekday1 = "Mi";
      if ($weekday == 4) $weekday1 = "Do";
      if ($weekday == 5) $weekday1 = "Fr";
      if ($weekday == 6) $weekday1 = "Sa";
      if ($weekday == 7) $weekday1 = "So";
      if ($weekday == 0) $weekday2 = "Sonntag";
      if ($weekday == 1) $weekday2 = "Montag";
      if ($weekday == 2) $weekday2 = "Dienstag";
      if ($weekday == 3) $weekday2 = "Mittwoch";
      if ($weekday == 4) $weekday2 = "Donnerstag";
      if ($weekday == 5) $weekday2 = "Freitag";
      if ($weekday == 6) $weekday2 = "Samstag";
      if ($weekday == 7) $weekday2 = "Sonntag";
      if ($month == 1) $month1 = "Jan";
      if ($month == 2) $month1 = "Feb";
      if ($month == 3) $month1 = "M".chr(228)."r";
      if ($month == 4) $month1 = "Apr";
      if ($month == 5) $month1 = "Mai";
      if ($month == 6) $month1 = "Jun";
      if ($month == 7) $month1 = "Jul";
      if ($month == 8) $month1 = "Aug";
      if ($month == 9) $month1 = "Sep";
      if ($month == 10) $month1 = "Okt";
      if ($month == 11) $month1 = "Nov";
      if ($month == 12) $month1 = "Dez";
      if ($month == 1) $month2 = "Januar";
      if ($month == 2) $month2 = "Februar";
      if ($month == 3) $month2 = "M".chr(228)."rz";
      if ($month == 4) $month2 = "April";
      if ($month == 5) $month2 = "Mai";
      if ($month == 6) $month2 = "Juni";
      if ($month == 7) $month2 = "Juli";
      if ($month == 8) $month2 = "August";
      if ($month == 9) $month2 = "September";
      if ($month == 10) $month2 = "Oktober";
      if ($month == 11) $month2 = "November";
      if ($month == 12) $month2 = "Dezember";
    } else {
      if ($weekday == 0) $weekday1 = "Su";
      if ($weekday == 1) $weekday1 = "Mo";
      if ($weekday == 2) $weekday1 = "Tu";
      if ($weekday == 3) $weekday1 = "We";
      if ($weekday == 4) $weekday1 = "Th";
      if ($weekday == 5) $weekday1 = "Fr";
      if ($weekday == 6) $weekday1 = "Sa";
      if ($weekday == 7) $weekday1 = "Su";

      if ($weekday == 0) $weekday2 = "Sunday";
      if ($weekday == 1) $weekday2 = "Monday";
      if ($weekday == 2) $weekday2 = "Tuesday";
      if ($weekday == 3) $weekday2 = "Wednesday";
      if ($weekday == 4) $weekday2 = "Thursday";
      if ($weekday == 5) $weekday2 = "Friday";
      if ($weekday == 6) $weekday2 = "Saturday";
      if ($weekday == 7) $weekday2 = "Sunday";

      if ($month == 1)  $month1 = "Jan";
      if ($month == 2)  $month1 = "Feb";
      if ($month == 3)  $month1 = "Mar";
      if ($month == 4)  $month1 = "Apr";
      if ($month == 5)  $month1 = "May";
      if ($month == 6)  $month1 = "Jun";
      if ($month == 7)  $month1 = "Jul";
      if ($month == 8)  $month1 = "Aug";
      if ($month == 9)  $month1 = "Sep";
      if ($month == 10) $month1 = "Oct";
      if ($month == 11) $month1 = "Nov";
      if ($month == 12) $month1 = "Dec";

      if ($month == 1)  $month2 = "January";
      if ($month == 2)  $month2 = "February";
      if ($month == 3)  $month2 = "March";
      if ($month == 4)  $month2 = "April";
      if ($month == 5)  $month2 = "May";
      if ($month == 6)  $month2 = "June";
      if ($month == 7)  $month2 = "July";
      if ($month == 8)  $month2 = "August";
      if ($month == 9)  $month2 = "September";
      if ($month == 10) $month2 = "October";
      if ($month == 11) $month2 = "November";
      if ($month == 12) $month2 = "December";
    }

    $format = str_replace ("D", "#1", $format); // Mo
    $format = str_replace ("l", "#2", $format); // Monday
    $format = str_replace ("M", "#3", $format); // Jan
    $format = str_replace ("F", "#4", $format); // Januari

    $result = adodb_date ($format, $time);

    $result = str_replace ("#1", $weekday1, $result);

    $result = str_replace ("#2", $weekday2, $result);
    $result = str_replace ("#3", $month1, $result);
    $result = str_replace ("#4", $month2, $result);

    return $result;
  }


//ericd 171208
//aanpassing N_VisualDate met $monthasnumber="", zodat maand als getal wordt getoond
//ericd 090109
//nog een: $nospaces, zodat er geen spaties tussen dag, maand en jaar staan.
//en... $leadingzero, als dag of maand < 10 een nul er voor.
//nette tabs opmaak uit notepad++ ziet er hier wat overdreven uit...

function N_VisualDate ($time=-1, $includetime="", $excludedayname="", $monthasnumber="", $nospaces="", $leadingzero="") {

  global $recordedtimestamp;
  $recordedtimestamp = $time;

  uuse ("multilang");
  if ($time==-1) $time=Time();

  $today   = adodb_getdate($time);
  $weekday = $today[wday];
  $day     = $today[mday];
  $month   = $today[mon];
  $year    = $today[year];


  if (ML_GetLanguage() == "nl") {

    if ($weekday == 0) $weekday = "Zo";
    if ($weekday == 1) $weekday = "Ma";
    if ($weekday == 2) $weekday = "Di";
    if ($weekday == 3) $weekday = "Wo";
    if ($weekday == 4) $weekday = "Do";
    if ($weekday == 5) $weekday = "Vr";
    if ($weekday == 6) $weekday = "Za";
    if ($weekday == 7) $weekday = "Zo";
  
  
    if(!$monthasnumber){

      if ($month == 1)  $month = "Jan";
      if ($month == 2)  $month = "Feb";
      if ($month == 3)  $month = "Mrt";
      if ($month == 4)  $month = "Apr";
      if ($month == 5)  $month = "Mei";
      if ($month == 6)  $month = "Jun";
      if ($month == 7)  $month = "Jul";
      if ($month == 8)  $month = "Aug";
      if ($month == 9)  $month = "Sep";
      if ($month == 10) $month = "Okt";
      if ($month == 11) $month = "Nov";
      if ($month == 12) $month = "Dec";
    }
  
    if($leadingzero && $day < 10) $day = "0".$day;
    if($leadingzero && $monthasnumber && $month < 10) $month = "0".$month;  

    if ($excludedayname) {
      if($nospaces) {
        $datestr = $day . $month . $year;
      } else {
        $datestr = $day . " " . $month . " " . $year;
      }
    } else {
      if($nospaces) {
        $datestr = $weekday . "," . $day . $month . $year;
      } else {
        $datestr = $weekday . ", " . $day . " " . $month . " " . $year;
      }
    }
    
    if ($includetime) {
      $datestr .= ($nospaces ? "": " ") . N_Date("H:i",$time);
    }
    
    return $datestr;

  } elseif (ML_GetLanguage() == "fr") {
    if ($weekday == 0) $weekday = "dim";
    if ($weekday == 1) $weekday = "lun";
    if ($weekday == 2) $weekday = "mar";
    if ($weekday == 3) $weekday = "mer";
    if ($weekday == 4) $weekday = "jeu";
    if ($weekday == 5) $weekday = "ven";
    if ($weekday == 6) $weekday = "sam";
    if ($weekday == 7) $weekday = "dim";
  
  
    if(!$monthasnumber){
      if ($month == 1)  $month = "jan";
      if ($month == 2)  $month = "f".html_entity_decode("&eacute;", ENT_COMPAT, 'ISO-8859-1')."b";
      if ($month == 2)  $month = "f".html_entity_decode("&eacute;", ENT_COMPAT, 'ISO-8859-1')."b";
      if ($month == 3)  $month = "mar";
      if ($month == 4)  $month = "avr";
      if ($month == 5)  $month = "mai";
      if ($month == 6)  $month = "jun";
      if ($month == 7)  $month = "jul";
      if ($month == 8)  $month = "ao".html_entity_decode("&ucirc;", ENT_COMPAT, 'ISO-8859-1');
      if ($month == 9)  $month = "sep";
      if ($month == 10) $month = "oct";
      if ($month == 11) $month = "nov";
      if ($month == 12) $month = "d".html_entity_decode("&eacute;", ENT_COMPAT, 'ISO-8859-1')."c";
    }
  
    if($leadingzero && $day < 10) $day = "0".$day;
    if($leadingzero && $monthasnumber && $month < 10) $month = "0".$month;  
    if(!$monthasnumber) {
      $datestr = "le ";
    } else {
      $datestr = "";
    }
    
    if ($excludedayname) {
      if($nospaces) {
        $datestr .= $day . $month . $year;
      } else {
        $datestr .= $le . $day . " " . $month . " " . $year;
      }
    } else {
      if($nospaces) {
        $datestr .= $weekday . " " . $day . $month . $year;
      } else {
        $datestr .= $le . $weekday . " " . $day . " " . $month . " " . $year;
      }
    }
    
    if ($includetime) {
      $datestr .= ($nospaces ? "": " ") . N_Date("H:i",$time);
    }
    
    return $datestr;
  } elseif (ML_GetLanguage() == "de") {
    if ($weekday == 0) $weekday = "So";
    if ($weekday == 1) $weekday = "Mo";
    if ($weekday == 2) $weekday = "Di";
    if ($weekday == 3) $weekday = "Mi";
    if ($weekday == 4) $weekday = "Do";
    if ($weekday == 5) $weekday = "Fr";
    if ($weekday == 6) $weekday = "Sa";
    if ($weekday == 7) $weekday = "So";
  
  
    if(!$monthasnumber){
      if ($month == 1) $month = "Jan";
      if ($month == 2) $month = "Feb";
      if ($month == 3) $month = "M".chr(228)."r";
      if ($month == 4) $month = "Apr";
      if ($month == 5) $month = "Mai";
      if ($month == 6) $month = "Jun";
      if ($month == 7) $month = "Jul";
      if ($month == 8) $month = "Aug";
      if ($month == 9) $month = "Sep";
      if ($month == 10) $month = "Okt";
      if ($month == 11) $month = "Nov";
      if ($month == 12) $month = "Dez";
    }
  
    if($leadingzero && $day < 10) $day = "0".$day;
    if($leadingzero && $monthasnumber && $month < 10) $month = "0".$month;  
    if (!$nospaces) $day = $day . ".";
    if (!$nospaces && $monthasnumber) $month = $month . ".";
    
    if ($excludedayname) {
      if($nospaces) {
        $datestr = $day . $month . $year;
      } else {
        $datestr = $day . " " . $month . " " . $year;
      }
    } else {
      if($nospaces) {
        $datestr = $weekday . " " . $day . $month . $year;
      } else {
        $datestr = $weekday . " " . $day . " " . $month . " " . $year;
      }
    }
    
    if ($includetime) {
      $datestr .= ($nospaces ? "": " ") . N_Date("H:i",$time);
    }
    
    return $datestr;
        
  } else {
    if ($weekday == 0) $weekday = "Su";
    if ($weekday == 1) $weekday = "Mo";
    if ($weekday == 2) $weekday = "Tu";
    if ($weekday == 3) $weekday = "We";
    if ($weekday == 4) $weekday = "Th";
    if ($weekday == 5) $weekday = "Fr";
    if ($weekday == 6) $weekday = "Sa";
    if ($weekday == 7) $weekday = "Su";
    
    
    if(!$monthasnumber) {
      
      if ($month == 1)  $month = "Jan";
      if ($month == 2)  $month = "Feb";
      if ($month == 3)  $month = "Mar";
      if ($month == 4)  $month = "Apr";
      if ($month == 5)  $month = "May";
      if ($month == 6)  $month = "Jun";
      if ($month == 7)  $month = "Jul";
      if ($month == 8)  $month = "Aug";
      if ($month == 9)  $month = "Sep";
      if ($month == 10) $month = "Oct";
      if ($month == 11) $month = "Nov";
      if ($month == 12) $month = "Dec";  
    }
    
    if($leadingzero && $day < 10) $day = "0".$day;
    if($leadingzero && $monthasnumber && $month < 10) $month = "0".$month;  
    
    if(!$monthasnumber && !$leadingzero){
      $end = "th";
      if ($day==1 or $day==21 or $day==31) $end = "st";
      if ($day==2 or $day==22) $end = "nd";
      if ($day==3 or $day==23) $end = "rd";
    } else {
      $end = "";
    }

    if ($excludedayname) {
      if($nospaces) {
        $datestr = $month . $day . $end . $year;
      } else {
        $datestr = $month . " " . $day . $end . " " . $year;
      }
    } else {
      if($nospaces) {
        $datestr = $weekday . "," . $month . $day . $end . $year;
      } else {
        $datestr = $weekday . ", " . $month . " " . $day . $end . " " . $year;
      }
    }
    
    if ($includetime) {
      $datestr .= ($nospaces ? "": " ") . N_Date("H:i",$time);
    }
    
    return $datestr;
    

  }
}
//end nwe N_VisualDate

//de oude

/*
  //ericd 171208: extra param: $monthasnumber="", zodat bv. Okt als 10 wordt weergegeven, als $monthasnumber = 1/true
  function N_VisualDate ($time=-1, $includetime="", $excludedayname="", $monthasnumber="") {  
    global $recordedtimestamp;
    $recordedtimestamp = $time;

    uuse ("multilang");
    if ($time==-1) $time=Time();

    $today   = adodb_getdate($time);
    $weekday = $today[wday];
    $day     = $today[mday];
    $month   = $today[mon];
    $year    = $today[year];

    if (ML_GetLanguage () != "nl") {

    if ($weekday == 0) $weekday = "Su";
    if ($weekday == 1) $weekday = "Mo";
    if ($weekday == 2) $weekday = "Tu";
    if ($weekday == 3) $weekday = "We";
    if ($weekday == 4) $weekday = "Th";
    if ($weekday == 5) $weekday = "Fr";
    if ($weekday == 6) $weekday = "Sa";
    if ($weekday == 7) $weekday = "Su";

    if(!$monthasnumber) {

       if ($month == 1)  $month = "Jan";
       if ($month == 2)  $month = "Feb";
       if ($month == 3)  $month = "Mar";
       if ($month == 4)  $month = "Apr";
       if ($month == 5)  $month = "May";
       if ($month == 6)  $month = "Jun";
       if ($month == 7)  $month = "Jul";
       if ($month == 8)  $month = "Aug";
       if ($month == 9)  $month = "Sep";
       if ($month == 10) $month = "Oct";
       if ($month == 11) $month = "Nov";
       if ($month == 12) $month = "Dec";

    }

    $end = "th";
    if ($day==1 or $day==21 or $day==31) $end = "st";
    if ($day==2 or $day==22) $end = "nd";
    if ($day==3 or $day==23) $end = "rd";

    if ($includetime) {
      if ($excludedayname) {
        return $month . " " . $day . $end . " " . $year . " " . N_Date("H:i",$time);
      } else {
        return $weekday . ", " . $month . " " . $day . $end . " " . $year . " " . N_Date("H:i",$time);
      }
    } else {
      if ($excludedayname) {
        return $month . " " . $day . $end . " " . $year;
      } else {
        return $weekday . ", " . $month . " " . $day . $end . " " . $year;
      }
    }

    } else {

    if ($weekday == 0) $weekday = "Zo";
    if ($weekday == 1) $weekday = "Ma";
    if ($weekday == 2) $weekday = "Di";
    if ($weekday == 3) $weekday = "Wo";
    if ($weekday == 4) $weekday = "Do";
    if ($weekday == 5) $weekday = "Vr";
    if ($weekday == 6) $weekday = "Za";
    if ($weekday == 7) $weekday = "Zo";

    if(!$monthasnumber) {

       if ($month == 1)  $month = "Jan";
       if ($month == 2)  $month = "Feb";
       if ($month == 3)  $month = "Mrt";
       if ($month == 4)  $month = "Apr";
       if ($month == 5)  $month = "Mei";
       if ($month == 6)  $month = "Jun";
       if ($month == 7)  $month = "Jul";
       if ($month == 8)  $month = "Aug";
       if ($month == 9)  $month = "Sep";
       if ($month == 10) $month = "Okt";
       if ($month == 11) $month = "Nov";
       if ($month == 12) $month = "Dec";

    }

    if ($includetime) {
      if ($excludedayname) {
        return $day . " " . $month . " " . $year . " " . N_Date("H:i",$time);
      } else {
        return $weekday . ", " . $day . " " . $month . " " . $year . " " . N_Date("H:i",$time);
      }
    } else {
      if ($excludedayname) {
        return $day . " " . $month . " " . $year;
      } else {
        return $weekday . ", " . $day . " " . $month . " " . $year;
      }
    }

    }
  }

*/

  function N_MyBareURL () 
  {
    global $ht_mode;
    global $ht_settings;
    if ($ht_mode) {
      $ret = "http://".$ht_settings["domain"]."/".$ht_settings["site"]."/".$ht_settings["object_id"].".php";
    } else {  
      if ($_SERVER["REDIRECT_URL"]) {
        $ret = N_CurrentProtocol() . getenv("HTTP_HOST") . $_SERVER["REDIRECT_URL"];
      } else {
        $ret = N_CurrentProtocol() . getenv("HTTP_HOST") . getenv("SCRIPT_NAME");
      }
    }
    return $ret;
  }

  function N_MyFullURL ()
  {
    global $ht_mode, $fake_query_string;
    $query_string = $fake_query_string;
    if (!$query_string) $query_string = getenv("QUERY_STRING");
    if (!$ht_mode && ((strlen($query_string . "")) > 0))
    {
      $url = N_MyBareURL () . "?" . $query_string;
    }
    else
    {
      $url = N_MyBareURL ();
    }
    return $url;

  }

  function N_MyVeryFullURL ($otherdomain="")
  {
    global $ht_mode;
    if ($ht_mode) return N_MyBareURL ();
    $url = N_MyFullURL ();
    global $REQUEST_METHOD, $_POST;
    if ($REQUEST_METHOD=="POST")
    {
      $parts = N_ExplodeURL ($url);
      foreach ($_POST as $arg => $val) {
        $parts["query"][$arg] = stripcslashes($val);
      }
      $url = N_ImplodeURL ($parts);
    }        
    return $url;
  }

  function N_TransferFile ($filename, $showname="", $attachment = false) 
  {
    // LF: Added $attachment parameter, which will trigger "Save as" dialog for all file types (even pdf, html etc)
    if (!$showname) $showname = $filename;
    $fsize = N_FileSize ($filename);
    $type = N_GetMimeType($showname);
    Header("Content-type: $type");
    $ext = strtolower (strrev(N_KeepBefore (strrev($showname), ".")));
    if (strpos($showname, ",") !== false) $showname = '"' . $showname . '"'; // because , indicates the end of an attribute. Some browsers just stop reading and get the filename wrong, others even refuse to download
    if($ext == 'xls' || $attachment)
      Header("Content-disposition: attachment; filename=" . $showname);
    else
      Header("Content-disposition: inline; filename=" . $showname);    
    Header("Content-length: " . $fsize );
    Header("Status: 200");
    
    $blocksize  = 1000000; 
    $pos = 0;
    $block = N_ReadFilePart ($filename, $pos, $blocksize);
    while (strlen ($block)) {
      $pos += strlen ($block);
      echo $block;
      N_Flush();
      $block = N_ReadFilePart ($filename, $pos, $blocksize);
    }
  }

  function N_TransferData ($data, $showname="")
  {
    if (!$showname) $showname = $filename;
    $type = N_GetMimeType($showname);
    Header("Content-type: $type");
    $ext = strtolower (strrev(N_KeepBefore (strrev($showname), ".")));
    if (strpos($showname, ",") !== false) $showname = '"' . $showname . '"'; // because , indicates the end of an attribute. Some browsers just stop reading and get the filename wrong, others even refuse to download
    if($ext=='xls')
      Header("Content-disposition: attachment; filename=" . $showname);
    else
      Header("Content-disposition: inline; filename=" . $showname); 
    // THB Header("Content-length: " . filesize(N_CleanPath($filename)));
    Header("Content-length: " . strlen($data) );
    // THB echo N_ReadFile ($filename);
    Header("Status: 200");
    echo $data;
  }

  function N_GetMimeType($filename) {
    $mimetype = array(
      'docm'       => 'application/vnd.ms-word.document.macroEnabled.12',
      'docx'       => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'dotm'       => 'application/vnd.ms-word.template.macroEnabled.12',
      'dotx'       => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
      'potm'       => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
      'potx'       => 'application/vnd.openxmlformats-officedocument.presentationml.template',
      'ppam'       => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
      'ppsm'       => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
      'ppsx'       => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
      'pptm'       => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
      'pptx'       => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
      'xlam'       => 'application/vnd.ms-excel.addin.macroEnabled.12',
      'xlsb'       => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
      'xlsm'       => 'application/vnd.ms-excel.sheet.macroEnabled.12',
      'xlsx'       => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'xltm'       => 'application/vnd.ms-excel.template.macroEnabled.12',
      'xltx'       => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
      'ez'         => 'application/andrew-inset',
      'hqx'        => 'application/mac-binhex40',
      'cpt'        => 'application/mac-compactpro',
      'doc'        => 'application/msword',
      'bin'        => 'application/octet-stream',
      'dms'        => 'application/octet-stream',
      'lha'        => 'application/octet-stream',
      'lzh'        => 'application/octet-stream',
      'exe'        => 'application/octet-stream',
      'class'      => 'application/octet-stream',
      'so'         => 'application/octet-stream',
      'dll'        => 'application/octet-stream',
      'oda'        => 'application/oda',
      'pdf'        => 'application/pdf',
      'ai'         => 'application/postscript',
      'eps'        => 'application/postscript',
      'ps'         => 'application/postscript',
      'smi'        => 'application/smil',
      'smil'       => 'application/smil',
      'mif'        => 'application/vnd.mif',
      'xls'        => 'application/vnd.ms-excel',
      'ppt'        => 'application/vnd.ms-powerpoint',
      'wbxml'      => 'application/vnd.wap.wbxml',
      'wmlc'       => 'application/vnd.wap.wmlc',
      'wmlsc'      => 'application/vnd.wap.wmlscriptc',
      'bcpio'      => 'application/x-bcpio',
      'vcd'        => 'application/x-cdlink',
      'pgn'        => 'application/x-chess-pgn',
      'cpio'       => 'application/x-cpio',
      'csh'        => 'application/x-csh',
      'dcr'        => 'application/x-director',
      'dir'        => 'application/x-director',
      'dxr'        => 'application/x-director',
      'dvi'        => 'application/x-dvi',
      'spl'        => 'application/x-futuresplash',
      'gtar'       => 'application/x-gtar',
      'hdf'        => 'application/x-hdf',
      'js'         => 'application/x-javascript',
      'skp'        => 'application/x-koan',
      'skd'        => 'application/x-koan',
      'skt'        => 'application/x-koan',
      'skm'        => 'application/x-koan',
      'latex'      => 'application/x-latex',
      'nc'         => 'application/x-netcdf',
      'cdf'        => 'application/x-netcdf',
      'sh'         => 'application/x-sh',
      'shar'       => 'application/x-shar',
      'swf'        => 'application/x-shockwave-flash',
      'sit'        => 'application/x-stuffit',
      'sv4cpio'    => 'application/x-sv4cpio',
      'sv4crc'     => 'application/x-sv4crc',
      'tar'        => 'application/x-tar',
      'tcl'        => 'application/x-tcl',
      'tex'        => 'application/x-tex',
      'texinfo'    => 'application/x-texinfo',
      'texi'       => 'application/x-texinfo',
      't'          => 'application/x-troff',
      'tr'         => 'application/x-troff',
      'roff'       => 'application/x-troff',
      'man'        => 'application/x-troff-man',
      'me'         => 'application/x-troff-me',
      'ms'         => 'application/x-troff-ms',
      'ustar'      => 'application/x-ustar',
      'src'        => 'application/x-wais-source',
      'xhtml'      => 'application/xhtml+xml',
      'xht'        => 'application/xhtml+xml',
      'zip'        => 'application/zip',
      'au'         => 'audio/basic',
      'snd'        => 'audio/basic',
      'mid'        => 'audio/midi',
       'midi'      => 'audio/midi',
      'kar'        => 'audio/midi',
      'mpga'       => 'audio/mpeg',
      'mp2'        => 'audio/mpeg',
      'mp3'        => 'audio/mpeg',
      'mp4'        => 'video/mp4',
      'aif'        => 'audio/x-aiff',
      'aiff'       => 'audio/x-aiff',
      'aifc'       => 'audio/x-aiff',
      'm3u'        => 'audio/x-mpegurl',
      'ram'        => 'audio/x-pn-realaudio',
      'rm'         => 'audio/x-pn-realaudio',
      'rpm'        => 'audio/x-pn-realaudio-plugin',
      'ra'         => 'audio/x-realaudio',
      'wav'        => 'audio/x-wav',
      'pdb'        => 'chemical/x-pdb',
      'xyz'        => 'chemical/x-xyz',
      'bmp'        => 'image/bmp',
      'gif'        => 'image/gif',
      'ief'        => 'image/ief',
      'jpeg'       => 'image/jpeg',
      'jpg'        => 'image/jpeg',
      'jpe'        => 'image/jpeg',
      'png'        => 'image/png',
      'tiff'       => 'image/tiff',
      'tif'        => 'image/tiff',
      'djvu'       => 'image/vnd.djvu',
      'djv'        => 'image/vnd.djvu',
      'wbmp'       => 'image/vnd.wap.wbmp',
      'ras'        => 'image/x-cmu-raster',
      'pnm'        => 'image/x-portable-anymap',
      'pbm'        => 'image/x-portable-bitmap',
      'pgm'        => 'image/x-portable-graymap',
      'ppm'        => 'image/x-portable-pixmap',
      'rgb'        => 'image/x-rgb',
      'xbm'        => 'image/x-xbitmap',
      'xpm'        => 'image/x-xpixmap',
      'xwd'        => 'image/x-xwindowdump',
      'igs'        => 'model/iges',
      'iges'       => 'model/iges',
      'msh'        => 'model/mesh',
      'mesh'       => 'model/mesh',
      'silo'       => 'model/mesh',
      'wrl'        => 'model/vrml',
      'vrml'       => 'model/vrml',
      'css'        => 'text/css',
      'html'       => 'text/html',
      'htm'        => 'text/html',
      'asc'        => 'text/plain',
      'txt'        => 'text/plain',
      'rtx'        => 'text/richtext',
      'rtf'        => 'text/rtf',
      'sgml'       => 'text/sgml',
      'sgm'        => 'text/sgml',
      'tsv'        => 'text/tab-separated-values',
      'wml'        => 'text/vnd.wap.wml',
      'wmls'       => 'text/vnd.wap.wmlscript',
      'etx'        => 'text/x-setext',
      'xsl'        => 'text/xml',
      'xml'        => 'text/xml',
      'mpeg'       => 'video/mpeg',
      'mpg'        => 'video/mpeg',
      'mpe'        => 'video/mpeg',
      'qt'         => 'video/quicktime',
      'mov'        => 'video/quicktime',
      'mxu'        => 'video/vnd.mpegurl',
      'avi'        => 'video/x-msvideo',
      'movie'      => 'video/x-sgi-movie',
      'ice'        => 'x-conference/x-cooltalk'
    );
    $ext = strtolower (strrev(N_KeepBefore (strrev($filename), ".")));
    $type = $mimetype[$ext];
    if (!$type) $type = "application/octet-stream";
    return $type;
  }


  function N_GzipFile ($outfile, $infile)
  {
    global $myconfig;
    $infile = escapeshellarg(N_ShellPath ($infile));
    $outfile = escapeshellarg(N_ShellPath ($outfile));

    $command = $myconfig["gzipcommand"]." -c $infile > $outfile";
    `$command`;
  }

  function N_GunzipFile ($outfile, $infile)
  {
    global $myconfig;
    $infile = escapeshellarg(N_ShellPath ($infile));
    $outfile = escapeshellarg(N_ShellPath ($outfile));

    $command = $myconfig["gunzipcommand"]." -c $infile > $outfile";
    `$command`;
  }
  
  function N_Gzip ($input)
  {
    if (!$input) return "";
    global $myconfig;
    if ($myconfig["hasgzlib"]!="yes") {
      $name = N_CleanPath ("html::/tmp/" . N_GUID());
      N_WriteFile ($name, $input);
      system ($myconfig["gzipcommand"]." \"$name\"");
      $ret = N_ReadFile ($name.".gz");
      N_ErrorHandling(false);
      @unlink ($name.".gz");
      N_ErrorHandling(true);
      return $ret;
    } else {
      return gzencode ($input);
    }
  }

  function N_Gunzip ($input)
  {
    if (!$input) return "";
    global $myconfig;
    if ($myconfig["hasgzlib"]!="yes") {
      $name = N_CleanPath ("html::/tmp/" . N_GUID());
      N_WriteFile ($name.".gz", $input);
      system ($myconfig["gunzipcommand"]." \"$name\"");
      $ret = N_ReadFile ($name);
      N_ErrorHandling(false);
      @unlink ($name);
      N_ErrorHandling(true);
      N_ErrorHandling(false);
      @unlink ($name.".gz");
      N_ErrorHandling(true);
      return $ret;
    } else {
      $eh = $name = N_CleanPath ("html::/tmp/" . N_GUID());
      $fd=fopen($eh,"w"); 
      fwrite($fd,$input); 
      fclose($fd); 
      $fd = gzopen ($eh, "r"); 
      while (1==1) { 
        $s=gzread($fd, 1048576);
        if ("$s" == "") { 
          break; 
        } 
        $str .= $s;
      } 
      gzclose ($fd);
      unlink($eh); 
      return $str;
    }
  }

  class N_VBrowser
  {
/* TEST CODE
$vb = new N_VBrowser();
$vb->setSite("dev.osict.com");
$vb->setLogon ("theid", "thepassword");

$vb->setProxy("", "");
echo $vb->GET ("/nkit/req.php", array ("a" => 1, "b" => 2));
echo $vb->POST ("/nkit/req.php", array ("a" => 1, "b" => 2));
echo $vb->UPLOAD ("/nkit/req.php", array ("a" => 1, "b" => 2), "thefile", "file.ext", "abcdef", "dbginfo");

$vb->setProxy("wwwproxy.xs4all.nl", "8080");
echo $vb->GET ("/nkit/req.php", array ("a" => 1, "b" => 2));
echo $vb->POST ("/nkit/req.php", array ("a" => 1, "b" => 2));
echo $vb->UPLOAD ("/nkit/req.php", array ("a" => 1, "b" => 2), "thefile", "file.ext", "abcdef", "dbginfo");

$vb->setProxy("wwwproxy.xs4all.nl", "8080", "test", "123");
echo $vb->GET ("/nkit/req.php", array ("a" => 1, "b" => 2));
echo $vb->POST ("/nkit/req.php", array ("a" => 1, "b" => 2));
echo $vb->UPLOAD ("/nkit/req.php", array ("a" => 1, "b" => 2), "thefile", "file.ext", "abcdef", "dbginfo");
*/
    var $site;
    var $port;
    var $cookies;
    var $id;
    var $password;
    var $timeout;
    var $referrer;
    var $fp;
    var $proxyserver;
    var $proxyport;
    var $proxyid;
    var $proxypwd;
    var $proxybypassfor;
    var $useragent; //gv 07-10-2011 BIBL-32 RSSfeed server need useragent

    function N_VBrowser ()
    {
      $this->timeout = 3600*4;
      global $myconfig;
      $this->proxyserver = $myconfig["proxyserver"];
      $this->proxyport = $myconfig["proxyport"];
      $this->proxyid = $myconfig["proxyid"];
      $this->proxypwd = $myconfig["proxypwd"];
      $this->proxybypassfor = $myconfig["proxybypassfor"];

    }
    function setProxy ($server, $port=8080, $id="", $pwd="", $bypassfor="")
    {
      $this->proxyserver = $server;
      $this->proxyport = $port;
      $this->proxyid = $id;
      $this->proxypwd = $pwd;
      $this->proxybypassfor = $bypassfor;
    }
    function setCookie ($name, $value)
    {
      $this->cookies[$name] = $value;
    }
    function setReferrer ($name)
    {
      $this->referrer = $name;
    }
    function setTimeout ($thetimeout)
    {
      $this->timeout = $thetimeout;
    }
    function setUserAgent ($theUserAgent)
    {
      $this->useragent = $theUserAgent;
    }
    function setSite ($thesite, $theport=80)
    {
      if (!strpos ($thesite, "sl://") && strpos ($thesite, ":")) {
        $theport = N_KeepAfter ($thesite, ":");
        $thesite = N_KeepBefore ($thesite, ":");
      }
      $this->site = $thesite;
      $this->port = $theport;
    }
    function setLogon ($theid, $thepassword)
    {
      $this->id = $theid;
      $this->password = $thepassword;
    }
    function Fetch ($type, $path, $params, $varname, $filename, $filecontent, $treading=false)
    {
      N_Debug ("N_VBrowser->Fetch ($type, $path, ...)", "N_VBrowser->Fetch");
      global $debug_fetch_show;
      if ($path=="") $path="/";

      // BUILD REQUEST
      if($params & $type!="UPLOAD" & $type!="POST"){
        reset($params);
        while (list ($key,$val) = each ($params)){
          $request .= $key;
          if ($val) $request .= '='.urlencode($val);
          $request .= '&';
        }
        if($request){
          $request = "?".substr($request,0,strlen($request)-1);
        }
        if($type == "URLPOST") $request = substr($request, 1); // remove first ? (it is a kind of POST, not a GET, after all)
      } else {
        $request = '';
      }
      if ($debug_fetch_show) {
        $dbg = "?dbg=".urlencode($debug_fetch_show);
        $debug_fetch_show = ""; 
      }
      if ($type=="UPLOAD") {
        $request = "-----------------------------7d129b29"."7a02fe\r\n";
        $request .= "Content-Disposition: form-data; name=\"$varname\"; filename=\"$filename\"\r\n";
        $request .= "Content-Type: text/plain\r\n\r\n";
        $request .= $filecontent."\r\n";
      }
      if ($type=="POST" || $type=="UPLOAD") {
        if($params){
          reset($params);
          while (list ($key,$val) = each ($params)){
            $request .= "-----------------------------7d129b29"."7a02fe\r\n";
            $request .= "Content-Disposition: form-data; name=\"$key\"\r\n\r\n";
            $request .= $val."\r\n"; 
          }
        }
        $request .= "-----------------------------7d129b29"."7a02fe--\r\n";
      }

      // DETERMINE PROXY STATUS
      if ($this->proxyserver) {
        $useproxy = true;
        if (is_array ($this->proxybypassfor)) {
          foreach ($this->proxybypassfor as $dummy => $me) {
            if ($me == $this->site) $useproxy = false;
          }
        }
      } else {
        $useproxy = false;
      }

      // TYPE (GET or POST)
      if ($useproxy) {
        if (($type=="GET") || ($type=="GetSize") || ($type=="GetServer")) {
          $header = "GET http://".$this->site."$path$request HTTP/1.0\r\n";
          $request = "";
        } else {
          $header = "POST http://".$this->site."$path$dbg HTTP/1.0\r\n";
        }
      } else {      
        if (($type=="GET") || ($type=="GetSize") || ($type=="GetServer")) {
          $header = "GET $path$request HTTP/1.0\r\n";
          $request = "";
        } else {
          $header = "POST $path$dbg HTTP/1.0\r\n";
        }
      }

      // CONTENT TYPE
      if ($type=="URLPOST") {
        $header .= "Content-type: application/x-www-form-urlencoded\r\n"; 
      }
      if ($type=="UPLOAD" || $type=="POST") {
        $header .= "Content-type: multipart/form-data; boundary=---------------------------7d129b29"."7a02fe\r\n";
      }

      // HOST
      // $header .= "Host: ".$this->site.":".$this->port."\r\n";
      $header .= "Host: ".$this->site."\r\n";

      // LENGTH
      if ($type=="UPLOAD" || $type=="POST" || $type=="URLPOST") {
        $header .= "Content-Length: " . strlen($request) . "\r\n";
      }

      // REFERRER
      if ("".$this->referrer!="") {
        $header.="Referer: ".$this->referrer."\r\n";
      }
      if ("".$this->useragent!="") {
        $header.= 'User-Agent: '. $this->useragent . "\r\n"; ;
      }

      // AUTHORIZATION
      if ($this->id) {
        $header .= "Authorization: Basic ".base64_encode($this->id.":".$this->password)."\r\n";
      }

      // COOKIES
      $first=1;
      if (is_array ($this->cookies)) {
        reset ($this->cookies);
        while (list ($key, $val) = each ($this->cookies)) {
          if ($first) $header .= "Cookie: "; else $header.="; ";
          $first=0;
          $header .= $key . "=" . $val;
        }
      }
      if (!$first) $header.="\r\n";

      // PROXY AUTHORIZATION
      if ($useproxy && $this->proxyid) {
        $header .= "Proxy-Authorization: Basic ".base64_encode($this->proxyid.":".$this->proxypwd) ."\r\n"; 
      }
      $header .= "\r\n";

      N_ErrorHandling (false);
      if ($useproxy) {
        $this->fp = fsockopen($this->proxyserver, $this->proxyport, $err_num, $err_msg, $this->timeout);

        N_Debug ("$this->fp = fsockopen($this->proxyserver, $this->proxyport, $err_num, $err_msg, $this->timeout);");
      } else {
        $this->fp = fsockopen($this->site, $this->port, $err_num, $err_msg, $this->timeout);
        N_Debug ("$this->fp = fsockopen($this->site, $this->port, $err_num, $err_msg, $this->timeout);");
      } 
      N_ErrorHandling (true);

      if(!$this->fp) return "";

      N_ErrorHandling(false);
      @socket_set_timeout($this->fp, $this->timeout);

      N_ErrorHandling(true);

      N_Debug ("<br>".str_replace(" ", "&nbsp;", str_replace(chr(10),"<br>",htmlentities($header.$request))));

      // SEND HEADER + REQUEST
      fputs($this->fp, $header . $request);

      $redirect = "";
      $content_length = 0;
      $server = "unknown";
      while(trim($line=fgets($this->fp, 1024)) != "")
      {
        N_Debug ("Response: ".$line);
        if (strpos ($line, "ontent-length:")) {
          $content_length = 0 + substr ($line, 16);
          N_Debug ("content_length = $content_length");
        }
        if (strpos ($line, "ontent-Length:")) {
          $content_length = 0 + substr ($line, 16);
          N_Debug ("content_length = $content_length");
        }
        if (strpos ($line, "erver:")) {
          $server = substr ($line, 7);
          N_Debug ("server = $server");
        }
        if (strpos($line, "Cookie:")) {
          $line = substr ($line, 12);
          if (strpos ($line, ";")) {
            $line = substr ($line, 0, strpos ($line, ";")+2);
          }
          $pos = strpos ($line, "=");
          if ($pos) {
            $name = substr ($line, 0, $pos);
            $value = substr ($line, $pos+1, strlen($line)-$pos-3);
            N_Debug ("Cookie[$name]=[$value]");
            $this->cookies[$name] = $value;
          }
        }
        if (strpos($line, "ocation:")==1) {
          if ($type=="GetURL") {
            $url = substr($line, 10);
          } else {
            $redirect = substr($line, 10);
          }
          N_Debug ("Redirect: $redirect");          
        }
      }

      if (!$treading) {
        if ($type=="GetSize") {
          $response = $content_length;
        } else if ($type=="GetURL") {
          $response = $url;
        } else if ($type=="GetServer") {
          $response = $server;
        } else {
          $len = 1;
          while (!feof($this->fp) AND $len) {
            $read = fread ($this->fp, 250000);
            $response .= $read;
            $len = strlen ($read);
            $totallen = strlen ($response);
            N_Debug ("Fetch ($type, $path, ...) fetched $len bytes, total $totallen bytes");
          }
        }
        fclose($this->fp);
      }

      if ($redirect && ($type!="GetServer")) {
        $response = N_GetPage ($redirect);
      }

      N_Debug ("Response size: ". strlen($response));
      return $response;
    }
    function RESULT ()
    {
      $len = 1;
      while (!feof($this->fp) AND $len) {
        $read = fread ($this->fp, 250000);
        $response .= $read;
        $len = strlen ($read);
        $totallen = strlen ($response);
        N_Debug ("Fetch ($type, $path, ...) fetched $len bytes, total $totallen bytes");
      }
      fclose($this->fp);
      return $response; 
    }
    function GET ($path, $params="")
    {
      N_Debug ("GET ($path, ", serialize($params));
      return $this->Fetch ("GET", $path, $params, "", "", "");
    }
    function GET_START ($path, $params="")
    {
      N_Debug ("GET_START ($path, ", serialize($params));
      return $this->Fetch ("GET", $path, $params, "", "", "", true);
    }
    function GetSize ($path, $params="")
    {
      N_Debug ("GetSite ($path, ", serialize($params));
      return $this->Fetch ("GetSize", $path, $params, "", "", "");
    }
    function GetURL ($path, $params="")
    {
      N_Debug ("GetURL ($path, ", serialize($params));
      return $this->Fetch ("GetURL", $path, $params, "", "", "");
    }
    function GetServer ($path, $params="")
    {
      N_Debug ("GetSite ($path, ", serialize($params));
      return $this->Fetch ("GetServer", $path, $params, "", "", "");
    }
    function POST ($path, $params="", $show="")
    {
      N_Debug ("POST ($path, ", serialize($params));
      global $debug_fetch_show;
      $debug_fetch_show = $show;      
      return $this->Fetch ("POST", $path, $params, "", "", "");
    }
    function URLPOST ($path, $params="", $show="")
    {
      N_Debug ("URLPOST ($path, ", serialize($params));
      global $debug_fetch_show;
      $debug_fetch_show = $show;      

      return $this->Fetch ("URLPOST", $path, $params, "", "", "");
    }
    function POST_START ($path, $params="")
    {
      N_Debug ("POST ($path, ", serialize($params));
      return $this->Fetch ("POST", $path, $params, "", "", "", true);
    }
    function UPLOAD ($path, $params, $varname, $filename, $filecontent, $show="")
    {
      global $debug_fetch_show;
      $debug_fetch_show = $show;      
      return $this->Fetch ("UPLOAD", $path, $params, $varname, $filename, $filecontent);
    }
    function UPLOAD_START ($path, $params, $varname, $filename, $filecontent, $show="")
    {
      global $debug_fetch_show;
      $debug_fetch_show = $show;      
      return $this->Fetch ("UPLOAD", $path, $params, $varname, $filename, $filecontent, true);
    }
  } // HD: classend

  function T_EO ($object) {
    print("<pre>".str_replace('#!ML!', '#!&#'.'8203;ML!', print_r($object,1))."</pre>"); // Add zero-width space in multilingual strings (to sabotage multilingual output filtering)
  }

  function T_Ref ($id) {
    $o = MB_Ref("ims_" . IMS_SuperGroupName() . "_objects", $id);
    T_EO($o);
  }

  function N_DEO ($object) {
    global $debug;
    if ($debug || N_Dev()) N_EO ($object);
  }

  function N_EO ($object) {
    if (N_PHP5()) {
      // 200903013: Bug found by Jack, workaround by LF.
      // Starting from PHP 5, the input encoding is automatically detected, so the ONLY way to set it is through a 
      // xml preamble (or http headers in some situations, but not when parsing strings...).
      // It also appears that instead of just mangling the characters a little, PHP5's xml parser has decided to CRASH 
      // when non-us-ascii iso-8859-1 characters appear in the input.
      // The output encoding defaults to utf8 starting from PHP 5.0.2 (this can be overruled by a parser option,
      // somewhere deep down in nkit/Xpath.class...)
      echo N_XML2HTML (utf8_decode(N_PrettyXML ('<?xml version="1.0" encoding="ISO-8859-1"?>'.N_Object2XML ($object))))."<br>";
    } else {
      echo N_XML2HTML (N_PrettyXML (N_Object2XML ($object)))."<br>";
    }
  }

  function N_PrettyXML ($xml) 
  {
    include_once (N_CleanPath ("html::/nkit/XPath.class.php"));
    $xml = str_replace ("&", "643876"."4857985_AMP", $xml);
    $xml = str_replace ("<char code='0A'/>", "643876"."4857985_0A", $xml);
    $xml = str_replace ("<char code='0D'/>", "643876"."4857985_0D", $xml);
    $xpath = new XPath;
    $xpath->importFromString($xml);
    $xml = $xpath->exportAsXML();
    $xml = str_replace ("643876"."4857985_AMP", "&", $xml);
    $xml = str_replace ("643876"."4857985_0A", "<char code='0A'/>", $xml);
    $xml = str_replace ("643876"."4857985_0D", "<char code='0D'/>", $xml);
    return $xml;
  }

  function N_Proper_wddx_serialize_vars ()
  {
    if (!function_exists ("wddx_serialize_vars")) return false;
    $test["a"] = "a";
    // check for some huge bug in some versions of PHP (e.g. 4.3.10 under Suse Linux and 4.4.3 under Windows)
    if (wddx_serialize_vars ("test")=="<wddxPacket version='1.0'><header/><data><struct><var name='test'><struct><var name='a'><string>a</string></var></struct></var></struct></data></wddxPacket>") {
      return true;
    } else {
      return false;
    }
  }

  function N_wddx_serialize_vars_needs_utffix() {
    // This function detects a bug that I havent been able to find in PHP's bug database.
    // I have seen the bug in PHP 5.2.0 and 5.2.6, but not in 5.1.6 or 5.2.9
    // The bug is: wddx_serialize_vars returns a string in which values, but NOT keys 
    // (of associative arrays), have been converted to UTF.
    // The expected behaviour is to convert nothing (ISO in, ISO out)

    if (!function_exists ("wddx_serialize_vars")) return false;
    if (!N_PHP5()) return false;
    global $N_wddx_serialize_vars_needs_utffix_tested, $N_wddx_serialize_vars_needs_utffix_result;
    if ($N_wddx_serialize_vars_needs_utffix_tested) return $N_wddx_serialize_vars_needs_utffix_result;

    $N_wddx_serialize_vars_needs_utffix_result = false;

    $test[chr(235)] = chr(235); // e-umlaut
    $expected = "<wddxPacket version='1.0'><header/><data><struct><var name='test'><struct><var name='".chr(235)."'><string>".chr(235)."</string></var></struct></var></struct></data></wddxPacket>";
    $wddx = wddx_serialize_vars("test");

    if ($wddx != $expected && N_wddx_serialize_vars_utffix($wddx) == $expected) {
      $N_wddx_serialize_vars_needs_utffix_result = true;
    }
  
    $N_wddx_serialize_vars_needs_utffix_tested = true;
    return $N_wddx_serialize_vars_needs_utffix_result;

  }

  function N_wddx_serialize_vars_utffix($xml) {
    // See N_wddx_serialize_vars_needs_utffix().
    return preg_replace_callback('!<string>[^<]+</string>!s',
                                 create_function('$matches', 'return N_UTF2HTML($matches[0]);'),
                                 $xml);
  }

  function N_Object2XML ($object)
  {
    if (N_Proper_wddx_serialize_vars ()) {
      $xml = wddx_serialize_vars("object");
      if (N_wddx_serialize_vars_needs_utffix()) $xml = N_wddx_serialize_vars_utffix($xml);
      $xml = substr ($xml, 68, strlen($xml)-103); 
      return $xml;
    } else {
      return "<string>".str_replace("<", "&lt;", str_replace(">", "&gt;", serialize ($object)))."</string>";
    }
  }

  function N_XML2Object ($xml)
  {
    if (function_exists ("wddx_deserialize")) {
      $xml = str_replace ("<number/>", "<number>0</number>", $xml);
      $xml = str_replace ("<string/>", "<string></string>", $xml);
      $xml = "<wddxPacket version='1.0'><header/><data><struct><var name=\"object\">" . $xml . "</var></struct></data></wddxPacket>";
      if (N_PHP5()) $xml = '<?xml version="1.0" encoding="ISO-8859-1"?>' . $xml; // LF20090527 http://bugs.php.net/bug.php?id=36775, or see rant at N_EO
      $array = wddx_deserialize($xml);
      if (N_PHP5()) $array = N_Utf8_decode($array); // The output encoding defaults to utf8 starting from PHP 5.0.2 (this can be overruled by a parser option, somewhere deep down in nkit/Xpath.class...)

      return $array["object"];
    } else {
      return unserialize ($xml);
    }
  }

  if(!function_exists("stripos"))
  {
    function stripos($haystack,$needle,$offset = 0)
    {
      return(strpos(strtolower($haystack),strtolower($needle),$offset));
    }
  }

  function N_XML2Object_Cleanup($input) // 
  {
    if (substr($input, 1, 3) == 'var')
    {
      $name = substr($input, 10);
      $name = preg_split('/["\']/', $name, -1, PREG_SPLIT_NO_EMPTY);
      $name = $name[0];
      $type = substr($input, strlen($name)+14, 4);
      $input = substr($input, 13+strlen($name));
      $input = substr($input, 0, strlen($input)-6);
      return Array("name"=>$name, "value"=>N_XML2Object_getResult($type, $input));
    }
  }

  function N_XML2Object_Special($input)
  {
    //Oracle with xml
    $input = N_Stri_replace($input, '<boolean value="true"/>', '<boolean>true</boolean>');
    $input = N_Stri_replace($input, '<boolean value="false"/>', '<boolean>false</boolean>');
    $input = N_Stri_replace($input, "<boolean value='true'/>", '<boolean>true</boolean>');

    $input = N_Stri_replace($input, "<boolean value='false'/>", '<boolean>false</boolean>');

    //Tamino
    $input = N_Stri_replace($input, '<boolean value="true"></boolean>', '<boolean>true</boolean>');
    $input = N_Stri_replace($input, '<boolean value="false"></boolean>', '<boolean>false</boolean>');
    $input = N_Stri_replace($input, "<boolean value='true'></boolean>", '<boolean>true</boolean>');
    $input = N_Stri_replace($input, "<boolean value='false'></boolean>", '<boolean>false</boolean>');
    $input = N_Stri_replace($input, '<char code="', "<char>" );
    $input = N_Stri_replace($input, '"/>', "</char>");
    $input = N_Stri_replace($input, '<char code=\'', "<char>" );
    $input = N_Stri_replace($input, '\'/>', "</char>");
    $input = N_Stri_replace($input, '<null/>', '<null></null>');
    $type = substr ($input, 1, 4);
    return N_XML2Object_getResult ($type, $input);
  }

  function N_XML2Object_Internal($input)
  {
    if (substr($input, 0, 7) == "<array ")
    {
      $pos1 = stripos($input, "'>");
      if (!$pos1) $pos1 = stripos($input, "\">");
      if ($pos1 === false) 
      {
        N_DIE ("XML decoding error");
      }
      else 
      {
        $input = substr($input, $pos1+2);
	$input = substr($input, 0, strlen($input)-8);
      }
      return N_XML2Object_getArray($test);
    }
    $buildedarray = Array();
    $test = substr($input, 7);
    $test = substr($test, 1, (strlen($test))-10);
    // N_Stri_replace chars and null !
    $vars = preg_split('/</', $test, -1, PREG_SPLIT_PREG_SPLIT_OFFSET_CAPTURE); 
    for($i = 1; $i < count($vars); $i++) 
    {
      $tags[$i] = "<".$vars[$i]; 
    }
    $vars = N_XML2Object_getVarChilds($tags);
    for ($c=0; $c<count($vars); $c++)
    {
      $result = N_XML2Object_Cleanup($vars[$c]);  
      if ($result)
      {
        $buildedarray[$result['name']] = $result['value'];
      }
    }
    return $buildedarray;
  }

  function N_XML2Object_getVarChilds($array)
  {
    $level = 1;
    $levelList = Array();
    $varList = Array();
    $left = 0;
    for ($i=1; $i<(count($array)+1); $i++)
    {
      if ($array[$i]{0}.$array[$i]{1} != '</')
      {
        $level++;  
        $levelList[$level]=$i;
      }
      else 
      {
        $tag = $array[$i]{0}. $array[$i]{2}. $array[$i]{3}. $array[$i]{4} . $array[$i]{5};
        if ($array[$levelList[$level]] != $tag)  
        {
          $level--;
        }
      }
      if ($level == 1 && $i != 1)
      {
        $temp = '';
        for ($left; $left<$i+1; $left++)
        {
          $temp .= $array[$left];
        }
        $varList[count($varList)] = $temp;
      }
    }
    return $varList;
  }

  function N_Stri_replace($text, $N_Stri_replace, $by)
  {
    $i = true;
    while ($i)
    {
      $pos1 = stripos($text, $N_Stri_replace);
      if ($pos1 === false) 
      {
        $i = FALSE;
      }
      else 
      {
        $before = substr($text, 0, $pos1);
        $text = substr($text, $pos1+strlen($N_Stri_replace));
        $text = $before . $by . $text;
      }
    }
    return $text;
  }

  function N_XML2Object_replaceChars($input)
  {
    $i = TRUE;
    while ($i)
    {
      $pos1 = stripos($input, "<char>");
      if ($pos1 === false) 
      {
        $i = FALSE;
      }
      else 
      {
        $before = substr($input, 0, $pos1);
        $text = substr($input, $pos1+strlen("<char>"));
	$pos2 = stripos($text, "</char>");
        $code = substr($text, 0, $pos2);
	$text = substr($text, $pos2+strlen("</char>"));
	$input = $before . chr(hexdec($code)) . $text;
      }
    }
    return $input;
  }

  function N_XML2Object_getArray($input)
  {
    $vars = preg_split('/</', $input, -1, PREG_SPLIT_PREG_SPLIT_OFFSET_CAPTURE);   
    for($i = 1; $i < count($vars); $i++) 
    {
      $tags[$i] = "<".$vars[$i]; 
    }
    $vars = N_XML2Object_getVarChilds($tags);
    $array = Array();
    foreach($vars as $row)
    {
      $type = substr($row, 1, 4);
      $array[count($array)] = N_XML2Object_getResult($type, $row);
    }
    return $array;
  }


  function N_XML2Object_getString($input)

  {
    $val = substr($input, 8);
    $val = preg_split('</string>', $val, -1, PREG_SPLIT_NO_EMPTY); 
    $val = substr($val[0], 0, strlen($val[0])-1);
    $val = N_XML2Object_replaceChars($val);
    $val = N_Stri_replace($val, '&amp;', '&');
    $val = N_Stri_replace($val, '&gt;', '>');
    $val = N_Stri_replace($val, '&lt;', '<');
    return $val;
  }

  function N_XML2Object_getNumb($input)
  {
    $val = substr($input, 8);
    $val = preg_split('</number>', $val, -1, PREG_SPLIT_NO_EMPTY); 
    $val = substr($val[0], 0, strlen($val[0])-1);
    $val = $val *1;
    return $val;
  }

  function N_XML2Object_getBoolean($input)
  {
    $val = substr($input, 9);
    $val = preg_split('</boolean>', $val, -1, PREG_SPLIT_NO_EMPTY); 
    $val = substr($val[0], 0, strlen($val[0])-1);
    if (strtoupper($val) == "TRUE")
      $val = TRUE;
    else 
      $val = FALSE;
    return $val;
  }

  function N_XML2Object_getResult($type, $input)
  {
    if ($type == 'stri')
    {
      return N_XML2Object_getString($input);
    }
    else if ($type == 'stru')
    {
      return N_XML2Object_Internal($input);
    }
    else if ($type == 'numb')
    {
      return N_XML2Object_getNumb($input);
    }
    else if ($type == 'bool')
    {
      return N_XML2Object_getBoolean($input, $distance);
    }
    else if ($type == 'arra')
    {
      $pos1 = stripos($input, '">');
      if (!$pos1) $pos1 = stripos($input, '\'>');
      if ($pos1 === false) 
      {
      }
      else 
      {
        $input = substr($input, $pos1+2);
        $len = strlen($input) -6;
        $test = substr($input, 0, $len);
      }
      $x = N_XML2Object_getArray($test);
      return $x;
    }
    else if ($type == 'null')
      return null;
    else if ($type == 'date')
    {
      $val = substr($input, 10);
      $val = preg_split('</dateTime>', $val, -1, PREG_SPLIT_NO_EMPTY); 
      $val = substr($val[0], 0, strlen($val[0])-1);
      return strtotime($val);
    }
    else
    {
      // type which doesn't exist!
      return null;
    }
  }

  function N_RawXML2Object ($rawxml) {
    global $rawxml_location, $rawxml_global;
    $rawxml_global = array();
    $rawxml_location = "";
    $xml_parser = xml_parser_create();
    xml_set_element_handler($xml_parser, "rawxml_startElement", "rawxml_endElement");
    xml_set_character_data_handler($xml_parser, "rawxml_character");
    if (!xml_parse($xml_parser, $rawxml, 1)) {
      die(sprintf("XML error: %s at line %d",
                xml_error_string(xml_get_error_code($xml_parser)),
                xml_get_current_line_number($xml_parser)));
    }
    xml_parser_free($xml_parser);
    $retval = $rawxml_global; // prevent referencing side effects
    return $retval;
  }

  function rawxml_startElement($parser, $name, $attrs) {
    global $rawxml_location, $rawxml_global;
    $name = strtolower ($name);    
    $rawxml_location .= "['$name']";
    eval ("\$index = \$rawxml_global".$rawxml_location."['?'];");
    $index++;
    N_Debug ("\$rawxml_global".$rawxml_location."['?'] = $index;");
    eval ("\$rawxml_global".$rawxml_location."['?'] = $index;");
    $rawxml_location .= "[$index]";
    reset ($attrs);
    while (list ($key, $value) = each ($attrs)) {
      $key = strtolower ($key);    
      N_Debug ("\$rawxml_global".$rawxml_location."['this']['$key'] = $value;");
      eval ("\$rawxml_global".$rawxml_location."['this'][\$key] = \$value;");
    }
  }

  function rawxml_endElement($parser, $name) {
    global $rawxml_location;
    $name = strtolower ($name);    
    $pos = strrpos ($rawxml_location, "[");
    $rawxml_location = substr ($rawxml_location, 0, $pos);    
    $pos = strrpos ($rawxml_location, "[");
    $rawxml_location = substr ($rawxml_location, 0, $pos);    
  }

  function rawxml_character ($parser, $data) {
  }

  function N_XML2HTML ($xml)
  {
    $result = htmlentities($xml);
    $result = str_replace (chr(13).chr(10), "<br />", $result); // Windows
    $result = str_replace (chr(10), "<br />", $result); // Linux
    $result = str_replace (chr(13), "<br />", $result); // Mac
    $result = str_replace ("  ", " &nbsp;", $result);
    return $result;
  }

  function N_DetectPHPCodeFields () // prevent OpenIMS from breaking PHP code in form fields
  {
    $result = false;
    $encspec = $_REQUEST["encspec"];
    if ($encspec) {
      $data = SHIELD_Decode ($encspec, true);
      if (is_array($data)) {

        // maatwerk (inrichting)
        if (is_array($data["input"])) {
          if ($data["input"]["type"]) { // LF20101015: deleted "id" so that it will also work for new components. Deleted "sgn" so that it will work for internal (core) components
            if (is_array($data["metaspec"])) {
              if (is_array($data["metaspec"]["fields"])) {
                foreach ($data["metaspec"]["fields"] as $name => $dummy) {
                  if (substr ($name, 0, 5) == "code_") $result = true;
                  if ($name == "code") $result = true;
                }
              }
            }
          }
        }

        // velden
        if (is_array($data["input"])) {
          if ($data["input"]["supergroupname"] && $data["input"]["field"]) {
            if (is_array($data["metaspec"])) {
              if (is_array($data["metaspec"]["fields"])) {
                foreach ($data["metaspec"]["fields"] as $name => $dummy) {
                  if ($name == "validationcode") $result = true; // validatie
                  if ($name == "viewrtf") $result = true; // code veld
                  if ($name == "calc") $result = true; // auto veld
                }
              }
            }
          }
        }

        // workflow / process
        if (is_array($data["input"])) {
          if ($data["input"]["col"] && $data["input"]["id"]) {
            if (is_array($data["metaspec"])) {
              if (is_array($data["metaspec"]["fields"])) {
                foreach ($data["metaspec"]["fields"] as $name => $dummy) {
                  if ($name == "eventcode") $result = true; // workflow event AND process event
                  if ($name == "postphp") $result = true; // process dossier php
                  if ($name == "viewphp") $result = true; // process advanced view
                  if ($name == "deletephp") $result = true; // process advanced delete
                  if ($name == "postpostcode") $result = true; // process advanced form handling
                }
              }
            }
          }
        }

      }
    }
    return $result;
  }

  function N_HtmlEntities($input, $strict = true, $formatting = false)
  {
    // Htmlentities without the "double" encoding problem.
    // strict. if true, prevent only double encoding of numeric entities (those creating by N_UTF2HTML)
    //         if false, also prevent double encoding of non-numeric entities entered by the user
    // formatting. if true, treat newlines and spaces the same way as N_XML2HTML.
    //             if false, do nothing about newline and spaces.

//    if(is_array($input)) {
//      return ML("Datalijst", "Data list"); // something else? maybe something to view the array data?
//    }

    $result = htmlentities($input, ENT_QUOTES); // encode both single and double quotes
    $result = str_replace('/', '&'. '#047;', $result); // OWASP recommends to escape the slash too
   
    if (!N_DetectPHPCodeFields ()) {
      if ($strict) {
        $result = str_replace("&amp;#", "&#", $result);
      } else {
        $result = preg_replace("/&amp;(#?[a-zA-Z0-9]+);/", "&$1;", $result);
      }
    } else {
      // Code Fields: sabotage FORMS_ML_Filter so that multilingual strings remain multilingal.
      // This relies on using numeric HTML entities, so it needs to be done after htmlentities.
      $result = str_replace('#!ML!', '&#'.'35;!ML!', $result);
    }

    if ($formatting) {
      $result = str_replace (chr(13).chr(10), "<br />", $result); // Windows
      $result = str_replace (chr(10), "<br />", $result); // Linux
      $result = str_replace (chr(13), "<br />", $result); // Mac
      $result = str_replace ("  ", " &nbsp;", $result);
    }
   
    return $result;
  }

  function N_Random ($spec1="Q", $spec2="Q") // ()=0<x<1   (6)=1..6   (3,5)=3..5
  {
    
    global $initialized_N_Random;
    if (!$initialized_N_Random) {
      list($usec, $sec) = explode(' ', microtime());
      mt_srand((getmypid() + $sec + $usec * 1000000) % 1000000000);
      $initialized_N_Random = true;
    }
    if ($spec2=="Q") {
      if ($spec1=="Q") {
         return mt_rand(1,999999999)/1000000000;
      } else {
         return mt_rand (1, $spec1);    
      }
    } else {
      return mt_rand ($spec1, $spec2);
    }
  }

  function N_EscapeCommandString ($command)
  {
    $command = addslashes ($command);
    $command = str_replace ("\$", "\\\$", $command);
    return $command;
  }

  function N_DeleteScedule ($scedulekey)
  {
    MB_Delete ("scedule_".N_CurrentServer (), $scedulekey);
    MB_Delete ("fast_scedule_".N_CurrentServer (), $scedulekey);
  }

  function N_AddModifyPreciseScedule ($scedulekey, $when, $what, $input="")
  {
    N_AddModifyScedule ($scedulekey, $when, $what, $input, true);
  }

  function N_AddModifyScedule ($scedulekey, $when, $what, $input="", $precise=false)
  {
    N_Debug ("N_AddModifyScedule ($scedulekey, $when, $what, ".serialize($input).")");
    
    if (N_OpenIMSCE()) N_SuperQuickScedule (N_CurrentServer(), $what, $input);
  }

  function N_QuickScedule ($where, $what, $input="")
  {
    N_Debug ("N_QuickScedule ($where, $what, ...)");
    
    if (N_OpenIMSCE()) N_SuperQuickScedule ($where, $what, $input);
  }

  function N_SuperQuickScedule ($where, $what, $input="")
  {
    N_Debug ("N_SuperQuickScedule ($where, $what, ...)");
    if (!$where) $where=N_CurrentServer();
    global $superquickscedule, $superquickscedule_todo;  
    $superquickscedule++;
    $superquickscedule_todo[$where][$superquickscedule]["what"]=$what;
    $superquickscedule_todo[$where][$superquickscedule]["input"]=$input;
  }

  function N_ExitQuickScedule ()
  {
    N_Debug ("N_ExitQuickScedule ()");
    global $exit_quickscedule;
    $exit_quickscedule = true;
    global $quickscedule, $quickscedule_todo;
    global $superquickscedule, $superquickscedule_todo;    
    if ($superquickscedule) {
      $sqtodo = $superquickscedule_todo;
      $superquickscedule_todo = array();
      $superquickscedule = 0;
      reset ($sqtodo);
      while (list($server, $todo)=each($sqtodo)) {
        if ($server==N_CurrentServer()) {

          N_ExecQuickScedule ($todo);

        } else {
          URPC ($server, "N_ExecQuickScedule (\$input);", $todo);
        }
      }
      if ($superquickscedule) { // scedule items might create scedule items, we allow this for a bit
        $sqtodo = $superquickscedule_todo;
        $superquickscedule_todo = array();
        $superquickscedule = 0;
        reset ($sqtodo);
        while (list($server, $todo)=each($sqtodo)) {
          if ($server==N_CurrentServer()) {
            N_ExecQuickScedule ($todo);
          } else {
            URPC ($server, "N_ExecQuickScedule (\$input);", $todo);
          }
        }
        if ($superquickscedule) { // scedule items might create scedule items, we allow this for a bit
          $sqtodo = $superquickscedule_todo;
          $superquickscedule_todo = array();
          $superquickscedule = 0;
          reset ($sqtodo);
          while (list($server, $todo)=each($sqtodo)) {
            if ($server==N_CurrentServer()) {
              N_ExecQuickScedule ($todo);
            } else {
               URPC ($server, "N_ExecQuickScedule (\$input);", $todo);
            }
          }
        }
      }  
    }
    if ($quickscedule) {
      reset($quickscedule_todo);
      while (list($server, $todo)=each($quickscedule_todo)) {
        N_AddModifyScedule ("", time()+10, 'URPC ("'.$server.'", "N_ExecQuickScedule (\$input);", $input);', $todo);
      }
    }
  }

  function N_ExecQuickScedule ($todo)
  {
    reset($todo);
    while (list(,$rec)=each($todo)) {
      $input = $rec["input"];
      eval ($rec["what"]);
    }
  }

  function N_DebugLog ($something)
  {
    global $N_Start;
    $N_Current = N_MicroTime(); 
    N_QuickAppendFile ("html::tmp/debug.log", "(".(int)(($N_Current-$N_Start) * 1000)." ms) ".$something.chr(13).chr(10)); 
  }

  function N_GiveMD5BfromData(&$data_in) {
    $datalen = strlen($data_in);
    $md5b= "";
    for($i=0; $i< ceil($datalen/10000); $i++) {
      $start = $i * 10000;
      $number = $datalen - $start;
      if ($number>10000) $number = 10000;
      $block = substr ($data_in, $start, $number);
      $md5b = md5($md5b.$block);
    }
    if (!$datalen) {
      $md5b = "d41d8cd98f00b204e9800998ecf8427e";
    } else {
       if ($datalen % 10000 == 0) $md5b = md5 ($md5b); // flawed by design
    }
    return $md5b."_".$datalen;
  }

  function N_Fileinfo ($filename, $refresh=false)
  {
    $ci = N_MTCacheInfo ($filename);
    if ($ci) {
      if (!file_exists ($ci["cachepath"])) {
        N_TrueCopyFile ($ci["cachepath"], $ci["truepath"]);
      }
      return N_DoFileinfo ($ci["cachepath"], $refresh);
    } else {
      return N_DoFileinfo (N_CleanPath ($filename), $refresh);
    }
  } 

  function N_DoFileinfo ($path, $refresh=false)
  {
    N_Debug ("N_Fileinfo ($path, $refresh)");
    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StartTimer ("read");
    $path = N_CleanPath ($path);
    if (is_file ($path)) {
      clearstatcache ();
      $ft = @filemtime ($path);
      $fs = N_Filesize ($path);
      $key = DFC_Key (N_VirtualCleanPath ($path)."#".$ft."#".$fs."#v6");
      if (DFC_Exists ($key) && !$refresh) {        
        $data = DFC_Read ($key);
        if (substr ($data["md5b"], -4)=="0000") { // special case due to flawed by design nature of mdb5 (we fixed it for a short while)
          if (!$data["doublechecked"]) {
            $data["doublechecked"] = "yes";
            $data["md5b"] = N_FileMD5B($path);
            $data["ft"] = $ft;
            DFC_Write ($key, $data);
          }
        }
      } else {
        $data["md5b"] = N_FileMD5B($path);
        $data["ft"] = $ft;
        DFC_Write ($key, $data);
      }
      $result["md5b"] = $data["md5b"];
      $result["age"] = time() - $data["ft"];
      if ($result["age"] < 0) { // something went wrong (e.g. system date in the future), repair it
        N_Touch ($path, time()-7*24*3600);
        $result["age"] = 7*24*3600;
      }
    } else {
      $result["md5b"] = "d41d8cd98f00b204e9800998ecf8427e_0";
      $result["age"] = 1000000000;
    } 
    global $myconfig; if ($myconfig["performancelogging"] == "yes") N_StopTimer ("read");
    return $result;
  }

  function N_FileMD5B($file) // *block* md5, not normal md5
  {
    return VVFILE_FileMD5B($file);
  }

  function N_NowFileMD5B($file) // *block* md5, not normal md5
  {
    // Note: not cached. Dont use this, use N_FileInfo instead, please.
    $file = N_CleanPath($file);
    $fs = filesize ($file);
    $fdsafe = fopen($file, 'rb');
    $md5b= "";
    $len = 0;
    if ($fdsafe) {
      while (!feof($fdsafe)) {
        $block = fread ($fdsafe, 10000);
        $md5b = md5($md5b.$block); // flawed by design
        $len += strlen($block);
      }
      fclose($fdsafe);
    }
    if (!$len) $md5b = "d41d8cd98f00b204e9800998ecf8427e";
    $result = $md5b."_".$len;
    return $result;
  }

  function N_Subs ($path)

  {
    $path = N_ProperPath ($path);
    $dir = array();
    N_ErrorHandling (false);
    $d = dir($path);
    N_ErrorHandling (true);
    if (!$d) return "";
    while (false !== ($entry = $d->read())) {
      if (is_dir ($path.$entry) && $entry!="." && $entry!="..") {
        $dir[$entry ] = $path.$entry;
      }
    }
    $d->close();
    return $dir;
  }

  function N_Files ($path)
  {
    N_Debug ("N_Dirs ($path)"); 
    $path = N_ProperPath ($path);
    $dir = array();
    N_ErrorHandling (false);
    $d = dir($path);
    N_ErrorHandling (true);
    if (!$d) return array();
    while (false !== ($entry = $d->read())) {
      if (is_file ($path.$entry)) {
        $result[$entry]=$entry;
      }
    }
    $d->close();
    return $result;
  }


  function N_Dirs ($path)
  {
    N_Debug ("N_Dirs ($path)"); 
    $path = N_ProperPath ($path);
    $dir = array();
    N_ErrorHandling (false);
    $d = dir($path);
    N_ErrorHandling (true);
    if (!$d) return array();
    while (false !== ($entry = $d->read())) {
      if (is_dir ($path.$entry) && $entry!="." && $entry!="..") {
        $result[$entry]=$entry;
      }
    }
    $d->close();
    return $result;
  }

  function N_QuickTree ($path, $relpath="/", $includesubs=true)
  {
    N_Debug ("N_QuickTree ($path)"); 
    $path = N_ProperPath ($path);
    $dir = array();
    N_ErrorHandling (false);
    $d = dir($path);
    N_ErrorHandling (true);
    if (!$d) return "";
    while (false !== ($entry = $d->read())) {
      if ($includesubs && is_dir ($path.$entry) && $entry!="." && $entry!="..") {
        if ($path) {
          $dir = N_array_merge ($dir, N_QuickTree ($path.$entry, $relpath.$entry."/", true));
        } else {
          $dir = N_array_merge ($dir, N_QuickTree ($entry, $relpath.$entry."/", true));
        }
      } else if (is_file ($path.$entry)) {
        clearstatcache ();
        $dir[N_VirtualCleanPath ($path.$entry)]["age"] = time() - @filemtime ($path.$entry);
        $dir[N_VirtualCleanPath ($path.$entry)]["relpath"] = $relpath;
        $dir[N_VirtualCleanPath ($path.$entry)]["filename"] = $entry;
      }
    }
    $d->close();
    return $dir;
  }

  function N_Tree ($path, $relpath="/", $includesubs=true)
  {
    N_Debug ("N_Tree ($path)"); 
    $path = N_ProperPath ($path);
    $dir = array();
    N_ErrorHandling (false);
    $d = dir($path);
    N_ErrorHandling (true);
    if (!$d) return "";
    while (false !== ($entry = $d->read())) {
      if ($includesubs && is_dir ($path.$entry) && $entry!="." && $entry!="..") {
        if ($path) {
          $dir = N_array_merge ($dir, N_Tree ($path.$entry, $relpath.$entry."/", true));
        } else {
          $dir = N_array_merge ($dir, N_Tree ($entry, $relpath.$entry."/", true));
        }
      } else if (is_file ($path.$entry)) {
        $fileinfo = N_Fileinfo ($path.$entry);
        $dir[N_VirtualCleanPath ($path.$entry)]["md5b"] = $fileinfo["md5b"];
        $dir[N_VirtualCleanPath ($path.$entry)]["age"] = $fileinfo["age"];
        $dir[N_VirtualCleanPath ($path.$entry)]["relpath"] = $relpath;
        $dir[N_VirtualCleanPath ($path.$entry)]["filename"] = $entry;
      }
    }
    $d->close();
    return $dir;
  }

  function N_ProperPath ($dir)
  {
    $dir = N_CleanPath ($dir);
    if (substr($dir, strlen($dir)-1,1)!="/") $dir.="/";
    return $dir;
  }

  function N_CopyFile ($dest, $src)
  {
    return VVFILE_CopyFile ($dest, $src);
  }

  function N_NowCopyFile ($dest, $src)
  { // #MT#
    if(!N_SamePathI($dest, $src)) {
      $ci = N_MTCacheInfo ($src);
      if ($ci) {
        if (!file_exists ($ci["cachepath"])) {
          N_TrueCopyFile ($ci["cachepath"], $ci["truepath"]);
        }
        $src = $ci["cachepath"];
      }
      $ci = N_MTCacheInfo ($dest);
      if ($ci) {
        $result = N_TrueCopyFile ($ci["cachepath"], $src);
        if (!file_exists ($ci["truepath"])) N_TrueWriteFile ($ci["truepath"], "*"); // dummy file
        N_AddModifyPreciseScedule ("MT".$ci["virtualpath"], time()+1, 'N_TrueCopyFile ($input["truepath"], $input["cachepath"]);', $ci);
        return $result;
      } else {
        return N_TrueCopyFile ($dest, $src);
      }

    }
  }


  function N_TrueCopyFile ($dest, $src)
  {
    if(!N_SamePathI($dest, $src)) {
      $blocksize = 2000000;
      N_Debug ("N_TrueCopyFile ($dest, $src)");
      if (N_TrueFileSize ($src) > $blocksize) {
        N_TrueWriteFile ($dest, N_TrueReadFilePart ($src, 0, $blocksize));
        $todo = N_TrueFileSize ($src) - $blocksize;
        $offset = $blocksize;
        while ($todo) {
          $block = $blocksize;
          if ($block > $todo) $block = $todo;
          N_TrueAppendFile ($dest, N_TrueReadFilePart ($src, $offset, $block));
          $offset += $block;
          $todo -= $block;
        }
      } else {
        N_TrueWriteFile ($dest, N_TrueReadFile ($src));
      }
    }
  }

  function N_CopyDir ($dest, $src, $postcode="")  // !!! DANGER !!!, everything in $dest will be deleted !!!
  { 
    if(!N_SamePathI($dest, $src)) {
      N_Debug ("N_CopyDir ($dest, $src)");
      $dest = N_ProperPath ($dest);
      $src  = N_ProperPath ($src);
      // try to prevent accidents
      if (strlen($dest)<5 || $dest==getenv("DOCUMENT_ROOT") || $dest==getenv("DOCUMENT_ROOT")."/") N_DIE ("N_CopyDir ($dest, $src)");
      $dest_tree = N_QuickTree ($dest);
      $src_tree = N_QuickTree ($src);
      if (is_array ($dest_tree)) reset($dest_tree);
      if (is_array ($dest_tree)) while (list ($file, $info)=each($dest_tree)) {
        N_DeleteFile (N_CleanPath ($file));
      }
      if (is_array ($src_tree)) reset($src_tree);
      if (is_array ($src_tree)) while (list ($file, $info)=each($src_tree)) {
        $destfile = str_replace (N_ProperPath ($src), N_ProperPath ($dest), N_CleanPath ($file));
        N_CopyFile ($destfile, $file);
        if ($postcode) eval ($postcode);
      }
    }
  }

  function N_CompareDirs ($dir1, $dir2)
  {
    $dir1 = N_ProperPath ($dir1);
    $dir2 = N_ProperPath ($dir2);
    $dir1_tree = N_Tree ($dir1);
    $dir2_tree = N_Tree ($dir2);
    $same = true;
    if (is_array ($dir1_tree)) reset($dir1_tree);
    if (is_array ($dir1_tree)) while (list ($file1, $info)=each($dir1_tree)) {
      $file2 = str_replace ($dir1, $dir2, $file1);
      if ($dir1_tree[$file1]["md5b"] != $dir2_tree[$file2]["md5b"]) $same = false;
    }
    if (is_array ($dir2_tree)) reset($dir2_tree);
    if (is_array ($dir2_tree)) while (list ($file2, $info)=each($dir2_tree)) {
      $file1 = str_replace ($dir2, $dir1, $file2);
      if ($dir1_tree[$file1]["md5b"] != $dir2_tree[$file2]["md5b"]) $same = false;
    }
    N_Debug ("N_CompareDirs ($dir1, $dir2) returns [$same]");
    return $same;
  }

  function N_Replace ($old, $new, $haystack) {

    return N_preg_replace('/'.quotemeta($old).'/i', $new, $haystack);
  }

  function N_String2HTML($text)
  {
    $chars = preg_split('#(<\/?)(\w+)([^>]*>)#mis', $text, -1, PREG_SPLIT_NO_EMPTY);
    for($i=0;$i<count($chars);$i++) {
      $text = str_replace($chars[$i],htmlentities($chars[$i]),$text);
    }
    $text = str_replace (chr(128), "&euro;", $text);
    return(stripslashes($text));
  }

  function N_HTML2String($text)
  {
    $chars = chr(138).chr(140).chr(142).chr(154).chr(156).chr(158).chr(159).chr(165).chr(181).chr(192).chr(193).chr(194).chr(195).chr(196).chr(197).chr(198).chr(199).chr(200).chr(201).chr(202).chr(203).chr(204).chr(205).chr(206).chr(207).chr(208).chr(209).chr(210).chr(211).chr(212).chr(213).chr(214).chr(216).chr(217).chr(218).chr(219).chr(220).chr(221).chr(223).chr(224).chr(225).chr(226).chr(227).chr(228).chr(229).chr(230).chr(231).chr(232).chr(233).chr(234).chr(235).chr(236).chr(237).chr(238).chr(239).chr(240).chr(241).chr(242).chr(243).chr(244).chr(245).chr(246).chr(248).chr(249).chr(250).chr(251).chr(252).chr(253).chr(255);
    for($i=0;$i<strlen($chars);$i++) {
      $text=str_replace(htmlentities(substr($chars,$i,1)), substr($chars,$i,1),$text);
    }
    $text = str_replace ("&euro;", chr(128), $text);
    return $text;
  }

  function N_Lock ($spec, $timetowait=15) // timetowait is in seconds
  {
    global $N_Lock_admin, $disablelocking;
    if ($disablelocking) return;
    $slot = md5("lock".serialize($spec));
    if ($N_Lock_admin[$slot]==0) {
      if (function_exists ("N_CustomDoLock2")) {
        N_CustomDoLock2 ($spec, $timetowait);
        global $lock3path;
        $lock3path = "html::/tmp/locks3";
      } else {
        N_DoLock2 ($spec, $timetowait);
      }
    }
    $N_Lock_admin[$slot]++;
  }

  function N_Unlock ($spec)
  {
    global $N_Lock_admin, $disablelocking;
    if ($disablelocking) return;
    $slot = md5("lock".serialize($spec));
    $N_Lock_admin[$slot]--;
    if ($N_Lock_admin[$slot]==0) {
      if (function_exists ("N_CustomDoUnLock2")) {
        N_CustomDoUnLock2 ($spec);
        global $lock3path;
        $lock3path = "html::/tmp/locks3";
      } else {
        N_DoUnLock2 ($spec);
      }
    }
  }

  global $lock3path;
  $lock3path = "html::/tmp/locks3";

  function N_DoLock2 ($spec, $timetowait=15)
  {
    global $lock3path;
    if (!file_exists ($path = N_CleanPath ($lock3path))) {
      @mkdir (N_CleanPath ("html::/tmp"));
      @mkdir ($path);
    }
    $start = time();
    $file = N_CleanPath ($lock3path."/".md5("lock".serialize($spec)));
    while (!$fd) {
      if (time()-$start > $timetowait) {
        if (time() - @filemtime ($file) > 60) @unlink ($file);
        N_DIE ("Failed to obtain lock ($spec)");
      }
      $fd = @fopen($file, "x");
      if (!$fd) N_Sleep (10);
    }
    fclose ($fd);
  }

  function N_DoUnlock2 ($spec)
  {
    global $lock3path;
    $file = N_CleanPath ($lock3path."/".md5("lock".serialize($spec)));
    @unlink ($file);
  }

  function N_DoLock ($spec, $timetowait=15)
  {    
    N_Debug ("N_Lock ($spec, $timetowait) START", "N_Lock");
    // NO (indirect) calls to N_Debug etc.
    N_ErrorHandling (false);

    $start = time();

    // make sure file exists
    $slot = md5("lock".serialize($spec));
    $dir = substr ($slot, 0, 3);
    $file = N_CleanPath ("html::tmp/locks2/$dir/$slot");
    if (!is_file ($file)) {
      @$fdsafe = fopen($file, 'wb');
      if (!$fdsafe) {
        N_MkDir ("html::tmp/locks2/$dir/");
        @$fdsafe = fopen($file, 'wb');
        if (!$fdsafe) {
          N_MkDir ("html::tmp/");
          N_MkDir ("html::tmp/locks2/");
          N_MkDir ("html::tmp/locks2/$dir/");
          @$fdsafe = fopen($file, 'wb');
          if (!$fdsafe) N_DIE ("Failed to obtain lock ($spec) #1");
        }
      }
      N_Chmod ($file); 
      fclose ($fdsafe);
    }
    
    // get access to file
    for ($i=0; $i<($timetowait*100) && !$handle; $i++) {
      $handle = fopen($file, 'a');
      if (!$handle) {
        N_Sleep (10);
      } else {
        $flock = flock($handle, LOCK_NB + LOCK_EX);
        if (!$flock) {
          fclose ($handle);
          N_Sleep (10);
          $handle = 0;
        }
      }
    }
    if (!$handle) N_DIE ("Failed to obtain lock ($spec) #2");

    global $n_lock_handles;
    @$n_lock_handles[$slot] = $handle;

    N_ErrorHandling (true);
    N_Debug ("N_Lock ($spec, $timetowait) END");
  }

  function N_DoUnlock ($spec)
  {
    $slot = md5("lock".serialize($spec));
    global $n_lock_handles;
    if ($n_lock_handles[$slot]) {
      flock ($n_lock_handles[$slot], LOCK_UN);
      fclose ($n_lock_handles[$slot]);
      unset ($n_lock_handles[$slot]);
    }
  }

  function N_ExplodeURL ($url) 
  {
    $parts = parse_url($url);
    $rawparams = explode ('&', $parts["query"]);
    foreach ($rawparams as $dummy => $elem) {
      $tupple = explode ('=', $elem);
      if ($tupple[0]) {
        if ($query[$tupple[0]]) {
           for ($i=1; $i<=99; $i++) {
             if (!$query[$tupple[0]."____$i"]) {
               $query[$tupple[0]."____$i"] = urldecode($tupple[1]);
               break;
             }
           }
        } else {
          $query[$tupple[0]] = urldecode($tupple[1]); 
        }
      }
    }
    $parts["query"] = $query;
    return $parts;    
  }

  function N_ImplodeURL ($parts)
  {
    if (strcmp($parts['scheme'], '') != 0) {
     $url = $parts['scheme'] . '://';
    }
    $url .= $parts['user'];
    if (strcmp($parts['pass'], '') != 0) {
     $url .= ':' . $parts['pass'];
    }
    if ((strcmp($parts['user'], '') != 0) || (strcmp($parts['pass'], '') != 0)) {
     $url .= '@';
    }
    $url .= $parts['host'];
    if (strcmp($parts['port'], '') != 0) {
     $url .= ':' . $parts['port'];
    }
    $url .= $parts['path'];
    if ($parts['query']) {
      $url .= '?';
      $first = true; 
      foreach ($parts['query'] as $arg => $val) {
        if (strpos ($arg, "____")) {
          $arg = N_KeepBefore ($arg, "____");
        }
        if (!$first) $url .= "&"; else $first=false;
        $url .= $arg."=".urlencode($val);
      }
    }
    if (strcmp($parts['fragment'], '') != 0) {
     $url .= '#' . $parts['fragment'];
    }
    
    return $url;
  }

  function N_AlterURL ($url, $arg, $val = "deletemyarg")
  {
    $parts = N_ExplodeURL ($url);
    for ($i=99; $i>=0; $i--) {
      if ($i) $arg2=$arg."____$i"; else $arg2=$arg;
      if ($parts["query"][$arg2]) {
        $parts["query"][$arg2] = $val;
        if ($val === "deletemyarg") unset($parts["query"][$arg2]); // Make deleting also work for duplicate arguments
        break;
      }
    }
    if ($i==-1) $parts["query"][$arg] = $val;
    if ($val === "deletemyarg") unset($parts["query"][$arg]);  // LF20100407: shorter function calls and shorter urls at the same time :)
    return N_ImplodeURL ($parts);
  }

  function N_str_replace_once($needle, $replace, $haystack) { 
     $pos = strpos($haystack, $needle); 
     if ($pos === false) { 
         return $haystack; 
     } 
     return substr_replace($haystack, $replace, $pos, strlen($needle)); 
  } 

  function N_preg_replace ($a, $b, $c, $d=-1) 
  {
    return str_replace ("0af98s6f"."0q923875", $b, preg_replace ($a, "0af98s6f"."0q923875", $c, $d));
  }

  function N_CurrentProtocol()
  {
    if ( strtolower($_SERVER['HTTPS']) == 'on' || ($_SERVER["HTTP_SCHEME"] && strtolower($_SERVER["HTTP_SCHEME"]) == "https"))
       return "https://";
    else
       return "http://";
  }

  function N_SSLRedirect()
  {
     global $myconfig;

     if ($_COOKIE and array_key_exists ("ssllogin", $_COOKIE) && $_COOKIE["ssllogin"] == "yes") {
       // The "ssllogin" cookie indicates that the user is logged in, but the authentication cookie will only be sent over https
       $myconfig[IMS_SuperGroupname()]["ssl_usage"] = "required"; 
     }

     if ($myconfig[IMS_SuperGroupname()] and array_key_exists ("ssl_usage", $myconfig[IMS_SuperGroupname()])) {
       $prop = strtolower($myconfig[IMS_SuperGroupname()]["ssl_usage"]);
       switch ($prop) 
       {
         case 'none':
            break;
         case 'optional':
            break;
         case 'required': 
            if (strtolower(substr(N_MyFullURL(), 0, 5)) != 'https') {
  
               $fullurl = N_MyFullURL();
               $returndirectarray = array("gate.php", "jgate.php", "grid.php", "callmeoften.php");
               foreach($returndirectarray as $returndirect) {
                 if (strpos ($fullurl, $returndirect)) return;
               }
               N_Redirect ( str_replace("http:","https:",N_MyFullURL()), 301);
            }
            break;
       }
    }  
  }

  function N_PrintStack ($stack, $showargs) {
    echo "<font size=\"3\"><pre> --------------<br>";
    foreach ($stack as $dummy => $specs) {
      echo "&nbsp;&nbsp;&nbsp;<b>".$specs["function"]."</b> called from ".$specs["file"]." line ".$specs["line"];
      if ($showargs) {
        uuse ("forms");
        $form = array();
        $form["formtemplate"] = "<pre>".print_r($specs["args"], true)."</pre>";
        echo " <a href=\"".FORMS_URL ($form)."\">args</a>"; 
      }
      $file = str_replace ("html::", "/", N_InternalPath ($specs["file"]));
      $url = "/private/edit.php?file=$file&view=yes&lineno=".$specs["line"]."#".($specs["line"]-20);
      echo " <a target=\"_blank\" href=\"$url\">show call</a>";
      echo "<br>";
    }
    echo " --------------</pre>";
  }

  function N_Stack2String ()
  { 
    if (!function_exists ("debug_backtrace")) {
      $result = "(N_Stack2String : debug_backtrace is not available)<br>";
    } else {
      $stack = debug_backtrace();
      foreach ($stack as $dummy => $specs) {
        $result .= "<b>".$specs["function"]."</b> called from ".$specs["file"]." line ".$specs["line"];
        $result .= " args: ".substr(serialize($specs["args"]), 0, 500)."<br>";
      }
    }
    return $result;
  }

  function N_ShowStack ($showargs=false)
  { 
    if (!function_exists ("debug_backtrace")) {
      echo "N_ShowStack: debug_backtrace is not available<br>";
    } else {
      $stack = debug_backtrace();
      N_PrintStack ($stack, $showargs);   
    }
  }

  function N_ObjectLog ($supergroupname, $objecttype, $objectid, $action, $details) {
    global $myconfig;
    if ($myconfig[$supergroupname]["disableobjectlog"]!= "yes") {
      $object = MB_Load ("ims_" . $supergroupname . "_objects", $objectid);
      $ext = strtolower(strrev(N_KeepBefore (strrev($object["filename"]), ".")));
      if (in_array ($ext, array("doc","pdf","xls","ppt","msg","txt","wmv","docx","docm","odt","xlsx","xlsm","ods","pptx","pptm","odp"))) { // e.g. skip gif, jpg, css, js, etc.
        $rec["objecttype"] = $objecttype; 
        $rec["objectid"] = $objectid; 
        $rec["user"] = SHIELD_CurrentUser(); 
        $rec["time"] = time(); 
        $rec["action"] = $action; 
        $rec["details"] = $details; 
        MB_REP_Save ("ims_".$supergroupname."_objects_log", N_GUID(), $rec);
      }
    }
  }
  
  function N_SafeStrToTime($strInput) {  
    $iVal = -1;  
    for ($i=1900; $i<=1969; $i++)  
    {  
        // Check for this year string in date  
        $strYear = (string)$i;  
        if (!(strpos($strInput, $strYear)===false))  
        {  
            $replYear = $strYear;  
            $yearSkew = 1970 - $i;  
            $strInput = str_replace($strYear, '1970', $strInput);  
        }  
    }  
    $iVal = N_strtotime($strInput);  // JG - was strtotome
    if ($yearSkew> 0)  
    {  
        $numSecs = (60 * 60 * 24 * 365 * $yearSkew);  
        $iVal = $iVal - $numSecs;  
        $numLeapYears = 0;   // determine number of leap years in period  
        for ($j=$replYear; $j<=1969; $j++)  
        {  
            $thisYear = $j;  
            $isLeapYear = false;  
            // Is div by 4?  
            if (($thisYear % 4) == 0)  
            {  
                $isLeapYear = true;  
            }  
            // Is div by 100?  
            if (($thisYear % 100) == 0)  
            {  
                $isLeapYear = false;  
            }  
            // Is div by 1000?  
            if (($thisYear % 1000) == 0)  
            {  
                $isLeapYear = true;  
            }  
            // Count every leap day between the date and 1970.  So if the date's original year is a leap year, 
            // only count it if the date is in Jan or Feb.
            if ($isLeapYear == true && ($j != $replYear || stripos(" ".$strInput, "jan") || stripos(" ".$strInput, "feb")))  
            {  
                $numLeapYears++;  
            }  
        }  
        $iVal = $iVal - (60 * 60 * 24 * $numLeapYears);  
    }  
    return $iVal;  
  }

  function N_RemoveUnicode($input) {
     uuse("search");
     return SEARCH_HTML2TEXT ($input);
  }

if (!function_exists('str_ireplace')) {
    function str_ireplace($search, $replace, $subject, $count = null)
    {
        if (is_string($search) && is_array($replace)) {
            user_error('Array to string conversion', E_USER_NOTICE);
            $replace = (string) $replace;
        }

        if (!is_array($search)) {
            $search = array ($search);
        }
        $search = array_values($search);

        if (!is_array($replace)) {
            $replace_string = $replace;

            $replace = array ();
            for ($i = 0, $c = count($search); $i < $c; $i++) {

                $replace[$i] = $replace_string;
            }
        }
        $replace = array_values($replace);

        $length_replace = count($replace);
        $length_search = count($search);
        if ($length_replace < $length_search) {
            for ($i = $length_replace; $i < $length_search; $i++) {
                $replace[$i] = '';
            }
        }

        $was_array = false;
        if (!is_array($subject)) {
            $was_array = true;
            $subject = array ($subject);
        }

        $count = 0;
        foreach ($subject as $subject_key => $subject_value) {
            foreach ($search as $search_key => $search_value) {
                $segments = explode(strtolower($search_value), strtolower($subject_value));

                $count += count($segments) - 1;
                $pos = 0;

                foreach ($segments as $segment_key => $segment_value) {
                    $segments[$segment_key] = substr($subject_value, $pos, strlen($segment_value));
                    $pos += strlen($segment_value) + strlen($search_value);
                }

                $subject_value = implode($replace[$search_key], $segments);
            }

            $result[$subject_key] = $subject_value;
        }

        if ($was_array === true) {
            return $result[0];
        }

        return $result;
    }
}

function N_strarray_ireplace($search, $replace, $source) {
  // Apply str_ireplace recursivly to $source. This applies to keys as well.
  if (is_string($source)) {
    return str_ireplace($search, $replace, $source);
  } elseif (is_array($source) && count($source)) {
    foreach ($source as $key => $value) {
      $newarray[str_ireplace($search, $replace, $key)] = N_strarray_ireplace($search, $replace, $value);
    }
    return $newarray;
  } else { // empty arrays, resources, other weird stuff
    return $source;
  }
}

function N_PHP5() {
 if (version_compare(phpversion(), "5", ">="))
   return true;
 else
   return false;
}

function N_array_merge() {
   $merged = array();
   for($i=0; $i<func_num_args(); $i++) {
     $tmp = ((is_array (func_get_arg ($i))) ? (func_get_arg ($i)) : (array (func_get_arg ($i))) );
     $merged = array_merge ($merged,$tmp);
   }
   return($merged);
}

function N_array_union() {
   $merged = array();
   for($i=0; $i<func_num_args(); $i++) {
     $tmp = ((is_array (func_get_arg ($i))) ? (func_get_arg ($i)) : (array ((pow(10,9)+(++$ctr))=>func_get_arg ($i))) );
     $merged = $merged + $tmp;
   }
   return($merged);
}

function N_ReverseAnyXML2Object ($object, $nicetags=true)
{
  if ($object["allxml"]) return N_ReverseAnyXML2Object ($object["allxml"], $nicetags);
  if ($object["type"]=="tag") {
    $result = "<".$object["name"];
    foreach ($object["attributes"] as $var => $val) {
      $val = htmlspecialchars ($val);
      $result .= " $var=\"$val\"";
    }
    if ($nicetags && !count($object["elems"])) {
      $result .= "/>";
    } else {
      $result .= ">";
      foreach ($object["elems"] as $o) $result .= N_ReverseAnyXML2Object ($o);
      $result .= "</".$object["name"].">";
    }
    return $result;
  } else if ($object["type"]=="cdata") {
    return htmlspecialchars ($object["value"]);
  }
}

function N_AnyXML2Object ($xml)
{
  $parser = xml_parser_create(''); // Auto detect format in both PHP4 and PHP5
  xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
  if (!xml_parse_into_struct ($parser, $xml, $rawdata)) {
    $result["error"] = xml_error_string(xml_get_error_code($parser));
  }
  $tmp = N_RawData2Object ($rawdata, 0);
  $result["alldata"] = N_FindDataAnyXML2Object ($tmp);
  $result["allxml"] = $tmp;
  xml_parser_free ($parser);
  return $result;
}

// Extract data from XML into easier-to-understand associative array.
// Not everything can be represented! If an element has both CDATA and children or attributes, the CDATA will be discarded.
function N_FindDataAnyXML2Object (
  $allxml, // output of N_RawData2Object
  $cdatatags = array() // tagnames for which you prefer CDATA and want to discard children and attributes if CDATA is present
  ) 
{
  if ($allxml["type"] == "tag") {
    foreach ($allxml["attributes"] as $var => $val) {
      $result[$var][] = $val;
    }
    for ($i=0; $i<count ($allxml["elems"]); $i++) {
      if ($allxml["elems"][$i]["type"] == "tag") {
        $tagname = $allxml["elems"][$i]["name"];
        if (in_array($tagname, $cdatatags) && ($allxml["elems"][$i]["elems"][0]["type"] == "cdata")) {
          $result[$tagname][] = $allxml["elems"][$i]["elems"][0]["value"];
        } else {
          $sub = N_FindDataAnyXML2Object ($allxml["elems"][$i], $cdatatags);
          if (is_array($sub)) {
            $result[$tagname][] = $sub;
          } else if ($allxml["elems"][$i]["elems"][0]["type"] == "cdata") {
            $result[$tagname][] = $allxml["elems"][$i]["elems"][0]["value"];
          }
        }
      }
    }
  }
  return $result;
}


function N_RawData2Object ($rawdata, $start)
{
  $elem = 0;
  $first = $rawdata[$start];
  if ($first["type"]=="open" || $first["type"]=="complete") {
    $result["type"] = "tag";
    $result["name"] = $first["tag"];
    $result["attributes"] = $first["attributes"];
    if ($first["value"]) {
      $result["elems"][$elem]["type"] = "cdata";
      $result["elems"][$elem++]["value"] = $first["value"];
    }
    if ($first["type"]=="open") for ($i=$start+1; $i<count($rawdata); $i++) {
      if ($rawdata[$i]["type"]=="close" && $rawdata[$i]["tag"]==$first["tag"] && $rawdata[$i]["level"]==$first["level"]) break;
      if ($rawdata[$i]["type"]=="cdata" && $rawdata[$i]["level"]==$first["level"]) {
        $result["elems"][$elem++] = N_RawData2Object ($rawdata, $i);        
        if ($result["elems"][$elem-1]["type"] == "cdata" && $result["elems"][$elem-2]["type"] == "cdata") {
          $result["elems"][$elem-2]["value"] .= $result["elems"][$elem-1]["value"];
          unset ($result["elems"][--$elem]);
        }
      } 
      if ($rawdata[$i]["type"]=="open" && $rawdata[$i]["level"]==$first["level"]+1) {
        $result["elems"][$elem++] = N_RawData2Object ($rawdata, $i);
      } 
      if ($rawdata[$i]["type"]=="complete" && $rawdata[$i]["level"]==$first["level"]+1) {
        $result["elems"][$elem++] = N_RawData2Object ($rawdata, $i);
      } 
    }
  } else if ($first["type"]=="cdata") {
    $result["type"] = "cdata";
    $result["value"] = $first["value"];
  }
  return $result;
}

function N_TidyCrap2XHTML ($CommonReadyforAdvancedPrettyprinting)
{
  // http://tidy.sourceforge.net/docs/quickref.html
  if (!function_exists('tidy_parse_string')) return "???";
  if (N_PHP5()) {
    $config = array(
             'indent'         => true,
             'output-xhtml'   => true,
             'numeric-entities' => true, 
             'wrap'           => 200);
    $tidy = tidy_parse_string ($CommonReadyforAdvancedPrettyprinting, $config, 'utf8');
    $tidy->cleanRepair();
    return tidy_get_output($tidy);
  } else {
    tidy_setopt('output-xhtml', 1);
    //tidy_setopt('output-html', 1);
    tidy_setopt('enclose-block-text', 1);
    tidy_setopt('indent', TRUE);
    tidy_setopt('indent-spaces', 2);
    tidy_setopt('wrap', 200); 
    tidy_setopt('hide-comments', 1); 
    tidy_setopt('bare', 1); // strip microsoft word specific HTML
    tidy_setopt('clean', 1); 
    tidy_setopt('drop-proprietary-attributes', 1);
    tidy_setopt('doctype', 'transitional'); // XHTML
    //tidy_setopt('doctype', 'loose'); // HTML
    //tidy_setopt('doctype', 'strict'); // XHTML & HTML
    tidy_setopt('force-output', 1);

    // Stupid (not recommended) options to get through "drempels vrij" test
    tidy_setopt('logical-emphasis', 1); // <i> -> <em> en <b> -> <strong>
    tidy_setopt('alt-text', 'Image'); // not recommended!

    tidy_parse_string($CommonReadyforAdvancedPrettyprinting);
    tidy_clean_repair();
    return tidy_get_output();
  }
}

function N_CRC32($input) {
  $result = crc32($input);
  if ($result >= 2147483648) { 
    // On 32bit systems, crc32 returns a number between -2^31 and 2^31-1.
    // On 64bit systems, crc32 returns a number between 0 and 2^32.
    // We want the 32bit behaviour everywhere.
    $result = 0 - (4294967296 - $result); // Two's complement
  }
  
  return $result;

}

function N_sputcsv($fields, $delimiter = ',', $enclosure = '"', $linebreak = "\n") {
  // Like php5's fputcsv-function, but returns string instead of writing to filehandle
  // $fields should be the fields for a *single* row, not an entire table.

  $delimiter_esc = preg_quote($delimiter, '/');
  $enclosure_esc = preg_quote($enclosure, '/');

  $output = array();
  foreach ($fields as $field) {
    if ($field === null && $mysql_null) {
      $output[] = 'NULL';
      continue;
    }

    // The ; delimiter is used by Excel in conjunction with , as decimal separator and . to separate thousandths.
    // So if the delimiter is ; convert . to , in fields looking like a number (including fields ending with a %)
    if ($delimiter == ";" && (is_numeric($field) || (is_numeric(substr($field, 0, -1)) && substr($field, -1) == "%"))) $field = str_replace(".", ",", $field);

    $output[] = preg_match("/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field) ? (
       $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure
    ) : $field;
  }
  return join($delimiter, $output) . $linebreak; 
}

  function N_Hexdump ($data)
  {
    $htmloutput = true;
    $uppercase = false;
    $return = false;

    $hexi   = '';
    $ascii  = '';
    $dump   = ($htmloutput === true) ? '<pre><font face="courier">' : '';
    $offset = 0;
    $len    = strlen($data);

    // Upper or lower case hexadecimal
    $x = ($uppercase === false) ? 'x' : 'X';

    // Iterate string
    for ($i = $j = 0; $i < $len; $i++)
    {
        // Convert to hexidecimal
        $hexi .= sprintf("%02$x ", ord($data[$i]));

        // Replace non-viewable bytes with '.'
        if (ord($data[$i]) >= 32) {
            $ascii .= ($htmloutput === true) ?
                            htmlentities($data[$i]) :
                            $data[$i];
        } else {
            $ascii .= '.';
        }

        // Add extra column spacing
        if ($j === 7) {
            $hexi  .= ' ';
            $ascii .= ' ';
        }

        // Add row
        if (++$j === 16 || $i === $len - 1) {
            // Join the hexi / ascii output
            $dump .= sprintf("%04$x  %-49s  %s", $offset, $hexi, $ascii);

            // Reset vars
            $hexi   = $ascii = '';
            $offset += 16;
            $j      = 0;

            // Add newline
            if ($i !== $len - 1) {
                $dump .= "\n";
            }
        }
    }

    // Finish dump
    $dump .= $htmloutput === true ?
                '</font></pre>' :
                '';
    $dump .= "\n";

    // Output method
    if ($return === false) {
        echo $dump;
        return ($data);
    } else {
        return $dump;
    }
  }

  function N_SetCookie($name, $content, $expires = 0, $path = "/", $domain = "", $only_https = false, $no_javascript = false) {
    // Slightly different default behaviour from setcookie ($path and $no_javascript).
    // Also fixes the stupid problem that the setcookie function does NOTHING when given more parameters than "expected" by that version op PHP. 
    if (version_compare(PHP_VERSION, '5.2.0') >= 1) {
      setcookie($name, $content, $expires, $path, $domain, $only_https, $no_javascript);      
    } else {
      setcookie($name, $content, $expires, $path, $domain, $only_https);      
    }
  }

  function N_SetOutputFilter($functionname) {
    // You can only have one output filter at a time (replaces the previous one)
    // Will break things if htmlcompression was enabled.
    // May also work on some "previous" output; if you don't want that, call N_Flush() before calling this function.
    global $N_Flush_Outputfilter;
    $N_Flush_Outputfilter = $functionname;

    // End the current output buffer, start a new one with the new output filter.
    // Since we can't flush, echo the contents of the current output buffer in the new one.
    $contents = ob_get_contents();
    ob_end_clean();
    ob_start($N_Flush_Outputfilter);
    echo $contents;
//    N_Flush(); // NDV: This flush is breaking basic authentication, not sure why it was here
  }

  function N_OpenIMSCE() {
    // This function should be used only to INCLUDE specific CE functionality, it should never be used to exclude PRO functionality.

    
    return true;
  }

  function N_OpenIMSCE_AutoconfRedirect() {
    if (N_OpenIMSCE()) {
      $sgn_object = MB_Load("ims_sitecollections", "ce_sites");
      $site_object = MB_Load("ims_sites", "ce_com");
      $admin_object = MB_Load("shield_ce_sites_users", "admin");
      if ($sgn_object && $site_object && $sgn_object["sites"]["ce_com"] && $admin_object) {
        return true;
      } else {
        N_Redirect(N_CurrentProtocol() . $_SERVER['HTTP_HOST'] . '/openims/ce/autoconf/');
      }
    }
  }

function N_EliminateUrlArg($url, $killargument = '') {
  /* Eliminate an argument from an url.
   * If a form in OpenIMS showed an error and the user uses the 'back' button,
   * the URL is extended with an argument 'reloaddata', which has encoded specs which
   * re-populates the form again with userdata.
   * If the form was posted with correct data, this complete URL is shown in signaling-mail.
   * That's why this function was born, although it's much more generic.
  */

  if ($killargument) {
    return N_AlterURL ($url, $killargument);
  } else {
    return $url;
  }
}

function N_DoNotDisturb($die = true) {
/* Check if DoNotDisturb settings apply, and (by default) call N_Die() if this is the case.
 * Call this just before executing performance heavy code that you want to disable in a DoNotDisturb situation.
 */
  global $myconfig;
  if ($myconfig["donotdisturb"] == "yes") {
    $sgn = IMS_SuperGroupName();
    if ($sgn && !$myconfig["donotdisturbexceptions"][$sgn]) {
      if ($die) {
        echo $myconfig["donotdisturbmessage"];
        N_Die("N_DoNotDisturb");
      } else {
        return true;
      }
    }
  }
  return false;
}

function N_performance_log( $logbook , $joinby, $prefix='start=' , $elapsed = false , $postfix = 's, ' , $max = 5 )
{
  $str = $prefix;
  if ( $elapsed!==false )
  {
    $elapsed = round( $elapsed , 2 );
    if ( $max>0 && $elapsed > $max )
      $elapsed = '<b>' . $elapsed . '</b>';
	$str .= $elapsed . $postfix;
  }
  $GLOBALS["N_EXIT_LOGGING"][$logbook][$joinby] .= $str;
}

function N_new_com( $comname )
{ 
  if ( version_compare(PHP_VERSION, '5.0.0', '>=') ) 
  { 
    $cobj = eval( 'try{ $cobj = new com("'.$comname.'"); } catch(Exception $e) { N_Log ("errors", "N_new_com " . $e->getMessage() ); $cobj = null;}return $cobj;' ); 
  } else { 
    $cobj = new com( $comname );
  } 
  return $cobj; 
}
if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = json_encode($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = json_encode($k).':'.json_encode($v);
      return '{' . join(',', $result) . '}';
    }
  }
}

function N_strtotime( $datestring ) 
{ 
  if ( version_compare(PHP_VERSION, '5.0.0', '>=') ) 
  { 
    return eval( 'try{ $dt = new DateTime("'.addcslashes($datestring,'"').'"); } catch(Exception $e) { N_Log ("errors", "N_strtotime " . $e->getMessage() ); return false;}return $dt->format("U");' ); 
//    $date = new DateTime( $datestring ); 
//    return $date->format("U"); 
  } else { 
    $time = strtotime( $datestring ); 
  } 
  return $time; 
}

?>