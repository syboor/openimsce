(function(){tinymce.PluginManager.requireLangPack('afkort');tinymce.create('tinymce.plugins.AfkortPlugin',{init:function(ed,url){ed.addCommand('mceAfkort',function(){ed.windowManager.open({file:url+'/dialog.htm',width:320+ed.getLang('afkort.delta_width',0),height:130+ed.getLang('afkort.delta_height',0),inline:1},{plugin_url:url,some_custom_arg:'custom arg'})});ed.addButton('afkort',{title:'afkort.desc',cmd:'mceAfkort',image:url+'/img/abbr.gif'})},createControl:function(n,cm){return null},getInfo:function(){return{longname:'Afkorting plugin',author:'OpenSesame ICT',authorurl:'http://www.osict.com',infourl:'http://www.osict.com',version:"2.0"}}});tinymce.PluginManager.add('afkort',tinymce.plugins.AfkortPlugin)})();