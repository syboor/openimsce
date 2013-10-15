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



// IMS Global mix.php

function IMSMIX_Metaspec() 
{
  global $siteinfo;

  $object = &MB_Ref ("ims_".$siteinfo["sitecollection"]."_objects", $siteinfo["object"]);
  $object["parameters"]["preview"]["template"] = $object["template"];

  $metaspec["fields"]["longtitle"]["type"] = "string";
  $metaspec["fields"]["shorttitle"]["type"] = "smallstring";
  $metaspec["fields"]["keywords"]["type"] = "bigstring";
  $metaspec["fields"]["template"]["type"] = "list";
  $list = MB_Query ("ims_".$siteinfo["sitecollection"]."_templates");
  if (is_array($list)) reset($list);
  if (is_array($list)) while (list($key)=each($list)) {
    $template = &MB_Ref ("ims_".$siteinfo["sitecollection"]."_templates", $key);
    $metaspec["fields"]["template"]["values"][$template["name"]] = $key;
    if (!$metaspec["fields"]["template"]["default"]) {
      $metaspec["fields"]["template"]["default"] = $template["name"];
    }
  }
  $metaspec["fields"]["workflow"]["type"] = "list";
  $wlist = MB_Query ("shield_".$siteinfo["sitecollection"]."_workflows", '$record["cms"]');
  $allowed = SHIELD_AllowedWorkflows ($siteinfo["sitecollection"], $siteinfo["object"]);
  if (is_array($wlist)) reset($wlist);
  if (is_array($wlist)) while (list($wkey)=each($wlist)) { 
    if ($allowed[$wkey]) {            
      $workflow = &MB_Ref ("shield_".$siteinfo["sitecollection"]."_workflows", $wkey);
      $metaspec["fields"]["workflow"]["values"][$workflow["name"]] = $wkey;
    }
  }
  $metaspec["fields"]["module"]["type"] = "list";
  $metaspec["fields"]["module"]["default"] = ""; 
  $metaspec["fields"]["module"]["values"][ML("Geen","None")] = "";
  $modules = OFLEX_Modules ("cmsmodule");
  foreach ($modules as $moduleid => $modulename) {
    $metaspec["fields"]["module"]["values"][$modulename] = $moduleid;
  }
  return $metaspec;
}

function IMSMIX_Formtemplate($extra)
{
  global $siteinfo;
  $formtemplate =  '<body bgcolor=#f0f0f0><br><center><table>';
  $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Template","Template").':</b></font></td><td>[[[template]]]</td></tr>';
  $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Workflow","Workflow").':</b></font></td><td>[[[workflow]]]</td></tr>';
  $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Editor","Editor").':</b></font></td><td><font face="arial" size=2>'.$siteinfo["allobjectdata"]["editor"].'</font></td></tr>';
  $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Lange titel","Long title").':</b></font></td><td>[[[longtitle]]]</td></tr>';
  $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Korte titel (menu)","Short title (menu)").':</b></font></td><td valign=top>[[[shorttitle]]]</td></tr>';
  $formtemplate .= '<tr><td><font face="arial" size=2><b>'.ML("Zoektermen","Keywords").':</b></font></td><td>[[[keywords]]]</td></tr>';
  $formtemplate .= $extra;
  $formtemplate .= '<tr><td colspan=2>&nbsp</td></tr>';
  $formtemplate .= '<tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>';
  $formtemplate .= '</table></center></body>';
  return $formtemplate;
}

function IMSMIX_FormDimensions ($extralines, $width)
{
  $output["formwidth"] = max (600, $width);
  $output["formheight"] = 300 + $extralines * 10;
  $output["formleft"] = 180;
  $output["formtop"] = 200;
  return $output;
}

function IMSMIX_Default()
{
  $output["longtitle"] = "";
  $output["shorttitle"] = "";
  $output["keywords"] = "";
  return $output;  
}

function IMSMIX_GetRawTemplate()
{
  global $siteinfo, $usetemplate;
  $templatename = $siteinfo["allobjectdata"]["template"];
  if ($usetemplate) $templatename = $usetemplate;
  if (IMS_Preview()) {
    $mypath = N_ProperPath ("html::".$siteinfo["sitecollection"]."/preview/templates/".$templatename);
  } else {
    $mypath = N_ProperPath ("html::".$siteinfo["sitecollection"]."/templates/".$templatename);
  }
  $template = N_ReadFile ($mypath."template.html");
  return $template;
}

function IMSMIX_ProcessContentElements ($content)
{
  uuse ("flex");

  $all = FLEX_LocalComponents (IMS_SuperGroupName(), "cmsblock");
  foreach ($all as $id => $specs) {
    $regexp = "(\[\[\[(<[^<>]*>)*".$specs["tag"]."(<[^<>]*>)*(:([^]]*))?\]\]\])";
    $tag = N_RegExp ($content, $regexp);
    while ($tag) {
      global $flexparams;
      $flexparams = preg_replace ("'<[\/\!]*?[^<>]*?>'si", "", N_RegExp ($content, $regexp, 5, 1));
      $content = str_replace ($tag, FLEX_Call (IMS_SuperGroupName(), "cmsblock", $id, "content"), $content);
      $flexparams = false;
      $tag = N_RegExp ($content, $regexp); 
    }
  }
  global $siteinfo;
  $content = str_replace ("[[[coolbar]]]", IMS_CoolBar(), $content);
//  $content = str_replace ("[[[year]]]", N_Year(), $content);

  $content = IMS_UseMetadata ($content, $siteinfo);  

  return $content;
}

function IMSMIX_InsertContent ($content)
{
  global $siteinfo;
  return IMS_MegaMix ($content, IMS_GetObjectContent ($siteinfo["sitecollection"], $siteinfo["object"]));
}


function IMSMIX_GenerateStaticPage()
{
  global $siteinfo;
  $content  = '<?';
  $content .= ' $sitecollection_id = "'.$siteinfo["sitecollection"].'";';
  $content .= ' $site_id = "'.$siteinfo["site"].'";';
  $content .= ' $object_id = "'.$siteinfo["object"].'";';
  $content .= ' include (getenv ("DOCUMENT_ROOT")."/nkit/ht.php");';
  $content .= ' if (!$htcacheing) {';
  $content .=   ' include (getenv ("DOCUMENT_ROOT")."/nkit/nkit.php");';
  $content .=   ' uuse ("ims");';
  $content .=   ' N_SetCookie ("ims_myurl", N_MyFullURL (), time()+100000, "/", "", (N_CurrentProtocol() == "https://"), true);';
  $content .=   ' echo IMS_SuperMixer ($sitecollection_id, $site_id, $object_id, "generate_dynamic_page");';
  $content .=   ' N_Exit();';
  $content .= ' }';
  $content .= '?>';
  return $content;
}

?>