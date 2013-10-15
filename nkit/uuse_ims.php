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



global $myconfig;

// *** determine agent version in transfer agent url's ***
global $imsagentversion;
   $imsagentversion = 428561; // known stable, used for long time, needs to be old version to prevent unwanted updates
// $imsagentversion = 428600; // new, tested
// $imsagentversion = 428655; // new, tested, used by OSICT
// $imsagentversion = 428665; // new, tested, has print function
// $imsagentversion = 428685; // new, tested, has print function, old Indy (much better Proxy support)
// $imsagentversion = 428695; // new, tested, has print function, old Indy (much better Proxy support), Outlook support
// $imsagentversion = 428698; // new, tested, also has domain based uploading instead of IP based uploading
// $imsagentversion = 428706; // new, tested, also agent settings can be changed from within a link
// $imsagentversion = 428708; // new, tested, also multiple download can ask for location (directory)
// $imsagentversion = 428710; // new, tested, allows use of temp:: in params from execute command
// $imsagentversion = 428724; // new, tested, patch for Firefox 2.0.0.6 protocol handling
// $imsagentversion = 428762; // new, tested, allows for n-office document handling
// $imsagentversion = 428765; // new, tested, allows locking OpenOffice 3 files
// $imsagentversion = 428766; // new, tested, No more errors while using containers
// $imsagentversion = 428767; // new, tested, 20100215 GvA: Bug fix for not caching
// $imsagentversion = 428768; // new, tested, 20100707 KvD: Check on errors while downloading for full disk or no write access and MD5
// $imsagentversion = 428769; // new, tested, 20100708 KvD: No empty files should be created when a copy error occurs
// $imsagentversion = 428771; // new, tested, 20101110 KM: Cache bug fixed. (Due to a 'readonly'-tag .xls and .ppt files could not be deleted in view mode without cache.)
// $imsagentversion = 428785; // new, tested, 20110520 KM: SSL ondersteuning, sneller downloaden, progress bar, rare tekens in de map naam filter, tijdelijke bestanden worden gefilterd, betere afhandeling bij upload problemen (log / betere try catch / refresh scherm na verhogen aantal retries)
// $imsagentversion = 428787; // new, tested, 20110714 KvD: Bouwfonds aanpassing dat er een melding verschijnt als uploaden mislukt
// $imsagentversion = 428788; // new, tested, 20111214 KvD: Bouwfonds aanpassing update
// $imsagentversion = 428789; // new, tested, 20111403 KvD: Range check error gevolg van sluitknop : opgelost
// $imsagentversion = 428790; // new, tested, 20120607 KvD: SWEBRU bug opglost : bij afdrukken bleef bestand gelockt

UUSE_DO ('IMS_Init();');

function IMS_Init() {
  N_Debug ("UUSE BEFORE CALL");

  global $imsagentversion, $myconfig;
  if ($myconfig[IMS_SuperGroupName()]["imsagentversion"] )
  {
    $imsagentversion = $myconfig[IMS_SuperGroupName()]["imsagentversion"];
  }

  N_Debug ("UUSE AFTER CALL");

  // *** determine which agent you get when you download it ***
  global $imsagentversiondownloadversion;
  $imsagentversiondownloadversion = 428785; // stable
  if ($myconfig[IMS_SuperGroupName()]["imsagentversiondownloadversion"])
  {
    $imsagentversiondownloadversion = $myconfig[IMS_SuperGroupName()]["imsagentversiondownloadversion"];
  }

  // Moved IMS_XssFilter from here to uuse_flex.php, because it needs to happen after FLEX_LoadSupportFunctions
  uuse("flex"); // Should always happen anyway, but now it should happen because of an important uuse_ims.php feature (IMS_XssFilter).

}

/*** uuse("ims") DOCUMENTATION ***

Object types
  webpage (including homepage)

Editors (for webpage objects)
  Microsoft Word
  Microsoft Excel
  Microsoft Powerpoint
  Notepad (for PHP code)

Generic
  Template (how does it look)
  Object (what does it contain)
  Mixers always operate at sitecollection level (they don't care which site it is)

Urls
  webpage:           http://www.amazingsite.com/amazingsite_com/03921478948712039.php
  document:          http://www.amazingsite.com/amazingsite_sites/objects/03921478948712039/03921478948712039.doc
  document preview:  http://www.amazingsite.com/amazingsite_sites/preview/objects/03921478948712039/1/03921478948712039.doc

Tables
  ims_sitecollections
    description
    sites
  ims_sites
    description
    domains
    sitecollection
    homepage
  ims_<<sitecollection>>_objects
    childen
    parameters preview
    parameters published
    parent
    sortvalue
    template
    type
  ims_<<sitecollection>>_templates
  ims_<<sitecollection>>_folders
  ims_<<sitecollection>>_catalogs

Files
  preview
    /<<sitecollection>>/preview/objects/<<objectid>>/...
    /<<sitecollection>>/preview/templates/<<templateid>>/...
  history
    /<<sitecollection>>/history/objects/<<objectid>>/<<historyid>>/...
    /<<sitecollection>>/history/templates/<<templateid>>/<<historyid>>/...
  published
    /<<sitecollection>>/objects/<<objectid>>/...
    /<<sitecollection>>/templates/<<templateid>>/...
    /<<site>>/<<objectid>>.<<type>>
    /<<site>>/forms/...

Mix
  $command=="meta"
    $output["parameters"][<<fieldname>>]["type"]   "string" / "text"
    $output["parameters"][<<fieldname>>]["ask"]   "<<fieldname as seen by users>>"
    $output["contenttype"]   "html", "none", ...
  $command=="new_object"
    $output["content"]
  $command=="generate_dynamic_page"
    $input["content"]
    $output["content"]
  $command=="generate_static_page"
    $output["content"]

*/

uuse ("shield");
uuse ("forms");
uuse ("mail");
uuse ("tree");
uuse ("search");
uuse ("tables");
uuse ("bpms");
uuse ("oflex");
uuse ("files");
uuse ("flex");
uuse ("opendoc");
uuse ("ht");
uuse ("niceurl");
uuse ("cmsuif");



function IMS_AddDomain ($site_id, $domain)
{
  $site = &MB_Ref ("ims_sites", $site_id);
  $site["domains"][$domain]["dummy"]="dummy";
}

function &IMS_RegisterTemplate ($sitecollection_id, $template_id)
{
  $template = &MB_Ref ("ims_".$sitecollection_id."_templates", $template_id);
  $template["name"] = $template_id;
  $template["preview"] = "yes";
  $template["published"] = "no";
  return $template;
}

function IMS_SetLocation ($sitecollection_id, $object_id, $parent_object_id="", $sortvalue="")
{
  N_Debug ("IMS_SetLocation ($sitecollection_id, $object_id, $parent_object_id, $sortvalue)");
  if (!$sortvalue) $sortvalue=N_GUID();
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  $object["sortvalue"] = $sortvalue;
  if ($object["parent"]) {
    // remove from old location
    $old_parent_object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object["parent"]);
    unset ($old_parent_object["children"][$object_id]);
  }
  $object["parent"] = $parent_object_id;
  if ($parent_object_id) {
    $parent_object = &MB_Ref ("ims_".$sitecollection_id."_objects", $parent_object_id);
    $parent_object["children"][$object_id] = $sortvalue;
  }
}

function IMS_NewObject ($sitecollection_id, $objecttype, $object_id="")
{
  if (!$object_id) $object_id = N_GUID();
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);

  $object["objecttype"] = $objecttype;
  $object["preview"] = "yes";
  $object["published"] = "no";
  $object["stage"] = 1; // stage "new"
  return $object_id;
}

function IMS_UpdateShortcuts ($sitecollection_id, $object_id)
{
  if(!FILES_IsPermalink($sitecollection_id, $object_id)) // leave normal shortcuts as they are
  {
    $list = MB_TurboSelectQuery ("ims_".$sitecollection_id."_objects", '$record["source"]', $object_id);
    foreach ($list as $key) IMS_RefreshShortcut ($sitecollection_id, $key);
  }
}

// IMS_SmartRead makes standard shortcuts transparant to code for queries etc.
// Old method: $object["workflow"];
// New method: IMS_SmartRead ($object, '["workflow"]');
function IMS_SmartRead ($object, $spec)
{
  if ($object["objecttype"]=="shortcut") {
    return eval ('return $object["baseall"]'.$spec.';');
  } else {
    return eval ('return $object'.$spec.';');
  }
}

function IMS_RefreshShortcut ($sitecollection_id, $shortcut_id, $new = false)
{
  if  (FILES_IsPermalink($sitecollection_id, $shortcut_id)) return; // In case of permalink: leave immediately

  $shortcutobject = &IMS_AccessObject ($sitecollection_id, $shortcut_id);
  $sourceobject = &MB_Ref ("ims_".$sitecollection_id."_objects", $shortcutobject["source"]);
  $shortcutobject["baseall"] = $sourceobject;
  $shortcutobject["base_stagename"] = SHIELD_CurrentStageName ($sitecollection_id, $shortcutobject["source"]);
  $shortcutobject["base_version"] = IMS_Version ($sitecollection_id, $shortcutobject["source"]);
  if (is_array($sourceobject["history"])) {
    reset ($sourceobject["history"]);
    while (list($k, $data)=each($sourceobject["history"])) {
      $time = $data["when"];
    }
  }
  $shortcutobject["base_changed"] = $time;
  if ($sourceobject["allocto"]) {
    $user_id = $sourceobject["allocto"];
    $user = MB_Ref ("shield_".$sitecollection_id."_users", $user_id);
    $shortcutobject["base_assigned"] = $user["name"];
  }
  $workflow = MB_Load("shield_{$sitecollection_id}_workflows", $sourceobject["workflow"]);
  if ($new || !$workflow["shortcutmeta"] || ($workflow["shortcutmeta"]["shorttitle"] != "indep")) {
    // Copy the shorttitle to base_shorttitle, except in the situation where shorttitle is "independent" AND this is not a new shortcut
    // base_shorttitle is that which we show in the DMS autotables; an independent shorttitle is allowed to overwrite this (in which case base_shorttitle has no relationship at all with the base document...)
    $shortcutobject["base_shorttitle"] = $sourceobject["shorttitle"];
  }
  if ($workflow["shortcutmeta"]) {
    $shortcutobject["workflow"] = $sourceobject["workflow"]; // Easy access to workflow without accessing source object (to find out which metadata fields are independent etc.)
                                                             // Also useful in queries or other functionality in which you want to treat independent shortcuts and documents almost the same way.
    foreach ($workflow["shortcutmeta"] as $fieldname => $type) {
      if ($type == "copy" || ($type == "indep" && $new)) {
        if ($fieldname == "shorttitle" || $fieldname == "longtitle") {
          $shortcutobject[$fieldname] = $sourceobject[$fieldname];
       } else {
          $shortcutobject["meta_".$fieldname] = $sourceobject["meta_".$fieldname];
       }
      }
    }
  }
}

function IMS_NewDocumentShortcut ($sitecollection_id, $directory_id, $source_id)
{
  $sourceobject = &MB_Ref ("ims_".$sitecollection_id."_objects", $source_id);
  $object_id = IMS_NewObject ($sitecollection_id, "shortcut");
  $object = &IMS_AccessObject ($sitecollection_id, $object_id);
  $object["directory"] = $directory_id;
  $object["source"] = $source_id;
  IMS_RefreshShortcut ($sitecollection_id, $object_id, true);

  global $myconfig;
  if ($myconfig["backgroundfulltextindex"] == "no") MB_Flush(); // Needed so that the query in SEARCH_blabla can find the new shortcut.
  SEARCH_AddPreviewDocumentToDMSIndex($sitecollection_id, $source_id);
  if ($sourceobject["published"] == "yes") SEARCH_AddDocumentToDMSIndex($sitecollection_id, $source_id);

  return $object_id;
}

function IMS_NewDocumentPermalink($sitecollection_id, $directory_id, $source_id)
{
  $object_id = IMS_NewDocumentShortcut ($sitecollection_id, $directory_id, $source_id);

  uuse("files");
  $keytoversion = "";
  $sourceobject = MB_Ref ("ims_".$sitecollection_id."_objects", $source_id);
  $thehistory = $sourceobject["history"];
  while (list($key, $data) = each($thehistory))
  {
    if ( FILES_HistoryVersionExistsOnDisk($sitecollection_id, $source_id, $key) )
    {
      $keytoversion = $key; // will collect last physical copy of document
    }
  }
  $object = &IMS_AccessObject ($sitecollection_id, $object_id);
  $object["versionshortcut"] = "yes";
  $object["sourceversion"] = $keytoversion;
  $object["base_shorttitle"] = "PL: ".$object["base_shorttitle"];
  return $object_id;
}

function IMS_NewDocumentObject ($sitecollection_id, $directory_id, $template_id="", $object_id="", $filename="" , $alwaysUseTemplatePreviewVersion = false )
{
  $id = IMS_NewObject ($sitecollection_id, "document", $object_id);
  $object_id = $id;
  $object = &IMS_AccessObject ($sitecollection_id, $id);
  $object["directory"] = $directory_id;
  $object["allocto"] = SHIELD_CurrentUser(IMS_SuperGroupName());
  if ($template_id) {
    $template = &IMS_AccessObject ($sitecollection_id, $template_id);
    $object["executable"] = $template["executable"];
    $object["workflow"] = $template["workflow"];
    if ($template ["executable"]=="winword.exe") {
      $doc = "document.doc";
    } else if ($template ["executable"]=="excel.exe") {
      $doc = "document.xls";
    } else if ($template ["executable"]=="powerpnt.exe") {
      $doc = "document.ppt";
    }
    if ($template ["filename"]) $doc = $template ["filename"];
    $doctype = FILES_FileType ($doc);
    if ($filename) {
      $filename = preg_replace ("'[^a-z0-9\\.]'i", "_", $filename);
      $filename = str_replace (".".$doctype, "", $filename).".".$doctype;
      $object["filename"] = $filename;
      if ($template["published"]=="yes" && !$alwaysUseTemplatePreviewVersion ) {
        N_CopyDir ("html::".$sitecollection_id."/preview/objects/".$id."/", "html::".$sitecollection_id."/objects/".$template_id);
      } else {
        N_CopyDir ("html::".$sitecollection_id."/preview/objects/".$id."/", "html::".$sitecollection_id."/preview/objects/".$template_id);
      }
      N_Rename ("html::".$sitecollection_id."/preview/objects/".$id."/".$doc, "html::".$sitecollection_id."/preview/objects/".$id."/".$filename);
    } else {
      if ($template["published"]=="yes" && !$alwaysUseTemplatePreviewVersion ) {
        N_CopyDir ("html::".$sitecollection_id."/preview/objects/".$id."/", "html::".$sitecollection_id."/objects/".$template_id);
      } else {
        N_CopyDir ("html::".$sitecollection_id."/preview/objects/".$id."/", "html::".$sitecollection_id."/preview/objects/".$template_id);
      }
      $object["filename"] = $template ["filename"];
    }
  }
  return $id;
}

function &IMS_AccessObject ($sitecollection_id, $object_id)
{
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  return $object;
}

function &IMS_CreateObject ($sitecollection_id, $template_id, $parent_id, $object_id, $site_id, $nohistory = false) // used to create web pages
{
  $object_id = IMS_NewObject ($sitecollection_id, "webpage", $object_id);
  $object = &IMS_AccessObject ($sitecollection_id, $object_id);
  $object["template"] = $template_id;
  IMS_SetLocation ($sitecollection_id, $object_id, $parent_id, "");
  $object["parent"] = $parent_id; // directly affects both preview and published
  $object["sortvalue"] = N_GUID(); // directly affects both preview and published
  $path =  N_ProperPath ("html::"."/".$sitecollection_id."/preview/objects/".$object_id."/");
  $output = IMS_SuperMixer ($sitecollection_id, $site_id, $object_id, "new_object");
  $object["parameters"]["preview"] = $output;
  N_WriteFile ($path."page.html", $output["content"]);

  if (!$nohistory) {
    global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
    $time = time();
    $guid = N_GUID();
    $object["history"][$guid]["type"] = "newpage";
    $object["history"][$guid]["when"] = $time;
    $object["history"][$guid]["author"] = SHIELD_CurrentUser ($sitecollection_id);
    $object["history"][$guid]["server"] = N_CurrentServer ();
    $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
    $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
    $object["history"][$guid]["server_addr"] = $SERVER_ADDR;
    N_CopyDir ("html::".$sitecollection_id."/objects/history/".$object_id."/".$guid,
               "html::".$sitecollection_id."/preview/objects/".$object_id);
  }

  return $object;
}

function IMS_GetPageContent ($sgn, $object_id, $path, $filename, $fallback = true, $language = "") {
  // Return (raw) page content for a specific language
  N_Debug("IMS_GetPageContent $sgn $path $filename $fallback $language");
  $path = N_ProperPath($path); // make sure $path ends with /
  global $myconfig;
  global $ml_page_language; // will be set for the benefit of CMS components (through ML_GetPageLanguage)
  $ml_page_language = "";
  if (is_array($myconfig[$sgn]["ml"]["sitelanguages"])) {
    if (!$language) $language = ML_GetLanguage();

    $site_id = IMS_Object2Site($sgn, $object_id);
    $languages = $myconfig[$sgn]["ml"]["sitelanguages"][$site_id];
    if ($languages) {

      if ($language == "concat") {
        // special case (for full text indexing)
        $content = N_ReadFile($path.$filename);
        array_shift($languages);
        foreach ($languages as $thelang) {
          $content .= "<br/>" . N_ReadFile($path.$thelang."-".$filename);
        }
        return $content;
      } else {
        if (in_array($language, $languages)) {
          $firstlang = array_shift($languages);
          // Try the desired language
          if ($language == $firstlang) {
            $content = N_ReadFile($path.$filename);
          } else {
            $content = N_ReadFile($path.$language."-".$filename);
          }
          if (!$fallback || !IMS_PageContentIsEmpty($content)) {
            $ml_page_language = $language;
            return $content;
          }
          // Fallback mechanisms
          if ($fallback) {
            // Try the configured fallback language (unless it is the first language, because we will try that next anyway)
            $fallbacklang = $myconfig[$sgn]["ml"]["fallbacklanguage"][$language];
            if ($fallbacklang && $fallbacklang != $language && $fallbacklang != $firstlang) {
              $content = N_ReadFile($path.$fallbacklang."-".$filename);
              if (!IMS_PageContentIsEmpty($content)) {
                $ml_page_language = $fallbacklang;
                return $content;
              }
            }
            // Try the first language
            if ($language != $firstlang) {
              $content = N_ReadFile($path.$filename);
              if (!IMS_PageContentIsEmpty($content)) {
                $ml_page_language = $firstlang;
                return $content;
              }
            }
            // Try the others (unless we already tried them...)
            foreach ($languages as $trylang) if ($trylang != $language && $trylang != $fallbacklang) {
              $content = N_ReadFile($path.$trylang."-".$filename);
              if (!IMS_PageContentIsEmpty($content)) {
                $ml_page_language = $trylang;
                return $content;
              }
            }
          }
        }
        $ml_page_language = $firstlang;
      }
    }
  }
  return N_ReadFile($path.$filename);
}

function IMS_PageContentIsEmpty($content) {
  if (!$content) return true;

  $search = array('@<script[^>]*?>.*?</script>@si',   // Strip out javascript
                  '@<head[^>]*?>.*?</head>@si',       // Strip out the head
                  '@<style[^>]*?>.*?</style>@si',     // Strip style tags properly
                  '@<![\\s\\S]*?--[ \\t\\n\\r]*>@'    // Strip multi-line comments including CDATA
  );
  $content = preg_replace($search, '', $content);
  $content = strip_tags($content);
  $content = str_replace("&#160;", "", $content);
  $content = str_replace("&nbsp;", "", $content);
  $content = trim($content);

  if ($content) return false;

  return true;
}

function IMS_PageHasLanguage($sitecollection_id, $object_id, $language, $preview = false) {
  $sgn = $sitecollection_id;
  if ($preview) {
    $path = N_ProperPath ("html::"."/".$sitecollection_id."/preview/objects/".$object_id."/");
  } else {
    $path = N_ProperPath ("html::"."/".$sitecollection_id."/objects/".$object_id."/");
  }
  $filename = "page.html";

  global $myconfig;
  if (is_array($myconfig[$sgn]["ml"]["sitelanguages"])) {
    $site_id = IMS_Object2Site($sgn, $object_id);
    $languages = $myconfig[$sgn]["ml"]["sitelanguages"][$site_id];
    if ($languages) {
      $firstlang = array_shift($languages);
      if ($language == $firstlang) {
        $content = N_ReadFile($path.$filename);
      } else {
        $content = N_ReadFile($path.$language."-".$filename);
      }
      if (!IMS_PageContentIsEmpty($content)) return true;
    }
  }
  return false;
}

function IMS_GetObjectContent ($sitecollection_id, $object_id, $internal=false, $language = "")
{
  if (!function_exists ("HTML_RawContentFilter")) {
    $internal_component = FLEX_LoadImportableComponent ("support", "a320b08b5d69b2026fbdb3392c307286");
    $internal_code = $internal_component["code"];
    eval ($internal_code);
  }

  if ($internal) {
    $path = N_ProperPath ("html::"."/".$sitecollection_id."/objects/".$object_id."/");
    return HTML_RawContentFilter(IMS_GetPageContent($sitecollection_id, $object_id, $path, "page.html", false, "concat"));
  }
  global $shielddummy;
  if ($shielddummy) SHIELD_Decode ($shielddummy);
  if (!$internal && !SHIELD_HasObjectRight ($sitecollection_id, $object_id, "view")) {
     global $myconfig;
     if ($myconfig[IMS_Supergroupname()]["custom"]["401"] != "") {
        die ($myconfig[IMS_Supergroupname()]["custom"]["401"]);
     } else {
        die ("U heeft helaas niet voldoende rechten om de inhoud van deze pagina te bekijken.");
     }
  }
  if (!$internal && IMS_Preview()) {
    $path = N_ProperPath ("html::"."/".$sitecollection_id."/preview/objects/".$object_id."/");
  } else {
    $path = N_ProperPath ("html::"."/".$sitecollection_id."/objects/".$object_id."/");
  }
  $object = &IMS_AccessObject ($sitecollection_id, $object_id);
  if ($object["editor"] == "PHP Code") {
    ob_start();
    eval ("?>".N_ReadFile ($path."page.php")."<?");
    $output = ob_get_contents();
    ob_end_clean();
  } else if ($object["editor"] == "Form" || $object["editor"] == "BPMSForm") {
    if (!$internal && IMS_Preview() && SHIELD_HasObjectRight ($sitecollection_id, $object_id, "edit")) {
      ML_UseProdLanguage();

      T_Start("black");
      echo ML ("Formulier opties", "Form options");
      T_NewRow();

      $form = array();
      $form["title"] = ML("Beschikbare velden", "Available fields");
      $form["formtemplate"] = '
        <table>';
      $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
      if (!$allfields) $allfields=array();
      ksort ($allfields);
      if (is_array($allfields)) reset ($allfields);
      if (is_array($allfields)) foreach ($allfields as $tag => $specs) {
        $form["formtemplate"] .= '<tr><td><font face="arial" size=2><b>'.$specs["title"].':</b></font></td><td>[[['.$tag.']<b></b>]]</td></tr>';
      }
      $form["formtemplate"] .= '
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
        </table>
      ';
      $url = FORMS_URL ($form);
      echo '<a href="'.$url.'" class="ims_link" title="'.ML("Toon de velden die in dit formulier gebruikt kunnen worden","Show available fields").'">'.ML("Toon velden","Show fields").'</a>';
      if (SHIELD_HasGlobalRight (IMS_SuperGroupName(), "system")) {
        $url = "/openims/openims.php?mode=admin&submode=fields";
        echo ' (<a href="'.$url.'" class="ims_link" title="'.ML("Wijzig de velden die in dit formulier gebruikt kunnen worden","Edit fields").'">'.ML("wijzig","edit").'</a>)';
      }
      echo '<br>';

      global $myconfig;
      $editor = "winword.exe";
      $url =  IMS_GenerateTransferURL("\\".$sitecollection_id."\\preview\\objects\\".$object_id."\\", "page.html", $editor, true);
      echo '<a href="'.$url.'" class="ims_link" title="'.ML("Wijzig de inhoud (content) van dit formulier","Edit the content fo this form").'">'.ML("Inhoud","Content").'</a><br>';

      $url =  IMS_GenerateTransferURL("\\".$sitecollection_id."\\preview\\objects\\".$object_id."\\", "confirm.html", $editor, true);
      echo '<a href="'.$url.'" class="ims_link" title="'.ML("Wijzig de tekst die getoond wordt aan mensen die dit formulier hebben ingevuld","Edit the confirmation text").'">'.ML("Bevestiging","Confirmation").'</a><br>';

      if ($object["editor"] == "Form") {

        $form = array();
        $form["title"] = "Signalering";
        $form["input"]["supergroupname"] = $sitecollection_id;
        $form["input"]["object_id"] = $object_id;
        $form["input"]["field"] = $field;
        $form["metaspec"]["fields"]["signal"]["type"] = "string";
        $form["precode"] = '
          $object = &MB_Ref ("ims_".$input["supergroupname"]."_objects", $input["object_id"]);
          $data["signal"] = $object["form"]["signal"];
        ';
        $form["formtemplate"] = '
          <table>
            <tr><td><font face="arial" size=2><b>'.ML("Stuur signaal naar","Send signal to").':</b></font></td><td>[[[signal]]]</td></tr>
            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["postcode"] = '
          $object = &MB_Ref ("ims_".$input["supergroupname"]."_objects", $input["object_id"]);
          $object["form"]["signal"] = $data["signal"];
        ';
        $url = FORMS_URL ($form);
        echo '<a href="'.$url.'" class="ims_link" title="'.ML("Specificeer wie er een signaal moeten krijgen als dit formulier wordt ingevuld","Specify who should get signals").'">'.ML("Signalering","Signals").'</a><br>';

        if ($object["bpmsstorage"]) {
          $form = array();
          $form["title"] = ML("Download ingevoerde gegevens", "Download collected data");
          $form["input"]["sgn"] = $sitecollection_id;
          $form["input"]["object_id"] = $object_id;
          $form["metaspec"]["fields"]["fromdate"]["type"] = "date";
          $form["metaspec"]["fields"]["todate"]["type"] = "date";
          $form["metaspec"]["fields"]["delim"]["type"] = "list";
          $form["metaspec"]["fields"]["delim"]["title"] = ML("Scheidingsteken", "Delimiter");
          $form["metaspec"]["fields"]["delim"]["method"] = "radiover";
          $form["metaspec"]["fields"]["delim"]["values"][", (" . ML("standaard","standard") . ")"] = ",";
          $form["metaspec"]["fields"]["delim"]["values"]["; (" . ML("Excel met Nederlandse regio-instellingen", "Excel with Dutch regional settings") . ")"] = ";";
          $form["metaspec"]["fields"]["delim"]["default"] = ",";
          if (ML_GetLanguage() == "nl") $form["metaspec"]["fields"]["delim"]["default"] = ";";
          $form["formtemplate"] = '
            <style>
              body, div, p, th, td, li, dd {
              font-family: Arial, Helvetica, sans-serif;
              font-size: 13px;
              }
            </style>
            <table width="400">
              <tr><td colspan=2><b>' . ML("Download ingevoerde gegevens in CSV-formaat", "Download collected data in CSV format") . '</b></font></td></tr>
              <tr><td colspan=2>'.ML("Ingevuld tussen %1 en %2", "Submitted between %1 and %2", '[[[fromdate]]]', '[[[todate]]]').'</td></tr>
              <tr><td>{{{delim}}}</td><td>[[[delim]]]</td></tr>
              <tr><td colspan=2>&nbsp;</td></tr>
              <tr><td colspan=2><center>[[[OK]]]</center></tr></tr>
            </table>
          ';
          $form["precode"] = '$data["fromdate"] = 1; $data["todate"] = 2147483647;';
          $form["postcode"] = '
            $fromdate = N_BuildDateTime(N_Date2Year($data["fromdate"]), N_Date2Month($data["fromdate"]), N_Date2Day($data["fromdate"]), 0, 0, 0);
            $todate = N_BuildDateTime(N_Date2Year($data["todate"]), N_Date2Month($data["todate"]), N_Date2Day($data["todate"]), 23, 59, 59);
            $url = "/openims/csv.php?mode=cmsformbpmsexport&supergroupname={$input["sgn"]}&object_id={$input["object_id"]}&delim=".urlencode($data["delim"])."&fromdate={$fromdate}&todate={$todate}";
            $gotook = "closeme&parentgoto:$url"; // The MIME type will make it a popup anyway.
          ';
          echo '<a href="'.FORMS_URL($form).'" class="ims_link" title="'.ML("Ingevoerde gegevens","Collected data").'">'.ML("Ingevoerde gegevens","Collected data").'</a>';

        } else {
          $url_uk = "/openims/csv.php?supergroupname=$sitecollection_id&object_id=$object_id";
          $url_nl = "/openims/csv.php?supergroupname=$sitecollection_id&object_id=$object_id&csv=nl";
          echo ML("Ingevoerde gegevens","Collected data").' '.
               '('.
                  '<a href="'.$url_nl.'" class="ims_link" title="'. ML("Ingevoerde gegevens (nl)","Collected data (nl)").'">'.'nl csv'.'</a>'.
                  ' / '.
                  '<a href="'.$url_uk.'" class="ims_link" title="'. ML("Ingevoerde gegevens (uk)","Collected data (uk)").'">'.'uk csv'.'</a>'.
               ')';
        }



// THB delete collected data start
        echo "<br>";
        if (!$object["bpmsstorage"]) {
          if (SHIELD_HasGlobalRight (IMS_SuperGroupName(), "system")) {
            $form = array();
            $form["title"] = ML("Wis alle ooit ingevoerde gegevens", "Delete all collected data");
            $form["input"]["sgn"] = $sitecollection_id;
            $form["input"]["object_id"] = $object_id;

            $form["metaspec"]["fields"]["sure"]["type"] = "list";
            $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
            $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";

            $form["formtemplate"] = '
               <table>
                <tr><td colspan=2><font face="arial" size=2><b>' . ML("Wis alle ooit ingevoerde gegevens", "Delete all collected data") . '</b></font></td></tr>
                <tr><td colspan=2>&nbsp</td></tr>
                <tr><td><font face="arial" size=2><b>' . ML("Weet u het zeker?","Are you sure?") . '</b></font></td><td>[[[sure]]]</td></tr>
                <tr><td colspan=2>&nbsp</td></tr>
                <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
              </table>
            ';
            $form["postcode"] = '
              $sure = $data["sure"];
              $sgn = $input["sgn"];
              $object_id = $input["object_id"];

              if($sure == "yes") {
                $table = "ims_" . $sgn . "_objects_objectdata";
                for ($i=1; $i<=10000; $i++) {
                  $key = $object_id . "__" . $i;
                  $entry = MB_Ref($table, $key);
                  if($entry) {
                    MB_Save ($table . "_deleted_items", $key . "_" . N_Guid(), $entry);
                    MB_Delete($table, $key);
                    MB_Flush();
                  } else {
                    break;
                  }
                }
                $key = $object_id;
                $entry = MB_Ref($table, $key);
                if($entry) {
                  MB_Save ($table . "_deleted_items", $key . "_" . N_Guid(), $entry);
                  MB_Delete($table, $key);
                  MB_Flush();
                }
              }
            ';

            $url = FORMS_URL ($form);
            echo '<a href="' . $url . '" class="ims_link" title="' . ML("Wis alle ooit ingevoerde gegevens", "Delete all collected data") . '">' .
                 ML("Wis alle ooit ingevoerde gegevens", "Delete all collected data") . '</a><br>';
          }
          // THB delete collected data finish
        }

        

        if ($myconfig[$sitecollection_id]["advancedformsettings"] == "yes") {

          $form = array();
          $form["title"] = ML("Geavanceerde instellingen","Advanced settings");
          $form["input"]["supergroupname"] = $sitecollection_id;
          $form["input"]["object_id"] = $object_id;
          $form["metaspec"]["fields"]["subjectfield"]["type"] = "list";
          $form["metaspec"]["fields"]["subjectfield"]["sort"] = "yes";
          $form["metaspec"]["fields"]["extratofield"]["type"] = "list";
          $form["metaspec"]["fields"]["extratofield"]["sort"] = "yes";
          $form["metaspec"]["fields"]["ccfield"]["type"] = "list";
          $form["metaspec"]["fields"]["ccfield"]["sort"] = "yes";
          $form["metaspec"]["fields"]["bccfield"]["type"] = "list";
          $form["metaspec"]["fields"]["bccfield"]["sort"] = "yes";
          $form["metaspec"]["fields"]["htmlmail"]["type"] = "yesno";

          $form["precode"] = '
             $object = MB_Ref ("ims_".$input["supergroupname"]."_objects", $input["object_id"]);
             $data["htmlmail"] = $object["form"]["htmlmail"];
             $metaspec["fields"]["subjectfield"]["default"] = $object["form"]["subjectfield"];
             $metaspec["fields"]["extratofield"]["default"] = $object["form"]["extratofield"];
             $metaspec["fields"]["ccfield"]["default"] = $object["form"]["ccfield"];
             $metaspec["fields"]["bccfield"]["default"] = $object["form"]["bccfield"];

             $metaspec["fields"]["subjectfield"]["values"]["Geen"] = "";
             $metaspec["fields"]["extratofield"]["values"]["Geen"] = "";
             $metaspec["fields"]["ccfield"]["values"]["Geen"] = "";
             $metaspec["fields"]["bccfield"]["values"]["Geen"] = "";
             $allfields = MB_Ref ("ims_fields", $input["supergroupname"]);
             foreach ($allfields as $fieldname => $values) {
               $metaspec["fields"]["subjectfield"]["values"][$values["title"]] = $fieldname;
               $metaspec["fields"]["extratofield"]["values"][$values["title"]] = $fieldname;
               $metaspec["fields"]["ccfield"]["values"][$values["title"]] = $fieldname;
               $metaspec["fields"]["bccfield"]["values"][$values["title"]] = $fieldname;
             }
          ';

          $form["formtemplate"] = '
            <table>
              <tr><td><font face="arial" size=2><b>'.ML("Gebruik veld als onderwerp","Use field as subject").':</b></font></td><td>[[[subjectfield]]]</td></tr>
              <tr><td><font face="arial" size=2><b>'.ML("Gebruik veld als extra verzendadres","Use field as extra e-mailaddress").':</b></font></td><td>[[[extratofield]]]</td></tr>
              <tr><td><font face="arial" size=2><b>'.ML("Gebruik veld als cc adres","Use field as cc e-mailaddress").':</b></font></td><td>[[[ccfield]]]</td></tr>
              <tr><td><font face="arial" size=2><b>'.ML("Gebruik veld als bcc adres","Use field as bcc e-mailaddress").':</b></font></td><td>[[[bccfield]]]</td></tr>
              <tr><td><font face="arial" size=2><b>'.ML("Verstuur e-mail als HTML","Send e-mail using HTML").':</b></font></td><td>[[[htmlmail]]]</td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          ';
          $form["postcode"] = '
            $object = &MB_Ref ("ims_".$input["supergroupname"]."_objects", $input["object_id"]);
            $object["form"]["subjectfield"] = $data["subjectfield"];
            $object["form"]["extratofield"] = $data["extratofield"];
            $object["form"]["ccfield"] = $data["ccfield"];
            $object["form"]["bccfield"] = $data["bccfield"];
            $object["form"]["htmlmail"] = $data["htmlmail"];
          ';
          $url = FORMS_URL ($form);

          echo '<br>';
          echo '<a href="'.$url.'" class="ims_link" title="'.ML("Geavanceerde instellingen","Advanced settings").'">'.ML("Instellingen","Settings").'</a>';
        }

        if (SHIELD_HasGlobalRight (IMS_SuperGroupName(), "develop")) {

          // Precode / postcode voor CMS formulieren
          $form = array();
          $form["title"] = ML("Geavanceerde formulierafhandeling (PHP)", "Advanced form handling (PHP)");
          $form["input"]["supergroupname"] = $sitecollection_id;
          $form["input"]["object_id"] = $object_id;
          $form["metaspec"]["fields"]["precode"]["type"] = "verywidetext";
          $form["metaspec"]["fields"]["postcode"]["type"] = "verywidetext";
          $form["formtemplate"] = '
            <table>
              <tr><td><font face="arial" size=2><b>'.ML("Precode (PHP)", "Precode (PHP)").':</b><br/>'.ML("Gegevens","Data").': $data<br/>'.ML("Precode wordt niet uitgevoerd in concept-modus. WAARSCHUWING: Fouten maken de pagina in site-modus onbruikbaar!", "Precode will not be executed in concept-mode. WARNING: Errors will make the page (in site-mode) unusable.").'</font></td></tr>
              <tr><td>[[[precode]]]</td></tr>
              <tr><td><font face="arial" size=2><b>'.ML("Postcode (PHP)", "Postcode (PHP)").':</b><br/>Input: $input["testrun"], $input["supergroupname"], $object["form"], $data, $postdata<br/>Output: $data (csv), $postdata (email)</font></td></tr>
              <tr><td>[[[postcode]]]</td></tr>
              <tr><td>&nbsp</td></tr>
              <tr><td><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]&nbsp;&nbsp;&nbsp;[[[RESET]]]</center></td></tr>
            </table>
          ';
          $form["precode"] = '
            $object = MB_Ref ("ims_".$input["supergroupname"]."_objects", $input["object_id"]);
            $data["precode"] = $object["form"]["precode"];
            $data["postcode"] = $object["form"]["postcode"];
          ';
          $form["postcode"] = '
            $object = &MB_Ref ("ims_".$input["supergroupname"]."_objects", $input["object_id"]);
            $object["form"]["precode"] = $data["precode"];
            $object["form"]["postcode"] = $data["postcode"];
          ';
          $eurl = FORMS_URL ($form);
          echo "<br><a class=\"ims_link\" href=\"$eurl\" title=\"".$form["title"]."\">".$form["title"]."</a>";

        }

      }

      $output .= TS_End()."<br>";

      T_Start("black");
      echo ML ("Formulier","Form");
      T_NewRow();
      ML_UseSiteLanguage($sitecollection_id, IMS_Object2Site ($sitecollection_id, $object_id));
      echo IMS_ShowCMSForm($sitecollection_id, $object_id, $path, true);
      ML_UseProdLanguage();
      $output .= TS_End();
      $output .= "<br>";

      T_Start("black");
      echo ML("Formulier bevestiging", "Confirmation");
      T_NewRow();
      ML_UseSiteLanguage($sitecollection_id, IMS_Object2Site ($sitecollection_id, $object_id));
      echo N_ReadFile ($path."confirm.html");
      $output .= TS_End();
    } else { // Site-modus (bezoekers)
       if ($object["editor"] == "BPMSForm") {
        global $done;
        if ($done=="yes") {
          $output = N_ReadFile ($path."confirm.html");
        } else {
          global $process_id, $theprocess, $thecase, $rebuild, $prepfield, $prepdata;
          $form = array();
          $form["input"]["col"] = IMS_SuperGroupName();
          $form["input"]["object_id"] = $object_id;
          $form["input"]["theprocess"] = $theprocess;
          $form["input"]["thecase"] = $thecase;
          $form["input"]["rebuild"] = $rebuild;
          $form["input"]["prepfield"] = $prepfield;
          $form["input"]["prepdata"] = $prepdata;
          $form["input"]["process_id"] = $process_id;
          global $editonly, $new;
          $form["input"]["editonly"] = $editonly;
          $form["input"]["new"] = $new;
          $form["formtemplate"] = HTML_RawContentFilter (N_ReadFile ($path."page.html"));
          $allfields = MB_Ref ("ims_fields", $sitecollection_id);
          $form["metaspec"]["fields"] = $allfields;
          $form["gotook"] = N_MyBareURL()."?done=yes";

          $form["precode"] = '
            if ($input["theprocess"]) {
              $process_id = $input["theprocess"];
            } else {
              list ($process_id, $stage, $initial) = BPMS_LocateForm ($input["object_id"]);
            }
            $process = SHIELD_AccessProcess ($input["col"], $process_id);
            uuse ("bpms");
            $data = BPMS_GetFormData ($input);
            if ($input["prepfield"]) {
              $data[$input["prepfield"]] = $input["prepdata"];
            }
            if ($process["precode"]) eval ($process["precode"]);
          ';
          $form["postcode"] = '
            if ($input["theprocess"]) {
              $process_id = $input["theprocess"];
            } else {
              list ($process_id, $stage, $initial) = BPMS_LocateForm ($input["object_id"]);
            }
            $process = SHIELD_AccessProcess ($input["col"], $process_id);
            uuse ("bpms");
            if ($process["postcode"]) eval ($process["postcode"]);
            if ($input["prepfield"] && !$data[$input["prepfield"]]) {
              $data[$input["prepfield"]] = $input["prepdata"];
            }
            if ($input["new"]=="yes") {
              global $newcase;
              if (!$newcase) {
                $newcase = N_GUID();
              }
            }
            if (!$input["new"] && !$input["editonly"]) {
              if (!$input["thecase"]) {
                if ($initial) { // initial form of a process
                  global $newcase;
                  if (!$newcase) {
                    $newcase = N_GUID();
                  }
                }
              }
            }
            BPMS_ProcessFormData ($input, $data);
            if ($input["new"]=="yes") {
              if ($process["postpostcode"]) eval ($process["postpostcode"]);
            }
            if (!$input["new"] && !$input["editonly"]) {
              if (!$input["thecase"]) {
                if ($initial) { // initial form of a process
                  if ($process["postpostcode"]) eval ($process["postpostcode"]);
                }
              }
            }
          ';
          $output .= FORMS_GenerateSuperForm ($form);
        }
      } else { // if $object["editor"] == "Form"
        global $done;
        global $myconfig;
        if ($done != "") {
          $formdataguid = $done;
          
          if ($process_id) {
            $storage = MB_Load("process_{$sitecollection_id}_cases_{$process_id}", $formdataguid);
            $formdata = $storage["data"];
            foreach ($formdata as $fieldname => $fieldvalue) {
              if (substr($fieldname, -5) == "_name") $formdata[substr($fieldname, 0, strlen($fieldname) - 5)] = $fieldvalue;
            }
          } else {
            $storage = MB_Ref("ims_". IMS_SupergroupName() ."_objects_objectdata", $object_id);
            $formdata = $storage[$formdataguid];
          }
          $allfields = MB_Ref("ims_fields", IMS_SupergroupName());

          $output = N_ReadFile ($path."confirm.html");
          if ($myconfig[$sitecollection_id]["formprint"] == "yes" ) {
            $output .= HTML_RawContentFilter(N_ReadFile ($path."page.html"));
            $output = IMS_TagReplace($output, "ok", "", true);
            $output = IMS_TagReplace($output, "cancel", "", true);
            // this printable confirmation used to have an 'ok' button at the end. now this has been removed, it's not really a form anymore
            // but i'm just going to leave it like this; JH;  20090219 not any more now it's text;
          }
          $output = IMS_CleanupTags($output);

          foreach ($formdata as $fdkey => $fdval) {
            $showval = FORMS_ShowValue($fdval, $fdkey);
            $showname = $allfields[$fdkey]["title"];
            //$output = IMS_RemoveCMSComponents($output);
            //$output = IMS_MakeFieldIdsLowercase($output);
            $output = str_ireplace("{{{" . $fdkey . ":}}}", $showname, $output); // : needed because of IMS_CleanupTags
            $output = IMS_TagReplace($output, $fdkey, $showval, true);
          }
          // Remove all tags that are still left (e.g. CMS components)
          $output = preg_replace('/\[\[\[[^\[]*\]\]\]/', '', $output);
        } else {
          $output .= IMS_ShowCMSForm($sitecollection_id, $object_id, $path);
        }
      }
    }
  } else {
    $output = IMS_CleanupTags (HTML_RawContentFilter (IMS_GetPageContent($sitecollection_id, $object_id, $path, "page.html", !IMS_Preview())));
  }
  global $mark;

  if ($mark) {
    if (function_exists ("IMS_MarkPage"))
    {
      $output = IMS_MarkPage($output,$mark);
    }
    else
    {
      $output = " ".$output." ";
      $words = explode (" ", SEARCH_TEXT2WORDSQUERY(SEARCH_REMOVEACCENTS(strtolower ($mark))));
      $count = count ($words);
      for ($i=0; $i<$count; $i++) {
        if (trim($words[$i])) {
          $search = $words[$i];
          $output = preg_replace("/(\>(((?>[^><]+)|(?R))*)\<)/ie", "preg_replace('/(?>$search+)/i', '<b style=\"color:black;background-color:#ffff66\">$search</b>', '\\0')", $output);
        }
      }
      $output = trim ($output);
    }
  }

  if (IMS_Preview()) {
    $contentlocation =  "/".$sitecollection_id."/preview/objects/".$object_id."/";
  } else {
    $contentlocation =  "/".$sitecollection_id."/objects/".$object_id."/";
  }
  $output = IMS_Relocate ($output, $contentlocation);
  $output = IMS_Improve ($output);

  return $output;
}

function IMS_Domain2Siteinfo ($domain)
{
  N_Debug ("IMS_Domain2Siteinfo ($domain);");
  global $ims_domain2siteinfo;

  if (!$ims_domain2siteinfo) {
    global $myconfig;
    if ($myconfig["readonlyserver"] == "yes") {
      // index expression is server-dependent, so the indexen that the slave needs are never generated on the master
     $result = MB_SelectQuery ("ims_sites", "\$record[\"domains\"][\"$domain\"][\"dummy\"]==\"dummy\"", true);
    } else {
      $result = MB_TurboSelectQuery ("ims_sites", "\$record[\"domains\"][\"".addslashes($domain)."\"][\"dummy\"]==\"dummy\"", true);
    }
    if (is_array($result)) {
      reset ($result);
      list($key)=each($result);
      $record = &MB_Ref ("ims_sites", $key);
      $ims_domain2siteinfo = array ("site" => $key, "sitecollection" => $record["sitecollection"]);
    }
  }

  return $ims_domain2siteinfo;
}

function IMS_CleanupTags ($content)
{
  $key = DFC_Key ($content."v2");
  if (DFC_Exists ($key)) {
    $content = DFC_Read ($key);
  } else {
    $content = preg_replace ("'(\[\[\[(<[^<>]*>)*([0-9a-z_-]*)(<[^<>]*>)*(:([^]]*))?\]\]\])'sie",
            '"[[[".strtolower("\\3").":".preg_replace ("|<[^<>]*>|si", "", str_replace("\\\'","\'","\\6"))."]]]"', $content);
    $content = preg_replace ("'(\(\(\((<[^<>]*>)*([0-9a-z_-]*)(<[^<>]*>)*(:([^)]*))?\)\)\))'sie",
            '"(((".strtolower("\\3").":".preg_replace ("|<[^<>]*>|si", "", str_replace("\\\'","\'","\\6")).")))"', $content);
    $content = preg_replace ("'(\{\{\{(<[^<>]*>)*([0-9a-z_-]*)(<[^<>]*>)*(:([^}]*))?\}\}\})'sie",
            '"{{{".strtolower("\\3").":".preg_replace ("|<[^<>]*>|si", "", str_replace("\\\'","\'","\\6"))."}}}"', $content);
    DFC_Write ($key, $content);
  }
  return $content;
}

function IMS_TagCount ($content, $tag)
{
  $tag = strtolower ($tag);
  while ($pos = strpos (" ".$content, "[[[".$tag.":", $pos + 1)) $ctr++;
  return $ctr;
}

function IMS_TagExists ($content, $tag)
{
  $tag = strtolower ($tag);
  if (strpos (" ".$content, "[[[".$tag.":")) return true;
  return false;
}

function IMS_TagParams ($content, $tag)
{
  $tag = strtolower ($tag);
  $pos1 = strpos (" ".$content, "[[[$tag:");
  $pos2 = strpos ($content, "]]]", $pos1);
  $result = substr ($content, $pos1+strlen($tag)+3, $pos2-$pos1-strlen($tag)-3);
  $result = str_replace (chr(13).chr(10), " ", $result);
  return $result;
}

function IMS_TagReplace ($content, $tag, $replace, $all=false)
{
  if ($all) {
    while (IMS_TagExists ($content, $tag)) {
      $content = IMS_TagReplace ($content, $tag, $replace);
      $i++;
      if ($i > 10000) break;
    }
    return $content;
  } else {
    $tag = strtolower ($tag);
    $pos1 = strpos (" ".$content, "[[[$tag:");
    if ($pos1) {
      $pos2 = strpos ($content, "]]]", $pos1);
      if ($pos2 > 0) {
         $result = substr ($content, 0, $pos1-1).$replace.substr ($content, $pos2+3);
      } else {
         $result = substr ($content, 0, $pos1-1)."<b><font color=red>ERROR: Incorrect tag syntax</font></b> ".substr ($content, $pos1+3-1);
      }
    } else {
      $result = $content;
    }
    return $result;
  }
}



function IMS_GenerateDynamicPage ($sitecollection_id, $site_id, $object_id)
{
  IMS_CaptureHtmlHeaders(); // If we call IMS_CaptureHtmlHeaders, we MUST also call IMS_MergeHtmlHeaders before exiting the function

  if (!function_exists ("IMS_HtmlContentFilter")) {
   $internal_component = FLEX_LoadImportableComponent ("support", "878a2f3118ee7a8a5e7d914d3e961ad3");
   $internal_code = $internal_component["code"];
   eval ($internal_code);
  }

  include_once (N_CleanPath ("html::"."/openims/mix.php"));
  global $siteinfo;
  global $usetemplate;
  $siteinfo = IMS_SiteInfo($sitecollection_id, $site_id, $object_id);
  $pageinfo = IMS_DetermineSite();

  if (IMS_Preview()) {
    if ($usetemplate && MB_Ref("ims_".$pageinfo["sitecollection"]."_templates", $usetemplate)) { // check if template exists in database
      $templatelocation = "/".$pageinfo["sitecollection"]."/preview/templates/$usetemplate/";
    } else {
      $templatelocation = "/".$pageinfo["sitecollection"]."/preview/templates/".$pageinfo["template"]."/";
    }
    $contentlocation =  "/".$pageinfo["sitecollection"]."/preview/objects/".$pageinfo["object"]."/";
  } else {
    if ($usetemplate && MB_Ref("ims_".$pageinfo["sitecollection"]."_templates", $usetemplate)) { // check if template exists in database
      $templatelocation = "/".$pageinfo["sitecollection"]."/templates/$usetemplate/";
    } else {
      $templatelocation = "/".$pageinfo["sitecollection"]."/templates/".$pageinfo["template"]."/";
    }
    $contentlocation =  "/".$pageinfo["sitecollection"]."/objects/".$pageinfo["object"]."/";
  }

  $template = IMS_CleanupTags (N_ReadFile ("html::".$templatelocation."template.html"));
  if (!$template) {
    if ($usetemplate) {
      if (IMS_Preview()) {
        $template = IMS_CleanupTags('<html><body>[[[coolbar]]]<br/><i>' . ML("Fout", "Error") . ': ' . ML("Template (in url) bestaat niet", "Template (from url) doesn't exist") . '</i><br/><br/>[[[content]]]</body></html>');
      } else {
        // do nothing. Users should not be able to see the [[[content]]] of content-less templates (like news etc.) by url manipulation
      }
    } else {
      if (IMS_Preview()) {
        $template = IMS_CleanupTags('<html><body>[[[coolbar]]]<br/><i>' . ML("Fout", "Error") . ': ' . ML("Template bestaat niet", "Template doesn't exist") . '</i><br/><br/>[[[content]]]</body></html>');
      } else {
        $template = IMS_CleanupTags('<html><body>[[[content]]]</body></html>');
      }
    }
  }
  global $IMS_HasHtmlDoctype;
  if (strpos(substr($template, 0, 100), "-//W3C//DTD")) $IMS_HasHtmlDoctype = true;

  ML_UseSiteLanguage($sitecollection_id, $site_id);

  uuse ("flex");
  $all = FLEX_LocalComponents (IMS_SuperGroupName(), "cmsblock");

  $content = IMS_GetObjectContent ($sitecollection_id, $object_id);

  uuse("tinymce");
  $inplace = TINYMCE_isinplace($object_id);

  if (IMS_Preview()) {
    $content = IMS_HtmlChecker ($content).$content;
  }

  if (IMS_Preview() and $inplace)
  {
    $htmlpath = "/".$sitecollection_id."/preview/objects/".$object_id."/";
    $loc_encodedsettings = TINYMCE_DetermineSettings($htmlpath, "", "", "");
    $content = TINYMCE_handle_tinymce($loc_encodedsettings);
  }

  $content = IMS_HtmlContentFilter ($content);

  foreach ($all as $id => $specs) {
    while (IMS_TagExists($content, $specs["tag"])) {
      global $flexparams;
      $flexparams = IMS_TagParams($content, $specs["tag"]);
      $content = IMS_TagReplace ($content, $specs["tag"], FLEX_Call (IMS_SuperGroupName(), "cmsblock", $id, "content"));
    }
  }

  foreach ($all as $id => $specs) {
    while (IMS_TagExists($template, $specs["tag"])) {
      global $flexparams;
      $flexparams = IMS_TagParams($template, $specs["tag"]);
      $template = IMS_TagReplace ($template, $specs["tag"], FLEX_Call (IMS_SuperGroupName(), "cmsblock", $id, "content"));
    }
  }

  $template = IMS_Relocate ($template, $templatelocation);

  $content = str_replace ("[[[content:]]]", $content, $template);

  $content = IMS_UseMetadata ($content, $siteinfo);

  ML_UseProdLanguage();
  if (IMS_TagExists ($content, "coolbar")) $content = str_replace ("[[[coolbar:]]]", IMS_CoolBar(), $content);
  ML_UseSiteLanguage($sitecollection_id, $site_id);

  // build in default components (and/or simulation of old mixer code)
  $content = str_replace ("[[[year:]]]", N_Year(), $content);
  if (IMS_TagExists ($content, "verticalmenu")) $content = IMS_TagReplace ($content, "verticalmenu", IMS_Sitemap ($sitecollection_id, $site_id, $object_id));
  if (IMS_TagExists ($content, "clickpath")) $content = IMS_TagReplace ($content, "clickpath", IMS_CLickPath ($sitecollection_id, $site_id, $object_id, 'face="arial,helvetica" size="2"'));
  if (IMS_TagExists ($content, "opensourcenews")) $content = IMS_TagReplace ($content, "opensourcenews", MB_Load ("news_blocks", "opensource_mini"));
  if (IMS_TagExists ($content, "opensourcenieuws")) $content = IMS_TagReplace ($content, "opensourcenieuws", MB_Load ("news_blocks", "opensource_mini"));
  if (IMS_TagExists ($content, "openimsnieuws")) $content = IMS_TagReplace ($content, "openimsnieuws", MB_Load ("news_blocks", "openims_mini"));
  if (IMS_TagExists ($content, "nieuws") && $sitecollection_id=="osict_sites") {
    $news = IMS_GetObjectContent ("osict_sites", "2678f8d3de0160f7b7b38d379679e6dd");
    if (IMS_Preview()) {
      $news .= '<br><b><a href="/osict_com/2678f8d3de0160f7b7b38d379679e6dd.php"><font size="2">Wijzig content nieuwsblok</a></b>';
    }
    $content = IMS_TagReplace ($content, "nieuws", $news);
  }
  if (IMS_TagExists ($content, "print") && $sitecollection_id=="openims_sites") {
    $content = IMS_TagReplace ($content, "print", N_MyFullURL()."?usetemplate=dc38e709d7d95b7b2a2988d7a8f9ef8d");
  }

  // if IMS_CoolBar was never called (no [[[coolbar]]]-tag in template AND no CMS component that calls IMS_CoolBar either)
  global $ims_coolbar_called;
  if (!$ims_coolbar_called) {
    ML_UseProdLanguage();
    $coolbar = IMS_CoolBar(); // IMS_CoolBar checks IMS_Preview and lots of other conditions
    ML_UseSiteLanguage($sitecollection_id, $site_id);
    if ($coolbar) {
      if (($pos1 = stripos($content, '<body')) !== false) {
        // $content = preg_replace('/<body([^>]*)>/', '<body$1>' . $coolbar, $content, 1); // Is there a preg_quote that only escaped $1 and \1 and other stuff that has special meaning in the replacement string?
        $pos2 = strpos($content, '>', $pos1);
        if ($pos2) {
          $content = substr($content, 0, $pos2 + 1) . $coolbar . substr($content, $pos2 + 1);
        } else {
          $content = $coolbar . $content;
        }
      } else {
        $content = $coolbar . $content;
      }
    }
  }

  ML_UseProdLanguage();

  $content = IMS_MergeHtmlHeaders($content);

  return $content;
}

function IMS_SuperMixer ($sitecollection_id, $site_id, $object_id, $command)
{
  uuse("flex");
  FLEX_LoadSupportFunctions (IMS_SuperGroupName());

  if ($command=="generate_dynamic_page") {
    global $myconfig;
    if ($myconfig["serverhasniceurl"] == "yes") NICEURL_AutoRedirect ($sitecollection_id, $object_id);
  }

  if (!function_exists ("IMS_HtmlFilter")) {
   $internal_component = FLEX_LoadImportableComponent ("support", "edeb53c7d5aa69d7ac2bf3ad6f5b145d");
   $internal_code = $internal_component["code"];
   eval ($internal_code);
  }

  if (!function_exists ("IMS_HtmlChecker")) {
   $internal_component = FLEX_LoadImportableComponent ("support", "c0f1edd1eeae83ba197cfb9f170cb3b8");
   $internal_code = $internal_component["code"];
   eval ($internal_code);
  }

  if ($command=="generate_dynamic_page") {
    $pageobject = IMS_AccessObject ($sitecollection_id, $object_id);

    // check on visibility of the page
    $visibility_show = true;

    global $myconfig;
    if ($myconfig[IMS_SuperGroupName()]["disabledatecheckonpageaccess"] != "yes") {
      if ( (!IMS_Preview()) && ($pageobject["parameters"]["published"]["from"].""!="") ) {
         if ( ($pageobject["parameters"]["published"]["from"] > time()) || ($pageobject["parameters"]["published"]["until"] < time()) ) {
            $my_root = IMS_Root($sitecollection_id, $site_id, $object_id);
            if ($my_root != $object_id) {
               $visibility_show = false;
            }
         }
      }
    }

    if ( ($pageobject["preview"]<>"yes" && $pageobject["published"]<>"yes") ||
         (!IMS_Preview() && $pageobject["published"]<>"yes") ||
         ($visibility_show == false)
       ) {
      $siteinfo = IMS_DetermineSite ();
      $site = MB_Ref ("ims_sites", $siteinfo["site"]);
      $homepage = $site["homepage"];

      global $myconfig;

      // Default behaviour: show homepage but do not change the url in the browsers' address bar (except in preview mode)
      // redirectonerror: show homepage and change the url in the browsers's address bar
      // custom404: show custom page and change the url in the browsers's address bar
      // redirectonerror and custom404 are mutually exclusive, if you use them both, the behaviour is inconsistent.
      global $HTTP_HOST;
      if (($myconfig[IMS_SuperGroupName()]["cms"]["redirectonerror"] == "yes") && ($siteinfo["object"] != $homepage)) {
        N_Redirect (N_CurrentProtocol().$HTTP_HOST, 404);
      } else {
        if ($myconfig["custom404"]) {
          N_Redirect ($myconfig["custom404"], 404);
        } else {
          if (IMS_Preview()) {
             N_Redirect (N_CurrentProtocol().$HTTP_HOST, 404);
          }
          Header ("HTTP/1.1 404"); // Needed to make link checker understand what it should do
          Header ("Status: 404"); // fastcgi
          include (N_CleanPath ("html::"."/".$siteinfo["site"]."/$homepage.php"));
          N_Exit();
          die ("");
        }
      }
     }
  }

  N_Debug ("IMS_SuperMixer ($sitecollection_id, $site_id, $object_id, $command)");
  N_ErrorHandling (false);

  if (!getenv("REDIRECT_ERROR_NOTES") && $_SERVER["REDIRECT_STATUS"]!="404") {
     N_SetCookie ("ims_myurl_".str_replace (".", "_", strtolower (getenv("HTTP_HOST"))), N_MyFullURL (), time()+100000, "/", "", (N_CurrentProtocol() == "https://"), true);
  }

  N_ErrorHandling (true);
  global $siteinfo;
  $siteinfo = IMS_SiteInfo($sitecollection_id, $site_id, $object_id);

  if ($command=="generate_dynamic_page") {
    $output = IMS_GenerateDynamicPage ($sitecollection_id, $site_id, $object_id);
  } else {
    $template_id = $siteinfo["template"];
    include_once (N_CleanPath ("html::"."/openims/mix.php"));
    include_once (N_CleanPath ("html::"."/".$sitecollection_id."/mix.php"));
    if (IMS_Preview() || $command=="generate_static_page") {
      $code = N_ReadFile ("html::".$sitecollection_id."/preview/templates/".$template_id."/mix.php");
      eval ("?>".$code."<?");
    } else {
      $code = N_ReadFile ("html::".$sitecollection_id."/templates/".$template_id."/mix.php");
      eval ("?>".$code."<?");
    }
  }

  if ($command=="generate_dynamic_page") {

    // Remove Microsoft "smart" tags
    global $myconfig;
    if ($myconfig[IMS_Supergroupname()]["allowsmarttags"]<>"yes") {
      $output = preg_replace ("'<object([ \n\r])*classid([ \n\r])*=([ \n\r])*\"clsid:38481807-CA0E-42D2-BF39-B33AF135CC4D\"([ \n\r])*id=([a-z])*>([ \n\r])*</object>'", "", $output);
    }

    $output = IMS_HtmlFilter($output);

    global $myconfig;
    if ($myconfig[$sitecollection_id]["showcreatedwith"] != "no") {
      IMS_CaptureHtmlHeaders();
      // Why use Capture/Merge both here and in IMS_GenerateDynamicPage, instead of a single call "around" IMS_Supermixer?
      // So that IMS_HtmlFilter has access to all HTML (including all headers generated by jscal / dhtml etc.)
      // except for this "showcreatedwith" header.
      if ($myconfig[$sitecollection_id]["partner"]) {
        IMS_AddHtmlHeader('raw', array(), "<!--
  Site created by ".$myconfig[$sitecollection_id]["partner"]." with OpenIMS (www.openims.com).
  OpenIMS is an Enterprise Content Management platform produced by OpenSesame ICT (www.osict.com) an Open Source specialist from the Netherlands, Europe.
-->
");
      } else {
        IMS_AddHtmlHeader('raw', array(), "<!--
  Site created with OpenIMS (www.openims.com).
  OpenIMS is an Enterprise Content Management platform produced by OpenSesame ICT (www.osict.com) an Open Source specialist from the Netherlands, Europe.
-->
");
      }
      $output = IMS_MergeHtmlHeaders($output);
    }
  }

  global $autosize;
  if ($autosize) {
    $page = "<html>";
    $page .= "<head>";
    $page .= "<title>".$fullspec["title"]."</title>";
    $page .= "<body bgcolor=#f0f2ff>";
    $page .= "<table cellspacing=0 cellpadding=15 border=0 id=\"MeasureMe\"><tr><td>";
    $page .= $output;
    $page .= "</td></tr></table>";
    $page .= DHTML_EmbedJavaScript (DHTML_PerfectSize ());
    $page .= "</body>";
    $page .= "</html>";
    $output = $page;
  }

  global $myconfig;
  if ($myconfig["serverhasniceurl"] == "yes") $output = NICEURL_Transform ($sitecollection_id, $output);

  global $myconfig;
  $sgn = IMS_SuperGroupName();
  if (($myconfig[$sgn]["ml"]["metadatalevel"] && $myconfig[$sgn]["ml"]["metadatafiltering"] != "no") ||
       $myconfig[$sgn]["ml"]["metadatafiltering"] == "yes") {
    $output = FORMS_ML_Filter($output);
  }

  return $output;
}

function IMS_PublishObject ($sitecollection_id, $site_id, $object_id)
{
  N_Debug ("IMS_PublishObject ($sitecollection_id, $site_id, $object_id)");
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  $object["parameters"]["published"] = $object["parameters"]["preview"];
  if ($object["objecttype"] == "document" && $object_id && FILES_FileType($sitecollection_id, $object_id, "preview") != "imsctn.txt") {
    // The published directory should only contain the document in its current format; it should not retain previously published versions in different formats.
    // Since old formats stay in preview forever (due to transfer agent cache), do not copy entire directory, just the file itself.
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure("html::".$sitecollection_id."/objects/".$object_id."/");
    N_CopyFile ("html::".$sitecollection_id."/objects/".$object_id."/".FILES_TrueFileName($sitecollection_id, $object_id),
                "html::".$sitecollection_id."/preview/objects/".$object_id."/".FILES_TrueFileName($sitecollection_id, $object_id));
  } else { // cms pages and containers
    N_CopyDir ("html::".$sitecollection_id."/objects/".$object_id, "html::".$sitecollection_id."/preview/objects/".$object_id);
  }
  if ($site_id) { // if "" the component is published but no .php file is generated (e.g. documents)
    $input ["object_id"] = $object_id;
    $output = IMS_SuperMixer ($sitecollection_id, $site_id, $object_id, "generate_static_page");
    N_WriteFile ("html::".$site_id."/".$object_id.".php", $output);
    $input ["sitecollection_id"] = $sitecollection_id;
    $input ["site_id"] = $site_id;
    $input ["object_id"] = $object_id;
    N_SuperQuickScedule (N_CurrentServer(), 'SEARCH_AddPageToSiteIndex ($input ["sitecollection_id"], $input ["site_id"], $input ["object_id"]);', $input);
  } else if ($object["objecttype"]=="document") {
    global $myconfig;

    $object["pub"] = array();
    $object["pub"]["shorttitle"] = $object["shorttitle"];
    $object["pub"]["longtitle"] = $object["longtitle"];
    $object["pub"]["allocto"] = $object["allocto"];
    $object["pub"]["filename"] = $object["filename"];
    $object["pub"]["executable"] = $object["executable"];
    foreach ($object as $okey => $oval)
      if (substr($okey, 0, 5) == "meta_")
        $object["pub"][$okey] = $oval;

    if (($object["meta_autohtml"]."" != "") && ($myconfig[$sitecollection_id]["autohtml"]["createstubpage"] == "yes")) {
      uuse ("webgen");
      WEBGEN_CreateStubPage($sitecollection_id, $object_id);
    }
    $input ["sitecollection_id"] = $sitecollection_id;
    $input ["object_id"] = $object_id;
    N_SuperQuickScedule (N_CurrentServer(), 'SEARCH_AddDocumentToDMSIndex ($input ["sitecollection_id"], $input ["object_id"]);', $input);
    MAIL_SignalObject ($sitecollection_id, $object_id, IMS_Object2Latestauthor ($sitecollection_id, $object_id), IMS_Object2Domain ($sitecollection_id, $object_id), "p");
  }
  $object["preview"] = "no";
  $object["published"] = "yes";
  // locate last edit and mark as published
  if (is_array ($object["history"])) foreach ($object["history"] as $id => $specs) {
    if ($specs["type"]=="" || $specs["type"]=="edit" || $specs["type"]=="new") {
      $last = $id;
    }
  }
  if ($last) {
    $object["history"][$last]["published"] = "yes";
  }
  if ($site_id) {
    MB_Flush();
    HT_HandlePublish ($sitecollection_id, $site_id, $object_id); // update high traffic cache
  }
}

function IMS_RecoverObject ($sitecollection_id, $site_id, $object_id)
{
  N_Debug ("IMS_RecoverObject ($sitecollection_id, $site_id, $object_id)");
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  $object["parameters"]["preview"] = $object["parameters"]["published"];

  global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
  $time = time();
  $guid = N_GUID();
  $object["history"][$guid]["type"] = "revoked";
  $object["history"][$guid]["revoketype"] = "restorelastpublished";
  $object["history"][$guid]["when"] = time();
  $object["history"][$guid]["author"] = SHIELD_CurrentUser($sitecollection_id);
  $object["history"][$guid]["server"] = N_CurrentServer ();
  $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
  $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
  $object["history"][$guid]["server_addr"] = $SERVER_ADDR;

  // LF20100512: Use the function also used to "restore a history version" so that:
  // - the workflow stadium will be changed to "Modified" (or whatever has been configured in the workflow)
  // - the history knows which version was restored
  foreach ($object["history"] as $version_id => $specs) {
    if ($specs["published"] == "yes") $publishedversion_id = $version_id;
  }
  IMS_RestoreObject ($sitecollection_id, $object_id, $publishedversion_id);

}

function IMS_CopyTemplate ($sitecollection_id, $template_id)
{
  $new_template_id = N_GUID();
  $template = &MB_Ref ("ims_".$sitecollection_id."_templates", $template_id);
  $newtemplate = &IMS_RegisterTemplate ($sitecollection_id, $new_template_id);
  $newtemplate["name"] = ML("Kopie van %1", "Copy of %1", $template["name"]);
  N_CopyDir ("html::".$sitecollection_id."/preview/templates/".$new_template_id, "html::".$sitecollection_id."/preview/templates/".$template_id);
  N_CopyDir ("html::".$sitecollection_id."/templates/".$new_template_id, "html::".$sitecollection_id."/templates/".$template_id);
}

function IMS_DeleteTemplate ($sitecollection_id, $template_id)
{
  MB_Delete ("ims_".$sitecollection_id."_templates", $template_id);
}

function IMS_Delete ($sitecollection_id, $site_id, $object_id)
{
  uuse ("link");
  uuse ("multi");

  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);

  if ($object["objecttype"]=="shortcut") {
    N_Log("deleted objects", "shortcut to ".$object["source"]." - by: " .SHIELD_CurrentUser ($sitecollection_id) . " - sitecollection: " . $sitecollection_id);
    $source_id = $object["source"];
    MB_Delete ("ims_".$sitecollection_id."_objects", $object_id);

    // update document in search index, so that shortcut metadata will be removed   
    uuse("search");   
    $source = MB_Load("ims_".$sitecollection_id."_objects", $source_id);   
    if ($source["published"] == "yes" || $source["preview"] == "yes") SEARCH_AddPreviewDocumentToDMSIndex($sitecollection_id, $source_id);   
    if ($source["published"] == "yes") SEARCH_AddDocumentToDMSIndex($sitecollection_id, $source_id);

    return;
  }
  if ($object["objecttype"]=="document") { // remove all shortcuts to this document
    $list = MB_TurboSelectQuery ("ims_".$sitecollection_id."_objects", '$record["source"]', $object_id);
    foreach ($list as $key) IMS_Delete ($sitecollection_id, "", $key);
  }

  LINK_DeleteAll ($sitecollection_id, $object_id); // delete all links with other documents

  MULTI_Unselect ($object_id); // De-select this document

  $object["preview"] = "no";
  $object["published"] = "no";

  global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
  $time = time();
  $guid = N_GUID();
  $object["history"][$guid]["when"] = $time;
  $object["history"][$guid]["author"] = SHIELD_CurrentUser ($sitecollection_id);
  $object["history"][$guid]["option"] = "delete";
  $object["history"][$guid]["server"] = N_CurrentServer ();
  $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
  $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
  $object["history"][$guid]["server_addr"] = $SERVER_ADDR;

  if ($site_id) SEARCH_RemovePageFromSiteIndex ($sitecollection_id, $site_id, $object_id);
  SEARCH_RemoveDocumentFromDMSIndex ($sitecollection_id, $object_id);


  // logging
  $name = "";
  if ($object["objecttype"] == "document") {
     $name = $object["shorttitle"];
  }
  if ($object["objecttype"] == "webpage") {
     $name = "" . $object["parameters"]["preview"]["shorttitle"];
     if ($name == "") $name = $object["parameters"]["published"]["shorttitle"];
  }
  $short = "id: " . $object_id . " - " .
           "type: " . $object["objecttype"] . " - " .
           "name: " . $name . " - " .
           "by: " . $object["history"][$guid]["author"] . " - " .
           "sitecollection: " . $sitecollection_id;
  N_Log("deleted objects", $short, "", "");

  if ($site_id) HT_HandlePublish ($sitecollection_id, $site_id, $object_id); // update high traffic cache
}

function IMS_PublishTemplate ($sitecollection_id, $template_id)
{
  N_Debug ("IMS_PublishTemplate ($sitecollection_id, $template_id)");
  N_CopyDir ("html::".$sitecollection_id."/templates/".$template_id, "html::".$sitecollection_id."/preview/templates/".$template_id);
/*
  $list = MB_Query ("ims_".$sitecollection_id."_objects");
  if (is_array ($list)) reset($list);
  if (is_array ($list)) while (list($object_id)=each($list)) {
    $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
    if ($object["template"]==$template_id) {
      $site_id = IMS_Object2Site ($sitecollection_id, $object_id);
      if ($site_id) {
        $output = IMS_SuperMixer ($sitecollection_id, $site_id, $object_id, "generate_static_page");
        N_WriteFile ("html::".$site_id."/".$object_id.".php", $output);
      }
    }
  }
*/
  $template = &MB_Ref ("ims_".$sitecollection_id."_templates", $template_id);
  $template["published"] = "yes";
  $template["preview"] = "no";
}

function IMS_Object2Site ($sitecollection_id, $object_id)
{
  N_Debug ("IMS_Object2Site ($sitecollection_id, $object_id)");
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  if ($object["mysite"]) return $object["mysite"];
  while ($object["parent"] && ++$counter<100) {
    $list = MB_TurboSelectQuery ("ims_sites", '$record["homepage"]', $object_id);
    if (is_array($list)) {
      reset ($list);
      list ($key) = each ($list);
      if ($key) return $key;
    }
    $object_id = $object["parent"];
    $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  }
  $list = MB_TurboSelectQuery ("ims_sites", '$record["homepage"].$record["sitecollection"]', $object_id.$sitecollection_id);
  if (is_array($list)) {
    reset ($list);
    list ($key) = each ($list);
    if ($key) {
      $object["mysite"] = $key; // buggy ???
      return $key;
    }
  } else {
    return "";
  }
}

function IMS_RecoverTemplate ($sitecollection_id, $template_id)
{
  N_Debug ("IMS_RecoverTemplate ($sitecollection_id, $template_id)");
  N_CopyDir ("html::".$sitecollection_id."/preview/templates/".$template_id, "html::".$sitecollection_id."/templates/".$template_id);
  $template = &MB_Ref ("ims_".$sitecollection_id."_templates", $template_id);
  $template["preview"] = "no";
}

function IMS_UseInlineHtmlEditor($object_id_or_file, $ta_editor = "") { // returns true if an inline html editor (devedit / tinymce / tinymce inplace) should be used
  /* Eg.:
   * IMS_UseInlineHtmlEditor("4b6c800f0b5e62da252d19ff70730559");
   * IMS_UseInlineHtmlEditor("confirm.html", "winword.exe");
   *
   * // For CMS pages, this function checks the user's preference.
   * In shield_$sgn_users, the following values for "inlineeditor" are possible:
   * "" => the user never configured anything
   * "no" => use transfer agent
   * "yes" => use tinymce or devedit
   * "inplace" => use tinymce inplace
   *
   * // For CMS templates, the settings below will result in this function being called with $ta_editor == "inline"
   * $myconfig[$sgn]["edit_template_html"] = "inline"; // The template that is used "around" the CMS pages using the template
   * $myconfig[$sgn]["edit_default_template_doc_html"] = "inline"; // What is copied when you create a new page (default content for new page).
   * These settings will result in this function being called with $ta_editor == "inline".
   *
   * Note: I am ignoring the setting to disable the transfer agent (and replace it with an upload dialog), because:
   * - the upload dialog sometimes works (if the CMS page / template happens to be a single file without subfolders)
   * - we might decide to add support for subfolders to the upload dialog in the future
   */

  global $myconfig;
  $sgn = IMS_SuperGroupName();
  $user_id = SHIELD_CurrentUser($sgn);
  $user = MB_Ref("shield_{$sgn}_users", $user_id);

  // Negative substr's are for multilingual CMS content, which creates pages named en-page.html, nl-page.html etc.
  if ($ta_editor) {
    $file = $object_id_or_file;
    if ($ta_editor == "inline" && (substr($file, -13) == "template.html" || substr($file, -9) == "page.html")) {
       return true; // regardless of user settings
    } else {
      // Check for some reasons to not use inline editor (e.g. because the document is not a webpage)
      if (!((substr($file, -9)=="page.html") || (substr($file,-12)=="confirm.html"))) return false;  // if it's a form. JH. // LF: this is done to prevent problems with *inplace* forms
      if ($ta_editor != "winword.exe") return false;
    }
  } else {
    $object_id = $object_id_or_file;
    $obj = MB_Ref("ims_{$sgn}_objects", $object_id);
    if (!($obj["editor"] == "Microsoft Word" || $obj["editor"] == "Form") || ($obj["editor"] == "BPMSForm")) return false; // LF: forms are allowed to to use the *inline* editor
  }

  if ($myconfig[$sgn]["useinlinehtmleditoronly"] == "yes") return true;
  if ($myconfig[$sgn]["useinlinehtmleditorbydefault"] == "yes" && $user["inlineeditor"] != "no") return true;
  if ($myconfig[$sgn]["usetinymceinplacebydefault"] == "yes" && $user["inlineeditor"] == "") return true;
  if ($myconfig[$sgn]["allowinlinehtmleditor"] == "yes" && ($user["inlineeditor"] == "yes" || $user["inlineeditor"] == "inplace")) return true;
  return false;
}
 /////////////////////////////
function IMS_TextareaEdit($file, $directory, $editmode=false)
{
  $directory = str_replace("\\", "/", $directory);
  $path = N_Cleanpath("html::".$directory.$file);

  // bestaat niet
  if (!is_file($path)) return false;

  // extensie van een niet-tekst bestand
  if (preg_match("/(jpe?g|gif|png|bmp|tiff?|pcx|cr2|nef|avi|aac|wav|mp3|mp4|mov|flv|asf|swf|zip|gz|tar|jar|dmg|bz2|'
            'docx?|xlsx?|pptx?|odt|odf|pdf|rar|bin|exe|psd|fla|rpm|deb|sisx?)$/i", $file))
    return false;

  // te groot
  $size = @filesize($path);
  if ($size >= 1048576) return false;

  $content = file_get_contents($path);

  $linecount = count(explode("\n", $content));
  if (!$linecount || ($size/$linecount) > 160) {
    N_Log("textedit", ML("[$path] bevat te lange regels ($size bytes, $linecount regel(s)): geen tekstbestand",
                         "[$path] has too long lines($size bytes, $linecount line(s)): no text file"));
    return false;
  }

  $thespecs["input"] = array(
  "directory" => $directory,
  "readonly" => !$editmode,
  "file" => $file,
  "path" => $path,
  "re" => '/^.+?\/((objects)|(templates))\/([0-9a-f]{32})\/([^\/]+)$/',
  );
  $thespecs["title"] = "&lt; ".$thespecs["input"]["directory"]."$file &gt;";

  if (preg_match_all($thespecs["input"]["re"], $directory . $file, $matches, PREG_SET_ORDER)) {
    $thespecs["input"]["objectid"] = $matches[0][4];
    $obj = MB_Ref("ims_${sgn}_" . $matches[0][1], $thespecs["input"]["objectid"]); // get object
  }

  $thespecs["load"] = '
  $content = file_get_contents($input["path"]);
  $readonly = $input["readonly"];

//  UUSE ("flex");
';
  if ($editmode) {
    $thespecs["save"] = '
    $directory = $input["directory"];
    $file = $input["file"];
    $re = $input["re"];
    $path = $input["path"];
    $input["content"] = $content;

    N_Writefile($path, $content);
// variable in directedit.php which processes at the end of the page
    $postprocess .= "
<script type=\"text/javascript\">
if (window.opener) {
  var url;
  url = window.opener.location.href+\"#\";
  pos = url.indexOf(\"#\",url);
  url = url.substring(0,pos);
  window.opener.location.href = url;
}
</script>
";
    // Alleen objecten en sjablonen
//        if (preg_match($input["re"], $path)) {
      IMS_Signal($directory.$file, SHIELD_CurrentUser(IMS_SuperGroupName()), getenv("HTTP_HOST"));
//        }
    if ($input["file"] == "myconfig.php") {
      N_Log ("myconfig", "change by ".SHIELD_CurrentUser(IMS_SuperGroupName()), "IMS_GenerateNoTransferUrl", N_ReadFile ("html::myconfig.php"));
      N_Writefile ("html::tmp/myconfig/".date ("Ymd_His").".txt", N_ReadFile ("html::myconfig.php"));
    }
';
  }
  $sspecs = SHIELD_Encode ($thespecs);
  $url = "/openims/directedit.php?specs=$sspecs";
  $funchexstring = N_GUID();
  $script = "javascript:function function_$funchexstring(){var URL='".$url."';window.open(URL,'".N_GUID()."');}function_$funchexstring();";

  return $script;
}
///////

function IMS_GenerateNoTransferUrl ($directory, $file, $includesubs=false, $editmode=true)
{
  // Create upload/download popup url for customers not using the transfer agent

  if ($includesubs) {
    $tree = N_QuickTree("html::".$directory);
    if (count($tree) > 1) {
      $url = FORMS_URL (array ("formtemplate"=>'
              <table>
              <tr><td><font face="arial" size=2><b>'.ML("Documenten met subfolders worden op dit platform niet ondersteund.", "Documents with subfolders are not supported on this platform").'</b></font></td></tr>
              <tr><td colspan=2>&nbsp;</td></tr>
              <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
              </table>
            '));
      return $url;
    }
  }
  // Use direct textarea text editor for ascii text files when 'notrans' cookie is set
  if ($_COOKIE['ims_notrans'] == 'yes') {
    $urltext = IMS_TextareaEdit($file, $directory, $editmode);
    if ($urltext) return $urltext;
  }

  // We use ufc (instead of direct download through apache) so that we can set http headers / mime-types.
  // E.g. a direct link to a txt or html file will show the content inside the tiny download popup;
  // with ufc we can force the "open with ... or save to disc" browser dialog.
  // "rawfile" is a new UFC type that forces this dialog for all file types, and allows authenticated
  // accees to all files (even myconfig.php).

  $url = "/ufc/rawfile/" . SHIELD_Encode(N_CleanPath("html::" . $directory . $file)) . "/" . $file;

  if ($editmode) {

    $form = array();
    $form["input"]["file"] = $file;
    $form["input"]["directory"] = $directory;
    $form["input"]["editmode"] = $editmode;
    $form["metaspec"]["fields"]["file"]["type"] = "bigfile";
    $form["metaspec"]["fields"]["file"]["title"] = ML("Bestand", "File");
    $form["gotook"] = "closeme&refreshparent";
    $form["formtemplate"] = '
      <style>
      body, div, p, th, td, li, dd {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 13px;
      }
      </style>
      <table cellpadding="10" cellspacing="0">
        <tr><td colspan="2"  style="border-bottom: 1px solid black;">'.ML("Bestand", "File").': <b>'.N_HtmlEntities($file).'</b></td></tr>
        <tr><td valign="top"><a href="'.$url.'"><img src="/ufc/rapid/openims/download.gif" border="0" style="padding-right: 5px" />'.ML("Download", "Download").'</a></td>
            <td valign="top" style="border-left: 1px solid black;"><img src="/ufc/rapid/openims/upload_small.gif" border="0" style="padding-right: 5px" />'.ML("Upload nieuwe versie","Upload new version").'<br/>[[[file]]]
            <br/><br/>
            <center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[Cancel]]]</center>
        </td></tr>
      </table>
    ';
    $form["postcode"] = '
      if (!N_FileSize ($files["file"]["tmpfilename"])) FORMS_ShowError (ML("Foutmelding","Error"), ML("Er is een leeg bestand of de upload is mislukt","The file is empty or the upload has failed"), true);
      $filename = $files["file"]["name"];
      $origfilename = $input["file"];
      $ext = N_KeepAfter ($filename, ".", true);
      $origext = N_KeepAfter ($origfilename, ".", true);
      if ($ext != $origext) {
        FORMS_ShowError (ML("Foutmelding","Error"), ML("U moet hier een","You have to upload")." \"".$origext."\" ".ML("bestand uploaden","files").".", true);
      }

      if ($filename == $origfilename) {
        IMS_GenerateNoTransferURL_Process($input, $files["file"]["tmpfilename"]);
      } else {
        N_Redirect(IMS_GenerateNoTransferURL_ConfirmationUrl($input, $files["file"]["name"], $files["file"]["tmpfilename"]));
      }

    ';
    return FORMS_URL($form);
  } else { // !$editmode
    return $url;
  }
}

function IMS_GenerateNoTransferURL_Process($input, $tmpfilename) {
  N_Rename($tmpfilename, N_CleanPath("html::".$input["directory"].$input["file"]));
  IMS_Signal($input["directory"].$input["file"], SHIELD_CurrentUser(IMS_SuperGroupName()), getenv("HTTP_HOST"));
  if ($input["file"] == "myconfig.php") {
    N_Log ("myconfig", "change by ".SHIELD_CurrentUser(IMS_SuperGroupName()), "IMS_GenerateNoTransferUrl", N_ReadFile ("html::myconfig.php"));
    N_Writefile ("html::tmp/myconfig/".date ("Ymd_His").".txt", N_ReadFile ("html::myconfig.php"));
  }
}

function IMS_GenerateNoTransferURL_ConfirmationUrl($input, $filename, $tmpfilename) {
  $form = array();
  $form["input"] = $input;
  $form["gotook"] = "closeme&refreshparent";
  uuse("tmp");
  $dir = TMP_Directory();
  N_Rename($tmpfilename, $dir . "/" . $input["file"]); // $tmpfilename needs to be renamed, because otherwise PHP will destroy it at the end of the current request
  $form["input"]["tmpfilename"] = $dir . "/" . $input["file"];
  // Move $tmpfilename, will be destroyed at end of this request
  $form["formtemplate"] = '
    <style>
      body, div, p, th, td, li, dd {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 13px;
    }
    </style>
    <p><b>'.ML("De bestandsnamen zijn niet hetzelfde","The file names are different").'</b></p>
    <p>'.ML("Wilt u het bestand %1 overschrijven met %2", "Do you wish to overwrite %1 with %2", "<b>".$input["file"]."</b>", "<b>".$filename."</b>").'?</p>
    <p><br/><center>[[[OK:'.ML("Overschrijven", "Overwrite").']]]&nbsp;&nbsp;&nbsp;[[[Cancel]]]</center></p>
  ';
  $form["postcode"] = '
    IMS_GenerateNoTransferURL_Process($input, $input["tmpfilename"]);
  ';
  return FORMS_URL($form, true);
}

function IMS_GenerateTransferURL ($directory, $file, $editor, $includesubs=false, $editmode=true)
{
  global $myconfig;

  // DVG: Achterhalen wat de extensie is, deze zit in $filetypeis[0]
  $filetypeis = array_reverse(explode(".",$file));
  // DVG: $myconfig["collectie_sites"]["definehowtoopenfile"] bevat de array met alles wat bepaald wordt
  // DVG: Kijken of deze extensie voorkomt in de array van de instellingen
  if (is_array($myconfig[IMS_SuperGroupName()]["definehowtoopenfile"])){
    if (array_key_exists($filetypeis[0], $myconfig[IMS_SuperGroupName()]["definehowtoopenfile"])) {
      // DVG: Wijzigen van de editor naar wat in de array staat
      $editor = $myconfig[IMS_SuperGroupName()]["definehowtoopenfile"][$filetypeis[0]];
    }
  }

  IMS_CheckForSpecialDocumentDataUpdate ($directory.$file);

  $user = MB_Ref ("shield_".IMS_SuperGroupName()."_users", SHIELD_CurrentUser());
  if (IMS_UseInlineHtmlEditor($file, $editor)) {
    if ($myconfig[IMS_Supergroupname()]["usetinymce"] == "yes") {
       uuse ("tinymce");
       if ($editmode) {
         return TINYMCE_EditURL ($directory, $file);
       } else {
         return TINYMCE_ViewURL ($directory, $file);
       }
    }
    uuse ("devedit");
    if ($editmode) {
      return DEVEDIT_EditURL ($directory, $file);
    } else {
      return DEVEDIT_ViewURL ($directory, $file);
    }
  } else {
// 20110324 KvD no transfer agent for textfiles
    if ($_COOKIE['ims_notrans'] == "yes")
      $url = IMS_Textareaedit($file, $directory, $editmode);

    if ($url) return $url;
///
    global $myconfig;
    if (!N_UseTransferAgent()) return IMS_GenerateNoTransferUrl ($directory, $file, $includesubs, $editmode);

    
  }
}

function IMS_GenerateEditURL ($directory, $file, $editor, $includesubs=false)
{
  $directory = "\\".str_replace ("/", "\\", $directory)."\\";
  $directory = str_replace ("\\\\", "\\", $directory);
  $directory = str_replace ("\\\\", "\\", $directory);
  return IMS_GenerateTransferURL ($directory, $file, $editor, $includesubs, true);
}

function IMS_GenerateViewURL ($directory, $file, $editor, $includesubs=false)
{
  return IMS_GenerateTransferURL ($directory, $file, $editor, $includesubs, false);
}

function IMS_GenerateAdvancedTransferURL ($commands)
{
  global $myconfig;
  if (!N_UseTransferAgent()) N_Die("IMS_GenerateAdvancedTransferURL called, but transfer agent disabled"); // Shouldnt happen

  
}

function IMS_SetHomepage ($site_id, $object_id)
{
  $site = &MB_Ref ("ims_sites", $site_id);
  $site["homepage"] = $object_id;
}

function IMS_Preview ()
{
  global $thecase, $new, $IMS_Preview_onetime;
  if ($thecase || $new) return false;
  global $HTTP_COOKIE_VARS, $activate_preview, $myconfig;

  if ($myconfig[IMS_SuperGroupName()]["cookieloginsettings"]["loginpageurl"]
      && (strtolower($_SERVER['SCRIPT_NAME']) == strtolower($myconfig[IMS_SuperGroupName()]["cookieloginsettings"]["loginpageurl"]) || strtolower($_SERVER["REDIRECT_URL"]) == strtolower($myconfig[IMS_SuperGroupName()]["cookieloginsettings"]["loginpageurl"]))
      && SHIELD_CurrentUser(IMS_SuperGroupName()) == "unknown") {
    // If the login page is a custom CMS page and IMS_Preview would return true, it would trigger SHIELD_NeedsGlobalRight(preview), which would
    // trigger SHIELD_ForceLogon, which would redirect to the login page (again), which would tell the user to restart the browser. That works,
    // but it is rather inconvenient and incomprehensible.
    // For logged in users, we do allow IMS_Preview to return true, because otherwise nobody would be able to edit the login page.
    return false;
  }

  if ($HTTP_COOKIE_VARS["ims_preview"]=="yes") return true;
  if ($activate_preview=="yes") {
    if (!$IMS_Preview_onetime) {
      N_SetCookie ("ims_preview", "yes", 0, "/", "", (N_CurrentProtocol() == "https://"), true);
      $IMS_Preview_onetime = true; // avoid completely stupid Internet Explorer limitation
    }
    return true;
  }
  return false;
}

function ims_coolbar_has_button( $button )
{
  global $myconfig;
  $show = true;
  $supergroupname = IMS_SuperGroupName();
  if($myconfig[$supergroupname]["hidecoolbarbuttons"])
  {
    if( in_array( $button , $myconfig[$supergroupname]["hidecoolbarbuttons"]) === true )
      $show = false;
  }
  return $show;
}

function IMS_CoolBar ()
{
  uuse ("skins");
  global $ims_coolbar_called;
  $ims_coolbar_called = true;
  if (IMS_Preview()) {
    $siteinfo = IMS_DetermineSite ();
    SHIELD_NeedsGlobalRight ($siteinfo["sitecollection"], "preview");
  }
  if (IMS_Preview () && !isset( $_GET["treeview_preview_no_coolbar_please"] ) ) {
    global $myconfig;

    if ( isset( $myconfig[ $siteinfo["sitecollection"] ]["hascmsright"] ) )
      SHIELD_NeedsGlobalRight( $siteinfo["sitecollection"], "cmsright" );

    $sitecollection_id = $siteinfo["sitecollection"];
    $supergroupname = $siteinfo["sitecollection"];
    $site_id = $siteinfo["site"];
    $object_id = $siteinfo["object"];

    $is_coolbar_v2 = ($myconfig[$sitecollection_id]["coolbar"]["v2"] == "yes" xor isset( $_GET["invert_coolbar"] ) );

    $object = &MB_Ref ("ims_".$siteinfo["sitecollection"]."_objects", $siteinfo["object"]);

    // determine (and fix if needed) workflow information)
    if (!$object["workflow"]) $object["workflow"] = "edit-publish";
    $workflow = &SHIELD_AccessWorkflow ($siteinfo["sitecollection"], $object["workflow"]);
    if (!$object["stage"]) $object["stage"] = 1; // first stage

    $goto = "http://".getenv("HTTP_HOST")."/$site_id/$object_id.php";
    $goto = str_replace ("activate_preview=yes", "q", $goto);

    $coolbar .= SKIN_CSS();

    // OpenIMS logo // JG added id "coolbar_table" to identify coolbar table with css to provent styling problems //
    $coolbar .= '<table id="coolbar_table" bgcolor="'. SKIN_Top_BgColor() .'" background="'. SKIN_Top_Background() .'" border="0" cellspacing="3" cellpadding="0" width="100%"><tr><td><table border="0" cellspacing="0" cellpadding="3"><tr><td class="ims_td">';



    if (N_OpenIMSCE()) {
      $coolbar .= '<img class="ims_image" src="/ufc/rapid/openims/openimsce50.jpg">&nbsp;';
    } else {
      $coolbar .= '<img class="ims_image" src="/ufc/rapid/openims/openims50.gif">&nbsp;';
    }
    $coolbar .= '</center></td><td class="ims_td"><center>';
    $coolbar .= '<span class="ims_text1">'.ML("Concept", "Concept").'</span><br>';
    $coolbar .= '<span class="ims_text2">'.$workflow[$object["stage"]]["name"].'</span><br>';
    $coolbar .= '<span class="ims_text3">'.SHIELD_CurrentUserName($siteinfo["sitecollection"]).'</span>';

    // Separator
    $coolbar .= '</center></td><td class="ims_td"><center>';
    $coolbar .= '<img class="ims_image" src="/ufc/rapid/openims/separator.gif">';



    global $HTTP_COOKIE_VARS;
    if ($HTTP_COOKIE_VARS["ims_moving"]=="yes") {

      $coolbar .= '</center></td><td class="ims_td"><center>';
      $coolbar .= '<font color="#000000" size="5" face="arial,helvetica"><b>'. ML("Verplaatsen pagina","Move page") .'</b></font><br>';
      $coolbar .= '<font color="#000000" size="2" face="arial,helvetica">&nbsp</font>';


      // Separator
      $coolbar .= '</center></td><td class="ims_td"><center>';
      $coolbar .= '<img class="ims_image" src="/ufc/rapid/openims/separator.gif">';

      $ok = "yes";
      $tmp = $siteinfo["object"];
      while ($tmp) {
        if ($tmp==$HTTP_COOKIE_VARS["ims_object_id"]) $ok = "no";
        $tmp = MB_Fetch ("ims_".$siteinfo["sitecollection"]."_objects", $tmp, "parent");
      }

      $goto3 = "http://".getenv("HTTP_HOST")."/$site_id/".$HTTP_COOKIE_VARS["ims_object_id"].".php";
      if ($ok=="yes") {

        $_drop_href = '/openims/action.php?goto='.urlencode($goto3).'&command=moveend&sitecollection_id='.$siteinfo["sitecollection"].'&site_id='.$siteinfo["site"].'&object_id='.$siteinfo["object"].'&moving_object_id='.$HTTP_COOKIE_VARS["ims_object_id"];
        $_drop_title = ML("De pagina moet hier onder komen","Move the page below this page");
        $_drop_txt = ML("Hier","Here");


        // Drop
        $coolbar .= '</center></td><td class="ims_td"><center>';
        $coolbar .= '<a class="ims_navigation" title="'. ML("De pagina moet hier onder komen","Move the page below this page") .'" href="/openims/action.php?goto='.urlencode($goto3).'&command=moveend&sitecollection_id='.$siteinfo["sitecollection"].'&site_id='.$siteinfo["site"].'&object_id='.$siteinfo["object"].'&moving_object_id='.$HTTP_COOKIE_VARS["ims_object_id"].'"><img class="ims_image" border="0" src="/ufc/rapid/openims/domove.gif"><br>' . ML("Hier","Here") . '</a>';
      }

      // Stop
      $_stop_title = ML("Bij nader inzien niet verplaatsen","Do not move this page");
      $_stop_href = '/openims/action.php?goto='.urlencode($goto3).'&command=movequit&sitecollection_id='.$siteinfo["sitecollection"].'&site_id='.$siteinfo["site"].'&object_id='.$siteinfo["object"].'&moving_object_id='.$HTTP_COOKIE_VARS["ims_object_id"];
      $_stop_txt = ML("Stop","Cancel");

      $coolbar .= '</center></td><td class="ims_td"><center>';
      $coolbar .= '<a class="ims_navigation" title="' . ML("Bij nader inzien niet verplaatsen","Do not move this page") .'" href="/openims/action.php?goto='.urlencode($goto3).'&command=movequit&sitecollection_id='.$siteinfo["sitecollection"].'&site_id='.$siteinfo["site"].'&object_id='.$siteinfo["object"].'&moving_object_id='.$HTTP_COOKIE_VARS["ims_object_id"].'"><img class="ims_image" border="0" src="/ufc/rapid/openims/stopmove.gif"><br>'. ML("Stop","Cancel") . '</a>';
    } else {

    $_site_title = ML("Laat de gepubliceerde site zien (zonder OpenIMS balk)","Show the published site (without OpenIMS bar)");
    $_site_txt = ML("Site", "Site");

    // Site button
    $coolbar .= '</center></td><td class="ims_td"><center>';
    $coolbar .= '<a class="ims_navigation" title="'.$_site_title.'" href="/openims/nopreview.php"><img class="ims_image" border="0" src="/ufc/rapid/openims/showsite.gif"><br>'.$_site_txt.'</a>';

    $_cms_title = ML("Ga naar de OpenIMS Content Management Server","Go to OpenIMS Content Management Server");
    $_cms_txt = ML("CMS", "CMS");
    $_cms_href = '/openims/openims.php?mode=cms';

    $_coolbar .= '<li class="cms"><a href="'.$_cms_href.'" title="'.$_cms_title.'"><span>'.$_cms_txt.'</span></a></li>';

    // Portal button
    $coolbar .= '</center></td><td class="ims_td"><center>';
    $coolbar .= '<a class="ims_navigation" title="'.$_cms_title.'" href="'.$_cms_href.'"><img class="ims_image" border="0" src="/ufc/rapid/openims/portal.gif"><br>'.$_cms_txt.'</a>';

    $_refresh_txt = $_refersh_title = ML("Ververs", "Refresh");
    $_refresh_href = 'javascript:window.location=\''.htmlspecialchars(N_MyFullURL()).'\'';


    // Refresh button
    $coolbar .= '</center></td><td class="ims_td"><center>';
    $coolbar .= '<a class="ims_navigation" title="'.$_refresh_title.'" href="'.$_refresh_href.'">';
    $coolbar .= '<img class="ims_image" border="0" src="/ufc/rapid/openims/refresh.gif"><br>';
    $coolbar .= $_refresh_txt.'</a>';

    // Separator
    $coolbar .= '</center></td><td class="ims_td"><center>';
    $coolbar .= '<img class="ims_image" src="/ufc/rapid/openims/separator.gif">';

    // Edit content button
    $show = ims_coolbar_has_button( 'edit' );
    if($show) {
    $dataerror = FORMS_BlindValidation ($supergroupname, $object_id);
    if (SHIELD_HasObjectRight ($supergroupname, $object_id, "edit") ) {
      uuse("tinymce");
      $inplace = TINYMCE_isinplace($object_id);
      if (!$inplace ) {
        $coolbar .= '</center></td><td class="ims_td"><center>';
        if ($object["editor"]=="Microsoft Word" ) {
          global $myconfig;
          $editor = "winword.exe";
          if ($myconfig[$siteinfo["sitecollection"]]["ml"]["sitelanguages"][$site_id]) { // multiple edit buttons for multilingual content
            $otherlangs = $myconfig[$siteinfo["sitecollection"]]["ml"]["sitelanguages"][$site_id];

            $defaultlang = array_shift($otherlangs);
            if (N_FileExists("html::openims/edit-$defaultlang.gif")) {
                $icon = "/ufc/rapid/openims/edit-$defaultlang.gif";
                $icon_coolbar_v2 =  ML_LanguageIcon($defaultlang); //'/ufc/rapid/openims/f0-'.$defaultlang.'.gif';
              $extratext = "";
            } else {
                $icon = "/ufc/rapid/openims/edit.gif";
                $icon_coolbar_v2 = false;
              $extratext = " (".strtoupper($otherlang).")";
            }
          } else {
            $icon = "/ufc/rapid/openims/edit.gif";
          }

          $_edit_txt = ML("Wijzig", "Edit").$extratext;
          $_edit_title = ML("Wijzig de inhoud (content) van deze pagina","Change the content of this page");
          $_edit_href = IMS_GenerateTransferURL(
                     "\\".$siteinfo["sitecollection"]."\\preview\\objects\\".$siteinfo["object"]."\\",
                     "page.html",
                     $editor, true
               );


          $coolbar .= '<a class="ims_navigation" title="'.$_edit_title.'" href="'.$_edit_href.'"><img class="ims_image" border="0" src="'.$icon.'"><br>'.$_edit_txt.'</a>';

          foreach ($otherlangs as $otherlang) {
            // Make sure the html file for the other language exists
            $path = "\\".$siteinfo["sitecollection"]."\\preview\\objects\\".$siteinfo["object"]."\\";
            $otherfile = $otherlang . "-page.html";
            if (!N_FileExists("html::".$path.$otherfile) && !N_ReadFile("html::".$path.$otherfile)) { // Use N_ReadFile (with locking) to be certain that the file does not exists
              N_CopyFile("html::".$path.$otherfile, "html::".$path."page.html");
            }
            if (N_FileExists("html::openims/edit-$otherlang.gif")) 
            {
                 $icon = "/ufc/rapid/openims/edit-$otherlang.gif";
              $icon_coolbar_v2 = ML_LanguageIcon($otherlang); //'/ufc/rapid/openims/f0-'.$otherlang.'.gif';
              $extratext = "";
            } else {
                 $icon = "/ufc/rapid/openims/edit.gif";
                $icon_coolbar_v2 = false;

              $extratext = " (".strtoupper($otherlang).")";
            }
            $_multi_href = IMS_GenerateTransferURL($path, $otherfile, $editor, true);
            $_multi_title = ML("Wijzig de inhoud (content) van deze pagina","Change the content of this page") . $extratext;
            $_multi_txt = ML("Wijzig", "Edit");
            $coolbar .= '</center></td><td class="ims_td"><center>';
            $coolbar .= '<a class="ims_navigation" title="'.$_multi_title.'" href="' . $_multi_href . '"><img class="ims_image" border="0" src="'.$icon.'"><br>'.$_multi_txt.$extratext.'</a>';



          }

        }
        if ($object["editor"]=="Microsoft Excel") {
          global $myconfig;
          if ($myconfig[IMS_Supergroupname()]["edit_xls_html"]) {
            $editor = $myconfig[IMS_Supergroupname()]["edit_xls_html"];
          } else {
            $editor = "excel.exe";
          }
          $coolbar .= '<a class="ims_navigation" title="'.ML("Wijzig de inhoud (content) van deze pagina met Microsoft Excel","Change the content of this page with Microsoft Excel").'" href="'.
            IMS_GenerateTransferURL(
              "\\".$siteinfo["sitecollection"]."\\preview\\objects\\".$siteinfo["object"]."\\",
              "page.html",
              $editor, true
            ).'"><img class="ims_image" border="0" src="/ufc/rapid/openims/edit.gif"><br>'.ML("Wijzig", "Edit").'</a>';
        }
        if ($object["editor"]=="Microsoft Powerpoint") {
          global $myconfig;
          if ($myconfig[IMS_Supergroupname()]["edit_ppt_html"]) {
            $editor = $myconfig[IMS_Supergroupname()]["edit_ppt_html"];
          } else {
            $editor = "powerpnt.exe";
          }
          $coolbar .= '<a class="ims_navigation" title="'.ML("Wijzig de inhoud (content) van deze pagina met Microsoft Powerpoint","Change the content of this page with Microsoft Powerpoint").'" href="'.
            IMS_GenerateTransferURL(
              "\\".$siteinfo["sitecollection"]."\\preview\\objects\\".$siteinfo["object"]."\\",
              "page.html",
              $editor, true
            ).'"><img class="ims_image" border="0" src="/ufc/rapid/openims/edit.gif"><br>'.ML("Wijzig", "Edit").'</a>';
        }
        if ($object["editor"]=="PHP Code") {
          if (SHIELD_HasGlobalRight ($supergroupname, "develop")) {
            global $myconfig;
            if ($myconfig[IMS_Supergroupname()]["edit_php"]) {
              $editor = $myconfig[IMS_Supergroupname()]["edit_php"];
            } else {
              $editor = "notepad.exe";
            }
            $_php_href = IMS_GenerateTransferURL( "\\".$siteinfo["sitecollection"]."\\preview\\objects\\".$siteinfo["object"]."\\", "page.php" , $editor , true );
            $_php_title = ML("Wijzig de PHP code achter deze pagina","Change the PHP code behind this page");
            $_php_txt = ML("Wijzig", "Edit");
            $coolbar .= '<a class="ims_navigation" title="'.$_php_title.'" href="' . $_php_href .'"><img class="ims_image" border="0" src="/ufc/rapid/openims/edit.gif"><br>' . $_php_txt . '</a>';

          }
        }
        if ($object["editor"]=="Form" || $object["editor"]=="BPMSForm") {
            $editor = "winword.exe";
            global $myconfig;
            $_bpms_href = IMS_GenerateTransferURL( "\\".$siteinfo["sitecollection"]."\\preview\\objects\\".$siteinfo["object"]."\\", "page.html", $editor, true );
            
            $_bpms_title = ML("Wijzig de inhoud (content) van dit formulier met Microsoft Word","Change the content of this form with Microsoft Word");
            $_bpms_txt = ML("Wijzig", "Edit");
              global $myconfig;
              $editor = "winword.exe";

              $coolbar .= '<a class="ims_navigation" title="'.$_bpms_title.'" href="'.$_bpms_href.'"><img class="ims_image" border="0" src="/ufc/rapid/openims/edit.gif"><br>'.$_bpms_txt.'</a>';
        }
      }
    }

      // Properties button
    $show = ims_coolbar_has_button( 'properties' );
    if($show) {

    if (SHIELD_HasObjectRight ($supergroupname, $object_id, "edit")) { 

      $_properties_url = FORMS_URL (CMSUIF_properties_formspec ($object_id));

      if ( !$is_coolbar_v2 )  $coolbar .= "</center></td><td class=\"ims_td\"><center><a class=\"ims_navigation\" title=\"\" href=\"$_properties_url\">".'<img class="ims_image" border="0" src="/ufc/rapid/openims/properties.gif"><br>'.ML("Eigenschappen","Properties")."</a>";



      // Separator
      $coolbar .= '</center></td><td class="ims_td"><center>';
      $coolbar .= '<img class="ims_image" src="/ufc/rapid/openims/separator.gif">';

      $form = array();

    } // if (SHIELD_HasObjectRight ($supergroupname, $object_id, "edit"))
//    } // end else of if ( $myconfig[ $sgn ]["cms"]["showtreeview"] == "yes" )
    } // end $show = ims_coolbar_has_button( 'properties' );
    } // end $show = ims_coolbar_has_button( 'edit' );

    // Publish content button
    /*
    $show = true;
    if($myconfig[$supergroupname]["hidecoolbarbuttons"]) {
       if( in_array("publish", $myconfig[$supergroupname]["hidecoolbarbuttons"]) === true ) $show = false;
    }*/
    $show = ims_coolbar_has_button( 'publish' );
    if($show) {

    $options = SHIELD_AllowedOptions ($supergroupname, $object_id);
    $workflow = &SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);
    if (is_array($options)) {
      reset ($options);
      uuse( "cmsuif" );
      while (list($option)=each($options)) {
        $form = cmsuif_workflow_option_formspec( $supergroupname , $option , $object_id );
        $url = FORMS_URL ( $form );
        $coolbar .= '</center></td><td class="ims_td"><center>';
        $_coolbar .= '<li class="edit"><a href="'.$url.'"><span>'.$option.'</span></a></li>';

        $coolbar .= '<a class="ims_navigation" href="'.$url.'"><img class="ims_image" border="0" size="2" src="/ufc/rapid/openims/template.gif"><br>'.$option.'</a>';
      }
    }
    }// $myconfig[$sgn]["hidecoolbarbuttons"]


    // Revoke content button (preview)
    $show = ims_coolbar_has_button( 'revoke' );
    if($show) {
    if (SHIELD_HasObjectRight ($supergroupname, $object_id, "edit")) {
        if (($siteinfo["preview"]=="yes") && ($siteinfo["published"]=="yes")) {

            $form = Array();
            $form["input"]["sgn"] = $supergroupname;
             $form["input"]["object_id"] = $object_id;
             $form["input"]["site_id"] = $site_id;
             $form["postcode"] = 
             '$sgn = $input["sgn"];$site_id = $input["site_id"]; $object_id = $input["object_id"];
             IMS_RecoverObject( $sgn , $site_id, $object_id );
             $obj = MB_load("ims_".$sgn."_objects" , $object_id );
             $obj["preview"] = "no";
             MB_save("ims_".$sgn."_objects" , $object_id , $obj );
             ';
             $_cancel_href = FORMS_URL ($form);

        $_cancel_title = ML("Annuleer wijzigingen sinds laatste publicatie(was terugtrekken / terughalen gepubliceerde versie in oude coolbar)","Cancel changes since last publication");
        $_cancel_txt = ML("Annuleren","Cancel");

        $coolbar .= '</center></td><td class="ims_td"><center>';
        $coolbar .= '<a class="ims_navigation" title="'.cancel_title.'" href="'.$cancel_href.'"><img class="ims_image" border="0" size="2" src="/ufc/rapid/openims/revoke.gif"><br>'.ML("Terugtrekken","Withdraw").'</a>';
      }
    }

    // Revoke content button (published)
    if (SHIELD_HasObjectRight ($supergroupname, $object_id, "edit") && SHIELD_HasObjectRight ($supergroupname, $object_id, "delete")) {
      if ( ($siteinfo["preview"]=="no") && ($siteinfo["published"]=="yes") && ($object["parent"]) ) {
        $form = array();
        $form["input"]["supergroupname"] = $supergroupname;
        $form["input"]["object_id"] = $object_id;
        $form["input"]["site_id"] = $site_id;
        $form["postcode"] = 'IMS_RevokePage( $input );';
        $url = FORMS_URL ($form);

        $_withdraw_href = $url;
        $_withdraw_title = ML("Verwijder de gepubliceerde versie en zet de pagina in preview", "Remove published page (return to preview version)");
        $_withdraw_txt = ML("Terugtrekken","Withdraw");

        $coolbar .= '</center></td><td class="ims_td"><center>';
        $coolbar .= '<a class="ims_navigation" title="'.ML("Verwijder de gepubliceerde versie en zet de pagina in preview", "Remove published page (return to preview version)").'" href="'.$url.'"><img class="ims_image" border="0" size="2" src="/ufc/rapid/openims/revoke.gif"><br>'.ML("Terugtrekken","Withdraw").'</a>';
      }
    }
    }

    // History button
    $show = ims_coolbar_has_button( 'history' );    
    if($show) {
        $_history_href = '/openims/openims.php?mode=history&back='.urlencode($goto).'&sitecollection_id='.$siteinfo["sitecollection"].'&site_id='.$siteinfo["site"].'&object_id='.$siteinfo["object"];
        $_history_txt = ML("Historie","History");
        $_history_title = ML("Laat oudere versies van deze pagina zien","Show older versions of this page");


        $coolbar .= '</center></td><td class="ims_td"><center>';
        $coolbar .= '<a class="ims_navigation" title="'.$_history_title.'" href="'.$_history_href.'"><img class="ims_image" border="0" src="/ufc/rapid/openims/history.gif"><br>'.$_history_txt.'</a>';
    }



    // Separator
    $coolbar .= '</center></td><td class="ims_td"><center>';
    $coolbar .= '<img class="ims_image" src="/ufc/rapid/openims/separator.gif">';

    // Move button
    $show = ims_coolbar_has_button( 'move' );    
    if($show) {
        if (SHIELD_HasObjectRight ($supergroupname, $object_id, "move")) 
        {
            if ($object["parent"]) {
            $goto2 = "http://".getenv("HTTP_HOST")."/$site_id/".$object["parent"].".php";

            $_move_txt = ML("Verplaats","Move");
            $_move_title = ML("Verplaats deze pagina","Move this page");
            $_move_href = '/openims/action.php?goto='.urlencode($goto2).'&command=movestart&sitecollection_id='.$siteinfo["sitecollection"].'&site_id='.$siteinfo["site"].'&object_id='.$siteinfo["object"];
            $goto2 = "http://".getenv("HTTP_HOST")."/$site_id/".$object["parent"].".php";


            $coolbar .= '</center></td><td class="ims_td"><center>';
            $coolbar .= '<a class="ims_navigation" title="'.ML("Verplaats deze pagina","Move this page").'" href="/openims/action.php?goto='.urlencode($goto2).'&command=movestart&sitecollection_id='.$siteinfo["sitecollection"].'&site_id='.$siteinfo["site"].'&object_id='.$siteinfo["object"].'"><img class="ims_image" border="0" src="/ufc/rapid/openims/move.gif"><br>'.ML("Verplaats","Move").'</a>';
    
            $_moveup_txt = ML("Omhoog","Up");
            $_moveup_title = ML("Verplaats naar boven","Move up");
            $_moveup_href = '/openims/action.php?goto='.urlencode($goto).'&command=up&sitecollection_id='.$siteinfo["sitecollection"].'&dummy='.N_GUID().'&site_id='.$siteinfo["site"].'&object_id='.$siteinfo["object"];

            $_movedown_txt = ML("Omlaag","Down");
            $_movedown_title = ML("Verplaats naar beneden","Move down");
            $_movedown_href = '/openims/action.php?goto='.urlencode($goto).'&command=down&sitecollection_id='.$siteinfo["sitecollection"].'&dummy='.N_GUID().'&site_id='.$siteinfo["site"].'&object_id='.$siteinfo["object"];



            $coolbar .= '</center></td><td class="ims_td"><center>';
            $coolbar .= '<a class="ims_navigation" title="'.$_moveup_title.'" href="'.$_moveup_href.'"><img class="ims_image" border="0" src="/ufc/rapid/openims/up.gif"><br>'.$_moveup_txt.'</a>';
    
            $coolbar .= '</center></td><td class="ims_td"><center>';
            $coolbar .= '<a class="ims_navigation" title="'.ML("Verplaats naar beneden","Move down").'" href="/openims/action.php?goto='.urlencode($goto).'&command=down&sitecollection_id='.$siteinfo["sitecollection"].'&dummy='.N_GUID().'&site_id='.$siteinfo["site"].'&object_id='.$siteinfo["object"].'"><img class="ims_image" border="0" src="/ufc/rapid/openims/down.gif"><br>'.ML("Omlaag","Down").'</a>';
    
            // Separator
            $coolbar .= '</center></td><td class="ims_td"><center>';
            $coolbar .= '<img class="ims_image" src="/ufc/rapid/openims/separator.gif">';
          }
        }
    }

    // Delete button
/*    $show = true;
    if($myconfig[$supergroupname]["hidecoolbarbuttons"]) {
       if( in_array("delete", $myconfig[$supergroupname]["hidecoolbarbuttons"]) === true ) $show = false;
    }*/
    $show = ims_coolbar_has_button( 'delete' );
    if($show) {
    if (SHIELD_HasObjectRight ($supergroupname, $object_id, "delete")) {
      if ($object["parent"]) {
        $doit = true;
        $children = $object["children"];
        if (is_array($children)) reset($children);
        if (is_array($children)) while (list($child)=each($children)) {
          $obj = &IMS_AccessObject ($supergroupname, $child);
          if ($obj["preview"]=="yes" || $obj["published"]=="yes") $doit = false;
        }
        if ($doit) {

          $goto2 = "http://".getenv("HTTP_HOST")."/$site_id/".$object["parent"].".php";
          $shorttitle = "" . $object["parameters"]["preview"]["shorttitle"];
          if ($shorttitle=="") $shorttitle = "" . $object["parameters"]["published"]["shorttitle"];

          $title = ML("Verwijder deze pagina","Remove this page");
          $form = array();     
          $form["title"] = ML("Weet u het zeker?","Are you sure?");
          $form["input"]["goto2"] = $goto2; 
          $form["input"]["shorttitle"] = $shorttitle;
          $form["input"]["collection"] =$siteinfo["sitecollection"];
          $form["input"]["site"] = $siteinfo["site"];
          $form["input"]["object"] = $siteinfo["object"];
          $form["formtemplate"] = '
             <table>
             <tr><td colspan=2>
               <font face="arial" size=2>'.ML("Wilt u de pagina %1 verwijderen?","Do you want to delete the page %1?", "<b>".$shorttitle."</b>").'
               </font>
             </td></tr>
             <tr><td colspan=2>&nbsp</td></tr>
             <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
             </table>
          ';        
          $form["postcode"] = '
             $gotook = "closeme&parentgoto:http://".getenv("HTTP_HOST")."/openims/action.php?goto=".urlencode($input["goto2"])."&command=delete&sitecollection_id=".
             $input["collection"]."&site_id=".$input["site"]."&object_id=".$input["object"];
          ';
      $url = FORMS_URL ($form);

      $_remove_txt = ML("Verwijder", "Remove");
      $_remove_href = $url;
      $_remove_title = ML("Verwijder deze pagina","Remove this page");


          $coolbar .= '</center></td><td class="ims_td"><center>';
          $coolbar .= '<a class="ims_navigation" title="'.ML("Verwijder deze pagina","Remove this page").'" href="'.$url.'"><img class="ims_image" border="0" size="2" src="/ufc/rapid/openims/delete.gif"><br>'.ML("Verwijder", "Remove").'</a>';
          }
        }
      }
    }

    // New page button
    $show = ims_coolbar_has_button( 'new' );    
    if($show) {
    if (SHIELD_HasObjectRight ($supergroupname, $object_id, "newpage")) {
      $coolbar .= '</center></td><td class="ims_td"><center>';

      uuse( "cmsuif" );
      $form = CMSUIF_newpage_formspec( $supergroupname , $site_id, $object_id );

      $url = FORMS_URL ($form);

      $_new_title = ML("Maak nieuwe webpagina (onder de huidige pagina)","Create new webpage (under current page)");
      $_new_txt = ML("Nieuw","New");



      $coolbar .= "<a href=\"$url\" class=\"ims_navigation\" title=\"".ML("Maak nieuwe webpagina (onder de huidige pagina)","Create new webpage (under current page)")."\"><img class=\"ims_image\" border=\"0\" src=\"/ufc/rapid/openims/newpage.gif\"><br>".ML("Nieuw","New")."</a>";
    }
    }

    // Separator
    if($show) {
        $coolbar .= '</center></td><td class="ims_td"><center>';
        $coolbar .= '<img class="ims_image" src="/ufc/rapid/openims/separator.gif">';
    }

    // CVE: Custom Coolbar buttons
    if($myconfig[$supergroupname]["usecustomcoolbarbuttons"] == "yes" ) {

    $coolbar_buttons = cmsuif_coolbar_custom_buttons( get_defined_vars() );
    if ( $coolbar_buttons )
    {
        foreach( $coolbar_buttons AS $key => $coolbar_button )
        {

            $coolbar .= '</center></td><td><center><a href="'.$coolbar_button["url"].'" class="ims_navigation"><img class="ims_image" border="0" size="2" src="'.$coolbar_button["icon"].'"><br />'.$coolbar_button["title"].'</a>';
        }
        $coolbar .= '</center></td><td class="ims_td"><center>';
        $coolbar .= '<img class="ims_image" src="/ufc/rapid/openims/separator.gif">';
    }
    } 
    
    // Help
    $coolbar .= '</center></td><td class="ims_td"><center>';
    global $myconfig;
    if ($myconfig[IMS_SuperGroupName()]["helpurl"]) {
      $url = $myconfig[IMS_SuperGroupName()]["helpurl"];
    } else {
      $url = "http://doc.openims.com/openimsdoc_com/2de2c50a8a2054361e8cf6e9a7d6a5b7.php";
    }

    $_help_txt = ML("Help","Help");
    $_help_title = ML("Online help","Online help");

    $coolbar .= '<a target="_blank" class="ims_navigation" title="'.$_help_title.'" href="'.$url.'"><img class="ims_image" border="0" src="/ufc/rapid/openims/help.gif"><br>'.$_help_txt.'</a>';

    global $myconfig;
    if ($myconfig[IMS_SuperGroupName()]["hidelanguages"] != "yes") {

    $coolbar .= '</center></td><td class="ims_td"><center>';
    $coolbar .= ML_LanguageSelect($goto, $site_id);

    }
    
//    $_coolbar .= '<div class="logo"><img title="powered by OpenIMS" alt="OpenIMS logo" src="/ufc/rapid/openims/coolbar2/logo.png"></div>';

    }

    // End of coolbar

    $coolbar .= "</center></td></tr></table></td></tr></table>";
    
    // CVE NS page refresh iFrame
     global $myconfig;
    if ($myconfig[IMS_SuperGroupName()]["refreshaftersave"] && $myconfig[IMS_SuperGroupName()]["refreshaftersave"] > 0) 
    {
        $coolbar .= IMS_RefreshAfterSave(IMS_SuperGroupName(), $object_id, $myconfig[IMS_SuperGroupName()]["refreshaftersave"]);
    }
       
    $coolbar .= '<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td background="'. SKIN_HorizontalSeparator() .'"><img src="'. SKIN_HorizontalSeparator() .'"></td></tr></table>';


    return "$coolbar";

  } else {
    return "";
  }
}

function IMS_RefreshAfterSave ($p_sgn, $p_object_id, $p_delay)
{
	$frame = 'IMS_RPCrefresh';
	 $tbl = "ims_".$p_sgn."_objects";                            
	 $objRef = MB_Ref($tbl,$p_object_id);
	$iStart  = count($objRef["history"]);
	
	$hidden = '$icnt ='.$iStart.';';
	$hidden .= '$tbl = "IMS_".IMS_SuperGroupname()."_objects";';                            
	$hidden .= '$objRef = MB_Ref($tbl,"'.$p_object_id.'");';
	$hidden .= '$iChanged  = count($objRef["history"]);';
	$hidden .= 'if($iChanged > $icnt)
			{
				echo DHTML_EmbedJavaScript(\'parent.location = parent.location;\');
                        }
                    ';
			
	$result = DHTML_PrepRPC($frame);	// iFrame plaatsen
	$url = DHTML_RPCURL($hidden,"",$frame);
	$result .= DHTML_EmbedJavaScript($url); // uitvoeren toevoeging
	
        $random = N_GUID();

	$js = 'var t = null;var objectid = \''.$p_object_id.'\';var delayInSec = \''.$p_delay.'\';var iChanges = '.$iStart.';
	(function(){t = new Stopwatch(reloadiFrame, delayInSec);
		t.start();
	})();
	function Stopwatch(f, l )
	{
		l = l*1000; // conv naar msec
		this.intervalID = null;
		this.start = function(){this.intervalID = setInterval(f, l);}

		//this.stop = function(){clearInterval(this.intervalID);}
	}
	function reloadiFrame(){
          window.frames["'.$frame.'"].location = window.frames["'.$frame.'"].location;
	}';
	$result .= DHTML_EmbedJavaScript($js);
	return $result;
}

function IMS_NewPage_Old ($input)
{
  $rec = &MB_Ref ("temp", $input["key"]);
  IMS_NewPage ($input, $rec);
}

function IMS_RevokePage( $input )
{
  SHIELD_ProcessEdit ($input["supergroupname"], $input["object_id"]);
  $object = &MB_Ref("ims_".$input["supergroupname"]."_objects",$input["object_id"]);
  $object["preview"] = "yes";
  $object["published"] = "no";
  SEARCH_RemovePageFromSiteIndex ($input["supergroupname"], $input["site_id"], $input["object_id"]);

  global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
  $time = time();
  $guid = N_GUID();
  $object["history"][$guid]["type"] = "revoked";
  $object["history"][$guid]["revoketype"] = "unpublish";
  $object["history"][$guid]["when"] = time();
  $object["history"][$guid]["author"] = SHIELD_CurrentUser($input["supergroupname"]);
  $object["history"][$guid]["server"] = N_CurrentServer ();
  $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
  $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
  $object["history"][$guid]["server_addr"] = $SERVER_ADDR;
}

function IMS_NewPage ($input, $rec, $object_id="")
{

  if (!$object_id) $object_id = N_GUID();
  //echo "object_id: $object_id<br>";

  $site_id = $input["site"];
  //echo "site_id: $site_id<br>";

  $parent_id = $input["parent"];
  //echo "parent_id: $parent_id<br>";

  $sitecollection_id = $input["sitecollection"];
  //echo "sitecollection_id: $sitecollection_id<br>";

  $list = MB_Query ("ims_".$sitecollection_id."_templates", '$record["name"]=="'.$rec["template"].'"');
  reset ($list);
  list ($template_id) = each ($list);
  if (!$template_id) $template_id = $rec["template"];
  //echo "template_id: $template_id<br>";

  // LF20100316: Do not let IMS_CreateObject create a history entry, do it later (after filling the preview directory!) with IMS_ArchiveObject.
  $object = &IMS_CreateObject ($sitecollection_id, $template_id, $parent_id, $object_id, $site_id, true);

  $object["mysite"] = $site_id;
  $object["objecttype"] = "webpage";
  $object["template"] = $template_id;
  $object["preview"] = "yes";
  $object["published"] = "no";
  $object["workflow"] = $rec["workflow"];
  $object["parameters"]["preview"]["module"] = $rec["module"];
  $object["parameters"]["preview"]["shorttitle"] = $rec["shorttitle"];
  $object["parameters"]["preview"]["longtitle"] = $rec["longtitle"]; 
  $object["parameters"]["preview"]["template"] = $template_id; 

  if ($rec["editor"]=="Microsoft Excel") {
    $object["editor"] = "Microsoft Excel";
    N_CopyDir ("html::".$sitecollection_id."/preview/objects/".$object_id, "html::".$sitecollection_id."/templates/".$template_id."/excel");
  } else if ($rec["editor"]=="Microsoft Powerpoint") {
    $object["editor"] = "Microsoft Powerpoint";
    N_CopyDir ("html::".$sitecollection_id."/preview/objects/".$object_id, "html::".$sitecollection_id."/templates/".$template_id."/powerpoint");
  } else if ($rec["editor"]=="Form") {
    $object["editor"] = "Form"; 
    $object["form"]["metaspec"]["fields"]["naam"]["type"] = "string";
    $object["form"]["metaspec"]["fields"]["email"]["type"] = "string";
    global $myconfig;
    
    N_CopyDir ("html::".$sitecollection_id."/preview/objects/".$object_id, "html::".$sitecollection_id."/templates/".$template_id."/word");
    N_WriteFile ("html::".$sitecollection_id."/preview/objects/".$object_id."/confirm.html",
                 N_ReadFile ("html::openims/new_word/page.html"));
  } else if ($rec["editor"]=="BPMSForm") {
    $object["editor"] = "BPMSForm"; 
    $object["form"]["metaspec"]["fields"]["naam"]["type"] = "string";
    $object["form"]["metaspec"]["fields"]["email"]["type"] = "string";
    N_CopyDir ("html::".$sitecollection_id."/preview/objects/".$object_id, "html::".$sitecollection_id."/templates/".$template_id."/word");
    N_WriteFile ("html::".$sitecollection_id."/preview/objects/".$object_id."/confirm.html",
                 N_ReadFile ("html::openims/new_word/page.html"));
  } else if ($rec["editor"]=="PHP Code") {
    $object["editor"] = "PHP Code";
    N_WriteFile ("html::".$sitecollection_id."/preview/objects/".$object_id."/page.php", "<? ?>");
  } else {
    $object["editor"] = "Microsoft Word";
    N_CopyDir ("html::".$sitecollection_id."/preview/objects/".$object_id, "html::".$sitecollection_id."/templates/".$template_id."/word");

    // Create pages for other languages, only if they do not exist yet (they might exist if we start support multilingual templates)
    global $myconfig;
    if (is_array($myconfig[$sitecollection_id]["ml"]["sitelanguages"])) {
      $languages = $myconfig[$sitecollection_id]["ml"]["sitelanguages"][$site_id];
      if ($languages) {
        array_shift($languages);
        foreach ($languages as $otherlang) {
          $path = "html::".$sitecollection_id."/preview/objects/".$object_id."/";
          $file = "page.html";
          $otherfile = $otherlang . "-page.html";
          if (!N_FileExists($path.$otherfile) && !N_ReadFile($path.$otherfile)) { 
            N_CopyFile($path.$otherfile, $path."page.html");
          }
        }
      }
    }
  }
  IMS_ArchiveObject ($sitecollection_id, $object_id, SHIELD_CurrentUser ($sitecollection_id), true); // Create history entry

  IMS_SetLocation ($sitecollection_id, $object_id, $parent_id);
  $input ["object_id"] = $object_id;
  $output = IMS_SuperMixer ($sitecollection_id, $site_id, $object_id, "generate_static_page");
  N_WriteFile ("html::".$site_id."/".$object_id.".php", $output);

  return $object_id;
}

function IMS_DetermineJustTheSite ()
{
  if (N_OpenIMSCE()) return "ce_com";
  
}

function IMS_DetermineSite()
{
  return IMS_SiteInfo ();
}

function IMS_SetSuperGroupName ($supergroupname)
{
  global $activesupergroupname, $knownsupergroupname;
  $activesupergroupname = $supergroupname;
  $knownsupergroupname = true;
}

function IMS_SuperGroupName() 
{
  N_Debug ("IMS_SuperGroupName()"); 
  global $activesupergroupname, $knownsupergroupname;
  if (N_OpenIMSCE()) {
    $activesupergroupname = $knownsupergroupname = "ce_sites";
  }
  
  return $activesupergroupname;
}

function IMS_Domain2SiteCollection ($domain)
{
  N_Debug ("IMS_Domain2SiteCollection ($domain)");
  if (N_OpenIMSCE()) return "ce_sites";
  
}
 
function IMS_SiteInfo ($sitecollection_id="", $site_id="", $object_id="")
{

  global $IMS_SiteInfo_cache, $IMS_SiteInfo_defined, $IMS_SiteInfo_domain;
 
  $cachekey = "[".$sitecollection_id."|".$site_id."|".$object_id."]";
  if (($IMS_SiteInfo_defined[$cachekey]) && ($IMS_SiteInfo_domain."" == "")){
    return $IMS_SiteInfo_cache[$cachekey];
  }
 
  N_Debug ("IMS_SiteInfo START ($sitecollection_id, $site_id, $object_id)");
 
  // determine $sitecollection_id
  if (N_OpenIMSCE()) $sitecollection_id = "ce_sites";
  
 
  // determine $site_id
  if (N_OpenIMSCE()) $site_id = "ce_com";
  
 
  // determine $object_id
  if (!$object_id) 
  {
    global $SCRIPT_NAME;
    $object_id = strtolower($SCRIPT_NAME);
    while (strpos (" ".$object_id, "/")) $object_id = str_replace (".php","", N_KeepAfter ($object_id, "/"));    
    if ($object_id=="index") {
      $site = &MB_Ref ("ims_sites", $site_id);

      $object_id = $site["homepage"];
    }
  }
 
  if ($sitecollection_id && $site_id && $object_id)  {
    if ($object_id=="openims") {
      $ret["sitecollection"] = $sitecollection_id;
      $ret["site"] = $site_id;
    } else {
      $ret["sitecollection"] = $sitecollection_id;
      $ret["site"] = $site_id;
      $ret["object"] = $object_id;
      $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
      $ret["template"] = $object["template"];
      $ret["parameters"] = $object["parameters"];
      $ret["allobjectdata"] = $object;
      $ret["preview"] = $object["preview"];
      $ret["published"] = $object["published"];
    }
  } else {    
    $ret = null; // failed to determine context. 
    /* LF: prevent "cannot use string offset as array" problem. 
     * Why the fuck can [] be used for string offsets, what is wrong with substr? 
     * Operator overloading + weak typing = stupid. See javascript's + operator.
     * Weak typing + fatal runtime errors instead of type casting = insane.
     * Throwing those fatal errors for data types that are ***created***
     * by an overloaded operator whose overloaded meaning is unwanted and
     * totally unnecessary = braindead.
     */
  }
 
  N_Debug ("IMS_SiteInfo END ($sitecollection_id, $site_id, $object_id)");

  $IMS_SiteInfo_cache[$cachekey] = $ret;
  $IMS_SiteInfo_defined[$cachekey] = true;
 
  return $ret;
 
}

function IMS_RestoreObject ($sitecollection_id, $object_id, $version_id)
{
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  N_CopyDir ("html::".$sitecollection_id."/preview/objects/".$object_id, 
             "html::".$sitecollection_id."/objects/history/".$object_id."/".$version_id);

  // check if the doctypes are the same
  $filename = FILES_TrueFilename ($sitecollection_id, $object_id, "preview");
  $newfilename = FILES_TrueFilename($sitecollection_id, $object_id, $version_id);
  if ($filename != $newfilename) {
    FILES_HandleFilenameChange($sitecollection_id, $object_id, $newfilename);
  }

  IMS_ArchiveObject ($sitecollection_id, $object_id, SHIELD_CurrentUser ($sitecollection_id));
  foreach ($object["history"] as $id => $specs) { 
    if ($specs["type"]=="" || $specs["type"]=="edit" || $specs["type"]=="new") $last = $id;
  }
  if ($last) $object["history"][$last]["restore"] = "yes";
  if ($last) $object["history"][$last]["fromversion"] = $version_id;

  SHIELD_ProcessEdit ($sitecollection_id, $object_id);
}

function IMS_ArchiveObject ($sitecollection_id, $object_id, $user_id, $new=false, $language = "", $allfiles=false)
{
  global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  $time = time();
  $guid = N_GUID();
  if ($new) {
    $object["history"][$guid]["type"] = "new";
  } else {
    $object["history"][$guid]["type"] = "edit";
  }
  $object["history"][$guid]["when"] = $time;
  $object["history"][$guid]["author"] = $user_id;
  if ($language) $object["history"][$guid]["language"] = $language;
  $object["history"][$guid]["server"] = N_CurrentServer ();
  $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
  $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
  $object["history"][$guid]["server_addr"] = $SERVER_ADDR;
  $object["history"][$guid]["filename"] = $object["filename"];
  $object["history"][$guid]["executable"] = $object["executable"];
  if (!$allfiles && $object["objecttype"] == "document" && FILES_FileType($sitecollection_id, $object_id, "preview") != "imsctn.txt") {
    // Only copy the document itself, not (non-up-to-date) versions in old document formats
    N_CopyFile ("html::".$sitecollection_id."/objects/history/".$object_id."/".$guid."/".FILES_TrueFileName($sitecollection_id, $object_id),
                "html::".$sitecollection_id."/preview/objects/".$object_id."/".FILES_TrueFileName($sitecollection_id, $object_id));
  } else { // cms pages and containers
    N_CopyDir ("html::".$sitecollection_id."/objects/history/".$object_id."/".$guid,
               "html::".$sitecollection_id."/preview/objects/".$object_id);
  }
}

function IMS_SignalDatachange ($sitecollection_id, $object_id, $onlyifdocdatachanged=false) // Called when the metadata is changed, TODO: do something smart with multilingual lists
{
  global $myconfig, $IMS_SignalDatachange_ctr;

  if ($onlyifdocdatachanged) { // Only allow 10 calls per document to prevent unwanted recursion (e.g. fields containing transfer agent links)
    if (++$IMS_SignalDatachange_ctr[$sitecollection_id][$object_id]>=11) return; 
  }

  uuse ("marker");
  $object = IMS_AccessObject ($sitecollection_id, $object_id);

  if (MARKER_CanHaveMarker(FILES_FileType($sitecollection_id, $object_id, "published")) || MARKER_CanHaveMarker(FILES_FileType($sitecollection_id, $object_id, "preview"))) {
    $workflow = MB_Ref ("shield_".$sitecollection_id."_workflows", $object["workflow"]);
    $allfields = MB_Ref ("ims_fields", $sitecollection_id);
    $allfields = FORMS_EnhanceAllFieldspecs($allfields);
    for ($i=1; $i<1000; $i++) {
      if ($field=$workflow["meta"][$i]) {
        $formvalue = FORMS_ShowValue ($object["meta_".$field], $allfields[$field], $object, $object);
        $data["set_".$field] = SEARCH_HTML2TEXT ($formvalue,true); // JG - search_html2text should not be done for openoffice files because it supports all characters - could be fixed later
      }
      if (($allfields[$field]["type"] == "strml" || $allfields[$field]["type"] == "txtml")
          && (!$allfields[$field]["specs"]["autolevel"] || ($myconfig[IMS_SuperGroupName()]["ml"]["metadatalevel"] & (1 << ($allfields[$fields]["specs"]["autolevel"] - 1))))) {
        $langs = $myconfig[IMS_SuperGroupName()]["ml"]["languages"];
        if (!$langs) $langs = array("nl", "en");
        foreach ($langs as $lang) {
          $data["set_{$lang}_{$field}"] = SEARCH_HTML2TEXT (FORMS_ShowValue ($object["meta_".$field], $allfields[$field], $object, $object, $lang), true);
        }
      }
    }
    if ($object["dynmeta"] && is_array ($object["dynmeta"])) {
      foreach ($object["dynmeta"] as $dummy => $field) {
        $data["set_".$field] = SEARCH_HTML2TEXT (FORMS_ShowValue ($object["meta_".$field], $allfields[$field], $object, $object),true);
      }
    }

    foreach (array("name" => $object["shorttitle"], 
                   "description" => $object["longtitle"], 
                   "workflow" => $workflow["name"])
             as $field => $value) {
      $data["set_{$field}"] = FORMS_ML_Filter($value);

      if (($myconfig[IMS_SuperGroupName()]["ml"]["metadatalevel"] && $myconfig[IMS_SuperGroupName()]["ml"]["metadatafiltering"] != "no") || 
           $myconfig[IMS_SuperGroupName()]["ml"]["metadatafiltering"] == "yes") {
        $langs = $myconfig[IMS_SuperGroupName()]["ml"]["languages"];
        if (!$langs) $langs = array("nl", "en");
        foreach ($langs as $lang) {
          $data["set_{$lang}_{$field}"] = FORMS_ML_Filter($value, $lang);
        }
      }
    }

    $time = time();
    if (is_array($object["history"])) {
      reset ($object["history"]);
      while (list($k, $dat)=each($object["history"])) {
        $time = $dat["when"];
      }
    }
    $publishtime = QRY_DMS_Published_v1(IMS_SuperGroupName(), $object);

    $dateFormatEN = (!empty($myconfig[$sitecollection_id]["DataChangeDateFormat"]["EN"])) ? $myconfig[IMS_SupergroupName()]["DataChangeDateFormat"]["EN"] : "F dS Y" ;
    $dateFormatNL = (!empty($myconfig[$sitecollection_id]["DataChangeDateFormat"]["NL"])) ? $myconfig[IMS_SupergroupName()]["DataChangeDateFormat"]["NL"] : "d F Y" ;
   
    $old = ML_SetLanguage ("nl");
    $data["set_lastchangeddmy"] = N_Date ($dateFormatNL, $dateFormatEN, $time);
    if ($publishtime) { 
      $data["set_lastpublisheddmy"] = N_Date ($dateFormatNL, $dateFormatEN, $publishtime);
    } else {
      $data["set_lastpublisheddmy"] = "niet gepubliceerd";
    }
    ML_SetLanguage ("en");
    $data["set_lastchangedmdy"] = N_Date ($dateFormatNL, $dateFormatEN, $time);
    if ($publishtime) {
      $data["set_lastpublishedmdy"] = N_Date ($dateFormatNL, $dateFormatEN, $publishtime);
    } else {
      $data["set_lastpublishedmdy"] = "not published";
    }
    ML_SetLanguage ($old);

    $user = MB_Ref ("shield_".$sitecollection_id."_users", $object["allocto"]);
    $data["set_allocto"] = $user["name"];

    FLEX_LoadSupportFunctions (IMS_SuperGroupName());

    if (!function_exists ("IMS_SpecialDocumentData")) {
      $internal_component = FLEX_LoadImportableComponent ("support", "a153a54aa0fbb8d96580544e4798d580");
      $internal_code = $internal_component["code"];
      eval ($internal_code);
    }

    $data = IMS_SpecialDocumentData ($data, $sitecollection_id, $object_id, $object);

    if ($onlyifdocdatachanged) { 
      if (!$object["oldspecialdata_md5"]) { // first time, do not update document
        $object["oldspecialdata_md5"] = md5 (serialize ($data));
        MB_Save ("ims_".$sitecollection_id."_objects", $object_id, $object);
        return;
      } else {
        if (md5 (serialize ($data)) != $object["oldspecialdata_md5"]) { // actual change, update document
          $object["oldspecialdata_md5"] = md5 (serialize ($data));
          MB_Save ("ims_".$sitecollection_id."_objects", $object_id, $object);
        } else { // no change, do nothing
          return;
        }
      }
    }

    // History versions 
    $ctr = 0;
    $hcount = 0;
    if (is_array ($object["history"])) foreach ($object["history"] as $id => $specs) {
      if ($specs["type"]=="" || $specs["type"]=="edit" || $specs["type"]=="new") {         
        $hcount++;
      }
    }
    if (is_array ($object["history"])) foreach ($object["history"] as $id => $specs) {
      if ($specs["type"]=="" || $specs["type"]=="edit" || $specs["type"]=="new") {         
        $ctr++;
        if ($ctr == $hcount) {
          $doc = FILES_TrueFileName ($sitecollection_id, $object_id, $id);
          $thedoctype = FILES_FileType ($sitecollection_id, $object_id, $id);
          if (MARKER_CanHaveMarker($thedoctype)) {
            $filename = "html::".$sitecollection_id."/objects/history/".$object_id."/".$id."/$doc";
            $data["set_version"] = IMS_Version($sitecollection_id, $object_id);
            $data["set_status"] = ML("historie", "history");
            $data["set_stage"] = $object["stage"];
            if (N_QuickFilesize ($filename) && MARKER_CanReadMarker($thedoctype)) {
              $olddata = MARKER_Load ($filename);
              if ($olddata["set_status"]!=ML("historie", "history")) VVFILE_AlterFile($filename, 'uuse("marker"); MARKER_Save($file, $input);', $data, 1); // do not alter history
            }
          }
        }
      }
    }

    // Preview version
    $doc = FILES_TrueFileName ($sitecollection_id, $object_id, "preview");
    $thedoctype = FILES_FileType ($sitecollection_id, $object_id, "preview");
    if (MARKER_CanHaveMarker($thedoctype)) {
      $filename = "html::".$sitecollection_id."/preview/objects/".$object_id."/$doc";
      $data["set_version"] = IMS_Version($sitecollection_id, $object_id);

      //wijziging voor DZ
      if($data["set_versienummer"]) $data["set_versienummer"] = $data["set_version"];

      $data["set_status"] = ML("concept", "concept");
      $data["set_stage"] = $object["stage"];
      if (N_QuickFilesize ($filename)) {
        VVFILE_AlterFile($filename, 'uuse("marker"); MARKER_Save($file, $input);', $data, 1);      
      }
    }

    // Published version
    $doc = FILES_TrueFileName ($sitecollection_id, $object_id, "published");
    $thedoctype = FILES_FileType ($sitecollection_id, $object_id, "published");
    if ($object["published"]=="yes" && MARKER_CanHaveMarker($thedoctype)) {
      $filename = "html::".$sitecollection_id."/objects/".$object_id."/$doc";


      // GV 13-7-2009
      // if force_metadata_changed_conversion is set to yes then always use changed metadata to generate pdf
      // aangenomen dat $sitecollection_id == $sgn !!!!!!!!!!!!!!!!!!!!!!!!!!!!! ???

      global $myconfig;
      if ($myconfig[$sitecollection_id]["directlyupdatepublishedworddocumentsmetadata"]!="yes" || ($thedoctype!="doc" && $thedoctype!="docx")) {
        if (MARKER_CanReadMarker($thedoctype) && ($myconfig[$sitecollection_id]["force_metadata_changed_conversion"] != 'yes')  ) {
          $data = MARKER_Load ($filename);
        }
      }

      // LF20081120 Just found out that the "lastpublished" field didnt work
      //   if you used the ufc link instead of the transfer agent to open the document.
      $old = ML_SetLanguage ("nl");
      if ($publishtime) { 
        $data["set_lastpublisheddmy"] = N_Date ($dateFormatNL, $dateFormatEN, $publishtime);
      } else {
        $data["set_lastpublisheddmy"] = "niet gepubliceerd";
      }
      ML_SetLanguage ("en");
      if ($publishtime) {
        $data["set_lastpublishedmdy"] = N_Date ($dateFormatNL, $dateFormatEN, $publishtime);
      } else {
        $data["set_lastpublishedmdy"] = "not published";
      }
      ML_SetLanguage ($old);

      $data["set_version"] = IMS_MajorVersion($sitecollection_id, $object_id);

      //wijziging voor DZ
      if($data["set_versienummer"]) $data["set_versienummer"] = $data["set_version"];

      $data["set_status"] = ML("gepubliceerd", "published");
      $data["set_stage"] = $workflow["stages"];

      if($myconfig[$sitecollection_id]["signaldatachangepostcode"]) {
        eval($myconfig[$sitecollection_id]["signaldatachangepostcode"]);
      }

      if (N_QuickFilesize ($filename)) {
        VVFILE_AlterFile($filename, 'uuse("marker"); MARKER_Save($file, $input);', $data, 1);      
      }
    }
  }
} 

function IMS_SignalObject ($sitecollection_id, $object_id, $user_id, $domain, $language = "") // called when the object is changed
{
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  IMS_ArchiveObject ($sitecollection_id, $object_id, $user_id, false, $language);
  SHIELD_ProcessEdit ($sitecollection_id, $object_id);
//  URPC_ExecuteInBackground_Now ('uuse ("search"); SEARCH_AddPreviewDocumentToDMSIndex ($input["sitecollection_id"), $input["object_id"]);', 
//                  array("sitecollection_id"=>$sitecollection_id, "object_id"=>$object_id));
  SEARCH_AddPreviewDocumentToDMSIndex ($sitecollection_id, $object_id);
  MAIL_SignalObject ($sitecollection_id, $object_id, $user_id, $domain);
  
  unset($object["doctopdf"]);
}

function IMS_ArchiveTemplate ($sitecollection_id, $template_id, $user_id)
{
  global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
  $object = &MB_Ref ("ims_".$sitecollection_id."_templates", $template_id);
  $time = time();
  $guid = N_GUID();
  $object["history"][$guid]["when"] = $time;
  $object["history"][$guid]["author"] = $user_id;
  $object["history"][$guid]["server"] = N_CurrentServer ();
  $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
  $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
  $object["history"][$guid]["server_addr"] = $SERVER_ADDR;

  N_CopyDir ("html::".$sitecollection_id."/templates/history/".$template_id."/".$guid,
             "html::".$sitecollection_id."/preview/templates/".$template_id);
}

function IMS_SignalTemplate ($sitecollection_id, $template_id, $user_id)
{
  $template = &MB_Ref ("ims_".$sitecollection_id."_templates", $template_id);
  $template["preview"] = "yes";
  IMS_ArchiveTemplate ($sitecollection_id, $template_id, $user_id);
}

function IMS_SignalRead ($file)
{
  set_magic_quotes_runtime(0);

  $file = str_replace ("//","/", $file);
  $file = str_replace ("\\","/", $file);
  $file = str_replace ("//","/", $file);
  $file = str_replace ("\\","/", $file);
  $file = substr ($file,1);

  //disect filename and call proper handling function
  $sitecollection_id = N_KeepBefore ($file, "/");
  $file1 = $file;
  $file = N_KeepAfter ($file1, "/objects/");
  if ($file) {
    $object_id = N_KeepBefore ($file, "/");
    $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
    if ($object["preview"] && $object["objecttype"]=="document") { // yes or no
      N_ObjectLog ($sitecollection_id, "document", $object_id, "read", array (
        "readtype" => "transfer agent",
        "rawfile" => $file1,
      ));
    }
    MB_Flush();
  }
}

function IMS_CheckForSpecialDocumentDataUpdate ($file)
{
  set_magic_quotes_runtime(0);

  $file = str_replace ("//","/", $file);
  $file = str_replace ("\\","/", $file);
  $file = str_replace ("//","/", $file);
  $file = str_replace ("\\","/", $file);
  $file = substr ($file,1);

  //disect filename and call proper handling function
  $sitecollection_id = N_KeepBefore ($file, "/");
  $file1 = $file;
  $file = N_KeepAfter ($file1, "/objects/");
  if ($file) {
    $object_id = N_KeepBefore ($file, "/");
    $object = MB_load("ims_".$sitecollection_id."_objects", $object_id);
    if ($object["objecttype"]=="document") { // yes or no
      IMS_SignalDatachange ($sitecollection_id, $object_id, true); // update metadata in document if specialdocumentdata has changed
    }
  }
}

function IMS_Signal ($file, $user_id, $domain)
{
  set_magic_quotes_runtime(0);

  $file = str_replace ("//","/", $file);
  $file = str_replace ("\\","/", $file);
  $file = str_replace ("//","/", $file);
  $file = str_replace ("\\","/", $file);
  $file = substr ($file,1);

  //disect filename and call proper handling function
  $sitecollection_id = N_KeepBefore ($file, "/");

  IMS_SetSuperGroupName ($sitecollection_id);
  FLEX_LoadSupportFunctions ($sitecollection_id);
  if (!function_exists ("IMS_HtmlFixer")) {
   $internal_component = FLEX_LoadImportableComponent ("support", "325f815b62726ddb4cb72b77d8010acb");
   $internal_code = $internal_component["code"];
   eval ($internal_code);
  }

  $file1 = $file;
  $file = N_KeepAfter ($file1, "/objects/");
  if ($file) {
    $object_id = N_KeepBefore ($file, "/");
    $thefilename = N_KeepAfter($file, "/", true);
    $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
    if ($object["objecttype"]=="webpage") {
      $filename = "html::".$sitecollection_id."/preview/objects/".$object_id."/".$thefilename;
      $language = N_KeepBefore($thefilename, "-page.html");
      $content = N_ReadFile ($filename);
      $content = IMS_HtmlFixer ($content);
      N_WriteFile ($filename, $content);
    }
    if ($object["preview"]) { // yes or no
      // check if the filetype has changed (e.g. doc -> docx)
      if ($object["objecttype"] == "document") {
        $path = "html::".$sitecollection_id."/preview/objects/".$object_id."/".$thefilename;
        $mtime = N_FileTime($path);
        if ($allowed = FILES_AllowedFiletypes(FILES_FileType($thefilename), $transferagent = true)) {
          // If other file types are allowed, check if they exists. If they exist, switch to the new file type.
          // FILES_AllowedFiletypes is NOT symmetrical when called with the $transferagent parameter.
          // You can switch from doc to docx, but you can never switch back.
          foreach ($allowed as $ext) {
            $checkfilename = substr($thefilename, 0, strlen($thefilename) - strlen(FILES_FileType($thefilename))) . $ext;
            $path = "html::".$sitecollection_id."/preview/objects/".$object_id."/".$checkfilename;
            if (N_FileExists($path) && N_FileSize($path)) {
              // Switch to this file type
              FILES_HandleFilenameChange($sitecollection_id, $object_id, $checkfilename);
              break;
            }
          }
        }
      }
      IMS_SignalObject ($sitecollection_id, $object_id, $user_id, $domain, $language);
    }
    MB_Flush();
  } else { // \amazingsite_sites\preview\templates\basic_homepage\mix.php 
    $file = N_KeepAfter ($file1, "/templates/");
    if ($file) {
      $template_id = N_KeepBefore ($file, "/");
      if (strpos ($file1, "word/page")) {
        $filename = "html::".$sitecollection_id."/preview/templates/".$template_id."/word/page.html";
        $content = N_ReadFile ($filename);
        $content = IMS_HtmlFixer ($content);
        N_WriteFile ($filename, $content);

      }
      $template = &MB_Ref ("ims_".$sitecollection_id."_templates", $template_id);
      if ($template["preview"]) { // yes or no
        IMS_SignalTemplate ($sitecollection_id, $template_id, $user_id);
      }
      MB_Flush();
    }
  }
} 


function IMS_RelocateIt ($content, $location, $prefix)
{
  $marker = "b893eca05c087f171b307d682d480c84";
  $content = str_replace ($prefix, $marker, $content) . "                                                                                ";
  while (strpos ($content, $marker)) {
    $markerplus = substr ($content, strpos ($content, $marker), strlen($marker)+40);
    $plus = substr ($markerplus, strlen($marker), 80);
    if (substr($plus,0,1)=="[" || substr($plus,0,1)=="/" || strpos (substr($plus,0,7), ":")) { 
      $plus = $prefix.$plus;

    } else {
      $found = 0;
      $newplus = $plus;
      while (substr ($newplus, 0, 3)=="../") { // path relative to root (e.g. template or cotent copied from other site)
        $newplus = substr ($newplus, 3);
        $found++;
      }
      if ($found>2) {
        $file = "html::/".N_KeepBefore ($newplus, '"');
        $plus = $prefix."/ufc/rapid2/".N_FileMD5 ($file)."/".$newplus;
      } else {
        $file = "html::/".N_KeepBefore ($location.$plus, '"');
        $plus = $prefix."/ufc/rapid2/".N_FileMD5 ($file).$location.$plus;
      }
//      if ($found>2) {
//        $plus = $prefix."/ufc/rapid/".$newplus;
//      } else {
//        $plus = $prefix."/ufc/rapid".$location.$plus;
//      }
    }
    $content = str_replace ($markerplus, $plus, $content);
  }
  return $content;
}

function IMS_Relocate ($content, $location)
{
  $content = IMS_RelocateIt ($content, $location, 'newImage("');
  $content = IMS_RelocateIt ($content, $location, '.src = "');
  $content = IMS_RelocateIt ($content, $location, 'src="');
  $content = IMS_RelocateIt ($content, $location, 'SRC="');
  $content = IMS_RelocateIt ($content, $location, 'background="');
  $content = IMS_RelocateIt ($content, $location, 'window.location.replace( "');
  $content = IMS_RelocateIt ($content, $location, "window.location.replace( '");
  $content = IMS_RelocateIt ($content, $location, 'path = "');
  $content = IMS_RelocateIt ($content, $location, 'var path = "');
  $content = IMS_RelocateIt ($content, $location, '"movie" value="');
  $content = IMS_RelocateIt ($content, $location, 'background-image:url("');
  return $content;
}

function IMS_Improve ($content)
{
  // use CSS of OpenIMS and/or the FrontPage template, ignore the MS-Word ones
  $content = str_replace ("a:link", "a.ignoreme:link", $content);
  $content = str_replace ("a:visited", "a.ignoreme:visited", $content);
  $content = preg_replace ("'(MsoNormal|MsoPlainText)([a-zA-Z0-9]*)'sie", '"\\1".substr(md5("\\2a'.N_GUID().'"),0,4)', $content);
  return $content;
}

function IMS_MegaMix ($template, $content) 
{ 
  uuse ("flex");
  $all = FLEX_LocalComponents (IMS_SuperGroupName(), "cmsblock");
  foreach ($all as $id => $specs) {
    $regexp = "(\[\[\[(<[^<>]*>)*".$specs["tag"]."(<[^<>]*>)*(:([^]]*))?\]\]\])";
    $tag = N_RegExp ($content, $regexp);
    while ($tag) {
      global $flexparams;
      $flexparams = preg_replace ("'<[\/\!]*?[^<>]*?>'si", "", N_RegExp ($content, $regexp, 5, 1));
      $content = str_replace ($tag, FLEX_Call (IMS_SuperGroupName(), "cmsblock", $id, "content"), $content);
      $flexparams = false;
      $tag = N_RegExp ($content, $regexp); 
    }
  }
  $pageinfo = IMS_DetermineSite();
  global $usetemplate;
  if (IMS_Preview()) {
    if ($usetemplate) {
      $templatelocation = "/".$pageinfo["sitecollection"]."/preview/templates/$usetemplate/"; 
    } else {
      $templatelocation = "/".$pageinfo["sitecollection"]."/preview/templates/".$pageinfo["template"]."/"; 
    }
    $contentlocation =  "/".$pageinfo["sitecollection"]."/preview/objects/".$pageinfo["object"]."/";
    if (strpos ($content, "window.location.replace")) {
      $content = '<font face="arial, helvetica" size="2" color="ff0000"><b>Dit type content wordt niet getoond in concept omgeving.</b></font>';
    }
    $module = $pageinfo["parameters"]["preview"]["module"];
  } else {
    if ($usetemplate) {
      $templatelocation = "/".$pageinfo["sitecollection"]."/templates/$usetemplate/";
    } else {
      $templatelocation = "/".$pageinfo["sitecollection"]."/templates/".$pageinfo["template"]."/";
    }
    $contentlocation =  "/".$pageinfo["sitecollection"]."/objects/".$pageinfo["object"]."/";
    $module = $pageinfo["parameters"]["published"]["module"];
  }
  $content = IMS_Relocate ($content, $contentlocation);
  $content = IMS_Improve ($content);
  $template = IMS_Relocate ($template, $templatelocation);  
  if ($module) {
    if (IMS_Preview()) {
      $content = OFLEX_Call ("cmsmodule", $module, "concept", $content);
    } else {
      $content = OFLEX_Call ("cmsmodule", $module, "content", $content);

    }
  }
  $result = str_replace ("[[[content]]]", $content, $template);
  return $result;
} 

function IMS_Parent ($sitecollection_id, $site_id, $object_id)
{
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  return $object["parent"];
}

function IMS_Root ($sitecollection_id, $site_id, $object_id)
{
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  while ($object["parent"]) {
    $object_id = $object["parent"];
    $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  }
  return $object_id;
}

function IMS_Visible ($sitecollection_id, $object_id, $forcesitemode = false)
{
  if (SHIELD_CurrentUser($sitecollection_id)!="unknown") {
    if (!SHIELD_HasObjectRight ($sitecollection_id, $object_id, "view")) return false;
  }
  $object = MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  $workflow = MB_Ref ("shield_".$sitecollection_id."_workflows", $object["workflow"]);
  $preview = IMS_Preview();
  if ($forcesitemode) $preview = false;
  if (($preview && ($object["preview"]=="yes")) || $object["published"]=="yes") {
    if (!$preview) {
      if ($workflow["scedule"]=="true") {
        $from = $object["parameters"]["published"]["from"];
        $until = $object["parameters"]["published"]["until"];
        if ($from>1000 && $until>1000) {
          if ($from <= time() && time() < $until) {
            return true;

          } else {
            return false;
          }
        }
      }
    }
    return true;
  } else {
    return false;
  }
}

function IMS_Children ($sitecollection_id, $site_id, $object_id)
{
  $ctr = 0;
  $ret = array();
  N_Debug ("IMS_Children ($sitecollection_id, $site_id, $object_id)", "IMS_Children");
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  $children = $object["children"];
  if (is_array($children)) {

    reset($children);
    MB_MultiLoad ("ims_".$sitecollection_id."_objects", $children);
    reset($children);
    while (list ($child_id, $sortkey) = each($children)) {
      $subobject = &MB_Ref ("ims_".$sitecollection_id."_objects", $child_id);
      if (IMS_Visible ($sitecollection_id, $child_id)) {
        if (IMS_Preview()) {
          $ret[$child_id]["shorttitle"] = $subobject["parameters"]["preview"]["shorttitle"];
          $ret[$child_id]["longtitle"] = $subobject["parameters"]["preview"]["longtitle"];
        } else {
          $ret[$child_id]["shorttitle"] = $subobject["parameters"]["published"]["shorttitle"];
          $ret[$child_id]["longtitle"] = $subobject["parameters"]["published"]["longtitle"];
        }
        if (!$ret[$child_id]["shorttitle"]) $ret[$child_id]["shorttitle"] = "???";
        $ret[$child_id]["url"] = "/$site_id/$child_id.php";
        $ctr++;
      }
    }
    return $ret;
  }
  return $ret;
}

function IMS_ChildrenOrBrothers ($sitecollection_id, $site_id, $object_id)
{
  $ret = IMS_Children ($sitecollection_id, $site_id, $object_id);
  if (!$ret) {
    $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
    if ($object["parent"]) $ret = IMS_Children ($sitecollection_id, $site_id, $object["parent"]);
  }
  return $ret;
}

function IMS_CLickPathI ($sitecollection_id, $site_id, $object_id, $font)
{
  N_Debug ("function IMS_CLickPath ($sitecollection_id, $site_id, $object_id, $font)");
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  if (IMS_Preview()) {

    $shorttitle = $object["parameters"]["preview"]["shorttitle"];
    $longtitle = $object["parameters"]["preview"]["longtitle"];
  } else {
    $shorttitle = $object["parameters"]["published"]["shorttitle"];
    $longtitle = $object["parameters"]["published"]["longtitle"];
  }
  $longtitle = htmlentities ($longtitle);
  $clickpath = "<a class=\"clickpath\" title = \"$longtitle\"href=\"/$site_id/$object_id.php\"><font $font>$shorttitle</font></a>";
  if ($object["parent"]) {
    if (!$shorttitle) {
      $clickpath = IMS_CLickPathI ($sitecollection_id, $site_id, $object["parent"], $font);
    } else {
      $clickpath = IMS_CLickPathI ($sitecollection_id, $site_id, $object["parent"], $font) . "<font $font>&nbsp;&gt;&nbsp;</font>".$clickpath;
    }
  }
  return $clickpath;

}

function IMS_CLickPath ($sitecollection_id, $site_id, $object_id, $font)
{
  N_Debug ("IMS_CLickPath ($sitecollection_id, $site_id, $object_id, $font)", "IMS_CLickPath");
  $res .= '<STYLE type="text/css">'.chr(13).chr(10);
  $res .= '<!--'.chr(13).chr(10);
  $res .= 'A.clickpath:link{color: #000000; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}'.chr(13).chr(10);
  $res .= 'A.clickpath:visited{color: #000000; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}'.chr(13).chr(10);
  $res .= 'A.clickpath:hover{color: #FF0000; text-decoration: underline; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}'.chr(13).chr(10);
  $res .= '-->'.chr(13).chr(10);
  $res .= '</STYLE>'.chr(13).chr(10);
  $res .= IMS_CLickPathI ($sitecollection_id, $site_id, $object_id, $font);
  return $res;
}

function IMS_Add2Tree ($sitecollection_id, &$tree, $object_id, $parent_id)
{
  if (IMS_Visible ($sitecollection_id, $object_id)) {

  if (IMS_Preview()) {
    $imsobj = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
    if ($imsobj["preview"]=="yes" || $imsobj["published"]=="yes") {
      TREE_AddObject ($tree, $object_id);
      TREE_ConnectObject ($tree, $parent_id, $object_id);
      $treeobj = &TREE_AccessObject ($tree, $object_id);  
      $treeobj["longtitle"] = htmlentities ($imsobj["parameters"]["preview"]["longtitle"]);
      $treeobj["shorttitle"] = $imsobj["parameters"]["preview"]["shorttitle"];
      $children = $imsobj["children"];
      MB_MultiLoad ("ims_".$sitecollection_id."_objects", $children);
      if (is_array ($children)) reset($children);
      if (is_array ($children)) while (list($key)=each($children)) {
        IMS_Add2Tree ($sitecollection_id, $tree, $key, $object_id);
      }
    }
  } else {
    $imsobj = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
    if ($imsobj["published"]=="yes") {
      TREE_AddObject ($tree, $object_id);
      TREE_ConnectObject ($tree, $parent_id, $object_id);
      $treeobj = &TREE_AccessObject ($tree, $object_id);  
      $treeobj["longtitle"] = $imsobj["parameters"]["published"]["longtitle"];
      $treeobj["shorttitle"] = $imsobj["parameters"]["published"]["shorttitle"];
      $children = $imsobj["children"];
      MB_MultiLoad ("ims_".$sitecollection_id."_objects", $children);

      if (is_array ($children)) reset($children);
      if (is_array ($children)) while (list($key)=each($children)) {
        IMS_Add2Tree ($sitecollection_id, $tree, $key, $object_id);
      }
    }
  }

  }
}

function IMS_Sitemap ($sitecollection_id, $site_id, $object_id="")
{
  N_Debug ("IMS_Sitemap ($sitecollection_id, $site_id, $object_id)", "IMS_Sitemap");
  $tree = TREE_Create();
  $root = MB_Fetch ("ims_sites", $site_id, "homepage");
  $children = MB_Fetch ("ims_".$sitecollection_id."_objects", $root, "children");
  MB_MultiLoad ("ims_".$sitecollection_id."_objects", $children);
  if (is_array ($children)) reset($children);
  if (is_array ($children)) while (list($key)=each($children)) {
    IMS_Add2Tree ($sitecollection_id, $tree, $key, "");
  }
  return TREE_CreateDHTML ($tree, "/".$site_id."/\$id.php", $object_id, true);
}

function IMS_DoDoc2Text ($docpath, $type) 
{
  $docpath = N_CleanPath ($docpath);
  $key = DFC_Key ("IMS_Word2Text ".N_ReadFile($docpath)." v4");
  if (DFC_Exists ($key)) {
    return DFC_Read ($key);
  } else {
    global $myconfig;
    $tmpfile = N_CleanPath ("html::"."/tmp/".N_Random().".txt");
    if ($myconfig["antiword"] && ($type=="doc" || $type=="dot")) {
      $command = $myconfig["antiword"]." ".escapeshellarg($docpath)." > \"".$tmpfile.'"';
      $internalcommand = "";
    }
    if ($myconfig["xlhtml"] && $type=="xls") { // not used anymore
      $command = $myconfig["xlhtml"]." ".escapeshellarg($docpath)." > \"".$tmpfile.'"';
      $internalcommand = "";
    }
    if ($myconfig["ppthtml"] && $type=="ppt") {
      $command = $myconfig["ppthtml"]." ".escapeshellarg($docpath)." > \"".$tmpfile.'"';
      $internalcommand = "";
    }
    if ($myconfig["pdftotext"] && $type=="pdf") {
      $command = $myconfig["pdftotext"]." ".escapeshellarg($docpath)." \"".$tmpfile.'"';
      $internalcommand = "";
    }
    if ($type=="odt") {
      $command = "";
      $internalcommand = 'OPENDOC_Odt2Text("' . addcslashes($docpath, '"\\\$') . '", "' . $tmpfile . '");';
    }
    if ($type=="ods") {
      $command = "";
      $internalcommand = 'OPENDOC_Ods2Text("' . addcslashes($docpath, '"\\\$') . '", "' . $tmpfile . '");';
    }
    if ($type=="odp") {
      $command = "";
      $internalcommand = 'OPENDOC_Odp2Text("' . addcslashes($docpath, '"\\\$') . '", "' . $tmpfile . '");';
    }
    if ($type=="docx") {
      uuse("openxml");
      $internalcommand = 'OPENXML_Docx2Text("' . addcslashes($docpath, '"\\\$') . '", "' . $tmpfile . '");';
    }
    if ($type=="xlsx" || $type=="xlsm") {
      uuse("openxml");
      $internalcommand = 'OPENXML_Xlsx2Text("' . addcslashes($docpath, '"\\\$') . '", "' . $tmpfile . '");';
    }

     if ($type=="pptx") {
      uuse("openxml");
      $internalcommand = 'OPENXML_Pptx2Text("' . addcslashes($docpath, '"\\\$') . '", "' . $tmpfile . '");';
    }
   if($command) {
      system ($command);
    } else {
      eval($internalcommand);
    }
   
    $ret = N_ReadFile ($tmpfile);
    if ($type=="xls" || $type=="ppt" || $type=="txt") {
      if ($type=="xls") {
        $ret = str_replace ("</TD>", " | ", $ret);
      }
      if ($type=="ppt") {
         $ret = str_replace (chr (hexdec("e2")).chr (hexdec("80")).chr (hexdec("9c")), "\"", $ret);
         $ret = str_replace (chr (hexdec("e2")).chr (hexdec("80")).chr (hexdec("9d")), "\"", $ret);
         $ret = str_replace (chr (hexdec("e2")).chr (hexdec("80")).chr (hexdec("99")).chr (hexdec("0b")), "'", $ret);
         $ret = str_replace (chr (hexdec("e2")).chr (hexdec("80")).chr (hexdec("98")).chr (hexdec("0b")), "'", $ret);
         // etc.
      }
      $ret = SEARCH_HTML2TEXT ($ret);
      $ret = str_replace ("Created with pptHtml", " ", $ret);
      $ret = str_replace ("Created with xlhtml", " ", $ret); 
      $ret = str_replace ($docpath, " ", $ret);

      if (N_KeepAfter ($docpath, ":")) {
        $ret = str_replace (N_KeepAfter ($docpath, ":"), " ", $ret);
      }
    }
    if (file_exists($tmpfile))
      unlink ($tmpfile);
    $ret = IMS_Accents2Ascii($ret); // convert accents (for indexing)
    return DFC_Write ($key, $ret);
  }
}

function IMS_Accents2Ascii($ret) {
  $multibyte = array(chr(195).chr(132), // A umlaut
                     chr(195).chr(164), // a umlaut
                     chr(195).chr(150), // O umlaut
                     chr(195).chr(182), // o umlaut
                     chr(195).chr(139), // E umlaut
                     chr(195).chr(171), // e umlaut
                     chr(195).chr(143), // I umlaut
                     chr(195).chr(175), // i umlaut
                     chr(195).chr(179), // o acute
                     chr(195).chr(129), // A /
                     chr(195).chr(161), // a /
                     chr(195).chr(128), // A \
                     chr(195).chr(160), // a \
                     chr(195).chr(156), // U umlaut
                     chr(195).chr(188), // u umlaut
                     chr(226).chr(128).chr(147), // streepje
                     chr(226).chr(128).chr(162), // bolletje
                     chr(194).chr(174), // copyright
                     chr(226).chr(128).chr(156), // dubbel quote links
                     chr(226).chr(128).chr(157), // dubbel quote rechts
                     chr(226).chr(128).chr(152), // enkel quote links
                     chr(226).chr(128).chr(153), // enkel quote rechts
                     chr(226).chr(128).chr(166), // ellips
                     chr(226).chr(128).chr(148), // em dash
                     chr(226).chr(128).chr(147)  // en dash
  );

  $ascii  = array(chr(196), // A umlaut
                  chr(228), // a umlaut
                  chr(214), // O umlaut
                  chr(246), // o umlaut
                  chr(203), // E umlaut
                  chr(235), // e umlaut
                  chr(207), // I umlaut
                  chr(239), // i umlaut
                  chr(243), // o acute
                  chr(193), // A /
                  chr(225), // a /
                  chr(192), // A \
                  chr(224), // a \
                  chr(220), // U umlaut
                  chr(252), // u umlaut
                  chr(45), // streepje
                  chr(42), // bolletje (sterretje
                  chr(174) ,// copyright
                  chr(34), // dubbel quote
                  chr(34), // dubbel quote
                  chr(39), // enkel quote
                  chr(39), // enkel quote
                  chr(46).chr(46).chr(46), // ellips
                  chr(45), // em dash
                  chr(45), // en dash

  );

  $ret = str_replace($multibyte, $ascii, $ret);

  return $ret;
}

function IMS_DocContent2Text ($content, $type) 
{
  uuse ("marker");
  global $myconfig;

  if ((($type=="doc" || $type=="dot") && $myconfig["antiword"]) || ($type=="ppt" && $myconfig["ppthtml"]) || 
      ($type=="pdf" && $myconfig["pdftotext"]) || 
      $type=="odt" || $type=="ods" || $type=="odp" || $type=="docx" ||
      $type=="xlsx" || $type=="xlsm" || $type=="pptx"
     ) {
    $tmpfile = N_CleanPath ("html::"."/tmp/".N_Random().".$type");
    N_WriteFile ($tmpfile, $content);
    MARKER_Remove ($tmpfile);
    $ret = IMS_DoDoc2Text ($tmpfile, $type);
    if (file_exists($tmpfile))
      unlink ($tmpfile);
  } else if ($type=="pdf") { // if !$myconfig["pdftotext"]
    $ret = SEARCH_PDF2Text ($content);
  } else if ($type=="doc" || $type=="dot" || $type=="ppt") { // if !$myconfig["antiword"]
    $ret = SEARCH_Any2Text ($content);
  } else if ($type=="rtf") {
    $ret = SEARCH_RTF2Text ($content);
  } else if ($type=="xml") { // kvd 20100824 use NO [LF: SEARCH_HTML2Text?] conversion
    // TODO: detect and convert encoding, assume UTF-8 (xml default) if no evidence to the contrary can be found
    $ret = SEARCH_Any2Text ($content); // LF: Use SEARCH_Any2Text to get rid of quotes, < > etc.
  } else if ($type=="cwk" || $type=="xls" || $type=="qxd" || $type=="fm" || $type=="eml" || $type=="txt" || $type=="msg" || $type=="php" || $type=="css" || $type=="js") { // use text mode conversion
    // TODO: decide what to do with lots of other possible file types
    $ret = SEARCH_Any2Text ($content);
  } else if ($type=="htm" || $type=="html") {
    $ret = SEARCH_HTML2TEXT ($content);
  } else if ($type=="zip") {  
    global $myconfig;  
    //write zip content to file  
    $tmpzipfile = N_CleanPath ("html::"."/tmp/".N_Random().".$type");  
    N_WriteFile ($tmpzipfile, $content);  
    $tmpfolder = N_CleanPath ("html::"."/tmp/zip".N_Random()."/");
    mkdir($tmpfolder);

    //extract comment from zip
    exec($myconfig["unzip"]." -z $docpath", $comment);  // TODO: $docpath not available here
    unset($comment[0]); //ignore first line (temporary filename of zip)  
    $zipcomment = implode(" ",$comment). " ";
 
    //unzip zip naar tmpfolder  
//    $unzipcommand = $myconfig["unzip"]." $tmpzipfile -d $tmpfolder";  
//    `$unzipcommand`;
    N_unzip( $tmpzipfile , $tmpfolder , " IMS_DocContent2Text" );
    $ret = $zipcomment . IMS_Folder2Text($tmpfolder);  
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($tmpfolder);  
    rmdir($tmpfolder);  
    if (file_exists($tmpzipfile))
      unlink ($tmpzipfile);
  } else { 
    $ret = "";
  }
  return $ret;
}


function IMS_Container2Text ( $docpath )
{
  global $myconfig;

  N_Log( 'container2text' , 'start : ' . $docpath . ' : ' . $docText );

  if( $myconfig["containersizelimitforindex"] ) 
  {
    $upperlimit = $myconfig["containersizelimitforindex"];
  } else {
    $upperlimit = 10000000;
  }

  $basename = basename( $docpath );

  $dirname = dirname( $docpath );
  $N_Tree_result = N_Tree ( $dirname . "/");

  $docText = '';

  foreach( $N_Tree_result AS $N_Tree_index => $N_Tree_value )
  {
    $docText .= $N_Tree_value['relpath'] . $N_Tree_value['filename'];
    if ( $docText!='' ) $docText.= ' --- ';
  }

  foreach( $N_Tree_result AS $N_Tree_index => $N_Tree_value )
  {
    if ( $N_Tree_value['filename'] != $basename )
    {
      N_Log( 'container2text' , 'size : ' . strlen( $docText ) . ' $N_Tree_index:' . $N_Tree_index );

      $type = strrev(N_KeepBefore (strrev($N_Tree_value['filename']), "."));

      $concatText = IMS_Doc2Text ( $N_Tree_index , $type , true );

      $calcedLength = strlen( $docText ) + strlen( $concatText );

      if ( $calcedLength <= $upperlimit )
      {
         if ( $docText!='' ) $docText .= ' ------ ';
         $docText .= $concatText;
         unset( $concatText ); // Free memory of done file before starting next.
      }
      else
      {
         N_Log( 'container2text' , 'halfway done at ' . strlen( $docText ) . ' ( upperlimit ' . $upperlimit . ', would fo overflown to ' . $calcedLength . ' with file' . $N_Tree_index . ' ):' . $docpath . ' : ' . $docText );
         return $docText;
      }
    } else
      N_Log( 'container2text' , 'skipped:' . $N_Tree_index );
  }
  N_Log( 'container2text' , 'done : ' . $docpath . ' : ' . $docText );
  return $docText;
}


function IMS_Doc2Text ($docpath, $type, $alreadyInContainer=false)
{
  global $myconfig;
  if($myconfig["filesizelimitforindex"]) {
    $upperlimit = $myconfig["filesizelimitforindex"];
  } else {
    $upperlimit = 10000000;
  }
  if (N_QuickFileSize ($docpath) > $upperlimit) {
    return ML("Document is te groot en wordt niet geindexeerd. Maximale grootte is %1", "Document is too large and will not be indexed. Maximum size is: %1", $upperlimit);
  }
  // ===== added by jg =====
  $basename = basename( $docpath );
  if ( !$alreadyInContainer && strpos (" $basename", '.imsctn.txt' ) )
     return IMS_Container2Text( $docpath );
  // ===== end added by jg =====
  N_Debug ("IMS_Doc2Text ($docpath, $type) START");
  $ret = IMS_DocContent2Text (N_SlowReadFile ($docpath), $type);
  if (function_exists('IMS_Doc2Text_Extra')) $ret = IMS_Doc2Text_Extra($docpath, $type, $ret);
  N_Debug ("IMS_Doc2Text ($docpath, $type) END");
  return $ret;
}

function IMS_Word2Text ($docpath) 
{
  $key = DFC_Key ("IMS_Word2Text ".N_SlowReadFile($docpath));
  if (DFC_Exists($key)) return DFC_Read ($key);
  global $myconfig;
  $tmpfile = N_CleanPath ("html::"."/tmp/".N_Random().".txt");
  $myconfig["antiword"];
  $command = $myconfig["antiword"]." \"".$docpath."\" > \"".$tmpfile.'"';
  system ($command);
  $ret = N_ReadFile ($tmpfile);
  if (file_exists($tmpfile))
    unlink ($tmpfile);
  return DFC_Write ($key, $ret);
}

function IMS_WordContent2Text ($content)
{
  $tmpfile = N_CleanPath ("html::"."/tmp/".N_Random().".doc");
  N_WriteFile ($tmpfile, $content);
  $ret = IMS_Word2Text ($tmpfile);
  if (file_exists($tmpfile))
    unlink ($tmpfile);
  return $ret;
}

function IMS_Folder2Text($folder) //used for reading contents of ZIP file  
{  
  $files = N_QuickTree($folder);
  $tmpfile = N_CleanPath ("html::"."/tmp/".N_Random().".txt");  
  foreach($files as $file => $extra)  
  {  
    $fileext = strtolower(strrev(N_KeepBefore (strrev($file), ".")));  
    $filecontent = IMS_Doc2Text ($file, $fileext);  
    N_AppendFile($tmpfile, ' '.$filecontent.' ');  
  }  
  $foldertext = N_ReadFile($tmpfile);  
  N_DeleteFile($tmpfile);  
  return $foldertext;  
}

function IMS_INT_MoveDown ($children, $object_id)
{ 
  $newchildren = array();
  reset ($children);
  while (list($id)=each($children)) {
    if ($id==$object_id) {
      $skip=true;
    } else {
      $newchildren[$id] = N_GUID();
      if ($skip) {
        $skip = false;
        $newchildren[$object_id] = N_GUID();
      }
    }
  }
  if ($skip) $newchildren[$object_id] = N_GUID();
  return $newchildren;
}

function IMS_MoveDown ($sitecollection_id, $site_id, $object_id)
{
  $parent_id = IMS_Parent ($sitecollection_id, $site_id, $object_id);
  if ($parent_id) {
    $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $parent_id);
    $next = "unknown";
    reset($object["children"]);
    while (list($id)=each($object["children"])) {
      if ($next=="bottom") $next = $id;
      if ($id==$object_id) $next = "bottom";

    }
    if ($next!="bottom") {
      $nextobject = &MB_Ref ("ims_".$sitecollection_id."_objects", $next);
      while ($next!="bottom" && $nextobject["preview"]!="yes" && $nextobject["published"]!="yes") { // skip deleted items
        $object["children"] = IMS_INT_MoveDown ($object["children"], $object_id);
        $next = "unknown";
        reset($object["children"]);
        while (list($id)=each($object["children"])) {
          if ($next=="bottom") $next = $id;
          if ($id==$object_id) $next = "bottom";
        }
        if ($next!="bottom") {
          $nextobject = &MB_Ref ("ims_".$sitecollection_id."_objects", $next);
        }
      }
    }
    $object["children"] = IMS_INT_MoveDown ($object["children"], $object_id);
  }
}

function IMS_INT_MoveUp ($children, $object_id) 
{
  $newchildren = array();
  reset ($children);
  $prev = "top";
  reset($children);
  while (list($id)=each($children)) {
    if ($id == $object_id) $above = $prev;

    $prev = $id;
  }
  reset ($children);
  if ($above=="top") $newchildren[$object_id] = N_GUID();
  reset($children);
  while (list($id)=each($children)) {
    if ($id==$above) $newchildren[$object_id] = N_GUID();
    if ($id!=$object_id) $newchildren[$id] = N_GUID();
  }
  return $newchildren;
}

function IMS_MoveUp ($sitecollection_id, $site_id, $object_id)
{
  $parent_id = IMS_Parent ($sitecollection_id, $site_id, $object_id);

  if ($parent_id) {
    $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $parent_id);
    $prev = "top";
    reset($object["children"]);
    while (list($id)=each($object["children"])) {
      if ($id == $object_id) $above = $prev;
      $prev = $id;
    }
    if ($above!="top") {
      $aboveobject = &MB_Ref ("ims_".$sitecollection_id."_objects", $above);
      while ($above!="top" && $aboveobject["preview"]!="yes" && $aboveobject["published"]!="yes") { // skip deleted items
        $object["children"] = IMS_INT_MoveUp ($object["children"], $object_id);
        $prev = "top";
        reset($object["children"]);
        while (list($id)=each($object["children"])) {
          if ($id == $object_id) $above = $prev;
          $prev = $id;
        }
        if ($above!="top") {
          $aboveobject = &MB_Ref ("ims_".$sitecollection_id."_objects", $above);
        }
      }
    }    
    $object["children"] = IMS_INT_MoveUp ($object["children"], $object_id);
  }
}

function IMS_VersionInternal ($sitecollection_id, $object_id, $history_id="") { 
  global $myconfig;

  $object = &IMS_AccessObject ($sitecollection_id, $object_id);

  $counter = 0; $major = 0; $minor = -1; // specs of last version
  if (is_array ($object["history"])) foreach ($object["history"] as $id => $specs) {    
    if ($specs["published"]=="yes") { // major version
      $counter++; $major++; $minor=0;
      $vers_counter[$id] = $counter;
      $vers_major[$id] = $major;
      $vers_minor[$id] = $minor;
      $lastpublished = $id;
      $lastversion = $id;
    } else if ($specs["type"]=="" || $specs["type"]=="edit" || $specs["type"]=="new") { // minor version
      $counter++; $minor++;
      $vers_counter[$id] = $counter;
      $vers_major[$id] = $major;
      $vers_minor[$id] = $minor;
      $lastversion = $id;
    }
  }  

  if ($history_id=="lastpublished") {
    $counter = $vers_counter[$lastpublished];
    $major = $vers_major[$lastpublished];
    $minor = $vers_minor[$lastpublished];
  } else if ($history_id) {
    $counter = $vers_counter[$history_id];
    $major = $vers_major[$history_id];
    $minor = $vers_minor[$history_id];
  } else {

    $counter = $vers_counter[$lastversion];
    $major = $vers_major[$lastversion];
    $minor = $vers_minor[$lastversion];
  }

  if ($myconfig[$sitecollection_id]["customversions"]) {
    eval ($myconfig[$sitecollection_id]["customversions"]);
  }
 
  return array ("version"=>$version, "major"=>$major, "minor"=>$minor, "counter"=>$counter, "maxmajor"=>$vers_major[$lastversion]);
}

function IMS_Version ($sitecollection_id, $object_id, $history_id="") { 
  $versions = IMS_VersionInternal ($sitecollection_id, $object_id, $history_id);
  return $versions["version"];
}

function IMS_MajorVersion ($sitecollection_id, $object_id, $history_id="") { 
  $versions = IMS_VersionInternal ($sitecollection_id, $object_id, $history_id);
   
  $major = $versions["major"];
  $minor = 0;  
  $version = $versions["version"];

  global $myconfig;
  if ($myconfig[$sitecollection_id]["customversions"]) {
    eval ($myconfig[$sitecollection_id]["customversions"]);
  }

  return $version;
}


function IMS_UseMetadata ($content, $siteinfo)
{
  $object = &MB_Ref ("ims_".$siteinfo["sitecollection"]."_objects", $siteinfo["object"]);
  $workflow = &MB_Ref ("shield_".$siteinfo["sitecollection"]."_workflows", $object["workflow"]);
  $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
  for ($i=1; $i<1000; $i++) {
    if ($workflow["meta"][$i]) {
      $metaspec["fields"]["meta_".$workflow["meta"][$i]] = $allfields[$workflow["meta"][$i]];

    }
  }
  $template = &MB_Ref ("ims_".$siteinfo["sitecollection"]."_templates", $object["template"]);
  for ($i=1; $i<1000; $i++) {
    if ($template ["meta"][$i]) {
      $metaspec["fields"]["meta_".$template ["meta"][$i]] = $allfields[$template ["meta"][$i]];
    }
  } 

  if (IMS_Preview()) { 
    $metadata = $siteinfo["parameters"]["preview"];
  } else {
    $metadata = $siteinfo["parameters"]["published"];
  }

  $content = IMS_TagReplace ($content, "longtitle", $metadata["longtitle"], 1);
  $content = IMS_TagReplace ($content, "shorttitle", $metadata["shorttitle"], 1);
  $content = IMS_TagReplace ($content, "keywords", $metadata["keywords"], 1);

  if (is_array ($metadata)) foreach ($metadata as $name => $value) {
    if (substr($name,0,5)=="meta_") {
      if (IMS_TagExists ($content, substr($name,5))) {
        $value = FORMS_ShowValue ($value, $metaspec["fields"][$name], $metadata, $object);
        while (IMS_TagExists ($content, substr($name,5))) {
          $content = IMS_TagReplace ($content, substr($name,5), $value);
        }
      }
    }
  }
  
  return $content;
}

function IMS_CopyTree ($supergroupname, $folder, $target)
{

//  TREE_AddObject ($targettree, $id."root");
//  $root = &TREE_AccessObject ($tree, $root_id);  

}


function IMS_MoveFolder ($supergroupname, $folder, $target, $replace=false)
// internal use only, ignores and removes security sections with inter case moving
{
  $sourcetree = &CASE_TreeRef ($supergroupname, $folder);
  $sourcetreekey = CASE_TreeKey ($supergroupname, $folder);
  if (CASE_TreeTable ($supergroupname, $folder) == "ims_trees") {
    $sourcerawid = $folder;
    $sourceprefix = "";
  } else {
    $sourcerawid = substr ($folder, strpos ($folder, ")")+1);
    $sourceprefix = substr ($folder, 0, strpos ($folder, ")")+1);
  }
  $sourceobject = &TREE_AccessObject ($sourcetree, $folder);

  $targettree = &CASE_TreeRef ($supergroupname, $target);
  $targettreekey = CASE_TreeKey ($supergroupname, $target);
  if (CASE_TreeTable ($supergroupname, $target) == "ims_trees") {
    $targetrawid = $target;
    $targetprefix = "";
  } else {
    $targetrawid = substr ($target, strpos ($target, ")")+1);
    $targetprefix = substr ($target, 0, strpos ($target, ")")+1);
  }
  $targetobject = &TREE_AccessObject ($targettree, $target); 

  if ($sourcetreekey == $targettreekey) {
    TREE_ConnectObject ($targettree, $target, $folder);
  } else {
    if ($replace) {

      $newid = $target;
    } else {
      $newid = $targetprefix.$sourcerawid;
      TREE_AddObject ($targettree, $newid);
      TREE_ConnectObject ($targettree, $target, $newid);
    }
    $newobject = &TREE_AccessObject ($targettree, $newid);
    $newobject["shorttitle"] = $sourceobject["shorttitle"];
    $newobject["longtitle"] = $sourceobject["longtitle"];
    foreach ($sourceobject["children"] as $id => $dummy)
    {
      IMS_MoveFolder ($supergroupname, $id, $newid);
    }
    $files = MB_TurboSelectQuery ("ims_".$supergroupname."_objects", array (
      '$record["directory"]' => $folder,
      '$record["published"]=="yes" || $record["preview"]=="yes"' => true
    ));
    foreach ($files as $id => $dummy) {
      $object = &MB_Ref ("ims_".$supergroupname."_objects", $id);
      $object["directory"] = $newid;
    }
    if (!CASE_RootFolder ($folder)) {
      TREE_DeleteObject($sourcetree, $folder);
    }
  }
}

function IMS_DMSPath2ID ($supergroupname, $folder_id, $relpath)
{
  if ($relpath=="/" || $relpath=="") {
    return $folder_id;
  } else {
    if (substr ($relpath, 0, 1) != "/") $relpath = "/".$relpath;
    if (substr ($relpath, strlen($relpath)-1, 1) != "/") $relpath .= "/";

    $subpath = N_KeepBefore (N_KeepAfter ($relpath, "/"), "/");
    $relpath = "/".N_KeepAfter (N_KeepAfter ($relpath, "/"), "/");
    $tree = &CASE_TreeRef ($supergroupname, $folder_id);
    $object = TREE_AccessObject ($tree, $folder_id);
    $children = $object["children"];
    if (is_array ($children)) foreach ($children as $id => $dummy) {
      $object = TREE_AccessObject ($tree, $id);
      if ($object["shorttitle"]==$subpath) return IMS_DMSPath2ID ($supergroupname, $id, $relpath);
    }
    // not found, create folder
    if (substr ($folder_id, 0, 1) == "(") {
      $id = TREE_AddObject ($tree, substr ($folder_id, 0, strpos ($folder_id, ")")+1).N_GUID());
    } else {
      $id = TREE_AddObject ($tree);
    }
    $object = &TREE_AccessObject($tree, $id);
    $object["shorttitle"] = $subpath;
    $object["longtitle"] = $subpath;
    TREE_ConnectObject ($tree, $folder_id, $id);
    return IMS_DMSPath2ID ($supergroupname, $id, $relpath);
  }
}

function IMS_IsLocked ($supergroupname, $id)
{
  $dir = "\\".$supergroupname."\\preview\\objects\\".$id."\\";
  $lockrec = MB_Ref ("ims_".$supergroupname."_objects_locks", str_replace ("\\", "__", $dir));
  if (is_array ($lockrec["users"])) foreach ($lockrec["users"] as $theuser => $time){
    if (time()-$time < 8 * 3600) {
      $lockedby = $theuser;
    }
  }

  if (!$lockedby) {
    $lockrec = MB_Ref ("ims_".$supergroupname."_objects_locks", str_replace ("\\", "_", $dir));
    if (is_array ($lockrec["users"])) foreach ($lockrec["users"] as $theuser => $time){
      if (time()-$time < 8 * 3600) {
        $lockedby = $theuser;

      }
    } 
  }
  if ($lockedby) {
    $ret .= ML ("Dit bestand wordt momenteel gewijzigd door", "This is currently eddited by");
    $ret .= " ";
    $user = MB_Ref ("shield_".IMS_SuperGroupName()."_users", $lockedby);
    if ($user["name"]) {
      $ret .= $user["name"];
    } else {
      $ret .= $lockedby;
    }
    $ret .= " (".ML("sinds","since")." " . N_VisualDate ($time, true) . ").";
  }
  return $ret;
}

function IMS_Object2Domain ($supergroupname, $object_id)
{
  $object = &IMS_AccessObject ($supergroupname, $object_id);
  $domain = getenv("HTTP_HOST");
  if (is_array($object["history"])) {
    foreach ($object["history"] as $guid => $spec) {
      if (IMS_Domain2Sitecollection($spec["http_host"])==$supergroupname) {
        $domain = $spec["http_host"];
      }
    }
  }
  if (function_exists("IMS_Object2Domain_Extra")) {
    $newdomain = IMS_Object2Domain_Extra ($supergroupname, $object_id, $domain);
    if ($newdomain) $domain = $newdomain;
  }
  return $domain;
}

function IMS_Object2Latestuser ($supergroupname, $object_id)
{
  $object = &IMS_AccessObject ($supergroupname, $object_id);
  $user = $object["allocto"];
  if (!$user && is_array($object["history"])) {
    foreach ($object["history"] as $guid => $spec) {
      if (!$user && $spec["author"]) $user = $spec["author"];
      if ($spec["when"] > $max) {
        $max = $spec["when"];
        if ($spec["author"]) $user = $spec["author"];
      }
    }
  }
  return $user;
}

function IMS_Object2Latestauthor ($supergroupname, $object_id)
{
  $object = &IMS_AccessObject ($supergroupname, $object_id);
  
  if (is_array($object["history"])) {
    foreach ($object["history"] as $guid => $spec) {
      if (!$user && $spec["author"]) $user = $spec["author"];
      if ($spec["when"] > $max) {
        $max = $spec["when"];
        if ($spec["author"]) $user = $spec["author"];
      }
    }
  }
  if (!$user) { $user = $object["allocto"];}
  return $user;
}


function IMS_KillChildren ($sitecollection_id, $site_id, $object_id)
{
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  $children = $object["children"];
  foreach ($children as $child => $dummy) {
    IMS_KillChildren ($sitecollection_id, $site_id, $child);
    IMS_Delete ($sitecollection_id, $site_id, $child);
  }
  $object["children"] = array();
}


function IMS_KillChildrenWebGen ($sitecollection_id, $site_id, $object_id)
{
  $skipped = false;
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);
  $children = $object["children"];
  if (is_array ($children)) foreach ($children as $child => $dummy) {

    // only delete children created by the webgenerator
    if (preg_match ("/.*x.*i.*/", $child)) {
       IMS_KillChildrenWebGen ($sitecollection_id, $site_id, $child);
       IMS_Delete ($sitecollection_id, $site_id, $child);
    } else {
       $skipped = true;
    }

  }
  if (!$skipped) $object["children"] = array();
}

function IMS_ReGenerateWebPage ($sitecollection_id, $site_id, $object_id, $source_dir, $source_html="789agd978dfsa98fdas")
{
  N_Debug ("IMS_ReGenerateWebPage ($sitecollection_id, $site_id, $object_id, $source_dir, ...");
  if ($source_html=="789agd978dfsa98fdas") $source_html = N_ReadFile ($source_dir."/page.html");
  if ($source_dir) {
    N_CopyDir ("html::".$sitecollection_id."/preview/objects/".$object_id, $source_dir);
  }
  N_WriteFile ("html::".$sitecollection_id."/preview/objects/".$object_id."/page.html", $source_html);
  IMS_PublishObject ($sitecollection_id, $site_id, $object_id);

  // add history entry for webgen
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);

  global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
  $time = time();
  $guid = N_GUID();
  $object["history"][$guid]["type"] = "webgen";
  $object["history"][$guid]["when"] = $time;
  $object["history"][$guid]["author"] = SHIELD_CurrentUser ($sitecollection_id);
  $object["history"][$guid]["server"] = N_CurrentServer ();
  $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
  $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
  $object["history"][$guid]["server_addr"] = $SERVER_ADDR;
}

function IMS_GenerateWebPage ($sitecollection_id, $site_id, $parent_id, $specs, $object_id="")
{
  N_Debug ("IMS_GenerateWebPage ($sitecollection_id, $site_id, $parent_id, $specs, $object_id=");
  // determine parameters
  $object = MB_Ref ("ims_".$sitecollection_id."_objects", $parent_id);

  if (!$specs["template"]) {
    $specs["template"] = $object["template"];  
  }
  if (!$specs["workflow"]) {
    $specs["workflow"] = $object["workflow"];  
  }
  $specs["editor"] = "Microsoft Word";
  $specs["site"] = $site_id; 
  $input["parent"] = $parent_id;
  $input["sitecollection"] = $sitecollection_id;
  $input["site"] = $site_id; 

  // generate page
  $object_id = IMS_NewPage ($input, $specs, $object_id);

  // fill page
  if ($specs["source_dir"]) {
    N_CopyDir ("html::".$sitecollection_id."/preview/objects/".$object_id, $specs["source_dir"]);
    if (!$specs["source_html"]) $specs["source_html"] = N_ReadFile ($specs["source_dir"]."/page.html");
  }
  N_WriteFile ("html::".$sitecollection_id."/preview/objects/".$object_id."/page.html", $specs["source_html"]);

  // publish page
  IMS_PublishObject ($sitecollection_id, $site_id, $object_id);
  $workflow = &SHIELD_AccessWorkflow ($sitecollection_id, $specs["workflow"]);
  $object = &MB_Ref ("ims_".$sitecollection_id."_objects", $object_id);

  $object["stage"] = $workflow["stages"];

  // add history entry for webgen
  global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
  $time = time();
  $guid = N_GUID();
  $object["history"][$guid]["type"] = "webgen";
  $object["history"][$guid]["when"] = $time;
  $object["history"][$guid]["author"] = SHIELD_CurrentUser ($sitecollection_id);
  $object["history"][$guid]["server"] = N_CurrentServer ();
  $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
  $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
  $object["history"][$guid]["server_addr"] = $SERVER_ADDR;

  // copy metadata from parent
  $parent = MB_Ref("ims_".$sitecollection_id."_objects", $parent_id);
  foreach ($parent["parameters"]["published"] as $key => $value) {
     if (substr($key, 0,5) == "meta_") {
        $object["parameters"]["published"][$key] = $value;
        $object["parameters"]["preview"][$key] = $value;
     }
  }

  return $object_id;
}

function IMS_GuessDomain ($supergroupname, $user_id, $application) // not for CMS !!!
{
  global $HTTP_HOST;
  $domain = MB_Load ("local_domain_hints", $supergroupname);
  if (!$domain) $domain = $HTTP_HOST;
  return $domain;
}

function IMS_StoreDomainHint ($supergroupname) // not for CMS !!!
{
  global $HTTP_HOST;
  MB_Save ("local_domain_hints", $supergroupname, $HTTP_HOST);
}


function IMS_GetDMSDocumentPath($sgn, $folder) {
   $tree = CASE_TreeRef ($sgn, $folder);
   $path = TREE_Path ($tree, $folder);
   $pathmode="all";
   $url = "/openims/openims.php?mode=dms&currentfolder=".$path[1]["id"];
   if (substr ($folder, 0, 1)=="(") {
      $case_id = substr ($folder, 0, strpos ($folder, ")")+1);
      $case = MB_Ref ("ims_".$sgn."_case_data", $case_id);
      $pathtitle .= "<a title = \"".$case["shorttitle"]."\" class=\"ims_headnav\" href=\"$url\">".(($case["longtitle"].""=="")?$case["shorttitle"]:$case["longtitle"])."</a> &gt;&gt; "; 
   }
   $pathtitle .= "<a title=\"".$path[1]["longtitle"]."\" class=\"ims_headnav\" href=\"$url\">".$path[1]["shorttitle"]."</a>"; 
   for ($i=2; $i<=count($path); $i++) {
      if ($path[$i]["id"] == $rootfolder) $pathmode="projects";
      if ($pathmode=="projects") {
         $url = "/openims/openims.php?mode=dms&submode=projects&rootfolder=$rootfolder&currentfolder=".$path[$i]["id"];
      } else {

         $url = "/openims/openims.php?mode=dms&currentfolder=".$path[$i]["id"];
      }
      $pathtitle .= " &gt; "."<a title=\"".$path[$i]["longtitle"]."\" class=\"ims_headnav\" href=\"$url\">".$path[$i]["shorttitle"]."</a>";
   }
   return strip_tags($pathtitle);
}

function IMS_UploadFilesForm_Auto($supergroupname, $mysecuritysection, $currentfolder, $currentobject = "", $config = array()) {
  // Upload form. Returns url.
  // Automatically chooses version (plain, activex or java) depending on settings and browser
  
  global $myconfig;
  // Make it possible for the calling code to (temporarily) overrule certain myconfig settings
  foreach (array(
     "multifile", 
     "uploadfileboxcount",                         
     "uploadrememberselectiondefault",             // "yes" / "no". Files will be selected by default; can be disabled by user.
     "uploadoverwritefilesdefault",                // "yes" / "no". Files will be overwritten by default; can be disabled by the user.
     "uploadalwaysenterproperties",                // "yes" / "no". User will always get a properties dialog.
     "uploadformperdocumentspecialpostcode",       // code to eval for each new document (only if the user had a properties dialog)
     "uploadformperdocumentspecialpostcodealways", // "yes" / "no". Also eval uploadformperdocumentspecialpostcode if the user did not have a properties dialog
     "uploadformmetadataprecode",                  // precode for properties dialog
     "uploadformdetectexistingcode",               // custom code to detect whether a document already exists (if overwritexisting has been checked). Available: $filename, $rawfilename. If an existing document is found, the code should set the variable $key. If your specialpostcode messes with the shorttitle or directory, your detectexistingcode should compensate.
     "uploadformworkflowfilter")                   // array of workflows. Only allow these workflows to be chosen (user will also need assignthisworkflow right). Please only use this setting in wizards, do not use in the siteconfig (visibility of the upload button will not be correct calculated, possibility of "empty" workflow list etc.)
       as $setting) {
    if (!$config[$setting]) $config[$setting] = $myconfig[$supergroupname][$setting];
  }

  // 20110324 KvD No advanced upload when cookie set
   if ($_COOKIE["ims_noadvancedupload"] != "yes" && ($myconfig[$supergroupname]["advancedupload"]=="yes" || $myconfig[$supergroupname]["advanceduploadbutnotjava"]=="yes")) {
    $url = "";
    if (!N_Macintosh()) {
      if(N_IE()) {
          $url = IMS_UploadFilesFormActiveX($supergroupname, $mysecuritysection, $currentfolder, $currentobject, $config);
      }
      if(N_Mozilla() || N_Opera()) {
        if ($myconfig[$supergroupname]["advanceduploadbutnotjava"] == "yes") {
          $url = IMS_UploadFilesForm($supergroupname, $mysecuritysection, $currentfolder, $currentobject, $config);
        } else {
          $url = IMS_UploadFilesFormJUpload($supergroupname, $mysecuritysection, $currentfolder, $currentobject, $config);
        }
      }
    }
    if (N_Macintosh()) {
      if(N_Safari()) {
        if ($myconfig[$supergroupname]["advanceduploadbutnotjava"] == "yes") {
          $url = IMS_UploadFilesForm($supergroupname, $mysecuritysection, $currentfolder, $currentobject, $config);
        } else {
          $url = IMS_UploadFilesFormJUpload($supergroupname, $mysecuritysection, $currentfolder, $currentobject, $config);
        }
      }
    }
    // nothing matches? Use default
    if(!$url) $url = IMS_UploadFilesForm($supergroupname, $mysecuritysection, $currentfolder, $currentobject, $config);
  } else {
    $url = IMS_UploadFilesForm($supergroupname, $mysecuritysection, $currentfolder, $currentobject, $config);
  }
  return $url;  
}

function IMS_UploadFilesForm($supergroupname, $mysecuritysection, $currentfolder, $currentobject = "", $config = array()) 
{
  if (!$config) {
    global $myconfig;
    $config = $myconfig[$supergroupname];
  }

  $longtitle = ML("Upload bestanden naar OpenIMS","Upload files to OpenIMS");
  $metaspec = array();
    
  // if multifile, add checkbox "Select files after uploading" to user interface and add logic
  if($config["multifile"] == "yes") {
    $enableselectfiles = true;
    $metaspec["fields"]["selectfiles"]["type"] = "yesno";
  } else {
    $enableselectfiles= false;
  }

  if($config["uploadfileboxcount"]) {
    $amount = $config["uploadfileboxcount"];
  }else {
    $amount = 10;
  }

  $metaspec["fields"]["uploadoverexisting"]["type"] = "yesno";
  $metaspec["fields"]["props"]["type"] = "yesno";   
  $metaspec["fields"]["workflow"]["type"] = "list";
  $metaspec["fields"]["publish"]["type"] = "yesno";
  $metaspec["fields"]["workflow"]["sort"] = "yes";
  $metaspec["fields"]["workflow"]["default"] = "edit-publish";
  if ($myconfig[$supergroupname]["uploaddefaultworkflow"])
    $metaspec["fields"]["workflow"]["default"] = $myconfig[$supergroupname]["uploaddefaultworkflow"];
  $wlist = MB_Query ("shield_".$supergroupname."_workflows", '$record["dms"]');
  $allowed = SHIELD_AllowedWorkflows ($supergroupname, "", $mysecuritysection);
  if ($config["uploadformworkflowfilter"]) foreach ($allowed as $allowedwf => $dummy) if (!in_array($allowedwf, $config["uploadformworkflowfilter"])) unset($allowed[$allowedwf]);
  if (is_array($wlist)) reset($wlist);
  if (is_array($wlist)) while (list($wkey)=each($wlist)) {
     if ($allowed[$wkey]) {            
        $workflow = &MB_Ref ("shield_".$supergroupname."_workflows", $wkey);
        $metaspec["fields"]["workflow"]["values"][$workflow["name"]] = $wkey;
     }
  }

  for ($i=1; $i<=$amount; $i++) {
    $metaspec["fields"]["file$i"]["type"] = "bigfile";
  }

  // 2010 01 04: CvE (VMM) config 
  $precode = '
    if($input["config"]["uploadrememberselectiondefault"] == "yes") {
      $data["selectfiles"] = "yes";
    }
    if($input["config"]["uploadoverwritefilesdefault"] == "yes") {
      $data["uploadoverexisting"] = "yes";
    }
  ';

  // 20091005 KvD maak 'workflow' vertaalbaar
  $formtemplate  = '<table>
                    <tr>
                      <td><font face="arial" size=2><b>' . ML("Workflow","Workflow") . '</b></font></td>
                      <td><font face="arial" size=2><b>[[[workflow]]]</b></font></td>
                    </tr>
                    <tr>
                    <td><font face="arial" size=2><b>'.ML("Overschrijf bestanden met dezelfde naam<br>(de al aanwezige bestanden behouden hun Workflow en Eigenschappen)", 
                      "Overwrite files with identical names<br>(the already existing files keep their Workflow and Properties)").'</b></font></td>
                    <td><font face="arial" size=2><b>[[[uploadoverexisting]]]</b></font></td>
                    </tr>';
  if ($config["uploadalwaysenterproperties"]!="yes") {
    $formtemplate .= '<tr>
                        <td><font face="arial" size=2><b>'.ML("Eigenschappen invullen", "Enter properties").'</b></font></td>
                        <td><font face="arial" size=2><b>[[[props]]]</b></font></td>
                      </tr>';
  }

  if($enableselectfiles) {

    $formtemplate .='<tr>
                      <td><font face="arial" size=2><b>'.ML("Selecteer bestanden na uploaden", "Select files after upload").'</b></font></td>
                      <td><font face="arial" size=2><b>[[[selectfiles]]]</b></font></td>
                    </tr>';
  }
  global $myconfig;
  if ($myconfig[$supergroupname]["uploaddirectpublish"] == "yes") {
    $formtemplate .='<tr>
                      <td><font face="arial" size=2><b>'.ML("Direct Definitief", "Direct publish").'</b></font></td>
                      <td><font face="arial" size=2><b>[[[publish]]]</b></font></td>
                    </tr>';
    } 

  $formtemplate .= "<tr>" . 
                      "<td colspan=2>". 
                      "<br>";



  $formtemplate .= "<table>";
  for ($i=1; $i<=$amount; $i++) {
    $formtemplate .= "<tr><td colspan=2>[[[file$i]]]</td></tr>";
  }
  $formtemplate .=    "</table>";
  $formtemplate .=    "</td>" .
                   "</tr>";
  $formtemplate .= '<tr><td colspan="4">&nbsp</td></tr>
                    <tr><td colspan="4"><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                    </table>';
  $input = array();
  $input["sitecollection_id"] = $supergroupname;
  $input["directory_id"] = $currentfolder;
  $input["currentobject"] = $currentobject;
  $input["user_id"] = SHIELD_CurrentUser ($input["sitecollection_id"]);
  $input["config"] = $config;
  $input["amount"] = $amount;

  $subdir = N_GUID();
  $uploaddir = TMP_DIR()."/".$subdir;
  $input["uploaddir"] = $uploaddir;

  $postcode = '

    uuse ("ims");
    uuse ("shield");

    for ($i=1; $i<=$input["amount"]; $i++) {
      if ($files["file$i"]["name"]) {
        $filename = $files["file$i"]["name"];
        if (N_Windows()) {
          N_CopyFile ($input["uploaddir"]."/".$filename, $files["file$i"]["tmpfilename"]);
        } else {
          N_MkDir($input["uploaddir"]);
          N_Rename($files["file$i"]["tmpfilename"], $input["uploaddir"]."/".$filename);  
        }
      }
    }
    // check if files are uploaded
    if ($filename."" == "") {
       FORMS_ShowError (ML("Foutmelding","Error"), ML("Er zijn geen bestanden geselecteerd.","No files selected."), true);
    }

    IMS_UploadFilesForm_HandleUpload_Step1($input, $data);
  ';
  $input["gotook"] = "closeme&parentgoto:".N_MyFullURL();
  $form = array();
  $form["title"] = $longtitle;
  $form["input"] = $input;
  $form["metaspec"] = $metaspec;
  $form["formtemplate"] = $formtemplate;
  $form["precode"] = $precode;
  $form["postcode"] = $postcode;
  $url = FORMS_URL ($form); 

  return $url;
}






function IMS_UploadFilesForm_HandleUpload_Step1($input, $data) {
  // $input: sitecollection_id, directory_id, user_id, amount, uploaddir, gotook. TODO: currentobject
  // $data: selectfiles, uploadoverexisting, workflow, props (choices made in the "first" screen)
  // Before calling this function, make sure $input["uploaddir"] contains the uploaded files.
  // Call this function from the postcode of the form in which the user uploads the files.
  // Depending on $config and $data, this function will either process the upload, or show a properties form.

  $sgn = $input["sitecollection_id"];
  
  // check if files are uploaded
  uuse ("tree");
  $tree = N_QuickTree ($input["uploaddir"]);
  // 20130313 KVD NSSTAT-109 Bereken limieten en aantallen vooraf
  $counttree = count($tree);
  $maxuploads = ini_get("max_file_uploads")-1;  // min een omdat anders de limiet net wel gehaald wordt
  if ($maxuploads < 250) $maxuploads = 250;     // oudere PHP versies dan 5.2.12 geven 0 terug !
  if ($counttree <= 0) {
    FORMS_ShowError (ML("Foutmelding","Error"), ML("Er zijn geen bestanden geselecteerd.","No files selected."), true);
  }
  // 20130313 KVD NSSTAT-109
  else if ($counttree >= $maxuploads) {
    FORMS_ShowError (ML("Foutmelding","Error"), ML("Er zijn meer bestanden dan het maximum [$maxuploads].","More files than the allowed amount [$maxuploads]."), true);
  }
  ///
  // check permissions if overwrite existing is on
  $overwriteall = false; 
  if($data["uploadoverexisting"]) {
    $overwriteall = true; 
    $errorstring = "";
    foreach($tree as $fullpath=>$treespecs) {
      $filename = $treespecs["filename"];
      $filename = IMS_NiceFilename ($filename,false); // Do not shorten the filename
      unset($key);
      if ($input["config"]["uploadformdetectexistingcode"]) {
        $key = N_Eval($input["config"]["uploadformdetectexistingcode"], array("filename" => $filename, "rawfilename" => $treespecs["filename"], "data" => $data, "input" => $input), "key");
      } else {
        $fileslist = MB_TurboSelectQuery ("ims_".$sgn."_objects", array (
          'IMS_NiceFilename($record["shorttitle"].".".strtolower(strrev(N_KeepBefore (strrev($record["filename"]), "."))),false)' => $filename,
          '$record["directory"]' => $input["directory_id"],
          '$record["published"]=="yes" || $record["preview"]=="yes"' => true
        ));
        // Only overwrite over an existing document if there is exactly 1 matching document. If there are more matches, show an error.
        if(count($fileslist)>1) {
          $errorstring2 .= $filename . "<br>";
        } elseif(count($fileslist)>0) {
          $key = key($fileslist);
        }
      }
      if ($key && !SHIELD_HasObjectRight ($sgn, $key, "edit", false)) {
        $errorstring .= $filename . "<br>";
        unset($key);
      }
      if (!$key) $overwriteall = false;
    }

    if($errorstring || $errorstring2) {
      MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($input["uploaddir"]);
      $error = "";
      if ($errorstring) {
        $error .= ML("Geen rechten om de volgende bestanden te bewerken:<br>","No permissions to edit the following files:<br>");
        $error .= $errorstring;
      }
      if ($errorstring2) {
        $error .= ML("Er zijn meerdere documenten met de volgende naam:","There are multiple documents with this name:") . "<br>";
        $error .= $errorstring2;
      }
      FORMS_ShowError (ML("Foutmelding", "Error"), $error, true);
    }
  }

  if ((($input["config"]["uploadalwaysenterproperties"]=="yes")||$data["props"]) && !$overwriteall) { 
    $form = IMS_UploadFilesPropertiesForm($input, $data);
    N_Redirect (FORMS_URL ($form));

  } else {
    $input["data"] = $data;
    $input["noprops"] = true; 
    IMS_UploadFilesForm_HandleUpload_Step2($input, array());
  }
}

function IMS_UploadFilesPropertiesForm($input, $data) {
  // returns form (not url)
  $form = array();
  $form["input"] = $input;        
  $form["input"]["data"] = $data;
  $workflow = &MB_Ref ("shield_".IMS_SuperGroupName()."_workflows", $data["workflow"]);
  $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
  for ($i=1; $i<1000; $i++) {
    if ($workflow["meta"][$i]) {
      $form["metaspec"]["fields"]["meta_".$workflow["meta"][$i]] = $allfields[$workflow["meta"][$i]];              
    }

  }
  $form["formtemplate"] = '<body bgcolor=#f0f0f0><br><center><table>';
  $form["formtemplate"] .= '<tr><td colspan=2><font face="arial" size=2><b>'.ML("Specificeer de metadata (voor alle nieuwe bestanden)","Specify the metadata (for all new files)").'</b></font></td></tr>';
  for ($i=1; $i<1000; $i++) {
    if ($workflow["meta"][$i]) {
      $form["formtemplate"] .= '<tr><td><font face="arial" size=2><b>'.$allfields[$workflow["meta"][$i]]["title"].':</b></font></td><td>[[[meta_'.$workflow["meta"][$i].']]]</td></tr>';
    }
  }
  $form["formtemplate"] .= '<tr><td colspan=2>&nbsp</td></tr>
                             <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                             </table></center></body>';

  if ($input["config"]["uploadformmetadataprecode"]) {
     $form["precode"] = $input["config"]["uploadformmetadataprecode"];
  }

  $form["postcode"] = '
    IMS_UploadFilesForm_HandleUpload_Step2($input, $data);
  ';

  return $form;
}

function IMS_UploadFilesForm_HandleUpload_Step2($input, $data) {
  // $data = data from properties form
  // $input["data"] = data from upload form (uploadoverexisting)

  uuse ("terra");
  $tree = N_QuickTree ($input["uploaddir"]);
  foreach ($tree as $path => $specs) {
    $id = IMS_DMSPath2ID ($input["sitecollection_id"], $input["directory_id"], $specs["relpath"]);
  }
  $specs["title"] = "UPLOAD";
  $specs["input"] = $input;
  $specs["input"]["data2"] = $data;
  if (!$specs["input"]["guidbase"]) $specs["input"]["guidbase"] = N_GUID();
  $specs["input"]["uploadoverexisting"] = $specs["input"]["data"]["uploadoverexisting"];
  $specs["list"] = $tree;
  $specs["step_code"] = '
    $path = $index;
    $specs = $value;
    $data = $input["data2"];
    $relpath = $specs["relpath"];
    $filename = IMS_NiceFilename ($specs["filename"],false);
    $ext = strtolower(strrev(N_KeepBefore (strrev($specs["filename"]), ".")));

    $documentupdated = false; // if true, do not create new document
    $sgn = $input["sitecollection_id"];
    if($input["uploadoverexisting"]) {
      $dir = IMS_DMSPath2ID ($sgn, $input["directory_id"], $relpath);

      unset($key);
      if ($input["config"]["uploadformdetectexistingcode"]) {
        $key = N_Eval($input["config"]["uploadformdetectexistingcode"], array("filename" => $filename, "rawfilename" => $specs["filename"], "data" => $input["data"], "input" => $input), "key");
      } else {
        $fileslist = MB_TurboSelectQuery ("ims_".$sgn."_objects", array (
            \'IMS_NiceFilename($record["shorttitle"].".".strtolower(strrev(N_KeepBefore (strrev($record["filename"]), "."))),false)\' => $filename,
            \'$record["directory"]\' => $dir,
            \'$record["published"]=="yes" || $record["preview"]=="yes"\' => true
        ));
        if(count($fileslist)>0) {
          $key = key($fileslist);
        }
      }

      if ($key) {
        $o = MB_Ref("ims_".$sgn."_objects",$key);
        N_CopyFile ("html::".$sgn."/preview/objects/".$key."/".$o["filename"] , $path);
        IMS_SignalObject ($sgn, $key, $input["user_id"], getenv("HTTP_HOST"));
        $documentupdated = true;
        $object_id = $key;
      }
    }

    if(!$documentupdated) {
      $shortfilename = IMS_NiceFilename($filename, true); // Should only be used for file on disk, not for title or queries for existing files etc.
      $object_id = md5 ($input["guidbase"].$specs["relpath"].$specs["filename"]);
      IMS_NewObject ($input["sitecollection_id"], "document", $object_id); 
      $object = &IMS_AccessObject ($input["sitecollection_id"], $object_id);
      $object["allocto"] = $input["user_id"];
      $object["directory"] = IMS_DMSPath2ID ($input["sitecollection_id"], $input["directory_id"], $relpath);
      $object["shorttitle"] = str_replace (".$ext", "", $specs["filename"]);
      $object["longtitle"] = str_replace (".$ext", "", $specs["filename"]);
      $object["workflow"] = $input["data"]["workflow"];
      $object["filename"] = $shortfilename;
      if ($ext=="doc") {
        $object["executable"]="winword.exe";
      } else if ($ext=="xls") {
        $object["executable"]="excel.exe";
      } else if ($ext=="htm" || $ext=="html") {
        $object["executable"]="notepad.exe";
      } else if ($ext=="ppt") {
        $object["executable"]="powerpnt.exe";
      } else {
        $object["executable"] = "auto"; // let windows determine the proper executable
      }  
      foreach ($data as $key => $value) {
        $object[$key] = $value;
      }

      N_CopyFile ("html::".$input["sitecollection_id"]."/preview/objects/".$object_id."/".$shortfilename, $path);

      if ($input["data"]["publish"]) {
        $sgn = $input["sitecollection_id"];

        $object = &MB_Ref ("ims_".$sgn."_objects", $object_id);
     	global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
    	$time = time();
        $guid = N_GUID();
        $object["history"][$guid]["type"] = "forcedpublish";
        $object["history"][$guid]["when"] = $time;
    	$object["history"][$guid]["author"] = SHIELD_CurrentUser ($sgn);
    	$object["history"][$guid]["server"] = N_CurrentServer ();
    	$object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
    	$object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
    	$object["history"][$guid]["server_addr"] = $SERVER_ADDR;

        $workflow = SHIELD_AccessWorkflow ($sgn, $object["workflow"]);
        $object["stage"] = $workflow["stages"];
        IMS_ArchiveObject ($sgn, $object_id, $input["user_id"], true);
        IMS_PublishObject ($sgn, "", $object_id); 
        EVENTS_WorkflowStageChanged($oldstage, $object["stage"], $object, $object_id);

        } else {
          IMS_ArchiveObject ($input["sitecollection_id"], $object_id, $input["user_id"], true);
        }

        $object["shorttitle"] = str_replace("_", " ", $object["shorttitle"] );
        $object["longtitle"] = str_replace("_", " ", $object["longtitle"] );

       if ($input["config"]["uploadformperdocumentspecialpostcode"]
          && (!$input["noprops"] || $input["config"]["uploadformperdocumentspecialpostcodealways"] == "yes")) {
        // Default behaviour is to process special postcode only after a properties dialog has appeared.
        // Use the setting uploadformperdocumentspecialpostcodealways="yes" to always process the postocde
        eval ($input["config"]["uploadformperdocumentspecialpostcode"]);
      }
      SEARCH_AddPreviewDocumentToDMSIndex ($input["sitecollection_id"], $object_id);
    }
'; 

  if($input["data"]["selectfiles"]) {
  $specs["step_code"] .= '
    uuse("multi");
    MULTI_DoSelect ($input["sitecollection_id"], $object_id, "' . SHIELD_CurrentUser() . '");
  ';
  }

  $specs["step_code"] .= '
    TERRA_Log ("PATH: ".$path);
    MB_Flush();
  ';

  if($input["data"]["selectfiles"]) {
    uuse("multi");
    MULTI_UnselectAll (); // unselect all files
    MB_Flush();
  }

  if (count($tree) < 6) {
    $pureinput = $specs["input"];
    $input["pureinput"] = $pureinput;
    $input["sgn"] = IMS_SuperGroupName();
    $input["usr"] = SHIELD_CurrentUser ();
    $specs["input"] = $input;
    TERRA_CreateProcess ("Multi", $specs);
  } else {
    TERRA_Multi ($specs);
    N_Redirect (FORMS_URL (array ("formtemplate"=>'
      <table>
      <tr><td><font face="arial" size=2><b>'.ML("De %1 bestanden worden in de achtergrond verwerkt","The %1 files are processed in the background", count($tree)).'</b></font></td></tr>
      <tr><td colspan=2>&nbsp</td></tr>
      <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
      </table>
    ')));
  }
}

function IMS_GetMaxUploadSize()
{
   $upload_max_filesize = ini_get('upload_max_filesize') * 1024 * 1024;
   $post_max_size = ini_get('post_max_size') * 1024 * 1024;

   $min = $upload_max_filesize;
   if ($post_max_size < $min) $min = $post_max_size;

   return $min;
}

function IMS_NiceFilename ($rawfilename, $shorten = true) {
  $maxchar = 50;
  $filename = strtolower ($rawfilename);
  $filename = preg_replace ("#[^a-z0-9.]#i", "_", $filename);
  $ext = strtolower (strrev(N_KeepBefore (strrev($filename), ".")));
  if ($shorten) {
    if (strlen($ext)>$maxchar/2) $ext = substr ($ext, 0, $maxchar/2);
    if (strlen($filename.".".$ext) > $maxchar) $filename = substr($filename, 0, ($maxchar - (strlen($ext)) - 1)) . "." . $ext;
  }
  return $filename;
}

function IMS_ImportDirs ($sgn, $folder_id, $dir)   
{   
  $dir = N_CleanPath ($dir."/");   
  if (is_dir($dir)) {   
    if ($dh = opendir($dir)) {   
        while (($entry = readdir($dh)) !== false) {   
          if (is_dir ($dir . $entry) && $entry!="." && $entry!="..") {   
            $fid = IMS_DMSPath2ID ($sgn, $folder_id, N_CleanPath($entry));   
            IMS_ImportDirs ($sgn, $fid, $dir . $entry);   
          }   
        }   
        closedir($dh);   
    }   
  }     
}

function IMS_AddHistoryComment($docid, $sgn, $comment) {
   $object = &MB_Ref("ims_".$sgn."_objects", $docid);
   $history = $object["history"];
   foreach ($history as $key => $value) {
      $last = $key;
   }
   $object["history"][$key]["comment"] .= $comment;
}


function IMS_PublishSingleStageWorkflowDocuments ($sgn) {

  // get objects to handle
  $table = "ims_".$sgn."_objects";
  $specs = array();
  $specs["select"] = array (
    '$record["objecttype"]' => "document",
    '$record["openims_flag_trigger_change_singlestageworflow"]' => "yes"
  );
  $list = MB_TurboMultiQuery($table, $specs);
  // NO $specs["value"] and NO MB_MultiLoad please!

  // get workflows with only one stage
  if (count($list)>0) {
    $wftable = "shield_".$sgn."_workflows";
    $wfspecs = array();
    $wfspecs["select"] = array (
      '$record["stages"]' => 1
    );
    $wflist = MB_TurboMultiQuery($wftable, $wfspecs);
  }

  // loop through found documents
  foreach ($list as $key => $dummy) {
    $object = &MB_Ref($table, $key);

    if ($object["objecttype"] == "document") { // extra check to prevent a race condition with new document creation
      if ((in_array($object["workflow"], $wflist)) &&
          ($object["preview"] == "yes" && $object["published"] == "no"))
      {
        N_Log("publishsinglestage", "IMS_PublishSingleStageWorkflow: publishing document $sgn $key", "old object: " . print_r(MB_MUL_Load("ims_{$sgn}_objects", $key), 1));
        IMS_PublishObject ($sgn, "", $key);
      }
      $object["openims_flag_trigger_change_singlestageworflow"] = "no";
      MB_Flush();
    }
  }
}


function IMS_UndeleteBlock()
{
  // this function has become a hook to the (uuse_)recylebin
  uuse("recyclebin");
  return RECYCLEBIN_UndeleteBlock();
}

// replace a variable string which begins with $begin and ends with $end with a string $new, inside string $str   
// if beginning or end of variable string is not found, the string is returned unaltered
// only the first occurence is replaced 
// JH   
function IMS_str_replace_var($begin, $end, $new, $str)   
{   
  $pbegin = stripos($str, $begin);
  if ($pbegin === false)
     return $str;
   
  $pend = stripos($str, $end, $pbegin + strlen($begin));   
  if ($pend === false)
     return $str;

  $newbegin = substr($str, 0, $pbegin);   
  $newend = substr($str, $pend + strlen($end));   
  
  $str = $newbegin . $new . $newend;   
  
  return $str;   
}

function IMS_AllSites ($sgn)
{
  return MB_Query ("ims_sites", '$record["sitecollection"]=="'.$sgn.'"');
}

function IMS_AllPages ($sgn, $site)
{
  return MB_TurboMultiQuery ("ims_{$sgn}_objects", array ("select" => array(
    '$object["objecttype"]' => "webpage",
    'IMS_Object2Site ("'.$sgn.'", $key)'=>$site,
    '($object["preview"]=="yes") || ($object["published"]=="yes")' => true
  )));
}

// JH 2009 - 08 - 31
// bij formulier afhandeling, mn bevestiging moest alles
// hoofdlettergevoelig zijn, hoeft nu niet meer

function IMS_MakeFieldIdsLowercase($text)
{
  $text = FORMS_ML_Filter($text);
  $l = strlen($text);
  for ($i = 0; $i < $l; ++$i)
  {
    if (substr($text, $i, 3) == "[[[" or substr($text, $i, 3) == "{{{")
    {
      for ($j = $i + 3; $j != "]" and $j != "}" and $j < $l; ++$j)
        $text[$j] = strtolower($text[$j]);
    }
  }

  return $text;     
}

function IMS_RemoveCMSComponents($text) // TODO make efficient
{
  $allfields = MB_Ref("ims_fields", IMS_SupergroupName());
  for ($i = 0; $i < strlen($text); ++$i)
  {
    if (substr($text, $i, 3) == "[[[")
    { 
      $p = $i + 3;
      $q = strpos($text, "]]]", $p);
      $l = $q - $p;
      $key = strtolower(substr($text, $p, $l));
      if (!array_key_exists($key, $allfields))
      {
        $text = substr($text, 0, $i) . substr($text, $q+3);
      }  
    } 
  }
  return $text; 
}

function IMS_ShowCMSForm($sitecollection_id, $object_id, $path, $testrun = false) {
  $object = &IMS_AccessObject ($sitecollection_id, $object_id);
  $form = array();
  $form["input"]["supergroupname"] = $sitecollection_id;
  $form["input"]["object_id"] = $object_id;
  $form["input"]["pageurl"] = N_MyFullURL();
  $form["input"]["content"] = IMS_CleanupTags (HTML_RawContentFilter (N_ReadFile ($path."page.html")));
  $form["formtemplate"] = HTML_RawContentFilter (N_ReadFile ($path."page.html"));
  $form["input"]["testrun"] = $testrun;
  $allfields = MB_Ref ("ims_fields", $sitecollection_id);
  $form["metaspec"]["fields"] = $allfields;
  $formdataguid = N_GUID();   
  $form["input"]["formdataguid"] = $formdataguid;   
  if ($testrun) $form["gotook"] = N_MyFullURL();   else  $form["gotook"] = N_MyBareURL() . "?done=$formdataguid";  
  global $new; // used in link from BPMS, disabled preview mode
  if (!$testrun && $new) $form["gotook"] .= "&new=yes"; // need to preserve "new" parameter to also disable preview mode in confirmation
  if (!$testrun && $object["form"]["precode"]) $form["precode"] = $object["form"]["precode"];
  $form["postcode"] = '
    $object = &MB_Ref ("ims_".$input["supergroupname"]."_objects", $input["object_id"]);
    
    if ($input["testrun"]) {
      $mailstart = ML ("TEST Formulier signaal", "TEST Form signal") . chr(13).chr(10);
      $mailstart .= "(".ML("Formulier is in CONCEPT ingevuld, er wordt wel een signaal verzonden maar het ingevulde formulier wordt niet opgeslagen",
                      "Form has been used in CONCEPT mode, a signal is sent but no data is stored").")" . chr(13).chr(10);
    } else {
      $mailstart = "Formulier signaal" . chr(13).chr(10);
    }
    $mailstart .= ML("Pagina", "Page").": " . N_EliminateUrlArg(N_EliminateUrlArg($input["pageurl"], "reloaddata"), "errordata")."  (".$object["parameters"]["published"]["longtitle"].")".chr(13).chr(10);
    $mailstart .= "Datum/tijd: " . N_VisualDate (time(), true).chr(13).chr(10);

    $mail = $mailstart;
    $mail_address = "";

    $attachmentname = array();
    $attachmentcontent = array();

    $postdata = Array();
    // 20120130 KvD GGDZW-11 Vanwege HTML datum veld ook day, month, year
    foreach ($_POST as $pfield => $pvalue) {
       if (substr ($pfield, 0, 6)=="field_") {
          $pkey = substr ($pfield, 6, strlen($pfield)-6);

          foreach (array("date", "hour", "minute", "day", "month", "year") AS $subkey) {
            $l = strlen($subkey)+1;
            if (substr ($pkey, 0, $l) == $subkey."_") {
              $pkey = substr ($pkey, $l, strlen($pkey)-$l);
              break;
            } 
          }
    ///
/*
          if (substr ($pkey, 0, 5)=="date_") {
            $pkey = substr ($pkey, 5, strlen($pkey)-5);
          } 
          if (substr ($pkey, 0, 5)=="hour_") {
            $pkey = substr ($pkey, 5, strlen($pkey)-5);
          } 
          if (substr ($pkey, 0, 7)=="minute_") {
            $pkey = substr ($pkey, 7, strlen($pkey)-7);
          } 
*/
          $postdata[$pkey] = $data[$pkey];
       } 
    }

    if ($object["form"]["postcode"]) eval($object["form"]["postcode"]); // 50% LF

    if (is_array($postdata)) {
       $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
       reset($postdata);
       while (list($key, $value)=each($postdata)) {
          $mtitle = $allfields[$key]["title"];
          if ($mtitle . "" == "") $mtitle = $allfields[N_KeepBefore($key,"__")]["title"];
          if ($allfields[$key]["type"] == "list") {
             $mail .= $mtitle.": ".str_replace("<br>", ",", FORMS_ShowValue($value,$key,$data).chr(13).chr(10));
          } else {
             $mail .= $mtitle.": ".N_RemoveUnicode(FORMS_ShowValue($value,$key,$data)).chr(13).chr(10);

           }
          if ($mail_address  == "") preg_replace("\'([_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*)\'ie","\$mail_address=\'\\\\1\';",$value);
      }
    }


    if (is_array($files)) {
       foreach($files as $key => $dummy) {
         if ($files[$key]["name"]) {
           $attachmentname[] = $files[$key]["name"];
           $attachmentcontent[] = $files[$key]["content"];
         }
       }
    }

    if ($input["testrun"]) {
      $subject = ML("TEST IMS Formulier signaal","TEST IMS Form signal")." (".$object["parameters"]["published"]["longtitle"].")";    
    } else {
      $subject = ML("IMS Formulier signaal", "IMS Form signal") ." (".$object["parameters"]["published"]["longtitle"].")";
    }
    $to = $object["form"]["signal"];
    if ($mail_address == "") $mail_address = $to;
    if ($mail_address  == "") $mail_address = "mailer@osict.com";

    global $myconfig;
    if  ($myconfig[$input["supergroupname"]]["advancedformsettings"] == "yes") {
      if  ($object["form"]["subjectfield"]."" != "")
        if ($input["testrun"]) {
          $subject = "TEST " . $data[$object["form"]["subjectfield"]];
        } else {
          $subject = $data[$object["form"]["subjectfield"]];
        }
      if  ($object["form"]["extratofield"]."" != "")
        $to .= ",".$data[$object["form"]["extratofield"]];
      if  ($object["form"]["ccfield"]."" != "")
        $cc = $data[$object["form"]["ccfield"]];
      if  ($object["form"]["bccfield"]."" != "")
        $bcc = $data[$object["form"]["bccfield"]]; 
    }  

    // send mail as HTML
    $sendhtml = false;
    if ($myconfig[$input["supergroupname"]]["advancedformsettings"] == "yes") {
      if ($object["form"]["htmlmail"]) {
        $htmlcontent = $input["content"];

        if (is_array($postdata)) {
           reset($postdata);
           $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
           while (list($key, $value)=each($postdata)) {
              $mtitle = $allfields[$key]["title"];
              if ($mtitle . "" == "") $mtitle = $allfields[N_KeepBefore($key,"__")]["title"];
              if ($allfields[$key]["type"] == "list") {
                 $myvalue = FORMS_ShowValue($value,$key,$data).chr(13).chr(10);
              } elseif ($allfields[$key]["type"] == "yesno") {
                 $myvalue = FORMS_ShowValue($value,$key,$data).chr(13).chr(10);
              } else {
                 $myvalue = N_RemoveUnicode(FORMS_ShowValue($value,$key,$data)).chr(13).chr(10);
              }
              $htmlcontent = str_replace("[[[".strtolower($key).":]]]", $myvalue, $htmlcontent);
          }
        }
        $pats[] = "/\[{3}.*?\]{3}/s";
        $htmlcontent = preg_replace($pats, array(), $htmlcontent);
        $mailstart = str_replace(chr(13).chr(10), "<br>",$mailstart);
        $mailstart = "<font face=\'arial\' size=\'2\'>".$mailstart."<hr><br></font>";
        $htmlcontent = $mailstart . $htmlcontent;
        $sendhtml = true;
      }
    }

    if (!$input["testrun"]) {
      $objectdatastorage = true;
      
      if ($objectdatastorage) {
        $storage = &MB_Ref ("ims_".$input["supergroupname"]."_objects_objectdata", $input["object_id"]);
        $data["datetime"] = N_VisualDate (time(), true);
        global $REMOTE_ADDR;
        $data["remote_addr"] = $REMOTE_ADDR;
        $formdataguid = $input["formdataguid"];   
        $storage[$formdataguid] = $data;   
        // uploaded files variable $files only available in form postcode it seems, so store filenames here; JH   
        if ($myconfig[$input["supergroupname"]]["formprint"] == "yes")
          foreach ($files as $filekey => $fileval)   
            $storage[$formdataguid][$filekey] = $fileval["name"];
      }
    }

    if ($sendhtml) {
      uuse ("phpmailer");
      $myattachments = array();
      foreach ($attachmentname as $key => $value) {
         $myattachments[$value] = $attachmentcontent[$key];
      }

// 20100728 BJNH-60 KvD alleen geldig email adres
      if (trim($to) && trim($mail_address))
///
        PHPMAILER_SendSingleHTMLMail($to, $to, $mail_address, $mail_address, $subject, $htmlcontent, $input["supergroupname"], $myattachments, $cc, $bcc);
   } else {
   $addhead = "";
   if ($cc)
     $addhead .= "cc: " . $cc . "\n";
   if ($bcc)
     $addhead .= "bcc: " . $bcc . "\n";
   
   N_SendMail ($mail_address, $to, $subject, $mail, $attachmentname, $attachmentcontent, false, $addhead);   
   }  

  ';
  if (function_exists("FORMS_CMS_ExtraFormSpecs")) $form = FORMS_CMS_ExtraFormSpecs($form);
  return FORMS_GenerateSuperForm ($form);  

}



function IMS_XssFilter() {
  global $myconfig;
  $sgn = IMS_SuperGroupName();

  global $RAWREQUEST, $RAWPOST, $RAWGET, $RAWCOOKIE, $IMS_XssFilter_done, $IMS_XSSFilter_scriptcategory;  // these globals are initialized in safer.php
  if ($IMS_XssFilter_done) return;

  $RAWREQUEST = $_REQUEST; // If you use these, beware that they are not autoglobal
  $RAWPOST = $_POST;
  $RAWGET = $_GET;
  $RAWCOOKIE = $_COOKIE;

  if ($myconfig[$sgn]["xssfilter"] != "yes" && $myconfig[$sgn]["xssfilter"] != "warn") {
    $IMS_XssFilter_done = true;
    return;
  }
    
  $IMS_XssFilter_done = true;  
}



function IMS_XmlSitemap_Pagelist($sgn, $site_id) {
  global $myconfig;
  $settings = $myconfig[$sgn][$site_id]["xmlsitemapsettings"];
  
  $protocol = $settings["protocol"];
  if (!$protocol && $myconfig[$sgn]["ssl_usage"] == "required") $protocol == "https";
  if (!$protocol) $protocol = "http";
  
  $root = MB_Fetch("ims_sites", $site_id, 'homepage');
  $domain = IMS_Object2Domain($sgn, $root);

  $user_id = SHIELD_CurrentUser($sgn);
  SHIELD_SimulateUser("unknown");
  
  if ($settings["unreachableorphans"]) {
    $list = array();
    $pages = IMS_AllPages ($sgn, $site_id);
    foreach ($pages as $page_id => $dummy) {
      $visible = IMS_Visible($sgn, $page_id, true) && SHIELD_HasObjectRight($sgn, $page_id, "view", false); // no forcelogon
      if ($visible) {
        $specs = IMS_XmlSitemap_GetPageSpecs($sgn, $site_id, $page_id, $protocol, $domain);
        foreach ($specs as $thespecs) {
          $list[$thespecs["loc"]] = $thespecs;
        }
      }
    }
  } else {
    $list = IMS_XmlSitemap_Pagelist_Helper($sgn, $site_id, $root, $protocol, $domain, $settings["unreachablechildren"]);
  }

  if ($myconfig[$sgn]["cookielogin"] == "yes" && !$settings["nocookieloginpage"]) {
    $loginpageurl = $myconfig[$sgn]["cookieloginsettings"]["loginpageurl"];
    if (!$loginpageurl) $loginpageurl = "/openims/login.php";
    $found = false;
    foreach ($list as $loc => $specs) {
      if (strpos($loc, $loginpageurl) !== false) { $found = true; break; }
    }
    if (!$found) {
      if ($myconfig[$sgn]["cookieloginsettings"]["loginrequireshttps"] == "yes") $protocol = "https";
      $loc = "{$protocol}://{$domain}{$loginpageurl}";
      $list[$loc] = array("loc" => $loc);
    }
  }
  
  if (function_exists('IMS_XmlSiteMap_PageList_Extra')) {
    // "loc" should be url encoded (only relevant when adding url parameters in custom code, OpenIMS bare url's should be OK), but not yet html escaped
    // "lastmod" is just a Unix timestamp at this point (not a W3C Datetime string)
    $list = IMS_XmlSiteMap_PageList_Extra($list, $sgn, $site_id, $protocol, $domain);    
  }
  
  SHIELD_SimulateUser($user_id);
  return $list;
}

function IMS_XmlSitemap_Pagelist_Helper($sgn, $site_id, $page_id, $protocol, $domain, $unreachablechildren) {
  // Please make sure to simulate user "unknown" before calling this function

  $pagelist = array();
  
  // page should be visible in site mode, without triggering a login prompt.
  $visible = IMS_Visible($sgn, $page_id, true) && SHIELD_HasObjectRight($sgn, $page_id, "view", false); // no forcelogon
  if ($visible) {
    $specs = IMS_XmlSitemap_GetPageSpecs($sgn, $site_id, $page_id, $protocol, $domain);
    foreach ($specs as $thespecs) {
      $pagelist[$thespecs["loc"]] = $thespecs;
    }
  }
  if ($visible || $unreachablechildren) {
    foreach (IMS_Children($sgn, $site_id, $page_id) as $child_id => $dummy) {
      $childlist = IMS_XmlSitemap_Pagelist_Helper($sgn, $site_id, $child_id, $protocol, $domain, $unreachablechildren);
      if ($childlist) $pagelist = array_merge($pagelist, $childlist);
    }
  }

  return $pagelist;
} 


function IMS_XmlSitemap_GetPageSpecs($sgn, $site_id, $page_id, $protocol, $domain) {
  global $myconfig;
  
  if ($myconfig["serverhasniceurl"] == "yes" && $myconfig[$sgn][$site_id]["niceurl"] == "yes" && $url = NICEURL_GetNiceUrl($sgn, $page_id)) {
    $loc = "{$protocol}://{$domain}/{$url}";
  } else {
    $loc = "{$protocol}://{$domain}/{$site_id}/{$page_id}.php";
  }
  $page = MB_Load("ims_{$sgn}_objects", $page_id);
  $lastmod = QRY_CMS_Published_v1($sgn, $page);
  
  if ($settings["priorityfield"]) $priority = $page["published"]["parameters"][$settings["priorityfield"]];
  if ($settings["changefreqfield"]) $changefreq = $page["published"]["parameters"][$settings["changefreqfield"]];

  if ($obj = MB_Load("ims_{$sgn}_xmlsitemap_".$site_id, $page_id)) {
    foreach ($obj as $flex_id => $flex_specs) {
      foreach ($flex_specs["urllist"] as $urlspecs) {
        $specs[] = array("loc" => $loc . ($urlspecs["querystring"] ? "?" . $urlspecs["querystring"] : $urlspecs["querystring"]), 
                         "lastmod" => ($urlspecs["lastmod"] ? $urlspecs["lastmod"] : $lastmod), 
                         "priority" => ($urlspecs["priority"] ? $urlspecs["priority"] : $priority),  
                         "changefreq" => ($urlspecs["changefreq"] ? $urlspecs["changefreq"] : $changefreq));
      }
    }
  } else {
    $specs[] = array("loc" => $loc, "lastmod" => $lastmod, "priority" => $priority, "changefreq" => $changefreq);
  }

  return $specs;
}

function IMS_XmlSitemap_Write($sgn, $site_id) {
  global $myconfig;
  if ($myconfig[$sgn][$site_id]["xmlsitemap"] != "yes") return;
  $settings = $myconfig[$sgn][$site_id]["xmlsitemapsettings"];

  $output =  IMS_XmlSitemap_Generate($sgn, $site_id);

  // Write sitemap
  $path = $settings["path"];
  if (!$path) $path = $site_id . "/sitemap.xml";
  N_WriteFile("html::/$path", $output);
  
  // Update robots.txt
  $sites = MB_MultiQuery ("ims_sites", array("value" => '$record["sitecollection"]'));
  $robotsold = $robotsnew = N_ReadFile("html::/robots.txt");
  $robotsnew = preg_replace('/^Sitemap:.*(\n|$)/m', '', $robotsnew);
  foreach ($sites as $site_id => $sgn) {
    if ($myconfig[$sgn][$site_id]["xmlsitemapsettings"]["robots"]) {
      $path = $myconfig[$sgn][$site_id]["xmlsitemapsettings"]["path"];
      if (!$path) $path = $site_id . "/sitemap.xml";
      if (substr($path, 0, 1) != "/") $path = "/" . $path;

      $protocol = $myconfig[$sgn][$site_id]["xmlsitemapsettings"]["protocol"];
      if (!$protocol && $myconfig[$sgn]["ssl_usage"] == "required") $protocol == "https";
      if (!$protocol) $protocol = "http";
      $root = MB_Fetch("ims_sites", $site_id, 'homepage');
      $domain = IMS_Object2Domain($sgn, $root);
      $url = "{$protocol}://{$domain}{$path}";
      
      if (substr($robotsnew, -1) != "\n") $robotsnew .= "\n";
      $robotsnew .= "Sitemap: $url\n";
    }
  }
  if ($robotsold != $robotsnew) N_WriteFile("html::/robots.txt", $robotsnew);
  
}

function IMS_XmlSitemap_Generate($sgn, $site_id) {
  global $myconfig;
  if ($myconfig[$sgn][$site_id]["xmlsitemap"] != "yes") return;
  $settings = $myconfig[$sgn][$site_id]["xmlsitemapsettings"];
  $output = '<?xml version="1.0" encoding="UTF-8"?>';
  $output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
  $list = IMS_XmlSitemap_Pagelist($sgn, $site_id);
  foreach ($list as $specs) {
    if (!$specs["loc"]) continue;
    $output .= '<url>';
    $output .= '<loc>';
    $output .= htmlspecialchars(N_HTML2UTF($specs["loc"]), ENT_QUOTES);
    $output .= '</loc>';
    if ($specs["lastmod"]) {
      $output .= '<lastmod>';
      if (preg_match('/^[+\-][0-9][0-9][0-9][0-9]$/', ($zone = strftime('%z', $specs["lastmod"])))) {
        // Check if %z gives a numeric time zone, such as +0200 (and not: "CEST")
        $zone = substr($zone,0,3) . ":" . substr($zone, 3);
        $output .= strftime('%Y-%m-%dT%H:%M:%S', $specs["lastmod"]) . $zone;
      } elseif (preg_match('/^[+\-][0-9][0-9][0-9][0-9]$/', ($zone = strftime('%Z', $specs["lastmod"])))) {
        // Check if maybe %Z gives a numeric time zone
        $zone = substr($zone,0,3) . ":" . substr($zone, 3);
        $output .= strftime('%Y-%m-%dT%H:%M:%S', $specs["lastmod"]) . $zone;
      } else {
        // Give up and omit the time (yes, if have seen a Windows machine where both %z and %Z failed)
        $output .= strftime('%Y-%m-%d', $specs["lastmod"]);
      }
      $output .= '</lastmod>';
    }
    if ($settings["priorityfield"] && ($priority = $specs["priority"])) {
      if (is_numeric($priority) && $priority > 0 && $priority <= 1) {
        $output .= '<priority>' . $priority . '</priority>';
      }
    }
    if ($settings["changefreqfield"] && ($changefreq = $specs["changefreq"])) {
      if (in_array($changefreq, array("always", "hourly", "daily", "weekly", "monthly", "yearly", "never"))) {
        $output .= '<changefreq>' . $changefreq . '</changefreq>';
      }
    }
    $output .= '</url>';
  }
  $output .= '</urlset>';
  return $output;
}

function IMS_XmlSitemap_HandleUpdate($sgn, $object_id) {
  global $xmlsitemap_hu_nesting, $myconfig;
  if ($myconfig["serverhasxmlsitemap"] != "yes") return;
  $site_id = IMS_Object2Site ($sgn, $object_id);
  if (!$xmlsitemap_hu_nesting++) { // prevent unwanted recursion
    if ($myconfig[$sgn][$site_id]["xmlsitemap"] == "yes") {
      N_AddModifyScedule ("xmlsitemap#".$sgn."#".$site_id, time()+10, '
        IMS_XmlSitemap_Write ($input["sgn"], $input["site_id"]);
      ', array ("sgn"=>$sgn, "site_id"=>$site_id));
    }
  }
  $xmlsitemap_hu_nesting--;
}

function IMS_XmlSitemap_UpdateFlexUrls($sgn, $site_id, $page_id, $flex_id) {
  // This function is normally called while generating CMS content.
  // If you call this function from elsewhere, you need to fake the correct url & siteinfo for $page_id.

  $table = "ims_{$sgn}_xmlsitemap_".$site_id;
  $obj = &MB_Ref($table, $page_id);
  //$flextime = filemtime(N_CleanPath(("html::/config/{$sgn}/flex/flex_cmsblock_{$flex_id}.ubd")));
  //$page = MB_Load("ims_{$sgn}_objects", $page_id);
  //$pagetime = QRY_CMS_Published_v1($sgn, $page);
  //N_debug("IMS_XmlSitemap_UpdateFlexUrls flextime = $flextime, pagetime = $pagetime, lastupdate = {$obj[$flex_id]["lastupdate"]}");
  //if ($flextime > $obj[$flex_id]["lastupdate"] || $pagetime > $obj[$flex_id]["lastupdate"]) {
  $urllist = FLEX_Call($sgn, "cmsblock", $flex_id, "xmlsitemap");
  if (is_array($urllist)) {
    $fullurllist = array();
    foreach ($urllist as $urlspecs) {
      if (is_array($urlspecs["querystring"])) {
        $allquerystrings = IMS_PermuteUrlParams($urlspecs["querystring"]);
        foreach ($allquerystrings as $querystring) {
          $theurlspecs = $urlspecs;
          $urlspecs["querystring"] = $querystring;
          $fullurllist[] = $urlspecs;
        }
      } else {
        $fullurllist[] = $urlspecs;
      }
    }
    if ($fullurllist != $obj[$flex_id]["urllist"]) {
      $obj[$flex_id]["urllist"] = $fullurllist;    
      $obj[$flex_id]["lastupdate"] = time();
    }          
  } else {
    unset($obj[$flex_id]);
  }
}

function IMS_PermuteUrlParams($paramlist) {
  // Return array will all possible permutations of the url parameters. 
  // Parameter names and values will be urlencoded. ? is not prepended.
  // Input:
  //   IMS_PermuteUrlParams(array("currentfolder" => array("root", "(blub)root"), 
  //                              "user"          => array("a", "b")));
  // Output:
  // Array (
  //  [0] => currentfolder=root&user=a
  //  [1] => currentfolder=root&user=b
  //  [2] => currentfolder=%28blub%29root&user=a
  //  [3] => currentfolder=%28blub%29root&user=b
  // )
  
  $result = array();
  if (count($paramlist)) {
    reset($paramlist);
    $paramname = key($paramlist);
    $paramvalues = array_shift($paramlist); // shortens $paramlist
    foreach ($paramvalues as $paramvalue) {
      $paramstring = urlencode($paramname) . "=" . urlencode($paramvalue);
      if ($paramlist) {
        $otherparams = IMS_PermuteUrlParams($paramlist);
        foreach ($otherparams as $otherparamstring) {
          $result[] = $paramstring . "&" . $otherparamstring;
        }
      } else {
        $result[] = $paramstring;
      }
    }
  }
  return $result;
}


global $IMS_CapturedHtmlHeaders, $IMS_CanCaptureHtmlHeaders, $IMS_ShownHtmlHeaders;
$IMS_CapturedHtmlHeaders = $IMS_ShownHtmlHeaders = array();
$IMS_CanCaptureHtmlHeaders = false;
  
function IMS_CaptureHtmlHeaders() {
  // Aangeroepen door IMS_GenerateDynamicPage oid, uuse_forms.php etc.
  // Calls can be nested (when cloning pages), so this function uses a stack.
  
  global $IMS_CapturedHtmlHeaders, $IMS_CanCaptureHtmlHeaders;
  
  $IMS_CanCaptureHtmlHeaders = true;
  $IMS_CapturedHtmlHeaders[] = array();
}  

function IMS_GetCapturedHtmlHeaders() {
  global $IMS_CapturedHtmlHeaders, $IMS_CanCaptureHtmlHeaders;
  
  if (is_array($IMS_CapturedHtmlHeaders)) {
    $headers = array_pop($IMS_CapturedHtmlHeaders);
    if (!$IMS_CapturedHtmlHeaders) $IMS_CanCaptureHtmlHeaders = false;
    return $headers;
  } else {
    $IMS_CanCaptureHtmlHeaders = false;
    return array();
  }
}

function IMS_MergeHtmlHeaders($page, $format = "auto") {; // HTML4 / XHTML1
  // Autodetect format
  if ($format == "auto") {
    // Dont check the entire doctype declaration, because there might be newlines between attributes
    if (strpos(substr($page, 0, 100), "-//W3C//DTD XHTML 1")) {
      $format = "XHTML1";
    } else {
      $format = "HTML4";
    }
  }

  $html = "";
  $headers = IMS_GetCapturedHtmlHeaders();
  foreach ($headers as $header) {
    // prevent duplicate (external) scripts (i.e. if the script is already loaded as part of the template)
    if ($header["type"] == "script" && $header["atts"]["src"]) {
      $regex = '!<script\s[^>]*src=[\'"]?('.preg_quote($header["atts"]["src"]).'|'.preg_quote(N_HtmlEntities($header["atts"]["src"])).')["\']?!mi';
      if (preg_match($regex, $page)) continue;
    }
    // same for external css
    if ($header["type"] == "link" && $header["atts"]["href"]) {
      $regex = '!<link\s[^>]*href=[\'"]?('.preg_quote($header["atts"]["href"]).'|'.preg_quote(N_HtmlEntities($header["atts"]["href"])).')["\']?!mi';
      if (preg_match($regex, $page)) continue;
    }
    $html .= IMS_ShowHtmlHeader($header["type"], $header["atts"], $header["content"], $format);
  }
  
  if (($p = stripos($page, '<script')) && ($q = stripos($page, '</head')) && ($p < $q)) {
    // If there is already a script inside the header, put the captured headers above it.
    // (Typical example: we captured the "load jquery" headers, but the "load fancybox" are in $page.)
    $page = substr($page, 0, $p) . $html . substr($page, $p);
  } elseif ($p = stripos($page, '</head>')) {
    $page = substr($page, 0, $p) . $html . substr($page, $p);
  } elseif (($p = stripos($page, '<script')) && ($q = stripos($page, '<body')) && ($p < $q)) {
    $page = substr($page, 0, $p) . $html . substr($page, $p);
  } elseif (stripos($page, '<head') !== false && $p = stripos($page, '<body')) {
    $page = substr($page, 0, $p) . $html . "</head>\n" . substr($page, $p);
  } elseif (($p = stripos($page, '<body')) !== false) {
    $page = substr($page, 0, $p) . "<head>\n" . $html . "</head>\n" . substr($page, $p);
  } else {
    $page = $html . $page;
  }
  return $page;
}

function IMS_ShowHtmlHeader($type, $atts = array(), $content = "", $format = "HTML4") {
  $type = strtolower($type);
  $format = strtoupper($format);
  /* Types and their attributes.
      style:  type (default "text/css"). Use $content for inline style (use "link" type for external style).
      link:   href, rel, type, media
      script: src, type (default text/javascript). For external script, use "src" atttribute. For inline script, use $content.
      meta:   name, content, http-equiv
      raw:    use $content for arbitrary HTML. attributes will be ignored.
    For all types except 'raw', any attributes not listed here will be added to the opening tag.
   */
  if ($type == "raw") {
    return $content . "\n";
  } else {
    if ($type == "meta" || $type == "link" || $type == "base") $content = false; // empty head elements (should not be closed in HTML4)
    // Default attributes
    if ($type == "style" && !$atts["type"]) $atts["type"] = "text/css";
    if ($type == "script" && !$atts["type"]) $atts["type"] = "text/javascript";
    // Escaping
    if ($type == "script" && $content && strpos($content, '<!--') === false) {
      if (substr($format,0,5) == "XHTML") {
        $content = "<!--//--><![CDATA[//><!--\n{$content}\n//--><!]]>";
      } else {
        // That CDATA stuff is so ugly, let's not do it unnecessarily
        $content = "<!--\n{$content}\n//-->";
      }
    }
    if ($type == "style" && $content && strpos($content, '<!--') === false && substr($format,0,5) == "XHTML") {
      $content = "<!--/*--><![CDATA[/*><!--*/\n{$content}\n/*]]>*/-->";
    }
    
    $html = "<$type";
    foreach ($atts as $name => $value) {
      if ($value === false) continue;
      $html .= " {$name}=\"" . N_HtmlEntities($value) . '"';
    }
    if ($content === false) {
     if (substr($format,0,5) == "XHTML") {
        $html .= " />\n";
     } else {
        $html .= ">\n";
     }
    } else {
      $html .= ">{$content}</{$type}>\n";
    }
    return $html;
  }
}

function IMS_AddHtmlHeader($type, $atts = array(), $content = "") {

  global $IMS_CapturedHtmlHeaders, $IMS_CanCaptureHtmlHeaders, $IMS_ShownHtmlHeaders;

  $headerkey = md5(serialize($atts) . $content); // deduplicate

  if ($IMS_CanCaptureHtmlHeaders) {
    end($IMS_CapturedHtmlHeaders);
    $index = key($IMS_CapturedHtmlHeaders);
    $IMS_CapturedHtmlHeaders[$index][$headerkey] = array("type" => $type, "atts" => $atts, "content" => $content);
  } else {
    if (!$IMS_ShownHtmlHeaders[$headerkey]) echo IMS_ShowHtmlHeader($type, $atts, $content);
    $IMS_ShownHtmlHeaders[$headerkey] = true;
  }
}

function IMS_AddStylesheet($url, $media=false) {
  return IMS_AddHtmlHeader("link", array("rel" => "stylesheet", "href" => $url, "type"=>"text/css", "media" => $media));
}


function IMS_HtmlDoctype() {
  // Doctype for use in OpenIMS itself etc (not CMS)
  // If your functionality requires a doctype, put the url or siteconfig setting enabling that functionality in here.
  // At some point we may even modify this fucntion to always return a doctype.
  // The "usedoctype" setting is for testing. No functionality should ever require customers to use the "usedoctype" setting.

  global $IMS_HasHtmlDoctype; // Used by N_IEQuirks function

  $doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
  global $submode, $myconfig;
  if ($submode == "treeview" || 
      $myconfig[IMS_SuperGroupName()]["usedoctype"] == "yes") {
    $IMS_HasHtmlDoctype = true;
    return $doctype;
  }

  return ""; // Default is no doctype
}

?>