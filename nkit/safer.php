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


function N_register_globals($order = 'egpcs')
{
    // define a subroutine
    if(!function_exists('N_register_global_array'))
    {
        function N_register_global_array($superglobal)
        {
            foreach($superglobal as $varname => $value)
            {
                global $$varname;
                $$varname = $value;
            }
        }
    }

    $order = explode("\r\n", trim(chunk_split($order, 1)));
    foreach($order as $k)
    {
        switch(strtolower($k))
        {
            case 'e':    N_register_global_array($_ENV);        break;
            case 'g':    N_register_global_array($_GET);        break;
            case 'p':    N_register_global_array($_POST);        break;
            case 'c':    N_register_global_array($_COOKIE);    break;
            case 's':    N_register_global_array($_SERVER);    break;
        }
    }
}

  N_register_globals();
  if(!isset ($HTTP_COOKIE_VARS)) $HTTP_COOKIE_VARS=$_COOKIE;
  if(!isset ($HTTP_SERVER_VARS)) $HTTP_SERVER_VARS=$_SERVER;
  if(!isset ($HTTP_POST_VARS)) $HTTP_POST_VARS=$_POST;
  if(!isset ($HTTP_GET_VARS)) $HTTP_GET_VARS=$_GET;
  if(!isset ($HTTP_ENV_VARS)) $HTTP_ENV_VARS=$_ENV;

  // Generic
  global $myconfig, $supergroupname;
  global $uuse_code_stack, $uuse_call_depth;
  $myconfig = array();
  $uuse_code_stack = $uuse_call_depth = $supergroupname = null;

  // Shield 
  global $SHIELD_SecsecList_cached, $SHIELD_SecsecList_cache, $SHIELD_ADG_E_C, $tmpcache, $currentuser_rememberresult, $busycalling_hasglobalright_extra, $busycalling_adddynamicgroups, $localsecret;
  global $SHIELD_HasProcessRight_cache_known, $SHIELD_HasProcessRight_cache_value;
  global $busycalling_hasobjectright_extra;
  global $shield_allowedworkflows;
  global $encodekey;
  global $encodeindex;
  global $encodedata;
  global $PHP_AUTH_USER_backup, $PHP_AUTH_PW_backup;

  $SHIELD_HasProcessRight_cache_known = $SHIELD_HasProcessRight_cache_value = array();
  $SHIELD_SecsecList_cached = array();
  $SHIELD_SecsecList_cache = array();
  $SHIELD_ADG_E_C = array();
  $tmpcache = array();
  $currentuser_rememberresult = null;
  $busycalling_hasglobalright_extra = null;
  $busycalling_adddynamicgroups = null;
  $localsecret = null;
  $busycalling_hasobjectright_extra = null;
  $shield_allowedworkflows = array();
  $encodekey = $encodeindex = null;
  $encodedata = array();
  $PHP_AUTH_USER_backup = $PHP_AUTH_PW_backup = null;

  // improve rand quality
  list ($usec, $sec) = explode(" ", microtime());
  srand ((int)(mt_rand(1,1000000)+$sec+($usec*1000000)+getmypid()+$_SERVER["REMOTE_PORT"]));

  // Metabase
  global $mb_cac_inmemorycache, $mb_cac_inmemorycopy, $mb_cac_inmemorypresent;
  $mb_cac_inmemorycache = $mb_cac_inmemorycopy = $mb_cac_inmemorypresent = array();

  global $MB_CAC_AllTables, $MB_REP_Save_PostBlah, $MB_REP_Save_PostBlah_known;
  $MB_CAC_AllTables = $MB_REP_Save_PostBlah = $MB_REP_Save_PostBlah_known = array();

  global $mgt_version, $MB_INDEX_MGT_Get, $MB_INDEX_MGT_Get_loaded, $MB_MUL_Save_trigger_test, $mb_index_busy;
  $mgt_version = $MB_INDEX_MGT_Get = $MB_INDEX_MGT_Get_loaded = $MB_MUL_Save_trigger_test = $mb_index_busy = array();

  global $MB_MUL_Save_trigger_test, $MB_MUL_Save_trigger_needed, $triggerreaddata_nesting;
  $MB_MUL_Save_trigger_test = $MB_MUL_Save_trigger_needed = $triggerreaddata_nesting = null;

  global $loaded_fasttables;
  $loaded_fasttables = array();

  global $mb_mul_onekey_table, $mb_mul_onekey_allkeys, $mb_mul_onekey_allkeys_indexed;
  $mb_mul_onekey_table = $mb_mul_onekey_allkeys = $mb_mul_onekey_allkeys_indexed = array();

  global $mb_hyper_cache, $mb_hyper_dirty;
  $mb_hyper_cache = $mb_hyper_dirty = array();

  global $MB_MYSQL_Name2Key_map;
  $MB_MYSQL_Name2Key_map = array();

  global $mb_virtual_load_functions;
  $mb_virtual_load_functions = array();

  global $key2name_cache;
  $key2name_cache = array();


  // Flex
  global $FLEX_LocalComponents, $FLEX_LoadAllLowLevelFunctions;
  $FLEX_LocalComponents = $FLEX_LoadAllLowLevelFunctions= array();

  global $flexcounters;
  $flexcounters = array();

  global $flex_loadcache, $flex_loadcache_loaded;
  $flex_loadcache = $flex_loadcache_loaded = array();;

  global $FLEX_RepairCache;
  $FLEX_RepairCache = array();

  global $FLEX_Packages_result, $FLEX_Packages_loaded;
  $FLEX_Packages_result = $FLEX_Packages_loaded = array();

  global $runandcapture;
  $runandcapture = array();

  global $RAWREQUEST, $RAWPOST, $RAWGET, $RAWCOOKIE, $IMS_XssFilter_done, $IMS_XSSFilter_scriptcategory;  
  $RAWREQUEST = $RAWPOST = $RAWGET = $RAWCOOKIE = array();  
  $IMS_XssFilter_done = $IMS_XSSFilter_scriptcategory = false;

  // ML  
  global $ml_loadcache, $ml_loadcache_loaded, $ml_loaddatabase_internal, $ml_loaddatabase_custom, $ml_loaddatabase_loaded;  
  $ml_loadcache = $ml_loadcache_loaded = $ml_loaddatabase_internal = $ml_loaddatabase_custom = $ml_loaddatabase_loaded = array();

  // IMS
  global $ims_coolbar_called, $ims_domain2siteinfo;
  $ims_coolbar_called = $ims_domain2siteinfo = array();

  global $activesupergroupname, $knownsupergroupname;
  $activesupergroupname = $knownsupergroupname = array();

  // Nkitloader itself
  global $N_Flush_Outputfilter;
  $N_Flush_Outputfilter = array();

  // Make sure these autoglobals always come from $_SERVER, regardless of "variable_order" setting
  global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
  $REMOTE_ADDR=$_SERVER['REMOTE_ADDR'];
  $REMOTE_HOST=$_SERVER['REMOTE_HOST'];
  $SERVER_ADDR=$_SERVER['SERVER_ADDR'];

  function SAFER_AddSlashes($var) { // works with arrays
    if (is_array($var)) {
      $result = array();
      foreach ($var as $key => $value) {
        $key = SAFER_AddSlashes($key);
        $value = SAFER_AddSlashes($value);
        $result[$key] = $value;
     }
      return $result;
    } else {
      return addslashes($var);
    }
  }

  // Simulate magic quotes
  global $N_magic_quotes_gpc;
  $N_magic_quotes_gpc = true;
  if (!ini_get("magic_quotes_gpc")) {
    $skip = ((strpos(getenv("SCRIPT_NAME"), "doku.php") !== false) || 
             (substr(getenv("SCRIPT_NAME"), 0, 12) == "/private/php")
            );
    if (!$skip) { 
      foreach (array('_GET', '_POST', '_COOKIE') as $requesttype) { // Tested: php "magic_quotes_gpc" setting does not escape file names in $_FILES, so neither do we
        foreach ($GLOBALS[$requesttype] as $fieldname => $value) {
          $newvalue = SAFER_AddSlashes($value);
          $newfieldname = addslashes($fieldname);
          if (true) { // Do it for all parameters (even if newvalue == value and newfieldname == fieldname); this will keep all parameters in the same "order"
            unset($GLOBALS[$requesttype][$fieldname]);
            $GLOBALS[$requesttype][$newfieldname]= $newvalue;
            if ($_REQUEST[$fieldname] == $value) {
              unset($_REQUEST[$fieldname]);
              $_REQUEST[$newfieldname] = $newvalue;
            }
              if ($GLOBALS[$fieldname] == $value) {
              unset($GLOBALS[$fieldname]);
              $GLOBALS[$newfieldname] = $newvalue;
            }
            if ($GLOBALS['HTTP'.$requesttype.'_VARS'] && $GLOBALS['HTTP'.$requesttype.'_VARS'][$fieldname] == $value) {
              unset($GLOBALS['HTTP'.$requesttype.'_VARS'][$fieldname]);
              $GLOBALS['HTTP'.$requesttype.'_VARS'][$newfieldname] = $newvalue;
            }
          }
        }
      }
    } else {
      $N_magic_quotes_gpc = false;
    }
  }
?>
