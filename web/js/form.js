/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

MESSAGE_ERROR = 0;
MESSAGE_WARNING = 1;
MESSAGE_STATUS = 2;
MESSAGE_INFO = 3;

function createForm(){
	
	function esc(str){
		
		let map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&apos;',
			"`": '&#96;',
		};
		
		let res = str;
		
		for(ch in map){
			res = res.replaceAll(ch, map[ch]);
		}
		
		return res;
		
	}
	
	let form = document.createElement('div');
	form.className = 'js_form';
	
	let messageBox = document.createElement('div');
	let closeBtn = document.createElement('span');
	closeBtn.style.float = 'right';
	closeBtn.style.cursor = 'pointer';
	closeBtn.innerText = '×';
	closeBtn.onclick = function(e){
		form.clearMessage();
	}
	let message = document.createElement('span');
	messageBox.style.display = 'none';
	messageBox.appendChild(message);
	messageBox.appendChild(closeBtn);
	form.appendChild(messageBox);
	
	form.setMessage = function(text, type=MESSAGE_ERROR, closeable=true){
		if(closeable) closeBtn.style.display = 'block';
		else closeBtn.style.display = 'none';
		switch(type){
			case MESSAGE_ERROR:
				messageBox.className = 'message err';
				break;
			case MESSAGE_WARNING:
				messageBox.className = 'message warning';
				break;
			case MESSAGE_STATUS:
				messageBox.className = 'message status';
				break;
			case MESSAGE_INFO:
				messageBox.className = 'message info';
				break;
		}
		message.innerHTML = text;
		messageBox.style.display = 'block';
		pages[page].resize();
	}
	
	form.clearMessage = function(){
		messageBox.style.display = 'none';
		pages[page].resize();
	}
	
	let tooltip = document.createElement('div');
	tooltip.className = 'tooltip';
	/*tooltip.style.position = 'fixed';
	tooltip.style.display = 'none';
	tooltip.style.background = 'white';*/
	
	form.appendChild(tooltip);
	
	form.getHeight = function(){
		if(messageBox.style.display=='block'){
			return form.offsetHeight-messageBox.offsetHeight;
		}
		return form.offsetHeight;
	}
	
	form.getWidth = function(){
		return form.offsetWidth;
	}
	
	document.addEventListener('mousemove', function(e){
		if(tooltip.style.display=='block'){
			tooltip.style.left = (e.clientX+5)+'px';
			tooltip.style.top = (e.clientY)+'px';
		}
	});
	
	form.showTooltip = function(text){
		tooltip.innerText = text;
		tooltip.style.display = 'block';
	}
	
	form.hideTooltip = function(){
		tooltip.style.display = 'none';
	}
	
	page = null;
	
	pages = [];
	
	form.form = {};
	form.inputs = {};
	
	form.onFormUpdate = null;
	
	form.addPage = function(id, page){
		pages[id] = page;
	}
	
	window.onbeforeunload = function(e){
		if(pages[page].critical) return "Do you really want to leave the page?";
	}
	
	window.addEventListener('resize', function(e){
		if(pages[page]) pages[page].resize();
	});
	
	form.init = function(){
		if(pages[page]) pages[page].resize();
	}
	
	form.setPage = function(id){
		if(page!=null) {
			form.removeChild(pages[page]);
			if(pages[page].onSubmit) pages[page].onSubmit();
		}
		page = id;
		form.appendChild(pages[page]);
		if(pages[page].onOpen) pages[page].onOpen();
		pages[page].resize();
		form.clearMessage();
	}
	
	form.update = function(name, val){
		//let v = esc(val);
		let v = val;
		form.form[name] = v;
		if(form.onFormUpdate) form.onFormUpdate(name, v);
	}
	
	return form;
	
}

function createFormPage(form, init){
	
	let page = document.createElement('div');
	page.className = 'formPage';
	page.critical = false;
	
	page.onOpen = null;
	page.onSubmit = null;
	
	let pageBody = document.createElement('div');
	pageBody.className = 'pageBody';
	
	page.add = function(element){
		pageBody.appendChild(element);
	}
	
	page.appendChild(pageBody);
	
	let pageControls = document.createElement('div');
	pageControls.className = 'pageControls';
	
	page.resize = function(){
		
		let controlsHeight = pageControls.offsetHeight;
		
		pageControls.style.position = 'absolute';
		pageControls.style.bottom = '0';
		
		pageBody.style.position = 'absolute';
		pageBody.style.height = (form.getHeight()-controlsHeight)+'px';
		
	}
	
	page.addControl = function(text, pg){
		let btn = document.createElement('button');
		btn.className = 'action';
		btn.innerText = text;
		btn.onclick = function(e){
			form.setPage(pg);
		}
		pageControls.appendChild(btn);
	}
	
	page.addControlF = function(text, func){
		let btn = document.createElement('button');
		btn.className = 'action';
		btn.innerText = text;
		btn.onclick = func;
		pageControls.appendChild(btn);
	}
	
	page.addControlB = function(btn){
		btn.className = 'action';
		pageControls.appendChild(btn);
	}
	
	page.appendChild(pageControls);
	
	init(page);
	
	return page;
	
}

function createTabBox(form, name, titleText){
	
	let box = document.createElement('div');
	box.className = 'formrow tabBox';
	
	let bar = document.createElement('div');
	bar.className = 'tabBar';
	
	let title = document.createElement('span');
	title.innerText = titleText;
	title.className = 'formlbl';
	bar.appendChild(title);
	
	let tabButtons = document.createElement('span');
	tabButtons.className = 'tabButtons';
	bar.appendChild(tabButtons);
	
	box.appendChild(bar);
	
	let tabContainer = document.createElement('div');
	box.appendChild(tabContainer);
	
	box.tabs = [];
	box.btnList = [];
	
	box.form = form;
	
	box.setTab = function(tabName){
		for(let i in box.btnList){
			box.btnList[i].classList.remove('selected');
		}
		box.btnList[tabName].classList.add('selected');
		if(tabContainer.children.length>0) tabContainer.removeChild(tabContainer.children[0]);
		tabContainer.appendChild(box.tabs[tabName]);
		form.update(name, tabName);
	}
	
	box.addTab = function(tabName, title, tab){
		box.tabs[tabName] = tab;
		let button = document.createElement('button');
		button.className = 'tabbtn';
		button.innerText = title;
		button.onclick = function(e){
			box.setTab(tabName);
		}
		box.btnList[tabName] = button;
		tabButtons.appendChild(button);
	}
	
	form.inputs[name] = box;
	
	return box;
	
}

function createTab(){
	
	let tab = document.createElement('div');
	tab.className = 'tabBody';
	
	tab.add = function(element){
		tab.appendChild(element);
	}
	
	return tab;
	
}

function createCheckList(form, name, titleText, selectAllText='Select all'){
	
	let checkListContainer = document.createElement('div');
	checkListContainer.className = 'formrow';
	
	let text = document.createElement('div');
	text.innerText = titleText;
	text.className = 'formlbl';
	checkListContainer.appendChild(text);
	
	let selectAll = document.createElement('label');
	let selectAllCheckBox = document.createElement('input');
	selectAll.checkBox = selectAllCheckBox;
	selectAllCheckBox.type = 'checkbox';
	selectAll.appendChild(selectAllCheckBox);
	//selectAll.innerHTML += selectAllText;
	selectAll.appendChild(document.createTextNode(selectAllText));
	checkListContainer.appendChild(selectAll);
	checkListContainer.appendChild(document.createElement('br'));
	
	let checkBoxes = [];
	
	function updateValue(){
		let val = [];
		for(let cb of checkBoxes){
			if(cb.checkBox.checked){
				val[val.length] = cb.value;
			}
		}
		form.update(name, val.join(','));
	}
	
	function updateSelectAll(){
		let allChecked = true;
		for(let cb of checkBoxes){
			if(!cb.checkBox.checked){
				allChecked = false;
				break;
			}
		}
		selectAllCheckBox.checked = allChecked;
	}
	
	selectAll.oninput = function(e){
		for(let cb of checkBoxes){
			cb.checkBox.checked = selectAllCheckBox.checked;
		}
		updateValue();
	}
	
	let checkList = document.createElement('div');
	checkList.items = [];
	
	checkListContainer.clearItems = function(){
		for(let el of checkList.children){
			checkList.removeChild(el);
		}
		checkBoxes.splice(0, checkBoxes.length);
	}
	
	checkListContainer.addItem = function(name, label){
		let checkBoxContainer = document.createElement('label');
		checkBoxContainer.style.display = 'block';
		checkList.items[name] = checkBoxContainer;
		let checkBox = document.createElement('input');
		checkBox.type = 'checkbox';
		checkBoxContainer.value = name;
		checkBoxContainer.checkBox = checkBox;
		checkBoxContainer.appendChild(checkBox);
		//checkBoxContainer.innerHTML += list[i];
		let span = document.createElement('span');
		span.innerHTML = label;
		//checkBoxContainer.appendChild(document.createTextNode(label));
		checkBoxContainer.appendChild(span);
		checkBoxContainer.oninput = function(e){
			updateSelectAll();
			updateValue();
		}
		checkBoxes[checkBoxes.length] = checkBoxContainer;
		checkList.appendChild(checkBoxContainer);
	}
	
	checkListContainer.check = function(name, check){
		checkList.items[name].checkBox.checked = check;
		updateSelectAll();
		updateValue();
	}
	
	checkListContainer.appendChild(checkList);
	
	form.inputs[name] = checkListContainer;
	
	return checkListContainer;
	
}

function createNumberInput(form, name, labelText, placeholder, min, max, def){
	
	let label = document.createElement('div');
	label.className = 'formrow';
	
	let text = document.createElement('span');
	text.className = 'formlbl';
	text.innerText = labelText;
	
	let input = document.createElement('input');
	input.className = 'formin';
	input.type = 'number';
	if(min) input.min = min;
	if(max) input.max = max;
	if(def) {
		input.value = def;
		form.update(name, def);
	}
	if(placeholder) input.placeholder = placeholder;
	input.oninput = function(e){
		form.update(name, input.value);
	}
	
	label.appendChild(text);
	label.appendChild(input);
	
	form.inputs[name] = input;
	
	return label;
	
}

function createDateInput(form, name, labelText){
	
	let label = document.createElement('div');
	label.className = 'formrow';
	
	let text = document.createElement('span');
	text.innerText = labelText;
	text.className = 'formlbl';
	
	let input = document.createElement('input');
	input.type = 'date';
	input.className = 'formin';
	input.oninput = function(e){
		form.update(name, input.value);
	}
	
	label.appendChild(text);
	label.appendChild(input);
	
	form.inputs[name] = input;
	
	return label;
	
}

function createTextInput(form, name, labelText, placeholder){
	
	let label = document.createElement('div');
	label.className = 'formrow';
	
	let text = document.createElement('span');
	text.innerText = labelText;
	text.className = 'formlbl';
	
	let input = document.createElement('input');
	input.type = 'text';
	input.className = 'formin';
	if(placeholder) input.placeholder = placeholder;
	input.oninput = function(e){
		form.update(name, input.value);
	}
	label.appendChild(text);
	label.appendChild(input);
	form.inputs[name] = input;
	return label;
	
}

function createTextArea(form, name, labelText, placeholder){
	
	let label = document.createElement('div');
	label.className = 'formrow';
	
	let text = document.createElement('span');
	text.innerText = labelText;
	text.className = 'formlbl';
	
	let hint = document.createElement('span');
	
	label.setHint = function(text){
		let hintButton = document.createElement('img');
		hintButton.src = 'res/hint.png';
		//hintButton.innerText = '?';
		hintButton.onmouseenter = function(e){
			form.showTooltip(text);
		}
		hintButton.onmouseleave = function(e){
			form.hideTooltip();
		}
		hint.appendChild(hintButton);
	}
	
	let input = document.createElement('textarea');
	input.className = 'textarea';
	if(placeholder) input.placeholder = placeholder;
	input.oninput = function(e){
		form.update(name, input.value)
	}
	label.appendChild(text);
	label.appendChild(hint);
	label.appendChild(input);
	form.inputs[name] = input;
	return label;
	
}

function createButton(label, clickHandler){
	
	let button = document.createElement('button');
	button.className = 'formrow action';
	
	button.innerText = label;
	button.onclick = clickHandler;
	
	return button;
	
}
