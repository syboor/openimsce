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





//ericd 101110 IMS-12
function ADMINUIF_MaintLog($title, $postcode, $input) {

  $sgn = IMS_SuperGroupName();
  $user = SHIELD_CurrentUser($sgn);
  if (!N_OPENIMSCE()) $gridStatus = GRID_MyStatus();
  $time = time();
  $visualDate = N_VisualDate($time, 1,1);
    
  $info = array();
  $info["title"] = $title;
  $info["user"] = $user;
  $info["when"] = $time;
  $info["visualDate"] = $visualDate;
  $info["postcode"] = $postcode;
  $info["input"] = $input;
  $info["gridStatus"] = $gridStatus;
  
  $class = "maintenance";
  $line = $user.": ".$title.":";
  $shortdesc = "";
  $longdesc = print_r($info,1);
  
  N_Log ($class, $line, $shortdesc, $longdesc, $time);
}


function ADMINUIF_MaintPopupForm($title, $postcode, $input = array()) {
  // Create an "are you sure" popup, with toolbars (so you can see when the popup is still loading,
  // where the output of the postcode will remain visible.
  // If $confirmation = false, just do it (in a popup) without dialog.
  $form = array();
  $form["title"] = $form["input"]["title"] = $title;
  $form["input"]["input"] = $input;

  //ericd 101110 IMS-12
  $form["input"]["postcode"] = $postcode;

  $form["metaspec"]["fields"]["sure"]["type"] = "list";
  $form["metaspec"]["fields"]["sure"]["values"][ML("nee","no")] = "";
  $form["metaspec"]["fields"]["sure"]["values"][ML("ja", "yes")] = "yes";
  $form["formtemplate"] = '
    <table width=100>
      <tr><td colspan=2><font face="arial" color="#ff0000" size=2><b>'.$title.'</b></font></td></tr>
      <tr><td colspan=2>&nbsp;</td></tr>
      <tr><td><nobr><font face="arial" size=2><b>'.ML("Weet u het zeker?","Are you sure?").'</b></font></nobr></td><td><nobr>[[[sure]]]</nobr></td></tr>
      <tr><td colspan=2>&nbsp;</td></tr>
       <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
    </table>
  ';
  $form["postcode"] = '
    if ($data["sure"] == "yes") {

      //ericd 101110 IMS-12
      uuse("adminuif");
      ADMINUIF_MaintLog($input["title"], $input["postcode"], $input["input"]);
    
      $gotook = "nowhere"; // Allow output of postcode to remain visible.
      echo "<html>";
      echo "<head>";
      uuse("skins");
      echo SKIN_CSS();
      echo "<title>".$input["title"]."</title>";
      echo "</head><body>";
      echo DHTML_EmbedJavascript("window.resizeTo(600, 400);");
      N_Flush(-1);
      $input = $input["input"];
   ' . $postcode . '
      echo "<br>";
      echo "<form><input type=\"button\" value=\"".ML("Sluiten", "Close")."\" onClick=\"window.close()\"></form>";
      echo DHTML_EmbedJavascript("window.scroll(0,100000)");
    } else {
      $gotook = "closeme";
    }
  ';
  global $debugforms;
  $debugforms = true; // Make sure that if something goes wrong, the form is visible, not hidden in the bottom right corner of the screen.
  $url = FORMS_URL($form);
  //$url = str_replace('toolbar=0', 'toolbar=1', $url); // we want a browser toolbar, so that the user can see whether the page is loading / finished
  //$url = str_replace('location=0', 'location=1', $url); // for IE 7, the "Stop" button that shows the page is loading is in the navigation bar, not the toolbar
  return $url;
}



function ADMINUIF_TopLog($id, $lines = 100) {
  /* Show the most recent 100 lines from the OpenIMS log file $id (i.e. "flexedit", "errors" etc.). 
   * Scans older logs files if today's log file doesnt contain enough lines.
   * Most recent lines are shown first.
   */
  $i = 0;
  $daysago = 0;
  while ($i <= $lines) {
    $date = time() - ($daysago * 86400);
    $datestr = N_Date("Ymd", $date);
    $logfile = N_ReadFile("html::/tmp/logging/$id/{$datestr}.log");
    if ($logfile) {
      $all = array_reverse(explode (chr(13).chr(10), $logfile));
      $content .= "<b>" . N_VisualDate($date) . "</b><br/>";
      foreach ($all as $line) {
        if (!$line) continue;
        $guid = substr ($line, 0, 32);
        $time = substr ($line, 33, 6);
        $subject = substr ($line, 40);
        $content .= "<nobr>" . substr($time, 0, 2).":".substr ($time, 2, 2).":".substr ($time,4,2)." ".$subject." ";
        if (N_FileSize ("html::tmp/logging/$id/short/$datestr/$guid.txt") || N_FileSize ("html::tmp/logging/$id/long/$datestr/$guid.txt")) {
          $content .= "<a href=\"/openims/showlog.php?id=$id&date=$datestr&time=$time&guid=$guid&subject=".urlencode($subject)."\" target=\"_blank\">".ML("details", "details")."</a> ";
        }
        $content .= "</nobr><br>";
        $i++;
        if ($i > $lines) break;
      }
    }
    $daysago++;
    if ($daysago > 30) { 
      if ($i) {
        $content .= "<i>" . ML("Geen verdere logregels in de laatste %1 dagen", "No more log entries in the last %1 days", $daysago - 1) . "</i>";
      } else {
        $content .= "<i>" . ML("Geen logregels in de laatste %1 dagen", "No log entries in the last %1 days", $daysago - 1) . "</i>";
      }
      break;
    }
  }
  return $content;
}



?>