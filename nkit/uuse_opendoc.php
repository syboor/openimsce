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



// OPENDOC_Odt2Text: 
// converts an opendoc odt document to a text file
function OPENDOC_Odt2Text($in, $out) {

  global $myconfig;
  if(!$myconfig["unzip"]) {
    N_LOG("error", "OPENDOC_Odt2Text: unzip not configured in myconfig.");
  } else {
    if(function_exists ("xml_parser_create")) {
      $tmpxml = N_CleanPath ("html::"."/tmp/".N_Random().".xml");

      // an odt file is a zip containing other files:
      // content.xml : main body of document
      // styles.xml : headers and footers
      $fileparts = array( array("file" => "content.xml"),
                          array("file" => "styles.xml")
                        );
      $convertedtext = "";
      foreach($fileparts as $filearray) {
        $temp = OPENDOC_ParseXMLFileInArchive($in, $filearray, $tmpxml);
        $convertedtext = $convertedtext . " " . $temp;
      }
      N_WriteFile($out, $convertedtext);
      N_DeleteFile($tmpxml);
    }
  }
}

// OPENDOC_Ods2Text: 
// converts an opendoc ods (spreadsheet) document to a text file
function OPENDOC_Ods2Text($in, $out) {

  global $myconfig;
  if(!$myconfig["unzip"]) {
    N_LOG("error", "OPENDOC_Ods2Text: unzip not configured in myconfig.");
  } else {
    if(function_exists ("xml_parser_create")) {
      $tmpxml = N_CleanPath ("html::"."/tmp/".N_Random().".xml");

      // an ods file is a zip containing other files:
      // content.xml : main body of document
      // styles.xml : headers and footers
      // from styles.xml: only take TEXT: elements
      $fileparts = array( array("file" => "content.xml"),
                          array("file" => "styles.xml",
                                "elementnamestartswith" => array("TEXT:"))
                        );
      $convertedtext = "";
      foreach($fileparts as $filearray) {
        $temp = OPENDOC_ParseXMLFileInArchive($in, $filearray, $tmpxml);
        $convertedtext = $convertedtext . " " . $temp;
      }
      N_WriteFile($out, $convertedtext);
      N_DeleteFile($tmpxml);
    }
  }
}

// OPENDOC_Odp2Text: 
// converts an opendoc odp presentation to a text file
function OPENDOC_Odp2Text($in, $out) {

  global $myconfig;
  if(!$myconfig["unzip"]) {
    N_LOG("error", "OPENDOC_Odp2Text: unzip not configured in myconfig.");
  } else {
    if(function_exists ("xml_parser_create")) {
      $tmpxml = N_CleanPath ("html::"."/tmp/".N_Random().".xml");

      // an odp file is a zip containing other files:
      // content.xml : main body of document
      // styles.xml : headers and footers
      $fileparts = array( array("file" => "content.xml"),
                          array("file" => "styles.xml")
                        );
      $convertedtext = "";
      foreach($fileparts as $filearray) {
        $temp = OPENDOC_ParseXMLFileInArchive($in, $filearray, $tmpxml);
        $convertedtext = $convertedtext . " " . $temp;
      }
      N_WriteFile($out, $convertedtext);
      N_DeleteFile($tmpxml);
    }
  }
}

function OPENDOC_ParseXMLFileInArchive($in, $filearray, $tmpxml) {
  global $myconfig;
  $file = $filearray["file"];

  $command = $myconfig["unzip"] . ' -p ' . escapeshellarg($in)  . ' ' . escapeshellarg($file) . ' > ' . escapeshellarg($tmpxml);

  system($command);

  global $totaltext;
  $totaltext = "";

  // only take element into account that start with strings from given array
  global $elementnamestartswith;
  $elementnamestartswith= $filearray["elementnamestartswith"];

  $xml_parser = xml_parser_create();
  xml_set_element_handler($xml_parser, "OPENDOC_startElement", "OPENDOC_endElement");
  xml_set_character_data_handler($xml_parser, "OPENDOC_characterData");
  if (!($fp = fopen($tmpxml, "r"))) {
    N_LOG("error", "could not open XML input for " . $in);
    return false;
  }
  while ($data = fread($fp, 1638400)) {
    if (!xml_parse($xml_parser, $data, feof($fp))) {
      N_LOG("error", "XML error: " . xml_error_string(xml_get_error_code($xml_parser)) . " at line " . 
         xml_get_current_line_number($xml_parser));
      return false;
    }
  }
  fclose($fp);
  xml_parser_free($xml_parser);
  return $totaltext;
}

function OPENDOC_startElement($parser, $name, $attrs) {
  global $depth;
  global $currentelement;
  $currentelement[$depth[$parser]] = $name;

  if (false) {
    for ($i = 0; $i < $depth[$parser]; $i++) {
      echo "--";
    }
    echo "Element name: $name ";
    T_EO($attrs);
    echo "<br>";
  }
  $depth[$parser]++;
}

function OPENDOC_endElement($parser, $name) {
  global $depth;
  $depth[$parser]--;
}

function OPENDOC_characterData($parser, $data)
{
  global $totaltext;
  global $elementnamestartswith;
  global $depth;
  global $currentelement;
  $name = $currentelement[$depth[$parser]];

  //echo "char: " . $data . "<br>";
  if(!is_array($elementnamestartswith)) {
    $totaltext .= $data . " ";
  } else {
    foreach($elementnamestartswith as $start) {
      if(strpos(" " . $name, $start) ==1) $totaltext .= $data . " ";
    }
  }
}

?>