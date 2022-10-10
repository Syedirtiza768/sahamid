<?php

	include('../misc.php');
	
	$db = createDBConnection();

/*	$SQL = "SELECT dcs.orderno,
				debtorsmaster.name,
				custbranch.brname,
				dcs.customerref,
				dcs.orddate,
				dcs.deliverto,
				dcs.deliverydate,
				dcs.printedpackingslip,
				dcs.poplaced
			FROM dcs

			INNER JOIN debtorsmaster ON dcs.debtorno = debtorsmaster.debtorno
			INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND dcs.branchcode = custbranch.branchcode)
			INNER JOIN currencies ON debtorsmaster.currcode = currencies.currabrev
			WHERE
			dcs.inprogress=1
			AND dcs.invoicegroupid IS NULL
			GROUP BY dcs.orderno
			ORDER BY dcs.orderno";
	*/
        $SQL="SELECT dcs.orderno, dcs.customerref, dcs.orddate, dcs.deliverto, dcs.deliverydate, 
        dcs.printedpackingslip, dcs.poplaced FROM dcs INNER JOIN dcdetails on dcs.orderno=dcdetails.orderno 
        WHERE dcs.inprogress=1 AND dcs.invoicegroupid IS NULL GROUP BY dcs.orderno ORDER BY dcs.orderno";
		$result = mysqli_query($db, $SQL);
        $dcs = [];
        while($row = mysqli_fetch_assoc($result)){
        $dcs[$row['orderno']]=$row;
        }

        $SQL = "SELECT dcdetails.orderno,SUM((dcdetails.quantity*(dcdetails.unitprice * (1-dcdetails.discountpercent)))*dcoptions.quantity) AS ordervalue
               
        FROM dcdetails INNER JOIN dcoptions on (dcdetails.orderno = dcoptions.orderno AND dcdetails.orderlineno = dcoptions.lineno) 

        GROUP BY dcdetails.orderno" ;

        $res = mysqli_query($db, $SQL);
        $ordervalue = [];
        while($row = mysqli_fetch_assoc($res)){
            $ordervalue[$row['orderno']]=$row;
        }

        $SQL = "SELECT  dcs.orderno,
				debtorsmaster.name,
				custbranch.brname
                FROM dcs INNER JOIN debtorsmaster ON dcs.debtorno = debtorsmaster.debtorno
			    INNER JOIN custbranch ON (debtorsmaster.debtorno = custbranch.debtorno AND dcs.branchcode = custbranch.branchcode) 
                WHERE dcs.orderno IN (SELECT dcs.orderno FROM dcs WHERE dcs.inprogress=1 AND dcs.invoicegroupid IS NULL )
                GROUP BY dcs.orderno" ;

        $ress = mysqli_query($db, $SQL);

        $custinfo = [];
        while($row = mysqli_fetch_assoc($ress)){
            $custinfo[$row['orderno']]=$row;
        }


        $data=[];
        foreach ($dcs as $key => $value){
            $r['0'] = "<p class='dcno'>".utf8_encode($dcs[$key]['orderno'])."<p>";
            $r['1'] = "<p class='cus'>".utf8_encode($custinfo[$key]['name'])."<p>";
            $r['2'] = "<p class='brn'>".utf8_encode($custinfo[$key]['brname'])."<p>";
            $r['3'] = "<p class='pono'>".utf8_encode($dcs[$key]['customerref'])."<p>";
            $r['4'] = "<p class='ordd'>".utf8_encode($dcs[$key]['orddate'])."<p>";

            $r['5'] = "<p class='ordv'>".round(utf8_encode($ordervalue[$key]['ordervalue']))."<p>";

            $data[] = $r;
        }




	/*	while($row = mysqli_fetch_assoc($result)){
						
			$r['0'] = "<p class='dcno'>".utf8_encode($row['orderno'])."<p>";
			$r['1'] = "<p class='cus'>".utf8_encode($row['name'])."<p>";
			$r['2'] = "<p class='brn'>".utf8_encode($row['brname'])."<p>";
			$r['3'] = "<p class='pono'>".utf8_encode($row['customerref'])."<p>";
			$r['4'] = "<p class='ordd'>".utf8_encode($row['orddate'])."<p>";

			$r['5'] = "<p class='ordv'>".round(utf8_encode($row = mysqli_fetch_assoc($res)['ordervalue']))."<p>";
			
			$data[] = $r;
			
		} */

		echo json_encode($data);

?>