var d_started = 0;
var d_store = new Array();
d_store["queuesize"] = 0;
d_store["queuepointer"] = 0;
d_store["queue"] = new Array();
d_store["locks"] = new Array();
d_store["watchsize"] = 0;
d_store["watches"] = new Array();
d_store["servervalues"] = new Array();
d_store["clientvalues"] = new Array();
d_store["protection"] = new Array();
d_store["rnd"] = 0;

var d_req;
if (window.XMLHttpRequest) {
  d_req = new XMLHttpRequest();
} else if (window.ActiveXObject) {
  d_req = new ActiveXObject("Microsoft.XMLHTTP");
}

function D_ClientUpdate (api, fieldid)
{
  value = document.getElementById(fieldid).value
  if (d_store["clientvalues"][fieldid] != value) {
    d_store["clientvalues"][fieldid] = value;
    d_store["protection"][fieldid] = D_ElapsedMS() + 5000;
    var i=new Array(); 
    i["command"] = "set";
    i["value"] = value;
    D_SendMessage (api, i);
  }
}

function D_ServerUpdate (fieldid, value)
{
  if (d_store["servervalues"][fieldid] != value) {
    if (document.getElementById(fieldid).value != d_store["clientvalues"][fieldid]) { // unprocessed change
      d_store["servervalues"][fieldid] = value;
    } else if (D_ElapsedMS() > d_store["protection"][fieldid]) {
      d_store["servervalues"][fieldid] = value;
      d_store["clientvalues"][fieldid] = value;
      document.getElementById(fieldid).value = value;
    }
  }
}

function D_ElapsedMS ()
{
  var tDate = new Date();
  if (d_started==0) d_started = tDate.getTime();
  return tDate.getTime() - d_started;
}

function D_Debug (msg)
{
  if (document.getElementById('divdebug')) {
    document.getElementById('divdebug').innerHTML += "DBG: "+msg+"<br>";
  }
}

function D_AddWatch (code, input, ms)
{
  if (!ms) ms = 1;
  if (!input) input = "";
  d_store["watchsize"]++;
  d_store["watches"][d_store["watchsize"]] = new Array();
  d_store["watches"][d_store["watchsize"]]["code"] = code;
  d_store["watches"][d_store["watchsize"]]["input"] = input;
  d_store["watches"][d_store["watchsize"]]["ms"] = ms;
  d_store["watches"][d_store["watchsize"]]["timelimit"] = D_ElapsedMS() + ms;
}

function D_DynamicObject (content, id)
{
  return "<div id='div"+id+"'>"+content+"</div>";
}

function D_Lock (lock)
{
  d_store["locks"][lock] = true;
}

function D_Unlock (lock)
{
  d_store["locks"][lock] = false;
}

function D_Locked (lock)
{
  return d_store["locks"][lock];
}

function D_SendMessage (id, input)
{
  d_store["queuesize"]++;
  d_store["queue"][d_store["queuesize"]] = new Array();
  d_store["queue"][d_store["queuesize"]]["id"] = id;
  d_store["queue"][d_store["queuesize"]]["input"] = input;
  setTimeout('D_InternalSendMessages()', 1);
}

function D_InternalSendMessages ()
{
  if (!D_Locked ("rpc")) {
    if (d_store["queuepointer"] < d_store["queuesize"]) {
      var queuesize = d_store["queuesize"] - d_store["queuepointer"];
      if (queuesize > 1) {
        var ctr=0;
        var all=new Array();        
        while (d_store["queuepointer"] < d_store["queuesize"]) {
          d_store["queuepointer"]++;
          ctr++;
          all[ctr] = new Array();
          all[ctr]["id"] = d_store["queue"][d_store["queuepointer"]]["id"];
          all[ctr]["input"] = d_store["queue"][d_store["queuepointer"]]["input"];
        }
        all["amount"] = ctr;        
        D_Lock ("rpc");
        d_store["rnd"]++;
        d_req.open("POST", '/nkit/dyna.php');
        d_req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        d_req.send('rnd='+d_store["rnd"]+'&multi=yes&i='+encodeURI (D_Serialize (all)));
        d_req.onreadystatechange = D_CallBack;
        d_req.send(null);
      } else {
        d_store["queuepointer"]++;
        D_Lock ("rpc");
        d_store["rnd"]++;
        d_req.open("POST", '/nkit/dyna.php');
        d_req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        d_req.send('rnd='+d_store["rnd"]+'&id='+d_store["queue"][d_store["queuepointer"]]["id"]+'&i='+encodeURI (D_Serialize (d_store["queue"][d_store["queuepointer"]]["input"])));
        d_req.onreadystatechange = D_CallBack;
        d_req.send(null);
        d_store["queue"][d_store["queuepointer"]] = false;
      }
    }
  }
}

function D_CallBack ()
{
  if (d_req.readyState == 4) {
    if (d_req.status == 200) {      
      result = d_req.responseText;
      var res = D_Unserialize (result);
      if (res["type"]=="single") {
        d_store["lastresult"] = res;
        serverinput = d_store["lastresult"]["serverinput"];
        input = d_store["lastresult"]["input"];
        output = d_store["lastresult"]["output"];
        jscode = d_store["lastresult"]["jscode"];
        eval (jscode);
      } else if (res["type"]=="multi") {
        for (j=1; j<=res["count"]; j++) {
          d_store["lastresult"] = res[j];
          serverinput = d_store["lastresult"]["serverinput"];
          input = d_store["lastresult"]["input"];
          output = d_store["lastresult"]["output"];
          jscode = d_store["lastresult"]["jscode"];
          eval (jscode);
        }
      }
      D_Unlock('rpc');
      D_InternalSendMessages();
    }
  }
}

function D_Save (key, value)
{
  d_store[key] = value;
}

function D_Load (key, value)
{
  return d_store[key];
}

function D_Serialize (o)
{ 
  var php = new D_PHP_Serializer(); 
  return php.serialize(o);
}

function D_Unserialize (s)
{
  var php = new D_PHP_Serializer(); 
  return php.unserialize(s);
}

function D_XML2HTML (xml)
{
  var result = "";
  for (var i=0; i<xml.length; i++) {
    var c = xml.charCodeAt(i);
    if (c==32) {
      result += "&nbsp";
    } else if (c==10) {
      result += "<br>";
    } else if (c==13) {
      result += "";
    } else {
      result += "&#";
      result += c;
      result += ";";
    }
  } 
  return result;
}

function D_EO (arr)
{
  document.write (D_XML2HTML (D_SO (arr, 0)));
}

function D_SO (arr,level)
{
  var dumped_text = "";
  if(!level) level = 0;
  var level_padding = "";
  for(var j=0;j<level+1;j++) level_padding += "    ";
  var level_padding_m1 = "";
  for(var j=0;j<level;j++) level_padding_m1 += "    ";
  if(typeof(arr) == 'object') {
    if (level==0) {
      dumped_text += level_padding_m1 + "Array (\n";
    }
    for(var item in arr) {
      var value = arr[item];
      if (typeof(value) == 'object') {
        dumped_text += level_padding + "'" + item + "' => Array (\n";
        dumped_text += D_SO(value,level+1);
      } else {
        dumped_text += level_padding + "'" + item + "' => \"" + value + "\" ("+typeof(value)+")\n";
      }
    }
    dumped_text += level_padding_m1 + ")\n";
  } else {
    dumped_text = "\""+arr+"\" ("+typeof(arr)+")\n";
  }
  return dumped_text;
}

function D_PHP_Serializer(UTF8) {
	
	/** public methods */
	function serialize(v) {
		// returns serialized var
		var	s;
		switch(v) {
			case null:
				s = "N;";
				break;
			default:
				s = this[this.__sc2s(v)] ? this[this.__sc2s(v)](v) : this[this.__sc2s(__o)](v);
				break;
		};
		return s;
	};
	
	function unserialize(s) {
		// returns unserialized var from a php serialized string
		__c = 0;
		__s = s;
		return this[__s.substr(__c, 1)]();
	};
	
	function stringBytes(s) {
		// returns the php lenght of a string (chars, not bytes)
		return s.length;
	};
	
	function stringBytesUTF8(s) {
		// returns the php lenght of a string (bytes, not chars)
		var 	c, b = 0,
			l = s.length;
		while(l) {
			c = s.charCodeAt(--l);
			b += (c < 128) ? 1 : ((c < 2048) ? 2 : ((c < 65536) ? 3 : 4));
		};
		return b;
	};
	
	/** private methods */
	function __sc2s(v) {
		return v.constructor.toString();
	};
	
	function __sc2sKonqueror(v) {
		var	f;
		switch(typeof(v)) {
			case ("string" || v instanceof String):
				f = "__sString";
				break;
			case ("number" || v instanceof Number):
				f = "__sNumber";
				break;
			case ("boolean" || v instanceof Boolean):
				f = "__sBoolean";
				break;
			case ("function" || v instanceof Function):
				f = "__sFunction";
				break;
			default:
				f = (v instanceof Array) ? "__sArray" : "__sObject";
				break;
		};
		return f;
	};
	
	function __sNConstructor(c) {
		return (c === "[function]" || c === "(Internal Function)");
	};
	
	function __sCommonAO(v) {
		var	b, n,
			a = 0,
			s = [];
		for(b in v) {
			n = v[b] == null;
			if(n || v[b].constructor != Function) {
				s[a] = [
					(!isNaN(b) && parseInt(b).toString() === b ? this.__sNumber(b) : this.__sString(b)),
					(n ? "N;" : this[this.__sc2s(v[b])] ? this[this.__sc2s(v[b])](v[b]) : this[this.__sc2s(__o)](v[b]))
				].join("");
				++a;
			};
		};
		return [a, s.join("")];
	};
	
	function __sBoolean(v) {
		return ["b:", (v ? "1" : "0"), ";"].join("");
	};
	
	function __sNumber(v) {
		var 	s = v.toString();
		return (s.indexOf(".") < 0 ? ["i:", s, ";"] : ["d:", s, ";"]).join("");
	};
	
	function __sString(v) {
		return ["s:", v.length, ":\"", v, "\";"].join("");
	};
	
	function __sStringUTF8(v) {
		return ["s:", this.stringBytes(v), ":\"", v, "\";"].join("");
	};
	
	function __sArray(v) {
		var 	s = this.__sCommonAO(v);
		return ["a:", s[0], ":{", s[1], "}"].join("");
	};
	
	function __sObject(v) {
		var 	o = this.__sc2s(v),
			n = o.substr(__n, (o.indexOf("(") - __n)),
			s = this.__sCommonAO(v);
		return ["O:", this.stringBytes(n), ":\"", n, "\":", s[0], ":{", s[1], "}"].join("");
	};
	
	function __sObjectIE7(v) {
		var 	o = this.__sc2s(v),
			n = o.substr(__n, (o.indexOf("(") - __n)),
			s = this.__sCommonAO(v);
		if(n.charAt(0) === " ")
			n = n.substring(1);
		return ["O:", this.stringBytes(n), ":\"", n, "\":", s[0], ":{", s[1], "}"].join("");
	};
	
	function __sObjectKonqueror(v) {
		var	o = v.constructor.toString(),
			n = this.__sNConstructor(o) ? "Object" : o.substr(__n, (o.indexOf("(") - __n)),
			s = this.__sCommonAO(v);
		return ["O:", this.stringBytes(n), ":\"", n, "\":", s[0], ":{", s[1], "}"].join("");
	};
	
	function __sFunction(v) {
		return "";
	};
	
	function __uCommonAO(tmp) {
		var	a, k;
		++__c;
		a = __s.indexOf(":", ++__c);
		k = parseInt(__s.substr(__c, (a - __c))) + 1;
		__c = a + 2;
		while(--k)
			tmp[this[__s.substr(__c, 1)]()] = this[__s.substr(__c, 1)]();
		return tmp;
	};

	function __uBoolean() {
		var	b = __s.substr((__c + 2), 1) === "1" ? true : false;
		__c += 4;
		return b;
	};
	
	function __uNumber() {
		var	sli = __s.indexOf(";", (__c + 1)) - 2,
			n = Number(__s.substr((__c + 2), (sli - __c)));
		__c = sli + 3;
		return n;
	};
	
	function __uStringUTF8() {
		var 	c, sls, sli, vls,
			pos = 0;
		__c += 2;
		sls = __s.substr(__c, (__s.indexOf(":", __c) - __c));
		sli = parseInt(sls);
		vls = sls = __c + sls.length + 2;
		while(sli) {
			c = __s.charCodeAt(vls);
			pos += (c < 128) ? 1 : ((c < 2048) ? 2 : ((c < 65536) ? 3 : 4));
			++vls;
			if(pos === sli)
				sli = 0;
		};
		pos = (vls - sls);
		__c = sls + pos + 2;
		return __s.substr(sls, pos);
	};
	
	function __uString() {
		var 	sls, sli;
		__c += 2;
		sls = __s.substr(__c, (__s.indexOf(":", __c) - __c));
		sli = parseInt(sls);
		sls = __c + sls.length + 2;
		__c = sls + sli + 2;
		return __s.substr(sls, sli);
	};
	
	function __uArray() {
		var	a = this.__uCommonAO([]);
		++__c;
		return a;
	};
	
	function __uObject() {
		var 	tmp = ["s", __s.substr(++__c, (__s.indexOf(":", (__c + 3)) - __c))].join(""),
			a = tmp.indexOf("\""),
			l = tmp.length - 2,
			o = tmp.substr((a + 1), (l - a));
		if(eval(["typeof(", o, ") === 'undefined'"].join("")))
			eval(["function ", o, "(){};"].join(""));
		__c += l;
		eval(["tmp = this.__uCommonAO(new ", o, "());"].join(""));
		++__c;
		return tmp;
	};
	
	function __uNull() {
		__c += 2;
		return null;
	};
	
	function __constructorCutLength() {
		function ie7bugCheck(){};
		var	o1 = new ie7bugCheck(),
			o2 = new Object(),
			c1 = __sc2s(o1),
			c2 = __sc2s(o2);
		if(c1.charAt(0) !== c2.charAt(0))
			__ie7 = true;
		return (__ie7 || c2.indexOf("(") !== 16) ? 9 : 10;
	};
	
	/** private variables */
	var 	__c = 0,
		__ie7 = false,
		__b = __sNConstructor(__c.constructor.toString()),
		__n = __b ? 9 : __constructorCutLength(),
		__s = "",
		__a = [],
		__o = {},
		__f = function(){};
	
	/** public prototypes */
	D_PHP_Serializer.prototype.serialize = serialize;
	D_PHP_Serializer.prototype.unserialize = unserialize;
	D_PHP_Serializer.prototype.stringBytes = UTF8 ? stringBytesUTF8 : stringBytes;
	
	/** serialize: private prototypes */
	if(__b) { // Konqueror / Safari prototypes
		D_PHP_Serializer.prototype.__sc2s = __sc2sKonqueror;
		D_PHP_Serializer.prototype.__sNConstructor = __sNConstructor;
		D_PHP_Serializer.prototype.__sCommonAO = __sCommonAO;
		D_PHP_Serializer.prototype[__sc2sKonqueror(__b)] = __sBoolean;
		D_PHP_Serializer.prototype.__sNumber = 
		D_PHP_Serializer.prototype[__sc2sKonqueror(__n)] = __sNumber;
		D_PHP_Serializer.prototype.__sString = D_PHP_Serializer.prototype[__sc2sKonqueror(__s)] = UTF8 ? __sStringUTF8 : __sString;
		D_PHP_Serializer.prototype[__sc2sKonqueror(__a)] = __sArray;
		D_PHP_Serializer.prototype[__sc2sKonqueror(__o)] = __sObjectKonqueror;
		D_PHP_Serializer.prototype[__sc2sKonqueror(__f)] = __sFunction;
	}
	else { // FireFox, IE, Opera prototypes
		D_PHP_Serializer.prototype.__sc2s = __sc2s;
		D_PHP_Serializer.prototype.__sCommonAO = __sCommonAO;
		D_PHP_Serializer.prototype[__sc2s(__b)] = __sBoolean;
		D_PHP_Serializer.prototype.__sNumber = 
		D_PHP_Serializer.prototype[__sc2s(__n)] = __sNumber;
		D_PHP_Serializer.prototype.__sString = D_PHP_Serializer.prototype[__sc2s(__s)] = UTF8 ? __sStringUTF8 : __sString;
		D_PHP_Serializer.prototype[__sc2s(__a)] = __sArray;
		D_PHP_Serializer.prototype[__sc2s(__o)] = __ie7 ? __sObjectIE7 : __sObject;
		D_PHP_Serializer.prototype[__sc2s(__f)] = __sFunction;
	};
	
	/** unserialize: private prototypes */
	D_PHP_Serializer.prototype.__uCommonAO = __uCommonAO;
	D_PHP_Serializer.prototype.b = __uBoolean;
	D_PHP_Serializer.prototype.i =
	D_PHP_Serializer.prototype.d = __uNumber;
	D_PHP_Serializer.prototype.s = UTF8 ? __uStringUTF8 : __uString;
	D_PHP_Serializer.prototype.a = __uArray;
	D_PHP_Serializer.prototype.O = __uObject;
	D_PHP_Serializer.prototype.N = __uNull;
};

function D_InternalProcessWatches ()
{
  for (var i=1; i<=d_store["watchsize"]; i++) {
    var specs = d_store["watches"][i];
    if (specs["timelimit"] <= D_ElapsedMS()) {
      d_store["watches"][i]["timelimit"] = specs["timelimit"] + specs["ms"];
      var input = specs["input"];
      eval (specs["code"]);
    }
  }
  setTimeout('D_InternalProcessWatches ()', 10);
}

setTimeout('D_InternalProcessWatches ()', 10);