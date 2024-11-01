var xmlreqs = new Array();

function CXMLReq(freed)
{
	this.freed = freed;
	this.xmlhttp = false;
	if (window.XMLHttpRequest) {
		this.xmlhttp = new XMLHttpRequest();
	} else if (window.ActiveXObject) {
		this.xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
}

function xmlreqGET(url) {
	var pos = -1;
	for (var i=0; i<xmlreqs.length; i++) {
		if (xmlreqs[i].freed == 1) { pos = i; break; }
	}
	if (pos == -1) { pos = xmlreqs.length; xmlreqs[pos] = new CXMLReq(1); }
	if (xmlreqs[pos].xmlhttp) {
		xmlreqs[pos].freed = 0;
		xmlreqs[pos].xmlhttp.open("GET",url,true);
		xmlreqs[pos].xmlhttp.onreadystatechange = function() {
			if (typeof(xmlhttpChange) != 'undefined') { xmlhttpChange(pos); }
		}
		if (window.XMLHttpRequest) {
			xmlreqs[pos].xmlhttp.send(null);
		} else if (window.ActiveXObject) {
			xmlreqs[pos].xmlhttp.send();
		}
	}
}

function xmlhttpChange(pos) {
	if (typeof(xmlreqs[pos]) != 'undefined' && xmlreqs[pos].freed == 0 && xmlreqs[pos].xmlhttp.readyState == 4) {
		if (xmlreqs[pos].xmlhttp.status == 200 || xmlreqs[pos].xmlhttp.status == 304) {
			starsrate_handleResponse(xmlreqs[pos].xmlhttp.responseText);
		} else {
			handle_error();
		}
		xmlreqs[pos].freed = 1;
    	}
}

function starsrate_sndVote(scriptname, vote, id_num, skin)
{
    if (vote && id_num)
    {
        var element = document.getElementById(id_num);
    	//new Effect.Fade(element);
        if (element)
            element.innerHTML = '<div class="loadingbox"></div>';
    	
        xmlreqGET(scriptname + '?l=' + location + '&j=' + vote + '&q=' + id_num + '&p=' + scriptname + '&sk=' + skin);
    }
}

function starsrate_sndCheckResult(scriptname, id_num, skin)
{
    if (id_num)
    {
        var element = document.getElementById(id_num);
    	//new Effect.Fade(element);
         if (element)
            element.innerHTML = '<div class="loadingbox"></div>';
        
        xmlreqGET(scriptname + '?l=' + location + '&check=' + id_num + '&q=' + id_num + '&p=' + scriptname + '&sk=' + skin);
    }  
}

function starsrate_sndOpinion(scriptname, id_num, opinion)
{
    if (id_num)
    {
        var element = document.getElementById(id_num);
    	//new Effect.Fade(element);
         // if (element)
         //    element.innerHTML = '<div class="loadingbox"></div>';
        
        xmlreqGET(scriptname + '?l=' + location + '&q=' + id_num + '&p=' + scriptname + '&opinion=' + opinion);
    }
}

function starsrate_sndCheckOpinion(scriptname, id_num)
{
    if (id_num)
    {
        var element = document.getElementById(id_num);
    	//new Effect.Fade(element);
         // if (element)
         //    element.innerHTML = '<div class="loadingbox"></div>';
        
        xmlreqGET(scriptname + '?l=' + location + '&q=' + id_num + '&p=' + scriptname + '&opinion_check=' + id_num);
    }
}

function handle_error()
{
}

function starsrate_handleResponse(response)
{   
    if (response != null)
    {
        var update = new Array();
    
        if(response.indexOf('|') != -1) {
            update = response.split('|');
            starsrate_changeText(update[0], update[1]);
        }   
    }
}

function starsrate_changeText( div2show, text )
{
    // Detect Browser
    var IE = (document.all) ? 1 : 0;
    var DOM = 0; 
    if (parseInt(navigator.appVersion) >=5) {DOM=1};

    // Grab the content from the requested "div" and show it in the "container"
    
    if (DOM) {
        var viewer = document.getElementById(div2show);
        if (viewer)
            viewer.innerHTML=text
    }
    else if(IE) {
        document.all[div2show].innerHTML=text
    }
}

function srb_mouse_over(el, e, object)
{
    
    if (!e) var e = window.event;
    
    if (window.Node && Node.prototype && !Node.prototype.contains){
        Node.prototype.contains = function (arg){
            return !!(this.compareDocumentPosition(arg) & 16)
        }
    }
    
    try
    {
        var tg = (window.event) ? e.srcElement : e.target;
        var relTarg = e.relatedTarget || e.fromElement;
        
        if (el.id != relTarg.id && !el.contains(relTarg))
        {
            Slide(object).down();
        }
    }
    catch(err)
    {
    }
}

function srb_mouse_out(el, e, object)
{    
     if (!e) var e = window.event;
    
    if (window.Node && Node.prototype && !Node.prototype.contains){
        Node.prototype.contains = function (arg){
            return !!(this.compareDocumentPosition(arg) & 16)
        }
    }
    
    try
    {
        var tg = (window.event) ? e.srcElement : e.target;
        var relTarg = e.relatedTarget || e.toElement;
        
        if (el.id != relTarg.id && !el.contains(relTarg))
        {
            Slide(object).up();
        }
    }
    catch(err)
    {
    }
}

var slideInUse = new Array();

function Slide(objId, options) {
	this.obj = document.getElementById(objId);
	this.duration = 1;
	this.height = parseInt(this.obj.style.height);

	if(typeof options != 'undefined') { this.options = options; } else { this.options = {}; }
	if(this.options.duration) { this.duration = this.options.duration; }
		
	this.up = function() {
		this.curHeight = this.height;
		this.newHeight = '1';
		if(slideInUse[objId] != true) {
			var finishTime = this.slide();
			window.setTimeout("Slide('"+objId+"').finishup("+this.height+");",finishTime);
		}
	}
	
	this.down = function() {
		this.newHeight = this.height;
		this.curHeight = '1';
		if(slideInUse[objId] != true) {
			this.obj.style.height = '1px';
			this.obj.style.display = 'block';
			this.slide();
		}
	}
	
	this.slide = function() {
		slideInUse[objId] = true;
		var frames = 30 * duration; // Running at 30 fps

		var tIncrement = (duration*200) / frames;
		tIncrement = Math.round(tIncrement);
		var sIncrement = (this.curHeight-this.newHeight) / frames;

		var frameSizes = new Array();
		for(var i=0; i < frames; i++) {
			if(i < frames/2) {
				frameSizes[i] = (sIncrement * (i/frames))*4;
			} else {
				frameSizes[i] = (sIncrement * (1-(i/frames)))*4;
			}
		}
		
		for(var i=0; i < frames; i++) {
			this.curHeight = this.curHeight - frameSizes[i];
			window.setTimeout("document.getElementById('"+objId+"').style.height='"+Math.round(this.curHeight)+"px';",tIncrement * i);
		}
		
		window.setTimeout("delete(slideInUse['"+objId+"']);",tIncrement * i);
		
		if(this.options.onComplete) {
			window.setTimeout(this.options.onComplete, tIncrement * (i-2));
		}
		
		return tIncrement * i;
	}
	
	this.finishup = function(height) {
		this.obj.style.display = 'none';
		this.obj.style.height = height + 'px';
	}
	
	return this;
}
