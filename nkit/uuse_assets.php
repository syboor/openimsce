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



function ASSETS_StoreImage ($supergroupname, $imagename, $imagecontent)
{
  $id = N_GUID();
  $dir = "html::$supergroupname/assets/".$id."/";
  N_WriteFile ($dir.$imagename, $imagecontent);
  $url = "/ufc/rapid/$supergroupname/assets/$id/$imagename";
  return $url;
}


function ASSETS_ShowImageThumbnail ($value, $hmax = 50, $wmax = 50)
{
  $value = str_replace ("/ufc/rapid/", "", $value);
  if ($value) {
    $image = N_CleanPath ("html::".$value);

    list($width, $height, $type, $attr) = getimagesize($image);
    $hscale = $height / $hmax;
    $wscale = $width / $wmax;
    if (($hscale > 1) || ($wscale > 1)) {
      $scale = ($hscale > $wscale)?$hscale:$wscale;
    } else {
      $scale = 1;
    }
    $newwidth = floor($width / $scale);
    $newheight= floor($height / $scale);
    return "<img width=\"$newwidth\" height=\"$newheight\" border=0 src=\"/ufc/rapid/$value\">";
  } else {
    return ML("N.v.t.", "N/A");
  }
}

function ASSETS_ShowDiskfileName ($value)
{
  $value = N_KeepAfter($value, "/", true);
  if ($value)
    return "$value";
}

function ASSETS_ShowImage ($value)
{
  $value = str_replace ("/ufc/rapid/", "", $value);
  if ($value) {
    $image = N_CleanPath ("html::".$value);
    list($width, $height, $type, $attr) = getimagesize($image);
    return "<img width=\"$width\" height=\"$height\" border=0 src=\"/ufc/rapid/$value\">";
  } else {
    return ML("N.v.t.", "N/A");
  }
}

function ASSETS_ShowDiskFile ($value)
{
  $lname = ASSETS_ShowDiskFileName($value); 
  $value = str_replace ("/ufc/rapid/", "", $value);
  if ($value) {
    return '<a target="_blank" href="/ufc/rapid/' . $value . '" title ="' . $lname .'">' . $lname . '</a>';
  } else {
    return ML("N.v.t.", "N/A");
  }
}

function ASSETS_ShowHTML ($supergroupname, $id)
{
  uuse ("ims");
  if (!$id) {
    $path = "html::openims/new_word/";
    $html = N_ReadFile ($path."page.html");
    $html = IMS_CleanupTags ($html);
    $html = IMS_Relocate ($html, "/openims/new_word/");
  } else {
    $path = "html::$supergroupname/assets/$id/";
    $html = N_ReadFile ($path."page.html");
    $html = IMS_CleanupTags ($html);
    $html = IMS_Relocate ($html, "/$supergroupname/assets/$id/");
  }
  $html = IMS_Improve ($html);
  return $html;
}

function ASSETS_ReEditHTML ($supergroupname, $id)
{
  $url = IMS_GenerateTransferURL("\\".$supergroupname."\\assets\\".$id."\\", "page.html", "winword.exe", true);
  uuse ("dhtml");
  DHTML_LoadTransURL($url);
}

function ASSETS_EditHTML ($supergroupname, $oldid)
{
  if ($oldid) {
    $newid = $oldid;
    if (!file_exists (N_CleanPath ("html::$supergroupname/assets/$newid/page.html"))) {
      $oldpath = "html::openims/new_word/";
      $newpath = "html::$supergroupname/assets/$newid/";
      N_CopyDir ($newpath, $oldpath);
    }
  } else {
    $oldpath = "html::openims/new_word/";
    $newid = N_GUID();
    $newpath = "html::$supergroupname/assets/$newid/";
    N_CopyDir ($newpath, $oldpath);
  }
  $url = IMS_GenerateTransferURL("\\".$supergroupname."\\assets\\".$newid."\\", "page.html", "winword.exe", true);
  uuse ("dhtml");
  DHTML_LoadTransURL($url);
  return $newid;

//  $newid = N_GUID();
//  $newpath = "html::$supergroupname/assets/$newid/";
//  N_CopyDir ($newpath, $oldpath);
//  $url = IMS_GenerateTransferURL("\\".$supergroupname."\\assets\\".$newid."\\", "page.html", "winword.exe", true);
//  uuse ("dhtml");
//  DHTML_LoadTransURL($url);
//  return $newid;
}

function ASSET_Delete ($supergroupname, $id)
{
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(getenv("DOCUMENT_ROOT")."/$supergroupname/assets/$id");
  N_CHMod (getenv("DOCUMENT_ROOT")."/$supergroupname/assets/$id");
  rmdir(N_CleanPath ("html::"."/$supergroupname/assets/$id"));    
}

?>
