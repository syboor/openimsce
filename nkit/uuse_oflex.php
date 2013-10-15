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



uuse ("ims");
uuse ("multilang");

// modules are either in html::openims/flex/.. or html::config/$supergroupname/flex/..
// module name: flex_$type_$name.php

function OFLEX_Types ()
{
  $result["cmsblock"] = ML("CMS functie blok", "CMS function block");
  $result["cmsmodule"] = ML("CMS module", "CMS module");
  return $result;
}

function OFLEX_Commands ($type)
{
  $result["name"] = ML("Geef leesbare naam terug", "Return readable name");
  if ($type=="cmsblock") {
    $result["content"] = ML("Geef content (HMTL) terug", "Return content (HTML)");
  } else if ($type=="cmsmodule") {
  }
}

function OFLEX_Modules ($type)
{
  $result = array();
  if (file_exists (getenv("DOCUMENT_ROOT")."/openims/flex")) $d = dir (getenv("DOCUMENT_ROOT")."/openims/flex/");
  if ($d) while ($entry = $d->read()) {
    if (substr ($entry, 0, strlen($type)+5)=="flex_".$type) {
      $entry = substr ($entry, strlen($type)+6, strlen($entry)-strlen($type)-10);
      $result[$entry] = OFLEX_Call ($type, $entry, "name");
    }
  }
  if (file_exists (getenv("DOCUMENT_ROOT")."/config/".IMS_Supergroupname()."/flex")) $d = dir (getenv("DOCUMENT_ROOT")."/config/".IMS_Supergroupname()."/flex/");
  if ($d) while ($entry = $d->read()) {
    if (substr ($entry, 0, strlen($type)+5)=="flex_".$type) {
      $entry = substr ($entry, strlen($type)+6, strlen($entry)-strlen($type)-10);
      $result[$entry] = OFLEX_Call ($type, $entry, "name");
    }
  }
  return $result;
}

function OFLEX_Call ($type, $module, $command, $input=array())
{
  $context["collection"] = IMS_Supergroupname();
  if ($type=="cmsblock" || $type=="cmsmodule") {
    $siteinfo = IMS_DetermineSite ();
    $context["site"] = $siteinfo["site"];
    $context["page"] = $siteinfo["object"];
    $context["url"] = N_MyBareURL();
    $context["user"] = SHIELD_CurrentUser (IMS_Supergroupname());
    $context["knownuser"] = ($context["user"]!="unknown");
    $context["flexdatatable"] = "ims_".$context["collection"]."_objects_flexdata_".$type."_".$module;
    $context["flexdatakey"] = $context["site"]."-".$context["page"];
    $context["flexuserdatatable"] = "ims_".$context["collection"]."_objects_flexuserdata_".$type."_".$module;
    $context["flexuserdatakey"] = $context["site"]."-".$context["page"]."-".$context["user"];
    $context["flexfiles"] = getenv("DOCUMENT_ROOT")."/".$context["collection"]."/flex/".$context["site"]."_".$context["page"]."/";
    $context["flexfilestrans"] = "\\".$context["collection"]."\\flex\\".$context["site"]."_".$context["page"]."\\";
    $flexdata = &MB_Ref ($context["flexdatatable"], $context["site"]."-".$context["page"]);
    if ($context["knownuser"]) {
      $flexuserdata = &MB_Ref ($context["flexuserdatatable"], $context["site"]."-".$context["page"]."-".$context["user"]);
    }
  }  
  if (file_exists (getenv("DOCUMENT_ROOT")."/openims/flex/flex_".$type."_".$module.".php")) {
    $file = getenv("DOCUMENT_ROOT")."/openims/flex/flex_".$type."_".$module.".php";
  }
  if (file_exists (getenv("DOCUMENT_ROOT")."/config/".IMS_Supergroupname()."/flex/flex_".$type."_".$module.".php")) {
    $file = getenv("DOCUMENT_ROOT")."/config/".IMS_Supergroupname()."/flex/flex_".$type."_".$module.".php";
  }

  return eval (N_ReadFile ($file));
}

?>