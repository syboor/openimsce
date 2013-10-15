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



function SDEX_2ComparableString ($s)
{
  return "*".bin2hex (SDEX_2ComparableString_Raw ($s))."0";
}

function SDEX_Num2String ($s)
{
  N_Debug ("SDEX_Num2String ($s)");
    if ($s > pow (10,300)) {
      return "#>e>999";
    } else if ($s < -pow (10,300)) {
      return "#<e<000";
    } else if ($s==0) {
      $sign = 0;
    } else {
      if ($s<0) {
        $sign = "-";
        $s = -$s;
      } else {
        $sign = "+";
      }
      $log10 = round (log ($s)/log (10));
      if (($s / pow (10, $log10)) < 1) $log10--;
      if (($s / pow (10, $log10)) >= 10) $log10++;
      $num = $s / pow (10, $log10);
      $num = round ($num * 100000000000) / 100000000000;
      $s = $num * pow (10, $log10);
      $log10 = round (log ($s)/log (10));
      if (($s / pow (10, $log10)) < 1) $log10--;
      if (($s / pow (10, $log10)) >= 10) $log10++;
      $num = $s / pow (10, $log10);
      if ($log10==0) {
        $log10 = "e=";
      } else if ($log10 < 0) {
        $log10 = "e<".strtr (substr ("000".(-$log10), strlen ("000".(-$log10))-3), "0123456789", "9876543210");
      } else {
        $log10 = "e>".substr ("000".$log10, strlen ("000".$log10)-3);
      }
    }
    if ($sign=="0") {
      return "#=";
    } else {
      $ns = $log10."n".$num;
      if ($sign=="-") {
        return "#<". strtr ("$ns", "<>0123456789", "><9876543210");
      } else {
        return "#>$ns";
      }
    }
}

function SDEX_2ComparableString_Raw ($s)
{
  N_Debug ("SDEX_2ComparableString_Raw ($s)");
  if ($s===false) $s=0;
  if ($s===null) $s=0;
  if ($s===true) $s=1;
  if (is_numeric ($s)) {
    return SDEX_Num2String ($s);
  } else {
    return strtolower ($s);
/*
    preg_match_all("/[+-]?([0-9]*\.?[0-9]+|[0-9]+\.?[0-9]*)/i", $s, $out);
    $out = $out[0];
    if (count($out)>0 && count($out)<3) {
      $s = preg_replace ("/[+-]?([0-9]*\.?[0-9]+|[0-9]+\.?[0-9]*)/i", "#", $s);
      foreach ($out as $num) {
        $s .= "-".SDEX_Num2String ($num);
      }
    }
*/
  }
}

function SDEX_Nuke ($name)
{
  $dir = getenv("DOCUMENT_ROOT")."/metabase/sdex/".MB_HELPER_Key2Name($name);
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure($dir);
}

function SDEX_Add ($name, $key, $value)
{
  N_Debug ("SDEX_Add ($name, $key, $value)");
  if ($value===false) $value = 0;
  $magiclimit = 500;
  $dir = getenv("DOCUMENT_ROOT")."/metabase/sdex/".MB_HELPER_Key2Name($name);
//  N_Lock ($dir."/master.phpdat");
  $master = unserialize (N_ReadFile ($dir."/master.phpdat"));
  if (!$master) {
    $master[N_Guid()] = -9999999999999; // -9999999999999 is smallest serialize proof number
    N_WriteFile ($dir."/master.phpdat", serialize ($master));
  }
  $bestguid="";
  $bestbottom = -9999999999999;
  foreach ($master as $guid => $bottom)
  {
    if ($value >= $bottom && $bottom >= $bestbottom) {
      $bestbottom = $bottom;
      $bestguid = $guid;
    }
  }
  $set = unserialize (N_ReadFile ($dir."/$bestguid.phpdat"));
  $set[$key] = $value;
  if (count ($set) > $magiclimit) {
    $count = count ($set);
    asort ($set);
    foreach ($set as $key => $value) {
      if (++$c == (int)($count*2/3)) {
        $newbottom = $value;
      }
    }
    if ($newbottom != $bestbottom) {
      $newguid = N_GUID();
      $master[$newguid] = $newbottom;
      N_WriteFile ($dir."/master.phpdat", serialize ($master));
      foreach ($set as $key => $value) 
      {
        if ($value >= $newbottom) {
          unset ($set[$key]);
          $set2[$key] = $value;
        }
      }      
      N_WriteFile ($dir."/$newguid.phpdat", serialize ($set2));
    }
  }
  N_WriteFile ($dir."/$bestguid.phpdat", serialize ($set));
//  N_Unlock ($dir."/master.phpdat");  
}

function SDEX_Delete ($name, $key, $value)
{
  N_Debug ("SDEX_Delete ($name, $key, $value)");
  if ($value===false) $value = 0;
  $dir = getenv("DOCUMENT_ROOT")."/metabase/sdex/".MB_HELPER_Key2Name($name);
//  N_Lock ($dir."/master.phpdat");
  $master = unserialize (N_ReadFile ($dir."/master.phpdat"));
  if (!$master) {
    $master[N_Guid()] = -9999999999999; // -9999999999999 is smallest serialize proof number
    N_WriteFile ($dir."/master.phpdat", serialize ($master));
  }
  $bestguid="";
  $bestbottom = -9999999999999;
  foreach ($master as $guid => $bottom)
  {
    if ($value >= $bottom && $bottom >= $bestbottom) {
      $bestbottom = $bottom;
      $bestguid = $guid;
    }
  }
  $set = unserialize (N_ReadFile ($dir."/$bestguid.phpdat"));
  unset ($set[$key]);
  N_WriteFile ($dir."/$bestguid.phpdat", serialize ($set));
//  N_Unlock ($dir."/master.phpdat");
}

function SDEX_Top ($name, $amount)
{
  $dir = getenv("DOCUMENT_ROOT")."/metabase/sdex/".MB_HELPER_Key2Name($name);
  $master = unserialize (N_ReadFile ($dir."/master.phpdat"));
  if (!$master) return array();
  asort ($master);
  $count = 0;
  foreach ($master as $guid => $bottom) {
    $id[$count] = $guid;
    $bot[$count] = $bottom;
    $count++;
  }
  for ($i=$count-1; $i>=0; $i--) {
    if ($amount) {
      $set = unserialize (N_ReadFile ($dir."/".$id[$i].".phpdat"));      
      arsort ($set);
      foreach ($set as $theid => $val) {
        if ($amount) {  
          $result[$theid] = $val;
          $amount--;
        }
      }
    }
  }
  return $result;
}

function SDEX_Bottom ($name, $amount)
{
  $dir = getenv("DOCUMENT_ROOT")."/metabase/sdex/".MB_HELPER_Key2Name($name);
  $master = unserialize (N_ReadFile ($dir."/master.phpdat"));
  if (!$master) return array();
  asort ($master);
  $count = 0;
  foreach ($master as $guid => $bottom) {
    $id[$count] = $guid;
    $bot[$count] = $bottom;
    $count++;
  }
  for ($i=0; $i<$count-1; $i++) {
    if ($amount) {
      $set = unserialize (N_ReadFile ($dir."/".$id[$i].".phpdat"));      
      asort ($set);
      foreach ($set as $theid => $val) {
        if ($amount) {  
          $result[$theid] = $val;
          $amount--;
        }
      }
    }
  }
  return $result;
}

function SDEX_Match ($name, $low, $high="4kj52k34j5kjg542kj34h5j4352kj3")
{
  if ($low===false) $low = 0;
  if ($high===false) $high = 0;
  if ($high=="4kj52k34j5kjg542kj34h5j4352kj3") $high = $low;
  $result = array();
  $dir = getenv("DOCUMENT_ROOT")."/metabase/sdex/".MB_HELPER_Key2Name($name);
  $master = unserialize (N_ReadFile ($dir."/master.phpdat"));
  if (!$master) return array();
  asort ($master);
  $count = 0;
  foreach ($master as $guid => $bottom) {
    $id[$count] = $guid;
    $bot[$count] = $bottom;
    $count++;
  }
  for ($i=0; $i<$count-1; $i++) {
    if ($bot[$i] <= $high && $bot[$i+1] >= $low) {
      $set = unserialize (N_ReadFile ($dir."/".$id[$i].".phpdat"));      
      foreach ($set as $theid => $val) {
        if ($val>=$low && $val<=$high) $result[$theid] = $val;
      }
    }
  }
  if ($bot[$count-1] <= $high) {
    $set = unserialize (N_ReadFile ($dir."/".$id[$count-1].".phpdat"));      
    if (is_array ($set)) foreach ($set as $theid => $val) {
      if ($val>=$low && $val<=$high) $result[$theid] = $val;
    }
  }
  asort ($result);
  return $result;
}

function SDEX_Test ($stage)
{
  if ($stage==0) {
    SDEX_Nuke ("test");
    for ($i=0; $i<1000; $i++) { // initial fill
      if (N_Random(2)==1) 
        SDEX_Add ("test", "x".$i, -N_Random (200)+100);
      else
        SDEX_Add ("test", "x".$i, chr(N_Random(256)).chr(N_Random(256)).chr(N_Random(256)).chr(N_Random(256)));
    }
  } else if ($stage==1) {
    SDEX_Nuke ("test");
    for ($i=0; $i<1000; $i++) {
      SDEX_Add ("test", $i, (int)($i/10));
    }
  } else if ($stage==2) {
    for ($i=0; $i<1000; $i++) { // re-set 1000 values
      SDEX_Delete ("test", $i, (int)($i/10));
      SDEX_Add ("test", $i, (int)($i/10));
    }
  } else if ($stage==3) { // dump index
    $dir = getenv("DOCUMENT_ROOT")."/metabase/sdex/".MB_HELPER_Key2Name("test");
    $master = unserialize (N_ReadFile ($dir."/master.phpdat"));
    asort ($master);
    foreach ($master as $guid => $bottom) {
      echo "<b>GUID: $guid BOTTOM: [$bottom]</b><br>";
      $set = unserialize (N_ReadFile ($dir."/$guid.phpdat"));
      N_EO ($set);
    }
  } else if ($stage==4) { // match
    echo "-1: ".serialize (SDEX_Match ("test", -1))."<br>";
    echo "0: ".serialize (SDEX_Match ("test", 0))."<br>";
    echo "1: ".serialize (SDEX_Match ("test", 1))."<br>";
    echo "10: ".serialize (SDEX_Match ("test", 10))."<br>";
    echo "25: ".serialize (SDEX_Match ("test", 25))."<br>";
    echo "<nobr>24-26: ".serialize (SDEX_Match ("test", 24, 26))."</nobr><br>";
    echo "<nobr>23-27: ".serialize (SDEX_Match ("test", 23, 27))."</nobr><br>";
    echo "<nobr>22-28: ".serialize (SDEX_Match ("test", 22, 28))."</nobr><br>";
    echo "50: ".serialize (SDEX_Match ("test", 50))."<br>";
    echo "90: ".serialize (SDEX_Match ("test", 90))."<br>";
    echo "98: ".serialize (SDEX_Match ("test", 98))."<br>";
    echo "99: ".serialize (SDEX_Match ("test", 99))."<br>";
    echo "100: ".serialize (SDEX_Match ("test", 100))."<br>";
    echo "101: ".serialize (SDEX_Match ("test", 101))."<br>";
  } else if ($stage==5) { // match
    echo "-1: ".serialize (SDEX_Match ("test", -1))."<br>";
    echo "1: ".serialize (SDEX_Match ("test", 1))."<br>";
    echo "10: ".serialize (SDEX_Match ("test", 10))."<br>";
    echo "25: ".serialize (SDEX_Match ("test", 25))."<br>";
    echo "<nobr>24-26: ".serialize (SDEX_Match ("test", 24, 26))."</nobr><br>";
    echo "<nobr>23-27: ".serialize (SDEX_Match ("test", 23, 27))."</nobr><br>";
    echo "<nobr>22-28: ".serialize (SDEX_Match ("test", 22, 28))."</nobr><br>";
    echo "50: ".serialize (SDEX_Match ("test", 50))."<br>";
    echo "90: ".serialize (SDEX_Match ("test", 90))."<br>";
    echo "98: ".serialize (SDEX_Match ("test", 98))."<br>";
    echo "99: ".serialize (SDEX_Match ("test", 99))."<br>";
    echo "100: ".serialize (SDEX_Match ("test", 100))."<br>";
    echo "101: ".serialize (SDEX_Match ("test", 101))."<br>";  
  } else if ($stage==6) { // match
    echo "top5: ".serialize (SDEX_Top ("test", 5))."<br>";
    echo "top10: ".serialize (SDEX_Top ("test", 10))."<br>";
    echo "top25: ".serialize (SDEX_Top ("test", 25))."<br>";
    echo "bottom5: ".serialize (SDEX_Bottom ("test", 5))."<br>";
    echo "bottom10: ".serialize (SDEX_Bottom ("test", 10))."<br>";
    echo "bottom25: ".serialize (SDEX_Bottom ("test", 25))."<br>";
  } else if ($stage==7) { // SDEX_2ComparableString

$test = array (
  3*pow (10, -250),
  3*pow (10, -260),
  3*-pow (10, -250),
  3*-pow (10, -260),
  pow (10, -250),
  pow (10, -260),
  -pow (10, -250),
  -pow (10, -260),
  3*pow (10, 250),
  3*pow (10, 260),
  3*-pow (10, 250),
  3*-pow (10, 260),
  pow (10, 250),
  pow (10, 260),
  -pow (10, 250),
  -pow (10, 260),
  pow (10, 500),
  -pow (10, 500),
  0.000000000000001,
  -0.000000000000001,
  pow (31321, 25),
  pow (31321, 50),
  -pow (31321, 25),
  -pow (31321, 50),
  pow (31321, -25),
  pow (31321, -50),
  -pow (31321, -25),
  -pow (31321, -50),
  0,
  1,
  false,
  true,
  null,
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
  9,
  99,
  999,
  9999,
  99999,
  999999,
  9999999,
  99999999,
  999999999,
  9999999999,
  99999999999,
  999999999999,
  9999999999999,
  99999999999999,
  999999999999999,
  9999999999999999,
  99999999999999999,
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

foreach ($test as $dummy => $s) 
{
  $array [(++$ctr)." - [".($s)."] ".SDEX_2ComparableString_Raw($s)." "] = SDEX_2ComparableString ($s);
}

asort ($array);

foreach ($array as $a => $b) {
  echo N_XML2HTML ($a)." <b>".N_XML2HTML ($b)."</b><br>";
}

  }

}


?>