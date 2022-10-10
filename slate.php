<?php
/**
 * Created by PhpStorm.
 * User: dell
 * Date: 1/27/2020
 * Time: 12:01 PM
 */
$db=mysqli_connect('localhost','irtiza','netetech321','sahamid');
$SQL="SELECT * FROM salescase_permissions";
$res=mysqli_query($db,$SQL);
while ($row=mysqli_fetch_assoc($res))
{
    $salesperson=$row['can_access'];
    $username=$row['user'];
    $SQL="SELECT custbranch.branchcode, salesman.salesmanname, www_users.userid
          FROM custbranch INNER JOIN salesman ON salesman.salesmancode=custbranch.salesman 
          INNER JOIN www_users ON salesman.salesmanname=www_users.realname WHERE userid='$salesperson'";
    $res2=mysqli_query($db,$SQL);
    while($row2=mysqli_fetch_assoc($res2)){
        $client=$row2['branchcode'];
        $SQL2="INSERT INTO recovery_access(user,can_access) VALUES ('$username','$client')";
        mysqli_query($db,$SQL2);
    }
}
$SQL="SELECT * FROM www_users";
$res=mysqli_query($db,$SQL);
while ($row=mysqli_fetch_assoc($res))
{
    $salesperson=$row['userid'];
    $username=$row['userid'];
   $SQL="SELECT custbranch.branchcode, salesman.salesmanname, www_users.userid
          FROM custbranch INNER JOIN salesman ON salesman.salesmancode=custbranch.salesman 
          INNER JOIN www_users ON salesman.salesmanname=www_users.realname WHERE userid='$salesperson'";
    $res2=mysqli_query($db,$SQL);
    while($row2=mysqli_fetch_assoc($res2)){
        $client=$row2['branchcode'];
        $SQL2="INSERT INTO recovery_access(user,can_access) VALUES ('$username','$client')";
        mysqli_query($db,$SQL2);
    }
}

echo "end";





?>