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



if (stristr (getenv("REQUEST_URI"), "/showerrors")) {
    include (getenv("DOCUMENT_ROOT")."/nkit/debugsettings.php");
    die ("");
  } else if (stristr (getenv("REQUEST_URI"), "/hideerrors")) {
    include (getenv("DOCUMENT_ROOT")."/nkit/debugsettings.php");
    die ("");
  } else if (stristr (getenv("REQUEST_URI"), "/showforms")) {
    include (getenv("DOCUMENT_ROOT")."/nkit/debugsettings.php");
    die ("");
  } else if (stristr (getenv("REQUEST_URI"), "/hideforms")) {
    include (getenv("DOCUMENT_ROOT")."/nkit/debugsettings.php");
    die ("");
  } else if (stristr (getenv("REQUEST_URI"), "/disableflex")) {
    include (getenv("DOCUMENT_ROOT")."/nkit/debugsettings.php");
    die ("");
  } else if (stristr (getenv("REQUEST_URI"), "/enableflex")) {
    include (getenv("DOCUMENT_ROOT")."/nkit/debugsettings.php");
    die ("");
  } else if (stristr (getenv("REQUEST_URI"), "/disablesso")) {
    include (getenv("DOCUMENT_ROOT")."/nkit/debugsettings.php");
    die ("");
  } else if (stristr (getenv("REQUEST_URI"), "/enablesso")) {
    include (getenv("DOCUMENT_ROOT")."/nkit/debugsettings.php");
    die ("");
  }

  // ufc turbo drive (tm)
  if ((getenv("REDIRECT_ERROR_NOTES") || $_SERVER["REDIRECT_STATUS"]=="404"))
  {
    if (is_file ($path = str_replace ("/ufc/rapid", getenv("DOCUMENT_ROOT"), getenv("REQUEST_URI")))) {
      if (strpos ($path, ".gif") || strpos ($path, ".jpg")) {
        Header ("Status: 200"); // fastcgi
        Header ("HTTP/1.1 200");
        Header("Expires: ".gmdate("D, d M Y H:i:s", time()+24*3600) . " GMT");
        if (strpos ($path, ".gif")) {
          Header("Content-type: image/gif");
        } else {
          Header("Content-type: image/jpeg");
        }
        $fp = fopen ($path, "rb");
        while (!feof($fp) && !$stop) {
          $b = fread ($fp, 262144);
          if (substr ($b, 0, 32)=="1f70bee424cb6a75"."a93b2472e082ff51") $stop = true; // VVFILE detection
          if (!$stop) echo $b;
        }
        fclose ($fp);
        if (!$stop) exit;
      }
    }

    if (!strpos (strtolower (getenv("REQUEST_URI")), "ufc")) {
      include (getenv ("DOCUMENT_ROOT"). "/nkit/nkit.php");
      if (N_FileExists ("html::$path")) { // usually we get here because of 32k fix in combination with a direct reference to a file
        Header ("Status: 200"); // fastcgi
        Header ("HTTP/1.1 200");
        Header("Expires: ".gmdate("D, d M Y H:i:s", time()+24*3600) . " GMT");
        N_TransferFile ("html::$path");
        exit;
      }
      foreach (array (".css", ".js", ".gif", ".jpg", ".png") as $ext) {
        if (strpos (strtolower (getenv("REQUEST_URI")), $ext)) {
          exit;
        }
      }
    }
  }

  include (getenv ("DOCUMENT_ROOT"). "/nkit/nkit.php");
  N_OpenIMSCE_AutoconfRedirect();

  uuse ("ims");
  if (getenv("REDIRECT_ERROR_NOTES") || $_SERVER["REDIRECT_STATUS"]=="404") { // unknown url like "www.somesite.com/somthing/unknown
    $siteinfo = IMS_DetermineSite ();
    $shorturl = substr (getenv("REQUEST_URI"), 1);
    if (!function_exists ("INDEX_Redirector")) {
       $internal_component = FLEX_LoadImportableComponent ("lowlevel", "8b58b3af72bed068de59a5038d77cd13");
       $internal_code = $internal_component["code"];
       eval ($internal_code);
    }
    INDEX_Redirector (getenv("HTTP_HOST"), $shorturl);
    $object = MB_Ref ("ims_".$siteinfo["sitecollection"]."_shorturls", $shorturl);
    if ($object) {
      $long_per_site = $object["url_per_site"][$siteinfo["site"]]["long"];
      if ( $long_per_site )
        N_Redirect ($long_per_site, 301);
      else
        N_Redirect ($object["long"], 301);
    } else if (stristr (getenv("REQUEST_URI"), "/ufc")) {
      uuse ("ufc");
      UFC_HandleEverything();
    } else if (stristr (getenv("REQUEST_URI"), "/cms")) {
      N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST")."/openims/openims.php?mode=cms");
    } else if (stristr (getenv("REQUEST_URI"), "/ems")) {
      N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST")."/openims/openims.php?mode=ems");
    } else if (stristr (getenv("REQUEST_URI"), "/bpms")) {
      N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST")."/openims/openims.php?mode=bpms");
    } else if (stristr (getenv("REQUEST_URI"), "/dbm")) {
      N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST")."/openims/openims.php?mode=dbm");
    } else if (stristr (getenv("REQUEST_URI"), "/pms")) {
      N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST")."/openims/openims.php?mode=ps");
    } else if (stristr (getenv("REQUEST_URI"), "/ps")) {
      N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST")."/openims/openims.php?mode=ps");
    } else if (stristr (" ".getenv("REQUEST_URI"), "theclq") || stristr (" ".getenv("HTTP_HOST"), "theclq")) {
      Header ("Status: 200"); // fastcgi
      Header ("HTTP/1.1 200");
      include (getenv("DOCUMENT_ROOT")."/theclq/index.php");
    } else if (stristr (getenv("REQUEST_URI"), "/portal") || stristr (getenv("REQUEST_URI"), "/portaal")) {
      Header ("Status: 200"); // fastcgi
      Header ("HTTP/1.1 200");
      include (getenv("DOCUMENT_ROOT")."/openims/portal.php");
    } else if (stristr (getenv("REQUEST_URI"), "/dms")) {
      global $myconfig;
      if($myconfig[$siteinfo["sitecollection"]]["dmsurl"]) {
        $dmslink = $myconfig[$siteinfo["sitecollection"]]["dmsurl"];
      } else {
        $dmslink = "/openims/openims.php?mode=dms";
      }
      N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST").$dmslink);
    } else if (stristr (getenv("REQUEST_URI"), "/adm")) {
      N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST")."/openims/openims.php?mode=admin");
    } else if (stristr (getenv("REQUEST_URI"), "/concept")) {
      N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST")."/openims/preview.php");
    } else if (stristr (getenv("REQUEST_URI"), "/preview")) {
      N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST")."/openims/preview.php");
    } else if (stristr (getenv("REQUEST_URI"), "/dev")) {
      N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST")."/private/index.php");
    } else if (stristr (getenv("REQUEST_URI"), "/fss")) {
      N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST")."/openims/openfss.php");
    } else { // determine site and show its homepage
      global $myconfig;
      if ($myconfig["custom404"]) {
        N_Redirect ($myconfig["custom404"], 404);
      } else {
        $siteinfo = IMS_DetermineSite ();  
        if ($siteinfo) {
          $sgn = $siteinfo["sitecollection"];
          if (count($myconfig[$sgn]["products"]) == 1 && $myconfig[$sgn]["products"][0] == "fss") {
            N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST")."/openims/openfss.php");
          } else {
            $site = MB_Ref ("ims_sites", $siteinfo["site"]);
            $homepage = $site["homepage"];
            include (N_CleanPath ("html::"."/".$siteinfo["site"]."/$homepage.php"));
          }
        }
      }
    }
  } else { // known url like "www.somesite.com"
    if (!function_exists ("INDEX_Redirector")) {
       $internal_component = FLEX_LoadImportableComponent ("lowlevel", "8b58b3af72bed068de59a5038d77cd13");
       $internal_code = $internal_component["code"];
       eval ($internal_code);
    }
    INDEX_Redirector (getenv("HTTP_HOST"), "");
    // determine site and show its homepage
    $siteinfo = IMS_DetermineSite ();
    if ($siteinfo) {
      $sgn = $siteinfo["sitecollection"];
      if (count($myconfig[$sgn]["products"]) == 1 && $myconfig[$sgn]["products"][0] == "fss") {
        N_Redirect (N_CurrentProtocol().getenv("HTTP_HOST")."/openims/openfss.php");
      } else {
        $site = MB_Ref ("ims_sites", $siteinfo["site"]);
        $homepage = $site["homepage"];
        include (N_CleanPath ("html::"."/".$siteinfo["site"]."/$homepage.php"));
        die (""); // N_Exit has alreade been called
      }
    }
  }
  N_Exit();

?>