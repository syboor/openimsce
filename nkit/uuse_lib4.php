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


class LIB_MSWord
{
       // Vars:
       var $handle;

       // Create COM instance to word
       function LIB_MSWord($Visible = false)
       {
           N_Log ("msword", "Before new COM");
           $this->handle = new COM("word.application") or N_Die ("Unable to instantiate Word");
           N_Log ("msword", "After new COM");
           $this->handle->Visible = $Visible;
           N_Log ("msword", "After visible");
       }

       // Print active document to a file using the specified driver
       function Print2File($File, $Driver="Generic Postscript Printer")
       {
           N_Log ("msword", "Before Activepinter $File, $Driver");
           $this->handle->ActivePrinter = $Driver;
           N_Log ("msword", "After Activepinter $File, $Driver");
           $this->handle->ActiveDocument->PrintOut(0, 0, 0, $File);
           N_Log ("msword", "After Printout $File, $Driver");
       }

       // Open existing document
       function Open($File)
       {
           N_Log ("msword", "Before Open $File");
           $this->handle->Documents->Open($File);
           N_Log ("msword", "After Open $File");
       }

       // Create new document
       function NewDocument()
       {
           N_Log ("msword", "Before Add");
           $this->handle->Documents->Add();
           N_Log ("msword", "After Add");
       }

       // Write text to active document
       function WriteText( $Text )
       {
           $this->handle->Selection->Typetext( $Text );
       }

       // Number of documents open
       function DocumentCount()
       {
           return $this->handle->Documents->Count;
       }

       // Save document as another file and/or format
       function SaveAs($File, $Format = 0 )
       {
           N_Log ("msword", "Before Save As $File, $Format");
           $this->handle->ActiveDocument->SaveAs($File, $Format);
           N_Log ("msword", "After Save As $File, $Format");
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

       // Get word version
       function GetVersion()
       {
           return $this->handle->Version;
       }

       // get handle to word
       function GetHandle()
       {
           return $this->handle;
       }

       // Clean up instance with word
       function Quit()
       {
           N_Log ("msword", "Before quit");
           if( $this->handle )
           {
               // close the active document
               @$this->handle->ActiveDocument->Close(false);

               // close word
               $this->handle->Quit(0);

               // free the object
               if (N_PHP5()) {
                  // do nothing, release results into errors using php5
               } else {
                  $this->handle->Release();
               }
               $this->handle = null;
           }
           N_Log ("msword", "After quit");
       }
} // HD: classend
?>