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



// CASE management 



function CASE_FolderExists ($supergroupname, $folder_id)
{
  $tree = CASE_TreeRef ($supergroupname, $folder_id);  
  if ($tree["objects"][$folder_id]) return true; else return false;
}

// access tree object containing $folder_id;
function &CASE_TreeRef ($supergroupname, $folder_id="")
{
  if (substr ($folder_id, 0, 1)=="(") {
    
  } else {
    $tree = &MB_Ref ("ims_trees", $supergroupname."_documents");
  }
  return $tree;
}

function CASE_TreeTable ($supergroupname, $folder_id="")
{
  
  return "ims_trees";
}

function CASE_TreeKey ($supergroupname, $folder_id="")
{
  
  return $supergroupname."_documents";
}

function CASE_RootFolder ($folder_id)
{
  if ($folder_id=="root") return true;
  
  return false;
}

function CASE_List ($supergroupname)
// in the future it should look at the currernt users access rights
{
  $result = array();
  
  return $result;
}

function CASE_visiblecasename( $sgn = false, $currentfolder = false )
{
  global $myconfig;

  if ( !$sgn ) $sgn = IMS_supergroupname();
  if ( !$currentfolder ) $currentfolder = $GLOBALS["currentfolder"];

  if ( substr($currentfolder, 0, 1)=="(" )
  {
    $case_id = substr ($currentfolder, 0, strpos ($currentfolder, ")")+1);
    $case = MB_Ref ("ims_".$sgn."_case_data", $case_id);
    $case_postfix = $myconfig[$sgn]["casetext"] ? $myconfig[$sgn]["casetext"] : ML("dossier","case");
    return N_htmlentities( $case["shorttitle"] ) . " " . $case_postfix;
  }
  return false;
}



?>