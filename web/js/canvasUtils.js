
let images = {};

async function getImage(imgSrc){

	if(!images[imgSrc]){

		let img = new Image();
		img.src = imgSrc;

		images[imgSrc] = img;

		return new Promise(function(resolve){
			img.onload = function(){
				resolve(img);
			}
			img.onerror = function(){
				resolve(null);
			}
		});

	}

	return images[imgSrc];

}

function getTextHeight(ctx, text, fontSize, x, y, width){

	let lineNum = 0;

	ctx.font = ((fontSize.length!=null)?(fontSize[1]+' '+fontSize[0]):fontSize)+'px Arial'
	let generalMetrics = ctx.measureText(' ');

	let lineHeight = (fontSize.length!=null)?fontSize[0]:fontSize;
	let lines = text.split('\n');

	for(let line of lines){

		let words = line.split(' ');
		let lineWidth = 0;

		for(let word of words){

			let metrics = ctx.measureText(word);

			if(lineWidth+metrics.width>width){
				lineWidth = 0;
				lineNum++;
			}

			lineWidth += metrics.width+generalMetrics.width;

		}

		lineNum++;

	}

	return lineNum*lineHeight;

}

function drawWrappedText(ctx, text, fontSize, x, y, width){

	let lineNum = 0;

	ctx.font = ((fontSize.length!=null)?(fontSize[1]+' '+fontSize[0]):fontSize)+'px Arial'
	let generalMetrics = ctx.measureText(' ');

	let lineHeight = (fontSize.length!=null)?fontSize[0]:fontSize;
	let lines = text.split('\n');

	for(let line of lines){

		let words = line.split(' ');
		let lineWidth = 0;

		for(let word of words){

			let metrics = ctx.measureText(word);

			if(lineWidth+metrics.width>width){
				lineWidth = 0;
				lineNum++;
			}

			ctx.fillText(word, x+lineWidth, y+lineNum*lineHeight+lineHeight);

			lineWidth += metrics.width+generalMetrics.width;

		}

		lineNum++;

	}

	return lineNum*lineHeight;

}
