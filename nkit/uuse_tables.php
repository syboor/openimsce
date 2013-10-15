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



uuse ("dhtml");
   
function TABLE_Mode ($mode, $specs)
{
  uuse ("skins");
  if (!$specs["fontsize"]) $specs["fontsize"]="13";
  if (!$specs["td-head-align"]) $specs["td-head-align"]="left";
  if (!$specs["td-std-align"])  $specs["td-std-align"]="left";
  if ($mode=="ims") {
    $tableparams = SKIN_ContentTable ($specs);
  } else if ($mode=="dynamic") {
    //ericd 140509 extra mode, waarbij alle tableparams/specs vrij invulbaar zijn.
    $tableparams["table-props"]   = $specs["extra-table-props"]."";
    $tableparams["td-head-props"] = $specs["extra-td-head-props"]."";
    $tableparams["td-std-props"]  = $specs["extra-td-std-props"]."";
    $tableparams["td-head-init"]  = $specs["extra-td-head-init"]."";
    $tableparams["td-head-exit"]  = $specs["extra-td-head-exit"]."";
    $tableparams["td-std-init"]   = $specs["extra-td-std-init"]."";
    $tableparams["td-std-exit"]   = $specs["extra-td-std-exit"]."";
    $tableparams["td-sortlink-style"] = $specs["extra-td-sortlink-style"]."";
  } else if ($mode=="rtf") { 
    $tableparams["table-props"]   = $specs["extra-table-props"]."";
    $tableparams["td-head-props"] = "";
    $tableparams["td-std-props"]  = "";
    $tableparams["td-head-init"]  = " <b>";
    $tableparams["td-head-exit"]  = "</b>";
    $tableparams["td-std-init"]   = " ";
    $tableparams["td-std-exit"]   = "";
    $tableparams["td-sortlink-style"] = " ";
  } else if ($mode=="white") {   
    $tableparams["table-props"]   = $specs["extra-table-props"].' cellpadding="3" cellspacing="0" STYLE="border: solid #FFFFFF 1px; border-collapse:collapse;"';   
    $tableparams["td-head-props"] = 'align="'.$specs["td-head-align"].'" STYLE="border: solid #FFFFFF 1px;"';   
    $tableparams["td-std-props"]   = 'align="'.$specs["td-std-align"].'" STYLE="border: solid #FFFFFF 1px;"';   
    $tableparams["td-head-init"]   = '<font style="color:white; font-family: Arial, Helvetica, sans-serif; font-size: '.$specs["fontsize"].'px;"><b>';   
    $tableparams["td-head-exit"]   = '</b></font>';   
    $tableparams["td-std-init"]   = '<font style="color:white; font-family: Arial, Helvetica, sans-serif; font-size: '.$specs["fontsize"].'px;">';   
    $tableparams["td-std-exit"]   = '</font>';
  } else if ($mode=="black") { 
    $tableparams["table-props"]   = $specs["extra-table-props"].' cellpadding="3" cellspacing="0" STYLE="border: solid #000000 1px; border-collapse:collapse;"';
    $tableparams["td-head-props"] = 'align="'.$specs["td-head-align"].'" STYLE="border: solid #000000 1px;"';
    $tableparams["td-std-props"]  = 'align="'.$specs["td-std-align"].'" STYLE="border: solid #000000 1px;"';
    $tableparams["td-head-init"]  = '<font style="font-family: Arial, Helvetica, sans-serif; font-size: '.$specs["fontsize"].'px;"><b>';
    $tableparams["td-head-exit"]  = '</b></font>';
    $tableparams["td-std-init"]   = '<font style="font-family: Arial, Helvetica, sans-serif; font-size: '.$specs["fontsize"].'px;">';
    $tableparams["td-std-exit"]   = '</font>';
  } else if ($mode=="portal_nav") {
    $tableparams = SKIN_PorletTableNav ($specs);
  } else if ($mode=="portal_docnav") {
    $tableparams = SKIN_PorletTableDocnav ($specs);
// ========= #TREEVIEW: Added to properly display the startblock of portal_pageedit and portal_treeview (WRITTEN BY MICHIEL, MICHT BE UNECECARY ===========
    } else if ($mode=="portal_pageedit") {
    $tableparams = SKIN_PorletTableDocnav ($specs);
  } else if ($mode=="portal_treeview") { 
	$tableparams = SKIN_PorletTableDocnav ($specs);
// ========= END #TREEVIEW: ===========
  } else if ($mode=="portal") {
    $tableparams = SKIN_PorletTableDocnav ($specs);
  } else if ($mode=="portal_action") {
    $tableparams = SKIN_PorletTableAction ($specs);
  } else { // default

    $tableparams["table-props"]   = $specs["extra-table-props"].' cellpadding="3" cellspacing="0"';
    $tableparams["td-head-props"] = 'align="'.$specs["td-head-align"].'"';
    $tableparams["td-std-props"]  = 'align="'.$specs["td-head-align"].'"';
    $tableparams["td-head-init"]  = '<font style="font-family: Arial, Helvetica, sans-serif; font-size: '.$specs["fontsize"].'px;"><b>';
    $tableparams["td-head-exit"]  = '</b></font>';
    $tableparams["td-std-init"]   = '<font style="font-family: Arial, Helvetica, sans-serif; font-size: '.$specs["fontsize"].'px;">';
    $tableparams["td-std-exit"]   = '</font>';
  }
  if (!$tableparams["td-sortlink-style"]) {
    $tableparams["td-sortlink-style"] = '
      <STYLE type="text/css"><!--
        A.###:link{color: #000000; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: '.$specs["fontsize"].'px;}
        A.###:visited{color: #000000; text-decoration: none; font-family: Arial, Helvetica, sans-serif; font-size: '.$specs["fontsize"].'px;}
        A.###:hover{color: #FF0000; text-decoration: underline; font-family: Arial, Helvetica, sans-serif; font-size: '.$specs["fontsize"].'px;}
      --></STYLE>
    ';
  }
  $tableparams["extrahead"] = $specs["extrahead"];

  return $tableparams;
}

function TH_Double($e1, $e2, $align1="left", $align2="right")
{
  global $tablestore;
  $tableparams = TABLE_Mode ($tablestore["mode"], $tablestore["specs"]);
  $ret = '<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="'.$align1.'">';
  $ret .= $tableparams["td-head-init"];
  $ret .= $e1;
  $ret .= $tableparams["td-head-exit"];
  $ret .= '</td><td align="'.$align2.'">';
  $ret .= $tableparams["td-head-init"];
  $ret .= $e2;
  $ret .= $tableparams["td-head-exit"];
  $ret .= "</td></tr></table>";
  return $ret;
}

function TH_Tripple ($e1, $e2, $e3, $align1="center", $align2="center", $align3="center")
{
  global $tablestore;
  $tableparams = TABLE_Mode ($tablestore["mode"], $tablestore["specs"]);
  $ret = '<table width="100%" cellpadding="0" cellspacing="0"><tr><td align="'.$align1.'">';
  $ret .= $tableparams["td-head-init"];
  $ret .= $e1;
  $ret .= $tableparams["td-head-exit"];
  $ret .= '</td><td align="'.$align2.'">';
  $ret .= $tableparams["td-head-init"];
  $ret .= $e2;
  $ret .= $tableparams["td-head-exit"];
  $ret .= '</td><td align="'.$align3.'">';
  $ret .= $tableparams["td-head-init"];
  $ret .= $e3;
  $ret .= $tableparams["td-head-exit"];
  $ret .= "</td></tr></table>";
  return $ret;
}

function T_Value($mode, $var)
{
  global $tablestore;
  $tableparams = TABLE_Mode ($mode, array());
  $ret = $tableparams[$var];
  return $ret;
}

function T_Start($mode="", $specs=array())
{
  // Dont use $specs="", causes lots of warnings in PHP5.4 about 'Illegal string offset'.
  // This warning is important and should not be suppressed, because if you go one level "deeper" you get a fatal runtime error.

  global $tablestore, $tablestack, $recordedtimestamp;
  $recordedtimestamp = 0;
  if (!is_array($tablestack)) $tablestack=array();
  array_push ($tablestack, $tablestore);
  $tablestore = array();
  $tablestore["specs"] = $specs;
  $tablestore["mode"] = $mode;
  $tablestore["nextrow"] = 1;
  $tablestore["nextcol"] = 1;
  ob_start();
}

function T_Next($specs=array())
{
  global $tablestore, $recordedtimestamp;
  $content = ob_get_contents();
  ob_end_clean();
  ob_start();
  $tablestore[$tablestore["nextrow"]][$tablestore["nextcol"]] = $content;
  $tablestore["specs"][$tablestore["nextrow"]][$tablestore["nextcol"]] = $specs;
  $tablestore["time"][$tablestore["nextrow"]][$tablestore["nextcol"]] = $recordedtimestamp;
  $recordedtimestamp = 0;
  if ($tablestore["nextcol"] > $tablestore["maxcol"]) $tablestore["maxcol"] = $tablestore["nextcol"];
  if ($tablestore["nextrow"] > $tablestore["maxrow"]) $tablestore["maxrow"] = $tablestore["nextrow"];
  $tablestore["nextcol"]++;
}

function T_Newrow($specs=array())
{
  global $tablestore, $recordedtimestamp;
  $content = ob_get_contents();
  ob_end_clean();
  ob_start();
  if ($content) {
    $tablestore[$tablestore["nextrow"]][$tablestore["nextcol"]] = $content;
    $tablestore["specs"][$tablestore["nextrow"]][$tablestore["nextcol"]] = $specs;
    $tablestore["time"][$tablestore["nextrow"]][$tablestore["nextcol"]] = $recordedtimestamp;
    if ($tablestore["nextcol"] > $tablestore["maxcol"]) $tablestore["maxcol"] = $tablestore["nextcol"];
    if ($tablestore["nextrow"] > $tablestore["maxrow"]) $tablestore["maxrow"] = $tablestore["nextrow"];
  }
  $tablestore["nextcol"]=1;
  $tablestore["nextrow"]++;
}



function TS_GenerateTable ($class, $tablestore) 
{
  $no_reload = $tablestore["specs"]["no_reload"];
  $maxrows = $tablestore["specs"]["maxrows"];
  if ($maxrows) {
    $showall = $tablestore["dynamic"]["showall"];
  }
  $cssclass = $class;
  if ($tablestore["specs"]["sortlinkcss"]) {
    $cssclass = $tablestore["specs"]["sortlinkcss"];
  }
  $tableparams = TABLE_Mode ($tablestore["mode"], $tablestore["specs"]);

  $ret .= "<table ".$tableparams["table-props"].">";
  if ($tableparams["extrahead"]) {
    $tdprops = $tableparams["td-std-props"];
    $init = $tableparams["td-std-init"];
    $exit = $tableparams["td-std-exit"];
    $ret.= "<td colspan=\"".$tablestore["maxcol"]."\" $tdprops>";
    if ($tablestore["specs"]["nobr"]) $ret.= "<nobr>";
    $ret .= $init;
    $ret .= $tableparams["extrahead"];
    $ret .= $exit;
    if ($tablestore["specs"]["nobr"]) $ret.= "</nobr>";
    $ret .= "</td>";
  }

  if ($tablestore["specs"]["sort"]) {

    if ($no_reload=="yes") {
      $sortcol = $tablestore["dynamic"]["sortcol"];
      $sortdir = $tablestore["dynamic"]["sortdir"];
    } else {
      // scan URL for sort command
      eval ("global \$tblsrt_".$tablestore["specs"]["sort"].";");
      eval ("\$sortcol = \$tblsrt_".$tablestore["specs"]["sort"].";");
      eval ("global \$tbldir_".$tablestore["specs"]["sort"].";");
      eval ("\$sortdir = \$tbldir_".$tablestore["specs"]["sort"].";");
    }

    if ($sortcol) {
//      setcookie ("cookie_tblsrt_".$tablestore["specs"]["sort"], $sortcol, time()+365*24*3600);
//      setcookie ("cookie_tbldir_".$tablestore["specs"]["sort"], $sortdir, time()+365*24*3600);
    } else {
      // scan cookies for sort command
//      eval ("global \$cookie_tblsrt_".$tablestore["specs"]["sort"].";");
//      eval ("\$sortcol = \$cookie_tblsrt_".$tablestore["specs"]["sort"].";");
//      eval ("global \$cookie_tbldir_".$tablestore["specs"]["sort"].";");
//      eval ("\$sortdir = \$cookie_tbldir_".$tablestore["specs"]["sort"].";");
      if (!$sortcol) {
        // use default sort order
        $sortcol = $tablestore["specs"]["sort_default_col"];
        $sortdir = $tablestore["specs"]["sort_default_dir"];
      }
    }
    if (!$sortdir) $sortdir="u";

    if ($tablestore["specs"]["sort_map_$sortcol"]) {
      $truesortcol = $tablestore["specs"]["sort_map_$sortcol"];
    } else {
      $truesortcol = $sortcol;
    }

    /* LF20090714: sort_topsticky = N will force the top N rows (after the header) to always remain at the top, unaffected by sorting. */
    global $myconfig;
    if (($tablestore["maxrow"] - ($tablestore["specs"]["sort_bottomskip"] + $tablestore["specs"]["sort_topsticky"])) > 1) {
      $tmptab = array();
      $fullcopy = $tablestore;
      for ($row=2+$tablestore["specs"]["sort_topsticky"]; $row<=($tablestore["maxrow"]-$tablestore["specs"]["sort_bottomskip"]); $row++) {      
        $sortvalue = $tablestore[$row][$truesortcol];
        if (($myconfig[IMS_SuperGroupName()]["ml"]["metadatalevel"] && $myconfig[IMS_SuperGroupName()]["ml"]["metadatafiltering"] != "no") || 
             $myconfig[IMS_SuperGroupName()]["ml"]["metadatafiltering"] == "yes") {
          $sortvalue = FORMS_ML_Filter($sortvalue);
        }
        if ($tablestore["specs"]["sort_$sortcol"]=="date") {
          $tmptab[$row] = strtolower(trim(SEARCH_HTML2TEXT($tablestore["time"][$row][$truesortcol])));
        } else if ($tablestore["specs"]["sort_$sortcol"]=="smartmix") {
          // LF20090219: for strings like STD1, STD9, STD10. This is not automatically detected, you have
          // to define it as "smartmix" explicitly.
          $tmptab[$row] = QRY_SmartSort_v1(strtolower(trim(SEARCH_HTML2TEXT($sortvalue))));
        } else if ($tablestore["specs"]["sort_$sortcol"]=="value") {
          $tmptab[$row] = strtolower(trim(SEARCH_HTML2TEXT($sortvalue)));
        } else { // autodetect
          if ($tablestore["time"][$row][$truesortcol]) {
            $tmptab[$row] = strtolower(trim(SEARCH_HTML2TEXT($tablestore["time"][$row][$truesortcol])));
          } else {
            $tmptab[$row] = strtolower(trim(SEARCH_HTML2TEXT($sortvalue)));
          }
        }
      }
      if (!$tablestore["specs"]["alreadysorted"]) {
        if ($sortdir=="d") {
          arsort ($tmptab);
        } else {
          asort ($tmptab);
        }
      }
      $ctr = 1;
      if ($tablestore["specs"]["sort_topsticky"]) for ($index = 2; $index < 2 + $tablestore["specs"]["sort_topsticky"]; $index++) {
        $ctr++;
        $tablestore[$ctr] = $fullcopy[$index];
        $tablestore["specs"][$ctr] = $fullcopy["specs"][$index];
        $tablestore["time"][$ctr] = $fullcopy["time"][$index];
      }
      foreach ($tmptab as $index => $dummy) {
        $ctr++;
        $tablestore[$ctr] = $fullcopy[$index];
        $tablestore["specs"][$ctr] = $fullcopy["specs"][$index];
        $tablestore["time"][$ctr] = $fullcopy["time"][$index];
      }

    }
  } 

  if ($maxrows) {
    if ($maxrows >= $tablestore["maxrow"]) $maxrows = 0;
    if ($maxrows) {
      $skiprows = $tablestore["maxrow"] - $maxrows;
    }
  }

  for ($row=1; $row<=$tablestore["maxrow"]; $row++) {
    $skip = false;
    if ($maxrows) {
      // sort_topsticky only affects *sorting*, unlike bottomskip, we do not actually *skip* rows if $maxrows is set.
      if ($tablestore["specs"]["sort_bottomskip"]) {
        if ($maxrows && !$showall && $row > ($maxrows - $tablestore["specs"]["sort_bottomskip"])) $skip = true; 
        if ($row > $tablestore["maxrow"] - $tablestore["specs"]["sort_bottomskip"]) $skip = false;
      } else {
        if ($maxrows && !$showall && $row > $maxrows) $skip = true; 
      }
    }
    if (!$skip) {
      $ret.="<tr>";

      // ============== added by johnny december 2009 ==============
      $groupcol = @$tablestore["specs"]["groupcol"];
      if ( $groupcol && $tablestore[$row][ $groupcol ] != $tablestore[$row-1][ $groupcol ] )
      {
        $ret .= "<td syle='border-bottom:2px;' colspan='" . ($tablestore["maxcol"]-1) . "'><b>";
        $content =  $tablestore[$row][ $groupcol ];

        if ( $row == 1 )
        { 
          $url = N_MyVeryFullURL();
          $url = N_AlterURL ($url, "tblsrt_".$tablestore["specs"]["sort"], $groupcol );

          if ( $groupcol == $sortcol )
            $url = N_AlterURL ($url, "tbldir_".$tablestore["specs"]["sort"], ( $sortdir=="d" ? "u" : "d" ) );
          else
            $url = N_AlterURL ($url, "tbldir_".$tablestore["specs"]["sort"], "u");

          $ret .= "<nobr><a title=\"".ML("Sorteren", "Sort")."\" class=\"$cssclass\" href=\"$url\">".$content;

          if ( $groupcol == $sortcol)
          {
            if ($sortdir=="u") {
              $ret .= " <img border=0 src=\"/openims/sortup.gif\">";
            } else {
              $ret .= " <img border=0 src=\"/openims/sortdown.gif\">";
            }
          }
          $ret .= "</a></nobr>";
        } else 
          $ret .= ($content=="" ? ML("-Leeg-", "-Empty-" ) : $content);
        $ret .= "</b></td></tr><tr>";
      }
      // ============ end added by johnny december 2009 ============

//    for ($col=1; $col<=$tablestore["maxcol"]; $col++) { // original johnny december 2009
      for ($col=1; $col<=$tablestore["maxcol"]; $col++) if ( !$groupcol || $col != $groupcol ) { // if added by johnny december 2009
        $span=1;
        $content = $tablestore[$row][$col];
        $specs = $tablestore["specs"][$row][$col];
//      while ($col<$tablestore["maxcol"] && !$tablestore[$row][$col+1]) { // original johnny december 2009
//        while ($col<$tablestore["maxcol"] && !$tablestore[$row][$col+1] && (!$groupcol || $col+1 != $groupcol ) ) { // && groupcol added by johnny december 2009
//          $span++;
//          $col++;
//        }
        while ($col<$tablestore["maxcol"] && (!$tablestore[$row][$col+1] || !(!$groupcol || $col+1 != $groupcol) )) { 
// ($groupcol && $col+1 == $groupcol)
          if (!$groupcol || $col+1 != $groupcol) $span++;
          $col++;
        }
        if ($row==1 && !$tablestore["specs"]["noheader"]) {
          $tdprops = $tableparams["td-head-props"];
          $init = $tableparams["td-head-init"];
          $exit = $tableparams["td-head-exit"];
        } else {
          $tdprops = $tableparams["td-std-props"];
          $init = $tableparams["td-std-init"];
          $exit = $tableparams["td-std-exit"];
        }
        if ($specs["td-align"]) {
          $tdprops = str_replace ('align="right"', 'align="'.$specs["td-align"].'"', $tdprops);
          $tdprops = str_replace ('align="left"', 'align="'.$specs["td-align"].'"', $tdprops);
          $tdprops = str_replace ('align="center"', 'align="'.$specs["td-align"].'"', $tdprops);
        }
        if ($specs["td-width"]) $tdprops .= " width=\"".$specs["td-width"]."\"";
        if ($specs["td-any"]) $tdprops .= " ".$specs["td-any"];
        if ($span>1) {
          $ret.= "<td colspan=\"$span\" $tdprops>";
        } else {
          $ret.= "<td $tdprops>";
        }
        if (($tablestore["specs"]["nobr"]) || ($specs["nobr"])) $ret.= "<nobr>";
        $ret .= $specs["td-init"] . $init;
//      if ($tablestore["specs"]["sort"] && $row==1 && $tablestore["specs"]["sort_".($col-$span+1)]) { // // original johnny zet sortering uit december 2009
        if ($tablestore["specs"]["sort"] && $row==1 && $tablestore["specs"]["sort_".($col-$span+1)] && !$groupcol) { // && groupcol added by johnny zet sortering uit december 2009
          $newsortcol = $col-$span+1;
          if ($col-$span+1 == $sortcol) {
            if ($sortdir=="d") {
              $newsortdir = "u";
            } else {
              $newsortdir = "d";
            }
          } else {
            $newsortdir = "u";
          }
          if ($no_reload=="yes") {
            $input["class"] = $class;
            $input["sortcol"] = $newsortcol;
            $input["sortdir"] = $newsortdir;
            $code = '
              $tablestore = TMP_LoadObject ($input["class"]);
              $tablestore["dynamic"]["sortcol"] = $input["sortcol"];
              $tablestore["dynamic"]["sortdir"] = $input["sortdir"];
              TMP_SaveObject ($input["class"], $tablestore);
              echo DHTML_EmbedJavaScript (DHTML_Master_SetDynamicObject ($input["class"], TS_GenerateTable ($input["class"], $tablestore)));
            ';
            $url = DHTML_RPCURL ($code, $input); 
          } else {      
            $url = N_MyVeryFullURL();
            $url = N_AlterURL ($url, "tblsrt_".$tablestore["specs"]["sort"], $col-$span+1);
            if ($col-$span+1 == $sortcol) {
              if ($sortdir=="d") {
                $url = N_AlterURL ($url, "tbldir_".$tablestore["specs"]["sort"], "u");
              } else {
                $url = N_AlterURL ($url, "tbldir_".$tablestore["specs"]["sort"], "d");
              }
            } else {
              $url = N_AlterURL ($url, "tbldir_".$tablestore["specs"]["sort"], "u");
            }
          }
          $ret .= "<nobr><a title=\"".ML("Sorteren", "Sort")."\" class=\"$cssclass\" href=\"$url\">".$content;
          if ($col-$span+1 == $sortcol) {
            if ($sortdir=="u") {
              $ret .= " <img border=0 src=\"/openims/sortup.gif\">";
            } else {
              $ret .= " <img border=0 src=\"/openims/sortdown.gif\">";
            }
          }
          $ret .= "</a> </nobr>";
        } else {
          $ret .= $content; 
        }
        if (!$tablestore[$row][$col]) $ret.=" ";
        $ret .= $exit . $specs["td-exit"];
        if (($tablestore["specs"]["nobr"]) || ($specs["nobr"])) $ret.= "</nobr>";
        $ret .= "</td>";
      }
      $ret.="</tr>";
    }
  }
  if ($maxrows) { 
    $ret.="<tr>";
    $tdprops = $tableparams["td-std-props"];
    $init = $tableparams["td-std-init"];
    $exit = $tableparams["td-std-exit"];
    $ret .= "<td colspan=\"".$tablestore["maxcol"]."\" align=\"left\" $tdprops align=\"left\">";
    $ret .= $init;
    if ($showall) {
      $input["class"] = $class;
      $input["sortcol"] = $newsortcol;
      $input["sortdir"] = $newsortdir;
      $code = '
        $tablestore = TMP_LoadObject ($input["class"]);
        $tablestore["dynamic"]["showall"] = false;
        TMP_SaveObject ($input["class"], $tablestore);
        echo DHTML_EmbedJavaScript (DHTML_Master_SetDynamicObject ($input["class"], TS_GenerateTable ($input["class"], $tablestore)));
      ';
      $url = DHTML_RPCURL ($code, $input); 
      $ret .= "<b><a class=\"$cssclass\" href=\"$url\">&lt;&lt;&lt; ".ML("Toon gedeelte","Show part")."</a></b> (-$skiprows)";
    } else {
      $input["class"] = $class;
      $input["sortcol"] = $newsortcol;
      $input["sortdir"] = $newsortdir;
      $code = '
        $tablestore = TMP_LoadObject ($input["class"]);
        $tablestore["dynamic"]["showall"] = true;
        TMP_SaveObject ($input["class"], $tablestore);
        echo DHTML_EmbedJavaScript (DHTML_Master_SetDynamicObject ($input["class"], TS_GenerateTable ($input["class"], $tablestore)));
      ';
      $url = DHTML_RPCURL ($code, $input); 
      $ret .= "<b><a class=\"$cssclass\" href=\"$url\">&gt;&gt;&gt; ".ML("Toon alles","Show everything")."</a></b> (+$skiprows)";
    }
    $ret .= $exit;
    $ret.="</tr>";
  }
  $ret.="</table>";
  if ($tablestore["specs"]["extratop"] || $tablestore["specs"]["extrabottom"] || $tablestore["specs"]["extraleft"] || $tablestore["specs"]["extraright"]) {
    $wrap = '<table cellspacing=0 cellpadding=0><tr><td style="';
    if ($tablestore["specs"]["extratop"]) $wrap.= 'border-top:'.$tablestore["specs"]["extratop"].'px solid;';
    if ($tablestore["specs"]["extrabottom"]) $wrap.= 'border-bottom:'.$tablestore["specs"]["extrabottom"].'px solid ;';
    if ($tablestore["specs"]["extraleft"]) $wrap.= 'border-left:'.$tablestore["specs"]["extraleft"].'px solid;';
    if ($tablestore["specs"]["extraright"]) $wrap.= 'border-right:'.$tablestore["specs"]["extraright"].'px solid ;';
    $ret = $wrap.'">'.$ret.'</td></tr></table>';
  }

  return $ret;
}

function TS_End($specs=array())
{
  global $tablestore, $tablestack;
  $content = ob_get_contents();
  ob_end_clean();
  if ($content) {
    $tablestore[$tablestore["nextrow"]][$tablestore["nextcol"]] = $content;
    $tablestore["specs"][$tablestore["nextrow"]][$tablestore["nextcol"]] = $specs;
    if ($tablestore["nextcol"] > $tablestore["maxcol"]) $tablestore["maxcol"] = $tablestore["nextcol"];
    if ($tablestore["nextrow"] > $tablestore["maxrow"]) $tablestore["maxrow"] = $tablestore["nextrow"];
  }

  $class = "q".N_GUID()."q";
  $no_reload = $tablestore["specs"]["no_reload"];
  $tableparams = TABLE_Mode ($tablestore["mode"], $tablestore["specs"]);
  $ret = '<span style="font-size: 1px; display: none;"><br></span>' . str_replace ("###", $class, $tableparams["td-sortlink-style"]); // If a <style> declaration is placed using innerHtml (inplace autotables), IE will ignore the <style> if there is no <br> in front of it. http://www.webdeveloper.com/forum/showthread.php?t=136331
  if ($no_reload == "yes") {
    TMP_SaveObject ($class, $tablestore);
    $ret .= DHTML_DynamicObject (TS_GenerateTable ($class, $tablestore), $class).DHTML_PrepRPC();
  } else {
    $ret .= TS_GenerateTable ($class, $tablestore);
  }

  $tablestore = array_pop ($tablestack);

  return $ret;
}   

function TE_End($specs=array()) { echo TS_End($specs); }

/* Some notes about the column filters

- Activate column filtering for a table by using $specs["colfilter"] = "yes"
  Or activate them globally by using $myconfig[$sgn]["autotables"]["colfilter"] = "yes".
  De-activate on a table (overruling $myconfig-setting) by using $specs["colfilter"] = "no"
- "invisiblemetadatafilter" will not work.
- Filtering is done is two stages:
  1. first we do standard filtering with all filterqueries concatenated. 
     This will give some false positive (a query in column 1 matching content in column 4).
  2. in a slowselect (this is slow!), we do per column filtering for the remaining matches
  The reason for this dual filtering is that, without it, for N columns, you would need 2^N different indexes
- specs["filterexp"] will be ignored
- If $specs["filterexp"] is present but neither $specs["colfilterexp"] nor $specs["colfiltertype"] is present, 
  column filtering disables itself. Reason: when $specs["filterexp"] is used, the content-expressions may be unsuitable for indexing.
  DO NOT DELETE $specs["filterexp"] to solve this, instead look carefully at everything that $specs["filterexp"] does and:
  - for columns that are omitted by $specs["filterexp"]: use $specs["colfiltertype"][$col] = "none"
  - for columns that are different from their content-expression: use $specs["colfilterexp"][$col] = $exp
  Make sure you are thorough and complete, because an "incomplete" solution will be enough to
  re-enable column filtering, but it may create broken indexes.
- The global filterexpression will be build by concatenating all columns:
  - columns without a filter field above them are skipped ($specs["colfiltertype"][$col] == "none" or $specs["colfiltertype"][$col] == "submit")
  - columns that are sort mapped to another column, will be skipped (usually, these columns have a filter field above them, but the field spans multiple columns)
  - if a column has a $specs["colfilterexp"][$index], this expression will be used
  - otherwise the filterexpression for the column will be based on the content, the same way as when filtering without column filters
- $specs["colfilterexp"][$index] can be used to specify the filter expression for a column.
  This expression must be deterministic.
  $index should be same as in $specs["sort"][$index] and $specs["content"][$index] (the first index appears to be 0, at least in the DMS)
- In the url, you have parameters tblcolflt_tablename_col, where col is the column number. 
  just like with sort default columns and sort maps, the first col is 1 (not 0)
- The column filter is shown if there are more records in the table than fit on a single page, 
  or if the appropriate url parameters (tblcolflt_tablename_col=blub) are present
- If you use sort_maps (multiple columns that are treated as one when sorting), the input
  field will appear above the first column in the sortmap, and the other columns will not have 
  an input field. If the columns in the sort map are right next to each other, the input field 
  will be stretched across all of them.
- You can disable filtering on a column $col by specifying $specs["colfiltertype"][$col] = "none";
- By default, there is no submit button, and the form is autosubmitted using the "onchange" event
- You can create a submit button above a column by specifying $specs["colfiltertype"][$col] = "submit".
  There will be no text input field above this column (so use this above a column not worth filtering, or create an extra column).
  However, when the submit button is part of a sort map, the next column in the sort map will not be skipped,
  so by default it will receive a text input field.
- You can turn off autosubmit by using $specs["colfilterautosubmit"] = "no"
- In almost all situations, when you turn column filtering on, new xml indexes will be created the first
  time somebody uses filtering. The only exception would be if the table never had a $specs["filterexp"] and 
  does not use sort maps and does not specify colfiltertypes or colfilterexp's.
- If your table columns contain multilingual data (ML-strings), the filter will search the "raw" value, which means
  that the filtervalue may match will languages that are not currently visible.


Leftcolfilters:
- If you set $specs["leftcolfilter"] = "yes", the filtervalues will match from the left. So a filtervalue of "Mod"
  will match a column value of "Modified", but a filtervalue "od" will no longer match.
- If you enable leftcolfilters, an index will be created for each column.
- If you also have multilingual metadata, an index will be created for each combination of column and language.
- Leftcolfilters will only match with the current (visible) language, not with other languages.
- Leftcolfilters may help performance because MySQL uses an index instead of the full table scan needed for normal filters.
- If the user specifies filtervalues in multiple columns, only the first column will use an index.



*/

function TABLES_Auto ($specs)
{
  uuse ("skins");
  N_Debug ("TABLES_Auto (...)"); 
  global $debug; if ($debug=="yes") N_EO ($specs); 
  global $myconfig;
  if ($specs["inplace"] == "yes" || ($myconfig[IMS_SuperGroupName()]["autotables"]["inplace"] == "yes" && $specs["inplace"] != "no")) {
    $specs["inplace"] = "no";  // to prevent loops
    return TABLES_Auto_NoReload($specs);
  }

  uuse ("forms");
  $input = $specs["input"];
  $specs["tablespecs"]["sort"] = $specs["name"];
  $specs["tablespecs"]["alreadysorted"] = true; // Tell TS_Generate not to sort, but still show the user interface for sorting
  if (!$specs["maxlen"]) $specs["maxlen"] = 25;

  // Find the value of global variables (usually from the url) for this table.
  // These variables have "variable" names (with the tablename in it), so that you can have more than one table on the same page.
  // The (url) parameters are:
  //   tblsrt_tablename=4          -> sort by column 4
  //   tbldir_tablename=u          -> sort up (or down)
  //   tblflt_tablename=blub       -> (global) filter "blub" (show only table rows that have "blub" in them somewhere)
  //   tblcolflt_tablename_3=nieuw -> column filter "nieuw" on column 3 (show only table rows that have "nieuw" somewhere in column 3)

  if ($myconfig[IMS_SuperGroupName()]["autotables"]["colfilter"] == "yes" && $specs["colfilter"] != "no") $specs["colfilter"] = "yes";
  if ($myconfig[IMS_SuperGroupName()]["autotables"]["leftcolfilter"] == "yes" && $specs["leftcolfilter"] != "no") $specs["leftcolfilter"] = "yes";
  if ($specs["leftcolfilter"] == "yes") $specs["colfilter"] == "yes"; // leftcolfilter implies colfilter
  if ($specs["filterexp"] && !(is_array($specs["colfilterexp"]) || is_array($specs["colfiltertype"]))) $specs["colfilter"] = "no"; 
  N_Debug("TABLES_Auto: colfilter = {$specs["colfilter"]}");
    // If a table has a $specs["filterexp"], quite often the table has content-expressions that should
    // not be indexed (calling functions that are not always available etc.)
    // This means that if we automatically create a global filterexpressions based on these 
    // content-expressions, we will create an broken index on the table. For this reason, if
    // a table specifies $specs["filterexp"] but not $specs["colfilterexp"], column filtering is disabled.
    // OTOH, if $specs["filterexp"] is absent, then the global filterexpression will be the
    // same (based on the content-expressions) regardless of whether column filtering is enabled or not.

  eval ("global \$tblsrt_".$specs["name"].";");
  eval ("\$sortcol = \$tblsrt_".$specs["name"].";");
  if (!$sortcol) {
    $sortcol = $specs["tablespecs"]["sort_default_col"];
  }
  if (!$sortcol) $sortcol = 1;
  if ($specs["tablespecs"]["sort_map_$sortcol"]) {
    $truesortcol = $specs["tablespecs"]["sort_map_$sortcol"];
  } else {
    $truesortcol = $sortcol;
  }
  $sortexpr = $specs["sort"][$truesortcol-1];

  if (($myconfig[IMS_SuperGroupName()]["ml"]["metadatalevel"] && $myconfig[IMS_SuperGroupName()]["ml"]["metadatafiltering"] != "no") || 
       $myconfig[IMS_SuperGroupName()]["ml"]["metadatafiltering"] == "yes") {
    if ($sortexpr) $sortexpr = 'FORMS_ML_Filter('.$sortexpr.', "'.ML_GetLanguage().'")';
  }

  if ($specs["tablespecs"]["sort_$sortcol"] == "smartmix") $sortexpr = "QRY_SmartSort_v1($sortexpr)";

  if ($myconfig[IMS_SuperGroupName()]["autotables"]["uniquesort"] != "no" && $sortexpr) $sortexpr = "MB_ComparableUnique($sortexpr, \$key)"; 
  /* Adding uniqueness makes "gotoobject" faster.
   */

  eval ("global \$tbldir_".$specs["name"].";");
  eval ("\$dir = \$tbldir_".$specs["name"].";");
  if (!$dir) $dir = $specs["tablespecs"]["sort_default_dir"];
  if ($dir=="d" && $sortexpr) $sortexpr = "MB_Invert ($sortexpr)";

  N_Debug("TABLES_Auto: sortexpr = ".htmlspecialchars($sortexpr));

  eval ("global \$tblflt_".$specs["name"].";");
  eval ("\$filter = \$tblflt_".$specs["name"].";");
  if ($filter === null) $filter = $specs["filter"];
  // Note: use $specs["nofilter"] to suppress showing the filter form, useful if the autotable is already part of a form.

  $colfilter = array();
  if ($specs["colfilter"] == "yes") {
    // $specs["colfilter"]: if this is "yes", filtering will be advanced (per column)
    // $filter: filterquery by the user. If using colfilter, $filter is the concatenation of all columns filtered
    for ($i = 1; $i <= count($specs["tableheads"]); $i++) {
      eval ("global \$tblcolflt_{$specs["name"]}_$i;");
      eval ("\$colfilter[$i] = \$tblcolflt_{$specs["name"]}_$i;");
      if ($colfilter[$i]) {
        $filter .= " " . $colfilter[$i];
        $colfilter[$i] = stripcslashes($colfilter[$i]);
      }
    }
    $filter = trim($filter);
    N_Debug("TABLES_Auto: colfilter: " . print_r($colfilter, 1));
  }
  $filter = stripcslashes ($filter);

  // Save the original slowselect for calculating the $rawcount (column filters will modify the slowselect)
  $specs["rawslowselect"] = $specs["slowselect"]; 

  // Create the global filterexpression ($filterexp) and the column filterexpression ($colfilterexp[$col])

  if ($specs["colfilter"] == "yes") {
    // Column filters are used: create both column filters and a global filter based on them
    $col = 0;
    $filterexp = '$object = $record; $text="";';
    $tablehead = $nonemptytablehead = "";
    foreach ($specs["content"] as $index => $content) {
      $col++;
      // $specs["content"] and $specs["index"] can start at 0, while $colfilter and the sort map start at 1. So $index does not have to be equal to $col

      $tablehead = $specs["tableheads"][$index]; // $index, not $col !
      if ($tablehead) $nonemptytablehead = $tablehead;
      if (!$nonemptytablehead) continue; // If the first column does not have a table head, skip this column

      // Skip if there is no filter above this column
      if ($specs["colfiltertype"][$col] == "none" || $specs["colfiltertype"][$col] == "submit") continue;
      if ($specs["tablespecs"]["sort_map_$col"] && $specs["tablespecs"]["sort_map_$col"] != $col) continue;

      $thiscolfilterexp = '$object = $record; $text="";';
      if ($specs["colfilterexp"][$index]) {
        $thiscolfilterexp = '$text = trim (SEARCH_RemoveAccents ('.$specs["colfilterexp"][$index].'));';
        $filterexp .= '$text .= trim (SEARCH_RemoveAccents ('.$specs["colfilterexp"][$index].'));';
      } else {
        if ($specs["sort"][$index]) {
          $thiscolfilterexp .= 'eval (\'$sortvalue = '.addcslashes($specs["sort"][$index], "'\\").';\');';
          $filterexp .= 'eval (\'$sortvalue = '.addcslashes($specs["sort"][$index], "'\\").';\');';
        } else {
          $thiscolfilterexp .= '$sortvalue="";';
          $filterexp .= '$sortvalue="";';
        }
        $content = $specs["content"][$index];
        $thiscolfilterexp .= '
          ob_start(); 
          eval (\''.addcslashes($content,"'\\").'\');
          $text = " ".ob_get_contents();
          ob_end_clean();
        '; // Note: $text = ipv $text .=, since this is executed multiple times per row
        $filterexp .= '
          ob_start(); 
          eval (\''.addcslashes($content,"'\\").'\');
          $text .= " ".ob_get_contents();
          ob_end_clean();
        ';
      }
      $thiscolfiltervalue = $colfilter[$col];
      $thiscolfiltervalue = trim (strtolower (SEARCH_RemoveAccents (SEARCH_HTML2TEXT ($thiscolfiltervalue))));
      if ($thiscolfiltervalue) {
        if ($specs["leftcolfilter"] == "yes") {
          // For "normal" filters, we can put ML strings in our index, because the current language is "somewhere in there", so we might have a few false positives but no false negatives.
          // For leftfilter, "somewhere in there" != "left" so we will have to index each language to prevent false negatives.
          if (($myconfig[IMS_SuperGroupName()]["ml"]["metadatalevel"] && $myconfig[IMS_SuperGroupName()]["ml"]["metadatafiltering"] != "no") || 
            $myconfig[IMS_SuperGroupName()]["ml"]["metadatafiltering"] == "yes") {
            $thiscolfilterexp .= '$text = FORMS_ML_Filter($text, "'.ML_GetLanguage().'");';
          }

          // Use only one leftfilter (for the first column that has a filtervalue), use slowselect for the other columns.
          if ($leftfilterused) {
            // LF: MB_Text2Words produces leading space if the value start with a digit; leftfilter is picky about this so we need to trim
            $thiscolfiltervalue = trim(MB_Text2Words($thiscolfiltervalue, true)); // removes quotes and other dangerous stuff
            $thiscolfilterexp .= '$text2 = \'' . addcslashes($thiscolfiltervalue, "'\\") . '\';';
            $thiscolfilterexp .= 'return (substr(trim(MB_Text2Words(strtolower(trim(SEARCH_RemoveAccents(SEARCH_HTML2Text($text)))))), 0, strlen($text2)) == $text2);';
            N_Debug("TABLES_Auto: thiscolfilterexp (left) $col: " . htmlspecialchars($thiscolfilterexp));
            $thiscolfilterexp = "eval ('".addcslashes($thiscolfilterexp, "'\\")."')";
            $specs["slowselect"][$thiscolfilterexp] = true;
          } else {
            $thiscolfilterexp .= 'return trim(MB_Text2Words(strtolower(trim(SEARCH_RemoveAccents(SEARCH_HTML2Text($text))))));';
            N_Debug("TABLES_Auto: thiscolfilterexp (left) $col: " . htmlspecialchars($thiscolfilterexp));
            $thiscolfilterexp = "eval ('".addcslashes($thiscolfilterexp, "'\\")."')";
            $specs["leftfilter"] = array($thiscolfilterexp, trim(MB_Text2Words(strtolower(trim(SEARCH_RemoveAccents(SEARCH_HTML2Text($thiscolfiltervalue)))))));
            $leftfilterused = true;
          }
        } else {
          // $thiscolfiltervalue is user input!
          // Prevously the code did this:   $specs["filter"] = array('... <<<programmer input>>>...', '... <<<user input>>> ...');
          // Now we are about to do this:   $specs["slowselect"]['... <<<programmer input>>> ... <<<user input>>>...'];
          // So the user input must be carefully sanitized.
          $thiscolfiltervalue = MB_Text2Words($thiscolfiltervalue, true); // removes quotes and other dangerous stuff
          $thiscolfilterexp .= 'return QRY_SlowFilter(strtolower (trim (SEARCH_RemoveAccents (SEARCH_HTML2TEXT ($text)))), \'' . addcslashes($thiscolfiltervalue, "'\\") . '\');'; // put $thiscolfiltervalue in single quotes, just in case someone decides to make MB_Text2Words less safe
          N_Debug("TABLES_Auto: thiscolfilterexp $col: " . htmlspecialchars($thiscolfilterexp));
          $thiscolfilterexp = "eval ('".addcslashes($thiscolfilterexp, "'\\")."')";

          $specs["slowselect"][$thiscolfilterexp] = true;
        }

      }
    }
    $filterexp .= 'return strtolower (trim (SEARCH_RemoveAccents (SEARCH_HTML2TEXT ($text))));';
    N_Debug("TABLES_Auto: filterexp: " . htmlspecialchars($filterexp));
    $filterexp = "eval ('".addcslashes($filterexp, "'\\")."')";

    $tablehead = $nonemptytablehead = "";
  } else {
    // No column filters, create global filterexpression
    if ($specs["filterexp"]) {
      $filterexp = "trim (SEARCH_RemoveAccents (".$specs["filterexp"]."))";
    } else {
      $filterexp = '$object = $record; $text="";';
      foreach ($specs["content"] as $index => $content) {
        if ($specs["sort"][$index]) {
          $filterexp .= 'eval (\'$sortvalue = '.addcslashes($specs["sort"][$index],"'\\").';\');';
        } else {
          $filterexp .= '$sortvalue="";';
        }
        $filterexp .= '
          ob_start(); 
          eval (\''.addcslashes($content, "'\\").'\');
          $text .= " ".ob_get_contents();
          ob_end_clean();
        ';
      }
      $filterexp .= 'return strtolower (trim (SEARCH_RemoveAccents (SEARCH_HTML2TEXT ($text))));';
      N_Debug("TABLES_Auto: filterexp: " . htmlspecialchars($filterexp));
      $filterexp = "eval ('".addcslashes($filterexp, "'\\")."')";
    }
  }

  if (($myconfig[IMS_SuperGroupName()]["ml"]["metadatalevel"] && $myconfig[IMS_SuperGroupName()]["ml"]["metadatafiltering"] != "no") || 
       $myconfig[IMS_SuperGroupName()]["ml"]["metadatafiltering"] == "yes") {
    $filterexp = 'FORMS_ML_Filter('.$filterexp.', "'.ML_GetLanguage().'")';
  }

  $rawcount = MB_TurboMultiQuery ($specs["table"], array (
    "slowselect" => $specs["rawslowselect"], 
    "wherein" => $specs["wherein"], 
    "select" => $specs["select"], 
    "multimatch" => $specs["multimatch"], 
    "multimultimatch" => $specs["multimultimatch"], 
    "sort" => $sortexpr,
    "slice" => "count"
  ));
  N_Debug("TABLES_Auto: rawcount = $rawcount");

  if ($filter) {
    $qspecs = array (
      "slowselect" => $specs["slowselect"], 
      "wherein" => $specs["wherein"], 
      "select" => $specs["select"], 
      "multimatch" => $specs["multimatch"], 
      "multimultimatch" => $specs["multimultimatch"], 
      "leftfilter" => $specs["leftfilter"],
      "sort" => $sortexpr,
      "filter" => array ($filterexp, $filter),
      "slice" => "count"
    );
    N_Debug("TABLES_Auto: qspecs = <pre>" . print_r($qspecs, 1) . "</pre>");
    if ( $specs{"filter_callback_function"} )
    {
      unset($qspecs["slice"]);
      $callback_list = MB_TurboMultiQuery ($specs["table"], $qspecs);      
      $fname = $specs["filter_callback_function"];
      $fname( $specs , $callback_list );
  
      $count = count( $callback_list );
    } else {
      $count = MB_TurboMultiQuery ($specs["table"], $qspecs);
    }
  } else {
    $count = $rawcount; // save 7 valuable milliseconds 
  }
  N_Debug("TABLES_Auto: count = $count");

  if ($count == 0 && $specs["shownothingifnoresults"]) return "";

  if ($count && $count > $specs["maxlen"]) {
    eval ("global \$tblblk_".$specs["name"].";");
    eval ("\$block = \$tblblk_".$specs["name"].";");

    if ($specs["gotoobjectautodisable"] && isset($block)) {
      unset($specs["gotoobject"]); // disable gotoobject when a block is chosen
    }

    if($specs["gotoobject"]) {
      $gotoobject = $specs["gotoobject"];
    }

    if($gotoobject) {

      $foundobject = MB_Ref ($specs["table"], $gotoobject );
      $key = $gotoobject;
      eval('
        $record=$object=$foundobject;
        $contentsortcolexpr = ' . $sortexpr . ';                  // NEW: use this one
      ');

      // $contentsortcol = ' . $specs["sort"][$truesortcol-1] .';  // OLD: Do not use this, it doesnt work with smartmix 
      // if ($dir=="d") $contentsortcol = MB_Invert ($contentsortcol);

      N_Debug("TABLES_Auto: gotoobject: contentsortcol = " . N_htmlentities($contentsortcol) . " <br/>");
      N_Debug("TABLES_Auto: gotoobject: sortexpr = " . N_htmlentities($sortexpr) . " <br/>");
      N_Debug("TABLES_Auto: gotoobject: contentsortcolexpr = " . N_htmlentities($contentsortcolexpr) . " <br/>");

      // find all records <= gotoobject
      $lessequalcount = MB_TurboMultiQuery ($specs["table"], array (
        "slowselect" => $specs["slowselect"], 
        "wherein" => $specs["wherein"], 
        "select" => $specs["select"], 
        "multimatch" => $specs["multimatch"], 
        "multimultimatch" => $specs["multimultimatch"], 
        "leftfilter" => $specs["leftfilter"],
        "sort" => $sortexpr,
        "filter" => array ($filterexp, $filter),
        "slice" => "count",
        "range" => array ($sortexpr, "", $contentsortcolexpr)
      ));

      // find all records = gotoobject
      $equalcount = MB_TurboMultiQuery ($specs["table"], array (
        "slowselect" => $specs["slowselect"], 
        "wherein" => $specs["wherein"], 
        "select" => $specs["select"], 
        "multimatch" => $specs["multimatch"], 
        "multimultimatch" => $specs["multimultimatch"], 
        "sort" => $sortexpr,
        "leftfilter" => $specs["leftfilter"],
        "filter" => array ($filterexp, $filter),
        "slice" => "count",
        "range" => array ($sortexpr, $contentsortcolexpr, $contentsortcolexpr)
      ));

      N_Debug("TABLES_Auto: gotoobject found $equalcount elements with the same value");
      if($equalcount==1) { // only 1 element found
        $block = ceil(($lessequalcount ) / $specs["maxlen"] ) - 1;
      } else { // multiple entries with the same specification
        $resultlist = MB_TurboMultiQuery ($specs["table"], array (
         "slowselect" => $specs["slowselect"], 
         "wherein" => $specs["wherein"], 
          "select" => $specs["select"], 
          "multimatch" => $specs["multimatch"], 
          "multimultimatch" => $specs["multimultimatch"], 
          "sort" => $sortexpr,
          "leftfilter" => $specs["leftfilter"],
          "filter" => array ($filterexp, $filter)
          ,"slice" => array($lessequalcount - $equalcount + 1, $lessequalcount)
        ));

        $i=0;
        reset($resultlist);
        while ((list($key, $value) = each($resultlist)) && ($gotoobject!=$key)) {
          $i++;
        }
        $block = ceil(( ($lessequalcount - $equalcount + $i) + 1) / $specs["maxlen"] ) - 1;
      }
    } 

    $start = $block * $specs["maxlen"] + 1; 
    $end = ($block + 1) * $specs["maxlen"];
    if ($end > $count) $end = $count;

    if ($end < $start) {
      N_Redirect (N_AlterURL (N_MyVeryFullURL(), "tblblk_".$specs["name"], $block-1)); // qqq
    }

    // TTT
    $specs["tablespecs"]["extrahead"] = '<table cellpadding="0" cellspacing="0">' .
                                        '<tr><td>' .
                                        '<font style="font-family: Arial,Helvetica,sans-serif; font-size: 13px;">';

    $specs["tablespecs"]["extrahead"] .= "<b>$start - $end ".ML("van","of")." $count</b> ";
    // TTT

    if ($start > 1) {
      $url = N_MyVeryFullURL();
      $url = N_AlterURL ($url, "tblblk_".$specs["name"], $block-1);
      $specs["tablespecs"]["extrahead"] .= "<a class=\"ims_link\" href=\"$url\">&lt;&lt; ".ML("Vorige","Previous")."</a> ";
    }
    if ($end < $count) {
      $url = N_MyVeryFullURL();
      $url = N_AlterURL ($url, "tblblk_".$specs["name"], $block+1);
      $specs["tablespecs"]["extrahead"] .= "<a class=\"ims_link\" href=\"$url\">".ML("Volgende","Next")." &gt;&gt;</a> ";
    }
    $specs["tablespecs"]["extrahead"] .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
  } else {
    $start = 1;
    $end = $count;

    if ( $count && ($specs["alwaysshowcount_string"]!="") )
    {
      $specs["tablespecs"]["extrahead"] = '<table cellpadding="0" cellspacing="0">' .
                                          '<tr><td>' .
                                          '<font style="font-family: Arial,Helvetica,sans-serif; font-size: 13px;">';
      $specs["tablespecs"]["extrahead"] .= sprintf( $specs["alwaysshowcount_string"] , $count ) . "&nbsp;&nbsp;&nbsp;";
    }
  }

 // 20120507 KvD bewaar voor DMS documentenlijst assistent
  $query = array (
    "slowselect" => $specs["slowselect"], 
    "wherein" => $specs["wherein"], 
    "select" => $specs["select"], 
    "multimatch" => $specs["multimatch"], 
    "multimultimatch" => $specs["multimultimatch"], 
    "sort" => $sortexpr,
    "leftfilter" => $specs["leftfilter"],
    "filter" => array ($filterexp, $filter),
    "slice" => array ($start, $end)
  ) ;
  $keys = MB_TurboMultiQuery ($specs["table"], $query);

// 20120525 KvD
  $sgn = IMS_Supergroupname();
  $loggedin = SHIELD_Currentuser($sgn);
  if ($loggedin != "unknown") {
    $u = MB_Load("shield_${sgn}_users", $loggedin);
    $u["autotablequery"] = array ("query" => $query, "table" => $specs["table"]);
    MB_Save("shield_${sgn}_users", $loggedin, $u);
  }

//  $GLOBALS['g_autotablequery'] = array ("query" => $query, "table" => $specs["table"]);
///

  if (($filter || $specs["tablespecs"]["extrahead"] || $specs["alwaysfilter"]=="yes") && !$specs["nofilter"]) {
    if ($specs["colfilter"] == "yes") $showcolfilter = true;
    if(!$specs["tablespecs"]["extrahead"]) {
      $specs["tablespecs"]["extrahead"] = '<table cellpadding="0" cellspacing="0">' .
                                          '<tr><td>' .
                                          '<font style="font-family: Arial,Helvetica,sans-serif; font-size: 13px;">';
    }

    $url = N_MyVeryFullURL();
    $filtervar = "tblflt_".$specs["name"];
    if ($specs["colfilter"] != "yes") {
      $specs["tablespecs"]["extrahead"] .=
        '
        <b>'.ML("Filter", "Filter").':</b>
        ';

      $arr = N_ExplodeURL($url);
      foreach($arr["query"] as $name=>$value) {
        if($name != $filtervar) { // don't write 2 filtervar values
          $specs["tablespecs"]["extrahead"] .= 
            '<input type="hidden" name="' . $name .'" value="'. $value .'">';
          }
      }
      if (function_exists ('SKIN_DrawMenu')) {
        $specs["tablespecs"]["extrahead"] .= 
          '<input title="Zoektermen" type="text" name="' . $filtervar . '" size="10" class="style10px" value="' . N_HtmlEntities($filter) . '">
           <input title="'.ML("Filter","Filter").'" type="submit" class="inputButton" value="'.ML("Filter","Filter").'">
          ';
      } else {
        $specs["tablespecs"]["extrahead"] .= 
          '<input title="Zoektermen" type="text" name="' . $filtervar . '" size="10" style="font-size: 10px;" value="' . N_HtmlEntities($filter) . '">
           <input title="'.ML("Filter","Filter").'" type="submit" style="font-weight:bold; font-size: 10px; width:50px;" value="'.ML("Filter","Filter").'">
          ';
      }
    }
    if($filter) {
      if ($specs["colfilter"] == "yes") {
        $specs["tablespecs"]["extrahead"] .= '(' . '<b>'.ML("Filter", "Filter").':</b> ' . $count . ' ' . ML("uit","from") . ' ' . $rawcount . ')';
      } else { 
        $specs["tablespecs"]["extrahead"] .= 
          '(' . $count . ' ' . ML("uit","from") . ' ' . $rawcount . ')';
      }
    }
  }
  if($specs["tablespecs"]["extrahead"]) {
    $specs["tablespecs"]["extrahead"] .= 
      '
      </font></td></tr>
      </table>
      ';
  }
  // TTT


if(false) { // TTT
  $form = array();
  $form["title"] = ML("Zoek / Filter", "Search / Filter");
  $form["input"]["filter"] = $filter;
  $form["input"]["url"] = N_MyVeryFullURL();
  $form["input"]["alter1"] = "tblflt_".$specs["name"];
  $form["input"]["alter2"] = "tblblk_".$specs["name"];
  $form["metaspec"]["fields"]["filter"]["type"] = "string";
  $form["formtemplate"] = '
    <table>
      <tr><td><font face="arial" size=2><b>'.ML("Zoek / Filter","Search / Filter").':</b></font></td><td>[[[filter]]]</td></tr>
      <tr><td colspan=2>&nbsp</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
    </table>
  ';         
  $form["precode"] = '
    $data["filter"] = $input["filter"];
  ';
  $form["postcode"] = '
    $url = $input["url"];
    $url = N_AlterURL ($url, $input["alter1"], $data["filter"]);
    $url = N_AlterURL ($url, $input["alter2"], 0);
    $gotook = "closeme&parentgoto:".$url;
  ';
  $url = FORMS_URL ($form);
  if ($filter) {
    $specs["tablespecs"]["extrahead"] .= "<b>".ML("Filter", "Filter").":</b> ";
    $specs["tablespecs"]["extrahead"] .=  "<a class=\"ims_link\" href=\"$url\">$filter</a> ($count ".ML("uit","from")." $rawcount)";
  } else {
    if ($specs["tablespecs"]["extrahead"]) {
      $specs["tablespecs"]["extrahead"] .= "<b>".ML("Filter", "Filter").":</b> ";
      $specs["tablespecs"]["extrahead"] .= "<a class=\"ims_link\" href=\"$url\">&lt;".ML("geen","none")."&gt;</a>";
    }
  }
} // TTT

  if ($showcolfilter) {
    $specs["tablespecs"]["sort_topsticky"] = 1; // First row will be sticky (always remain at the top, unaffected by sorting)
  }

  T_Start ($specs["style"], $specs["tablespecs"]);
  foreach ($specs["tableheads"] as $dummy => $title) {
    echo $title;
    T_Next();
  }
  T_NewRow();


  if ($showcolfilter) {
    $basefieldname = "tblcolflt_" . $specs["name"];
    $arr = N_ExplodeURL($url);
    foreach($arr["query"] as $name=>$value) {
      if(substr($name, 0, strlen($basefieldname)) != $basefieldname) { // don't write 2 filtervar values
        echo '<input type="hidden" name="' . $name .'" value="'. $value .'">';
      }
    }

    // Put the column filters in the sticky row
    $i = 0;
    //for ($i = 1; $i <= count($specs["tableheads"]); $i++) {
    foreach ($specs["tableheads"] as $tablehead) {
     $i++;
     if ($tablehead) $nonemptytablehead = $tablehead;
     unset ($sortcol); 
     if ($specs["colfiltertype"][$i] == "submit") {
       echo '<input type="submit" style="font-weight: bold; font-size: 10px; width: 50px;"  title="Filter"  value="Filter">';
     } else {
       if ($specs["tablespecs"]["sort_map_$i"] && $specs["tablespecs"]["sort_map_$i"] != $i) {
          $sortcol = $specs["tablespecs"]["sort_map_$i"];
          if ($sortcol > $i) {
            // Show the field in this column and skip the mapped column
            $skipcols[$sortcol] = true;
          } else {
            // We already showed the the field above the mapped columns, so skip this one
            T_Next();
            continue;
          }
        }
        if (!$skipcols[$i]) {
          if ($specs["colfiltertype"][$i] == "none" || 
              !$nonemptytablehead) { // if the *first* column doesnt have a table head (ie like BPMS Gegevens), dont show any field 
            echo "&nbsp;";
          } else {
            $fieldname = $basefieldname . "_" . ($sortcol ? $sortcol : $i);
            // Gecko doesnt support multiline tooltips, so we put the entire second line inside braces
            $tooltip = ML("Filter op","Filter with").': '.N_HtmlEntities($nonemptytablehead). (N_Mozilla() ? " (" : "\n") . ML("Typ zoektermen + enter om te filteren", "Type search phrase + enter to filter") . (N_Mozilla() ? ")" : "");
            echo '<input style="width: 100%" type="text" title="'.$tooltip.'" name="'.$fieldname.'"  value="' . N_HtmlEntities($colfilter[$sortcol ? $sortcol : $i]) . '" ' . ($specs["colfilterautosubmit"] == "no" ? "" : 'onkeypress="return submitViaEnter(event)"') . '>';
          }
        }
      }
      T_Next();
    }
    T_NewRow();
  }

  foreach ($keys as $key => $dummy) {
    foreach ($specs["content"] as $index => $content) {
      $record = $object = MB_Load ($specs["table"], $key);
      $sortexp = $specs["sort"][$index]; 
      $sortvalue = "";
      if ($sortexp) {
        eval ("\$sortvalue = $sortexp;");
        if (($myconfig[IMS_SuperGroupName()]["ml"]["metadatalevel"] && $myconfig[IMS_SuperGroupName()]["ml"]["metadatafiltering"] != "no") || 
             $myconfig[IMS_SuperGroupName()]["ml"]["metadatafiltering"] == "yes") {
          $sortvalue = FORMS_ML_Filter($sortvalue);
        }
      }
      eval ($content);
      T_Next();
    }
    T_NewRow();
  }
  $result = TS_End();

  if ($showcolfilter && !$specs["nofilter"]) {
    $colfilterscript = '
      <script type="text/javascript">
      <!--
      function submitViaEnter(evt) {
        evt = (evt) ? evt : event;
        var target = (evt.target) ? evt.target : evt.srcElement;
        var form = target.form;
        var charCode = (evt.charCode) ? evt.charCode :
          ((evt.which) ? evt.which : evt.keyCode);
        if (charCode == 13) {
          form.submit();
          return false;
        }
        return true;
      }
      //-->
      </script>
    ';

    // Somehow, browsers dont like it if I put the <form> inside the tr (around multiple td's), so let's nest it around the entire table
    // When the entire page is loaded at once, browsers correct whatever it was I did wrong. But when replace a div's innerHTML, they dont make
    // the same correction.
    $result = $colfilterscript . 
              '<form name="colfilterform" action="'.$arr["path"].'" method="get" style="margin: 0; padding: 0">' .
              $result .
              '</form>';

  } elseif ($specs["tablespecs"]["extrahead"] && !$specs["nofilter"]) { 
    // The old filter also had some minor layout problems when replacing a div's innerHTML, so is now also nested aound the entire table
    $result = '<form name="filterform" action="'.$arr["path"].'" method="get" style="margin: 0; padding: 0">' .
              $result .
              '</form>';
  }
  return $result;
}

function TABLES_Auto_NoReload ($specs) {
  /* Same $specs as TABLES_Auto, but makes everything (sorting, pagination) work without reloading the page */

  $tablename = $specs["name"];
  if (!$specs["name"]) N_Die("TABLES_Auto_Noreload: requires a name");
  $dynid = N_Guid();
  N_Debug("TABLES_Auto_NoReload: dynid = $dynid");

  // Store the specs and the script path in a tmp object
  $tablesettings["specs"] = $specs;
  $tablesettings["origparams"] = $_GET;
  $myurlparts = N_ExplodeUrl(N_MyBareUrl());
  $tablesettings["path"] = $myurlparts["path"];
  TMP_SaveObject($dynid, $tablesettings);

  // Generate table
  $content = TABLES_Auto_DynamicTable($tablesettings, $dynid);
  if (!$content) return $content; // dont wrap around empty content

  // Wrap the content in a dynamic object
  return DHTML_DynamicObject($content, $dynid) . DHTML_PrepRPC();

}

function TABLES_Auto_DynamicUrl($url, $tablename, $dynid, $dhtmlcall = false) {
  // This function is called for each url, whenever a dynamic autotable is created. That means it is 
  // called when the page is first created, but also each time that only the table is updated ($dhtmlcall = true).

  // For each url to the *current* page:
  // - If the url causes the table parameters to change, but does not change anything else (no currentobject etc.), transform the url into
  //   a DHTML_RPCURL.
  // - For other url's, do nothing if it is a normal call, but if it is a dynamic call:
  //   - compare the current (dynamic) parameters to the original parameters for the page
  //   - for each table parameter in the url that matches the original parameters, replace it with the current dynamic parameter
  //   - for each dynamic table parameter that is not part of the url and is not part of the original parameters, add it to the url
  //   This will cause dynamic changes (sorting, pagination) to persist when you click a non-dynamic link.

  // This function only looks at href's, and can not deal with url's that are javascript popup. However, any code that is executed
  // by TABLES_Auto and that uses N_MyFullUrl to build url's (or hidden form fields, or whatever), will "see" the dynamic parameters.
  // This is why dynamic changes persist when you use the filter.

  // Skip url's that we don't understand
  $url = trim($url);
  if (substr($url, 0, 17) == "openimstoolkit://") return $url;
  if (substr($url, 0, 1) == "#") return $url;
  if (substr($url, 0, 10) == "javascript") return $url;

  // Skip url's to a different page. ONLY process url's to the current page.
  $urlparts = N_ExplodeUrl($url);
  if ($urlparts["host"] && ($urlparts["host"] != getenv("HTTP_HOST"))) return $url;
  if ($urlparts["path"]) {
    $myurlparts = N_ExplodeUrl(N_MyBareUrl());
    if ($urlparts["path"] != $myurlparts["path"]) return $url;
  }

  // Determine what the url does

  $modifiestableparams = false;  // True if the url modifies a table parameter. 
                                 // If the url explicitly contains an empty table parameter (and the parameter is not empty at the moment), this is considered modification.
  $modifiesotherparams = false;  // True if the url modifies some other parameter (such as currentobject)
  foreach ($urlparts["query"] as $urlparamname => $urlparamvalue) {
    if (strpos($urlparamname, $tablename) !== false && substr($urlparamname, 0, 3) == "tbl") {
      if ($_GET[$urlparamname] != $urlparamvalue) {
        $modifiestableparams = true;
      }
    } else {
      if ($_GET[$urlparamname] != $urlparamvalue) $modifiesotherparams = true;
      //if ($_GET[$urlparamname] != $urlparamvalue) $debugurl .= "url modifies param $urlparamname -$urlparamvalue- -" . $_GET[$urlparamname] . "- // "; // DEBUG
    }
  }

  //if ($modifiesotherparams) N_Log("test", $debugurl, "url : " . N_HtmlEntities($url) . "<br/>_GET: " . N_HtmlEntities(print_r($_GET, 1)));
  //if ($modifiesotherparams) return "$debugurl"; // DEBUG

  // Modify the url

  if ($modifiestableparams && !$modifiesotherparams) {
    N_Debug("TABLES_Auto_DynamicUrl: found url to ajaxify: $url");
    $input["dynid"] = $dynid;
    foreach ($urlparts["query"] as $urlparamname => $urlparamvalue) {
      $input["dynamic"][$urlparamname] = $urlparamvalue;
    }
    $code = '
       $tablesettings = TMP_LoadObject($input["dynid"]);
       echo DHTML_EmbedJavaScript(DHTML_Master_SetDynamicObject($input["dynid"], TABLES_Auto_DynamicTable($tablesettings, $input["dynid"], $input["dynamic"])));
    ';
    $url = DHTML_RPCURL($code, $input); 
  } elseif ($dhtmlcall) {

    N_Debug("TABLES_Auto_DynamicUrl: found url to modify: $url");
    $tablesettings = TMP_LoadObject($dynid); 
    $origparams = $tablesettings["origparams"];
    foreach ($urlparts["query"] as $urlparamname => $urlparamvalue) {
      if (strpos($urlparamname, $tablename) !== false && substr($urlparamname, 0, 3) == "tbl") {
        $origvalue = $origparams[$urlparamname];
        $currentvalue = $_GET[$urlparamname];
        if (($origvalue == $urlparamvalue) && ($currentvalue != $urlparamvalue)) $url = N_AlterUrl($url, $urlparamname, $currentvalue);
      }     
    }
    $urlparts = N_ExplodeUrl($url);
    foreach ($_GET as $currentname => $currentvalue) {
      if ($currentvalue && strpos($currentname, $tablename) !== false && substr($currentname, 0, 3) == "tbl") {
        $origvalue = $origparams[$currentname];
        $urlparamvalue = $urlparts[$currentname];
        if (!$origvalue && !$urlparamvalue && $currentvalue) $url = N_AlterUrl($url, $currentname, $currentvalue);
      }     
    }
  }
    
  return $url;   
}

function TABLES_Auto_DynamicTable($tablesettings, $dynid, $dynamic = array()) {
  // Create an autotable, based on the $specs and the dynamic parameters (which will overrule the global request parameters) in the dynamic object
  // Modify all the hyperlinks in the standard autotable, to make them replace the table without reloading the page.
  // Be aware that this function from the RPCURL handler, with openims.php not even loaded!

  if (!function_exists ("DMS_MouseOver")) {
  // DMS mouse over
    $internal_component = FLEX_LoadImportableComponent ("support", "08fa2037f2f020a44e9aac15d6d92135");
    $internal_code = $internal_component["code"];
    eval ($internal_code);
  }

  // Load a bunch of libraries so that there is a chance that TABLES_Auto will be able to evaluate all those content-expressions.
  if ($dynamic) {
    uuse("multi");
    uuse("dhtml");
    uuse("ims"); 
    uuse("bpms");
    uuse("skins");
    uuse("dmsuif");
    uuse("bpmsuif");
    uuse("dbmuif");
    uuse("files");
    uuse("case");
    uuse("black");
    uuse("link");
    uuse("reports");
  }

  global $fake_query_string; // Used by N_MyFullUrl
  foreach ($dynamic as $paramname => $paramvalue) {
    $GLOBALS[$paramname] = $paramvalue;
    $_GET[$paramname] = $paramvalue;
    if ($fake_query_string) $fake_query_string .= "&$paramname=".urlencode($paramvalue); else $fake_query_string = "$paramname=".urlencode($paramvalue);
    // LF20090906: added urlencode, so that you don't lose the "refreshparent" in "closeme&refreshparent". Losing part of an url parameter breaks dynamic tables, because if your table contains urls with gotook=closeme while the actual current value is "closeme&refreshparent", all those url's will become static and refresh the entire page instead of just the table.
  }
  $_SERVER["QUERY_STRING"] = $fake_query_string; // just in case (but does not change the outcome of getenv("QUERY_STRING");
  // fake the script name
  $_SERVER["REDIRECT_URL"] = $tablesettings["path"];

  $specs = $tablesettings["specs"];
  $specs["inplace"] = "no";  // to prevent loops
  $specs["gotoobjectautodisable"] = "yes";
  $content = TABLES_Auto($specs);
  if (!$content) return $content;
  $tablename = $specs["name"];

  // If this is a dynamic call, save the sort params, because that's what openims.php does on a non-dynamic call.
  global $myconfig, $currentfolder;
  if ($dynamic && $tablename=="dmsgrid" && $currentfolder && $myconfig[IMS_SuperGroupName()]["donotsavesortparams"] != "yes") {
    // retrieve saved sort params from "shield_$sgn_users_sortparams"
    global $currentfolder;
    $sortparams = &MB_Ref("shield_" . IMS_SuperGroupName() . "_users_sortparams", SHIELD_CurrentUser (IMS_SuperGroupName())."#".$currentfolder);
    global $tbldir_dmsgrid, $tblsrt_dmsgrid;
    if ($tbldir_dmsgrid) $sortparams["tbldir"] = $tbldir_dmsgrid;
    if ($tblsrt_dmsgrid) $sortparams["tblsrt"] = $tblsrt_dmsgrid;
  } 

  // Scan for url's.
  $replacefunc = create_function('$matches', 
                                 'return \'href="\' . TABLES_Auto_DynamicUrl($matches[1], "' . $tablename .'", "' . $dynid . '", ' . ($dynamic ? "true" : "false") . ') . \'"\';');
  // Url's with double quotes
  $content = preg_replace_callback('/href="([^"]+)"/', $replacefunc, $content);
  // Url's without quotes
  $content = preg_replace_callback('/href=([^\'"][^\s]*)/', $replacefunc, $content);

  // Replace filterforms. We dont check if the action is to the current page, we just check the name of the form.
  $code = '
    $tablesettings = TMP_LoadObject($input["dynid"]);
    echo DHTML_EmbedJavaScript(DHTML_Master_SetDynamicObject($input["dynid"], TABLES_Auto_DynamicTable($tablesettings, $input["dynid"], $data)));
  ';
  $filterformurl = DHTML_RPCFormAction($code, array("dynid" => $dynid));
  $content = preg_replace('/name="(col)?filterform" action="[^"]+"/', 'name="\1filterform" action="'.$filterformurl.'"', $content); 

  return $content;
}
?>