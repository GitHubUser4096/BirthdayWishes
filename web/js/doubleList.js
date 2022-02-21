/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

function createDoubleList(form, name, title){

	let listBox = document.createElement('div');
	listBox.className = "formrow doubleListBox";

	let selectedBox = document.createElement('div');
	let deselectedBox = document.createElement('div');

	// selectedBox.style.minHeight = '200px';
	// deselectedBox.style.height = '200px';

	let dragging = document.createElement('div');

	listBox.getSelected = function(){

		let res = [];

		for(let field of selectedBox.children){
			res[res.length] = field.children[0].children[1].name;
		}

		return res;

	}

	listBox.setSelectedItems = function(items){

		// for(let item of deselectedBox.children){
		// for(let i = 0; i<deselectedBox.children.length; i++){
		// 	let item = deselectedBox.children[i];
		// 	let innerItem = item.children[0];
		// 	if(items.indexOf(innerItem.name.toString())>=0){
		// 		innerItem.children[0].checked = true;
		// 		deselectedBox.removeChild(item);
		// 		selectedBox.appendChild(createMoveField(innerItem));
		// 		innerItem.classList.add('selected');
		// 		i--;
		// 	}
		// }

		for(let id of items){
			for(let field of deselectedBox.children){
				let innerItem = field.children[0];
				if(innerItem.name.toString()==id){
					innerItem.children[0].checked = true;
					deselectedBox.removeChild(field);
					selectedBox.appendChild(createMoveField(innerItem));
					innerItem.classList.add('selected');
				}
			}
		}

	}

	function updateForm(){

		let res = listBox.getSelected();

		form.update(name, res.join(','));

	}

	document.addEventListener('mousemove', pointermove);
	document.addEventListener('touchmove', pointermove);

	function pointermove(e){
		if(dragging.moveItem){
			dragging.style.top = (e.clientY || e.touches[0]?.clientY)+'px';
			let dragBounds = dragging.getBoundingClientRect();
			for(let child of selectedBox.children){
				let bounds = child.getBoundingClientRect();
				if(dragBounds.top+dragBounds.height/2>bounds.top && dragBounds.top+dragBounds.height/2<bounds.bottom){
					//child.style.background = 'yellow';
					child.classList.add('selected');
				} else {
					//child.style.background = 'gray';
					child.classList.remove('selected');
				}
			}
		}
	}

	document.addEventListener('mouseup', pointerup);
	document.addEventListener('touchend', pointerup);

	function pointerup(e){
		if(dragging.moveItem){

			let fields = Array.from(selectedBox.children);

			let moved = false;
			let from = fields.indexOf(dragging.moveItem.moveField);
			let to = -1;

			let dragBounds = dragging.getBoundingClientRect();
			for(let field of fields){
				let bounds = field.getBoundingClientRect();
				if(dragBounds.top+dragBounds.height/2>bounds.top && dragBounds.top+dragBounds.height/2<bounds.bottom){
					moved = true;
					to = fields.indexOf(field);
				}
				//field.style.background = 'gray';
				field.classList.remove('selected');
			}

			if(moved && to>=0 && from!=to){

				if(to>from){ // move down - push UP

					for(let i=from; i<to; i++){
						let item = fields[i+1].children[0];
						fields[i+1].removeChild(item);
						fields[i].appendChild(item);
						item.moveField = fields[i];
					}

					dragging.removeChild(dragging.moveItem);
					fields[to].appendChild(dragging.moveItem);
					dragging.moveItem.moveField = fields[to];
					dragging.moveItem = null;

				} else if(to<from) { // move up - push DOWN

					for(i=from; i>to; i--){
						let item = fields[i-1].children[0];
						fields[i-1].removeChild(item);
						fields[i].appendChild(item);
						item.moveField = fields[i];
					}

					dragging.removeChild(dragging.moveItem);
					fields[to].appendChild(dragging.moveItem);
					dragging.moveItem.moveField = fields[to];
					dragging.moveItem = null;

				}

				updateForm();

			} else {
				dragging.removeChild(dragging.moveItem);
				dragging.moveItem.moveField.appendChild(dragging.moveItem);
				dragging.moveItem = null;
			}

			dragging.style.display = 'none';

		}
	}

	dragging.style.position = 'fixed';
	dragging.style.overflow = 'hidden';

	function createMoveField(item){

		let moveField = document.createElement('div');

		moveField.classList.add('moveField');

		/*moveField.style.height = '30px';
		moveField.style.background = 'gray';
		moveField.style.overflow = 'hidden';*/

		let moveItem = document.createElement('div');
		moveItem.moveField = moveField;
		moveItem.style.userSelect = 'none';
		moveItem.style.lineHeight = '30px';

		let mover = document.createElement('span');
		mover.style.fontSize = '24px';
		mover.innerHTML = '=';
		mover.style.cursor = 'n-resize';
		mover.onmousedown = function(e){
			moveItem.moveField.removeChild(moveItem);
			dragging.appendChild(moveItem);
			dragging.moveItem = moveItem;
			dragging.style.width = moveField.offsetWidth+'px';
			dragging.style.height = moveField.offsetHeight+'px';
			dragging.style.display = 'block';
		}
		moveItem.ontouchstart = function(e){
			moveItem.moveField.removeChild(moveItem);
			dragging.appendChild(moveItem);
			dragging.moveItem = moveItem;
			dragging.style.width = moveField.offsetWidth+'px';
			dragging.style.height = moveField.offsetHeight+'px';
			dragging.style.display = 'block';
		}

		moveItem.style.touchAction = 'none';

		moveItem.appendChild(mover);
		moveItem.appendChild(item);

		moveField.appendChild(moveItem);

		return moveField;

	}

	let label1 = document.createElement('div');
	label1.innerText = 'Vybrané:';
	label1.className = 'formlbl';

	let hint1 = document.createElement('span');

	listBox.setSelectedHint = function(text){
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
		hint1.appendChild(hintButton);
	}

	label1.appendChild(hint1);

	listBox.appendChild(label1);
	listBox.appendChild(selectedBox);
	listBox.appendChild(document.createElement('hr'));

	let label2 = document.createElement('div');
	label2.innerText = 'Na výběr:';
	label2.className = 'formlbl';

	let hint2 = document.createElement('span');

	listBox.setToSelectHint = function(text){
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
		hint2.appendChild(hintButton);
	}

	label2.appendChild(hint2);

	listBox.appendChild(label2);
	listBox.appendChild(deselectedBox);
	listBox.appendChild(dragging);

	listBox.clearItems = function(){

		selectedBox.innerHTML = '';
		deselectedBox.innerHTML = '';

		/*for(let child of selectedBox.children){
			selectedBox.removeChild(child);
		}

		for(let child of deselectedBox.children){
			deselectedBox.removeChild(child);
		}*/

	}

	listBox.addItem = function(name, label){

		let item = document.createElement('label');
		item.className = 'doubleListItem';
		//item.style.display = 'block';
		let checkBox = document.createElement('input');
		checkBox.type = 'checkbox';
		checkBox.style.display = 'none';
		item.appendChild(checkBox);
		let selectImg = document.createElement('img');
		selectImg.src = 'res/select.png';
		selectImg.className = 'selectItem';
		selectImg.onmouseenter = function(e){
			form.showTooltip('Vybrat');
		}
		selectImg.onmouseleave = function(e){
			form.hideTooltip();
		}
		selectImg.onclick = function(e){
			form.hideTooltip();
		}
		item.appendChild(selectImg);
		let deselectImg = document.createElement('img');
		deselectImg.src = 'res/deselect.png';
		deselectImg.className = 'deselectItem';
		deselectImg.onmouseenter = function(e){
			form.showTooltip('Zrušit výběr');
		}
		deselectImg.onmouseleave = function(e){
			form.hideTooltip();
		}
		deselectImg.onclick = function(e){
			form.hideTooltip();
		}
		item.appendChild(deselectImg);
		item.appendChild(document.createTextNode(label));
		item.name = name;

		item.oninput = function(){
			if(checkBox.checked){
				deselectedBox.removeChild(item.parentElement);
				selectedBox.appendChild(createMoveField(item));
				item.classList.add('selected');
			} else {
				selectedBox.removeChild(item.parentElement.parentElement);
				let holder = document.createElement('div');
				holder.appendChild(item);
				deselectedBox.appendChild(holder);
				item.classList.remove('selected');
			}
			updateForm();
			if(checkBox.checked && listBox.onSelect) listBox.onSelect(name);
		}

		let holder = document.createElement('div');
		holder.appendChild(item);

		deselectedBox.appendChild(holder);

	}

	return listBox;

}
