<style>
    table {
        border-collapse: collapse;
        border-spacing: 0;
        width: 100%;
        border: 1px solid #ddd;
    }

    th, td {
        text-align: left;
        padding: 16px;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2
    }
</style>
<?php


//include('includes/session.inc');
echo "<h3><center>Comments</center></h3>";
echo "
<table border = 1>
<tr><td>Comment</td>
<td>Username</td>
<td>Time</td>


</tr>



";
$conn = mysqli_connect('localhost','root','','sahamid');
$sql = 'SELECT * FROM `reversedAllocationHistoryCommentsVendor` 
where reversedAllocationHistoryID = "'.$_GET['revid'].'"';
$result = mysqli_query($conn,$sql);
while($myrow = mysqli_fetch_assoc($result))
{
	echo '
	<tr>
<td>'.$myrow['comment'].'</td>
<td>'.$myrow['user'].'</td>
<td>'.$myrow['time'].'</td>

</tr>
';
	
	
	
}

echo "</table>";






?>