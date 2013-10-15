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


include (getenv("DOCUMENT_ROOT") . "/nkit/nkit.php");

  uuse ("shield");
  $thespecs = SHIELD_Decode ($_REQUEST["specs"]); // LF: global $specs -> $_REQUEST["specs"] because $specs was used as a temp variable in one of my many siteconfig's and it took me ages to find it.
  $readonly = false;

   // enable or disable linenumbers with an extra button
    $enablevalue = $_COOKIE['ims_linenumbers'];
    $enable_linenumbers = ($enablevalue=="yes");

    if ($enable_linenumbers) {
      $newvalue = "no";
      $newtitle = "OFF";
      $webpath = preg_replace('/(^.+?\/)[^\/]*$/', "$1", $_SERVER["SCRIPT_NAME"]);
      $postprocess = '
<script type="text/javascript" src="'.$weboath.'linenumbers.js"></script>
<script type="text/javascript">
inittextarea("codetextarea", 1, 3000);
</script>
  ';

    } else {
      $postprocess = '';
      $newvalue = "yes";
      $newtitle = "ON";
    }
    $togglelinenumbers = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="togglelinenumbers" value="Set linenumbers '.$newtitle.'">';
    if ($_POST['togglelinenumbers']) {

      $cookietimeout = time()+365*86400;
      header ("HTTP/1.1 200");
      header ("Status: 200");
      setcookie ('ims_linenumbers', $newvalue, $cookietimeout, "/");
      // This is really fuzzy: without extra parameter the cookie is not read out correctly :-(
      $url = $_SERVER["REQUEST_URI"];
      $i = strpos($url, "&cookie=");
      if ($i > 0) $url = substr($url,0,$i);
      ///
      N_Redirect($url. "&cookie=$enablevalue");
      exit;
    }

    // force output
    echo " ";
    flush();

    $body = str_replace(chr(160), chr(32), stripcslashes($body));
    $body = str_replace(chr(13).chr(10), chr(10), $body);
    $body = str_replace(chr(13), "", $body);
    $body = str_replace(chr(10), chr(13).chr(10), $body);

    if ($action=="save") {
      $content = $body;
      $input = $thespecs["input"];
      eval ($thespecs["save"]);
      
    } else {
      $input = $thespecs["input"];
      eval ($thespecs["load"]);
    }
    $content = htmlentities ($content);
    $options = array();
    $closebutton = '<input type="button" name="close" onclick="javascript:window.close()" value="close">';
    $options["togglelinenumbers"] = $togglelinenumbers;
    if ($readonly) {
      $options["textareasavebutton"] = $closebutton;
      $options["textareaextra"] = 'readonly="true"';
    } else {
      $options["textareasavebutton"] = '<input type="hidden" name="action" value="save">'."\n".
                                       '<input type="submit" name="save" value="save">&#160;' . $closebutton;
    }

$out = '
<html><head>
<title>'.$thespecs["title"].'</title>
<style type="text/css">
<!--
a:link{color: #000366; text-decoration: underline; font-family: Arial, Helvetica, sans-serif; font-size: 11px;}
a:visited{color: #000366; text-decoration: underline; font-family: Arial, Helvetica, sans-serif; font-size: 11px;}
a:hover{color: #FF0000; text-decoration: underline; font-family: Arial, Helvetica, sans-serif; font-size: 11px;}
#lineObj, textarea#codetextarea { font-family: monospace; font-size: 13px; 
-->
</style>
<!--script type="text/javascript" src="/openims/javascript/linenumbers.js"></script-->

<body style="margin: 0; border: 0; overflow: hidden">
  <form name="edit" method="post" action="">
    <input type="hidden" name="specs" value="'. $_REQUEST["specs"] . '">
    <table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr height="24" style="background-color: threedface">
        <td style="font: messagebox">
'.$options["textareasavebutton"].'
'.$options["textareaextrabutton"].'
          <font face="arial" size="2">
          <b>Direct EDIT</b>&nbsp;&nbsp;&nbsp;
          Element: <b>'.$thespecs["title"] . '</b>&nbsp;&nbsp;&nbsp;
          '. ($action=="save" ? "Last save: <b>".date ("r", time())."</b>" : "") .'
          &nbsp;</font>
'.$options["togglelinenumbers"].'
        </td>
      </tr>
      <tr><td colspan="2">
        <textarea id="codetextarea" wrap="off" '.$options["textareaextra"].' name="body" style="height: 100%; width: 100%;">'. $content . '</textarea>
      </td></tr>
    </table>
  </form>
'.$postprocess.'
</body>
';
echo $out;
N_Exit();
?>