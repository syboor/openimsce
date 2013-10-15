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



//********************************************************************************************************************
// S3 search engine 
// ----------------
//
// Can check rights on individual search results before returning them as an array. This will cost time and 
// performance. Therefore timelimits are configurable.
//
// Config:
//   $myconfig[$sgn]["S3"]["maxtime"]     : maximum search time
//   $myconfig[$sgn]["S3"]["mintime"]     : minimum search time, after this time, result is returned when wanted results are found 
//   $myconfig[$sgn]["S3"]["bonustime"]   : extra time that can be spend on post prosessing raw search results 

//   $myconfig[$sgn]["S3"]["sortmaxtime"] : maximum search time when sorting
//   $myconfig[$sgn]["S3"]["sortmintime"] : minimum search time when sorting, after this time, result is returned when wanted results are found 

//   $myconfig[$sgn]["S3"]["steps"]       : array of limits for native search engine
//   $myconfig[$sgn]["S3"]["checkrights"] : when set to "yes" view, viewpub an viewsearchresult rights are checked 
//                                          (the viewsearchresult right is added to the workflow settings)
//
//   N.B. This engine is only implemented for standaard and advanced DMS searching.
//********************************************************************************************************************


//********************************************************************************************************************
// Main engine layer
//********************************************************************************************************************


function S3_SEARCH ($specs) {
  // check params
  $index = $specs["index"];
  $query = $specs["query"];
  $friendlyquery = $specs["friendlyquery"]; // will be used for csv logging
  $from  = $specs["from"];
  if (!$from) $from = 1;
  $to = $specs["to"];
  if (!$to) $to=999999999;
  $sgn = $specs["sgn"];
  $filterexpression = $specs["filterexpression"];
  if (!$filterexpression) $filterexpression = "\$ok=true;";
  $sortexpression = $specs["sortexpression"];
  $multiloadexpression = $specs["multiloadexpression"];

  // common vars
  global $myconfig;
  $sortarray = array();
  $realresults = array();
  $realamount = 0;

  // set search parameters (defaults)
  $S3_steps = $myconfig[$sgn]["S3"]["steps"];
  if (!is_array($S3_steps)) $S3_steps = array(101, 501, 1001, 5001, 10001, 50001, 100001);
  $S3_maxtime = $myconfig[$sgn]["S3"]["maxtime"];
  if (!$S3_maxtime) $S3_maxtime = 20; // seconds
  $S3_mintime = $myconfig[$sgn]["S3"]["mintime"];
  if (!$S3_mintime) $S3_mintime = 0.25; // seconds
  $S3_bonustime = $myconfig[$sgn]["S3"]["bonustime"];
  if (!$S3_bonustime) $S3_bonustime = 1; // seconds

  $S3_sortmaxtime = $myconfig[$sgn]["S3"]["sortmaxtime"];
  if (!$S3_sortmaxtime) $S3_sortmaxtime = 20; //seconds
  $S3_sortmintime = $myconfig[$sgn]["S3"]["sortmintime"];
  if (!$S3_sortmintime) $S3_sortmintime = 3; //seconds

  // determine number of results to aim for
  if (($to != 999999999) && ($to > 0)) $wanted = $to;
  else $wanted = max($S3_steps);

  // get time limit  
  if ($S3_maxtime) $maxtime = $S3_maxtime;
  if ($S3_mintime) $mintime = $S3_mintime;
  if ($S3_bonustime) $bonustime = $S3_bonustime;

  $starttime = N_Microtime();
  global $SPHINXCOMPACT, $T_IN_SPHINXCMDTIME, $T_IN_SPHINXDTIME, $T_IN_SPHINXSQLTIME, $T_IN_SPHINXWAITFORLOCK, $SPHINXCACHE, $SPHINXPARTIAL; // for sphinx performance logging
  $SPHINXCOMPACT = $T_IN_SPHINXCMDTIME = $T_IN_SPHINXDTIME = $T_IN_SPHINXSQLTIME = $T_IN_SPHINXWAITFORLOCK = $SPHINXCACHE = $SPHINXPARTIAL = 0;

  // sorting gets more time
  if ($sortexpression) {
    $maxtime = $S3_sortmaxtime;
    $mintime = $S3_sortmintime;
  }

  // init step vars
  $results = 0;
  $stop = false;
  $steps = count($S3_steps);
  $mystep = 0;

  // go on until got wanted results and nobody tells you to stop
  while ($stop != true) {

     // debug info
     //echo "<br>-------------------------------";
     //echo "<br>steps: $steps";
     //echo "<br>mystep: $mystep";
     //echo "<br>stop: $stop";
     //echo "<br>to: ".$S3_steps[$mystep];
     //echo "<br>wanted: ".$wanted;
     //echo "<br>results:".$results;
     //N_Flush();

     // get limited results by step array

     $T_BEFORE_NATIVE = N_MicroTime();
     $smartresult = SEARCH_Native($index, $query, 0, $S3_steps[$mystep], $specs["extraenginespecs"]);
     $T_AFTER_NATIVE = N_MicroTime();
     $T_IN_NATIVE_TIME += ($T_AFTER_NATIVE - $T_BEFORE_NATIVE);
     $T_IN_NATIVE_STEPS++;
     $smartresult["llmore"] = ( ($smartresult["amount"] - $S3_steps[$mystep]) >= 0);

     // multiload speeds up this loop
     if ($specs["filterexpression"] || $specs["sortexpression"]) {
       $T_BEFORE_MULTILOAD = N_MicroTime();
       eval($multiloadexpression);
       $T_IN_MULTILOAD += N_MicroTime() - $T_BEFORE_MULTILOAD;
     }
     
     // counter for number of 'real' (validated) results, for 1 loop (all loops is in $realamount)
     $count = 0;
     $T_BEFORE_FILTERSORT = N_MicroTime();

     foreach ($smartresult["result"] as $key => $value) {
       
           $ok = true;

           // check expression
           if (!$realresults[$key]) {
             eval ($filterexpression);
           }

           if (!$ok) {         
             $smartresult["amount"]--;
           } else {
             $count++;
             if (!$realresults[$key]) {
               $realresults[$key] = $smartresult["result"][$key];
               $realamount++;

               // determine sortvalue in the same loop
               if ($sortexpression) {
                  $sortvalue = "";
                  eval ($sortexpression);
                  $sortarray[$key] = $sortvalue;
               }             
             }
           }       

        // when maxtime exceeds stop
// kill        if (N_Microtime() - $starttime > $maxtime) break;

        // only stop when mintime exceeds if we got enough results 
        if ((N_Microtime() - $starttime > $mintime + $bonustime) && ($realamount > $wanted)) {
           $smartresult["llmore"] = true; // There are (probably) more results
           break;
        }

     }
     $T_IN_FILTERSORT += N_MicroTime() - $T_BEFORE_FILTERSORT;

     // transform to original results array
     $results = $realamount;
 
     // should we stop?
     $mystep++;
     if ($mystep >= $steps) $stop = true;
     if (!$smartresult["llmore"]) $stop = true;

     if ((N_Microtime() - $starttime > $mintime) && ($realamount > $wanted)) {
       $stop = true;
       // LF20110411: with "checkrights" it is very common to see "results 1 to 10 of more than 25" when
       // paging reveals there are exactly 26 results. And it turns out OpenIMS knew it (that there were 
       // 26 results) all along. $bonustime (higher time limit in inner filtering loop than mintimelimit)
       // causes us to end up "here" even though all results have been filtered.
       // Solution: ONLY set "mintimelimitexpired" when there are more result (either because
       // we broke out of the filtering loop, or because SEARCH_Native gave us as much we asked
       // for but we didnt ask for more). Just make certain (elsewhere) that both of those conditions
       // set "llmore".
       if ($smartresult["llmore"]) $smartresult["mintimelimitexpired"] = 1;
     } else if (N_Microtime() - $starttime > $maxtime) {
       $stop = true;
       $smartresult["maxtimelimitexpired"] = 1;
     }




  }

  // transform to ancient array structure to maintain backwards compatibility
  $smartresult["result"] = $realresults;
  $smartresult["amount"] = $realamount;

  // multisort result set
  if ( ($sortexpression) && (count($smartresult["result"])>0) ) {
    //$sortarray = array_map('strtolower', $sortarray);
    //array_multisort($sortarray, SORT_ASC, SORT_STRING, $smartresult["result"]);
    array_multisort($sortarray, $smartresult["result"]);
  }
  
  if ($myconfig["searchlogging"]!="no" || $myconfig[IMS_SuperGroupName()]["searchcsvlogging"] == "yes") {
    $TOTALTIME = N_MicroTime() - $starttime;
    $record = array();


    $line = "q: \"$query\", ";
    $long = "query : \"$query\"<br/>";
    $record["query"] = $query;
    $record["friendlyquery"] = ($friendlyquery ? $friendlyquery : $query);

    $line .= "w: {$from}-{$wanted}, ";
    $long .= "wanted: {$from}-{$wanted}<br/>";
    $record["wanted"] = "{$from}-{$wanted}";

    if ($smartresult["amount"] < 1) {
      $line .= "r: <b><font color=\"#ff0000\">0</font></b>";
      $long .= "results: <b><font color=\"#ff0000\">0</font></b>";
    } else {
      $line .= "r: {$smartresult["amount"]}";
      $long .= "results: {$smartresult["amount"]}";
    }
    $record["results"] = "{$smartresult["amount"]}";
    if ($smartresult["mintimelimitexpired"]) {
      $line .= "*";
      $long .= " (mintime limit expired)";
      $record["results"] .= "*";
    }
    if ($smartresult["maxtimelimitexpired"]) {
      $line .= "!";
      $long .= " (maxtime limit expired)";
      $record["results"] .= "!";
    }
    $line .= ", "; $long .= "<br/>";

    if ($specs["sortexpression"]) {
      $line .= "sorted, ";
      $long .= "sorted: yes<br/>";
      //$longend .= "sortexpression: <pre>".htmlentities($specs["sortexpression"])."</pre><br/>";
      $record["sorted"] = true;
    } else {
      $long .= "sorted: no<br/>";
    }

    if ($specs["filterexpression"]) {
      $line .= "filtered, ";
      $long .= "filtered: yes<br/>";
      //$longend .= "filterexpression: <pre>".htmlentities($specs["filterexpression"])."</pre><br/>";
      $record["filtered"] = true;
    } else {
      $long .= "filtered: no<br/>";
    }

    if ($TOTALTIME > 5) {
      $line .= "totaltime: <b><font color=\"#ff0000\">" . (int)(1000*$TOTALTIME) . "</font></b>, ";
      $long .= "total time: <b><font color=\"#ff0000\">" . (int)(1000*$TOTALTIME) . "</font></b><br/>";
    } else {
      $line .= "totaltime: " . (int)(1000*$TOTALTIME) . ", ";
      $long .= "total time: " . (int)(1000*$TOTALTIME) . "<br/>";
    }
    $record["totaltime"] = (int)(1000*$TOTALTIME);

    if ($specs["multiloadexpression"]) {
      $line .= "multiload: " . (int)(1000*$T_IN_MULTILOAD) . ", ";
      $long .= "multiload time: " . (int)(1000*$T_IN_MULTILOAD) . "<br/>";
      //$longend .= "multiload expression: <pre>".htmlentities($specs["multiloadexpression"])."</pre><br/>";
    }
    if ($specs["filterexpression"]) {
      $line .= "filtersort: " . (int)(1000*$T_IN_FILTERSORT) . ", ";
      $long .= "filter and/or sort time: " . (int)(1000*$T_IN_FILTERSORT) . "<br/>";
    }
    $line .= "nativetime: " . (int)(1000*$T_IN_NATIVE_TIME) . ", ";
    $long .= "native time: " . (int)(1000*$T_IN_NATIVE_TIME) . "<br/>";
    $line .= "steps: {$T_IN_NATIVE_STEPS}, ";
    $long .= "steps: {$T_IN_NATIVE_STEPS}<br/>";
    if ($myconfig["ftengine"]=="S2_SPHINX2") {
      if ($T_IN_SPHINXDTIME) {
        $line .= "sphinxdtime: " . (int)(1000*$T_IN_SPHINXDTIME) . ", ";
        $long .= "sphinx daemon time: " . (int)(1000*$T_IN_SPHINXDTIME) . "<br/>";
      }
      if ($T_IN_SPHINXCMDTIME) {
        $line .= "sphinxcmdtime: " . (int)(1000*$T_IN_SPHINXCMDTIME) . ", ";
        $long .= "sphinx cmdline time: " . (int)(1000*$T_IN_SPHINXCMDTIME) . "<br/>";
      }
      $line .= "sphinxsqltime: " . (int)(1000*$T_IN_SPHINXSQLTIME) . ", ";
      $long .= "sphinx sql time: " . (int)(1000*$T_IN_SPHINXSQLTIME) . "<br/>";
      if ($T_IN_SPHINXWAITFORLOCK) {
        $line .= "sphinxwaitforlocktime: " . (int)(1000*$T_IN_SPHINXWAITFORLOCK) . ", ";
        $long .= "sphinxwaitforlocktime: " . (int)(1000*$T_IN_SPHINXWAITFORLOCK) . "<br/>";
      }
      $line .= "compact: $SPHINXCOMPACT, ";
      $long .= "compact: $SPHINXCOMPACT<br/>";
      if ($SPHINXCACHE) {
        $line .= "sphinxcache: yes, ";
        $long .= "sphinxcache: yes<br/>";
      }
      if ($SPHINXPARTIAL) {
        $line .= "sphinxpartial: yes, ";
        $long .= "sphinxpartial: yes<br/>";
      }
      if ($specs["extraenginespecs"]["S2_SPHINX2"] && $specs["extraenginespecs"]["S2_SPHINX2"]["sortby"]) {
        $line .= "sphinxsort: {$specs["extraenginespecs"]["S2_SPHINX2"]["sortby"]}, ";
        $long .= "sphinxsort: {$specs["extraenginespecs"]["S2_SPHINX2"]["sortby"]}<br/>";
      }
    }
    $line .= "index: $index";
    $long .= "index: $index<br/>";
    $record["index"] = $index;

    $long .= "user: ". SHIELD_CurrentUser(IMS_SuperGroupName()) . "<br/>";
    $record["user"] = SHIELD_CurrentUser(IMS_SuperGroupName());

    $long .= "url: " . N_MyFullUrl() . "<br/>";
    $long = $long . $longend;
    //$line .= ", specs: " . print_r($specs, 1);
    N_Log("search", $line, $line, $long);

    if ($myconfig[IMS_SuperGroupName()]["searchcsvlogging"]) {
      $table = "local_".IMS_SuperGroupName()."_searchlog";
      $record["when"] = time();
      MB_Save($table, N_Guid(), $record);
    }
  }

  // generate user message
  $realfrom = $from;
  $realto = $to;
  if ($smartresult["amount"] < $to) $realto = $smartresult["amount"];
  $smartresult["message"] = ML("Resultaat","Result")." $realfrom - $realto";
  if ($smartresult["maxtimelimitexpired"]) {
     $smartresult["extramessage"] = ML("De resultaten zijn beperkt omdat de tijdslimiet voor het zoeken is bereikt.","The results are limited because the timelimit has expired.");
  }
  if ($smartresult["mintimelimitexpired"]) 
    $smartresult["message"] .= " ".ML("van meer dan","of more than")." ".($smartresult["amount"]-1)." ".ML("resultaten","results");
  else
    $smartresult["message"] .= " ".ML("van","from")." ".$smartresult["amount"];
  if ($realamount == 0) {
     if ($smartresult["maxtimelimitexpired"]) {
       $smartresult["message"] = "";
       $smartresult["extramessage"] = "<br><b>".ML("Uw zoekopdracht heeft niets opgeleverd","Your search has not found anything").".</b><br><br>"; 
       $smartresult["extramessage"] .= ML("De resultaten zijn beperkt omdat de tijdslimiet voor het zoeken is bereikt.","The results are limited because the timelimit has expired.")."<br><br>";
       $smartresult["extramessage"] .= ML("Suggesties","Tips").":<br>";
       $smartresult["extramessage"] .= "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Zorg ervoor dat alle woorden goed gespeld zijn","Make sure the spelling is correct").".<br>";
       $smartresult["extramessage"] .= "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Probeer andere zoektermen","Try other terms").".<br>";
       $smartresult["extramessage"] .= "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Maak de zoektermen specifieker","Use more specific terms").".<br>";
     } else {
       $smartresult["message"] = "";
       $smartresult["extramessage"] = "<br><b>".ML("Uw zoekopdracht heeft niets opgeleverd","Your search has not found anything").".</b><br><br>"; 
       $smartresult["extramessage"] .= ML("Suggesties","Tips").":<br>";
       $smartresult["extramessage"] .= "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Zorg ervoor dat alle woorden goed gespeld zijn","Make sure the spelling is correct").".<br>";
       $smartresult["extramessage"] .= "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Probeer andere zoektermen","Try other terms").".<br>";
       $smartresult["extramessage"] .= "&nbsp;&nbsp;&nbsp;&nbsp;- ".ML("Maak de zoektermen algemener","Use more generic terms").".<br>";
     }
  }

  //T_EO($smartresult["amount"]);
  //T_EO($smartresult["message"]);
  //T_EO($smartresult["extramessage"]);

  return $smartresult;
}


//********************************************************************************************************************
// Expression layer
//********************************************************************************************************************


//---------------------------------------------------------------------
function S3_CMS_FilterExpression() {

  $sgn = IMS_SuperGroupName();
  global $myconfig;

  if ($myconfig[$sgn]["S3"]["checkrights"] == "yes") {
    $expression = '
        global $myconfig;
        $table = "ims_".$sgn."_objects";
        $ok = true; 
        $object_id = $key;
        $supergroupname = $sgn;

        // check right
        if ($ok) {
           if ($myconfig[$supergroupname]["S3"]["checkrights"] == "yes") {
              $ok = false;
              if (SHIELD_HasObjectRight ($supergroupname, $object_id, "view", false)) { // no autologon please
                 $ok = true;
              } else if (SHIELD_HasObjectRight ($supergroupname, $object_id, "viewpub", false) && $object["published"]=="yes") { 
                 $ok = true;
              } else if (SHIELD_HasObjectRight ($supergroupname, $object_id, "viewsearchresult", false)) {
                 $ok = true;
              }
           }
        }
    ';

    return $expression;
  } else {
    return ""; // ??? of SHIELD_HasObjectRight?
  }
}

function S3_BPMS_FilterExpression() {
  // BPMS doet volledige nafiltering (inclusief herberekenen van het totaal), dus filteren is niet nodig / niet zinnig
  // zonder grondige wijziging aan openims.php.
  return "";
}

function S3_DMS_NeedsFilter() {
  global $searchmode, $myconfig;
  $sgn = IMS_SuperGroupName();

  if ($myconfig[$sgn]["S3"]["checkrights"] == "yes") return true; // maybe not needed for administrators?

  if ($searchmode == "advanced") {
    global $wstatus, $date, $theindex, $qr, $qr1, $qr2, $qr3, $qr4, $qr5, $qr6, $qr7, $qr8, $qr9;
    global $c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $fileformat, $qrn, $fromdate, $todate;
    if ($wstatus || $date || $fileformat || $fromdate || $todate) return true;
    if ($qrn && $myconfig["ftengine"]!="S2_MYSQLFT" && $myconfig["ftengine"]!="S2_SPHINX2") return true;
    for ($i=1; $i<=9; $i++) { 
      if ($_REQUEST["qr".$i] && $_REQUEST["c".$i] && $_REQUEST["c".$i] != "content") return true;
    }
  }

  return false;

}

function S3_DMS_FilterExpression() {
  // Alleen filterexpressie (en bijbehorende multiload) als het echt noodzakelijk is.
  // Scheelt een hoop werk bij het dossierzoeken (geavanceerd zoekscherm, zonder dat nafiltering / multiload nodig is).
  if (!S3_DMS_NeedsFilter()) return ""; 

  // Let op: als je dingen toevoegt aan de $expression, voeg hetzelfde criterium dan ook toe aan S3_DMS_NeedsFilter
   $expression = '

        global $myconfig;
        $table = "ims_".$sgn."_objects";
        $ok = true; 
        $object_id = $key;
        $supergroupname = $sgn;

        global $searchmode;
        if ($searchmode=="advanced") {
          $object = MB_Ref($table, $key);

          //ericd 250509 $qr5, $qr6 en $c5, $c6 toegevoegd
          //ericd 250609 nog een extra nodig, gelijk maar met 3 uitgebreid
          global $wstatus, $date, $theindex, $qr, $qr1, $qr2, $qr3, $qr4, $qr5, $qr6, $qr7, $qr8, $qr9;
          global $c1, $c2, $c3, $c4, $c5, $c6, $c7, $c8, $c9, $fileformat, $qrn, $fromdate, $todate;

          //global $wstatus, $date, $theindex, $qr, $qr1, $qr2, $qr3, $qr4;
          //global $c1, $c2, $c3, $c4, $fileformat, $qrn;

          // fileformat
          // We always look at the preview version of the filename / doctype, even when searching in the published index.
          // Why? Because it is the preview version (with version number, icon, etc) that will be presented in the search results.
          $doc = FILES_TrueFileName ($supergroupname, $object_id, "preview");
          $thedoctype = FILES_FileType ($supergroupname, $object_id, "preview");
          if ($fileformat && $fileformat!=$thedoctype) $ok = false;

          // workflow
          if ($wstatus && $wstatus!=$object["workflow"]) $ok = false;

          // date
          if ($date) {
            $max = 0;
            if (is_array($object["history"])) {
              foreach ($object["history"] as $guid => $spec) {
                if ($spec["when"] > $max) $max = $spec["when"];
              }
            }
            if ($max < (time()-(($date+1)*24*3600))) $ok = false;
          }

          if ($fromdate) {
            uuse("jscal");
            $max = 0;
            if (is_array($object["history"])) {
              foreach ($object["history"] as $guid => $spec) {
                if ($spec["when"] > $max) $max = $spec["when"];
              }
            }
            $fromtime = JSCAL_Decode($fromdate);
            if ($max < $fromtime) $ok = false;
          }

          if ($todate) {
            uuse("jscal");
            $max = 0;
            reset($object["history"]);
            if (is_array($object["history"])) {
              foreach ($object["history"] as $guid => $spec) {
                if ($spec["when"] > $max) $max = $spec["when"];
              }
            }
            $totime = JSCAL_Decode($todate) + 24 * 3600;
            if ($max >= $totime) $ok = false;
          }

          // search specs
          //if ($ok) for ($i=1; $i<=4; $i++) {

          //ericd 250509 was 4 nu 6, 2 x q erbij in globals
          //ericd 250609 6 was niet genoeg, naar 9 opgehoogd
          // search specs
          if ($ok) for ($i=1; $i<=9; $i++) {

            $qr = eval("return \$qr$i;");
  
            $c = eval("return \$c$i;"); 
            if (trim($qr) && $c) {
              if ($c=="content") {
// speed optimalization // if (!SEARCH_MatchContent ($qr, $theindex, $object_id)) $ok = false;
              } else {
                $multifound = false;
                for ($multi=1; $multi<=10; $multi++) {
                  if ($multi==1) {
                    if (isset($object[$c]) && SEARCH_MatchString ($qr, $object[$c])) $multifound = true; 
                    if (FORMS_ShowValue ($object[$c],substr($c, 5),$object,$object) && SEARCH_MatchString ($qr, FORMS_ShowValue ($object[$c],substr($c, 5),$object,$object))) $multifound = true;
                  } else {
                    if (isset($object[$c."__".$multi]) && SEARCH_MatchString ($qr, $object[$c."__".$multi])) $multifound = true;
                    if (isset($object[$c."__".$multi]) && SEARCH_MatchString ($qr, FORMS_ShowValue ($object[$c."__".$multi],substr($c, 5)."__".$multi,$object,$object))) $multifound = true;
                  }
                } 
                if (!$multifound) $ok = false;
              }

            }
          }

          // exclude specs
          if ($ok) if ($qrn) {
            if (SEARCH_MatchAnyContent ($qrn, $theindex, $object_id)) $ok = false;
            if (SEARCH_MatchAnyMeta ($qrn, $theindex, $object_id)) $ok = false;
          }
        }

        // check right
        if ($ok) {

           if ($myconfig[$supergroupname]["S3"]["checkrights"] == "yes") {

              $ok = false;
              if (SHIELD_HasObjectRight ($supergroupname, $object_id, "view", false)) { // no autologon please
                 $ok = true;
              } else if (SHIELD_HasObjectRight ($supergroupname, $object_id, "viewpub", false) && $object["published"]=="yes") { 
                 $ok = true;
              } else if (SHIELD_HasObjectRight ($supergroupname, $object_id, "viewsearchresult", false)) {
                 $ok = true;
              }
           }
        }


   ';
   return $expression;
}

//---------------------------------------------------------------------
function S3_DMS_NeedsSort($i_will_also_call_extraenginespecs = false) {
  global $myconfig, $sortby;

  if ($sortby) {
    if ($sortby == "rawrel" || $sortby == "segments") return false;
    if ($i_will_also_call_extraenginespecs && $myconfig["ftengine"]=="S2_SPHINX2" && ($sortby == "date" || $sortby == "datedown")) {
      uuse("sphinx2");
      $hascorrectdates  = SPHINX2_HasCorrectDates();
      N_Debug("S3_DMS_NeedsSort: using S2_SPHINX2, sortby = $sortby, SPHINX2_HasCorrectDates = " . intval($hascorrectdates) . ", OpenIMS sorting needed = " . intval(!$hascorrectdates));
      if ($hascorrectdates) {
        return false; // Sphinx2 can handle date sorting, but only if we gave it the correct dates...
      }
    }
    return true;
  } else {
    return false;
  }
}

function S3_DMS_SortExpression($sortby, $i_will_also_call_extraenginespecs = false) {
  if (!S3_DMS_NeedsSort($i_will_also_call_extraenginespecs)) return ""; 

  if ($sortby) {

     if ($sortby=="name") {

        $sortbyexpression = '
           $object = MB_Ref("ims_".$sgn."_objects", $key);
           $sortvalue = strtolower($object["shorttitle"]);  
        ';       
     
     } else if ($sortby=="datedown") {
     
        $sortbyexpression = '
           $object = MB_Ref("ims_".$sgn."_objects", $key);
           $time = 0;
           if (is_array($object["history"])) {
              reset ($object["history"]);
              while (list($k, $data)=each($object["history"])) {
                $time = $data["when"];
              }
              $sortvalue = -$time; 
           } else {
              $sortvalue = 0; 
           }
        ';

      } else if ($sortby=="date") {

        $sortbyexpression = '
           $object = MB_Ref("ims_".$sgn."_objects", $key);
           $time = 0;
           if (is_array($object["history"])) {
              reset ($object["history"]);
              while (list($k, $data)=each($object["history"])) {
                $time = $data["when"];
              }
              $sortvalue = $time; 
           } else {
              $sortvalue = 0; 
           }
        ';
     }
     //ericd 270509
     else if ($sortby=="stage") {
	 
        $sortbyexpression = '
                $object = MB_Ref("ims_".$sgn."_objects", $key);
		$stage = $object["stage"];
		$id = $object["workflow"];
		$sgn = IMS_SuperGroupName();
		$table = "shield_".$sgn."_workflows";
		$workflow = MB_Ref($table, $id);
		$wfstagename = $workflow[$stage]["name"];
		$sortvalue = str_replace(" ", "",strtolower($wfstagename));  
        '; 
     }
  }
  return $sortbyexpression;
}

//---------------------------------------------------------------------
function S3_DMS_ExtraEngineSpecs() {
  global $myconfig, $sortby;
  /* Mapping between DMS sort options and Sphinx2 sort options:
   *
   * DMS            SPHINX2
   * "" (default)   "reldate"       // relevance, date
   * "segments"     "segments"      // date, relevance
   * "rawrel"       "" (default)
   * "date"         "date"
   * "datedown"     "datedown"
   * "name"         "" (not supported, so use default)
   */
  if ($sortby == "date" || $sortby == "datedown" || $sortby == "segments") {
    $result["S2_SPHINX2"]["sortby"] = $sortby;
  }
  if (!$sortby) {
    $result["S2_SPHINX2"]["sortby"] = "reldate"; // "reldate" is the (new) default for all DMS searching using Sphinx.
  }
  return $result;
}


//---------------------------------------------------------------------
function S3_DMS_MultiLoadExpression ($i_will_also_call_extraenginespecs = false) {
  // Met name het dossierzoeken zorgt ervoor dat we nogal vaak in advanced search zonder dat een multiload noodzakelijk is
  global $sortby;
  if (!S3_DMS_NeedsFilter() && !S3_DMS_NeedsSort($i_will_also_call_extraenginespecs)) return "";

  $mle .= '  $table = "ims_".$sgn."_objects";';
  $mle .= '  MB_MultiLoad($table, $smartresult["result"]);';
  return $mle;
}



?>