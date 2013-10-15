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



/**********************************************************************************************
* For compatability of calender control with html-layouts that are built of nested <div>'s
* containing z-index definitions, the style-definition for the .calender-element in the
* calendar-openims.css file is augmented with one line:
*
* z-index:99
*
* this should work good as the .calendar .combo definition in the .css-file has z-index:100
*
**********************************************************************************************/
  
function JSCAL_Init ()
{
  global $JSCAL_init;
  if (!$JSCAL_init) {
    $JSCAL_init = "yes";
    // IMS_AddHtmlHeader already does deduplication. Custom code may still use $JSCAL_init.
    IMS_AddHtmlHeader('style', array(), N_ReadFile ("html::/openims/libs/jscalendar/calendar-openims.css"));
    IMS_AddHtmlHeader('script', array("src" => "/openims/libs/jscalendar/calendar.js"));
    IMS_AddHtmlHeader('script', array("src" => "/openims/libs/jscalendar/lang/calendar-".ML_GetLanguage().".js"));
    IMS_AddHtmlHeader('script', array("src" => "/openims/libs/jscalendar/calendar-setup.js"));
  }
}
 
function JSCAL_CreateDate ($fieldname, $value) 
{
  $result = JSCAL_Init();

  if (ML_GetLanguage ()=="en") {
    $result .= "<input onchange=\"if (this.value != '') {var d=Date.parseDate(this.value,'%b %e %Y');this.value=d.print('%b %e %Y');}\" size=10 type=\"text\" id=\"jscalid_$fieldname\" name=\"$fieldname\" value=\"";
  } else {
    $result .= "<input onchange=\"if (this.value != '') {var d=Date.parseDate(this.value,'%e %b %Y');this.value=d.print('%e %b %Y');}\" size=10 type=\"text\" id=\"jscalid_$fieldname\" name=\"$fieldname\" value=\"";
  }
  if ($value) {
    $result .= N_Date ("j M Y", "M j Y", $value);
  }
  $result .= "\">";
  $result .= "&nbsp;<img alt=\"".ML("Kalender","Calender")."\" title=\"".ML("Kies datum","Choose date")."\" style=\"cursor : hand;\" id=\"trigger_$fieldname\" border=0 margin-bottom:0 src=\"/openims/calender.gif\">";
  $result .= '<script type="text/javascript">';
  $result .= "Calendar.setup({\n";
  if (ML_GetLanguage ()=="en") {
    $result .= "ifFormat:\"%b %e %Y\",";
    $result .= "daFormat:\"%b %e %Y\",";
    $result .= "firstDay:0,";      
    $result .= "timeFormat:12,";   
  } else {
    $result .= "ifFormat:\"%e %b %Y\",";
    $result .= "daFormat:\"%e %b %Y\",";
    $result .= "firstDay:1,";      
    $result .= "timeFormat:24,";   
  }
  $result .= "showOthers:true,";
  $result .= "step:1,";
  $result .= "inputField:\"jscalid_$fieldname\",";
  $result .= "button:\"trigger_$fieldname\",";
  $result .= "date:\"2004/03/31\"";
  $result .= '});';
  $result .= '</script>';
  return $result;
}

function JSCAL_CreateDateTime ($fieldname, $value) 
{
  $result = JSCAL_Init();
  if (ML_GetLanguage ()=="en") { // qqq
    $result .= "<input onchange=\"var d=Date.parseDate(this.value,'%b %e %Y %H:%M');this.value=d.print('%b %e %Y %H:%M');\" size=18 type=\"text\" id=\"jscalid_$fieldname\" name=\"$fieldname\" value=\"";
  } else {
    $result .= "<input onchange=\"var d=Date.parseDate(this.value,'%e %b %Y %H:%M');this.value=d.print('%e %b %Y %H:%M');\" size=16 type=\"text\" id=\"jscalid_$fieldname\" name=\"$fieldname\" value=\"";
  }
  if ($value) {
    $result .= N_Date ("j M Y H:i", "M j Y H:i", $value);
  }
  $result .= "\">";  
  $result .= "&nbsp;<img alt=\"".ML("Kalender","Calender")."\" title=\"".ML("Kies datum en tijd","Choose date and time")."\" style=\"cursor : hand;\" id=\"trigger_$fieldname\" border=0 margin-bottom:0 src=\"/openims/calender.gif\">";
  $result .= '<script type="text/javascript">';
  $result .= 'Calendar.setup({';
  if (ML_GetLanguage ()=="en") {
    $result .= "ifFormat:\"%b %e %Y %H:%M\",";
    $result .= "daFormat:\"%b %e %Y %H:%M\",";
    $result .= "firstDay:0,";   
    $result .= "timeFormat:12,";   
  } else {
    $result .= "ifFormat:\"%e %b %Y %H:%M\",";
    $result .= "daFormat:\"%e %b %Y %H:%M\",";
    $result .= "firstDay:1,";      
    $result .= "timeFormat:24,";   
  }
  $result .= "showOthers:true,";
  $result .= "step:1,";
  $result .= "showsTime:true,";
  $result .= "inputField:\"jscalid_$fieldname\",";
  $result .= "button:\"trigger_$fieldname\"";
  $result .= '});';
  $result .= '</script>';
  return $result;
}

function JSCAL_Decode ($rawvalue)
{
  if (!$rawvalue) {
    return "";
  }
  if (ML_GetLanguage ()=="en") {
    return N_SafeStrToTime ($rawvalue);
  } elseif (ML_GetLanguage()=="nl") {    
    $rawvalue = strtolower ($rawvalue);
    $rawvalue = str_replace ("mrt" , "mar", $rawvalue);
    $rawvalue = str_replace ("mei" , "may", $rawvalue);
    $rawvalue = str_replace ("okt" , "oct", $rawvalue);
    return N_SafeStrToTime ($rawvalue);
  } elseif (ML_GetLanguage()=="fr") {
    uuse("search");
    $rawvalue = strtolower(SEARCH_RemoveAccents($rawvalue));
    $rawvalue = str_replace ("fev" , "feb", $rawvalue);
    $rawvalue = str_replace ("avr" , "apr", $rawvalue);
    $rawvalue = str_replace ("mai" , "may", $rawvalue);
    $rawvalue = str_replace ("juin" , "jun", $rawvalue);
    $rawvalue = str_replace ("juil" , "jul", $rawvalue);
    $rawvalue = str_replace ("aout" , "aug", $rawvalue);
    $rawvalue = str_replace ("aou" , "aug", $rawvalue);
    return N_SafeStrToTime ($rawvalue);
  } elseif (ML_GetLanguage()=="de") {
    uuse("search");
    $rawvalue = strtolower(SEARCH_RemoveAccents($rawvalue));
    $rawvalue = str_replace ("mai" , "may", $rawvalue);
    $rawvalue = str_replace ("dez" , "dec", $rawvalue);
    return N_SafeStrToTime ($rawvalue);
  }
}

function JSCAL_Test ()
{
  global $test1, $test2;
  ML_SetLanguage ("en");
//  ML_SetLanguage ("nl");
  echo "<form method=\"post\" action=\"/private/eval.php\">";
  global $code;
  echo "<input name=\"code\" type=\"hidden\" value=\"".htmlentities($code)."\">";
  echo "Test DATE: ";
  echo JSCAL_CreateDate ("test1", JSCAL_Decode ($test1))."<br>";
  echo "Test DATE + TIME: ";
  echo JSCAL_CreateDateTime ("test2", JSCAL_Decode ($test2))."<br>";
  echo "<input type=\"submit\" value=\"OK\">";
  echo "<form><br>";
  echo "LANG: ".ML_GetLanguage ()."<br>";
  echo "RAW VALUE: ".$test1."<br>";
  echo "DECODED VALUE (raw format): ".JSCAL_Decode ($test1)."<br>";  
  echo "DECODED VALUE (time format): ".N_VisualDate (JSCAL_Decode ($test1), true, true)."<br>";  
  echo "RAW VALUE: ".$test2."<br>";
  echo "DECODED VALUE (raw format): ".JSCAL_Decode ($test2)."<br>";  
  echo "DECODED VALUE (time format): ".N_VisualDate (JSCAL_Decode ($test2), true, true)."<br>";  
}

?>