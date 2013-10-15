tinyMCEPopup.requireLangPack();

var AfkortingDialog = {
	init : function() {
		var f = document.forms[0];

		// Get the selected contents as text and place it in the input
		f.someval.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		
	},

	insert : function() {
		
		var d = document.forms[0];
		var titel = d.title.value;

		//tags erbij zetten
		afkort = "<acronym title=\""+ titel +"\">"+tinyMCEPopup.editor.selection.getContent({format : 'text'})+"</acronym>";

		// tags met input weer terugsturen
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, afkort);
		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(AfkortingDialog.init, AfkortingDialog);