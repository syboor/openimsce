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



function STATS_Summary ()
{
  uuse ("sys");
  N_Log ("stats", "Start");
  $meminfo = STATS_OS_Memory ();
  N_Log ("stats", "After STATS_OS_Memory");
  $doing = STATS_Apache_Doing ();
  N_Log ("stats", "After STATS_Apache_Doing");
  $bakinfo = SYS_AutoCheckBackup ();
  N_Log ("stats", "After SYS_AutoCheckBackup");
  $sum = "Up: ".STATS_OS_Uptime ()." ";  
  N_Log ("stats", "After STATS_OS_Uptime");
  if ($bakinfo["status"]=="ok") {
    $sum .= "bak: ";
    if ((int)($bakinfo["age"]/3600) > 24) {
      $sum .= "<b><font color=\"#ff0000\">".(int)($bakinfo["age"]/3600)."h</font></b> / ";
    } else {
      $sum .= (int)($bakinfo["age"]/3600)."h / ";
    }
    if ((int)($bakinfo["size"]/1048576) < 1) {
      $sum .= "<b><font color=\"#ff0000\">".(int)($bakinfo["size"]/1048576)."MB</font></b> ";
    } else {
      $sum .= (int)($bakinfo["size"]/1048576)."MB ";
    }
  } else {
    $sum .= "bak: <b><font color=\"#ff0000\">ERROR</font></b> ";
  }
  $sum .= "cpu: ".STATS_OS_CPULoad ()." ";
  N_Log ("stats", "After STATS_OS_CPULoad");
  $sum .= "mem+cache: ".(int)((($meminfo ["memused"]-$meminfo ["cached"])/$meminfo ["memtotal"])*100)."%";
  $sum .= "+".(int)(($meminfo ["cached"]/$meminfo ["memtotal"])*100)."% ";
  $sum .= "swp: ".(int)(($meminfo ["swapused"]/$meminfo ["swaptotal"])*100)."% ";
  if ((int)(N_DiskSpace ()/(1024*1024*1024)) < 3) {
    $sum .= "dsk: <b><font color=\"#ff0000\">".(int)(N_DiskSpace ()/(1024*1024*1024))."GB</font></b> ";
  } else {
    $sum .= "dsk: ".(int)(N_DiskSpace ()/(1024*1024*1024))."GB ";
  }
  N_Log ("stats", "After N_DiskSpace");
  $sum .= "osload: ".STATS_OS_Load ()." ";
  N_Log ("stats", "After STATS_OS_Load");
  $sum .= "aload: ".STATS_Apache_Load ()." ";
  N_Log ("stats", "After STATS_Apache_Load");
  $sum .= "(";
  foreach ($doing as $key => $value) {
    $sum .= strtolower (substr ($key, 0, 2)).": ";
    $sum .= $value." ";
  }
  $sum = trim ($sum);
  $sum .= ") ";
  N_Log ("stats", "End");
  return trim ($sum);
}

function STATS_OS_CPULoad ()
{
  static $last;
 
  $info = STATS_OS_RAW_CPULoad ();
  if (!$info) return false;

  if ($lastload===NULL) {
    sleep(1);
    $lastload = $info;
    $info = STATS_OS_RAW_CPULoad ();
  }

  $last = $lastload;
  $lasload = $info;

  $d_user = $info[0] - $last[0];
  $d_nice = $info[1] - $last[1];
  $d_system = $info[2] - $last[2];
  $d_idle = $info[3] - $last[3];
		
  if (N_Windows()) {
    if ($d_idle < 1) $d_idle = 1;
    return ((int)(100*(1-$d_user/$d_idle)))."%";
  } else {
    $total=$d_user+$d_nice+$d_system+$d_idle;
    if ($total<1) $total=1;
    return ((int)(100*($d_user+$d_nice+$d_system)/$total))."%"; 
  }
}

function STATS_OS_Uptime ()
{
  if (N_Windows()) {
    $netstat = STATS_RAW_Uptime ();
    if (N_RegExp ($netstat, "Statistics since ([0-9]*)[^0-9]([0-9]*)[^0-9]([0-9][0-9][0-9][0-9])")) {
      $date = N_BuildDateTime (
        N_RegExp ($netstat, "Statistics since ([0-9]*)[^0-9]([0-9]*)[^0-9]([0-9][0-9][0-9][0-9])", 3),
        N_RegExp ($netstat, "Statistics since ([0-9]*)[^0-9]([0-9]*)[^0-9]([0-9][0-9][0-9][0-9])", 2),
        N_RegExp ($netstat, "Statistics since ([0-9]*)[^0-9]([0-9]*)[^0-9]([0-9][0-9][0-9][0-9])", 1)
      );
    } else if (N_RegExp ($netstat, "Statistieken vanaf ([0-9]*)[^0-9]([0-9]*)[^0-9]([0-9][0-9][0-9][0-9])")) {
      $date = N_BuildDateTime (
        N_RegExp ($netstat, "Statistieken vanaf ([0-9]*)[^0-9]([0-9]*)[^0-9]([0-9][0-9][0-9][0-9])", 3),
        N_RegExp ($netstat, "Statistieken vanaf ([0-9]*)[^0-9]([0-9]*)[^0-9]([0-9][0-9][0-9][0-9])", 1),
        N_RegExp ($netstat, "Statistieken vanaf ([0-9]*)[^0-9]([0-9]*)[^0-9]([0-9][0-9][0-9][0-9])", 2)
      );
    }
    $ret = (int)((time()-$date)/(24*3600))." days";
    if($ret==0) $ret=1;
    return $ret;
  } else {
    $uptime = STATS_RAW_Uptime ();
    $ret = N_Regexp ($uptime, "up ([^,]*)");
    if($ret==0) $ret=1;
    return $ret;
  }
}

function STATS_OS_Load ()
{
  if (N_Windows()) {
    return 1;
  } else {
    $ret = N_Regexp (STATS_RAW_Uptime (), "load average: ([0-9]*.[0-9]*)");
    if($ret==0) $ret=1;
    return $ret;
  }
}

function STATS_OS_Memory ()
{
  $meminfo = STATS_RAW_Meminfo ();
  preg_match ("'memtotal: *([0-9]*) *kB'is", $meminfo, $matches);
  $mem["memtotal"] = (int)($matches[1] / 1024);
  preg_match ("'memfree: *([0-9]*) *kB'is", $meminfo, $matches);
  $mem["memfree"] = (int)($matches[1] / 1024);
  $mem["memused"] = $mem["memtotal"]-$mem["memfree"];

  preg_match ("'swaptotal: *([0-9]*) *kB'is", $meminfo, $matches);
  $mem["swaptotal"] = (int)($matches[1] / 1024);
  preg_match ("'swapfree: *([0-9]*) *kB'is", $meminfo, $matches);
  $mem["swapfree"] = (int)($matches[1] / 1024);
  $mem["swapused"] = $mem["swaptotal"]-$mem["swapfree"];

  preg_match ("'cached: *([0-9]*) *kB'is", $meminfo, $matches);
  $mem["cached"] = (int)($matches[1] / 1024);



  if($mem["memtotal"]==0) $mem["memtotal"]=1;
  if($mem["memfree"]==0) $mem["memfree"]=1;
  if($mem["memused"]==0) $mem["memused"]=1;
  if($mem["swaptotal"]==0) $mem["swaptotal"]=1;
  if($mem["swapfree"]==0) $mem["swapfree"]=1;
  if($mem["swapused"]==0) $mem["swapused"]=1;

  return $mem;
}

function STATS_Apache_Load ()
{
  $ret = N_Regexp (STATS_RAW_ServerStatus(), "[^0-9]([0-9]*) requests currently being processed");
  if($ret==0) $ret=1;
  return $ret;
}

function STATS_Apache_Doing ()
{
  $list = STATS_RAW_Apache_Doing ();
  foreach ($list as $dummy => $request) {
    if (strpos (" ".$request, "grid.php") && strpos (" ".$request, "terra")) {
      $doing["terra"]++;
    } else if (strpos (" ".$request, "grid.php") || strpos (" ".$request, "master.php")) {
      $doing["grid"]++;
    } else if (strpos (" ".$request, "callmeoften")) {
      $doing["callmeoften"]++;
    } else if (strpos (" ".$request, "server-status")) {
      $doing["server-status"]++;
    } else {
      $doing["other"]++;
    }
  }
  ksort ($doing);
  return $doing;
}

function STATS_RAW_Apache_Doing ()
{
  $stats = STATS_RAW_ServerStatus();
  preg_match_all ("'<tr(.*)<b>(w|k|g|c)</b>(.*)(post|get) (.*)</tr>'Uis", $stats, $matches);
  return $matches[5];
}

function STATS_RAW_ServerStatus()
{
  static $serverstatus;
  if ($serverstatus===NULL) {
    global $VBrowser4GetPage;
    $oldtimeout = $VBrowser4GetPage->timeout;
    $VBrowser4GetPage->setTimeout (10);
    $serverstatus = N_GetPage (N_ServerAddress (N_CurrentServer())."/server-status");
    $VBrowser4GetPage->setTimeout ($oldtimeout);
  }
  return ($serverstatus);
}

function STATS_RAW_Uptime ()
{
  static $uptime;
  if ($uptime===NULL) {
    if (N_Windows()) {
      $uptime = `net statistics workstation`;
    } else {
      $uptime = `uptime`;
    }  
  }
  return $uptime;
}

function STATS_RAW_Meminfo ()
{
  static $meminfo;
  if ($meminfo===NULL) {
    if (N_Windows()) {
      $meminfo = `c:\\cygwin\\bin\\cat /proc/meminfo`;
    } else {
      $meminfo = N_ReadFile ("/proc/meminfo");
    }
  }
  if(!$meminfo) $meminfo=1;
  return $meminfo;
}

function STATS_OS_RAW_CPULoad ()
{
  if (N_Windows()) {
    @$c = new COM("WinMgmts:{impersonationLevel=impersonate}!Win32_PerfRawData_PerfOS_Processor.Name='_Total'");
    if (!$c) return false;
    $info[0] = $c->PercentProcessorTime;
    $info[1] = 0;
    $info[2] = 0;
    $info[3] = $c->TimeStamp_Sys100NS;
    return $info;
  } else {
    $statfile = '/proc/stat';
    if (!file_exists($statfile)) return false;
    $fd = fopen($statfile,"r");
    if (!$fd) return false;
    $statinfo = explode("\n",fgets($fd, 1024));
    fclose($fd);
    foreach($statinfo as $line) {
      $info = explode(" ",$line);
      if($info[0]=="cpu") {
        array_shift($info);  // pop off "cpu"
        if(!$info[0]) array_shift($info); // pop off blank space (if any)
        return $info;
      }
    }
  }
  return false;
}

?>