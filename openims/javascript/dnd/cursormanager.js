function CursorManager(cursors) {
	this.cursors = cursors;
	this.oldcursorList = new Array();
	this.elementList = new Array();
}
CursorManager.prototype.setElementCursor = 
function(element, n){
	this.oldcursorList[this.oldcursorList.length] = element.style.cursor;
		
	this.elementList[this.elementList.length] = element;
	this.setCursor(element, this.cursors[n]);
}
CursorManager.prototype.resetElementCursor =
function(element){
	
	for(var i=0; i < this.elementList.length; i++){
		if(this.elementList[i] == element){
			element.style.cursor = this.oldcursorList[i];
			this.elementList.splice(i, 1);
			this.oldcursorList.splice(i, 1);
			i -= 1;
		}
	}
	
}
CursorManager.prototype.resetAll =
function(){
	while(this.elementList.length > 0){
		var l = this.elementList.length-1;
		this.elementList[l].style.cursor = this.oldcursorList[l];
		this.elementList.splice(l, 1);
		this.oldcursorList.splice(l, 1);
	}
}
CursorManager.prototype.resetCursor =
function(n){
	for(var i=this.elementList.length-1; i >=0 ; i--){
		if(this.equals(this.elementList[i].style.cursor, this.cursors[n])){
			this.elementList[i].style.cursor = this.oldcursorList[i];
			this.elementList.splice(i, 1);
			this.oldcursorList.splice(i, 1);
			i -= 1;
		}
	}
}

CursorManager.prototype.setCursor =
function(element, cursors){
	if(cursors instanceof Array){
		for(var i=0; i < cursors.length; i++){
//qqq			element.style.cursor = cursors[i];
		}
	} else {
//qqq		element.style.cursor = cursors;
        }
}
CursorManager.prototype.equals =
function(cursor, cursors){
	if(cursors instanceof Array){
		for(var i=0; i < cursors.length; i++){
			if(this.equals(cursor, cursors[i]))
				return true;
		}
		return false;
	}else
		return (cursor == cursors ||cursor ==  "url('" + cursors + "')");
}