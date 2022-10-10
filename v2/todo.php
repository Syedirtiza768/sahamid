<?php 

	$active = "dashboard";
	$AllowAnyone = true;

	include_once("config.php");
	
	if(isset($_GET['json'])){
		
		$response = array(
			'message' => 'SUCCESS',
			'data' => []
		);
		
		if ( empty($_REQUEST['action']) ){
			die(json_encode($response));
		}
		
		if(!empty( $_GET['action'])){
			
			$todo = [];
			$SQL = "SELECT * FROM todo WHERE user_id='".$_SESSION['UserID']."'";
			$res = mysqli_query($db, $SQL);
			
			while($row = mysqli_fetch_assoc($res)){
				
				array_push( $todo, array(
					'id' => $row['id'],
					'completed' => $row['completed'],
					'title' => $row['title'],
				));
				
			}
			
			$response['data'] = $todo;
			
		}
		
		if(!empty( $_POST['action'])){
			
			$SQL = "DELETE FROM todo WHERE user_id='".$_SESSION['UserID']."'";
			mysqli_query($db, $SQL);
			
			foreach ( $_POST['todos'] as $key => $todo ) {

				$datetime = date('Y-m-d H:i:s');

				$SQL = "INSERT INTO todo (user_id, title, completed, date_time) 
						VALUES('".$_SESSION['UserID']."','".htmlentities($todo['title'])."',".$todo['completed'].",'".$datetime."')";

				mysqli_query($db, $SQL);

			}

			return count($_POST['todos']);
			
		}
		
		echo json_encode($response);
		return;
		
	}

	include_once("includes/header.php");
	
?>
<style>
.wrapper{
	width:100%;
	background:white;
}
</style>
<?php 
	include_once("includes/sidebar.php");
?>

<link rel="stylesheet" href="todo/node_modules/todomvc-common/base.css">
<link rel="stylesheet" href="todo/node_modules/todomvc-app-css/index.css">
<link rel="stylesheet" href="todo/css/app.css">
<input type="hidden" id="FormID" value="<?php echo $_SESSION['FormID']; ?>"/>
<div class="content-wrapper">

	

    <section class="content">
	    
		<div class="row">
			<div class="col-md-12">
				<section id="todoapp">
					<header id="header">
						<h1>Todos</h1>
						<input id="new-todo" placeholder="What needs to be done?" autofocus>
					</header>
					<section id="main">
						<input id="toggle-all" type="checkbox">
						<label for="toggle-all">Mark all as complete</label>
						<ul id="todo-list"></ul>
					</section>
					<footer id="footer"></footer>
				</section>
				<footer id="info">
					<p>Double-click to edit</p>
				</footer>
			</div>
		</div>

    </section>
  
</div>

<div style="clear:both"></div>

<?php
	include_once("includes/footer.php");
?>

<script id="todo-template" type="text/x-handlebars-template">
	{{#this}}
		<li {{#if completed}}class="completed"{{/if}} data-id="{{id}}">
			<div class="view">
				<input class="toggle" type="checkbox" {{#if completed}}checked{{/if}}>
				<label>{{title}}</label>
				<button class="destroy"></button>
			</div>
			<input class="edit" value="{{title}}">
		</li>
	{{/this}}
</script>
<script id="footer-template" type="text/x-handlebars-template">
	<span id="todo-count"><strong>{{activeTodoCount}}</strong> {{activeTodoWord}} left</span>
	<ul id="filters">
		<li>
			<a {{#eq filter 'all'}}class="selected"{{/eq}} href="#/all">All</a>
		</li>
		<li>
			<a {{#eq filter 'active'}}class="selected"{{/eq}}href="#/active">Active</a>
		</li>
		<li>
			<a {{#eq filter 'completed'}}class="selected"{{/eq}}href="#/completed">Completed</a>
		</li>
	</ul>
	{{#if completedTodos}}<button id="clear-completed">Clear completed</button>{{/if}}
</script>

<!--<script src="todo/node_modules/todomvc-common/base.js"></script>-->
<!--<script src="todo/node_modules/jquery/dist/jquery.js"></script>-->
<script src="todo/node_modules/handlebars/dist/handlebars.js"></script>
<script src="todo/node_modules/director/build/director.js"></script>
<script src="todo/js/app.js"></script>

<?php
	include_once("includes/foot.php");
?>