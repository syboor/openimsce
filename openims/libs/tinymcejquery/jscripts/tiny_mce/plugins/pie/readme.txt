PHP Image Editor as TinyMCE Plugin: pie

OpenSesame ICT ericd nov. 2009

http://www.opensesameict.com
http://phpimageeditor.se
http://tinymce.moxiecode.com


PHP Image Editor as TinyMCE Plugin for OpenIMS Windows install:

If C is your OpenIMS install drive:



a) PHP Image Editor:

- download the editor from http://phpimageeditor.se
- put the phpimageeditor folder in C:\openims\openims\libs\ (same level as tinymce)

- make the following 2 changes to PHP Image Editor files:

	- make 1 change to classes\phpimageeditor.php: commented (//) line 594 (567): 
	unlink($this->srcWorkWith);, otherwise no image is shown after save.

	- make 1 change to C:\openims\openims\libs\phpimageeditor\language\en-GB: SAVE AND CLOSE=Bewaren
	(instead of Save and Close)


b) Plugin pie for TinyMCE:

- download the zip from
- put the complete plugin folder "pie" in C:\openims\openims\libs\tinymce\jscripts\tiny_mce\plugins\


c) make TinyMCE aware of the plugin:

- Open the file C:\openims\nkit\uuse_tinymce.php
- add "pie" to: tinyMCE.init and theme_advanced_buttons (so the button is shown in TinyMCE)




General use:

Make sure TinyMCE is used as inline HTML editor for OpenIMS.
After opening in TinyMCE: click the image you want to edit, click the pie button (it's highlighted now), the PHP Image Editor opens in a pop-up window. Do your edits. Click the Bewaren (Save) button. Last, click "invoeren", so the edited image is inserted.



