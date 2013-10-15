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



/* This file has three sections:
 * - DOCX TO TEXT CONVERSION
 * - METADATA SUBSTITUTION
 * - OLD APPROACH TO METADATA SUBSTITION
 */

/******* DOCX TO TEXT CONVERSION *********/

// TODO: check how < > and " in text will appear in the search index

// OPENXML_Docx2Text: 
// Converts a Microsoft Word "Open"XML document (docx) to text
function OPENXML_Docx2Text($in, $out) {

  global $myconfig;
  if(!$myconfig["unzip"]) {
    N_LOG("error", "OPENXML_Docx2Text: unzip not configured in myconfig.");
  } else {
    uuse("tmp");
    $tmpdir = TMP_Directory();
    N_Debug("OPENXML_Docx2Text: tmpdir = $tmpdir");

    $command = $myconfig["unzip"] . ' ' . escapeshellarg($in) . ' -d ' . $tmpdir;
    exec($command);

    // Find the document relationships (hyperlinks and other stuff that is defined in /word/_rels/document.xml.refs and used in word/document.xml)
    $relxml = N_UTF2HTML(N_ReadFile($tmpdir . "/word/_rels/document.xml.rels"));
    $matches = $docrels = array();
    preg_match_all('{<Relationship Id="(.*?)" Type=".*?/([^/]*?)" Target="(.*?)"( .*?)?/>}', $relxml, $matches, PREG_SET_ORDER);
    foreach ($matches as $match) {
      $key = $match[2] . "#" . $match[1];
      $docrels[$key] = $match[3];
    }
    N_Debug("OPENXML_Docx2Text: docrels = <pre>" . htmlspecialchars(print_r($docrels, 1)) . "</pre>");

    $files = OPENXML_InterestingFilesDocx($tmpdir);
    foreach ($files as $file) {
      if (N_FileSize($tmpdir . "/" . $file) < 10000000) { // protect us against large files (preg_match can take a long time...)
        N_Debug("OPENXML_Docx2Text: processing file $file");
        $xml = N_UTF2HTML(N_ReadFile($tmpdir . "/" . $file));
        $tmptext = OPENXML_Xml2Text($xml, $docrels) . "\n";
        if (strlen($convertedtext) + strlen($tmptext) > 10000000) { // Make sure final result is not too large
          $convertedtext .= ML("Het document is te groot en wordt daarom niet volledig geindexeerd", "The document is too large and wordt daarom niet volledig geindexeerd") . "\n";
          break;
        } else {
          $convertedtext .= $tmptext;
        }
        N_Debug("OPENXML_Docx2Text: convertedtext " . strlen($convertedtext) . " bytes");
      } else {
        $convertedtext .= ML("Het document is te groot en wordt daarom niet volledig geindexeerd", "The document is too large and wordt daarom niet volledig geindexeerd") . "\n";
        N_Debug("OPENXML_Docx2Text: skipping file $file (too large)");
      }
    }

    N_WriteFile($out, $convertedtext);
  }
}

// OPENXML_Xlsx2Text: 
// Converts a Microsoft Office "Open" XML spreadsheet (xslx) to text
function OPENXML_Xlsx2Text($in, $out) {

  global $myconfig;
  if(!$myconfig["unzip"]) {
    N_LOG("error", "OPENXML_Xlsx2Text: unzip not configured in myconfig.");
  } else {
    uuse("tmp");
    $tmpdir = TMP_Directory();
    N_Debug("OPENXML_Xlsx2Text: tmpdir = $tmpdir");

    $command = $myconfig["unzip"] . ' ' . escapeshellarg($in) . ' -d ' . $tmpdir;
    exec($command);

    $files = OPENXML_InterestingFilesXlsx($tmpdir, true);
    foreach ($files as $file) {
      if (N_FileSize($tmpdir . "/" . $file) < 500000) { // protect us against large files (preg_match can take a long time...) // gv  this was 10MB but indexing stopped then, maybe 5 mb is best limit...
        N_Debug("OPENXML_Xlsx2Text: processing file $file");

// 20110301 KvD use strip-tags for xlsx shared strings
        $xml = N_UTF2HTML(N_ReadFile($tmpdir . "/" . $file));

        $sharedstrtxt = "sharedStrings.xml";

        if (substr($file, -strlen($sharedstrtxt)) == $sharedstrtxt) {
          $tmptext = str_replace("<", " <", $xml);
          $tmptext = strip_tags($tmptext);
        } else {
          $tmptext = OPENXML_Worksheet2Text($xml, $tmpdir);
        }

        if (strlen($convertedtext) + strlen($tmptext) > 10000000) { // Make sure final result is not too large
          $convertedtext .= ML("Het document is te groot en wordt daarom niet volledig geindexeerd", "The document is too large and wordt daarom niet volledig geindexeerd") . "\n";
          break;
        } else {
          $convertedtext .= $tmptext;
        }
        N_Debug("OPENXML_Xlsx2Text: convertedtext " . strlen($convertedtext) . " bytes");
      } else {
        $convertedtext .= ML("Het document is te groot en wordt daarom niet volledig geindexeerd", "The document is too large and wordt daarom niet volledig geindexeerd") . "\n";
        N_Debug("OPENXML_Xlsx2Text: skipping file $file (too large)");
      }
    }

    N_WriteFile($out, $convertedtext);
  }
}

// OPENXML_Pptx2Text: 
// Converts a Microsoft Office "Open" Powerpoint presentation (pptx) to text (or at least the text parts)
function OPENXML_Pptx2Text($in, $out) {

  global $myconfig;
  if(!$myconfig["unzip"]) {
    N_LOG("error", "OPENXML_Pptx2Text: unzip not configured in myconfig.");
  } else {
    uuse("tmp");
    $tmpdir = TMP_Directory();
    N_Debug("OPENXML_Pptx2Text: tmpdir = $tmpdir");

    $command = $myconfig["unzip"] . ' ' . escapeshellarg($in) . ' -d ' . $tmpdir;
    exec($command);

    $files = OPENXML_InterestingFilesPptx($tmpdir);
    foreach ($files as $file) {
      if (N_FileSize($tmpdir . "/" . $file) < 10000000) { // protect us against large files (preg_match can take a long time...)
        N_Debug("OPENXML_Pptx2Text: processing file $file");
        $xml = N_UTF2HTML(N_ReadFile($tmpdir . "/" . $file));
        $tmptext = OPENXML_Slide2Text($xml);
        if (strlen($convertedtext) + strlen($tmptext) > 10000000) { // Make sure final result is not too large
          $convertedtext .= ML("Het document is te groot en wordt daarom niet volledig geindexeerd", "The document is too large and wordt daarom niet volledig geindexeerd") . "\n";
          break;
        } else {
          $convertedtext .= $tmptext;
        }
        N_Debug("OPENXML_Pptx2Text: convertedtext " . strlen($convertedtext) . " bytes");
      } else {
        $convertedtext .= ML("Het document is te groot en wordt daarom niet volledig geindexeerd", "The document is too large and wordt daarom niet volledig geindexeerd") . "\n";
        N_Debug("OPENXML_Pptx2Text: skipping file $file (too large)");
      }
    }

    N_WriteFile($out, $convertedtext);
  }
}


function OPENXML_Worksheet2Text($xml, $tmpdir)
{
  $p1 = strpos($xml, "<c");
  $p2 = strpos($xml, "</c>");
  $txt = "";
  while ($p1 !== false and $p2 !== false)
  {
    $xml1 = substr($xml, $p1, $p2 - $p1 + 4);
    $txt .= " " . OPENXML_WorksheetCell2Text($xml1, $tmpdir);

    $xml = substr($xml, $p2 + 4);
    $p1 = strpos($xml, "<c");
    $p2 = strpos($xml, "</c>");
  }

  return $txt;
}

function OPENXML_Slide2Text($xml)
{
  $p1 = strpos($xml, "<a:t>");
  $p2 = strpos($xml, "</a:t>");  
  $txt = "";

  while ($p1 !== false and $p2 !== false)
  {
    $xml1 = substr($xml, $p1, $p2 - $p1 + 6);
    $txt .= " " . OPENXML_SlideText2Text($xml1);

    $xml = substr($xml, $p2 + 6); 
    $p1 = strpos($xml, "<a:t>");
    $p2 = strpos($xml, "</a:t>");  
  }

  return $txt;
}

function OPENXML_WorksheetCell2Text($xml, $tmpdir)
{
  $pt = strpos($xml, 't="s"');
  $pv = strpos($xml, "<v>"); $pv = $pv+3;

  if ($pt === false)
  {
    $txt = (float)substr($xml, $pv);
  }
  else 
  {
    $txt = "";
// Not needed anymore
//    $i = (int)substr($xml, $pv);
//    $txt = OPENXML_SharedString2Text($i, $tmpdir);
  }

  return $txt;
}

function OPENXML_SlideText2Text($xml)
{
  $p1 = strpos($xml, "<a:t>");
  $p2 = strpos($xml, "</a:t>"); 

  if ($p1 === false or $p2 === false)
  {
    $txt = "";
  }
  else 
  {
    $txt = substr($xml, $p1 + 5, $p2 - $p1 - 5);
  }

  return $txt;
}

function OPENXML_SharedString2Text($i, $tmpdir)
{
   global $g_xmlcache;

  if (!$g_xmlcache) {
    $g_xmlcache = $xml = N_ReadFile($tmpdir . "/xl/sharedStrings.xml");
  } else
    $xml = $g_xmlcache;

  while ($i>0 and $xml)
  {
    $p = strpos($xml, "<t>");
    if ($p !== false)
      $xml = substr($xml, $p + 3); 
    else
      $xml = "";
    $i = $i - 1;
  }

  if (($i == 0) and $xml)
  {
    $p = strpos($xml, "<t>");
    $p = $p+3;
    $xml = substr($xml, $p);
    $p2 = strpos($xml, "</t>");
    $txt = substr($xml, 0, $p2);
  }

  return $txt;
}

function OPENXML_InterestingFilesDocx($path) {
  // Files that are interesting, meaning that they contain content that we want to make searchable and/or metadata-substitutable
  // Make sure the *order* of the result is the order that you would want to present the content (first headers, than the body, than footers etc.)
  // This list is probably not complete; feel free to expand it if you have a document with interesting textual content in a specific file.

  $tree = N_QuickTree($path);
  $rawlist = array();
  $result = array();
  foreach ($tree as $specs) {
    $relname = substr($specs["relpath"] . $specs["filename"], 1);
    $rawlist[$relname] = $relname;
  }

  // The header
  foreach ($rawlist as $file) if (preg_match('{^word/header.*\.xml$}', $file)) $result[] = $file;

  // The document body, footnotes, endnotes
  foreach (array("word/document.xml", "word/footnotes.xml", "word/endnotes.xml") as $file) {
    if ($rawlist[$file]) $result[] = $file;
  }

  // The footer
  foreach ($rawlist as $file) if (preg_match('{^word/footer.*\.xml$}', $file)) $result[] = $file;

  return $result;

}

function OPENXML_InterestingFilesXlsx($path, $sharedstrings=false) {

  $tree = N_QuickTree($path);
  $rawlist = array();
  foreach ($tree as $specs) 
  {
    $relname = substr($specs["relpath"] . $specs["filename"], 1);
    if ($specs["relpath"] == "/xl/worksheets/" || $relname == "xl/sharedStrings.xml" )
      $rawlist[$relname] = $relname;
  }

  return $rawlist;

}

function OPENXML_InterestingFilesPptx($path) {

  $tree = N_QuickTree($path);
  $rawlist = array();
  foreach ($tree as $specs) 
  {
    $relname = substr($specs["relpath"] . $specs["filename"], 1);
    if ($specs["relpath"] == "/ppt/slides/")
      $rawlist[$relname] = $relname;
  }

  return $rawlist;

}

function OPENXML_Xml2Text($xml, $docrels = array()) {
  $content = $xml;
  N_Debug("OPENXML_Xml2Text: xml = <pre>" . htmlentities($xml) . "</pre>");

  // Remove XML preamble
  $content = preg_replace('{\<\?xml .*?\?>(\r)?\n}', '', $content);

  // Remove language tags
  $content = preg_replace('{<w:rPr><w:lang w:val="[a-zA-Z0-9\-_]*"/></w:rPr>}', '', $content);
  $content = preg_replace('{<w:lang w:val="[a-zA-Z0-9\-_]*"/>}', '', $content);

  // Convert paragraph start and paragraph end into newlines
  $content = preg_replace('{<w:p [^/>]+?/>|</w:p>}', "\n", $content);
  // Convert breaks into newlines
  $content = preg_replace('{<w:br/>}', "\n", $content);
  // Convert tabs (ordinary and positional) into tabs
  $content = preg_replace('{<w:p?tab/>|<w:p?tab [^/]+/>}', "    ", $content);
  // Convert "paragraph border" (?) into ascii art horizontal line
  $content = preg_replace('{<w:pBdr>.*?</w:pBdr>}', str_repeat("-", 78) . "\n", $content);

  // Lists
  $content = preg_replace_callback('{<w:numPr><w:ilvl w:val="([0-9]+)"/>}', 
                                   create_function('$matches',
                                                   '$bullets = array("* ", "o ", "- ", "* ", "o ", "- ", "* ", "o ");
                                                    return str_repeat("  ", $matches[1]) . $bullets[$matches[1]];'), 
                                   $content);

  // Capitalize allcaps text
  $content = preg_replace_callback('{<w:caps/>.*?(<w:t>|<w:t [^>]+>)(.*?)</w:t>}', 
                                   create_function('$matches',
                                                   'return strtoupper($matches[2]);'),
                                   $content);

  // Center / right justify text
  $content = preg_replace_callback('{<w:pPr><w:jc w:val="center"/></w:pPr>(<w:r>|<w:r [^>]+>)(<w:t>|<w:t [^>]+>)(.*?)</w:t></w:r>}',
                                   create_function('$matches',
                                                   'return OPENXML_CenterJustify($matches[3]);'),
                                   $content);
  $content = preg_replace_callback('{<w:pPr><w:jc w:val="right"/></w:pPr>(<w:r>|<w:r [^>]+>)(<w:t>|<w:t [^>]+>)(.*?)</w:t></w:r>}',
                                   create_function('$matches',
                                                   'return OPENXML_RightJustify($matches[3]);'),
                                   $content);

  // Find the url's of hyperlinks refer to (should be in $docrels) and add it to the converted text
  global $OPENXML_Xml2Text_docrels;
  $OPENXML_Xml2Text_docrels = $docrels;
  $content = preg_replace_callback('{<w:hyperlink r:id="(.*?)".*?>(.*?)</w:hyperlink>}', 
                                   create_function('$matches', 
                                                   'global $OPENXML_Xml2Text_docrels;
                                                    $url = $OPENXML_Xml2Text_docrels["hyperlink#".$matches[1]];
                                                    return $matches[2] . " [$url]";
                                                   '),
                                   $content);

  // Get rid of completely meaningless picture posOffset (why oh why did they use PCDATA for that, when an attribute would have been so much simpler?)
  $content = preg_replace('{<wp:posOffset>.*?</wp:posOffset>}', '', $content);
  // Deal with OpenIMS macrobuttons
  $content = preg_replace('{(<w:instrText>|<w:instrText [^>]+>)MACROBUTTON OPENIMS_FIELD_[a-zA-Z0-9\-_]+ (.*?)</w:instrText>}', '\2', $content);
  // Delete all other types of instrText (to get rid of "PAGEREF _Toc239065560 \h" stuff from the table of contents)
  $content = preg_replace('{(<w:instrText>|<w:instrText [^>]+>).*?</w:instrText>}', '', $content);

  // Remove all tags
  $content = preg_replace('{<.*?>}', '', $content);

  N_Debug("OPENXML_Xml2Text: converted = <pre>" . htmlentities($content) . "</pre>");

  return $content;


}

function OPENXML_CenterJustify($text) {
  $len = strlen($text);
  if ($len < 80 - 1) {
    return str_repeat(" ", (80 - $len) / 2) . $text;
  } else {
    return $text;
  }
}

function OPENXML_RightJustify($text) {
  $len = strlen($text);
  if ($len < 80) {
    return str_repeat(" ", (80 - $len)) . $text;
  } else {
    return $text;
  }
}


/******* METADATA SUBSTITUTION *********/

/*
  Updates the metadata in a document.
  @param $file, the path to the file.
  @param $arr, an associative array with the data to set as metadata.
  @return true on success, false on failure.
*/
function OPENXML_UpdateMetaData($file, $arr) {
  
  if(!OPENXML_IsSupported()) { return false; }
  uuse("openxml2");  

  $file = N_CleanPath($file);
  $temp = TMP_Directory()."/";
  
  $pos = strrpos($file, ".");
  
  switch(strtolower(substr($file, $pos === false ? 0 : $pos + 1))) {
    case "docx":
    case "docm":
      $doc = new OpenIMSWordDocument($file, $temp);
      return $doc->setOpenIMSMetaData(OpenIMSMetaDataXml::toDOMDocument($arr));
    case "xlsm":
      $doc = new OpenIMSExcelSheet($file, $temp);
      return $doc->setOpenIMSMetaData($arr);
    default:
      return false;
  }
}

/*
  Reads the embedded metadata from an OpenXml archive.
  @param $file, the file to open
  @return An array containing the metadata or an empty array if there isn't any.
*/
function OPENXML_ReadMetaData($file) {

  if(!OPENXML_IsSupported()) { return array(); } // not supported so can't read the data
  uuse("openxml2");

  $file = N_CleanPath($file);
  $temp = TMP_Directory()."/";
  
  $pos = strrpos($file, ".");
  
  switch(strtolower(substr($file, $pos === false ? 0 : $pos + 1))) {
    case "docx":
    case "docm":
      $doc = new OpenIMSWordDocument($file, $temp);
      if(($dom = $doc->getOpenIMSMetaData()) !== false) {
        return OpenIMSMetaDataXml::toArray($dom);
      }
      else {
        return array();
      }
    case "xlsm":
      $doc = new OpenIMSExcelSheet($file, $temp);
      return($doc->getOpenIMSMetaData());
    default:
      return array();
  }
}


/*
  Checks if system supports OpenXML metadata substitution
  @return true if the DOM library is installad / enabled, the system php version >= 5 and zip and unzip executables are set.
*/
function OPENXML_IsSupported() {
  global $myconfig;

  if (!class_exists("DOMDocument")) return false;
  
  $domsupport = version_compare(phpversion(), "5.0.0") >= 0;
  $zip = isset($myconfig) && isset($myconfig["zip"]) && isset($myconfig["unzip"]) && $myconfig["windowsooxmlhandling"] == "yes"; // TODO: maybe rename myconfig-setting
  return $domsupport && $zip;
}

/*
  Checks if system can handle this type of file.
  @param $file, the filename or extension.
  @return true if system can work with $file, false if not.
*/
function OPENXML_IsOpenXml($file) {

  $pos = strrpos($file, ".");
  
  switch(strtolower(substr($file, $pos === false ? 0: $pos + 1))) {
    case "docx":
    case "docm":
    case "xlsm":
      return true;

    default:
      return false;
  }
}

/******* OLD APPROACH TO METADATA SUBSTITUTION (based on regular expressions, argh!) ***********/
// TODO: implement ReadMetadata so that history versions can be updated
// TODO: test if fieldname in field value (e.g. name = [[[version]]] works
// TODO: prevent refucktoring of entire documents, do as little as possible
// TODO: <<< fields and wrong tags

function OPENXML_ReplaceMetadataDocx($filename, $data) {
  N_Debug("OPENXML_ReplaceMetadataDocx filename = $filename, data = " . print_r($data, 1));

  global $myconfig;

  uuse("tmp");
  $tmpdir = TMP_Directory();
  N_Debug("OPENXML_ReplaceMetadataDocx: tmpdir = $tmpdir");

  if (getenv("SCRIPT_NAME") == "/nkit/gate.php") {
    N_CopyFile($filename . ".imsbakg", $filename); // make backup copy (should not be copied by transfer agent, because transfer agent uses single file mode for docx)
  } else {
    N_CopyFile($filename . ".imsbako", $filename); // make backup copy (should not be copied by transfer agent, because transfer agent uses single file mode for docx)
  }

  $command = $myconfig["unzip"] . ' ' . escapeshellarg(N_CleanPath($filename)) . ' -d ' . $tmpdir;
  exec($command);

  $files = OPENXML_InterestingFilesDocx($tmpdir);
  foreach ($files as $file) {
    if (N_FileSize($tmpdir . "/" . $file) < 10000000) { // protect us against large files (preg_match can take a long time...)
      N_Debug("OPENXML_ReplaceMetadataDocx: processing file $file");
      $xml = N_ReadFile($tmpdir . "/" . $file); 
      // $xml will contain UTF8 data throughout this loop

      N_Debug("OPENXML_ReplaceMetadataDocx: xml (before) = <pre>");// . htmlentities($xml) . "</pre>");

      // Preprocessing. If there are any XML-tags inside [[[fields]]], move them outside the field. Do this multiple times, in case
      // a field is being interrupted by XML-tags at multiple locations.
      for ($i = 0; $i < 1000; $i++) {
        $xml = preg_replace('{\[\[\[([^\]]*?)((?:<[^>]+>)+)([^\]<]*)\]\]\]}', '[[[\1\3]]]\2', $oldxml=$xml); // super enhanced edition
        if ($oldxml==$xml) break;
      }
      N_Debug("OPENXML_ReplaceMetadataDocx: xml (after preprocessing) = <pre>");// . htmlentities($xml) . "</pre>");

      foreach ($data as $dataname => $datavalue) {
        //N_Debug("OPENXML_ReplaceMetadataDocx: $dataname " . strlen($datavalue) . " " . htmlspecialchars($datavalue));

        $dataname = substr($dataname, 4); // remove the "set_" part
        $datavalue = htmlspecialchars(N_HTML2UTF($datavalue)); // non-iso UTF8 doesn't actually work, I can't help it, $data is already broken when it arrives in this funciton
        $datavalue = str_replace('\\', '&#92;', $datavalue); // replace backslash, because otherwise it might be interpreted as a backreference when $datavalue is used in $replacement
        $datavalue = str_replace('$', '&#36;', $datavalue);  // same problem with $
        $datavalue = str_replace('[', '&#91;', $datavalue);  // same problem with [
        $datavalue = str_replace(']', '&#93;', $datavalue);  // same problem with ]

        // $datavalue1 is for use inside macrobuttons. 
        // Remove all newlines from $datavalue1 (they show up as spaces anyway. Although Word is quite happy to do this
        // conversion for us, if we let it, it will produce XML inside the macrobutton that is more complicated that we can handle...
        $datavalue1 = str_replace("\r\n", ' ', $datavalue); 
        $datavalue1 = str_replace("\r", ' ', $datavalue1);
        $datavalue1 = str_replace("\n", ' ', $datavalue1);     
        // $datavalue2 is for plain content. It can be multiline, but to achieve that, we have to change ascii newlines into Word "breaks".
        // Note: completely empty lines don't work, because SEARCH_Html2Text (called by IMS_SignalDataChange) eats consecutive newlines.
        $datavalue2 = str_replace("\r\n", '<w:br/>', $datavalue); 
        $datavalue2 = str_replace("\r", '<w:br/>', $datavalue2);
        $datavalue2 = str_replace("\n", '<w:br/>', $datavalue2);
 

        //N_Debug("OPENXML_ReplaceMetadataDocx: $dataname " . strlen($datavalue) . " " . htmlspecialchars($datavalue));

        // Replace the content of macrobuttons
        $xml = preg_replace("{(<w:instrText>|<w:instrText [^>]+>)(MACROBUTTON OPENIMS_FIELD_" . preg_quote($dataname). " )(.*?)(</w:instrText>)}", 
                            '${1}${2}'.$datavalue1.'${4}',
                            $xml);


        // Replace [[[fields]]] (first time, produces a macrobutton). If there is a space in front of the field, change the space into a "preserved space" 
        $xml = preg_replace("{ \[\[\[" . preg_quote($dataname) . "\]\]\]}",
                            '<w:t xml:space="preserve"> </w:t><w:fldChar w:fldCharType="begin"/><w:instrText>MACROBUTTON OPENIMS_FIELD_' . $dataname . ' ' . $datavalue1 . '</w:instrText><w:fldChar w:fldCharType="end"/>', 
                            $xml);
        $xml = preg_replace("{\[\[\[" . preg_quote($dataname) . "\]\]\]}",
                            '<w:fldChar w:fldCharType="begin"/><w:instrText>MACROBUTTON OPENIMS_FIELD_' . $dataname . ' ' . $datavalue1 . '</w:instrText><w:fldChar w:fldCharType="end"/>', 
                            $xml);
        // I knew OpenXML was approved on other things than its merits, but I had no idea it was so terrible. Just look at the XML for a macrobutton field:
        // - Three elements (fldChar begin, instrText and fldEnd) that clearly constitute a single thing (a button), 
        //   not in any way held together in the XML (no container element). 
        // - XML elements that cause changes in the state of the application, where the scope of these changes has no relation 
        //   at all to the structure of the XML elements.
        // - The entire specification of the macrobutton (the fact that it is a macrobutton, the name of the button, and the value) is 
        //   whitespace separated PCDATA, there is no XML structure at all.
        // - There is non-content in the PCDATA, and we need special exceptions to docx2text to suppress this non-content from being shown.

        // Replace <<<fields>>> (one-time only, no macrobutton)
        $xml = preg_replace("{&lt;&lt;&lt;" . preg_quote($dataname) . "&gt;&gt;&gt;}",
                            $datavalue2, 
                            $xml);

      }

      N_WriteFile($tmpdir . "/" . $file, $xml); // make conditional (only if something was changed...)

      N_Debug("OPENXML_ReplaceMetadataDocx: xml (after) = <pre>"); // . htmlentities($xml) . "</pre>");

    } else {
      N_Debug("OPENXML_ReplaceMetadataDocx: skipping file $file (too large)");
    }

  }

  $olddir = getcwd();
  chdir($tmpdir);
  $command = $myconfig["zip"] . ' -r document.docx *';
  N_Debug("OPENXML_ReplaceMetadataDocx: command = $command");
  exec($command);
  chdir($olddir);
  N_CopyFile($filename, $tmpdir . "/document.docx");
}

function OPENXML_ReadMetadataDocx($filename) {
  /* TODO: find a *cheap* way to implement this (without parsing document.xml), so that history versions can be updated */
  N_Debug("OPENXML_ReadMetadataDocx filename = $filename");
}


/* Test code docx2text

$debug = "OPENXML";
uuse("openxml");

$sgn = IMS_SuperGroupName();
//$id = "f3cebb3dca5e594dd95e3e0293cf43b1";
$id = "c69c559a1028d3bea465f1d882b876aa";
$in = FILES_Filelocation ($sgn, $id);
$out = "html::/tmp/out.txt";

OPENXML_Docx2Text($in, $out);
echo "<pre>" . htmlspecialchars(N_ReadFile($out)) . "</pre>";


*/

/* Test code marker

// If necessary, temporarily disable the call from metabase.php to IMS_SignalDataChange
$debug = "OPENXML";
uuse("openxml");

$sgn = IMS_SuperGroupName();
$id = "910dd752e1fc3367bb6e07574133d8a8";

IMS_SignalDatachange($sgn, $id);

*/


?>