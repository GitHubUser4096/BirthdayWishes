/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

function createDoubleList(form, name, title){
	
	let listBox = document.createElement('div');
	
	let selectedBox = document.createElement('div');
	let deselectedBox = document.createElement('div');
	
	selectedBox.style.minHeight = '200px';
	deselectedBox.style.height = '200px';
	
	let dragging = document.createElement('div');
	
	function updateForm(){
		
		let res = [];
		
		for(let field of selectedBox.children){
			res[res.length] = field.children[0].children[1].name;
		}
		
		form.update(name, res.join(','));
		
	}
	
	document.addEventListener('mousemove', function(e){
		if(dragging.moveItem){
			dragging.style.top = e.clientY+'px';
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
	});
	
	document.addEventListener('mouseup', function(e){
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
			
		}
	});
	
	dragging.style.position = 'fixed';
	
	function createMoveField(item){
		
		let moveField = document.createElement('div');
		
		moveField.classList.add('moveField');
		
		/*moveField.style.height = '30px';
		moveField.style.background = 'gray';
		moveField.style.overflow = 'hidden';*/
		
		let moveItem = document.createElement('div');
		moveItem.moveField = moveField;
		moveItem.style.userSelect = 'none';
		
		let mover = document.createElement('span');
		mover.style.fontSize = '24px';
		mover.innerHTML = '=';
		mover.style.cursor = 'n-resize';
		mover.onmousedown = function(e){
			moveItem.moveField.removeChild(moveItem);
			dragging.appendChild(moveItem);
			dragging.moveItem = moveItem;
		}
		
		moveItem.appendChild(mover);
		moveItem.appendChild(item);
		
		moveField.appendChild(moveItem);
		
		return moveField;
		
	}
	
	listBox.appendChild(document.createTextNode('Vybrané:'));
	listBox.appendChild(selectedBox);
	listBox.appendChild(document.createElement('hr'));
	listBox.appendChild(document.createTextNode('Na výběr:'));
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
		//item.style.display = 'block';
		let checkBox = document.createElement('input');
		checkBox.type = 'checkbox';
		item.appendChild(checkBox);
		item.appendChild(document.createTextNode(label));
		item.name = name;
		
		item.oninput = function(){
			if(checkBox.checked){
				deselectedBox.removeChild(item.parentElement);
				selectedBox.appendChild(createMoveField(item));
			} else {
				selectedBox.removeChild(item.parentElement.parentElement);
				let holder = document.createElement('div');
				holder.appendChild(item);
				deselectedBox.appendChild(holder);
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
