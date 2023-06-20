<?php 

	$active = "shopsale";

	include_once("config.php");
	
	if(!userHasPermission($db, 'cashDrawer')){
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		return;
	}
	
	if(isset($_GET['json'])){
	
		$SQL = "SELECT  shopsale.orderno,
						shopsale.payment,
						shopsale.advance,
						shopsale.orddate,
						shopsale.created_by,
						(SUM(shopsalelines.price*shopsalelines.quantity) * (1 - (shopsale.discount/100))) - shopsale.discountPKR as ovamount,
						cashdrawer_payments.received,
						cashdrawer_payments.received_at
				FROM shopsale
				INNER JOIN shopsalelines ON shopsalelines.orderno = shopsale.orderno
				LEFT OUTER JOIN cashdrawer_payments ON shopsale.orderno = cashdrawer_payments.orderno";
		
		//INNER JOIN debtortrans ON (transno = shopsale.orderno AND type=750)		

		if(isset($_GET['today']))
			$SQL .= " WHERE shopsale.orddate='".date("Y-m-d")."'"; 
		
		else if(isset($_GET['date']) && $_GET['date'] != "")
			$SQL .= " WHERE shopsale.orddate='".$_GET['date']."'"; 
		
		$SQL .= " GROUP BY shopsale.orderno ORDER BY shopsale.orderno";
		
		$res = mysqli_query($db, $SQL);
		
		$shopSale 		= [];
		$person 		= [];
		$notReceived 	= 0;
		$receivedToday 	= 0;
		
		while($row = mysqli_fetch_assoc($res)){
			
			if(!isset($person[$row['created_by']])){
				$person[$row['created_by']] = [];
				$person[$row['created_by']]['initials'] = strtoupper(explode(" ",$row['created_by'])[0][0]."".explode(" ",$row['created_by'])[1][0]);
				$person[$row['created_by']]['amount'] = 0;
			}
			
			$item = [];
			
			$item['orderno']	= $row['orderno'];
			$item['payment'] 	= strtoupper($row['payment']);
			$item['amount']		= ($row['payment'] == "csv" ? $row['ovamount'] : $row['advance']);
			$item['person']		= $row['created_by'];
			$item['received']   = ($row['received'] == null || $row['received'] == "" || $row['received'] == 0) ? "" : "received";
			
			if(isset($_GET['today'])){
				if($item['received'] == "received" && $row['received_at'] == date("Y-m-d"))
					$receivedToday += $item['amount'];
			}else if(isset($_GET['date']) && $_GET['date'] != ""){
				if($item['received'] == "received" && $row['received_at'] == $_GET['date'])
					$receivedToday += $item['amount'];
			}else{
				if($item['received'] == "received")
					$receivedToday += $item['amount'];
			}
			
			
			if($item['payment'] == "CRV" && $item['amount'] == 0)
				continue;
			
			if(isset($_GET['received']) && !isset($_GET['notreceived']) && $item['received'] == "")
				continue;
			
			if(isset($_GET['notreceived']) && !isset($_GET['received']) && $item['received'] != "")
				continue;
			
			if($item['received'] == ""){
				$person[$row['created_by']]['amount'] += $item['amount'];
				$notReceived += $item['amount'];
			}
			
			$item['amount'] = locale_number_format($item['amount']);
			
			$shopSale[] = $item;
		}
		
		foreach($person as &$p){
			$p['amount'] = locale_number_format($p['amount']);
		}

		echo json_encode([
			
			'person' 		=> $person,
			'items'	 		=> $shopSale,
			'notReceived' 	=> locale_number_format($notReceived),
			'receivedToday' => locale_number_format($receivedToday),
		
		]);
		
		return;
	
	}
	
	if(isset($_POST['received'])){
		
		$orderno 	= $_POST['orderno'];
		$received 	= $_POST['received'];
		
		$SQL = "SELECT * FROM cashdrawer_payments WHERE orderno=$orderno";
		$res = mysqli_query($db, $SQL);
		
		if(mysqli_num_rows($res) == 0){
			
			$SQL = "INSERT INTO cashdrawer_payments (orderno, received, received_at)
					VALUES ($orderno, $received, '".date("Y-m-d")."')";
			
		}else{
			
			$SQL = "UPDATE cashdrawer_payments 
					SET received=$received,
						received_at='".date('Y-m-d')."'
					WHERE orderno=$orderno";
			
		}
		
		DB_query($SQL, $db);
		
		echo json_encode([
			'status' => 'success'
		]);
		return;
		
	}

	if(isset($_POST['processBatch'])){

		$data = $_POST['data'];

		if(count($data) == 0){
			echo json_encode([
				'status' => 'error',
				'message' => 'data array length zero'
			]);
			return;
		}

		$SQL = "SELECT max(batch) as batch FROM cashdrawer_payments";
		$res = mysqli_query($db, $SQL);
		$batchID = (mysqli_fetch_assoc($res)['batch'] + 1);

		foreach ($data as $orderno) {
			$SQL = "SELECT * FROM cashdrawer_payments WHERE orderno=$orderno";
			$res = mysqli_query($db, $SQL);
			
			if(mysqli_num_rows($res) == 0){
				
				$SQL = "INSERT INTO cashdrawer_payments (orderno, batch, received, received_at)
						VALUES ($orderno, $batchID, 1, '".date("Y-m-d")."')";
				
			}else{
				
				$SQL = "UPDATE cashdrawer_payments 
						SET received=1,
							batch=$batchID,
							received_at='".date('Y-m-d')."'
						WHERE orderno=$orderno";
				
			}
			
			DB_query($SQL, $db);
		}

		echo json_encode([
			'status' => 'success'
		]);

		return;
	}

	include_once("includes/header.php");
	include_once("includes/sidebar.php");

?>

<style>
	.content{
		height: calc(100vh - 101px);
		display: flex; 
		padding:0px;
	}
	.main-area{
		flex: 1;
		padding: 10px;
	}
	.main-area .title{
		text-align: center;
		margin-top: 0px;
		font-variant-caps: petite-caps;
	}
	.main-area .cash-totals{
		width: calc(100% + 30px);
		display: flex;
		background: white;
		margin: 10px -15px;
	}
	.main-area .cash-totals .cash-cont{
		flex: 1;
		text-align: center;
		padding: 10px;
		font-size: 1.2em;
	}
	.main-area .cash-totals .cash-cont .heading{
		font-variant-caps: petite-caps;
		display: block;
	}
	.main-area .filters{
		width: 100%;
		margin: 10px 0px;
		display: flex;
		justify-content: space-between;
	}
	.main-area .filters .person-filter{
		display: flex;
	}
	.main-area .filters .person-filter .person{
		padding: 5px;
		background: gainsboro;
		border: 1px solid #a0a0a0;
		border-radius: 10px;
		cursor: pointer;
		margin-right: 5px;
	}
	.main-area .filters .date-filter{
		display: flex;
		flex-direction: row-reverse;
	}
	.main-area .filters .date-filter .date{
		padding: 5px;
		background: gainsboro;
		border: 1px solid #a0a0a0;
		border-radius: 10px;
		cursor: pointer;
		margin-right: 5px;
	}
	.main-area .shopsale-list-container{
		width: calc(100%);
		height: 100px;
		margin: 10px 0px;
		overflow-y: scroll;
	}
	.main-area .shopsale-list-container .shopsale-list{
		widht:100%;
		display: flex;
		justify-content: space-between;
		flex-wrap: wrap;
	}
	.main-area .shopsale-list-container .shopsale-list .shopsale-item{
		width: 30%;
		height: 50px; 
		background: grey;
		border: 1px solid #424242;
		margin-bottom: 10px;
		border-radius: 7px;
		display: flex;
	}
	.main-area .shopsale-list-container .shopsale-list .shopsale-item .icon{
		width: 50px;
		display: flex;
		justify-content: center;
		align-items: center;
		background: white;
		border-radius: 7px 0px 0px 7px;
		font-size: 1.8em;
		cursor: pointer;
	}
	.main-area .shopsale-list-container .shopsale-list .shopsale-item .item-details{
		background: gainsboro;
		width: 100%;
		padding: 5px;
		border-radius: 0 7px 7px 0;
	}
	.person-area{
		width:50px;
		background: #29313a; //#425262;
		display: flex;
		flex-direction: column;
	}
	.person-area .person{
		width: 35px;
		height: 35px;
		background: white;
		display:flex;
		justify-content: center;
		align-items:center;
		margin: 10px 8px;
		margin-bottom: 0px; 
		font-size: 1.5em;
		border-radius: 50%;
		cursor: pointer;
	}
	.person .person-details{
		display: none;
	}
	.person:hover .person-details{
		display: block;
		position: absolute;
		right: 55px;
		font-size: .6em;
		background: white;
		border: 1px solid #424242; 
		border-radius: 4px;
		padding: 10px;
	}
	.selected{
		background: #cdb5e6 !important;
		border: 2px solid #00961b !important;
	}
	.received{
		color: white;
		background: #4d9a55 !important;
	}
	.selectDate{
		height: 34px;
		border: 1px solid #424242;
		border-radius: 8px;
	}
</style>

<div class="content-wrapper">
    
    <section class="content">
	    
		<section class="main-area">
			<h1 class="title">Cash Payment Tracking</h1>
			<div class="cash-totals">
				<!--<div class="cash-cont">
					<div class="heading">Total</div>
					<div class="amount">10000</div>
				</div>-->
				<div class="cash-cont">
					<div class="heading">Not Received</div>
					<div class="amount cashnotreceived">...</div>
				</div>
				<div class="cash-cont">
					<div class="heading">Received</div>
					<div class="amount cashreceived">...</div>
				</div>
			</diV>
			<div class="filters">
				<div class="person-filter">
					<div class="person rec" data-selected="false">
						Received
					</div>
					<div class="person nrec selected" data-selected="true">
						Not Received
					</div>
				</div>
				<div>
					<button class="btn btn-info process-batch" data-processing="false">Process Batch</button>
				</div>
				<div class="date-filter">
					<div class="">
						<input type="date" class="selectDate"/>
					</div>
					<div class="date today" data-selected="false">
						Today
					</div>
					<div class="date selected" data-selected="true">
						All
					</div>
				</div>
			</div>
			<section class="shopsale-list-container">
				<section class="shopsale-list"></section>
			</section>
		</section>
		
		<section class="person-area"></section>
		
    </section>
  
</div>

<?php
	include_once("includes/footer.php");
?>

<script>
	
	var received = null;
	var notReceived = null;
	var batchArray = [];
	
	$(document).ready(function(){
		
		let height = $(".person-area").outerHeight() - $(".main-area .title").outerHeight();
		height = height - $(".main-area .cash-totals").outerHeight();
		height = height - $(".main-area .filters").outerHeight();
		height = height - $(".main-footer").outerHeight();
		height += 11;
		
		$(".main-area .shopsale-list-container").height(height);
		
		update();
		
	});
	
	$(".person-filter .person").on("click", function(){
		
		let ref = $(this);
		let selected = ref.attr("data-selected");
		
		$(".person-filter .person").each(function(){			
			$(this).removeClass("selected");
			$(this).attr("data-selected","false");
		});
		
		if(selected == "false"){
			ref.attr("data-selected","true");
			ref.addClass("selected");
		}else{
			ref.attr("data-selected","false");
			ref.removeClass("selected");
		}
		
		update();
		
	});
	
	$(".date-filter .date").on("click", function(){
		
		let ref = $(this);
		let selected = ref.attr("data-selected");
		
		$(".date-filter .date").each(function(){			
			$(this).removeClass("selected");
			$(this).attr("data-selected","false");
		});
		
		if(selected == "false"){
			ref.attr("data-selected","true");
			ref.addClass("selected");
		}
		
		$(".selectDate").val("");
		
		update();
		
	});
	
	$(document.body).on("click",".shopsale-item .icon", function(){
		
		let status = false;
		let ref	   = $(this);
		let orderno = ref.attr("data-orderno");
		
		if(ref.hasClass("received")){
			return;
		}

		if(ref.attr("data-processing") == "true"){
			batchArray.splice(batchArray.indexOf(orderno), 1);
			ref.css("background","white");
			ref.attr("data-processing","false");
		}else{
			batchArray.push(orderno);
			ref.css("background","orange");
			ref.attr("data-processing","true");
		}

		console.log(batchArray);
		/*if(!ref.hasClass("received"))
			status = true;
		
		if(ref.attr("data-processing") == "true")
			return;
		
		if(!confirm("Are you sure you want to proceed?"))
			return;
		
		ref.attr("data-processing","true");
		
		$.post("cashDrawer.php",{
			FormID:'<?php echo $_SESSION['FormID']; ?>',
			received: ((status == true) ? 1:0),
			orderno: ref.attr("data-orderno")
		}, function(res, sta, smt){
			res = JSON.parse(res);
			
			if(res.status == "success"){
				
				if(status){
					ref.addClass("received");
				}else{
					ref.removeClass("received");
				}
				
				update(false);
				
				if(!(($(".person-filter .rec").attr("data-selected") == "true" && $(".person-filter .nrec").attr("data-selected") == "true") || ($(".person-filter .rec").attr("data-selected") == "false" && $(".person-filter .nrec").attr("data-selected") == "false"))){
				
					setTimeout(function(){
						ref.parent().remove();
						ref.attr("data-processing","false");
					},2000);
		
				}else{
					setTimeout(function(){
						ref.attr("data-processing","false");
					},2000);
				}
				
			}
		
		});*/
		
	});

	$(".process-batch").on("click", function(){
		console.log("processing begin...");
		let ref = $(this);
		let processBatch = true;

		if(batchArray.length == 0){
			console.log("Batch Array Length is zero...");
			return;
		}

		if(ref.attr("data-processing") == "true"){
			console.log("Already processing a request...");
			return;
		}

		if(!confirm("Are you sure you want to proceed? This action cannot be reversed so confirm you have selected the right documents before proceeding."))
			return;

		let data = Object.assign({}, batchArray);
		console.log("Data...");
		console.log(data);

		ref.attr("data-processing", "true");

		$.post("cashDrawer.php",{
			FormID:'<?php echo $_SESSION['FormID']; ?>',
			data,
			processBatch
		}, function(res, sta, smt){
			res = JSON.parse(res);
			console.log(res);
			batchArray = [];	
			update(true);
			ref.attr("data-processing", "false");
		});
	});
	
	$(".selectDate").on("change",function(){
		$(".date-filter .date").each(function(){			
			$(this).removeClass("selected");
			$(this).attr("data-selected","false");
		});
		update();
	});
	
	function update(pass = true){
		
		if(pass){
			clear();
		}
		
		let URL = "cashDrawer.php?json"; 
		
		if($(".date-filter .today").attr("data-selected") == "true")
			URL += "&today";
		
		if($(".person-filter .rec").attr("data-selected") == "true")
			URL += "&received";
		
		if($(".person-filter .nrec").attr("data-selected") == "true")
			URL += "&notreceived";
		
		if($(".selectDate").val() != ""){
			URL += "&date="+$(".selectDate").val();
		}
		
		$.get(URL,function(res,status){
			
			res = JSON.parse(res);
			
			let persons = res.person;
			$(".person-area").html("");
			
			for (let person in persons) {
				if (persons.hasOwnProperty(person)) {
					if(persons[person].amount != 0)
						addNewPerson(persons[person].initials, person, persons[person].amount);
				}
			}
			
			if(pass){
				
				let items = res.items;
				
				for (let item in items) {
					if (items.hasOwnProperty(item)) {
						addNewItem(items[item].payment, items[item].orderno, items[item].amount, items[item].person, items[item].received);
					}
				}
				
			}
			
			$(".cashnotreceived").html(res.notReceived);
			$(".cashreceived").html(res.receivedToday);
			
		});
		
	}
	
	function addNewItem(payment, orderno, amount, person, received){
		
		let html = `<div class="shopsale-item">
						<span class="icon ${received}" data-orderno="${orderno}" data-processing="false"><i class="fa fa-check"></i></span>
						<span class="item-details">
							<a href="/sahamid/shop/pos/shopSalePrint.php?orderno=${orderno}&internal" target="_blank">${payment} (${orderno})</a> <br>
							${amount} (${person})
						</span> 
					</div>`;
					
		$(".shopsale-list").append(html);
		
	}
	
	function addNewPerson(initial, name, amount){
		
		let html = `<div class="person">
						${initial}
						<div class="person-details">
							<span class="person-name">${name} </span>
							( <span class="person-amount">${amount}</span> )
						</div>
					</div>`;
		
		$(".person-area").append(html);
		
	}
	
	function clear(){
		$(".person-area").html("");
		$(".shopsale-list").html("");
	}

</script>

<?php
	include_once("includes/foot.php");
?>