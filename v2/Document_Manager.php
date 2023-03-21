<?php 
	$active = "reports";
    include 'config1.php';
	include_once("config.php");
	include_once("includes/header.php");
    include_once("includes/sidebar.php");

?>

<!DOCTYPE html>
<html>
<head>
<head>
    <meta charset="UTF-8">
    <title>Document Mangement System</title>
    <link rel="stylesheet" href="links/bootstrap.css">
    <link rel="stylesheet" href="links/icons.css">
    <link rel="stylesheet" href="docStyle.css">
    <link rel="stylesheet" href="">
    <link rel="stylesheet" type="text/css" href="datatable/dts.css">
    <script src="js/jquery.min.js"></script>
<link rel="stylesheet" href="links/bootstrap_selects.css">
<script src="links/bootstrap_select.js"></script>
    <meta name="viewport" content="initial-scale=1.0; maximum-scale=1.0; width=device-width;">
</head>

<style>
    body{border:0}
    .dataTables_scroll{position:relative}
    .dataTables_scrollHead{margin-bottom:40px;}
    .dataTables_scrollFoot{position:absolute; top:38px}

    #My_word_form {
        display: none;
        width: 300px;
        padding: 14px;
        background: none;
    }
    
    #file_upload {
        display: none;
        width: 300px;
        padding: 14px;
        background: none;
    }
</style>

<body class="fixed sidebar-mini sidebar-mini-expand-feature skin-black"> 
<div class="paceDiv"></div>
<div class="wrapper">
<header class="main-header" data-turbolinks="false">
  <a class="logo">
    <span class="logo-mini"><b>SA</b>H</span>
    <span class="logo-lg"><b>SA</b> Hamid</span>
  </a>
  <nav class="navbar navbar-static-top">
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </a>
    <div class="navbar-custom-menu">
      <er class="nav navbar-nav">
        <?php if($_SESSION['UserID'] == "admin" || (isset($_SESSION['orignalUserID']) && $_SESSION['orignalUserID'] == "admin")) { ?>
        <li class="user" style="padding: 10px; border-right: 1px solid #ccc; display: none;">
            <form action="/sahamid/loginAs.php">
              <input type="text" name="UserID" style="border:1px solid #424242; border-radius: 7px; padding: 3px" placeholder="Boom">
              <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </li>
        <?php } ?>
        <li class="user" style="padding: 15px">
            <span><?php echo date('d/m/Y h:i A'); ?></span>
        </li>
          <!--<li>

          <ul class="nav navbar-nav navbar-right">
              <li class="dropdown inbox">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-bell" style="font-size:18px;"><span class="label label-pill label-danger count" style="border-radius:10px;z-index: "></span> </span></a>
                  <ul class="dropdown-menu scrollable-menu "></ul>
              </li>
          </ul>
          </li>-->
        <li class="dropdown user user-menu">
          <a href="#" class="dropdown-toggle" data-toggle="user-menu">
            <img src="assets/dist/img/default.jpg" class="user-image">
            <span class="hidden-xs"><?php echo $_SESSION['UsersRealName']; ?></span>
          </a>


            <!-- <li class="header">
                <a href="#" class="" style="padding: 10px">Profile</a>
            </li> -->

            <li class="header"><a href="<?php echo $NewRootPath; ?>Logout.php" style="padding: 10px"><span class="glyphicon glyphicon-log-out" style="font-size:18px;"></a></li>

        </li>
      </ul>
    </div>
  </nav>
</header>

</style>
<input type="hidden" value="<?php ec($_SESSION['FormID']); ?>" id="FormID">
<div class="content-wrapper">
<div id="wrapper">
        <h3 style="text-align: center; margin-top: 5%;"> <b> Document Management System </b></h3><br>
       
        <?php
                $addPerm="";
                $user_id = $_SESSION['UserID'];
                $result = mysqli_query($conn,"SELECT permission FROM addPerm WHERE userid='$user_id' ");
                    while($row = mysqli_fetch_array($result)) {
                        $addPerm = $row['permission'];
                    }
                        if($addPerm==1){
        ?>
                            <button style="width: 203px;
                height: 26px;
                border-radius: 40px;
                background-color: rgb(27, 30, 36);
                color: white;
                margin-top: 10px;" type="button" class="btn btn-primary" id='Add-File'><span class="bi bi-plus-circle blue-color"></span> Add New Documment</button>
                 
        <?php        
                }
                ?>
        <div><br>
            <form id="file_upload" enctype="multipart/form-data">
                <input style=" background-color: rgb(235, 235, 235); " name="name" type="text" id="name" placeholder="Doc Name*" required /><br>
                <!-- New doc number field -->
                <input style=" background-color:rgb(235, 235, 235); " name="number" type="text" id="number" placeholder="Doc Number*" required /><br>
                <!-- New category field -->
                <select id="category" name="category" class="selectpicker" multiple data-selected-text-format="count > 2" title='Choose one...' data-width=174px>
                <?php
                $result = mysqli_query($conn,"SELECT * FROM category");
                    while($row = mysqli_fetch_array($result)) {
                ?>
                    <option value="<?php echo $row["cat_name"]; ?>"><?php echo $row["cat_name"]; ?></option>
                <?php
                    }
                ?>    
                  </select><br>
                <!-- New doc Revision field -->
                <input style=" background-color:rgb(235, 235, 235); " type="text" name="revision" id="revision" pattern="[a-zA-Z0-9]+" placeholder="Revision Number*" required /><br>

                <!-- New doc date field -->
                <label for="Date">Date*:</label>
                <input style=" background-color:rgb(235, 235, 235); " max="<?php echo date("Y-m-d"); ?>" name="date" type="date" id="date" placeholder="Document Date" required /><br>
                <!-- <textarea id="s_desc" name="s_desc" placeholder="Short Description" maxlength="300"></textarea><br> -->
                <label for="pdf">PDF*:</label>
                <input name="pdf" type="file" id="pdf" accept="application/pdf" />
                <label for="word">Word*:</label>
                <input name="word" type="file" id="word" accept="application/msword" />
                <label for=" excel ">Excel*:</label>
                <input name="excel" type="file" id="excel" accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" />
                <label for=" powerpoint ">PowerPoint*:</label>
                <input name="ppt" type="file" id="ppt"  accept=".ppt, .pptx"/><br>
                <input id="btn-Upload" style="width: 203px;
                height: 26px;
                border-radius: 40px;
                background-color: rgb(27, 30, 36);
                color: white;
                margin-top: 10px;" type="submit" value="upload"></input>
            </form>
        </div>
        <br>
        <br>

        <main>
            <div id="table">
            
            </div>
        </main>

                </div>

     <!-- Edit Modal HTML -->
<div id="editDocumentModal" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<form id="update_form" enctype="multipart/form-data">
				<div class="modal-header">						
					<h4 class="modal-title">Edit Document</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<div class="modal-body">
					<input type="hidden" id="id" name="id" class="form-control id" required>					
					<div class="form-group">
						<label>Document Name</label>
						<input type="text" id="d_name" name="d_name" class="form-control d_name"  disabled>
					
                     
						<label>Document Number*</label>
						<input type="number" id="d_number" name="d_number" class="form-control d_number" required>


                        <label>Document Revision*</label>
						<input type="text" id="d_revision" name="d_revision" class="form-control d_revision" required>
					 
                     
						<label>Category</label>
                        <input type="text" name="d_category" id="d_category" class="form-control d_category" disabled>
					 
					 
						<label>Category Date*</label>
						<input type="date" id="d_date" name="d_date" class="form-control d_date" required>
					 
                     
                        <label for="pdf">PDF*:</label>
                        <input name="pdf_file" type="file" id="pdf_file"  class="form-control" accept="application/pdf"/>
                     
                     
                        <label for="word">Word*:</label>
                        <input name="word_file" type="file" id="word_file"  class="form-control" accept="application/msword"/>
                     
                     
                        <label for=" excel ">Excel*:</label>
                        <input name="excel_file" type="file" id="excel_file" class="form-control" accept="application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/>				
                        
                        <label for=" excel ">PowerPoint*:</label>
                        <input name="ppt_file" type="file" id="ppt_file" class="form-control" accept=".ppt, .pptx"/><br>				
 
					<input type="button" class="btn btn-default" data-dismiss="modal" value="Cancel"></input>
					<button type="submit" value="upload" class="btn btn-info" id="update">Update</button>
		
                    
                    </div>
                </div>
				
			</form>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
  console.log($('select').selectpicker());
});
</script>

<script>
    $(document).on('click', '.update-form', function(e) {
    var id = $(this).attr("data_id");
    var name = $(this).attr("data_name");
    var number = $(this).attr("data_number");
    var revision = $(this).attr("data_revision");
    var category = $(this).attr("data_category");
    var date = $(this).attr("data_date");
    $('.id').val(id);
    $('.d_name').val(name);
    $('.d_number').val(number);
    $('.d_revision').val(revision);
    $('.d_category').val(category);
    $('.d_date').val(date);
        
});
</script>

<script>
    $("#update").on("click", function(e){
        e.preventDefault();
        result = confirm('Are you sure you want to edit this?'); 
        if(result){
            var id = $('#id').val();
            var d_number = $('#d_number').val();
            var d_date = $('#d_date').val();
            var d_revision = $('#d_revision').val();
            var pdf_file = $("#pdf_file").prop('files')[0];
            var word_file = $("#word_file").prop('files')[0];
            var excel_file = $("#excel_file").prop('files')[0];
            var ppt_file = $("#ppt_file").prop('files')[0];
            var form_data = new FormData(); 
            form_data.append('id', $('#id').val());
            form_data.append('d_number', $('#d_number').val());
            form_data.append('d_revision', $('#d_revision').val());
            form_data.append('d_date', $('#d_date').val())
            form_data.append('pdf_file', pdf_file);
            form_data.append('word_file', word_file);
            form_data.append('excel_file', excel_file);
            form_data.append('ppt_file', ppt_file);
    $.ajax({
        data: form_data,
        type: "post",
        url: "backend/save.php",
        processData: false,
        contentType: false,
        cache: false,
        enctype: 'multipart/form-data',
        success: function(dataResult) {
            console.log(dataResult);
            $('#editDocumentModal').hide;
            location.reload();
        }
    });

}
else{
    location.reload();
}
});

</script>
    <!-- Show input form button -->
    <script>
        $(document).ready(function() {
            $("#Add-File").click(function() {
                $("#file_upload").toggle('slow');
            });
        });
</script>



    <!-- Script to save the Record -->
    <script>
        $("#file_upload").submit(function(e) {
            e.preventDefault();
            var pdfSize = 0;
            var wordSize = 0;
            var excelSize = 0;
            var pptSize = 0;
            var formData = new FormData(this);
            $('#name').val('');
            var my_cat = $('#category').val();
            $('#date').val('');
            $('#revision').val('');
            formData.append('my_cat',my_cat); 
            maxFileSize = 25 * 1024 * 1024;
            if($("#pdf")[0].files[0] != undefined){
                pdfSize= $("#pdf")[0].files[0].size;
            }
            if($("#word")[0].files[0] != undefined){
                wordSize = $("#word")[0].files[0].size;
            }
            if($("#excel")[0].files[0] != undefined){
                excelSize=$("#excel")[0].files[0].size;
            }
            if($("#ppt")[0].files[0] != undefined){
                pptSize = $("#ppt")[0].files[0].size;
            }
            
            if (pdfSize > maxFileSize || wordSize > maxFileSize || excelSize > maxFileSize || pptSize > maxFileSize) {
                alert('Sorry! You can only upload file less then 25MB');
            } else {
                
                $.ajax({
                    url: 'backend/upload_data.php',
                    type: 'post',
                    data: formData,
                    processData: false,
                    cache: false,
                    contentType: false,
                    success: function(data) {
                        alert(data);
                        load_data();
                        $("#file_upload").toggle('close');
                    },
                    error: function(data) {
                        console.log("error");
                        console.log(data);
                        load_data();
                    },
                });
            }
        });


        function load_data() {
            $.ajax({
                url: 'show_data.php',
                method: "GET",
                success: function(res, status) {
                    if (status == "success") {
                        console.log(html(res));
                        $("#table").html(res);
                        $('#myTable tfoot th').each( function () {
                        var title = $(this).text();
                        $(this).html( '<input type="text" style="width:150px;" placeholder="'+title+'" />' );
                    });
                    
                            table = $('#myTable').DataTable();
                            // Apply the search
                            table.columns().every( function () {
                                var that = this;
                                $( 'input', this.footer() ).on( 'keyup change', function () {
                                    if ( that.search() !== this.value ) {
                                        that
                                            .search( this.value )
                                            .draw();
                                    }
                                });
                            });
                    
                    }
                }
            });

        }
    </script>

    <!-- Auto load table when refresh -->
    <script>
        $.ajax({
            url: 'show_data.php',
            method: "GET",
            success: function(res, status) {
                if (status == "success") {
                    console.log(res);
                    $("#table").html(res);
                    load_data();
                }
            }
        })

        
        function load_data() {
            $.ajax({
                url: 'show_data.php',
                method: "GET",
                success: function(res, status) {
                    if (status == "success") {
                        $("#table").html(res);
                        $('#myTable tfoot th').each( function () {
                        var title = $(this).text();
                        $(this).html( '<input type="text" style="width:150px;" placeholder="'+title+'" />' );
                    });
                    
                            table = $('#myTable').DataTable();
                            // Apply the search
                            table.columns().every( function () {
                                var that = this;
                                $( 'input', this.footer() ).on( 'keyup change', function () {
                                    if ( that.search() !== this.value ) {
                                        that
                                            .search( this.value )
                                            .draw();
                                    }
                                });
                            });
                    
                    }
                }
            });

        }
    </script>


    <script type="text/javascript" src="datatable/dt.js"></script>
</div>


<?php
	include_once("includes/footer.php");
?>
<?php
	include_once("includes/foot.php");
?>