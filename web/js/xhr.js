/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

XHR_DEBUG = true;

function get(request, handler){
	let xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(){
		if(this.readyState==4 && this.status==200){
			if(XHR_DEBUG) console.log('GET:', request, this.responseText);
			handler(this.responseText);
		}
	}
	xhr.open('GET', request, true);
	xhr.send();
}

function post(request, data, handler){
	let xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function(){
		if(this.readyState==4 && this.status==200){
			if(XHR_DEBUG) console.log('POST:', request, data, this.responseText);
			handler(this.responseText);
		}
	}
	xhr.open('POST', request, true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.send(data);
}
