<?php
/*
 * Dynamický formulář pro vytvoření přání s náhledem přání
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
}

?>
<!doctype html>
<html>

	<head>

		<title>Vytvořit přání</title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="icon" href="res/cake.png">
		
		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/controls.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<link rel="stylesheet" href="css/form_page.css">
		<link rel="stylesheet" href="css/form.css">
		<link rel="stylesheet" href="css/wish.css">
		
		<script src="js/titlebar.js"></script>
		<script src="js/xhr.js"></script>
		<script src="js/form.js"></script>
		<script src="js/doubleList.js"></script>
		<script src="js/bagList.js"></script>
		
		<!--script src="https://github.com/devongovett/pdfkit/releases/download/v0.10.0/pdfkit.standalone.js"></script-->
		<!--script src="https://github.com/devongovett/blob-stream/releases/download/v0.1.3/blob-stream.js"></script-->
		
		<style>
			
			.previewbox {
				background: gray;
				position: relative;
				/*overflow: hidden;*/
				height: 100%;
			}
			
			.preview {
				
			}
			
			#previewEmbed {
				width: 100%;
				height: 100%;
			}
			
			.previewFrame {
				width: 100%;
				height: 100%;
			}
			
			.previewCanvas {
				margin: 10px;
				width: calc(100% - 20px);
			}
			
			@media only screen and (max-width: 600px) {
				
				.pageBody {
					width: 100%;
					height: calc(50% - 40px);
				}
				
				.pageControls {
					bottom: 50%;
					height: 40px;
				}
				
				.previewbox {
					position: absolute;
					top: 50%;
					height: 50%;
				}
				
			}
			
		</style>
		
		<script>
			
			function esc(str){
				
				let map = {
					'&': '&amp;',
					'<': '&lt;',
					'>': '&gt;',
					'"': '&quot;',
					"'": '&apos;',
					"`": '&#96;',
					'\\': '&#92;',
					//' ': '&nbsp',
					'\n': '<br>',
				};
				
				let res = str;
				
				for(ch in map){
					res = res.replaceAll(ch, map[ch]);
				}
				
				return res;
				
			}
			
			function deesc(str){
				
				let map = {
					'&amp;': '&',
					'&lt;': '<',
					'&gt;': '>',
					'&quot': '"',
					'&apos;': "'",
					'&#96;': "`",
					'&#92;': '\\',
				};
				
				let res = str;
				
				for(ch in map){
					res = res.replaceAll(ch, map[ch]);
				}
				
				return res;
				
			}
			
			function getSearchObj(){
				let txt = location.search;
				if(txt.startsWith('?')) txt = txt.substring(1);
				let entries = txt.split('&');
				let obj = [];
				for(let entry of entries){
					let parts = entry.split('=');
					obj[parts[0]] = parts[1];
				}
				return obj;
			}
			
			function setSearchText(search){
				history.replaceState(null, '', location.origin+location.pathname+'?'+search);
			}
			
			let loc = location.href.substring(0, location.href.lastIndexOf('/'));
			
			let wish = {};
			let infoCache = {};
			let highlight = null;
			
			async function getInfo(id){
				
				if(infoCache[id]) return infoCache[id];
				
				let info = await get(loc+'/get/info.php?id='+encodeURIComponent(id));
				
				let json = JSON.parse(info);
				for(let row of json){
					infoCache[row.id] = row;
					return row;
				}
				
				return null;
				
			}
			
			let usedPages = [];
			let freePages = [];
			
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
				
				canvas.className = 'previewCanvas';
				
				// A4 at 96 DPI (calculated using https://www.a4-size.com/a4-size-in-pixels)
				canvas.width = 794;
				canvas.height = 1123;
				
				let ctx = canvas.getContext('2d');
				canvas.ctx = ctx;
				
				usedPages[usedPages.length] = canvas;
				
				return canvas;
				
			}
			
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
			
			function getTextHeight(ctx, text, x, y, width){
				
				let lineNum = 0;
				let generalMetrics = ctx.measureText(' ');
				let lineHeight = generalMetrics.fontBoundingBoxAscent+generalMetrics.fontBoundingBoxDescent;
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
			
			function drawWrappedText(ctx, text, x, y, width){
				
				let lineNum = 0;
				let generalMetrics = ctx.measureText(' ');
				let lineHeight = generalMetrics.fontBoundingBoxAscent+generalMetrics.fontBoundingBoxDescent;
				let lineAscent = generalMetrics.fontBoundingBoxAscent;
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
						
						ctx.fillText(word, x+lineWidth, y+lineNum*lineHeight+lineAscent);
						
						lineWidth += metrics.width+generalMetrics.width;
						
					}
					
					lineNum++;
					
				}
				
				return lineNum*lineHeight;
				
			}
			
			let updating = false;
			let links = [];
			
			async function updateWish(){
				
				if(updating) return;
				
				updating = true;
				
				let wish_for = wish['for']||'Milá Alice';
				let wish_from = wish['from']||'Bob';
				let bday = wish['bday']||'42';
				// let wishText = esc(wish['wishText']??'Všechno nejlepší!');
				let wishText = wish['wishText']||'Všechno nejlepší!';
				
				/*let doc = new PDFDocument();
				let stream = doc.pipe(blobStream());
				
				doc.font('fonts/OpenSans-Regular.ttf');
				doc.fontSize(25);
				doc.text(wish_for+', '+wish_from+' ti přeje všechno nejlepší k '+bday+'. narozeninám!', 20, 20);
				
				*//*doc.image('res/cake.png', {
					fit: [100, 100],
					align: 'center',
					valign: 'center',
				});*//*
				
				doc.end();
				stream.on('finish', function(){
					let url = stream.toBlobURL('application/pdf');
					previewEmbed.src = url;
				});
				
				return;*/
				
				/*let html = document.createElement('div');
				
				let canvas1 = document.createElement('canvas');
				canvas1.width = 210;
				canvas1.height = 297;
				
				html.appendChild(canvas1);
				
				preview.appendChild(html);
				
				return;*/
				
				//previewEmbed.src = "pdftest.php?wish_for="+encodeURIComponent(wish_for)+"&wish_from="+encodeURIComponent(wish_from);
				
				returnPages();
				previewBox.innerHTML = '';
				links = [];
				
				let page1Wrapper = document.createElement('a');
				let page1 = newPage();
				
				links[links.length] = '';
				
				page1.ctx.fillStyle = '#f3eee3';
				page1.ctx.fillRect(0, 0, page1.width, page1.height);
				
				page1.ctx.fillStyle = "black";
				// page1.ctx.fillText(wish_from+' ti přeje všechno nejlepší k '+bday+'. narozeninám!', 20, 340);
				let textPos = 296;
				if(wish.textMode=='auto'){
					page1.ctx.font = "60px Calibri";
					page1.ctx.fillText(wish_for+',', 290, 200);
					page1.ctx.font = "40px Calibri";
					textPos += drawWrappedText(page1.ctx, wish_from+' ti přeje všechno nejlepší k '+bday+'. narozeninám!', 20, textPos, page1.width-40)+20;
					// previewHTML += '		<div class="wish_for">'+wish_for+',</div>';
					// previewHTML += '		<div class="wish_text">'+wish_from+' ti přeje všechno nejlepší k <b>'+bday+'.</b> narozeninám!</div>';
				} else if(wish.textMode=='custom'){
					// previewHTML += '		<div class="wish_text">'+wishText+'</div>';
					page1.ctx.font = "40px Calibri";
					textPos += drawWrappedText(page1.ctx, wishText, 20, textPos, page1.width-40)+20;
				}
				
				page1.ctx.font = "30px Calibri";
				textPos += drawWrappedText(page1.ctx, 'Na dalších stranách najdeš zajímavosti k číslu tvých narozenin!', 20, textPos, page1.width-40);
				
				// drawImage(page1.ctx, 'res/cake256.png', 20, 20);
				page1.ctx.drawImage(await getImage('res/cake256.png'), 20, 20);
				
				page1Wrapper.appendChild(page1);
				previewBox.appendChild(page1Wrapper);
				
				let infos = [];
				if(wish.infoMode=='list' && wish.infoList){
					infos = wish.infoList.split(',');
				} else if(wish.infoMode=='random' && wish.randomInfoList){
					infos = wish.randomInfoList.split(',');
				}
				
				for(let infoId of infos){
					let info = await getInfo(infoId);
					if(info){
						
						let background = esc(info.background?info.background:'white');
						let color = esc(info.color?info.color:'black');
						
						// previewHTML += '<div id="page_'+info.id+'" class="wish_page" style="width:'+width+'px;height:'+height+'px;background:'+background+'">';
						// previewHTML += '	<div class="info_text" style="color:'+color+'">'+info.content+'</div>';
						// previewHTML += '	<div><a target="_blank" class="info_link" href="'+info.link+'" style="color:'+color+'">'+info.link+'</a></div>';
						// previewHTML += '	<img class="info_img" src="'+info.imgSrc+'"></img>';
						// previewHTML += '	<div class="attribution" style="color:'+color+'">'+info.imgAttrib+'</div>';
						// previewHTML += '</div>';
						
						let pageWrapper = document.createElement('a');
						if(info.link) pageWrapper.href = info.link;
						pageWrapper.target = '_blank';
						links[links.length] = info.link;
						let page = newPage();
						
						page.ctx.fillStyle = background;
						page.ctx.fillRect(0, 0, page.width, page.height);
						
						page.ctx.fillStyle = color;
						page.ctx.font = "32px Calibri";
						let textPos = 20;
						textPos += drawWrappedText(page.ctx, deesc(info.content), 20, textPos, page.width-40)+20;
						page.ctx.fillStyle = "blue";
						page.ctx.font = "28px Calibri";
						textPos += drawWrappedText(page.ctx, info.link, 20, textPos, page.width-40)+20;
						
						let img = await getImage(info.imgSrc);
						
						if(img){
							
							let imgRatio = img.width/img.height;
							let fullWidthHeight = (page.width-40)/imgRatio;
							let attribHeight = getTextHeight(page.ctx, info.imgAttrib, 20, textPos, page.width-40);
							
							page.ctx.fillStyle = color;
							page.ctx.font = "italic 22px Calibri";
							
							if(textPos+fullWidthHeight+20+attribHeight+20>page.height){
								
								let imgHeight = page.height-textPos-20-attribHeight-20;
								let imgWidth = imgHeight*imgRatio;
								let imgPos = page.width/2-imgWidth/2;
								
								page.ctx.drawImage(img, imgPos, textPos, imgWidth, imgHeight);
								textPos += imgHeight+20;
								drawWrappedText(page.ctx, info.imgAttrib, 20, textPos, page.width-40);
								
							} else {
								page.ctx.drawImage(img, 20, textPos, page.width-40, fullWidthHeight);
								textPos += fullWidthHeight+20;
								drawWrappedText(page.ctx, info.imgAttrib, 20, textPos, page.width-40);
							}
							
						}
						
						// drawImage(page.ctx, info.imgSrc, 20, textPos);
						// page.ctx.drawImage(img, 20, textPos);
						
						pageWrapper.appendChild(page);
						previewBox.appendChild(pageWrapper);
						
					} else {
						form.setMessage('Některé z použitých zajímavosti nejsou momentálně dostupné.', MESSAGE_WARNING);
					}
				}
				
				let endPageWrapper = document.createElement('a');
				endPageWrapper.href = loc;
				endPageWrapper.target = '_blank';
				let endPage = newPage();
				
				links[links.length] = loc;
				
				endPage.ctx.fillStyle = '#f3eee3';
				endPage.ctx.fillRect(0, 0, endPage.width, endPage.height);
				
				endPage.ctx.drawImage(await getImage('res/cake256.png'), 20, 20);
				
				textPos = 296;
				
				endPage.ctx.fillStyle = "black";
				endPage.ctx.font = "36px Calibri";
				textPos += drawWrappedText(endPage.ctx, 'Přání pomohl vytvořit web Narozeninová přání.', 20, textPos, endPage.width-40)+20;
				endPage.ctx.font = "28px Calibri";
				textPos += drawWrappedText(endPage.ctx, 'Chcete svému blízkému udělat radost něčím netradičním?\nPopřejte mu formou přání zaslaného v den narozenin.', 20, textPos, endPage.width-40)+20;
				let list = ['Přání si zde sestavíte z různých ftipných i seriózních zajímavostí.',
						'Vybrané zajímavosti se číselně pojí s oslavencovým věkem.',
						'Po registraci také můžete přispět do sdíleného seznamu vlastní zajímavostí.',
						'Můžete odeslání přání naplánovat dopředu a pustit to z hlavy.'];
				for(item of list){
					endPage.ctx.beginPath();
					endPage.ctx.arc(50, textPos+25, 5, 0, Math.PI*2);
					endPage.ctx.fill();
					textPos += drawWrappedText(endPage.ctx, item, 80, textPos, endPage.width-100)+20;
				}
				textPos += drawWrappedText(endPage.ctx, 'Je to opravdu jednoduché :)', 20, textPos, endPage.width-40)+20;
				endPage.ctx.font = "36px Calibri";
				textPos += drawWrappedText(endPage.ctx, 'Vytvořte přání na '+loc, 20, textPos, endPage.width-40)+20;
				
				endPageWrapper.appendChild(endPage);
				previewBox.appendChild(endPageWrapper);
				
				updating = false;
				
				// return;
				
				// let previewHead = '';
				
				// previewHead += '<link rel="stylesheet" href="css/wish.css">';
				
				// let previewHTML = '';
				
				// // let width = preview.getBoundingClientRect().width-40;
				// // let height = width*Math.sqrt(2);
				
				// let width = 210;
				// let height = 297;
				
				// // let width = 2480;
				// // let height = 3508;
				
				// // preview.style.transform = 'scale(.1)';
				// // preview.style.transformOrigin = '0 0';
				
				// /* Title page */
				// previewHTML += '<div class="wish_page" style="width:'+width+'px;height:'+height+'px;background:#f3eee3">';
				// previewHTML += '	<div class="wish_image"><img src="res/cake.png"></img></div>';
				// previewHTML += '	<div class="wish_body">';
				// if(wish.textMode=='auto'){
					// previewHTML += '		<div class="wish_for">'+wish_for+',</div>';
					// previewHTML += '		<div class="wish_text">'+wish_from+' ti přeje všechno nejlepší k <b>'+bday+'.</b> narozeninám!</div>';
				// } else if(wish.textMode=='custom'){
					// previewHTML += '		<div class="wish_text">'+wishText+'</div>';
				// }
				// previewHTML += '		<div class="wish_text">Na dalších stranách najdeš zajímavosti k číslu tvých narozenin!</div>';
				// previewHTML += '	</div>';
				// previewHTML += '</div>';
				
				// // let infos = [];
				// if(wish.infoMode=='list' && wish.infoList){
					// infos = wish.infoList.split(',');
				// } else if(wish.infoMode=='random' && wish.randomInfoList){
					// infos = wish.randomInfoList.split(',');
				// }
				
				// /* Info pages */
				// for(let infoId of infos){
					
					// let info = await getInfo(infoId);
					// if(info){
						// let background = esc(info.background?info.background:'white');
						// let color = esc(info.color?info.color:'black');
						// previewHTML += '<div id="page_'+info.id+'" class="wish_page" style="width:'+width+'px;height:'+height+'px;background:'+background+'">';
						// previewHTML += '	<div class="info_text" style="color:'+color+'">'+info.content+'</div>';
						// previewHTML += '	<div><a target="_blank" class="info_link" href="'+info.link+'" style="color:'+color+'">'+info.link+'</a></div>';
						// previewHTML += '	<img class="info_img" src="'+info.imgSrc+'"></img>';
						// previewHTML += '	<div class="attribution" style="color:'+color+'">'+info.imgAttrib+'</div>';
						// previewHTML += '</div>';
					// } else {
						// form.setMessage('Některé z použitých zajímavosti nejsou momentálně dostupné.', MESSAGE_WARNING);
					// }
					
				// }
				
				// /* End page */
				// previewHTML += '<div class="wish_page" style="width:'+width+'px;height:'+height+'px;background:#f3eee3">';
				// previewHTML += '	<div class="wish_image"><img src="res/cake.png"></img></div>';
				// previewHTML += '	<div class="wish_body">';
				// previewHTML += '		<div class="wish_text">Přání pomohl vytvořit web Narozeninová přání.</div>';
				// previewHTML += `		<div>
											// <div>Chcete svému blízkému udělat radost něčím netradičním?</div>
											// <div>Popřejte mu formou přání zaslaného v den narozenin.</div>
											// <ul>
												// <li>Přání si zde sestavíte z různých ftipných i seriózních zajímavostí.</li>
												// <li>Vybrané zajímavosti se číselně pojí s oslavencovým věkem.</li>
												// <li>Po registraci také můžete přispět do sdíleného seznamu vlastní zajímavostí.</li>
												// <li>Můžete odeslání přání naplánovat dopředu a pustit to z hlavy.</li>
											// </ul>
											// <div>Je to opravdu jednoduché :)</div>
											// <div><a target="_blank" style="text-decoration:underline;" href="`+loc+`">VYTVOŘIT PŘÁNÍ</a></div>
										// </div>`;
				// previewHTML += '	</div>';
				// previewHTML += '</div>';
				
				// preview.innerHTML = previewHTML;
				// // previewFrame.contentWindow.document.head.innerHTML = previewHead;
				// // previewFrame.contentWindow.document.body.innerHTML = previewHTML;
				
				// let pages = document.querySelectorAll('.wish_page');
				let pages = document.querySelectorAll('.previewCanvas');
				if(highlight) {
					// console.log(highlight, pages[highlight], pages[highlight].offsetTop);
					previewBox.scrollTo(0, pages[highlight].offsetTop-5);
					highlight = null;
				}
				
			}
			
			let form;
			
			function main(){
				
				form = createForm();
				
				form.onFormUpdate = function(name, val){
					//console.log(name, '=', val);
					//updateWish(form.form);
					wish[name] = val;
					updateWish();
				}
				
				window.addEventListener('resize', function(e){
					updateWish();
				})
				
				/* Page 0 */
				
				form.addPage(0, createFormPage(form, function(page){
					
					page.add(createNumberInput(form, 'bday', 'Číslo narozenin:', '42', 1, 199));
					
					let textModeTabBox = createTabBox(form, 'textMode', 'Text přání:');
					
					let tab1 = createTab();
					tab1.add(createTextInput(form, 'for', 'Oslovení:', 'Milá Alice'));
					tab1.add(createTextInput(form, 'from', 'Kdo přeje:', 'Bob'));
					textModeTabBox.addTab('auto', 'Automatický', tab1);
					
					let tab2 = createTab();
					tab2.add(createTextArea(form, 'wishText', 'Text:', 'Všechno nejlepší!'));
					textModeTabBox.addTab('custom', 'Vlastní', tab2);
					
					textModeTabBox.setTab('auto');
					
					page.add(textModeTabBox);
					
					let catList = createCheckList(form, 'categories', 'Zájmy:', 'Vybrat všechny')
					page.add(catList);
					
					page.addControlF('Další >', function(){
						if(!wish['bday']){
							form.setMessage('Prosím vyplňte Číslo narozenin!');
						} else if(wish['bday']!=Math.floor(wish['bday'])){
							form.setMessage('Neplatné číslo narozenin!');
						} else if(wish['bday']<1){
							form.setMessage('Číslo narozenin musí být větší než 0!');
						} else if((wish['textMode']=='auto' && (!wish['for'] || !wish['from'])) || (wish['textMode']=='custom' && !wish['wishText'])){
							form.setMessage('Prosím vyplňte Text přání!');
						} else if(!wish['categories']){
							form.setMessage('Prosím vyberte aspoň jeden zájem!');
						} else if((wish['textMode']=='auto' && (wish['for']+wish['from']).length>209) || (wish['textMode']=='custom' && wish['wishText'].length>255)){
							form.setMessage('Text přání je příliš dlouhý!');
						} else {
							form.setPage(1);
						}
					});
					
					catList.clearItems();
					get(loc+'/get/categories.php', function(res){
						let json = JSON.parse(res);
						for(let row of json){
							catList.addItem(row.name, row.name);
						}
					});
					
				}));
				
				/* Page 1 */
				
				form.addPage(1, createFormPage(form, function(page){
					
					let infosTabBox = createTabBox(form, 'infoMode', 'Vybrat zajímavosti:');
					
					let randomInfoTab = createTab();
					
					randomInfoTab.add(createNumberInput(form, 'infoCount', 'Počet zajímavostí:', 1, 1, 100, 1));
					
					let randomList = createBagList(form, 'randomInfoList', 'Zajímavosti', 'Vybrat jinou zajímavost');
					
					randomInfoTab.add(createButton('Vybrat náhodně', function(){
						let infos = [];
						for(let i in infoCache){
							if(infoCache[i].number==wish['bday']) infos[infos.length] = {name:infoCache[i].id, label:deesc(infoCache[i].content)};
						}
						randomList.set(infos, wish.infoCount);
						//let pages = document.querySelectorAll('.wish_page');
						//if(pages.length>1) previewBox.scrollTo(0, pages[1].offsetTop-5);
						highlight = 1;
					}));
					
					randomInfoTab.add(randomList);
					
					let listInfoTab = createTab();
					
					//let infoList = createCheckList(form, 'infoList', 'Zajímavosti', 'Vybrat všechny');
					let infoList = createDoubleList(form, 'infoList', 'Zajímavosti');
					
					infoList.onSelect = function(name){
						//previewBox.scrollTo(0, window['page_'+name].offsetTop-5);
						highlight = infoList.getSelected().indexOf(name)+1;
						if(highlight<0) highlight = null;
					}
					
					listInfoTab.add(infoList);
					
					infosTabBox.addTab('random', 'Náhodně', randomInfoTab);
					infosTabBox.addTab('list', 'Ze seznamu', listInfoTab);
					
					infosTabBox.setTab('random');
					
					page.add(infosTabBox);
					
					page.onOpen = function(){
						
						infoList.clearItems();
						
						form.setMessage('Načítání zajímavostí...', MESSAGE_STATUS, false);
						
						get(loc+'/get/info.php?bday='+encodeURIComponent(wish.bday)+'&categories='+encodeURIComponent(wish.categories), function(res){
							form.clearMessage();
							let json = JSON.parse(res);
							if(json.length==0){
								form.setMessage('Nenalezeny žádné zajímavosti. <a style="color:white" class="link" href="add_info.php">Přidat zajímavost</a>', MESSAGE_WARNING);
							}
							for(let row of json){
								infoList.addItem(row.id, deesc(row.content));
								infoCache[row.id] = row;
							}
						});
						
					}
					
					page.addControl('< Zpět', 0);
					page.addControlF('Vytvořit >', function(){
						if((wish['infoMode']=='random' && !wish['randomInfoList']) || (wish['infoMode']=='list' && !wish['infoList'])) {
							form.setMessage('Prosím vyberte aspoň jednu zajímavost!');
						} else {
							form.setMessage('Vytváření přání (může trvat několik vteřin)', MESSAGE_STATUS, false);
							let get = '';
							let uid = getSearchObj().uid;
							if(uid){
								get = '?uid='+uid;
							}
							let postData = 'bday='+encodeURIComponent(wish['bday']??'')+
									'&textMode='+encodeURIComponent(wish['textMode'])+
									'&for='+encodeURIComponent(wish['for']??'')+
									'&from='+encodeURIComponent(wish['from']??'')+
									'&wishText='+encodeURIComponent(wish['wishText']??'')+
									'&categories='+encodeURIComponent(wish['categories']??'')+
									'&infoMode='+encodeURIComponent(wish['infoMode'])+
									'&infoList='+encodeURIComponent(wish['infoList']??'')+
									'&infoCount='+encodeURIComponent(wish['infoCount']??'')+
									'&randomInfoList='+encodeURIComponent(wish['randomInfoList']??'')+
									'&numPages='+usedPages.length;
							for(let i in usedPages){
								postData += '&page'+i+'='+encodeURIComponent(usedPages[i].toDataURL('image/png'));
								postData += '&link'+i+'='+encodeURIComponent(links[i]);
							}
							post(loc+'/makepdf.php'+get,
									postData,
									function(res){
										try {
											let json = JSON.parse(res);
											setSearchText('uid='+json.uid);
											form.setPage(2);
										} catch(e){
											console.error('Failed to create wish:', e);
											form.setMessage('Nelze vytvořit přání (chyba serveru)');
										}
									}, function(error, message){
										console.error('Server responded with error: ', error, message);
										form.setMessage('Nelze vytvořit přání (chyba serveru nebo sítě)');
									});
						}
						/*post(loc+'/pdftest.php', 'imgData='+encodeURIComponent(previewBox.children[0].toDataURL('image/png')), function(res){
							
						});*/
						//window.open('pdftest.php?imgData='+previewBox.children[0].toDataURL('image/png'));
					});
					
				}));
				
				/* Page 2 */
				
				form.addPage(2, createFormPage(form, function(page){
					
					let dlbox = document.createElement('div');
					
					page.add(dlbox);
					
					page.onOpen = function(){
						
						dlbox.innerHTML = '';
						
						// TODO get dl link
						
						get(loc+'/get/wish_mailInfo.php?uid='+getSearchObj().uid, function(res){
							
							let json = JSON.parse(res);
							
							dlbox.innerHTML = '<a href="'+loc+'/get/wish_pdf.php?uid='+getSearchObj().uid+'" download="Přání.pdf"><button class="formrow action">Stáhnout přání</button></a>';
							
							if(json.mail_sent=='1'){
								//dlbox.appendChild(document.createTextNode('Přání již bylo odesláno.'));
								dlbox.innerHTML += '<div class="formrow"><span class="formlbl">Přání bylo odesláno.</span></div>';
							} else {
								
								wish['mailAddress'] = decodeURIComponent(json['mail_address']);
								wish['mailHiddenCopy'] = decodeURIComponent(json['mail_hidden']);
								wish['mailDate'] = decodeURIComponent(json['mail_date']);
								
								form.inputs['mailAddress'].value = decodeURIComponent(json['mail_address']);
								form.inputs['mailHiddenCopy'].value = decodeURIComponent(json['mail_hidden']);
								form.inputs['mailDate'].value = decodeURIComponent(json['mail_date']);
								
								if(json.mail_date){
									//dlbox.appendChild(document.createTextNode('Přání bude odesláno '+json.mail_date));
									dlbox.innerHTML += '<div class="formrow"><span class="formlbl">Přání bude odesláno '+decodeURIComponent(json.mail_date)+'</span></div>';
								}
								
								dlbox.appendChild(createButton('Možnosti odeslání', function(){
									form.setPage('mail');
								}));
								
								dlbox.appendChild(createButton('Upravit přání', function(){
									form.setPage(0);
								}));
								
							}
							
						});
						
					}
					
				}));
				
				function checkAddresses(addr){
					
					let regex = /^[A-Za-z0-9\.\_\-]+@[A-Za-z0-9\_\-]+\.[A-Za-z0-9]+$/;
					let list = addr.split('\n');
					for(let i=0; i<list.length; i++){
						let line = list[i].trim();
						if(!regex.test(line)) return false;
					}
					
					return true;
					
				}
				
				/* Mail Page */
				
				form.addPage('mail', createFormPage(form, function(page){
					
					let ta = createTextArea(form, 'mailAddress', 'E-mail:');
					ta.setHint('Můžete zadat více adres, každou na vlastní řádek');
					page.add(ta);
					let ta2 = createTextArea(form, 'mailHiddenCopy', 'Skrytá kopie:');
					ta2.setHint('Můžete zadat více adres, každou na vlastní řádek');
					page.add(ta2);
					
					let tabBox = createTabBox(form, 'mailMode', 'Možnost odeslání:');
					
					let tab1 = createTab();
					tab1.add(createDateInput(form, 'mailDate', 'Datum:'));
					tab1.add(createButton('Uložit', function(){
						let mailDate = new Date(wish['mailDate']);
						let nextYear = new Date(new Date().setDate(new Date().getDate()+365));
						if(!wish['mailAddress']){
							form.setMessage('Prosím vyplňte E-mail!');
						} else if(wish['mailAddress'].length>100){
							form.setMessage('E-mail je příliš dlouhý!');
						} else if(wish['mailHiddenCopy'].length>100){
							form.setMessage('Skrytá kopie je příliš dlouhá!');
						} else if(!checkAddresses(wish['mailAddress'])){
							form.setMessage('Neplatný e-mail!');
						} else if(wish['mailHiddenCopy']&&!checkAddresses(wish['mailHiddenCopy'])){
							form.setMessage('Neplatná skrytá kopie!');
						} else if(!(mailDate>new Date() && mailDate<nextYear)){
							form.setMessage('Neplatné datum!');
						} else {
							post(loc+'/post/schedule_send.php',
									'uid='+getSearchObj().uid+
									'&mailAddress='+encodeURIComponent(wish['mailAddress'])+
									'&mailHiddenCopy='+encodeURIComponent(wish['mailHiddenCopy'])+
									'&date='+encodeURIComponent(wish['mailDate']),
									function(res){
										form.setPage(2);
									});
						}
					}));
					tab1.add(createButton('Zrušit odeslání', function(){
						post(loc+'/post/cancel_send.php',
									'uid='+getSearchObj().uid,
									function(res){
										form.setPage(2);
									});
					}));
					
					let tab2 = createTab();
					tab2.add(createButton('Odeslat', function(){
						if(!wish['mailAddress']){
							form.setMessage('Prosím vyplňte E-mail!');
						} else if(wish['mailAddress'].length>100){
							form.setMessage('E-mail je příliš dlouhý!');
						} else if(wish['mailHiddenCopy'].length>100){
							form.setMessage('Skrytá kopie je příliš dlouhá!');
						} else if(!checkAddresses(wish['mailAddress'])){
							form.setMessage('Neplatný e-mail!');
						} else if(wish['mailHiddenCopy']&&!checkAddresses(wish['mailHiddenCopy'])){
							form.setMessage('Neplatná skrytá kopie!');
						} else {
							form.setMessage('Odesílání', MESSAGE_STATUS, false);
							post(loc+'/post/send_mail.php',
									'uid='+getSearchObj().uid+
									'&mailAddress='+encodeURIComponent(wish['mailAddress'])+
									'&mailHiddenCopy='+encodeURIComponent(wish['mailHiddenCopy']),
									function(res){
										form.setPage(2);
									});
						}
					}));
					
					tabBox.addTab('date', 'Datum', tab1);
					tabBox.addTab('now', 'Ihned', tab2);
					
					tabBox.setTab('date');
					
					page.add(tabBox);
					
					page.addControl('< Zpět', 2);
					
					page.onOpen = function(){
						get(loc+'/get/auth.php', function(res){
							if(res=='false') {
								location.href = loc+"/login.php?page="+encodeURIComponent(location.href);
							} else {
								let json = JSON.parse(res);
								if(!json.verified){
									form.setPage(2);
									form.setMessage('Učet není ověřen!');
								}
							}
						});
					}
					
				}));
				
				let uid = getSearchObj().uid;
				
				if(uid){
					
					get(loc+'/get/wish_json.php?uid='+uid, function(res){
						
						let json = JSON.parse(res);
						
						wish['bday'] = decodeURIComponent(json['bday']);
						wish['textMode'] = decodeURIComponent(json['textMode']);
						wish['for'] = decodeURIComponent(json['for']);
						wish['from'] = decodeURIComponent(json['from']);
						wish['wishText'] = decodeURIComponent(json['wishText']);
						wish['categories'] = decodeURIComponent(json['categories']);
						wish['infoMode'] = decodeURIComponent(json['infoMode']);
						wish['infoList'] = decodeURIComponent(json['infoList']);
						wish['infoCount'] = decodeURIComponent(json['infoCount']);
						wish['randomInfoList'] = decodeURIComponent(json['randomInfoList']);
						
						get(loc+'/get/info.php?bday='+encodeURIComponent(wish.bday)+'&categories='+encodeURIComponent(wish.categories), function(res){
							
							let json2 = JSON.parse(res);
							for(let row of json2){
								infoCache[row.id] = row;
							}
							
							form.setPage(2);
							
							updateWish();
							
							form.inputs['bday'].value = decodeURIComponent(json['bday']);
							form.inputs['for'].value = decodeURIComponent(json['for']);
							form.inputs['from'].value = decodeURIComponent(json['from']);
							form.inputs['wishText'].value = decodeURIComponent(json['wishText']);
							
							form.inputs['textMode'].setTab(decodeURIComponent(json['textMode']));
							form.inputs['infoMode'].setTab(decodeURIComponent(json['infoMode']));
							
							let cats = decodeURIComponent(json['categories']).split(',');
							
							for(let cat of cats){
								form.inputs['categories'].check(cat, true);
							}
							
						});
						
					}, function(status, message){
						form.setMessage(message);
					});
					
				} else {
					
					form.setPage(0);
					updateWish();
				}
				
				formContainer.appendChild(form);
				
			}
			
		</script>
		
	</head>

    <body onload="main();">

		<?php include('php/titlebar.php'); ?>

		<div class="content">

			<div class="subtitlebar">
				<div class="backbtn"><a href="index.php"><</a></div><div class="subtitle">Vytvořit přání</div>
			</div>

			<div class="form">
				
				<div class="leftcol">
					
					<div id="formContainer"></div>
					
				</div>
				
				<div id="previewBox" class="rightcol previewbox">
					
					<!--Fuck you, you are not getting a preview, because fuck this-->
					
					<!--embed id="previewEmbed" type="application/pdf"></embed-->
					<!--div id="preview"></div-->
					<!--iframe id="previewFrame" frameborder="0" class="previewFrame"></iframe-->
					
				</div>
				
			</div>

		</div>

    </body>

</html>
<?php

?>