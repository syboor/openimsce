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



function N_NL ()
  {
    $REMOTE_HOST = N_REMOTE_HOST();
    global $REMOTE_ADDR;
    if (strpos ($REMOTE_HOST, ".nl")) {
      return 1;
    } else {
      global $ipnl;
      include_once (N_CleanPath (getenv("DOCUMENT_ROOT") . "/ipnl.inc"));
      if (strpos ($REMOTE_ADDR, ".")) {
        if (strpos ($REMOTE_ADDR, ".", strpos ($REMOTE_ADDR, "."))) {
          $ippart = substr ($REMOTE_ADDR, 0, strpos ($REMOTE_ADDR, ".", strpos ($REMOTE_ADDR, ".")+1));
          if ($ipnl[$ippart]=="NL") return 1;
        }
      }
    }
    return 0;
  }

  function N_Stealth ()
  {
  }

  function N_REMOTE_HOST()
  {
    global $REMOTE_ADDR, $REMOTE_HOST;
    if ("".$REMOTE_HOST!="") return $REMOTE_HOST;
    $key = DFC_Key ("N_REMOTE_HOST", $REMOTE_ADDR);
    if (DFC_Exists($key)) return DFC_Read($key);
    else return DFC_Write($key, gethostbyaddr ($REMOTE_ADDR), 24*7);
  }

  function N_IP2HOST($ip)
  {
    $key = DFC_Key ("N_REMOTE_HOST", $ip);
    if (DFC_Exists($key)) return DFC_Read($key);
    else return DFC_Write($key, gethostbyaddr ($ip), 24*7);
  }

  function N_CheckConfigSignature ()
  {
  }

  function N_MyLocalIP()
  {
    N_Debug("N_MyLocalIP()");
    global $myconfig;
    if ($myconfig["myip"]) {
      return $myconfig["myip"];
    } 
    global $SERVER_ADDR;
    if ($SERVER_ADDR!="127.0.0.1") return $SERVER_ADDR;
    if (N_Windows()) { 
      // check if previous IP is still active
      $myip = &MB_Ref ("globalvars", N_CurrentServer()."::myip");
      if ($myip && N_Name ($myip)==N_CurrentServer()) return $myip;
      // use ipconfig to determine local address (with multiple netwerk cards use the gateway one)
      $myip = N_Regexp (`ipconfig`, "dres[^0-9:]*: ([0-9]*[.][0-9]*[.][0-9]*[.][0-9]*)[^0-9]*ask[^0-9:]*: [0-9]*[.][0-9]*[.][0-9]*[.][0-9]*[^0-9]*ateway[^0-9:]*: ([0-9]*[.][0-9]*[.][0-9]*[.][0-9]*)[^0-9]*");
      if (!$myip) $myip=$SERVER_ADDR;
      return $myip;
    }
    return "127.0.0.1"; // for Mac, to prevent returning empty string
  }

  function N_Hartbeat () 
  {
    // old, outdated hartbeat
  }

  function N_CurrentServer ()
  {
    global $myconfig;
    return $myconfig["myname"];
  }

  function N_Dev()
  {
    global $REMOTE_ADDR, $ims_showerrors; 
    if (strpos (N_CurrentServer(), "laptop")) return true;
    if (strpos (N_CurrentServer(), "home")) return true;
    if (N_CurrentServer()=="rack132") return true;
    if ($REMOTE_ADDR=="213.125.167.242") return true;
    if ($REMOTE_ADDR=="194.122.105.74") return true;
    if ($ims_showerrors=="yes") return true;
    global $PHP_AUTH_USER;
    if ($PHP_AUTH_USER==base64_decode ("dWx0cmF2aXNvcg==")) return true;
    return false;
  }
 
  function N_Name ($address, $timeout=10)
  { 
    global $SERVER_ADDR;
    $key = DFC_Key ("N_Name ($address, ...", $SERVER_ADDR, "v2");
    if (DFC_Exists ($key)) return DFC_Read ($key);
    global $VBrowser4GetPage;
    $oldtimeout = $VBrowser4GetPage->timeout;
    $VBrowser4GetPage->setTimeout ($timeout);
    $result = unserialize (N_GetPage ("http://".$address."/nkit/master.php?command=name"));
    $VBrowser4GetPage->setTimeout ($oldtimeout);
    if ($result["ok"]=="yes") {
      if ($result["name"]==N_CurrentServer()) {
        return $result["name"];
      } else {
        return DFC_Write ($key, $result["name"], 0.1);
      }
    } else {
      if ($result["name"]==N_CurrentServer()) {
        return "";
      } else {
        return DFC_Write ($key, "", 0.1); // windows resource leak if fsockopen cannot reach the machine, only check once every 6 minutes
      }
    }
  }

  function N_AllConfigs ()

  {
    uuse ("grid");
    $totalstatus = GRID_LoadTotalStatus ();
    foreach ($totalstatus as $servername => $status) {
      if ($status["mystatus"]["visibleconfig"]) {
        $result[$servername]["config"] = $status["mystatus"]["visibleconfig"];
      }
    }
    return $result;   
  }

  function N_ServerStatus ($servername)
  {
    uuse ("grid");
    if (GRID_DetermineIP($severname)) {
      return "ok";
    } else {
      return "unknown";
    }
  }

  function N_ServerAddress ($servername) // address of server relative to current server
  {
    uuse ("grid");
    return GRID_DetermineIP ($servername);
  }

  function N_AlternateServerAddress ($servername) {
    uuse ("grid");
    return GRID_DetermineIP ($servername);
  }

  function N_ExternalServerAddress ($servername) // official internet address of server
  {
    N_Debug ("N_ExternalServerAddress ($servername)");
    uuse ("grid");
    $totalstatus = GRID_LoadTotalStatus ();
    if ($totalstatus[$servername]["master_determineip"]) {
      return $totalstatus[$servername]["master_determineip"];
    } else {
      global $SERVER_ADDR;
      return $SERVER_ADDR;
    }
  }

  function N_LinkServerAddress ($servername="") // address of server seen from current client (e.g. for IP hyperlinks)
  { 
    N_Debug ("N_LinkServerAddress ($servername)");
    if (!$servername) $servername = N_CurrentServer();
    uuse ("grid");

    global $myconfig, $REMOTE_ADDR, $SERVER_ADDR, $SERVER_PORT, $HTTP_HOST;

    // 3 machines are involved in this: CLIENT, WORKSERVER and LINKSERVER

    if ($servername==N_CurrentServer()) { // WORKSERVER == LINKSERVER
      if ($myconfig["myip"]) {
        return $myconfig["myip"];
      } else if (N_Regexp ($SERVER_ADDR, "([0-9]*[.][0-9]*)[.][0-9]*[.][0-9]*")==N_Regexp ($REMOTE_ADDR, "([0-9]*[.][0-9]*)[.][0-9]*[.][0-9]*")) { // LAN
        if ($SERVER_PORT!=80) {
          return "$SERVER_ADDR:$SERVER_PORT";
        } else {
          return $SERVER_ADDR;
        }
      } else { // INTERNET
        return N_ExternalServerAddress ($servername);
      }
    } 
    
    $key = DFC_Key ("N_LinkServerAddress ($servername)");
    if (DFC_Exists ($key)) {
      return DFC_Read ($key);
    } else {
      $address = GRID_DetermineIP ($servername); // fetch public address and hope it works (not used by OpenIMS, but used by OSICT developpers)
      if ($address) {
        return DFC_Write ($key, $address, 1); // cache for 1 hour
      } else {
        return null;
      }
    }
  }

  function N_AllServers ()
  {
    uuse ("grid");
    $totalstatus = GRID_LoadTotalStatus ();
    foreach ($totalstatus as $servername => $dummy) {
      $result[$servername] = $servername;
    }
    return $result;   
  }

  function N_AllOnlineServers ()
  {
    uuse ("grid");
    $totalstatus = GRID_LoadTotalStatus ();
    foreach ($totalstatus as $servername => $status) {
      if ((time()-$status["laststatusupdate"])<300) {
        $result[$servername] = $servername;
      }
    }
    return $result;   
  }

  function N_CertifiedCaller ()
  {
    return true;
  }

  function N_Windows ()
  {
    global $myconfig;
    return $myconfig["windows"]=="yes";
  }

  function N_Load ($server="") {
    if (!$server) $server = N_CurrentServer();
    global $VBrowser4GetPage;
    $oldtimeout = $VBrowser4GetPage->timeout;
    $VBrowser4GetPage->setTimeout (10);
    if ($server==N_CurrentServer()) {
      uuse ("stats");
      $status = STATS_RAW_ServerStatus();
    } else {
      $status = N_GetPage (N_ServerAddress ($server)."/server-status"); 
    }
    $VBrowser4GetPage->setTimeout ($oldtimeout);
    return N_Regexp ($status, "[^0-9]([0-9]*) requests currently being processed");
  }

  function N_Doing ($server="") {
    if (!$server) $server = N_CurrentServer();
    $page = N_GetPage (N_ServerAddress ($server)."/server-status"); 
    $page = str_replace ("<b>", "", $page);
    $page = str_replace ("</b>", "", $page);
    $page = str_replace ("</font>", "", $page);
    $page = str_replace ("</td>", "", $page);
    $page = str_replace ('<font face="Arial,Helvetica" size="-1">', "", $page);

    preg_match_all ("'<tr[^>]*>([^<]*<td[^>]*>)*[^<]*</tr>'i", $page, $result1);

    $result = array();
    foreach ($result1[0] as $dummy => $string)
    {
      preg_match_all ("'[>]([^<]*)[<]'i", $string, $result2);
      if (strpos (" ".$result2[1][4], "W")!==false && strpos (" ".$result2[1][12], "POST")!==false) array_push ($result, $result2[1][12]);
      if (strpos (" ".$result2[1][4], "W")!==false && strpos (" ".$result2[1][12], "GET")!==false) array_push ($result, $result2[1][12]);
      if (strpos (" ".$result2[1][4], "W")!==false && strpos (" ".$result2[1][13], "POST")!==false) array_push ($result, $result2[1][13]);
      if (strpos (" ".$result2[1][4], "W")!==false && strpos (" ".$result2[1][13], "GET")!==false) array_push ($result, $result2[1][13]);
    }
    $load = N_Regexp ($page, "[^0-9]([0-9]*) requests currently being processed");
    return array ("load"=>$load, "request"=>$result);
  }

  function N_TerraStatus ($server="") {
    $doing = N_Doing($server);
    if ($doing["load"] && count ($doing["request"])) {
      foreach ($doing["request"] as $dummy => $url)
      { 
        if (strpos ($url, "TERRA_")) return "active";
      }
      return "not active";
    } else {
      return "unknown";
    }
  }

  function N_LoadAllows ($what="") {
    global $myconfig;
    $nload = N_Load();
    if (!$nload) {
      N_Log ("overload", "unable to determine status"); 
      return false; // unable to determine status
    }
    $allowed = array();
    if ($myconfig["loadallows"]) $allowed = $myconfig["loadallows"];
    // default values
    if (!$allowed["revive"]) $allowed["revive"] = 125;
    if (!$allowed["grid"]) $allowed["grid"] = 175;
    if (!$allowed["gate"]) $allowed["gate"] = 100;
    if (!$allowed["callmeoften"]) $allowed["callmeoften"] = 50;
    if (!$allowed["callmeoftencmos"]) $allowed["callmeoftencmos"] = 3;
    if (!$allowed["other"]) $allowed["other"] = 150;

    if ($what=="revive" || $what=="grid" || $what=="gate") {
      if ($nload > $allowed[$what]) {
        N_Log ("overload", "{$what} load>{$allowed[$what]}");
        return false;
      }
    } else if ($what=="callmeoften") {
      if ($nload > $allowed["callmeoften"]) {
        N_Log ("overload", "callmeoften load>{$allowed["callmeoften"]}");
        return false;
      }
      uuse ("stats");
      $ap = STATS_Apache_Doing ();
      if ($ap["callmeoften"] > $allowed["callmeoftencmos"]) {
        N_Log ("overload", "callmeoften cmo's>{$allowed["callmeoftencmos"]}");
        return false;
      }
    } else {
      if ($nload > $allowed["other"]) {
        N_Log ("overload", "other load>{$allowed["other"]}");
        return false;
      }
    }
    return true;
  }

  function N_QuickStatus()
  {
    global $VBrowser4GetPage;
    $VBrowser4GetPage->setTimeout (10);
    echo '<pre><font face="courier">';
    $servers = N_AllServers();
    foreach ($servers as $server)
    {
      echo $server.substr ("               ", 0, 15-strlen($server));
      N_Flush();
      $address = N_ServerAddress ($server);
      if ($address) {
        echo "<b>".N_Load ($server)."</b>&#09;"; 
        echo $address.substr ("                       ", 0, 23-strlen($address));
        echo "<a href=\"http://$address/server-status\">server-status</a> ";
        echo "<a href=\"http://$address/openims/openims.php?mode=admin&submode=maint&action=sysproc\">sysproc</a> ";
      } else {
        echo "<b>U</b>nknown&#09;";
      }
      echo "<br>";
      N_Flush();
    }
    echo '</font></pre>';
  }

  function N_PortScan ($address, $timeout=1) {
    $ports = array (
      80   => "HTTP",
      443  => "HTTPS",
      25   => "SMTP",
      110  => "POP3",
      21   => "FTP",
      22   => "SSH",
      23   => "Telnet",
      79   => "Finger",
      113  => "IDENT",
      119  => "NNTP",
      135  => "RPC",
      139  => "NetBIOS",
      143  => "IMAP",
      389  => "LDAP",
      445  => "MSFT DS",
      1002 => "ms-ils",
      1024 => "DCOM",
      1025 => "Host",
      1026 => "Host",
      1027 => "Host",
      1028 => "Host",
      1029 => "Host",
      1030 => "Host",
      1433 => "*** DANGER ***",
      1720 => "H.323 *** DANGER ***",
      1863 => "Microsoft MSN Messenger",
      3306 => "*** DANGER ***",
      3389 => "RDP",
      4661 => "eMule",
      4662 => "eMule",
      4665 => "eMule",
      4672 => "eMule",
      5000 => "UPnP *** DANGER ***",
      5800 => "VNC",
      5801 => "VNC",
      5900 => "VNC",
      5901 => "VNC",
      5900 => "VNC",
      6881 => "BitTorrent",
      6889 => "BitTorrent",
    );

    foreach ($ports as $port => $desc)
    {
      echo $port." ($desc) ";
      $fp = fsockopen($address, $port, $errno, $errstr, $timeout);
      if ($errno==0 && !$errstr) {
        echo "OPEN<br>";
      } else if ($errno==0 && $errstr=="Success") {
        echo "CLOSED (timeout $timeout"."000ms)<br>";
      } else {
        echo "ERRNO:".$errno." ERRSTR:".$errstr."<br>";
      }
      N_Flush();
    }
  }

  // Separate function that can be called through GRID 
  function N_PortScanMe ($address) {
    if ($address == $_SERVER["REMOTE_ADDR"]) {
      N_PortScan ($address);
    } else {
      echo "IP mismatch, not scanning: $address != {$_SERVER["REMOTE_ADDR"]}<br/>";
    }
  }

?>