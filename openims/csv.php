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



include (getenv ("DOCUMENT_ROOT")."/nkit/nkit.php");
uuse("ims");
uuse("diff");
uuse("forms");
uuse("tables");

global $myconfig;
$sgn = IMS_SuperGroupName();
if (($myconfig[$sgn]["ml"]["metadatalevel"] && $myconfig[$sgn]["ml"]["metadatafiltering"] != "no") || 
     $myconfig[$sgn]["ml"]["metadatafiltering"] == "yes") {
  N_SetOutputFilter('FORMS_ML_Filter');
}

$supergroupname = $_GET["supergroupname"];
IMS_SetSuperGroupName($supergroupname);
 
function csv ($s)
{
  return str_replace (";", " ", $s);
}

if ($mode=="bpmsexport") {

  if (!SHIELD_HasProcessRight ($supergroupname, $theprocess, "export")) SHIELD_Unauthorized ();
  if ($csv=="nl") $c=";"; else $c=","; 

  Header ("Content-Type: application/vnd.ms-excel; name='excel'");
  Header ("Content-Disposition: attachment; filename=data.csv");
  $keys = MB_Query ("process_".$supergroupname."_cases_".$theprocess, '', '$record["visualid"]');
  foreach ($keys as $key => $id)
  {
    $object = MB_Load ("process_".$supergroupname."_cases_".$theprocess, $key);
    foreach ($object["data"] as $name => $value) {
      $fieldlist[$name] = "x";
    }
  }
  echo '"ID"';
  foreach ($fieldlist as $name => $dummy) {
    echo $c.'"'.$name.'"';
  }
  echo chr(13).chr(10);
  foreach ($keys as $key => $id)
  {
    $object = MB_Load ("process_".$supergroupname."_cases_".$theprocess, $key);
    echo '"'.$id.'"';
    foreach ($fieldlist as $name => $dummy) {
      echo $c.'"'.str_replace ('"','""',$object["data"][$name]).'"';
    }
    echo chr(13).chr(10);
  }
  
} else if ($mode=="cmsformbpmsexport" && $object_id && SHIELD_HasObjectRight ($supergroupname, $object_id, "edit")) {
  global $fromdate, $todate, $delim;
  if (!$delim) $delim = ",";

  // if you ever want to add encoding, see MLUIF_DownloadCsvForm

  uuse("bpms");
  $process_id = BPMS_GetProcessIdForCMSForm($supergroupname, $object_id);
  if ($process_id) {
    $table = "process_".$supergroupname."_cases_".$process_id;

    $qspecs = array();
    if ($fromdate || $todate) {
      if (!$fromdate) $fromdate = 0;
      if (!$todate) $todate = 2147483647;
      $qspecs["range"] = array('QRY_BPMS_Created_v1($record)', $fromdate, $todate);
    }
    $qspecs["slice"] = "count";
    $count = MB_TurboMultiQuery($table, $qspecs);

    $limit = 20000; // Dont go over the Excel limit of 65000
    if ($count > $limit) {
      echo ML("Meer dan %1 resultaten; probeer het opnieuw met een kleinere datum range", "More than %1 results; please try again using a limited date range", $limit);
      N_Exit();
      die();
    }

    Header ("Content-Type: application/vnd.ms-excel; name='excel'");
    Header ("Content-Disposition: attachment; filename=data.csv");
    $enclosure = '"';

    $qspecs["sort"] = 'QRY_BPMS_Created_v1($record)';
    $qspecs["value"] = '$record';
    unset($qspecs["slice"]);
    $results = MB_TurboMultiQuery($table, $qspecs);

    // Check all records to determine fields (possible performance shortcut would be to parse page.html, but fields deleted from the form would no longer be available)
    $fields = array();
    foreach ($results as $key => $record) {
      foreach ($record["data"] as $fieldname => $dummy) {
        $fields[$fieldname] = "x";
      }
    }
    $allfields = MB_Load("ims_fields", $supergroupname);

    // Show fields
    foreach ($fields as $fieldname => $dummy) {
      $title = $allfields[$fieldname]["title"];
      if (!$title) $title = $fieldname;
      $cols[] = $title;
    }
    $cols[] = ML("Ingevuld", "Submitted");
    echo N_sputcsv($cols, $delim, $enclosure, chr(13).chr(10));

    // Show records
    foreach ($results as $key => $record) {
      $cols = array();
      foreach ($fields as $fieldname => $dummy) {
        $cols[] = FORMS_ShowValue($record["data"][$fieldname], $allfields[$fieldname]);
      }
      $cols[] = N_VisualDate(QRY_BPMS_Created_v1($record), true, true);
      echo N_sputcsv($cols, $delim, $enclosure, chr(13).chr(10));
    }

  }

} else if ($object_id && SHIELD_HasObjectRight ($supergroupname, $object_id, "edit")) {

  Header ("Content-Type: application/vnd.ms-excel; name='excel'");
  Header ("Content-Disposition: attachment; filename=data.csv");

  for ($i=1; $i<=10000; $i++) {
    if ($storage = MB_Load ("ims_".$supergroupname."_objects_objectdata", $object_id."__$i")) {
      reset($storage);
      while (list ($guid, $record)=each($storage)) {
        if (is_array($record)) reset($record);
        if (is_array($record)) while (list($field)=each($record)){
          $fields[$field]="x";
        }
      }
    } else {
      break;
    }
  }
  if ($storage = MB_Load ("ims_".$supergroupname."_objects_objectdata", $object_id)) {
    reset($storage);
    while (list ($guid, $record)=each($storage)) {
      if (is_array($record)) reset($record);
      if (is_array($record)) while (list($field)=each($record)){
        $fields[$field]="x";
      }
    }
  }

  $first = true;
  for ($i=1; $i<=10000; $i++) {
    if (MB_Load ("ims_".$supergroupname."_objects_objectdata", $object_id."__$i")) {
      CSV_DumpCSV ($fields, $supergroupname, $object_id."__$i", $first);
      $first = false;
    } else {
      break;
    }
  }
  CSV_DumpCSV ($fields, $supergroupname, $object_id, $first);
} else if ($encspec) {
  $specs = SHIELD_Decode($encspec);

  if ($specs["code"]) {
    $rows = N_Eval($specs["code"], array("input" => $specs["input"]), "data");
  } else {
    $rows = $specs["data"];
  }
  $delim = $specs["delimiter"];
  if (!$delim) $delim = ";";
  $enclosure = $specs["enclosure"];
  if (!$enclosure) $enclosure = '"';
  $linebreak = $specs["linebreak"];
  if (!$linebreak) $linebreak = "\r\n";

  $encoding = $specs["encoding"];

  if ($specs["skipemptycolumns"]) {
    $used_columns = array();
    foreach (array_values($rows) as $rownr => $row) {
      if ($rownr == 0) continue;
      foreach ($row as $columnnr => $column) {
        if ($column) $used_columns[$columnnr] = true;
      }
    }
  }

  $filename = $specs["filename"];
  if (!$filename) $filename = "export.csv";
  header('Content-type: text/csv');
  header('Content-disposition: attachment;filename='.$filename);

  foreach ($rows as $row) {
    if ($encoding == "utf") {
      foreach ($row as $columnnr => $column) {
        $row[$columnnr] = N_Html2Utf($column);
      }
    }

    if ($specs["skipemptycolumns"]) {
      $shortrow = array();
      foreach ($row as $columnnr => $column) {
        if ($used_columns[$columnnr]) $shortrow[] = $column;
      }
      echo N_sputcsv($shortrow, $delim, $enclosure, $linebreak);
    } else {
      echo N_sputcsv($row, $delim, $enclosure, $linebreak);
    }
  }
}

N_Exit();

function CSV_DumpCSV ($fields, $supergroupname, $object_id, $showheaders) {
  $allfields = MB_Ref("ims_fields", $supergroupname);
  global $csv;
  if ($csv=="nl") $c=";"; else $c=","; 

  $storage = &MB_Ref ("ims_".$supergroupname."_objects_objectdata", $object_id);
  if (is_array($storage)) {
    $first=true;
    reset ($fields);
    if ($showheaders) {
      while (list ($field)=each($fields)) {
        if (!$first) echo $c;
        echo '"'.str_replace ('"','""',$field).'"';
        $first=false;
      }
      echo chr(13).chr(10);
    }

    reset($storage);
    while (list ($guid, $record)=each($storage)) {
      $first=true;
      reset ($fields);
      while (list ($field)=each($fields)) {
        if (!$first) echo $c;

        $value = $record[$field];
        $value = FORMS_ShowValue ($value, $field, "", "");
        $value = N_RemoveUnicode($value);
        echo '"'.str_replace ('"', '""', str_replace (chr(10), " ", str_replace (chr(13), " ", $value))).'"';
        $first=false;
      }
      echo chr(13).chr(10);
    }
  }    
}

?>