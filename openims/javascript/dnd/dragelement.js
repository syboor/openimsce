//----------------------------------------------------------------------\\
//								DragElement								\\
//Purpose: Takes care of the thing floating under the mouse when 		\\
//			something is dragged. DragSource usually creates this and	\\
//			DropTargets use it. They check 			 					\\
//			"DragElement.hovering" to find out what is dragged			\\
//			over them.													\\
//Options:																\\
//	Constructor: Specify what this has to represent.					\\
//		dragsource: Object that contains getElement() and getData()		\\
//		getElement: Optional function that returns an element.			\\
//		dragStart: Optional function.									\\
//		dragEnd: Optional function.										\\
//Needs: MouseListener, Geometry.getPosition							\\
//																		\\
//----------------------------------------------------------------------\\

function DragElement(dragsource, getElement, dragStart, dragEnd) {
	if(DragElement.hovering != null)						//if somehow an element is still dragged it has to be removed first
		DragElement.hovering.stopDrag();
		
	this.getElementFunction = getElement;
	this.dragStartFunction = dragStart;
	this.dragEndFunction = dragEnd;
	
	this.dragsource = dragsource;
	this.droptarget = null;
	this.element = null;
	
	//settings
	this.cssClass = null;
	this.elementPos = [20, 5];
	this.cursorManager = null;	
	this.floatback = true;
	this.sign = null;
	this.content_drop = null;
	this.content_nodrop = null;
	
	//trailing sign
	this.trail = null;
	this.trailimg = null;
	//capture document mousemove and mouseup events
	this.dragListener = new MouseListener(document, MouseListener.MOUSE_MOVE, this, this.drag);
	this.cancelListener = new MouseListener(document, MouseListener.MOUSE_UP, this, this.stopDrag);
	DragElement.hovering = this;			//set to hovering element
}

DragElement.hovering = null;

DragElement.prototype.toString = 
function() {
	if(this == DragElement.hovering)
		return "DragElement; currently hovering";
	else
		return "DragElement; fugitive";
}
//-----------------settings-----------------------
DragElement.prototype.setCursors =
function(cursors){
	if(cursors != null)
		this.cursorManager = new CursorManager(cursors);
	else
		this.cursorManager = null;
}
DragElement.prototype.setCssClass =
function(cssClass){
	this.cssClass = cssClass;
}
DragElement.prototype.setElementPos = 
function(a){
	if(a != null)
		this.elementPos = a;
}
DragElement.prototype.setFloatBack = 
function(b){
	if(b != null)
		this.floatback = b;
}
DragElement.prototype.setContentDrop =
function(content){
	this.content_drop = content;
}
DragElement.prototype.setContentNoDrop =
function(content){
	this.content_nodrop = content;
}
DragElement.prototype.setSign =
function(sign){
	if(sign != null){
		this.setCursors(new Array("default", null));
	}
	this.sign = sign;
}

//-----------------public functions for other objects to use-----------------------
//the droptarget 

DragElement.prototype.getDropTarget =
function(){
	return this.droptarget;
}
DragElement.prototype.setDropTarget =
function(droptarget){
	if(this.droptarget != null && droptarget != this.droptarget && this.backuptitle) {
		this.droptarget.getElement().title = this.backuptitle;
        }
	if(droptarget != null) {
                if ("" + droptarget.getElement().title == "") {
  		  this.backuptitle = droptarget.getElement().parentNode.title;
                } else {
  		  this.backuptitle = droptarget.getElement().title;
                }
		droptarget.getElement().title = "";
	}
	this.droptarget = droptarget;
	
	//apply settings
	if(this.element){
		if(this.cssClass != null)
			this.element.className = this.droptarget ? this.cssClass[0] : this.cssClass[1];
		if(this.cursorManager != null){
			this.cursorManager.resetCursor(0);
			if(this.droptarget != null){
				this.cursorManager.setElementCursor(droptarget.getElement(), 0);
				this.cursorManager.setElementCursor(this.dragsource.getElement(), 0);
			}
		}
		if(this.sign != null){
			if(this.trailimg != null)
				this.trail.removeChild(this.trailimg);
			this.trailimg = this.droptarget ? this.sign[0] : this.sign[1];
			if(this.trailimg != null)
				this.trail.appendChild(this.trailimg);
		}
		if(this.droptarget != null && this.content_drop != null)
			this.element.innerHTML = this.content_drop;
		else if(this.droptarget == null && this.content_nodrop != null)
			this.element.innerHTML = this.content_nodrop;
	}
}
DragElement.prototype.getDragSource =
function(){
	return this.dragsource;
}
DragElement.prototype.getElement =
function(){
	return this.element;
}
DragElement.prototype.setElement =
function(element){
	this.element = element;
}

//-----------------handeling mouse events-----------------------
//called when the element is dragged
DragElement.prototype.drag =
function(element, mouseEvent){	
	var coords = this.dragListener.getCoordinates();

	if(this.element == null){		//if the dragelement doesn't exist yet
		if(this.startPos == null){			//make sure the element won't appear until the mouse is dragged 5-7 pixels 
			this.startPos = coords;
			return;
		}else if(Math.abs(this.startPos[0] - coords[0]) > 5 || Math.abs(this.startPos[1] - coords[1]) > 5 ){
			if(this.dragStartFunction != null)
				this.dragStartFunction.call(this.getDragSource());	//tell function we are going to drag this
			this.setElement(this.createDragElement());		//create the element that is dragged with the mouse
		}else
			return;
	}
	if(this.sign != null && this.trailimg != null){
		var trailpos = new Array(coords[0]+3, coords[1] + 20);
		trailpos = Geometry.keepElementInScreen(trailpos, this.trail);
		this.trail.style.left = trailpos[0]+"px";
		this.trail.style.top = trailpos[1]+"px";
	}
	Geometry.handleScroll(coords);
	
	coords[0] += this.elementPos[0];		//keep the dragelement from getting under the mousepointer
	coords[1] += this.elementPos[1];		//		otherwise droptargets won't receive mouseover events
	coords = Geometry.keepElementInScreen(coords, this.getElement());
	
	this.getElement().style.left = coords[0]+"px";
	this.getElement().style.top = coords[1]+"px";
}

//called when the mousebutton is released
DragElement.prototype.stopDrag =
function(element, mouseEvent){
	//remove listeners
	this.dragListener.kill();
	this.cancelListener.kill();
	if(this.cursorManager != null)
		this.cursorManager.resetAll();
			
	
	if(this.getElement() != null){
		if(this.sign != null)
			window.document.body.removeChild(this.trail);	//remove the drag element
		if (this.getDropTarget() == null && this.floatback){
			DragElement.startElement = this.getElement();
			DragElement.endElement = this.getDragSource().getElement();
			DragElement.jumpAnimation();
			this.setElement(null);
		}else{
			window.document.body.removeChild(this.getElement());	//remove the drag element
			this.setElement(null);
		}
		if(this.getDragSource().rightclick)
			MouseListener.setMenu(false, 10);
	}
	this.dragEndFunction.call(this.getDragSource(), this.getDropTarget());
	DragElement.hovering = null;
	this.setDropTarget(null);
}

//-----------------functions for for the visible dragelement-----------------------
//creates the element that is dragged over the screen
DragElement.prototype.createDragElement =
function(){
	//Create a floating layer with a red border
	var element = document.createElement("div");
	element.style.position = "absolute";
	element.style.top = "1px";
	element.style.left = "1px";
	if(this.cssClass != null)
		element.className = this.cssClass[1];
		
	if(this.content_nodrop != null){
		element.innerHTML = this.content_nodrop;
	//Try to get the element from the function that is given in the constructor
	}else if(this.getElementFunction != null){
		element.appendChild(this.getElementFunction.call(this, this.getDragSource().getElement(), this.getDragSource().getData()));
	//Else clone the dragged object
	}else{
		var contents = this.getDragSource().getElement().cloneNode(true);
		contents.id += "dragged";
		if(contents.childNodes != null){
			for(var i=0; i < contents.childNodes.length; i++){
				if(contents.childNodes[i] != null && contents.childNodes[i].id != null){
					contents.childNodes[i].id += "dragged";
				}
			}
		}
		element.appendChild(contents);
	}
	window.document.body.appendChild(element);	//add it all to the body
	
	if(this.cursorManager != null){
		this.cursorManager.setElementCursor(document.body, 1);
		this.cursorManager.setElementCursor(this.dragsource.getElement(), 1);
	}
		
	if(this.sign != null){
		this.trail = document.createElement("div");
		this.trail.style.position = "absolute";
		this.trail.style.top = "1px";
		this.trail.style.left = "1px";
		this.trailimg = this.sign[1];
		if(this.trailimg != null)
			this.trail.appendChild(this.trailimg);
		window.document.body.appendChild(this.trail);	//add it all to the body
	}
	
	return element;
}

//animation of released drag element jumping back to where it was
DragElement.animspeed = 50; //pixels per 5 milliseconds
DragElement.startElement = null;
DragElement.endElement = null
DragElement.jumpAnimation =
function(xd, yd){
	var startpos = Geometry.getPosition(DragElement.startElement, null);
	var endpos = Geometry.getPosition(DragElement.endElement, null);
	var xlen = endpos[0] - startpos[0];
	var ylen = endpos[1] - startpos[1];
	var distance = Math.sqrt(xlen*xlen + ylen*ylen);
	
	if(distance < DragElement.animspeed){
		window.document.body.removeChild(DragElement.startElement);	//remove the drag element
		DragElement.startElement = null;
		return;
	}
	
	var firsttime= false;
	if(xd == null || yd == null){
		var steps = distance / DragElement.animspeed;
		xd = Math.round(xlen/steps);
		yd = Math.round(ylen/steps);
	}
	
	DragElement.startElement.style.left = startpos[0] + xd+"px";
	DragElement.startElement.style.top = startpos[1] + yd+"px";
	window.setTimeout("DragElement.jumpAnimation("+xd+","+yd+");", 10);
}