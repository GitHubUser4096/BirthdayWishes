<?php
/*
 * Projekt: Narozeninová přání
 * Vytvořil: Michal
 */
session_start();

if(!isSet($_SERVER['HTTPS'])){
	header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']);
	exit;
}

require_once('php/db.php');

$db = DB_CONNECT();

if(!isSet($_SESSION['user'])) {
	header('Location: login.php?page=info_mgmt.php');
	exit;
}

if(!$_SESSION['user']['verified']){
	die('Účet není ověřen!');
}

if(!$_SESSION['user']['admin']) {
	die('401 - Unauthorized');
}

if($_SERVER['REQUEST_METHOD']==='POST') {

	if(isSet($_POST['change_state'])) {

		$id = $_POST['id'];
		$state = $_POST['state'];

		$stmt = $db->prepare('update NumberInfo set state=? where id=?');
		$stmt->bind_param("si", $state, $id);
		$stmt->execute();
		$stmt->close();

	}

}

?>
<!doctype html>
<html lang="cs">

	<head>

		<title>Spravovat zajímavosti</title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<link rel="icon" href="res/cake.png">
		<link rel="stylesheet" href="css/page.css">
		<link rel="stylesheet" href="css/titlebar.css">
		<script src="js/titlebar.js"></script>
		<script src="js/xhr.js"></script>

		<style>

			table {
				border-collapse: collapse;
			}

			th, td {
				border: solid 1px;
			}

			img {
				max-width: 100px;
				vertical-align: top;
			}

			.link {
				color: blue;
			}

			.content {
				position: absolute;
				width: 100%;
				height: calc(100% - 80px);
				background: #e6e2d7;
				overflow: hidden;
			}

			.subtitlebar {
				width: 100%;
				height: 40px;
			}

			.tablecont {
				width: 100%;
				height: calc(100% - 40px);
			}

			.tableheadcont {
				width: 100%;
				height: 30px;
			}

			.tablebodycont {
				width: 100%;
				height: calc(100% - 40px);
				overflow-y: overlay;
			}

			table {
				width: 100%;
			}

			/* thead {
				position: fixed;
			} */

			/* .col {
				position: relative;
			} */

			.col1 { width: 45px; text-align: center; }
			.col2 { width: auto; min-width: 200px; }
			.col3 { width: 100px }
			.col4 { width: 50px }
			.col5 { width: 120px }
			.col6 { width: 90px }
			.col7 { width: 100px }
			.col8 { width: 80px }
			.col9 { width: 80px }
			.col10 { width: 60px }
			.col11 { width: 60px }

			.editbtn {
				text-decoration: underline;
			}

			.subtitlebar {
				font-size: 24px;
			}

			.subtitle {
				padding: 10px;
			}

			.backbtn {
				padding: 10px;
			}

			.right {
				float: right;
			}

			.addbtn {
				margin: 5px;
				width: 30px;
				height: 30px;
				font-size: 20px;
				border: none;
				background: #2edc15;
				color: white;
				cursor: pointer;
			}

			.addbtn:hover {
				background: #7be96c;
			}

			.clearFilersImg {
				margin-top: 4px;
			}

			.warn {
				padding: 10px;
				font-weight: bold;
				font-size: 18px;
				color: white;
				background: #EECC00;
			}

			.tableDiv {
				width: 100%;
				height: calc(100vh - 80px - 41px);
				overflow: auto;
			}

			td, th {
				padding: 5px;
			}

			.filter {
				position: absolute;
				z-index: 99;
				background: #e6e2d7;
				border: 1px solid black;
				display: none;
				top: 165px;
				padding-left: 10px;
				padding-right: 10px;
			}

			/* #stateFilter {
				right: 20px;
			}

			#categoryFilter {
				right: 550px;
			}

			#createdByFilter {
				right: 400px;
			}

			#modifiedByFilter {
				right: 200px;
			} */

			#categoryFilter {
				min-width: 200px;
			}

			.stateBtn {
				padding: 0;
				border: none;
				background: none;
				cursor: pointer;
			}

			.state .stateImg {
				display: none;
			}

			.state[state="pending"] .pending {
				display: block;
			}
			.state[state="approved"] .approved {
				display: block;
			}
			.state[state="dismissed"] .dismissed {
				display: block;
			}
			.state[state="unknown"] .unknown {
				display: block;
			}

			.categoryFilterCategoryList {
				max-height: calc(100vh - 300px);
				overflow: auto;
			}

			@media only screen and (max-width: 600px) {

				.content {
					height: calc(100% - 60px);
				}

				.tableDiv {
					height: calc(100vh - 60px - 41px);
				}

			}

			@media only screen and (max-height: 500px) {

				.content {
					height: calc(100% - 60px);
				}

				.tableDiv {
					height: calc(100vh - 60px - 41px);
				}

			}

		</style>

		<script type="text/javascript">

		let filters;
		let params = new URLSearchParams(location.search);

		function main(){
			filters = {
				'number': numberFilter,
				'state': stateFilter,
				'category': categoryFilter,
				'createdBy': createdByFilter,
				'modifiedBy': modifiedByFilter,
			};
		}

		function getParam(name){
			return params.get(name);
		}

		function deleteParam(name){
			params.delete(name);
		}

		function setParam(name, value){
			params.set(name, value);
		}

		function updateQuery(){
			location.search = params.toString();
		}

		function setState(id, state){

			let disp = document.querySelector('#state'+id);
			let prevState = disp.getAttribute('state');
			disp.setAttribute('state', 'unknown');

			post('post/set_state.php', 'state='+state+'&id='+id, function(result){
				if(result=='success'){
					disp.setAttribute('state', state);
				} else {
					disp.setAttribute('state', prevState);
				}
			}, function(){
				disp.setAttribute('state', prevState);
			});

		}

		function checkAll(name, check){
			let items = document.getElementsByClassName(name);
			for(let item of items){
				item.checked = check;
			}
		}

		function getCheckList(name){
			let res = [];
			let items = document.getElementsByClassName(name);
			for(let item of items){
				if(item.checked) res.push(item.value);
			}
			return res.join(',');
		}

		function sort(name){
			deleteParam('page');
			setParam('sort', name);
			updateQuery();
		}

		function filter(name, value){
			deleteParam('page');
			setParam(name, value);
			updateQuery();
		}

		function noFilter(name){
			deleteParam('page');
			deleteParam(name);
			updateQuery();
		}

		function showFilter(name, element){
			for(let filter in filters){
				hideFilter(filter);
			}
			let bounds = element.getBoundingClientRect();
			filters[name].style.left = bounds.left+'px';
			filters[name].style.right = null;
			filters[name].style.display = 'block';
			let filterBounds = filters[name].getBoundingClientRect();
			if(filterBounds.right>window.visualViewport.width){
				filters[name].style.left = null;
				filters[name].style.right = '0px';
			}
		}

		function hideFilter(name){
			filters[name].style.display = 'none';
		}

		</script>

	</head>

    <body onload="main();">

		<?php include('php/titlebar.php'); ?>

		<div class="content">

			<div class="subtitlebar">
				<a class="backbtn" href="index.php"><</a><span class="subtitle">Spravovat zajímavosti</span>
				<span class="right">
					<a href="info_mgmt.php" title="Zrušit filtry/řazení"><img class="clearFilersImg" src="res/clear_filter.png"></img></a>
					<a href="add_info.php" title="Přidat zajímavost"><button class="addbtn">+</button></a>
				</span>
			</div>

			<div class="tableDiv">

				<?php

				function queryValue($db, $query, ...$args) {

			  	$stmt = $db->prepare($query);
			  	if(!$stmt) die('Invalid statement: '.$query);
			  	if(count($args)>0){
			  		$stmt->bind_param(str_repeat("s", count($args)), ...$args);
			  	}
			  	$executed = $stmt->execute();
			  	if(!$executed) die('Failed executing statement '.$query.': '.$this->db->error);
			  	$res = $stmt->get_result();
			  	$stmt->close();

			  	if(!$res) return false;

					return $res->fetch_row()[0];

			  }

				function query($db, $query, ...$args) {

			  	$stmt = $db->prepare($query);
			  	if(!$stmt) die('Invalid statement: '.$query);
			  	if(count($args)>0){
			  		$stmt->bind_param(str_repeat("s", count($args)), ...$args);
			  	}
			  	$executed = $stmt->execute();
			  	if(!$executed) die('Failed executing statement '.$query.': '.$db->error);
			  	$res = $stmt->get_result();
			  	$stmt->close();

			  	if(!$res) return false;

					return $res->fetch_all(MYSQLI_ASSOC);

			  }

				$where = '';
				$params = [];
				if(isSet($_GET['number'])) {
					$where .= 'where number=?';
					$params[] = $_GET['number'];
				}
				if(isSet($_GET['state'])){
					$where .= (strlen($where)>0?' and':'where').' state=?';
					$params[] = $_GET['state'];
				}
				if(isSet($_GET['createdBy'])){
					$where .= (strlen($where)>0?' and':'where').' createdBy=(select id from User where username=?)';
					$params[] = $_GET['createdBy'];
				}
				if(isSet($_GET['modifiedBy'])){
					$where .= (strlen($where)>0?' and':'where').' modifiedBy=(select id from User where username=?)';
					$params[] = $_GET['modifiedBy'];
				}
				if(isSet($_GET['category'])){
					$where .= (strlen($where)>0?' and':'where').' id in (select infoId from InfoCat where InfoCat.catId in ('.$db->real_escape_string($_GET['category']).'))';
					// $params[] = '('.$_GET['category'].')';
				}

				// $stmt = $db->prepare('select count(*) from NumberInfo '.$where);
				// if(isSet($_GET['number'])) $stmt->bind_param('i', $_GET['number']);
				// $stmt->execute();
				// $res = $stmt->get_result();
				// $stmt->close();
				// $totalRows = $res->fetch_row()[0];

				$totalRows = queryValue($db, 'select count(*) from NumberInfo '.$where, ...$params);

				$sort = isSet($_GET['sort'])?$_GET['sort']:'number';
				if($sort!='number' && $sort!='createdTime desc' && $sort!='modifiedTime desc') $sort = 'number';
				$page = isSet($_GET['page'])?$_GET['page']:0;
				$offset = $page*10;

				// $stmt = $db->prepare('select id, number, content, background, color, imgSrc, createdBy, createdTime, state from NumberInfo '.$where.' order by number limit 10 offset ?');
				// if(isSet($_GET['number'])) $stmt->bind_param('i', $_GET['number']);
				// $stmt->bind_param('i', $offset);
				// $stmt->execute();
				// $res = $stmt->get_result();
				// $stmt->close();

				// $params[] = $sort;
				$params[] = $offset;

				// $row = $res->fetch_assoc();
				$rows = query($db, 'select id, number, content, background, color, imgSrc, createdBy, createdTime, modifiedBy, modifiedTime, state from NumberInfo '
						.$where.' order by '.$db->real_escape_string($sort).' limit 10 offset ?', ...$params);
				$count = 0;

				// if(!$row){
				// if(!$rows){
				// ? ><div class="warn">Nenalezeny žádné zajímavosti.</div><?php
				// } else {
				{
				?>

					<table>
						<thead>
							<tr>
								<th class="col1">
									Číslo
									<span title="filtrovat" onclick="showFilter('number', this);">
										<?php if(isSet($_GET['number'])) { ?><img src="res/filter_on.png"></img>
										<?php } else { ?><img src="res/filter.png"></img><?php } ?>
									</span>
									<span title="seřadit" onclick="sort('number');">
										<?php if($sort=='number') { ?><img src="res/sort_on.png"></img>
										<?php } else { ?><img src="res/sort.png"></img><?php } ?>
									</span>
								</th>
								<th class="col col2">Popis</th>
								<th class="col col3">Obrázek</th>
								<th class="col col4">Barva</th>
								<th class="col col5">
									Kategorie
									<span title="filtrovat" onclick="showFilter('category', this);">
										<?php if(isSet($_GET['category'])) { ?><img src="res/filter_on.png"></img>
										<?php } else { ?><img src="res/filter.png"></img><?php } ?>
									</span>
								</th>
								<th class="col col6">
									Vytvořil
									<span title="filtrovat" onclick="showFilter('createdBy', this);">
										<?php if(isSet($_GET['createdBy'])) { ?><img src="res/filter_on.png"></img>
										<?php } else { ?><img src="res/filter.png"></img><?php } ?>
									</span>
								</th>
								<th class="col col7">
									Datum vytvoření
									<span title="seřadit" onclick="sort('createdTime desc');">
										<?php if($sort=='createdTime desc') { ?><img src="res/sort_on.png"></img>
										<?php } else { ?><img src="res/sort.png"></img><?php } ?>
									</span>
								</th>
								<th class="col col8">
									Upravil
									<span title="filtrovat" onclick="showFilter('modifiedBy', this);">
										<?php if(isSet($_GET['modifiedBy'])) { ?><img src="res/filter_on.png"></img>
										<?php } else { ?><img src="res/filter.png"></img><?php } ?>
									</span>
								</th>
								<th class="col col9">
									Datum úpravy
									<span title="seřadit" onclick="sort('modifiedTime desc');">
										<?php if($sort=='modifiedTime desc') { ?><img src="res/sort_on.png"></img>
										<?php } else { ?><img src="res/sort.png"></img><?php } ?>
									</span>
								</th>
								<th class="col col10">
									Stav
									<span title="filtrovat" onclick="showFilter('state', this);">
										<?php if(isSet($_GET['state'])) { ?><img src="res/filter_on.png"></img>
										<?php } else { ?><img src="res/filter.png"></img><?php } ?>
									</span>
								</th>
								<th class="col11">Upravit</th>
							</tr>
						</thead>
					<!-- </table>
					<table> -->
						<tbody>
							<?php

								// while($row){
								while($count<count($rows)){
									$row = $rows[$count];
									?>
									<tr>
										<td class="col col1"><?php echo $row['number']; ?></td>
										<td class="col col2"><?php echo $row['content']; ?></td>
										<td class="col col3"><img src="<?php echo $row['imgSrc']; ?>"></img></td>
										<td class="col col4"><span style="background:<?php echo $row['background']?>;color:<?php echo $row['color']?>">Barva</span></td>
										<td class="col col5"><?php

											$stmt = $db->prepare('select name from InfoCat inner join Category on Category.id=catid where infoid=? order by name');
											$stmt->bind_param('i', $row['id']);
											$stmt->execute();
											$res2 = $stmt->get_result();
											$stmt->close();

											while($row2 = $res2->fetch_assoc()){
												echo $row2['name'].'<br>';
											}

										?></td>
										<td class="col col6"><?php

											$stmt = $db->prepare('select username from User where id=?');
											$stmt->bind_param('i', $row['createdBy']);
											$stmt->execute();
											$res2 = $stmt->get_result();
											$stmt->close();

											while($row2 = $res2->fetch_assoc()){
												echo $row2['username'].'<br>';
											}

										?></td>
										<td class="col col7"><?php

											echo $row['createdTime'];

										?></td>
										<td class="col col8"><?php

											$stmt = $db->prepare('select username from User where id=?');
											$stmt->bind_param('i', $row['modifiedBy']);
											$stmt->execute();
											$res2 = $stmt->get_result();
											$stmt->close();

											while($row2 = $res2->fetch_assoc()){
												echo $row2['username'].'<br>';
											}

										?></td>
										<td class="col col9"><?php

											echo $row['modifiedTime'];

										?></td>
										<td class="col col10">
											<?php
												// if($row['state']=='pending') echo '<img title="Před schválením" src="res/pending.png"></img>';
												// else if($row['state']=='approved') echo '<img title="Schváleno" src="res/approved.png"></img>';
												// else if($row['state']=='dismissed') echo '<img title="Zamítnuto" src="res/dismissed.png"></img>';
											?>
											<span id="state<?php echo $row['id']; ?>" class="state" state="<?php echo $row['state']; ?>">
												<img class="stateImg pending" title="Před schválením" src="res/pending.png"></img>
												<img class="stateImg approved" title="Schváleno" src="res/approved.png"></img>
												<img class="stateImg dismissed" title="Zamítnuto" src="res/dismissed.png"></img>
												<img class="stateImg unknown" title="Neznámý" src="res/wait.gif"></img>
											</span>
											<!-- <input name="id" value="< ?php echo $row['id']; ?>" type="hidden"></input>
											<input name="change_state" value="true" type="hidden"></input> -->
											<button onclick="setState(<?php echo $row['id']; ?>, 'approved');" class="stateBtn" title="Schválit"><img src="res/approve.png"></img></button>
											<button onclick="setState(<?php echo $row['id']; ?>, 'dismissed');" class="stateBtn" title="Zamítnout"><img src="res/dismiss.png"></img></button>
											<!-- <form method="post">
												<input name="id" value="< ?php echo $row['id']; ?>" type="hidden"></input>
												<input name="change_state" value="true" type="hidden"></input>
												<button style="padding:0;border:none;background:none;cursor:pointer;" title="Schválit" name="state" value="approved"><img src="res/approve.png"></img></button>
												<button style="padding:0;border:none;background:none;cursor:pointer;" title="Zamítnout" name="state" value="dismissed"><img src="res/dismiss.png"></img></button>
											</form> -->
										</td>
										<td class="col col11"><a class="editbtn" href="edit_info.php?id=<?php
											echo $row['id']
											.(isSet($_GET['page'])?'&page='.$_GET['page']:'')
											.(isSet($_GET['sort'])?'&sort='.$_GET['sort']:'')
											.(isSet($_GET['number'])?'&number='.$_GET['number']:'')
											.(isSet($_GET['state'])?'&state='.$_GET['state']:'')
											.(isSet($_GET['category'])?'&category='.$_GET['category']:'')
											.(isSet($_GET['createdBy'])?'&createdBy='.$_GET['createdBy']:'')
											.(isSet($_GET['modifiedBy'])?'&modifiedBy='.$_GET['modifiedBy']:'');
										?>">Upravit</a></td>
									</tr>
									<?php
									// $row = $res->fetch_assoc();
									$count++;
								}

							?>
						</tbody>
					</table>

			<?php } ?>

			<p>
				<?php if($page>0) { ?><button onclick="setParam('page', 0);updateQuery();">&lt;&lt;</button><?php } ?>
				<?php if($page>0) { ?><button onclick="setParam('page', <?php echo $page-1; ?>);updateQuery();">&lt;</button><?php } ?>
				<?php echo $offset.'-'.($offset+$count).' / '.$totalRows; ?>
				<?php if(($offset+$count)<$totalRows) { ?><button onclick="setParam('page', <?php echo $page+1; ?>);updateQuery();">&gt;</button><?php } ?>
				<?php if(($offset+$count)<$totalRows) { ?><button onclick="setParam('page', <?php echo floor($totalRows/10); ?>);updateQuery();">&gt;&gt;</button><?php } ?>
			</p>

			</div>

		</div>

		<div id="numberFilter" class="filter">
			<p><b>Filtrovat podle čísla:</b><button onclick="hideFilter('number');">X</button></p>
			<p>Číslo: <input id="numberFilterNumber" type="number" min="1" max="200" value="<?php echo isSet($_GET['number'])?$_GET['number']:1; ?>"></input></p>
			<p>
				<button onclick="filter('number', numberFilterNumber.value);">Filtrovat</button>
				<button onclick="noFilter('number');">Zrušit filtr</button>
			</p>
		</div>

		<div id="stateFilter" class="filter">
			<p><b>Filtrovat podle stavu:</b><button onclick="hideFilter('state');">X</button></p>
			<p>
				Stav:
				<select id="stateFilterState">
					<option <?php if(isSet($_GET['state']) && $_GET['state']=='pending') echo 'selected'; ?> value="pending">Před schválením</option>
					<option <?php if(isSet($_GET['state']) && $_GET['state']=='approved') echo 'selected'; ?> value="approved">Schváleno</option>
					<option <?php if(isSet($_GET['state']) && $_GET['state']=='dismissed') echo 'selected'; ?> value="dismissed">Zamítnuto</option>
				</select>
			</p>
			<p>
				<button onclick="filter('state', stateFilterState.value);">Filtrovat</button>
				<button onclick="noFilter('state');">Zrušit filtr</button>
			</p>
		</div>

		<div id="categoryFilter" class="filter">
			<p><b>Filtrovat podle kategorie:</b><button onclick="hideFilter('category');">X</button></p>
			<div class="categoryFilterCategoryList">
				<label><input onchange="checkAll('categoryFilterItem', this.checked);" type="checkbox">Vybrat všechny</label><br>
				<?php
				$stmt = $db->prepare('select id, name from Category order by name');
				$stmt->execute();
				$res = $stmt->get_result();
				$stmt->close();

				$cats = [];
				if(isSet($_GET['category'])){
					$cats = explode(',', $_GET['category']);
				}

				while($row = $res->fetch_assoc()){
					?>
					<label><input value="<?php echo $row['id']; ?>" <?php if(array_search($row['id'], $cats)!==false) echo 'checked'; ?> class="categoryFilterItem" type="checkbox">
						</input><?php echo $row['name']; ?></label><br>
					<?php
				}
				?>
			</div>
			<p>
				<button onclick="filter('category', getCheckList('categoryFilterItem'));">Filtrovat</button>
				<button onclick="noFilter('category')">Zrušit filtr</button>
			</p>
		</div>

		<div id="createdByFilter" class="filter">
			<p><b>Filtrovat podle Vytvořil:</b><button onclick="hideFilter('createdBy');">X</button></p>
			<p>Jméno: <input id="createdByFilterInput" value="<?php echo isSet($_GET['createdBy'])?$_GET['createdBy']:''; ?>"></input></p>
			<p>
				<button onclick="filter('createdBy', createdByFilterInput.value);">Filtrovat</button>
				<button onclick="noFilter('createdBy');">Zrušit filtr</button>
			</p>
		</div>

		<div id="modifiedByFilter" class="filter">
			<p><b>Filtrovat podle Upravil:</b><button onclick="hideFilter('modifiedBy');">X</button></p>
			<p>Jméno: <input id="modifiedByFilterInput" value="<?php echo isSet($_GET['modifiedBy'])?$_GET['modifiedBy']:''; ?>"></input></p>
			<p>
				<button onclick="filter('modifiedBy', modifiedByFilterInput.value);">Filtrovat</button>
				<button onclick="noFilter('modifiedBy');">Zrušit filtr</button>
			</p>
		</div>

    </body>

</html>
<?php
$db->close();
?>
