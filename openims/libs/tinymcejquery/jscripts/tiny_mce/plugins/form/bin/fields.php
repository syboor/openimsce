<?php
include (getenv ("DOCUMENT_ROOT"). "/nkit/nkit.php");

  global $myconfig;
  $sgn = IMS_SuperGroupName();
  if (($myconfig[$sgn]["ml"]["metadatalevel"] && $myconfig[$sgn]["ml"]["metadatafiltering"] != "no") || 
       $myconfig[$sgn]["ml"]["metadatafiltering"] == "yes") {
    N_SetOutputFilter('FORMS_ML_Filter');
  }

  //formfields
  $allfields = MB_Ref ("ims_fields", IMS_SuperGroupName());
  $velden = "";
  
  foreach ($allfields as $key=>$value) {
  	$velden .= "$key:{$value['title']};";
  	}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{#form_dlg.title}</title>
	<script type="text/javascript" src="../../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="../js/dialog.js"></script>
</head>
<body>
<form onsubmit="FormDialog.insert();return false;" action="#">
  <table border="0">
    <tr>
      <td width=90%>{#form_dlg.text} </td>
      <td width="40"><select name="txt_fields" id="txt_fields">
<?php

$veldenTMP = explode(';',$velden);
foreach ($veldenTMP as $value) {
	$veldenTMP2 = explode(':',$value);
	if (!empty($veldenTMP2[0]))
		echo "<option value=\"{$veldenTMP2[0]}\">{$veldenTMP2[1]}</option>";
}
?>
      </select></td>
      <td width="10">&nbsp;</td>
      <td width="20"><input name="txt_labels" type="checkbox" id="txt_labels" value="1" /></td>
      <td width="75">label</td>
    </tr>
  </table>
  <p>
    <input type="button" id="insert" name="insert" value="{#insert}" onclick="FormDialog.insert();" />
    <input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
    </div>
</p>
</form>
</body>
</html>