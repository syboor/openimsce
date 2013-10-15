tinyMCEPopup.requireLangPack();

var DmsDialog = {
	init : function() {
	},

	insert : function() {
		
		var f = document.forms[0];
		var form = String;
                url = '';
// alert (f.ch_img.checked);
// alert (f.txt_link.value);
// alert (f.txt_link.value.substr (f.txt_link.value.length-4, 4));
		if (f.ch_img.checked) { // insert as real image
                    if (f.txt_link.value.substr (f.txt_link.value.length-4, 4) == ".jpg") {
  		      url = '<img src="' + f.txt_link.value + '" alt="' + f.txt_naam.value + '" />';
                    }
                    if (f.txt_link.value.substr (f.txt_link.value.length-4, 4) == ".gif") {
  		      url = '<img src="' + f.txt_link.value + '" alt="' + f.txt_naam.value + '" />';
                    }
                    if (f.txt_link.value.substr (f.txt_link.value.length-4, 4) == ".png") {
  		      url = '<img src="' + f.txt_link.value + '" alt="' + f.txt_naam.value + '" />';
                    }
                    if (f.txt_link.value.substr (f.txt_link.value.length-4, 4) == ".swf") {
                         url = '[[[flash:' + f.txt_link.value + ']]]';
                    }
                    if (f.txt_link.value.substr (f.txt_link.value.length-4, 4) == ".flv") {
                         url = '[[[flvplayer:' + f.txt_link.value + ']]]';
                    }
		} else {	// insert just link
                   if (f.txt_link.value.substr (f.txt_link.value.length-4, 4) == ".dir") {
                     url = '[[[folder:' + f.txt_link.value.substr (0, f.txt_link.value.length-4) + ']]]';
                   } else {
    		     url = '<a title="' + f.txt_naam.value + '" href="' + f.txt_link.value + 
		     '" onclick="window.open(this.href); return false;" onkeypress="window.open(this.href); return false;">' + f.txt_naam.value + '</a>';
                   }
		}
		
		tinyMCEPopup.editor.execCommand('mceInsertContent', false, url);
//              if (f.txt_link.value.substr (f.txt_link.value.length-4, 4) == ".swf") {
//    		    tinyMCEPopup.editor.execCommand('mceRepaint');
//                  tinyMCE.selectedInstance.repaint();
//    		    tinyMCEPopup.editor.repaint();
//              }

		tinyMCEPopup.close();
	}
};

tinyMCEPopup.onInit.add(DmsDialog.init, DmsDialog);