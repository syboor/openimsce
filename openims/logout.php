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
  uuse("ims"); 
  uuse("shield");  
  uuse("forms"); 

  global $myconfig;
  $sgn = IMS_SuperGroupName();
  
  if ($myconfig[$sgn]["cookielogin"] != "yes") die("This logout mechanism is not enabled");

  global $option, $delay, $cancel, $genc;
  $settings = $myconfig[$sgn]["cookieloginsettings"];
  $goto = "";
  if ($genc) $goto = SHIELD_Decode($genc);

  $user_id = SHIELD_CurrentUser($sgn);
  $userobj = MB_Ref("shield_".$sgn."_users", $user_id);

  if ($cancel) {
    // Redirect naar de pagina waar de gebruiker vandaan kwam, anders naar homepagina
    $location = $goto;
    if (!$location) $location = N_CurrentProtocol() . getenv('HTTP_HOST') . '/';
    N_Redirect($location, 302);
  } elseif ($option) {
    SHIELD_LogOff($goto, $option);
  } elseif (!$delay && ($settings["logoutwherechoice"] != "yes" || $myconfig[$sgn]["allowautologon"] == "no" || !$userobj["permsessionkey"])) {
    // No option chosen, but only one option ("here") possible. Unless "delay=bla" in url, log off immediately
    SHIELD_LogOff($goto, "here");
  } else {
    // Show a form so that the user can choose how to log off ("here" or "everywhere")

    // Get custom template (specified in $myconfig)
    $template = "";
    // It is not sufficient to check with IMS_Preview() that someone has the preview cookie,
    // he must also be logged in and have sufficient rights.
    $mypreview = IMS_Preview() && SHIELD_CurrentUser($sgn) && SHIELD_CurrentUser($sgn) != "unknown" && SHIELD_HasGlobalRight($sgn, "preview"); // Very important to check SHIELD_CurrentUser, because otherwise SHIELD_HasGlobalRight may force a logon and trigger the "You do not have sufficient rights to view the login page" error.
    if ($settings["logouttemplate"]) {
      if ($mypreview) {
        $templatelocation = "/$sgn/preview/templates/" . $settings["logouttemplate"] . "/";
      } else {
        $templatelocation = "/$sgn/templates/" . $settings["logouttemplate"] . "/";
      }     
      $template = IMS_CleanupTags(N_ReadFile("html::".$templatelocation."template.html"));
    }
    // Get default template
    if (!$template) {
      $template = IMS_CleanupTags('
<html>
  <head>
    <title>Logout</title>
    <style>
        body, div, p, th, td, li, dd {
          font-family: Arial, Helvetica, sans-serif;
          font-size: 13px;
        }
    </style>
  </head>
  <body>
    [[[content]]]
  </body>
</html>');
    }

    if ($settings["logouttemplate"]) {
      $template = IMS_TagReplace($template, "coolbar", "", 1);

      
      $template = IMS_TagReplace($template, "longtitle", "Logout", 1);
      $template = IMS_TagReplace($template, "shorttitle", "Logout", 1);
      $template = IMS_TagReplace($template, "keywords", "", 1);
    }

    $logouttimeout = intval($settings["logouttimeout"]);
    if (!$logouttimeout) $logouttimeout = 120;

    $content .= '
      <form action="'.N_MyFullURL().'" method="post" id="logoutform" name="logoutform" class="defaultform">
      <div>
        <input type="hidden" name="genc" value="'.$genc.'" />
        <label for="option">' . ML("Log mij uit", "Log me off") . '</label><br/>
        <input type="radio" class="radio" name="option" value="here" checked>'. ML("Op deze computer", "On this computer") . '<br/>
        ' . (($myconfig[$sgn]["allowautologon"] != "no" && $userobj["permsessionkey"])
             ? ('<input type="radio" class="radio" name="option" value="everywhere">'. ML("Overal (alle computers waarop ik ingelogd ben)", "Everywhere (all computers on which I am logged in)") . '<br/>')
             : MB_Fetch("shield_".$sgn."_users", SHIELD_CurrentUser($sgn), "permsessionkey")
        ) . '
        <br/>
        <input type="submit" id="logoutform_submitbutton" name="submit" value="' . ML("Uitloggen", "Logoff") . '" />
        <input type="submit" id="logoutform_cancelbutton" name="cancel" value="' . ML("Annuleren", "Cancel") . '"  />
      </div>
      <div>' . ML("U wordt automatisch uitgelogd over", "You will be automatically logged off in") . " " . '
              <span id="logofftimer">'.$logouttimeout.'</span> ' . ML("seconden", "seconds") .'
      </div>
    ';
  }

  // Automatic logoff after 120 seconds (in case the user leaves the computer because he thinks he has already logged out)
  $url = N_MyBareUrl() . "?option=here&genc=$genc";
  $script = '
    var timer = '.$logouttimeout.';
    setTimeout("location.replace(\''.$url.'\');", timer * 1000);
    setInterval("timer--; document.getElementById(\'logofftimer\').innerHTML = timer;", 1000);
  '; 
  $content .= DHTML_EmbedJavascript($script);

  $template = IMS_Relocate ($template, $templatelocation);
  $content = str_replace ("[[[content:]]]", $content, $template); 
  echo $content;

  N_Exit();
?>