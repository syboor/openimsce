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



// GVA jan 2011
// onder switch 'trackinglist' 

uuse("ims");

function TRACKINGLIST_cmp($a, $b)
{
  if ($a["added"] == $b["added"]) {
    return 0;
  }
  return ($a["added"] < $b["added"]) ? -1 : 1;
}

function TRACKINGLIST_visiblecasename( $sgn, $currentfolder )
{
  if ( substr($currentfolder, 0, 1)=="(" )
  {
    $case_id = substr ($currentfolder, 0, strpos ($currentfolder, ")")+1);
    $case = MB_Ref ("ims_".$sgn."_case_data", $case_id);
    return N_htmlentities( $case["shorttitle"] );
  } 
  if ($currentfolder) return N_htmlentities (ML("Algemeen","Generic"));
  return false;
}

function TRACKINGLIST_ReminderValues($reverse=false) {
  global $myconfig;
  $supergroupname = IMS_Supergroupname(); // 20130307 KVD WSG-52 
  if($myconfig[$supergroupname]["trackinglist_pulldown"]) $list = $myconfig[$supergroupname]["trackinglist_pulldown"];
  else $list = array(0,1,3,7,14,23);
  $values = array();
  if ($reverse) {
    foreach($list as $listitem) {
      if($listitem==0) { $values[$listitem] = ML("Geen reactie nodig","no reply required"); }
      elseif($listitem==1) { $values[$listitem] = $listitem.ML(" dag"," day"); }
      else { $values[$listitem] = $listitem.ML(" dagen"," days"); }
    }
  } else {
    foreach($list as $listitem) {
      if($listitem==0) { $values[ML("Geen reactie nodig","no reply required")] = $listitem; }
      elseif($listitem==1) { $values[$listitem.ML(" dag"," day")] = $listitem; }
      else { $values[$listitem.ML(" dagen"," days")] = $listitem; }
    }
  }
  return $values;
}

function TRACKINGLIST_AddDocument($sgn, $document_id, $reminder, $user_id="")
{
  $me = ($user_id)?$user_id:SHIELD_CurrentUser();
  $utable = "shield_" . $sgn . "_users";
  $uobj = &MB_Ref($utable, $me);
  $uobj["trackinglist"][$document_id] = array("added"=>time(), "reminder_days"=>$reminder);
  $history = TRACKINGLIST_HistoryEntry($sgn,"add");
  if($history) {
    $obj = &mb_ref("ims_${sgn}_objects", $document_id);
    $guid = N_GUID();
    $obj["history"][$guid] = $history;
  }
  $log = array("doc_id"=>$document_id, "doc_name"=>$obj["shorttitle"], "uid"=>$me, "uname"=>$uobj["name"]);
  N_Log("trackinglist",ML("document toegevoegd aan volglijst","document added to trackinglist"),print_r($log,1));
}

function TRACKINGLIST_RemoveDocument($sgn, $document_id, $user_id="")
{
  $me = ($user_id)?$user_id:SHIELD_CurrentUser();
  $utable = "shield_" . $sgn . "_users";
  $uobj = &MB_Ref($utable, $me);
  $history = TRACKINGLIST_HistoryEntry($sgn,"add");
  if($history) {
    $obj = &mb_ref("ims_${sgn}_objects", $document_id);
    $guid = N_GUID();
    $obj["history"][$guid] = $history;
  }
  unset ($uobj["trackinglist"][$document_id]);
  $log = array("doc_id"=>$document_id, "doc_name"=>$obj["shorttitle"], "uid"=>$me, "uname"=>$uobj["name"]);
  N_Log("trackinglist",ML("document verwijderd uit volglijst","document removed from trackinglist"),print_r($log,1));
}

function TRACKINGLIST_Show($sgn, $user_id="")
{
  $me = ($user_id)?$user_id:SHIELD_CurrentUser();
  $utable = "shield_" . $sgn . "_users";
  $otable = "ims_" . $sgn . "_objects";
  $uobj = MB_Ref($utable, $me);
  $trackinglist = $uobj["trackinglist"];
  foreach($trackinglist as $doc_id=>$dummy) {
    $deleted = TRACKINGLIST_FileIsDeleted($sgn,$doc_id);
    if($deleted) unset($trackinglist[$doc_id]);
  }
  if(!$trackinglist) return ML("Geen documenten","No Documents");
//N_Log("trackinglist","list voor sort",print_r($trackinglist,1));
  uasort($trackinglist, "TRACKINGLIST_cmp");
  //sort($trackinglist);
//N_Log("trackinglist","list na sort",print_r($trackinglist,1));
  $reminder_values = TRACKINGLIST_ReminderValues(true);
  $specs = array(
    "sort" => "dms_docs",
    "sort_default_dir" => "u",
    "sort_map_1" => 2,
    "sort_2" => "auto",
    "sort_3" => "auto",
    "sort_4" => "auto",
    "sort_5" => "auto",
    "sort_6" => "auto"
  );
  T_Start("ims",$specs);
  echo "&nbsp;";
  T_Next();
  echo ML("Dossier","Case");
  T_Next();
  echo ML("Document","Document");
  T_Next();
  echo ML("Toegewezen aan","Allocated to");
  T_Next();
  echo ML("Toegevoegd","Added");
  T_Next();
  echo ML("Herinner mij na","Remind me after");
  T_Next();
  echo "&nbsp;";
  T_NewRow();
  global $recordedtimestamp;
  foreach($trackinglist as $doc=>$data) {
    //Remove from list form
    $remove = array();
    $remove["input"] = array("sgn"=>$sgn, "doc"=>$doc);
    $remove["formtemplate"] = ML("Van de lijst verwijderen?","Remove from list?")."<br>[[[OK]]] [[[Cancel]]]";
    $remove["postcode"] = '
      uuse("trackinglist");
      TRACKINGLIST_RemoveDocument($input["sgn"],$input["doc"]);
      //N_Redirect("closeme&parentrefresh");
    ';
    $removeurl = FORMS_URL($remove);
    //end form
    $dmsurl = FILES_DMSURL($sgn,$doc);
    //N_Log("trackinglist","sgn=$sgn doc=$doc",print_r($data,1));
    echo '<a class="ims_navigation" title="Ga naar folder" href="'.$dmsurl.'"><img border="0" src="/ufc/rapid/openims/folder.gif"></a>';
    T_Next();
    $folder = mb_fetch($otable, $doc, "directory");
    $casename = TRACKINGLIST_visiblecasename($sgn,$folder);
    echo ($casename)?$casename:"&nbsp;";
    T_Next();
    $shorttitle = mb_fetch($otable, $doc, "shorttitle");
    echo ($shorttitle)?$shorttitle:"&nbsp;";
    T_Next();
    $allocto = mb_fetch($otable, $doc, "allocto");
    $name = mb_fetch($utable, $allocto, "name");
    echo ($name)?$name:"&nbsp;";
    T_Next();
    echo N_VisualDate($data["added"]);
    $recordedtimestamp = $data["added"];
    T_Next();
    //$val = ($data["reminder_days"])?$reply_values[$data["reminder_days"]]:$reply_values[0];
    //echo $val;
    $reminder = $reminder_values[$data["reminder_days"]];
    echo ($reminder)?$reminder:"&nbsp;";
    T_Next();
    echo '<a href="'.$removeurl.'">'.ML("Verwijder","Remove").'</a>';
    T_NewRow();
  }
  return TS_End();
}

function TRACKINGLIST_HistoryEntry($supergroupname, $option) {
  $time = time();
  $history["type"] = "option";
  $history["when"] = $time;
  $history["author"] = SHIELD_CurrentUser ($supergroupname);
  switch($option) {
    case "add":    $history["option"] = ML("Volglijst","Trackinglist");
                   $history["comment"] = ML("Het document is aan de volglijst toegevoegd","The document has been added to the trackinglist");
		   break;
    case "remove": $history["option"] = ML("Volglijst","Trackinglist");
                   $history["comment"] = ML("Het document is van de volglijst verwijderd","The document has been removed from the trackinglist");
                   break;
    default:       return;
  }
  return $history;
}

function TRACKINGLIST_BatchJobs($sgn)
{
  $table = "ims_" . $sgn . "_objects";
  $utable = "shield_" . $sgn . "_users";
  $time = time();
  $specs = array();
  $specs["value"] = '$record["trackinglist"]';
  $list = MB_MultiQuery($utable,$specs);
  foreach($list as $uid=>$track) {
    if($track) {
      foreach($track as $doc_id=>$data) {
        $days = $data["reminder_days"];
        $deleted = TRACKINGLIST_FileIsDeleted($sgn,$doc_id);
        if($days&&!$deleted) {
          $reminder_max = (3600*24*$days)+$data["added"];
          $reminder_min = (3600*24*($days-1))+$data["added"];
          if ($time>$reminder_min && $time<$reminder_max) TRACKINGLIST_SendReminder($sgn, $uid, $doc_id);		
        }
      }
    }
  }
  MB_Flush();
}

function TRACKINGLIST_SendReminder($sgn, $uid, $doc_id)
{
  uuse("ims");
  uuse("flex");
  FLEX_LoadSupportFunctions($sgn);
  if(function_exists("TRACKINGLIST_SendReminder_Extra")) {
    //TRACKINGLIST_SendReminder_Extra definable support function for custom email. Copy this function and modify.
	TRACKINGLIST_SendReminder_Extra($sgn,$uid,$doc_id);
  } else {
    $table = "ims_" . $sgn . "_objects";
    $utable = "shield_" . $sgn . "_users";
    $user = mb_ref($utable, $uid);
    $obj = mb_ref($table, $doc_id);

    $domain = IMS_Object2Domain ($sgn, $key);

    $title = $obj["shorttitle"];
    $fn = $obj["filename"];
    $subj = ML("Volglijst notificatie ","Trackinglist notification ")."\"" . $title . "\""; // 20120229
    $url = "http://" . $domain . FILES_DocPreviewUrl($sgn, $doc_id);
    $dmsurl = "http://" . $domain . "/ufc/url/" . $sgn . "/" . $doc_id . "/" . $fn;

    $body = "";
    $body .= "Document: " . $title . "\r\n";
    $body .= ML("De herinneringstermijn is verstreken","The reminderperiod has passed") . "\r\n";
    $body .= ML("Link naar het DMS: ", "Link to the DMS: ") . $dmsurl . "\r\n";
  // $body .= "Link: " . $url . "\r\n";
  
    $email = $user["email"];
    if($email) N_SendMail($email, $email, $subj, $body);
  }
}

function TRACKINGLIST_FileIsDeleted($sgn,$doc_id)
{
   $doc = mb_ref("ims_${sgn}_objects", $doc_id);
   if($doc["preview"]!=="yes" && $doc["published"]!=="yes") return true;
   else return false;
}