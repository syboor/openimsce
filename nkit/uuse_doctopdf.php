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



// JH mei 2011
// enkele conversie doc to pdf 
// onder switch 'doctopdfoption' 
// voor bouwfonds

uuse("files");
uuse("forms");

function DOCTOPDF_Option()
{
  global $currentobject, $truecurrentobject, $myconfig;
  $sgn = IMS_SuperGroupName();
  $table = "ims_" . $sgn . "_objects";

  $doc = MB_Load($table, $currentobject);
  
  $sc = FILES_IsShortcut ($sgn, $truecurrentobject);
  $ext = FILES_FileType($sgn, $currentobject, "preview");

  if ($myconfig["neevia"] == "yes")
    $arr = $myconfig["neeviadocformats"];
  else
    $arr = array("doc");

  if (!$sc and $arr and in_array($ext, $arr) and $doc["doctopdf"] !== "yes")
  { 
    $form = array();
    $form["input"]["currentobject"] = $currentobject;

    $form["title"] = ML("Opslaan als pdf", "Save as pdf");
    $form["formtemplate"] = "<table><tr><td><font face=\"arial\" size=2>".ML("Een PDF versie van het actieve document maken?", "Make a PDF version of the active document?")."</font></td></tr>
                                    <tr><td>&nbsp;</td></tr>
                                    <tr><td><center>[[[ok]]]&nbsp;&nbsp;&nbsp;[[[cancel]]]</center></td></tr></table>";
    $form["postcode"] = '

      uuse("flex");
      $sgn = IMS_SupergroupName();
      FLEX_LoadSupportFunctions($sgn);

      uuse("doctopdf");
      $currentobject = $input["currentobject"];

      DOCTOPDF_Convert($currentobject);
    ';

    $url = FORMS_URL($form);
    return '<a class="ims_navigation" href="'.$url.'"><img border=0 src="/ufc/rapid/openims/ico_pdf.gif"> ' . 
           ML("Opslaan als pdf", "Save as pdf") . '</a><br>';
  }
}

function DOCTOPDF_Convert($docid)
{
  $sgn = IMS_SupergroupName();
  $table = "ims_" . $sgn . "_objects";

  $doc = MB_Load($table, $docid);
  $pdf = N_CurrentProtocol() . $_SERVER["HTTP_HOST"] . FILES_DocPreviewUrl($sgn, $docid, true);
  $pdfcont = file_get_contents($pdf);
  if ($pdfcont)
  {
    $doc["doctopdf"] = "yes";

    $usr = SHIELD_CurrentUser();
    $doc["allocto"] = $usr;

    $time = time();
    $guid = N_GUID();
    $doc["history"][$guid]["type"] = "doctopdf";
    $doc["history"][$guid]["when"] = $time;
    $doc["history"][$guid]["author"] = $usr;  
    $doc["history"][$guid]["server"] = N_CurrentServer ();
    $doc["history"][$guid]["http_host"] = getenv("HTTP_HOST");
    $doc["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
    $doc["history"][$guid]["server_addr"] = $SERVER_ADDR;

    MB_Save($table, $docid, $doc);
  }
  else
  {
     uuse("forms");
     FORMS_ShowError(ML("Fout", "Error"), ML("PDF Conversie van dit document is niet mogelijk", "PDF Conversion of this document is not possible"));
  }
}

?>