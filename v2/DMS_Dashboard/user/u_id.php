<?php
session_start();
include 'config.php';
$_SESSION["user_val"]=$_POST['user_id'];

                $val = '<table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>
                            
                        </th>
                        <th>Category Name</th>
                        <th>Category Code</th>
                    </tr>
                </thead>
                <tbody>';

                                $result = mysqli_query($conn,"SELECT * FROM category");
                                    while($row = mysqli_fetch_array($result)) {

                             
                                $val .= '<tr id="'.$row["id"].'">';
                                $val.= ' <td>';
                                            $result1 = mysqli_query($conn,"SELECT * FROM category_perm WHERE user_id ='".$_SESSION['user_val']."' AND cat_id='".$row["cat_name"]."' ");
                                            $cat_check = mysqli_fetch_array($result1);
                                            if($cat_check['cat_id'] ?? null){

                                $val.= '<span class="custom-checkbox">
                                                <input type="checkbox" class="user_checkbox" cat-id="'. $row["cat_name"].'" users-id="'.$_POST['user_id'].'" checked>
                                                <label for="checkbox2"></label>
                                            </span>';
  
                                            }
                                            else{
   
                                $val.= '<span class="custom-checkbox">
                                                <input type="checkbox" class="user_checkbox" cat-id="'. $row["cat_name"].'" users-id="'.$_POST['user_id'].'" >
                                                <label for="checkbox2"></label>';

                                            }
                                $val.= '  </td>
                                        <td>'. $row["cat_name"].'</td>
                                        <td>'. $row["cat_code"].'</td>
                                        <td>
                                        </td>
                                </tr>
                                
                                ';
			                
                                        }
                                    $val.=' </tbody>
        </table>';
                                    echo $val;