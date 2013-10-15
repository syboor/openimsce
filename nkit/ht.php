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


if (!function_exists('N_SetCookieHt')) {
  function N_SetCookieHt($name, $content, $expires = 0, $path = "/", $domain = "", $only_https = false, $no_javascript = false) {
    // Slightly different default behaviour from setcookie ($path and $no_javascript).
    // Also fixes the stupid problem that the setcookie function does NOTHING when given more parameters than "expected" by that version op PHP. 
    if (version_compare(PHP_VERSION, '5.2.0') >= 1) {
      setcookie($name, $content, $expires, $path, $domain, $only_https, $no_javascript);      
    } else {
      setcookie($name, $content, $expires, $path, $domain, $only_https);      
    }
  }
}

$nocachecookie = md5($_SERVER["SCRIPT_NAME"]);  

  if ( strtolower($_SERVER['HTTPS']) == 'on' || ($_SERVER["HTTP_SCHEME"] && strtolower($_SERVER["HTTP_SCHEME"]) == "https")) {
    $https_only = true;
  } else {
    $https_only = false;
  }

  if (!$_GET && !$_POST && $_COOKIE["ims_preview"]!="yes" && $_COOKIE["ims_showerrors"]!="yes" && $_COOKIE[$nocachecookie]!="yes") { // static caching allowed?
    $file = getenv ("DOCUMENT_ROOT")."/tmp/ht/pages/".$sitecollection_id."/".$site_id."/".$object_id.".html";
    if (file_exists ($file)) {
      header ("Content-Type: text/html; charset=ISO-8859-1");
      if (strtolower($_SERVER['HTTPS']) == 'on')
        $url = "https://" . getenv("HTTP_HOST") . getenv("SCRIPT_NAME");
      else
        $url = "http://" . getenv("HTTP_HOST") . getenv("SCRIPT_NAME");
      N_SetCookieHt ("ims_myurl_".str_replace (".", "_", strtolower (getenv("HTTP_HOST"))), $url, time()+100000, "/", "", $https_only, true);
      N_SetCookieHt ("ims_myurl", $url, time()+100000, "/", "", $https_only, true);
      $fd = fopen($file, 'r');
      if ($fd) {
        while (!feof($fd)) {
          echo fread ($fd, 100000);
        }
        fclose($fd);
      }
      $htcacheing=true;
      if ($_COOKIE["ims_debught"]=="yes") echo "[CACHED COPY - STATIC CACHE]";
    } // xxx.php handles pages which are not cached at all
  } else if ($_COOKIE["ims_preview"]!="yes" && $_COOKIE["ims_showerrors"]!="yes" && $_COOKIE[$nocachecookie]!="yes") { // dynamic caching allowed ?
    $cachekey = md5 (serialize ($_POST).serialize($_GET));
    $file = getenv ("DOCUMENT_ROOT")."/tmp/ht/pages/".$sitecollection_id."/".$site_id."/$object_id/$cachekey.html";
    if (file_exists ($file)) {
      header ("Content-Type: text/html; charset=ISO-8859-1");
      if (strtolower($_SERVER['HTTPS']) == 'on')
        $url = "https://" . getenv("HTTP_HOST") . getenv("SCRIPT_NAME");
      else
        $url = "http://" . getenv("HTTP_HOST") . getenv("SCRIPT_NAME");
      N_SetCookieHt ("ims_myurl_".str_replace (".", "_", strtolower (getenv("HTTP_HOST"))), $url, time()+100000, "/", "", $https_only, true);
      N_SetCookieHt ("ims_myurl", $url, time()+100000, "/", "", $https_only, true);
      $fd = fopen($file, 'r');
      if ($fd) {
        while (!feof($fd)) {
          echo fread ($fd, 100000);
        }
        fclose($fd);
      }
      if ($_COOKIE["ims_debught"]=="yes") echo "[CACHED COPY - DYNAMIC CACHE]"; 
    } else {
      include (getenv ("DOCUMENT_ROOT")."/nkit/nkit.php");
      uuse ("ims");
      uuse ("ht");
      if (HT_CachableDynamic ($sitecollection_id, $object_id)) {
        ob_start();
        N_SetCookieHt ("ims_myurl", N_MyFullURL (), time()+100000, "/", "", $https_only, true);
        echo IMS_SuperMixer ($sitecollection_id, $site_id, $object_id, "generate_dynamic_page");
        $content = ob_get_contents();
        ob_end_clean();
        echo $content;
        N_WriteFile ($file, $content);
        N_Log ("hightraffic", "Update dynamic cache COL:$sitecollection_id PAGE:$object_id CACHEKEY:$cachekey");
        N_Exit();
        if ($_COOKIE["ims_debught"]=="yes") echo "[NOT CACHED COPY - UPDATE DYNAMIC CACHE]"; 
      } else {
        N_SetCookieHt ("ims_myurl", N_MyFullURL (), time()+100000, "/", "", $https_only, true);
        echo IMS_SuperMixer ($sitecollection_id, $site_id, $object_id, "generate_dynamic_page");
        N_Exit();
        if ($_COOKIE["ims_debught"]=="yes") echo "[NOT CACHED COPY]";
      }
    }
    $htcacheing=true;
  } // xxx.php handles preview mode and show errors mode
?>