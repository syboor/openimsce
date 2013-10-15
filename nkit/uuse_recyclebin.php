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

function RECYCLEBIN_select_list( $specs , $list ) 
{
  global $recyclebin_has_filter;
  $recyclebin_has_filter = true;
  
  if ( $_POST["make_selection"] )
  {
    foreach( $list AS $key => $value )
    {
      RECYCLEBIN_Select ( $key );
    }
  } 
}

function RECYCLEBIN_DoSelect ($sgn, $id, $user) 
{ // low level 
  $selected = &MB_Ref ("local_recyclebin_".$sgn, $user);
  $selected[$id] = "*";
}

function RECYCLEBIN_Select ($id) // files and shortcuts
{
    RECYCLEBIN_DoSelect (IMS_SuperGroupName(), $id, SHIELD_CurrentUser());
}

function RECYCLEBIN_Unselect ($id) // files and shortcuts
{
  $selected = &MB_Ref ("local_recyclebin_".IMS_SuperGroupName(), SHIELD_CurrentUser());
  unset ($selected[$id]);
}

function RECYCLEBIN_load_selected()
{
  $sgn = IMS_SuperGroupName();
  $usr = SHIELD_CurrentUser($sgn);
  return MB_Load ("local_recyclebin_".$sgn, $usr);
}

function RECYCLEBIN_Selected($id="")
{
  $selected = RECYCLEBIN_Load_selected();
  if ($id) {
    return $selected[$id] ? true : false;
  } else {
    return count ($selected);
  }
}

function RECYCLEBIN_UnselectAll () // files and shortcuts
{
  $multi = &MB_Ref ("local_recyclebin_".IMS_SuperGroupName(), SHIELD_CurrentUser());
  $multi = array();
}

function RECYCLEBIN_checkbox($key)
{
  $specs["input"] = $key;
  $specs["state"] = RECYCLEBIN_Selected($key);
  $specs["on_code"] = 'uuse("recyclebin"); RECYCLEBIN_Select ($input);';
  $specs["off_code"] = 'uuse("recyclebin"); RECYCLEBIN_UnSelect ($input);';
  $specs["js_on_code"] = "selectcounter = selectcounter + 1;deselect_link = deselect_link_dyn;
    " . DHTML_SetDynamicObject ("selectcounter") . DHTML_SetDynamicObject ("deselect_link");

  $specs["js_off_code"] = "
    selectcounter = selectcounter - 1;
    if ( selectcounter== 0 )
      deselect_link = '';
    " . DHTML_SetDynamicObject ("selectcounter") . DHTML_SetDynamicObject ("deselect_link");
  $vink = DHTML_IntelliImage($specs, $key) ;
  echo $vink . " ";
}

function RECYCLEBIN_UndeleteBlock() {
  global $myconfig;

  if (!SHIELD_HasGlobalRight(IMS_SuperGroupName(), "system")) SHIELD_Unauthorized();

  $content = '';

  global $type;
  if (($type != "webpages") && SHIELD_HasProduct("dms")) {
    $type = "documents";
  } else {
    $type = "webpages";
  }
  $content .= '<p><font face="arial" size=2>' . ML('Toon verwijderde', 'Show deleted') . ' ';
  if ($type == "documents") {
    $content .= '<b>' . ML('documenten', 'documents') .  ' | </b><a href="' . N_AlterUrl(N_MyFullUrl(), "type", "webpages") . '">' . ML('webpagina&#039;s', 'webpages') . '</a>';
  } else {
    $content .= '<a href="' . N_AlterUrl(N_MyFullUrl(), "type", "documents") . '">' . ML('documenten', 'documents') .  '</a><b> | ' . ML('webpagina&#039;s', 'webpages') . '</b>';
  }
  $content .= '</font></p>';

  if ($type == "documents") {
  
    $recoverform = array();
    $recoverform["title"] = ML("Herstel verwijderd document", "Recover deleted file");
    $recoverform["input"]["sgn"] = IMS_SuperGroupName();
    $recoverform["metaspec"]["fields"]["id"]["type"] = "string";
    $recoverform["precode"] = '
      global $id;
      $data["id"] = $id;
    ';
    $recoverform["formtemplate"] = '<br><table>
      <tr><td colspan=2><font face="arial" size=2><b>' . ML("Herstel verwijderd document", "Recover deleted file") . '</td><tr>
      <tr><td><font face="arial" size=2><b>'.ML("ID","ID").':</b></font></td>
          <td>[[[id]]]</td></tr>
      <tr><td colspan=2>&nbsp;</td></tr>
      <tr><td colspan=2><font face="arial" size=2>'.ML("Indien de folder niet meer bestaat, wordt het document 
in de hoofdfolder geplaatst. Hierdoor kan het beveiligingsregime veranderen.", "If the folder does not exist 
anymore, the document is placed in the main folder. The security regime of the document may change as a 
result of this.").'</td></tr>
      <tr><td colspan=2>&nbsp;</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>';
    $recoverform["postcode"] =' 
      uuse ("search");
      $id = trim ($data["id"]);
      if (!$id) FORMS_ShowError (ML("Foutmelding","Error"), ML("Het ID ontbreekt","The ID is missing"), true);
      $object = &MB_Ref ("ims_".$input["sgn"]."_objects", $id);
      if ($object["objecttype"]!="document") FORMS_ShowError (ML("Foutmelding","Error"), ML("Geen document 
gevonden met dit ID","No document found with this ID"), true);
      if ($object["preview"]=="yes" || $object["published"]=="yes") FORMS_ShowError (ML("Foutmelding","Error"), 
ML("Het document is niet verwijderd","The document is not deleted"), true);
      if ($object["destroyed"]) FORMS_ShowError (ML("Foutmelding","Error"), ML("Het document kan niet worden 
teruggehaald","The document could not be retrieved"), true);

      $tree = CASE_TreeRef ($input["sgn"], $object["directory"]);
      if (!$tree["objects"][$object["directory"]]["shorttitle"]) {
        $object["directory"] = "root";
      }

      if (is_array ($object["history"])) foreach ($object["history"] as $verid => $specs) {    
        if ($specs["published"]=="yes") {
          $object["published"]="yes";
          SEARCH_AddDocumentToDMSIndex ($input["sgn"], $id);
        }

      }  
      $object["preview"]="yes";
      SEARCH_AddPreviewDocumentToDMSIndex ($input["sgn"], $id);
      N_Log ("deleted objects", "UNDELETE $id");
      $docurl = "/openims/openims.php?mode=dms&currentfolder=".$object["directory"]."&currentobject=".$id;
      $gotook = "closeme&parentgoto:$docurl";
    '; 
    $recoverurl = FORMS_URL ($recoverform);

    if (!function_exists ("DMS_MouseOver")) {
    // DMS mouse over
      $internal_component = FLEX_LoadImportableComponent ("support", "08fa2037f2f020a44e9aac15d6d92135");
      $internal_code = $internal_component["code"];
      eval ($internal_code);
    }

    $lijst_select = array (
      '$record["objecttype"]=="document"' => true,
      '$record["published"]' => 'no',
      '$record["preview"]' => 'no',
    );

    if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
      $sort_default_col = 5;
      $sort_default_dir = "d";
      $lijst_tablespecs = array ("sort_default_col" => $sort_default_col, "sort_default_dir" => 
$sort_default_dir, "sort_map_1" => 2, 
        "sort_1"=>"auto", "sort_3"=>"auto", "sort_4"=>"auto", "sort_5"=>"date", "sort_6"=>"auto"); 
    } else {
      $sort_default_col = 4;  
      $sort_default_dir = "d";
      $lijst_tablespecs = array ("sort_default_col" => $sort_default_col, "sort_default_dir" => 
$sort_default_dir, "sort_map_1" => 2, 
        "sort_1"=>"auto", "sort_3"=>"auto", "sort_4"=>"date", "sort_5"=>"auto"); 
    }

    $lijst_tableheads = array(ML("Document","Document"),"");
    $lijst_tableheads = N_array_merge($lijst_tableheads, array(ML("Status","Status")));
    if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
      $lijst_tableheads = N_array_merge($lijst_tableheads, array(ML("Versie","Version")));
    }
    $lijst_tableheads = N_array_merge($lijst_tableheads, array(ML("Verwijderd","Deleted"))); 
    $lijst_tableheads = N_array_merge($lijst_tableheads, array(ML("Toegewezen","Assigned")));

    $lijst_sort = array ('',  'QRY_DMS_Name_v1 ($record)'); 
    $lijst_sort = N_array_merge($lijst_sort, array('QRY_DMS_Status_v1 ("'.IMS_SuperGroupName().'", $key, 
$record)'));
    if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") {
      $lijst_sort = N_array_merge($lijst_sort, array('QRY_DMS_Version_v1 ("'.IMS_SuperGroupName().'", $key, 
$record)')); 
    }
    $lijst_sort = N_array_merge($lijst_sort, array('QRY_DMS_Changed_v1 ($record)'));
    $lijst_sort = N_array_merge($lijst_sort, array('QRY_DMS_Assigned_v1 ("'.IMS_SuperGroupName().'", 
$record)')); 

    $lijst_filterexp = 
      'QRY_DMS_Name_v1 ($record) . " " .
       QRY_DMS_Status_v1 ("'.IMS_SuperGroupName().'", $key, $record) . " " .
       N_VisualDate (QRY_DMS_Changed_v1 ($record), 1, 1) . " " .
       QRY_DMS_Assigned_v1 ("'.IMS_SuperGroupName().'", $record)
      '; 

    if ($myconfig[IMS_SuperGroupName()]["invisiblemetadatafilter"] == "yes") {
      $lijst_filterexp .= ' . " " . QRY_ListMetaDataValues("'.IMS_SuperGroupName().'",$record)';     
    }       



    $lijst_content = array ('
        $findme;
        echo "<nobr>"; 
        $truekey = $key;
        $image = FILES_Icon (IMS_SuperGroupName(), $key, false, "preview"); 
        $ob = $object;
        uuse("files");
     
        '.($myconfig[IMS_SuperGroupName()]["allowcleanupdeletedobjects"]=="yes"?'RECYCLEBIN_checkbox($key);':'').'

        $histurl = "/openims/openims.php?mode=history&back=".urlencode(N_MyFullUrl())."&object_id=".$key;
        $histtitle = ML("Historie van","History of")." &quot;".$object["shorttitle"]."&quot;";
        $histlink = "<a title=\"$histtitle\" href=\"".$histurl."\"><img border=0 
src=\"/ufc/rapid/openims/history_small.gif\"></a>";
        echo $histlink;

        if (N_FileExists("html::/".IMS_SuperGroupName()."/preview/objects/".$key."/".$object["filename"])) {
          $viewurl = FILES_TransViewPreviewURL (IMS_SuperGroupName(), $key);
      $viewtitle = DMS_MouseOver(IMS_SuperGroupName(), $key, "view"); 
          echo "&nbsp;<a title=\"$viewtitle\" href=\"$viewurl\">";
          echo "<img border=0 height=16 width=16 src=\"/ufc/rapid/openims/view.gif\">";
          echo "</a>";
          $recoverurl = str_replace("&encspec", "&id=$key&encspec", "'.$recoverurl.'");
          echo "&nbsp;<a title=\"".ML("Haal verwijderd bestand terug","Recover deleted file")."\" 
href=\"".$recoverurl."\"><img border=0 src=\"/ufc/rapid/openims/wand.gif\"></a><br>";
        }
      '
    ,
      '
        $truekey = $key;
        $ob = $object;

        $commandtitle = DMS_MouseOver(IMS_SuperGroupName(), $key, "command");

        if($truekey=="' . $currentobject . '") {
          $commandstyle="class=\"ims_active\"";
        } else {
          $commandstyle="class=\"ims_navigation\"";
        }
        if (!trim($sortvalue)) {
          echo "&nbsp;???".$ext; 
        } else {

          echo "&nbsp;".str_replace ("_", " ", $sortvalue).$ext."</a>"; 
        }

        if (!N_FileExists("html::/".IMS_SuperGroupName()."/preview/objects/".$key."/".$object["filename"])) { 
          echo "<br/>&nbsp;<small>" . ML("Verwijderd van disk", "Deleted from disk") . "</small>";
        }
      ');
    $lijst_content = N_array_merge($lijst_content, array('echo $sortvalue;'));
    if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes") { //version
      $lijst_content = N_array_merge($lijst_content, array('echo $sortvalue;'));
    }
    $lijst_content = N_array_merge($lijst_content, array('echo "<nobr>".N_VisualDate ($sortvalue, 1, 
1)."</nobr>";')); //last modified

    $lijst_content = N_array_merge($lijst_content, array('echo "$sortvalue&nbsp;";'));

    if(!$myconfig[IMS_SuperGroupName()]["dmsautotablemaxlen"]) {
      $dmsautotablemaxlen= 50;

    } else {
      $dmsautotablemaxlen= $myconfig[IMS_SuperGroupName()]["dmsautotablemaxlen"];
    }

    if ( $myconfig[IMS_SuperGroupName()]["extended_recyclebin"]=="yes" )
    {
	$lijst_tableheads = N_array_merge($lijst_tableheads, array(ML("Verwijderd door","Deleted by")));

        $lijst_content = N_array_merge($lijst_content, array('echo QRY_DMS_Deleted_By_v1 ("'.IMS_SuperGroupName().'", $record);'));
	$lijst_sort = N_array_merge($lijst_sort, array('strtolower(QRY_DMS_Deleted_By_v1 ("'.IMS_SuperGroupName().'", $record))'));

        $lijst_colfilterexp = Array(
        1 => 'QRY_DMS_Name_v1 ($record)',
        2 => 'QRY_DMS_Status_v1 ("'.IMS_SuperGroupName().'", $key, $record)' );

       if ($myconfig[IMS_Supergroupname()]["versioning"]=="yes")
         N_array_merge($lijst_colfilterexp, array('echo QRY_DMS_Version_v1 ("'.IMS_SuperGroupName().'", $record);'));

        N_array_merge( $lijst_colfilterexp, 
        Array( 3 => 'N_VisualDate (QRY_DMS_Changed_v1 ($record), 1, 1)',
        4 => 'QRY_DMS_Assigned_v1 ("'.IMS_SuperGroupName().'", $record)',
        5 => 'QRY_DMS_Deleted_By_v1 ("'.IMS_SuperGroupName().'", $record)'
      ) );
      if ( isset( $lijst_tablespecs["sort_6"] ) )
        $lijst_tablespecs["sort_7"] = "auto";
      else
        $lijst_tablespecs["sort_6"] = "auto";
    }

    $lijst = array (
      "name" => "dmsgrid",
      "style" => "ims",
      "filter" => "",
      "maxlen" => $dmsautotablemaxlen,
      "table" => "ims_".IMS_SuperGroupName()."_objects",
      "select" => $lijst_select,
      "tablespecs" => $lijst_tablespecs,
      "tableheads" => $lijst_tableheads,
      "sort" => $lijst_sort,
      "filterexp" => $lijst_filterexp,
      "content" => $lijst_content
    );

    if ( $myconfig[IMS_SuperGroupName()]["extended_recyclebin"]=="yes" )
    {
	//$lijst[	"alwaysfilter" ] = "yes";
        unset( $lijst["filterexp"] );
        $lijst["colfilter"] = "yes";
        $lijst["leftcolfilter"] = "no";
	$lijst["colfilterexp"] = $lijst_colfilterexp;

        $lijst["colfiltertype"] = Array
       	( // Filtertype per kolom. Sleutels van deze array zijn significant. Begin te nummeren bij kolom 1, en sla geen kolommen over!!!
               1 => "",
               2 => "",
               3 => "",
               4 => "",
               5 => ""
        );
    }

    eval ("global \$tblblk_".$lijst["name"].";");
    eval ("\$block = \$tblblk_".$lijst["name"].";");

    if(!isset($block)) {
      $lijst["gotoobject"] = $currentobject;
    }
    $lijst["filter_callback_function"] = "RECYCLEBIN_select_list";
	$content .= TABLES_Auto ( $lijst );
	
  if ($myconfig[IMS_SuperGroupName()]["allowcleanupdeletedobjects"] == "yes" && $_GET["type"]!="webpages") {
    $cleanupform = array();
    $cleanupform["title"] = ML("Maak prullenbak leeg", "Empty recycle bin");
    $cleanupform["input"]["sgn"] = IMS_SuperGroupName();
    $cleanupform["input"]["whodidit"] = SHIELD_CurrentUser(IMS_SuperGroupName());
    $cleanupform["gotook"] = "closeme&refreshparent";
    $cleanupform["metaspec"]["fields"]["daystokeep"]["type"] = "smallstring";
    $cleanupform["metaspec"]["fields"]["sure"]["type"] = "list";
    $cleanupform["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
    $cleanupform["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
    $cleanupform["metaspec"]["fields"]["deleterecord"]["type"] = "yesno";

    $cleanupform["formtemplate"] = '
      <table width="600px">
        <tr><td colspan=2><font face="arial" size=2><b>' . ML("Verwijderde documenten en webpagina's permanent 
van disk verwijderen", "Remove deleted documents en webpages from disk permanently") . '</td><tr>
        <tr><td colspan=2><font face="arial" size=2 color="red"><b>' . ML("Deze actie kan niet ongedaan gemaakt 
worden", "This action can not be undone") . '</td><tr>
        <tr>___tplpart___</tr>
        <tr><td><font face="arial" size=2>' . ML("Verwijder ook de database-records", "Delete database-records 
as well") . '</td>
            <td>[[[deleterecord]]]</td>
        </tr>
        <tr><td colspan=2>&nbsp;</td></tr>
        <tr><td><font face="arial" size=2>' . ML("Weet u het zeker?", "Are you sure") . 
'</td><td>[[[sure]]]</td></tr>
        <tr><td colspan=2>&nbsp;</td></tr>
        <tr><td colspan=2><center>[[[OK]]] &nbsp; [[[Cancel]]]</center></td></tr>
      </table>
    ';
    $cleanupform["precode"] = '
      // Added by JG:
      uuse("recyclebin");
	  $portal["selected_files"] = $selected_files = RECYCLEBIN_load_selected();

      if ( count( $selected_files ) == 0 )
      {  
        $data["daystokeep"] = 31;
        $tplpart = \'<td><font face="arial" size=2>\' . ML("Bewaar documenten die minder dan", "Keep documents deleted less than") . \'</td>
                    <td><font face="arial" size=2><font face="arial" size=2>[[[daystokeep]]] \' . ML("dagen geleden verwijderd zijn.", "days ago") . "</td>";
      } else {
        $tplpart = \'<td colspan="2">\'.ML("Verwijder","Delete").\' <b>\' . count( $selected_files ) . \'</b> \'.ML("in de prullenbak geselecteerde documenten.","in the recylebin selected documents.").\'</td>\';
        $data["daystokeep"] = 0;
      }
      $formtemplate = str_replace( "___tplpart___" , $tplpart, $formtemplate );
    ';
    $cleanupform["postcode"] = 'uuse("recyclebin");
	  if ($data["sure"] == "yes") {
        $logmsg = "Garbage collector called by " . $input["whodidit"] . " (deleted documents and webpages 
permanently deleted from disk). Daystokeep = " . $data["daystokeep"] . ", Deleterecord = " . 
($data["deleterecord"] ? "true" : "false") . ", RECYCLEBIN_load_selected = " . print_r( $portal["selected_files"] , 1 );
        N_Log("garbage_collector", $logmsg);
        N_Log("deleted objects", $logmsg);

        uuse("maint");uuse("recyclebin");
	$selected_files = $portal["selected_files"];
	if ( count( $selected_files )==0 ) $selected_files = false;
		
        MAINT_CollectGarbage($input["sgn"], $data["daystokeep"], $data["deleterecord"], false , $selected_files );
		RECYCLEBIN_UnselectAll ();
        $message = array();
        $message["gotook"] = $gotook;
        $message["formtemplate"] = "<font face=\"arial\" size=2><p>" . ML("De documenten worden in de 
achtergrond verwijderd.", "The documents are being deleted in the background") . 
"</p></font><p><p><center>[[[OK]]]</p>";
        N_Redirect(FORMS_URL($message));
      }
    ';
    $cleanupurl = FORMS_URL($cleanupform);

    $specs = Array();
    $specs["postcode"] = 'uuse("recyclebin");RECYCLEBIN_UnselectAll();$gotook="closeme&refreshparent";';
    $url = forms_url( $specs );
	
    $files_count = RECYCLEBIN_Selected();

    $content_before .= '<font face="arial" size=2><a href="'.$cleanupurl.'">'.ML("Prullenbak leegmaken", "Empty recycle bin").'</a></font> ';

    $content_before .= DHTML_DynamicObject ( $files_count , "selectcounter" , true ) . ' ' . ML( "documenten geselecteerd" , "files selected" );

    $deselect_link = ' (<a href="'.$url.'">' . ML( 'de-selecteren' , 'unselect' ) . '</a>)';
	
    $content_before .= DHTML_EmbedJavaScript ("deselect_link_dyn = '".str_replace ("'", "\\'", $deselect_link )."';");
	
    if ( $files_count>0 ) {
      $content_before .= DHTML_DynamicObject ( $deselect_link , 'deselect_link' , true );
    } else {
      $content_before .= DHTML_DynamicObject ( "" , 'deselect_link' , true );
    }

	if ( $GLOBALS["recyclebin_has_filter"] )
	{
	  $content_before .= ' <a href="javascript:document.getElementById(\'make_selection\').submit();">'.ml('huidige selectie aanvinken','check current selection').'</a><form id="make_selection" method="post"><input type="hidden" name="make_selection" value="1" /></form>';
	} 

    $selectcounter = 0 + $files_count;
    $content_before .= DHTML_EmbedJavaScript ("selectcounter=$selectcounter;");

    $content_before .= ' </p>';
    $content = $content_before . $content;
  }	
    
  } elseif ($type == "webpages") { // undelete webpages
    uuse("tinymce");
    $recoverform = array();
    $recoverform["title"] = ML("Herstel verwijderde webpagina", "Recover deleted webpage");
    $recoverform["input"]["sgn"] = IMS_SuperGroupName();
    $recoverform["metaspec"]["fields"]["id"]["type"] = "string";
    $recoverform["precode"] = '
      global $id;
      $data["id"] = $id;
    ';
    $recoverform["formtemplate"] = '<br><table>
      <tr><td colspan=2><font face="arial" size=2><b>' . ML("Herstel verwijderde webpagina", "Recover deleted 
webpage") . '</td><tr>
      <tr><td><font face="arial" size=2><b>'.ML("ID","ID").':</b></font></td>
          <td>[[[id]]]</td></tr>
      <tr><td colspan=2>&nbsp;</td></tr>
      <tr><td colspan=2><font face="arial" size=2>'.ML("Indien de bovenliggende pagina niet meer bestaat, wordt de pagina onder de startpagina geplaatst.", "If the parent page does not exist 
anymore, the page will be placed below the start page.").'</td></tr>
      <tr><td colspan=2><font face="arial" size=2>'.ML("De webpagina zal niet gepubliceerd worden.", "The webpage will not be published.").'</td></tr>
      <tr><td colspan=2>&nbsp;</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
      </table>';
    $recoverform["postcode"] =' 
      uuse ("search");
      $id = trim ($data["id"]);
      if (!$id) FORMS_ShowError (ML("Foutmelding","Error"), ML("Het ID ontbreekt","The ID is missing"), true);
      if (strpos($id, "webgen") !== false) FORMS_ShowError(ML("Foutmelding","Error"), ML("Geconverteerde pagina&#039;s kunnen niet teruggeplaatst worden", "Converted pages can not be restored"), true);
      $object = &MB_Ref ("ims_".$input["sgn"]."_objects", $id);
      if ($object["objecttype"]!="webpage") FORMS_ShowError (ML("Foutmelding","Error"), ML("Geen webpagina 
gevonden met dit ID","No webpage found with this ID"), true);
      if ($object["preview"]=="yes" || $object["published"]=="yes") FORMS_ShowError (ML("Foutmelding","Error"), 
ML("De webpagina is niet verwijderd","The webpage is not deleted"), true);
      if ($object["destroyed"]) FORMS_ShowError (ML("Foutmelding","Error"), ML("De webpagina kan niet worden teruggehaald","The webpage can not be retrieved"), true);

      $object = &MB_Ref("ims_".$input["sgn"]."_objects",$id);
      $object["stage"] = $workflow[$object["stage"]]["stageafteredit"];
      if (!$object["stage"]) $object["stage"] = 1;
      if ($object["stage"] > $workflow["stages"]) $object["stage"] = $workflow["stages"];
      $object["preview"] = "yes";
      $object["published"] = "no";

      $site_id =  IMS_Object2Site($input["sgn"], $id);
      $parent_id = $object["parent"];
      $parent = MB_Ref("ims_".$input["sgn"]."_objects", $parent_id);
      if (!($parent_id && ($parent["preview"] == "yes" || $parent["published"] == "yes"))) {
        $parent_id = $site_id. "_homepage";
      }
      IMS_SetLocation ($input["sgn"], $id, $parent_id);
      
      N_Log ("deleted objects", "UNDELETE $id");
      $webpageurl = "/" . $site_id . "/" . $id . ".php?activate_preview=yes";
      $gotook = "closeme&parentgoto:$webpageurl";
    ';
    $recoverurl = FORMS_URL ($recoverform);

    $specs = array();
    $specs["select"] = array(
      '$record["objecttype"]' => 'webpage',
      '$record["preview"]' => 'no',
      '$record["published"]' => 'no',
    );
    $list = MB_TurboMultiQuery("ims_".IMS_SuperGroupName()."_objects", $specs);
    MB_MultiLoad("ims_".IMS_SuperGroupName()."_objects", $list);
    $count = count($list);
    $maxrows = 50;
    
    if (is_array($list)) {
      T_Start ("ims", array ("sort"=>"cms_undelete", "sort_default_col" => 5, "sort_default_dir" => "d", "sort_map_1" => 2, "sort_1" => "auto", "sort_3" => "auto", "sort_4" => "auto", "sort_5" => "date")); 
      echo ML("Pagina","Page");
      T_Next();
      T_Next();
      echo ML("Lange titel","Long title");
      T_Next();
      echo ML("Site","Site");
      T_Next();
      echo ML("Verwijderd","Deleted");
      T_Newrow();
      reset($list);
      while (list($key)=each($list)) {
        $rec = MB_Ref ("ims_".IMS_SuperGroupName()."_objects", $key);
        $histurl = "/openims/openims.php?mode=history&back=".urlencode(N_MyFullUrl())."&object_id=".$key;
        $histtitle = ML("Historie van","History of")." &quot;".$rec["parameters"]["preview"]["shorttitle"]."&quot;";
        $histlink = "<a title=\"$histtitle\" href=\"".$histurl."\"><img border=0 
src=\"/ufc/rapid/openims/history_small.gif\"></a>";
        echo $histlink;
        if (N_FileExists("html::/".IMS_SuperGroupName()."/preview/objects/".$key."/page.html")) {
          $file = "page.html";

          if (!TINYMCE_isinplace($key)) {
            // Dont show view link if the inplace editor is used (it is still possible to view the document from the history)
            if ($rec["editor"]=="Form") $exe = "winword.exe";
            if ($rec["editor"]=="Microsoft Word") $exe = "winword.exe";
            if ($rec["editor"]=="Microsoft Excel") $exe = "excel.exe";
            if ($rec["editor"]=="Microsoft Powerpoint") $exe = "powerpnt.exe";
            if ($rec["editor"]=="PHP Code") {
              $exe = "notepad.exe";
              $file = "page.php";
            }
            $viewurl = IMS_GenerateViewURL ("\\".IMS_SuperGroupName()."\\preview\\objects\\".$key."\\", $file, $exe, true);
            echo '&nbsp;<a title="' . ML("Bekijk", "View") . ' &quot;' . $rec["parameters"]["preview"]["shorttitle"] . '&quot;" href="' . $viewurl . '">';
            echo "<img border=0 height=16 width=16 src=\"/ufc/rapid/openims/view.gif\">";
            echo "</a>";
          }

          $myrecoverurl = str_replace("&encspec", "&id=$key&encspec", $recoverurl);
          echo '&nbsp;<a title="'.ML("Haal verwijderde webpagina terug","Recover deleted webpage").'" 
href="'.$myrecoverurl.'"><img border=0 src="/ufc/rapid/openims/wand.gif"></a><br>';
        }
        T_Next();
        if ($rec["parameters"]["preview"]["shorttitle"]) {
          echo $rec["parameters"]["preview"]["shorttitle"];
        } else {
          echo "...";
        }
        T_Next();
        echo $rec["parameters"]["preview"]["longtitle"];
        T_Next();
        echo IMS_Object2Site (IMS_SuperGroupName(), $key);
        T_Next();
        $max = 0;
        if (is_array($rec["history"])) {
          foreach ($rec["history"] as $guid => $spec) {
            if ($spec["when"] > $max) {
              $max = $spec["when"];
            }
          }
        }
        echo N_VisualDate ($max, false, true);
        T_NewRow();
      } 
      $content .= TS_End();
    }
  }

  return $content;
}
?>