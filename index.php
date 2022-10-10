<!DOCTYPE html>
<html>
<head>
	<title>ERP SA-HAMID</title>
	<link type="text/css" rel="stylesheet" href="erp/css/font-awesome.min.css"  media="screen,projection"/>
	<link type="text/css" rel="stylesheet" href="erp/css/materialize.css"  media="screen,projection"/>
	<link type="text/css" rel="stylesheet" href="erp/css/main.css"  media="screen,projection"/>
	<link href="erp/css/bootstrap.min.css" rel="stylesheet" />
    <link href="erp/css/now.css" rel="stylesheet" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

	<?php

		$PageSecurity=0;
		include('includes/session.inc');
		$Title=_('Main Menu');
		include('MainMenuLinksArray.php'); 
		
		/*if(($_SESSION['UserID'] == 'admin')){
			if(!isset($_GET['pass'])){
				header("Location: v2/dashboard-UD.php");
				return;
			}
		}*/
    if(!userHasPermission($db, 'recovery_alert')) {


        header("Location: /sahamid/v2/tasks.php");

    }
    else
		if(!isset($_GET['pass']))
			header("Location: v2/outstandingdashboard.php");

	?>

	<style type="text/css">
		

		.side-link{

			text-align: left; 
			margin: 0px; 
			width: 100%; 
			padding: 7px; 
			background: url('erp/images/bg.jpg');
			border-bottom: 0px white dotted;
			color: white;

		}

		.side-link:hover{
			cursor: pointer;
		}

		.favorite{
			text-align: right;
			 margin: 0px; 
			 width: 100%; 
			 padding: 5px; 
			 border-bottom: 2px white dotted; 
			 color: white; 
			 margin-top: 20px;
		}

		.fav-item{
			text-align: right;
			margin: 0px; 
			width: 100%; 
			float: right;
			padding: 3px; 
			border-bottom: 1px white solid; 
			color: white; 
			font-weight: normal;
			cursor: pointer;
		}

		.fav-item:hover{
			background: #ccc;
			color: #000;	
		}

		.module-name{
			text-align: center;
			padding: 15px; 
			background: #aaa;
			color: white; 
			margin-left: -15px; 
			margin-right: -15px;
			margin-bottom: 5px;
		}

		.title{
			text-align: center;
			padding: 7px; 
			background: #aaa;
			color: white; 
			margin-left: -15px; 
			margin-right: -15px;
		}

		.padding20{
			padding: 20px;
		}

		.m-link{
			width: 100%;
			color: grey;
			text-decoration: none;
			padding: 7px;
			display:inline-block;
			background: white;
			margin-bottom: 2px;
		}

		.m-link:hover{
			color: black;
			background: #ddd !important;
			text-decoration: none;
		}

		.content-body{
			height: calc(100VH - 180px); 
			min-height: 100%;
		}

		.content-menu{
			height: calc(100VH - 180px); 
			min-height: 100%; 
			background: url('erp/images/bg.jpg');
		}

		.ib{
			width: 80%;
			color: blue;
			display: inline-block;
			cursor: pointer;
		}

		.start{
			background: #aaa;
			float: left;
			padding: 7px;
		}

		.mf{
			width: 5%;
			text-align: right;
			float: right;
			display: inline-block;
			cursor: pointer;
		}

		.y-scroll{
			overflow-y: scroll;
		}

		@media only screen and (max-width: 500px) {

			.content-body{
				height: 100%;
			}

			.content-menu{
				height: 100%;
			}

		}

		@media only screen and (max-height: 900px) {

			.content-body{
				height: 100%;
			}

			.content-menu{
				height: 100%;
			}

		}

	</style>

</head>
<body>

	<header style="font-size:1em; padding:7px; width:100%; background:#424242;">
      	<span style="color:white">
      		<?php echo stripslashes($_SESSION['CompanyRecord']['coyname']); ?> 
      		&nbsp;|&nbsp;
      		<span style="color:#ccc">
      			<?php echo stripslashes($_SESSION['UsersRealName']); ?>
          </span>
      		<span class="pull-right" style="background:#424242; padding: 0 10px;">
      			<a href="<?php echo $RootPath; ?>/index.php" style="color: white; text-decoration: none;">Main Menu</a>
      			<a class="bold" href="<?php echo $RootPath; ?>/Logout.php" style="color: white; text-decoration: none; margin-left:20px;">Logout</a>
      		</span>
      	</span>
  	</header>
	
	<div class="card" style="padding: 10px; height: 100%;">
	<div class="container-fluid">
		<div class="row"> 
			<div class="col-sm-12 col-md-4 col-lg-2">
				<div class="card content-menu">
		            
				<?php
					$i=0;
					foreach($ModuleLink as $module => $moduleName){
						if ($_SESSION['ModulesEnabled'][$i]==1)	{
				?>
							<h6 id="h<?php echo $module; ?>" class="side-link" onclick="moduleNameClicked('<?php echo $module; ?>')"><?php echo $moduleName; ?></h6>
				<?php
						}
						$i++;
					}	
			

				?>
				
		        </div>
			</div>
			<?php

				$i=0;
        		foreach($ModuleLink as $module => $moduleName){
        			if ($_SESSION['ModulesEnabled'][$i]==1)	{
        	?>
			<div id="<?php echo $module; ?>" class="col-sm-12 col-md-8 col-lg-10" style="visibility: hidden; display: none;">
				<div class="card content-body y-scroll">
		            <div class="container-fluid">
		            	
	            		<h2 class="module-name"><?php echo $moduleName; ?></h2>
	            		<div class="row">
			            	<div class="col-sm-12 col-md-6 col-lg-4 card padding20" style="background: #eee;">
			            		<h5 class="title">Transactions</h5>
			            		<?php 

			            			foreach($MenuItems[$module]['Transactions'] as $caption => $href ){
			            				$ScriptNameArray = explode('?', substr($href,1));
			            				$ScriptNameArray = explode('/',$ScriptNameArray[0]);
										$PageSecurity = $_SESSION['PageSecurityArray'][$ScriptNameArray[count($ScriptNameArray)-1]];
										if ((in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PageSecurity))) {
											echo '<div class="m-link"><a class="ib" href="'.$RootPath.$href.'">'.$caption.'</a><span class="mf"><i class="fa fa-star" aria-hidden="true"></i>
</span></div><br>';
										}
			            			}

			            		?>
			            	</div>
			            	<div class="col-sm-12 col-md-6 col-lg-4 card padding20" style="background: #eee;">
			            		<h5 class="title">Reports</h5>
			            		<?php 

			            			foreach($MenuItems[$module]['Reports'] as $caption => $href ){
			            				$ScriptNameArray = explode('?', substr($href,1));
			            				$ScriptNameArray = explode('/',$ScriptNameArray[0]);
										$PageSecurity = $_SESSION['PageSecurityArray'][$ScriptNameArray[count($ScriptNameArray)-1]];
										if ((in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PageSecurity))) {
			            					echo '<div class="m-link"><a class="ib" href="'.$RootPath.$href.'">'.$caption.'</a><span class="mf"><i class="fa fa-star" aria-hidden="true"></i>
</span></div><br>';
										}
			            			}

			            		?>
			            	</div>
			            	<div class="col-sm-12 col-md-6 col-lg-4 card padding20" style="background: #eee;">
			            		<h5 class="title">Maintenance</h5>
			            		<?php 

			            			foreach($MenuItems[$module]['Maintenance'] as $caption => $href ){
			            				$ScriptNameArray = explode('?', substr($href,1));
			            				$ScriptNameArray = explode('/',$ScriptNameArray[0]);
										$PageSecurity = $_SESSION['PageSecurityArray'][$ScriptNameArray[count($ScriptNameArray)-1]];
										if ((in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) OR !isset($PageSecurity))) {
			            					echo '<div class="m-link"><a class="ib" href="'.$RootPath.$href.'">'.$caption.'</a><span class="mf"><i class="fa fa-star" aria-hidden="true"></i>
 </span></div><br>';
			            				}
			            			}

			            		?>
			            	</div>
			            </div>
		            </div>
		        </div>
			</div>
			<?php 
					}
					$i++;
        		}
        	?>
		</div>
	</div>

	<footer style="background-color:#424242; color:grey">
		Powered By&nbsp;:&nbsp;<span class="bold" style="color:white">Compresol</span>
	</footer>

	</div>


	<script type="text/javascript" src="erp/js/jquery.js"></script>
    <script type="text/javascript" src="erp/js/materialize.min.js"></script>
    <script>
    	function moduleNameClicked(id){ 
    		<?php
    			$i=0;
    			$firstModule = "";
        		foreach($ModuleLink as $module => $moduleName){
        			if ($_SESSION['ModulesEnabled'][$i]==1)	{
        				if($firstModule == "")
        					$firstModule = $module;
        	?>
        		if(id == '<?php echo $module; ?>'){
		    		document.getElementById('<?php echo $module; ?>').style.visibility = "visible";
		    		document.getElementById('<?php echo $module; ?>').style.display = "block";
		    		document.getElementById('h<?php echo $module; ?>').style.background = "#aaa";
		    		document.getElementById('h<?php echo $module; ?>').style.color = "black";
		    	}else{
		    		document.getElementById('<?php echo $module; ?>').style.visibility = "hidden";
		    		document.getElementById('<?php echo $module; ?>').style.display = "none";
		    		document.getElementById('h<?php echo $module; ?>').style.background = "url('erp/images/bg.jpg')";
		    		document.getElementById('h<?php echo $module; ?>').style.color = "white";
		    	}

			<?php
					}
    				$i++;
    			}
    		?>
    	}

    	$(document).ready(function(){
    		document.getElementById('<?php echo $firstModule; ?>').style.visibility = "visible";
		    document.getElementById('<?php echo $firstModule; ?>').style.display = "block";
		    document.getElementById('h<?php echo $firstModule; ?>').style.background = "#aaa";
		    document.getElementById('h<?php echo $firstModule; ?>').style.color = "black";
    	});
    </script>

</body>
</html>