function DragSource(drag,drop,dragElement,cursors,cssClass,sign,floatback,rightclick,elementPos){this.dragFunction=drag;this.dropFunction=drop;this.dragElementSource=dragElement;this.rightclick=rightclick;this.cursors=cursors;this.cssClass=cssClass;this.elementPos=elementPos;this.floatback=floatback;this.sign=sign;if(this.sign!=null){if(this.sign[0]!=null&&this.sign[0]!=""){var img1=new Image;img1.src=this.sign[0];}else
var img1=null;if(this.sign[1]!=null&&this.sign[1]!=""){var img2=new Image;img2.src=this.sign[1];}else
var img2=null;this.sign=new Array(img1,img2);}
this.elementSpecificSettings=new Array();this.element=null;}
DragSource.prototype.toString=function(){return"DragSource";}
DragSource.prototype.addElement=function(element,inhoud_drop,inhoud_nodrop){if(this.rightclick)
new MouseListener(element,MouseListener.MOUSE_RIGHT_DOWN,this,this.activate);else
new MouseListener(element,MouseListener.MOUSE_DOWN,this,this.activate);this.elementSpecificSettings[this.elementSpecificSettings.length]=new Array(element,inhoud_drop,inhoud_nodrop);}
DragSource.prototype.removeElement=function(element){listener=MouseListener.findListener(element,MouseListener.MOUSE_DOWN);if(listener!=null)
listener.kill();}
DragSource.prototype.addElementById=function(id,inhoud_drop,inhoud_nodrop){var element=document.getElementById(id);this.addElement(element,inhoud_drop,inhoud_nodrop);}
DragSource.prototype.removeElementById=function(id){this.removeElement(document.getElementById(id));}
DragSource.prototype.activate=function(element,mouseEvent){var de=new DragElement(this,this.dragElementSource,this.dragStart,this.dragEnd);de.setCursors(this.cursors);de.setCssClass(this.cssClass);de.setElementPos(this.elementPos);de.setFloatBack(this.floatback);de.setSign(this.sign);for(var i=0;i<this.elementSpecificSettings.length;i++){if(this.elementSpecificSettings[i][0]==element){de.setContentDrop(this.elementSpecificSettings[i][1]);de.setContentNoDrop(this.elementSpecificSettings[i][2]);break;}}
this.setElement(element);}
DragSource.prototype.dragStart=function(){if(this.dragFunction!=null)
this.dragFunction.call(this,this.getElement());}
DragSource.prototype.dragEnd=function(droptarget){if(droptarget!=null){droptarget.doDrop(this);if(this.dropFunction!=null){this.dropFunction.call(this,droptarget.getElement(),this.getElement(),this.getData());}}}
DragSource.prototype.drop=function(){}
DragSource.prototype.getElement=function(){return this.element;}
DragSource.prototype.setElement=function(element){this.element=element;}
DragSource.prototype.getData=function(){return this.drag_data;}
DragSource.prototype.setData=function(data){this.drag_data=data;}
function DropTarget(requestDrop,drop){this.dropPermission=requestDrop;this.dropFunction=drop;this.element=null;}
DropTarget.prototype.toString=function(){return"DropTarget";}
DropTarget.prototype.addElement=function(element){new MouseListener(element,MouseListener.MOUSE_OVER,this,this.hover);new MouseListener(element,MouseListener.MOUSE_MOVE,this,this.hover);new MouseListener(element,MouseListener.MOUSE_OUT,this,this.cancel);}
DropTarget.prototype.removeElement=function(element){var listener=MouseListener.findListener(element,MouseListener.MOUSE_OVER);if(listener!=null)
listener.kill();listener=MouseListener.findListener(element,MouseListener.MOUSE_MOVE);if(listener!=null)
listener.kill();}
DropTarget.prototype.addElementById=function(id){this.addElement(document.getElementById(id));}
DropTarget.prototype.removeElementById=function(id){this.removeElement(document.getElementById(id));}
DropTarget.prototype.hover=function(element,mouseEvent){var activeHover=DragElement.hovering;if(activeHover==null)
return;if(activeHover.getDropTarget()==this&&element==this.getElement())
return;if(activeHover.getDragSource().getElement()==element)
return;this.setElement(element);if(this.dropPermission!=null){var dragsourceElement=activeHover.getDragSource().getElement();var data=activeHover.getDragSource().getData();var validDrop=this.dropPermission.call(this,this.element,dragsourceElement,data);}else
var validDrop=true;if(validDrop)
activeHover.setDropTarget(this);}
DropTarget.prototype.cancel=function(element,mouseEvent){var activeHover=DragElement.hovering;if(activeHover!=null)
activeHover.setDropTarget(null);}
DropTarget.prototype.doDrop=function(dragsource){if(this.dropFunction!=null)
this.dropFunction.call(this,this.getElement(),dragsource.getElement(),dragsource.getData());dragsource.drop();}
DropTarget.prototype.getElement=function(){return this.element;}
DropTarget.prototype.setElement=function(element){this.element=element;}
function DragElement(dragsource,getElement,dragStart,dragEnd){if(DragElement.hovering!=null)
DragElement.hovering.stopDrag();this.getElementFunction=getElement;this.dragStartFunction=dragStart;this.dragEndFunction=dragEnd;this.dragsource=dragsource;this.droptarget=null;this.element=null;this.cssClass=null;this.elementPos=[20,5];this.cursorManager=null;this.floatback=true;this.sign=null;this.content_drop=null;this.content_nodrop=null;this.trail=null;this.trailimg=null;this.dragListener=new MouseListener(document,MouseListener.MOUSE_MOVE,this,this.drag);this.cancelListener=new MouseListener(document,MouseListener.MOUSE_UP,this,this.stopDrag);DragElement.hovering=this;}
DragElement.hovering=null;DragElement.prototype.toString=function(){if(this==DragElement.hovering)
return"DragElement; currently hovering";else
return"DragElement; fugitive";}
DragElement.prototype.setCursors=function(cursors){if(cursors!=null)
this.cursorManager=new CursorManager(cursors);else
this.cursorManager=null;}
DragElement.prototype.setCssClass=function(cssClass){this.cssClass=cssClass;}
DragElement.prototype.setElementPos=function(a){if(a!=null)
this.elementPos=a;}
DragElement.prototype.setFloatBack=function(b){if(b!=null)
this.floatback=b;}
DragElement.prototype.setContentDrop=function(content){this.content_drop=content;}
DragElement.prototype.setContentNoDrop=function(content){this.content_nodrop=content;}
DragElement.prototype.setSign=function(sign){if(sign!=null){this.setCursors(new Array("default",null));}
this.sign=sign;}
DragElement.prototype.getDropTarget=function(){return this.droptarget;}
DragElement.prototype.setDropTarget=function(droptarget){if(this.droptarget!=null&&droptarget!=this.droptarget&&this.backuptitle){this.droptarget.getElement().title=this.backuptitle;}
if(droptarget!=null){if(""+droptarget.getElement().title==""){this.backuptitle=droptarget.getElement().parentNode.title;}else{this.backuptitle=droptarget.getElement().title;}
droptarget.getElement().title="";}
this.droptarget=droptarget;if(this.element){if(this.cssClass!=null)
this.element.className=this.droptarget?this.cssClass[0]:this.cssClass[1];if(this.cursorManager!=null){this.cursorManager.resetCursor(0);if(this.droptarget!=null){this.cursorManager.setElementCursor(droptarget.getElement(),0);this.cursorManager.setElementCursor(this.dragsource.getElement(),0);}}
if(this.sign!=null){if(this.trailimg!=null)
this.trail.removeChild(this.trailimg);this.trailimg=this.droptarget?this.sign[0]:this.sign[1];if(this.trailimg!=null)
this.trail.appendChild(this.trailimg);}
if(this.droptarget!=null&&this.content_drop!=null)
this.element.innerHTML=this.content_drop;else if(this.droptarget==null&&this.content_nodrop!=null)
this.element.innerHTML=this.content_nodrop;}}
DragElement.prototype.getDragSource=function(){return this.dragsource;}
DragElement.prototype.getElement=function(){return this.element;}
DragElement.prototype.setElement=function(element){this.element=element;}
DragElement.prototype.drag=function(element,mouseEvent){var coords=this.dragListener.getCoordinates();if(this.element==null){if(this.startPos==null){this.startPos=coords;return;}else if(Math.abs(this.startPos[0]-coords[0])>5||Math.abs(this.startPos[1]-coords[1])>5){if(this.dragStartFunction!=null)
this.dragStartFunction.call(this.getDragSource());this.setElement(this.createDragElement());}else
return;}
if(this.sign!=null&&this.trailimg!=null){var trailpos=new Array(coords[0]+3,coords[1]+20);trailpos=Geometry.keepElementInScreen(trailpos,this.trail);this.trail.style.left=trailpos[0]+"px";this.trail.style.top=trailpos[1]+"px";}
Geometry.handleScroll(coords);coords[0]+=this.elementPos[0];coords[1]+=this.elementPos[1];coords=Geometry.keepElementInScreen(coords,this.getElement());this.getElement().style.left=coords[0]+"px";this.getElement().style.top=coords[1]+"px";}
DragElement.prototype.stopDrag=function(element,mouseEvent){this.dragListener.kill();this.cancelListener.kill();if(this.cursorManager!=null)
this.cursorManager.resetAll();if(this.getElement()!=null){if(this.sign!=null)
window.document.body.removeChild(this.trail);if(this.getDropTarget()==null&&this.floatback){DragElement.startElement=this.getElement();DragElement.endElement=this.getDragSource().getElement();DragElement.jumpAnimation();this.setElement(null);}else{window.document.body.removeChild(this.getElement());this.setElement(null);}
if(this.getDragSource().rightclick)
MouseListener.setMenu(false,10);}
this.dragEndFunction.call(this.getDragSource(),this.getDropTarget());DragElement.hovering=null;this.setDropTarget(null);}
DragElement.prototype.createDragElement=function(){var element=document.createElement("div");element.style.position="absolute";element.style.top="1px";element.style.left="1px";if(this.cssClass!=null)
element.className=this.cssClass[1];if(this.content_nodrop!=null){element.innerHTML=this.content_nodrop;}else if(this.getElementFunction!=null){element.appendChild(this.getElementFunction.call(this,this.getDragSource().getElement(),this.getDragSource().getData()));}else{var contents=this.getDragSource().getElement().cloneNode(true);contents.id+="dragged";if(contents.childNodes!=null){for(var i=0;i<contents.childNodes.length;i++){if(contents.childNodes[i]!=null&&contents.childNodes[i].id!=null){contents.childNodes[i].id+="dragged";}}}
element.appendChild(contents);}
window.document.body.appendChild(element);if(this.cursorManager!=null){this.cursorManager.setElementCursor(document.body,1);this.cursorManager.setElementCursor(this.dragsource.getElement(),1);}
if(this.sign!=null){this.trail=document.createElement("div");this.trail.style.position="absolute";this.trail.style.top="1px";this.trail.style.left="1px";this.trailimg=this.sign[1];if(this.trailimg!=null)
this.trail.appendChild(this.trailimg);window.document.body.appendChild(this.trail);}
return element;}
DragElement.animspeed=50;DragElement.startElement=null;DragElement.endElement=null
DragElement.jumpAnimation=function(xd,yd){var startpos=Geometry.getPosition(DragElement.startElement,null);var endpos=Geometry.getPosition(DragElement.endElement,null);var xlen=endpos[0]-startpos[0];var ylen=endpos[1]-startpos[1];var distance=Math.sqrt(xlen*xlen+ylen*ylen);if(distance<DragElement.animspeed){window.document.body.removeChild(DragElement.startElement);DragElement.startElement=null;return;}
var firsttime=false;if(xd==null||yd==null){var steps=distance/DragElement.animspeed;xd=Math.round(xlen/steps);yd=Math.round(ylen/steps);}
DragElement.startElement.style.left=startpos[0]+xd+"px";DragElement.startElement.style.top=startpos[1]+yd+"px";window.setTimeout("DragElement.jumpAnimation("+xd+","+yd+");",10);}
function MouseListener(element,event_types,object,f,belongs_to){if(element==null||event_types==0)
return;this.element=element;this.n_element=belongs_to;this.object=object;this.f=f;this.backup=new Array();this.mouseEvent=null;this.event_types=event_types;MouseListener.addListener(this);var x=event_types;var event_n=MouseListener.ALL+1;while(x>=1){event_n/=2;if(x>=event_n){x-=event_n;if(!(element==document&&MouseListener.registerListener(event_n))){try{switch(event_n){case MouseListener.MOUSE_DOWN:case MouseListener.MOUSE_RIGHT_DOWN:this.backup[event_n]=element.onmousedown;element.onmousedown=MouseListener.EVENT_FUNCTIONS[event_n];break;case MouseListener.MOUSE_MOVE:this.backup[event_n]=element.onmousemove;element.onmousemove=MouseListener.EVENT_FUNCTIONS[event_n];break;case MouseListener.MOUSE_UP:this.backup[event_n]=element.onmouseup;element.onmouseup=MouseListener.EVENT_FUNCTIONS[event_n];break;case MouseListener.MOUSE_OVER:this.backup[event_n]=element.onmouseover;element.onmouseover=MouseListener.EVENT_FUNCTIONS[event_n];break;case MouseListener.MOUSE_OUT:this.backup[event_n]=element.onmouseout;element.onmouseout=MouseListener.EVENT_FUNCTIONS[event_n];break;}}catch(e){}}}}
this.subListeners=new Array();if(element==document)
return;if(element.childNodes!=null){for(var i=0;i<element.childNodes.length;i++){if(element.childNodes[i]!=null){if(this.n_element!=null)
this.subListeners[this.subListeners.length]=new MouseListener(element.childNodes[i],this.event_types,object,f,this.n_element);else
this.subListeners[this.subListeners.length]=new MouseListener(element.childNodes[i],this.event_types,object,f,element);}}}}
MouseListener.prototype.toString=function(){if(this.element.id)
return"MouseListener listening to "+this.element.id;else
return"MouseListener";}
MouseListener.MOUSE_DOWN=1;MouseListener.MOUSE_RIGHT_DOWN=2;MouseListener.MOUSE_MOVE=4;MouseListener.MOUSE_UP=8;MouseListener.MOUSE_OVER=16;MouseListener.MOUSE_OUT=32;MouseListener.ALL=63;MouseListener.EVENT_STRINGS=new Array();MouseListener.EVENT_STRINGS[MouseListener.MOUSE_DOWN]="mousedown";MouseListener.EVENT_STRINGS[MouseListener.MOUSE_RIGHT_DOWN]="mousedown";MouseListener.EVENT_STRINGS[MouseListener.MOUSE_MOVE]="mousemove";MouseListener.EVENT_STRINGS[MouseListener.MOUSE_UP]="mouseup";MouseListener.EVENT_STRINGS[MouseListener.MOUSE_OVER]="mouseover";MouseListener.EVENT_STRINGS[MouseListener.MOUSE_OUT]="mouseout";MouseListener.prototype.getCoordinates=function(){if(this.mouseEvent!=null)
return Geometry.getCoordinatesFromEvent(this.mouseEvent);}
MouseListener.prototype.notify=function(mouseEvent,event_type){if(this.backup[event_type]!=null&&this.backup[event_type]!=MouseListener.EVENT_FUNCTIONS[event_type])
this.backup[event_type].call(this.element,mouseEvent);this.mouseEvent=mouseEvent;var element=(this.n_element!=null)?this.n_element:this.element;if(this.object!=null)
this.f.call(this.object,element,mouseEvent);else
this.f.call(element,mouseEvent);}
MouseListener.prototype.kill=function(){var x=this.event_types;var event_n=MouseListener.ALL+1;while(x>=1){event_n/=2;if(x>=event_n){x-=event_n;if(!(this.element==document&&MouseListener.unregisterListener(event_n))){try{switch(event_n){case MouseListener.MOUSE_DOWN:case MouseListener.MOUSE_RIGHT_DOWN:this.element.onmousedown=this.backup[event_n];break;case MouseListener.MOUSE_MOVE:this.element.onmousemove=this.backup[event_n];break;case MouseListener.MOUSE_UP:this.element.onmouseup=this.backup[event_n];break;case MouseListener.MOUSE_OVER:this.element.onmouseover=this.backup[event_n];break;case MouseListener.MOUSE_OUT:this.element.onmouseout=this.backup[event_n];break;}}catch(e){}}}}
MouseListener.removeListener(this);for(var i=0;i<this.subListeners.length;i++){if(this.subListeners[i]!=null)
this.subListeners[i].kill();}}
MouseListener.prototype.listensTo=function(event_type){var x=this.event_types;var dec=MouseListener.ALL+1;while(x>=1){dec/=2;if(x>=dec){x-=dec;if(dec==event_type)
return true;}}
return false;}
MouseListener.listenerList=new Array();MouseListener.addListener=function(listener){MouseListener.listenerList[MouseListener.lCount()]=listener;}
MouseListener.lCount=function(){return MouseListener.listenerList.length;}
MouseListener.getListener=function(x){if(x>=0&&x<MouseListener.lCount())
return MouseListener.listenerList[x];return null;}
MouseListener.findListener=function(element,event_type){for(var i=0;i<MouseListener.lCount();i++){var listener=MouseListener.getListener(i);if(listener!=null&&listener.element==element){if(listener.listensTo(event_type))
return listener;}}}
MouseListener.removeListener=function(listener){for(var i=0;i<MouseListener.lCount();i++){if(MouseListener.getListener(i)==listener)
MouseListener.listenerList.splice(i,1);}}
MouseListener.registerListener=function(event_type){if(document!=null&&document.addEventListener!=null){document.addEventListener(MouseListener.EVENT_STRINGS[event_type],MouseListener.EVENT_FUNCTIONS[event_type],true);if(document.setCapture)
document.setCapture();return true;}else if(document.body!=null&&document.body.addEventListener!=null){document.body.addEventListener(MouseListener.EVENT_STRINGS[event_type],MouseListener.EVENT_FUNCTIONS[event_type],true);if(document.body.setCapture)
document.body.setCapture();return true;}
return false;}
MouseListener.unregisterListener=function(event_type){if(document!=null&&document.addEventListener!=null){document.removeEventListener(MouseListener.EVENT_STRINGS[event_type],MouseListener.EVENT_FUNCTIONS[event_type],true);if(document.releaseCapture)
document.releaseCapture();return true;}else if(document.body!=null&&document.body.addEventListener!=null){document.body.removeEventListener(MouseListener.EVENT_STRINGS[event_type],MouseListener.EVENT_FUNCTIONS[event_type],true);if(document.body.releaseCapture)
document.body.releaseCapture();return true;}
return false;}
MouseListener.mouseEvent=function(mouseEvent,event_type){mouseEvent=(mouseEvent)?mouseEvent:((window.event)?window.event:null);var target=MouseListener.getTarget(mouseEvent);var listener=MouseListener.findListener(target,event_type);if(listener!=null)
listener.notify(mouseEvent,event_type);listener=MouseListener.findListener(document,event_type);if(listener!=null)
listener.notify(mouseEvent,event_type);}
MouseListener.mouseOut=function(mouseEvent){MouseListener.mouseEvent(mouseEvent,MouseListener.MOUSE_OUT);}
MouseListener.mouseOver=function(mouseEvent){MouseListener.mouseEvent(mouseEvent,MouseListener.MOUSE_OVER);}
MouseListener.mouseUp=function(mouseEvent){MouseListener.mouseEvent(mouseEvent,MouseListener.MOUSE_UP);}
MouseListener.mouseMove=function(mouseEvent){MouseListener.mouseEvent(mouseEvent,MouseListener.MOUSE_MOVE);return false;}
MouseListener.mouseDown=function(mouseEvent){mouseEvent=(mouseEvent)?mouseEvent:((window.event)?window.event:null);if(MouseListener.rightButton(mouseEvent))
MouseListener.mouseEvent(mouseEvent,MouseListener.MOUSE_RIGHT_DOWN);else
MouseListener.mouseEvent(mouseEvent,MouseListener.MOUSE_DOWN);return false;}
MouseListener.EVENT_FUNCTIONS=new Array();MouseListener.EVENT_FUNCTIONS[MouseListener.MOUSE_DOWN]=MouseListener.mouseDown;MouseListener.EVENT_FUNCTIONS[MouseListener.MOUSE_RIGHT_DOWN]=MouseListener.mouseDown;MouseListener.EVENT_FUNCTIONS[MouseListener.MOUSE_MOVE]=MouseListener.mouseMove;MouseListener.EVENT_FUNCTIONS[MouseListener.MOUSE_UP]=MouseListener.mouseUp;MouseListener.EVENT_FUNCTIONS[MouseListener.MOUSE_OVER]=MouseListener.mouseOver;MouseListener.EVENT_FUNCTIONS[MouseListener.MOUSE_OUT]=MouseListener.mouseOut;MouseListener.getTarget=function(ev){if(ev.target){if(ev.target.nodeType==3){return ev.target.parentNode;}else{return ev.target;}}else if(ev.srcElement){return ev.srcElement;}else{return null;}}
MouseListener.rightButton=function(ev){if(ev==null)
return false;if(ev.which)
return(ev.which==3);else if(ev.button)
return(ev.button==2);}
MouseListener.setMenu=function(on,duration){if(on){document.oncontextmenu=null;}else{document.oncontextmenu=function(){return false;}}
if(duration!=null)
window.setTimeout("MouseListener.setMenu(true)",duration);}
function Geometry(){}
Geometry.getCoordinatesFromEvent=function(ev){if(ev.pageX!=null){var docX=ev.pageX;var docY=ev.pageY;}else if(ev.clientX!=null){var docX=ev.clientX+document.body.scrollLeft-document.body.clientLeft;var docY=ev.clientY+document.body.scrollTop-document.body.clientTop;if(document.body.parentElement){var bodParent=document.body.parentElement;docX+=bodParent.scrollLeft-bodParent.clientLeft;docY+=bodParent.scrollTop-bodParent.clientTop;}}
return[docX,docY];}
Geometry.keepElementInScreen=function(coords,element){var scroll=Geometry.getScroll();if(coords[0]<scroll[0])
coords[0]=scroll[0];if(coords[1]<scroll[1])
coords[1]=scroll[1];var windowsize=Geometry.getWindowSize();var elementsize=Geometry.getSize(element);if(coords[0]>(scroll[0]+windowsize[0])-elementsize[0])
coords[0]=(scroll[0]+windowsize[0])-elementsize[0];if(coords[1]>(scroll[1]+windowsize[1])-elementsize[1])
coords[1]=(scroll[1]+windowsize[1])-elementsize[1];return coords;}
Geometry.handleScroll=function(coords){var scroll=Geometry.getScroll();if(coords[1]<scroll[1]+20){if(Geometry.stopScrolling==true){Geometry.stopScrolling=false;Geometry.scrollPage(-2);}
return;}
var windowsize=Geometry.getWindowSize();if(coords[1]>scroll[1]+windowsize[1]-20){if(Geometry.stopScrolling==true){Geometry.stopScrolling=false;Geometry.scrollPage(2);}
return;}
Geometry.stopScrolling=true;}
Geometry.stopScrolling=true;Geometry.scrollPage=function(y){window.scrollBy(0,y);y+=(y>0)?.3:-.3;var scroll=Geometry.getScroll();var wsize=Geometry.getWindowSize();var psize=Geometry.getPageSize();if(scroll[1]==0)
Geometry.stopScrolling=true;else if(scroll[1]+wsize[1]==psize[1])
Geometry.stopScrolling=true;if(!Geometry.stopScrolling)
window.setTimeout("Geometry.scrollPage("+y+");",5);}
Geometry.getWindowSize=function(){var p=[0,0];if(window.innerWidth){p[0]=window.innerWidth;p[1]=window.innerHeight;}else if(window.document.body.parentElement.clientWidth){p[0]=window.document.body.parentElement.clientWidth;p[1]=window.document.body.parentElement.clientHeight;}else if(window.document.body&&window.document.body.clientWidth){p[0]=window.document.body.clientWidth;p[1]=window.document.body.clientHeight;}
return p;}
Geometry.getPageSize=function(){var p=[0,0];var test1=document.body.scrollHeight;var test2=document.body.offsetHeight
if(test1>test2){p[0]=document.body.scrollWidth;p[1]=document.body.scrollHeight;}else{p[0]=document.body.offsetWidth;p[1]=document.body.offsetHeight;}
return p;}
Geometry.getSize=function(htmlElement){var p=[0,0];if(htmlElement.offsetWidth!=null){p[0]=htmlElement.offsetWidth;p[1]=htmlElement.offsetHeight;}else if(htmlElement.clip&&htmlElement.clip.width!=null){p[0]=htmlElement.clip.width;p[1]=htmlElement.clip.height;}else if(htmlElement.style&&htmlElement.style.pixelWidth!=null){p[0]=htmlElement.style.pixelWidth;p[1]=htmlElement.style.pixelHeight;}
p[0]=parseInt(p[0]);p[1]=parseInt(p[1]);return p;}
Geometry.getPosition=function(htmlElement,containerElement){var p=[0,0];var offsetParent=htmlElement;while(offsetParent!=containerElement){p[0]+=offsetParent.offsetLeft;p[1]+=offsetParent.offsetTop;offsetParent=offsetParent.offsetParent;}
return p;}
Geometry.getScroll=function(){var scroll=new Array();if(self.pageYOffset){scroll[0]=self.pageXOffset;scroll[1]=self.pageYOffset;}else if(document.documentElement&&document.documentElement.scrollTop){scroll[0]=document.documentElement.scrollLeft;scroll[1]=document.documentElement.scrollTop;}else if(document.body){scroll[0]=document.body.scrollLeft;scroll[1]=document.body.scrollTop;}
return scroll;}
function CursorManager(cursors){this.cursors=cursors;this.oldcursorList=new Array();this.elementList=new Array();}
CursorManager.prototype.setElementCursor=function(element,n){this.oldcursorList[this.oldcursorList.length]=element.style.cursor;this.elementList[this.elementList.length]=element;this.setCursor(element,this.cursors[n]);}
CursorManager.prototype.resetElementCursor=function(element){for(var i=0;i<this.elementList.length;i++){if(this.elementList[i]==element){element.style.cursor=this.oldcursorList[i];this.elementList.splice(i,1);this.oldcursorList.splice(i,1);i-=1;}}}
CursorManager.prototype.resetAll=function(){while(this.elementList.length>0){var l=this.elementList.length-1;this.elementList[l].style.cursor=this.oldcursorList[l];this.elementList.splice(l,1);this.oldcursorList.splice(l,1);}}
CursorManager.prototype.resetCursor=function(n){for(var i=this.elementList.length-1;i>=0;i--){if(this.equals(this.elementList[i].style.cursor,this.cursors[n])){this.elementList[i].style.cursor=this.oldcursorList[i];this.elementList.splice(i,1);this.oldcursorList.splice(i,1);i-=1;}}}
CursorManager.prototype.setCursor=function(element,cursors){if(cursors instanceof Array){for(var i=0;i<cursors.length;i++){}}else{}}
CursorManager.prototype.equals=function(cursor,cursors){if(cursors instanceof Array){for(var i=0;i<cursors.length;i++){if(this.equals(cursor,cursors[i]))
return true;}
return false;}else
return(cursor==cursors||cursor=="url('"+cursors+"')");}