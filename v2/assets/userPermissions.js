$(document).ready(function(){

	var permissionsTable = $("#userPermissions").DataTable({
		"columns" : [
			{"data" : "name"},
			{"data" : "description"},
			{"data" : "action"},
		]
	});
	
	$("#userPermissions_length").html("<h3 class='no-margin'>Assisned Permissions <button class='btn btn-success' id='assignNewPermission'>Assign New Permission</button> </h3>");

	$(".closePermissionContainer").on("click",function(){
		
		$(".newPermissionContainer").removeClass("containerVisible");
		
	});
	
	$("#assignNewPermission").on("click",function(){
		
		if ($("#selectedUser").val()=="") return;
		
		$(".newPermissionContainer").addClass("containerVisible");
		
	});
	
	$("#selectedUser").on("change",function(){
		 
		let UserID = $(this).val();
		
		if(UserID.trim() == ""){
			return;
		}
		
		$(".newPermissionContainer").removeClass("containerVisible");
		
		permissionsTable.clear().draw();
		
		let FormID = $("#FormID").val();
		let getUserPermissions = true;
		
		$.post("userPermissions.php",{
			FormID,getUserPermissions,UserID
		},function(res,status,something){
			res = JSON.parse(res);
			
			if(res.status == "success"){
				permissionsTable.rows.add(res.data).draw();
			}
			
		});
	
	});
	
	$(document.body).on("click", ".removeUserPermission", function(){
		
		let FormID = $("#FormID").val();
		let removeUserPermission = true;
		let ref = $(this);
		
		ref.prop("disabled",true);
		
		let id = ref.attr("data-id");
		
		$.post("userPermissions.php",{
			FormID,removeUserPermission,id
		},function(res,status,something){
			res = JSON.parse(res);
			
			if(res.status == "success"){
				permissionsTable.row(ref.parent().parent()).remove().draw();
			}
			
		});
		
	});
	
	$(".assignSelectedPermission").on("click", function(){
		
		let FormID = $("#FormID").val();
		let permission = $("#selectedPermission").val();
		let assignUserPermission = true;
		
		let ref = $(this);
		ref.prop("disabled",true);
		
		if(permission.trim() == ""){
			ref.prop("disabled",false);
			return;
		}
		
		let UserID = $("#selectedUser").val();
		
		if(UserID.trim() == ""){
			ref.prop("disabled",false);
			return;
		}
		
		$.post("userPermissions.php",{
			FormID,assignUserPermission,UserID,permission
		},function(res,status,something){
			res = JSON.parse(res);
			
			if(res.status == "success"){
				permissionsTable.row.add(res.row).draw();
			}
			
			$("#selectedPermission").val("");
			ref.prop("disabled",false);
			
		});
		
	});
	
	$(".viewAllAssigned").on("click", function(){
		
		let FormID = $("#FormID").val();
		let viewAllAssigned = true;
		
		permissionsTable.clear().draw();
		
		$("#selectedUser").val("");
		$(".newPermissionContainer").removeClass("containerVisible");
		
		$.post("userPermissions.php",{
			FormID,viewAllAssigned
		},function(res,status,something){
			
			res = JSON.parse(res);
			
			if(res.status == "success"){
				permissionsTable.rows.add(res.data).draw();
			}
			
		});
		
	});
	
});