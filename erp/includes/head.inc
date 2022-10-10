<link type="text/css" rel="stylesheet" href="erp/css/font-awesome.min.css"  media="screen,projection"/>
<link type="text/css" rel="stylesheet" href="erp/css/materialize.css"  media="screen,projection"/>
<link type="text/css" rel="stylesheet" href="erp/css/main.css"  media="screen,projection"/>
<link href="erp/css/bootstrap.min.css" rel="stylesheet" />
<link href="erp/css/now.css" rel="stylesheet" />
<script type="text/javascript" src="erp/js/jquery.js"></script>
<script type="text/javascript" src="erp/js/materialize.min.js"></script>
<?php echo '<script type="text/javascript" src = "'.$RootPath.'/javascripts/MiscFunctions.js"></script>'; ?>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

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

	thead{
		background: #c5bd99;
	}

	tfoot{
		background: #c5bd99;
	}

	.hilight{
		background: #A48A7B;
		color: white;
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
	
	body{
		background:#f1f1f1;
	}
	
	input:required{
		background-color:#e2f5ff;
	}
	
	table{
		background:#fff;
		display:table;
		border-collapse: separate;
		border-spacing: 2px;
		border-color: grey;
		width:auto;
		margin: 30px auto;
		border: thin outset #b3b3b3;
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
	
	.bold{
		color:#aaa;
	}

	.start{
		background: #aaa;
		float: left;
	}

	header{
		background: #424242;
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