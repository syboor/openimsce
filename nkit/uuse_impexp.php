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



uuse ("terra");

function IMPEXP_Test ()
{
  IMPEXP_Nuke ("impexptest_sites", "please");
  MB_Flush();
  SHIELD_SimulateUser (base64_decode ("dWx0cmF2aXNvcg=="));
  IMPEXP_Import_Background ("impexptest_sites", "impexptest_com", "/export/test7/export");
}

function IMPEXP_RepairAllChildren ($sgn, $siteid)
{
  MB_Flush();
  $list = MB_TurboMultiQuery ("ims_".$sgn."_objects", array ("select" => array ('$record["objecttype"]'=>"webpage")));
  foreach ($list as $id)
  {
    $object = &MB_Ref ("ims_".$sgn."_objects", $id);
    $childs = MB_TurboMultiQuery ("ims_".$sgn."_objects", array ("select" => array ('$record["parent"]'=>$id)));
    $object["children"] = $childs;
  }
}

function IMPEXP_AddWebpage ($sgn, $siteid, $colid, $pageid, $parentid, $templateid, $name, $content)
{
  $object = &IMS_AccessObject ($sgn, $pageid);
  if ($object["objecttype"]=="webpage") {
    $object["objecttype"] = "webpage";
    $object["template"] = $templateid;
    $object["workflow"] = IMPEXP_CMS_AccessWorkflow ($sgn, $colid);
    IMS_ReGenerateWebPage ($sgn, $siteid, $pageid, "", $content);
    $object["parent"] = $parentid;
  } else {
    $specs["template"] = $templateid;
    $specs["workflow"] = IMPEXP_CMS_AccessWorkflow ($sgn, $colid);
    $specs["source_html"] = $content;
    IMS_GenerateWebPage ($sgn, $siteid, $parentid, $specs, $pageid);
  }
  $object["parameters"]["preview"]["shorttitle"] = $name;
  $object["parameters"]["published"]["shorttitle"] = $name;
  $object["parameters"]["preview"]["longtitle"] = $name;
  $object["parameters"]["published"]["longtitle"] = $name;
}

function IMPEXP_AddAsset ($sgn, $colid, $assetid, $name, $ext, $rawfile)
{
  $filename = "asset.$ext";
  IMS_NewObject ($sgn, "document", $assetid); 
  $object = &IMS_AccessObject ($sgn, $assetid);
  $object["allocto"] = SHIELD_CurrentUser();
  $object["directory"] = IMPEXP_DMS_AccessFolder ($sgn, $colid);
  $object["shorttitle"] = $name;
  $object["workflow"] = IMPEXP_DMS_AccessWorkflow ($sgn, $colid);
  $object["filename"] = $filename;
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
  N_CopyFile ("html::".$sgn."/preview/objects/".$assetid."/".$filename, $rawfile);
  IMS_ArchiveObject ($sgn, $assetid, SHIELD_CurrentUser(), true);
  SEARCH_AddPreviewDocumentToDMSIndex ($sgn, $assetid);
  IMS_PublishObject ($sgn, "", $assetid);
  $workflow = &SHIELD_AccessWorkflow ($sgn, $object["workflow"]);
  $object["stage"] = $workflow["stages"];
}

function IMPEXP_InitDirXML ($path)
{
  $path = N_CleanPath ($path);
  return dir($path);
}

function IMPEXP_NextDirXML ($handle)
{
  while (false !== ($entry = $handle->read())) {
    if (substr ($entry, strlen($entry)-4, 4) == ".xml") {
      return $entry;
    }
  }
  return null;
}

function IMPEXP_ReadWDDX ($filename) 
{
  $filename = N_CleanPath ($filename);
  $data = N_ReadFile ($filename);
  $data = str_replace (" />", "/>", $data);
  $data = str_replace ("<number/>", "<number>0</number>", $data);
  $data = str_replace ("<string/>", "<string></string>", $data);
  return wddx_deserialize ($data);
}

function IMPEXP_CMS_AccessWorkflow ($sgn, $id, $name="")
{
  $object = &MB_Ref ("local_impexp_cms_collections", $id);
  if ($name) $object["workflow"] = $name;
  $workflow = &SHIELD_AccessWorkflow ($sgn, $id);
  if (!$workflow) {
      $workflow = array(); 
      $workflow["name"] = $name;
      $workflow["stages"] = 3;
      $workflow["cms"] = true;      
      $workflow["dms"] = false;      
      $workflow["wms"] = false;      
      $workflow["alloc"] = true;      

      $workflow[1]["name"] = "Nieuw";

        $workflow[1]["stageafteredit"] = 2;
        $workflow[1]["edit"]["webmasters"] = "x";
        $workflow[1]["edit"]["editors"] = "x";

        $workflow[1]["#Publiceren"] = 3;
        $workflow[1]["changestage"]["#Publiceren"]["webmasters"] = "x";
        $workflow[1]["changestage"]["#Publiceren"]["publishers"] = "x";

      $workflow[2]["name"] = "Gewijzigd";

        $workflow[2]["stageafteredit"] = 2;
        $workflow[2]["edit"]["webmasters"] = "x";
        $workflow[2]["edit"]["editors"] = "x";

        $workflow[2]["#Publiceren"] = 3;
        $workflow[2]["changestage"]["#Publiceren"]["webmasters"] = "x";
        $workflow[2]["changestage"]["#Publiceren"]["publishers"] = "x";

      $workflow[3]["name"] = "Gepubliceerd";

        $workflow[3]["stageafteredit"] = 2;
        $workflow[3]["edit"]["webmasters"] = "x";
        $workflow[3]["edit"]["editors"] = "x";

      $workflow["rights"]["view"]["everyone"] = "x";
      $workflow["rights"]["delete"]["publishers"] = "x";
      $workflow["rights"]["delete"]["webmasters"] = "x";
      $workflow["rights"]["move"]["publishers"] = "x";
      $workflow["rights"]["move"]["webmasters"] = "x";
      $workflow["rights"]["assignthisworkflow"]["publishers"] = "x";
      $workflow["rights"]["assignthisworkflow"]["webmasters"] = "x";
      $workflow["rights"]["assignthisworkflow"]["editors"] = "x";
      $workflow["rights"]["removethisworkflow"]["webmasters"] = "x";
      $workflow["rights"]["newpage"]["editors"] = "x";
      $workflow["rights"]["newpage"]["webmasters"] = "x";
  }
  return $id;
}

function IMPEXP_DMS_AccessFolder ($sgn, $id, $name="")
{
  if (!$name) {
    $object = &MB_Ref ("local_impexp_dms_collections", $id);
    $name = $object["foldername"];
  } else {
    $object = &MB_Ref ("local_impexp_dms_collections", $id);
    $object["foldername"] = $name;
  }
  return IMS_DMSPath2ID ($sgn, "root", "/CMS Bestandsbeheer/$name");
}

function IMPEXP_DMS_AccessWorkflow ($sgn, $id, $name="") // workflow and directory
{
  if (!$name) {
    $object = &MB_Ref ("local_impexp_dms_collections", $id);
    $name = $object["workflowname"];
  } else {
    $object = &MB_Ref ("local_impexp_dms_collections", $id);
    $object["workflowname"] = $name;      
    $object["foldername"] = str_replace ("/", "-", $name);
  }
  $workflow = &SHIELD_AccessWorkflow ($sgn, $id);
  if (!$workflow) {
      $workflow = array(); 
      $workflow["name"] = $name;
      $workflow["stages"] = 3;
      $workflow["cms"] = false;      
      $workflow["dms"] = true;      
      $workflow["wms"] = false;      
      $workflow["alloc"] = true;      

      $workflow[1]["name"] = "Nieuw";

        $workflow[1]["stageafteredit"] = 2;
        $workflow[1]["edit"]["webmasters"] = "x";
        $workflow[1]["edit"]["editors"] = "x";

        $workflow[1]["#Publiceren"] = 3;
        $workflow[1]["changestage"]["#Publiceren"]["webmasters"] = "x";
        $workflow[1]["changestage"]["#Publiceren"]["publishers"] = "x";

      $workflow[2]["name"] = "Gewijzigd";

        $workflow[2]["stageafteredit"] = 2;
        $workflow[2]["edit"]["webmasters"] = "x";
        $workflow[2]["edit"]["editors"] = "x";

        $workflow[2]["#Publiceren"] = 3;
        $workflow[2]["changestage"]["#Publiceren"]["webmasters"] = "x";
        $workflow[2]["changestage"]["#Publiceren"]["publishers"] = "x";

      $workflow[3]["name"] = "Gepubliceerd";

        $workflow[3]["stageafteredit"] = 2;
        $workflow[3]["edit"]["webmasters"] = "x";
        $workflow[3]["edit"]["editors"] = "x";

      $workflow["rights"]["view"]["everyone"] = "x";
      $workflow["rights"]["delete"]["publishers"] = "x";
      $workflow["rights"]["delete"]["webmasters"] = "x";
      $workflow["rights"]["move"]["publishers"] = "x";
      $workflow["rights"]["move"]["webmasters"] = "x";
      $workflow["rights"]["assignthisworkflow"]["publishers"] = "x";
      $workflow["rights"]["assignthisworkflow"]["webmasters"] = "x";
      $workflow["rights"]["assignthisworkflow"]["editors"] = "x";
      $workflow["rights"]["removethisworkflow"]["webmasters"] = "x";
      $workflow["rights"]["newpage"]["editors"] = "x";
      $workflow["rights"]["newpage"]["webmasters"] = "x";
  }
  return $id;
}

function IMPEXP_Nuke ($sgn, $please) // delete all pages, documents and document templates, keep the rest (users, cms templates etc.) intact
{
  if ($please!="please") N_DIE ("IMPEXP_Nuke: say please!");
  if ($sgn!="impexptest_sites") N_DIE ("IMPEXP_Nuke: not allowed!");
  $sites = MB_Query ("ims_sites", '$record["sitecollection"]=="'.$sgn.'"');
  foreach ($sites as $site) {
    echo "Deleting php files ($site)<br>"; N_Flush (1);
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(getenv("DOCUMENT_ROOT") . "/$site/");

    $index = $sgn."#".$site;
    echo "Deleting search index $index<br>"; N_Flush (1);
    SEARCH_NukeIndex ($index, "please");

  }

  $index = "$sgn"."#previewdocuments";
  echo "Deleting search index $index<br>"; N_Flush (1);
  SEARCH_NukeIndex ($index, "please");

  $index = "$sgn"."#publisheddocuments";
  echo "Deleting search index $index<br>"; N_Flush (1);
  SEARCH_NukeIndex ($index, "please");

  echo "Deleting files (objects)<br>"; N_Flush (1);
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(getenv("DOCUMENT_ROOT") . "/$sgn/objects/");

  echo "Deleting files (preview)<br>"; N_Flush (1);
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(getenv("DOCUMENT_ROOT") . "/$sgn/preview/objects/");

  echo "Deleting objects (xml)<br>"; N_Flush (1);
  MB_DeleteTable ("ims_".$sgn."_objects");
}


function IMPEXP_Import ($sgn, $imssiteid, $dir)
{
  $handle = IMPEXP_InitDirXML ($dir);
  while ($manifest = IMPEXP_NextDirXML ($handle)) {
    $manifest = IMPEXP_ReadWDDX ($dir."/".$manifest);
    $siteid = $manifest["id"];
    IMS_SetHomepage ($imssiteid, $manifest["homepageid"]);
    foreach ($manifest["pagecollections"] as $pcolid => $pcolspecs) {
      IMPEXP_CMS_AccessWorkflow ($sgn, $pcolid, $pcolspecs["name"]);
      $phandle = IMPEXP_InitDirXML ($dir."/".$siteid."/pagecollections/".$pcolid);
      while ($page = IMPEXP_NextDirXML ($phandle)) {
        ++$ctr;
        echo "<b>".$ctr."</b> "; N_Flush();
        $content = N_ReadFile (str_replace (".xml", ".html", $dir."/".$siteid."/pagecollections/".$pcolid."/".$page));
        $page = IMPEXP_ReadWDDX ($dir."/".$siteid."/pagecollections/".$pcolid."/".$page);
        IMPEXP_AddWebpage ($sgn, $imssiteid, $pcolid, $page["id"], $page["cms_parentid"], "impexptest_com_homepage_template", $page["cms_title"], $content);
        $object = &MB_Ref ("ims_".$sgn."_objects", $page["id"]);
        $object["sogetixml"] = $page;
      }
    }
/*
    foreach ($manifest["assetcollections"] as $acolid => $assetspecs) {
      IMPEXP_DMS_AccessWorkflow ($sgn, $acolid, $assetspecs["name"]);
      $ahandle = IMPEXP_InitDirXML ($dir."/".$siteid."/assetcollections/".$acolid);
      while ($asset = IMPEXP_NextDirXML ($ahandle)) {
        ++$ctr;
        echo "<b>".$ctr."</b> "; N_Flush();
        $rawfile = str_replace (".xml", ".bin", $dir."/".$siteid."/assetcollections/".$acolid."/".$asset);
        $asset = IMPEXP_ReadWDDX ($dir."/".$siteid."/assetcollections/".$acolid."/".$asset);
        IMPEXP_AddAsset ($sgn, $acolid, $asset["id"], $asset["title"], $asset["extension"], $rawfile);
        $object = &MB_Ref ("ims_".$sgn."_objects", $asset["id"]);
        $object["sogetixml"] = $asset;
      }
    }
*/
  }
  IMPEXP_RepairAllChildren ($sgn, $imssiteid);
}

function IMPEXP_Import_Background ($sgn, $imssiteid, $dir)
{
  $handle = IMPEXP_InitDirXML ($dir);
  echo "Scanning...<br>"; N_Flush();
  $specs["input"]["sgn"] = $sgn;
  $specs["input"]["imssiteid"] = $imssiteid;
  $specs["input"]["dir"] = $dir;
  while ($manifest = IMPEXP_NextDirXML ($handle)) {
    $manifest = IMPEXP_ReadWDDX ($dir."/".$manifest);
    $siteid = $manifest["id"];
    IMS_SetHomepage ($imssiteid, $manifest["homepageid"]);
    foreach ($manifest["pagecollections"] as $pcolid => $pcolspecs) {
      IMPEXP_CMS_AccessWorkflow ($sgn, $pcolid, $pcolspecs["name"]);
      $phandle = IMPEXP_InitDirXML ($dir."/".$siteid."/pagecollections/".$pcolid);
      while ($page = IMPEXP_NextDirXML ($phandle)) {
        $list[++$ctr] = array (
          "type" => "page", 
          "siteid" => $siteid, 
          "pcolid" => $pcolid, 
          "page" => $page
        );
      }
    }
    foreach ($manifest["assetcollections"] as $acolid => $assetspecs) {
      IMPEXP_DMS_AccessWorkflow ($sgn, $acolid, $assetspecs["name"]);
      $ahandle = IMPEXP_InitDirXML ($dir."/".$siteid."/assetcollections/".$acolid);
      while ($asset = IMPEXP_NextDirXML ($ahandle)) {
        $list[++$ctr] = array (
          "type" => "asset", 
          "siteid" => $siteid, 
          "sgn" => $sgn, 
          "acolid" => $acolid, 
          "asset" => $asset
        );
      }
    }
  }
  MB_Flush();
  echo "Found $ctr objects.<br>"; N_Flush();
  $specs["list"] = $list;
  $specs["step_code"] = '
    uuse ("impexp");
    if ($value["type"]=="page") {
      $sgn = $input["sgn"];
      $imssiteid = $input["imssiteid"];
      $dir = $input["dir"];
      $siteid = $value["siteid"];
      $pcolid = $value["pcolid"];
      $page = $value["page"];
      $content = N_ReadFile (str_replace (".xml", ".html", $dir."/".$siteid."/pagecollections/".$pcolid."/".$page));
      $page = IMPEXP_ReadWDDX ($dir."/".$siteid."/pagecollections/".$pcolid."/".$page);
      IMPEXP_AddWebpage ($sgn, $imssiteid, $pcolid, $page["id"], $page["cms_parentid"], "impexptest_com_homepage_template", $page["cms_title"], $content);
      $object = &MB_Ref ("ims_".$sgn."_objects", $page["id"]);
      $object["sogetixml"] = $page;
    } else { // asset
      $sgn = $input["sgn"];
      $imssiteid = $input["imssiteid"];
      $dir = $input["dir"];
      $siteid = $value["siteid"];
      $acolid = $value["acolid"];
      $asset = $value["asset"];    
      $rawfile = str_replace (".xml", ".bin", $dir."/".$siteid."/assetcollections/".$acolid."/".$asset);
      $asset = IMPEXP_ReadWDDX ($dir."/".$siteid."/assetcollections/".$acolid."/".$asset);
      IMPEXP_AddAsset ($sgn, $acolid, $asset["id"], $asset["title"], $asset["extension"], $rawfile);
      $object = &MB_Ref ("ims_".$sgn."_objects", $asset["id"]);
      $object["sogetixml"] = $asset;
    }
  ';
  $specs["exit_code"] = '
    uuse ("impexp");
    echo "<br>"; 
    $sgn = $input["sgn"];
    $imssiteid = $input["imssiteid"];
    IMPEXP_RepairAllChildren ($sgn, $imssiteid);    
  ';
  TERRA_MultiList($specs);
  echo "Background process started (<a target=\"_blank\" href=\"/openims/openims.php?mode=admin&submode=maint&action=sysproc\">show</a>)<br>";
}

?>