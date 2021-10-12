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

		<style>
			
		</style>
		
		<script>
			
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
			
			function updateWish(){
				
				let previewHTML = '';
				
				let wish_for = wish['for']??'Milá Alice';
				let wish_from = wish['from']??'Bob';
				let bday = wish['bday']??'42';
				let wishText = wish['wishText']??'Všechno nejlepší!';
				
				let width = preview.getBoundingClientRect().width-40;
				let height = width*Math.sqrt(2);
				
				/* Title page */
				previewHTML += '<div class="wish_page" style="width:'+width+'px;height:'+height+'px;background:#f3eee3">';
				previewHTML += '	<div class="wish_image"><img src="res/cake.png"></img></div>';
				previewHTML += '	<div class="wish_body">';
				if(wish.textMode=='auto'){
					previewHTML += '		<div class="wish_for">'+wish_for+',</div>';
					previewHTML += '		<div class="wish_text">'+wish_from+' ti přeje všechno nejlepší k <b>'+bday+'.</b> narozeninám!</div>';
				} else if(wish.textMode=='custom'){
					previewHTML += '		<div class="wish_text">'+wishText+'</div>';
				}
				previewHTML += '	<div class="wish_text">Na dalších stranách najdeš zajímavosti k číslu tvých narozenin!</div>';
				previewHTML += '	</div>';
				previewHTML += '</div>';
				
				let infos = [];
				if(wish.infoMode=='list' && wish.infoList){
					infos = wish.infoList.split(',');
				} else if(wish.infoMode=='random' && wish.randomInfoList){
					infos = wish.randomInfoList.split(',');
				}
				
				for(let infoId of infos){
					
					let info = wish.infoCache[infoId];
					let background = info.background?info.background:'white';
					let color = info.color?info.color:'black';
					previewHTML += '<div id="page_'+info.id+'" class="wish_page" style="width:'+width+'px;height:'+height+'px;background:'+background+'">';
					previewHTML += '	<p class="info_text" style="color:'+color+'">'+info.content+'</p>';
					previewHTML += '	<a class="info_link" href="'+info.link+'" style="color:'+color+'">'+info.link+'</a>';
					previewHTML += '	<img class="info_img" src="'+info.imgSrc+'"></img>';
					previewHTML += '	<div class="attribution" style="color:'+color+'">'+info.imgAttrib+'</div>';
					previewHTML += '</div>';
					
				}
				
				preview.innerHTML = previewHTML;
				
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
						} else if(wish['bday']<1){
							form.setMessage('Číslo narozenin musí být větší než 0!');
						} else if((wish['textMode']=='auto' && (!wish['for'] || !wish['from'])) || (wish['textMode']=='custom' && !wish['wishText'])){
							form.setMessage('Prosím vyplňte Text přání!');
						} else if(!wish['categories']){
							form.setMessage('Prosím vyberte aspoň jeden zájem!');
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
					
					randomInfoTab.add(createNumberInput(form, 'infoCount', 'Počet zajímavostí:'));
					
					let randomList = createBagList(form, 'randomInfoList', 'Zajímavosti', 'Vybrat jinou zajímavost');
					
					randomInfoTab.add(createButton('Vybrat náhodně', function(){
						let infos = [];
						for(let i in wish.infoCache){
							infos[infos.length] = {name:wish.infoCache[i].id, label:wish.infoCache[i].content};
						}
						randomList.set(infos, wish.infoCount);
					}));
					
					randomInfoTab.add(randomList);
					
					let listInfoTab = createTab();
					
					//let infoList = createCheckList(form, 'infoList', 'Zajímavosti', 'Vybrat všechny');
					let infoList = createDoubleList(form, 'infoList', 'Zajímavosti');
					
					infoList.onSelect = function(name){
						previewBox.scrollTo(0, window['page_'+name].offsetTop-5);
					}
					
					listInfoTab.add(infoList);
					
					infosTabBox.addTab('random', 'Náhodně', randomInfoTab);
					infosTabBox.addTab('list', 'Ze seznamu', listInfoTab);
					
					infosTabBox.setTab('random');
					
					page.add(infosTabBox);
					
					page.onOpen = function(){
						
						infoList.clearItems();
						
						form.setMessage('Načítání zajímavostí...', MESSAGE_STATUS, false);
						
						get(loc+'/get/info.php?bday='+wish.bday+'&categories='+wish.categories, function(res){
							form.clearMessage();
							let json = JSON.parse(res);
							if(json.length==0){
								form.setMessage('Nenalezeny žádné zajímavosti. <a style="color:white" class="link" href="add_info.php">Přidat zajímavost</a>', MESSAGE_WARNING);
							}
							wish.infoCache = {};
							for(let row of json){
								infoList.addItem(row.id, row.content);
								wish.infoCache[row.id] = row;
							}
						});
						
					}
					
					page.addControl('< Zpět', 0);
					page.addControlF('Vytvořit >', function(){
						if((wish['infoMode']=='random' && !wish['randomInfoList']) || (wish['infoMode']=='list' && !wish['infoList'])) {
							form.setMessage('Prosím vyberte aspoň jednu zajímavost!');
						} else {
							form.setMessage('Vytváření přání...', MESSAGE_STATUS, false);
							let get = '';
							let uid = getSearchObj().uid;
							if(uid){
								get = '?uid='+uid;
							}
							post(loc+'/makepdf.php'+get,
									'bday='+(wish['bday']??'')+
									'&textMode='+wish['textMode']+
									'&for='+(wish['for']??'')+
									'&from='+(wish['from']??'')+
									'&wishText='+(wish['wishText']??'')+
									'&categories='+(wish['categories']??'')+
									'&infoMode='+wish['infoMode']+
									'&infoList='+(wish['infoList']??'')+
									'&infoCount='+(wish['infoCount']??'')+
									'&randomInfoList='+(wish['randomInfoList']??''),
									function(res){
										let json = JSON.parse(res);
										setSearchText('uid='+json.uid);
										form.setPage(2);
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
						
						// TODO get dl link
						
						get(loc+'/get/wish.php?uid='+getSearchObj().uid, function(res){
							
							let json = JSON.parse(res);
							
							dlbox.innerHTML = '<a href="'+loc+'/'+json.document+'" download="Přání.pdf"><button class="formrow action">Stáhnout přání</button></a>';
							
							if(json.mail_sent=='1'){
								//dlbox.appendChild(document.createTextNode('Přání již bylo odelsáno.'));
								dlbox.innerHTML += '<div class="formrow"><span class="formlbl">Přání již bylo odelsáno.</span></div>';
							} else {
								
								wish['mailAddress'] = json['mail_address'];
								wish['mailHiddenCopy'] = json['mail_hidden'];
								wish['mailDate'] = json['mail_date'];
								
								form.inputs['mailAddress'].value = json['mail_address'];
								form.inputs['mailHiddenCopy'].value = json['mail_hidden'];
								form.inputs['mailDate'].value = json['mail_date'];
								
								if(json.mail_date){
									//dlbox.appendChild(document.createTextNode('Přání bude odesláno '+json.mail_date));
									dlbox.innerHTML += '<div class="formrow"><span class="formlbl">Přání bude odesláno '+json.mail_date+'</span></div>';
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
						} else if(!(mailDate>new Date() && mailDate<nextYear)){
							form.setMessage('Neplatné datum!');
						} else {
							post(loc+'/post/schedule_send.php',
									'uid='+getSearchObj().uid+
									'&mailAddress='+wish['mailAddress']+
									'&mailHiddenCopy='+wish['mailHiddenCopy']+
									'&date='+wish['mailDate'],
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
						} else {
							post(loc+'/post/send_mail.php',
									'uid='+getSearchObj().uid+
									'&mailAddress='+wish['mailAddress']+
									'&mailHiddenCopy='+wish['mailHiddenCopy'],
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
								location.href = loc+"/login.php?page="+location.href;
							} else {
								let json = JSON.parse(res);
							}
						});
					}
					
				}));
				
				let uid = getSearchObj().uid;
				
				if(uid){
					
					get(loc+'/generated/json/'+uid+'.json', function(res){
						
						let json = JSON.parse(res);
						
						wish['bday'] = json['bday'];
						wish['textMode'] = json['textMode'];
						wish['for'] = json['for'];
						wish['from'] = json['from'];
						wish['wishText'] = json['wishText'];
						wish['categories'] = json['categories'];
						wish['infoMode'] = json['infoMode'];
						wish['infoList'] = json['infoList'];
						wish['infoCount'] = json['infoCount'];
						wish['randomInfoList'] = json['randomInfoList'];
						
						get(loc+'/get/info.php?bday='+wish.bday+'&categories='+wish.categories, function(res){
							
							let json2 = JSON.parse(res);
							wish.infoCache = {};
							for(let row of json2){
								wish.infoCache[row.id] = row;
							}
							
							form.setPage(2);
							
							updateWish();
							
							form.inputs['bday'].value = json['bday'];
							form.inputs['for'].value = json['for'];
							form.inputs['from'].value = json['from'];
							form.inputs['wishText'].value = json['wishText'];
							
							form.inputs['textMode'].setTab(json['textMode']);
							form.inputs['infoMode'].setTab(json['infoMode']);
							
							let cats = json['categories'].split(',');
							
							for(let cat of cats){
								form.inputs['categories'].check(cat, true);
							}
							
						});
						
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
					
					<div id="preview"></div>
					
				</div>
				
			</div>

		</div>

    </body>

</html>
<?php

?>