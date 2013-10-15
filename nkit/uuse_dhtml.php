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
  jQuery Datatables links:
    http://datatables.net/extras/thirdparty/ColumnFilterWidgets/DataTables/extras/ColumnFilterWidgets/
*/


UUSE_DO ('DHTML_Init ();');

function DHTML_Init () {
  global $myconfig;
  foreach ($myconfig as $sgn => $siteconfig) {
    if (is_array($siteconfig) && substr($sgn, -6) == "_sites") {
      // Check and/or set minimum versions of JQuery and JQuery UI required by some core functionality

      if (!$siteconfig["jqueryversion"]) $myconfig[$sgn]["jqueryversion"] = "1.5.2";
      if (!$siteconfig["jqueryuiversion"]) $myconfig[$sgn]["jqueryuiversion"] = "1.8.13";

      // autocompletefields: requires jquery ui == 1.8.* (and jquery >= 1.3.2, but that is the oldest version we include anyway)
      if ($siteconfig["autocompletefields"] == "yes" && $siteconfig["jqueryuiversion"] && substr($siteconfig["jqueryuiversion"], 0, 4) !== "1.8.") {
        unset($myconfig[$sgn]["autocompletefields"]);
        trigger_error("DHTML_Init: Jquery UI version conflict between settings jqueryuiversion and autocompletefields", E_USER_WARNING);
      }
      // treeview requires jquery >= 1.4 (assume it is forward compatible with 1.5 and 1.6 etc.)
      if (is_array($siteconfig["cms"]) && $siteconfig["cms"]["showtreeview"] == "yes" &&
          $siteconfig["jqueryversion"] && version_compare($siteconfig["jqueryversion"], "1.4.2") < 0) {
        unset($myconfig[$sgn]["cms"]["showtreeview"]);
        trigger_error("DHTML_Init: Jquery version conflict between settings jqueryversion and showtreeview", E_USER_WARNING);
      }
    }
  }
}

uuse ("tables");

function DHTML_LoadjQuery ($p_loadedfromflex = false)
{ /* Default (old) behaviour: 
   *  Returns a string, which should be inserted (by CMS component in the template) into the page.
   *  If called repeatedly, returns nothing after the first time.
   * New behaviour, with $myconfig[$supergroupname]["jqueryautoinsert"] = "yes":
   *  Returns nothing.
   *  When the page has been created, the JQuery stuff is inserted into the HTML <head> section.
   *  If this is not possible, the JQuery stuff is echo'd immediately.
   *  If called repeatedly, the JQuery stuff is only inserted once.
   *  If there is already JQuery stuff hard-coded into the template, nothing will be inserted, provided 
   *  that the paths of the external scripts / stylesheets match.
   *
   * USAGE GUIDELINES:
   *  1) Call DHTML_LoadJQuery in the request in which JQuery is used. If JQuery is used inside a popup form, 
   *     call DHTML_LoadJQuery in the "precode" or inside a PHP code field; do NOT call it while creating 
   *     the form specs.
   *  2) Condition 1 may not be true in legacy custom code. Check and ensure that this condition is met 
   *     before enabling the "jqueryautoinsert" option.
   *  3) In core code and in importable components/packages, always do something (echo) with the return 
   *     value, so that it will work without the "jqueryautoinsert" setting. If it is not possible to do 
   *     something with the return value, use DHTML_RequireJQuery instead of DHTML_LoadJQuery.
   */

  global $myconfig, $jqueryloaded;
  $supergroupname = IMS_SuperGroupname();
  if ($myconfig[$supergroupname]["jqueryautoinsert"] == "yes") return DHTML_RequireJQuery();
  $version = ($myconfig[$supergroupname]["jqueryversion"]) ? $myconfig[$supergroupname]["jqueryversion"] : "1.5.2";
  $content = '';

  if($p_loadedfromflex) {
    // we're loaded explicitly from flex, don't set the global jqueryloaded
    $content .= "\r\n".'<!-- loading jQuery base -->'."\r\n";
    $content .= '<script type="text/javascript" src="/openims/libs/jquery/jquery-'.$version.'.min.js"></script>'."\r\n";
    $content .= '<script type="text/javascript" src="/nkit/javascript/jquerykit.js"></script>'."\r\n";
    if( $myconfig[$supergroupname]["loadjqueryui"] != "no") {
      $content .= DHTML_LoadjQueryUI();
    }
  } else {

    if( !$jqueryloaded ) {
      $jqueryloaded = true;
      $content .= "\r\n".'<!-- loading jQuery base -->'."\r\n";
      $content .= '<script type="text/javascript" src="/openims/libs/jquery/jquery-'.$version.'.min.js"></script>'."\r\n";
      $content .= '<script type="text/javascript" src="/nkit/javascript/jquerykit.js"></script>'."\r\n";
      if( $myconfig[$supergroupname]["loadjqueryui"] != "no") {
        $content .= DHTML_LoadjQueryUI();
      }
    } else {
      //N_Log ("errors", "DHTML_LoadjQuery called multiple times");
    }

  }
  return $content;
}

function DHTML_LoadjQueryUI ()
{
  global $myconfig;
  $version = $myconfig[IMS_SuperGroupName()]["jqueryuiversion"];
  if ($version == "1.8.13") {
    $content = '';
    $language = ML_GetLanguage();
    // load the full -but minified- UI library
    $content .= "\r\n".'<!-- loading jQuery UI complete -->'."\r\n";
    $content .= '<script type="text/javascript" src="/openims/libs/jquery/ui-'.$version.'/js/jquery-ui-1.8.13.custom.min.js"></script>'."\r\n";
    $content .= '<script type="text/javascript" src="/openims/libs/jquery/ui-'.$version.'/development-bundle/ui/i18n/jquery.ui.datepicker-'.$language.'.js"></script>'."\r\n";
    $content .= '<link type="text/css" href="/openims/libs/jquery/ui-'.$version.'/css/openims/jquery-ui-1.8.13.custom.css"  rel="stylesheet" />'."\r\n";

  } else {
    $content = '';
    $language = ML_GetLanguage();
    // load the full -but minified- UI library
    $content .= "\r\n".'<!-- loading jQuery UI complete -->'."\r\n";
    $content .= '<script type="text/javascript" src="/openims/libs/jquery/ui/minified/jquery-ui.min.js"></script>'."\r\n";
    $content .= '<script type="text/javascript" src="/openims/libs/jquery/ui/minified/i18n/ui.datepicker-'.$language.'.min.js"></script>'."\r\n";
    $content .= '<link type="text/css" href="/openims/libs/jquery/themes/redmond/ui.all.css" rel="stylesheet" />'."\r\n";
  }
  return $content;
}

//ericd 210711 added functionality for the JQuery version of TinyMCE
function DHTML_RequireJQuery($ui = "jquery-ui") {
  /* Does autoinsert (into the HTML <head> section) or echo. See DHTML_LoadJQuery. */
  global $myconfig;
  $supergroupname = IMS_SuperGroupname();
  
  if ($ui == "tinymcejquery" && isset($myconfig[$supergroupname]["tinymcejqueryversion"]) && $myconfig[$supergroupname]["tinymceversion"] == "jquery") {
  
	//ericd 080711 needed for TinyMCE JQuery version
    if($myconfig[$supergroupname]["tinymcejqueryversion"])
		$tinyjqversion = $myconfig[$supergroupname]["tinymcejqueryversion"];
	else
		$tinyjqversion = "1.3";	
	IMS_AddHtmlHeader('script', array("src"=>"/openims/libs/jquery/jquery-{$tinyjqversion}.min.js"));
  }
  else {
  //ericd 210711 original code 
  $version = ($myconfig[$supergroupname]["jqueryversion"]) ? $myconfig[$supergroupname]["jqueryversion"] : "1.5.2";
  $language = ML_GetLanguage();
  IMS_AddHtmlHeader('script', array("src"=>"/openims/libs/jquery/jquery-{$version}.min.js"));
  
  if ($myconfig[$supergroupname]["loadjqueryui"] != "no") {
    if ($ui == "jquery-ui") {
      $version = $myconfig[IMS_SuperGroupName()]["jqueryuiversion"];
      if ($version == "1.8.13") {
        IMS_AddHtmlHeader('script', array("src"=>"/openims/libs/jquery/ui-{$version}/js/jquery-ui-1.8.13.custom.min.js"));
        IMS_AddHtmlHeader('script', array("src"=>"/openims/libs/jquery/ui-{$version}/development-bundle/ui/i18n/jquery.ui.datepicker-{$language}.js"));
        IMS_AddHtmlHeader('script', array("src"=>"/nkit/javascript/jquerykit.js"));
        IMS_AddStylesheet("/openims/libs/jquery/ui-{$version}/css/openims/jquery-ui-1.8.13.custom.css");
      } else {
        IMS_AddHtmlHeader('script', array("src"=>"/openims/libs/jquery/ui/minified/jquery-ui.min.js"));
        IMS_AddHtmlHeader('script', array("src"=>"/openims/libs/jquery/ui/minified/i18n/ui.datepicker-{$language}.min.js"));
        IMS_AddHtmlHeader('script', array("src"=>"/nkit/javascript/jquerykit.js"));
        IMS_AddStylesheet("/openims/libs/jquery/themes/redmond/ui.all.css");
      }
    }
  } 
 } //end if tinymcejquery 
}


function DHTML_RequireFancybox() {
  /* Does autoinsert (into the HTML <head> section) or echo. */
  DHTML_RequireJQuery(""); // no ui needed
  IMS_AddHtmlHeader('script', array("src"=>"/openims/libs/fancybox/jquery.fancybox-1.3.0.pack.js"));
  IMS_AddHtmlHeader('script', array("src"=>"/openims/libs/fancybox/jquery.mousewheel-3.0.2.pack.js"));
  IMS_AddStylesheet("/openims/libs/fancybox/jquery.fancybox-1.3.0.css", "screen");
  // In addition to calling DHTML_RequireFancybox, you also need some inline script to assiociate fancybox with a class. Example:
  // IMS_AddHtmlHeader('script', array(), '$(document).ready( function() { $("a.fancy_jwplayer").fancybox( { type:"iframe" , width:540, height:340 , scrolling:"no" } ); } );');
}

// Recommended function for OpenIMS CORE development
function DHTML_RequireAll ($level = 1) { // TODO: Prevent multiple calls with different levels, add setting to call this from the mixer
  if ($level == 1) {
    $language = ML_GetLanguage();

    // jQuery (http://jquery.com)
    IMS_AddHtmlHeader('script', array('src'=>'/openims/libs/jquery/jquery-1.5.2.min.js'));
  
    // jQuery UI (http://jqueryui.com)
    IMS_AddHtmlHeader('script', array('src'=>'/openims/libs/jquery/ui-1.8.13/js/jquery-ui-1.8.13.custom.min.js'));
    IMS_AddHtmlHeader('script', array('src'=>"/openims/libs/jquery/ui-1.8.13/development-bundle/ui/i18n/jquery.ui.datepicker-{$language}.js"));
    IMS_AddStylesheet('/openims/libs/jquery/ui-1.8.13/css/openims/jquery-ui-1.8.13.custom.css');

    // Fancy box (http://fancybox.net)
    IMS_AddHtmlHeader('script', array('src'=>'/openims/libs/fancybox/jquery.fancybox-1.3.0.pack.js'));
    IMS_AddHtmlHeader('script', array('src'=>'/openims/libs/fancybox/jquery.mousewheel-3.0.2.pack.js'));
    IMS_AddStylesheet('/openims/libs/fancybox/jquery.fancybox-1.3.0.css', 'screen');

    // Datatables (http://datatables.net)
    IMS_AddHtmlHeader('script', array('src'=>'/openims/libs/jquery/datatables-1.8.2/media/js/jquery.dataTables.min.js'));

    // OpenIMS
    IMS_AddHtmlHeader('script', array('src'=>'/nkit/javascript/jquerykit.js'));
  }
  if ($level == 2) {
    $language = ML_GetLanguage();

    // jQuery (http://jquery.com)
    IMS_AddHtmlHeader('script', array('src'=>'/openims/libs/jquery/jquery-1.5.2.min.js'));
  
    // jQuery UI (http://jqueryui.com)
    IMS_AddHtmlHeader('script', array('src'=>'/openims/libs/jquery/ui-1.8.13/js/jquery-ui-1.8.13.custom.min.js'));
    IMS_AddHtmlHeader('script', array('src'=>"/openims/libs/jquery/ui-1.8.13/development-bundle/ui/i18n/jquery.ui.datepicker-{$language}.js"));
    IMS_AddStylesheet('/openims/libs/jquery/ui-1.8.13/css/openims/jquery-ui-1.8.13.custom.css');

    // Fancy box (http://fancybox.net)
    IMS_AddHtmlHeader('script', array('src'=>'/openims/libs/jquery/fancybox-2.0.3/jquery.fancybox.pack.js'));
    IMS_AddHtmlHeader('script', array('src'=>'/openims/libs/jquery/fancybox-2.0.3/jquery.mousewheel-3.0.6.pack.js'));
    IMS_AddStylesheet('/openims/libs/jquery/fancybox-2.0.3/jquery.fancybox.css', 'screen');

    // Datatables (http://datatables.net)
    IMS_AddHtmlHeader('script', array('src'=>'/openims/libs/jquery/datatables-1.8.2/media/js/jquery.dataTables.min.js'));

    // OpenIMS
    IMS_AddHtmlHeader('script', array('src'=>'/nkit/javascript/jquerykit.js'));
  }
}

function DHTML_AskURL ($ask, $url) 
{ 
  $form = array();      
  $form["title"] = $ask;
  $form["input"]["url"] = $url;     
  $form["formtemplate"] = '
        <table width=100>
          <tr><td colspan=2><font face="arial" color="#ff0000" size=2><b>'.$ask.'</b></font></td></tr>
          <tr><td colspan=2>&nbsp</td></tr>
           <tr><td colspan=2><nobr><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></nobr></td></tr>
        </table>
  ';         
  $form["postcode"] = '
    $gotook = "closeme&parentgoto:".$input["url"];
  ';
  return FORMS_URL ($form);  
}

function DHTML_Eolas ($content)
{
  $cs = "''";
  for ($i=0; $i<strlen($content); $i++) {
    $cs .= "+String.fromCharCode(".ord(substr($content,$i,1)).")";
  }
  $content = "document.write($cs)";
  $key = SHIELD_Encode ($content);
  return "<script type=\"text/javascript\" src=\"/ufc/rawhtml/$key/script.js\"></script>"; 
}

function DHTML_Xform2URL ($xform, $resulthandler)
{
  $resulthandler = '$rawxml = TMP_LoadObject ($input);'.$resulthandler;
  $tmpid = N_GUID();
  $so1["input"] = $tmpid;
  $so1["code"] = $resulthandler;
  $shieldid1 = SHIELD_Encode ($so1);
  $so2["code"] = '
    echo DHTML_EmbedJavascript ("window.parent.location.href=\"$input\";");
  ';
  $so2["input"] = "/ufc/eval/$shieldid1/page.html";
  $shieldid2 = SHIELD_Encode ($so2);
  $xform = str_replace ("#SUBMIT#", "/openims/capturexml.php?tmpid=$tmpid", $xform);
  $xform = str_replace ("#CONFIRM#", "/ufc/eval/$shieldid2/page.html", $xform);
  return DHTML_RawHTML2URL ($xform);
}

function DHTML_RawHTML2URL ($rawhtml, $params)
{
  $key = SHIELD_Encode ($rawhtml);
  return "/ufc/rawhtml/$key/page.html";  
}

function DHTML_InitDragDrop ($specs)
{
  global $initdragdrop;
  global $myconfig; if ($myconfig[IMS_SuperGroupName()]["dragdrop"]!="yes") return "";  
  $initdragdrop = true;
  $url = FORMS_URL ($specs, false);
  $code = DHTML_PopupURL ($url, 1, false, true, true);
  // LF20091201: Minimize number of http requests by combining javascript into one file.
  $dhtml = '<script type="text/javascript" src="/ufc/rapid/openims/javascript/dnd/all-min.js"></script>';
  //$dhtml = '<script type="text/javascript" src="/openims/javascript/dnd/dragsource.js"></script>';
  //$dhtml .= '<script type="text/javascript" src="/openims/javascript/dnd/droptarget.js"></script>';
  //$dhtml .= '<script type="text/javascript" src="/openims/javascript/dnd/dragelement.js"></script>';
  //$dhtml .= '<script type="text/javascript" src="/openims/javascript/dnd/mouselistener.js"></script>';
  //$dhtml .= '<script type="text/javascript" src="/openims/javascript/dnd/geometry.js"></script>';
  //$dhtml .= '<script type="text/javascript" src="/openims/javascript/dnd/cursormanager.js"></script>';
  $dhtml .= DHTML_EmbedJavaScript ($code . '
    function handledropleft (target, source)
    {
      targetid = target.id;
      sourceid = source.id;
      extra = "&targetid=" + targetid + "&sourceid=" + sourceid + "&mousebutton=left";
      function_dragdrop (extra);
    }
    function handledropright (target, source)
    {
      targetid = target.id;
      sourceid = source.id;
      extra = "&targetid=" + targetid + "&sourceid=" + sourceid + "&mousebutton=right";
      function_dragdrop (extra);
    }
  ');
  $dhtml .= DHTML_EmbedJavaScript ('var dsl = new DragSource(null,handledropleft,null,null,null,["/openims/javascript/dnd/drop.gif","/openims/javascript/dnd/nodrop.gif"]);');
  $dhtml .= DHTML_EmbedJavaScript ('var dsr = new DragSource(null,handledropright,null,null,null,["/openims/javascript/dnd/drop.gif","/openims/javascript/dnd/nodrop.gif"], true, true);');
  $dhtml .= DHTML_EmbedJavaScript ('var dt = new DropTarget();');
  return $dhtml;
}

function DHTML_AddDragSource ($id, $html="")
{
  global $initdragdrop; if (!$initdragdrop) return "";
  global $myconfig; if ($myconfig[IMS_SuperGroupName()]["dragdrop"]!="yes") return "";  
  if (trim($html)) {
    $dhtml = DHTML_EmbedJavaScript ('dsl.addElementById("'.$id.'", \''.$html.'\', \''.$html.'\');');    
    $dhtml .= DHTML_EmbedJavaScript ('dsr.addElementById("'.$id.'", \''.$html.'\', \''.$html.'\');');    
  } else {
    $dhtml = DHTML_EmbedJavaScript ('dsl.addElementById("'.$id.'");');
    $dhtml .= DHTML_EmbedJavaScript ('dsr.addElementById("'.$id.'");');
  }
  return $dhtml;
}

function DHTML_AddDropTarget ($id)
{
  global $initdragdrop; if (!$initdragdrop) return "";
  global $myconfig; if ($myconfig[IMS_SuperGroupName()]["dragdrop"]!="yes") return "";  
  return DHTML_EmbedJavaScript ('dt.addElementById("'.$id.'");');
}

// Close the parent of the current window
function DHTML_CloseParent ()
{
  return "opener.window.close();";
}

// Create a proper mailto: hyperlink
function DHTML_EmailURL ($to, $subject, $body)
{
  global $myconfig;
  if ($myconfig["thunderbirdfix"]=="yes") {
    if (!$to) $to=ML("ontvanger","receiver"); // qqq
  }

  //ericd 311008 str_replace (" ","_", bij $body en $subject weggehaald,
  //zodat in de mail body en subject spaties niet vervangen worden door underscores (niet de url).
  $body = str_replace ("'", urlencode ("'"),
          str_replace ("\"", urlencode ("\""), 
          str_replace ("&", urlencode ("&"), 
          str_replace (chr(10), "%0A", 
          str_replace (chr(13), "%0D",  
          str_replace ("%",urlencode("%"),
          $body))))));
  $body = str_replace(urlencode("&")."#08364;", "_", $body); // Fix for Euro sign

  $subject = str_replace ("'", urlencode ("'"), 
             str_replace ("\"", urlencode ("\""), 
             str_replace ("&", urlencode ("&"), 
             str_replace ("%",urlencode("%"),
             $subject))));
  $subject = str_replace(urlencode("&")."#08364;", "_", $subject);
/*
  $body = str_replace ("'", urlencode ("'"), 
          str_replace ("\"", urlencode ("\""), 
          str_replace ("&", urlencode ("&"), 
          str_replace (chr(10), "%0A", 
          str_replace (chr(13), "%0D",  
          str_replace ("%",urlencode("%"),
          str_replace (" ","_",
          $body)))))));
  $subject = str_replace ("'", urlencode ("'"), 
             str_replace ("\"", urlencode ("\""), 
             str_replace ("&", urlencode ("&"), 
             str_replace ("%",urlencode("%"),
             str_replace (" ","_", 
             $subject)))));
*/

  return "mailto:$to%20?subject=$subject%20&body=$body%20"; 
}

// Immediately open a url
function DHTML_OpenNow ($url)
{
  return "window.open('$url');"; 
}

function DHTML_LoadEmail ($url)
{
  echo DHTML_EmbedJavaScript (DHTML_OpenNow ($url));
}

function DHTML_LoadTransURL($url) 
{
  uuse ("shield");
  SHIELD_FlushEncoded();
  if (strlen($url) > 1990) {
    $url = substr ($url, 0, 1990)."   ...";
  }
  echo "<iframe style=\"width:0px; height:0px; border: 0px\" target=\"_blank\" SRC=\"$url\"></iframe>";
  N_Flush ("BR"); // option BR needed because firefox won't flush "empty" pages
  N_Sleep (1000);
}

function DHTML_InvisiTable ($init, $exit, $e1, $e2)
{
  $tableparams = TABLE_Mode ($mode, array());
  $ret = "<table cellspacing=0 cellpadding=0><tr><td>$init$e1$exit</td><td>$init$e2$exit</td>";
  
  $numargs = func_num_args();
  if ($numargs > 4) {
      for ($i = 4; $i<$numargs; $i++)
      {
        if (func_get_arg($i)) $ret .="<td>$init".func_get_arg($i)."$exit</td>";
      }
  }
  return $ret."</tr></table>";
}

function DHTML_ID()
{
  global $dhtml_id;
  return ++$dhtml_id;
}

function DHTML_SetValue ($field, $value)
{
  $value = DHTML_EncodeJsString($value); 
  return "document.forms[0].elements['{$field}'].value = '".$value."';";
}

function DHTML_Parent_SetValue ($field, $value)
{
  $value = DHTML_EncodeJsString($value); 
  return "for( i=0;i!=opener.document.forms.length;i++ )
          {
            if (opener.document.forms[i].elements['{$field}']) {
             opener.document.forms[i].elements['{$field}'].value = '".$value."';
             break;
            }
          }
         ";
}

// Set the value (content) of a with DHTML_DynamicObject created object 
function DHTML_Parent_Parent_SetValue ($field, $value) 
{ 
  $value = DHTML_EncodeJsString($value); 
  return "if (opener.opener.document.forms[0].elements['{$field}']) { 
              opener.opener.document.forms[0].elements['{$field}'].value = '".$value."'; 
          } else { 
              opener.opener.document.forms[1].elements['{$field}'].value = '".$value."'; 
          } 
          "; 
}

function DHTML_EncodeJsString($input, $all = true, $onlyunicode = false) {
  // Encode a string using \x escapes i.e. \x77\x34\x41 etc.
  // Numeric HTML-entities (which OpenIMS uses for non-iso characters) are encoded using \u, i.e. \u0F34.
  // By default, all characters will be encoded; with $all = false, alphanumeric stuff will not be encoded.
  // By default, ISO characters are encoded using \xNN; with $onlyunicode, everything is encoded using \uNNNN (required for JSON).
  $tmp = $input;
  for ($i=0; $i<strlen($tmp); $i++) { 
    $char = substr($tmp, $i, 1);
    if ($char == "&" && preg_match('/^&#0*([0-9]*);/', substr($tmp, $i), $matches)) {
      if ($matches[1] > 0xffff) {
        // On Windows (Firefox + IE), nothing works to get correct output.
        // Even though it works fine in HTML: " & # 1050624 ; " -> results in a *single* unknows-character-glyph
        // This doesnt work: \uNNNNNN or \uNNNNNNNN -> some of those N's end up as clear text
        // This doesnt work either: \uNNNN\uNNNN -> results in two (known or unknown) glyphs instead of one
        $value .= "?";
      } else {
        $value .= "\u".sprintf("%04X",$matches[1]);
      }
      $i = $i + strlen($matches[0]) - 1;
    } else {
      if (!$all && ((ord($char) >= ord('A') && ord($char) <= ord('Z')) || 
                    (ord($char) >= ord('a') && ord($char) <= ord('z')) || 
                    (ord($char) >= ord('0') && ord($char) <= ord('9')))) {
        $value .= $char;
      } elseif ($onlyunicode) {
        $value .= "\u".sprintf("%04X",ord (substr ($tmp, $i, 1)));
      } else {
        $value .= "\x".sprintf("%02X",ord (substr ($tmp, $i, 1))); 
      }
    }
  } 
  return $value;
}

function DHTML_EncodeCssString($input, $all = false) {
  // Encode a CSS string using \x escaps i.e. \x77\x34\x41 etc.
  // By default, will only encode dangerous stuff, but not alphanumeric stuff.
  // With $all = true, all characters will be encoded.
  $tmp = N_UTF2HTML(N_HTML2UTF($input), true); // convert to true ascii html (all 8 bit characters become numeric entities)
  for ($i=0; $i<strlen($tmp); $i++) { 
    $char = substr($tmp, $i, 1);
    if ($char == "&" && preg_match('/^&#0*([0-9]*);/', substr($tmp, $i), $matches)) { // find numeric entities
      if ($matches[1] > 0xffffff) {
        $value .= "?";
      } else {
        $value .= "\\".sprintf("%06X",$matches[1]);
      }
      $i = $i + strlen($matches[0]) - 1;
    } else {
      if (ord($char) > 127) {
        // convert to unicode
      } elseif (ord($char) == 0) {
        // skip this char
      } elseif (!$all && (
                        (ord($char) >= ord('A') && ord($char) <= ord('Z')) || 
                        (ord($char) >= ord('a') && ord($char) <= ord('z')) || 
                        (ord($char) >= ord('0') && ord($char) <= ord('9')))) {
        $value .= $char;
      } else {
        $value .= "\\".sprintf("%06X",ord (substr ($tmp, $i, 1)));
      }
    }
  } 
  return $value;
}

function DHTML_DynamicObject ($content, $id="", $inline=false)
{  
  $id = $id ? $id : N_GUID();
  if ($inline) {
    return "<div id='div".$id."' style='display: inline;'>$content</div>";
  } else {
    return "<div id='div".$id."'>$content</div>";
  }
}

function DHTML_RPCURL ($code, $input="", $p_frame='IMS_RPCFrame') {
  //ericd 0809: uitbreiding met 2 (globale) js parameters
  // One needs to jump through a lot of hoops to get it working properly with IE
  $encspec = SHIELD_Encode (array("code"=>$code,"input"=>$input));
  $theurl = "/nkit/form.php?command=execute&encspec=$encspec";
  global $debugforms;
  $id = N_GUID();
  $url = "javascript:function function_1$id(){";
  $url .= "  if (typeof (jspar1) == 'undefined') jspar1 = '';";
  $url .= "  if (typeof (jspar2) == 'undefined') jspar2 = '';";
  $url .= "  var src='$theurl&jspar1='+jspar1+'&jspar2='+jspar2; document.getElementById('$p_frame').src=src;";
  $url .= " }";
  $url .= " function function_2$id(){";
  $url .= "   setTimeout('function_1$id()', 10);";
  $url .= "}  function_2$id();";
  return $url;
}

function DHTML_RPCFormAction($code, $input="", $p_frame='IMS_RPCFrame')
{
  // Use the output as your form action. When the form is submitted,
  // your $code will be executed. Your $code can use $input and $data,
  // where $data is everything from the form.
  // Note that a GET request is used, so form data should not exceed 2000 characters.
 
  $code = '
    foreach ($_GET as $paramname => $paramvalue) {
      if ($paramname != "command" && $paramname != "encspec") $data[$paramname] = $paramvalue;
    }
  ' . $code;
  $encspec = SHIELD_Encode (array("code"=>$code,"input"=>$input));
  $theurl = "/nkit/form.php?command=execute&encspec=$encspec";
  global $debugforms;
  $id = N_GUID();
  $urlscript  = "javascript:function function_1$id(){";
  $urlscript .= "var url = '$theurl';";
  // Go through all the forms in the document, stop when we find ourselves (we look for our unique function $id somewhere in the action-attribute)
  $urlscript .= "var j; var form; for (j = 0; j < 5; j++) { form = document.forms[j]; if (form.action.indexOf('". $id ."') != -1) break; }";
  // Go through all the form elements and build a GET-request from them.
  $urlscript .= "var el; var value; for (var i=0;i<form.length;i++) { ";
  $urlscript .=   "el = form.elements[i]; ";
  $urlscript .=   "if (el.type == 'checkbox' || el.type == 'radio') value = (el.checked ? el.value : ''); else value = el.value;";
  $urlscript .=   "if (value) url += '&' + escape(el.name) + '=' + escape(value);"; 
  $urlscript .= "}";
  $urlscript .= "document.getElementById('$p_frame').src=url;";
  $urlscript .= "}";
  $urlscript .= "function function_2$id(){";
  $urlscript .= "setTimeout('function_1$id()', 10);";
  $urlscript .= "}function_2$id();";
  return $urlscript;
}

function DHTML_PrepRPC ($p_frame = 'IMS_RPCFrame')
{
  global $init_DHTML_PrepRPC;
  if (!$init_DHTML_PrepRPC) {
    $init_DHTML_PrepRPC = true;
    global $ims_showforms; // cookie
    if ($ims_showforms=="yes") {
      $size = "200px";
    } else {
      $size = "0px";
    }
    return "<iframe name=\"$p_frame\" id=\"$p_frame\" style=\"width:$size; height:$size; border: 0px;\"></iframe>";
  }
}

function DHTML_SetDynamicObject ($id, $newcontent="", $append=false)
{
  if ($newcontent) {     
    $tmp = $newcontent;
    global $myconfig;
    $sgn = IMS_SuperGroupName();
    if (($myconfig[$sgn]["ml"]["metadatalevel"] && $myconfig[$sgn]["ml"]["metadatafiltering"] != "no") || 
         $myconfig[$sgn]["ml"]["metadatafiltering"] == "yes") {
      $tmp = FORMS_ML_Filter($tmp); // because filtering becomes impossible after the hex encoding
    }

    $newcontent = DHTML_EncodeJsString($tmp); 
    if ($append) {
      $ret = "document.getElementById('div$id').innerHTML = document.getElementById('div$id').innerHTML + '$newcontent';";
    } else {
      $ret = "document.getElementById('div$id').innerHTML = '$newcontent';";
    }
  } else {
    $ret = "document.getElementById('div$id').innerHTML = $id;";
  }
  return $ret;
}

function DHTML_Master_SetDynamicObject ($id, $newcontent) 
{
  $tmp = $newcontent;
  $newcontent = "";
  global $myconfig;
  $sgn = IMS_SuperGroupName();
  if (($myconfig[$sgn]["ml"]["metadatalevel"] && $myconfig[$sgn]["ml"]["metadatafiltering"] != "no") || 
       $myconfig[$sgn]["ml"]["metadatafiltering"] == "yes") {
    $tmp = FORMS_ML_Filter($tmp); // because filtering becomes impossible after the hex encoding
  }

  $newcontent = DHTML_EncodeJsString($tmp); 
  return "window.parent.document.getElementById('div$id').innerHTML = '$newcontent';";
}


function DHTML_Parent_SetDynamicObject ($id, $newcontent) 
{
  $tmp = $newcontent;
  $newcontent = "";
  global $myconfig;
  $sgn = IMS_SuperGroupName();
  if (($myconfig[$sgn]["ml"]["metadatalevel"] && $myconfig[$sgn]["ml"]["metadatafiltering"] != "no") || 
       $myconfig[$sgn]["ml"]["metadatafiltering"] == "yes") {
    $tmp = FORMS_ML_Filter($tmp); // because filtering becomes impossible after the hex encoding
  }
  $newcontent = DHTML_EncodeJsString($tmp); 
  return "opener.document.getElementById('div$id').innerHTML = '$newcontent';";
}

function DHTML_Parent_Parent_SetDynamicObject ($id, $newcontent)
{
  $tmp = $newcontent;
  $newcontent = "";
  global $myconfig;
  $sgn = IMS_SuperGroupName();
  if (($myconfig[$sgn]["ml"]["metadatalevel"] && $myconfig[$sgn]["ml"]["metadatafiltering"] != "no") || 
       $myconfig[$sgn]["ml"]["metadatafiltering"] == "yes") {
    $tmp = FORMS_ML_Filter($tmp); // because filtering becomes impossible after the hex encoding
  }
  $newcontent = DHTML_EncodeJsString($tmp); 
  return "opener.opener.document.getElementById('div$id').innerHTML = '$newcontent';";
}

function DHTML_Once ($javascript)
{
  global $DHTML_Once;
  if (!$DHTML_Once[$javascript]) {
    $DHTML_Once[$javascript] = true;
    return $javascript;
  }
}

function DHTML_EmbedJavaScript ($javascript)
{
  global $init_DHTML_EmbedJavaScript, $ims_showerrors;
  if (!$init_DHTML_EmbedJavaScript) {
    // LF20110530: please show me my mistakes !
    if ($ims_showerrors != "yes") $javascript = $javascript."function DHTML_EmbedJavaScript_handleError() {return true;} window.onerror = DHTML_EmbedJavaScript_handleError;";
    for ($i=0; $i<100; $i++) $javascript .= " ";
    $init_DHTML_EmbedJavaScript = true;
  }
  return "<script type=\"text/javascript\"><!--
$javascript
//--></script>";
}

function DHTML_Alert ($message)
{
  return "alert('$message');";
}

function DHTML_IntelliImage_SetState ($id, $state)
{
  global $dhtml_intelliimage;  
  $specs = $dhtml_intelliimage[$id];  
  $r = $specs["id"];
  $img_on = $specs["img_on"] ? $specs["img_on"] : "/ufc/rapid/openims/toggle_on.jpg";
  $img_off = $specs["img_off"] ? $specs["img_off"] : "/ufc/rapid/openims/toggle_off.jpg";
  $alt_on = $specs["alt_on"];
  $alt_off = $specs["alt_off"];
  $specs["img" ]= "/openims/blank.gif";
  $enc = SHIELD_Encode ($specs);
  $code_img_on = "/nkit/img.php?state=1&specs=".$enc;
  $code_img_off = "/nkit/img.php?state=0&specs=".$enc;
  if ($state) {
    $dhtml = "
      state$id = 1;
      getRef ('iiv$r').src = '$img_on';
      setAlt ('iiv$r', '$alt_on');
      setImg ('iic$r', '$code_img_on' + '&random=' + (Math.round((Math.random()*999999)+1)));
      setAlt ('iic$r', '$alt_on');
      ".$specs["js_on_code"]."
    ";
  } else {
    $dhtml = "
      state$id = 0;
      getRef ('iiv$r').src = '$img_off';
      setAlt ('iiv$r', '$alt_off');
      setImg ('iic$r', '$code_img_off' + '&random=' + (Math.round((Math.random()*999999)+1)));
      setAlt ('iic$r', '$alt_off');
      ".$specs["js_off_code"]."
    ";
  }  
  return $dhtml;
}

function DHTML_IntelliImage ($specs, $id="")
{
  if (!$specs) $specs=array();
  if ($id=="dynamic") {
    $r = N_GUID();
  } else {
    $r = $id ? $id : DHTML_ID();
  }
  global $dhtml_intelliimage;  
  $img_on = $specs["img_on"] ? $specs["img_on"] : "/ufc/rapid/openims/toggle_on.jpg";
  $img_off = $specs["img_off"] ? $specs["img_off"] : "/ufc/rapid/openims/toggle_off.jpg";
  $alt_on = $specs["alt_on"];
  $alt_off = $specs["alt_off"];
  $width = $specs["width"] ? $specs["width"] : 13;
  $height = $specs["height"] ? $specs["height"] : 13;
  $state = $specs["state"];
  $specs["id"] = $r;
  $dhtml_intelliimage[$r] = $specs;
  if ($state) {
    $img = $img_on;
    $alt = $alt_on;
    $state = 1;
  } else {
    $img = $img_off;
    $alt = $alt_off;
    $state = 0;
  }
  $specs["img" ]= "/openims/blank.gif";
  $enc = SHIELD_Encode ($specs);
  $code_img_on = "/nkit/img.php?state=1&specs=".$enc;
  $code_img_off = "/nkit/img.php?state=0&specs=".$enc;
  $thecode = "
      function setImg$r(obj, source){
          if(!document.getElementById)
              return;
          getRef$r(obj).src = source;
      }
      function setAlt$r(obj, source){
          if(!document.getElementById)
              return;
          getRef$r(obj).alt = source;
      }
      
      function getRef$r(obj){
          return(typeof obj == \"string\") ? document.getElementById(obj) : obj;
      }"."
      if (!window.state$r) window.state$r = $state;
      function swap$r(){
        if (window.state$r == 1) {
          window.state$r = 0;
          getRef$r ('iiv$r').src = '$img_off';
          setAlt$r ('iiv$r', '$alt_off');
          setImg$r ('iic$r', '$code_img_off' + '&random=' + (Math.round((Math.random()*999999)+1)));
          setAlt$r ('iic$r', '$alt_off');
          ".$specs["js_off_code"]."
        } else {
          window.state$r = 1;
          getRef$r ('iiv$r').src = '$img_on';
          setAlt$r ('iiv$r', '$alt_on');
          setImg$r ('iic$r', '$code_img_on' + '&random=' + (Math.round((Math.random()*999999)+1)));
          setAlt$r ('iic$r', '$alt_on');
          ".$specs["js_on_code"]."
        }
      }
  ";

  $swapper = $thecode;
  $swapper = str_replace ("\n", " ", $swapper);
  $swapper = str_replace ("\r", " ", $swapper);
  $swapper = json_encode ($swapper);
  $swapper = substr (substr ($swapper, 1), 0, -1);
  $swapper = str_replace ('\'', '\\\'', $swapper);
  $swapper = str_replace ('\\"', '\'+String.fromCharCode(34)+\'', $swapper);
  $swapper = "eval ('$swapper');";

  if ($id=="dynamic") {
    $dhtml .= "<img id=iiv$r border=0 src=\"$img\" alt=\"$alt\" width=\"$width\" height=\"$height\" onclick=\"$swapper swap$r()\" />";
    $dhtml .= "<img id=iic$r border=0 src=\"/openims/blank.gif\" alt=\"$alt\" width=\"1\" height=\"1\" onclick=\"$swapper swap$r()\" />";
  } else {
    $dhtml = DHTML_EmbedJavaScript (DHTML_Once ($thecode));
    $dhtml .= "<img id=iiv$r border=0 src=\"$img\" alt=\"$alt\" width=\"$width\" height=\"$height\" onclick=\"swap$r()\" />";
    $dhtml .= "<img id=iic$r border=0 src=\"/openims/blank.gif\" alt=\"$alt\" width=\"1\" height=\"1\" onclick=\"swap$r()\" />";
  }
  return $dhtml;
}

function DHTML_PopupURL ($theurl, $scollbars=1, $noshield=false, $statusbar=true, $dragdrop=false, $extraurlscript="", $resize=false)
{
  /****************************************************************************
   *                               WARNING 
   * Format should match with the RegExp in N_Redirect1_Body and N_Redirect2
   * Use extreme caution when changing things.
   ****************************************************************************/
  if ($statusbar) $statusbar = "1"; else $statusbar="0";
  global $debugforms;
  if (!$noshield) {
    $theurl = N_AlterURL($theurl, "shielddummy", SHIELD_Encode (""));
  }
  $theurl = str_replace ("%", "%25", $theurl);
  if ($dragdrop) {
    $id = "dragdrop";
    $url = "javascript:function function_$id(extra){";
    $url .= "var URL = '$theurl'+extra;";
  } elseif ($extraurlscript) {
    $id = N_GUID();
    $url = "javascript:function function_$id(el){";
    $url .= "var URL = '$theurl';";
    $url .= $extraurlscript; // For example: $extraurlscript = "URL += '&blub=bla';"
  } else {
    $id = N_GUID();
    $url = "javascript:function function_$id(){";
    $url .= "var URL = '$theurl';";
  }
  $url .= "var cmd = 'page' + '$id' + ' = window.open(URL, ' + String.fromCharCode(39)";
  $url .= "+ '$id' + String.fromCharCode(39) + ', ' + String.fromCharCode(39)";
  if ($debugforms) {
    $url .= "+ 'toolbar=0,scrollbars=$scollbars,location=0,status=$statusbar,menubar=0,resizable=0,width=1000,height=800,left=10,top=10' + String.fromCharCode(39)";
  } else  {
    if(N_Safari()) {   
      // Safari has some problems resizing tables with text.   
      $url .= "+ 'toolbar=0,scrollbars=$scollbars,location=0,status=$statusbar,menubar=0,resizable=1,width=300,height=1,left=10000,top=10000' + String.fromCharCode(39)";   
    } else {   
      if ($resize)
        $url .= "+ 'toolbar=0,scrollbars=$scollbars,location=0,status=$statusbar,menubar=0,resizable=1,width=1,height=1,left=10000,top=10000' + String.fromCharCode(39)";
      else
        $url .= "+ 'toolbar=0,scrollbars=$scollbars,location=0,status=$statusbar,menubar=0,resizable=0,width=1,height=1,left=10000,top=10000' + String.fromCharCode(39)";
    }
  }
  $url .= "+ ');';";
  if ($dragdrop) {
    $url .= "eval(cmd);page$id.blur();window.focus();}";
  } elseif ($extraurlscript) {
    $url .= "eval(cmd);page$id.blur();window.focus();}function_$id(this);";
  } else {
    $url .= "eval(cmd);page$id.blur();window.focus();}function_$id();";
  }
  return $url;
}

function DHTML_PerfectSize ($level=0)
{
  while ($level--) $prefix.="opener.";
  $dhtml =  "  var table = ".$prefix."document.getElementById('MeasureMe');";
  $dhtml .= "  ".$prefix."window.moveTo(2000, 2000);";
  $dhtml .= "  ".$prefix."window.resizeTo(1000, 1000);";

  $dhtml .= "  height = table.clientHeight+90;";
  $dhtml .= "  width = table.clientWidth+50;";

  $dhtml .= "  if (height>(screen.height-50)) {height=(screen.height-50)};";
  $dhtml .= "  if (width>(screen.width-30)) {width=(screen.width-30)};";
  $dhtml .= "  ".$prefix."window.resizeTo(width, height);";
  $dhtml .= "  ".$prefix."window.moveTo((screen.width-width)/4 + 10,(screen.height-height)/4);";

  $dhtml .= "  heightDelta = height - document.body.clientHeight;";
  $dhtml .= "  ".$prefix."window.resizeTo(width , height + heightDelta-60); window.focus();";
  return $dhtml;
}

function DHTML_Parent_PerfectSize ()
{
//  return DHTML_PerfectSize (1);
}

function DHTML_Parent_Parent_PerfectSize ()
{
//  return DHTML_PerfectSize (2);
}

function DHTML_PdfclickJs()
{
  $js = '
    <script type="text/javascript">
      function showPdfPreview(divid) { $("#"+divid).show(); }
      function hidePdfPreview() { $(".pdfpreview").hide(); }
      $(window).scroll(function () { $(".pdfpreview").css("top",($(this).scrollTop()+20)); });
    </script>
  <STYLE type="text/css">
    .pdfpreview {
      position: absolute;
      right: 10px;
      padding: 25px;
      display: none;
      text-align: right;
      background-color: white;
      -moz-box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);      
      border: 1px solid #999999;
      float:right;
      top:20px;
    }    
  </STYLE>
  ';
  return $js;  
}

function DHTML_PdfclickPreview($sgn, $objectid)
{  
  $img = '<div class="pdfpreview" id="pdfpreview_'.$objectid.'">
    <div id="waitmessage_'.$objectid.'">'.ML("Afbeelding wordt gemaakt, dit kan enige tijd in beslag nemen.","Please wait").'</div>
    <img src="/ufc/thumb/'.$sgn."/".$objectid.'/600/600/" style="display:none" onload="$(\'#waitmessage_'.$objectid.'\').hide();$(this).show();">
  </div>';
  //img = '<div class="pdfpreview" id="pdfpreview_'.$objectid.'"><img src="/ufc/rapid/openims/logo_osict.gif"></div>';
  return $img;
}




?>