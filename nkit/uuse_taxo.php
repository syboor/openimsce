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



function TAXO_FindItem ($taxo, $item)
{
  foreach ($taxo as $key => $specs) {
    if ($specs["item"]==$item) {
      return array ("id" => $key, "specs"=>$specs, "parent"=>"", "level"=>1);
    }
    if ($found = TAXO_FindItem ($specs["children"], $item)) {
      $found["parent"] = $key;
      $found["level"]++;
      return $found;
    }
  }
  return array();
}

function TAXO_FindID ($taxo, $id)
{
  foreach ($taxo as $key => $specs) {
    if ($key==$id) {
      return array ("specs"=>$specs, "parent"=>"", "level"=>1);
    }
    if ($found = TAXO_FindID ($specs["children"], $id)) {
      $found["parent"] = $key;
      $found["level"]++;
      return $found;
    }
  }
  return array();
}

function TAXO_DoParse2 ($from, $to, $level, $parent)
{
  global $taxo_parse_list; 
  $result = array();
  for ($i=$from; $i<=$to; $i++)
  {
    if ($taxo_parse_list[$i]["level"]==$level) {
      if ($taxo_parse_list[$i]["key"]) {
        $key = $taxo_parse_list[$i]["key"];
      } else {
        $key = N_GUID();
      }
      $result[$key]["item"] = $taxo_parse_list[$i]["item"];
      if ($parent) {
        $result[$key]["fullitem"] = $parent." > ".$taxo_parse_list[$i]["item"];
      } else {
        $result[$key]["fullitem"] = $taxo_parse_list[$i]["item"];
      }
      if ($i<$to && $taxo_parse_list[$i+1]["level"]==$level+1) {
        $f = $i+1;
        $t = $i+1;
        while ($t<$to && $taxo_parse_list[$t]["level"]!=$level) $t++;
        $result[$key]["children"] = TAXO_DoParse2 ($f, $t, $level+1, $result[$key]["fullitem"]);
      }
    }
  }
  return $result;
}

function TAXO_DoParse1 ($input)
{
  global $taxo_parse_list;
  $input = str_replace ("\r\n", "\n", $input);
  $input = str_replace ("\r", "\n", $input);
  $list = explode ("\n", $input);
  foreach ($list as $item) {
    $item = trim($item);
    if ($item) {
      $level = 0;
      while (substr ($item, $level, 1)=="+") $level++;
      $item = trim (substr ($item, $level));
      $taxo_parse_list[++$ctr]["level"] = $level;
      if (substr ($item, strlen($item)-1, 1) == ")") {
        for ($i=strlen($item)-3; $i>0; $i--) {
          if (substr ($item, $i, 1) == "(") break;
        }
        if (substr ($item, $i, 1) == "(") {
          $key = substr ($item, $i+1, strlen ($item)-$i-2);
          $item = trim (substr ($item, 0, $i-1));
          $taxo_parse_list[$ctr]["key"] = $key;
        }
      }
      $taxo_parse_list[$ctr]["item"] = $item;
    }
  }
  return $ctr;
}


function TAXO_Parse ($input)
{  
  $ctr = TAXO_DoParse1 ($input);
  return TAXO_DoParse2 (1, $ctr, 0, "");
}

function TAXO_UnParse1 ($taxo, $level=0)
{
  $max = 0;
  foreach ($taxo as $key => $obj) {
    $content = "";
    for ($i=0; $i<$level; $i++) $content .= "+";
    $content .= $obj["item"];
    if (strlen($content)>$max) $max = strlen($content);
    $submax = TAXO_UnParse1 ($obj["children"], $level+1);
    if ($submax > $max) $max = $submax;
  }
  return $max;
}

function TAXO_UnParse2 ($max, $taxo, $level=0)
{
  foreach ($taxo as $key => $obj) {
    $line = "";
    for ($i=0; $i<$level; $i++) $line .= "+";
    $line .= $obj["item"];
    while (strlen($line) < $max) $line .= " ";
    $content .= $line."          (".$key.")\r\n";
    $content .= TAXO_UnParse2 ($max, $obj["children"], $level+1);
  }
  return $content;
}

function TAXO_UnParse ($taxo)
{
  $max = TAXO_UnParse1 ($taxo);
  return TAXO_UnParse2 ($max, $taxo);
}

function TAXO_CountEndPoints($arr) //Count the number of endpoints of an array from TAXO_Parse();
{
  $children = 0;
  foreach($arr as $id => $info)
  {
    if(isset($info["children"]) && count($info["children"]))
      $children += TAXO_CountEndPoints($arr[$id]["children"]);
    else
      $children++;
  }
  return $children;
}

function TAXO_TestSet ($set = 1)
/*
  uuse ("taxo");
  N_EO ($taxo = TAXO_Parse (TAXO_TestSet ()));
  echo "<br>------------------------------------------------------------------<br>";
  echo N_XML2HTML ($specs = TAXO_UnParse ($taxo));
  echo "<br>------------------------------------------------------------------<br>";
  N_EO ($taxo2 = TAXO_Parse ($specs));
  echo "<br>------------------------------------------------------------------<br>";
  echo N_XML2HTML ($specs2 = TAXO_UnParse ($taxo2));
  echo "<br>------------------------------------------------------------------<br>";
  N_EO ($found = TAXO_FindItem ($taxo, "Blob"));
  echo "<br>------------------------------------------------------------------<br>";
  N_EO ($found = TAXO_FindID ($taxo, $found["id"]));
*/
{
  if ($set==1) {
    return "Een
+Aap
++Hans
++Grietje
+++Bla
+++++Blub
+++Blob
++Heks
+Noot
+Mies
Twee
+Aap
+Noot
+Mies
Drie
+Aap
+Noot
+Mies";
  }
}

?>