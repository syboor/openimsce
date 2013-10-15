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
  
*** TERRA HIGH VOLUME DATA PROCESSING ***
 
Terra elements: 
  logic     php code defining what to do
  step      logic is devided into steps which should each take as little time as possible
  process   active instance executing logic
  session   small php call (http request) executing a few steps of a process
  tdata     special object containing all data during the entire process
  queue     unlimited size object queue

Terra modes:
  ""            regular execution, no feedback at all
  "feedback"    show feedback messages (status)
  "debug"       show debug messages
  "localdebug"  use direct execution (no memory saving through http localloop)

Terra ($tdata) internal values:
  terra_max_session_time   overrules default TERRA_MAX_SESSION_TIME
  terra_end                set to "yes" if process has completed 
  terra_logic              name of the process logic (e.g. "Megacount")
  terra_process_id         terra process id (data is stored in ims_process_data)
  terra_mode               contains the active terra operation mode
  terra_debugtext          text queue for debug messages (needed for "debug" mode, "localdebug" uses echo instead)
  terra_feedbacktext       text queue for feedback messages (needed for "debug" and "feedback" mode, "localdebug" uses echo instead)
  terra_status             contains the current status
  terra_step               step counter
  terra_session            session counter
  terra_starttime          timestamp of process start (unix seconds)

*/


/*******************************************************************************************************************************/
/*** TERRA - EXECUTION ENGINE ***/

if (!defined ("TERRA_MAX_SESSION_TIME")) define ("TERRA_MAX_SESSION_TIME", 10); // max time per session in seconds


/*******************************************************************************************************************************/
/*** TERRA - ENGINE ***/

function TERRA_Continue()  // determine max amount of time per batch processing call
{
  global $tdata;
  global $terra_extra;  
  if ($tdata["terra_max_session_time"]) {
    return ((N_Elapsed ()-$terra_extra) < $tdata["terra_max_session_time"]);
  } else {
    return ((N_Elapsed ()-$terra_extra) < TERRA_MAX_SESSION_TIME);
  }
}

function TERRA_Reset() 
{
  global $terra_extra;  
  $terra_extra = N_Elapsed ();
}

function TERRA_SaveState ($process_id, $data)
{
  MB_Flush();
  $data["lastupdate"] = time();
  MB_REP_Save ("local_terra_processdata", $process_id, $data);
}

function TERRA_LoadState ($process_id)
{
  return MB_REP_Load ("local_terra_processdata", $process_id);
}

function TERRA_Active ($process_id)
{
  if ($tdata = TERRA_LoadState ($process_id)) {
    if ($tdata["terra_end"]!="yes" && $tdata["terra_logic"]) {
      return true;
    } else {
      return false;
    }
  } else {
    return false;
  }
}

function TERRA_NoPause ($process_id)
{
  $pause = MB_REP_Load ("globalvars", "processpause");
  if ($pause == "yes") return false;
  $pause = MB_REP_Load ("local_terra_processpausedata", $process_id);  
  if ($pause == "yes") return false;
  return true;
}

function TERRA_DeleteProcess($process_id)
{
  MB_Flush();
  N_Log ("terra", "delete process $process_id");
  MB_Delete ("local_terra_processdata", $process_id);
  MB_REP_Delete ("local_terra_processdata", $process_id);
}

function TERRA_EndProcess($process_id="")
{
  MB_Flush();
  if ($process_id) {
    N_Log ("terra", "end process $process_id");
    $data = TERRA_LoadState ($process_id);
    $data["terra_end"] = "yes";    
    TERRA_SaveState ($process_id, $data);
  } else {
    global $tdata;
    N_Log ("terra", "end process ".$tdata["terra_process_id"]);
    $tdata["terra_end"] = "yes";
  }
}

function TERRA_DoProcess ($process_id)
{
  if (!TERRA_NoPause ($process_id)) return;
  $data = TERRA_LoadState ($process_id);
  TERRA_SaveState ($process_id, $data); // update lastupdated counter
  $mode = $data["terra_mode"];
  $logic = $data["terra_logic"];
  $status = $data["terra_status"];

  N_Log ("terra", "(re)start CHILD process $process_id ($logic) $status");
  URPC1 (N_CurrentServer(), 'uuse("terra"); TERRA_Slave_ExecuteProcess ($input["process_id"]);',array("process_id"=>$process_id)); 

  if (TERRA_Active ($process_id)) {
    N_Log ("terra", "(re)start PARENT process $process_id ($logic) $status");
    URPC_ExecuteInBackground_Now ('uuse ("terra"); TERRA_DoProcess ($input);', $process_id);
  } else {
    TERRA_DeleteProcess ($process_id);
  }
}

function TERRA_DoProcess_OLD ($process_id)
{
  $data = TERRA_LoadState ($process_id);
  TERRA_SaveState ($process_id, $data); // update lastupdated counter
  $mode = $data["terra_mode"];
  $logic = $data["terra_logic"];
  $status = $data["terra_status"];
  N_Log ("terra", "(re)start process $process_id ($logic) $status");
  if ($mode=="debug" || $mode=="localdebug") {
    echo "DEBUG: process_id=$process_id<br>";
  }
  TERRA_Master_ExecuteProcess ($process_id, $mode);
  $data = TERRA_LoadState ($process_id);
  if ($mode) echo "STATUS (".$data["terra_logic"]." ".N_VisualDate (time(), true)."): " . $data["terra_status"] . "<br>";
  TERRA_DeleteProcess($process_id);
}

function TERRA_CreateProcess($logic, $data=array(), $mode="")
{
  N_Debug ("TERRA_CreateProcess($logic, ..., $mode)");
  set_time_limit (0);
  ignore_user_abort (1); 
  $process_id = N_GUID();
  if ($data["from"]) {
    $process_id .= "_f_".$data["from"];
  }
  if ($data["from"]) {
    $process_id .= "_t_".$data["to"];
  }
  $data["terra_logic"] = $logic;
  $data["terra_process_id"] = $process_id;
  $data["terra_mode"] = $mode;
  $data["terra_starttime"] = time();
  TERRA_SaveState ($process_id, $data);
  N_Log ("terra", "create process $process_id");
  TERRA_DoProcess ($process_id);
}

function TERRA_CreateBackgroundProcess($logic, $data=array(), $mode="")
{
  N_Debug ("TERRA_CreateBackgroundProcess($logic, ..., $mode)");

  N_DoNotDisturb();

  set_time_limit (0);
  ignore_user_abort (1); 
  $process_id = N_GUID();
  if ($data["from"]) {
    $process_id .= "_f_".$data["from"];
  }
  if ($data["to"]) {
    $process_id .= "_t_".$data["to"];
  }
  $data["terra_logic"] = $logic;
  $data["terra_process_id"] = $process_id;
  $data["terra_mode"] = $mode;
  $data["terra_starttime"] = time();
  TERRA_SaveState ($process_id, $data);
  N_Log ("terra", "create background process $process_id");
  URPC_ExecuteInBackground_Now ('uuse ("terra"); TERRA_DoProcess ($input);', $process_id);
  return $process_id;
}

function TERRA_ProcessStatus ($process_id)
{
  $data = TERRA_LoadState ($process_id);
  if ($data) {
    if ($data["terra_end"]!="yes") {
      return "Process: ".$data["terra_logic"]."-$process_id, status: ".$data["terra_status"]. " (last status update: ".(time()-$data["lastupdate"])." seconds ago)";
    }  
  }
  return "Process $process_id has ended";
}  

function TERRA_Master_ExecuteProcess ($process_id, $mode="") // function calling TERRA_Slave_ExecuteProcess (using HTTP) until the process dies
{
  while (TERRA_Active ($process_id) && TERRA_NoPause ($process_id)) {
    if ($mode=="localdebug") {
      TERRA_Slave_ExecuteProcess ($process_id);
      TERRA_Reset();
    } else {
      URPC1 (N_CurrentServer(), 'uuse("terra"); TERRA_Slave_ExecuteProcess ($input["process_id"]);',array("process_id"=>$process_id));
    }
    if ($mode!="") {
      $tdata = TERRA_LoadState ($process_id);
      if ($mode=="debug" || $mode=="feedback") {
        echo $tdata["terra_feedbacktext"];
        N_Flush();
        $tdata["terra_feedbacktext"] = "";
        TERRA_SaveState ($process_id, $tdata);
      }
      if ($mode=="debug") {
        echo $tdata["terra_debugtext"];
        N_Flush();
        $tdata["terra_debugtext"] = "";
        TERRA_SaveState ($process_id, $tdata);
      }
      if ($mode) echo "STATUS (".$tdata["terra_logic"]." ".N_VisualDate (time(), true)."): " . $tdata["terra_status"] . "<br>";
      N_Flush();
    }
  }
}

function TERRA_FLush ()
{
  global $tdata;
  global $activeprocess;
  if (TERRA_Active ($activeprocess)) { // in case someone nuked our process
    TERRA_SaveState ($activeprocess, $tdata);
  }
}

function TERRA_Crashed ()
{
  global $tdata;
  return $tdata["terra_before"] - $tdata["terra_after"] - 1;
}

function TERRA_Slave_ExecuteProcess ($process_id) // function executing steps until time runs out
{
  set_time_limit (8*3600); // 8 hours
  global $tdata;
//  global $myconfig;
  global $activeprocess;
  $activeprocess = $process_id;
//  $myconfig["backgroundfulltextindex"] = "no";
  if (TERRA_Active ($process_id)) {
    $tdata = TERRA_LoadState ($process_id);
    $tdata["terra_before"]++;
    TERRA_SaveState ($process_id, $tdata);
    $tdata["terra_session"]++;
    // at least set 1 step
    $tdata["terra_step"]++;
    N_Log ("terra", "slave execute process $process_id");
    TERRA_ExecuteStep ();
    while (TERRA_Continue() && TERRA_Active($process_id) && $tdata["terra_end"]!="yes" && TERRA_NoPause ($process_id)) {
      if (1 == rand (1, 10)) TERRA_SaveState ($process_id, $tdata); // sometimes a process gets lucky and saves some data (needed for crash loops to prevent unlimited init calls) 
      $tdata["terra_step"]++;
      TERRA_ExecuteStep ();
    }
    if (TERRA_Active ($process_id)) { // in case someone nuked our process
      $tdata["terra_after"]++;
      TERRA_SaveState ($process_id, $tdata);
    }
  }
}

function TERRA_ExecuteStep ()
{
  global $tdata;
  SHIELD_SimulateUser(base64_decode ("dWx0cmF2aXNvcg==")); // make sure access right problems can not occur here
  N_PMLog ("pmlog_eval", "TERRA_ExecuteStep () TERRA_LOGIC_".$tdata["terra_logic"]."();");
  eval ("TERRA_LOGIC_".$tdata["terra_logic"]."();");
}

function TERRA_Revive ($maxage=3600) // 60 minutes
{
  if (!MB_INDEX_CheckFlag ("TERRA_Revive")) return;
  $processes = MB_Query ("local_terra_processdata");
  global $myconfig;
  if (is_array ($processes) && (N_TerraStatus ()=="not active") && N_LoadAllows ("revive")) { // selftest now properly tests if this is possible
    foreach ($processes as $process_id) {
      $data = TERRA_LoadState ($process_id);
      TERRA_SaveState ($process_id, $data); // update timestamp
      URPC_ExecuteInBackground_Now ('uuse ("terra"); TERRA_DoProcess ($input);', $process_id);
    }    
  } else {
    if (is_array ($processes) && N_LoadAllows ("revive")) {
      foreach ($processes as $process_id) {
        $data = TERRA_LoadState ($process_id);
        $age = time()-$data["lastupdate"];
        if ($age > $maxage) {
          TERRA_SaveState ($process_id, $data); // update timestamp
          URPC_ExecuteInBackground_Now ('uuse ("terra"); TERRA_DoProcess ($input);', $process_id);    
        }
      }
    }
  }
  MB_INDEX_RemoveFlag ("TERRA_Revive");
}


/*******************************************************************************************************************************/
/*** TERRA - QUEUE FUNCTIONS ***/

function TERRA_CreateQueue ($queue_id="")
{
  if (!$queue_id) $queue_id = N_GUID();
  $dir = getenv("DOCUMENT_ROOT")."/tmp/queue_".$queue_id;
  N_WriteFile ($dir."/inctr.dat", "0"); // last inserted object
  N_WriteFile ($dir."/outctr.dat", "0"); // last retrieved object
  return $queue_id;
}

function TERRA_DeleteQueue ($queue_id)
{
  N_AddModifyScedule ("delqueue $queue_id", time()+60, 'uuse ("terra"); TERRA_DoDeleteQueue ($input);', $queue_id);
}

function TERRA_DoDeleteQueue ($queue_id)
{
  N_Log ("terra_delqueue", "start delete $queue_id");
  $dir = getenv("DOCUMENT_ROOT")."/tmp/queue_".$queue_id;
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($dir);
  N_ErrorHandling (0);
  rmdir ($dir);
  N_ErrorHandling (1);
  N_Log ("terra_delqueue", "end delete $queue_id");
}

function TERRA_QueueAdd ($queue_id, $object)
{
  $dir = getenv("DOCUMENT_ROOT")."/tmp/queue_".$queue_id;
  $inctr = N_ReadFile ($dir."/inctr.dat");
  $inctr++;
  N_WriteFile ($dir."/".$inctr.".dat", serialize ($object));
  N_WriteFile ($dir."/inctr.dat", $inctr);
}

function TERRA_QueueRetrieve ($queue_id)
{
  TERRA_Flush();
  $dir = getenv("DOCUMENT_ROOT")."/tmp/queue_".$queue_id;
  $inctr = N_ReadFile ($dir."/inctr.dat");
  $outctr = N_ReadFile ($dir."/outctr.dat");  
  if ($outctr < $inctr)  {
    $outctr++;
    N_WriteFile ($dir."/outctr.dat", $outctr);
    return (unserialize (N_ReadFile ($dir."/".$outctr.".dat")));
  } else {
    return "";
  }
}

function TERRA_QueueRetrieve_Peek ($queue_id)
{
  TERRA_Flush();
  $dir = getenv("DOCUMENT_ROOT")."/tmp/queue_".$queue_id;
  $inctr = N_ReadFile ($dir."/inctr.dat");
  $outctr = N_ReadFile ($dir."/outctr.dat");  
  if ($outctr < $inctr)  {
    $outctr++;
    return (unserialize (N_ReadFile ($dir."/".$outctr.".dat")));
  } else {
    return "";
  }
}

function TERRA_QueueRetrieve_Confirm ($queue_id)
{
  TERRA_Flush();
  $dir = getenv("DOCUMENT_ROOT")."/tmp/queue_".$queue_id;
  $inctr = N_ReadFile ($dir."/inctr.dat");
  $outctr = N_ReadFile ($dir."/outctr.dat");  
  if ($outctr < $inctr)  {
    $outctr++;
    N_WriteFile ($dir."/outctr.dat", $outctr);
  }
  TERRA_Flush();
}


function TERRA_QueueEmpty ($queue_id)
{
  $dir = getenv("DOCUMENT_ROOT")."/tmp/queue_".$queue_id;
  $inctr = N_ReadFile ($dir."/inctr.dat");
  $outctr = N_ReadFile ($dir."/outctr.dat");  
  return (!($outctr < $inctr));
}

/*******************************************************************************************************************************/
/*** TERRA - SUPPORT FUNCTIONS ***/

function TERRA_Status ($status)
{
  global $tdata;
  $tdata["terra_status"] = $status;
  TERRA_Log ("status: ".$status);
}

function TERRA_Feedback ($message)
{
  global $tdata;
  if ($tdata["terra_mode"]=="debug" || $tdata["terra_mode"]=="feedback") {
    $tdata["terra_feedbacktext"] .= "FEEDBACK (".$tdata["terra_logic"]."): ".$message."<br>";   
  } else if ($tdata["terra_mode"]=="localdebug") {
    echo "FEEDBACK (".$tdata["terra_logic"]."): ".$message."<br>";
    N_Flush();
  }
}

function TERRA_Debug ($message)
{
  global $tdata;
  if ($tdata["terra_mode"]=="debug") {
    $tdata["terra_debugtext"] .= "DEBUG: ".$message."<br>";   
  } else if ($tdata["terra_mode"]=="localdebug") {
    echo "DEBUG: ".$message."<br>";
    N_Flush();
  }
  TERRA_Log ("debug: ".$message);
}

function TERRA_Log ($message)
{
  global $tdata;
  N_Log ("terra_".$tdata["terra_logic"], $tdata["terra_process_id"].": ".$message);
}

function TERRA_DirSize ($path) // get size of directory
{
  $path = N_CleanPath ($path);
  if (is_dir($path)) {
    if ($dh = opendir($path)) {
      while (($item = readdir($dh)) !== false) {
        if ($item!="." && $item!="..") $counter++;
      }
      closedir($dh);
    }
  }
  TERRA_Debug ("TERRA_DirSize ($path) -> $counter");
  return $counter;
}

function TERRA_DirSlice ($path, $start, $end) // get slice of directory
{
  TERRA_Debug ("TERRA_DirSlice ($path, $start, $end)");
  $path = N_CleanPath ($path);
  if (is_dir($path)) {
    if ($dh = opendir($path)) {
      while (($item = readdir($dh)) !== false) {
        if ($item!="." && $item!="..") $counter++;
        if ($counter>=$start && $counter<=$end) {
          $result[$counter] = $item;
        }
      }
      closedir($dh);
    }
  }  
  return $result;
}

function TERRA_ItemFromDir ($path, $index)
{
  global $TERRA_ItemFromDir;
  $path = N_CleanPath ($path);
  if (is_dir($path)) {
    if (!($TERRA_ItemFromDir[$path]["cachestart"] <= $index && $index <= $TERRA_ItemFromDir[$path]["cacheend"])) {
      if ($dh = opendir($path)) {
        $TERRA_ItemFromDir[$path]["cachestart"] = $index;
        $TERRA_ItemFromDir[$path]["cacheend"] = $index + 2500;
        for ($i=$TERRA_ItemFromDir[$path]["cachestart"]; $i<=$TERRA_ItemFromDir[$path]["cacheend"]; $i++) {
          $TERRA_ItemFromDir[$path]["cache"][$i - $TERRA_ItemFromDir[$path]["cachestart"]] = "";
        }
        while (($item = readdir($dh)) !== false) {
          if ($item!="." && $item!="..") { 
            $counter++;
            if ($TERRA_ItemFromDir[$path]["cachestart"] <= $counter && $TERRA_ItemFromDir[$path]["cacheend"] >= $counter) {
              $TERRA_ItemFromDir[$path]["cache"][$counter - $TERRA_ItemFromDir[$path]["cachestart"]] = $item;
            }
            if ($counter > $TERRA_ItemFromDir[$path]["cacheend"]) break; // save 50% time
          }
        }
        closedir($dh);
      }
    }
    $result = $TERRA_ItemFromDir[$path]["cache"][$index - $TERRA_ItemFromDir[$path]["cachestart"]];
  }  
  return $result;
} 

function TERRA_ItemFromTable ($table, $index, $timeborder="nope")
{
  if (!$timeborder) $timeborder="nope"; // undefined means don't use it
  return MB_MUL_OneKey ($table, $index, $timeborder);
}

function TERRA_InitMultiDir ($dirs, $server="")
{
  foreach ($dirs as $dummy => $dir) {
    $handle["coredirs"][++$ctr] = $dir;
  }
  $handle["current_coredir"] = 1;
  $handle["current_level"] = 1;  
  $handle[1]["next_item"] = 1;  
  $handle["server"] = $server;  
  return $handle;
}

function TERRA_MultiDir (&$handle)
{
  global $myconfig; $myconfig["32kfix"]=""; // do a raw directory copy
  if ($handle["current_level"]==0) { 
    return "";
  } else {
    if ($handle["server"]=="" || $handle["server"]==N_CurrentServer()) {
      if ($handle["current_level"]==1) { // root level
        $item = TERRA_ItemFromDir ($handle["coredirs"][$handle["current_coredir"]], $handle[1]["next_item"]);
        $handle[1]["next_item"]++;
        if ($item) {
          $item = N_CleanPath ($handle["coredirs"][$handle["current_coredir"]]."/".$item);
          if (is_file ($item)) {
            if (N_InternalPath ($handle["coredirs"][$handle["current_coredir"]])=="html::") {
              global $myconfig;
              $doit = true;
              foreach ($myconfig["coreskiprootfiles"] as $check) {
                if ("html::".$check==N_InternalPath($item)) $doit=false;
              }
              foreach ($myconfig["coreskiprootfilescontaining"] as $check) {
                if (strpos(N_HTMLPath($item), $check) !== false) $doit=false;
              }
              if (!$doit) {
                return TERRA_MultiDir ($handle);              
              }              
            }
            if (strpos (" ".$item, "t3mp")) return TERRA_MultiDir ($handle);              
            return $item;
          } else if (is_dir ($item)) {
            if (N_InternalPath ($handle["coredirs"][$handle["current_coredir"]])=="html::") {
              return TERRA_MultiDir ($handle);
            } else {
              $handle["current_level"]++;
              $handle[$handle["current_level"]]["dir"] = $item;
              $handle[$handle["current_level"]]["next_item"] = 1;
              return TERRA_MultiDir ($handle);
            }
          } else { // special case when it is not a file and it is not a directory (e.g. weird long path in Windows)
            N_Log ("errors", "TERRA_MultiDir found something that is neither file nor directory: $item");
            return TERRA_MultiDir ($handle);              
          }
        } else {
          $handle["current_coredir"]++;
          if ($handle["coredirs"][$handle["current_coredir"]]) {
            $handle["current_level"] = 1;  
            $handle[1]["next_item"] = 1;      
            return TERRA_MultiDir ($handle);
          } else {
            $handle["current_level"] = 0;
            return "";
          }
        }
      } else {
        $item = TERRA_ItemFromDir ($handle[$handle["current_level"]]["dir"], $handle[$handle["current_level"]]["next_item"]);
        $handle[$handle["current_level"]]["next_item"]++;
        if ($item) {
          $item = N_CleanPath ($handle[$handle["current_level"]]["dir"]."/".$item);
          if (is_file ($item)) {
            if (strpos (" ".$item, "t3mp")) return TERRA_MultiDir ($handle);
            return $item;
          }
          if (is_dir ($item)) {
            $handle["current_level"]++;
            $handle[$handle["current_level"]]["dir"] = $item;
            $handle[$handle["current_level"]]["next_item"] = 1;
            return TERRA_MultiDir ($handle);
          }
        } else {
          $handle["current_level"]--;
          return TERRA_MultiDir ($handle);      
        }
      }
    } else {
      $output = URPC1 ($handle["server"], 'uuse ("terra"); $output["result"] = TERRA_MultiDir ($input); $output["handle"] = $input;', $handle);
      $handle = $output["handle"];
      return $output["result"];      
    }
  }
} 

function TERRA_MultiDirPlus (&$handle, $amount=1, $precheck="")
{
  global $myconfig; $myconfig["32kfix"]=""; // do a raw directory copy

  // it would be better to do this when this function is called
  if (!$precheck && function_exists ("TERRA_ReplicateFilePrecheck")) {
    $precheck = TERRA_ReplicateFilePrecheck();
  }

  $result = array();
  if ($handle["current_level"]!=0) { 
    if ($handle["server"]=="" || $handle["server"]==N_CurrentServer()) {      
      if ($precheck) {
        $dummyfile = "html::/private/random.txt";
        $dummyfileinfo = N_FileInfo($dummyfile);
        while ($c<$amount) {
          $item = TERRA_MultiDir ($handle);
          if (!$item) break;
          $result[$dummyfile] = $dummyfileinfo; // ensure we dont end prematurely because precheck returns false all the time
          $objectid = "";
          $supergroup = "";
          $file = N_InternalPath ($item);
          $virtualfile = N_32kFix_UnTransform ($file);
          if (strpos ($virtualfile, "_sites/preview/objects/")) {
            $objectid = N_KeepBefore (N_KeepAfter ($virtualfile, "/preview/objects/"), "/");
            $supergroup = N_KeepBefore (N_KeepAfter ($virtualfile, "html::"), "/");
          } else if (strpos ($virtualfile, "_sites/objects/history/")) {
            $objectid = N_KeepBefore (N_KeepAfter ($virtualfile, "/objects/history/"), "/");
            $supergroup = N_KeepBefore (N_KeepAfter ($virtualfile, "html::"), "/");
          } else if (strpos ($virtualfile, "_sites/objects/")) {
            $objectid = N_KeepBefore (N_KeepAfter ($virtualfile, "/objects/"), "/");
            $supergroup = N_KeepBefore (N_KeepAfter ($virtualfile, "html::"), "/");
          }
          N_PMLog ("pmlog_eval", "TERRA_MultiDirPlus precheck", $precheck);
          eval ($precheck);
          global $tdata;
          if ($ok) {
            $result [N_InternalPath ($item)] = N_FileInfo ($item);
            //$c++; // LF20120210: moved from here to fix out-of-memory problem
            $tdata["precheck_ok"]++;
          } else {
            $tdata["precheck_notok"]++;
          }
          $c++; // LF20120210: moved to here
        }
      } else {
        for ($i=0; $i<$amount; $i++)
        {
          $item = TERRA_MultiDir ($handle);
          if ($item) {
            $result [N_InternalPath ($item)] = N_FileInfo ($item);
          }
        }
      }
    } else {
      $output = URPC1 ($handle["server"], '
        uuse ("terra"); $output["result"] = TERRA_MultiDirPlus ($input["handle"], $input["amount"], $input["precheck"]); $output["handle"] = $input["handle"];', 
        array("handle"=>$handle, "amount"=>$amount, "precheck"=>$precheck));
      $handle = $output["handle"];
      $result = $output["result"];
    }
  }
  return $result;
}

function TERRA_MultiDirCompare ($objects, $server="") // see which objects have to be send to $server
{
  global $myconfig; $myconfig["32kfix"]=""; // do a raw directory copy
  if ($server=="" || $server==N_CurrentServer()) {
    $result = array();
    foreach ($objects as $path => $remotespecs) {
      $localspecs = N_FileInfo ($path);
      if ($localspecs["md5b"] != $remotespecs["md5b"]) {
        if ($localspecs["age"] >= $remotespecs["age"]) { // if object on $server is older
          $result[++$ctr] = $path;
        }
      }
    }
    return $result;
  } else {
    return URPC1 ($server, 'uuse ("terra"); $output = TERRA_MultiDirCompare ($input);', $objects);
  }  
} 

function TERRA_SendFile ($to, $from, $file)
{
  global $myconfig; $myconfig["32kfix"]=""; // do a raw directory copy
  if (function_exists("TERRA_ReplicateFile")) {
    if (strpos ($file, "_sites/preview/objects/")) {
      $objectid = N_KeepBefore (N_KeepAfter ($file, "/preview/objects/"), "/");
      $supergroup = N_KeepBefore (N_KeepAfter ($file, "html::"), "/");
    } else if (strpos ($file, "_sites/objects/history/")) {
      $objectid = N_KeepBefore (N_KeepAfter ($file, "/objects/history/"), "/");
      $supergroup = N_KeepBefore (N_KeepAfter ($file, "html::"), "/");
    } else if (strpos ($file, "_sites/objects/")) {
      $objectid = N_KeepBefore (N_KeepAfter ($file, "/objects/"), "/");
      $supergroup = N_KeepBefore (N_KeepAfter ($file, "html::"), "/");
    }
    if (!TERRA_ReplicateFile ($to, $from, $file, $supergroup, $objectid)) { 
      TERRA_Log ("Skipping file from $from to $to ($file)");
      return;
    }
  } // qqq
  TERRA_FeedBack ("Sending file from $from to $to ($file)");
  TERRA_Log ("Sending file from $from to $to ($file)");
  if ($from==N_CurrentServer()) {
    $content = N_ReadFile ($file);
  } else {
    $content = URPC1 ($from, 'global $myconfig; $myconfig["32kfix"]=""; $output = N_ReadFile ($input);', $file);
  }
  if ($to==N_CurrentServer()) {
    N_WriteFile ($file, $content);
    N_Touch ($file, time()-365*24*3600);
  } else {
    URPC1 ($to, '  global $myconfig; $myconfig["32kfix"]=""; N_WriteFile ($input["file"], $input["content"]); N_Touch($input["file"], time()-365*24*3600);', array ("file"=>$file, "content"=>$content));
  }
}

function TERRA_InitMultiTable ($tables, $server="", $fastmode=false, $timeborder="nope") // fastmode disables MD5 calculations
{
  foreach ($tables as $dummy => $table) {
    $handle["tables"][++$ctr] = $table;
    MB_MUL_Delete ($table, "");
    MB_MUL_Delete ($table, 0);
    MB_MUL_Delete ($table, "0");
    MB_MUL_Delete ($table, " ");
    MB_MUL_Delete ($table, "_");
  }
  $handle["fastmode"] = $fastmode;
  $handle["current_table"] = 1;
  $handle["next_item"] = 1;  
  $handle["timeborder"] = $timeborder;
  $handle["server"] = $server;
  return $handle;
}

function TERRA_MultiTable (&$handle)
{
  $result = array();
  if ($handle["current_table"]) {
    if ($handle["server"]=="" || $handle["server"]==N_CurrentServer()) {
      $item = TERRA_ItemFromTable ($handle["tables"][$handle["current_table"]], $handle["next_item"], $handle["timeborder"]);
      $handle["next_item"]++;
      if ($item) {
        if (strpos ($item, "t3mp")) {
          $result = TERRA_MultiTable ($handle);
        } else {
          $result = array("table"=>$handle["tables"][$handle["current_table"]], "key"=>$item);
        }
      } else {
        $handle["current_table"]++;
        if ($handle["tables"][$handle["current_table"]]) {
          $handle["next_item"] = 1;  
          $result = TERRA_MultiTable ($handle);
        } else {
          $handle["current_table"] = 0;
        }
      }
    } else {
      $output = URPC1 ($handle["server"], 'uuse ("terra"); $output["result"] = TERRA_MultiTable ($input); $output["handle"] = $input;', $handle);
      $handle = $output["handle"];
      $result = $output["result"];
    }
  }
  return $result;
}

function TERRA_MultiTablePlus (&$handle, $amount=1, $precheck="")
{
  N_Debug ("TERRA_MultiTablePlus (..., $amount)");

  // it would be better to do this when this function is called
  if (!$precheck && function_exists ("TERRA_ReplicateObjectPrecheck")) {
    $precheck = TERRA_ReplicateObjectPrecheck();
  }
  $result = array();
  if ($handle["current_table"]) {
    if ($handle["server"]=="" || $handle["server"]==N_CurrentServer()) {
      if ($precheck) {
        while ($ctr<$amount) {
          $item = TERRA_MultiTable ($handle);
          if (!$item) break;
          $table = $item["table"];
          $key = $item["key"];
          N_PMLog ("pmlog_eval", "TERRA_MultiTablePlus precheck", $precheck);
          eval ($precheck);
          if ($ok) {
            $result [++$ctr] = $item;
            if (!$handle["fastmode"]) {
              $object = MB_MUL_Load ($item["table"], $item["key"]);
              $result [$ctr]["md5"] = md5 (serialize ($object["data"]));
              $result [$ctr]["age"] = time() - $object["time"];
            }
          }                 
        }
      } else {      
        for ($i=0; $i<$amount; $i++)
        {
          $item = TERRA_MultiTable ($handle);
          if ($item) {
            $result [++$ctr] = $item;
            if (!$handle["fastmode"]) {
              $object = MB_MUL_Load ($item["table"], $item["key"]);
              $result [$ctr]["md5"] = md5 (serialize ($object["data"]));
              $result [$ctr]["age"] = time() - $object["time"];
            }
          }
        }
      }
    } else {
      $output = URPC1 ($handle["server"], '
        uuse ("terra"); $output["result"] = TERRA_MultiTablePlus ($input["handle"], $input["amount"], $input["precheck"]); $output["handle"] = $input["handle"];',
        array("handle"=>$handle, "amount"=>$amount, "precheck"=>$precheck));
      $handle = $output["handle"];
      $result = $output["result"];
    }
  }
  return $result;
}

function TERRA_MultiTableCompare ($objects, $server="") // see which objects have to be send to $server
{
  N_Debug ("TERRA_MultiTableCompare (..., $server)");
  if ($server=="" || $server==N_CurrentServer()) {
    $result = array();
    foreach ($objects as $dummy => $objectspecs) {
      $object = MB_MUL_Load ($objectspecs["table"], $objectspecs["key"]);
      if (!$object) { // if object does nog exist on $server
        $result[++$ctr]["table"] = $objectspecs["table"];
        $result[$ctr]["key"] = $objectspecs["key"];
      } else if ($objectspecs["md5"] != md5 (serialize ($object["data"]))) {
        $localage = time() - $object["time"];
        if ($localage >= $objectspecs["age"]) { // if object on $server is older
          $result[++$ctr]["table"] = $objectspecs["table"];
          $result[$ctr]["key"] = $objectspecs["key"];
        }
      }
    } 
    return $result;
  } else {
    return URPC1 ($server, 'uuse ("terra"); $output = TERRA_MultiTableCompare ($input);', $objects);
  }
}


function TERRA_SendObject ($to, $from, $table, $key) 
{
  if (function_exists ("TERRA_ReplicateObject")) {
    if (!TERRA_ReplicateObject ($to, $from, $table, $key)) {
      TERRA_Log ("Skipping XML object from $from to $to (table: $table key: $key)");
      return;
    } // qqq
  }
  TERRA_FeedBack ("Sending XML object from $from to $to (table: $table key: $key)");
  TERRA_Log ("Sending XML object from $from to $to (table: $table key: $key)");
  if ($from==N_CurrentServer()) {
    $object = MB_MUL_Load ($table, $key);
  } else {
    $object = URPC1 ($from, '$output = MB_MUL_Load ($input["table"], $input["key"]);', array ("table"=>$table, "key"=>$key));
  }
  if ($to==N_CurrentServer()) {
    MB_MUL_Save ($table, $key, $object);
  } else {
    URPC1 ($to, 'MB_MUL_Save ($input["table"], $input["key"], $input["object"]);', array ("table"=>$table, "key"=>$key, "object"=>$object));
  }
}

function TERRA_SendObjects ($to, $from, $tables, $keys) 
{
  for ($i=1; $i<=count($tables); $i++) {
    TERRA_FeedBack ("Sending XML object from $from to $to (table: ".$tables[$i]." key: ".$keys[$i].")");
    TERRA_Log ("Sending XML object from $from to $to (table: ".$tables[$i]." key: ".$keys[$i].")");
  }
  if ($from==N_CurrentServer()) {
    for ($i=1; $i<=count($tables); $i++) {
      $objects[$i] = MB_MUL_Load ($tables[$i], $keys[$i]);
    }
  } else {
    $objects = URPC1 ($from, '
      $tables = $input["tables"];
      $keys = $input["keys"];    
      for ($i=1; $i<=count($tables); $i++) {
        $output[$i] = MB_MUL_Load ($tables[$i], $keys[$i]);
      }
    ', array ("tables"=>$tables, "keys"=>$keys));
  }
  if ($to==N_CurrentServer()) {
    for ($i=1; $i<=count($tables); $i++) {
      MB_MUL_Save ($tables[$i], $keys[$i], $objects[$i]);
    }
  } else {
    URPC1 ($to, '
      $tables = $input["tables"];
      $keys = $input["keys"];    
      $objects = $input["objects"];    
      for ($i=1; $i<=count($tables); $i++) {
        MB_MUL_Save ($tables[$i], $keys[$i], $objects[$i]);
      }
    ', array ("tables"=>$tables, "keys"=>$keys, "objects"=>$objects));    
  }
}


/*******************************************************************************************************************************/
/*** TERRA LOGIC ***/

// e.g. TERRA_CreateProcess ("Sleep", array("seconds"=>60), "feedback");

function TERRA_LOGIC_Sleep ()
{
  global $tdata;
  if ($tdata["counter"] < $tdata["seconds"]) {
    $tdata["counter"]++;
    TERRA_Feedback ($tdata["counter"]);
    TERRA_Log ($tdata["counter"]);
    N_Sleep (1000);    
  } else {
    TERRA_EndProcess();
  }
  TERRA_Status ($tdata["counter"]."/".$tdata["seconds"]);
}


// e.g. TERRA_CreateProcess ("SendData", array("from"=>"nicohome", "to"=>"nicolaptop", "tables"=>array("test")), "feedback");

function TERRA_LOGIC_SendData()
{
  global $tdata;

  if (!$tdata["mode"]) {

    // INIT
    TERRA_Debug ("init");
    $tdata["mode"] = "scanning";
    $tdata["queue"] = TERRA_CreateQueue ();
    $tdata["handle"] = TERRA_InitMultiTable ($tdata["tables"], $tdata["from"]);

  } else if ($tdata["mode"]=="scanning") {

    // SCAN TABLES
    $objects = TERRA_MultiTablePlus ($tdata["handle"], 100 );// 1000
    if ($objects) {
      $tdata["scanned"] += count ($objects);
      $action = TERRA_MultiTableCompare ($objects, $tdata["to"]);
      foreach ($action as $dummy => $specs) {
        if (function_exists ("TERRA_ReplicateObject")) {
          if (TERRA_ReplicateObject ($tdata["to"], $tdata["from"], $specs["table"], $specs["key"])) { 
            TERRA_QueueAdd ($tdata["queue"], $specs);
          } else {
            TERRA_Log ("Skipping XML object from ".$tdata["from"]." to ".$tdata["to"]." (table: ".$specs["table"]." key: ".$specs["key"].")");
          } 
        } else {
          TERRA_QueueAdd ($tdata["queue"], $specs);
        } // qqq
      }
    } else {
      $tdata["mode"] = "copying";
    }

  } else if ($tdata["mode"]=="copying") {    

    // COPY OBJECTS
    $specs = TERRA_QueueRetrieve ($tdata["queue"]);
    if ($specs) {
      $tables[1] = $specs["table"];
      $keys[1] = $specs["key"];
      for ($i=2; $i<=100; $i++) {
        $specs = TERRA_QueueRetrieve ($tdata["queue"]);
        if ($specs) {
          $tables[$i] = $specs["table"];
          $keys[$i] = $specs["key"];          
        }
      }
      TERRA_SendObjects ($tdata["to"], $tdata["from"], $tables, $keys);
      // qqq KNOWN BUG WILL SKIP RECORDS IF IT FAILS
      $tdata["sent"]+= count ($tables);
    } else {
      TERRA_DeleteQueue ($tdata["queue"]);
      TERRA_EndProcess();
    }

  }
  TERRA_Status ("scanned: ".(0+$tdata["scanned"])." sent: ".(0+$tdata["sent"]));
}

// e.g. TERRA_CreateProcess ("SendFiles", array("from"=>"nicohome", "to"=>"nicolaptop", "dirs"=>array("test")), "feedback");

function TERRA_LOGIC_SendFiles () 
{ 
  global $myconfig; $myconfig["32kfix"]=""; // do a raw directory copy
  global $tdata;
  if (!$tdata["mode"]) {

    // INIT
    TERRA_Debug ("init");
    $tdata["mode"] = "scanning";
    $tdata["queue"] = TERRA_CreateQueue ();
    $tdata["handle"] = TERRA_InitMultiDir ($tdata["dirs"], $tdata["from"]);

  } else if ($tdata["mode"]=="scanning") {

    // SCAN DIRECTORIES
    $objects = TERRA_MultiDirPlus ($tdata["handle"], 2500); // NDV
    if ($objects) {
      if ($tdata["precheck_notok"] || $tdata["precheck_ok"]) {
        $tdata["scanned"] += count ($objects) - 1; // ignore dummy object
      } else {
        $tdata["scanned"] += count ($objects);
      }
      $action = TERRA_MultiDirCompare ($objects, $tdata["to"]);
      foreach ($action as $dummy => $file) {
        TERRA_QueueAdd ($tdata["queue"], $file);
      }
    } else {
      $tdata["mode"] = "copying";
    }

  } else if ($tdata["mode"]=="copying") {    

    // COPY FILES
    $file = TERRA_QueueRetrieve_Peek ($tdata["queue"]);
    if ($file) {
      TERRA_SendFile ($tdata["to"], $tdata["from"], $file);
      TERRA_QueueRetrieve_Confirm ($tdata["queue"]);
      $tdata["sent"]++;
    } else {
      FLEX_RepairCache();
      TERRA_DeleteQueue ($tdata["queue"]);
      TERRA_EndProcess();
    }

  }
  if ($tdata["precheck_notok"] || $tdata["precheck_ok"]) {
    TERRA_Status ("scanned: ".(0+$tdata["scanned"])." pre ok: ".$tdata["precheck_ok"]." pre not ok: ".$tdata["precheck_notok"]." sent: ".(0+$tdata["sent"]));
  } else {
    TERRA_Status ("scanned: ".(0+$tdata["scanned"])." sent: ".(0+$tdata["sent"]));
  }

}

// e.g. TERRA_CreateProcess ("ReIndex", array("cols"=>array("demo_sites")), "localdebug");

function TERRA_LOGIC_ReIndex()
{
  uuse ("search");
  global $tdata;
  if (!$tdata["mode"]) {

    // INIT
    TERRA_Debug ("init");
    $tdata["mode"] = "scanning";
    $tdata["queue"] = TERRA_CreateQueue ();

  } else if ($tdata["mode"]=="scanning") {

    TERRA_Status ("scanning");

    foreach ($tdata["cols"] as $dummy => $sitecollection_id) {
      $sitelist = MB_Query ("ims_sites", '$record["sitecollection"]=="'.$sitecollection_id.'"');
      if (is_array ($sitelist)) reset($sitelist);
      if (is_array ($sitelist)) while (list ($site_id)=each($sitelist)) {
        TERRA_QueueAdd ($tdata["queue"], array ("type"=>"site", "col"=>$sitecollection_id, "site"=>$site_id));
      }
      TERRA_QueueAdd ($tdata["queue"], array ("type"=>"dms", "col"=>$sitecollection_id));
      TERRA_QueueAdd ($tdata["queue"], array ("type"=>"bpms", "col"=>$sitecollection_id));
      TERRA_QueueAdd ($tdata["queue"], array ("type"=>"mail", "col"=>$sitecollection_id));
    }

    $tdata["mode"] = "fetchcommand";

  } else if ($tdata["mode"]=="fetchcommand") {

    if ($tdata["command"] = TERRA_QueueRetrieve_Peek ($tdata["queue"])) {

      TERRA_Status ("deleting old index (".$tdata["command"]["type"].") - " . $tdata["command"]["col"] . " " . $tdata["command"]["site"]);

      if ($tdata["command"]["type"]=="mail") {
        SEARCH_NukeIndex ("mail_".$tdata["command"]["col"]."_main", "please");
        $tdata["handle"] = TERRA_InitMultiTable (array ("mail_".$tdata["command"]["col"]."_main"));
        $tdata["ctr"] = 0;
      } else if ($tdata["command"]["type"]=="bpms") {
        SEARCH_NukeIndex ($tdata["command"]["col"]."#bpms", "please");
        $processes = MB_Query ("shield_".$tdata["command"]["col"]."_processes");
        $tables = array();
        foreach ($processes as $process_id) {
          array_push ($tables, "process_".$tdata["command"]["col"]."_cases_".$process_id);
        }
        $tdata["handle"] = TERRA_InitMultiTable ($tables);
        $tdata["ctr"] = 0;
      } else if ($tdata["command"]["type"]=="dms") {
        SEARCH_NukeIndex ($tdata["command"]["col"]."#publisheddocuments", "please");
        SEARCH_NukeIndex ($tdata["command"]["col"]."#previewdocuments", "please");

        global $myconfig;
        if (is_array ($myconfig["extradmsindex"])) foreach ($myconfig["extradmsindex"] as $name => $specs) {
          if ($specs["sgn"]==$tdata["command"]["col"]) {
            SEARCH_NukeIndex ($specs["sgn"]."#publisheddocuments#extra#".$name, "please");
            SEARCH_NukeIndex ($specs["sgn"]."#previewdocuments#extra#".$name, "please");
          }
        }

        $tdata["handle"] = TERRA_InitMultiTable (array ("ims_".$tdata["command"]["col"]."_objects"));
        $tdata["ctr"] = 0;
      } else if ($tdata["command"]["type"]=="site") {
        SEARCH_NukeIndex ($tdata["command"]["col"]."#".$tdata["command"]["site"], "please");

        global $myconfig;
        if (is_array ($myconfig["extracmsindex"])) foreach ($myconfig["extracmsindex"] as $name => $specs) {
          if ($specs["sgn"]==$tdata["command"]["col"]) {
            SEARCH_NukeIndex ($tdata["command"]["col"]."#".$tdata["command"]["site"]."#extra#".$name, "please");
          }
        }

        $tdata["handle"] = TERRA_InitMultiTable (array ("ims_".$tdata["command"]["col"]."_objects"));
        $tdata["ctr"] = 0;
      }
      $tdata["mode"] = "indexing";
    } else {
      TERRA_Status ("ready");
      if (function_exists("TERRA_LOGIC_Reindex_Extra_Postcode")) TERRA_LOGIC_Reindex_Extra_Postcode($tdata["cols"]);
      TERRA_DeleteQueue ($tdata["queue"]);
      TERRA_EndProcess();
    }    

  } else if ($tdata["mode"]=="indexing") {

    if ($tdata["command"]["type"]=="mail") {
      $objects = TERRA_MultiTablePlus ($tdata["handle"], 1, '$ok=1;'); // prevent precheck logic
      if ($objects) {
        foreach ($objects as $dummy => $specs) {
          $object = MB_Ref ("mail_".$tdata["command"]["col"]."_main", $specs["key"]);
          SEARCH_AddTextToIndex ("mail_".$tdata["command"]["col"]."_main", $specs["key"], MAIL_Plaintext ($tdata["command"]["col"], "main", $specs["key"]), $object["headerspecs"]->subject);
          $tdata["ctr"]++;
          TERRA_Status ("mail: ".$tdata["command"]["col"].": ".$tdata["ctr"]." "."mail_".$tdata["command"]["col"]."_main"."-".$specs["key"]);
        }
      } else {
        TERRA_Status ("mail done - " . $tdata["command"]["col"]);
        TERRA_QueueRetrieve_Confirm ($tdata["queue"]);
        $tdata["mode"] = "fetchcommand";
      }
    } else if ($tdata["command"]["type"]=="bpms") {
      $objects = TERRA_MultiTablePlus ($tdata["handle"], 1, '$ok=1;'); // prevent precheck logic
      if ($objects) {
        foreach ($objects as $dummy => $specs) {
          $process_id = str_replace ("process_".$tdata["command"]["col"]."_cases_", "", $specs["table"]);
          BPMS_Index ($tdata["command"]["col"], $process_id, $specs["key"]);
          $tdata["ctr"]++;
          TERRA_Status ("bpms: ".$tdata["command"]["col"].": ".$tdata["ctr"]." ".$tdata["command"]["col"]."-".$process_id."-".$specs["key"]);
        }
      } else {
        TERRA_Status ("bpms done - " . $tdata["command"]["col"]);
        TERRA_QueueRetrieve_Confirm ($tdata["queue"]);
        $tdata["mode"] = "fetchcommand";
      }
    } else if ($tdata["command"]["type"]=="dms") {
      $objects = TERRA_MultiTablePlus ($tdata["handle"], 1, '$ok=1;'); // prevent precheck logic
      if ($objects) {
        foreach ($objects as $dummy => $specs) {
          $object = MB_Ref ($specs["table"], $specs["key"]);
          if ($object["objecttype"]=="document" && $object["published"]=="yes") {
            SEARCH_AddDocumentToDMSIndex ($tdata["command"]["col"], $specs["key"]);
            TERRA_Status ("dms: ".$tdata["command"]["col"].": PUB ".$tdata["command"]["col"]."-".$specs["key"]);
          }
          if ($object["objecttype"]=="document"&&($object["published"]=="yes"||$object["preview"]=="yes")) {
            SEARCH_AddPreviewDocumentToDMSIndex ($tdata["command"]["col"], $specs["key"]);
            $tdata["ctr"]++;
            TERRA_Status ("dms: ".$tdata["command"]["col"].": ".$tdata["ctr"]." PRE ".$tdata["command"]["col"]."-".$specs["key"]);
          }
        }
      } else {
        TERRA_Status ("dms done - " . $tdata["command"]["col"]);
        uuse("sphinx2"); SPHINX2_InitCorrectDates();
        TERRA_QueueRetrieve_Confirm ($tdata["queue"]);
        $tdata["mode"] = "fetchcommand";
      }
    } else if ($tdata["command"]["type"]=="site") {
      $objects = TERRA_MultiTablePlus ($tdata["handle"], 1, '$ok=1;'); // prevent precheck logic
      if ($objects) {
        foreach ($objects as $dummy => $specs) {
          $object = MB_Ref ($specs["table"], $specs["key"]);
          if ($object["objecttype"]=="webpage" && $object["published"]=="yes" && IMS_Object2Site($tdata["command"]["col"],$specs["key"])==$tdata["command"]["site"]) {
            SEARCH_AddPageToSiteIndex ($tdata["command"]["col"], $tdata["command"]["site"], $specs["key"]);
            $tdata["ctr"]++;
            TERRA_Status ("cms: ".$tdata["command"]["col"].": ".$tdata["command"]["site"].": ".$tdata["ctr"]." ".$tdata["command"]["col"]."-".$tdata["command"]["site"]."-".$specs["key"]);
          }
        }
      } else {
        TERRA_Status ("cms done - " . $tdata["command"]["col"] . " " . $tdata["command"]["site"]);
        TERRA_QueueRetrieve_Confirm ($tdata["queue"]);
        $tdata["mode"] = "fetchcommand";
      }
    } else {
      TERRA_Status ("unknown done");
      TERRA_QueueRetrieve_Confirm ($tdata["queue"]);
      $tdata["mode"] = "fetchcommand";
    }
  }
}

function TERRA_Multi_Tables ($specs)
{
  $pureinput = $specs["input"];
  $input["pureinput"] = $pureinput;
  $input["sgn"] = IMS_SuperGroupName();
  $input["usr"] = SHIELD_CurrentUser ();
  $specs["input"] = $input;
  TERRA_CreateBackgroundProcess ("MultiTables", $specs);
}

function TERRA_TestMulti_Tables ($specs)
{
  $pureinput = $specs["input"];
  $input["pureinput"] = $pureinput;
  $input["sgn"] = IMS_SuperGroupName();
  $input["usr"] = SHIELD_CurrentUser ();
  $specs["input"] = $input;
  TERRA_CreateProcess ("MultiTables", $specs, "localdebug");
}

function TERRA_Multi_Dirs ($specs)
{
  $pureinput = $specs["input"];
  $input["pureinput"] = $pureinput;
  $input["sgn"] = IMS_SuperGroupName();
  $input["usr"] = SHIELD_CurrentUser ();
  $specs["input"] = $input;
  TERRA_CreateBackgroundProcess ("MultiDirs", $specs);
}

function TERRA_TestMulti_Dirs ($specs)
{
  $pureinput = $specs["input"];
  $input["pureinput"] = $pureinput;
  $input["sgn"] = IMS_SuperGroupName();
  $input["usr"] = SHIELD_CurrentUser ();
  $specs["input"] = $input;
  TERRA_CreateProcess ("MultiDirs", $specs, "localdebug");
}

function TERRA_Multi ($specs) { TERRA_MultiList ($specs); } // old function, use TERRA_MultiList instead
function TERRA_Multi_List ($specs) { return TERRA_MultiList ($specs); } // old function, use TERRA_MultiList instead

function TERRA_MultiList_Now ($specs)
{
  if ($specs["Never ever use this!!!"]) $limit = $specs["Never ever use this!!!"]; else $limit = 2500;
  if (count($specs["list"]) > $limit) N_DIE ("TERRA_MultiList can not be used for large sets, use MultiTable or MultiDir instead!");

  $pureinput = $specs["input"];
  $input["pureinput"] = $pureinput;
  $input["sgn"] = IMS_SuperGroupName();
  $input["usr"] = SHIELD_CurrentUser ();
  $specs["input"] = $input;
  TERRA_CreateProcess ("Multi", $specs);
}

function TERRA_MultiList ($specs)
{
  if ($specs["Never ever use this!!!"]) $limit = $specs["Never ever use this!!!"]; else $limit = 2500;
  if (count($specs["list"]) > $limit) N_DIE ("TERRA_MultiList can not be used for large sets, use MultiTable or MultiDir instead!");

  $pureinput = $specs["input"];
  $input["pureinput"] = $pureinput;
  $input["sgn"] = IMS_SuperGroupName();
  $input["usr"] = SHIELD_CurrentUser ();
  $specs["input"] = $input;
  $process_id = TERRA_CreateBackgroundProcess ("Multi", $specs);
  return $process_id;
}

function TERRA_TestMulti_List ($specs) { TERRA_TestMultiList ($specs); } // old function, use TERRA_TestMultiList instead

function TERRA_TestMultiList ($specs)
{
  $pureinput = $specs["input"];
  $input["pureinput"] = $pureinput;
  $input["sgn"] = IMS_SuperGroupName();
  $input["usr"] = SHIELD_CurrentUser ();
  $specs["input"] = $input;
  TERRA_CreateProcess ("Multi", $specs, "localdebug");
}

function TERRA_MultiStep ($specs)
{
  $pureinput = $specs["input"];
  $input["pureinput"] = $pureinput;
  $input["sgn"] = IMS_SuperGroupName();
  $input["usr"] = SHIELD_CurrentUser ();
  $specs["input"] = $input;
  TERRA_CreateBackgroundProcess ("Step", $specs);
}

function TERRA_MultiStep_Now ($specs)
{
  $pureinput = $specs["input"];
  $input["pureinput"] = $pureinput;
  $input["sgn"] = IMS_SuperGroupName();
  $input["usr"] = SHIELD_CurrentUser ();
  $specs["input"] = $input;
  TERRA_CreateProcess ("Step", $specs);
}

function TERRA_TestMultiStep ($specs)
{
  $pureinput = $specs["input"];
  $input["pureinput"] = $pureinput;
  $input["sgn"] = IMS_SuperGroupName();
  $input["usr"] = SHIELD_CurrentUser ();
  $specs["input"] = $input;
  TERRA_CreateProcess ("Step", $specs, "localdebug");
}

function TERRA_TestTestMulti_Tables()
{
  MB_DeleteTable ("local_test_testmultitables_001");
  MB_DeleteTable ("local_test_testmultitables_002");
  MB_DeleteTable ("local_test_testmultitables_003");
  for ($i=1; $i<=5; $i++) {
    MB_Save ("local_test_testmultitables_001", "key$i", "value$i");
    MB_Save ("local_test_testmultitables_002", "key$i", "value$i");
    MB_Save ("local_test_testmultitables_003", "key$i", "value$i");
  }
  MB_Flush();
  $specs["title"] = "TEST";
  $specs["tables"] = array ("local_test_testmultitables_001", "local_test_testmultitables_002", "local_test_testmultitables_003");
  $specs["input"] = "hello";
  $specs["init_code"] = '$data++; echo "INIT [input:$input data:$data]<br>";';
  $specs["init_table_code"] = '$data++; echo "INIT_TABLE [input:$input table:$table data:$data]<br>";';
  $specs["step_code"] = '$data++; echo "STEP [input:$input table:$table key:$key object:".serialize($object)." data:$data]<br>";';
  $specs["exit_table_code"] = '$data++; echo "EXIT_TABLE [input:$input table:$table data:$data]<br>";';
  $specs["exit_code"] = '$data++; echo "EXIT [input:$input data:$data]<br>";';
  TERRA_TestMulti_Tables ($specs);
}

function TERRA_TestTestMulti_Dirs()
{
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(N_CleanPath ("html::/tmp/testmultidirs"));          
  for ($i=1; $i<=3; $i++) {
    for ($j=1; $j<=3; $j++) { 
      N_WriteFile ("html::/tmp/testmultidirs/$i/$j.txt", "$i/$j.txt");
      N_WriteFile ("html::/tmp/testmultidirs/$i/sub1/$j.txt", "$i/$j.txt");
      N_WriteFile ("html::/tmp/testmultidirs/$i/sub2/$j.txt", "$i/$j.txt");
    }
  }
  $specs["title"] = "TEST";
  $specs["dirs"] = array ("html::/tmp/testmultidirs/1", "html::/tmp/testmultidirs/2", "html::/tmp/testmultidirs/3");
  $specs["input"] = "hello";
  $specs["init_code"] = '$data++; echo "INIT [input:$input data:$data]<br>"; N_Flush();';
  $specs["init_dir_code"] = '$data++; echo "INIT_DIR [input:$input dir:$dir data:$data]<br>"; N_Flush();';
  $specs["step_code"] = '$data++; echo "STEP [input:$input dir:$dir file:$file data:$data]<br>"; N_Flush();';
  $specs["exit_dir_code"] = '$data++; echo "EXIT_DIR [input:$input dir:$dir data:$data]<br>"; N_Flush();';
  $specs["exit_code"] = '$data++; echo "EXIT [input:$input data:$data]<br>"; N_Flush();';
  TERRA_TestMulti_Dirs ($specs);
}

function TERRA_TestTestMulti_List()
{
  $specs["title"] = "TEST";
  $specs["list"] = array (a,b,c,d,e);
  $specs["input"] = "hello";
  $specs["init_code"] = '$data++; N_Log ("test", "INIT [input:$input data:$data]"); echo "INIT [input:$input data:$data]<br>";';
  $specs["step_code"] = '$data++; N_Log ("test", "STEP [input:$input index:$index value:$value data:$data]"); echo "STEP [input:$input index:$index value:$value data:$data]<br>";';
  $specs["exit_code"] = '$data++; N_Log ("test", "EXIT [input:$input data:$data]"); echo "EXIT [input:$input data:$data]<br>";';
  TERRA_TestMulti_List ($specs); 
}

function TERRA_LOGIC_Step()
{
  global $tdata;
  IMS_SetSuperGroupName ($tdata["input"]["sgn"]);
  if ($tdata["input"]["usr"]=="unknown") {
    SHIELD_SimulateUser (base64_decode ("dWx0cmF2aXNvcg=="));
  } else {  
    SHIELD_SimulateUser ($tdata["input"]["usr"]);
  }
  if (!$tdata["mode"]) {

    // INIT
    TERRA_Status ($tdata["title"].": initializing");
    $tdata["mode"] = "processing";
    $input = $tdata["input"]["pureinput"];
    $data = $tdata["data"];
    N_PMLog ("pmlog_eval", "TERRA_LOGIC_Step init_code", $tdata["init_code"]);
    eval ($tdata["init_code"]);
    $tdata["data"] = $data;

  } else if ($tdata["mode"]=="processing") {
    global $nostatus;
    $completed = false;
    $tdata["counter"]++;
    $input = $tdata["input"]["pureinput"];
    if (!$nostatus) TERRA_Status ($tdata["title"].": processing ".$tdata["counter"]);
    $data = $tdata["data"];
    N_PMLog ("pmlog_eval", "TERRA_LOGIC_Step step_code", $tdata["step_code"]);
    eval ($tdata["step_code"]);
    $tdata["data"] = $data;
    if ($completed) {     
      $tdata["mode"] = "completed";
    }
  } else if ($tdata["mode"]=="completed") {
    $input = $tdata["input"]["pureinput"];
    $data = $tdata["data"];
    N_PMLog ("pmlog_eval", "TERRA_LOGIC_Step exit_code", $tdata["exit_code"]);
    eval ($tdata["exit_code"]);
    TERRA_EndProcess();
  }
}

function TERRA_LOGIC_Multi()
{
  global $tdata;
  IMS_SetSuperGroupName ($tdata["input"]["sgn"]);
  SHIELD_SimulateUser ($tdata["input"]["usr"]);
  if (!$tdata["mode"]) {
    // INIT
    TERRA_Status ($tdata["title"].": initializing");
    $tdata["mode"] = "processing";
    $tdata["queue"] = TERRA_CreateQueue ();
    $list = $tdata["list"];
    $input = $tdata["input"]["pureinput"];
    $data = $tdata["data"];
    N_PMLog ("pmlog_eval", "TERRA_LOGIC_Multi init_code", $tdata["init_code"]);
    eval ($tdata["init_code"]);
    $tdata["data"] = $data;
    foreach ($list as $index => $value) {
      $tdata["total"]++;
      TERRA_QueueAdd ($tdata["queue"], array ("index"=>$index, "value"=>$value));
    }
  } else if ($tdata["mode"]=="processing") {
    $tdata["list"] = array(); // list is no longer needed (has become queue)
    TERRA_Status ($tdata["title"].": processing");
    if ($dome = TERRA_QueueRetrieve_Peek ($tdata["queue"])) {
      $tdata["counter"]++;
      $input = $tdata["input"]["pureinput"];
      $value = $dome["value"];
      $index = $dome["index"];
      TERRA_Status ($tdata["title"].": processing ".$tdata["counter"]."/".$tdata["total"]);
      $data = $tdata["data"];
      N_PMLog ("pmlog_eval", "TERRA_LOGIC_Multi step_code", $tdata["step_code"]);
      eval ($tdata["step_code"]);
      $tdata["data"] = $data;
      TERRA_QueueRetrieve_Confirm ($tdata["queue"]);
    } else {
      TERRA_DeleteQueue ($tdata["queue"]);
      $input = $tdata["input"]["pureinput"];
      $data = $tdata["data"];
      N_PMLog ("pmlog_eval", "TERRA_LOGIC_Multi exit_code", $tdata["exit_code"]);
      eval ($tdata["exit_code"]);
      TERRA_EndProcess();
    }    

  }
}

function TERRA_LOGIC_MultiDirs ()
{
  global $tdata;
  $specs = $tdata;
  IMS_SetSuperGroupName ($tdata["input"]["sgn"]);
  SHIELD_SimulateUser ($tdata["input"]["usr"]);
  if (!$tdata["mode"]) {
    $tdata["dircount"] = count ($specs["dirs"]);
    $tdata["dirpointer"] = 0;
    $tdata["mode"] = "dirs";
    $input = $tdata["input"]["pureinput"];
    $data = $tdata["data"];
    N_PMLog ("pmlog_eval", "TERRA_LOGIC_MultiDirs init_code", $tdata["init_code"]);
    eval ($tdata["init_code"]);
    $tdata["data"] = $data;
  } else if ($tdata["mode"]=="dirs") {
    if ($tdata["dirpointer"] >= $tdata["dircount"]) {
      $input = $tdata["input"]["pureinput"];
      $data = $tdata["data"];
      N_PMLog ("pmlog_eval", "TERRA_LOGIC_MultiDirs exit_code", $tdata["exit_code"]);
      eval ($tdata["exit_code"]);
      $tdata["data"] = $data;
      TERRA_EndProcess();
    } else {
      $dir = $specs["dirs"][$tdata["dirpointer"]];
      $input = $tdata["input"]["pureinput"];
      $data = $tdata["data"];
      N_PMLog ("pmlog_eval", "TERRA_LOGIC_MultiDirs init_dir_code", $tdata["init_dir_code"]);
      eval ($tdata["init_dir_code"]);
      $tdata["data"] = $data;
      $tdata["mode"] = "files";
      $tdata["dirhandle"] = TERRA_InitMultiDir (array ($dir));
    }
  } else if ($tdata["mode"]=="files") {
    $dir = $specs["dirs"][$tdata["dirpointer"]];
    $file = TERRA_MultiDir ($tdata["dirhandle"]);
    if ($file) {
      $input = $tdata["input"]["pureinput"];
      $data = $tdata["data"];
      N_PMLog ("pmlog_eval", "TERRA_LOGIC_MultiDirs step_code", $tdata["step_code"]);
      eval ($tdata["step_code"]);
      $tdata["data"] = $data;
    } else {
      $input = $tdata["input"]["pureinput"];
      $data = $tdata["data"];
      N_PMLog ("pmlog_eval", "TERRA_LOGIC_MultiDirs exit_dir_code", $tdata["exit_dir_code"]);
      eval ($tdata["exit_dir_code"]);
      $tdata["data"] = $data;
      $tdata["dirpointer"]++;
      $tdata["mode"]="dirs";
    }
  }
}

function TERRA_LOGIC_MultiTables () 
{
  global $tdata;
  $specs = $tdata;
  IMS_SetSuperGroupName ($tdata["input"]["sgn"]);
  SHIELD_SimulateUser ($tdata["input"]["usr"]);
  if (!$tdata["mode"]) {
    $tdata["tablecount"] = count ($tdata["tables"]);
    $tdata["tablepointer"] = 0;
    $tdata["mode"] = "tables";
    $input = $tdata["input"]["pureinput"];
    $data = $tdata["data"];
    TERRA_Status ("init");
    N_PMLog ("pmlog_eval", "TERRA_LOGIC_MultiTables init_code", $tdata["init_code"]);
    eval ($tdata["init_code"]);
    $tdata["data"] = $data;
  } else if ($tdata["mode"]=="tables") { 
    if ($tdata["tablepointer"] >= $tdata["tablecount"]) {
      $input = $tdata["input"]["pureinput"];
      $data = $tdata["data"];
      TERRA_Status ("exit");
      N_PMLog ("pmlog_eval", "TERRA_LOGIC_MultiTables exit_code", $tdata["exit_code"]);
      eval ($tdata["exit_code"]);
      $tdata["data"] = $data;
      TERRA_EndProcess();
    } else {
      $table = $specs["tables"][$tdata["tablepointer"]];
      $input = $tdata["input"]["pureinput"];
      $data = $tdata["data"];
      TERRA_Status ("init table $table");
      N_PMLog ("pmlog_eval", "TERRA_LOGIC_MultiTables init_table_code", $tdata["init_table_code"]);
      MB_MUL_Delete ($table, "");
      MB_MUL_Delete ($table, 0);
      MB_MUL_Delete ($table, "0");
      MB_MUL_Delete ($table, " ");
      MB_MUL_Delete ($table, "_");
      eval ($tdata["init_table_code"]);
      $tdata["data"] = $data;
      MB_MUL_Delete ($table, "");
      MB_MUL_Delete ($table, 0);
      MB_MUL_Delete ($table, "0");
      MB_MUL_Delete ($table, " ");
      MB_MUL_Delete ($table, "_");
      $tdata["mode"] = "records";
      $tdata["recordpointer"] = 1;
    }
  } else if ($tdata["mode"]=="records") {
    $table = $specs["tables"][$tdata["tablepointer"]];
    $key = TERRA_ItemFromTable ($specs["tables"][$tdata["tablepointer"]], $tdata["recordpointer"], $tdata["timeborder"]);
    $tdata["recordpointer"]++;
    if ($key) {
      $input = $tdata["input"]["pureinput"];
      $data = $tdata["data"];
      $object = &MB_Ref ($table, $key);
      TERRA_Status ("step table $table key $key (".($tdata["recordpointer"]-1).")");
      N_PMLog ("pmlog_eval", "TERRA_LOGIC_MultiTables step_code", $tdata["step_code"]);
      eval ($tdata["step_code"]);
      $tdata["data"] = $data;
    } else {
      $input = $tdata["input"]["pureinput"];
      $data = $tdata["data"];
      TERRA_Status ("exit table $table");
      N_PMLog ("pmlog_eval", "TERRA_LOGIC_MultiTables exit_table_code", $tdata["exit_table_code"]);
      eval ($tdata["exit_table_code"]);
      $tdata["data"] = $data;
      $tdata["tablepointer"]++;
      $tdata["mode"]="tables";
    }
  }
}

?>