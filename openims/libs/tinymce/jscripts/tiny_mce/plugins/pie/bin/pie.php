<?
//ericd nov 2009. phpimageeditor as tinymce plugin
include (getenv ("DOCUMENT_ROOT"). "/nkit/nkit.php");
uuse("assets");

//tnx to ndv
if (!$_GET["theimage"]) {
?>
	<script type="text/javascript" src="../../../tiny_mce_popup.js"></script>
	<script type="text/javascript">
		tinyTagSrc = tinyMCEPopup.editor.selection.getContent({format : 'IMG'});
		if(tinyTagSrc == '') {
			alert("Foutmelding: Selecteer eerst een afbeelding om te bewerken.");
			tinyMCEPopup.close();
		}
      
      //get contents of src part
      var begin = tinyTagSrc.indexOf('src="');
		tinyTagSrc = tinyTagSrc.substring(begin+5);
		var end = tinyTagSrc.indexOf('"');
		tinyTagSrc = tinyTagSrc.substring(0,end);
      window.location = window.location + "?theimage=" + tinyTagSrc;
      
   </script>	
<?  
  die ("");
} else {
    $theimage = $_GET["theimage"];
    $sgn = IMS_SuperGroupName();
    $imagename = N_KeepAfter ($theimage, "/", true);
	if (strpos (" $theimage", "http:")) {
	  $imageurl = $theimage;
	} else {
	  $imageurl = "http://".getenv("HTTP_HOST").$theimage;
	}
	$imagecontent = N_GetPage ($imageurl);
	$doc = ASSETS_StoreImage ($sgn, $imagename, $imagecontent);
	$theurl = str_replace("/ufc/rapid/", "/", $doc);
	$doc = str_replace("/ufc/rapid/", getenv("DOCUMENT_ROOT")."/", $doc);
    $frameurl = "http://".getenv("HTTP_HOST")."/openims/libs/phpimageeditor/index.php?&imagesrc=". $doc;	
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>{#pie_dlg.title}</title>
	<script type="text/javascript" src="../../../tiny_mce_popup.js"></script>
	<script type="text/javascript" src="../js/dialog.js"></script>
	<script type="text/javascript">	
		function getIT() {
			return '<? echo $theurl; ?>';
		}
	</script>	
</head>
<body>

<form onsubmit="pieDialog.insert();return false;" action="#">
Bewerk de afbeelding, klik eerst op Bewaren, en daarna op Invoegen.<br />
    <input type="button" id="insert" name="insert" value="{#insert}" onclick="pieDialog.insert();" />
    <input type="button" id="cancel" name="cancel" value="{#cancel}" onclick="tinyMCEPopup.close();" />
</form>
<iframe id="pieiframe" src="<? echo $frameurl; ?>" width="800" height="800">
	<p>Your browser does not support iframes.</p>
</iframe>

</body>
</html>