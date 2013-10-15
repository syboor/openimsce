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



/* GENERIC FUNCTIONS *********************************************************************************/

function DFC_Disable()
{
  global $disabledfc;
  $disabledfc = true;
}

function DFC_Key () // 0.06 ms
{
  return md5(serialize(func_get_args()));
}

function DFC_Exists ($key)
{
  global $disabledfc;
  if ($disabledfc) return false;
  if (DFC_MySQL()) {
    return DFC_MYSQL_Exists ($key);
  } else {
    return DFC_DISK_Exists ($key);
  }
}

function DFC_Read ($key)
{
  if (DFC_MySQL()) {    
    return DFC_MYSQL_Read ($key);
  } else {
    return DFC_DISK_Read ($key);
  }
}

function DFC_Write ($key, $object, $hours=8760)
{
  if (DFC_MySQL()) {
    DFC_MYSQL_Write ($key, $object, $hours);    
  } else {
    DFC_DISK_Write ($key, $object, $hours);
  }
  return $object;
}

function DFC_Delete ($key)
{
  if (DFC_MySQL()) {
    DFC_MYSQL_Delete ($key);    
  } else {
    DFC_DISK_Delete ($key);
  }
}

function DFC_Nuke ($please)
{
  if ($please=="please") {
    UB2_DestroyDirContentsIfYouAreVeryVerySure(getenv("DOCUMENT_ROOT") . "/dfc/");
  }
}

function DFC_Cleanup ($all=false)  // called once every night
{
  if ($all == "all") {  // 20101108 KvD Clean ALL
    $dir = getenv("DOCUMENT_ROOT")."/dfc";
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($dir);
    // clean table
    uuse("sys");
    SYS_MySQL_Query("drop table if exists internal_dfc;", 1);
    N_Log ("batch_night", "DFC_Cleanup ALL DISK + MYSQL deleted.");
  } else {
    for ($i=0; $i<4; $i++) { // every disk slice slice is cleaned aproximately once every 64 days
      $slice = substr (N_GUID(), 0, 2);
      $allslices .= $slice." ";
      $list = DFC_DISK_Slice ($slice); // 0.4%
      foreach ($list as $dummy => $value) {
        $del++;
        DFC_DISK_Delete ($value);
      }
    }
    N_Log ("batch_night", "DFC_Cleanup DISK deleted: $del, slices: $allslices");
  }
}

function DFC_Migrate () // obsolete
{
  global $myconfig;
  if ($myconfig["dfcengine"]=="MYSQL") {
    return false;
  } else if ($myconfig["dfcengine"]=="MYSQL_MIGRATE") {  
    return true;
  } else {
    return false;
  }
}

function DFC_MySQL ()
{
  global $myconfig;
  if ($myconfig["dfcengine"]=="MYSQL") {
    return true;
  } else if ($myconfig["dfcengine"]=="MYSQL_MIGRATE") {  
    return true;
  } else {
    return false;
  }
}

/* DISK BASED FUNCTIONS *********************************************************************************/

function DFC_DISK_Slice ($prefix) {
  $dir = N_Shellpath ("html::dfc/");
  if (N_Windows()) {
    $dir = $dir . $prefix."*";
    $dir = `dir $dir`;
    preg_match_all ("/[a-f0-9]{32}/", $dir, $matches);
    return $matches[0];
  } else {
    $dir = N_Shellpath ("html::dfc/");
    $dir = `ls $dir`;
    preg_match_all ("/[a-f0-9]{32}/", $dir, $matches);
    $result = array();
    foreach ($matches[0] as $dummy => $value) {
      if (substr ($value, 0, strlen($prefix))==$prefix) {
        array_push ($result, $value);
      }
    }
    return $result;
  }
}

function DFC_DISK_Exists ($key)
{
  N_Debug ("DFC_Exists ($key)");
  global $disabledfc;
  if ($disabledfc) return false;
  global $dfc_request_cache;
  if (file_exists (N_CleanPath ("html::" . "/dfc/" . $key))) {
    if ("".$dfc_request_cache[$key]=="") {
      $packet = unserialize (N_QuickReadFile (getenv("DOCUMENT_ROOT") . "/dfc/" . $key));
      $dfc_request_cache[$key] = $packet;       
    } else {
      $packet = $dfc_request_cache[$key];
    }
    if (time() > $packet["expires"]) return false;
    if (count ($dfc_request_cache) > 100) $dfc_request_cache = array(); 
    $value = true;
  } else {
    $value = false;
  }
  return $value;
}

function DFC_DISK_Read ($key)
{
  N_Debug ("DFC_Read ($key)", "DFC_Read");
  global $dfc_request_cache;
  if ("".$dfc_request_cache[$key]=="") {
    $packet = unserialize (N_QuickReadFile (getenv("DOCUMENT_ROOT") . "/dfc/" . $key));
    $dfc_request_cache[$key] = $packet;
  } else {
    $packet = $dfc_request_cache[$key];
  }
  if (count ($dfc_request_cache) > 100) $dfc_request_cache = array(); 
  return $packet["data"];
}

function DFC_DISK_Write ($key, $object, $hours=8760)
{
  N_Debug ("DFC_Write ($key, ..., $hours)", "DFC_Write");
  global $dfc_request_cache;
  $packet ["data"] = $object;
  $packet ["expires"] = time() + 3600 * $hours;
  $dfc_request_cache[$key] = $packet;
  N_WriteFile (getenv("DOCUMENT_ROOT") . "/dfc/" . $key, serialize($packet));
  if (count ($dfc_request_cache) > 100) $dfc_request_cache = array(); 
  return $object;
}

function DFC_DISK_Delete ($key)
{
  N_Debug ("DFC_Delete ($key)");
  N_ErrorHandling (false);
  @unlink (N_CleanPath ("html::"."/dfc/" . $key));
  N_ErrorHandling (true);
}

/* DATABASE BASED FUNCTIONS *********************************************************************************/

function DFC_MYSQL_Exists ($key)
{
  MB_MYSQL_Connect ();
  $result = MB_MYSQL_Query ("select thevalue from internal_dfc where thekey = '".mysql_escape_string($key)."';", 1);
  if (!$result) {
    DFC_MYSQL_CreateTable ();
    $result = MB_MYSQL_Query ("select thevalue from internal_dfc where thekey = '".mysql_escape_string($key)."';");
  }
  if ($row = mysql_fetch_row ($result)) {
    $object = unserialize ($row[0]);
  } else {
    return false;
  }
  if (time() > $object["expires"]) return false;
  return true;
}

function DFC_MYSQL_CreateTable ()
{
  global $myconfig;
  if ($myconfig["xmlmysql"]["tabletype"]) {
    MB_MYSQL_Query("CREATE TABLE internal_dfc (thekey varchar(255) PRIMARY KEY, thevalue mediumblob) ENGINE=".$myconfig["xmlmysql"]["tabletype"].";", 1);
  } else {
    MB_MYSQL_Query("CREATE TABLE internal_dfc (thekey varchar(255) PRIMARY KEY, thevalue mediumblob);", 1);
  }
}

function DFC_MYSQL_Read ($key)
{
  MB_MYSQL_Connect ();
  $result = MB_MYSQL_Query ("select thevalue from internal_dfc where thekey = '".mysql_escape_string($key)."';", 1);
  if (!$result) {
    DFC_MYSQL_CreateTable ();
    $result = MB_MYSQL_Query ("select thevalue from internal_dfc where thekey = '".mysql_escape_string($key)."';");
  }
  if ($row = mysql_fetch_row ($result)) {
    $object = unserialize ($row[0]);
  }
  return $object["data"];
}

function DFC_MYSQL_Write ($key, $value, $hours=8760)
{
  MB_MYSQL_Connect();
  // Determine if DFC > 1GB and reset it if it is
  $result = MB_MYSQL_Query  ("show table status where name ='internal_dfc' and Data_length > 1000000000", 1);
  if ($result && $row = mysql_fetch_row ($result)) {
    MB_MYSQL_Query  ("drop table internal_dfc", 1);
  }
  $object ["data"] = $value;
  $object ["expires"] = time() + 3600 * $hours;
  $result = MB_MYSQL_Query ("REPLACE INTO internal_dfc (thekey, thevalue) VALUES ('".mysql_escape_string($key)."', '".mysql_escape_string(serialize ($object))."');", 1);
  if (!$result) { // table does not exist
    DFC_MYSQL_CreateTable ();
    $result = MB_MYSQL_Query ("REPLACE INTO internal_dfc (thekey, thevalue) VALUES ('".mysql_escape_string($key)."', '".mysql_escape_string(serialize ($object))."');");
  }
  return $value;
}

function DFC_MYSQL_Delete ($key)
{
  MB_MYSQL_Connect ();
  $result = MB_MYSQL_Query ("delete from internal_dfc where thekey = '".mysql_escape_string($key)."';", 1);
  if (!$result) {
    MB_MYSQL_CreateTable ($table);
    $result = MB_MYSQL_Query ("delete from internal_dfc where thekey = '".mysql_escape_string($key)."';");
  }
}

?>