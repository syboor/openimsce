tinyMCEPopup.requireLangPack();

var pieDialog = {
	init : function() {
	
	},

	insert : function() {
		
                var valueFromEditor = getIT();
		var imgHtml = '<img src="'+valueFromEditor+'"/>';
				
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, imgHtml);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(pieDialog.init, pieDialog);