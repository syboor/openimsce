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



uuse ("dhtml");
uuse ("shield");
 
function DYNA_TextField ($getcode, $setcode)
{
  eval ($getcode);
  $id = N_GUID();
  $api = DYNA_CreateAPI ('
    if ($input["command"]=="get") {
      eval ($serverinput["getcode"]);
      echo "D_ServerUpdate (\'".$serverinput["id"]."\', \'".$value."\');";
    } else if ($input["command"]=="set") {
      $value = $input["value"];
      eval ($serverinput["setcode"]);
    }
  ', array (
    "id"=>$id,
    "getcode"=>$getcode,
    "setcode"=>$setcode
  ));
  $ret = "<input onchange=\"D_ClientUpdate('".$api."', '".$id."');\" type=\"text\" id=\"$id\" name=\"$id\" value=\"$value\"/>";
  $ret .= DHTML_EmbedJavascript ('
    D_AddWatch (\'var i=new Array(); i["command"]="get";D_SendMessage ("'.$api.'",i);\', "'.$id.'", 1000);
    D_AddWatch (\'D_ClientUpdate ("'.$api.'","'.$id.'");\', 0, 1000);
  ');
  return $ret;
}

function DYNA_CreateAPI ($code, $serverinput=array())
{
  $enc = SHIELD_Encode (array ("code"=>$code, "serverinput"=>$serverinput));
  SHIELD_FlushEncoded();
  return $enc;
}

function DYNA_ProcessRequest ()
{
  global $js, $id, $sid, $i, $test, $multi, $input, $dbmuif;
  if ($dbmuif) {
    uuse ("dbm");
    DBMUIF_ProcessAJAXRequest();
  }
  if ($js) {
    echo N_ReadFile ("html::/nkit/dyna.js");
  }
  if ($test) {
    N_Sleep (2000);
    echo "$test !!!";
  }
  if ($multi) {
    $i = str_replace ("\\\"", "\"", $i);
    $i = str_replace ("\\\'", "\'", $i);
    $all = unserialize($i);
    for ($j=1; $j<=$all["amount"]; $j++) {
      $id = $all[$j]["id"];
      $input = $all[$j]["input"];
      $dec = SHIELD_Decode ($id);
      $serverinput = $dec["serverinput"];
      $result["serverinput"] = $serverinput;
      $result["input"] = $input;
      global $runandcapture; 
      $runandcapture=true; 
      ob_start();
      $jscode = "";
      $output = array();
      eval ($dec["code"]);
      $captured = ob_get_contents();
      ob_end_clean();
      $runandcapture=false; 
      $result["output"] = $output;
      $result["jscode"] = $captured.$jscode;
      $totalresult[++$totalctr] = $result;    
    }
    $totalresult["count"] = $totalctr;
    $totalresult["type"] = "multi";
    echo serialize ($totalresult);
  }
  if ($sid) {
    $dec = SHIELD_Decode ($sid);
    $serverinput = $dec["serverinput"];
    eval ($dec["code"]);
  }
  if ($id) {
    $i = str_replace ("\\\"", "\"", $i);
    $i = str_replace ("\\\'", "\'", $i);
    $input = unserialize($i);
    $dec = SHIELD_Decode ($id);
    $serverinput = $dec["serverinput"];
    $result["serverinput"] = $serverinput;
    $result["input"] = $input;
    global $runandcapture; 
    $runandcapture=true; 
    ob_start();
    eval ($dec["code"]);
    $captured = ob_get_contents();
    ob_end_clean();
    $runandcapture=false; 
    $result["output"] = $output;
    $result["jscode"] = $captured.$jscode;
    $result["type"] = "single";
    echo serialize ($result);
  }
}

function DYNA_IncludeDynaJS ()
{
  return '<script language="JavaScript" type="text/javascript" src="/nkit/dyna.php?js='.N_FileMD5("html::/nkit/dyna.js").'"></script>';
}

function DYNA_Save2Browser ($key, $value)
{
}

?>