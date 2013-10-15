//----------------------------------------------------------------------\\
//								DragSource								\\
//Purpose: Add an element to an instance of this class to make it 		\\
//	draggable. A mousedown event from that element will trigger this	\\
//	class to make a DragElement.										\\
//Options:																\\
//	Constructor: Make a new DragSource everytime you want to register	\\
//					different functions for draggables.					\\
//		drag: a function that is called right when the drag starts		\\
//		drop: called after a succesful drop on a droptarget.			\\
//		dragElement: a function that has to return and element. This	\\
//						element will be *visibly* dragged across the	\\
//						screen.											\\
//	AddElement/removeElement: Use this to add or remove draggable 		\\
//								elements.								\\
//	AddElementById/removeElementById: same functions but with ids		\\
//Needs: MouseListener													\\
//																		\\
//----------------------------------------------------------------------\\
function DragSource(drag, drop, dragElement, cursors, cssClass, sign, floatback, rightclick, elementPos) {
	//proper attribute declarations
	this.dragFunction = drag;
	this.dropFunction = drop;
	this.dragElementSource = dragElement;
	
	//Settings
	this.rightclick = rightclick;
	this.cursors = cursors;
	this.cssClass = cssClass;
	this.elementPos = elementPos;
	this.floatback = floatback;
	this.sign = sign;
	if(this.sign != null){
		if(this.sign[0] != null && this.sign[0] != ""){
			var img1 = new Image;
			img1.src = this.sign[0];
		}else
			var img1 = null;
		if(this.sign[1] != null && this.sign[1] != ""){
			var img2 = new Image;
			img2.src = this.sign[1];
		}else
			var img2 = null;
		this.sign = new Array(img1, img2);
	}
	
	this.elementSpecificSettings = new Array();
	
	//only defined when something is dragged
	this.element = null;		//the object which is being dragged
}

DragSource.prototype.toString = 
function() {
	return "DragSource";
}

//-----------------adding and removing elements-----------------------
DragSource.prototype.addElement =
function(element, inhoud_drop, inhoud_nodrop){
	//make a new mouselistener that activates the dragging process
	if(this.rightclick)
		new MouseListener(element, MouseListener.MOUSE_RIGHT_DOWN, this, this.activate);
	else
		new MouseListener(element, MouseListener.MOUSE_DOWN, this, this.activate);
	this.elementSpecificSettings[this.elementSpecificSettings.length] = new Array(element, inhoud_drop, inhoud_nodrop);
}
DragSource.prototype.removeElement =
function(element){
	listener = MouseListener.findListener(element, MouseListener.MOUSE_DOWN);
	if(listener != null)
		listener.kill();
}
DragSource.prototype.addElementById =
function(id, inhoud_drop, inhoud_nodrop){
	var element = document.getElementById(id);
	this.addElement(element, inhoud_drop, inhoud_nodrop);
}
DragSource.prototype.removeElementById =
function(id){
	this.removeElement(document.getElementById(id));
}

//-----------------handeling mouse events-----------------------
//called when a mousebutton is pressed over an element
DragSource.prototype.activate =
function(element, mouseEvent){
	var de = new DragElement(this, this.dragElementSource, this.dragStart, this.dragEnd);
	de.setCursors(this.cursors);
	de.setCssClass(this.cssClass);
	de.setElementPos(this.elementPos);
	de.setFloatBack(this.floatback);
	de.setSign(this.sign);
	
	for(var i = 0; i < this.elementSpecificSettings.length; i++){
		if(this.elementSpecificSettings[i][0] == element){
			de.setContentDrop(this.elementSpecificSettings[i][1]);
			de.setContentNoDrop(this.elementSpecificSettings[i][2]);
			break;
		}
	}
	
	this.setElement(element);
}

//-----------------handeling drag events-----------------------
//called when the element is dragged
DragSource.prototype.dragStart =
function(){	
	if(this.dragFunction != null)
		this.dragFunction.call(this, this.getElement());	//tell function we are going to drag this
}
//called when the drag has ended(mousebutton is released)
DragSource.prototype.dragEnd =
function(droptarget){
	if(droptarget != null) {
	     droptarget.doDrop(this);			//tell the droptarget we are dropping
             if(this.dropFunction != null) {
                 this.dropFunction.call(this, droptarget.getElement(), this.getElement(), this.getData());
             }
        }
}

//-----------------handeling succesful drop events-----------------------
//called by drop target after a succesfull drop
DragSource.prototype.drop =
function(){
}
//-----------------public methods for other objects to use-----------------------
DragSource.prototype.getElement =
function(){
	return this.element;
}
DragSource.prototype.setElement =
function(element){
	this.element = element;
}
//this.drag_data will be handed over to the droptarget
DragSource.prototype.getData =
function(){
	return this.drag_data;
}
DragSource.prototype.setData =
function(data){
	this.drag_data = data;
}