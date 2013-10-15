tinyMCEPopup.requireLangPack();

var ManualDialog = {
	init : function() {
		
	},

	insert : function() {
		
		// tags met input weer terugsturen
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, manual);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(ManualDialog.init, ManualDialog);

