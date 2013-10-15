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



// SKIN management is handled by FLEX allowing customers to define their own skins

uuse ("ims");
uuse("flex"); 

UUSE_DO ('SKINS_Init ();');

function SKINS_Init () {

FLEX_LoadSupportFunctions (IMS_SuperGroupName());

if (!function_exists ("SKIN_BgColor")) {
   // SKIN Management (default)
   $internal_component = FLEX_LoadImportableComponent ("support", "c545fda12b52faa6b936f34b0cb9a2f6");
   $internal_code = $internal_component["code"];
   eval ($internal_code);
}

if (!function_exists ("SKIN_Bottom_Background"))
{
   function SKIN_Bottom_Background()
   {
      return "";
   }
}

if (!function_exists ("SKIN_Bottom_BgColor"))
{
   function SKIN_Bottom_BgColor()
   {
      return "FFFFFF";
   }
}

if (!function_exists ("SKIN_Top_Background"))
{
   function SKIN_Top_Background()
   {
      return "";
   }
}

if (!function_exists ("SKIN_Top_BgColor"))
{
   function SKIN_Top_BgColor()
   {
      return "FFFFFF";
   }
}

if (!function_exists ("SKIN_ExtraButtons"))
{
   function SKIN_ExtraButtons()
   {
      return "";
   }
}

if (!function_exists ("SKIN_HorizontalSeparator"))
{
   function SKIN_HorizontalSeparator()
   {
      return "/openims/bottom.gif";
   }
}

if (!function_exists ("SKIN_TabStyle")) 
{
   function SKIN_TabStyle()
   {
      return '
        .ims_tabcontainer { display: block; margin: 10px 0 0 0; padding: 0; }
        .ims_tabcontainer ul { list-style: none; margin: 0; padding: 0; border: none; }
        .ims_tabcontainer li { list-style: none; display: block; margin: 0; padding: 0 2px 0 0; float: left; }
        .ims_tabcontainer a, .ims_tabcontainer a:visited { color: #333; display: block; text-decoration: none; float: left;
                                                           margin: 0; padding: 2px 10px; background: #fff; line-height: 16px;
                                                           border-top: 1px solid #0863C6; border-left: 1px solid #0863C6; border-right: 1px solid #0863C6; }
        .ims_tabcontainer a:hover, .ims_tabcontainer a:active { text-decoration: underline; }
        .ims_tabcontainer a.active, .ims_tabcontainer a.active:link, .ims_tabcontainer a.active:visited { color: #fff; background: #0863C6; background-image: url(/openims/tile_back.gif); font-weight: bold; text-decoration: none; }
        .ims_tabcontainer a.active:hover, .ims_tabcontainer a.active:active { text-decoration: underline; }
        .ims_tabcontentwrap1 { width: 100%; float: left; clear: left; }
        * html .ims_tabcontentwrap1 { margin-top: -10px; overflow: auto; border: 1px solid #0863C6; } 
          /* Rules starning with * html are IE specific. */
          /* I dont know where that 10px vertical space in IE comes from, but margin-top: -10px gets rid of it */
          /* Somehow, when the content is too large, IE doesnt grow the containing box. Hence the overflow: auto to get scroll bars, and the border to get the border around the scroll bars instead of inside it */
        .ims_tabcontentwrap2 { border: 1px solid #0863C6; margin-right: 4px; margin-bottom: 4px; }
        * html .ims_tabcontentwrap2 { border: none; margin: 0; }
        .ims_tabcontent { margin: 10px; } /* must be margin, not padding, so that the margin is allowed to collapse with margins of elements inside it */
      ';
   }
}

if (!function_exists ("SKIN_WizardbarStyle")) 
{
   function SKIN_WizardbarStyle()
   {
      /* Example with fake buttons (only looks good if all icon images are transparent! */
      // return '
      //   .ims_wizardbarwrap { margin: 10px 0 10px 0; padding: 0; vertical-align: middle; }
      //   .ims_wizardbar { display: inline; margin-left: 0; width: auto; vertical-align: middle; background-color: #ddd; padding: 2px;border: 1px solid #999; }
      //   .ims_wizardbarwrap>.ims_wizardbar { display: inline-block; }
      //   .ims_wizardbar img { vertical-align: middle; border-bottom: 1px solid #999; border-right: 1px solid #999; border-top: 1px solid #fff; border-left: 1px solid #fff; }
      // ';

      return '
        .ims_wizardbarwrap { margin: 10px 0 10px 0; padding: 0; vertical-align: middle; }
        .ims_wizardbar { display: inline; margin-left: 0; width: auto; vertical-align: middle; }
        .ims_wizardbarwrap>.ims_wizardbar { display: inline-block; }
        .ims_wizardbar img { vertical-align: text-bottom; }
      ';
   }
}


if (!function_exists ("SKIN_buildMenu")) { // prevent multiple skin definitions

function SKIN_buildMenu()
{
// TODO: Make sure this function ALWAYS works if DISABLE FLEX is active
    $fakeAll = false;
    // =========== some globals are needed but they may not be changed to garantee backward compatibility of OPENIMS! ===========
    $myconfig = $GLOBALS["myconfig"];
    $supergroupname = ims_supergroupname();

      if ( isset( $_REQUEST["back"] ) )
        $back = $_REQUEST["back"];
      if ( isset( $_REQUEST["mode"] ) )
        $mode = $_REQUEST["mode"];
      if ( isset( $_REQUEST["submode"] ) )
        $submode = $_REQUEST["submode"];
//    }
      if ( isset( $_REQUEST["app"] ) )  $app = $_REQUEST["app"];
      if ( isset( $_REQUEST["table"] ) )  $table = $_REQUEST["table"];

    // JG - jira CORE-18 DMS file compare is showed under cms menu
    if ( $mode=="compare" )
      $mode = "dms";

    $goto = N_MyFullURL();

    if ($mode=="cms") 
    {
      if (!$submode) $submode = "assigned";
    } else if ($mode=="dbm") {
    } else if ($mode=="dms") {
      if (!$submode) $submode = "documents";
      // if currentobject is deleted make currentobject empty
      if($currentobject) {
        $object = MB_Ref("ims_" . $supergroupname . "_objects",$currentobject);
        if(($object["preview"]!="yes") && ($object["published"]!="yes")) $currentobject = "";
      }

    } else if ($mode=="ems") {
      if (!$submode) $submode = "searchemails";
    } else if ($mode=="bpms") {
    } else if ($mode=="ps") {
      SHIELD_NeedsGlobalRight ($supergroupname, "portalmanagement");
      if (!$submode) $submode = "portlets";
    } else if ($mode=="admin") {
      if (!$submode) $submode = "users";
    } else if ($mode=="internal") {
      SHIELD_NeedsGlobalRight ($supergroupname, "system");
    } else if ($mode=="wms") { // DokuWiki
      if (!$submode) $submode = "access";
    } 
    // 20091221 KvD IJSSELGROEP uitlogknop in admin / dms

    $sgn = IMS_Supergroupname();

    $siteinfo = IMS_SiteInfo();
    $supergroupname = $siteinfo["sitecollection"];
    $user_id = SHIELD_CurrentUser ($supergroupname);
    $user = MB_Ref ("shield_$supergroupname"."_users", $user_id);
    $menu["username"] = $user["name"];

    if (is_callable($myconfig[$sgn]["functionlogoff"]) && $myconfig[$sgn]["cookielogin"] == "yes") 
    {
      $logouturl = $myconfig[$sgn]["functionlogoff"]($sgn, $mode);
      if ($logouturl) 
        $menu['links']['top']['logouturl'] = Array( 'url' => $logouturl , 'shorttitle' =>  ML("Uitloggen", "Log off") );
    }

    if($myconfig["showhttphost"]=="yes") 
       $menu['options']['showhttphost'] = getenv("HTTP_HOST");

    if ($back) 
      $menu['links']['top']['back'] = Array( 'url' => $_GET['back'] , 'shorttitle' =>  ML("Terug","Back") );

    $menu['links']['top']['refresh'] = Array( 'url' => 'javascript:window.location=\''.urlencode(N_KeepBefore(N_MyFullURL()."#", "#")).'\';' , 'img' => '/ufc/rapid/openims/refresh.gif' , 'shorttitle' =>  ML("Ververs","Refresh") );

    if ($mode == "history") {
      $parts = N_ExplodeUrl($_REQUEST["back"]);
      if ($parts["query"] && $parts["query"]["mode"]) {
        $mode = $parts["query"]["mode"];
        $submode = $parts["query"]["submode"];
      } else {
        $mode = "cms";
      }
    }

    if ( SHIELD_HasProduct ("cms") )
    {
      if ( !isset( $myconfig[IMS_SuperGroupName()]["hascmsright"] ) || SHIELD_HasGlobalRight(IMS_SuperGroupName(), "cmsright") )
      {
        $menu['links']['products']['cms'] = Array( 'url' => '/openims/openims.php?mode=cms' , 'img' => '/ufc/rapid/openims/edit.gif' , 'shorttitle' => 'CMS' , longtitle => ML("Content Management Server","Content Management Server"), 'selected' => $mode=="cms" );
        if($myconfig[$supergroupname]["cms"]["showassigned"] != "no")
          $menu['links']['submenus']['cms']['assigned'] = Array(  'selected' => ($mode=="cms" && $submode=="assigned") , 'url' => '/openims/openims.php?mode=cms&submode=assigned' , 'shorttitle' => ML("Toegewezen","Assigned") );
        if($myconfig[$supergroupname]["cms"]["showinpreview"] != "no")
          $menu['links']['submenus']['cms']['preview'] = Array(  'selected' => ($mode=="cms" && $submode=="preview") , 'url' => '/openims/openims.php?mode=cms&submode=preview' , 'shorttitle' => ML("In behandeling","In preview") );
        if($myconfig[$supergroupname]["cms"]["showrecentlychanged"] != "no")
          $menu['links']['submenus']['cms']['recent'] = Array(  'selected' =>($mode=="cms" && $submode=="recent") , 'url' => '/openims/openims.php?mode=cms&submode=recent' , 'shorttitle' => ML("Recent gewijzigd","Recently changed") );
        if($myconfig[$supergroupname]["cms"]["showleastrecent"] != "no")
          $menu['links']['submenus']['cms']['leastrecent'] = Array(  'selected' => ($mode=="cms" && $submode=="leastrecent") , 'url' => '/openims/openims.php?mode=cms&submode=leastrecent' , 'shorttitle' => ML("Minst recent","Least recent") );
        if($myconfig[$supergroupname]["cms"]["showexpired"] != "no") 
          $menu['links']['submenus']['cms']['expired'] = Array(  'selected' => ($mode=="cms" && $submode=="expired") , 'url' => '/openims/openims.php?mode=cms&submode=expired' , 'shorttitle' => ML("Verlopen","Expired") );
        if($myconfig[$supergroupname]["cms"]["showallsites"] != "no")
          $menu['links']['submenus']['cms']['sites'] = Array( 'selected' => ($mode=="cms" && $submode=="sites") , 'url' => '/openims/openims.php?mode=cms&submode=sites' , 'shorttitle' => ML("Alle sites","All sites") );
        if ( $myconfig[$supergroupname]["cms"]["showtemplates"] != "no") 
          if ( SHIELD_HasModule ("cms", "templates") && (SHIELD_HasGlobalRight ($supergroupname, "webtemplateedit") || SHIELD_HasGlobalRight ($supergroupname, "webtemplatepublish")))
            $menu['links']['submenus']['cms']['templates'] = Array( 'selected' => ($mode=="cms" && $submode=="templates") , 'url' => '/openims/openims.php?mode=cms&submode=templates' , 'shorttitle' => ML("Templates","Templates") );
         if ( $myconfig[$supergroupname]["cms"]["showtreeview"] == "yes" )
		$menu['links']['submenus']['cms']['treeview'] = Array(  'selected' => ($mode=="cms" && $submode=="treeview") , 'url' => '/openims/openims.php?mode=cms&submode=treeview' , 'shorttitle' => ML("Boom weergave","Tree view") );//, ,'lastitem' => true //removed lastitem JG no function
      }
    }

    if (SHIELD_HasProduct ("dms")) { // ============== DMS MENU ===================

      //ericd 311212 PLEGT-2 needed for "casescopeincaseview" (url "In behandeling" in DMS)
      global $currentfolder;

      if ($myconfig[IMS_SuperGroupName()]["dmsurl"])
        $url = $myconfig[IMS_SuperGroupName()]["dmsurl"];
      else
        $url = "/openims/openims.php?mode=dms";
      $menu['links']['products']['dms'] = Array( 'url' => $url , 'img' => '/ufc/rapid/openims/documents.gif' , 'shorttitle' => ML("DMS","DMS") , 'longtitle' => ML("Document Management Server","Document Management Server") , 'selected' => $mode=="dms" );

// ============== START DMS SUBMENU ===================
      if ($myconfig[IMS_SuperGroupName()]["projectfilter"]=="advanced") 
      {
        if ($myconfig[IMS_SuperGroupName()]["show_generic"] != "no") 
        {
          $generictext = $myconfig[IMS_SuperGroupName()]["generictext"];
          if (!$generictext) $generictext = ML("Algemeen", "Generic");
          $menu['links']['submenus']['dms']['documents'] = Array( 'url' => '/openims/openims.php?mode=dms&submode=documents' , 'shorttitle' => $generictext );
          $menu['links']['submenus']['dms']['documents']["selected"] = ($mode=="dms" && $submode=="documents"&&(substr($currentfolder, 0, 1)!="("));
        }
      } else
        $menu['links']['submenus']['dms']['documents'] = Array( 'selected' => ($mode=="dms" && $submode=="documents") , 'url' => '/openims/openims.php?mode=dms&submode=documents' , 'shorttitle' => ML("Alle","All") );

      if ($myconfig[IMS_SuperGroupName()]["show_perproject"] != "no") {
       if ($myconfig[IMS_SuperGroupName()]["projectfilter"]=="advanced") {      
        $percasetext = $myconfig[IMS_SuperGroupName()]["percasetext"];
        if (!$percasetext) {
          $casetext = strtolower($myconfig[IMS_SuperGroupName()]["casetext"]);
          if (!$casetext) $casetext = ML("dossier", "case");
          $percasetext = "Per " . $casetext;
        }
        $menu['links']['submenus']['dms']['cases'] = Array( 'url' => '/openims/openims.php?mode=dms&submode=cases' , 'shorttitle' => $percasetext );
        if ( $mode=="dms" && ($submode=="cases" || (substr($currentfolder, 0, 1)=="(") ) ) { 
          $menu['links']['submenus']['dms']['cases']['selected'] = true;
        } else {
          $menu['links']['submenus']['dms']['cases']['selected'] = false;
        }
      } else if ($myconfig[IMS_SuperGroupName()]["projectfilter"]!="no") {
         if ( $myconfig[IMS_SuperGroupName()]["projectstext"] )
           $menu['links']['submenus']['dms']['cases']["shorttitle"] = ML("Per","Per")." ".strtolower($myconfig[IMS_SuperGroupName()]["projecttext"]);
         else
           $menu['links']['submenus']['dms']['cases']["shorttitle"] = ML("Per project","Per project");
         $menu['links']['submenus']['dms']['cases'] = Array( 'selected' => ($mode=="dms" && $submode=="projects"), 'url' => '/openims/openims.php?mode=dms&submode=projects' );
        }
      }
      if ($myconfig[IMS_SuperGroupName()]["show_assigned"] != "no") {
        $menu['links']['submenus']['dms']['alloced'] = Array( 'selected' => ($mode=="dms" && $submode=="alloced"), 'url' => '/openims/openims.php?mode=dms&submode=alloced' , 'shorttitle' => ML("Toegewezen","Assigned") , 'longtitle' => ML("Toegewezen","Assigned") );
      }
      if ($myconfig[IMS_SuperGroupName()]["show_recentlychanged"] != "no") {
        $menu['links']['submenus']['dms']['recent'] = Array( 'selected' => ($mode=="dms" && $submode=="recent"), 'url' => '/openims/openims.php?mode=dms&submode=recent' , 'shorttitle' => ML("Recent gewijzigd","Recently changed") , 'longtitle' => ML("Recent gewijzigd","Recently changed") );
      }
      if ($myconfig[IMS_SuperGroupName()]["show_inpreview"] != "no") 
      {
        if ($myconfig["$sgn"]["casescopeincaseview"] == "yes")
        {
          if ($currentfolder and substr($currentfolder, 0, 1) == "(")
          {
            $thecase = substr($currentfolder, 0, 34);
          }  
        }
        if ($thecase)
          $ac_url='/openims/openims.php?mode=dms&thecase='.$thecase.'&submode=activities&securitysection='.$mysecuritysection.'&act=-1';
        else
         $ac_url='/openims/openims.php?mode=dms&submode=activities&securitysection='.$mysecuritysection.'&act=-1'; 
        $menu['links']['submenus']['dms']['activities'] = Array( 'selected' => ($mode=="dms" && $submode=="activities"), 'url' => $ac_url , 'shorttitle' => ML("In behandeling","In preview") );
      }

      function cmp_dmsview_menudata($v1, $v2) {
        if ($v1["sort"]<$v2["sort"]) {
          return -1;
        } else if ($v1["sort"]>$v2["sort"]) {
          return 1;
        } else {
          return 0;
        }
      }

/*      $list = FLEX_LocalComponents (IMS_SuperGroupName(), "dmsview");
      uasort  ($list, 'cmp_dmsview_menudata');
      foreach ($list as $id => $specs) {
        $result = true;
        eval ($specs["code_condition"]);
        if ($result) {
          if ($dmsviewid==$id) {
            echo '<a class="ims_active" href="/openims/openims.php?mode=dms&submode=dmsview&dmsviewid='.$id.'">'.$specs["title"].'</a><br>';
          } else {
            echo '<a class="ims_navigation" href="/openims/openims.php?mode=dms&submode=dmsview&dmsviewid='.$id.'">'.$specs["title"].'</a><br>';
          }
          $menu['links']['submenus']['dmsviewid'.$id] = Array( 'selected' => ($mode=="dms" && $dmsviewid==$id), 'url' => '/openims/openims.php?mode=dms&submode=dmsview&dmsviewid='.$id , 'shorttitle' => $specs["title"] );
        }
      }
*/

      $list = FLEX_LocalComponents (IMS_SuperGroupName(), "dmsview");
      uasort  ($list, 'cmp_dmsview_menudata');
      foreach ($list as $id => $specs) {
        $result = true;
        eval ($specs["code_condition"]);
        if ($result) {
          $url = '/openims/openims.php?mode=dms&submode=dmsview&dmsviewid='.$id;
          if ($specs["code_url"]) $url = N_Eval ($specs["code_url"], get_defined_vars(), "url");
          $menu['links']['submenus']['dms']['dmsview_url'.$id] = Array( 'selected' => ($mode=="dms" && $GLOBALS['dmsviewid']==$id), 'url' => $url , 'shorttitle' => $specs["title"] );
        }
      }

      $list = FLEX_LocalComponents (IMS_SuperGroupName(), "autotableview");
      uasort  ($list, 'cmp_dmsview_menudata');
      foreach ($list as $id => $specs) 
      {
        $result = false;
        $result = N_Eval ($specs["code_condition"], get_defined_vars(), "result");
        if ($result) {
          // gv 11-11-2009 $specs["code_url"] added so that the $url can be altered in the autotableview userinterface
          $url = '/openims/openims.php?mode=dms&submode=autotableview&autotableviewid='.$id;
          if ($specs["code_url"]) $url = N_Eval ($specs["code_url"], get_defined_vars(), "url");
          $menu['links']['submenus']['dms']['code_url'.$id] = Array( 'selected' => ($mode=="dms" && $GLOBALS['autotableviewid']==$id), 'url' => $url , 'shorttitle' => $specs["title"] );
        }
      }

      //ericd 030609 aanpassingen DMS document blok (links boven)
      //1) extra myconf show_search: no, dan geen "zoeken" link
      //2) als wel link, dan middels extra myconf customdmssearch een andere url opgeven
      if ($myconfig[IMS_SuperGroupName()]["show_search"] !== "no") 
      {
        $menu['links']['submenus']['dms']['search'] = Array( 'selected' => ($mode=="dms" && $submode=="search") , 'shorttitle' => ML("Zoeken","Search") ,
          'url' => ( $myconfig[IMS_SuperGroupName()]["customdmssearch"] ? $myconfig[IMS_SuperGroupName()]["customdmssearch"] : '/openims/openims.php?mode=dms&submode=search' ) );
      }

    } // ============= END DMS SUBMENU ===============

    if ( SHIELD_HasProduct ( "sugarcrm" ) )
    {
      //do some stuff to prevent an inline getMSID, which slows everything down
      $specs = array();
      $specs["code"] = '
        uuse("sugar");
        N_Redirect(SUGAR_GetSSOLink($input["module"]));
      ';
      $specs["input"]["module"]="";
      $shieldenc = SHIELD_Encode($specs);
    
      $menu['links']['products']['sugarcrm'] = Array( 'url'=> '/ufc/eval/'.$shieldenc.'/' , 'img' => '/ufc/rapid/openims/template.gif' , 'shorttitle' => ML("CRM","CRM") , 'longtitle' => ML("sugarCRM","sugarCRM"), 'selected' => false );

      if ( $myconfig[$sgn]["sugarcrm"]["contacts"] != 'no' ) // JG - was == "yes", it isn't logic to enable sugarcrm and to have no links
      {
        $specs["input"]["module"]="Contacts";
        $shieldenc = SHIELD_Encode($specs);
        $menu['links']['submenus']['sugarcrm']['contacts'] = Array( 'selected' => false, 'url' => '/ufc/eval/'.$shieldenc.'/' , 'shorttitle' => ML( 'Contactpersonen' , 'Contacts' ) );
      }
      if ( $myconfig[$sgn]["sugarcrm"]["accounts"] != 'no' ) //JG - was == "yes", it isn't logic to enable sugarcrm and to have no links
      {
        $specs["input"]["module"]="Accounts";
        $shieldenc = SHIELD_Encode($specs);
        $menu['links']['submenus']['sugarcrm']['accounts'] = Array( 'selected' => false, 'url' => '/ufc/eval/'.$shieldenc.'/' , 'shorttitle' => ML( 'Organisaties' , 'Accounts' ) );
      }
    }


    //Am : schakeloptie gemaakt voor autorisatie bouwfonds.
 
    if ($myconfig[IMS_SuperGroupName()]["extrasecuritycheckbpmslink"] == "yes")
      {  
         $expr = ( SHIELD_HasProduct ("bpms") && BPMSUIF_HasAccess ($supergroupname) && SHIELD_ValidateAccess_Group ($supergroupname, SHIELD_CurrentUser($supergroupname), "administrators"));
      }else{    
         $expr = ( SHIELD_HasProduct ("bpms") && BPMSUIF_HasAccess ($supergroupname) ); 
      }
      
    if ($expr) 

    {
      $menu['links']['products']['bpms'] = Array( 'url'=> '/openims/openims.php?mode=bpms' , 'img' => '/ufc/rapid/openims/template.gif' , 'shorttitle' => ML("BPMS","BPMS") , 'longtitle' => ML("Bedrijfs Proces Management Server","Business Process Management Server"), 'selected' => $mode=="bpms" );

      // ============= START OF BPMS SUBMENU ===============

      $hasaccess = false;
      $processes = MB_SelectQuery ("shield_".$supergroupname."_processes", '$record["stages"]>0', true);
      foreach ($processes as $table_id => $dummy) 
      {
        if (SHIELD_HasProcessRight ($supergroupname, $table_id, "processview")) $hasaccess=true;
      }

      if ($hasaccess) 
      {
        if (!$submode) $submode = "assigned";
        $menu['links']['submenus']['bpms']['assigned'] = Array( 'selected' => ($mode=="bpms" && $submode=="assigned") , 'url'=> '/openims/openims.php?mode=bpms&submode=assigned' , 'shorttitle' => ML("Toegewezen","Assigned") );
      }
      if ($hasaccess) 
      {
        if (!$submode) $submode = "inprocess";
        $menu['links']['submenus']['bpms']['inprocess'] = Array( 'selected' => ($mode=="bpms" && $submode=="inprocess") , 'url'=> '/openims/openims.php?mode=bpms&submode=inprocess' , 'shorttitle' => ML("Processen","Processes") );
      }

      $hasaccess = false;
      $processes = MB_SelectQuery ("shield_".$supergroupname."_processes", '$record["dataaccess"]=="true"', true);
      foreach ( $processes as $table_id => $dummy )
      {
        if (SHIELD_HasProcessRight ($supergroupname, $table_id, "dataview")) $hasaccess=true;
      }
      if ($hasaccess) 
      {
        if (!$submode) $submode = "data";
        $menu['links']['submenus']['bpms']['data'] = Array( 'selected' => ($mode=="bpms" && $submode=="data") , 'url'=> '/openims/openims.php?mode=bpms&submode=data' , 'shorttitle' => ML("Gegevens","Data") );
      }

      $menu['links']['submenus']['bpms']['search'] = Array( 'selected' => ($mode=="bpms" && $submode=="search") , 'url'=> '/openims/openims.php?mode=bpms&submode=search' , 'shorttitle' => ML("Zoeken","Search") );
      // ============= END OF BPMS SUBMENU ===============
    }

    if ( SHIELD_HasProduct ("ems") || $fakeAll )
    {
      $menu['links']['products']['ems'] = Array(  'selected' => ($mode=="ems") , 'url' => '/openims/openims.php?mode=ems' , 'shorttitle' => ML("EMS","EMS") );
    }

    if (SHIELD_HasProduct ("dbm")  || $fakeAll )
    {
      uuse ("dbm");
      $apps = MB_Query ("ims_dbm_".DBM_WorkSpace()."_appdefs");
      foreach ($apps as $id) {
        $specs = MB_Load ("ims_dbm_".DBM_WorkSpace()."_appdefs", $id);
        $menu['links']['products']["dbm_$id"] = Array( 'url'=> "/openims/openims.php?mode=dbm&app=$id" , 'img' => '/ufc/rapid/openims/properties.gif' , 'shorttitle' => $specs["name"], 'selected' => $mode=="dbm" && $app==$id);
        foreach ($specs["tables"] as $tableid => $tablespecs) {
          $menu['links']['submenus']["dbm_$id"][$tableid] = Array( 'selected' => ($mode=="dbm" && $app==$id && $table==$tableid) , 'url'=> "/openims/openims.php?mode=dbm&app=$id&table=$tableid" , 'shorttitle' => $tablespecs["name"] );
        }
      }
    }

    if (SHIELD_HasProduct ("ps") || $fakeAll )
      $menu['links']['products']['ps'] = Array( 'url'=> '/openims/openims.php?mode=ps' , 'img' => '/ufc/rapid/openims/portal.gif' , 'shorttitle' => ML("PS","PS") , 'longtitle' => ML("Database Manager","Database Manager"), 'selected' => $mode=="ps" );

    if ( $fakeAll || SHIELD_HasProduct ("wms") && SHIELD_HasGlobalRight(IMS_SuperGroupName(), "system")) // DokuWiki
    {
      $menu['links']['products']['wms'] = Array( 'url'=> '/openims/openims.php?mode=wms' , 'img' => '/ufc/rapid/openims/wiki.gif' , 'shorttitle' => ML("WMS","WMS") , 'longtitle' => ML("Wiki Management Server","Wiki Management Server"), 'selected' => $mode=="wms" );
      $menu['links']['submenus']['wms']['access'] = Array(  'selected' =>($mode=="wms"&& $submode=="access") , 'url' => '/openims/openims.php?mode=wms&submode=access' , 'shorttitle' => ML("Toegangsrechten","Access rights") );
      $menu['links']['submenus']['wms']['basic'] = Array(  'selected' =>($mode=="wms"&& $submode=="basic") , 'url' => '/openims/openims.php?mode=wms&submode=basic' , 'shorttitle' => ML("Basis","Basic") );      
      $menu['links']['submenus']['wms']['display'] = Array(  'selected' =>($mode=="wms"&& $submode=="display") , 'url' => '/openims/openims.php?mode=wms&submode=display' , 'shorttitle' => ML("Beeld","Display") );
      $menu['links']['submenus']['wms']['antispam'] = Array(  'selected' =>($mode=="wms"&& $submode=="antispam") , 'url' => '/openims/openims.php?mode=wms&submode=antispam' , 'shorttitle' => ML("Anti-spam","Anti-spam") );
      $menu['links']['submenus']['wms']['edit'] = Array(  'selected' =>($mode=="wms"&& $submode=="edit") , 'url' => '/openims/openims.php?mode=wms&submode=edit' , 'shorttitle' => ML("Pagina wijziging","Page edit") );
      $menu['links']['submenus']['wms']['basic'] = Array(  'selected' =>($mode=="wms"&& $submode=="basic") , 'url' => '/openims/openims.php?mode=wms&submode=basic' , 'shorttitle' => ML("Basis","Basic") );                              
      $menu['links']['submenus']['wms']['link'] = Array(  'selected' =>($mode=="wms"&& $submode=="link") , 'url' => '/openims/openims.php?mode=wms&submode=link' , 'shorttitle' => ML("Links","Links") );
      $menu['links']['submenus']['wms']['media'] = Array(  'selected' =>($mode=="wms"&& $submode=="media") , 'url' => '/openims/openims.php?mode=wms&submode=media' , 'shorttitle' => ML("Media","Media") );
      $menu['links']['submenus']['wms']['advanced'] = Array(  'selected' =>($mode=="wms"&& $submode=="advanced") , 'url' => '/openims/openims.php?mode=wms&submode=advanced' , 'shorttitle' => ML("Geavanceerd","Advanced") );      
    }

    $admin_position = @$myconfig[$sgn]["skin"]["admin_position"]; // JG 16-08-2010 custom admin position
    $show_admin_products = $admin_position != 'top'; // EvK and Robin want this as default behaviour
    $show_admin_top = $admin_position == "both" || $admin_position =='top';

    if (SHIELD_HasGlobalRight ($supergroupname, "system"))
    {
      $adminlinks = Array( 'url'=> '/openims/openims.php?mode=admin' , 'img' => '/ufc/rapid/openims/group.gif' , 'shorttitle' => ML("Admin","Admin"), 'longtitle' => ML("Gebruikers, groepen, ...","Administer users, groups, ..."), 'selected' => $mode=="admin" );
      if ( $show_admin_top )
        $menu['links']['top']['admin'] = $adminlinks;
      if ( $show_admin_products )
        $menu['links']['products']['admin'] = $adminlinks;
    }
    if ( $myconfig[IMS_SuperGroupName()]["customglobalsearch"] )
      $searchurl = $myconfig[IMS_SuperGroupName()]["customglobalsearch"];
    else
      $searchurl = '/openims/openims.php?mode=search';
//    $menu['links']['products']['search'] = Array( 'url'=> '/openims/openims.php?mode=search' , 'img' => '/ufc/rapid/openims/search.gif' , 'shorttitle' => ML("Zoeken","Search"), 'longtitle' => ML("Zoeken","Search"), 'selected' => $mode=="search" );

//  Customer logo
    if ($myconfig[IMS_SuperGroupName()]["customerlogo"])
    {
       $customer_logo_url = $myconfig[IMS_SuperGroupName()]["customerlogo"] ;
       $menu['customerlogo'] = $customer_logo_url;
    } 

    $url = "/openims/openims.php?mode=pers";
    $menu['links']['top']['pers'] = Array( 'url'=> '/openims/openims.php?mode=pers' , 'img' => 'ufc/rapid/openims/user.gif' , 'shorttitle' => ML("Profiel","Profile"), 'longtitle' => ML("Persoonlijke instellingen","Personal settings"), 'selected' => $mode=="pers" );

    if ($myconfig[IMS_SuperGroupName()]["helpurl"]) {
      $url = $myconfig[IMS_SuperGroupName()]["helpurl"];
    } else {
      $url = "http://doc.openims.com/openimsdoc_com/2de2c50a8a2054361e8cf6e9a7d6a5b7.php";
    }
    $menu['links']['top']['help'] = Array( 'target' => '_blank', 'url'=> $url , 'img' => 'ufc/rapid/openims/user.gif' , 'shorttitle' => ML("Help","Help"), 'longtitle' => ML("Online help","Online help"), 'selected' => false );

    if ($myconfig[IMS_SuperGroupName()]["hidelanguages"] != "yes") 
//  echo ML_LanguageSelect($goto);
    $menu["options"]["hidelanguages"] = $myconfig[IMS_SuperGroupName()]["hidelanguages"] != "yes" ? "no" : "yes";

    $adm_mode = $_GET['mode'];
    $adm_submode = $_GET['submode'];
    // =============== START ADMIN SUBMENU =================
    if ( SHIELD_HasGlobalRight ($supergroupname, "system") )
    {
      $menu['links']['submenus']['admin']['users'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="users") , 'url' => '/openims/openims.php?mode=admin&submode=users' , 'shorttitle' => ML("Gebruikers","Users") );
      $menu['links']['submenus']['admin']['groups'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="groups") , 'url' => '/openims/openims.php?mode=admin&submode=groups' , 'shorttitle' => ML("Groepen","Groups") );
      if ($myconfig["serverhassitesecurity"] == "yes") {
        $menu['links']['submenus']['admin']['site_security'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="site_security") , 'url' => '/openims/openims.php?mode=admin&submode=site_security' , 'shorttitle' => ML("Beveiliging per site","Security per site") );
      }
      $menu['links']['submenus']['admin']['report'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="report") , 'url' => '/openims/openims.php?mode=admin&submode=report' , 'shorttitle' => ML("Overzicht rechten","Security overview") );
      $menu['links']['submenus']['admin']['workflow'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="workflow") , 'url' => '/openims/openims.php?mode=admin&submode=workflow' , 'shorttitle' => ML("Document workflows","Document workflows") );
      if($myconfig[IMS_SuperGroupName()]["casetypes"]=="yes")
        $menu['links']['submenus']['admin']['casetypes'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="casetypes") , 'url' => '/openims/openims.php?mode=admin&submode=casetypes' , 'shorttitle' => ML("Dossiercategorieën","Casetypes") );
      $menu['links']['submenus']['admin']['process'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="process") , 'url' => '/openims/openims.php?mode=admin&submode=process' , 'shorttitle' => ML("Data en processen","Data and processes") );
      if($myconfig["mail"]["multiarchive"])
        $menu['links']['submenus']['admin']['ems_permissions'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="ems_permissions") , 'url' => '/openims/openims.php?mode=admin&submode=ems_permissions' , 'shorttitle' => ML("EMS rechten","EMS permissions") );
      $menu['links']['submenus']['admin']['fields'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="fields") , 'url' => '/openims/openims.php?mode=admin&submode=fields' , 'shorttitle' => ML("Velden","Fields") );
      $menu['links']['submenus']['admin']['shorturl'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="shorturl") , 'url' => '/openims/openims.php?mode=admin&submode=shorturl' , 'shorttitle' => ML("Korte URL's","Short URLs") );
      $menu['links']['submenus']['admin']['undelete'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="undelete") , 'url' => '/openims/openims.php?mode=admin&submode=undelete' , 'shorttitle' => ML("Prullenbak", "Recycle bin") );
    }
    if (count(ML_ModifiableLanguages()))
      $menu['links']['submenus']['admin']['multilang'] = Array(  'selected' => ($mode=="admin" && $submode=="multilang") , 'url' => '/openims/openims.php?mode=admin&submode=multilang' , 'shorttitle' => ML("Meertaligheid", "Multilinguality") );
    if ( BLACK_OK() )
      $menu['links']['submenus']['admin']['maint'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="maint") , 'url' => '/openims/openims.php?mode=admin&submode=maint' , 'shorttitle' => ML("Onderhoud","Maintenance") );
    if ( !$myconfig[IMS_SuperGroupName()]["hideflex"] ) 
    {
      if (BLACK_OK() && SHIELD_HasGlobalRight ($supergroupname, "develop", $mysecuritysection))
      {
        $menu['links']['submenus']['admin']['flex'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="flex") , 'url' => '/openims/openims.php?mode=admin&submode=flex' , 'shorttitle' => ML("Inrichting","Configuration") );
        $menu['links']['submenus']['admin']['flex_code'] = Array(  'selected' => ($adm_mode=="admin" && $adm_submode=="flex_code") , 'url' => '/openims/openims.php?mode=admin&submode=flex_code' , 'shorttitle' => ML("Code tester","Code tester") );
      }
    }

    $txt = strtoupper( $_GET['mode'] );
    if ( $txt == 'HISTORY' ) // (JG) upper case is ugly
      $txt = ML("Historie","History");
    if ( $txt == 'PERS' )
      $txt = ML("Profiel","Profile");
    if ( $txt == 'SEARCH' )
      $txt = ML("Zoeken","Search");
    if ( $txt == 'ADMIN' )
      $txt = ML("Admin","Admin");
    if ( $mode=="dms" && $submode=="documents" && $GLOBALS['currentfolder'] ) //JG 18-8-2010 - show casename in html title
      $menu["current_casename"] = CASE_visiblecasename();
    $menu["html_title"] = "OpenIMS " . trim( $txt . " " . $menu['links']['submenus'][$_GET['mode']][$_GET['submode']]["shorttitle"] . " " . @$menu["current_casename"] );
    $menu["imsversion"] = "OpenIMS " . trim( $txt . " " . $menu['links']['submenus'][$_GET['mode']][$_GET['submode']]["shorttitle"] );

    if ( $mode=="compare" )
      $mode = "dms";
    if ($mode == "dbm") {
      $menu["selected"]["mode"] = $mode."_".$app; // qqq
      $menu["selected"]["submode"] = "";
    } else {
      $selected_mode = ( isset( $menu['links']['products'][$mode] ) ) ? $mode : key( $menu['links']['products'] );
      $selected_submode = isset( $menu['links']['submenus'][$selected_mode][$submode] ) ? $submode : key( $menu['links']['submenus'][$selected_mode] );
      $menu['links']['products'][$selected_mode]["selected"] = true;
      $menu["selected"]["mode"] = $selected_mode; // $mode
      $menu["selected"]["submode"] = $selected_submode; // $submode
    }

//    print_r( $menu["selected"] );

/*    if ( $_GET['mode'] != 'admin' && $_GET['mode'] != 'search' )
    {
      $_SESSION['SES_mode'] = $mode;
      $_SESSION['SES_submode'] = $submode;
    }*/
    // =============== END ADMIN SUBMENU =================



    return $menu;
}

} 

} // function SKINS_Init ()

?>