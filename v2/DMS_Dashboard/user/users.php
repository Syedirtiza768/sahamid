<?php
session_start();
include 'config.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../sidebarStyles.css">
    <link rel="stylesheet" href="./links/bootstrap.css">
    <script src="../js/jquery.min.js"></script>
    <script src="./links/bootstrap.js"></script>
    <title>DMS</title>
</head>

<body>
    <div class="nav-btn">Menu</div>
    <div class="container">

        <div class="sidebar">
            <nav>
            <h1 style="color: white;
                margin-left: 42px;
                margin-top: 30px;
                margin-bottom: 20px;
                font-size: 12px;
                text-decoration: underline;"> Document Managment System</h1>

                <!-- <img id='index-page' src="../img/imageedit_1_6978996357.png" alt=""> -->
                <ul>
                    <!-- <li class="active"><a href="#">Dashboard</a></li> -->
                    <li style="font-size: 100%;"><a class='show-category'>Category</a></li>
                        <li style="font-size: 100%;"><a >Users</a></li>
                        <li style="font-size: 100%;"><a class="edit-perm-page">Edit Permission</a></li>
                    <li style="font-size: 100%;"><a class="add-perm-page">Add Permission</a></li>
                </ul>
            </nav>
        </div>

        <div class="user_sidebar" style="left: 100px;";>
                    <br><h3 style="text-align:center; color: white; " >  <b>Users List</b> </h3><br>
                    <nav>
                    <input type="text" id="myInput" onkeyup="myFunction()" placeholder="Search for names..">
                    <ul id="myUL">
                    <?php
                    $result = mysqli_query($conn,"SELECT * FROM www_users");
                        while($user_row = mysqli_fetch_array($result)) {
                    ?>
                            <li style="color:black"><a  data-toggle='modal' class='clicked_user' user-id="<?php echo $user_row["userid"]; ?>"><?php echo $user_row["userid"]; ?></a></li>
                            
                            
                        <?php 
                       
                        }
                        ?>  
                        </ul>
                    </nav>
         </div>
        
    </div>


        <div id="assign_category" class="modal fade" data-keyboard="false" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">						
                        <h4 class="modal-title">Allow User Permission To Show Category</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                    </div>
                    <!-- <b><h1 style="font-size: 35px; margin-left: 45px; margin-bottom: 15px;">Select Category To Show User On Main Page</h1></b> -->
                    <div class="modal-body">
                    <div class="table-title" id="table">
           
                     </div>
                        
                    </div>
                </div>
             </div>

        </div>

        <!-- Show category page-->
        <script>
            $(document).on('click', '.show-category', function() {
                window.location.replace('../category/category.php');
            });
        </script>

    <!-- Show editPerm page-->-->
    <script>
        $(document).on('click', '.edit-perm-page', function() {
            window.location.replace('../editPerm/editPerm.php');
        });
    </script>

    <!-- Show addPerm page-->-->
    <script>
        $(document).on('click', '.add-perm-page', function() {
            window.location.replace('../addPerm/addPerm.php');
        });
    </script>

<script>
function myFunction() {
    var input, filter, ul, li, a, i, txtValue;
    input = document.getElementById("myInput");
    filter = input.value.toUpperCase();
    ul = document.getElementById("myUL");
    li = ul.getElementsByTagName("li");
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("a")[0];
        txtValue = a.textContent || a.innerText;
        if (txtValue.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";
        }
    }
}
</script>


<script>
    $(document).on("click", ".clicked_user", function() {
    var user_id = $(this).attr("user-id");
    $('#user_id').val(user_id);
    $.ajax({
        data: {'user_id': user_id},
        type: "post",
        url: "u_id.php",
         success: function (res, status) {
            if (status == "success") {
                $("#table").html(res); 
                $('#assign_category').modal('show');
            }
        }
        // success: function(dataResult) {
        //     console.log(dataResult);
        //     $('#assign_category').modal('show');

        // }
    });
});
</script>

<script>
    $(document).on("click", ".user_checkbox", function() {
    var cat_id = $(this).attr("cat-id");
    var user_id = $(this).attr("users-id");
    $.ajax({
        data: {'user_id': user_id, 'cat_id':cat_id },
        type: "post",
        url: "user_perm.php",
        success: function(dataResult) {
            console.log(dataResult);

        }
    });

});
</script>

</body>

</html>