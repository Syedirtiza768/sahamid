<?php

	$PathPrefix='../../';
	include('../../includes/session.inc');
	include('../../includes/SQL_CommonFunctions.inc');

	if (isset($_POST['updateGroupAlias'])){
	
		if (!isset($_POST['alias']) || !isset($_POST['id'])){
			echo json_encode(["status"=>"error","message"=>"missing parameters"]);
			return;
		} 
		
		$alias = trim($_POST['alias']);
		$id = (int)$_POST['id'];
		
		$SQL="UPDATE customergroups SET alias='$alias' WHERE id=$id";
		DB_query($SQL,$db);
		
		echo json_encode(["status"=>"success","alias"=>$alias]);
		return;
		
	}
	
	if (isset($_POST['newCustomerGroup'])){
		
		if (!isset($_POST['alias'])){
			echo json_encode(["status"=>"error","message"=>"missing parameters"]);
			return;
		} 
		
		$alias = trim($_POST['alias']);
		
		$SQL = "INSERT INTO customergroups(alias) VALUES ('$alias')";
		DB_query($SQL,$db);
		
		$id = $_SESSION['LastInsertId'];
		
		echo json_encode(["status"=>"success","alias"=>$alias, "id"=>$id]);
		return;
		
	}
	
	if (isset($_POST['deleteCustomerGroup'])){
		
		if (!isset($_POST['id'])){
			echo json_encode(["status"=>"error","message"=>"missing parameters"]);
			return;
		} 
		
		$id = trim($_POST['id']);
		
		$SQL = "DELETE FROM customergroups WHERE id='$id'";
		DB_query($SQL,$db);
		
		$SQL = "DELETE FROM cgdetails WHERE cgid='$id'";
		DB_query($SQL,$db);
		
		$SQL = "DELETE FROM cgassignments WHERE cgid='$id'";
		DB_query($SQL,$db);
		
		echo json_encode(["status"=>"success","id"=>$id]);
		return;
		
	}
	
	if (isset($_POST['fetchGroupBranches'])){
	
		if (!isset($_POST['id'])){
			echo json_encode(["status"=>"error","message"=>"missing parameters"]);
			return;
		} 
		
		$id = (int)$_POST['id'];
		
		$SQL = "SELECT custbranch.brname, cgdetails.id FROM cgdetails 
				INNER JOIN custbranch ON cgdetails.branchcode=custbranch.branchcode
				WHERE cgid='$id'";
		$res = mysqli_query($db,$SQL);
		
		$branches = [];
		
		while($row = mysqli_fetch_assoc($res)){
			
			$branches[] = $row;
			
		}
		
		echo json_encode(["status"=>"success","branches"=>$branches]);
		return;
		
	}
	
	if (isset($_POST['fetchAvailableDebtors'])){
		
		if (!isset($_POST['salesman'])){
			echo json_encode(["status"=>"error","message"=>"missing parameters"]);
			return;
		} 
		
		$salesman = $_POST['salesman'];
	
		$SQL = "SELECT debtorsmaster.debtorno, debtorsmaster.name 
				FROM custbranch 
				INNER JOIN debtorsmaster ON debtorsmaster.debtorno=custbranch.debtorno
				WHERE custbranch.branchcode NOT IN (SELECT branchcode FROM cgdetails) 
				AND custbranch.salesman='$salesman'
				GROUP BY custbranch.debtorno";
		$res = mysqli_query($db,$SQL);
		
		$clients = [];
		
		while($row = mysqli_fetch_assoc($res)){
			
			$client = [];
			$client['id'] = $row['debtorno'];
			$client['text'] = $row['name'];
			
			$clients[] = $client;
			
		}
		
		echo json_encode(["status"=>"success","clients"=>$clients]);
		return;
		
	}
	
	if (isset($_POST['fetchBranches'])){
		
		if (!isset($_POST['debtorno'])){
			echo json_encode(["status"=>"error","message"=>"missing parameters"]);
			return;
		} 
		
		$debtorno = $_POST['debtorno'];
	
		$SQL = "SELECT custbranch.branchcode, custbranch.brname 
				FROM custbranch 
				WHERE custbranch.branchcode NOT IN (SELECT branchcode FROM cgdetails)
				AND custbranch.debtorno='$debtorno'";
		$res = mysqli_query($db,$SQL);
		
		$branches = [];
		
		while($row = mysqli_fetch_assoc($res)){
			
			$branch = [];
			$branch['id'] = $row['branchcode'];
			$branch['text'] = $row['brname'];
			
			$branches[] = $branch;
			
		}
		
		echo json_encode(["status"=>"success","branches"=>$branches]);
		return;
		
	}
	
	if (isset($_POST['attachBranchToGroup'])){
		
		if (!isset($_POST['branchcode']) || !isset($_POST['id'])){
			echo json_encode(["status"=>"error","message"=>"missing parameters"]);
			return;
		} 
		
		$id = $_POST['id'];
		$branchcode = $_POST['branchcode'];
	
		$SQL = "SELECT * FROM customergroups WHERE id='$id'";
		$res = mysqli_query($db, $SQL);
		
		if(mysqli_num_rows($res) != 1){
			echo json_encode(["status"=>"error","message"=>"Not a Valid Group ID."]);
			return;
		} 
		
		$SQL = "SELECT * FROM custbranch WHERE branchcode='$branchcode'";
		$res = mysqli_query($db, $SQL);
		
		if(mysqli_num_rows($res) != 1){
			echo json_encode(["status"=>"error","message"=>"Invalid Branch Code."]);
			return;
		}
		
		$name = mysqli_fetch_assoc($res)['brname'];
	
		$SQL = "SELECT * FROM cgdetails WHERE branchcode='$branchcode'";
		$res = mysqli_query($db, $SQL);
		
		if(mysqli_num_rows($res) != 0){
			echo json_encode(["status"=>"error","message"=>"Branch Already Attached To A Group."]);
			return;
		} 
		
		$SQL = "INSERT INTO cgdetails (cgid,branchcode)
				VALUES ('$id','$branchcode')";
		DB_query($SQL, $db);
		
		$branchID = $_SESSION['LastInsertId'];
		
		echo json_encode(["status"=>"success","id"=>$branchID,"brname"=>$name]);
		return;
		
	}
	
	if (isset($_POST['deleteGroupBranch'])){
	
		if (!isset($_POST['id'])){
			echo json_encode(["status"=>"error","message"=>"missing parameters"]);
			return;
		} 
		
		$id = (int)$_POST['id'];
		
		$SQL = "DELETE FROM cgdetails WHERE id='$id'";
		DB_query($SQL, $db);
		
		echo json_encode(["status"=>"success","id"=>$id]);
		return;
		
	}
	
	if (isset($_POST['updateGroupTarget'])){
	
		if (!isset($_POST['group']) || !isset($_POST['target'])){
			echo json_encode(["status"=>"error","message"=>"missing parameters"]);
			return;
		} 
		
		$id 	= (int)$_POST['group'];
		$target = (int)$_POST['target'];
		$year = date('Y');
		
		$SQL = "SELECT * FROM cgassignments WHERE cgid = $id AND year = '$year'";
		$res = mysqli_query($db,$SQL);
	
		if(mysqli_num_rows($res)==1){
			
			$SQL = "UPDATE cgassignments SET target=$target WHERE cgid=$id and year = '$year'";
			DB_query($SQL,$db);
		
			echo json_encode(["status"=>"success","target"=>$target]);
			return;
		
		}
		
		$SQL = "SELECT * FROM cgassignments WHERE cgid = $id";
		$res = mysqli_query($db,$SQL);
	
		if(mysqli_num_rows($res) != 0){
			
			$row 		= mysqli_fetch_assoc($res);
			$salesman 	= $row['salesman'];
			
			$SQL = "INSERT into cgassignments(salesman, cgid, target, year)
					VALUES ('$salesman','$id','$target','$year')";
			DB_query($SQL,$db);
			
			echo json_encode(["status"=>"success","target"=>$target]);
			return;
		
		}
		
		$SQL = "SELECT * FROM cgdetails WHERE cgid=$id";
		$res = mysqli_query($db, $SQL);
		
		if(mysqli_num_rows($res) != 0){
			
			$branchCode = mysqli_fetch_assoc($res)['branchcode'];
			
			$SQL = "SELECT salesman FROM custbranch WHERE branchcode='$branchCode'";
			$res = mysqli_query($db, $SQL);
			
			if(mysqli_num_rows($res) == 1){
				
				$salesman = mysqli_fetch_assoc($res)['salesman'];
				
				$SQL = "INSERT into cgassignments(salesman, cgid, target, year)
						VALUES ('$salesman','$id','$target','$year')";
				DB_query($SQL,$db);				
				
				$SQL 	= "SELECT salesmanname FROM salesman WHERE salesmancode='$salesman'";
				$name 	= mysqli_fetch_assoc(mysqli_query($db, $SQL))['salesmanname'];
				
				echo json_encode(["status"=>"success","target"=>$target,"salesman"=>$salesman,"name"=>$name]);
				return;	
				
			}
			
			echo json_encode(["status"=>"error","message"=>"Invalid Branch Code Provided!!!"]);
			return;
			
		}
		
		echo json_encode(["status"=>"error","message"=>"No Branches Assisned To Selected Group!!!"]);
		return;
		
	}
	