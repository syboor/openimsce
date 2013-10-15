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


uuse ("multilang"); // must be loaded before ims, otherwise the world explodes or not
uuse ("ims");

// modules are either in html::openims/flex/.. or html::config/$supergroupname/flex/..
// module name: flex_$type_$id.ubd

// ML documentation note: use parameters (%1, %2) to prevent the translator from translating variable names and other stuff.

UUSE_DO ('
  FLEX_LoadSupportFunctions (IMS_SuperGroupName());

  // Call IMS_XssFilter here, because it needs to happen after FLEX_loadSupportFunctions
  if ($_SERVER["SCRIPT_NAME"] != "/openims/catchall.php") {
    // catchall does its own call to IMS_XssFilter after faking the script name
    IMS_XssFilter();
  }
');

function FLEX_Types ()
{
  $flex["typename"] = ML ("CMS componenten", "CMS components");
    $flex["minimanual"] = 
      '<b>' . ML('Mini handleiding', 'Mini manual') . '</b><br>' .
      ML('Invoer content generator: %1', 'Input content generator: %1', '$context') . '<br>' .
      ML('Uitvoer content generator: %1', 'Output content generator: %1', '$content') . '<br>';
    $flex["fields"]["name"]["type"] = "string";
    $flex["fields"]["name"]["name"] = ML("Naam", "Name");
    $flex["fields"]["tag"]["type"] = "string";
    $flex["fields"]["tag"]["name"] = ML("Markeertekst", "Tag");
    $flex["fields"]["code_content"]["type"] = "verybigtext";
    $flex["fields"]["code_content"]["name"] = ML("Content generator", "Content generator");
    $xmlsitemap = false;
    global $myconfig;
    $sitelist = MB_Query ("ims_sites", '$record["sitecollection"]=="'.IMS_SuperGroupName().'"');
    foreach ($sitelist as $site_id => $dummy) {
      if ($myconfig[IMS_SuperGroupName()][$site_id]["xmlsitemap"] == "yes") $xmlsitemap = true;
    }
    if ($xmlsitemap) {
      $flex["fields"]["code_xmlsitemap"]["type"] = "verywidetext";
      $flex["fields"]["code_xmlsitemap"]["name"] = ML("XML Sitemap", "XML Sitemap");
      $flex["minimanual"] .= 
        ML('Invoer XML Sitemap: %1', 'Input XML Sitemap: %1', '$context') . '<br>' .
        ML('Uitvoer XML Sitemap: %1', 'Output XML Sitemap: %1', '$urllist') . '<br>';
    }
    $flex["minimanual"] .= 
      ML('Klik %1 voor de uitgebreide handleiding.', 'Click %1 to read the detailed manual', '<a target="_blank" href="http://doc.openims.com/openimsdoc_com/aad265660cfe075d0b986b3212a8e2a2.php">'.ML("hier","here").'</a>');

    $ret["cmsblock"] = $flex; 
    $flex = array();

  $flex["typename"] = ML ("DMS assistenten", "DMS wizards");
    $flex["minimanual"] = 
      '<b>' . ML('Mini handleiding', 'Mini manual') . '</b><br>' .
      ML('Conditie: Kan %1 op true zetten om de assistent te tonen', 'Condition: Set %1 to true to show the wizard', '$result') . '<br>' .
      ML('URL Generator: Plaats de assistent url in %1', 'URL Generator: Use %1 for the wizard URL', '$result') . '<br>' .
      ML('Klik %1 voor de uitgebreide handleiding.', 'Click %1 to read the detailed manual', '<a target="_blank" href="http://doc.openims.com/openimsdoc_com/aad265660cfe075d0b986b3212a8e2a2.php">'.ML("hier","here").'</a>');
    $flex["fields"]["name"]["type"] = "strml1";
    $flex["fields"]["name"]["name"] = ML("Naam", "Name");
    $flex["fields"]["title"]["type"] = "strml1";
    $flex["fields"]["title"]["name"] = ML("Titel", "Title");
    $flex["fields"]["iconurl"]["name"] = ML("Icoon (url)", "Icon (url)");
    $flex["fields"]["iconurl"]["type"] = "string";
    $flex["fields"]["sort"]["type"] = "string";
    $flex["fields"]["sort"]["name"] = ML("Sorteerwaarde", "Sort value");
    $flex["fields"]["block"]["name"] = ML("Blok", "Block");
    $flex["fields"]["block"]["type"] = "list";
    $flex["fields"]["block"]["values"][ML("Acties","Actions")] = "actions";
    $flex["fields"]["block"]["values"][ML("Workflow","Workflow")] = "workflow";
    $flex["fields"]["block"]["values"][ML("Assistenten","Wizards")] = "wizards";
    $flex["fields"]["block"]["values"][ML("Assistenten, alleen indien een document actief is","Wizards, only if a document is active")] = "wizards_active";
    $flex["fields"]["block"]["values"][ML("Geselecteerde documenten","Selected files")] = "files";
    $flex["fields"]["block"]["values"][ML("Nieuw document","New document")] = "newdoc";
    $flex["fields"]["block"]["values"][ML("Niet tonen","Do not show")] = "noshow";
    $flex["fields"]["block"]["default"] = "wizards";
    $flex["fields"]["submode"]["name"] = ML("Submodes", "Submodes");
    $flex["fields"]["submode"]["type"] = "list";
    $flex["fields"]["submode"]["method"] = "multi";
    $flex["fields"]["submode"]["show"] = "visible";
    $flex["fields"]["submode"]["values"][ML("Documenten","Documents")] = "documents";
    $flex["fields"]["submode"]["values"][ML("Dossieroverzicht","Case overview")] = "cases";
    $flex["fields"]["submode"]["values"][ML("Toegewezen","Assigned")] = "alloced";
    $flex["fields"]["submode"]["values"][ML("Recent gewijzigd","Recently changed")] = "recent";
    $flex["fields"]["submode"]["values"][ML("In behandeling","In preview")] = "activities";
    $flex["fields"]["submode"]["values"][ML("Zoeken","Search")] = "search";
    $flex["fields"]["submode"]["values"][ML("Gekoppelde documenten","Related documents")] = "related";
    //$flex["fields"]["incaseoverview"]["type"] = "yesno";
    //$flex["fields"]["incaseoverview"]["name"] = ML("Toon in dossieroverzicht", "Show in cases overview");
    $flex["fields"]["code_condition"]["type"] = "verywidetext";
    $flex["fields"]["code_condition"]["name"] = ML("Conditie", "Condition");
    $flex["fields"]["code_urlgenerator"]["type"] = "verybigtext";
    $flex["fields"]["code_urlgenerator"]["name"] = ML("URL generator", "URL generator");
    $ret["dmswizard"] = $flex;

    

  $flex["typename"] = ML ("Speciale en ondersteunende functionaliteit", "Special and support logic");
    $flex["fields"]["name"]["type"] = "string";
    $flex["fields"]["name"]["name"] = ML("Naam", "Name");
    $flex["fields"]["code"]["type"] = "verybigtext";
    $flex["fields"]["code"]["name"] = ML("Code", "Code");
    $ret["support"] = $flex;
    $flex = array();

  

  return $ret;
}

function FLEX_CreateLink ($type, $specs, $id)
{
  foreach ($specs as $key => $value) 
  {
    if (substr ($key, 0, 5)=="code_" || $key=="code") {
      $code = '// '.ML("Automatische link", "Automatic link").chr(13).chr(10);
      $code .= '$internal_component = FLEX_LoadImportableComponent ("'.$type.'", "'.$id.'");'.chr(13).chr(10);
      $code .= '$internal_code = $internal_component["'.$key.'"];'.chr(13).chr(10);
      $code .= 'eval ($internal_code);'.chr(13).chr(10);
      $newspecs[$key] = $code;
    } else {
      $newspecs[$key] = $specs[$key];
    }
  }
  return $newspecs;
}

function FLEX_LLCheckCode ($base, $comp)
{
  global $myconfig;
  if ($myconfig["allowhugeerrorsincodepleasedonotuseme"] == "yes") return;
  
  preg_match_all ('|[ ]*function[ ][ ]*([^ $\n\r]*)[ ]*[(]([^)]*)[)]|', $base, $basematches);
  preg_match_all ('|[ ]*function[ ][ ]*([^ $\n\r]*)[ ]*[(]([^)]*)[)]|', $comp, $compmatches);
  foreach ($compmatches[1] as $id1 => $fun1) {
    foreach ($basematches[1] as $fun2) {
      if (strtolower ($fun1) == strtolower ($fun2)) FORMS_ShowError ("", "Function $fun2 is already defined");
    }
    foreach ($compmatches[1] as $id2 => $fun3) {
      if ($id1 != $id2 && strtolower ($fun1) == strtolower ($fun3)) FORMS_ShowError ("", "Function $fun3 is defined twice");
    }
  }
}

function FLEX_CheckCode ($supergroupname, $type, $id, $specs)
{
  $flextypes = FLEX_Types();
  foreach ($flextypes as $typeid => $cspecs) {
    $modules = FLEX_LocalComponents ($supergroupname, $typeid);
    if (is_array ($modules)) foreach ($modules as $cid => $modulespecs) {
      $data = FLEX_LoadLocalComponent ($supergroupname, $typeid, $cid);
      if ($id != $cid) $all .= serialize ($data);
    }
  }
  FLEX_LLCheckCode ($all, serialize($specs));
}

function FLEX_SaveLocalComponent ($supergroupname, $type, $id, $specs)
{
  FLEX_CheckCode ($supergroupname, $type, $id, $specs);
  $oldspecs = unserialize (N_ReadFile ("html::config/".$supergroupname."/flex/flex_".$type."_".$id.".ubd"));
  N_Log ("flexedit", SHIELD_CurrentUserName()." FLEX_SaveLocalComponentOLD ($supergroupname, $type, $id, ...)", N_Object2XML ($oldspecs));
  N_Log ("flexedit", SHIELD_CurrentUserName()." FLEX_SaveLocalComponent ($supergroupname, $type, $id, ...)", N_Object2XML ($specs));
  N_Debug ("FLEX_SaveLocalComponent ($supergroupname, $type, $id, ...)");
  if (!$id) $id = N_GUID();
  N_WriteFile ("html::config/".$supergroupname."/flex/flex_".$type."_".$id.".ubd", serialize ($specs));
  FLEX_RepairCache();
}

function FLEX_LoadLocalComponent ($supergroupname, $type, $id)
{
  N_Debug ("FLEX_LoadLocalComponent ($supergroupname, $type, $id)");
//  return unserialize (N_ReadFile ("html::config/".$supergroupname."/flex/flex_".$type."_".$id.".ubd"));
  $all = FLEX_LoadCache ($supergroupname);
  return $all["flex_".$type."_".$id.".ubd"]; 
}

function FLEX_DeleteLocalComponent ($supergroupname, $type, $id)
{
  $specs = N_ReadFile ("html::config/".$supergroupname."/flex/flex_".$type."_".$id.".ubd");
  $specs = unserialize($specs);
  N_Log ("flexedit", SHIELD_CurrentUserName()." DELETE - FLEX_SaveLocalComponent ($supergroupname, $type, $id, ...)", N_Object2XML ($specs));

  N_WriteFile ("html::config/".$supergroupname."/flex/flex_".$type."_".$id.".ubd", serialize (null));
  FLEX_RepairCache();
}

function FLEX_LoadImportableComponent ($type, $id)
{
  N_Debug ("FLEX_LoadImportableComponent ($type, $id)");
//  return unserialize (N_ReadFile ("html::openims/flex/flex_".$type."_".$id.".ubd"));
  $all = FLEX_LoadCache ("importable");
  if (!($all["flex_".$type."_".$id.".ubd"])) { 
    FLEX_RepairCache ();
    $all = FLEX_LoadCache ("importable");
  }
  return $all["flex_".$type."_".$id.".ubd"];
}

function FLEX_SaveImportableComponent ($type, $id, $specs)
{
  N_Log ("flexedit", SHIELD_CurrentUserName()." FLEX_SaveImportableComponent ($type, $id, ...)", N_PrettyXML (N_Object2XML ($specs)));
  if (!$id) $id = N_GUID();
  N_WriteFile ("html::openims/flex/flex_".$type."_".$id.".ubd", serialize ($specs));
  global $imsbuild;
  N_WriteFile (getenv("DOCUMENT_ROOT")."/nkit/build.php", '<? global $imsbuild; $imsbuild="'.($imsbuild+1).'"; ?>');
  N_WriteFile (getenv("DOCUMENT_ROOT")."/nkit/build.js", "document.write('".($imsbuild+1)."');");
  FLEX_RepairCache();
}

function FLEX_DeleteImportableComponent ($type, $id)
{
  N_WriteFile ("html::openims/flex/flex_".$type."_".$id.".ubd", serialize (null));
  FLEX_RepairCache();
}

function FLEX_LocalComponents ($supergroupname, $type)
{
  N_Debug ("FLEX_LocalComponents ($supergroupname, $type)");
  global $FLEX_LocalComponents;
  if ($FLEX_LocalComponents[$supergroupname][$type]["loaded"]) {
    $result = $FLEX_LocalComponents[$supergroupname][$type]["result"];
  } else {
    $result = array();
    $prefix = "flex_".$type."_";
    $all = FLEX_LoadCache ($supergroupname);
    foreach ($all as $file => $comp)
    {
      if (substr ($file, 0, strlen ($prefix))==$prefix) {
        $id = substr ($file, strlen ($prefix), strlen($file)-strlen ($prefix)-4);
        if ($comp) $result[$id] = $comp;
      }
    } 
    
    if (count ($result)) {
      foreach ($result as $id => $specs) {
        $tmp[$id] = $specs["name"];
      }
      asort ($tmp);
      foreach ($tmp as $id => $dummy) {
        $result2[$id] = $result[$id];
      }
      $result = $result2;
    }
    $FLEX_LocalComponents[$supergroupname][$type]["result"] = $result;
    $FLEX_LocalComponents[$supergroupname][$type]["loaded"] = "yes";
  }
  return $result;
}

function FLEX_LocalComponents_OLD ($supergroupname, $type)
{
  N_Debug ("FLEX_LocalComponents ($supergroupname, $type)");
  global $FLEX_LocalComponents;
  if ($FLEX_LocalComponents[$supergroupname][$type]["loaded"]) {
    $result = $FLEX_LocalComponents[$supergroupname][$type]["result"];
  } else {
    $result = array();
    $prefix = "flex_".$type."_";
    if (is_dir (getenv("DOCUMENT_ROOT")."/config/".$supergroupname."/flex/")) {
      $dh = opendir (getenv("DOCUMENT_ROOT")."/config/".$supergroupname."/flex/");
      while ($file = readdir ($dh)) {
        if (substr ($file, 0, strlen ($prefix))==$prefix && substr($file, strlen($file)-4, 4)==".ubd") {
          $id = substr ($file, strlen ($prefix), strlen($file)-strlen ($prefix)-4);
          $comp = FLEX_LoadLocalComponent ($supergroupname, $type, $id);
          if ($comp) $result[$id] = $comp;
        }
      }
    }
    if (count ($result)) {
      foreach ($result as $id => $specs) {
        $tmp[$id] = $specs["name"];
      }
      asort ($tmp);
      foreach ($tmp as $id => $dummy) {
        $result2[$id] = $result[$id];
      }
      $result = $result2;
    }
    $FLEX_LocalComponents[$supergroupname][$type]["result"] = $result;
    $FLEX_LocalComponents[$supergroupname][$type]["loaded"] = "yes";

  }
  return $result;
}

function FLEX_LoadAllLowLevelFunctions ()
{
  global $ims_disableflex;
  if ($ims_disableflex=="yes") return;
  global $FLEX_LoadAllLowLevelFunctions;
  if ($FLEX_LoadAllLowLevelFunctions!="loaded") {
    $FLEX_LoadAllLowLevelFunctions = "loaded";
/*
    $list = glob (N_CleanPath ("html::/config/*"));
    foreach ($list as $dir) {
      if (is_dir ($dir)) {
        $supergroupname = strrev (N_KeepBefore (strrev ($dir), "/"));
        $all = FLEX_LocalComponents ($supergroupname, "lowlevel");
        foreach ($all as $id => $specs) {
          eval ($specs["code"]);
        }
      }
    }    
*/
    $all = FLEX_LoadCache ("lowlevel");
    foreach ($all as $dummy => $specs) {
      N_Debug("FLEX_LoadAllLowLevelFunctions: about to eval component " . $specs["name"] . " ($id)"); // goed idee (zo op de zondag) :-)
      N_PMLog ("pmlog_eval", "FLEX_LoadAllLowLevelFunctions", $specs["code"]);
      eval ($specs["code"]);
    }
  }
}

function FLEX_LoadSupportFunctions ($supergroupname)
{
  global $ims_disableflex;
  if ($ims_disableflex=="yes") return;

  global $busycalling_flexloadsupportfunctions; 
    // Prevent recursion (recursion may occur when support components call certain OpenIMS functions "outside function context", ie. a component uses SHIELD_CurrentUser to decide which importable skin component to load
  if (!$busycalling_flexloadsupportfunctions) {
    $busycalling_flexloadsupportfunctions = true;
    global $FLEX_LoadSupportFunctions;
    if ($FLEX_LoadSupportFunctions[$supergroupname]!="loaded") {
      $all = FLEX_LocalComponents ($supergroupname, "support");
      foreach ($all as $id => $specs) {
// echo N_XML2HTML ($specs["code"]);
        N_Debug("FLEX_LoadSupportFunctions: about to eval component " . $specs["name"] . " ($id)");
        N_PMLog ("pmlog_eval", "FLEX_LoadSupportFunctions ($supergroupname)", $specs["code"]);
        eval ($specs["code"]);
      }
      $FLEX_LoadSupportFunctions[$supergroupname] = "loaded";
    } 
    $busycalling_flexloadsupportfunctions = false;
  }

}

function FLEX_ImportableComponents ($type)
{
  $result = array();
  $prefix = "flex_".$type."_";
  if (is_dir (getenv("DOCUMENT_ROOT")."/openims/flex/")) {
    $dh = opendir (getenv("DOCUMENT_ROOT")."/openims/flex/");
    while ($file = readdir ($dh)) {
      if (substr ($file, 0, strlen ($prefix))==$prefix && substr($file, strlen($file)-4, 4)==".ubd") {
        $id = substr ($file, strlen ($prefix), strlen($file)-strlen ($prefix)-4);
//        $comp = FLEX_LoadImportableComponent ($type, $id);
        $comp = unserialize (N_ReadFile ("html::openims/flex/flex_".$type."_".$id.".ubd"));
        if ($comp) $result[$id] = $comp;
      }
    }
  }
  if (count ($result)) {
    foreach ($result as $id => $specs) {
      $tmp[$id] = $specs["name"];
    }
    asort ($tmp);
    foreach ($tmp as $id => $dummy) {
      $result2[$id] = $result[$id];
    }
    $result = $result2;
  }
  return $result;
}

function FLEX_ToCore ($supergroupname, $type, $id)
{
  $specs = FLEX_LoadLocalComponent ($supergroupname, $type, $id);
  $id = N_GUID();
  N_WriteFile ("html::openims/flex/flex_".$type."_".$id.".ubd", serialize ($specs));   
  FLEX_RepairCache();
}

function FLEX_Call ($supergroupname, $type, $id, $command, $input=array())
{
  global $flexcounters;
  $flex_id = $id; // just in case custom code overwrites it
  $flex_type = $type;
  $flex_command = $command;
  FLEX_LoadSupportFunctions ($supergroupname);
  $all = FLEX_LocalComponents ($supergroupname, $type);  
  $specs = $flex_specs = $all[$id];  
  if ($type=="cmsblock" || $type=="cmsmodule") {
    $siteinfo = IMS_DetermineSite ();
    $context["site"] = $siteinfo["site"];
    $context["page"] = $siteinfo["object"];
    $context["collection"] = $supergroupname;
    $context["url"] = N_MyBareURL();
    $context["user"] = SHIELD_CurrentUser (IMS_Supergroupname());
    $context["knownuser"] = ($context["user"]!="unknown");
    $context["flexdatatable"] = "ims_".$context["collection"]."_objects_flexdata_".$type."_".$module;
    $context["flexdatakey"] = $context["site"]."-".$context["page"].$flexcounters["$type$id"];
    $context["flexcookiekey"] = $context["site"]."_".$context["page"].$flexcounters["$type$id"];
    $context["flexuserdatatable"] = "ims_".$context["collection"]."_objects_flexuserdata_".$type."_".$module;
    $context["flexuserdatakey"] = $context["site"]."-".$context["page"]."-".$context["user"].$flexcounters["$type$id"];
    $context["flexfiles"] = N_CleanPath ("html::"."/".$context["collection"]."/flex/".$context["site"]."_".$context["page"].$flexcounters["$type$id"]."/");
    $context["flexfilestrans"] = "\\".$context["collection"]."\\flex\\".$context["site"]."_".$context["page"].$flexcounters["$type$id"]."\\";
    $flexcounters["$type$id"]++;
    $context["flexusagecount"] = $flexcounters["$type$id"];
    global $flexparams;
    if ($flexparams) {
      $context ["flexparams"] = $flexparams;
      $context ["flexparams"] = str_replace(chr(10)," ",$context ["flexparams"]);
      $context ["flexparams"] = str_replace(chr(13)," ",$context ["flexparams"]);
      $context ["flexparams"] = str_replace("  "," ",$context ["flexparams"]);
    }
    $flexdata = &MB_Ref ($context["flexdatatable"], $context["flexdatakey"]);
    if ($context["knownuser"]) {
      $flexuserdata = &MB_Ref ($context["flexuserdatatable"], $context["flexuserdatakey"]);
    }
    N_Debug("FLEX_Call: about to eval component " . $specs["name"] . " ($id) ($command)");
    if ($command) {
      if ($command == "xmlsitemap") {
        $user_id = SHIELD_CurrentUser(IMS_SuperGroupName());
        SHIELD_SimulateUser("unknown");
        $context["user"] = "unknown";
        $context["knownuser"] = false;
        unset($flexuserdata);
        global $new;
        $old = $new;
        $new = true; // force site mode
        $urllist = false;
        eval ($specs["code_".$command]);
        SHIELD_SimulateUser($user_id);
        $new = $old;        
        return $urllist;
      } else {
        eval ($specs["code_".$command]);
      }
    } else {
      eval ($specs["code"]);
    }

    global $myconfig;
    if ($flex_command == "content" && $flex_specs["code_xmlsitemap"] && $myconfig[IMS_SuperGroupName()][$siteinfo["site"]]["xmlsitemap"] == "yes") {
      $flexcounters["{$flex_type}$id"]--;
      IMS_XmlSitemap_UpdateFlexUrls(IMS_SuperGroupName(), $siteinfo["site"], $siteinfo["object"], $flex_id);
      $flexcounters["{$flex_type}$id"]++;
    }

    return $content;
  }  
}

function FLEX_LoadCache ($col)
{
  N_Debug ("FLEX_LoadCache ($col)");
  if ($col=="unknown") return array();
  FLEX_InitCache ();
  global $flex_loadcache, $flex_loadcache_loaded;
  if (!$flex_loadcache_loaded[$col]) {
    $d = N_ReadFile ("html::/tmp/flexcache/$col.dat");
    if ($d) {
      $dat = unserialize ($d);
    } else {
      $dat = array();
    }
    $flex_loadcache[$col] = $dat;
    $flex_loadcache_loaded[$col] = "yes";
  }
  return $flex_loadcache[$col];
}

function FLEX_InitCache ()
{
  if (!file_exists (N_CleanPath ("html::/tmp/flexcache/init.dat"))) {
    FLEX_RepairCache ();
    N_WriteFile ("html::/tmp/flexcache/init.dat", "yes");
  }
}

function FLEX_RepairCache ()

{
  global $FLEX_RepairCache;
  if ($FLEX_RepairCache!="busy") {
    $FLEX_RepairCache = "busy";
    $list = glob (N_CleanPath ("html::/config/*"));
    foreach ($list as $dir) {
      if (is_dir ($dir)) {
        $supergroupname = strrev (N_KeepBefore (strrev ($dir), "/"));
        $all[$supergroupname] = array(); // Fix bug regarding not being able to delete last component in sitecollection
        if (is_dir (getenv("DOCUMENT_ROOT")."/config/".$supergroupname."/flex/")) {
          $dh = opendir (getenv("DOCUMENT_ROOT")."/config/".$supergroupname."/flex/");
          while ($f = readdir ($dh)) {
            if (substr ($f, 0, 5)=="flex_") {
              $file = N_InternalPath (getenv("DOCUMENT_ROOT")."/config/".$supergroupname."/flex/$f");
              if ($specs = unserialize (N_ReadFile ($file))) {
                if (strpos ($f, "lowlevel")) {
                  $all["lowlevel"][$f] = $specs; 
                }
                $all[$supergroupname][$f] = $specs;
              }
            }
          }
        }
        ML_RepairCache($supergroupname); // repair multilingual cache (because the underlying database might be changed by a product upgrade or by replication) 
      }
    }
    if (is_dir (getenv("DOCUMENT_ROOT")."/openims/flex/")) {
      $dh = opendir (getenv("DOCUMENT_ROOT")."/openims/flex/");
      while ($file = readdir ($dh)) {
        if (substr ($file, 0, strlen ($prefix))==$prefix && substr($file, strlen($file)-4, 4)==".ubd") {
          $specs = unserialize (N_ReadFile (getenv("DOCUMENT_ROOT")."/openims/flex/".$file));
          if ($specs) $all["importable"][$file] = $specs;
        }
      }
    }    
    foreach ($all as $name => $files) {
      N_WriteFile ("html::/tmp/flexcache/$name.dat", serialize ($files));
    }
    global $flex_loadcache_loaded;
    $flex_loadcache_loaded = array();
  }
}
function FLEX_GetComponentHistory($sgn, $id, $server = "") {
  $results = array();

  $input = array();
  $input["id"] = $id;
  $code = '
    $output = array();
    if ($dirHandle = opendir(N_CleanPath("html::/tmp/logging/flexedit"))) {
      while ($file = readdir($dirHandle)) {
        if (substr($file, strlen($file) - 4) == ".log") {
          $datestr = substr($file, 0, strlen($file) - 4);
          $filecontent = N_ReadFile("html::/tmp/logging/flexedit/$file");
          $lines = explode("\n", $filecontent);
          foreach ($lines as $line) {
            if (strpos($line, $input["id"]) && strpos($line, $sgn)) { // When using packages, the same id can end up in multiple sitecollections
              $fields = explode(" ", $line);
              $shortid = $fields[0];

              $tmpresult = array();
              $tmpresult["shortid"] = $shortid;
              $timestr = $fields[1];
              $fieldn = 3;
              while ($fields[$fieldn] && substr($fields[$fieldn],0,4) != "FLEX") {
                $tmpresult["user"] .= $fields[$fieldn] . " ";
                $fieldn++;
              }
              $tmpresult["action"] = $fields[$fieldn];
              $tmpresult["date"] = N_BuildDateTime(substr($datestr, 0, 4), substr($datestr, 4, 2), substr($datestr, 6, 2), 
                                                   substr($timestr, 0, 2), substr($timestr, 2, 2), substr($timestr, 4, 2));
              $tmpresult["datestr"] = $datestr;

              $output[$shortid] = $tmpresult;
            }
          }
        }
      }
      closedir($dirHandle);
    } 
  ';

  if ($server == "" || $server == N_CurrentServer()) {
    eval($code);
    return $output;
  } else {
    uuse("grid");
    return GRID_RPC($server, $code, $input, 60);
  }

}

function FLEX_ButtonEntities($str) 
{ 
  $str = str_replace("[[[", "&#91;&#91;&#91;", $str); 
  $str = str_replace("]]]", "&#93;&#93;&#93;", $str); 

  return $str; 
} 

function FLEX_HistoryURL($sgn, $typeid, $id) {

  $form = array();
  $form["input"]["sgn"] = $sgn;
  $form["input"]["typeid"] = $typeid;
  $form["input"]["id"] = $id;

  $form["gotook"] = "closeme";
  $form["formtemplate"] = '
    <style>
    body, div, p, th, td, li, dd {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 13px;

    }
    </style>
    xxyyzz
    <center>[[[OK]]]</center>
  ';

  $form["precode"] = '
    uuse("flex");
    $data = FLEX_LoadLocalComponent ($input["sgn"], $input["typeid"], $input["id"]);      

    $formcontent = "";
    $formcontent .= "<b>Component: </b>" . $data["name"] . "<br/>";
    $formcontent .= "<b>ID: </b>" . $input["id"] . "<br/><br/>";
    $formcontent .= "<b>Let op: wijzigingen ten gevolge van replicatie zijn niet gelogd!</b><br/>";

    T_Start("ims");
    echo ML("Datum", "Date");
    T_Next();
    echo ML("Gebruiker", "User");
    T_Next();
    echo ML("Actie", "Action");
    T_Next();
    echo "&nbsp;";
    T_NewRow();

    $results = FLEX_GetComponentHistory($input["sgn"], $input["id"]);
// 201000518 KvD sorteer op tijdstempel via "date"
    uasort($results, create_function(\'$a,$b\', \'return $a["date"] < $b["date"];\'));
///
    foreach ($results as $shortid => $specs) {
      $form2 = array();
      $form2["input"]["date"] = $specs["datestr"];
      $form2["input"]["shortid"] = $specs["shortid"];

      // Copied from form used to edit custom code, so that N_DetectPHPCodeFields will treat our output as code.
      // This makes N_HtmlEntities behave correctly for code.
      $form2["metaspec"]["fields"]["code"] = "code";
      $form2["input"]["type"] = $input["typeid"];
      $form2["formtemplate"] = \'
        xxyyzz
        <center>[[[OK]]]</center>
      \';
      $form2["gotook"] = "closeme";
      $form2["precode"] = \'
        T_Start();
        $xml = N_ReadFile(N_CleanRoot() . "tmp/logging/flexedit/short/".$input["date"]."/".$input["shortid"].".txt");
        $object = N_XML2Object($xml);
        foreach ($object as $prop => $value) {
          echo "<h2>" . $prop . "</h2>";
          echo "<pre>" . FLEX_ButtonEntities(N_HtmlEntities($value))  . "</pre>";
        }
        $formtemplate = str_replace("xxyyzz", TS_End(), $formtemplate);
      \';
      $form2url = FORMS_URL($form2);
      echo N_VisualDate($specs["date"], true, true);
      T_Next();
      echo $specs["user"];
      T_Next();
      echo $specs["action"];
      T_Next();
      echo "<a href=\"$form2url\">".ML("Bekijk", "View")."</a> ";
      T_NewRow();      
    }
  
    $formcontent .= TS_End();

    $formtemplate = str_replace("xxyyzz", $formcontent, $formtemplate);
  ';

  return FORMS_URL($form);
}

function FLEX_Packages() {
  N_Debug("FLEX_Packages called");
  // Alleen core!

  // Only simple per-request caching. No advanced caching. 
  // This is OK since packages are only loaded when internal=yes or when clicking on "Install standard packages"

  global $FLEX_Packages_result, $FLEX_Packages_loaded;
  if ($FLEX_Packages_loaded) return $FLEX_Packages_result;
  N_Debug("FLEX_Packages: reading packages from disk");

  $FLEX_Packages_result = array();
  $dirs = N_Dirs("html::/openims/flex/modules/");
  foreach ($dirs as $package_id) {
    $manifest = unserialize(N_ReadFile("html::/openims/flex/modules/".$package_id."/manifest.ubd"));
    if ($manifest) $FLEX_Packages_result[$package_id] = $manifest;
  }

  N_Debug("FLEX_Packages: finished reading packages from disk");

  $FLEX_Packages_loaded = true;
  return $FLEX_Packages_result;

}

function FLEX_HasPackages() {
  if (N_Dirs("html::/openims/flex/modules/")) return true;
  return false;
}

function FLEX_LoadPackage($sgn, $id) {
  $allpackages = FLEX_Packages();
  return $allpackages[$id];
}

function FLEX_SavePackage($supergroupname, $id, $specs)
{
  $oldspecs = unserialize (N_ReadFile("html::openims/flex/modules/$id/manifest.ubd"));
  N_Log ("flexedit", SHIELD_CurrentUserName()." FLEX_SavePackageOLD ($supergroupname, $id, ...)", N_Object2XML ($oldspecs));
  N_Log ("flexedit", SHIELD_CurrentUserName()." FLEX_SavePackage ($supergroupname, $id, ...)", N_Object2XML ($specs));
  N_Debug ("FLEX_SavePackage ($supergroupname, $id, ...)");
  if (!$id) $id = N_GUID();
  N_WriteFile ("html::openims/flex/modules/$id/manifest.ubd", serialize ($specs));
}

function FLEX_DeletePackage($supergroupname, $id)
{
  $specs = N_ReadFile ("html::openims/flex/modules/$id/manifest.ubd");
  $specs = unserialize($specs);
  N_Log ("flexedit", SHIELD_CurrentUserName()." DELETE - FLEX_DeletePackage ($supergroupname, $id, ...)", N_Object2XML ($specs));

  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(N_CleanPath("html::openims/flex/modules/$id/")); 
  @rmdir(N_CleanPath("html::openims/flex/modules/$id/"));

  // Create an empty manifest file (to make the delete proprograte through the upgrades / replication mechanism)
  N_WriteFile("html::openims/flex/modules/$id/manifest.ubd", serialize(null));

  FLEX_RepairCache();
}

function FLEX_ExportPackage($package_id, $specs) {
  $sgn = $specs["sgn"];
  $site = $specs["site"];

  if (!$sgn || !$site) FORMS_ShowError("Error", '$specs["sgn"] or $specs["site"] is missing', "no");

  // Everything in exportdata will be serialized into data.ubd (after substitution supergroupname and stuff...)
  $exportdata = array();
 
  // Copy those overwritesettings that apply to records / config / fields / folders to $exportdata.
  // The other overwritesettings (about objects etc.) will be converted (so that they are about records and files) and added later
  if ($specs["overwritesettings"]["records"]) $exportdata["overwritesettings"]["records"] = $specs["overwritesettings"]["records"];
  if ($specs["overwritesettings"]["config"]) $exportdata["overwritesettings"]["config"] = $specs["overwritesettings"]["config"];
  if ($specs["overwritesettings"]["fields"]) $exportdata["overwritesettings"]["fields"] = $specs["overwritesettings"]["fields"];
  if ($specs["overwritesettings"]["folders"]) $exportdata["overwritesettings"]["folders"] = $specs["overwritesettings"]["folders"];

  // Export workflows (add record in $exportdata and collect fields and groups if the autofields / autogroups settings are active)
  if ($specs["workflows"]) foreach ($specs["workflows"] as $workflow_id) {
    $workflow = MB_Ref("shield_{$sgn}_workflows", $workflow_id);
    if ($specs["autofields"]) foreach ($workflow["meta"] as $dummy => $fieldname) if ($fieldname) $specs["fields"][] = $fieldname;

    if ($specs["autogroups"]) {
      foreach ($workflow["rights"] as $right => $groups) {
        foreach ($groups as $groupname => $dummy) {
         $specs["groups"][] = $groupname;
        }
      }
      // Ignore right for workflow stage changes, since everybody needs "view" right anyway.
    }

    $specs["records"]["shield_{$sgn}_workflows"][] = $workflow_id;
  }

  // Export objects (document and webpages): 
  // - add records in $exportdata 
  // - collect directories for later processing
  // - do the same for overwritesettings 
  if ($specs["objects"]) foreach ($specs["objects"] as $object_id) {
    $object = MB_Load("ims_{$sgn}_objects", $object_id);
    $specs["records"]["ims_{$sgn}_objects"][] = $object_id;
    $specs["dirs"][] = "{$sgn}/preview/objects/$object_id";
    $specs["dirs"][] = "{$sgn}/objects/$object_id";
    if ($specs["includeobjecthistory"]) $specs["dirs"][] = "{$sgn}/objects/history/{$object_id}";

    if ($object["objecttype"] == "webpage") $specs["files"][] = "{$site}/{$object_id}.php";
    if ($specs["autofolders"] && $object["objecttype"] == "document") {
      $specs["folders"][] = $object["directory"];
    }

    if ($overwritesetting = $specs["overwritesettings"]["objects"][$object_id]) {
      $exportdata["overwritesettings"]["records"]["ims_{$sgn}_objects"][$object_id] = $overwritesetting;
      $specs["overwritesettings"]["dirs"]["{$sgn}/preview/objects/$object_id"] = $overwritesetting;
      $specs["overwritesettings"]["dirs"]["{$sgn}/objects/$object_id"] = $overwritesetting;
      if ($specs["includeobjecthistory"]) $specs["overwritesettings"]["dirs"]["{$sgn}/objects/history/$object_id"] = $overwritesetting;
    }
  }

  // Export templates
  if ($specs["templates"]) foreach ($specs["templates"] as $template_id) {
    $template = MB_Load("ims_{$sgn}_templates", $template_id);
    $specs["records"]["ims_{$sgn}_templates"][] = $template_id;
    $specs["dirs"][] = "{$sgn}/preview/templates/$template_id";
    $specs["dirs"][] = "{$sgn}/templates/$template_id";
    if ($specs["includetemplatehistory"]) $specs["dirs"][] = "{$sgn}/templates/history/{$template_id}";

    // CMS templates can have fields associated with them (just like workflows)
    if ($specs["autofields"]) foreach ($template["meta"] as $dummy => $fieldname) if ($fieldname) $specs["fields"][] = $fieldname;

    if ($overwritesetting = $specs["overwritesettings"]["templates"][$template_id]) {
      $exportdata["overwritesettings"]["records"]["ims_{$sgn}_templates"][$template_id] = $overwritesetting;
      if ($object["objecttype"] == "webpage") $specs["overwritesettings"]["files"]["{$site}/{$object_id}.php"] = $overwritesetting;
      $specs["overwritesettings"]["dirs"]["{$sgn}/preview/templates/$template_id"] = $overwritesetting;
      $specs["overwritesettings"]["dirs"]["{$sgn}/templates/$template_id"] = $overwritesetting;
      if ($specs["includetemplatehistory"]) $specs["overwritesettings"]["dirs"]["{$sgn}/templates/history/$template_id"] = $overwritesetting;
    }
  }


  // Export fields (add to $exportdata)
  if ($specs["fields"]) foreach ($specs["fields"] as $fieldname) { 
    $allfields = MB_Load("ims_fields", $sgn);
    $exportdata["fields"][$fieldname] = $allfields[$fieldname];
  }

  // Export DMS folders
  if ($specs["folders"]) foreach ($specs["folders"] as $folder_id) {
    $tree = CASE_TreeRef($sgn, $folder_id);
    $path = TREE_Path($tree, $folder_id);
    foreach ($path as $dummy => $pathspecs) {
      $myfolder_id = $pathspecs["id"];
      if (!$exportdata["folders"][$myfolder_id]) {
        $treedata = $tree["objects"][$myfolder_id];
        unset($treedata["children"]);
        $exportdata["folders"][$myfolder_id]["treedata"] = $treedata;
        $connections = MB_Load("shield_{$sgn}_localsecurity_connections", $myfolder_id);
        if ($connections) {
          $exportdata["folders"][$myfolder_id]["connections"] = $connections;
          if ($specs["autogroups"]) {
            foreach ($connections as $groupname => $connspecs) {
              $specs["groups"][] = $groupname;              
              foreach ($connections[$groupname] as $groupname2 => $dummy) $specs["groups"][] = $groupname2;
            }
          }
        }
        if (strpos($myfolder_id, ")") && !$treedata["parent"]) { // rootfolder of dossier
          $p = strpos($myfolder_id, ")");
          $case_id = substr($myfolder_id, 0, $p+1);
          $exportdata["folders"][$myfolder_id]["case_id"] = $case_id;
          $exportdata["folders"][$myfolder_id]["casedata"] = MB_Load("ims_{$sgn}_case_data", $case_id);
        }
      }
    }
  }

  // Export groups (note that the members of a group are not included in the export)
  if ($specs["groups"]) foreach ($specs["groups"] as $groupname) {
    $group = MB_Load("shield_{$sgn}_groups", $groupname);
    unset($group["users"]); unset($group["users_global"]); unset($group["users_secsec"]);
    $exportdata["data"]["shield_{$sgn}_groups"][$groupname] = $group;
  }

  // Export full tables 
  if ($specs["tables"]) foreach ($specs["tables"] as $tablename) {
    $allkeys = MB_AllKeys($tablename);
    foreach ($allkeys as $key) {
      $object = MB_Load($tablename, $key);
      if ($object) {
        $exportdata["data"][$tablename][$key] = $object;
      }
    }
  }

  // Export individual records
  if ($specs["records"]) foreach ($specs["records"] as $tablename => $records) {
    foreach ($records as $key) {
      $object = MB_Load($tablename, $key);
      if ($object) {
        $exportdata["data"][$tablename][$key] = $object;
      }
    }
  }

  // Export configuration settings
  $exportdata["config"] = $specs["config"];

  // Determine all files in the specified directories
  if ($specs["dirs"]) foreach ($specs["dirs"] as $dir) {
    $files = N_Tree("html::/" . $dir . "/");
    $overwritesetting = $specs["overwritesettings"]["dirs"][$dir];
    foreach ($files as $fullpath => $filespecs) {
      $path = $dir . $filespecs["relpath"] . $filespecs["filename"];
      $specs["files"][] = $path;
      if ($overwritesetting) $specs["overwritesettings"]["files"][$path] = $overwritesetting;
    }
  }
  
  // Check file paths, refuse to package core
  if ($specs["files"]) foreach ($specs["files"] as $path) {
    if (!$path) continue;
    $exportpath = str_ireplace($site, "___site___", str_ireplace($sgn, "___sgn___", $path));
    if (substr($exportpath, 0, 5) == "sugar") continue;
    if ($path == $exportpath) FORMS_ShowError("Error", "File $path is part of the OpenIMS core", "no");
  }

  // Delete old files
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(N_CleanPath("html::openims/flex/modules/$package_id/files/"));


  // Export files. Substitutions are done on both the path and the file contents. 
  if ($specs["files"]) foreach ($specs["files"] as $path) {
    if (!$path) continue;
    $exportpath = N_32kFix_UnTransform(str_ireplace($site, "___site___", str_ireplace($sgn, "___sgn___", $path)));
    if ($specs["overwritesettings"]["files"][$path]) $exportdata["overwritesettings"]["files"][$exportpath] = $specs["overwritesettings"]["files"][$path];

    $dest = "html::/openims/flex/modules/$package_id/files/" . $exportpath;
    $source = "html::/".$path;
    $content = N_ReadFile($source);
    if (!$content) continue;
    if (substr($path, 0, 6) == "config" && FILES_FileType($path) == "ubd") {
      $flexobject = unserialize($content);
      $flexobject = N_strarray_ireplace($site, "___site___", N_strarray_ireplace($sgn, "___sgn___", $flexobject));
      $content = serialize($flexobject);
    } else {
      $content = str_ireplace($site, "___site___", str_ireplace($sgn, "___sgn___", $content));
    }

    global $myconfig; 
    $kfix = $myconfig["32kfix"];
    $myconfig["32kfix"]=""; // Write file without 32kfix
    N_WriteFile($dest, $content);
    echo "<b>File</b>: $exportpath <br/>";
    $myconfig["32kfix"] = $kfix;
  }

  // Substitute and save everything in $exportdata
  $exportdata = N_strarray_ireplace($site, "___site___", N_strarray_ireplace($sgn, "___sgn___", $exportdata));
  N_WriteFile ("html::openims/flex/modules/".$package_id."/data.ubd", serialize ($exportdata));

  // Echo some stuff (can be captured by calling function)
  echo "<br/>";
  echo "<b>Data: </b><br/>";
  T_EO($exportdata["data"]);
  echo "<b>Folders: </b><br/>";
  T_EO($exportdata["folders"]);
  echo "<b>Fields: </b><br/>";
  T_EO($exportdata["fields"]);
  echo "<b>Config: </b><br/>";
  T_EO($exportdata["config"]);
  echo "<b>Overwrite settings: </b><br/>";
  T_EO($exportdata["overwritesettings"]);

  // Increase IMS build
  global $myconfig;
  if ($myconfig[$sgn]["allowflexpackagecore"] == "yes") {
    global $imsbuild;
    N_WriteFile (getenv("DOCUMENT_ROOT")."/nkit/build.php", '<? global $imsbuild; $imsbuild="'.($imsbuild+1).'"; ?>');
    N_WriteFile (getenv("DOCUMENT_ROOT")."/nkit/build.js", "document.write('".($imsbuild+1)."');");
  }
}

function FLEX_FileExists($path) {
  // When you delete a flex component, a .ubd file is saved that contains only 'N;'
  // This function pretends that these files do not exist
  uuse("files");
  if (N_FileExists($path)) {
    if (FILES_FileType($path) == "ubd") {
      $fileinfo = N_FileInfo($path);
      if ($fileinfo["md5b"] == N_GiveMD5BfromData($dummy = 'N;')) {
        return false;
      }
    }
    return true;
  }
  return false;

}


?>