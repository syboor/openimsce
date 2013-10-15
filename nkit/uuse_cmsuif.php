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


/********************** function CMSUIF_history **********************
****  changed ot original openims.php code are:
****  added global $myconfig because the code was global in openims.php
****  added $goto as a parameters, where to redirect after restore of older version
****  added uuse("diff"); because this code needs diff, openims.php always has diff
****  muliple replacements of IMS_supergroupname() to $supergroupname
****  added $object = MB_Ref( "ims_" . $supergroupname . "_objects", $object_id ); because code needs $object
****  commented startblock is still in openims.php
****  commented echo "<br>"; ( the br is still in openims.php )
****  TE_End() has become TS_End and output is now returned by the function
****  remove obsolete echo '</form>'; form is only used in dms history
*/


function cmsuif_coolbar_custom_buttons( $defined_vars )
{
  $buttons = Array();

  // AVAILABLE VARs & OBJECTS: $siteinfo, $sitecollection_id, $supergroupname, $site_id, $object_id, $object, $workflow, $goto
  $list = FLEX_LocalComponents (IMS_SuperGroupName(), "coolbarblock");

//  print_r( $list );

  if($list)
  {
    foreach($list as $cbbkey=>$specs)
    {
            $result = false;
                  $result = N_Eval ($specs["code_condition"], $defined_vars, "result");

            if ( $result )
      {
                    $url = N_Eval ($specs["code_urlgenerator"], $defined_vars, "url");
        if($specs["icon"] == '') $specs["icon"] = '/ufc/rapid/openims/projects.gif';

//        $coolbar .= '</center></td><td><center><a href="'.$url.'" class="ims_navigation"><img class="ims_image" border="0" size="2" src="'.$specs["icon"].'"><br />'.$specs["title"].'</a>';

        $buttons[ $cbbkey ] = Array( "url" => $url , "icon" => $specs["icon"], 'title' => $specs["title"] );
      }
    }
  }
  return $buttons;
}

function CMSUIF_history( $supergroupname , $object_id , $goto )
{
  uuse("diff");
  global $myconfig;
  $object = MB_Ref( "ims_" . $supergroupname . "_objects", $object_id );

      $file = "page.html";
      if ($object["editor"]=="Form") $exe = "winword.exe";
      if ($object["editor"]=="Microsoft Word") $exe = "winword.exe";
      if ($object["editor"]=="Microsoft Excel") $exe = "excel.exe";
      if ($object["editor"]=="Microsoft Powerpoint") $exe = "powerpnt.exe";
      if ($object["editor"]=="PHP Code") {
        $exe = "notepad.exe";
        $file = "page.php";
      }
//      startblock (ML("Historie van webpagina","History of web page")." \"".$object["parameters"]["preview"]["longtitle"]."\"", "docnav"); //JG stayed in openims.php
      if ($myconfig[$supergroupname]["ml"]["sitelanguages"]) {
        $site_id = IMS_DetermineJustTheSite();
        if ($myconfig[$supergroupname]["ml"]["sitelanguages"][$site_id]) {
          $sitelanguages = $myconfig[$supergroupname]["ml"]["sitelanguages"][$site_id];
          $firstsitelang = array_shift($sitelanguages);
        }
      }
//      echo "<br>";// JG starting line break stayed in openims.php
      T_Start ("ims", array("noheader"=>"yes"));
      reset($object["history"]);
      while (list($key, $data)=each($object["history"])) {
        if ($data["type"] == "" && $data["option"] == "delete") $data["type"] = "option";
        if ($data["type"]=="signal") {
          echo "<b>".ML("Automatisch signaal","Automatic signal")."</b> ";
          echo "(".N_VisualDate ($data["when"], true).")";
          T_NewRow();
        } else if ($data["type"]=="" || $data["type"]=="edit" || $data["type"]=="new" || $data["type"]=="newpage") {
          // LF20100310: "edit" can have an extra field "restore" and "fromversion" to indicate it was a restore.
          //   In case of a restore, the diff between the current version and the chronologically "previous" version will no longer be shown.
          //   The current version will still count as the starting point for the next diff.
          // A revoke (go back to last published version) has two history entries: first one of type "revoked", second one of type "edit"
          //   LF20100310: for diff'ing purposes, handle a "revoke" as a "restore"
          // LF20100310: "edit" can have an extra field "language" to indicate that the content for a specific language was edited
          //   If the language field is not present:
          //   1. the site doesn't support multiple languages
          //   2. the content for the "first" site language was changed
          //   3. it was a restore, so the content for ALL languages was changed.

          if ($data["type"]=="new" || $data["type"]=="newpage") {
            echo "<b>".ML("Aangemaakt","Created")." ";
          } else {
            echo "<b>".ML("Aangepast","Changed")." ";
            if (!$data["restore"] && ($prevdata["type"] != "revoked") && ($firstsitelang || $data["language"])) echo " (" . strtoupper($data["language"] ? $data["language"] : $firstsitelang) . ") ";
          }
          echo ML("door","by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b> ";
          echo "(".N_VisualDate ($data["when"], true).")";
          T_Next();

          if ($firstsitelang) {
            $image = "edit-".$firstsitelang.".gif";
            if (!N_FileExists("html::openims/$image")) $image = "edit_small.gif";
            $viewurl = IMS_GenerateViewURL ("\\".$supergroupname."\\objects\\history\\".$object_id."\\".$key."\\", $file, $exe, true);
            echo "<a title=\"".ML("Bekijk deze %1 versie","View this %1 version", strtoupper($firstsitelang))."\" href=\"$viewurl\"><img border=0 height=16 width=16 src=\"$image\"></a>&nbsp;";
            foreach ($sitelanguages as $otherlang) {
              $image = "edit-".$otherlang.".gif";
              if (!N_FileExists("html::openims/$image")) $image = "edit_small.gif";
              $viewurl = IMS_GenerateViewURL ("\\".$supergroupname."\\objects\\history\\".$object_id."\\".$key."\\", $otherlang . "-". $file, $exe, true);
              echo "<a title=\"".ML("Bekijk deze %1 versie","View this %1 version", strtoupper($otherlang))."\" href=\"$viewurl\"><img border=0 height=16 width=16 src=\"$image\"></a>&nbsp;";
            }
            T_Next();
            $version_id = $key;
            $restoreurl = "/openims/action.php?command=restore&sitecollection_id=$supergroupname&object_id=$object_id&version_id=$version_id&goto=".urlencode($goto);
            if (SHIELD_HasObjectRight ($supergroupname, $object_id, "edit")) {
              $title = ML("Herstel deze versies. Voor ALLE talen wordt de preview versie overschreven.","Restore these versions. For ALL LANGUAGES the preview version will be overwritten.");
              echo "<a title=\"$title\" href=\"$restoreurl\"><img border=0 src=\"history_small.gif\"></a>&nbsp";
            }
          } else {
            $image = "edit_small.gif";
            $viewurl = IMS_GenerateViewURL ("\\".$supergroupname."\\objects\\history\\".$object_id."\\".$key."\\", $file, $exe, true);
            echo "<a title=\"".ML("Bekijk deze versie","View this version")."\" href=\"$viewurl\"><img border=0 src=\"$image\"></a>&nbsp;";
            $version_id = $key;
            $restoreurl = "/openims/action.php?command=restore&sitecollection_id=$supergroupname&object_id=$object_id&version_id=$version_id&goto=".urlencode($goto);
            if (SHIELD_HasObjectRight ($supergroupname, $object_id, "edit")) {
              echo "<a title=\"".ML("Herstel deze versie (plaats in preview)","Restore this version (put in preview)")."\" href=\"$restoreurl\"><img border=0 src=\"history_small.gif\"></a>&nbsp";
            }
          }

          T_NewRow();

          if ($object["editor"]=="PHP Code") {
            $thedoc = getenv("DOCUMENT_ROOT")."/".$supergroupname."/objects/history/".$object_id."/".$key."/page.php";
          } else {
            if ($data["language"] && $data["language"] != $firstsitelang) {
              $thedoc = getenv("DOCUMENT_ROOT")."/".$supergroupname."/objects/history/".$object_id."/".$key."/".$data["language"]."-page.html";
            } else {
              $thedoc = getenv("DOCUMENT_ROOT")."/".$supergroupname."/objects/history/".$object_id."/".$key."/page.html";
            }
          }
          if (($prevdoc || $prevmldoc) && $object["editor"]=="Microsoft Word" && !$data["restore"] && $prevdata["type"] != "revoked") {
            echo "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td><font size=2>";
            if ($data["language"] && $data["language"] != $firstsitelang) {
              if ($prevmldoc[$data["language"]]) {
                $docold = SEARCH_HTML2TEXT (N_ReadFile ($prevmldoc[$data["language"]]));
              } else {
                $docold = SEARCH_HTML2TEXT (N_ReadFile ($prevdoc));
              }
            } else {
              $docold = SEARCH_HTML2TEXT (N_ReadFile ($prevdoc));
            }
            $docnew = SEARCH_HTML2TEXT (N_ReadFile ($thedoc));
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
            echo "</td></tr></table>";
            T_NewRow();

          }
          if ($prevdoc && $object["editor"]=="PHP Code") {
            echo "<table><tr><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td><font size=2>";
            $docold = N_ReadFile ($prevdoc);
            $docnew = N_ReadFile ($thedoc);
            $diff = DIFF_SourceCode ($docold, $docnew);
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
            echo "</td></tr></table>";
            T_NewRow();
          }
          if ($data["language"] && $data["language"] != $firstsitelang) {
            $prevmldoc[$data["language"]] = $thedoc;
          } else {
            $prevdoc = $thedoc;
            // In case of a restore, all versions (for all languages) were changed.
            if (($data["restore"] || $prevdata["type"] == "revoked") && $prevmldoc) foreach ($prevmldoc as $otherlang => $dummy) {
              $prevmldoc[$otherlang] = getenv("DOCUMENT_ROOT")."/".$supergroupname."/objects/history/".$object_id."/".$key."/".$otherlang."-page.html";
            }
          }
          if ($data["restore"]) {
            echo ML("De versie van %1 werd hersteld.", "The version of %1 was restored.", N_VisualDate($object["history"][$data["fromversion"]]["when"], true));
            T_NewRow();
          }

        } else if ($data["type"]=="option") {
          echo "<b>".ML("Keuze","Choice")." \"".$data["option"]."\" ";
          echo ML("door","by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b>";
          if ($data["allocto"]) {

             echo "<b>, ".ML("toegewezen aan","allocated to")." ".SHIELD_UserName ($supergroupname, $data["allocto"])."</b> ";
          }
          echo "(".N_VisualDate ($data["when"], true).")";
          T_NewRow();
          if ($data["comment"]) {
            echo "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td><font size=\"2\">";
            echo str_replace(chr(13).chr(10),"<br>",$data["comment"]);
            echo "</td></tr></table>";
            T_NewRow();
          }
        } else if ($data["type"]=="properties") {
          echo "<b>".ML("Eigenschappen aangepast door","Properties changed by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b> ";
          echo "(".N_VisualDate ($data["when"], true).")";
          T_Next();
          T_NewRow();

        } else if ($data["type"]=="revoked") {
          if ($data["revoketype"]=="restorelastpublished") {
            echo "<b>".ML("Teruggetrokken (terug naar laatst gepubliceerde versie) door","Revoked (return to published version) by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b> ";
          } elseif ($data["revoketype"]=="unpublish") {
            echo "<b>".ML("Teruggetrokken (verwijder de gepubliceerde versie) door","Revoked (remove published page) by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b> ";
          } else {
            echo "<b>".ML("Teruggetrokken door","Revoked by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b> ";
          }
          echo "(".N_VisualDate ($data["when"], true).")";
          T_Next();
          T_NewRow();

        } else if ($data["type"]=="webgen") {
          echo "<b>".ML("Gegenereerd met de webgenerator door","Generated with the webgenerator by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b> ";
          echo "(".N_VisualDate ($data["when"], true).")";
          T_Next();
          T_NewRow();
        }
        $prevdata = $data;
      }

      $content = TS_End();
//      echo '</form>';// form was needed by dms part of history, not cms part, so removed (JG)
      return $content;
}

/*********** CHANGES BY JG FROM uuse_ims coolbar ***********
** changed $siteinfo["sitecollection"] to $supergroupname
** changed $siteinfo["object"] to $parent_id
** changed $siteinfo["site"] to $site
** added global $myconfig; for config options
** added $object = MB_Ref( "ims_" . $supergroupname . "_objects", $parent_id ); to initialize object
**********************/
function CMSUIF_newpage_formspec( $supergroupname , $site, $parent_id )
{
      global $myconfig;
      $object = MB_Ref( "ims_" . $supergroupname . "_objects", $parent_id );

      $metaspec["fields"]["template"]["type"] = "list";
      $list = MB_Query ("ims_".$supergroupname."_templates");
      if (is_array($list)) reset($list);
      $groups = MB_Query ("shield_".$supergroupname."_groups");

      $hasaccess_list = array();  // 20120828 NDV make list of rights to prevent calling SHIELD_ValidateAccess_Group() too often
      foreach ($groups as $group_id) {
        if (SHIELD_ValidateAccess_Group( $supergroupname, SHIELD_CurrentUser(), $group_id)) {
          $hasaccess_list[$group_id] = true;
        }
      }

      if (is_array($list)) while (list($key)=each($list)) {
        $template = &MB_Ref ("ims_".$supergroupname."_templates", $key);
        $hasaccess = false;
        foreach ($groups as $group_id) {
          if (!$template["noaccess"][$group_id] && $hasaccess_list[$group_id]) {
            $hasaccess = true;
          }
        }
        if ($hasaccess) {
          $metaspec["fields"]["template"]["values"][$template["name"]] = $template["name"];
//          if (!$metaspec["fields"]["template"]["default"])
//            $metaspec["fields"]["template"]["default"] = $template["name"];
        } else {
          unset ($metaspec["fields"]["template"]["values"][$template["name"]]);
        }
      }

      ksort($metaspec["fields"]["template"]["values"]);
      reset($metaspec["fields"]["template"]["values"]);
      $boventemplate = each($metaspec["fields"]["template"]["values"]);
      $metaspec["fields"]["template"]["default"] = $boventemplate["key"];
      reset($metaspec["fields"]["template"]["values"]);

      global $myconfig;
      if ($myconfig[$supergroupname]["cms"]["newpagedefaulttemplate"]) {
         $metaspec["fields"]["template"]["default"] = $myconfig[$supergroupname]["cms"]["newpagedefaulttemplate"];


         // ddddd
         if ($myconfig[$supergroupname]["cms"]["newpagedefaulttemplate"] == "parent") {
            $mytemplate = MB_Ref("ims_".$supergroupname."_templates", $object["parameters"]["preview"]["template"]);
            $metaspec["fields"]["template"]["default"] = $mytemplate["name"];

         }

      }

      $metaspec["fields"]["editor"]["type"] = "list";
      $editorkey = ML("Tekstverwerker","Content Editor") ;
      $metaspec["fields"]["editor"]["values"]["$editorkey"] = "Microsoft Word";


      $formeditorkey = ML("Standaard Formulier","Standard Form") ;
      $metaspec["fields"]["editor"]["values"]["$formeditorkey"] = "Form";


      $metaspec["fields"]["workflow"]["type"] = "list";

      global $myconfig;
      if($myconfig[$supergroupname]["cms"]["newpagedefaultworkflow"]) {
        $metaspec["fields"]["workflow"]["default"] = $myconfig[$supergroupname]["cms"]["newpagedefaultworkflow"];

         // ddddd
         if ($myconfig[$supergroupname]["cms"]["newpagedefaultworkflow"] == "parent") {
            $metaspec["fields"]["workflow"]["default"] = $object["workflow"];
         }

      } else {
        $metaspec["fields"]["workflow"]["default"] = "edit-publish";
      }

      $wlist = MB_Query ("shield_".$supergroupname."_workflows", '$record["cms"]');
      $allowed = SHIELD_AllowedWorkflows ($supergroupname);
      if (is_array($wlist)) reset($wlist);
      if (is_array($wlist)) while (list($wkey)=each($wlist)) {
        if ($allowed[$wkey]) {
          $workflow = &MB_Ref ("shield_".$supergroupname."_workflows", $wkey);
          $metaspec["fields"]["workflow"]["values"][$workflow["name"]] = $wkey;
        }
      }

      $metaspec["fields"]["longtitle"]["type"] = "strml6";
      $metaspec["fields"]["shorttitle"]["type"] = "smallstrml6";

      // 20110321 KvD go to newly created page
      $metaspec["fields"]["gotopage"]["type"] = "yesno";
      $metaspec["fields"]["gotopage"]["default"] = 1;
      if($myconfig[$supergroupname]["cms"]["gotonewpage"] == "no") { // niet naar nieuwe pagina gaan
        $metaspec["fields"]["gotopage"]["default"] = 0;
      }
      $gotopagecode = '
        if ($data["gotopage"]) {
          $url = "/" . $input["site"] . "/$objid.php?activate_preview=yes";
          $gotook = "closeme&parentgoto:$url";
        }
' ;
      ///

      if($myconfig[$supergroupname]["cms"]["customidfornewpage"] == "yes") { // custom id
        $metaspec["fields"]["id"]["type"] = "string";
        $metaspec["fields"]["id"]["required"] = true;
      }

      $form = array();
      $form["title"] = ML("Nieuwe webpagina", "New webpage");
      $form["input"]["parent"] = $parent_id;
      $form["input"]["sitecollection"] = $supergroupname;
      $form["input"]["site"] = $site;
      $form["metaspec"] = $metaspec;
      $form["formtemplate"] = '
        <table>
          <tr><td><font face="arial" size=2><b>'.ML("Template","Template").':</b></font></td><td>[[[template]]]</td></tr>
          <tr><td><font face="arial" size=2><b>'.ML("Workflow","Workflow").':</b></font></td><td>[[[workflow]]]</td></tr>
          <tr><td><font face="arial" size=2><b>'.ML("Editor","Editor").':</b></font></td><td>[[[editor]]]</td></tr>
          <tr><td><font face="arial" size=2><b>'.ML("Lange titel","Long title").':</b></font></td><td>[[[longtitle]]]</td></tr>
          <tr><td><font face="arial" size=2><b>'.ML("Korte titel (menu)","Short title (menu)").':</b></font></td><td valign=top>[[[shorttitle]]]</td></tr>
          <tr><td><font face="arial" size=2><b>'.ML("Direct openen","Open directly").':</b></font></td><td>[[[gotopage]]]</td></tr>
      ';
      if($myconfig[$supergroupname]["cms"]["customidfornewpage"] == "yes") { // custom id

         if (!function_exists ("IMS_CustomIdForNewPage_Default")) {
        $internal_component = FLEX_LoadImportableComponent ("support", "71a878b304e00c4d0a6d00c2a0ef364b");
        $internal_code = $internal_component["code"];
        eval ($internal_code);
         }

        $form['metaspec']["fields"]["id"]["default"] = IMS_CustomIdForNewPage_Default($supergroupname, $parent_id); // output functie
        $form["formtemplate"] .= '
          <tr><td><font face="arial" size=2><b>'.ML("Paginanaam voor url*","Page name for url*").':</b></font></td><td valign=top>[[[id]]]</td></tr>
        ';

      }

      $form["formtemplate"] .= '
          <tr><td colspan=2>&nbsp</td></tr>
          <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
        </table>
      ';
      if($myconfig[$supergroupname]["cms"]["customidfornewpage"] == "yes") { // custom id
        $form["postcode"] = '
          $customID = "'.$form['metaspec']["fields"]["id"]["default"].'";
          $sgn = $input["sitecollection"];
          uuse ("ims");
          $newid = preg_replace("\'[^a-z0-9\\-]\'i", "_", strtolower(SEARCH_REMOVEACCENTS($data["id"])));

          if (strlen($newid) < 4) {
            FORMS_ShowError (ML("Foutmelding","Error"), ML("De naam (sleutel) moet minimaal 4 tekens zijn", "The name (key) should be at least 4 characters") . " :" . $newid, true);
          }

          if ($newid == "history") {
            FORMS_ShowError (ML("Foutmelding","Error"), ML("Deze naam (sleutel) is gereserveerd voor intern gebruik", "This name (key) is reserved for internal use") . " :" . $newid, true);
          }

          if($newid == $customID){ // the customid was not altered by the user
      FORMS_ShowError (ML("Foutmelding","Error"), ML("U dient een paginanaam achter het (reeds ingevulde) pad op te geven", "You must extend the (given) path with a pagename") . " : " . $newid, true);
    }

          $testobject = MB_Ref("ims_" . $sgn . "_objects", $newid);
          if($testobject) {
            FORMS_ShowError (ML("Foutmelding","Error"), ML("Pagina met deze naam (sleutel) bestaat al", "Page with this name (key) already exists") . " :" . $newid, true);
          }

          $objid = IMS_NewPage ($input, $data, $newid);
          $obj = &MB_Ref("ims_".$sgn."_objects",$objid);
          $obj["allocto"] = SHIELD_CurrentUser($sgn);
' . $gotopagecode;
      } else {
        $form["postcode"] = '
          uuse ("ims");
          $objid = IMS_NewPage ($input, $data);
          $obj = &MB_Ref("ims_".$input["sitecollection"]."_objects",$objid);
          $obj["allocto"] = SHIELD_CurrentUser($input["sitecollection"]);
' . $gotopagecode;
      }
   return $form;
}


function cmsuif_workflow_option_formspec( $supergroupname , $option , $object_id )
{
        $object = MB_Ref( "ims_" . $supergroupname . "_objects", $object_id );// ADDED JG!
        $workflow = &SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);
        $form = array();
        $form["input"]["col"] = $supergroupname;
        $form["input"]["id"] = $object_id;
        $form["input"]["user"] = SHIELD_CurrentUser ($supergroupname);
        $form["input"]["opt"] = $option;
        $form["input"]["user_id"] = SHIELD_CurrentUser($supergroupname);
        $form["input"]["assign"] = $workflow["stages"]!=$workflow[$object["stage"]]["#".$option];
        $form["title"] = $option;
        $form["metaspec"]["fields"]["comment"]["type"] = "text";
        $form["metaspec"]["fields"]["signal"]["type"] = "yesno";
        $form["metaspec"]["fields"]["alloc"]["type"] = "list";
     

         /* if a field with the name specifyusersforspecialoption exists
         * the developer is able to override the assignable users for an wf-option */
        $replacedallocfield = MB_Fetch("ims_fields", $supergroupname, "specifyusersforspecialoption");
        if ($replacedallocfield) {
          $form["metaspec"]["fields"]["alloc"] = $replacedallocfield;
        }

        if ($workflow["alloc"] && $workflow["stages"]!=$workflow[$object["stage"]]["#".$option]) {
          $form["formtemplate"] = '
            <table>
              <tr><td><font face="arial" size=2><b>'.ML("Toewijzen aan","Assign to").':</b></font></td><td>[[[alloc]]]</td></tr>
              <tr><td><font face="arial" size=2><b>'.ML("Signaal sturen","Send signal").':</b></font></td><td>[[[signal]]]</td></tr>
              <tr><td colspan=2><font face="arial" size=2><b>'.ML("Opmerkingen","Remarks").':</b></font></td></tr>
              <tr><td colspan=2>[[[comment]]]</td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          ';
        } else {
          $form["formtemplate"] = '
            <table>
              <tr><td colspan=2><font face="arial" size=2><b>'.ML("Opmerkingen","Remarks").':</b></font></td></tr>
              <tr><td colspan=2>[[[comment]]]</td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
            </table>
          ';
        }
        $form["precode"] = '
          global $myconfig;

        if($input["assign"]) {
        $users = SHIELD_AssignableUsers ($input["col"], $input["id"], $input["opt"]);
        foreach ($users as $user_id => $name) {
          $metaspec["fields"]["alloc"]["values"][$name] = $user_id;
          }
        }

          if ($myconfig[IMS_SuperGroupName()]["signaldefaulttrue"] == "yes") {
            $data["signal"] = true;
          }
          $data["alloc"] = $input["user"];

        ';
        $form["postcode"] = '
          $history["user_id"] = $input["user_id"];
          $history["timestamp"] = time();
          $history["comment"] = $data["comment"];
          SHIELD_ProcessOption ($input["col"], $input["id"], $input["opt"], $history, $data["alloc"], $data["signal"]);
        ';
        if ($dataerror) {
          $form = array();
          $form["formtemplate"] = '
            <table width=100>
              <tr><td colspan=2><font face="arial" color="#ff0000" size=2><b>'.$title.'</b></font></td></tr>
              <tr><td colspan=2>&nbsp;</td></tr>
              <tr><td colspan=2><nobr><font face="arial" size=2><b><font color=#ff0000 size=4>'.ML("Er zit een fout in de eigenschappen.","There is an error in the properties.").'</font></b></font></nobr></td></tr>
              <tr><td colspan=2>&nbsp;</td></tr>
              <tr><td colspan=2><nobr><font face="arial" size=2><b>'.$dataerror.'</b></font></nobr></td></tr>
              <tr><td colspan=2>&nbsp</td></tr>
              <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
            </table>
          ';
        }
    return $form;
}

function CMSUIF_properties_formspec( $object_id )
{
      global $myconfig;

      $siteinfo = IMS_SiteInfo( "" , "" , $object_id );
      $supergroupname = $siteinfo["sitecollection"];
      $siteinfo["object"] = $object_id;

    if (SHIELD_HasObjectRight ($supergroupname, $object_id, "edit")) {

      $therec = &MB_Ref ("ims_".$siteinfo["sitecollection"]."_objects", $siteinfo["object"]);
      $therec["parameters"]["preview"]["workflow"] = $therec["workflow"];
      $table = "ims_".$siteinfo["sitecollection"]."_objects";
      $key = $siteinfo["object"];
      global $theobject;
      $GLOBALS ["theobject"] = MB_Ref ($table, $key );

      $object = &MB_Ref ("ims_".$siteinfo["sitecollection"]."_objects", $siteinfo["object"]);
      if (!$object["parameters"]["preview"]["from"]) $object["parameters"]["preview"]["from"] = N_BuildDateTime (2000, 1, 1);
      if (!$object["parameters"]["preview"]["until"]) $object["parameters"]["preview"]["until"] = N_BuildDateTime (2025, 12, 31, 23, 59, 59);
      $object["parameters"]["preview"]["template"] = $object["template"];
      $metaspec = array();
      $metaspec["fields"]["longtitle"]["type"] = "strml6";
      $metaspec["fields"]["shorttitle"]["type"] = "smallstrml6";
      $metaspec["fields"]["from"]["type"] = "datetime";
      $metaspec["fields"]["until"]["type"] = "datetime";
      $metaspec["fields"]["keywords"]["type"] = "text";

      $metaspec["fields"]["template"]["type"] = "list";

      $tlist = MB_Query ("ims_".$siteinfo["sitecollection"]."_templates");
      if (is_array($tlist)) reset($tlist);
      $tgroups = MB_Query ("shield_".$supergroupname."_groups");

      $hasaccess_list = array();  // 20120828 NDV Prevent too many calls to SHIELD_ValidateAccess_Group()
      foreach ($tgroups as $group_id) {
        if (SHIELD_ValidateAccess_Group( $supergroupname, SHIELD_CurrentUser(), $group_id)) {
          $hasaccess_list[$group_id] = true;
        }
      }

      if (is_array($tlist)) while (list($tkey)=each($tlist)) {
        $ttemplate = &MB_Ref ("ims_".$siteinfo["sitecollection"]."_templates", $tkey);
        $hasaccess = false;
         // 20120828 NDV Prevent too many calls to SHIELD_ValidateAccess_Group()
        foreach ($tgroups as $tgroup_id) {
          if (!$ttemplate["noaccess"][$tgroup_id] && $hasaccess_list[$tgroup_id]) {
            $hasaccess = true;
          }
        }
        if ($tkey == $object["template"]) $hasaccess = true;
        if ($hasaccess) {
          $metaspec["fields"]["template"]["values"][$ttemplate["name"]] = $tkey;
//          if (!$metaspec["fields"]["template"]["default"])
//            $metaspec["fields"]["template"]["default"] = $ttemplate["name"];
        } else {
        }
      }

      ksort($metaspec["fields"]["template"]["values"]);
      if (!$metaspec["fields"]["template"]["default"]) {
        reset($metaspec["fields"]["template"]["values"]);
        $boventemplate = each($metaspec["fields"]["template"]["values"]);
        $metaspec["fields"]["template"]["default"] = $boventemplate["key"];
        reset($metaspec["fields"]["template"]["values"]);
      }

      $metaspec["fields"]["workflow"]["type"] = "list";
      $wlist = MB_Query ("shield_".$siteinfo["sitecollection"]."_workflows", '$record["cms"]');
      $allowed = SHIELD_AllowedWorkflows ($siteinfo["sitecollection"], $siteinfo["object"]);
      if (is_array($wlist)) reset($wlist);
      if (is_array($wlist)) while (list($wkey)=each($wlist)) {
        if ($allowed[$wkey]) {
          $workflow = &MB_Ref ("shield_".$siteinfo["sitecollection"]."_workflows", $wkey);
          $metaspec["fields"]["workflow"]["values"][$workflow["name"]] = $wkey;
        }
      }
      $workflow = &MB_Ref ("shield_".$supergroupname."_workflows", $object["workflow"]);
      $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
      for ($i=1; $i<1000; $i++) {
        if ($workflow["meta"][$i]) {
          $metaspec["fields"]["meta_".$workflow["meta"][$i]] = $allfields[$workflow["meta"][$i]];

        }
      }
      $template = &MB_Ref ("ims_".$supergroupname."_templates", $object["template"]);
      for ($i=1; $i<1000; $i++) {
        if ($template ["meta"][$i]) {
          $metaspec["fields"]["meta_".$template ["meta"][$i]] = $allfields[$template ["meta"][$i]];
        }
      }
      $metaspec["fields"]["module"]["type"] = "list";
      $metaspec["fields"]["module"]["default"] = "";
      $metaspec["fields"]["module"]["values"][ML("Geen","None")] = "";
      $modules = OFLEX_Modules ("cmsmodule");
      foreach ($modules as $moduleid => $modulename) {
        $metaspec["fields"]["module"]["values"][$modulename] = $moduleid;
      }

      $metaspec["fields"]["htsetting"]["type"] = "list";
      $metaspec["fields"]["htsetting"]["default"] = "";
      $metaspec["fields"]["htsetting"]["values"][ML("Automatisch (standaard pagina)","Automatic (standard page)")] = "";
      $metaspec["fields"]["htsetting"]["values"][ML("Niet (dynamische pagina)","None (dynamic page)")] = "never";
      $metaspec["fields"]["htsetting"]["values"][ML("Dynamisch (dynamische pagina)","Dynamic (dynamic page)")] = "dynamic";
      $metaspec["fields"]["htsetting"]["values"][ML("Altijd verversen (afhankelijke pagina)","Always refresh (dependent page)")] = "always";

      $formtemplate =  '<body bgcolor=#f0f0f0><br><center><table>';
      if ($myconfig["serverhasniceurl"] == "yes") {
        $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("ID","ID").':</b></font></td><td><font face="arial" size=2>'.$object_id.'</font></td></tr>';
      }
      $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Template","Template").':</b></font></td><td>[[[template]]]</td></tr>';
      $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Workflow","Workflow").':</b></font></td><td>[[[workflow]]]</td></tr>';
      $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Editor","Editor").':</b></font></td><td><font face="arial" size=2>'.$siteinfo["allobjectdata"]["editor"].'</font></td></tr>';
      $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Lange titel","Long title").':</b></font></td><td>[[[longtitle]]]</td></tr>';
      $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Korte titel (menu)","Short title (menu)").':</b></font></td><td valign=top>[[[shorttitle]]]</td></tr>';
      if ($workflow["scedule"]=="true") {
        $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Zichtbaar van","Visible from").':</b></font></td><td valign=top>[[[from]]]</td></tr>';
        $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Zichtbaar tot","Visible until").':</b></font></td><td valign=top>[[[until]]]</td></tr>';
      }
      $si = IMS_SiteInfo();
      uuse ("ht");
      if (HT_UseCaching ($si["sitecollection"], $si["site"])) {
        $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Versnelling","Acceleration").':</b></font></td><td valign=top>[[[htsetting]]]</td></tr>';
      }
      $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Zoektermen","Keywords").':</b></font></td><td>[[[keywords]]]</td></tr>';
      for ($i=1; $i<1000; $i++) {
        if ($workflow["meta"][$i]) {
          $formtemplate .= '<tr><td><font face="arial" size=2><b>'.$allfields[$workflow["meta"][$i]]["title"].':</b></font></td><td>';
          if ($workflow["readonly"][$i]) {
            $formtemplate .= '<font face="arial" size=2>(((meta_'.$workflow["meta"][$i].')))</font>';
          } else {
            $formtemplate .= '[[[meta_'.$workflow["meta"][$i].']]]';
          }
          $formtemplate .= '</td></tr>';
        }
      }
      for ($i=1; $i<1000; $i++) {

        if ($template["meta"][$i]) {
          $formtemplate .= '<tr><td><font face="arial" size=2><b>'.$allfields[$template["meta"][$i]]["title"].':</b></font></td><td>[[[meta_'.$template["meta"][$i].']]]</td></tr>';
        }
      }
      $formtemplate .= '<tr><td colspan=2>&nbsp</td></tr>';
      $formtemplate .= '<tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>';
      $formtemplate .= '</table></center></body>';
      $input["col"] = $supergroupname;
      $input["id"] = $object_id;
      $coolbar .= '</center></td><td class="ims_td"><center>';
      $content = '<img class="ims_image" border="0" src="/ufc/rapid/openims/properties.gif"><br>'.ML("Eigenschappen","Properties");
      $code = '
        $rec["template"] = $rec["parameters"]["preview"]["template"];
        $rec["workflow"] = $rec["parameters"]["preview"]["workflow"];
        $rec["htsetting"] = $rec["parameters"]["preview"]["htsetting"];
        SHIELD_ProcessEdit ($input["col"], $input["id"]);
        global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
        $object = &MB_Ref ("ims_".$input["col"]."_objects", $input["id"]);
        $time = time();
        $guid = N_GUID();
        $object["history"][$guid]["type"] = "properties";
        $object["history"][$guid]["newproperties"] = $object ["parameters"]["preview"];
        $object["history"][$guid]["when"] = $time;
        $object["history"][$guid]["author"] = SHIELD_CurrentUser($input["col"]);
        $object["history"][$guid]["server"] = N_CurrentServer ();
        $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
        $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
        $object["history"][$guid]["server_addr"] = $SERVER_ADDR;
      ';

  $form["input"] = $input;
  $form["title"]= ML("Eigenschappen","Properties");
        $form["metaspec"]=$metaspec;
  $form["formtemplate"]=$formtemplate;
        $form["autoobject"]= MB_Load ($table, $key);
  $form["input"] = $input;
  $form["input"]["table"] = $table;
  $form["input"]["key"] = $key;
  $form["input"]["path"] = '["parameters"]["preview"]';

  $form["precode"] = '$rec = MB_Load ($input["table"], $input["key"]); eval (\'$data = $rec\'.$input["path"].";");';
  $thecode = $code;
  $form["postcode"] = '
    uuse("forms");
    $rec = MB_Load ($input["table"], $input["key"]);
    MB_Save ($input["table"], $input["key"], FORMS_Integrate ($rec, $input["path"], $data));
    $rec = &MB_Ref ($input["table"], $input["key"]);
    '.$thecode.'
  ';

  return $form;
    }
}

?>