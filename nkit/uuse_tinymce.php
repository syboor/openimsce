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
  To get a working spell checker:   
  - make sure "aspell" is installed on the server (http://aspell.net)   
  - make sure the language libraries of aspell are installed   
  - make sure /libs/tinymce/jscripts/tiny_mce/plugins/spellchecker/config.php contains proper settings   
    
  TODO: make aspell part of openims.zip   
  
  SETTINGS:   
    $myconfig[<<<supergroupname>>>]["tinymcedmsroot"] id of root folder for DMS plugin   
    $myconfig["tinymcespellcheck"] yes is available   
  
*/

  uuse ("dhtml");
  uuse ("shield");

  // function is used twice, for inline html editing, using a popup, or   
  // for inplace editing, in which case it's in the content. 
  // the encodedsettings are needed for the popup. 
  
  
  
  function TINYMCE_handle_tinymce($encodedsettings)   
  {   
    $settings = SHIELD_Decode ($encodedsettings);   
  
    //css   
    $file_path = $settings["htmlpath"];   
    $file_path_id = explode ("/", $file_path);   
    if (sizeof($file_path_id) == 6)
    {   
      $file_id = $file_path_id[4]; // Windows Server path   
    }   
    else 
    {   
      $file_id = $file_path_id[4].$file_path_id[6]; // Linux Server path (32k fix)   
    }   

    global $myconfig;   
    $sgn = IMS_SupergroupName();   
    $context = IMS_SiteInfo();
	
    $inplace = TINYMCE_isinplace($file_id, $settings["htmlpath"]); 

    if($myconfig[$sgn]["tinymceversion"] == "jquery" && !$inplace)
      $pageTitle = "OpenIMS TinyMCE JQ";
    else
      $pageTitle = "OpenIMS TinyMCE JS";
      
    //ericd 250811 needed for TinyMCE JQuery version
    if($myconfig[$sgn]["tinymceversion"] == "jquery" && !$inplace) {
      IMS_CaptureHtmlHeaders();
    }
      
    ML_SetLanguage ($settings["language"]);   
  
    if (!file_exists (N_CleanPath ("html::/tmp/phpthumbcache"))) {   
      N_WriteFile ("html::/tmp/phpthumbcache/dummy.txt", "dummy");   
      N_DeleteFile ("html::/tmp/phpthumbcache/dummy.txt");   
    }   
  
    $_SERVER['SERVER_NAME'] = $_SERVER["HTTP_HOST"];   
  
    $echostr = "";   
    
    if (!$inplace)   
      $echostr .=   '<html><head><title>'.$pageTitle.'</title></head><body   topmargin="0" leftmargin="0" bgcolor="#ffffff">';   
    
    $echostr .= '<script type="text/javascript">   
    var image_dir ="'. $settings["imagepath"].'";   
    var taal = "'. $settings["language"].'";   
    var formlink = "libs/tinymce/jscripts/tiny_mce/plugins/form/bin/fields.php";   
    </script>';
    
    //ericd 250811 needed for TinyMCE JQuery version, ui = tinymcejqueryk, uuse_dhtml was changed also 
    if($myconfig[$sgn]["tinymceversion"] == "jquery" && !$inplace) {
      DHTML_RequireJQuery("tinymcejquery");
    }
  
    $width = ($inplace ? (string)$myconfig[$sgn]["inplaceeditorwidth"] : "980"); 
    if ($width == "")
      $width = "700";  
    if  ($_GET["submode"] != "treeview") // #treeview If treeview cms is shown, no <table> tags needed
    {  $echostr .= '<table bgcolor="#ffffff" width=' . $width . ' height=580 cellspacing=0 cellpadding=0 border=0 id="MeasureMe"><tr><td>';   }
  
    //spellcheck   
    if ($myconfig["tinymcespellcheck"] == "yes") {   
      $spellcheckbutton = "|,spellchecker,";       
    }     
  
    //ericd 101109 als php 5, dan PHP Image Editor met button in TinyMCE
    if (version_compare(PHP_VERSION, '5.0.0', '>=')) {
	$pluginsPie = ',pie';
	$theme_advanced_buttons2Pie = ',|,pie';		
    }
    else {
	$pluginsPie = '';
	$theme_advanced_buttons2Pie = '';		
    }
    
    $table = "ims_".IMS_SuperGroupname()."_objects";   
    $page_object = MB_Load ($table, $file_id);   
    $template_object = MB_Load ("ims_".IMS_SuperGroupName()."_templates", $page_object["template"]);   
    $css_file = $template_object["cssurl"];   
  
    if ($_GET['mode']=="cms" && $_GET['submode'] == "treeview")
    {
	$context['object'] = $_GET['showpage'];
	$self = ($inplace ? "/" . $context["site"] . "/" . $context["object"] . ".php" : "/openims/handle_tinymce.php");
    } else
      $self = ($inplace ? "/" . $context["site"] . "/" . $context["object"] . ".php" : "/openims/handle_tinymce.php");   

    if($_GET['mode'] == "cms" && $_GET['submode'] == "treeview")
    { $echostr .= '<form action="http://'.$_SERVER["SERVER_NAME"].'/openims/treeview/ajax/handle_save.php?action=savetinymce" method="post" id="tinymce_form" onsubmit="return false;">'; }
    else
      $echostr .= '<form action=' . $self . ' method="post">';
    $echostr .= '<input type=hidden name=encodedsettings value="'.$encodedsettings.'">';   
    $echostr .= '<input type=hidden name=check123 value="123">';   

    if ($_GET['submode'] != 'treeview')
      {
        //ericd 250811 needed for TinyMCE JQuery version
        if($myconfig[$sgn]["tinymceversion"] == "jquery" && !$inplace) {
          $echostr .= '<script language="javascript" type="text/javascript" src="/openims/libs/tinymcejquery/jscripts/tiny_mce/tiny_mce.js"></script>';
        }
        else {
          $echostr .= '<script language="javascript" type="text/javascript" src="/openims/libs/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>';
        }     
      }
      

    $echostr .= '<script language="javascript" type="text/javascript">   
      tinyMCE.init({   
      entity_encoding : "raw",   
      language : "'.ML("nl","en").'",   
      '.($_GET['submode'] == "treeview" ? 'save_callback: "treeview_tinymce_through_ajax",' : '').'
      mode : "exact",   
      elements : "content",   
      theme : "advanced",   
      entity_encoding : "numeric",


      plugins : "'.($settings["fullpage"] ? "fullpage," : "").'spellchecker,table,save,advimage,advlink,preview,media,searchreplace,contextmenu,paste,fullscreen,noneditable,visualchars,nonbreaking,pagebreak,ibrowser,dfn,afkort,print,form,dms,manual,'.$pluginsPie.'",   
    
      theme_advanced_toolbar_location : "top",   
      theme_advanced_toolbar_align : "left",   
      theme_advanced_statusbar_location : "bottom",   
      apply_source_formatting : true,   
      paste_text_sticky : true, /* JG - GGDZW-8 */
      paste_use_dialog : false,
      content_css : "'.$css_file.'",   
      convert_urls : false,   
  
      ' . ($settings["readonly"] ? '
      theme_advanced_buttons1 : "print,preview,|,search,replace,'.$spellcheckbutton.'|,cut,copy,paste,pastetext,pasteword,cleanup,|,undo,redo,|,link,unlink,anchor,|,tablecontrols,|,manual",   
      ' : '
      theme_advanced_buttons1 : "save,newdocument,|,print,preview,|,search,replace,'.$spellcheckbutton.'|,cut,copy,paste,pastetext,pasteword,cleanup,|,undo,redo,|,link,unlink,anchor,|,tablecontrols,|,manual",   
      ') . '
      theme_advanced_buttons2 : "formatselect,styleselect,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,outdent,indent,hr,removeformat,visualaid,|,charmap,|,media,ibrowser,image,dms,|,afkort,dfn,|,code'.($settings["fullpage"] ? ",fullpage" : "").$theme_advanced_buttons2Pie;   
  
      if ($page_object["editor"] == "Form")
        $echostr .= ',|,form",';
      else
        $echostr .= '",';   
      
      $echostr .= '   
        theme_advanced_buttons3 : "",   
        width: ' . $width . ',   
        height: 580,   
        spellchecker_languages : "'.ML("+Nederlands=nl,English=en","+English=en,Nederlands=nl").'"   
    });   
        </script>';   
  
    if ($_POST["check123"])
    {   
      $html = stripcslashes ($_POST["content"]);
      if (!$settings["fullpage"]) {
        $html = TINYMCE_UnprepHTML ($html, $settings["htmlpath"]);
      } 
      N_WriteFile ("html::".$settings["htmlpath"].$settings["htmlfile"], $html); // undo GPC magic   
      IMS_Signal ( N_32kFix_UnTransform ($settings["htmlpath"].$settings["htmlfile"]), SHIELD_CurrentUser(), getenv("HTTP_HOST"));   
      if (!$inplace)
        N_Redirect ("closeme&refreshparent");   
    }   
  
    if ($settings["fullpage"]) {
      $echostr .= '<textarea id="content" name="content">'.
          htmlentities (N_ReadFile ("html::".$settings["htmlpath"].$settings["htmlfile"])).   
        '</textarea>';   

    } else {
      $echostr .= '<textarea id="content" name="content">'.
          htmlentities (TINYMCE_PrepHTML (N_ReadFile ("html::".$settings["htmlpath"].$settings["htmlfile"]), $settings["htmlpath"])).   
        '</textarea>';   
    }
  
    $echostr .= "</form>";   
    
    if ( $_GET["submode"] != "treeview" ) // #TREEVIEW: If treeview cms is shown, no <table> tags needed
    { $echostr .= "</td></tr></table>"; }
  
    if (!$inplace)   
    {   
      $echostr .= "</body></html>";   
      if ( $_GET["submode"] != "treeview" ) 
      {  $echostr .= DHTML_EmbedJavascript (DHTML_PerfectSize ()); }
      
      //ericd 250811 needed for TinyMCE JQuery version
      if($myconfig[$sgn]["tinymceversion"] == "jquery" && !$inplace) {
        $echostr = IMS_MergeHtmlHeaders($echostr);
      }
      echo $echostr;   
      N_Exit();   
    }   
    else   
      return $echostr;   
  } 
  
     

  function TINYMCE_DetermineSettings($htmlpath, $htmlfile, $imagepath, $settings)
  {
    $htmlpath = $settings["htmlpath"] = N_VeryRawInternalPath (N_CleanPath ("html::".$htmlpath));
    if (!$htmlfile) $htmlfile="page.html";
    $settings["htmlfile"] = $htmlfile;    
    if (!$imagepath) {
      if (is_dir (N_CleanPath ("html::".$htmlpath."page_files/"))) {
        $imagepath = $htmlpath."page_files/";
      } else if (is_dir (N_CleanPath ("html::".$htmlpath."page_bestanden/"))) {
        $imagepath = $htmlpath."page_bestanden/";
      } else {
        $imagepath = $htmlpath;
      }
    }
    $settings["imagepath"] = N_VeryRawInternalPath (N_CleanPath ("html::".$imagepath));
    $settings["language"] = ML_GetLanguage();
    if (substr($htmlfile, -13) == "template.html") $settings["fullpage"] = true;

    $loc_encodedsettings = SHIELD_Encode ($settings);
    SHIELD_FlushEncoded();

    return $loc_encodedsettings;
  }

  function TINYMCE_EditURL ($htmlpath, $htmlfile="", $imagepath="", $settings=array())
  {
    $encodedsettings = TINYMCE_DetermineSettings($htmlpath, $htmlfile, $imagepath, $settings);    
    return DHTML_PopupURL ("/openims/handle_tinymce.php?encodedsettings=$encodedsettings", 0);
  }

  function TINYMCE_ViewURL ($htmlpath, $htmlfile="", $imagepath="", $settings=array())
  {
    $settings["readonly"] = "yes";
    $encodedsettings = TINYMCE_DetermineSettings($htmlpath, $htmlfile, $imagepath, $settings);    
    return DHTML_PopupURL ("/openims/handle_tinymce.php?encodedsettings=$encodedsettings", 0);
  }
  
  // take HTML from editor and convert to what will be on disk
  function TINYMCE_UnprepHTML ($html, $contentlocation) {
    $html = preg_replace ('|/ufc/rapid2/[^/]*/|i', '/', $html);
    $html = str_replace ("/ufc/rapid", "", $html); 
    $search = array (
      "'http://[^/]*$contentlocation'si",
      "'$contentlocation'si",
      "'[.][.]page_files'si",
      '@<acronym@i',
      '@</acronym>@i',
      '@^<p>&nbsp;</p>@',
      '@^<@',
      '@>$@',
    );
    $replace = array (
      "",
      "",
      "page_files",

      "<abbr",			// place the original <abbr> tags back
      "</abbr>",		// place the original <abbr> tags back
      "",			// removes empty line at beginning (bug)
      "<html><",		// adds <html> tags
      "></html>"		// adds <html> tags
    );
    $siteinfo = IMS_DetermineSite ();
    $imgloc1 = $siteinfo["sitecollection"]."/ibrowser/".$siteinfo["site"]."/";
    $imgloc2 = "//".$siteinfo["sitecollection"]."/ibrowser/".$siteinfo["site"]."/";
    $imgloc = "/".$siteinfo["sitecollection"]."/ibrowser/".$siteinfo["site"]."/";
    $html = str_replace (".".$imgloc, $imgloc, $html);
    $html = str_replace (".".$imgloc, $imgloc, $html);
    $html = str_replace ($imgloc1, $imgloc, $html);
    $html = str_replace ($imgloc2, $imgloc, $html);
    $html = preg_replace ($search, $replace, $html);
    return $html;
  }

  // prepare HTML for editing
  function TINYMCE_PrepHTML ($html, $contentlocation) 
  {
    $search = array (
      "'<!--\[if gte vml 1\]>.*?-->'si",
      "'<!\[if !vml\]>'",
      "'<!\[endif\]>'",
      "'v:shapes=\"[^\"]*\"'si",
      "'<style>[^>]*VML[^>]*</style>'si",
      '@<script[^>]*?>.*?</script>@si',
      '@<style[^>]*?>.*?</style>@si',
      '@<![\\s\\S]*?--[ \\t\\n\\r]*>@',					   // ------------------------------ ^ orginal edit
      '@<div class=Section[^>]*>@',					   // MS <div Section X> remove
      '@<div>@',							   // MS remove unused <div>'s	-->	e.g. <div><div><div>
      '@(style=\')([^\']*mso-[^\']*)([\'])@siU',			   // KM/03/04/2009 regel die alle style tags met mso- er in weg haalt.
      //'@(.*)(<td.*)([ \n]style.*["\'].*["\'])(.*>)(.*</td>)(.*)@siU',	   // MS <td> tags cleanup KM/03/04/2009 commentaar gemaakt omdat deze expressie niet speciefiek genoeg is. De regel hier boven lost dit probleem op.
      //'@(.*)(<table.*)([ \n]style.*["\'].*["\'])(.*>)(.*</td>)(.*)@siU',   // MS <table> tags cleanup KM/03/04/2009 commentaar gemaakt omdat deze expressie niet speciefiek genoeg is.
      '@<span style=\'mso[^>]*[[:space:]]*[^>]*\'>@',			   // MS span tags remove	--> <span style='mso-bidi-font-weight:normal'>
      '@[[:space:]][class|style]{5}=[mM]{1}so[a-zA-Z0-9]*@',		   // MS tags remove --> class="MsoNormald3a4"
      //'@<tr[[:space:]]style=.*.\'>@',					   // MS <tr> tags fix KM/03/04/2009 commentaar gemaakt omdat deze expressie niet speciefiek genoeg is.
      '@[[:space:]]class=MsoTableGrid@',				   // MS table class remove
      '@<(o|u){1}l[[:space:]]*[^>]*>@',					   // <ol> and <ul> tags cleanup
      '@<li[[:space:]][^>]*>@',						   // <li> tag cleanup
      '@(<p align=[^>]*)( style=\'[^>]*\')>@',				   // <p> tag alignment cleanup
      '@<span[[:space:]]*class=(SpellE|GramE)>@',		           // MS spellcheck tag remove
      '@<abbr @i',							   // replace <abbr> tags with <acronym> tags temporary
      '@</abbr>@i'							   // replace <abbr> tags with <acronym> tags temporary
    ); // LF201104: changed .* to [^>]* whenever matching parameters/attributes inside tags, to limit scope. .* only worked correctly if there was a newline behind the tag being matched, since . doesnt match newline.
 

    $replace = array (
      "",
      "", 
      "",
      "",
      "", 
      "",
      "",
      "", 
      "", 
      "",
      "", // KM/03/04/2009
      //"$1$2$4$5$6", // KM/03/04/2009
      //"$1$2$4$5$6", // KM/03/04/2009
      "", 
      "", 
      //"<tr>", // KM/03/04/2009
      "",
      "<$1l>",
      "<li>",
      "$1>",
      "", 
      "<acronym ",
      "</acronym>"
    );

    $html = preg_replace ($search, $replace, $html);
    $html = str_replace (chr(10), " ", $html);
    $html = str_replace (chr(13), " ", $html);
    $html = IMS_Relocate ($html, $contentlocation);
    $html = str_replace ('"/ufc', '"http://'. $_SERVER["HTTP_HOST"].'/ufc', $html);
    $html = IMS_Improve ($html);
    return $html;
  }

  function TINYMCE_isinplace($object_id, $htmlpath = "")
  {
    if ($_GET["submode"] == "treeview") return true;

    global $myconfig;   
    $sgn = IMS_SupergroupName();   
    $obj = MB_Ref("ims_" . $sgn . "_objects", $object_id);
    $user = MB_Ref ("shield_" . $sgn . "_users", SHIELD_CurrentUser($sgn));   

    // Use tiny at all?
    if (!IMS_UseInlineHtmlEditor($object_id)) return false;

    // Use inplace only for CMS pages, not for (cms / bpms) forms or CMS templates
    if ($obj["editor"] != "Microsoft Word") return false;

    // Using tinymce (and not devedit)?
    if ($myconfig[$sgn]["usetinymce"] != "yes") return false;
              
    // Never use inplace for history versions
    if ($htmlpath && (strpos($htmlpath, "/objects/history/") !== false)) return false;

    if ($myconfig[$sgn]["usetinymceinplaceonly"] == "yes") return true; // Note: this setting disables inline option, but use "useinlinehtmleditoronly" to disable transfer agent
    if ($myconfig[$sgn]["usetinymceinplacebydefault"] == "yes" && $user["inlineeditor"] != "yes") return true;
    if ($myconfig[$sgn]["usetinymceinplace"] == "yes" && $user["inlineeditor"] == "inplace") return true;

    return $inplace;
  }
  
?>