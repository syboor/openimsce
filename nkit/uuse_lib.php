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



define ("LIB_wdFormatDocument", 0);
define ("LIB_wdFormatTemplate", 1);
define ("LIB_wdFormatText", 2);
define ("LIB_wdFormatTextLineBreaks", 3);
define ("LIB_wdFormatDOSText", 4);
define ("LIB_wdFormatDOSTextLineBreaks", 5);
define ("LIB_wdFormatRTF", 6);
define ("LIB_wdFormatUnicodeText", 7);
define ("LIB_wdFormatHTML", 8);

// 20110506 KvD DZ-123 Check on PHP version to enable try/catch in PHP 5 (code sls gv)

if (substr(phpversion(),0,1) <= 4) {
  require_once("uuse_lib4.php");
} else {
  require_once("uuse_lib5.php");
} 
///



function LIB_Doc2URL ($content, $name)
{
  uuse ("tmp");
  $guid = N_GUID();
  $data["content"] = $content;
  $data["name"] = $name;
  TMP_SaveObject ($guid, $data);
  return "/ufc/doc2url/$guid/$name";
}


function LIB_Doc2URLHandler ($name, $params)
{
  Header("Content-type: application/octet-stream");
  Header("Content-Disposition: attachment; filename=$name");
  $data = TMP_LoadObject ($params[1]);
  echo $data["content"];
}

class LIB_MSVisio
{
       // Vars:
       var $handle;
       
       // Create COM instance to word
       function LIB_MSVisio($Visible = false)
       {
           N_Log ("msvisio", "Before new COM");
           $this->handle = new COM("visio.application") or N_Die ("Unable to instantiate Visio");
           N_Log ("msvisio", "After new COM");
           $this->handle->Visible = $Visible;
           N_Log ("msvisio", "After visible");
       }
       
       // Open existing document
       function Open($File)
       {
           N_Log ("msvisio", "Before Open $File");
           $this->handle->Documents->Open($File);
           N_Log ("msvisio", "After Open $File");
       }
              
       // Save active document
       function Save()
       {
           $this->handle->ActiveDocument->Save();
       }
       
       // close active document.
       function Close()
       {
           $this->handle->ActiveDocument->Close();
       }
       
       // get handle to word
       function GetHandle()
       {
           return $this->handle;
       }
   
       // Clean up instance with visio
       function Quit()
       {
           N_Log ("msvisio", "Before quit");
           if( $this->handle )
           {
               // close the active document
               @$this->handle->ActiveDocument->Close(false);

               // close word
               $this->handle->Quit();
   
               // free the object
               if (N_PHP5()) {
                  // do nothing, release results into errors using php5
               } else {
                  $this->handle->Release();
               }
               $this->handle = null;
           }
           N_Log ("msvisio", "After quit");
       }
} // HD: classend


?>