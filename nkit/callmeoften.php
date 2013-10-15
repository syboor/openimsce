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



  $CMO_CallTime = time();
  
  $dummy = true;
  if ($dummy) { // enforce dynamic execution
    $inc1 = getenv("DOCUMENT_ROOT") . "/nkit/nkit.php";
    include ($inc1);
    $inc2 = getenv("DOCUMENT_ROOT") . "/nkit/batch.php";
    include ($inc2);
  }

  $CMO_NKITLoadedTime = time();

  if ($localloop) { // called with loop
    MB_Save ("globalvars", "lastcallmeoften_int", time());
    MB_Flush();
    N_Log ("callmeoften", "CALL EMERGENCY MODE: calltime:".date ("H:i:s", $CMO_CallTime)." nkitloadedtime:".date ("H:i:s", $CMO_NKITLoadedTime));
  } else { // called with crontab or sceduler 
    MB_Save ("globalvars", "lastcallmeoften_ext", time());
    MB_Flush();
    N_Log ("callmeoften", "CALL: calltime:".date ("H:i:s", $CMO_CallTime)." nkitloadedtime:".date ("H:i:s", $CMO_NKITLoadedTime));
  }


  if (N_Windows()) {
    global $myconfig;
    if ($myconfig["windowsprofiling"]=="yes") {
//      $path = N_ShellPath ("html::openims/libs/winexec/pslist.exe");
//      N_Log ("windowsprofiling", "pslist", `$path`);
//      N_Log ("windowsprofiling", "pslist -m", `$path -m`);
      N_Log ("windowsprofiling", "CMO_DetectWindowsActivities", serialize (CMO_DetectWindowsActivities()));
      N_Log ("callmeoften", "After windowsprofiling");
    }
  }

  SHIELD_SimulateUser (base64_decode ("dWx0cmF2aXNvcg=="));

  if (!N_LoadAllows ("callmeoften")) {
    N_Log ("callmeoften", "TERMINATED (LoadAllows)");
    die(""); // prevent server overloading
  } else {
    N_Log ("callmeoften", "APPROVED (LoadAllows)");
  }

  

  $rec = &MB_Ref ("globalvars", N_CurrentServer ()."::batchdata");
  $rec["callmeoften"]++;
  $rec["lastupdate"] = time();   
  MB_Flush();
  N_Log ("callmeoften", "After globalvars update");

  uuse ("stats");
  N_Log ("serverstatus", STATS_Summary (), "", STATS_RAW_ServerStatus());
  N_Log ("callmeoften", "After serverstatus logging update");

  global $myconfig;
  if($myconfig["batch_night_hour_from"]) {
    $night_hour_from = $myconfig["batch_night_hour_from"];
  } else {
    $night_hour_from = 2;
  }
  if($myconfig["batch_night_hour_until"]) {
    $night_hour_until = $myconfig["batch_night_hour_until"];
  } else {
    $night_hour_until = 7;
  }

  if (! ($rec["callmeoften"] % (8*60-1))) {
    N_Log ("callmeoften", "Before CallMeOnceEvery8Hours");
    CallMeOnceEvery8Hours();
    N_Log ("callmeoften", "After CallMeOnceEvery8Hours");
  } else if (! ($rec["callmeoften"] % (4*60-7))) {
    N_Log ("callmeoften", "Before CallMeOnceEvery4Hours");
    CallMeOnceEvery4Hours();
    N_Log ("callmeoften", "After CallMeOnceEvery4Hours");
  } else if (! ($rec["callmeoften"] % (2*60-13))) {
    N_Log ("callmeoften", "Before CallMeOnceEvery2Hours");
    CallMeOnceEvery2Hours();
    N_Log ("callmeoften", "After CallMeOnceEvery2Hours");
  } else if (! ($rec["callmeoften"] % 60-3)) {
    N_Log ("callmeoften", "Before CallMeOnceEveryHour");
    CallMeOnceEveryHour();
    N_Log ("callmeoften", "After CallMeOnceEveryHour");
  } else if (! ($rec["callmeoften"] % 10)) {
    N_Log ("callmeoften", "Before CallMeOnceEvery10Minutes");
    CallMeOnceEvery10Minutes();
    N_Log ("callmeoften", "After CallMeOnceEvery10Minutes");
  } else if (time()-$rec["lastnightcall"] > 3600*15 && N_Time2Hour (time())>=$night_hour_from  && N_Time2Hour (time())<=$night_hour_until) { // 02:00am - 07:00am
    N_Log ("callmeoften", "Before ExecuteInBackground CallMeOnceEveryNight");
    $rec["lastnightcall"] = time();
    MB_Flush();
    URPC_ExecuteInBackground_Now  ('include (getenv("DOCUMENT_ROOT") . "/nkit/batch.php"); CallMeOnceEveryNight();');
    N_Log ("callmeoften", "After ExecuteInBackground CallMeOnceEveryNight");
  } else if (time()-$rec["lastnightcall"] > 3600*60) { // do it anyway after 60 hours
    N_Log ("callmeoften", "Before ExecuteInBackground CallMeOnceEveryNight EMERGENCY");
    $rec["lastnightcall"] = time();
    MB_Flush();
    URPC_ExecuteInBackground_Now  ('include (getenv("DOCUMENT_ROOT") . "/nkit/batch.php"); CallMeOnceEveryNight();');
    N_Log ("callmeoften", "After ExecuteInBackground CallMeOnceEveryNightt EMERGENCY");
  }

  N_Log ("callmeoften", "Before CallMeOnceEveryMinute");
  CallMeOnceEveryMinute();
  N_Log ("callmeoften", "After CallMeOnceEveryMinute");

  N_Exit(); 
  N_Log ("callmeoften", "After N_Exit");

  N_CheckCallMeOften (true);
  N_Log ("callmeoften", "After N_CheckCallMeOften");

function CMO_DetectWindowsActivities()
{
  $objLocator = new COM("WbemScripting.SWbemLocator");
  $objService = $objLocator->ConnectServer();
  $objWEBM = $objService->Get("Win32_Process");
  $objProp = $objWEBM->Properties_;
  $arrProp = $objProp->Next($objProp->Count);
  $objWEBMCol = $objWEBM->Instances_();
  $arrWEBMCol = $objWEBMCol->Next($objWEBMCol->Count);
  foreach($arrWEBMCol as $objItem)
  {
    ++$ctr;
    foreach($arrProp as $propItem)
    {
      eval("\$itemvalue = \$objItem->" .$propItem->Name .";");
      $result[$ctr][$propItem->Name] = $itemvalue;
    }
  }
  return $result;
}

?>