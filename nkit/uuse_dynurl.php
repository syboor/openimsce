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



function DYNURL_CleanupURL ($url)
{
  return $url;
}

function DYNURL_CheckIfValid ($url)
{
  return true;
}

function DYNURL_CurrentURL ($sgn, $object_id)
{
  $object = &MB_Ref ("ims_{$sgn}_objects", $object_id);
  $site_id = IMS_Object2Site ($sgn, $object_id);
  if ($object["dynurl"]["current"]) return $object["dynurl"]["current"];
  return "/$site_id/$object_id.php";
}

function DYNURL_LLCreate ($sgn, $object_id, $url) // Assumes valid clean URL
{
  if (!DYNURL_CheckIfValid ($url)) N_DIE ("DYNURL_Create invalid URL");
  $object = &MB_Ref ("ims_{$sgn}_objects", $object_id);
  $site_id = IMS_Object2Site ($sgn, $object_id);
  $currenturl =  DYNURL_CurrentURL ($sgn, $object_id);
  if ($currenturl == $url) return;
  $object["dynurl"]["current"] = $url;
  $object["dynurl"]["other"][$currenturl] = $currenturl;
  unset ($object["dynurl"]["other"][$url]);

  $phpcode = '
    <?  
      include (getenv ("DOCUMENT_ROOT")."/nkit/nkit.php");  
      uuse ("ims");  
      $sitecollection_id = "'.$sgn.'";  
      $site_id = "'.$site_id.'";  
      $object_id = "'.$object_id.'";  
      N_SetCookie ("ims_myurl", N_MyFullURL (), time()+100000, "/", "", (N_CurrentProtocol() == "https://"), true);  
      echo IMS_SuperMixer ($sitecollection_id, $site_id, $object_id, "generate_dynamic_page");  
      N_Exit();
   ?>';
  N_WriteFile ("html::$url/index.php", $phpcode);
  T_EO ($object);
}

?>