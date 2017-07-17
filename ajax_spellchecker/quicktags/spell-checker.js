var wHeight = 0, wWidth = 0, owHeight = 0, owWidth = 0, sr = null, status = null, suggestions = null, htmlSrc = null, edCanvas = null;

function saveContent() {
	for(var i = 0; i < suggestions.length; i++){
		var a = document.getElementById('w'+i);
		var m = document.getElementById('m'+i);

		if(a){
			var t = document.createTextNode(a.innerHTML);
			a.parentNode.replaceChild(t,a);
		}
		if(m)
			m.parentNode.removeChild(m);
	}
	edCanvas.value = sr.innerHTML;
	window.close();
}

function onLoadInit() {
	edCanvas = window.opener.document.getElementById("content");
	sr = document.getElementById('spellResults');
	status = document.getElementById('status');

	sr.innerHTML = edCanvas.value;
	htmlSrc = sr.innerHTML;
	status.innerHTML = "idle";

	addEvent(document,"click", handleClicks);

	resizeInputs();
	checkSpelling();
}

function resizeInputs() {
	if (self.innerHeight) {
		 wHeight = self.innerHeight - 80;
		 wWidth = self.innerWidth - 16;
	} else {
		 wHeight = document.body.clientHeight - 80;
		 wWidth = document.body.clientWidth - 16;
	}

	sr.style.height = Math.abs(wHeight) + 'px';
	sr.style.width  = Math.abs(wWidth) + 'px';
}

function ajaxOnLoading() {
	status.innerHTML = 'Sending ...';
}

function ajaxOnLoaded() {
	status.innerHTML = 'Sending ... done';
}

function ajaxOnInteractive() {
	status.innerHTML = 'Receiving ...';
}

function ajaxOnCompletion() {
	status.innerHTML = 'Receiving ... done';
	timer = window.setTimeout('clearStatus()', 1000);
}

function clearStatus() {
	status.innerHTML = 'idle';
}

function checkSpelling() {
	htmlSrc = sr.innerHTML.replace(/<a class="error" id="w[0-9]+">([^<]*)<\/a>/g,"$1");

	ajax = new sack('../service/spell-check-service.php')
	ajax.setVar('do', 'check');
	ajax.setVar('content', escape(htmlSrc));

	ajax.method = 'POST';
	ajax.onLoading = ajaxOnLoading;
	ajax.onLoaded = ajaxOnLoaded;
	ajax.onInteractive = ajaxOnInteractive;
	ajax.onCompletion = ajaxOnCompletion;
	ajax.execute = true;

	ajax.runAJAX();
}

function addToPersonal(word) {
	var ajax = new sack('../service/spell-check-service.php');
	ajax.setVar('do', 'add');
	ajax.setVar('content', escape(word));

	ajax.method = 'POST';
	ajax.onLoading = ajaxOnLoading;
	ajax.onLoaded = ajaxOnLoaded;
	ajax.onInteractive = ajaxOnInteractive;
	ajax.onCompletion = ajaxOnCompletion;
	ajax.execute = true;

	ajax.runAJAX();
}

function storeReplacement(bad, good) {
	var ajax = new sack('../service/spell-check-service.php');
	ajax.setVar('do', 'store');
	ajax.setVar('content', escape(bad+':'+good));

	ajax.method = 'POST';
	ajax.onLoading = ajaxOnLoading;
	ajax.onLoaded = ajaxOnLoaded;
	ajax.onInteractive = ajaxOnInteractive;
	ajax.onCompletion = ajaxOnCompletion;
	ajax.execute = true;

	ajax.runAJAX();
}

function updateDisplay(data) {
	suggestions = data;
	html = '';
	offset = 0;
	for(i = 0; i < data.length; i++) {
		var wOffset = data[i]['o'];
		var wLength = data[i]['l'];
		var wSug = data[i]['value'];
		var contentBefore = htmlSrc.substring(offset, wOffset);
		var word = '<a class="error" id="w' + i + '">' + htmlSrc.substring(wOffset, wOffset + wLength) + '</a>';

		html += (contentBefore + word);
		offset = wOffset + wLength;
	}
	html += htmlSrc.substring(offset);
	sr.innerHTML = html;
}

function toggle(obj){
	var menu = document.getElementById(obj.id.replace("w","m"));
	if(!menu){
		left = obj.offsetLeft;
		top = obj.offsetTop + obj.offsetHeight;
		id = obj.id.replace('w', '');
		parent = obj.parentNode;

		menu = document.createElement('ul');
		menu.innerHTML = renderSuggestions(id);
		menu.className = 'menu';
		menu.style.left = left + 'px';
		menu.style.top = top + 'px';
		menu.style.display = 'none';
		menu.id = 'm'+id;

		parent.appendChild(menu);
	} else if(menu.style.display == "none") {
		menu.style.display = 'block'
	} else {
		menu.style.display = "none";
	}
}

function renderSuggestions(index) {
	var menu = '';

	if(suggestions[index]['value'][0].length == 0) {
		menu += "<li>(No&nbsp;suggestions)</li>";
	} else {
		for(var i = 0; i < suggestions[index]['value'].length; i++) {
			menu += ('<li><a class="menuitem" id="m' + index + 's' + i + '">');
			menu += (suggestions[index]['value'][i] + '</a></li>');
		}
	}

	menu += '<li><hr /><a class="menuitem" id="m' + index + 'custom">Enter&nbsp;word</a></li>';
	menu += '<li><hr /><a class="menuitem" id="m' + index + 'add">Add&nbsp;to&nbsp;dictionary</a></li>';
	return menu;
}

function handleClicks(e) {
	var src;
	if(e.target)
		src = e.target;
	else if(e.srcElement)
		src = e.srcElement;

	if(src.className == 'error') {
		toggle(src);
	} else if(src.className == 'menuitem') {
		if(src.id.indexOf('custom') >= 0)
			replaceCustom(src.id.replace(/m([0-9]+)custom/,'$1'));
		else if(src.id.indexOf('add') >= 0)
			addWord(src.id.replace(/m([0-9]+)add/,'$1'));
		else
			replaceWord(src.id.replace(/m([0-9]+)s([0-9]+)/, '$1'), src.id.replace(/m([0-9]+)s([0-9]+)/, '$2'));
	} else {

		for(var i = 0; i < suggestions.length; i++) {
			var menu = document.getElementById('m' + i);
			if(menu && menu != src)
				menu.style.display = 'none';
		}
	}
}

function replaceWord(wid, sid) {
	var contentBefore = htmlSrc.substring(0, suggestions[wid]['o']);
	var newWord = suggestions[wid]['value'][sid];
	var contentAfter = htmlSrc.substring(suggestions[wid]['o'] + suggestions[wid]['l']);
	var word = document.getElementById('w'+wid);
	var menu = document.getElementById('m'+wid);

	htmlSrc = contentBefore + newWord + contentAfter;
	nwe = document.createTextNode(newWord);
	word.parentNode.replaceChild(nwe, word);
	menu.parentNode.removeChild(menu);
}

function addWord(wid) {
	var wLength = suggestions[wid]['l'];
	var wOffset = suggestions[wid]['o'];
	var newWord = htmlSrc.substring(wOffset, wOffset + wLength);
	var word = document.getElementById('w' + wid);
	var menu = document.getElementById('m' + wid);

	addToPersonal(newWord);

	nwe = document.createTextNode(newWord);
	word.parentNode.replaceChild(nwe, word);
	menu.parentNode.removeChild(menu);
}

function replaceCustom(wid) {
	var wLength = suggestions[wid]['l'];
	var wOffset = suggestions[wid]['o'];
	var contentBefore = htmlSrc.substring(0, wOffset);
	var oldWord = htmlSrc.substring(wOffset, wOffset + wLength);
	var newWord = prompt("Enter replacement");
	var contentAfter = htmlSrc.substring(wOffset + wLength);
	var span = document.getElementById('w' + wid);
	var menu = document.getElementById('m' + wid);

	if(newWord == '' || newWord == null)
		return;

	htmlSrc = contentBefore + newWord + contentAfter;

	nwe = document.createTextNode(newWord);
	span.parentNode.replaceChild(nwe,span);
	menu.parentNode.removeChild(menu);

	storeReplacement(oldWord, newWord);
}

function addEvent(obj, evt, handler) {
	if(obj.attachEvent)
		obj.attachEvent("on" + evt, handler);
	else if(obj.addEventListener)
		obj.addEventListener(evt,handler,false);
}
