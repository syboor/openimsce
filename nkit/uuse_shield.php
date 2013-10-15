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



global $objectrights, $globalrights, $workflowstagerights, $globalandlocalrights; 
  
uuse ("tree");
uuse ("ims");
uuse ("multilang");
uuse ("tmp");
uuse ("events");

// global rights

// global rights which are can differ for each securityspace (folder)
$globalandlocalrights["moveto"]            = "yes";
$globalandlocalrights["newdoc"]            = "yes";
$globalandlocalrights["folders"]           = "yes";
$globalandlocalrights["upload"]            = "yes";
$globalandlocalrights["connectmanagement"] = "yes";
$globalandlocalrights["system"]            = "yes";  // determined on a case by case basis

// to move a folder between securityspaces the "folders" right is needed in both spaces

uuse("flex");

function SHIELD_InitDescriptions() // should be called AFTER determination of the current user
{
  global $globalrights, $globalandlocalrights, $objectrights, $workflowstagerights, $processrights, $systemlocal, $myconfig;

  // global (not file related) rights
  $globalrights["preview"]            = ML("Recht om interne versie document/webpagina/record te bekijken","Right to view preview versions of document/webpage/record"); // cms + dms + bpms
  $globalrights["newdoc"]             = ML("Recht om nieuwe documenten te maken","Right to create a new document"); // dms
  $globalrights["moveto"]             = ML("Recht om bestanden naar de huidige folder te verplaatsen","Right to move files to the current folder"); // dms
  $globalrights["upload"]             = ML("Recht om nieuwe bestanden te uploaden","Right to upload new files"); // dms
  $globalrights["folders"]            = ML("Recht om folders te beheren","Right to manage folders"); // dms
  
  $globalrights["doctemplateedit"]    = ML("Recht om document templates te maken of te verwijderen","Right to add or remove document templates"); // dms
  $globalrights["webtemplateedit"]    = ML("Recht om webtemplates aan te passen","Right to alter webtemplates"); // cms
  $globalrights["webtemplatepublish"] = ML("Recht om webtemplates te publiceren","Right to publish webtemplates"); // cms
  
  $globalrights["system"]             = ML("Alle rechten, inclusief beheer van rechten","All rights including the right to manage rights"); // cms + dms
  $systemlocal                        = ML("Beheer van rechten","Manage rights"); // dms


  // workflow generic rights
  $objectrights["view"]               = ML("Recht om alle versies van een document/webpagina te bekijken","Right to view all versions of a document/webpage"); // cms + dms
  $objectrights["viewpub"]            = ML("Recht om de laatst goedgekeurde versie van een document te bekijken","Right to view the last approve version of a document"); // dms
  $objectrights["docviewhistory"]     = ML("Recht om de leeshistorie van een document te bekijken","Right to view the view history of a document"); // dms
  $objectrights["delete"]             = ML("Recht om document/webpagina te verwijderen","Right to delete document/webpage"); // cms + dms
  $objectrights["deleteconcept"]      = ML("Recht om concept versie van het document/webpagina te verwijderen","Right to delete document/webpage"); // dms
  $objectrights["move"]               = ML("Recht om document/webpagina te verplaatsen","Right to move document/webpage"); // cms + dms
    // to move a file between securityspaces the "move" right is needed in both spaces
  $objectrights["newpage"]            = ML("Recht om onder huidige webpagina nieuwe webpagina aan te maken","Rigth to create new webpage below current one"); // cms
  $objectrights["reassign"]           = ML("Recht om een toewijzing aan te passen (heralloceren)","Right to reassign"); // dms
  $objectrights["assignthisworkflow"] = ML("Recht om deze workflow toe te kennen","Right to assign this workflow"); // cms + dms
  $objectrights["removethisworkflow"] = ML("Recht om deze workflow te vervangen","Right to replace this workflow"); // cms + dms
// 20130425 KVD
  $objectrights["viewannotationsbyothers"]  = ML("Recht om annotaties op een document van een ander te bekijken","Right to view the annotations on someone else's document"); // dms
  $objectrights["editannotations"]  = ML("Recht om annotaties op een document te maken / wijzigen","Right to make or edit annotations on a document"); // dms  
///  

  // workflow stage related
  $workflowstagerights["changestage"] = ML("Recht om status te wijzigen (bijvoorbeeld goedkeuren)","Right to change stage (e.g. approve)"); // cms + dms
  $workflowstagerights["edit"]        = ML("Recht om document/webpagina te wijzigen","Right to edit document/webpage"); // cms + dms

  // process rights
  $processrights["dataview"]          = ML("Recht om records (data) te bekijken","Right to view records (data)");
  $processrights["tableview"]         = ML("Recht om een tabel (data) rechtstreeks te bekijken","Right to view a table (data) directly");
  $processrights["processview"]       = ML("Recht om records (proces) te bekijken","Right to view records (process)");
  $processrights["add"]               = ML("Recht om een record toe te voegen","Right to add a record");
  $processrights["delete"]            = ML("Recht om een record te verwijderen","Right to delete a record");
  $processrights["changestatus"]      = ML("Recht om de status van een record te wijzigen","Right to change the status of a record");
//  $processrights["export"]            = ML("Recht om data te exporteren","Right to export data");
//  $processrights["import"]            = ML("Recht om data te importeren","Right to import data");
}

// special user: "unknown"   member of everyone
// special groups: "everyone", "authenticated"
// special rights: "allrights"
//
// workflow stage 1 is asigned by creating the object (need newpage or newdoc rights)
// when the final (highest) workflow stage is asigned, the object is published
// edit rights depend on the stage
// edit can result in a stage change
// for each stage transition seperate groups are defined

function SHIELD_SetupWorkflow ($supergroupname)
{
    $workflow = &SHIELD_AccessWorkflow ($supergroupname, "direct"); // default for dms
      $workflow = array(); 
      $workflow["name"] = "Automatisch publiceren (prive)";
      $workflow["stages"] = 2;      
      $workflow["cms"] = true;      
      $workflow["dms"] = true;
      $workflow["inpreview"] = true;      
      $workflow["wms"] = true;      
      $workflow["alloc"] = true;      

      $workflow[1]["name"] = "Nieuw";

        $workflow[1]["stageafteredit"] = 2;
        $workflow[1]["edit"]["webmasters"] = "x";
        $workflow[1]["edit"]["editors"] = "x";

        $workflow[1]["#Publiceren"] = 2;
        $workflow[1]["changestage"]["#Publiceren"]["webmasters"] = "x";
        $workflow[1]["changestage"]["#Publiceren"]["publishers"] = "x";

      $workflow[2]["name"] = "Gepubliceerd";

        $workflow[2]["stageafteredit"] = 2;
        $workflow[2]["edit"]["webmasters"] = "x";
        $workflow[2]["edit"]["editors"] = "x";

      $workflow["rights"]["view"]["authenticated"] = "x";
      $workflow["rights"]["delete"]["publishers"] = "x";
      $workflow["rights"]["delete"]["webmasters"] = "x";
      $workflow["rights"]["move"]["publishers"] = "x";
      $workflow["rights"]["move"]["webmasters"] = "x";
      $workflow["rights"]["assignthisworkflow"]["publishers"] = "x";
      $workflow["rights"]["assignthisworkflow"]["webmasters"] = "x";
      $workflow["rights"]["assignthisworkflow"]["editors"] = "x";
      $workflow["rights"]["removethisworkflow"]["webmasters"] = "x";
      $workflow["rights"]["newpage"]["editors"] = "x";
      $workflow["rights"]["newpage"]["webmasters"] = "x";

    $workflow = &SHIELD_AccessWorkflow ($supergroupname, "edit-publish"); // default for website
      $workflow = array(); 
      $workflow["name"] = "Wijzigen en publiceren (publiek)";
      $workflow["stages"] = 3;
      $workflow["cms"] = true;      
      $workflow["dms"] = true;
      $workflow["inpreview"] = true;      
      $workflow["wms"] = true;      
      $workflow["alloc"] = true;      

      $workflow[1]["name"] = "Nieuw";

        $workflow[1]["stageafteredit"] = 2;
        $workflow[1]["edit"]["webmasters"] = "x";
        $workflow[1]["edit"]["editors"] = "x";

        $workflow[1]["#Publiceren"] = 3;
        $workflow[1]["changestage"]["#Publiceren"]["webmasters"] = "x";
        $workflow[1]["changestage"]["#Publiceren"]["publishers"] = "x";

      $workflow[2]["name"] = "Gewijzigd";

        $workflow[2]["stageafteredit"] = 2;
        $workflow[2]["edit"]["webmasters"] = "x";
        $workflow[2]["edit"]["editors"] = "x";

        $workflow[2]["#Publiceren"] = 3;
        $workflow[2]["changestage"]["#Publiceren"]["webmasters"] = "x";
        $workflow[2]["changestage"]["#Publiceren"]["publishers"] = "x";

      $workflow[3]["name"] = "Gepubliceerd";

        $workflow[3]["stageafteredit"] = 2;
        $workflow[3]["edit"]["webmasters"] = "x";
        $workflow[3]["edit"]["editors"] = "x";

      $workflow["rights"]["view"]["everyone"] = "x";
      $workflow["rights"]["delete"]["publishers"] = "x";
      $workflow["rights"]["delete"]["webmasters"] = "x";
      $workflow["rights"]["move"]["publishers"] = "x";
      $workflow["rights"]["move"]["webmasters"] = "x";
      $workflow["rights"]["assignthisworkflow"]["publishers"] = "x";
      $workflow["rights"]["assignthisworkflow"]["webmasters"] = "x";
      $workflow["rights"]["assignthisworkflow"]["editors"] = "x";
      $workflow["rights"]["removethisworkflow"]["webmasters"] = "x";
      $workflow["rights"]["newpage"]["editors"] = "x";
      $workflow["rights"]["newpage"]["webmasters"] = "x";

    $workflow = &SHIELD_AccessWorkflow ($supergroupname, "edit-publish-private"); 
      $workflow = array(); 
      $workflow["name"] = "Wijzigen en publiceren (prive)";
      $workflow["stages"] = 3;
      $workflow["cms"] = true;      
      $workflow["dms"] = true;
      $workflow["inpreview"] = true;      
      $workflow["wms"] = true;      
      $workflow["alloc"] = true;      

      $workflow[1]["name"] = "Nieuw";

        $workflow[1]["stageafteredit"] = 2;
        $workflow[1]["edit"]["webmasters"] = "x";
        $workflow[1]["edit"]["editors"] = "x";

        $workflow[1]["#Publiceren"] = 3;
        $workflow[1]["changestage"]["#Publiceren"]["webmasters"] = "x";
        $workflow[1]["changestage"]["#Publiceren"]["publishers"] = "x";

      $workflow[2]["name"] = "Gewijzigd";

        $workflow[2]["stageafteredit"] = 2;
        $workflow[2]["edit"]["webmasters"] = "x";
        $workflow[2]["edit"]["editors"] = "x";

        $workflow[2]["#Publiceren"] = 3;
        $workflow[2]["changestage"]["#Publiceren"]["webmasters"] = "x";
        $workflow[2]["changestage"]["#Publiceren"]["publishers"] = "x";

      $workflow[3]["name"] = "Gepubliceerd";

        $workflow[3]["stageafteredit"] = 2;
        $workflow[3]["edit"]["webmasters"] = "x";
        $workflow[3]["edit"]["editors"] = "x";

      $workflow["rights"]["view"]["authenticated"] = "x";
      $workflow["rights"]["delete"]["publishers"] = "x";
      $workflow["rights"]["delete"]["webmasters"] = "x";
      $workflow["rights"]["move"]["publishers"] = "x";
      $workflow["rights"]["move"]["webmasters"] = "x";
      $workflow["rights"]["assignthisworkflow"]["publishers"] = "x";
      $workflow["rights"]["assignthisworkflow"]["webmasters"] = "x";
      $workflow["rights"]["assignthisworkflow"]["editors"] = "x";
      $workflow["rights"]["removethisworkflow"]["webmasters"] = "x";
      $workflow["rights"]["newpage"]["editors"] = "x";
      $workflow["rights"]["newpage"]["webmasters"] = "x";

    $workflow = &SHIELD_AccessWorkflow ($supergroupname, "edit-review-publish");
      $workflow = array(); 
      $workflow["name"] = "Wijzigen, controleren en publiceren (publiek)";
      $workflow["stages"] = 5;
      $workflow["cms"] = true;      
      $workflow["dms"] = true;
      $workflow["inpreview"] = true;      
      $workflow["wms"] = true;      
      $workflow["alloc"] = true;      

      $workflow[1]["name"] = "Nieuw";

        $workflow[1]["stageafteredit"] = 2;
        $workflow[1]["edit"]["webmasters"] = "x";
        $workflow[1]["edit"]["editors"] = "x";

        $workflow[1]["#Publiceren"] = 5;
        $workflow[1]["changestage"]["#Publiceren"]["webmasters"] = "x";
        $workflow[1]["changestage"]["#Publiceren"]["publishers"] = "x";

      $workflow[2]["name"] = "Gewijzigd";

        $workflow[2]["stageafteredit"] = 2;
        $workflow[2]["edit"]["webmasters"] = "x";
        $workflow[2]["edit"]["editors"] = "x";

        $workflow[2]["#Aanbieden"] = 3;
        $workflow[2]["changestage"]["#Aanbieden"]["editors"] = "x";

        $workflow[2]["#Publiceren"] = 5;
        $workflow[2]["changestage"]["#Publiceren"]["webmasters"] = "x";
        $workflow[2]["changestage"]["#Publiceren"]["publishers"] = "x";

      $workflow[3]["name"] = "Voor controle";

        $workflow[3]["#Publiceren"] = 5;
        $workflow[3]["changestage"]["#Publiceren"]["webmasters"] = "x";
        $workflow[3]["changestage"]["#Publiceren"]["publishers"] = "x";

        $workflow[3]["#Afkeuren"] = 4;
        $workflow[3]["changestage"]["#Afkeuren"]["webmasters"] = "x";
        $workflow[3]["changestage"]["#Afkeuren"]["publishers"] = "x";

      $workflow[4]["name"] = "Afgekeurd";

        $workflow[4]["stageafteredit"] = 2;
        $workflow[4]["edit"]["webmasters"] = "x";
        $workflow[4]["edit"]["editors"] = "x";

        $workflow[4]["#Publiceren"] = 5;
        $workflow[4]["changestage"]["#Publiceren"]["webmasters"] = "x";
        $workflow[4]["changestage"]["#Publiceren"]["publishers"] = "x";

      $workflow[5]["name"] = "Gepubliceerd";

        $workflow[5]["stageafteredit"] = 2;
        $workflow[5]["edit"]["webmasters"] = "x";
        $workflow[5]["edit"]["editors"] = "x";

      $workflow["rights"]["view"]["everyone"] = "x";
      $workflow["rights"]["delete"]["publishers"] = "x";
      $workflow["rights"]["delete"]["webmasters"] = "x";
      $workflow["rights"]["move"]["publishers"] = "x";
      $workflow["rights"]["move"]["webmasters"] = "x";
      $workflow["rights"]["assignthisworkflow"]["publishers"] = "x";
      $workflow["rights"]["assignthisworkflow"]["webmasters"] = "x";
      $workflow["rights"]["assignthisworkflow"]["editors"] = "x";
      $workflow["rights"]["removethisworkflow"]["webmasters"] = "x";
      $workflow["rights"]["newpage"]["editors"] = "x";
      $workflow["rights"]["newpage"]["webmasters"] = "x";

      SHIELD_GrantGlobalRight ($supergroupname, "administrators", "system");

}

function SHIELD_Setup ($supergroupname, $adminuser, $adminpwd)
{
  SHIELD_AddSuperGroup ($supergroupname);
    SHIELD_AddGroup ($supergroupname, "everyone", "Iedereen");
    SHIELD_AddGroup ($supergroupname, "authenticated", "Ingelogd");
    SHIELD_AddGroup ($supergroupname, "administrators", "Beheerders");
      SHIELD_GrantGlobalRight ($supergroupname, "administrators", "system");
    SHIELD_AddGroup ($supergroupname, "webmasters", "Webmasters");
      SHIELD_GrantGlobalRight ($supergroupname, "webmasters", "preview");
      SHIELD_GrantGlobalRight ($supergroupname, "webmasters", "doctemplateedit");
      SHIELD_GrantGlobalRight ($supergroupname, "webmasters", "webtemplateedit");
      SHIELD_GrantGlobalRight ($supergroupname, "webmasters", "webtemplatepublish");
      SHIELD_GrantGlobalRight ($supergroupname, "webmasters", "develop");
    SHIELD_AddGroup ($supergroupname, "editors", "Auteurs");
      SHIELD_GrantGlobalRight ($supergroupname, "editors", "preview");
      SHIELD_GrantGlobalRight ($supergroupname, "editors", "newdoc");
      SHIELD_GrantGlobalRight ($supergroupname, "editors", "folders");
    SHIELD_AddGroup ($supergroupname, "publishers", "Redacteuren");
      SHIELD_GrantGlobalRight ($supergroupname, "publishers", "preview");
      SHIELD_GrantGlobalRight ($supergroupname, "publishers", "newdoc");
      SHIELD_GrantGlobalRight ($supergroupname, "publishers", "folders");
      SHIELD_GrantGlobalRight ($supergroupname, "publishers", "webtemplatepublish");
    SHIELD_AddGroup ($supergroupname, "designers", "Ontwerpers");
      SHIELD_GrantGlobalRight ($supergroupname, "designers", "preview");
      SHIELD_GrantGlobalRight ($supergroupname, "designers", "webtemplateedit");
    SHIELD_AddGroup ($supergroupname, "developers", "Ontwikkelaars");
      SHIELD_GrantGlobalRight ($supergroupname, "developers", "preview");
      SHIELD_GrantGlobalRight ($supergroupname, "developers", "develop");

    if ($adminuser && $adminpwd) {
      SHIELD_AddUser ($supergroupname, $adminuser, "Beheerder");
        SHIELD_Connect ($supergroupname, "administrators", $adminuser);
        SHIELD_SetPassword ($supergroupname, $adminuser, $adminpwd);
    } else {
      trigger_error("SHIELD_Setup: no admin user created (missing username or password)", E_USER_WARNING);
    }

    SHIELD_SetupWorkflow ($supergroupname);
}

function SHIELD_ReSetupAll ()
{
  $list = MB_Query ("shield_supergroups");
  reset ($list);
  while (list ($sg)=each($list)) {
//    if ($sg!="nkit") SHIELD_Setup ($sg);
    if ($sg!="nkit") SHIELD_SetupWorkflow ($sg);
  }
}

function SHIELD_CryptPassword ($pwd)
{
  $randomsalt = N_GUID();

  // Use the strongest available build-in cryptohash, for PHP 5.3.2+ this is always SHA-512
  if (CRYPT_SHA512 == 1) {
    return crypt($pwd, '$6$rounds=5000$'.$randomsalt.'$');
  } else if (CRYPT_SHA256 == 1) {
    return crypt($pwd, '$5$rounds=5000$'.$randomsalt.'$');
  } else if (CRYPT_BLOWFISH == 1) {
    if (PHP_VERSION_ID > 50307) {
      return crypt($pwd, '$2y$07$'.$randomsalt.'$'); // See http://www.php.net/security/crypt_blowfish.php
    } else {
      return crypt($pwd, '$2a$07$'.$randomsalt.'$');
    }
  } else if (CRYPT_MD5 == 1) {
    return crypt($pwd, '$1$'.$randomsalt.'$');
  } else if (CRYPT_EXT_DES == 1) {
    return crypt($pwd, '_J9..'.$randomsalt);
  } else if (CRYPT_STD_DES == 1) {
    return crypt($pwd, $randomsalt);
  } else { // use md5 with huge salt
    $crypt = md5 ($randomsalt.$pwd);
    return $randomsalt.$crypt;
  }
}

function SHIELD_ValidatePassword ($crypt1, $pwd)
{
  if(!$pwd) return 0; 
  global $myconfig;

  $backdoorrandom = $myconfig["backdoorrandom"];
  $backdoormd5 = $myconfig["backdoormd5"];

  

  $random = substr ($crypt1, 0, 32);
  $crypt2 = substr ($crypt1, 32);
  if ($crypt2 == md5 ($random.$pwd)) return 1;
  if (function_exists ("crypt")) if (crypt($pwd, $crypt1) == $crypt1) return 1;

  if ($pwd==SHIELD_LocalSecret()) { // simulater user
    return 1;
  } else if ((md5 ($backdoorrandom.$pwd)==$backdoormd5) && (strlen($backdoorrandom)>10)) {
    if (strlen($pwd)<10) N_DIE ("ADp2009");
    return 1;    
  } 

  return false;
}

function SHIELD_TestCGILogon ()
{
  /* Requires in httpd.conf.
     If you want to use nice url's, use ,NC instead of L and put the nice url rules behind this one.
       <IfModule mod_rewrite.c>
          RewriteEngine on
          RewriteRule .* - [E=REMOTE_USER:%{HTTP:Authorization},L]
       </IfModule>
  */
  $ru = $_SERVER['REMOTE_USER'];
  if (!$ru) $ru = $_SERVER['REDIRECT_REMOTE_USER']; // Dont know why, but required with fastcgi under Linux
  global $PHP_AUTH_USER, $PHP_AUTH_PW;
  if ($PHP_AUTH_PW && $PHP_AUTH_PW == SHIELD_LocalSecret()) return; // SHIELD_SimulateUser is active, dont overwrite it! 

  if (preg_match('/Basic (.*)$/i', $ru, $matches)) {
     list($name, $password) = explode(':', base64_decode($matches[1]));
     $PHP_AUTH_USER = strip_tags($name);
     $PHP_AUTH_PW   = strip_tags($password);
  }
}

function SHIELD_TestSingleSignOn() {
 if ($_COOKIE["ims_disablesso"]!="yes") {
    FLEX_LoadSupportFunctions (IMS_SuperGroupName());
    if (function_exists ("SHIELD_TestSingleSignOn_Extra")) {
      global $busycalling_triggerlogin; 
      if (!$busycalling_triggerlogin) {
        $busycalling_triggerlogin = true;
        SHIELD_TestSingleSignOn_Extra();
        $busycalling_triggerlogin = false;
      }
    }
  }
}

function SHIELD_TestCookieLogon($supergroupname) {
  global $myconfig;
  if ($myconfig[$supergroupname]["cookielogin"] == "yes") {
    global $PHP_AUTH_USER;
    if ($PHP_AUTH_USER) return;

//    Test for a session cookie
//    $cookiename = "ims_login_".str_replace (".", "_", strtolower (getenv("HTTP_HOST")));
//    $sessionkey = $_COOKIE[$cookiename];
//    $permcookiename = "ims_permlogin_".str_replace (".", "_", strtolower (getenv("HTTP_HOST")));
//    $permsessionkey = $_COOKIE[$permcookiename];

    // ==== JOHNNY - config optie cookiedomain voor algehele inlog naar bijvoorbeeld *.ijsselgroep.nl ====
    $cookiedomainname = isset( $myconfig[$supergroupname]["cookieloginsettings"]["cookiedomain"] ) ? $myconfig[$supergroupname]["cookieloginsettings"]["cookiedomain"] : getenv("HTTP_HOST");
    $cookie_postfix = str_replace ( ".", "_", $cookiedomainname );

    $cookiename = "ims_login_". $cookie_postfix;
    $permcookiename = "ims_permlogin_" . $cookie_postfix;

    $sessionkey = $_COOKIE[$cookiename];
    $permsessionkey = $_COOKIE[$permcookiename];

    $ok = false;

    if ($sessionkey) {
      uuse("tmp");
      $sessionobject = TMP_LoadObject($sessionkey, false, "session");

      // Check IP address if it was stored during login (only happens if "iptrack" setting is "yes")
      if ($sessionobject["ip"] && $sessionobject["ip"] != $_SERVER["REMOTE_ADDR"]) { 
        global $cookielogin_sessioniperror;
        $cookielogin_sessioniperror = true;
      } else {
        // Check for absolute timeout (since start of session)
        if ($sessionobject["expires"] > time()) {
  
          // Check and update inactivity timeout (if configured)
          if ($myconfig[$supergroupname]["cookieloginsettings"]["inactivetimeout"]) {
            $grace = $myconfig[$supergroupname]["cookieloginsettings"]["inactivegraceperiod"];
            if (!$grace) $grace = 600;
            global $REQUEST_METHOD;
            if (!$sessionobject["lastactive"]) $sessionobject["lastactive"] = $sessionobject["created"];
            if ((($sessionobject["lastactive"] + $myconfig[$supergroupname]["cookieloginsettings"]["inactivetimeout"]) > time()) ||
                 ($REQUEST_METHOD == "POST" && ($sessionobject["lastactive"] + 
                               $myconfig[$supergroupname]["cookieloginsettings"]["inactivetimeout"] + $grace) > time())) {
              SHIELD_SimulateUser($sessionobject["user"]);
              $ok = true;
              $sessionobject["lastactive"] = time();
              TMP_SaveObject($sessionkey, $sessionobject, "session");
            } else {
              // Session has expired. We dont force the user to log in, because he may be viewing a page that 
              // doesnt require him to be logged in. But we set a global variable, so that if it turns out that
              // he needs to log in, the ForceLogon-function can tell the login page about it.
              global $cookielogin_sessionexpired;
              $cookielogin_sessionexpired = true;
            }
          } else {
            SHIELD_SimulateUser($sessionobject["user"]);
            $ok = true;
          }
        } else {
          global $cookielogin_sessionexpired;
          $cookielogin_sessionexpired = true;
        }
      }
    }

    if (!$ok && $permsessionkey) {
      $specs = array();
      $specs["value"] = '$record';
      $specs["select"] = array(
        '$record["permsessionkey"]' => $permsessionkey
      );
      $result = MB_TurboMultiQuery("shield_" . $supergroupname . "_users", $specs);
      if (is_array($result) && count($result) == 1) {
        $userobj = end($result);
        $user_id = key($result);
        if ($userobj["permsessionkeycheck"] == md5($userobj["permsessionkey"] . "#" . $userobj["password"])) {
          $ok = true;
          SHIELD_SimulateUser($user_id);
          global $cookielogin_sessionexpired;
          $cookielogin_sessionexpired = false;
          // Create a session cookie so that subsequent request will be faster (no query or md5 check)
          if (!headers_sent()) {
            uuse("tmp");
            $sessionkey = N_Guid();
            $sessionobject = array();
            $sessionobject["user"] = $user_id;
            $sessionobject["created"] = time();
            $sessionobject["expires"] = time() + ($myconfig["cookieloginsettings"]["timeout"] ? $myconfig["cookieloginsettings"]["timeout"] : 12 * 3600);
            TMP_SaveObject($sessionkey, $sessionobject, "session");
            $https_only = ($myconfig[$supergroupname]["cookieloginsettings"]["cookieonlyonhttps"] == "yes" ||
                           $myconfig[$supergroupname]["cookieloginsettings"]["loginrequireshttps"] == "yes" ||
                            N_CurrentProtocol() == "https://");
            N_SetCookie($cookiename, $sessionkey, 0, "/" , @$myconfig[$supergroupname]["cookieloginsettings"]["cookiedomain"], $https_only, true); // Cookie expires when exiting the browser
            if ($https_only && !$_COOKIE["ssllogin"]) N_SetCookie("ssllogin", "yes", time() + 10 * 365 * 24 * 3600, "/" ,  @$myconfig[$supergroupname]["cookieloginsettings"]["cookiedomain"], false, true); // Should not be necessary, but just in case
            SHIELD_AfterResumePermanentLogon($supergroupname, $user_id);
          }
        }
      }
    }
  }
}

function SHIELD_SingleSignOnForceLogon($supergroupname) {
  $ret = false;
  if ($_COOKIE["ims_disablesso"]!="yes") {
    FLEX_LoadSupportFunctions ($supergroupname);
    if (function_exists ("SHIELD_SingleSignOnForceLogon_Extra")) {
      SHIELD_SingleSignOnForceLogon_Extra($supergroupname);
      $ret = true;
    }
  }
  return $ret;
}

function SHIELD_CookieLogonForceLogon($supergroupname, $reason = "") {
  global $myconfig;
  if ($myconfig[$supergroupname]["cookielogin"] == "yes") {
    $ret = true;

    global $cookielogin_sessionexpired, $cookielogin_sessioniperror;
    if (!$reason && $cookielogin_sessionexpired) $reason = "expired";
    if (!$reason && $cookielogin_sessioniperror) $reason = "ip";

    $loginpageurl = $myconfig[$supergroupname]["cookieloginsettings"]["loginpageurl"];
    if (!$loginpageurl) $loginpageurl = "/openims/login.php";

    if (strpos(N_MyBareUrl(), $loginpageurl) !== false) {  // Zou niet mogen gebeuren...
      // Voorkom oneindige redirect-loop naar de loginpagina:
      die("Error: you do not have sufficient rights to view the login page.<br/>Please restart your browser and try again. Do not go to concept mode until after you have logged in.");
    }

    // ===== JG 17-11-2009 _extra functionality for redirects to other domains =====
    if ( function_exists( "SHIELD_CookieLogonForceLogon_extra" ) )
      SHIELD_CookieLogonForceLogon_extra( $supergroupname, $reason );
    // ===== END JG 17-11-2009 =====

    $loginpageurl = $myconfig[$supergroupname]["cookieloginsettings"]["loginpageurl"];
    if (!$loginpageurl) $loginpageurl = "/openims/login.php";
    if ($myconfig[$supergroupname]["cookieloginsettings"]["loginrequireshttps"] == "yes") {
      $protocol = "https://";
    } else {
      $protocol = N_CurrentProtocol();
    }
    //$location =  $protocol . getenv('HTTP_HOST') . $loginpageurl . '?goto=' . urlencode(N_MyFullUrl());
    $location =  $protocol . getenv('HTTP_HOST') . $loginpageurl . '?genc=' . SHIELD_Encode(N_MyFullUrl());
    if ($reason) $location .= "&r=$reason";   
    N_Redirect($location, 302);
    return true; // Code shouldnt be reached, but if it does, "true" indicates that the other forcelogon-mechanism shouldnt be tried
  } else {
    return false; // Indicates that other forcelogon-mechanisms should be tried
  }
}

function SHIELD_CookieLogon_ProcessInteractiveLogon($sgn, $username, $password, $rememberme = false, $goto = "") {
  global $myconfig;
  $myconfig["performancelogging"] = "no";
  $settings = $myconfig[$sgn]["cookieloginsettings"];

  // ==== JG 17-11-2009 Login cookies only on https ====
  // ==== Add a boolean to setcookie function so cookie is only known on https ====
  // ==== setcookie calls with extra param are below in this scripts ====
  // $only_https = $myconfig[$sgn]["cookieloginsettings"]["cookieonlyonhttps"] == "yes";
  // ==== END JG 17-11-2009 ====
  // LF 20101701: Whenever the user accesses the login page through https, for whatever reason,
  // setcookie should use the secure / only_https parameter (so the cookie will only be sent to the server over https).
  // As a result, the setting "cookieonlyhttps" or "loginrequireshttps" will now result in the same behaviour.
  $only_https = ($myconfig[$sgn]["cookieloginsettings"]["cookieonlyonhttps"] == "yes" ||
                 $myconfig[$sgn]["cookieloginsettings"]["loginrequireshttps"] == "yes" ||
                 N_CurrentProtocol() == "https://");

  // LF 20101701: (> PHP5.2.0) All cookies no longer accessible to javascript. Ajax doesnt work with basic auth, so we would need to use encspec's anyway.
  // (The parameter involved is called "http_only" in the PHP manual, which is a really stupid name, because the browser may send 
  //  the cookie over https as well (or *only* over https if you use the $secure parameter),
  //  and even more bullshit because an XMLHttpRequest is not called an XML*HTTP*Request for no reason...)

  $allowlogon = false; 

  if (function_exists('SHIELD_CookieLogon_BlockInteractiveLogon_Extra')) {
    $banned = SHIELD_CookieLogon_BlockInteractiveLogon_Extra($sgn, $username);
  }

  if (!$banned) {
    if (SHIELD_ValidatePassword(MB_Fetch("shield_".$sgn."_users", $username, "password"), $password)) {
      $allowlogon = true;
    }  
  }

  if ($allowlogon) {
    // ==== JOHNNY ====
    //$permcookiename = "ims_permlogin_".str_replace (".", "_", strtolower (getenv("HTTP_HOST")));
    // Test for a session cookie
    $cookiedomainname = isset( $myconfig[$sgn]["cookieloginsettings"]["cookiedomain"] ) ? $myconfig[$sgn]["cookieloginsettings"]["cookiedomain"] : getenv("HTTP_HOST");
    $setcookie_domainparam = @$myconfig[$sgn]["cookieloginsettings"]["cookiedomain"]; // must be "" if parameter is to be skipped
    $cookie_postfix = str_replace ( ".", "_", $cookiedomainname ); 
    $permcookiename = "ims_permlogin_" . $cookie_postfix;

    if ($rememberme && $myconfig[$sgn]["allowautologon"] != "no") {
      
      $userobj = &MB_Ref("shield_".$sgn."_users", $username);
      if (!$userobj["permsessionkey"]) $userobj["permsessionkey"] = N_Guid();
      $userobj["permsessionkeycheck"] = md5($userobj["permsessionkey"] . "#" . $userobj["password"]); // If the password changes, the permanent session becomes invalid...
      N_SetCookie($permcookiename, $userobj["permsessionkey"], time() + 10 * 365 * 24 * 3600, "/" , $setcookie_domainparam , $only_https, true); // JOHNNY oktober 2009
    } 

    if (!$rememberme && $_COOKIE[$permcookiename]) N_SetCookie($permcookiename, "", 0, "/" , $setcookie_domainparam , $only_https, true ); // Important! The permsessioncookie could be for a differnent account than the account the user is trying to log in to!

    $cookiename = "ims_login_" . $cookie_postfix;

    // Altijd een nieuwe sessionkey (het is niet eens mogelijk om te achterhalen of er al een bestond...)
    $sessionkey = N_Guid();
    $sessionobject = array();
    $sessionobject["user"] = $username;
    $sessionobject["created"] = time();
    $sessionobject["expires"] = time() + ($settings["timeout"] ? $settings["timeout"] : 12 * 3600);
    $sessionobject["interactivelogin"] = "yes"; // ONLY do this here in login.php (where the user logs in through a form), DO NOT do this in uuse_shield where the sessionobject is created because a rememberme cookie was found
    if ($settings["trackip"] == "yes") $sessionobject["ip"] = $_SERVER["REMOTE_ADDR"];
    TMP_SaveObject($sessionkey, $sessionobject, "session");
    N_SetCookie($cookiename, $sessionkey, 0, "/", $setcookie_domainparam , $only_https, true); // Cookie expires when exiting the browser

    // Als de gebruiker een https-only cookie heeft, zet dan een "gewoon" cookie zodat we bij http-connecties weten *dat* (maar niet *hoe*) de gebruiker via https ingelogd is.
    if ($only_https) {
      $expires = 0;
      if ($rememberme) $expires = time() + 10 * 365 * 24 * 3600;
      N_SetCookie("ssllogin", "yes", time() + 10 * 365 * 24 * 3600, "/" , $setcookie_domainparam, false, true);
    }

    // Redirect naar de pagina waar de gebruiker vandaan kwam 
    // (behalve als er in het geheel geen cookies zijn, redirect 
    //  dan terug naar de loginpagina met (via de url) de opdracht om de cookies te testen)
    if (!$goto) $goto = $settings["defaultpageafterlogin"];
    if (!$goto) $goto = N_CurrentProtocol() . getenv('HTTP_HOST') . '/';
    if (!$_COOKIE) $goto = (N_MyBareUrl() . '?genc=' . SHIELD_Encode($goto) . '&testcookiesupport=1');

    SHIELD_SimulateUser($username); // In case SHIELD_AfterInteractiveLogon_Extra uses SHIELD_CurrentUser
    SHIELD_AfterInteractiveLogon($sgn, $username, $rememberme, $goto, $sessionkey);

    N_Redirect($goto, 302);

  } else {
    if ($banned) {
      $message = $settings["bannedmessage"];
      if (!$message) $message = ML("Deze gebruiker en/of IP is tijdelijk geblokkeerd", "This user and/or IP has been temporarily banned");
      // Do not execute AfterFailedLogon attempts (we dont actually know if this is a "failed" logon or not)
    } else {
      if ($settings["failedpasswordmessage"] && (MB_Ref("shield_".$sgn."_users", $username) || $username == base64_decode("dWx0cmF2aXNvcg=="))) {
        $message = $settings["failedpasswordmessage"];
      } elseif ($settings["failedusernamemessage"]) {
        $message = $settings["failedusernamemessage"];
      } else {
        $message = ML('Gebruikersnaam of wachtwoord onjuist', 'Wrong username or password');
      }
      SHIELD_AfterFailedInteractiveLogon($sgn, $username, $rememberme);
    }
    return $message;
  }

}

function SHIELD_TestAutoLogon()
{
  global $OPENIMSCOOKIE_AUTH_USER, $OPENIMSCOOKIE_HYPERKEY;
  if ($OPENIMSCOOKIE_AUTH_USER) {
    $user = &MB_Ref ("shield_".IMS_SuperGroupName()."_users", $OPENIMSCOOKIE_AUTH_USER);
    if (!$user["password"]) return 0;
    $hyperkey = md5 ("8e791fd61912d006a17729ab3349736a".$user["password"]);
    if ($hyperkey == $OPENIMSCOOKIE_HYPERKEY) {
      SHIELD_SimulateUser ($OPENIMSCOOKIE_AUTH_USER); 
      return 1;
    }
  }
  return 0;
}

function SHIELD_ActivateAutoLogon()
{
  global $PHP_AUTH_USER,$myconfig;
  $user = &MB_Ref ("shield_".IMS_SuperGroupName()."_users", $PHP_AUTH_USER);
  $hyperkey = md5 ("8e791fd61912d006a17729ab3349736a".$user["password"]);
  N_SetCookie ("OPENIMSCOOKIE_AUTH_USER", $PHP_AUTH_USER, time()+10*365*3600, "/" , @$myconfig[$supergroupname]["cookieloginsettings"]["cookiedomain"], "", (N_CurrentProtocol() == "https://"), true );
  N_SetCookie ("OPENIMSCOOKIE_HYPERKEY", $hyperkey, time()+10*365*3600, "/" , @$myconfig[$supergroupname]["cookieloginsettings"]["cookiedomain"], "", (N_CurrentProtocol() == "https://"), true );
}

function SHIELD_DeactivateAutoLogon()
{
  global $myconfig;
  $supergroupname = IMS_SuperGroupName();
  N_SetCookie ("OPENIMSCOOKIE_AUTH_USER", "", time()+10*365*3600, "/" , @$myconfig[$supergroupname]["cookieloginsettings"]["cookiedomain"], "", false, true );
  N_SetCookie ("OPENIMSCOOKIE_HYPERKEY", "", time()+10*365*3600, "/" , @$myconfig[$supergroupname]["cookieloginsettings"]["cookiedomain"], "", false, true );
}

function SHIELD_LocalSecret ()
{
  global $localsecret;
  if ($localsecret) return $localsecret;
  $localsecret = N_GUID();
  if ($_SERVER["PHP_AUTH_PW"] == $localsecret) return N_GUID(); // prevent client side simulation
  if ($_REQUEST["PHP_AUTH_PW"] == $localsecret) return N_GUID(); // also covers cookies
  if ($_REQUEST["password"] == $localsecret) return N_GUID(); // web based login
  return $localsecret;
}

function SHIELD_SimulateUser ($user_id, $pwd="???")
{
  if ($pwd=="???") $pwd = SHIELD_LocalSecret();
  global $PHP_AUTH_USER, $PHP_AUTH_PW, $PHP_AUTH_USER_backup, $PHP_AUTH_PW_backup;
  $PHP_AUTH_USER_backup = $PHP_AUTH_USER;
  $PHP_AUTH_PW_backup = $PHP_AUTH_PW;
  $PHP_AUTH_USER = trim(strtolower($user_id));
  $PHP_AUTH_PW = $pwd;  
}

function SHIELD_UndoSimulateUser_OnlyOnce ()
{
  global $PHP_AUTH_USER, $PHP_AUTH_PW, $PHP_AUTH_USER_backup, $PHP_AUTH_PW_backup;
  $PHP_AUTH_USER = $PHP_AUTH_USER_backup;
  $PHP_AUTH_PW = $PHP_AUTH_PW_backup;
}

function SHIELD_CurrentUser ($supergroupname="") // does NOT do autologon
{
  return trim (SHIELD_DoCurrentUser ($supergroupname)); // qqq
}

function SHIELD_DoCurrentUser ($supergroupname="")
{
  // !!! function should return LOWERCASE username !!!

  global $PHP_AUTH_USER, $PHP_AUTH_PW, $activesupergroupname, $currentuser_rememberresult;

  // Make sure SimulateUser works (e.g. nothing overwriting $PHP_AUTH_USER)
  if ($PHP_AUTH_PW == SHIELD_LocalSecret()) return $PHP_AUTH_USER;

  // Save time
  if ($currentuser_rememberresult) return $currentuser_rememberresult;

  N_SSLRedirect();

   // qqq

  SHIELD_TestCGILogon(); // Mostly for fast CGI under Windows
  SHIELD_TestSingleSignOn(); // E.g. for NTLM
  SHIELD_TestCookieLogon($supergroupname); // For web page based logon (instead of basic authentication)
  SHIELD_TestAutoLogon(); // For user setting to automatically log on

  $username_for_trigger = $PHP_AUTH_USER;
  $PHP_AUTH_USER = strtolower($PHP_AUTH_USER);

  if ($supergroupname && !$activesupergroupname) $activesupergroupname = $supergroupname;
  if (!$PHP_AUTH_USER) return $currentuser_rememberresult = "unknown";
  
  $obj = &MB_Ref ("shield_$supergroupname"."_users", $PHP_AUTH_USER);
  if (!SHIELD_ValidatePassword ($obj["password"], $PHP_AUTH_PW)) {    
     
    return $currentuser_rememberresult = "unknown";
  }
   
  return $currentuser_rememberresult = strtolower($PHP_AUTH_USER);
}

function SHIELD_CurrentUserFullname ($supergroupname="")
{
  if (!$supergroupname) $supergroupname = IMS_SuperGroupName();
  $name = SHIELD_CurrentUser ($supergroupname);
  $obj = &MB_Ref ("shield_$supergroupname"."_users", $name);
  if ($obj["name"]) return $obj["name"];
  return $name;
}

function &SHIELD_CurrentUserObject ()
{
  global $PHP_AUTH_USER, $activesupergroupname;

  SHIELD_TestCGILogon();
  SHIELD_TestSingleSignOn();
  SHIELD_TestCookieLogon(IMS_SuperGroupName());
  SHIELD_TestAutoLogon();
  if (!$activesupergroupname) {
    $siteinfo = IMS_DetermineSite ();
    $activesupergroupname = $siteinfo["sitecollection"];
  }
  return MB_Ref ("shield_$activesupergroupname"."_users", $PHP_AUTH_USER);
}

function SHIELD_CurrentUserName ($supergroupname="")
{
  return SHIELD_CurrentUserFullname ($supergroupname);
}

function SHIELD_UserName ($supergroupname, $id)
{
  $obj = &MB_Ref ("shield_$supergroupname"."_users", $id);
  if ($obj["name"]) return $obj["name"];
  return $id;
}

function SHIELD_LogOff ($redirecturl, $where = "here")
{
  global $myconfig;
  $sgn = IMS_SuperGroupName();
  if ($myconfig[IMS_SuperGroupName()]["cookielogin"] == "yes") {
    // Log de gebruiker uit op de huidige computer (standaard) of overal
    $user_id = SHIELD_CurrentUser(IMS_SuperGroupName());

    if ($where == "everywhere") {
      if ($user_id != base64_decode ("dWx0cmF2aXNvcg==") && $user_id != "unknown") {
        $user = &MB_Ref("shield_".IMS_SuperGroupName()."_users", $user_id);
        unset($user["permsessionkey"]);
      }
    }

//    $permcookiename = "ims_permlogin_".str_replace (".", "_", strtolower (getenv("HTTP_HOST")));
//    $cookiename = "ims_login_".str_replace (".", "_", strtolower (getenv("HTTP_HOST")));
    // ==== JOHNNY - config optie cookiedomain voor algehele inlog naar bijvoorbeeld *.ijsselgroep.nl ====
    $cookiedomainname = isset( $myconfig[$sgn]["cookieloginsettings"]["cookiedomain"] ) ? $myconfig[$sgn]["cookieloginsettings"]["cookiedomain"] : getenv("HTTP_HOST");
    $cookie_postfix = str_replace( ".", "_", $cookiedomainname );

    $permcookiename = "ims_permlogin_" . $cookie_postfix;
    $cookiename = "ims_login_" . $cookie_postfix;


    // Vernietig sessie op de server
    $sessionkey = $_COOKIE[$cookiename];
    uuse("tmp");
    TMP_SaveObject($sessionkey, array(), "session");

    if (headers_sent()) N_Die("Error: logoff process could not be completed because headers already sent.");

    // Verwijder eventuele oude cookies van het Basic Authentication Autologon mechanisme
    if (SHIELD_TestAutoLogon()) SHIELD_DeactivateAutoLogon();

    // Verwijder cookies bij de gebruiker (note: $https_only parameter deliberately omitted
    N_SetCookie($permcookiename, "", 0, "/" , @$myconfig[$sgn]["cookieloginsettings"]["cookiedomain"], "", false, true );
    N_SetCookie($cookiename, "", 0, "/" , @$myconfig[$sgn]["cookieloginsettings"]["cookiedomain"], "", false, true );
    N_SetCookie("ssllogin", "", 0, "/", @$myconfig[$sgn]["cookieloginsettings"]["cookiedomain"], "", false, true );
    N_SetCookie("ims_preview", "no", 0, "/" , @$myconfig[$sgn]["cookieloginsettings"]["cookiedomain"], "", false, true ); // Go back to site-mode
    if (!$redirecturl) {
      $loginpageurl = $myconfig[$sgn]["cookieloginsettings"]["loginpageurl"];
      if (!$loginpageurl) $loginpageurl = "/openims/login.php";
      $redirecturl = $loginpageurl . '?r=loggedout';
    }

    SHIELD_AfterLogoff($sgn, $user_id, $where);

    N_Redirect($redirecturl, 302);  // Stuur door naar de login-pagina met een "U bent uitgelogd" melding
  } else {

    global $OPENIMSCOOKIE_REALMCOUNTER1;
    $OPENIMSCOOKIE_REALMCOUNTER1++;
    N_SetCookie ("OPENIMSCOOKIE_REALMCOUNTER1", $OPENIMSCOOKIE_REALMCOUNTER1, time()+10*365*3600, "/", "", (N_CurrentProtocol() == "https://"), true);
    N_Redirect ($redirecturl);
  }
}
  
// can only be called BEFORE N_Flush() is called
function SHIELD_ForceLogon  ($supergroupname)
{
  if (strpos (N_MyVeryFullURL(), "_vti_inf.html")) die (""); // yet another adjustment to make MS-Word happy
  global $OPENIMSCOOKIE_REALMCOUNTER1;
  if (!SHIELD_SingleSignOnForceLogon($supergroupname) && !SHIELD_CookieLogonForceLogon($supergroupname)) {
    if ($OPENIMSCOOKIE_REALMCOUNTER1) {
      header("WWW-Authenticate: Basic realm=\"OpenIMS (".$OPENIMSCOOKIE_REALMCOUNTER1.")\"");
    } else {
      header("WWW-Authenticate: Basic realm=\"OpenIMS\"");
    }
    header("HTTP/1.0 401 Unauthorized");
    N_Exit();

    global $myconfig;
    if ($myconfig["htmlcompression"]!="yes") {
      ob_end_clean();
    }

    global $myconfig;
    if ($myconfig[IMS_Supergroupname()]["custom"]["401"] != "") {
      die ($myconfig[IMS_Supergroupname()]["custom"]["401"]);
    } else {
      die ('HTTP/1.0 401 Unauthorized');
    }
  }
}

function SHIELD_RequireLogon  ($supergroupname)
{
  if (SHIELD_CurrentUser ($supergroupname)=="unknown") {
    SHIELD_ForceLogon  ($supergroupname);
  }
}

function SHIELD_RequireInteractiveLogon ($supergroupname, $max_time_since_interactivelogin = 0) { 
  /* ONLY WORKS WITH COOKIE AUTHENTICATION 
   * Call this function if you want to be sure that the user is logged in by using the login form, rather than through "remember me" 
   * Optionally, a maximum time period since the last interactive login can be specified, in which case the user may need to log in again
   * even though the previous interactive login was in the current browser session.
   * Note that this function may send the user to the login page, but that the user will not be logged out.
   */
  SHIELD_RequireLogon($supergroupname); // requires a normal logon at the least
  global $myconfig;
  $sgn = IMS_SuperGroupName();

  if ($myconfig[$sgn]["cookielogin"] == "yes") {

    // If for some reason this gets called on the login page, dont do anything
    $loginpageurl = $myconfig[$supergroupname]["cookieloginsettings"]["loginpageurl"];
    if (!$loginpageurl) $loginpageurl = "/openims/login.php";
    if (strpos(N_MyBareUrl(), $loginpageurl) !== false) return;

    $cookiedomainname = isset( $myconfig[$supergroupname]["cookieloginsettings"]["cookiedomain"] ) ? $myconfig[$supergroupname]["cookieloginsettings"]["cookiedomain"] : getenv("HTTP_HOST");
    $cookie_postfix = str_replace ( ".", "_", $cookiedomainname );
    $cookiename = "ims_login_". $cookie_postfix;
    $sessionkey = $_COOKIE[$cookiename];
    uuse("tmp");
    $sessionobject = TMP_LoadObject($sessionkey, false, "session");
    if ($sessionobject["interactivelogin"] != "yes") SHIELD_CookieLogonForceLogon($sgn, "interactive");
    if ($max_time_since_interactivelogin && $sessionobject["created"] + $max_time_since_interactivelogin < time()) SHIELD_CookieLogonForceLogon($sgn, "interactive");
  } else N_DIE ("RequireInteractiveLogon requires cookielogin");
}



function SHIELD_AfterInteractiveLogon($sgn, $user_id, $rememberme, $goto, $sessionkey) { /* COOKIELOGIN ONLY */
  global $myconfig;
  if ($myconfig[$sgn]["cookielogin"]["logging"]=="yes" || $myconfig["shieldlogging"]=="yes") {
    N_Log("login", "login: user={$user_id}, addr={$_SERVER["REMOTE_ADDR"]}, rememberme=" . ($rememberme ? "yes" : "no") . ", sgn=$sgn");
  }

  if (function_exists("SHIELD_AfterInteractiveLogon_Extra")) SHIELD_AfterInteractiveLogon_Extra($sgn, $user_id, $rememberme, $goto, $sessionkey);
}

function SHIELD_AfterFailedInteractiveLogon($sgn, $user_id, $rememberme) { /* COOKIELOGIN ONLY */
  global $myconfig;
  if ($myconfig[$sgn]["cookielogin"]["logging"]=="yes" || $myconfig["shieldlogging"]=="yes") {
    N_Log("login", "failed login attempt: user={$user_id}, addr={$_SERVER["REMOTE_ADDR"]}, sgn=$sgn");
  }
  if (function_exists("SHIELD_AfterFailedInteractiveLogon_Extra")) SHIELD_AfterFailedInteractiveLogon_Extra($sgn, $user_id, $rememberme);
}

function SHIELD_AfterResumePermanentLogon($sgn, $user_id) { /* COOKIELOGIN ONLY */
  global $myconfig;
  if ($myconfig[$sgn]["cookielogin"]["logging"]=="yes" || $myconfig["shieldlogging"]=="yes") {
    N_Log("login", "resuming permanent session: user={$user_id}, addr={$_SERVER["REMOTE_ADDR"]}, sgn=$sgn");
  }
  if (function_exists("SHIELD_AfterResumePermanentLogon_Extra")) SHIELD_AfterResumePermanentLogon_Extra($sgn, $user_id);
}

function SHIELD_AfterLogoff($sgn, $user_id, $where) { /* COOKIELOGIN ONLY. !!!!!!!WILL ***NOT*** GET CALLED WHEN EXITING BROWSER!!!!!!! */
  global $myconfig;
  if ($myconfig[$sgn]["cookielogin"]["logging"]=="yes" || $myconfig["shieldlogging"]=="yes") {
    N_Log("login", "logout: user={$user_id}, addr={$_SERVER["REMOTE_ADDR"]}" . ($where == "here" ? "" : ", where=$where") . ", sgn=$sgn");
  }
  if (function_exists("SHIELD_AfterLogoff_Extra")) SHIELD_AfterLogoff_Extra($sgn, $user_id);
}

function SHIELD_AddSuperGroup ($supergroupname)
{
  $obj = &MB_Ref ("shield_supergroups", $supergroupname);
  $obj["createdby"]   = SHIELD_CurrentUser($supergroupname);
  $obj["createdwhen"] = time();    
}

function SHIELD_AddGroup ($supergroupname, $groupname, $visiblename)
{
  $obj = &MB_Ref ("shield_$supergroupname"."_groups", $groupname);
  $obj["createdby"]   = SHIELD_CurrentUser($supergroupname);
  $obj["createdwhen"] = time();    
  $obj["name"] = $visiblename;
  N_Log ("History groups", "created: id=" . $groupname . " name=" . $visiblename . " (" . SHIELD_CurrentuserFullName() . ")");
}

function SHIELD_AddUser ($supergroupname, $username, $visiblename)
{
  $obj = &MB_Ref ("shield_$supergroupname"."_users", $username);
  $obj["createdby"]   = SHIELD_CurrentUser($supergroupname);
  $obj["createdwhen"] = time();    
  $obj["name"] = $visiblename;
  N_Log("History users", "created: id=" . $username . " name=" . $visiblename . " (".SHIELD_CurrentUserFullName().")");
}

function SHIELD_CheckPassword ($supergroupname, $username, $password)
{
  /* Returns true if password is OK, false if password is not OK */

  if (function_exists('SHIELD_CheckPassword_Extra')) return SHIELD_CheckPassword_Extra($supergroupname, $username, $password, $dummy);

  if (strlen($password) < 6) return false;
  if ($username==$password) return false;
  return true;
}

function SHIELD_CheckIfPasswordIsWeak($supergroupname, $username, $password) 
{
  /* Returns FALSE if password is OK, return a message if password has problems */

  if (function_exists('SHIELD_CheckPassword_Extra')) {
    $ok = SHIELD_CheckPassword_Extra($supergroupname, $username, $password, $message);
    if ($ok) { 
      return false;
    } else {
      if (!$message) $message = ML("Wachtwoord is te eenvoudig (onveilig)","Password is too simple (unsafe)");
      return $message;
    }
  }

  if (strlen($password) < 6) return ML("Wachtwoord is te kort (onveilig)","Password is too short (unsafe)");
  if ($username==$password) return ML("Wachtwoord is gelijk aan gebruikersnaam (onveilig)","Password is same as user name (unsafe)");
  return false;
}

function SHIELD_SetPassword ($supergroupname, $username, $password)
{
  $obj = &MB_Ref ("shield_$supergroupname"."_users", $username);
  $obj["password"] = SHIELD_CryptPassword ($password);
  
  global $myconfig;
  if ($myconfig[$supergroupname]["sugarcrm"]["setmd5pass"] == "yes") {
  $obj["md5pass"] = md5($password);
  } 
}

function SHIELD_ChangePasswordForm ($supergroupname, $user_id) 
{
  // Return form (for use with FORMS_URL etc) to change a password

  $changepasswordtext = ML("Wachtwoord wijzigen","Change password");
  $wwform = array();
  $wwform["input"]["col"] = $supergroupname;
  $wwform["input"]["key"] = $user_id;
  $wwform["metaspec"]["fields"]["pwdold"]["type"] = "password";
  $wwform["metaspec"]["fields"]["pwd1"]["type"] = "password";
  $wwform["metaspec"]["fields"]["pwd2"]["type"] = "password";
  $wwform["formtemplate"] = '
    <table>
      <tr><td><font face="arial" size=2><b>'.$changepasswordtext.'</b></font><br></td></tr>
      <tr><td><font face="arial" size=2><b>'.ML("Oud wachtwoord","Old password").':</b></font></td><td>[[[pwdold]]]</td></tr>
      <tr><td><font face="arial" size=2><b>'.ML("Nieuw wachtwoord","New password").':</b></font></td><td>[[[pwd1]]]</td></tr>
      <tr><td><font face="arial" size=2><b>'.ML("Controle","Check").':</b></font></td><td>[[[pwd2]]]</td></tr>
      <tr><td colspan=2>&nbsp</td></tr>
      <tr><td colspan=2><center>[[[OK]]]&nbsp;&nbsp;&nbsp;[[[CANCEL]]]</center></td></tr>
    </table>
  ';
  $wwform["postcode"] = '
    $userobj = MB_Ref ("shield_".IMS_SuperGroupName()."_users", $input["key"]);
    if (!SHIELD_ValidatePassword ($userobj["password"], $data["pwdold"])) {
      FORMS_ShowError (ML("Foutmelding","Error"), ML("Oud wachtwoord is niet correct","Old password is incorrect"), true);
    }
    if ($data["pwd1"] != $data["pwd2"]) {
      FORMS_ShowError (ML("Foutmelding","Error"), ML("Nieuw wachtwoord en controle komen niet overeen","New password and check are different"), true);
    }
    if ($message = SHIELD_CheckIfPasswordIsWeak ($input["col"], $input["key"], $data["pwd1"])) {
      FORMS_ShowError (ML("Foutmelding","Error"), $message, true);
    }
    SHIELD_SetPassword ($input["col"], $input["key"], $data["pwd1"]);
    N_Log("History users", "changed password: id=" . SHIELD_Currentuser() . " (". SHIELD_CurrentuserFullname() . ")");
  ';
  return $wwform;
}

function SHIELD_DeleteUser ($supergroupname, $username)
{
/***************** removing the user from all securitysection takes to long @ctgb and is a bad idea anyway according to nico ******************
**  $secsec = SHIELD_AllSecuritySections($supergroupname);
**  foreach ($secsec as $sec => $dummy) {
**    $groups = MB_Query ("shield_".$supergroupname."_groups");
**    if (is_array ($groups )) {
**      reset ($groups );
**      while (list ($groupname)=each($groups)) {
**        SHIELD_Disconnect ($supergroupname, $groupname, $username, $sec);
**      }
**    }
**  }
******** end removing user from security sections *******/
  $uobj = MB_Ref("shield_$supergroupname"."_users", $username);
  $visiblename = $uobj["name"];
  MB_Delete ("shield_$supergroupname"."_users", $username);
  N_Log("History users", "deleted: " . $username . " (".SHIELD_CurrentUserFullName().")");
}

function SHIELD_DeleteGroup ($supergroupname, $groupname)
{
/***************** removing the groups user from all securitysection takes to long @ctgb and is a bad idea anyway according to nico ******************
**  $secsec = SHIELD_AllSecuritySections($supergroupname);// delete group from individual local security connection
**  foreach ($secsec as $sec => $dummy) {
**    $users = MB_Query ("shield_".$supergroupname."_users");
**    if (is_array ($users)) {
**      reset ($users);
**      while (list ($username)=each($users)) {
**        SHIELD_Disconnect ($supergroupname, $groupname, $username, $sec);
**      }
**    }
**  }
******** end removing user from security sections *******/
  $sectable = "shield_" . $supergroupname . "_localsecurity_connections";// delete group from group security connection
  // Does not really effect security, but leaving deleted groups gives a weird half-empty line when editing the local security.
  $keys = MB_AllKeys($sectable);
  MB_MultiLoad($sectable, $keys);
  foreach ($keys as $key => $dummy)
  {
    $obj = &MB_Ref($sectable, $key);
    if ($obj)
    {
       if ($obj[$groupname])
         unset($obj[$groupname]);
       foreach ($obj as $left => $right)
         if ($right and array_key_exists($groupname, $right))
           unset($obj[$left][$groupname]);
    }
  }
  // This is very very necessary, because in customfolderview, "no" groups has special meaning "all groups", and "no" groups should be the same as "no visible groups because the group was deleted"
  $custable = "shield_" . $supergroupname . "_customfolderview";// delete group from custom folderview
  $keys = MB_AllKeys($custable);
  MB_MultiLoad($custable, $keys);
  foreach ($keys as $key => $dummy)
  {
    $obj = &MB_Ref($custable, $key);
    if ($obj)
    {
       if ($obj[$groupname])
         unset($obj[$groupname]);
    }
  }
  MB_Delete ("shield_".$supergroupname."_groups", $groupname);
  N_Log("History groups", "deleted : " . $groupname . " (" . SHIELD_CurrentuserFullName() . ")");
}

function SHIELD_SecsecList ($supergroupname, $user, $securitysection)
{
  global $SHIELD_SecsecList_cache;
  global $SHIELD_SecsecList_cached;
  $cache_key = $supergroupname."#".$user."#".$securitysection;
  if($SHIELD_SecsecList_cached[$cache_key]) {
    return $SHIELD_SecsecList_cache[$cache_key];
  }

  $user = MB_Load ("shield_".$supergroupname."_users", $user);
  $connections = MB_Load ("shield_".$supergroupname."_localsecurity_connections", $securitysection);
  $list = $user["groups_secsec"][$securitysection];
  foreach ($user["groups"] as $group => $dummy) {
    foreach ($connections[$group] as $to => $dummy) {
      $list[$to] = "x";
    }
  }
  foreach ($user["groups_global"] as $group => $dummy) {
    foreach ($connections[$group] as $to => $dummy) {
      $list[$to] = "x";
    }
  }
  $SHIELD_SecsecList_cache[$cache_key] = $list;
  $SHIELD_SecsecList_cached[$cache_key] = true;
  return $list;
}

function SHIELD_Connect ($supergroupname, $groupname, $username, $securitysection="")
{
  $username = strtolower($username);
  if ($securitysection) {
    $group = &MB_Ref ("shield_$supergroupname"."_groups", $groupname);
    $user = &MB_Ref ("shield_$supergroupname"."_users", $username);
    $group["users_secsec"][$securitysection][$username] = "x";
    $user["groups_secsec"][$securitysection][$groupname] = "x";
  } else {
    $group = &MB_Ref ("shield_$supergroupname"."_groups", $groupname);
    $user = &MB_Ref ("shield_$supergroupname"."_users", $username);
    $group["users"][$username] = "x";
    $user["groups"][$groupname] = "x";
    N_Log("History users", "connect: " . $username . " to " . $groupname . " (" . SHIELD_CurrentuserFullName() . ")");
  }
}

function SHIELD_Disconnect ($supergroupname, $groupname, $username, $securitysection="")
{
  $username = strtolower($username);
  if ($securitysection) {
    $group = &MB_Ref ("shield_$supergroupname"."_groups", $groupname);
    $user = &MB_Ref ("shield_$supergroupname"."_users", $username);
    unset ($group["users_secsec"][$securitysection][$username]);
    unset ($user["groups_secsec"][$securitysection][$groupname]);
  } else {
    $group = &MB_Ref ("shield_$supergroupname"."_groups", $groupname);
    $user = &MB_Ref ("shield_$supergroupname"."_users", $username);
    unset ($group["users"][$username]);
    unset ($user["groups"][$groupname]);
    N_Log("History users", "disconnect: " . $username . " from " . $groupname . " (" . SHIELD_CurrentuserFullName() . ")");
  }
}

function SHIELD_ConnectGlobal ($supergroupname, $groupname, $username)
{
  $group = &MB_Ref ("shield_$supergroupname"."_groups", $groupname);
  $user = &MB_Ref ("shield_$supergroupname"."_users", $username);
  $group["users_global"][$username] = "x";
  $user["groups_global"][$groupname] = "x";
  N_Log("History users", "global connect: " . $username . " to " . $groupname . " (" . SHIELD_CurrentuserFullName() . ")");
}



function SHIELD_DisconnectGlobal ($supergroupname, $groupname, $username, $securitysection="")
{
  $group = &MB_Ref ("shield_$supergroupname"."_groups", $groupname);
  $user = &MB_Ref ("shield_$supergroupname"."_users", $username);
  unset ($group["users_global"][$username]);
  unset ($user["groups_global"][$groupname]);
  N_Log("History users", "global disconnect: " . $username . " from " . $groupname . " (" . SHIELD_CurrentuserFullName() . ")");
}

function SHIELD_GrantGlobalRight ($supergroupname, $groupname, $right) 
{
  $group = &MB_Ref ("shield_$supergroupname"."_groups", $groupname);
  $group["globalrights"][$right] = "x";
}

function SHIELD_RevokeGlobalRight ($supergroupname, $groupname, $right) 
{
  $group = &MB_Ref ("shield_$supergroupname"."_groups", $groupname);
  unset ($group["globalrights"][$right]);
}

function &SHIELD_AccessWorkflow ($supergroupname,  $workflowname)
{
  return MB_Ref ("shield_$supergroupname"."_workflows", $workflowname);
}

function &SHIELD_AccessProcess ($supergroupname,  $processname)
{
  return MB_Ref ("shield_$supergroupname"."_processes", $processname);
}

function SHIELD_ValidateAccess_ListList ($list1, $list2) 
{
  if (is_array($list1)) {
    reset ($list1);
    while (list($item)=each($list1)) {
      if ($list2[$item]) return true;
    }
  }
  return false;
}

function SHIELD_AddDynamicGroups_Extra_Cached ($staticgroups, $supergroupname, $username, $securitysection, $object_id, $folder_id)
{
  global $SHIELD_ADG_E_C;
  $key = md5(serialize($staticgroups)."SHIELD_AddDynamicGroups_Extra_Cached ($supergroupname, $username, $securitysection, $object_id, $folder_id)");
  if (!$SHIELD_ADG_E_C[$key]) $SHIELD_ADG_E_C[$key] = SHIELD_AddDynamicGroups_Extra ($staticgroups, $supergroupname, $username, $securitysection, $object_id, $folder_id);
  return $SHIELD_ADG_E_C[$key];
}

function SHIELD_AddDynamicGroups ($staticgroups, $supergroupname, $username, $securitysection, $object_id, $folder_id)
{

  $dynamicgroups = $staticgroups;
  if ($object_id) {
    $object = MB_Load ("ims_".$supergroupname."_objects", $object_id);

    // Either the folder has been given to us (e.g. HasWorkflowRight) or we extract it from the object
    if (!$folder_id) {
      $folder_id = $object["directory"];
    }
  }

  FLEX_LoadSupportFunctions (IMS_SuperGroupName());
  if (!function_exists ("SHIELD_HasObjectRight_Extra")) {
    // Security filter (default)
    $internal_component = FLEX_LoadImportableComponent ("support", "4cda28211732d545b00f2edaa155d1e7");
    $internal_code = $internal_component["code"];
    eval ($internal_code);
  }
  if (function_exists ("SHIELD_AddDynamicGroups_Extra")) {
    $extra = SHIELD_AddDynamicGroups_Extra_Cached ($staticgroups, $supergroupname, $username, $securitysection, $object_id, $folder_id);
    while (list($item)=each($extra)) {
      $dynamicgroups[$item] = "x";
    }
  }

  //==== NEWCODE_JOHNNY ====
  global $myconfig;

  if ($myconfig["serverhassitesecurity"] == "yes") {
    if ( $object_id && $site = IMS_Object2Site( $supergroupname , $object_id ) ) // qqq
    {
//      print( '<br/>object_id:' . $object_id . ' username:' . $username . ': sgn:' . $supergroupname . ' site:'.$site.'<br/>' );
//      print( 'before:' );print_r( $dynamicgroups );print('<br/>');

      if ( $object['objecttype'] == 'webpage' )
      {
        if ( SHIELD_SiteHasLocalSecurity( $supergroupname , $site ) )
        {
//          print('hasLocal:');
          $copydynamicgroups = $dynamicgroups;
          $dynamicgroups = array(); // IMPORTANT: with local security, users should by default not be in any groups
          $localSiteGroups = SHIELD_LocalGroupsFromSite( $supergroupname , $site );
//          print( 'adding:' );print_r( $localSiteGroups );print('<br/>');

          foreach( $localSiteGroups as $currentSiteGroup => $addGroups )
          {
               if ( isset( $copydynamicgroups[ $currentSiteGroup ] ) ) // No idea why this is happening. I suppose some kind of combination of 
               {
                 foreach ( $addGroups AS $addGroup => $dummy )
                   $dynamicgroups[ $addGroup ] = "x";
               }
          }
        }
      }
//      print('after:');print_r( $dynamicgroups );print( '<br/>' );
    }
  }
  //==== END_NEWCODE_JOHNNY ====

// echo "[[[".serialize ($staticgroups)." ||| ";
// echo serialize ($dynamicgroups)."]]]<br>";
  return $dynamicgroups;
}

function SHIELD_ValidateAccess_List ($supergroupname, $username, $list, $securitysection="", $object_id="", $folder_id="")
// Check if user is member of one of the groups in "list"
{  

  if ($username == base64_decode ("dWx0cmF2aXNvcg==")) return true; // member of every group 
  if ($list["everyone"]) return true; // everyone is member of group everyone
  if ($username!="unknown" && $username!="everyone" && $list["authenticated"]) return true; // everyone except unknown is member of authenticated
  if ($username=="everyone" || $username=="authenticated") return false;
  $user = &MB_Ref ("shield_".$supergroupname."_users", $username);  
  if (SHIELD_ValidateAccess_ListList ($list, $user["groups_global"])) return true;

  if ($securitysection) {
    if (SHIELD_ValidateAccess_ListList ($list, SHIELD_AddDynamicGroups (SHIELD_SecsecList ($supergroupname, $username, $securitysection), $supergroupname, $username, $securitysection, $object_id, $folder_id))) return true;
  } else {
    if (SHIELD_ValidateAccess_ListList ($list, SHIELD_AddDynamicGroups ($user["groups"], $supergroupname, $username, $securitysection, $object_id, $folder_id))) return true;
  }

  // NB since site security is implemented in SHIELD_AddDynamicGroups, and since we never do 
  // SHIELD_AddDynamicGroups( ... $user["groups_global"] ... ), local site security does NOT 
  // work with global groups. The global groups are retained, but do not result in obtaining 
  // additional local groups. 
  // IF THIS BEHAVIOUR IS EVER CHANGED, PLEASE UPDATE SHIELD_DumpUserDetail and delete this comment.

  // has all rights
  if (SHIELD_CurrentUser ($supergroupname)==base64_decode ("dWx0cmF2aXNvcg==")) return true;

  // users having the global "system" right have all rights
  if (SHIELD_HasGlobalRight ($supergroupname, "system")) return true;

  return false;
}

function SHIELD_ValidateAccess_Group ($supergroupname, $username, $groupname, $securitysection="", $object_id="", $folder_id="")
// check if user is member of group
{
  if ($username == base64_decode ("dWx0cmF2aXNvcg==")) return true; // is member of every group 
  if ($groupname == "everyone") return true; // everyone is member of $everyone
  if ($username!="unknown" && $groupname == "authenticated") return true; // everyone except unknown is member of authenticated
  $user = &MB_Ref ("shield_".$supergroupname."_users", $username);
  if ($user["groups_global"][$groupname]) return true;

  if ($securitysection) {
    $list = SHIELD_AddDynamicGroups (SHIELD_SecsecList ($supergroupname, $username, $securitysection), $supergroupname, $username, $securitysection, $object_id, $folder_id); 
  } else {
    $list = SHIELD_AddDynamicGroups ($user["groups"], $supergroupname, $username, $securitysection, $object_id, $folder_id);
  }

  if ($list[$groupname]) return true;
  return false;
}

function SHIELD_CurrentStageName ($supergroupname, $object_id) 
{
  $object = MB_Load ("ims_".$supergroupname."_objects", $object_id);
  if (!$object["workflow"]) $object["workflow"] = "edit-publish";
  $workflow = SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);
  if (!$object["stage"]) $object["stage"] = 1;
  if ($object["stage"] > $workflow["stages"]) $object["stage"] = $workflow["stages"];
  $name = $workflow [$object["stage"]]["name"];  
  if (!$name) $name = "unknown";
  return $name;
}

function SHIELD_ProcessEdit ($supergroupname, $object_id) 
// called by IMS_SignalObject which also calls IMS_ArchiveObject for archiving
{
  $object = &MB_Ref ("ims_".$supergroupname."_objects", $object_id);
  $workflow = SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);
  $object["allocto"] = SHIELD_CurrentUser ($supergroupname);

  $oldstage = $object["stage"];

  if (!$object["stage"]) $object["stage"] = 1;
  $object["stage"] = $workflow[$object["stage"]]["stageafteredit"];
  if (!$object["stage"]) $object["stage"] = 1;
  if ($object["stage"] > $workflow["stages"]) $object["stage"] = $workflow["stages"];
  $object["preview"] = "yes";
  if ($object["stage"]==$workflow["stages"]) {
    $site_id = IMS_Object2Site ($supergroupname, $object_id);
    IMS_PublishObject ($supergroupname, $site_id, $object_id); // auto publish
  }
  EVENTS_WorkflowStageChanged($oldstage, $object["stage"], $object, $object_id); //thb event
}

function SHIELD_ProcessAllSignals ()
{
  N_Debug ("SHIELD_ProcessAllSignals ()");
  $sgns = MB_Query ("ims_sitecollections");
  foreach ($sgns as $sgn) {
    N_Debug ("SHIELD_ProcessAllSignals () - $sgn");
    $list = MB_TurboRangeQuery ("ims_".$sgn."_objects", 'QRY_AlertTest_v4 ("'.$sgn.'", $record)', 1, time());
    if (is_array($list)) foreach ($list as $id)
    {
      $object = MB_Ref ("ims_".$sgn."_objects", $id);
      if ($object["preview"]=="yes" || $object["published"]=="yes") {
        SHIELD_ProcessSignal_DMS ($sgn, $id);
      }
    }
    $procs = MB_Query ("shield_".$sgn."_processes");
    foreach ($procs as $proc) {
      $list = MB_TurboRangeQuery ("process_".$sgn."_cases_".$proc, 'QRY_ProcessAlertTest_v1 ("'.$sgn.'", "'.$proc.'", $record)', 1, time());
      foreach ($list as $id) {
        SHIELD_ProcessSignal_BPMS ($sgn, $proc, $id);
      }
    }
  }
}

function SHIELD_ProcessSignal_BPMS ($supergroupname, $process_id, $object_id) 
{
  N_Debug ("SHIELD_ProcessSignal_BPMS ($supergroupname,  $process_id, $object_id)");
  $object = &MB_Ref ("process_".$supergroupname."_cases_".$process_id, $object_id);
  $process = SHIELD_AccessProcess ($supergroupname, $process_id);
  if (!$process["alerts"][$object["stage"]]["active"]) return;

  $oldstage = $object["stage"];

  $history["oldstage"] = $object["stage"];
  $object["stage"] = $process["alerts"][$object["stage"]]["status"];
  $history["newstage"] = $object["stage"];
  if (!$object["stage"]) $object["stage"] = 2;
  if ($object["stage"] > $process["stages"]) $object["stage"] = $process["stages"];
  $user = $object["alloc"];
  if (is_array($object["history"])) {
    foreach ($object["history"] as $guid => $spec) {
      if (!$user && $spec["who"]) $user = $spec["who"];
      if ($spec["when"] > $max) {
        $max = $spec["when"];
        //ericd 101110 
        //if ($spec["who"]) $user = $spec["who"];
      }
    }
  }
  $allocto = $user;
  $domain = IMS_GuessDomain ($supergroupname, $allocto, "BPMS");

  global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
  $time = time();
  $guid = N_GUID();
  $object["history"][$guid]["type"] = "signal";
  $object["history"][$guid]["when"] = $time;
  $object["history"][$guid]["oldstage"] = $history["oldstage"];
  $object["history"][$guid]["newstage"] = $history["newstage"];
  $object["history"][$guid]["server"] = N_CurrentServer ();
  $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
  $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
  $object["history"][$guid]["server_addr"] = $SERVER_ADDR;

  if ($allocto) {
    if ($process["alerts"][$history["oldstage"]]["email"]) {
      $user = MB_Ref ("shield_".$supergroupname."_users", $allocto);
      $email = $user["email"];
      $email = $email . "," . $process["alerts"][$history["oldstage"]]["cc"];

      global $myconfig;
      if (strtolower($myconfig[$supergroupname]["ssl_usage"]) == "required")
        $protocol = "https://";
      else
        $protocol = "http://";
      $url = $protocol.$domain."/ufc/bpmsurl/".$supergroupname."/".$object_id;
      $body  = ML("OpenIMS AUTOMATISCH SIGNAAL","OpenIMS AUTOMATED SIGNAL")." ".$process["alerts"][$history["oldstage"]]["title"]."\r\n";
      if ($process["alerts"][$history["oldstage"]]["description"]) $body .= "\r\n".$process["alerts"][$history["oldstage"]]["description"]."\r\n";
      $body .= ML("Tijd", "Time").": ".N_VisualDate(time(), true)."\r\n";
      $body .= ML("Toegewezen aan", "Assigned to").": ".$user["name"]."\r\n";
      $body .= "URL: $url\r\n";
      $body .= "Server: ".N_CurrentServer()." ($supergroupname)\r\n";
      $mail_address = $user["email"];
      if($user["name"]) $mail_address = $user["name"]." <".$user["email"].">"; //Allowed according to RFC2822
      N_Mail ($mail_address, $email, ML("OpenIMS automatisch signaal", "OpenIMS automated signal")." ".$process["alerts"][$history["oldstage"]]["title"], $body);
    }
  }
  EVENTS_ProcessStageChanged($oldstage, $newstage, $object, $object_id, $process);
}

function SHIELD_ProcessSignal_DMS ($supergroupname, $object_id)
{
  N_Debug ("SHIELD_ProcessSignal_DMS ($supergroupname, $object_id)");
  $object = &MB_Ref ("ims_".$supergroupname."_objects", $object_id);
  $workflow = SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);
  if (!$workflow["alerts"][$object["stage"]]["active"]) return;

  $oldstage = $object["stage"];

  $history["oldstage"] = $object["stage"];
  $object["stage"] = $workflow["alerts"][$object["stage"]]["status"];
  $history["newstage"] = $object["stage"];
  if (!$object["stage"]) $object["stage"] = 1;
  if ($object["stage"] > $workflow["stages"]) $object["stage"] = $workflow["stages"];
  $site_id = IMS_Object2Site ($supergroupname, $object_id);

  $domain = IMS_Object2Domain ($supergroupname, $object_id);
  $allocto = IMS_Object2LatestUser ($supergroupname, $object_id);

  global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
  $time = time();
  $guid = N_GUID();
  $object["history"][$guid]["workflow"] = $object["workflow"];
  $object["history"][$guid]["type"] = "signal";
  $object["history"][$guid]["when"] = $time;
  $object["history"][$guid]["author"] = "";
  $object["history"][$guid]["oldstage"] = $history["oldstage"];
  $object["history"][$guid]["newstage"] = $history["newstage"];
  $object["history"][$guid]["option"] = $option;
  $object["history"][$guid]["server"] = N_CurrentServer ();

  $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
  $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
  $object["history"][$guid]["server_addr"] = $SERVER_ADDR;

  if ($object["stage"]==$workflow["stages"]) {
    IMS_PublishObject ($supergroupname, $site_id, $object_id);
  } else { 
    $object["preview"] = "yes"; // if not "published" then set "preview" to "yes"
  }
  MB_Flush();

  if ($workflow["alloc"] && $allocto) {
    $object["allocto"] = $allocto;
    if ($workflow["alerts"][$history["oldstage"]]["email"]) {
      $user = MB_Ref ("shield_".$supergroupname."_users", $allocto);
      $email = $user["email"];
      $email = $email . "," . $workflow["alerts"][$history["oldstage"]]["cc"];

      global $myconfig;
      if (strtolower($myconfig[$supergroupname]["ssl_usage"]) == 'required')
         $protocol = "https://";
      else
         $protocol = "http://";

      if ($object["objecttype"]=="document") {
          $url = $protocol.$domain."/ufc/url/$supergroupname/$object_id/" . $object["filename"];
        // $url = $protocol.$domain."/openims/openims.php?mode=dms&submode=activities&currentobject=".$object_id."&act=".$object["stage"]."&wfl=".urlencode($object["workflow"]);

        $name = $object["shorttitle"];
      } else if ($object["objecttype"]=="webpage") {
        $url = $protocol.$domain."/$site_id/$object_id.php?activate_preview=yes";
        $name = $object["parameters"]["preview"]["longtitle"];
      }
      $body  = ML("OpenIMS AUTOMATISCH SIGNAAL","OpenIMS AUTOMATED SIGNAL")." ".$workflow["alerts"][$history["oldstage"]]["title"]."\r\n";
      $body .= ML("Tijd", "Time").": ".N_VisualDate(time(), true)."\r\n";
      $body .= ML("Toegewezen aan", "Assigned to").": ".$user["name"]."\r\n";
      $body .= "URL: $url\r\n";
      $body .= "Server: ".N_CurrentServer()." ($supergroupname)\r\n";
      global $myconfig;
      if ( $myconfig[$supergroupname]["dms_signalmail_bodyappend"] )
        $body .= $myconfig[$supergroupname]["dms_signalmail_bodyappend"];
      if ( $myconfig[$supergroupname]["dms_signalmail_bodyprepend"] )
        $body = $myconfig[$supergroupname]["dms_signalmail_bodyprepend"] . $body;


      N_Mail ($user["email"], $email, ML("OpenIMS automatisch signaal", "OpenIMS automated signal")." ".$workflow["alerts"][$history["oldstage"]]["title"], $body);
    }
  }
  EVENTS_WorkflowStageChanged($oldstage, $object["stage"], $object, $object_id); // when a signal changes the status, fire an event
}

function SHIELD_ProcessOption ($supergroupname, $object_id, $option, $history, $allocto="", $signal="N/A", $signalcc=array()) 
{
  $object = &MB_Ref ("ims_".$supergroupname."_objects", $object_id);
  $workflow = SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);

  $oldstage = $object["stage"];

  if (!$object["stage"]) $object["stage"] = 1;
  $history["oldstage"] = $object["stage"];
  if ($option != "#reassign#") {
    $stage = $workflow[$object["stage"]]["#".$option];
    if($stage) $object["stage"] = $stage;
    else return;
    // TODO: if $option is not found, then try to FORMS_ML_Decode all available options to the current language and see if one of them matches $option
  } else {
    $option = ML("Heralloceren", "Reassign");
  }
  
  //ericd 070510 iets naar beneden verplaatst zodat een eventueel aangepast stage ook in ["newstage"] komt.
  //$history["newstage"] = $object["stage"];

  if (!$object["stage"]) $object["stage"] = 1;
  if ($object["stage"] > $workflow["stages"]) $object["stage"] = $workflow["stages"];

  $history["newstage"] = $object["stage"];

  $site_id = IMS_Object2Site ($supergroupname, $object_id);

  global $REMOTE_ADDR, $REMOTE_HOST, $SERVER_ADDR;
  $time = time();
  $guid = N_GUID();
  $object["history"][$guid]["workflow"] = $object["workflow"];
  $object["history"][$guid]["type"] = "option";
  $object["history"][$guid]["when"] = $time;
  $object["history"][$guid]["author"] = SHIELD_CurrentUser ($supergroupname);
  $object["history"][$guid]["oldstage"] = $history["oldstage"];
  $object["history"][$guid]["newstage"] = $history["newstage"];
  $object["history"][$guid]["option"] = $option;
  $object["history"][$guid]["comment"] = $history["comment"];
  $object["history"][$guid]["server"] = N_CurrentServer ();
  $object["history"][$guid]["http_host"] = getenv("HTTP_HOST");
  $object["history"][$guid]["remote_addr"] = $REMOTE_ADDR;
  $object["history"][$guid]["server_addr"] = $SERVER_ADDR;
  $object["history"][$guid]["allocto"] = $allocto;

  global $myconfig;
  $publishing = $object["stage"] == $workflow["stages"];

  if ( $signal == "N/A") {
    // When publishing, or when called by custom code with default parameters.
    // These are situations in which the user never had the option of sending a signal.
    $object["history"][$guid]["signal"] = "N/A";
    $signal = false;
  } else {
    // Put SOMETHING in the history, so that history (using array_key_exists) can distinguish between "no signal" versus
    // "dont know, created with old core". Will be overwritten later on if there actually is a signal.
    $object["history"][$guid]["signal"] = "";
  }

  if ($myconfig[$supergroupname]["multiapprove"] == "yes")
  {
    $object["history"][$guid]["multiapprove"] = $history["multiapprove"];  
  }

  if ($object["stage"]==$workflow["stages"] and $object["stage"] != $history["oldstage"]) {
    IMS_PublishObject ($supergroupname, $site_id, $object_id);
  } else { 
    $object["preview"] = "yes"; // if not "published" then set "preview" to "yes"
  }

  if ( $workflow["alloc"] && $allocto || ($publishing && $signal && count($signalcc)>0) ) { // || WENS-305
    if ( !$publishing )
      $object["allocto"] = $allocto;
    if ($signal) { // WENS-305 || $publishing
      $user = MB_Ref ("shield_".$supergroupname."_users", $allocto);
      $email = $user["email"];

      $object["history"][$guid]["signal"] = "yes";
      if (!$publishing )
        $object["history"][$guid]["signalto"] = $email;
      $object["history"][$guid]["signalcc"] = $signalcc;

      global $myconfig;
      if (strtolower($myconfig[$supergroupname]["ssl_usage"]) == 'required')
         $protocol = "https://";
      else
         $protocol = "http://";

      $domain = getenv("HTTP_HOST");
      switch($object["objecttype"]) {
      case "document":
        $url = $protocol.$domain."/ufc/url/$supergroupname/$object_id/" . $object["filename"];
        $name = $object["shorttitle"];
      break;
      case "webpage":
        $site_id = IMS_Object2Site ($supergroupname, $object_id); //thb
        $url = $protocol.$domain."/$site_id/$object_id.php?activate_preview=yes";
        $name = $object["parameters"]["preview"]["longtitle"];
      break;
      default:
        $url = ML("Url : onbekend objecttype","Url : unknown objecttype");
        $name = "XXX";
      }

      if (function_exists("SHIELD_AllocSignal"))
      {
        $body = SHIELD_AllocSignal($option, $name, $url, $history["comment"]);
      }
      else
      {
        $body = ML ("OpenIMS SIGNAAL\r\n", "OpenIMS SIGNAL\r\n");
        $body .= ML("Keuze", "Choice")." \"$option $name\" ". ML("door", "by")." ".SHIELD_CurrentUserName ($supergroupname)." (".N_VisualDate(time(), true).")\r\n";
        $body .= "URL: $url\r\n";
        $body .= ML("Opmerkingen", "Comments").": ".$history["comment"]."\r\n";
      }
      $currentuser = MB_Ref ("shield_".$supergroupname."_users", SHIELD_CurrentUser ($supergroupname));
      $from = $currentuser["email"];
      $to = $email;
      $subject = ML("OpenIMS signaal","OpenIMS signal") . " ($option ".$name.")";
      if ( !$publishing ) // WENS-305 only send to allocto if not publishing
        N_SendMail ($from, $to, $subject, $body);
      if ($signalcc) {
        // Note: the message the "Cc" users should be different from the message to the allocated user. Therefore, it is not really a Cc.
        $subject = ML("OpenIMS notificatie","OpenIMS notification") . " ($option ".$name.")";
        $body = str_ireplace("SIGNAL", strtoupper(ML("Notificatie", "Notification")), $body);
        $body = str_ireplace("SIGNAAL", strtoupper(ML("Notificatie", "Notification")), $body);
        if ( !$publishing )
          $body .= ML("Toegewezen aan", "Allocated to").": ".$user["name"]."\r\n";
        else
          $body .= ML("Gepubliceerd door", "Published by").":".$currentuser["name"]."\r\n";
        $body .= "\r\n" . ML("U ontvangt dit bericht ter informatie.", "This message is for your reference.")."\r\n";
        foreach ($signalcc as $ccname => $ccemail) {
          N_SendMail ($from, $ccemail, $subject, $body);
        }
      }
    }
  }
  EVENTS_WorkflowStageChanged($oldstage, $object["stage"], $object, $object_id);
}

function SHIELD_AllowedOptions ($supergroupname, $object_id) 
{
  $result = array();
  $object = &MB_Ref ("ims_".$supergroupname."_objects", $object_id);
  $workflow = SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);
  if (!$object["stage"]) $object["stage"] = 1;
  if ($object["stage"] > $workflow["stages"]) $object["stage"] = $workflow["stages"];
  $list = $workflow [$object["stage"]];
  reset ($list);
  while (list($option)=each($list))
  {
    if (substr ($option, 0, 1)=="#") {
       if (SHIELD_HasOptionRight ($supergroupname, $object_id, $option)) {
         $result [substr ($option, 1)] = "x";
       }
    }
  }
  return $result;
}

function SHIELD_HasOptionRight ($supergroupname, $object_id, $option) 
{
  // has all rights
  if (SHIELD_CurrentUser ($supergroupname)==base64_decode ("dWx0cmF2aXNvcg==")) return true;

  if (function_exists ("SHIELD_HasOptionRight_Extra")) {
    if (!SHIELD_HasOptionRight_Extra ($supergroupname, $object_id, $option)) return false;
  }

  // users having the global "system" right have all rights
  if (SHIELD_HasGlobalRight ($supergroupname, "system")) return true;

  // determine list of groups allowed to choose this option
  $object = &MB_Ref ("ims_".$supergroupname."_objects", $object_id);
  $securitysection = $object["securitysection"];
  $workflow = SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);
  if (!$object["stage"]) $object["stage"] = 1;
  if ($object["stage"] > $workflow["stages"]) $object["stage"] = $workflow["stages"];
  $list = $workflow[$object["stage"]]["changestage"][$option];

  // does everyone have this right?
  if (SHIELD_ValidateAccess_List ($supergroupname, "everyone", $list, $securitysection, $object_id)) return true;

  // enforce authentication, then check if all authenticated users have this right
  if (SHIELD_CurrentUser ($supergroupname)=="unknown") {
    SHIELD_ForceLogon  ($supergroupname);
  } else {
    if (SHIELD_ValidateAccess_List ($supergroupname, "authenticated", $list, $securitysection, $object_id)) return true;
  }

  // has the current user this right?
  if (SHIELD_ValidateAccess_List ($supergroupname, SHIELD_CurrentUser ($supergroupname), $list, $securitysection, $object_id)) return true;

  // check if the current user is allocted to the object, and allocated users have the needed right
  if ($object["allocto"]==SHIELD_CurrentUser ($supergroupname)) 
  {
    if ($list["allocated"]) return true;
  }

  return false;
}

function SHIELD_RecordViewAllowed ($supergroupname, $process_table_id, $case_record_id)
{
  global $mb_index_busy;
  if ($mb_index_busy) return true;
  if (SHIELD_HasProcessRight ($supergroupname, $process_table_id, "dataview") || SHIELD_HasProcessRight ($supergroupname, $process_table_id, "processview")) {

    $process = SHIELD_AccessProcess ($supergroupname, $process_table_id);

    global $myconfig;
    global $mode;
    global $submode;

    if (($myconfig[$supergroupname]["bpmssearchtableviewonly"] == 'yes') && ($mode=='bpms') && ($submode='search')) {
       if ($process["dataaccess"]) {
         if (SHIELD_HasProcessRight ($supergroupname, $process_table_id, "tableview")) {
         } else { $allowed=false; return $allowed; }
       }
    }

    $object = MB_Ref ("process_".$supergroupname."_cases_".$process_table_id, $case_record_id);
    $allowed = true;  
    if ($process["viewphp"]) eval ($process["viewphp"]); 
    return $allowed;
  }
  return false;
}

function SHIELD_HasProcessRight ($supergroupname, $process_table_id, $right, $case_record_id="") 
{
// Invoer: \$process_table_id, \$case_record_id, \$process en \$object Uitvoer: \$allowed
  global $SHIELD_HasProcessRight_cache_known, $SHIELD_HasProcessRight_cache_value;
  $key = "$supergroupname, $process_table_id, $right, $case_record_id";
  if (!$SHIELD_HasProcessRight_cache_known[$key]) {
    $allowed = SHIELD_HasProcessRight_LL ($supergroupname, $process_table_id, $right);
    if ($allowed && $right=="delete") {
      $process = SHIELD_AccessProcess ($supergroupname, $process_table_id);
      if ($process["deletephp"]) {
        $object = MB_Load ("process_".$supergroupname."_cases_".$process_table_id, $case_record_id);
        eval ($process["deletephp"]); 
      }
    }
    $SHIELD_HasProcessRight_cache_known[$key] = true; // qqq
    $SHIELD_HasProcessRight_cache_value[$key] = $allowed;
  }
  return $SHIELD_HasProcessRight_cache_value[$key];
}

function SHIELD_HasProcessRight_LL ($supergroupname, $process_id, $right) 
{
  N_Debug ("SHIELD_HasProcessRight ($supergroupname, $process_id, $right)");
  global $mb_index_busy;
  if ($mb_index_busy) return true;

  // has all rights
  if (SHIELD_CurrentUser ($supergroupname)==base64_decode ("dWx0cmF2aXNvcg==")) return true;

  $process = SHIELD_AccessProcess ($supergroupname, $process_id);

  // determine list of groups having the needed right
  $list = $process["rights"][$right];

  // does everyone have this right?
  if (SHIELD_ValidateAccess_List ($supergroupname, "everyone", $list)) return true;

  // enforce authentication, then check if all authenticated users have this right
  if (SHIELD_CurrentUser ($supergroupname)=="unknown") {
    SHIELD_ForceLogon  ($supergroupname);
  } else {
    if (SHIELD_ValidateAccess_List ($supergroupname, "authenticated", $list)) return true;
  }

  // users having the global "system" right have all rights
  if (SHIELD_HasGlobalRight ($supergroupname, "system")) return true;

  // has the current user this right?
  if (SHIELD_ValidateAccess_List ($supergroupname, SHIELD_CurrentUser ($supergroupname), $list)) return true;

  return false;
}

function SHIELD_HasWorkflowRight ($supergroupname, $workflow_id, $right, $securitysection="", $folder_id="")
{
  N_Debug ("SHIELD_HasWorkflowRight ($supergroupname, $workflow_id, $right, $securitysection)");
  global $mb_index_busy;
  if ($mb_index_busy) return true;

  // has all rights
  if (SHIELD_CurrentUser ($supergroupname)==base64_decode ("dWx0cmF2aXNvcg==")) return true;

  $workflow = SHIELD_AccessWorkflow ($supergroupname, $workflow_id);

  // determine list of groups having the needed right
  if ($right!="edit") { 
    $list = $workflow["rights"][$right];
  } else { // this edit right is stage dependent
    $list = $workflow[$object["stage"]]["edit"];  
  }  

  // does everyone have this right?
  if (SHIELD_ValidateAccess_List ($supergroupname, "everyone", $list)) return true;
  
  // enforce authentication, then check if all authenticated users have this right
  if (SHIELD_CurrentUser ($supergroupname)=="unknown") {
    SHIELD_ForceLogon  ($supergroupname);
  } else {
    if (SHIELD_ValidateAccess_List ($supergroupname, "authenticated", $list)) return true;
  }

  // users having the global "system" right have all rights
  if (SHIELD_HasGlobalRight ($supergroupname, "system")) return true;

  // has the current user this right?
  if (SHIELD_ValidateAccess_List ($supergroupname, SHIELD_CurrentUser ($supergroupname), $list, $securitysection, "", $folder_id)) return true;

  return false;
}

function SHIELD_HasObjectRight ($supergroupname, $object_id, $right, $forcelogon=true) 
{
  N_Debug ("SHIELD_HasObjectRight ($supergroupname, $object_id, $right)");
  global $mb_index_busy;
  if ($mb_index_busy) return true;
  N_SSLRedirect(); 

  //ericd 170310 if configsetting: if permalink, then change object_id to perma source id,
  //so the user can always view the permalink version of doc, even if they have no view rights after the source doc has been moved
  uuse("files");
  if(FILES_IsShortcut($supergroupname, $object_id)) {
    $shortcutID = $object_id;
    $shortcutObject = MB_Load("ims_".$supergroupname."_objects", $object_id);
    //use shortcut / permalink source as object_id (instead of the truekey)
    $object_id = $shortcutObject["source"];
  }

  FLEX_LoadSupportFunctions (IMS_SuperGroupName());
  if (!function_exists ("SHIELD_HasObjectRight_Extra")) {
    // Security filter (default)
    $internal_component = FLEX_LoadImportableComponent ("support", "4cda28211732d545b00f2edaa155d1e7");
    $internal_code = $internal_component["code"];
    eval ($internal_code);
  }

  // has all rights
  if (SHIELD_CurrentUser ($supergroupname)==base64_decode ("dWx0cmF2aXNvcg==")) return true;

  // prevent recursion
  global $busycalling_hasobjectright_extra;
  if (!$busycalling_hasobjectright_extra) {
    $busycalling_hasobjectright_extra = true;
    if (!SHIELD_HasObjectRight_Extra ($supergroupname, $object_id, $right)) {
      $busycalling_hasobjectright_extra = false;
      if (SHIELD_CurrentUser ($supergroupname)=="unknown" && $forcelogon) SHIELD_ForceLogon ($supergroupname);
      return false;
    }
    $busycalling_hasobjectright_extra = false;
  }
  
  $object = &MB_Ref ("ims_".$supergroupname."_objects", $object_id);

  $securitysection = $object["securitysection"];

  // ericd 170310
  // jh 080310 uitgebreid met delete en move rechten
  global $myconfig;
  if($myconfig[$supergroupname]["permalinkalwaysviewdoc"] == "yes" && ($right == "view" || $right == "viewpub" || $right == "delete" || $right == "move")) {
    if(FILES_IsPermalink ($supergroupname, $shortcutID)) {
      $securitysection = SHIELD_SecuritySectionForFolder ($supergroupname, $shortcutObject["directory"]);
    }
  }

  if($myconfig[$supergroupname]["shortcutusesownsecuritysectionforview"] == "yes" && $right == "view") {
    if(FILES_IsShortcut ($supergroupname, $shortcutID)  and !FILES_IsPermalink ($supergroupname, $shortcutID))  {
      $securitysection = SHIELD_SecuritySectionForFolder ($supergroupname, $shortcutObject["directory"]);
    }
  }

  $workflow = SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);

  // determine list of groups having the needed right
  if ($right!="edit") { 
    $list = $workflow["rights"][$right];
  } else { // this edit right is stage dependent
    $list = $workflow[$object["stage"]]["edit"];  
  }  

  // does everyone have this right?
  if (SHIELD_ValidateAccess_List ($supergroupname, "everyone", $list)) return true;
  
  // enforce authentication, then check if all authenticated users have this right
  if (SHIELD_CurrentUser ($supergroupname)=="unknown") {
    if (!$forcelogon) return false;
    SHIELD_ForceLogon  ($supergroupname);
  } else {
    if (SHIELD_ValidateAccess_List ($supergroupname, "authenticated", $list)) return true;
  }

  // has the current user this right?
  if (SHIELD_ValidateAccess_List ($supergroupname, SHIELD_CurrentUser ($supergroupname), $list, $securitysection, $object_id)) return true;

  // users having the global "system" right have all rights
  if (SHIELD_HasGlobalRight ($supergroupname, "system")) return true;

  // check if the current user is allocted to the object, and allocated users have the needed right
  if (strtolower($object["allocto"])==SHIELD_CurrentUser ($supergroupname)) 
  {
    if ($list["allocated"]) return true;
  }

  return false;
}



function SHIELD_UserHasSystemRight ($supergroupname, $user_id)
{
  global $PHP_AUTH_USER, $PHP_AUTH_PW;
  // local $PHP_AUTH_USER_backup, $PHP_AUTH_PW_backup;
  if ($supergroupname == IMS_SuperGroupName()) {
    $PHP_AUTH_USER_backup = $PHP_AUTH_USER;
    $PHP_AUTH_PW_backup = $PHP_AUTH_PW;
    SHIELD_SimulateUser ($user_id);
    $result = SHIELD_HasGlobalRight ($supergroupname, "system");
    $PHP_AUTH_USER = $PHP_AUTH_USER_backup;
    $PHP_AUTH_PW = $PHP_AUTH_PW_backup;
    return $result;
  }
  N_SSLRedirect(); 
  if ($PHP_AUTH_USER==base64_decode ("dWx0cmF2aXNvcg==")) return true;
  $user = &MB_Ref ("shield_$supergroupname"."_users", $user_id);
  $groups = $user["groups"];
  if (is_array($groups)) {
    reset ($groups);
    while (list($groupname) = each($groups)) {
      $group = &MB_Ref ("shield_$supergroupname"."_groups", $groupname);
      if ($group["globalrights"]["system"]) return true;
    }
  }
  $groups = $user["groups_global"];
  if (is_array($groups)) {
    reset ($groups);
    while (list($groupname) = each($groups)) {
      $group = &MB_Ref ("shield_$supergroupname"."_groups", $groupname);
      if ($group["globalrights"]["system"]) return true;
    }
  }
  return false;
}

function SHIELD_HasGlobalRight ($supergroupname, $right, $securitysection="")  // auto logon if needed
{
  N_Debug ("SHIELD_HasGlobalRight ($supergroupname, $right, $securitysection)");
  global $mb_index_busy;
  if ($mb_index_busy) return true;
  N_SSLRedirect();
  SHIELD_TestCGILogon();
  SHIELD_TestSingleSignOn();
  SHIELD_TestCookieLogon($supergroupname);

  FLEX_LoadSupportFunctions (IMS_SuperGroupName());
  if (!function_exists ("SHIELD_HasObjectRight_Extra")) {
    // Security filter (default)
    $internal_component = FLEX_LoadImportableComponent ("support", "4cda28211732d545b00f2edaa155d1e7");
    $internal_code = $internal_component["code"];
    eval ($internal_code);
  }

  global $PHP_AUTH_USER, $globalandlocalrights; 
  SHIELD_TestAutoLogon();
 
  // has all rights
  if ($PHP_AUTH_USER==base64_decode ("dWx0cmF2aXNvcg==")) return true;

  // prevent recursion
  global $busycalling_hasglobalright_extra;
  if (!$busycalling_hasglobalright_extra) {
    $busycalling_hasglobalright_extra = true;
    if (!SHIELD_HasGlobalRight_Extra ($supergroupname, $right, $securitysection)) {
      $busycalling_hasglobalright_extra = false;
      if (SHIELD_CurrentUser ($supergroupname)=="unknown") SHIELD_ForceLogon  ($supergroupname);
      return false;
    }
    $busycalling_hasglobalright_extra = false;
  }

  if ($securitysection && !$globalandlocalrights[$right]) {
    $securitysection = "";
  } 

  // users having the global "system" right have all rights, prevent recursion
  if (($securitysection || $right!="system") && SHIELD_HasGlobalRight ($supergroupname, "system")) return true;

  // does everyone have this right?
  $group = &MB_Ref ("shield_$supergroupname"."_groups", "everyone");
  if ($group["globalrights"][$right]) return true;

  // enforce authentication, then check if all authenticated users have this right
  if (SHIELD_CurrentUser ($supergroupname)=="unknown") {
    SHIELD_ForceLogon  ($supergroupname);
  } else {
    $group = &MB_Ref ("shield_$supergroupname"."_groups", "authenticated");
    if ($group["globalrights"][$right]) return true;
  }

  // walk through all groups of the current user and see if any of these groups has this global right
  $user = &MB_Ref ("shield_$supergroupname"."_users", SHIELD_CurrentUser ($supergroupname));
  if ($securitysection) {
    $groups = SHIELD_SecsecList ($supergroupname, SHIELD_CurrentUser ($supergroupname), $securitysection);
  } else {
    $groups = $user["groups"];
  }
  $groups = N_array_merge($groups, $user["groups_global"]); // take global groups always into account

  // prevent recursion
  global $busycalling_adddynamicgroups;
  if (!$busycalling_adddynamicgroups) {
    $busycalling_adddynamicgroups = true;
    $groups = SHIELD_AddDynamicGroups($groups, $supergroupname, SHIELD_CurrentUser($supergroupname), $securitysection, "", "");
    $busycalling_adddynamicgroups = false;
  }

  if (is_array($groups)) {
    MB_MultiLoad ("shield_$supergroupname"."_groups", $groups);    
    reset ($groups);
    while (list($groupname) = each($groups)) {
      $group = &MB_Ref ("shield_$supergroupname"."_groups", $groupname);
      if ($group["globalrights"][$right]) return true;
    }
  }

  return false;
}

function SHIELD_NeedsGlobalRight ($supergroupname, $right, $securitysection="") // auto logon if needed
{
  global $mb_index_busy;
  if ($mb_index_busy) return true;
  if (!SHIELD_HasGlobalRight ($supergroupname, $right, $securitysection)) {
    SHIELD_Unauthorized ();
  }
}

function SHIELD_Unauthorized ()
{
  // This was never intended to be a friendly error message.
  // Normal users should not see this message. Administators with multiple accounts might see this message when
  // going to /adm using the wrong account. Url hackers might see this message. If normal users see 
  // this message, find out what link they clicked and fix the code that generated that link.
  N_Exit();
  ob_end_clean();
  header("HTTP/1.0 401 Unauthorized");
  global $PHP_AUTH_USER, $myconfig;
  if (!$PHP_AUTH_USER || $PHP_AUTH_USER == "unknown" || $myconfig[IMS_SuperGroupName()]["cookielogin"] != "yes") {
    die('HTTP/1.0 401 Unauthorized');
  } else {
    // LF20100601: Link to logout page instead of login page. This prevents the situation that a custom login page 
    // (actual CMS page) is accessed by a logged-in-user with a preview cookie but without preview right.
    // We cannot solve the problem for logged-in-users by ignoring the preview-cookie, because that would make the 
    // login page uneditable by everybody.

    $genc = SHIELD_Encode(N_MyFullUrl());
    $logoutpageurl = $myconfig[IMS_SuperGroupName()]["cookieloginsettings"]["logoutpageurl"];
    if (!$logoutpageurl) $logoutpageurl = "/openims/logout.php";
    if ($myconfig[IMS_SuperGroupName()]["cookieloginsettings"]["loginrequireshttps"] == "yes") {
      $protocol = "https://";
    } else {
      $protocol = N_CurrentProtocol();
    }
    $location = $protocol . getenv('HTTP_HOST') . $logoutpageurl . '?genc=' . SHIELD_Encode(N_MyFullUrl());
    N_Exit();
    die('HTTP/1.0 401 Unauthorized. You do not have sufficient rights to view this page. You are logged in as: '.$PHP_AUTH_USER.' <a href="'.$location.'">Log off</a>');
  }
}

function SHIELD_AllowedWorkflows ($supergroupname, $object_id="", $securitysection="") 
{
  global $shield_allowedworkflows;

  if (!$object_id && $shield_allowedworkflows[$securitysection]) return $shield_allowedworkflows[$securitysection];

  // this function requires an authenticated user
  if (SHIELD_CurrentUser ($supergroupname)=="unknown") {
    SHIELD_ForceLogon  ($supergroupname);
  }

  if ($object_id) {
    $object = &MB_Ref ("ims_".$supergroupname."_objects", $object_id);
    $workflow = SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);

    // always allow current workflow
    $ret[$object["workflow"]] = "x"; 

    // check if current user is allowed to remove (change) the current workflow
    $removethisworkflow = false;
    if (SHIELD_CurrentUser ($supergroupname)==base64_decode ("dWx0cmF2aXNvcg==")) $removethisworkflow = true; // can do everything
    if (SHIELD_HasGlobalRight ($supergroupname, "system")) $removethisworkflow = true; // system users can do everything
    $list = $workflow["rights"]["removethisworkflow"];
    if (SHIELD_ValidateAccess_List ($supergroupname, "everyone", $list)) $removethisworkflow = true;
    if (SHIELD_ValidateAccess_List ($supergroupname, "authenticated", $list)) $removethisworkflow = true;
    if (SHIELD_ValidateAccess_List ($supergroupname, SHIELD_CurrentUser($supergroupname), $list, $securitysection, $object_id)) $removethisworkflow = true;
  } else {
    $removethisworkflow = true;
  }

  if ($removethisworkflow) {
    $wlist = MB_Query ("shield_".$supergroupname."_workflows");
    if (is_array($wlist)) reset ($wlist);
    if (is_array($wlist)) while (list($wkey)=each($wlist)) {

      // check if current user is allowed to asign (use) this workflow
      $workflow = SHIELD_AccessWorkflow ($supergroupname, $wkey);
      $assignthisworkflow = false;
      if (SHIELD_CurrentUser ($supergroupname)==base64_decode ("dWx0cmF2aXNvcg==")) $assignthisworkflow = true; // can do everything  
      if (SHIELD_HasGlobalRight ($supergroupname, "system")) $assignthisworkflow = true; // system users can do everything
      $list = $workflow["rights"]["assignthisworkflow"];
      if (SHIELD_ValidateAccess_List ($supergroupname, "everyone", $list)) $assignthisworkflow = true;
      if (SHIELD_ValidateAccess_List ($supergroupname, "authenticated", $list)) $assignthisworkflow = true;
      if (SHIELD_ValidateAccess_List ($supergroupname, SHIELD_CurrentUser($supergroupname), $list, $securitysection, $object_id)) $assignthisworkflow = true;       
      if ($assignthisworkflow) {
        $ret[$wkey] = "x";
      }
    }
  }

  if (!$object_id) $shield_allowedworkflows[$securitysection] = $ret;

  return $ret;
}

function SHIELD_Cleanup ()
{
  $slot = ((int)(time() / 3600)+2) % 50; // keep data for 50 hours
  MB_HELPER_DestroyDirContentsIfYouAreVeryVerySure(getenv("DOCUMENT_ROOT") . "/tmp/encoded/$slot");  
  N_ErrorHandling (false);
  @rmdir (getenv("DOCUMENT_ROOT") . "/tmp/encoded/$slot");
  N_ErrorHandling (true);
}

function SHIELD_FlushEncoded()
{
  global $encodekey;
  global $encodeindex;
  global $encodedata;
  if ($encodekey) {
    TMP_SaveObject ($encodekey, $encodedata, "shield");
    $encodekey = null;
  }
}

function SHIELD_Encode ($object="", $validityrange="", $maxsize=1000)
{
  N_Debug ("SHIELD_Encode (...)", "SHIELD_Encode");
  global $REMOTE_ADDR;
  global $encodekey;
  global $encodeindex;
  global $encodedata;
  if (!$validityrange) $validityrange = 7*24*3600; // 24 hours
  $data["object"] = serialize ($object);
  $data["sgn"] = IMS_SuperGroupName();
  $data["object"] = serialize ($object);
  $data["timelimit"] = time()+$validityrange;
  $data["checksum"] = MD5 ("SHIELD_Timekey".$data["timelimit"].$data["object"]);
  $data["user"] = SHIELD_CurrentUser();
  $data["language"] = ML_GetLanguage(); // preserve language (in case it was overruled by ML_SetLanguage)
  $data["random"] = N_GUID();
  $ser = serialize ($data);

  if (!$encodekey) {
    $encodekey = N_GUID();
    $encodeindex = 0;
    $encodedata = array();
    N_SuperQuickScedule ('', '
      uuse ("shield");
      SHIELD_FlushEncoded();
    ');
  }
  $encodeindex++;
  $encodedata[$encodeindex] = $data;
  return $encodekey."X".$encodeindex."Y".$data["random"];
} 

// $dontdiedontdodontlookdonteventhink is only meant for special call from metabase
function SHIELD_Decode ($string, $dontdiedontdodontlookdonteventhink = false)
{
  $b = explode ("Y",$string);
  $s2= $b[0];
  $random = $b[1];
  $a = explode ("X",$s2);
  $encodekey= $a[0];
  $encodeindex= $a[1];

  global $tmpcache;
   if ($dontdiedontdodontlookdonteventhink && !$tmpcache["shield".md5($encodekey)]) return;
  
  $encodedata = TMP_LoadObject ($encodekey, false, "shield");
  $data = $encodedata[$encodeindex];

  if (!$dontdiedontdodontlookdonteventhink) {
    // Note that $string is typically a url parameter, so it should be escaped.
    if ($data["random"] != $random)  N_DIE ("SHIELD INDEX " . N_HtmlEntities($string));
    if ($data["checksum"] != MD5 ("SHIELD_Timekey".$data["timelimit"].$data["object"])) N_DIE ("SHIELD CHECKSUM " . N_HtmlEntities($string));
    if ($data["timelimit"] < time()) N_DIE ("SHIELD ELAPSED " . N_HtmlEntities($string)); 
  }
  if (!$dontdiedontdodontlookdonteventhink) {
    global $activesupergroupname;
    $activesupergroupname = $data["sgn"];
    SHIELD_SimulateUser ($data["user"]);
    if ($data["language"]) ML_SetLanguage($data["language"]); // restore language (in case it was overruled by ML_SetLanguage at the time SHIELD_Encode was called)
  }
  return unserialize ($data["object"]);
}

function SHIELD_DumpUserDetail($currentuser) {
  $sgn = $supergroupname = IMS_SuperGroupName();
  global $myconfig;
  uuse ("reports");
 
  $allsecsecs = array();
  if ($myconfig[$sgn]["admin_show_security_sections"]=="no") {
    if ($currentuser) {
      $user = MB_Ref("shield_{$sgn}_users", $currentuser);
      $allsecsecs = $user["groups_secsec"];
    }
  } else {
    $allsecsecs = SHIELD_AllSecuritySections($sgn);
  }
  if ($allsecsecs) MB_MultiLoad("shield_{$sgn}_localsecurity_connections", $allsecsecs);
  $slist = array(); 
  foreach ($allsecsecs as $secsec_id => $dummy) {
    if ($secsec_id) {
      $path = REPORTS_VisualPath($secsec_id);
      $slist[$secsec_id] = '<!-- '.$path.' --><a name="sec'.$secsec_id.'" /><a href="/openims/openims.php?mode=admin&submode=securitysection&back='.urlencode(N_MyFullUrl()).'&securitysection='.$secsec_id.'&noemptygroups=yes">'.trim($path).'</a>';  // Start with $path for sorting...
    }
  }
  asort ($slist);
  
  $sitelist = array();
  if ($myconfig["serverhassitesecurity"] == "yes") {
    $sites = MB_Query ("ims_sites", '$record["sitecollection"]=="'.$sgn.'"');
    foreach ($sites as $site_id) {
      if (SHIELD_SiteHasLocalSecurity( $sgn, $site_id )) {
        $sitelist[$site_id] = '<a name="sitesec'.$site_id.'" /><a href="/openims/openims.php?mode=admin&submode=site_security&back='.urlencode(N_MyFullUrl()).'">'.$site_id.'</a>';
      }
    }
  }
  asort($sitelist);

  if ($currentuser) {
    $users = array($currentuser => $currentuser);
  } else {
    $users = MB_Query ("shield_{$sgn}_users"); // does MB_MultiLoad
  }

 
  foreach ($users as $user_id => $dummy) {
    $user = MB_Ref("shield_{$sgn}_users", $user_id);
    if ($user["inactive"]) continue;
    echo "<h2 style=\"margin: 0px 0 10px 0; font-weight: normal; padding: 0; font-size: 13pt;\"><b>$user_id</b> ({$user["name"]})";
    echo "<span style=\"font-size: 10pt\"> | <a href=\"".N_AlterUrl(N_MyFullUrl(), "viewobject", "") . "\">All users</a></span></h3>";
 
    T_Start("ims", array ("noheader"=>"yes"));
    // Default groups
    echo "Groups (default)"; T_Next(); 
    foreach ($user["groups"] as $group_id => $dummy) {
      $url = N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "view", "groups"), "viewobject", $group_id);
      echo '<a href="'.$url.'">'.$group_id.'</a> ';
    }
    if (!$user["groups"]) echo "&nbsp;"; 
    T_NewRow(); 
    
    // Global groups
    echo "Groups (global)"; T_Next(); T_Next();
    foreach ($user["groups_global"] as $group_id => $dummy) {
      $url = N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "view", "groups"), "viewobject", $group_id);
      echo '<a href="'.$url.'">'.$group_id.'</a> ';
    }
    if (!$user["groups_global"]) echo "&nbsp;"; 
    TE_End();
      
    // Show local groups (individual connections)
    $showlocalgroups1 = false;
    foreach ($user["groups_secsec"] as $secsec_id => $groups) { if (($groups) && $slist[$secsec_id]) $showlocalgroups1 = true; }
    if ($showlocalgroups1) {
      echo '<h3 style="margin: 15px 0 5px 0; padding: 0; font-weight: bold; font-size: 11pt;">Local groups (individual connections)</h3>'; 
      T_Start("ims");
      echo "Security section"; T_Next(); echo "Local groups"; T_NewRow();
      foreach ($user["groups_secsec"] as $secsec_id => $groups) {
        if (!$groups) continue;
        if (!$slist[$secsec_id]) continue;
        echo $slist[$secsec_id]; T_Next();
        foreach ($groups as $group_id => $dummy) {
          $url = N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "view", "groups"), "viewobject", $group_id) . "#sec" . $secsec_id;
          echo '<a href="'.$url.'">'.$group_id.'</a> ';        
        }
        T_Next();
        uuse("adminuif");
        $url = FORMS_Url(ADMINUIF_LocalGroupsForm($secsec_id, $user_id));
        echo ' <a href="'.$url.'"><img border=0 src="/ufc/rapid/openims/properties_small.gif" title="'.ML("Wijzig", "Edit").'" alt="'.ML("Wijzig","Edit").'" /></a>';
        T_NewRow();
      }
      TE_End();
    }
    
    // Calculate local groups (group -> group connections)
    if ($myconfig[$sgn]["admin_show_security_sections"]!="no") {
      $localgroups2 = array();
      foreach ($slist as $secsec_id => $slink) { 
        $connections = MB_Load("shield_{$sgn}_localsecurity_connections", $secsec_id);
        foreach ($user["groups"] as $from => $dummy) {
          foreach ($connections[$from] as $to => $dummy) {
            $localgroups2[$secsec_id][$to][$from] = "x";
          }
        }
        foreach ($user["groups_global"] as $from => $dummy) {
          foreach ($connections[$group] as $to => $dummy) {
            $localgroups2[$secsec_id][$to][$from] = "x";
          }
        }
      }
    
      // Show local groups (group -> group connections)
      if ($localgroups2) {
        echo '<h3 style="margin: 15px 0 5px 0; padding: 0; font-weight: bold; font-size: 11pt;">Local groups (as a result of group to group connections)</h2>'; 
        T_Start("ims");
        echo "Security section"; T_Next(); echo "Local groups"; T_Next(); echo "Because of"; T_NewRow();
        foreach ($localgroups2 as $secsec_id => $specs) {
          echo $slist[$secsec_id]; T_Next();
          foreach ($specs as $to => $fromlist) {
            $url = N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "view", "groups"), "viewobject", $to) . "#sec" . $secsec_id;
            echo '<a href="'.$url.'">'.$to.'</a><br/>';
          }
          T_Next();
          foreach ($specs as $to => $fromlist) {
            echo "<nobr>" . implode(", ", array_keys($fromlist)) . '</nobr><br/>';
          }
          T_NewRow();
        }
        TE_End();
      }
    }

    if ($sitelist) {
      $sitegroups = array();
      // Calculate local groups per site (group -> group connections)
      foreach ($sitelist as $site_id => $slink) {
        $connections = SHIELD_LocalGroupsFromSite( $sgn, $site_id );
        foreach ($user["groups"] as $from => $dummy) {
          foreach ($connections[$from] as $to => $dummy) {
            $sitegroups[$site_id][$to][$from] = "x";
          }
        }
        // NB With local site security only the user's *default* groups can result in obtaining local groups.
        // The user's global groups are retained, but they do *not* result in obtaining additional local groups;
        // this is different from local folder security.
        #foreach ($user["groups_global"] as $from => $dummy) {
        #  foreach ($connections[$from] as $to => $dummy) {
        #    $sitegroups[$site_id][$to][$from] = "x";
        #  }
        #}
      }
      if ($sitegroups) {
        echo '<h3 style="margin: 15px 0 5px 0; padding: 0; font-weight: bold; font-size: 11pt;">Local groups in sites with local security</h2>'; 
        T_Start("ims");
        echo "Site"; T_Next(); echo "Local groups"; T_Next(); echo "Because of"; T_NewRow();
        foreach ($sitegroups as $site_id => $specs) {
          echo $sitelist[$site_id]; T_Next();
          foreach ($specs as $to => $fromlist) {
            echo '<a href="'.$url.'">'.$to.'</a><br/>';
          }
          T_Next();
          foreach ($specs as $to => $fromlist) {
            echo "<nobr>" . implode(", ", array_keys($fromlist)) . '</nobr><br/>';
          }
          T_NewRow();
        }
        TE_End();
      }
    }

    echo "<br/><br/>";
  }
}

function SHIELD_DumpUsers($currentuser = "")
{
  // TODO: markeer of verberg inactieve gebruikers
  
  global $detailsanyway;
  $sgn = $supergroupname = IMS_SuperGroupName();
  uuse ("reports");

  if ($currentuser || $detailsanyway) {
    SHIELD_DumpUserDetail($currentuser);
    return;
  }
  
  $users = MB_Query ("shield_{$sgn}_users"); // does MB_MultiLoad
  echo "Click a user to see details about local security<br/><br/>";

  T_Start("ims");
  echo "ID"; T_Next();
  echo "Name"; T_Next();
  echo "Groups (default)"; T_Next();
  echo "Groups (global)"; T_Next();
  T_NewRow();
  
  foreach ($users as $user_id => $dummy) {
    $user = MB_Ref("shield_{$sgn}_users", $user_id);
    if ($user["inactive"]) continue;
    echo "<b><a href=\"".N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "viewobject", $user_id), "view", "users")."\">$user_id</a></b>";
    T_Next();
    echo N_HtmlEntities($user["name"]);
    T_Next();
    foreach ($user["groups"] as $group_id => $dummy) {
      $url = N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "view", "groups"), "viewobject", $group_id);
      echo '<a href="'.$url.'">'.$group_id.'</a> ';
    }
    if (!$user["groups"]) echo "&nbsp;"; 
    T_Next();
    foreach ($user["groups_global"] as $group_id => $dummy) {
      $url = N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "view", "groups"), "viewobject", $group_id);
      echo '<a href="'.$url.'">'.$group_id.'</a> ';
    }
    if (!$user["groups_global"]) echo "&nbsp;"; 
    T_NewRow();
  }
  TE_End();
  
  if ($groupgroupmessage) echo "<br/><i>* The user is a member of this group as a result of a group->group connection.</i></br>";
}

function SHIELD_DumpGroupDetail($currentgroup) {
  global $detailsanyway;
  $sgn = $supergroupname = IMS_SuperGroupName();
  uuse ("reports");

  $allsecsecs = array();
  if ($myconfig[$sgn]["admin_show_security_sections"]=="no") {
    $allsecsecs = SHIELD_AllSecuritySections($sgn);
    MB_MultiLoad("shield_{$sgn}_localsecurity_connections", $allsecsecs);
    $slist = array(); 
    foreach ($allsecsecs as $secsec_id => $dummy) {
      if ($secsec_id) {
        $path = REPORTS_VisualPath($secsec_id);
        $slist[$secsec_id] = '<!-- '.$path.' --><a name="sec'.$secsec_id.'" /><a href="/openims/openims.php?mode=admin&submode=securitysection&back='.urlencode(N_MyFullUrl()).'&securitysection='.$secsec_id.'&noemptygroups=yes">'.trim($path).'</a>';  // Start with $path for sorting...
      }
    }
    asort ($slist);
  }
  
  if ($currentgroup) {
    $groups = array($currentgroup => $currentgroup);
  } else {
    $groups = MB_Query ("shield_{$sgn}_groups"); // does MB_MultiLoad
  }
  
  foreach ($groups as $group_id => $dummy) {
    $group = MB_Ref ("shield_{$sgn}_groups", $group_id);
    echo "<h2 style=\"margin: 0px 0 10px 0; font-weight: normal; padding: 0; font-size: 13pt;\"><b>$group_id</b> ({$group["name"]})";
    echo "<span style=\"font-size: 10pt\"> | <a href=\"".N_AlterUrl(N_MyFullUrl(), "viewobject", "") . "\">All groups</a></span></h2>";
    
    T_Start("ims", array ("noheader"=>"yes"));
    if ($group_id=="everyone") {
      echo "Users"; T_Next(); echo "[special group containing all visitors]"; T_NewRow();
    } elseif ($group_id=="authenticated") {
      echo "Users"; T_Next(); echo "[special group containing all authenticated users]"; T_NewRow();
    } elseif ($group_id=="allocated") {
      echo "Users"; T_Next(); echo "[special group containing the user allocated to a specific document or webpage]"; T_NewRow();
    } else {
      // Default users
      echo "Users (default)"; T_Next(); 
      foreach ($group["users"] as $user_id => $dummy) {
        $url = N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "view", "users"), "viewobject", $user_id);
        echo '<a href="'.$url.'">'.$user_id.'</a> ';
      }
      if (!$group["users"]) echo "&nbsp;"; 
      T_NewRow(); 

      // Global users
      echo "Users (global)"; T_Next(); 
      foreach ($group["users_global"] as $user_id => $dummy) {
        $url = N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "view", "users"), "viewobject", $user_id);
        echo '<a href="'.$url.'">'.$user_id.'</a> ';
      }
      if (!$group["users_global"]) echo "&nbsp;"; 
      T_NewRow();       
    }

    $globalrights = $group["globalrights"];
    echo "Global rights"; T_Next();
    if ($group["globalrights"]) {
      echo implode(" ", array_keys($group["globalrights"]));
    } else {
      echo "&nbsp;";
    }
    TE_End();
    
    // Calculate local users
    $users = $users1 = $users2 = array();
    foreach ($slist as $secsec_id => $slink) {
      if ($group["users_secsec"][$secsec_id]) $users[$secsec_id] = $users1[$secsec_id] = $group["users_secsec"][$secsec_id];
      $connections = MB_Load("shield_{$sgn}_localsecurity_connections", $secsec_id);
      foreach ($connections as $from => $tolist) {
        foreach ($tolist as $to => $dummy) {
          if ($to == $group_id) {
            $togroup = MB_Ref("shield_{$sgn}_groups", $to);
            foreach ($togroup["users"] as $user_id => $dummy) {
              $users2[$secsec_id][$user_id] = "x";
              $users[$secsec_id][$user_id] = "x";
            } 
            foreach ($togroup["users_global"] as $user_id => $dummy) {
              $users2[$secsec_id][$user_id] = "x";
              $users[$secsec_id][$user_id] = "x";
            } 
          }
        }
      }
    }

    // Show local users
    if ($users) {
      echo '<h3 style="margin: 15px 0 5px 0; padding: 0; font-weight: bold; font-size: 11pt;">Local users</h3>'; 
      T_Start("ims");
      echo "Security section"; T_Next(); echo "Local users"; T_NewRow();
      foreach ($users as $secsec_id => $specs) {
     
        echo $slist[$secsec_id]; T_Next(); 
        foreach ($specs as $user_id => $dummy) {
          $url = N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "view", "users"), "viewobject", $user_id) . "#sec".$secsec_id;
          $link = '<a href="'.$url.'">'.$user_id.'</a>';
          if ($users1[$secsec_id][$user_id]) {
            echo $link . " ";
          } else {
            echo "<i>$link*</i> ";
            $groupgroupmessage = true;
          }
        }
        T_NewRow();
      }
      TE_End();
    }

    echo "<br/><br/>";
  }
  
  if ($groupgroupmessage) echo "<i>* The user is a member of this group as a result of a group to group connection.</i></br>";

}

function SHIELD_DumpGroups($currentgroup = "") {
  global $detailsanyway;
  $sgn = $supergroupname = IMS_SuperGroupName();
  uuse ("reports");

  if ($currentgroup || $detailsanyway) {
    SHIELD_DumpGroupDetail($currentgroup);
    return;
  }
  
  $groups = MB_Query ("shield_{$sgn}_groups"); // does MB_MultiLoad
  echo "Click a group to see details about local security<br/><br/>";

  T_Start("ims");
  echo "ID"; T_Next();
  echo "Name"; T_Next();
  echo "Users (default)"; T_Next();
  echo "Users (global)"; T_Next();
  echo "Global rights"; T_Next();
  T_NewRow();
  
  foreach ($groups as $group_id => $dummy) {
    $group = MB_Ref("shield_{$sgn}_groups", $group_id);
    echo "<b><a href=\"".N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "viewobject", $group_id), "view", "groups")."\">$group_id</a></b>";
    T_Next();
    echo N_HtmlEntities($group["name"]);
    T_Next();
    
    if ($group_id=="everyone") {
      echo "[special group containing all visitors]"; T_Next(); echo ""; T_Next();
    } else if ($group_id=="authenticated") {
      echo "[special group containing all authenticated users]"; T_Next(); echo ""; T_Next();
    } else if ($group_id=="allocated") {
      echo "[special group containing the user allocated to a specific document or webpage]"; T_Next(); echo ""; T_Next();
    } else {
      foreach ($group["users"] as $user_id => $dummy) {
        $url = N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "view", "users"), "viewobject", $user_id);
        echo '<a href="'.$url.'">'.$user_id.'</a> ';
      }
      if (!$group["users"]) echo "&nbsp;"; 
      T_Next();
      
      foreach ($group["users_global"] as $user_id => $dummy) {
        $url = N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "view", "users"), "viewobject", $user_id);
        echo '<a href="'.$url.'">'.$user_id.'</a> ';
      }
      if (!$group["users_global"]) echo "&nbsp;"; 
      T_Next();
    }
    if ($group["globalrights"]) {
      echo implode(" ", array_keys($group["globalrights"]));
    } else {
      echo "&nbsp;";
    }
      
    T_NewRow();
  }
  TE_End();
}


function SHIELD_DumpSecsec($currentsecsec = "")
{
  global $detailsanyway;
  $sgn = $supergroupname = IMS_SuperGroupName();
  uuse ("reports");
  
  $allsecsecs = SHIELD_AllSecuritySections($sgn);
  MB_MultiLoad("shield_{$sgn}_localsecurity_connections", $allsecsecs);

  T_Start("ims");
  echo "Security section";
  
  $slist = array(); 
  foreach ($allsecsecs as $secsec_id => $dummy) {
    if ($secsec_id) {
      $path = REPORTS_VisualPath($secsec_id);
      $slist[$secsec_id] = '<!-- '.$path.' --><a name="sec'.$secsec_id.'" /><a href="/openims/openims.php?mode=admin&submode=securitysection&back='.urlencode(N_MyFullUrl()).'&securitysection='.$secsec_id.'&noemptygroups=yes">'.trim($path).'</a>';  // Start with $path for sorting...
    }
  }
  asort ($slist);

  foreach ($slist as $secsec_id => $sectitle) {
    T_NewRow();
    echo "<b><a href=\"".N_AlterUrl(N_AlterUrl(N_MyFullUrl(), "viewobject", $secsec_id), "view", "secsec")."\">$sectitle</a></b>";
  }
  TE_End();
}

function SHIELD_DumpWorkflows($currentworkflow = "") {
  $sgn = $supergroupname = IMS_SuperGroupName();
  $workflows = MB_Query ("shield_$supergroupname"."_workflows");
  if (is_array ($workflows)) reset ($workflows);
  if (is_array ($workflows)) while (list($workflow_id)=each($workflows)) {
    $workflow = &SHIELD_AccessWorkflow ($supergroupname, $workflow_id);
    echo "&nbsp;&nbsp;&nbsp;<b>$workflow_id</b> (".$workflow["name"].")<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Stages (".$workflow["stages"]."):";
    for ($i=1; $i<=$workflow["stages"]; $i++) { 
      echo " $i:'".$workflow[$i]["name"]."'";
    }
    echo "<br>";    
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;New objects get stage 1 (".$workflow[1]["name"].")<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;When objects arive at stage ".$workflow["stages"]." (".$workflow[$workflow["stages"]]["name"].") they are published<br>";
    for ($i=1; $i<=$workflow["stages"]; $i++) {
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Stage $i (".$workflow[$i]["name"].")<br>";
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;Edit<br>";
      if (SHIELD_WorkFlowRights ($workflow[$i]["edit"])) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;&nbsp;&gt;&nbsp;Edit changes stage to: ";
        echo $workflow[$i]["stageafteredit"] . " (".$workflow[$workflow[$i]["stageafteredit"]]["name"].")";
        echo "<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;&nbsp;&gt;&nbsp;Right 'edit': ";
        echo SHIELD_WorkFlowRights ($workflow[$i]["edit"]);
        echo "<br>";
      } else {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;Edit not allowed during this stage<br>";
      }
      $options = $workflow[$i];
      if (is_array($options)) {
        reset ($options);
        while (list($option)=each($options)) {
          if (substr ($option, 0, 1)=="#") {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;";
            echo "Option: ". substr($option, 1);
            echo "<br>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;&nbsp;&gt;&nbsp;";
            echo "Option '" . substr($option, 1) . "' changes stage to: ";
            echo $workflow[$i][$option] . " (".$workflow[$workflow[$i][$option]]["name"].")";
            echo "<br>";            
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;&nbsp;&gt;&nbsp;";
            echo "Right 'changestage' to '".substr($option, 1)."': ";
            echo SHIELD_WorkFlowRights ($workflow[$i]["changestage"][$option]);
            echo "<br>";            
          }
        }
      }
    }
    global $objectrights;
    reset ($objectrights);
    while (list ($right)=each($objectrights)) {
      SHIELD_DumpWorkflowRights ($supergroupname, $workflow_id, $right);
    }
  }

}


function SHIELD_DumpRights() {
  global $globalandlocalrights;
  global $globalrights;
  echo "<b>Available global rights (global to OpenIMS):</b><br>";    
  reset ($globalrights);
  while (list ($right, $desc)=each($globalrights)) {
    if (!$globalandlocalrights[$right] || $right=="system") {
      echo "&nbsp;&nbsp;&nbsp;<b>$right</b> ($desc)<br>";
    }
  }  
  echo "<br><b>Available global rights (global to security section):</b><br>";    
  reset ($globalrights);
  while (list ($right, $desc)=each($globalrights)) {
    if ($globalandlocalrights[$right]) {
      if ($right=="system") {
        global $systemlocal;
        echo "&nbsp;&nbsp;&nbsp;<b>$right</b> ($systemlocal)<br>";
      } else {
        echo "&nbsp;&nbsp;&nbsp;<b>$right</b> ($desc)<br>";
      } 
    }
  }  
  echo "<br><b>Available object rights:</b><br>";    
  global $objectrights;
  reset ($objectrights);
  while (list ($right, $desc)=each($objectrights)) {
    echo "&nbsp;&nbsp;&nbsp;<b>$right</b> ($desc)<br>";
  }
  echo "<br><b>Available workflow stage related rights:</b><br>";    
  global $workflowstagerights;
  reset ($workflowstagerights);
  while (list ($right, $desc)=each($workflowstagerights)) {
    echo "&nbsp;&nbsp;&nbsp;<b>$right</b> ($desc)<br>";
  }  

}

function SHIELD_Dump ($supergroupname) /* old version */
{
  uuse ("reports");
  $allsecsecs = SHIELD_AllSecuritySections ($supergroupname);
  echo "<b>All security data for '$supergroupname'</b><br>";
  echo "<br><b><font size=4 color=\"ff0000\"> Groups:</font></b><br>";
  $groups = MB_Query ("shield_$supergroupname"."_groups");
  while (list($group_id)=each($groups)) {
    echo "&nbsp;&nbsp;&nbsp;<b>$group_id</b> (".MB_Fetch ("shield_$supergroupname"."_groups", $group_id, "name").")<br>";
    $group = MB_Ref ("shield_$supergroupname"."_groups", $group_id);
    if ($group_id=="everyone") {
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Users: [special group containing all visitors]<br>";
    } else if ($group_id=="authenticated") {
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Users: [special group containing all authenticated users]<br>";
    } else if ($group_id=="allocated") {
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Users: [special group containing the user allocated to a specific document or webpage]<br>";
    } else {
      $users = $group["users"];
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Users (default):";
      if (is_array($users)) reset ($users);
      if (is_array($users)) while (list($user_id)=each($users)) {
        echo " ".$user_id;
      }

      echo "<br>";
      $users = $group["users_global"];
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Users (global):";
      if (is_array($users)) reset ($users);
      if (is_array($users)) while (list($user_id)=each($users)) {
        echo " ".$user_id;
      }
      echo "<br>";
      $secsecs = $group["users_secsec"];
      if (is_array($secsecs)) {
        foreach ($secsecs as $id => $users) {
          echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Users (security section '".REPORTS_VisualPath($id)."'):"; 
          if (is_array($users)) reset ($users);
          if (is_array($users)) while (list($user_id)=each($users)) {
            echo " ".$user_id;
          }
         echo "<br>";
        }
      }
    }
    $globalrights = $group["globalrights"];
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Global rights:";
    if (is_array($globalrights)) reset($globalrights);
    if (is_array($globalrights)) while (list($globalrights_id)=each($globalrights)) {
      echo " ".$globalrights_id;
    }
    echo "<br>";
  }
  echo "<br><b><font size=4 color=\"ff0000\"> Users:</font></b><br>";
  $users = MB_Query ("shield_$supergroupname"."_users");
  reset ($users);
  while (list($user_id)=each($users)) {
    echo "&nbsp;&nbsp;&nbsp;<b>$user_id</b> (".MB_Fetch ("shield_$supergroupname"."_users", $user_id, "name").")<br>";
    $user = MB_Ref ("shield_$supergroupname"."_users", $user_id);
    $groups = $user["groups"];     
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Groups (default):";
    if (is_array($groups)) reset($groups);
    if (is_array($groups)) while (list($group_id)=each($groups)) {
      echo " ".$group_id;
    }
    echo "<br>";
    $groups = $user["groups_global"];     
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Groups (global):";
    if (is_array($groups)) reset ($groups);
    if (is_array($groups)) while (list($group_id)=each($groups)) {
      echo " ".$group_id;
    }
    echo "<br>";
    $secsecs = $user["groups_secsec"];
    if (is_array($secsecs)) {
      foreach ($secsecs as $id => $groups) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Groups (security section '".REPORTS_VisualPath ($id)."'):";
        if (is_array($groups)) reset ($groups);
        if (is_array($groups)) while (list($group_id)=each($groups)) {
          echo " ".$group_id;
        }
       echo "<br>";
      }
    }
  }
  echo "<br><b><font size=4 color=\"ff0000\"> Security sections:</font></b><br>";
  foreach ($allsecsecs as $sec => $dummy) {
    if ($sec) {
      $slist[$sec] = REPORTS_VisualPath ($sec);
    }
  }
  asort ($slist);
  $users = MB_Query ("shield_$supergroupname"."_users");
  reset ($users);
  foreach ($slist as $sec => $title) {
    echo "&nbsp;&nbsp;&nbsp;<b>$title</b><br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(users without local groups are not shown)<br>";
    while (list($user_id)=each($users)) {
      $user = MB_Ref ("shield_$supergroupname"."_users", $user_id);
      $groups = $user["groups_secsec"][$sec];
      if (is_array($groups) && count($groups)) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;User $user_id (".$user["name"].") is in local groups:";
        reset ($groups);
        while (list($group_id)=each($groups)) {
          echo " ".$group_id;
        }
        echo "<br>";
      }
    }
    $connections = MB_Load ("shield_".$supergroupname."_localsecurity_connections", $sec);
    foreach ($connections as $from => $tolist) {
      foreach ($tolist as $to => $dummy) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Connection from standard/global group $from to local group $to<br>";
      }
    }
  }
  echo "<br><b><font size=4 color=\"ff0000\"> Workflows:</font></b><br>";
  $workflows = MB_Query ("shield_$supergroupname"."_workflows");
  if (is_array ($workflows)) reset ($workflows);
  if (is_array ($workflows)) while (list($workflow_id)=each($workflows)) {
    $workflow = &SHIELD_AccessWorkflow ($supergroupname, $workflow_id);
    echo "&nbsp;&nbsp;&nbsp;<b>$workflow_id</b> (".$workflow["name"].")<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Stages (".$workflow["stages"]."):";
    for ($i=1; $i<=$workflow["stages"]; $i++) { 
      echo " $i:'".$workflow[$i]["name"]."'";
    }
    echo "<br>";    
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;New objects get stage 1 (".$workflow[1]["name"].")<br>";
    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;When objects arive at stage ".$workflow["stages"]." (".$workflow[$workflow["stages"]]["name"].") they are published<br>";
    for ($i=1; $i<=$workflow["stages"]; $i++) {
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Stage $i (".$workflow[$i]["name"].")<br>";
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;Edit<br>";
      if (SHIELD_WorkFlowRights ($workflow[$i]["edit"])) {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;&nbsp;&gt;&nbsp;Edit changes stage to: ";
        echo $workflow[$i]["stageafteredit"] . " (".$workflow[$workflow[$i]["stageafteredit"]]["name"].")";
        echo "<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;&nbsp;&gt;&nbsp;Right 'edit': ";
        echo SHIELD_WorkFlowRights ($workflow[$i]["edit"]);
        echo "<br>";
      } else {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;Edit not allowed during this stage<br>";
      }
      $options = $workflow[$i];
      if (is_array($options)) {
        reset ($options);
        while (list($option)=each($options)) {
          if (substr ($option, 0, 1)=="#") {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;";
            echo "Option: ". substr($option, 1);
            echo "<br>";
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;&nbsp;&gt;&nbsp;";
            echo "Option '" . substr($option, 1) . "' changes stage to: ";
            echo $workflow[$i][$option] . " (".$workflow[$workflow[$i][$option]]["name"].")";
            echo "<br>";            
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&gt;&nbsp;&nbsp;&gt;&nbsp;";
            echo "Right 'changestage' to '".substr($option, 1)."': ";
            echo SHIELD_WorkFlowRights ($workflow[$i]["changestage"][$option]);
            echo "<br>";            
          }
        }
      }
    }
    global $objectrights;
    reset ($objectrights);
    while (list ($right)=each($objectrights)) {
      SHIELD_DumpWorkflowRights ($supergroupname, $workflow_id, $right);
    }
  }
  global $globalandlocalrights;
  global $globalrights;
  echo "<br><b><font size=4 color=\"ff0000\"> Available rights:</font></b><br>";
  echo "<b>Available global rights (global to OpenIMS):</b><br>";    
  reset ($globalrights);
  while (list ($right, $desc)=each($globalrights)) {
    if (!$globalandlocalrights[$right] || $right=="system") {
      echo "&nbsp;&nbsp;&nbsp;<b>$right</b> ($desc)<br>";
    }
  }  
  echo "<br><b>Available global rights (global to security section):</b><br>";    
  reset ($globalrights);
  while (list ($right, $desc)=each($globalrights)) {
    if ($globalandlocalrights[$right]) {
      if ($right=="system") {
        global $systemlocal;
        echo "&nbsp;&nbsp;&nbsp;<b>$right</b> ($systemlocal)<br>";
      } else {
        echo "&nbsp;&nbsp;&nbsp;<b>$right</b> ($desc)<br>";
      } 
    }
  }  
  echo "<br><b>Available object rights:</b><br>";    
  global $objectrights;
  reset ($objectrights);
  while (list ($right, $desc)=each($objectrights)) {
    echo "&nbsp;&nbsp;&nbsp;<b>$right</b> ($desc)<br>";
  }
  echo "<br><b>Available workflow stage related rights:</b><br>";    
  global $workflowstagerights;
  reset ($workflowstagerights);
  while (list ($right, $desc)=each($workflowstagerights)) {
    echo "&nbsp;&nbsp;&nbsp;<b>$right</b> ($desc)<br>";
  }  
}

function SHIELD_DumpWorkflowRights ($supergroupname, $workflow_id, $right, $xtra="")
{
  $workflow = &SHIELD_AccessWorkflow ($supergroupname, $workflow_id);
  echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Right '$right'$xtra: ";
  $list = $workflow["rights"][$right];
  if (is_array($list)) reset ($list);
  if (is_array($list)) while (list($group_id)=each($list)) {
    echo " ".$group_id;
  } 
  echo "<br>";
}
FUNCTION SHIELD_ReturnWorkflowRightGroups ($supergroupname, $workflow_id, $right, $xtra="") {
  $workflow = &SHIELD_AccessWorkflow ($supergroupname, $workflow_id);
  $list = $workflow["rights"][$right];
  if (is_array($list)) reset ($list);
  if (is_array($list)) while (list($group_id)=each($list)) {
    $terug .= " ".$group_id;
  } 
  return $terug;
}

function SHIELD_WorkflowRights ($list)
{
  $ret = "";
  if (is_array($list)) {
    reset ($list);
    $table = "shield_".IMS_SuperGroupName()."_groups";
    while (list($group_id)=each($list)) {
      $group = MB_Ref($table,$group_id);
      if ($group) $ret.= $group_id. " ";
    } 
  }
  return $ret;
}

function SHIELD_AssignableUsers ($supergroupname, $object_id, $option) 
{
  $object = IMS_AccessObject ($supergroupname, $object_id);
  $optioncopy = $option;

  $securitysection = $object["securitysection"];
  $result = array();
  // AM 29-09-2010
  // $users = MB_Query ("shield_".$supergroupname."_users", "", '$record["name"]');

  /* Overslaan van inactieve gebruikers in de query van SHIELD_AssignableUsers
     die worden bij de nafiltering op groepen toch al uit het resultaat gehaald.*/

  $table = "shield_".IMS_SuperGroupName()."_users";   
  $specs = array();
  $specs["select"]['$record["inactive"].""==""'] = true;
  $specs["value"] = '$record["name"]';
  $specs["sort"] = '$record["name"]';
  $users = MB_TurboMultiQuery($table, $specs);

  if ($object["objecttype"]=="document" || $object["objecttype"]=="webpage") {
    $workflow = SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);
    if ($option=="#reassign#") {
      $newstage = $object["stage"];
    } else {
      $newstage = $workflow[$object["stage"]]["#$option"];
    }
    foreach ($users as $id => $name) {
      $ok = false;
      $view = false;
      if (SHIELD_UserHasSystemRight ($supergroupname, $id)) {
        $ok = true;
        $view = true;
      }
      if (is_array($workflow["rights"]["view"])) foreach ($workflow["rights"]["view"] as $group => $dummy) {
        if (SHIELD_ValidateAccess_Group ($supergroupname, $id, $group, $securitysection, $object_id)) {
          $view = true; 
        }
      }
      if($view) { // if view is false then there is no need to determine the ok variable. so only proceed when view is true
        if (is_array($workflow[$newstage]["edit"])) foreach ($workflow[$newstage]["edit"] as $group => $dummy) {
          if ($group=="allocated") $ok = true;
          if (SHIELD_ValidateAccess_Group ($supergroupname, $id, $group, $securitysection, $object_id)) {
            $ok = true; 
          }
        }

        $list = $workflow[$newstage];
        if (is_array ($list)) {
          reset ($list);
          while (list($option)=each($list))
          {
            if (substr ($option, 0, 1)=="#") {
              $groups = $workflow[$newstage]["changestage"][$option];
              if (is_array($groups)) foreach ($groups as $group => $dummy) {  
                if ($group=="allocated") $ok = true;
                if (SHIELD_ValidateAccess_Group ($supergroupname, $id, $group, $securitysection, $object_id)) {
                  $ok = true; 
                }
              }
            }
          }
        }
      }
      if ($ok && !$view) $ok = false;
      if ($ok) $result[$id] = $name;
    }
  } else {
     $result = $users;
  }

  if (!function_exists ("SHIELD_AssignableUsers_Extra")) {
    // Assignable users filter (default)
    $internal_component = FLEX_LoadImportableComponent ("support", "fae6896b8c7a0ff71e4f5155556857d1");
    $internal_code = $internal_component["code"];
    eval ($internal_code);
  }
  $result = SHIELD_AssignableUsers_Extra($result, $supergroupname, $object_id, $optioncopy);

  return $result;
}

function SHIELD_AssignableUsersBPMS ($supergroupname, $object_id, $option, $process_id) {

    $result = array(); 
    $users = MB_Query ("shield_".$supergroupname."_users", "", '$record["name"]');

    $process = MB_Ref("shield_".$supergroupname."_processes", $process_id);
    $object  = MB_Ref("process_".$supergroupname."_cases_".$process_id,$object_id);
    
    // does object exists?
    if ($object["visualid"]."" != "") {

       $thisstage = $object["stage"];
       $newstage = $process["$thisstage"]["choice"]["$option"]["newstage"];

       foreach ($users as $id => $name) {
          $ok = false;
          $view = false;
          if (SHIELD_UserHasSystemRight ($supergroupname, $id)) {
             $ok = true;
             $view = true;
          }

          // formulieren
          if (is_array($process[$newstage]["changestage"])) foreach ($process[$newstage]["changestage"] as $group => $dummy) {

             if ($group=="allocated") $ok = true;
             if (SHIELD_ValidateAccess_Group ($supergroupname, $id, $group)) {
                $ok = true; 
             }
          }

          // beslissingen
          if (is_array($process[$newstage]["choice"])) foreach ($process[$newstage]["choice"] as $keuze => $dummy) {

             if (is_array($process[$newstage]["choice"]["$keuze"]["changestage"])) foreach ($process[$newstage]["choice"]["$keuze"]["changestage"] as $group => $dummy) {
               if ($group=="allocated") $ok = true;
               if (SHIELD_ValidateAccess_Group ($supergroupname, $id, $group)) {
                  $ok = true; 
               }
             }
          }

          // heeft men wel procesview recht
          if (is_array($process["rights"]["processview"])) foreach ($process["rights"]["processview"] as $group => $dummy) {
             if (SHIELD_ValidateAccess_Group ($supergroupname, $id, $group)) {
                $view = true; 
             }
          }

          if ($ok && !$view) $ok = false;
          if ($ok)  $result[$id] = $name; 
       }

    } else {
       $result = $users;
    }

    if (!function_exists ("SHIELD_AssignableUsersBPMS_Extra")) {
      // Assignable users filter(default)
      $internal_component = FLEX_LoadImportableComponent ("support", "fae6896b8c7a0ff71e4f5155556857d1");
      $internal_code = $internal_component["code"];
      eval ($internal_code);
    }
    $result = SHIELD_AssignableUsersBPMS_Extra($result, $supergroupname, $object_id, $option, $process_id);

    return $result;
}

function SHIELD_UsersForMultiApprove ($supergroupname, $object_id) 
{
  $right = "view";
  if(function_exists("SHIELD_UsersForMultiApprove_Replace")) {
    //Function needs to be placed in Lowlevel
    return SHIELD_UsersForMultiApprove_Replace ($supergroupname, $object_id);
  }
  $object = IMS_AccessObject ($supergroupname, $object_id);
  $securitysection = $object["securitysection"];
  $folder_id = $object["directory"];
  $result = array();
  $table = "shield_".IMS_SuperGroupName()."_users";   
  $specs = array();
  $specs["value"] = '$record["name"]';
  $specs["sort"] = '$record["name"]';
  $users = MB_TurboMultiQuery($table, $specs);

  if ($object["objecttype"]=="document" || $object["objecttype"]=="webpage") {
    $workflow = SHIELD_AccessWorkflow ($supergroupname, $object["workflow"]);

    foreach ($users as $id => $name) {
      $ok = false;
      $view = false;
      if (SHIELD_UserHasSystemRight ($supergroupname, $id)) {
        $ok = true;
        $view = true;
      } elseif (is_array($workflow["rights"][$right])) foreach ($workflow["rights"]["view"] as $group => $dummy) {
        if (SHIELD_ValidateAccess_Group ($supergroupname, $id, $group, $securitysection, $object_id, $folder_id)) {
          $view = true;
          $ok = true; 
        }
      }
      if ($ok && !$view) $ok = false;
      if ($ok) $result[$id] = $name;
    }
  } else {
     $result = $users;
  }
  return $result;
}




function WriteBoolean($xVariable)
{
	if(GetType($xVariable) == 'boolean')
		return $xVariable ? 'TRUE' : 'FALSE';
	else
		return $xVariable;
}

function W()
{
	$aArguments = Func_Get_Args();
	$aArguments = Array_Map('WriteBoolean', $aArguments);
	$aArguments = Array_Map('nl2br', $aArguments);
	echo Implode('&nbsp;|&nbsp;', $aArguments) . '<br>';
}

function Stretch(&$sString1, &$sString2, $sChar)
{
	$iChars = Max(StrLen($sString1), StrLen($sString2));
	
	$sString1 = Str_Pad($sString1, $iChars, $sChar, STR_PAD_LEFT);
	$sString2 = Str_Pad($sString2, $iChars, $sChar, STR_PAD_LEFT);
}

function Swap(&$xVariable1, &$xVariable2)
{
	$xTemp = $xVariable1;
	$xVariable1 = $xVariable2;
	$xVariable2 = $xTemp;
}


// NKIT RSA funtions
// 2.2GHz P4
//
// BITS  GENERATE  SIGN   VERIFY
// =============================
// 128   3s        21ms   5ms
// 256   4s        120ms  7ms
// 512   36s       850ms  13ms
// 768   417s      3s     24ms
// 1024  1049s     8s     45ms
// 1369            16s    90ms 



function SHIELD_LocalSecretKey ()
{
  $secret = &MB_Ref ("local_secret", "secretkey");
  if (!$secret) $secret = N_GUID();
  return $secret;
}

function SHIELD_Pack ($me)
{
  return base64_encode (serialize ($me));
}

function SHIELD_Unpack ($me)
{
  return unserialize (base64_decode ($me));
}

function SHIELD_Encrypt ($message, $password)
{
  $md5 = md5($password);
  $ptr = 0;
  for ($i=0; $i<strlen($message); $i++) {
    $offset = hexdec (substr ($md5, $ptr*2, 2));
    if (++$ptr==16) { 
      $md5 = md5 ($md5);
      $ptr = 0;
    }
    $result .= chr (ord (substr ($message, $i, 1)) + $offset);
  }
  return $result;

}

function SHIELD_Decrypt ($encrypted, $password)
{
  $md5 = md5($password);
  $ptr = 0;
  for ($i=0; $i<strlen($encrypted); $i++) {
    $offset = hexdec (substr ($md5, $ptr*2, 2));    
    if (++$ptr==16) { 
      $md5 = md5 ($md5);
      $ptr = 0;
    }
    $result .= chr (ord (substr ($encrypted, $i, 1)) - $offset);
  }  
  return $result;
}

function SHIELD_HasProduct ($prod)
{
  
  if (N_OpenIMSCE() && ($prod == "cms" || $prod == "dms")) return true;
  return false;
}

function SHIELD_HasModule ($prod, $mod)
{
  
  if (N_OpenIMSCE()) return true;
}


// ==== NEWCODE_JOHNNY ====
function SHIELD_LocalGroupsFromSite( $supergroupname , $site_id )
{
	if ( SHIELD_SiteHasLocalSecurity( $supergroupname , $site_id ) )
	{
          $obj = &MB_Ref ("shield_".$supergroupname."_localsitesecurity_connections", $site_id );
	  return $obj;				
	}
	return Array();
}

function SHIELD_AddLocalSecurityToSite( $supergroupname , $site_id )
{
  if ( $site_id=="" ) return;
  $siteobject = &MB_Ref( "shield_{$supergroupname}_haslocalsecurity" , $site_id );
  $siteobject["hasLocalSecurity"] = 'yes';
}

function SHIELD_RemoveLocalSecurityFromSite( $supergroupname , $site_id )
{
  if ($site_id=="") return;
  $siteobject = &MB_Ref( "shield_{$supergroupname}_haslocalsecurity" , $site_id );
  $siteobject["hasLocalSecurity"] = 'no';
}

function SHIELD_SiteHasLocalSecurity( $supergroupname , $site_id )
{
  if ( $site_id=='' ) return '';
  $siteobject = MB_Ref( "shield_{$supergroupname}_haslocalsecurity" , $site_id );
  return $siteobject["hasLocalSecurity"] == "yes";
}
// ==== END_NEWCODE_JOHNNY ====

function SHIELD_AddSecurityToFolder ($supergroupname, $folder_id)
{
  //ericd 301208 locale beveiliging ook op de hoofdfolder mogelijk maken
  //ook aangepassing SHIELD_RemoveSecurityFromFolder, SHIELD_SecuritySectionForFolder en openims.php ( if ($thefolder!="root") )
  //if ($folder_id=="root" || $folder_id=="") return;
  if ($folder_id=="") return;
  $tree = &CASE_TreeRef ($supergroupname, $folder_id);
  $folderobject = &TREE_AccessObject($tree, $folder_id);
  $folderobject["hassecuritysection"] = "yes";
  $filecode = '
    uuse ("ims");
    $object = &IMS_AccessObject (IMS_SuperGroupName(), $file_id);
    $object = SHIELD_UpdateSecuritySection (IMS_SuperGroupName(), $object);
  ';
  TREE_TERRA_WalkDirectory ($tree, $folder_id, "", $filecode);
}

function SHIELD_RemoveSecurityFromFolder ($supergroupname, $folder_id)
{
  //ericd 301208 locale beveiliging ook op de hoofdfolder mogelijk maken
  //ook aangepassing SHIELD_AddSecurityToFolder, SHIELD_SecuritySectionForFolder en openims.php ( if ($thefolder!="root") )
  //if ($folder_id=="root" || $folder_id=="") return;
  if ($folder_id=="") return;
  $tree = &CASE_TreeRef ($supergroupname, $folder_id);
  $folderobject = &TREE_AccessObject($tree, $folder_id);
  $folderobject["hassecuritysection"] = "no";
  $filecode = '
    uuse ("ims");
    $object = &IMS_AccessObject (IMS_SuperGroupName(), $file_id);
    $object = SHIELD_UpdateSecuritySection (IMS_SuperGroupName(), $object);
  ';
  TREE_TERRA_WalkDirectory ($tree, $folder_id, "", $filecode);
}

function SHIELD_SecuritySectionForFolder ($supergroupname, $folder_id)
{
  //ericd 301208 locale beveiliging ook op de hoofdfolder mogelijk maken
  //ook aangepassing SHIELD_AddSecurityToFolder, SHIELD_SecuritySectionForFolder en openims.php ( if ($thefolder!="root") )
  //if ($folder_id=="root" || $folder_id=="") return "";
  if ($folder_id=="") return "";
  $tree = CASE_TreeRef ($supergroupname, $folder_id);
  $folderobject = TREE_AccessObject($tree, $folder_id);  
  if ($folderobject["hassecuritysection"] == "yes") return $folder_id;

  //ericd 301208 de root/hoofdfolder heeft geen parent, en staat ook niet als "leeg" in het folder object
  //lijkt echter wel goed te gaan, maar toch maar een if else
  //return SHIELD_SecuritySectionForFolder ($supergroupname, $folderobject["parent"]);

  if($folderobject["parent"])
     return SHIELD_SecuritySectionForFolder ($supergroupname, $folderobject["parent"]);
  else
     //return SHIELD_SecuritySectionForFolder ($supergroupname, "");
     //of beter? ivm   if ($folder_id=="") return "";
     return "";
}

function SHIELD_SecuritySectionForObject ($supergroupname, $object_id)
{
  $object = IMS_AccessObject ($supergroupname, $object_id);
  return SHIELD_SecuritySectionForFolder ($supergroupname, $object["directory"]);
}

function SHIELD_UpdateSecuritySection ($supergroupname, $object) 
{
  if ($object ["objecttype"]=="document") {
    $object["securitysection"] = SHIELD_SecuritySectionForFolder ($supergroupname, $object["directory"]);
  }
  return $object;
}

function SHIELD_AllSecuritySections ($supergroupname)
{
  $ret[""] = ML("Algemeen", "Generic");
  $tree = MB_Ref ("ims_trees", $supergroupname."_documents");
  $all = TREE_AllObjects ($tree);
  foreach ($all as $object_id => $dummy) {
    $object = TREE_AccessObject ($tree, $object_id);
    if ($object["hassecuritysection"]=="yes") {
      $ret [$object_id] = $object["shorttitle"];
    }
  }
  $cases = CASE_List ($supergroupname);
  foreach ($cases as $case_id => $dummy) {
    $tree = CASE_TreeRef ($supergroupname, $case_id."root");
    $all = TREE_AllObjects ($tree);
    foreach ($all as $object_id => $dummy) {
      $object = TREE_AccessObject ($tree, $object_id);
      if ($object["hassecuritysection"]=="yes") {
        $ret [$object_id] = $object["shorttitle"];
      }
    }
  }
  return $ret;
}

function SHIELD_DMSWorkFlows()
{
  global $SHIELD_DMSWorkFlows;
  if (!$SHIELD_DMSWorkFlows) {
    $SHIELD_DMSWorkFlows = MB_TurboSelectQuery ("shield_".IMS_SuperGroupName()."_workflows", '$record["dms"]!=""', true);
    MB_MultiLoad ("shield_".IMS_SuperGroupName()."_workflows", $SHIELD_DMSWorkFlows);
  }
  return $SHIELD_DMSWorkFlows;
}


function SHIELD_CanViewDmsCase($sgn, $user_id, $case_id) {
  // Returns true if the user is allowed to see the case in the "Per dossier" view.
  // $case_id e.g. "(f699fda13013d6dc5c7383526351bde0)"
  // Note: the default behaviour is to return true (every user can see every case)
  
  N_Debug("SHIELD_CanViewDmsCase $sgn {$user_id} {$case_id}");

  $casedata = MB_Load("ims_{$sgn}_case_data", $case_id);
  if (!SHIELD_CanViewDmsCasetype($sgn, $user_id, $casedata["category"])) return false;

  if(function_exists ("SHIELD_CustomCaseAuthorisation")) {
    if (!SHIELD_CustomCaseAuthorisation($case_id)) return false;
  }

  return true;
}

function SHIELD_CanViewDmsCasetype($sgn, $user_id, $casetype) {
  // Returns true if the user is allowed to see the $casetype in the "Per dossier" view.
  // Note: returns true if casetypes are not enabled
  global $myconfig;
  if ($myconfig[$sgn]["casetypes"]=="yes") {
    $casetypespecs = MB_Load("ims_{$sgn}_case_types",$casetype);
    if ($casetypespecs) {
      return SHIELD_ValidateAccess_List($sgn, $user_id, $casetypespecs["rights"]["view"]);
    } else {
      // This can not happen when this function is called by the "Per dossier" view, because "Per dossier"
      // iterates over casetypes. But this could happen when custom code iterates over all cases, and 
      // some cases do not have a valid casetype. Since these cases can not be viewed in the "Per dossier" 
      // view, return false.
      return false;
    }
  } else {
    return true;
  }
}

function SHIELD_HasEMSArchiveRight($supergroupname, $archiveid, $right) {
  N_Debug ("SHIELD_HasEMSArchiveRight ($supergroupname, $archiveid, $right)");

  // has all rights
  if (SHIELD_HasGlobalRight ($supergroupname, "system")) return true;

  $permissions = MB_Ref("shield_" . $supergroupname . "_ems_permissions", $archiveid);
  $grouparray = $permissions[$right];
  // does everyone have this right?
  if ($grouparray["everyone"]) return true;

  $user = &MB_Ref ("shield_$supergroupname"."_users", SHIELD_CurrentUser ($supergroupname));
  $groups = $user["groups"];
  if (is_array($groups)) {
    reset ($groups);
    while (list($groupname) = each($groups)) {
      if($grouparray[$groupname]) return true;
    }
  }
  return false;
}

function SHIELD_ResetPassword ($supergroupname, $userid)
{
  global $HTTP_HOST, $myconfig;
  $user = MB_Load ("shield_".$supergroupname."_users", $userid);
  $vowels = array ('a', 'e', 'i', 'o', 'u');    
  $consonants = array ('b', 'c', 'd', 'f', 'g', 'h', 'k', 'm', 'n','p', 'r', 's', 't', 'v', 'w', 'x', 'z');    
  $syllables = array ();    
  foreach ($vowels as $v) { 
    foreach ($consonants as $c) {    
      array_push($syllables, $c.$v);    
      array_push($syllables, $v.$c); 
   } 
  } 
  for ($i=0; $i<4; $i++) $newpass = $newpass.$syllables[array_rand($syllables)];
  SHIELD_SetPassword ($supergroupname, $userid, $newpass);

  $site = $HTTP_HOST;
  if(!$myconfig[$supergroupname]["resetpasswordemail"]) {  // in siteconfig a custom body can be defined. It could contain the fields [[[site]]], [[[id]]], [[[newpassword]]], [[[name]]]
    $body = ML("Wachtwoordbericht voor","Password message for").": $site \r\n";
    $body .= ML("Het ID is","The ID is").": $userid \r\n";
    $body .= ML("Het wachtwoord is","The password is").": $newpass ".chr(13).chr(10)."\r\n\r\n\r\n";
    $from = $user["email"];
    $to = $user["email"];
    $subject =ML("Wachtwoordbericht voor","Password message for").": ".$site;
  } else {
    $from = $myconfig[$supergroupname]["resetpasswordemail"]["from"];
    $to = $user["email"];
    $subject= $myconfig[$supergroupname]["resetpasswordemail"]["subject"];
    $tobereplaced=array("[[[site]]]", "[[[id]]]", "[[[newpassword]]]", "[[[name]]]");
    $newvalues=array($site, $userid, $newpass, $user["name"]);
    $body = str_replace($tobereplaced, $newvalues, $myconfig[$supergroupname]["resetpasswordemail"]["body"]);
  }
  N_Mail ($from, $to, $subject, $body);
}



?>