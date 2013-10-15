tinyMCEPopup.requireLangPack();

var ExampleDialog = {
	init : function() {
		var f = document.forms[0];

		// Get the selected contents as text and place it in the input
		f.someval.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		
	},

	insert : function() {
		
		var dfn = String;

		//tags erbij zetten
		dfn = "<dfn>"+tinyMCEPopup.editor.selection.getContent({format : 'text'})+"</dfn>";

		// Insert the contents from the input into the document
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, dfn);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(ExampleDialog.init, ExampleDialog);

