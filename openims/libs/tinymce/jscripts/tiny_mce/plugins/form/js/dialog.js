tinyMCEPopup.requireLangPack();

var FormDialog = {
	init : function() {
	},

	insert : function() {
		
		var f = document.forms[0];
		var form = String;
		
		if (f.txt_labels.checked) {
		form = "{{{" + f.txt_fields.value + "}}}";
		}
		else {
		form = "[[[" + f.txt_fields.value + "]]]";
		}

		tinyMCEPopup.editor.execCommand('mceInsertContent', false, form);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(FormDialog.init, FormDialog);