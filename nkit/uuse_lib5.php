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


// Original SLS code Guido Vriesinga

class LIB_MSWord {
  // Vars:
  var $handle;
  var $error;

  var $false;
  var $true;

  // Create COM instance to word
  function LIB_MSWord($Visible = false) {

    $this->error = "noError";

    $this->false =  new VARIANT(false, VT_BOOL);
    $this->true= new VARIANT(true, VT_BOOL);

//    N_Log ("msword", "Before new COM");
    $this->handle = new COM("word.application") or N_Die ("Unable to instantiate Word");
//    N_Log ("msword", "After new COM");
    $this->handle->DisplayAlerts = $this->false;
    $this->handle->Visible = $Visible;
//    N_Log ("msword", "After visible");
  }

  // Print active document to a file using the specified driver
  function Print2File($File, $Driver="Generic Postscript Printer") {
    if ($this->error == "noError") {
//      N_Log ("msword", "Before Activepinter $File, $Driver");
      try {
        $this->handle->ActivePrinter = $Driver;
      } catch (com_exception $e) {
        $this->error = "SetActivePrinterErr";
        N_Log ("error", "LIB_MSWord $this->error $Driver $e");
      }

//      N_Log ("msword", "After Activepinter $File, $Driver");
      if ($this->error == "noError") {
        try {
          $this->handle->ActiveDocument->PrintOut(0, 0, 0, $File);
        } catch (com_exception $e) {

          $this->error = "PrintOutErr";
          N_Log ("error", "LIB_MSWord $this->error $e");
        }

 //       N_Log ("msword", "After Printout $File, $Driver");
      }
    }
  }

  // Open existing document
  function Open($File)
  {
    if ($this->error == "noError") {
 //     N_Log ("msword", "Before Open $File");
      try {
        $this->handle->Documents->Open($File);
      } catch (com_exception $e) {
        $this->error = "OpenDocErr";
        N_Log ("error", "LIB_MSWord $this->error $e");

        //$this->handle->Quit($this->false);
      }

//      N_Log ("msword", "After Open $File");
    }
  }

  // Create new document
  function NewDocument()
  {
    if ($this->error == "noError") {
 //     N_Log ("msword", "Before Add");
      try {
        $this->handle->Documents->Add();
      } catch (com_exception $e) {
        $this->error = "AddDocErr";
        N_Log ("error", "LIB_MSWord $this->error $e");
      }
 //     N_Log ("msword", "After Add");
    }
  }

  // Write text to active document
  function WriteText( $Text )
  {
    if ($this->error == "noError") {
      try {
        $this->handle->Selection->Typetext( $Text );
      } catch (com_exception $e) {
        $this->error = "TypetextErr";
        N_Log ("error", "LIB_MSWord $this->error $e");
      }
    }
  }

  // Number of documents open
  function DocumentCount()
  {
    try {
      return $this->handle->Documents->Count;
    } catch (com_exception $e) {
      $this->error = "DocumentCountErr";
      N_Log ("error", "LIB_MSWord $this->error $e");
    }
  }

  // Save document as another file and/or format
  function SaveAs($File, $Format = 0 )
  {
//    N_Log ("msword", "Before Save As $File, $Format");
    try {
      $this->handle->ActiveDocument->SaveAs($File, $Format);
    } catch (com_exception $e) {
      $this->error = "SaveAsErr";
      N_Log ("error", "LIB_MSWord $this->error $File $Format $e");
    }
//    N_Log ("msword", "After Save As $File, $Format");
  }

  // Save active document
  function Save()
  {
    try {
      $this->handle->ActiveDocument->Save();
    } catch (com_exception $e) {
      $this->error = "SaveErr";
      N_Log ("error", "LIB_MSWord $this->error $e");
    }
  }

  // close active document.
  function Close()
  {
    try {
      $this->handle->ActiveDocument->Close();
    } catch (com_exception $e) {
      $this->error = "DocCloseErr";
      N_Log ("error", "LIB_MSWord $this->error $e");
    }
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
//    N_Log ("msword", "Before quit");
    if( $this->handle ) {
      // close the active document

      try {
        @$this->handle->ActiveDocument->Close(false);
      } catch (com_exception $e) {
        $this->error = "QuitDocCloseErr";
        N_Log ("error", "LIB_MSWord $this->error $e");
      }

      // Quit word
      try {
        $this->handle->Quit($this->false);
      } catch (com_exception $e) {
        $this->error = "QuitErr";
        N_Log ("error", "LIB_MSWord $this->error $e");
      }
      // free the object
      if (N_PHP5()) {
        // do nothing, release results into errors using php5
      } else {
        $this->handle->Release();
      }
      $this->handle = null;
    }
//    N_Log ("msword", "After quit");
  }
} // HD: classend

?>