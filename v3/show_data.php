<?php
//including db.php file for database connection
include 'config1.php';
session_start(); 
$user_id = $_SESSION['UserID'];
$final_result = "";
$multiCat = "";

$editPerm="";
$user_id = $_SESSION['UserID'];
$doc_category="";

//Logic to compare multi category data individually
$result = mysqli_query($conn,"SELECT category FROM doc");
      while($row = mysqli_fetch_array($result)) {
        $searchForValue = ',';
        $cat = $row['category'];
    if( strpos($cat, $searchForValue) !== false ) {
      $str= $cat;
        $words = explode($searchForValue, $str);
        $my_query = "SELECT * FROM category_perm WHERE user_id = '$user_id' ";
        $result1 = $conn->query($my_query);
        if ($result1->num_rows > 0) {
          while ($row1 = $result1->fetch_assoc()) {
            foreach ($words as $word) {
              if($word == $row1['cat_id']){
                $accurateCat[] = $str;
              }
            }
          }
        }
    }
}
$result = mysqli_query($conn,"SELECT permission FROM editPerm WHERE userid='$user_id' ");
while($row = mysqli_fetch_array($result)) {
  if($row['permission']==1){
    $editPerm= 1;
  }
}
$final_result="";
if(!empty($accurateCat)){
$final = array_unique($accurateCat);
    foreach ($final as $word) {
      $last = "category = '$word' OR ";
      $multiCat =  $multiCat . $last;
    }
    
     
      


    }
    $last="";
//query for selecting data from our database
$my_query = "SELECT * FROM category_perm WHERE user_id = '$user_id' ";
 $result1 = $conn->query($my_query);
 if ($result1->num_rows > 0) {
 while ($row1 = $result1->fetch_assoc()) {

$resultarr= "category = '".$row1['cat_id']."' OR $multiCat";
$last =  $last . $resultarr;
 }
 $final_result = substr($last, 0, -3);
 
}


$query = "SELECT * FROM doc WHERE $final_result";
//execute query
$result = $conn->query($query);
$pdf__file_path = "./upload/pdf/";
$excel__file_path = "./upload/excel/";
$word__file_path = "./upload/word/";
$ppt__file_path = "./upload/ppt/";



//checking result that should be more than 0
if (!empty($result)) {
    //html code for output
    // <tfoot>
		// <tr>
    // <th>Sr.#</th>
    // <th>Document Name</th>
    // <th>Category</th>
    // <th>PDF</th>
    // <th>Excel</th>
    // <th>Word</th>
    // <th>PPT</th>
		// </tr>
	  //   </tfoot>
    $opt = '


    <table id="myTable" class="table-fill" style="width:100%; margin:auto">
      <thead>
        <tr>
          <th class="text-left" style="width: 5px;">Sr.#</th>
          <th class="text-left" style="width: 150px;">Document Name</th>
          <th class="text-left" style="width: 100px;">Category</th>
          <th class="text-left">PDF</th>
          <th class="text-left">Excel</th>
          <th class="text-left">Word</th>
          <th class="text-left">PPT</th>';
          if($editPerm==1){
          $opt.='<th class="text-left" style="width: 100px;">Action</th>';
          }
          $opt.=' </tr>
      </thead>
      <tbody>

    ';
    $sr = 1;
    //looping our data till the last on our database
    while ($row = $result->fetch_assoc()) {
      if(!empty($row['pdf'])){
        $pdf__file_path= $pdf__file_path.$row['pdf'];
        $pdf = "<a href='". $pdf__file_path ."';><img src='./img/pdf.png'></a>";
      }
      else{
        $pdf = "";
      }

      if(!empty($row['excel'])){
        $excel__file_path= $excel__file_path.$row['excel'];
        $excel = "<a href='".$excel__file_path."';><img src='./img/excel.png'></a>";
      }
      else{
        $excel = "";
      }

      if(!empty($row['word'])){
        $word__file_path = $word__file_path.$row['word'];
        $word = "<a href='".$word__file_path."';><img src='./img/word.png'></a>";
      }
      else{
        $word = "";
      }
      
      if(!empty($row['ppt'])){
        $ppt__file_path = $ppt__file_path.$row['ppt'];
        $ppt = "<a href='".$ppt__file_path."';><img src='./img/ppt.png'></a>";
      }
      else{
        $ppt = "";
      }


        $opt .= "
        <tr>
          <td style='font-size:15px'>
            $sr
          </td>
          <td  style='font-size:15px'>
            {$row['d_name']}
          </td>
          <td style='font-size:15px'>
          {$row['category']}
          </td>
          <td style='font-size:10px'>
          <h6>Number:{$row['pdf_number']}  |   Revision: {$row['d_revision']}</h6> 
          <h6>Date:{$row['pdf_date']}</h6>
          $pdf
          <br>
          <hr style='border-top: 1px dashed black;'>
          
          <h6>Deleted Versions:</h6>";
          // to get deleted pdf-file paths
          
          if(!empty($row['del_pdf'])){
            $pre_del= $row['del_pdf'];
            if(!empty($row['pdf_del_version'])){
              $pre_recd= $row['pdf_del_version'];
              $del_version = explode (",", $pre_recd);
          if(!empty($row['pdf_del_date'])){
                $pre_recd= $row['pdf_del_date'];
                $del_date = explode (",", $pre_recd);
            $str_arr = explode (",", $pre_del); 
          for($i=0; $i<count($str_arr); $i++){
            $deleted= "./upload/pdf/".$str_arr[$i];
            $opt .= "<a style='color:red'>Number:$del_version[$i]</a>----<a style='color:red'>$del_date[$i]</a>
            <a href='$deleted';><img width='30' height='25' src='./img/pdf_red.png' class='content_img'></a></br>";
          }
          // for($i=0; $i<count($str_arr); $i++){
          //   $deleted= "./upload/pdf/".$str_arr[$i];
          //   $opt .= " <a href='$deleted';><img width='30' height='25' src='./img/pdf_red.png' class='content_img'></a>";
          // }
        }
      }
    }
      
          $opt .= "</td>

          <td style='font-size:10px'>
          <h6>Number:{$row['excel_number']}</h6>
          <h6>Date:{$row['excel_date']}</h6>
          $excel
          <br><hr style='border-top: 1px dashed black;'>
          <h6><b>Deleted Versions:</b></h6>";
          // to get deleted excel-file paths
          if(!empty($row['del_excel'])){
            $pre_del= $row['del_excel'];
            $str_arr = explode (",", $pre_del); 

            if(!empty($row['excel_del_version'])){
              $pre_recd= $row['excel_del_version'];
              $del_version = explode (",", $pre_recd);

          if(!empty($row['excel_del_date'])){
                $pre_recd= $row['excel_del_date'];
                $del_date = explode (",", $pre_recd);

          for($i=0; $i<count($str_arr); $i++){
            $deleted= "./upload/excel/".$str_arr[$i];
            $opt .= "<a style='color:red'>Number:$del_version[$i]</a>----<a style='color:red'>$del_date[$i]</a>
            <a href='$deleted';><img width='30' height='25' src='./img/excel_red.png'></a></br>";
          }
        }
      }
    }
        $opt .= " </td>

          <td style='font-size:10px'>
          <h6>Number:{$row['word_number']}</h6>
          <h6>Date:{$row['word_date']}</h6>
          $word
          <br><hr style='border-top: 1px dashed black;'>
          <h6><b>Deleted Versions:</b></h6>";
          // to get deleted word-file paths
          if(!empty($row['del_word'])){
            $pre_del= $row['del_word'];
            if(!empty($row['word_del_version'])){
              $pre_recd= $row['word_del_version'];
              $del_version = explode (",", $pre_recd);
          if(!empty($row['word_del_date'])){
                $pre_recd= $row['word_del_date'];
                $del_date = explode (",", $pre_recd);
            $str_arr = explode (",", $pre_del); 
          for($i=0; $i<count($str_arr); $i++){
            $deleted= "./upload/word/".$str_arr[$i];
            $opt .= " <a style='color:red'>Number:$del_version[$i]</a>----<a style='color:red'>$del_date[$i]</a>
            <a href='$deleted';><img width='30' height='25' src='./img/word_red.png'></a></br>";
          }
        }
      }
    } 
       
    $opt .= " </td>

          <td style='font-size:10px'>
          <h6>Number:{$row['ppt_number']}</h6>
          <h6>Date:{$row['ppt_date']}</h6>
          $ppt
          <br><hr style='border-top: 1px dashed black;'>
          <h6><b>Deleted Versions:</b></h6>";
          // to get deleted word-file paths
          if(!empty($row['del_ppt'])){
            $pre_del= $row['del_ppt'];
            if(!empty($row['ppt_del_version'])){
              $pre_recd= $row['ppt_del_version'];
              $del_version = explode (",", $pre_recd);
          if(!empty($row['ppt_del_date'])){
                $pre_recd= $row['ppt_del_date'];
                $del_date = explode (",", $pre_recd);
            $str_arr = explode (",", $pre_del); 
          for($i=0; $i<count($str_arr); $i++){
            $deleted= "./upload/ppt/".$str_arr[$i];
            $opt .= " <a style='color:red'>Number:$del_version[$i]</a>----<a style='color:red'>$del_date[$i]</a>
            <a href='$deleted';><img width='30' height='25' src='./img/ppt_red.png'></a></br>";
          }
        }
      }
    } 
    $opt.="</td>";
      
    if($editPerm==1){
       $opt .= " 
        <td>
        <a href='#editDocumentModal' class='btn btn-success edit' data-toggle='modal'>
        <i class='material-icons update-form' data-toggle='modal'
        data_id= '". $row['id']."'
        data_name='".$row['d_name']."'
        data_revision='".$row['d_revision']."'
        data_category='".$row['category']."'
        title='Edit'></i>
      </a>
        </td>";
    }
      $opt.= "</tr> 
        ";
        $sr++;
        $pdf__file_path = "./upload/pdf/";
        $excel__file_path = "./upload/excel/";
        $word__file_path = "./upload/word/";  
        $ppt__file_path = "./upload/ppt/";  
    }
    $opt .= '
    </tbody>
        </table>';
    // echo our output that we can use in our index.php using ajax response
    echo $opt;
}
else{
  echo  "<h1 style='font-size:10px'>No Result Found</h1>";
}
