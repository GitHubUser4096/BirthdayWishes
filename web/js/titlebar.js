
let menuWidth;

document.addEventListener('mouseup', function(e){
	if(typeof usermenu === 'undefined') return;
	let mx = e.clientX;
	let my = e.clientY;
	let bx = usermenu.offsetLeft;
	let by = usermenu.offsetTop;
	let bw = usermenu.offsetWidth;
	let bh = usermenu.offsetHeight;
	if(mx<bx || mx>bx+bw || my<by || my>by+bh){
		usermenu.style.display = 'none';
	}
});

window.addEventListener('load', function(e){
	menuWidth = menu.clientWidth;
	if(window.innerWidth-titlea.clientWidth<menuWidth||window.innerWidth<=600){
		menu.style.display = "none";
		hamburger.style.display = "block";
	} else {
		menu.style.display = "block";
		hamburger.style.display = "none";
	}
});

window.addEventListener('resize', function(e){
	if(window.innerWidth-titlea.clientWidth<menuWidth||window.innerWidth<=600){
		menu.style.display = "none";
		hamburger.style.display = "block";
	} else {
		menu.style.display = "block";
		hamburger.style.display = "none";
	}
});

function toggleUsermenu(){
	if(usermenu.style.display=='block') {
		usermenu.style.display = 'none';
	} else {
		usermenu.style.display = 'block';
		usermenu.style.right = (document.body.offsetWidth-userBtn.offsetLeft-userBtn.offsetWidth)+"px";
	}
}

function toggleMobileMenu(){
	if(mobilemenu.style.display=='block') {
		mobilemenu.style.display = 'none';
	} else {
		mobilemenu.style.display = 'block';
	}
}
