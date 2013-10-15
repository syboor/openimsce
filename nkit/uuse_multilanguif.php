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



uuse("multilang");

// When scanning the source code, add these to the top of the multilang database.
// ML(" 0001 [test, NIET VERTALEN] grave: &agrave;&egrave;&igrave;&ograve;&ugrave; aigu: &aacute;&eacute;&iacute;&oacute;&uacute; trema: &auml;&euml;&iuml;&ouml;&uuml;", " 0001 [test, DO NOT TRANSLATE] grave: &agrave;&egrave;&igrave;&ograve;&ugrave; acute: &aacute;&eacute;&iacute;&oacute;&uacute; diaeresis: &auml;&euml;&iuml;&ouml;&uuml;");
// ML(" 0002 [test, NIET VERTALEN] grieks: &#8125;&#917;&#925; &#8125;&#913;&#929;&#935;&#919; &#7968;&#957; &#8001; &#955;&#959;&#947;&#959;&#962;", " 0001 [test, DO NOT TRANSLATE] greek: &#8125;&#917;&#925; &#8125;&#913;&#929;&#935;&#919; &#7968;&#957; &#8001; &#955;&#959;&#947;&#959;&#962;");


function MLUIF_AdminBlock() {
  global $myconfig;

  echo "<p>";
  //echo ML("Op dit systeem zijn de volgende talen beschikbaar", "On this system, the following languages are available") . ": <b>" . implode(", ", array_filter(N_Array_merge($myconfig[IMS_SuperGroupName()]["ml"]["languages"], $myconfig[IMS_SuperGroupName()]["ml"]["sitelanguage"]))) . ".</b><br/>";   //LF: Vague and open to misinterpretation now that product languages and site languages have become independent.

  $modlanguages = ML_ModifiableLanguages();
  if (count($modlanguages)) {
    echo ML("De database voor de volgende talen kan aangepast worden", "The database for these languages can be modified") . ": <b>" . implode(", ", $modlanguages) . ".</b><br/>";
  }

  $nmodified = $nmissing = array();
  $records = ML_LoadDatabase();
  $intrecords = ML_LoadDatabase(true);
  foreach ($records as $key => $record) {
    foreach ($modlanguages as $lang) {
      $intrecord = $intrecords[$key];
      if ($lang == "nl" || $lang == "en") {
        if ($record["trans"][$lang] && $intrecord["orig"][$lang] && ($record["trans"][$lang] != $intrecord["orig"][$lang])) {
          $nmodified[$lang]++;
        }
      } else {
        if (!$record["trans"][$lang] && !$intrecord["trans"][$lang]) {
          $nmissing[$lang]++;
        }
        if ($record["trans"][$lang] && $intrecord["trans"][$lang] && ($record["trans"][$lang] != $intrecord["trans"][$lang])) {
          $nmodified[$lang]++;
        }
      }
    }
  }
  echo "</p><p>";

  echo ML("De meertaligheidsdatabase heeft %1 records.", "The multilingual database has %1 records.", "<b>" . count($records) . "</b>") . "<br/>";
  foreach ($modlanguages as $lang) {
    if ($nmissing[$lang]) {
      echo ML("De taal %1 heeft %2 records zonder vertaling.", "The language %1 has %2 records with no translation.", "<b>$lang</b>", "<b>" .$nmissing[$lang] . "</b>") . "<br/>";
    }
    if ($nmodified[$lang]) {
      echo ML("De taal %1 heeft %2 records die aangepast zijn t.o.v. de productdatabase.", "The language %1 has %2 records that have been modified from the product database.", "<b>$lang</b>", "<b>" .$nmodified[$lang] . "</b>") . "<br/>";
    }
  }
  echo "</p>";

  echo '
    <style>
    form { margin: 0; padding: 0; }
    input { width: 11em; }
    </style>
  ';

  T_Start("", array("noheader" => true));

  $form = array();
  $oktext = ML("Database bijwerken", "Update database");
  if (count($records) == 0) $oktext = ML("Database aanmaken", "Create database");
  $form["postcode"] = 'ML_CleanDatabase(); ML_UpdateDatabase();';
  $form["formtemplate"] = "[[[OK:$oktext]]]";
  echo FORMS_GenerateSuperForm($form);
  T_Next();
  echo ML("Doorzoek OpenIMS en de Inrichting op teksten die vertaald kunnen worden, en voeg deze teksten toe aan de database", "Search OpenIMS and the Configuration for texts that can be translated, and add these texts to the database");
  T_NewRow();

  $url = FORMS_URL(MLUIF_DownloadCsvForm());
  // Turn the hyperlink into a button. $url is already javascript anyway.
  echo '<input type="button" name="download" value="'.ML("Download CSV", "Download CSV").'" onclick="'.$url.'">';
  T_Next();
  echo ML("Download de database in CSV formaat", "Download the database in CSV format");
  T_NewRow();

  $url = FORMS_URL(MLUIF_UploadCsvForm());
  echo '<input type="button" name="upload" value="'.ML("Upload CSV", "Upload CSV").'" onclick="'.$url.'">';
  T_Next();
  echo ML("Upload de database in CSV formaat", "Upload the database in CSV format");
  T_NewRow();

  if ($myconfig[IMS_SuperGroupName()]["ml"]["internal"] == "yes") {
    $alllanguages = array_merge(array("nl", "en"), ML_ModifiableLanguages(true));
    $nmissing = array();
    echo "<p>";
    $intrecords = ML_LoadDatabase(true);
    foreach ($intrecords as $key => $record) {
      foreach ($alllanguages as $lang) {
        if (($lang == "en" || $lang == "nl") && !$record["orig"][$lang]) $nmissing[$lang]++;
        if (($lang != "en" && $lang != "nl") && !$record["trans"][$lang]) $nmissing[$lang]++;
      }
    }
    echo "The core multilingual database has <b>" . count($intrecords) . "</b> records.<br/>";
    foreach ($alllanguages as $lang) {
      if ($nmissing[$lang]) {
        echo "In the core database, the language <b>$lang</b> has <b>" . $nmissing[$lang] . "</b> records with no translation.<br/>";
      }
    }
    echo "</p>";

    $form = array();
    $oktext = "Update core db";
    if (count($intrecords) == 0) $oktext = "Create core db";
    $form["postcode"] = 'ML_CleanDatabase(true); ML_UpdateDatabase(true);';
    $form["formtemplate"] = "[[[OK:$oktext]]]";
    echo FORMS_GenerateSuperForm($form);
    T_NewRow();

    $url = FORMS_URL(MLUIF_DownloadCsvForm(true));
    // Turn the hyperlink into a button. $url is already javascript anyway.
    echo '<input type="button" name="download" value="Download core CSV" onclick="'.$url.'">';
    T_NewRow();

    $url = FORMS_URL(MLUIF_UploadCsvForm(true));
    // Turn the hyperlink into a button. $url is already javascript anyway.
    echo '<input type="button" name="upload" value="Upload core CSV" onclick="'.$url.'">';
    T_NewRow();
  }

  TE_End();
  
?>
<br><br>
<b>Translation guide</b>
<p>Please learn and test the complete download-upload-import process while making only a few minor modifications, before attempting a full translation.</p>
<ol>
  <li>Create or update the database</li>
  <li>Download the database in CSV format. Choose which language you want to translate into, and use the correct settings for your CSV editor (e.g. Excel). If you are translating into a language with a non-latin alphabet, you should use UTF-8.</li>
  <li>Open the database in your CSV editor. For some CSV editors, you might have to repeat the settings specified in step 2 while opening the database.</li>
  <li>Check the row that starts with "0001" (near the top). You should see the five vowels, each with accent grave, accent acute/aigu, and diaeresis. <b>Do not continue if these accents are not shown correctly.</b><li>
  <li>If you are using ISO-8859-1, <b>ignore this step</b>. If you are using UTF-8, check the row that starts with "0002". You should see the text <i>EN ARCH&Ecirc; &ecirc;n ho logos</i> in the Greek alphabet. Some letters may have been replaced with question marks or rectangles, depending on your font support. This is OK if you do not need the missing letters. However, if you see weird letters or if you see ampersands (&amp;) and numbers, <b>do not continue</b></li>
  <li>Add or modify the text in the (translation) or (custom) column. You may also modify the #STATUS column. Do not modify any other columns.</li>
  <li>Save the CSV file.</li>
  <li>Upload the CSV file to OpenIMS. Ensure you specify the correct settings for your CSV editor.</li>
  <li>OpenIMS will show you the first and last ten rows of the uploaded CSV. Check this preview, esp. check the accented vowels and Greek characters from step 4 and 5. If everything looks correct, confirm that you wish to proceed with importing the data into OpenIMS.</li>
</ol>

<?

}

function MLUIF_UploadCsvForm($internal = false) {
  $form = array();
  $form["input"]["internal"] = $internal;
  $form["metaspec"]["fields"]["upload"]["type"] = "file";
  $form["metaspec"]["fields"]["upload"]["required"] = true;
  $form["metaspec"]["fields"]["upload"]["title"] = ML("Upload", "Upload");
  $form["metaspec"]["fields"]["delim"]["type"] = "list";
  $form["metaspec"]["fields"]["delim"]["title"] = ML("Scheidingsteken", "Delimiter");
  $form["metaspec"]["fields"]["delim"]["method"] = "radiover";
  $form["metaspec"]["fields"]["delim"]["values"][", (" . ML("standaard","standard") . ")"] = ",";
  $form["metaspec"]["fields"]["delim"]["values"]["; (" . ML("Excel met Nederlandse regio-instellingen", "Excel with Dutch regional settings") . ")"] = ";";
  $form["metaspec"]["fields"]["delim"]["default"] = ",";
  $form["metaspec"]["fields"]["encoding"]["type"] = "list";
  $form["metaspec"]["fields"]["encoding"]["title"] = ML("Codering", "Encoding");
  $form["metaspec"]["fields"]["encoding"]["method"] = "radiover";
  $form["metaspec"]["fields"]["encoding"]["values"][ML("ISO-8859-1 (werkt met Excel)", "ISO-8859-1 (werkt met Excel)")] = "iso";
  $form["metaspec"]["fields"]["encoding"]["values"][ML("UTF-8 (alle talen mogelijk, vereist een UTF-8 geschikte editor)","UTF-8 (all languages possible, requires a UTF-8 suitable editor)")] = "utf";
  $form["metaspec"]["fields"]["encoding"]["default"] = "iso";
  $form["formtemplate"] = '
    <style>
    body, div, p, th, td, li, dd {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 13px;
    }
    </style>
    <table>
    ' . ($internal ? '<tr><td colspan=2><b>INTERNAL</b></td></tr>' : '') . '
    <tr><td>{{{upload}}}</td><td>[[[upload]]]</td></tr>
    <tr><td>{{{delim}}}</td><td>[[[delim]]]</td></tr>
    <tr><td>{{{encoding}}}</td><td>[[[encoding]]]</td></tr>
    <tr><td colspan=2>&nbsp;</td></tr>
    <tr><td colspan=2><center>[[[OK]]]</center></tr></tr>
    </table>
  ';
  $form["postcode"] = '
    uuse("tmp");
    uuse("multilanguif");
    $tmpdir = TMP_Directory();
    N_WriteFile($tmpdir . "/multilang.csv", $files["upload"]["content"]);
    $form2 = MLUIF_UploadCsvConfirmForm($tmpdir . "/multilang.csv", $input["internal"], $data["delim"], $data["encoding"]);

    N_Redirect(FORMS_URL($form2));
  ';
  return $form;
}

function MLUIF_UploadCsvConfirmForm($file, $internal, $delim, $encoding) {
  $form = array();
  $form["input"]["file"] = $file;
  $form["input"]["delim"] = $delim;
  $form["input"]["encoding"] = $encoding;
  $form["input"]["internal"] = $internal;
  $form["metaspec"]["fields"]["sure"]["type"] = "list";
  $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
  $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
  $form["formtemplate"] = '
    <style>
    body, div, p, th, td, li, dd {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 13px;
    }
    </style>
    ' . ($internal ? '<p><b>INTERNAL</b></p>' : '') . '
    <div>
       xxyyzz
    </div>
    Weet u zeker dat u door wilt gaan met importeren? [[[sure]]] <br/>
    [[[OK]]] [[[Cancel]]]
  ';
  $form["precode"] = '
    T_Start();
    ML_ImportCsv($input["file"], true, $input["internal"], $input["delim"], $input["encoding"]);
    $formtemplate = str_replace("xxyyzz", TS_End(), $formtemplate);
  ';
  $form["postcode"] = '
    if ($data["sure"] == "yes") {
      ML_ImportCsv($input["file"], false, $input["internal"], $input["delim"], $input["encoding"]);
      $form2["formtemplate"] = \'
        <style>
        body, div, p, th, td, li, dd {
          font-family: Arial, Helvetica, sans-serif;
          font-size: 13px;
        }
        </style>
        Het bestand is ge&iuml;mporteerd. [[[Cancel:OK]]]
      \';
      N_Redirect(FORMS_URL($form2));
    }
  ';
  return $form;
}

function MLUIF_DownloadCsvForm($internal = false) {
  global $myconfig;
  $form = array();
  $form["input"]["internal"] = $internal;
  $form["metaspec"]["fields"]["language"]["type"] = "list";
  $form["metaspec"]["fields"]["language"]["title"] = ML("Taal", "Language");
  foreach (ML_ModifiableLanguages($internal) as $lang) $form["metaspec"]["fields"]["language"]["values"][strtoupper($lang)] = $lang;
  $form["metaspec"]["fields"]["all"]["type"] = "list";
  $form["metaspec"]["fields"]["all"]["title"] = ML("Welke records", "Which records");
  $form["metaspec"]["fields"]["all"]["method"] = "radiover";
  $form["metaspec"]["fields"]["all"]["values"][ML("Records zonder vertaling in the productdatabase en records met aangepaste vertalingen", "Records with no translation in the product database and records with modified translations")] = "";
  $form["metaspec"]["fields"]["all"]["values"][ML("Alle records", "All records")] = "yes";
  $form["metaspec"]["fields"]["delim"]["type"] = "list";
  $form["metaspec"]["fields"]["delim"]["title"] = ML("Scheidingsteken", "Delimiter");
  $form["metaspec"]["fields"]["delim"]["method"] = "radiover";
  $form["metaspec"]["fields"]["delim"]["values"][", (" . ML("standaard","standard") . ")"] = ",";
  $form["metaspec"]["fields"]["delim"]["values"]["; (" . ML("Excel met Nederlandse regio-instellingen", "Excel with Dutch regional settings") . ")"] = ";";
  $form["metaspec"]["fields"]["delim"]["default"] = ",";
  $form["metaspec"]["fields"]["encoding"]["type"] = "list";
  $form["metaspec"]["fields"]["encoding"]["title"] = ML("Codering", "Encoding");
  $form["metaspec"]["fields"]["encoding"]["method"] = "radiover";
  $form["metaspec"]["fields"]["encoding"]["values"][ML("ISO-8859-1 (werkt met Excel)", "ISO-8859-1 (werkt met Excel)")] = "iso";
  $form["metaspec"]["fields"]["encoding"]["values"][ML("UTF-8 (alle talen mogelijk, vereist een UTF-8 geschikte editor)","UTF-8 (all languages possible, requires a UTF-8 suitable editor)")] = "utf";
  $form["metaspec"]["fields"]["encoding"]["default"] = "iso";
  $form["formtemplate"] = '
    <style>
    body, div, p, th, td, li, dd {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 13px;
    }
    </style>
    <table>
    ' . ($internal ? '<tr><td colspan=2><b>INTERNAL</b></td></tr>' : '') . '
    <tr><td>{{{language}}}</td><td>[[[language]]]</td></tr>
    ' . ($internal ? '' : '<tr><td>{{{all}}}</td><td>[[[all]]]</td></tr>') . '
    <tr><td>{{{delim}}}</td><td>[[[delim]]]</td></tr>
    <tr><td>{{{encoding}}}</td><td>[[[encoding]]]</td></tr>
    <tr><td colspan=2>&nbsp;</td></tr>
    <tr><td colspan=2><center>[[[OK]]]</center></tr></tr>
    </table>
  ';
  $form["postcode"] = '
    $url = ML_ExportCsv($data["language"], $input["internal"], ($data["all"] == "yes"), $data["delim"], $data["encoding"]);
    $form2["formtemplate"] = "
      <style>
      body, div, p, th, td, li, dd {
        font-family: Arial, Helvetica, sans-serif;
        font-size: 13px;
      }
      </style>
      <p><a href=\"$url\">Download het bestand</a></p>
      <p><center>[[[Cancel:".ML("Sluiten", "Close")."]]]</center></p>";
    N_Redirect(FORMS_URL($form2));
  ';
  return $form;
}


?>