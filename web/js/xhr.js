/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */

const NONE = 0;
const NO_POST = 1;
const ALL = 2;

const XHR_DEBUG = NONE;

function get(request, handler, errorHandler){
	return new Promise(resolve=>{

		let xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(){
			if(this.readyState==4){
				if(this.status==200) {
					if(XHR_DEBUG) console.log('GET:', request, this.responseText);
					if(handler) handler(this.responseText);
					resolve(this.responseText);
				} else if(this.status>=400) {
					if(XHR_DEBUG) console.error('GET:', request, this.status, this.responseText);
					if(errorHandler) errorHandler(this.status, this.responseText);
					resolve({status:this.status,message:this.responseText});
				}
			}
		}
		xhr.open('GET', request, true);
		xhr.send();

	});
}

function post(request, data, handler, errorHandler){
	return new Promise(resolve=>{

		let xhr = new XMLHttpRequest();
		xhr.onreadystatechange = function(){
			if(this.readyState==4){
				if(this.status==200){
					if(XHR_DEBUG==NO_POST) console.log('POST:', request, this.responseText);
					else if(XHR_DEBUG==ALL) console.log('POST:', request, data, this.responseText);
					if(handler) handler(this.responseText);
					resolve(this.responseText);
				} else if(this.status>=400){
					if(XHR_DEBUG==NO_POST) console.error('POST:', request, this.status, this.responseText);
					else if(XHR_DEBUG==ALL) console.error('POST:', request, data, this.status, this.responseText);
					if(errorHandler) errorHandler(this.status, this.responseText);
					resolve({status:this.status,message:this.responseText});
				}
			}
		}
		xhr.open('POST', request, true);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.send(data);

	});
}
