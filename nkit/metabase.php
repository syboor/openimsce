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



uuse("qry");
uuse("sdex");

$myconfig["xmlengine"] = "ULTRA";
$myconfig["sdexengine"] = "";
$myconfig["dfcengine"] = "";
$myconfig["ftengine"] = "ULTRA";

// Set a number of settings to remove dependencies on proprietary client side software.
// Dont change this; we promise you it won't work. 
foreach ($myconfig as $thesgn => $sgnspecs) {
  if (is_array($sgnspecs)) {
    unset($myconfig[$thesgn]["advancedupload"]);
    $myconfig[$thesgn]["useinlinehtmleditoronly"] = "yes";
    $myconfig[$thesgn]["useinlinehtmleditorbydefault"] = "yes";
    $myconfig[$thesgn]["usetinymce"] = "yes";
    $myconfig[$thesgn]["trans"] = "no";
    $myconfig[$thesgn]["edit_template_html"] = "inline";
    $myconfig[$thesgn]["edit_default_template_doc_html"] = "inline";
  }
}

if (!$myconfig["tmp"]) $myconfig["tmp"] = N_CleanRoot() . "tmp/tmp/"; 
  // Quick fix needed to make the autoconf's popup forms work.
  // $myconfig["tmp"] should be something short and outside the document root.

/*

*** METABASE XML DATA STORE ***

This is the CE implementation of metabase, using the "ultra" engine.

*/

 

/*******************************************************************************************************************************/
/*** METABASE - INTERFACE LAYER (MB) ***/

function &MB_Ref ($table, $key)
{
  $table = strtolower($table);
  $key = strtolower($key);
  return MB_CAC_Ref ($table, $key);
}

function MB_Fetch ($table, $key, $field)
{
  $obj = MB_Ref ($table, $key);
  if (is_array($obj)) {
    N_Debug ("MB_Fetch ($table, $key, $field) => ".$obj[$field]);
    return $obj[$field];
  } else {
    N_Debug ("MB_Fetch ($table, $key, $field) => false");
    return "";
  }
}

function MB_Store ($table, $key, $field, $value)
{
  $obj = &MB_Ref ($table, $key);
  $obj[$field] = $value;
}

function MB_Load ($table, $key)
{
  return MB_Ref ($table, $key);
}

function MB_Read ($table, $key)
{
  return MB_Ref ($table, $key);
}

function MB_Save ($table, $key, $object) // save to cache
{
  $obj = &MB_Ref ($table, $key);
  $obj = $object;
  return $object;
}

function MB_Delete ($table, $key)
{
  MB_CAC_Delete ($table, $key);
}

function MB_AutoDelete ($table, $key, $seconds=604800) // default 1 week
{
  $table = strtolower($table);
  $key = strtolower($key);
  if ($seconds==-1) { // remove the autodelete command from the queue
    N_DeleteScedule ("autodel$table#$key");
  } else {
    N_AddModifyScedule ("autodel$table#$key", time()+$seconds, 'MB_Delete ($input["table"], $input["key"]);', array("table"=>$table, "key"=>$key));
  }
}

function MB_Invert ($me="")
{
  $me = SDEX_2ComparableString_Raw ($me);
  for ($i=0; $i<strlen ($me); $i++) {
    $result .= chr (255-ord (substr ($me, $i, 1)));
  }
  return "*".$result.chr(255);
}

function MB_Comparable ($me)
{
  return SDEX_2ComparableString ($me);
}

function MB_Text2Words ($me,$isfilter=false)
{
  if ($isfilter) {
    return SEARCH_TEXT2FILTERQUERY ($me);  //sbr
  } else {
    return SEARCH_TEXT2WORDSQUERY ($me);
  }
}

function MB_Filter ($table, $expression, $filter)
{
  return MB_QRY_Multi ($table, array ("filter" => array ($expression, $filter)));
} 

function MB_TurboFilter ($table, $expression, $filter)
{
  return MB_Filter ($table, $expression, $filter);
}

function MB_MultiQuery ($table, $specs=array())
{
  N_Debug ("MB_MultiQuery ($table, ...)");
  return MB_QRY_Multi ($table, $specs);
}

function MB_TurboMultiQuery ($table, $specs=array())
{
  return MB_MultiQuery ($table, $specs);
}

function MB_Query ($table, $constraint="", $sort="", $amount=1000000000) // default exclude deleted (empty) records
{
  $table = strtolower($table);
  $result = MB_QRY_SlowQuery ($table, $constraint, $sort, $amount);
  if (!is_array ($result)) {
    $result = array();   
  }
  reset ($result);
  return $result;
}

function MB_RangeQuery ($table, $expression="", $smallest="qkj52k34j5kjg542kj34h5j4352kj3", $largest="qkj52k34j5kjg542kj34h5j4352kj3", $sort="")
{
  N_Debug ("MB_RangeQuery ($table, $expression, $smallest, $largest, $sort)");
  $result = array();
  if (!$expression) $expression="1==1";
  if ($smallest=="qkj52k34j5kjg542kj34h5j4352kj3") {
    $smallest = -9999999999999;
    $largest = 9999999999999;    
  }
  if ($largest=="qkj52k34j5kjg542kj34h5j4352kj3") $largest = $smallest;
  return MB_QRY_Multi ($table, array ("range"=>array($expression, $smallest, $largest), "sort"=>$sort));
}

function MB_TurboRangeQuery ($table, $expression="", $smallest="qkj52k34j5kjg542kj34h5j4352kj3", $largest="qkj52k34j5kjg542kj34h5j4352kj3", $sort="")
{
  return MB_RangeQuery ($table, $expression, $smallest, $largest, $sort);
}

function MB_AnalyzeQuery ($table, $expression="", $value="qkj52k34j5kjg542kj34h5j4352kj3", $sort="")
{
  if (is_array ($expression) && !is_array($value)) { // @D
    if ($value == "qkj52k34j5kjg542kj34h5j4352kj3") $sort=""; else $sort=$value;
    $theexpression = '""';
    foreach ($expression as $exp => $val) {
      $theexpression .= '."[".MB_Comparable('.$exp.')."]"';
    }
    $total_low = '';
    $total_high = '';
    foreach ($expression as $exp => $subval) {
      if (is_array ($subval)) {
        $total_low .= '['.MB_Comparable($subval[0]).']';
        $total_high .= '['.MB_Comparable($subval[1]).']';
      } else {
        $total_low .= '['.MB_Comparable($subval).']';
        $total_high .= '['.MB_Comparable($subval).']';
      }
    }
    return array ($table, $theexpression, $total_low, $total_high, $sort);
  } else { // @B & @C
    if (is_array ($expression)) { // @C
      $total = '""';
      foreach ($expression as $subexp) {      
        $total .= '."[".MB_Comparable('.$subexp.')."]"';
      }
      $expression = $total;
    } else { // @B
      $expression = "MB_Comparable($expression)";
    }
    if (is_array ($value)) { // @C
      $total = '';
      $total_high = '';
      foreach ($value as $subval) {      
        if (is_array ($subval)) {
          $total_low .= '['.MB_Comparable($subval[0]).']';
          $total_high .= '['.MB_Comparable($subval[1]).']';
        } else {
          $total_low .= '['.MB_Comparable($subval).']';
          $total_high .= '['.MB_Comparable($subval).']';
        }
      }
    } else { // @B
      $total_low .= MB_Comparable($value);
      $total_high .= MB_Comparable($value);
    }
    return array ($table, $expression, $total_low, $total_high, $sort);
  }
}

// MB_SelectQuery has 4 interfaces:
//   @A  MB_SelectQuery ($table)
//   @B  MB_SelectQuery ($table, $expression, $value, $sort)
//   @C  MB_SelectQuery ($table, $expression_array, $value_array, $sort) OBSOLETE
//   @D  MB_SelectQuery ($table, $expression_and_value_array, $sort)
function MB_SelectQuery ($table, $expression="", $value="qkj52k34j5kjg542kj34h5j4352kj3", $sort="")
{
  N_Debug ("MB_SelectQuery ($table, ...)");
  if (!$expression) { // @A
    if ($value && $value != "qkj52k34j5kjg542kj34h5j4352kj3") $sort = $value;
    return MB_QRY_SlowQuery ($table, "", $sort); 
  } else { // @B, @C or @D
    list ($table, $expression, $smallest, $largest, $sort) = MB_AnalyzeQuery ($table, $expression, $value, $sort);
    if (!$expression) $expression="1==1";
    if ($smallest=="qkj52k34j5kjg542kj34h5j4352kj3") {
      $smallest = -9999999999999;
      $largest = 9999999999999;    
    }
    if ($largest=="qkj52k34j5kjg542kj34h5j4352kj3") $largest = $smallest;
    return MB_QRY_Multi ($table, array ("range"=>array($expression, $smallest, $largest), "sort"=>$sort));
  } 
}

// MB_TurboSelectQuery has 4 interfaces:
//   MB_TurboSelectQuery ($table)
//   MB_TurboSelectQuery ($table, $expression, $value, $sort)
//   MB_TurboSelectQuery ($table, $expression_array, $value_array, $sort) OBSOLETE
//   MB_TurboSelectQuery ($table, $expression_and_value_array, $sort)
function MB_TurboSelectQuery ($table, $expression="", $value="qkj52k34j5kjg542kj34h5j4352kj3", $sort="")
{
  return MB_SelectQuery ($table, $expression, $value, $sort);
}

function MB_TopQuery ($table, $expression, $amount)
{
  $rexpression = "MB_Invert ($expression)";
  return MB_QRY_Multi ($table, array ("sort"=>$rexpression, "slice"=>array(1, $amount), "value"=>$expression));
}

function MB_TurboTopQuery ($table, $expression, $amount)
{
  $rexpression = "MB_Invert ($expression)";
  return MB_TopQuery ($table, $expression, $amount);
}

function MB_Flush() // needs to be called at exit
{
  MB_CAC_Flush (); 
}

function MB_NukeDatabase ($please)
{
  MB_CAC_NukeDatabase ($please);
}

function MB_AllTables ()
{
  return MB_CAC_AllTables();
}

function MB_TableExists ($table)
{
  $all = MB_AllTables();
  foreach ($all as $t => $dummy) {
    if ($t == $table) return true;
  }
  return false;
}

function MB_AllKeys ($table) // include deleted (empty) records
{
  $table = strtolower($table);
  return MB_CAC_AllKeys ($table);
}

function MB_ExportAll () 
{
}

function MB_Import ($everything)
{
}

function MB_DeleteTable ($table)
{
  $table = strtolower($table);
  MB_CAC_DeleteTable ($table);
}

function MB_Compact ($age=172800) // physically remove deleted records, default older then 2 days
{
  MB_MUL_Compact ($age);
}

function MB_CopyTable ($to, $from, $postcode="")
{
  MB_FLush(); // copytable operates at the replication layer level, so make sure the cache is not dirty
  MB_REP_CopyTable ($to, $from, $postcode);
}

function MB_MultiLoad ($table, $keys)
{
  if ($keys) MB_CAC_MultiLoad ($table, $keys);
}

/*******************************************************************************************************************************/
/*** QUERY LAYER (MB_QRY) - QUERY ANALYZER AND EXECUTOR ***/

function MB_QRY_NoNeg ($n)
{
  if ($n>0) return $n;
  return 0;
}

function MB_QRY_Multi ($table, $specs=array()) 
{
  $result = MB_DOQRY_Multi ($table, $specs);
  if (is_array ($result)) reset ($result);
  return $result;
}

function MB_DOQRY_Multi ($table, $specs=array()) 
{
  return MB_DOQRY_Multi_LL ($table, $specs); // no caching (yet)
  global $MB_DOQRY_Multi_cache_known, $MB_DOQRY_Multi_cache_value;
  $key = md5($table.serialize($specs));
  if (!$MB_DOQRY_Multi_cache_known[$key]) {
    $MB_DOQRY_Multi_cache_known[$key] = true;
    $MB_DOQRY_Multi_cache_value[$key] = MB_DOQRY_Multi_LL ($table, $specs);
  }
  return $MB_DOQRY_Multi_cache_value[$key];
}

function MB_DOQRY_Multi_LL ($table, $specs=array()) 
{
  global $myconfig;

  if ($specs["multimultimatch"]) {
    foreach ($specs["multimultimatch"] as $mspecs) {
      list ($type, $exp, $list) = $mspecs;
      $specs["select"]["count ($exp) > 0"] = true;
      $theexp = "";
      foreach ($list as $val) {
        if ($theexp) {
          if ($type=="and") $theexp .= " && ";
          if ($type=="or") $theexp .= " || ";
        }
        $theexp .= "in_array (base64_decode(\"".base64_encode($val)."\"), $exp)";
      }
      $specs["slowselect"][$theexp] = true;  
    }
  }
  if ($specs["multimatch"]) {
    list ($type, $exp, $list) = $specs["multimatch"];
    $specs["select"]["count ($exp) > 0"] = true;
    $theexp = "";
    foreach ($list as $val) {
      if ($theexp) {
        if ($type=="and") $theexp .= " && ";
        if ($type=="or") $theexp .= " || ";
      }
      $theexp .= "in_array (base64_decode(\"".base64_encode($val)."\"), $exp)";
    }
    $specs["slowselect"][$theexp] = true;
  }

  global $debug; if ($debug=="yes") N_EO ($specs);
  $slice      = $specs["slice"];      // "" => everything, "count" => number, array(11,20) => records 11..20
  $select     = $specs["select"];     // array (exp1 => val1, exp2 => val2)
  $wherein    = $specs["wherein"];    // array (exp1 => array (val11, val12), exp2 => array (val21, val22))
  $slowselect = $specs["slowselect"]; // array (exp1 => val1, exp2 => val2)
  $range      = $specs["range"];      // array (exp, low, high)
  $sql        = $specs["sql"];        // array (exp1, oper1, val1, exp2, oper2, val2, ...), SQL only
  $filter     = $specs["filter"];     // array (exp, filter)
  $rawfilter  = $specs["rawfilter"];  // array (exp, filter), SQL only
  $sort       = $specs["sort"];       // exp
  $rsort      = $specs["rsort"];      // exp
  $value      = $specs["value"];      // exp
  $totaltable = $specs["totaltable"]; // include records which are deleted but not compacted
  $turbo      = false;

  N_Debug ("MB_QRY_Multi ($table, ".serialize ($specs).")", "MB_QRY_Multi NON TURBO");

  if ($wherein) { 
    global $myconfig;
    // NOT MySQL or NOT Indexing
    foreach ($wherein as $exp => $vals) {
      $subexp = "";
      foreach ($vals as $val) {
        if ($subexp) {
          $subexp .= ' || MB_Comparable('.$exp.') == "'.MB_Comparable($val).'" ';
        } else {
          $subexp = ' ( MB_Comparable('.$exp.') == "'.MB_Comparable($val).'" ';
        }
      }
      if ($subexp) {
        $select[$subexp." ) "] = true;
      } else {
        return array(); // list is empty, nothing can match
      }
    }
  }
  // Check if SQL is required
  if ($sql || $rawfilter) {
    N_DIE ("MySQL required but not allowed (CE)");
  }

  // transform filter to separate words
  if ($filter) {
    if (!trim($filter[1])) {
      $filter = null;
    } else {
      $words = explode (" ", MB_Text2Words ($filter[1],true));//sbr
    }
  }

  // handle empty records (append to select)
  if (!$totaltable) {
    $select['$record!=false'] = true;
  }

  // handle range (append to select)
  if ($range) {
	  $select[$range[0]] = array ($range[1], $range[2]);
  }

  // determine $value
  if (!$value && $sort) {
    $value = $sort;
  }

  // determine $sort
  if ($rsort) {
    $sort = "MB_Invert($rsort)";  
  }

  // determine (slow)select $expression, $smallest and $largest
  if (!$turbo && $slowselect) { // merge $slowselect into $select
    foreach ($slowselect as $a => $b) {
      $select2[$a] = $b;
    }
    if ($select) foreach ($select as $a => $b) {
      $select2[$a] = $b;
    }
    $select = $select2;    
    $slowselect = array();
  }
  if ($select) {
    $expression = '""';
    foreach ($select as $exp => $val) {
      $expression .= '."[".MB_Comparable('.$exp.')."]"';
    }
    $smallest = '';
    $largest = '';
    foreach ($select as $exp => $val) {
      if (is_array ($val)) {
        $smallest .= '['.MB_Comparable($val[0]).']';
        $largest .= '['.MB_Comparable($val[1]).']';
      } else {
        $smallest .= '['.MB_Comparable($val).']';
        $largest .= '['.MB_Comparable($val).']';
      }
    }
  }
  if ($slowselect) {
    $slowexpression = '""';
    foreach ($slowselect as $exp => $val) {
      $slowexpression .= '."[".MB_Comparable('.$exp.')."]"';
    }
    $slowsmallest = '';
    $slowlargest = '';
    foreach ($slowselect as $exp => $val) {
      if (is_array ($val)) {
        $slowsmallest .= '['.MB_Comparable($val[0]).']';
        $slowlargest .= '['.MB_Comparable($val[1]).']';
      } else {
        $slowsmallest .= '['.MB_Comparable($val).']';
        $slowlargest .= '['.MB_Comparable($val).']';
      }
    }
  }

  // determine $constraint (from $select)
  if ($select) {
    $constraint = "(($expression)>=(\$smallest)) && (($expression)<=(\$largest))";
  }

  // determine all keys
  $keys = MB_CAC_AllKeys ($table);

  // handle empty table
  if (!$keys) {
    if ($slice=="count"){
      return 0;
    } else {
      return array();
    }
  }

  // preload all objects (optimize query speed)
  MB_CAC_MultiLoad ($table, $keys); 

  // execute contraint
  if ($constraint) {
    $result = array();
    foreach ($keys as $key => $dummy)
    {
      $record = MB_CAC_Ref ($table, $key); // do not allow updates
      $object = $record;
      if ($record) {
        N_PMLog ("pmlog_eval", "MB_DOQRY_Multi_LL", '$match = ' . $constraint . ';');
        eval ('$match = ' . $constraint . ';');
        if ($match){
          N_PMLog ("pmlog_eval", "MB_DOQRY_Multi_LL", '$result[$key] = $key;');
          eval ('$result[$key] = $key;');
        }
      }
    }
    $keys = $result;
  }

  // execute filter
  if ($filter) {
    $result = array();
    foreach ($keys as $key => $dummy)
    {
      $record = MB_CAC_Ref ($table, $key); // do not allow updates
      $object = $record;
      if ($record) {
        N_PMLog ("pmlog_eval", "MB_DOQRY_Multi_LL", '$expressionvalue = MB_Text2Words('.$filter[0].', true);');
        eval ('$expressionvalue = MB_Text2Words('.$filter[0].', true);');
        $ok = true;
        foreach ($words as $dummy => $word) {
          if ($word && !strpos (" ".$expressionvalue, $word)) $ok = false;
        }
        if ($ok) {
          $result[$key] = $key;
        }
      }
    } 
    $keys = $result;
  }

  if ($slowselect) {
    // determine $slowconstraint (from $slowselect)
    $slowconstraint = "(($slowexpression)>=(\$slowsmallest)) && (($slowexpression)<=(\$slowlargest))";

    // handle empty table
    if (!$keys) {
      if ($slice=="count"){
        return 0;
      } else {
        return array();
      }
    }

    // execute contraint
    if ($slowconstraint) {
      $result = array();
      foreach ($keys as $key => $dummy)
      {
        $record = MB_CAC_Ref ($table, $key); // do not allow updates
        $object = $record;
        if ($record) {
          N_PMLog ("pmlog_eval", "MB_DOQRY_Multi_LL", '$match = ' . $slowconstraint . ';');
          eval ('$match = ' . $slowconstraint . ';');
          if ($match){
            N_PMLog ("pmlog_eval", "MB_DOQRY_Multi_LL", '$result[$key] = $key;');
            eval ('$result[$key] = $key;');
          }
        } 
      }
      $keys = $result;
    }
  }

  // execute sort
  if (!is_array($keys)) $keys=array();
  if ($sort) {
    foreach ($keys as $key => $dummy) {
      $record = MB_CAC_Ref ($table, $key); // do not allow updates
      $object = $record;
      N_PMLog ("pmlog_eval", "MB_DOQRY_Multi_LL", '$sortvalue = MB_Comparable ('.$sort.');');
      eval ('$sortvalue = MB_Comparable ('.$sort.');');
      $keys[$key] = $sortvalue;
    }    
    asort ($keys);
  } else {
    asort ($keys);
  }

  // handle empty result set
  if (!$keys) {
    if ($slice=="count"){
      return 0;
    } else {
      return array();
    }
  }
  
  // handle slice
  if ($slice=="count") {
    return count ($keys);
  } else if ($slice) {
    $oldkeys = $keys;
    $keys = array();
    foreach ($oldkeys as $a => $b) {
      ++$ctr;
      if ($ctr>=$slice[0] && $ctr<=$slice[1]) {
        $keys[$a] = $b;
      }
    }
  }
 
  // handle value
  foreach ($keys as $key => $dummy) {
    $record = MB_CAC_Ref ($table, $key); // do not allow updates
    $object = $record;
    if ($value) {
      N_PMLog ("pmlog_eval", "MB_DOQRY_Multi_LL", '$thevalue = '.$value.';');
      eval ('$thevalue = '.$value.';');
      $keys[$key] = $thevalue;
    } else {
      $keys[$key] = $key;
    }
  }

  return $keys;
}

function MB_QRY_SlowQuery ($table, $constraint="", $sort="", $amount=1000000000)
{
  N_Debug("MB_QRY_RawQuery ($table, $constraint, $sort, $amount) START", "MB_QRY_SlowQuery");

  // analyze parameters
  if ($constraint=="") $constraint='$record';
  if ($sort=="") $sort='$key';

  // get all objects
  $keys = MB_CAC_AllKeys ($table);
  if (!is_array($keys)) $keys = array();
  MB_CAC_MultiLoad ($table, $keys); 

  // apply constraint, prepare for sort
  $result = array();
  foreach ($keys as $key => $dummy) {
    $record = MB_CAC_Ref ($table, $key); // do not allow updates
    $object = $record;
    if ($record) { // skip empty (deleted) objects
      N_PMLog ("pmlog_eval", "MB_QRY_SlowQuery", '$match = ' . $constraint . ';');
      eval ('$match = ' . $constraint . ';');
      if ($match) {
        N_PMLog ("pmlog_eval", "MB_QRY_SlowQuery", '$result[$key] = MB_Comparable(' . $sort . ');');
        eval ('$result[$key] = MB_Comparable(' . $sort . ');');
      }
    }
  }

  // sort
  asort ($result);

  // fill value with result of sort expression
  foreach ($result as $key => $dummy) {
    $record = MB_CAC_Ref ($table, $key); // do not allow updates
    $object = $record;
    N_PMLog ("pmlog_eval", "MB_QRY_SlowQuery", '$result[$key] = ' . $sort . ';');
    eval ('$result[$key] = ' . $sort . ';');
  }

  // apply $amount
  if (count ($result) > $amount)
  {
    $result = array_slice ($result, 0, $amount);
  }

  N_Debug("MB_QRY_SlowQuery ($table, $constraint, $sort, $amount) END");
  return $result;    
}

/*******************************************************************************************************************************/
/*** CACHING LAYER (MB_CAC) - OPTIMIZING EFFICIENCY ***/

function MB_CAC_FlushAndClean ()
{
  global $mb_cac_inmemorycache, $mb_cac_inmemorycopy, $mb_cac_inmemorypresent;
  MB_FLush();
  $mb_cac_inmemorycache = $mb_cac_inmemorycopy = $mb_cac_inmemorypresent = array();
}

function &MB_CAC_Ref ($table, $key)
{
  global $mb_cac_inmemorycache, $mb_cac_inmemorycopy, $mb_cac_inmemorypresent;
  if ($mb_cac_inmemorypresent[$table][$key]) return $mb_cac_inmemorycache[$table][$key];
  $mb_cac_inmemorypresent[$table][$key] = true;
  $mb_cac_inmemorycache[$table][$key] = MB_REP_Load ($table, $key);
  $mb_cac_inmemorycopy[$table][$key] = $mb_cac_inmemorycache[$table][$key]; // keep a copy to detect changes  
  return $mb_cac_inmemorycache[$table][$key];
}

function MB_CAC_Present ($table, $key) 
{
  global $mb_cac_inmemorycache, $mb_cac_inmemorycopy, $mb_cac_inmemorypresent;
  return $mb_cac_inmemorypresent[$table][$key];
}

function MB_CAC_Set ($table, $key, $object) 
{
  global $mb_cac_inmemorycache, $mb_cac_inmemorycopy, $mb_cac_inmemorypresent;
  $mb_cac_inmemorypresent[$table][$key] = true;
  $mb_cac_inmemorycache[$table][$key] = $object;
  $mb_cac_inmemorycopy[$table][$key] = $mb_cac_inmemorycache[$table][$key]; // keep a copy to detect changes  
}

function MB_CAC_Unset ($table, $key) 
{
  global $mb_cac_inmemorycache, $mb_cac_inmemorycopy, $mb_cac_inmemorypresent;
  unset ($mb_cac_inmemorypresent[$table][$key]);
  unset ($mb_cac_inmemorycopy[$table][$key]);
  unset ($mb_cac_inmemorycache[$table][$key]);
}

function MB_CAC_MultiLoad ($table, $keys)
{
  global $mb_cac_inmemorycache, $mb_cac_inmemorycopy, $mb_cac_inmemorypresent;
  foreach ($keys as $key => $dummy) {
    if (!$mb_cac_inmemorypresent[$table][$key]) {
      if (!$all) {
        $all = MB_REP_MultiLoad ($table, $keys);        
      }
      $mb_cac_inmemorypresent[$table][$key] = true;
      $mb_cac_inmemorycache[$table][$key] = $all[$key];
      $mb_cac_inmemorycopy[$table][$key] = $mb_cac_inmemorycache[$table][$key]; // keep a copy to detect changes  
    }
  }
} 

function MB_CAC_Flush ()
{
  N_Debug ("MB_CAC_Flush ()");
  global $mb_cac_inmemorycache, $mb_cac_inmemorycopy;
  if (is_array($mb_cac_inmemorycache)) {
    reset ($mb_cac_inmemorycache);
    while (list($table, $allrecords) = each ($mb_cac_inmemorycache)) {
      reset ($allrecords);
      while (list ($key, $object)=each($allrecords)) {
//        if (serialize($object)==serialize($mb_cac_inmemorycopy[$table][$key])) {
        if ($object==$mb_cac_inmemorycopy[$table][$key]) {
          N_Debug ("MB_CAC_Flush () $table $key UNCHANGED ");
        } else {
          N_Debug ("MB_CAC_Flush () $table $key CHANGED");
          MB_REP_Save ($table, $key, $object);
        }
      }
    }
  }
  MB_REP_Flush();
}

function MB_CAC_Delete ($table, $key)
{
  global $mb_cac_inmemorycache, $mb_cac_inmemorycopy, $mb_cac_inmemorypresent;
  MB_REP_Delete ($table, $key);
  // remove from in memory cache
  unset ($mb_cac_inmemorypresent[$table][$key]);
  unset ($mb_cac_inmemorycopy[$table][$key]);
  unset ($mb_cac_inmemorycache[$table][$key]);
}

function MB_CAC_AllTables ()
{
  $list = MB_DOCAC_AllTables ();
  if (is_array ($list)) reset ($list);
  return $list;
}

function MB_DOCAC_AllTables ()
{
  global $MB_CAC_AllTables;
  if (!$MB_CAC_AllTables) {
    $MB_CAC_AllTables = MB_REP_AllTables();
  }
  return $MB_CAC_AllTables;
}

function MB_CAC_AllKeys ($table)
{
  $result = MB_REP_AllKeys($table);
  if (is_array($result)) asort ($result);
  return $result;  
}

function MB_CAC_NukeDatabase ($please)
{
  MB_REP_NukeDatabase ($please);
  // nuke cache
  global $mb_cac_inmemorycache, $mb_cac_inmemorycopy, $mb_cac_inmemorypresent;
  $mb_rep_inmemorypresent = array();
  $mb_rep_inmemorycache = array();
  $mb_rep_inmemorycopy = array();
}

function MB_CAC_CreateModifyTable ($table, $specs)
{
  MB_REP_CreateModifyTable ($table, $specs);
}

function MB_CAC_DeleteTable ($table)
{
  MB_REP_DeleteTable ($table);
}

/*******************************************************************************************************************************/
/*** REPLICATION LAYER (MB_REP) ***/

function MB_REP_CopyTable ($to, $from, $postcode="")
{
  N_Debug ("MB_REP_CopyTable ($to, $from)");
  $tokeys = MB_REP_AllKeys ($to);
  $fromkeys = MB_REP_AllKeys ($from);

  // Detect inserted and deleted records 
  $insertkeys = array();
  $deletekeys = array();
  $checkkeys = array();
  foreach ($fromkeys as $key => $dummy) {
    if ($tokeys[$key]) {
      $checkkeys[$key] = $key;
    } else {
      $insertkeys[$key] = $key;
    }
  }
  foreach ($tokeys as $key => $dummy) {
    if (!$fromkeys[$key]) {
      $deletekeys[$key] = $key;     
    }
  }

  // delete objects
  foreach ($deletekeys as $key => $dummy) {
    MB_REP_Delete ($to, $key);
  }

  // insert objects
  $insert  = MB_REP_MultiLoad ($from, $insertkeys);
  foreach ($insert as $key => $object) {
    $llobject = $object;    
    if ($postcode) {
      N_PMLog ("pmlog_eval", "MB_REP_CopyTable", $postcode);
      eval ($postcode);
    }
    MB_REP_Save ($to, $key, $llobject);
  }

  // check for changed objects
  $checkfrom = MB_REP_MultiLoad ($from, $checkkeys);
  $checkto = MB_REP_MultiLoad ($to, $checkkeys);
  foreach ($checkkeys as $key => $dummy) {
    if ($postcode || ($checkfrom[$key] != $checkto[$key])) {
      $llobject = $checkfrom[$key];
      if ($postcode) {
        N_PMLog ("pmlog_eval", "MB_REP_CopyTable", $postcode);
        eval ($postcode);
      }
      MB_REP_Save ($to, $key, $llobject);
    }
  }
}

function MB_REP_Flush ()
{
  MB_MUL_Flush();
}

function MB_REP_Load ($table, $key)
{
  N_Debug ("MB_REP_Load ($table, $key, ...)"); 

  $repobj = MB_MUL_Load ($table, $key);  
  if ($repobj["data"] === "") return null; // make php5 happy (instead of us unhappy)
  return $repobj["data"];
}

function MB_REP_MultiLoad ($table, $keys)
{
  $rep = MB_MUL_MultiLoad ($table, $keys); 
  foreach ($rep as $key => $data) {
    if (($result[$key] = $data["data"]) === "") $result[$key] = null; // make php5 happy (instead of us unhappy)
  }
  return $result;
} 

function MB_REP_Save_PostBlah ()
{
  global $MB_REP_Save_PostBlah, $MB_REP_Save_PostBlah_known;
  if (!$MB_REP_Save_PostBlah_known) {
    $MB_REP_Save_PostBlah_known = true;
    if ($REQUEST_METHOD=="POST") {
      $npost = 0;
      foreach ($_POST as $arg => $val) {
        if (strlen($val) > 200) $val = substr($val, 0, 200) . "...";
        $MB_REP_Save_PostBlah .= $arg."=".stripcslashes(N_UTF2HTML($val)) . " ";
        $npost++;
        if ($npost > 20) {
          $MB_REP_Save_PostBlah .= " .....";
          break;
        }
      }
    }
  }
  return $MB_REP_Save_PostBlah;
}

function MB_REP_Save ($table, $key, $object)
{
  N_Debug ("MB_REP_Save ($table, $key, ...)"); 
  global $REMOTE_ADDR, $REQUEST_METHOD, $encspec;

  if ($object === "") $object = null; // make php5 happy (instead of us unhappy)

  // encapsulate object to remember its origin and allow for p2p replication
  $repobj["data"] = $object;
  $repobj["time"] = time();
  $repobj["when"] = date("l F j, Y, G:i:s O");
  global $PHP_AUTH_USER;
  $repobj["user"] = $PHP_AUTH_USER;
  if ($_COOKIE["loguser"]) $repobj["user"] .= " (" . $_COOKIE["loguser"] . ")";
  $repobj["server"] = N_CurrentServer ();
  $repobj["host"] = getenv("HTTP_HOST");
  $repobj["script"] = getenv("SCRIPT_NAME");
  $repobj["post"] = MB_REP_Save_PostBlah ();

  if ($encspec) {
    $es = SHIELD_Decode($encspec, true);
    if ($es && $es["title"]) $repobj["encspectitle"] = $es["title"];
  }      

  $repobj["query"] = getenv("QUERY_STRING");
  $repobj["remote"] = $REMOTE_ADDR;

  MB_MUL_Save ($table, $key, $repobj); // save local
}

function MB_REP_Delete ($table, $key)
{
  MB_MUL_Delete ($table, $key);
}

function MB_REP_AllTables ()
{
  return MB_MUL_AllTables();
}

function MB_REP_AllKeys ($table)
{
  return MB_MUL_AllKeys($table);
}

function MB_REP_NukeDatabase ($please)
{
  if ($please=="please") {
    MB_MUL_NukeDatabase ("please");
  }
}

function MB_REP_CreateModifyTable ($table, $specs)
{
  MB_MUL_CreateModifyTable ($table, $specs);
}

function MB_REP_DeleteTable ($table)
{
  MB_MUL_DeleteTable ($table);
}

/*******************************************************************************************************************************/
/***  INDEX LAYER (MB_INDEX) - HANDLE HIGH SPEED QUERY PROCESSING ***/ 

function MB_INDEX_DeleteIndex ($table)
{
}

function MB_INDEX_DeleteIndexLike ($table, $like)
{
}

function MB_INDEX_MGT_Get ($table)
{
  return array();
}

/*******************************************************************************************************************************/
/***  TRIGGER LAYER (MB_TRIG) - HANDLE TRIGGERS ***/

function MB_TRIG_Load ($table, $key, $object) // called right after loading a record (MB_MUL_Load)
{
  N_Debug ("MB_TRIG_Load ($table, $key, ...)");
  return $object;
} 

function MB_TRIG_Save ($table, $key, $object) // called right before saving a record (MB_MUL_Save)
{
  N_Debug ("MB_TRIG_Save ($table, $key, ...)");

  if (!MB_CAC_Present ($table, $key)) {
    $fake_object = true;
    MB_CAC_Set ($table, $key, $object["data"]);
  }

  $old = MB_MUL_Load_NoTrigger ($table, $key);
  $oldbackup = $old;

  // Trigger to update security section (e.g. in case document is moved between folders)
  if (substr ($table, 0, 4)=="ims_" && substr ($table, strlen($table)-8, 8)=="_objects") {
    uuse ("shield");
    uuse ("ims");
    $object["data"] = SHIELD_UpdateSecuritySection (substr ($table, 4, strlen($table)-12), $object["data"]);
    if ($object["data"]["objecttype"]=="document") {
      IMS_UpdateShortcuts (substr ($table, 4, strlen($table)-12), $key); 
    }
  }  

  // Trigger to update niceurl url's
  global $myconfig;
  if ($myconfig["serverhasniceurl"] == "yes") {
    if (substr ($table, 0, 4)=="ims_" && substr ($table, strlen($table)-8, 8)=="_objects") {
      uuse ("niceurl");
      if ($object["data"]["objecttype"]=="webpage") {
        NICEURL_HandlePageUpdate (substr ($table, 4, strlen($table)-12), $key, $oldbackup, $object);
      }
    }
  }

  // LF: Update full text indexes when document is moved between cases
  if (substr ($table, 0, 4)=="ims_" && substr ($table, strlen($table)-8, 8)=="_objects") {
    // Dont do anything if data is empty for new or old object (e.g. creating new document, deleting record)
    if ($object["data"] && $oldbackup["data"]) {
      // Check that casesearching is enabled for this supergroup
      $sgn = substr($table, 4, strlen($table)-12);
      global $myconfig;
      if ($myconfig[$sgn]["casesearch"] == "yes") {
        // Check that the object's directory changed
        if ($object["data"]["directory"] != $oldbackup["data"]["directory"]) {
          // Check that the object's case changed
          $p = strpos ($object["data"]["directory"], ")");
          if ($p) {
           $case_id = substr($object["data"]["directory"], 1, $p-1);
          } else {
            $case_id = "";
          }
          $p = strpos ($oldbackup["data"]["directory"], ")");
          if ($p) {
           $case_id_old = substr($oldbackup["data"]["directory"], 1, $p-1);
          } else {
            $case_id_old = "";
          }
          if ($case_id != $case_id_old) {
            // Now schedule full text indexing for the object
            if ($object["data"]["preview"] == "yes" || $object["data"]["published"] == "yes") {
              $what = 'uuse("search"); SEARCH_Do_AddPreviewDocumentToDMSIndex ($input["sgn"], $input["key"]);';
              $input  = array("sgn"=>$sgn, "key"=>$key);
              N_QuickScedule (N_CurrentServer(), $what, $input);
            }
            if ($object["data"]["published"] == "yes") {
              $what = 'uuse("search"); SEARCH_Do_AddDocumentToDMSIndex ($input["sgn"], $input["key"]);';
              $input = array("sgn"=>$sgn, "key"=>$key);
              N_QuickScedule (N_CurrentServer(), $what, $input);
            }
          }
        }
      }
    }
  }

  // DVB 08-04-08: flag object for single-stage-workflow documents
  // batch processed by IMS_PublishSingleStageWorkflowDocuments($sgn)
  if (substr ($table, 0, 4)=="ims_" && substr ($table, strlen($table)-8, 8)=="_objects") {
    if ($object["data"]["objecttype"] == "document") {
       if ($oldbackup["data"]["openims_flag_trigger_change_singlestageworflow"] == "") {
          $object["data"]["openims_flag_trigger_change_singlestageworflow"] = "yes";
       }
    }
  }

  // save the object
  $result = MB_MUL_Save_NoIndex ($table, $key, $object);

  if ($fake_object) MB_CAC_Unset ($table, $key);

  return $object;
} 

function MB_TRIG_AfterSave ($table, $key, $object) // called right after saving a record (MB_MUL_Save)
{
  uuse ("ims");
  N_Debug ("MB_TRIG_AfterSave ($table, $key, ...)");

  if (substr ($table, 0, 4)=="ims_" && substr ($table, strlen($table)-8, 8)=="_objects") {
    IMS_SignalDatachange (substr ($table, 4, strlen($table)-12), $key);
  } 
}

function MB_TRIG_Delete ($table, $key) // called right before a record is deleted (MB_MUL_Delete)
{
  N_Debug ("MB_TRIG_Delete ($table, $key)");
}


/*******************************************************************************************************************************/
/***  MULTIPLEXING LAYER (MB_MUL) - HANDLE MULTIPLE ENGINES  - ENGINE DEPENDANT FUNCTIONS ***/

function MB_MUL_Engine ($table)
{
  return "ULTRA";
}

function MB_MUL_NukeDatabase ($please)
{
  if ($please!="please") return;

  MB_ULTRA_NukeDatabase ("please");
}

function MB_MUL_DeleteTable ($table)
{
  MB_ULTRA_DeleteTable ($table);
}

function MB_MUL_Load_NoTrigger ($table, $key)
{
  N_Debug ("MB_MUL_Load ($table, $key)", "MB_MUL_Load");
  $ret = MB_ULTRA_Load ($table, $key);
  if ($ret["time"] > time()) { // something went wrong (e.g. system with date in the future), repair it
    $ret["time"] = time()-7*24*3600;
    $ret["when"] = date("l F j, Y, G:i:s O", time()-7*24*3600)." RESET";
  }
  return $ret;
}

function MB_MUL_Save ($table, $key, $object)  // save to disk (record based engine)
{
  global $mb_cac_inmemorycache, $mb_cac_inmemorycopy, $mb_cac_inmemorypresent;

  N_Debug ("MB_MUL_Save ($table, $key, ...)", "MB_MUL_Save"); 

  MB_MUL_CreateModifyTable ($table); // make sure table exists

  if (substr ($table, 0, 4)=="ims_" && substr ($table, strlen($table)-8, 8)=="_objects") { 
    $object = MB_TRIG_Save ($table, $key, $object);
  } else {
    MB_MUL_Save_NoIndex ($table, $key, $object);
  }

  if ($mb_cac_inmemorypresent[$table][$key]) {
    $mb_cac_inmemorycopy[$table][$key] = $object["data"];
    $mb_cac_inmemorycache[$table][$key] = $object["data"];
  }

  MB_TRIG_AfterSave ($table, $key, $object);
  return true;
} 

function MB_MUL_Save_NoIndex ($table, $key, $object)  // save to disk (record based engine)
{
  $result = MB_ULTRA_Save ($table, $key, $object);
  return $result;
} 

function MB_MUL_Flush ()
{
  N_Debug ("MB_MUL_Flush ()");
  MB_ULTRA_Flush ();
}

function MB_MUL_Delete ($table, $key)
{
  N_Debug ("MB_MUL_Delete ($table, $key)", "MB_MUL_Delete");
  return MB_ULTRA_Delete ($table, $key);
}

function MB_MUL_AllTables ()
{
  N_Debug ("MB_MUL_AllTables ()");
  return MB_ULTRA_AllTables ();
}

function MB_MUL_TableSize ($table) // also returns records marked for deletion
{
  global $mb_mul_onekey_table;
  global $mb_mul_onekey_allkeys;
  global $mb_mul_onekey_allkeys_indexed;
  if ($mb_mul_onekey_table != $table) {
    $mb_mul_onekey_allkeys = MB_MUL_AllKeys ($table);
    $mb_mul_onekey_table = $table;
    $mb_mul_onekey_allkeys_indexed = array();
    foreach ($mb_mul_onekey_allkeys as $key) {
      $mb_mul_onekey_allkeys_indexed[++$counter] = $key;
    }
  }
  return count ($mb_mul_onekey_allkeys);
} 

function MB_MUL_KeyRange ($table, $from, $to) // also returns records marked for deletion
{
  for ($i=$from; $i<=$to; $i++) {
    $key = MB_MUL_OneKey ($table, $i);
    if ($key) {
      $return[$key] = $key;
    }
  }
  return $return;
} 

function MB_MUL_OneKey ($table, $index) // also returns records marked for deletion
{
  global $mb_mul_onekey_table;
  global $mb_mul_onekey_allkeys;
  global $mb_mul_onekey_allkeys_indexed;
  if ($mb_mul_onekey_table != $table) {
    $mb_mul_onekey_allkeys = MB_MUL_AllKeys ($table);
    $mb_mul_onekey_table = $table;
    $mb_mul_onekey_allkeys_indexed = array();
    foreach ($mb_mul_onekey_allkeys as $key) {
      $mb_mul_onekey_allkeys_indexed[++$counter] = $key;
    }
  }
  $result = $mb_mul_onekey_allkeys_indexed[$index];
  return $result;
}

function MB_MUL_AllKeys ($table)
{
  $list = MB_DOMUL_AllKeys ($table);
  if (is_array ($list)) reset ($list);
  return $list;
}

function MB_DOMUL_AllKeys ($table)
{
  return MB_ULTRA_AllKeys ($table);
}

function MB_MUL_MultiLoad ($table, $keys)
{
  N_Debug ("MB_MUL_MultiLoad ($table, ...)", "MB_MUL_MultiLoad");
  if (!is_array ($keys) || count($keys)==0) return array();
  foreach ($keys as $key => $dummy) {
    $result[$key] = MB_MUL_Load_NoTrigger ($table, $key);
  }
  return $result;
}


/*******************************************************************************************************************************/
/***  MULTIPLEXING LAYER (MB_MUL) - HANDLE MULTIPLE ENGINES  - GENERIC FUNCTIONS ***/

$mb_mull_fullbackup_testmode = "no"; // in testmode only "local_test" is processed

function MB_MUL_FullBackup ($filename="", $showprogress=false) 
{
  uuse ("terra");

  if (!$filename) {
    $filename = "html::backups/default.xml.gz";
  }

  $specs["title"] = "FULL BACKUP";
  $specs["input"]["filename"] = N_CleanPath ($filename);

  global $mb_mull_fullbackup_testmode;  
  if ($mb_mull_fullbackup_testmode=="yes") {
    $specs["init_code"] = '
      $tables = array();
      $tables["local_test"] = "local_test";
      $data["handle"] = TERRA_InitMultiTable ($tables, "", true); 
      N_WriteFile ($input["filename"].".tmp", "");
    ';   
  } else {
// $tables = MB_MUL_AllTables () can be used here (HYPER and VIRTUAL/FAST troubles not applicable)
    $specs["init_code"] = '
      $tables = MB_MUL_AllTables ();
      $data["handle"] = TERRA_InitMultiTable ($tables);
      N_WriteFile ($input["filename"].".tmp", "");
    ';   
  }

  $specs["step_code"] = '
    $list = TERRA_MultiTablePlus ($data["handle"], 250, \'$ok=1;\'); // prevent precheck logic
    if ($list) {
      $block = "";
      $prevtable = "";
      $allkeys = array();
      foreach ($list as $dummy => $s) {
        if (!$prevtable) {
          $prevtabe = $s["table"];
        }
        if ($prevtable != $s["table"]) {
          $all = MB_MUL_MultiLoad ($prevtable, $allkeys);
          foreach ($all as $key => $thedata) {
            $obj["table"] = $prevtable;
            $obj["key"] = $key;
            $obj["data"] = $thedata;
            $block .= base64_encode (serialize ($obj))."\n";
          }
          $allkeys = array();
          $prevtable = $s["table"];
        }
        $allkeys[$s["key"]] = $s["key"];
      }
      if ($allkeys) {
        $all = MB_MUL_MultiLoad ($prevtable, $allkeys);
        foreach ($all as $key => $thedata) {
          $obj["table"] = $prevtable;
          $obj["key"] = $key;
          $obj["data"] = $thedata;
          $block .= base64_encode (serialize ($obj))."\n";
        }
      }
      N_AppendFile ($input["filename"].".tmp", $block); 
    } else {
      $completed = true;
    }
  ';

  $specs["exit_code"] = '
    N_GzipFile ($input["filename"], $input["filename"].".tmp");
    N_DeleteFile ($input["filename"].".tmp");
  ';

  TERRA_MultiStep ($specs);
  if ($showprogress) {
    echo ML ("De database wordt in de achtergrond gebackuped", "The database is backupped in the background");
  }
}

function MB_MUL_FullRestore ($filename="", $showprogress=false)
{

  uuse ("terra");

  $specs["title"] = "FULL RESTORE";
  $specs["input"]["filename"] = $filename;

  global $mb_mull_fullbackup_testmode;  
  if ($mb_mull_fullbackup_testmode=="yes") {
    $specs["init_code"] = '
      $filename = $input["filename"];
      $filename = N_CleanPath ($filename);
      N_GunzipFile ($filename.".tmp", $filename);
      $data["offset"] = 0;
      $data["ctr"] = 0;
      $tables = array();
      $tables["local_test"] = "local_test";
      foreach ($tables as $table)
      {
        if ($table != "local_terra_processdata") {
          N_Log ("restore", "DeleteTable ($table)");
          MB_MUL_DeleteTable ($table);
        }
      }
    ';   
  } else {
    $specs["init_code"] = '
      $filename = $input["filename"];
      $filename = N_CleanPath ($filename);
      N_GunzipFile ($filename.".tmp", $filename);
      $data["offset"] = 0;
      $data["ctr"] = 0;
      $tables = MB_MUL_AllTables ();
      foreach ($tables as $table)
      {
        if ($table != "local_terra_processdata") {
          N_Log ("restore", "DeleteTable ($table)");
          MB_MUL_DeleteTable ($table);
        }
      }
    ';   

  }

  $specs["step_code"] = '
    $filename = $input["filename"];
    $filename = N_CleanPath ($filename);
    $fp = fopen ($filename.".tmp", "r");
    if (!$fp) {
      TERRA_EndProcess();
      N_DIE ("Failed to open $filename");
    }
    $todo = 100;
    N_Flush (-1);
    fseek ($fp, $data["offset"]);
    while (!feof ($fp) && $todo) {
      if (version_compare(phpversion(), "4.3", ">=")) {
        $line = fgets ($fp);
      } else {
        $line = fgets ($fp, 10000000);
      } 
      $data["offset"] = ftell ($fp);
      if ($line) {
        $todo--;
        $data["ctr"]++;
        $obj = unserialize (base64_decode ($line));
        $table = $obj["table"];
        $key = $obj["key"];
        $record = $obj["data"];
        if ($table != "local_terra_processdata") {
          MB_MUL_Save_NoIndex ($table, $key, $record);
        }
        N_Log ("restore", "Restore $table $key (".$data["ctr"].")");
      }
    }  
    if (feof ($fp)) $completed = true;
    fclose ($fp);
  ';

  $specs["exit_code"] = '
    $filename = $input["filename"];
    $filename = N_CleanPath ($filename);
    N_DeleteFile ($filename.".tmp");
    echo "Processed records:".$data["ctr"];
    $dir = N_CleanPath ("html::"."/metabase/sdex");
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($dir);
    $dir = N_CleanPath ("html::"."/metabase/index");
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($dir);
    $dir = N_CleanPath ("html::"."/metabase/mysqlindex");
    MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($dir);
    N_Log ("restore", "Restore completed (XML indexes deleted)");
  ';

  TERRA_MultiStep ($specs);
  if ($showprogress) {
    echo ML ("De database wordt in de achtergrond hersteld", "The database is restores in the background");
  }
}

function MB_MUL_Compact ($age=172800) // physically remove deleted records, default older then 2 days
{
}

function MB_MUL_CreateModifyTable ($table, $specs="") // variable parameters !!!
{
  N_Debug ("MB_MUL_CreateModifyTable ($table, ...");
}

function MB_MUL_Load ($table, $key)
{
  $ret = MB_MUL_Load_NoTrigger ($table, $key);
  return MB_TRIG_Load ($table, $key, $ret);
}

function MB_MUL_Export ($tables, $timelimit=1000000000)
{
  $after = time() - $timelimit;
  if ($tables) reset($tables);
  if ($tables) while (list(,$table)=each($tables)) {

    $keys = MB_MUL_AllKeys ($table);
    if ($keys) while (list(,$key)=each($keys)) {
      $obj = MB_MUL_Load ($table, $key);
      if ($obj["time"] >= $after) $result["tables"][$table][$key] = $obj;
    }
  }

  return $result;  
}

function MB_MUL_Import ($everything) // import data, keep newest
{
  N_Debug ("MB_MUL_Import (...) START");
  global $debug;
  if ($debug=="yes") N_EO ($everything);
  if ($everything) if (is_array($everything)) if ($everything["tables"]) if (is_array($everything["tables"])) {
    reset ($everything["tables"]);
    while (list($table, $allrecords)=each($everything["tables"])) {
      reset ($allrecords);
      while (list ($key, $object)=each($allrecords)) {
        $oldobject = MB_MUL_Load ($table, $key);
        if ($debug=="yes") N_EO ($oldobject);
        if ($oldobject) {
          if ($object==$oldobject) {
            N_Debug ("$table $key NO CHANGE");
          } else {
            if ($object["time"] > $oldobject["time"]) {
              N_Debug ("$table $key CHANGE, IMPORT IS NEWER");
              MB_MUL_Save ($table, $key, $object); 
            } else {
              N_Debug ("$table $key KEEP, IMPORT IS OLDER");
            }
          }
        } else {
          N_Debug ("$table $key NEW OBJECT");
          MB_MUL_Save ($table, $key, $object);
        }
      }
    }
    MB_MUL_Flush(); // flush once for each table
  }
  N_Debug ("MB_MUL_Import (...) END");
}

/*******************************************************************************************************************************/
/*** ENGINE LAYER - ULTRA ENGINE (MB_ULTRA) ***/


function MB_ULTRA_NukeDatabase($please)
{
  if ($please!="please") return;
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(getenv("DOCUMENT_ROOT") . "/metabase/ultra");
}

function MB_ULTRA_Load ($table, $key)
{
  N_Debug ("MB_ULTRA_Load ($table, $key)");
  return unserialize (N_ReadFile (
    getenv("DOCUMENT_ROOT") . "/metabase/ultra/".MB_HELPER_Key2Name($table)."/".MB_HELPER_Key2Name($key).".phpdat"));
}

function MB_ULTRA_Save ($table, $key, $object)
{
  N_Debug ("MB_ULTRA_Save ($table, $key, ...)");
  N_WriteFile (
    getenv("DOCUMENT_ROOT") . "/metabase/ultra/".MB_HELPER_Key2Name($table)."/".MB_HELPER_Key2Name($key).".phpdat", serialize($object));
}

function MB_ULTRA_Flush ()
{
  N_Debug ("MB_ULTRA_Flush ()");
  // do nothing, MB_ULTRA_Save writes to disk immidiately
}

function MB_ULTRA_Delete ($table, $key)
{
  N_Debug ("MB_ULTRA_Delete ($table, $key)");
  N_ErrorHandling (false);
  N_DeleteFile (N_CleanPath ("html::"."/metabase/ultra/".MB_HELPER_Key2Name($table)."/".MB_HELPER_Key2Name($key).".phpdat"));
  N_ErrorHandling (true);
}

function MB_ULTRA_CreateTable ($table)
{
  N_Debug ("MB_ULTRA_CreateTable ($table)");
  N_ErrorHandling (false); // allow multiple adds
  mkdir (N_CleanPath ("html::"."/metabase/ultra/".MB_HELPER_Key2Name($table), 0777));
  N_ErrorHandling (true);
}

function MB_ULTRA_DeleteTable ($table)
{
  N_Debug ("MB_ULTRA_DeleteTable ($table)");
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(getenv("DOCUMENT_ROOT") . "/metabase/ultra/".MB_HELPER_Key2Name($table));
  N_ErrorHandling (false);
  rmdir (N_CleanPath ("html::"."/metabase/ultra/".MB_HELPER_Key2Name($table)));
  N_ErrorHandling (true);
}

function MB_ULTRA_AllKeys ($table)
{
  N_Debug ("MB_ULTRA_AllKeys ($table) START");
  $list = array();
  if (file_exists (N_CleanPath ("html::"."/metabase/ultra/".MB_HELPER_Key2Name($table)))) {
    $d = dir(N_CleanPath ("html::"."/metabase/ultra/".MB_HELPER_Key2Name($table)));
    while (false !== ($entry = $d->read())) {
      if ($entry!="." && $entry!=".." && !strpos ($entry, "_t3mp")) {
        $list [str_replace (".phpdat", "", MB_HELPER_Name2Key($entry))] = str_replace (".phpdat", "", MB_HELPER_Name2Key($entry));
      }
    }
    // qqq: Maybe sort
    $d->close();
  }
  N_Debug ("MB_ULTRA_AllKeys ($table) END");
  return $list;
}

function MB_ULTRA_AllTables ()
{
  N_Debug ("MB_ULTRA_AllTables ()");
  if (!file_exists (N_CleanPath ("html::/metabase/ultra"))) {

    N_WriteFile ("html::/metabase/ultra/dummy.txt", "dummy");
    N_DeleteFile ("html::/metabase/ultra/dummy.txt");
  }
  $list = array();
  $d = dir(N_CleanPath ("html::"."/metabase/ultra"));
  while (false !== ($entry = $d->read())) {
    if ($entry!="." && $entry!="..") $list[MB_HELPER_Name2Key($entry)] = MB_HELPER_Name2Key($entry);
  }
  $d->close();
  return $list;
}

/*******************************************************************************************************************************/
/*** HELPER FUNCTIONS (MP_HELPER) ***/

function MB_HELPER_Key2Name ($key)
{
  if (!$key) return "_5f";
  $key= strtolower($key);
  $trans = array (
    " "=>"_20",
    "!"=>"_21",
    "\""=>"_22",
    "#"=>"_23",
    "$"=>"_24",
    "%"=>"_25",
    "&"=>"_26",
    "'"=>"_27",
    "("=>"_28",
    ")"=>"_29",
    "*"=>"_2a",
    "+"=>"_2b",
    ","=>"_2c",
    "-"=>"_2d",
    "."=>"_2e",
    "/"=>"_2f",
    ":"=>"_3a",
    ";"=>"_3b",
    "<"=>"_3c",
    "="=>"_3d",
    ">"=>"_3e",
    "?"=>"_3f",
    "@"=>"_40",
    "["=>"_5b",
    "\\"=>"_5c",
    "]"=>"_5d",
    "^"=>"_5e",
    "_"=>"_5f",
    "`"=>"_60",
    "{"=>"_7b",
    "|"=>"_7c",
    "}"=>"_7d",
    "~"=>"_7e");
  $key = strtr ($key, $trans);
  return $key;
}

function MB_HELPER_Name2Key ($key)
{
  $trans = array (
    "_20"=>" ",
    "_21"=>"!",
    "_22"=>"\"",
    "_23"=>"#",
    "_24"=>"$",
    "_25"=>"%",
    "_26"=>"&",
    "_27"=>"'",
    "_28"=>"(",
    "_29"=>")",
    "_2a"=>"*",
    "_2b"=>"+",
    "_2c"=>",",
    "_2d"=>"-",
    "_2e"=>".",
    "_2f"=>"/",
    "_3a"=>":",
    "_3b"=>";",
    "_3c"=>"<",
    "_3d"=>"=",
    "_3e"=>">",
    "_3f"=>"?",
    "_40"=>"@",
    "_5b"=>"[",
    "_5c"=>"\\",
    "_5d"=>"]",
    "_5e"=>"^",
    "_5f"=>"_",
    "_60"=>"`",
    "_7b"=>"{",
    "_7c"=>"|",
    "_7d"=>"}",
    "_7e"=>"~");
  $key = strtr ($key, $trans);

  return $key;
}

function MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($location, $ignore=array()) 
{
  if (strlen ($location)<5) N_DIE ("ASSERT: DestroyDirContents called with short parameter");
  $location = N_CleanPath ($location);
  if (strlen ($location)<5) N_DIE ("ASSERT: DestroyDirContents called with short parameter");
  N_ErrorHandling (false);
  if (substr($location,-1) <> "/")
    $location = $location."/";
  $all=opendir($location);
  while ($file=readdir($all)) {
    if (!in_array($file, $ignore)) {
      if (is_dir($location.$file) && $file <> ".." && $file <> ".") {
        MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($location.$file, $ignore);
        N_CHMod ($location.$file);
        rmdir($location.$file);
        unset($file);
      } elseif (!is_dir($location.$file)) {
        N_CHMod ($location.$file);
        N_DeleteFile ($location.$file);
        unset($file);
      }
    }
  }
  closedir ($all);
  N_ErrorHandling (true);
}

// Compare multiple arguments 
// Use the second argument to determine the ordering of records for which the value of the first argument is the same, 
// use the third argument if the value of both the first two arguments is the same, etc...
// The result can be used in metabase queries. It should not be used "directly" in MySQL.
// Example: $specs["sort"] = 'MB_MultiComparable($record["shorttitle"], $record["meta_author"], QRY_DMS_Changed_v1($record))';
function MB_MultiComparable($arg1)
{
  $args = func_get_args();
  foreach ($args as $arg) {
    $result .= SDEX_2ComparableString_Raw($arg) . "#";
  }
  return $result;
}

// Add uniqueness to sort values to speed up "gotoobject"
// Example: $specs["sort"] = 'MB_ComparableUnique($record["shorttitle"], $key)'
// $key should be unique and meaningless (because the sort order of $key is not preserved).
function MB_ComparableUnique($arg, $key) 
{
  $noise = (crc32($key) & 0xffff); // (tested) this gives the same output on 64bit and 32bit systems, regardless of what the PHP coders have done with the leftmost bit
  $result = SDEX_2ComparableString_Raw($arg) . "#" . $noise;
  return $result;
}

function MB_INDEX_RemoveFlag ($source) 
{
  $flag = "html::tmp/" . md5($source)."-"."index.flag";
  N_DeleteFile($flag);
}

function MB_INDEX_PulseFlag ($source) 
{
  $flag = "html::tmp/" . md5($source)."-"."index.flag";
  N_WriteFile($flag, time());
}

function MB_INDEX_CheckFlag ($source, $maxtime = 600) // 10 minutes
{
  $flag = "html::tmp/" . md5($source)."-"."index.flag";
  N_Lock ("MB_INDEX_CheckFlag($source)");
  $file = N_ReadFile($flag);
  if ($file) {
    $diff = $maxtime;
    //echo "file: $file<br>time: ".time()."<br>";
    if ((time() - $file) > $diff) {
      N_WriteFile($flag, time());
      N_Unlock ("MB_INDEX_CheckFlag($source)");
//    N_Log ("MB_INDEX", "CHECK FLAG TRUE (timeout) $source");
      return true;
    } else {
//    N_Log ("MB_INDEX", "CHECK FLAG FALSE $source");
      N_Unlock ("MB_INDEX_CheckFlag($source)");
      return false;
    }
  } else {
    N_WriteFile($flag, time());
//  N_Log ("MB_INDEX", "CHECK FLAG TRUE (not busy) $source");
    N_Unlock ("MB_INDEX_CheckFlag($source)");
    return true;
  }
}

?>