
<!DOCTYPE html>
<html lang="en">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Costing</title>
    <script src="links/jquery.js"></script>
    <link rel="stylesheet" href="links/bootstrap.css">
    <script src="links/bootstrap.js"></script>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.12.1/css/all.css" crossorigin="anonymous">
    <script src="links/alert.js"></script>
    <link rel="stylesheet" href="styles.css">
    <script src="javascripty.js"></script>
</head>
<body>
  
  <button  class="close" style="border:1px solid red; background-color:red; color:white" aria-label onclick="javascript:window.close()"></button>
    <div class="container mt-5">
        <div class="row d-flex justify-content-center align-items-center">
            <div class="col-md-8">
                    
                    <h1 id="register">Panel Costing</h1>
                    <div class="all-steps" id="all-steps"> 
                      <button class="step" id="one" title="Panel Size" onclick="viewTab(0)"><i>1</i></button> 
                      <button class="step" id="two" title="Front Door" onclick="viewTab(1)"><i>2</i></button>
                      <button class="step" id="three" title="Back Door" onclick="viewTab(2)"><i>3</i></button>
                      <button class="step" id="four" title="Side Door Right & Left" onclick="viewTab(3)"><i>4</i></button>
                      <button class="step" id="five" title="Vertical Structure Piece Front & Back" onclick="viewTab(4)"><i>5</i></button>
                      <button class="step" id="six" title="Horizontal Structure Piece Front & Back" onclick="viewTab(5)"><i>6</i></button>
                      <button class="step" id="seven" title="Horizontal Structure Piece Right & Left" onclick="viewTab(6)"><i>7</i></button> 
                      <button class="step" id="eight" title="Component Plate" onclick="viewTab(7)"><i>8</i></button>
                      <button class="step" id="nine" title="Mounting Plate" onclick="viewTab(8)"><i>9</i></button>
                      <button class="step" id="ten" title="Front & Back Base" onclick="viewTab(9)"><i>10</i></button>
                      <button class="step" id="eleven" title="Right & Left Base" onclick="viewTab(10)"><i>11</i></button>
                      <button class="step" id="twelve" title="Vertical Inside Piece Door's U" onclick="viewTab(11)"><i>12</i></button>
                      <button class="step" id="thirteen" title="Horizontal Inside Piece Door's U" onclick="viewTab(12)"><i>13</i></button>
                      <button class="step" id="fourteen" title="Vertical Inside Piece L Type" onclick="viewTab(13)"><i>14</i></button>
                      <button class="step" id="fifteen" title="Horizontal Inside Piece U Type" onclick="viewTab(14)"><i>15</i></button> 
                      <button class="step" id="sixteen" title="Top" onclick="viewTab(15)"><i>16</i></button>
                      <button class="step" id="seventeen" title="Bottom" onclick="viewTab(16)"><i>17</i></button>
                      <button class="step" id="eighteen" title="Protection Sheet" onclick="viewTab(17)"><i>18</i></button>
                      <button class="step" id="ninteen" title="Miscellaneous" onclick="viewTab(18)"><i>19</i></button> 
                      <button class="step" id="twenty" title="Sheet Selection" onclick="viewTab(19)"><i>20</i></button>

                      <button class="step" id="twenty_one" title="Bus Bar Sheet" onclick="viewTab(20)"><i>21</i></button> 
                      <button class="step" id="twenty_two" title="Bus Bar Sheet" onclick="viewTab(21)"><i>22</i></button>
                      <button class="step" id="twenty_three" title="Panel Lock" onclick="viewTab(22)"><i>23</i></button>
                      <button class="step" id="twenty_four" title="Hinges" onclick="viewTab(23)"><i>24</i></button>
                      <button class="step" id="twenty_five" title="ACRYLIC SHEET" onclick="viewTab(24)"><i>25</i></button> 
                      <button class="step" id="twenty_six" title="D-shaped/ Patti = GAS KIT" onclick="viewTab(25)"><i>26</i></button>
                      <button class="step" id="twenty_seven" title="I-Bolt" onclick="viewTab(26)"><i>27</i></button>

                      <button class="step" id="twenty_eight" title="Cable Duct" onclick="viewTab(27)"><i>28</i></button>
                      <button class="step" id="twenty_nine" title="Paint Cost" onclick="viewTab(28)"><i>29</i></button>
                      
                      <button class="step" id="thirty" title="Other Stationary Costs" onclick="viewTab(29)"><i>30</i></button>
                      <button class="step" id="thirty_one" title="SMG Cost" onclick="viewTab(30)"><i>31</i></button>
                      <button class="step" title="Panel Cost Total Price" onclick="viewTab(31)"><i>32</i></button>
                      
                    </div>
                    
                    <div style="overflow:auto;" >
                        <div style="float:right;">
                          <button type="button" id="prevBtn" onclick="nextPrev(-1)"><i class="fa fa-angle-double-left">Back</i></button> 
                          <button type="button" id="nextBtn" onclick="nextPrev(1)"><i class="fa fa-angle-double-right">Next</i></button>
                        </div>
                    </div>
                      <br>
                    <div class="tab">
                      <h3 style="text-align: center">Panel Size</h3>
                      <h6>Height(MM)</h6>
                      <p><input placeholder="Height..." type="number" id="ps_h" name="ps_h"></p>
                      <h6>Width(MM)</h6>
                      <p><input placeholder="Width..." type="number" id="ps_w" name="ps_w"></p>
                      <h6>Depth(MM)</h6>
                      <p><input placeholder="Depth..." type="number" id="ps_d" name="ps_d"></p>
                      <button class="f_door" type="button" id="ps-cost"><b>Save And Continue</b></button><br><br>
                      <p><input  type="hidden" class="pannel_costing_id" readonly></p>
                    </div>


                    <div class="tab">
                        <h3 style="text-align: center">Front Door 1</h3>
                        <h6>Height(MM)</h6>
                        <p><input placeholder="Height..." type="number" id="fd_h" name="fd_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="fd_w" name="fd_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="fd_q" name="fd_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="fd_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="fd_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="fd_cal" readonly></p>
                        <h5>Total Weight By Sheet of Front Door</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="fd_WBS" readonly></p><br>
                        <button id="deleteButton" class="fa fa-trash fd_del" style="font-size:48px;color:white"> Delete</button> <br>
                        <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#second_fd').show()">Add More</button>
                        <br>
                      </div>

                    
                    <div id="second_fd">
                      <h3 style="text-align: center">Front Door 2</h3>
                      <h6>Height(MM)</h6>
                      <p><input placeholder="Height..." type="number" id="s_fd_h" name="s_fd_h"></p>
                      <h6>Width(MM)</h6>
                      <p><input placeholder="Width..." type="number" id="s_fd_w" name="s_fd_w"></p>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="s_fd_q" name="s_fd_q"></p>
                      <h6>Sheet Selection</h6>
                      <select class="select" id="s_fd_sheet">
                          <option value="">Chose one</option>
                          <option value="12">12 Gage</option>
                          <option value="14">14 Gage</option>
                          <option value="16">16 Gage</option>
                          <option value="20">20 Gage</option>
                        </select> <br><br>
                      <button class="f_door" id="s_fd_cost"><b>Calculate</b></button><br><br>
                      <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                      <p><input placeholder="Square Feet..." type="number" class="s_fd_cal" readonly></p>
                      <h5>Total Weight By Sheet of Front Door</h5>
                      <p><input placeholder="Weight By Sheet..." type="number" class="s_fd_WBS" readonly></p><br>
                      <button id="deleteButton" class="fa fa-trash s_fd_del" style="font-size:48px;color:white"> Delete</button> <br>
                      <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#third_fd').show()">Add More</button>
                      <br>
                    </div>
                    <div id="third_fd">
                      <h3 style="text-align: center">Front Door 3</h3>
                      <h6>Height(MM)</h6>
                      <p><input placeholder="Height..." type="number" id="t_fd_h" name="t_fd_h"></p>
                      <h6>Width(MM)</h6>
                      <p><input placeholder="Width..." type="number" id="t_fd_w" name="t_fd_w"></p>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="t_fd_q" name="t_fd_q"></p>
                      <h6>Sheet Selection</h6>
                      <select class="select" id="t_fd_sheet">
                          <option value="">Chose one</option>
                          <option value="12">12 Gage</option>
                          <option value="14">14 Gage</option>
                          <option value="16">16 Gage</option>
                          <option value="20">20 Gage</option>
                        </select> <br><br>
                      <button class="f_door" id="t_fd_cost"><b>Calculate</b></button><br><br>
                      <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                      <p><input placeholder="Square Feet..." type="number" class="t_fd_cal" readonly></p>
                      <h5>Total Weight By Sheet of Front Door</h5>
                      <p><input placeholder="Weight By Sheet..." type="number" class="t_fd_WBS" readonly></p>
                      <button id="deleteButton" class="fa fa-trash t_fd_del" style="font-size:48px;color:white"> Delete</button> <br>
                      <button id="addButton" class="fa fa-angle-down " style="font-size:48px;color:red" onclick="$('#fourth_fd').show()">Add More</button>
                    </div>
                    <div id="fourth_fd">
                      <h3 style="text-align: center">Front Door 4</h3>
                      <h6>Height(MM)</h6>
                      <p><input placeholder="Height..." type="number" id="f_fd_h" name="t_fd_h"></p>
                      <h6>Width(MM)</h6>
                      <p><input placeholder="Width..." type="number" id="f_fd_w" name="t_fd_w"></p>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="f_fd_q" name="t_fd_q"></p>
                      <h6>Sheet Selection</h6>
                      <select class="select" id="f_fd_sheet">
                          <option value="">Chose one</option>
                          <option value="12">12 Gage</option>
                          <option value="14">14 Gage</option>
                          <option value="16">16 Gage</option>
                          <option value="20">20 Gage</option>
                        </select> <br><br>
                      <button class="f_door" id="f_fd_cost"><b>Calculate</b></button><br><br>
                      <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                      <p><input placeholder="Square Feet..." type="number" class="f_fd_cal" readonly></p>
                      <h5>Total Weight By Sheet of Front Door</h5>
                      <p><input placeholder="Weight By Sheet..." type="number" class="f_fd_WBS" readonly></p>
                      <button id="deleteButton" class="fa fa-trash f_fd_del" style="font-size:48px;color:white"> Delete</button> <br>
                      <button id="addButton" class="fa fa-angle-down " style="font-size:48px;color:red" onclick="$('#fifth_fd').show()">Add More</button>
                    </div>
                    <div id="fifth_fd">
                      <h3 style="text-align: center">Front Door 5</h3>
                      <h6>Height(MM)</h6>
                      <p><input placeholder="Height..." type="number" id="fth_fd_h" name="t_fd_h"></p>
                      <h6>Width(MM)</h6>
                      <p><input placeholder="Width..." type="number" id="fth_fd_w" name="t_fd_w"></p>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="fth_fd_q" name="t_fd_q"></p>
                      <h6>Sheet Selection</h6>
                      <select class="select" id="fth_fd_sheet">
                          <option value="">Chose one</option>
                          <option value="12">12 Gage</option>
                          <option value="14">14 Gage</option>
                          <option value="16">16 Gage</option>
                          <option value="20">20 Gage</option>
                        </select> <br><br>
                      <button class="f_door" id="fth_fd_cost"><b>Calculate</b></button><br><br>
                      <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                      <p><input placeholder="Square Feet..." type="number" class="fth_fd_cal" readonly></p>
                      <h5>Total Weight By Sheet of Front Door</h5>
                      <p><input placeholder="Weight By Sheet..." type="number" class="fth_fd_WBS" readonly></p>
                      <button id="deleteButton" class="fa fa-trash fth_fd_del" style="font-size:48px;color:white"> Delete</button> <br>
                      <button id="addButton" class="fa fa-angle-down " style="font-size:48px;color:red" onclick="$('#sixth_fd').show()">Add More</button>
                    </div>
                    <div id="sixth_fd">
                      <h3 style="text-align: center">Front Door 6</h3>
                      <h6>Height(MM)</h6>
                      <p><input placeholder="Height..." type="number" id="six_fd_h" name="t_fd_h"></p>
                      <h6>Width(MM)</h6>
                      <p><input placeholder="Width..." type="number" id="six_fd_w" name="t_fd_w"></p>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="six_fd_q" name="t_fd_q"></p>
                      <h6>Sheet Selection</h6>
                      <select class="select" id="six_fd_sheet">
                          <option value="">Chose one</option>
                          <option value="12">12 Gage</option>
                          <option value="14">14 Gage</option>
                          <option value="16">16 Gage</option>
                          <option value="20">20 Gage</option>
                        </select> <br><br>
                      <button class="f_door" id="six_fd_cost"><b>Calculate</b></button><br><br>
                      <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                      <p><input placeholder="Square Feet..." type="number" class="six_fd_cal" readonly></p>
                      <h5>Total Weight By Sheet of Front Door</h5>
                      <p><input placeholder="Weight By Sheet..." type="number" class="six_fd_WBS" readonly></p>
                      <button id="deleteButton" class="fa fa-trash six_fd_del" style="font-size:48px;color:white"> Delete</button> <br>
                      <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#seventh_fd').show()">Add More</button>
                    </div>
                    <div id="seventh_fd">
                      <h3 style="text-align: center">Front Door 7</h3>
                      <h6>Height(MM)</h6>
                      <p><input placeholder="Height..." type="number" id="sth_fd_h" name="t_fd_h"></p>
                      <h6>Width(MM)</h6>
                      <p><input placeholder="Width..." type="number" id="sth_fd_w" name="t_fd_w"></p>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="sth_fd_q" name="t_fd_q"></p>
                      <h6>Sheet Selection</h6>
                      <select class="select" id="sth_fd_sheet">
                          <option value="">Chose one</option>
                          <option value="12">12 Gage</option>
                          <option value="14">14 Gage</option>
                          <option value="16">16 Gage</option>
                          <option value="20">20 Gage</option>
                        </select> <br><br>
                      <button class="f_door" id="sth_fd_cost"><b>Calculate</b></button><br><br>
                      <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                      <p><input placeholder="Square Feet..." type="number" class="sth_fd_cal" readonly></p>
                      <h5>Total Weight By Sheet of Front Door</h5>
                      <p><input placeholder="Weight By Sheet..." type="number" class="sth_fd_WBS" readonly></p>
                      <button id="deleteButton" class="fa fa-trash sth_fd_del" style="font-size:48px;color:white"> Delete</button> <br>
                      <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#eight_fd').show()">Add More</button>
                    </div>
                    <div id="eight_fd">
                      <h3 style="text-align: center">Front Door 8</h3>
                      <h6>Height(MM)</h6>
                      <p><input placeholder="Height..." type="number" id="e_fd_h" name="e_fd_h"></p>
                      <h6>Width(MM)</h6>
                      <p><input placeholder="Width..." type="number" id="e_fd_w" name="e_fd_w"></p>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="e_fd_q" name="e_fd_q"></p>
                      <h6>Sheet Selection</h6>
                      <select class="select" id="e_fd_sheet">
                          <option value="">Chose one</option>
                          <option value="12">12 Gage</option>
                          <option value="14">14 Gage</option>
                          <option value="16">16 Gage</option>
                          <option value="20">20 Gage</option>
                        </select> <br><br>
                      <button class="f_door" id="e_fd_cost"><b>Calculate</b></button><br><br>
                      <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                      <p><input placeholder="Square Feet..." type="number" class="e_fd_cal" readonly></p>
                      <h5>Total Weight By Sheet of Front Door</h5>
                      <p><input placeholder="Weight By Sheet..." type="number" class="e_fd_WBS" readonly></p>
                      <button id="deleteButton" class="fa fa-trash e_fd_del" style="font-size:48px;color:white"> Delete</button> <br>
                      <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#ninth_fd').show()">Add More</button>
                    </div>
                    <div id="ninth_fd">
                      <h3 style="text-align: center">Front Door 9</h3>
                      <h6>Height(MM)</h6>
                      <p><input placeholder="Height..." type="number" id="n_fd_h" name="n_fd_h"></p>
                      <h6>Width(MM)</h6>
                      <p><input placeholder="Width..." type="number" id="n_fd_w" name="n_fd_w"></p>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="n_fd_q" name="n_fd_q"></p>
                      <h6>Sheet Selection</h6>
                      <select class="select" id="n_fd_sheet">
                          <option value="">Chose one</option>
                          <option value="12">12 Gage</option>
                          <option value="14">14 Gage</option>
                          <option value="16">16 Gage</option>
                          <option value="20">20 Gage</option>
                        </select> <br><br>
                      <button class="f_door" id="n_fd_cost"><b>Calculate</b></button><br><br>
                      <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                      <p><input placeholder="Square Feet..." type="number" class="n_fd_cal" readonly></p>
                      <h5>Total Weight By Sheet of Front Door</h5>
                      <p><input placeholder="Weight By Sheet..." type="number" class="n_fd_WBS" readonly></p>
                      <button id="deleteButton" class="fa fa-trash n_fd_del" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                        <h3 style="text-align: center">Back Door 1</h3>
                        <h6>Height(MM)</h6>
                        <p><input placeholder="Height..." type="number" id="bd_h" name="bd_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="bd_w" name="bd_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="bd_q" name="bd_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="bd_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="bd_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="bd_cal" readonly></p>
                        <h5>Total Weight By Sheet of Back Door</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="bd_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash bd_del" style="font-size:48px;color:white"> Delete</button> <br>
                        <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#second_bd').show()">Add More</button>
                    </div>

                    <div id="second_bd">
                      <h3 style="text-align: center">Back Door 2</h3>
                      <h6>Height(MM)</h6>
                      <p><input placeholder="Height..." type="number" id="s_bd_h" name="s_bd_h"></p>
                      <h6>Width(MM)</h6>
                      <p><input placeholder="Width..." type="number" id="s_bd_w" name="s_bd_w"></p>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="s_bd_q" name="s_bd_q"></p>
                      <h6>Sheet Selection</h6>
                      <select class="select" id="s_bd_sheet">
                          <option value="">Chose one</option>
                          <option value="12">12 Gage</option>
                          <option value="14">14 Gage</option>
                          <option value="16">16 Gage</option>
                          <option value="20">20 Gage</option>
                        </select> <br><br>
                      <button class="f_door" id="s_bd_cost"><b>Calculate</b></button><br><br>
                      <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                      <p><input placeholder="Square Feet..." type="number" class="s_bd_cal" readonly></p>
                      <h5>Total Weight By Sheet of Back Door</h5>
                      <p><input placeholder="Weight By Sheet..." type="number" class="s_bd_WBS" readonly></p>
                      <button id="deleteButton" class="fa fa-trash s_bd_del" style="font-size:48px;color:white"> Delete</button> <br>
                      <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#third_bd').show()">Add More</button>
                  </div>
                  <div id="third_bd">
                    <h3 style="text-align: center">Back Door 3</h3>
                    <h6>Height(MM)</h6>
                    <p><input placeholder="Height..." type="number" id="t_bd_h" name="t_bd_h"></p>
                    <h6>Width(MM)</h6>
                    <p><input placeholder="Width..." type="number" id="t_bd_w" name="t_bd_w"></p>
                    <h6>Quantity</h6>
                    <p><input placeholder="Quantity..." type="number" id="t_bd_q" name="t_bd_q"></p>
                    <h6>Sheet Selection</h6>
                    <select class="select" id="t_bd_sheet">
                        <option value="">Chose one</option>
                        <option value="12">12 Gage</option>
                        <option value="14">14 Gage</option>
                        <option value="16">16 Gage</option>
                        <option value="20">20 Gage</option>
                      </select> <br><br>
                    <button class="f_door" id="t_bd_cost"><b>Calculate</b></button><br><br>
                    <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                    <p><input placeholder="Square Feet..." type="number" class="t_bd_cal" readonly></p>
                    <h5>Total Weight By Sheet of Back Door</h5>
                    <p><input placeholder="Weight By Sheet..." type="number" class="t_bd_WBS" readonly></p><br>
                    <button id="deleteButton" class="fa fa-trash t_bd_del" style="font-size:48px;color:white"> Delete</button> <br>
                    <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#fourth_bd').show()">Add More</button>
                </div>
                <div id="fourth_bd">
                  <h3 style="text-align: center">Back Door 4</h3>
                  <h6>Height(MM)</h6>
                  <p><input placeholder="Height..." type="number" id="f_bd_h" name="f_bd_h"></p>
                  <h6>Width(MM)</h6>
                  <p><input placeholder="Width..." type="number" id="f_bd_w" name="f_bd_w"></p>
                  <h6>Quantity</h6>
                  <p><input placeholder="Quantity..." type="number" id="f_bd_q" name="f_bd_q"></p>
                  <h6>Sheet Selection</h6>
                  <select class="select" id="f_bd_sheet">
                      <option value="">Chose one</option>
                      <option value="12">12 Gage</option>
                      <option value="14">14 Gage</option>
                      <option value="16">16 Gage</option>
                      <option value="20">20 Gage</option>
                    </select> <br><br>
                  <button class="f_door" id="f_bd_cost"><b>Calculate</b></button><br><br>
                  <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                  <p><input placeholder="Square Feet..." type="number" class="f_bd_cal" readonly></p>
                  <h5>Total Weight By Sheet of Back Door</h5>
                  <p><input placeholder="Weight By Sheet..." type="number" class="f_bd_WBS" readonly></p><br>
                  <button id="deleteButton" class="fa fa-trash f_bd_del" style="font-size:48px;color:white"> Delete</button> <br>
                  <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#fifth_bd').show()">Add More</button>
              </div>
              <div id="fifth_bd">
                <h3 style="text-align: center">Back Door 5</h3>
                <h6>Height(MM)</h6>
                <p><input placeholder="Height..." type="number" id="fth_bd_h" name="fth_bd_h"></p>
                <h6>Width(MM)</h6>
                <p><input placeholder="Width..." type="number" id="fth_bd_w" name="fth_bd_w"></p>
                <h6>Quantity</h6>
                <p><input placeholder="Quantity..." type="number" id="fth_bd_q" name="fth_bd_q"></p>
                <h6>Sheet Selection</h6>
                <select class="select" id="fth_bd_sheet">
                    <option value="">Chose one</option>
                    <option value="12">12 Gage</option>
                    <option value="14">14 Gage</option>
                    <option value="16">16 Gage</option>
                    <option value="20">20 Gage</option>
                  </select> <br><br>
                <button class="f_door" id="fth_bd_cost"><b>Calculate</b></button><br><br>
                <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                <p><input placeholder="Square Feet..." type="number" class="fth_bd_cal" readonly></p>
                <h5>Total Weight By Sheet of Back Door</h5>
                <p><input placeholder="Weight By Sheet..." type="number" class="fth_bd_WBS" readonly></p><br>
                <button id="deleteButton" class="fa fa-trash fth_bd_del" style="font-size:48px;color:white"> Delete</button> <br>
                <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#sixth_bd').show()">Add More</button>
            </div>
            <div id="sixth_bd">
              <h3 style="text-align: center">Back Door 6</h3>
              <h6>Height(MM)</h6>
              <p><input placeholder="Height..." type="number" id="six_bd_h" name="six_bd_h"></p>
              <h6>Width(MM)</h6>
              <p><input placeholder="Width..." type="number" id="six_bd_w" name="six_bd_w"></p>
              <h6>Quantity</h6>
              <p><input placeholder="Quantity..." type="number" id="six_bd_q" name="six_bd_q"></p>
              <h6>Sheet Selection</h6>
              <select class="select" id="six_bd_sheet">
                  <option value="">Chose one</option>
                  <option value="12">12 Gage</option>
                  <option value="14">14 Gage</option>
                  <option value="16">16 Gage</option>
                  <option value="20">20 Gage</option>
                </select> <br><br>
              <button class="f_door" id="six_bd_cost"><b>Calculate</b></button><br><br>
              <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
              <p><input placeholder="Square Feet..." type="number" class="six_bd_cal" readonly></p>
              <h5>Total Weight By Sheet of Back Door</h5>
              <p><input placeholder="Weight By Sheet..." type="number" class="six_bd_WBS" readonly></p><br>
              <button id="deleteButton" class="fa fa-trash six_bd_del" style="font-size:48px;color:white"> Delete</button> <br>
              <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#seventh_bd').show()">Add More</button>
          </div>
          <div id="seventh_bd">
            <h3 style="text-align: center">Back Door 7</h3>
            <h6>Height(MM)</h6>
            <p><input placeholder="Height..." type="number" id="sth_bd_h" name="sth_bd_h"></p>
            <h6>Width(MM)</h6>
            <p><input placeholder="Width..." type="number" id="sth_bd_w" name="sth_bd_w"></p>
            <h6>Quantity</h6>
            <p><input placeholder="Quantity..." type="number" id="sth_bd_q" name="sth_bd_q"></p>
            <h6>Sheet Selection</h6>
            <select class="select" id="sth_bd_sheet">
                <option value="">Chose one</option>
                <option value="12">12 Gage</option>
                <option value="14">14 Gage</option>
                <option value="16">16 Gage</option>
                <option value="20">20 Gage</option>
              </select> <br><br>
            <button class="f_door" id="sth_bd_cost"><b>Calculate</b></button><br><br>
            <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
            <p><input placeholder="Square Feet..." type="number" class="sth_bd_cal" readonly></p>
            <h5>Total Weight By Sheet of Back Door</h5>
            <p><input placeholder="Weight By Sheet..." type="number" class="sth_bd_WBS" readonly></p><br>
            <button id="deleteButton" class="fa fa-trash sth_bd_del" style="font-size:48px;color:white"> Delete</button> <br>
            <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#eightth_bd').show()">Add More</button>
        </div>
        <div id="eightth_bd">
          <h3 style="text-align: center">Back Door 8</h3>
          <h6>Height(MM)</h6>
          <p><input placeholder="Height..." type="number" id="e_bd_h" name="e_bd_h"></p>
          <h6>Width(MM)</h6>
          <p><input placeholder="Width..." type="number" id="e_bd_w" name="e_bd_w"></p>
          <h6>Quantity</h6>
          <p><input placeholder="Quantity..." type="number" id="e_bd_q" name="e_bd_q"></p>
          <h6>Sheet Selection</h6>
          <select class="select" id="e_bd_sheet">
              <option value="">Chose one</option>
              <option value="12">12 Gage</option>
              <option value="14">14 Gage</option>
              <option value="16">16 Gage</option>
              <option value="20">20 Gage</option>
            </select> <br><br>
          <button class="f_door" id="e_bd_cost"><b>Calculate</b></button><br><br>
          <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
          <p><input placeholder="Square Feet..." type="number" class="e_bd_cal" readonly></p>
          <h5>Total Weight By Sheet of Back Door</h5>
          <p><input placeholder="Weight By Sheet..." type="number" class="e_bd_WBS" readonly></p><br>
          <button id="deleteButton" class="fa fa-trash e_bd_del" style="font-size:48px;color:white"> Delete</button> <br>
          <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#ninth_bd').show()">Add More</button>
      </div>
      <div id="ninth_bd">
        <h3 style="text-align: center">Back Door 9</h3>
        <h6>Height(MM)</h6>
        <p><input placeholder="Height..." type="number" id="n_bd_h" name="n_bd_h"></p>
        <h6>Width(MM)</h6>
        <p><input placeholder="Width..." type="number" id="n_bd_w" name="n_bd_w"></p>
        <h6>Quantity</h6>
        <p><input placeholder="Quantity..." type="number" id="n_bd_q" name="n_bd_q"></p>
        <h6>Sheet Selection</h6>
        <select class="select" id="n_bd_sheet">
            <option value="">Chose one</option>
            <option value="12">12 Gage</option>
            <option value="14">14 Gage</option>
            <option value="16">16 Gage</option>
            <option value="20">20 Gage</option>
          </select> <br><br>
        <button class="f_door" id="n_bd_cost"><b>Calculate</b></button><br><br>
        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
        <p><input placeholder="Square Feet..." type="number" class="n_bd_cal" readonly></p>
        <h5>Total Weight By Sheet of Back Door</h5>
        <p><input placeholder="Weight By Sheet..." type="number" class="n_bd_WBS" readonly></p><br>
        <button id="deleteButton" class="fa fa-trash n_bd_del" style="font-size:48px;color:white"> Delete</button> <br>
    </div>

                    <div class="tab">
                        <h3 style="text-align: center">Side Door Right & Left</h3>
                        <h6>Height(MM)</h6>
                        <p><input placeholder="Height..." type="number" id="sd_h" name="bd_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="sd_w" name="bd_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="sd_q" name="bd_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="sd_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="sd_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="sd_cal" readonly></p>
                        <h5>Total Weight By Sheet of Side Door</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="sd_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash sd_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>


                    <div class="tab">
                        <h3 style="text-align: center">Vertical Structure Piece Front & Back</h3>
                        <h6>Length(MM)</h6>
                        <p><input placeholder="Length..." type="number" id="vs_h" name="vs_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="vs_w" name="vs_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="vs_q" name="vs_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="vs_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="vs_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="vs_cal" readonly></p>
                        <h5>Total Weight By Sheet of Vertical Structure Piece</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="vs_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash vs_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>
    
                    <div class="tab">
                        <h3 style="text-align: center">Horizontal Structure Piece Front & Back</h3>
                        <h6>Length(MM)</h6>
                        <p><input placeholder="Length..." type="number" id="hs_h" name="hs_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="hs_w" name="hs_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="hs_q" name="hs_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="hs_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="hs_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="hs_cal" readonly></p>
                        <h5>Total Weight By Sheet of Horizontal Structure Piece</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="hs_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash hs_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>
    
    
                    <div class="tab">
                        <h3 style="text-align: center">Horizontal Structure Piece Right & Left</h3>
                        <h6>Height(MM)</h6>
                        <p><input placeholder="Height..." type="number" id="hsp_h" name="hsp_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="hsp_w" name="hsp_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="hsp_q" name="hsp_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="hsp_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="hsp_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="hsp_cal" readonly></p>
                        <h5>Total Weight By Sheet of Horizontal Structure Piece Right & Left</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="hsp_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash hsp_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>
                    
                    <div class="tab">
                        <h3 style="text-align: center">Component Plate 1</h3>
                        <h6>Height(MM)</h6>
                        <p><input placeholder="Height..." type="number" id="cp_h" name="cp_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="cp_w" name="cp_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="cp_q" name="cp_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="cp_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="cp_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="cp_cal" readonly></p>
                        <h5>Total Weight By Sheet of Mounting Plate</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="cp_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash cp_del" style="font-size:48px;color:white"> Delete</button> <br>
                        <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#second_cp').show()">Add More</button>
                        <br>
                      </div>
                    <div id="second_cp">
                      <h3 style="text-align: center">Component Plate 2</h3>
                      <h6>Height(MM)</h6>
                      <p><input placeholder="Height..." type="number" id="s_cp_h" name="s_cp_h"></p>
                      <h6>Width(MM)</h6>
                      <p><input placeholder="Width..." type="number" id="s_cp_w" name="s_cp_w"></p>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="s_cp_q" name="s_cp_q"></p>
                      <h6>Sheet Selection</h6>
                      <select class="select" id="s_cp_sheet">
                          <option value="">Chose one</option>
                          <option value="12">12 Gage</option>
                          <option value="14">14 Gage</option>
                          <option value="16">16 Gage</option>
                          <option value="20">20 Gage</option>
                        </select> <br><br>
                      <button class="f_door" id="s_cp_cost"><b>Calculate</b></button><br><br>
                      <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                      <p><input placeholder="Square Feet..." type="number" class="s_cp_cal" readonly></p>
                      <h5>Total Weight By Sheet of Mounting Plate</h5>
                      <p><input placeholder="Weight By Sheet..." type="number" class="s_cp_WBS" readonly></p>
                    <button id="deleteButton" class="fa fa-trash s_cp_del" style="font-size:48px;color:white"> Delete</button> <br>
                    <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#third_cp').show()">Add More</button>
                  </div>
                  <div id="third_cp">
                    <h3 style="text-align: center">Component Plate 3</h3>
                    <h6>Height(MM)</h6>
                    <p><input placeholder="Height..." type="number" id="t_cp_h" name="t_cp_h"></p>
                    <h6>Width(MM)</h6>
                    <p><input placeholder="Width..." type="number" id="t_cp_w" name="t_cp_w"></p>
                    <h6>Quantity</h6>
                    <p><input placeholder="Quantity..." type="number" id="t_cp_q" name="t_cp_q"></p>
                    <h6>Sheet Selection</h6>
                    <select class="select" id="t_cp_sheet">
                        <option value="">Chose one</option>
                        <option value="12">12 Gage</option>
                        <option value="14">14 Gage</option>
                        <option value="16">16 Gage</option>
                        <option value="20">20 Gage</option>
                      </select> <br><br>
                    <button class="f_door" id="t_cp_cost"><b>Calculate</b></button><br><br>
                    <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                    <p><input placeholder="Square Feet..." type="number" class="t_cp_cal" readonly></p>
                    <h5>Total Weight By Sheet of Mounting Plate</h5>
                    <p><input placeholder="Weight By Sheet..." type="number" class="t_cp_WBS" readonly></p>
                  <button id="deleteButton" class="fa fa-trash t_cp_del" style="font-size:48px;color:white"> Delete</button> <br>
                  <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#fourth_cp').show()">Add More</button>
                </div>
                <div id="fourth_cp">
                  <h3 style="text-align: center">Component Plate 4</h3>
                  <h6>Height(MM)</h6>
                  <p><input placeholder="Height..." type="number" id="f_cp_h" name="f_cp_h"></p>
                  <h6>Width(MM)</h6>
                  <p><input placeholder="Width..." type="number" id="f_cp_w" name="f_cp_w"></p>
                  <h6>Quantity</h6>
                  <p><input placeholder="Quantity..." type="number" id="f_cp_q" name="f_cp_q"></p>
                  <h6>Sheet Selection</h6>
                  <select class="select" id="f_cp_sheet">
                      <option value="">Chose one</option>
                      <option value="12">12 Gage</option>
                      <option value="14">14 Gage</option>
                      <option value="16">16 Gage</option>
                      <option value="20">20 Gage</option>
                    </select> <br><br>
                  <button class="f_door" id="f_cp_cost"><b>Calculate</b></button><br><br>
                  <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                  <p><input placeholder="Square Feet..." type="number" class="f_cp_cal" readonly></p>
                  <h5>Total Weight By Sheet of Mounting Plate</h5>
                  <p><input placeholder="Weight By Sheet..." type="number" class="f_cp_WBS" readonly></p>
                <button id="deleteButton" class="fa fa-trash f_cp_del" style="font-size:48px;color:white"> Delete</button> <br>
                <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#fifth_cp').show()">Add More</button>
              </div>
              <div id="fifth_cp">
                <h3 style="text-align: center">Component Plate 5</h3>
                <h6>Height(MM)</h6>
                <p><input placeholder="Height..." type="number" id="fth_cp_h" name="fth_cp_h"></p>
                <h6>Width(MM)</h6>
                <p><input placeholder="Width..." type="number" id="fth_cp_w" name="fth_cp_w"></p>
                <h6>Quantity</h6>
                <p><input placeholder="Quantity..." type="number" id="fth_cp_q" name="fth_cp_q"></p>
                <h6>Sheet Selection</h6>
                <select class="select" id="fth_cp_sheet">
                    <option value="">Chose one</option>
                    <option value="12">12 Gage</option>
                    <option value="14">14 Gage</option>
                    <option value="16">16 Gage</option>
                    <option value="20">20 Gage</option>
                  </select> <br><br>
                <button class="f_door" id="fth_cp_cost"><b>Calculate</b></button><br><br>
                <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                <p><input placeholder="Square Feet..." type="number" class="fth_cp_cal" readonly></p>
                <h5>Total Weight By Sheet of Mounting Plate</h5>
                <p><input placeholder="Weight By Sheet..." type="number" class="fth_cp_WBS" readonly></p>
              <button id="deleteButton" class="fa fa-trash fth_cp_del" style="font-size:48px;color:white"> Delete</button> <br>
              <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#sixth_cp').show()">Add More</button>
            </div>
            <div id="sixth_cp">
              <h3 style="text-align: center">Component Plate 6</h3>
              <h6>Height(MM)</h6>
              <p><input placeholder="Height..." type="number" id="six_cp_h" name="six_cp_h"></p>
              <h6>Width(MM)</h6>
              <p><input placeholder="Width..." type="number" id="six_cp_w" name="six_cp_w"></p>
              <h6>Quantity</h6>
              <p><input placeholder="Quantity..." type="number" id="six_cp_q" name="six_cp_q"></p>
              <h6>Sheet Selection</h6>
              <select class="select" id="six_cp_sheet">
                  <option value="">Chose one</option>
                  <option value="12">12 Gage</option>
                  <option value="14">14 Gage</option>
                  <option value="16">16 Gage</option>
                  <option value="20">20 Gage</option>
                </select> <br><br>
              <button class="f_door" id="six_cp_cost"><b>Calculate</b></button><br><br>
              <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
              <p><input placeholder="Square Feet..." type="number" class="six_cp_cal" readonly></p>
              <h5>Total Weight By Sheet of Mounting Plate</h5>
              <p><input placeholder="Weight By Sheet..." type="number" class="six_cp_WBS" readonly></p>
            <button id="deleteButton" class="fa fa-trash six_cp_del" style="font-size:48px;color:white"> Delete</button> <br>
            <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#seventh_cp').show()">Add More</button>
          </div>
          <div id="seventh_cp">
            <h3 style="text-align: center">Component Plate 7</h3>
            <h6>Height(MM)</h6>
            <p><input placeholder="Height..." type="number" id="sth_cp_h" name="sth_cp_h"></p>
            <h6>Width(MM)</h6>
            <p><input placeholder="Width..." type="number" id="sth_cp_w" name="sth_cp_w"></p>
            <h6>Quantity</h6>
            <p><input placeholder="Quantity..." type="number" id="sth_cp_q" name="sth_cp_q"></p>
            <h6>Sheet Selection</h6>
            <select class="select" id="sth_cp_sheet">
                <option value="">Chose one</option>
                <option value="12">12 Gage</option>
                <option value="14">14 Gage</option>
                <option value="16">16 Gage</option>
                <option value="20">20 Gage</option>
              </select> <br><br>
            <button class="f_door" id="sth_cp_cost"><b>Calculate</b></button><br><br>
            <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
            <p><input placeholder="Square Feet..." type="number" class="sth_cp_cal" readonly></p>
            <h5>Total Weight By Sheet of Mounting Plate</h5>
            <p><input placeholder="Weight By Sheet..." type="number" class="sth_cp_WBS" readonly></p>
          <button id="deleteButton" class="fa fa-trash sth_cp_del" style="font-size:48px;color:white"> Delete</button> <br>
          <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#eighth_cp').show()">Add More</button>
        </div>
        <div id="eighth_cp">
          <h3 style="text-align: center">Component Plate 8</h3>
          <h6>Height(MM)</h6>
          <p><input placeholder="Height..." type="number" id="e_cp_h" name="e_cp_h"></p>
          <h6>Width(MM)</h6>
          <p><input placeholder="Width..." type="number" id="e_cp_w" name="e_cp_w"></p>
          <h6>Quantity</h6>
          <p><input placeholder="Quantity..." type="number" id="e_cp_q" name="e_cp_q"></p>
          <h6>Sheet Selection</h6>
          <select class="select" id="e_cp_sheet">
              <option value="">Chose one</option>
              <option value="12">12 Gage</option>
              <option value="14">14 Gage</option>
              <option value="16">16 Gage</option>
              <option value="20">20 Gage</option>
            </select> <br><br>
          <button class="f_door" id="e_cp_cost"><b>Calculate</b></button><br><br>
          <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
          <p><input placeholder="Square Feet..." type="number" class="e_cp_cal" readonly></p>
          <h5>Total Weight By Sheet of Mounting Plate</h5>
          <p><input placeholder="Weight By Sheet..." type="number" class="e_cp_WBS" readonly></p>
        <button id="deleteButton" class="fa fa-trash e_cp_del" style="font-size:48px;color:white"> Delete</button> <br>
        <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#ninth_cp').show()">Add More</button>
      </div>
      <div id="ninth_cp">
        <h3 style="text-align: center">Component Plate 9</h3>
        <h6>Height(MM)</h6>
        <p><input placeholder="Height..." type="number" id="n_cp_h" name="n_cp_h"></p>
        <h6>Width(MM)</h6>
        <p><input placeholder="Width..." type="number" id="n_cp_w" name="n_cp_w"></p>
        <h6>Quantity</h6>
        <p><input placeholder="Quantity..." type="number" id="n_cp_q" name="n_cp_q"></p>
        <h6>Sheet Selection</h6>
        <select class="select" id="n_cp_sheet">
            <option value="">Chose one</option>
            <option value="12">12 Gage</option>
            <option value="14">14 Gage</option>
            <option value="16">16 Gage</option>
            <option value="20">20 Gage</option>
          </select> <br><br>
        <button class="f_door" id="n_cp_cost"><b>Calculate</b></button><br><br>
        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
        <p><input placeholder="Square Feet..." type="number" class="n_cp_cal" readonly></p>
        <h5>Total Weight By Sheet of Mounting Plate</h5>
        <p><input placeholder="Weight By Sheet..." type="number" class="n_cp_WBS" readonly></p>
      <button id="deleteButton" class="fa fa-trash n_cp_del" style="font-size:48px;color:white"> Delete</button> <br>
    </div>
                  
                    <div class="tab">
                        <h3 style="text-align: center">Mounting Plate 1</h3>
                        <h6>Length(MM)</h6>
                        <p><input placeholder="Length..." type="number" id="p_h" name="p_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="p_w" name="p_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="p_q" name="p_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="p_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="p_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="p_cal" readonly></p>
                        <h5>Total Weight By Sheet of Component Plat</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="p_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash p_del" style="font-size:48px;color:white"> Delete</button> <br>
                        <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#second_p').show()">Add More</button>
                        <br>
                      </div>
                   
                    <div id="second_p">
                      <h3 style="text-align: center">Mounting Plate 2</h3>
                      <h6>Length(MM)</h6>
                      <p><input placeholder="Length..." type="number" id="s_p_h" name="s_p_h"></p>
                      <h6>Width(MM)</h6>
                      <p><input placeholder="Width..." type="number" id="s_p_w" name="s_p_w"></p>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="s_p_q" name="s_p_q"></p>
                      <h6>Sheet Selection</h6>
                      <select class="select" id="s_p_sheet">
                          <option value="">Chose one</option>
                          <option value="12">12 Gage</option>
                          <option value="14">14 Gage</option>
                          <option value="16">16 Gage</option>
                          <option value="20">20 Gage</option>
                        </select> <br><br>
                      <button class="f_door" id="s_p_cost"><b>Calculate</b></button><br><br>
                      <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                      <p><input placeholder="Square Feet..." type="number" class="s_p_cal" readonly></p>
                      <h5>Total Weight By Sheet of Component Plat</h5>
                      <p><input placeholder="Weight By Sheet..." type="number" class="s_p_WBS" readonly></p>
                      <button id="deleteButton" class="fa fa-trash s_p_del" style="font-size:48px;color:white"> Delete</button> <br>
                  </div>

                    <div class="tab">
                        <h3 style="text-align: center">Front & Back Base</h3>
                        <h6>Length(MM)</h6>
                        <p><input placeholder="Length..." type="number" id="front_back_h" name="front_back_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="front_back_w" name="front_back_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="front_back_q" name="front_back_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="front_back_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="f-b-cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="front_back_cal" readonly></p>
                        <h5>Total Weight By Sheet of Front & Back Base</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="front_back_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash f_b_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                        <h3 style="text-align: center">Right & Left Base</h3>
                        <h6>Length(MM)</h6>
                        <p><input placeholder="Length..." type="number" id="right_left_h" name="right_left_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="right_left_w" name="right_left_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="right_left_q" name="right_left_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="right_left_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="right_left_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="right_left_cal" readonly></p>
                        <h5>Total Weight By Sheet of Right & Left Base</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="right_left_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash right_left_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                        <h3 style="text-align: center">Vertical Inside Piece Door's U</h3>
                        <h6>Length(MM)</h6>
                        <p><input placeholder="Length..." type="number" id="vertical_inside_h" name="vertical_inside_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="vertical_inside_w" name="vertical_inside_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="vertical_inside_q" name="vertical_inside_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="vertical_inside_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="vertical_inside_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="vertical_inside_cal" readonly></p>
                        <h5>Total Weight By Sheet of Vertical Inside Piece Door's U</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="vertical_inside_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash vertical_inside_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                        <h3 style="text-align: center">Horizontal Inside Piece Door's U</h3>
                        <h6>Length(MM)</h6>
                        <p><input placeholder="Length..." type="number" id="horizonal_inside_h" name="horizonal_inside_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="horizonal_inside_w" name="horizonal_inside_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="horizonal_inside_q" name="horizonal_inside_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="horizonal_inside_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="horizonal_inside_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="horizonal_inside_cal" readonly></p>
                        <h5>Total Weight By Sheet of Horizontal Inside Piece Door's U</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="horizonal_inside_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash horizonal_inside_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                        <h3 style="text-align: center">Vertical Inside Piece L Type</h3>
                        <h6>Length(MM)</h6>
                        <p><input placeholder="Length..." type="number" id="Vertical_inside_piece_h" name="Vertical_inside_piece_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="Vertical_inside_piece_w" name="Vertical_inside_piece_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="Vertical_inside_piece_q" name="Vertical_inside_piece_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="Vertical_inside_piece_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="Vertical_inside_piece_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet of <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="Vertical_inside_piece_cal" readonly></p>
                        <h5>Total Weight By Sheet of Vertical Inside Piece L Type</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="Vertical_inside_piece_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash Vertical_inside_piece_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                        <h3 style="text-align: center">Horizontal Inside Piece U Type</h3>
                        <h6>Length(MM)</h6>
                        <p><input placeholder="Length..." type="number" id="horizental_inside_piece_U_h" name="horizental_inside_piece_U_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="horizental_inside_piece_U_w" name="horizental_inside_piece_U_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="horizental_inside_piece_U_q" name="horizental_inside_piece_U_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="horizental_inside_piece_U_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="horizental_inside_piece_U_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="horizental_inside_piece_U_cal" readonly></p>
                        <h5>Total Weight By Sheet of Horizontal Inside Piece U Type</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="horizental_inside_piece_U_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash horizental_inside_piece_U_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                        <h3 style="text-align: center">Top</h3>
                        <h6>Length(MM)</h6>
                        <p><input placeholder="Length..." type="number" id="top_h" name="top_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="top_w" name="top_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="top_q" name="top_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="top_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="top_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="top_cal" readonly></p>
                        <h5>Total Weight By Sheet of Top</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="top_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash top_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                        <h3 style="text-align: center">Bottom</h3>
                        <h6>Length(MM)</h6>
                        <p><input placeholder="Length..." type="number" id="bottom_h" name="bottom_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="bottom_w" name="bottom_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="bottom_q" name="bottom_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="bottom_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="bottom_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="bottom_cal" readonly></p>
                        <h5>Total Weight By Sheet of Bottom</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="bottom_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash bottom_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                        <h3 style="text-align: center">Protection Sheet 1</h3>
                        <h6>Height(MM)</h6>
                        <p><input placeholder="Height.." type="number" id="p_s_h" name="p_s_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="p_s_w" name="p_s_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="p_s_q" name="p_s_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="p_s_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="p_s_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="p_s_cal" readonly></p>
                        <h5>Total Weight By Sheet of Protection Sheet</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="p_s_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash ps_delete" style="font-size:48px;color:white"> Delete</button> <br>
                        <button id="addButton" class="fa fa-angle-down " style="font-size:48px;color:red" onclick="$('#second_p_s').show()">Add More</button>
                    </div>
                    <div id="second_p_s">
                      <h3 style="text-align: center">Protection Sheet 2</h3>
                      <h6>Height(MM)</h6>
                      <p><input placeholder="Height.." type="number" id="s_p_s_h" name="s_p_s_h"></p>
                      <h6>Width(MM)</h6>
                      <p><input placeholder="Width..." type="number" id="s_p_s_w" name="s_p_s_w"></p>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="s_p_s_q" name="s_p_s_q"></p>
                      <h6>Sheet Selection</h6>
                      <select class="select" id="s_p_s_sheet">
                          <option value="">Chose one</option>
                          <option value="12">12 Gage</option>
                          <option value="14">14 Gage</option>
                          <option value="16">16 Gage</option>
                          <option value="20">20 Gage</option>
                        </select> <br><br>
                      <button class="f_door" id="s_p_s_cost"><b>Calculate</b></button><br><br>
                      <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                      <p><input placeholder="Square Feet..." type="number" class="s_p_s_cal" readonly></p>
                      <h5>Total Weight By Sheet of Protection Sheet</h5>
                      <p><input placeholder="Weight By Sheet..." type="number" class="s_p_s_WBS" readonly></p>
                      <button id="deleteButton" class="fa fa-trash s_ps_delete" style="font-size:48px;color:white"> Delete</button> <br>
                  </div>

                    <div class="tab">
                        <h3 style="text-align: center">Miscellaneous</h3>
                        <h6>Height(MM)</h6>
                        <p><input placeholder="Height.." type="number" id="miscellaneous_h" name="miscellaneous_h"></p>
                        <h6>Width(MM)</h6>
                        <p><input placeholder="Width..." type="number" id="miscellaneous_w" name="miscellaneous_w"></p>
                        <h6>Quantity</h6>
                        <p><input placeholder="Quantity..." type="number" id="miscellaneous_q" name="miscellaneous_q"></p>
                        <h6>Sheet Selection</h6>
                        <select class="select" id="miscellaneous_sheet">
                            <option value="">Chose one</option>
                            <option value="12">12 Gage</option>
                            <option value="14">14 Gage</option>
                            <option value="16">16 Gage</option>
                            <option value="20">20 Gage</option>
                          </select> <br><br>
                        <button class="f_door" id="miscellaneous_cost"><b>Calculate</b></button><br><br>
                        <h5>Total Square Feet <b>(height/25.4/12*width/25.4/12*quantity)</b></h5>
                        <p><input placeholder="Square Feet..." type="number" class="miscellaneous_cal" readonly></p>
                        <h5>Total Weight By Sheet of Protection Sheet</h5>
                        <p><input placeholder="Weight By Sheet..." type="number" class="miscellaneous_WBS" readonly></p>
                        <button id="deleteButton" class="fa fa-trash miscellaneous_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab" id="text-message">
                      <h3 style="text-align: center">Sheet Selection</h3>
                      <h6>Sheet Type</h6>
                        <select class="select" id="sheet_type">
                            <option value="">Chose one</option>
                            <option value="Matal">Matal Sheet</option>
                            <option value="Stainless">Stainless Sheet</option>
                            <option value="Galvanized">Galvanized Steel Sheet</option>
                        </select><br>
                        <div id="Matal">
                          <br><h5>Please Enter Matal steel Sheet price</h5>
                      <p><input type="number" id="matal_s_price"></p>
                        </div>

                        <div id="Stainless">
                          <br><h5>Please Enter Stainless Sheet price</h5>
                      <p><input type="number" id="stainless_s_price"></p>
                        </div>

                        <div id="Galvanized">
                          <br><h5>Please Enter Galvanized Steel Sheet price</h5>
                      <p><input type="number" id="galvanized_s_price"></p>
                        </div><br>
                      
                      <button class="f_door" id="sheet_selection"><b>Save and continue..</b></button><br><br>
                      <button id="deleteButton" class="fa fa-trash sheet_selection_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

    <!-- This is Bus Bar Tabs Portion -->
    <div class="tab">
      <h3 style="text-align: center">Bus Bar Sheet</h3>
      <h6>Dimension</h6>
      <select class="select" id="first_dms">
          <option value="">Chose one</option>
          <option value="20*5">20*5</option>
          <option value="25*5">25*5</option>
          <option value="25*10">25*10</option>
          <option value="30*5">30*5</option>
          <option value="30*10">30*10</option>
          <option value="40*5">40*5</option>
          <option value="40*10">40*10</option>
          <option value="50*5">50*5</option>
          <option value="50*10">50*10</option>
          <option value="60*5">60*5</option>
          <option value="60*10">60*10</option>
          <option value="80*5">80*5</option>
          <option value="80*10">80*10</option>
          <option value="100*5">100*5</option>
          <option value="100*10">100*10</option>
          <option value="120*5">120*5</option>
          <option value="120*10">120*10</option>
          <option value="150*10">150*10</option>
        </select>
      <h6>Foot Size</h6>
      <p><input placeholder="Foot Size..." type="number" id="first_foot_size" name="first_foot_size"></p>
      <h6>Sleeve Cost</h6>
      <p><input placeholder="Sleeve Cost..." type="number" id="first_sleeve_cost" name="first_sleeve_cost"></p>
      <h6>Factor</h6>
      <select class="select" id="first_factor">
          <option value="">Chose one</option>
          <option value="1.1">1.1</option>
          <option value="1.2">1.2</option>
          <option value="1.3">1.3</option>
          <option value="1.4">1.4</option>
          <option value="1.5">1.5</option>
          <option value="1.6">1.6</option>
          <option value="1.7">1.7</option>
        </select> <br><br>
      <button class="f_door" id="first_cost"><b>Calculate</b></button><br><br>
      <h5>Sleeve Total Cost <b>(foot_size*sleeve_cost*factor)</b></h5>
      <p><input placeholder="Sleeve Total Cost..." type="number" class="first_cost" readonly></p>
      <h5>Bus Bar Weight (kg)</h5>
      <p><input placeholder="Bus Bar Weight (kg)..." type="number" class="first_bbr_weight" readonly></p><br>
      <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#second_bbs').show()"> Add More</button>
    </div>

    <div id="second_bbs">
      <h3 style="text-align: center">Bus Bar Sheet</h3>
      <h6>Dimension</h6>
      <select class="select" id="second_dms">
          <option value="">Chose one</option>
          <option value="20*5">20*5</option>
          <option value="25*5">25*5</option>
          <option value="25*10">25*10</option>
          <option value="30*5">30*5</option>
          <option value="30*10">30*10</option>
          <option value="40*5">40*5</option>
          <option value="40*10">40*10</option>
          <option value="50*5">50*5</option>
          <option value="50*10">50*10</option>
          <option value="60*5">60*5</option>
          <option value="60*10">60*10</option>
          <option value="80*5">80*5</option>
          <option value="80*10">80*10</option>
          <option value="100*5">100*5</option>
          <option value="100*10">100*10</option>
          <option value="120*5">120*5</option>
          <option value="120*10">120*10</option>
          <option value="150*10">150*10</option>
        </select>
      <h6>Foot Size</h6>
      <p><input placeholder="Foot Size..." type="number" id="second_foot_size" name="second_foot_size"></p>
      <h6>Sleeve Cost <b>foot_size*sleeve_cost*factor</b></h6>
      <p><input placeholder="Sleeve Cost..." type="number" id="second_sleeve_cost" name="second_sleeve_cost"></p>
      <h6>Factor</h6>
      <select class="select" id="second_factor">
          <option value="">Chose one</option>
          <option value="1.1">1.1</option>
          <option value="1.2">1.2</option>
          <option value="1.3">1.3</option>
          <option value="1.4">1.4</option>
          <option value="1.5">1.5</option>
          <option value="1.6">1.6</option>
          <option value="1.7">1.7</option>
        </select> <br><br>
      <button class="f_door" id="second_cost"><b>Calculate</b></button><br><br>
      <h5>Sleeve Total Cost <b>(foot_size*sleeve_cost*factor)</b></h5>
      <p><input placeholder="Sleeve Total Cost..." type="number" class="second_cost" readonly></p>
      <h5>Bus Bar Weight (kg)</h5>
      <p><input placeholder="Bus Bar Weight (kg)..." type="number" class="second_bbr_weight" readonly></p><br>
      <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#third_bbs').show()">Add More</button>
    </div>

    <div id="third_bbs">
      <h3 style="text-align: center">Bus Bar Sheet</h3>
      <h6>Dimension</h6>
      <select class="select" id="third_dms">
          <option value="">Chose one</option>
          <option value="20*5">20*5</option>
          <option value="25*5">25*5</option>
          <option value="25*10">25*10</option>
          <option value="30*5">30*5</option>
          <option value="30*10">30*10</option>
          <option value="40*5">40*5</option>
          <option value="40*10">40*10</option>
          <option value="50*5">50*5</option>
          <option value="50*10">50*10</option>
          <option value="60*5">60*5</option>
          <option value="60*10">60*10</option>
          <option value="80*5">80*5</option>
          <option value="80*10">80*10</option>
          <option value="100*5">100*5</option>
          <option value="100*10">100*10</option>
          <option value="120*5">120*5</option>
          <option value="120*10">120*10</option>
          <option value="150*10">150*10</option>
        </select>
      <h6>Foot Size</h6>
      <p><input placeholder="Foot Size..." type="number" id="third_foot_size" name="third_foot_size"></p>
      <h6>Sleeve Cost</h6>
      <p><input placeholder="Sleeve Cost..." type="number" id="third_sleeve_cost" name="third_sleeve_cost"></p>
      <h6>Factor</h6>
      <select class="select" id="third_factor">
          <option value="">Chose one</option>
          <option value="1.1">1.1</option>
          <option value="1.2">1.2</option>
          <option value="1.3">1.3</option>
          <option value="1.4">1.4</option>
          <option value="1.5">1.5</option>
          <option value="1.6">1.6</option>
          <option value="1.7">1.7</option>
        </select> <br><br>
      <button class="f_door" id="third_cost"><b>Calculate</b></button><br><br>
      <h5>Sleeve Total Cost <b>(foot_size*sleeve_cost*factor)</b></h5>
      <p><input placeholder="Sleeve Total Cost..." type="number" class="third_cost" readonly></p>
      <h5>Bus Bar Weight (kg)</h5>
      <p><input placeholder="Bus Bar Weight (kg)..." type="number" class="third_bbr_weight" readonly></p><br>
      <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#fourth_bbs').show()">Add More</button>
    </div>

    <div id="fourth_bbs">
      <h3 style="text-align: center">Bus Bar Sheet</h3>
      <h6>Dimension</h6>
      <select class="select" id="fourth_dms">
          <option value="">Chose one</option>
          <option value="20*5">20*5</option>
          <option value="25*5">25*5</option>
          <option value="25*10">25*10</option>
          <option value="30*5">30*5</option>
          <option value="30*10">30*10</option>
          <option value="40*5">40*5</option>
          <option value="40*10">40*10</option>
          <option value="50*5">50*5</option>
          <option value="50*10">50*10</option>
          <option value="60*5">60*5</option>
          <option value="60*10">60*10</option>
          <option value="80*5">80*5</option>
          <option value="80*10">80*10</option>
          <option value="100*5">100*5</option>
          <option value="100*10">100*10</option>
          <option value="120*5">120*5</option>
          <option value="120*10">120*10</option>
          <option value="150*10">150*10</option>
        </select>
      <h6>Foot Size</h6>
      <p><input placeholder="Foot Size..." type="number" id="fourth_foot_size" name="fourth_foot_size"></p>
      <h6>Sleeve Cost</h6>
      <p><input placeholder="Sleeve Cost..." type="number" id="fourth_sleeve_cost" name="fourth_sleeve_cost"></p>
      <h6>Factor</h6>
      <select class="select" id="fourth_factor">
          <option value="">Chose one</option>
          <option value="1.1">1.1</option>
          <option value="1.2">1.2</option>
          <option value="1.3">1.3</option>
          <option value="1.4">1.4</option>
          <option value="1.5">1.5</option>
          <option value="1.6">1.6</option>
          <option value="1.7">1.7</option>
        </select> <br><br>
      <button class="f_door" id="fourth_cost"><b>Calculate</b></button><br><br>
      <h5>Sleeve Total Cost <b>(foot_size*sleeve_cost*factor)</b></h5>
      <p><input placeholder="Sleeve Total Cost..." type="number" class="fourth_cost" readonly></p>
      <h5>Bus Bar Weight (kg)</h5>
      <p><input placeholder="Bus Bar Weight (kg)..." type="number" class="fourth_bbr_weight" readonly></p><br>
      <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#fifth_bbs').show()">Add More</button>
    </div>

    <div id="fifth_bbs">
      <h3 style="text-align: center">Bus Bar Sheet</h3>
      <h6>Dimension</h6>
      <select class="select" id="fifth_dms">
          <option value="">Chose one</option>
          <option value="20*5">20*5</option>
          <option value="25*5">25*5</option>
          <option value="25*10">25*10</option>
          <option value="30*5">30*5</option>
          <option value="30*10">30*10</option>
          <option value="40*5">40*5</option>
          <option value="40*10">40*10</option>
          <option value="50*5">50*5</option>
          <option value="50*10">50*10</option>
          <option value="60*5">60*5</option>
          <option value="60*10">60*10</option>
          <option value="80*5">80*5</option>
          <option value="80*10">80*10</option>
          <option value="100*5">100*5</option>
          <option value="100*10">100*10</option>
          <option value="120*5">120*5</option>
          <option value="120*10">120*10</option>
          <option value="150*10">150*10</option>
        </select>
      <h6>Foot Size</h6>
      <p><input placeholder="Foot Size..." type="number" id="fifth_foot_size" name="fifth_foot_size"></p>
      <h6>Sleeve Cost</h6>
      <p><input placeholder="Sleeve Cost..." type="number" id="fifth_sleeve_cost" name="fifth_sleeve_cost"></p>
      <h6>Factor</h6>
      <select class="select" id="fifth_factor">
          <option value="">Chose one</option>
          <option value="1.1">1.1</option>
          <option value="1.2">1.2</option>
          <option value="1.3">1.3</option>
          <option value="1.4">1.4</option>
          <option value="1.5">1.5</option>
          <option value="1.6">1.6</option>
          <option value="1.7">1.7</option>
        </select> <br><br>
      <button class="f_door" id="fifth_cost"><b>Calculate</b></button><br><br>
      <h5>Sleeve Total Cost <b>(foot_size*sleeve_cost*factor)</b></h5>
      <p><input placeholder="Sleeve Total Cost..." type="number" class="fifth_cost" readonly></p>
      <h5>Bus Bar Weight (kg)</h5>
      <p><input placeholder="Bus Bar Weight (kg)..." type="number" class="fifth_bbr_weight" readonly></p><br>
      <button id="addButton" class="fa fa-angle-down" style="font-size:48px;color:red" onclick="$('#sixth_bbs').show()">Add More</button>
    </div>

    <div id="sixth_bbs">
      <h3 style="text-align: center">Bus Bar Sheet</h3>
      <h6>Dimension</h6>
      <select class="select" id="sixth_dms">
          <option value="">Chose one</option>
          <option value="20*5">20*5</option>
          <option value="25*5">25*5</option>
          <option value="25*10">25*10</option>
          <option value="30*5">30*5</option>
          <option value="30*10">30*10</option>
          <option value="40*5">40*5</option>
          <option value="40*10">40*10</option>
          <option value="50*5">50*5</option>
          <option value="50*10">50*10</option>
          <option value="60*5">60*5</option>
          <option value="60*10">60*10</option>
          <option value="80*5">80*5</option>
          <option value="80*10">80*10</option>
          <option value="100*5">100*5</option>
          <option value="100*10">100*10</option>
          <option value="120*5">120*5</option>
          <option value="120*10">120*10</option>
          <option value="150*10">150*10</option>
        </select>
      <h6>Foot Size</h6>
      <p><input placeholder="Foot Size..." type="number" id="sixth_foot_size" name="sixth_foot_size"></p>
      <h6>Sleeve Cost</h6>
      <p><input placeholder="Sleeve Cost..." type="number" id="sixth_sleeve_cost" name="sixth_sleeve_cost"></p>
      <h6>Factor</h6>
      <select class="select" id="sixth_factor">
          <option value="">Chose one</option>
          <option value="1.1">1.1</option>
          <option value="1.2">1.2</option>
          <option value="1.3">1.3</option>
          <option value="1.4">1.4</option>
          <option value="1.5">1.5</option>
          <option value="1.6">1.6</option>
          <option value="1.7">1.7</option>
        </select> <br><br>
      <button class="f_door" id="sixth_cost"><b>Calculate</b></button><br><br>
      <h5>Sleeve Total Cost <b>(foot_size*sleeve_cost*factor)</b></h5>
      <p><input placeholder="Sleeve Total Cost..." type="number" class="sixth_cost" readonly></p>
      <h5>Bus Bar Weight (kg)</h5>
      <p><input placeholder="Bus Bar Weight (kg)..." type="number" class="sixth_bbr_weight" readonly></p><br><br>                    
    </div>

    <div class="tab">
      <h3 style="text-align: center">Bus Bar Sheet</h3>
      <h6>Bus Bar Price</h6>
      <p><input placeholder="Bus Bar Price..." type="number" id="bus_bar_price" name="bus_bar_price"></p>
      <button class="f_door" id="total_cost" onclick="nextPrev(1)"><b>Save And Continue</b></button><br><br>
      <!-- <h5>Cost</h5>
      <p><input placeholder="GAS KIT Cost..." type="number" class="gk_cost" readonly></p> -->
    </div>



    <!-- This is Cost Sheet Tabs Portion -->
                    <div class="tab">
                      <h3 style="text-align: center">Panel Lock</h3>
                      <h6>Model</h6>
                      <select class="select" id="pl_model">
                          <option value="">Chose one</option>
                          <option value="480">480</option>
                          <option value="408">408</option>
                        </select> <br><br>

                      <p><input placeholder="Unit Price..." type="number" class="pl_mf" id="pl_mf"></p>
                      <p><input placeholder="Quantity..." type="number" id="pl_qty" name="pl_qty"></p>                   
                      <button class="f_door" id="pl_cost"><b>Calculate</b></button><br><br>                    
                      <h5>Cost <b>(unit_price*quantity)</b></h5>
                      <p><input placeholder="Pannel Lock Cost..." type="number" class="pl_cost" readonly></p>
                      <button id="deleteButton" class="fa fa-trash pl_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                      <h3 style="text-align: center">Hinges</h3>
                      <h6>Model</h6>
                      <select class="select" id="h_model">
                          <option value="">Chose one</option>
                          <option value="30">30</option>
                          <option value="27">27</option>
                        </select> <br><br>
                      <p><input placeholder="Unit Price..." type="number" class="h_mf" id="h_mf"></p>
                      <p><input placeholder="Quantity..." type="number" id="h_qty" name="h_qty"></p>                    
                      <button class="f_door" id="h_cost"><b>Calculate</b></button><br><br>
                      <h5>Cost <b>(unit_price*quantity)</b></h5>
                      <p><input placeholder="Hinges Cost..." type="number" class="h_cost" readonly></p>
                      <button id="deleteButton" class="fa fa-trash h_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                      <h3 style="text-align: center">ACRYLIC SHEET</h3>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="as_qty"></p>
                      <h6>Unit Cost</h6>
                      <p><input placeholder="Unit Cost..." type="number" id="as_uc"></p>
                      <button class="f_door" id="as_cost"><b>Calculate</b></button><br><br>
                      <h5>Cost <b>(quantity*unit cost)</b></h5>
                      <p><input placeholder="ACRYLIC SHEET Cost..." type="number" class="as_cost" readonly></p>
                      <button id="deleteButton" class="fa fa-trash as_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>


                    <div class="tab">
                      <h3 style="text-align: center">D-shaped/ Patti = GAS KIT</h3>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="gk_qty" name="gk_qty"></p>
                      <h6>Unit Cost</h6>
                      <p><input placeholder="Unit Cost..." type="number" id="gk_uc"></p>
                      <button class="f_door" id="gk_cost"><b>Calculate</b></button><br><br>
                      <h5>Cost <b>(quantity*unit cost)</b></h5>
                      <p><input placeholder="GAS KIT Cost..." type="number" class="gk_cost" readonly></p>
                      <button id="deleteButton" class="fa fa-trash gk_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                      <h3 style="text-align: center">I-Bolt</h3>
                      <h6>Quantity</h6>
                      <p><input placeholder="Quantity..." type="number" id="i_qty" name="i_qty"></p>
                      <h6>Unit Cost</h6>
                      <p><input placeholder="Unit Cost..." type="number" id="i_uc"></p>
                      <button class="f_door" id="i_cost"><b>Calculate</b></button><br><br>
                      <h5>Cost <b>(quantity*unit cost)</b></h5>
                      <p><input placeholder="I-Bolt Cost..." type="number" class="i_cost" readonly></p>
                      <button id="deleteButton" class="fa fa-trash i_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                      <h3 style="text-align: center">Cable Duct</h3>
                      <h6>Model</h6>
                      <select class="select" id="cd_model">
                          <option value="">Chose one</option>
                          <option value="2525">2525</option>
                          <option value="2540">2540</option>
                          <option value="333">333</option>
                          <option value="8080">8080</option>
                          <option value="4040">4040</option>
                          <option value="4060">4060</option>
                          <option value="6040">6040</option>
                          <option value="6060">6060</option>
                          <option value="100100">100100</option>
                        </select> <br><br>
                      <p><input placeholder="Unit Price..." type="number" class="cd_mf" id="cd_mf"></p>
                      <p><input placeholder="Quantity..." type="number" id="cd_qty" name="pl_qty"></p>                     
                      <button class="f_door" id="cd_cost"><b>Calculate</b></button><br><br>
                      <h5>Cost <b>(unit_price*quantity)</b></h5>
                      <p><input placeholder="Cable Duct Cost..." type="number" class="cd_cost" readonly></p>
                      <button id="deleteButton" class="fa fa-trash cd_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div> 

                    <div class="tab">
                      <h3 style="text-align: center">Paint Cost</h3>
                      <h6>Model</h6>
                      <select class="select" id="pc_model">
                          <option value="">Chose one</option>
                          <option value="7035">7035</option>
                          <option value="7032">7032</option>
                        </select> <br><br>
                      <p><input placeholder="Unit Price..." type="number" class="pc_mf" id="pc_mf"></p>
                      
                      <button class="f_door" id="pc_cost"><b>Calculate</b></button><br><br>
                      <h5>Cost <b>(height*width/645.16/144*unit_price)</b></h5>
                      <p><input placeholder="Paint cost..." type="number" class="pc_cost" readonly></p>
                      <button id="deleteButton" class="fa fa-trash pc_delete" style="font-size:48px;color:white"> Delete</button> <br>
                      </div>

                    <div class="tab">
                      <h3 style="text-align: center">Other Stationary Costs</h3>
                      <h5>Polish Cost</h5>
                      <p><input placeholder="Polish Cost..." type="number" class="polish_cost" ></p>
                      <button id="deleteButton" class="fa fa-trash polish_delete" style="font-size:48px;color:white"> Delete</button> <br>
                      <h5>Rent Cost</h5>
                      <p><input placeholder="Rent Cost..." type="number" class="rent_cost" ></p>
                      <button id="deleteButton" class="fa fa-trash rent_delete" style="font-size:48px;color:white"> Delete</button> <br>
                      <h5>Wiring Cost</h5>
                      <p><input placeholder="Wiring Cost..." type="number" class="wc_cost" ></p>
                      <button id="deleteButton" class="fa fa-trash wc_delete" style="font-size:48px;color:white"> Delete</button> <br>
                      <h5>Labour Cost</h5>
                      <p><input placeholder="Labour Cost..." type="number" class="lc_cost" ></p>
                      <button id="deleteButton" class="fa fa-trash lc_delete" style="font-size:48px;color:white"> Delete</button> <br>
                      <h5>MISC.EXP Cost</h5>
                      <p><input placeholder="MISC.EXP Cost..." type="number" class="me_cost" ></p>
                      <button id="deleteButton" class="fa fa-trash me_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>

                    <div class="tab">
                      <h3 style="text-align: center">COST PRICE IN MS 12 SWG</h3>
                      <h5>Increase By %</h5>
                      <p><input placeholder="Increase By %..." type="number" class="t_smg_percent"></p>
                      <button class="f_door" id="t_smg_percent"><b>Calculate</b></button><br><br>
                      <h5>COST PRICE IN MS 12 SWG</h5>
                      <p><input placeholder="COST PRICE IN MS 12 SWG..." type="number" class="cp_tsmg" readonly></p>
                      <h5>PERCENTAGE PRICE OF 12 SWG</h5>
                      <p><input placeholder="PERCENTAGE PRICE OF 12 SWG..." type="number" class="pp_tsmg" readonly></p>
                      <button id="deleteButton" class="fa fa-trash t_smg_delete" style="font-size:48px;color:white"> Delete</button> <br>
                      <br>
                      <h3 style="text-align: center">COST PRICE IN MS 14 SWG</h3>
                      <h5>Increase By %</h5>
                      <p><input placeholder="Increase By %..." type="number" class="f_smg_percent"></p>
                      <button class="f_door" id="f_smg_percent"><b>Calculate</b></button><br><br>
                      <h5>COST PRICE IN MS 14 SWG</h5>
                      <p><input placeholder="COST PRICE IN MS 14 SWG..." type="number" class="cp_fsmg" readonly></p>
                      <h5>PERCENTAGE PRICE OF 14 SWG</h5>
                      <p><input placeholder="PERCENTAGE PRICE OF 14 SWG..." type="number" class="pp_fsmg" readonly></p>
                      <button id="deleteButton" class="fa fa-trash f_smg_del" style="font-size:48px;color:white"> Delete</button> <br>
                      <br>
                      <h3 style="text-align: center">COST PRICE IN MS 16 SWG</h3>
                      <h5>Increase By %</h5>
                      <p><input placeholder="Increase By %..." type="number" class="s_smg_percent"></p>
                      <button class="f_door" id="s_smg_percent"><b>Calculate</b></button><br><br>
                      <h5>COST PRICE IN MS 16 SWG</h5>
                      <p><input placeholder="COST PRICE IN MS 16 SWG..." type="number" class="cp_ssmg" readonly></p>
                      <h5>PERCENTAGE PRICE OF 16 SWG</h5>
                      <p><input placeholder="PERCENTAGE PRICE OF 16 SWG..." type="number" class="pp_ssmg" readonly></p>
                      <button id="deleteButton" class="fa fa-trash s_smg_delete" style="font-size:48px;color:white"> Delete</button> <br>
                      <br>
                      <h3 style="text-align: center">COST PRICE IN MS 18 SWG</h3>
                      <h5>Increase By %</h5>
                      <p><input placeholder="Increase By %..." type="number" class="e_smg_percent"></p>
                      <button class="f_door" id="e_smg_percent"><b>Calculate</b></button><br><br>
                      <h5>COST PRICE IN MS 18 SWG</h5>
                      <p><input placeholder="COST PRICE IN MS 18 SWG..." type="number" class="cp_esmg" readonly></p>
                      <h5>PERCENTAGE PRICE OF 18 SWG</h5>
                      <p><input placeholder="PERCENTAGE PRICE OF 18 SWG..." type="number" class="pp_esmg" readonly></p>
                      <button id="deleteButton" class="fa fa-trash e_smg_delete" style="font-size:48px;color:white"> Delete</button> <br>
                      <br>
                      <h3 style="text-align: center">COST PRICE IN MS 20 SWG</h3>
                      <h5>Increase By %</h5>
                      <p><input placeholder="Increase By %..." type="number" class="ty_smg_percent"></p>
                      <button class="f_door" id="ty_smg_percent"><b>Calculate</b></button><br><br>
                      <h5>COST PRICE IN MS 20 SWG</h5>
                      <p><input placeholder="COST PRICE IN MS 20 SWG..." type="number" class="cp_tysmg" readonly></p>
                      <h5>PERCENTAGE PRICE OF 20 SWG</h5>
                      <p><input placeholder="PERCENTAGE PRICE OF 20 SWG..." type="number" class="pp_tysmg" readonly></p>
                      <button id="deleteButton" class="fa fa-trash ty_smg_delete" style="font-size:48px;color:white"> Delete</button> <br>
                      <br>
                      <h3 style="text-align: center">COST PRICE IN Multiple Gauge</h3>
                      <h5>Increase By %</h5>
                      <p><input placeholder="Increase By %..." type="number" class="mult_gauge_percent"></p>
                      <button class="f_door" id="mult_gauge_percent"><b>Calculate</b></button><br><br>
                      <h5>COST PRICE IN  Multiple Gauge</h5>
                      <p><input placeholder="COST PRICE IN Multiple Gauge..." type="number" class="cp_mult_gauge" readonly></p>
                      <h5>PERCENTAGE PRICE OF  Multiple Gauge</h5>
                      <p><input placeholder="PERCENTAGE PRICE OF Multiple Gauge..." type="number" class="pp_mult_gauge" readonly></p>
                      <button id="deleteButton" class="fa fa-trash mult_gauge_delete" style="font-size:48px;color:white"> Delete</button> <br>
                    </div>
                    
                    <div class="tab">
                    <button class="f_door" id="total_cost"><b>Calculate All Panel Price</b></button><br><br>                     
                    <button type="button"  id="buttonAlert"><i class="fa fa-save"> Save And Exit</i></button>
                      <br><br>
                      <h5>Multiple Gauge's Total Weight</h5>
                      <p><input placeholder="Multiple Gauge's Total Weight..." type="number" id="g_total_weight" readonly></p>
                      <h5>Total Square Feet</h5>
                      <p><input placeholder="Total Square Feet..." type="number" class="total_sf" readonly></p>
                      <h5>Sheet By Square Feet</h5>
                      <p><input placeholder="Sheet By Square Feet..." type="number" class="s_by_sf" readonly></p>
                      <h5>Sheet Consume</h5>
                      <p><input placeholder="Sheet Consumed..." type="number" class="sheet_consume" readonly></p>
                      <h5>Multiple Gauge Price</h5>
                      <p><input placeholder="Multiple Gauge Price..." type="number" class="mlt_gauge_pr" readonly></p>
                      <h5>12 SWG Sheet's Price</h5>
                      <p><input placeholder="12 SWG Sheet's Price..." type="number" class="twelve_SWG" readonly></p>
                      <h5>14 SWG Sheet's Price</h5>
                      <p><input placeholder="14 SWG Sheet's Price..." type="number" class="f_SWG" readonly></p>
                      <h5>16 SWG Sheet's Price</h5>
                      <p><input placeholder="16 SWG Sheet's Price..." type="number" class="sixtn_SWG" readonly></p>
                      <h5>18 SWG Sheet's Price</h5>
                      <p><input placeholder="18 SWG Sheet's Price..." type="number" class="atn_SMG" readonly></p>
                      <h5>20 SWG Sheet's Price</h5>
                      <p><input placeholder="20 SWG Sheet's Price..." type="number" class="twenty_SMG" readonly></p>
                      <h5>Panel Lock Cost</h5>
                      <p><input placeholder="Pannel Lock Cost..." type="number" class="pl_cost" readonly></p>
                      <h5>Hinges Cost</h5>
                        <p><input placeholder="Hinges Cost..." type="number" class="h_cost" readonly></p>
                      <h5>ACRYLIC SHEET Cost</h5>
                        <p><input placeholder="ACRYLIC SHEET Cost..." type="number" class="as_cost" readonly></p>
                      <h5>GAS KIT Cost</h5>
                        <p><input placeholder="GAS KIT Cost..." type="number" class="gk_cost" readonly></p>
                      <h5>I-Bolt Cost</h5>
                        <p><input placeholder="I-Bolt Cost..." type="number" class="i_cost" readonly></p>
                      <h5>Cable Duct Cost</h5>
                        <p><input placeholder="Cable Duct Cost..." type="number" class="cd_cost" readonly></p>
                      <h5>Paint Cost</h5>
                        <p><input placeholder="Paint cost..." type="number" class="pc_cost" readonly></p>
                        <h5>Bus Bar total weight</h5>
                        <p><input placeholder="Bus Bar Weight..." type="number" class="bbr_total_weight" readonly></p>
                        <h5>Bus Bar Cost</h5>
                        <p><input placeholder="Bus Bar Cost..." type="number" class="bbr_total_cost" readonly></p>
                        <h5>Bus Bar Tin Cost</h5>
                        <p><input placeholder="Tin Cost..." type="number" class="tin_cost" readonly></p>
                        <h5>Bus Bar Seleeve Cost</h5>
                        <p><input placeholder="Seleeve Cost.." type="number" class="total_sleeve_cost" readonly></p>
                    </div> 


                    <div class="hidden">
                      <input type="hidden" class="20_5_foot_size"></p>
                      <input type="hidden" class="20_5_sleeve_cost"></p>
                      <input type="hidden" class="20_5_factor"></p>
                      <input type="hidden" class="20_5_sleeve_total_cost"></p>
                      <input type="hidden" class="20_5_bbr_weight"></p>

                      <input type="hidden" class="25_5_foot_size"></p>
                      <input type="hidden" class="25_5_sleeve_cost"></p>
                      <input type="hidden" class="25_5_factor"></p>
                      <input type="hidden" class="25_5_sleeve_total_cost"></p>
                      <input type="hidden" class="25_5_bbr_weight"></p>

                      <input type="hidden" class="25_10_foot_size"></p>
                      <input type="hidden" class="25_10_sleeve_cost"></p>
                      <input type="hidden" class="25_10_factor"></p>
                      <input type="hidden" class="25_10_sleeve_total_cost"></p>
                      <input type="hidden" class="25_10_bbr_weight"></p>
                      
                      <input type="hidden" class="30_5_foot_size"></p>
                      <input type="hidden" class="30_5_sleeve_cost"></p>
                      <input type="hidden" class="30_5_factor"></p>
                      <input type="hidden" class="30_5_sleeve_total_cost"></p>
                      <input type="hidden" class="30_5_bbr_weight"></p>

                      <input type="hidden" class="30_10_foot_size"></p>
                      <input type="hidden" class="30_10_sleeve_cost"></p>
                      <input type="hidden" class="30_10_factor"></p>
                      <input type="hidden" class="30_10_sleeve_total_cost"></p>
                      <input type="hidden" class="30_10_bbr_weight"></p>

                      <input type="hidden" class="40_5_foot_size"></p>
                      <input type="hidden" class="40_5_sleeve_cost"></p>
                      <input type="hidden" class="40_5_factor"></p>
                      <input type="hidden" class="40_5_sleeve_total_cost"></p>
                      <input type="hidden" class="40_5_bbr_weight"></p>

                      <input type="hidden" class="40_10_foot_size"></p>
                      <input type="hidden" class="40_10_sleeve_cost"></p>
                      <input type="hidden" class="40_10_factor"></p>
                      <input type="hidden" class="40_10_sleeve_total_cost"></p>
                      <input type="hidden" class="40_10_bbr_weight"></p>

                      <input type="hidden" class="50_5_foot_size"></p>
                      <input type="hidden" class="50_5_sleeve_cost"></p>
                      <input type="hidden" class="50_5_factor"></p>
                      <input type="hidden" class="50_5_sleeve_total_cost"></p>
                      <input type="hidden" class="50_5_bbr_weight"></p>

                      <input type="hidden" class="50_10_foot_size"></p>
                      <input type="hidden" class="50_10_sleeve_cost"></p>
                      <input type="hidden" class="50_10_factor"></p>
                      <input type="hidden" class="50_10_sleeve_total_cost"></p>
                      <input type="hidden" class="50_10_bbr_weight"></p>

                      <input type="hidden" class="60_5_foot_size"></p>
                      <input type="hidden" class="60_5_sleeve_cost"></p>
                      <input type="hidden" class="60_5_factor"></p>
                      <input type="hidden" class="60_5_sleeve_total_cost"></p>
                      <input type="hidden" class="60_5_bbr_weight"></p>

                      <input type="hidden" class="60_10_foot_size"></p>
                      <input type="hidden" class="60_10_sleeve_cost"></p>
                      <input type="hidden" class="60_10_factor"></p>
                      <input type="hidden" class="60_10_sleeve_total_cost"></p>
                      <input type="hidden" class="60_10_bbr_weight"></p>

                      <input type="hidden" class="80_5_foot_size"></p>
                      <input type="hidden" class="80_5_sleeve_cost"></p>
                      <input type="hidden" class="80_5_factor"></p>
                      <input type="hidden" class="80_5_sleeve_total_cost"></p>
                      <input type="hidden" class="80_5_bbr_weight"></p>

                      <input type="hidden" class="80_10_foot_size"></p>
                      <input type="hidden" class="80_10_sleeve_cost"></p>
                      <input type="hidden" class="80_10_factor"></p>
                      <input type="hidden" class="80_10_sleeve_total_cost"></p>
                      <input type="hidden" class="80_10_bbr_weight"></p>

                      <input type="hidden" class="100_5_foot_size"></p>
                      <input type="hidden" class="100_5_sleeve_cost"></p>
                      <input type="hidden" class="100_5_factor"></p>
                      <input type="hidden" class="100_5_sleeve_total_cost"></p>
                      <input type="hidden" class="100_5_bbr_weight"></p>

                      <input type="hidden" class="100_10_foot_size"></p>
                      <input type="hidden" class="100_10_sleeve_cost"></p>
                      <input type="hidden" class="100_10_factor"></p>
                      <input type="hidden" class="100_10_sleeve_total_cost"></p>
                      <input type="hidden" class="100_10_bbr_weight"></p>

                      <input type="hidden" class="120_5_foot_size"></p>
                      <input type="hidden" class="120_5_sleeve_cost"></p>
                      <input type="hidden" class="120_5_factor"></p>
                      <input type="hidden" class="120_5_sleeve_total_cost"></p>
                      <input type="hidden" class="120_5_bbr_weight"></p>

                      <input type="hidden" class="120_10_foot_size"></p>
                      <input type="hidden" class="120_10_sleeve_cost"></p>
                      <input type="hidden" class="120_10_factor"></p>
                      <input type="hidden" class="120_10_sleeve_total_cost"></p>
                      <input type="hidden" class="120_10_bbr_weight"></p>

                      <input type="hidden" class="150_10_foot_size"></p>
                      <input type="hidden" class="150_10_sleeve_cost"></p>
                      <input type="hidden" class="150_10_factor"></p>
                      <input type="hidden" class="150_10_sleeve_total_cost"></p>
                      <input type="hidden" class="150_10_bbr_weight"></p>
                    </div>

                    <div style="overflow:auto;" id="nextprevious">
                        <div style="float:right;">
                          <button type="button" id="prevBtn1" onclick="nextPrev(-1)"><i class="fa fa-angle-double-left">Back</i></button>
                          <button type="button" id="nextBtn1" onclick="nextPrev(1)"><i class="fa fa-angle-double-right">Next</i></button> </div>
                </div>
            </div>
        </div>
    </div>

  
</body>
</html>