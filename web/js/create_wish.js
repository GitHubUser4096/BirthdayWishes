
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

	let wish_for = wish['for']||'Mil?? Marie';
	let wish_from = wish['from']||'Ji????';
	let bday = wish['bday']||'42';
	let wishText = wish['wishText']||'V??echno nejlep????!';

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
		textPos += drawWrappedText(page1.ctx, wish_from+' ti p??eje v??echno nejlep???? k '+bday+'. narozenin??m!', 40, 20, textPos, page1.width-40)+20;
	} else if(wish.textMode=='custom'){
		textPos += drawWrappedText(page1.ctx, wishText, 40, 20, textPos, page1.width-40)+20;
	}

	textPos += drawWrappedText(page1.ctx, 'Na dal????ch stran??ch najde?? zaj??mavosti k ????slu tv??ch narozenin!', 30, 20, textPos, page1.width-40);

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
			form.setMessage('N??kter?? z pou??it??ch zaj??mavosti nejsou moment??ln?? dostupn??.', MESSAGE_WARNING);
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
	textPos += drawWrappedText(endPage.ctx, 'P????n?? pomohl vytvo??it web Narozeninov?? p????n??.', 36, 20, textPos, endPage.width-40)+20;
	textPos += drawWrappedText(endPage.ctx, 'Chcete sv??mu bl??zk??mu ud??lat radost n??????m netradi??n??m?\nPop??ejte mu formou p????n?? zaslan??ho v den narozenin.', 28, 20, textPos, endPage.width-40)+20;
	let list = ['Sestavte si p????n?? z vtipn??ch i seri??zn??ch zaj??mavost?? souvisej??c??ch s oslavencov??m v??kem.',
			'Napl??nujte odesl??n?? p????n?? dop??edu a pus??te to z hlavy.',
			'Tvo??te obsah webu s n??mi ??? zaregistrujte se a vkl??dejte vlastn?? zaj??mavosti.'];
	for(item of list){
		endPage.ctx.beginPath();
		endPage.ctx.arc(50, textPos+25, 5, 0, Math.PI*2);
		endPage.ctx.fill();
		textPos += drawWrappedText(endPage.ctx, item, 28, 80, textPos, endPage.width-100)+20;
	}
	textPos += drawWrappedText(endPage.ctx, 'Je to opravdu jednoduch?? :)', 28, 20, textPos, endPage.width-40)+20;
	textPos += drawWrappedText(endPage.ctx, 'Vytvo??te p????n?? na '+loc, 36, 20, textPos, endPage.width-40)+20;

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

		// page.critical = true;

		page.add(createNumberInput(form, 'bday', 'Pro kolik??t?? narozeniny:', '42', 1, 199, null, 'Ke kolik??t??m narozenin??m p??ejete, k takov??mu ????slu se budou nab??zet zaj??mavosti do p????n??.'));

		let textModeTabBox = createTabBox(form, 'textMode', 'Text p????n??:', 'Text na prvn?? stran?? p????n?? si nechte rychle vygenerovat nebo napi??te vlastn??.');

		let tab1 = createTab();
		tab1.add(createTextInput(form, 'for', 'Osloven??:', 'Mil?? Marie'));
		tab1.add(createTextInput(form, 'from', 'Kdo p??eje:', 'Ji????'));
		textModeTabBox.addTab('auto', 'Generovan??', tab1, 'Zad??te osloven?? a va??e jm??no. Text p????n?? se vygeneruje, v??e vid??te v n??hledu.');

		let tab2 = createTab();
		tab2.add(createTextArea(form, 'wishText', 'Text:', 'V??echno nejlep????!'));
		textModeTabBox.addTab('custom', 'Vlastn??', tab2, 'Nap????ete vlastn?? text p????n??, v??e vid??te v n??hledu.');

		textModeTabBox.setTab('auto');

		page.add(textModeTabBox);

		let catList = createCheckList(form, 'categories', 'Z??jmy oslavence:', 'Vybrat v??echny', 'Vyberte z??jmy oslavence. Nab??dnou se jen zaj??mavosti, kter?? souvis?? s vybran??mi z??jmy.')
		page.add(catList);

		let cancelEditBtn = createButton('Zru??it ??pravy ??', function(){
			// window.onbeforeunload = null;
			location.reload();
		});

		page.confirmExit = false;

		page.onOpen = function(){
			// cancelEditBtn.style.display = (getSearchObj().uid!=null)?'inline-block':'none';
      cancelEditBtn.style.display = (getSearch('uid')!=null)?'inline-block':'none';
		}

		page.onChange = function(){
			page.confirmExit = true;
		}

		page.addControlB(cancelEditBtn);

		page.addControlF('Dal???? >', function(){
			if(!wish['bday']){
				form.setMessage('Kolonka ???Pro kolik??t?? narozeniny??? nen?? vypln??na!');
			} else if(wish['bday']!=Math.floor(wish['bday'])){
				form.setMessage('Neplatn?? ????slo narozenin!');
			} else if(wish['bday']<1){
				form.setMessage('????slo narozenin mus?? b??t v??t???? ne?? 0!');
			} else if(wish['textMode']=='auto' && (!wish['for'] || !wish['from'])){
				if(!wish['for']){
					form.setMessage('Kolonka ???Osloven????? v Oblasti ???Text p????n????? nen?? vypln??na!');
				} else {
					form.setMessage('Kolonka ???Kdo p??eje??? v Oblasti ???Text p????n????? nen?? vypln??na!');
				}
			} else if(wish['textMode']=='custom' && !wish['wishText']){
				form.setMessage('Kolonka ???Text??? v Oblasti ???Text p????n????? nen?? vypln??na!');
			} else if(!wish['categories']){
				form.setMessage('Pros??m vyberte aspo?? jeden z??jem!');
			} else if((wish['textMode']=='auto' && (wish['for']+wish['from']).length>209) || (wish['textMode']=='custom' && wish['wishText'].length>255)){
				form.setMessage('Text p????n?? je p????li?? dlouh??!');
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

		// page.critical = true;
		page.confirmExit = true;

		let infosTabBox = createTabBox(form, 'infoMode', 'Zaj??mavosti:', 'Zaj??mavosti (dle ????sla narozenin a z??jm??) se vyberou n??hodn?? nebo je vyberete ze seznamu.');

		let randomInfoTab = createTab();

		let infoCountIn = createNumberInput(form, 'infoCount', 'Kolik zaj??mavost?? vybrat:', 1, 1, 100, 1);

		randomInfoTab.add(infoCountIn);

		let randomList = createBagList(form, 'randomInfoList', 'Zaj??mavosti', 'Vybrat jinou zaj??mavost');

		randomInfoTab.add(createButton('Vybrat n??hodn??', function(){
			let infos = [];
			for(let i in infoCache){
				if(infoCache[i].number==wish['bday']) infos[infos.length] = {name:infoCache[i].id, label:deesc(infoCache[i].content)};
			}
			randomList.set(infos, wish.infoCount);
			highlight = 1;
		}));

		randomInfoTab.add(randomList);

		let listInfoTab = createTab();

		let infoList = createDoubleList(form, 'infoList', 'Zaj??mavosti');
		infoList.setSelectedHint('Tyto zaj??mavosti budou zahrnuty do p????n??. M????ete m??nit jejich po??ad?? nebo je z v??b??ru odebrat.');
		infoList.setToSelectHint('Zaj??mavost vyberete kliknut??m. Uvid??te ji v n??hledu.');

		infoList.onSelect = function(name){
			highlight = infoList.getSelected().indexOf(name)+1;
			if(highlight<0) highlight = null;
		}

		listInfoTab.add(infoList);

		infosTabBox.addTab('random', 'Vybrat n??hodn??', randomInfoTab, 'Web za v??s vybere v??mi zadan?? po??et zaj??mavost?? dle ????sla narozenin a z??jm??. M????ete m??nit jejich po??ad??.\nPokud se v??m zaj??mavost nel??b??, web v??m m????e nab??dnout jinou.');
		infosTabBox.addTab('list', 'Vyberu s??m', listInfoTab, 'Web nab??dne seznam zaj??mavost?? dle ????sla narozenin a z??jm??, a vy z nich vyberete ty, kter?? by se oslavenci mohly l??bit.');

		infosTabBox.setTab('random');

		page.add(infosTabBox);

		page.onOpen = function(){

			infoList.clearItems();

			form.setMessage('Na????t??n?? zaj??mavost??...', MESSAGE_STATUS, false);

			get(loc+'/get/info.php?bday='+encodeURIComponent(wish.bday)+'&categories='+encodeURIComponent(wish.categories), function(res){
				form.clearMessage();
				let json = JSON.parse(res);
				if(json.length==0){
					form.setMessage('Pro dan?? ????slo a vybran?? z??jmy zat??m nen?? ????dn?? zaj??mavost k dispozici. <a style="color:white" class="link" href="add_info.php">P??idat zaj??mavost</a>', MESSAGE_WARNING);
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

		page.addControl('< Zp??t', 0);
		page.addControlF('Vytvo??it >', function(){
			if(infoList.getSelected().length>INFO_LIMIT){
				form.setMessage('Pros??m vyberte '+INFO_LIMIT+' nebo m??n?? zaj??mavost??');
			} else if((wish['infoMode']=='random' && !wish['randomInfoList']) || (wish['infoMode']=='list' && !wish['infoList'])) {
				form.setMessage('Pros??m vyberte alespo?? jednu zaj??mavost!');
			} else {
				// console.log('creating wish', performance.now());
				form.setMessage('Vytv????en?? p????n?? (m????e trvat n??kolik vte??in)', MESSAGE_STATUS, false);
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
								form.setMessage('Nelze vytvo??it p????n?? (chyba webu)');
								form.noblackout();
							}
						}, function(error, message){
							console.error('Server responded with error: ', error, message);
							form.setMessage('Nelze vytvo??it p????n?? (chyba serveru nebo s??t??)');
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

				// dlbox.innerHTML = '<button class="formrow action"><a href="'+loc+'/get/wish_pdf.php?uid='+getSearchObj().uid+'" download="P????n??.pdf" style="color:white;">St??hnout PDF</a></button>';
        // dlbox.innerHTML = '<button class="formrow action"><a href="'+loc+'/get/wish_pdf.php?uid='+getSearch('uid')+'" download="P????n??.pdf" style="color:white;">St??hnout PDF</a></button>';
				let dlDiv = document.createElement('div');
				let dlBtn = createButton('St??hnout PDF', function(){
					let a = document.createElement('a');
					a.href = loc+'/get/wish_pdf.php?uid='+getSearch('uid');
					// a.target = '_blank';
					a.download = 'P????n??.pdf';
					a.click();
				});
				dlBtn.classList.add('wide');
				dlDiv.appendChild(dlBtn);
				dlDiv.appendChild(createHint('St??hnete vygenerovan?? pdf - jeho n??hled vid??te'));
				dlbox.appendChild(dlDiv);

				if(json.mail_sent=='1'){
					// dlbox.innerHTML += '<div class="formrow"><span class="formlbl">P????n?? bylo odesl??no.</span></div>';
					let div = document.createElement('div');
					div.className = 'formrow';
					div.innerText = 'P????n?? bylo odesl??no.';
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

						// dlbox.innerHTML += '<div class="formrow"><span class="formlbl">P????n?? bude odesl??no '+decodeURIComponent(json.mail_date)+'</span></div>';
						let row = document.createElement('div');
						row.className = 'formrow';
						row.innerText = 'P????n?? bude odesl??no '+decodeURIComponent(json.mail_date);
						dlbox.appendChild(row);

						let sendDiv = document.createElement('div');
						let sendBtn = createButton('Zm??nit odesl??n??', function(){
							form.setPage('mail');
						});
						sendBtn.classList.add('wide');
						sendDiv.appendChild(sendBtn);
						sendDiv.appendChild(createHint('Zm??nit nebo zru??it kdy a na jak?? email pdf odeslat'));
						dlbox.appendChild(sendDiv);

					} else {
						let sendDiv = document.createElement('div');
						let sendBtn = createButton('Odeslat p????n??', function(){
							form.setPage('mail');
						});
						sendBtn.classList.add('wide');
						sendDiv.appendChild(sendBtn);
						sendDiv.appendChild(createHint('Nadefinujete kdy a na jak?? email pdf odeslat'));
						dlbox.appendChild(sendDiv);
					}

					let editDiv = document.createElement('div');
					let editBtn = createButton('Upravit p????n??', function(){
						form.setPage(0);
					});
					editBtn.classList.add('wide');
					editDiv.appendChild(editBtn);
					editDiv.appendChild(createHint('Vr??t?? v??s do pr??vodce vytvo??en??m p????n??'));
					dlbox.appendChild(editDiv);

				}

			});

		}

	}));

	/* Mail Page */

	form.addPage('mail', createFormPage(form, function(page){

		// page.critical = true;

		let ta = createTextArea(form, 'mailAddress', 'E-mail:', 'marie@email.cz\nkarel.novak@priklad.cz');
		ta.setHint('E-mailov?? adresy, na kter?? bude p????n?? odesl??no - budou zobrazeny jako p????jemci e-mailu.\nM????ete zadat v??ce adres, ka??dou na vlastn?? ????dek. Zadejte alespo?? jednu adresu.');
		page.add(ta);
		let ta2 = createTextArea(form, 'mailHiddenCopy', 'Skryt?? kopie:');
		ta2.setHint('E-mailov?? adresy, na kter?? bude p????n?? odesl??no - nebudou zobrazeny v e-mailu.\nM????ete zadat v??ce adres, ka??dou na vlastn?? ????dek. Nemus?? b??t vypln??no.');
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
		signLabel.innerText = 'Zahrnout moj?? adresu do textu e-mailu:';
		sign.appendChild(signLabel);
		sign.appendChild(signCheckbox);
		signDiv.appendChild(sign);
		page.add(signDiv);

		// signCheckbox.checked = wish['sign'];

		let tabBox = createTabBox(form, 'mailMode', 'Kdy odeslat e-mail:', 'M?? se p????n?? odeslat hned nebo a?? r??no zadan??ho dne?');

		let tab1 = createTab();
		tab1.add(createDateInput(form, 'mailDate', 'Datum:', 'Datum, kdy bude p????n?? odesl??no. Mus?? b??t nejd????ve z??tra a nejpozd??ji za 1 rok.'));
		tab1.add(createButton('Ulo??it', function(){
			let mailDate = new Date(wish['mailDate']);
			let nextYear = new Date(new Date().setDate(new Date().getDate()+365));
			if(!wish['mailAddress']){
				form.setMessage('Pros??m vypl??te E-mail!');
			} else if(wish['mailAddress'].length>100){
				form.setMessage('E-mail je p????li?? dlouh??!');
			} else if(wish['mailHiddenCopy'].length>100){
				form.setMessage('Skryt?? kopie je p????li?? dlouh??!');
			} else if(!checkAddresses(wish['mailAddress'])){
				form.setMessage('Neplatn?? e-mail!');
			} else if(wish['mailHiddenCopy']&&!checkAddresses(wish['mailHiddenCopy'])){
				form.setMessage('Neplatn?? skryt?? kopie!');
			} else if(!(mailDate>new Date() && mailDate<nextYear)){
				form.setMessage('Neplatn?? datum!');
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
		let cancelSendBtn = createButton('Zru??it odesl??n??', function(){
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
		filler.innerHTML = 'Kliknut??m na tla????tko potvrd??te odesl??n??';
		tab2.add(filler);
		tab2.add(createButton('Odeslat', function(){
			if(!wish['mailAddress']){
				form.setMessage('Pros??m vypl??te E-mail!');
			} else if(wish['mailAddress'].length>100){
				form.setMessage('E-mail je p????li?? dlouh??!');
			} else if(wish['mailHiddenCopy'].length>100){
				form.setMessage('Skryt?? kopie je p????li?? dlouh??!');
			} else if(!checkAddresses(wish['mailAddress'])){
				form.setMessage('Neplatn?? e-mail!');
			} else if(wish['mailHiddenCopy']&&!checkAddresses(wish['mailHiddenCopy'])){
				form.setMessage('Neplatn?? skryt?? kopie!');
			} else {
				form.setMessage('Odes??l??n??', MESSAGE_STATUS, false);
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

		page.addControl('Zru??it ??', 2, ()=>page.confirmExit);

		page.onOpen = function(){
			page.confirmExit = false;
			cancelSendBtn.style.display = sendScheduled?'inline-block':'none';
			get(loc+'/get/auth.php', function(res){
				if(res=='false') {
					// window.onbeforeunload = null;
					location.href = loc+"/login.php?page="+encodeURIComponent(location.href);
				} else {
					let json = JSON.parse(res);
					if(!json.verified){
						form.setPage(2);
						form.setMessage('U??et nen?? ov????en!');
					} else {
						signLabel.innerText = 'Zahrnout moj?? adresu ('+json.email+') do textu e-mailu:';
					}
				}
			});
		}

		page.onChange = function(){
			page.confirmExit = true;
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
