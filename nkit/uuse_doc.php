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
uuse ("forms");

function DOC_AllFIles ()
{
  $files = N_Tree ("html::nkit");
  foreach ($files as $file => $specs) {
    if (strpos ($specs["filename"], ".php")) {
      $result[$specs["filename"]] = $file;
    }
  }
  unset ($result["adodb-time.inc.php"]);
  unset ($result["XPath.class.php"]);
  unset ($result["xfer.php"]);
  unset ($result["build.php"]);
  unset ($result["content.php"]);
  unset ($result["flowtest.php"]);
  unset ($result["form.php"]);
  unset ($result["gate.php"]);
  unset ($result["start.php"]);
  unset ($result["callmeoften.php"]);
  unset ($result["grid.php"]);
  unset ($result["img.php"]);
  unset ($result["imsform.php"]);
  unset ($result["master.php"]);
  unset ($result["nkit.php"]);
  unset ($result["phpinfo.php"]);
  unset ($result["showhtml.php"]);
  unset ($result["showlog.php"]);
  unset ($result["showlog.php"]);
  unset ($result["showlog.php"]);
  return $result;
}

function DOC_AnalyzeFile ($file)
{
  $key = DFC_Key (N_ReadFile ($file));
  if (DFC_Exists ($key)) {
    return DFC_Read ($key);
  }
  $content = N_ReadFile ($file);
  $lines = explode ("\n", $content);
  $classmode = false;
  $lastfunction = -1;
  for ($i=0; $i<count($lines); $i++) {
    if (preg_match ('|^[ ]*function[ ][ ]*([^ $\n\r]*)[ ]*[(]([^)]*)[)]|', $lines[$i], $matches)) {
      if ($classmode) {
        $specs[$i]["type"] = "methoddef";
        $specs[$i]["class"] = $classname;
      } else {
        $specs[$i]["type"] = "functiondef";
      }
      $specs[$i]["function"] = $matches[1];
      $specs[$i]["params"] = $matches[2];
      $lastfunction = $i;
    }
    if (preg_match ('|^[ ]*class[ ][ ]*([^ $\n\r]*)|', $lines[$i], $matches)) {
      $specs[$i]["type"] = "classdef";
      $specs[$i]["name"] = $classname = $matches[1];
      $classmode = true;
    }
    if (preg_match ('|//[ ]*HD:[ ][ ]*classend|', $lines[$i], $matches)) {
      $specs[$i]["type"] = "classend";
      $classmode = false;
    } else if (preg_match ('|//[ ]*HD:[ ][ ]*hidefunction|', $lines[$i], $matches)) {
      unset ($specs[$lastfunction]);
    } else if (preg_match ('|//[ ]*HD:[ ]*(.*)|', $lines[$i], $matches)) {
      if ($lastfunction==-1) {
        $specs["desc"] = $matches[1];
      } else {
        if ($specs[$lastfunction]["desc"]) {
          $specs[$lastfunction]["desc2"] .= $matches[1]."<br>";
        } else {
          $specs[$lastfunction]["desc"] = $matches[1];
        }
      }
    }
  }
  if ($classmode) N_DIE ("Class without matching // HD: classend ($file).");
  return DFC_Write ($key, $specs);
}

function DOC_Generate ($all=true)
// HD: Generate OpenIMS function documentation.
{
  $files = DOC_AllFIles ();
  foreach ($files as $filename => $file) {
    $specs = DOC_AnalyzeFile ($file);
    if ($specs["desc"] || $all) {
      echo "<br>";
      $title = "<font face=\"arial\" size=\"3\"><u><b>$filename";
      if ($specs["desc"]) $title .= " - ".$specs["desc"];
      $title .= "</b></u><font>";
      echo $title."<br>";
      foreach ($specs as $i => $spec) {
        if ($spec["type"]=="methoddef") { 
          if ($spec["desc"] || $all) {
            echo "&nbsp;&nbsp;&nbsp;";
            echo "<font face=\"arial\" size=\"2\"><b>".$spec["class"]."::".$spec["function"]."</b> (".$spec["params"].")";
            if ($spec["desc"]) echo " - ".$spec["desc"];
            if ($spec["desc2"]) {
              $form = array();
              $fulldesc = $title."<br><br>";
              $fulldesc .= "<font face=\"arial\" size=\"2\"><b>".$spec["class"]."::".$spec["function"]."</b> (".$spec["params"].")";
              if ($spec["desc"]) $fulldesc .= " - ".$spec["desc"]."<br><br>";
              $fulldesc .= "<font face=\"courier\">".$spec["desc2"];
              $form["formtemplate"] = $fulldesc;
              echo " <a href=\"".FORMS_URL ($form)."\"><b>?</b></a>";
            }
            echo "</font><br>";
          }
        } else if ($spec["type"]=="functiondef") { 
          if ($spec["desc"] || $all) {
            echo "&nbsp;&nbsp;&nbsp;";
            echo "<font face=\"arial\" size=\"2\"><b>".$spec["function"]."</b> (".$spec["params"].")";
            if ($spec["desc"]) echo " - ".$spec["desc"];
            if ($spec["desc2"]) {
              $form = array();
              $fulldesc = $title."<br><br>";
              $fulldesc .= "<font face=\"arial\" size=\"2\"><b>".$spec["function"]."</b> (".$spec["params"].")";
              if ($spec["desc"]) $fulldesc .= " - ".$spec["desc"]."<br><br>";
              $fulldesc .= "<font face=\"courier\">".$spec["desc2"];
              $form["formtemplate"] = $fulldesc;
              echo " <a href=\"".FORMS_URL ($form)."\"><b>?</b></a>";
            }
            echo "</font><br>";
          }
        }
      }
    }
  }
}

?>