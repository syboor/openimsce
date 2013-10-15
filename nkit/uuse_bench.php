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



uuse("ims");
uuse("sys");
uuse ("terra");

global $benchmarksgn;
$benchmarksgn = "internalbenchmarkdonotuse_sites";
global $benchmarksite;
$benchmarksite = "internalbenchmarkdonotuse_com";
global $benchmarkdomain;
$benchmarkdomain = "internalbenchmarkdonotuse";
global $benchmarkdomain_other;
$benchmarkdomain_other=array("bench.osict.com");


global $benchmarkurl;
$benchmarkurl = "http://" . $benchmarkdomain;
global $benchdebug;
$benchdebug = true;

 function BENCH_JS_Bench_Multiple($numberofthreads=10, $numberofrunsinthread=5) {
  // use terra to benchmark

  global $benchmarksgn;
  global $benchmarksite;
  global $benchmarkdomain;
  global $benchmarkurl;

  $list = array();
  $specs = array();
  for($i=0; $i<$numberofrunsinthread; $i++) $list[$i] = $i;
  for($j=0; $j<$numberofthreads; $j++) { 
    $specs[$j] = array();
    $specs[$j]["title"] = "Benchmark: benchmarking";
    $specs[$j]["list"] = $list;
    $specs[$j]["input"]["benchmarksgn"] = $benchmarksgn;
    $specs[$j]["input"]["benchmarksite"] = $benchmarksite;
    $specs[$j]["input"]["benchmarkdomain"] = $benchmarkdomain;
    $specs[$j]["input"]["benchmarkurl"] = $benchmarkurl;
    $specs[$j]["input"]["terranumber"] = $j;

    $specs[$j]["step_code"] = '
      global $benchmarksgn;
      global $benchmarksite;
      global $benchmarkdomain;
      global $benchmarkurl;
      $benchmarksgn = $input["benchmarksgn"];
      $benchmarksite = $input["benchmarksite"];
      $benchmarkdomain = $input["benchmarkdomain"];
      $benchmarkurl = $input["benchmarkurl"];
      $terranumber = $input["terranumber"];

      uuse("bench");

      N_LOG("bench", "terra $terranumber bench $index start");
      $starttime = time ();

      BENCH_JS_Bench("t" . $terranumber . "i" . $index);
      $endtime = time ();
      $elapsed = $endtime - $starttime;
      N_LOG("bench", "terra $terranumber bench $index elapsed $elapsed endtime $endtime");
    ';
    $specs[$j]["init_code"] = '
    ';
    $specs[$j]["exit_code"] = '
      $terranumber = $input["terranumber"];
      $endtime = time ();
      $delta = $endtime - $input["starttime"];
      N_LOG("bench", "terra $terranumber bench elapsed $delta FINISH (Exitcode)");
    ';
    $specs[$j]["input"]["starttime"] = time ();
    TERRA_Multi_List($specs[$j]);
    N_LOG("bench", "terra $j bench Started");
    echo "Terra $j started  <br>";
    $delay = 5;
    echo "sleeping $delay s. <br>";
    N_Flush();
    sleep($delay);
  }
}

function BENCH_JS_Bench($testid="aa") {
// has to be called from EVAL (DEV):
// $debug = "speed"; // displays debug info
// // SYS_ : to prevent automatic SYS_HELP
// uuse("bench");
// BENCH_JS_Bench();

  global $benchmarksgn;

  $dmstestdoc1 = "x00000000000000000000000000000000";
  $activeuser1 = base64_decode ("dWx0cmF2aXNvcg==");
  $searchterm1 = "lorem ipsum";

  $dmstestfolder2 = "root";
  $dmstestdoc2 = "x00000000000000000000000000000001";

  $dmstestdoc3 = "x00000000000000020000000000000001";

  $dmstestdoc4 = "x00000000000000030000000000000001";

  $dmstestdoc5 = "x00000000000000000000000000000002";

  $dmstestdoc6 = "x00000000000000010000000000000002";

  $dmstestdoc7 = "x00000000000000020000000000000002"; // [14]

  $dmstestdoc8 = "x00000000000000030000000000000001"; // [16]

  $dmstestdoc9 = "x00000000000000030000000000000002"; // [17]

  $dmstestdoc10 = "x00000000000000010000000000000002"; // [18]
  $dossiertable = "bench_" . $benchmarksgn . "_dossiers";
  $dossiers = MB_Ref($dossiertable, "1");
  $dmstestdossier1 = $dossiers["root"]; // [18]

  $dmstestdoc11 = "x00000000000000070000000000000013"; // [23]
  $dossiers = MB_Ref($dossiertable, "7");
  $dmstestfolder3 = $dossiers["root"]; // [23]

  $testcntr = 0;
  // [0] => toegewezen overzicht
  BENCH_Test_DMS_Allocated($testid, $testcntr); 
  $testcntr = 1;
  // [1] => object geselecteerd in allocated
  BENCH_Test_DMS_Allocated($testid, $testcntr,$dmstestdoc1 , $activeuser1);
  $testcntr = 2;
  // [2] => zoeken 
  BENCH_Test_DMS_Search($testid, $testcntr, $searchterm1);
  $testcntr = 3;
  // [3] => object geselecteerd
  BENCH_Test_DMS_Main($testid, $testcntr, $dmstestdoc2, $dmstestfolder2);
  $testcntr = 4;
  // [4] => (nogmaals) object geselecteerd
  BENCH_Test_DMS_Main($testid, $testcntr, $dmstestdoc2, $dmstestfolder2);
  $testcntr = 5;
  // [5] => eigenschappen van geselecteerd document veranderd
  $newprops = array("shorttitle" => "abcdef");
  BENCH_Test_DMS_DMSUIF_Properties ($testid, $testcntr, $benchmarksgn, $dmstestdoc2, $newprops);
  $testcntr = 6;
  // [6] => toegewezen overzicht
  BENCH_Test_DMS_Allocated($testid, $testcntr);
  $testcntr = 7;
  // [7] => object geselecteerd in toegewezen
  BENCH_Test_DMS_Allocated($testid, $testcntr, $dmstestdoc3 , $activeuser1);
  $testcntr = 9;
  // [9] => Assistent "Postverwerking"
  $fieldlist = array("longtitle", "dummy");
  $newvalues = array("longtitle"=>"long", "dummy"=>"123");
  $wflstep = "Publiceren";
  BENCH_Test_DMS_Postverwerking ($testid, $testcntr, $benchmarksgn, $dmstestdoc4, $fieldlist, $newvalues, $wflstep);
  $testcntr = 10;
  // [10] => object geselecteerd in toegewezen
  BENCH_Test_DMS_Allocated($testid, $testcntr, $dmstestdoc5 , $activeuser1);
  $testcntr = 11;
  // [11] => ander object geselecteerd in toegewezen
  BENCH_Test_DMS_Allocated($testid, $testcntr, $dmstestdoc6 , $activeuser1);
  $testcntr = 12;
  // [12] => zelfde object geselecteerd in toegewezen
  BENCH_Test_DMS_Allocated($testid, $testcntr, $dmstestdoc6 , $activeuser1);
  // [14] => Assisent "Postverwerking"
  $testcntr = 14;
  $fieldlist = array("longtitle", "dummy");
  $newvalues = array("longtitle"=>"long", "dummy"=>"345");
  $wflstep = "Publiceren";
  BENCH_Test_DMS_Postverwerking ($testid, $testcntr, $benchmarksgn, $dmstestdoc7, $fieldlist, $newvalues, $wflstep);
  // [15] => zelfde object geselecteerd in toegewezen
  $testcntr = 15;
  BENCH_Test_DMS_Allocated($testid, $testcntr, $dmstestdoc6 , $activeuser1);
  // [16] => ander object geselecteerd in toegewezen
  $testcntr = 16;
  BENCH_Test_DMS_Allocated($testid, $testcntr, $dmstestdoc8 , $activeuser1);
  // [17] => historie opvragen
  $testcntr = 17;
  BENCH_Test_DMS_History($testid, $testcntr, $dmstestdoc9);
  // [18] => ga naar object in dossier
  $testcntr = 18;
  BENCH_Test_DMS_Main($testid, $testcntr, $dmstestdoc10, $dmstestdossier1);
  // [19] => ga naar toegewezen
  $testcntr = 19;
  BENCH_Test_DMS_Allocated($testid, $testcntr); 
  // [20] => ga naar zoeken (leeg)
  $testcntr = 20;
  BENCH_Test_DMS_Search($testid, $testcntr, "");
  // [21] => ga naar advanced zoeken
  $testcntr = 21;
  BENCH_Test_DMS_Search_Advanced($testid, $testcntr, "");
  // [22] => advanced zoeken //qr1=lorem&c1=shorttitle
  $testcntr = 22;
  BENCH_Test_DMS_Search_Advanced($testid, $testcntr, "lorem", "shorttitle");
  // [23] => ga naar object in dossier
  $testcntr = 23;
  BENCH_Test_DMS_Main($testid, $testcntr, $dmstestdoc11, $dmstestfolder3);
  // [24] => ga naar object in dossier
  $testcntr = 24;
  BENCH_Test_DMS_Main($testid, $testcntr, $dmstestdoc11, $dmstestfolder3);

  // [25] => Assistent "TITLE=Document generatie"
  $testcntr = 25;
  $test25newdoc = BENCH_Test_DMS_NewDocument ($testid, $testcntr, $benchmarksgn);
  // [27] => ga naar (nieuw) object in dossier
  $testcntr = 27;
  BENCH_Test_DMS_Main($testid, $testcntr, $test25newdoc, $dmstestfolder2);

  // [28] => idem als 27
  $testcntr = 28;
  BENCH_Test_DMS_Main($testid, $testcntr, $test25newdoc, $dmstestfolder2);
  // [29] => ga naar object in dossier
  $testcntr = 29;
  $dossiers = MB_Ref($dossiertable, "7");
  BENCH_Test_DMS_Main($testid, $testcntr, "x00000000000000070000000000000012", $dossiers["root"]);
  // [30] => Assistent "TITLE=Document generatie"
  $testcntr = 30;
  $test30newdoc = BENCH_Test_DMS_NewDocument ($testid, $testcntr, $benchmarksgn);
  // [32] => ga naar (nieuw) object in dossier
  $testcntr = 32;
  BENCH_Test_DMS_Main($testid, $testcntr, $test30newdoc, $dmstestfolder2);
  // [33] => idem als [32]
  $testcntr = 33;
  BENCH_Test_DMS_Main($testid, $testcntr, $test30newdoc, $dmstestfolder2);
  // [34] => ga naar ander document in dms
  $testcntr = 34;
  $dossiers = MB_Ref($dossiertable, "9");
  BENCH_Test_DMS_Main($testid, $testcntr, "x00000000000000090000000000000015", $dossiers["root"]);
  // [35] => ga naar ander document in dms
  $testcntr = 35;
  $dossiers = MB_Ref($dossiertable, "5");
  BENCH_Test_DMS_Main($testid, $testcntr, "x00000000000000050000000000000012", $dossiers["root"]);
  // [36] => ga naar toegewezen overzicht
  $testcntr = 36;
  BENCH_Test_DMS_Allocated($testid, $testcntr); 

  // [37] extra: dms (als 27) 
  $testcntr = 37;
  BENCH_Test_DMS_Main($testid, $testcntr, $test25newdoc, $dmstestfolder2);
  // [38] extra: verwijder document van [25] 
  $testcntr = 38;
  BENCH_DeleteDocFromDMS($testid, $testcntr, $benchmarksgn, $test25newdoc);
  // [39] extra: dms (als 37)
  $testcntr = 39;
  BENCH_Test_DMS_Main($testid, $testcntr, $test25newdoc, $dmstestfolder2);

  // [40] extra: dms (als 27) 
  $testcntr = 40;
  BENCH_Test_DMS_Main($testid, $testcntr, $test30newdoc, $dmstestfolder2);
  // [41] extra: verwijder document van [30] 
  $testcntr = 41;
  BENCH_DeleteDocFromDMS($testid, $testcntr, $benchmarksgn, $test30newdoc);
  // [42] extra: dms (als 37)
  $testcntr = 42;
  BENCH_Test_DMS_Main($testid, $testcntr, $test30newdoc, $dmstestfolder2);

}

function BENCH_Write_Siteconfig($sgn) {
$a = '<?
   // Actieve OpenIMS producten en modules
   $myconfig["'.$sgn.'"]["products"] = array ("cms", "dms", "ems", "bpms", "ps");
   $myconfig["'.$sgn.'"]["cms_modules"] = array ("search", "develop", "forms", "audit", "templates", "workflow");
   $myconfig["'.$sgn.'"]["dms_modules"] = array ("search", "email", "audit", "workflow");
   $myconfig["'.$sgn.'"]["maxnamedusers"] = 1000;

   $myconfig["'.$sgn.'"]["projectfilter"] = "advanced";
  ?>';
  N_WriteFile("html::/config/" . $sgn . "/siteconfig.php", $a);
}

function BENCH_PrepareEnvironment($sure="no") {
  // use "yesplease" as parameter
  global $benchmarksgn;
  global $benchmarksite;
  global $benchmarkdomain;
  global $benchmarkdomain_other;
  $user = base64_decode ("dWx0cmF2aXNvcg==");

  $dossiertable = "bench_" . $benchmarksgn . "_dossiers";
  $objecttable = "bench_" . $benchmarksgn . "_objects";

  if($sure != "yesplease") {
    N_DIE("No Benchmark");
  } else {
    BENCH_ClearAndCreateSGN($benchmarksgn,$benchmarksite,$benchmarkdomain,$benchmarkdomain_other);

    // create base document
    $path = "html::/openims/libs/performance/lorem-word-groot.doc";
    $parentfolder = "root";
    $newwfl = "edit-review-publish";
    
    N_LOG("bench", "BENCH_PrepareEnvironment before BENCH_InsertDocInDMS");
    $newobject = BENCH_InsertDocInDMS($benchmarksgn, $path, $user, $parentfolder, "base example", "base example", $newwfl , array());
    N_LOG("bench", "BENCH_PrepareEnvironment after BENCH_InsertDocInDMS");

    MB_FLush();
    // force index (get openims page)
    BENCH_Test_DMS_Main("forcecache", "", $newobject, $parentfolder);

    // use terra to make copies of base document
    $numberofthreads = 10;
    // first create $numberofthreads-1 dossiers
    $dossiers = array();
    $dossiers[0] = $parentfolder;
    for($j=1; $j<$numberofthreads; $j++) {
      $nieuwdossiernaam = "dossier " . sprintf("%06d", $j);
      $nieuwdossieromschrijving = $nieuwdossiernaam;
      $case = CASE_Create ($benchmarksgn, $nieuwdossiernaam , $nieuwdossieromschrijving );
echo "case $case $nieuwdossiernaam <br>";
      $caseroot = $case . "root";
      $dossiers[$j] = $caseroot;
      $newcase = &MB_Ref($dossiertable, $j);
      $newcase["caseid"] = $case;
      $newcase["root"] = $caseroot;
    }
    for($j=0; $j<$numberofthreads; $j++) { 
      $specs[$j] = array();
      $list[$j] = array();
      for($i=0; $i<20; $i++) $list[$j][$i] = $i;
      $specs[$j]["title"] = "Benchmark: Creating documents";
      $specs[$j]["list"] = $list[$j];
      $specs[$j]["input"]["sgn"] = $benchmarksgn;
//      $specs[$j]["input"]["parentfolder"] = $parentfolder;
      $specs[$j]["input"]["parentfolder"] = $dossiers[$j];
      $specs[$j]["input"]["parentobject"] = $newobject;
      $specs[$j]["input"]["newwfl"] = $newwfl;
      $specs[$j]["input"]["user"] = $user;
      $specs[$j]["input"]["terranumber"] = $j;
      $specs[$j]["input"]["objecttable"] = $objecttable;

      $specs[$j]["step_code"] = '
        $starttime = time ();
        $terranumber = $input["terranumber"];
        N_LOG("bench", "terra $terranumber create doc START $index starttime " . $starttime);
        uuse("ims");
        uuse ("terra");
        $sgn = $input["sgn"];
        $parentfolder = $input["parentfolder"];
        $parentobject = $input["parentobject"];
        $newwfl = $input["newwfl"];
        $user = $input["user"];

        $newfilename = "terra" . sprintf("%03d", $terranumber) . "doc" . sprintf("%06d", $index) . ".doc";
        $newid = "x" . sprintf("%016d", $terranumber) . sprintf("%016d", $index);
        $docid = IMS_NewDocumentObject ($sgn, $parentfolder, $parentobject, $newid, $newfilename);
        N_LOG("bench", "terra $terranumber create doc $index id $docid");
        $newobject = &MB_Ref($input["objecttable"], $docid);
        $newobject["directory"] = $parentfolder;
        $object = &IMS_AccessObject ($sgn, $docid);
        $object["shorttitle"] = $newfilename;
        $object["longtitle"] = $newfilename;
        $object["workflow"] = $newwfl;
        $object["stage"] = 1;
        $object["executable"]="winword.exe";
        IMS_ArchiveObject ($sgn, $docid, $user, true);
        SEARCH_AddPreviewDocumentToDMSIndex ($sgn, $docid);
        $endtime = time ();
        $elapsed = $endtime - $starttime;
        N_LOG("bench", "terra $terranumber create doc FINISH $index elapsed $elapsed endtime $endtime");
      ';
      $specs[$j]["init_code"] = '
      ';
      $specs[$j]["exit_code"] = '
        $terranumber = $input["terranumber"];
        N_LOG("bench", "terra $terranumber FINISH (Exitcode)");
      ';
    
      TERRA_Multi_List($specs[$j]);
      echo "Terra $j started  <br>";
    }
if(false) {
    // make copies
    $starttime = time ();
    $docids = array();
    $object = array();
    for($i=0;$i<3;$i++) {
      $newfilename = "doc" . sprintf("%06d", $i) . ".doc";
echo "doc #". $i . ":" . $newfilename ."<br>";
N_FLUSH();
      $docids[$i] = IMS_NewDocumentObject ($benchmarksgn, $parentfolder, $newobject, "", $newfilename);
echo "IMS_NewDocumentObject ($benchmarksgn, $parentfolder, $newobject, .., $newfilename) <br>";
echo "docids[$i] " . $docids[$i] . "<br>";
N_FLUSH();
      $object = &IMS_AccessObject ($benchmarksgn, $docids[$i]);
      $object["shorttitle"] = $newfilename;
      $object["longtitle"] = $newfilename;
      $object["workflow"] = $newwfl;
      $object["stage"] = 1;
      $object["executable"]="winword.exe";
      IMS_ArchiveObject ($benchmarksgn, $docids[$i], $user, true);
      SEARCH_AddPreviewDocumentToDMSIndex ($benchmarksgn, $docids[$i]);
    }
    $endtime = time ();
echo "start time :" . $starttime . "<br>";
echo "end time :" . $endtime . "<br>";
echo "newobject $newobject <br>";
}

  }
}

function BENCH_ClearAndCreateSGN($benchmarksgn,$benchmarksite,$benchmarkdomain,$benchmarkdomain_other) {
  $dossiertable = "bench_" . $benchmarksgn . "_dossiers";
  $objecttable = "bench_" . $benchmarksgn . "_objects";

  echo "start <br>";
N_Flush();
  N_LOG("bench", "BENCH_ClearSGN START before SYS_NukeSitecollection");
  // destroy benchmark supergroup
  SYS_NukeSitecollection ($benchmarksgn, "please");
  N_LOG("bench", "BENCH_ClearSGN after SYS_NukeSitecollection");
  N_LOG("bench", "BENCH_ClearSGN before nuke search indexes");
  echo "BENCH_ClearSGN before nuke search indexes<br>";
N_Flush();
  $index = $benchmarksgn."#publisheddocuments";
  SEARCH_NukeIndex ($index, "please");
  $index = $benchmarksgn."#previewdocuments";
  SEARCH_NukeIndex ($index, "please");
  N_LOG("bench", "BENCH_ClearSGN after nuke search indexes");
  N_LOG("bench", "BENCH_ClearSGN before MB_DeleteTable");
  echo "BENCH_ClearSGN before MB_DeleteTable<br>";
N_Flush();
  MB_DeleteTable($dossiertable);
  MB_DeleteTable($objecttable);
  N_LOG("bench", "BENCH_ClearSGN after MB_DeleteTable");

  N_LOG("bench", "BENCH_ClearSGN before IMS_CreateEverything");
  IMS_CreateEverything ($benchmarksgn, $benchmarksite, $benchmarkdomain);
  IMS_AddDomain ($benchmarksite, $benchmarkdomain . ":80");
  foreach($benchmarkdomain_other as $tempdomain) {
    IMS_AddDomain ($benchmarksite, $tempdomain);
    IMS_AddDomain ($benchmarksite, $tempdomain . ":80");
  }

  N_LOG("bench", "BENCH_ClearSGN after IMS_CreateEverything");

  N_LOG("bench", "BENCH_ClearSGN before BENCH_Write_Siteconfig");
  BENCH_Write_Siteconfig($benchmarksgn);
  N_LOG("bench", "BENCH_ClearSGN after BENCH_Write_Siteconfig");
}

// this function wil create lots of copies of the small lorem doc
function BENCH_CreateMultipleCopiesOfSmallDocumentOLD($sgn, $docsperdossier, $numberofdossiers, $foldersperdossier, $dossiername, $dossierstart) {
  // create base document

N_Log("bench", "start $sgn, $docsperdossier, $numberofdossiers, $foldersperdossier, $dossiername, $dossierstart ");

  $dossiertable = "bench_" . $sgn . "_dossiers";
  $objecttable = "bench_" . $sgn . "_objects";

  $path = "html::/openims/libs/performance/lorem-word-klein.doc";
  $user = base64_decode ("dWx0cmF2aXNvcg==");
  $parentfolder = "root";
  $newwfl = "edit-review-publish";
  $newobject_small = BENCH_InsertDocInDMS($sgn, $path, $user, $parentfolder, "base example small", "base example small", $newwfl , array());
echo "newobject_small $newobject_small <br>";
  // use terra to make copies of base document

  // first create $numberofdossiers-1 dossiers
  $dossiers = array();
  for($j=0; $j<$numberofdossiers; $j++) {
    $dossierteller = $dossierstart+$j;
    $nieuwdossiernaam = $dossiername . " " . sprintf("%16d", $dossierteller);
    $nieuwdossieromschrijving = $nieuwdossiernaam;
    $case = CASE_Create ($sgn, $nieuwdossiernaam , $nieuwdossieromschrijving );
echo "case $case $nieuwdossiernaam <br>";
    $caseroot = $case . "root";
    $dossiers[$j]["caseroot"] = $caseroot;
    $dossiers[$j]["case"] = $case;
    $newcase = &MB_Ref($dossiertable, $nieuwdossiernaam);
    $newcase["caseid"] = $case;
    $newcase["root"] = $caseroot;
    // create folders in case
    $to = &CASE_TreeRef ($sgn, $caseroot);
echo "to $to CASE_TreeRef ( $sgn , $caseroot );<br>";
    for($k=0; $k<$foldersperdossier; $k++) {
      $foldername = sprintf("%08d", $k);
      $folderid = TREE_AddObject ($to, $case.$foldername);
echo "foldername $foldername folderid $folderid <br>";
      $folderobject = &TREE_AccessObject($to, $folderid);
      $folderobject["shorttitle"] = $foldername;
      TREE_ConnectObject ($to, $caseroot, $folderid);
    }
  }
  $specs=array();
  $list=array();
  for($j=0; $j<$numberofdossiers; $j++) { 
    $dossierteller = $dossierstart+$j;
    $nieuwdossiernaam = $dossiername . " " . sprintf("%16d", $dossierteller);
    $specs[$j] = array();
    $list[$j] = array();
    for($i=0; $i<$docsperdossier; $i++) $list[$j][$i] = $i;
echo "create terra $j caseroot " . $dossiers[$j]["caseroot"] .  " case " . $dossiers[$j]["case"] . "<br>";
echo "create terra $j parentobject " . $newobject_small ." list " . count($list) ."<br>";
    $specs[$j]["title"] = "Benchmark: Creating documents";
    $specs[$j]["list"] = $list[$j];
    $specs[$j]["input"]["sgn"] = $sgn;
//      $specs[$j]["input"]["parentfolder"] = $parentfolder;
    $specs[$j]["input"]["parentfolder"] = $dossiers[$j];
    $specs[$j]["input"]["parentobject"] = $newobject_small;
    $specs[$j]["input"]["newwfl"] = $newwfl;
    $specs[$j]["input"]["user"] = $user;
    $specs[$j]["input"]["terranumber"] = $j;
    $specs[$j]["input"]["objecttable"] = $objecttable;
    $specs[$j]["input"]["dossierstart"] = $dossierstart;
    $specs[$j]["input"]["numberofdossiers"] = $numberofdossiers;
    $specs[$j]["input"]["foldersperdossier"] = $foldersperdossier;
    $specs[$j]["input"]["docsperdossier"] = $docsperdossier;

    $specs[$j]["step_code"] = '
      $starttime = time ();
      $terranumber = $input["terranumber"];
      N_LOG("bench", "terra $terranumber create doc small START $index starttime " . $starttime);
      uuse("ims");
      uuse ("terra");
      $sgn = $input["sgn"];
      $parentfolder = $input["parentfolder"]["caseroot"];
      $case = $input["parentfolder"]["case"];
      $parentobject = $input["parentobject"];
      $newwfl = $input["newwfl"];
      $user = $input["user"];
      $dossierstart = $input["dossierstart"];
      $dossierteller = $dossierstart+$terranumber;

      $numberofdossiers = $input["numberofdossiers"];
      $foldersperdossier= $input["foldersperdossier"];
      $docsperdossier= $input["docsperdossier"];
      $filesperfolder = ceil($docsperdossier/($numberofdossiers*$foldersperdossier));
      // calculate targetfolder to insert document into
      $targetfolder= $case.sprintf("%08d",floor($index/$filesperfolder));
      N_LOG("bench", "terra $terranumber dossierteller $dossierteller :$docsperdossier::$numberofdossiers::$foldersperdossier::$filesperfolder:");

      $newfilename = "terra" . sprintf("%07d", $dossierteller) . "docsmall" . sprintf("%07d", $index) . ".doc";
      $newid = "xs" . sprintf("%016d", $dossierteller) . sprintf("%016d", $index);
      $docid = IMS_NewDocumentObject ($sgn, $targetfolder, $parentobject, $newid, $newfilename);
      N_LOG("bench", "terra $terranumber dossierteller $dossierteller create doc $index id $docid");
      N_LOG("bench", "terra $terranumber dossierteller $dossierteller create doc $targetfolder");
      N_LOG("bench", "terra $terranumber dossierteller $dossierteller create doc sgn $sgn newfilename $newfilename");
      $newobject = &MB_Ref($input["objecttable"], $docid);
      $newobject["directory"] = $targetfolder;
      $object = &IMS_AccessObject ($sgn, $docid);
      $object["shorttitle"] = $newfilename;
      $object["longtitle"] = $newfilename;
      $object["workflow"] = $newwfl;
      $object["stage"] = 1;
      $object["executable"]="winword.exe";
      IMS_ArchiveObject ($sgn, $docid, $user, true);
      SEARCH_AddPreviewDocumentToDMSIndex ($sgn, $docid);
      $endtime = time ();
      $elapsed = $endtime - $starttime;
      N_LOG("bench", "terra $terranumber dossierteller $dossierteller create doc small FINISH $index elapsed $elapsed endtime $endtime");
    ';
    $specs[$j]["init_code"] = '
    ';
    $specs[$j]["exit_code"] = '
      $terranumber = $input["terranumber"];
      N_LOG("bench", "terra small $terranumber FINISH (Exitcode)");
    ';
    
    TERRA_Multi_List($specs[$j]);
    echo "Terra small $j started  <br>";
  }
}

// this function wil create lots of copies of the small lorem doc
function BENCH_CreateMultipleCopiesOfSmallDocument($sgn, $docsperdossier, $numberofdossiers, $foldersperdossier, $dossiername, $dossierstart, $numberofthreads=2) {
  N_LOG("bench", "start $sgn, $docsperdossier, $numberofdossiers, $foldersperdossier, $dossiername, $dossierstart, $numberofthreads");
  // create base document

  $dossiertable = "bench_" . $sgn . "_dossiers";
  $objecttable = "bench_" . $sgn . "_objects";

  $path = "html::/openims/libs/performance/lorem-word-klein.doc";
  $user = base64_decode ("dWx0cmF2aXNvcg==");
  $parentfolder = "root";
  $newwfl = "edit-review-publish";

  $newobject_small = BENCH_InsertDocInDMS($sgn, $path, $user, $parentfolder, "base example small", "base example small", $newwfl , array());
echo "newobject_small $newobject_small <br>";
  // use terra to make copies of base document

  // first create $numberofdossiers-1 dossiers
  $dossiers = array();
  for($j=0; $j<$numberofdossiers; $j++) {
    $dossierteller = $dossierstart+$j;
    $nieuwdossiernaam = $dossiername . " " . sprintf("%16d", $dossierteller);
    $nieuwdossieromschrijving = $nieuwdossiernaam;
    $case = CASE_Create ($sgn, $nieuwdossiernaam , $nieuwdossieromschrijving );
echo "case $case $nieuwdossiernaam <br>";
    $caseroot = $case . "root";
    $dossiers[$j]["caseroot"] = $caseroot;
    $dossiers[$j]["case"] = $case;
    $newcase = &MB_Ref($dossiertable, $nieuwdossiernaam);
    $newcase["caseid"] = $case;
    $newcase["root"] = $caseroot;
    // create folders in case
    $to = &CASE_TreeRef ($sgn, $caseroot);
echo "to $to CASE_TreeRef ( $sgn , $caseroot );<br>";
    for($k=0; $k<$foldersperdossier; $k++) {
      $foldername = sprintf("%08d", $k) . "k";
      $folderid = TREE_AddObject ($to, $case.$foldername);
echo "foldername $foldername folderid $folderid <br>";
      $folderobject = &TREE_AccessObject($to, $folderid);
      $folderobject["shorttitle"] = $foldername;
      TREE_ConnectObject ($to, $caseroot, $folderid);
    }
  }

  $totaldocs=$docsperdossier*$numberofdossiers;
  $docsperfolder=ceil($docsperdossier/$foldersperdossier);
  $docsperthread=ceil($totaldocs/$numberofthreads);
echo "totaldocs $totaldocs docsperfolder $docsperfolder docsperthread $docsperthread numberofthreads $numberofthreads<br>";
  $curdoc=0;
  for($thread=0; $thread<$numberofthreads; $thread++) {
    $specs = array();
    $list=array();
    for($doc=0; $doc<$docsperthread; $doc++) $list[$doc]=$doc;
    $specs["title"] = "Benchmark: Creating documents";
    $specs["list"] = $list;
    $specs["input"]["sgn"] = $sgn;

    $specs["input"]["parentobject"] = $newobject_small;
    $specs["input"]["dossiers"] = $dossiers;

    $specs["input"]["newwfl"] = $newwfl;
    $specs["input"]["user"] = $user;
    $specs["input"]["terranumber"] = $thread;
    $specs["input"]["objecttable"] = $objecttable;

    $specs["input"]["numberofthreads"] = $numberofthreads;
    $specs["input"]["docsperdossier"] = $docsperdossier;
    $specs["input"]["numberofdossiers"] = $numberofdossiers;
    $specs["input"]["foldersperdossier"] = $foldersperdossier;
    $specs["input"]["dossiername"] = $dossiername;
    $specs["input"]["dossierstart"] = $dossierstart;

    $specs["input"]["totaldocs"] = $totaldocs;
    $specs["input"]["docsperfolder"] = $docsperfolder;
    $specs["input"]["docsperthread"] = $docsperthread;

    $specs["step_code"] = '
      $starttime = time ();
      uuse("ims");
      uuse ("terra");
      $sgn = $input["sgn"];
      $parentobject = $input["parentobject"];
      $dossiers = $input["dossiers"];

      $newwfl = $input["newwfl"];
      $user = $input["user"];
      $terranumber = $input["terranumber"];
      $objecttable = $input["objecttable"];

      $numberofthreads = $input["numberofthreads"];
      $docsperdossier= $input["docsperdossier"];
      $numberofdossiers = $input["numberofdossiers"];
      $foldersperdossier= $input["foldersperdossier"];
      $dossiername = $input["dossiername"];
      $dossierstart = $input["dossierstart"];

      $totaldocs = $input["totaldocs"];
      $docsperfolder = $input["docsperfolder"];
      $docsperthread = $input["docsperthread"];

      N_LOG("bench", "terra $terranumber create doc small START $index starttime " . $starttime);
      $curdoc = ($terranumber*$docsperthread)+$index;

      $dossier=floor($curdoc/$docsperdossier);
      $folder=floor(($curdoc-($dossier*$docsperdossier))/$docsperfolder);
      $docnumber=$curdoc-($dossier*$docsperdossier)-($folder*$docsperfolder);
      $dossier+=$dossierstart;

      $case=$dossiers[$dossier]["case"];
      $caseroot=$dossiers[$dossier]["caseroot"];

      // calculate targetfolder to insert document into
      $targetfolder= $case.sprintf("%08d",$folder). "k";

      N_LOG("bench", "terra $terranumber dossier $dossier folder $folder docnumber $docnumber");

      $newfilename = "docsmall-dossier" . sprintf("%06d", $dossier) . "folder" . sprintf("%06d", $folder) . "kdocnumber" . sprintf("%06d", $docnumber) . ".doc";
      //$newid = $dossiername . sprintf("%011d", $dossier) . sprintf("%011d", $folder) . sprintf("%011d", $docnumber) . "o";
      $newid = N_GUID();
      $docid = IMS_NewDocumentObject ($sgn, $targetfolder, $parentobject, $newid, $newfilename);    
      N_LOG("bench", "terra $terranumber dossier $dossier create doc $index id $docid");
      N_LOG("bench", "terra $terranumber dossier $dossier create doc $targetfolder");
      N_LOG("bench", "terra $terranumber dossier $dossier create doc sgn $sgn newfilename $newfilename");

      $newobject = &MB_Ref($input["objecttable"], $docid);
      $newobject["directory"] = $targetfolder;
      $object = &IMS_AccessObject ($sgn, $docid);
      $object["shorttitle"] = $newfilename;
      $object["longtitle"] = $newfilename;
      $object["workflow"] = $newwfl;
      $object["stage"] = 1;
      $object["executable"]="winword.exe";
      IMS_ArchiveObject ($sgn, $docid, $user, true);
      SEARCH_AddPreviewDocumentToDMSIndex ($sgn, $docid);
      $endtime = time ();
      $elapsed = $endtime - $starttime;
      N_LOG("bench", "terra $terranumber dossierteller $dossierteller create doc small FINISH $index elapsed $elapsed endtime $endtime");
    ';
    $specs["init_code"] = '
    ';
    $specs["exit_code"] = '
      $terranumber = $input["terranumber"];
      N_LOG("bench", "terra small $terranumber FINISH (Exitcode)");
    ';
    
    TERRA_Multi_List($specs);
    echo "Terra small $thread started  <br>";

  }
}


function BENCH_InsertDocInDMS($sgn, $path, $user="qqqpngvqqq",$dmsfolder, $shorttitle="example", $longtitle="example", $wfl , $data) {
  if ($user="qqqpngvqqq") $user = base64_decode ("dWx0cmF2aXNvcg==");
  $object_id = N_GUID();
  IMS_NewObject ($sgn, "document", $object_id); 
  $object = &IMS_AccessObject ($sgn, $object_id);
  $object["allocto"] = $user;
  $object["directory"] = $dmsfolder;
  $object["shorttitle"] = $shorttitle;
  $object["longtitle"] = $longtitle;
  $object["workflow"] = $wfl;
  $path_parts = pathinfo(N_CleanPath($path));
  $filename = $path_parts["basename"];
  $object["filename"] = $filename;
  $ext = $path_parts["extension"];
  if ($ext=="doc") {
    $object["executable"]="winword.exe";
  } else if ($ext=="xls") {
    $object["executable"]="excel.exe";
  } else if ($ext=="htm" || $ext=="html") {
    $object["executable"]="notepad.exe";
  } else if ($ext=="ppt") {
    $object["executable"]="powerpnt.exe";
  } else {
    $object["executable"] = "auto"; // let windows determine the proper executable
  }  
  foreach ($data as $key => $value) {
    $object[$key] = $value;
  }
  global $myconfig;
  N_CopyFile ("html::".$sgn."/preview/objects/".$object_id."/".$filename, $path);
  IMS_ArchiveObject ($sgn, $object_id, $user, true);
  SEARCH_AddPreviewDocumentToDMSIndex ($sgn, $object_id);
  return $object_id;
}

function BENCH_Call_openimsphp() {
  global $benchdebug;

  global $mode, $submode, $thecase, $rootfolder, $currentfolder, $act, $wfl, $activeuser;
  global $tblsrt_dmsgrid, $tbldir_dmsgrid, $currentobject, $q; $q;
  // if($debug) BENCH_Dump_Globalvars ();

  if(false) {
    // start buffering output
    ob_start();
    include ('../openims/openims.php');
    if($debug)  $cntnt = ob_get_contents();
    ob_end_clean(); // discard any output
    if($debug) echo $cntnt;
  } else {
    global $VBrowser4GetPage;
    global $benchmarkdomain;
    $VBrowser4GetPage->setSite ($benchmarkdomain);
    $VBrowser4GetPage->setLogon ("admin", "admin");
    $params = array("mode"=>$mode, "submode"=>$submode, "thecase"=>$thecase,
      "rootfolder"=>$rootfolder, "currentfolder"=>$currentfolder, "act"=>$act, "wfl"=>$wfl, 
      "activeuser"=>$activeuser, "tblsrt_dmsgrid"=>$tblsrt_dmsgrid, "tbldir_dmsgrid"=>$tbldir_dmsgrid,
      "currentobject"=>$currentobject, "q"=>$q
    );
    $a = $VBrowser4GetPage->GET ("/openims/openims.php", $params);
    // if($benchdebug) echo $a;
  }
}

function BENCH_Dump_Globalvars() {
  global $mode, $submode, $thecase, $rootfolder, $currentfolder, $act, $wfl, $activeuser;
  global $tblsrt_dmsgrid, $tbldir_dmsgrid, $currentobject, $q;
  echo "mode $mode <br>";
  echo "submode $submode <br>";
  echo "thecase $thecase <br>";
  echo "rootfolder $rootfolder <br>";
  echo "currentfolder $currentfolder <br>";
  echo "act $act <br>";
  echo "wfl $wfl <br>";
  echo "activeuser $activeuser <br>";
  echo "tblsrt_dmsgrid $tblsrt_dmsgrid <br>";
  echo "tbldir_dmsgrid $tbldir_dmsgrid <br>";
  echo "currentobject $currentobject <br>";
  echo "q $q <br>";
}

function BENCH_Init_Globalvars() {
  global $mode, $submode, $thecase, $rootfolder, $currentfolder, $act, $wfl, $activeuser;
  global $tblsrt_dmsgrid, $tbldir_dmsgrid, $currentobject, $q, $searchmode, $qr1, $c1;
  global $qr2, $c2, $qr3, $c3, $qr4, $c4;
  global $qrn, $pstatus,$wstatus,$fileformat,$date,$sortby;

  $mode = "";
  $submode = "";
  $thecase = "";
  $rootfolder = "";
  $currentfolder = "";
  $act = "";
  $wfl = "";
  $activeuser = "";
  $tblsrt_dmsgrid = "";
  $tbldir_dmsgrid = "";
  $currentobject = "";
  $q = "";
  $searchmode = "";
  $c1 = "";
  $qr1 = "";
  $c2 = "";
  $qr2 = "";
  $qr3 = "";
  $c3 = "";
  $qr4 = "";
  $c4 = "";

  $qrn = "";
  $pstatus = "";
  $wstatus = "";
  $fileformat = "";
  $date = "";
  $sortby = "";
}

function BENCH_DeleteDocFromDMS($testid, $testcntr, $sgn, $id) {

  global $benchdebug;
  if($benchdebug) echo "BENCH_DeleteDocFromDMS $testid $testcntr start<br>";
  BENCH_LOG($testid, "$testcntr BENCH_DeleteDocFromDMS", "start", "");

  $starttime = time();
  IMS_Delete ($sgn, "", $id);

  $endtime = time();

  $timedelta = $endtime - $starttime;
  BENCH_LOG($testid, "$testcntr BENCH_DeleteDocFromDMS", "end", $timedelta);
  if($benchdebug) echo "BENCH_DeleteDocFromDMS $testid $testcntr end " . $timedelta . "<br>";
  N_Flush();

}

function BENCH_Test_DMS_Allocated($testid, $testcntr, $currentobjectin="", $activeuserin="") {
  // openims.php?mode=dms&submode=alloced
  // openims.php?mode=dms&submode=alloced&thecase=&rootfolder=&currentfolder=&act=&wfl=&activeuser=...&tblsrt_dmsgrid=&tbldir_dmsgrid=&currentobject=508da194d04e0dd1c438c62c9703841f

  BENCH_Init_Globalvars();

  global $mode;
  $mode = "dms";
  global $submode;
  $submode = "alloced";

  global $currentobject;
  $currentobject = $currentobjectin;
  global $activeuser;
  $activeuser = $activeuserin;

  global $benchdebug;
  if($benchdebug) echo "BENCH_Test_DMS_Allocated $testid $testcntr start<br>";
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_Allocated", "start", "");

  $starttime = time();
  BENCH_Call_openimsphp();
  $endtime = time();

  $timedelta = $endtime - $starttime;
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_Allocated", "end", $timedelta);
  if($benchdebug) echo "BENCH_Test_DMS_Allocated $testid $testcntr end " . $timedelta . "<br>";
  N_Flush();
}

function BENCH_Test_DMS_Main($testid, $testcntr, $currentobjectin="", $currentfolderin="root") {
// openims.php?mode=dms&submode=documents&thecase=&rootfolder=&currentfolder=root&act=&wfl=&activeuser=&tblsrt_dmsgrid=&tbldir_dmsgrid=&currentobject=10a13267740b6886ce09abae6b03bf80

  BENCH_Init_Globalvars();
  global $mode;
  $mode = "dms";

  global $submode;
  $submode = "documents";
  
  global $currentfolder;
  $currentfolder = $currentfolderin;
  
  global $currentobject;
  $currentobject = $currentobjectin;

  global $benchdebug;
  if($benchdebug) echo "BENCH_Test_DMS_Main $testid $testcntr start<br>";
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_Main", "start", "obj: $currentobjectin fldr: $currentfolderin");

  $starttime = time();
  BENCH_Call_openimsphp();
  $endtime = time();

  $timedelta = $endtime - $starttime;
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_Main", "end", $timedelta);
  if($benchdebug) echo "BENCH_Test_DMS_Main $testid $testcntr end " . $timedelta . "<br>";
  N_Flush();
}

function BENCH_Test_DMS_Search($testid, $testcntr, $question) {
// openims.php?mode=dms&submode=search&q=lorem+ipsum

  BENCH_Init_Globalvars();
  global $mode;
  $mode = "dms";

  global $submode;
  $submode = "search";
  
  global $q;
  $q = $question;

  global $benchdebug;
  if($benchdebug) echo "BENCH_Test_DMS_Search $testid $testcntr start<br>";
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_Search", "start", "obj: $currentobjectin fldr: $currentfolderin");

  $starttime = time();
  global $VBrowser4GetPage;
  global $benchmarkdomain;
  $VBrowser4GetPage->setSite ($benchmarkdomain);
  $VBrowser4GetPage->setLogon ("admin", "admin");
  $params = array("mode"=>"dms", "submode"=>"search", "q"=>$q);
  $a = $VBrowser4GetPage->GET ("/openims/openims.php", $params);
//  if($benchdebug) echo $a;
//  BENCH_Call_openimsphp();
  $endtime = time();

  $timedelta = $endtime - $starttime;
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_Search", "end", $timedelta);
  if($benchdebug) echo "BENCH_Test_DMS_Search $testid $testcntr end " . $timedelta . "<br>";
  N_Flush();
}

function BENCH_Test_DMS_Search_Advanced($testid, $testcntr, $qr1in="", $c1in="", $qr2in="", $c2in="", $qr3in="", $c3in="", $qr4in="", $c4in="", 
  $qrnin="", $pstatusin="", $wstatusin="", $fileformatin="", $datein="", $sortbyin="") {
// openims.php?mode=dms&submode=search&searchmode=advanced&qr1=
// openims.php?mode=dms&submode=search&searchmode=advanced&qr1=07051
//   &c1=meta_documentnummer&qr2=&c2=&qr3=&c3=&qr4=&c4=
//   &qrn=&pstatus=&wstatus=&fileformat=&date=&sortby=

  BENCH_Init_Globalvars();
  global $mode;
  $mode = "dms";
  global $submode;
  $submode = "search";
  global $searchmode;
  $searchmode = "advanced";
  global $qr1;
  $qr1 = $qr1in;
  global $c1;
  $c1 = $c1in;
  global $qr2;
  $qr2 = $qr2in;
  global $c2;
  $c2 = $c2in;
  global $qr3;
  $qr3 = $qr3in;
  global $c3;
  $c3 = $c3in;
  global $qr4;
  $qr4 = $qr4in;
  global $c4;
  $c4 = $c4in;

  global $qrn;
  $qrn = $qrnin;
  global $pstatus;
  $pstatus = $pstatusin;
  global $wstatus;
  $wstatus = $wstatusin;
  global $fileformat;
  $fileformat = $fileformatin;
  global $date;
  $date = $datein;
  global $sortby;
  $sortby = $sortbyin;

  global $benchdebug;
  if($benchdebug) echo "BENCH_Test_DMS_Search_Advanced $testid $testcntr start<br>";
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_Search_Advanced", "start", "obj: $currentobjectin fldr: $currentfolderin");

  $starttime = time();
//  BENCH_Call_openimsphp();

  global $VBrowser4GetPage;
  global $benchmarkdomain;
  $VBrowser4GetPage->setSite ($benchmarkdomain);
  $VBrowser4GetPage->setLogon ("admin", "admin");
  $params = array("mode"=>"dms", "submode"=>"search", "searchmode"=>"advanced", "qr1"=>$qr1in, "c1"=>$c1in,
                  "qr2"=>$qr2in, "c2"=>$c2in, "qr3"=>$qr3in, "c3"=>$c3in, "qr4"=>$qr4in, "c4"=>$c4in,
                  "qrn"=>$qrnin, "pstatus"=>$pstatusin, "wstatus"=>$wstatusin, "fileformat"=>$fileformatin,
                  "fileformat"=>$fileformatin, "date"=>$datein, "sortby"=>$sortbyin);
  $a = $VBrowser4GetPage->GET ("/openims/openims.php", $params);
  // echo $a;
  $endtime = time();

  $timedelta = $endtime - $starttime;
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_Search_Advanced", "end", $timedelta);
  if($benchdebug) echo "BENCH_Test_DMS_Search_Advanced $testid $testcntr end " . $timedelta . "<br>";
  N_Flush();
}


function BENCH_Test_DMS_History($testid, $testcntr, $currentobjectin) {
//openims.php?mode=history&back=http%3A%2F%2Finternalbenchmarkdonotuse%2Fopenims%2Fopenims.php%3Fmode%3Ddms%26submode%3Ddocuments%26thecase%3D%26rootfolder%3D%26currentfolder%3Droot%26act%3D%26wfl%3D%26activeuser%3D%26tblsrt_dmsgrid%3D%26tbldir_dmsgrid%3D%26currentobject%3D508da194d04e0dd1c438c62c9703841f
//     &object_id=508da194d04e0dd1c438c62c9703841f

  global $benchmarkurl;

  global $benchdebug;
  if($benchdebug) echo "BENCH_Test_DMS_History $testid $testcntr start<br>";
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_History", "start", "obj: $currentobjectin");

  $starttime = time();
  global $VBrowser4GetPage;
  global $benchmarkdomain;
  $VBrowser4GetPage->setSite ($benchmarkdomain);
  $VBrowser4GetPage->setLogon ("admin", "admin");
  $params = array("mode"=>"history", "object_id"=>$currentobjectin);
  $a = $VBrowser4GetPage->GET ("/openims/openims.php", $params);
//  $a = N_Getpage($benchmarkurl. "/openims/openims.php?mode=history&object_id=" . $currentobjectin);
//  echo $a;

  $endtime = time();

  $timedelta = $endtime - $starttime;
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_History", "end", $timedelta);
  if($benchdebug) echo "BENCH_Test_DMS_History $testid $testcntr end " . $timedelta . "<br>";
  N_Flush();
}

function BENCH_LOG($testid, $testfunction, $phase, $extra) {
  N_LOG("bench", "# $testid : $testfunction : $phase : $extra ");
}

function BENCH_Test_DMS_DMSUIF_Properties ($testid, $testcntr, $supergroupname, $key, $newprops, $icononly=false)
{
  global $benchdebug;
  if($benchdebug) echo "BENCH_Test_DMS_DMSUIF_Properties $testid $testcntr start<br>";
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_DMSUIF_Properties", "start", "");
  $starttime = time();

  $objectkey = $key;
  if (SHIELD_HasObjectRight ($supergroupname, $key, "edit") || SHIELD_HasObjectRight ($supergroupname, $key, "view")) { 
    $object = &MB_Ref ("ims_" . $supergroupname . "_objects", $objectkey); 
    $title = ML("Eigenschappen van","Properties of")." &quot;".$object["shorttitle"]."&quot;";
    $metaspec = array();
    $metaspec["fields"]["workflow"]["type"] = "list";
    $metaspec["fields"]["workflow"]["default"] = "edit-publish";
    $metaspec["fields"]["workflow"]["show"] = "visible";
    $wlist = MB_Query ("shield_".$supergroupname."_workflows", '$record["dms"]', 'strtolower($record["name"])'); 
    $secsection = SHIELD_SecuritySectionForFolder ($supergroupname, $object["directory"]);
    $allowed = SHIELD_AllowedWorkflows ($supergroupname, $key, $secsection);
    if (is_array($wlist)) reset($wlist); 
    if (is_array($wlist)) while (list($wkey)=each($wlist)) { 
      if ($allowed[$wkey]) {            
        $workflow = &MB_Ref ("shield_".$supergroupname."_workflows", $wkey);
        $metaspec["fields"]["workflow"]["values"][$workflow["name"]] = $wkey;
      }
    }
    $metaspec["fields"]["longtitle"]["type"] = "string";
    $metaspec["fields"]["shorttitle"]["type"] = "string";
    $metaspec["fields"]["shorttitle"]["title"] = ML("Document naam","Document name");
    $metaspec["fields"]["shorttitle"]["validationcode"] = '
      global $myconfig;

      if ($myconfig[IMS_SuperGroupName()]["hasshorttitlevalidation"]=="yes") {                    
        eval ($myconfig[IMS_SuperGroupName()]["shorttitlevalidationcode"]);
      }

      if (!trim($input)) {
        $error = ML("is leeg","is empty");
      }
    ';

    $workflow = &MB_Ref ("shield_".$supergroupname."_workflows", $object["workflow"]);
    $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
    $obj = MB_Load ("ims_".$supergroupname."_objects", $key);
    for ($i=1; $i<100; $i++) {
      if ($workflow["meta"][$i]) {
        $metalist[$workflow["meta"][$i]] = $workflow["meta"][$i];
      }
    }
    if ($obj["dynmeta"] && is_array ($obj["dynmeta"])) {
      foreach ($obj["dynmeta"] as $dummy => $field) {
        $metalist[$field] = $field;
      }
    }
    foreach ($metalist as $field) {
    $metaspec["fields"]["meta_".$field] = $allfields[$field];
    }

    global $myconfig;
    if ($myconfig[$supergroupname]["autohtml"]) {
      if (($obj["executable"] == "winword.exe") && ($myconfig[$supergroupname]["autohtml"]["showfield"] != "no")) {
        $metaspec["fields"]["meta_autohtml"]["type"] = "list";
        $metaspec["fields"]["meta_autohtml"]["title"] = $myconfig[$supergroupname]["autohtml"]["title"];
        $metaspec["fields"]["meta_autohtml"]["values"][ML("Geen HTML conversie", "No HTML conversion")] = "";
        foreach ($myconfig[$supergroupname]["autohtml"]["options"] as $wgkey => $wgvalues) {
          $metaspec["fields"]["meta_autohtml"]["values"][$wgvalues["name"]] = $wgkey;
        }
        $metalist["autohtml"] = "autohtml";
      }
    }

    $formtemplate  = '<body bgcolor=#f0f0f0><br><center><table>
                            <tr><td><font face="arial" size=2><b>'.ML("Naam","Name").':</b></font></td><td>[[[shorttitle]]]</td></tr>
                            <tr><td><font face="arial" size=2><b>'.ML("Omschrijving","Description").':</b></font></td><td>[[[longtitle]]]</td></tr>
                            <tr><td><font face="arial" size=2><b>'.ML("Workflow","Workflow").':</b></font></td><td>[[[workflow]]]</td></tr>';
    foreach ($metalist as $field) {
      $formtemplate .= '<tr><td><font face="arial" size=2><b>'.$metaspec["fields"]["meta_".$field]["title"].':</b></font></td><td>[[[meta_'.$field.']]]</td></tr>';
    }

    if (!SHIELD_HasObjectRight ($supergroupname, $key, "edit")) { 
      $formtemplate = str_replace ("[[[", "&nbsp;<font face=\"arial\" size=2>(((", $formtemplate);
      $formtemplate = str_replace ("]]]", ")))</font>", $formtemplate);
      $formtemplate .= '<tr><td colspan=2>&nbsp</td></tr>
                              <tr><td colspan=2><center>[[[OK]]]</center></td></tr>
                              </table></center></body>';
    } else {
    
      $formtemplate .= '<tr><td colspan=2>&nbsp</td></tr>
                              <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
                              </table></center></body>';
    }
    
    $input["sgn"] = $supergroupname;
    $input["id"] = $key; 
    if ($object["published"]=="yes") {
      $code = '
              N_SuperQuickScedule (N_CurrentServer(), \'SEARCH_AddPreviewDocumentToDMSIndex ($input ["sgn"], $input ["id"]);\', $input);
              N_SuperQuickScedule (N_CurrentServer(), \'SEARCH_AddDocumentToDMSIndex ($input ["sgn"], $input ["id"]);\', $input);
      ';
    } else {
      $code = '
        N_SuperQuickScedule (N_CurrentServer(), \'SEARCH_AddPreviewDocumentToDMSIndex ($input ["sgn"], $input ["id"]);\', $input);
      ';
    }

    // add changes to properties to history
    $obj_pre_id = SHIELD_Encode (MB_Ref("ims_" . $supergroupname . "_objects", $key));
    $code .= '$obj_pre = SHIELD_Decode ("' . $obj_pre_id . '");
      $allfields = MB_Load ("ims_fields", IMS_SuperGroupName());
      $ret = array();
      foreach($rec as $key=>$value) {
        if(($key=="shorttitle") || ($key=="longtitle") || ($key=="workflow") || (substr($key,0,5)=="meta_") ) {
          if($rec[$key] != $obj_pre[$key]) {
            $new_ret = array();
            $new_ret["key"] = $key;
            if(substr($key,0,5)=="meta_") {
              $new_ret["title"] = $allfields[substr($key,5)]["title"];
            } else {
              $new_ret["title"] = $key;
            }
            switch($key) {
              case "workflow":
                $new_ret["old"] = MB_Fetch("shield_" . IMS_SuperGroupName() . "_workflows", $obj_pre[$key], "name");
                $new_ret["new"] = MB_Fetch("shield_" . IMS_SuperGroupName() . "_workflows", $rec[$key], "name");
                break;
              case "meta_autohtml":
                global $myconfig;
                // If the object is published, and meta_autohtml used to be empty (now its not, because
                // were looping through properties that have changed), a link to the published html version
                // will appear in DMS and webgen will generate the html in the next batch.
                // So create stub page if this functionality is enabled in myconfig.
                if ((!$obj_pre[$key]) && ($obj_pre["published"] == "yes") && ($myconfig[$input["sgn"]]["autohtml"]["createstubpage"] == "yes")) {
                  uuse ("webgen");
                  WEBGEN_CreateStubPage($input["sgn"], $input["id"]);
                }
                // no break.  falling through on purpose.  DO NOT ADD NEW "case"s AT THIS POSITION!
              default:
                if(substr($key,0,5)=="meta_") {
                  $new_ret["old"] = FORMS_ShowValue($obj_pre[$key] , substr($key,5),$obj_pre ,$obj_pre);
                  $new_ret["new"] = FORMS_ShowValue($rec[$key] , substr($key,5), $rec, $rec);  
                } else {
                  $new_ret["old"] = $obj_pre[$key];
                  $new_ret["new"] = $rec[$key];  
                }
            }
            $ret[]=$new_ret;
          }
        }
      }
      if($ret) {
        global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
        $time = time();
        $guid = N_GUID();
        $rec["history"][$guid]["when"] = $time;
        $rec["history"][$guid]["author"] = SHIELD_CurrentUser ($sitecollection_id);
        $rec["history"][$guid]["type"] = "properties";
        $rec["history"][$guid]["server"] = N_CurrentServer ();
        $rec["history"][$guid]["http_host"] = getenv("HTTP_HOST");
        $rec["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
        $rec["history"] [$guid]["server_addr"] = $SERVER_ADDR;
        $rec["history"][$guid]["changes"] = $ret;
      }
    ';
    $returnlink = "";
    if ($icononly) {
      $returnlink .= "&nbsp";
      $returnlink .= FORMS_GenerateEditExecuteLink ($code, $input, $title, '<img border="0" src="/openims/properties_small.gif">', $title, "ims_".$supergroupname."_objects", $key, '', $metaspec, $formtemplate, 400, 200, 200, 200, "ims_navigation");
      $returnlink .= "<br>";
    } else {
      $returnlink .= FORMS_GenerateEditExecuteLink ($code, $input, $title, '<img border="0" src="/openims/properties_small.gif"> '.ML("Eigenschappen","Properties"), $title, "ims_".$supergroupname."_objects", $key, '', $metaspec, $formtemplate, 400, 200, 200, 200, "ims_navigation");
      $error = FORMS_BlindValidation ($supergroupname, $key); 
      if ($error) {
        $returnlink .= " <span title=\"$error\"><font color=#ff0000><b>!!!</b></font></span>";
      }
      $returnlink .= "<br>";
    }
    $rec = &MB_Ref ("ims_" . $supergroupname . "_objects", $objectkey);
    foreach($newprops as $newpropkey=>$newpropvalue) {
      $rec[$newpropkey] = $newpropvalue;
    }
    $rec["shorttitle"] = $rec["shorttitle"] . "...";
    $input["sgn"] = $supergroupname;
    $input["id"] = $objectkey;
    eval($thecode);
  }
  MB_FLUSH();

  $endtime = time();

  $timedelta = $endtime - $starttime;
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_DMSUIF_Properties", "end", $timedelta);
  if($benchdebug) echo "BENCH_Test_DMS_DMSUIF_Properties $testid $testcntr end " . $timedelta . "<br>";
  N_Flush();
  return $returnlink;
}

function BENCH_Test_DMS_Postverwerking ($testid, $testcntr, $sgn, $key, $fieldlist, $newvalues, $wflstep) {
  global $benchdebug;
  if($benchdebug) echo "BENCH_Test_DMS_Postverwerking $testid $testcntr start<br>";
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_Postverwerking", "start", "");
  $starttime = time();

  $input["sgn"] = $sgn;
  $input["currentobject"] = $key;
  $input["velden"] = $fieldlist;
  $data = $newvalues;

  $obj = &MB_Ref("ims_" . $input["sgn"] . "_objects", $input["currentobject"]);
  
  $ret = array();
  $allfields = MB_Load ("ims_fields", IMS_SuperGroupName());
  foreach($input["velden"] as $i=>$veld) {
    if($veld != "projectnaam_dossiernaam") {
      if($obj["meta_".$veld] != $data[$veld])
      {
        $new_ret = array();
        $new_ret["key"] = $veld;
        $new_ret["title"] = $allfields[$veld]["title"];
        $new_ret["old"] = FORMS_ShowValue($obj["meta_" . $veld], $veld, $obj, $obj);
        $obj["meta_".$veld] = $data[$veld];

        $new_ret["new"] = FORMS_ShowValue($obj["meta_" . $veld], $veld, $obj, $obj);
        $ret[]=$new_ret;
      }
    }
  }
  if($ret) {
    global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
    $time = time();
    $guid = N_GUID();
    $obj["history"][$guid]["when"] = $time;
    $obj["history"][$guid]["author"] = SHIELD_CurrentUser ($sitecollection_id);
    $obj["history"][$guid]["type"] = "properties";
    $obj["history"][$guid]["server"] = N_CurrentServer ();
    $obj["history"][$guid]["http_host"] = getenv("HTTP_HOST");
    $obj["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
    $obj["history"][$guid]["server_addr"] = $SERVER_ADDR;
    $obj["history"][$guid]["changes"] = $ret;
  }
  if($data["actie"] == "goedkeuren") {
    $history["user_id"] = SHIELD_CurrentUser();
    $history["timestamp"] = time();
    SHIELD_ProcessOption ($input["sgn"], $input["currentobject"], "Post verwerkings assistent", $history);
  }
  MB_FLUSH();
  $endtime = time();

  $timedelta = $endtime - $starttime;
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_Postverwerking", "end", $timedelta);
  if($benchdebug) echo "BENCH_Test_DMS_Postverwerking $testid $testcntr end " . $timedelta ."<br>";
  N_Flush();
}

function BENCH_Test_DMS_NewDocument ($testid, $testcntr, $sgn) {
  global $benchdebug;
  if($benchdebug) echo "BENCH_Test_DMS_NewDocument $testid $testcntr start<br>";
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_NewDocument", "start", "");
  $starttime = time();
  // simulate new document assistent
  // simulate get template
  $dir = "root"; 
  $templatelist = MB_TurboSelectQuery ("ims_".$sgn."_objects", array(
    '$record["directory"]' => $dir,
    '$record["published"]=="yes" && $record["preview"]=="no"' => true,
    '$record["workflow"]' => "templates"
  ), '$record["shorttitle"]'); 
  $nieuwwfl = "edit-review-publish";
  $doctype = "doc";
  $newfilename = "aaaaaa." . $doctype;

  // $docid = IMS_NewDocumentObject ($sgn, $parentfolder, $parentobject, $newid, $newfilename);
  $docid = IMS_NewDocumentObject ($sgn, $dir, "x00000000000000000000000000000001", "", $newfilename);
  $object = &IMS_AccessObject ($sgn, $docid);
  $object["shorttitle"] = $newfilename;
  $object["longtitle"] = $newfilename;
  $object["workflow"] = $nieuwwfl;
  $object["stage"] = 1;

  $object["executable"] = "winword.exe";
  IMS_ArchiveObject ($sgn, $docid, base64_decode ("dWx0cmF2aXNvcg=="), true);
  SEARCH_AddPreviewDocumentToDMSIndex ($sgn, $docid);

  $endtime = time();

  $timedelta = $endtime - $starttime;
  BENCH_LOG($testid, "$testcntr BENCH_Test_DMS_NewDocument", "end", $timedelta);
  if($benchdebug) echo "BENCH_Test_DMS_NewDocument $testid $testcntr end " . $timedelta . "<br>";
  N_Flush();
  return $docid;
}
?>