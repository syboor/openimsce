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



function EVENTS_ProcessStageChanged($oldstage, $newstage, &$case, $key, $process) {
  // this function should be called after changing the processstage of an object
  // it executes events 

  $events_statuschangeonly = $process["events"]["$newstage"]["statuschangeonly"];
  if(!$events_statuschangeonly) $events_statuschangeonly = "false";

  if($process["events"]["$newstage"]) {

    if(($process["events"]["$newstage"]["active"]=="true") && ($process["events"]["$newstage"]["eventcode"])) {
      if(  ($oldstage<>$newstage) || 
          (($oldstage==$newstage) && ($events_statuschangeonly=="false")) ) {
        eval($process["events"]["$newstage"]["eventcode"]);
      }
    }
  } else {
  } 

  
  // event handling on data access forms
  if($process["dataformevent"]) {
     if(($process["dataformevent"]["active"]=="true") && ($process["dataformevent"]["eventcode"])) {
        eval($process["dataformevent"]["eventcode"]);
     }
  }


}

function EVENTS_WorkflowStageChanged($oldstage, $newstage, &$object, $key) {
  // this function should be called after changing the workflowstage of an object
  // it executes events and sends signal mails

  $sitecollection_id = IMS_SuperGroupName(); //thb
  $workflow = &MB_Ref ("shield_".$sitecollection_id."_workflows", $object["workflow"]);

// AM 7-6-2010
  if ($oldstage != $newstage) {
    MAIL_SignalObject ($sitecollection_id, $key, IMS_Object2Latestauthor ($sitecollection_id, $key), IMS_Object2Domain ($sitecollection_id, $key), "s");
  }

  $events_statuschangeonly = $workflow["events"]["$newstage"]["statuschangeonly"];
  $signals_statuschangeonly = $workflow["signals"]["$newstage"]["statuschangeonly"];
  if(!$events_statuschangeonly) $events_statuschangeonly = "false";
  if(!$signals_statuschangeonly) $signals_statuschangeonly = "false";

  // send signal email?
  if($workflow["signals"]["$newstage"]) {
    if(($workflow["signals"]["$newstage"]["active"]=="true") && ($workflow["signals"]["$newstage"]["mailto"])) {
      //$currentuser = SHIELD_CurrentUserObject($sitecollection_id);
      if(  ($oldstage<>$newstage) || 
          (($oldstage==$newstage) && ($signals_statuschangeonly=="false")) ) {
        EVENTS_SendSignalMail($oldstage, $newstage, $object, $key, $workflow, $sitecollection_id);
      }
    }
  }

  if($workflow["events"]["$newstage"]) {

    if(($workflow["events"]["$newstage"]["active"]=="true") && ($workflow["events"]["$newstage"]["eventcode"])) {
      if(  ($oldstage<>$newstage) || 
          (($oldstage==$newstage) && ($events_statuschangeonly=="false")) ) {
        eval($workflow["events"]["$newstage"]["eventcode"]);
      }
    }
  } else {
  } 
}

function EVENTS_SendSignalMail($oldstage, $newstage, &$object, $object_id, &$workflow, $sitecollection_id) {

  global $myconfig;

$max=0;
  $domain = getenv("HTTP_HOST");
  $allocto = $object["allocto"];
  if (is_array($object["history"])) {
    foreach ($object["history"] as $guid => $spec) {
      if (IMS_Domain2Sitecollection($spec["http_host"])==$sitecollection_id) {
        $domain = $spec["http_host"];
      }
      if (!$allocto && $spec["author"]) $allocto = $spec["author"];
      if ($spec["when"] > $max) {
        $max = $spec["when"];
        if ($spec["author"]) $allocto = $spec["author"];
      }
    }
  }
  $user = $allocto?MB_Ref ("shield_".$sitecollection_id."_users", $allocto):"";

  if (strtolower($myconfig[$sitecollection_id]["ssl_usage"]) == 'required') $protocol = "https://";
  else $protocol = "http://";

  switch($object["objecttype"]) {
    case "document":
        $url = $protocol.$domain."/ufc/url/$sitecollection_id/$object_id/" . $object["filename"]; // OK preview
        $name = $object["shorttitle"];
        $name_in_intro = ML("het document","the document")." '".$name ."'";
    break;
    case "webpage":
        $site_id = IMS_Object2Site ($sitecollection_id, $object_id); //thb
        $url = $protocol.$domain."/$site_id/$object_id.php?activate_preview=yes";
        $name = $object["parameters"]["preview"]["longtitle"];
        $name_in_intro= ML("de webpagina","the webpage")." '".$name ."'";
    break;
    default:
        $url = ML("Url : onbekend objecttype","Url : unknown objecttype");
        $name = "XXX";
        $name_in_intro= ML("onbekend objecttype met onbekende naam","unknown objecttype with unknown name");
  }

  $subject = ML("OpenIMS automatisch signaal: status","OpenIMS automated signal: stage")." '".$workflow[$newstage]["name"]."' ".ML("is bereikt","has been reached");
  if (function_exists("MAIL_ChangeSignal"))
  {
    $body = MAIL_ChangeSignal($object["shorttitle"], $user["name"], $url, "s");
  }
  else
  {
    $body = ML("Deze mail is automatisch verstuurd door het OpenIMS systeem op %1", "This mail has been sent automatically by the OpenIMS system on %1", N_VisualDate(time(), true));
    $body .= " ".ML("omdat %1 de status %2 heeft bereikt","because %1 has reached stage %2", $name_in_intro, $workflow[$newstage]["name"]).".\r\n";
    $body .= "\r\n";
    $body .= ML("Naam","Name").": $name\r\n";
    $body .= ML("Vorig stadium","Previous stage").": ".$workflow[$oldstage]["name"]."\r\n";
    $body .= ML("Nieuw stadium","New stage").": ".$workflow[$newstage]["name"]."\r\n";
    $body .= ML("Toegewezen aan", "Assigned to").": ".$user["name"]." (mailto: ".$user["email"].")\r\n";
    $body .= "URL: $url\r\n";
    $body .= "Server: ".N_CurrentServer()." ($sitecollection_id)\r\n";
  }

  $mail_address = $user["email"];
  if($user["name"]) $mail_address = $user["name"].' <'.$user["email"].'>'; //Allowed according to RFC2822

  N_Mail ($mail_address, $workflow["signals"]["$newstage"]["mailto"], $subject , $body);
}


function TZEB_Debug($instring) {
  $handle = fopen("c:\\tzebdebug.txt", "a+");
  fwrite($handle, date('y-m-j H:i:s') . ": " . $instring . "\n");
  fclose($handle);
}

?>