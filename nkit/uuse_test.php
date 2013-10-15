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



uuse ("tables");
uuse ("java");
uuse ("s2");
uuse ("dyna");
uuse ("sphinx");
uuse ("sphinx2");

function TEST_AutoComplete ()
{
  $form["metaspec"]["fields"] = MB_Ref("ims_fields", IMS_SuperGroupNAme());
  $form["formtemplate"] = '<table><tr><td>Country (List):</td><td>[[[country]]]</td></tr>';
  foreach ($form["metaspec"]["fields"] as $fieldname => $fieldspecs) {
    if ($fieldspecs["type"] == "fk") $form["formtemplate"] .= "<tr><td>{{{{$fieldname}}}} (FK):</td><td>[[[$fieldname]]]</td></tr>";
    if ($fieldspecs["type"] == "list" && count($fieldspecs["values"]) > 25) $form["formtemplate"] .= str_repeat("<tr><td>{{{".$fieldname."}}} (List):</td><td>[[[$fieldname]]]</td></tr>", 2); // dubbel
    if ($fieldspecs["type"] == "code" && strpos($fieldspecs["edit"], "FORMS_FK_Edit")) $form["formtemplate"] .= "<tr><td>{{{{$fieldname}}}} (FK code):</td><td>[[[$fieldname]]]</td></tr>";
  }
  $form["metaspec"]["fields"]["country"]["values"]["&lt;kies&gt;"] = "";
  //$form["metaspec"]["fields"]["country"]["values"]["<kies>"] = "";
  foreach (json_decode(N_GetPage("http://opencountrycodes.appspot.com/json")) as $country) {
    $form["metaspec"]["fields"]["country"]["values"][$country->name] = $country->code;
  }
  $form["metaspec"]["fields"]["country"]["type"] = "list";
  $form["formtemplate"] .= '<tr><td colspan=2>[[[OK]]]</td></tr></table>';
 
  $form["precode"] = '
    global $myconfig; $myconfig[IMS_SuperGRoupName()]["autocompletefields"] = "yes";
    //$data["country"] = "NL";
  ';
  $form["postcode"] = '
    N_Sleep(1000); // to test some stuff with grey OK button
    T_Start();
    T_EO($data);
    FORMS_ShowError("debug", TS_End());
  ';
  $form["gotook"] = "closeme";
  echo '<a href="' . FORMS_URL($form) . '">klik</a>';
}

function TEST_IndexBug ()
{
  MB_DeleteTable ("test_indexbug");
  MB_CAC_FlushAndClean ();
  MB_Save ("test_indexbug", "a", array ("q"=>"aaa"));
  MB_Save ("test_indexbug", "b", array ("q"=>"bbb"));
  MB_Save ("test_indexbug", "c", array ("q"=>"ccc"));
  MB_CAC_FlushAndClean ();
  echo "pre rec: "; T_EO (MB_TurboMultiQuery ("test_indexbug", array (
    "filter" => array ('$record["q"]', "bbb")
  )));
  echo "pre fn: "; T_EO (MB_TurboMultiQuery ("test_indexbug", array (
    "filter" => array ('MB_Fetch ("test_indexbug", $key, "q")', "bbb")
  )));
  MB_Save ("test_indexbug", "b", array ("q"=>"qqq"));
  MB_CAC_FlushAndClean ();
  echo "post rec: "; T_EO (MB_TurboMultiQuery ("test_indexbug", array (
    "filter" => array ('$record["q"]', "bbb")
  )));
  echo "post fn (bbb): "; T_EO (MB_TurboMultiQuery ("test_indexbug", array (
    "filter" => array ('MB_Fetch ("test_indexbug", $key, "q")', "bbb")
  )));
  echo "post fn (qqq): "; T_EO (MB_TurboMultiQuery ("test_indexbug", array (
    "filter" => array ('MB_Fetch ("test_indexbug", $key, "q")', "qqq")
  )));
  MB_DeleteTable ("test_indexbug");
  MB_CAC_FlushAndClean ();
}

function TEST_TERRA ()
{
  $specs["init_code"] = '
    N_Log ("test_terra", "init");
  ';   
  $specs["step_code"] = '
    N_Log ("test_terra", "step");
    if (rand(1, 100)==1) $completed = 1;
  ';
  $specs["exit_code"] = '
    N_Log ("test_terra", "exit");
  ';
  TERRA_MultiStep ($specs);  
}

function TEST_MultiMatch ()
{
  MB_DeleteTable ("test_multi");
  for ($i=1; $i<=100; $i++) {
    $values = array();
    if ($i % 2 == 0) $values[] = "val2";
    if ($i % 3 == 0) $values[] = "val3";
    if ($i % 5 == 0) $values[] = "val5";
    if ($i % 7 == 0) $values[] = "val7";
    MB_Save ("test_multi", $i, $values);
  }
  MB_CAC_FlushAndClean ("test_multi", $specs);

  $specs = array ("slowselect" => array ('in_array ("val3", $record) && in_array ("val5", $record)' => true));
  $count = MB_MultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_MultiQuery ("test_multi", $specs);
  echo "<b>3 && 5: slowselect: </b><br>";
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";
  $specs = array ("slowselect" => array ('in_array ("val3", $record) && in_array ("val5", $record)' => true));
  $count = MB_TurboMultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_TurboMultiQuery ("test_multi", $specs);
  echo "<b>3 && 5: slowselect (turbo): </b><br>";
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";
  $specs = array ("multimatch" => array ("and", '$record', array ("val3", "val5")));
  $count = MB_MultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_MultiQuery ("test_multi", $specs);
  echo "<b>3 && 5: multimatch: </b><br>";
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";
  $specs = array ("multimatch" => array ("and", '$record', array ("val3", "val5")));
  $count = MB_TurboMultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_TurboMultiQuery ("test_multi", $specs);
  echo "<b>3 && 5: multimatch (turbo): </b><br>";
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";

  $specs = array ("slowselect" => array ('in_array ("val3", $record) || in_array ("val5", $record)' => true));
  $count = MB_MultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));  $result = MB_MultiQuery ("test_multi", $specs);
  echo "<b>3 || 5: slowselect: </b><br>";
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";
  $specs = array ("slowselect" => array ('in_array ("val3", $record) || in_array ("val5", $record)' => true));
  $count = MB_TurboMultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_TurboMultiQuery ("test_multi", $specs);
  echo "<b>3 || 5: slowselect (turbo): </b><br>";
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";
  $specs = array ("multimatch" => array ("or", '$record', array ("val3", "val5")));
  $count = MB_MultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_MultiQuery ("test_multi", $specs);
  echo "<b>3 || 5: multimatch : </b><br>";
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";
  $specs = array ("multimatch" => array ("or", '$record', array ("val3", "val5")));
  $count = MB_TurboMultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_TurboMultiQuery ("test_multi", $specs);
  echo "<b>3 || 5: multimatch (turbo): </b><br>";
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";
  
  $specs = array ("multimultimatch" => array (array ("or", '$record', array ("val2", "val3")), array ("or", '$record', array ("val5", "val7"))));
  echo "<b>(2 || 3) && (5 || 7): multimultimatch: </b><br>";
  $count = MB_MultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_MultiQuery ("test_multi", $specs);
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";

  $specs = array ("multimultimatch" => array (array ("or", '$record', array ("val2", "val3")), array ("or", '$record', array ("val5", "val7"))));
  echo "<b>(2 || 3) && (5 || 7): multimultimatch (turbo): </b><br>";
  $count = MB_TurboMultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_TurboMultiQuery ("test_multi", $specs);
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";

  $specs = array ("multimultimatch" => array (array ("and", '$record', array ("val2", "val3")), array ("and", '$record', array ("val3", "val2"))));
  echo "<b>(2 && 3) && (3 && 2): multimultimatch (turbo): </b><br>";
  $count = MB_TurboMultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_TurboMultiQuery ("test_multi", $specs);
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";

  $specs = array ("multimultimatch" => array (array ("and", '$record', array ("val2", "val3")), array ("and", '$record', array ("val3", "val2"))));
  echo "<b>(2 && 3) && (3 && 2): multimultimatch: </b><br>";
  $count = MB_MultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_MultiQuery ("test_multi", $specs);
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";

  $specs = array ("multimultimatch" => array (array ("and", '$record', array ("val2", "val3")), array ("or", '$record', array ("val5", "val7"))));
  echo "<b>(2 && 3) && (5 || 7): multimultimatch (turbo): </b><br>";
  $count = MB_TurboMultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_TurboMultiQuery ("test_multi", $specs);
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";

  $specs = array ("multimultimatch" => array (array ("and", '$record', array ("val2", "val3")), array ("or", '$record', array ("val5", "val7"))));
  echo "<b>(2 && 3) && (5 || 7): multimultimatch: </b><br>";
  $count = MB_MultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_MultiQuery ("test_multi", $specs);
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";

  $specs = array ("multimultimatch" => array (array ("and", 'array_merge($record,array())', array ("val2", "val3")), array ("or", '$record', array ("val5", "val7"))));
  echo "<b>(2 && 3) && (5 || 7): multimultimatch (turbo): </b><br>";
  $count = MB_TurboMultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_TurboMultiQuery ("test_multi", $specs);
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";

  $specs = array ("multimultimatch" => array (array ("and", 'array_merge($record,array())', array ("val2", "val3")), array ("or", '$record', array ("val5", "val7"))));
  echo "<b>(2 && 3) && (5 || 7): multimultimatch: </b><br>";
  $count = MB_MultiQuery ("test_multi", array_merge($specs, array("slice" => "count")));
  $result = MB_MultiQuery ("test_multi", $specs);
  echo "<b>$count</b>: " . implode(", ", $result) . "<br/>";
}

function TEST_Sphinx2 () // A458MR57
{
  global $myconfig;
  $myconfig["sphinx"]["maxdynamicsegmentsize"] = 50;
  $myconfig["sphinx"]["maxstaticsegmentsize"]  = 100;
  $myconfig["sphinx"]["documentqueuesize"]     = 50;
  $myconfig["sphinx"]["segmentresultmax"]      = 1200;
  $myconfig["sphinx"]["sqlrangestep"]          = 10;

  $index = "test";

  echo "Disabling compact indexes...<br>"; N_FLush(-1);
  $myconfig["sphinx"]["compactindex"] = "no"; // disable compact indexes for the first part of the test

  echo "Nuking index...<br>"; N_FLush(-1);
  SPHINX2_NukeIndex ($index);

  echo "Creating index...<br>"; N_FLush(-1);
  SPHINX2_CreateIndex ($index);

  $m = SPHINX2_Meta_Load($index);
  echo "Using compact index: " . ($m["compact"] == "yes" ? "yes" : "no") . "<br/>";

  echo "Adding 3 records (+flush+update)...<br>"; N_FLush(-1);
  SPHINX2_AddReadableTextToIndex ($index, "key001", "text", "keywords qqq 10");
  SPHINX2_AddReadableTextToIndex ($index, "key002", "text", "keywords qqq 20");
  SPHINX2_AddReadableTextToIndex ($index, "key003", "text", "keywords qqq 30 aap");
  SPHINX2_Flush ($index);

  SPHINX2_UpdateDirtyIndexes ();
  TEST_SphinxQuery2 ($index, "qqq", "5c285bf721edd1466883082c253e617d", "5394623610cc2fcbb52c11fe98a325ce");
  TEST_SphinxQuery2 ($index, "aap", "529bd801211b5e82d3b78eab5daca62c");

  echo "Replacing 1 record (+flush)...<br>"; N_FLush(-1);
  SPHINX2_AddReadableTextToIndex ($index, "key003", "text", "keywords qqq 30 extra");
  SPHINX2_Flush ($index);
  N_AddModifyScedule ("updatesphinx", time()+10, ''); // prevent updating
  TEST_SphinxQuery2 ($index, "aap", "dcca48101505dd86b703689a604fe3c4");
  TEST_SphinxQuery2 ($index, "extra", "dcca48101505dd86b703689a604fe3c4");

  echo "Update...<br>"; N_FLush(-1);
  SPHINX2_UpdateDirtyIndexes ();
  TEST_SphinxQuery2 ($index, "aap", "dcca48101505dd86b703689a604fe3c4");
  TEST_SphinxQuery2 ($index, "extra", "529bd801211b5e82d3b78eab5daca62c");

  echo "Adding bunch of records, two of them twice, also test ranking (+flush)...<br>"; N_FLush(-1);
  for ($i=100; $i<999; $i++) {  SPHINX2_AddReadableTextToIndex ($index, "key$i", "text", "keywords $i"); }
  SPHINX2_AddReadableTextToIndex ($index, "key1001", "text", "keywords qqq 1010");
  SPHINX2_AddReadableTextToIndex ($index, "key1002", "text", "keywords qqq 2020 text");
  SPHINX2_AddReadableTextToIndex ($index, "key1003", "text", "keywords qqq 3030 aap");
  SPHINX2_AddReadableTextToIndex ($index, "key1003", "text", "keywords qqq 3030 extra");
  echo "Records added, flushing... "; N_FLush(-1);
  SPHINX2_Flush ($index);
  echo " Flushed.<br>"; N_FLush(-1);

  TEST_SphinxQuery2 ($index, "qqq", "5394623610cc2fcbb52c11fe98a325ce");
  TEST_SphinxQuery2 ($index, "aap", "dcca48101505dd86b703689a604fe3c4");
  TEST_SphinxQuery2 ($index, "extra", "529bd801211b5e82d3b78eab5daca62c");
  echo "Update...<br>"; N_FLush(-1);
  SPHINX2_UpdateDirtyIndexes ();
  TEST_SphinxQuery2 ($index, "text", "e0828f3e0970a842dddf29aadbb0b3a8");
  TEST_SphinxQuery2 ($index, "qqq", "089333ed61e1111cbbea1306f040e1b5");
  TEST_SphinxQuery2 ($index, "aap", "dcca48101505dd86b703689a604fe3c4");
  TEST_SphinxQuery2 ($index, "extra", "604d0a6d4baa8b04822f88b7ef429197");

  echo "Removing a record (+flush)...<br>"; N_FLush(-1);
  SPHINX2_RemoveFromIndex($index, "key1001");
  TEST_SphinxQuery2 ($index, "text", "cea0f93cc248ba3c8b36b3e6705a6cb1");
  TEST_SphinxQuery2 ($index, "qqq", "b88b9316e411e5c07aa497737372939e");
  echo "Update...<br>"; N_FLush(-1);
  SPHINX2_UpdateDirtyIndexes ();
  TEST_SphinxQuery2 ($index, "text", "cea0f93cc248ba3c8b36b3e6705a6cb1");
  TEST_SphinxQuery2 ($index, "qqq", "b88b9316e411e5c07aa497737372939e");

  echo "Enabling compact indexes...<br>"; N_FLush(-1);
  $myconfig["sphinx"]["compactindex"] = "yes"; 
  echo "Optimize...<br>"; N_FLush(-1);
  SPHINX2_OptimizeIndex ($index);
  $m = SPHINX2_Meta_Load($index);
  echo "Using compact index: " . ($m["compact"] == "yes" ? "yes" : "no") . "<br/>";
  TEST_SphinxQuery2 ($index, "text", "63564226a31bb9914786bba89966006a");
  TEST_SphinxQuery2 ($index, "qqq", "d589c7cf5da21314f66d3f58fbbcdec4");
  TEST_SphinxQuery2 ($index, "aap", "dcca48101505dd86b703689a604fe3c4");
  TEST_SphinxQuery2 ($index, "extra", "604d0a6d4baa8b04822f88b7ef429197");

  echo "Removing a dynamic record (+flush)...<br>"; N_FLush(-1);
  SPHINX2_RemoveFromIndex($index, "key1002");
  TEST_SphinxQuery2 ($index, "text", "4f64b3a3514d5bd04aa581b8e341433d");
  TEST_SphinxQuery2 ($index, "qqq", "16cdc8a632151b356c1d5cadc44874cf");
  echo "Update...<br>"; N_FLush(-1);
  SPHINX2_UpdateDirtyIndexes ();
  TEST_SphinxQuery2 ($index, "text", "4f64b3a3514d5bd04aa581b8e341433d");
  TEST_SphinxQuery2 ($index, "qqq", "16cdc8a632151b356c1d5cadc44874cf");
  echo "Optimize...<br>"; N_FLush(-1);
  SPHINX2_OptimizeIndex ($index);
  TEST_SphinxQuery2 ($index, "text", "4f64b3a3514d5bd04aa581b8e341433d");
  TEST_SphinxQuery2 ($index, "qqq", "16cdc8a632151b356c1d5cadc44874cf");

  echo "Removing a static record (+flush)...<br>"; N_FLush(-1);
  SPHINX2_RemoveFromIndex($index, "key500");
  TEST_SphinxQuery2 ($index, "text", "09e41fd8a5d61ef48e97b9c8619592d8");
  echo "Update...<br>"; N_FLush(-1);
  SPHINX2_UpdateDirtyIndexes ();
  TEST_SphinxQuery2 ($index, "text", "09e41fd8a5d61ef48e97b9c8619592d8");
  echo "Optimize...<br>"; N_FLush(-1);
  SPHINX2_OptimizeIndex ($index);
  TEST_SphinxQuery2 ($index, "text", "09e41fd8a5d61ef48e97b9c8619592d8");

  // Test adding records again (for compact indexes)
  echo "Adding a few records again <br/>"; N_FLush(-1);
  SPHINX2_AddReadableTextToIndex ($index, "key2001", "text", "keywords qqq 1010");
  SPHINX2_AddReadableTextToIndex ($index, "key2002", "text", "keywords qqq 2020 text");
  SPHINX2_AddReadableTextToIndex ($index, "key2003", "text", "keywords qqq 3030 aap");
  SPHINX2_AddReadableTextToIndex ($index, "key2003", "text", "keywords qqq 3030 extra");
  echo "Records added, flushing... "; N_FLush(-1);
  SPHINX2_Flush ($index);

  echo " Flushed.<br>"; N_FLush(-1);
  TEST_SphinxQuery2 ($index, "qqq", "16cdc8a632151b356c1d5cadc44874cf");
  TEST_SphinxQuery2 ($index, "aap", "dcca48101505dd86b703689a604fe3c4");
  TEST_SphinxQuery2 ($index, "extra", "604d0a6d4baa8b04822f88b7ef429197");
  echo "Update...<br>"; N_FLush(-1);
  SPHINX2_UpdateDirtyIndexes ();
  TEST_SphinxQuery2 ($index, "text", "0cd3b743ba5ee6e211ca22267a26504c");
  TEST_SphinxQuery2 ($index, "qqq", "d91233d210fec1f3ad711ff5e9dc05d8");
  TEST_SphinxQuery2 ($index, "aap", "dcca48101505dd86b703689a604fe3c4");
  TEST_SphinxQuery2 ($index, "extra", "1505fa21eaf74225483e92ba13379a72");


  echo "Adding records for phrase and negative search<br/>"; N_FLush(-1);
  SPHINX2_AddReadableTextToIndex ($index, "key2011", "text", "qqq keywords 1010");
  SPHINX2_AddReadableTextToIndex ($index, "key2012", "text", "qqq text keywords 2020");
  SPHINX2_AddReadableTextToIndex ($index, "key2013", "text", "qqq extra keywords 3030");
  SPHINX2_AddReadableTextToIndex ($index, "key2014", "text", "extra qqq keywords 4040");
  echo "Records added, flushing... "; N_FLush(-1);
  SPHINX2_Flush ($index);
  echo " Flushed.<br>"; N_FLush(-1);
  echo "Update...<br>"; N_FLush(-1);
  SPHINX2_UpdateDirtyIndexes ();

  TEST_SphinxQuery2 ($index, 'qqq extra', "a22b361a75c12fec700df1c655cae361"); // no phrase search
  TEST_SphinxQuery2 ($index, '"qqq extra"', "b1fd3536f3da273322a7d4a204458619"); // phrase search
  TEST_SphinxQuery2 ($index, 'keywords "qqq extra"', "b1fd3536f3da273322a7d4a204458619"); // normal search + phrase search
  TEST_SphinxQuery2 ($index, 'aap "qqq extra"', "dcca48101505dd86b703689a604fe3c4"); // normal search + phrase search (no results)
  TEST_SphinxQuery2 ($index, 'qqq -extra', "95fc40cb7d7d2ed677baa9e447738c68"); // negative search  
  TEST_SphinxQuery2 ($index, '-extra qqq', "95fc40cb7d7d2ed677baa9e447738c68"); // negative search starting with -
}

function TEST_SphinxQuery2 ($index, $q, $checksum, $checksum2="")
{
  $result = SPHINX2_Search ($index, $q);
  if (is_array($result)) $result = array_keys($result); // ignore actual rankings, only look at order of results (useful when comparing standard search with extended search)
  $c = md5(serialize($result));
  if ($c==$checksum) {
    echo "TEST_SphinxQuery2 ($index, $q, $checksum) OK (".count($result)." results)<br>";
  } else if ($c==$checksum2) {
    echo "TEST_SphinxQuery2 ($index, $q, $checksum2) OK (".count($result)." results)<br>";
  } else {
    echo "TEST_SphinxQuery2 ($index, $q, $checksum) ERROR (actual checksum=$c, ".count($result)." results)<br>";
    T_EO ($result);
  }
}

function TEST_Sphinx () // A458MR57
{
  global $myconfig;
  $myconfig["sphinx"]["maxdynamicsegmentsize"] = 50;
  $myconfig["sphinx"]["maxstaticsegmentsize"]  = 100;
  $myconfig["sphinx"]["documentqueuesize"]     = 50;
  $myconfig["sphinx"]["segmentresultmax"]      = 1200;
  $myconfig["sphinx"]["sqlrangestep"]          = 10;

  $index = "test";

  echo "Nuking index...<br>"; N_FLush(-1);
  SPHINX_NukeIndex ($index);

  echo "Creating index...<br>"; N_FLush(-1);
  SPHINX_CreateIndex ($index);

  echo "Adding 3 records (+flush+update)...<br>"; N_FLush(-1);
  SPHINX_AddReadableTextToIndex ($index, "key001", "text", "keywords qqq 10");
  SPHINX_AddReadableTextToIndex ($index, "key002", "text", "keywords qqq 20");
  SPHINX_AddReadableTextToIndex ($index, "key003", "text", "keywords qqq 30 aap");
  SPHINX_Flush ($index);
  SPHINX_UpdateDirtyIndexes ();
  TEST_SphinxQuery ($index, "qqq", "5c285bf721edd1466883082c253e617d", "ef915b60827be6bfb02533f597f24114");   
  TEST_SphinxQuery ($index, "aap", "150b6e5f2af4dccfd88a07994c15c279");

  echo "Replacing 1 record (+flush)...<br>"; N_FLush(-1);
  SPHINX_AddReadableTextToIndex ($index, "key003", "text", "keywords qqq 30 extra");
  SPHINX_Flush ($index);
  N_AddModifyScedule ("updatesphinx", time()+10, ''); // prevent updating
  TEST_SphinxQuery ($index, "aap", "dcca48101505dd86b703689a604fe3c4");
  TEST_SphinxQuery ($index, "extra", "dcca48101505dd86b703689a604fe3c4");

  echo "Update...<br>"; N_FLush(-1);
  SPHINX_UpdateDirtyIndexes ();
  TEST_SphinxQuery ($index, "aap", "dcca48101505dd86b703689a604fe3c4");
  TEST_SphinxQuery ($index, "extra", "150b6e5f2af4dccfd88a07994c15c279");

  echo "Adding bunch of records, two of them twice, also test ranking (+flush)...<br>"; N_FLush(-1);
  for ($i=100; $i<999; $i++) {  SPHINX_AddReadableTextToIndex ($index, "key$i", "text", "keywords $i"); }
  SPHINX_AddReadableTextToIndex ($index, "key1001", "text", "keywords qqq 1010");
  SPHINX_AddReadableTextToIndex ($index, "key1002", "text", "keywords qqq 2020 text");
  SPHINX_AddReadableTextToIndex ($index, "key1003", "text", "keywords qqq 3030 aap");
  SPHINX_AddReadableTextToIndex ($index, "key1003", "text", "keywords qqq 3030 extra");
  SPHINX_Flush ($index);
  TEST_SphinxQuery ($index, "qqq", "5c285bf721edd1466883082c253e617d");
  TEST_SphinxQuery ($index, "aap", "dcca48101505dd86b703689a604fe3c4");
  TEST_SphinxQuery ($index, "extra", "150b6e5f2af4dccfd88a07994c15c279");
  echo "Update...<br>"; N_FLush(-1);
  SPHINX_UpdateDirtyIndexes ();
  TEST_SphinxQuery ($index, "text", "ad4c3fb9735a8ed7ff002d1d9dd822c1");
  TEST_SphinxQuery ($index, "qqq", "3a95721cbee76f787e961a19ece5ec5b");
  TEST_SphinxQuery ($index, "aap", "dcca48101505dd86b703689a604fe3c4");
  TEST_SphinxQuery ($index, "extra", "60d9d1a47527fabe79f6e6aea5ddce93");

  echo "Removing a record (+flush)...<br>"; N_FLush(-1);
  SPHINX_RemoveFromIndex($index, "key1001");
  TEST_SphinxQuery ($index, "text", "0bc9c286ea9925fa2a7c100535869e9d");
  TEST_SphinxQuery ($index, "qqq", "f87d138c55166dd9a3b03032800f2885");
  echo "Update...<br>"; N_FLush(-1);
  SPHINX_UpdateDirtyIndexes ();
  TEST_SphinxQuery ($index, "text", "0bc9c286ea9925fa2a7c100535869e9d");
  TEST_SphinxQuery ($index, "qqq", "f87d138c55166dd9a3b03032800f2885");

  echo "Optimize...<br>"; N_FLush(-1);
  SPHINX_OptimizeIndex ($index);
  TEST_SphinxQuery ($index, "text", "4fc7c9593371d155b7848a37ff617d4c");
  TEST_SphinxQuery ($index, "qqq", "c6d53039a52507b9bd5b98e136446b42");
  TEST_SphinxQuery ($index, "aap", "dcca48101505dd86b703689a604fe3c4");
  TEST_SphinxQuery ($index, "extra", "60d9d1a47527fabe79f6e6aea5ddce93");

  echo "Removing a dynamic record (+flush)...<br>"; N_FLush(-1);
  SPHINX_RemoveFromIndex($index, "key1002");
  TEST_SphinxQuery ($index, "text", "d216f35f5780f7c7abf96469636fb9a8");
  TEST_SphinxQuery ($index, "qqq", "de7f0e813a79dae3b3b9546f2a4c1798");
  echo "Update...<br>"; N_FLush(-1);
  SPHINX_UpdateDirtyIndexes ();
  TEST_SphinxQuery ($index, "text", "d216f35f5780f7c7abf96469636fb9a8");
  TEST_SphinxQuery ($index, "qqq", "de7f0e813a79dae3b3b9546f2a4c1798");
  echo "Optimize...<br>"; N_FLush(-1);
  SPHINX_OptimizeIndex ($index);
  TEST_SphinxQuery ($index, "text", "d216f35f5780f7c7abf96469636fb9a8");
  TEST_SphinxQuery ($index, "qqq", "9d8a3d979dae2b96d9a6b7519a9c16fc");

  echo "Removing a static record (+flush)...<br>"; N_FLush(-1);
  SPHINX_RemoveFromIndex($index, "key500");
  TEST_SphinxQuery ($index, "text", "ac31afac82ce99b162dda76dbd0af2d6");
  echo "Update...<br>"; N_FLush(-1);
  SPHINX_UpdateDirtyIndexes ();
  TEST_SphinxQuery ($index, "text", "ac31afac82ce99b162dda76dbd0af2d6");
  echo "Optimize...<br>"; N_FLush(-1);
  SPHINX_OptimizeIndex ($index);
  TEST_SphinxQuery ($index, "text", "ae11031e89e5049ad133506702eba2e3");
}

function TEST_SphinxQuery ($index, $q, $checksum, $checksum2="")
{
  $result = SPHINX_Search ($index, $q);
  $c = md5(serialize($result));
  if ($c==$checksum) {
    echo "TEST_SphinxQuery ($index, $q, $checksum) OK (".count($result)." results)<br>";
  } else if ($c==$checksum2) {
    echo "TEST_SphinxQuery ($index, $q, $checksum2) OK (".count($result)." results)<br>";
  } else {
    echo "TEST_SphinxQuery ($index, $q, $checksum) ERROR (actual checksum=$c, ".count($result)." results)<br>";
    N_EO ($result);
  }
}

function TEST_Dyna ()
{
  echo DYNA_IncludeDynaJS ();

  echo "<br>";
  for ($i=1; $i<10; $i++) {
    echo "Field #$i:".DYNA_TextField ('$value = MB_REP_Load ("local_test", "dynatest'.$i.'");', 'MB_REP_Save ("local_test", "dynatest'.$i.'", $value);')." ";
    $i++;
    echo "Field #$i:".DYNA_TextField ('$value = MB_REP_Load ("local_test", "dynatest'.$i.'");', 'MB_REP_Save ("local_test", "dynatest'.$i.'", $value);')."<BR>";
  }
  echo "<br>";

//  echo "DEBUG:[".DHTML_DynamicObject ("", "debug")."]";
//  echo DHTML_EmbedJavascript ("D_Debug ('test');");

//  echo '<input type="text" value="go" name=search onFocus=alert("The image is in focus") onChange=alert("The user changed the value")>';
//  echo '<a href="JavaScript:void(0)" onMouseDown=alert("The user pressed the mouse button")> Click here</a>';

//  echo DHTML_InvisiTable ("","","Counter:[",DHTML_DynamicObject ("", "counter"),"]");
//  echo DHTML_EmbedJavascript ("var testctr=0; D_AddWatch (\"document.getElementById('divcounter').innerHTML = testctr++/10;\", '', 100);");

  echo DHTML_InvisiTable ("","","Time:[",DHTML_DynamicObject ("", "time"),"]");
  echo DHTML_EmbedJavascript ("D_AddWatch (\"var tDate = new Date();document.getElementById('divtime').innerHTML =  + tDate.getHours() + ':'  + tDate.getMinutes() + ':'  + tDate.getSeconds();\", '', 250);");

//  echo DHTML_InvisiTable ("","","Test:[",DHTML_DynamicObject ("", "test"),"]");
//  $api = DYNA_CreateAPI ('
//    $output = "hallo";
//    echo "D_Debug (output);";
//    echo DHTML_SetDynamicObject ("test", "($serverinput)12345($input)");
//  ', "test");
//  for ($i=1; $i<=10; $i++) {
//    echo DHTML_EmbedJavascript ("D_SendMessage ('$api', $i);");
//  }
//  echo DHTML_EmbedJavascript ("var testctr=0; D_AddWatch ('D_SendMessage (\'$api\', testctr++)', '', 1000);");
//  echo "DUMP:[".DHTML_DynamicObject ("", "dump")."]";
//  echo DHTML_EmbedJavascript ("var testctr=0; D_AddWatch (\"document.getElementById('divdump').innerHTML = D_XML2HTML(D_SO(d_store));\", '', 1000);");

//  echo DHTML_EmbedJavascript ("D_EO (d_store)");
}

function TEST_FTIndex ()
{
  uuse ("s2");
  // 7s / 6s
//  SEARCH_NukeIndex ("test::100x1000", "please"); // 180ms
//  for ($i=1; $i<=100; $i++) {
//    SEARCH_AddTextToIndex ("test::100x1000", $i, TEST_DocGen (200, $i), "");
//  }
  // 128s / 82s
//  SEARCH_NukeIndex ("test::1000x1000", "please"); // 1.8s
//  for ($i=1; $i<=1000; $i++) {
//   SEARCH_AddTextToIndex ("test::1000x1000", $i, TEST_DocGen (200, $i), "");
//  }
  // 8673s (after optimizing documentlist management:  5298s)
//  SEARCH_MYSQL_NukeIndex ("test::10000x1000", "please"); // 17s
//  for ($i=1; $i<=10000; $i++) {
//    SEARCH_AddTextToIndex ("test::10000x1000", $i, TEST_DocGen (200, $i), "");
//  }  
  // 12s (ex SQL 2.1s) / 8s
//  for ($i=1; $i<=100; $i++) {
//    SEARCH_AddTextToIndex ("test::100x1000", $i, TEST_DocGen (200, $i), "");
//    echo "$i "; N_FLush(-1);
//  }
  // 227s (retry 95s, ex SQL 2.5s) / 95s
//  for ($i=1; $i<=100; $i++) {
//    SEARCH_AddTextToIndex ("test::10000x1000", $i, TEST_DocGen (200, $i), "");
//    echo "$i "; N_FLush(-1);
//  }
  // 500ms, v2 600ms, v3 270ms
//  S2_NukeIndex ("test::100x1000", "please"); // 22ms
//  for ($i=1; $i<=100; $i++) {
//    S2_AddReadableTextToIndex ("test::100x1000", $i, TEST_DocGen (200, $i), "");
//  }
  // 362s, v2 754s, v3 525s, MY5 322s
//  S2_NukeIndex ("test::10000x1000", "please");
//  for ($i=1; $i<=10000; $i++) {
//    S2_AddReadableTextToIndex ("test::10000x1000", $i, TEST_DocGen (200, $i), "");
//  }
  // 151s, v2 234s, MY5 247s
//  S2_NukeIndex ("test::1000x10000", "please");
//  for ($i=1; $i<=1000; $i++) {
//    S2_AddReadableTextToIndex ("test::1000x10000", $i, TEST_DocGen (2000, $i), "");
//  }

//  S2_NukeIndex ("test::complex", "please");
//  S2_AddReadableTextToIndex ("test::complex", 1, "aap noot mies", "");
//  S2_AddReadableTextToIndex ("test::complex", 2, "aap@noot.com", "");
//  S2_AddReadableTextToIndex ("test::complex", 3, "123€", "");
//  S2_AddReadableTextToIndex ("test::complex", 4, "12.345,67", "");
//  S2_AddReadableTextToIndex ("test::complex", 5, "12,345.67", "");
//  S2_AddReadableTextToIndex ("test::complex", 6, "1234AB", "");
//  S2_AddReadableTextToIndex ("test::complex", 7, "1234 AB", "");
//  S2_AddReadableTextToIndex ("test::complex", 8, "http://www.osict.com", "");
//  S2_AddReadableTextToIndex ("test::complex", 9, "&euro;", "");
//  S2_AddReadableTextToIndex ("test::complex", 10, "&#20001; &#20002; &#20003;", "");
//  S2_AddReadableTextToIndex ("test::complex", 11, "&#20001;&#20002;&#20003;", "");
//  S2_AddReadableTextToIndex ("test::complex", 12, "&#20001;&#20002;&#20003;", "");
//  S2_AddReadableTextToIndex ("test::complex", 13, "Défilé Prêt à Françoise", "");
//  S2_AddReadableTextToIndex ("test::complex", 14, "C++ TCP/IP J#", "");
//  S2_AddReadableTextToIndex ("test::complex", 15, "C# J#", "");
//  S2_AddReadableTextToIndex ("test::complex", 16, "KZ2-34.6-88Q/BB", "");
//  N_EO (S2_MYSQLFT_Query ("test::complex", "aap@noot.com")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "\"aap@noot.com\"")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "12.345,67")); // ok (2 results)
//  N_EO (S2_MYSQLFT_Query ("test::complex", "12,345.67")); // ok (2 results)
//  N_EO (S2_MYSQLFT_Query ("test::complex", "12345.67")); // ok (2 results)
//  N_EO (S2_MYSQLFT_Query ("test::complex", "12345,67")); // ok (2 results)
//  N_EO (S2_MYSQLFT_Query ("test::complex", "12345")); // not ok (0 results)
//  N_EO (S2_MYSQLFT_Query ("test::complex", "1234AB")); // not ok (1 result)
//  N_EO (S2_MYSQLFT_Query ("test::complex", "1234 AB")); // not ok (1 result)
//  N_EO (S2_MYSQLFT_Query ("test::complex", "osict")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "osict.com")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "www.osict.com")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "http://www.osict.com")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "+http://+www.+osict.+com")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "&#20001;")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "&#20001;&#20002;")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "&#20001; &#20002;")); // ok 
//  N_EO (S2_MYSQLFT_Query ("test::complex", "20001")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "&#euro;")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "€")); // not ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "defile pret a francoise")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "Défilé Prêt à Françoise")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "\"defile pret a francoise\"")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "\"Défilé Prêt à Françoise\"")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "C++")); // aprox (2 results)
//  N_EO (S2_MYSQLFT_Query ("test::complex", "C#")); // aprox (2 results)
//  N_EO (S2_MYSQLFT_Query ("test::complex", "TCP/IP")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "KZ2-34.6-88Q/BB")); // ok
//  N_EO (S2_MYSQLFT_Query ("test::complex", "KZ2-34.6")); // ok

// echo count ($l1=S2_MYSQLFT_Query ("test::100x1000", "+abc"))."<br>";
// echo count ($l1=S2_MYSQLFT_Query ("test::100x1000", "a b c"))."<br>";
// echo count ($l1=S2_MYSQLFT_Query ("test::10000x1000", "+abc"))."<br>"; // cold 1.5s, MY5 4.3s
// $res = SEARCH ("test::10000x1000", "abc", 0, 0); echo count ($l2=$res["result"])."<br>"; // cold 8.6s

// echo count ($l1=S2_MYSQLFT_Query ("test::10000x1000", "+abc +a +b +c +x +y +z"))."<br>"; // cold 2.4s MY5 2.6s
// $res = SEARCH ("test::10000x1000", "abc x y z a b c"); echo count ($l2=$res["result"])."<br>"; // cold 22.8s
// global $myconfig; $myconfig["ftengine"]="S2_MYSQLFT";

// SEARCH_NukeIndex ("test::mini", "please");
// SEARCH_AddTextToIndex ("test::mini", "a", "a b c d e f 1 1 1 1", " a a ");
// T_EO (SEARCH ("test::mini", "b"));

// SEARCH_AddTextToIndex ("test::mini", "b", "a b c d e f 2 2 2 2", " b b ");
// SEARCH_AddTextToIndex ("test::mini", "c", "a b c d e f 3 3 3 3", " c c ");
// SEARCH_AddTextToIndex ("test::mini", "d", "a d c d e f 4 4 4 b", " d d ");
// T_EO (SEARCH ("test::mini", "b"));

// SEARCH_AddTextToIndex ("test::mini", "d", "b b b b e f 4 4 4 b", " b b ");
// T_EO (SEARCH ("test::mini", "b"));
}

function TEST_DocAnalyze ($doc)
{
  $tmplist = explode (" ", $doc);
  $result = array_count_values($tmplist);
  unset ($result[""]);
  foreach ($result as $word => $count) {
    $total[$count]++;
  }
  ksort ($total);
  foreach ($total as $count => $amount) {
    echo $amount." words occur $count times<br>";
    $tot += $amount * $count;
  }
  echo "total $tot words<br>";
}

// 7781ms
function TEST_DocGen ($size, $index=0)
{
  if ($index) {
    $key = DFC_Key ("TEST_DocGen ($size, $index) v2");
    if (DFC_Exists ($key)) return DFC_Read ($key);
    srand($index);
  }
  $total = $size;
  $size = $size / 100;
  for ($i=0; $i<$size; $i++) {
    $tmp = "";
    $chunk = 100;
    if ($chunk > $total) $chunk = $total;
    $total -= $chunk;  
    for ($j=0; $j<$chunk; $j++) {
      if (rand(1,10)==1) { // 10% uncommon words
        $tmp .= chr(97+rand(0,25));
        if (rand(1,2)==1) $tmp .= chr(97+rand(0,25)).chr(97+rand(0,25));
        if (rand(1,2)==1) $tmp .= chr(97+rand(0,25)).chr(97+rand(0,25)).chr(97+rand(0,25)).chr(97+rand(0,25));
      } else { // 95% common words
        $tmp .= chr(97+rand(0,3));
        if (rand(1,2)==1) $tmp .= chr(97+rand(0,3)).chr(97+rand(0,3));
        if (rand(1,2)==1) $tmp .= chr(97+rand(0,3)).chr(97+rand(0,3)).chr(97+rand(0,3)).chr(97+rand(0,3));
      }
      $tmp .= " ";
    }
    $result .= $tmp;
  }  
  if ($index) {
    return DFC_Write ($key, $result);
  } else {
    return $result;
  }
}

function N_PHPEscape ($string) {
  for ($i=0; $i<strlen($string); $i++) {
    $value = dechex(ord (substr ($string, $i, 1)));
    if (strlen($value)<2) $value = "0".$value;
    $result .= "\\x$value";
  }
  return $result;
}

function TEST_DFC ($testsize = 100)
{ 
  for ($i=0; $i<$testsize; $i++) $key[$i] = DFC_Key ("TEST_DFC $i");
  for ($i=0; $i<10; $i++) $value[$i] = "$i-".str_repeat ("*", 10000*$i);
  for ($i=10; $i<$testsize; $i++) $value[$i] = "$i";
  for ($i=0; $i<$testsize; $i++) DFC_Delete ($key[$i]);
  for ($i=0; $i<$testsize; $i++) if (DFC_Exists ($key[$i], $i)) echo "ERROR EXISTS ";
  for ($i=0; $i<$testsize; $i++) DFC_Write ($key[$i], $value[$i]);
  for ($i=0; $i<$testsize; $i++) if (!DFC_Exists ($key[$i], $i)) echo "ERROR !EXISTS ";
  for ($i=0; $i<$testsize; $i++) if (DFC_Read ($key[$i]) != $value[$i]) {
    echo "ERROR $key[$i] VALUE ($i) <br>IS: [".DFC_Read ($key[$i])."]<br> SHOULDBE: [".$value[$i]."]<br>";
  }
  echo "TEST COMPLETED<br>";
} 

function TEST_JAVA()
{
  $java["log"] = "yes";
  $java["command"]["1"] = "ask";
  $java["params"]["1"]["title"] = "Vraag " . N_GUID();
  $java["params"]["1"]["question"] = "Weet u het zeker? 01234567890123456789012345678901234567890123456789";
  $java["params"]["1"]["okstring"] = "OK";
  $java["params"]["1"]["cancelstring"] = "Cancel";

  $java["command"]["2"] = "ask";
  $java["params"]["2"]["title"] = "Vraag " . N_GUID();
  $java["params"]["2"]["question"] = "Weet u het zeker? 01234567890123456789012345678901234567890123456789";
  $java["params"]["2"]["okstring"] = "OK";
  $java["params"]["2"]["cancelstring"] = "Cancel";

  $java["command"]["3"] = "showmessage";
  $java["params"]["3"]["title"] = "Bericht" . N_GUID();
  $java["params"]["3"]["message"] = "U heeft 2 maal OK gekozen! 01234567890123456789012345678901234567890123456789";
  $java["params"]["3"]["okstring"] = "OK";

  $java["command"]["4"] = "evaljavascript";
  $java["params"]["4"]["script"] = "document.getElementById('status').innerHTML = 'Test 1 2 3'";

  $java["command"]["5"] = "evaljavascript";
  $java["params"]["5"]["script"] = "function handleError() {return true;} window.onerror = handleError;";

  $java["command"]["6"] = "download";
  $java["params"]["6"]["dir"] = "\\demo_sites\\preview\\objects\\4fef93e1c8bcaa354efad67d461f5a91\\";
  $java["params"]["6"]["file"] = "document.doc";
  $java["params"]["6"]["subs"] = "no";

  $java["command"]["7"] = "edit";
  $java["params"]["7"]["executable"] = "winword.exe";
  $java["params"]["7"]["dir"] = "\\demo_sites\\preview\\objects\\4fef93e1c8bcaa354efad67d461f5a91\\";
  $java["params"]["7"]["file"] = "document.doc";

  $java["command"]["8"] = "upload";
  $java["params"]["8"]["dir"] = "\\demo_sites\\preview\\objects\\4fef93e1c8bcaa354efad67d461f5a91\\";
  $java["params"]["8"]["file"] = "document.doc";
  $java["params"]["8"]["subs"] = "no";

  $java["command"]["9"] = "showmessage";
  $java["params"]["9"]["title"] = "Bericht" . N_GUID();
  $java["params"]["9"]["message"] = "U heeft 3 maal OK gekozen! 01234567890123456789012345678901234567890123456789";
  $java["params"]["9"]["okstring"] = "OK";

//  $java["command"]["10"] = "evaljavascript";
//  $java["params"]["10"]["script"] = "window.opener.location.href = window.opener.location.href";

//  $java["command"]["11"] = "evaljavascript";
//  $java["params"]["11"]["script"] = "window.close()";

  $url = JAVA_TransURL ($java);

  echo N_GUID()."<br>";
  echo "<a href=\"$url\">test</a>";
}

function TEST_DHTML ()
{
  echo DHTML_PrepRPC();
  $form = array();
  $code = '
    echo DHTML_EmbedJavaScript (DHTML_Master_SetDynamicObject ("test", N_Random($input)));
  ';
  $url = DHTML_RPCURL ($code, 1000);
  echo DHTML_InvisiTable ("<font face=\"arial\" size=\"2\">", "</font>", "[", DHTML_DynamicObject ("???", "test"), "]<a href=\"$url\">test</a>");
}

function TEST_Generate_InPlaceTable ($id)
{
  $t = N_GUID();
  $specs["td-head-align"]="center";
  $specs["td-std-align"]="center";
  $specs["sort"]="test$t";
  $specs["sort_1"]="auto";
  $specs["sort_2"]="auto";
  $specs["sort_3"]="auto";
  $specs["sort_4"]="auto";
  $specs["sort_5"]="auto";
  $specs["sort_7"]="auto";
  $specs["sort_map_7"]=8;
  $specs["sort_10"]="auto";
  $specs["sort_bottomskip"] = 1;
  $specs["no_reload"] = "yes";
  $specs["maxrows"] = 5;
  $specs["sortlinkcss"] = "ims_test";

  T_Start ("black", $specs);
  echo "Col1";
  T_Next();
  echo "Col2";
  T_Next();
  echo "Col3";
  T_Next();
  echo "Col4";
  T_Next();
  echo "Col5";
  T_Next();
  echo "Col6";
  T_Next();
  echo "Col7";
  T_Next();
  T_Next();
  T_Next();
  echo "ColX";
  T_NewRow();
  for ($i=0; $i<7; $i++) {
    echo N_VisualDate (time()-N_Random(10000000), true, true);
    T_Next();
    echo N_VisualDate (time()-N_Random(10000000), true, false);
    T_Next();
    echo N_VisualDate (time()-N_Random(10000000), false, true);
    T_Next();
    echo N_VisualDate (time()-N_Random(10000000), false, false);
    T_Next();
    for ($j=0; $j<N_Random (30); $j++) {
      if (N_Random(2)==1) {
        echo chr (N_Random(26)+ord(A)-1);
      } else {
        echo chr (N_Random(26)+ord(a)-1);
      }
    }   
    for ($k=0; $k<5; $k++) {
    T_Next();
    echo N_Random(1000);
    }
    T_NewRow();
  }
  $code = '
    uuse ("test");
    $dyn = TEST_Generate_InPlaceTable ("'.$id.'");
    echo DHTML_EmbedJavaScript (DHTML_Master_SetDynamicObject ("'.$id.'", $dyn));
  ';
  $url = DHTML_RPCURL ($code);
  echo "<a href=\"$url\">TEST 1 2 3</a>";
  return TS_End();
}

function TEST_InPlaceTable()
{
  $id = "test".N_GUID();
    $css = '
      <STYLE type="text/css"><!--
        A.ims_test:link{color: #000000; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}
        A.ims_test:visited{color: #000000; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}
        A.ims_test:hover{color: #FF0000; text-decoration: underline; font-family: Arial, Helvetica, sans-serif; font-size: 13px;}
      --></STYLE>
    ';
  echo $css.DHTML_DynamicObject (TEST_Generate_InPlaceTable ($id), $id);
}

function TEST_Tables_DoTest($mode, $specs="")
{
  T_Start($mode, $specs);
  echo "Head1";
  T_Next();
  echo "Head2";
  T_Next();
  echo "Head3";
  T_NewRow();

  echo "Row1Col1";
  T_Next();
  echo "Row1Col2";
  T_Next();
  echo "Row1Col3";
  T_NewRow();

  T_Next();
  echo '<a href="">Row2Col1</a>';
  T_Next();
  T_NewRow();

  echo "Row3Col1";
  T_NewRow();
  TE_End();
}

function TEST_Tables()
{
  for ($t=1; $t<3; $t++) {

  if ($t==1) {
    ML_SetLanguage ("nl");
  } else {
    ML_SetLanguage ("uk");
  }

  $specs["td-head-align"]="center";
  $specs["td-std-align"]="center";
  $specs["sort"]="test$t";
  $specs["sort_1"]="auto";
  $specs["sort_2"]="auto";
  $specs["sort_3"]="auto";
  $specs["sort_4"]="auto";
  $specs["sort_5"]="auto";
  $specs["sort_7"]="auto";
  $specs["sort_map_7"]=8;
  $specs["sort_10"]="auto";

  T_Start ("dynamic", $specs); // qqqq
  echo "Col1";
  T_Next();
  echo "Col2";
  T_Next();
  echo "Col3";
 
  $specs = array();
  $specs["td-init"] = "hallo";
  $specs["td-exit"] = "daar";
  $specs["td-any"] = " style=\"border: 10px solid #ff0000\"";
  T_Next($specs); // qqqq

  echo "Col4";
  T_Next();
  echo "Col5";
  T_Next();
  echo "Col6";
  T_Next();
  echo "Col7";
  T_Next();
  T_Next();
  T_Next();
  echo "ColX";
  T_NewRow();
  for ($i=0; $i<7; $i++) {
    echo N_VisualDate (time()-N_Random(10000000), true, true);
    T_Next();
    echo N_VisualDate (time()-N_Random(10000000), true, false);
    T_Next();
    echo N_VisualDate (time()-N_Random(10000000), false, true);
    T_Next();
    echo N_VisualDate (time()-N_Random(10000000), false, false);
    T_Next();
    for ($j=0; $j<N_Random (30); $j++) {
      if (N_Random(2)==1) {
        echo chr (N_Random(26)+ord(A)-1);
      } else {
        echo chr (N_Random(26)+ord(a)-1);
      }
    }   
    for ($k=0; $k<5; $k++) {
    T_Next();
    echo N_Random(1000);
    }
    T_NewRow();
  }
  TE_End();
  echo "<br>";

  }


  T_Start ("black", array("extra-table-props" => 'width="80%"', "td-head-align"=>"center", "td-std-align"=>"center"));
  for ($i=1; $i<=3; $i++) {
    echo "default<br>";
    T_Next();
    echo "ims<br>";
    T_NewRow();
    TEST_Tables_DoTest ("default");
    T_Next();
    TEST_Tables_DoTest ("ims");
    T_Next();
    TEST_Tables_DoTest ("black", array("noheader" => true));
    T_NewRow(); 
  }
  TE_End();
}

global $tx;

global $sorttest;
$sorttest = array (
  3*pow (10, -25),
  3*pow (10, -26),
  3*-pow (10, -25),
  3*-pow (10, -26),
  pow (10, -25),
  pow (10, -26),
  -pow (10, -25),
  -pow (10, -26),
  3*pow (10, 25),
  3*pow (10, 26),
  3*-pow (10, 25),
  3*-pow (10, 26),
  pow (10, 25),
  pow (10, 26),
  -pow (10, 25),
  -pow (10, 26),
  0.000000000000001, 
  -0.000000000000001,
  pow (3321, 2),
  pow (3321, 5),
  -pow (3321, 2),
  -pow (3321, 5),
  pow (3321, -2),
  pow (3321, -5),
  -pow (3321, -2),
  -pow (3321, -5),
  0,
  1,
  -1,
  10,
  -10,
  10000000000001,
  -10000000000001,
  100000000000100000000000000000000000000000000000000,
  -100000000000100000000000000000000000000000000000000,
  0.1,
  -0.1,
  0.10000000001,
  -0.10000000001,
  0.000000000000000000000000000000000000000000000001001,
  -0.000000000000000000000000000000000000000000000001001,
  7,
  79,
  799,
  7999,
  79999,
  799999,
  7999999,
  79999999,
  799999999,
  7999999999,
  79999999999,
  799999999999,
  7999999999999,
  79999999999999,
  799999999999999,
  7999999999999999,
  79999999999999999,
  "aap",
  "noot",
  "mies",
  "boom",
  "kat",
  1078669886,
  1078669816,
  1078669186,
  1078661886,
  -1078669886,
  -1078669816,
  -1078669186,
  -1078661886
);


function TEST_MetaBase_INDEX ($table="local_test")
{
  MB_DeleteTable ($table);
  $start = N_MIcroTime();
  MB_REP_Save ($table, "test", "test");
  for ($i=0; $i<100; $i++) {
    $t1 = md5(serialize(MB_TurboMultiQuery ($table, array ("select" => array ('$record.substr ("t1'.$i.'", 0, 1)' => "testt")))));
    $t2 = md5(serialize(MB_TurboMultiQuery ($table, array ("select" => array ('$record.substr ("t1'.$i.'", 0, 1)' => "testq")))));
    $t3 = md5(serialize(MB_TurboMultiQuery ($table, array ("select" => array ('$record.substr ("t2'.$i.'", 0, 1)' => "testq")))));
    $t4 = md5(serialize(MB_TurboMultiQuery ($table, array ("select" => array ('$record.substr ("t2'.$i.'", 0, 1)' => "testt")))));
    if ($t1 != "9630c1efb63ffa0db8d2c94163484284") N_DIE ("boem");
    if ($t2 != "40cd750bba9870f18aada2478b24840a") N_DIE ("boem");
    if ($t3 != "40cd750bba9870f18aada2478b24840a") N_DIE ("boem");
    if ($t4 != "9630c1efb63ffa0db8d2c94163484284") N_DIE ("boem");
  }
  echo (int)(1000 *(N_MIcroTime() - $start))."ms (create $i indexes)<br>";
  $start = N_MIcroTime();
  for ($j=0; $j<100; $j++) {
    MB_REP_Save ($table, "test$j", "test$j");  
  }
  echo (int)(1000 *(N_MIcroTime() - $start))."ms (create $j objects)<br>";
  echo "TEST OK<br>";
  $list = MB_INDEX_MGT_Get ($table);
  foreach ($list as $expr => $key) {
    echo $key." ".$expr."<br>";
  }

}

function TEST_MetaBase($table="local_test") // qqqqqq
{
  echo "<b>TEST_MetaBase test empty table handling (no crash = good news)</b><br>"; N_FLush (-1); 
  $table = "local_test_".N_GUID();
  MB_DeleteTable ($table);
  MB_TurboMultiQuery ($table, array ("select" => array ("true"=>"true")));
  MB_DeleteTable ($table);
  echo "<b>TEST_MetaBase_Prep</b><br>"; N_FLush (-1); TEST_MetaBase_Prep ($table); N_Flush(-1);
  MB_CAC_FlushAndClean ();
  echo "<b>TEST_MetaBase_Cycle</b><br>"; N_FLush (-1); TEST_MetaBase_Cycle (0, 84759684756984569, $table); N_Flush(-1);
  MB_CAC_FlushAndClean ();
  echo "<b>TEST_MetaBase_Cycle</b><br>"; N_FLush (-1); TEST_MetaBase_Cycle (0, 84759684756984569, $table); N_Flush(-1);
  MB_CAC_FlushAndClean ();
  echo "<b>TEST_MetaBase_Scramble</b><br>"; N_FLush (-1); TEST_MetaBase_Scramble($table); N_Flush(-1);
  MB_CAC_FlushAndClean ();
  echo "<b>TEST_MetaBase_Cycle</b><br>"; N_FLush (-1); TEST_MetaBase_Cycle (0, 84759684756984569, $table); N_Flush(-1);
}


function TEST_MetaBase_Scramble($table="local_test")
{
  for ($i=0; $i<=9; $i++) {
    for ($j=0; $j<=9; $j++) {
      for ($k=0; $k<=9; $k++) {
        MB_Delete ($table, "key$i$j$k");
      }
    }
  }
  MB_Flush();
  for ($i=0; $i<=9; $i++) {
    for ($j=0; $j<=9; $j++) {
      for ($k=0; $k<=9; $k++) {
        MB_Save ($table, "key$i$j$k", array ("x"=>"x", "i"=>$i, "j"=>$j, "k"=>$k, "#ijk#" => $i.$j.$k));
      }
    }
  }
  MB_Flush();
}

function TEST_MetaBase_Prep ($table="local_test")
{
  echo "Deleting tables<br>"; N_FLush(-1);
  MB_DeleteTable ($table);
  MB_DeleteTable ($table."2");
  MB_DeleteTable ($table."3");
  MB_DeleteTable ($table."4");
  MB_DeleteTable ($table."5");

  echo "Filling Tables<br>"; N_FLush(-1);


  for ($i=0; $i<=9; $i++) {
    for ($j=0; $j<=9; $j++) {
      for ($k=0; $k<=9; $k++) {
        MB_Save ($table, "key$i$j$k", array ("x"=>"x", "i"=>$i, "j"=>$j, "k"=>$k, "#ijk#" => $i.$j.$k));
      }
    }
  }

  for ($i=0; $i<256; $i++) {
    MB_Save ($table."2", "CHAR#$i", array ("type"=>"char", "value"=>strtoupper("C".chr($i).chr($i).chr($i)."-CHAR#$i")));
    MB_Save ($table."2", "INT#$i", array ("type"=>"int", "value"=>$i));
    MB_Save ($table."2", "DELME#$i", array ("type"=>"del", "value"=>$i));
  }

  global $sorttest;
  foreach ($sorttest as $i => $value) {
    MB_Save ($table."2", "SORT#$i", array ("type"=>"sort", "value"=>$value));
  }
  MB_Flush();
  for ($i=0; $i<256; $i+=2) {
    MB_Delete ($table."2", "DELME#$i");
  }

  $long = ""; for ($j=0; $j<1000; $j++) $long .= "#";
  for ($i=0; $i<2560; $i++) {
	  if (strlen($i) == 1) $entry = "000".$i;
	  if (strlen($i) == 2) $entry = "00".$i;
	  if (strlen($i) == 3) $entry = "0".$i;
	  if (strlen($i) == 4) $entry = $i;
    MB_Save ($table."3", "LONG#$i", $long.$entry);
  }
  $long = ""; for ($j=0; $j<130; $j++) $long .= "a";
  $entry = $long."string".$long;
  MB_Save($table."3", "key", $entry);

  for ($i=0; $i<256; $i++) $tobeimproved .= chr($i);
  MB_Save($table."3", "tobeimproved", $long.$tobeimproved.$long);

  $array = array("Berlin", "Paris", "London", "Rome", "Madrid");
  MB_Save ($table."4", "arraytest", $array);
  MB_Save ($table."4", "number", 123);
  MB_Save ($table."4", "string", "abc");
  MB_Save ($table."4", "float", 12345.6789);
  MB_Save ($table."4", "booleantrue", true);
  MB_Save ($table."4", "complextype", array(
    "a" => 123,
    "b" => 123.456,
    "c" => 123.456E12,
    "d" => false,
    "e" => true,
    "f" => 0,
    "g" => "",
    "h" => null,
    "i" => array(),
    "j" => array("a"=>array("a"=>array("a"=>array("a"=>array("a"=>array("a"=>array("a"=>array("a"=>array("a"=>array("a"=>array("a"=>"b")))))))))))
  ));

  for ($i=0; $i<11000; $i++) {  
    MB_Save ($table."5", "INT#$i", array ("type"=>"int", "value"=>$i));  
  }

}

function TEST_MetaBase_One ($table, $no, $desc, $code, $check)
{
  global $tx;
  global $ffrom, $tto;
  if ($no<$ffrom) return;
  if ($no>$tto) return;
  echo "TEST #$no ($desc): ";
  N_FLush(-1);
  $start = N_Microtime();
  eval ("\$set = $code;");

  // thb 2008-01-30 patch to correct results for test 27: MB_TurboFilter
  // this tests returns filtered records. Default the order is the order in which are in the database.
  // with "segmentedindex" the order is random. This patch orders the result.
  if($no==27) ksort($set);

  $tx[$no]=$set;
  $end = N_Microtime();

  $md5 = md5 (serialize ($set));
  if (strpos (" ".$check, $md5)) {
    echo "OK <b>".(int)(($end-$start)*1000)." ms</b> <br>";
  } else {
    echo "<b>ERROR</b><br>";
    T_EO ($md5);
    T_EO ($set);
  }
  N_FLush(-1);
}

function TEST_MetaBase_Cycle ($from=0, $to=84759684756984569, $table="")
{
  global $ffrom, $tto;

  if ($from && $to==84759684756984569) $to=$from;
  $ffrom= $from;
  $tto = $to;

  if (!($table)) $table ="local_test";
  $engine = MB_MUL_Engine($table);  
  echo "Engine: $engine <br/>";

  TEST_MetaBase_One ($table,  1, "MB_Query all with key sort", 'MB_Query ($table, "", \'$key\')', "054ed95ba979e843465962e4fb99ab1d");
  TEST_MetaBase_One ($table,  2, "MB_Query all with reverse key sort", 'MB_Query ($table, "", \'MB_Invert($key)\')', "664f409199371ad6b2b9d335392005dc");
  TEST_MetaBase_One ($table,  3, "MB_Query all with value sort", 'MB_Query ($table, "", \'$record["#ijk#"]\')', "03f49f56cf6afdb3a7b1afa38fbfa520");

  TEST_MetaBase_One ($table,  4, "MB_SelectQuery (blank) all with key sort", 'MB_SelectQuery ($table, null, \'$key\')', "054ed95ba979e843465962e4fb99ab1d");
  TEST_MetaBase_One ($table,  5, "MB_TurboSelectQuery (blank) all with key sort", 'MB_TurboSelectQuery ($table, null, \'$key\')', "054ed95ba979e843465962e4fb99ab1d");

  TEST_MetaBase_One ($table,  6, "MB_SelectQuery (single) all with key sort", 'MB_SelectQuery ($table, \'$record["x"]\', "x", \'$key\')', "054ed95ba979e843465962e4fb99ab1d");
  TEST_MetaBase_One ($table,  7, "MB_TurboSelectQuery (single) all with key sort", 'MB_TurboSelectQuery ($table, \'$record["x"]\', "x", \'$key\')', "054ed95ba979e843465962e4fb99ab1d");

  TEST_MetaBase_One ($table,  8, "MB_SelectQuery (single array element) all with key sort", 'MB_SelectQuery ($table, array (\'$record["x"]\' => "x"), \'$key\')', "054ed95ba979e843465962e4fb99ab1d");
  TEST_MetaBase_One ($table,  9, "MB_TurboSelectQuery (single array element) all with key sort", 'MB_TurboSelectQuery ($table, array (\'$record["x"]\' => "x"), \'$key\')', "054ed95ba979e843465962e4fb99ab1d");

  TEST_MetaBase_One ($table, 10, "MB_SelectQuery (single array element) 10% with key sort", 'MB_SelectQuery ($table, \'$record["i"]\', 3, \'$key\')', "1742ae2fd4fda5d782e5b72ecbbf219c");
  TEST_MetaBase_One ($table, 11, "MB_TurboSelectQuery (single array element) 10% with key sort", 'MB_TurboSelectQuery ($table, \'$record["i"]\', 3, \'$key\')', "1742ae2fd4fda5d782e5b72ecbbf219c");

  TEST_MetaBase_One ($table, 12, "MB_SelectQuery (double array elements) 1% with key sort", 'MB_SelectQuery ($table, array (\'$record["j"]\' => 3, \'$record["k"]\' => 7), \'$key\')', "0b73b97a15acec385589e6104c241c1a");
  TEST_MetaBase_One ($table, 13, "MB_TurboSelectQuery (double array elements) 1% with key sort", 'MB_TurboSelectQuery ($table, array (\'$record["j"]\' => 3, \'$record["k"]\' => 7), \'$key\')', "0b73b97a15acec385589e6104c241c1a");

  TEST_MetaBase_One ($table, 14, "MB_SelectQuery (double array elements) 1% with value sort", 'MB_SelectQuery ($table, array (\'$record["j"]\' => 3, \'$record["k"]\' => 7), \'$record["#ijk#"]\')', "898a25862a8e5f25ce3048a152e4c5e2");
  TEST_MetaBase_One ($table, 15, "MB_TurboSelectQuery (double array elements) 1% with value sort", 'MB_TurboSelectQuery ($table, array (\'$record["j"]\' => 3, \'$record["k"]\' => 7), \'$record["#ijk#"]\')', "898a25862a8e5f25ce3048a152e4c5e2");

  TEST_MetaBase_One ($table, 16, "MB_SelectQuery (double array elements, one of them boolean expression) 1% with value sort)", 'MB_SelectQuery ($table, array (\'$record["j"]==3\' => true, \'$record["k"]\' => 7), \'$record["#ijk#"]\')', "898a25862a8e5f25ce3048a152e4c5e2");
  TEST_MetaBase_One ($table, 17, "MB_TurboSelectQuery (double array elements, one of them boolean expression) 1% with value sort)", 'MB_TurboSelectQuery ($table, array (\'$record["j"]==3\' => true, \'$record["k"]\' => 7), \'$record["#ijk#"]\')', "898a25862a8e5f25ce3048a152e4c5e2");

  TEST_MetaBase_One ($table, 18, "MB_SelectQuery (double obsolete) 1% with value sort", 'MB_SelectQuery ("'.$table.'", array (\'$record["k"]\', \'$record["j"]\'), array (7, 3), \'$record["#ijk#"]\')', "898a25862a8e5f25ce3048a152e4c5e2");
  TEST_MetaBase_One ($table, 19, "MB_TurboSelectQuery (double obsolete) 1% with value sort", 'MB_TurboSelectQuery ("'.$table.'", array (\'$record["k"]\', \'$record["j"]\'), array (7, 3), \'$record["#ijk#"]\')', "898a25862a8e5f25ce3048a152e4c5e2");

  TEST_MetaBase_One ($table, 20, "MB_SelectQuery (double array elements, last of them range based) 2% with value sort", 'MB_SelectQuery ("'. $table. '", array (\'$record["k"]\' => 7, \'$record["j"]\' => array (3, 4)), \'$record["#ijk#"]\')', "d0f998868b723c670b7b5603e7adb8ca");
  TEST_MetaBase_One ($table, 21, "MB_TurboSelectQuery (double array elements, last of them range based) 2% with value sort", 'MB_TurboSelectQuery ("'. $table. '", array (\'$record["k"]\' => 7, \'$record["j"]\' => array (3, 4)), \'$record["#ijk#"]\')', "d0f998868b723c670b7b5603e7adb8ca");

  TEST_MetaBase_One ($table, 22, "MB_SelectQuery (double obsolete, last of them range based) 1% with value sort", 'MB_SelectQuery ($table, array (\'$record["k"]\', \'$record["j"]\'), array (7, array (3, 4)), \'$record["#ijk#"]\')', "d0f998868b723c670b7b5603e7adb8ca");
  TEST_MetaBase_One ($table, 23, "MB_TurboSelectQuery (double obsolete, last of them range based) 1% with value sort", 'MB_TurboSelectQuery ("'. $table. '", array (\'$record["k"]\', \'$record["j"]\'), array (7, array (3, 4)), \'$record["#ijk#"]\')', "d0f998868b723c670b7b5603e7adb8ca");

  TEST_MetaBase_One ($table, 24, "MB_TopQuery 2%", 'MB_TopQuery ("'.$table.'", \'$record["i"]+10*$record["j"]+100*$record["k"]\', 20)', "3f369d88da0779b9dca30261b68d795b");
  TEST_MetaBase_One ($table, 25, "MB_TurboTopQuery 2%", 'MB_TurboTopQuery ("'.$table.'", \'$record["i"]+10*$record["j"]+100*$record["k"]\', 20)', "3f369d88da0779b9dca30261b68d795b");

  TEST_MetaBase_One ($table, 26, "MB_Filter 5.4%", 'MB_Filter ($table, \'$record["#ijk#"]\', "4 7")', "382b3f94dd139ec4ca5fbe9a7e086f4c");
  TEST_MetaBase_One ($table, 27, "MB_TurboFilter 5.4%", 'MB_TurboFilter ("' . $table. '", \'$record["#ijk#"]\', "4 7")', "382b3f94dd139ec4ca5fbe9a7e086f4c");

  TEST_MetaBase_One ($table, 28, "MB_MultiQuery 1.4%", '
    MB_MultiQuery ($table, array (
      "select" => array (\'$record[i]\' => 3), 
      "range" => array (\'$record[k]\', 2, 8), 
      "filter" => array (\'$record["#ijk#"]\', "3 7"), 
      "rsort" => \'$record["#ijk#"]\', 
      "value" => \'"#".$record["#ijk#"]."#"\',
      "slice" => array (2, 15)));', 
  "fd59cf74ccecd495ae9351095fb6b14e");
  TEST_MetaBase_One ($table, 29, "MB_TurboMultiQuery 1.4%", '
    MB_TurboMultiQuery ($table, array (
      "select" => array (\'$record[i]\' => 3), 
      "range" => array (\'$record[k]\', 2, 8), 
      "filter" => array (\'$record["#ijk#"]\', "3 7"), 
      "rsort" => \'$record["#ijk#"]\', 
      "value" => \'"#".$record["#ijk#"]."#"\',
      "slice" => array (2, 15)));', 
  "fd59cf74ccecd495ae9351095fb6b14e");

  TEST_MetaBase_One ($table, 30, "MB_Query chars (sort)", 'MB_Query ("'. $table .'2", \'$record["type"]=="char"\', \'$record["value"]\');', "9eea7eaa20dc1cb209f63a643547f6ee / d42262828e338529fd608fc049bc4448");
  TEST_MetaBase_One ($table, 31, "MB_MultiQuery chars (sort)", '
    MB_MultiQuery ($table. "2", array (
      "select" => array (\'$record["type"]\' => "char"), 
      "sort" => \'$record["value"]\'
    ));',
  "9eea7eaa20dc1cb209f63a643547f6ee / d42262828e338529fd608fc049bc4448");
  TEST_MetaBase_One ($table, 32, "MB_TurboMultiQuery chars (sort)", 'MB_TurboMultiQuery ("'.$table. '2", array (
      "select" => array (\'$record["type"]\' => "char"), 
      "sort" => \'$record["value"]\'
    ));',
  "9eea7eaa20dc1cb209f63a643547f6ee / d42262828e338529fd608fc049bc4448");

  $ok = "26f15c3c8074049de21305c048aad5d6 / 15b0c25dc2f5ee753f186c5b647cbddc / 56e334278d2008c3ea7fd6c354ca8e35 / 86cd420dd84f979eb1c1f05882d2157e / 01fbb99f17ecedd53e2a135fcdbe8aec /0e672d3a6b7eb77ef67e8ed9552b3a70 / 9a9463aca6d13a1a3ca22a1c0b1ea7b6 / bab6dba5793d8cf7ce26f441a103b6a0";
  TEST_MetaBase_One ($table, 33, "MB_Query sort (sort)", 'MB_Query ("' . $table .'2", \'$record["type"]=="sort"\', \'$record["value"]\');', $ok);

  TEST_MetaBase_One ($table, 34, "MB_MultiQuery sort (sort)", 'MB_MultiQuery ("'.$table. '2", array (
      "select" => array (\'$record["type"]\' => "sort"), 
      "sort" => \'$record["value"]\'
    ));',
  $ok);
  TEST_MetaBase_One ($table, 35, "MB_TurboMultiQuery sort (sort)", 'MB_TurboMultiQuery ("'.$table. '2", array (
      "select" => array (\'$record["type"]\' => "sort"), 
      "sort" => \'$record["value"]\'
    ));',
  $ok);

  TEST_MetaBase_One ($table, 36, "MB_Query ints (sort)", 'MB_Query ("'.$table. '2", \'$record["type"]=="int"\', \'$record["value"]\');', "5d52ee510739548114ef73037e5bca8d");
  TEST_MetaBase_One ($table, 37, "MB_MultiQuery ints (sort)", 'MB_MultiQuery ("'.$table. '2", array (
      "select" => array (\'$record["type"]\' => "int"), 
      "sort" => \'$record["value"]\'
    ));',
  "5d52ee510739548114ef73037e5bca8d");
  TEST_MetaBase_One ($table, 38, "MB_TurboMultiQuery ints (sort)", 'MB_TurboMultiQuery ("'.$table. '2", array (
      "select" => array (\'$record["type"]\' => "int"), 
      "sort" => \'$record["value"]\'
    ));',
  "5d52ee510739548114ef73037e5bca8d");

  $ok = "f0804ad0da32560a7106efb5c11757ec / 07a27d3e7d75561a46972d315141389a";
  TEST_MetaBase_One ($table, 39, "MB_Query (del)", 'MB_Query ("'. $table .'2", \'\', \'$key\');', $ok);
  TEST_MetaBase_One ($table, 40, "MB_MultiQuery (del)", 'MB_MultiQuery ("'.$table. '2", array (
      "sort" => \'$key\'
    ));',
  $ok);
  TEST_MetaBase_One ($table, 41, "MB_TurboMultiQuery (del)", 'MB_TurboMultiQuery ("'.$table. '2", array (
      "sort" => \'$key\'
    ));',
  $ok);

  TEST_MetaBase_One ($table, 42, "MB_MultiQuery (light count) 80%", '
    MB_MultiQuery ($table, array (
      "range" => array (\'$record[k]\', 1, 8), 
      "slice" => "count"
    ));', 
  "92c01b456b9ace04c494219d5c81287b");

  TEST_MetaBase_One ($table, 43, "MB_TurboMultiQuery (light count) 80%", '
    MB_TurboMultiQuery ($table, array (
      "range" => array (\'$record[k]\', 1, 8), 
      "slice" => "count"
    ));', 
  "92c01b456b9ace04c494219d5c81287b");

  TEST_MetaBase_One ($table, 44, "MB_MultiQuery (heavy count) 80%", '
    MB_MultiQuery ($table, array (
      "range" => array (\'$record[k]\', 1, 8), 
      "filter" => array (\'$record["#ijk#"]."1234567"\', "1 2 3 4 5 6 7"), 
      "rsort" => \'$record["#ijk#"]\', 
      "value" => \'"#".$record["#ijk#"]."#"\',
      "slice" => "count"
    ));', 
  "92c01b456b9ace04c494219d5c81287b");

  TEST_MetaBase_One ($table, 45, "MB_TurboMultiQuery (heavy count) 80%", '
    MB_TurboMultiQuery ($table, array (
      "range" => array (\'$record[k]\', 1, 8), 
      "filter" => array (\'$record["#ijk#"]."1234567"\', "1 2 3 4 5 6 7"), 
      "rsort" => \'$record["#ijk#"]\', 
      "value" => \'"#".$record["#ijk#"]."#"\',
      "slice" => "count"
    ));', 
  "92c01b456b9ace04c494219d5c81287b");

  $long = ""; for ($j=0; $j<1000; $j++) $long .= "#";
  TEST_MetaBase_One ($table, 46, "MB_MultiQuery long", '
    MB_MultiQuery ($table."3", array (
      "range" => array (\'$record\', "'.$long."0040".'", "'.$long."0050".'"),
      "sort" => \'$key\', 
      "value" => \'"#".substr ($record, 1000)."#"\',
    ));', 
  "06e22248907b875f4b1c34304de22b00 / f82b42531df11b5b0b4351becc64317d");

  $long = ""; for ($j=0; $j<1000; $j++) $long .= "#";
  TEST_MetaBase_One ($table, 47, "MB_TurboMultiQuery long", '
    MB_TurboMultiQuery ($table. "3", array (
      "range" => array (\'$record\', "'.$long."0040".'", "'.$long."0050".'"),
      "sort" => \'$key\', 
      "value" => \'"#".substr ($record, 1000)."#"\',
    ));', 
  "06e22248907b875f4b1c34304de22b00 / f82b42531df11b5b0b4351becc64317d");

  TEST_MetaBase_One ($table, 48, "MB_MultiQuery select + slowselect", '
    MB_MultiQuery ($table, array (
      "select" => array (\'$record[i]\' => 3), 
      "slowselect" => array (\'$record[j]<>5\' => true), 
      "range" => array (\'$record[k]\', 2, 8), 
      "filter" => array (\'$record["#ijk#"]\', "6"), 
      "rsort" => \'$record["#ijk#"]\', 
      "value" => \'"#".$record["#ijk#"]."#"\',
      "slice" => array (2, 14)));', 
  "55ed46dacae3fae68bb865e41d3f2a1d");
  TEST_MetaBase_One ($table, 49, "MB_TurboMultiQuery select + slowselect", '
    MB_TurboMultiQuery ($table, array (
      "select" => array (\'$record[i]\' => 3), 
      "slowselect" => array (\'$record[j]<>5\' => true), 
      "range" => array (\'$record[k]\', 2, 8), 
      "filter" => array (\'$record["#ijk#"]\', "6"), 
      "rsort" => \'$record["#ijk#"]\', 
      "value" => \'"#".$record["#ijk#"]."#"\',
      "slice" => array (2, 14)));', 
  "55ed46dacae3fae68bb865e41d3f2a1d");

  TEST_MetaBase_One ($table, 50, "MB_MultiQuery count select + slowselect", '
    MB_MultiQuery ($table, array (
      "select" => array (\'$record[i]\' => 3), 
      "slowselect" => array (\'$record[j]<>5\' => true), 
      "range" => array (\'$record[k]\', 2, 8), 
      "filter" => array (\'$record["#ijk#"]\', "6"), 
      "rsort" => \'$record["#ijk#"]\', 
      "value" => \'"#".$record["#ijk#"]."#"\',
      "slice" => "count"));', 
  "3ae4e7e87b9038a299ee40119700914a");
  TEST_MetaBase_One ($table, 51, "MB_TurboMultiQuery count select + slowselect", '
    MB_TurboMultiQuery ($table, array (
      "select" => array (\'$record[i]\' => 3), 
      "slowselect" => array (\'$record[j]<>5\' => true), 
      "range" => array (\'$record[k]\', 2, 8), 
      "filter" => array (\'$record["#ijk#"]\', "6"), 
      "rsort" => \'$record["#ijk#"]\', 
      "value" => \'"#".$record["#ijk#"]."#"\',
      "slice" => "count"));', 
  "3ae4e7e87b9038a299ee40119700914a");


  TEST_MetaBase_One ($table, 52, "MB_MultiQuery 1.4% with slowselect", '
    MB_MultiQuery ($table, array (
      "slowselect" => array (\'$record[i]\' => 3), 
      "range" => array (\'$record[k]\', 2, 8), 
      "filter" => array (\'$record["#ijk#"]\', "3 7"), 
      "rsort" => \'$record["#ijk#"]\', 
      "value" => \'"#".$record["#ijk#"]."#"\',
      "slice" => array (2, 15)));', 
  "fd59cf74ccecd495ae9351095fb6b14e");
  TEST_MetaBase_One ($table, 53, "MB_TurboMultiQuery 1.4% with slowselect", '
    MB_TurboMultiQuery ($table, array (
      "slowselect" => array (\'$record[i]\' => 3), 
      "range" => array (\'$record[k]\', 2, 8), 
      "filter" => array (\'$record["#ijk#"]\', "3 7"), 
      "rsort" => \'$record["#ijk#"]\', 
      "value" => \'"#".$record["#ijk#"]."#"\',
      "slice" => array (2, 15)));', 
  "fd59cf74ccecd495ae9351095fb6b14e");
  $ok = "dff60aa6f11227dfec12f6015f0d313a";
  TEST_MetaBase_One ($table, 54, "Special object: array", "MB_Ref ($table .'4', 'arraytest');", $ok);
  TEST_MetaBase_One ($table, 55, "Special object: number", "MB_Ref ($table .'4', 'number');", "1d486638b18a1438c3f66bcf2ac0a867");
  TEST_MetaBase_One ($table, 56, "Special object: string", "MB_Ref ($table .'4', 'string');", "022335c6225c27b2efd2ba6a9329cb15");
  TEST_MetaBase_One ($table, 57, "Special object: float", "MB_Ref ($table .'4', 'float');", "c902d9f264dc5c87f68d0eba1f2c4f08");
  TEST_MetaBase_One ($table, 58, "Special object: booleantrue", "MB_Ref ($table .'4', 'booleantrue');", "431014e4a761ea216e9a35f20aaec61c");
  TEST_MetaBase_One ($table, 59, "Special object: complextype", "MB_Ref ($table .'4', 'complextype');", "33ef89e8df58c11f28ff0c40e3255919 / 027ec10dd4de30458865c314e8380f76");
  TEST_MetaBase_One ($table, 60, "AllTables", "strpos(strtoupper(serialize(MB_AllTables())), strtoupper($table)) != 0", "431014e4a761ea216e9a35f20aaec61c");
  TEST_MetaBase_One ($table, 61, "AllKeys", "MB_AllKeys($table)", "054ed95ba979e843465962e4fb99ab1d");
  TEST_MetaBase_One ($table, 62, "TableSize (1000)", "MB_MUL_TableSize($table)", "0886ff1b45cadf60cd91d63c4d980020 / e25ca08e5da0023ff9b6f02640a7bd5a");
  TEST_MetaBase_One ($table, 63, "TableSize (2562)", "MB_MUL_TableSize($table"."3)", "c79235644f3f779b81ca1520d830c8e3 / faa88a152bab3b88c124848967a7921c / 354ab558abc4fd982195341dd1dc97bf");
  if ($engine != "ULTRA") { // in ULTRA, key order is filesystem dependent
    TEST_MetaBase_One ($table, 64, "OneKey", "MB_MUL_OneKey($table, 123)", "cfcf2f1644761acf286aa560eea20862");
    TEST_MetaBase_One ($table, 65, "OneKey", "MB_MUL_OneKey($table, 1)", "2be0673dc1f24c0e394fc1c6677f0f0d");
    TEST_MetaBase_One ($table, 66, "OneKey", "MB_MUL_OneKey($table, 222)", "44b2633a60c52e01e564a65ed05fd055");
    TEST_MetaBase_One ($table, 67, "OneKey", "MB_MUL_OneKey($table, 1234)", "dcca48101505dd86b703689a604fe3c4");
    TEST_MetaBase_One ($table, 68, "OneKey", "MB_MUL_OneKey($table, 1000)", "2c2f57ebf43dcef1f4733bfb4bc42ad5");
    TEST_MetaBase_One ($table, 69, "KeyRange", "MB_MUL_KeyRange($table, 990, 1100)", "6149443da2142aaa9b524735feb8d48e");
    TEST_MetaBase_One ($table, 70, "KeyRange", "MB_MUL_KeyRange($table, 1, 10)", "2ae463847a82fc4626deecc7881f445c");
    TEST_MetaBase_One ($table, 71, "KeyRange", "MB_MUL_KeyRange($table, 10, 20)", "0657d8c3ceed8248a81587f4d5c3a248");
    TEST_MetaBase_One ($table, 72, "MultiLoad", "MB_REP_Multiload($table, Array('key000'=>'key000', 'key500'=>'key500', 'key999'=>'key999', 'key1234'=>'key1234'))", "cd9c4ff92dd9944ac7b909d238805e44");
  }

  TEST_MetaBase_One ($table, 73, "MB_MultiQuery select + slowselect + wherein", '
    MB_MultiQuery ($table, array (
      "select" => array (\'$record[i]\' => 3), 
      "slowselect" => array (\'$record[j]<>5\' => true), 
      "wherein" => array (\'$record[k]\'=>Array(2, 3, 5, 6, 8)), 
      "filter" => array (\'$record["#ijk#"]\', "6"), 
      "rsort" => \'$record["#ijk#"]\', 
      "value" => \'"#".$record["#ijk#"]."#"\',
      "slice" => array (2, 14)));', 
  "287881af173032dcca78dfacb6379e01 / 604c39d788d2d2409204bfe6750dc3d3");

  TEST_MetaBase_One ($table, 74, "MB_TURBO_MultiQuery select + slowselect + wherein", '
    MB_MultiQuery ($table, array (
      "turbo" => true, 
      "select" => array (\'$record[i]\' => 3), 
      "slowselect" => array (\'$record[j]<>5\' => true), 
      "wherein" => array (\'$record[k]\'=>Array(2, 3, 5, 6, 8)), 
      "filter" => array (\'$record["#ijk#"]\', "6"), 
      "rsort" => \'$record["#ijk#"]\', 
      "value" => \'"#".$record["#ijk#"]."#"\',
      "slice" => array (2, 14)));', 
  "287881af173032dcca78dfacb6379e01");

  $long = ""; for ($j=0; $j<130; $j++) $long .= "a"; 
  $entry = $long."string".$long;
  TEST_MetaBase_One ($table, 75, "MB_TurboMultiQuery filter + long + sort", '
    MB_TurboMultiQuery ($table. "3", array (
      "filter" => array (\'$record\', "'.$entry.'"),
      "sort" => \'$key\', 
      "value" => \'"#".substr(substr ($record, 130), 0, 6)."#"\',
    ));', 
  "d97295f8bb1ec21fe730e8ae3bd59f61");

  $long = ""; for ($j=0; $j<130; $j++) $long .= "a";
  $entry = $long."string".$long;
  TEST_MetaBase_One ($table, 76, "MB_MultiQuery filter + long + sort", '
    MB_MultiQuery ($table. "3", array (
      "filter" => array (\'$record\', "'.$entry.'"),
      "sort" => \'$key\', 
      "value" => \'"#".substr(substr ($record, 130), 0, 6)."#"\',
    ));', 
  "d97295f8bb1ec21fe730e8ae3bd59f61");

  TEST_MetaBase_One ($table, 77, "MB_TurboMultiQuery ints (sort, largetable)", 'MB_TurboMultiQuery ("'.$table. '5", array (  
      "select" => array (\'$record["type"]\' => "int"),  
      "sort" => \'$record["value"]\'  
    ));',  
  "e842115d02ca0ddc7960b113ad52784a");

//qqq   TEST_MetaBase_One ($table, , "", '', "");

  echo "INDEX LIST ($table):<br><code><font size=2>";
  $list = MB_INDEX_MGT_Get ($table);
  foreach ($list as $expr => $key) {
    echo $key." ".$expr."<br>";
  }
  echo "</font></code>";
  echo "INDEX LIST ($table"."2):<br><code><font size=2>";
  $list = MB_INDEX_MGT_Get ("local_test2");
  foreach ($list as $expr => $key) {
    echo $key." ".$expr."<br>";
  }
  echo "</font></code>";
  echo "INDEX LIST ($table"."3):<br><code><font size=2>";
  $list = MB_INDEX_MGT_Get ("local_test3");
  foreach ($list as $expr => $key) {
    echo $key." ".$expr."<br>";
  }
  echo "</font></code>";

  // Moved MySQL-dependent tests to the end because they cause Fatal Error for non-mysql engines.
  $long = ""; for ($j=0; $j<130; $j++) $long .= "a";
  $entry = $long."string".$long;
  TEST_MetaBase_One ($table, 78, "MB_TurboMultiQuery rawfilter + long + sort", '
    MB_TurboMultiQuery ($table. "3", array (
      "rawfilter" => array (\'$record\', "'.$entry.'"),
      "sort" => \'$key\', 
      "value" => \'"#".substr(substr ($record, 130), 0, 6)."#"\',
    ));', 
  "d97295f8bb1ec21fe730e8ae3bd59f61");

  $long = ""; for ($j=0; $j<130; $j++) $long .= "a";
  for ($i=0; $i<256; $i++) $tobeimproved .= chr($i);
  $entry =  $long.$tobeimproved.$long;
  $entry = str_replace ("\\", "%", $entry);
  $entry = str_replace ("[", "%", $entry);
  $entry = str_replace ("]", "%", $entry);
  TEST_MetaBase_One ($table, 79, "MB_MultiQuery rawfilter + long + sort", '
    MB_TurboMultiQuery ($table. "3", array (
      "rawfilter" => array (\'$record\', TEST_MetaBase_repQuot(mysql_escape_string("'.N_PHPEscape($entry).'"))),
      "sort" => \'$key\', 
      "value" => \'"#".$record."#"\',
    ));', 
  "e036e3ddfc58b2d0686a905e802256f9");

  TEST_MetaBase_One ($table, 80, "MB_TurboMultiQuery with leftfilter", '
    MB_TurboMultiQuery ($table, array (
      "leftfilter" => array (\'$record["#ijk#"]\', "37"), 
      "rsort" => \'$record["#ijk#"]\', 
      "value" => \'"#".$record["#ijk#"]."#"\'));',
  "5788761021fce298935bf89cdb481809");

}

function TEST_MetaBase_repQuot($input)
{
	global $myconfig;

        if ($myconfig["sdexengine"] == "MYSQL" || $myconfig["sdexengine"] == "MYSQL2" || ($myconfig["sdexengine"] == "ADODB" && $myconfig["adodb"]["DBTYPE"] == "mysql"))
		return $input;
	else 
		return str_replace("'", "''", $input);
}

function TEST_Prep ()
{
  MB_MYSQL_Connect ();
  $table = "test";
  global $myconfig;
  $statement = "
    (thekey varchar(64),
     theword varchar(64),
     therank INT,
     INDEX thekey_index (thekey),
     INDEX theword_index (theword),
     INDEX theword_therank_index (theword, therank),
     UNIQUE combined_index (thekey, theword))
  ";
  MB_MYSQL_Query ("drop table ftindex_".MB_MYSQL_Key2Name($table).";", 1);
  if ($myconfig["xmlmysql"]["tabletype"]) {
    MB_MYSQL_Query("CREATE TABLE ftindex_".MB_MYSQL_Key2Name_Remember($table)." $statement ENGINE=".$myconfig["xmlmysql"]["tabletype"].";");
  } else {
    MB_MYSQL_Query("CREATE TABLE ftindex_".MB_MYSQL_Key2Name_Remember($table)." $statement;");
  }
}

function TEST_Multi1()
{
  MB_MYSQL_Connect ();
  $table = "test";
  for ($i=0; $i<1000; $i++) {
    $key = "k$i";
    $word = "w$i";
    $rank = $i;
    MB_MYSQL_Query ("insert into ftindex_".MB_MYSQL_Key2Name($table)."(thekey, theword, therank) VALUES ('".mysql_escape_string($key)."', '".mysql_escape_string($word)."', $rank);");
  }  
}

function TEST_Multi2()
{
  for ($i=0; $i<1000; $i++) {
    N_WriteFile ("/tmp/$i.tst", "$i$i$i$i$i$i$i$i$i$i$i");
  }  
}

function TEST_Compare ($t1, $t2)
{
  $i=0;
  foreach ($t1 as $key => $value) {
    $c1[$i] = $key;
    $c2[$i] = N_XML2HTML (N_Object2XML ($value));
    $i++;
  }
  $count = $i;
  $i=0;
  foreach ($t2 as $key => $value) {
    $c3[$i] = $key;
    $c4[$i] = N_XML2HTML (N_Object2XML ($value));
    $i++;
  }
  if ($i>$count) $count = $i;
  T_Start("ims");
  echo "Key #1"; T_Next();
  echo "Value #1 (".md5(serialize($t1)).")"; T_Next();
  echo "Key #2"; T_Next();
  echo "Value #2 (".md5(serialize($t2)).")"; T_Newrow();
  for ($i=0; $i<$count; $i++) {
    if ($c1[$i]!=$c3[$i]) {
      echo "<b>".$c1[$i]."</b>"; T_Next();
      echo "<b>".$c2[$i]."</b>"; T_Next();
      echo "<b>".$c3[$i]."</b>"; T_Next();
      echo "<b>".$c4[$i]."</b>"; T_NewRow();
    } else {
      echo $c1[$i]; T_Next();
      echo $c2[$i]; T_Next();
      echo $c3[$i]; T_Next();
      echo $c4[$i]; T_NewRow();
    }
  }
  TE_End();
}

function TEST_CompactDump ($array)
{
  asort ($array);
  foreach ($array as $key => $val) {
    echo "\"<b>$key</b>\"=>\"<b>$val</b>\" ";
  }
  echo "<br>";
}

function TEST_CopyTable ()
{
  MB_REP_DeleteTable ("local_test_copy_from");
  MB_REP_DeleteTable ("local_test_copy_to");

  MB_REP_Save ("local_test_copy_from", "A", "1"); // add
  MB_REP_Save ("local_test_copy_from", "B", "2"); // do nothing
  MB_REP_Save ("local_test_copy_from", "C", "3"); // change

  MB_REP_Save ("local_test_copy_to", "B", "2"); // do nothing
  MB_REP_Save ("local_test_copy_to", "C", "9"); // change
  MB_REP_Save ("local_test_copy_to", "D", "9"); // delete

  TEST_CompactDump (MB_REP_MultiLoad ("local_test_copy_from", MB_REP_AllKeys ("local_test_copy_from")));
  TEST_CompactDump (MB_REP_MultiLoad ("local_test_copy_to", MB_REP_AllKeys ("local_test_copy_to")));

  MB_REP_CopyTable ("local_test_copy_to", "local_test_copy_from");

  TEST_CompactDump (MB_REP_MultiLoad ("local_test_copy_from", MB_REP_AllKeys ("local_test_copy_from")));
  TEST_CompactDump (MB_REP_MultiLoad ("local_test_copy_to", MB_REP_AllKeys ("local_test_copy_to")));

  MB_REP_CopyTable ("local_test_copy_to", "local_test_copy_from");

  TEST_CompactDump (MB_REP_MultiLoad ("local_test_copy_from", MB_REP_AllKeys ("local_test_copy_from")));
  TEST_CompactDump (MB_REP_MultiLoad ("local_test_copy_to", MB_REP_AllKeys ("local_test_copy_to")));
}

function TEST_AutoTables ()
{
  echo SKIN_CSS();
  IMS_SetSuperGroupName ("osict_sites");
  echo TABLES_Auto (array (
    "name" => "testhypertable",
    "style" => "ims",
    "filter" => "feb 2003",
    "maxlen" => 3,
    "table" => "ims_osict_sites_objects",
    "select" => array (
      '$record["directory"]' => "33e5a52efae28477d60b4399a397fd22",
      '$record["published"]=="yes" || $record["preview"]=="yes"' => true
    ),
    "tablespecs" => array (
      "sort_default_col" => 1, "sort_default_dir" => "u", 
      "sort_map_1" => 2, 
      "sort_1"=>"auto", "sort_3"=>"auto", "sort_4"=>"date", "sort_5"=>"auto"
    ),
    "tableheads" => array ("Document", "", "Status", "Laatst gewijzigd", "Toegewezen"),
    "sort" => array (
      '',
      '$record["shorttitle"]',
      'SHIELD_CurrentStageName (IMS_SuperGroupName(), $key)',
      'eval (\'
        if (is_array($record["history"])) {
          reset ($record["history"]);
          while (list($k, $data)=each($record["history"])) {
            $time = $data["when"];
          }
          return $time; 
        }
      \')',
      'eval (\'
        if ($record["allocto"]) {
          $user_id = $record["allocto"];
          $user = MB_Ref ("shield_".IMS_SuperGroupName()."_users", $user_id);     
          return $user["name"];
        }
      \')'
    ),
    "filterexp" => '
      $record["shorttitle"] . " " .
      SHIELD_CurrentStageName (IMS_SuperGroupName(), $key) . " " .
      N_VisualDate (eval (\'
        if (is_array($record["history"])) {
          reset ($record["history"]);
          while (list($k, $data)=each($record["history"])) {
            $time = $data["when"];
          }
          return $time; 
        }
      \'), 1, 1) . " " .
      eval (\'
        if ($record["allocto"]) {
          $user_id = $record["allocto"];
          $user = MB_Ref ("shield_".IMS_SuperGroupName()."_users", $user_id);     
          return $user["name"];
        }
      \')
    ',
    "content" => array (
      '
        $image = FILES_Icon (IMS_SuperGroupName(), $key); 
        $url = FILES_TransEditURL (IMS_SuperGroupName(), $key);
        $viewurl = FILES_TransViewPreviewURL ($supergroupname, $key);
        $title = ML("Wijzig","Edit")." &quot;".$sortvalue."&quot; (".$object["longtitle"].")";
        if (SHIELD_HasObjectRight (IMS_SuperGroupName(), $key, "edit")) {
          echo "<a title=\"$title\" href=\"$url\">";
          echo "<img border=0 height=16 width=16 src=\"$image\">";
          echo "</a>";
        } else {
          echo "<img border=0 height=16 width=16 src=\"$image\">";
        }
        echo "&nbsp;<a title=\"$viewtitle\" href=\"$viewurl\">";
        echo "<img border=0 height=16 width=16 src=\"/openims/view.gif\">";
        echo "</a>";
      ',
      '
        $commandurl = FILES_DMSURL (IMS_SuperGroupName(), $key);
        $commandtitle = ML("Acties voor","Actions for")." &quot;".$sortvalue."&quot; (".$object["longtitle"].")"; 
        echo "<a target=\"_blank\" class=\"ims_navigation\" title=\"$commandtitle\" href=\"$commandurl\">";
        echo "&nbsp;".$object["shorttitle"].$ext."</a>";
      ',
      '          
        echo $sortvalue;
      ',
      '
        echo "<nobr>".N_VisualDate ($sortvalue, 1, 1)."</nobr>"; 
      ',
      '
        echo "$sortvalue&nbsp;";
      '
    ),
  ));
}

function TEST_Reporting_DWBPMS ()
{
  IMS_SetSuperGroupName ("dewaardenbpms_sites");
  uuse ("mds");
  uuse ("reporting");

  $mds1 = new MDS("Jeugdigen");
  $mds1->ConnectToBPMSTable ("46b1a9af61f5f5ab90daf258cba00db9");
  $mds1->SetColumns (array("naam", "groep", "beeldbeschrijving"));
  $mds1->AddField ("key", "Key", '$result = $key;');
  $mds1->AddField ("visualid", "Visual ID", '$result = $rawobject["visualid"];');
  $mds1->AddField ("lastedit", "Last edit", '
    foreach ($rawobject["history"] as $dummy => $rec) {
      if ($rec["who"]) $who = $rec["who"];
    }
    if ($who) {
      $obj = MB_Load ("shield_".IMS_SuperGroupName()."_users", $who);
      $result = $obj["name"];
    }
  ');

  $mds2 = new MDS("Dagrapportages");
  $mds2->ConnectToBPMSTable ("Dagrapportage", array (
    "slowselect"=>array (
      '$record["data"]["datumverslag"] > N_BuildDate(2004,6,1)' => true
    )
  ));
  $mds2->DeleteColumns (array("opnemenindossier", "jeugdige"));
  $mds2->ConnectToParent ($mds1, '$record["data"]["jeugdige"]');

  $mds3 = new MDS("Onderwijs");
  $mds3->ConnectToBPMSTable ("Onderwijs");
  $mds3->DeleteColumns (array("jeugdige"));
  $mds3->ConnectToParent ($mds1, '$record["data"]["jeugdige"]');

  $mds4 = new MDS("Behandelplan (Aanvang)");
  $mds4->ConnectToBPMSTable ("Behandelplan (Aanvang)");
  $mds4->SetColumns (array("aanvangsdatumjeugdzorg", "hulpverleningsvariant", "diagnostischbeeld", "vastgestelddoor"));
  $mds4->ConnectToParent ($mds1, '$record["data"]["jeugdige"]');

  $mds5 = new MDS("Werkdoelen - (Behandelplan A)");
  $mds5->ConnectToBPMSTable ("Werkdoelen - (Behandelplan A)");
  $mds5->DeleteColumns (array("behandelplan"));
  $mds5->ConnectToParent ($mds4, '$record["data"]["behandelplan"]');

  $mds6 = new MDS("Fake data");
  $mds6->ConnectToCode ('
    if ($command=="keys") {
      $result = array (1=>"aap", 2=>"noot", 3=>"mies");
    } else if ($command=="rawobject") {
      $result = $key;
    }
  ');
  $mds6->AddField ("key", "Key", '$result = $key;');
  $mds6->AddField ("value", "Value", '$result = strtoupper($key);');
  $mds6->AddField ("else", "Something else", '$result = $this->parentobject->Key();');
  $mds6->ConnectToParent ($mds5, 'eval ("echo 123;")');

  REPORTING_Dump ($mds1);
}

function TEST_REPORTING_DEMODMS ()
{
  IMS_SetSuperGroupName ("demo_sites");
  uuse ("mds");
  uuse ("reporting");

  $mds = new MDS("DMS");
  $mds->ConnectToDMS(array("select"=>array('strpos ($record["directory"], ")")<>0'=>true)));
  $mds->SetColumns (array("shorttitle", "longtitle", "workflow", "allocto", "case", "case_id", "meta_bedrijfsnaam", "meta_contactpersoon"));

  REPORTING_Dump ($mds);
}

uuse ("dhtml");   
  
function TEST_Xform ()   
{   
uuse ("dhtml");   
$content = '   
<!DOCTYPE   
  html PUBLIC "-//W3C//DTD XHTML 1.0//EN"   
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">   
  
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:xf="http://www.w3.org/2002/xforms" xmlns:ev="http://www.w3.org/2001/xml-events" xml:lang="en">   
  <head>   
    <title>Submit</title>   
      
<style><!--   
body, table {   
  font: 10pt black "Tahoma", sans-serif;   
}   
  
body {   
  padding-top: 95px;   
}   
  
h1, h2, h3 {   
  color: #05A;   
}   
  
#status {   
  margin-top: 1in;   
  border-top: 1px solid #999;   
  padding-top: 1em;   
  color: #999;   
  font-size: xx-small;   
}   
  
.readonly input {   
  background: #AAA;   
}   
  
.invalid label {   
  color: red;   
}   
  
.required label {   
  font-weight: bold;   
}                             
  
.xforms-repeat-index {   
  background: #EEF;   
}   
  
.xforms-repeat-index .xforms-repeat-index {   
  background: #DDE;   
}   
//--> </style>   
      
    <script type="text/javascript" src="/openims/javascript/formf/formfaces.js"></script>   
      
    <xf:model>   
  
      <xf:instance>   
        <result xmlns="">   
          <somevalue>blub</somevalue>   
        </result>   
      </xf:instance>   
  
      <xf:submission id="submission" action="#SUBMIT#" method="post" replace="instance">   
          <xf:load ev:event="xforms-submit-done" resource="#CONFIRM#" show="replace"/>   
      </xf:submission>   
  
    </xf:model>   
  </head>   
    
  <body>       
  
        <xf:input ref="somevalue"><xf:label>Den waarde</xf:label></xf:input>   
  
        <br/>   
  
<xf:trigger>   
    <xf:label>Submit instance</xf:label>   
  <xf:send ev:event="DOMActivate" submission="submission"/>   
</xf:trigger>   
  
  
<!--- For Debug! // -->   
<p id="status123"></p>   
  
  </body>   
</html>';   
  
$url = DHTML_Xform2URL ($content, '   
  echo N_XML2HTML ($rawxml);   
');   
  
echo "<a target=\"_blank\" href=\"$url\">test</a>";   
// echo "<iframe src=\"$url\" width=100% height=300 frameborder=0></iframe>";   
  
}   
  
function TEST_DragDrop()   
{   
  global $myconfig;   
  $myconfig[IMS_SuperGroupName()]["dragdrop"]="yes";   
  $specs["precode"] = '   
    global $sourceid, $targetid, $mousebutton;   
    FORMS_ShowError ("TEST", "$sourceid, $targetid, $mousebutton", "no");   
  ';   
  echo DHTML_InitDragDrop($specs);   
  T_Start ("black");   
  echo DHTML_InvisiTable ('<font size="2" face="arial"><nobr>','</nobr></font>','<img id="dragme" src="/openims/word_small.gif">', "&nbsp;", "Test 1 2 3");   
  T_NewRow();   
  echo '<img id="droponme" src="/openims/folder.gif">';   
  TE_End ();   
  echo DHTML_AddDragSource("dragme", DHTML_InvisiTable ('<font size="2" face="arial">','</font>','<img src="/openims/word_small.gif">', "&nbsp;", "Test 1 2 3"));   
  echo DHTML_AddDragSource("droponme", "hallo #2");   
  echo DHTML_AddDropTarget ("dragme");   
  echo DHTML_AddDropTarget ("droponme");   
}

function TEST_S2_REMOVEACCENTS ()
{
T_Start ("black");
echo "\$i";
T_Next();
echo "\$j";
T_Next();
echo "\$i";
T_Next();
echo "\$j";
T_Next();
echo "chr(\$i)";
T_Next();
echo "&#\$i;";
T_Next();
echo "chr(\$j)";
T_Next();
echo "&#\$j;";
T_NewRow();
for ($i=0; $i<256; $i++)
{
  $j = ord (S2_REMOVEACCENTS (chr($i)));
  echo "".(0+$i)." ";
  T_Next();
  if ($j<>$i) {
    echo "<b>".(0+$j)."</b> ";
  } else {
    echo "".(0+$j)." ";
  }
  T_Next();
  echo S2_PHPEncodeString (chr($i));
  T_Next();
  echo S2_PHPEncodeString (chr($j));
  T_Next();
  echo chr($i)." ";
  T_Next();
  echo "&#$i; ";
  T_Next();
  echo chr($j)." ";
  T_Next();
  echo "&#$j; ";
  T_NewRow();
}
TE_End();
}

function TEST_VVFILE () {
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure("html::/vfiletest_sites/");
  TEST_DOVVFILE();
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure("html::/vfiletest_sites/");
  TEST_DOVVFILE();
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure("html::/vfiletest_sites/");
}

function TEST_DOVVFILE($dir = "html::/vfiletest_sites/") {
  echo "VVFILE Test. Using dir " . N_CleanPath($dir) . "<br/>";
  global $myconfig;
  echo "Delayedfilewrite is " . ($myconfig["delayedfilewrite"] == "yes" ? "enabled" : "disabled") . "<br/>";
  echo 'Results are presented as:<br/> <b>SUCCESS</b> result <font color="blue">(expected_result)</font> / lowlevel_result <font color="blue">(expected_lowlevel_result)</font> <br/>';
  $myconfig["delayedfilewrite"] = "yes";

  @mkdir($dir);
  $tree = N_QuickTree($dir);
  if (count($tree)) {
     echo "Directory is not empty. Please run MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(\"$dir\") and restart the test.<br>";
     return;
  }

  echo "<br>Test #1. Statistics of non-existing file.<br>";
  $file = N_Guid() . ".dat";
  echo "# Filemtime<br>";
  TEST_VVFILE_One($dir, $file, '@N_FileTime($path)', '@filemtime($cleanpath)', false);
  echo "# Fileexists<br>";
  TEST_VVFILE_One($dir, $file, 'N_FileExists($path)', 'file_exists($cleanpath)', false);
  echo "# Filesize<br>";
  TEST_VVFILE_One($dir, $file, '@N_FileSize($path)', '@filesize($cleanpath)', 0);

  echo "<br>Test #2. Creating file with N_WriteFile. <br>"; 
  $file = "test.txt";
  echo '...creating file...<br/>'; N_FLush(-1);
  N_WriteFile($dir . "/$file", str_repeat("Test", 10000));
  echo "# Filemtime<br>";
  TEST_VVFILE_One($dir, $file, 'N_FileTime($path)', 'filemtime($cleanpath)');
  echo '...sleeping 2 s...<br/>'; N_FLush(-1);
  N_Sleep(2000);
  echo "# Filemtime is in the past<br>";
  TEST_VVFILE_One($dir, $file, '(time() - N_FileTime($path) >= 1)', '', true);
  echo "# Fileexists<br>";
  TEST_VVFILE_One($dir, $file, 'N_FileExists($path)', 'file_exists($cleanpath)', true);
  echo "# Filesize<br>";
  TEST_VVFILE_One($dir, $file, 'N_FileSize($path)', 'filesize($cleanpath)', 40000);
  echo "# N_Tree (may fail if your machine is very slow or has an unusual apache root directory)<br>";
  TEST_VVFILE_One($dir, $file, 'N_Tree($dir)', '', '2fe2a3d8e84f9f4d8c79d0bdc4d492b8 / 6726c6f362c7c165f5dffb95595e0a47 / b5ce45f36eb045b4ac8615eba4ddecaa');

  echo "<br>Test #3. Reading file. <br>"; 
  echo "# N_ReadFile<br>";
  TEST_VVFILE_One($dir, $file, 'N_ReadFile($path)', '', str_repeat("Test", 10000));
  echo "# N_ReadFilePart<br>";
  TEST_VVFILE_One($dir, $file, 'N_ReadFilePart($path, 10, 4)', '', "stTe");
  echo "# MD5B<br>";
  TEST_VVFILE_One($dir, $file, 'N_FileMD5B($path)', 'N_GiveMD5BFromData(N_ReadFile($path))', 'e24b5692be6fada58d1ded783b86b454_40000');

  echo "<br>Test #4. Appending file. <br>"; 
  echo '...appending to file...<br/>'; N_FLush(-1);
  N_AppendFile($dir . "/$file", str_repeat("aapnootmies", 1000));
  TEST_VVFILE_One($dir, $file, 'N_ReadFile($path)', '', str_repeat("Test", 10000) . str_repeat("aapnootmies", 1000));
  echo "# N_ReadFilePart (last part)<br>";
  TEST_VVFILE_One($dir, $file, 'N_ReadFilePart($path, 40000, 20000)', '', str_repeat("aapnootmies", 1000));
  echo "# MD5B<br>";
  TEST_VVFILE_One($dir, $file, 'N_FileMD5B($path)', 'N_GiveMD5BFromData(N_ReadFile($path))', '2e81ed70102fb78a68e6477b1e83a70b_51000');


  echo "<br>Test #5. Deleting file. <br>"; 
  echo '...deleting file...<br/>'; N_FLush(-1);
  N_DeleteFile($dir . "/$file");
  echo "# Filemtime<br>";
  TEST_VVFILE_One($dir, $file, '@N_FileTime($path)', '@filemtime($cleanpath)', false);
  echo "# Fileexists<br>";
  TEST_VVFILE_One($dir, $file, 'N_FileExists($path)', 'file_exists($cleanpath)', false);
  echo "# Filesize<br>";
  TEST_VVFILE_One($dir, $file, '@N_FileSize($path)', '@filesize($cleanpath)', 0);

  echo "<br>Test #5. Altering file (immediate). <br>"; 
  echo '...disabling delayedwrite...<br/>'; N_FLush(-1);
  $myconfig["delayedfilewrite"] = "no";
  echo '...creating file...<br/>'; N_FLush(-1);
  N_WriteFile($dir . "/$file", str_repeat("AlterMe", 10000));  
  $input = array("addme" => "ADDME"); 
  $code = 'N_AppendFile($file, $input["addme"]);';
  echo '...altering file...<br/>'; N_FLush(-1);
  VVFILE_AlterFile($dir . "/$file", $code, $input);
  $code = 'N_WriteFile($file, strtolower(N_ReadFile($file)));';
  echo '...altering file...<br/>'; N_FLush(-1);
  VVFILE_AlterFile($dir . "/$file", $code);
  echo "# Is VRef? <br/>";
  TEST_VVFILE_One($dir, $file, '(bool) VVFILE_IsVRef($path)', '', 0);
  echo "# Is modified VRef? <br/>";
  TEST_VVFILE_One($dir, $file, '(bool) VVFILE_IsModifiedVRef($path)', '', 0);
  echo '...reading file...<br/>'; N_FLush(-1);
  echo "# N_ReadFile<br>";
  TEST_VVFILE_One($dir, $file, 'N_ReadFile($path)', '', str_repeat("alterme", 10000) . "addme");
  echo "# MD5B<br>";
  TEST_VVFILE_One($dir, $file, 'N_FileMD5B($path)', 'N_GiveMD5BFromData(N_ReadFile($path))', 'fb3966e11e53561121eabd0597d44b77_70005');

  echo "<br>Test #6. Altering file (delayed) <br>"; 
  echo '...enabling delayedwrite...<br/>'; N_FLush(-1);
  $myconfig["delayedfilewrite"] = "yes";
  echo '...creating file...<br/>'; N_FLush(-1);
  N_WriteFile($dir . "/$file", str_repeat("AlterMe", 10000));  
  $input = array("addme" => "ADDME"); 
  $code = 'N_AppendFile($file, $input["addme"]);';
  echo '...altering file...<br/>'; N_FLush(-1);
  VVFILE_AlterFile($dir . "/$file", $code, $input);
  $code = 'N_WriteFile($file, strtolower(N_ReadFile($file)));';
  echo '...altering file...<br/>'; N_FLush(-1);
  VVFILE_AlterFile($dir . "/$file", $code);
  echo "# Is VRef? <br/>";
  TEST_VVFILE_One($dir, $file, '(bool) VVFILE_IsVRef($path)', '', 1);
  echo "# Is modified VRef? <br/>";
  TEST_VVFILE_One($dir, $file, '(bool) VVFILE_IsModifiedVRef($path)', '', 1);
  echo '...reading file...<br/>'; N_FLush(-1);
  echo "# N_ReadFile<br>";
  TEST_VVFILE_One($dir, $file, 'N_ReadFile($path)', '', str_repeat("alterme", 10000) . "addme");
  echo "# MD5B<br>";
  TEST_VVFILE_One($dir, $file, 'N_FileMD5B($path)', '', 'fb3966e11e53561121eabd0597d44b77_70005'); 
  echo "# Is VRef? <br/>";
  TEST_VVFILE_One($dir, $file, '(bool) VVFILE_IsVRef($path)', '', 0);
  echo "# Is modified VRef? <br/>";
  TEST_VVFILE_One($dir, $file, '(bool) VVFILE_IsModifiedVRef($path)', '', 0);

  echo "<br>Test #7. Delayed code creating a delayed modification<br>"; 
  echo '...altering file...<br/>'; N_FLush(-1);
  $code = 'VVFILE_AlterFile($file, $input["code"]);';
  $input = array("code" => 'N_WriteFile($file, strtoupper(N_ReadFile($file)));');
  VVFILE_AlterFile($dir . "/$file", $code, $input);
  echo "# Is VRef? <br/>";
  TEST_VVFILE_One($dir, $file, '(bool) VVFILE_IsVRef($path)', '', 1);
  echo "# Is modified VRef? <br/>";
  TEST_VVFILE_One($dir, $file, '(bool) VVFILE_IsModifiedVRef($path)', '', 1);
  echo '...reading file...<br/>'; N_FLush(-1);
  echo "# N_ReadFile<br>";
  TEST_VVFILE_One($dir, $file, 'N_ReadFile($path)', '', str_repeat("ALTERME", 10000) . "ADDME");
  echo "# MD5B<br>";
  TEST_VVFILE_One($dir, $file, 'N_FileMD5B($path)', '', '5fa616f28b3a7de638999dcd4c4ad563_70005'); 
  echo "# Is VRef? <br/>";
  TEST_VVFILE_One($dir, $file, '(bool) VVFILE_IsVRef($path)', '', 0);
  echo "# Is modified VRef? <br/>";
  TEST_VVFILE_One($dir, $file, '(bool) VVFILE_IsModifiedVRef($path)', '', 0);

  echo "<br>Test #8. CopyFile (immediately)<br>";
  echo '...disabling delayedwrite...<br/>'; N_FLush(-1);
  $myconfig["delayedfilewrite"] = "no";
  echo '...creating source...<br/>'; N_FLush(-1);
  N_WriteFile($dir . "/source.dat", str_repeat("blub", 10000));  
  echo '...copying file...<br/>'; N_FLush(-1);
  N_CopyFile($dir . "/dest.dat", $dir . "/source.dat");
  echo "# Is VRef source? <br/>";
  TEST_VVFILE_One($dir, "source.dat", '(bool) VVFILE_IsVRef($path)', '', 0);
  echo "# Is modified VRef source? <br/>";
  TEST_VVFILE_One($dir, "source.dat", '(bool) VVFILE_IsModifiedVRef($path)', '', 0);
  echo "# Is VRef dest? <br/>";
  TEST_VVFILE_One($dir, "dest.dat", '(bool) VVFILE_IsVRef($path)', '', 0);
  echo "# Is modified VRef dest? <br/>";
  TEST_VVFILE_One($dir, "dest.dat", '(bool) VVFILE_IsModifiedVRef($path)', '', 0);
  echo "# MD5B source<br>";
  TEST_VVFILE_One($dir, "source.dat", 'N_FileMD5B($path)', '', '3542a8311a7276ec2be1c2c48147481e_40000'); 
  echo "# MD5B destination<br>";
  TEST_VVFILE_One($dir, "dest.dat", 'N_FileMD5B($path)', '', '3542a8311a7276ec2be1c2c48147481e_40000'); 
  echo "# Is VRef source? <br/>";
  TEST_VVFILE_One($dir, "source.dat", '(bool) VVFILE_IsVRef($path)', '', 0);
  echo "# Is VRef dest? <br/>";
  TEST_VVFILE_One($dir, "dest.dat", '(bool) VVFILE_IsVRef($path)', '', 0);
  echo "# Is modified VRef source? <br/>";
  TEST_VVFILE_One($dir, "source.dat", '(bool) VVFILE_IsModifiedVRef($path)', '', 0);
  echo "# Is modified VRef dest? <br/>";
  TEST_VVFILE_One($dir, "dest.dat", '(bool) VVFILE_IsModifiedVRef($path)', '', 0);

  echo "<br>Test #9. CopyFile (delayed)<br>";
  echo '...enabling delayedwrite...<br/>'; N_FLush(-1);
  $myconfig["delayedfilewrite"] = "yes";
  echo '...creating source...<br/>'; N_FLush(-1);
  N_WriteFile($dir . "/source2.dat", str_repeat("noot", 10000));  
  echo "# Is VRef source? <br/>";
  TEST_VVFILE_One($dir, "source2.dat", '(bool) VVFILE_IsVRef($path)', '', 0);
  echo "# Is modified VRef source? <br/>";
  TEST_VVFILE_One($dir, "source2.dat", '(bool) VVFILE_IsModifiedVRef($path)', '', 0);
  echo '...copying file...<br/>'; N_FLush(-1);
  N_CopyFile($dir . "/dest2.dat", $dir . "/source2.dat");
  echo "# Is VRef source? <br/>";
  TEST_VVFILE_One($dir, "source2.dat", '(bool) VVFILE_IsVRef($path)', '', 1);
  echo "# Is modified VRef source? <br/>";
  TEST_VVFILE_One($dir, "source2.dat", '(bool) VVFILE_IsModifiedVRef($path)', '', 0);
  echo "# Is VRef dest? <br/>";
  TEST_VVFILE_One($dir, "dest2.dat", '(bool) VVFILE_IsVRef($path)', '', 1);
  echo "# Is modified VRef dest? <br/>";
  TEST_VVFILE_One($dir, "dest2.dat", '(bool) VVFILE_IsModifiedVRef($path)', '', 0);
  echo "# MD5B source<br>";
  TEST_VVFILE_One($dir, "source2.dat", 'N_FileMD5B($path)', '', 'b295af814cb9c8728d2d3792c2e6360e_40000'); 
  echo "# MD5B destination<br>";
  TEST_VVFILE_One($dir, "dest2.dat", 'N_FileMD5B($path)', '', 'b295af814cb9c8728d2d3792c2e6360e_40000'); 
  echo "# Is VRef source? <br/>";
  TEST_VVFILE_One($dir, "source2.dat", '(bool) VVFILE_IsVRef($path)', '', 1);
  echo "# Is VRef dest? <br/>";
  TEST_VVFILE_One($dir, "dest2.dat", '(bool) VVFILE_IsVRef($path)', '', 1);
  echo "# Is modified VRef source? <br/>";
  TEST_VVFILE_One($dir, "source2.dat", '(bool) VVFILE_IsModifiedVRef($path)', '', 0);
  echo "# Is modified VRef dest? <br/>";
  TEST_VVFILE_One($dir, "dest2.dat", '(bool) VVFILE_IsModifiedVRef($path)', '', 0);

  echo "<br>[todo] Test #10. Can become VRef (paths)<br>";
  // Test if various paths can be Vref  VVFILE_CanBecomeVRef($path, true);

  echo "<br>[todo] Test #11. Can become VRef (existing files)<br>";
  // Test if various existing files can become Vref. This should include files that already are Vref.

  echo "<br>Test #12. Filesize of modified VRef<br>";
  // Test if various existing files can become Vref. This should include files that already are Vref.
  echo '...creating file...<br/>'; N_FLush(-1);
  $file = "filesize.dat";
  N_WriteFile($dir . "/$file", str_repeat("AlterMe", 10000));  
  $input = array("addme" => "ADDME"); 
  $code = 'N_AppendFile($file, $input["addme"]);';
  echo '...altering file...<br/>'; N_FLush(-1);
  VVFILE_AlterFile($dir . "/$file", $code, $input);
  echo '# QuickFileSize';
  TEST_VVFILE_One($dir, $file, 'N_QuickFileSize($path)', '', 70000);
  echo "# Is VRef? <br/>";
  TEST_VVFILE_One($dir, $file, '(bool) VVFILE_IsVRef($path)', '', 1);
  echo "# Filesize<br>";
  TEST_VVFILE_One($dir, $file, 'N_FileSize($path)', 'filesize($cleanpath)', 70005);
  echo "# Is VRef? <br/>";
  TEST_VVFILE_One($dir, $file, '(bool) VVFILE_IsVRef($path)', '', 0);

  echo "<br>[todo] Test #13. Caching of N_FileInfo<br>";

  echo "<br>[todo] Test #14. Delayed modification with no timestamp update.<br>";

  echo "<br>[todo] Test #15. Oversized delayed modification.<br>";


}

function TEST_VVFILE_One($dir, $file, $highlevelcode, $lowlevelcode = "", $highlevelvalue = "aoeu", $lowlevelvalue = "aoeu") {
  $path = $dir . '/' . $file;
  $cleanpath = N_CleanPath($path);

  eval('$highlevelresult = ' . $highlevelcode . ';');
  if ($lowlevelcode) eval('$lowlevelresult = ' . $lowlevelcode . ';');

  $success = "OK";
  if ($highlevelvalue !== "aoeu" && !($highlevelvalue == $highlevelresult || strpos (" $highlevelvalue", md5(serialize($highlevelresult))))) $success = "ERROR"; 
  if ($lowlevelvalue !== "aoeu" && !($lowlevelvalue == $lowlevelresult || strpos (" $lowlevelvalue", md5(serialize($lowlevelresult))))) $success = "ERROR"; 
  if ($lowlevelcode && $lowlevelvalue === "aoeu" && !($lowlevelresult == $highlevelresult)) $success = "ERROR";

  echo '<font color="'.($success == "OK" ? 'green' : 'red') . '"><b>'.$success.'</b></font>';
  echo ' ' . (strlen($highlevelresult) > 50 ? substr($highlevelresult, 0, 50) . "..." : $highlevelresult);
  if ($success != "OK" && is_array($highlevelresult)) {
    T_EO($highlevelresult);
    echo "md5: " . md5(serialize($highlevelresult)) . "<br/>";
  }
  if ($highlevelvalue != "aoeu") {
    echo '<font color="blue"> (' . (strlen($highlevelvalue) > 50 ? substr($highlevelvalue, 0, 50) . "..." : $highlevelvalue) . ')';
    if ($success != "OK" && is_array($highlevelvalue)) {
      T_EO($highlevelvalue);
    }
    echo '</font>';
  }
  if ($lowlevelcode) echo ' / ' . (strlen($lowlevelresult) > 50 ? substr($lowlevelresult, 0, 50) . "..." : $lowlevelresult);
  if ($success != "OK" && $lowlevelcode && is_array($lowlevelresult)) {
    T_EO($lowlevelresult);
    echo "md5: " . md5(serialize($lowlevelresult)) . "<br/>";
  }
  if ($lowlevelvalue != "aoeu") {
    echo '<font color="blue"> (' . (strlen($lowlevelvalue) > 50 ? substr($lowlevelvalue, 0, 50) . "..." : $lowlevelvalue) . ')';
    if ($success != "OK" && is_array($lowlevelvalue)) {
      T_EO($lowlevelvalue);
    }
    echo '</font>';
  }

  echo "<br>";

  N_FLush(-1);
}

function TEST_VVBench () // only works on http://demo.dev.openims.com
{
IMS_SetSuperGroupName ("demo_sites");
$sgn = IMS_SuperGroupName();

if (N_CurrentServer() == "liesbethlaptop") {
  $folder_id = "38332fc6fb3caebf8faa1e521abd27a2";
  $templates = array("211c3049472b199a28d0598a9b2d8573", "b4603ebd412780491790416692ddd39a", "b5267302e0e2bc1ea10e96559513ce3f");
}
if (N_CurrentServer() == "rack132") {
  $folder_id = "00ddefc3fdb7c58da4033c81081c1312";
  $templates = array("a44cf895ea68990d7d4f5ce9d53c12be", "68e8eb79266153ee5007cd87f61fb27c", "7d34021382c4e2449e6d3343ff754d78");
}
if (N_CurrentServer() == "rack132" && $sgn == "moduledev_sites") {
  $folder_id = "4b3fa08940edbe12e7fc1cd6f29af85e";
  $templates = array("70041734a2197e28142c331c7fb9c434", "b8d0f8e30019b237e4f76e685fd091ba", "23858d6afaf7840f55a760ebe94f1fed");
}
  
  
uuse("marker");
 
$folder_id = IMS_DMSPath2Id($sgn, $folder_id, "Obj. perf. test " . N_VisualDate(time(), true, true));
//$debug = "PERF";
 
foreach ($templates as $t => $template_id) {
  $template = MB_Load("ims_{$sgn}_objects", $template_id);
  if (!$template) continue;
  echo "Template $t <br/>";
  echo "Key: $template_id <br/>";
  echo "Doctype: " .  FILES_FileType($sgn, $template_id) . "<br/>";
  echo "Marker: " .  MARKER_CanHaveMarker(FILES_FileType($sgn, $template_id)) . "<br/>";
  echo "Filesize: " . N_FileSize(FILES_FileLocation($sgn, $template_id)) ."<br/>";
  echo "<br/>";
  echo "Creating 10 documents...<br/>"; N_FLush(-1);
 
  $t_newdocumentobject = 0;
  $t_repsave = 0;
  $t_archiveobject = 0;
 
  $t_startall = N_MicroTime();
 
  for ($i = 1; $i <= 10; $i++) {
    N_Debug("PERF Before template $key newdoc $i");
 
    $t_start = N_MicroTime();
    $object_id = IMS_NewDocumentObject($sgn, $folder_id, $template_id);
// `sync`;
    $object = MB_Load("ims_{$sgn}_objects", $object_id);
    $t_end = N_MicroTime();
    $t_newdocumentobject += ($t_end - $t_start);
    
 
    $object["workflow"] = $template["workflow"];
    $object["shorttitle"] = "Obj. perf. test $t $i";
 
    $t_start = N_MicroTime();
    IMS_ArchiveObject($sgn, $object_id, base64_decode ("dWx0cmF2aXNvcg=="), true);
// `sync`;
    $t_end = N_MicroTime();
    $t_archiveobject += ($t_end - $t_start);
 
    $t_start = N_MicroTime();
    MB_REP_Save("ims_{$sgn}_objects", $object_id, $object);
    $t_end = N_MicroTime();
    $t_repsave += ($t_end - $t_start);
 
    N_Debug("PERF After template $key newdoc $i");
  }
 
  $t_endall = N_MicroTime();
  echo "Total time: " . (int)(1000 * ($t_endall - $t_startall)) . "ms<br/>";
  echo "Time spent in IMS_NewDocumentObject: " . (int)(1000 * $t_newdocumentobject) . "ms<br/>";
  echo "Time spent in MB_REP_SAVE: " . (int)(1000 * $t_repsave) . "ms<br/>";
  echo "Time spent in IMS_ArchiveObject: " . (int)(1000 * $t_archiveobject) . "ms<br/>";
  echo "Other: " . (int)(1000 * ($t_endall - $t_startall - $t_newdocumentobject - $t_repsave - $t_archiveobject) ). "ms<br/>";
  echo "<br/><br/>";
  N_FLush(-1);
 
}

}

function TEST_VVBench2_PREP ()
{
  MB_DeleteTable ("local_vvb2");
  for ($i=0; $i<1000; $i++) {
    MB_Save ("local_vvb2", "rec$i", "val$i");
  }
  MB_Flush();
  for ($i=0; $i<200; $i++) {
    MB_TurboMultiQuery ("local_vvb2", array ("select"=>array('rand(0,9999)+'.rand(0,9999)=>'xyz')));
  }
  N_WriteFile ("html::/tmp/vvb2/f1", str_repeat ("*", 50000));
  N_WriteFile ("html::/tmp/vvb2/f2", str_repeat ("*", 500000));
  N_WriteFile ("html::/tmp/vvb2/f3", str_repeat ("*", 4000000));
  `sync`;
}

function TEST_VVBench2 ()
{
  $tmp = N_GUID();
  for ($i=1; $i<=3; $i++) { 
    for ($j=0; $j<10; $j++) {
      $start = N_MicroTime();
      N_CopyFile ("html::/tmp/vvb2/$tmp/f$i-$j", "html::/tmp/vvb2/f$i");
      $t_copy += N_MicroTime() - $start;
      $start = N_MicroTime();
      MB_REP_Save ("local_vvb2", "$tmp$i-$j", $tmp);
      $t_rep += N_MicroTime() - $start;
      $start = N_MicroTime();
      N_CopyFile ("html::/tmp/vvb2/$tmp/f$i-$j.cpy", "html::/tmp/vvb2/$tmp/f$i-$j");
      $t_copy += N_MicroTime() - $start;
    }
  }
  echo "Time COPY: " . (int)(1000 * $t_copy) . "ms<br/>";
  echo "Time REP: " . (int)(1000 * $t_rep) . "ms<br/>";
}


// Proof of concept: how to escape something so that it can be used inside an eval, inside a string literal.
// Correct solutions are:
// - use single quotes, and escape single quote and backslash
// - use double quotes, and escape double quote, backslash and $
// If you escape too much, there are always some stings that you will mangle as a result.
// A solution with base64_encode / decode will also work.
function TEST_EvalEscaping() {
  $inputs = array("c:\\openims\\tmp", "c:/openims/tmp", "\\\\", "aoeu", '"', "'", '\\"', '\\\'', "\0", '\0', '', '\\', '$myconfig', '{$myconfig}', '\{$myconfig}', '\n');
  $inputs[] = $_REQUEST["code"]; // if this works correctly, I dont know what wouldnt work
  foreach ($inputs as $docpath) {
    $internalcommand1 = '$test1 = "'.addcslashes($docpath, '"\\\$').'";'; 
    //$internalcommand1 = '$test1 = base64_decode("'.base64_encode($docpath).'");'; // Also works
    //$internalcommand1 = '$test1 = unserialize(base64_decode("'.base64_encode(serialize($docpath)).'"));'; // Also works
    $internalcommand2 = '$test2 = \''.addcslashes($docpath, "'\\").'\';';
    eval ($internalcommand1);
    eval ($internalcommand2);
    echo htmlspecialchars($docpath) . "(1): " . ($test1 === $docpath ? "<b>OK</b>" : "<b>ERROR</b> " . htmlspecialchars($test1) . " // " . htmlspecialchars($internalcommand1)) . "<br/>";
    echo htmlspecialchars($docpath) . "(2): " . ($test2 === $docpath ? "<b>OK</b>" : "<b>ERROR</b> " . htmlspecialchars($test2) . " // " . htmlspecialchars($internalcommand2)) . "<br/>"; 
  }
}

?>