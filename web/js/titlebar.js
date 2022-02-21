/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */



document.addEventListener('mouseup', function(e){
	if(typeof popupMenu === 'undefined') return; // menu does not exist when not signed in
	if(typeof popupMenuBtn === 'undefined') return; // menu does not exist when not signed in
	let mx = e.clientX;
	let my = e.clientY;
	let bx = popupMenu.offsetLeft;
	let by = popupMenu.offsetTop;
	let bw = popupMenu.offsetWidth;
	let bh = popupMenu.offsetHeight;
	let bbx = popupMenuBtn.offsetLeft;
	let bby = popupMenuBtn.offsetTop;
	let bbw = popupMenuBtn.offsetWidth;
	let bbh = popupMenuBtn.offsetHeight;
	if((mx<bx || mx>bx+bw || my<by || my>by+bh) && (mx<bbx || mx>bbx+bbw || my<bby || my>bby+bbh)){
		popupMenu.style.display = 'none';
	}
});

document.addEventListener('keydown', function(e){
	if(e.key=='Escape'){
		popupMenu.style.display = 'none';
	}
});

function togglePopupMenu(){
	if(popupMenu.style.display=='block') {
		popupMenu.style.display = 'none';
	} else {
		popupMenu.style.display = 'block';
	}
}

function initTitlebar(){
	let usernameText = document.querySelector('#usernameText');
	if(usernameText){
		let text = usernameText.innerText;
		let btnWidth = usernameBtn.offsetWidth;
		if(usernameText.offsetWidth<=btnWidth) return;
		usernameText.innerText = '';
		let i = 0;
		while(usernameText.offsetWidth<btnWidth){
			usernameText.innerText = text.substr(0, i)+'...';
			i++;
		}
	}
}
