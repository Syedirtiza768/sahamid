<?php
//including db.php file for database connection
$PathPrefix='../../';
$AllowAnyone = true;
include('../../includes/session.inc');
include 'config.php';

//query for selecting data from our database
$query = "SELECT * FROM doc";

//execute query
$result = $conn->query($query);
$pdf__file_path = "./upload/pdf/";
$excel__file_path = "./upload/excel/";
$word__file_path = "./upload/word/";



//checking result that should be more than 0
if ($result->num_rows > 0) {
    //html code for output
    $opt = '


    <table id="myTable" class="table-fill" width="100%">
      <thead>
        <tr>
          <th class="text-left">Sr.#</th>
          <th class="text-left">Document Name</th>
          <th class="text-left">PDF</th>
          <th class="text-left">Excel</th>
          <th class="text-left">Word</th>
        </tr>
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


      

        $opt .= "
        <tr>
          <td>
            $sr
          </td>
          <td >
            {$row['d_name']}
            <p>{$row['d_desc']}</p>
          </td>
          <td>
          <h6>Version:{$row['pdf_version']}.0</h6>
          $pdf
          <br>";
        if(userHasPermission($db, "can_edit_documents"))
        $opt .= "<button style='padding: 0px 25px;color:black;cursor: pointer;margin: 0px -4px;font-size: 10px;' class='add_files' pdf='" . $row['pdf'] . "'  doc_id='" . $row['id'] . "';>ADD NEW</button>";
        $opt .= " <form id='My_form" . $row['id'] . "' style='display: none; width: 300px; padding: 14px; background: none;' enctype='multipart/form-data'>
            <input name='filess' type='file' id='filess'  />
            <input name='id' value='" . $row['id'] . "' type='hidden' />
            <input  id='btn-Upload' type='submit' value='upload' ></input>
          </form>";
        if(userHasPermission($db, "can_edit_documents"))
            $opt .= "<button style='padding: 0px 29px;color:black;cursor: pointer;margin: 0px -4px;font-size: 10px;' class='btn btn-danger delete_pdf' id='" . $row['id'] . "'; pdf='" . $row['pdf'] . "'>DELETE</button>";
        $opt .= "<h6>Deleted Versions:</h6>";
          // to get deleted pdf-file paths
          if(!empty($row['del_pdf'])){
            $pre_recd= $row['del_pdf'];
            $str_arr = explode (",", $pre_recd); 
          for($i=0; $i<count($str_arr); $i++){
            $deleted= "./upload/pdf/".$str_arr[$i];
            $opt .= " <a href='$deleted';><img width='30' height='25' src='./img/pdf_red.png'></a>";
          }
        }
          $opt .= "</td>

          <td>
          <h6>version:{$row['excel_version']}.0</h6>
          $excel
          <br>";
        if(userHasPermission($db, "can_edit_documents"))
        $opt .= "<button style='padding: 0px 25px;color:black;cursor: pointer;margin: 0px -4px;font-size: 10px;' class='add_excel_file' excel='" . $row['excel'] . "' doc_id='" . $row['id'] . "';>ADD NEW</button>";
        $opt .= "  <form id='My_excel_form" . $row['id'] . "' style='display: none; width: 300px; padding: 14px; background: none;' enctype='multipart/form-data'>
            <input name='filess' type='file' id='excel'  />
            <input name='id' value='" . $row['id'] . "' type='hidden' />
            <input type='submit' value='upload' ></input>
          </form>";
        if(userHasPermission($db, "can_edit_documents"))
        $opt .= " <button style='padding: 0px 29px;color:black;cursor: pointer;margin: 0px -4px;font-size: 10px;' class='btn btn-danger delete_excel' id='" . $row['id'] . "'; excel='" . $row['excel'] . "'>DELETE</button>";
        $opt .= "<h6><b>Deleted Versions:</b></h6>";
          // to get deleted excel-file paths
          if(!empty($row['del_excel'])){
            $pre_recd= $row['del_excel'];
            $str_arr = explode (",", $pre_recd); 
          for($i=0; $i<count($str_arr); $i++){
            $deleted= "./upload/excel/".$str_arr[$i];
            $opt .= " <a href='$deleted';><img width='30' height='25' src='./img/excel_red.png'></a>";
          }
        }
        $opt .= " </td>

          <td>
          <h6>version:{$row['word_version']}.0</h6>
          $word
          <br>";
        if(userHasPermission($db, "can_edit_documents"))
        $opt .= "<button style='padding: 0px 25px;color:black;cursor: pointer;margin: 0px -4px;font-size: 10px;' class='add_word_file' word='" . $row['word'] . "' doc_id='" . $row['id'] . "';>ADD NEW</button>";
        $opt .= " <form id='My_word_form" . $row['id'] . "' style='display: none; width: 300px; padding: 14px; background: none;' enctype='multipart/form-data'>
            <input name='filess' type='file' id='word'  />
            <input name='id' value='" . $row['id'] . "' type='hidden' />
            <input type='submit' value='upload' ></input>
          </form>";
        if(userHasPermission($db, "can_edit_documents"))
        $opt .= " <button style='padding: 0px 29px;color:black;cursor: pointer;margin: 0px -4px;font-size: 10px;' class='btn btn-danger delete_word' id='" . $row['id'] . "'; word='" . $row['word'] . "'>DELETE</button>";
        $opt .= " <h6><b>Deleted Versions:</b></h6>";
          // to get deleted word-file paths
          if(!empty($row['del_word'])){
            $pre_recd= $row['del_word'];
            $str_arr = explode (",", $pre_recd); 
          for($i=0; $i<count($str_arr); $i++){
            $deleted= "./upload/word/".$str_arr[$i];
            $opt .= " <a href='$deleted';><img width='30' height='25' src='./img/word_red.png'></a>";
          }
        }
        $opt .= " </td>
        </tr>
        ";
        $sr++;
        $pdf__file_path = "./upload/pdf/";
        $excel__file_path = "./upload/excel/";
        $word__file_path = "./upload/word/";  
    }
    $opt .= '</tbody>
        </table>';
    // echo our output that we can use in our index.php using ajax response
    echo $opt;
}
