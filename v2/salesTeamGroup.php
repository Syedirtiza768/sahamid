<?php


$active = "dashboard";

include_once("config.php");
session_start();
// if(isset($_POST['findByBranch'])){

// 	$SQL = "SELECT * FROM cgdetails WHERE branchcode='".$_POST['branchCode']."'";
// 	$res = mysqli_query($db, $SQL);

// 	if(mysqli_num_rows($res) == 1){

// 		$row = mysqli_fetch_assoc($res);
// 		echo json_encode(['status' => 'success', 'id' => $row['cgid']]);

// 	}else{
// 		echo json_encode(['status' => 'error','id' => 'none']);
// 	}

// 	return;

// }
// if(isset($_POST['findByBranchName'])){

// $SQL = "SELECT * FROM cgdetails 
// 			INNER JOIN custbranch ON( cgdetails.branchcode = custbranch.branchcode
// 			AND custbranch.brname LIKE '%".str_replace(" ","%",$_POST['branchName'])."%')";
// 	$res = mysqli_query($db, $SQL);

// 	if(mysqli_num_rows($res) >= 1){
// 		$arr=[];
// 		while($row = mysqli_fetch_assoc($res))
// 			$arr[] = $row['cgid'];

// 		echo json_encode(['status' => 'success', 'id' => $arr]);

// 	}else{
// 		echo json_encode(['status' => 'error','id' => 'none']);
// 	}

// 	return;

// }

include_once("includes/header.php");
include_once("includes/sidebar.php");
?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="assets/bower_components/jquery/dist/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<?php
$SQL = "SELECT * 
			FROM salesteam";
$teams = mysqli_query($db, $SQL);

$SQL = "SELECT * FROM salesman";
$salesmen = mysqli_query($db, $SQL);

?>
<link rel="stylesheet" href="assets/customerGroups.css">
<input type="hidden" id="FormID" value="<?php ec($_SESSION['FormID']); ?>">
<div class="content-wrapper main-body">

    <section class="content-header">
        <h1 class="text-center">
            <i class="fa fa-edit"></i>
            Manage Sales Teams
            <button class="btn btn-success" data-toggle="modal" data-target="#team-modal">
                <i class="fa fa-plus"></i>
                New Team
            </button>
        </h1>
        <h3 class="text-center">
            Search
            <input class="filter-group-input" placeholder="Team Search" />
            <label class="search-result-count"></label>
        </h3>
    </section>

    <!-- Create New Team Modal -->
    <div id="team-modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <a class="close" data-dismiss="modal">×</a>
                    <h3>New Team</h3>
                </div>
                <form id="create_team" name="contact" role="form">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="name">Team Name</label>
                            <input type="text" name="name" class="form-control" id="team_n">
                        </div>
                        <div class="form-group">
                            <label for="email">Team Lead</label>
                            <select class="form-control" id="team_l" name="team_lead">
                                <option value="" selected>Choose any one</option>
                                <?php
                                $SQL = "SELECT * FROM salesman ";
                                $result = mysqli_query($db, $SQL);
                                while ($row_salesman = mysqli_fetch_assoc($result)) {
                                ?>
                                    <option value="<?php echo $row_salesman['salesmanname']; ?>"><?php echo $row_salesman['salesmanname']; ?></option>
                                <?php }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Team Member</label>
                            <select class="js-example-basic-multiple" id="team_m" name="team_members[]" multiple="multiple">
                                <?php
                                $SQL = "SELECT * FROM salesman ";
                                $result = mysqli_query($db, $SQL);
                                while ($row_salesman = mysqli_fetch_assoc($result)) {
                                ?>
                                    <option value="<?php echo $row_salesman['salesmanname']; ?>"><?php echo $row_salesman['salesmanname']; ?></option>
                                <?php }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-success" id="submit">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <section class="group-container">
                    <?php while ($row = mysqli_fetch_assoc($teams)) { ?>
                        <div id="group-<?php ec($row['id']); ?>" class="customer-group">
                            <div class="group-display-view">
                                <div class="group-name-display"><b>Team: </b><?php ec($row['name']); ?></div>
                                <div class="group-salesman" data-code="<?php ec($row['lead']); ?>">
                                    <b>Team Lead: </b><?php ec($row['lead']); ?>
                                </div>
                                <?php
                                $members = $row['members'];
                                $members_array = explode(",", $members);
                                $result = count($members_array);
                                ?>
                                <div class="group-target"><b>Members: </b><?php ec($result); ?></div>
                                <section class="pull-right" style="font-size:9px; padding-top: 20px">
                                    <button class="btn btn-success editTeam" data-id="<?php ec($row['id']) ?>" data-toggle="modal" href="#team-edit-modal">
                                        <i class="fa fa-pencil"></i></button>
                                    <button class="btn btn-danger deleted-customer-group">&times;</button>
                                </section>
                            </div>
                        </div>
                    <?php } ?>
                </section>
            </div>
        </div>
    </section>

    <!-- Edit Team Modal -->
    <div id="team-edit-modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <a class="close" data-dismiss="modal">×</a>
                    <h3>New Team</h3>
                </div>
                <form id="edit_team" name="contact" role="form">
                    <div class="modal-body">
                        <div class="form-group">
                            <input type="hidden" id="edit_team_id">
                            <label for="name">Team Name</label>
                            <input type="text" name="name" class="form-control" value="<?php ec($_SESSION['team_id']) ?>"
                             id="edit_team_n">
                        </div>
                        <div class="form-group">
                            <label for="email">Team Lead</label>
                            <select class="form-control" id="edit_team_l" name="team_lead">
                                <option value="" selected>Choose any one</option>
                                <?php
                                $SQL = "SELECT * FROM salesman ";
                                $result = mysqli_query($db, $SQL);
                                while ($row_salesman = mysqli_fetch_assoc($result)) {
                                ?>
                                    <option value="<?php echo $row_salesman['salesmanname']; ?>"><?php echo $row_salesman['salesmanname']; ?></option>
                                <?php }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="message">Team Member</label>
                            <select class="js-example-basic-multiple" id="edit_team_m" name="team_members[]" multiple="multiple">
                                <?php
                                $SQL = "SELECT * FROM salesman ";
                                $result = mysqli_query($db, $SQL);
                                while ($row_salesman = mysqli_fetch_assoc($result)) {
                                ?>
                                    <option value="<?php echo $row_salesman['salesmanname']; ?>"><?php echo $row_salesman['salesmanname']; ?></option>
                                <?php }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-success" id="submit">
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>



<script>
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2({
            dropdownAutoWidth: true,
            multiple: true,
            width: '98%',
            height: '30px',
            placeholder: "  Select Multiple",
            allowClear: true
        });

        $(document).on("click", ".editTeam", function() {
            var id = $(this).data('id');
            $.ajax({
                type: "POST",
                url: "api/editTeamData.php",
                cache: false,
                data: {
                    id: id
                },
                success: function(response) {
                    var respData = JSON.parse(response);
                    var split = respData['members'].split(',');
                    $(".modal-body #edit_team_m").select2().val(split).trigger('change'); 
                    $(".modal-body #edit_team_id").val(respData['id']);
                    $(".modal-body #edit_team_n").val(respData['name']);
                    $(".modal-body #edit_team_l").val(respData['lead']).change();
                },
                error: function() {
                    alert("Error");
                }
            });

            // $(".modal-body #bookId").val(myBookId);
            // As pointed out in comments, 
            // it is unnecessary to have to manually call the modal.
            // $('#addBookDialog').modal('show');
        });

        $("#create_team").submit(function(event) {
            submitForm();
            return false;
        });

        function submitForm() {
            var name = $('#team_n').val();
            var lead = $('#team_l').val();
            var members = $('#team_m').val();
            members = members.join();
            $.ajax({
                type: "POST",
                url: "api/saveTeam.php",
                cache: false,
                data: {
                    name: name,
                    lead: lead,
                    members: members
                },
                success: function(response) {
                    window.location.reload();
                },
                error: function() {
                    alert("Error");
                }
            });
        }
    });


    // Edit Form
    $("#edit_team").submit(function(event) {
            editForm();
            return false;
        });

        function editForm() {
            var name = $('#edit_team_n').val();
            var lead = $('#edit_team_l').val();
            var members = $('#edit_team_m').val();
            var id = $('#edit_team_id').val();
            members = members.join();
            $.ajax({
                type: "POST",
                url: "api/editTeam.php",
                cache: false,
                data: {
                    id:id,
                    name: name,
                    lead: lead,
                    members: members
                },
                success: function(response) {
                    window.location.reload();
                },
                error: function() {
                    alert("Error");
                }
            });
        }



        // Delete form
    $(document.body).on("click", ".deleted-customer-group", function() {
        let result = confirm("Are you sure you want to delete this group");

        if (result) {

            let group = $(this).parent().parent().parent();
            let id = group.attr("id").split("-")[1];

            $.ajax({
                type: "POST",
                url: "api/deleteTeam.php",
                cache: false,
                data: {
                    id: id
                },
                success: function(response) {
                    $('#group-' + response).remove();
                },
                error: function() {
                    alert("Error");
                }
            });

        }
    });
</script>

<?php
include_once("includes/footer.php");
?>

<script src="assets/customerGroups.js?v=<?php echo rand(1, 99999); ?>"></script>

<?php
include_once("includes/foot.php");
?>