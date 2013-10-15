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
  uuse("ims");
  uuse("diff");
  uuse("forms");
  uuse("tables");
  uuse("sync");
  uuse("search");
  uuse("mail");
  uuse("portal");
  uuse("bpms");
  uuse("skins");
  uuse("dmsuif");
  uuse("bpmsuif");
  uuse("dbmuif");
  uuse("multi");
  uuse("files");
  uuse("case");
  uuse("black");
  uuse("link");
  uuse("reports");
  uuse("tree");
  uuse("dhtml");
  uuse("shield");
  uuse("word");

  global $myconfig;
  $sgn = IMS_SuperGroupName();

  if (($myconfig[$sgn]["ml"]["metadatalevel"] && $myconfig[$sgn]["ml"]["metadatafiltering"] != "no") ||
       $myconfig[$sgn]["ml"]["metadatafiltering"] == "yes") {
    if ($submode != "flex_data" && $submode != "flex_code") {
      N_SetOutputFilter('FORMS_ML_Filter');
      // works with flushing and with DHTML_LoadTransferUrl
    }
  }

 // if ($cfolder) $currentfolder = $cfolder;
 // if ($cobject) $currentobject = $cobject;

  if ($cfolder) {
    $currentfolder = $cfolder;
    $_REQUEST['currentfolder'] = $cfolder; //gv 25-03-2010 backward compatibility for older custom code (maatwerk)
  }

  if ($cobject) {
    $currentobject = $cobject;
    $_REQUEST['currentobject'] = $cobject; //gv 25-03-2010 backward compatibility for older custom code (maatwerk)
  }

  if ($submode=="activities") { // prevent conflicts with BPMS
    if (($myconfig["$sgn"]["defaultscopeeverything"] == "yes") || ($myconfig["$sgn"]["show_generic"] == "no"))
    {
      global $thecase;
      $thecase = $_REQUEST["thecase"];
      if (!is_string($thecase))
        $thecase = "allofthem";
    }
  }

  if ($currentfolder) {

    //check if folder is visible for user (custom folder view), hyperlinks used possibly
    $mysecuritysection = SHIELD_SecuritySectionForFolder (IMS_SuperGroupName(), $currentfolder);
    if ($mysecuritysection) {
      $tree = Array();
      $sgn = IMS_SupergroupName();
      $tree = CASE_TreeRef($sgn, $mysecuritysection);
      $sectionobject = TREE_AccessObject($tree, $mysecuritysection);
      $vis = TREE_Visible($mysecuritysection, $sectionobject); // Does not do autologon (for very good reasons)
      if (!$vis) {
        SHIELD_RequireLogon($sgn);
        $vis = TREE_Visible($mysecuritysection, $sectionobject); // try again. Mostly useless, since (assuming no SSO) RequireLogon will either make no difference whatsoever (already logged in), or it will redirect and kill the current request.
        if (!$vis) {
         $casetype = MB_FETCH("ims_".$sgn."_case_data",substr($currentfolder,0,34),"category");
          $javascript_message_alert_id = N_guid();
          TMP_SaveObject ( $javascript_message_alert_id , Array( 'message' => ML( 'U heeft geen toegang tot dit dossier.\nKies een andere a.u.b.' , 'You have no access to this case.\nPlease choose another.' ) ) );
          N_Redirect("/openims/openims.php?mode=dms&submode=cases&casetype=$casetype&javascript_message_alert_id=" . $javascript_message_alert_id ); // goto case overview
        }
      }
    }

    // If currentfolder doesnt exists, redirect to the root of the current case (if it exists), or to
    // the root of Algemeen/Generic.
    if (!CASE_FolderExists (IMS_SuperGroupName(), $currentfolder)) {
      $alterurl = "";
      $p = strpos ($currentfolder, ")");
      if ($p) {
        $case_id = substr($currentfolder, 0, $p+1);
        if (CASE_FolderExists(IMS_SuperGroupName(), $case_id."root")) {
          $alterurl = N_AlterURL (N_AlterURL (N_MyFullURL(), "currentfolder", $case_id."root"), "cfolder", $case_id."root");
        }
      }
      if (!$alterurl) $alterurl = N_AlterURL (N_AlterURL (N_MyFullURL(), "currentfolder", ""), "cfolder", "");
      N_Redirect ($alterurl);
    }

  }

  if ($currentobject) {
    $object = MB_Load ("ims_".IMS_SuperGroupName()."_objects", $currentobject);
    if ($object) {
      global $myconfig;
      if (($myconfig[IMS_SuperGroupName()]["jumptocurrentobjectfolder"] == "yes") && $object["directory"] && $currentfolder && ($object["directory"] != $currentfolder) && CASE_FolderExists(IMS_SuperGroupName(), $object["directory"])) {
        $alterurl = N_AlterURL (N_AlterURL (N_MyFullURL(), "currentfolder", $object["directory"]), "cfolder", $object["directory"]);
        N_Redirect($alterurl);
      }
    } else { // object doesnt exist
      $alterurl = N_AlterURL (N_AlterURL (N_MyFullURL(), "currentobject", ""), "cobject", "");
      N_Redirect ($alterurl);
    }
  }

  if ($filter) $filter = stripcslashes ($filter);

  if ($q || $qr1 || $qr2 || $qr3 || $qr4 || $qrn) {
    $q = stripcslashes ($q);
    $qr1 = stripcslashes ($qr1);
    $qr2 = stripcslashes ($qr2);
    $qr3 = stripcslashes ($qr3);
    $qr4 = stripcslashes ($qr4);
    $qrn = stripcslashes ($qrn);
  }

  global $debug, $profiling;


if(!function_exists("DumpWorkflowRights")) {
  function DumpWorkflowRights ($wf_pr, $workflow_or_process, $right, $ignoregroups=array())
  {
    global $objectrights, $processrights, $workflow_id, $process_id;
    if ($wf_pr=="workflow") {
      $id = $workflow_id;
      echo "<b>".ML("Het","The")." '$right' ".ML("recht","right")."</b> (".$objectrights[$right].")";
    } else {
      $id = $process_id;
      echo "<b>".ML("Het","The")." '$right' ".ML("recht","right")."</b> (".$processrights[$right].")";
    }
    T_NewRow();
    $list = $workflow_or_process["rights"][$right];
    echo "&nbsp;&nbsp;&nbsp;";
    T_Next();

    echo WorkflowRights ($wf_pr, $list, $ignoregroups);

    T_NewRow();
  }
}

if(!function_exists("WorkflowRights")) {
  function WorkflowRights ($wf_pr, $list, $ignoregroups=array())
  {
    global $supergroupname;
    $ret = "";
    $first=true;
    if (is_array($list)) {
      reset ($list);
      ksort ($list);
      reset ($list);
      while (list($group_id)=each($list)) {
        $group = MB_Ref ("shield_".$supergroupname."_groups", $group_id);
        if (($group) && (!in_array($group_id, $ignoregroups)))  {
          if (!$first) $ret.=", ";
          $ret .= $group["name"];
          $first=false;
        }
      }
    }
    if (!$ret) $ret = ML("Niemand","Nobody");
    return $ret;
  }
}

  N_SSLRedirect();
  N_OpenIMSCE_AutoconfRedirect();

  $siteinfo = IMS_SiteInfo();
  $supergroupname = $siteinfo["sitecollection"];
  $user_id = SHIELD_CurrentUser ($supergroupname);
  $user = MB_Ref ("shield_$supergroupname"."_users", $user_id);
  $searchblock = 10; // results in search pages
  SHIELD_InitDescriptions();

// probably not needed because of code below this code
////20091112 KvD currentfolder MOET gezet zijn !!
//  if ($mode == "dms" && (!$submode || $submode=="documents") && !$currentfolder)
//     $currentfolder = "root";
////

  if ($securitysection) {
    $mysecuritysection = $securitysection;
  } else {
    if ($currentfolder) {
      $mysecuritysection = SHIELD_SecuritySectionForFolder ($supergroupname, $currentfolder);
    } else {
      if ($submode=="documents") {
        // LF20090729: local security should also work on root folder of DMS
        $mysecuritysection = SHIELD_SecuritySectionForFolder ($supergroupname, "root");
      } else {
        $mysecuritysection = SHIELD_SecuritySectionForFolder ($supergroupname, $rootfolder);
      }
    }
    $securitysection = $mysecuritysection;
  }

if(!function_exists("elapsed")) {
  function elapsed ($seconds) {
    if ($seconds > 3600*24) {
      $days = (int)($seconds/(3600*24));
      if ($days==1)
        return "1 dag oud";
      else
        return $days." dagen oud";
    } else if ($seconds > 3600) {
      $hours = (int)($seconds/3600);
      if ($hours==1)
        return "1 uur oud";
      else
        return $hours." uren oud";
    } else if ($seconds > 60) {
      $minutes = (int)($seconds/60);
      if ($minutes==1)
        return "1 minuut oud";
      else
        return $minutes." minuten oud";
    } else {
      if ($seconds==1)
        return "1 seconde oud";
      else
        return $seconds." seconden oud";
    }
  }
}
echo IMS_HtmlDoctype();
?>
<html>
<head>
<style type="text/css">
  input.style10px{font-size:10px;}/*Oude "zonder skin" vormgeving behouden */
  input.inputButton{font-size:10px;font-weight:bold;width:90px;}/*Oude "zonder skin" vormgeving behouden, tevens class voor nieuwe buttons */
  div.insteadOfBR{height:3px;}/*Op bepaalde plekken moeten BR's weg op nieuwe skin, en moeten kleiner in oude skin!*/
  ul.foldinglist{ /* verwijder lege regel (<br>) voor de folderlijst */
    margin-top:0px;
  }
</style>
<?
  if( $myconfig[$supergroupname]["loadjquery"] == "auto" ) {
    uuse("dhtml");
    echo DHTML_LoadJquery();
  }
?>
<?
  if ( function_exists("SKIN_buildMenu") && ( function_exists( "SKIN_drawMenu" ) || function_exists( "SKIN_drawHtmlTitle" ) ) )
  {
    $O_IMS_MENU = SKIN_buildMenu();
    echo "<title>"; 
    if ( function_exists("SKIN_drawHtmlTitle") )
      ob_start(); // prevent default HTML Title output
  } else {
    echo "<title>"; 
  }

  if (!$mode) $mode="cms";
  echo ML("OpenIMS","OpenIMS");
  if ($mode=="cms") {
    echo " ";
    echo ML("CMS","CMS");
  } else if ($mode=="dms") {
    echo " ";
    echo ML("DMS","DMS");
  } 
  echo " 4.2 build $imsbuild ";
  if ($mode=="cms") {
    echo " ";
    echo "(".ML("Content Management Server","Content Management Server").")";
  } else if ($mode=="dms") {
    echo " ";
    echo "(".ML("Document Management Server","Document Management Server").")";
  } 

  if ( function_exists("SKIN_buildMenu") && function_exists("SKIN_drawHtmlTitle") )
  {
    ob_end_clean(); // prevent default HTML Title output
    echo SKIN_drawHtmlTitle( $O_IMS_MENU );
  }
?></title>
<META http-equiv="Pragma" content="no-cache">
<? echo SKIN_CSS(); ?>
<?
  if (SHIELD_HasProduct("dms")) echo DHTML_InitDragDrop (DMSUIF_DragDropHandler());
?>
</head>

<body topmargin="0" leftmargin="0" marginheight="0" marginwidth="0" class="openims_php" <?
  if ( $_GET["javascript_message_alert_id"] )
  {
    $tmp_message_object = TMP_LoadObject( $_GET["javascript_message_alert_id"] );
    if ( $tmp_message_object && $tmp_message_object["message"] )
    {
      TMP_SaveObject( $_GET["javascript_message_alert_id"] , Array("message" => "" ) );
      print(" onload=\"alert('" . $tmp_message_object["message"] . "');\" ");
    }
  }
?> background="<? echo SKIN_Bottom_Background() ?>" bgcolor="#<? echo SKIN_Bottom_BgColor() ?>" >
<?
    SHIELD_NeedsGlobalRight ($supergroupname, "preview");

    $goto = N_MyFullURL();

    if ($viewmode!="report") {
      if ( function_exists( "SKIN_drawMenu" ) ) SKIN_preventMenuOutputStart( "MAINMENU" );


    echo '<table width="100%" border="0" cellspacing="0" cellpadding="0"><tr><td>'.
         '<table background="' . SKIN_Top_Background() . '" bgcolor="#' . SKIN_Top_BgColor() . '" ' .
         'border="0" cellspacing="3" cellpadding="0" width="100%"><tr><td><table border="0" cellspacing="0" cellpadding="3"><tr><td><img src="/ufc/rapid/openims/'.(N_OpenIMSCE() ? "openimsce50.jpg" : "openims50.gif").'">&nbsp;</center></td><td><center><font color="#000000" size="5" face="arial,helvetica"><b>';
    if ($mode=="cms") {
      echo ML("CMS","CMS");
      if (!$submode) $submode = "assigned";
    } else if ($mode=="dbm") {
      echo ML("DBM","DBM");
    } else if ($mode=="dms") {
      echo ML("DMS","DMS");
      if (!$submode) $submode = "documents";

      // if currentobject is deleted make currentobject empty
      if($currentobject) {
        $object = MB_Ref("ims_" . $supergroupname . "_objects",$currentobject);
        if(($object["preview"]!="yes") && ($object["published"]!="yes")) $currentobject = "";
      }

    } else if ($mode=="ems") {
      echo ML("EMS","EMS");
      if (!$submode) $submode = "searchemails";
    } else if ($mode=="bpms") {
      echo ML("BPMS", "BPMS");
    } else if ($mode=="ps") {
      SHIELD_NeedsGlobalRight ($supergroupname, "portalmanagement");
      echo ML("PS","PS");
      if (!$submode) $submode = "portlets";
    } else if ($mode=="admin") {
      if ($submode=="usrhistory")
        $wide = true;
      if ($submode=="securitysection") {
        if (!(SHIELD_HasGlobalRight ($supergroupname, "system") || (SHIELD_HasGlobalRight ($supergroupname, "system", $mysecuritysection)))) {
          SHIELD_Unauthorized ();
        }
      } else {
        SHIELD_NeedsGlobalRight ($supergroupname, "system");
      }
      echo ML("Admin","Admin");
      if (!$submode) $submode = "users";
    }  else if ($mode=="history") {
      echo ML("Historie", "History");
      $wide = true;
    } else if ($mode=="related") {
      echo ML("Gekoppeld", "Connected");
      $wide = !($myconfig[$sgn]["multifile"] == "yes" and $myconfig[$sgn]["multifileblock_in_related"] == "yes");
    } else if ($mode=="shortcuts") {
      echo ML("Snelkoppelingen", "Shortcuts");
      $wide = true;
    } else if ($mode=="compare") {
      echo ML("Vergelijken", "Compare");
      $wide = true;
    } else if ($mode=="fields") {
      echo ML("Velden", "Fields");
    } else if ($mode=="search") {
      echo ML("Zoeken", "Search");
      $wide = true;
    } else if ($mode=="pers") {
      echo ML("Instellingen","Settings");
    } else if ($mode=="wms") { // DokuWiki
      if (!$submode) $submode = "access";
      echo ML("WMS", "WMS");
    } else {
      echo ML("Informatie Management Server", "Information Management Server");
    }
    echo '</b></font><br><font color="#000000" size="2" face="arial,helvetica">';
    echo SHIELD_CurrentUserName($siteinfo["sitecollection"]);

    // 20091221 KvD IJSSELGROEP uitlogknop in admin / dms
    global $myconfig;
    $sgn = IMS_Supergroupname();
    if (is_callable($myconfig[$sgn]["functionlogoff"]) && $myconfig[$sgn]["cookielogin"] == "yes") {
      $logouturl = $myconfig[$sgn]["functionlogoff"]($sgn, $mode);
      if ($logouturl) {
        echo "<br /><a class=\"ims_navigation\" href=\"$logouturl\">" . ML("Uitloggen", "Log off") . "</a>";
      }
    }
    ///
    if($myconfig["showhttphost"]=="yes") echo '</font><br><font color="#000000" size="-2" face="arial,helvetica">'.getenv("HTTP_HOST");

    echo '</font></center></td><td><center><table width=10><tr><td><center>';
    echo '<font color="#000000" size="6" face="arial,helvetica"><b>';
    echo '</b></font></center></td></tr></table>';

    if ($back) {
      echo '</center></td><td><center>';
      echo '<a class="ims_navigation" title="'.ML("Terug","Back").'" href="'.$back.'">';
      echo '<img border="0" src="/ufc/rapid/openims/back.gif"><br>';
      echo ML("Terug","Back").'</a>';
    }

    echo '</center></td><td><center>';
    //dddd
    //echo '<a class="ims_navigation" title="'.ML("Ververs","Refresh").'" href="javascript:tmp=window.location; window.location=tmp;">';
    echo '<a class="ims_navigation" title="'.ML("Ververs","Refresh").'" href="javascript:window.location=\''.urlencode(N_KeepBefore(N_MyFullURL()."#", "#")).'\';">';
    echo '<img border="0" src="/ufc/rapid/openims/refresh.gif"><br>';
    echo ML("Ververs","Refresh").'</a>';

    // Separator
    echo '</center></td><td><center>';
    echo '<img src="/ufc/rapid/openims/separator.gif">';

    if ( SHIELD_HasProduct ("cms") )
    {
      if ( !isset( $myconfig[IMS_SuperGroupName()]["hascmsright"] ) || SHIELD_HasGlobalRight(IMS_SuperGroupName(), "cmsright") )
      {
        echo '</center></td><td><center>';
        if ($mode=="cms") {
          echo '<a class="ims_active" title="'.ML("Content Management Server","Content Management Server").'" href="/openims/openims.php?mode=cms"><img border="0" src="/ufc/rapid/openims/edit.gif"><br>'.ML("CMS","CMS").'</a>';
        } else {
          echo '<a class="ims_navigation" title="'.ML("Content Management Server","Content Management Server").'" href="/openims/openims.php?mode=cms"><img border="0" src="/ufc/rapid/openims/edit.gif"><br>'.ML("CMS","CMS").'</a>';
        }
      } else if ( $mode=="cms" ) {
        SHIELD_Unauthorized ();
      }
    }
    if (SHIELD_HasProduct ("dms")) {
      echo '</center></td><td><center>';
      if ($myconfig[IMS_SuperGroupName()]["dmsurl"]) {
        $url = $myconfig[IMS_SuperGroupName()]["dmsurl"];
      } else {
        $url = "/openims/openims.php?mode=dms";
      }

      if ($mode=="dms") {
        echo '<a class="ims_active" title="'.ML("Document Management Server","Document Management Server").'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/documents.gif"><br>';
        echo ML("DMS","DMS");
      } else {
        echo '<a class="ims_navigation" title="'.ML("Document Management Server","Document Management Server").'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/documents.gif"><br>'.ML("DMS","DMS").'</a>';
      }
    }

    if (SHIELD_HasGlobalRight ($supergroupname, "system")) {
      echo '</center></td><td><center>';
      if ($mode=="admin") {
        echo '<a class="ims_active" title="'.ML("Gebruikers, groepen, ...","Administer users, groups, ...").'" href="/openims/openims.php?mode=admin"><img border="0" src="/ufc/rapid/openims/group.gif"><br>'.ML("Admin","Admin").'</a>';
      } else {
        echo '<a class="ims_navigation" title="'.ML("Gebruikers, groepen, ...","Administer users, groups, ...").'" href="/openims/openims.php?mode=admin"><img border="0" src="/ufc/rapid/openims/group.gif"><br>'.ML("Admin","Admin").'</a>';
      }
    }

    // Possible self-defined extra buttons
    echo SKIN_ExtraButtons();

    // Separator
    echo '</center></td><td><center>';
    echo '<img src="/ufc/rapid/openims/separator.gif">';

    echo '</center></td><td><center>';
    if ($myconfig[IMS_SuperGroupName()]["customglobalsearch"]) {
      echo '<a class="ims_navigation" title="'.ML("Zoeken","Search").'" href="'.$myconfig[IMS_SuperGroupName()]["customglobalsearch"].'"><img border="0" src="/ufc/rapid/openims/search.gif"><br>'.ML("Zoeken","Search").'</a>';
    } else {
      if ($mode=="search") {
        echo '<a class="ims_active" title="'.ML("Globaal zoeken","Global search").'" href="/openims/openims.php?mode=search"><img border="0" src="/ufc/rapid/openims/search.gif"><br>'.ML("Zoeken","Search").'</a>';
      } else {
        echo '<a class="ims_navigation" title="'.ML("Globaal zoeken","Global search").'" href="/openims/openims.php?mode=search"><img border="0" src="/ufc/rapid/openims/search.gif"><br>'.ML("Zoeken","Search").'</a>';
      }
    }

    // Separator
    echo '</center></td><td><center>';
    echo '<img src="/ufc/rapid/openims/separator.gif">';

    // Customer logo
    if ($myconfig[IMS_SuperGroupName()]["customerlogo"])
    {
       $customer_logo_url = $myconfig[IMS_SuperGroupName()]["customerlogo"] ;
       echo '</center></td></tr></table></td><td><center>';
       echo '<table><tr><td><img border="0" src="'.$customer_logo_url.'"></td></tr></table>';
    }

    // settings

    $url = "/openims/openims.php?mode=pers";
    echo '</center></td><td><center>';
    if ($mode=="pers") {
      echo '<a class="ims_active" title="'.ML("Persoonlijke instellingen","Personal settings").'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/user.gif"><br>'.ML("Instellingen","Settings").'</a>';
    } else {
      echo '<a class="ims_navigation" title="'.ML("Persoonlijke instellingen","Personal settings").'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/user.gif"><br>'.ML("Instellingen","Settings").'</a>';
    }

    // spli tter
    echo '</center></td></tr></table></td><td align="right" width="1%"><table><tr><td><center>';

    // Help
    echo '</center></td><td><center>';
    global $myconfig;
    if ($myconfig[IMS_SuperGroupName()]["helpurl"]) {
      $url = $myconfig[IMS_SuperGroupName()]["helpurl"];
    } else {
      $url = "http://doc.openims.com/openimsdoc_com/2de2c50a8a2054361e8cf6e9a7d6a5b7.php";
    }
    echo '<a target="_blank" class="ims_navigation" title="'.ML("Online help","Online help").'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/help.gif"><br>'.ML("Help","Help").'</a>';

    if ($myconfig[IMS_SuperGroupName()]["hidelanguages"] != "yes") {
      // Separator
      echo '</center></td><td><center>';
      echo '<img src="/ufc/rapid/openims/separator.gif">';

      // Language selection
      echo '</center></td><td><center>';
      echo ML_LanguageSelect($goto);
    }

    echo '</center></td></tr></table></tr></td></table>';

    } // $viewmode!="report"

if ($selectmode=="select") {
  echo "<br>&nbsp;&nbsp;&nbsp;<font face=\"arial\" color=\"ff0000\" ><b>".ML("Alle gevonden documenten zijn geselecteerd.", "All found documents have been selected.")."</b></font><br>";
}
if ($selectmode=="deselect") {
  echo "<br>&nbsp;&nbsp;&nbsp;<font face=\"arial\" color=\"ff0000\" ><b>".ML("Alle gevonden documenten zijn ge-de-selecteerd.", "All found documents have been deselected.")."</b></font><br>";
}

if(!function_exists("startblock")) {
  function startblock ($title, $type="", $helpspecs="" , $id="" , $hideRow = false )
  {
    uuse("flex");
    FLEX_LoadSupportFunctions(IMS_SuperGroupName());
    if(function_exists("startblock_extra")) {
      startblock_extra ($title, $type, $helpspecs , $id , $hideRow );
      return;
    }

    global $debug;
    if ($debug=="ouif") {
      $x = debug_backtrace();
      echo "openims.php line: ".$x[0]["line"];
    }
    $style = "portal_".$type;
    T_Start($style, array("extra-table-props" => 'width="100%" ' . ($id!=''?'id="'.$id.'"':'') ));

// http://www.google.com?oid=$object_id&stat=$status

    if ($helpspecs) {
      global $currentobject;
      global $myconfig;
      $helplink = $helpspecs[0];
      $helptext = $helpspecs[1];
      $helplink = str_replace ('$object_id', $currentobject, $helplink);
      $helplink = str_replace ('$status', SHIELD_CurrentStageName (IMS_SuperGroupName(), $currentobject), $helplink);
      $ico = $myconfig[IMS_SuperGroupName()]["workflowhelpicon"];
      echo $title;
      echo " <a target=\"_blank\" title=\"$helptext\" href=\"$helplink\"><img border=0 src=\"$ico\"></a>";
    } else {
      echo $title;
    }
    if ( !$hideRow ) // Hide erow in case of treeview
       T_NewRow();
  }
}

if(!function_exists("endblock")) {
  function endblock()
  {
    TE_End();
    echo "<font size=1><br></font>";
  }
}

if(!function_exists("startblock_fast")) {
  function startblock_fast($a, $b="", $c="") // flushable blocks (but not in IE :((()
  {
    ob_start();
    startblock($a, $b, $c);
    $guid = N_Guid();
    echo $guid;
    endblock();
    $blockcontent = ob_get_clean();
    echo N_KeepBefore($blockcontent, $guid);
    global $endblock_fast;
    $endblock_fast[] = N_KeepAfter($blockcontent, $guid);
  }
}
if(!function_exists("endblock_fast")) {
  function endblock_fast()
  {
    global $endblock_fast;
    echo array_pop($endblock_fast);
  }
}

?>

</td></tr></table></td></tr></table>
<? if ($viewmode!="report") { ?>
<?
    $skin_horizontalseparator = SKIN_HorizontalSeparator();
?>
<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td background="<? echo $skin_horizontalseparator; ?>"><img src="<? echo $skin_horizontalseparator ; ?>"></td></tr></table>
<?
    if ( function_exists( 'SKIN_drawMenu' ) )
    {
      SKIN_preventMenuOutputEnd( "MAINMENU" );
      echo SKIN_drawmenu( $O_IMS_MENU );
    }
?>
<table background="<? echo SKIN_Background(); ?>" bgcolor=#<? echo SKIN_BgColor(); ?> border="0" cellspacing="10" cellpadding="0" width="100%"><tr valign="top">
<? }  else { ?>
<table border="0" cellspacing="10" cellpadding="0" width="100%"><tr valign="top">
<? } ?>
<?
  if ($submode=="dmsview" && $dmsviewid) {
    $flexspecs = FLEX_LoadLocalComponent(IMS_SuperGroupName(), "dmsview", $dmsviewid);
    if ($flexspecs["viewmode"]) $viewmode = $flexspecs["viewmode"];
  }
  if ($submode=="autotableview" && $autotableviewid) {
    $flexspecs = FLEX_LoadLocalComponent(IMS_SuperGroupName(), "autotableview", $autotableviewid);
    if ($flexspecs["viewmode"]) $viewmode = $flexspecs["viewmode"];
  }
  if ($wide) {
    echo "<td width=5%>&nbsp;";
  } else if ($viewmode=="report" || $viewmode=="noleftcol" || $viewmode=="norightorleftcol") {
    echo "<td width=1%>&nbsp;";
  } else {
//    if ( function_exists("SKIN_preventEmptyColumnStart") )
//      SKIN_preventEmptyColumnStart( "LEFT" );
//    else
      echo "<td width=20% class='openims_left_column'>";
?>
<? // LLEFT
  if ($mode=="pers") {
    startblock (ML ("Persoonlijke instellingen","Personal settings"), "nav");

    // change password
    global $myconfig;
    if ($myconfig[IMS_Supergroupname()]["allowchangepassword"]!="no") {
      $userobj = MB_Ref ("shield_".IMS_SuperGroupName()."_users", SHIELD_CurrentUser(IMS_SuperGroupName()));
      if (!$userobj["ldap"]) {
        $wwform = SHIELD_ChangePasswordForm (IMS_SuperGroupName(), SHIELD_CurrentUser(IMS_SuperGroupName()));
        $url = FORMS_URL ($wwform);
        echo '<a class="ims_navigation" href="'.$url.'">'.ML("Wachtwoord wijzigen","Change password").'</a><br>';
      }
    }

    /* LF210100125: because of new myconfig settings, it has become possible that the user can choose between inline and inplace, or between MS-word and inplace.
     * The text "Deactive inline HTML editor" has been replaced with "Active MS-Word" in all situations.
     */
    $htmleditors = array();
    if ($myconfig[IMS_SuperGroupName()]["useinlinehtmleditoronly"] != "yes") {
      $htmleditors[] = array("internal" => "no", "short" => "MS-Word", "long" => "MS-Word");
    }
    if ($myconfig[IMS_SuperGroupName()]["usetinymceinplace"] == "yes" ||
        $myconfig[IMS_SuperGroupName()]["usetinymceinplacebydefault"] == "yes" ||
        $myconfig[IMS_SuperGroupName()]["usetinymceinplaceonly"] == "yes") {
      $htmleditors[] = array("internal" => "inplace", "short" => "inplace", "long" => "inplace HTML editor");
    }
    if (($myconfig[IMS_SuperGroupName()]["allowinlinehtmleditor"] == "yes" ||
         $myconfig[IMS_SuperGroupName()]["useinlinehtmleditorbydefault"] == "yes" ||
         $myconfig[IMS_SuperGroupName()]["useinlinehtmleditoronly"] == "yes") &&
        ($myconfig[IMS_SuperGroupName()]["usetinymceinplaceonly"] != "yes")) {
      $htmleditors[] = array("internal" => "yes", "short" => "inline", "long" => "inline HTML editor");
    }
    if (count($htmleditors) >= 2) {
      $userobj = MB_Ref ("shield_".IMS_SuperGroupName()."_users", SHIELD_CurrentUser());
      $currenthtmleditor = $userobj["inlineeditor"];
      if (!$currenthtmleditor) {
        if ($myconfig[IMS_SuperGroupName()]["usetinymceinplacebydefault"] == "yes") {
          $currenthtmleditor = "inplace";
        } elseif ($myconfig[IMS_SuperGroupName()]["useinlinehtmleditorbydefault"] == "yes" && $myconfig[IMS_SuperGroupName()]["usetinymce"] == "yes") {
          $currenthtmleditor = "yes";
        } else {
          $currenthtmleditor = "no";
        }
      }
      foreach ($htmleditors as $i => $editorspecs) {
        if ($editorspecs["internal"] == $currenthtmleditor) {
          $currenti = $i;
          $nexti = $i+1;
          if ($nexti >= count($htmleditors)) $nexti = 0;
          break;
        }
        $nexti = 0; // Shouldnt happen
      }
      $form = array();
      $form["input"]["user_id"] = SHIELD_CurrentUser(IMS_SuperGroupName());
      $form["input"]["editor"] = $htmleditors[$nexti]["internal"];
      $form["postcode"] = '
        $userobj = &MB_Ref ("shield_".IMS_SuperGroupName()."_users", $input["user_id"]);
        $userobj["inlineeditor"]=$input["editor"];
      ';
      $url = FORMS_URL ($form);
      echo '<a class="ims_navigation" href="'.$url.'">'.ML("Activeer", "Activate") . " " . $htmleditors[$nexti]["long"] . " (" . ML("nu", "now") . ": " . $htmleditors[$currenti]["short"] . ")<a/><br/>";

    }

    if ($myconfig[IMS_Supergroupname()]["allowautologon"]!="no" && $myconfig[IMS_Supergroupname()]["cookielogin"] != "yes") {
      if (!SHIELD_TestAutoLogon()) {
        $form = array();
        $form["postcode"] = '
          SHIELD_ActivateAutoLogon();
        ';
        $url = FORMS_URL ($form);
        echo '<a class="ims_navigation" href="'.$url.'">'.ML("Activeer automatisch inloggen","Activate automatic logon").'</a><br>';
      } else {
        $form = array();
        $form["postcode"] = '
          SHIELD_DeactivateAutoLogon();
        ';
        $url = FORMS_URL ($form);
        echo '<a class="ims_navigation" href="'.$url.'">'.ML("Deactiveer automatisch inloggen","Deactivate automatic logon").'</a><br>';
      }
    }

    if (SHIELD_HasProduct ("dms")) {
      if ($submode=="signals") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=pers&submode=signals">'.ML("Signalering","Signals").'</a><br>';
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=pers&submode=signals">'.ML("Signalering","Signals").'</a><br>';
      }
    }

  

    if ($myconfig[IMS_Supergroupname()]["customsetting"]) {
      $result = "";
      if ($myconfig[IMS_Supergroupname()]["customsetting"]["urlcode"]) {
        eval ($myconfig[IMS_Supergroupname()]["customsetting"]["urlcode"]);
      }
      if ($result) {
        echo '<a class="ims_navigation" href="'.$result.'">'.$myconfig[IMS_Supergroupname()]["customsetting"]["text"].'</a><br>';
      } else if ($myconfig[IMS_Supergroupname()]["customsetting"]["url"]) {
        echo '<a class="ims_navigation" href="'.$myconfig[IMS_Supergroupname()]["customsetting"]["url"].'">'.$myconfig[IMS_Supergroupname()]["customsetting"]["text"].'</a><br>';
      }
    }

    if ($myconfig[IMS_SupergroupName()]["multiapprove"] == "yes")
    {
      if ($submode=="multiapprove") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=pers&submode=multiapprove">'.ML("Distributielijsten","Distribution lists").'</a><br>';
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=pers&submode=multiapprove">'.ML("Distributielijsten","Distribution lists").'</a><br>';
      }
    }
    if ($myconfig[IMS_SupergroupName()]["trackinglist"] == "yes")
    {
      if ($submode=="trackinglist") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=pers&submode=trackinglist">'.ML("Volglijst","Tracking list").'</a><br>';
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=pers&submode=trackinglist">'.ML("Volglijst","Tracking list").'</a><br>';
      }
    }

    endblock();
  }


  if ($mode=="dms" && $viewmode!="report") {
    if ($submode=="dmsview") {
      $list = FLEX_LocalComponents (IMS_SuperGroupName(), "dmsview");
      $specs = $list[$dmsviewid];
      $hidedocsblock = $specs["hidedocsblock"];
    }
    if ($submode=="autotableview") {
      $list = FLEX_LocalComponents (IMS_SuperGroupName(), "autotableview");
      $specs = $list[$autotableviewid];
      $hidedocsblock = $specs["hidedocsblock"];
    }
    if(!($hidedocsblock || ($myconfig[IMS_SuperGroupName()]["hidedocsblock"]=="yes" && $submode=="cases"))) {
      if ( $submode=="search" && function_exists("SKIN_preventMenuOutputStart") ) SKIN_preventMenuOutputStart( "DMSSEARCH" );
     startblock (ML ("Documenten","Documents"), "nav");

      if ( function_exists( "SKIN_preventMenuOutputStart" ) ) SKIN_preventMenuOutputStart( "DMS" );
      global $myconfig;

      if ($myconfig[IMS_SuperGroupName()]["projectfilter"]=="advanced") {
        if ($myconfig[IMS_SuperGroupName()]["show_generic"] != "no") {
          $generictext = $myconfig[IMS_SuperGroupName()]["generictext"];
          if (!$generictext) $generictext = ML("Algemeen", "Generic");
          if ($submode=="documents" && (substr($currentfolder, 0, 1)!="(")) {
      echo '<a class="ims_active" href="/openims/openims.php?mode=dms&submode=documents">'.$generictext.'</a><br>';
          } else {
      echo '<a class="ims_navigation" href="/openims/openims.php?mode=dms&submode=documents">'.$generictext.'</a><br>';
          }
        }
      } else {
        if ($submode=="documents") {
          echo '<a class="ims_active" href="/openims/openims.php?mode=dms&submode=documents">'.ML("Alle","All").'</a><br>';
        } else {
          echo '<a class="ims_navigation" href="/openims/openims.php?mode=dms&submode=documents">'.ML("Alle","All").'</a><br>';
        }
      }
      if ($myconfig[IMS_SuperGroupName()]["show_perproject"] != "no") {
       if ($myconfig[IMS_SuperGroupName()]["projectfilter"]=="advanced") {
        $percasetext = $myconfig[IMS_SuperGroupName()]["percasetext"];
        if (!$percasetext) {
          $casetext = strtolower($myconfig[IMS_SuperGroupName()]["casetext"]);
          if (!$casetext) $casetext = ML("dossier", "case");
          $percasetext = "Per " . $casetext;
        }
        if ($submode=="cases" || (substr($currentfolder, 0, 1)=="(")) {
          echo '<a class="ims_active" href="/openims/openims.php?mode=dms&submode=cases">'.$percasetext.'</a><br>';
        } else {
          echo '<a class="ims_navigation" href="/openims/openims.php?mode=dms&submode=cases">'.$percasetext.'</a><br>';
        }
      } else if ($myconfig[IMS_SuperGroupName()]["projectfilter"]!="no") {
        if ($myconfig[IMS_SuperGroupName()]["projectstext"]) {
          if ($submode=="projects") {
            echo '<a class="ims_active" href="/openims/openims.php?mode=dms&submode=projects">'.ML("Per","Per")." ".strtolower($myconfig[IMS_SuperGroupName()]["projecttext"]).'</a><br>';
          } else {
            echo '<a class="ims_navigation" href="/openims/openims.php?mode=dms&submode=projects">'.ML("Per","Per")." ".strtolower($myconfig[IMS_SuperGroupName()]["projecttext"]).'</a><br>';
          }
        } else {

          if ($submode=="projects") {
            echo '<a class="ims_active" href="/openims/openims.php?mode=dms&submode=projects">'.ML("Per project","Per project").'</a><br>';
          } else {
            echo '<a class="ims_navigation" href="/openims/openims.php?mode=dms&submode=projects">'.ML("Per project","Per project").'</a><br>';
          }
         }
        }
      }
      if ($myconfig[IMS_SuperGroupName()]["show_assigned"] != "no") {
        if ($submode=="alloced") {
          echo '<a class="ims_active" href="/openims/openims.php?mode=dms&submode=alloced">'.ML("Toegewezen","Assigned").'</a><br>';
        } else {
          echo '<a class="ims_navigation" href="/openims/openims.php?mode=dms&submode=alloced">'.ML("Toegewezen","Assigned").'</a><br>';
        }
      }
      if ($myconfig[IMS_SuperGroupName()]["show_recentlychanged"] != "no") {
        if ($submode=="recent") {
          echo '<a class="ims_active" href="/openims/openims.php?mode=dms&submode=recent">'.ML("Recent gewijzigd","Recently changed").'</a><br>';
        } else {
          echo '<a class="ims_navigation" href="/openims/openims.php?mode=dms&submode=recent">'.ML("Recent gewijzigd","Recently changed").'</a><br>';
        }
      }
      
//      if ( function_exists("SKIN_preventMenuOutputEND") ) SKIN_preventMenuOutputEnd( "DMS" );

      function cmp_dmsview ($v1, $v2) {
        if ($v1["sort"]<$v2["sort"]) {
          return -1;
        } else if ($v1["sort"]>$v2["sort"]) {
          return 1;
        } else {
          return 0;
        }
      }
      $list = FLEX_LocalComponents (IMS_SuperGroupName(), "dmsview");
      uasort  ($list, 'cmp_dmsview');
      foreach ($list as $id => $specs) {
        $result = true;
        eval ($specs["code_condition"]);
        if ($result) {
          $url = '/openims/openims.php?mode=dms&submode=dmsview&dmsviewid='.$id;
          if ($specs["code_url"]) $url = N_Eval ($specs["code_url"], get_defined_vars(), "url");
          $href = 'href="'. $url .'"';

          if ($dmsviewid==$id) {
            echo '<a class="ims_active" '. $href. '>'.$specs["title"].'</a><br>';
          } else {
            echo '<a class="ims_navigation" '. $href. '>'.$specs["title"].'</a><br>';
          }
        }
      }

      $list = FLEX_LocalComponents (IMS_SuperGroupName(), "autotableview");
      uasort  ($list, 'cmp_dmsview');
      foreach ($list as $id => $specs) {
        $result = false;
        $result = N_Eval ($specs["code_condition"], get_defined_vars(), "result");
        if ($result) {

// gv 11-11-2009
// $specs["code_url"] added so that the $url can be altered in the autotableview userinterface

          $url = '/openims/openims.php?mode=dms&submode=autotableview&autotableviewid='.$id;
          if ($specs["code_url"]) $url = N_Eval ($specs["code_url"], get_defined_vars(), "url");
          $href = 'href="'. $url .'"';

          if ($autotableviewid==$id) {
            $theclass = 'class="ims_active"';
          } else {
             $theclass = 'class="ims_navigation"';
          }
          echo '<a '.$theclass.' '. $href .'>'.$specs["title"].'</a><br>';
        }
      }

     if ( function_exists("SKIN_preventMenuOutputEND") ) SKIN_preventMenuOutputEnd( "DMS" );

//ericd 030609 aanpassingen DMS document blok (links boven)
//1) extra myconf show_search: no, dan geen "zoeken" link
//2) als wel link, dan middels extra myconf customdmssearch een andere url opgeven

if ($myconfig[IMS_SuperGroupName()]["show_search"] !== "no") {

    if ($submode=="search") {
      if($myconfig[IMS_SuperGroupName()]["customdmssearch"])
         echo '<a class="ims_active" href="'.$myconfig[IMS_SuperGroupName()]["customdmssearch"].'">'.ML("Zoeken","Search").'</a><br>';
      else
         echo '<a class="ims_active" href="/openims/openims.php?mode=dms&submode=search">'.ML("Zoeken","Search").'</a><br>';
      $q1 = $q;
    } else {
      if($myconfig[IMS_SuperGroupName()]["customdmssearch"])
         echo '<a class="ims_navigation" href="'.$myconfig[IMS_SuperGroupName()]["customdmssearch"].'">'.ML("Zoeken","Search").'</a><br>';
      else
         echo '<a class="ims_navigation" href="/openims/openims.php?mode=dms&submode=search">'.ML("Zoeken","Search").'</a><br>';
    }

}

/*
      if ($submode=="search") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=dms&submode=search">'.ML("Zoeken","Search").'</a><br>';
        $q1 = $q;
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=dms&submode=search">'.ML("Zoeken","Search").'</a><br>';
      }
*/

//ericd 030609 aanpassingen DMS document blok (links boven)
//3) middels myconf mycustomdmssearchbox: maak andere dan de standaard zoekbox (textinput, submit, plus scope radio buttons)
// functie myCustomDMSSearchBox ($q, $currentfolder, $submode, $sgn) levert de nieuwe content op (naar eigen inzicht op te zetten)

      if ($submode!="search") {

    //ericd 020609 echo eigen searchbox
  if ($myconfig[IMS_SuperGroupName()]["mycustomdmssearchbox"] == "yes") {
    if (function_exists ("myCustomDMSSearchBox"))
      echo myCustomDMSSearchBox($q1, $currentfolder, $submode, IMS_SuperGroupName());
    else
      echo ML("Fout: myCustomDMSSearchBox bestaat niet", "Error: myCustomDMSSearchBox does not exist");
    } else {
    //hier gaat de originele code weer verder

      // LF: search box with radio buttons to switch between searching "everything" or "this case / generic"
        if (($myconfig[IMS_SuperGroupName()]["casesearch"] == "yes") && ($submode=="documents" || $currentfolder)) {
      // LF20080403: Added #results-anchor in the form action.  This only has an effect if the user ends up
      // in "advanced search"; the basic search result screen doesnt define this anchor.
  ?>


  <?
  /*
     DvG - Adjust default search index

     Hints:
      * make configurable in siteconfig
      * add searchmode=advanced input type
      * optioneel hidden meegeven input type, name = index, value is default zoekindex
      * heel sjiek: eval van stukje code toevoegen (code in siteconfig), zodat zelf bijvoorbeeld een uitklapbox
        kan worden gemaakt met de voor de gebruiker te kiezen indexen. Ook hier is weer een 'default value'
        handig.

  */
?>

<br>
<table cellpadding=0 cellspacing=0>
  <form action="<? echo N_MyBareURL(); ?>#results" method="put">
    <tr><td>
      <input type="hidden" name="mode" value="dms">
      <input type="hidden" name="submode" value="search">
      <input title="Zoektermen" type="text" name="q" class="style10px inputText" size="18" value="<? echo $q1; ?>"><br>
    </td><td>
      &nbsp;
    </td><td>
      <input title="<? echo ML("Zoeken in documenten","Search in documents"); ?>" type="submit" class="inputButton" value="<? echo ML("Zoeken","Search"); ?>">
    </td></tr>
    <tr><td colspan="3" style="font-size: 12px;">
<?
//ericd 050609 hierboven stond </tr><td colspan="3" style="font-size: 12px;">, maar er begint hier een nieuwe row...

        echo "&nbsp;". ML("Zoek in:", "Search:") . '<input type="radio" name="qscope" value="everything" '.($myconfig[IMS_SuperGroupName()]["casesearchbydefault"] == "yes" ? "" : "checked").'>' . ML("Alles", "Everything");
        $p = strpos ($currentfolder, ")");
        if ($p) {
          $case_id = substr($currentfolder, 0, $p+1);
          $casetext = $myconfig[IMS_SuperGroupName()]["casetext"];
          if (!$casetext) $casetext = ML("dossier", "case");
          echo '<input type="radio" name="qscope" value="case" '.($myconfig[IMS_SuperGroupName()]["casesearchbydefault"] == "yes" ? "checked" : "").'>' . ML("Dit", "This") . " " . $casetext;
          echo '<input type="hidden" name="qcase" value="'.$case_id.'">';
        } else {
          $generictext = $myconfig[IMS_SuperGroupName()]["generictext"];
          if (!$generictext) $generictext = ML("Algemeen", "Generic");
          echo '<input type="radio" name="qscope" value="generic" '.($myconfig[IMS_SuperGroupName()]["casesearchbydefault"] == "yes" ? "checked" : "").'>' . $generictext;
        }
?>    </td></tr>
  </form>
</table>
<?
        } else { // default search box
?>
<br>
<table cellpadding=0 cellspacing=0>
  <form action="<? echo N_MyBareURL(); ?>" method="put">
    <tr><td>
      <input type="hidden" name="mode" value="dms">
      <input type="hidden" name="submode" value="search">
      <input title="Zoektermen" type="text" name="q" size="18" class="style10px" value="<? echo $q1; ?>"><br>
    </td><td>
      &nbsp;
    </td><td>
      <input title="<? echo ML("Zoeken in documenten","Search in documents"); ?>" type="submit" class="inputButton" value="<? echo ML("Zoeken","Search"); ?>">
    </td></tr>
  </form>
</table>
<?
        }



       }//end if myCustomDMSSearchBox
      }
      endblock();
      if ( $submode=="search" && function_exists("SKIN_preventMenuOutputEND") ) SKIN_preventMenuOutputEnd( "DMSSEARCH" );
    }
    if ($submode=="dmsview") {
      $list = FLEX_LocalComponents (IMS_SuperGroupName(), "dmsview");
      $specs = $list[$dmsviewid];
      if ($specs["recheckcondition"]) {
        $result = false;
        eval ($specs["code_condition"]);
        if (!$result) N_Redirect("/openims/openims.php?mode=dms");
      }
      eval (N_GeneratePreEvalCleanupCode());
      eval ($specs["code_contentgenerator2"]);
      eval (N_GeneratePostEvalCleanupCode ($specs["code_contentgenerator2"]));
      if ($content) {
        startblock ($specs["title2"], "docnav");
        echo $content;
        endblock();
      }
    }

    
    if ($submode=="cases") {
    // portlet with fixed case filters
    // Categories block
      global $myconfig;
      if ($myconfig[IMS_SuperGroupName()]["casetypes"]=="yes") {
        $sgn = IMS_SuperGroupName();
        $table = "ims_".$sgn."_case_types";
        $specs = array();
        $specs["value"] = '$record["name"]';
        $specs["sort"] = '$record["default"]!=="x"';
        if($myconfig[IMS_SuperGroupName()]["casetypessort"]!=="no") $specs["sort"] = '$record["name"]';
        $categories = MB_TurboMultiQuery($table,$specs);

        if (!$casetype){
          $table2 = "ims_".$sgn."_case_data";
          $specs2 = array();
          $specs2["select"]['$record["category"]'] = false;
          $list = MB_TurboMultiQuery($table2,$specs2);
          foreach($list as $item){
            $obj = &mb_ref($table2,$item);
            $obj["category"] = "general";
            unset($obj);
             // LF: So this morning one of my dossiers didnt have a shorttitle / longtitle anymore,
             // and when I looked more closely, it was the "last" record (according to MB_AllKeys) and it contained
             // garbage and I have no idea where the garbage came from, except that it happened not through
             // eval/codetester but simply through clicking on /openims/openims.php?mode=dms&submode=cases,
          }

          $specs["select"]['$record["default"]'] = "x";
          $list = MB_TurboMultiQuery($table,$specs);
          foreach ($list as $key => $dummy) {
            $casetype = $key;
          }
        }

        startblock(ML("Categorie&euml;n","Types"),"nav");
        foreach($categories as $id => $name) {
          if (SHIELD_CanViewDmsCasetype($sgn,SHIELD_CurrentUser(),$id)){
            if ($casetype==$id&&!$_REQUEST["casetype_group"]) { // JG - NSPOORT
              echo '<a class="ims_active" href="/openims/openims.php?mode=dms&submode=cases&casetype='.$id.'">'.$name.'</a><br>';
            } else {
              echo '<a class="ims_navigation" href="/openims/openims.php?mode=dms&submode=cases&casetype='.$id.'">'.$name.'</a><br>';
            }
          }
        }

        if ( function_exists("dms_casetype_groups") ) // JG - NSPOORT
        {
          $casetype_groups = dms_casetype_groups( $sgn );

          foreach( $casetype_groups AS $casetype_group_key => $casetype_group ) 
          {
            $group_casetypes = array();
            foreach( $casetype_group["casetypes"] AS $casetype_index => $casetypeid )
            {
              if ( SHIELD_CanViewDmsCasetype( $sgn , SHIELD_CurrentUser() , $casetypeid ) )
              {
                $group_casetypes[$casetypeid] = N_htmlentities( MB_Fetch($table , $casetypeid , "name" ) );
              } 
            }
            if ( count( $group_casetypes ) )
              echo '<a title="'.implode(', ',$group_casetypes).'" class="' . ( $_REQUEST["casetype_group"] == $casetype_group_key ? "ims_active":"ims_navigation" ) . '" href="/openims/openims.php?mode=dms&submode=cases&casetype_group='.$casetype_group_key.'">' . $casetype_group['name'] . '</a><br>';
          }
        }

        endblock();
      }

      global $myconfig;
      $fixedcasefilters = $myconfig[IMS_SuperGroupName()]["casefilterlist"];

      if(is_array($fixedcasefilters)) {
        // use tempsort to sort list case sensitive: make array of (lowercased) keys and sort
        $tempsort = array();
        foreach($fixedcasefilters as $cases=>$casefilter) {
          $tempsort[strtolower($cases)] = $cases;
        }
        ksort($tempsort);

        startblock (ML("Vaste dossierfilters","Fixed case filters"), "docnav");
        T_Start ("ims",array ("noheader"=>"yes", "extra-table-props" => 'width="100%"'));
        foreach($tempsort as $dummy=>$cases) {
          $casefilter = $fixedcasefilters[$cases];
          $url = "/openims/openims.php?" . $casefilter;
          echo '<a class="ims_navigation" href="' . $url . '" title="' . $cases. '">' . $cases. '</a>';
          T_NewRow ();
        }
        TE_End ();
        endblock();
        unset($tempsort);
        unset($fixedcasefilters);
      }
    }

		
    if ($submode=="documents" || ($submode=="projects" && $rootfolder)) {
      if (!$currentfolder) {
        if ($submode=="documents") {
          $currentfolder="root";
        } else {
          $currentfolder=$rootfolder;
        }
      }
      if ($submode=="documents") {
        if (substr ($currentfolder, 0, 1)=="(") {
          $case_id = substr ($currentfolder, 0, strpos ($currentfolder, ")")+1);

          // record case id for most recently used list
          if($myconfig[IMS_SuperGroupName()]["dontuserecentcaselist"]=="yes") {
          } else {
            $user_id = SHIELD_CurrentUser (IMS_SuperGroupName());
            $user = MB_Ref ("shield_" . IMS_SuperGroupName() . "_users", $user_id);

            if(is_array($user["recent_cases"])) {
              if( array_key_exists($case_id, $user["recent_cases"]) ) {
                unset($user["recent_cases"][$case_id]);
              } else {
                if(count($user["recent_cases"]) >= 10) {
                  array_pop($user["recent_cases"]);
                }
              }
              $user["recent_cases"] = array_reverse($user["recent_cases"], true);
              $user["recent_cases"][$case_id] = $currentfolder;
              $user["recent_cases"] = array_reverse($user["recent_cases"], true);
              $user["force_write"] = time(); // change something in the user record to force writing
            } else {
              $user["recent_cases"][$case_id] = $currentfolder;
            }
            MB_Save("shield_" . IMS_SuperGroupName() . "_users", $user_id, $user);
          }
          $case = MB_Ref ("ims_".IMS_SuperGroupName()."_case_data", $case_id);
/*          if ($myconfig[IMS_SuperGroupName()]["casetext"]) {
            startblock ($case["shorttitle"]." ".strtolower ($myconfig[IMS_SuperGroupName()]["casetext"]), "docnav");
          } else {
            startblock ($case["shorttitle"]." ".ML("dossier","case"), "docnav");
          }*/
         startblock( CASE_visiblecasename() , "docnav" );
        } else {
          startblock (ML("Folders","Folders"), "docnav");
        }

      } else {
        if ($myconfig[IMS_SuperGroupName()]["projecttext"]) {
          startblock ($myconfig[IMS_SuperGroupName()]["projecttext"], "docnav");
        } else {
          startblock (ML("Project","Project"), "docnav");
        }
      }
//      echo "<br>"; // BREAKWEG door JG

      // dvb 24-02-2008
      if ($myconfig[$supergroupname]["folderblock"]["height"]."" != "") {
         echo "<div id='foldersblock' style='height: ".$myconfig[$supergroupname]["folderblock"]["height"]."px; width:".($myconfig[$supergroupname]["folderblock"]["width"]!=''?$myconfig[$supergroupname]["folderblock"]["width"]:'240')."px; overflow: auto;'>";
      }

      $tree = CASE_TreeRef ($supergroupname, $currentfolder);
      if ($submode=="projects") {
        echo TREE_CreateDHTML ($tree, '/openims/openims.php?mode=dms&submode=projects&currentfolder=$id&rootfolder='.$rootfolder, $currentfolder, true, $rootfolder);
      } else {
        echo TREE_CreateDHTML ($tree, '/openims/openims.php?mode=dms&currentfolder=$id', $currentfolder, true, $rootfolder);
      }

      if ($myconfig[$supergroupname]["folderblock"]["height"]."" != "") {
         echo "</div>";
         echo "<script>
                 function folderblock_init() {
                    document.getElementById('folder_".$currentfolder."').scrollIntoView();
                    window.scrollTo(0,0);
                 }
                 window.onload = folderblock_init;
               </script>";
      }


    $openims_favoriteLinkVisible = DMSUIF_canbeFavoriteFolder( $currentfolder )  && !DMSUIF_isFavoriteFolder( $currentfolder );
	$openims_showFolderBlock = SHIELD_HasGlobalRight ($supergroupname, "folders", $mysecuritysection);
	
	if ( $openims_showFolderBlock || $openims_favoriteLinkVisible )
	{
      endblock();
      startblock ($tree["objects"][$currentfolder]["shorttitle"], "docnav");
    }
	if ( $openims_showFolderBlock ) {
//    echo '<b><font size="2" face="arial,helvetica">'.ML("Folder acties","Folder actions").':</font></b>';
      echo "<table><tr><td>&nbsp;&nbsp;</td><td>";

      $form = array();
      $form["title"] = ML("Eigenschappen van","Properties of")." &quot;".$tree["objects"][$currentfolder]["shorttitle"]."&quot;";
      $form["input"]["folder"] = $currentfolder;
      $form["metaspec"]["fields"]["longtitle"]["type"] = "strml5";
      $form["metaspec"]["fields"]["shorttitle"]["type"] = "strml5";
      $foldercolortemplate="";
      global $myconfig;
      if ($myconfig[IMS_SuperGroupName()]["foldercolors"]) {
        $form["metaspec"]["fields"]["foldercolor"]["type"] = "list";
        $form["metaspec"]["fields"]["foldercolor"]["show"] = "visible";
        $form["metaspec"]["fields"]["foldercolor"]["values"] = $myconfig[IMS_SuperGroupName()]["foldercolors"];
        $form["metaspec"]["fields"]["subfolders"]["type"] = "yesno";
        $foldercolortemplate = '
                        <tr><td><font face="arial" size=2><b>'.ML("Kleur","Color").':</b></font></td><td>[[[foldercolor]]]</td></tr>
                        <tr><td><font face="arial" size=2><b>'.ML("Geef alle onderliggende folders deze kleur","Apply this color to all subfolders").':</b></font></td><td>[[[subfolders]]]</td></tr>';
      }
      $form["formtemplate"] = '<body bgcolor=#f0f0f0><br><center><table>
                        <tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[shorttitle]]]</td></tr>
                        <tr><td><font face="arial" size=2><b>'.ML("Omschrijving","Description").':</b></font></td><td>[[[longtitle]]]</td></tr>
                        '.$foldercolortemplate.'
                        <tr><td colspan=2>&nbsp</td></tr>
                        <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                        </table></center></body>';
      $form["precode"] = '
        uuse ("case");
        $tree = CASE_TreeRef (IMS_SuperGroupName(), $input["folder"]);
        $data["shorttitle"] = $tree["objects"][$input["folder"]]["shorttitle"];
        $data["longtitle"] = $tree["objects"][$input["folder"]]["longtitle"];
        $data["foldercolor"] = $tree["objects"][$input["folder"]]["foldercolor"];
      ';
      $form["postcode"] = '
        uuse ("case");
        $tree = &CASE_TreeRef (IMS_SuperGroupName(), $input["folder"]);
        if ($tree["objects"][$input["folder"]]["shorttitle"] != $data["shorttitle"])
          N_Log("History folders", "changed name: \"" . $tree["objects"][$input["folder"]]["shorttitle"] . "\" to \"" . $data["shorttitle"] . "\" ("  . SHIELD_CurrentUserFullName() . ")");
        $tree["objects"][$input["folder"]]["shorttitle"] = $data["shorttitle"];
        if ($tree["objects"][$input["folder"]]["longtitle"] != $data["longtitle"])
          N_Log("History folders", "changed description: \"" . $tree["objects"][$input["folder"]]["longtitle"] . "\" to \"" . $data["longtitle"] . "\" ("  . SHIELD_CurrentUserFullName() . ")");

        $tree["objects"][$input["folder"]]["longtitle"] = $data["longtitle"];
        global $myconfig;
        if ($myconfig[IMS_SuperGroupName()]["foldercolors"]) {
          $tree["objects"][$input["folder"]]["foldercolor"] = $data["foldercolor"];
          if ($data["subfolders"]) {
            $foldercode = \'
              $folder["foldercolor"] = "\'.$data["foldercolor"].\'";
            \';
            TREE_WalkDirectory ($tree, $input["folder"], $foldercode, "");
          }
        }
      ';
      $url = FORMS_URL ($form);
      echo '<table border=0 cellspacing=0 cellpadding=0><tr><td>';
      echo '<a class="ims_navigation" title="'.$form["title"].'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/properties_small.gif"></a>';
      echo '</td><td>&nbsp;</td><td>';
      echo '<a class="ims_navigation" title="'.$form["title"].'" href="'.$url.'">'.ML("Eigenschappen","Properties").'</a>';
      echo '</td></tr></table>';

      $title = ML("Maak nieuwe folder onder","Create a new folder under")." &quot;".$tree["objects"][$currentfolder]["shorttitle"]."&quot;";
      $metaspec = array();
      $metaspec["fields"]["longtitle"]["type"] = "strml5";
      $metaspec["fields"]["shorttitle"]["type"] = "strml5";
      $formtemplate  = '<body bgcolor=#f0f0f0><br><center><table>
                        <tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[shorttitle]]]</td></tr>
                        <tr><td><font face="arial" size=2><b>'.ML("Omschrijving","Description").':</b></font></td><td>[[[longtitle]]]</td></tr>
                        <tr><td colspan=2>&nbsp</td></tr>
                        <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                        </table></center></body>';
      $input["treename"] = $supergroupname."_documents";
      $input["parent"] = $currentfolder;
      $code = '
        uuse ("tree");
        uuse ("case");
        $tree = &CASE_TreeRef (IMS_SuperGroupName(), $input["parent"]);
        if (substr ($input["parent"], 0, 1) == "(") {
          $id = TREE_AddObject ($tree, substr ($input["parent"], 0, strpos ($input["parent"], ")")+1).N_GUID());

        } else {
          $id = TREE_AddObject ($tree);
        }
        $object = &TREE_AccessObject($tree, $id);
        $object["shorttitle"] = $data["shorttitle"];
        $object["longtitle"] = $data["longtitle"];
        TREE_ConnectObject ($tree, $input["parent"], $id);
        N_Log("History folders", "created: name=" . $object["shorttitle"] . ", description=". $object["longtitle"] . ", path=" . IMS_GetDMSDocumentPath (IMS_SupergroupName(), $id) . " (". SHIELD_CurrentUserFullName(). ")");
      ';
      echo '<table border=0 cellspacing=0 cellpadding=0><tr><td>';
      echo FORMS_GenerateExecuteLink ($code, $input, $title, '<img border="0" src="/ufc/rapid/openims/folder.gif">', $title, $metaspec, $formtemplate, 400, 200, 200, 200, "ims_navigation");
      echo '</td><td>&nbsp;</td><td>';
      echo FORMS_GenerateExecuteLink ($code, $input, $title, ML("Nieuwe folder","New folder"), $title, $metaspec, $formtemplate, 400, 200, 200, 200, "ims_navigation");
      echo '</td></tr></table>';
    }
      
    if ( $openims_favoriteLinkVisible )
    {
      $url = DMSUIF_createFavoriteFolderFormsUrl( $currentfolder );
      echo '<table border=0 cellspacing=0 cellpadding=0><tr><td><a class="ims_navigation" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/ico_star.gif"></a></td><td>&nbsp;</td><td><a class="ims_navigation" href="'.$url.'">'.ML("Maak favoriet","add as favorite").'</a></td></tr></table>';
    }
	  
    if ( $openims_showFolderBlock ) {	  

      if (!CASE_RootFolder ($currentfolder)) {
        $title = ML("Verplaats folder","Move folder")." &quot;".$tree["objects"][$currentfolder]["shorttitle"]."&quot;";
        $form = array();
        $form["title"] = $title;
        $form["input"]["treetable"] = CASE_TreeTable (IMS_SuperGroupName(), $currentfolder);
        $form["input"]["treekey"] = CASE_TreeKey (IMS_SuperGroupName(), $currentfolder);
        $form["input"]["parentfolder"] = $tree["objects"][$currentfolder]["parent"];
        $form["input"]["me"] = $currentfolder;
        $form["precode"] = '$data["loc"] = $input["parentfolder"];';
        $form["metaspec"]["fields"]["loc"]["type"] = "tree";
        $form["metaspec"]["fields"]["loc"]["tree"] = 'CASE_TreeRef ("'.$supergroupname.'", "'.$currentfolder.'")';
        $form["metaspec"]["fields"]["loc"]["hide"] = $currentfolder;
        $form["formtemplate"] = '
          <table>
            <tr><td><font face="arial" size=2><b>'.ML("Onder folder","Under folder").':</b></font></td><td>[[[loc]]]</td></tr>
            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';

        $form["postcode"] = '
          uuse ("case");
          if (SHIELD_HasGlobalRight (IMS_SuperGroupName(), "folders", SHIELD_SecuritySectionForFolder (IMS_SuperGroupName(), $data["loc"]))) {
            $tree = &CASE_TreeRef (IMS_SuperGroupName(), $input["me"]);
            TREE_ConnectObject ($tree, $data["loc"], $input["me"]);
            $filecode = \'
              uuse ("ims");
              $object = &IMS_AccessObject (IMS_SuperGroupName(), $file_id);
              $object = SHIELD_UpdateSecuritySection (IMS_SuperGroupName(), $object);
            \';
            TREE_WalkDirectory ($tree, $input["me"], "", $filecode);
            $gotook = "closeme&parentgoto:/openims/openims.php?mode=dms&currentfolder=".$input["me"];
            if ($input["parentfolder"] !=  $tree["objects"][$input["me"]]["parent"])
              N_Log("History folders", "moved: " . $tree["objects"][$input["me"]]["shorttitle"] . ", from " . IMS_GetDMSDocumentPath (IMS_SupergroupName(), $input["parentfolder"]) . " to " . IMS_GetDMSDocumentPath (IMS_SupergroupName(), $tree["objects"][$input["me"]]["parent"]) . " (" . SHIELD_CurrentUserFullName() . ")");
          } else {
            FORMS_ShowError (ML("Foutmelding","Error"), ML("U heeft niet voldoende rechten","You have insufficient rights"), true);
          }
         ';
        $url = FORMS_URL ($form);
        echo '<table border=0 cellspacing=0 cellpadding=0><tr><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/move_small.gif"></a></td><td>&nbsp</td><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'">'.ML("Verplaats folder","Move folder").'</a></td></tr></table>';

        // up
        $form = array();

        $form["input"]["treetable"] = "ims_trees";
        $form["input"]["treekey"] = $supergroupname.'_documents';
        $form["input"]["me"] = $currentfolder;
        $form["metaspec"]["fields"]["amt"]["type"] = "list";
        $tree = CASE_TreeRef ($supergroupname, $currentfolder);

        $list = $tree["objects"][$tree["objects"][$currentfolder]["parent"]]["children"];
        $amount = count ($list);
        $ctr = 0;
        $list_id = array();
        foreach ($list as $id => $dummy) {
          $ctr++;
          $list_id[$ctr] = $id;
          if ($id==$currentfolder) $index = $ctr;
        }
        if ($index!=1) {
          for ($i=$index-1; $i>0; $i--) {
            $form["metaspec"]["fields"]["amt"]["values"][ML("Boven","Above")." ".$tree["objects"][$list_id[$index-$i]]["shorttitle"]." ($i)"] = $i;
          }
          $form["formtemplate"] = '
            <table>
              <tr><td><font face="arial" size=2><b>'.ML("Plaats","Location").':</b></font></td><td>[[[amt]]]</td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          ';
          $form["precode"] = '
            $data["amt"] = 1;

          ';
          $form["postcode"] = '
            $tree = &CASE_TreeRef (IMS_SuperGroupName(), $input["me"]);
            for ($i=0; $i<$data["amt"]; $i++) {
              TREE_MoveObjectUp ($tree, $input["me"]);
            }
            if ($i > 0)
              N_Log("History folders", "moved up: " . $tree["objects"][$input["me"]]["shorttitle"] . " (" . SHIELD_CurrentuserFullName() . ")");
          ';
          $url=FORMS_URL ($form);
          $title=ML("Verplaats folder","Move folder")." &quot;".$tree["objects"][$currentfolder]["shorttitle"]."&quot; ".ML("omhoog","up");
          echo '<table border=0 cellspacing=0 cellpadding=0><tr><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/up_small.gif"></a></td><td>&nbsp</td><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'">'.ML("Omhoog","Up").'</a></td></tr></table>';
        }

        // down
        $form = array();
        $form["input"]["treetable"] = "ims_trees";
        $form["input"]["treekey"] = $supergroupname.'_documents';
        $form["input"]["me"] = $currentfolder;
        $form["metaspec"]["fields"]["amt"]["type"] = "list";
        $tree = CASE_TreeRef ($supergroupname, $currentfolder);
        $list = $tree["objects"][$tree["objects"][$currentfolder]["parent"]]["children"];
        $amount = count ($list);
        $ctr = 0;
        $list_id = array();
        foreach ($list as $id => $dummy) {
          $ctr++;
          $list_id[$ctr] = $id;
          if ($id==$currentfolder) $index = $ctr;
        }
        if ($amount!=$index) {
          for ($i=1; $i<=($amount-$index); $i++) {
            $form["metaspec"]["fields"]["amt"]["values"][ML("Onder","Under")." ".$tree["objects"][$list_id[$index+$i]]["shorttitle"]." ($i)"] = $i;
          }
          $form["formtemplate"] = '
            <table>
              <tr><td><font face="arial" size=2><b>'.ML("Plaats","Location").':</b></font></td><td>[[[amt]]]</td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          ';
          $form["precode"] = '
            $data["amt"] = 1;
          ';
          $form["postcode"] = '
            $tree = &CASE_TreeRef (IMS_SuperGroupName(), $input["me"]);
            for ($i=0; $i<$data["amt"]; $i++) {
              TREE_MoveObjectDown ($tree, $input["me"]);
            }
            if ($i > 0)
              N_Log("History folders", "moved down: " . $tree["objects"][$input["me"]]["shorttitle"] . " (" . SHIELD_CurrentuserFullName() . ")");
          ';
          $url=FORMS_URL ($form);
          $title=ML("Verplaats folder","Move folder")." &quot;".$tree["objects"][$currentfolder]["shorttitle"]."&quot; ".ML("omlaag","down");
          echo '<table border=0 cellspacing=0 cellpadding=0><tr><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/down_small.gif"></a></td><td>&nbsp</td><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'">'.ML("Omlaag","Down").'</a></td></tr></table>';
        }
      }

      // sort
      $form = array();
      $form["input"]["treetable"] = "ims_trees";
      $form["input"]["treekey"] = $supergroupname.'_documents';

      $form["input"]["me"] = $currentfolder;
      $form["postcode"] = '
        $tree = &CASE_TreeRef (IMS_SuperGroupName(), $input["me"]);
        $again = true;
        while ($again && $loops++<10000) {
          $again = false;
          $list = $tree["objects"][$input["me"]]["children"];
          $count = count ($list);
          $ctr = 0;
          foreach ($list as $id => $dummy) if (!$again) {
            $ctr++;
            $list_id[$ctr] = $id;
            if ($ctr>1) {
              if (strtoupper($tree["objects"][$list_id[$ctr]]["shorttitle"]) < strtoupper($tree["objects"][$list_id[$ctr-1]]["shorttitle"])) {
                TREE_MoveObjectDown ($tree, $list_id[$ctr-1]);
                $again = true;
              }
            }
          }
        }
      ';
      $tree = CASE_TreeRef ($supergroupname, $currentfolder);
      $list = $tree["objects"][$currentfolder]["children"];
      $amount = count ($list);
      if ($amount > 1) {
        $url = FORMS_URL ($form);
        $title = ML("Sorteer folders onder","Sort folders under")." &quot;".$tree["objects"][$currentfolder]["shorttitle"]."&quot; ".ML("op alfabet","alphabetically");
        echo '<table border=0 cellspacing=0 cellpadding=0><tr><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/sort.jpg"></a></td><td>&nbsp</td><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'">'.ML("Sorteer","Sort").'</a></td></tr></table>';
      }

    if (!CASE_RootFolder ($currentfolder)) {
      if (!($tree["objects"][$currentfolder]["children"]) && !(
        MB_TurboSelectQuery ("ims_".$supergroupname."_objects", array (
         '$record["directory"]' => $currentfolder,
         '$record["published"]=="yes" || $record["preview"]=="yes"' => true
        ))
      )) {
        $title = ML ("Verwijder folder","Remove folder")." &quot;".$tree["objects"][$currentfolder]["shorttitle"]."&quot;";
        $form = array();
        $form["input"]["sitecollection_id"] = $supergroupname;
        $form["input"]["node"] = $currentfolder;
        $form["postcode"] = '
          uuse ("tree");
          $tree = &CASE_TreeRef (IMS_SuperGroupName(), $input["node"]);
          N_Log("History folders", "deleted: " . $tree["objects"][$input["node"]]["shorttitle"] . ", path=" . IMS_GetDMSDocumentPath (IMS_SupergroupName(), $input["node"]) . " (" . SHIELD_CurrentUserFullname() .")");
          TREE_DeleteObject($tree, $input["node"]);

        ';

        $form["gotook"] = "closeme&parentgoto:/openims/openims.php?mode=dms&currentfolder=".$tree["objects"][$currentfolder]["parent"];
        $url = FORMS_URL ($form);
        echo '<table border=0 cellspacing=0 cellpadding=0><tr><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/delete_small.gif"></a></td><td>&nbsp</td><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'">'.ML("Verwijder folder","Remove folder").'</a></td></tr></table>';
      }
    }
    echo "</td></tr></table>";
    echo '<div class="insteadOfBR"></div>'; // instead of <br/>
    }

    $thefolder = $currentfolder;
    if (!$thefolder) $thefolder = $rootfolder;
    if (!$thefolder) $thefolder = "root";
    $folderobject = array();
    if ($thefolder!="root") {
      $folderobject = &TREE_AccessObject($tree, $thefolder);
    }

    if (SHIELD_HasGlobalRight ($supergroupname, "system") || (SHIELD_HasGlobalRight ($supergroupname, "system", $mysecuritysection) && $folderobject["hassecuritysection"]=="yes")) {

//    echo '<b><font size="2" face="arial,helvetica">'.ML("Administratie","Administration").':</font></b>';
    echo "<table><tr><td>&nbsp;&nbsp;</td><td>";


    $thefolder = $currentfolder;
    if (!$thefolder) $thefolder = $rootfolder;
    if (!$thefolder) $thefolder = "root";



    if (SHIELD_HasGlobalRight ($supergroupname, "system")) { // only for global system managers


    $title = ML ("Vernietig folder, alle onderliggende folders en alle documenten in deze folders",
                 "Destroy folder, all subfolders and all documents in these folders");
    $form = array();
    $form["title"] = ML("Weet u het zeker?","Are you sure?");
    $form["input"]["me"] = $thefolder;
    $form["metaspec"]["fields"]["sure"]["type"] = "list";
    $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
    $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
    $form["formtemplate"] = '
      <table width=100>
        <tr><td colspan=2><font face="arial" color="#ff0000" size=2><b>'.$title.'</b></font></td></tr>
        <tr><td colspan=2>&nbsp;</td></tr>
        <tr><td><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td><td><nobr>[[[sure]]]</nobr></td></tr>
        <tr><td colspan=2>&nbsp</td></tr>
        <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>
    ';
    $form["postcode"] = '
      if ($data["sure"]=="yes") {
        $foldercode = \'
          $tree = &CASE_TreeRef (IMS_SuperGroupName(), "\'.$input["me"].\'");
// 20101112 KvD Verwijder ook folders met ID als bijv. "heelgrootenaam" alleen root aan einde is hoofdfolder
//          if (!strpos (" ".$folder_id, "root")) TREE_DeleteObject($tree, $folder_id);
          if ($folder_id != "root" && substr ($folder_id, -5) != ")root") 
          {
            N_Log("History folders", "deleted: " . $tree["objects"][$folder_id]["shorttitle"] . ", path=" . IMS_GetDMSDocumentPath (IMS_SupergroupName(), $folder_id) . " (" . SHIELD_CurrentUserFullname() .")");
            TREE_DeleteObject($tree, $folder_id);
          }
        \';
        $filecode = \'
          IMS_Delete (IMS_SuperGroupName(), "", $file_id);
        \';
        $tree = &CASE_TreeRef (IMS_SuperGroupName(), $input["me"]);
        $count = TREE_TERRA_WalkDirectory ($tree, $input["me"], $foldercode, $filecode);
        N_Redirect (FORMS_URL (array ("formtemplate"=>\'
          <table>
          <tr><td><font face="arial" size=2><b>\'.ML("De %1 bestanden worden in de achtergrond verwerkt","The %1 files are processed in the background", $count).\'</b></font></td></tr>
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
          </table>
        \', "gotook"=>"closeme")));
      }
    ';
    $url = FORMS_URL ($form);
    echo '<table border=0 cellspacing=0 cellpadding=0><tr><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/delete_small.gif"></a></td><td>&nbsp</td><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'">'.ML("Vernietig folder","Destroy folder").'</a></td></tr></table>';

    $title = ML ("Wijzig metadata van alle documenten in deze folder en onderliggende folders",
                 "Change metadata of all files in this folder and all subfolders");
    $form = array();
    $form["title"] = ML("Weet u het zeker?","Are you sure?");
    $form["input"]["me"] = $thefolder;
    $form["metaspec"]["fields"]["sure"]["type"] = "list";
    $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
    $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
    $form["metaspec"]["fields"]["thefield"]["type"] = "list";
    $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
    if (is_array($allfields)) {
      ksort ($allfields);
      foreach ($allfields as $field => $dummy) {
        $form["metaspec"]["fields"]["thefield"]["values"][$field] = $field;
      }
      $form["metaspec"]["fields"]["value"]["type"] = "string";
      $form["formtemplate"] = '
        <table width=100>
          <tr><td colspan=2><font face="arial" color="#ff0000" size=2><b>'.$title.'</b></font></td></tr>
          <tr><td colspan=2>&nbsp;</td></tr>
          <tr><td><nobr><font face="arial" size=2><b>'.ML("Veld","Field").':</b></font></nobr></td><td><nobr>[[[thefield]]]</nobr></td></tr>
          <tr><td><nobr><font face="arial" size=2><b>'.ML("Waarde","Value").':</b></font></nobr></td><td><nobr>[[[value]]]</nobr></td></tr>
          <tr><td><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td><td><nobr>[[[sure]]]</nobr></td></tr>
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
        </table>
      ';
      $form["postcode"] = '
        if ($data["sure"]=="yes") {
          $tree = &CASE_TreeRef (IMS_SuperGroupName(), $input["me"]);
          $filecode = \'
            $file["meta_\'.$data["thefield"].\'"] = "\'.$data["value"].\'";
            if ($file["published"]=="yes") {
              SEARCH_AddPreviewDocumentToDMSIndex (IMS_SuperGroupName(), $file_id);
              SEARCH_AddDocumentToDMSIndex (IMS_SuperGroupName(), $file_id);
            } else {
              SEARCH_AddPreviewDocumentToDMSIndex (IMS_SuperGroupName(), $file_id);

            }
          \';
          $count = TREE_TERRA_WalkDirectory ($tree, $input["me"], "", $filecode);
          N_Redirect (FORMS_URL (array ("formtemplate"=>\'

            <table>
            <tr><td><font face="arial" size=2><b>\'.ML("De %1 bestanden worden in de achtergrond verwerkt","The %1 files are processed in the background", $count).\'</b></font></td></tr>
            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
            </table>
          \', "gotook"=>"closeme")));
        }
      ';
      $url = FORMS_URL ($form);
      echo '<table border=0 cellspacing=0 cellpadding=0><tr><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/properties_small.gif"></a></td><td>&nbsp</td><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'">'.ML("Bulk metadata","Bulk metadata").'</a></td></tr></table>';
    }

    $title = ML ("Wijzig workflow van alle documenten in deze folder en onderliggende folders",
                 "Change workflow of all files in this folder and all subfolders");
    $form = array();
    $form["title"] = ML("Weet u het zeker?","Are you sure?");
    $form["input"]["me"] = $thefolder;
    $form["metaspec"]["fields"]["sure"]["type"] = "list";
    $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
    $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
    $form["metaspec"]["fields"]["workflow"]["type"] = "list";
    $form["metaspec"]["fields"]["workflow"]["default"] = "edit-publish";

    $wlist = SHIELD_DMSWorkFlows();

    if (is_array($wlist)) reset($wlist);
    if (is_array($wlist)) while (list($wkey)=each($wlist)) {
      if ($wkey!=$key) {
        $workflow = &MB_Ref ("shield_".$supergroupname."_workflows", $wkey);
        $form["metaspec"]["fields"]["workflow"]["values"][$workflow["name"]] = $wkey;
      }
    }
    $form["formtemplate"] = '
      <table width=100>
        <tr><td colspan=2><font face="arial" color="#ff0000" size=2><b>'.$title.'</b></font></td></tr>
        <tr><td colspan=2>&nbsp;</td></tr>
        <tr><td><nobr><font face="arial" size=2><b>'.ML("Workflow","Workflow").':</b></font></nobr></td><td><nobr>[[[workflow]]]</nobr></td></tr>
        <tr><td><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td><td><nobr>[[[sure]]]</nobr></td></tr>
        <tr><td colspan=2>&nbsp</td></tr>
        <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>
    ';
    $form["postcode"] = '
      if ($data["sure"]=="yes") {
        $tree = &CASE_TreeRef (IMS_SuperGroupName(), $input["me"]);
        $filecode = \'
          $file["workflow"] = "\'.$data["workflow"].\'";
        \';
        $count = TREE_TERRA_WalkDirectory ($tree, $input["me"], "", $filecode);
        N_Redirect (FORMS_URL (array ("formtemplate"=>\'
          <table>
          <tr><td><font face="arial" size=2><b>\'.ML("De %1 bestanden worden in de achtergrond verwerkt","The %1 files are processed in the background", $count).\'</b></font></td></tr>
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
          </table>
        \', "gotook"=>"closeme")));
      }
    ';
    $url = FORMS_URL ($form);
    echo '<table border=0 cellspacing=0 cellpadding=0><tr><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/workstep.gif"></a></td><td>&nbsp</td><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'">'.ML("Bulk workflow","Bulk workflow").'</a></td></tr></table>';
    $sgn = IMS_SupergroupName();
    if ($myconfig[$sgn]["dmsbulkoperation"] == "yes")
    {
      $title = ML ("Voer operatie uit met alle documenten in deze folder en onderliggende folders",
                   "Execute operation on all files in this folder and all subfolders");

      $form = array();
      $form["title"] = ML("Weet u het zeker?","Are you sure?");
      $form["input"]["me"] = $thefolder;
      $form["metaspec"]["fields"]["sure"]["type"] = "list";
      $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
      $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
      $form["metaspec"]["fields"]["code"]["type"] = "bigtext";
      $form["formtemplate"] = '
      <table>
        <tr><td colspan=2><font face="arial" color="#ff0000" size=2><b>'.$title.'</b></font></td></tr>
        <tr><td colspan=2>&nbsp;</td></tr>
        <tr><td colspan=2><nobr><font face="arial" size=2><b>'.ML("Code","Code").':</b></font></nobr></td></tr>
        <tr><td colspan=2><nobr>[[[code]]]</nobr></td></tr>
        <tr><td colspan=2><font face="arial" size=2>'.ML("Bijvoorbeeld","For example").': $file["shorttitle"] = strtoupper ($file["shorttitle"]); </font></td></tr>
        <tr><td colspan=2>&nbsp;</td></tr>
        <tr><td colspan=2><table>
          <tr><td><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td><td><nobr>[[[sure]]]</nobr></td></tr>
        </table></td></tr>
        <tr><td colspan=2>&nbsp</td></tr>
        <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>
    ';
      $form["postcode"] = '
      if ($data["sure"]=="yes") {
        $tree = &CASE_TreeRef (IMS_SuperGroupName(), $input["me"]);
        $count = TREE_TERRA_WalkDirectory ($tree, $input["me"], "", $data["code"]);
        N_Redirect (FORMS_URL (array ("formtemplate"=>\'
          <table>
          <tr><td><font face="arial" size=2><b>\'.ML("De %1 bestanden worden in de achtergrond verwerkt","The %1 files are processed in the background", $count).\'</b></font></td></tr>
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
          </table>
        \', "gotook"=>"closeme")));
      }
    ';
      $url = FORMS_URL ($form);
      echo '<table border=0 cellspacing=0 cellpadding=0><tr><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/edit_small.gif"></a></td><td>&nbsp</td><td><a class="ims_navigation" title="'.$title.'" href="'.$url.'">'.ML("Bulk operatie","Bulk operation").'</a></td></tr></table>';
    }

    }

    echo "</td></tr></table>";
    echo "<br>";


    }

      endblock();
    }

  }
  if ($mode=="admin") {

    if ($submode!="securitysection" && !$print) {

    if (function_exists ("SKIN_preventMenuOutputStart")) SKIN_preventMenuOutputStart( "ADMIN" );

    startblock (ML("Administratie", "Administration"), "nav");
    if ($submode=="users") {
      echo '<a class="ims_active" href="/openims/openims.php?mode=admin&submode=users">'.ML("Gebruikers","Users").'</a><br>';
    } else {
      echo '<a class="ims_navigation" href="/openims/openims.php?mode=admin&submode=users">'.ML("Gebruikers","Users").'</a><br>';
    }
    if ($submode=="groups") {
      echo '<a class="ims_active" href="/openims/openims.php?mode=admin&submode=groups">'.ML("Groepen","Groups").'</a><br>';
    } else {
      echo '<a class="ims_navigation" href="/openims/openims.php?mode=admin&submode=groups">'.ML("Groepen","Groups").'</a><br>';
    }

    if ($submode=="workflow") {
      echo '<a title="'.ML("Workflows voor content (CMS) en documenten (DMS)","Workflows for content (CMS) and documents (DMS)").'" class="ims_active" href="/openims/openims.php?mode=admin&submode=workflow"><nobr>'.ML("Document workflows","Document workflows").'</nobr></a><br>';
    } else {
      echo '<a title="'.ML("Workflows voor content (CMS) en documenten (DMS)","Workflows for content (CMS) and documents (DMS)").'" class="ims_navigation" href="/openims/openims.php?mode=admin&submode=workflow"><nobr>'.ML("Document workflows","Document workflows").'</nobr></a><br>';
    }
    // casetypes
    if($myconfig[IMS_SuperGroupName()]["casetypes"]=="yes") {
      if ($submode=="casetypes") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=admin&submode=casetypes">'.ML("Dossiercategorie&euml;n","Casetypes").'</a><br>';
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=admin&submode=casetypes">'.ML("Dossiercategorie&euml;n","Casetypes").'</a><br>';
      }
    }

    // multi archive
    if($myconfig["mail"]["multiarchive"]) {
      if ($submode=="ems_permissions") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=admin&submode=ems_permissions">'.ML("EMS rechten","EMS permissions").'</a><br>';
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=admin&submode=ems_permissions">'.ML("EMS rechten","EMS permissions").'</a><br>';
      }
    }
    if ($submode=="fields") {
      echo '<a class="ims_active" href="/openims/openims.php?mode=admin&submode=fields">'.ML("Velden","Fields").'</a><br>';
    } else {
      echo '<a class="ims_navigation" href="/openims/openims.php?mode=admin&submode=fields">'.ML("Velden","Fields").'</a><br>';
    }

    if (count(ML_ModifiableLanguages())) {
      if ($submode=="multilang") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=admin&submode=multilang">'.ML("Meertaligheid", "Multilinguality").'</a><br>';
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=admin&submode=multilang">'.ML("Meertaligheid", "Multilinguality").'</a><br>';
      }
    }
    if (BLACK_OK()) {
      if ($submode=="maint") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=admin&submode=maint">'.ML("Onderhoud","Maintenance").'</a><br>';
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=admin&submode=maint">'.ML("Onderhoud","Maintenance").'</a><br>';
      }
    }

    global $myconfig;
    if (!$myconfig[IMS_SuperGroupName()]["hideflex"]) {
      if (BLACK_OK() && SHIELD_HasGlobalRight ($supergroupname, "develop", $mysecuritysection)) {
        if ($submode=="flex") {
          echo '<a class="ims_active" href="/openims/openims.php?mode=admin&submode=flex">'.ML("Inrichting","Configuration").'</a><br>';
        } else {
          echo '<a class="ims_navigation" href="/openims/openims.php?mode=admin&submode=flex">'.ML("Inrichting","Configuration").'</a><br>';
        }
      }
    }

    endblock();

    if (function_exists ("SKIN_preventMenuOutputEnd")) SKIN_preventMenuOutputEnd( "ADMIN" );

    } // ! ($submode!="securitysection")



  } if ($mode=="cms") {

        
        startblock (ML ("Content","Content"), "nav");


    if (function_exists ("SKIN_preventMenuOutputStart")) SKIN_preventMenuOutputStart( "CMS" );

    if($myconfig[$supergroupname]["cms"]["showassigned"] != "no") {
      if ($submode=="assigned") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=cms&submode=assigned">'.ML("Toegewezen","Assigned").'</a><br>';
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=cms&submode=assigned">'.ML("Toegewezen","Assigned").'</a><br>';
      }
    }

    if($myconfig[$supergroupname]["cms"]["showinpreview"] != "no") {
      if ($submode=="preview") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=cms&submode=preview">'.ML("In behandeling","In preview").'</a><br>';
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=cms&submode=preview">'.ML("In behandeling","In preview").'</a><br>';
      }
    }

    if($myconfig[$supergroupname]["cms"]["showrecentlychanged"] != "no") {
      if ($submode=="recent") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=cms&submode=recent">'.ML("Recent gewijzigd","Recently changed").'</a><br>';
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=cms&submode=recent">'.ML("Recent gewijzigd","Recently changed").'</a><br>';
      }
    }

    if($myconfig[$supergroupname]["cms"]["showleastrecent"] != "no") {
      if ($submode=="leastrecent") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=cms&submode=leastrecent">'.ML("Minst recent","Least recent").'</a><br>';
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=cms&submode=leastrecent">'.ML("Minst recent","Least recent").'</a><br>';
      }
    }

    if($myconfig[$supergroupname]["cms"]["showexpired"] != "no") {
      if ($submode=="expired") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=cms&submode=expired">'.ML("Verlopen","Expired").'</a><br>';
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=cms&submode=expired">'.ML("Verlopen","Expired").'</a><br>';
      }
    }

    if($myconfig[$supergroupname]["cms"]["showallsites"] != "no") {
      if ($submode=="sites") {
        echo '<a class="ims_active" href="/openims/openims.php?mode=cms&submode=sites">'.ML("Alle sites","All sites").'</a><br>';
      } else {
        echo '<a class="ims_navigation" href="/openims/openims.php?mode=cms&submode=sites">'.ML("Alle sites","All sites").'</a><br>';
      }
    }

     if($myconfig[$supergroupname]["cms"]["showtemplates"] != "no") {
      if ($submode=="templates") {
        if (SHIELD_HasModule ("cms", "templates") && (SHIELD_HasGlobalRight ($supergroupname, "webtemplateedit") || SHIELD_HasGlobalRight ($supergroupname, "webtemplatepublish"))) {
          echo '<a class="ims_active" href="/openims/openims.php?mode=cms&submode=templates">'.ML("Templates","Templates").'</a><br>';
        } else {
          SHIELD_Unauthorized ();
        }
      } else {
        if (SHIELD_HasModule ("cms", "templates") && (SHIELD_HasGlobalRight ($supergroupname, "webtemplateedit") || SHIELD_HasGlobalRight ($supergroupname, "webtemplatepublish"))) {
          echo '<a class="ims_navigation" href="/openims/openims.php?mode=cms&submode=templates">'.ML("Templates","Templates").'</a><br>';
        }
      }
    }

    if (function_exists ("SKIN_preventMenuOutputEnd")) SKIN_preventMenuOutputEnd( "CMS" );

    endblock();
    
  }
  } // (!$wide)

  if ($wide) {
    echo "</td><td width=90%>";
  } else if ($mode=="dbm" || $viewmode=="report" || $viewmode=="norightorleftcol"){
    echo "</td><td width=98%>";
  } else if ($viewmode=="norightcol"){
    echo "</td><td width=80%>";
  } else {
//    if ( function_exists("SKIN_preventEmptyColumnEnd") )
//      SKIN_preventEmptyColumnEnd( "LEFT" );
//    else
    echo "</td><td width=60% class='openims_middle_column'>";
  }

  if ($submode=="multiapprove" and $myconfig[IMS_SupergroupName()]["multiapprove"] == "yes")
  {
     uuse("multiapprove");
     startblock(ML("Distributielijsten", "Distribution lists"), "docnav");
     MULTIAPPROVE_ShowDistributionLists();
     endblock();
  }
  $sgn = IMS_SupergroupName();
  if ($submode=="trackinglist" and $myconfig[$sgn]["trackinglist"] == "yes")
  {
     uuse("trackinglist");
     startblock(ML("Volglijst", "Tracking list"), "docnav");
     echo TRACKINGLIST_Show($sgn);
     endblock();
  }
  // MMIDDLE
  if ($submode=="signals") {
    startblock (ML("Signalering","Signals"), "docnav");
    echo "<br>";
    T_Start("ims", array ("noheader"=>true ) );

    $signal_user = MB_Ref("shield_".IMS_SuperGroupName()."_users", SHIELD_CurrentUser());
    // start counter here
    $real_count = 0;

    if (is_array($signal_user["mailobjects"]) && count($signal_user["mailobjects"])) {
      foreach ($signal_user["mailobjects"] as $object_id => $dummy) {
        $object = &IMS_AccessObject (IMS_SuperGroupName(), $object_id);
        //20101029 KvD CORE-12 Only with non-discarded documents
        if ($object["published"] == "yes" || $object["preview"] == "yes") {
          $real_count++;
          ///
          $form = array();
          $form["input"]["object_id"] = $object_id;
          $form["input"]["user_id"] = SHIELD_CurrentUser();
          $form["metaspec"]["fields"]["changed"]["type"] = "yesno";
          $form["metaspec"]["fields"]["published"]["type"] = "yesno";
          $form["metaspec"]["fields"]["statuschanged"]["type"] = "yesno";
          $form["formtemplate"] = '
          <table>
            <tr><td><font face="arial" size=2><b>'.ML("Bij wijziging","On change").':</b></font></td><td>[[[changed]]]</td></tr>
            <tr><td><font face="arial" size=2><b>'.ML("Bij publicatie","On publish").':</b></font></td><td>[[[published]]]</td></tr>
            <tr><td><font face="arial" size=2><b>'.ML("Bij statuswijziging","On status changed").':</b></font></td><td>[[[statuschanged]]]</td></tr>

            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
          $form["precode"] = '
          $mode = MAIL_IsObjectConnectedToUser ($input["object_id"], $input["user_id"]);
          if (strpos (" ".$mode, "x")) $data["changed"] = true;
          if (strpos (" ".$mode, "p")) $data["published"] = true;
          if (strpos (" ".$mode, "s")) $data["statuschanged"] = true;
        ';
          $form["postcode"] = '
          $mode = "";
          if ($data["changed"]) $mode .= "x";
          if ($data["published"]) $mode .= "p";
           if ($data["statuschanged"]) $mode .= "s";
          MAIL_ConnectObjectToUser ($input["object_id"], $input["user_id"], $mode);
        ';
          $url = FORMS_URL ($form);
          $tree = CASE_TreeRef ($supergroupname, $object["directory"]);
          $folderobj = TREE_AccessObject($tree, $object["directory"]);
          if (TREE_Visible($object["directory"], $folderobj)) {
            echo "<a class=\"ims_navigation\" title=\"".ML("Ga naar de folder", "Go to folder")."\" href=\"/openims/openims.php?mode=dms&submode=documents&currentfolder=" . $object["directory"] . "&currentobject=" . $object_id . '">';
            echo '<img border=0 src="/ufc/rapid/openims/folder.gif" /></a> &nbsp;';
          }
          echo "<a class=\"ims_navigation\" title=\"".ML("E-mail signalering aanpassen","Reconfigure e-mail signals")."\" href=\"$url\">";
          echo '<img border=0 height=16 width=16 src="/ufc/rapid/openims/signal_on.gif"> '.$object["shorttitle"];
          echo "</a><br>";
        }  // CORE-12 extra if block
      }
    }
    // no real count of objects with signal
    if (!$real_count) {
      echo ML ("Signalering staat voor alle documenten uit", "Signals for all documents are off");
    }
    TE_End();
    echo "<br>";
    endblock();
  }
  if ($mode=="search") {
    startblock (ML("Globaal zoeken","Global search"), "docnav");
    echo "<br>";
    ?>
<font face="Arial, Helvetica" size="2">
<table border="0" cellspacing="0" cellpadding="0">
  <form action="<? echo N_MyBareURL(); ?>" method="put">
    <tr><td>
      <input type="hidden" name="mode" value="search">

      <input type="text" name="q" size="40" value="<? echo $q; ?>"><br>
    </td><td>
      &nbsp;
    </td><td>
      <input type="submit" style="font-weight:bold" value="<? echo ML("Zoeken","Search"); ?>">
    </td></tr>
  </form>
</table>
<script language="javascript">
<!--
  document.forms[0].elements[1].focus();
// -->
</script>
    <?
    echo "<br>";
    $words = explode (" ", SEARCH_REMOVEACCENTS(strtolower ($q)));
    $count = count ($words);
    $result = SEARCH ($supergroupname."#bpms", $q, 0, 0);
    $amount = $result["amount"]; $totamount = $amount;
    if ($amount) {
      if ($amount > 7) $amount = 7;
      $url = "/openims/openims.php?mode=bpms&submode=search&q=".urlencode($q);
      echo "<b>".ML("Gegevens (data / proces) 1-%1 van %2", "Data (data / process) 1-%1 of %2", $amount, $result["amount"])."</b> <a href=\"$url\">".ML("Meer...","More...")."</a>.<br><br>";
      foreach ($result["result"] as $case_id => $dummy) {
        if ($amount-->0) {
          $processes = MB_Query ("shield_".IMS_SuperGroupName()."_processes");
          foreach ($processes as $process_id) {
            $mcase = MB_Ref ("process_".IMS_SuperGroupName()."_cases_".$process_id, $case_id);
            if ($mcase) {
              $case = $mcase;
              $myprocess = $process_id;
            }
          }
          $process = MB_Ref ("shield_".IMS_SuperGroupName()."_processes", $myprocess);
          $title = $case["visualid"];
          if (SHIELD_RecordViewAllowed (IMS_SuperGroupName(), $myprocess, $case_id)) {
            if ($process["stages"]) {
              $title .= " ".$case["data"][$process["titlefield1"]];
              if ($process["titlefield2"]) $title.=" ".$case["data"][$process["titlefield2"]];
              if ($process["titlefield3"]) $title.=" ".$case["data"][$process["titlefield3"]];
              if ($process["titlefield4"]) $title.=" ".$case["data"][$process["titlefield4"]];
              if ($process["titlefield5"]) $title.=" ".$case["data"][$process["titlefield5"]];
              if ($process["titlefield6"]) $title.=" ".$case["data"][$process["titlefield6"]];

              if ($process["titlefield7"]) $title.=" ".$case["data"][$process["titlefield7"]];
            } else {

              $title .= " ".$case["data"][$process["datatitlefield1"]];
              if ($process["datatitlefield2"]) $title.=" ".$case["data"][$process["datatitlefield2"]];
              if ($process["datatitlefield3"]) $title.=" ".$case["data"][$process["datatitlefield3"]];
              if ($process["datatitlefield4"]) $title.=" ".$case["data"][$process["datatitlefield4"]];
              if ($process["datatitlefield5"]) $title.=" ".$case["data"][$process["datatitlefield5"]];
              if ($process["datatitlefield6"]) $title.=" ".$case["data"][$process["datatitlefield6"]];
              if ($process["datatitlefield7"]) $title.=" ".$case["data"][$process["datatitlefield7"]];
            }
            for ($i=0; $i<$count; $i++) {
              if (trim($words[$i])) {
                $title = preg_replace ("/([^a-zA-Z]|^)(".preg_quote($words[$i], "/").")([^a-zA-Z]|\$)/i", "\\1<b>\\2</b>\\3", $title);
              }
            }
          }
          echo "<a class=\"ims_result\" href=\"$url\">";
          if (SHIELD_RecordViewAllowed (IMS_SuperGroupName(), $myprocess, $case_id)) {
            echo '<img src="/ufc/rapid/openims/workstep.gif" border=0>&nbsp;';
          } else {
            echo '<img src="/ufc/rapid/openims/lock.gif" border=0>&nbsp;';
          }
          echo $title."</a> ";
          echo $process[$case["stage"]]["name"];

          echo " ";
          $lastupdate=0;
          foreach ($case["history"] as $dummy => $data) {
            if ($data["when"] > $lastupdate) $lastupdate=$data["when"];
          }
          echo N_VisualDate ($data["when"], 0, 1);
          echo "<br>";
        }
      }
      echo "<br>";
    }

    // multi archive
//    $result = SEARCH ("mail_".$supergroupname."_main", $q, 0, 0);
    if($myconfig["mail"]["multiarchive"]) {
      $result = SEARCH_Mail($supergroupname, $q, 0, 0, "all", true);
    } else {
      $result = SEARCH_Mail($supergroupname, $q, 0, 0, "main", false);
    }
    $amount = $result["amount"]; $totamount = $totamount + $amount;
    if ($amount) {
      if ($amount > 7) $amount = 7;
      $url = "/openims/openims.php?mode=ems&submode=searchemails&q=".urlencode($q)."&archive=all";
      echo "<b>".ML("E-mails 1-%1 van %2", "Emails 1-%1 of %2", $amount, $result["amount"])."</b> <a href=\"$url\">".ML("Meer...","More...")."</a>.<br><br>";
      foreach ($result["result"] as $email => $dummy) {
        if ($amount-->0) {
          if(!$myconfig["mail"]["multiarchive"]) {
            $selectedarchive="main";
          } else {
            $selectedarchive=$dummy["archiveid"];
          }

//          $emailobject = MB_Ref ("mail_".$supergroupname."_main", $email);
          $emailobject = MB_Ref ("mail_".$supergroupname."_" . $selectedarchive, $email);

          $title = $emailobject["headerspecs"]->subject;
//          $url = IMS_GenerateViewURL ("\\".$supergroupname."\\mailarchives\\main\\", $email.".eml", "auto");
          $url = IMS_GenerateViewURL ("\\".$supergroupname."\\mailarchives\\" . $selectedarchive . "\\", $email.".eml", "auto");
          $archive = $res["archivename"];

//          echo "<a title=\"". htmlentities (SEARCH_HTML2TEXT (SEARCH_Summary ("mail_".$supergroupname."_main", $q, $email)))."\" class=\"ims_result\" href=\"$url\">";
          if($selectedarchive!="main")
            $searchsummary =  SEARCH_Summary ("mail_".$supergroupname."_" . $selectedarchive , $q, $email);
          else

            $searchsummary = SEARCH_Summary ("mail_".$supergroupname."_$selectedarchive" , $q, $email);
          echo "<a title=\"". htmlentities (SEARCH_HTML2TEXT ( $searchsummary ))."\" class=\"ims_result\" href=\"$url\">";

          echo '<img src="/ufc/rapid/openims/mini_email.gif" border=0>&nbsp;';
          for ($i=0; $i<$count; $i++) {
            if (trim($words[$i])) {
              $title = preg_replace ("/([^a-zA-Z]|^)(".preg_quote($words[$i], "/").")([^a-zA-Z]|\$)/i", "\\1<b>\\2</b>\\3", $title);
            }
          }
          echo $title."</a> ";
          echo "<font size=2>".N_VisualDate ($emailobject["headerspecs"]->udate)."</font></a>";
          echo "<br>";
        }

      }
      if ($archive != "") {
            echo "&nbsp;" . ML("archief", "archive") . "&nbsp;" . $archive;
      }
      echo "<br>";

    }



    $result = SEARCH ("$supergroupname#previewdocuments", $q, 0, 10); // qqq
    $amount = $result["amount"]; $totamount = $totamount + $amount;
    if ($amount) {
      if ($amount > 7) $amount = 7;
      $url = "/openims/openims.php?mode=dms&submode=search&q=".urlencode($q);
      if ($result["amount"] < 11) {
        echo "<b>".ML("Documenten 1-%1 van %2", "Documents 1-%1 of %2", $amount, $result["amount"])." </b> <a href=\"$url\">".ML("Meer...","More...")."</a>.<br><br>";
      } else {
        echo "<b>".ML("Documenten 1-%1 van meer dan 10 resultaten", "Documents 1-%1 of over 10 results", $amount, $result["amount"])." </b> <a href=\"$url\">".ML("Meer...","More...")."</a>.<br><br>";
      }
      foreach ($result["result"] as $object_id => $dummy) {
        if ($amount-->0) {
          $object = &IMS_AccessObject ($supergroupname, $object_id);

          $image = FILES_Icon ($supergroupname, $object_id, false, "preview");
          $doc = FILES_TrueFileName ($supergroupname, $object_id, "preview");
          $thedoctype = FILES_FileType ($supergroupname, $object_id, "preview");

          if (SHIELD_HasObjectRight ($supergroupname, $object_id, "view")) {
            $url = FILES_DocPreviewURL ($supergroupname, $object_id);
          } else {

            $url = "/openims/openims.php?mode=dms&submode=search&q=".urlencode($q);
          }
          $title = $object["shorttitle"];
          if ($url) {
            echo "<a title=\"".htmlentities (SEARCH_HTML2TEXT (SEARCH_Summary ("$supergroupname#previewdocuments", $q, $object_id)))."\" class=\"ims_result\" href=\"$url\">";
            echo "<img src=\"$image\" border=0>&nbsp;";
            for ($i=0; $i<$count; $i++) {
              if (trim($words[$i])) {
                $title = preg_replace ("/([^a-zA-Z]|^)(".preg_quote($words[$i], "/").")([^a-zA-Z]|\$)/i", "\\1<b>\\2</b>\\3", $title);
              }
            }
            echo $title."</a> ";
            echo " ".SHIELD_CurrentStageName ($supergroupname, $object_id)." ";
          } else {
            echo "<img src=\"$image\" border=0>&nbsp;";

            for ($i=0; $i<$count; $i++) {
              if (trim($words[$i])) {
                $title = preg_replace ("/([^a-zA-Z]|^)(".preg_quote($words[$i], "/").")([^a-zA-Z]|\$)/i", "\\1<b>\\2</b>\\3", $title);
              }
            }
            echo $title." ";
            echo " ".SHIELD_CurrentStageName ($supergroupname, $object_id)." ";

          }

          if (is_array($object["history"])) {
            foreach ($object["history"] as $dummy => $data) {
              $time = $data["when"];

            }
            echo N_VisualDate ($time, 0, 1);
          } else {
            echo " ";
          }
          echo "<br>";
        }
      }
      echo "<br>";
    }
    $custom = FLEX_LocalComponents (IMS_SuperGroupName(), "search");

    foreach ($custom as $id => $specs)
    {
      $content = "";
      eval ($specs["code_search"]);
      if ($content) echo $content;
    }

    if (!$totamount and count($custom) == 0)
    {
      echo "<br><b>".ML("Uw zoekopdracht heeft niets opgeleverd","Your search has not found anything").".</b><br><br>";

      echo ML("Suggesties","Tips").":<br>";
      echo "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Zorg ervoor dat alle woorden goed gespeld zijn","Make sure the spelling is correct").".<br>";
      echo "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Probeer andere zoektermen","Try other terms").".<br>";
      echo "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Maak de zoektermen algemener","Use more generic terms").".<br>";
      if ($count>1) echo "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Gebruik minder termen","Use less terms").".<br>";

    }

   endblock();
  }



  if ($mode=="dms") {

    if ($submode=="search") {

    if ($viewmode!="report") {
      startblock (ML("Zoeken in documenten","Search in documents"), "docnav");
      echo "<br>";
    }

    global $myconfig, $selectcounter;
    if ($submode=="search" and $searchmode=="advanced" and $myconfig[IMS_SuperGroupName()]["multifile"]=="yes" and $myconfig[IMS_SuperGroupName()]["multifileblock_in_advancedsearch"]=="yes")
    {
      uuse ("dhtml");
      uuse ("multi");
      $selectcounter = 0 + MULTI_Selected();
      echo DHTML_EmbedJavaScript ("selectcounter=$selectcounter;");
    }

    global $qscope;
    // If visitors arrived here from the search box in the "Documents"-block, and they are searching a specific case,
    // "redirect" them to advanced search
    if (($myconfig[IMS_SuperGroupName()]["casesearch"] == "yes") && !$searchmode && ($qscope=="generic" || $qscope=="case")) {
      $searchmode = "advanced";
      $qr1 = $q;
    }
    if ($searchmode=="advanced") {

      //ericd 051012
      //save last chosen workflow in advanced search in user table
      if($myconfig[IMS_SuperGroupName()]["advsearchrememberwfl"] == "yes") {
        if(!empty($wstatus)) {
          $user = SHIELD_CurrentUser(IMS_SuperGroupName());
          $usersTable = "shield_".IMS_SuperGroupName()."_users";
          $userObject = MB_Load($usersTable, $user);
          $userObject["searchsettings"]["wstatus"] = $wstatus;
          MB_Save($usersTable, $user, $userObject);
        }       
      }

      echo "<form id=\"dmsadvsearchform\" action=\"".N_MyBareURL()."#results\" method=\"put\">";
      echo '<input type="hidden" name="mode" value="dms">';
      echo '<input type="hidden" name="submode" value="search">';
      echo '<input type="hidden" name="searchmode" value="advanced">';
      T_Start("ims", array ("noheader"=>true));
        T_Start("", array ("noheader"=>true, "nobr"=>true));
        for ($i=1; $i<=4; $i++) {
            if ($i==1) {
              if ($viewmode=="report") {
                echo "<b>".ML ("Resultaten", "Results")."</b>";
              } else {
                echo "<b>".ML ("Zoek resultaten", "Find results")."</b>";

              }
            }
          T_Next();
            echo "<b>".ML ("met", "with")."</b> ";
            echo ML ("de termen", "the terms");
          T_Next();
            echo "<input type=\"text\" name=\"qr$i\" value=\"".eval("return N_HtmlEntities(\$qr$i);")."\">&nbsp;";
            echo '<select name="c'.$i.'" value="'.eval("return N_HtmlEntities(\$c$i);").'">';
            echo '<option value="" '.N_If(!eval("return N_HtmlEntities(\$c$i);"),"Selected").'>'.ML("overal", "anywhere").'</option>';
            // 20090901 naam => documentnaam
            echo '<option value="shorttitle" '.N_If("shorttitle"==eval("return N_HtmlEntities(\$c$i);"),"Selected").'>'.ML("in de documentnaam", "in document name").'</option>';
            echo '<option value="longtitle" '.N_If("longtitle"==eval("return N_HtmlEntities(\$c$i);"),"Selected").'>'.ML("in de omschrijving", "in the description").'</option>';
            echo '<option value="content" '.N_If("content"==eval("return N_HtmlEntities(\$c$i);"),"Selected").'>'.ML("in het document", "in the document").'</option>';
            $workflows = MB_Query ("shield_$supergroupname"."_workflows", '$record["dms"]', 'FORMS_ML_Filter($record["name"])');
            $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
            if (!is_array ($allfields)) $allfields = array();
            $thefields = array();
            global $myconfig;
            if ($myconfig[IMS_SuperGroupName()]["showallfieldsindmsadvancessearch"]=="yes") {
              foreach ($allfields as $fieldname => $dummy)
              {
                if ($allfields[$fieldname]["advsearchdms"]!="no") {
                  $thefields[$fieldname] = $allfields[$fieldname]["title"];
                }
              }
            } else {
              foreach ($workflows as $workflow_id => $dummy)
              {
                $workflow = MB_Ref ("shield_$supergroupname"."_workflows", $workflow_id);
                if (is_array($workflow["meta"])) foreach ($workflow["meta"] as $fieldname) {
                  if ($fieldname && ($allfields[$fieldname]["advsearchdms"]!="no")) {
                    $thefields[$fieldname] = $allfields[$fieldname]["title"];
                  }
                }
              }
            }
            asort ($thefields);
            foreach ($thefields as $fieldname => $title) {
              // 20090901 KvD geen aanhalingstekens
              if ($GLOBALS['myconfig'][IMS_SuperGroupName()]['noquotesindmssearchdropdown'])
                $disptitle = $title;
              else
                $disptitle = "'$title'";
              ///
              echo '<option value="meta_'.$fieldname.'" '.N_If('meta_'.$fieldname==eval("return N_HtmlEntities(\$c$i);"),"Selected").'>'.ML("in", "in")." $disptitle</option>";
            }
            echo '</select>';
          T_NewRow();
        }
        global $myconfig;
        if ($myconfig["ftengine"]=="S2_MYSQLFT" || $myconfig["ftengine"]=="S2_SPHINX2") {
//        T_Next();
//          echo "<b>".ML ("met", "with")."</b> ";
//          echo ML ("de optionele termen", "the optional terms");
//        T_Next();
//          echo "<input type=\"text\" name=\"qropt\" value=\"".eval("return N_HtmlEntities(\$qropt);")."\">";
//        T_Next();
//        T_NewRow();
          T_Next();
            echo "<b>".ML ("met", "with")."</b> ";
            echo ML ("de frase", "the phrase");
          T_Next();
            echo "<input type=\"text\" name=\"qrphr\" value=\"".eval("return N_HtmlEntities(\$qrphr);")."\">";
          T_Next();
          T_NewRow();
          if ($myconfig["ftengine"]=="S2_MYSQLFT") {
            T_Next();
              echo "<b>".ML ("met", "with")."</b> ";
              echo ML ("de wildcard termen", "the wildcard terms");
            T_Next();

            echo DHTML_InvisiTable ('<font face="arial" size="2">', '</font>',
                   "<input type=\"text\" name=\"qrwil\" value=\"".eval("return N_HtmlEntities(\$qrwil);")."\">",
                   "&nbsp;(".ML ("gebruik","use")." *)");
            T_NewRow();
          }
        }
        T_Next();
          echo "<b>".ML ("zonder", "without")."</b> ";

          echo ML ("de termen", "the terms");
        T_Next();
          echo "<input type=\"text\" name=\"qrn\" value=\"".eval("return N_HtmlEntities(\$qrn);")."\">";
        T_Next();
        T_NewRow();
        T_Next();
          echo " ".ML ("doorzoek", "search")."";
          echo " <b>".ML ("versies", "versions")."</b>";
        T_Next();
          echo '<select name="pstatus" value="">';
          echo '<option value="" '.N_If(""==$pstatus,"Selected").'>'.ML("in behandeling en gepubliceerd", "preview and published").'</option>';
          echo '<option value="published" '.N_If("published"==$pstatus,"Selected").'>'.ML("gepubliceerd", "published").'</option>';
          echo '</select>';
        T_NewRow();

        global $myconfig;
        $showindexchoice=false;
        if (is_array ($myconfig["extradmsindex"])) foreach ($myconfig["extradmsindex"] as $name => $specs) {
          if ($specs["sgn"]==IMS_SuperGroupName()) {
            if (SHIELD_ValidateAccess_List (IMS_SuperGroupName(), SHIELD_CurrentUser(), $specs["advancedsearchstandardusergroups"])) {
              $showindexchoice=true;
            }
          }
        }
        if ($showindexchoice)
        {
          T_Next();
            echo " ".ML ("doorzoek", "search")."";
            echo " <b>".ML ("index", "index")."</b>";
          T_Next();
            echo '<select name="index" value="">';
            echo '<option value="" '.N_If(""==$index,"Selected").'>'.ML("standaard","standard").'</option>';
            if (is_array ($myconfig["extradmsindex"])) foreach ($myconfig["extradmsindex"] as $name => $specs) {
              if ($specs["sgn"]==IMS_SuperGroupName()) {
                if (SHIELD_ValidateAccess_List (IMS_SuperGroupName(), SHIELD_CurrentUser(), $specs["advancedsearchstandardusergroups"])) {
                  echo '<option value="'.$name.'" '.N_If($name==$index,"Selected").'>'.$specs["advancedsearchdescription"].'</option>';
                }
              }
            }
            echo '</select>';
          T_NewRow();
        }

        // interface for selecting scope and case
        if ($myconfig[IMS_SuperGroupName()]["casesearch"] == "yes") {
          global $field_qcase, $qcase, $qscope; // FORMS_FK_Edit prepends "field_" to "qcase"
          if ($field_qcase) $qcase = $field_qcase;
          if (($qscope == "case") && !$qcase) $qscope = "everything";
          if (!$qscope) $qscope = "everything";
          T_Next();
         // 20091005 KvD moet vertaald kunnen worden
          echo " <b>" . ML("scope", "scope") . "</b>";
          T_Next();
          echo "<table width=300>";
          $generictext = $myconfig[IMS_SuperGroupName()]["generictext"];
          if (!$generictext) $generictext = ML("Algemeen", "Generic");
          $casetext = ucfirst($myconfig[IMS_SuperGroupName()]["casetext"]);
          if (!$casetext) $casetext = ML("Dossier", "Case") . ":";
          echo '<tr><td style="padding-bottom: 5px"><font face="arial" size=2>';
          if($myconfig[IMS_SuperGroupName()]["show_generic"]=="no") {
            $scopes = array(ML("Alles","Everything") => "everything", $casetext => "case");
          } else {
            $scopes = array($generictext => "generic", ML("Alles","Everything") => "everything", $casetext => "case");
          }
          $myconfig_has_casetypes = $myconfig[IMS_SuperGroupName()]["casetypes"] == "yes";

          $found_categories = Array();
          if ( $myconfig_has_casetypes )
          {
            $specs_wherein = Array();
            $all_categories = MB_allkeys( "ims_".$sgn."_case_types" );
            foreach($all_categories as $id => $dummy) {
              if (SHIELD_CanViewDmsCasetype($sgn,SHIELD_CurrentUser(),$id) ) {
                $found_categories[$id] = $id;
              }
            }
          }

          foreach ($scopes as $name => $value) {
            if ($qscope == $value) {
              echo '<input type="radio" name="qscope" value="'.$value.'" checked> '.$name.'<br>';
            } else {
              echo '<input type="radio" name="qscope" value="'.$value.'"' . ($myconfig_has_casetypes && $value=="case" && (count($found_categories)==0)?" disabled":"") . '> '.$name.'<br>';
            }
          }
          echo '</td></font>';
          //STARTJG
          if( !$myconfig_has_casetypes || count($found_categories) > 0 ) {
            if ( $myconfig_has_casetypes ) 
              $specs_wherein = array('$record["category"]' => $found_categories);
            else
              $specs_wherein = array();

            echo '<td valign="bottom">' . FORMS_FK_Edit ("ims_".IMS_SuperGroupName()."_case_data", "qcase", $qcase, array (
              ML("Naam","Name") => '$object["shorttitle"]',
              ML("Omschrijving","Description") => '$object["longtitle"]'
            ) ,"" , "" , Array() , Array(), false , $specs_wherein ) . '</td>';
          } else {
             echo '<td valign="bottom" style="padding-bottom:6px;">'.ML("U heeft geen toegangkelijk " . $casetext  ,"You have no available cases")."</td>";
          }
          //ENDJG

          echo "</tr></table>";
          T_NewRow();
        }

        T_Next();
          echo ML ("met", "with");
          echo " <b>".ML ("werkstroom", "workflow")."</b>";

        T_Next();
          echo '<select name="wstatus" value="">';
          echo '<option value="">'.ML("elke", "any").'</option>';


        //ericd 121012
        //read last chosen workflow in advanced search in user table
        $user = SHIELD_CurrentUser(IMS_SuperGroupName());
        $usersTable = "shield_".IMS_SuperGroupName()."_users";
        $userObject = MB_Load($usersTable, $user);
        if($myconfig[IMS_SuperGroupName()]["advsearchrememberwfl"] == "yes" && is_array($userObject["searchsettings"])) {
            global $wstatus;
            $wstatus = $userObject["searchsettings"]["wstatus"];     
        }


          $workflows = MB_Query ("shield_$supergroupname"."_workflows", '$record["dms"]', 'FORMS_ML_Filter($record["name"])');
          foreach ($workflows as $workflow_id => $dummy)
          {
            if ( !$myconfig[$supergroupname]["advanced_search_hide_workflows"] or array_search( $workflow_id , $myconfig[$supergroupname]["advanced_search_hide_workflows"] )=== false )
            {
              $workflow = MB_Ref ("shield_$supergroupname"."_workflows", $workflow_id);
              echo '<option value="'.$workflow_id.'" '.N_IF ($wstatus==$workflow_id,"selected").'>'.$workflow["name"].'</option>';
            }
          }

          echo '</select>';
        T_NewRow();
        T_Next();
          echo ML ("met", "with");
          echo " <b>".ML ("bestandsformaat", "file format")."</b>";
        T_Next();
          echo '<select name="fileformat" value="">';
          echo '<option value="">'.ML("elk formaat", "any format").'</option>';
          //sbr 02-01-2008
          $fileformatoptions = array("bpm"=>"BPM (.bpm)",
                                     "pdf"=>"Adobe Acrobat (.pdf)",
                                     "fm"=>"Adobe FrameMaker (.fm)",
                                     "pm"=>"Adobe PageMaker (.pm)",
                                     "psd"=>"Adobe Photoshop (.psd)",
                                     "dwg"=>"Autodesk AutoCAD (.dwg)",
                                     "dgn"=>"Bentley MicroStation (.dgn)",
                                     "doc"=>"Microsoft Word (.doc)",
                                     "docx"=>"Microsoft Word (.docx)",
                                     "xls"=>"Microsoft Excel (.xls)",
                                     "xlsx"=>"Microsoft Excel (.xlsx)",
                                     "ppt"=>"Microsoft Powerpoint (.ppt)",
                                     "pptx"=>"Microsoft Powerpoint (.pptx)",
                                     "vsd"=>"Microsoft Visio (.vsd)",
                                     "qxd"=>"QuarkXPress (.qxd)",
                                     "zip"=>"WinZip (.zip)");
          if (is_array($myconfig[IMS_SuperGroupName()]["searchablefileformatoptions"])) {
             $fileformatoptions = $myconfig[IMS_SuperGroupName()]["searchablefileformatoptions"];
          }
          foreach ($fileformatoptions as $fileformatoption=>$fileformatoptiondescription) {
             echo '<option value="'.$fileformatoption.'" '.N_If($fileformat==$fileformatoption,"selected").'>'.$fileformatoptiondescription.'</option>';
          }
          echo '</select>';
        T_NewRow();
        T_Next();
          echo ML ("met", "with");
          echo " <b>".ML ("datum", "date")."</b>";
          if ($myconfig[IMS_SuperGroupName()]["advsearchdaterange"] == "yes")
            echo ML(" tussen", " between ");
        T_Next();
          if ($myconfig[IMS_SuperGroupName()]["advsearchdaterange"] != "yes")
          {
            echo '<select name="date" value="">';
            echo '<option value="">'.ML("elke", "any").'</option>';
            echo '<option value="1" '.N_If ($date==1,"selected").'>'.ML("afgelopen 48 uur", "past 48 hours").'</option>';
            echo '<option value="7" '.N_If ($date==7,"selected").'>'.ML("afgelopen 7 dagen", "past 7 days").'</option>';
            echo '<option value="30" '.N_If ($date==30,"selected").'>'.ML("afgelopen 30 dagen", "past 30 days").'</option>';
            echo '<option value="90" '.N_If ($date==90,"selected").'>'.ML("afgelopen 90 dagen", "past 90 days").'</option>';
            echo '<option value="365" '.N_If ($date==365,"selected").'>'.ML("afgelopen 365 dagen", "past 365 days").'</option>';
            echo '</select>';
          }
          else
          {
            uuse("jscal");

            $fieldname = "fromdate";
            $value = ($fromdate ? JSCAL_Decode($fromdate) : 0);
            echo JSCAL_CreateDate ($fieldname, $value);
            echo ML("  en  ", "  and  ");

            $fieldname2 = "todate";
            $value2 = ($todate ? JSCAL_Decode($todate) : 0);
            echo JSCAL_CreateDate ($fieldname2, $value2);
          }
        T_NewRow();
        T_Next();
          echo "<b>".ML ("sorteren", "sort")."</b> ";

          echo ML ("op", "by")." ";
        T_Next();

          echo '<select name="sortby" value="">';
          if ($myconfig["ftengine"] == "S2_SPHINX2") {
            echo '<option value="">'.ML("relevantie en datum", "relevance and date").'</option>';
            echo '<option value="segments" '.N_If ($sortby=="segments","selected").'>'.ML("datum en relevantie", "date and relevance").'</option>'; // TODO: maybe remove this. For now, it is useful for testing / comparison
            echo '<option value="rawrel" '.N_If ($sortby=="rawrel","selected").'>'.ML("relevantie", "relevance").'</option>';
          } else {
            echo '<option value="">'.ML("relevantie", "relevance").'</option>';
          }
          echo '<option value="name" '.N_If ($sortby=="name","selected").'>'.ML("naam", "name").'</option>';
          echo '<option value="date" '.N_If ($sortby=="date","selected").'>'.ML("datum (oplopend)", "date (increasing)").'</option>';
          echo '<option value="datedown" '.N_If ($sortby=="datedown","selected").'>'.ML("datum (aflopend)", "date (decreasing)").'</option>';
          echo '</select>';
        TE_End();
      if ($viewmode!="report") {
        T_NewRow();
          if ($myconfig[IMS_SuperGroupName()]["standardsearchlinkondmsadvsearchform"] == "yes") {
            // Why this option should NOT be enabled by default:
            // If you go from standard search to advanced search, you only had one input field, which can be copied to the advanced search screen.
            // If you go from advanced search to standard search, you had lots of input fields, and all except the top one are discarded when you go to standard search.
            // To make matters worse, the link is right next to actual Search button, so it's very easy to click when actually you wanted to search. 
            // With "Reset" (also a very bad idea) at least experienced web users are already familiar with its destructiveness. 
            // This link is a completely novel way for users to screw themselves.
            echo '<a class="ims_link" onclick="this.href += document.getElementById(\'dmsadvsearchform\').elements[\'qr1\'].value; return true;" href="/openims/openims.php?mode=dms&submode=search&q=">';
            echo ML ("Standaard zoeken", "Standard search");
            echo '</a>&nbsp;&nbsp;&nbsp;';
          }
          echo "<input type=\"submit\" style=\"font-weight:bold\" value=\"".ML("Zoeken","Search")."\">";
//20090901 KvD IJsselgroep mogelijkheid tot wissen alle velden: herroep URL
          if ($GLOBALS["myconfig"][IMS_SuperGroupName()]["resetdmsadvancedsearchform"] == "yes") {
            echo "&nbsp;<input type=\"button\"  onclick=\"javascript:location.href='/openims/openims.php?mode=dms&submode=search&searchmode=advanced';\" style=\"font-weight:bold\" value=\"".ML("Wissen","Clear")."\">";
          }
///
      }
      TE_End(array("td-align"=>"right"));
      echo "</form>";
      $q = trim($qr1." ".$qr2." ".$qr3." ".$qr4);
    } else {
    ?>
<font face="Arial, Helvetica" size="2">
<table border="0" cellspacing="0" cellpadding="0">
  <form id="dmssearchform" action="<? echo N_MyBareURL(); ?>" method="put">
    <tr><td>
      <input type="hidden" name="mode" value="dms">
      <input type="hidden" name="submode" value="search">
      <input type="text" name="q" size="35" value="<? echo htmlentities($q); ?>"><br>
    </td><td>
      &nbsp;
    </td><td>
      <input type="submit" style="font-weight:bold" value="<? echo ML("Zoeken","Search"); ?>">
    </td><td><nobr>&nbsp;
      <a class="ims_link" onclick="this.href += document.getElementById('dmssearchform').elements['q'].value; return true;" href="/openims/openims.php?mode=dms&submode=search&searchmode=advanced&qr1=">
        <? echo ML ("Geavanceerd zoeken", "Advanced search"); ?>
      </a>
    </nobr></td></tr>
  </form>
</table>
    <?
    }
    
  global $myconfig;
  if (($searchmode!="advanced") && ($myconfig[IMS_SuperGroupName()]["searchsuggestspelling"]=="yes")) {
    uuse ("soap");
    if ($qalt = SOAP_doGoogleSpellingSuggestionNL ($q)) {
      $aurl = N_AlterURL (N_MyFullURL(), "q", $qalt);
      echo "<br><font color=\"#ff0000\">".ML("Bedoelt u", "Do you mean").":</font> <b><a href=\"$aurl\">$qalt</a></b>?<br>";
    }
  }
  if($myconfig[IMS_SuperGroupName()]["searchresultswithpdfpreview"]=="yes")
    echo DHTML_PdfclickJs();

  $q = str_replace ("-", " ", $q);
  $qr1 = str_replace ("-", " ", $qr1);
  $qr2 = str_replace ("-", " ", $qr2);
  $qr3 = str_replace ("-", " ", $qr3);
  $qr4 = str_replace ("-", " ", $qr4);

  $words = explode (" ", SEARCH_REMOVEACCENTS(strtolower ($q)));
  $count = count ($words);
  if (!$from) $from = 1;
//  if (!$to) $to = $from + 9;
  $dms_search_results = $myconfig[$sgn]["dms_search_results"] ? $myconfig[$sgn]["dms_search_results"] : 10;
  if (!$to) $to = $from + ($dms_search_results-1);

  if ($viewmode=="report") $to = 99999;

  global $myconfig;
  $casekeyword = "";
  if ($myconfig["ftengine"]=="S2_MYSQLFT" || $myconfig["ftengine"]=="S2_SPHINX2") {
    if (($qropt || $qrphr || $qrwil) || ($qrn && ($qr1 || $qr2 || $qr3 || $qr4)) || ($qr1 && $c1) || ($qr2 && $c2) || ($qr3 && $c3) || ($qr4 && $c4)) {
      $specs = array();
      if ($myconfig[IMS_SuperGroupName()]["casesearch"] == "yes") {
        // add extra parameter $casekeyword to search query to search a specific case
        global $field_qcase, $qcase, $qscope;
        if ($field_qcase) $qcase = $field_qcase;
        $casekeyword = "";
        if ($qscope == "case" && $qcase) $casekeyword = SEARCH_CaseKeywordForFolder($qcase."root");
        if ($qscope == "generic") $casekeyword = SEARCH_CaseKeywordForFolder("root");
        $specs["required"] = $casekeyword;
      }
      foreach (array("1", "2", "3", "4") as $x) {
        eval ('$qrx = $qr'.$x.';');
        eval ('$cx = $c'.$x.';');
        if ($qrx) {
          if ($cx && $cx != "content") {
            // The search term $qrx should occur in a specific metadata field ($cx).
            // So we use keyword search (tell the engine to search only the metadata, not the content of the document). 
            // This will result in less false positives, so less work for our filtering routines. 
            // Filtering will still be necessary, as the engine searches all metadata, not just the field we wanted.
            $specs["keywords"] = trim($specs["keywords"] . " " . $qrx);
          } else {
            $specs["required"] = trim($specs["required"] . " " . $qrx);
          }
        }
      }
      if ($qrwil) $specs["required"] = trim($specs["required"] . " " . $qrwil);
      if ($qropt) $specs["optional"] = $qropt;
      if ($qrphr) $specs["phrase"] = $qrphr;
      if ($qrn) $specs["exclude"] = $qrn;
      $q = S2_BuildQuery ($specs);
    }
  }
  if ($q) {
    if (($myconfig[IMS_SuperGroupName()]["casesearch"] == "yes") && !$casekeyword) {
      // add extra parameter $casekeyword to seach query to search a specific case
      // (unless $casekeyword was already created before (S2 with advanced options))
      global $field_qcase, $qcase, $qscope;
      if ($field_qcase) $qcase = $field_qcase;
      if ($qscope == "case" && $qcase) $casekeyword = SEARCH_CaseKeywordForFolder($qcase."root");
      if ($qscope == "generic") $casekeyword = SEARCH_CaseKeywordForFolder("root");
      if ($casekeyword) {
        $q = $casekeyword . " " . $q;
      }
    }

  // LF: create "friendlyquery" (representing what the user wanted rather than what we do internally) for logging purposes  
  if (!($qr1 || $qr2 || $qr3 || $qr4)) $friendlyquery = $q;  
  if ($friendlyquery) $friendlyquery .= " ";  
  if ($qrwil) $friendlyquery .= $qrwil . " ";  
  if ($qrphr) $friendlyquery .= '"' . $qrphr . '" ';  
  if ($qr1) $friendlyquery .= ($c1 ? $c1 . ":[" : "") . $qr1 . ($c1 ? "]" : "") . " ";  
  if ($qr2) $friendlyquery .= ($c2 ? $c2 . ":[" : "") . $qr2 . ($c2 ? "]" : "") . " ";  
  if ($qr3) $friendlyquery .= ($c3 ? $c3 . ":[" : "") . $qr3 . ($c3 ? "]" : "") . " ";  
  if ($qr4) $friendlyquery .= ($c4 ? $c4 . ":[" : "") . $qr4 . ($c4 ? "]" : "") . " ";  
  if ($wstatus) $friendlyquery .= "workflow:[" . $wstatus . "] ";  
  if ($fileformat) $friendlyquery .= "filetype:[" . $fileformat . "] ";  
  if ($date)   $friendlyquery .= "date:[" . ($date < 30 ? $date + 1 : $date) . "d] ";  
  if ($qrn) $friendlyquery .=   '-[' . $qrn . '] ';  
  if ($qscope == "case" && $qcase) {  
    $casedata = MB_ref("ims_".IMS_SuperGroupName()."_case_data", $qcase);  
    $friendlyquery .= "case:[" . $casedata["shorttitle"] . "] ";  
  }  
  if ($qscope == "generic") {  
    $generictext = $myconfig[IMS_SuperGroupName()]["generictext"];  
    if (!$generictext) $generictext = ML("Algemeen", "Generic");  
    $friendlyquery .= "case:[" . $generictext . "] ";  
  }

//   *******************************

    $search_specs = array();
    $search_specs["query"] = $q;
    $search_specs["friendlyquery"] = $friendlyquery;
    $search_specs["from"] = $from;
    $search_specs["to"] = $to;
    $search_specs["sgn"] = $supergroupname;
    $search_specs["filterexpression"] = S3_DMS_FilterExpression(); //$expression;
    $search_specs["sortexpression"] =  S3_DMS_SortExpression($sortby, $i_will_also_call_extraenginespecs = true); //$sortbyexpression;
    $search_specs["multiloadexpression"] =  S3_DMS_MultiLoadExpression($i_will_also_call_extraenginespecs = true);
    $search_specs["extraenginespecs"] = S3_DMS_ExtraEngineSpecs();

    if ($index) {
      if (is_array ($myconfig["extradmsindex"])) foreach ($myconfig["extradmsindex"] as $name => $specs) {
        if ($specs["sgn"]==IMS_SuperGroupName() && $name == $index) {
          if (SHIELD_ValidateAccess_List (IMS_SuperGroupName(), SHIELD_CurrentUser(), $specs["advancedsearchstandardusergroups"])) {
            if ($pstatus=="published") {
              $theindex = "$supergroupname#publisheddocuments#extra#$index";
              $search_specs["index"] = $theindex;
              $tmpresult = S3_SEARCH ($search_specs);
            } else if ($pstatus=="") {
              $theindex = "$supergroupname#previewdocuments#extra#$index";
              $search_specs["index"] = $theindex;
              $tmpresult = S3_SEARCH ($search_specs);
            }
          }
        }
      }
    } else {
      if ($pstatus=="published") {
        $theindex = "$supergroupname#publisheddocuments";
        $search_specs["index"] = $theindex;
        $tmpresult = S3_SEARCH ($search_specs);
      } else if ($pstatus=="") {
        $theindex = "$supergroupname#previewdocuments";
        $search_specs["index"] = $theindex;

        $tmpresult = S3_SEARCH ($search_specs);
        //$tmpresult = SEARCH ($theindex, $q, $from, $to);
      }
    }

    if ($searchmode=="advanced") echo "<a name=\"results\" ></a>"; // LF20080403: anchor to support jumping directly to search results // JG tag met </A> afgesloten wegens lelijke underline;

    $list = $tmpresult["ignore"];
    if ($list) {
      echo "<br>".ML("Niet meegenomen met zoeken","Excluded from the search").": ";
      reset($list);
      while (list($word)=each($list)) {
        echo $word." ";
      }
      echo "<br>";
    }
    $amount = 0;
    if ($tmpresult["amount"]) {

// *************************************************************************************************
// old loop for parsing results
// *************************************************************************************************
       $amount = $tmpresult["amount"];
       $result = $tmpresult;
    }
    if ($amount) {
      if ($to > $amount) $to = $amount;
      if ($viewmode!="report") {
       // if ($result["more"]) {
       //   echo "<br><b>".ML("Resultaten","Results")." $from - $to ".ML("van","of")." ".ML("meer dan %1 resulaten","over %1 results", $result["limit"]).".</b>&nbsp;&nbsp;&nbsp;";
       // } else {
       //   echo "<br><b>".ML("Resultaten","Results")." $from - $to ".ML("van","of")." $amount.</b>&nbsp;&nbsp;&nbsp;";
       // }

       echo "<br><b>".$tmpresult["message"]."</b>&nbsp;&nbsp;&nbsp;";
      }

      $params = "&qr1=".urlencode($qr1);
      $params .= "&qr2=".urlencode($qr2);
      $params .= "&qr3=".urlencode($qr3);
      $params .= "&qr4=".urlencode($qr4);
      $params .= "&qropt=".urlencode($qropt);
      $params .= "&qrphr=".urlencode($qrphr);
      $params .= "&qrwil=".urlencode($qrwil);
      $params .= "&c1=".urlencode($c1);
      $params .= "&c2=".urlencode($c2);
      $params .= "&c3=".urlencode($c3);
      $params .= "&c4=".urlencode($c4);

      if ($index) $params .= "&index=" .urlencode($index);
      $params .= "&qrn=".urlencode($qrn);
      $params .= "&pstatus=".urlencode($pstatus);
      $params .= "&wstatus=".urlencode($wstatus);
      $params .= "&fileformat=".urlencode($fileformat);
      $params .= "&date=".urlencode($date);
      $params .= "&fromdate=".urlencode($fromdate);
      $params .= "&todate=".urlencode($todate);
      $params .= "&sortby=".urlencode($sortby);
      if ($myconfig[IMS_SuperGroupName()]["casesearch"] == "yes") {
        $params .= "&qscope=".urlencode($qscope);
        $params .= "&qcase=".urlencode($qcase);
      }

      global $myconfig;
      if ($from!=1) echo "<a class=\"ims_link\" href=\"".N_MyBareURL()."?mode=dms&submode=search&searchmode=$searchmode&q=".urlencode($q)."&from=".($from-$dms_search_results)."$params#results\">&lt;&lt; ".ML("Vorige","Previous")."</a>&nbsp;";
      if ($to!=$amount) echo "<a class=\"ims_link\" href=\"".N_MyBareURL()."?mode=dms&submode=search&searchmode=$searchmode&q=".urlencode($q)."&from=".($from+$dms_search_results)."$params#results\">".ML("Volgende","Next")." &gt;&gt;</a>";
      if ($myconfig[IMS_SupergroupName()]["multifileblock_in_advancedsearch"] != "yes")
      {
        if ($viewmode!="report" && $searchmode=="advanced") {
          echo "&nbsp;&nbsp;&nbsp;<a title=\"".ML("Toon resultaten als rapport in een nieuw venster", "Show results as report in a new window")."\" target=\"_blank\" href=\"".N_MyBareURL()."?mode=dms&submode=search&viewmode=report&index=$index&searchmode=$searchmode&q=".urlencode($q).$params."\"><img border=0 src=\"/ufc/rapid/openims/report.jpg\">&nbsp;".ML("Rapport", "Report")."</a>";
        }
        if ($viewmode!="report" && $searchmode=="advanced" && $myconfig[IMS_SuperGroupName()]["multifile"]=="yes") {
          echo "&nbsp;&nbsp;&nbsp;<a title=\"".ML("Selecteer alle gevonden documenten", "Select all found documents")."\" target=\"_blank\" href=\"".N_MyBareURL()."?mode=dms&submode=search&viewmode=report&selectmode=select&index=$index&searchmode=$searchmode&q=".urlencode($q).$params."\">".ML("Selecteer", "Select")."</a>";
          echo "&nbsp;&nbsp;&nbsp;<a title=\"".ML("De-selecteer alle gevonden documenten", "Deselect all found documents")."\" target=\"_blank\" href=\"".N_MyBareURL()."?mode=dms&submode=search&viewmode=report&selectmode=deselect&index=$index&searchmode=$searchmode&q=".urlencode($q).$params."\">".ML("De-selecteer", "Deselect")."</a>";
        }
      }

      if ($tmpresult["extramessage"]) echo "<br><br><b>".$tmpresult["extramessage"]."</b><br>";

      if ($viewmode!="report") {
        echo "<br><br>";
      }
      $r = $result["result"];
      if ($viewmode=="report") {
        T_Start ("ims");
          echo ML ("Document", "Document");

        T_Next();
          echo ML ("Status", "Status");
          T_Next();


        global $myconfig;
        if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
            echo ML ("Versie", "Version");
          T_Next();
        }

          echo ML ("Laatst gewijzigd", "Last Changed");
/*

        T_Next();
          echo ML ("Toegewezen", "Assigned");
*/
        T_NewRow();
      }


//   ******************************************************
      if ($sortby && false) {
        $rold = $r;
        $tmp = array();
        foreach ($rold as $object_id => $rating) {

          $object = &IMS_AccessObject ($supergroupname, $object_id);
          if ($sortby=="name") {
            $sortvalue = $object["shorttitle"];
          } else if ($sortby=="datedown") {
            if (is_array($object["history"])) {
              reset ($object["history"]);
              while (list($k, $data)=each($object["history"])) {
                $time = $data["when"];
              }
              $sortvalue = -$time;
            } else {
              $sortvalue = 0;
            }
          } else if ($sortby=="date") {
            if (is_array($object["history"])) {
              reset ($object["history"]);
              while (list($k, $data)=each($object["history"])) {
                $time = $data["when"];
              }
              $sortvalue = $time;
            } else {
              $sortvalue = 0;
            }
          }
          $tmp[$object_id] = $sortvalue;
        }
        asort ($tmp);
        $r = array();
        foreach ($tmp as $object_id => $dummy) {
          $r[$object_id] = $rold[$object_id];
        }
      }
      reset($r);
      while (list($object_id ,$res)=each($r)) {
        $ctr++;
        if ($ctr>=$from && $ctr<=$to) {
          $object = &IMS_AccessObject ($supergroupname, $object_id);

          $image = FILES_Icon ($supergroupname, $object_id, false, ($pstatus== "published" ? "published" : "preview"));
          $doc = FILES_TrueFileName ($supergroupname, $object_id, ($pstatus== "published" ? "published" : "preview"));
          $thedoctype = FILES_FileType ($supergroupname, $object_id, ($pstatus== "published" ? "published" : "preview"));

          if ($viewmode!="report") {
            if ($pstatus!="published") {
              $viewurl = FILES_TransViewPreviewURL ($supergroupname, $object_id);
            } else {
              $viewurl = FILES_TransViewPublishedURL ($supergroupname, $object_id);
            }
          }
          $box = "";
          if ($mode=="dms" and $submode=="search" and $viewmode != "report" and function_exists("OSICT_AdvancedSearchMouseover"))
            $box = OSICT_AdvancedSearchMouseover($object);
          
          if (!$box)
            $box = $object["longtitle"];
          
          $title = $object["shorttitle"];
          if ($title == "") $title = "???";
          if ($viewmode=="report") {
              echo "<img border=\"0\" src=\"$image\"> ";
              if ($selectmode=="select") {
                echo "<img border=\"0\" src=\"/ufc/rapid/openims/toggle_on.jpg\"> ";
                MULTI_Select ($object_id);
              }
              if ($selectmode=="deselect") {
                echo "<img border=\"0\" src=\"/ufc/rapid/openims/toggle_off.jpg\"> ";
                MULTI_Unselect ($object_id);
              }
              echo "$title";
            T_Next();
              echo SHIELD_CurrentStageName ($supergroupname, $object_id);
            global $myconfig;
            if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
              T_Next();
                echo IMS_Version ($supergroupname, $object_id);
            }
            T_Next();
              if (is_array($object["history"])) {
                reset ($object["history"]);
                while (list($k, $data)=each($object["history"])) {
                  $time = $data["when"];
                }
                echo "<nobr>".N_VisualDate ($time, 1, 1)."</nobr>";
              } else {
                echo " ";
              }
/*
            T_Next();
              if ($object["allocto"]) {
                $user_id = $object["allocto"];
                $user = MB_Ref ("shield_".$supergroupname."_users", $user_id);
                echo $user["name"];
              } else {
                echo " ";
              }
*/
            // new pdf preview
            T_NewRow();
          } else {

            if (SHIELD_HasObjectRight ($supergroupname, $object_id, "view")) {
              if ($searchmode=="advanced" and $myconfig[IMS_SupergroupName()]["multifile"] == "yes" and
                  $myconfig[IMS_SupergroupName()]["multifileblock_in_advancedsearch"] == "yes")
              {
                 $specs["input"] = $object_id;
                 $specs["state"] = MULTI_Selected($object_id);
                 $specs["on_code"] = 'uuse("multi"); MULTI_Select ($input);';
                 $specs["off_code"] = 'uuse("multi"); MULTI_UnSelect ($input);';
                 $specs["js_on_code"] = "
                   selectcounter = selectcounter + 1;
                   if (selectcounter == 1) {
                     multifileoptions = dyn;
                   ".DHTML_SetDynamicObject ("multifileoptions")."
                   }
                   " . DHTML_SetDynamicObject ("selectcounter");
                 $specs["js_off_code"] = "
                   selectcounter = selectcounter - 1;
                   if (selectcounter == 0) {
                   multifileoptions = stat;
                   ".DHTML_SetDynamicObject ("multifileoptions")."
                   }
                   " . DHTML_SetDynamicObject ("selectcounter");
                 $vink = DHTML_IntelliImage($specs, $object_id) ;
                 echo $vink . " ";
              }

              $title_view = ML("Bekijk","View") . " '" . $object["shorttitle"] . "'";
              $title_view = htmlentities($title_view);
              if ($myconfig [IMS_SuperGroupName()]["alloweditfromsearchresults"]=="yes") {
                if (SHIELD_HasObjectRight ($supergroupname, $object_id, "edit")) {

                  $title_edit = ML("Bewerk","Edit") . " '" . $title . "'";
                  $title_edit = htmlentities($title_edit);
                  $edit_url = FILES_TransEditURL ($supergroupname, $object_id);
                  echo "<a title=\"$title_edit\" href=\"$edit_url\"><img border=\"0\" src=\"$image\"></a> ";
                  echo "<a title=\"$title_view\" href=\"$viewurl\"><img border=\"0\" src=\"/ufc/rapid/openims/view.gif\"></a> ";
                } else {
                  echo "<img border=\"0\" src=\"$image\"> ";
                  echo "<a title=\"$title_view\"href=\"$viewurl\"><img border=\"0\" src=\"/ufc/rapid/openims/view.gif\"></a> ";
                }
              } else {
                echo "<a title=\"$title_view\"href=\"$viewurl\"><img border=\"0\" src=\"$image\"></a> ";
              }
              $url = FILES_DMSURL ($supergroupname, $object_id);// moved to here
              $tree = CASE_TreeRef ($supergroupname, $object["directory"]);
              $folderobj = TREE_AccessObject($tree, $object["directory"]);
              if (TREE_Visible($object["directory"], $folderobj)) {
                echo "<a title=\"".ML("Ga naar de folder","Go to folder")." ".DMSUIF_folderClickPath( $supergroupname , $object["directory"] )."\"href=\"$url\"><img border=\"0\" style=\"margin-right:2px;\" src=\"/ufc/rapid/openims/folder.gif\"></a>";
              }

              $showZoomIcon = $myconfig[$supergroupname]["searchresultswithpdfpreview"]=="yes" && ( $thedoctype=="pdf" || ( $myconfig[$supergroupname]["dmspreview_conversion"] == "yes" && WORD_isConvertableToPDFwithCurrentSettings( $supergroupname , $thedoctype ) ));
              if ( $showZoomIcon )
              {
                echo DHTML_PdfclickPreview($supergroupname, $object_id);

        if ( !$secondTime )
        {
          echo DMSUIF_pdfpreview_clickAway();
          $secondTime = false;
        }
                //ericd 210912 paden aangepast naar de gifs
                echo '<a href="javascript:;" onclick="hidePdfPreview();showPdfPreview(\'pdfpreview_'.$object_id.'\');"><img border="0" class="imagezoomin" src="/openims/magnifier.gif" onmouseover="this.src=\'/openims/magnifier-closed.gif\'" onmouseout="this.src=\'/openims/magnifier.gif\'" /></a> ';
              }

              echo "<a title=\"$box\" class=\"ims_result\" href=\"$viewurl\">";
              for ($i=0; $i<$count; $i++) {
                if (trim($words[$i])) {
                  //$words[$i] = str_replace(array("/","."),array(".","\/"),$words[$i]);
                  if ($title." "!=" ") {
                    $title = preg_replace ("/([^a-zA-Z]|^)(".preg_quote($words[$i], "/").")([^a-zA-Z]|\$)/i", "\\1<b>\\2</b>\\3", $title);
                  }
                }
               }
              $title = trim ($title);
              echo "$title</a> ";
//              $url = FILES_DMSURL ($supergroupname, $object_id);// moved up


              global $myconfig;
              if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
                echo " (".ML("Versie","Version")." ".trim(IMS_Version (IMS_Supergroupname(), $object_id)).") ";
              }

              // foldericon moved from here

              echo " ".SHIELD_CurrentStageName ($supergroupname, $object_id)." ";

              if (is_array($object["history"])) {
                foreach ($object["history"] as $dummy => $data) {
                  $time = $data["when"];
                }
                echo N_VisualDate ($time, 1, 1);
              } else {
                echo " ";
              }

              echo "<br>";
              if (!$myconfig[IMS_SuperGroupName()]["dmssearchexcludefromsummary"] || !in_array(FILES_FileType($supergroupname, $object_id, "preview"), $myconfig[IMS_SuperGroupName()]["dmssearchexcludefromsummary"])) {
                echo SEARCH_Summary ($theindex, $q, $object_id);
              }
              echo "<br><br>";
            } else if (SHIELD_HasObjectRight ($supergroupname, $object_id, "viewpub") && $object["published"]=="yes") {

              $title_view = ML("Bekijk","View") . "' " . $object["shorttitle"] . "'";
              $title_view = htmlentities($title_view);

              $viewurl = FILES_TransViewPublishedURL (IMS_SuperGroupName(), $object_id);
              if ($myconfig [IMS_SuperGroupName()]["alloweditfromsearchresults"]=="yes") {
                echo "<img border=\"0\" src=\"$image\"> ";
                echo "<a title=\"$title_view\"href=\"$viewurl\"><img border=\"0\" src=\"/ufc/rapid/openims/viewgrey.gif\"></a> ";
              } else {
                echo "<a title=\"$title_view\"href=\"$viewurl\"><img border=\"0\" src=\"$image\"></a> ";
              }

              echo "<a title=\"$box\" class=\"ims_result\" href=\"$viewurl\">";
              for ($i=0; $i<$count; $i++) {
                if (trim($words[$i])) {
                  //$words[$i] = str_replace(array("/","."),array(".","\/"),$words[$i]);
                  if ($title." "!=" ") {
                    $title = preg_replace ("/([^a-zA-Z]|^)(".preg_quote($words[$i], "/").")([^a-zA-Z]|\$)/i", "\\1<b>\\2</b>\\3", $title);
                  }
                }
               }
              $title = trim ($title);
              echo "$title</a> ";
              $url = FILES_DMSURL ($supergroupname, $object_id);

              $tree = CASE_TreeRef ($supergroupname, $object["directory"]);
              $folderobj = TREE_AccessObject($tree, $object["directory"]);
              if (TREE_Visible($object["directory"], $folderobj)) {
                echo " <a title=\"".ML("Ga naar de folder","Go to folder")."\"href=\"$url\"><img border=\"0\" src=\"/ufc/rapid/openims/folder.gif\"></a> ";
              }

              if (is_array($object["history"])) {
                foreach ($object["history"] as $dummy => $data) {
                  $time = $data["when"];
                }
                echo N_VisualDate ($time, 0, 1);
              } else {
                echo " ";
              }
              echo "<br>";

              echo ML("U mag van dit document alleen de laatst gepubliceerde versie zien","You are only allowed to see the latest published version of this document").".";
              echo "<br><br>";

            } else {

              echo "<img border=\"0\" src=\"$image\">";
              for ($i=0; $i<$count; $i++) {
                if (trim($words[$i])) {
                  $title = preg_replace ("/([^a-zA-Z]|^)(".preg_quote($words[$i], "/").")([^a-zA-Z]|\$)/i", "\\1<b>\\2</b>\\3", $title);
                }
               }
              $title = trim ($title);

              global $myconfig;
              if ($myconfig[$supergroupname]["searchresultmouseoverforprotecteddocuments"] == "yes") {
                $user_id = $object["allocto"];
                $user = MB_Ref ("shield_".$supergroupname."_users", $user_id);
                $username = $user["name"];
                $title = "<a title=\"".ML("Dit document is toegewezen aan","This document is allocated to")." ".$username."\">" .
                          $title .
                          "</a>";
              }

              echo "&nbsp;$title<br>";
              echo ML("U heeft niet voldoende rechten om dit document te bekijken","You have insufficient rights to view this document").".";
              echo "<br><br>";

            }

          }
        }
      }
      if ($viewmode=="report") {
        TE_End();
      }
    } else {
      echo "<br>".$tmpresult["extramessage"]."<br><br>";

      //echo "<br><b>".ML("Uw zoekopdracht heeft geen documenten opgeleverd","Your search has not found any documents").".</b><br><br>";
      //echo ML("Suggesties","Tips").":<br>";
      //echo "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Zorg ervoor dat alle woorden goed gespeld zijn","Make sure the spelling is correct").".<br>";
      //echo "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Probeer andere zoektermen","Try other terms").".<br>";
      //echo "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Maak de zoektermen algemener","Use more generic terms").".<br>";
      //if ($count>1) echo "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Gebruik minder termen","Use less terms").".<br>";
    }
  }

    if ($viewmode!="report") {
      echo "<br>";
      endblock();
    }


    } else if ($submode=="recent") {

//    if (($myconfig[$supergroupname]["onlymyown"] == "yes")&&(!$onlymyown)) $onlymyown = "yes";
    if (!$onlymyown) $onlymyown="yes";

    if ($myconfig[$supergroupname]["showowneditsonly"] == "yes") {
      startblock (ML("Recent gewijzigd","Recently changed")." (".ML("door mijzelf","by me").")", "docnav");

    } else {

        if ($onlymyown=="yes") {
        $url = N_AlterURL (N_MyFullURL(), "onlymyown", "no");
        startblock (ML("Recent gewijzigd","Recently changed")." (<a title=\"" . ML("Kies iedereen","Choose everyone") . "\" href=\"$url\" class=\"ims_headnav\">".ML("door mijzelf","by me")."</a>)", "docnav");
        } else {
        $url = N_AlterURL (N_MyFullURL(), "onlymyown", "yes");
        startblock (ML("Recent gewijzigd","Recently changed")." (<a title=\"" . ML("Kies mijzelf","Choose me") . "\" href=\"$url\" class=\"ims_headnav\">".ML("door iedereen","by everyone")."</a>)", "docnav");  
       }
    } 

     echo "<br>";
    T_Start ("ims");
    echo ML("Datum","Date")." <img src=\"/ufc/rapid/openims/sortdown.gif\">";

    T_Next();
    echo ML("Document","Document");
    T_Next();
    T_Next();
    echo ML("Status","Status");
    T_Next();
    if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
      echo ML ("Versie", "Version");
      T_Next();
    }
    if ($myconfig[$supergroupname]["onlymyown"] !== "yes") {
    echo ML("Door","By");
    T_Newrow();
    }else {
    echo ML("Toegewezen", "Allocto");
    T_Newrow();
    }

    if ($onlymyown =="yes" || $myconfig[$supergroupname]["showowneditsonly"] == "yes") {
         $list = MB_TurboMultiQuery ("ims_".$supergroupname."_objects", array (
         "rsort" => 'QRY_RecentlyChangedDocuments_v1 ($record)',
         "select" => array (
          'QRY_RecentlyChangedBy_v1 ($record)' => SHIELD_CurrentUser()
        ),
        "slice" => array (1,25),
        "value" =>  'QRY_RecentlyChangedDocuments_v1 ($record)'
      ));
      } else {
        $list = MB_TurboTopQuery ("ims_".$supergroupname."_objects", 'QRY_RecentlyChangedDocuments_v1 ($record)', 25);
     }

    foreach ($list as $key => $date) {
      if(SHIELD_HasObjectRight ($supergroupname, $key, "view")) {
        $rec = MB_Ref ("ims_".$supergroupname."_objects", $key);
        if ($rec ["objecttype"]=="document" && ($rec["published"]=="yes" || $rec["preview"]=="yes")) {

          $workflow = &SHIELD_AccessWorkflow ($supergroupname, $rec["workflow"]);
          echo "<nobr>" . N_VisualDate ($date, true, true) . "</nobr>";
          T_Next();
          $object = $rec;

          $image = FILES_Icon ($supergroupname, $key, false, "preview");
          $doc = FILES_TrueFileName ($supergroupname, $key, "preview");
          $thedoctype = FILES_FileType ($supergroupname, $key, "preview");

          $url = FILES_DMSURL ($supergroupname, $key);
          $box = $object["longtitle"];
          $title = $object["shorttitle"];
          if (!function_exists ("DMS_MouseOver")) {
            // DMS mouse over
            $internal_component = FLEX_LoadImportableComponent ("support", "08fa2037f2f020a44e9aac15d6d92135");
            $internal_code = $internal_component["code"];
            eval ($internal_code);
          }
          $title2 = DMS_MouseOver(IMS_SuperGroupName(),$key, "view");
          $tree = CASE_TreeRef ($supergroupname, $object["directory"]);
          $folderobj = TREE_AccessObject($tree, $object["directory"]);

          if ($title == "") $title = "???";
          $dms_click_path = DMSUIF_folderClickPath( $supergroupname ,  $object["directory"] );

           if (TREE_Visible($object["directory"], $folderobj) and ($myconfig[IMS_Supergroupname()]["dmsmouseover"]=="yes"))  {
             echo "<a  title=\"$title\" href=\"$url\"><img border=\"0\" alt=\"$title2\" src=\"$image\"></a> ";
          } else { 
           if (TREE_Visible($object["directory"], $folderobj)) {
                echo "<a title=\"$title ($dms_click_path)\" href=\"$url\"><img border=\"0\" src=\"$image\"></a> ";
             } else {
                echo "<img border=\"0\" src=\"$image\">";
             }
          }
          T_Next();
          if (TREE_Visible($object["directory"], $folderobj) and ($myconfig[IMS_Supergroupname()]["dmsmouseover"]=="yes")) {
              echo "<a title=\"$title2\" class=\"ims_link\" href=\"$url\">$title</a>";
          } else {
            if (TREE_Visible($object["directory"], $folderobj)) {
                echo "<a title=\"$box ($dms_click_path)\" class=\"ims_link\" href=\"$url\">$title</a>";
             } else {
                echo "$title";
             }
          }
          T_Next();
          if ($myconfig[IMS_Supergroupname()]["nobr"]=="yes") {
           echo $workflow[$rec["stage"]]["name"];
          }else {
           echo "<nobr>" .$workflow[$rec["stage"]]["name"]. "</nobr>";
          }
          T_Next();
          if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
            echo IMS_Version (IMS_Supergroupname(), $key);
            T_Next();
          }
          $who="";
          $max = 0;
      if ($myconfig[$supergroupname]["onlymyown"] !== "yes") {
          if (is_array($rec["history"])) {
            foreach ($rec["history"] as $guid => $spec) {
              if ($spec["when"] > $max) {
                $max = $spec["when"];
                $who = $spec["author"];
              }

            }
          }
           $user = MB_Ref ("shield_".$supergroupname."_users", $who);
          if ($user["name"]) {
           if ($myconfig[IMS_Supergroupname()]["nobr"]=="yes") {
                echo $user["name"];
            }else {echo "<nobr>" .$user["name"]. "</nobr>";}
          } else {
            echo " ";
          }
          T_NewRow();
       }else {
          $user = MB_LOAD ("shield_".$supergroupname."_users", $rec["allocto"]);
           if ($user["name"]) {
             if ($myconfig[IMS_Supergroupname()]["nobr"]=="yes") {
               echo $user["name"];
             } else { echo "<nobr>" .$user["name"]. "</nobr>";}
          } else {
            echo " ";
          }
         T_NewRow();
       }
        }
      }
    }
    TE_End();
    echo "<br>";
   endblock();

    } else if ($submode=="dmsview") {

      $list = FLEX_LocalComponents (IMS_SuperGroupName(), "dmsview");
      $specs = $list[$dmsviewid];
      if ($specs["recheckcondition"]) {
        $result = false;
        eval ($specs["code_condition"]);
        if (!$result) N_Redirect("/openims/openims.php?mode=dms");
      }
      startblock ($specs["title"], "docnav");
      eval (N_GeneratePreEvalCleanupCode());
      eval ($specs["code_contentgenerator"]);
      eval (N_GeneratePostEvalCleanupCode ($specs["code_contentgenerator"]));
      echo $content;
      endblock();
    } else if ($submode=="autotableview") {
      $list = FLEX_LocalComponents (IMS_SuperGroupName(), "autotableview");
      $specs = $list[$autotableviewid];

      // recheck the condition (so sending hyperlinks to other users will NOT work)
      $result = false;
      eval ($specs["code_condition"]);
      if (!$result) N_Redirect("/openims/openims.php?mode=dms");

      startblock ($specs["title"], "docnav");
      echo DMSUIF_Autotableview_Content($autotableviewid);
      endblock();
    } else if ($submode=="projects" && !$rootfolder) {

      if ($myconfig[IMS_SuperGroupName()]["projectstext"]) {
        startblock ($myconfig[IMS_SuperGroupName()]["projectstext"], "docnav");
      } else {
        startblock (ML("Projecten","Projects"), "docnav");
      }
      echo "<br>";
      if (!$filter) {
        echo "&nbsp;<a class=\"ims_active\" href=\"/openims/openims.php?mode=dms&submode=projects\">*</a>";
      } else {
        echo "&nbsp;<a class=\"ims_navigation\" href=\"/openims/openims.php?mode=dms&submode=projects\">*</a>";
      }
      for ($i=1; $i<=36; $i++) {
        $c = substr ("_0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ", $i, 1);
        if ($filter=="$c") {
          echo "&nbsp;<a class=\"ims_active\" href=\"/openims/openims.php?mode=dms&submode=projects&filter=$c\">$c</a>";
        } else {
          echo "&nbsp;<a class=\"ims_navigation\" href=\"/openims/openims.php?mode=dms&submode=projects&filter=$c\">$c</a>";
        }
      }
      echo "<br><br>";

      $tree = CASE_TreeRef ($supergroupname, $currentfolder);
      $projects = TREE_AccessObject ($tree, "projects");

      foreach ($projects["children"] as $child => $dummy) {
        $node = TREE_AccessObject ($tree, $child);
        $list[$node["shorttitle"]] = $child;
      }
      ksort ($list);
      foreach ($list as $title => $id) {
        if (!$filter || strtoupper(substr($title,0,1))==$filter) {
          echo "<a class=\"ims_navigation\" title=\"$commandtitle\" href=\"/openims/openims.php?mode=dms&submode=projects&rootfolder=$id\">";
          echo "<img border=0 src=\"/ufc/rapid/openims/folder.gif\">&nbsp;";
          echo $title;
          echo "</a><br>";
        }
      }
      echo "<br>";

      endblock();


    } else if ($submode=="documents" || $submode=="activities" || $submode=="alloced" || ($submode=="projects" && $rootfolder)) {

      if ($submode=="documents" || $submode=="projects") {
/*
        $path = TREE_Path ($tree, $currentfolder);

        $pathmode="all";
        $url = "/openims/openims.php?mode=dms&currentfolder=".$path[1]["id"];
        if (substr ($currentfolder, 0, 1)=="(") {
          $case_id = substr ($currentfolder, 0, strpos ($currentfolder, ")")+1);
          $case = MB_Ref ("ims_".IMS_SuperGroupName()."_case_data", $case_id);
          if ($myconfig[IMS_SuperGroupName()]["casetypes"]=="yes" && $myconfig[IMS_SuperGroupName()]["casetypeinclickpath"]=="yes") {
            // Add casetype
            $viscasetype = MB_Fetch("ims_".IMS_SuperGroupName()."_case_types", $case["category"], "name");
            $casetypeurl = "/openims/openims.php?mode=dms&submode=cases&casetype=".$case["category"];
            $pathtitle .= "<a title = \"".ML("Dossiercategorie","Case type") . ": " . N_Htmlentities($viscasetype)."\" class=\"ims_headnav\" href=\"$casetypeurl\">".N_htmlentities($viscasetype)."</a>: ";
          } 
// 20101207 KvD SWEBRU-45: Aanhalingstekens moeten ook kunnen
          $pathtitle .= "<a title = \"".N_htmlentities($case["shorttitle"])."\" class=\"ims_headnav\" href=\"$url\">".N_htmlentities(($case["longtitle"].""=="")?$case["shorttitle"]:$case["longtitle"])."</a> &gt;&gt; ";

        }
        $pathtitle .= "<a title=\"".N_htmlentities($path[1]["longtitle"])."\" class=\"ims_headnav\" href=\"$url\">".$path[1]["shorttitle"]."</a>";
///
        for ($i=2; $i<=count($path); $i++) {
          if ($path[$i]["id"] == $rootfolder) $pathmode="projects";
          if ($pathmode=="projects") {
            $url = "/openims/openims.php?mode=dms&submode=projects&rootfolder=$rootfolder&currentfolder=".$path[$i]["id"];
          } else {
            $url = "/openims/openims.php?mode=dms&currentfolder=".$path[$i]["id"];
          }
          $pathtitle .= " &gt; "."<a title=\"".$path[$i]["longtitle"]."\" class=\"ims_headnav\" href=\"$url\">".$path[$i]["shorttitle"]."</a>";
        }
*/
//        unset($url);unset($viscasetype);unset($casetypeur);unset($case);unset($case_id);unset($path);
        $pathtitle = DMSUIF_folderClickPath( $supergroupname , $currentfolder , true );

        startblock ($pathtitle, "docnav" , "", "centerBlock" ); //print("DITISDEJUISTEPLEK!");
      } else if ($submode=="activities") {
        startblock ($blocktitle, "docnav");
      } else {
        if (!$activeuser) $activeuser = SHIELD_CurrentUser($supergroupname);
        $user = MB_Ref ("shield_".$supergroupname."_users", $activeuser);
        $form = array();
        $form["title"] = ML("Selecteer persoon", "Select user");
        $form["metaspec"]["fields"]["user"]["type"] = "list";
        $form["formtemplate"] = '
          <table>
            <tr><td><font face="arial" size=2><b>'.ML("Persoon","User").':</b></font></td><td>[[[user]]]</td></tr>
            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["precode"] = '
          global $myconfig;
          if ($myconfig[IMS_SuperGroupName()]["reassigndefaultstoempty"] == "yes") {
            $metaspec["fields"]["user"]["values"][ML("Kies...", "Choose...")] = "";
          }
          $users = MB_Query ("shield_".IMS_SuperGroupName()."_users");
          foreach ($users as $user_id => $dummy) {
            $tmpuser = MB_Ref ("shield_".IMS_SuperGroupName()."_users", $user_id);
            $metaspec["fields"]["user"]["values"][$tmpuser["name"]] = $user_id;
          }
        ';
        $form["postcode"] = '
          uuse("shield");
          if (!$data["user"]) $data["user"] = SHIELD_CurrentUser(IMS_SuperGroupName());
          $gotook = "closeme&parentgoto:/openims/openims.php?mode=dms&submode=alloced&activeuser=".$data["user"];
        ';
        $url = FORMS_URL ($form);

        if ($myconfig[IMS_SupergroupName()]["multiapprove"] == "yes" && $submode == "alloced") {
          // Start the "For approval" block
          // Capture the output, so that we can hide the "For approval" block if a user has no documents
          ob_start();
          startblock (ML("Ter goedkeuring","For approval")." (<a title=\"" . $form["title"] . "\"href=\"$url\" class=\"ims_headnav\">".$user["name"]."</a>)", "docnav");

          // The code below is going to generate the "Assigned" block first, and only when that is finished, will the
          // "For approval" block be created. We use output capturing so that we can show the "Assigned" block after the "For approval" block.
          // So for now, we capture all output associated with the "Assigned" block.
          ob_start();
        }
        startblock (ML("Toegewezen","Assigned")." (<a title=\"" . $form["title"] . "\"href=\"$url\" class=\"ims_headnav\">".$user["name"]."</a>)", "docnav");
      }

  //    echo "<br>";  // BREAK WEG IS BOVEN HOOFDFOLDER NAVIGATIE!

      //ericd 170912 always show case description 
      global $currentfolder; 
      if (substr($currentfolder,-5)==")root") { //only show in rootfolder of case
         $casedescription = MB_Fetch("ims_".IMS_SuperGroupName()."_case_data",substr($currentfolder,0,strpos($currentfolder,")")+1),"description") ;
         if ($casedescription." "!=" ") echo N_XML2HTML($casedescription)."<br><br>";
       } 

      // GV 16-9-2010
      if (function_exists("GetCustomCasePlaceHolderData")) { 
        echo GetCustomCasePlaceHolderData(); 
      } 

   // GV  16-9-2010 // name should be better
   //   if (function_exists("GetCustomCasePlaceHolderData")) {
   //      echo GetCustomCasePlaceHolderData();
   //    } else {     
   //     global $currentfolder;
   //     if (substr($currentfolder,-5)==")root") { //only show in rootfolder of case
   //       $casedescription = MB_Fetch("ims_".IMS_SuperGroupName()."_case_data",substr($currentfolder,0,strpos($currentfolder,")")+1),"description") ;
   //       if ($casedescription." "!=" ") echo N_XML2HTML($casedescription)."<br><br>";
   //     }
   //   }

      if ($submode=="documents" || $submode=="projects") {
        $lijst_select = array (
          '$record["objecttype"]=="document" || $record["objecttype"]=="shortcut"' => true,
          '$record["directory"]' => $currentfolder,
          '$record["published"]=="yes" || $record["preview"]=="yes"' => true
        );
      } else if ($submode=="activities") {
        if ($thecase=="allofthem") {
          $lijst_select = array(
            '$record["stage"]' => $act,
            '$record["objecttype"]' => "document",
            '$record["workflow"]' => $wfl,
            '($record["published"]=="yes") || ($record["preview"]=="yes")' => true
          );
          // value: , 'strtolower($record["shorttitle"])');
        } else if ($thecase) {

          $lijst_select = array(
            'N_KeepBefore ($record["directory"], ")").")"' => $thecase,
            '$record["stage"]' => $act,
            '$record["objecttype"]' => "document",
            '$record["workflow"]' => $wfl,
            '($record["published"]=="yes") || ($record["preview"]=="yes")' => true
          );

          // value: , 'strtolower($record["shorttitle"])');

        } else {
          $lijst_select = array(
            'N_KeepBefore ($record["directory"], ")")' => "",
            '$record["stage"]' => $act,

            '$record["objecttype"]' => "document",
            '$record["workflow"]' => $wfl,
            '($record["published"]=="yes") || ($record["preview"]=="yes")' => true
          );
          // value: 'strtolower($record["shorttitle"])');
        }
      } else {
        $user_id = $activeuser;
        $lijst_select =  array (
          '$record["objecttype"]' => "document",
          '$record["allocto"]' => $user_id,
          '$record["preview"]' => 'yes'
        );
        // value: 'strtolower($record["shorttitle"])');
      }

      if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
        if ($submode=="documents") {
          $sort_default_col = "";
          $sort_default_dir = "";
          if ($myconfig[IMS_SuperGroupName()]["donotsavesortparams"] != "yes") {
            // retrieve saved sort params from "shield_$sgn_users_sortparams"
            global $currentfolder;
            $sortparams = &MB_Ref("shield_" . IMS_SuperGroupName() . "_users_sortparams", SHIELD_CurrentUser (IMS_SuperGroupName())."#".$currentfolder);
            $sort_default_col = $sortparams["tblsrt"];
            $sort_default_dir = $sortparams["tbldir"];
            // if there were any url params, save them in the database
            // (no need to set $sort_default_col etc., since uuse_tables looks at url params too)
            global $tbldir_dmsgrid, $tblsrt_dmsgrid;
            if ($tbldir_dmsgrid) $sortparams["tbldir"] = $tbldir_dmsgrid;
            if ($tblsrt_dmsgrid) $sortparams["tblsrt"] = $tblsrt_dmsgrid;
          }
          if (!$sort_default_col) { // No saved sort order found, try site wide default
            if ($myconfig[IMS_Supergroupname()]["defaultsortcol"]) $sort_default_col = $myconfig[IMS_Supergroupname()]["defaultsortcol"];
          }
          if (!$sort_default_dir) { // No saved sort direction found, try site wide default
            if ($myconfig[IMS_Supergroupname()]["defaultsortdir"]) $sort_default_dir = $myconfig[IMS_Supergroupname()]["defaultsortdir"];
          }
          if (!$sort_default_col) $sort_default_col = 1;
          if (!$sort_default_dir) $sort_default_dir = "u";

          $lijst_tablespecs = array ("sort_default_col" => $sort_default_col, "sort_default_dir" => $sort_default_dir, "sort_map_1" => 2,
            "sort_1"=>"auto", "sort_3"=>"auto", "sort_4"=>"auto", "sort_5"=>"date", "sort_6"=>"auto");
          //"sort"=>"dms_docs",
        }
        if ($submode=="alloced") {
          $lijst_tablespecs = array ("sort_default_col" => 5, "sort_default_dir" => "d", "sort_map_1" => 2,
            "sort_1"=>"auto", "sort_3"=>"auto", "sort_4"=>"auto", "sort_5"=>"date");
          //"sort"=>"dms_docs",
        }
        if ($submode=="projects") {
          $lijst_tablespecs = array ("sort_default_col" => 1, "sort_default_dir" => "u", "sort_map_1" => 2,
            "sort_1"=>"auto", "sort_3"=>"auto", "sort_4"=>"auto", "sort_5"=>"date", "sort_6"=>"auto");
          //"sort"=>"dms_docs",
        }
        if ($submode=="activities") {
          $lijst_tablespecs = array ("sort_default_col" => 1, "sort_default_dir" => "u", "sort_map_1" => 2,
            "sort_1"=>"auto", "sort_3"=>"auto", "sort_4"=>"date", "sort_5"=>"auto");
          //"sort"=>"dms_docs",
        }
      }
      else {
        if ($submode=="documents") {
          $sort_default_col = "";
          $sort_default_dir = "";
          if ($myconfig[IMS_SuperGroupName()]["donotsavesortparams"] != "yes") {
            // retrieve saved sort params from "shield_$sgn_users_sortparams"
            global $currentfolder;
            $sortparams = &MB_Ref("shield_" . IMS_SuperGroupName() . "_users_sortparams2", SHIELD_CurrentUser (IMS_SuperGroupName())."#".$currentfolder);
            $sort_default_col = $sortparams["tblsrt"];
            $sort_default_dir = $sortparams["tbldir"];
            // if there were any url params, save them in the database
            // (no need to set $sort_default_col etc., since uuse_tables looks at url params too)
            global $tbldir_dmsgrid, $tblsrt_dmsgrid;
            if ($tbldir_dmsgrid) $sortparams["tbldir"] = $tbldir_dmsgrid;
            if ($tblsrt_dmsgrid) $sortparams["tblsrt"] = $tblsrt_dmsgrid;
          }
          if (!$sort_default_col) { // No saved sort order found, try site wide default
            if ($myconfig[IMS_Supergroupname()]["defaultsortcol"]) $sort_default_col = $myconfig[IMS_Supergroupname()]["defaultsortcol"];
          }
          if (!$sort_default_dir) { // No saved sort direction found, try site wide default
            if ($myconfig[IMS_Supergroupname()]["defaultsortdir"]) $sort_default_dir = $myconfig[IMS_Supergroupname()]["defaultsortdir"];
          }
          if (!$sort_default_col) $sort_default_col = 1;
          if (!$sort_default_dir) $sort_default_dir = "u";
          $lijst_tablespecs = array ("sort_default_col" => $sort_default_col, "sort_default_dir" => $sort_default_dir, "sort_map_1" => 2,
            "sort_1"=>"auto", "sort_3"=>"auto", "sort_4"=>"date", "sort_5"=>"auto");
          //"sort"=>"dms_docs",
        }
        if ($submode=="alloced") {
          $lijst_tablespecs = array ("sort_default_col" => 1, "sort_default_dir" => "u", "sort_map_1" => 2,
            "sort_1"=>"auto", "sort_3"=>"auto", "sort_4"=>"date");
          //"sort"=>"dms_docs",
        }
        if ($submode=="projects") {
          $lijst_tablespecs = array ("sort_default_col" => 1, "sort_default_dir" => "u", "sort_map_1" => 2,
            "sort_1"=>"auto", "sort_3"=>"auto", "sort_4"=>"date", "sort_5"=>"auto");
          //"sort"=>"dms_docs",
        }

        if ($submode=="activities") {
          $lijst_tablespecs = array ("sort_default_col" => 1, "sort_default_dir" => "u", "sort_map_1" => 2,
            "sort_1"=>"auto", "sort_3"=>"date", "sort_4"=>"auto");
          //"sort"=>"dms_docs",
        }
      }

      $lijst_tableheads = array (ML("Document","Document"),"");
      if ($submode=="documents" || $submode=="alloced" || $submode=="projects") {
        $lijst_tableheads = N_array_merge($lijst_tableheads, array(ML("Status","Status")));
      }
      if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
        $lijst_tableheads = N_array_merge($lijst_tableheads, array(ML("Versie","Version")));
      }
      $lijst_tableheads = N_array_merge($lijst_tableheads, array(ML("Laatst gewijzigd","Last changed")));
      if ($submode=="activities" || $submode=="documents" || $submode=="projects") {
        $lijst_tableheads = N_array_merge($lijst_tableheads, array(ML("Toegewezen","Assigned")));
      }

      $lijst_sort = array ('',  'QRY_DMS_Name_v1 ($record)');
      if ($submode=="documents" || $submode=="alloced" || $submode=="projects") {
        $lijst_sort = N_array_merge($lijst_sort, array('QRY_DMS_Status_v1 ("'.IMS_SuperGroupName().'", $key, $record)'));
      }
      if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
        $lijst_sort = N_array_merge($lijst_sort, array('QRY_DMS_Version_v1 ("'.IMS_SuperGroupName().'", $key, $record)'));
      }
      $lijst_sort = N_array_merge($lijst_sort, array('QRY_DMS_Changed_v1 ($record)'));
      if ($submode=="activities" || $submode=="documents" || $submode=="projects") {
        $lijst_sort = N_array_merge($lijst_sort, array('QRY_DMS_Assigned_v1 ("'.IMS_SuperGroupName().'", $record)'));
      }

      $lijst_filterexp =
        'QRY_DMS_Name_v1 ($record) . " " .
         QRY_DMS_Status_v1 ("'.IMS_SuperGroupName().'", $key, $record) . " " .
         N_VisualDate (QRY_DMS_Changed_v1 ($record), 1, 1) . " " .
         QRY_DMS_Assigned_v1 ("'.IMS_SuperGroupName().'", $record)
        ';

      global $myconfig;
      if ($myconfig[IMS_SuperGroupName()]["invisiblemetadatafilter"] == "yes") {
        $lijst_filterexp .= ' . " " . QRY_ListMetaDataValues("'.IMS_SuperGroupName().'",$record)';
      }


      $commandurl = "/openims/openims.php?mode=dms&submode=$submode&thecase=$thecase&rootfolder=$rootfolder&currentfolder=$currentfolder&act=$act&wfl=".urlencode($wfl)."&activeuser=".urlencode($activeuser);
      $commandurl .= "&tblsrt_dmsgrid=$tblsrt_dmsgrid&tbldir_dmsgrid=$tbldir_dmsgrid";

      global $tblflt_dmsgrid;//sbr 21-9-2007
      if ($tblflt_dmsgrid." "!=" ") {
         $commandurl .= "&tblflt_dmsgrid=".urlencode(stripcslashes($tblflt_dmsgrid));
      }
      for ($i = 1; $i <= 20; $i++) {
        if ($_REQUEST["tblcolflt_dmsgrid_$i"]) $commandurl .= "&tblcolflt_dmsgrid_$i=".urlencode(stripcslashes($_REQUEST["tblcolflt_dmsgrid_$i"]));
      }

      if (!function_exists ("DMS_MouseOver")) {
      // DMS mouse over
        $internal_component = FLEX_LoadImportableComponent ("support", "08fa2037f2f020a44e9aac15d6d92135");
        $internal_code = $internal_component["code"];
        eval ($internal_code);
      }

      $selecton = (($mode == "dms" and $submode == "documents" and $_GET["select"] == "page") ? 'true' : 'false');
      $lijst_content = array ('
          echo "<nobr>";
          $truekey = $key;
          $url = $viewurl = "";
          if (FILES_IsShortcut (IMS_SuperGroupName(), $key) && (!FILES_IsPermalink (IMS_SuperGroupName(), $key)) ) {
            $key = FILES_Base (IMS_SuperGroupName(), $key);
            $image = FILES_Icon (IMS_SuperGroupName(), $key, true, "preview");
            $ob = MB_Load ("ims_".IMS_SuperGroupName()."_objects", $key);
            $url = FILES_TransEditURL (IMS_SuperGroupName(), $key);
            $viewurl = FILES_TransViewPreviewURL (IMS_SuperGroupName(), $key);
          } elseif (FILES_IsShortcut (IMS_SuperGroupName(), $key) && FILES_IsPermalink (IMS_SuperGroupName(), $key) ) {
            $key = FILES_Base (IMS_SuperGroupName(), $key); // qqq
            $iconkey = $key;
            $image = FILES_Icon (IMS_SuperGroupName(), $iconkey, true, "preview");
            $ob = MB_Load ("ims_".IMS_SuperGroupName()."_objects", $truekey);
            $permaref = MB_Load ("ims_".IMS_SuperGroupName()."_objects", $truekey);
            $historykey = $permaref["sourceversion"];

            //ericd 170310 use truekey so that SHIELD_HasObjectRight can check for permalinkalwaysviewdoc setting
            if (SHIELD_HasObjectRight (IMS_SuperGroupName(), $truekey, "view") && FILES_HistoryVersionExistsOnDisk( IMS_SuperGroupName(), $permaref["source"], $historykey))
            {
              $url      = FILES_TransViewHistoryURL( IMS_SuperGroupName(), $permaref["source"], $historykey ); // read-only
              $viewurl  = $url;
            }

          } else {
            $image = FILES_Icon (IMS_SuperGroupName(), $key, false, "preview");
            $ob = $object;
            $url = FILES_TransEditURL (IMS_SuperGroupName(), $key);
            $viewurl = FILES_TransViewPreviewURL (IMS_SuperGroupName(), $key);
          } // qqq

          $title = DMS_MouseOver(IMS_SuperGroupName(), $key, "edit");
          $viewtitle = DMS_MouseOver(IMS_SuperGroupName(), $key, "view");
          if (FILES_IsIndependentShortcut(IMS_SuperGroupName(), $truekey)) {
            uuse("dmsuif");
            $title = DMSUIF_FixMouseoverForIndependentShortcut(IMS_SuperGroupName(), $truekey, $key, $title);
            $viewtitle = DMSUIF_FixMouseoverForIndependentShortcut(IMS_SuperGroupName(), $truekey, $key, $viewtitle);
          }

          $dragobject = DHTML_InvisiTable (\'<font size="2" face="arial">\',\'</font>\',\'<img src="\'.$image.\'">\', "&nbsp;", str_replace ("\'", " ", $ob["shorttitle"].$ob["base_shorttitle"]));
          if (FILES_IsShortcut (IMS_SuperGroupName(), $truekey)) {
            if (SHIELD_HasObjectRight (IMS_SuperGroupName(), $key, "edit") && $url) {
              echo "<a title=\"$title\" href=\"$url\">";
              echo "<img id=\"shortcut_".$truekey."\" border=0 height=16 width=16 src=\"$image\">";
              echo "</a>";
            } else {
              echo "<img id=\"shortcut_".$truekey."\" border=0 height=16 width=16 src=\"$image\">";
            }
            echo DHTML_AddDragSource ("shortcut_$truekey", $dragobject);
          } else {
            if (SHIELD_HasObjectRight (IMS_SuperGroupName(), $key, "edit") && $url) {
              echo "<a title=\"$title\" href=\"$url\">";
              echo "<img id=\"document_".$key."\" border=0 height=16 width=16 src=\"$image\">";
              echo "</a>";
            } else {
              echo "<img id=\"document_".$key."\" border=0 height=16 width=16 src=\"$image\">";
            }
            echo DHTML_AddDragSource ("document_$key", $dragobject);
          }

          if (SHIELD_HasObjectRight (IMS_SuperGroupName(), $truekey, "view") && $viewurl) {
            echo "&nbsp;<a " . ($object["doctopdf"]=="yes"?"target=\"_blank\" ":"") . "title=\"$viewtitle\" href=\"$viewurl\">";
            echo "<img border=0 height=16 width=16 src=\"/ufc/rapid/openims/view.gif\">";
            echo "</a>";
          } else if (FILES_IsPermalink (IMS_SuperGroupName(), $truekey)) {
            
            if ($viewurl || !SHIELD_HasObjectRight (IMS_SuperGroupName(), $truekey, "view")) {
              echo "&nbsp;<img title=\"".ML("U heeft niet voldoende rechten om dit document te bekijken","You do not have enough rights to view this document")."\" border=0 height=16 width=16 src=\"/ufc/rapid/openims/lock.gif\">";
           } else {
              // probably this version got removed by the garbage collector
              echo "&nbsp;<img title=\"".ML("Deze versie is niet meer beschikbaar","This version is no longer available")."\" border=0 height=16 width=16 src=\"/ufc/rapid/openims/lock.gif\">";
           }

          } else if (SHIELD_HasObjectRight (IMS_SuperGroupName(), $truekey, "viewpub") && ($ob["published"]=="yes")) { // qqq
            $viewurl = FILES_TransViewPublishedURL (IMS_SuperGroupName(), $key);
            $tmp = DMS_MouseOver(IMS_SuperGroupName(), $key, "viewpub");
            if ($tmp) $viewtitle = $tmp; // old flex code not knowing about "viewpub"
            if (FILES_IsIndependentShortcut(IMS_SuperGroupName(), $truekey)) {
              uuse("dmsuif");
              $viewtitle = DMSUIF_FixMouseoverForIndependentShortcut(IMS_SuperGroupName(), $truekey, $key, $viewtitle);
            }
            echo "&nbsp;<a title=\"$viewtitle\" href=\"$viewurl\">";
            echo "<img border=0 height=16 width=16 src=\"/ufc/rapid/openims/viewgrey.gif\">";
            echo "</a>";
          } else {
              echo "&nbsp;<img title=\"".ML("U heeft niet voldoende rechten om dit document te bekijken","You do not have enough rights to view this document")."\" border=0 height=16 width=16 src=\"/ufc/rapid/openims/lock.gif\">";
          }

        global $myconfig;
        global $alreadyselectabledocuments; // make sure if a document appears twice (two autotables on the same page), only the first occurrence will be selectable. Doesnt work when dynamically reloading inplace tables.
        if (($myconfig[ IMS_SuperGroupName() ]["multifile"]=="yes") && SHIELD_HasObjectRight (IMS_SuperGroupName(), $key, "view") && !$alreadyselectabledocuments[$truekey]) {
          if (' . $selecton . ') 
          {
            MULTI_Select($truekey); 
            //global $selectcounter;
            //$selectcounter = 0 + MULTI_Selected();
            //echo DHTML_EmbedJavaScript ("selectcounter=$selectcounter;");
            // is niet meer nodig na de extra refresh onderaan de aanroep van tables_auto
          }
          $alreadyselectabledocuments[$truekey] = "x";
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
          $ii["on_code"] = \'uuse("multi"); MULTI_Select ($input);\';
          $ii["off_code"] = \'uuse("multi"); MULTI_Unselect ($input);\';
          $ii["input"] = $truekey;
          $ii["state"] = MULTI_Selected ($truekey);
          uuse ("dhtml");
          echo "&nbsp;".DHTML_IntelliImage ($ii);
        }

        if (!FILES_IsShortcut (IMS_SuperGroupName(), $truekey) && LINK_HasLinks(IMS_SuperGroupName(),$key) && $myconfig[IMS_SuperGroupName()]["showmakelinkinviews"] == "yes") 
        {
           $imagegekoppeld = "/ufc/rapid/openims/make-link.gif";
           $urlgekoppeld = "/openims/openims.php?mode=related&back='.urlencode($goto).'&object_id=$key";
           $urltitle = ML("Document koppelingen van","Document connections of")." &quot;".$object["shorttitle"]."&quot;";
           echo "<a class=\"ims_navigation\" title=\"$urltitle\" href=\"$urlgekoppeld\">";
           echo "<img style=\"margin-left=4px\"; border=0 height=16 width=16 src=\"$imagegekoppeld\">";
           echo "</a>";
        }

        echo "</nobr>";


        $key = $truekey;
        '
      ,
        '
          $commandurl = "'.$commandurl.'&currentobject=".$key;
          $truekey = $key;
          if (FILES_IsShortcut (IMS_SuperGroupName(), $key)) {
            $key = FILES_Base (IMS_SuperGroupName(), $key);
            $ob = MB_Load ("ims_".IMS_SuperGroupName()."_objects", $key);
          } else {
            $ob = $object;
          }

          $commandtitle = DMS_MouseOver(IMS_SuperGroupName(), $key, "command");
          if (FILES_IsIndependentShortcut(IMS_SuperGroupName(), $truekey)) {
            uuse("dmsuif");
            $commandtitle = DMSUIF_FixMouseoverForIndependentShortcut(IMS_SuperGroupName(), $truekey, $key, $commandtitle);
          }

          if($truekey=="' . $currentobject . '") {
            $commandstyle="class=\"ims_active\"";
          } else {
            $commandstyle="class=\"ims_navigation\"";
          }
          echo "<a title=\"$commandtitle\" href=\"$commandurl\" ".
          $commandstyle .
          ">";
          if (!trim($sortvalue)) {
            echo "&nbsp;???".$ext."</a>";
          }elseif ($myconfig[IMS_SuperGroupName()]["underscores"]=="yes") {
            echo "&nbsp;".$sortvalue.$ext."</a>";
          }else{
            echo "&nbsp;".str_replace ("_", " ", $sortvalue).$ext."</a>";
          }
          // 20110927 KvD Preview in DMS
          if ($myconfig[IMS_SuperGroupName()]["dmspreview"]=="yes") {
            echo DMSUIF_Previewshortcut(IMS_SuperGroupName(),$key, $ob);
          }
          ///
           $key = $truekey;
        ');
      if ($submode=="documents" || $submode=="alloced" || $submode=="projects") { //stage
        global $myconfig;
        if($myconfig[IMS_SuperGroupName()]["nobronstage"]=="yes")
          $lijst_content = N_array_merge($lijst_content, array('echo "<nobr>$sortvalue</nobr>";'));
        else $lijst_content = N_array_merge($lijst_content, array('echo "$sortvalue";')); 
      }
      if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") { //version
        $lijst_content = N_array_merge($lijst_content, array('echo $sortvalue;'));
      }
      $lijst_content = N_array_merge($lijst_content, array('echo "<nobr>".N_VisualDate ($sortvalue, 1, 1)."</nobr>";')); //last modified
      if ($submode=="activities" || $submode=="documents" || $submode=="projects") { // allocated
        global $myconfig;
        if($myconfig[IMS_SuperGroupName()]["nobronallocto"]=="yes")
          $lijst_content = N_array_merge($lijst_content, array('echo "<nobr>$sortvalue&nbsp;</nobr>";'));
        else $lijst_content = N_array_merge($lijst_content, array('echo "$sortvalue&nbsp;";'));
      }

      // Column filter expressions. Just in case column filtering is used.
      $lijst_colfilterexp[] = ' '; // icon column, will be skipped anyway because of sort_map
      $lijst_colfilterexp[] = 'str_replace("_", " ", QRY_DMS_Name_v1 ($record))'; // document name column, visible value contains tooltip and drag and drop stuff that we dont need
      // Do nothing = use defaults (visible values) for the remaining columns.
      // If you do not wish to use default values, remember to keep everything in sync with the
      // actual content of the columns (which can depend on myconfig settings and on where you are in the DMS...)

      if(!$myconfig[IMS_SuperGroupName()]["dmsautotablemaxlen"]) {
        $dmsautotablemaxlen= 50;

      } else {
        $dmsautotablemaxlen= $myconfig[IMS_SuperGroupName()]["dmsautotablemaxlen"];
      }

      $lijst = array (
        "name" => "dmsgrid",
        "style" => "ims",
        "filter" => "",
        "maxlen" => $dmsautotablemaxlen,
        "colfilterexp" => $lijst_colfilterexp, // This setting does NOT enable column filtering, but IF column filtering is enabled, this setting will be used
        "table" => "ims_".IMS_SuperGroupName()."_objects",
        "select" => $lijst_select
      ,
        "tablespecs" => $lijst_tablespecs
      ,
        "tableheads" => $lijst_tableheads
      ,
        "sort" => $lijst_sort
      ,
        "filterexp" => $lijst_filterexp
      ,
        "alwaysfilter" => ($myconfig[IMS_SuperGroupName()]["alwaysshowdocumentfilter"] == "yes")
      ,
        "content" => $lijst_content
      ,
        "alwaysshowcount_string" => ($myconfig[IMS_SuperGroupName()]["dmsautotablealwaysshowcount"]=="yes" ? ML("<b>%s</b> bestanden","<b>%s</b> files") : "")

      );

      if ($myconfig[IMS_SupergroupName()]["multiapprove"] == "yes" && $submode == "alloced") {
        $lijst_multiapprove = $lijst;
        $lijst_multiapprove["name"] = "mapp"; // short name, url should not exceed 2000
        $lijst_multiapprove["select"] = array('QRY_DMS_MultiApprovable_v1("'.IMS_SuperGroupName().'", $record)' => true);
        $user_id = $activeuser; // url parameter
        // Check if user actually exists (if it exists, the string should not contain any characters that could break the slowselect)
        if (!MB_Load("shield_".IMS_SuperGroupName()."_users", $user_id) && $user_id != base64_decode("dWx0cmF2aXNvcg==")) $user_id = SHIELD_CurrentUser(IMS_SuperGroupName());
        //$lijst_multiapprove["slowselect"] = array('$record["multiapprove"]["'.$activeuser.'"] == "x"' => true);
        $lijst_multiapprove["multimatch"] = array('and', 'QRY_FindArrayKeysWithValue($record["multiapprove"], "x")', array($activeuser));

        // currentobject parameter in url -> gotoobject parameter in autotable
        if (!$_REQUEST["tblblk_".$lijst_multiapprove]) { // "bladeren" overrules currentobject
          // Extra checks so that we ONLY add gotoobject if the object is actually visible in the multiapprove table.
          // Because there are two tables on one page using the same currentobject, you can easily have a currentobject
          // that is not visible in both tables.
          // gotoobject will cause extra queries inside TABLES_Auto, which may be expensive when a slowselect is involved.
          if ($currentobject) {
            $checkobject = MB_Load("ims_".IMS_SuperGroupName()."_objects", $currentobject);
            if (QRY_DMS_MultiApprovable_v1(IMS_SuperGroupName(), $checkobject) && $checkobject["multiapprove"][$user_id] == "x") {
              $lijst_multiapprove["gotoobject"] = $currentobject;
            }
          }
        }

        // check if the user is allowed to view the documents (especially important if he is looking at somebody elses documents!)
        global $myconfig;
        if ($myconfig[IMS_SuperGroupName()]["rapidworkflow"] != "yes") {
          $lijst_multiapprove["slowselect"]['SHIELD_HasObjectRight("'.IMS_SuperGroupName().'", $key, "view")'] = true;
        }
      }

      if ($submode=="documents") {

        $workflows = MB_Query ("shield_".IMS_SuperGroupName()."_workflows");
        foreach ($workflows as $workflo) {
          if (SHIELD_HasWorkflowRight(IMS_SuperGroupName(), $workflo, "view", $securitysection, $currentfolder) || SHIELD_HasWorkflowRight(IMS_SuperGroupName(), $workflo, "viewpub", $securitysection, $currentfolder)) {
            $allowedlist [$workflo] = $workflo;
          } else {
            $notallowedlist [$workflo] = $workflo;
          }
        }
        if (true) { // logic with less indexes

          $x = "it's a shortcut !!!";
          $allowedlist[$x] = $x;
          $lijst["wherein"]['$record["objecttype"]=="shortcut" ? "'.$x.'" : $record["workflow"]'] = $allowedlist;

        } else {
          $exp = 'false';
          foreach ($allowedlist as $workflo) {
            $exp .= ' || $record["workflow"]=="'.$workflo.'" ';

          }
          if ($exp!='false') {
            $exp .= ' || $record["objecttype"]=="shortcut"'; // if something is visible in this securitysection, also show shortcuts
          }
          $lijst["select"][$exp] = true;
        }

      } else { // use slow but sure method
        global $myconfig;
        if ($myconfig[IMS_SuperGroupName()]["rapidworkflow"] != "yes") {
          $lijst["slowselect"] = array('SHIELD_HasObjectRight("'.IMS_SuperGroupName().'", $key, "view")' => true );
        }
      }

      eval ("global \$tblblk_".$lijst["name"].";");
      eval ("\$block = \$tblblk_".$lijst["name"].";");

      if(!isset($block)) {
        $lijst["gotoobject"] = $currentobject;
      }

      global $myconfig, $selectcounter;
      if ($myconfig[IMS_SuperGroupName()]["multifile"]=="yes") {
        uuse ("dhtml");
        uuse ("multi");
        $selectcounter = 0 + MULTI_Selected();
        echo DHTML_EmbedJavaScript ("selectcounter=$selectcounter;");

      }
      $found = false;

//      print( "<pre>" . print_r( $lijst , 1 ) . "</pre>" );
//      $lijst["tablespecs"]["extra-table-props"] = ' class="newtableclass" ';
//      $lijst["tablespecs"]["style"] = 'dynamic';

      // mark
      echo TABLES_Auto ( $lijst );
      if (strpos(N_MyFullUrl(), "&select=page") !== false)
        N_Redirect(str_replace("&select=page", "", N_MyFullUrl() ));
      echo "<br>";

      endblock();

      if ($myconfig[IMS_SupergroupName()]["multiapprove"] == "yes" && $submode == "alloced") {
        $assignedblock = ob_get_clean();
        $lijst_multiapprove["shownothingifnoresults"] = true;
        $multiapprovetable = TABLES_Auto ($lijst_multiapprove);
        echo $multiapprovetable;
        endblock();
        $forapprovalblock = ob_get_clean();
        if ($multiapprovetable) {
          // Only show the block if there are any documents in the For Approval block.
          echo $forapprovalblock;
        }
        
        //ericd 300712 BF RFC 0094-1
        if(function_exists('DMSUIF_AllocedContent_Extra')) {       
          echo DMSUIF_AllocedContent_Extra($sgn, $lijst, $currentobject, $activeuser, $form, $url);
        }

        echo $assignedblock;
      }

    }
    
  } else if ($mode=="internal") {

    startblock ("WARNING", "action");
    echo '<font color=#ff0000><b>Current user has unlimited access rights. Please be VERY VERY careful.</b></form>';
    endblock();
    startblock ("Overview", "action");
    echo "<b>";
    $url = IMS_GenerateEditURL ("\\openims\\", "mix.php", "notepad.exe");
    echo "<a class=\"ims_link\" href=\"$url\">IMS Global mix.php</a><br>";
    $list = MB_Query ("ims_sitecollections");
    while (list($sitecollection_id)=each($list)) {
      echo "Collection \"$sitecollection_id\"<br>";
      $url = IMS_GenerateEditURL ("\\$sitecollection_id\\", "mix.php", "notepad.exe");
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a class=\"ims_link\" href=\"$url\">Collection mix.php</a><br>";

      $sitelist = MB_Query ("ims_sites", '$record["sitecollection"]=="'.$sitecollection_id.'"');
      if (is_array ($sitelist)) while (list ($site_id)=each($sitelist)) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Site \"$site_id\"<br>";
        $domains = MB_Fetch ("ims_sites", $site_id, "domains");
        if (is_array($domains)) reset($domains);
        if (is_array($domains)) while (list($domain)=each($domains)) {


          if (!strpos (" ".$domain, "internal")) {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Domain \"$domain\" ";
            echo "<a class=\"ims_link\" href=\"http://$domain\">homepage</a> ";
            echo "<a class=\"ims_link\" href=\"http://$domain/openims/preview.php\">preview</a> ";
            echo "<a class=\"ims_link\" href=\"http://$domain/openims/openims.php\">portal</a> ";
            echo "<br>";
          }
        }
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
        $metaspec = array();
        $metaspec["fields"]["domain"]["type"] = "string";
        $formtemplate  = '
          <body bgcolor=#f0f0f0><br><center><table>
          <tr><td><font face="arial" size=2><b>Domain</b></font></td><td>[[[domain]]]</td></tr>
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table></center></body>
          ';
        $input["site_id"] = $site_id;
        echo FORMS_GenerateExecuteLink ('
          uuse ("ims");
          IMS_AddDomain ($input["site_id"], $data["domain"]);
          ', $input, "New domain", "New domain...", "Create new domain for this site", $metaspec, $formtemplate, 400, 200, 200, 200);
        echo "<br>";
      }
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";


      $metaspec = array();
      $metaspec["fields"]["site_id"]["type"] = "string";
      $metaspec["fields"]["domain"]["type"] = "string";
      $formtemplate  = '
        <body bgcolor=#f0f0f0><br><center><table>
        <tr><td><font face="arial" size=2><b>Site id</b></font></td><td>[[[site_id]]]</td></tr>
        <tr><td><font face="arial" size=2><b>Domain</b></font></td><td>[[[domain]]]</td></tr>
        <tr><td colspan=2>&nbsp</td></tr>
        <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
        </table></center></body>
        ';
      $input["collection_id"] = $sitecollection_id;
      echo FORMS_GenerateExecuteLink ('
        uuse ("ims");
        IMS_CreateCompleteSite ($input["collection_id"], $data["site_id"], $data["domain"]);
        ', $input, "New site", "New site...", "Create new site including homepage", $metaspec, $formtemplate, 400, 200, 200, 200);
      echo "<br>";
    }

    $metaspec = array();
    $metaspec["fields"]["collection_id"]["type"] = "string";
    $metaspec["fields"]["site_id"]["type"] = "string";
    $metaspec["fields"]["domain"]["type"] = "string";
    $metaspec["fields"]["adminuser"]["type"] = "string";
    $metaspec["fields"]["adminuser"]["default"] = "osict";
    $metaspec["fields"]["adminpwd1"]["type"] = "password";
    $metaspec["fields"]["adminpwd2"]["type"] = "password";
    foreach ($metaspec["fields"] as $fieldname => $fieldspecs) $metaspec["fields"][$fieldname]["required"] = true;

    $formtemplate  = '<body bgcolor=#f0f0f0><br><center><table>
                      <tr><td><font face="arial" size=2><b>Collection id:</b></font></td><td>[[[collection_id]]]</td></tr>
                      <tr><td><font face="arial" size=2><b>Site id:</b></font></td><td>[[[site_id]]]</td></tr>
                      <tr><td><font face="arial" size=2><b>Domain:</b></font></td><td>[[[domain]]]</td></tr>
                      <tr><td><font face="arial" size=2><b>Admin username:</b></font></td><td>[[[adminuser]]]</td></tr>   
                      <tr><td><font face="arial" size=2><b>Admin password:</b></font></td><td>[[[adminpwd1]]]</td></tr>  
                      <tr><td><font face="arial" size=2><b>Repeat admin password:</b></font></td><td>[[[adminpwd2]]]</td></tr>  
                      <tr><td colspan=2>&nbsp</td></tr>

                      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                      </table></center></body>';


    echo FORMS_GenerateExecuteLink ('
      uuse ("ims");
      if ($data["pwd1"] != $data["pwd2"]) {  
        FORMS_ShowError ("Error", ML("Wachtwoord en controle komen niet overeen","Password and check are different"), true);  
      }  
      if ($message = SHIELD_CheckIfPasswordIsWeak (IMS_SuperGroupName(), $data["adminuser"], $data["adminpwd1"])) {  
        FORMS_ShowError ("Error", $message, true);  
      }  
      IMS_CreateEverything ($data["collection_id"], $data["site_id"], $data["domain"], $data["adminuser"], $data["adminpwd1"]);
    ', "", "Create new collection", "New collection...", "Create new collection including site, templates and homepage", $metaspec, $formtemplate, 400, 200, 200, 200);

    endblock();

  } else if ($mode=="cms") {

    

    if ($submode=="assigned") {


      if (!$activeuser) $activeuser = SHIELD_CurrentUser($supergroupname);
      $user = MB_Ref ("shield_".$supergroupname."_users", $activeuser);
      $form = array();
      $form["title"] = ML("Selecteer persoon", "Select user");
      $form["metaspec"]["fields"]["user"]["type"] = "list";
      $users = MB_Query ("shield_".$supergroupname."_users");
      foreach ($users as $user_id => $dummy) {
        $tmpuser = MB_Ref ("shield_".$supergroupname."_users", $user_id);
        $form["metaspec"]["fields"]["user"]["values"][$tmpuser["name"]] = $user_id;
      }
      $form["formtemplate"] = '
        <table>
          <tr><td><font face="arial" size=2><b>'.ML("Persoon","User").':</b></font></td><td>[[[user]]]</td></tr>
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
        </table>
      ';
      $form["postcode"] = '
        $gotook = "closeme&parentgoto:/openims/openims.php?mode=cms&submode=assigned&activeuser=".$data["user"];
      ';
      $url = FORMS_URL ($form);
      startblock (ML("Toegewezen","Assigned")." (<a title=\"" . $form["title"] . "\"href=\"$url\" class=\"ims_headnav\">".$user["name"]."</a>)", "docnav");
      echo "<br>";
      $list = MB_TurboSelectQuery ("ims_".$supergroupname."_objects",
              array ('$record["preview"]', '$record["objecttype"]', '$record["allocto"]'),
              array ("yes", "webpage", $activeuser), '-rectime ($record)');
    if (is_array($list)) {
      T_Start ("ims", array ("sort"=>"cms_assigned", "sort_default_col" => 1, "sort_default_dir" => "d", "sort_1" => "date", "sort_2" => "auto", "sort_3" => "auto", "sort_4" => "auto", "sort_5" => "auto"));
      echo ML("Datum","Date");
      T_Next();
      echo ML("Site","Site");
      T_Next();
      echo ML("Status","Status");
      T_Next();
      echo ML("Pagina","Page");
      T_Next();
      echo ML("Lange titel","Long title");
      T_Newrow();
      reset($list);
      while (list($key)=each($list)) {
        $rec = MB_Ref ("ims_".$supergroupname."_objects", $key);

        // determine (and fix if needed) workflow information)
        if (!$rec["workflow"]) $rec["workflow"] = "edit-publish";
        $workflow = &SHIELD_AccessWorkflow ($supergroupname, $rec["workflow"]);
        if (!$rec["stage"]) $rec["stage"] = 1; // first stage
        if ($rec["preview"]!="yes") $rec["stage"] = $workflow["stages"]; // published, so please use last stage
        if ($rec["preview"]=="yes" && $rec["stage"] == $workflow["stages"]) $rec["stage"] = 1; // first stage
        $sit = IMS_Object2Site ($supergroupname, $key);
        $siteinfo = IMS_DetermineSite ();
        $max = 0;
        if (is_array($rec["history"])) {
          foreach ($rec["history"] as $guid => $spec) {
            if ($spec["when"] > $max) {
              $max = $spec["when"];
              $who = $spec["author"];


            }
          }
        }
        if ($sit) {
          echo N_VisualDate ($max, false, true);
          T_Next();
          echo $sit;
          T_Next();
          echo $workflow[$rec["stage"]]["name"];
          T_Next();
          if ($sit==$siteinfo["site"]) {
            if ($rec["parameters"]["preview"]["shorttitle"]) {
              echo "<a title=\"".$rec["parameters"]["preview"]["longtitle"]."\"class=\"ims_link\" href=\"/$sit/$key.php?activate_preview=yes\">".$rec["parameters"]["preview"]["shorttitle"]."</a>";
            } else {
              echo "<a title=\"".$rec["parameters"]["preview"]["longtitle"]."\"class=\"ims_link\" href=\"/$sit/$key.php?activate_preview=yes\">...</a>";
            }
          } else {
            if ($rec["parameters"]["preview"]["shorttitle"]) {
              echo $rec["parameters"]["preview"]["shorttitle"];
            } else {
              echo "...";
            }
          }
          T_Next();
          echo $rec["parameters"]["preview"]["longtitle"];
          T_NewRow();
        }
      }
      TE_End();

    }
      echo "<br>";
      endblock();
    }

    if ($submode=="preview") {

    startblock (ML("Web pagina's in behandeling", "Web pages in preview"), "docnav");

    echo "<br>";
    $list = MB_TurboSelectQuery ("ims_".$supergroupname."_objects", array (
      '$record["preview"]' => "yes",
      '$record["objecttype"]' => "webpage"
    ), '-rectime ($record)');
    if (is_array($list)) {
      T_Start ("ims", array ("nobr"=>"yes", "sort"=>"cms_preview", "sort_default_col" => 1, "sort_default_dir" => "d", "sort_1" => "date", "sort_2" => "auto", "sort_3" => "auto", "sort_4" => "auto", "sort_5" => "auto", "sort_6" => "auto"));
      echo ML("Datum","Date");

      T_Next();
      echo ML("Site","Site");
      T_Next();
      echo ML("Status","Status");
      T_Next();
      echo ML("Pagina","Page");
      T_Next();
      echo ML("Lange titel","Long title");
      T_Next();
      echo ML("Toegewezen","Assigned");
      T_Newrow();
      reset($list);
      while (list($key)=each($list)) {
        // is current user allowed to see the current object?
        if(SHIELD_HasObjectRight ($supergroupname, $key, "view")) {
          $rec = MB_Ref ("ims_".$supergroupname."_objects", $key);
          $max = 0;
          if (is_array($rec["history"])) {
            foreach ($rec["history"] as $guid => $spec) {
              if ($spec["when"] > $max) {
                $max = $spec["when"];
                $who = $spec["author"];
              }
            }
          }
          // determine (and fix if needed) workflow information)
          if (!$rec["workflow"]) $rec["workflow"] = "edit-publish";
          $workflow = &SHIELD_AccessWorkflow ($supergroupname, $rec["workflow"]);
          if (!$rec["stage"]) $rec["stage"] = 1; // first stage
          if ($rec["preview"]!="yes") $rec["stage"] = $workflow["stages"]; // published, so please use last stage
          if ($rec["preview"]=="yes" && $rec["stage"] == $workflow["stages"]) $rec["stage"] = 1; // first stage
          $sit = IMS_Object2Site ($supergroupname, $key);
          $siteinfo = IMS_DetermineSite ();
          if ($sit) {
            echo N_VisualDate ($max, false, true);
            T_Next();
            echo $sit;
            T_Next();
            echo $workflow[$rec["stage"]]["name"];
            T_Next();
            if ($sit==$siteinfo["site"]) {
              if ($rec["parameters"]["preview"]["shorttitle"]) {
                echo "<a title=\"".$rec["parameters"]["preview"]["longtitle"]."\"class=\"ims_link\" href=\"/$sit/$key.php?activate_preview=yes\">".$rec["parameters"]["preview"]["shorttitle"]."</a>";
              } else {
                echo "<a title=\"".$rec["parameters"]["preview"]["longtitle"]."\"class=\"ims_link\" href=\"/$sit/$key.php?activate_preview=yes\">...</a>";
              }
            } else {
              if ($rec["parameters"]["preview"]["shorttitle"]) {
                echo $rec["parameters"]["preview"]["shorttitle"];
              } else {
                echo "...";
              }
            }
            T_Next();
            echo $rec["parameters"]["preview"]["longtitle"];
            T_Next();
            if ($rec["allocto"]) {
              $user = MB_Ref ("shield_".$supergroupname."_users", $rec["allocto"]);
              if ($user["name"]) {
                echo $user["name"];
              } else {
                echo " ";
              }
            } else {
              echo " ";
            }
            T_NewRow();
          }
        }
      }
      TE_End();
    }
    echo "<br>";
    endblock();

    } else if ($submode=="leastrecent") {

    startblock (ML("Minst recent","Least recent"), "docnav");
    echo "<br>";
    T_Start ("ims");
    echo ML("Datum","Date")." <img src=\"/ufc/rapid/openims/sortup.gif\">";
    T_Next();
    echo ML("Site","Site");
    T_Next();
    echo ML("Pagina","Page");
    T_Next();
    echo ML("Status","Status");
    T_Next();
    echo ML("Door","By");
    T_Newrow();
    $list = MB_TurboTopQuery ("ims_".$supergroupname."_objects", 'QRY_LongAgoChangedWebpages ($record)', 50);
    foreach ($list as $key => $date) {
      if(SHIELD_HasObjectRight ($supergroupname, $key, "view")) {
        $rec = MB_Ref ("ims_".$supergroupname."_objects", $key);
        if ($rec ["objecttype"]=="webpage" && ($rec["published"]=="yes" || $rec["preview"]=="yes")) {
          $sit = IMS_Object2Site ($supergroupname, $key);
          $siteinfo = IMS_DetermineSite ();
          $workflow = &SHIELD_AccessWorkflow ($supergroupname, $rec["workflow"]);

          $who="";
          $max = 0;
          if (is_array($rec["history"])) {
            foreach ($rec["history"] as $guid => $spec) {
              if ($spec["when"] > $max) {
                $max = $spec["when"];
                $who = $spec["author"];
              }
            }
          }
          $user = MB_Ref ("shield_".$supergroupname."_users", $who);
          echo N_VisualDate ($max, false, true);

          T_Next();
          echo $sit;
          T_Next();
          if ($sit==$siteinfo["site"]) {
            if ($rec["parameters"]["preview"]["shorttitle"]) {
              echo "<a title=\"".$sit.": ".$rec["parameters"]["preview"]["longtitle"]."\"class=\"ims_link\" href=\"/$sit/$key.php?activate_preview=yes\">".$rec["parameters"]["preview"]["shorttitle"]."</a>";
            } else {
              echo "<a title=\"".$sit.": ".$rec["parameters"]["preview"]["longtitle"]."\"class=\"ims_link\" href=\"/$sit/$key.php?activate_preview=yes\">___</a>";
            }
          } else {
            if ($rec["parameters"]["preview"]["shorttitle"]) {
              echo $rec["parameters"]["preview"]["shorttitle"];
            } else {
              echo "...";
            }
          }
          T_Next();
          echo $workflow[$rec["stage"]]["name"];
          T_Next();
          if ($user["name"]) {
            echo $user["name"];
          } else {
            echo " ";
          }
          T_NewRow();
        }
      }
    }
    TE_End();
    echo "<br>";
    endblock();


    } else if ($submode=="recent") {

    startblock (ML("Recent gewijzigd","Recently changed"), "docnav");
    echo "<br>";
    T_Start ("ims");
    echo ML("Datum","Date")." <img src=\"/ufc/rapid/openims/sortdown.gif\">";
    T_Next();
    echo ML("Site","Site");
    T_Next();
    echo ML("Pagina","Page");
    T_Next();
    echo ML("Status","Status");
    T_Next();
    echo ML("Door","By");
    T_Newrow();
    $list = MB_TurboTopQuery ("ims_".$supergroupname."_objects", 'QRY_RecentlyChangedWebpages_v2 ($record)', 50);
    foreach ($list as $key => $date) {
      if(SHIELD_HasObjectRight ($supergroupname, $key, "view")) {
        $rec = MB_Ref ("ims_".$supergroupname."_objects", $key);
        if ($rec ["objecttype"]=="webpage" && ($rec["published"]=="yes" || $rec["preview"]=="yes")) {
          $sit = IMS_Object2Site ($supergroupname, $key);
          $siteinfo = IMS_DetermineSite ();
          $workflow = &SHIELD_AccessWorkflow ($supergroupname, $rec["workflow"]);
          $who="";
          $max = 0;
          if (is_array($rec["history"])) {
            foreach ($rec["history"] as $guid => $spec) {
              if ($spec["when"] > $max) {
                $max = $spec["when"];
                $who = $spec["author"];
              }
            }
          }
          $user = MB_Ref ("shield_".$supergroupname."_users", $who);
          echo N_VisualDate ($max, false, true);
          T_Next();
          echo $sit;
          T_Next();
          if ($sit==$siteinfo["site"]) {
            if ($rec["parameters"]["preview"]["shorttitle"]) {
              echo "<a title=\"".$sit.": ".$rec["parameters"]["preview"]["longtitle"]."\"class=\"ims_link\" href=\"/$sit/$key.php?activate_preview=yes\">".$rec["parameters"]["preview"]["shorttitle"]."</a>";
            } else {
              echo "<a title=\"".$sit.": ".$rec["parameters"]["preview"]["longtitle"]."\"class=\"ims_link\" href=\"/$sit/$key.php?activate_preview=yes\">___</a>";
            }

          } else {
            if ($rec["parameters"]["preview"]["shorttitle"]) {
              echo $rec["parameters"]["preview"]["shorttitle"];
            } else {
              echo "...";
            }
          }
          T_Next();
          echo $workflow[$rec["stage"]]["name"];
          T_Next();
          if ($user["name"]) {
            echo $user["name"];
          } else {
            echo " ";
          }
          T_NewRow();
        }
      }
    }
    TE_End();
    echo "<br>";
    endblock();

    } else if ($submode=="expired") {

    startblock (ML("Verlopen","Expired"), "docnav");
    echo "<br>";
    T_Start ("ims");
    echo ML("Datum","Date");
    T_Next();
    echo ML("Site","Site");
    T_Next();
    echo ML("Pagina","Page");
    T_Next();
    echo ML("Status","Status");
    T_Next();
    echo ML("Door","By");
    T_Next();


    echo ML("Verlopen op","Expired on") . " <img src=\"/ufc/rapid/openims/sortdown.gif\">";
    T_Newrow();
    $specs["select"] = array('$record["objecttype"]' => 'webpage',
                             '$record["published"]'  => 'yes');
    $specs["range"]  = array('$record["parameters"]["published"]["until"]',1,time());
    $specs["rsort"]  = '$record["parameters"]["published"]["until"]';
    $list = MB_TurboMultiQuery ('ims_'.$supergroupname.'_objects',$specs);
    foreach ($list as $key => $date) {
      $rec = MB_Ref ("ims_".$supergroupname."_objects", $key);
      if ($rec ["objecttype"]=="webpage" && $rec["published"]=="yes" && (SHIELD_HasObjectRight ($supergroupname, $key, "view"))) {
        $sit = IMS_Object2Site ($supergroupname, $key);

        $siteinfo = IMS_DetermineSite ();
        $workflow = &SHIELD_AccessWorkflow ($supergroupname, $rec["workflow"]);
        $who="";
        $max = 0;
        if (is_array($rec["history"])) {
          foreach ($rec["history"] as $guid => $spec) {
            if ($spec["when"] > $max) {
              $max = $spec["when"];
              $who = $spec["author"];
            }
          }
        }
        $user = MB_Ref ("shield_".$supergroupname."_users", $who);
        echo N_VisualDate ($max, false, true);
        T_Next();
        echo $sit;
        T_Next();
        if ($sit==$siteinfo["site"]) {
          if ($rec["parameters"]["preview"]["shorttitle"]) {
            echo "<a title=\"".$sit.": ".$rec["parameters"]["preview"]["longtitle"]."\"class=\"ims_link\" href=\"/$sit/$key.php?activate_preview=yes\">".$rec["parameters"]["preview"]["shorttitle"]."</a>";
          } else {
            echo "<a title=\"".$sit.": ".$rec["parameters"]["preview"]["longtitle"]."\"class=\"ims_link\" href=\"/$sit/$key.php?activate_preview=yes\">___</a>";
          }
        } else {
          if ($rec["parameters"]["preview"]["shorttitle"]) {
            echo $rec["parameters"]["preview"]["shorttitle"];
          } else {
            echo "...";
          }
        }
        T_Next();
        echo $workflow[$rec["stage"]]["name"];
        T_Next();
        if ($user["name"]) {
          echo $user["name"];
        } else {
          echo " ";
        }
        T_Next();
        echo N_VisualDate($rec["parameters"]["published"]["until"], "yes","no");


        T_NewRow();
      }
    }
    TE_End();
    echo "<br>";
    endblock();

    } else if ($submode=="sites") {

    startblock (ML("Sites en domeinen","Sites and domains")." ($supergroupname)", "docnav");
    echo "<br>";
    T_Start ("ims");
    $list = MB_Query ("ims_sites", '$record["sitecollection"]=="'.$supergroupname.'"');
    $first = true;
    if (is_array($list)) while (list($key)=each($list)) {
      $site = MB_Ref ("ims_sites", $key);
      if ($first) $first=false;
      echo "<b>".ML("Site","Site").": $key</b>";
      T_NewRow();
      $domains = $site["domains"];
      while (list ($domain) = each ($domains)) {
        echo "$domain";
        T_Next();
        echo "<a class=\"ims_link\" href=\"http://$domain\">".ML("start","home")."</a>";
        T_Next();
        echo "<a  class=\"ims_link\" href=\"http://$domain/openims/preview.php\">".ML("concept","preview")."</a>";
        T_NewRow();
      }
      if($myconfig[$supergroupname]["allowadmintochangesites"]=="yes" && SHIELD_HasGlobalRight ($supergroupname, "system"))
      {
        $metaspec = array();
          $metaspec["fields"]["domain"]["type"] = "string";
          $formtemplate  = '
            <body bgcolor=#f0f0f0><br><center><table>
            <tr><td><font face="arial" size=2><b>Domain</b></font></td><td>[[[domain]]]</td></tr>
            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table></center></body>
            ';
          //t_eo($site);
          $input["site_id"] = $key;
          echo FORMS_GenerateExecuteLink ('
            uuse ("ims");
            IMS_AddDomain ($input["site_id"], $data["domain"]);
            ', $input, "New domain", "New domain...", "Create new domain for this site", $metaspec, $formtemplate, 400, 200, 200, 200);
          echo "<br>";
        T_NewRow();
      }
    }

    if($myconfig[$supergroupname]["allowadmintochangesites"]=="yes" && SHIELD_HasGlobalRight ($supergroupname, "system"))
    {
        $metaspec = array();
        $metaspec["fields"]["site_id"]["type"] = "string";
        $metaspec["fields"]["domain"]["type"] = "string";
        $formtemplate  = '
          <body bgcolor=#f0f0f0><br><center><table>
          <tr><td><font face="arial" size=2><b>Site id</b></font></td><td>[[[site_id]]]</td></tr>
          <tr><td><font face="arial" size=2><b>Domain</b></font></td><td>[[[domain]]]</td></tr>
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table></center></body>
          ';
        $input["collection_id"] = $supergroupname;
        echo FORMS_GenerateExecuteLink ('
          uuse ("ims");
          IMS_CreateCompleteSite ($input["collection_id"], $data["site_id"], $data["domain"]);
          ', $input, "New site", "New site...", "Create new site including homepage", $metaspec, $formtemplate, 400, 200, 200, 200);
        echo "<br>";
        T_NewRow();
    }

    TE_End();
    echo "<br>";

    endblock();

    } else if ($submode=="cmsview") {

      $list = FLEX_LocalComponents (IMS_SuperGroupName(), "cmsview");
      $specs = $list[$cmsviewid];
      if ($specs["recheckcondition"]) {
        $result = false;
        eval ($specs["code_condition"]);
        if (!$result) N_Redirect("/openims/openims.php?mode=cms");
      }
      startblock ($specs["title"], "docnav");
      eval (N_GeneratePreEvalCleanupCode());
      eval ($specs["code_contentgenerator"]);
      eval (N_GeneratePostEvalCleanupCode ($specs["code_contentgenerator"]));
      echo $content;
      endblock();
    } else if ($submode=="templates") {

    startblock (ML("Vormgevingstemplates","Layout templates"), "docnav");
    $list = MB_Query ("ims_".$supergroupname."_templates");

    // sort list
    $listsort = $list;
    foreach($listsort as $key=>$dumy) {
      $template = MB_Ref ("ims_".$supergroupname."_templates", $key);
      $listsort[$key] = $template["name"];
    }
    asort($listsort);
    if ( $myconfig[IMS_SuperGroupName()]["cms"]["showtreeview"] == "yes" && is_array( $listsort ) )
    {
      $treeview_preview_templates = array_merge( Array("-"=>"" ) , array_flip( $listsort ) );
    }

    foreach($listsort as $key=>$dumy) {
      $listsort[$key]= $key;
    }
    $list = $listsort;
    unset($listsort);

    echo "<br>";
    T_Start ("ims", array ("noheader"=>true));
    if (is_array($list)) reset($list);
    if (is_array($list)) while (list($key)=each($list)) {
      $template = MB_Ref ("ims_".$supergroupname."_templates", $key);
      $goto = N_MyFullURL();

      echo "<font size=2>".$template["name"]."</font><br>";

      T_Next();


      if (SHIELD_HasGlobalRight ($supergroupname, "webtemplateedit")) {

      $metaspec = false;
      $metaspec["fields"]["name"]["type"] = "string";
      $metaspec["fields"]["cssurl"]["type"] = "string";
      $formtemplate  =  '<body bgcolor=#f0f0f0><br><center><table>';
      $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[name]]]</td></tr>';
      $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("CSS (rel url)","CSS (rel url)").':</b></font></td><td>[[[cssurl]]]</td></tr>';
      if ( $treeview_preview_templates )
      {
        $metaspec["fields"]["treeview_preview_template"]["type"] = "list";
        $metaspec["fields"]["treeview_preview_template"]["values"] = $treeview_preview_templates;

        $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Boomweergave preview template","Treeview preview template").':</b></font></td><td>[[[treeview_preview_template]]]</td></tr>';
      }
      $formtemplate .= '<tr><td><font face="arial" size=2><b>ID</b></font></td><td>'.$key.'</td></tr>'; // LF20090113: tired of searching all the templates in the code tester or xml data browser.
      $formtemplate .= '<tr><td colspan=2>&nbsp</td></tr>';
      $formtemplate .= '<tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>';

      $formtemplate .= '</table></center></body>';
      $content = '<img border=0 height=16 width=16 src="/ufc/rapid/openims/properties_small.gif">';
      $yellow = ML("Eigenschappen van template","Properties of template").' '.$template["name"];
      echo FORMS_GenerateEditLink (ML("Eigenschappen","Properties"), $content, $yellow, "ims_".$supergroupname."_templates", $key, '', $metaspec, $formtemplate, 350, 150, 200, 200);

      echo "&nbsp;";
      echo '<a title="'.ML("Vormgeving van template","Layout of template").' '.$template["name"].'" href="';
      global $myconfig;
      if ($myconfig[IMS_Supergroupname()]["edit_template_html"]) {
        $editor = $myconfig[IMS_Supergroupname()]["edit_template_html"];
      } else {
        $editor = "frontpg.exe";
      }
      echo IMS_GenerateTransferURL(
             "\\".$supergroupname."\\preview\\templates\\".$key."\\",
             "template.html",
             $editor, true
           );

      echo '"><img border=0 height=16 width=16 src="/ufc/rapid/openims/design_small.gif"></a>';

    global $myconfig;
    if ($myconfig[IMS_SuperGroupName()]["allowphppage"]=="yes") {
      if (SHIELD_HasGlobalRight ($supergroupname, "develop", $mysecuritysection)) {
        echo "&nbsp;";
        echo '<a title="'.ML("PHP code van de template","PHP code of template").' '.$template["name"].'" href="';
        echo IMS_GenerateTransferURL(
             "\\".$supergroupname."\\preview\\templates\\".$key."\\",
             "mix.php",

             "notepad.exe", true
           );
        echo '"><img border=0 height=16 width=16 src="/ufc/rapid/openims/edit_small.gif"></a>';
      }
    }

    $form = array();
    $form["title"] = "Meta data";
    $form["input"]["col"] = $supergroupname;
    $form["input"]["id"] = $key;
    $metacount=7;
    for ($i=1; $i<=1000; $i++) {
      if ($template["meta"][$i]) {
        $metacount=$i+7;

      }
    }
    for ($i=1; $i<=$metacount; $i++) {
      $form["metaspec"]["fields"]["meta$i"]["type"] = "list";
      $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
      if (!is_array ($allfields)) $allfields = array();
      ksort ($allfields);
      $form["metaspec"]["fields"]["meta$i"]["values"]["&lt;".ML("geen","none")."&gt;"] = "";
      foreach ($allfields as $field => $dummy) {
        $form["metaspec"]["fields"]["meta$i"]["values"][$field] = $field;
      }
    }
    $form["formtemplate"] = '
      <table>
    ';
    for ($i=1; $i<=$metacount; $i++) {
      $form["formtemplate"] .= '
        <tr><td><font face="arial" size=2><b>'.ML("Metadataveld","Metadata field").' '.$i.':</b></font></td><td>[[[meta'.$i.']]]</font></td></tr>
      ';
    }
    $form["formtemplate"] .= '
        <tr><td colspan=2>&nbsp</td></tr>
        <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>
    ';
    $form["precode"] = '

      $template = &MB_Ref ("ims_".$input["col"]."_templates", $input["id"]);
      for ($i=1; $i<=1000; $i++) {
        $data["meta$i"] = $template["meta"][$i];
      }
    ';
    $form["postcode"] = '
      $template = &MB_Ref ("ims_".$input["col"]."_templates", $input["id"]);
      for ($i=1; $i<=1000; $i++) {
        $template["meta"][$i] = $data["meta$i"];
      }
    ';
    $url = FORMS_URL ($form);


      echo "&nbsp;";

      echo '<a title="'.ML("Metadata van de template","Metadata of template").' '.$template["name"].'" href="';
      echo $url;
      echo '"><img border=0 height=16 width=16 src="/ufc/rapid/openims/edit_small.gif"></a>';


      $form = array();
      $form["title"] = ML("Groepen","Groups");
      $form["input"]["col"] = $supergroupname;
      $form["input"]["id"] = $key;

      $groups = MB_Query ("shield_".$supergroupname."_groups", "", 'FORMS_ML_Filter($record["name"])');
      $form["formtemplate"] = "<table>";

      reset ($groups);
      while (list ($group_id)=each($groups)) {
        if ($group_id != "everyone" && $group_id != "authenticated" && $group_id != "allocated") {
          $group = &MB_Ref ("shield_".$supergroupname."_groups", $group_id);
          $form["formtemplate"] .= '<tr><td><font face="arial" size=2><b>'.$group["name"].':</b></font></td><td>[[['.$group_id.']]]</td></tr>';
          $form["metaspec"]["fields"][$group_id]["type"] = "yesno";
        }
      }
      $form["formtemplate"] .= '
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
        </table>
      ';
      $form["precode"] = '
        $template = &MB_Ref ("ims_".$input["col"]."_templates", $input["id"]);
        $groups = MB_Query ("shield_".$input["col"]."_groups");
        if (is_array($groups)) {
          while (list($group_id)=each($groups)) {
            if ($template["noaccess"][$group_id]) {
              $data[$group_id] = false;
            } else {
              $data[$group_id] = true;

            }
          }
        }
      ';
      $form["postcode"] = '
        $template = &MB_Ref ("ims_".$input["col"]."_templates", $input["id"]);
        $groups = MB_Query ("shield_".$input["col"]."_groups");
        if (is_array($groups)) {
          while (list($group_id)=each($groups)) {
            if ($data[$group_id]) {
              $template["noaccess"][$group_id] = false;
            } else {
              $template["noaccess"][$group_id] = true;
            }
          }
        }
      ';
      $url = FORMS_URL ($form);

      if (SHIELD_HasGlobalRight ($supergroupname, "system")) {
        echo "&nbsp;";
        echo '<a title="'.ML("Recht om gebruik te maken van template","Right to use the template").' '.$template["name"].'" href="';
        echo $url;
        echo '"><img border=0 height=16 width=16 src="/ufc/rapid/openims/group_small.gif"></a>';
      }

      echo "&nbsp;";
      echo '<a title="'.ML("Wijzig de standaard pagina","Change the standard page").'" href="';
      echo IMS_GenerateTransferURL(
             "\\".$supergroupname."\\preview\\templates\\".$key."\\word\\",
             "page.html",
             ($myconfig[$supergroupname]["edit_default_template_doc_html"]?$myconfig[$supergroupname]["edit_default_template_doc_html"]:"winword.exe"), true
           );
      echo '"><img border=0 height=16 width=16 src="/ufc/rapid/openims/word_small.gif"></a>';
/*
      echo "&nbsp;";
      echo '<a title="'.ML("Wijzig de standaard MS-Excel pagina","Change the standard MS-Excel page").'" href="';
      echo IMS_GenerateTransferURL(
             "\\".$supergroupname."\\preview\\templates\\".$key."\\excel\\",
             "page.html",
             "excel.exe", true
           );
      echo '"><img border=0 height=16 width=16 src="/ufc/rapid/openims/excel_small.gif"></a>';

      echo "&nbsp;";
      echo '<a title="'.ML("Wijzig de standaard MS-Powerpoint pagina","Change the standard MS_Powerpoint page").'" href="';
      echo IMS_GenerateTransferURL(
             "\\".$supergroupname."\\preview\\templates\\".$key."\\powerpoint\\",
             "page.html",
             "powerpnt.exe", true
           );
      echo '"><img border=0 height=16 width=16 src="/ufc/rapid/openims/powerpoint_small.gif"></a>';
*/

      echo "&nbsp;";
      echo '<a title="'.ML("Vervang Word, Excel en Powerpoint standaardpagina's door blanco pagina's","Replace the standard Word, Excel and Powerpoint pages by blank pages").'" href="';
      $form = array();
      $form["input"]["sitecollection_id"] = $supergroupname;
      $form["input"]["key"] = $key;
      $form["precode"] = '
        $sitecollection_id = $input["sitecollection_id"];
        $key = $input["key"];
        N_CopyDir ("html::".$sitecollection_id."/preview/templates/".$key."/word/", "html::openims/new_word/");
        N_CopyDir ("html::".$sitecollection_id."/preview/templates/".$key."/excel/", "html::openims/new_excel/");
        N_CopyDir ("html::".$sitecollection_id."/preview/templates/".$key."/powerpoint/", "html::openims/new_powerpoint/");
        $template = &MB_Ref ("ims_".$sitecollection_id."_templates", $key);
        $template["preview"]="yes";
      ';
      echo FORMS_URL ($form);
      echo '"><img border=0 height=16 width=16 src="/ufc/rapid/openims/workstep.gif"></a>';

      echo "&nbsp;";
      echo '<a title="'.ML("Maak kopie van template","Copy template").' '.$template["name"].'" href="/openims/action.php?goto='.urlencode($goto).'&command=copytemplate&sitecollection_id='.$supergroupname.'&template_id='.$key.'">';
      echo '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/copy_small.gif"></a>';

      }

      if ($template["preview"]=="yes") {
        if (SHIELD_HasGlobalRight ($supergroupname, "webtemplatepublish")) {
          echo "&nbsp;";
          echo '<a title="'.ML("Implementeer template","Implement template").' '.$template["name"].'" href="/openims/action.php?goto='.urlencode($goto).'&command=publishtemplate&sitecollection_id='.$supergroupname.'&template_id='.$key.'">';
          echo '<img border=0 height=16 width=16 src="/ufc/rapid/openims/publish_small.gif"></a>';
        }

        if (SHIELD_HasGlobalRight ($supergroupname, "webtemplateedit")) {
          echo "&nbsp;";
          echo '<a title="'.ML("Verwijder template in preview","Remove template in preview").' '.$template["name"].' ('.ML("ga terug naar laatst gepubliceerde template","revert to the last published template").')" href="/openims/action.php?goto='.urlencode($goto).'&command=unpublishtemplate&sitecollection_id='.$supergroupname.'&template_id='.$key.'">';
          echo '<img border=0 height=16 width=16 src="/ufc/rapid/openims/revoke_small.gif"></a>';
        }

      }

      echo "&nbsp;";
      echo '<a title="'.ML("Historie van","History of").' '.$template["name"].'" href="/openims/openims.php?mode=history&back='.urlencode($goto).'&template_id='.$key.'">';
      echo '<img border=0 height=16 width=16 src="/ufc/rapid/openims/history_small.gif"></a>';

      if (SHIELD_HasGlobalRight ($supergroupname, "webtemplatepublish")) {

        $delform = array();
        $delform ["title"] = ML("Bevestig verwijdering","Confirm deletion");
        $delform ["input"]["col"] = $supergroupname;
        $delform ["input"]["key"] = $key;

        $delform ["formtemplate"] = '

           <table>
           <tr><td><font face="arial" size=2>'.ML("Wilt u de template %1 verwijderen?",
                                                  "Do you want to delete template %1?",
                                                  "<b>".$template["name"]."</b>").'</font></td></tr>
           <tr><td colspan=2>&nbsp</td></tr>
           <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
           </table>
           ';
        $delform ["postcode"] = '
          IMS_DeleteTemplate ($input["col"], $input["key"]);
        ';
        $url = FORMS_URL ($delform );

        echo "&nbsp;";
        echo '<a title="'.ML("Verwijder template","Remove template").' '.$template["name"].'" href="'.$url.'">';

        echo '<img border=0 height=16 width=16 src="/ufc/rapid/openims/delete_small.gif"></a>';
      }

      if ($internal=="yes") {
        echo $key;

      }

      T_NewRow();
    }
    $form = array();
    $form["title"] = ML("Kopieer tussen templates", "Copy between templates");
    $form["metaspec"]["fields"]["from"]["type"] = "list";
    $form["metaspec"]["fields"]["to"]["type"] = "list";
    $list = MB_Query ("ims_".$supergroupname."_templates");

    // sort list
    $listsort = $list;
    foreach($listsort as $key=>$dumy) {
      $template = MB_Ref ("ims_".$supergroupname."_templates", $key);
      $listsort[$key] = $template["name"];
    }
    asort($listsort);
    foreach($listsort as $key=>$dumy) {
      $listsort[$key]= $key;
    }
    $list = $listsort;
    unset($listsort);

    if (is_array($list)) reset($list);
    if (is_array($list)) while (list($key)=each($list)) {
      $thetemplate = MB_Ref ("ims_".$supergroupname."_templates", $key);
      $form["metaspec"]["fields"]["from"]["values"][$thetemplate ["name"]] = $key;
      $form["metaspec"]["fields"]["to"]["values"][$thetemplate ["name"]] = $key;
    }
    $form["metaspec"]["fields"]["layout"]["type"] = "yesno";
    $form["metaspec"]["fields"]["content"]["type"] = "yesno";
    $form["metaspec"]["fields"]["meta"]["type"] = "yesno";
    $form["formtemplate"] = '
      <table>
        <tr><td><font face="arial" size=2><b>'.ML("Van","From").':</b></font></td><td>[[[from]]]</td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("Naar","To").':</b></font></td><td>[[[to]]]</td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("Layout","Layout").':</b></font></td><td>[[[layout]]]</td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("Standaard content","Default content").':</b></font></td><td>[[[content]]]</td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("Metadata specificaties","Metadata specifications").':</b></font></td><td>[[[meta]]]</td></tr>
        <tr><td colspan=2>&nbsp</td></tr>
        <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>
    ';
    $form["input"]["sitecollection_id"] = $supergroupname;
    $form["postcode"] = '
      if ($data["to"]!=$data["from"]) {
        $sitecollection_id = $input["sitecollection_id"];
        $fromtemplate = &MB_Ref ("ims_".$sitecollection_id."_templates", $data["from"]);
        $totemplate = &MB_Ref ("ims_".$sitecollection_id."_templates", $data["to"]);
        $totemplate["preview"]="yes";
        if ($data["meta"]) {
          $totemplate["meta"] = $fromtemplate["meta"];
        }
        if ($data["content"]) {
          N_CopyDir ("html::".$sitecollection_id."/preview/templates/".$data["to"]."/word", "html::".$sitecollection_id."/preview/templates/".$data["from"]."/word");
          N_CopyDir ("html::".$sitecollection_id."/preview/templates/".$data["to"]."/excel", "html::".$sitecollection_id."/preview/templates/".$data["from"]."/excel");
          N_CopyDir ("html::".$sitecollection_id."/preview/templates/".$data["to"]."/powerpoint", "html::".$sitecollection_id."/preview/templates/".$data["from"]."/powerpoint");
        }
        if ($data["layout"]) {
          MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(N_CleanPath ("html::/tmp/tempcopy"));
          N_CopyDir ("html::/tmp/tempcopy/word", "html::".$sitecollection_id."/preview/templates/".$data["to"]."/word");
          N_CopyDir ("html::/tmp/tempcopy/excel", "html::".$sitecollection_id."/preview/templates/".$data["to"]."/excel");
          N_CopyDir ("html::/tmp/tempcopy/powerpoint", "html::".$sitecollection_id."/preview/templates/".$data["to"]."/powerpoint");
          N_CopyDir ("html::".$sitecollection_id."/preview/templates/".$data["to"], "html::".$sitecollection_id."/preview/templates/".$data["from"]);
          N_CopyDir ("html::".$sitecollection_id."/preview/templates/".$data["to"]."/word", "html::/tmp/tempcopy/word");
          N_CopyDir ("html::".$sitecollection_id."/preview/templates/".$data["to"]."/word", "html::/tmp/tempcopy/word");
          N_CopyDir ("html::".$sitecollection_id."/preview/templates/".$data["to"]."/word", "html::/tmp/tempcopy/word");
        }
      }
    ';
    $url = FORMS_URL ($form);
    echo "<a href=\"$url\">".$form["title"]."</a>";

    TE_End();
    echo "<br>";
    endblock();

    }
  } else if ($mode=="wms") { // Begin DokuWiki
    uuse("wiki");
    uuse("wikiuif");
    WIKI_CheckNamespace();
    if($submode == "access") {
    startblock(ML("Toegangsrechten", "Access control"), "docnav");
    WIKIUIF_GetAccessControlList();
    endblock();
    } else if($submode == "basic") {
    startblock(ML("Basis instellingen", "Basic settings"), "docnav");
    WIKIUIF_GetBasicSettings();
    endblock();
    } else if($submode == "display") {
    startblock(ML("Beeld instellingen", "Display settings"), "docnav");
    WIKIUIF_GetDisplaySettings();
    endblock();
    } else if($submode == "antispam") {
    startblock(ML("Anti-Spam instellingen", "Anti-Spam settings"), "docnav");
    WIKIUIF_GetAntiSpamSettings();
    endblock();
    } else if($submode == "edit") {
    startblock(ML("Pagina-wijzigings instellingen", "Editing settings"), "docnav");
    WIKIUIF_GetEditingSettings();
    endblock();
    } else if($submode == "link") {
    startblock(ML("Link instellingen", "Link settings"), "docnav");
    WIKIUIF_GetLinkSettings();
    endblock();
    } else if($submode == "media") {
    startblock(ML("Media instellingen", "Media settings"), "docnav");
    WIKIUIF_GetMediaSettings();
    endblock();
    } else if($submode == "advanced") {
    startblock(ML("Geavanceerde instellingen", "Advanced settings"), "docnav");
    WIKIUIF_GetAdvancedSettings();
    endblock();
    } // Einde DokuWiki
  } else if ($mode=="compare") {


    $object = MB_Ref ("ims_".$supergroupname."_objects", $object_id);

    startblock (ML("Vergelijken", "Compare")." \"".$object["shorttitle"]."\"", "docnav");
    echo "<br>";

    reset ($object["history"]);
    while (list($key, $data)=each($object["history"])) {
      $it = $$key;
      if ($it) {
        if ($comp1) {
          if ($comp2) {
            if (!$comp3) {
              $comp3 = $key;
            }
          } else {
            $comp2 = $key;
            $data2 = $data;

          }
        } else {
          $comp1 = $key;
          $data1 = $data;
        }
      }
    }
    if ($comp1 && $comp2 && !$comp3) {
      $doc1 = $doc = FILES_TrueFileName ($supergroupname, $object_id, $comp1);
      $doc2 = FILES_TrueFileName ($supergroupname, $object_id, $comp2);
      $thedoctype1 = $thedoctype = FILES_FileType ($supergroupname, $object_id, $comp1);
      $thedoctype2 = FILES_FileType ($supergroupname, $object_id, $comp2);

      FLEX_LoadSupportFunctions (IMS_SuperGroupName());

      if (!function_exists ("FILES_SpecialCompare")) {
       // DMS special files (default)
       $internal_component = FLEX_LoadImportableComponent ("support", "f56996e35ef98d2f15f2310e62cc75a8");
       $internal_code = $internal_component["code"];
       eval ($internal_code);
      }

      if (FILES_SpecialCompare ($supergroupname, $object_id)) {

        T_Start ("ims", array("noheader"=>"yes"));

        echo "<b>" . ML("Versie door","Version by")." ".SHIELD_UserName ($supergroupname, $data1["author"]);

        echo " - ".N_VisualDate ($data1["when"], true);
        if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
          echo " - ";
          echo ML ("Versie", "Version")." ".IMS_Version (IMS_Supergroupname(), $object_id, $comp1);
        }
        echo "</b>";

        T_NewRow();

        foreach (FILES_SpecialCompare ($supergroupname, $object_id, $comp1, $comp2) as $title => $url) {
          echo "<a href=\"$url\">".ML("Vergelijk met", "Compare with")." $title</a><br>";
        }

        T_NewRow();


        echo "<b>" . ML("Versie door","Version by")." ".SHIELD_UserName ($supergroupname, $data2["author"]);
        echo " - ".N_VisualDate ($data2["when"], true);
        if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {


          echo " - ";
          echo ML ("Versie", "Version")." ".IMS_Version (IMS_Supergroupname(), $object_id, $comp2);
        }
        echo "</b>";

        TE_End();
      }
      if (FILES_Comparable(IMS_SuperGroupName(), $object_id)) {
        T_Start ("ims", array("noheader"=>"yes"));

        echo "<b>" . ML("Versie door","Version by")." ".SHIELD_UserName ($supergroupname, $data1["author"]);
        echo " - ".N_VisualDate ($data1["when"], true);
        if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
          echo " - ";
          echo ML ("Versie", "Version")." ".IMS_Version (IMS_Supergroupname(), $object_id, $comp1);
        }
        echo "</b>";

        T_NewRow();

        echo "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td><font size=\"2\">";
        $file1 = getenv("DOCUMENT_ROOT")."/".$supergroupname."/objects/history/".$object_id."/".$comp1."/".$doc1;
        $file2 = getenv("DOCUMENT_ROOT")."/".$supergroupname."/objects/history/".$object_id."/".$comp2."/".$doc2;

        if ($thedoctype1 != $thedoctype2) {
          // Tested with doc/docx. If you allow the compare, you get many spurious differences that do not reflect real differences beween the documents
          echo ML("Deze versies kunnen niet vergeleken worden omdat het bestandsformaat niet hetzelfde is", "The selected versions can not be compared, because the file types are not the same");
        } else {
          if ($thedoctype == "txt") {
            // Use the raw file instead of the "everything on a single line" output of SEARCH_Any2Text.
            // Escape it (DIFF adds useful HTML markup, so we can't escape the DIFF output).
            $docold = htmlspecialchars(N_ReadFile($file1));
            $docnew = htmlspecialchars(N_ReadFile($file2));
          } else {
            $docold = IMS_Doc2Text ($file1, $thedoctype1);
            $docnew = IMS_Doc2Text ($file2, $thedoctype2);
          }
          $diff = DIFF ($docold, $docnew);
          if ($diff) {
            reset($diff);
            while (list(,$change)=each($diff)) {
              if ($change["action"]=="change") {
                echo '<b>'.ML("Van","From").':&nbsp;&nbsp;"</b>'.$change["old"].'<b>"</b><br>';
                echo '<b>'.ML("Naar","To").': "</b>'.$change["new"].'<b>"</b><br>';
              } else if ($change["action"]=="insert") {

                echo '<b>'.ML("Toegevoegd","Added").': "</b>'.$change["new"].'<b>"</b><br>';
              } else if ($change["action"]=="delete") {
                echo '<b>'.ML("Verwijderd","Deleted").': "</b>'.$change["old"].'<b>"</b><br>';
              }
            }
          } else {
            echo ML("Geen textuele aanpassingen","No textual changes")."<br>";
          }
        }
        echo "</td></tr></table>";
        T_NewRow();

        echo "<b>" . ML("Versie door","Version by")." ".SHIELD_UserName ($supergroupname, $data2["author"]);
        echo " - ".N_VisualDate ($data2["when"], true);
        if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
          echo " - ";
          echo ML ("Versie", "Version")." ".IMS_Version (IMS_Supergroupname(), $object_id, $comp2);
        }
        echo "</b>";

        TE_End();
      }
    } else {
      echo "<b>".ML("Foutmelding", "Error").":</b> ".ML("selecteer 2 versies", "select 2 versions")."<br>";
    }
    echo "<br>";
    endblock();


  } else if ($mode=="related") {

    $object = MB_Ref ("ims_".$supergroupname."_objects", $object_id);
    startblock (ML("Gekoppelde documenten van","Connected documents of")." \"".$object["shorttitle"]."\"", "docnav");
    echo "<br>";
    LINK_ShowLinkinfo ($object_id);
    echo "<br>";
    endblock();

 } else if ($mode=="shortcuts") {

    $object = MB_Ref ("ims_".$supergroupname."_objects", $object_id);
    startblock (ML("Snelkoppelingen van","Shortcuts of")." \"".$object["shorttitle"]."\"", "docnav");
    echo "<br>";
    $list = MB_TurboSelectQuery ("ims_".IMS_SupergroupName()."_objects", '$record["source"]', $object_id);
    T_Start ("ims", array("noheader"=>"yes"));
    foreach ($list as $sckey)
    {
      $scobj = MB_Load("ims_" . IMS_SupergroupName() . "_objects", $sckey);
      $scfolder = $scobj["directory"];
      $tree = CASE_Treeref(IMS_SupergroupName(), $scfolder);
      $path = TREE_Path($tree, $scfolder);
      $url = "/openims/openims.php?mode=dms&currentfolder=".$path[count($path)]["id"];
            
      if (substr ($scfolder, 0, 1)=="(")
      {
         $case_id = substr ($scfolder, 0, strpos ($scfolder, ")")+1);
         $case = MB_Ref ("ims_".IMS_SuperGroupName()."_case_data", $case_id);
         $fullpath  = $case["shorttitle"]." &gt;&gt; ";
      }
      else
      {
        $fullpath = "";
      }
      for($i = 1; $i<=count($path); $i++)
        $fullpath .= $path[$i]["shorttitle"] . ($i < count($path) ? ' &gt; ' : "");

      echo $fullpath; T_Next();
      $title = "Naar folder";
      echo '<a class="ims_navigation"  title="'.$title.'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/folder.gif"> '.ML("Naar folder","To folder").'</a><br>';

      T_NewRow();
    }
    TE_End();
    echo "<br>";
    endblock();

  } else if ($mode=="history" && $thecase) { // BPMS history
    BPMSUIF_History(IMS_SuperGroupName(), $thecase, $theprocess);
  } else if ($mode=="history") { // CMS or DMS history, including templates

    if ($object_id) {
      $object = MB_Ref ("ims_".$supergroupname."_objects", $object_id);
    } else {
      $object = null;
      $template = MB_Ref ("ims_".$supergroupname."_templates", $template_id);
      startblock (ML("Historie van webtemplate","History of web template")." \"".$template["name"]."\"", "docnav");
      echo "<br>";
      T_Start ("ims", array("noheader"=>"yes"));
      foreach ($template["history"] as $id => $data) {
        echo "<b>".SHIELD_UserName ($supergroupname, $data["author"])."</b> ";
        echo "(".N_VisualDate ($data["when"], true).")";
        T_Next();
        if (FILES_TemplateHistoryVersionExistsOnDisk($supergroupname, $template_id, $id)) {
          global $myconfig;
          if ($myconfig[IMS_Supergroupname()]["edit_template_html"]) {
            $editor = $myconfig[IMS_Supergroupname()]["edit_template_html"];
          } else {
            $editor = "frontpg.exe";
          }
          $title = ML ("Bekijk deze versie", "View this version");
          echo "<a class=\"ims_navigation\" title=\"$title\" href=\"";
          echo IMS_GenerateTransferURL(
                 "\\".$supergroupname."\\templates\\history\\".$template_id."\\".$id."\\",
                 "template.html",
                $editor, true, false
               );
          echo '"><img border=0 height=16 width=16 src="/ufc/rapid/openims/view.gif"></a>';

          $title = ML("Herstel deze versie", "Restore this version");
          $form = array();
          $form["title"] = ML("Weet u het zeker?","Are you sure?");
          $form["input"]["sgn"] = $supergroupname;
          $form["input"]["tpl"] = $template_id;
          $form["input"]["ver"] = $id;
          $form["metaspec"]["fields"]["sure"]["type"] = "list";
          $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
          $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
          $form["formtemplate"] = '
            <table width=100>
              <tr><td colspan=2><font face="arial" color="#ff0000" size=2><b>'.$title.'</b></font></td></tr>
              <tr><td colspan=2>&nbsp;</td></tr>
              <tr><td><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td><td><nobr>[[[sure]]]</nobr></td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          ';
          $form["postcode"] = '
            if ($data["sure"]=="yes") {
              $sitecollection_id = $input["sgn"];
              $template_id = $input["tpl"];
              $ver = $input["ver"];
              N_CopyDir ("html::".$sitecollection_id."/preview/templates/".$template_id, "html::".$sitecollection_id."/templates/history/".$template_id."/".$ver);
              $template = &MB_Ref ("ims_".$sitecollection_id."_templates", $template_id);
              $template["preview"] = "yes";
            }
          ';
          $url = FORMS_URL ($form);

          if (SHIELD_HasGlobalRight ($supergroupname, "webtemplateedit")) {
            echo "&nbsp;<a class=\"ims_navigation\" title=\"$title\" href=\"$url".'"><img border=0 height=16 width=16 src="/ufc/rapid/openims/history_small.gif"></a>';
          }
        } else {
          echo "&nbsp;";
        }
        T_NewRow();
      }
      TE_End();
    }

    if ($object["objecttype"]=="webpage") {
      uuse("cmsuif");
      startblock (ML("Historie van webpagina","History of web page")." \"".$object["parameters"]["preview"]["longtitle"]."\"", "docnav"); //stayed in openims.php
      echo "<br>";
      echo CMSUIF_History( $supergroupname, $object_id , $goto );
      echo "<br>";
      endblock();
    }

    if ($object["objecttype"]=="document") {
      uuse("dmsuif");

      if (!SHIELD_HasObjectRight ($supergroupname, $object_id, "view")) N_DIE ("security violation");

      startblock (ML("Historie van document","History of document")." \"".$object["shorttitle"]."\"", "docnav");
      echo "<br>";
      echo DMSUIF_History($supergroupname, $object_id, array("compare" => true, "tablestyle" => "ims"));
      echo "<br>";
      endblock();

    }

  } else if ($mode=="viewhistory") { // DMS view history (who looked at the document)

    if ($object_id) {
      $object = MB_Ref ("ims_".$supergroupname."_objects", $object_id);
    }

    if ($object["objecttype"]=="document") {
      uuse("dmsuif");
//T_EO($object);
      if (!SHIELD_HasObjectRight ($supergroupname, $object_id, "docviewhistory") || !strlen(SHIELD_ReturnWorkflowRightGroups (IMS_SuperGroupName(),  $object["workflow"], "docviewhistory", $extra=""))>0) N_DIE ("security violation");
      startblock (ML("Leeshistorie van document","View history of document")." \"".$object["shorttitle"]."\"", "docnav");
      echo "<br>";
      echo DMSUIF_docviewhistory($object_id, "inpage", array("compare" => true, "tablestyle" => "ims"));
      //echo DMSUIF_History($supergroupname, $object_id, array("compare" => true, "tablestyle" => "ims"));
      echo "<br>";
      endblock();

    }

  } else if ($mode=="admin" && $submode=="fields") {

    startblock (ML("Velden","Fields"), "docnav");
    echo '<br>';
    $fieldsobject = MB_Ref ("ims_fields", $supergroupname);
    if (!is_array($fieldsobject)) $fieldsobject = array();
    ksort ($fieldsobject);

    // table voor filter en nieuw
    echo '<table border="0" cellpadding="0" cellspacing="0" width="100%">';
    echo '<tr>';

    // nieuw - linkje
    $form = array();
    $form["title"] = ML("Nieuw","New");

    $form["input"]["type"] = $specs["type"];
    $form["input"]["supergroupname"] = $supergroupname;

    $form["metaspec"]["fields"]["name"]["type"] = "string";
    $form["metaspec"]["fields"]["title"]["type"] = "strml2";
    $form["metaspec"]["fields"]["type"]["type"] = "list";
    $form["metaspec"]["fields"]["required"]["type"] = "yesno";
    $form["metaspec"]["fields"]["group"]["type"] = "string";

    $form["metaspec"]["fields"]["type"]["values"][ML("Tekst (klein)","Text (small)")] = "smallstring";
    $form["metaspec"]["fields"]["type"]["values"][ML("Tekst (middel)","Text (medium)")] = "string";

    $form["metaspec"]["fields"]["type"]["values"][ML("Keuzelijst","Choice list")] = "list";
    $form["metaspec"]["fields"]["type"]["values"][ML("Meerkeuzelijst","Multiple choice list")] = "multilist";

    $form["metaspec"]["fields"]["type"]["values"][ML("Datum","Date")] = "date";

    $fieldtypefieldspec = $form["metaspec"]["fields"]["type"];

    $form["formtemplate"] = '
      <table>
        <tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[[[[name]]]]]]</td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("Titel","Title").':</b></font></td><td>[[[title]]]<b></b></td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("Type","Type").':</b></font></td><td>[[[type]]]</td></tr>

        <tr><td><font face="arial" size=2><b>'.ML("Verplicht","Required").':</b></font></td><td>[[[required]]]</td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("Groep","Group").':</b></font></td><td>[[[group]]]</td></tr>'; /* OPENIMSCE NOTE: string split*/

    $form["formtemplate"] .= '
        <tr><td colspan=2>&nbsp</td></tr>
        <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>

    ';

    $form["postcode"] = '
      $data["name"] = strtolower ($data["name"]);
      if (!$data["name"]) {
        FORMS_ShowError (ML("Foutmelding","Error"), ML("U moet een naam invoeren","You have to choose a name"), true);

      }
      if (!preg_match ("/^[0-9a-z_]*$/i", $data["name"])) {
        FORMS_ShowError (ML("Foutmelding","Error"), ML("Er mogen alleen letters, cijfers en _ gebruikt worden in veldnamen","Only letters, digits and _ can be used in field names"), true);
      }
      if (!$data["title"]) {
        $data["title"] = strtoupper(substr($data["name"],0,1)).substr($data["name"],1);
      }
      $object = &MB_Ref ("ims_fields", $input["supergroupname"]);

      // check if field allready exists
      if ($object[$data["name"]]) {
        FORMS_ShowError (ML("Foutmelding","Error"), ML("Veld bestaat al, kies een andere naam","Field already exists, choose a different name"), true);
      }

      $object[$data["name"]]["type"] = $data["type"];
      $object[$data["name"]]["title"] = $data["title"];
      $object[$data["name"]]["required"] = $data["required"];
      $object[$data["name"]]["group"] = $data["group"];

      $object[$data["name"]]["advsearchdms"] = N_If ($data["advsearchdms"], "yes", "no");
      $object[$data["name"]]["advsearchbpms"] = N_If ($data["advsearchbpms"], "yes", "no");

      $form["metaspec"]["fields"]["advsearchdms"]["type"] = "yesno";
      $form["metaspec"]["fields"]["advsearchbpms"]["type"] = "yesno";

      N_Log("History fields", "created: id=" . $data["name"] . " name=" . $data["title"] . " type=" . $data["type"] . " (" . SHIELD_CurrentuserFullname() . ")");
    ';
    $newfieldurl = FORMS_URL ($form);
    echo '<td width="50%">';
    echo "<a class=\"ims_link\" href=\"$newfieldurl\">".ML("Nieuw veld toevoegen","Add new field")."</a>";
    echo '</td>';

    // filter box
    $sgroups = Array();
    foreach ($fieldsobject as $sfield => $sspecs) {
       if ($sspecs["group"] != "")
          $sgroups[$sspecs["group"]] = $sspecs["group"];
    }
    asort($sgroups);
    global $filter;
    echo '<td width="50%" align="right">';
    echo '<font face="arial, tahoma, helvetica" size="2">';

    echo ML("Filter","Filter").": ";
    echo "<select onchange='document.location.href=\"".N_AlterURL(str_replace("filter","old",N_MyFullURL()),"old","")."&filter=\"+escape(this.value)'>";
    echo "<option value=''>&lt;".ML("alle velden", "all fields")."&gt;</option>";
    foreach ($sgroups as $skey => $svalue) {
       echo "<option value='".$skey."' ";
       if ($filter == $svalue) echo " selected ";
       echo ">".$svalue."</option>";
    }
    echo "</select>";
    echo "</font>";

    // eind tabel voor filter en nieuw
    echo '</td>';
    echo '</tr>';
    echo '</table>';

    echo '<br>';

    T_Start ("ims", array ("sort"=>"ims_fields", "sort_default_col" => 2, "sort_default_dir" => "u", "sort_bottomskip"=>1,
                           "sort_1"=>"auto", "sort_2"=>"auto", "sort_3"=>"auto", "sort_4"=>"auto", "sort_5"=>"auto", "sort_6"=>"auto"));
    echo ML("Groep","Group");
    T_Next();
    echo ML("Veld","Field");
    T_Next();
    echo ML("Titel","Title");
    T_Next();
    echo ML("Type","Type");
    T_Next();

    echo ML("Verplicht","Required");
    T_Next();
    
    echo " ";
    T_NewRow();

    reset($fieldsobject);
    while (list ($field,$specs) = each ($fieldsobject)) {

     // filter check
     if ((($filter) && ($specs["group"] == $filter)) || ($filter."" == "")) {

      echo $specs["group"];
      T_Next();
      echo "[[[$field]]]";
      T_Next();
      $form = array();
      $form["title"] = ML("Type","Type");
      $form["input"]["type"] = $specs["type"];
      $form["input"]["supergroupname"] = $supergroupname;
      $form["input"]["field"] = $field;
      $form["metaspec"]["fields"]["type"]["type"] = "list";
      $form["metaspec"]["fields"]["title"]["type"] = "smallstrml2";
      $form["metaspec"]["fields"]["group"]["type"] = "smallstring";
      $form["metaspec"]["fields"]["required"]["type"] = "yesno";
      $form["metaspec"]["fields"]["advsearchdms"]["type"] = "yesno";
      $form["metaspec"]["fields"]["advsearchbpms"]["type"] = "yesno";
      $form["metaspec"]["fields"]["type"] = $fieldtypefieldspec;

      $form["formtemplate"] = '
        <table>
          <tr><td><font face="arial" size=2><b>'.ML("Titel","Title").':</b></font></td><td>[[[title]]]<b></b></td></tr>
          <tr><td><font face="arial" size=2><b>'.ML("Type","Type").':</b></font></td><td>[[[type]]]</td></tr>
          <tr><td><font face="arial" size=2><b>'.ML("Verplicht","Required").':</b></font></td><td>[[[required]]]</td></tr>
          <tr><td><font face="arial" size=2><b>'.ML("Groep","Group").':</b></font></td><td>[[[group]]]<b></b></td></tr>
          <tr><td><font face="arial" size=2><b>'.ML("Geavanceerd zoeken in DMS","Advanced search in DMS").':</b></font></td><td>[[[advsearchdms]]]</td></tr>
          <tr><td><font face="arial" size=2><b>'.ML("Geavanceerd zoeken in BPMS","Advanced search in BPMS").':</b></font></td><td>[[[advsearchbpms]]]</td></tr>
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
        </table>
        ';
      $form["precode"] = '
        $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
        $data["type"] = $object[$input["field"]]["type"];
        $data["title"] = $object[$input["field"]]["title"];
        $data["group"] = $object[$input["field"]]["group"];
        $data["required"] = $object[$input["field"]]["required"];
        $data["advsearchdms"] = "no"!=$object[$input["field"]]["advsearchdms"];
        $data["advsearchbpms"] = "no"!=$object[$input["field"]]["advsearchbpms"];
      ';
      $form["postcode"] = '
        $object = &MB_Ref ("ims_fields", $input["supergroupname"]);


        // check if field allready exists
        //if ($object[$data["name"]]) {
        //   FORMS_ShowError (ML("Foutmelding","Error"), ML("Veld bestaat al, kies een andere naam","Field already exists, choose a different name"), true);
        //}

        if ($object[$input["field"]]["type"] != $data["type"])
          N_Log("History fields", "changed type: from " . $object[$input["field"]]["type"] . " to " . $data["type"] . " for " . $input["field"] . " ("  . SHIELD_CurrentUserFullName() . ")");          
        if ($object[$input["field"]]["title"] != $data["title"])
          N_Log("History fields", "changed name: from " . $object[$input["field"]]["title"] . " to " . $data["title"] . " for " . $input["field"] . " ("  . SHIELD_CurrentUserFullName() . ")");          
        $object[$input["field"]]["title"] = $data["title"];
        $object[$input["field"]]["type"] = $data["type"];
        $object[$input["field"]]["group"] = $data["group"];
        $object[$input["field"]]["required"] = $data["required"];
        $object[$input["field"]]["advsearchdms"] = N_If ($data["advsearchdms"], "yes", "no");
        $object[$input["field"]]["advsearchbpms"] = N_If ($data["advsearchbpms"], "yes", "no");

      ';
      $url = FORMS_URL ($form);
      $url_type_dialoog = $url;
      echo "<a class=\"ims_link\" href=\"$url\">".$specs["title"]."</a><b>:</b>";
      T_Next();
      $type = $specs["type"];
      foreach ($form["metaspec"]["fields"]["type"]["values"] as $desc => $val) {
        if ($specs["type"]==$val) $type = $desc;

      }
      echo "<a class=\"ims_link\" href=\"$url\">".$type."</a>";

      if ($specs["type"]=="date" || $specs["type"]=="datetime") {
        $form = Array();
        $form["title"] = ML ("Opties", "Options");
        $form["input"]["supergroupname"] = $supergroupname;
        $form["input"]["object_id"] = $object_id;
        $form["input"]["field"] = $field;
        $form["metaspec"]["fields"]["auto"]["type"] = "yesno";
        $form["metaspec"]["fields"]["edit"]["type"] = "list";
        $form["metaspec"]["fields"]["edit"]["method"] = "radiover";
        $form["metaspec"]["fields"]["edit"]["values"][ML("Kalender", "Calendar") . " (javascript)"]  = "javascript";
        $form["metaspec"]["fields"]["edit"]["values"][ML("Alleen HTML", "HTML only")]  = "html";
        $form["metaspec"]["fields"]["minyear"]["type"] = "list";
        foreach (array(0, -1, -2, -3, -4, -5, -10, -20, -50, -100) as $yearoffset) {
          $desc = ML("Huidig jaar", "Current year");
          if ($yearoffset) $desc .= " " . sprintf("%+d", $yearoffset);
          $form["metaspec"]["fields"]["minyear"]["values"][$desc] = "current" . sprintf("%+d", $yearoffset);
        }
        foreach (array(1900, 1950, 1970, 1980, 1990, 2000, 2010) as $year) $form["metaspec"]["fields"]["minyear"]["values"][$year] = $year;
        $form["metaspec"]["fields"]["maxyear"]["type"] = "list";

        foreach (array(2000, 2010, 2020, 2038, 2050, 2099) as $year) $form["metaspec"]["fields"]["maxyear"]["values"][$year] = $year;
        foreach (array(0, +1, +2, +3, +4, +5, +10, +20, +50, +100) as $yearoffset) {
          $desc = ML("Huidig jaar", "Current year");
          if ($yearoffset) $desc .= " " . sprintf("%+d", $yearoffset);
          $form["metaspec"]["fields"]["maxyear"]["values"][$desc] = "current" . sprintf("%+d", $yearoffset);
        }

        $form["metaspec"]["fields"]["format"]["type"] = "list";
        $form["metaspec"]["fields"]["format"]["method"] = "radiover";
        $date = 1234567890; // Use a fixed date, not the current date, because the examples are confusing on 1/1, 2/2, 3/3 etc.
        global $myconfig;
        $langs = $myconfig[IMS_SuperGroupName()]["ml"]["languages"];
        $currentlang = ML_GetLanguage();
        if (!$langs) $langs = array("nl", "en");
        $langformats = array();
        foreach ($langs as $lang) {
          ML_SetLanguage($lang);
          // If you add a format here, you must add it in FORMS_ShowValue() as well!
          $langformats['default'][] = N_Date ("j F Y", "F jS Y", $date, array("de" => "j. F Y"));
          $langformats['numeric'][] = N_Date ("d-m-Y", "m-d-Y", $date, array("de" => "d. m. Y"));
          $langformats['shortnumeric'][] = N_Date ("d-m-y", "m-d-y", $date, array("de" => "d. m. y"));
          $langformats['reversednumeric'][] = N_Date ("Y-m-d", "Y-m-d", $date);
          $langformats['anniversary'][] = N_Date ("j F", "j F", $date);
          $langformats['visual'][] = N_VisualDate($date);
        }
        ML_SetLanguage($currentlang);
        foreach ($langformats as $internal => $visualarr) {
          $visual = implode(" /// ", $visualarr);
          $form["metaspec"]["fields"]["format"]["values"][$visual] = $internal;
        }
        $form["metaspec"]["fields"]["format"]["default"] = "default";
        $form["formtemplate"] = '
          <table>
            <tr><td><font face="arial" size=2><b>'.ML("Automatisch", "Automatic").':</b></font></td><td colspan=2>[[[auto]]]</td></tr>
            <tr><td><font face="arial" size=2><b><nobr>'.ML("Wijzigen via", "Edit using").':</nobr></b></font></td><td><font face="arial" size=2><nobr>[[[edit]]]</nobr></font></td><td valign="bottom"><font face="arial" size=2><nobr>HTML '.ML("jaartal", "year").' '.ML("van %1 tot %2", "from %1 to %2", "[[[minyear]]]", "[[[maxyear]]]").'</nobr></td></font></td></tr>
            <tr><td><font face="arial" size=2><b>'.ML("Formaat", "Format").':</b></font></td><td colspan=2><font face="arial" size=2><nobr>[[[format]]]</nobr></font></td></tr>
            <tr><td>&nbsp;</td><td colspan=2><font face="arial" size=2><small>('.ML("wordt alleen gebruikt bij bekijken, niet bij wijzigen","only used when viewing, not when editing").')</small></td></tr>
            <tr><td colspan=3>&nbsp;</td></tr>
            <tr><td colspan=3><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["precode"] = '
          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          $data["auto"] = $object[$input["field"]]["auto"];
          $data["format"] = $object[$input["field"]]["format"];
          $data["edit"] = $object[$input["field"]]["specs"]["edit"]; // start using "specs" like other fields do, to avoid name clashes
          $data["minyear"] = $object[$input["field"]]["specs"]["minyear"];
          $data["maxyear"] = $object[$input["field"]]["specs"]["maxyear"];
          if (!$data["edit"]) $data["edit"] = "javascript";
          if (!$data["minyear"]) $data["minyear"] = 1900;
          if (!$data["maxyear"]) $data["maxyear"] = "current+0";
        ';
        $form["postcode"] = '
          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          $object[$input["field"]]["auto"] = $data["auto"];
          $object[$input["field"]]["format"] = $data["format"];
          $object[$input["field"]]["specs"]["edit"] = $data["edit"];
          $object[$input["field"]]["specs"]["minyear"] = $data["minyear"];
          $object[$input["field"]]["specs"]["maxyear"] = $data["maxyear"];
        ';
        $url = FORMS_URL ($form);
        echo " (<a class=\"ims_link\" href=\"$url\">".ML("opties","options")."</a>)";
      }
/*
      if ($specs["type"]=="hyperlink" && ($myconfig[IMS_SuperGroupName()]["ml"]["multilingualfields"] == "yes" || $specs["mltext"] || $specs["mlurl"])) {
        $form = Array();
        $form["title"] = ML ("Opties", "Options");
        $form["input"]["supergroupname"] = $supergroupname;
        $form["input"]["field"] = $field;
        $form["metaspec"]["fields"]["mltext"]["type"] = "yesno";
        $form["metaspec"]["fields"]["mlurl"]["type"] = "yesno";
        $form["formtemplate"] = '
          <table>
            <tr><td><font face="arial" size=2><b>'.ML("Meertalige tekst", "Multilingual text").':</b></font></td><td>[[[mltext]]]</td></tr>
            <tr><td><font face="arial" size=2><b>'.ML("Meertalige URL", "Multilingual URL").':</b></font></td><td><font face="arial" size=2>[[[mlurl]]]</font></td></tr>
            <tr><td colspan=2>&nbsp;</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["precode"] = '
          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          $data["mltext"] = $object[$input["field"]]["mltext"];
          $data["mlurl"] = $object[$input["field"]]["mlurl"];
        ';
        $form["postcode"] = '
          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          $object[$input["field"]]["mltext"] = $data["mltext"];
          $object[$input["field"]]["mlurl"] = $data["mlurl"];
        ';
        $url = FORMS_URL ($form);
        echo " (<a class=\"ims_link\" href=\"$url\">".ML("opties","options")."</a>)";
      }
*/
      if ($specs["type"]=="strml") {
        $form = Array();
        $form["title"] = ML ("Opties", "Options");
        $form["input"]["supergroupname"] = $supergroupname;
        $form["input"]["field"] = $field;
        $form["metaspec"]["fields"]["cols"]["type"] = "list";
        $form["metaspec"]["fields"]["cols"]["method"] = "other";
        $form["metaspec"]["fields"]["cols"]["default"] = 30;
        $form["metaspec"]["fields"]["cols"]["values"][15] = 15;
        $form["metaspec"]["fields"]["cols"]["values"][30] = 30;
        $form["metaspec"]["fields"]["cols"]["values"][60] = 60;
        $form["metaspec"]["fields"]["autolevel"]["type"] = "list";
        $form["metaspec"]["fields"]["autolevel"]["values"]["&lt;".ML("geen","none")."&gt;"] = "";
        $form["metaspec"]["fields"]["autolevel"]["values"]["ML_WIZARDS"] = 1;
        $form["metaspec"]["fields"]["autolevel"]["values"]["ML_FIELDS"] = 2; // TODO: should become FIELDTITLE and FIELDVALUE
        $form["metaspec"]["fields"]["autolevel"]["values"]["ML_WORKFLOW"] = 3;
        $form["metaspec"]["fields"]["autolevel"]["values"]["ML_DMSMETA"] = 4;
        $form["metaspec"]["fields"]["autolevel"]["values"]["ML_FOLDERS"] = 5;
        $form["metaspec"]["fields"]["autolevel"]["values"]["ML_CMSMETA"] = 6;
        $form["formtemplate"] = '
          <table>
            <tr><td><font face="arial" size=2><b>'.ML("Breedte", "Width").':</b></font></td><td><font face="arial" size=2>[[[cols]]]</font></td></tr>
            <tr><td><font face="arial" size=2><b>'.ML("Vereist", "Required").' metadatalevel ('.ML("site configuratie", "site configuration").'):</b></font></td><td><font face="arial" size=2>[[[autolevel]]]</font></td></tr>
            <tr><td colspan=2>&nbsp;</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["precode"] = '
          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          $data["cols"] = $object[$input["field"]]["specs"]["cols"];
          $data["rows"] = $object[$input["field"]]["specs"]["rows"];
          $data["autolevel"] = $object[$input["field"]]["specs"]["autolevel"];
        ';
        $form["postcode"] = '
          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          $object[$input["field"]]["specs"]["cols"] = $data["cols"];
          $object[$input["field"]]["specs"]["rows"] = $data["rows"];
          $object[$input["field"]]["specs"]["autolevel"] = $data["autolevel"];
        ';
        $url = FORMS_URL ($form);
        echo " (<a class=\"ims_link\" href=\"$url\">".ML("opties","options")."</a>)";
      }
      if ($specs["type"]=="txtml") {
        $form = Array();
        $form["title"] = ML ("Opties", "Options");
        $form["input"]["supergroupname"] = $supergroupname;
        $form["input"]["field"] = $field;
        $form["metaspec"]["fields"]["cols"]["type"] = "list";
        $form["metaspec"]["fields"]["cols"]["method"] = "other";
        $form["metaspec"]["fields"]["cols"]["default"] = 46;
        $form["metaspec"]["fields"]["cols"]["values"][23] = 23;
        $form["metaspec"]["fields"]["cols"]["values"][46] = 46;
        $form["metaspec"]["fields"]["rows"]["type"] = "list";
        $form["metaspec"]["fields"]["rows"]["method"] = "other";
        $form["metaspec"]["fields"]["rows"]["default"] = 3;
        $form["metaspec"]["fields"]["rows"]["values"][3] = 3;
        $form["metaspec"]["fields"]["rows"]["values"][4] = 4;
        $form["metaspec"]["fields"]["rows"]["values"][12] = 12;
        $form["metaspec"]["fields"]["autolevel"]["type"] = "list";
        $form["metaspec"]["fields"]["autolevel"]["values"]["&lt;".ML("geen","none")."&gt;"] = "";
        $form["metaspec"]["fields"]["autolevel"]["values"]["ML_WIZARDS"] = 1;
        $form["metaspec"]["fields"]["autolevel"]["values"]["ML_FIELDS"] = 2;
        $form["metaspec"]["fields"]["autolevel"]["values"]["ML_WORKFLOW"] = 3;
        $form["metaspec"]["fields"]["autolevel"]["values"]["ML_DMSMETA"] = 4;
        $form["metaspec"]["fields"]["autolevel"]["values"]["ML_FOLDERS"] = 5;
        $form["metaspec"]["fields"]["autolevel"]["values"]["ML_CMSMETA"] = 6;
        $form["formtemplate"] = '
          <table>
            <tr><td><font face="arial" size=2><b>'.ML("Breedte", "Width").':</b></font></td><td><font face="arial" size=2>[[[cols]]]</font></td></tr>
            <tr><td><font face="arial" size=2><b>'.ML("Hoogte", "Height").':</b></font></td><td><font face="arial" size=2>[[[rows]]]</font></td></tr>
            <tr><td><font face="arial" size=2><b>'.ML("Vereist", "Required").' metadatalevel ('.ML("site configuratie", "site configuration").'):</b></font></td><td><font face="arial" size=2>[[[autolevel]]]</font></td></tr>
            <tr><td colspan=2>&nbsp;</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["precode"] = '
          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          $data["cols"] = $object[$input["field"]]["specs"]["cols"];
          $data["rows"] = $object[$input["field"]]["specs"]["rows"];
          $data["autolevel"] = $object[$input["field"]]["specs"]["autolevel"];
        ';
        $form["postcode"] = '
          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          $object[$input["field"]]["specs"]["cols"] = $data["cols"];
          $object[$input["field"]]["specs"]["rows"] = $data["rows"];
          $object[$input["field"]]["specs"]["autolevel"] = $data["autolevel"];
        ';
        $url = FORMS_URL ($form);
        echo " (<a class=\"ims_link\" href=\"$url\">".ML("opties","options")."</a>)";
      }

      /**** dvb : mutiple handlers for auto-field, removed this one
      if ($specs["type"]=="auto") {
        $form = array();
        $form["title"] = ML("Auto (php)","Auto (php)");
        $form["input"]["supergroupname"] = $supergroupname;
        $form["input"]["field"] = $field;
        $form["metaspec"]["fields"]["calc"]["type"] = "verywidetext";
        $form["formtemplate"] = '
          <table>
            <tr><td><font face="arial" size=2><b>'.ML("Berekening (php)", "Calculation (php)").':</b> '.ML("Invoer", "Input").': $data '.ML("en","and").' \$object<br/>'.ML("Uitvoer","Output").': $value</font></td></tr>
            <tr><td>[[[calc]]]</td></tr>
            <tr><td>&nbsp;</td></tr>
            <tr><td><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]&nbsp;&nbsp;&nbsp;[[[RESET]]]</center></td></tr>
          </table>
        ';
        $form["precode"] = '
          $object = MB_Ref ("ims_fields", $input["supergroupname"]);
          $data["calc"] = $object[$input["field"]]["calc"];
        ';
        $form["postcode"] = '
          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          $object[$input["field"]]["calc"] = $data["calc"];
        ';
        $url = FORMS_URL ($form);
        echo " (<a class=\"ims_link\" href=\"$url\">".ML("code","code")."</a>)";
      }
      ******/

      if ($specs["type"]=="list") {
        $form = Array();
        $form["title"] = ML ("Opties", "Options");
        $form["input"]["supergroupname"] = $supergroupname;
        $form["input"]["object_id"] = $object_id;
        $form["input"]["field"] = $field;
        $count = 0;

        if (is_array ($specs["values"])) while (list ($visible, $internal) = each ($specs["values"])) {
          $count++;
        }
        $form["formtemplate"] = '
          <table>
            <tr><td><font face="arial" size=2><b>'.ML("Zichtbaar","Visible").'</a></font></td><td><font face="arial" size=2><b>'.ML("Intern","Internal").'</a></font></td></tr>
        ';
        for ($i=1; $i<=($count+10); $i++) {
          if ($myconfig[IMS_SuperGroupName()]["ml"]["multilingualfields"] == "yes") {
            $form["metaspec"]["fields"]["visible$i"]["type"] = "strml";
            $form["metaspec"]["fields"]["visible$i"]["specs"]["horizontal"] = true;
          } else {
            $form["metaspec"]["fields"]["visible$i"]["type"] = "strml31"; // Do not show a multilingual interface, but do please handle multilingual values correctly
          }
          if ($specs["blocks"]) {
            $form["metaspec"]["fields"]["internal$i"]["type"] = "smalltext";
          } else {
            $form["metaspec"]["fields"]["internal$i"]["type"] = "string";
          }
          $form["formtemplate"] .= '<tr><td>[[[visible'.$i.']]]</td><td>[[[internal'.$i.']]]</td></tr>';
        }
        $form["metaspec"]["fields"]["show"]["type"] = "list";
        $form["metaspec"]["fields"]["show"]["values"][ML("Zichtbaar","Visible")] = "visible";
        $form["metaspec"]["fields"]["show"]["values"][ML("Intern","Internal")] = "";
        $form["metaspec"]["fields"]["method"]["type"] = "list";
        $form["metaspec"]["fields"]["method"]["values"][ML("1 keuze (keuzelijst)","1 choice (listbox)")] = "";
        $form["metaspec"]["fields"]["method"]["values"][ML("1 keuze (horizontale lijst met keuzes)","1 choice (horizontal radiobuttons)")] = "radiohor";
        $form["metaspec"]["fields"]["method"]["values"][ML("1 keuze (verticale lijst met keuzes)","1 choice (vertical radiobuttons)")] = "radiover";
        $form["metaspec"]["fields"]["method"]["values"][ML("1 keuze + overig","1 choice + other")] = "other";
        $form["metaspec"]["fields"]["method"]["values"][ML("Meerdere keuzes","Multiple choices")] = "multi";
        if ($myconfig[IMS_SuperGroupName()]["customlistmethod"]) foreach ($myconfig[IMS_SuperGroupName()]["customlistmethod"] as $methodname => $methodspecs) {
          $methodtitle = $methodspecs["title"];
          if (!$methodtitle) $methodtitle = $methodname;
          $form["metaspec"]["fields"]["method"]["values"][$methodtitle] = "custom".$methodname;
        }
        $form["metaspec"]["fields"]["sort"]["type"] = "yesno";
        $form["metaspec"]["fields"]["blocks"]["type"] = "yesno";
        $form["formtemplate"] .= '
            <tr><td><font face="arial" size=2><b>'.ML("Methode", "Method").'</b></font></td><td>[[[method]]]</td></tr>
            <tr><td><font face="arial" size=2><b>'.ML("Weergave", "Show").'</b></font></td><td>[[[show]]]</td></tr>
            <tr><td><font face="arial" size=2><b>'.ML("Sorteren", "Sort").'</b></font></td><td>[[[sort]]]</td></tr>
            <tr><td><font face="arial" size=2><b>'.ML("Tekstblokken", "Text blocks").'</b></font></td><td>[[[blocks]]]</td></tr>
            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>

          </table>
        ';
        $form["precode"] = '

          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          if (is_array ($object[$input["field"]]["values"])) {
            global $myconfig;      
            if ( $myconfig[IMS_SuperGroupName()]["admin_sort_choicelist_values"] == "yes" && $object[$input["field"]]["sort"] )
            {
              ksort( $object[$input["field"]]["values"] );
            }

            while (list ($visible, $internal) = each ($object[$input["field"]]["values"])) {
              $count++;
              $data["visible$count"] = $visible;
              $data["internal$count"] = $internal;
            }
          }
          $data["method"] = $object[$input["field"]]["method"];
          $data["show"] = $object[$input["field"]]["show"];
          $data["sort"] = $object[$input["field"]]["sort"];
          $data["blocks"] = $object[$input["field"]]["blocks"];
        ';
        $form["postcode"] = '

          if ($data["method"]) {
            for ($i=1; $i<1000; $i++) {
              if ($data["visible$i"] && !$data["internal$i"]) {
                FORMS_ShowError (ML("Foutmelding", "Error"), ML("Interne waarde mag niet leeg zijn", "Internal value cannot be empty"));
              }
            }
          }
          for ($i=1; $i<1000; $i++) {
            if (strpos (" ".$data["internal$i"], ";")) {
              FORMS_ShowError (ML("Foutmelding", "Error"), ML("Interne waarde mag geen ; bevatten", "Internal value can not contain a ;"));
            }
          }
          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          $oldvalues = array();
          $oldvalues = $object[$input["field"]]["values"];
          unset ($object[$input["field"]]["values"]);
          for ($i=1; $i<1000; $i++) {
            if ($data["visible$i"]) {
              $object[$input["field"]]["values"][$data["visible$i"]] = $data["internal$i"];
            }
          }
          if ($oldvalues != $object[$input["field"]]["values"])
            N_Log("History fields", "changed options: for " . $input["field"] . " ("  . SHIELD_CurrentUserFullName() . ")");

          $object[$input["field"]]["method"] = $data["method"];
          $object[$input["field"]]["show"] = $data["show"];
          $object[$input["field"]]["sort"] = $data["sort"];
          $object[$input["field"]]["blocks"] = $data["blocks"];
        ';
        $url = FORMS_URL ($form);
        echo " (<a class=\"ims_link\" href=\"$url\">".ML("opties","options")."</a>)";
      }
      if ($specs["type"]=="multilist") {
        // This field is used to create a multi-list- or multi-fk-field, by pointing to an (existing) list or fk-field.
        // For multi-list, the advantage is that the list items do not need to be specified again.
        // For multi-fk, this is the only way to create a multi-fk field.
        // multi-fk is only possible and available when "autocompletefields" is enabled.
        $form = Array();
        $form["title"] = ML ("Opties", "Options");
        $form["input"]["supergroupname"] = $supergroupname;
        $form["input"]["object_id"] = $object_id;
        $form["input"]["field"] = $field;
        $count = 0;

        if (is_array ($specs["values"])) while (list ($visible, $internal) = each ($specs["values"])) {
          $count++;
        }
        $form["formtemplate"] = '
          <table>
            <tr><td><font face="arial" size=2><b>'.ML("Bron","Source").'</a></font></td><td>[[[source]]]</td></tr>
            <tr><td colspan=2>&nbsp;</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["metaspec"]["fields"]["source"]["type"] = "list";
        foreach (MB_Load("ims_fields", IMS_SuperGroupName()) as $thefieldname => $thefieldspecs) {
          global $myconfig;
          if (($thefieldspecs["type"] == "fk" && $myconfig[IMS_SuperGroupName()]["autocompletefields"] == "yes") // multi-FK ONLY works with autocomplete
              || $thefieldspecs["type"] == "list") // multi-list is the same thing as method=multi in normal lists
            {
            $form["metaspec"]["fields"]["source"]["values"][$thefieldspecs["title"]] = $thefieldname;
          }
        }

        $form["precode"] = '
          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          $data["source"] = $object[$input["field"]]["source"];
        ';
        $form["postcode"] = '
          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          if ($object[$input["field"]]["source"] != $data["source"]) {
            N_Log("History fields", "changed options: for " . $input["field"] . " ("  . SHIELD_CurrentUserFullName() . ")");
          }

          $object[$input["field"]]["source"] = $data["source"];
        ';
        $url = FORMS_URL ($form);
        echo " (<a class=\"ims_link\" href=\"$url\">".ML("opties","options")."</a>)";
      }
      T_Next();
      // db: allow clones to have required fields of their own
      if ( (false) && ($specs["type"] == "clone")) {
        echo " ";
      } else {
        if ($specs["required"]) {
          echo "<a class=\"ims_link\" href=\"$url_type_dialoog\">".ML("Ja","Yes")."</a>";
        } else {
          echo "<a class=\"ims_link\" href=\"$url_type_dialoog\">".ML("Nee","No")."</a>";
        }
      }
      T_Next();

      T_Next();

      $form = array();
      $form["title"] = ML("Bevestig verwijdering","Confirm deletion");
      $form["input"]["supergroupname"] = $supergroupname;
      $form["input"]["field"] = $field;

      $form["formtemplate"] = '
          <table>
          <tr><td><font face="arial" size=2>'.ML("Wilt u het veld %1 verwijderen?","Do you want to delete field %1?", "<b>$field</b>").
             '</font></td></tr>
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
          ';

      $form["postcode"] = '

          $object = &MB_Ref ("ims_fields", $input["supergroupname"]);
          unset ($object[$input["field"]]);
          N_Log("History fields", "deleted: " . $input["field"] . " ("  . SHIELD_CurrentUserFullName() . ")");
          ';

      $url = FORMS_URL ($form);
      echo "<a class=\"ims_link\" href=\"$url\">".ML("verwijder","delete")."</a>";

      T_NewRow();

     //DDD end filter
     }

    }

    T_Next();
    T_Next();

    echo "<a class=\"ims_link\" href=\"$newfieldurl\">".ML("nieuw","new")."</a>";
    T_NewRow();
    TE_End();

    echo '<br>';
    endblock();

  } else if ($mode=="admin" && $submode=="flex_code" && SHIELD_HasGlobalRight ($supergroupname, "develop", $mysecuritysection)) {

    startblock (ML("Code tester","Code tester"), "docnav");
    echo "<br>";


    $code = str_replace(chr(160), chr(32), stripcslashes($code ));

    $code = str_replace(chr(13).chr(10), chr(10), $code );
    $code = str_replace(chr(13), "", $code );

?>
      <form action="/openims/openims.php?mode=admin&submode=flex_code" method="post">
        <textarea style="width: 600px; height: 250px; font-size: 8pt;" name="code" id="codetester"><? echo htmlentities ($code); ?></textarea><br>
        <input type="submit" style="font-weight:bold" value="<? echo ML("Uitvoeren","Execute")?>">
      </form>
<?

        function show_ms ($ms)
        {
          $tmp = ((int)($ms*10))/10+0.01;
          return substr ($tmp, 0, strlen($tmp)-1);
        }

        if ("".$code != "") {
          echo "<b>--- ".ML("Voor", "Before")." ---</b><font size=2><br>";
          $T_BEFORE = N_MicroTime();
          eval ($code);
          $T_AFTER = N_MicroTime();
          echo "<BR><b>--- ".ML("Na","After")." ---</b><font size=2><br><br>";
          echo ML("Benodigde tijd", "Elapsed time").": <b>" . show_ms(1000*($T_AFTER-$T_BEFORE)) . "</b> ms<br>";
        }

    echo "<br>";
    endblock();

  }  else if ($mode=="admin" && $submode=="syslogs_showdate" && SHIELD_HasGlobalRight ($supergroupname, "develop", $mysecuritysection)) {

    startblock (ML("Systeemlogboek","System log")." ".$id, "docnav");
    echo "<br>";

    $all = explode (chr(13).chr(10), N_ReadFile ("html::/tmp/logging/$id/$date.log"));
    $total = count($all)-1;
    if (!$block) $block=1;
    $maxblock = (int)(($total-1)/100)+1;
    if ($block>$maxblock) $block=maxblock;
    $from = ($block-1)*100+1;
    $to = ($block)*100;
    if ($to>$total) $to=$total;
    echo "<b>".N_VisualDate (N_BuildDate (substr ($date, 0, 4), substr ($date, 4, 2), substr ($date, 6, 2)))."</b> - ";
    if ($showall=="yes") {
      echo ML ("Regels","Lines")." <b>1</b> - <b>".$total."</b> ".ML("van", "from")." <b>".$total."</b>";
      echo " - <b>";
      $url = N_AlterURL (N_MyFullURL(), "showall", "");
      echo " <a href=\"$url\">".ML("bladeren","browse")."</a> ";
      echo "</b>";
    } else {
      echo ML ("Regels","Lines")." <b>".$from."</b> - <b>".$to."</b> ".ML("van", "from")." <b>".$total."</b>";
      if ($maxblock<>1) {
        echo " - <b>";
        $url = N_AlterURL (N_MyFullURL(), "block", 1);
        if ($block!=1) {
          $url = N_AlterURL (N_MyFullURL(), "block", 1);
          echo " <a title=\"".ML("start","start")."\" href=\"$url\">&lt;&lt;&lt;</a> ";
          $url = N_AlterURL (N_MyFullURL(), "block", $block-1);
          echo " <a title=\"".ML("terug","back")."\" href=\"$url\">&lt;&lt;</a> ";
        }
        $url = N_AlterURL (N_MyFullURL(), "showall", "yes");
        echo " <a title=\"".ML("toon alle regels","show all lines")."\" href=\"$url\">".ML("alles","all")."</a> ";
        if ($block!=$maxblock) {
          $url = N_AlterURL (N_MyFullURL(), "block", $block+1);
          echo " <a title=\"".ML("verder","forward")."\" href=\"$url\">&gt;&gt;</a> ";
          $url = N_AlterURL (N_MyFullURL(), "block", $maxblock);
          echo " <a title=\"".ML("einde","end")."\" href=\"$url\">&gt;&gt;&gt;</a> ";
        }
        echo "</b>";
      }
    }
    echo "<br>";

    foreach ($all as $line)
    {
      if ($line) {
        ++$ctr;
        if ($showall=="yes" || ($ctr>=$from && $ctr<=$to)) {
          if (substr ($line, 38, 1)==" ") {
            $line = substr ($line, 0, 33) . "0" . substr ($line, 33);
          }
          $guid = substr ($line, 0, 32);
          $time = substr ($line, 33, 6);
          $subject = substr ($line, 40);
          echo "<nobr>".substr($time, 0, 2).":".substr ($time, 2, 2).":".substr ($time,4,2)." ".$subject." ";
          if (N_FileSize ("html::tmp/logging/$id/short/$date/$guid.txt") || N_FileSize ("html::tmp/logging/$id/long/$date/$guid.txt")) {
            echo "<a href=\"/openims/showlog.php?id=$id&date=$date&time=$time&guid=$guid&subject=".urlencode($subject)."\" target=\"_blank\">".ML("details", "details")."</a> ";
          }
          echo "</nobr><br>";
        }
      }
    }

    echo "<br>";
    endblock();

  } else if ($mode=="admin" && $submode=="syslogs_showlatest" && SHIELD_HasGlobalRight ($supergroupname, "develop", $mysecuritysection)) {

    startblock (ML("Systeemlogboek","System log")." ".$id, "docnav");
    echo "<br>";
    uuse("adminuif");
    $latestlines = intval($_REQUEST["lines"]);
    if (!$latestlines) $latestlines = 100;
    echo ADMINUIF_TopLog($id, $latestlines);

    echo "<br>";
    endblock();

  } else if ($mode=="admin" && $submode=="syslogs_show" && SHIELD_HasGlobalRight ($supergroupname, "develop", $mysecuritysection)) {

    startblock (ML("Systeemlogboek","System log")." ".$id, "docnav");
    echo "<br>";

    $latestlines = 100;
    echo "<a href=\"/openims/openims.php?mode=admin&submode=syslogs_showlatest&id=$id&lines=$latestlines\">";
    echo ML("meest recente %1 regels", "latest %1 entries", $latestlines);
    echo "</a><br><br>";

    $list = N_QuickTree ("html::/tmp/logging/$id/", "", false);
    $list = N_SortByRev ($list, '$object["filename"]');
    foreach ($list as $path=>$specs) {
      $date = substr ($specs["filename"], 0, 8);
      echo "<a href=\"/openims/openims.php?mode=admin&submode=syslogs_showdate&id=$id&date=$date\">";
      echo N_VisualDate (N_BuildDate (substr ($specs["filename"], 0, 4), substr ($specs["filename"], 4, 2), substr ($specs["filename"], 6, 2)))." ";
      $filesize = N_FileSize(N_CleanPath ($path));
      echo "<font size=\"1\">(".$filesize." bytes)</font>";
      echo "</a><br>";
    }

    echo "<br>";
    endblock();

  } else if ($mode=="admin" && $submode=="syslogs" && SHIELD_HasGlobalRight ($supergroupname, "develop", $mysecuritysection)) {

    startblock (ML("Systeemlogboeken","System logs"), "docnav");
    echo "<br>";

    $list = N_Subs ("html::/tmp/logging");
    $list = N_SortBy ($list, '$key');
    if ($list) {
      foreach ($list as $id => $dummy) {

        echo "<a href=\"/openims/openims.php?mode=admin&submode=syslogs_show&id=$id\">".ML("Toon", "Show")." <b>".$id."</b> ".ML("systeemlogboek", "system log")."</a><br>";
      }
    } else {
      echo ML("Het systeemlogboek is leeg", "The system log is empty")."<br>";
    }
    echo "<br>";

    $form = array();
    $form["title"] = ML("Opschonen systeemlogboeken","Cleanup system logs");
    $form["formtemplate"] = '
      <table>
      <tr><td><font face="arial" size=2>'.ML("Wilt u alle systeemlogregels verwijderen?","Do you want to delete al system log entries?").'</font></td></tr>
      <tr><td colspan=2>&nbsp</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>
    ';
    $form["postcode"] = '
      MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(N_CleanPath ("html::/tmp/logging"));
      N_WriteFile ("html::/tmp/logging/.htaccess", "deny from all".chr(13).chr(10)); 
    ';
    $url = FORMS_URL ($form);
    echo "<a href=\"$url\">".$form["title"]."</a><br>";

    echo "<br>";
    endblock();

  }  else if ($mode=="admin" && $submode=="flex" && SHIELD_HasGlobalRight ($supergroupname, "develop", $mysecuritysection)) {

    uuse ("flex");
    if (!BLACK_OK()) die ("");
    startblock (ML("Inrichting (ge&iuml;ntegreerde omgeving)","Configuration (integrated environment)"), "docnav");
    echo '<br>';
    T_Start("ims", array ("noheader"=>true));
    echo "<b>".ML ("Hulpmiddelen","Tools")."</b><br>";
    T_NewRow();
    echo "&nbsp;&nbsp;&nbsp;";
    T_Next();
    echo "<a target=\"_blank\" href=\"http://doc.openims.com/openimsdoc_com/aad265660cfe075d0b986b3212a8e2a2.php\">".ML("Handleiding OpenIMS-inrichting", "Configuration manual (Dutch)")."</a><br>";

    echo "<a href=\"/openims/openims.php?mode=admin&submode=flex_code\">".ML("Codetester", "Code tester")."</a><br>";
    echo "<a href=\"/openims/openims.php?mode=admin&submode=syslogs\">".ML("Systeemlogboeken", "System logs")."</a><br>";
    T_NewRow();
?>
<table>
  <form action="/openims/openims.php" method="put"><!--verwijzing naar http door JG weggehaald omdat dit op https niet juist werkt //-->
    <tr><td><nobr>
      <font face="arial" size="2"><b><? echo ML ("Doorzoek componenten", "Search through components"); ?>:</b>&nbsp;</font>
    </nobr></td><td>
      <input type="hidden" name="mode" value="admin">
      <input type="hidden" name="submode" value="flex">
      <input title="Zoektermen" type="text" name="q" size="18" class="style10px" value="<? echo $q; ?>"><br>
    </td><td>
      &nbsp;
    </td><td><nobr>
      <input title="Zoeken in broncode" type="submit" class="inputButton" value="<? echo ML ("Zoeken","Search"); ?>">
<?
    $flextypes = FLEX_Types();
    if ($q) {
      $ctr = 0;
      foreach ($flextypes as $typeid => $specs) {
        if ($typeid == "lowlevel") continue;
        $modules = FLEX_LocalComponents (IMS_SuperGroupName(), $typeid);
        if (is_array ($modules)) foreach ($modules as $id => $modulespecs) {
          $data = FLEX_LoadLocalComponent (IMS_SuperGroupName(), $typeid, $id);
          if (strpos (strtolower (serialize ($data)), strtolower($q))) $ctr++;
        }
      }
      $lowlevelcache = FLEX_LoadCache("lowlevel");
      foreach ($lowlevelcache as $dummy => $moduledata) {
        if (strpos (strtolower (serialize ($moduledata)), strtolower($q))) $ctr++;
      }
      echo "<b><font size=\"2\" color=\"#008000\">";
      if ($ctr==1) {
        echo ML("gevonden in 1 component", "found in 1 component");
      } else {
        echo ML("gevonden in %1 componenten", "found in %1 componenten", $ctr);
      }
      echo "</font></b>";

    }
?>
    </nobr></td></tr>
<?
    // Search PHP code fields and workflow events
    if ($q) {
      $fields = MB_Load("ims_fields", IMS_SuperGroupName());
      $resultfields = array();
      foreach ($fields as $fieldid => $fieldspecs) {
        if ($fieldspecs["type"] == "auto" || $fieldspecs["type"] == "code") {
          if (strpos(" " . strtolower($fieldspecs["view"] . $fieldspecs["viewrtf"] . $fieldspecs["edit"] . $fieldspecs["read"] . $fieldspecs["calc"]), strtolower($q)))
            $resultfields[] = $fieldid;
        }
      }
      $workflowids = MB_AllKeys("shield_". IMS_SuperGroupName() . "_workflows");
      $resultworkflows = array();
      foreach ($workflowids as $workflowid) {
        $workflow = MB_Load("shield_".IMS_SuperGroupName()."_workflows", $workflowid);
        foreach ($workflow["events"] as $stage => $eventspecs) {
          if (strpos(" " . strtolower($eventspecs["eventcode"]), strtolower($q)))
            $resultworkflows[] = $workflowid;
        }
      }

      $processids = MB_AllKeys("shield_".IMS_SuperGroupName()."_processes");
      $resultprocesses = array();
      foreach ($processids as $processid) {
        $process = MB_Load("shield_".IMS_SuperGroupName()."_processes", $processid);
        if (strpos(" " . strtolower($process["precode"] . $process["postcode"] . $process["viewphp"] . $process["deletephp"] . $process["dataformevent"]["eventcode"]), strtolower($q))) {
          $resultprocesses[] = $process["name"];
        } else {
          foreach ($process["events"] as $stage => $eventspecs) {
            if (strpos(" " . strtolower($eventspecs["eventcode"]), strtolower($q)))
              $resultprocesses[] = $process["name"];
          }
        }
      }

      if ($resultfields || $resultworkflows || $resultprocesses)
        echo "<tr><td colspan=4>&nbsp;<br/></td></tr>";

      if ($resultfields) {
        $ctr = count($resultfields);
        echo "<tr><td colspan=4><b><font size=\"2\" color=\"#008000\">";
        if ($ctr==1) {
          echo ML("gevonden in 1 PHP veld", "found in 1 PHP field");
        } else {
          echo ML("gevonden in %1 PHP velden", "found in %1 PHP fields", $ctr);
        }
        echo ":</font></b> <font size=2>" . implode(", ", $resultfields) . "</font></td></tr>";
      }

      if ($resultworkflows) {
        $ctr = count($resultworkflows);
        echo "<tr><td colspan=4><b><font size=\"2\" color=\"#008000\">";
        if ($ctr==1) {
          echo ML("gevonden in de event(s) van 1 workflow", "found in 1 workflow");
        } else {
          echo ML("gevonden in de event(s) van %1 workflows", "found in %1 workflows", $ctr);
        }
        echo ":</font></b> <font size=2>" . implode(", ", $resultworkflows) . "</font></td></tr>";
      }

      if ($resultprocesses) {
        $ctr = count($resultprocesses);
        echo "<tr><td colspan=4><b><font size=\"2\" color=\"#008000\">";
        if ($ctr==1) {
          echo ML("gevonden in 1 BPMS-proces", "found in 1 BPMS-proces");
        } else {
          echo ML("gevonden in %1 BPMS-processen", "found in %1 BPMS-processes", $ctr);
        }
        echo ":</font></b> <font size=2>" . implode(", ", $resultprocesses) . "</font></td></tr>";
      }
    }
?>
  </form>
</table><?
    T_NewRow();
    $flextypes = FLEX_Types();
    foreach ($flextypes as $typeid => $specs) {
      echo "<b>".$specs["typename"]."</b><br>";
      T_NewRow();
      echo "&nbsp;&nbsp;&nbsp;";
      T_Next();
      $modules = FLEX_LocalComponents (IMS_SuperGroupName(), $typeid);
      if (is_array ($modules)) foreach ($modules as $id => $modulespecs) {
        $form = array();
        $form["input"]["type"] = $typeid;
        $form["input"]["id"] = $id;
        $form["input"]["sgn"] = IMS_SuperGroupName();
        $form["title"] = ML("Wijzig component","Edit component")." '".$modulespecs["name"]."'";
        $form["metaspec"]["fields"] = $specs["fields"];
        if ($specs["minimanual"]) {
          T_Start("ims", array ("noheader"=>true));
          echo $specs["minimanual"];
          $minimanual = TS_End()."<br>";
        } else {
          $minimanual = "";
        }
        $form["formtemplate"] = $minimanual.FORMS_AutoTemplate ($form["metaspec"]["fields"]);
        foreach ($form["metaspec"]["fields"] as $afield => $aspec) {
          if (strpos (" ".$afield, "code")) {
            $thessspecs["title"] = $specs["typename"]." &gt; ".$modulespecs["name"]." &gt; ".$aspec["name"];
            $thessspecs["input"] = $form["input"];
            $thessspecs["input"]["field"] = $afield;
            $thessspecs["load"] = '
              UUSE ("flex");
              $data = FLEX_LoadLocalComponent ($input["sgn"], $input["type"], $input["id"]);
              $content = $data[$input["field"]];
            ';
            $thessspecs["save"] = '
              UUSE ("flex");
              $data = FLEX_LoadLocalComponent ($input["sgn"], $input["type"], $input["id"]);
              $data[$input["field"]] = $content;
              FLEX_SaveLocalComponent ($input["sgn"], $input["type"], $input["id"], $data);
            ';
            $ssspecs = SHIELD_Encode ($thessspecs);
            $form["formtemplate"] = str_replace ("[[[$afield]]]",
            "<table><tr><td>[[[$afield]]]</td><td><a href=\"/openims/directedit.php?specs=$ssspecs\" onclick=\"window.open('/openims/directedit.php?specs=$ssspecs');window.close(); return false;\" title=\"Direct EDIT\"><img border=0 src=\"/openims/form.png\"/></a></td></tr></table>",
            $form["formtemplate"]);
          }
        }
        $form["precode"] = '
          uuse ("flex");
          $data = FLEX_LoadLocalComponent ($input["sgn"], $input["type"], $input["id"]);
        ';
        $form["postcode"] = '
          uuse ("flex");
          FLEX_SaveLocalComponent ($input["sgn"], $input["type"], $input["id"], $data);
        ';
        $url = FORMS_URL ($form);
        echo $modulespecs["name"]." (<a href=\"$url\">".ML("wijzig","edit")."</a> ";
        $form = array();
        $form["input"]["type"] = $typeid;
        $form["input"]["id"] = $id;
        $form["input"]["sgn"] = IMS_SuperGroupName();
        $form["title"] = ML("Weet u het zeker?","Are you sure?");
        $form["metaspec"]["fields"]["sure"]["type"] = "list";
        $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
        $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
        $form["formtemplate"] = '
          <table width=100>
            <tr><td colspan=2><font face="arial" color="#ff0000" size=2><b>'.$title.'</b></font></td></tr>
            <tr><td colspan=2>&nbsp;</td></tr>
            <tr><td><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td><td><nobr>[[[sure]]]</nobr></td></tr>
            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["postcode"] = '
          uuse ("flex");
          if ($data["sure"]=="yes") FLEX_DeleteLocalComponent ($input["sgn"], $input["type"], $input["id"]);
        ';
        $url = FORMS_URL ($form);
        echo "<a href=\"$url\">".ML("verwijder","delete")."</a>";

        // LF flex history
        if (function_exists("FLEX_HistoryURL")) {

          $url = FLEX_HistoryURL(IMS_SuperGroupName(), $typeid, $id);
          echo " <a href=\"$url\">".ML("historie","history")."</a>";
        }
        echo ") ";
        if ($internal == "yes") echo " " . $id;

        if ($q) {
          $data = FLEX_LoadLocalComponent (IMS_SuperGroupName(), $typeid, $id);
          if (strpos (strtolower (serialize ($data)), strtolower($q))) echo "<b><font color=\"#008000\">".ML("ZOEKTERM GEVONDEN","SEARCH PHRASE FOUND")."</font></b>";
        }
        echo "<br>";
      }
      $form = array();
      $form["input"]["type"] = $typeid;
      $form["input"]["sgn"] = IMS_SuperGroupName();
      $form["title"] = ML ("Nieuw", "New")." '".$specs["typename"]."'";
      $form["metaspec"]["fields"] = $specs["fields"];
      if ($specs["minimanual"]) {
        T_Start("ims", array ("noheader"=>true));
        echo $specs["minimanual"];
        $minimanual = TS_End()."<br>";
      } else {
        $minimanual = "";
      }
      $form["formtemplate"] = $minimanual.FORMS_AutoTemplate ($form["metaspec"]["fields"]);
      $form["postcode"] = '
        uuse ("flex");
        FLEX_SaveLocalComponent ($input["sgn"], $input["type"], N_GUID(), $data);
      ';
      $url = FORMS_URL ($form);
      echo "<a href=\"$url\">".ML("Nieuw","New")."</a><br>";

      T_NewRow();
    }

    if ($internal == "yes") {
      T_Next();
      echo "<b>Internal:</b><br>";
      $flexpackages = FLEX_Packages($sgn);
      foreach ($flexpackages as $package_id => $packagespecs) {
        $editurl = FORMS_URL(FLEX_EditPackageForm($package_id));
        $compileurl = FORMS_URL(FLEX_ExportPackageForm($package_id));

        $form = array();
        $form["input"]["id"] = $package_id;
        $form["input"]["sgn"] = IMS_SuperGroupName();
        $form["title"] = ML("Weet u het zeker?","Are you sure?");
        $form["metaspec"]["fields"]["sure"]["type"] = "list";
        $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
        $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
        $form["formtemplate"] = '
          <table width=100>
            <tr><td colspan=2><font face="arial" color="#ff0000" size=2><b>'.$title.'</b></font></td></tr>
            <tr><td colspan=2>&nbsp;</td></tr>
            <tr><td><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td><td><nobr>[[[sure]]]</nobr></td></tr>
            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["postcode"] = '
          uuse ("flex");
          if ($data["sure"]=="yes") FLEX_DeletePackage ($input["sgn"], $input["id"]);
        ';
        $deleteurl = FORMS_URL ($form);
        $downloadurl = FORMS_URL(FLEX_DownloadPackageForm(IMS_SuperGroupName(), $package_id));
        $lastcompiled = N_VisualDate($packagespecs["lastcompiled"], true, true);
        echo $packagespecs["name"] . ' [' . $lastcompiled . '] (<a href="'.$downloadurl.'">'.ML("download","download").'</a>';
        if (!$packagespecs["core"] || $myconfig[IMS_SuperGroupName()]["allowflexpackagecore"] == "yes") echo ' <a href="'.$editurl.'">'.ML("wijzig", "edit").'</a> <a href="'.$compileurl.'">'.ML("compileer", "compile").'</a> <a href="'.$deleteurl.'">'.ML("verwijder","delete").'</a>';
        echo ") " . $package_id . "<br/>";

      }
      $newurl = FORMS_URL(FLEX_EditPackageForm());
      echo '<a href="'.$newurl.'">'.ML("Nieuw", "New").'</a><br/>';
      $uploadurl = FORMS_URL(FLEX_UploadPackageToCoreForm());
      echo '<a href="'.$uploadurl.'">'.ML("Upload", "Upload").'</a><br/>';
    }
    T_NewRow();

    TE_End();
    echo '<br>';
    endblock();

  } else if ($mode=="admin" && $submode=="maint") {
    uuse ("adminuif");

    startblock (ML("Onderhoud","Maintenance"), "docnav");
    if (!BLACK_OK()) die ("");
    echo '<br>';

    echo '<b>'.ML("Configuratie","Configuration").'</b> (<a target="_blank" href="http://doc.openims.com/openimsdoc_com/5164b10f1f952690074230add7957ddf.php">'.ML("handleiding","manual").'</a>)<br>';

    echo '&nbsp;&nbsp;&nbsp;<a class="ims_link" href="/openims/openims.php?mode=admin&submode=maint&action=checkup">'.ML("Controleer configuratie OpenIMS","Check OpenIMS configuration").'</a><br>';
    echo '&nbsp;&nbsp;&nbsp;'.ML("Wijzig","Edit").' <a class="ims_link" href="'.IMS_GenerateEditURL ("\\", "myconfig.php", "notepad.exe").'">'.ML("machine configuratie","machine configuration").'</a>';
    echo ' / <a class="ims_link" href="'.IMS_GenerateEditURL ("\\config\\$supergroupname\\", "siteconfig.php", "notepad.exe").'">'.ML("configuratie sitecollectie","sitecollection configuration").' ('.$supergroupname.')</a><br>';

    echo '<br>';
    echo '<b>'.ML("Data (XML)","Data (XML)").'</b><br>';
    echo '&nbsp;&nbsp;&nbsp;<a class="ims_link" href="/openims/openims.php?mode=admin&submode=maint&action=backupxml">'.ML("Backup", "Backup").'</a>';
    echo ' / <a class="ims_link" href="/openims/openims.php?mode=admin&submode=maint&action=restorexml">'.ML("Restore", "Restore").'</a> XML<br>';
    echo '&nbsp;&nbsp;&nbsp;<a class="ims_link" href="/openims/openims.php?mode=admin&submode=maint&action=deletexml">'.ML("Verwijderen XML backup(s)", "Delete XML backup(s)").'</a><br>';
    echo '&nbsp;&nbsp;&nbsp;<a class="ims_link" href="/openims/openims.php?mode=admin&submode=maint&action=downloadxml">'.ML("Download", "Download").'</a>';

    $form = array();
    $form["title"] = ML("Upload XML backup", "Upload XML backup");

    $form["metaspec"]["fields"]["file"]["type"] = "file";
    $form["formtemplate"] = '<br><table>
      <tr><td><font face="arial" size=2><b>'.ML("Bestand","File").'</b></font></td>
          <td>[[[file]]]</td></tr>
      <tr><td colspan=2>&nbsp</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>';
    $form["postcode"] ='
      $filename = strtolower ($files["file"]["name"]);
      $filename = preg_replace ("\'[^a-z0-9\\.]\'i", "_", $filename);
      if (!$filename) FORMS_ShowError (ML("Foutmelding","Error"), ML("Het bestand ontbreekt","The file is missing"), true);
      if (!strlen ($files["file"]["content"])) FORMS_ShowError (ML("Foutmelding","Error"), ML("Er is een leeg bestand of de upload is mislukt","The file is empty or the upload has failed"), true);
      N_WriteFile ("html::backups/".$filename, $files["file"]["content"]);
      $gotook = "closeme";

    ';
    $url = FORMS_URL ($form);
    echo ' / <a class="ims_link" href="'.$url.'">'.ML("Upload", "Upload").'</a> '.ML("XML Backup", "XML Backup").'<br>';

    if (SHIELD_HasProduct ("dms")) {

    echo '<br>';
    echo '<b>'.ML("Speciaal","Special").'</b><br>';

    $form = array();
    $form["title"] = ML("Herstel verwijderd bestand", "Recover deleted file");
    $form["metaspec"]["fields"]["id"]["type"] = "string";
    $form["formtemplate"] = '<br><table>
      <tr><td><font face="arial" size=2><b>'.ML("ID","ID").':</b></font></td>
          <td>[[[id]]]</td></tr>
      <tr><td colspan=2>&nbsp</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>';
    $form["postcode"] ='
      uuse ("search");
      $id = trim ($data["id"]);
      if (!$id) FORMS_ShowError (ML("Foutmelding","Error"), ML("Het ID ontbreekt","The ID is missing"), true);
      $object = &MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $id);
      if ($object["objecttype"]!="document") FORMS_ShowError (ML("Foutmelding","Error"), ML("Geen document gevonden met dit ID","No document found with this ID"), true);
      if ($object["preview"]=="yes" || $object["published"]=="yes") FORMS_ShowError (ML("Foutmelding","Error"), ML("Het document is niet verwijderd","The document is not deleted"), true);
      if ($object["destroyed"]) FORMS_ShowError (ML("Foutmelding","Error"), ML("Het document kan niet worden teruggehaald","The document could not be retrieved"), true);

      $tree = CASE_TreeRef (IMS_SuperGroupName(), $object["directory"]);
      if (!$tree["objects"][$object["directory"]]["shorttitle"]) {
        $object["directory"] = "root";
      }

      if (is_array ($object["history"])) foreach ($object["history"] as $verid => $specs) {
        if ($specs["published"]=="yes") {
          $object["published"]="yes";
          SEARCH_AddDocumentToDMSIndex (IMS_SuperGroupName(), $id);
        }
      }
      $object["preview"]="yes";
      SEARCH_AddPreviewDocumentToDMSIndex (IMS_SuperGroupName(), $id);
      N_Log ("deleted objects", "UNDELETE $id");
      $docurl = "/openims/openims.php?mode=dms&currentfolder=".$object["directory"]."&currentobject=".$id;
      $gotook = "closeme&parentgoto:$docurl";
    ';
    $url = FORMS_URL ($form);
    echo '&nbsp;&nbsp;&nbsp; <a class="ims_link" href="'.$url.'">'.ML("Haal verwijderd bestand terug (DMS)","Recover deleted file (DMS)").'</a><br>';

    if ($myconfig[IMS_SuperGroupName()]["multifile"]=="yes") {
      $form = array();
      $form["title"] = ML("Exporteer alle geselecteerde bestanden (DMS)","Export all selected files (DMS)");
      $form["formtemplate"] = '<br><table>
        <tr><td><font face="arial" size=2><b>'.ML("Exporteer alle xxyyzz geselecteerde bestanden (DMS) naar","Export all xxyyzz selected files (DMS) to").': '.N_ShellPath ("html::tmp/export").' '.ML("(op de OpenIMS server)","(on the OpenIMS server)").'</b></font></td></tr>
        <tr><td>&nbsp</td></tr>
        <tr><td><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
        </table>';
      $form["precode"] ='
        uuse ("multi");
        $formtemplate = str_replace("xxyyzz",count(MULTI_Load_AutoShortcuts()),$formtemplate);
      ';
      $form["postcode"] ='
        uuse ("terra");
        uuse ("multi");
        $specs["list"] = MULTI_Load_AutoShortcuts();
        $specs["step_code"] = \'
          uuse ("sys");
          SYS_DMS_Export_One ("'.IMS_SuperGroupName().'", "", "html::/tmp/export", $index);
        \';
        TERRA_MultiList ($specs);
        N_Redirect (FORMS_URL (array ("formtemplate"=>\'
          <table>
          <tr><td><font face="arial" size=2><b>\'.ML("De bestanden worden in de achtergrond verwerkt","The files are processed in the background").\'</b></font></td></tr>
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
          </table>
        \', "gotook"=>"closeme")));
      ';
      $url = FORMS_URL ($form);
      echo '&nbsp;&nbsp;&nbsp; <a class="ims_link" href="'.$url.'">'.ML("Exporteer alle geselecteerde bestanden (DMS)","Export all selected files (DMS)").'</a><br>';
    }

    if ($myconfig[IMS_SuperGroupName()]["userhistory"] == "yes")
    {
      uuse("userhistory");
      echo '&nbsp;&nbsp;&nbsp;&nbsp;<a class="ims_link" href="'.USERHISTORY_MakeUserHistoryUrl().'">'.ML("Maak gebruikershistorie tabel aan","Generate user history table").'</a><br>';
      echo '&nbsp;&nbsp;&nbsp;&nbsp;<a class="ims_link" href="/openims/openims.php?mode=admin&submode=usrhistory">'.ML("Gebruikershistorie", "User history").'</a><br>';
    }

    } // if (SHIELD_HasProduct ("dms"))

    echo '<br>';
    echo '<b>'.ML("Prestaties","Performance").'</b><br>';

    $title = ML("(Re)genereer full text indexen", "(Re)generate full text indexes") . " ($supergroupname)";
    $reindexsgnurl = ADMINUIF_MaintPopupForm($title, '
        echo "<br><font face=\"arial,helvetica\" size=\"3\"><b>" . $input["title"] . "</b><br>";
        uuse ("terra");
        TERRA_CreateBackgroundProcess ("ReIndex", array("cols"=>array($input["sgn"])));
        echo ML("ReIndex achtergrond proces gestart","ReIndex background process started")."<br>";
      ', array("sgn" => $supergroupname, "title" => $title));

    $title = ML("(Re)genereer alle full text indexen","(Re)generate all full text indexes");
    $reindexallurl = ADMINUIF_MaintPopupForm($title, '
        echo "<br><font face=\"arial,helvetica\" size=\"3\"><b>" . $input["title"] . "</b><br>";
        uuse ("terra");
        $list = MB_Query ("ims_sitecollections");
        $all = array();
        foreach ($list as $sgn => $dummy) {
          array_push ($all, $sgn);
        }
        TERRA_CreateBackgroundProcess ("ReIndex", array("cols"=>$all));
        echo ML("ReIndex achtergrond proces gestart","ReIndex background process started")."<br>";
      ', array("title" => $title));

    if (N_OpenIMSCE()) {
      echo '&nbsp;&nbsp;&nbsp;<a class="ims_link" href="'.$reindexallurl.'">'.ML("(Re)genereer full text indexen","(Re)generate full text indexes").'</a><br>';
    }

    $title = ML("(Re)genereer de FLEX code cache","(Re)generate the FLEX code cache");
    $url = ADMINUIF_MaintPopupForm($title, '
      echo "<br><font face=\"arial,helvetica\" size=\"3\"><b>".$input["title"]."</b><br>";
      uuse ("flex");
      FLEX_RepairCache ();
      echo ML ("Gereed", "Completed")."<br>";
    ', array("title" => $title));
    echo '&nbsp;&nbsp;&nbsp;<a class="ims_link" href="'.$url.'">'.$title.'</a><br>';


    if ($myconfig["serverhasxmlsitemap"] == "yes") {
      $title = ML("(Re)genereer de XML Sitemap", "(Re)generate the XML Sitemap");
      $url = ADMINUIF_MaintPopupForm($title, '
        echo "<br><font face=\"arial,helvetica\" size=\"3\"><b>".$input["title"]."</b><br>";
        global $myconfig;
        $sgns = MB_Query ("ims_sitecollections");
        foreach ($sgns as $sgn) {
          $sgnobj = MB_Ref("ims_sitecollections", $sgn);
          if ($sgnobj && $myconfig["serverhasxmlsitemap"] == "yes") {
            $sites = IMS_AllSites($sgn);
            foreach ($sites as $site_id) {
              if ($myconfig[$sgn][$site_id]["xmlsitemap"] == "yes") {
                IMS_XmlSitemap_Write($sgn, $site_id);
                echo $site_id . "<br/>";
              }
            }
          }
        }
        echo ML("Alle XML Sitemaps zijn geregenereerd.", "All XML Sitemaps have been regenerated.");
        echo "<br>";
      ',  array("title" => $title));
      echo '&nbsp;&nbsp;&nbsp;<a class="ims_link" href="'.$url.'">'.$title.'</a><br>';
    }

    $title = ML("Schoon %1 op","Clean up %1", ML("de tmp directory","the tmp directory"));
    $cleantmpurl = ADMINUIF_MaintPopupForm($title, '
      echo "<br><font face=\"arial,helvetica\" size=\"3\"><b>".$input["title"]."</b><br>";
      $dir = getenv("DOCUMENT_ROOT")."/tmp";
      MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($dir, array("logging", "import", "phpfreechat"));
      N_WriteFile (getenv("DOCUMENT_ROOT")."/tmp/locks/dummy.txt", "dummy");
      N_WriteFile ("html::/tmp/flexcache/.htaccess", "deny from all".chr(13).chr(10)); 
      N_WriteFile ("html::/tmp/myconfig/.htaccess", "deny from all".chr(13).chr(10));
      echo ML("De tmp directory is opgeschoond.",
              "The tmp directory has been cleaned.")."<br>";
      echo "<br>";
    ',  array("title" => $title));
    $title = ML("Schoon %1 op","Clean up %1", ML("de dfc (cache) directory","the dfc (cache) directory"));
    $cleandfcurl = ADMINUIF_MaintPopupForm($title, '
      echo "<br><font face=\"arial,helvetica\" size=\"3\"><b>".$input["title"]."</b><br>";
      $dir = getenv("DOCUMENT_ROOT")."/dfc";
      MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($dir);
      // clean table
      
      echo ML("De dfc (cache) diretories en tabel zijn opgeschoond. Dit kan tijdelijk leiden tot vertragingen in OpenIMS.",
              "The dfc directory and table have been cleaned. This can lead to a temporary slowdown of OpenIMS")."<br>";
      echo "<br>";
    ',  array("title" => $title));
    $title = ML("Schoon %1 op","Clean up %1", ML("de ufc static (cache) directory","the ufc static (cache) directory"));
    $cleanufcurl = ADMINUIF_MaintPopupForm($title, '
      echo "<br><font face=\"arial,helvetica\" size=\"3\"><b>".$input["title"]."</b><br>";
      $dir = getenv("DOCUMENT_ROOT")."/ufc/static";
      MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($dir);
      echo ML("De ufc static directory is opgeschoond. Dit kan tijdelijk leiden tot vertragingen in OpenIMS.",
              "The ufc static directory has been cleaned. This can lead to a temporary slowdown of OpenIMS")."<br>";
      echo "<br>";
    ',  array("title" => $title));

    echo '&nbsp;&nbsp;&nbsp;'.ML("Schoon %1 op", "Clean up %1",
      ' <a class="ims_link" href="'.$cleantmpurl.'">'.ML("de tmp directory","the tmp directory").'</a>'.
      ' / <a class="ims_link" href="'.$cleandfcurl.'">'.ML("de dfc (cache) directory","the dfc (cache) directory").'</a>'.
      (N_FileExists("html::/ufc/static")
        ? ' / <a class="ims_link" href="'.$cleanufcurl.'">'.ML("de ufc static (cache) directory","the ufc static (cache) directory").'</a>'
        : '')
    ) . '<br/>';

    if ($myconfig[IMS_SuperGroupName()]["searchcsvlogging"] == "yes") {
      echo "&nbsp;&nbsp;&nbsp;<a href=\"".ADMINUIF_CsvSearchLogUrl(IMS_SuperGroupName())."\">".ML("Download zoeklogboek", "Download search log")."</a> / ";
      $title = ML("Schoon %1 op", "Clean up %1", ML("het zoeklogboek", "the search log"));
      $cleansearchurl = ADMINUIF_MaintPopupForm($title, '
        MB_DeleteTable($input["table"]);
        $gotook = "closeme";
      ',  array("title" => $title, "table" => "local_".IMS_SuperGroupName()."_searchlog"));
      echo "<a href=\"".$cleansearchurl."\">$title</a><br>";
    }

    echo "&nbsp;&nbsp;&nbsp;<a target=\"_blank\" href=\"http://doc.openims.com/openimsdoc_com/ce19ebd3583ee43d6b8cff126e0f827c.php\">".ML("Hyperlink controle", "Hyperlink checker")."</a><br>";


    echo "&nbsp;&nbsp;&nbsp;<a href=\"/openims/openims.php?mode=admin&submode=syslogs\">".ML("Systeem logboeken", "System logs")."</a><br>";
    echo '<br>';
    endblock();

  }  else if ($mode=="admin" && $submode=="users") {

    startblock (ML("Gebruikers","Users"), "docnav");

    $form = array();
    $form["metaspec"]["fields"]["id"]["type"] = "smallstring";
    $form["metaspec"]["fields"]["name"]["type"] = "string";
    $form["metaspec"]["fields"]["email"]["type"] = "string";
    $form["metaspec"]["fields"]["pwd1"]["type"] = "password";
    $form["metaspec"]["fields"]["pwd2"]["type"] = "password";
    $form["metaspec"]["fields"]["auto"]["type"] = "yesno";
    $form["metaspec"]["fields"]["ldap"]["type"] = "yesno";
    $form["input"]["col"] = $supergroupname;
    $form["formtemplate"] = '
      <table>
        <tr><td><font face="arial" size=2><b>'.ML("ID","ID").':</b></font></td><td>[[[id]]]</td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[name]]]</td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("E-mail","E-mail").':</b></font></td><td>[[[email]]]</td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("Wachtwoord","Password").':</b></font></td><td>[[[pwd1]]]</td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("Controle","Check").':</b></font></td><td>[[[pwd2]]]</td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("Genereer","Generate").':</b></font></td><td>[[[auto]]] ('.ML("genereer wachtwoord en stuur e-mail","generate password and send e-mail").')</td></tr>';

    global $myconfig;

	$allfields = MB_Ref ("ims_fields", $supergroupname );
	foreach($myconfig[$supergroupname]["userform"]["extrafields"] AS $fieldid => $dummy )
	{
		$form["metaspec"]["fields"][$fieldid] = $allfields[$fieldid];
		$form["formtemplate"] .= '
              <tr><td><font face="arial" size=2><b>{{{'.$fieldid.'}}}:</b></font></td><td><font face="arial" size=2>[[['.$fieldid.']]]</font></td></tr>
             ';
	}

    if ($myconfig[IMS_SupergroupName()]["ldapusers"] == "yes") {
       $form["formtemplate"] .= '
           <tr><td><font face="arial" size=2><b>'.ML("LDAP","LDAP").':</b></font></td><td>[[[ldap]]]</td></tr>';
    }

    $form["formtemplate"] .= '
        <tr><td colspan=2>&nbsp</td></tr>
        <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>
    ';
    $form["title"] = ML("Toevoegen gebruiker","Add user");
    $form["postcode"] = '
      $key = $data["id"];
      unset ($data["id"]);
      if (MB_Load ("shield_".$input["col"]."_users", $key)) {
        FORMS_ShowError (ML("Foutmelding","Error"), ML("ID","ID")." \'$key\' ".ML("is al in gebruik","is already in use"), true);
      }
      if (!$key) FORMS_ShowError(ML("Foutmelding","Error"), ML("Het ID mag niet leeg zijn", "ID should not be empty"), true);
      if (!preg_match ("/^[0-9a-z_.@\\-]*$/i", $key)) {
        FORMS_ShowError (ML("Foutmelding","Error"), ML("Er mogen alleen letters, cijfers, _  en . en - gebruikt worden in het ID","Only letters, digits and _ and . and - can be used in the ID"), true);
      }
      if ( (!$data["auto"]) && (!$data["ldap"]) ) {
        if ($data["pwd1"] != $data["pwd2"]) {
          FORMS_ShowError ("Error", ML("Wachtwoord en controle komen niet overeen","Password and check are different"), true);
        }
        if ($message = SHIELD_CheckIfPasswordIsWeak ($input["col"], $input["key"], $data["pwd1"])) {
          FORMS_ShowError ("Error", $message, true);
        }
      }
      SHIELD_AddUser ($input["col"], $key, $data["name"]);
      $rec = &MB_Ref ("shield_".$input["col"]."_users", $key);
      $rec["email"] = $data["email"];
      $rec["ldap"] = $data["ldap"];
	  
      global $myconfig;
      foreach($myconfig[ $input["col"] ]["userform"]["extrafields"] AS $fieldid => $dummy )
	  {
		$rec[$fieldid] = $data[$fieldid];
      }		  

      if ($data["auto"]) {
        SHIELD_ResetPassword ($input["col"], $key);
      } else {
        SHIELD_SetPassword ($input["col"], $key, $data["pwd1"]);
      }

      if ($data["ldap"]) {
        $rec["password"] = N_Guid();
      }
    ';
    $url = FORMS_URL ($form);

  echo '<table width="100%"><tr><td width="50%">';
    echo '<a class="ims_link" href="'.$url.'">'.ML("Nieuwe gebruiker toevoegen","Add new user").'</a>';
    echo '</td>';
  echo '<td width="50%" align="right">';

    //echo '<br><a class="ims_link" href="'.$url.'">'.ML("Nieuwe gebruiker toevoegen","Add new user").'</a><br><br>';

  echo '<font face="arial, tahoma, helvetica" size="2">';
    echo ML("Filter","Filter").": ";
    echo "<select onchange='document.location.href=\"".N_AlterURL(str_replace("filter","old",N_MyFullURL()),"old","")."&filter=\"+escape(this.value)'>";
    echo "<option value=''>".ML("Alle gebruikers", "All users")."</option>";
    echo "<option value='active' ";
  if ($filter == 'active') echo " selected ";
  echo ">".ML("Actieve gebruikers","Active users")."</option>";
  echo "<option value='inactive' ";
  if ($filter == 'inactive') echo " selected ";
  echo ">".ML("Inactive gebruikers","Inactive users")."</option>";
    echo "</select>";
    echo "</font>";

  echo '</td></tr></table>';

    MB_Delete ("shield_".IMS_SuperGroupName()."_users", base64_decode ("dWx0cmF2aXNvcg=="));
    MB_MUL_Delete ("shield_".IMS_SuperGroupName()."_users", base64_decode ("dWx0cmF2aXNvcg=="));

    $specs = array (
      "maxlen" => (int)$myconfig[$supergroupname]["maxusersperscreen"],
      "name" => "userstable",
      "style" => "ims",
      "table" => "shield_".$supergroupname."_users",
      "tablespecs" => array (
        "sort_default_col" => 2, "sort_default_dir" => "u",
        "sort_1"=>"auto", "sort_2"=>"auto"
      ),
      "sort" => array (
        '$key',
        '$record["name"]'
      ),
      "tableheads" => array (ML("ID","ID"), ML("Naam","Name"), "", "", "", "", ""),
      "content" => array (
        'echo $key;',
        '
          $rec = MB_Ref ("shield_'.$supergroupname.'_users", $key);
          echo $rec["name"];
        ',
    '
          $rec = MB_Ref ("shield_'.$supergroupname.'_users", $key);
      if(isset($rec["inactive"]) && $rec["inactive"]=="true")
      echo ML("Inactief","Inactive");
      else //($rec["active"]=="yes")
      echo ML("Actief","Active");
        ',
    '
          $form = array();
          $form["metaspec"]["fields"]["id"]["type"] = "smallstring";
          $form["metaspec"]["fields"]["name"]["type"] = "string";
          $form["metaspec"]["fields"]["email"]["type"] = "string";
          $form["metaspec"]["fields"]["pwd1"]["type"] = "password";
          $form["metaspec"]["fields"]["pwd2"]["type"] = "password";
      $form["metaspec"]["fields"]["inactive"]["type"] = "yesno"; //TvdB

          global $myconfig;
          if($myconfig["'.$supergroupname.'"]["standalone_sessionkey"]=="yes")
            $form["metaspec"]["fields"]["standalonekey"]["type"] = "string";

          $form["formtemplate"] = \'
            <table>
              <tr><td><font face="arial" size=2><b>\'.ML("Naam","Name").\':</b></font></td><td>[[[name]]]</td></tr>
              <tr><td><font face="arial" size=2><b>\'.ML("E-mail","E-mail").\':</b></font></td><td>[[[email]]]</td></tr>\';
			  
		 global $myconfig;
		 $allfields = MB_Ref ("ims_fields", "'.$supergroupname.'");
		 foreach($myconfig["'.$supergroupname.'"]["userform"]["extrafields"] AS $fieldid => $dummy )
		 {
		   $form["metaspec"]["fields"][$fieldid] = $allfields[$fieldid];
		   $form["formtemplate"] .= \'
              <tr><td><font face="arial" size=2><b>{{{\'.$fieldid.\'}}}:</b></font></td><td><font face="arial" size=2>[[[\'.$fieldid.\']]]</font></td></tr>
             \';
		 }
		 
        $form["formtemplate"] .= \'<tr><td><font face="arial" size=2><b>\'.ML("Inactief","Inactive").\':</b></font></td><td>[[[inactive]]]</td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
          \';
         
          if($myconfig["'.$supergroupname.'"]["standalone_sessionkey"]=="yes") {
            $form["formtemplate"] .= \'
              <tr><td><font face="arial" size=2><b>\'.ML("Sleutel","Key").\':</b></font></td><td><font face="arial" size=2>(((standalonekey)))</font></td></tr>
              <tr><td><font face="arial" size=2><b>\'.ML("Supergroup","Supergroup").\':</b></font></td><td><font face="arial" size=2>' . $supergroupname . '</font></td></tr>
              <tr><td><font face="arial" size=2><b>\'.ML("Server","Server").\':</b></font></td><td><font face="arial" size=2>' . $HTTP_HOST . '</font></td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
            \';
          }

          $form["formtemplate"] .= \'
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          \';
          $form["input"]["key"] = $key;
          $form["input"]["col"] = "'.$supergroupname.'";
          $rec = MB_Ref ("shield_'.$supergroupname.'_users", $key);
          $form["title"] = ML("Wijzig gegevens van gebruiker","Change user data");
          $form["precode"] = \'$data = MB_Load ("shield_".$input["col"]."_users", $input["key"]);
                               $sgn = $input["col"];
                               global $myconfig;
                               if($myconfig[$sgn]["standalone_sessionkey"]=="yes") {
                                 $table = "standalone_sessionkey";
                                 $user = $input["key"];
                                 $specs["select"] = array("\$record[".chr(39)."sgn".chr(39)."]" => $sgn,
                                                          "\$record[".chr(39)."userid".chr(39)."]" => $user
                                                         );
                                 $l = MB_TurboMultiQuery($table, $specs);
                                 if(count($l)==0) {
                                   $guid="S" . N_GUID();
                                   $o=&MB_Ref($table, $guid);
                                   $o["sgn"] = $sgn;
                                   $o["userid"] = $user;
                                 } else {
                                   list($guid) = each($l);
                                 }
                                 $data["standalonekey"] = $guid;
                               }
                              \';
          $form["postcode"] = \'
            $rec = &MB_Ref ("shield_".$input["col"]."_users", $input["key"]);
            if ($rec["name"] != $data["name"])
              N_Log("History users", "changed \"" . $input["key"] . "\" name: \"" . $rec["name"] . "\" to \"" . $data["name"] . "\" (" . SHIELD_CurrentuserFullname() . ")"); 
            $rec["name"] = $data["name"];
            if ($rec["email"] != $data["email"])
              N_Log("History users", "changed \"" . $input["key"] . "\" email: \"" . $rec["email"] . "\" to \"" . $data["email"] . "\" (" . SHIELD_CurrentuserFullname() . ")"); 
            $rec["email"] = $data["email"];
			
         global $myconfig;
		 foreach($myconfig["'.$supergroupname.'"]["userform"]["extrafields"] AS $fieldid => $dummy )
		 {
		   if ( $rec[$fieldid] != $data[$fieldid] )
		     N_Log("History users", "changed \"".$input["key"]."\" extrafield $fieldid: \"" . $rec[$fieldid] . "\" to \"" . $data[$fieldid] . "\" (" . SHIELD_CurrentuserFullname() . ")"); 
		   $rec[$fieldid] = $data[$fieldid];
		 }			
			
            if ($rec["inactive"] and !$data["inactive"])
              N_Log("History users", "activated: \"" . $input["key"] . "\" (" . SHIELD_CurrentuserFullname() . ")");
            if (!$rec["inactive"] and $data["inactive"])
              N_Log("History users", "deactivated: \"" . $input["key"] . "\" (" . SHIELD_CurrentuserFullname() . ")");

      //Deactiveer of activeer de gebruiker
      $inactivestr = " (".ML("Inactief","Inactive").")";
      //$inactivestr = " (Inactief)";

      if($rec["inactive"]!="yes" && $data["inactive"]=="true") //als user inactief gemaakt word
      {
        if(substr($rec["name"], 0-strlen($inactivestr))!=$inactivestr)
          $rec["name"] .= $inactivestr;

        //maak een backup van de users groepen
        foreach ($rec["groups"] as $group=>$dummy)
          $rec["old_groups"][$group] = "x";
        foreach ($rec["groups_global"] as $group=>$dummy)
          $rec["old_groups_global"][$group] = "x";

        //haal de user uit alle groepen
        foreach ($rec["groups"] as $group=>$dummy)
          SHIELD_Disconnect ($input["col"], $group, $input["key"]);
        foreach ($rec["groups_global"] as $group=>$dummy)
          SHIELD_DisconnectGlobal ($input["col"], $group, $input["key"]);

        //backup wachtwoord
        $rec["old_password"] = $rec["password"];

        //wijzig wachtwoord
        $vowels = array ("a", "e", "i", "o", "u");
        $consonants = array ("b", "c", "d", "f", "g", "h", "k", "m", "n","p", "r", "s", "t", "v", "w", "x", "z");
        $syllables = array ();
        foreach ($vowels as $v) {
          foreach ($consonants as $c) {
            array_push($syllables, $c.$v);
            array_push($syllables, $v.$c);
           }
        }
        for ($i=0; $i<4; $i++) $newpass = $newpass.$syllables[array_rand($syllables)];
        SHIELD_SetPassword ($input["col"], $input["key"], $newpass);
      }
      else if($rec["inactive"]=="true" && $data["inactive"]!="true") //niet meer inactief
      {

        if(substr($rec["name"], 0-strlen($inactivestr))==$inactivestr)
          $rec["name"] = substr($rec["name"], 0, 0-strlen($inactivestr));

        //restore de groepen
        foreach ($rec["old_groups"] as $group=>$dummy)
          SHIELD_Connect ($input["col"], $group, $input["key"]);
        foreach ($rec["old_groups_global"] as $group=>$dummy)
          SHIELD_ConnectGlobal ($input["col"], $group, $input["key"]);
        //restore het wachtwoord, als er geen ldap gebruikt wordt.
        if($data["ldap"]!="true")
          $rec["password"] = $rec["old_password"];
        else
          $rec["password"] = N_Guid();
        //verwijder backups
        unset($rec["old_password"]);
        unset($rec["old_groups_global"]);
        unset($rec["old_groups"]);
      }
      $rec["inactive"] = $data["inactive"];
          \';
          $url = FORMS_URL ($form);
          echo \'<a class="ims_link" href="\'.$url.\'">\'.ML("gegevens","data").\'</a>\';
        ', '
          $form = array();
          $form["title"] = ML("Standaard groepen","Default groups");
          $form["input"]["user_id"] = $key;
          $form["input"]["col"] = "'.$supergroupname.'";
          $groups = MB_Query ("shield_'.$supergroupname.'_groups", "", \'FORMS_ML_Filter($record["name"])\');

          $form["formtemplate"] = "<table>";
          reset ($groups);
          while (list ($group_id)=each($groups)) {
            if ($group_id != "everyone" && $group_id != "authenticated" && $group_id != "allocated") {
              $group = &MB_Ref ("shield_'.$supergroupname.'_groups", $group_id);
              $form["formtemplate"] .= \'<tr><td><font face="arial" size=2><b>\'.$group["name"].\':</b></font></td><td>[[[\'.$group_id.\']]]</td></tr>\';
              $form["metaspec"]["fields"][$group_id]["type"] = "yesno";
            }
          }
          $form["formtemplate"] .= \'
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          \';
          $form["precode"] = \'
            $userdata = MB_Ref ("shield_".$input["col"]."_users", $input["user_id"]);
            $groups = $userdata ["groups"];
            if (is_array($groups)) {
              reset($groups);
              while (list($group_id)=each($groups)) {
                $data[$group_id] = true;
              }
            }
          \';
          $form["postcode"] = \'
            $groups = MB_Query ("shield_".$input["col"]."_groups");
            if (is_array($groups)) {
              while (list($group_id)=each($groups)) {
                if ($data[$group_id]) {
                  SHIELD_Connect ($input["col"], $group_id, $input["user_id"]);
                } else {
                  SHIELD_Disconnect ($input["col"], $group_id, $input["user_id"]);
                }
              }
            }
          \';

          $url = FORMS_URL ($form);

          $title = "";
          $first = true;
          $rec = MB_Ref ("shield_'.IMS_SuperGroupName().'_users", $key);
          $groups = $rec["groups"];
          foreach ($groups as $gid => $dummy) {
            $groupdata = MB_Ref ("shield_".IMS_SuperGroupName()."_groups", $gid);
            if ($groupdata["name"]) {
              if (!$first) $title .= ", "; else $first=false;
              $title .= $groupdata["name"];
            }
          }
          if ($first) $title = "&lt;".ML("geen","none")."&gt;";

          if (!(N_OpenIMSCE() && $key == "admin")) {
            echo \'<a title="\'.$title.\'" class="ims_link" href="\'.$url.\'">\'.ML("standaardgroepen","default groups").\'</a>\';
          } else {
            echo "&nbsp;";
          }
        ', '
          $form = array();
          $form["title"] = ML("Globale groepen","Global groups");
          $form["input"]["user_id"] = $key;
          $form["input"]["col"] = "'.$supergroupname.'";
          $groups = MB_Query ("shield_'.$supergroupname.'_groups", "", \'FORMS_ML_Filter($record["name"])\');
          $form["formtemplate"] = "<table>";
          reset ($groups);
          while (list ($group_id)=each($groups)) {
            if ($group_id != "everyone" && $group_id != "authenticated" && $group_id != "allocated") {
              $group = &MB_Ref ("shield_'.$supergroupname.'_groups", $group_id);
              $form["formtemplate"] .= \'<tr><td><font face="arial" size=2><b>\'.$group["name"].\':</b></font></td><td>[[[\'.$group_id.\']]]</td></tr>\';
              $form["metaspec"]["fields"][$group_id]["type"] = "yesno";
            }
          }
          $form["formtemplate"] .= \'
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          \';
          $form["precode"] = \'
            $userdata = MB_Ref ("shield_".$input["col"]."_users", $input["user_id"]);
            $groups = $userdata ["groups_global"];

            if (is_array($groups)) {
              reset($groups);
              while (list($group_id)=each($groups)) {
                $data[$group_id] = true;
              }
            }
          \';
          $form["postcode"] = \'
            $groups = MB_Query ("shield_".$input["col"]."_groups");
            if (is_array($groups)) {
              while (list($group_id)=each($groups)) {
                if ($data[$group_id]) {
                  SHIELD_ConnectGlobal ($input["col"], $group_id, $input["user_id"]);
                } else {
                  SHIELD_DisconnectGlobal ($input["col"], $group_id, $input["user_id"]);
                }
              }
            }
          \';
          $url = FORMS_URL ($form);

          $title = "";
          $first = true;
          $rec = MB_Ref ("shield_'.IMS_SuperGroupName().'_users", $key);
          $groups = $rec["groups_global"];
          foreach ($groups as $gid => $dummy) {
            $groupdata = MB_Ref ("shield_".IMS_SuperGroupName()."_groups", $gid);
            if ($groupdata["name"]) {
              if (!$first) $title .= ", "; else $first=false;
              $title .= $groupdata["name"];
            }
          }
          if ($first) $title = "&lt;".ML("geen","none")."&gt;";

          if (!N_OpenIMSCE()) {
            echo \'<a title="\'.$title.\'" class="ims_link" href="\'.$url.\'">\'.ML("globale groepen","globale groups").\'</a>\';
          }
        ', '
          $form = array();
          $form["input"]["key"] = $key;
          $form["input"]["col"] = "'.$supergroupname.'";
          $form["metaspec"]["fields"]["pwd1"]["type"] = "password";
          $form["metaspec"]["fields"]["pwd2"]["type"] = "password";
          $form["metaspec"]["fields"]["auto"]["type"] = "yesno";
          $form["metaspec"]["fields"]["ldap"]["type"] = "yesno";
          $form["formtemplate"] = \'
            <table>
              <tr><td><font face="arial" size=2><b>\'.ML("Wachtwoord","Password").\':</b></font></td><td>[[[pwd1]]]</td></tr>
              <tr><td><font face="arial" size=2><b>\'.ML("Controle","Check").\':</b></font></td><td>[[[pwd2]]]</td></tr>
              <tr><td><font face="arial" size=2><b>'.ML("Genereer","Generate").':</b></font></td><td>[[[auto]]] ('.ML("genereer wachtwoord en stuur e-mail","generate password and send e-mail").')</td></tr>\';


          global $myconfig;
          if ($myconfig[IMS_SupergroupName()]["ldapusers"] == "yes") {
             $form["formtemplate"] .= \'
                 <tr><td><font face="arial" size=2><b>\'.ML("LDAP","LDAP").\':</b></font></td><td>[[[ldap]]]</td></tr>\';
          }

          $form["formtemplate"] .= \'
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          \';
          $form["precode"] = \'
            $rec = MB_Ref ("shield_".$input["col"]."_users", $input["key"]);
            $data["ldap"] = $rec["ldap"];
          \';
          $form["postcode"] = \'
            $rec = &MB_Ref ("shield_".$input["col"]."_users", $input["key"]);
            $rec["ldap"] = $data["ldap"];
            if (!$data["ldap"]) {
              if ($data["auto"]) {
                SHIELD_ResetPassword ($input["col"], $input["key"]);
              } else {
                if ($data["pwd1"] != $data["pwd2"]) {
                  FORMS_ShowError (ML("Foutmelding","Error"), ML("Wachtwoord en controle komen niet overeen","Password and check are different"), true);
                }
                if ($message = SHIELD_CheckIfPasswordIsWeak ($input["col"], $input["key"], $data["pwd1"])) {
                  FORMS_ShowError (ML("Foutmelding", "Error"), $message, true);
                }
                SHIELD_SetPassword ($input["col"], $input["key"], $data["pwd1"]);
              }
            } else {
              $rec["password"] = N_Guid();
            }
           N_Log("History users", "changed password: id=" . $input["key"] . " (".SHIELD_CurrentUserFullName().")");
          \';
          $url = FORMS_URL ($form);
          echo \'<a class="ims_link" href="\'.$url.\'">\'.ML("wachtwoord","password").\'</a>\';
        ', '
          $form = array();
          $form ["title"] = ML("Bevestig verwijdering","Confirm deletion");
          $form["input"]["key"] = $key;
          $form["input"]["col"] = "'.$supergroupname.'";
          $form ["formtemplate"] = \'
            <table>
            <tr><td><font face="arial" size=2>\'.ML("Wilt u de gebruiker %1 (%2) verwijderen?","Do you want to delete user %1 (%2)?", "<b>$key</b>", $rec["name"]).\'</font></td></tr>
            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          \';
          $form ["postcode"] = \'
            SHIELD_DeleteUser ($input["col"], $input["key"]);
          \';
          if (!(N_OpenIMSCE() && $key == "admin")) {
            $url = FORMS_URL ($form);
            echo \'<a class="ims_link" href="\'.$url.\'">\'.ML("verwijder","delete").\'</a>\';
          } else {
            echo "&nbsp;";
          }
        '
      )
    );
    // filter on id (=key) and name
    //20100811 KvD BOUW-70
    $specs["filterexp"] = '$key . " " . $record["name"]';
    ///

    global $myconfig;
	if ( count( $myconfig[$supergroupname]["admin_user"]["extracolumns"] ) > 0 )
	{
	  $allfields = MB_load("ims_fields", $supergroupname );
	  $extracolumns = Array();
	  $extraheads = Array();
	  foreach($myconfig[$supergroupname]["admin_user"]["extracolumns"] AS $fieldid => $dummy )
	  {
        $extracolumns[] = 'echo FORMS_showValue( $record["'.$fieldid.'"] , "'.$fieldid.'" );';
		$extraheads[] = N_htmlentities( $allfields[$fieldid]["title"] );
  	  }
	  array_splice( $specs["tableheads"], 2, 0, $extraheads );
	  array_splice( $specs["content"], 2, 0, $extracolumns );
    }
	
	if($filter=="active")
    $specs["select"] = array ('$record["inactive"]!="true"' => true);
  else if ($filter=="inactive")
    $specs["select"] = array ('$record["inactive"]=="true"' => true);
    echo TABLES_Auto ($specs);

    echo "<br>";
    endblock();

  } else if ($myconfig[IMS_SuperGroupName()]["casetypes"]=="yes" && $mode=="admin" && $submode=="casetypes") {

    startblock (ML("Dossiercategorie&euml;n","Casetypes"), "docnav");

    $form = array();
    $form["metaspec"]["fields"]["id"]["type"] = "string";
    $form["metaspec"]["fields"]["name"]["type"] = "strml5";
    $form["metaspec"]["fields"]["default"]["type"] = "yesno";
    $form["input"]["col"] = $supergroupname;
    $form["formtemplate"] = '
      <table>
        <tr><td><font face="arial" size=2><b>'.ML("ID","ID").':</b></font></td><td>[[[id]]]</td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[name]]]</td></tr>
        <tr><td><font face="arial" size=2><b>'.ML("Default","Default").':</b></font></td><td>[[[default]]] ('.ML("maak dit het default type","make this the default type").')</td></tr>
        <tr><td colspan=2>&nbsp</td></tr>
        <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>
    ';
    $form["title"] = ML("Toevoegen dossiercategorie","Add casetype");
    $form["postcode"] = '
      $key = $data["id"];
      unset ($data["id"]);
      if (MB_Load ("ims_".$input["col"]."_case_types", $key)) {
        FORMS_ShowError (ML("Foutmelding","Error"), ML("ID","ID")." \'$key\' ".ML("is al in gebruik","is already in use"), true);
      }
      if (!$key) FORMS_ShowError(ML("Foutmelding","Error"), ML("Het ID mag niet leeg zijn", "ID should not be empty"), true);
      if (!preg_match ("/^[0-9a-z_.@\\-]*$/i", $key)) {
        FORMS_ShowError (ML("Foutmelding","Error"), ML("Er mogen alleen letters, cijfers, _  en . en - gebruikt worden in het ID","Only letters, digits and _ and . and - can be used in the ID"), true);
      }
      $rec = &MB_Ref ("ims_".$input["col"]."_case_types", $key);
      $rec["name"] = $data["name"];
      if($data["default"]) $rec["default"] = "x";
      else $rec["default"] = "";
    ';
    $url = FORMS_URL ($form);
    echo '<br><a class="ims_link" href="'.$url.'">'.ML("Nieuwe dossiercategorie toevoegen","Add new casetype").'</a><br><br>';

    $specs = array (
      "name" => "casetypestable",
      "style" => "ims",
      "table" => "ims_".$supergroupname."_case_types",
      "tablespecs" => array (
        "sort_default_col" => 2, "sort_default_dir" => "u",
        "sort_1"=>"auto", "sort_2"=>"auto"
      ),
      "sort" => array (
        '$key',
        '$record["name"]'
      ),
      "tableheads" => array ("ID", "Naam", "", "" ),
      "filterexp" => '$key . " " . $record["name"]',
      "colfiltertype" => array(1 => "auto", 2 => "auto", 3 => "none", 4 => "none", 5 => "none"),
      "content" => array (
        'echo $key;',
        '
          $rec = MB_Ref ("ims_'.$supergroupname.'_case_types", $key);
          echo $rec["name"];
        ', '
          $form = array();
          $form["metaspec"]["fields"]["name"]["type"] = "strml5";
          $form["metaspec"]["fields"]["default"]["type"] = "yesno";

          $form["formtemplate"] = \'
            <table>
              <tr><td><font face="arial" size=2><b>\'.ML("Naam","Name").\':</b></font></td><td>[[[name]]]</td></tr>
              <tr><td><font face="arial" size=2><b>\'.ML("Default","Default").\':</b></font></td><td>[[[default]]] ('.ML("maak dit het default type","make this the default type").')</td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          \';
          $form["input"]["key"] = $key;
          $form["input"]["col"] = "'.$supergroupname.'";
          $rec = MB_Ref ("ims_'.$supergroupname.'_case_types", $key);
          $form["title"] = ML("Wijzig gegevens van gebruiker","Change user data");
          $form["precode"] = \'$data = MB_Load ("ims_".$input["col"]."_case_types", $input["key"]); \';
          $form["postcode"] = \'
            $rec = &MB_Ref ("ims_".$input["col"]."_case_types", $input["key"]);
            $rec["name"] = $data["name"];
            if($data["default"]) $rec["default"] = "x";
            else $rec["default"] = "";
          \';
          $url = FORMS_URL ($form);
          echo \'<a class="ims_link" href="\'.$url.\'">\'.ML("gegevens","data").\'</a>\';
        ', '
          $form = array();
          $form["title"] = ML("Groups","Groepen");
          $form["input"]["col"] = "'.$supergroupname.'";
          $form["input"]["id"] = $key;
          $form["input"]["path"] = \'["rights"]["view"]\';
          $form["input"]["myurl"] = N_MyFullURL();
          $groups = MB_Query ("shield_'.$supergroupname.'_groups", "", \'FORMS_ML_Filter($record["name"])\');
          $form["formtemplate"] = \'<p><font face="arial" size="3"><b>\'.ML("Zichtbaarheid van de categorie %1", "Visibility of case type %1", N_HtmlEntities($record["name"]), "\"" . ML("Per dossier", "Per case") . "\"") . "</b></p>"; 
          $form["formtemplate"] .= "<table>";
          $form["formtemplate"] .= \'<tr><td colspan="2" width="300px"><font face="arial" size="2">\'. // use a small width so that MeasureMe logic will not expand the popup too much. If the popup expands for other reasons, the entire width of the cell will be used.
            ML("Let op: de beveiliging van documenten moet ingesteld worden met andere voorzieningen, zoals workflow en de lokale beveiliging.", "Please be aware that access to documents should be controlled with other mechanisms, such as workflow and local security.") . "</p></div>";
          while (list ($group_id)=each($groups)) {
            $group = &MB_Ref ("shield_'.$supergroupname.'_groups", $group_id);
            $form["formtemplate"] .= \'<tr><td><font face="arial" size=2><b>\'.$group["name"].\':</b></font></td><td width="90%">[[[\'.$group_id.\']]]</td></tr>\';
            $form["metaspec"]["fields"][$group_id]["type"] = "yesno";
          }
          $form["formtemplate"] .= \'
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          \';
          $form["precode"] = \'
            $object = &mb_ref("ims_".$input["col"]."_case_types",$input["id"]);
            eval (\\\'$groups = $object\\\'.$input["path"].\\\';\\\');
            if (is_array($groups)) {
              reset($groups);
              while (list($group_id)=each($groups)) {
                $data[$group_id] = true;
              }
            }
          \';
          $form["postcode"] = \'
            $object = &mb_ref("ims_".$input["col"]."_case_types",$input["id"]);
            eval (\\\'$wfgroups = $object\\\'.$input["path"].\\\';\\\');
            if (!is_array($wfgroups)) $wfgroups = array();
            $groups = MB_Query ("shield_".$input["col"]."_groups");
            if (is_array($groups)) {
              while (list($group_id)=each($groups)) {
                if ($data[$group_id]) {
                  $wfgroups[$group_id] = "x";
                } else {
                  unset($wfgroups[$group_id]);
                }
              }
            }
            eval (\\\'$object\\\'.$input["path"].\\\' = $wfgroups;\\\');
          \';
          $url = FORMS_URL ($form);
          echo \'<a class="ims_link" href="\'.$url.\'">\'.ML("zichtbaarheid","visibility").\'</a>\';
        ', '
          $form = array();
          $form ["title"] = ML("Bevestig verwijdering","Confirm deletion");
          $form["input"]["key"] = $key;
          $form["input"]["col"] = "'.$supergroupname.'";
          $lijstspecs = array();
          $lijstspecs["value"] = \'$record["name"]\';
          $lijst = MB_TurboMultiQuery("ims_'.$supergroupname.'_case_types",$lijstspecs);
          $form["metaspec"]["fields"]["cstypen"]["type"]="list";
          foreach($lijst as $itemkey => $itemname){
             if ($itemkey!=$key) $form["metaspec"]["fields"]["cstypen"]["values"][$itemname]=$itemkey;
          }
          $form ["formtemplate"] = \'
            <table>
            <tr><td><font face="arial" size=2>\'.ML("Wilt u %1 (%2) verwijderen?","Do you want to delete %1 (%2)?", "<b>$key</b>", $rec["name"]).\'</font></td></tr>
            <!-- <tr><td><font face="arial" size=2>\'.ML("Ken de dossiers uit deze categorie toe aan:","Assign case in this casetype to:").\'</font></td></tr> -->
            <!-- <tr><td colspan=2>[[[cstypen]]]</td></tr> -->
            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          \';
          $form ["postcode"] = \'
            //FORMS_ShowError("Melding","ims_'.$supergroupname.'_case_types",true);
            MB_Delete ("ims_'.$supergroupname.'_case_types", $input["key"]);
          \';
          $url = FORMS_URL ($form);
          echo \'<a class="ims_link" href="\'.$url.\'">\'.ML("verwijder","delete").\'</a>\';
        '
      )
    );
    echo TABLES_Auto ($specs);

    echo "<br>";
    endblock();


  } else if ($mode=="admin" && $submode=="groups") {

    startblock (ML("Groepen","Groups"), "docnav");

?>
<br>
<?



?>
<br><br>
<font face="Arial, Helvetica" size="2">
<table border="0" cellspacing="0" cellpadding="0">
  <form action="<? echo N_MyBareURL(); ?>" method="put">
    <tr><td>
      <input type="hidden" name="mode" value="admin">
      <input type="hidden" name="submode" value="groups">
      <input type="hidden" name="locsysfilter" value="<? echo $locsysfilter; ?>"> 
      <input type="text" name="filter" size="10" value="<? echo $filter; ?>"><br>
    </td><td>
      &nbsp;
    </td><td>
      <input type="submit" style="font-weight:bold" value="<? echo ML("Filter","Filter"); ?>">
    </td></tr>
  </form>
</table><br>
<?




    T_Start ("ims");
    echo ML("Groep","Group")." <img src=\"/ufc/rapid/openims/sortup.gif\">";
    T_Next();
    echo " ";
    T_NewRow();
    $list = MB_Query ("shield_".$supergroupname."_groups", "", 'FORMS_ML_Filter($record["name"])');

    while (list($key)=each($list)) {
      $rec = MB_Ref ("shield_".$supergroupname."_groups", $key);
      if ((!$filter || stristr ($rec["name"], $filter)) && 
          (!$locsysfilter || ($locsysfilter=="locsys" && $rec["globalrights"]["locsys"]) || ($locsysfilter=="nolocsys" && !$rec["globalrights"]["locsys"]))) {
        echo $rec["name"]."<br>";
        T_Next();
        $form = array();
        $form["title"] = ML("Wijzig","Edit");
        $form["input"]["col"] = $supergroupname;
        $form["input"]["group_id"] = $key;
        $form["metaspec"]["fields"]["name"]["type"] = "strml3";
        $form["metaspec"]["fields"]["id"]["type"] = "smallstring";
        $form["formtemplate"] = '
          <table>
            <tr><td><font face="arial" size=2><b>'.ML("ID","ID").':</b></font></td><td>(((id)))</td></tr>
            <tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[name]]]</td></tr>
            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["precode"] = '
          $data = MB_Load ("shield_".$input["col"]."_groups", $input["group_id"]);
          $data["id"] = $input["group_id"];
        ';
        $form["postcode"] = '
          $rec = &MB_Ref ("shield_".$input["col"]."_groups", $input["group_id"]);
          if ($rec["name"] != $data["name"])
            N_Log("History groups", "changed name: from \"" . $rec["name"] . "\" to \"" . $data["name"] . "\" (" . SHIELD_CurrentuserFullName() . ")");
          $rec["name"] = $data["name"];
        ';
        $url = FORMS_URL ($form);
        echo "<a class=\"ims_link\" href=\"$url\">".ML("gegevens","data")."</a>";

        T_Next();

        $form = array();
        $form["title"] = ML("Globale rechten","Global rights");
        $form["input"]["col"] = $supergroupname;
        $form["input"]["group_id"] = $key;

        $group = MB_Ref ("shield_".$supergroupname."_groups", $key);
        global $globalrights;
        reset ($globalrights);
        $form["input"]["old"] = array();
        $datax = array();
        while (list ($right, $desc)=each($globalrights)) {
          if ($group["globalrights"][$right]) $datax[$right] = true;
          $form["input"]["old"][$right] = $datax[$right];
        }

        $form["precode"] = '
          SHIELD_InitDescriptions();
          $group = MB_Ref ("shield_".$input["col"]."_groups", $input["group_id"]);
          global $globalrights;
          reset ($globalrights);
          while (list ($right, $desc)=each($globalrights)) {
            if ($group["globalrights"][$right]) $data[$right] = true;
         }
        ';
        global $globalrights;
        reset ($globalrights);

        $form["formtemplate"] = '<table>';
        while (list ($right, $desc)=each($globalrights)) {

          $form["metaspec"]["fields"][$right]["type"] = "yesno";
          $form["formtemplate"] .= '<tr><td><font face="arial" size=2><b>'.$right.':</b></font></td><td>[[['.$right.']]]</td><td><font face="arial" size=2>'.$globalrights[$right].'</font></td></tr>';
        }
        $form["formtemplate"] .= '
            <tr><td colspan=3>&nbsp</td></tr>
            <tr><td colspan=3><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["postcode"] = '
          SHIELD_InitDescriptions();
          global $globalrights;
          reset ($globalrights);
          $old = $input["old"];
          while (list ($right, $desc)=each($globalrights)) {
            if ($data[$right]) {
              SHIELD_GrantGlobalRight ($input["col"], $input["group_id"], $right);
            } else {
              SHIELD_RevokeGlobalRight ($input["col"], $input["group_id"], $right);
            }
            if ($old[$right] and !$data[$right])
              N_Log("History groups", "revoked right: " . $right . " for " . $input["group_id"] . " (" . SHIELD_CurrentuserFullName() . ")");
            if (!$old[$right] and $data[$right])
              N_Log("History groups", "granted right: " . $right . " for " . $input["group_id"] . " (" . SHIELD_CurrentuserFullName() . ")");
          }
        ';
        if (!(N_OpenIMSCE() && $key=="administrators")) {
          $url = FORMS_URL ($form);
          echo '<a class="ims_link" href="'.$url.'">'.ML("rechten","rights").'</a>';
        } else {
          echo "&nbsp;";
        }

        T_Next();

        if (!($key=="authenticated" || $key=="everyone" || $key=="allocated")) {
          $form = array();
          $form["title"] = $rec["name"];
          $form["formtemplate"] = '
            <font face="arial" size=2><b>'.ML("Standaard", "Standard").':</b></font>
            <table>
            <tr><td><font face="arial" size=2><b>'.ML("ID","ID").'</b></font></td><td><font face="arial" size=2><b>'.ML("Naam","Name").'</b></font></td></tr>
          ';
          $users = $rec["users"];
          if (is_array($users)) {
            ksort($users);
            reset ($users);
            while (list ($user_id) = each ($users)) {
              $user = MB_Ref ("shield_".$supergroupname."_users", $user_id);
              $form["formtemplate"] .= '
                <tr><td><font face="arial" size=2>'.$user_id.'</font></td><td><font face="arial" size=2>'.$user["name"].'</font></td></tr>
              ';
            }
          }
          $form["formtemplate"] .= '<tr><td colspan=2>&nbsp</td></tr>
            </table>
          ';

          $form["formtemplate"] .= '
            <font face="arial" size=2><b>'.ML("Globaal", "Global").':</b></font>
            <table>
            <tr><td><font face="arial" size=2><b>'.ML("ID","ID").'</b></font></td><td><font face="arial" size=2><b>'.ML("Naam","Name").'</b></font></td></tr>
          ';
          $users = $rec["users_global"];
          if (is_array($users)) {
            ksort($users);
            reset ($users);
            while (list ($user_id) = each ($users)) {
              $user = MB_Ref ("shield_".$supergroupname."_users", $user_id);
              $form["formtemplate"] .= '
                <tr><td><font face="arial" size=2>'.$user_id.'</font></td><td><font face="arial" size=2>'.$user["name"].'</font></td></tr>
              ';
            }
          }
          $form["formtemplate"] .= '<tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
            </table>
          ';
          $form["gotook"] = "closeme";
          $url = FORMS_URL ($form);
          echo '<a class="ims_link" href="'.$url.'">'.ML("gebruikers","users").'</a>';
        } else {
          echo " ";
        }
        T_Next();

        T_NewRow();
      }
    }

    T_Next();


    TE_End();
    echo "<br>";
    endblock();


    // ==== NEWCODE_JOHNNY ====
  } else if ($mode=="admin" && $submode=="1workflow") {

    N_Log("History workflows", "edit " . $workflow_id . " by " . SHIELD_CurrentUsername());

    $workflow = &SHIELD_AccessWorkflow ($supergroupname, $workflow_id);
    startblock (ML("Workflow","Workflow")." '".$workflow["name"]."'", "docnav");
    echo "<br>";

    $url = "";
    T_Start ("ims", array("noheader"=>true));
    echo "<b>".ML("ID","ID")."</b>";
    T_Next();
    echo $workflow_id;
    T_Newrow();
    echo "<b>".ML("Naam","Name")."</b>";
    T_Next();
    
    if ($url) echo "<a class=\"ims_link\" href=\"$url\">";
    echo $workflow["name"];
    if ($url) echo "</a>";
    T_Newrow();
    echo "<b>".ML("Aantal stadia","Stages")."</b>";
    T_Next();
    if ($url) echo "<a class=\"ims_link\" href=\"$url\">";
    echo $workflow["stages"];
    if ($url) echo "</a>";
    T_Newrow();
    if (SHIELD_HasProduct ("cms")) {
      echo "<b>".ML("CMS","CMS")."</b>";
      T_Next();
      if ($url) echo "<a class=\"ims_link\" href=\"$url\">";
      echo N_IF ($workflow["cms"], ML("ja","yes"), ML("nee","no"));
      if ($url) echo "</a>";
      T_Newrow();
    }
    if (SHIELD_HasProduct ("dms")) {
      echo "<b>".ML("DMS","DMS")."</b>";
      T_Next();
      if ($url) echo "<a class=\"ims_link\" href=\"$url\">";
      echo N_IF ($workflow["dms"], ML("ja","yes"), ML("nee","no"));
      if ($url) echo "</a>";
      T_Newrow();
    }
    if ($myconfig[IMS_SuperGroupName()]["showworkflowinpreview"]=="yes") {
      echo "<b>".ML("Tonen bij In behandeling","Show with In preview")."</b>";
      T_Next();
      if ($url) echo "<a class=\"ims_link\" href=\"$url\">";
      echo N_IF ($workflow["inpreview"], ML("ja","yes"), ML("nee","no"));
      if ($url) echo "</a>";
      T_Newrow();
    }
    if (SHIELD_HasProduct ("cms")) {
      echo "<b>".ML("Planbaar","Can be scheduled")."</b>";
      T_Next();
      if ($url) echo "<a class=\"ims_link\" href=\"$url\">";
      echo N_IF ($workflow["scedule"], ML("ja","yes"), ML("nee","no"));
      if ($url) echo "</a>";
      T_Newrow();
    }
    echo "<b>".ML("Toewijsbaar","Assignable")."</b>";
    T_Next();
    if ($url) echo "<a class=\"ims_link\" href=\"$url\">";
    echo N_IF ($workflow["alloc"], ML("ja","yes"), ML("nee","no"));
    if ($url) echo "</a>";
    T_Newrow();
    echo "<b>".ML("Uitsluiten van zoekmachine","Exclude from search engine")."</b>";
    T_Next();
    if ($url) echo "<a class=\"ims_link\" href=\"$url\">";
    echo N_IF ($workflow["noindex"], ML("ja","yes"), ML("nee","no"));
    if ($url) echo "</a>";

    global $myconfig;
    if ($myconfig[IMS_SuperGroupName()]["workflowhelpicon"]) {
      T_Newrow();
      echo "<b>".ML("Help link","Help link")."</b>";
      T_Next();
      if ($url) echo "<a class=\"ims_link\" href=\"$url\">";
      echo ML("wijzigen","edit");
      if ($url) echo "</a>";
    }

    $myconfig["demo_sites"]["workflowhelpicon"] = "/openims/help.gif"; // ?????

    T_Newrow();
    echo "<b>".ML("Metadata","Metadata")."</b>";
    T_Next();
    $form = array();
    $form["title"] = "Meta data";
    $form["input"]["col"] = $supergroupname;
    $form["input"]["id"] = $workflow_id;
    $metacount=7;
    for ($i=1; $i<=1000; $i++) {
      if ($workflow["meta"][$i]) {
        $metacount=$i+7;
      }
    }
    for ($i=1; $i<=$metacount; $i++) {
      $form["metaspec"]["fields"]["meta$i"]["type"] = "list";
      $form["metaspec"]["fields"]["readonly$i"]["type"] = "yesno";
      $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
      if (!is_array ($allfields)) $allfields = array();
      ksort ($allfields);
      $form["metaspec"]["fields"]["meta$i"]["values"]["&lt;".ML("geen","none")."&gt;"] = "";
      foreach ($allfields as $field => $dummy) {
        $form["metaspec"]["fields"]["meta$i"]["values"][$field] = $field;
      }
    }
    $form["formtemplate"] = '
      <table>
        <tr><th>&nbsp;</th><th>&nbsp;</td><th><font face="arial" size=2>'.ML("Niet wijzigbaar", "Read only").'</font></th></tr>
    ';
    for ($i=1; $i<=$metacount; $i++) {
      $form["formtemplate"] .= '
        <tr><td><font face="arial" size=2><b>'.ML("Metadata veld","Metadata field").' '.$i.':</b></font></td><td>[[[meta'.$i.']]]</font></td><td align="right">[[[readonly'.$i.']]]</td></tr>
      ';

    }
    $form["formtemplate"] .= '
        <tr><td colspan=3>&nbsp;</td></tr>
        <tr><td colspan=3><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>
    ';
    $form["precode"] = '
      $workflow = &SHIELD_AccessWorkflow ($input["col"], $input["id"]);
      for ($i=1; $i<=1000; $i++) {
        $data["meta$i"] = $workflow["meta"][$i];
        if ($workflow["meta"][$i]) $data["readonly$i"] = $workflow["readonly"][$i];
      }
    ';
    $form["postcode"] = '
      $workflow = &SHIELD_AccessWorkflow ($input["col"], $input["id"]);
      for ($i=1; $i<=1000; $i++) {
        $workflow["meta"][$i] = $data["meta$i"];
        $workflow["readonly"][$i] = $data["readonly$i"];
      }
    ';
    $url = FORMS_URL ($form);
    echo "<a class=\"ims_link\" href=\"$url\">";
    $ctr=0;
    for ($i=1; $i<=1000; $i++) {
      if ($workflow["meta"][$i]) {
        if ($ctr) echo ", ";
        echo $workflow["meta"][$i];
        $ctr++;


      }
    }
    if (!$ctr) echo "...";
    echo "</a>";

    TE_End();
    echo "<br>";
    T_Start ("ims", array("noheader"=>true));
    global $objectrights;
    reset ($objectrights);
    while (list ($right)=each($objectrights)) {
      if ($right=="view") {
        DumpWorkflowRights ("workflow", $workflow, $right, array("allocated"));
      } else {
        DumpWorkflowRights ("workflow", $workflow, $right);
      }
    }
    TE_End();
    echo "<br>";
    T_Start ("ims", array("noheader"=>true));
    for ($i=1; $i<=$workflow["stages"]; $i++) {

// dddd
      echo "<a name='".$workflow_id.$i."' />";
      
      echo "<b>".ML("Status","Status")."</b> ";
      
      if ($workflow[$i]["name"]) {
        echo $workflow[$i]["name"];
      } else {
        echo "...";
      }
      
      if ($i==1) echo "<br>".ML("Nieuwe documenten en webpagina's krijgen status","New documents and webpages get stage")." '".$workflow[$i]["name"]."' ($i)";
      if ($i==$workflow["stages"]) echo "<br>".ML("Als een pagina status","If a page gets stage")." '".$workflow[$i]["name"]."' ($i) ".ML("krijgt wordt deze gepubliceerd","it will be published").".";
      T_NewRow();
      echo "&nbsp;&nbsp;&nbsp;";
      T_Next();
      echo "<b>".ML("Wijzigen","Edit")."</b>";
      T_NewRow();
      echo "&nbsp;&nbsp;&nbsp;";
      T_Next();
      echo "&nbsp;&nbsp;&nbsp;";
      T_Next();
      if (SHIELD_WorkFlowRights ($workflow[$i]["edit"])) {
        
        echo ML("Na wijzigen wordt de status","After edit the stage becomes")." ";
        
        echo "'".$workflow[$workflow[$i]["stageafteredit"]]["name"]."' (".$workflow[$i]["stageafteredit"].")";
        
        T_NewRow();
        echo "&nbsp;&nbsp;&nbsp;";
        T_Next();
        echo "&nbsp;&nbsp;&nbsp;";
        T_Next();
        echo ML("Er mag gewijzigd worden door","Edit is allowed for").": ";
        
        echo WorkFlowRights ("workflow", $workflow[$i]["edit"]);
        
      } else {
        
        echo ML("In dit stadium mag document/pagina niet gewijzigd worden","In this stage edit is not allowed");
        
      }
      T_NewRow();
      $options = $workflow[$i];
      if (is_array($options)) { // used to also check $i!=$workflow["stages"]
        ksort ($options);
        reset ($options);
        while (list($option)=each($options)) {
          if (substr ($option, 0, 1)=="#") {
            echo "&nbsp;&nbsp;&nbsp;";
            T_Next();
            
            echo "<b>".ML("Keuze","Choice")."</b> ";
            
            echo substr($option, 1);
            
            T_NewRow();
            echo "&nbsp;&nbsp;&nbsp;";
            T_Next();
            echo "&nbsp;&nbsp;&nbsp;";
            T_Next();
            
            echo ML("Keuze","Choice")." '". substr($option, 1)."' ".ML("verandert de status in","changes the status into").": ";
            
            echo "'".$workflow[$workflow[$i][$option]]["name"]."' (".$workflow[$i][$option].")";
            

            

            T_NewRow();
            echo "&nbsp;&nbsp;&nbsp;";
            T_Next();
            echo "&nbsp;&nbsp;&nbsp;";
            T_Next();
            echo ML("Deze keuze mag gemaakt worden door","This choice can be made by").": ";
            
            echo WorkFlowRights ("workflow", $workflow[$i]["changestage"][$option]);
            
            T_NewRow();
          }
        }
      }

      
    }
    TE_End();
    echo "<br>";
    endblock();

  }  else if ($mode=="admin" && $submode=="workflow") {

    startblock (ML("Overzicht workflows", "Overview workflows"), "docnav");
?>
<br>
<font face="Arial, Helvetica" size="2">
<table border="0" cellspacing="0" cellpadding="0">
  <form action="<? echo N_MyBareURL(); ?>" method="put">
    <tr><td>
      <input type="hidden" name="mode" value="admin">
      <input type="hidden" name="submode" value="workflow">
      <input type="text" name="filter" size="10" value="<? echo $filter; ?>"><br>
    </td><td>
      &nbsp;
    </td><td>
      <input type="submit" style="font-weight:bold" value="<? echo ML("Filter","Filter"); ?>">
    </td></tr>
  </form>
</table><br>
</font>
<?
    T_Start ("ims", array ("nobr"=>true, "sort"=>"ims_workflows", "sort_default_col" => 1, "sort_default_dir" => "u", "sort_bottomskip"=>1,
                           "sort_1"=>"auto", "sort_2"=>"auto", "sort_3"=>"auto", "sort_4"=>"auto"));
    echo ML("Workflow","Workflow");
    T_Next();
    if (SHIELD_HasProduct ("cms")) {
       echo ML("CMS","CMS");
       T_Next();
    }
    if (SHIELD_HasProduct ("dms")) {
       echo ML("DMS","DMS");
       T_Next();
    }
    echo ML("Samenvatting","Summary");
    T_Next();
    echo " ";
    T_Newrow();
    $list = MB_Query ("shield_".$supergroupname."_workflows");
    while (list($key)=each($list)) {
      $rec = MB_Ref ("shield_".$supergroupname."_workflows", $key);
      if (!$filter || stristr ($rec["name"], $filter)) {
        echo $rec["name"];
        T_Next();

        if (SHIELD_HasProduct ("cms")) {
           $url = N_MyBareURL()."?mode=admin&submode=1workflow&workflow_id=".$key;
           if ($rec["cms"]) {
             echo "<a class=\"ims_link\" href=\"$url\">".ML("ja","yes")."</a>";
           } else {
             echo "<a class=\"ims_link\" href=\"$url\">".ML("nee","no")."</a>";
           }
           T_Next();
        }

        if (SHIELD_HasProduct ("dms")) {
           $url = N_MyBareURL()."?mode=admin&submode=1workflow&workflow_id=".$key;
           if ($rec["dms"]) {
             echo "<a class=\"ims_link\" href=\"$url\">".ML("ja","yes")."</a>";
           } else {
             echo "<a class=\"ims_link\" href=\"$url\">".ML("nee","no")."</a>";
           }

           T_Next ();
        }


        for ($i=1; $i<=$rec["stages"]; $i++) {
          if (strpos($rec[$i]["name"], "(hide)")) continue;
          echo $rec[$i]["name"];
          if ($i != $rec["stages"]) echo " - ";
        }

        T_Next();

        $url = N_MyBareURL()."?mode=admin&submode=1workflow&workflow_id=".$key;
        echo "<a class=\"ims_link\" href=\"$url\">".ML("bewerken","edit")."</a>";

        T_Next();
          

        T_Newrow();
      }
    }

    TE_End();
    echo "<br>";
    endblock();

  } else if ($mode=="admin" && $submode=="ems_permissions" && SHIELD_HasProduct ("ems") && ($myconfig["mail"]["multiarchive"])) { // multi archive
      startblock (ML("EMS rechten", "EMS permissions"), "docnav");
?>
<br>
<font face="Arial, Helvetica" size="2">
<table border="0" cellspacing="0" cellpadding="0">

  <form action="<? echo N_MyBareURL(); ?>" method="put">
    <tr><td>
      <input type="hidden" name="mode" value="admin">
      <input type="hidden" name="submode" value="groups">
      <input type="text" name="filter" size="10" value="<? echo $filter; ?>"><br>
    </td><td>
      &nbsp;
    </td><td>
      <input type="submit" style="font-weight:bold" value="<? echo ML("Filter","Filter"); ?>">
    </td></tr>
  </form>
</table><br>

<?
    T_Start ("ims");
    echo ML("Archief","Archive")." <img src=\"/ufc/rapid/openims/sortup.gif\">";
    T_Next();
    echo " ";
    T_Next();
    echo " ";
    T_NewRow();

    $groups = MB_Query ("shield_".$supergroupname."_groups", "", '$record["name"]');
    for($i=1;$i<=$myconfig["mail"]["accounts"];$i++)
    {
      if($myconfig["mail"][$i]["sitecollection"]==$supergroupname) {
        echo $myconfig["mail"][$i]["archivename"]."<br>";
        foreach(array("read","delete") as $currentpermission) {
          T_Next();

          $gform = array();
          switch($currentpermission) {
          case "read":
            $gform["title"] = ML("leesrechten","Read permissions");

            break;
          case "delete":
            $gform["title"] = ML("verwijderrechten","Delete permissions");
            break;
          }
          $gform["input"]["col"] = $supergroupname;
          $gform["input"]["archiveid"] = $myconfig["mail"][$i]["archiveid"];

          $gform["formtemplate"] = "<table>";
          $gform["formtemplate"] .= '<tr><td halign="center"><font face="arial" size=2>';
          switch($currentpermission) {
          case "read":
            $gform["formtemplate"] .= ML("Leesrechten voor %1","Read permissions for %1", $myconfig["mail"][$i]["archivename"]);
            break;
          case "delete":
            $gform["formtemplate"] .= ML("Verwijderrechten voor %1","Delete permissions for %1", $myconfig["mail"][$i]["archivename"]);
            break;
          }
          $gform["formtemplate"] .= '</td></tr></font>';

          reset ($groups);
          while (list ($group_id)=each($groups)) {

            $group = &MB_Ref ("shield_".$supergroupname."_groups", $group_id);
            $gform["formtemplate"] .= '<tr><td><font face="arial" size=2><b>'.$group["name"].':</b></font></td><td>[[['.$group_id.']]]</td></tr>';
            $gform["metaspec"]["fields"][$group_id]["type"] = "yesno";
          }
          $gform["formtemplate"] .= '
             <tr><td colspan=2>&nbsp</td></tr>
             <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
             </table>
          ';

          $gform["precode"] = '
              $permissions = MB_Ref ("shield_".$input["col"]."_ems_permissions", $input["archiveid"]);
              $permissions = $permissions["' . $currentpermission .'"];
              if (is_array($permissions)) {
                reset($permissions);
                while (list($group_id,$perm)=each($permissions)) {
                  $data[$group_id] = $perm;
                }
              }
            ';

          $gform["postcode"] = '
                $groups = MB_Query ("shield_".$input["col"]."_groups");
                if (is_array($groups)) {
                  $permissions = &MB_Ref ("shield_".$input["col"]."_ems_permissions", $input["archiveid"]);
                   while (list ($group_id)=each($groups)) {
                     $grouparray = MB_Ref("shield_".$input["col"]."_groups", $group_id);
                     $permissions["' . $currentpermission . '"][$group_id] = $data[$group_id];
                  }
                }
              ';

          $url = FORMS_URL ($gform);
          echo '<a href="' . $url . '">';
          switch($currentpermission) {
          case "read":
            echo ML("Leesrechten", "Read persmissions");
            break;
          case "delete":
            echo ML("Verwijderrechten", "Delete persmissions");
            break;
          }
          echo "</a>";
        }
      }
    T_NewRow();
  }

  TE_End();
  echo "<br>";
  endblock();
} // multi archive




  if ($wide) {

    echo "</td><td width=5%>&nbsp;";
  } else if ($mode=="dbm" || $viewmode=="norightcol") {
    echo "</td><td width=1%>&nbsp;";
  } else if ($viewmode=="report" || $viewmode=="norightorleftcol") {
    echo "</td><td width=1%>&nbsp;";
  } else {
    echo "</td>";

  // RRIGHT
    if ( function_exists( "SKIN_preventEmptyColumnStart" ) )
      SKIN_preventEmptyColumnStart( "RIGHT" );
    else
      echo "<td width=20% class='openims_right_column'>";

  $truecurrentobject = $currentobject;
  if (FILES_IsShortcut ($supergroupname, $currentobject)) {
    $currentobject = FILES_Base ($supergroupname, $currentobject);
  }


  if ($mode=="bpms") {
    global $bpmswizards; // Needs to be accessible in BPMSUIF_ functions
    $bpmswizards = array();
    // Evaluate all BPMS Wizards (so they can be used in any block)
    uuse ("flex");
    // Some wizard code copies therecord to thecase for convenience (or vice versa). This confuses BPMS.
    // The Pre/PostEvalCleanup Code only deals with references, not with normal variables.
    $therecord_backup = $therecord;
    $thecase_backup = $thecase;
    $thetable_backup = $thetable;
    $theprocess_backup = $theprocess;
    $list_bpmswizards = FLEX_LocalComponents (IMS_SuperGroupName(), "bpmswizard");
    FLEX_LoadSupportFunctions(IMS_SuperGroupName()); // backward compatibility, maybe some wizard relies on this
    foreach ($list_bpmswizards as $id_bpmswizard => $specs_bpmswizard) {  // long var names to make sure that wizards dont
                                                                          // accidently overwrite their own id or specs
      if ($therecord) {
        $case = MB_Ref ("process_".$supergroupname."_cases_".$thetable, $therecord);
      } 
      if ($thecase) {
        $case = MB_Ref ("process_".$supergroupname."_cases_".$theprocess, $thecase);
      } 
      $result = false; // seems like a sensible improvement
      if ($profiling=="yes") {
        $before = N_Elapsed();
        eval (N_GeneratePreEvalCleanupCode());
        eval ($specs_bpmswizard["code_condition"]);
        eval (N_GeneratePostEvalCleanupCode ($specs_bpmswizard["code_condition"]));
        $after = N_Elapsed();
        if ((int)(1000*($after-$before))>9) echo "[condition:".$specs_bpmswizard["title"]." ".(int)(1000*($after-$before))." ms]<br>";
      } else {
        eval (N_GeneratePreEvalCleanupCode());
        eval ($specs_bpmswizard["code_condition"]);
        eval (N_GeneratePostEvalCleanupCode ($specs_bpmswizard["code_condition"]));
      }

      if ($result) {
        // Determine the url for this wizard (conveniently stored in the variable $result)
        // Would be better to do this only if wizard is actually going to be shown.
        // But that means either coding it in a function, which will cause wizards that
        // rely on global context to fail, or it means coding it 5 times (each block)
        if ($profiling=="yes") {
          $before = N_Elapsed();
          eval (N_GeneratePreEvalCleanupCode());
          eval ($specs_bpmswizard["code_urlgenerator"]);
          eval (N_GeneratePostEvalCleanupCode ($specs_bpmswizard["code_urlgenerator"]));
          $after = N_Elapsed();
          if ((int)(1000*($after-$before))>9) echo "[urlgenerator:".$specs_bpmswizard["title"]." ".(int)(1000*($after-$before))." ms]<br>";
        } else {
          eval (N_GeneratePreEvalCleanupCode());
          eval ($specs_bpmswizard["code_urlgenerator"]);
          eval (N_GeneratePostEvalCleanupCode ($specs_bpmswizard["code_urlgenerator"]));
        }
        $bpmswizards[$id_bpmswizard] = $result;
      }
    }
    // Some wizard code copies therecord to thecase for convenience (or vice versa). This confuses BPMS.
    $therecord = $therecord_backup;
    $thecase = $thecase_backup;
    $thetable = $thetable_backup;
    $theprocess = $theprocess_backup;

    if ($therecord) {
      $case = MB_Ref ("process_".$supergroupname."_cases_".$thetable, $therecord);
      startblock (ML("Acties","Actions")." (".$case["visualid"].")", "action");
      BPMSUIF_ActionBlock ($supergroupname, $thetable, $therecord);
      echo BPMSUIF_ShowWizards(IMS_SuperGroupName(), $bpmswizards, "actions", $dummy);
      endblock();
    }
    if ($thecase) {
      $case = MB_Ref ("process_".$supergroupname."_cases_".$theprocess, $thecase);

      startblock (ML("Acties","Actions")." (".$case["visualid"].")", "action");
      BPMSUIF_ActionBlock ($supergroupname, $theprocess, $thecase);
      echo BPMSUIF_ShowWizards(IMS_SuperGroupName(), $bpmswizards, "actions", $dummy);
      endblock();
      if (1===BPMSUIF_HasChoicesBlock ($supergroupname, $theprocess, $thecase)) {
        startblock (ML("Formulier","Form"), "action");
        BPMSUIF_ChoicesBlock ($supergroupname, $theprocess, $thecase);
        echo BPMSUIF_ShowWizards(IMS_SuperGroupName(), $bpmswizards, "decision", $dummy);
        endblock();
      }
      if (2===BPMSUIF_HasChoicesBlock ($supergroupname, $theprocess, $thecase)) {
        startblock (ML("Beslissing","Decision"), "action");
        BPMSUIF_ChoicesBlock ($supergroupname, $theprocess, $thecase);
        echo BPMSUIF_ShowWizards(IMS_SuperGroupName(), $bpmswizards, "decision", $dummy);
        endblock();
      }
    }

    $wizardcount = 0;
    $wizardstring = BPMSUIF_ShowWizards(IMS_SuperGroupName(), $bpmswizards, (($thecase || $therecord) ? "wizards_active" : "wizards"), $wizardcount);
    if ($wizardcount) {
      if ($wizardcount > 1) {

        startblock (ML("Assistenten", "Wizards"), "action");
      } else {
        startblock (ML("Assistent", "Wizard"), "action");
      }
      echo $wizardstring;
      endblock();
    }

    if ($myconfig[IMS_SuperGroupName()]["multibpms"]=="yes") {
      // This block is for one specific process.
      // You can have only one block per page.
      // Therefore, it can not be used in assigned or search submode.
      if ($submode == "inprocess") {
        BPMSUIF_MultiActionBlock ($theprocess, $thestage); // Handles its own startblock / endblock stuff
   
      } elseif ($submode == "data") {
        BPMSUIF_MultiActionBlock ($thetable); // Handles its own startblock / endblock stuff
      }
    }

  }

  $dmswizards = array();
  if ($mode=="dms" or $mode=="related") { // Evaluate all DMS Wizards (so they can be used in any block)
    uuse ("flex");
    // make sure wizard code cannot break $object
    unset ($object); // breaks connection but not the value (http://www.php.net/manual/en/language.references.unset.php)
    unset ($workflow);
    $list_dmswizards = FLEX_LocalComponents (IMS_SuperGroupName(), "dmswizard");
    function cmp_dmswizard ($v1, $v2) {
      if ($v1["sort"] . "~" . $v1["name"] < $v2["sort"] . "~" . $v2["name"]) {
        return -1;
      } else if ($v1["sort"] . "~" . $v1["name"] > $v2["sort"] . "~" . $v2["name"]) {
        return 1;
      } else {
        return 0;
      }
    }
    uasort ($list_dmswizards, 'cmp_dmswizard');
    FLEX_LoadSupportFunctions(IMS_SuperGroupName()); // backward compatibility, maybe some wizard relies on this
    foreach ($list_dmswizards as $id_dmswizard => $specs_dmswizard) {  // long var names to make sure that wizards dont
                                                                       // accidently overwrite their own id or specs
      // If submode has never been set in the wizard, set default value (everything except cases / dmsview / autotableview)
      if (!$specs_dmswizard["submode"]) {
        $specs_dmswizard["submode"] = "documents;alloced;recent;activities;search";
        if ($specs_dmswizard["incaseoverview"]) $specs_dmswizard["submode"] .= ";cases";  // legacy option
      }

      if ((strpos(" " . $specs_dmswizard["submode"], $submode))
          || ($mode == "related" && (strpos(" " . $specs_dmswizard["submode"], $mode)))) {

        if ($currentobject) {
          $object = IMS_AccessObject (IMS_SuperGroupName(), $currentobject);
          $workflow = SHIELD_AccessWorkflow (IMS_SuperGroupName(), $object["workflow"]);
        } else {
          $object = 0;
          $workflow = 0;
        }

        $result = false; // seems like a sensible improvement
        if ($profiling=="yes") {
          $before = N_Elapsed();
          eval (N_GeneratePreEvalCleanupCode());
          eval ($specs_dmswizard["code_condition"]);
          eval (N_GeneratePostEvalCleanupCode ($specs_dmswizard["code_condition"]));
          $after = N_Elapsed();
          if ((int)(1000*($after-$before))>9) echo "[condition:".$specs_dmswizard["title"]." ".(int)(1000*($after-$before))." ms]<br>";
        } else {
          eval (N_GeneratePreEvalCleanupCode());
          eval ($specs_dmswizard["code_condition"]);
          eval (N_GeneratePostEvalCleanupCode ($specs_dmswizard["code_condition"]));
        }

        if ($result) {
          // Determine the url for this wizard (conveniently stored in the variable $result)
          // Would be better to do this only if wizard is actually going to be shown.
          // But that means either coding it in a function, which will cause wizards that
          // rely on global context to fail, or it means coding it 5 times (each block)
          if ($profiling=="yes") {
            $before = N_Elapsed();
            eval (N_GeneratePreEvalCleanupCode());
            eval ($specs_dmswizard["code_urlgenerator"]);
            eval (N_GeneratePostEvalCleanupCode ($specs_dmswizard["code_urlgenerator"]));
            $after = N_Elapsed();
            if ((int)(1000*($after-$before))>9) echo "[urlgenerator:".$specs_dmswizard["title"]." ".(int)(1000*($after-$before))." ms]<br>";
          } else {
            eval (N_GeneratePreEvalCleanupCode());
            eval ($specs_dmswizard["code_urlgenerator"]);
            eval (N_GeneratePostEvalCleanupCode ($specs_dmswizard["code_urlgenerator"]));
          }
          $dmswizards[$id_dmswizard] = $result;
        }
      }
    }
  }

  if ($mode=="dms") {
    $key = $currentobject;
    $object = &IMS_AccessObject ($supergroupname, $key);

    if ($currentobject && (SHIELD_HasObjectRight ($supergroupname, $truecurrentobject, "view") || (SHIELD_HasObjectRight ($supergroupname, $truecurrentobject, "viewpub") && ($object["published"]=="yes")))) {

    $doc = FILES_TrueFileName ($supergroupname, $key, "preview");

    $image = FILES_Icon ($supergroupname, $key, false, "preview");
    $ext = FILES_FileExt ($supergroupname, $key, "preview");
    $thedoctype = FILES_FileType ($supergroupname, $key, "preview");

    if (!trim($object["shorttitle"])) {
      startblock ("???".$ext, "action");
    } else {
      startblock (str_replace ("_", " ", $object["shorttitle"]).$ext, "action");
    }

  FLEX_LoadSupportFunctions (IMS_SuperGroupName());

  if (!function_exists ("FILES_SpecialCompare")) {
   // DMS special files (default)
   $internal_component = FLEX_LoadImportableComponent ("support", "f56996e35ef98d2f15f2310e62cc75a8");
   $internal_code = $internal_component["code"];
   eval ($internal_code);
  }

        if (SHIELD_HasObjectRight ($supergroupname, $key, "edit") && (! FILES_IsPermalink ($supergroupname, $truecurrentobject))) {
          $url = FILES_TransEditURL ($supergroupname, $key);
          $title = ML("Wijzig","Edit")." &quot;".$object["shorttitle"]."&quot; (".$object["longtitle"].")";
          echo "<a class=\"ims_navigation\" title=\"$title\" href=\"$url\">";
          echo '<img border=0 height=16 widh=16 src="'.$image.'"> '.ML("Wijzig","Edit");
          echo "</a><br>";
        }
        if (SHIELD_HasObjectRight ($supergroupname, $key, "edit") && FILES_SpecialEdit ($supergroupname, $key) && (! FILES_IsPermalink ($supergroupname, $truecurrentobject))) {
          foreach (FILES_SpecialEdit ($supergroupname, $key) as $show => $specs) {
            echo "<a class=\"ims_navigation\" title=\"".$specs["title"]."\" href=\"".$specs["url"]."\">";
            echo '<img border=0 height=16 widh=16 src="'.$specs["icon"].'"> '.$show;
            echo "</a><br>";
          }
        }
        if (SHIELD_HasObjectRight ($supergroupname, $truecurrentobject, "view")) {
          if(!FILES_IsPermalink($supergroupname, $truecurrentobject)){ // qqq
            // $url = FILES_TransViewPublishedURL (IMS_SuperGroupName(), $key);
            $url = FILES_TransViewPreviewURL (IMS_SuperGroupName(), $key);
          } else {
            $permaref   = MB_Load ("ims_".$supergroupname."_objects", $truecurrentobject);
            $historykey = $permaref["sourceversion"];
            if (FILES_HistoryVersionExistsOnDisk( IMS_SuperGroupName(), $permaref["source"], $historykey)) {
              $url = FILES_TransViewHistoryURL( IMS_SuperGroupName(), $permaref["source"], $historykey );
            } else {
              $url = "";
            }
          } // qqqq

          if ($url) {
            $title = ML("Bekijk","View")." &quot;".$object["shorttitle"]."&quot; (".$object["longtitle"].")";
            echo "<a class=\"ims_navigation\" " . ($object["doctopdf"]=="yes"?"target=\"_blank\" ":"") . "title=\"$title\" href=\"$url\">";
            echo '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/view.gif"> '.ML("Bekijk","View");
            echo "</a><br>";
            global $HTTP_HOST;
          }
        }
        if (SHIELD_HasObjectRight ($supergroupname, $key, "view") && FILES_SpecialView ($supergroupname, $key) && !FILES_IsPermalink($supergroupname, $truecurrentobject)) {
          foreach (FILES_SpecialView ($supergroupname, $key) as $show => $specs) {
            echo "<a class=\"ims_navigation\" title=\"".$specs["title"]."\" href=\"".$specs["url"]."\">";
            echo '<img border=0 height=16 widh=16 src="'.$specs["icon"].'"> '.$show;
            echo "</a><br>";
          }
        }
        if (SHIELD_HasObjectRight ($supergroupname, $key, "view")) {
          // 20091008 KvD IJsselgroep deze knop een andere functie geven
          if ($GLOBALS["myconfig"][$supergroupname]["custom_dms_sendmail"] == "yes")
            custom_dms_sendmail ($supergroupname, $key);
          else
            DMSUIF_SendMail ($supergroupname, $key);
        }
        if (SHIELD_HasObjectRight ($supergroupname, $key, "edit") && (! FILES_IsPermalink ($supergroupname, $truecurrentobject)) ) {
          $metaspec = array();
          $metaspec["fields"]["file"]["type"] = "bigfile";

         //ericd 080310 pas doc name aan bij doc upload
         if($myconfig[$supergroupname]["changedocnameatupload"] == "yes") {
            $metaspec["fields"]["changename"]["type"] = "yesno";
            $metaspec["fields"]["changename"]["title"] = ML("Documentnaam aanpassen?","Change document name?");

            if(!$myconfig[$supergroupname]["useuploaddocname"]) {
               $metaspec["fields"]["docname"]["type"] = "string";
               $metaspec["fields"]["docname"]["title"] = ML("Naam:","Name:");
            }
          }

          $formtemplate  = '<br><table><tr>
                         <td><font face="arial" size=2><b>'.ML("Bestand","File").'</b></font></td>
                         <td>[[[file]]]</td></tr>
                         <tr><td colspan=2>&nbsp</td></tr>
                         <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                       </table>';

         //ericd 080310 pas doc name aan bij doc upload
         if($myconfig[$supergroupname]["changedocnameatupload"] == "yes") {
            $formtemplate  = '<br><table><tr>
                         <td><font face="arial" size=2><b>'.ML("Bestand","File").'</b></font></td>
                         <td>[[[file]]]</td></tr>
                         <tr><td colspan="2">&nbsp</td></tr>
                         <tr><td colspan="2"><font face="arial" size=2>{{{changename}}}</font> [[[changename]]]</td></tr>';

            if($myconfig[$supergroupname]["useuploaddocname"] != "yes") {

               $formtemplate  .= '
               <tr><td><font face="arial" size=-2>{{{docname}}}</font></td><td>[[[docname]]]</td></tr>';
            }
            elseif($myconfig[$supergroupname]["useuploaddocname"] == "yes") {
               $formtemplate  .= '
               <tr><td colspan="2"><font face="arial" size=-2>'.ML("(Naam van upload document wordt gebruikt.)","(Name of upload document is used.)").'</font></td></tr>';
            }

            $formtemplate  .= '<tr><td colspan=2>&nbsp</td></tr>
                         <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                       </table>';
          }

          $input = array();
          $input["sitecollection_id"] = $supergroupname;
          $input["directory_id"] = $currentfolder;
          $input["object_id"] = $currentobject;
          $input["originaldocname"] = MB_Fetch("ims_".$input["sitecollection_id"]."_objects", $currentobject, "shorttitle");
          $input["user_id"] = SHIELD_CurrentUser ($input["sitecollection_id"]);
          $form = array();

         //ericd 080310 pas doc name aan bij doc upload
         if($myconfig[$supergroupname]["changedocnameatupload"] == "yes") {

            $precode = '$data["changename"] = "yes";';

            if($myconfig[$supergroupname]["useuploaddocname"] != "yes") {
               $precode .= '
                  $object_id = $input["object_id"];
                  $object = IMS_AccessObject ($input["sitecollection_id"], $object_id);
                  $data["docname"] = $object["shorttitle"];';
            }
         }

          $postcode = '
            uuse ("ims");
            uuse ("shield");
            uuse ("files");
            $filename = strtolower ($files["file"]["name"]);
            $filename = preg_replace ("\'[^a-z0-9\\.]\'i", "_", $filename);
            $ext = N_KeepAfter ($filename, ".", true);
            if (!$filename) FORMS_ShowError (ML("Foutmelding","Error"), ML("Het bestand ontbreekt","The file is missing"), true);
            if (!N_FileSize ($files["file"]["tmpfilename"])) FORMS_ShowError (ML("Foutmelding","Error"), ML("Er is een leeg bestand of de upload is mislukt","The file is empty or the upload has failed"), true);
//            if (!strlen ($files["file"]["content"])) FORMS_ShowError (ML("Foutmelding","Error"), ML("Er is een leeg bestand of de upload is mislukt","The file is empty or the upload has failed"), true);
            $object_id = $input["object_id"];
            $object = &IMS_AccessObject ($input["sitecollection_id"], $object_id);

            //ericd 030210 pas doc name aan bij doc upload
            if($myconfig[$input["sitecollection_id"]]["changedocnameatupload"] == "yes" && $data["changename"] == "true") {
               if($myconfig[$input["sitecollection_id"]]["useuploaddocname"] != "yes") {
                     if(trim($data["docname"]) !== "")
                        $object["shorttitle"] = trim($data["docname"]);
               }
               elseif ($myconfig[$input["sitecollection_id"]]["useuploaddocname"] == "yes") {
                  $uploadFileName = substr_replace($files["file"]["name"], "", -strlen($ext)-1);
                  $object["shorttitle"] = $uploadFileName;
               }
               IMS_AddHistoryComment($object_id, $input["sitecollection_id"], "Upload: bestandsnaam \"".$input["originaldocname"]."\" vervangen voor \"".$object["shorttitle"]."\"");  //CVE: some additional info in history
            }

            $doc = FILES_TrueFileName ($input["sitecollection_id"], $object_id, "preview");
            $doctype = FILES_FileType ($input["sitecollection_id"], $object_id, "preview");
            if ($ext!=$doctype) {
              if ($allowed = FILES_AllowedFiletypes($doctype)) {
                if (in_array($ext, $allowed)) {
                  $filenamechange = true;
                  $doc = substr($doc, 0, strlen($doc) - strlen($doctype)) . $ext;
                } else {
                  $types = "&quot;".$doctype."&quot;";
                  foreach ($allowed as $i => $allowedtype) {
                    if ($i + 1 == count($allowed)) {
                      $types .= " " . ML("of", "or") . " &quot;".$allowedtype."&quot;";
                    } else {
                      $types .= ", &quot;".$allowedtype."&quot;";
                    }
                  }
                  FORMS_ShowError (ML("Foutmelding","Error"), ML("U moet hier een","You have to upload")." $types ".ML("bestand uploaden","files").".", true);
                }
              } else {
                FORMS_ShowError (ML("Foutmelding","Error"), ML("U moet hier een","You have to upload")." \"".$doctype."\" ".ML("bestand uploaden","files").".", true);
              }
            }
            N_CopyFile ("html::".$input["sitecollection_id"]."/preview/objects/".$object_id."/".$doc, $files["file"]["tmpfilename"]);
//            N_WriteFile ("html::".$input["sitecollection_id"]."/preview/objects/".$object_id."/".$doc, $files["file"]["content"]);
            if ($filenamechange) FILES_HandleFilenameChange($input["sitecollection_id"], $object_id, $doc);
            IMS_SignalObject ($input["sitecollection_id"], $object_id, $input["user_id"], getenv("HTTP_HOST"));
          ';
          $form["title"] = ML("Upload nieuwe","Upload new")." &quot;".$object["shorttitle"]."&quot;";

          $form["input"] = $input;
          $form["metaspec"] = $metaspec;
          $form["formtemplate"] = $formtemplate;

          //ericd 080310 pas doc name aan bij doc upload
          if($myconfig[$supergroupname]["changedocnameatupload"] == "yes") {
            if($precode)
               $form["precode"] = $precode;
          }

          $form["postcode"] = $postcode;
          $uploadurl = FORMS_URL ($form);

          echo "<a class=\"ims_navigation\" title=\"".$form["title"]."\" href=\"$uploadurl\">";
          // 20091215 KvD IJsselgroep
          echo '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/upload_small.gif"> '.ML("Upload nieuwe versie","Upload new version");

          echo "</a><br>";

          global $myconfig;
          if ($myconfig[IMS_SuperGroupName()]["directaccess"]=="yes") {
            $accessurl = FILES_TransDirectAccess (IMS_SuperGroupName(), $key);
            echo "<a class=\"ims_navigation\" title=\"".$form["title"]."\" href=\"$accessurl\">";
            echo '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/folder.gif"> '.ML("Directe toegang","Direct Access");
            echo "</a><br>";
          }

        }

        if (FILES_IsIndependentShortcut ($supergroupname, $truecurrentobject)) {
          $url = DMSUIF_IndependentShortcutProperties ($supergroupname, $truecurrentobject, $currentobject);
          if ($url) {
            echo "<a class=\"ims_navigation\" title=\"".ML("Eigenschappen voor snelkoppeling","Properties of shortcut")." &quot;".$object["shorttitle"]."&quot;\" href=\"$url\">";
            echo '<img border="0" src="/openims/properties_small.gif"> '.ML("Eigenschappen","Properties");
            echo "</a><br>";
          }
        } elseif (FILES_IsPermalink ($supergroupname, $truecurrentobject)) {
          $permaobject = MB_Ref("ims_".$supergroupname."_objects", $truecurrentobject);
          if(SHIELD_HasObjectRight ($supergroupname, $key, "edit")){
            $form["title"] = ML("Shortcut wijzigen","Edit shortcut");
            $form["input"]["sgn"] = $supergroupname;
            $form["input"]["currentobject"] = $truecurrentobject;
            $form["input"]["currentfolder"] = $currentfolder;
            $form["metaspec"]["fields"]["shorttitle"]["type"] = "strml4";
            $form["metaspec"]["fields"]["shorttitle"]["title"] = ML("Document naam","Document name");
            $form["formtemplate"] = '
              <table>
                <tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[shorttitle]]]</td></tr>
                <tr><td colspan=2>&nbsp</td></tr>
                <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
              </table>
            ';
            $form["precode"] = '
            $data["shorttitle"] = "'.$permaobject["base_shorttitle"].'";
            ';
            $form["postcode"] = '
            if($data["shorttitle"]=="") FORMS_ShowError ("Foutmelding","U moet een documentnaam opgeven.",true);
            $shortcutobject = &IMS_AccessObject ($input["sgn"], $input["currentobject"]);
            $shortcutobject["base_shorttitle"] = $data["shorttitle"];
            $gotook = "closeme&parentgoto:/openims/openims.php?mode=dms&currentfolder=".$input["currentfolder"]."&currentobject=".$input["currentobject"];
            ';
            $url = FORMS_URL ($form);
            echo "<a class=\"ims_navigation\" title=\"".ML("Eigenschappen voor permalink","Properties for permalink")."\" href=\"$url\">";
            echo '<img border="0" src="/openims/properties_small.gif"> '.ML("Eigenschappen","Properties");
            echo "</a><br>";
          }
        } else {
          DMSUIF_Properties ($supergroupname, $key, $object);
        }

        if (SHIELD_HasObjectRight ($supergroupname, $key, "view")) {
          $title = ML("Historie van","History of")." &quot;".$object["shorttitle"]."&quot;";
          echo '<a class="ims_navigation" title="'.$title.'" href="/openims/openims.php?mode=history&back='.urlencode($goto).'&object_id='.$key.'"><img border=0 src="/ufc/rapid/openims/history_small.gif"> '.ML("Historie","History").'</a><br>';

        }
        if (SHIELD_HasObjectRight ($supergroupname, $key, "docviewhistory") && strlen(SHIELD_ReturnWorkflowRightGroups (IMS_SuperGroupName(),  $object["workflow"], "docviewhistory", $extra=""))>0) {
          $title = ML("Leeshistorie van","View history of")." &quot;".$object["shorttitle"]."&quot;";
          echo '<a class="ims_navigation" title="'.$title.'" href="/openims/openims.php?mode=viewhistory&back='.urlencode($goto).'&object_id='.$key.'"><img border=0 src="/ufc/rapid/openims/history_small.gif"> '.ML("Leeshistorie","View history").'</a><br>';
        }
        
//20130425 KVD Annotations
        if ($GLOBALS['myconfig'][$supergroupname]["useflexpaper"] == "yes") {
          uuse("flexpaper");
          echo FLEXPAPER_askoption($supergroupname, $key, $object);          
        }
///

        if (LINK_EnabledDMS() && LINK_HasLinks  (IMS_SuperGroupName(),$currentobject)) {
          $title = ML("Document koppelingen van","Document connections of")." &quot;".$object["shorttitle"]."&quot;";
          echo '<a class="ims_navigation" title="'.$title.'" href="/openims/openims.php?mode=related&back='.urlencode($goto).'&object_id='.$key.'"><img border=0 src="/ufc/rapid/openims/make-link.gif"> '.ML("Gekoppeld","Connected").' ('.LINK_HasLinks  (IMS_SuperGroupName(),$currentobject).')</a><br>';
        }

        $form = array();
        $form["input"]["object_id"] = $key;
        $form["input"]["user_id"] = SHIELD_CurrentUser();

        $form["metaspec"]["fields"]["changed"]["type"] = "yesno";
        $form["metaspec"]["fields"]["published"]["type"] = "yesno";
        $form["metaspec"]["fields"]["statuschanged"]["type"] = "yesno";
        $form["formtemplate"] = '
          <table>
            <tr><td><font face="arial" size=2><b>'.ML("Bij wijziging","On change").':</b></font></td><td>[[[changed]]]</td></tr>
            <tr><td><font face="arial" size=2><b>'.ML("Bij publicatie","On publish").':</b></font></td><td>[[[published]]]</td></tr>
            <tr><td><font face="arial" size=2><b>'.ML("Bij statuswijziging","On status changed").':</b></font></td><td>[[[statuschanged]]]</td></tr>

            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
          </table>
        ';
        $form["precode"] = '
          $mode = MAIL_IsObjectConnectedToUser ($input["object_id"], $input["user_id"]);
          if (strpos (" ".$mode, "x")) $data["changed"] = true;
          if (strpos (" ".$mode, "p")) $data["published"] = true;
          if (strpos (" ".$mode, "s")) $data["statuschanged"] = true;
        ';
        $form["postcode"] = '
          $mode = "";
          if ($data["changed"]) $mode .= "x";
          if ($data["published"]) $mode .= "p";
          if ($data["statuschanged"]) $mode .= "s";
          MAIL_ConnectObjectToUser ($input["object_id"], $input["user_id"], $mode);
        ';
        $url = FORMS_URL ($form);
        if (SHIELD_HasObjectRight ($supergroupname, $key, "view")) {
          echo "<a class=\"ims_navigation\" title=\"".ML("E-mail signalering voor dit document","E-mail signals for this document")."\" href=\"$url\">";

          echo '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/signal_on.gif"> '.ML("Signalering","Signal");
          echo "</a><br>";
        }
        global $myconfig;
        if (($myconfig[IMS_SuperGroupName()]["projectfilter"]!="advanced") && SHIELD_HasObjectRight ($supergroupname, $key, "move") && ($submode=="documents" || $submode=="projects")) {
          $title = ML("Verplaatsen van","Move")." &quot;".$object["shorttitle"]."&quot;";
          $form["title"] = $title;
          $form["input"]["supergroupname"] = $supergroupname;
          $form["input"]["currentfolder"] = $currentfolder;
          $form["input"]["table"] = "ims_".$supergroupname."_objects";
          $form["input"]["key"] = $key;
          $form["metaspec"]["fields"]["loc"]["type"] = "tree";
          $form["formtemplate"] = '
            <table>
              <tr><td><font face="arial" size=2><b>'.ML("Folder","Folder").':</b></font></td><td>[[[loc]]]</td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>

          ';
          $form["precode"] = '
            $data["loc"] = $input["currentfolder"];
            $metaspec["fields"]["loc"]["tree"] = \'MB_Load ("ims_trees", "\'.$input["supergroupname"].\'_documents")\';
          ';
          $form["postcode"] = '


            $object = &MB_Ref ($input["table"], $input["key"]);
            if (SHIELD_HasWorkflowRight (IMS_SuperGroupName(), $object["workflow"], "move", SHIELD_SecuritySectionForFolder (IMS_SuperGroupName(), $data["loc"]), $currentfolder)) {

               // ###
               global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
               $time = time();
               $guid = N_GUID();
               $object["history"][$guid]["type"] = "move";
               $object["history"][$guid]["when"] = $time;
               $object["history"][$guid]["author"] = SHIELD_CurrentUser ($input["supergroupname"]);
               $object["history"][$guid]["server"] = N_CurrentServer ();
               $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
               $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
               $object["history"][$guid]["server_addr"] = $SERVER_ADDR;
               $object["history"][$guid]["from"] = IMS_GetDMSDocumentPath($input["supergroupname"], $object["directory"]);
               $object["history"][$guid]["to"]   = IMS_GetDMSDocumentPath($input["supergroupname"], $data["loc"]);

               $object ["directory"] = $data["loc"];
               $gotook = "closeme&parentgoto:/openims/openims.php?mode=dms&currentfolder=".$data["loc"];

            } else {
              FORMS_ShowError (ML("Foutmelding","Error"), ML("U heeft niet voldoende rechten","You have insufficient rights"), true);
            }
          ';
          $url = FORMS_URL ($form);
          echo '<a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/move_small.gif"> '.ML("Verplaats","Move").'</a><br>';
        }

        global $myconfig;
        $ext = strtolower (FILES_FileType ($supergroupname, $key, "preview"));

        if (FILES_IsPermalink ($supergroupname, $truecurrentobject)) {
          if (FILES_HistoryVersionExistsOnDisk($supergroupname,$key, $permaobject["sourceversion"])) {
            $permaobject = MB_Ref("ims_".$supergroupname."_objects", $truecurrentobject);
            $myurl = FILES_DocHistoryURL ($supergroupname, $truecurrentobject, $permaobject["sourceversion"]);
              $title = ML("Hyperlink naar deze versie","Hyperlink to this version");
              if ($myurl) {
                echo '<a class="ims_navigation" title="'.$title.'" href="'.$myurl.'"><img border=0 src="/ufc/rapid/openims/hyperlink.gif">&nbsp;'.$title.'</a>&nbsp<br/>';
              }
          }

        } else {
          if (FILES_DocPreviewURL ($supergroupname, $key) && SHIELD_HasGlobalRight ($supergroupname, "preview") && SHIELD_HasObjectRight ($supergroupname, $key, "view") && $myconfig[$supergroupname]["hidedmsufclinks"] != "yes") {
            $myurl = FILES_DocPreviewURL ($supergroupname, $key);
            $title = ML("Directe hyperlink naar laatste versie","Direct hyperlink to latest version").' &quot;'.$object["shorttitle"].'&quot; ('.ML("voor download of hyperlinks","for download or hyperlinks").')';

            // only in IE open in new window
            $target = '';
            if (N_IE()) $target = ' target="_blank" ';

            echo '<a '.$target.' class="ims_navigation" title="'.$title.'" href="'.$myurl.'"><img border=0 src="/ufc/rapid/openims/find.gif"> '.ML("Concept versie","Preview version").'</a> ';

//            $neeviadocformats = $myconfig["neeviadocformats"];

//            if (!is_array($neeviadocformats)) $neeviadocformats = array("doc", "xls", "vsd", "ppt", "dwg", "docx", "xlsx", "pptx");
//            if ($myconfig[IMS_SuperGroupName()]["autopdf"]=="yes" && ($myconfig["neevia"]!="yes" && ($ext=="doc"||($myconfig["docxconversion"] == "yes" && $ext=="docx"))) || (($myconfig["neevia"]=="yes") && in_array($ext, $neeviadocformats))) {
              if ( WORD_isConvertableToPDFwithCurrentSettings( $supergroupname , $ext ) ) {
              $mypdfurl = FILES_DocPreviewURL ($supergroupname, $key, true);
              $pdftitle = ML("Directe hyperlink naar laatste versie / in PDF formaat","Direct hyperlink to latest version / as PDF document").' &quot;'.$object["shorttitle"].'&quot; ('.ML("voor download of hyperlinks","for download or hyperlinks").')';
              if ($myconfig[IMS_SuperGroupName()]["pdflinkbold"]=="yes")
                      echo '(<a '.$target.' class="ims_navigation" title="'.$pdftitle.'" href="'.$mypdfurl.'"><b>'.pdf.'</b></a>)';
                    else
                      echo '(<a '.$target.' class="ims_navigation" title="'.$pdftitle.'" href="'.$mypdfurl.'">'.pdf.'</a>)';
            }
            if ($myconfig[IMS_SuperGroupName()]["autovisiohtml"]=="yes" && $ext=="vsd") {
              $myhtmlurl = FILES_DocPreviewURL ($supergroupname, $key, "html");
              $htmltitle = ML("Directe hyperlink naar laatste versie / in HTML formaat","Direct hyperlink to latest version / in HTML format").' &quot;'.$object["shorttitle"].'&quot; ('.ML("voor download of hyperlinks","for download or hyperlinks").')';
              echo '(<a '.$target.' class="ims_navigation" title="'.$htmltitle .'" href="'.$myhtmlurl .'">'.html.'</a>)';
            }
            if (($myconfig[IMS_SuperGroupName()]["autohtml"] != "") && ($object["meta_autohtml"]."" != "") && ($ext=="doc"||($myconfig["docxconversion"] == "yes" && $ext=="docx")))             {
              uuse("webgen");
              $myhtmlurl = WEBGEN_URL (IMS_SuperGroupName(), $key, true);
              // don't open javascript in new window
              if (strpos($myhtmlurl, "function") > 0) $target = '';

              $htmltitle = ML("Directe hyperlink naar laatste versie / in HTML formaat","Direct hyperlink to latest version / in HTML format").' &quot;'.$object["shorttitle"].'&quot; ('.ML("voor download of hyperlinks","for download or hyperlinks").')';
              echo ' (<a '.$target.' class="ims_navigation" title="'.$htmltitle.'" href="'.$myhtmlurl.'">'.html.'</a>)';
            }
            echo '<br>';
          }

          if (FILES_DocPublishedURL ($supergroupname, $key) && $myconfig[$supergroupname]["hidedmsufclinks"] != "yes") {
            $ext = strtolower (FILES_FileType ($supergroupname, $key, "published"));
            $myurl = FILES_DocPublishedURL ($supergroupname, $key);
            $title = ML("Directe hyperlink naar de laatste volledig goedgekeurde versie","Direct hyperlink to the latest approved version").' &quot;'.$object["shorttitle"].'&quot; ('.ML("voor download of hyperlinks","for download or hyperlinks").')';

            // only in IE open in new window
            $target = '';
            if (N_IE()) $target = ' target="_blank" ';

            echo '<a '.$target.' class="ims_navigation" title="'.$title.'" href="'.$myurl.'"><img border=0 src="/ufc/rapid/openims/hyperlink.gif"> '.ML("Gepubliceerde versie","Published version").'</a> ';

//          $neeviadocformats = $myconfig["neeviadocformats"];
//          if (!is_array($neeviadocformats)) $neeviadocformats = array("doc", "xls", "vsd", "ppt", "dwg","docx", "xlsx", "pptx");
//          if ($myconfig[IMS_SuperGroupName()]["autopdf"]=="yes" && ($myconfig["neevia"]!="yes" && ($ext=="doc"||($myconfig["docxconversion"] == "yes" && $ext=="docx"))) || (($myconfig["neevia"]=="yes") && in_array($ext, $neeviadocformats))) {
            if ( WORD_isConvertableToPDFwithCurrentSettings( $supergroupname , $ext ) ) {
              $mypdfurl = FILES_DocPublishedURL ($supergroupname, $key, true);
              $pdftitle = ML("Directe hyperlink naar de laatste volledig goedgekeurde versie / in PDF formaat","Direct hyperlink to the latest approved version / as PDF document").' &quot;'.$object["shorttitle"].'&quot; ('.ML("voor download of hyperlinks","for download or hyperlinks").')';
              if ($myconfig[IMS_SuperGroupName()]["pdflinkbold"]=="yes")
                      echo '(<a '.$target.' class="ims_navigation" title="'.$pdftitle.'" href="'.$mypdfurl.'"><b>'.pdf.'</b></a>)';
                    else
                echo '(<a '.$target.' class="ims_navigation" title="'.$pdftitle.'" href="'.$mypdfurl.'">'.pdf.'</a>)';
            }
            if ($myconfig[IMS_SuperGroupName()]["autovisiohtml"]=="yes" && $ext=="vsd") {
              $myhtmlurl = FILES_DocPublishedURL ($supergroupname, $key, "html");
              $htmltitle = ML("Directe hyperlink naar laatste versie / in HTML formaat","Direct hyperlink to latest version / in HTML format").' &quot;'.$object["shorttitle"].'&quot; ('.ML("voor download of hyperlinks","for download or hyperlinks").')';
              echo '(<a '.$target.' class="ims_navigation" title="'.$htmltitle .'" href="'.$myhtmlurl .'">'.html.'</a>)';
            }
            if (($myconfig[IMS_SuperGroupName()]["autohtml"] != "") && ($object["meta_autohtml"]."" != "") && ($ext=="doc"||($myconfig["docxconversion"] == "yes" && $ext=="docx")))               {
               uuse("webgen");
              $myhtmlurl = WEBGEN_URL (IMS_SuperGroupName(), $key, false);
              $htmltitle = ML("Directe hyperlink naar de laatste volledig goedgekeurde versie / in HTML formaat","Direct hyperlink to the latest approved version / in HTML format").' &quot;'.$object["shorttitle"].'&quot; ('.ML("voor download of hyperlinks","for download or hyperlinks").')';
              echo ' (<a '.$target.' class="ims_navigation" title="'.$htmltitle.'" href="'.$myhtmlurl.'">'.html.'</a>)';
            }
            echo '<br>';
          }
        }

        if ($myconfig[$supergroupname]["hiderevokelink"] !== "yes" && $object["preview"]=="yes" && $object["published"]=="yes" && SHIELD_HasObjectRight ($supergroupname, $key, "edit")) {
          $title = ML("Terugtrekken van","Revoke")." &quot;".$object["shorttitle"]."&quot;" . " " . ML("(ga terug naar de gepubliceerde versie)", "(return to published version)");
          echo '<a class="ims_navigation" title="'.$title.'" href="/openims/action.php?goto='.urlencode($goto).'&command=unpublish&sitecollection_id='.$supergroupname.'&object_id='.$key.'"><img border=0 src="/ufc/rapid/openims/revoke_small.gif"> '.ML("Terugtrekken","Revoke").'</a><br>';
        }

        if (SHIELD_HasGlobalRight ($supergroupname, "newdoc", $mysecuritysection) && ($submode=="documents" || $submode=="projects") && SHIELD_HasObjectRight ($supergroupname, $key, "view")) {
         if (!($myconfig[$supergroupname]["multifile"] == "yes" && $myconfig[$supergroupname]["hidecopylink"] == "yes")) {
          // only hide copy link when multifile is set to yes
          $title = ML("Maak kopie van","Copy")." &quot;".$object["shorttitle"]."&quot;";
          $form = array();
          $form["title"] = $title;
          $form["input"]["key"] = $key;
          $form["input"]["sitecollection_id"] = $supergroupname;
          $form["input"]["directory_id"] = $currentfolder;
          $form["metaspec"]["fields"]["shorttitle"]["type"] = "strml4";

          $form["metaspec"]["fields"]["longtitle"]["type"] = "strml4";
          $form["metaspec"]["shorttitle"]["type"] = "strml4";

          $form ["metaspec"]["fields"]["workflow"]["type"] = "list";
          $form ["metaspec"]["fields"]["workflow"]["default"] = $object["workflow"];
          $form ["metaspec"]["fields"]["workflow"]["sort"] = "yes";

          $wlist = SHIELD_DMSWorkFlows();

          $allowed = SHIELD_AllowedWorkflows ($supergroupname, "", $mysecuritysection);
          if (is_array($wlist)) reset($wlist);
          if (is_array($wlist)) while (list($wkey)=each($wlist)) {
            if ($allowed[$wkey]) {
              $workflow = &MB_Ref ("shield_".$supergroupname."_workflows", $wkey);
              $form ["metaspec"]["fields"]["workflow"]["values"][$workflow["name"]] = $wkey;
            }
          }
          $form ["formtemplate"] = '<body bgcolor=#f0f0f0><br><center><table>
                            <tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[shorttitle]]]</td></tr>
                            <tr><td><font face="arial" size=2><b>'.ML("Omschrijving","Description").':</b></font></td><td>[[[longtitle]]]</td></tr>
                            <tr><td><font face="arial" size=2><b>'.ML("Workflow","Workflow").':</b></font></td><td>[[[workflow]]]</td></tr>
                            <tr><td colspan=2>&nbsp</td></tr>
                            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                            </table></center></body>';
          $form["postcode"] = '
            $id = IMS_NewDocumentObject ($input["sitecollection_id"], $input["directory_id"]);
            $object = &IMS_AccessObject ($input["sitecollection_id"], $id);
            $sourceobject = &IMS_AccessObject ($input["sitecollection_id"], $input["key"]);
            N_CopyDir ("html::".$input["sitecollection_id"]."/preview/objects/".$id."/", "html::".$input["sitecollection_id"]."/preview/objects/".$input["key"]."/");
            $object["shorttitle"] = $data["shorttitle"];
            $object["longtitle"] = $data["longtitle"];
            $object["workflow"] = $data["workflow"];
            $object["executable"] = $sourceobject ["executable"];
            $object["filename"] = $sourceobject ["filename"];
            $object["dynmeta"] = $sourceobject["dynmeta"];
            IMS_ArchiveObject ($input["sitecollection_id"], $id, SHIELD_CurrentUser ($input["sitecollection_id"]), true);
            SEARCH_AddPreviewDocumentToDMSIndex ($input["sitecollection_id"], $id);
            $gotook="closeme&parentgoto:' . "/openims/openims.php?mode=dms&submode=$submode&currentfolder=$currentfolder&currentobject=\$id".'";
          ';
          $url = FORMS_URL ($form);
          echo '<a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/copy_small.gif"> '.ML("Kopie","Copy").'</a><br>';
         }
        }

 
        // shortcutsscreen option

        $list = MB_TurboSelectQuery ("ims_".IMS_SupergroupName()."_objects", '$record["source"]', $currentobject);
        $nrsc = count($list);
        if ($nrsc and $currentobject and !FILES_IsShortcut(IMS_Supergroupname(), $truecurrentobject)) {
          $title = ML("Snelkoppelingen van","Shortcuts off")." &quot;".$object["shorttitle"]."&quot;";
          echo '<a class="ims_navigation" title="'.$title.'" href="/openims/openims.php?mode=shortcuts&back='.urlencode($goto).'&object_id='.$currentobject.'"><img border=0 src="/ufc/rapid/openims/shortcut_small.gif"> '.ML("Snelkoppeling","Shortcut").' ('.$nrsc.')</a><br>';
        }

        if (SHIELD_HasGlobalRight ($supergroupname, "doctemplateedit")) {
          $title = ML("Kopieer","Copy")." &quot;".$object["shorttitle"]."&quot; ".ML("naar document templates (nieuw document)","to document templates (new document)");
          $form = array();
          $form["title"] = $title;
          $form["input"]["key"] = $currentobject;
          $form["input"]["sitecollection_id"] = $supergroupname;
          $form["input"]["directory_id"] = $currentfolder;
          $form["metaspec"]["fields"]["shorttitle"]["type"] = "strml4";
          $form["metaspec"]["fields"]["longtitle"]["type"] = "strml4";
          $form["metaspec"]["shorttitle"]["type"] = "strml4";
          $form ["formtemplate"] = '<body bgcolor=#f0f0f0><br><center><table>
                            <tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[shorttitle]]]</td></tr>
                            <tr><td><font face="arial" size=2><b>'.ML("Omschrijving","Description").':</b></font></td><td>[[[longtitle]]]</td></tr>
                            <tr><td colspan=2>&nbsp</td></tr>
                            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                            </table></center></body>';
          $form["postcode"] = '
            $id = IMS_NewObject ($input["sitecollection_id"], "doctemplate");
            $object = &IMS_AccessObject ($input["sitecollection_id"], $id);
            $sourceobject = &IMS_AccessObject ($input["sitecollection_id"], $input["key"]);
            $doc = FILES_TrueFileName ($input["sitecollection_id"], $input["key"], "preview");
            N_CopyDir ("html::".$input["sitecollection_id"]."/preview/objects/".$id."/", "html::".$input["sitecollection_id"]."/preview/objects/".$input["key"]."/");
            $object["shorttitle"] = $data["shorttitle"];
            $object["longtitle"] = $data["longtitle"];
            $object["executable"] = $sourceobject ["executable"];
            $object["filename"] = $doc;
            IMS_PublishObject ($input["sitecollection_id"], "", $id);
          ';
          $url = FORMS_URL ($form);
          echo '<a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/copy_small.gif"> '.ML("Template","Template").'</a><br>';
        }

        if ((SHIELD_HasObjectRight ($supergroupname, $key, "delete") && SHIELD_HasObjectRight ($supergroupname, $key, "view")) or (SHIELD_HasObjectRight ($supergroupname, $key, "deleteconcept") and $object["published"] == "no" ))
        {  // "view" right check needed because you may see this block only because of "view" right on $truecurrentobject
         if ( $myconfig[$supergroupname]["hide_delete_document_on_shortcuts"]!="yes" || !FILES_IsShortcut ($supergroupname, $truecurrentobject) )
         {
          $title = ML("Verwijder document","Delete file")." &quot;".$object["shorttitle"]."&quot;";

          $form = array();
          $form["title"] = $title;
          $form["input"]["sitecollection_id"] = $supergroupname;
          $form["input"]["object_id"]         = $key;
          $form["metaspec"]["fields"]["sure"]["type"] = "list";
          $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";

          $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
          if ( $myconfig[IMS_SupergroupName()]["prevent_delete_files_with_shortcut"]=="yes" )
          {
            $form["precode"] = '
            $object = MB_load("ims_" . $input["sitecollection_id"]. "_objects" , $input["object_id"] );			
            if ( count( FILES_documentShortcuts( $input["sitecollection_id"] , $input["object_id"] ) ) > 0 ) 
            {
              $formtemplate = \'<table>
              <tr><td><font face="arial" size=2>\'.ML("Document %1 heeft snelkoppelingen en kan niet worden verwijderd.","Document %1 has shortcuts and can not be deleted.", "<b>".$object["shorttitle"]."</b>").\'
              <tr><td colspan=2><center><nobr>[[[CANCEL:\'.ML(\'Sluiten\',\'Close\').\']]]</nobr></center></td></tr>
              </table>\';
            }
            ';
          }
          if (LINK_HasLinks  (IMS_SuperGroupName(),$currentobject)) {
            $form["formtemplate"] = '
              <table>
              <tr><td><font face="arial" size=2>'.ML("Wilt u het bestand %1 verwijderen?","Do you want to delete the file %1?", "<b>".$object["shorttitle"]."</b>").
                 '</font></td></tr>';
              if($myconfig[$supergroupname]["extrawarningondelete"] != "no")$form["formtemplate"] .= '<tr><td><font color="#ff0000" face="arial" size=2>'.ML("Dit bestand heeft koppelingen met andere bestanden!<b>","This file has links to other documents!</b>").
                 '</font></td></tr>';
              $form["formtemplate"] .= '<tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center><nobr>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</nobr></center></td></tr>
              </table>
            ';
          } else {
            $form["formtemplate"] = '
              <table>
              <tr><td><font face="arial" size=2>'.ML("Wilt u het bestand %1 verwijderen?","Do you want to delete the file %1?", "<b>".$object["shorttitle"]."</b>").
                 '</font></td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center><nobr>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</nobr></center></td></tr>
              </table>
            ';
          }
          $form["postcode"] = '
            uuse ("ims");
            if (IMS_IsLocked ($input["sitecollection_id"], $input["object_id"])) {
              FORMS_ShowError (ML("Foutmelding", "Error"), IMS_IsLocked ($input["sitecollection_id"], $input["object_id"]), false);
            }
            IMS_Delete ($input["sitecollection_id"], "", $input["object_id"]);
          ';
          $url = FORMS_URL ($form);
          echo '<a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/delete_small.gif"> '.ML("Verwijder document","Delete file").'</a><br>';
        }
        }

        global $myconfig;

        if($myconfig[$supergroupname]["deleteshortcutwithviewrightonsource"]=="yes") {
          $HasObjectRightSource = SHIELD_HasObjectRight ($supergroupname, $key, "view");
        }else {
          $HasObjectRightSource = SHIELD_HasObjectRight ($supergroupname, $key, "delete");
        }

        if (($HasObjectRightSource and FILES_IsShortcut ($supergroupname, $truecurrentobject)) or
            ($myconfig[$supergroupname]["permalinkalwaysviewdoc"] == "yes" and SHIELD_HasObjectRight ($supergroupname, $truecurrentobject, "delete") and FILES_IsPermalink ($supergroupname, $truecurrentobject))) {
          $title = ML("Verwijder snelkoppeling","Delete shortcut")." &quot;".$object["shorttitle"]."&quot;";
          $form = array();
          $form["title"] = $title;
          $form["input"]["sitecollection_id"] = $supergroupname;
          $form["input"]["object_id"]         = $key;
          $form["input"]["trueobject_id"]     = $truecurrentobject;
          $form["metaspec"]["fields"]["sure"]["type"] = "list";
          $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
          $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
          $form["formtemplate"] = '
            <table>
            <tr><td><font face="arial" size=2>'.ML("Wilt u de snelkoppeling naar %1 verwijderen?","Do you want to delete the shortcut to %1?", "<b>".$object["shorttitle"]."</b>").
               '</font></td></tr>
            <tr><td colspan=2>&nbsp</td></tr>
            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          ';
          $form["postcode"] = '
            uuse ("ims");
            if (IMS_IsLocked ($input["sitecollection_id"], $input["object_id"])) {
              FORMS_ShowError (ML("Foutmelding", "Error"), IMS_IsLocked ($input["sitecollection_id"], $input["object_id"]), false);
            }
            IMS_Delete ($input["sitecollection_id"], "", $input["trueobject_id"]);
          ';
          $url = FORMS_URL ($form);

          echo '<a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/delete_small.gif"> '.ML("Verwijder snelkoppeling","Delete shortcut").'</a><br>';
        }

        // code for "go to folder" in DMS
        if (($mode=="dms") && ((FILES_IsShortcut ($supergroupname, $truecurrentobject)) || ($submode=="activities") || ($submode=="alloced")) && (SHIELD_HasObjectRight ($supergroupname, $key, "view"))) {
          $url = FILES_DMSURL ($supergroupname, $currentobject);
          $thecurrentfolder = $object["directory"];
          $tree = CASE_TreeRef ($supergroupname, $thecurrentfolder);
          $thefolder = TREE_AccessObject($tree, $thecurrentfolder);
          if (TREE_Visible($thecurrentfolder, $thefolder)) {

            $path = TREE_Path ($tree, $thecurrentfolder);

            if (substr ($thecurrentfolder, 0, 1)=="(") {
              $case_id = substr ($thecurrentfolder, 0, strpos ($thecurrentfolder, ")")+1);
              $case = MB_Ref ("ims_".IMS_SuperGroupName()."_case_data", $case_id);
             $fullpath  = $case["shorttitle"]."&gt;&gt; ";
            } else {
              $fullpath = "";
            }
            for($i=1; $i<=count($path); $i++) {
              $fullpath .= $path[$i]["shorttitle"] . ' &gt; ';
            }
            print('<a class="ims_navigation" title="' . ML("Ga naar basis folder", "Go to base folder") . " " . $fullpath . $object["shorttitle"] . '" href="'.$url .'"><img border=0 src="/ufc/rapid/openims/folder.gif"> '.ML("Naar basis folder","To base folder") . '</a><br>');
          }
        }

    if ($myconfig[$supergroupname]["multiapprove"] == "yes")
    {
       uuse("multiapprove");
       $str = MULTIAPPROVE_Status();
       echo $str;
    }

    if ($myconfig[$supergroupname]["doctopdfoption"] == "yes" and SHIELD_HasObjectRight($supergroupname, $key, "edit"))
    {
       uuse("doctopdf");
       $str = DOCTOPDF_Option();
       echo $str;
    }

    echo DMSUIF_ShowWizards(IMS_SuperGroupName(), $dmswizards, "actions", $dummy);

    endblock();

    $helpspecs = "";
    if ($myconfig[IMS_SuperGroupName()]["workflowhelpicon"]) {
      $workflow = &SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);
      if ($workflow["helplink"]) {
        $helpspecs = array ($workflow["helplink"], $workflow["helptext"]);
      }
    }

    if (SHIELD_HasObjectRight ($supergroupname, $key, "view")) {

    startblock (ML("Document workflow","Document workflow"), "action", $helpspecs);

    $bwf = false;
    uuse("workflow");
    $opt_arr = WORKFLOW_Options($supergroupname, $key, $object, true, $myconfig[IMS_SuperGroupName()]["autocompletealloc"] == "yes");
    if ($opt_arr)
      $bwf = true;
      
    if ($myconfig[IMS_SuperGroupName()]["multiapprove"] == "yes")
    {
      uuse("multiapprove");
      $str = MULTIAPPROVE_ShowChoices($bwf);
      if ($str)
        echo $str;
    }
      
    foreach ($opt_arr as $url => $option)
      echo '&nbsp;<a class="ims_navigation" href="'.$url.'">'.$option.'</a><br>';

    if ($workflow["alloc"] && SHIELD_HasObjectRight ($supergroupname, $key, "reassign"))
    {
          $options = SHIELD_AllowedOptions ($supergroupname, $key);
          if (is_array($options) && count($options)>0) {
            $option = "#reassign#";
            if($customfield["type"] !== "list") $alloc = '[[[alloc:'.$key.','.$option.']]]';
      else $alloc = '[[[alloc]]]';
            $form = array();
            $form["input"]["col"] = $supergroupname;
            $form["input"]["id"] = $key;
            $form["input"]["user"] = SHIELD_CurrentUser ($supergroupname);
            $form["input"]["opt"] = $option;
            $form["input"]["customfield"] = $customfield;
            $form["input"]["user_id"] = SHIELD_CurrentUser($supergroupname);
            $form["title"] = ML("Heralloceren", "Reassign");
            $form["metaspec"]["fields"]["comment"]["type"] = "text";
            $form["metaspec"]["fields"]["signal"]["type"] = "yesno";
            if($customfield) $form["metaspec"]["fields"]["alloc"] = $customfield;
            else $form["metaspec"]["fields"]["alloc"]["type"] = "list";
            if ($workflow["alloc"] && $workflow["stages"]!=$workflow[$object["stage"]]["#".$option]) {
              $form["input"]["validateallocfield"] = "yes";
              $form["formtemplate"] = '
                <table>
                  <tr><td><font face="arial" size=2><b>'.ML("Toewijzen aan","Allocate to").':</b></font></td><td>'.$alloc.'</td></tr>
                  <tr><td><font face="arial" size=2><b>'.ML("Signaal sturen","Send signal").':</b></font></td><td>[[[signal]]]</td></tr>
                  <tr><td colspan=2><font face="arial" size=2><b>'.ML("Opmerkingen","Comments").':</b></font></td></tr>
                  <tr><td colspan=2>[[[comment]]]</td></tr>
                  <tr><td colspan=2>&nbsp</td></tr>
                  <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                </table>

              ';
            } else {
              $form["formtemplate"] = '
                <table>
                  <tr><td colspan=2><font face="arial" size=2><b>'.ML("Opmerkingen","Comments").':</b></font></td></tr>
                  <tr><td colspan=2>[[[comment]]]</td></tr>
                  <tr><td colspan=2>&nbsp</td></tr>
                  <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                </table>
              ';
            }
            $form["precode"] = '
              global $myconfig;
              $customfield = $input["customfield"];
              if ($myconfig[IMS_SuperGroupName()]["signaldefaulttrue"] == "yes") {
                $data["signal"] = true;
              }
              if(($customfield["type"]=="list")||(!$customfield)) {
                $users = SHIELD_AssignableUsers (IMS_SuperGroupName(), $input["id"], $input["opt"]);
                if ($myconfig[IMS_SuperGroupName()]["reassigndefaultstoempty"] == "yes") {
                  $metaspec["fields"]["alloc"]["values"][ML("Kies...", "Choose...")] = "";
                }
                foreach ($users as $user_id => $name) {
                  $metaspec["fields"]["alloc"]["values"][$name] = $user_id;
                }
        }
              if ($myconfig[IMS_SuperGroupName()]["reassigndefaultstoempty"] != "yes") $data["alloc"] = $input["user"];
            ';
            $form["postcode"] = '
              if ($input["validateallocfield"])
                if (!$data["alloc"]) FORMS_ShowError(ML("Fout", "Error"), ML("Bij het veld &quot;Toewijzen aan:&quot; is geen gebruiker gekozen", "No user was chosen in the field &quot;Allocate to:&quot;"));
              $history["user_id"] = $input["user_id"];
              $history["timestamp"] = time();
              $history["comment"] = $data["comment"];
              SHIELD_ProcessOption ($input["col"], $input["id"], $input["opt"], $history, $data["alloc"], $data["signal"]);
            ';
            $url = FORMS_URL ($form);
            echo '&nbsp;<a class="ims_navigation" href="'.$url.'">'.ML("Heralloceren", "Reassign").'</a><br>';
            $bwf = true;
          }
        }
        
        $showw = DMSUIF_ShowWizards(IMS_SuperGroupName(), $dmswizards, "workflow", $dummy);
        if ($showw)
        {
          echo $showw;
          $bwf = true;
        }
        echo " ";
    
      endblock();
    }
    

    }


    $wizardcount = 0;
    $wizardstring = DMSUIF_ShowWizards(IMS_SuperGroupName(), $dmswizards, ($currentobject ? "wizards_active" : "wizards"), $wizardcount);
    global $myconfig;
    $legacywizardstring = "";
    if (($submode != "cases") && ($submode != "dmsview") && ($submode != "autotableview") && is_array($myconfig[IMS_Supergroupname()]["dmswizzard"])) foreach ($myconfig[IMS_Supergroupname()]["dmswizzard"] as $i => $specs) {
      eval ($specs["condition"]);
      if ($result) {
        $wizardcount++;
        eval ($specs["url"]);
        $legacywizardstring .= "<a class=\"ims_navigation\" title=\"".$specs["title"]." (classic)\" href=\"$result\"><img border=0 src=\"/ufc/rapid/openims/wand.gif\">&nbsp;".$specs["name"]."</a><br>";
      }
    }
    if ($wizardcount) {
      if ($wizardcount > 1) {
        startblock (ML("Assistenten", "Wizards"), "action");
      } else {
        startblock (ML("Assistent", "Wizard"), "action");
      }
      echo $wizardstring;
      echo $legacywizardstring;


      endblock();
    }
  }

  // LF: start multifileblock

  global $myconfig, $selectcounter;
  if ($myconfig[IMS_SuperGroupName()]["multifile"]=="yes" &&
      ($submode=="documents" || ($myconfig[IMS_SuperGroupName()]["multifileblock_in_related"] == "yes" && $mode=="related") || 
      ($myconfig[IMS_SuperGroupName()]["multifileblock_in_alloced"] == "yes" && $submode=="alloced") ||
      ($myconfig[IMS_SuperGroupName()]["multifileblock_in_advancedsearch"] == "yes" && $submode=="search" && $searchmode=="advanced"))) {

    // LF: Discussiepunt: is dit de beste manier om het te configureren?  What about activities/"In behandeling"?

    uuse ("dhtml");
    startblock (DHTML_InvisiTable (T_Value("portal_action", "td-head-init"), T_Value("portal_action", "td-head-exit"), "<b>".ML("Documenten", "Files")."</b> (&nbsp;", DHTML_DynamicObject ($selectcounter, "selectcounter"), "&nbsp;)"), "action");

    $stat = "";

    if ($submode=="documents") {
      $form = array();
      $form["title"] = ML("Selecteer documenten","Select files");
      $form["input"]["currentfolder"] = $currentfolder;
      $form["input"]["treename"] = IMS_SuperGroupName()."_documents";
      $form["metaspec"]["fields"]["scope"]["type"] = "list";
      $form["metaspec"]["fields"]["scope"]["values"][ML("Huidige folder","Current folder")] = "";
      $form["metaspec"]["fields"]["scope"]["values"][ML("Huidige en onderliggende folders","Current folder and subfolders")] = "subs";
      $form["formtemplate"] = '
        <table>
          <tr><td><font face="arial" size=2><b>'.ML("Scope","Scope").':</b></font></td><td>[[[scope]]]</td></tr>
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
        </table>
      ';
      $form["postcode"] = '
         uuse ("multi");
         uuse ("tree");
         if ($data["scope"]=="subs") {
           $tree = CASE_TreeRef (IMS_SuperGroupName(), $input["currentfolder"]);
           function tmp_walk ($tree, $currentfolder) {
             $list = MB_TurboSelectQuery ("ims_".IMS_SuperGroupName()."_objects",
               array(\'$record["directory"]\', \'$record["published"]=="yes" || $record["preview"]=="yes"\'),
               array($currentfolder, true));
             if (is_array($list)) foreach ($list as $key => $dummy) {
               MULTI_Select ($key);
             }
             $object = &TREE_AccessObject($tree, $currentfolder);
             if (is_array($object["children"])) foreach ($object["children"] as $id => $dummy) {
               tmp_walk ($tree, $id);
             }
           }
           tmp_walk ($tree, $input["currentfolder"]);
         } else {
           $list = MB_TurboSelectQuery ("ims_".IMS_SuperGroupName()."_objects",
             array(\'$record["directory"]\', \'$record["published"]=="yes" || $record["preview"]=="yes"\'),
             array($input["currentfolder"], true));
           if (is_array($list)) foreach ($list as $key => $dummy) {
             MULTI_Select ($key);
           }
         }
      ';
      $url = FORMS_URL ($form);

      $stat .= "<a class=\"ims_navigation\" title=\"".ML("Selecteer documenten","Select files")."\" href=\"$url\">";
      $stat .= '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/toggle_on_ico.jpg"> '.ML("Selecteren","Select");
      $stat .= "</a> (";
    }

    if ($submode=="documents") {
      $form = array();
      $form["input"]["securitysection"] = $securitysection;
      $form["input"]["currentfolder"] = $currentfolder;
      $form["input"]["treename"] = IMS_SuperGroupName()."_documents";
      $form["precode"] = '
        uuse ("multi");
        $list = MB_TurboSelectQuery ("ims_".IMS_SuperGroupName()."_objects",
          array(\'$record["directory"]\', \'$record["published"]=="yes" || $record["preview"]=="yes"\'),
          array($input["currentfolder"], true), \'$record["shorttitle"]\');
        if (is_array($list)) foreach ($list as $key => $dummy) {
          MULTI_Select ($key);
        }
      ';
      $url = FORMS_URL ($form);
      $stat .= "<a class=\"ims_navigation\" title=\"".ML("Selecteer alle documenten in deze folder","Select all files in this folder")."\" href=\"$url\">";
      $stat .= ML("folder", "folder");
      if (strpos(N_MyFullUrl(), "select=page") === false)
        $urlp = N_MyFullUrl() . "&select=page";
      else
        $urlp = N_MyFullUrl();
      $stat .= "</a> <a class=\"ims_navigation\" title=\"".ML("Selecteer alle documenten op deze pagina","Select all files on this page")."\" href=\"$urlp\">";       
      $stat .= ML("pagina", "page");
      $stat .= ")</a><br>";

      $form = array();
      $form["title"] = ML("De-selecteer documenten","Deselect files");
      $form["input"]["currentfolder"] = $currentfolder;
      $form["input"]["treename"] = IMS_SuperGroupName()."_documents";
      $form["metaspec"]["fields"]["scope"]["type"] = "list";
      $form["metaspec"]["fields"]["scope"]["values"][ML("Huidige folder","Current folder")] = "";
      $form["metaspec"]["fields"]["scope"]["values"][ML("Huidige en onderliggende folders","Current folder and subfolders")] = "subs";
      $form["metaspec"]["fields"]["scope"]["values"][ML("Alle documenten","All files")] = "all";
      $form["formtemplate"] = '
        <table>
          <tr><td><font face="arial" size=2><b>'.ML("Scope","Scope").':</b></font></td><td>[[[scope]]]</td></tr>
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
        </table>
      ';
      $form["postcode"] = '
         uuse ("multi");
         uuse ("tree");
         if ($data["scope"]=="subs") {
           $tree = CASE_TreeRef (IMS_SuperGroupName(), $input["currentfolder"]);
           function tmp_walk ($tree, $currentfolder) {
             $list = MB_TurboSelectQuery ("ims_".IMS_SuperGroupName()."_objects",
               array(\'$record["directory"]\', \'$record["published"]=="yes" || $record["preview"]=="yes"\'),
               array($currentfolder, true));
             if (is_array($list)) foreach ($list as $key => $dummy) {
               MULTI_Unselect ($key);
             }
             $object = &TREE_AccessObject($tree, $currentfolder);
             if (is_array($object["children"])) foreach ($object["children"] as $id => $dummy) {
               tmp_walk ($tree, $id);
             }
           }
           tmp_walk ($tree, $input["currentfolder"]);
         } else if ($data["scope"] == "all") {
           MULTI_UnselectAll ();
         } else {
           $list = MB_TurboSelectQuery ("ims_".IMS_SuperGroupName()."_objects",
             array(\'$record["directory"]\', \'$record["published"]=="yes" || $record["preview"]=="yes"\'),
             array($input["currentfolder"], true));
           if (is_array($list)) foreach ($list as $key => $dummy) {
             MULTI_Unselect ($key);
           }
         }
      ';
      $url = FORMS_URL ($form);

      $dyn = "<a class=\"ims_navigation\" title=\"".ML("De-selecteer documenten","Unselect files")."\" href=\"$url\">";
      $dyn .= '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/toggle_off_ico.jpg"> '.ML("De-selecteren","Unselect");
      $dyn .= "</a> (";
    }

    $form = array();
    $form["input"]["currentfolder"] = $currentfolder;
    $form["input"]["treename"] = IMS_SuperGroupName()."_documents";
    $form["precode"] = '
      uuse ("multi");
      MULTI_UnselectAll ();
    ';
    $url = FORMS_URL ($form);

    if ($submode=="documents") {
      $dyn .= "<a class=\"ims_navigation\" title=\"".ML("De-selecteer alle geselecteerde documenten","Unselect all selected files")."\" href=\"$url\">";
      $dyn .= ML("alle", "all");
      $dyn .= "</a>)<br>";
    } else {
      $dyn .= "<a class=\"ims_navigation\" title=\"".ML("De-selecteer alle geselecteerde documenten","Unselect all selected files")."\" href=\"$url\">";
      $dyn .= '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/toggle_off_ico.jpg"> '.ML("De-selecteer alles","Unselect all");
      $dyn .= "</a><br>";
    }

    // jh 15-7-2010 tbv ctgb
    $form = array();
    $form["input"]["currentfolder"] = $currentfolder;
    $form["input"]["treename"] = IMS_SuperGroupName()."_documents";
    $form["input"]["object_id"] = $object_id;
    $form["precode"] = '
      uuse ("multi");
      uuse("link");

      $keys = LINK_LinkedObjects($input["object_id"]);
      foreach ($keys as $key => $dummy)
        MULTI_Select($key);
    ';
    $url = FORMS_URL ($form);

    if ($mode=="related")
    {
      $stat .= "<a class=\"ims_navigation\" title=\"".ML("Selecteer alle gekoppelde documenten","Select all linked files")."\" href=\"$url\">";
      $stat .= '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/toggle_on_ico.jpg"> '.ML("Selecteer alle gekoppelden","Select all linked");
      $stat .= "</a><br>";
    }
    // einde jh

    if ((SHIELD_HasGlobalRight ($supergroupname, "moveto", $mysecuritysection) || SHIELD_HasGlobalRight ($supergroupname, "newdoc", $mysecuritysection)) && ($myconfig[IMS_SuperGroupName()]["projectfilter"]=="advanced") && ($submode=="documents" || $submode=="projects")) {

      $title = ML("Verplaats geselecteerde documenten naar huidige folder","Move selected files to current folder");
      $form = array();
      $form["title"] = $title;
      $form["input"]["me"] = $currentfolder;
      $form["metaspec"]["fields"]["sure"]["type"] = "list";
      $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
      $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
      $form["metaspec"]["fields"]["keep"]["type"] = "yesno";
      $form["metaspec"]["fields"]["shortcuts"]["type"] = "list";
      $form["metaspec"]["fields"]["shortcuts"]["values"][ML("verplaatsen","move")] = "moveshortcuts";
      $form["metaspec"]["fields"]["shortcuts"]["values"][ML("overslaan","skip")] = "skipshortcuts";
      $form["metaspec"]["fields"]["shortcuts"]["values"][ML("achterliggende documenten verplaatsen","move connected documents")] = "moveconnectedfiles";
      $form["metaspec"]["fields"]["remark"]["type"] = "string";
      $form["formtemplate"] = '
              <table>
              <tr><td colspan=2><font face="arial" size=2><b>'. ML("Geselecteerde documenten: Verplaatsen","Selected files: Move").'</b><br><br></font></td></tr>
              <tr><td colspan=2>'. 'xyz' .'</td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              xxyyzz
              <tr><td width=0%><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td><td width=100%><nobr>[[[sure]]]</nobr></td></tr>
              <tr><td><nobr><font face="arial" size=2><b>'.ML("Onthoud selectie","Remember selection").'</b></font></nobr></td><td><nobr>[[[keep]]]</nobr></td></tr>
              <tr><td><nobr><font face="arial" size=2><b>'.ML("Opmerking","Remark").'</b></font></nobr></td><td><nobr>[[[remark]]]</nobr></td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
              </table>
      ';
      $form["precode"] = '
           uuse("reports");
           uuse("multi");
           global $myconfig;
           if($myconfig[IMS_SuperGroupname()]["movedocumentsdefault"] == "yes") {
             $data["sure"] = "yes";
           }
           if($myconfig[IMS_SuperGroupname()]["movedocumentsrememberselectiondefault"]  == "yes") {
             $data["keep"] = "yes";
           }
           $portal = MULTI_GetCurrentSelection();
           $formtemplate = str_replace("xyz",REPORTS_Selected("simple", true),$formtemplate);
           if (MULTI_SelectedShortcuts()) {
             $formtemplate = str_replace("xxyyzz",\'<tr><td width=0%><nobr><font face="arial" size=2><b>\'.ML("Geselecteerde snelkoppelingen","Selected shortcuts").\':</b></font></nobr></td><td width=100%><nobr>[[[shortcuts]]]</nobr></td></tr>\',$formtemplate);
           } else {
             $formtemplate = str_replace("xxyyzz","",$formtemplate);
           }
      ';
      $form["postcode"] = '
            uuse ("multi");
            MULTI_UseSelection($portal);
            if ($data["sure"]=="yes") {
               $errors = MULTI_MoveAll($input["me"], $data["shortcuts"], $data["remark"]);
               if ($errors) {
                 $title = ML("Niet verplaatste documenten","Non moved files");
                 $form = array();
                 $form["title"] = $title;
                 $form["formtemplate"] = "
                   <table>
                   <tr><td colspan=2><font face=\"arial\" size=2><b>" . ML("De volgende documenten konden niet worden verplaatst:",
                                            "The following documents could not be moved:") . "</b></font></td></tr>";
                 foreach($errors as $key=>$reason) {
                   if (FILES_IsShortcut (IMS_SuperGroupName(), $key)) {
                     $name = MB_Fetch ("ims_".IMS_SuperGroupName()."_objects", FILES_Base (IMS_SuperGroupName(), $key), "shorttitle");
                   } else {
                     $name = MB_Fetch ("ims_".IMS_SuperGroupName()."_objects", $key, "shorttitle");
                   }
                   $form["formtemplate"] .= "<tr><td><font face=\"arial\" size=2><b>".$name.":</b></font></td><td><font face=\"arial\" size=2>".$reason."</font></td></tr>";
                 }
                 $form["formtemplate"] .= "
                   <tr><td colspan=2>&nbsp</td></tr>
                   <tr><td colspan=2><center>[[[OK]]]</center></td></tr>

                   </table>";
                 $url = FORMS_URL($form);
                 $gotook = $url;
               }
               if (!$data["keep"]) {
                 MULTI_UnselectAll ();
               }
            }
      ';
      $url = FORMS_URL ($form);

      $dyn .= "<a class=\"ims_navigation\" title=\"".ML("Verplaats geselecteerde documenten naar huidige folder","Move selected files to current folder")."\" href=\"$url\">";
      $dyn .= '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/move_small.gif"> '.ML("Verplaats","Move");
      $dyn .= "</a><br>";


    }
    if (SHIELD_HasGlobalRight ($supergroupname, "newdoc", $mysecuritysection) && ($myconfig[IMS_SuperGroupName()]["projectfilter"]=="advanced") && ($submode=="documents" || $submode=="projects") ) {

      $title = ML("Kopieer geselecteerde documenten naar huidige folder","Copy selected files to current folder");
      $form = array();
      $form["title"] = $title;
      $form["input"]["me"] = $currentfolder;
      $form["metaspec"]["fields"]["sure"]["type"] = "list";
      $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
      $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
      $form["metaspec"]["fields"]["keep"]["type"] = "yesno";
      $form["metaspec"]["fields"]["shortcuts"]["type"] = "list";
      $form["metaspec"]["fields"]["shortcuts"]["values"][ML("kopieren","copy")] = "copyshortcuts";
      $form["metaspec"]["fields"]["shortcuts"]["values"][ML("overslaan","skip")] = "skipshortcuts";
      $form["metaspec"]["fields"]["shortcuts"]["values"][ML("achterliggende documenten kopieren","copy connected documents")] = "copyconnectedfiles";
      $form["formtemplate"] = '
              <table>
              <tr><td colspan=2><font face="arial" size=2><b>'. ML("Geselecteerde documenten: Kopi&euml;ren","Selected files: Copy").'</b><br><br></font></td></tr>
              <tr><td colspan=2>'. 'xyz' .'</td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              xxyyzz
              <tr><td width=0%><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td><td width=100%><nobr>[[[sure]]]</nobr></td></tr>
              <tr><td><nobr><font face="arial" size=2><b>'.ML("Onthoud selectie","Remember selection").'</b></font></nobr></td><td><nobr>[[[keep]]]</nobr></td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
              </table>
      ';
      $form["precode"] = '
           uuse("reports");
           uuse("multi");
           global $myconfig;
           if ($myconfig[IMS_SuperGroupname()]["copydocumentsdefault"] == "yes") {
             $data["sure"] = "yes";
           }
           $portal = MULTI_GetCurrentSelection();
           $formtemplate = str_replace("xyz",REPORTS_Selected("simple", true),$formtemplate);
           if (MULTI_SelectedShortcuts()) {
             $formtemplate = str_replace("xxyyzz",\'<tr><td width=0%><nobr><font face="arial" size=2><b>\'.ML("Geselecteerde snelkoppelingen","Selected shortcuts").\':</b></font></nobr></td><td width=100%><nobr>[[[shortcuts]]]</nobr></td></tr>\',$formtemplate);
           } else {
             $formtemplate = str_replace("xxyyzz","",$formtemplate);
           }
      ';
      $form["postcode"] = '
            uuse ("multi");
            MULTI_UseSelection($portal);
            if ($data["sure"]=="yes") {
               MULTI_CopyAll($input["me"], $data["shortcuts"]);
               if (!$data["keep"]) {
                 MULTI_UnselectAll ();
               }
            }
      ';
      if ($myconfig[$supergroupname]["hidemulticopylink"] !== "yes") {
        $url = FORMS_URL ($form);
        $dyn .= "<a class=\"ims_navigation\" title=\"".$title."\" href=\"$url\">";
        $dyn .= '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/copy_small.gif"> '.ML("Kopieer","Copy");
        $dyn .= "</a><br>";
      }
      $title = ML("Maak snelkoppeling van geselecteerde documenten naar huidige folder","Create shortcut of selected files to current folder");
      $form = array();
      $form["title"] = $title;
      $form["input"]["me"] = $currentfolder;
      $form["metaspec"]["fields"]["sure"]["type"] = "list";
      $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
      $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
      $form["metaspec"]["fields"]["permalink"]["type"] = "list";
      $form["metaspec"]["fields"]["permalink"]["values"][ML("nee","no")] = "";
      $form["metaspec"]["fields"]["permalink"]["values"][ML("ja", "yes")] = "yes";
// 20120601 KvD DZ MW      
      if ($myconfig[$supergroupname]["hideshortcutcurrent"] !== "yes") {
        $shortcut_current = '              <tr><td width=0%><nobr><font face="arial" size=2><b>'.ML("Koppeling(en) naar huidige versie maken?","Create shortcut(s) to current version(s)?").'</b></font></nobr></td><td width=100%><nobr>[[[permalink]]]</nobr></td></tr>';
      }
///
      $form["metaspec"]["fields"]["keep"]["type"] = "yesno";
      $form["formtemplate"] = '
              <table>
              <tr><td colspan=2><font face="arial" size=2><b>'. ML("Geselecteerde documenten: Snelkoppeling maken","Selected files: Create shortcut").'</b><br><br></font></td></tr>
              <tr><td colspan=2>'. 'xyz' .'</td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
' // 20120601 KvD DZ MW 
. $shortcut_current . 
'              <tr><td width=0%><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td><td width=100%><nobr>[[[sure]]]</nobr></td></tr>
              <tr><td><nobr><font face="arial" size=2><b>'.ML("Onthoud selectie","Remember selection").'</b></font></nobr></td><td><nobr>[[[keep]]]</nobr></td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
              </table>
      ';
      $form["precode"] = '
           uuse("reports");
           uuse("multi");
           global $myconfig;
           if($myconfig[IMS_SuperGroupname()]["createshortcutsdefault"] == "yes") {
             $data["sure"] = "yes";
           }
           if($myconfig[IMS_SuperGroupname()]["createshortcutsrememberselectiondefault"] == "yes") {
             $data["keep"] = "yes";
           }
           //gv 06-05-2010 mw1190
           if($myconfig[IMS_SuperGroupname()]["createshortcutstocurrentversion"] == "yes") {
             $data["permalink"] = "yes";
           }
           $portal = MULTI_GetCurrentSelection();
           $formtemplate = str_replace("xyz",REPORTS_Selected("simple"),$formtemplate);
      ';
      $form["postcode"] = '
            uuse ("multi");
            MULTI_UseSelection($portal);
            if ($data["sure"]=="yes" && ($myconfig[IMS_SuperGroupname()]["hideshortcutcurrent"] === "yes" || $data["permalink"]!="yes")) {
               MULTI_ShortcutAll($input["me"]);
               if (!$data["keep"]) {
                 MULTI_UnselectAll ();
               }

            // 20120601 KvD DZ MW ALTIJD naar laatste versie
            } elseif ($data["sure"]=="yes" && ($myconfig[IMS_SuperGroupname()]["hideshortcutcurrent"] !== "yes" && $data["permalink"]=="yes")) {
              MULTI_PermalinkAll($input["me"]);
              if (!$data["keep"]) {
                 MULTI_UnselectAll ();
               }
            }
      ';
      $url = FORMS_URL ($form);

      if ($myconfig[IMS_SuperGroupName()]["hidemakeshortcuts"]!="yes") {
        $dyn .= "<a class=\"ims_navigation\" title=\"".$title."\" href=\"$url\">";
        $dyn .= '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/shortcut_small.gif"> '.ML("Snelkoppeling","Shortcut");
        $dyn .= "</a><br>";
      }
    }

    $title = ML("Koppel geselecteerde documenten","Connect selected files");
    $form = array();
    $form["title"] = $title;
    // $form["input"]["folder"] = $currentfolder; // was never used. Cant be used from now on, because this form can be visible in alloced-submode
    $form["input"]["document"] = $currentobject;
    $form["metaspec"]["fields"]["keep"]["type"] = "yesno";
    $form["metaspec"]["fields"]["type"]["type"] = "list";
    uuse ("link");
    $list = LINK_AvailableTypesDMS ();
    global $myconfig;
    foreach ($list as $type => $specs)
    {
     if($myconfig[IMS_SuperGroupName()]["koppelvolgorde"]=="yes") {
      $form["metaspec"]["fields"]["type"]["values"][$specs["right2left_1_n"]] = "right2left#$type";
      $form["metaspec"]["fields"]["type"]["values"][$specs["left2right_1_n"]] = "left2right#$type";
     }else {
      $form["metaspec"]["fields"]["type"]["values"][$specs["left2right_1_n"]] = "left2right#$type";
      $form["metaspec"]["fields"]["type"]["values"][$specs["right2left_1_n"]] = "right2left#$type";
     }
   }
    $form["formtemplate"] = '
      <table>
        <tr><td><nobr><font face="arial" size=2><b>'.ML("Onthoud selectie","Remember selection").'</b></font></nobr></td><td><nobr>[[[keep]]]</nobr></td></tr>
        <tr><td colspan=2><font face="arial" size=2><b>'. ML("Actieve document:","Active document:")." ".MB_Fetch ("ims_".IMS_SuperGroupName()."_objects", $currentobject, "shorttitle").'</b></font></td></tr>
        <tr><td colspan=2>&nbsp</td></tr>
        <tr>
          <td><font face="arial" size=2><b>'. ML("Relatietype:","Relation type:").'</b></font></td>
          <td width=100%>[[[type]]]</td>
        </tr>
        <tr><td colspan=2>&nbsp</td></tr>
        <tr><td colspan=2><font face="arial" size=2><b>'. ML("Geselecteerde documenten:","Selected documents:").'</b></font></td></tr>
        <tr><td colspan=2>'. 'xyz' .'</td></tr>
        <tr><td colspan=2>&nbsp</td></tr>
        <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>

    ';
    $form["precode"] = '
         uuse("reports");
         uuse("multi");
         global $myconfig;
         if($myconfig[IMS_SuperGroupname()]["linkingrememberselectiondefault"]  == "yes") {
           $data["keep"] = "yes";
         }
         $portal = MULTI_GetCurrentSelection();
         $formtemplate = str_replace("xyz",REPORTS_Selected("simple"),$formtemplate);
    ';
    $form["postcode"] = '
      uuse ("multi");
      MULTI_UseSelection($portal);
      uuse ("link");
      $todo = explode ("#", $data["type"]);
      $multi = &MULTI_Load_AutoShortcuts();
      foreach ($multi as $key => $dummy) {
        if ($todo[0]=="left2right") {
          $error = LINK_Create (IMS_SuperGroupName(), $input["document"], $key, $todo[1]);
        } else {
          $error = LINK_Create (IMS_SuperGroupName(), $key, $input["document"], $todo[1]);
        }
        if ($error) $errors[$key] = $error;
      }
      if ($errors) {
       $title = ML("Niet gekoppelde documenten","Not linked documents");
       $form = array();
       $form["title"] = $title;
       $form["formtemplate"] = "
         <table>
         <tr><td colspan=2><font face=\"arial\" size=2><b>" . ML("De volgende documenten konden niet worden gekoppeld:",
                                  "The following documents could not be linked:") . "</b></font></td></tr>";
       foreach($errors as $key=>$reason) {
         if (FILES_IsShortcut (IMS_SuperGroupName(), $key)) {
           $name = MB_Fetch ("ims_".IMS_SuperGroupName()."_objects", FILES_Base (IMS_SuperGroupName(), $key), "shorttitle");
         } else {
           $name = MB_Fetch ("ims_".IMS_SuperGroupName()."_objects", $key, "shorttitle");
         }
         $form["formtemplate"] .= "<tr><td><font face=\"arial\" size=2><b>".$name.":</b></font></td><td><font face=\"arial\" size=2>".$reason."</font></td></tr>";
       }
       $form["formtemplate"] .= "
         <tr><td colspan=2>&nbsp</td></tr>
         <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
         </table>";
       $url = FORMS_URL($form);
       $gotook = $url;
      }
      if (!$data["keep"]) {
        MULTI_UnselectAll ();
      }
    ';
    $url = FORMS_URL ($form);

    $folderactivedocument = SHIELD_SecuritySectionForObject ($supergroupname, $currentobject);
    
    if (LINK_EnabledDMS() && SHIELD_HasGlobalRight (IMS_SuperGroupName(), "connectmanagement", $folderactivedocument) && $currentobject) {
      $dyn .= "<a class=\"ims_navigation\" title=\"".$title."\" href=\"$url\">";
      $dyn .= '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/make-link.gif"> '.ML("Koppel","Connect");
      $dyn .= "</a><br>";
    }

    if (true) {
      $title = ML("Verwijder geselecteerde documenten","Delete selected files");
      $form = array();
      $form["title"] = $title;
      // $form["input"]["me"] = $currentfolder;    // LF: was never used. Cant be used from now on, because this form can be visible in alloced-submode
      $form["metaspec"]["fields"]["sure"]["type"] = "list";
      $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
      $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
      $form["metaspec"]["fields"]["shortcuts"]["type"] = "list";
      $form["metaspec"]["fields"]["shortcuts"]["values"][ML("verwijderen","delete")] = "deleteshortcuts";
      $form["metaspec"]["fields"]["shortcuts"]["values"][ML("overslaan","skip")] = "skipshortcuts";
      if ( $myconfig[ IMS_supergroupname() ]["hide_delete_document_on_shortcuts"] != "yes" )
        $form["metaspec"]["fields"]["shortcuts"]["values"][ML("achterliggende documenten verwijderen","delete connected documents")] = "deleteconnectedfiles";
      $form["formtemplate"] = '
              <table>
              <tr><td colspan=2><font face="arial" size=2><b>'. ML("Geselecteerde documenten: Verwijderen","Selected files: Delete").'</b><br><br></font></td></tr>
              <tr><td colspan=2>'. 'xyz' .'</td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              xxyyzz
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td width=0%><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td><td width=100%><nobr>[[[sure]]]</nobr></td></tr>
              xxxyyyzzz
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
              </table>
      ';
      $form["precode"] = '
           uuse("reports");
           uuse("multi");
           uuse("link");
           global $myconfig;
           $supergroupname = IMS_SuperGroupName();
           $portal = MULTI_GetCurrentSelection();
           $haslinks = LINK_MultiHasLinks($supergroupname, $portal);
           $formtemplate = str_replace("xyz",REPORTS_Selected("simple", true),$formtemplate);
           if (MULTI_SelectedShortcuts()) {
             $formtemplate = str_replace("xxyyzz",\'<tr><td width=0%><nobr><font face="arial" size=2><b>\'.ML("Geselecteerde snelkoppelingen","Selected shortcuts").\':</b></font></nobr></td><td width=100%><nobr>[[[shortcuts]]]</nobr></td></tr>\',$formtemplate);
           } else {
             $formtemplate = str_replace("xxyyzz","",$formtemplate);
           }
           if ($haslinks && ($myconfig[$supergroupname]["extrawarningondelete"] != "no")) {
             $formtemplate = str_replace("xxxyyyzzz",\'<tr><td colspan="2"><font color="#ff0000" face="arial" size=2><b>'.ML("Geselecteerde bestanden hebben koppelingen met andere bestanden!","Selected files have links to other documents!").'</b></font></td></tr>\',$formtemplate);
           } else {
             $formtemplate = str_replace("xxxyyyzzz","",$formtemplate);
           }
      ';
      $form["postcode"] = '
            uuse ("multi");
            MULTI_UseSelection($portal);
            if ($data["sure"]=="yes") {
               $errors = MULTI_DeleteAll($data["shortcuts"]);
               if ($errors) {
                 $title = ML("Niet verwijderde documenten","Non deleted files");
                 $form = array();
                 $form["title"] = $title;
                 $form["formtemplate"] = "

                   <table>
                   <tr><td colspan=2><font face=\"arial\" size=2><b>" . ML("De volgende documenten konden niet worden verwijderd:",
                                            "The following documents could not be deleted:") . "</b></font></td></tr>";
                 foreach($errors as $key=>$reason) {
                   if (FILES_IsShortcut (IMS_SuperGroupName(), $key)) {
                     $name = MB_Fetch ("ims_".IMS_SuperGroupName()."_objects", FILES_Base (IMS_SuperGroupName(), $key), "shorttitle");
                   } else {
                     $name = MB_Fetch ("ims_".IMS_SuperGroupName()."_objects", $key, "shorttitle");
                   }
                   $form["formtemplate"] .= "<tr><td><font face=\"arial\" size=2><b>".$name.":</b></font></td><td><font face=\"arial\" size=2>".$reason."</font></td></tr>";
                 }
                 $form["formtemplate"] .= "
                   <tr><td colspan=2>&nbsp</td></tr>
                   <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
                   </table>";
                 $url = FORMS_URL($form);
                 $gotook = $url;
               }
               MULTI_UnselectAll ();
            }
      ';
      $url = FORMS_URL ($form);

      if ( ($myconfig[IMS_SuperGroupName()]["hidemultidelete"]!="yes") &&
           ( ($myconfig[IMS_SuperGroupName()]["multideleteforsystemonly"]!="yes") ||
             (($myconfig[IMS_SuperGroupName()]["multideleteforsystemonly"]=="yes") && (SHIELD_HasGlobalRight (IMS_SuperGroupName(), "system")) )
           )
         ) {
        $dyn .= "<a class=\"ims_navigation\" title=\"".ML("Verwijder geselecteerde documenten","Delete selected files")."\" href=\"$url\">";
        $dyn .= '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/delete_small.gif"> '.ML("Verwijder","Delete");
        $dyn .= "</a><br>";
      }
    }

    

    $params = array();
    $params["type"] = "report";
    $url = "/openims/dmsreport.php?params=".SHIELD_Encode ($params);


    $dyn .= "<a target=\"_blank\" class=\"ims_navigation\" title=\"".ML("Maak een rapport van de geselecteerde documenten","Create a report of the selected files")."\" href=\"$url\">";
    $dyn .= '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/report.jpg"> '.ML("Rapport","Report");

    $dyn .= "</a><br>";


    if ( $myconfig[IMS_Supergroupname()]["nodmssnapshot"]!='yes' ) 
    {
      $params = array();
      $params["type"] = "snapshot";
      $url = "/openims/dmsreport.php?params=".SHIELD_Encode ($params);

      $dyn .= "<a target=\"_blank\" class=\"ims_navigation\" title=\"".ML("Maak een snapshot van de geselecteerde documenten","Create a snapshot of the selected files")."\" href=\"$url\">";
      $dyn .= '<img border=0 height=16 widh=16 src="/ufc/rapid/openims/snapshot.jpg"> '.ML("Snapshot","Snapshot");
      $dyn .= "</a><br>";
    }

    $dyn .= DMSUIF_ShowWizards(IMS_SuperGroupName(), $dmswizards, "files", $dummy);

    echo DHTML_EmbedJavascript ("stat = '".str_replace ("'", "\\'", $stat)."';");
    echo DHTML_EmbedJavascript ("dyn = '".str_replace ("'", "\\'", $stat.$dyn)."';");

    if (MULTI_Selected()) {
      echo DHTML_DynamicObject ($stat.$dyn, "multifileoptions");
    } else {
      echo DHTML_DynamicObject ($stat, "multifileoptions");
    }

    endblock();

  }

  // LF: end multifileblock

  if ($submode=="documents" || ($submode=="projects" && $rootfolder)) {
    $wizardcount = 0;
    $wizardstring = DMSUIF_ShowWizards(IMS_SuperGroupName(), $dmswizards, "newdoc", $wizardcount);
    $allowed = SHIELD_AllowedWorkflows ($supergroupname, "", $mysecuritysection); // thb: only show newdoc and upload options when there are available workflows
    $wlist = SHIELD_DMSWorkflows();
    foreach ($allowed as $workflowid => $dummy) {
      if (!$wlist[$workflowid]) unset($allowed[$workflowid]);
    }

    // LF20090515: fixed weird situation where all templates had been deleted and user got an empty New Document block
    $allowupload = (count($allowed)>=1 && SHIELD_HasGlobalRight($supergroupname, "newdoc", $mysecuritysection) && SHIELD_HasGlobalRight($supergroupname, "upload", $mysecuritysection));

    // $list is the list of templates (code moved up)
    $list = MB_TurboSelectQuery ("ims_".$supergroupname."_objects", array(
      '$record["objecttype"]' => "doctemplate",
      '$record["published"]' => "yes"
    ), '$record["shorttitle"]');
    $allownewdocfromtemplates = (count($allowed)>=1 && SHIELD_HasGlobalRight($supergroupname, "newdoc", $mysecuritysection) && count($list)>=1);

    // Show the New Document block if someone can create a document from templates, or upload, or can use a dms wizards in this block
    if($allownewdocfromtemplates || $allowupload || $wizardcount) {

      startblock (ML("Nieuw document", "New document"), "action");

      if ($allownewdocfromtemplates || $allowupload) {
        $wlist = SHIELD_DMSWorkFlows();

        if ($allownewdocfromtemplates) while (list ($key, $longtitle) = each($list)) {
          $object = &MB_Ref ("ims_".$supergroupname."_objects", $key);
          $img = FILES_Icon ($supergroupname, $key, false, "published"); // Templates do not have any mechanism for publishing them, nor for changing the preview version, but if they did, we would want the published verison
          $thedoctype = FILES_FileType ($supergroupname, $key, "published");
          $ext = FILES_FileExt ($supergroupname, $key, "published");


          $metaspec = array();
          $metaspec["fields"]["longtitle"]["type"] = "strml4";
          $metaspec["fields"]["shorttitle"]["type"] = "strml4";
          $metaspec["fields"]["filename"]["type"] = "string";
          $metaspec["fields"]["workflow"]["type"] = "list";
          $metaspec["fields"]["workflow"]["sort"] = "yes";
          $metaspec["fields"]["workflow"]["default"] = "edit-publish";

          $allowed = SHIELD_AllowedWorkflows ($supergroupname, "", $mysecuritysection);
          if (is_array($wlist)) {
            reset ($wlist);
            while (list($wkey)=each($wlist)) {
              if ($allowed[$wkey]) {
                $workflow = &MB_Ref ("shield_".$supergroupname."_workflows", $wkey);
                $metaspec["fields"]["workflow"]["values"][$workflow["name"]] = $wkey;
              }
            }
          }

          $formtemplate  = '<body bgcolor=#f0f0f0><br><center><table>
                            <tr><td><font face="arial" size=2><b>'.ML("Document naam","Document name").':</b></font></td><td>[[[shorttitle]]]</td></tr>
                            <tr><td><font face="arial" size=2><b>'.ML("Omschrijving","Description").':</b></font></td><td>[[[longtitle]]]</td></tr>
                            <tr><td><font face="arial" size=2><b>'.ML("Workflow","Workflow").':</b></font></td><td>[[[workflow]]]</td></tr>
                            <tr><td colspan=2>&nbsp</td></tr>
                            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                            </table></center></body>';
          echo '<table border=0 cellspacing=0 cellpadding=0><tr><td>';
          $code = '
            if (!trim($data["shorttitle"])) FORMS_ShowError (ML("Foutmelding","Error"), ML("Document naam is leeg","Document name is empty"), true);
            uuse("ims");
            uuse("files");
            $id = IMS_NewDocumentObject ($input["sitecollection_id"], $input["directory_id"], $input["template"], "", $data["filename"]);
            $object = &IMS_AccessObject ($input["sitecollection_id"], $id);
            $object["shorttitle"] = $data["shorttitle"];
            $object["longtitle"] = $data["longtitle"];
            $object["workflow"] = $data["workflow"];
            $object["executable"] = $input["executable"];
            IMS_ArchiveObject ($input["sitecollection_id"], $id, SHIELD_CurrentUser ($input["sitecollection_id"]), true);
            SEARCH_AddPreviewDocumentToDMSIndex ($input["sitecollection_id"], $id);
            MB_Flush();
            global $myconfig;

            if ($myconfig[IMS_SuperGroupName()]["autolaunchnewdocument"]=="yes") {

              $url = FILES_TransEditURL (IMS_SuperGroupName(), $id);
              uuse ("dhtml");
              DHTML_LoadTransURL($url);
            }
            $gotook = "closeme&parentgoto:" . N_AlterURL($input["gotook"],"currentobject", $id);
          ';
          $input["gotook"] = N_MyVeryFullURL();
          $input["template"] = $key;
          $input["sitecollection_id"] = $supergroupname;
          $input["directory_id"] = $currentfolder;

          $input["executable"] = $object["executable"];
          echo FORMS_GenerateExecuteLink ($code, $input, $longtitle, '<img border="0" src="'.$img.'">', $longtitle, $metaspec, $formtemplate, 400, 200, 200, 200, "ims_navigation");
          echo '</td><td>&nbsp;</td><td>';
          echo FORMS_GenerateExecuteLink ($code, $input, $longtitle, $object["shorttitle"].$ext, $longtitle, $metaspec, $formtemplate, 400, 200, 200, 200, "ims_navigation");
          echo '</td>';
          if (SHIELD_HasGlobalRight ($supergroupname, "doctemplateedit")) {
            $deltmpform["input"]["sitecollection_id"] = $supergroupname;
            $deltmpform["input"]["template_id"] = $key;
            $deltmpform["formtemplate"] = '
              <table>
              <tr><td><font face="arial" size=2>'.ML("Wilt u de template %1 verwijderen?","Do you want to delete the template %1?", "<b>".$object["shorttitle"]."</b>").
                 '</font></td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
              </table>
            ';
            $deltmpform["postcode"] = '
              IMS_Delete ($input["sitecollection_id"], "", $input["template_id"]);
            ';
            $deltempurl = FORMS_URL ($deltmpform);

            $title = "Verwijder template &quot;".$object ["shorttitle"]."&quot;";
            echo "<td>&nbsp;<a title=\"$title\" href=\"$deltempurl\"><img border=\"0\" src=\"/ufc/rapid/openims/delete_small.gif\"></a></td>";
          }
          echo '</tr></table>';
        }

        global $myconfig;

        if ($allowupload && !N_DoNotDisturb(false)) {
          $url = IMS_UploadFilesForm_Auto($supergroupname, $mysecuritysection, $currentfolder, $currentobject);

          echo '<table border=0 cellspacing=0 cellpadding=0><tr><td>';
          echo "<a title=\"$longtitle\" href=\"$url\"><img border=\"0\" src=\"/ufc/rapid/openims/upload_small.gif\"></a>";
          echo '</td><td>&nbsp;</td><td>';
          // 20091215 KvD IJsselgroep
          echo "<a class=\"ims_navigation\" title=\"$longtitle\" href=\"$url\">".ML("Upload","Upload")."</a>";
          echo '</td></tr></table>';
        }

        

      }

      echo $wizardstring;
      endblock();
    } // thb
  }

  if ( function_exists("SKIN_preventEmptyColumnEnd") )
    SKIN_preventEmptyColumnEnd( "RIGHT" );
  else
    echo "</td>";

  } // (!$wide)
?>
</tr></table>
<?

if ($mode=="admin" && $submode=="maint" && $action=="checkup") {
  uuse ("selftest");
  SELFTEST_TestAll ();
}


if ($mode=="admin" && $submode=="maint" && $action=="backupxml") {
  echo '<br><font face="arial,helvetica" size="3"><b>'.ML("Backup XML", "Backup XML").'</b><br>';

  uuse ("backups");

  BACKUPS_CreateManual();
  echo '<br>';
}
// 20100519 KvD Backup core, flex
else if ($mode=="admin" && $submode=="maint" && $action=="backupcore") {
  echo '<br /><font face="arial,helvetica" size="3"><b>'.ML("Backup Core", "Backup Core").'</b><br />';

  uuse ("backups");

  BACKUPS_OpenIMSCore(1);
  echo '<br />';
}
else if ($mode=="admin" && $submode=="maint" && $action=="backupflex") {
  echo '<br /><font face="arial,helvetica" size="3"><b>'.ML("Backup Maatwerk", "Backup Configuration").'</b><br />';

  uuse ("backups");

  BACKUPS_ConfigFlex(1);
  echo '<br />';
}
//

if ($mode=="admin" && $submode=="maint" && $action=="restorexml") {
  echo '<br><font face="arial,helvetica" size="3"><b>'.ML("Restore XML", "Restore XML").'</b><br>';
  uuse ("backups");
  if (false && $filename) {
  } else {
    echo "Server: <b>" . N_CurrentServer() . "</b>, MySQL database: <b>" . ($myconfig["xmlmysql"]["database"] ? $myconfig["xmlmysql"]["database"] : ML("n.v.t.", "N/A")) . "</b><br/>"; //LF20090304
    uuse ("backups");
    $list = N_QuickTree ("html::backups");

      foreach ($list as $key => $row)
        $age[$key] = $row["age"];
      array_multisort($age, SORT_ASC, $list);

    if (is_array ($list)) {
// 201000518 KvD sorteer op tijdstempel via 'age'
      uasort($list, create_function('$a,$b', 'return $a["age"] > $b["age"];'));
///
      foreach ($list as $filename => $dummy) {
        $url = ADMINUIF_MaintPopupForm(ML("Restore XML", "Restore XML") . " " . $filename, 
                                       'uuse("backups"); BACKUPS_Restore ($input);', 
                                       $filename);
        //echo "Restore <a href=\"/openims/openims.php?mode=admin&submode=maint&action=restorexml&filename=".urlencode($filename)."\">".N_HTMLPath ($filename)."</a><br>";
        echo "Restore <a href=\"$url\">".N_HTMLPath ($filename)."</a><br>";
      }
    }
  }
  echo '<br>';
}
if ($mode=="admin" && $submode=="maint" && $action=="deletexml") {
  echo '<br><font face="arial,helvetica" size="3"><b>'.ML("Verwijderen XML backup(s)", "Delete XML backup(s)").'</b><br>';
  echo "Server: <b>" . N_CurrentServer() . "</b>, MySQL database: <b>" . ($myconfig["xmlmysql"]["database"] ? $myconfig["xmlmysql"]["database"] : ML("n.v.t.", "N/A")) . "</b><br/>";
  uuse ("backups");
  if (false && $filename) {
    echo ML("Verwijder","Delete")." $filename<br>";
    N_DeleteFile ($filename);
  } else {
    uuse ("backups");
    $list = N_QuickTree ("html::backups");

      foreach ($list as $key => $row)
        $age[$key] = $row["age"];
      array_multisort($age, SORT_ASC, $list);

    if (is_array ($list)) {
      foreach ($list as $filename => $dummy) {
        $url = ADMINUIF_MaintPopupForm(ML("Verwijder XML", "Delete XML") . " " . $filename, 
                                       'uuse("backups"); N_DeleteFile ($input); $gotook = "closeme&refreshparent";', 
                                       $filename);
        echo ML("Verwijder","Delete")." <a href=\"{$url}\">".N_HTMLPath ($filename)."</a><br>";
        //echo ML("Verwijder","Delete")." <a href=\"/openims/openims.php?mode=admin&submode=maint&action=deletexml&filename=".urlencode($filename)."\">".N_HTMLPath ($filename)."</a><br>";
      }
    }
  }
  echo '<br>';
  echo '<br>';
}
if ($mode=="admin" && $submode=="report" && $print) {
  uuse("adminuif");
  ADMINUIF_SecurityOverviewContent($view, $viewobject, 1);
}
if ($mode=="admin" && $submode=="maint" && $action=="downloadxml") {
  echo '<br><font face="arial,helvetica" size="3"><b>'.ML("Download XML backup(s)", "Download XML backup(s)").'</b><br>';
  echo "Server: <b>" . N_CurrentServer() . "</b>, MySQL database: <b>" . ($myconfig["xmlmysql"]["database"] ? $myconfig["xmlmysql"]["database"] : ML("n.v.t.", "N/A")) . "</b><br/>";
  echo '<br><font face="arial,helvetica" color="ff0000" size="3"><b>';
  echo ML('Gebruik "opslaan als" om een backup bestand lokaal op te slaan',
          'Use "save as" to save a backup file locally')."<br>";
  echo '<br></font></b>';
  $list = N_QuickTree ("html::backups");
  if (is_array ($list)) {
// 201000518 KvD sorteer op tijdstempel via 'age'
    uasort($list, create_function('$a,$b', 'return $a["age"] > $b["age"];'));
///
    foreach ($list as $filename => $dummy) {
      echo "Download <a href=\"". N_HTMLPath ($filename)."\">".N_HTMLPath ($filename)."</a> (".N_NiceSize (N_FileSize ($filename)).")<br>";
    }
  }
  echo '<br>';
}
?>
<? if ($viewmode!="report") { ?>
<STYLE type="text/css">
<!--
  A.ims_disclaimer:link{color: #000366; text-decoration: underline; font-family: Arial, Helvetica, sans-serif; font-size: 10px;}
  A.ims_disclaimer:visited{color: #000366; text-decoration: underline; font-family: Arial, Helvetica, sans-serif; font-size: 10px;}
  A.ims_disclaimer:hover{color: #FF0000; text-decoration: underline; font-family: Arial, Helvetica, sans-serif; font-size: 10px;}
--></STYLE>
<table border="0" cellspacing="0" cellpadding="0" width="100%"><tr><td background="<? echo $skin_horizontalseparator; ?>"><img src="<? echo $skin_horizontalseparator; ?>"></td></tr></table>
<?
  if ( function_exists('SKIN_custom_openims_copyright') )
    print( SKIN_custom_openims_copyright() );
  else {
?>
<center><font face="arial,helvetica" size="1"><a class="ims_disclaimer" href="/openims/termsofuse.txt">Copyright &copy; 2001-<? echo N_Year(); ?> OpenSesame ICT. <? echo ML ("Alle rechten voorbehouden","All Rights Reserved"); ?>.</a></font></center>
<? } } // SKIN_custom_openims_copyright and viewmode != report ?>

</body></html>

<? 
  SHIELD_FlushEncoded();
  N_Flush();
  N_Exit(); 
?>