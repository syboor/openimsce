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



class FTP {
  var $server, $id, $password, $root, $connection;

  function FTP ($server, $id, $password, $root)
  {
    global $ftp_servers;
    $this->server = $server;
    $this->id= $id;
    $this->password= $password;
    $this->root= $root;
    if (!$ftp_servers[$server][$id]) {
      $ftp_servers[$server][$id] = ftp_connect($this->server) or N_Die ("Failed to connect to FTP server ".$this->server);
      ftp_login($ftp_servers[$server][$id], $this->id, $this->password) or N_Die ("Failed to login on FTP server ".$this->server);
    }
    $this->connection = $ftp_servers[$server][$id];
  }

  function dir ($dir="")
  {
    $list = ftp_nlist ($this->connection, $this->root.$dir);
    $result = array();
    foreach ($list as $value) {
      $value = str_replace ($this->root.$dir."/", "", $value);
      $value = str_replace ($this->root.$dir, "", $value);
      if ($value != "." && $value != ".." && !strpos ($value, "_t3mp")) $result[] = $value;      
    }
    return $result;
  }

  function mkdir ($dir) 
  {
    ftp_mkdir ($this->connection, $this->root.$dir);
  }

  function Read ($file)
  {
    $tmp = TMP_DIR()."/".N_GUID();
    $this->Copy2Local ($tmp, $file);
    $ret = N_ReadFile ($tmp);
    return $ret;
  }

  function Write ($file, $content)
  {
    $tmp = TMP_DIR()."/".N_GUID();
    N_WriteFile ($tmp, $content);
    $this->Copy2FTP ($file, $tmp);
  }

  function Delete ($ftpfile)
  {
    ftp_delete ($this->connection, $this->root.$ftpfile);
  }

  function Copy2FTP ($ftpdestinationfile, $localsourcefile)
  {
    N_ErrorHandling (false);
    ftp_delete ($this->connection, $this->root.$ftpdestinationfile);
    N_ErrorHandling (true);
    $result = ftp_put ($this->connection, $this->root.$ftpdestinationfile, N_CleanPath ($localsourcefile), FTP_BINARY);
    if (!$result) N_DIE ("Failed to upload $ftpdestinationfile");
  }

  function Copy2Local ($localdestinationfile, $ftpsourcefile)
  {
    $result = ftp_get ($this->connection, N_CleanPath ($localdestinationfile), $this->root.$ftpsourcefile, FTP_BINARY);
    if (!$result) N_DIE ("Failed to download $ftpsourcefile");
  }
}
// HD: classend

?>