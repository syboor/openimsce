<?php
include (getenv ("DOCUMENT_ROOT"). "/nkit/nkit.php");

/* If you change this, you might want to change the fckeditor version too (in /openims/libs/fckeditor/plugins/dms/bin/dms.php) */

///////////////////////////////////////////////////////////////////////////////////   [[[dmstree]]]

global $currentfolder;

$sgn = IMS_SuperGroupname(); 

global $myconfig;

if (($myconfig[$sgn]["ml"]["metadatalevel"] && $myconfig[$sgn]["ml"]["metadatafiltering"] != "no") || 
     $myconfig[$sgn]["ml"]["metadatafiltering"] == "yes") {
  N_SetOutputFilter('FORMS_ML_Filter');
}

$tinymcecasetype = $myconfig[IMS_SuperGroupName()]["tinymcecasetype"];
$caseResult = array();

// Make certain that this script can not be called directly, but only using the hyperlink from tinymce
$shielddummy = $_REQUEST["shielddummy"];
SHIELD_Decode($shielddummy);
SHIELD_RequireLogon($sgn);

if ( $tinymcecasetype )
{
    $caseDataTable = "ims_".$sgn."_case_data";
    $specs = array();
    $specs["sort"] = '$record["shorttitle"]';
    $specs["value"] = '$record["shorttitle"]';
    $specs["select"]['$record["category"]'] = $tinymcecasetype;

    $caseResult = MB_TurboMultiQuery( $caseDataTable, $specs );
    if ( !$myconfig[IMS_SuperGroupName()]["tinymcedmsroot"] ) 
      $myconfig[IMS_SuperGroupName()]["tinymcedmsroot"] = key( $caseResult ) . "root";
} 
else if ( empty( $myconfig[IMS_SuperGroupName()]["tinymcedmsroot"] ) )
{
  die (ML("DMS rootfolder niet ingesteld in sitecollectie configuratie","DMS rootfolder not set in sitecollection config"));
}

$linkDMS = "http://".$_SERVER['HTTP_HOST']."/openims/openims.php?mode=dms&currentfolder=".$myconfig[IMS_SuperGroupName()]["tinymcedmsroot"];

$rootfolder = $myconfig[IMS_SuperGroupName()]["tinymcedmsroot"];

$contentpage = "dms"; 

if(!$currentfolder) $currentfolder=$rootfolder;
else if ( $tinymcecasetype ) //// JG - NVZA-1
  $rootfolder = substr( $currentfolder , 0 , 34 ) . "root";

$tree = CASE_TreeRef ($sgn, $currentfolder);

$action = $contentpage . '.php?shielddummy='.$shielddummy.'&currentfolder=$id&rootfolder='.$rootfolder;
$treestring = TREE_CreateDHTML ($tree, $action, $currentfolder, true, $rootfolder);
$dmstree = strtr($treestring,array ("folder_secure.gif" => "folder.gif") );


///////////////////////////////////////////////////////////////////////////////////   [[files]]]

$content = "";

if($context["flexparams"]) {
  $dir = $context["flexparams"];
} else {
  global $currentfolder;
  $dir = $currentfolder;
}

$files = MB_TurboSelectQuery ("ims_".$sgn."_objects", array (
           '$record["directory"]' => $dir,
           '$record["published"]' => "yes"
         ), '$record["shorttitle"]');

$shortcuts = MB_TurboSelectQuery ("ims_".$sgn."_objects", array (
           '$record["objecttype"]' => "shortcut",
           '$record["directory"]' => $dir
         ), '$record["base_shorttitle"]');

$files = N_array_merge($files, $shortcuts);
asort($files,SORT_STRING);

T_Start("ims", array ("noheader"=>true));

	echo "<a onclick=\"getAll('$dir.dir','folder','.dir')\" title=\"folder\" href=\"#\"><img border=0 src=\"/ufc/rapid/openims/openfolder.gif\"></a>";
	
    T_Next();
	
	echo "<a onclick=\"getAll('$dir.dir','folder','.dir')\" title=\"folder\" href=\"#\">folder</a>";
	
    T_NewRow();


foreach ($files as $id => $title)
{
  $obj = IMS_AccessObject ($sgn, $id);
  if ($obj["objecttype"] == "shortcut")
  {
     $id = $obj["source"];
     $obj = IMS_AccessObject ($sgn, $id);
     $title = $obj["shorttitle"];
  } 

	echo "<a onclick=\"getAll('".FILES_DocPublishedURL($sgn, $id)."','".$obj["shorttitle"]."','".substr($obj["filename"], -4)."')\"
	title=\"".$obj["longtitle"]."\" href=\"#\"><img border=0 src=\"".FILES_Icon($sgn, $id)."\"></a>";
	
    T_Next();
	
	echo "<a onclick=\"getAll('".FILES_DocPublishedURL($sgn, $id)."','".$obj["shorttitle"]."','".substr($obj["filename"], -4)."')\"
	title=\"".$obj["longtitle"]."\" href=\"#\">$title</a>";
	
    T_NewRow();
	 
}
$content = TS_End();

/////////////////////////////////////////////////////////////////////////////////////

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>{#dms_dlg.title}</title>
<script type="text/javascript" src="../../../tiny_mce_popup.js"></script>
<script type="text/javascript" src="../js/dialog.js"></script>
<link href="../css/dms.css" rel="stylesheet" type="text/css" />
<style type="text/css">
.dms {
	margin-left:15px;
}
.content {
margin-top:5px;
margin-bottom:5px;
margin-left:45px;
overflow-y:scroll;
height:460px;
}
.show {
display:none;
}

</style>
<script language="javascript">
function getLink(ref,name) {
	document.getElementById('txt_naam').value = name; //write name
	document.getElementById('txt_link').value = ref; //write link
	document.getElementById('buttons').style.display = 'block';
	document.getElementById('name').style.display = 'block';
	document.getElementById('img').style.display = 'none';
}
function getImage(ext) {
	if(ext == '.jpg' || ext == 'jpeg' || ext == '.gif' || ext == '.png' || ext == '.bmp' || ext == '.swf' || ext == '.flv') {
	document.getElementById('img').style.display = 'block';	
	}
}
function getAll(ref,name,ext) {
	getLink(ref,name);
	getImage(ext);
}
function upScreen() {
	window.resizeBy(100,100);
}
function downScreen() {
var winW = 630, winH = 460;

if (parseInt(navigator.appVersion)>3) {
 if (navigator.appName=="Netscape") {
  winW = window.innerWidth;
  winH = window.innerHeight;
 }
 if (navigator.appName.indexOf("Microsoft")!=-1) {
  winW = document.body.offsetWidth;
  winH = document.body.offsetHeight;
 }
}

	if (winW > 600) {
	window.resizeBy(-100,-100);
	}
}
</script>
</head>
<body>

<form onsubmit="DmsDialog.insert();return false;" action="#">
<div id="frmbody">

<!-- JG - start here //-->
<?

    if ( count( $caseResult ) > 0 )
    {
      if ( !isset( $caseResult[substr($currentfolder,0,34)] ) )
        die( ML( "De huidige folder heeft een onjuist dossiertype." , "The current folder has an Invalid case or case has wrong casetype." ) );

      if ( $myconfig[$sgn]["casetext"] )
        echo $myconfig[$sgn]["casetext"];
      else
        echo ML("Dossier","Case");

      echo ' : <select name="currentfolder" onchange="location.href=\'dms.php?shielddummy='.$shielddummy.'&currentfolder=\'+this.options[this.selectedIndex].value+\'root\';">';

      foreach( $caseResult AS $dossier => $naam ) 
        echo "<option value='" . $dossier . "' " . ( $dossier==substr($currentfolder,0,34)?"selected":"" ) . ">" . htmlentities( $naam ) . "</option>";

      echo '</select>';
    }
?>
<!-- JG - end here //-->

  <table width="100%" border="0">
    <tr>
      <td width="70%"><div align="left"><strong>{#dms_dlg.desc}</strong></div></td>
      <td width="20%"><div align="right"><a href="#" onclick="window.open('<?=$linkDMS;?>', 'dms','width=1024, height=768')">{#dms_dlg.dmslink}</a> </div></td>
      <td width="10%"><div align="right"><a href="#" onclick="downScreen()"><img src="../img/down.gif" alt="{#dms_dlg.down}" width="17" height="17" border="0" /></a>&nbsp;&nbsp;&nbsp;<a href="#" onclick="upScreen()"><img src="../img/up.gif" alt="{#dms_dlg.up}" width="17" height="17" border="0" /></a></div></td>
    </tr>
  </table>
  <table width="100%" border="0">
    <tr>
      <td colspan="3"><hr /></td>
    </tr>
    <tr>
      <td width="10%" valign="top"><div class="dms"><?php echo $dmstree; ?></div></td>
      <td valign="top"><div class="content"><?php echo $content; ?></div></td>
    </tr>
    <tr>
      <td colspan="3"><hr /></td>
    </tr>
	<tr>
      <td colspan="2"><div id="name" class="show">{#dms_dlg.name}&nbsp;&nbsp;&nbsp;&nbsp;<input name="txt_naam" type="text" id="txt_naam" size="30" /></div>&nbsp;<div id="img" class="show"><input name="ch_img" id="ch_img" type="checkbox" value="1" />&nbsp;{#dms_dlg.img}</div></td>
      <td><input name="txt_link" type="hidden" id="txt_link" size="40" /></td>
    </tr>
  </table>
  </div>
  <p>
  <div id="buttons" class="show">
    <input type="button" id="insert" name="insert" value="{#insert}" onclick="DmsDialog.insert();" />
    <input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
  </div>
  </p>
 </form>
</body>
</html>