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



/* 

  QRY functions should NEVER fail, therefore old QRY functions have to remain present (forever).

  Also QRY should avoud using memory as much as possible (e.g. not call MB_Ref for document 
  objects or call a function which calls MB_Ref for document objects).

*/

  function QRY_DMS_Deleted_By_v1($sgn, $object) 
  {
    if ($object["objecttype"]=="document") {
      if ( is_array($object["history"]) ) 
      {
        foreach( array_reverse( $object["history"] ) AS $key => $data )
        {
          if ($data["option"]=="delete") 
          {
            $userid = $data["author"];
            if ( !$userid ) return "unknown";
            $user = MB_Load ("shield_".$sgn."_users", $userid );
            if ( !$user["name"] ) return $userid;
            return $user["name"];
          }
        }
      }
    }
    return "unknown";
  }


  function QRY_DMS_Name ($object) {}
  function QRY_DMS_Name_v1 ($object) {
    if ($object["objecttype"]=="document") {
      return $object["shorttitle"];
    } else {
      return $object["base_shorttitle"];
    } 
  }

  function QRY_DMS_Status_v1 ($sgn, $key, $object) {
    if ($object["objecttype"]=="document") {
      if (!$object["workflow"]) $object["workflow"] = "edit-publish";
      $workflow = SHIELD_AccessWorkflow ($sgn, $object["workflow"]);
      if (!$object["stage"]) $object["stage"] = 1;
      if ($object["stage"] > $workflow["stages"]) $object["stage"] = $workflow["stages"];
      $name = $workflow [$object["stage"]]["name"];  
      if (!$name) $name = "unknown";
      return $name;
    } else {
      return $object["base_stagename"];
    } 
  }

  function QRY_DMS_Version_v1 ($sgn, $key, $object) {
    if ($object["objecttype"]=="document") {

      global $myconfig;
      $counter = 0; $major = 0; $minor = -1; // specs of last version
      if (is_array ($object["history"])) foreach ($object["history"] as $id => $specs) {
        if ($specs["published"]=="yes") { // major version
          $counter++; $major++; $minor=0;
          $vers_counter[$id] = $counter;
          $vers_major[$id] = $major;
          $vers_minor[$id] = $minor;
          $lastpublished = $id;
          $lastversion = $id;
        } else if ($specs["type"]=="" || $specs["type"]=="edit" || $specs["type"]=="new") { // minor version
          $counter++; $minor++;
          $vers_counter[$id] = $counter;
          $vers_major[$id] = $major;
          $vers_minor[$id] = $minor;
          $lastversion = $id;
        }
      }  

      if ($history_id=="lastpublished") {
        $counter = $vers_counter[$lastpublished];
        $major = $vers_major[$lastpublished];
        $minor = $vers_minor[$lastpublished];
      } else if ($history_id) {
        $counter = $vers_counter[$history_id];
        $major = $vers_major[$history_id];
        $minor = $vers_minor[$history_id];
      } else {
        $counter = $vers_counter[$lastversion];
        $major = $vers_major[$lastversion];
        $minor = $vers_minor[$lastversion];
      }

      if ($myconfig[$sgn]["customversions"]) {
        eval ($myconfig[$sgn]["customversions"]);
      }
 
      return $version;
    } else {
      return $object["base_version"];
    }
  }

  function QRY_DMS_Changed_v1 ($object) {
    if ($object["objecttype"]=="document") {
      if (is_array($object["history"])) {
        reset ($object["history"]);
        while (list($k, $data)=each($object["history"])) {
          $time = $data["when"];
        }
      }
      return $time; 
    } else {
      return $object["base_changed"];
    }
  }

  function QRY_DMS_Published_v1($sgn, $object) {
    // NB: duurder dan meeste andere QRY-queries
    // NB: indexen die deze query gebruiken, worden VERWIJDERD iedere keer dat er aan een workflow wordt geprutst.
    if ($object["objecttype"]=="shortcut") {
      $object = MB_Load("ims_{$sgn}_objects", $object["source"]);
    }

    if ($object["objecttype"]=="document") {
      $workflow = MB_Load("shield_{$sgn}_workflows", $object["workflow"]);
      $laststage = $workflow["stages"];
      foreach ($object["history"] as $id => $hist) {
        if (($hist["newstage"] == $laststage && $hist["oldstage"] != $laststage) || 
             $hist["type"] == "forcedpublish" ||
             (($hist["type"] == "edit" || $hist["type"] == "new" || $hist["type"] == "") && $hist["published"] == "yes")) {
          $time = $hist["when"];
       }
      }
      return $time; 
    }
  }

  function QRY_DMS_Created_v1 ($object) { // LF20090609
    if ($object["objecttype"]=="document") {
      if (is_array($object["history"])) {
        reset ($object["history"]);
        $guid = key($object["history"]); // first element
        return $object["history"][$guid]["when"];
      }
    }
    return 0;
  }

  function QRY_DMS_Assigned_v1 ($sgn, $object) {
    if ($object["objecttype"]=="document") {
      if ($object["allocto"]) {
        $user_id = $object["allocto"];
        $user = MB_Load ("shield_".$sgn."_users", $user_id);     
        return $user["name"];
      }
    } else {
      return $object["base_assigned"];
    }
  }

  function QRY_DMS_MultiApprovable_v1 ($sgn, $object) {
    // Check if the object is in a workflow stage which has multiple approval.
    // Also checks if there is at least one user who still needs to approve it.
    // Returns true or false
    // Note: this index should be deleted when the workflow is edited!
    if ($object["objecttype"]=="document") {
      if (!($object["published"]=="yes" || $object["preview"]=="yes")) return 0;
      $workflow = MB_Load("shield_{$sgn}_workflows", $object["workflow"]);
      $stage = $object["stage"];
      if ($workflow["multiapprove"][$stage]["choices"]) {
        foreach ($object["multiapprove"] as $user => $status) {
          if ($status == "x") return true; // We assume non user fields (e.g. enddate and remind) never contain an "x".
        }
      }


    }
    return false;
  }

  function QRY_DMS_CaseId_v1($object) {
    $folder_id = $object["directory"];
    if (substr ($folder_id, 0, 1)=="(") {
      $p = strpos ($folder_id, ")");
      $case_id = substr ($folder_id, 0, $p+1);
      return $case_id;
    } else {
      return false;
    }
  }

  function QRY_BPMS_AssignedToUser_v1 ($supergroupname, $theprocess, $case_id, $user_id) {}
  function QRY_BPMS_AssignedToUser_v2 ($supergroupname, $theprocess, $case_id, $user_id, $object)
  {
    $process = MB_Load ("shield_".$supergroupname."_processes", $theprocess);
    if ($user_id != $object["alloc"]) return false;
    if ($process[$object["stage"]]["type"]=="end") return false;
    return true;         
  }

  function QRY_BPMS_AssignedUserName ($supergroupname, $theprocess, $case_id)
  {
    $object = MB_Ref ("process_".$supergroupname."_cases_".$theprocess, $case_id);
    $user_id = $object["alloc"];
    $user = MB_Ref ("shield_".$supergroupname."_users", $user_id);
    return $user["name"];
  }

  function QRY_BPMS_LastUpdate ($supergroupname, $theprocess, $case_id)
  {
    $object = &MB_Ref ("process_".$supergroupname."_cases_".$theprocess, $case_id);
    $lastupdate=0;
    if (is_array ($object)) if (is_array($object["history"])) foreach ($object["history"] as $dummy => $data) {
      if ($data["when"] > $lastupdate) $lastupdate=$data["when"];
    }
    return $lastupdate;
  }

  function QRY_BPMS_Created_v1 ($object)
  {
    if (is_array($object["history"])) {
      reset ($object["history"]);
      $guid = key($object["history"]); // first element
      return $object["history"][$guid]["when"];
    }
    return 0;
  }

  function rectime ($rec)
  {
    $max = 0;
    if (is_array($rec["history"])) {
      foreach ($rec["history"] as $guid => $spec) {
        if ($spec["when"] > $max) {
            $max = $spec["when"];
        }
      }
    }
    return $max;
  }

  function QRY_ProcessAlertTest_v1 ($sgn, $process_id, $object) 
  {
    $process = MB_Ref ("shield_".$sgn."_processes", $process_id);
    if ($process["alerts"][$object["stage"]]["active"]) {
      $max = 0;
      if (is_array($object["history"])) {
        foreach ($object["history"] as $guid => $spec) {
          if ($spec["when"] > $max) $max = $spec["when"];
        }
      }
      if ($max) {
        $ret = $max+$process["alerts"][$object["stage"]]["days"]*24*3600;
      } else {
        $ret = 2000000000;
      } 
    } else {
      $ret = 2000000000;
    }
    return $ret;
  }

  function QRY_AlertTest_v4 ($sgn, $object)
  {
    $workflow = MB_Ref ("shield_".$sgn."_workflows", $object["workflow"]);
    if ($workflow["alerts"][$object["stage"]]["active"]) {
      $days = $workflow["alerts"][$object["stage"]]["days"];
      $daysfield = $workflow["alerts"][$object["stage"]]["daysfield"]; // an object can have a field to determine the number of days for alert (overruling the workflow setting)
      if ($daysfield && $object["meta_".$daysfield]) $days = $object["meta_".$daysfield];

      $max = 0;
      if (is_array($object["history"])) {
        foreach ($object["history"] as $guid => $spec) {
          if ($spec["when"] > $max) $max = $spec["when"];
        }
      }
      if ($max) {
        $ret = $max+$days*24*3600;
      } else {
        $ret = 2000000000;
      } 
    } else {
      $ret = 2000000000;
    }
    return $ret;
  }
  function QRY_AlertTest_v1 ($sgn, $key) {}
  function QRY_AlertTest_v2 ($object) {}
  function QRY_AlertTest_v3 ($sgn, $object) {}

  function QRY_RecentlyChangedDocuments_v1 ($record) {
    if ($record["objecttype"]!="document") return 0;
    if (!($record["published"]=="yes" || $record["preview"]=="yes")) return 0;
    if (is_array($record["history"])) {
      foreach ($record["history"] as $guid => $spec) {
        $max = $spec["when"];
      }
    }
    return $max;
  }

  function QRY_RecentlyChangedBy_v1 ($record) {
    if ($record["objecttype"]!="document") return "";
    if (!($record["published"]=="yes" || $record["preview"]=="yes")) return "";
    if (is_array($record["history"])) {
      foreach ($record["history"] as $guid => $spec) {
        {
          $max = $spec["when"];
          $who = $spec["author"];
        }
      }
    }
    return $who;
  }

  function QRY_RecentlyChangedWebpages_v2 ($record) {
    if ($record["objecttype"]!="webpage") return 0;
    if (!($record["published"]=="yes" || $record["preview"]=="yes")) return 0;
    if (is_array($record["history"])) {
      foreach ($record["history"] as $guid => $spec) {
        if ($spec["when"] > $max) $max = $spec["when"];
      }
    }
    return $max;
  }
  function QRY_RecentlyChangedWebpages ($record) {}

  function QRY_LongAgoChangedWebpages ($record) {
    if ($record["objecttype"]!="webpage") return 0;
    if (!($record["published"]=="yes" || $record["preview"]=="yes")) return 0;
    if (is_array($record["history"])) {
      foreach ($record["history"] as $guid => $spec) {
        if ($spec["when"] > $max) $max = $spec["when"];
      }
    }
    return time()-$max+1;
  }

  function QRY_ListMetaDataValues ($sgn,$record) {
    if ($record["objecttype"]!="document") return "";

  // this code assumes $record is a DMS IMS_AccesObject
    $keywords = $record["longtitle"]." ";
    foreach ($record as $tag => $value) {
      if (substr ($tag, 0, 5)=="meta_") {
        $keywords .= $value." ";
        $field = substr ($tag, 5);
        uuse("ims");
        IMS_SetSuperGroupName ($sgn);
        $keywords .= FORMS_ShowValue ($value, $field, $record, $record)." ";
      }
    }
    return $keywords; 
  }

  function QRY_SmartSort_v1($mixedtext) {  
    // LF20090219: For sorting strings such as STD1, STD9, STD10
    return preg_replace('/([0-9]+\.?[0-9]*)/e', '"#" . MB_Comparable("$1") . "#"', $mixedtext);  
  }

  function QRY_SlowFilter($haystack, $needle) {
    // Returns true if every word in $needle occurs somewhere in $haystack (not necessarily in the same order as in $needle)
    // !!! Do not ever use in a "select" or "sort", it will result in a non-deterministic query!
    // !!! Do not use in FK_Edit or in autotables, actually, do not use in any string unless you know exactly where/how/why this string will be eval'd!
    // !!! Do not use in a "slowselect" either, a "filter" can probably do what you want but faster.
    uuse("search");

    if (!@strlen(trim($needle))) { // allow "0"
      $needle = null;
    } else {
      $words = explode (" ", MB_Text2Words ($needle, true));
    }

    $expressionvalue = MB_Text2Words($haystack, true);
    $ok = true;
    foreach ($words as $dummy => $word) {
      if (@strlen($word) && !strpos (" ".$expressionvalue, $word)) $ok = false;
    }
    return $ok;
  }

  function QRY_CMS_Published_v1($sgn, $object) {
    // NB: duurder dan meeste andere QRY-queries
    // NB: indexen die deze query gebruiken, worden VERWIJDERD iedere keer dat er aan een workflow wordt geprutst.

    if ($object["objecttype"]=="webpage") {
      $workflow = MB_Load("shield_{$sgn}_workflows", $object["workflow"]);
      $laststage = $workflow["stages"];
      foreach ($object["history"] as $id => $hist) {
        if (($hist["newstage"] == $laststage && $hist["oldstage"] != $laststage) || 
             $hist["type"] == "forcedpublish" ||
             (($hist["type"] == "edit" || $hist["type"] == "new" || $hist["type"] == "") && $hist["published"] == "yes")) {
          $time = $hist["when"];
       }
      }
      return $time; 
    }
  }

  function QRY_FindArrayKeysWithValue($array, $value) {
    $result = array();
    foreach ($array as $key => $v) {
      if ($value."" == $v."") $result[] = $key;
    }
    return $result;
  }

  function QRY_CategoryName_v1 ($sgn, $object)
  {
    return MB_Fetch("ims_${sgn}_case_types", $object["category"], "name");
  }

?>
