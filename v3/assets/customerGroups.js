$(document).ready(function(){
		
	$(document.body).on("click",".edit-group",function(){
		
		let group = $(this).parent().parent().parent();
		group.find(".group-display-view").css("display","none");
		group.find(".group-edit-view").removeClass("display-hidden");
		
		let title = group.find(".group-name-display").html();
		
		group.find(".group-edit-view").find(".group-name-input").val(title);
		
	});
	
	$(document.body).on("click",".cancel-group-edit", function(){
		
		let group = $(this).parent().parent().parent();
		group.find(".group-display-view").css("display","block");
		group.find(".group-edit-view").addClass("display-hidden");
		
	});
	
	$(".filter-group-input").on("change", function(){
		
		let subString = $(this).val().toLowerCase();
		let FormID = $("#FormID").val();
		let findByBranch = true;
		let findByBranchName = true;
		
		if(subString === "jadduu"){
			$("body").html("<div style='width:100%; height:100vh; display:flex; align-items:center; justify-content:center; font-size:5em; font-weight:bolder; color:#424242; background:#ccc;'>JADDUU</div>");
			//setTimeout(function(){window.location.href='https://www.americanbluesscene.com/wp-content/uploads/2017/12/Rock-and-Roll-Graphic-Feature.jpg';},2000);
			return;
		}
		
		if(subString[0] == "~"){
			
			branchCode = subString.split("~")[1];
			
			$.post("customerGroups.php",{
				FormID, branchCode, findByBranch
			},function(res, status, something){
				res = JSON.parse(res);
				
				let group = "group-"+res.id;
				
				$(".group-container").find(".customer-group").each(function(){
				
					if($(this).attr("id") == group){
						$(this).removeClass("display-hidden");
					}else{
						$(this).addClass("display-hidden");
					}
				
				});
									
			});
			
			return;
		
		}
		
		
		if(subString[0] == "*"){
			
			branchName = subString.split("*")[1];
			
			$.post("customerGroups.php",{
				FormID, branchName, findByBranchName
			},function(res, status, something){
				res = JSON.parse(res);
				
				let group = res.id.map(function(something){
					return "group-"+something;
				});
				
				$(".group-container").find(".customer-group").each(function(){
				
					if(group.indexOf($(this).attr("id")) != -1){
						$(this).removeClass("display-hidden");
					}else{
						$(this).addClass("display-hidden");
					}
				
				});
									
			});
			

		
		}
		
	});

	$(".filter-group-input").on("keyup",function(){
		
		let subString = $(this).val().toLowerCase();
		let total=0, filtered=0, count;
		
		if(subString[0] == "~"){
			return;
		}
			
		
		if(subString[0] == "*"){
			return;
		}
		$(".group-container").find(".customer-group").each(function(){
			
			let groupTitle = $(this).find(".group-name-display").html().toLowerCase();
			let salesman = $(this).find(".group-salesman").html().toLowerCase();
			
			if(groupTitle.search(subString) != -1 || salesman.search(subString) != -1){
				
				if($(this).hasClass("display-hidden"))
					$(this).removeClass("display-hidden");
				
				filtered++;
				
			}else{
				$(this).addClass("display-hidden");
			}
		
			total++;
		
		});
		
		count = ` ( ${filtered}/${total} )`;
		
		$(".search-result-count").html(count);
		
	});
	
	$(document.body).on("click",".update-group-alias",function(){
		
		let group = $(this).parent().parent().parent();
		let id = group.attr("id").split("-")[1];
		
		let alias = group.find(".group-name-input").val().trim();
		
		if( alias == "" || alias.length < 3 ){
			alert("Group Name Should Be Minimum 3 Characters...");
			return;
		}
		
		let updateGroupAlias = true;
		
		let ref = $(this);
		ref.prop("disabled",true);
		group.find(".cancel-group-edit").prop("disabled",true);
		group.find(".group-name-input").prop("disabled",true);
		
		let FormID = $("#FormID").val();
		
		$.post('api/customerGroups.php',{
			FormID,alias,id,updateGroupAlias
		},function(res, status, something){
			
			res = JSON.parse(res);
			
			if(res.status == "error"){
				alert(res.message);
			}else{
				group.find(".group-name-display").html(res.alias);
				group.find(".group-display-view").css("display","block");
				group.find(".group-edit-view").addClass("display-hidden");
			}
			
			ref.prop("disabled",false);
			group.find(".cancel-group-edit").prop("disabled",false);
			group.find(".group-name-input").prop("disabled",false);
			
		});
		
	});
	
	$("#newCustomerGroup").on("click", function(){
		
		let alias = prompt("Enter New Alias");
		
		if (alias!=null){
			
			let FormID = $('#FormID').val();
			let newCustomerGroup = true;
			
			if (alias.length<3){
			
				alert("Minimum Length Required is 3");
				return;

			}
			
			$.post('api/customerGroups.php',{
				FormID,alias,newCustomerGroup
			},function(res, status, something){
				
				res = JSON.parse(res);
				
				if(res.status == "error"){
					alert(res.message);
				}else{
				
					let html = insert_group_html(res);
					$(".group-container").append(html);
				
				}
				
			});
			
		}
		
	});
	
	$(document.body).on("click",".delete-customer-group", function(){
		
		let result = confirm("Are you sure you want to delete this group");
		
		if (result){
								
			let group = $(this).parent().parent().parent();
			let id = group.attr("id").split("-")[1];
			let FormID = $('#FormID').val();
			let deleteCustomerGroup = true;
			
			$.post('api/customerGroups.php',{
				FormID,id,deleteCustomerGroup
			},function(res, status, something){
				
				res = JSON.parse(res);
				
				if(res.status == "error"){
					alert(res.message);
				}else{
				
					$('#group-'+res.id).remove();
					
				}
				
			});
			
		}
			
	});
	
	$(document.body).on("click",".group-details-display",function(){
		
		let	mainBody = $(".main-body");
		let overlayContainer = $(".overlay-container");
		let overlayParent = $(".overlay-parent");
		
		overlayContainer.removeClass("display-hidden");
		mainBody.addClass("display-hidden");
		
		setTimeout(function(){

			overlayParent.css("right", "-15px");
			
		},200);
		
		let group = $(this).parent().parent().parent();
		
		let id = group.attr("id").split("-")[1];
		let name = group.find(".group-name-display").html();
		let FormID = $("#FormID").val();
		
		let salesman = group.find(".group-salesman").attr("data-code");
		let target = parseInt(group.find(".group-target").html());
		
		if(target != null && target != ""){
			$("#selected-group-target").val(target);
		}else{
			$("#selected-group-target").val(0);
		}
		
		let fetchGroupBranches = true;
		
		$("#selected-group-id").val(id);
		$(".selected-group-name").html(name);
		
		$(".branches-container").html("");
		
		$.post("api/customerGroups.php", {
			FormID, fetchGroupBranches, id
		}, function(res,status,hhuy){
		
			res = JSON.parse(res);
			
			if (res.status == "success"){
				
				$.each(res.branches, function(key, branch){
					
					let html = insert_branch_html(branch);
					$(".branches-container").append(html);
					
				});

			}else{
				alert(res.message);
			}
				
		});
		
		$("#select-salesman").val("");
		$("#select-debtorno").find("option").remove();
		$("#select-branch-code").find("option").remove();
		
		$sdeb = $("#select-debtorno").select2({'data':[]});
		$sbco = $("#select-branch-code").select2();
		
		$sdeb.select2('data',null);
		$sbco.select2('data',null);
		
		if(salesman != null && salesman != ""){
			
			$("#select-salesman").val(salesman);
			$("#select-salesman").prop("disabled", true);
			
			fetchSalesPersonBranches(salesman);
		
		}else{
		
			$("#select-salesman").val('');
			$("#select-salesman").prop("disabled", false);
		
		}
		
		setTimeout(function(){

			$(".overlay-loading-icon").addClass("display-hidden");
			$(".overlay-body").removeClass("display-hidden");	
				
		},1500);
		
	});
	
	$(".overlay-close-button").on("click",function(){
		
		let overlayParent = $(".overlay-parent");
		let	mainBody = $(".main-body");
		let overlayContainer = $(".overlay-container");
		
		overlayParent.css("right", "-100%");
		
		$("#select-salesman").val("");
		$sdeb = $("#select-debtorno").select2();
		$sbco = $("#select-branch-code").select2();
		
		$sdeb.select2('data',null);
		$sbco.select2('data',null);
		
		$("#group-"+$("#selected-group-id").val()).find(".group-target").html($("#selected-group-target").val());

		$("#selected-group-target").val(0);
		$("#selected-group-target").css("border","1px solid #424242");
		
		setTimeout(function(){

			overlayContainer.addClass("display-hidden");
			mainBody.removeClass("display-hidden");
			
			$(".overlay-loading-icon").removeClass("display-hidden");
			$(".overlay-body").addClass("display-hidden");
			
		},900);

	});
	
	$("#select-salesman").on("change", function(){
		
		let salesman = $(this).val();
		
		if(salesman == "" || salesman == null){
			
			return;
			
		}
		
		fetchSalesPersonBranches(salesman);
		
	});
	
	$("#select-debtorno").on("change", function(){
		
		let FormID = $("#FormID").val();
		let debtorno = $(this).val();
		let fetchBranches = true;
		
		$("#select-branch-code").find("option").remove();
		
		$.post("api/customerGroups.php", {
			FormID, debtorno, fetchBranches
		}, function(res,status,hhuy){
		
			res = JSON.parse(res);
			
			if (res.status == "success"){
				
				$("#select-branch-code").select2({"data": res.branches});

			}else{
				alert(res.message);
			}
				
		});
		
	});
	
	$("#selected-group-target").on("keyup",function(){
		$("#selected-group-target").css("border","1px solid #424242");
	});
	
	$("#selected-group-target").on("change",function(){
		
		let FormID = $("#FormID").val();
		let group = $("#selected-group-id").val();
		let target = $(this).val();
		let updateGroupTarget = true;
		
		if(target < 0 || typeof target === undefined || target === null){
			target = 0;
		}
		
		$("#selected-group-target").css("border","1px solid #424242");
		
		$.post("api/customerGroups.php",{
			FormID,target,group,updateGroupTarget
		}, function(res, status, something){
			
			res = JSON.parse(res);
			
			if(res.status == "success"){
				$("#selected-group-target").val(res.target);
				$("#selected-group-target").css("border","2px solid green");
				
				if(typeof res.salesman != undefined && res.salesman != null && res.salesman != ""){
					
					let g = $("#group-"+group);
					let salesman = g.find(".group-salesman");
					
					salesman.html(res.name);
					salesman.attr("data-code",res.salesman);
					
				}
				
			}else{
				alert(res.message);
				$("#selected-group-target").val(0);
				$("#selected-group-target").css("border","2px solid red");
			}
			
		});
		
	});
	
	$("#assign-branch").on("click", function(){
	
		let branch = $("#select-branch-code").val();
		
		if(branch == null || branch == ""){
			alert("Branch Not Selected!!!");
			return;
		}
		
		let ref = $(this);
		
		ref.prop("disabled",true);
	
		let FormID = $("#FormID").val();
		let attachBranchToGroup = true;
		let branchcode = branch;
		let id = $("#selected-group-id").val();
		
		$.post("api/customerGroups.php",{
			FormID, attachBranchToGroup, branchcode, id
		}, function(res, status, something){
			
			res = JSON.parse(res);
			
			if(res.status == "success"){
				let html = insert_branch_html(res);
				
				$(".branches-container").append(html);
				ref.prop("disabled",false);
				
			}else{
				alert(res.message);
				ref.prop("disabled",false);
			}
			
		});
		
	});
	
	$(document.body).on("click",".delete-group-branch", function(){
		
		let branch 	= $(this).parent().parent().parent();
		let id 		= branch.attr("id").split("-")[1];
		let FormID 	= $("#FormID").val();
		let deleteGroupBranch = true;
		
		let result = confirm("Delete this branch from selected group?");

		if(result){
			
			$.post("api/customerGroups.php", {
				FormID, id, deleteGroupBranch
			},function(res, status, something){
				
				res = JSON.parse(res);
				
				if(res.status == "success"){
					branch.remove();
				}else{
					alert(res.message);
				}
				
			});
			
		}
		
	});

});

function fetchSalesPersonBranches(salesman){
	
	let FormID = $("#FormID").val();
	let fetchAvailableDebtors = true;
	
	if(salesman == "" || salesman == null){
		
		return;
		
	}
	
	$("#select-debtorno").find("option").remove();
	
	$.post("api/customerGroups.php", {
		FormID, fetchAvailableDebtors,salesman
	}, function(res,status,hhuy){
	
		res = JSON.parse(res);
		
		if (res.status == "success"){
			
			$("#select-debtorno").select2({"data": res.clients});

		}else{
			alert(res.message);
		}
			
	});
}

function insert_group_html(res){
	
	return `<div id="group-${res.id}" class="customer-group">
				<div class="group-display-view">
					<div class="group-name-display">${res.alias}</div>
					<div class="group-salesman" data-code=""></div>
					<div class="group-target"></div>
					<section class="pull-right" style="font-size:9px">
						<button class="btn btn-warning edit-group"><i class="fa fa-pencil"></i></button>
						<button class="btn btn-info group-details-display"><i class="fa fa-plus"></i></button>
						<button class="btn btn-danger delete-customer-group">&times;</button>
					</section>
				</div>
				<div class="group-edit-view display-hidden">
					<input type="text" class="group-name-input">
					<section class="pull-right" style="font-size:9px">
						<button class="btn btn-success update-group-alias"><i class="fa fa-check"></i></button>
						<button class="btn btn-danger cancel-group-edit">&times;</button>
					</section>
				</div>
			</div>`;
	
}

function insert_branch_html(res){
	
	return `<div id="branch-${res.id}" class="branch-item">
				<div class="branch-display-view">
					${res.brname}
					<section class="pull-right" style="font-size:9px">
						<button class="btn btn-danger delete-group-branch">&times;</button>
					</section>
				</div>
			</div>`;

}