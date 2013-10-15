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



include (getenv ("DOCUMENT_ROOT")."/nkit/nkit.php"); 

  if ($ashtml) {

  if ($ashtml=="short") echo N_ReadFile ("html::tmp/logging/$id/short/$date/$guid.txt");
  if ($ashtml=="long") echo N_ReadFile ("html::tmp/logging/$id/long/$date/$guid.txt");

  } else { // if ($ashtml)

  uuse ("tables");

  T_Start ("ims", array ("noheader"=>true));
  echo "<b>".ML("Systeem logboek", "System log")."</b>";
  T_Next();
  echo $id;
  T_NewRow();

  echo "<b>".ML("Datum", "Date")."</b>";
  T_Next();
  echo N_VisualDate (N_BuildDate (substr ($date, 0, 4), substr ($date, 4, 2), substr ($date, 6, 2)));
  T_NewRow();

  echo "<b>".ML("Tijd", "Time")."</b>";
  T_Next();
  echo substr($time, 0, 2).":".substr ($time, 2, 2).":".substr ($time,4,2);
  T_NewRow();

  echo "<b>".ML("Onderwerp", "Subject")."</b>";
  T_Next();
  echo stripcslashes($subject);
  T_NewRow();

  TE_End(); 
  echo "<br>";
  T_Start ("ims", array ("noheader"=>true));


  if (N_FileSize ("html::tmp/logging/$id/short/$date/$guid.txt")) {
    $url = N_MyFullURL()."&ashtml=short";
    echo "<b>".ML("Details (klein)", "Details (short)")."</b>&nbsp;<a href=\"$url\">html</a>";
    T_NewRow();
    echo "<font face=\"courier\">".N_XML2HTML (N_ReadFile ("html::tmp/logging/$id/short/$date/$guid.txt"));
    T_NewRow();    
  }

  TE_End(); 
  echo "<br>";
  T_Start ("ims", array ("noheader"=>true));

  if (N_FileSize ("html::tmp/logging/$id/long/$date/$guid.txt")) {
    $url = N_MyFullURL()."&ashtml=long";
    echo "<b>".ML("Details (groot)", "Details (long)")."</b>&nbsp;<a href=\"$url\">html</a>";
    T_NewRow();
    echo "<font face=\"courier\">".N_XML2HTML (N_ReadFile ("html::tmp/logging/$id/long/$date/$guid.txt"));
    T_NewRow();    
  }

  TE_End();

  } // if ($ashtml)

  N_Exit(); 
?>