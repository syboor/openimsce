
//----------------------------------------------------------------------\\
//								DropTarget								\\
//Purpose: Add an element to an instance of this class to make it 		\\
//	able to receive draggable objects. A mouseover and mousemove event	\\
//	will alert this class to hovering draggables.						\\
//Options:																\\
//	Constructor: Make a new DropTarget everytime you want to register	\\
//					different functions for draggables.					\\
//		requestDrop: a function that is called when a draggable is 		\\
//						hovering over an element. if it returns true,	\\
//						a drop is allowed, if false it's denied.		\\
//		drop: called after a succesful drop on a droptarget.			\\
//	AddElement/removeElement: Use this to add or remove droptarget- 	\\
//								elements.								\\
//	AddElementById/removeElementById: same functions but with ids		\\
//Needs: MouseListener 
//																		\\
//----------------------------------------------------------------------\\

function DropTarget(requestDrop, drop) {
	this.dropPermission = requestDrop;
	this.dropFunction = drop;
	
	this.element = null;
}

DropTarget.prototype.toString = 
function() {
	return "DropTarget";
}

//-----------------adding and removing elements-----------------------
DropTarget.prototype.addElement =
function(element){
	new MouseListener(element, MouseListener.MOUSE_OVER, this, this.hover);
	new MouseListener(element, MouseListener.MOUSE_MOVE, this, this.hover);
	new MouseListener(element, MouseListener.MOUSE_OUT, this, this.cancel);
}

DropTarget.prototype.removeElement =
function(element){
	var listener = MouseListener.findListener(element, MouseListener.MOUSE_OVER);
	if(listener != null)
		listener.kill();
		
	listener = MouseListener.findListener(element, MouseListener.MOUSE_MOVE);
	if(listener != null)
		listener.kill();
}
DropTarget.prototype.addElementById =
function(id){
	this.addElement(document.getElementById(id));
}
DropTarget.prototype.removeElementById =
function(id){
	this.removeElement(document.getElementById(id));
}

//-----------------handeling mouse events-----------------------	
//the mouse hovers over one of our droptarget-elements
DropTarget.prototype.hover =
function(element, mouseEvent){
	var activeHover = DragElement.hovering;
	if(activeHover == null)									//nothing is dragged
		return;
	if(activeHover.getDropTarget() == this && element == this.getElement())		//this funtion has already been called
		return;
	if(activeHover.getDragSource().getElement() == element)	//an element can not be dropped on itself(but can be drag source and droptarget)
		return;
		
	this.setElement(element);
	
	if(this.dropPermission != null){						//if there is a permission function given
		var dragsourceElement = activeHover.getDragSource().getElement();
		var data = activeHover.getDragSource().getData();
		var validDrop = this.dropPermission.call(this, this.element, dragsourceElement, data);	//request for permission
	}else
		var validDrop = true;								//else assume we have permission to receive this dragElement
		
	if(validDrop)
		activeHover.setDropTarget(this);					//tell the hovering dragelement we are a valid droptarget
}
DropTarget.prototype.cancel =
function(element, mouseEvent){
	var activeHover = DragElement.hovering;
	if(activeHover != null)
		activeHover.setDropTarget(null);					//we are n longer a valid drop target
}
	
//-----------------handeling drop events(mousebutton is released holding a dragElement)-----------------------
DropTarget.prototype.doDrop =
function(dragsource){	
	if(this.dropFunction != null)
		this.dropFunction.call(this, this.getElement(), dragsource.getElement(), dragsource.getData());
	dragsource.drop();
}

//-----------------public methods for other objects to use-----------------------
DropTarget.prototype.getElement =
function(){
	return this.element;
}
DropTarget.prototype.setElement =
function(element){
	this.element = element;
}