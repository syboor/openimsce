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
uuse ("tables");



function SYS_32KFIXDIR ($dir)
{
  $dir = N_CleanPath ($dir);
  $list = array();
  $d = dir("$dir");
  while(false !== ($entry = $d->read())) {
    if (strlen($entry)>30) array_push ($list, $entry);
  }
  $d->close();  
  foreach ($list as $l) {
    $old = $dir."/".$l;
    $new = $dir."/".substr ($l, 0, 3)."/32k/".substr($l,3);
    N_MKDIR ($dir."/".substr ($l, 0, 3));
    N_MKDIR ($dir."/".substr ($l, 0, 3)."/32k");
    `mv $old $new`;
  }
}

function SYS_CopySitecollection ($newsgn, $existingsgn, $sites, $locals)
{

// SYS_NukeSitecollection ("acconet2_sites", "please");
// SYS_CopySitecollection ("acconet2_sites", "acconet_sites", array ("acconet2_com", "acconet_com", "acconet2.dev.openims.com"), array ("local_acconet_document_counters"=>"local_acconet2_document_counters"));

  echo "Warning #1: Custom metabase tables NOT containing the name of the sitecollection will NOT be duplicated.<br>";
  echo "Warning #2: Batch processes are duplicated as well.<br>";
  echo "Warning #3: You have to manually generate search indexes and high traffic caches after the copy has completed.<br>";
  echo "Warning #4: Copying takes some time. Do as little as possble until copying has been completed.<br>";
  echo "Warning #5: Copying might have unpredictable side efects, especially when custom code is involved.<br>";
  echo "Warning #6: Many hyperlinks (e.g. those in MS-Word documents) will NOT be corrected.<br>";  
  N_Flush();

  global $fromlist, $tolist;
  $fromlist = array ($existingsgn);
  $tolist = array ($newsgn);  
  $ims_sitecollection = array();
  $ims_sitecollection["description"] = $newsgn;
  for ($i=1; $i<=count($sites)/3; $i++) {
    $fromsite = $sites[3*$i-2];
    $tosite = $sites[3*$i-3];
    array_push ($fromlist, $fromsite);
    array_push ($tolist, $tosite);
    array_push ($fromlist, $tosite."_homepage");
    array_push ($tolist, $fromsite."_homepage");
    $todomain = $sites[3*$i-1];
    $fromimssite = MB_Load ("ims_sites", $fromsite);
    foreach ($fromimssite["domains"] as $fromdomain => $dummy) {
      array_push ($fromlist, $fromdomain);
      array_push ($tolist, $todomain);
    }
  }
  foreach ($locals as $fromtable => $totable) {
    array_push ($fromlist, $fromtable);
    array_push ($tolist, $totable);
  }

  foreach ($locals as $fromtable => $totable) {
    if (MB_TableExists ($fromtable)) { // allow use of $locals for search and replace purposes
      echo "<nobr>COPY TABLE $fromtable TO $totable</nobr><br>"; N_Flush();    
      MB_CopyTable ($totable, $fromtable, '
        global $fromlist, $tolist;
        $llobject2 = N_XML2Object (str_ireplace ($fromlist, $tolist, N_Object2XML ($llobject)));
        if ($llobject!=$llobject2) {
          echo "<nobr>COPY AND ALTER RECORD $key FROM TABLE '.$newtable.'</nobr><br>"; N_Flush();
          $llobject = $llobject2;
        }
      ');
    }
  }

  for ($i=1; $i<=count($sites)/3; $i++) {
    $fromsite = $sites[3*$i-2];
    $tosite = $sites[3*$i-3];
    $todomain = $sites[3*$i-1];
    echo "<nobr>COPY SITE $fromsite TO $tosite ($todomain)</nobr><br>"; N_Flush();
    echo "CREATE ims_sitesrecord<br>"; N_Flush();
    $ims_sitecollection["sites"][$tosite]["dummy"] = "dummy";
    $ims_site= array();
    $ims_site["sitecollection"] = $newsgn;
    $ims_site["description"] = $todomain;
    $ims_site["domains"][$todomain]["dummy"] = "dummy";
    $fromimssite = MB_Load ("ims_sites", $fromsite);
    $ims_site["homepage"] = $fromimssite["homepage"];
    MB_Save ("ims_sites", $tosite, $ims_site);
    MB_Flush();

    echo "<nobr>COPY DIR html::$fromsite TO html::$tosite</nobr><br>"; N_Flush();
    N_CopyDir ("html::$tosite", "html::$fromsite", '
      if (strpos ($destfile, ".php")) {
        global $fromlist, $tolist;
        $content = N_ReadFile ($destfile);
        $content2 = str_ireplace ($fromlist, $tolist, $content);
        if ($content2!=$content) echo "<nobr>ALTER $destfile</nobr><br>"; N_Flush();
        N_WriteFile ($destfile, $content2);
      }
    ');
  }

  echo "COPY Trees<br>"; N_Flush();
  MB_Save ("ims_trees", $newsgn."_discussions", MB_Load ("ims_trees", $existingsgn."_discussions"));
  MB_Save ("ims_trees", $newsgn."_documents", MB_Load ("ims_trees", $existingsgn."_documents"));
  MB_Save ("ims_trees", $newsgn."_hyperlinks", MB_Load ("ims_trees", $existingsgn."_hyperlinks"));
  MB_Save ("ims_trees", $newsgn."_issues", MB_Load ("ims_trees", $existingsgn."_issues"));
  echo "CREATE ims_sitecollections record<br>"; N_Flush();
  MB_Save ("ims_sitecollections", $newsgn, $ims_sitecollection);
  MB_Flush();

  $tables = MB_AllTables ();
  foreach ($tables as $table) {
    if ((MB_MUL_Engine($table)!="VIRTUAL") && (strpos (" ".$table, "_".$existingsgn."_"))) {
      $newtable = str_ireplace ("_".$existingsgn."_", "_".$newsgn."_", $table);
      echo "<nobr>COPY AND ALTER TABLE $table TO $newtable</nobr><br>"; N_Flush();
      MB_CopyTable ($newtable, $table, '
         global $fromlist, $tolist;
         $llobject2 = N_XML2Object (str_ireplace ($fromlist, $tolist, N_Object2XML ($llobject)));
         if ($llobject!=$llobject2) {
           echo "<nobr>COPY AND ALTER RECORD $key FROM TABLE '.$newtable.'</nobr><br>"; N_Flush();
           $llobject = $llobject2;
         }
      ');
    }
  }    

  echo "COPY AND ALTER siteconfig.php<br>"; N_Flush();
  $file = N_ReadFile ("html::/config/$existingsgn/siteconfig.php");
  $file = str_ireplace ($fromlist, $tolist, $file);
  N_WriteFile ("html::/config/$newsgn/siteconfig.php", $file); 

  echo "COPY AND ALTER custom components<br>"; N_Flush();  
  $list = glob (N_CleanPath ("html::/config/$existingsgn/flex/*ubd"));
  foreach ($list as $dummy => $file) {
    $newfile = str_ireplace ($existingsgn, $newsgn, $file);
    $specs = unserialize (N_ReadFile ($file));
    $specs2 = N_XML2Object (str_ireplace ($fromlist, $tolist, N_Object2XML ($specs)));
    if (strpos (" ".$file, "lowlevel")) {
      echo "<nobr>SKIP $file</nobr><br>"; N_Flush();   
    } else {
      if ($specs2!=$specs) {
        echo "<nobr>COPY + ALTER $file TO $newfile</nobr><br>"; N_Flush();   
      } else {
        echo "<nobr>COPY $file TO $newfile</nobr><br>"; N_Flush();
      }
      N_WriteFile ($newfile, serialize ($specs2));
    }
  }

  echo "<nobr>COPY DIR html::$existingsgn TO html::$newsgn</nobr><br>"; N_Flush();
  N_CopyDir ("html::$newsgn", "html::$existingsgn", '
    if (strpos ($destfile, ".html")) {
      global $fromlist, $tolist;
      $content = N_ReadFile ($destfile);
      $content2 = str_ireplace ($fromlist, $tolist, $content);
      if ($content2!=$content) echo "<nobr>ALTER $destfile</nobr><br>"; N_Flush();
      N_WriteFile ($destfile, $content2);
    }
  ');

  echo "REPAIR flex cache<br>"; N_Flush();  
  FLEX_RepairCache();

  echo "CREATE shield_supergroups record<br>"; N_Flush();
  MB_Save ("shield_supergroups", $newsgn, MB_Load ("shield_supergroups", $existingsgn));

  $fields = MB_Load ("ims_fields", $existingsgn);
  $fields2 = N_XML2Object (str_ireplace ($fromlist, $tolist, N_Object2XML ($fields)));
  if ($fields!=$fields2) {
    echo "COPY + ALTER Fields<br>"; N_Flush();
  } else {
    echo "COPY Fields<br>"; N_Flush();
  }
  MB_Save ("ims_fields", $newsgn, $fields2);
  MB_Flush();

  echo "CLEAN UP OBSOLETE PAGES FROM NOT (LONGER) EXISTING SITES<br>"; N_Flush();
  $sgn = $newsgn;
  $keys = MB_AllKeys ("ims_sites");
  $validsites = array();
  foreach ($keys as $key => $dummy) {
    $obj = MB_Load ("ims_sites", $key);
    if ($obj["sitecollection"]==$sgn) {
      array_push ($validsites, $key);
    }
  }

//  $keys = MB_AllKeys ("ims_".$sgn."_objects");
//  foreach ($keys as $key => $dummy) {
//    $obj = MB_Load ("ims_".$sgn."_objects", $key);
//    if ($obj["objecttype"]=="webpage") {
//      if (!in_array (IMS_Object2Site ($sgn, $key), $validsites)) {
//        MB_Delete ("ims_".$sgn."_objects", $key);
//        echo "DELETE page $key from ims_".$sgn."_objects (not existent site)<BR>"; N_Flush();
//      }
//    }  
//  }
//  MB_Flush();

  echo "SITECOLLECTION COPY COMPLETED<br>";
}

function SYS_NukeSitecollection ($sgn, $please)
{
  if ($please!="please") N_DIE ("no please");
  
  //check if sitecollection exists
  $sgnTable = 'ims_sitecollections';
  $sgnRec = MB_Load($sgnTable, $sgn);
  
  if(!$sgnRec)
  {
    N_DIE("Sitecollection does not exist");
  }
  else
  {
  	echo "<nobr>DELETE DIR html::$sgn</nobr><br>"; N_Flush(); 
  	MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure("html::$sgn");
  	rmdir (N_CleanPath ("html::$sgn"));

  	echo "<nobr>DELETE DIR html::/config/$sgn</nobr><br>"; N_Flush(); 
  	MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure("html::/config/$sgn");
  	rmdir (N_CleanPath ("html::/config/$sgn"));

  	echo "<nobr>DELETE Trees</nobr><br>"; N_Flush(); 
  	MB_Delete ("ims_trees", $sgn."_discussions");
  	MB_Delete ("ims_trees", $sgn."_documents");
  	MB_Delete ("ims_trees", $sgn."_hyperlinks");
  	MB_Delete ("ims_trees", $sgn."_issues");

  	$result = MB_Query ("ims_sites", "\$record[\"sitecollection\"]==\"$sgn\"", true);
  	foreach ($result as $site => $dummy)
  	{
  		if($site != "" && $site != NULL)
  		{
	    	echo "<nobr>DELETE DIR html::$site</nobr><br>"; N_Flush(); 
    		MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure("html::$site");
    		rmdir (N_CleanPath ("html::$site"));

    		echo "<nobr>DELETE ims_sites record $site</nobr><br>"; N_Flush(); 
    		MB_Delete ("ims_sites", $site);
    	}
  	}

	  $tables = MB_AllTables ();
  	foreach ($tables as $table) {
    	if ((MB_MUL_Engine($table)!="VIRTUAL") && (strpos (" ".$table, "_".$sgn."_"))) {
	      echo "<nobr>DELETE TABLE $table</nobr><br>"; N_Flush(); 
      	MB_DeleteTable ($table);
    	}
  	}

  	echo "<nobr>DELETE ims_sitecollections record $sgn</nobr><br>"; N_Flush(); 
  	MB_Delete ("ims_sitecollections", $sgn);

	  echo "DELETE shield_supergroups record<br>"; N_Flush();
	  MB_Delete ("shield_supergroups", $sgn);
		
	  echo "REPAIR flex cache<br>"; N_Flush();  
	  FLEX_RepairCache();
	
	  echo "SITECOLLECTION DESTRUCTION COMPLETED<br>";
  }
}


function SYS_ShowStatus ($status="")
{
  echo DHTML_DynamicObject ($status, "status");
}

function SYS_UpdateStatus ($status, $delay=-1)
{
  global $last;
  $e = N_Elapsed() * 1000;
  if ($last + $delay < $e) {
    $last = $e;
    echo DHTML_EmbedJavascript (DHTML_SetDynamicObject ("status", $status));
    N_Flush("N");
  }
}



function SYS_Hex2Readable ($input)
{
  // Strip off the 0 that MB_Comparable adds at the end (only if we also see the * at the beginning)
  if (substr($input, 0, 1) == "*" && substr($input, -1) == "0") $input = substr($input, 1, -1);

  $input .= "  ";
  for ($i=0; $i<=strlen($input); $i++) {
    $c = substr ($input, $i, 1);
    $cp = substr ($input, $i+1, 1);
    if (strpos (" 0123456789abcdef", $c) && strpos (" 0123456789abcdef", $cp)) {
      $output .= pack("H*", $c.$cp); 
      $i++;
    } else {
      $output .= $c;
    }
  }
  return $output;
}

function SYS_AnalyzeCode ($all)
{
  foreach ($all as $loc => $code) {
    $count = preg_match_all ("#function[ \n\r]+([a-z0-9_]*)[ \n\r]*[(]#i", $code, $matches);
    if ($count) {
      foreach ($matches[1] as $fn) {
        echo "Function <b>$fn</b> defined in: $loc<br>";
        foreach ($all as $loc2 => $code2) {
          if (($loc!=$loc2) && (N_KeepBefore ($loc, "-") == N_KeepBefore ($loc2, "-"))) {
            if (preg_match ("#[^a-z0-9_]".$fn."[^a-z0-9_]#i", $code2)) {
              echo "   called from: $loc2<br>";
            }
          }
        }        
        N_Flush();
      }
    }
  }
}

function SYS_CollectCode ()
{
  $sitecols = MB_Query ("ims_sitecollections");
  $types = FLEX_Types ();
  foreach ($sitecols as $sgn) {
    foreach ($types as $type => $specs) {
      $list = FLEX_LocalComponents ($sgn, $type);
      foreach ($list as $id => $raw) {
        $name = $raw["name"];
        foreach ($specs["fields"] as $fieldname => $dummy) {
          if (substr ($fieldname, 0, 5)=="code_" || $fieldname=="code") {
            echo ". ";
            $all["$sgn - $type - $name - $fieldname"] = $raw[$fieldname];
          }
        }
      }
    }
    N_Flush();
  }
  return $all;
}

function SYS_CodeChecker ()
{
  echo "Locating all custom source code...<br>";
  $all = SYS_CollectCode ();
  echo "<br>Analyzing all custom source code...<br>";
  SYS_AnalyzeCode ($all);
}



function SYS_CleanupShield ($sgn, $repair=false)
{
/* TEST CODE:
uuse ("sys");
SYS_CleanupShield ("demo_sites", false);

if (false) {

$user = &MB_Ref ("shield_demo_sites_users", "nevries");
$user["groups"]["non_existing_group"] = "x";
$user["groups_global"]["non_existing_group"] = "x";
$user["groups_secsec"]["non_existing_secsec"] = "x";
$user["groups_secsec"]["6fc6dd457151b828c1d5cee3483ecb62"]["non_existing_group"] = "x";

$group = &MB_Ref ("shield_demo_sites_groups", "administrators");
$group["users"]["non_existing_user"] = "x";
$group["users_global"]["non_existing_user"] = "x";
$group["users_secsec"]["non_existing_secsec"] = "x";
$group["users_secsec"]["6fc6dd457151b828c1d5cee3483ecb62"]["non_existing_user"] = "x";

$workflow = &MB_Ref ("shield_demo_sites_workflows", "adhesie");
N_EO ($workflow);
$workflow["rights"]["delete"]["non_existing_group"] = "x";
$workflow["1"]["edit"]["non_existing_group"] = "x";
$workflow["1"]["changestage"]["#Aanbieden voor evaluatie"]["non_existing_group"] = "x";
$workflow["1"]["stageafteredit"] = 2; // 99 for test, 2 for repair
$workflow["1"]["#Aanbieden voor evaluatie"] = 2; // 99 for test, 2 for repair

} 
*/
  SHIELD_InitDescriptions();
  if ($repair) {
    echo "Errors will be repaired<br>";
  } else {
    echo "Errors will NOT be repaired (reporting errors only)<br>";
  }   
  echo "Users: ".count ($users = MB_Query ("shield_".$sgn."_users"))."<br>"; N_Flush();
  echo "Groups: ".count ($groups = MB_Query ("shield_".$sgn."_groups"))."<br>"; N_Flush();
  $groups["everyone"] = "everyone";
  $groups["authenticated"] = "authenticated";
  $groups["assigned"] = "assigned";
  echo "Workflows: ".count ($workflows = MB_Query ("shield_".$sgn."_workflows"))."<br>"; N_Flush();
  echo "Security sections: ".count ($allsecsecs = SHIELD_AllSecuritySections ($sgn))."<br>"; N_Flush();
  $conntable = "shield_{$sgn}_localsecurity_connections";
  if (MB_MUL_TableSize($conntable) > 1000) {
    $connections = MB_AllKeys($conntable);
    $nconnections = count($connections) . " (est.)"; // Estimate is probably accurate, because this function uses MB_REP_Delete (not MB_Delete), and nowhere else does OpenIMS any deletions on this table.
  } else {
    $connections = MB_Query ($conntable);
    $nconnections = count($connections);
  }
  echo "Folders with group-group-connections: $nconnections<br>"; N_Flush();
  foreach ($users as $user_id) {
    $userobj = &MB_Ref ("shield_".$sgn."_users", $user_id);
    $user = $userobj; 
    echo "Checking user ".$user["name"]." ($user_id): <b><br>";
    foreach ($user["groups"] as $group_id => $dummy) {
      if (!$groups[$group_id]) {
        if ($repair) unset ($userobj["groups"][$group_id]);
        echo "-removed standard group '$group_id'<br>";
      }
    }
    foreach ($user["groups_global"] as $group_id => $dummy) {
      if (!$groups[$group_id]) {
        if ($repair) unset ($userobj["groups_global"][$group_id]);
        echo "-removed global group '$group_id'<br>";
      }
    }
    foreach ($user["groups_secsec"] as $secsec => $specs) {
      if (!$allsecsecs[$secsec]) {
        if ($repair) unset ($userobj["groups_secsec"][$secsec]);
        echo "-removed secsec '$secsec'<br>";
      } else {
        foreach ($specs as $group_id => $dummy) {
          if (!$groups[$group_id]) {
            if ($repair) unset ($userobj["groups_secsec"][$secsec][$group_id]);
            echo "-removed group '$group_id' from secsec '$secsec'<br>";
          }
        }
      }
    }
    echo "</b>";
    N_Flush();
  }
  foreach ($groups as $group_id) 
  {
    $groupobj = &MB_Ref ("shield_".$sgn."_groups", $group_id);
    $group = $groupobj; 
    echo "Checking group ".$group["name"]." ($group_id): <b><br>";
    foreach ($group["users"] as $user_id => $dummy) {
      if (($user_id != strtolower($user_id)) && $users[strtolower($user_id)]) { 
        // Repair upper case foreign keys in group table.
        // NOT by converting to lower case, but by accepting shield_$sgn_users as authorative
        $user = MB_Ref("shield_".$sgn."_users", strtolower($user_id));
        if ($user["groups"][$group_id] == "x") {
          if ($repair) { 
            unset($groupobj["users"][$user_id]);
            $groupobj["users"][strtolower($user_id)] = "x";
          }
          echo "-removed foreign key '$user_id' from shield_".$sgn."_groups and created '".strtolower($user_id)."'<br>";
        } else {
          if ($repair) unset($groupobj["users"][$user_id]);
          echo "-removed foreign key '$user_id' from shield_".$sgn."_groups; no new key created<br>";
        }
      } else { // standard behaviour
        if (!$users[$user_id]) {
          if ($repair) unset ($groupobj["users"][$user_id]);
          echo "-removed standard user '$user_id'<br>";
        }
      }
    }
    foreach ($group["users_global"] as $user_id => $dummy) {
      if (!$users[$user_id]) {
        if ($repair) unset ($groupobj["users_global"][$user_id]);
        echo "-removed global user '$user_id'<br>";
      }
    }
    foreach ($group["users_secsec"] as $secsec => $specs) {
      if (!$allsecsecs[$secsec]) {
        echo "-removed secsec '$secsec'<br>";
        if ($repair) unset ($groupobj["users_secsec"][$secsec]);
      } else {
        foreach ($specs as $user_id => $dummy) {
          if (!$users[$user_id]) {
            if ($repair) unset ($groupobj["users_secsec"][$secsec][$user_id]);
            echo "-removed user '$user_id' from secsec '$secsec'<br>";
          }
        }
      }
    }
    echo "</b>";
    N_Flush();
  }
  foreach ($workflows as $workflow_id)  {
    $workflowobj = &MB_Ref ("shield_".$sgn."_workflows", $workflow_id);
    $workflow = $workflowobj;
    echo "Checking workflow ".$workflow["name"]." ($workflow_id): <b><br>";

    for ($i=1; $i<=$workflow["stages"]; $i++) { 
      $stagedata = $workflow[$i];
      if ($stagedata["stageafteredit"] < 1 || $stagedata["stageafteredit"] > $workflow["stages"]) {
        if ($repair) $workflowobj[$i]["stageafteredit"] = 1;
        echo "-reset after edit stage for $i from '".$stagedata["stageafteredit"]."' to 1<br>";
      }
      foreach ($stagedata["edit"] as $group_id => $dummy) {
        if (!$groups[$group_id]) {
          if ($repair) unset ($workflowobj[$i]["edit"][$group_id]);
          echo "-removed group '$group_id' from right 'edit' stage $i<br>"; 
        }
      }
      foreach ($stagedata as $option => $specs) {
        if (substr ($option, 0, 1)=="#") {
          if ($specs < 1 || $specs > $workflow["stages"]) {
            if ($repair) $workflowobj[$i][$option] = 1;
            echo "-reset after $i '$option' stage from '$specs' to 1<br>"; 
          }
        }
      }
      foreach ($stagedata["changestage"] as $option => $specs) {
        foreach ($specs as $group_id => $dummy) {
          if (!$groups[$group_id]) {
            if ($repair) unset ($workflowobj[$i]["changestage"][$option][$group_id]);
            echo "-removed group '$group_id' from stage $i option '$option'<br>"; 
          }
        }
      }
    }

    global $objectrights;
    foreach ($objectrights as $obr => $dummy) {
      foreach ($workflow["rights"][$obr] as $group_id => $dummy) {
        if (!$groups[$group_id]) {
          if ($repair) unset ($workflowobj["rights"][$obr][$group_id]);
          echo "-removed group '$group_id' from right '$obr'<br>";
        }
      }
    }

// N_EO ($workflow);
    echo "</b>";
    N_Flush();
  }

  // Check folders with group-group connections on them
  if (!$allsecsecs) return; // I dont trust this...
  foreach ($connections as $folder_id) {
    $connection = MB_REP_Load($conntable, $folder_id);
    if (!$connection) continue; // needed because of MB_AllKeys earlier on
    echo "Checking folder {$folder_id}: <br/>";
    if (!$allsecsecs[$folder_id]) {
      if ($repair) MB_REP_Delete("shield_{$sgn}_localsecurity_connections", $folder_id);
      echo "<b>-removed group-group-connection (folder no longer has local security)- '$folder_id'</b><br>";
    }
  }

}

function SYS_LogAnalyseElapsed ($datefrom, $dateto="") // e.g. 20050914 // qqq
{
  if (!$dateto) $dateto = $datefrom;
  if ($dateto < 30000000) $dateto = N_BuildDate (substr ($dateto,0,4),substr ($dateto,4,2),substr ($dateto,6,2));
  if ($datefrom < 30000000) $datefrom = N_BuildDate (substr ($datefrom,0,4),substr ($datefrom,4,2),substr ($datefrom,6,2));
  $content = 'DATE, TIME, TYPE, MS, USER, SPECS'.chr(13).chr(10);
  foreach (array ("404", "back", "other", "sites", "uif") as $list) {
    for ($date = $datefrom; $date <= $dateto; $date+=24*3600) {
      $name = N_Date ("Ymd", $date);
      $all = explode (chr(13).chr(10), preg_replace('/.W:[0-9]*ms.R:[0-9]*ms.S:[0-9]*ms/i', '', N_ReadFile ($q="html::/tmp/logging/elapsed_$list/$name.log")));
      foreach ($all as $line) if ($line)
      {
        preg_match ("#([0-9]*) \\[[0-9:]*\\] ([0-9]*)ms [(]([^)]*)[)]#", $line, $matches);
        $c[$name.((1000*$matches[1])+N_Random(990))] = ($name).",".(0+$matches[1]).",".$list.",".(0+$matches[2]).",".$matches[3].",\"$line\"".chr(13).chr(10);
      }
    }
  }
  ksort ($c);
  foreach ($c as $dummy => $line) {
    $content .= $line;
  }
  $content = str_replace (",", ";", $content);
  $content = str_replace (".", ",", $content);
  $asset = ASSETS_StoreImage ("local_sites", "elapsedlist".N_Date ("Ymd", $datefrom)."_".N_Date ("Ymd", $dateto).".csv", $content);
  echo "<a href=\"$asset\">Download CSV (elapsed)</a><br>";
}

function SYS_LogAnalyseServerstatus ($datefrom, $dateto="")
{
  if (!$dateto) $dateto = $datefrom;
  $content = 'DATE, TIME, CPU, OS LOAD, APACHE LOAD, SPECS'.chr(13).chr(10);
  for ($date = $datefrom; $date <= $dateto; $date+=24*3600) {
    $name = N_Date ("Ymd", $date);
    $all = explode (chr(13).chr(10), N_ReadFile ("html::/tmp/logging/serverstatus/$name.log"));
    foreach ($all as $line) if ($line)
    {
      preg_match ("#([0-9]*) [^ ]* [0-9]* .*cpu: ([0-9]*)% .*osload: ([0-9.]*) aload: ([0-9.]*) #", $line, $matches);
      $content .= ($name).",".(0+$matches[1]).",".(0+$matches[2]).",".(0+$matches[3]).",".(0+$matches[4]).",\"$line\"".chr(13).chr(10);
    }
  }  
  $content = str_replace (",", ";", $content);
  $content = str_replace (".", ",", $content);
// echo N_XML2HTML ($content);
  $asset = ASSETS_StoreImage ("local_sites", "serverstatuslist".N_Date ("Ymd", $datefrom)."_".N_Date ("Ymd", $dateto).".csv", $content);
  echo "<a href=\"$asset\">Download CSV (serverstatus)</a><br>";
}

function SYS_Queue ($queue_id, $amount=10, $start=-1)
{
  $dir = getenv("DOCUMENT_ROOT")."/tmp/queue_".$queue_id;
  $inctr = N_ReadFile ($dir."/inctr.dat");
  $outctr = N_ReadFile ($dir."/outctr.dat");  
  echo "QUEUE: $queue_id<br>";
  echo "INCTR: $inctr<br>"; 
  echo "OUTCTR: $outctr<br>"; 
  if ($start==-1) $start = $outctr; else $start--;
  for ($i=0; $i<$amount; $i++) {
    $loc = $start+1+$i;
    if ($loc > $inctr) break;
    if ($loc <= $outctr) echo "<font color=\"ff0000\">";
    echo "PEEK (".($loc).") ";
    $elem = unserialize (N_ReadFile ($dir."/".$loc.".dat"));
    if (is_string ($elem)) {
      echo $elem."<br>";
    } else {
      N_EO ($elem);
    }    
    if ($loc <= $outctr) echo "</font>";
  }
}

global $code;
if (strpos (N_MyBareURL(), "eval.php") && !strpos ($code, "SYS_") && !strpos ($code, "STATS_")) {
  SYS_Help ();
  echo "<br>";
}

function SYS_Help ()
{
  T_Start("black");
  echo "Function"; T_Next(); echo "Description"; T_NewRow();
  echo 'SYS_Help()'; T_Next(); echo "Show this amazing list"; T_NewRow();
  echo 'SYS_AnalyzeSearchLogs ()'; T_Next(); echo "Determine average, median, etc."; T_NewRow();
  echo 'SYS_UsersWithChangeAccess ($verbose=false, $sgn="")'; T_Next(); echo "Determine amount of needed OpenIMS licences"; T_NewRow();
  echo 'SYS_ShowDocConvertLog ($when=0)'; T_Next(); echo "Combine and show DMS and conversion server logs"; T_NewRow();
  echo 'SYS_AnalyzeSphinx2 ($index="")'; T_Next(); echo "Show list of indexes or metadata of an index"; T_NewRow();
  echo 'SYS_SPACE ($dir="html::")'; T_Next(); echo "Analyze total disk usage (slow!)"; T_NewRow();
  echo 'SYS_Benchmark()'; T_Next(); echo "Benchmark server"; T_NewRow();
  
  echo 'SYS_CleanupShield ($sgn, $repair=false)'; T_Next(); echo "Check (and repair) small flaws in security data (e.g. removed security sections)"; T_NewRow();
  echo 'SYS_CheckBackup ($filename="last", $checktable="")'; T_Next(); echo "Compare database to backup"; T_NewRow();
  echo 'SYS_ShowBackupObject ($filename="last", $checktable, $key)'; T_Next(); echo "Show (echo) single object from backup"; T_NewRow();
  echo 'SYS_RestoreObject ($filename, $resttable, $restkey)'; T_Next(); echo "Restore single metabase object"; T_NewRow();
  echo 'SYS_RestoreTable ($filename, $resttable)'; T_Next(); echo "Restore single metabase table"; T_NewRow();
  
  echo 'SYS_Queue ($queue_id, $amount=10, $start=-1)'; T_Next(); echo "Show contents of queue"; T_NewRow();
  echo 'SYS_LogAnalyseElapsed ($datefrom, $dateto="")'; T_Next(); echo "Convert to CSV (NL) format"; T_NewRow();
  echo 'SYS_LogAnalyseServerstatus ($datefrom, $dateto="")'; T_Next(); echo "Convert to CSV (NL) format"; T_NewRow();
  
  echo 'SYS_NukeSitecollection ($sgn, $please)'; T_Next(); echo "*** DANGER *** completely destroy sitecollection"; T_NewRow();
  echo 'SYS_CopySitecollection ($newsgn, $existingsgn, $sites, $locals)'; T_Next(); echo 'Create copy of complete sitecollection: SYS_CopySitecollection ("acconet2_sites", "acconet_sites", array ("acconet2_com", "acconet_com", "acconet2.dev.openims.com"), array ("local_acconet_document_counters"=>"local_acconet2_document_counters"));'; T_NewRow();
  
  echo 'SYS_RebuildNavigation($sgn, $page_id, $doit)'; T_Next(); echo 'Rebuild CMS Navigation recursively, starting at $page_id (fix "children", assume that "parent" is correct)'; T_NewRow();
  echo 'SYS_RebuildTree($sgn, $folder_id, $doit)'; T_Next(); echo 'Rebuild a DMS tree'; T_NewRow();
  echo 'SYS_RepairFulltextIndex($supergroupname,$ageindays = 2)'; T_Next(); echo 'Partial reindex of the SPHINX2 fulltextindex'; T_NewRow();
  echo 'SYS_RepairSphinxDates($supergroupname)'; T_Next(); echo 'Repair SPHINX2 dates for better/faster sorting of search results'; T_NewRow();
  TE_End();
}



function SYS_ShowBackupObject ($filename="last", $table, $key) 
{
  if ($filename=="last") $filename=SYS_LastBackup();
  $filename = "html::/backups/$filename";
  $filename = N_CleanPath ($filename);

  $init_code = '
    echo "SYS_ShowBackupObject($filename, {$input["table"]}, {$input["key"]}) STARTED<br/>";
    echo "Scanning backup";
    N_Flush();
  ';
  $exit_code = '
    echo "<br/>SYS_ShowBackupObject($filename, {$input["table"]}, {$input["key"]}) COMPLETED<br/>";
    N_Flush();
  ';

  $record_code = '
    if ($data["ctr"] % 10000 == 0) {
      echo "#";
      N_Flush(-1);
    }

    if ($table==$input["table"] && $key==$input["key"]) {
      echo "<br/>Table: $table, key: $key<br/>";
      T_EO($record);
      N_Flush();
    }
  ';

  $input = array("table" => $table, "key" => $key);
  SYS_WalkBackup($filename, $init_code, $table_code, $record_code, $exit_code, $input);
}

function SYS_RestoreObject ($filename, $resttable, $restkey = false)
{
  if ($restkey) {
    SYS_RestoreTable ($filename, $resttable, $restkey);
  } else {
    N_Die("SYS_RestoreObject called without a \$restkey");
  }
}

function SYS_RestoreTable ($filename="last", $resttable, $restkey = false)
{
  // Restore a / all objects in a table from a backup file.
  // Does NOT delete the current contents of the table.

  if ($filename=="last") $filename=SYS_LastBackup();
  $filename = "html::/backups/".$filename;
  $input["table"] = $resttable;
  $input["key"] = $restkey;

  $init_code = '
    if ($input["key"] === false) {
      echo "Table restore started: $filename {$input["table"]}<br/>";
    } else {
      echo "Object restore started: $filename {$input["table"]} {$input["key"]}<br/>";
    }
    echo "Scanning backup";
  ';

  $record_code = '
    if ($data["ctr"] % 10000 == 0) {
      echo "#";
      N_Flush(-1);
    }

    if ($table == $input["table"] && ($input["key"] === false || $input["key"] == $key)) {
      MB_MUL_Save($table, $key, $record);
      $data["pctr"]++;
      echo "<br/>Restore $table $key";
    }
  ';

  $exit_code = '
    $report = "Scanned records: {$data["ctr"]}, processed records: {$data["pctr"]}";
    if ($input["key"] === false) {
      echo "<br/>Table restore completed. $report <br/>";
    } else {
      echo "<br/>Object restore completed. $report <br/>";
    }
  ';

  SYS_WalkBackup($filename, $init_code, $table_code, $record_code, $exit_code, $input);
}




function SYS_LastBackup ()
{
  $list = N_QuickTree ("html::backups");
  if (is_array ($list)) {
    $auto = array();
    foreach ($list as $name => $specs)
    {
      if (strpos ($name, "auto")) $auto[$name] = $specs["age"];
    }
    asort ($auto);
  }
  foreach ($auto as $name => $dummy) {
    if (!strpos($name,".tmp")) return str_replace ("html::backups/", "", N_InternalPath ($name));
  }
}

function SYS_AutoCheckBackup ()
{
  $result["status"] = "not found";
  $filename = N_CleanPath ("html::/backups/".SYS_LastBackup());
  if (!is_file ($filename)) return $result;
  $result["status"] = "ok";
  clearstatcache ();
  $result["time"] = filemtime ($filename);
  $result["age"] = time()-$result["time"];
  $result["size"] = filesize ($filename);
  return $result;
}

function SYS_WalkBackup($filename="last", $init_code, $table_code, $record_code, $exit_code, $input = array()) 
{
  $filename = N_CleanPath ($filename);
  if (!file_exists ($filename)) N_DIE ("Backup $filename does not exist.");
  $data = N_Eval($init_code, array("input" => $input, "data" => $data, "filename" => $filename), "data");
  N_Flush(1);
  if (substr($filename, -4) == ".tgz") {
    $olddir = getcwd();
    $dir = dirname($filename);
    chdir($dir);
    global $myconfig;
    $command = $myconfig["tarcommand"]." -xvzf " . escapeshellarg($filename) . " 2>&1";
    $result = `$command`;
    chdir($olddir);
    // No "fake" the file name (because the uncompressed files have gz, not tgz, in their names
    $filename = substr($filename, 0, -4) . ".gz";  // the "base" filename of the file(s) inside the archive. Convention: this is always .gz.tmp, never .tgz.tmp
  } else {
    N_GunzipFile ($filename.".tmp", $filename);
  }
  $fp = fopen ($filename.".tmp", "r");
  if (!$fp) N_DIE ("Failed to open $filename");
  $prevtable = "";
  while (!$completed) {
    if (version_compare(phpversion(), "4.3", ">=")) {
      $line = fgets ($fp);
    } else {
      $line = fgets ($fp, 10000000);
    } 
    if ($line) {
      $data["ctr"]++;
      $obj = unserialize (base64_decode ($line));
      $table = $obj["table"];
      if ($table!=$prevtable) {
        $data = N_Eval($table_code, array("input" => $input, "data" => $data, "filename" => $filename, "table" => $table, "prevtable" => $prevtable), "data");
        $prevtable = $table;
        N_Flush (1); 
      }
      $key = $obj["key"];
      $record = $obj["data"];
      $data = N_Eval($record_code, array("input" => $input, "data" => $data, "filename" => $filename, "table" => $table, "key" => $key, "record" => $record), "data");
    }
    if (feof ($fp)) {
      // check the "next" segment
      $segment++;
      $newfilename = N_CleanPath ($filename) . ".tmp." . $segment;
      if (N_FileExists($newfilename)) {
        echo "Opening next backup segment: $segment";
        fclose($fp);
        $fp = fopen ($newfilename, "r");
      } else {
        $completed = true;
      }
    }
  }    
  fclose ($fp);
  N_DeleteFile ($filename.".tmp");
  if ($segment) {
    for ($i = 1; $i < $segment; $i++) {
      N_DeleteFile (N_CleanPath ($filename) . ".tmp." . $i);
    }
  }
  $data = N_Eval($exit_code, array("input" => $input, "data" => $data, "filename" => $filename, "prevtable" => $prevtable), "data");
  N_Flush (1);
}

function SYS_CheckBackup ($filename="last", $checktable="") 
{
  if ($filename=="last") $filename=SYS_LastBackup();
  $filename = "html::/backups/$filename";
  $filename = N_CleanPath ($filename);

  $init_code = '
    echo "SYS_CheckBackup ($filename, {$input["checktable"]}) STARTED<br>";
    $data["backuptables"] = array();
  ';
  $table_code = '
    $data["backuptables"][$table] = "x";
    $data["dbctr"] = MB_MUL_TableSize ($prevtable); 
    if ($data["tblctr"] || $data["dbctr"]) {
      if ($data["tblctr"]==$data["dbctr"]) {
        echo " ({$data["tblctr"]}:{$data["dbctr"]})";
      } else {
        echo " <b>({$data["tblctr"]}:{$data["dbctr"]}</b>)";
      }
    }
    $data["dbtotal"] += $data["dbctr"];
    $data["baktotal"] += $data["tblctr"];
    $data["tblctr"] = 0;
    echo "<br>";
    echo "Table: $table ";
  ';
  $record_code = '
    if ($table==$input["checktable"]) {
      MB_MUL_Save ("local_test_".$input["checktable"], $key, $record);
    }
    $data["tblctr"]++;
  ';
  $exit_code = '
    $dbctr = MB_MUL_TableSize ($prevtable);
    $ctr = $data["tblctr"];
    $backuptables = $data["backuptables"];
    $dbtotal = $data["dbtotal"];
    $baktotal = $data["baktotal"];
    $checktable = $input["checktable"];
    if ($ctr==$dbctr) {
      echo " ($ctr:$dbctr)";
    } else {
      echo " <b>($ctr:$dbctr)</b>";
    }
    $dbtotal += $dbctr;
    $baktotal += $ctr;
    $localtables = MB_MUL_AllTables();
    foreach ($localtables as $localtable) {
      if (!$backuptables[$localtable] && $dbctr = MB_MUL_TableSize($localtable)) {
        if ($checktable && $localtable == "local_test_$checktable") continue;
        echo "<br>Table: {$localtable} <b>(0:$dbctr)</b>";
        $dbtotal += $dbctr;
      }
    }
    echo "<br>";
    echo "<b>TOTAL BAK:$baktotal DB:$dbtotal</b><br>";
    if ($checktable != "") {
      echo "<b>TABLE COMPARE ($checktable) STARTED</b><br>";
      MB_Flush();
      $dbkeys = MB_MUL_AllKeys ($checktable);
      $bakkeys = MB_MUL_AllKeys ("local_test_$checktable");
      foreach ($dbkeys as $key) {
        if (!$bakkeys[$key]) {
          echo "<b>KEY: $key MISSING FROM BACKUP</b><br>";
        } else {
          $db = MB_MUL_Load ($checktable, $key);
          $bak = MB_MUL_Load ("local_test_$checktable", $key);
          if ($db <> $bak) {
            echo "<b>KEY: $key BACKUP DIFFERS FROM DATABASE</b><br>";
            N_EO ($db);
            N_EO ($bak);
          }
        }
      }
      foreach ($bakkeys as $key) {
        if (!$dbkeys[$key]) echo "<b>KEY: $key MISSING FROM DATABASE</b><br>";
      }
      MB_MUL_DeleteTable ("local_test_$checktable");
      echo "<b>TABLE COMPARE ({$input["checktable"]}) COMPLETED</b><br>";
    }
    echo "SYS_CheckBackup ($filename, {$input["checktable"]}) COMPLETED<br>"; 
  ';
  $input = array("checktable" => $checktable);
  SYS_WalkBackup($filename, $init_code, $table_code, $record_code, $exit_code, $input);
}

function SYS_Benchmark($test="")
{
  global $imsbuild, $mb_mysql_connect_link;  
  echo "Benchmarking OpenIMS build $imsbuild on server ".N_CurrentServer()."<br>";
  echo "Preparing...<br>"; N_Flush(1);
  MB_MUL_DeleteTable ("local_test_mysql");
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(N_CleanPath ("html::"."/tmp/bench/"));
  
  //ericd 170113 CORE-52: display output in html table
  $output = array();

  if (!$test || $test==1) {
    echo "Test #1 (MySQL create 1000 small 256 byte records)"; N_Flush(1);
    $before = N_Elapsed();
    for ($i=0; $i<1000; $i++) {
      MB_MUL_Save ("local_test_mysql", "#1 $i", str_repeat ("*", 256));
    }
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #1"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==2) {
    echo "Test #2 (MySQL create 100 large 256 kb records)"; N_Flush(1);
    $before = N_Elapsed();
    for ($i=0; $i<100; $i++) {
       MB_MUL_Save ("local_test_mysql", "#2 $i", str_repeat ("*", 256000));
    }
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #2"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==3) {
    echo "Test #3 (MySQL multi table burstmode create 1000 small 256 byte records)"; N_Flush(1);
    $before = N_Elapsed();
    for ($i=0; $i<1000; $i++) {
      $table = "local_test_mysql";
      $key = "#3 $i";
      $object = str_repeat ("*", 2);
      mysql_query ("insert into openims_".MB_MYSQL_Key2Name($table)."(thekey, thevalue) VALUES ('".mysql_escape_string($key)."', '".mysql_escape_string(serialize ($object))."');", $mb_mysql_connect_link);
    }
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #3"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==4) {
    echo "Test #4 (MySQL single table burstmode create 10000 small 256 byte records)"; N_Flush(1);
    $before = N_Elapsed();
    $sql = "insert into openims_".MB_MYSQL_Key2Name($table)."(thekey, thevalue) VALUES ";
    for ($i=0; $i<10000; $i++) {
      $table = "local_test_mysql";
      $key = "#4 $i";
      $object = str_repeat ("*", 2);
      if ($i) $sql .= ",";
      $sql .= "('".mysql_escape_string($key)."', '".mysql_escape_string(serialize ($object))."')";
    }
    MB_MYSQL_Query ($sql.";");
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #4"] = (int)(($after-$before)*1000);
  }
  
  if (!$test || $test==5) {
    echo "Test #5 (Filesys create 250 small 256 byte files)"; N_Flush(1);
    $ng = N_GUID();
    $before = N_Elapsed();
    for ($i=0; $i<250; $i++) {
      N_WriteFile ("html::tmp/bench/$i.dat_".$ng,  $ng.str_repeat ("*", 256));
    }
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #5"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==6) {
    echo "Test #6 (Filesys (re)create 250 small 256 byte files)"; N_Flush(1);
    $before = N_Elapsed();
    for ($i=0; $i<250; $i++) {
      N_WriteFile ("html::tmp/bench/$i.dat_".$ng,  $ng.str_repeat ("*", 256));
    }
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #6"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==7) {
    echo "Test #7 (Filesys create 100 256 kb files)"; N_Flush(1);
    $before = N_Elapsed();
    $ng = N_GUID();
    for ($i=0; $i<250; $i++) {
      N_WriteFile ("html::tmp/bench/$i.dat_".$ng,  $ng2.str_repeat ("*", 256000));
    }
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #7"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==8) {
    echo "Test #8 (Filesys create 100 large 2.5 Mb files)"; N_Flush(1);
    $ng = N_GUID();
    $before = N_Elapsed();
    $l = $ng.str_repeat ("*", 2560000);
    for ($i=0; $i<100; $i++) {
      N_WriteFile ("html::tmp/bench/$i.dat_".$ng, $l);
    }
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #8"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==9) {
    echo "Test #9 (10000 * small eval)"; N_Flush(1);
    $before = N_Elapsed();
    for ($i=0; $i<10000; $i++) {
      eval ('$a = $a + $a + '.$i.';');
    }
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #9"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==10) {
    echo "Test #10 (1000 * strpos 1 Mb string)"; N_Flush(1);
    $before = N_Elapsed();
    $big = str_repeat ("*", 1000000)."qqq";
    for ($i=0; $i<1000; $i++) {
      $dummy = strpos ($big, "qqq");
    }
    eval ($cmd);
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #10"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==11) {
    echo "Test #11 (10000 * N_GUID)"; N_Flush(1);
    $before = N_Elapsed();
    for ($i=0; $i<10000; $i++) N_GUID();
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #11"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==12) {
    echo "Test #12 (100 * DFC_Exists)"; N_Flush(1);
    $before = N_Elapsed();
    for ($i=0; $i<100; $i++) DFC_Exists (N_GUID());
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #12"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==13) {
    echo "Test #13 Memory copy 250 x 10MB"; N_Flush(1);
    $before = N_Elapsed();
    $blub = str_repeat ("*", 10000000);
    for ($i=0; $i<250; $i++) $blob = $i.$blub;
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #13"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==14) {
    echo "Test #14 1000 lock test"; N_Flush(1);
    $before = N_Elapsed();
    for ($i=0; $i<1000; $i++) {
      N_Lock ("locktest $i");
      N_Unlock ("locktest $i");
    }
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #14"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==15) {
    echo "Test #15 100 local http requests"; N_Flush(1);
    $before = N_Elapsed();
    for ($i=0; $i<100; $i++) {
      $ok = N_GetPage (N_ServerAddress (N_CurrentServer())."/server-status");
    }
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #15"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==16) {
    echo "Test #16 100 forced small OpenIMS compilations"; N_Flush(1);
    $before = N_Elapsed();
    $code = 'include (getenv ("DOCUMENT_ROOT")."/nkit/nkit.php");';
    for ($i=0; $i<100; $i++) {
      $ur = "tmp/".N_GUID()."_bench.php";
      N_WriteFile ("html::$ur", "<? echo '".$ur."'; $code echo '".$ur."'; ?>");
      $ok = N_GetPage (N_ServerAddress (N_CurrentServer())."/$ur");
      N_DeleteFile ("html::$ur", $code);
    }
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #16"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==17) {
    echo "Test #17 100 forced large OpenIMS compilations"; N_Flush(1);
    $before = N_Elapsed();
    $code = 'include (getenv ("DOCUMENT_ROOT")."/nkit/nkit.php");  uuse("ims");  uuse("diff");  uuse("forms");  uuse("tables");  uuse("sync");  uuse("search");
             uuse("mail");  uuse("portal");  uuse("bpms");  uuse("skins");  uuse("dmsuif");  uuse("bpmsuif");  uuse("dbmuif");  uuse("multi");  uuse("files");
             uuse("case");  uuse("black");  uuse("link");  uuse("reports");  uuse("tree");  uuse("dhtml");  uuse("shield");';
    for ($i=0; $i<100; $i++) {
      $ur = "tmp/".N_GUID()."_bench.php";
      N_WriteFile ("html::$ur", "<? echo '".$ur."'; $code echo '".$ur."'; ?>");
      $ok = N_GetPage (N_ServerAddress (N_CurrentServer())."/$ur");
      N_DeleteFile ("html::$ur", $code);
    }
    $after = N_Elapsed();
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #17"] = (int)(($after-$before)*1000);
  }

  if (!$test || $test==18) {
    echo "Test #18 100 cached(?) OpenIMS compilations"; N_Flush(1);
    $code = 'include (getenv ("DOCUMENT_ROOT")."/nkit/nkit.php");  uuse("ims");  uuse("diff");  uuse("forms");  uuse("tables");  uuse("sync");  uuse("search");
             uuse("mail");  uuse("portal");  uuse("bpms");  uuse("skins");  uuse("dmsuif");  uuse("bpmsuif");  uuse("dbmuif");  uuse("multi");  uuse("files");
             uuse("case");  uuse("black");  uuse("link");  uuse("reports");  uuse("tree");  uuse("dhtml");  uuse("shield");';
    $ur = "tmp/".N_GUID()."_bench.php";
    N_WriteFile ("html::$ur", "<? echo '".$ur."'; $code echo '".$ur."'; ?>");
    $before = N_Elapsed();
    for ($i=0; $i<100; $i++) {
      $ok = N_GetPage (N_ServerAddress (N_CurrentServer())."/$ur");
    }
    $after = N_Elapsed();
    N_DeleteFile ("html::$ur", $code);
    echo (int)(($after-$before)*1000) . " ms<br>"; N_Flush(1);
    $output["Test #18"] = (int)(($after-$before)*1000);
  }
  
  $htmlTable = '<table border="1">';
  $htmlRow1 = '<tr valign="top">';
  $htmlRow2 = '<tr valign="top">';
  foreach ($output as $key => $value) {
    $htmlRow1 .= '<td>'.$key.'</td>';
    $htmlRow2 .= '<td>'.$value.'</td>';
  }
  $htmlRow1 .= '</tr>';
  $htmlRow2 .= '</tr>';
  $htmlTable .= $htmlRow1 .$htmlRow2;
  $htmlTable .= '</table>';
  echo $htmlTable;
  
  echo "Cleaning...<br>"; N_Flush(1);
  MB_MUL_DeleteTable ("local_test_mysql");
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(N_CleanPath ("html::"."/tmp/bench/"));
  echo "Done<br>"; N_Flush(1);
}

function SYS_DMS_Export ($sgn, $folder, $thepath, $preservedate = true, $readonly = false)
{
  /* Obsolete, please use SYS_DMS_FullExport */
  $specs["input"]["sgn"] = $sgn;
  $specs["input"]["folder"] = $folder;
  $specs["input"]["thepath"] = $thepath;
  $specs["input"]["preservedate"] = $preservedate;
  $specs["input"]["readonly"] = $readonly;
  $specs["init_code"] = '
    $tables = array();
    $tables["'."ims_".$sgn."_objects".'"] = "'."ims_".$sgn."_objects".'";
    $data["handle"] = TERRA_InitMultiTable ($tables);
  ';     
  $specs["step_code"] = '
    uuse ("sys");
    $list = TERRA_MultiTablePlus ($data["handle"], 10, \'$ok=1;\'); // prevent precheck logic
    if ($list) {
      foreach ($list as $dummy => $s) {
        SYS_DMS_Export_One ($input["sgn"], $input["folder"], $input["thepath"], $s["key"], $input["preservedate"], $input["readonly"]);
      }
    } else {
      $completed = true;
    }
  ';
  TERRA_MultiStep ($specs);
}

function SYS_DMS_Export_One ($sgn, $folder, $thepath, $key, $preservedate = true, $readonly = false)
{
  /* Used by (obsolete) SYS_DMS_Export */
    $obj = MB_Load ("ims_".$sgn."_objects", $key);
    if ($obj["objecttype"]=="document" && ($obj["preview"]=="yes" || $obj["published"]=="yes")) { 
      $found = false;
      if (!$folder) $found = true;
      $dir = $obj["directory"];
      $tree = CASE_TreeRef ($sgn, $obj["directory"]);
      $max = 0;
      while ((!$found || $dir) && $max++<100) {
        if ($dir==$folder) $found = true;
        $dir = $tree["objects"][$dir]["parent"];
      }
      if ($found) {
        echo $obj["shorttitle"]."<br>";
        echo $obj["directory"]."<br>";
        $path = TREE_Path ($tree, $obj["directory"]);
        $pathtitle = "\\".preg_replace ("'[^A-Za-z0-9.]'i", "_", $path[1]["shorttitle"]); 
        for ($i=2; $i<=count($path); $i++) {
          $pathtitle .= "\\".preg_replace ("'[^A-Za-z0-9.]'i", "_", $path[$i]["shorttitle"]);
        }
        $pathtitle.="\\";
        if (strpos ($obj["directory"], ")")) {
           $case_id = substr ($obj["directory"], 0, strpos ($obj["directory"], ")")+1);
           $case = MB_Ref ("ims_".$sgn."_case_data", $case_id);
           $pathtitle = "\\".$case["shorttitle"].$pathtitle;
        }
        $pathtitle = $thepath.$pathtitle;
        $thedoctype = FILES_FileType ($sgn, $key, "preview");
        $imspath = "html::\\".$sgn."\\preview\\objects\\".$key."\\";
        $newname = preg_replace ("'[^A-Za-z0-9.]'i", "_", $obj["shorttitle"]);
        $ctr = 0;
        if ($thedoctype=="imsctn.txt") {
          $thename = $newname;
          while (file_exists (N_CleanPath ($pathtitle.$thename)) && $ctr++<1000) {
            $thename = $newname."_".$ctr;
          }
          N_CopyDir ($pathtitle.$thename, $imspath);
          N_Log ("export", "N_CopyDir (".$pathtitle.$thename.", ".$imspath.")");
        } else {
          $thename = $newname.".".$thedoctype;
          while (file_exists (N_CleanPath ($pathtitle.$thename)) && $ctr++<1000) {
            $thename = $newname."_".$ctr.".".$thedoctype;
          }
	  $destination 	= $pathtitle.$thename;
	  $source 	= $imspath.FILES_TrueFileName ($sgn, $key, "preview");
          N_CopyFile ($destination, $source);
	  if($preservedate)
	  {
		$mtime = N_FileTime($source);
		touch($destination, $mtime);
	  }
	  if($readonly)
	  {
		$rocmd = 'attrib +r "'.$destination.'"';
		$result = exec($rocmd);
	  }
          N_Log ("export", "N_CopyFile (".$pathtitle.$thename.", ".$imspath.FILES_TrueFileName ($sgn, $key, "preview").")");
        }
      }
    }
}

function SYS_ImportTextfileIntoListField() {
  // example code of importing a textfile into a list [[[field]]]
  // every line of the textfile is a seperate entry in the list

  // change the following in order to use it:
  // 1. remove if(false)
  // 2. change newfield and newtitle
  // 3. change filelocation in $list = file("/openims2/vessel2.txt");
  // 4. change $newkey algorithm 
  if(false) {
    $table = "ims_fields";
    $key = IMS_SuperGroupName();
 
    //$o = &MB_Ref($table, $key); VERANDER DIT
    $o = MB_Ref($table, $key);
 
    T_EO($o);
 
    $newfield = "vessel";
    $newtitle = "Vessel";
    $list = file("/openims2/vessel2.txt");
 
    $newtype = "list";
    $newrequired = false;
    $newmethod = false;
    $newshow = "visible";
    $newsort = false;
    $newblocks = false;
    $newvalues = array();
  
    T_EO($list);
    $newvalues["---"] = "---";
    foreach($list as $key=>$name) {
      $name = trim($name);
      $tobechanged = array(" ", "." , "(", ")", "#");
      $newkey = strtolower(str_replace($tobechanged , "_", $name));
      $newvalues[$name] = $newkey;
    }
    // T_EO($newvalues);
 
    $o[$newfield] = array( "type" => $newtype,
                           "title" => $newtitle,
                           "required" => $newrequired,
                           "method" => $newmethod,
                           "show" => $newshow,
                           "sort" => $newsort,
                           "blocks" => $newblocks,
                           "values" => $newvalues
    );
    T_EO($o);
  }
}

function SYS_FindRegExInPagesFromSite($sgn,$site,$regex) {
   $table = "ims_".$sgn."_objects";
   $specs = array();
   $specs["select"]['$record["mysite"]'] = $site;
   $specs["select"]['$record["published"]=="yes"||$record["preview"]=="yes"'] = true;
   $specs["value"] = '$record';
   $pages = MB_MultiQuery($table,$specs);
   $res = array();

   foreach($pages as $key=>$obj) {
     if ($obj["preview"]=="yes") {
       $pre_path = N_ProperPath ("html::"."/".$sgn."/preview/objects/".$key."/");
       $file = N_ReadFile($pre_path."page.html");
       if (preg_match_all($regex,$file,$matches)) {
         $res["preview"][$key] = "/".$site."/".$key.".php";
       }
     }
     if ($obj["published"]=="yes") {
       $pub_path = N_ProperPath ("html::"."/".$sgn."/objects/".$key."/");
       $file = N_ReadFile($pub_path."page.html");
       if (preg_match_all($regex,$file,$matches)) {
         $res["published"][$key] = "/".$site."/".$key.".php";
       }
     }
   }

   return $res;
}



function SYS_RebuildNavigation($sgn, $page_id, $doit = "") {
  // Rebuild a CMS navigation structure, starting at $page_id and recursively repairing all its children.
  // assuming that all webpages are correct about their "parent" but that they may be wrong about their "children".
  // The ordering of "children" is preserved.

  $table = "ims_{$sgn}_objects";

  $page = MB_Load($table, $page_id);

  echo "<div>";
  echo $page["parameters"]["preview"]["shorttitle"] . " ($page_id)<br/>";

  // Remove incorrect and deleted children from "children"
  MB_MultiLoad($table, $page["children"]);
  foreach ($page["children"] as $child_id => $dummy) {
    $child = MB_Load($table, $child_id);
    if ($child["parent"] != $page_id || !($child["preview"] == "yes" || $child["published"] == "yes")) {
      echo "<i>Disconnected: <font color=red>" . $child["parameters"]["preview"]["shorttitle"] . "</font> ($child_id)</i><br/>";
      unset($page["children"][$child_id]);
    }
  }

  // Use a query to find all children and if necessary, add them to "children"
  $specs = array();
  $specs["select"]['$record["objecttype"]'] = "webpage";
  $specs["select"]['$record["parent"]'] = $page_id;
  $specs["select"]['$record["preview"] == "yes" || $record["published"] == "yes"'] = true;
  $children = MB_TurboMultiQuery($table, $specs);
  foreach ($children as $child_id => $dummy) {
    if (!$page["children"][$child_id]) {
      $page["children"][$child_id] = $child_id;
      $child = MB_Load($table, $child_id);
      echo "<i>Connected: <font color=red>" . $child["parameters"]["preview"]["shorttitle"] . "</font> ($child_id)</i><br/>";
    }
  }

  if ($page["children"]) {
    echo 'Resulting children:<ul style="margin-top: 0; margin-bottem: 0">';
    foreach ($page["children"] as $child_id => $dummy) {
      echo "<li>";
      SYS_RebuildNavigation($sgn, $child_id, ($doit ? $doit : "no"));    
      echo "</li>";
    }
    echo "</ul>";
  }

  if ($doit == "yes") {
    MB_Save($table, $page_id, $page);
  }

  if (!$doit) echo '<b>Use SYS_RebuildNavigation("'.$sgn.'", "'.$page_id.'", "yes") to actually do something</b><br/>';


  echo "</div>";

}

function SYS_CheckFields($sgn = "") {
  echo "Testing FORMS_ShowValue on all code fields using a background call. The supergroupname will be set, but flex support functions will not be loaded. If any output is produced, you will see a warning. Errors must be fixed, but use your own judgement on warnings.<br/>";
  if (!$sgn) $sgn = IMS_SuperGroupName();
  if (!$sgn) { echo "<b>Please tell me which supergroup to test.</b>"; return; }
  $fields = MB_Load("ims_fields", $sgn);
  global $detecterrors;
  foreach ($fields as $name => $specs) {
    $detecterrors = false;
    if ($specs["type"] == "code" || $specs["type"] == "auto") {
      echo "Field $name... "; N_Flush();
      $code = 'IMS_SetSuperGroupName("'.$sgn.'"); FORMS_ShowValue("", $input);';
      $result = GRID_RPC(N_CurrentServer(), $code, $specs, 5, 1, true);
      if ($result == "ERROR") {
        echo '<font color="#FF0000"><b>ERROR</b></font><br/>';
        global $lowlevelresult;
        echo $lowlevelresult . "<br/>";
      } else {
        // Try to detect warnings (because the warnings will garble the grid pack, these will become automatically become errors
        $detecterrors = true;
        $result = GRID_RPC(N_CurrentServer(), $code, $specs, 5, 1, true);
        if ($result == "ERROR") {
          echo '<font color="#FF8000"><b>WARNING</b></font><br/>';
          global $lowlevelresult;
          if (strpos($lowlevelresult, chr(139).chr(8))) $lowlevelresult = N_KeepBefore($lowlevelresult, chr(139).chr(8)); // kill the gzip'ed part
          $lowlevelresult = preg_replace('!Warning: [^:]*/nkit/uuse_grid\.php\([0-9]+\) : !', 'Warning: ', $lowlevelresult);
          echo $lowlevelresult . "<br/>";
        } else {
          echo '<font color="#008000"><b>OK</b></font><br/>';
        }
      }
      
    }
  }
}

function SYS_RebuildTree($sgn, $directory_id, $doit = false) {
  $tree = CASE_Treeref($sgn, $directory_id);
  if (strpos($folder_id, ")") !== false) {
    $root_id = N_KeepBefore($folder_id, ")") . ")" . "root";
  } else {
    $root_id = "root";  
  }
  
  // Find orphaned documents and create folders
  $specs = array();
  $specs["select"]['$record["objecttype"]=="document" || $record["objecttype"]=="shortcut"'] = true;
  $specs["select"]['N_KeepBefore($record["directory"], ")")'] = N_KeepBefore($folder_id, ")");
  $specs["select"]['$record["published"]=="yes" || $record["preview"]=="yes"'] = true;
  $result = MB_MultiQuery("ims_{$sgn}_objects", $specs);
  foreach ($result as $key => $dummy) {
    $obj = MB_Ref("ims_{$sgn}_objects", $key);
    $dir_id = $obj["directory"];
    if (!$tree["objects"][$dir_id]) {
      echo "Creating folder $dir_id because of document $key <br/>";
      $tree["objects"][$dir_id]["x"] = "x";
      $tree["objects"][$dir_id]["parent"] = $root_id;
      $tree["objects"][$dir_id]["shorttitle"] = $tree["objects"][$dir_id]["longtitle"] = "Unknown (found document(s))";
      $tree["objects"][$root_id]["children"][$dir_id] = $dir_id;
    }
  }  
  
  foreach ($tree["objects"] as $folder_id => $folderspecs) {
    $parent_id = $folderspecs["parent"];
    //echo "Folder $folder_id, parent {$parent_id} <br/>";

    // Create parent if it does not exist
    if ($parent_id && !$tree["objects"][$parent_id]) {
      $tree["objects"][$parent_id]["x"] = "x";
      $tree["objects"][$parent_id]["parent"] = $root_id;
      $tree["objects"][$parent_id]["shorttitle"] = $tree["objects"][$parent_id]["longtitle"] = "Unknown (parent of " . $folderspecs["shorttitle"] . ")";
      $tree["objects"][$parent_id]["children"][$folder_id] = $folder_id;
      echo "Created folder {$parent_id}, parent of {$folder_id} <br/>";
    }

    // Register child if the parent if fucked up
    if ($parent_id && !$tree["objects"][$parent_id]["children"][$folder_id]) {
      echo "Registering child {$folder_id} with parent $parent_id<br/>";
      $tree["objects"][$parent_id]["children"][$folder_id] = $folder_id; 
    }

    // Help the poor orphans
    if (!$parent_id && $folder_id != $root_id) {
      $tree["objects"][$folder_id]["parent"] = $root_id;
      echo "Adopting orphan folder $folder_id by {$root_id}<br/>";      
    }

    // Repair the current object
    $tree["objects"][$folder_id]["x"] = "x";
    if (!$tree["objects"][$folder_id]["shorttitle"]) $tree["objects"][$folder_id]["shorttitle"] = "Unknown";

  }


  
  if ($doit) {
    $savetree = &CASE_Treeref($sgn, $directory_id);
    $savetree = $tree;
  } else {
    T_EO($tree);
  }
}


  function SYS_CreateDmsExportSlugs($sgn, $folder_id, $maxlength = 259, $maxfolderlength = 40, $update = false) {
    /* Used by SYS_DMS_FullExport */

    /* Create and store slugs for all documents and subfolders in de DMS directory $folder_id.
     * Slugs are based on the shorttitle, and are unique (in a case-insensitive way).
     * Slugs are not persistent (although you can choose how much non-persistence you want).
     * $maxlength: Maximum length for document slugs. To enforce a maximum total path length, 
     *             substract what you already used for the folders / drive letters / UNC path.
     * $maxfolderlength: Maximum length for subfolder slugs. Also used for containers.
     * $update: If true, change existing slug when shorttitle changes (more efficient)
     *          If false, only change existing slug to solve conflicts with other slugs.
     *
     * This function stores the slugs in the documents and in the tree.
     * This function returns a associate array of $document_id => $slug pairs, so that you do not
     * need to do a query. The subfolders are not returned, but you can get them with a simple lookup.
     *
     * Some path and file name limits:
     * NTFS filesystem core: max 226 characters per path component (directory or filename), 32K total
     * Windows Explorer / most NTFS API's: max 260 characters total (and also max 226 per path component)
     * Linux: max 4096 characters total, max 255 for file names
     * These limits may include the \0 byte at the end, so may be 1 shorter in practice.
     *
     */
     
    // We never use IMS_NiceFileName because its automatic shortening of long file names could
    // delete the counters that we need for uniquness. But the same principles regarding safe/unsafe
    // characters apply.
    
    $result = array();
    $existingslugs = array(); 
      // Store everything that is already used (as a filename slug or as a subdir name).
      // Please use strtolower before checking and before inserting.
     
    // These strings may not be used as a filename (in any directory) in MS-DOS
    foreach (array('con', 'prn', 'aux', 'nul', 'ltp1', 'lpt2', 'lpt3', 'com1', 'com2', 'com3', 'com4') as $device) {
      $existingslugs[strtolower($device)] = "x";
    }

    // Get list of subfolders and add their lowercase names to the existing slug list.
    $tree = &CASE_TreeRef($sgn, $folder_id);
    $subfolders = $tree["objects"][$folder_id]["children"];
    foreach ($subfolders as $subfolder_id => $dummy) {
      // Check if the folder already has a slug and that it does not conflict or is too long
      $currentslug = $tree["objects"][$subfolder_id]["dmsexportslug"];
      //echo "<hr/>Currentslug: $currentslug<br/>";
      if ($currentslug && !$existingslugs[strtolower($currentslug)] 
          && (strlen($currentslug) <= $maxfolderlength) && !$update) {
        // Unless the caller wants to update, keep the existing (non-conflicting) slug
        $existingslugs[strtolower($currentslug)] = "x";
        continue;
      }
      
      // Find out the foldername 
      $foldername = $tree["objects"][$subfolder_id]["shorttitle"];
      $foldername = FORMS_ML_Filter($foldername);
      $foldername = SEARCH_RemoveAccents($foldername); // Because "een_twee_drie" looks better than "__n_twee_drie"
      $nicefoldername = preg_replace ("'[^A-Za-z0-9]'i", "_", $foldername);
      //echo "Folder: $nicefoldername<br/>";
        
      if ($currentslug && strlen($currentslug) <= $maxfolderlength) { // check that existing slug is not too long
        // Check if the foldername matches the existing slug. We only reach this code if $update = true
        
        if (strlen($currentslug) >= strlen($nicefoldername) || strlen($currentslug) == $maxfolderlength) { // Check that slug is not unnecessarily short. If it is, we create a new slug anyway.
          // Check whether the slug (still) matches the current foldername.
          // To check this, we remove the uniqueness counter for this check.
          $checkcurrentslug = preg_replace('/_[0-9]+$/', '', $currentslug);
          if (substr($nicefoldername, 0, strlen($checkcurrentslug)) == $checkcurrentslug) {
            $existingslugs[strtolower($currentslug)] = "x";
            continue;
          }
        }
      }

      // Create a slug
      $newslug = SYS_CreateUniqueSlug($nicefoldername, $existingslugs, $maxfolderlength);
      if (!$newslug) trigger_error("SYS_CreateUniqueSlug: unable to create unique slug for folder {$subfolder_id} with maxfolderlength $maxfolderlength"); // will be logged but does not halt execution
      $tree["objects"][$subfolder_id]["dmsexportslug"] = $newslug;
      $existingslugs[strtolower($newslug)] = "x";
      //echo "Newslug: $newslug<br/>";
    }
    MB_Flush();

    // Query all (preview or published) documents and their slugs
    $qspecs = array();
    $qspecs["select"]['$record["objecttype"] == "document" || $record["objecttype"] == "shortcut"'] = true;
    $qspecs["select"]['$record["preview"] == "yes" || $record["published"] == "yes"'] = true;
    $qspecs["select"]['$record["directory"]'] = $folder_id;
    // Sort by creation date. By always looking at the documents in the same order,
    // and by looking at new documents last, we (hopefully) will solve slug conflicts in a
    // predictable way. Exception: folders always win from documents, even if the folder is newer.
    $qspecs["sort"] = 'QRY_DMS_Created_v1($record)';
    $qspecs["value"] = '$record["dmsexportslug"]';
    
    $documents = MB_TurboMultiQuery("ims_{$sgn}_objects", $qspecs);
  
    foreach ($documents as $truekey => $currentslug) {
      // Check if the document already has a slug and that it does not conflict or is too long
      //echo "<hr/>Currentslug: $currentslug<br/>";
      if ($currentslug && !$existingslugs[strtolower($currentslug)] 
          && (strlen($currentslug) <= $maxlength) && !$update) {
        // Unless the caller wants to update, keep the existing (non-conflicting) slug
        $existingslugs[strtolower($currentslug)] = "x";
        $result[$truekey] = $currentslug;
        continue;
      }

      $trueobject = &MB_Ref("ims_{$sgn}_objects", $truekey);
      if (FILES_IsShortcut($sgn, $truekey)) {
        $document_id = FILES_Base($sgn, $truekey);
        $document = MB_Ref("ims_{$sgn}_objects", $document_id);
        $title = $trueobject["base_shorttitle"]; 
      } else {
        $document_id = $truekey;
        $document = MB_Ref("ims_{$sgn}_objects", $document_id);
        $title = $document["shorttitle"];
      }
      
      // Find out the document name 
      $title = FORMS_ML_Filter($title);
      $title = SEARCH_RemoveAccents($title); // Because "een_twee_drie" looks better than "__n_twee_drie"
      $nicefilename = preg_replace ("'[^A-Za-z0-9]'i", "_", $title);
      $thedoctype = FILES_FileType($sgn, $document_id);
      if ($thedoctype && $thedoctype != "imsctn.txt") {
        $nicefilename = $nicefilename . "." . $thedoctype;
        $themaxlength = $maxlength;
      } else {
        $themaxlength = min($maxlength - 15, $maxfolderlength); 
          // A container IS a folder, so it makes sense not to give it a longer slug than maxfolderlength.
          // If despite this, there is not enough room in the path for the contents (subdirs and files) of the
          // container, we create a zip file "container.zip" inside the container directory. For that option,
          // we need $maxlength - 15).
      }
      //echo "Title: $nicefilename<br/>";
     
      if ($currentslug && strlen($currentslug) <= $themaxlength) { // check that existing slug is not too long
        // Check if the title matches the existing slug. We only reach this code if $update = true
        if (strlen($currentslug) >= strlen($nicefilename) || strlen($currentslug) == $themaxlength) { // Check that slug is not unnecessarily short. If it is, we create a new slug anyway.
          // Check whether the slug (still) matches the current foldername.
          
          // Remove extension and uniqueness counter
          $checkcurrentslug = preg_replace('/(_[0-9]+)?(\..+)?$/', '', $currentslug);
          if (substr($nicefilename, 0, strlen($checkcurrentslug)) == $checkcurrentslug) {
            $existingslugs[strtolower($currentslug)] = "x";
            $result[$truekey] = $currentslug;
            continue;
          }
        }
      }

      // Create a slug
      $newslug = SYS_CreateUniqueSlug($nicefilename, $existingslugs, $themaxlength);
      if (!$newslug) trigger_error("SYS_CreateUniqueSlug: unable to create unique slug for document {$truekey} with maxlength $themaxlength");
      $trueobject["dmsexportslug"] = $newslug;
      if ($flushcount++ % 20 == 0) MB_Flush();
      $existingslugs[strtolower($newslug)] = "x";
      $result[$truekey] = $newslug;
      //echo "Newslug: $newslug<br/>";
    }
    
    return $result;
  }

  function SYS_CreateUniqueSlug($candidate, $existingslugs, $maxlength = 226) {
    /* Used by SYS_DMS_FullExport */

    // This will check and return $candidate if it is unique and not too long.
    // If needed, this function will generate and try new candidates.
    // This function does not in any way make $candidate nice, safe, or suitable as a file name.
    // $existingslugs should be lowercase.
    
    if ($pos = strpos($candidate, ".")) {
      $basename = substr($candidate, 0, $pos);
      $extension = substr($candidate, $pos); // includes the .
    } else {
      $basename = $candidate;
      $extension = "";
    }

    if (strlen($extension) + 1 >= $maxlength) return false; // use trigger_error in the calling function (where you know the object_id or other useful stuff)

    $shortcandidate = substr($basename, 0, ($maxlength - (strlen($extension) + 1))) . $extension;
    if (!$existingslugs[strtolower($shortcandidate)]) return $shortcandidate;
 
    for ($i = 1; $i < 20000; $i++) {
      if (strlen($i) + strlen($extension) + 1 >= $maxlength) return false;
      $shortcandidate = substr($basename, 0, ($maxlength - (strlen($i) + strlen($extension) + 1))) . "_" . $i . $extension;
      if (!$existingslugs[strtolower($shortcandidate)]) return $shortcandidate;
    }
    return false;
    
  }
 
  function SYS_DMS_FullExport($basepath, $cases = array(), $input = array()) {
    /* This export solves the following problems:
       - Solves path length problems in Windows Explorer and other primitive NTFS API's, when caused by long document names.
       - Solves path length problems, when caused by containers (zips containers).
       - Gives you a setting (maxfolderlength) to solve path length problems caused by too long / deep subfolder names.
       - Can "incrementally" update a previous export, because unique filenames are stored with the document objects. 
         (but: if a documents is deleted / moved / renamed, the old file on disk will not be deleted.
       - Can handle duplicate folder names
       - Can export preview / published / both versions of documents (in separate folder structures)
       - Can handle shortcuts and permalinks
     */
    if (!isset($input["maxfilenamelength"]))   $input["maxfilenamelength"] = 226;
    if (!isset($input["maxpathlength"]))       $input["maxpathlength"] = 259;
    if (!isset($input["maxfolderlength"]))     $input["maxfolderlength"] = 50;
    if (!isset($input["updateslugs"]))         $input["updateslugs"] = true;
    if (!isset($input["createemptyfolders"]))  $input["createemptyfolders"] = true;
    if (!isset($input["published"]))           $input["published"] = true;             // Export published documents
    if (!isset($input["preview"]))             $input["preview"] = false;              // Export preview documents
    if (!isset($input["logskippedfiles"]))     $input["logskippedfiles"] = false;
      // Log files that were already present in the destination dir and did not need to be overwritten
    if (!isset($input["alwayszipcontainers"])) $input["alwayszipcontainers"] = false;
      // Always zip containers, even if not necessary for path length. Use this if you have problems with special characters in filenames inside containers
      
    $sgn = $input["sgn"] = IMS_SuperGroupName();

    $basepath = N_CleanPath($basepath); // make slashes uniform (forward)
    if (substr($basepath, -1) != "/") $basepath .= "/"; // make sure $basepath ends in forward slash
    N_WriteFile($basepath . "test_if_dms_export_path_is_writable.txt", "0"); // do or die
    N_DeleteFile($basepath . "test_if_dms_export_path_is_writable.txt");
    $input["basepath"] = $basepath;

    if (!MB_INDEX_CheckFlag("SYS_DMS_FullExport", 4000)) {
      echo ML("Er is nog een export bezig", "Export in progress");
      N_Die("SYS_DMS_FullExport: " . ML("Er is nog een export bezig", "Export in progress"));
    }

    
    // Determine cases to export
    if (!$cases) {
      $cases = MB_AllKeys("ims_{$sgn}_case_data");
      $cases[] = ""; // Algemeen
    }

    // Create unique case slugs
    MB_MultiLoad("ims_{$sgn}_case_data", $cases);
    $caseslugs = array();
    foreach ($cases as $case_id) {
      if ($case_id) {
        $case = MB_Ref("ims_{$sgn}_case_data", $case_id);
        $casetitle = $case["shorttitle"];
        $casetitle = FORMS_ML_Filter($casetitle);
        $casetitle = SEARCH_RemoveAccents($casetitle);
        $nicecasetitle = preg_replace ("'[^A-Za-z0-9]'i", "_", $casetitle);
      } else {
        $nicecasetitle = ML("Algemeen", "Generic");
      }
      $caseslug = SYS_CreateUniqueSlug($nicecasetitle, $caseslugs);
      $caseslugs[strtolower($caseslug)] = "x";
      $stack[] = array("what" => "folder", "id" => $case_id . "root", "path" => $caseslug);
    }
    $input["initstack"] = $stack;

    $specs["title"] = "DMS Export";
    $specs["input"] = $input;
    
    $specs["init_code"] = '
      $sgn = $input["sgn"];
      $data = $input["initstack"];
      TERRA_Flush();
    ';
    
    $specs["step_code"] = '
      uuse("sys");
      MB_INDEX_PulseFlag("SYS_DMS_FullExport");
      $sgn = $input["sgn"];
      $stack = $data;
      if (!$stack) $completed = true;
      $dospecs = array_pop($stack);

      if ($dospecs["what"] == "folder") {
        $folder_id = $dospecs["id"];
        TERRA_Status("Folder {$folder_id} {$dospecs["path"]}");
        if ($input["createemptyfolders"]) {
          if ($input["published"]) N_MkDir($input["basepath"] . "pu/" . $dospecs["path"]);
          if ($input["preview"]) N_MkDir($input["basepath"] . "pr/" . $dospecs["path"]);
        }
        $maxlength1 = $input["maxpathlength"] - (strlen($input["basepath"]) + strlen($dospecs["path"]) + 19); // 3 for "pr/" or "pu/", 1 because $dospecs["path"] does not have a trailing / yet, 15 for N_WriteFile temporary suffixes (_XXXXX_new_t3mp)
        $maxlength = min($maxlength1, $input["maxfilenamelength"]);
        $maxfolderlength = min($maxlength1, $input["maxfolderlength"]);
        TERRA_Status("Determining unique filenames for folder {$folder_id}, maxlength {$maxlength}, maxfolderlength {$maxfolderlength}, path {$dospecs["path"]}");
        
        // Note that although this function only returns documents, it also creates slugs for subfolders.
        $documents = SYS_CreateDmsExportSlugs($input["sgn"], $folder_id, $maxlength, $maxfolderlength, $input["updateslugs"]);

        //TERRA_Status("Queueing documents and subfolders for {$folder_id}");
        $tree = CASE_TreeRef($sgn, $folder_id);
        $children = $tree["objects"][$folder_id]["children"];
        foreach ($children as $child_id => $dummy) {
          if ($tree["objects"][$child_id]["dmsexportslug"]) { // Skip (new?) items without a slug
            $stack[] = array("what" => "folder", "id" => $child_id, "path" => $dospecs["path"] . "/" . $tree["objects"][$child_id]["dmsexportslug"]);
          }
        }

        foreach ($documents as $document_id => $slug) {
          if ($slug) $stack[] = array("what" => "document", "id" => $document_id, "path" => $dospecs["path"] . "/" . $slug);
        }

      } elseif ($dospecs["what"] == "document") {
        TERRA_Status("Document(s) {$dospecs["id"]}... {$dospecs["path"]}...");
        for ($i = 0; $i < 50; $i++) { // do up to 50 documents at a time without TERRA flushing and other overhead
          $document_id = $dospecs["id"];
          $document = MB_Load("ims_{$sgn}_objects", $document_id);
          if (FILES_IsShortcut($sgn, $document_id)) {
            $base_id = FILES_Base($sgn, $document_id);
            $base = MB_Load("ims_{$sgn}_objects", $base_id);
            if (FILES_IsPermaLink($sgn, $document_id)) {
              //TERRA_Status("Permalink: $document_id -> $base_id / {$document["base_version"]}");
              if ($input["published"] && $document["base_version"] == intval($document["base_version"])) {
                SYS_DMS_FullExport_Document($base_id, $dospecs["path"], $input, false, $document["sourceversion"]);
              }
              if ($input["preview"]) {
                SYS_DMS_FullExport_Document($base_id, $dospecs["path"], $input, true, $document["sourceversion"]);
              }
            } else {
              //TERRA_Status("Shortcut: $document_id -> $base_id");
              if ($input["published"] && $base["published"] == "yes") {
                SYS_DMS_FullExport_Document($base_id, $dospecs["path"], $input);
              }
              if ($input["preview"]) {
                SYS_DMS_FullExport_Document($base_id, $dospecs["path"], $input, true); // export preview version
              }
            }
          } else {
            if ($input["published"] && $document["published"] == "yes") {
              SYS_DMS_FullExport_Document($document_id, $dospecs["path"], $input);
            }
            if ($input["preview"]) {
              SYS_DMS_FullExport_Document($document_id, $dospecs["path"], $input, true); // export preview version
            }
          }
          
          $peek = end($stack);
          if ($peek && $peek["what"] == "document" && $i < 49) {
            $dospecs = array_pop($stack);
          } else {
            break; // break out of for loop
          }
        }   
      }
      
      $data = $stack;
      TERRA_Flush();
    ';
    
    if ($input["exit_code"]) {
      $specs["exit_code"] = '
        TERRA_Status("Executing custom exit code");
      ' . $input["exit_code"] . '
        MB_INDEX_RemoveFlag("SYS_DMS_FullExport");
        TERRA_Status("Finished");
      ';
    } else {
      $specs["exit_code"] .= '
        MB_INDEX_RemoveFlag("SYS_DMS_FullExport");
        TERRA_Status("Finished");
      ';
    }
    TERRA_MultiStep ($specs);
    
  }

  function SYS_DMS_FullExport_Document($document_id, $path, $input, $preview = false, $version = "") {
    /* Used by SYS_DMS_FullExport */
    $sgn = $input["sgn"];
    $input["basepath"];
    
    $obj = MB_Load("ims_{$sgn}_objects", $document_id);
    if ($preview && !($obj["preview"]=="yes" || $obj["published"]=="yes")) return false;
    if (!$preview && $obj["published"]!="yes") return false;

    if ($preview) {
      $srcpath = FILES_FileLocation($sgn, $document_id);
      $dstpath = $input["basepath"] . "pr/" . $path;
    } else {
      $srcpath = FILES_PublishedFilelocation($sgn, $document_id);
      $dstpath = $input["basepath"] . "pu/" . $path;
      if ((FILES_FileType($path) != FILES_FileType($sgn, $document_id, "published")) && FILES_FileType($sgn, $document_id, "published") != "imsctn.txt") {
        $extrapath = substr($path, 0, strlen($path) - strlen(FILES_FileType($path))) . FILES_FileType($sgn, $document_id, "published");
        TERRA_Status("Warning: filename-filetype mismatch because they changed since last publication. Copying $path to $extrapath. This could potentially overwrite something or be overwritten by something. In you run into trouble, publish the offending file or do a preview export.");
        SYS_DMS_FullExport_Document($document_id, $extrapath, $input, $preview, $version);
      }
    }
    if ($version) {
      $srcpath = "html::/{$sgn}/objects/history/{$document_id}/{$version}/" . FILES_TrueFileName($sgn, $document_id, $version);
    }
    
    $thedoctype = FILES_FileType ($sgn, $document_id, ($version ? $version : ($preview ? "preview" : "published")));
    if ($thedoctype=="imsctn.txt") {
      if ($preview) {
        $srcpath = "html::/{$sgn}/preview/objects/{$document_id}/";
      } else {
        $srcpath = "html::/{$sgn}/objects/{$document_id}/";
      }
      N_DeleteFile($dstpath); // mkdir will fail if there is a file with the same name
      N_MkDir($dstpath);
      $tree = N_QuickTree($path);
      $maxrelpath = 0;
      foreach ($tree as $dummy => $filespecs) {
        $maxrelpath = max($maxrelpath, strlen($filespecs["relpath"]) . strlen($filespecs["filename"]));
      }
      if ($input["alwayszipcontainers"] || ((strlen($dstpath) + $maxrelpath) > $input["maxpathlength"])) {
        // Zip and copy
        global $myconfig;
        if ($myconfig["zip"]) {
          TERRA_Status("Zipping and copying container $document_id from $srcpath to destination $dstpath");
          uuse("tmp");
          $tmpdir = TMP_Directory();
          N_CopyDir($tmpdir, $srcpath);
          $cwd = getcwd();
          chdir($tmpdir);
          $command = $myconfig["zip"] . " -r container.zip .";
          system($command);
          chdir($cwd);
          N_CopyFile($dstpath . "/container.zip", $tmpdir . "/container.zip");
        } else {
          trigger_error("SYS_DMS_FullExport_Document: unable to zip container, \$myconfig[zip] not defined");
        }
      } else {
        // Just copy it
        TERRA_Status("Copying container $document_id from $srcpath to destination $dstpath");
        N_CopyDir($dstpath, $srcpath);
      }
    } else {
      if (N_FileExists($dstpath) && (N_FileSize($srcpath) == N_FileSize($dstpath)) && (N_FileTime($srcpath) == N_FileTime($dstpath)))  {
        // Nothing to do
        if ($input["logskippedfiles"]) TERRA_Status("Nothing to do for document $document_id, destination $dstpath");
      } else {
        TERRA_Status("Copying document $document_id to destination $dstpath");
        N_CopyFile($dstpath, $srcpath);
        N_Touch($dstpath, N_FileTime($srcpath));
      }
    }    
  }

  function SYS_RepairFulltextIndex($supergroupname,$ageindays = 2)
  {
    global $myconfig;
    if ($myconfig["ftengine"] != "S2_SPHINX2") N_Die("SYS_RepairFulltextIndex: only works with Sphinx2");
    $index = $supergroupname."#previewdocuments";
    $index = SPHINX2_NormalizeIndexName ($index);
    
    $indexafter = time() - ($ageindays * 24 * 60 * 60);
    
    $specs = array();
    $specs["title"] = "Add unindexed files to index";
    $specs["tables"] = array("ims_".$supergroupname."_objects"); //          list of tables, e.g. array ("table1", "table2")
    $specs["input"] = array(
      "index" => $index,
      "sgn" => $supergroupname,
      "indexafter" => $indexafter,
      "ageindays" => $ageindays
    );
    $specs["init_code"] = 'N_Log("repairfulltextindex","START");';
    $specs["step_code"] = '
      if($object["objecttype"]=="document" && ($object["preview"]=="yes" ||  $object["published"]=="yes"))
      {
        if(!count(SPHINX2_GetFromIndex ($input["index"], $key)))
        {
          N_Log("repairfulltextindex","Document $key not indexed, adding to preview index");
          SEARCH_AddPreviewDocumentToDMSIndex($input["sgn"], $key);
          if($object["published"]=="yes")
          {
            N_Log("repairfulltextindex","Document $key also adding to published index");
            SEARCH_AddDocumentToDMSIndex ($input["sgn"], $key);
          }
        } else if ($input["ageindays"] && QRY_DMS_Changed_v1($object)>$input["indexafter"]) {
          N_Log("repairfulltextindex","Document $key is newer than ".$input["ageindays"].", so adding to index");
          SEARCH_AddPreviewDocumentToDMSIndex($input["sgn"], $key);
          if($object["published"]=="yes")
          {
            N_Log("repairfulltextindex","Document $key also adding to published index");
            SEARCH_AddDocumentToDMSIndex ($input["sgn"], $key);
          }
        }
      }  
    ';

    $specs["exit_code"] = '
      N_Log("repairfulltextindex","DONE");
    ';

    TERRA_Multi_Tables ($specs);
    echo ML ("De full text index wordt in de achtergrond gerepareerd", "The full text index will be repaired in the background");
  }

  function SYS_RepairSphinxDates($supergroupname)
  {
    // Repareer datums (date_added) in Sphinx, zodat hier de datum van laatste wijziging (ipv van laatste indexering)
    // komt te staan. Vanaf build XXX wordt standaard gesorteerd op relevantie binnen datumsegmenten (SPH_SORT_TIME_SEGMENTS)
    // ipv op pure relevantie. Dus hoe beter die datum, hoe beter de sortering.
    global $myconfig;
    if ($myconfig["ftengine"] != "S2_SPHINX2") N_Die("SYS_RepairSphinxDates: only works with Sphinx2");
    
    $specs = array();
    $specs["title"] = "Repair Sphinx dates";
    $specs["tables"] = array("ims_".$supergroupname."_objects");
    $specs["input"]["sgn"] = $supergroupname;
    $specs["input"]["mysqltables"][] = "sphinxftindex2_" . MB_MYSQL_Key2Name(SPHINX2_NormalizeIndexName($supergroupname."#previewdocuments"));
    $specs["input"]["mysqltables"][] = "sphinxftindex2_" . MB_MYSQL_Key2Name(SPHINX2_NormalizeIndexName($supergroupname."#publisheddocuments"));
    if (is_array ($myconfig["extradmsindex"])) foreach ($myconfig["extradmsindex"] as $name => $specs) {
      if ($specs["sgn"]==$supergroupname) {
        $specs["input"]["mysqltables"][] = "sphinxftindex2_" . MB_MYSQL_Key2Name(SPHINX2_NormalizeIndexName($supergroupname."#previewdocuments#extra#".$name));
      }
    }

    $specs["step_code"] = '
      if ($object["objecttype"]=="document" && ($object["preview"]=="yes" || $object["published"]=="yes")) {
        $date = intval(QRY_DMS_Changed_v1($object));
        global $myconfig;
        foreach ($input["mysqltables"] as $mysqltable) {
          $sql = "UPDATE {$mysqltable} SET date_added={$date} WHERE thekey=\'{$key}\';";
          MB_MYSQL_Query ($sql, 0, $myconfig["ftsqlconnection"]);
        }
      }
    ';

    $specs["exit_code"] = '
      uuse("sphinx2"); SPHINX2_InitCorrectDates();
    ';

    TERRA_Multi_Tables ($specs);
    echo ML ("De full text index wordt in de achtergrond gerepareerd", "The full text index will be repaired in the background");
  }

  function SYS_ForcedConditionalReplicate($to, $from, $sgn, $age, $log=false)
  {

    uuse ("terra");
    $now = time();
 
    // check if previous call to terra has ended
    $sem = "custom_" . $sgn . "_conditionelereplicatiesemafoor";
    $logname = "ForcedConditionalReplicate";
    $flag = MB_Load($sem, "x");
    if ($flag)
    {
      //if ($log)
      //  N_Log($logname, "vlag gevonden");
      return;
    }

    MB_Save($sem, "x", "x");
    //if ($log)
    //  N_Log($logname, "vlag gezet");
 
    $specs = array();
    $specs["tables"] = array("ims_" . $sgn  . "_objects");
    $specs["input"] = array("sgn" =>$sgn, "to" => $to, "age" => $age, "now" => $now, "from" => $from, "log" => $log, "sem" => $sem, "logname" => $logname);

    $specs["step_code"]= '

      $sgn = $input["sgn"];
      $to = $input["to"];
      $age = $input["age"];
      $now = $input["now"];
      $from = $input["from"];
      $log = $input["log"];
      $logname = $input["logname"];

      uuse("sys");
      uuse("terra");

      $llobj = URPC($from, \'$output = MB_MUL_Load($input["table"], $input["key"]);\', array("table" => $table, "key" => $key));
      $tm = $llobj["time"];
        
      $young = ($now - $tm <= $age);
      if ($young)
      {
        $ok = true; 
        $exist = function_exists("TERRA_ReplicateFilePreCheck");
        if ($exist)
        {
          $supergroup = $sgn;
          $objectid = $key;
          $precheck = TERRA_ReplicateFilePreCheck();
          eval ($precheck); 
        } 
        if ($ok) 
          SYS_ReplicateObjectAndFiles ($to, $from, $sgn, $key, true);
      }
 
      if ($log and $ok)
      { 
        //N_Log($logname, "table " . $table);
        //N_Log($logname, "key " . $key);
        //N_Log($logname, "to " . $to);
        //N_Log($logname, "from " . $from);
        $obj = $llobj["data"];
        if ($obj["objecttype"] == "webpage")
          $title = $obj["parameters"]["preview"]["shorttitle"];
        else 
          $title = $obj["shorttitle"];
        N_Log($logname, "title " . $title);
        //N_Log($logname, "max age " . $age);
        //N_Log($logname, "timestamp " . N_VisualDate($tm, true));
        //N_Log($logname, "now " . N_VisualDate($now, true));
        //N_Log($logname, "young " . ($young ? "ja" : "nee"));
        //N_Log($logname, "precheck func " . ($exist ? "ja" : "nee"));
        //N_Log($logname, "precheck " . ($ok ? "ja" : "nee"));
      }

    ';

    $specs["exit_code"] = '

      $sem = $input["sem"];
      $log = $input["log"];
      $logname = $input["logname"];

      MB_Delete($sem, "x");
      //if ($log)
      //  N_Log($logname, "vlag verwijderd");
    ';
    $specs["timeborder"] = time()-$age;

    TERRA_Multi_Tables($specs);
  }


?>
