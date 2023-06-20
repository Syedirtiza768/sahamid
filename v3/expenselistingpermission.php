<?php

$active = "reports";
//	$AllowAnyone = true;

include_once("config.php");
if(!userHasPermission($db, 'expense_listing_permissions')) {


    header("Location: /sahamid/");
    exit;
}

if(isset($_GET['json'])){
    $username=$_GET['userID'];
    $SQL = "SELECT can_access FROM expense_listing_access WHERE user = '$username'";

    $res = mysqli_query($db, $SQL);
    $assignedTabs=[];
    while($row=mysqli_fetch_assoc($res))
    {
        $assignedTabs[]=$row['can_access'];
    }

   $SQL = "SELECT * FROM pctabs GROUP BY tabcode";

    $res = mysqli_query($db, $SQL);
    $response = [];
    $today = time();

    while($row = mysqli_fetch_assoc($res)){

        if(in_array($row['tabcode'],$assignedTabs))
            $row['checked']="checked";


        $response[] = $row;
    }

    echo json_encode($response);
    return;
}
$SQL2 = "SELECT  * FROM www_users";

$res2 = mysqli_query($db, $SQL2);

include_once("includes/header.php");
include_once("includes/sidebar.php");

?>
    <style>
        th,td{
            text-align: left;

        }

        #searchresults_length label select {
            color: black;

        }

        #searchresults_length,#searchresults_info{
            color: #efefef;
        }

        #searchresults_filter label,.datatables-footer,.datatables-header{
            width: 100%;
            float: right;


        }
        input{
            float: right;
        }

        #searchresults thead th{
            border: 1px white solid;
            border-bottom: 0px;
        }

        #searchresults tfoot th{
            border: 1px white solid;
            border-top: 0px;
        }

        #searchresults td{
            border: 1px black solid;
            width: 1%;
        }
        .request-container{
            display:flex;
            justify-content: center;
            margin: 15px;
            margin-top: 15px;
            margin-bottom: 50px;
        }

        .footer{
            background:#efefef;
            bottom:0; width:100%;
            text-align:center;
            padding: 5px;
            position: fixed;
        }

        .form{
            float: left;
            display: flex;
            flex-direction: column;

            width: 300px;
            height: 400px;

            overflow: scroll;
        }

        .request-header{
            text-align: center;
            background: #e0e0e0;
            border-radius: 10px 10px 0 0;
            padding: 5px;
            margin-bottom: 0;
        }

        .request-header button{
            padding: 1px 10px !important;
        }

        .request-body{
            background: white;
            padding: 25px 15px;
            display: flex;
            flex-direction: column;
        }

        .request-body label{
            margin: 10px 0;
        }

        .input{
            width: 100%;
            border: 1px solid #ccc;
            padding: 5px;

        }

        .request-submit{
            border-radius: 0 0 7px 7px;
        }

        .existing-requests{
            flex: 1;
            margin-left: 10px;
            height: calc(100vh - 150px);
            overflow: hidden;
            overflow-y: scroll;
        }

        .request{
            display: flex;
            background: #efefef;
            margin-bottom: 5px;
        }

        .request .details{
            flex: 1;
            padding: 5px;
        }

        .request .details p{
            margin-bottom: 0;
            display: inline-block;
        }

        .users{
            background: #efefef;
            padding: 7px;
            float:left;
            border: 1px solid #ccc;
            border-radius: 7px;
            display: flex;
            justify-content: space-between;
            cursor: pointer;
        }

        .userssearch{
            background: #efefef;
            padding: 7px;
            float:left;
            border: 1px solid #ccc;
            border-radius: 7px;
            display: flex;
            justify-content: space-between;
            cursor: pointer;
        }

        .users:hover{

            background: green;
            color: white;

        }

        .selected{
            background: #efefef;
            color: green;
        }

        .qtyInput{
            width: 80px;
            border-radius: 7px;
            padding: 4px;
        }

        #scrollUp{
            position: fixed;
            bottom: 50px;
            right: 0;
            padding:10px;
            color: white;
            background: #efefef;
        }

        .inp{
            border: 1px solid #E5E7E9;
            border-radius: 6px;
            height: 46px;
            padding: 12px;
            outline: none;
        }

        .actinf{
            font-size: 10px;
        }

        .fit{
            width: 1%;
        }
        .datechange{
            width: 135px;
            padding: 5px;
            border-radius: 7px;
        }

    </style>
    <input type="hidden" value="<?php ec($_SESSION['FormID']); ?>" id="FormID">
    <div class="content-wrapper">


        <section class="content" id="content">
            <h3 class="request-header">
                Expense Listing Assignments
            </h3>
            <div class="form" id="form">


                <div class="request-body">
                   <div class="userssearch" ><input class="namesearch" style="width: 100%; border: lightgrey;" placeholder="Search" type = "text"></div>
                    <?php while($users = mysqli_fetch_assoc($res2)){ ?>
                        <div class="users" data-user="<?php echo $users['userid']; ?>">
                            <span class = "usern" id="<?php echo $users['userid']; ?>"><?php echo '<span hidden>'.strtolower($users['realname'])."</span> ".$users['realname']; ?></span>

                        </div>

                    <?php } ?>
                </div>
            </div>
            <div class="request-container">





                <div class= "data" style="float: left; width:100%;" id="data">


                    <div id="resultscontainer" class="" style=" background-color:#ecedf0;float: left;">

                        <table id="searchresults" width="100%" class="responsive table-striped">
                            <thead>
                            <tr style="background:#efefef; color:black">

                                <th class="fit">Tab Code</th>
                                <th class="fit">User</th>
                                <th class="fit">Assigner</th>
                                <th class="fit">Authorizer</th>
                                <th class="fit">Assign</th>

                            </tr>
                            </thead>
                            <tbody id="srb" style="color: black">

                            </tbody>
                            <tfoot>
                            <tr style="background:#efefef; color:white">

                                <th class="fit">Tab Code</th>
                                <th class="fit">User</th>
                                <th class="fit">Assigner</th>
                                <th class="fit">Authorizer</th>
                                <th class="fit">Assign</th>




                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>

    </div>

<?php
include_once("includes/footer.php");
?>
    <script>

        table=null;
        table=$('#searchresults').DataTable({
            "pageLength": -1,
            <?php
            if(userHasPermission($db, "can_print_csv")){
                echo "dom: 'Bfrtip',
                
        buttons: [
                
            ],";}?>



            columns:[

                {"data":"tabcode"},
                {"data":"usercode"},
                {"data":"assigner"},
                {"data":"authorizer"},
                {"data":"checked"},



            ],
            "columnDefs": [



                {
                    className: "fit center",
                    "render": function ( data, type, row ) {
                        let html="";
                        let background="";


                            html+='<input class="check '+data+'" type="checkbox"'+ data+' data-user="" data-canaccess="'+row['tabcode']+'"/>';

                        return html;
                    },
                    "targets": [ 4 ]
                },



            ],
            drawCallback: function () {
                let api = this.api();
                $( api.table().footer() ).html(
                    `<tr style="background-color:#efefef; color:white">
		     			<th></th>
		     			<th></th>
		     			<th></th>
		     			<th></th>
		     			<th></th>



		     		</tr>`
                );
            }
        });

        $.get("expenselistingpermission.php?json", function(res, status){
            res = JSON.parse(res);
            table.clear().draw();
            table.rows.add(res).draw();
        });

        $(document.body).on("change", ".selectall", function(){
            let ref = $(this);
            let value = ($(this).is(":checked")) ? 1 : 0;
            if (value) {
                $('.check').each(function () {
                    $(this).prop("checked", true);

                });
            }

        });
        $('#searchresults').on('change','.check',function(){
            let can_access = $(this).attr('data-canaccess');
            let user = $(this).attr('data-user');
            let value = ($(this).is(":checked")) ? 1 : 0;
            $.post("api/updateexpenselistingPermissions.php",{can_access,value,user},function(data, status){
                console.log("Data: " + data + "\nStatus: " + status+value);


            });
            if (value){

                console.log($(this).attr('data-canaccess'));
                $(this).parent().parent().css("background-color" , "green");
            }else
            {

                console.log($(this).attr('data-canaccess'));
                $(this).parent().parent().css("background-color" , "white");
            }

        });


        $(document.body).on("change", ".comment", function(){
            let ref = $(this);
            let column = ref.attr("data-column");
            let invoice = ref.attr("data-invoice");
            let value = ref.val();
            let update = true;
            $.post("expenselistingpermission.php",{column,value,invoice,update}, function(res, status){
                if(res == "success"){
                    alert("Updated Successfully");
                }
            });
        });
        $(document.body).on("keyup", ".namesearch", function(){
            let ref=$(this).val();
            //console.log(ref);

            $('.users').hide();

                let r=ref.toLowerCase();
                console.log(r);
                $( `span:contains('${r}')` ).parent().show();



        });

        table.columns().every( function () {
            let that = this;
            $('input', this.footer()).on('keyup change', function (){
                if(that.search() !== this.value){
                    that.search(this.value).draw();
                }
            });
        });
        var FormID = '<?php echo $_SESSION['FormID']?>';
        var userID="";

        $(".users").on("click", function(){

            let ref = $(this);

            if(ref.hasClass("selected")){
                return;
            }

            $(".users").each(function(){
                $(this).removeClass("selected");
            });

            ref.addClass("selected");

            let getUserRequests = true;

            if (typeof ref.attr("data-user") === 'undefined') {
                userID="";
            }
            else {
                userID = ref.attr("data-user");
            }
            $.get("expenselistingpermission.php?json&userID="+userID, function(res, status){
                res = JSON.parse(res);
                table.clear().draw();
                table.rows.add(res).draw();
                $(".checked").parent().parent().css("background-color" , "green");
                $('.check').each(function(){
                    $(this).attr('data-user',userID);

                });
            });




        });


    </script>
    <script>
        $(document).ready(function () {


        })

    </script>
<?php
include_once("includes/foot.php");
?>