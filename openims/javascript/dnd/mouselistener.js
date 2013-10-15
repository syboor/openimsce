
//----------------------------------------------------------------------\\
//								MouseListener							\\
//Purpose: Every instance of MouseListener listens to one or more 		\\
//	events from an element. When triggered it will call a function.		\\
//Options:																\\
//	Constructor: Specify to what you want to listen to.					\\
//		element: The element that creates events						\\
//		event_types: the events that have to be captured.(use the 		\\
//						constants)										\\
//	getCoordinates: returns the coordinates of the mouse when the last	\\
//						event occured in an array[x, y]					\\
//Needs: Geometry(unless you remove getCoordinates)						\\
//																		\\
//----------------------------------------------------------------------\\

function MouseListener(element, event_types, object, f, belongs_to) {
	if(element == null || event_types == 0)
		return;
	
	this.element = element;
	this.n_element = belongs_to;
	this.object = object;
	this.f = f;
	this.backup = new Array();
	
	this.mouseEvent = null;
	this.event_types = event_types;
	
	MouseListener.addListener(this);
	
	var x = event_types;
	var event_n = MouseListener.ALL+1;
	while(x >= 1){
		event_n /= 2;
		if(x >= event_n){
			x -= event_n;
			if(!(element == document && MouseListener.registerListener(event_n))){
				try{
					switch(event_n){
					case MouseListener.MOUSE_DOWN:
					case MouseListener.MOUSE_RIGHT_DOWN:
						this.backup[event_n] = element.onmousedown;
						element.onmousedown = MouseListener.EVENT_FUNCTIONS[event_n];
						break;
					case MouseListener.MOUSE_MOVE:
						this.backup[event_n] = element.onmousemove;
						element.onmousemove = MouseListener.EVENT_FUNCTIONS[event_n];
						break;
					case MouseListener.MOUSE_UP:
						this.backup[event_n] = element.onmouseup;
						element.onmouseup = MouseListener.EVENT_FUNCTIONS[event_n];
						break;
					case MouseListener.MOUSE_OVER:
						this.backup[event_n] = element.onmouseover;
						element.onmouseover = MouseListener.EVENT_FUNCTIONS[event_n];
						break;
					case MouseListener.MOUSE_OUT:
						this.backup[event_n] = element.onmouseout;
						element.onmouseout = MouseListener.EVENT_FUNCTIONS[event_n];
						break;
					}
				}catch(e){}
			}
		}
	}
	
	
	this.subListeners = new Array();			//dispatch listeners for elements inside the element
	if(element == document)
		return;
	if(element.childNodes != null){
		for(var i=0; i < element.childNodes.length; i++){
			if(element.childNodes[i] != null){
				if(this.n_element != null)
					this.subListeners[this.subListeners.length] = new MouseListener(element.childNodes[i], this.event_types, object, f, this.n_element);
				else
					this.subListeners[this.subListeners.length] = new MouseListener(element.childNodes[i], this.event_types, object, f, element);
			}
		}
	}
}

MouseListener.prototype.toString = 
function() {
	if(this.element.id)
		return "MouseListener listening to "+this.element.id;
	else
		return "MouseListener";
}

//-----------------event constants-----------------------
MouseListener.MOUSE_DOWN = 1;
MouseListener.MOUSE_RIGHT_DOWN = 2;
MouseListener.MOUSE_MOVE = 4;
MouseListener.MOUSE_UP = 8;
MouseListener.MOUSE_OVER = 16;
MouseListener.MOUSE_OUT = 32;
MouseListener.ALL = 63;

MouseListener.EVENT_STRINGS = new Array();
MouseListener.EVENT_STRINGS[MouseListener.MOUSE_DOWN] = "mousedown";
MouseListener.EVENT_STRINGS[MouseListener.MOUSE_RIGHT_DOWN] = "mousedown";
MouseListener.EVENT_STRINGS[MouseListener.MOUSE_MOVE] = "mousemove";
MouseListener.EVENT_STRINGS[MouseListener.MOUSE_UP] = "mouseup";
MouseListener.EVENT_STRINGS[MouseListener.MOUSE_OVER] = "mouseover";
MouseListener.EVENT_STRINGS[MouseListener.MOUSE_OUT] = "mouseout";

//-----------------listener functions-----------------------

//get mouse coordinats out of last event
MouseListener.prototype.getCoordinates = 
function() {
	if(this.mouseEvent != null)
		return Geometry.getCoordinatesFromEvent(this.mouseEvent);
}
//call function originially assigned to the event and our listener function
MouseListener.prototype.notify = 
function(mouseEvent, event_type) {
	if(this.backup[event_type] != null && this.backup[event_type] != MouseListener.EVENT_FUNCTIONS[event_type])
		this.backup[event_type].call(this.element, mouseEvent);
	this.mouseEvent = mouseEvent;
	
	var element = (this.n_element != null) ? this.n_element : this.element;			//determine what element to pass
	
	//calls the function with target element and the event
	if(this.object != null)
		this.f.call(this.object, element, mouseEvent);
	else
		this.f.call(element, mouseEvent);
}

//replace our functions back to the old ones(or null if there weren't any)
MouseListener.prototype.kill = 
function(){
	var x = this.event_types;
	var event_n = MouseListener.ALL+1;
	while(x >= 1){
		event_n /= 2;
		if(x >= event_n){
			x -= event_n;
			if(!(this.element == document && MouseListener.unregisterListener(event_n))){
				try{
					switch(event_n){
						case MouseListener.MOUSE_DOWN:
						case MouseListener.MOUSE_RIGHT_DOWN:
							this.element.onmousedown = this.backup[event_n];
							break;
						case MouseListener.MOUSE_MOVE:
							this.element.onmousemove = this.backup[event_n];
							break;
						case MouseListener.MOUSE_UP:
							this.element.onmouseup = this.backup[event_n];
							break;
						case MouseListener.MOUSE_OVER:
							this.element.onmouseover = this.backup[event_n];
							break;
						case MouseListener.MOUSE_OUT:
							this.element.onmouseout = this.backup[event_n];
							break;
					}
				}catch(e){}
			}
		}
	}
	
	MouseListener.removeListener(this);
	for(var i=0; i < this.subListeners.length; i++){
		if(this.subListeners[i] != null)
			this.subListeners[i].kill();
	}
}
MouseListener.prototype.listensTo = 
function(event_type){
	var x = this.event_types;
	var dec = MouseListener.ALL+1;
	while(x >= 1){
		dec /= 2;
		if(x >= dec){
			x -= dec;
			if(dec == event_type)
				return true;
		}
	}
	return false;
}

//-----------------global list with listeners-----------------------
MouseListener.listenerList = new Array();

MouseListener.addListener = 
function(listener){
	MouseListener.listenerList[MouseListener.lCount()] = listener;
}
MouseListener.lCount = 
function(){
	return MouseListener.listenerList.length;
}
MouseListener.getListener = 
function(x){
	if(x >= 0 && x < MouseListener.lCount())
		return MouseListener.listenerList[x];
	return null;
}
//returns a listener that listens to element on event_type events
MouseListener.findListener = 
function(element, event_type){
	for(var i=0; i < MouseListener.lCount(); i++){
		var listener = MouseListener.getListener(i);
		if(listener != null && listener.element == element){
			if(listener.listensTo(event_type))
				return listener;
		}
	}
}
MouseListener.removeListener = 
function(listener){
	for(var i=0; i < MouseListener.lCount(); i++){
		if(MouseListener.getListener(i) == listener)
			MouseListener.listenerList.splice(i, 1);
	}
}

//-----------------special document-wide mouse events listening-----------------------

//function that registers event listeners to the document
MouseListener.registerListener = 
function(event_type){
	if (document != null && document.addEventListener != null) {
		document.addEventListener(MouseListener.EVENT_STRINGS[event_type], MouseListener.EVENT_FUNCTIONS[event_type], true);
		if (document.setCapture)
			document.setCapture();
		return true;
	}else if (document.body != null && document.body.addEventListener != null){
		document.body.addEventListener(MouseListener.EVENT_STRINGS[event_type], MouseListener.EVENT_FUNCTIONS[event_type], true);
		if (document.body.setCapture)
			document.body.setCapture();
		return true;
	}
	return false;
}
MouseListener.unregisterListener = 
function(event_type){
	if (document != null && document.addEventListener != null) {
		document.removeEventListener(MouseListener.EVENT_STRINGS[event_type], MouseListener.EVENT_FUNCTIONS[event_type], true);
		if (document.releaseCapture)
			document.releaseCapture();
		return true;
	}else if (document.body != null && document.body.addEventListener != null){
		document.body.removeEventListener(MouseListener.EVENT_STRINGS[event_type], MouseListener.EVENT_FUNCTIONS[event_type], true);
		if (document.body.releaseCapture)
			document.body.releaseCapture();
		return true;
	}
	return false;
}

//-----------------handeling mouse events-----------------------
// function for handeling mouse events
MouseListener.mouseEvent = 
function(mouseEvent, event_type){
	mouseEvent = (mouseEvent) ? mouseEvent : ((window.event) ? window.event : null);
	var target = MouseListener.getTarget(mouseEvent);
	
	var listener = MouseListener.findListener(target, event_type);		//see if a listener for this element can be found
	if(listener != null)
		listener.notify(mouseEvent, event_type);
		
	listener = MouseListener.findListener(document, event_type);		//if the document listens to this event always notify the listener
	if(listener != null)
		listener.notify(mouseEvent, event_type);	
}
MouseListener.mouseOut = 
function(mouseEvent){
	MouseListener.mouseEvent(mouseEvent, MouseListener.MOUSE_OUT);
}
MouseListener.mouseOver = 
function(mouseEvent){
	MouseListener.mouseEvent(mouseEvent, MouseListener.MOUSE_OVER);
}
MouseListener.mouseUp = 
function(mouseEvent){
	MouseListener.mouseEvent(mouseEvent, MouseListener.MOUSE_UP);
}
MouseListener.mouseMove = 
function(mouseEvent){
	MouseListener.mouseEvent(mouseEvent, MouseListener.MOUSE_MOVE);
	return false;				//tell IE we are not selecting things right now
}
MouseListener.mouseDown = 
function(mouseEvent){
	mouseEvent = (mouseEvent) ? mouseEvent : ((window.event) ? window.event : null);
	if(MouseListener.rightButton(mouseEvent))
		MouseListener.mouseEvent(mouseEvent, MouseListener.MOUSE_RIGHT_DOWN);
	else
		MouseListener.mouseEvent(mouseEvent, MouseListener.MOUSE_DOWN);
	return false;				//tell firefox that wasn't really a mouseclick so he doesn't have to start selecting or dragging things
}
//drawback from above "return false" statements is that when the mouse leaves the window 
//the browser thinks that he doesn't own it anymore, where he does when someone is dragging it out.
//this means that the dragged element will stop moving when the mouse is outside the window.
MouseListener.EVENT_FUNCTIONS = new Array();
MouseListener.EVENT_FUNCTIONS[MouseListener.MOUSE_DOWN] = MouseListener.mouseDown;
MouseListener.EVENT_FUNCTIONS[MouseListener.MOUSE_RIGHT_DOWN] = MouseListener.mouseDown;
MouseListener.EVENT_FUNCTIONS[MouseListener.MOUSE_MOVE] = MouseListener.mouseMove;
MouseListener.EVENT_FUNCTIONS[MouseListener.MOUSE_UP] = MouseListener.mouseUp;
MouseListener.EVENT_FUNCTIONS[MouseListener.MOUSE_OVER] = MouseListener.mouseOver;
MouseListener.EVENT_FUNCTIONS[MouseListener.MOUSE_OUT] = MouseListener.mouseOut;
//-----------------function for finding the target of an event(the element that triggered it)-----------------------
MouseListener.getTarget =
function(ev)  {
	if (ev.target) {
		/* if text node (like on Safari) return parent */
		if (ev.target.nodeType == 3) {
			return ev.target.parentNode;
		} else {
			return ev.target;
		}
	} else if (ev.srcElement) {
		return ev.srcElement;
	} else {
		return null;
	}
}
MouseListener.rightButton = 
function(ev)  {
	if(ev == null)
		return false;
	if (ev.which) 
		return (ev.which == 3);
	else if (ev.button) 
		return (ev.button == 2);
}
MouseListener.setMenu = 
function(on, duration)  {
	if(on){
		document.oncontextmenu = null;
	}else{
		document.oncontextmenu = function(){return false;}
	}
	if(duration != null)
		window.setTimeout("MouseListener.setMenu(true)", duration);
}
