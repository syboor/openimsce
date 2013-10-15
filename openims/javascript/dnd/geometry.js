
//----------------------------------------------------------------------\\
//								Geometry								\\
//Purpose: Convenient set of functions that help coordinates, sizes and	\\
//			positions without having to go through any crossbrowser 	\\
//			trouble.													\\
//Options: Just check out the functions.								\\
//																		\\
//----------------------------------------------------------------------\\


function Geometry(){
	//empty constructor
}

// Compute document coordinates of the mousepointer
Geometry.getCoordinatesFromEvent =
function(ev){
	if (ev.pageX != null) {
		var docX = ev.pageX;
		var docY = ev.pageY;
	} else if (ev.clientX != null) {
		var docX = ev.clientX + document.body.scrollLeft - document.body.clientLeft;
		var docY = ev.clientY + document.body.scrollTop - document.body.clientTop;
		if (document.body.parentElement) {
				var bodParent = document.body.parentElement;
				docX += bodParent.scrollLeft - bodParent.clientLeft;
				docY += bodParent.scrollTop - bodParent.clientTop;
		}
	}
	return [docX, docY];
}

//modifies coordinates so dragelement stays within the screen so no scrollbars appear AND the screen doesn't scroll by itself
Geometry.keepElementInScreen = 
function(coords, element){
	var scroll = Geometry.getScroll();
	
	if(coords[0] < scroll[0])
		coords[0] = scroll[0];
	if(coords[1] < scroll[1])
		coords[1] = scroll[1];
	
	var windowsize = Geometry.getWindowSize();
	var elementsize = Geometry.getSize(element);
	
	if(coords[0] > (scroll[0] + windowsize[0]) - elementsize[0])
		coords[0] = (scroll[0] + windowsize[0]) - elementsize[0];
	if(coords[1] > (scroll[1] + windowsize[1]) - elementsize[1])
		coords[1] = (scroll[1] + windowsize[1]) - elementsize[1];
		
	return coords;
}
Geometry.handleScroll = 
function(coords){
	var scroll = Geometry.getScroll();
		
	if(coords[1] < scroll[1]+20){
		if(Geometry.stopScrolling == true){
			Geometry.stopScrolling = false;
			Geometry.scrollPage(-2);
		}
		return;
	}
		
	var windowsize = Geometry.getWindowSize();
	if(coords[1] > scroll[1] + windowsize[1] - 20){
		if(Geometry.stopScrolling == true){
			Geometry.stopScrolling = false;
			Geometry.scrollPage(2);
		}
		return;
	}
	Geometry.stopScrolling = true;
}
Geometry.stopScrolling = true;
Geometry.scrollPage =
function(y){
	window.scrollBy(0,y);
	y += (y>0) ? .3 : -.3;
	
	var scroll = Geometry.getScroll();
	var wsize = Geometry.getWindowSize();
	var psize = Geometry.getPageSize();
	if(scroll[1] == 0)
		Geometry.stopScrolling = true;
	else if(scroll[1] + wsize[1] == psize[1])
		Geometry.stopScrolling = true;
	
	if(!Geometry.stopScrolling)
		window.setTimeout("Geometry.scrollPage("+y+");", 5);
}

//get the size of the screen in which the webpage is viewed
Geometry.getWindowSize =
function() {
	var p = [0, 0];
	if (window.innerWidth) {
		p[0] = window.innerWidth;
		p[1] = window.innerHeight;
	} else if (window.document.body.parentElement.clientWidth) {
		p[0] = window.document.body.parentElement.clientWidth;
		p[1] = window.document.body.parentElement.clientHeight;
	} else if (window.document.body && window.document.body.clientWidth) {
		p[0] = window.document.body.clientWidth;
		p[1] = window.document.body.clientHeight;
	}
	return p;
}
Geometry.getPageSize =
function() {
	var p = [0, 0];
	var test1 = document.body.scrollHeight;
	var test2 = document.body.offsetHeight
	if (test1 > test2){ // all but Explorer Mac
		p[0] = document.body.scrollWidth;
		p[1] = document.body.scrollHeight;
	}else{ // Explorer Mac; //would also work in Explorer 6 Strict, Mozilla and Safari
		p[0] = document.body.offsetWidth;
		p[1] = document.body.offsetHeight;
	}
	return p;
}
//return the size of an element
Geometry.getSize = 
function(htmlElement) {
	var p = [0, 0];
	if (htmlElement.offsetWidth != null) {
		p[0] = htmlElement.offsetWidth;
		p[1] = htmlElement.offsetHeight;
	} else if (htmlElement.clip && htmlElement.clip.width != null) {
		p[0] = htmlElement.clip.width;
		p[1] = htmlElement.clip.height;
	} else if (htmlElement.style && htmlElement.style.pixelWidth != null) {
		p[0] = htmlElement.style.pixelWidth;
		p[1] = htmlElement.style.pixelHeight;
	}
	p[0] = parseInt(p[0]);
	p[1] = parseInt(p[1]);
	return p;
}
//returns position of an element
Geometry.getPosition =
function(htmlElement, containerElement) {
	var p = [0, 0];
	// EMC 6/3/2005
	// changed the below line, since it meant we did not 
	// include the given element in our location calculation.
	//var offsetParent = htmlElement.offsetParent;
	var offsetParent = htmlElement;
	while (offsetParent != containerElement) {
		p[0] += offsetParent.offsetLeft;
		p[1] += offsetParent.offsetTop;
		offsetParent = offsetParent.offsetParent;
	}
	return p;
}

Geometry.getScroll =
function(){
	var scroll = new Array();
	if(self.pageYOffset){ // all except Explorer
		scroll[0] = self.pageXOffset;
		scroll[1] = self.pageYOffset;
	}else if (document.documentElement && document.documentElement.scrollTop){// Explorer 6 Strict
		scroll[0] = document.documentElement.scrollLeft;
		scroll[1] = document.documentElement.scrollTop;
	}else if (document.body){ // all other Explorers
		scroll[0] = document.body.scrollLeft;
		scroll[1] = document.body.scrollTop;
	}
	return scroll;
}