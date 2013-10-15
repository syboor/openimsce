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



function N_Content ($item)
  { 
    global $REMOTE_ADDR;
    $record = &MB_Ref ("currentcontent", $REMOTE_ADDR);
    $record["value"] = $item;
    MB_AutoDelete ("currentcontent", $REMOTE_ADDR, 3600);
    $rawcontent = MB_Fetch ("content", $item, "value");
    $content = N_ProcessContent ($rawcontent);
    echo $content;
  }

  function N_ShowSource ($content)
  {
    return str_replace(" ", "&nbsp;", str_replace(chr(10),"<br>",htmlentities($content)));

  }

  function N_ExplodeContent ($content)
  {
    $array = Array();
    $pos = 0;
    $elem = "";
    $counter = 1;
    if (substr ($content, $pos, 1)=="<") $tag=true; else $tag=false;
    $done = false;
    while (!$done) {
      if ($tag) {
        if (substr ($content, $pos, 1)==">") {
          $array[$counter++] = $elem.">";          
          $elem = "";
          $tag = false;
        } else {
          $elem.=substr ($content, $pos, 1);
        }
      } else {
        if (substr ($content, $pos, 1)=="<") {
          if (trim($elem)!="") $array[$counter++] = $elem;
          $elem="<";
          $tag = true;
        } else {
          $elem.=substr ($content, $pos, 1);
        }
      }
      $pos++;
      if ($pos==strlen($content)) {
        $done = true;
        if (trim($elem)!="") $array[$counter++] = $elem;
      }
    }
    return $array;
  }

  function N_ImplodeContent ($array)
  {
    for ($i=1; $i<=count($array); $i++) {
      $content .= $array[$i];
    }
    return $content;
  }

  function N_ProcessContentElements ($array) 
  {
    for ($i=1; $i<=count($array); $i++) {
      $elem = $array[$i];
      if (strtolower(substr ($elem, 1, 4))=="font") {
        $elem = "<font>";
      }
      $array[$i] = $elem;      
    }    
    return $array;
  }

  function N_ProcessContent ($rawcontent)
  {
    $array = N_ExplodeContent ($rawcontent);
    $array = N_ProcessContentElements ($array);
    $content = N_ImplodeContent ($array);
//    return N_ShowSource($content);
    return $content;
  }

?>