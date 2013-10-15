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



// Example link: http://fusion.google.com/add?moduleurl=http%3A//dev.openims.com/ufc/gadget/1/2/3/ims.xml

function GADGETS_ContentGenerator ($name, $params)
{
  uuse ("portal");
  if (SHIELD_CurrentUser()=="unknown") SHIELD_ForceLogon();
  $portlet = MB_Ref ("portlets_".$params[2], $params[3]);
  $params["define"] = $portlet["define"];
  $params["edit"] = $elemdata["data"];  
  echo "<font face=\"arial\" size=\"2\">".PORTLET_Call ($portlet["type"], "show", $params);
}

function GADGETS_Generator ($name, $params)
{
  $portlet = MB_Ref ("portlets_".$params[2], $params[3]);
  $url = "http://".getenv("HTTP_HOST")."/ufc/gadgetcontent/".$params[1]."/".$params[2]."/".$params[3]."/ims.xml";
  N_Log ("test", $url);
  echo '<?xml version="1.0" encoding="UTF-8" ?> 
  <Module>
    <ModulePrefs title="'.$portlet["name"].'" /> 
    <ModulePrefs scrolling="true" /> 
    <ModulePrefs height="'.$portlet["height"].'" />  
    <Content type="url" href="'.$url.'"/>
  </Module>';
/*
    <UserPref name="size" 
         display_name="Size"
         datatype="enum"
         default_value="3">
      <EnumValue value="3" display_value="Small (3)"/>
      <EnumValue value="5" display_value="Medium (5)"/>
      <EnumValue value="9" display_value="Big (9)"/>
    </UserPref>
*/
}

?>