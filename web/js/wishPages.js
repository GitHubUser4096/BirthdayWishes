
let usedPages = [];
let freePages = [];
let pageScale = 100;

function returnPages(){
	freePages = freePages.concat(usedPages);
	usedPages = [];
}

function newPage(){

	if(freePages.length>0){
		let page = freePages[0];
		freePages.splice(0, 1);
		usedPages[usedPages.length] = page;
		return page;
	}

	let canvas = document.createElement('canvas');
	// canvas.scale = pageScale;
	canvas.style.width = 'calc('+pageScale+'% - 20px)';

	canvas.className = 'previewCanvas';

	// A4 at 96 DPI (calculated using https://www.a4-size.com/a4-size-in-pixels)
	canvas.width = 794;
	canvas.height = 1123;

	let ctx = canvas.getContext('2d');
	canvas.ctx = ctx;

	usedPages[usedPages.length] = canvas;

	return canvas;

}
