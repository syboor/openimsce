<?
  include (getenv ("DOCUMENT_ROOT")."/nkit/nkit.php");
?>
<html>
  <head>
    <title>OpenIMS CE Configurator</title>
  </head>
</body>
<h1>OpenIMS CE Configurator</h1>
<?

  // Check OpenIMS version
  global $imsbuild, $myconfig;

  if (N_OpenIMSCE()) {
    echo '<p>OpenIMS version found: <b>OpenIMS CE ' . $imsbuild . '</b></p>';
  } else {
    // Disabled $imsbuild to pacify customer with very serious misconfiguration that makes everything accessible regardless of .htaccess
    // echo '<p>OpenIMS version found: <b>OpenIMS PRO ' . $imsbuild . '</b><br/>The configurator can not be used, exiting...</p>';
    echo '<p>OpenIMS version found: <b>OpenIMS PRO</b><br/>The configurator can not be used, exiting...</p>';
    die();
  }

  // Check that myconfig.php exists
  if (N_FileExists("html::/myconfig.php")) {
    echo '<p>Machine config ('.$_SERVER['DOCUMENT_ROOT'].'/myconfig.php) found (OK)</p>';
  } else {
    $form = array();
    $form['title'] = 'Create machine config';
    $form['gotook'] = 'closeme&refreshparent';
    $form['formtemplate'] = '
      <p><b>Create machine config?</b><br/>
         Existing '.$_SERVER['DOCUMENT_ROOT'].'/myconfig.php will be overwritten!<br/>
         <center>[[[OK:Create]]] [[[Cancel]]]</center></p>
    ';
    $form['postcode'] = '
      N_CopyFile("html::/myconfig.php", "html::/openims/ce/basicsite/myconfig_linux.php");
      $form2["formtemplate"] = "<p>{$_SERVER["DOCUMENT_ROOT"]}/myconfig.php has been created. Please review the contents of this file.</p><center>[[[Cancel:OK]]]</center>";
      //N_Redirect(FORMS_URL($form2, false)); // Doesnt work because myconfig.php changes the tmp directory where the encspec is stored...
    ';
    echo 'Machine config ('.$_SERVER['DOCUMENT_ROOT'].'/myconfig.php) not found <a href="' . FORMS_URL($form) . '">create example machine config</a></p>';
    N_Exit();
    die();
  }

  // Check for installed site
  $sgn_object = MB_Load("ims_sitecollections", "ce_sites");
  $site_object = MB_Load("ims_sites", "ce_com");
  if ($sgn_object && $site_object && $sgn_object["sites"]["ce_com"]) {
    $domain = key($site_object["domains"]);
    echo '<p>Site found (OK)</p>';
  } else {
    echo '<p>No site installed. ';

    $form = array();
    $form['title'] = 'Install site';
    $form['gotook'] = 'closeme&refreshparent';
    $form['formtemplate'] = '
      <p><b>Install site?</b><br/>
         Existing site, custom components, and database will be destroyed!<br/>
         <center>[[[OK:Install]]] [[[Cancel]]]</center></p>
    ';
    $form['postcode'] = '
      $dirs = N_Dirs("html::/openims/ce/basicsite/");
      foreach ($dirs as $dir) {
        $dest = "html::/$dir";
        $src = "html::/openims/ce/basicsite/$dir";
        N_CopyDir ($dest, $src);
      }
      FLEX_RepairCache();
      uuse("clean");
      CLEAN_Up(); // important because it creates .htaccess "deny from all" files in certain directories 
    ';
    echo '<a href="' . FORMS_URL($form) . '">install site</a></p>';
    N_Exit();
    die();
  }
  
  $admin_object = MB_Load("shield_ce_sites_users", "admin");
  $form = array();
  $form['title'] = 'Reset admin password';
  $form["metaspec"]["fields"]["password1"]["type"] = "password";
  $form["metaspec"]["fields"]["password1"]["title"] = "Password";
  $form["metaspec"]["fields"]["password1"]["required"] = true;
  $form["metaspec"]["fields"]["password2"]["type"] = "password";
  $form["metaspec"]["fields"]["password2"]["title"] = "Confirm password";
  $form["metaspec"]["fields"]["password2"]["required"] = true;
  $form['gotook'] = 'closeme&refreshparent';
  $form['formtemplate'] = '
    <table><tr><td>{{{password1}}}:</td><td>[[[password1]]]</td></tr>
           <tr><td>{{{password2}}}:</td><td>[[[password2]]]</td></tr>
           <tr><td colspan=2>&nbsp;</td></tr>
           <tr><td colspan=2>[[[OK]]]  [[[Cancel]]]</td></tr>
    </table>
  ';
  $form['postcode'] = '
    if ($data["password1"] != $data["password2"]) FORMS_ShowError(ML("Foutmelding","Error"), "Wachtwoorden komen niet overeen");
    if ($message = SHIELD_CheckIfPasswordIsWeak ("ce_sites", "admin", $data["password1"])) {
      FORMS_ShowError(ML("Foutmelding","Error"), $message, true);
    }
    $admin_object = MB_Load("shield_ce_sites_users", "admin");
    if (!$admin_object || !$admin_object["groups"]["administrators"]) {
      $admin_object = array(
        "createdby" => "autoconf " . $_SERVER["REMOTE_ADDR"],
        "createdwhen" => time(),
        "name" => "admin",
        "groups" => array("administrators" => "x")
      );

      MB_Save("shield_ce_sites_users", "admin", $admin_object);
    }
    SHIELD_SetPassword ("ce_sites", "admin", $data["password1"]);
  ';
  $url = FORMS_URL($form);
  if ($admin_object) {
    echo '<p>Admin account found (OK) <a href="'.$url.'">reset password</a></p>';
  } else {
    echo '<p>No admin account found  <a href="'.$url.'">create</a></p>';
    N_Exit();
    die();
  }

?>
<p><b>Configuration complete. Please review the contents of <?= $_SERVER['DOCUMENT_ROOT']; ?>/myconfig.php. You should be able to <a href="/openims/openims.php?mode=admin">log in</a> as admin.</p>

<p><font color="red"><b>After using the configuration tool, remove your IP address from <?= $_SERVER['DOCUMENT_ROOT']; ?>/openims/ce/autoconf/.htaccess immediately.</b></font></p>
<?
  N_Exit();
?>
</html>