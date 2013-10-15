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
uuse ("shield");
uuse ("files");


function MULTI_ShortcutAll($tofolder)
{
  $multi = MULTI_Load_AutoShortcuts();
  foreach ($multi as $key => $dummy) {
    IMS_NewDocumentShortcut (IMS_SuperGroupName(), $tofolder, $key);
  }  
}

function MULTI_PermalinkAll($tofolder)
{
  $multi = MULTI_Load_AutoShortcuts();
  
  foreach ($multi as $key => $dummy) {
    IMS_NewDocumentPermalink (IMS_SuperGroupName(), $tofolder, $key);
  }
}

function MULTI_ShortcutAll_old($tofolder)
{
  $multi = MULTI_Load_AutoShortcuts();
  foreach ($multi as $key => $dummy) {
    if (SHIELD_HasObjectRight (IMS_SuperGroupName(), $key, "move")) {
      IMS_NewDocumentShortcut (IMS_SuperGroupName(), $tofolder, $key);
    }
  }  
}


function MULTI_DeleteAll($shortcutmode)
{  
  global $myconfig;
  $errors = array();
  if ($shortcutmode=="deleteshortcuts") {
    $multi = MULTI_Load_ManualShortcuts();
  } else if ($shortcutmode=="deleteconnectedfiles") {
    $multi = MULTI_Load_AutoShortcuts();
  } else { // skipshortcuts
    $multi = MULTI_Load_SkipShortcuts();
  }

  foreach ($multi as $key => $dummy) {
    $objectbase = MB_LOAD("ims_".IMS_SuperGroupName()."_objects", $key);
    if ( $myconfig[IMS_SupergroupName()]["prevent_delete_files_with_shortcut"]=="yes" && count( FILES_documentShortcuts(IMS_SuperGroupName(), $key) ) > 0 )
    {
      $errors[$key] =  ML ("Heeft snelkoppelingen","Has shortcuts");
    } else if (FILES_IsShortcut (IMS_SuperGroupName(), $key)) {
     $objectbaseshortcut = MB_LOAD("ims_".IMS_SuperGroupName()."_objects", FILES_Base (IMS_SuperGroupName(), $key));
      if (SHIELD_HasObjectRight (IMS_SuperGroupName(), FILES_Base (IMS_SuperGroupName(), $key), "delete") ||
           SHIELD_HasObjectRight (IMS_SuperGroupName(), FILES_Base (IMS_SuperGroupName(), $key), "deleteconcept") && $objectbaseshortcut["published"] =="no") 
        {
        if (!($locked=IMS_IsLocked (IMS_SuperGroupName(), FILES_Base (IMS_SuperGroupName(), $key)))) {
          IMS_Delete (IMS_SuperGroupName(), "", $key);
        } else {
          $errors[$key] = $locked;
        }
      } else {
        $errors[$key] = ML ("Niet voldoende rechten","Insufficient access rights");
      }
    } else {
      if (SHIELD_HasObjectRight (IMS_SuperGroupName(), $key, "delete") || 
           SHIELD_HasObjectRight (IMS_SuperGroupName(),$key, "deleteconcept") && $objectbase["published"] == "no") {
        if (!($locked=IMS_IsLocked (IMS_SuperGroupName(), $key))) {
          IMS_Delete (IMS_SuperGroupName(), "", $key);
        } else {
          $errors[$key] = $locked;
        }
      } else {
        $errors[$key] = ML ("Niet voldoende rechten","Insufficient access rights");
      }
    }
  }  
  return $errors;
}

function MULTI_CopyAll($tofolder, $shortcutmode)
{
  if ($shortcutmode=="copyshortcuts") {
    $multi = MULTI_Load_ManualShortcuts();
  } else if ($shortcutmode=="copyconnectedfiles") {
    $multi = MULTI_Load_AutoShortcuts();
  } else { // skipshortcuts
    $multi = MULTI_Load_SkipShortcuts();
  }
  foreach ($multi as $key => $dummy) {
    if (FILES_IsShortcut (IMS_SuperGroupName(), $key)) {
      IMS_NewDocumentShortcut (IMS_SuperGroupName(), $tofolder, FILES_Base (IMS_SuperGroupName(), $key));
    } else {
      $sourceobject = &MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $key);
      $id = IMS_NewDocumentObject (IMS_SuperGroupName(), $tofolder);
      $object = &IMS_AccessObject (IMS_SuperGroupName(), $id);
      N_CopyDir ("html::".IMS_SuperGroupName()."/preview/objects/".$id."/", "html::".IMS_SuperGroupName()."/preview/objects/".$key."/");
      $object["shorttitle"] = $sourceobject["shorttitle"];
      $object["longtitle"] = $sourceobject["longtitle"];
      $object["workflow"] = $sourceobject["workflow"];
      $object["executable"] = $sourceobject["executable"];
      $object["filename"] = $sourceobject["filename"];
      $object["dynmeta"] = $sourceobject["dynmeta"];
      foreach ($sourceobject as $key => $value) {
        if (strpos (" ".$key, "meta_")) {
          $object[$key] = $sourceobject[$key];
        }
      }
      $object["directory"] = $tofolder;
      // jh 27-9-2010 for ctgb to avoid duplicate doc nr's
      if (function_exists("MULTI_MultiCopySpecialCode"))
        MULTI_MultiCopySpecialCode($object);
      IMS_ArchiveObject (IMS_SuperGroupName(), $id, SHIELD_CurrentUser (IMS_SuperGroupName()), true);
      SEARCH_AddPreviewDocumentToDMSIndex (IMS_SuperGroupName(), $id);
    }
  }
}

function MULTI_MoveAll($tofolder, $shortcutmode, $remark="")
{
  $errors = array();
  if ($shortcutmode=="moveshortcuts") {
    $multi = MULTI_Load_ManualShortcuts();
  } else if ($shortcutmode=="moveconnectedfiles") {
    $multi = MULTI_Load_AutoShortcuts();
  } else { // skipshortcuts
    $multi = MULTI_Load_SkipShortcuts();
  }
  foreach ($multi as $key => $dummy) {    
    if (FILES_IsShortcut (IMS_SuperGroupName(), $key)) {
      if (SHIELD_HasObjectRight (IMS_SuperGroupName(), FILES_Base (IMS_SuperGroupName(), $key), "move")) {
        if (!($locked=IMS_IsLocked (IMS_SuperGroupName(), FILES_Base (IMS_SuperGroupName(), $key)))) {
          $object = &IMS_AccessObject (IMS_SuperGroupName(), $key);
          $object["directory"] = $tofolder;
          IMS_RefreshShortcut (IMS_SuperGroupName(), $key);
        } else {
          $errors[$key] = $locked;
        }
      } else {
        $errors[$key] = ML ("Niet voldoende rechten","Insufficient access rights");
      }
    } else {
      if (SHIELD_HasObjectRight (IMS_SuperGroupName(), $key, "move")) {
        if (!($locked=IMS_IsLocked (IMS_SuperGroupName(), $key))) {
          $object = &MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $key);
          // ###
          global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
          $time = time();
          $guid = N_GUID();
          $object["history"][$guid]["type"] = "move";
          $object["history"][$guid]["when"] = $time;
          $object["history"][$guid]["author"] = SHIELD_CurrentUser (IMS_SuperGroupName());
          $object["history"][$guid]["remark"] = $remark;
          $object["history"][$guid]["server"] = N_CurrentServer ();
          $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
          $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
          $object["history"][$guid]["server_addr"] = $SERVER_ADDR;
          $object["history"][$guid]["from"] = IMS_GetDMSDocumentPath(IMS_SuperGroupName(), $object["directory"]);
          $object["history"][$guid]["to"]   = IMS_GetDMSDocumentPath(IMS_SuperGroupName(), $tofolder); 
          $object["directory"] = $tofolder;
        } else {
          $errors[$key] = $locked;
        }
      } else {
        $errors[$key] = ML ("Niet voldoende rechten","Insufficient access rights");
      }
    }
  }  
  return $errors;
}

function MULTI_DownloadAll()
{
  global $myconfig;
  if ($myconfig[IMS_SuperGroupName()]["imstrans"]) {
    $imstrans = $myconfig[IMS_SuperGroupName()]["imstrans"];
    if (substr ($imstrans, strlen($imstrans)-1, 1)=="\\") {
      $imstrans = substr ($imstrans, 0, strlen($imstrans)-1);
    }
  } else {
    $imstrans = "c:\\imstrans";
  }
  uuse ("dhtml");
  $commands = array();
  $ctr = 1;

  if($myconfig[IMS_SuperGroupName()]["multidownloadallowsdirselection"] == "yes") {
    $commands[$ctr . "_command"] = "selectdirectoryorexit";
    $commands[$ctr . "_title"] = ML("Selecteer map voor download", "Select folder for download");
    $ctr++;
  }

  if($myconfig[IMS_SuperGroupName()]["multidownloadallowsdirselection"] != "yes") {
    $commands[$ctr . "_command"] = "deletedir";
    $commands[$ctr . "_dir"] = $imstrans;
    $ctr++;
  }

  $multi = MULTI_Load_AutoShortcuts();
  foreach ($multi as $key => $specs) {
    // if "multidownloadtoonefolder" == yes download to one folder (do not create tree)
    if($myconfig[IMS_SuperGroupName()]["multidownloadtoonefolder"]=="yes") {
      $thepath[$key] = "\\";
    } else {
      $object = MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $key);
      $currentfolder = $object["directory"];
      $tree = CASE_TreeRef (IMS_SuperGroupName(), $currentfolder);
      $path = TREE_Path ($tree, $currentfolder);
      $pathtitle = "\\".FORMS_ML_Filter($path[1]["shorttitle"]); 
      for ($i=2; $i<=count($path); $i++) {
        $pathtitle .= "\\".FORMS_ML_Filter($path[$i]["shorttitle"]);
      }
      $pathtitle.="\\";
      $thepath[$key] = $pathtitle;
    }
  }
  $retry = true;
  while ($retry) {
    foreach ($thepath as $key => $path) $somepath = $path;
    $first = "\\".N_KeepBefore (N_KeepAfter ($somepath, "\\"), "\\")."\\";
    foreach ($thepath as $key => $path) {
      if (substr ($path, 0, strlen($first))!=$first) $retry = false;
    }
    if ($retry) {
      foreach ($thepath as $key => $path) {
        $thepath2[$key] = substr ($path, strlen($first)-1);
      }
      $thepath = $thepath2;
    }    
  }

  //KM: filter $thepath zodat /:*?"<>| tekens geen problemen geven in mapnamen.
  $illegal = array("/", ":", "*", "?", "\"", "<", ">", "|");
  $thepath = str_replace($illegal, "_", $thepath);

  foreach ($multi as $key => $specs) {
    $object = MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $key);
    $doc = FILES_TrueFileName (IMS_SuperGroupName(), $key, "preview");
    $newname = preg_replace ("'[^A-Za-z0-9.]'i", "_", FORMS_ML_Filter($object["shorttitle"])); 
    // this part checks for duplicate names, even if files are in different directories.
    // do not change this behaviour! "multidownloadtoonefolder" depends on this behaviour
    if ($used[$newname]) {
      for ($i=2; $i<1000; $i++) {
        if (!$used[$newname."_".$i]) {
          $newname = $newname."_".$i;
          $i = 1001;
        }
      }
    }
    $used[$newname] = "*";
    $thedoctype = FILES_FileType (IMS_SuperGroupName(), $key);
    if ($thedoctype=="imsctn.txt") {
      $path = "\\".IMS_SuperGroupName()."\\preview\\objects\\".$key."\\";
      $commands["$ctr"."_command"] = "download";
      $commands["$ctr"."_dir"] = $path;
      $commands["$ctr"."_file"] = $doc;
      $commands["$ctr"."_subs"] = "true";
      $ctr++;
      if($myconfig[IMS_SuperGroupName()]["multidownloadallowsdirselection"] == "yes") {
        $commands["$ctr"."_command"] = "copydir";
        $commands["$ctr"."_to"] = "selected::".$thepath[$key]."$newname";
        $commands["$ctr"."_from"] = "temp::$path";
        $ctr++;
      } else {
        $commands["$ctr"."_command"] = "copydir";
        $commands["$ctr"."_to"] = "$imstrans".$thepath[$key]."$newname";
        $commands["$ctr"."_from"] = "temp::$path";
        $ctr++;
      }
    } else {
      $path = "\\".IMS_SuperGroupName()."\\preview\\objects\\".$key."\\";
      $commands["$ctr"."_command"] = "download";
      $commands["$ctr"."_dir"] = $path;
      $commands["$ctr"."_file"] = $doc;
      $ctr++;
      if($myconfig[IMS_SuperGroupName()]["multidownloadallowsdirselection"] == "yes") {
        $commands["$ctr"."_command"] = "copyfile";
        $commands["$ctr"."_to"] = "selected::".$thepath[$key]."$newname.$thedoctype";
        $commands["$ctr"."_from"] = "temp::$path$doc";
        $ctr++;
      } else {
        $commands["$ctr"."_command"] = "copyfile";
        $commands["$ctr"."_to"] = "$imstrans".$thepath[$key]."$newname.$thedoctype";
        $commands["$ctr"."_from"] = "temp::$path$doc";
        $ctr++;
      }
    }
  }
  if($myconfig[IMS_SuperGroupName()]["multidownloadallowsdirselection"] == "yes") {
    $commands["$ctr"."_command"] = "start";
    $commands["$ctr"."_doc"] = "selected::";
  } else {
    $commands["$ctr"."_command"] = "start";
    $commands["$ctr"."_doc"] = "$imstrans\\";
  }

  $url = IMS_GenerateAdvancedTransferURL ($commands);
  SHIELD_FlushEncoded();
  DHTML_LoadTransURL($url);
}


function MULTI_Ref () // obsolete, used by some wizards
{
  return MULTI_Load_AutoShortcuts();
}

function MULTI_Load_SkipShortcuts()
{
  $result = array();
  $list = MULTI_Load_ManualShortcuts();
  MB_MultiLoad("ims_".IMS_SuperGroupName()."_objects", $list);
  foreach ($list as $key => $dummy) {
    if (!FILES_IsShortcut (IMS_SuperGroupName(), $key)) {
      $result[$key] = "*";
    }
  }
  return $result;
}

function MULTI_GetCurrentSelection() {
  return MB_Load ("local_selected_".IMS_SuperGroupName(), SHIELD_CurrentUser(IMS_SuperGroupName()));
}

function MULTI_UseSelection($selection) {
  /* Fake a "selection" for the remainder of this request, to be used instead of the actual current 
   * selection according to metabase. Useful if you want to be sure that you use the selection
   * as shown to the user and confirmed by the user during a previous request.
   */
  
  // disable, use real selection if allowmultideselect is on
  global $myconfig;
  if ($myconfig[IMS_SupergroupName()]["allowmultideselect"] == "yes")
     $selection = MULTI_GetCurrentSelection();


  if (is_array($selection)) {
    global $MULTI_UseSelection_known, $MULTI_UseSelection_selection;
    $sgn = IMS_SuperGroupName();
    $usr = SHIELD_CurrentUser($sgn);
    $MULTI_UseSelection_selection[$sgn][$usr] = $selection;
    $MULTI_UseSelection_known[$sgn][$usr] = true;
  }
}

function MULTI_Load_ManualShortcuts()
{
  global $MULTI_UseSelection_known, $MULTI_UseSelection_selection;
  $sgn = IMS_SuperGroupName();
  $usr = SHIELD_CurrentUser($sgn);
  if ($MULTI_UseSelection_known[$sgn][$usr]) {
    return $MULTI_UseSelection_selection[$sgn][$usr];
  } else {
    return MB_Load ("local_selected_".$sgn, $usr);
  }
}

function MULTI_Load_AutoShortcuts ()
{
  $result = array();
  $list = MULTI_Load_ManualShortcuts();
  MB_MultiLoad("ims_".IMS_SuperGroupName()."_objects", $list);
  foreach ($list as $key => $dummy) {
    if (FILES_IsShortcut (IMS_SuperGroupName(), $key)) {
      $key = FILES_Base (IMS_SuperGroupName(), $key);
    }
    $result[$key] = "*";
  }
  return $result;
}

function MULTI_SelectedShortcuts()
{
  $list = MULTI_Load_ManualShortcuts();
  foreach ($list as $key => $dummy) {
    if (FILES_IsShortcut (IMS_SuperGroupName(), $key)) {
      return true;
    }
  }
  return false;
}

function MULTI_Selected($id="")
{
  $selected = MULTI_Load_ManualShortcuts();
  if ($id) {
    return $selected[$id] ? true : false;
  } else {
    return count ($selected);
  }
}

function MULTI_Select ($id) // files and shortcuts
{
  if (FILES_IsShortcut (IMS_SuperGroupName(), $id)) {
    if (SHIELD_HasObjectRight (IMS_SuperGroupName(), FILES_Base (IMS_SuperGroupName(), $id), "view")) {
      MULTI_DoSelect (IMS_SuperGroupName(), $id, SHIELD_CurrentUser());
   }
  } else {
    if (SHIELD_HasObjectRight (IMS_SuperGroupName(), $id, "view")) {
      MULTI_DoSelect (IMS_SuperGroupName(), $id, SHIELD_CurrentUser());
    }
  }
}

function MULTI_DoSelect ($sgn, $id, $user) { // low level 
  $selected = &MB_Ref ("local_selected_".$sgn, $user);
  $selected[$id] = "*";
}

function MULTI_Unselect ($id) // files and shortcuts
{
  $selected = &MB_Ref ("local_selected_".IMS_SuperGroupName(), SHIELD_CurrentUser());
  unset ($selected[$id]);
}

function MULTI_UnselectAll () // files and shortcuts
{
  $multi = &MB_Ref ("local_selected_".IMS_SuperGroupName(), SHIELD_CurrentUser());
  $multi = array();
}

function MULTI_PrintAll()
{
  global $myconfig;
  uuse ("dhtml");
  $commands = array();
  $ctr = 1;
  $multi = MULTI_Load_AutoShortcuts();
  foreach ($multi as $key => $specs) {
    $object = MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $key);
    $currentfolder = $object["directory"];
    $tree = CASE_TreeRef (IMS_SuperGroupName(), $currentfolder);
    $path = TREE_Path ($tree, $currentfolder);
    $pathtitle = "\\".$path[1]["shorttitle"]; 
    for ($i=2; $i<=count($path); $i++) {
      $pathtitle .= "\\".$path[$i]["shorttitle"];
    }
    $pathtitle.="\\";
    $thepath[$key] = $pathtitle;
  }
  $retry = true;
  while ($retry) {
    foreach ($thepath as $key => $path) $somepath = $path;
    $first = "\\".N_KeepBefore (N_KeepAfter ($somepath, "\\"), "\\")."\\";
    foreach ($thepath as $key => $path) {
      if (substr ($path, 0, strlen($first))!=$first) $retry = false;
    }
    if ($retry) {
      foreach ($thepath as $key => $path) {
        $thepath2[$key] = substr ($path, strlen($first)-1);
      }
      $thepath = $thepath2;
    }    
  }
  foreach ($multi as $key => $specs) {
    $object = MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $key);
    $doc = FILES_TrueFileName (IMS_SuperGroupName(), $key, "preview");
    $newname = preg_replace ("'[^A-Za-z0-9.]'i", "_", $object["shorttitle"]); 
    if ($used[$newname]) {
      for ($i=2; $i<1000; $i++) {
        if (!$used[$newname."_".$i]) {
          $newname = $newname."_".$i;
          $i = 1001;
        }
      }
    }
    $used[$newname] = "*";
    $path = "\\".IMS_SuperGroupName()."\\preview\\objects\\".$key."\\";
    $commands["$ctr"."_command"] = "download";
    $commands["$ctr"."_params"] = "print";
    $commands["$ctr"."_dir"] = $path;
    $commands["$ctr"."_file"] = $doc;
    $ctr++;
    $commands["$ctr"."_command"] = "shellexecute_hide";
    $commands["$ctr"."_operation"] = "print";
    $commands["$ctr"."_doc"] = "temp::$path$doc";
    $ctr++;
  }
  $url = IMS_GenerateAdvancedTransferURL ($commands);
  SHIELD_FlushEncoded();
  DHTML_LoadTransURL($url);
}


?>