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

/*
   DVG / 06-05-2013
   importeerbaar component document leeshistorie op werkstroom niveau beschikbaarheid instelbaar gemaakt 
   en net onder historie beschikbaar gemaakt indien lid van juiste groepen (if (SHIELD_HasObjectRight ($supergroupname, $key, "docviewhistory")))
*/
function DMSUIF_docviewhistory($currentobject,$type="assistent", $options = array()) {
  if ($type=="assistent") {
    $form = array();
    $form["title"] = "Lees historie"; 
    $form["input"]["sgn"] = IMS_SuperGroupName(); 
    $form["input"]["docid"] = $currentobject; 
    $form["formtemplate"] = ' 
       [[[ok]]]
    '; 
    $form["precode"] = '  
       $list = MB_TurboMultiQuery ("ims_".$input["sgn"]."_objects_log", array (
                   "select" => array (
                   \'$record["objectid"]\' => $input["docid"]
       )));  
       foreach ($list as $key){
//n_log("test",$key);
          $obj = MB_Load ("ims_".$input["sgn"]."_objects_log", $key);
          $list2[$obj["time"]] = $obj["time"];
          asort ($list2);
       }
       T_Start ("ims");

       echo "Datum";
       T_Next();
       echo "Wie";
       T_Next();
       echo "Hoe";
       T_NewRow();

       $lasttime = 0;
       $lastuser = "";

       foreach ($list2 as $time) {
          foreach ($list as $key) {
             $obj = MB_Load ("ims_".$input["sgn"]."_objects_log", $key);
             if ($obj["time"]==$time && $obj["action"]=="read") {        
                $user = &MB_Ref ("shield_".$input["sgn"]."_users", $obj["user"]);
                echo N_VisualDate ($time, 1, 1)."<br>";
                T_Next();
                if ($user["name"]) echo $user["name"]; else echo "Onbekend";
                echo "<br>";
                T_Next();
                if ($obj["details"]["readtype"]=="hyperlink") {
                   echo "Hyperlink";
                }
                if ($obj["details"]["readtype"]=="transfer agent") {
                   echo "Transfer agent";
		}
                T_NewRow(); 
             }
          }
       }
       $formtemplate = TS_End()."<br>".$formtemplate;
    '; 
    return FORMS_URL ($form);
  }

  if ($type=="inpage") {
       if (!isset($options["compare"])) $options["compare"] = true;
       if (!isset($options["tablestyle"])) $options["tablestyle"] = "ims";
       if (!isset($options["nobuttonsatall"])) $options["nobuttonsatall"] = false;
       $list = MB_TurboMultiQuery ("ims_".IMS_SuperGroupName()."_objects_log", array (
                   "select" => array (
                   '$record["objectid"]' => $currentobject
       )));  
       foreach ($list as $key){
          $obj = MB_Load ("ims_".IMS_SuperGroupName()."_objects_log", $key);
          $list2[$obj["time"]] = $obj["time"];
          arsort ($list2);
       }
       T_Start ($options["tablestyle"]);
       echo "<b>Datum</b>";
       T_Next();
       echo "<b>Wie</b>";
       T_Next();
       echo "<b>Hoe</b>";
       T_NewRow();

       $lasttime = 0;
       $lastuser = "";

       foreach ($list2 as $time) {
          foreach ($list as $key) {
             $obj = MB_Load ("ims_".IMS_SuperGroupName()."_objects_log", $key);
             if ($obj["time"]==$time && $obj["action"]=="read") {        
                $user = &MB_Ref ("shield_".IMS_SuperGroupName()."_users", $obj["user"]);
                echo N_VisualDate ($time, 1, 1)."<br>";
                T_Next();
                if ($user["name"]) echo $user["name"]; else echo "Onbekend";
                echo "<br>";
                T_Next();
                if ($obj["details"]["readtype"]=="hyperlink") {
                   echo "Hyperlink";
                }
                if ($obj["details"]["readtype"]=="transfer agent") {
                   echo "Transfer agent";
		}
                T_NewRow(); 
             }
          }
       }
       $content = TS_End()."<br>";
     return $content;
  }
}

//ericd 30052013 RFC319 new save and load code for favorites (uses _favorites table, instead of _users)
function DMSUIF_SaveFavorite ($sgn, $key = "", $userID, $favorite, $data) {

  if(!$sgn)
    return false;

  $table = "ims_".$sgn."_favorites";

  if(!$key)
    $key = N_GUID();

  $object = MB_Load($table, $key);
  if(!is_array($object))
    $object = array();

  if(is_array($data))
    $object = array_merge($object, $data);
	
  $object["time"] = time();
  $object["user"] = $userID;
  $object["favorite"] = $favorite;

  MB_Save($table, $key, $object);
}

function DMSUIF_CheckAndSaveFormerUserFavorites ($sgn, $userID) {

  //save user favorites from _users in favorits table and deletes "myfavorites" from user in _users
  if(!$sgn)
    return false;

  $table = "shield_".$sgn."_users";
  $object = MB_Load($table, $userID);
  $formerFavs = array();
  if(is_array($object["myfavorites"])) {
    foreach($object["myfavorites"] as $favorite => $data) {
      $formerFavs[$favorite] = $data;
      $key = N_GUID();
      DMSUIF_SaveFavorite ($sgn, $key, $userID, $favorite, $data);
      MB_Flush();
    }
  }
  if($object["myfavorites"]) {
    //delete from users
    unset($object["myfavorites"]);
    MB_Save($table, $userID, $object);
    MB_Flush();
  }
}

function DMSUIF_GetUserFavorites ($sgn, $userID) {

  if(!$sgn)
    return false;

  $table = "ims_".$sgn."_favorites";
  $specs = array();
  $specs["select"] = array('$record["user"]' => $userID);
  $specs["value"] = '$record';
  $result = MB_TurboMultiQuery($table, $specs);
	
  return $result;
}

function DMSUIF_GetUserFavoritesFormerFormat ($sgn, $userID) {

  if(!$sgn)
    return false;

  $table = "ims_".$sgn."_favorites";
  $specs = array();
  $specs["select"] = array('$record["user"]' => $userID);
  $specs["value"] = '$record';
  $result = MB_TurboMultiQuery($table, $specs);
	
  $formerFavs = array();
	
  foreach ($result as $key => $data) {
    $formerFavs[$data["favorite"]] = $data;
  }
	
  return $formerFavs;
}

function DMSUIF_GetUserFavorite ($sgn, $userID, $favorite) {

  if(!$sgn || !$userID || !$favorite)
    return false;

  $table = "ims_".$sgn."_favorites";
  $specs = array();
  $specs["select"] = array('$record["user"]' => $userID,
                           '$record["favorite"]' => $favorite);
  $specs["value"] = '$record';
  $result = MB_TurboMultiQuery($table, $specs);
	
  if(count($result) == 1)
    return $result;
  else
    return false;
}

function DMSUIF_DeleteUserFavorite ($sgn, $userID, $favorite) {

  if(!$sgn)
    return false;

  $table = "ims_".$sgn."_favorites";
  $specs = array();
  $specs["select"] = array('$record["user"]' => $userID,
                           '$record["favorite"]' => $favorite);
  $specs["value"] = '$record';
  $result = MB_TurboMultiQuery($table, $specs);

  foreach ($result as $key => $dummy) {
    MB_Delete ($table, $key);
  }
}

/***
*** favorieten zijn sinds 3-2013 favoriete folders ipv favoriete dossiers
*** de sleutel is vanaf dit moment dus "(12345678901234567890123456789012)root" of "(12345678901234567890123456789012)12345678901234567890123456789012" 
*** ipv "(12345678901234567890123456789012)"
*** de onderstaande functie repareerd dit
*** in oude situatie werd de shorttitle niet gebruikt, echter kon wel worden gewijzigd. de longtitle werd getoond.
***/
function DMSUIF_repairFavorites()
{
  $user_id = SHIELD_CurrentUser (IMS_SuperGroupName());
  $user = &MB_Ref ("shield_" . IMS_SuperGroupName() . "_users", $user_id);

  //ericd 31052013 RFC319
  if(is_array($user["myfavorites"])) {

    $favloop = $user["myfavorites"];
    $favset = &$user["myfavorites"];
    foreach( $favloop AS $key => $favObject )
    {
      if ( strlen( $key ) == '34' )
      {
        $favset[ $key . 'root' ] = $favObject;
        unset( $favset[ $key ] );
      }
    }

  }
}

function DMSUIF_favoritesBlock( $casetype = false )
{
  global $myconfig;
  
  $user_id = SHIELD_CurrentUser (IMS_SuperGroupName());
  $user = MB_Ref ("shield_" . IMS_SuperGroupName() . "_users", $user_id);
  $tempsort = array();

  //ericd 300513 RFC319 read from new format, return former format
  $sgn = IMS_SuperGroupName();
  $favorites = DMSUIF_GetUserFavoritesFormerFormat ($sgn, $user_id);
  //$favorites = $user["myfavorites"];

  $table = "ims_".IMS_SuperGroupName()."_case_data";
  if($myconfig[IMS_SuperGroupName()]["casetypes"] == "yes") {
    foreach($favorites as $key=>$dummy) {
      $body = mb_ref($table, substr($key,0,34) );
      if($casetype&&($casetype!=$body["category"])) { // JG !== to != fixes not showing of favorites without $_GET["casetype"]
        if($casetype !== "allcases") unset($favorites[$key]);
      }
    }
  }

  foreach($favorites as $key=>$favo) {
    $value = $favo["longtitle"]?$favo["longtitle"]:$favo["shorttitle"];
    $tempsort[$key] = strtolower($value);
  }

  asort($tempsort);
  if($favorites) {
          startblock (ML("Favorieten","Favorites"), "docnav");
          T_Start ("ims",array ("noheader"=>"yes", "extra-table-props" => 'width="100%"'));
          foreach($tempsort as $favoritekey=>$dummy) {
            $form = array ();
            $form["title"] = ML ("Eigenschappen Favoriet", "Properties Favorite");
            $form["input"]["me"] = $favoritekey;

            //ericd 300513 RFC319
            $form["input"]["sgn"] = $sgn;
            $form["input"]["userID"] = $user_id;

            $form["metaspec"]["fields"]["longtitle"]["type"] = "string";
            if ($myconfig[IMS_SuperGroupName()]["casetypes"] == "yes") {
              $form["metaspec"]["fields"]["category"]["type"] = "list";
              foreach ($categories as $id => $name) {
                $object = mb_ref("ims_".IMS_SuperGroupName()."_case_types",$id);
                $list = $object["rights"]["view"];
                if(SHIELD_ValidateAccess_List(IMS_SuperGroupName(),SHIELD_CurrentUser(),$list)){
                  $form["metaspec"]["fields"]["category"]["values"][$name] = $id;
                }
              }
            }
//                            <tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[shorttitle]]]</td></tr>
            $form["formtemplate"] = '<body bgcolor=#f0f0f0><br><center><table>
                            <tr><td><font face="arial" size=2><b>'.ML("Omschrijving","Omschrijving").':</b></font></td><td>[[[longtitle]]]</td></tr>
                            <tr><td colspan=2>&nbsp</td></tr>
                            <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                            </table></center></body>';
            $form["precode"] = '
              //$user_id = SHIELD_CurrentUser (IMS_SuperGroupName());
              //$user = MB_Ref ("shield_" . IMS_SuperGroupName() . "_users", $user_id);
              //$data = $user["myfavorites"][$input["me"]];

              //ericd 300513 RFC319
              uuse("dmsuif");
              $sgn = $input["sgn"];
              $userID = $input["userID"];
              $favorites = DMSUIF_GetUserFavoritesFormerFormat ($sgn, $userID);
              $data = $favorites[$input["me"]];

            ';
            $form["postcode"] = '
              //$user_id = SHIELD_CurrentUser (IMS_SuperGroupName());
              //$user = &MB_Ref ("shield_" . IMS_SuperGroupName() . "_users", $user_id);
              //$user["myfavorites"][$input["me"]]["longtitle"] = $data["longtitle"];
              //if ($myconfig[IMS_SuperGroupName()]["casetypes"] == "yes") {$user["myfavorites"][$input["me"]]["category"] = $data["category"];}

              //ericd 300513 RFC319, save fav, get key first
              uuse("dmsuif");
              $sgn = $input["sgn"];
              $userID = $input["userID"];
              $userFav = DMSUIF_GetUserFavorite ($sgn, $userID, $input["me"]);
              if($userFav !== false) {
                $k = key($userFav);
                DMSUIF_SaveFavorite ($sgn, $k, $userID, $input["me"], $data);
              }
            ';
            $url = FORMS_URL ($form);
            echo '<a class="ims_navigation" title="'.$form["title"].'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/properties_small.gif">&nbsp;</a>';
            
            //ericd 300513 RFC319
            $sgn = IMS_SuperGroupName();

            $form = array ();

            //ericd 300513 RFC319
            $form["input"]["sgn"] = $sgn;
            $form["input"]["userID"] = SHIELD_CurrentUser($sgn);

            $form["input"]["me"] = $favoritekey;
            $form["postcode"] = '
              //$user_id = SHIELD_CurrentUser (IMS_SuperGroupName());
              //$user = &MB_Ref ("shield_" . IMS_SuperGroupName() . "_users", $user_id);
              //unset($user["myfavorites"][$input["me"]]);

              //ericd 300513 RFC319, delete fav
              uuse("dmsuif");
              $sgn = $input["sgn"];
              $userID = $input["userID"];
              DMSUIF_DeleteUserFavorite ($sgn, $userID, $input["me"]);
            ';
            $url = FORMS_URL ($form);
            echo '<a class="ims_navigation" title="'.ML("Verwijder favoriet","Delete favorite").'" href="'.$url.'"><img border="0" src="/ufc/rapid/openims/delete_small.gif">&nbsp;</a>';
            T_Next(array("extra-td-std-props" => 'width="100%"'));

            //$folder = $user["myfavorites"][$favoritekey]["folder"];
            //$longtitle = $user["myfavorites"][$favoritekey]["longtitle"];
            //$title = $user["myfavorites"][$favoritekey]["shorttitle"]?$user["myfavorites"][$favoritekey]["shorttitle"]:$user["myfavorites"][$favoritekey]["longtitle"];

            //ericd 300513 RFC319, show fav, returns 1 result of false
            $userFav = DMSUIF_GetUserFavorite ($sgn, $user_id, $favoritekey);
            if($userFav !== false) {
              $k = key($userFav);
              $longtitle = $title = $userFav[$k]["longtitle"];
            }
            else
              $longtitle = $title = "";

            $url = "/openims/openims.php?mode=dms&currentfolder=" . $favoritekey;
            echo '<a class="ims_navigation" href="' . $url . '" title="' . DMSUIF_folderClickPath( IMS_SuperGroupName() , $favoritekey ) . '">' .
            htmlentities($longtitle) . '</a>';
            T_NewRow ();
          }
          TE_End ();
          endblock();
  }
}

function DMSUIF_canbeFavoriteFolder( $currentfolder )
{
  //"(hash)root" or longer ( (hash)hash
  global $myconfig;
  return $myconfig[IMS_SuperGroupName()]["myfavorites"] == "yes" && strlen( $currentfolder ) >= 38; 
}

function DMSUIF_isFavoriteFolder( $currentfolder )
{
  //$user_id = SHIELD_CurrentUser (IMS_SuperGroupName());
  //$user = MB_load ("shield_" . IMS_SuperGroupName() . "_users", $user_id);
  //return is_array( $user["myfavorites"][$currentfolder] );

  //ericd 300513 RFC319
  $sgn = IMS_SuperGroupName();
  $userID = SHIELD_CurrentUser($sgn);
  $userFav = DMSUIF_GetUserFavorite ($sgn, $userID, $currentfolder);
  if($userFav !== false)
    return true;
  else
    return false;
}

function DMSUIF_createFavoriteFolderFormsUrl( $currentfolder )
{
  //ericd 300513 RFC319
  $sgn = IMS_SuperGroupName();

//  $currentfolder = substr( $currentfolder , 0 , 34 );
  $form = array ();
  $form["title"] = ML ("Maak Favoriet","Make Favorite");

  //ericd 300513 RFC319
  $form["input"]["sgn"] = $sgn;
  $form["input"]["userID"] = SHIELD_CurrentUser($sgn);

  $form["input"]["me"] = $currentfolder;//= substr( $key , 0 , 34 );
  $form["input"]["folder"] = "root";
  $form["metaspec"]["fields"]["longtitle"]["type"] = "string";
//  $form["metaspec"]["fields"]["shorttitle"]["type"] = "string";
  $form["metaspec"]["fields"]["longtitle"]["required"] = true;
//  $form["metaspec"]["fields"]["shorttitle"]["required"] = true;
  $form["metaspec"]["fields"]["longtitle"]["title"] = ML("Omschrijving", "Description");
//  $form["metaspec"]["fields"]["shorttitle"]["title"] = ML("Naam", "Name");
  if ($myconfig[IMS_SuperGroupName()]["casetypes"] == "yes") 
  {
    $form["metaspec"]["fields"]["category"]["type"] = "list";
    foreach ($categories as $id => $name) {
      $object = mb_ref("ims_".IMS_SuperGroupName()."_case_types",$id);
      $list = $object["rights"]["view"];
      if(SHIELD_ValidateAccess_List(IMS_SuperGroupName(),SHIELD_CurrentUser(),$list))
      {
        $form["metaspec"]["fields"]["category"]["values"][$name] = $id;
      }
    }
  }
  // <tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[shorttitle]]]</td></tr>
  $form["formtemplate"] = '<body bgcolor=#f0f0f0><br><center><table>
                           <tr><td colspan=2><font face="arial" size=4><b>'.ML("Maak Favoriet","Make Favorite").'</b></font></td></tr>
                           <tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[longtitle]]]</td></tr>
                           <tr><td colspan=2>&nbsp</td></tr>
                           <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                           </table></center></body>';

  $form["precode"] = '
                    $casedata = MB_ref("ims_".IMS_SuperGroupName()."_case_data", substr( $input["me"] , 0 , 34 ) );
					$data["longtitle"] = $casedata["longtitle"];
					if (!$data["longtitle"]) !$data["longtitle"] = $casedata["shortitle"];
					
					if ( substr( $input["me"],-5 ) != ")root")
					{
  					  $casetrees = MB_ref("ims_".IMS_SuperGroupName()."_case_trees", substr( $input["me"] , 0 , 34 ) );
					  $foldername = $casetrees["objects"][$input["me"]]["shorttitle"];
					  if ( $foldername )
   					    $data["longtitle"] .= "-" . $foldername;
					  if ( function_exists("favoriteFolders_alter_longtitle_in_precode") )
					    $data["longtitle"] = favoriteFolders_alter_longtitle_in_precode( $data["longtitle"] );
					}
                  ';

  $form["postcode"] = '
                    //$user_id = SHIELD_CurrentUser (IMS_SuperGroupName());
                    //$user = &MB_Ref ("shield_" . IMS_SuperGroupName() . "_users", $user_id);
                    //$user["myfavorites"][$input["me"]]["shorttitle"] = $data["shorttitle"];
                    //$user["myfavorites"][$input["me"]]["longtitle"] = $data["longtitle"];
                    //$user["myfavorites"][$input["me"]]["folder"] = "root";//$input["folder"];

    //ericd 300513 RFC319, save fav
    uuse("dmsuif");
    $sgn = $input["sgn"];
    $userID = $input["userID"];
    $favorite = $input["me"];
    DMSUIF_SaveFavorite ($sgn, "", $userID, $favorite, $data);
  ';

  $url = FORMS_URL ($form);
  return $url;
}

function DMSUIF_objectClickPath( $supergroupname , $object_id , $clickable = false )
{
  $object = MB_load( "ims_" . $supergroupname . "_objects" , $object_id );
  $currentfolder = $object["directory"];
  return DMSUIF_folderClickPath( $supergroupname , $currentfolder , $clickable );
}

function DMSUIF_folderClickPath( $supergroupname , $currentfolder , $clickable = false )
{
  global $myconfig;
  $tree = CASE_TreeRef ($supergroupname, $currentfolder);

  $path = TREE_Path ($tree, $currentfolder);

  $pathmode="all";
        $url = "/openims/openims.php?mode=dms&currentfolder=".$path[1]["id"];
        if (substr ($currentfolder, 0, 1)=="(") {
          $case_id = substr ($currentfolder, 0, strpos ($currentfolder, ")")+1);
          $case = MB_Ref ("ims_".$supergroupname."_case_data", $case_id);
          if ($myconfig[$supergroupname]["casetypes"]=="yes" && $myconfig[$supergroupname]["casetypeinclickpath"]=="yes") {
            // Add casetype
            $viscasetype = MB_Fetch("ims_".$supergroupname."_case_types", $case["category"], "name");
            $casetypeurl = "/openims/openims.php?mode=dms&submode=cases&casetype=".$case["category"];
            if ( !$clickable )
              $pathtitle .= N_htmlentities($viscasetype.": ");
            else
              $pathtitle .= "<a title = \"".ML("Dossiercategorie","Case type") . ": " . N_Htmlentities($viscasetype)."\" class=\"ims_headnav\" href=\"$casetypeurl\">".N_htmlentities($viscasetype)."</a>: ";
          } 
// 20101207 KvD SWEBRU-45: Aanhalingstekens moeten ook kunnen
            if ( !$clickable )
              $pathtitle .= N_htmlentities((($case["longtitle"].""=="")?$case["shorttitle"]:$case["longtitle"])." >> ");
            else
              $pathtitle .= "<a title = \"".N_htmlentities($case["shorttitle"])."\" class=\"ims_headnav\" href=\"$url\">".N_htmlentities(($case["longtitle"].""=="")?$case["shorttitle"]:$case["longtitle"])."</a> &gt;&gt; ";

        }
        if ( !$clickable )
          $pathtitle .= N_htmlentities($path[1]["shorttitle"]);
        else
          $pathtitle .= "<a title=\"".N_htmlentities($path[1]["longtitle"])."\" class=\"ims_headnav\" href=\"$url\">".$path[1]["shorttitle"]."</a>";

        for ($i=2; $i<=count($path); $i++) {
          if ($path[$i]["id"] == $rootfolder) $pathmode="projects";
          if ($pathmode=="projects") {
            $url = "/openims/openims.php?mode=dms&submode=projects&rootfolder=$rootfolder&currentfolder=".$path[$i]["id"];
          } else {
            $url = "/openims/openims.php?mode=dms&currentfolder=".$path[$i]["id"];
          }
          if ( !$clickable )
            $pathtitle .= " &gt;". N_htmlentities( $path[$i]["shorttitle"] );
          else
            $pathtitle .= " &gt; "."<a title=\"".$path[$i]["longtitle"]."\" class=\"ims_headnav\" href=\"$url\">".$path[$i]["shorttitle"]."</a>";
        }

  return $pathtitle;
}

function DMSUIF_pdfpreview_clickAway()
{
        $content .= <<<clickaway
<script type="text/javascript">

$(document).click(function(event) { 
// 20130115 KVD CORE-44 Ook *op* plaatje klikkekn klikt m weg
  if (!$(event.target).is('.imagezoomin'))// && !$(event.target).is('.pdfpreview > img'))
  {
    hidePdfPreview();
  }
} );
   // Bij klikken op een plaatje werkt het alleen met een wachtlus van 0.1 seconde, niet direct. Met tekst wel.
   // Vandaar deze extra functie.

   function XshowPdfPreview(divid) { setTimeout('$("#'+divid+'").show();', 100); }

</script>
clickaway;
return $content;
}

function DMSUIF_Firefox()
{
  $str = $_SERVER['HTTP_USER_AGENT'];

  if (stripos($str, "firefox") === false)
    return false;
  else
    return true;
}

function DMSUIF_DragDropHandler() // qqq
{
  $specs["precode"] = '
    global $sourceid, $targetid, $mousebutton;
    if (substr ($targetid, 0, 7)=="folder_" && substr ($sourceid, 0, 9)=="shortcut_") {
      if ($mousebutton=="right") { // ask
//        $formtemplate = "SHORTCUT [[[ok]]] [[[cancel]]]";
      }
    }
    if (substr ($targetid, 0, 7)=="folder_" && substr ($sourceid, 0, 9)=="document_") {
      if ($mousebutton=="right") { // ask
//        $formtemplate = "DOCUMENT [[[ok]]] [[[cancel]]]";
      }
    }
  ';
  $specs["postcode"] = '
    global $sourceid, $targetid, $mousebutton, $myconfig;
    if (substr ($targetid, 0, 7)=="folder_" && substr ($sourceid, 0, 9)=="shortcut_") {
      if (true) { // move
        if ($myconfig[IMS_SupergroupName()]["permalinkalwaysviewdoc"] == "yes" and FILES_IsPermalink(IMS_SupergroupName(), substr($sourceid, 9)))
          $base  = substr($sourceid, 9);
        else
          $base = FILES_Base (IMS_SuperGroupName(), substr ($sourceid, 9));
        if (!SHIELD_HasObjectRight (IMS_SuperGroupName(), $base, "move")) {
          FORMS_ShowError (ML("Foutmelding","Error"), ML("Niet voldoende rechten om documenten/shortcuts te verplaatsen","Not enough rights to move document/shortcut"), "no");
        }
        if ($locked=IMS_IsLocked (IMS_SuperGroupName(), $base)) {
          FORMS_ShowError (ML("Foutmelding","Error"), $locked, "no");
        }
        $object = &IMS_AccessObject (IMS_SuperGroupName(), substr ($sourceid, 9));
        $object["directory"] = substr ($targetid, 7);
        IMS_RefreshShortcut (IMS_SuperGroupName(), $object_id);
      }
    }
    if (substr ($targetid, 0, 7)=="folder_" && substr ($sourceid, 0, 9)=="document_") {
      if (true) { // move
        if (!SHIELD_HasObjectRight (IMS_SuperGroupName(), substr ($sourceid, 9), "move")) {
          FORMS_ShowError (ML("Foutmelding","Error"), ML("Niet voldoende rechten om documenten te verplaatsen","Not enough rights to move document"), "no");
        }
        $secsec = SHIELD_SecuritySectionForFolder (IMS_SuperGroupName(), substr ($targetid, 7));
        if (!SHIELD_HasGlobalRight (IMS_SuperGroupName(), "moveto", $secsec) &&
            !SHIELD_HasGlobalRight (IMS_SuperGroupName(), "newdoc", $secsec)) {
          FORMS_ShowError (ML("Foutmelding","Error"), ML("Niet voldoende rechten om documenten te verplaatsen","Not enough rights to move document"), "no");
        }
        if ($locked=IMS_IsLocked (IMS_SuperGroupName(), substr ($sourceid, 9))) {
          FORMS_ShowError (ML("Foutmelding","Error"), $locked, "no");
        }
        $object = &MB_Ref ("ims_".IMS_SuperGroupName()."_objects", substr ($sourceid, 9));
        global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
        $time = time();
        $guid = N_GUID();
        $object["history"][$guid]["type"] = "move";
        $object["history"][$guid]["when"] = $time;
        $object["history"][$guid]["author"] = SHIELD_CurrentUser (IMS_SuperGroupName());
        $object["history"][$guid]["server"] = N_CurrentServer ();
        $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
        $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
        $object["history"][$guid]["server_addr"] = $SERVER_ADDR;
        $object["history"][$guid]["from"] = IMS_GetDMSDocumentPath(IMS_SuperGroupName(), $object["directory"]);
        $object["history"][$guid]["to"]   = IMS_GetDMSDocumentPath(IMS_SuperGroupName(), substr ($targetid, 7));
        $object["directory"] = substr ($targetid, 7);
      }
    }
  ';
  return $specs;
}


function DMSUIF_SendMail ($supergroupname, $key)
{
  if ( N_android() || N_iOs() )
    DMSUIF_SendMail_mailTo ($supergroupname, $key);
  else
    DMSUIF_SendMail_outlook ($supergroupname, $key);
}

function DMSUIF_SendMail_mailTo ($supergroupname, $key)
{
  global $HTTP_HOST;
  global $myconfig;

  $object = &IMS_AccessObject ($supergroupname, $key);

  $mailto = 'mailto:?subject='.str_replace( '+',' ',urlencode($object["shorttitle"])).'&amp;body=';
  $dmsurl = urlencode( N_CurrentProtocol() . $HTTP_HOST . FILES_DMSURL($supergroupname, $key) );
  
  $directurl = N_CurrentProtocol() . $HTTP_HOST . FILES_DocPreviewURL($supergroupname, $key);
  $body_pre = 'Hyperlink: ';
  $body_post = urlencode( N_EOL() );
  
  $workflow = &MB_Ref ("shield_".$supergroupname."_workflows", $object["workflow"]);
  $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
 
  $body_post .=  'Naam: ' . str_replace( '+' , ' ' , urlencode( $object["shorttitle"] . N_EOL() ) ) ;
  if( !$myconfig[$supergroupname]["dmsemailnodescription_mailto"] )
    $body_post .= 'Omschrijving: ' . str_replace( '+' , ' ' , urlencode( $object["longtitle"] . N_EOL() ) ); 
  
  if( !$myconfig[$supergroupname]["dmsemailnometadata_mailto"] )
  {
     for ($i=1; $i<1000; $i++)
	{
	  if ( $fieldname = $workflow["meta"][$i] )
	  {
            $title = $allfields[$fieldname]["title"];
		
	    $body_post .= str_replace( "+" , " " , urlencode(
                   $title.": " . 
                   FORMS_ShowValue($object['meta_'.$fieldname] , $allfields[$fieldname] ) . //, $object['meta_'.$fieldname] , $object 
                   N_EOL()
                 ) );
        }
      }
  }
  $title = ML("Verstuur hyperlink naar document in DMS","Send a hyperlink to the document in the DMS");
  echo '<a class="ims_navigation" title="'.$title.'" href="' . $mailto . $body_pre . $dmsurl . $body_post . '"><img border=0 src="/openims/ico_eml.gif"> '.ML("E-mail link naar dms","E-mail to dms").'</a><br/>';
  $title = ML("Verstuur directe hyperlink naar document","Send a direct hyperlink to the document");
  echo '<a class="ims_navigation" title="'.$title.'" href="' . $mailto . $body_pre . $directurl . $body_post . '"><img border=0 src="/openims/ico_eml.gif"> '.ML("E-mail link naar document","E-mail link to document").'</a><br/>';
}

function DMSUIF_SendMail_outlook ($supergroupname, $key)
{
  global $HTTP_HOST;
  $object = &IMS_AccessObject ($supergroupname, $key);
  $title = ML("Verzend","Send")." &quot;".htmlentities ($object["shorttitle"])."&quot; ".ML("via e-mail","through e-mail");
  $form["title"] = $title;
  $form["input"]["sgn"] = $supergroupname;
  $form["metaspec"]["fields"]["subject"]["type"] = "list";
  $form["metaspec"]["fields"]["subject"]["values"][ML("Naam","Name")] = "shorttitle";
  $form["metaspec"]["fields"]["subject"]["values"][ML("Omschrijving","Description")] = "longtitle";
  $workflow = &MB_Ref ("shield_".$supergroupname."_workflows", $object["workflow"]);
  $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());

  global $myconfig;
  if(!$myconfig[$supergroupname]["dmsemailnometadata"]) {
    for ($i=1; $i<1000; $i++) {
      if ($workflow["meta"][$i]) {
        $form["metaspec"]["fields"]["subject"]["values"][$allfields[$workflow["meta"][$i]]["title"]] = "meta_".$workflow["meta"][$i];
      }
    }
  }

  $form["metaspec"]["fields"]["subject"]["values"][ML("Naam","Name")] = "shorttitle";
  $form["metaspec"]["fields"]["subject"]["values"][ML("Omschrijving","Description")] = "longtitle";
  $form["metaspec"]["fields"]["method"]["type"] = "list";
  if ( $myconfig[$supergroupname]["email_method_default"]!="" )
    $form["metaspec"]["fields"]["method"]["default"] = $myconfig[$supergroupname]["email_method_default"];

  $protocol = N_CurrentProtocol();

  $form["metaspec"]["fields"]["method"]["values"][ML("Hyperlink naar DMS","Hyperlink to DMS")] = "DMSURL";
  $form["input"]["DMSURL"] = $protocol .$HTTP_HOST.FILES_DMSURL ($supergroupname, $key);
  if (FILES_DocPreviewURL ($supergroupname, $key)) {
    $form["metaspec"]["fields"]["method"]["values"][ML("Directe hyperlink naar de laatste versie","Direct hyperlink to the latest version")] = "DocPreviewURL";
    $form["input"]["DocPreviewURL"] = $protocol . $HTTP_HOST.FILES_DocPreviewURL ($supergroupname, $key);
  }
  if (FILES_DocPublishedURL ($supergroupname, $key)) {
    $form["metaspec"]["fields"]["method"]["values"][ML("Directe hyperlink naar de laatste volledig goedgekeurde versie","Direct hyperlink to the latest approved version")] = "DocPublishedURL";
    $form["input"]["DocPublishedURL"] = $protocol .$HTTP_HOST.FILES_DocPublishedURL ($supergroupname, $key);
  }
   if ($myconfig[$supergroupname]["dmssendmail_permaURL"] == "yes") { // gv 10-8-2012 
    $form["metaspec"]["fields"]["method"]["values"][ML("Permanente hyperlink naar de huidige versie","Permanent hyperlink to the current version")] = "DocPermanentVersionURL";
    $form["input"]["DocPermanentVersionURL"] = $protocol .$HTTP_HOST.FILES_DocPermanentVersionURL ($supergroupname, $key);
  }


  $form["formtemplate"] = '
    <table>
      <tr><td><font face="arial" size=2><b>'.ML("Onderwerp","Subject").':</b></font></td><td>[[[subject]]]</td></tr>
      <tr><td><font face="arial" size=2><b>'.ML("Methode","Method").':</b></font></td><td>[[[method]]]</td></tr>
      <tr><td colspan=2>&nbsp</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
    </table>
  ';
  $form["postcode"] = '
    uuse ("dhtml");
    uuse("dmsuif");
    $sgn = $input["sgn"];
    if ($data["method"]=="DocPublishedURL") {
      $url = $input["DocPublishedURL"];
    } else if ($data["method"]=="DMSURL") {
      $url = $input["DMSURL"];
    } else if ($data["method"]=="DocPreviewURL") {
      $url = $input["DocPreviewURL"];
    } else if ($data["method"]=="DocPermanentVersionURL") {
      $url = $input["DocPermanentVersionURL"];
    }

    $body = "Hyperlink: " . $url . N_EOL();;
    $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
    foreach ($input["metaspec"]["fields"]["subject"]["values"] as $title => $id)
    {
      if ($input["object"][$id]) {
        $body .= str_replace("/"," ",
                   $title.": " .
                   FORMS_ShowValue($input["object"][$id],$allfields[str_replace("meta_","",$id)],$object[$id],$object) .
                   N_EOL()
                 );
      }
    }
    //ericd 230712
    if($myconfig[$sgn]["dmsemailextratext"] && $data["method"] !== "DMSURL") {
      $body .= $myconfig[$sgn]["dmsemailextratext"];
    }
    //sbr
    $url = DHTML_EmailURL ("", FORMS_ShowValue(str_replace("/"," ",$input["object"][$data["subject"]]),str_replace("/"," ",$allfields[str_replace("meta_","",$data["subject"])]),str_replace("/"," ",$object[$data["subject"]]),$object), $body);
    $url = str_replace("_htt","%20htt",$url);
    // 20100602 KvD
    if ($GLOBALS["myconfig"]["dmssendmail_replacespaces"] == "yes")
      $url = str_replace(" ", "%20", $url);
    ///
    $gotook = "closeme&parentgoto:$url";
  ';
  $form["input"]["object"] = $object;
  $form["input"]["metaspec"] = $form["metaspec"];
  $url = FORMS_URL ($form);
  echo '<a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border=0 src="/openims/ico_eml.gif"> '.ML("E-Mail","E-Mail").'</a><br>';
}


function DMSUIF_SendMail_Multi ($supergroupname)
{
  global $HTTP_HOST;

  $title = ML("Verzend","Send")." ".ML("geselecteerde bestanden","selected documents")." ".ML("via e-mail","through e-mail");
  $form["title"] = $title;
  $form["input"]["sgn"] = $supergroupname;
  $form["metaspec"]["fields"]["subject"]["type"] = "string";
  $form["metaspec"]["fields"]["method"]["type"] = "list";
  $form["metaspec"]["fields"]["method"]["values"][ML("Directe hyperlink naar de laatste versie","Direct hyperlink to the latest version")] = "DocPreviewURL";
  $form["metaspec"]["fields"]["method"]["values"][ML("Directe hyperlink naar de laatste volledig goedgekeurde versie","Direct hyperlink to the latest approved version")] = "DocPublishedURL";
  $form["metaspec"]["fields"]["method"]["values"][ML("Hyperlink naar DMS","Hyperlink to DMS")] = "DMSURL";

  $form["formtemplate"] = '
    <table>
      <tr><td><font face="arial" size=2><b>'.ML("Onderwerp","Subject").':</b></font></td><td>[[[subject]]]</td></tr>
      <tr><td><font face="arial" size=2><b>'.ML("Methode","Method").':</b></font></td><td>[[[method]]]</td></tr>
      <tr><td colspan=2>&nbsp</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
    </table>
  ';

  $form["postcode"] = '
    uuse ("dhtml");
    uuse ("multi");
    uuse ("files");
    global $HTTP_HOST;
    $protocol = N_CurrentProtocol();

    $list = MULTI_Load_AutoShortcuts($input["sgn"]);
    foreach ($list as $key => $dummy) {
      $object = MB_Ref("ims_".$input["sgn"]."_objects", $key);
      $url .= N_EOL() . N_EOL() . $object["shorttitle"] . ":";
      if ($data["method"]=="DocPublishedURL") {
         if (FILES_DocPublishedURL ($input["sgn"], $key)) {
            $url .= N_EOL() . $protocol . $HTTP_HOST.FILES_DocPublishedURL ($input["sgn"], $key);
         } else {
            $url .= N_EOL() . ML("Onbekend (document is niet gepubliceerd).","Unknown (document not published).");
         }
      } else if ($data["method"]=="DMSURL") {
         $url .= N_EOL() . $protocol . $HTTP_HOST.FILES_DocPreviewURL ($input["sgn"], $key);
      } else {
         $url .= N_EOL() . $protocol . $HTTP_HOST.FILES_DMSURL ($input["sgn"], $key);
      }
    }

    $body = "" . $url . N_EOL();

    $url = DHTML_EmailURL ("", str_replace("/"," ",$data["subject"]), $body);

    $gotook = "closeme&parentgoto:$url";
  ';
  $form["input"]["metaspec"] = $form["metaspec"];
  $url = FORMS_URL ($form);
  echo '<a class="ims_navigation" title="'.$title.'" href="'.$url.'"><img border=0 src="/openims/ico_eml.gif"> '.ML("E-Mail","E-Mail").'</a><br>';
}


function DMSUIF_Properties ($supergroupname, $key, $object, $icononly=false, $donotcenter=false)
{
  /* $icononly:
   *   false: return an clickable icon + textual hyperlink (HTML)
   *   "justgivemetheform": return something that you can use as input for FORMS_URL or FORMS_GenerateSuperform
   *   other true values: return a clickable icon (HTML)
   */
  // 20110111 KvD Ook grotere popup laten zien
  global $myconfig;
  $largepopup = ($myconfig[$supergroupname]["showdocumentwithproperties"] && ($maxsize = $myconfig[$supergroupname]["showdocumentwithproperties_size"])) ;


        if (SHIELD_HasObjectRight ($supergroupname, $key, "edit") || SHIELD_HasObjectRight ($supergroupname, $key, "view")) {
          $title = ML("Eigenschappen van","Properties of")." &quot;".$object["shorttitle"]."&quot;";
          $metaspec = array();
          $metaspec["fields"]["workflow"]["type"] = "list";
          $metaspec["fields"]["workflow"]["default"] = "edit-publish";
          $metaspec["fields"]["workflow"]["show"] = "visible";
          $wlist = MB_Query ("shield_".$supergroupname."_workflows", '$record["dms"]', 'strtolower(FORMS_ML_Filter($record["name"]))');
          $secsection = SHIELD_SecuritySectionForFolder ($supergroupname, $object["directory"]);
          $allowed = SHIELD_AllowedWorkflows ($supergroupname, $key, $secsection);
          if (is_array($wlist)) reset($wlist);
          if (is_array($wlist)) while (list($wkey)=each($wlist)) {
            if ($allowed[$wkey]) {
              $workflow = &MB_Ref ("shield_".$supergroupname."_workflows", $wkey);
              $metaspec["fields"]["workflow"]["values"][$workflow["name"]] = $wkey;
            }
          }
          $metaspec["fields"]["longtitle"]["type"] = "strml4";
    
          $metaspec["fields"]["longtitle"]["title"] = ML("Omschrijving", "Description");
          if ($myconfig[IMS_SupergroupName()]["dms"]["longtitlerequired"] == "yes") {
            $metaspec["fields"]["longtitle"]["required"] = "yes";
          }       

          $metaspec["fields"]["shorttitle"]["type"] = "strml4";
          $metaspec["fields"]["shorttitle"]["title"] = ML("Document naam", "Document name");
          $metaspec["fields"]["shorttitle"]["validationcode"] = '
            global $myconfig;

            if ($myconfig[IMS_SuperGroupName()]["hasshorttitlevalidation"]=="yes") {
              eval ($myconfig[IMS_SuperGroupName()]["shorttitlevalidationcode"]);
            }

            if (!trim($input)) {
              $error = ML("is leeg","is empty");
            }
          ';

          $workflow = &MB_Ref ("shield_".$supergroupname."_workflows", $object["workflow"]);
          $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
          $obj = MB_Load ("ims_".$supergroupname."_objects", $key);
          for ($i=1; $i<1000; $i++) {
            if ($workflow["meta"][$i]) {
              $metalist[$workflow["meta"][$i]] = $workflow["meta"][$i];
              $readonlylist[$workflow["meta"][$i]] = $workflow["readonly"][$i];
            }
          }
          if ($obj["dynmeta"] && is_array ($obj["dynmeta"])) {
            foreach ($obj["dynmeta"] as $dummy => $field) {
              $metalist[$field] = $field;
            }
          }
          foreach ($metalist as $field) {
            $metaspec["fields"]["meta_".$field] = $allfields[$field];
          }

          global $myconfig;
          if ($myconfig[$supergroupname]["autohtml"]) {
             $ext = FILES_FileType($supergroupname, $key, "preview");
             if (($obj["executable"] == "winword.exe" || ($obj["executable"] == "auto" && ($ext=="doc")||($myconfig["docxconversion"] == "yes" && $ext=="docx"))) && ($myconfig[$supergroupname]["autohtml"]["showfield"] != "no")) { 
                $metaspec["fields"]["meta_autohtml"]["type"] = "list";
                $metaspec["fields"]["meta_autohtml"]["title"] = $myconfig[$supergroupname]["autohtml"]["title"];
                $metaspec["fields"]["meta_autohtml"]["values"][ML("Geen HTML conversie", "No HTML conversion")] = "";
                if (is_array ($myconfig[$supergroupname]["autohtml"]) && is_array ($myconfig[$supergroupname]["autohtml"]["options"])) {
                  foreach ($myconfig[$supergroupname]["autohtml"]["options"] as $wgkey => $wgvalues) {
                     $metaspec["fields"]["meta_autohtml"]["values"][$wgvalues["name"]] = $wgkey;
                  }
                }
                $metalist["autohtml"] = "autohtml";
             }
          }
//          if ($myconfig[$supergroupname]["autovisiohtml"]) {
//             $ext = strtolower (FILES_FileType ($supergroupname, $key, "preview"));
//             if ($ext == "vsd") {
//                $metaspec["fields"]["meta_autovisiohtml"]["type"] = "list";
//                $metaspec["fields"]["meta_autovisiohtml"]["title"] = ML("Visio naar HTML", "Visio to HTML");
//                $metaspec["fields"]["meta_autovisiohtml"]["values"][ML("Geen HTML conversie", "No HTML conversion")] = "";
//                $metaspec["fields"]["meta_autovisiohtml"]["values"][ML("HTML conversie", "HTML conversion")] = "yes";

//                $metalist["autovisiohtml"] = "autovisiohtml";
//             }
//          }

//          for ($i=1; $i<1000; $i++) {
//            if ($workflow["meta"][$i]) {
//              $metaspec["fields"]["meta_".$workflow["meta"][$i]] = $allfields[$workflow["meta"][$i]];
//            }
//          }

          $showimage = false;
          $showdoc = false;
          $validpreviewextensions = false;  //20110111 KvD Gweldige extensies voor preview
          if (SHIELD_HasObjectRight ($supergroupname, $key, "edit") || $largepopup)
          {
            global $myconfig;
            if ($myconfig[$supergroupname]["showimagewithproperties"] == "yes")
            {
              $imagepath = FILES_Filelocation($supergroupname, $key);
              N_ErrorHandling(false);
              $imageinfo = @getImageSize($imagepath);
              N_ErrorHandling(true);
              if ($imageinfo)
              {
                $showimage = true;
                $imagewidth = $imageinfo[0];
                $imageheight = $imageinfo[1];
                if ($imagewidth > 500) // if the picture is too big...
                {
                  $imageheight = (500/$imagewidth) * $imageheight;
                  $imagewidth = 500;
                }
              }
              $imagelink = FILES_DocPreviewURL($supergroupname, $key);
            }
            $doctypes = $myconfig[$supergroupname]["showdocumentwithproperties"];
            if (is_array($doctypes))
            {
              foreach ($doctypes as $i => $doctype)
                $doctypes[$i] = strtoupper($doctype);
              $nrdoctypes = count($doctypes);
            }
            else
            {
              $nrdoctypes = 0;
            }
            if (!$showimage && $nrdoctypes > 0)
            {
              $filename = $obj["filename"]; // OK preview
              $ext = N_KeepAfter($filename, ".", true);
              $ext = strtoupper($ext);
              $validpreviewextensions = in_array($ext, $doctypes);

              // 20110111 KvD Alleen niet in de browser wijzigbare bestandstypen
              if (!SHIELD_HasObjectRight ($supergroupname, $key, "edit")) 
                 $validpreviewextensions = $validpreviewextensions && in_array($ext, array("PDF", "GIF", "JPG", "PNG", "JPEG", "SVG"));  // 201000209 KvD in HOOFDletters !!

              if ($validpreviewextensions) // in order to make this work for word
              {                               // you will need to adjust some browser settings
                $showdoc = true;
                $doclink = FILES_DocPreviewURL($supergroupname, $key);
                $spstr = "";
                // 20110111 KvD Niet bij grotere popup
                if (!$largepopup) {
                  for ($i = 1; $i <= 275; ++$i)
                  {
                    $spstr .= "&nbsp;"; // this string is needed because pdf etc. is squashed by the popup window
                  }
                }
              } else  // anders GEEN grote popup
                $largepopup = false;
            }
          }
          // 20110111 KvD Ook grotere popup laten zien
          $pixelwidth = $pixelwidth1 = $pixelwidth100 = "";

          if ($largepopup) {
            $pixelwidth100 = " width='100%'";
            $pixelwidth1 = " width='1'";
            if ($maxsize == "yes") $maxsize = 90;
            $resizediv = "<script type=\"text/javascript\">
document.write('<div style=\"width:'+(window.screen.availWidth*($maxsize)/100)+'px\">&nbsp;</div>');
</script>
";
          }
          ///
          $ast = ($myconfig[IMS_SupergroupName()]["useasteriskwithnameinproperties"] == "yes" ? "*" : "");
          $oast = ($myconfig[IMS_SupergroupName()]["dms"]["longtitlerequired"] == "yes" ? "*" : "");
          $formtemplate  = '<body bgcolor=#f0f0f0><br>' . $resizediv . ($donotcenter ? '' : '<center>') . (($showimage || $showdoc) ? "<table$pixelwidth100><tr><td$pixelwidth1 valign=\"top\">" : "") . "<table>
                            <tr><td><font face=\"arial\" size=2><b>".ML("Naam","Name").$ast.':</b></font></td><td>[[[shorttitle]]]</td></tr>
                            <tr><td><font face="arial" size=2><b>'.ML("Omschrijving","Description").$oast.':</b></font></td><td>[[[longtitle]]]</td></tr>
                            <tr><td><font face="arial" size=2><b>'.ML("Workflow","Workflow").$ast.':</b></font></td><td>[[[workflow]]]</td></tr>';
          foreach ($metalist as $field) {
            $formtemplate .= '<tr><td><font face="arial" size=2><b>'.$metaspec["fields"]["meta_".$field]["title"].':</b></font></td><td>';
            if ($readonlylist[$field]) {
              $formtemplate .= '<font face="arial" size=2>(((meta_'.$field.')))</font>';
            } else {
              $formtemplate .= '[[[meta_'.$field.']]]';
            }
            $formtemplate .= '</td></tr>';
          }
//          for ($i=1; $i<1000; $i++) {
//            if ($workflow["meta"][$i]) {
//              $formtemplate .= '<tr><td><font face="arial" size=2><b>'.$allfields[$workflow["meta"][$i]]["title"].':</b></font></td><td>[[[meta_'.$workflow["meta"][$i].']]]</td></tr>';
//            }
//          }
          // optional workflowsteps jh dec 2010
          uuse("workflow");
          $opt_arr = WORKFLOW_Options($supergroupname, $key, $object, false);
          if ($myconfig[$supergroupname]["showworkflowoptionswithproperties"]=="yes" and $opt_arr and is_array($opt_arr))
          {
            $formtemplate .= '<tr><td><font face="arial" size=2><b>Document workflow:</b></font></td>';
            $formtemplate .= '<td><select name="workflowstep"><option value="">-</option>';
            foreach ($opt_arr as $url => $option)
            {
              $formtemplate .= '<option value="' . $url . '">'.$option.'</option>';
            }
            $formtemplate .= "</select></td></tr>";
          }

          if (!SHIELD_HasObjectRight ($supergroupname, $key, "edit")) {
            $formtemplate = str_replace ("[[[", "&nbsp;<font face=\"arial\" size=2>(((", $formtemplate);
            $formtemplate = str_replace ("]]]", ")))</font>", $formtemplate);
          }
          if (!$showimage && !$validpreviewextensions) {
            $formtemplate .= '<tr><td colspan=2>&nbsp</td></tr>' .
                              ($donotcenter
                                ? '<tr><td></td><td>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</tr>'
                                : '<tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>') .
                             '</table>' . ($donotcenter ? '' : '</center>') . '</body>';
          } else {
            $formtemplate .= '<tr><td colspan=2>&nbsp</td></tr>' .
                              ($donotcenter
                                ? '<tr><td></td><td>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</tr>'
                                : '<tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>') .
                             '</table>' . (($showimage || $showdoc)?  "</td><td valign=\"top\"><table".$pixelwidth100."><tr><td valign=\"top\"><font face=\"arial\" size=2><b>" . ($showimage?ML("Inhoud afbeelding:", "Image content:"):"") .
                             "</b></font>" . ($showimage ? "<br><br><img width=\"" . $imagewidth . "\" height=\"" . $imageheight . "\" src=\"" . $imagelink . "\">" : "<iframe width=\"100%\" height=\"".(!DMSUIF_Firefox()?"600%":"1000px")."\" src=\"" . $doclink . "\"></iframe>"). "<br>" . $spstr . "<br>". "</td></tr></table></td></tr></table>" : "") . ($donotcenter ? '' : '</center>') . '</body>';

          }
          if (!SHIELD_HasObjectRight ($supergroupname, $key, "edit")) {
            $formtemplate = str_replace ("[[[CANCEL]]]", "&nbsp;", $formtemplate);
          }
          $input["sgn"] = $supergroupname;
          $input["id"] = $key;
          $input["workflowold"] = $object["workflow"];
          global $myconfig;
          $code = "";

          // JH,LF20100730: added setting "publishversion_dmsmetadata" to not update published index when metadata changes, but only
          // when the document is republished. This setting should not be used in isolation, but should be used together with
          // a modified search-component that uses $object["pub"] for filtering and displaying results. The package
          // Kwaliteitshandboek contains such a search-component.
          if ($object["published"]=="yes" and $myconfig[$supergroupname]["publishversion_dmsmetadata"] != "yes") {
            $code .= '
              N_SuperQuickScedule (N_CurrentServer(), \'SEARCH_AddPreviewDocumentToDMSIndex ($input ["sgn"], $input ["id"]);\', $input);
              N_SuperQuickScedule (N_CurrentServer(), \'SEARCH_AddDocumentToDMSIndex ($input ["sgn"], $input ["id"]);\', $input);
            ';
          } else {
            $code .= '
              N_SuperQuickScedule (N_CurrentServer(), \'SEARCH_AddPreviewDocumentToDMSIndex ($input ["sgn"], $input ["id"]);\', $input);
            ';
          }
          // add changes to properties to history
          $obj_pre_id = SHIELD_Encode (MB_Ref("ims_" . $supergroupname . "_objects", $key));
          $code .= '$obj_pre = SHIELD_Decode ("' . $obj_pre_id . '");
          $allfields = MB_Load ("ims_fields", IMS_SuperGroupName());

          $ret = array();
          $visualchange = false;
          foreach($rec as $key=>$value) {
            if(($key=="shorttitle") || ($key=="longtitle") || ($key=="workflow") || (substr($key,0,5)=="meta_") ) {
              if($rec[$key] != $obj_pre[$key]) {
                $new_ret = array();
                $new_ret["key"] = $key;
                if(substr($key,0,5)=="meta_") {
                  $new_ret["title"] = $allfields[substr($key,5)]["title"];
                } else {
                  $new_ret["title"] = $key;
                }
                switch($key) {
                  case "workflow":
                    $new_ret["old"] = MB_Fetch("shield_" . IMS_SuperGroupName() . "_workflows", $obj_pre[$key], "name");
                    $new_ret["oldinternal"] = $obj_pre[$key];
                    $new_ret["new"] = MB_Fetch("shield_" . IMS_SuperGroupName() . "_workflows", $rec[$key], "name");
                    $new_ret["newinternal"] = $rec[$key];
                  break;
                  case "meta_autohtml":
                    global $myconfig;
                    // If the object is published, and meta_autohtml used to be empty (now its not, because
                    // were looping through properties that have changed), a link to the published html version
                    // will appear in DMS and webgen will generate the html in the next batch.
                    // So create stub page if this functionality is enabled in myconfig.
                    if ((!$obj_pre[$key]) && ($obj_pre["published"] == "yes") && ($myconfig[$input["sgn"]]["autohtml"]["createstubpage"] == "yes")) {
                      uuse ("webgen");
                      WEBGEN_CreateStubPage($input["sgn"], $input["id"]);
                    }
                    // no break.  falling through on purpose.  DO NOT ADD NEW "case"s AT THIS POSITION!
                  default:
                    if(substr($key,0,5)=="meta_") {
                      $new_ret["old"] = FORMS_ShowValue($obj_pre[$key] , substr($key,5),$obj_pre ,$obj_pre);
                      if ($new_ret["old"] != $obj_pre[$key]) $new_ret["oldinternal"] = $obj_pre[$key];
                      $new_ret["new"] = FORMS_ShowValue($rec[$key] , substr($key,5), $rec, $rec);
                      if ($new_ret["new"] != $rec[$key]) $new_ret["newinternal"] = $rec[$key];
                    } else {
                      $new_ret["old"] = $obj_pre[$key];
                      $new_ret["new"] = $rec[$key];
                    }
                }
                if ($new_ret["old"] != $new_ret["new"]) $visualchange = true;
                $ret[]=$new_ret;
              }
            }
          }
          if($ret) {
            global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
            $time = time();
            $guid = N_GUID();
            if ($myconfig[IMS_SuperGroupName()]["dmshistoryhidepropertychangewithsamevisualvalue"] == "yes" && !$visualchange) {
              // If none of the changes involve a change in visual value, we do not want the "last changed" date to change
              // We could use a simple flag "hidden" to tell QRY_DMS_Changed to ignore it, but there are many copies of QRY_DMS_Changed logic elsewhere.
              // So only reliable solution is to spoof the timestamp itself.
              // DMS history view has been changed so that entries with spoofed date are never shown, regardless of the (current) value of "dmshistoryhidepropertychangewithsamevisualvalue"
              $last = end($rec["history"]);
              $time = $last["when"];
              $rec["history"][$guid]["truetime"] = time();
            }
            $rec["history"][$guid]["when"] = $time;
            $rec["history"][$guid]["author"] = SHIELD_CurrentUser ($sitecollection_id);
            $rec["history"][$guid]["type"] = "properties";
            $rec["history"][$guid]["server"] = N_CurrentServer ();
            $rec["history"][$guid]["http_host"] = getenv("HTTP_HOST");
            $rec["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
            $rec["history"][$guid]["server_addr"] = $SERVER_ADDR;
            $rec["history"][$guid]["changes"] = $ret;
            if ($input["workflowold"] != $rec["workflow"]) $rec["stage"] = 1;
          }
         ';

          uuse("workflow");
          $opt_arr = WORKFLOW_Options($supergroupname, $key, $object);
          if ($myconfig[$supergroupname]["showworkflowoptionswithproperties"]=="yes" and $opt_arr and is_array($opt_arr))
          {
            $code .= '$url=$_REQUEST["workflowstep"];if($url)$gotook=$url;';
          }

          if ($icononly === "justgivemetheform") {
            $form = array();
            $form["title"] = $title;
            $form["metaspec"] = $metaspec;
            $form["formtemplate"] = $formtemplate;
            $form["input"] = $input;
            $form["input"]["table"] = "ims_".$supergroupname."_objects";
            $form["input"]["key"] = $key;
            $form["input"];
            $form["precode"] = '$data = MB_Load ($input["table"], $input["key"]);';
            $form["postcode"] = '
              uuse("forms");
              $rec = MB_Load ($input["table"], $input["key"]);
              MB_Save ($input["table"], $input["key"], FORMS_Integrate ($rec, $input["path"], $data));
              $rec = &MB_Ref ($input["table"], $input["key"]);
              '.$code.'
            ';
            return $form;
          } elseif ($icononly) {
            echo "&nbsp";
            echo FORMS_GenerateEditExecuteLink ($code, $input, $title, '<img border="0" src="/openims/properties_small.gif">', $title, "ims_".$supergroupname."_objects", $key, '', $metaspec, $formtemplate, 400, 200, 200, 200, "ims_navigation");
            echo "<br>";
          } else {
            echo FORMS_GenerateEditExecuteLink ($code, $input, $title, '<img border="0" src="/openims/properties_small.gif"> '.ML("Eigenschappen","Properties"), $title, "ims_".$supergroupname."_objects", $key, '', $metaspec, $formtemplate, 400, 200, 200, 200, "ims_navigation");
            $error = FORMS_BlindValidation ($supergroupname, $key);
            if ($error) {
              echo " <span title=\"$error\"><font color=#ff0000><b>!!!</b></font></span>";
            }
            echo "<br>";
          }
        }

}

function DMSUIF_IndependentShortcutProperties ($sgn, $shortcut_id, $source_id) {
  // Returns a form url for editing the properties of independent shortcuts

  if (SHIELD_HasObjectRight ($sgn, $source_id, "edit") || SHIELD_HasObjectRight ($sgn, $source_id, "view")) {
    $shortcut = MB_Load("ims_{$sgn}_objects", $shortcut_id);
    $source = MB_Load("ims_{$sgn}_objects", $source_id);
    $workflow = MB_Load("shield_{$sgn}_workflows", $shortcut["workflow"]);

    $title = ML("Eigenschappen van snelkoppeling","Properties of shortcut")." &quot;".$shortcut["base_shorttitle"]."&quot;";
    $metaspec = array();
    $metaspec["fields"]["longtitle"]["type"] = "strml4";
    $metaspec["fields"]["longtitle"]["title"] = ML("Omschrijving","Description");
    $metaspec["fields"]["shorttitle"]["type"] = "strml4";
    $metaspec["fields"]["shorttitle"]["title"] = ML("Document naam","Document name");
    $metaspec["fields"]["shorttitle"]["validationcode"] = '
      global $myconfig;

      if ($myconfig[IMS_SuperGroupName()]["hasshorttitlevalidation"]=="yes") {
        eval ($myconfig[IMS_SuperGroupName()]["shorttitlevalidationcode"]);
      }

      if (!trim($input)) {
        $error = ML("is leeg","is empty");
      }
    ';
    $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
    for ($i=1; $i<1000; $i++) {
      if ($workflow["meta"][$i]) {
        $fieldname = $workflow["meta"][$i];
        if ($workflow["shortcutmeta"][$fieldname] == "indep") {
          $metalist[$fieldname] = $fieldname;
        } elseif ($workflow["shortcutmeta"][$fieldname] == "copy") {
          $metalist[$fieldname] = $fieldname;
          $readonlylist[$fieldname] = $fieldname;
        }
      }
    }
    foreach ($metalist as $field) {
      $metaspec["fields"]["meta_".$field] = $allfields[$field];
    }

    $formtemplate  = '<body bgcolor=#f0f0f0><br><center><table>' .
                     '<tr><td><font face="arial" size=2><b>{{{shorttitle}}}:</b></font></td>
                          <td><font face="arial" size=2>'. ($workflow["shortcutmeta"]["shorttitle"] == "indep" ? '[[[shorttitle]]]' : '(((shorttitle)))') . '</font></td></tr>';
    if ($workflow["shortcutmeta"]["longtitle"]) $formtemplate .=
                     '<tr><td><font face="arial" size=2><b>{{{longtitle}}}:</b></font></td>
                          <td><font face="arial" size=2>'. ($workflow["shortcutmeta"]["longtitle"] == "indep" ? '[[[longtitle]]]' : '(((longtitle)))') . '</font></td></tr>';
    foreach ($metalist as $field) {
      $formtemplate .= '<tr><td><font face="arial" size=2><b>{{{meta_'.$field.'}}}:</b></font></td><td>';
      if ($readonlylist[$field]) {
        $formtemplate .= '<font face="arial" size=2>(((meta_'.$field.')))</font>';
      } else {
        $formtemplate .= '[[[meta_'.$field.']]]';
      }
      $formtemplate .= '</td></tr>';
    }
    $formtemplate .= '</table>';

    if (!SHIELD_HasObjectRight ($sgn, $source_id, "edit")) {
      $formtemplate = str_replace ("[[[", "&nbsp;<font face=\"arial\" size=2>(((", $formtemplate);
      $formtemplate = str_replace ("]]]", ")))</font>", $formtemplate);
      $formtemplate .= '<tr><td colspan=2>&nbsp;</td></tr><tr><td colspan=2><center>[[[OK]]]</center></td></tr></table></body>';
    } else {
      $formtemplate .= '<tr><td colspan=2>&nbsp;</td></tr>' .
                        '<tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>' .
                        '</table></center></body>';
    }

    $form = array();
    $form["input"]["sgn"] = $sgn;
    $form["input"]["id"] = $shortcut_id;
    $form["input"]["key"] = $shortcut_id;
    $form["input"]["table"] = "ims_{$sgn}_objects";
    $form["title"] = $title;
    $form["metaspec"] = $metaspec;
    $form["formtemplate"] = $formtemplate;
    $form["precode"] = '$data = MB_Load ($input["table"], $input["key"]); $data["shorttitle"] = $data["base_shorttitle"];';
    $form["postcode"] = '
      $shortcut = &MB_Ref($input["table"], $input["key"]);
      $old = $shortcut;
      $visualchange = false;
      foreach ($data as $name => $value) {
        if ($old[$name] != $value) {
          $shortcut[$name] = $value;
          $change = array();
          if(substr($name,0,5)=="meta_") {
            $change["old"] = FORMS_ShowValue($old[$name], substr($name,5), $old, $old);
            if ($change["old"] != $old[$name]) $change["oldinternal"] = $old[$name]; // store internal value if different from visual value
            $change["new"] = FORMS_ShowValue($shortcut[$name], substr($name,5), $shortcut, $shortcut);
            if ($change["new"] != $shortcut[$name]) $change["newinternal"] = $shortcut[$name];
          } else {
            $change["old"] = $old[$name];
            $change["new"] = $shortcut[$name];
          }
          if ($change["old"] != $change["new"]) $visualchange = true;
          $changes[] = $change;
        }
      }
      if ($shortcut["shorttitle"]) $shortcut["base_shorttitle"] = $shortcut["shorttitle"];
      if ($changes) {
        global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
        $time = time();
        $guid = N_GUID();
        if ($myconfig[IMS_SuperGroupName()]["dmshistoryhidepropertychangewithsamevisualvalue"] == "yes" && !$visualchange) {
          $last = end($shortcut["history"]);
          $time = $last["when"];
          $shortcut["history"][$guid]["truetime"] = time();
        }
        $shortcut["history"][$guid]["when"] = $time;
        $shortcut["history"][$guid]["author"] = SHIELD_CurrentUser(IMS_SuperGroupName());
        $shortcut["history"][$guid]["type"] = "properties";
        $shortcut["history"][$guid]["server"] = N_CurrentServer ();
        $shortcut["history"][$guid]["http_host"] = getenv("HTTP_HOST");
        $shortcut["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
        $shortcut["history"][$guid]["server_addr"] = $SERVER_ADDR;
        $shortcut["history"][$guid]["changes"] = $changes;

        // Reindex document (to incorporate shortcut metadata)
        uuse("search");
        $source = MB_Load($input["table"], $shortcut["source"]);
        SEARCH_AddPreviewDocumentToDMSIndex(IMS_SuperGroupName(), $shortcut["source"]);
        if ($source["published"] == "yes") SEARCH_AddDocumentToDMSIndex(IMS_SuperGroupName(), $shortcut["source"]);

      }

    ';

    $url = FORMS_URL($form);
    return $url;

  } else {
    return false;
  }
}

function DMSUIF_FixMouseoverForIndependentShortcut($sgn, $shortcut_id, $source_id, $mouseovertext) {
  if (FILES_IsIndependentShortcut($sgn, $shortcut_id)) {
    $shortcut = MB_Load("ims_{$sgn}_objects", $shortcut_id);
    $source = MB_Load("ims_{$sgn}_objects", $source_id);
    $workflow = SHIELD_AccessWorkflow ($sgn, $shortcut["workflow"]);
    if ($source["longtitle"] && $workflow["shortcutmeta"]["longtitle"] == "indep") {
      $source_longtitle = str_replace('"', '_', $source["longtitle"]);
      if ($shortcut["longtitle"]) {
        $shortcut_title = str_replace('"', '_', $shortcut["longtitle"]);
      } else {
        $shortcut_title = str_replace('"', '_', $shortcut["shorttitle"]);
      }
      $mouseovertext = str_replace("&quot;".$source_longtitle."&quot;", "&quot;".$shortcut_title."&quot;", $mouseovertext);
    }
    if (!$source["longtitle"] && ($workflow["shortcutmeta"]["shorttitle"] == "indep" || $workflow["shortcutmeta"]["longtitle"] == "indep")) {
      $source_shorttitle = str_replace('"', '_', $source["shorttitle"]);
      if ($shortcut["longtitle"]) {
        $shortcut_title = str_replace('"', '_', $shortcut["longtitle"]);
      } else {
        $shortcut_title = str_replace('"', '_', $shortcut["shorttitle"]);
      }
      $mouseovertext = str_replace("&quot;".$source_shorttitle."&quot;", "&quot;".$shortcut_title."&quot;", $mouseovertext);
    }
  }
  return $mouseovertext;
}

function DMSUIF_ShowWizards($sgn, $wizards, $block, &$number) {
  // $wizards is a list of $wizards (should already be filtered on submode and code_condition), in $id => $url format.
  uuse("flex");
  $result = "";
  $number = 0;
  foreach ($wizards as $id => $url) {
    $specs = FLEX_LoadLocalComponent($sgn, "dmswizard", $id);

    if (!$specs["block"]) $specs["block"] = "wizards";
    if ($specs["block"] == $block || ($block == "wizards_active" && $specs["block"] == "wizards")) {
      $number++;
      if ($block == "workflow") {
        // no icon
        $result .= '&nbsp;<a class="ims_navigation" title="'.$specs["title"].'" href="'.$url.'">'.$specs["name"].'</a><br>';
      } else {
        // icon
        $iconurl = trim($specs["iconurl"]);
        if (!$iconurl) $iconurl = "/ufc/rapid/openims/wand.gif";
        if ($block == "newdoc") {
          // in table
          $result .= '<table border=0 cellspacing=0 cellpadding=0><tr><td>';
          $image = '<img border=0 alt="" src="'.$iconurl.'">';
          $result .= '<a title="'.$specs["title"].'" href="'.$url.'">' . $image . '</a>';
          $result .= '</td><td>&nbsp;</td><td>';
          $result .= '<a class="ims_navigation" title="'.$specs["title"].'" href="'.$url.'">'.$specs["name"].'</a>';
          $result .= '</td></tr></table>';
        } else {
          // default
          $result .= "<a class=\"ims_navigation\" title=\"".$specs["title"]."\" href=\"$url\"><img border=0 src=\"".$iconurl."\" height=16 width=16>&nbsp;".$specs["name"]."</a><br>";
        }
      }
    }
  }
  return $result;
}

// Autotableview is a special kind of DMS view
function DMSUIF_Autotableview_Content($autotableviewid) {
  N_Debug("DMSUIF_Autotableview_Content: autotableviewid = $autotableviewid");
  global $currentobject, $tab, $myconfig;

  if (!$autotableviewid) N_Die("autotableviewid unknown");

  $sgn = IMS_SuperGroupName();
  $specs = FLEX_LoadLocalComponent($sgn, "autotableview", $autotableviewid);

  if ($myconfig[$sgn]["multifile"]=="yes") {
    uuse ("dhtml");
    uuse ("multi");
    $selectcounter = 0 + MULTI_Selected();
    $content .= DHTML_EmbedJavaScript ("selectcounter=$selectcounter;");
  }

  $table = N_Eval('$table = "' . $specs["table"] . '";', array("supergroupname" => $sgn), "table");
  N_Debug("DMSUIF_Autotableview_Content: table = $table");

  $aspecs = DMSUIF_Autotableview_TableSpecs($autotableviewid, $table);

  $content .= TABLES_Auto_NoReload($aspecs);

  //$content = "autotableview content<br/>";
  //$content .= "<pre>" . htmlentities(print_r($specs, 1)) . "</pre>";

  // Show the wizards
  $style = SKIN_WizardbarStyle();
  $content .= '<style type="text/css">' . $style . '</style>';
  $content .= '<div class="ims_wizardbarwrap">';
  $content .= '<div class="ims_wizardbar">';

  $separator = $specs["wizardseparator"];
  if (!$separator) $separator = " ";

  // All wizards that are active when documents are selected, will appear at the end, not between other wizards.
  foreach ($specs["wizards"] as $wizardid) {
    if ($wizardid == "separator") {
      $stat .= '<span class="ims_wizardseparator"><span class="ims_wizardseparator_content">|</span></span>&nbsp;';
    } else {
      $wizardspecs = FLEX_LoadLocalComponent($sgn, "autotablewizard", $wizardid);
      if ($wizardspecs["show"] == "active" && !$currentobject) continue;
      $result = N_Eval($wizardspecs["code_condition"], array("currentobject" => $currentobject, "result" => true), "result");
      $iconurl = trim($wizardspecs["iconurl"]);
      $iconurl = N_Eval($wizardspecs["code_iconurl"], array("currentobject" => $currentobject, "result" => $iconurl), "result");
      if (!$iconurl) $iconurl = "/ufc/rapid/openims/wand.gif";
      if ($result) {
        $url = N_Eval($wizardspecs["code_urlgenerator"], array("currentobject" => $currentobject), "result");

// 20100930 KvD ICON URL tussen dubbele quotes: maak er tekst van
        if (preg_match('/^".+?"$/', $iconurl))
          $image = substr($iconurl,1, -1);
        else
          $image = '<img border=0 alt="" src="'.$iconurl.'">';
///
        $link = '<a href="'.$url.'" class="ims_navigation" alt="'.N_HtmlEntities($wizardspecs["name"]).'" title="'.N_HtmlEntities($wizardspecs["name"]).'">';
        if ($specs["showwizards"] == "name") {
          $link .= N_HtmlEntities($wizardspecs["name"]);
        } elseif ($specs["showwizards"] == "both") {
          $link .= $image . '&nbsp;' . N_HtmlEntities($wizardspecs["name"]);
        } else { // icons only
          $link .= $image;
        }
        $link .= '</a>' . $separator;

        if ($wizardspecs["show"] == "selected") {
          $dyn .= $link;
        } else {
          $stat .= $link;
        }
      }
    }
  }
  // pr10 - 242, workflowstappen als documentassistenten
  if ($currentobject and $specs["showworkflowoptions"])
  {
    uuse ("workflow");
    $object = MB_Ref("ims_" . $sgn . "_objects", $currentobject);
    $opt_arr = WORKFLOW_Options($sgn, $currentobject, $object);
    if ($opt_arr)
    {
      foreach ($opt_arr as $url => $option)
      {
        $link = '<a href="' . $url . '" class="ims_navigation" alt="'. $option . '" title="'. $option . '" >' . $option . '</a>' . $separator;
        $stat .= $link;
      }
    }
    if ($myconfig[$sgn]["multiapprove"] == "yes")
    {
      uuse("multiapprove");
      $ma_arr = MULTIAPPROVE_Choices();
      if ($ma_arr)
      {
        foreach ($ma_arr as $url => $option)
        {
          $link = '<a href="' . $url . '" class="ims_navigation" alt="'. $option . '" title="'. $option . '" >' . $option . '</a>' . $separator;
          $stat .= $link;
        }
      }
    }
  }
  // pr10-242

  $content .= $stat;

  if ($myconfig[$sgn]["multifile"]=="yes" && $dyn) {
    $content .= DHTML_EmbedJavascript ("stat = '';");
    $content .= DHTML_EmbedJavascript ("dyn = '".str_replace ("'", "\\'", $dyn)."';");
    if (MULTI_Selected()) {
      $content .= DHTML_DynamicObject ($dyn, "multifileoptions", true);
    } else {
      $content .= DHTML_DynamicObject ("", "multifileoptions", true);
    }
    // Not sure why, but without this font-tage, Firefox changes the face to default / Timen New Roman when the selectcounter is updated by innerHtml ?!?
    // beacause this always follows, no stripping of last separator is needed anymore JH
    $content .= '<font face="arial">'. DHTML_DynamicObject ($selectcounter, "selectcounter", true) . '</font>&nbsp;' . ML("document(en) geselecteerd", "document(s) selected");
  }
  else // only here where there is no counter and no dyn assistents stripping is necessary
  {
     $content = substr($content, 0, -strlen($separator));
  }

  $content .= '</div></div>';

  // Show the tabs
  if ($currentobject) {
    $firsttab = false; // first (allowed) tab has not yet been encountered
    $illegaltab = false; // trying to view a tab which is not allowed (for this document)
    $style = SKIN_TabStyle();
    $content .= '<style type="text/css">' . $style . '</style>';
// geen lelijke vierkantjes links indien in DMS View een document geselecteerd is CORE-24 in andere browsers dan IE
    if (!N_IE())
      $content .= '<a name="tabcontainer" style="display:none;">';
    else
      $content .= '<a name="tabcontainer">';
    $content .= '<div class="ims_tabcontainer">';
    $content .= '<ul class="ims_tablist">';
    foreach ($specs["tabs"] as $tabid) {
      $tabspecs = FLEX_LoadLocalComponent($sgn, "autotabletab", $tabid);
      $result = N_Eval($tabspecs["code_condition"], array("currentobject" => $currentobject, "result" => true), "result");
      if ($result) {
        if (!$tab) $tab = $tabid; // if there is no active tab, make the first tab active
        $url = N_AlterUrl(N_MyFullUrl(), "tab", $tabid);
        $url = N_AlterUrl(N_AlterUrl($url, "reloaddata"), "errordata"); // Kill the reloaddata (from forms) when the users switches between tabs
        if ($tabspecs["scroll"]) $url .= '#tabcontainer';
        if ($tabid == $tab) {
          $content .= '<li' . ($firsttab ? '' : ' class="first"') . '><a class="active" title="'.N_HtmlEntities($tabspecs["title"]).'" href="'.$url.'">'.N_HtmlEntities($tabspecs["name"]).'</a></li>';
        } else {
          $content .= '<li' . ($firsttab ? '' : ' class="first"') . '><a title="'.N_HtmlEntities($tabspecs["title"]).'" href="'.$url.'">'.N_HtmlEntities($tabspecs["name"]).'</a></li>';
        }
        if (!$firsttab) $firsttab = $tabid;
      }
      if (!$result && $tabid == $tab) $illegaltab = true;
    }
    $content .= '</ul>';
    $content .= '</div>';

    if ($illegaltab) {
      if ($firsttab) {
        N_Redirect(N_AlterUrl(N_MyFullUrl(), "tab", $firsttab));
      } else {
        N_Redirect(N_AlterUrl(N_MyFullUrl(), "tab", ""));
      }
    }

    // Show the content of the active tab
    if ($tab) {
      $tabspecs = FLEX_LoadLocalComponent($sgn, "autotabletab", $tab);
      $tabcontent = N_Eval($tabspecs["code_contentgenerator"], array("currentobject" => $currentobject), "content");
      $content .= '<div class="ims_tabcontentwrap1"><div class="ims_tabcontentwrap2"><div class="ims_tabcontent">' . $tabcontent . '</div></div></div>';
    }
  }



  $currentobject = ""; // otherwise, some blocks on the right side of the screen become active
  return $content;
}

function DMSUIF_Autotableview_TableSpecs($autotableviewid, $table) {
  global $currentobject;

  $sgn = IMS_SuperGroupName();
  $flexspecs = FLEX_LoadLocalComponent($sgn, "autotableview", $autotableviewid);

  $maxlen = $flexspecs["tablerows"];
  if (!intval($maxlen)) $maxlen = 10;


  $sort_default_col = $flexspecs["defaultsortcol"];
  if (!intval($sort_default_col)) $sort_default_col = 1;
  $sort_default_dir = $flexspecs["defaultsortdir"];
  if (!$sort_default_dir) $sort_default_dir = "u";
  $specs_tablespecs["sort_default_col"] = $sort_default_col;
  $specs_tablespecs["sort_default_dir"] = $sort_default_dir;

  $specs_select = N_Eval($flexspecs["code_select"], array(), "select");
  $specs_slowselect = N_Eval($flexspecs["code_select"], array(), "slowselect");

  $col = 0;
  foreach ($flexspecs["columns"] as $columnspec) {
    $col++;

    if ($columnspec["nolink"]) {
      $specs_content[] = $columnspec["contentexp"];
    } else {
      $specs_content[] =
        'global $currentobject;' .
        ($flexspecs["mouseover"] ? $flexspecs["mouseover"] . ';' : '$title = "";') .
        'echo "<a href=\"" .  N_AlterUrl(N_MyFullUrl(), "currentobject", $key) . "\" title=\"$title\" class=\"" . ($key == $currentobject ? "ims_active" : "ims_navigation" ) . "\">";' .
        $columnspec["contentexp"];
        'echo "</a>";';
    }

    $specs_sort[] = $columnspec["sortexp"];
    if (!$columnspec["sortexp"]) $columnspec["sorttype"] == "none";

    if ($columnspec["filterexp"]) {
      $specs_colfilterexp[] = $columnspec["filterexp"];
    } else {
      // We can't use the default (empty) colfilter, because if we do that, all the extra hyperlink stuff
      // that we add to the content (including checking if we are dealing with the "currentobject", a non-deterministic check)
      // will become part of the filter.
      // So we create our own filter, based on the contentexpression.
      // While the contentexppression can be a list of echo-statements, the filterexp should be a single expression.
      // We use N_Eval to change it into a single expression
      $specs_colfilterexp[] = '
        N_Eval(\'ob_start(); ' .
                ($columnspec["sortexp"]
                   ? '$sortvalue = '.addcslashes($columnspec["sortexp"], "\\'") .';'
                   : '') .
                addcslashes($columnspec["contentexp"], "\\'") . ';
                $text = " ".ob_get_contents(); ob_end_clean();\',
           array("record" => $record, "object" => $object, "key" => $key),
           "text")';
    }

    if ($columnspec["nohead"]) {
      $specs_tableheads[] = '';
    } else {
      $specs_tableheads[] = $columnspec["tablehead"];
    }

    $specs_colfiltertype[$col] = $columnspec["filtertype"];

    if (!$columnspec["sorttype"]) {
      $specs_tablespecs["sort_$col"] = "auto";
    } elseif ($columnspec["sorttype"] != "none") {
      $specs_tablespecs["sort_$col"] = $columnspec["sorttype"];
    }

    if (!$specs_tablespecs["sort_$col"] && $specs_tablespecs["sort_default_col"] == $col) {
      // Find the first sortable column
      $col2 = 0;
      foreach ($flexspecs["columns"] as $columnspec2) {
        $col2++;
        if ($columnspec2["sorttype"] != "none" && $columnspec2["sortexp"]) {
          $firstsortablecol = $col2;
          break;
        }
      }
      $specs_tablespecs["sort_default_col"] = $firstsortablecol;
      N_Debug("DMSUIF_Autotableview_TableSpecs: changed default_sort_col to $firstsortablecol");
    }
  }

  $specs = array (
    "name" => "autotableview",
    "style" => "ims",
    "maxlen" => $maxlen,
    "colfilter" => "yes",
    "colfilterexp" => $specs_colfilterexp,
    "colfiltertype" => $specs_colfiltertype,
    "leftcolfilter" => ($flexspecs["leftcolfilter"] ? "yes" : "no"),
    "table" => $table,
    "select" => $specs_select,
    "slowselect" => $specs_slowselect,
    "tablespecs" => $specs_tablespecs,
    "tableheads" => $specs_tableheads,
    "sort" => $specs_sort,
    "content" => $specs_content,
  );
  if (!isset($_REQUEST["tblblk_autotableview"])) $specs["gotoobject"] = $currentobject;

  if ($flexspecs["code_extratablespecs"]) $specs = N_Eval($flexspecs["code_extratablespecs"], array("specs" => $specs), "specs");

  N_Debug("DMSUIF_Autotableview_TableSpecs: autotable specs = <pre>" . htmlspecialchars(print_r($specs, 1)) . "</pre>");

  return $specs;

}

function DMSUIF_CheckDmsViewCondition() {
  global $dmsviewid;

  $list = FLEX_LocalComponents (IMS_SuperGroupName(), "dmsview");
  $result = true;
  eval ($list[$dmsviewid]["code_condition"]);
  if (!$result)  SHIELD_Unauthorized();
}

function DMSUIF_LastModifiedStatus($object_id) {
  /* Add this to your form:
   * Do this only for forms that you generate "inplace" using FORMS_GenerateSuperForm.
   * Do'nt do this for popup forms (because for popup forms, you shouldn't save the status until in the precode)
   *
   *   uuse("dmsuif");
   *   $form["input"]["modstatus"] = DMSUIF_LastModifiedStatus($object_id)
   *   $form["postcode"] = 'uuse("dmsuif"); DMSUIF_CheckLastModifiedStatus($input["modstatus"]);' . $form["postcode"];
   */
  $object = MB_Load("ims_".IMS_SuperGroupName()."_objects", $object_id);
  end($object["history"]);
  $last = current($object["history"]);
  return $object_id . "#" . $last["author"] . "#" . $last["when"];
}

function DMSUIF_CheckLastModifiedStatus($savedstatus, $noerror = false) {
  // Check if a document has been changed since the form was shown.
  // If yes, throw a FORMS_Showerror (default) or return false (if $noerror = true)
  // If no, return true.

  $savedstatus_arr = explode("#", $savedstatus);
  $object = MB_Load("ims_".IMS_SuperGroupName()."_objects", $savedstatus_arr[0]);
  $savedauthor = $savedstatus_arr[1];
  $savedtime = $savedstatus_arr[2];

  end($object["history"]);
  $last = current($object["history"]);
  if ($last["when"] == $savedtime && $last["author"] == $savedauthor) {
    return true;
  } elseif ($last["author"] == SHIELD_CurrentUser(IMS_SuperGroupName())) {
    return true;
  } else {
    if ($noerror) return false;

    $user = MB_Load("shield_".IMS_SuperGroupName()."_users", $last["author"]);
    $username = $user["name"];

    if (!$username) $username = $last["author"];
    FORMS_ShowError(ML("Fout", "Error"),
           '<font face="arial" size=2>' .
           ML("Uw invoer is niet verwerkt omdat het document sinds het samenstellen van het formulier gewijzigd is.",
              "Your input has not been processed because the document has changed since the form was composed.") . "<br/>" .
           ML("Het document is gewijzigd door %1 om %2", "The document has been changed by %1 at %2", $username, N_VisualDate($last["when"], true))
           . "</font>", "no");
  }

}

function DMSUIF_BulkUploadProgressForm($process_id, $count) {
  $form = array();
  $form["input"]["process_id"] = $process_id;
  $form["input"]["total"] = $count;

  $form["precode"] = '
    uuse("terra");
    uuse("webgen");
    uuse("dhtml");

    echo "<html>";
    echo "<head>";
    echo "<title>".ML("Importeer documenten in het DMS", "Import document into the DMS")."</title>";
    echo "<body bgcolor=#f0f2ff>";
    echo "<table cellspacing=0 cellpadding=1 border=0 id=\"MeasureMe\"><tr><td valign=top>";

    echo "<font face=\'arial\' size=\'2\'>".
         ML("De %1 documenten worden in het DMS ge&iuml;mporteerd.", "The %1 documents are imported into the DMS.", "<b>". $input["total"]."</b>");
    echo "<br><br>";

    echo DHTML_DynamicObject ("", "webgenstatus");

    echo "</td>".
         "<td><img src=\'/openims/blank.gif\' border=\'0\' height=\'90\' width=\'1\'></td>".
         "</tr>".
         "<tr>".
         "<td colspan=2><img src=\'/openims/blank.gif\' border=\'0\' height=\'1\' width=\'410\'></td>".
         "</tr>".
         "</table>";

    echo DHTML_EmbedJavaScript (DHTML_SetDynamicObject ("webgenstatus", WEBGEN_ShowStatus($input["total"],0)));
    echo DHTML_EmbedJavaScript (DHTML_PerfectSize ());
    N_Flush (-1);

    $laststep = 0;
    while (TERRA_Active ($input["process_id"])) {
      $process = TERRA_LoadState($input["process_id"]);
      $nextstep = $process["counter"];
      if (($nextstep != $laststep) || (time() - $lastmsg > 30)) {
        // Send something to the browser if there is progress to report, or if it is more than 10 seconds since the last time
        // we sent something. (Not every second because my Firefox hangs if I do that for > 1 minute.)
        echo DHTML_EmbedJavaScript (DHTML_SetDynamicObject ("webgenstatus", WEBGEN_ShowStatus($input["total"],$nextstep)));
        $lastmsg = time();
        N_Flush (-1);
      }
      N_Sleep (1000);
      $laststep = $nextstep;
    }

    $readytext = "<font face=arial size=2>".
                 ML("De import is gereed.", "Import finished.") ." ".
                 "<br><center><input type=\'button\' value=\'".ML("Sluiten","Close")."\' onclick=\"var url = window.opener.location.href+\'#\'; pos = url.indexOf(\'#\',url); url = url.substring(0,pos); window.opener.location.href = url; window.close();\"></center>".
                 "</font>";

    echo DHTML_EmbedJavaScript (DHTML_SetDynamicObject ("webgenstatus", $readytext));

    echo "</body>";
    echo "</html>";

  ';
  $form["formtemplate"] = '<img src="/openims/blank.gif" border="0" width="400" height="90">';
  return $form;
}

function DMSUIF_History($supergroupname, $object_id, $options = array()) {

   uuse("flex");
   FLEX_LoadSupportFunctions($supergroupname);
    if(function_exists("DMSUIF_History_Extra")) {
      $result = DMSUIF_History_Extra ($supergroupname, $object_id, $options);
      return $result;
    }

  global $myconfig;

  if (!isset($options["compare"])) $options["compare"] = true;
  if (!isset($options["tablestyle"])) $options["tablestyle"] = "ims";
  if (!isset($options["nobuttonsatall"])) $options["nobuttonsatall"] = false;

  $object = MB_Ref("ims_{$supergroupname}_objects", $object_id);

  $image = "/openims/view.gif";

  $NO_ElementsToBeCompared = 0;
  if ($options["compare"] && !$options["nobuttonsatall"] && FILES_Comparable ($supergroupname, $object_id)) {
     echo '<form method="put" action="/openims/openims.php">';
     echo '<input type="hidden" name="mode" value="compare">';
     echo '<input type="hidden" name="back" value="'.N_MyFullURL().'">';
     echo '<input type="hidden" name="object_id" value="'.$object_id.'">';
    foreach ($object["history"] as $key => $data) {
      if ($data["type"]=="" || $data["type"]=="edit" || $data["type"]=="new" && FILES_HistoryVersionExistsOnDisk($supergroupname,$object_id,$key)) {
        $NO_ElementsToBeCompared++;
      }
    }
  }
  T_Start ($options["tablestyle"], array("noheader"=>"yes"));

  $thehistory = $object["history"];
  if ($myconfig[$supergroupname]["reversedhistory"] == "yes") {
    $thehistory = array_reverse($object["history"],true);
    if ($NO_ElementsToBeCompared > 1) {
      T_Next();
      echo '<input type="submit" name="compare" value="'.ML("Vergelijken", "Compare").'">';
      T_NewRow();
    }
  }
  reset($thehistory);

// 20130522 KVD annotations
  $allowothers = SHIELD_HasObjectRight ($supergroupname, $object_id, "viewannotationsbyothers");
  $me = SHIELD_CurrentUser($supergroupname);
///

  while (list($key, $data)=each($thehistory)) {
    if ($data["truetime"] && $data["truetime"] != $data["when"]) continue; // do not show entries with a spoofed date ever (which date to show? how to explain that the entry has no effect on "last changed" date?)

    if ($data["type"] == "" && $data["option"] == "delete") $data["type"] = "option";

    if ($data["type"]=="signal") {

      echo "<b>".ML("Automatisch signaal","Automatic signal")."</b> ";
      echo "(".N_VisualDate ($data["when"], true).")";
      T_NewRow();
// 20130522 KVD annotations
// alleen eigen documenten of alleen van anderen als dat mag
    } else if ($data["type"]=="annotate" && ($data["author"] == $me || $allowothers)) {
      echo "<b>".ML("Annotatie","Annotation")."</b> ";
      echo ML("door","by")." <b>".SHIELD_UserName ($supergroupname, $data["author"])."</b> ";
      echo "(".N_VisualDate ($data["when"], true).")";

      $flexurl =
      "/nkit/flexpaper.php?currentobject=$object_id&currentversion=$key";

      T_Next();
      echo "<a title=\"".ML("Bekijk deze versie in Flexpaper","View this version in Flexpaper")."\" href=\"$flexurl\" target=\"_blank\"><img border=0 src=\"/openims/view.gif\"></a>&nbsp;";
      T_NewRow();
///
    } else if ($data["type"]=="" || $data["type"]=="edit" || $data["type"]=="new") {
      if ($data["type"]=="new" || $data["type"]=="newpage") {
        echo "<b>".ML("Aangemaakt","Created")." ";
      } else {
        echo "<b>".ML("Aangepast","Changed")." ";
      }
      echo ML("door","by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b> ";

      echo "(".N_VisualDate ($data["when"], true).")";

      if (!$options["nobuttonsatall"]) {
        T_Next();
        if (FILES_HistoryVersionExistsOnDisk($supergroupname,$object_id,$key)) {
          $viewurl = FILES_TransViewHistoryURL ($supergroupname, $object_id, $key);
          echo "<font size=2>";
          echo "<a title=\"".ML("Bekijk deze versie","View this version")."\" href=\"$viewurl\"><img border=0 src=\"$image\"></a>&nbsp;";
          $version_id = $key;
          $restoreurl = "/openims/action.php?command=restore&sitecollection_id=$supergroupname&object_id=$object_id&version_id=$version_id&goto=".urlencode($goto);

          $form = array();
            $form["input"]["sitecollection_id"] = $supergroupname;
          $form["input"]["object_id"] = $object_id;
          $form["input"]["version_id"] = $version_id;
          $form["postcode"] = '
            IMS_RestoreObject ($input["sitecollection_id"], $input["object_id"], $input["version_id"]);
            $gotook = "closeme&refreshparent";
          ';
          $restoreurl = FORMS_URL ($form);

          if (SHIELD_HasObjectRight ($supergroupname, $object_id, "edit")) {
            echo "<a title=\"".ML("Herstel deze versie (plaats in preview)","Restore this version (put in preview)")."\" href=\"$restoreurl\"><img border=0 src=\"history_small.gif\"></a>&nbsp";
          }
          $myurl = FILES_DocHistoryURL ($supergroupname, $object_id, $key);
          $title = ML("Hyperlink naar deze versie","Hyperlink to this version");
          if ($myurl) {
            echo '<a class="ims_navigation" title="'.$title.'" href="'.$myurl.'"><img border=0 src="/ufc/rapid/openims/hyperlink.gif"></a>&nbsp';
          }
          if ($NO_ElementsToBeCompared > 1) {
            echo '<input type="checkbox" name="' . $key . '" value="true">';
          }
        } else {
          echo "&nbsp;";
        }
      }

      if ($myconfig[$supergroupname]["versioning"]=="yes") {
        T_Next();
        echo ML ("Versie", "Version")." ".IMS_Version (IMS_Supergroupname(), $object_id, $key);
      }
      T_NewRow();


      if ($data["restore"]) {
        echo ML("Versie %1 van %2 werd hersteld.", "Version %1 of %2 was restored.", IMS_Version(IMS_SuperGroupName(), $object_id, $data["fromversion"]), N_VisualDate($object["history"][$data["fromversion"]]["when"], true));
        T_NewRow();
      }

      if ($data["comment"]) {
        echo "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td><font size=\"2\">";
        echo str_replace(chr(13).chr(10),"<br>",$data["comment"]);
        echo "</td></tr></table>";
        T_NewRow();
      }

    } else if ($data["type"]=="option") {

      echo "<b>".ML("Keuze","Choice")." \"".$data["option"]."\" ";
      echo ML("door","by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b> ";
      if ($data["allocto"]) {
         echo "<b>, ".ML("toegewezen aan","allocated to")." ".SHIELD_UserName ($supergroupname, $data["allocto"])."</b> ";
      }
      echo "(".N_VisualDate ($data["when"], true).")";
      T_NewRow();
      if ($myconfig[$supergroupname]["signalcc"] == "yes" && array_key_exists("signal", $data) && $data["signal"] != "N/A") {
        // if the key "signal" does not exist: document created/edited with old core, we do NOT KNOW whether a signal was sent
        // signal == "yes" : a signal was sent
        // signal == ""    : no signal was sent, probably because the user choose not to
        // signal == "N/A" : no signal was sent, and the user never got the chance to choose to (publishing, or custom code)
        T_Start("", array("noheader" => true));
        echo "&nbsp;&nbsp;&nbsp;" . ML("Signaal", "Signal");
        T_Next();
        if ($data["signal"] == "yes") {
          echo ML("ja", "yes");
          echo " (" . N_HtmlEntities($data["signalto"]) . ")";
          if ($data["signalcc"]) {
            T_NewRow();
            echo "&nbsp;&nbsp;&nbsp;" . ML("Cc", "Cc");
            T_Next();
            $cctext = array();
            foreach ($data["signalcc"] as $user_id => $email) {
              $username = MB_Fetch("shield_".IMS_SuperGroupName()."_users", $user_id, "name");
              if (!$username) $username = $user_id;
              $cctext[] = N_HtmlEntities($username) . " (" . N_HtmlEntities($email) . ")";
            }
            echo implode(", ", $cctext);
          }
        } else {
          echo ML("nee", "no");
        }
        T_NewRow();
        if ($data["comment"]) {
          echo "&nbsp;&nbsp;&nbsp;" .  ML("Opmerking", "Comment");
          T_Next();
          echo str_replace(chr(13).chr(10),"<br>",$data["comment"]);
        }
        TE_End();
        T_NewRow();
      } else {
        if ($data["comment"]) {
          echo "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td><font size=\"2\">";
          echo str_replace(chr(13).chr(10),"<br>",$data["comment"]);
          echo "</td></tr></table>";
          T_NewRow();
        }
      }
      if ($myconfig[$supergroupname]["multiapprove"] == "yes" and $data["multiapprove"]) {
        echo "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td><font size=\"2\">";
        echo ML("Parallelle controle deelnemers", "Parallel approval participants") . ":<br>";
        foreach ($data["multiapprove"] as $mauser => $dummy)
          if (($mauser != "remind") and ($mauser != "enddate"))
            echo SHIELD_UserName($supergroupname, $mauser) . "<br>";
        $enddate = $data["multiapprove"]["enddate"];
        if ($enddate) echo ML("Verloopdatum: ", "Enddate: ") . N_VisualDate($enddate) . "<br>";
        $reminddate = $data["multiapprove"]["remind"];
        if ($reminddate) echo ML("Rappeldatum: ", "Reminder: ") . N_VisualDate($reminddate) . "<br>";
        echo "</td></tr></table>";
        T_NewRow();
        $cnt = 0;
        foreach ($data["multiapprove"] as $mauser => $machoice)
          if (($machoice != "x") and ($mauser != "remind") and ($mauser != "enddate"))
            ++$cnt;
        if ($cnt)
        {
          reset ($data["multiapprove"]);
          echo "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td><font size=\"2\">";
          foreach ($data["multiapprove"] as $mauser => $machoice)
            if (($machoice != "x") and ($mauser != "remind") and ($mauser != "enddate")) {
              echo ML("Parallelle controle stap \"", "Parallel approval step \"") . $machoice . ML("\" door ", "\" by ") . SHIELD_UserName($supergroupname, $mauser) . "<br>";
              if($data["multiapprovecomment"][$mauser]) {
                echo ML("Toelichting:", "Explanation");
                echo "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td><font size=\"2\">";
                echo str_replace(chr(13).chr(10),"<br>",$data["multiapprovecomment"][$mauser]);
                echo "</td></tr></table>";
              }
          }
          echo "</td></tr></table>";
          T_NewRow();
        }
      }
    } else if ($data["type"]=="move") {
      echo "<b>".ML("Document","Document")." ". ML("verplaatst door","moved by")." ".SHIELD_UserName ($supergroupname, $data["author"])." </b>";
      echo "(".N_VisualDate ($data["when"], true).")";
      T_NewRow();
      if ($data["from"]) {
        echo "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td><font size=\"2\">";
        echo ML("Van","From").": ".str_replace(chr(13).chr(10),"<br>",$data["from"]);
        echo "<br>";
        echo ML("Naar","To").": ".str_replace(chr(13).chr(10),"<br>",$data["to"]);
        if ($data["remark"]) {
          echo "<br>";
          echo ML("Opmerking","Remark").": ".str_replace(chr(13).chr(10),"<br>",$data["remark"]);
        }
        echo "</td></tr></table>";
        T_NewRow();
      }
    } else if ($data["type"]=="properties") {
      // history entry for "change properties

      if ($myconfig[$supergroupname]["dmshistoryhidepropertychangewithsamevisualvalue"]) {
        $showchanges = array();
        foreach ($data["changes"] as $dummy => $change) {
          if ($change["old"] != $change["new"]) $showchanges[] = $change;
        }
      } else {
        $showchanges = $data["changes"];
      }

      if ($showchanges || !$data["changes"]) { // if there are no changes at all, the code creating the entry was legacy/defective (?) and something probably did change, we just dont know what
        echo "<b>".ML("Eigenschappen veranderd door %1","Properties changed by %1", SHIELD_UserName ($supergroupname, $data["author"]))."</b> ";
        echo "(".N_VisualDate ($data["when"], true).")";
        T_NewRow();
        if ($data["changes"]) {
          echo "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td><font size=\"2\"><b>" . ML("Veld","Field") . "</b></td><td ><font size=\"2\"><b>" .
            ML("Oud","Old") . "</b></td><td ><font size=\"2\"><b>" . ML("Nieuw","New") . "</b></td></tr>";
          foreach($showchanges as $dummy=>$change) {
            $old = $change["old"];
            $new = $change["new"];
            if($change["title"]=="shorttitle") $change["title"]=ML("Naam","Name");
            if($change["title"]=="longtitle") $change["title"]=ML("Omschrijving","Description");
            if($change["title"]=="workflow") {
              $change["title"]="Workflow";
            }
            echo "<tr><td>&nbsp;&nbsp;&nbsp;</td><td><font size=\"2\">" . $change["title"] . "&nbsp;</td><td ><font size=\"2\">" .
            $old . "&nbsp;</td><td ><font size=\"2\">" . $new . "&nbsp;</td></tr>";
          }
        echo "</table>";
        }
        T_NewRow();
      }
    } else if ($data["type"]=="custom") {
      echo "<b>".$data["name"]."";
      if ($data["author"]) {
        echo " " . ML("door","by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b>";
      } else {
        echo "</b>";
      }
      if ($data["allocto"]) {
       echo "<b>, ".ML("toegewezen aan","allocated to")." ".SHIELD_UserName ($supergroupname, $data["allocto"])."</b> ";
      }
      echo " (".N_VisualDate ($data["when"], true).")";
      T_NewRow();
      if ($data["comment"]) {
        echo "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td><font size=\"2\">";
        echo str_replace(chr(13).chr(10),"<br>",$data["comment"]);
        echo "</td></tr></table>";
        T_NewRow();
      }
    } else if ($data["type"]=="forcedpublish") {
      echo "<b>".ML("Keuze","Choice")." \"". ML("Geforceerd publiceren","Forced publish") ."\" ";
      echo ML("door","by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b> ";
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
    } else if ($data["type"]=="bulkreallocation") {
      echo "<b>".ML("Keuze","Choice")." \"". ML("Bulkherallocatie","Bulk re-allocation") ."\" ";
      echo ML("door","by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b> ";
      echo "<b>, ".ML("van","from")." ".SHIELD_UserName ($supergroupname, $data["old"]). " " .

           ML("naar","to")." ".SHIELD_UserName ($supergroupname, $data["new"]) . "</b> ";
      echo "(".N_VisualDate ($data["when"], true).")";
      T_NewRow();
      if ($data["comment"]) {
        echo "<table><tr><td>&nbsp;&nbsp;&nbsp;</td><td><font size=\"2\">";
        echo str_replace(chr(13).chr(10),"<br>",$data["comment"]);
        echo "</td></tr></table>";
        T_NewRow();
      }
    } else if ($data["type"]=="revoked") {
      if ($data["revoketype"]=="restorelastpublished") {
        echo "<b>".ML("Teruggetrokken (terug naar laatst gepubliceerde versie)","Revoked (return to published version)") . " ";
      } elseif ($data["revoketype"]=="unpublish") {
        echo "<b>".ML("Teruggetrokken (verwijder de gepubliceerde versie)","Revoked (remove published document)") . " ";
      } else {
        echo "<b>".ML("Teruggetrokken","Revoked")." ";
      }
      echo ML("door","by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b>";
      echo "(".N_VisualDate ($data["when"], true).")";
      T_NewRow();
    } else if ($data["type"]=="doctopdf") {
        echo "<b>".ML("Document opgeslagen als PDF ", "Document saved as PDF ");
      echo ML("door","by")." ".SHIELD_UserName ($supergroupname, $data["author"])."</b>";
      echo " (".N_VisualDate ($data["when"], true).")";
      T_NewRow();
    }
  }

  if ($myconfig[$supergroupname]["reversedhistory"] != "yes") {
    if ($NO_ElementsToBeCompared > 1) {
      T_Next();
      echo '<input type="submit" name="compare" value="'.ML("Vergelijken", "Compare").'">';
      T_NewRow();
    }
  }

  $result = TS_End();
  if ($NO_ElementsToBeCompared > 1) $result .= '</form>';
  return $result;

}


// 20110927 KvD Plaatjes en peedeeefjes bekijken
function DMSUIF_Previewshortcut($sgn, $key, $ob, $fancybox=false)
{
  $content = "";
  // 20120418 KvD CORE-35 mag bekijken ?
  if (SHIELD_HasObjectRight($sgn, $key, "view") || 
     (SHIELD_HasObjectRight($sgn, $key, "viewpub") && $ob["published"]=="yes")) {  
  ///
   $fancybox = ($fancybox=="fancybox");
   if ($ob["filename"]) {
    $extn = strrchr($ob["filename"], ".");
    if ($extn && strlen($extn) < strlen($ob["filename"])) 
      $extn = strtolower(substr($extn,1));
    else
      $extn = "";        
  }
  uuse( "word" ); global $myconfig;   
  if (in_array($extn, array("pdf", "gif", "jpeg", "jpg", "png" , "tif" , "tiff" ) ) || ($myconfig[$sgn]["dmspreview_conversion"]=="yes" && WORD_isConvertableToPDFwithCurrentSettings( $sgn , $extn )) ) {
//  if (in_array($extn, array("pdf"))) {
    $doclink = FILES_DocPreviewURL($sgn, $key);
    
    if (!$GLOBALS["g_fancyboxloaded"]) {
      
      $content .= DHTML_PdfclickJs();
      DHTML_Requirejquery();    

      if ($fancybox) {
        DHTML_RequireFancybox();
       $content .= <<<fancyparams
<script language="javascript">
jQuery(document).ready(function() 
{
  $("a.fancybox").attr('rel', 'gallery').fancybox( { transitionIn : "elastic" , transitionOut : "elastic", titlePosition : "outside" , changeSpeed : 100 , 'width' : '50%', 'height' : '50%', showNavArrows : false, 'type' : 'iframe' } );
} );
</script>

fancyparams;
      } else {
        $content .= DMSUIF_pdfpreview_clickAway();
      }
      $GLOBALS["g_fancyboxloaded"] = true;
    }

    if (N_IE())
      $margin_top = "-20px";
    else
      $margin_top = "-5px";

    $divstyle = "font-weight: bold; font-style: italic; display: inline; margin-bottom: -5px; margin-top: $margin_top; padding-bottom: 5px; padding-top: 5px;";
    if ($fancybox) {
/*
      if ($extn=="pdf") {
        $url = FILES_DocPreviewURL($sgn, $key);
    //$img = htmlentities($img);
      } else */ {
        $url = "/ufc/thumb/$sgn/$key/400/400/";
      }
      $title = $ob["shorttitle"];
      $content .= "<a style=\"display:block; float: right; $divstyle\" href=\"$url\" class=\"fancybox\" title=\":$title\"><img border=\"0\" src=\"/openims/magnifier.gif\" alt=\"preview\" /></a>"; 
    } else {
      $onclick = 'onClick="hidePdfPreview(); XshowPdfPreview(\'pdfpreview_'.$key.'\'); "';
      $link = "&nbsp;<img border=\"0\" src=\"/openims/magnifier.gif\" onmouseover=\"this.src='/openims/magnifier-closed.gif'\";  onmouseout=\"this.src='/openims/magnifier.gif'\"; alt=\"preview\" />"; 
// PLAATJE WERKT NIET  IS NIET KLIKBAAR !!!!
//$link = "&nbsp;" . ML("(Voorbeeld)", "Preview");
      $content .= DHTML_PdfclickPreview($sgn, $key, false, false, false);
      $title = $ob["shorttitle"];
      $content .= <<<divje
<div style="float:right; $divstyle" class="imagezoomin" id="a_$key" $onclick"
      title="$title">$link</div>
divje;
    } // if ($fancybox)
   }
  } // if (mag bekijken)
  return $content;
}
// 20120607 KvD AEQ-137 Werkstroom verwijderen in Terra

 
function DMSUIF_TERRA_DeleteWorkflow($sgn, $oldworkflow, $newworkflow,  $prompt=false,  $doitreally=false)
{
  $userid = SHIELD_CurrentUser($sgn);

  uuse("terra");
  if ($newworkflow . "" && $oldworkflow . "")
  {
    $table = "ims_".$sgn."_objects";
    $specs = array();
 
    $specs["title"] = "changeworkflows";

    $specs["input"]["sgn"] = $sgn;
    $specs["input"]["table"] = $table;
    $specs["tables"] = array($table);
    $specs["input"]["oldworkflow"] = $oldworkflow;
    $specs["input"]["newworkflow"] = $newworkflow;
    $specs["list"] = $list;
    $specs["input"]["doitreally"] = $doitreally;
    $specs["data"]["count"] = 0;

    $specs["step_code"] = '

      $newworkflow = $input["newworkflow"];
      $oldworkflow = $input["oldworkflow"];
      $sgn = $input["sgn"];
      $table = $input["table"];
      $doitreally = $input["doitreally"];
      $record = MB_Load($table, $key);

      if ($record["workflow"] == $oldworkflow) {
        if (!$doitreally) {
          $test = "TEST: ";
        } else  {
          $test = "";
          $record["workflow"] = $newworkflow;
          MB_Save($table, $key, $record);
        }    
        N_LOG("History workflows", "${test}key: [".$key."] from [$oldworkflow] to [$newworkflow]"); 
      }

    ';

    if ($doitreally) {
      MB_Delete("shield_${sgn}_workflows", $oldworkflow);
      N_Log("History workflows", "deleted " . $oldworkflow . " by " . SHIELD_CurrentUsername()); 
    }

    TERRA_Multi_Tables ($specs);
    if ($prompt) {
      FORMS_Showerror(ML("Werkstroom verwijderen", "Delete workflow"), 
         ML("Alle documenten worden in de achtergrond van werkstroom veranderd", "All documents will be changed from workflow in the background"), "no");
    }
  }
}

?>