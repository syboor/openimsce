// Add linenumbers to an existing textarea
// textarea should have an ID
//  Put this code direct after the <body> tag
//
// Put after the </body> tag within javascript:
//
// inittextarea(id, resize, maxlines);
//
// where:
// id = the tag ID of the textarea
// resize : when true: resize to full screen after window resize
// maxlines : do not use this line numbers script when more than [maxlines] lines long
   var lineObjOffsetTop = 4;
   var g_textarea = false;

  function getStyle(x,styleProp)
  {
    if (x.currentStyle)
      var y = x.currentStyle[styleProp];
    else if (window.getComputedStyle)
      var y = document.defaultView.getComputedStyle(x,null).getPropertyValue(styleProp);
    return y;
  }


   function createTextAreaWithLines(ta, linecount, style)
   {
      var el = document.createElement('DIV');
      ta.parentNode.insertBefore(el,ta);
      el.appendChild(ta);

//      el.className='textAreaWithLines';
      el.id='textAreaWithLines';
      el.style.width = (ta.offsetWidth + 50) + 'px';
      ta.style.position = 'absolute';
      ta.style.left = '50px';
//      el.style.height = (ta.offsetHeight + 1) + 'px';
      el.style.height = '100%';
      el.style.overflow='hidden';
      el.style.position = 'relative';
//      el.style.width = (ta.offsetWidth + 50) + 'px';
      el.style.width = '100%';
      var lineObj = document.createElement('DIV');
//      var lineObj = document.createElement('PRE');
      lineObj.style.position = 'absolute';
      lineObj.style.top = lineObjOffsetTop + 'px';
      lineObj.style.left = '0px';


//!! alleen bij pre
//      lineObj.style.marginTop = '0px';
//
      lineObj.style.width = '47px';
      el.insertBefore(lineObj,ta);
      lineObj.style.textAlign = 'right';
//      lineObj.className='lineObj';
      lineObj.id='lineObj';
/*
      if (arguments.length > 2) {
        var i;
        for (i in style ) {
          lineObj.style[i] = style[i];
          ta.style[i] = style[i];
        }
      }
*/
      var string = '';
      for(var no=1;no<linecount+1;no++){
//         if(string.length>0)string = string + '\r\n';
         if(string.length>0)string = string + '<br />';
         string = string + no;
      }

      ta.onkeydown = function() { positionLineObj(lineObj,ta); };
      ta.onmousedown = function() { positionLineObj(lineObj,ta); };
      ta.onscroll = function() { positionLineObj(lineObj,ta); };
//      ta.onmousewheel = function() { alert(ta); positionLineObj(lineObj,ta); };
      ta.onblur = function() { positionLineObj(lineObj,ta); };
      ta.onfocus = function() { positionLineObj(lineObj,ta); };
      ta.onmouseover = function() { positionLineObj(lineObj,ta); };
      lineObj.innerHTML = string;

   }

   function positionLineObj(obj,ta)
   {
//p_r(ta.scrollTop);
      obj.style.top = (ta.scrollTop * -1 + lineObjOffsetTop) + 'px';
   }


  function getcookievalue(name)
  {
      var ind;
      name += '=';
      str = document.cookie;
      if ((ind = str.indexOf(name)) != -1)
      {
          var end = str.indexOf(';', ind);
          if (end == -1) end = str.length;
          str = str.substr(ind+name.length, end-ind-name.length);
          str = unescape(str);
          return str;
      }
      return '';
  }

  function setcookievalue(name, value)
  {
      name += '=';
      var expDays = 100;
      var exp = new Date();
      exp.setTime(exp.getTime() + (expDays*24*60*60*1000));

      var expire = '; expires=' + exp.toGMTString();
      var str = name + escape(value) + expire;
      document.cookie = str;
  }


// http://stackoverflow.com/questions/263743/how-to-get-cursor-position-in-textarea
/*
function getCaretPosition(elemId) {
  var el = document.getElementById(elemId);
  if (el.selectionStart) {
    return el.selectionStart;
  } else if (document.selection) {
    el.focus();

    var r = document.selection.createRange();
    if (r == null) {
      return 0;
    }

    var re = el.createTextRange(),
        rc = re.duplicate();
    re.moveToBookmark(r.getBookmark());
    rc.setEndPoint('EndToStart', re);

    return rc.text.length;
  }
  return 0;
}

//
// http://blog.josh420.com/archives/2007/10/setting-cursor-position-in-a-textbox-or-textarea-with-javascript.aspx

   function setCaretPosition(elemId, caretPos)
   {
      var elem = document.getElementById(elemId);

      if(elem != null) {
        if(elem.createTextRange) {
          var range = elem.createTextRange();
          range.move('character', caretPos);
          range.select();
        } else {
          if(elem.selectionStart) {
            elem.focus();
            elem.setSelectionRange(caretPos, caretPos);
          }
          else
            elem.focus();
        }
     }
  }
*/
///
//alert(document.getElementById('textAreaWithLines').style.fontFamily);

// http://blog.josh420.com/archives/2007/10/setting-cursor-position-in-a-textbox-or-textarea-with-javascript.aspx

function addLoadEvent(func)
{
    if(typeof window.onload != 'function') {
        window.onload = func;
    }
    else {
        if(func) {
            var oldLoad = window.onload;
            window.onload = function() {
                if(oldLoad)
                        oldLoad();

                func();
            }
        }
    }
}


function getwidth()
{
  var x = 0;
  if (self.innerHeight)
  {
    x = self.innerWidth;
  }
  else if (document.documentElement && document.documentElement.clientHeight)
  {
    x = document.documentElement.clientWidth;
  }
  else if (document.body)
  {
    x = document.body.clientWidth;
  }
  return x;
}

function getheight()
{
  var y = 0;
  if (self.innerHeight)
  {
    y = self.innerHeight;
  }
  else if (document.documentElement && document.documentElement.clientHeight)
  {
    y = document.documentElement.clientHeight;
  }
  else if (document.body)
  {
    y = document.body.clientHeight;
  }
  return y;
}

// The setCaretPosition function belongs right here!

var g_resize=false;

function settextsize()
{
    var textAreas = g_textarea;
    var textblock = document.getElementById('textAreaWithLines');
    var width = getwidth();
    textAreas.style.width = "" + (width - 65) + "px";
    textblock.style.width = "" + (width) + "px";

}


function setTextAreasOnFocus() {
/***
 * This function will force the cursor to be positioned
 * at the end of all textareas when they receive focus.
 */
    var textAreas = g_textarea;
/*

    for(var i = 0; i < textAreas.length; i++) {
        textAreas[i].onfocus = function() {
            setCaretPosition(this.id, this.value.length);
        }
    }
*/


    if (g_resize)
      settextsize();
//alert(getStyle(g_textarea,"font-size") + " " + g_textarea.style.fontSize);

    textAreas = null;
}

function inittextarea(id, resize, maxlines)
{
  if (arguments.length < 2)
    resize = false;
//alert(111);

  g_resize = resize;
  if (typeof(id)!='object')
      id = document.getElementById(id);

  var linecount = (id.value.split('\n').length);

  if (arguments.length < 3 || linecount <= maxlines) {
    g_textarea = id;
    createTextAreaWithLines(id, linecount);

    addLoadEvent(setTextAreasOnFocus);

    if (resize)
      window.onresize = settextsize;
    window.onfocus = function() { id.focus();}
  }
}

