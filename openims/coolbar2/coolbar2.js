/* Declare a namespace for the site */

function openImsToolbar_compatMode()
{
	var mode=document.compatMode;
	if(mode)
	{
		if( mode=='BackCompat' )
			m='Quirks';
		else if(mode=='CSS1Compat')m='Standards Compliance';
		else m='Almost Standards Compliance';
		alert('The document is being rendered in '+m+' Mode.');
	}
}

function openImsToolbar_isQuirks()
{
	var mode=document.compatMode;
	if(mode)
	{
		if( mode=='BackCompat' )
			return true;
	}
	return false;
}

if (window.attachEvent) 
{
	window.attachEvent('onload', openImsToolbar_onload);
}
else if (window.addEventListener) 
{
	window.addEventListener('load', openImsToolbar_onload, false);
}
else {
	document.addEventListener('load', openImsToolbar_onload, false);
}

function openImsToolbar_toggleDisplay()
{

	var toolbar = document.getElementById('OpenIMSToolbar');
	if ( toolbar )
	{
		var display = openImsToolbar_cookie( "openImsToolbarDisplay" );
		if ( !display ) display="block";
		display = display=="block" ? "none" : "block";
		openImsToolbar_cookie( "openImsToolbarDisplay" , display , { path: '/' } );
		toolbar.style.display = display;
		var toolbarButton = document.getElementById('OpenIMSToolbarButton_a');
		if ( display!="none" ) 
			toolbarButton.className = "active";
		else
			toolbarButton.className = "";
	}
}

function openImsToolbar_onload()
{
	var display = openImsToolbar_cookie( 'openImsToolbarDisplay' );
//	if ( display && display=="none") 
//		openImsToolbar_toggleDisplay( "none" );
}

function openImsToolbar_loadJsCssFile(filename, filetype)
{
		 if (filetype=="js")
		 { //if filename is a external JavaScript file
				   var fileref=document.createElement('script')
						     fileref.setAttribute("type","text/javascript")
							   fileref.setAttribute("src", filename)
		 }
		 else if (filetype=="css"){ //if filename is an external CSS file
				    var fileref=document.createElement("link")
							  fileref.setAttribute("rel", "stylesheet")
							    fileref.setAttribute("type", "text/css")
								  fileref.setAttribute("href", filename)
								   }
		   if (typeof fileref!="undefined")
			     document.getElementsByTagName("head")[0].appendChild(fileref);
}
// openImsToolbar_loadjscssfile("mystyle.css", "css");


function openImsToolbar_trim( text )
{
	var trimLeft = /^\s+/;
	var trimRight = /\s+$/;
	return text == null ? "" : text.toString().replace( trimLeft, "" ).replace( trimRight, "" );
}

function openImsToolbar_cookie( name, value, options ) 
{
		if (typeof value != 'undefined') { // name and value given, set cookie
			options = options || {};
			if (value === null) {
				value = '';
				options.expires = -1;
			}
			var expires = '';
			if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
				var date;
				if (typeof options.expires == 'number') {
					date = new Date();
					date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
				} else {
					date = options.expires;
				}
				expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
			}
			// CAUTION: Needed to parenthesize options.path and options.domain
			// in the following expressions, otherwise they evaluate to undefined
			// in the packed version for some reason...
			var path = options.path ? '; path=' + (options.path) : '';
			var domain = options.domain ? '; domain=' + (options.domain) : '';
			var secure = options.secure ? '; secure' : '';
			document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
		} else { // only name given, get cookie
			var cookieValue = null;
			if (document.cookie && document.cookie != '') {
				var cookies = document.cookie.split(';');
				for (var i = 0; i < cookies.length; i++) {
					var cookie = openImsToolbar_trim(cookies[i]);
					// Does this cookie string begin with the name we want?
					if (cookie.substring(0, name.length + 1) == (name + '=')) {
						cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
						break;
					}
				}
			}
			return cookieValue;

	}
}
/*		
			// store settings in array and init ToolBar
			var OIT_settings = new Array();
			OIT_settings['ptm_display'] = "none";
			$('#pit_tinymenu_items').hide();		
			// toggle the OIT based on cookie

			if ($.cookie('OIT_display')) {
				OIT_settings['OIT_display'] = $.cookie('OIT_display');
				if(OIT_settings['OIT_display'] == "none") {
					bar.hide();
					$('#OpenIMSToolbarButton a').removeClass('active');
				} else {
					bar.show();
					$('#OpenIMSToolbarButton a').addClass('active');
				}
			} else {
				$.cookie('OIT_display', OIT_settings['OIT_display'], { path: '/' });	
			}
			// toggle the OIT when button is clicked
			$('#OpenIMSToolbarButton a').click(function() {
				bar.slideToggle('slow', function() {
					OIT_settings['OIT_display'] = ( OIT_settings['OIT_display'] == "none" )? "block" : "none"											
					$.cookie('OIT_display', OIT_settings['OIT_display'], { path: '/' });	
				});
				$(this).toggleClass('active');
*/
