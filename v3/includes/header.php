<?php 
  include_once("head.php");
?>
<header class="main-header" data-turbolinks="false">
  <a class="logo">
    <span class="logo-mini"><b>SA</b>H</span>
    <span class="logo-lg"><b>SA</b> Hamid</span>
  </a>
  <nav class="navbar navbar-static-top">
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
      <span class="sr-only">Toggle navigation</span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
      <span class="icon-bar"></span>
    </a>
    <!-- <form action="#" method="get" class="sidebar-form hidden-xs" style="float: left; margin: 6px !important; ">
      <div class="input-group">
        <input type="text" name="q" class="" placeholder="Search..." style="background-color: white">
        <span class="input-group-btn" style="width: auto">
              <button type="submit" name="search" id="search-btn" class="btn btn-flat" style="background-color: white; border-left: 1px #424242 solid"><i class="fa fa-search"></i>
              </button>
            </span>
      </div>
    </form> -->
    <div class="navbar-custom-menu">
      <er class="nav navbar-nav">
        <!-- <li class="dropdown messages-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-envelope-o"></i>
            <span class="label label-success">4</span>
          </a>
          <ul class="dropdown-menu">
            <li class="header" style="text-align: center; background-color: white">Messages</li>
            <li>
              <ul class="menu">
                <li style="text-align: center; "><a href="#">You have no new messages</a></li>

                <li>
                  <a href="#">
                    <div class="pull-left">
                      <img src="assets/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
                    </div>
                    <h4>
                      Support Team
                      <small><i class="fa fa-clock-o"></i> 5 mins</small>
                    </h4>
                    <p>Why not buy a new awesome theme?</p>
                  </a>
                </li>
              </ul>
            </li>
            <li class="footer"><a href="#">See All Messages</a></li>
          </ul>
        </li> -->
        <!-- <li class="dropdown notifications-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="fa fa-bell-o"></i>
            <span class="label label-warning">10</span>
          </a>
          <ul class="dropdown-menu">
            <li class="header" style="text-align: center; background-color: white">Notifications</li>
            <li>
              <ul class="menu">
                <li>
                  <a href="#">
                    <i class="fa fa-users text-aqua"></i> 5 new members joined today
                  </a>
                </li>
              </ul>
            </li>
            <li class="footer"><a href="#">View all</a></li>
          </ul>
        </li> -->
        <!-- User Account: style can be found in dropdown.less -->
        <?php if($_SESSION['UserID'] == "admin" || (isset($_SESSION['orignalUserID']) && $_SESSION['orignalUserID'] == "admin")) { ?>
        <li class="user" style="padding: 10px; border-right: 1px solid #ccc; display: none;">
            <form action="/sahamid/loginAs.php">
              <input type="text" name="UserID" style="border:1px solid #424242; border-radius: 7px; padding: 3px" placeholder="Boom">
              <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </li>
        <?php } ?>
        <li class="user" style="padding: 15px">
            <span><?php echo date('d/m/Y h:i A'); ?></span>
        </li>
          <!--<li>

          <ul class="nav navbar-nav navbar-right">
              <li class="dropdown inbox">
                  <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-bell" style="font-size:18px;"><span class="label label-pill label-danger count" style="border-radius:10px;z-index: "></span> </span></a>
                  <ul class="dropdown-menu scrollable-menu "></ul>
              </li>
          </ul>
          </li>-->
        <li class="dropdown user user-menu">
          <a href="#" class="dropdown-toggle" data-toggle="user-menu">
            <img src="assets/dist/img/default.jpg" class="user-image">
            <span class="hidden-xs"><?php echo $_SESSION['UsersRealName']; ?></span>
          </a>


            <!-- <li class="header">
                <a href="#" class="" style="padding: 10px">Profile</a>
            </li> -->

            <li class="header"><a href="<?php echo $NewRootPath; ?>Logout.php" style="padding: 10px"><span class="glyphicon glyphicon-log-out" style="font-size:18px;"></a></li>

        </li>
      </ul>
    </div>
  </nav>
</header>