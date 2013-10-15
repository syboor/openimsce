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
  uuse("tmp");

  global $myconfig, $genc, $rememberme, $r, $testcookiesupport;
  $sgn = IMS_SuperGroupName();

  if ($myconfig[$sgn]["cookielogin"] != "yes") die("This login mechanism is not enabled");
  $message = "";
  $content = "";
  $settings = $myconfig[$sgn]["cookieloginsettings"];
  $goto = "";
  if ($genc) $goto = SHIELD_Decode($genc);

  if ($r == "loggedout") $message = ML("U bent uitgelogd", "You have been logged off");
  if ($r == "interactive") $message = ML("Voor deze functionaliteit moet u opnieuw inloggen", "To use this functionality, you must login again");
  if ($r == "expired") {
    $message = $myconfig[$sgn]["cookieloginsettings"]["timeoutmessage"];
    if (!$message) $message = ML('Uw sessie is verlopen. U moet opnieuw inloggen.', 'Your session has expired. You need to login again.');
  }
  if (!$r && SHIELD_CurrentUser($sgn) != "unknown") $message = ML('U bent ingelogd als', 'You are logged in as') . ' ' . SHIELD_CurrentUser($sgn);

  if ($testcookiesupport) {
    if ($_COOKIE) {
      header("Location: $goto", true, 302);
      N_Exit(); 
      die();
    } else {
      if (strpos(getenv("HTTP_HOST"), "_") !== false) {
        $message = ML('Uw browser heeft cookies uitgeschakeld, waarschijnlijk vanwege een underscore in de domeinnaam. Hierdoor kunt u niet inloggen. Neem contact op met de DNS beheerder.', 'Your browser has disabled cookies, probably because of an underscore in the domain name. As a result, you can not log in. Please contact your DNS administrator.');
      } else {
        $message = ML('Om in te kunnen loggen dient u cookies toe te staan in uw browser', 'To log in, you need to enable cookies in your browser');
      }
    }
  }

  if ($_POST["username"]) { // Someone tried to login (only allow POST, dont allow bookmarking with GET)
    $username = stripslashes(N_UTF2HTML($_POST["username"]));
    $password = stripslashes(N_UTF2HTML($_POST["password"]));

    $failmessage = SHIELD_CookieLogon_ProcessInteractiveLogon($sgn, $username, $password, $rememberme, $goto);

    // if we reach this point, logon was unsuccesful
    if ($failmessage) $message = $failmessage;
  }

  // Show the login form
  if (($myconfig[$sgn]["cookieloginsettings"]["loginrequireshttps"] == "yes" || $myconfig[$sgn]["cookieloginsettings"]["cookieonlyonhttps"] == "yes") &&
                 strtolower(substr(N_MyFullURL(), 0, 5)) != 'https') {
    $goto = str_replace("http:","https:",N_MyFullURL());
    if (headers_sent()) {
      N_Redirect($goto);
    } else {
      header("Location: $goto", true, 302);
      N_Exit();
      die();
    }
  }

  // Get custom template (specified in $myconfig)
  $template = "";
  // It is not sufficient to check with IMS_Preview() that someone has the preview cookie,
  // he must also be logged in and have sufficient rights.
  $mypreview = IMS_Preview(); 
  if ($settings["logintemplate"]) {
    if ($mypreview) {
      $templatelocation = "/$sgn/preview/templates/" . $settings["logintemplate"] . "/";
    } else {
      $templatelocation = "/$sgn/templates/" . $settings["logintemplate"] . "/";
    }     
    $template = IMS_CleanupTags(N_ReadFile("html::".$templatelocation."template.html"));
  }

  // Get default template
  if (!$template) {
    $template = IMS_CleanupTags('
<html>
  <head>
    <title>Login</title>
    <style>
        body, div, p, th, td, li, dd {
          font-family: Arial, Helvetica, sans-serif;
          font-size: 13px;
        }
        #loginform_message {
          color: #ff0000;
        }
    </style>
  </head>
  <body>
    [[[content]]]
  </body>
</html>');
  }

  if ($settings["logintemplate"]) {
    if ($mypreview) {
      $templateobject = MB_Ref("ims_".$sgn."_templates", $settings["logintemplate"]);
      $templatemessage = ML('U kunt geen eigenschappen van deze pagina bewerken', 'You can not edit the properties of this page') . '.<br/>' .
                         ML('De CMS template voor deze pagina is', 'The CMS template for this page is') . ': ' . 
                         $templateobject["name"] . ' (' . $settings['logintemplate'] . ')' . '.<br/>' .
                         ML('U kijkt naar de preview versie van de template', 'You are looking at the preview version of the template') . '.' . '<hr/>';
      $template = IMS_TagReplace($template, "coolbar", $templatemessage, 1);
    } else {
      $template = IMS_TagReplace($template, "coolbar", "", 1);
    }

    

    $template = IMS_TagReplace($template, "longtitle", "Login", 1);
    $template = IMS_TagReplace($template, "shorttitle", "Login", 1);
    $template = IMS_TagReplace($template, "keywords", "", 1);
  }

  // Create content
  if ($message) $content .= '<p id="loginform_message">' . $message . '</p>';
  $content .= '
    <form action="'.N_MyBareURL().'" method="post" id="loginform" name="loginform">
    <input type="hidden" name="genc" value="'.$genc.'" />
    <input type="hidden" name="enforceutf8" value="&#307;">
    <table>
      <tr><td><label for="username">' . ML("Gebruikersnaam", "Username") . '</label></td>
          <td><input type="text" id="loginform_username" name="username" value="' . N_HtmlEntities($username) . '" /></td></tr>
      <tr><td><label for="password">' . ML("Wachtwoord", "Password") . '</label></td>
          <td><input type="password" id="loginform_password" name="password" /></td></tr>
  ';
  if ($myconfig[$sgn]["allowautologon"] != "no") {
    $content .= '
      <tr><td><label for="rememberme">'.ML("Onthoud mij","Remember me").'</label></td>
          <td><input type="checkbox" id="loginform_rememberme" name="rememberme" class="checkbox" /></td></tr>
    ';
  }
  $content .= '
      <tr><td colspan=2>&nbsp;</td></tr>
      <tr><td>&nbsp;</td><td><input type="submit" id="loginform_submitbutton" value="'.ML("Inloggen","Log on").'" /></td></tr>
    </table>
    </form>
    <script type="text/javascript">
      document.getElementById("loginform_username").focus();
    </script>
  ';

  $template = IMS_Relocate ($template, $templatelocation);
  $content = str_replace ("[[[content:]]]", $content, $template); 

  echo $content;

  N_Exit();

/* Features, caveats etc.

"Logoff everywhere" doesnt work instantaneously. 
If there users on other computers with an active browser session using the same account,
they will be logged off when they quit / restart their browser, or in any case (if the user 
somehow prevents the browser from cleaning up the cookie) after at most 12 hours.

  
$myconfig[$sgn]["cookielogin"] = "yes";
$myconfig[$sgn]["cookieloginsettings"] = array(
  "inactivetimeout" => 600, 
     // Default 0 = no timeout. 
     // If this is non-0, it will cause a write action on *every* page request (performance!)
  "inactivegraceperiod" => 600,
     // Default 600, only relevant if inactivetimeout is non-0
     // If a user has been inactive for longer than the inactivetimeout, 
     // but less than inactivetimeout + inactivegraceperiod,
     // a request with POST will be accepted (and this reset the timeout timer), 
     // but a request without POST will be redirected to the login page.
  "timeout" => 12*3600,
     // Default 12*3600. Values larger that the lifespan of temporary objects (about 2 days?) won't work.
     // Server-side enforced limit on browser session duration.
     // NB: if a user has chosen "rememberme", he will (transparently) start a "new" session.
  "logintemplate" => $template_id,
     // Default: none. Only relevant if "loginpageurl" is not set.
     // ID of CMS template to replace the default HTML structure of the login-page.
     // The template should contain [[[content]]] somewhere. Other fields and CMS component 
     // included in the template may or may not work, depending on what context the components require.
  "loginpageurl" => "/testnet_demo_nl/d91d66ee866bd14b63234e1287149e5a.php",
    // Use with EXTREME caution, and only enable after extensive testing of your login page.
    // The url MUST start with a slash. NO protocol or hostname, but no relative path either.
    // If the login page doesnt work (you forgot to publish it, someone changed the workflow
    // etc.), go to /openims/login.php to log in and fix your mistakes.
  "logoutwherechoice" => "yes",
    // Default: false -> when accessing the logout page, the user will immediately be logged out,
    //   without any dialogs.
    // If "yes" and the user has used the "Remember me" option, the user will see a form 
    //   in which he can choose between logging out locally (this computer) or logging
    //   out everywhere (all computers on which the user is logged in)
  "logouttimeout" = 15, 
    // Default: 15. Only relevant if "logoutwherechoice" is enabled.
    // If the user goes to the logout page but doesnt proceed with the dialog, 
    // autologout after 15 seconds (javascript)
  "logouttemplate" => $template_id,
    // Only relevant if "logoutwherechoice" is enabled.
  "logoutpageurl" => $url,
  "loginrequireshttps" => "yes",
    // Default: false.
    // If "yes", the login page will require https. As of 20100117, the authentication cookie may only be sent over https.
  "logging" => "yes",
    // If "yes", log all login attemps to the OpenIMS log "login"
  "trackip" => "yes",
    // Default: false.
    // Track a user's IP address and require the user to login again if the IP address changes. 
    // This functionality will not work if "Remember me" is being used.
  "cookieonlyonhttps" => "yes",
    // Default: false.
    // As of 20100117, this has the same effect as "loginrequireshttps"
  "failedusernamemessage" = 'Gebruikersnaam onjuist',
    // Default: ML('Gebruikersnaam of wachtwoord onjuist', 'Wrong username or password')
    // Alternative (example): "No such user"
  "failedpasswordmessage" = 'Wachtwoord onjuist',
    // Default: ML('Gebruikersnaam of wachtwoord onjuist', 'Wrong username or password')
  "timeoutmessage" => 'Uw sessie is verlopen. U moet opnieuw inloggen.';
  "defaultpageafterlogin" => '/openims/openims.php?mode=dms', 
    // Where to send the user after logging in, in case the login-page was called
    // without a $goto-parameter (the user typed in the /openims/login.php url)
  "defaultpageafterlogout" => '/openims/login.php?r=loggedout', // waar de gebruiker heen moet na het uitloggen (tenzij in de url parameters iets anders is aangegeven,
    // Where to send the user after logging out, in case the logout-page was called
    // without a $goto-parameter (the user typed in the /openims/logout.php url)
);
$myconfig[$sgn]["allowautologon"] = "yes"; 
  // Default: "yes"
  // Settings this to "no" will disable the "Remember me" checkbox on the login page.


*/

?>