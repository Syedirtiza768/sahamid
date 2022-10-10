<?php

include('includes/session.inc');
echo "<h3>Comments</h3>";
echo "
<table border = 1>
<tr><td>Comment</td>
<td>Username</td>
<td>Time</td>


</tr>



";
$conn = mysqli_connect('localhost','root','','sahamid');
$sql = 'SELECT * FROM `salescasecomments` 
where commentcode = "'.$_GET['commentcode'].'"';
$result = mysqli_query($conn,$sql);
while($myrow = mysqli_fetch_assoc($result))
{
	echo '
	<tr><
<td>'.$myrow['comment'].'</td>
<td>'.$myrow['username'].'</td>
<td>'.$myrow['time'].'</td>

</tr>
';
	
	
	
}

echo "</table>";






?>