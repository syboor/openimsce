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



uuse ("files");
uuse ("case");
uuse ("multi");
uuse ("dhtml");

function REPORTS_VisualPath ($folderid)
{
  if (substr ($folderid, 0, 1)=="(") {
    $case_id = substr ($folderid, 0, strpos ($folderid, ")")+1);
    $caserec = MB_Load ("ims_".IMS_SuperGroupName()."_case_data", $case_id); 
    $result = $caserec["longtitle"]." (".$caserec["shorttitle"].") &gt;&gt; ";
  }
  $tree = CASE_TreeRef (IMS_SuperGroupName (), $folderid);
  $path = TREE_Path ($tree, $folderid);
  foreach ($path as $dummy => $specs) {
    $result .= $specs["shorttitle"] . " &gt; ";    
  }
  return $result;
}

// 20120511 KvD Gebruik eigen lijst indien opgegeven
function REPORTS_Selected ($type, $showshortcuts=false, $deselect=true, $ownkeys=false)
{
  // Types: "simple", "snapshot", "report"

  global $myconfig;

  if ($deselect && !$ownkeys)
    $muldes = ($myconfig[IMS_SupergroupName()]["allowmultideselect"] == "yes");

  ob_start();

  if ($ownkeys) {
    $all = $ownkeys;  // keys vooraf opgegeven
  } else if ($showshortcuts) {
    $all = MULTI_Load_ManualShortcuts();
  } else {
    $all = MULTI_Load_AutoShortcuts();
  }

  foreach ($all as $thekey => $dummy)
  {
    $key = $thekey;
    $tobject = MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $key);
    if (FILES_IsShortcut (IMS_SuperGroupName(), $key)) {
      $key = FILES_Base (IMS_SuperGroupName(), $key);
    }
    $object = MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $key);
    $tree = CASE_TreeRef (IMS_SuperGroupName (), $tobject["directory"]);
    $path = TREE_Path ($tree, $tobject["directory"]);
    $title = chr(1).REPORTS_VisualPath ($tobject["directory"]).chr(1);
    $title .= $object["shorttitle"];
    $list[$thekey] = strtolower ($title);
  }
  if ($list)
    asort ($list);

  T_Start ("ims");

  if ($type=="report") {  
    echo ML("OpenIMS DMS rapport 'geselecteerde bestanden'","OpenIMS DMS 'selected files' report");
  } elseif ($type=="simple") {
    // don't show header
  } else { // snapshot
    echo ML("OpenIMS DMS snapshot","OpenIMS DMS snapshot");
  }

  if ($type != "simple") {
     T_NewRow();
     echo "<b>".ML("Datum", "Date")."</b>";
     T_Next();
     echo N_VisualDate (time(), 1, 0);
     T_NewRow();
     echo "<b>".ML("Gemaakt door","Made by")."</b>";
     T_Next();
     echo SHIELD_CurrentUserName();
     T_Newrow();
     echo "<b>".ML("Aantal documenten","Number of files")."</b>";
     T_Next();
     echo MULTI_Selected();
     TE_End();
     echo "<br>";
  }

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
  if (($type=="report") || ($type=="simple")) {  
    echo ML ("Laatst gewijzigd", "Last Changed");
    T_Next();
    echo ML ("Toegewezen", "Assigned");
  } else { // snapshot
    echo ML ("Datum", "Date");
  }

  T_NewRow();
  foreach ($list as $key => $title)
  {
    $memkey = $key;
    $tobject = MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $key);
    if (FILES_IsShortcut (IMS_SuperGroupName(), $key)) {
      $key = FILES_Base (IMS_SuperGroupName(), $key);
      $image = FILES_Icon (IMS_SuperGroupName(), $key, true, "preview"); 
    } else {
      $image = FILES_Icon (IMS_SuperGroupName(), $key, false, "preview");
    }
    $object = MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $key);
    $tree = CASE_TreeRef (IMS_SuperGroupName (), $tobject["directory"]);
    $path = TREE_Path ($tree, $tobject["directory"]);
    $dir = REPORTS_VisualPath ($tobject["directory"]);
    if ($dir!=$lastdir) {
      $lastdir = $dir;
      echo "<b>$dir</b>";
      T_NewRow();
    }
    if ($muldes)
    {
      $specs["input"] = $memkey;
      $specs["state"] = MULTI_Selected($memkey);
      $specs["on_code"] = 'uuse("multi"); MULTI_Select ($input);';
      $specs["off_code"] = 'uuse("multi"); MULTI_UnSelect ($input);';
      $vink = DHTML_IntelliImage($specs, $memkey);
      echo $vink;
    }
    else
    {
      echo "&nbsp;&nbsp;&nbsp;";
    }
    T_Next();

    $doc = FILES_TrueFileName (IMS_SuperGroupName(), $key, "preview"); // Although we are creating a history link, we always link to the last version, which must be equal to the preview version
    $ext = FILES_FileExt (IMS_SuperGroupName(), $key, "preview");
    $thedoctype = FILES_FileType (IMS_SuperGroupName(), $key, "preview");
    
    if (($type=="report") || ($type=="simple")) {  
      echo '<img border=0 height=16 width=16 src="'.$image.'">';
    } else { // snapshot
      reset ($object["history"]);
      $url = "";
      while (list($verkey, $data)=each($object["history"])) {
        if ($data["type"]=="new" || $data["type"]=="edit") {
          $url = FILES_DocHistoryURL (IMS_SuperGroupName(), $key, $verkey);
        }
      }
      if ($url) {
        echo '<a title="'.ML("Hyperlink naar snapshot versie van %1","Hyperlink to snapshot version of %1", $object["shorttitle"].$ext) .'" href="'.$url.'"><img border=0 height=16 width=16 src="'.$image.'"></a>';
      } else {
        echo '<img border=0 height=16 width=16 src="'.$image.'">';
      }
    }

    T_Next();

    if (($type=="report") || ($type=="simple")) {  
      echo $object["shorttitle"].$ext;
    } else { // snapshot
      if ($url) {
        echo '<a title="'.ML("Hyperlink naar snapshot versie van %1","Hyperlink to snapshot version of %1", $object["shorttitle"].$ext) .'" href="'.$url.'">'.$object["shorttitle"].$ext.'</a>';
      } else {
        echo $object["shorttitle"].$ext;
      }
    }

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

    if (($type=="report") || ($type=="simple")) {  
      T_Next();
 
       if ($object["allocto"]) {
        $user_id = $object["allocto"];
        $user = MB_Ref ("shield_".IMS_SuperGroupName()."_users", $user_id);     
        echo "<nobr>".$user["name"]."</nobr>";
      }
    }

    T_NewRow();
  }
  TE_End();
  if ($type=="snapshot") {  
    echo '<br><font face="helvetica" style="font-size: 12px;" color="ff0000"><b>';
    echo ML ("WAARSCHUWING: De hyperlinks in deze snapshot geven toegang tot de betreffende bestanden.",
             "WARNING: The hyperlinks in this snaphot provide access to the related files.");
    echo "</b></font>";
  }

  $result = ob_get_contents();
  ob_end_clean();

  return $result;
}


function REPORTS_Selected_Custom($condition = 1, $extracolumns = array(), $shortcutmode = "auto", $max = 0) {
  /* Report comparable with the "simple" report type of REPORTS_Selected, but with customizable columns.
   * Only the title is shown by default, all other columns should be specified in $extracolumns.
   *
   * $condition: 
   *    Expression to determine if a document should be shown ($record is available).
   *    Please be aware that documents not meeting the condition will be hidden, but will not be deleted from the selection,
   *    so any code "doing" something with the selection should also check the condition.
   * $extracolumns:
   *    array of $columntitle => $columncode pairs, where $columncode is a statement that echo's the desired output
   * $shortcutmode: 
   *    "auto"     show shortcuts as shortcuts and documents as documents, give document ids to $extracolumns code
   *    "manual"   show shortcuts as shortcuts and documents as documents, give document or shortcut ids to $extracolumns code
   *    "skip"     ignore shortcuts (don't show them at all)
   * $max: 
   *    maximum number of documents to show (if documents are hidden because of this setting, a warning will be shown)
   *
   * Example code (note that $condition is just an expression, but $columncode is a statement using "echo")
      $condition = '$object["workflow"] == "wf_dms"';
      echo REPORTS_Selected_Custom($condition, array(
        "Toegewezen" => 'if ($object["allocto"]) {
              $user_id = $object["allocto"];
              $user = MB_Ref ("shield_".IMS_SuperGroupName()."_users", $user_id);     
              echo "<nobr>".$user["name"]."</nobr>";
           }',
        "Workflow" => '$workflow = MB_Ref("shield_".$sgn."_workflows", $object["workflow"]); echo $workflow["name"];',
        "Huidig stadium" => 'echo SHIELD_CurrentStageName($sgn, $key);',
        "Nieuw stadium" => 'echo $workflow[$workflow["stages"]]["name"];',
        "Toegewezen" => 'if ($object["allocto"]) {
              $user_id = $object["allocto"];
              $user = MB_Ref ("shield_".IMS_SuperGroupName()."_users", $user_id);     
              echo "<nobr>".$user["name"]."</nobr>";
           }',
        ), "auto", 20);
   */

  uuse("multi");
  uuse("files");
  uuse("case");
  uuse("tree");
  uuse("reports");

  if ($shortcutmode == "manual") {
    $all = MULTI_Load_ManualShortcuts();
  } elseif ($shortcutmode == "skip") {
    $all = MULTI_Load_SkipShortcuts();  
  } else {
    //$all = MULTI_Load_AutoShortcuts();
    $all = MULTI_Load_ManualShortcuts(); // manual shortcuts enable us to show the appropriate icon (with the shortcut arrow). 
  }

  $sgn = IMS_SuperGroupName();
  $nvalid = 0;
  $ninvalid = 0;
  $showed = 0;
  
  MB_MultiLoad("ims_".$sgn."_objects", $all);
  foreach ($all as $thekey => $dummy) {
    $key = $thekey;
    $tobject = MB_Ref ("ims_".$sgn."_objects", $key);
    if (FILES_IsShortcut ($sgn, $key)) {
      $key = FILES_Base ($sgn, $key);
    }
    $object = MB_Ref ("ims_".$sgn."_objects", $key);
    $result = false;
    $result = N_Eval ('$result = ' . $condition . ';', get_defined_vars(), "result");
    if ($result) {
      $nvalid++;
      $tree = CASE_TreeRef ($sgn, $tobject["directory"]);
      $path = TREE_Path ($tree, $tobject["directory"]);
      $sortkey = chr(1).REPORTS_VisualPath ($tobject["directory"]).chr(1);
      $sortkey .= $object["shorttitle"];
      $sortlist[$thekey] = strtolower ($sortkey);
    } else {
      $ninvalid++;
    }
  }
  if ($sortlist)
    asort ($sortlist);

  $lastdir = "";

  T_Start ("ims");
  echo "&nbsp;&nbsp;&nbsp;";
  T_Next();
  echo ML ("Document", "Document");
  T_Next();
  T_Next();
  foreach ($extracolumns as $label => $columncode) {
    echo $label;
    T_Next();
  }
  T_NewRow();
  
  foreach ($sortlist as $truekey => $shorttitle) {
    $key = $truekey;
    $tobject = MB_Ref ("ims_".$sgn."_objects", $key);
    if (FILES_IsShortcut ($sgn, $key)) {
      $key = FILES_Base ($sgn, $key);
      $image = FILES_Icon ($sgn, $key, true, "preview"); 
    } else {
      $image = FILES_Icon ($sgn, $key, false, "preview");
    }
    $object = MB_Ref ("ims_".$sgn."_objects", $key);
    $tree = CASE_TreeRef ($sgn, $tobject["directory"]);
    $path = TREE_Path ($tree, $tobject["directory"]);
    $dir = REPORTS_VisualPath ($tobject["directory"]);
    if ($dir!=$lastdir) {
      $ndirs++;
      $lastdir = $dir;
      echo "<b>$dir</b>";
      T_NewRow();
    }
    echo "&nbsp;&nbsp;&nbsp;";
    T_Next();

    $doc = FILES_TrueFileName ($sgn, $key, "preview");
    $ext = FILES_FileExt ($sgn, $key, "preview");
    $thedoctype = FILES_FileType ($sgn, $key, "preview");
    
    echo '<img border=0 height=16 width=16 src="'.$image.'">';

    T_Next();

    echo $object["shorttitle"];

    T_Next();

    if ($shortcutmode == "manual") $key = $truekey;
    foreach ($extracolumns as $label => $columncode) {
      N_Eval($columncode, get_defined_vars(), "");
      T_Next();
    }

    T_NewRow();
    
    $showed++;
    if ($max && $showed >= $max) break;
  }
  $result = "";
  $result .= TS_End();
  if ($ninvalid) $result .= '<font face=arial size=2>' . ML("U heeft %1 documenten geselecteerd. %2 documenten zijn overgeslagen omdat ze niet voldoen aan de condities van deze Assistent.", "You have selected %1 documents. %2 documents have been skipped because they do not meet the criteria for this Assistant.", ($nvalid+$ninvalid), $ninvalid) . '</font><br/>';
  if ($max && $nvalid > $max) $result .= '<font face=arial size=3 color=red><b>' . ML("Er zullen %1 documenten verwerkt worden. Alleen de eerste %2 documenten zijn getoond. De niet getoonde documenten kunnen zich in andere folders dan de getoonde folders bevinden.", "%1 documents will be processed. Only the first %2 documents have been shown. The hidden documents may be located in different folders than the folders shown.", $nvalid, $max) . '</b></font><br/>';
  
  return $result;
  
}



?>