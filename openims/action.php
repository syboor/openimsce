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



include (getenv ("DOCUMENT_ROOT")."/nkit/nkit.php");
  uuse(ims);
  $siteinfo = IMS_DetermineSite ();

  if ($genc) {
    $goto = SHIELD_Decode($genc); // url param $genc
  } else {
    $goto = N_AbsoluteUrl($goto); // url param $goto

    global $ht_mode;
    global $ht_settings;
    if ($ht_mode) {
      $base1 = "http://".$ht_settings["domain"]."/";
      $base2 = "http://".$ht_settings["domain"]."/";
    } else {  
      $base1 = "http://".getenv("HTTP_HOST") . "/";
      $base2 = "https://".getenv("HTTP_HOST") . "/";
    }
    if ((substr($goto, 0, strlen($base1)) != $base1) && (substr($goto, 0, strlen($base2)) != $base2)) {
      N_Die("EXTERNAL REDIRECT"); // To fix this error, link to action.php like this: action.php?genc=SHIELD_Encode($goto)
    }
  }

  if ($command=="setlang") { // does not require logon anymore
    $obj = &SHIELD_CurrentUserObject ();
    if ($obj) $obj["lang"] = $lang;
    global $myconfig;
    // Anynomous site visitors can choose a language too...
    if ($myconfig[$siteinfo["sitecollection"]]["ml"]["sitelanguages"][$siteinfo["site"]]) N_SetCookie("ims_lang", $lang, time()+3*365*24*3600, "/", "", (N_CurrentProtocol() == "https://"), true);

    N_Redirect ($goto);
  }
  // everything else requires logon
  SHIELD_RequireLogon  ($siteinfo["sitecollection"]);
  if ($command=="publish") {
    IMS_PublishObject ($sitecollection_id, $site_id, $object_id);
    N_Redirect ($goto);
  } else if ($command=="unpublish") {
    IMS_RecoverObject ($sitecollection_id, $site_id, $object_id);
    N_Redirect ($goto);
  } else if ($command=="publishtemplate") {
    IMS_PublishTemplate ($sitecollection_id, $template_id);
    N_Redirect ($goto);
  } else if ($command=="delete") {
    IMS_Delete ($sitecollection_id, $site_id, $object_id);
    N_Redirect ($goto);
  } else if ($command=="unpublishtemplate") {
    IMS_RecoverTemplate ($sitecollection_id, $template_id);
    N_Redirect ($goto);
  } else if ($command=="copytemplate") {
    IMS_CopyTemplate ($sitecollection_id, $template_id);
    N_Redirect ($goto);
  } else if ($command=="deletetemplate") {
    IMS_DeleteTemplate ($sitecollection_id, $template_id);
    N_Redirect ($goto);
  } else if ($command=="movestart") {
    N_SetCookie ("ims_moving", "yes", 0, "/", "", (N_CurrentProtocol() == "https://"), true);
    N_SetCookie ("ims_object_id", $object_id, 0, "/", "", (N_CurrentProtocol() == "https://"), true);
    N_Redirect ($goto);
  } else if ($command=="moveend") {
    IMS_SetLocation ($sitecollection_id, $moving_object_id, $object_id);
    N_SetCookie ("ims_moving", "no", 0, "/", "", (N_CurrentProtocol() == "https://"), true);
    N_Redirect ($goto);
  } else if ($command=="movequit") {
    N_SetCookie ("ims_moving", "no", 0, "/", "", (N_CurrentProtocol() == "https://"), true);
    N_Redirect ($goto);
  } else if ($command=="up") {
    IMS_MoveUp ($sitecollection_id, $site_id, $object_id);
    N_Redirect ($goto);
  } else if ($command=="down") {
    IMS_MoveDown ($sitecollection_id, $site_id, $object_id);
    N_Redirect ($goto);
  } else if ($command=="refresh") {
    N_Redirect ($goto);
  } else if ($command=="restore") {
    IMS_RestoreObject ($sitecollection_id, $object_id, $version_id);
    N_Redirect ($goto);
  } else if ($command=="logout") {
    N_SetCookie ("ims_logout", "yes", 0, "/", "", (N_CurrentProtocol() == "https://"), true);
    N_Redirect ($goto);
  } 
  N_Exit();
?>