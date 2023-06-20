<?php

    $AllowAnyone = true;
    $active = "reports";

	include_once("config.php");

	include_once("includes/header.php");
	include_once("includes/sidebar.php");
	
	if(isset($_GET['ajax'])){

		$salesman = $_POST['salesman'];
        $cgid = $_POST['cgid'];
        $year = $_POST['year'];
		$target = $_POST['target'];
		
    echo	$SQL = "UPDATE cgassignments SET target=$target WHERE salesman='$salesman' AND cgid='$cgid' AND year='2020'";
		DB_query($SQL, $db);

		return;
	}


$SQL = "SELECT SUM(
				CASE WHEN (invoice.gst = '')
					THEN (ovamount)
				WHEN (invoice.services = 1) 
					THEN (ovamount/1.16)
				WHEN (invoice.services = 0)
					THEN (ovamount/1.17)
				END) as amount, debtortrans.branchcode FROM debtortrans
			INNER JOIN custbranch ON debtortrans.branchcode = custbranch.branchcode
			INNER JOIN invoice ON invoice.invoiceno = debtortrans.transno
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			WHERE debtortrans.reversed = 0
			AND debtortrans.type = 10
			AND invoice.inprogress = 0
			AND invoice.returned = 0";


$SQL .=	" AND invoice.invoicesdate >= '2019-01-01'
			AND invoice.invoicesdate <= '2019-12-31'
			GROUP BY branchcode";
$res = mysqli_query($db,$SQL);

$achieved = [];

while ($row=mysqli_fetch_assoc($res)){

    $achieved[$row['branchcode']] = $row['amount'];

}

$year=2019;
$SQL = "SELECT customergroups.id, customergroups.alias, cgdetails.branchcode,cgassignments.target 
			FROM cgassignments
			INNER JOIN cgdetails ON cgassignments.cgid = cgdetails.cgid
			INNER JOIN customergroups ON customergroups.id =  cgdetails.cgid 
			WHERE year='$year'
			ORDER BY customergroups.id desc";

$res = mysqli_query($db,$SQL);

$groups = [];
$branches = [];
$targets = [];

while ($row = mysqli_fetch_assoc($res)){

    $groups[$row['id']] = $row['alias'];
    $branches[$row['branchcode']] = $row['id'];
    $targets[$row['id']] = $row['target'];

}

$nAchieved = [];

foreach($achieved as $branch => $amount){

    if(isset($branches[$branch])) {

        if(!isset($nAchieved[$branches[$branch]])){
            $nAchieved[$branches[$branch]] = 0;
        }

        $nAchieved[$branches[$branch]] += $amount;

    }

}

$yaxis = [];

$dTargets = [];
$dAchieved = [];

foreach ($groups as $groupid => $groupname){

    $yaxis[] = $groupname;

    $dTargets[] = (int)$targets[$groupid];

    if(isset($nAchieved[$groupid])){
        $dAchieved[$groupid] = (int)$nAchieved[$groupid];
    }else{
        $dAchieved[$groupid] = 0;
    }

}
//print_r($dAchieved);
$i=0;
foreach ($dAchieved as $key=>$value)
{
    $SQL="SELECT customergroups.*,cgassignments.*,salesman.salesmanname from customergroups INNER JOIN cgassignments ON customergroups.id=cgassignments.cgid 
        INNER  JOIN salesman ON salesman.salesmancode = cgassignments.salesman WHERE customergroups.id=$key AND cgassignments.year='2019'";
    $row=mysqli_fetch_assoc(mysqli_query($db,$SQL));
    $data[]=$row;
    $data[$i]['target']=locale_number_format($row['target'],0);
    $cgid=$row['cgid'];
    $salesman=$row['salesman'];
    $SQLNewTarget="SELECT target FROM cgassignments WHERE cgassignments.cgid='$cgid' 
                                    AND cgassignments.salesman='$salesman' AND cgassignments.year='2020' ";
    $nextTarget=mysqli_fetch_assoc(mysqli_query($db,$SQLNewTarget))['target'];
   // ec(mysqli_fetch_assoc(mysqli_query($db,$SQLNewTarget))['target']);
    $data[$i]['nextTarget']=$nextTarget;
    $data[$i]['percentage']=round($value/$row['target']*100,2);
    $data[$i]['achieved']=locale_number_format($value,0);
    $i++;
/*
    $cgid=$row['cgid'];
    $salesman=$row['salesman'];
    $SQLA="INSERT INTO cgassignments(`cgid`, `salesman`, `target`, `year`)VALUES ('$cgid','$salesman',$value*1.5,'2019')";
    mysqli_query($db,$SQLA);
*/


}
$salesPersons = [];
$salesPersons=$data;
$SQL = "SELECT SUM(
				CASE WHEN (invoice.gst = '')
					THEN (ovamount)
				WHEN (invoice.services = 1) 
					THEN (ovamount/1.16)
				WHEN (invoice.services = 0)
					THEN (ovamount/1.17)
				END) as amount, debtortrans.branchcode FROM debtortrans
			INNER JOIN custbranch ON debtortrans.branchcode = custbranch.branchcode
			INNER JOIN invoice ON invoice.invoiceno = debtortrans.transno
			INNER JOIN salesman ON custbranch.salesman = salesman.salesmancode
			WHERE debtortrans.reversed = 0
			AND debtortrans.type = 10
			AND invoice.inprogress = 0
			AND invoice.returned = 0";


$SQL .=	" AND invoice.invoicesdate >= '2018-01-01'
			AND invoice.invoicesdate <= '2018-12-31'
			GROUP BY branchcode";
$res = mysqli_query($db,$SQL);

$achieved = [];

while ($row=mysqli_fetch_assoc($res)){

    $achieved[$row['branchcode']] = $row['amount'];

}

$year=2018;
$SQL = "SELECT customergroups.id, customergroups.alias, cgdetails.branchcode,cgassignments.target 
			FROM cgassignments
			INNER JOIN cgdetails ON cgassignments.cgid = cgdetails.cgid
			INNER JOIN customergroups ON customergroups.id =  cgdetails.cgid 
			WHERE year='$year'
			ORDER BY customergroups.id desc";

$res = mysqli_query($db,$SQL);

$groups = [];
$branches = [];
$targets = [];

while ($row = mysqli_fetch_assoc($res)){

    $groups[$row['id']] = $row['alias'];
    $branches[$row['branchcode']] = $row['id'];
    $targets[$row['id']] = $row['target'];

}

$nAchieved = [];

foreach($achieved as $branch => $amount){

    if(isset($branches[$branch])) {

        if(!isset($nAchieved[$branches[$branch]])){
            $nAchieved[$branches[$branch]] = 0;
        }

        $nAchieved[$branches[$branch]] += $amount;

    }

}

$yaxis = [];

$dTargets = [];
$dAchieved = [];

foreach ($groups as $groupid => $groupname){

    $yaxis[] = $groupname;

    $dTargets[] = (int)$targets[$groupid];

    if(isset($nAchieved[$groupid])){
        $dAchieved[$groupid] = (int)$nAchieved[$groupid];
    }else{
        $dAchieved[$groupid] = 0;
    }

}
//print_r($dAchieved);
$data=[];
$i=0;
foreach ($dAchieved as $key=>$value)
{
    $SQL="SELECT customergroups.*,cgassignments.*,salesman.salesmanname from customergroups INNER JOIN cgassignments ON customergroups.id=cgassignments.cgid 
        INNER  JOIN salesman ON salesman.salesmancode = cgassignments.salesman WHERE customergroups.id=$key";
    $row=mysqli_fetch_assoc(mysqli_query($db,$SQL));
    $data[]=$row;
    $data[$i]['target']=locale_number_format($row['target'],0);
    $cgid=$row['cgid'];
    $salesman=$row['salesman'];
    $SQLNewTarget="SELECT target FROM cgassignments WHERE cgassignments.cgid='$cgid' 
                                    AND cgassignments.salesman='$salesman' AND cgassignments.year='2020' ";
    $nextTarget=mysqli_fetch_assoc(mysqli_query($db,$SQLNewTarget))['target'];
    // ec(mysqli_fetch_assoc(mysqli_query($db,$SQLNewTarget))['target']);
    $data[$i]['nextTarget']=$nextTarget;
    $data[$i]['percentage']=round($value/$row['target']*100,2);
    $data[$i]['achieved']=locale_number_format($value,0);
    $i++;
    /*   $cgid=$row['cgid'];
        $salesman=$row['salesman'];
        $SQLA="INSERT INTO cgassignments(`cgid`, `salesman`, `target`, `year`)VALUES ('$cgid','$salesman',$value*1.5,'2019')";

        mysqli_query($db,$SQLA);
    */


}
$salesPersons2 = [];
$salesPersons2=$data;

$draft=[];
foreach ($salesPersons as $index => $item) {
    $draft[$item['cgid']]=$item;
    $draft[$item['cgid']]['target2']="";
    $draft[$item['cgid']]['percentage2']="";
    $draft[$item['cgid']]['achieved2']="";
    $draft[$item['cgid']]['difference']="";


}
foreach ($salesPersons2 as $index => $item) {

    $draft[$item['cgid']]['target2']=$item['target'];
    $draft[$item['cgid']]['percentage2']=$item['percentage'];
    $draft[$item['cgid']]['achieved2']=$item['achieved'];


}
$completed=[];
foreach ($draft as $key=>$value)
{
    $completed[]=$value;
}
//print_r($draft);

?>

<div class="content-wrapper">
    <section class="content-header">
		<h3 style="font-family: initial; margin:0">Update Sale Person Customer Groups Sales Target</h3>
    </section>

    <section class="content">
	    <div class="row">

			<div class="col-md-12">
			
				<table id="searchresults" class="table table-striped" style="width:100%">
			
					<thead>
						<tr style="background:#222d32; color:white">
                            <th class="fit">Customer Group</th>
                            <th class="fit">Salesman</th>
                            <th class="fit">Target 2018</th>
                            <th class="fit">Achieved</th>
                            <th class="fit">Percentage</th>
                            <th class="fit">Target 2019</th>
                            <th class="fit">Achieved</th>
                            <th class="fit">Percentage</th>
                            <th class="fit">Difference</th>
                            <th class="fit">Target 2020</th>
                            <th class="fit" style="visibility: hidden"></th>
                            <th class="fit" style="visibility: hidden"></th>
						</tr>
					</thead>
					<tbody>
						<?php $count = 1; foreach($completed as $salesPerson){

						    ?>

							<tr>
								<td><?php ec($salesPerson['alias']); ?></td>
								<td><?php ec($salesPerson['salesmanname']); ?></td>
                                <td class="target2"><?php ec($salesPerson['target2']); ?></td>
                                <td class="achieved2"><?php ec($salesPerson['achieved2']); ?></td>
                                <td class="percentage2"><?php ec($salesPerson['percentage2']); ?></td>
								<td class="target"><?php ec($salesPerson['target']); ?></td>
                                <td class="achieved"><?php ec($salesPerson['achieved']); ?></td>

                                <td class="percentage"><?php ec($salesPerson['percentage']); ?></td>
                                <td><?php ec($salesPerson['difference']); ?></td>
								<td style="width:1%">
									<input class="target" style="width:120px" value="<?php ec($salesPerson['nextTarget']); ?>"/>
								</td>
                                <td class="salesman" style="visibility: hidden"><?php ec($salesPerson['salesman']); ?></td>
                                <td class="cgid" style="visibility: hidden"><?php ec($salesPerson['cgid']); ?></td>
							</tr>

						<?php } ?>
					</tbody>
					
				</table>
			
			</div>

	    </div>
    </section>
  
</div>

<?php
	include_once("includes/footer.php");
?>

<script>
    $(document).ready(function() {
        $('#searchresults').DataTable( {
            dom: 'Bflrtip',
            buttons: [
                'csv'
            ],
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            columns:[

                {"data":"a"},
                {"data":"b"},
                {"data":"c"},
                {"data":"d"},
                {"data":"e"},
                {"data":"f"},
                {"data":"g"},
        {"data":"h"},
        {"data":"i"},
        {"data":"j"},
        {"data":"k"},
        {"data":"l"},


            ],
            "columnDefs": [
                {
                    "render": function ( data, type, row ) {

                        //let difference=parseFloat($(this).parent().find(".achieved").val())-parseFloat($(this).parent().find(".achieved2").val());
                       // let difference=$(this).siblings().html();
                        //console.log($(this).prev().prop("class"));
                        let diff= (parseFloat(row['h']).toFixed(2) - parseFloat(row['e']).toFixed(2)).toFixed(2);
                        let html="";
                        if (diff>0) {
                             html= '<span style="color:green;">'+diff+'</span>';
                        }
                        else {
                            html= '<span style="color:red;">'+diff+'</span>';
                        }

                        return html;
                    },
                    className: 'text-center',
                    "targets": 8
                }
            ]
        } );
    } );
	$(".target").on("change",function(){
		
		let ref = $(this);
		let val = parseInt($(this).val());
		let salesman = $(this).parent().parent().find(".salesman").html();
        let cgid = $(this).parent().parent().find(".cgid").html();
		$.post("updateCustomerGroupsSalesTargets.php?ajax=true",{
			target: val,
			FormID: '<?php ec($_SESSION['FormID']); ?>',
			salesman:salesman,
            cgid:cgid,
            year: '2020'
		}, function(res, status, something){
			ref.css("border","2px solid green");
		});
		
	});

</script>

<?php
	include_once("includes/foot.php");
?>