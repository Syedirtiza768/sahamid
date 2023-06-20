<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php echo ((isset($title)) ? $title:"SAHamid ERP"); ?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/bower_components/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/bower_components/Ionicons/css/ionicons.min.css">
  <link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/bower_components/icons/icons.css">
  <link rel="stylesheet" href="<?php echo $NewRootPath; ?>quotation/assets/vendor/jquery-datatables-bs3/assets/css/datatables.css" />
  <link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/dist/css/skins/_all-skins.min.css">
  <link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/plugins/pace/pace.min.css">
  <link rel="stylesheet" href="<?php echo $NewRootPath; ?>shop/parchi/inward/assets/searchSelect.css">
  <link rel="stylesheet" href="<?php echo $NewRootPath; ?>v2/assets/bower_components/icons/icons.css">


  <script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/jquery/dist/jquery.min.js"></script>
  <script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
  <script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
  <script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/fastclick/lib/fastclick.js"></script>
  <script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/Chart.js/Chart.js"></script>
  <script src="<?php echo $NewRootPath; ?>quotation/assets/vendor/jquery-datatables/media/js/jquery.dataTables.js"></script>
  <script src="<?php echo $NewRootPath; ?>quotation/assets/vendor/jquery-datatables-bs3/assets/js/datatables.js"></script>
  <script src="<?php echo $NewRootPath; ?>v2/assets/dist/js/adminlte.min.js"></script>
  <script src="<?php echo $NewRootPath; ?>v2/assets/bower_components/PACE/pace.min.js"></script>
  <script src="<?php echo $NewRootPath; ?>v2/assets/dist/js/demo.js"></script>
  <script src="<?php echo $NewRootPath; ?>v2/assets/plugins/highcharts.js"></script>
  <script src="<?php echo $NewRootPath; ?>v2/assets/plugins/funnel.js"></script>
  <script src="<?php echo $NewRootPath; ?>shop/parchi/inward/assets/searchSelect.js"></script>
  <script src="<?php echo $NewRootPath; ?>v2/assets/datatables/dataTables.buttons.min.js"></script>
  <script src="<?php echo $NewRootPath; ?>v2/assets/datatables/buttons.html5.min.js"></script>
  <script src="<?php echo $NewRootPath; ?>v2/assets/popper.min.js"></script>

  <script src="<?php echo $NewRootPath; ?>quotation/assets/vendor/jquery-datatables/extras/sum.js"></script>

 <!-- <script>
      $(document).ready(function(){

          function load_unseen_notification(view = '')
          {
              $.ajax({
                  url:"fetch.php",
                  method:"POST",
                  data:{view:view},
                  dataType:"json",
                  success:function(data)
                  {
                      $('.dropdown-menu').html(data.notification);
                      if(data.unseen_notification > 0)
                      {
                          $('.count').html(data.unseen_notification);
                      }
                  }
              });
          }

          load_unseen_notification();



          $(document).on('click', '.dropdown-toggle', function(){
              $('.count').html('');
              load_unseen_notification('yes');
          });

          setInterval(function(){
              load_unseen_notification();
          }, 30000);

      });
  </script>
  -->
  <style>
    div.paceDiv {
      position: fixed;
      top: 0;
      left: 0;
      z-index: 999;
      width: 100%;
      height: 100vh;
      background-color: rgba(255,255,255,1.0);
      display: block;
      transition: all 0.8s ease-out;

    }
    .display-block{
      display: block;
    }
    .highcharts-credits{
      display: none !important;
    }

    .dropdown-menu {
        width: 600px !important;
        background-color: #F5F5F5;
        border:none;

        word-wrap: break-spaces;
    }
    .scrollable-menu {
        height: auto;
        max-height: 500px;
        overflow-x: hidden;
    }
  </style>
</head>
<body class="fixed sidebar-mini sidebar-mini-expand-feature skin-black"> <!--sidebar-collapse-->
<div class="paceDiv"></div>
<div class="wrapper">