
function checkAddresses(addr){

  let regex = /^[A-Za-z0-9\.\_\-]+@[A-Za-z0-9\_\-]+\.[A-Za-z0-9]+$/;
  let list = addr.split('\n');
  for(let i=0; i<list.length; i++){
    let line = list[i].trim();
    if(!regex.test(line)) return false;
  }

  return true;

}

function esc(str){

	let map = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&apos;',
		"`": '&#96;',
		'\\': '&#92;',
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
		'&quot;': '"',
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

let search = new URLSearchParams(location.search);

function getSearch(name){
  return search.get(name);
}

// function getSearchObj(){
// 	let txt = location.search;
// 	if(txt.startsWith('?')) txt = txt.substring(1);
// 	let entries = txt.split('&');
// 	let obj = [];
// 	for(let entry of entries){
// 		let parts = entry.split('=');
// 		obj[parts[0]] = parts[1];
// 	}
// 	return obj;
// }

function setSearchText(searchText){
	history.replaceState(null, '', location.origin+location.pathname+'?'+searchText);
  search = new URLSearchParams(searchText);
}

let INFO_LIMIT = 10;

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

// let updating = false;
let initialized = false;
let links = [];

function initWish(){
	initialized = true;
	updateWish();
}

async function updateWish(){

	if(!initialized) {
    // console.log('not initialized');
		return;
	}

  // console.log('updating');

	// if(updating) {
	// 	return;
	// }

	// updating = true;

	let wish_for = wish['for']||'Milá Marie';
	let wish_from = wish['from']||'Jiří';
	let bday = wish['bday']||'42';
	let wishText = wish['wishText']||'Všechno nejlepší!';

	returnPages();
	previewBox.innerHTML = '';
	links = [];

	let page1Wrapper = document.createElement('a');
	let page1 = newPage();

	links[links.length] = '';

	page1.ctx.fillStyle = '#f3eee3';
	page1.ctx.fillRect(0, 0, page1.width, page1.height);

	page1.ctx.fillStyle = "black";
	let textPos = 296;
	if(wish.textMode=='auto'){
		page1.ctx.font = "60px Arial";
		page1.ctx.fillText(wish_for+',', 290, 200);
		textPos += drawWrappedText(page1.ctx, wish_from+' ti přeje všechno nejlepší k '+bday+'. narozeninám!', 40, 20, textPos, page1.width-40)+20;
	} else if(wish.textMode=='custom'){
		textPos += drawWrappedText(page1.ctx, wishText, 40, 20, textPos, page1.width-40)+20;
	}

	textPos += drawWrappedText(page1.ctx, 'Na dalších stranách najdeš zajímavosti k číslu tvých narozenin!', 30, 20, textPos, page1.width-40);

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

			let pageWrapper = document.createElement('a');
			if(info.link) pageWrapper.href = info.link;
			pageWrapper.target = '_blank';
			links[links.length] = info.link;
			let page = newPage();

			page.ctx.fillStyle = background;
			page.ctx.fillRect(0, 0, page.width, page.height);

			page.ctx.fillStyle = color;
			let textPos = 20;
			textPos += drawWrappedText(page.ctx, deesc(info.content), 32, 20, textPos, page.width-40)+20;
			page.ctx.fillStyle = "blue";
			textPos += drawWrappedText(page.ctx, info.link, 28, 20, textPos, page.width-40)+20;

			let img = await getImage(info.imgSrc);

			if(img){

        let imgAttrib = info.imgAttrib??'';

				let imgRatio = img.width/img.height;
				let fullWidthHeight = (page.width-40)/imgRatio;
				let attribHeight = getTextHeight(page.ctx, imgAttrib, 22, 20, textPos, page.width-40);

				page.ctx.fillStyle = color;

				if(textPos+fullWidthHeight+20+attribHeight+20>page.height){

					let imgHeight = page.height-textPos-20-attribHeight-20;
					let imgWidth = imgHeight*imgRatio;
					let imgPos = page.width/2-imgWidth/2;

					page.ctx.drawImage(img, imgPos, textPos, imgWidth, imgHeight);
					textPos += imgHeight+20;
					drawWrappedText(page.ctx, imgAttrib, [22, 'italic'], 20, textPos, page.width-40);

				} else {
					page.ctx.drawImage(img, 20, textPos, page.width-40, fullWidthHeight);
					textPos += fullWidthHeight+20;
					drawWrappedText(page.ctx, imgAttrib, [22, 'italic'], 20, textPos, page.width-40);
				}

			}

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
	textPos += drawWrappedText(endPage.ctx, 'Přání pomohl vytvořit web Narozeninová přání.', 36, 20, textPos, endPage.width-40)+20;
	textPos += drawWrappedText(endPage.ctx, 'Chcete svému blízkému udělat radost něčím netradičním?\nPopřejte mu formou přání zaslaného v den narozenin.', 28, 20, textPos, endPage.width-40)+20;
	let list = ['Sestavte si přání z vtipných i seriózních zajímavostí souvisejících s oslavencovým věkem.',
			'Naplánujte odeslání přání dopředu a pusťte to z hlavy.',
			'Tvořte obsah webu s námi – zaregistrujte se a vkládejte vlastní zajímavosti.'];
	for(item of list){
		endPage.ctx.beginPath();
		endPage.ctx.arc(50, textPos+25, 5, 0, Math.PI*2);
		endPage.ctx.fill();
		textPos += drawWrappedText(endPage.ctx, item, 28, 80, textPos, endPage.width-100)+20;
	}
	textPos += drawWrappedText(endPage.ctx, 'Je to opravdu jednoduché :)', 28, 20, textPos, endPage.width-40)+20;
	textPos += drawWrappedText(endPage.ctx, 'Vytvořte přání na '+loc, 36, 20, textPos, endPage.width-40)+20;

	endPageWrapper.appendChild(endPage);
	previewBox.appendChild(endPageWrapper);

	let pages = document.querySelectorAll('.previewCanvas');
	if(highlight) {
		previewBox.scrollTo(0, pages[highlight].offsetTop-5);
		highlight = null;
	}

	// updating = false;

  // console.log('updated');

}

let form;
let prevDist;

function main(){

	form = createForm();

	form.onFormUpdate = function(name, val){
		wish[name] = val;
		updateWish();
	}

	window.addEventListener('resize', function(e){
		updateWish();
	});

	previewBox.addEventListener('mousewheel', function(e){
		if(e.ctrlKey){
			e.preventDefault();
			zoom(e.deltaY/10);
		}
	});

	previewBox.ontouchstart = function(e){
		if(e.touches.length>1){
			e.preventDefault();
			prevDist = Math.hypot(e.touches[0].pageX-e.touches[1].pageX, e.touches[0].pageY-e.touches[1].pageY);
		}
	}

	previewBox.ontouchmove = function(e){
		if(e.touches.length>1){
			let dist = Math.hypot(e.touches[0].pageX-e.touches[1].pageX, e.touches[0].pageY-e.touches[1].pageY);
			let diff = prevDist-dist;
			prevDist = dist;
			zoom(diff);
		}
	}

	let sendScheduled = false;

	/* Page 0 */

	form.addPage(0, createFormPage(form, function(page){

		page.critical = true;

		page.add(createNumberInput(form, 'bday', 'Pro kolikáté narozeniny:', '42', 1, 199, null, 'Ke kolikátým narozeninám přejete, k takovému číslu se budou nabízet zajímavosti do přání.'));

		let textModeTabBox = createTabBox(form, 'textMode', 'Text přání:', 'Text na první straně přání si nechte rychle vygenerovat nebo napište vlastní.');

		let tab1 = createTab();
		tab1.add(createTextInput(form, 'for', 'Oslovení:', 'Milá Marie'));
		tab1.add(createTextInput(form, 'from', 'Kdo přeje:', 'Jiří'));
		textModeTabBox.addTab('auto', 'Generovaný', tab1, 'Zadáte oslovení a vaše jméno. Text přání se vygeneruje, vše vidíte v náhledu.');

		let tab2 = createTab();
		tab2.add(createTextArea(form, 'wishText', 'Text:', 'Všechno nejlepší!'));
		textModeTabBox.addTab('custom', 'Vlastní', tab2, 'Napíšete vlastní text přání, vše vidíte v náhledu.');

		textModeTabBox.setTab('auto');

		page.add(textModeTabBox);

		let catList = createCheckList(form, 'categories', 'Zájmy oslavence:', 'Vybrat všechny', 'Vyberte zájmy oslavence. Nabídnou se jen zajímavosti, které souvisí s vybranými zájmy.')
		page.add(catList);

		let cancelEditBtn = createButton('Zrušit úpravy ×', function(){
			window.onbeforeunload = null;
			location.reload();
		});

		page.onOpen = function(){
			// cancelEditBtn.style.display = (getSearchObj().uid!=null)?'inline-block':'none';
      cancelEditBtn.style.display = (getSearch('uid')!=null)?'inline-block':'none';
		}

		page.addControlB(cancelEditBtn);

		page.addControlF('Další >', function(){
			if(!wish['bday']){
				form.setMessage('Kolonka „Pro kolikáté narozeniny“ není vyplněna!');
			} else if(wish['bday']!=Math.floor(wish['bday'])){
				form.setMessage('Neplatné číslo narozenin!');
			} else if(wish['bday']<1){
				form.setMessage('Číslo narozenin musí být větší než 0!');
			} else if(wish['textMode']=='auto' && (!wish['for'] || !wish['from'])){
				if(!wish['for']){
					form.setMessage('Kolonka „Oslovení“ v Oblasti „Text přání“ není vyplněna!');
				} else {
					form.setMessage('Kolonka „Kdo přeje“ v Oblasti „Text přání“ není vyplněna!');
				}
			} else if(wish['textMode']=='custom' && !wish['wishText']){
				form.setMessage('Kolonka „Text“ v Oblasti „Text přání“ není vyplněna!');
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
			// TODO DEBUG remove this? (should only trigger on localhost)
			// if(getSearchObj().debug && loc.indexOf('localhost')>=0){
      //
			// 	form.inputs['bday'].value = 8;
			// 	wish['bday'] = 8;
			// 	form.inputs['wishText'].value = 'debug';
			// 	wish['wishText'] = 'debug';
      //
			// 	form.inputs['textMode'].setTab('custom');
			// 	wish['textMode'] = 'custom';
      //
			// 	// form.inputs['infoMode'].setTab('list');
			// 	// wish['infoMode'] = 'list';
      //
			// 	form.inputs['infoCount'].value = 3;
			// 	wish['infoCount'] = 3;
      //
			// 	let cats = [];
			// 	for(let item in form.inputs['categories'].checkList.items){
			// 		form.inputs['categories'].check(item, true);
			// 		cats.push(item);
			// 	}
			// 	wish['cats'] = cats.join(',');
      //
			// 	form.setPage(1);
      //
			// }
		});

	}));

	/* Page 1 */

	form.addPage(1, createFormPage(form, function(page){

		page.critical = true;

		let infosTabBox = createTabBox(form, 'infoMode', 'Zajímavosti:', 'Zajímavosti (dle čísla narozenin a zájmů) se vyberou náhodně nebo je vyberete ze seznamu.');

		let randomInfoTab = createTab();

		let infoCountIn = createNumberInput(form, 'infoCount', 'Kolik zajímavostí vybrat:', 1, 1, 100, 1);

		randomInfoTab.add(infoCountIn);

		let randomList = createBagList(form, 'randomInfoList', 'Zajímavosti', 'Vybrat jinou zajímavost');

		randomInfoTab.add(createButton('Vybrat náhodně', function(){
			let infos = [];
			for(let i in infoCache){
				if(infoCache[i].number==wish['bday']) infos[infos.length] = {name:infoCache[i].id, label:deesc(infoCache[i].content)};
			}
			randomList.set(infos, wish.infoCount);
			highlight = 1;
		}));

		randomInfoTab.add(randomList);

		let listInfoTab = createTab();

		let infoList = createDoubleList(form, 'infoList', 'Zajímavosti');
		infoList.setSelectedHint('Tyto zajímavosti budou zahrnuty do přání. Můžete měnit jejich pořadí nebo je z výběru odebrat.');
		infoList.setToSelectHint('Zajímavost vyberete kliknutím. Uvidíte ji v náhledu.');

		infoList.onSelect = function(name){
			highlight = infoList.getSelected().indexOf(name)+1;
			if(highlight<0) highlight = null;
		}

		listInfoTab.add(infoList);

		infosTabBox.addTab('random', 'Vybrat náhodně', randomInfoTab, 'Web za vás vybere vámi zadaný počet zajímavostí dle čísla narozenin a zájmů. Můžete měnit jejich pořadí.\nPokud se vám zajímavost nelíbí, web vám může nabídnout jinou.');
		infosTabBox.addTab('list', 'Vyberu sám', listInfoTab, 'Web nabídne seznam zajímavostí dle čísla narozenin a zájmů, a vy z nich vyberete ty, které by se oslavenci mohly líbit.');

		infosTabBox.setTab('random');

		page.add(infosTabBox);

		page.onOpen = function(){

			infoList.clearItems();

			form.setMessage('Načítání zajímavostí...', MESSAGE_STATUS, false);

			get(loc+'/get/info.php?bday='+encodeURIComponent(wish.bday)+'&categories='+encodeURIComponent(wish.categories), function(res){
				form.clearMessage();
				let json = JSON.parse(res);
				if(json.length==0){
					form.setMessage('Pro dané číslo a vybrané zájmy zatím není žádná zajímavost k dispozici. <a style="color:white" class="link" href="add_info.php">Přidat zajímavost</a>', MESSAGE_WARNING);
				}
				infoCountIn.setMax(Math.max(Math.min(json.length, INFO_LIMIT), 1));
				for(let row of json){
					infoList.addItem(row.id, deesc(row.content));
					infoCache[row.id] = row;
				}
        if(wish['infoMode']=='list'){
          if(wish['infoList']){
            let infoIds = wish['infoList'].split(',');
            if(infoIds.length>0) infoList.setSelectedItems(infoIds);
          }
        } else {
          if(wish['randomInfoList']){
            let infoIds = wish['randomInfoList'].split(',');
            if(infoIds.length>0){
              let allInfos = [];
        			for(let i in infoCache){
        				if(infoCache[i].number==wish['bday']) allInfos[allInfos.length] = {name:infoCache[i].id, label:deesc(infoCache[i].content)};
        			}
              let usedInfos = [];
              for(let i of infoIds){
                let id = parseInt(i);
                usedInfos.push({name:infoCache[id].id, label:deesc(infoCache[id].content)});
              }
              randomList.setItems(allInfos, usedInfos);
            }
          }
        }
			});

		}

		page.addControl('< Zpět', 0);
		page.addControlF('Vytvořit >', function(){
			if(infoList.getSelected().length>INFO_LIMIT){
				form.setMessage('Prosím vyberte '+INFO_LIMIT+' nebo méně zajímavostí');
			} else if((wish['infoMode']=='random' && !wish['randomInfoList']) || (wish['infoMode']=='list' && !wish['infoList'])) {
				form.setMessage('Prosím vyberte alespoň jednu zajímavost!');
			} else {
				// console.log('creating wish', performance.now());
				form.setMessage('Vytváření přání (může trvat několik vteřin)', MESSAGE_STATUS, false);
				form.blackout();
				let get = '';
				// let uid = getSearchObj().uid;
        let uid = getSearch('uid');
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
					// console.log('creating page images', performance.now());
				for(let i in usedPages){
					postData += '&page'+i+'='+encodeURIComponent(usedPages[i].toDataURL('image/jpeg'));
					postData += '&link'+i+'='+encodeURIComponent(links[i]);
				}
				// console.log('sending wish', performance.now());
				post(loc+'/makepdf.php'+get,
						postData,
						function(res){
							// console.log('response received', performance.now());
							try {
								let json = JSON.parse(res);
								setSearchText('uid='+json.uid);
								form.setPage(2);
								form.noblackout();
								// console.log('wish created', performance.now());
							} catch(e){
								console.error('Failed to create wish:', e);
								form.setMessage('Nelze vytvořit přání (chyba webu)');
								form.noblackout();
							}
						}, function(error, message){
							console.error('Server responded with error: ', error, message);
							form.setMessage('Nelze vytvořit přání (chyba serveru nebo sítě)');
							form.noblackout();
						});
			}
		});

	}));

	/* Page 2 */

	form.addPage(2, createFormPage(form, function(page){

		let dlbox = document.createElement('div');

		page.add(dlbox);

		page.onOpen = function(){

			dlbox.innerHTML = '';

			// get(loc+'/get/wish_mailInfo.php?uid='+getSearchObj().uid, function(res){
      get(loc+'/get/wish_mailInfo.php?uid='+getSearch('uid'), function(res){

				let json = JSON.parse(res);

				// dlbox.innerHTML = '<button class="formrow action"><a href="'+loc+'/get/wish_pdf.php?uid='+getSearchObj().uid+'" download="Přání.pdf" style="color:white;">Stáhnout PDF</a></button>';
        // dlbox.innerHTML = '<button class="formrow action"><a href="'+loc+'/get/wish_pdf.php?uid='+getSearch('uid')+'" download="Přání.pdf" style="color:white;">Stáhnout PDF</a></button>';
				let dlDiv = document.createElement('div');
				let dlBtn = createButton('Stáhnout PDF', function(){
					let a = document.createElement('a');
					a.href = loc+'/get/wish_pdf.php?uid='+getSearch('uid');
					// a.target = '_blank';
					a.download = 'Přání.pdf';
					a.click();
				});
				dlBtn.classList.add('wide');
				dlDiv.appendChild(dlBtn);
				dlDiv.appendChild(createHint('Stáhnete vygenerované pdf - jeho náhled vidíte'));
				dlbox.appendChild(dlDiv);

				if(json.mail_sent=='1'){
					// dlbox.innerHTML += '<div class="formrow"><span class="formlbl">Přání bylo odesláno.</span></div>';
					let div = document.createElement('div');
					div.className = 'formrow';
					div.innerText = 'Přání bylo odesláno.';
					dlbox.appendChild(div);
				} else {

					sendScheduled = !!json.mail_date;

					wish['mailAddress'] = decodeURIComponent(json['mail_address']??'');
					wish['mailHiddenCopy'] = decodeURIComponent(json['mail_hidden']??'');
					wish['mailDate'] = decodeURIComponent(json['mail_date']??'');

					form.inputs['mailAddress'].value = decodeURIComponent(json['mail_address']??'');
					form.inputs['mailHiddenCopy'].value = decodeURIComponent(json['mail_hidden']??'');
					form.inputs['mailDate'].value = decodeURIComponent(json['mail_date']??'');
					form.inputs['signMail'].checked = json['mail_sign'];

					if(json.mail_date){

						// dlbox.innerHTML += '<div class="formrow"><span class="formlbl">Přání bude odesláno '+decodeURIComponent(json.mail_date)+'</span></div>';
						let row = document.createElement('div');
						row.className = 'formrow';
						row.innerText = 'Přání bude odesláno '+decodeURIComponent(json.mail_date);
						dlbox.appendChild(row);

						let sendDiv = document.createElement('div');
						let sendBtn = createButton('Změnit odeslání', function(){
							form.setPage('mail');
						});
						sendBtn.classList.add('wide');
						sendDiv.appendChild(sendBtn);
						sendDiv.appendChild(createHint('Změnit nebo zrušit kdy a na jaký email pdf odeslat'));
						dlbox.appendChild(sendDiv);

					} else {
						let sendDiv = document.createElement('div');
						let sendBtn = createButton('Odeslat přání', function(){
							form.setPage('mail');
						});
						sendBtn.classList.add('wide');
						sendDiv.appendChild(sendBtn);
						sendDiv.appendChild(createHint('Nadefinujete kdy a na jaký email pdf odeslat'));
						dlbox.appendChild(sendDiv);
					}

					let editDiv = document.createElement('div');
					let editBtn = createButton('Upravit přání', function(){
						form.setPage(0);
					});
					editBtn.classList.add('wide');
					editDiv.appendChild(editBtn);
					editDiv.appendChild(createHint('Vrátí vás do průvodce vytvořením přání'));
					dlbox.appendChild(editDiv);

				}

			});

		}

	}));

	/* Mail Page */

	form.addPage('mail', createFormPage(form, function(page){

		page.critical = true;

		let ta = createTextArea(form, 'mailAddress', 'E-mail:', 'marie@email.cz\nkarel.novak@priklad.cz');
		ta.setHint('E-mailové adresy, na které bude přání odesláno - budou zobrazeny jako příjemci e-mailu.\nMůžete zadat více adres, každou na vlastní řádek. Zadejte alespoň jednu adresu.');
		page.add(ta);
		let ta2 = createTextArea(form, 'mailHiddenCopy', 'Skrytá kopie:');
		ta2.setHint('E-mailové adresy, na které bude přání odesláno - nebudou zobrazeny v e-mailu.\nMůžete zadat více adres, každou na vlastní řádek. Nemusí být vyplněno.');
		page.add(ta2);

		// let sign = createButton('test', function(){});
		let signDiv = document.createElement('div');
		signDiv.className = 'formrow';
		let sign = document.createElement('label');
		let signCheckbox = document.createElement('input');
		signCheckbox.type = 'checkbox';
		form.inputs['signMail'] = signCheckbox;
		let signLabel = document.createElement('span');
		signLabel.className = 'formlbl';
		signLabel.innerText = 'Zahrnout mojí adresu do textu e-mailu:';
		sign.appendChild(signLabel);
		sign.appendChild(signCheckbox);
		signDiv.appendChild(sign);
		page.add(signDiv);

		// signCheckbox.checked = wish['sign'];

		let tabBox = createTabBox(form, 'mailMode', 'Kdy odeslat e-mail:', 'Má se přání odeslat hned nebo až ráno zadaného dne?');

		let tab1 = createTab();
		tab1.add(createDateInput(form, 'mailDate', 'Datum:', 'Datum, kdy bude přání odesláno. Musí být nejdříve zítra a nejpozději za 1 rok.'));
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
						// 'uid='+getSearchObj().uid+
            'uid='+getSearch('uid')+
						'&mailAddress='+encodeURIComponent(wish['mailAddress'])+
						'&mailHiddenCopy='+encodeURIComponent(wish['mailHiddenCopy'])+
						'&date='+encodeURIComponent(wish['mailDate'])+
						'&signMail='+(form.inputs['signMail'].checked?1:0),
						function(res){
							form.setPage(2);
						});
			}
		}));
		let cancelSendBtn = createButton('Zrušit odeslání', function(){
			post(loc+'/post/cancel_send.php',
						// 'uid='+getSearchObj().uid,
            'uid='+getSearch('uid'),
						function(res){
							form.setPage(2);
						});
		})
		tab1.add(cancelSendBtn);

		let tab2 = createTab();
		let filler = document.createElement('div');
		filler.className = 'formrow';
		filler.innerHTML = 'Kliknutím na tlačítko potvrdíte odeslání';
		tab2.add(filler);
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
						// 'uid='+getSearchObj().uid+
            'uid='+getSearch('uid')+
						'&mailAddress='+encodeURIComponent(wish['mailAddress'])+
						'&mailHiddenCopy='+encodeURIComponent(wish['mailHiddenCopy'])+
						'&signMail='+(form.inputs['signMail'].checked?1:0),
						function(res){
							form.setPage(2);
						});
			}
		}));

		tabBox.addTab('date', 'Zadat datum', tab1);
		tabBox.addTab('now', 'Ihned', tab2);

		tabBox.setTab('date');

		page.add(tabBox);

		page.addControl('< Zpět', 2);

		page.onOpen = function(){
			cancelSendBtn.style.display = sendScheduled?'inline-block':'none';
			get(loc+'/get/auth.php', function(res){
				if(res=='false') {
					window.onbeforeunload = null;
					location.href = loc+"/login.php?page="+encodeURIComponent(location.href);
				} else {
					let json = JSON.parse(res);
					if(!json.verified){
						form.setPage(2);
						form.setMessage('Učet není ověřen!');
					} else {
						signLabel.innerText = 'Zahrnout mojí adresu ('+json.email+') do textu e-mailu:';
					}
				}
			});
		}

	}));

	// let uid = getSearchObj().uid;
  let uid = getSearch('uid');

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

      let ids = json.infoMode=='list'?json.infoList:json.randomInfoList;

			get(loc+'/get/info.php?bday='+encodeURIComponent(wish.bday)+'&categories='+encodeURIComponent(wish.categories), function(res){
      // get(loc+'/get/info.php?idList='+ids, function(res){

				let json2 = JSON.parse(res);
				for(let row of json2){
					infoCache[row.id] = row;
				}

				form.setPage(2);

				form.inputs['bday'].value = decodeURIComponent(json['bday']);
				form.inputs['for'].value = decodeURIComponent(json['for']);
				form.inputs['from'].value = decodeURIComponent(json['from']);
				form.inputs['wishText'].value = decodeURIComponent(json['wishText']);
				form.inputs['infoCount'].value = decodeURIComponent(json['infoCount']);
				form.inputs['textMode'].setTab(decodeURIComponent(json['textMode']));
				form.inputs['infoMode'].setTab(decodeURIComponent(json['infoMode']));

				let cats = decodeURIComponent(json['categories']).split(',');

				for(let cat of cats){
					form.inputs['categories'].check(cat, true);
				}

        initWish();

			});

		}, function(status, message){
			form.setMessage(message);
		});

	} else {

		form.setPage(0);
		initWish();

	}

	formContainer.appendChild(form);
	form.init();

}

function zoom(amount){
	pageScale -= amount;
	pageScale = Math.min(Math.max(pageScale, 30), 170);
	for(let page of usedPages){
		page.style.width = 'calc('+pageScale+'% - 20px)';
	}
}

function zoomout(){
	if(pageScale>30) pageScale -= 10;
	for(let page of usedPages){
		page.style.width = 'calc('+pageScale+'% - 20px)';
	}
}

function resetZoom(){
	pageScale = 100;
	for(let page of usedPages){
		page.style.width = "calc(100% - 20px)";
	}
}

function zoomin(){
	if(pageScale<170) pageScale += 10;
	for(let page of usedPages){
		page.style.width = 'calc('+pageScale+'% - 20px)';
	}
}
