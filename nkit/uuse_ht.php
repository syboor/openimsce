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



/*
   To do (may):
     - HT_RebuildEverything () aanpassen zodat deze niet meer eerst alles weg gooit (dat het kan terwijl de site live is)
     - verbeteren pagina (turbo e.d.) mee nemen in caching mechanisme
     - load truuc ook met include (getenv ("DOCUMENT_ROOT")."/nkit/nkit.php");'; 
     - zorgen dat het ook werkt als het niet lukt een bepaalde pagina te genereren
*/

uuse ("grid");
uuse ("ims");
uuse ("terra");

function HT_PeriodicUpdate () // called once every 10 minutes
{ 
  HT_UpdateSitetableCache ();
  $list = array();
  $keys = MB_AllKeys ("ims_sites");
  MB_MultiLoad ("ims_sites", $keys);
  foreach ($keys as $key)
  {
    $obj = MB_Load ("ims_sites", $key);
    $sgn = $obj["sitecollection"];
    if (HT_UseCaching ($sgn, $key)) {
      $list[$sgn] = $sgn;
    }
  }  
  foreach ($list as $sgn) {
    $pages = MB_TurboMultiQuery ("ims_".$sgn."_objects", array ("range" => array ('$object["parameters"]["published"]["from"]', time()-1800, time())));
    foreach ($pages as $page) {
      $site = IMS_Object2Site ($sgn, $page);
      HT_HandlePublish ($sgn, $site, $page);
    }
    $pages = MB_TurboMultiQuery ("ims_".$sgn."_objects", array ("range" => array ('$object["parameters"]["published"]["until"]', time()-1800, time())));
    foreach ($pages as $page) {
      $site = IMS_Object2Site ($sgn, $page);
      HT_HandlePublish ($sgn, $site, $page);
    }
  }
}

function HT_RebuildEverything ()
{
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(N_CleanPath ("html::tmp/ht/"));
  MB_Delete ("globalvars", N_CurrentServer()."::ht_ims_sites");  
  HT_UpdateSitetableCache ();
  $specs["title"] = "Rebuild Everything (high traffic)";
  
  $keys = MB_AllKeys ("ims_sites");
  $list = array();
  foreach ($keys as $key)
  {
    $obj = MB_Load ("ims_sites", $key);
    $sgn = $obj["sitecollection"];
    if (HT_UseCaching ($sgn, $key)) {
      array_push ($list, "ims_".$sgn."_objects");
    }
  }

  $specs["tables"] = $list;
  $specs["step_code"] = '
    uuse ("ht");
    if ($object["objecttype"]=="webpage") {
      $sgn = str_replace ("ims_", "", str_replace ("_objects", "", $table));
      $site = IMS_Object2Site ($sgn, $key);
      HT_UpdateCache ($sgn, $site, $key);
    }
  ';

  TERRA_Multi_Tables ($specs);
}

function HT_HandlePublish ($sgn, $site, $object_id)
{
  if (HT_UseCaching ($sgn, $site)) {
    HT_MakeDependantsDirty ($sgn, $object_id);
    HT_UpdateCache ($sgn, $site, $object_id);
  }
}

function HT_CachableDynamic ($sgn, $object_id) 
{
  if (!HT_Cachable ($sgn, $object_id)) return false;
  $object = MB_Load ("ims_".$sgn."_objects", $object_id);
  if ($object["htsetting"]=="dynamic") return true;

  global $myconfig;
  $site = IMS_Object2Site ($sgn, $object_id);
  if ($myconfig["hightraffic"]["defaultdynamic"][$sgn][$site] == "yes" && $object["htsetting"]=="") return true;

  return false;
}

function HT_Cachable ($sgn, $object_id)
{
  // page has been published
  $object = MB_Load ("ims_".$sgn."_objects", $object_id);
  if ($object["published"]!="yes") return false;

  // page is visible
  $workflow = MB_Ref ("shield_".$sgn."_workflows", $object["workflow"]);
  if ($workflow["scedule"]=="true") {
    $from = $object["parameters"]["published"]["from"];
    $until = $object["parameters"]["published"]["until"];
    if ($from>1000 && $until>1000) {
      if ((time()<$from) || (time()>$until)) return false;
    }
  }

  // caching is allowed for this site
  $site = IMS_Object2Site ($sgn, $object_id);
  if (!HT_UseCaching ($sgn, $site)) return false;

  // caching has not been disabled for this page
  if ($object["htsetting"]=="never") return false;

  return true;
}

function HT_MakeDependantsDirty ($sgn, $object_id)
{
  $obj = MB_Ref ("ims_".$sgn."_objects", $object_id);
  $list [$object_id] = $object_id;
  $site = IMS_Object2Site ($sgn, $object_id);
  $children = IMS_Children ($sgn, $site, $object_id);
  MB_MultiLoad ("ims_".$sgn."_objects", $children);
  foreach ($children as $child => $dummy) {
    $list[$child] = $child;
  }
  if ($obj["parent"]) {
    $list[$obj["parent"]] = $obj["parent"];
    $children = IMS_Children ($sgn, $site, $obj["parent"]);
    MB_MultiLoad ("ims_".$sgn."_objects", $children);
    foreach ($children as $child => $dummy) {
      $list[$child] = $child;
    }
  }
  $always = MB_TurboMultiQuery ("ims_".$sgn."_objects", array ("select"=>array('$record["htsetting"]'=>"always")));
  foreach ($always as $key => $dummy) {
    $list[$key] = $key;
  }
  unset ($list[$object_id]);
  foreach ($list as $key) {
    N_AddModifyPreciseScedule ($sgn."-".$key, time()+10 , 'uuse("ht"); HT_UpdateCache ($input["sgn"], $input["site"], $input["key"]);', array("sgn"=>$sgn, "site"=>$site, "key"=>$key));
  }
}

function HT_UseCaching ($sgn, $site)
{
  global $myconfig;
  return (!!$myconfig["hightraffic"][$sgn][$site]);
}

function HT_UpdateCache ($sgn, $site, $object_id="") 
{
  if (!$object_id) {
    $obj = MB_Load ("ims_sites", $site);
    $object_id = $obj["homepage"];
  }
  if (HT_Cachable ($sgn, $object_id)) {
    $path = "html::tmp/ht/pages/$sgn/$site/$object_id.html";
    $content = HT_GetPage ($sgn, $site, $object_id);
    if ($content) {
      N_WriteFile ($path, $content);
      N_Log ("hightraffic", "Write to cache COL:$sgn SITE:$site PAGE:$object_id");
    } else {
      N_DeleteFile ($path);
      N_Log ("hightraffic", "Remove from cache (empty) COL:$sgn SITE:$site PAGE:$object_id");
    }
    $output = IMS_SuperMixer ($sgn, $site, $object_id, "generate_static_page"); // mix.php: IMSMIX_GenerateStaticPage()
    N_WriteFile ("html::".$site."/".$object_id.".php", $output);
    $path = "html::tmp/ht/pages/$sgn/$site/$object_id/";
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure ($path);
  } else {
    $path = "html::tmp/ht/pages/$sgn/$site/$object_id/";
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure ($path);
    $path = "html::tmp/ht/pages/$sgn/$site/".$object_id.".html";
    N_DeleteFile ($path);
    N_Log ("hightraffic", "Remove from cache COL:$sgn SITE:$site PAGE:$object_id");
  }
}

function HT_UpdateSitetableCache ()
{
  $keys = MB_AllKeys ("ims_sites");
  MB_MultiLoad ("ims_sites", $keys);
  foreach ($keys as $key) {
    $all [$key] = MB_Load ("ims_sites", $key);
  }  
  $all["htversion"] = "v5";
  $stored = MB_Load ("globalvars", N_CurrentServer()."::ht_ims_sites");
  if (($all != $stored) || !N_FileExists ("html::tmp/ht/data/present.sem")) {
    foreach ($keys as $key)
    {
      $obj = MB_Load ("ims_sites", $key);
      $sgn = $obj["sitecollection"];
      $home = $obj["homepage"];
      foreach ($obj["domains"] as $dom => $dummy) {
        $dom = strtolower ($dom);
        $data = array();
        $data["sgn"] = $sgn;
        $data["site"] = $key;
        $data["home"] = $home;
        N_WriteFile ("html::tmp/ht/data/$dom.ubd", serialize ($data));
      }
    }
    MB_Save ("globalvars", N_CurrentServer()."::ht_ims_sites", $all);
    N_WriteFile ("html::tmp/ht/data/present.sem", "yes");
  }
}

function HT_GetPage ($sgn, $site, $object_id, $domain="")
{
  global $myconfig;
  if (!$domain) {
    if ($myconfig["hightraffic"]["preferreddomain"][$sgn][$site]) {
      $domain = $myconfig["hightraffic"]["preferreddomain"][$sgn][$site];
    } else {
      $obj = MB_Load ("ims_sites", $site);
      reset($obj["domains"]);
      list ($domain, $dummy) = each ($obj["domains"]);
    }
  }
  if (!$object_id) {
    $obj = MB_Load ("ims_sites", $site);
    $object_id = $obj["homepage"];
  }
  $input["object_id"] = $object_id;
  $input["sgn"] = $sgn;
  $input["site"] = $site;
  $input["domain"] = $domain;
  $result = GRID_RPC (N_CurrentServer(), 'uuse ("ht"); $output=HT_GetPage_LL ($input["sgn"], $input["site"], $input["object_id"], $input["domain"]);', $input, 1800, 3, true);
  if ($result=="ERROR") {
    N_Log ("hightraffic", "ERROR RETRIEVING PAGE COL:$sgn SITE:$site PAGE:$object_id");
    return ""; 
  }
  return $result;
}

function HT_GetPage_LL ($sgn, $site, $object_id, $domain) {
   global $ht_mode;
   global $ht_settings;
   $ht_mode = "yes";
   $ht_settings["sgn"] = $sgn;
   $ht_settings["site"] = $site;
   $ht_settings["object_id"] = $object_id;
   $ht_settings["domain"] = $domain; 

   $obj = MB_Load ("ims_".$sgn."_objects", $object_id);
   if (!($obj["published"]=="yes")) return "";

   // set command for IMS_Supermixer
   $command="generate_dynamic_page";

   // fake scriptname so IMS_SiteInfo() determines scriptname of object_id
   global $SCRIPT_NAME;
   $tmp_SCRIPT_NAME = $SCRIPT_NAME;
   $SCRIPT_NAME = $object_id;

   // fake cookie for preview (you don't want the coolbar in your e-mail)
   global $HTTP_COOKIE_VARS;
   $tmp_HTTP_COOKIE_VARS = $HTTP_COOKIE_VARS["ims_preview"];
   $HTTP_COOKIE_VARS["ims_preview"]="no";

   SHIELD_SimulateUser(base64_decode ("dWx0cmF2aXNvcg=="));
   IMS_SetSupergroupName($sgn);

   global $IMS_SiteInfo_domain; 
   $IMS_SiteInfo_domain = $domain;

   // generate html page
   $content = IMS_SuperMixer ($sgn, $site, $object_id, $command);

   // set cookie for preview to original value
   $HTTP_COOKIE_VARS["ims_preview"] = $tmp_HTTP_COOKIE_VARS;

   // set scriptname to original value
   $SCRIPT_NAME = $tmp_SCRIPT_NAME;

   return $content;
}

?>