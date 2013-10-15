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



function CLEAN_Up ($manual=false) // true => manual, false => once every hour
{
  if ($manual) { echo "Cleaning security data<br>"; N_Flush(); }
  SHIELD_Cleanup ();

  if ($manual) { echo "Cleaning TMP directory<br>"; N_Flush(); }
  TMP_CleanUp ();



  if ($manual) { echo "Cleaning obsolete locks<br>"; N_Flush(); }
  CLEAN_ObsoleteLocks();

  if ($manual) { echo "Cleaning locks<br>"; N_Flush(); }
  CLEAN_Locks();

  if ($manual) { echo "Cleaning invalid keys<br>"; N_Flush(); }
  CLEAN_InvalidKeys();

  if ($manual) { echo "Clean up completed<br>"; N_Flush(); }

  // work around MS-Word trying to access  /_vti_bin/shtml.exe/_vti_rpc in combination with FastCGI
  $antivti  = "<Files .htaccess>".chr(13).chr(10);
  $antivti .= "  order allow,deny".chr(13).chr(10);
  $antivti .= "  deny from all".chr(13).chr(10);
  $antivti .= "</Files>".chr(13).chr(10);
  $antivti .= "IndexIgnore *".chr(13).chr(10);
  $antivti .= "Options -Indexes".chr(13).chr(10);
  $antivti .= "ErrorDocument 404 default".chr(13).chr(10);
  N_WriteFile ("html::/_vti_bin/.htaccess", $antivti);
  N_WriteFile ("html::/metabase/.htaccess", "deny from all".chr(13).chr(10)); // prevent unauthorized snooping by Liesbeth
  N_WriteFile ("html::/tmp/logging/.htaccess", "deny from all".chr(13).chr(10)); 
  N_WriteFile ("html::/tmp/flexcache/.htaccess", "deny from all".chr(13).chr(10)); 
  N_WriteFile ("html::/tmp/myconfig/.htaccess", "deny from all".chr(13).chr(10)); 

  $antiexec = '<Files ~ "\.(php|cgi|fcgi)$">'.chr(13).chr(10);
  $antiexec .= '    Order deny,allow'.chr(13).chr(10);
  $antiexec .= '    Deny from all'.chr(13).chr(10);
  $antiexec .= '    ErrorDocument 403 default'.chr(13).chr(10);
  $antiexec .= '</Files>'.chr(13).chr(10);
  $dirs = glob(N_CleanPath("html::*_sites")); // Locations where users can upload files (and choose the file name). Dont worry, you can still include files (such as mix.php).
  foreach ($dirs as $dir) {
    if (is_dir($dir)) {
      $file = $dir."/.htaccess";
      if (!N_FileExists($file)) N_WriteFile($file, $antiexec);
    }
  }


}

function CLEAN_InvalidKeys()
{
  // clean phantom object with "" as key

  $lijst = MB_MultiQuery("shield_supergroups", "");

  foreach($lijst as $supergroupname) {
    $object = MB_Ref("ims_" . $supergroupname . "_objects", "");
    if($object) {
      MB_Delete ("ims_" . $supergroupname . "_objects","");
    }
  }
}

function CLEAN_Locks()
{
  for ($i=0; $i<10; $i++) {
    $dir = N_CleanPath ("html::tmp/locks2/".substr (N_GUID(), 0, 3));
    if (file_exists($dir)) {
      $d = dir($dir);
      if ($d) while (false !== ($entry = $d->read())) {
        if ($entry=="." || $entry=="..") {
        } else if (is_file ($dir."/".$entry)) {
          if (filemtime ($dir."/".$entry) < (time()-2*24*3600)) {
            unlink ($dir."/".$entry);
          }
        }
      }
    }
  }
}

function CLEAN_ObsoleteLocks()
{
  N_ErrorHandling (false);
  $all = opendir(N_CleanPath ("html::tmp/locks"));
  $limit=2500;
  while ($limit-- && $file=readdir($all)) {
    if ($file!="." && $file!="..") {
      unlink (N_CleanPath ("html::tmp/locks/$file"));
    }
  }
  N_ErrorHandling (true);
}



?>