/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

function createBagList(form, name, label, nextTooltip){

	let listBox = document.createElement('div');

	let list = document.createElement('div');

	let dragging = document.createElement('div');
	dragging.style.position = 'fixed';
  dragging.style.overflow = 'hidden';

	function updateForm(){

		let res = [];

		for(let field of list.children){
			res[res.length] = field.item.name;
		}

		form.update(name, res.join(','));

	}

	document.addEventListener('mousemove', pointermove);
	document.addEventListener('touchmove', pointermove);

	function pointermove(e){
		if(dragging.item){
			// dragging.style.top = e.clientY+'px';
			dragging.style.top = (e.clientY || e.touches[0]?.clientY)+'px';
			let dragBounds = dragging.getBoundingClientRect();
			for(let child of list.children){
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
	};

	document.addEventListener('mouseup', pointerup);
	document.addEventListener('touchend', pointerup);

	function pointerup(e){
		if(dragging.item){

			let fields = Array.from(list.children);

			let moved = false;
			let from = fields.indexOf(dragging.item.holder);
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
						item.holder = fields[i];
						fields[i].item = item;
					}

					dragging.removeChild(dragging.item);
					fields[to].appendChild(dragging.item);
					dragging.item.holder = fields[to];
					fields[to].item = dragging.item;
					dragging.item = null;

				} else if(to<from) { // move up - push DOWN

					for(i=from; i>to; i--){
						let item = fields[i-1].children[0];
						fields[i-1].removeChild(item);
						fields[i].appendChild(item);
						item.holder = fields[i];
						fields[i].item = item;
					}

					dragging.removeChild(dragging.item);
					fields[to].appendChild(dragging.item);
					dragging.item.holder = fields[to];
					fields[to].item = dragging.item;
					dragging.item = null;

				}

				updateForm();

			} else {
				dragging.removeChild(dragging.item);
				dragging.item.holder.appendChild(dragging.item);
				dragging.item = null;
			}

			dragging.style.display = 'none';

		}
	};

	function createItem(bag){

		let index = Math.floor(Math.random()*bag.length);
		let item = bag[index];
		bag.splice(index, 1);

		let field = document.createElement('div');
		field.style.userSelect = 'none';
		field.style.touchAction = 'none';
    field.style.lineHeight = '30px';
		let mover = document.createElement('span');
		mover.style.fontSize = '24px';
		mover.innerHTML = '=';
		mover.style.cursor = 'n-resize';
		mover.onmousedown = function(e){
			field.holder.removeChild(field);
			dragging.appendChild(field);
			dragging.item = field;
      dragging.style.width = field.holder.offsetWidth+'px';
			dragging.style.height = field.holder.offsetHeight+'px';
			dragging.style.display = 'block';
		}
		field.ontouchstart = function(e){
			field.holder.removeChild(field);
			dragging.appendChild(field);
			dragging.item = field;
      dragging.style.width = field.holder.offsetWidth+'px';
			dragging.style.height = field.holder.offsetHeight+'px';
			dragging.style.display = 'block';
		}
		field.appendChild(mover);
		field.name = item.name;
		let newbtn = document.createElement('img');
		newbtn.src = 'res/random.png';
		newbtn.onmouseenter = function(e){
			form.showTooltip(nextTooltip);
		}
		newbtn.onmouseleave = function(e){
			form.hideTooltip();
		}
		newbtn.style.marginBottom = '-5px';
		newbtn.style.marginLeft = '5px';
		newbtn.style.marginRight = '5px';
		//newbtn.innerText = '?';
		newbtn.onclick = function(){
			if(bag.length>0){
				//list.appendChild(createItem(bag));
				//list.insertBefore(createItem(bag), field);
				//list.removeChild(field);
				let newField = createItem(bag);
				let holder = field.holder;
				newField.holder = holder;
				holder.removeChild(field);
				holder.appendChild(newField);
				holder.item = newField;
				updateForm();
				form.hideTooltip();
			}
		}
		field.appendChild(newbtn);
		field.appendChild(document.createTextNode(item.label));

		return field;

	}

	listBox.set = function(bag, showCount){

		/*for(let child of list.children){
			list.removeChild(child);
		}*/
		list.innerHTML = '';

		let count = Math.min(showCount, bag.length);

		for(let i=0; i<count; i++){

			let holder = document.createElement('div');
			/*holder.style.height = '40px';
			holder.style.overflow = 'hidden';
			holder.style.background = 'gray';*/
			holder.classList.add('moveField');
			let item = createItem(bag);
			item.holder = holder;
			holder.appendChild(item);
			holder.item = item;

			list.appendChild(holder);

		}

		updateForm();

	}

	listBox.appendChild(list);
	listBox.appendChild(dragging);

	return listBox;

}
