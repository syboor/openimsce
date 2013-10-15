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



include (N_CleanPath (getenv("DOCUMENT_ROOT") . "/nusoap/nusoap.php"));

function SOAP_Online ()
{
  global $soap_online, $soap_online_known;
  if (!$soap_online_known) { // one investigation per request
    uuse ("grid");
    $stat = MB_Load ("local_grid_servers", "master");
    if (abs(time()-$stat["laststatusupdate"]) > 600) {
      $soap_online = false; // grid says I am offline (no heartbeat for 10 minutes)
    } else {
      $time = MB_Load ("globalvars", "offline::".N_CurrentServer());
      if (time()<$time) { // less than 10 minutes ago that N_GetPage failed
        $soap_online = false;
      } else {
        global $VBrowser4GetPage;
        $backup = $VBrowser4GetPage->timeout;
        $VBrowser4GetPage->timeout = 2;
        $test = N_GetPage ("www.google.com");
        $VBrowser4GetPage->timeout = $backup;
        if (strpos ($test, "Google")) {
          $soap_online = true; // online
        } else {
          MB_Save ("globalvars", "offline::".N_CurrentServer(), time()+600);
          $soap_online = false; // offline, don't try again for 10 minutes
        }
      }
    }  
    $soap_online_known = true;
  }
  return $soap_online;
}

function SOAP_Call ($server_url, $server_urn, $function, $params, $quick=0)
{
  global $nusoapclient, $debug;
  if ($quick) {
    $dfckey = DFC_Key ("v3", $server_url, $server_urn, $function, $params);
    if (DFC_Exists($dfckey)) return DFC_Read ($dfckey);
  }
  if (!$nusoapclient[$server_url]) $nusoapclient[$server_url] = new nusoapclient($server_url);
  if ($debug=="yes") $nusoapclient[$server_url]->debug_flag = 1;
  $result = $nusoapclient[$server_url]->call($function, $params, $server_urn, $server_urn); 
  if ($quick) {
    DFC_Write ($dfckey, $result, 24*7);
  }
  return $result;  
}

function SOAP_WSDL_Call ($wsdl, $function, $params, $quick=0)
{
  global $nusoapclient, $debug;
  if ($quick) {
    $dfckey = DFC_Key ("v3", $wsdl, $function, $params);
    if (DFC_Exists($dfckey)) return DFC_Read ($dfckey);
  }
  if (!$nusoapclient["wsdl"]) $nusoapclient["wsdl"] = new nusoapclient($wsdl,'wsdl');
  if ($debug=="yes") $nusoapclient["wsdl"]->debug_flag = 1;
  $result = $nusoapclient["wsdl"]->call($function, $params);
  if ($quick) {
    DFC_Write ($dfckey, $result, 24*7);
  }
  return $result; 
}

function SOAP_doGoogleSearch ($q, $amount=10)
{
  return SOAP_Call ("http://api.google.com/search/beta2", "urn:GoogleSearch", "doGoogleSearch", 
    array (
      'key' => 'KZtI8wv0EHgwv3JI9ihl48JZBoyYoFqT',   // Google license key
      'q'   => $q,                                   // search term
      'start' => 0,                                  // start from result n
      'maxResults' => $amount,                       // show a total of n results
      'filter' => true,                              // remove similar results
      'restrict' => '',                              // restrict by topic
      'safeSearch' => false,                         // remove adult links
      'lr' => '',                                    // restrict by language
      'ie' => '',                                    // input encoding
      'oe' => ''                                     // output encoding
    ),1
  );
}

function SOAP_doGoogleSpellingSuggestionNL ($q)
{
  $key = DFC_Key ("SOAP_GoogleCorrectNL ($q) v3");
  if (DFC_Exists ($key)) {
    return DFC_Read ($key);
  } else {
//    if (!SOAP_Online()) return ""; // gv 16-02-2011  allow suggestions if the clock is out of sync
    $c = N_GetPage ("http://www.google.nl/search?hl=nl&q=".urlencode($q));
    $c = SEARCH_HTML2TEXT ($c);
    if (strpos ($c, "Google")) {
      $regexp = '#Bedoelde u:\\s*([a-zA-Z]*)\\s#i';
      if (PREG_Match ($regexp, $c, $match)) {
        return DFC_Write ($key, SEARCH_HTML2TEXT ($match[1]));
      } else {
        return DFC_Write ($key, "");
      }
    } else {
      return "";
    }
  }
}

function SOAP_doGoogleSpellingSuggestion ($q)
{
  return SOAP_Call ("http://api.google.com/search/beta2", "urn:GoogleSearch", "doSpellingSuggestion", 
    array (
      'key' => 'KZtI8wv0EHgwv3JI9ihl48JZBoyYoFqT',   // Google license key
      'phrase'   => $q                               // phrase
    ),1
  );
}

function SOAP_getDelayedQuote($stock) 
{
  return SOAP_WSDL_Call ('http://services.xmethods.net/soap/urn:xmethods-delayed-quotes.wsdl', 'getQuote', array('symbol'=>$stock));
}

function SOAP_Translate ($from, $to, $text)
{
  return SOAP_WSDL_Call ('http://www.xmethods.net/sd/2001/BabelFishService.wsdl', 'BabelFish', 
                         array('translationmode'=>$from.'_'.$to, 'sourcedata'=>$text));
}

function SOAP_SiteInspect ($site)
{
  return SOAP_WSDL_Call ('http://www.flash-db.com/services/ws/siteInspect.wsdl', 'doSiteInspect',
                         array(
                   	   "username"	=>"Any",
	                   "password"	=>"Any",	 
	                   "siteURL"	=>$site),1);
}

function SOAP_WhoIs ($site)
{
  return SOAP_WSDL_Call ('http://www.SoapClient.com/xml/SQLDataSoap.wsdl', 'ProcessSRL',
                         array(
                           "SRLFile" => "/xml/WHOIS.SRI",
                           "RequestName" => "Whois", 
                           "key" => $site), 1);
}

?>