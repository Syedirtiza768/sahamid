<aside class="main-sidebar">
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar" data-turbolinks="false">
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu">

	  <li class="treeview <?php ecif($active == "tasks","active") ?>">
      <a href="<?php echo $NewRootPath; ?>v2/tasks.php">
        <i class="fa fa-tasks"></i> <span>Tasks</span>
      </a>
    </li>
        <li class="treeview <?php ecif($active == "documentmanager","active") ?>">
            <a href="<?php echo $NewRootPath; ?>v2/Document_Manager.php" >
                <i class="fa fa-tasks"></i> <span>Document Manager</span>
            </a>
        </li>
        <?php if(userHasPermission($db, 'dms_dashboard')){ ?>
            <li>
                <a target="_blank" href="<?php echo $NewRootPath; ?>v2/DMS_Dashboard">
                    <i class="fa fa-check-circle"></i> DMS Dashboard
                </a>
            </li>
        <?php } ?>

        <li class="treeview dropdownmenu <?php ecif($active == "dashboard","active") ?>">
        <a href="#">
          <i class="fa fa-dashboard"></i> <span>New Dashboards</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="">
          <?php
            $SQL = "SELECT dashboard_id,name 
                    FROM user_dashboards 
                    INNER JOIN dashboards ON dashboards.id = user_dashboards.dashboard_id
                    WHERE user_id='".$_SESSION['UserID']."'";
            $dashboards = mysqli_query($db, $SQL);
            while($row = mysqli_fetch_assoc($dashboards)){
          ?>
          <li class="treeview">
            <a target="_blank" href="<?php echo $NewRootPath."v2/dashV2.php?Dashboard=".$row['dashboard_id'].'"'; ?>>
              <i class="fa fa-dashboard"></i> <?php echo $row['name']; ?>
            </a>
          </li>
          <?php } ?>
          <?php if(userHasPermission($db, 'create_new_dashboard')){ ?>
            <li class="treeview">
              <a href="<?php echo $NewRootPath; ?>v2/dashboard-UD.php"; >
                <i class="fa fa-dashboard"></i> Create Dashboard
              </a>
            </li>
          <?php } ?>
          <?php if(userHasPermission($db, 'assign_user_dashboard')){ ?>
            <li class="treeview">
              <a href="<?php echo $NewRootPath; ?>v2/userDashboards.php"; >
                <i class="fa fa-dashboard"></i> Assign User Dashboard
              </a>
            </li>
          <?php } ?>
        </ul>
      </li>




        <li class="treeview dropdownmenu <?php ecif($active == "dashboard","active") ?>">
        <a href="#">
          <i class="fa fa-dashboard"></i> <span>Dashboards</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="">
          <?php
            $SQL = "SELECT dashboard_id,name 
                    FROM user_dashboards 
                    INNER JOIN dashboards ON dashboards.id = user_dashboards.dashboard_id
                    WHERE user_id='".$_SESSION['UserID']."'";
            $dashboards = mysqli_query($db, $SQL);
            while($row = mysqli_fetch_assoc($dashboards)){
          ?>
          <li class="treeview">
            <a href="<?php echo $NewRootPath."v2/dash.php?Dashboard=".$row['dashboard_id'].'"'; ?>>
              <i class="fa fa-dashboard"></i> <?php echo $row['name']; ?>
            </a>
          </li>
          <?php } ?>
          <?php if(userHasPermission($db, 'create_new_dashboard')){ ?>
            <li class="treeview">
              <a href="<?php echo $NewRootPath; ?>v2/dashboard-UD.php"; >
                <i class="fa fa-dashboard"></i> Create Dashboard
              </a>
            </li>
          <?php } ?>
          <?php if(userHasPermission($db, 'assign_user_dashboard')){ ?>
            <li class="treeview">
              <a href="<?php echo $NewRootPath; ?>v2/userDashboards.php"; >
                <i class="fa fa-dashboard"></i> Assign User Dashboard
              </a>
            </li>
          <?php } ?>
        </ul>
      </li>
      <li class="treeview dropdownmenu">
        <a href="#">
          <i class="fa fa-briefcase"></i> <span>Sales</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="">
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['salescase.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>salescase.php?NewSalesCase=Yes">
              <i class="fa fa-plus"></i> Create Salescase
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['selectsalescase.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>salescase/selectsalescase.php">
              <i class="fa fa-list"></i> 
                View Salescases
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['selectsalescaseclosed.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>salescase/selectsalescaseclosed.php">
              <i class="fa fa-list"></i> 
                View Closed Salescases
            </a>
          </li>
          <?php } ?>
            <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['salescaseWatchlist.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){
                ?>
                <li>
                    <a href="<?php echo $NewRootPath; ?>salescase/salescaseWatchlist.php">
                        <i class="fa fa-list"></i>
                       Salescase Watchlist
                    </a>
                </li>
            <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['Customers.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>Customers.php">
              <i class="fa fa-plus"></i> 
                Add Customer
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['SelectCustomer.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>SelectCustomer.php">
              <i class="fa fa-search"></i> 
                Search Customer
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['newinvoice.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>invoice/newinvoice.php">
              <i class="fa fa-money"></i> 
                DC To Invoice
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['CustomerReceipt.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>CustomerReceipt.php?NewReceipt=Yes&Type=Customer">
              <i class="fa fa-plus"></i> 
                Enter Receipt
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['CustomerAllocations.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>CustomerAllocations.php">
              <i class="fa fa-ticket"></i> 
                Allocate Receipt
            </a>
          </li>
          <?php } ?>
		  <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['invoiceReturn.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>invoice/invoiceReturn.php">
              <i class="fa fa-ticket"></i> 
                Invoice Return
            </a>
          </li>
          <?php } ?>
        </ul>
      </li>
      <li class="treeview dropdownmenu">
        <a href="#">
          <i class="fa fa-th-large"></i> <span>Inventory</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="">
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['Stocks.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>Stocks.php">
              <i class="fa fa-plus"></i> 
                Add Item
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['itemSearch.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>inventory/itemSearch.php">
              <i class="fa fa-search"></i> Search Item
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['StockCategories.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>StockCategories.php">
              <i class="fa fa-map-marker"></i> Stock Category Maintainance
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['Manufacturers.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>Manufacturers.php">
              <i class="fa fa-map-marker"></i> Brand Maintainance
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['igp.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>igp.php?New=Yes">
              <i class="fa fa-arrow-right"></i> IGP
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['pos.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>pos.php?New=Yes">
              <i class="fa fa-arrow-left"></i> OGP
            </a>
          </li>
          <?php } ?>
		  <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['StockAdjustments.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>StockAdjustments.php?NewAdjustment=Yes">
              <i class="fa fa-arrow-left"></i> Inventory Adjustments
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['InterStoreStockRequest.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>InterStoreStockRequest.php?New=Yes">
              <i class="fa fa-plus"></i> Create Stock Request
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['requeststatus.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>requeststatus.php">
              <i class="fa fa-eye"></i> View Request Status
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['InterStoreStockRequestAuthorisation.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>InterStoreStockRequestAuthorisation.php">
              <i class="fa fa-question"></i> Authorise Stock Request
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['InterStoreStockRequestFulfill.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>InterStoreStockRequestFulfill.php">
              <i class="fa fa-check-circle"></i> Fulfill Stock Request
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['reorderRequest.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>reorder/reorderRequest.php">
              <i class="fa fa-check-circle"></i> New Reorder Item Request
            </a>
          </li>
          <?php } ?>
    		  <?php if(userHasPermission($db, 'reorder_items_approval')){ ?>
    		  <li>
            <a href="<?php echo $NewRootPath; ?>reorder/reorderApproval.php">
              <i class="fa fa-check-circle"></i> Reorder Items Acknowledge
            </a>
          </li>
    		  <?php } ?>
        </ul>
      </li>
      <li class="treeview dropdownmenu">
        <a href="#">
          <i class="fa fa-money"></i> <span>Petty Cash</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="">
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['PcTabs.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>PcTabs.php">
              <i class="fa fa-table"></i> Tabs
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['PcTypeTabs.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>PcTypeTabs.php">
              <i class="fa fa-question-circle"></i> Tab Types
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['PcExpenses.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>PcExpenses.php">
              <i class="fa fa-minus-circle"></i> Expenses
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['PcExpensesTypeTab.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>PcExpensesTypeTab.php">
              <i class="fa fa-cog"></i> Tab Expense Management
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['PcAssignCashToTab.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>PcAssignCashToTab.php">
              <i class="fa fa-money"></i> Assign Cash
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['PcClaimExpensesFromTab.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>PcClaimExpensesFromTab.php">
              <i class="fa fa-tags"></i> Claim Expenses
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['PcAuthorizeExpenses.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>PcAuthorizeExpenses.php">
              <i class="fa fa-gavel"></i> Expenses Authorisation
            </a>
          </li>
          <?php } ?>
        </ul>
      </li>
      <li class="treeview dropdownmenu">
        <a href="#">
          <i class="fa fa-book"></i> <span>General Ledger</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="">
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['GLAccounts.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>GLAccounts.php">
              <i class="fa fa-list"></i> GL Accounts
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['GLBudgets.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>GLBudgets.php">
              <i class="fa fa-money"></i> GL Budgets
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['GLTags.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>GLTags.php">
              <i class="fa fa-tags"></i> GL Tags
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['BankAccounts.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>BankAccounts.php">
              <i class="fa fa-list"></i> Bank Accounts
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['BankAccountUsers.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>BankAccountUsers.php">
              <i class="fa fa-gavel"></i> Authorised Users
            </a>
          </li>
          <?php } ?>
        </ul>
      </li>
    <li class="treeview dropdownmenu <?php ecif($active == "procurement","active") ?>">
        <a href="#">
          <i class="fa fa-book"></i> <span>Procurement</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="">
          <?php
            if(userHasPermission($db,"procurement_workspace")){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>procurement/workspace.php">
              <i class="fa fa-plus"></i> Workspace
            </a>
          </li>
          <?php } ?>
      </ul>
    </li>  
	  <li class="treeview dropdownmenu <?php ecif($active == "shop","active") ?>">
        <a href="#">
          <i class="fa fa-book"></i> <span>Shop</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="">
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['newShopVendor.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>v2/newShopVendor.php">
              <i class="fa fa-plus"></i> Add New Market Place Business
            </a>
          </li>
          <?php } ?>
		      <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['inwardParchi.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>shop/parchi/inward/inwardParchi.php">
              <i class="fa fa-plus"></i> Create Inward Bazar Parchi
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['listInwardBazarParchi.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>shop/parchi/inward/listInwardBazarParchi.php">
              <i class="fa fa-plus"></i> List Inward Bazar Parchi
            </a>
          </li>
          <?php } ?>
            <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['mpireturn.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){
                ?>
                <li>
                    <a href="<?php echo $NewRootPath; ?>shop/parchi/inward/mpireturn.php">
                        <i class="fa fa-plus"></i> MPI Return
                    </a>
                </li>
            <?php } ?>

            <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['listInwardBazarParchi.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){
                ?>
                <li>
                    <a href="<?php echo $NewRootPath; ?>/shop/voucher/voucher.php?type=rv">
                        <i class="fa fa-plus"></i> Receipt Voucher
                    </a>
                </li>
            <?php } ?>
            <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['listInwardBazarParchi.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){
                ?>
                <li>
                    <a href="<?php echo $NewRootPath; ?>/shop/voucher/voucher.php?type=pv">
                        <i class="fa fa-plus"></i> Payment Voucher Direct
                    </a>
                </li>
            <?php } ?>
            <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['listInwardBazarParchi.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){
                ?>
                <li>
                    <a href="<?php echo $NewRootPath; ?>/shop/voucher/paymentVoucher.php">
                        <i class="fa fa-plus"></i> Payment Voucher
                    </a>
                </li>
            <?php } ?>
            <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['listInwardBazarParchi.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){
                ?>
                <li>
                    <a href="<?php echo $NewRootPath; ?>/shop/voucher/voucherList.php?type=604">
                        <i class="fa fa-plus"></i> List Receipt Voucher
                    </a>
                </li>
            <?php } ?>
            <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['listInwardBazarParchi.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){
                ?>
                <li>
                    <a href="<?php echo $NewRootPath; ?>/shop/voucher/voucherList.php?type=605">
                        <i class="fa fa-plus"></i> List Payment Voucher
                    </a>
                </li>
            <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['outwardParchi.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>shop/parchi/outward/outwardParchi.php">
              <i class="fa fa-plus"></i> Create Outward Bazar Parchi
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['listOutwardBazarParchi.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>shop/parchi/outward/listOutwardBazarParchi.php">
              <i class="fa fa-plus"></i> List Outward Bazar Parchi
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['SupplierAllocations.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>SupplierAllocations.php">
              <i class="fa fa-plus"></i> Allocate Supplier Payment
            </a>
          </li>
          <?php } ?>
          <?php
            if(userHasPermission($db, 'mpi_cust_balance_sheet')){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>reports/balance/custbalance/MPICustBalanceSheet.php">
              <i class="fa fa-plus"></i> MPI Balance Sheet
            </a>
          </li>
          <?php } ?>
          <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['SuppBalanceSheet.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>reports/balance/suppbalance/SuppBalanceSheet.php">
              <i class="fa fa-plus"></i> Supplier Balance Sheet
            </a>
          </li>
          <?php } ?>
            <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['SuppBalanceSheetAdjusted.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){
                ?>
                <li>
                    <a href="<?php echo $NewRootPath; ?>reports/balance/suppbalance/SuppBalanceSheetAdjusted.php">
                        <i class="fa fa-plus"></i> Supplier Balance Sheet Adjusted
                    </a>
                </li>
            <?php } ?>
		  <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['SuppStatementFilter.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>reports/balance/suppstatement/SuppStatementFilter.php">
              <i class="fa fa-plus"></i> Market Place Business Statements
            </a>
          </li>
          <?php } ?>

            <?php
            $PageSecurity = $_SESSION['PageSecurityArray']['SuppStatementFilterRev.php'];
            if(in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens'])){
                ?>
                <li>
                    <a href="<?php echo $NewRootPath; ?>reports/balance/suppstatement/SuppStatementFilterRev.php">
                        <i class="fa fa-plus"></i> Vendor Reverse Allocation
                    </a>
                </li>
            <?php } ?>
		 </ul>
	  </li>
	  <li class="treeview dropdownmenu <?php ecif($active == "shopsale","active") ?>">
        <a href="#">
          <i class="fa fa-book"></i> <span>POS</span>
          <span class="pull-right-container">
            <i class="fa fa-angle-left pull-right"></i>
          </span>
        </a>
        <ul class="treeview-menu" style="">
          <?php
            if(userHasPermission($db,'create_shop_sale')){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>shop/pos/shopSale.php">
              <i class="fa fa-plus"></i> Create (CSV/CRV)
            </a>
          </li>
          <?php } ?>
		  <?php
            if(userHasPermission($db,'shopsale_list')){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>shop/pos/shopSaleList.php">
              <i class="fa fa-plus"></i> (CSV/CRV) List
            </a>
          </li>
          <?php } ?>
		  <?php
            if(userHasPermission($db,'cashDrawer')){ 
          ?>
          <li>
            <a href="<?php echo $NewRootPath; ?>v2/cashDrawer.php">
              <i class="fa fa-plus"></i> Drawer
            </a>
          </li>
          <?php } ?>
		 </ul>
	  </li>
      <li class="treeview <?php ecif($active == "reports","active") ?>">
        <a href="<?php echo $NewRootPath; ?>v2/reportLinks.php">
          <i class="fa fa-file-text"></i> <span>Reports</span>
        </a>
      </li>
	  
      
    </ul>
  </section>
  <!-- /.sidebar -->
</aside>