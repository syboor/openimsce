<?php
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


function N_SetCookie($name, $content, $expires = 0, $path = "/", $domain = "", $only_https = false, $no_javascript = false) {
  // Slightly different default behaviour from setcookie ($path and $no_javascript).
  // Also fixes the stupid problem that the setcookie function does NOTHING when given more parameters than "expected" by that version op PHP. 
  if (version_compare(PHP_VERSION, '5.2.0') >= 1) {
    setcookie($name, $content, $expires, $path, $domain, $only_https, $no_javascript);      
  } else {
    setcookie($name, $content, $expires, $path, $domain, $only_https);      
  }
}

  foreach (array('ims_showerrors', 'ims_shownotices', 'ims_showforms', 'ims_disableflex', 'ims_debught', 'ims_disablesso', 'ims_speed', 'ims_noredirect', 'ims_cookiekey') as $cookie_var) $$cookie_var = $_COOKIE[$cookie_var];
  foreach (array('showerrors', 'shownotices', 'showforms', 'disableflex', 'debught', 'disablesso', 'speed', 'noredirect', 'notrans', 'noadvancedupload', 'key') as $url_var) $$url_var = $_GET[$url_var];
  
  $cookietimeout = time()+365*86400;  // 1 year after today

  if ( strtolower($_SERVER['HTTPS']) == 'on' || ($_SERVER["HTTP_SCHEME"] && strtolower($_SERVER["HTTP_SCHEME"]) == "https")) {
    $https_only = true;
  } else {
    $https_only = false;
  }

  // Dont cache me (for those configurations where caching is the default...)
  header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
  header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

  if (!$ims_cookiekey) {
    $ims_cookiekey = md5 ("small secret".$_SERVER['REMOTE_ADDR'].time());
    Header ("HTTP/1.1 200");
    Header ("Status: 200");
    N_SetCookie ("ims_cookiekey", $ims_cookiekey, time()+8*3600, "/", "", $https_only, true);
  }
  $thekey = $ims_cookiekey;

  if ($showerrors=="yes") {
    if ($key!=$thekey) die ("invalid key");
    $ims_showerrors = "yes";
    Header ("HTTP/1.1 200");
    Header ("Status: 200");
    N_SetCookie ("ims_showerrors", "yes", $cookietimeout, "/", "", $https_only, true);
  }
  if ($showerrors=="no") {
    if ($key!=$thekey) die ("invalid key");
    $ims_showerrors = "no";
    Header ("HTTP/1.1 200");
    Header ("Status: 200");
    N_SetCookie ("ims_showerrors", "no", $cookietimeout, "/", "", $https_only, true);
  }

  if ($shownotices=="yes") {
    if ($key!=$thekey) die ("invalid key");
    $ims_shownotices= "yes";
    Header ("HTTP/1.1 200");
    Header ("Status: 200");
    N_SetCookie ("ims_shownotices", "yes", $cookietimeout, "/", "", $https_only, true);
  }
  if ($shownotices=="no") {
    if ($key!=$thekey) die ("invalid key");
    $ims_shownotices= "no";
    Header ("HTTP/1.1 200");
    Header ("Status: 200");
    N_SetCookie  ("ims_shownotices", "no", $cookietimeout, "/", "", $https_only, true);
  }

  if ($speed=="yes") {
    if ($key!=$thekey) die ("invalid key");
    $ims_speed= "yes";
    Header ("HTTP/1.1 200");
    Header ("Status: 200");
    N_SetCookie ("ims_speed", "yes", $cookietimeout, "/", "", $https_only, true);
  }
  if ($speed=="no") {
    if ($key!=$thekey) die ("invalid key");
    $ims_speed= "no";
    Header ("HTTP/1.1 200");
    Header ("Status: 200");
    N_SetCookie ("ims_speed", "no", $cookietimeout, "/", "", $https_only, true);
  }

  if ($noredirect=="yes") {
    if ($key!=$thekey) die ("invalid key");
    $ims_noredirect= "yes";
    Header ("HTTP/1.1 200");
    Header ("Status: 200");
    N_SetCookie ("ims_noredirect", "yes", $cookietimeout, "/", "", $https_only, true);
  }
  if ($noredirect=="no") {
    if ($key!=$thekey) die ("invalid key");
    $ims_noredirect= "no";
    Header ("HTTP/1.1 200");
    Header ("Status: 200");
    N_SetCookie ("ims_noredirect", "no", $cookietimeout, "/", "", $https_only, true);
  }



  echo "<font face=\"arial\">";
  echo "<font color=ff0000><b>WAARSCHUWING: DEZE INSTELLINGEN KUNNEN DE SITE ONBRUIKBAAR MAKEN VOOR UW PC !!!</b></font><br>";
  if ($ims_showerrors=="yes") {
    echo "SHOW ERRORS STAAT <b>AAN</b> <a href=\"/nkit/debugsettings.php?showerrors=no&key=$thekey\">uit zetten</a><br>";
  } else {
    echo "SHOW ERRORS STAAT <b>UIT</b> <a href=\"/nkit/debugsettings.php?showerrors=yes&key=$thekey\">aan zetten</a><br>";
  }
  if ($ims_shownotices=="yes") {
    echo "SHOW NOTICES STAAT <b>AAN</b> <a href=\"/nkit/debugsettings.php?shownotices=no&key=$thekey\">uit zetten</a><br>";
  } else {
    echo "SHOW NOTICES STAAT <b>UIT</b> <a href=\"/nkit/debugsettings.php?shownotices=yes&key=$thekey\">aan zetten</a><br>";
  }


  if ($ims_speed=="yes") {
    echo "SPEED STAAT <b>AAN</b> <a href=\"/nkit/debugsettings.php?speed=no&key=$thekey\">uit zetten</a><br>";
  } else {
    echo "SPEED STAAT <b>UIT</b> <a href=\"/nkit/debugsettings.php?speed=yes&key=$thekey\">aan zetten</a><br>";
  }
  if ($ims_noredirect=="yes") {
    echo "NOREDIRECT STAAT <b>AAN</b> <a href=\"/nkit/debugsettings.php?noredirect=no&key=$thekey\">uit zetten</a><br>";
  } else {
    echo "NOREDIRECT STAAT <b>UIT</b> <a href=\"/nkit/debugsettings.php?noredirect=yes&key=$thekey\">aan zetten</a><br>";
  }


  echo "<br>";

  echo "<font color=ff0000><b>WARNING: THESE SETTINGS CAN MAKE THE SITE UNVIEWABLE ON YOUR PC !!!</b></font><br>";
  if ($ims_showerrors=="yes") {
    echo "SHOW ERRORS IS <b>ON</b> <a href=\"/nkit/debugsettings.php?showerrors=no&key=$thekey\">switch off</a><br>";
  } else {
    echo "SHOW ERRORS IS <b>OFF</b> <a href=\"/nkit/debugsettings.php?showerrors=yes&key=$thekey\">switch on</a><br>";
  }
  if ($ims_shownotices=="yes") {
    echo "SHOW NOTICES IS <b>ON</b> <a href=\"/nkit/debugsettings.php?shownotices=no&key=$thekey\">switch off</a><br>";
  } else {
    echo "SHOW NOTICES IS <b>OFF</b> <a href=\"/nkit/debugsettings.php?shownotices=yes&key=$thekey\">switch on</a><br>";
  }

  if ($ims_speed=="yes") {
    echo "SPEED IS <b>ON</b> <a href=\"/nkit/debugsettings.php?speed=no&key=$thekey\">switch off</a><br>";
  } else {
    echo "SPEED IS <b>OFF</b> <a href=\"/nkit/debugsettings.php?speed=yes&key=$thekey\">switch on</a><br>";
  }
  if ($ims_noredirect=="yes") {
    echo "NOREDIRECT IS <b>ON</b> <a href=\"/nkit/debugsettings.php?noredirect=no&key=$thekey\">switch off</a><br>";
  } else {
    echo "NOREDIRECT IS <b>OFF</b> <a href=\"/nkit/debugsettings.php?noredirect=yes&key=$thekey\">switch on</a><br>";
  }

  echo "<br><font color=ff0000><b>LET OP: </b></font>Debugging moet TEVENS zijn toegestaan in de machine-config, bijvoorbeeld: <code>\$myconfig[\"allowdebuggingfrom\"][] = \"{$_SERVER["REMOTE_ADDR"]}\";</code>";
  echo "<br><font color=ff0000><b>LET OP: </b></font>Debugging ALSO needs to be allowed in the machine config, e.g.: <code>\$myconfig[\"allowdebuggingfrom\"][] = \"{$_SERVER["REMOTE_ADDR"]}\";</code>";

  echo "</font>";

?>