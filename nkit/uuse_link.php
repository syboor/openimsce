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



//HD: Manage relations between objects (documents)

uuse ("dhtml");

/*
Example configuration:
   $myconfig["demo_sites"]["linking"]["derived"]["dms"] = "yes";

   $myconfig["demo_sites"]["linking"]["derived"]["left2right_1_1"] = "is afgeleid van"; // "is derived from"
   $myconfig["demo_sites"]["linking"]["derived"]["left2right_1_n"] = "is afgeleid van"; // "is derived from"
   $myconfig["demo_sites"]["linking"]["derived"]["left2right_n_1"] = "zijn afgeleid van"; // "are derived from"

   $myconfig["demo_sites"]["linking"]["derived"]["right2left_1_1"] = "heeft als afgeleide"; // "has as derivative"
   $myconfig["demo_sites"]["linking"]["derived"]["right2left_1_n"] = "heeft als afgeleiden"; // "has as derivatives"
   $myconfig["demo_sites"]["linking"]["derived"]["right2left_n_1"] = "hebben als afgeleide"; // "have as derivative"

   $myconfig["demo_sites"]["linking"]["part"]["dms"] = "yes";

   $myconfig["demo_sites"]["linking"]["part"]["connect2self"] = "no"; // a document may not be connected to itself (for this connection type)
   $myconfig["demo_sites"]["linking"]["part"]["mirrorconnections"] = "no"; // if document A is connected to document B, document B may not be connected to document A (at least not using the same connection type)

   $myconfig["demo_sites"]["linking"]["part"]["left2right_1_1"] = "is onderdeel van"; // "is part of"
   $myconfig["demo_sites"]["linking"]["part"]["left2right_1_n"] = "is onderdeel van"; // "is part of"
   $myconfig["demo_sites"]["linking"]["part"]["left2right_n_1"] = "zijn onderdeel van"; // "are part of"

   $myconfig["demo_sites"]["linking"]["part"]["right2left_1_1"] = "heeft als onderdeel"; // "has as part"
   $myconfig["demo_sites"]["linking"]["part"]["right2left_1_n"] = "heeft als onderdelen"; // "has as parts"
   $myconfig["demo_sites"]["linking"]["part"]["right2left_n_1"] = "hebben als onderdeel"; // "have as part"
*/

function LINK_EnabledDMS ($sgn="")
{
  if (!$sgn) $sgn = IMS_SuperGroupName();
  global $myconfig;
  $linking = $myconfig[$sgn]["linking"];
  foreach ($linking as $id => $specs) {
    if ($specs["dms"]=="yes") return true;
  }
  return false;
}

function LINK_AvailableTypesDMS ($sgn="") 
{
  $result = array();
  if (!$sgn) $sgn = IMS_SuperGroupName();
  global $myconfig;
  $linking = $myconfig[$sgn]["linking"];
  foreach ($linking as $id => $specs) {
    if ($specs["dms"]=="yes") {
      $result [$id] = $specs;
    }
  }
  return $result;
}

function LINK_Create ($sgn, $leftid, $rightid, $type, $specs="")
{
  // returns nothing if successful, error message (string) if not successful
  global $myconfig;
  if ( $myconfig[$sgn]["linking_dontlink_alreadylinked"] == "yes" ) 
  {
    $linksleft = LINK_LinkedObjects($leftid);
    $linksright = LINK_LinkedObjects($rightid);
    if ( isset($linksleft[$rightid]) || isset($linksright[$leftid]) )
    {
      N_Log ("linking_".$sgn, "ignored already linked link ($type) $leftid -> $rightid [".SHIELD_CurrentUser()."]");
      return ML("De documenten zijn reeds aan elkaar gekoppeld", "These documents are already connected");   
    }
  }
  if ($myconfig[$sgn]["linking"][$type]["connect2self"] == "no" && ($leftid == $rightid)) {
    N_Log ("linking_".$sgn, "ignored connect2self ($type) $leftid -> $rightid [".SHIELD_CurrentUser()."]");
    return ML("Het document kan niet aan zichzelf gekoppeld worden", "The document can not be connected to itself");
  } else {
    $relobj = &MB_Ref ("ims_".$sgn."_object_relations", $leftid."_".$rightid."_".$type);
    if (($relobj["leftid"] == $leftid) && ($relobj["rightid"] == $rightid) && ($relobj["type"] == $type)) {
      N_Log ("linking_".$sgn, "ignored duplicate link ($type) $leftid -> $rightid [".SHIELD_CurrentUser()."]");
      return ML("De documenten zijn reeds aan elkaar gekoppeld", "These documents are already connected");
    }
    if ($myconfig[$sgn]["linking"][$type]["mirrorconnections"] == "no") {
      // Check for a mirror connection
      $mirrorobj = MB_Ref ("ims_".$sgn."_object_relations", $rightid."_".$leftid."_".$type);
      if (($mirrorobj["leftid"] == $rightid) && ($mirrorobj["rightid"] == $leftid) && ($mirrorobj["type"] == $type)) {
        N_Log ("linking_".$sgn, "ignored mirror duplicate link ($type) $leftid -> $rightid [".SHIELD_CurrentUser()."]");
        return ML("De documenten zijn reeds aan elkaar gekoppeld", "These documents are already connected");
      }
    } 
    N_Log ("linking_".$sgn, "create($type) $leftid -> $rightid [".SHIELD_CurrentUser()."]");
    $relobj["leftid"] = $leftid;
    $relobj["rightid"] = $rightid;
    $relobj["type"] = $type;
    $relobj["specs"] = $specs;
  }
}

function LINK_HasLinks ($sgn, $objectid) 
{
  global $myconfig;
  $object = MB_Load ("ims_".$sgn."_objects", $objectid);
  $linking = $myconfig[$sgn]["linking"];
  foreach ($linking as $type => $specs) {
    if ($specs["dms"]=="yes") {
      $list = MB_TurboMultiQuery ("ims_".$sgn."_object_relations", array ("select" => array (
        '$record["leftid"]' => $objectid,
        '$record["type"]' => $type
      )));
      $ctr += count ($list);
      $list = MB_TurboMultiQuery ("ims_".$sgn."_object_relations", array ("select" => array (
        '$record["rightid"]' => $objectid,
        '$record["type"]' => $type
      )));
      $ctr += count ($list);
    }
  }
  return $ctr;
}

function LINK_MultiHasLinks ($sgn, $objects)
{
  $ctr = 0;
  foreach ($objects as $object_id=>$dummy) {
    $ctr += LINK_HasLinks($sgn, $object_id);
  }
  return $ctr;
}

function LINK_Delete ($sgn, $leftid, $rightid, $type)
{
  N_Log ("linking_".$sgn, "delete($type) $leftid -> $rightid [".SHIELD_CurrentUser()."]");
  MB_Delete ("ims_".$sgn."_object_relations", $leftid."_".$rightid."_".$type);
}

function LINK_DeleteAll ($sgn, $objectid)
{
  global $myconfig;
  $object = MB_Load ("ims_".$sgn."_objects", $objectid);
  $linking = $myconfig[$sgn]["linking"];
  foreach ($linking as $type => $specs) {
    if ($specs["dms"]=="yes") {
      $list = MB_TurboMultiQuery ("ims_".$sgn."_object_relations", array ("select" => array (
        '$record["leftid"]' => $objectid,
        '$record["type"]' => $type
      )));
      foreach ($list as $key) {
        $obr = MB_Load ("ims_".$sgn."_object_relations", $key);
        LINK_Delete ($sgn, $objectid, $obr["rightid"], $type);
      }
      $list = MB_TurboMultiQuery ("ims_".$sgn."_object_relations", array ("select" => array (
        '$record["rightid"]' => $objectid,
        '$record["type"]' => $type
      )));
      foreach ($list as $key) {
        $obr = MB_Load ("ims_".$sgn."_object_relations", $key);
        LINK_Delete ($sgn, $obr["leftid"], $objectid, $type);
      }
    }
  }
  return $ctr;
}

function LINK_ShowLinkinfo ($objectid)
{
  global $myconfig, $selectcounter;
  if ($myconfig[IMS_SuperGroupName()]["multifile"]=="yes" and $myconfig[IMS_SuperGroupName()]["multifileblock_in_related"]=="yes")
  {
    uuse ("dhtml");
    uuse ("multi");
    $selectcounter = 0 + MULTI_Selected();
    echo DHTML_EmbedJavaScript ("selectcounter=$selectcounter;");
  }

  //global $myconfig;
  $nothing = true;
  $currentcheckboxed = false;// main document should have only one checkbox, jh 21-7-2010
  $object = MB_Load ("ims_".IMS_SuperGroupName()."_objects", $objectid);
  $linking = $myconfig[IMS_SuperGroupName()]["linking"];
  foreach ($linking as $type => $specs) {
    if ($specs["dms"]=="yes") {
      $list = MB_TurboMultiQuery ("ims_".IMS_SuperGroupName()."_object_relations", array ("select" => array (
        '$record["leftid"]' => $objectid,
        '$record["type"]' => $type
      )));
      $all = array();
      foreach ($list as $key)
      {
        $nothing = false;
        $obr = MB_Load ("ims_".IMS_SuperGroupName()."_object_relations", $key);
        $all[$obr["rightid"]] = $obr["rightid"];
      }
      if ($all) {
        $sgn = IMS_SupergroupName();
        if ($myconfig[$sgn]["multifile"] == "yes" and $myconfig[$sgn]["multifileblock_in_related"] == "yes" and !$currentcheckboxed) {
         
          //global $key;

          $truekey = $objectid;
          $ii = array();
          $ii["alt_on"] = ML("De-selecteer document","Unselect file");
          $ii["alt_off"] = ML("Selecteer document","Select file");
          $ii["js_on_code"] = "
            selectcounter = selectcounter + 1;
            if (selectcounter == 1) {
              multifileoptions = dyn;
              ".DHTML_SetDynamicObject ("multifileoptions")."
            }
            " . DHTML_SetDynamicObject ("selectcounter");
          $ii["js_off_code"] = "
            selectcounter = selectcounter - 1;
            if (selectcounter == 0) {
              multifileoptions = stat;
              ".DHTML_SetDynamicObject ("multifileoptions")."
            }
          " . DHTML_SetDynamicObject ("selectcounter");
          $ii["on_code"] = 'uuse("multi"); MULTI_Select ($input);';
          $ii["off_code"] = 'uuse("multi"); MULTI_Unselect ($input);';
          $ii["input"] = $truekey;
          $ii["state"] = MULTI_Selected ($truekey);
          uuse ("dhtml");  
          echo "&nbsp;".DHTML_IntelliImage ($ii);
          $currentcheckboxed = true;
          $object_id = $truekey;
        }
        echo "<b>".ML ("Document", "Document")." '".$object["shorttitle"]."' ";
        echo $specs["left2right_1_n"].":</b><br>";
        echo DHTML_InvisiTable ("","","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",LINK_ShowLinkInfoSection ($all, "left2right", $objectid, $type))."<br>";
      }
      $list = MB_TurboMultiQuery ("ims_".IMS_SuperGroupName()."_object_relations", array ("select" => array (
        '$record["rightid"]' => $objectid,
        '$record["type"]' => $type
      )));
      $all = array();
      foreach ($list as $key)
      {
        $nothing = false;
        $obr = MB_Load ("ims_".IMS_SuperGroupName()."_object_relations", $key);
        $all[$obr["leftid"]] = $obr["leftid"];
      }
      if ($all) {
   
        $sgn = IMS_SupergroupName();
        if ($myconfig[$sgn]["multifile"] == "yes" and $myconfig[$sgn]["multifileblock_in_related"] == "yes"  and !$currentcheckboxed) {
         
          //global $key;

          $truekey = $objectid;
          $ii = array();
          $ii["alt_on"] = ML("De-selecteer document","Unselect file");
          $ii["alt_off"] = ML("Selecteer document","Select file");
          $ii["js_on_code"] = "
            selectcounter = selectcounter + 1;
            if (selectcounter == 1) {
              multifileoptions = dyn;
              ".DHTML_SetDynamicObject ("multifileoptions")."
            }
            " . DHTML_SetDynamicObject ("selectcounter");
          $ii["js_off_code"] = "
            selectcounter = selectcounter - 1;
            if (selectcounter == 0) {
              multifileoptions = stat;
              ".DHTML_SetDynamicObject ("multifileoptions")."
            }
          " . DHTML_SetDynamicObject ("selectcounter");
          $ii["on_code"] = 'uuse("multi"); MULTI_Select ($input);';
          $ii["off_code"] = 'uuse("multi"); MULTI_Unselect ($input);';
          $ii["input"] = $truekey;
          $ii["state"] = MULTI_Selected ($truekey);
          uuse ("dhtml");  
          echo "&nbsp;".DHTML_IntelliImage ($ii);
          $currentcheckboxed = true;
          $object_id = $truekey;
        }

        echo "<b>".ML ("Document", "Document")." '".$object["shorttitle"]."' ";
        echo $specs["right2left_1_n"].":</b><br>";
        echo DHTML_InvisiTable ("","","&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",LINK_ShowLinkInfoSection ($all, "right2left", $objectid, $type))."<br>";
      }
    }
  }
  if ($nothing) {
    echo "<b>".ML ("Document", "Document")." '".$object["shorttitle"]."' ".ML("is niet gekoppeld","is not connected").".<br>";
  }
}

function LINK_ShowLinkInfoSection ($all, $leftorright, $baseid, $type)
{
  ob_start();

  foreach ($all as $key => $dummy)
  {
    $object = MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $key);
    $tree = CASE_TreeRef (IMS_SuperGroupName (), $object["directory"]);
    $path = TREE_Path ($tree, $object["directory"]);
    $title = "";
    for ($i=1; $i<=count($path); $i++) {
      $title .= "z".$path[$i]["shorttitle"]."z";
    }
    $title .= $object["shorttitle"];
    $list[$key] = strtolower ($title);
  }
  asort ($list);

  $lastdir = "";
  T_Start ("ims");
  echo "&nbsp;&nbsp;&nbsp;";
  T_Next();
  echo ML ("Document", "Document");
  T_Next();
  T_Next();
  echo ML ("Status", "Status");
  T_Next();
  global $myconfig;
  if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
    echo ML ("Versie", "Version");
    T_Next();
  }
  echo ML ("Laatst gewijzigd", "Last Changed");
  T_Next();
  echo ML ("Acties", "Actions");

  T_NewRow();
  foreach ($list as $key => $title)
  {
    $object = MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $key);
    $tree = CASE_TreeRef (IMS_SuperGroupName (), $object["directory"]);
    $path = TREE_Path ($tree, $object["directory"]);
    $dir = "";
    for ($i=1; $i<=count($path); $i++) {
      $dir .= $path[$i]["shorttitle"]." > ";
    }
    if ($dir!=$lastdir) {
      $lastdir = $dir;
      echo "<b>$dir</b>";
      T_NewRow();
    }

    $sgn = IMS_SupergroupName();
    if (!($myconfig[$sgn]["multifile"] == "yes" and $myconfig[$sgn]["multifileblock_in_related"] == "yes")) {
      echo "&nbsp;&nbsp;&nbsp;";
    }
    else {
          //global $key;

          $truekey = $key;
          $ii = array();
          $ii["alt_on"] = ML("De-selecteer document","Unselect file");
          $ii["alt_off"] = ML("Selecteer document","Select file");
          $ii["js_on_code"] = "
            selectcounter = selectcounter + 1;
            if (selectcounter == 1) {
              multifileoptions = dyn;
              ".DHTML_SetDynamicObject ("multifileoptions")."
            }
            " . DHTML_SetDynamicObject ("selectcounter");
          $ii["js_off_code"] = "
            selectcounter = selectcounter - 1;
            if (selectcounter == 0) {
              multifileoptions = stat;
              ".DHTML_SetDynamicObject ("multifileoptions")."
            }
          " . DHTML_SetDynamicObject ("selectcounter");
          $ii["on_code"] = 'uuse("multi"); MULTI_Select ($input);';
          $ii["off_code"] = 'uuse("multi"); MULTI_Unselect ($input);';
          $ii["input"] = $truekey;
          $ii["state"] = MULTI_Selected ($truekey);
          uuse ("dhtml");  
          echo "&nbsp;".DHTML_IntelliImage ($ii);
          $key = $truekey;
    }
    T_Next();

    $doc = FILES_TrueFileName (IMS_SuperGroupName(), $key, "preview");
    $image = FILES_Icon (IMS_SuperGroupName(), $key, false, "preview");
    $ext = FILES_FileExt (IMS_SuperGroupName(), $key, "preview");
    $thedoctype = FILES_FileType (IMS_SuperGroupName(), $key, "preview");
    
    echo '<img border=0 height=16 width=16 src="'.$image.'">';

    T_Next();

    echo $object["shorttitle"].$ext; 

    T_Next();

    echo SHIELD_CurrentStageName (IMS_SuperGroupName(), $key);

    T_Next();

    if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
      echo IMS_Version (IMS_Supergroupname(), $key); 
      T_Next();
    }

    if (is_array($object["history"])) {
      reset ($object["history"]);
      while (list($k, $data)=each($object["history"])) {
        $time = $data["when"];
      }
      echo N_VisualDate ($time, 1, 1); 
    } else {
      echo " "; 
    }
    T_Next();
    
    if (SHIELD_HasObjectRight (IMS_SuperGroupName(), $key, "view")) 
    {
      $url = FILES_TransViewPreviewURL (IMS_SuperGroupName(), $key);
      echo "<a title=\"".ML("Bekijk","View")."\"href=\"$url\"><img border=0 src=\"/ufc/rapid/openims/view.gif\"></img></a>&nbsp;";
    }

    $url = FILES_DMSURL (IMS_SuperGroupName(), $key);
    echo "<a title=\"".ML("Naar basis folder","To base folder")."\"href=\"$url\"><img border=0 src=\"/ufc/rapid/openims/folder.gif\"></img></a>&nbsp;";

    if (SHIELD_HasObjectRight (IMS_SuperGroupName(), $key, "view"))
    {
      $url = N_AlterURL (N_MyVeryFullURL(), "object_id", $key);
      echo "<a title=\"".ML("Volg koppeling","Follow link")."\"href=\"$url\"><img border=0 src=\"/ufc/rapid/openims/follow.gif\"></img></a>&nbsp;";
    }

    $mysecuritysection = SHIELD_SecuritySectionForObject (IMS_SuperGroupName(), $key);
    if (SHIELD_HasGlobalRight (IMS_SuperGroupName(), "connectmanagement", $mysecuritysection)) {
      if ($myconfig[IMS_SuperGroupName()]["confirmdeleteconnection"] == "yes") {
        $form = array();
        $form["input"]["sgn"] = IMS_SuperGroupName();
        $form["input"]["type"] = $type;
        if ($leftorright=="left2right") {
          $form["input"]["left"] = $baseid;
          $form["input"]["right"] = $key;
        } else {
          $form["input"]["left"] = $key;
          $form["input"]["right"] = $baseid;
        }
        $form["metaspec"]["fields"]["sure"]["type"] = "list";
        $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
        $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
        $form["title"] = ML("Weet u het zeker?","Are you sure?");
        $form["formtemplate"] = '
          <table width=100>
            <tr><td colspan=2><font face="arial" color="#ff0000" size=2><b>'.ML("Verwijder koppeling", "Remove link").'</b></font></td></tr>
            <tr><td colspan=2>&nbsp;</td></tr>
            <tr><td><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td><td><nobr>[[[sure]]]</nobr></td></tr>
            <tr><td colspan=2>&nbsp;</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["precode"] = '
           global $myconfig;
           if ($myconfig[IMS_SuperGroupname()]["confirmdeleteconnectiondefault"] == "yes") {
             $data["sure"] = "yes";
           }
        ';
        $form["postcode"] = '
          if ($data["sure"]=="yes") {
            uuse ("link"); LINK_Delete ($input["sgn"], $input["left"], $input["right"], $input["type"]);
          }
        ';

        $url = FORMS_URL ($form);

      } else {
        $form = array();
        if ($leftorright=="left2right") {
          $form["postcode"] = 'uuse ("link"); LINK_Delete ("'.IMS_SuperGroupName().'", "'.$baseid.'", "'.$key.'", "'.$type.'");';
        } else {
          $form["postcode"] = 'uuse ("link"); LINK_Delete ("'.IMS_SuperGroupName().'", "'.$key.'", "'.$baseid.'", "'.$type.'");';
        }
        $url = FORMS_URL ($form);
      }
      echo "<a title=\"".ML("Verwijder koppeling","Remove link")."\"href=\"$url\"><img border=0 src=\"/ufc/rapid/openims/break-link.gif\"></img></a>";
    }

    T_NewRow();
  }
  TE_End();

  $result = ob_get_contents();
  ob_end_clean();

  return $result;
}

//jh 15-7-2010 tbv ctgb
function LINK_LinkedObjects($object_id)
{
  $table = "ims_" . IMS_SupergroupName() . "_object_relations";
  $specs1["select"] = array('$record["leftid"]' => $object_id);
  $specs1["sort"] = '$record["rightid"]';
  $specs2["select"] = array('$record["rightid"]' => $object_id);
  $specs2["sort"] = '$record["leftid"]';

  $k1 = array_flip(MB_TurboMultiQuery($table, $specs1));
  $k2 = array_flip(MB_TurboMultiQuery($table, $specs2));

  $k3 = n_array_merge($k1, $k2);
  $k4 = n_array_merge($k3, array($object_id => $object_id));

  return $k4;
}



?>