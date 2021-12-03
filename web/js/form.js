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
	let blackout = document.createElement('span');
	blackout.style.display = 'none';
	blackout.style.backgroundColor = '#00000030';
	blackout.style.position = 'absolute';
	blackout.style.width = '100%';
	blackout.style.height = '100%';
	blackout.style.zIndex = 9999;
	form.appendChild(blackout);

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

	form.blackout = function(){
		blackout.style.display = 'block';
	}

	form.noblackout = function(){
		blackout.style.display = 'none';
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
			if(window.innerWidth-e.clientX<tooltip.width){
				if(e.clientX<tooltip.width){
					tooltip.style.left = '0px';
					tooltip.style.top = (e.clientY+10)+'px';
				} else {
					tooltip.style.left = (e.clientX-tooltip.width-10)+'px';
					tooltip.style.top = (e.clientY)+'px';
				}
			} else {
				tooltip.style.left = (e.clientX+10)+'px';
				tooltip.style.top = (e.clientY)+'px';
			}
		}
	});

	form.showTooltip = function(text){
		tooltip.innerText = text;
		tooltip.style.left = '0px';
		tooltip.style.top = '0px';
		tooltip.style.display = 'block';
		tooltip.width = tooltip.offsetWidth;
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

function createTabBox(form, name, titleText, hintText){

	let box = document.createElement('div');
	box.className = 'formrow tabBox';

	let bar = document.createElement('div');
	bar.className = 'tabBar';

	let title = document.createElement('span');
	title.innerText = titleText;
	title.className = 'formlbl tabboxlbl';
	bar.appendChild(title);

	let hint = document.createElement('span');

	box.setHint = function(text){
		let hintButton = document.createElement('img');
		hintButton.className = 'hintButton';
		hintButton.src = 'res/hint.png';
		hintButton.onmouseenter = function(e){
			form.showTooltip(text);
		}
		hintButton.onmouseleave = function(e){
			form.hideTooltip();
		}
		hint.appendChild(hintButton);
	}

	if (hintText) {
		box.setHint(hintText);
	}

	title.appendChild(hint);

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

	box.addTab = function(tabName, title, tab, hintText){

		box.tabs[tabName] = tab;
		let button = document.createElement('button');
		let radioOff = document.createElement('img');
		radioOff.className = "radioOff";
		radioOff.src = "res/radio_off.png";
		let radioOn = document.createElement('img');
		radioOn.className = "radioOn";
		radioOn.src = "res/radio_on.png";
		button.appendChild(radioOff);
		button.appendChild(radioOn);
		button.className = 'tabbtn';
		button.innerHTML += title;
		button.onclick = function(e){
			box.setTab(tabName);
		}
		box.btnList[tabName] = button;
		if(hintText){
			let hintButton = document.createElement('img');
			hintButton.className = 'hintButton';
			hintButton.src = 'res/hint.png';
			hintButton.onmouseenter = function(e){
				form.showTooltip(hintText);
			}
			hintButton.onmouseleave = function(e){
				form.hideTooltip();
			}
			button.appendChild(hintButton);
		}
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

function createCheckList(form, name, titleText, selectAllText='Select all', hintText){

	let checkListContainer = document.createElement('div');
	checkListContainer.className = 'formrow checklist';

	let text = document.createElement('div');
	text.innerText = titleText;
	text.className = 'formlbl';

	let hint = document.createElement('span');

	checkListContainer.setHint = function(text){
		let hintButton = document.createElement('img');
		hintButton.className = 'hintButton';
		hintButton.src = 'res/hint.png';
		hintButton.onmouseenter = function(e){
			form.showTooltip(text);
		}
		hintButton.onmouseleave = function(e){
			form.hideTooltip();
		}
		hint.appendChild(hintButton);
	}

	if(hintText) {
		checkListContainer.setHint(hintText);
	}

	text.appendChild(hint);

	checkListContainer.appendChild(text);

	let selectAll = document.createElement('label');
	selectAll.className = 'checkItem';
	let selectAllCheckBox = document.createElement('input');
	selectAllCheckBox.style.display = 'none';
	selectAll.checkBox = selectAllCheckBox;
	selectAllCheckBox.type = 'checkbox';
	selectAll.appendChild(selectAllCheckBox);
	let offImg = document.createElement('img');
	offImg.src = 'res/checkbox_off.png';
	offImg.className = 'checkboxOff';
	selectAll.appendChild(offImg);
	let onImg = document.createElement('img');
	onImg.src = 'res/checkbox_on.png';
	onImg.className = 'checkboxOn';
	selectAll.appendChild(onImg);
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
				cb.classList.add('selected');
			} else {
				cb.classList.remove('selected');
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
		if(allChecked){
			selectAll.classList.add('selected');
		} else {
			selectAll.classList.remove('selected');
		}
	}

	selectAll.oninput = function(e){
		for(let cb of checkBoxes){
			cb.checkBox.checked = selectAllCheckBox.checked;
		}
		if(selectAllCheckBox.checked){
			selectAll.classList.add('selected');
		} else {
			selectAll.classList.remove('selected');
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
		checkBoxContainer.className = 'checkItem';
		checkList.items[name] = checkBoxContainer;
		let checkBox = document.createElement('input');
		checkBox.type = 'checkbox';
		checkBox.style.display = 'none';
		checkBoxContainer.value = name;
		checkBoxContainer.checkBox = checkBox;
		checkBoxContainer.appendChild(checkBox);
		let offImg = document.createElement('img');
		offImg.src = 'res/checkbox_off.png';
		offImg.className = 'checkboxOff';
		checkBoxContainer.appendChild(offImg);
		let onImg = document.createElement('img');
		onImg.src = 'res/checkbox_on.png';
		onImg.className = 'checkboxOn';
		checkBoxContainer.appendChild(onImg);
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

	checkListContainer.checkList = checkList;
	checkListContainer.appendChild(checkList);

	form.inputs[name] = checkListContainer;

	return checkListContainer;

}

function createNumberInput(form, name, labelText, placeholder, min, max, def, hintText){

	let label = document.createElement('div');
	label.className = 'formrow';

	let text = document.createElement('span');
	text.className = 'formlbl';
	text.innerText = labelText;

	let hint = document.createElement('span');

	label.setHint = function(text){
		let hintButton = document.createElement('img');
		hintButton.className = 'hintButton';
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

	if (hintText) {
		label.setHint(hintText);
	}

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

	label.setValue = function(value){
		input.value = value;
	}

	label.setMin = function(min){
		input.min = min;
	}

	label.setMax = function(max){
		input.max = max;
	}

	label.appendChild(text);
	label.appendChild(hint);
	label.appendChild(input);

	form.inputs[name] = input;

	return label;

}

function createDateInput(form, name, labelText, hintText){

	let label = document.createElement('div');
	label.className = 'formrow';

	let text = document.createElement('span');
	text.innerText = labelText;
	text.className = 'formlbl';

	let hint = document.createElement('span');

	label.setHint = function(text){
		let hintButton = document.createElement('img');
		hintButton.className = 'hintButton';
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

	if (hintText) {
		label.setHint(hintText);
	}

	let input = document.createElement('input');
	input.type = 'date';
	input.className = 'formin';
	input.oninput = function(e){
		form.update(name, input.value);
	}

	label.appendChild(text);
	label.appendChild(hint);
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

	let hint = document.createElement('span');

	label.setHint = function(text){
		let hintButton = document.createElement('img');
		hintButton.src = 'res/hint.png';
		hintButton.className = 'hintButton';
		hintButton.onmouseenter = function(e){
			form.showTooltip(text);
		}
		hintButton.onmouseleave = function(e){
			form.hideTooltip();
		}
		hint.appendChild(hintButton);
	}

	let input = document.createElement('input');
	input.type = 'text';
	input.className = 'formin';
	if(placeholder) input.placeholder = placeholder;
	input.oninput = function(e){
		form.update(name, input.value);
	}
	label.appendChild(text);
	label.appendChild(hint);
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
		hintButton.className = 'hintButton';
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
