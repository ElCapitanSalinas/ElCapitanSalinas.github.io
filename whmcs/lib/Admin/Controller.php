<?php

namespace WHMCS\Module\Addon\rspanel\Admin;
use WHMCS\Database\Capsule;
/**
 * Sample Admin Area Controller
 */
class Controller{

    /**
     * Index action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return string
     */
    public function index($vars)
    {
        // Get common module parameters
        $modulelink = $vars['modulelink']; 
        $version = $vars['version']; 
        $LANG = $vars['_lang']; 

        return <<<EOF

            <h2>Manage</h2>
            <div class="reports-index">
                <h2>Manage</h2>
                <div>
                    <a class="btn btn-info" href="{$modulelink}&action=customers">Manage Users</a>
                    <a class="btn btn-info" href="{$modulelink}&action=servers">Manage Servers</a>
                    <a class="btn btn-info" href="{$modulelink}&action=nullroutes">Manage Null Routes</a>
                    <a class="btn btn-info" href="{$modulelink}&action=ddoshistory">DDoS History</a>
                </div>
            </div>        



        EOF;
    }
 
    public function customers($vars)
    {   
        
        try{
            // Get common module parameters
            $modulelink = $vars['modulelink'];
            $LANG = $vars['_lang'];
            $errors = [];
            $customersData = [];
            $customerIds = [];
            
            $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
            $search = isset($_REQUEST['search']) ? trim($_REQUEST['search']) : '';
            $filterField = isset($_REQUEST['filterField']) ? $_REQUEST['filterField'] : '';
            
            $pageLink = $modulelink.'&action=customers';
            if(!empty($search) && !empty($filterField)){
                $pageLink .= '&filterField='.$filterField.'&search='.$search;
            }
            
            
            $prevPage = $page - 1 ;
            if($page == 1){
                $prevPage = 1;
            }
            $nextPage = $page + 1 ;
            
            /*get all customer*/
            $apiObj = rs_getAPIObj();
            $getCustomerResp = $apiObj->getCustomers($page, $search, $filterField);
            
            if($getCustomerResp['success'] != true){
                $errors = $getCustomerResp['errors'];
            }
            else{
                $customers = $getCustomerResp['data'];
                foreach($customers as $customer){
                    $customersData[$customer['userId']] = [
                        'rsUserId' => $customer['userId'],
                        'rsUserName' => $customer['userName'],
                        'rsEmailAddress' => $customer['emailAddress'],
                        'clientId' => '',
                        'clientName' => ''
                    ];

                    $customerIds[] = $customer['userId'];
                }

                $whmcsCustomers = Capsule::table("mod_reliablesite_customers")
                        ->select('rsUserId','tblclients.id', 'tblclients.firstname', 'tblclients.lastname', 'tblclients.email')
                        ->join('tblclients', 'tblclients.id','=', 'mod_reliablesite_customers.clientId')
                        ->whereIn('rsUserId', $customerIds) 
                        ->get();

                foreach($whmcsCustomers as $whmcsCustomer){
                    $rsUserId = $whmcsCustomer->rsUserId;
                    if(isset($customersData[$rsUserId])){
                        $customersData[$rsUserId]['clientId'] = $whmcsCustomer->id;
                        $customersData[$rsUserId]['clientName'] = $whmcsCustomer->firstname.' '.$whmcsCustomer->lastname;
                    }
                }        
               
            }
            
            ?>
            <p>
                <a href="<?php echo $modulelink ?>" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i>
                    <?php echo $LANG['backToHome.Label']; ?>
                </a>
            </p>
            <h2>Add Customer (ReliableSite)</h2>
            <form id="rsAddCustomer" method="post" action="<?php echo $modulelink ?>&action=addcustomer">
                <table class="form" width="100%">
                    <tbody>
                        <tr>
                            <td class="fieldlabel">User Name</td>
                            <td class="fieldarea">
                                <input type="text" class="form-control input-250 input-inline" name="username" value="">
                            </td>
                            <td class="fieldlabel">Email</td>
                            <td class="fieldarea">
                                <input type="text" class="form-control input-250 input-inline" name="email" value="">
                            </td>
                            <td class="fieldlabel"><?php echo $LANG['password.Label']; ?></td>
                            <td class="fieldarea">
                                <input type="text" class="form-control input-250 input-inline" name="password" id="inputPassword" autocomplete="off" value="">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="btn-container">
                    <input type="submit" id="btnAddCusomer" value="Add Customer" class="btn btn-primary">
                </div>
            </form>
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    jQuery("form#rsAddCustomer").submit(function(e){
                        e.preventDefault();

                        var btn = jQuery("form#rsAddCustomer input#btnAddCusomer");

                        btn.attr('disabled',true);
                        var oldBtnHtml = btn.val();

                        btn.val('Adding...');


                        var url = jQuery(this).attr('action');
                        var data = jQuery(this).serialize();

                        $.ajax({
                            url : url,
                            method : "post",
                            data : data
                        }).done(function (result) {
                            if(result.success == 1){

                                swal({
                                        title: "Success", 
                                        text: "Customer added successfully!", 
                                        type: "success"
                                    },
                                    function(){ 
                                        location.reload();
                                    }
                                );



                            }
                            else{
                                swal(
                                    'Error',
                                    result.errors.join("\n"),
                                    'error'
                                );
                            }

                            btn.val(oldBtnHtml);
                            btn.attr('disabled',false);

                        });
                    });
                });
            </script>
            
            <h2>ReliableSite Customers</h2>

            <form method="POST" action="<?php echo $pageLink; ?>">
                <table class="form" width="100%">
                    <tbody>
                        <tr>
                            <td class="fieldlabel">Search:</td>
                            <td class="fieldarea">
                                <select class="form-control input-250 input-inline" name="filterField">
                                    <option value="">--Please Select--</option>
                                    <option value="Username" <?php if($filterField=='Username'){ echo "selected"; } ?>>Username</option>
                                    <option value="EmailAddress" <?php if($filterField=='EmailAddress'){ echo "selected"; } ?>>Email Address</option>
                                </select>
                                <input type="text" class="form-control input-250 input-inline" name="search" autocomplete="off" value="<?php echo $search ?>">
                                
                                <input type="submit" value="Search" class="btn btn-primary">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
            
            
            
            <?php
            
            
            if(empty($errors)){
                ?>
                
                
                <table class="datatable" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>WHMCS User</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if(count($customersData) > 0){
                                $no = 1;
                                foreach($customersData as $customerData){
                                    ?>
                                    <tr>
                                        <td class="text-right"><?php echo $no; ?></td>
                                        <td class="text-center"><?php echo $customerData['rsUserName']; ?></td>
                                        <td class="text-center"><?php echo $customerData['rsEmailAddress']; ?></td>
                                        <td class="text-center">
                                            <?php 
                                                if(!empty($customerData['clientId'])){
                                                    echo '<a href="clientssummary.php?userid='.$customerData['clientId'].'">'.$customerData['clientName'].' (#'.$customerData['clientId'].')</a>'; 
                                                }
                                                ?>
                                        </td>
                                        <td class="text-center">
                                            <?php echo '<a href="'.$modulelink.'&action=managecustomer&rsuid='.$customerData['rsUserId'].'" class="btn btn-primary btn-sm">'.$LANG['manageServer.Label'].'</a>'; ?>
                                            <?php echo '<a href="javascript:deleteUser('.$customerData['rsUserId'].',\''.$customerData['rsUserName'].'\')" class="btn btn-primary btn-sm">Delete</a>'; ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $no++;
                                }
                            }
                            else{
                                ?>
                                    <tr>
                                        <td class="text-center" colspan="5">No Data Found</td>
                                    </tr>    
                                <?php    
                            }
                        ?>

                    </tbody>    
                </table>
                <ul class="pager">
                    <li class="previous"><a href="<?php echo $pageLink; ?>&page=<?php echo $prevPage; ?>">« Previous Page</a></li>
                    <li class=""><a href="javascript:void();">Page <?php echo $page; ?></a></li>
                    <li class="next"><a href="<?php echo $pageLink; ?>&page=<?php echo $nextPage; ?>">Next Page »</a></li>
                </ul>
                <!-- Confirm Box -->
                <div class="modal fade" id="deleteUserConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserConfirmModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content panel panel-primary">
                            <div class="modal-header panel-heading">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="deleteUserConfirmLabel">Are you sure?</h4>
                            </div>
                            <div class="modal-body panel-body">
                                Are you sure you want to delete customer?
                            </div>
                            <div class="modal-footer panel-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="btnDeleteUser">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        jQuery("button#btnDeleteUser").click(function(e){

                            url = "<?php echo $modulelink ?>&action=deletecustomer";
                            var btn = jQuery(this);

                            btn.attr('disabled',true);
                            var oldBtnHtml = btn.val();

                            btn.val('Deleting...');

                            var rsUserId = btn.attr('data-rsuserid');
                            var rsUsername = btn.attr('data-rsusername');

                            $.ajax({
                                url : url,
                                method : "post",
                                data : {rsUserId : rsUserId, rsUsername : rsUsername}
                            }).done(function (result) {
                                if(result.success == 1){

                                    swal({
                                            title: "Success", 
                                            text: "User deleted successfully!", 
                                            type: "success"
                                        },
                                        function(){ 
                                            location.reload();
                                        }
                                    );



                                }
                                else{
                                    swal(
                                        'Error',
                                        result.errors.join("\n"),
                                        'error'
                                    );
                                }

                                btn.val(oldBtnHtml);
                                btn.attr('disabled',false);

                                $('#deleteUserConfirmModal').modal('hide');

                            });
                        });
                    });
                    function deleteUser(rsUserId, rsUsername){
                        jQuery("button#btnDeleteUser").attr('data-rsuserid', rsUserId);
                        jQuery("button#btnDeleteUser").attr('data-rsusername', rsUsername);
                        jQuery('#deleteUserConfirmModal').modal('show');
                    }    
                </script>
                <?php
            }
            else{
                ?>
                <div class="alert alert-danger"><?php echo $errors[0]; ?></div>
                <?php
            }
            
        }
        catch (Exception $e){
            logActivity("ReliableSite: Error in {$e->getMessage()} ");
        }
    }
    
    public function managecustomer($vars){
        try{
            global $CONFIG;
            // Get common module parameters
            $systemUrl = $CONFIG['SystemURL'];
            $modulelink = $vars['modulelink'];
            $LANG = $vars['_lang'];
            $errors = [];
            
            $page = 1;
            
            $rsUserId = isset($_REQUEST['rsuid']) ? $_REQUEST['rsuid'] : '';
            $filterField = 'UserId';
            
            /*get all customer*/
            $apiObj = rs_getAPIObj();
            $getCustomerResp = $apiObj->getCustomers($page, $rsUserId, $filterField);
            
            if($getCustomerResp['success'] != true){
                $errors = $getCustomerResp['errors'];
            }
            else if(isset($getCustomerResp['data'][0])){
               $customer = $getCustomerResp['data'][0];
               
               /*get current customer is linked or not*/
               $isAssigned = false;
              
               $assignedUser = Capsule::table("mod_reliablesite_customers")
                        ->select('rsUserId', 'tblclients.firstname', 'tblclients.lastname', 'tblclients.email')
                        ->join('tblclients', 'tblclients.id','=', 'mod_reliablesite_customers.clientId')
                            ->where('rsUserId',$customer['userId'])
                            ->first();

                if($assignedUser){
                    $isAssigned = true;
                }

                if($isAssigned == false){
                    /*get customer list which is not linked up*/
                    $whmcsUsers = Capsule::table("tblclients")->select('tblclients.id',
                        'tblclients.firstname',
                        'tblclients.lastname',
                        'tblclients.email')->whereNotIn('tblclients.id',function($query) {
                            $query->select('clientId')->from('mod_reliablesite_customers');
                        })
                        ->orderBy('tblclients.firstname')
                        ->get();
                }       
               
            }
            else{
                $errors[] = "Customer not found";
            }
            
            
            ?>
            <p>
                <a href="<?php echo $modulelink ?>" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i>
                    <?php echo $LANG['backToHome.Label']; ?>
                </a>
                <a href="<?php echo $modulelink ?>&action=customers" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i>
                    Back to Customers
                </a>
            </p>
            <?php
            if(empty($errors)){
                ?>
                <h2>Update Customer (ReliableSite)</h2>
                <form id="rsUpdateCustomer" method="post" action="<?php echo $modulelink ?>&action=updatecustomer">
                    <table class="form" width="100%">
                        <tbody>
                            <tr>
                                <td class="fieldlabel">User Name</td>
                                <td class="fieldarea">
                                    <input type="text" class="form-control input-250 input-inline" name="username" value="<?php echo $customer['userName']; ?>" readonly>
                                </td>
                            </tr>
                            <tr>
                                <td class="fieldlabel">Email</td>
                                <td class="fieldarea">
                                    <input type="text" class="form-control input-250 input-inline" name="email" value="<?php echo $customer['emailAddress']; ?>">
                                </td>
                            </tr>
                            <tr>
                                <td class="fieldlabel"><?php echo $LANG['password.Label']; ?></td>
                                <td class="fieldarea">
                                    <input type="text" class="form-control input-250 input-inline" name="password" id="inputPassword" autocomplete="off" value="">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="btn-container">
                        <input type="submit" id="btnUpdateCusomer" value="Update Customer" class="btn btn-primary">
                    </div>
                </form>
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        jQuery("form#rsUpdateCustomer").submit(function(e){
                            e.preventDefault();

                            var btn = jQuery("form#rsUpdateCustomer input#btnUpdateCusomer");

                            btn.attr('disabled',true);
                            var oldBtnHtml = btn.val();

                            btn.val('Updating...');


                            var url = jQuery(this).attr('action');
                            var data = jQuery(this).serialize();

                            $.ajax({
                                url : url,
                                method : "post",
                                data : data
                            }).done(function (result) {
                                if(result.success == 1){
                                    swal({
                                            title: "Success", 
                                            text: "Customer updated successfully!", 
                                            type: "success"
                                        }
                                    );
                                }
                                else{
                                    swal(
                                        'Error',
                                        result.errors.join("\n"),
                                        'error'
                                    );
                                }

                                btn.val(oldBtnHtml);
                                btn.attr('disabled',false);

                            });
                        });
                    });
                </script>
                <h2>Assign/Unassign Customer</h2>
                <form id="assignCustomer" method="post" action="<?php echo $modulelink ?>&action=assigncustomer">
                    <table class="form" width="100%">
                        <tbody>
                            <tr>
                                <td class="fieldlabel">ReliableSite User Name</td>
                                <td class="fieldarea">
                                    <input type="hidden" name="rsUserId" value="<?php echo $customer['userId']; ?>" readonly>
                                    <input type="text" class="form-control input-250 input-inline" name="rsUserName" value="<?php echo $customer['userName']; ?>" readonly>
                                </td>
                            </tr>
                            <tr>
                                <td class="fieldlabel">WHMCS Customer</td>
                                <td class="fieldarea">
                                    <?php if($isAssigned == true){
                                        ?>
                                        <input type="text" class="form-control input-250 input-inline" name="username" value="<?php echo "{$assignedUser->firstname} {$assignedUser->lastname} ($assignedUser->email)" ?>" readonly>
                                        <?php

                                    }
                                    else{
                                        ?>
                                        <script src="<?php echo $systemUrl ?>/modules/addons/rspanel/templates/js/select2.full.min.js"></script>
                                        <link rel="stylesheet" type="text/css" href="<?php echo $systemUrl ?>/modules/addons/rspanel/templates/css/select2.min.css">
                                        <select class="form-control input-250" name="clientId">
                                            <option value="">Please Select</option>
                                            <?php
                                                foreach ($whmcsUsers as $whmcsUser){
                                                    echo "<option value='{$whmcsUser->id}'>{$whmcsUser->firstname} {$whmcsUser->lastname} ($whmcsUser->email)</option>";
                                                }
                                            ?>
                                        </select>
                                        <script>
                                            jQuery(document).ready(function(){
                                                jQuery('select[name="clientId"]').select2();
                                            })
                                        </script>
                                        <?php    
                                        }
                                        ?>
                                </td>
                            </tr>
                           
                        </tbody>
                    </table>
                    <div class="btn-container">
                    <?php if($isAssigned == true){
                        ?>
                        <input type="button" onclick="unAssignCustomer(this)";  value="Unassign Customer" class="btn btn-primary">
                        <?php
                    }
                    else{
                        ?>
                        <input type="submit" id="btnAssignCustomer" value="Assign Customer" class="btn btn-primary">
                        <?php
                    }
                    ?>  
                    </div>
                </form>
                <!-- Confirm Box -->
                <div class="modal fade" id="unassignConfirmModal" tabindex="-1" role="dialog" aria-labelledby="unassignConfirmModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content panel panel-primary">
                            <div class="modal-header panel-heading">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title" id="unassignConfirmLabel">Are you sure?</h4>
                            </div>
                            <div class="modal-body panel-body">
                                Are you sure you want to un-assign customer?
                            </div>
                            <div class="modal-footer panel-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="btnUnassignCustomer">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        jQuery("form#assignCustomer input#btnAssignCustomer").click(function(e){
                            e.preventDefault();

                            var btn = jQuery(this);

                            btn.attr('disabled',true);
                            var oldBtnHtml = btn.val();

                            btn.val('Updating...');


                            var url = jQuery("form#assignCustomer").attr('action');
                            var data = jQuery("form#assignCustomer").serialize();

                            $.ajax({
                                url : url,
                                method : "post",
                                data : data
                            }).done(function (result) {
                                if(result.success == 1){
                                    swal({
                                            title: "Success", 
                                            text: "Customer Linked successfully!",
                                            type: "success"
                                        },
                                        function(){
                                            location.reload();
                                        }
                                                
                                    );
                                }
                                else{
                                    swal(
                                        'Error',
                                        result.errors.join("\n"),
                                        'error'
                                    );
                                }

                                btn.val(oldBtnHtml);
                                btn.attr('disabled',false);

                            });
                        });

                        jQuery("button#btnUnassignCustomer").click(function(e){

                            url = "<?php echo $modulelink ?>&action=unassigncustomer";
                            var btn = jQuery(this);

                            btn.attr('disabled',true);
                            var oldBtnHtml = btn.val();

                            btn.val('UnAssigning...');

                            var data = jQuery("form#assignCustomer").serialize();

                            $.ajax({
                                url : url,
                                method : "post",
                                data : data
                            }).done(function (result) {
                                if(result.success == 1){

                                    swal({
                                            title: "Success", 
                                            text: "Customer un-assigned successfully!", 
                                            type: "success"
                                        },
                                        function(){ 
                                            location.reload();
                                        }
                                    );



                                }
                                else{
                                    swal(
                                        'Error',
                                        result.errors.join("\n"),
                                        'error'
                                    );
                                }

                                btn.val(oldBtnHtml);
                                btn.attr('disabled',false);

                                $('#unassignConfirmModal').modal('hide');

                            });
                        });

                    });
                    function unAssignCustomer(e){
                        jQuery('#unassignConfirmModal').modal('show');
                    }
                </script>
                <?php
            }
            else{
                ?>
                <div class="alert alert-danger"><?php echo $errors[0]; ?></div>
                <?php
            }
        }
        catch (Exception $e){
            logActivity("ReliableSite: Error in {$e->getMessage()} ");
        }
    }

    public function servers($vars)
    {
        
        try{
        
            // Get common module parameters
            $modulelink = $vars['modulelink'];
            $LANG = $vars['_lang'];
            
            
            $servers = [];
            $rsServerIds = [];
            $serversData = [];
            
            $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
            $showReassignServer = isset($_REQUEST['showReassignServer']) ? $_REQUEST['showReassignServer'] : "true";
            $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : '';
            $filterField = isset($_REQUEST['filterField']) ? $_REQUEST['filterField'] : '';
 
            $pageLink = $modulelink.'&action=servers';
            if(!empty($search) && !empty($filterField)){
                $pageLink .= '&filterField='.$filterField.'&search='.$search;
            }

            if(!empty($showReassignServer)){
                $pageLink .= '&showReassignServer='.$showReassignServer;
            }
            
            $prevPage = $page - 1 ;
            if($page == 1){
                $prevPage = 1;
            }
            $nextPage = $page + 1 ;
            
            /*get all servers*/
            $apiObj = rs_getAPIObj();
            $getServersResp = $apiObj->getServers($page, $showReassignServer, $search, $filterField);
            
            if($getServersResp['success'] != true){
                $errors = $getServersResp['errors'];
            }
            else{
                $servers = $getServersResp['data'];
            }
            
            foreach($servers as $server){
                $rsServerId = $server['serverId'];
                $serversData[$rsServerId] = [
                    'rsServerId' => $rsServerId,
                    'rsServerLabel' => $server['serverLabel'],
                    'rsStatus' => $server['status'],
                    'userId' => 0,
                    'userFirstName' => '',
                    'userLastName' => '',
                    'packageId' => 0,
                    'packageName' => '-',
                    'hostingId' => 0,
                ];

                $rsServerIds[] = $rsServerId;
            }

            /*get Reliable site customer list*/
            $rsOrders = Capsule::table('tblhosting AS a')
                ->select(
                        'a.id as hostingId',
                        'a.packageid',
                        'a.regdate', 
                        'c.name as packageName', 
                        'b.rsServerId',
                        'b.rsServerName',
                        'd.id as clientId',
                        'd.firstname',
                        'd.lastname',                        
                        'd.email',
                        'e.rsUserName'
                    )
                ->leftJoin('mod_reliablesite_orders as b', 'b.hostingId','=', 'a.id')     
                ->leftJoin('tblproducts AS c','c.id','=','a.packageid')
                ->leftJoin('tblclients AS d','d.id','=','a.userId')
                ->leftJoin('mod_reliablesite_customers AS e','e.clientId','=','d.id')    
                ->where('c.servertype','autorelease')
                ->whereIn('b.rsServerId', $rsServerIds)    
                ->orderBy('a.regdate', 'desc')    
                ->get();
            
            /*add order information if available in server data*/
            foreach ($rsOrders as $rsOrder){
                
                $rsServerId = $rsOrder->rsServerId;
                if(isset($serversData[$rsServerId])){
                    $serversData[$rsServerId]['userId'] = $rsOrder->clientId;
                    $serversData[$rsServerId]['userFirstName'] = $rsOrder->firstname;
                    $serversData[$rsServerId]['userLastName'] = $rsOrder->lastname;
                    $serversData[$rsServerId]['packageId'] = $rsOrder->packageid;
                    $serversData[$rsServerId]['packageName'] = $rsOrder->packageName;
                    $serversData[$rsServerId]['hostingId'] = $rsOrder->hostingId;
                }
            }

            ?>

            <p>
                <a href="<?php echo $modulelink ?>" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i>
                    <?php echo $LANG['backToHome.Label']; ?>
                </a>
            </p>
            
            <?php
               if(!empty($errors)){
                    foreach($errors as $error){
                        ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php
                    }
               }
               else{
                ?>

                 
                <h2>ReliableSite Servers</h2>
                <form method="POST" action="<?php echo $pageLink; ?>">
                    <table class="form" width="100%">
                        <tbody>
                            <tr>
                                <td class="fieldlabel">Search By:</td>
                                <td class="fieldarea">
                                    <select class="form-control input-250 input-inline" name="filterField">
                                        <option value="">--Please Select--</option>
                                        <option value="ServerLabel" <?php if($filterField=='ServerLabel'){ echo "selected"; } ?>>Server Name</option>
                                    </select>
                                    <input type="text" class="form-control input-250 input-inline" name="search" autocomplete="off" value="<?php echo $search ?>">
                                    <label class="font-weight-normal">Show Reassigned Servers:</label>
                                    <select class="form-control input-250 input-inline" name="showReassignServer">
                                        <option value="true" <?php if($showReassignServer=='true'){ echo "selected"; } ?>>Yes</option>
                                        <option value="false" <?php if($showReassignServer=='false'){ echo "selected"; } ?>>No</option>
                                    </select>
                                    <input type="submit" value="Search" class="btn btn-primary">
                                    
                                </td>
                                
                            </tr>
                        </tbody>
                    </table>
                </form>
                <table class="datatable" width="100%">
                    <thead>
                        <tr>
                            <th><?php echo $LANG['serverName.Label']; ?></th>
                            <th>Order#</th>
                            <th><?php echo $LANG['productName.Label']; ?></th>
                            <th><?php echo $LANG['customer.Label']; ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            
                            foreach($serversData as $server){
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo  $server['rsServerLabel'].' (' .$server['rsServerId'].')'; ?></td>
                                    <td class="text-right">
                                        <?php if(!empty($server['hostingId'])){ ?>
                                            <a href="clientsservices.php?userid=<?php echo $server['userId'] ?>&id=<?php echo $server['hostingId'] ?>" target="_blank"><?php echo $server['hostingId']; ?></a>
                                        <?php } ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if(!empty($server['packageId'])){ ?>
                                        <a href="configproducts.php?action=edit&id=<?php echo $server['packageId'] ?>" target="_blank"><?php echo $server['packageName']; ?></a>
                                        <?php } ?>
                                    </td>
                                    
                                    <td class="text-center">
                                        <?php if(!empty($server['userId'])){ ?>
                                        <a href="clientssummary.php?userid=<?php echo $server['userId']; ?>" target="_blank"><?php echo $server['userFirstName'].' '.$server['userLastName']; ?></a>
                                        <?php } ?>
                                    </td>
                                    
                                    
                                    <td class="text-center">
                                        <?php 
                                        echo '<a href="'.$modulelink.'&action=manageserver&rsid='.$server['rsServerId'].'" class="btn btn-primary btn-sm">'.$LANG['manageServer.Label'].'</a>';
                                        
                                        ?>
                                    </td>
                                </tr>
                                <?php
                            }
                        ?>

                    </tbody>    
                </table>
                <ul class="pager">
                    <li class="previous"><a href="<?php echo $pageLink?>&page=<?php echo $prevPage; ?>">« Previous Page</a></li>
                    <li class=""><a href="javascript:void();">Page <?php echo $page; ?></a></li>
                    <li class="next"><a href="<?php echo $pageLink; ?>&page=<?php echo $nextPage; ?>">Next Page »</a></li>
                </ul>
            <?php
            }
        }
        catch (Exception $e){
            logActivity("ReliableSite: Error in {$e->getMessage()} ");
        }
    }
    
    public function nullroutes($vars)
    {   
        try{
            
            // Get common module parameters
            $modulelink = $vars['modulelink'];
            $LANG = $vars['_lang'];
            $errors = [];
            $nullRoutes = [];
            
            $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
            
            $searchIP = isset($_REQUEST['searchIP']) ? $_REQUEST['searchIP'] : '';
            $onlyActive = isset($_REQUEST['onlyActive']) ? $_REQUEST['onlyActive'] : "true";
            
            $pageLink = $modulelink.'&action=nullroutes';
            if(!empty($searchIP)){
                $pageLink .= '&searchIP='.$searchIP;
            }

            if(!empty($onlyActive)){
                $pageLink .= '&onlyActive='.$onlyActive;
            }

            $prevPage = $page - 1 ;
            if($page == 1){
                $prevPage = 1;
            }
            $nextPage = $page + 1 ;
            
            /*get all routes*/
            $apiObj = rs_getAPIObj();
            $getNullRouteResp = $apiObj->getNullRoutes($page, $searchIP, $onlyActive);
            
            if($getNullRouteResp['success'] != true){
                $errors = $getNullRouteResp['errors'];
            }
            else{
                $nullRoutes = $getNullRouteResp['data'];
            }
            
            
            ?>
            <p>
                <a href="<?php echo $modulelink ?>" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i>
                    <?php echo $LANG['backToHome.Label']; ?>
                </a>
            </p>
            <h2>Add Null Route</h2>

            <form id="rsAddNullRoute" method="GET" action="<?php echo $modulelink ?>&action=addnullroute">
                <table class="form" width="100%">
                    <tbody>
                        <tr>
                            <td class="fieldlabel">IP Address:</td>
                            <td class="fieldarea">
                                <input type="text" class="form-control input-250 input-inline" name="ipAddress" autocomplete="off" value="">
                            </td>
                            <td class="fieldlabel">Remove After:</td>
                            <td class="fieldarea input-150">
                                <input type="text" class="form-control" name="removeTime" value="">
                            </td>    
                            <td class="fieldarea">
                                <select class="form-control input-250" name="removeTimeType">
                                    <option value="">Please Select</option>
                                    <option value="minute">Minute(s)</option>
                                    <option value="hour">Hour(s)</option>
                                    <option value="day">Day(s)</option>
                                    <option value="month">Month(s)</option>
                                </select>
                            </td>
                            <td class="fieldlabel">Allow Customer to Remove:</td>
                            <td class="fieldarea">
                                <select class="form-control input-250" name="resellerLock">
                                    <option value="">Please Select</option>
                                    <option value="true">Yes</option>
                                    <option value="false">No</option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="btn-container">
                    <input type="submit" id="btnAddNullRoute" value="Add Null Route" class="btn btn-primary">
                </div>
            </form>
            
            
            
            <?php
            
            
            if(empty($errors)){
                ?>
                
                <h2>Manage Null Route</h2>
                <form method="POST" action="<?php echo $pageLink; ?>">
                    <table class="form" width="100%">
                        <tbody>
                            <tr>
                                <td class="fieldlabel">Search By:</td>
                                <td class="fieldarea">
                                    <label class="font-weight-normal">IP:</label>
                                    <input type="text" class="form-control input-250 input-inline" name="searchIP" autocomplete="off" value="<?php echo $searchIP ?>">
                                    <label class="font-weight-normal">Only Active?:</label>
                                    <select class="form-control input-250 input-inline" name="onlyActive">
                                        <option value="true" <?php if($onlyActive=='true'){ echo "selected"; } ?>>Yes</option>
                                        <option value="false" <?php if($onlyActive=='false'){ echo "selected"; } ?>>No</option>
                                    </select>
                                    <input type="submit" value="Search" class="btn btn-primary">
                                </td>
                                
                            </tr>
                        </tbody>
                    </table>
                </form>
                <table class="datatable" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>IP Address</th>
                            <th>Server</th>
                            <th>Active</th>
                            <th>Description</th>
                            <th>Add Time</th></th>
                            <th>Remove Time</th>
                            <th>Scheduled Remove</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $no = 1;
                            foreach($nullRoutes as $nullRoute){
                                ?>
                                <tr>
                                    <td class="text-right"><?php echo $no; ?></td>
                                    <td class="text-center"><?php echo $nullRoute['nullRouteIP']; ?></td>
                                    <td class="text-center"><?php echo $nullRoute['server']; ?></td>
                                    <td class="text-center"><?php echo ($nullRoute['nullRouteActive'] == 1) ? '<span class="label active">Yes</span>' : '<span class="label inactive">No</span>'; ?></td>
                                    <td class="text-center"><?php echo $nullRoute['nullRouteDescription']; ?></td>
                                    <td class="text-center">
                                        <?php 
                                        if(!empty($nullRoute['nullRouteAddTime'])){
                                            echo fromMySQLDate($nullRoute['nullRouteAddTime'], true, true); 
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        if(!empty($nullRoute['nullRouteRemoveTime'])){
                                            echo fromMySQLDate($nullRoute['nullRouteRemoveTime'], true, true); 
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                        if(!empty($nullRoute['scheduledRemove'])){
                                            echo fromMySQLDate($nullRoute['scheduledRemove'], true, true); 
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                            echo  '<input type="button" value="Remove" onclick="removeNullRoute(this,\''.$nullRoute['nullRouteIP'].'\')" class="btn btn-primary">';
                                        ?>
                                    </td>
                                </tr>
                                <?php
                                $no++;
                            }
                        ?>

                    </tbody>    
                </table>
                <ul class="pager">
                    <li class="previous"><a href="<?php echo $pageLink; ?>&page=<?php echo $prevPage; ?>">« Previous Page</a></li>
                    <li class=""><a href="javascript:void();">Page <?php echo $page; ?></a></li>
                    <li class="next"><a href="<?php echo $pageLink; ?>&page=<?php echo $nextPage; ?>">Next Page »</a></li>
                </ul>
                
                <script type="text/javascript">
                    function removeNullRoute(e, ip){
                        if (confirm("Are you sure you want to remove this null route?") == true) {
                            url = "<?php echo $modulelink ?>&action=removenullroute";

                            var btn = jQuery(e);

                            btn.attr('disabled',true);
                            var oldBtnHtml = btn.val();

                            btn.val('Removing...');

                            $.ajax({
                                url : url,
                                method : "post",
                                data : {ip : ip}
                            }).done(function (result) {
                                if(result.success == 1){

                                    swal({
                                            title: "Success", 
                                            text: "Null Route removed successfully!", 
                                            type: "success"
                                        },
                                        function(){ 
                                            location.reload();
                                        }
                                    );
                                }
                                else{
                                    swal(
                                        'Error',
                                        result.errors.join("\n"),
                                        'error'
                                    );
                                }

                                btn.val(oldBtnHtml);
                                btn.attr('disabled',false);

                            });


                        }
                    }
                    jQuery(document).ready(function(){
                        jQuery("form#rsAddNullRoute").submit(function(e){
                            e.preventDefault();

                            var btn = jQuery("form#rsAddNullRoute input#btnAddNullRoute");

                            btn.attr('disabled',true);
                            var oldBtnHtml = btn.val();

                            btn.val('Adding...');


                            var url = jQuery(this).attr('action');
                            var data = jQuery(this).serialize();

                            $.ajax({
                                url : url,
                                method : "post",
                                data : data
                            }).done(function (result) {
                                if(result.success == 1){

                                    swal({
                                            title: "Success", 
                                            text: "Successfully null routed!", 
                                            type: "success"
                                        },
                                        function(){ 
                                            location.reload();
                                        }
                                    );



                                }
                                else{
                                    swal(
                                        'Error',
                                        result.errors.join("\n"),
                                        'error'
                                    );
                                }

                                btn.val(oldBtnHtml);
                                btn.attr('disabled',false);

                            });
                        });
                    });
                </script>
                <?php
            }
            else{
                ?>
                <div class="alert alert-danger"><?php echo $errors[0]; ?></div>
                <?php
            }
            
        }
        catch (Exception $e){
            logActivity("ReliableSite: Error in {$e->getMessage()} ");
        }
    }
    
    public function ddoshistory($vars)
    {   
        try{
            
            // Get common module parameters
            $modulelink = $vars['modulelink'];
            $LANG = $vars['_lang'];
            $errors = [];
            $ddosAttacks = [];
            
            $ipAddress = isset($_REQUEST['ipAddress']) ? $_REQUEST['ipAddress'] : '';
            $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
            
            $prevPage = $page - 1 ;
            if($page == 1){
                $prevPage = 1;
            }
            $nextPage = $page + 1 ;
            
            /*get all DDos Attacks*/
            $apiObj = rs_getAPIObj();
            $getDDosAttacksResp = $apiObj->GetDDoSAttacks($page, $ipAddress);
            
            if($getDDosAttacksResp['success'] != true){
                $errors = $getDDosAttacksResp['errors'];
            }
            else{
                $ddosAttacks = $getDDosAttacksResp['data'];
            }
            
            
            ?>
            <p>
                <a href="<?php echo $modulelink ?>" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i>
                    <?php echo $LANG['backToHome.Label']; ?>
                </a>
            </p>
            <h2>DDos History</h2>

            <form method="post" action="<?php echo $modulelink ?>&action=ddoshistory">
                <table class="form" width="100%">
                    <tbody>
                        <tr>
                            <td class="fieldlabel">IP Address:</td>
                            <td class="fieldarea">
                                <input type="text" class="form-control input-250 input-inline" name="ipAddress" autocomplete="off" value="<?php echo $ipAddress ?>">
                                
                                <input type="submit" id="searchByIP" value="Search" class="btn btn-primary">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </form>
            
            
            
            <?php
            
            
            if(empty($errors)){
                ?>
                
                <h2>DDos History</h2>
                <table class="datatable" width="100%">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Target IP</th>
                            <th>Max PPS</th>
                            <th>Max BPS</th>
                            <th>Date Started</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if(count($ddosAttacks) > 0){
                                $no = 1;
                                foreach($ddosAttacks as $ddosAttack){
                                    ?>
                                    <tr>
                                        <td class="text-right"><?php echo $no; ?></td>
                                        <td class="text-center"><?php echo $ddosAttack['targetIp']; ?></td>
                                        <td class="text-center"><?php echo $ddosAttack['maxPps']; ?></td>
                                        <td class="text-center"><?php echo $ddosAttack['maxBps']; ?></td>
                                        <td class="text-center"><?php echo date("m/d/Y H:i:s a", strtotime($ddosAttack['startTimeStamp'])); ?></td>
                                    </tr>
                                    <?php
                                    $no++;
                                }
                            }
                            else{
                                ?>
                                    <tr>
                                        <td class="text-center" colspan="5">No Data Found</td>
                                    </tr>    
                                <?php    
                            }
                        ?>

                    </tbody>    
                </table>
                <ul class="pager">
                    <li class="previous"><a href="<?php echo $modulelink; ?>&action=ddoshistory&page=<?php echo $prevPage; ?>">« Previous Page</a></li>
                    <li class=""><a href="javascript:void();">Page <?php echo $page; ?></a></li>
                    <li class="next"><a href="<?php echo $modulelink; ?>&action=ddoshistory&page=<?php echo $nextPage; ?>">Next Page »</a></li>
                </ul>
                <?php
            }
            else{
                ?>
                <div class="alert alert-danger"><?php echo $errors[0]; ?></div>
                <?php
            }
            
        }
        catch (Exception $e){
            logActivity("ReliableSite: Error in {$e->getMessage()} ");
        }
    }
    
    function manageserver($vars){
        try{
            
            global $CONFIG;
            // Get common module parameters
            $systemUrl = $CONFIG['SystemURL'];
            $modulelink = $vars['modulelink'];
            $LANG = $vars['_lang'];
            $errors = [];
            
            $rsServerId = isset($_REQUEST['rsid']) ? $_REQUEST['rsid'] : 0;
            $serverDetail = [];
            $ipAddressList = [];
           
            //get server details
            $apiObj = rs_getAPIObj();
            $getServerResp = $apiObj->getServerDetails($rsServerId);

            if($getServerResp['success'] == true){
                $serverDetail = $getServerResp['data']['server'];
                $ipAddressList = $getServerResp['data']['ipAddressList'];


                /*get current server is linked with order or not*/
                $isAssigned = false;
              
                $assignedOrder = Capsule::table("mod_reliablesite_orders")
                        ->select('tblclients.email', 'mod_reliablesite_orders.hostingId','mod_reliablesite_orders.rsServerId')
                        ->join('tblhosting', 'tblhosting.id','=','mod_reliablesite_orders.hostingId')
                        ->join('tblclients','tblhosting.userId','=','tblclients.id')
                        ->where('rsServerId', $rsServerId)
                        ->first();

                if($assignedOrder){
                    $isAssigned = true;
                }

                if($isAssigned == false){
                    /*get customer list which is not linked up*/
                   /*get Reliable site order list*/
                    $unAssignedOrders = Capsule::table('tblhosting AS a')
                    ->select(
                            'a.id as hostingId',
                            'a.regdate',
                            'd.firstname',
                            'd.lastname',                        
                            'd.email'
                        )
                        
                    ->leftJoin('tblproducts AS c','c.id','=','a.packageid')
                    ->leftJoin('tblclients AS d','d.id','=','a.userId')
                    ->join('mod_reliablesite_customers AS e','e.clientId','=','d.id')
                    ->whereNotIn('a.id',function($query) {
                        $query->select('mod_reliablesite_orders.hostingId')->from('mod_reliablesite_orders');
                    }) 
                    ->where('c.servertype','autorelease')
                    ->orderBy('a.regdate', 'desc')    
                    ->get();
                }

            }
            else{
                $errors = $getServerResp['errors'];
            }
            
        
            
            ?>
            <p>
                <a href="<?php echo $modulelink ?>" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i>
                    <?php echo $LANG['backToHome.Label']; ?>
                </a>
                <a href="<?php echo $modulelink ?>&action=servers" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i>
                    Back to Servers
                </a>
            </p>
            
            
            <?php
               if(!empty($errors)){
                    foreach($errors as $error){
                        ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php
                    }
               }
               else{
            ?>
            <h2>Assign to WHMCS Product</h2>
            <div class="alert alert-info">
            Assign this server to an existing WHMCS product using the product ID. The product ID can be found in the URL when selecting a specific product/service under Clients > Products/Services. Only products/services with users linked to WHMCS users will be listed. To link users, use the Manage Users menu.
            </div>    
            <form id="rsAssinServer" method="post" action="<?php echo $modulelink ?>&action=assignserver">
                <table class="form" width="100%">
                    <tbody>
                        <tr>
                            <td class="fieldlabel"><?php echo $LANG['server.Label']; ?>:</td>
                            <td class="fieldarea">
                                <input  class="form-control input-250" type="hidden" name="serverId" value="<?php echo $rsServerId; ?>">
                                <input  class="form-control input-250" type="text" name="serverName" value="<?php echo $serverDetail['serverLabel']; ?>" readonly>
                            </td>
                            <td class="fieldlabel">WHMCS Product/Service ID:</td>
                            <td class="fieldarea">
                            <?php 
                            if($isAssigned == true){
                                ?>
                                <input type="text" class="form-control input-250 input-inline" name="order" value="<?php echo "{$assignedOrder->email} (#{$assignedOrder->hostingId})" ?>" readonly>
                                <?php

                            }
                            else{
                                ?>
                                
                                <script src="<?php echo $systemUrl ?>/modules/addons/rspanel/templates/js/select2.full.min.js"></script>
                                <link rel="stylesheet" type="text/css" href="<?php echo $systemUrl ?>/modules/addons/rspanel/templates/css/select2.min.css">
                               
                                <select class="form-control input-250" name="hostingId">
                                    <option value="">Please Select</option>
                                    <?php
                                        foreach ($unAssignedOrders as $rsOrder){
                                            
                                            $selected = '';
                                            if($rsOrder->hostingId == $vars['sid']){
                                                $selected = 'selected';
                                            }
                                            echo "<option value='{$rsOrder->hostingId}' {$selected}>{$rsOrder->email} (#{$rsOrder->hostingId})</option>";
                                        }
                                    ?>
                                </select>
                                <script>
                                    jQuery(document).ready(function(){
                                        jQuery('select[name="hostingId"]').select2();
                                    })
                                </script>
                                <?php
                            }
                            ?>    
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="btn-container">
                <?php if($isAssigned == true){
                        ?>
                        <input type="button" onclick="unAssignServer(<?php echo $assignedOrder->rsServerId ?>)";  value="Unassign Server" class="btn btn-primary">
                        <?php
                    }
                    else{
                    ?>    
                        <input type="submit" id="btnAssignServer" value="<?php echo $LANG['assignServer.Label']; ?>" class="btn btn-primary">
                    <?php
                    }
                    ?>    

                </div>
            </form>
            <!-- Confirm Box -->
            <div class="modal fade" id="unassignConfirmModal" tabindex="-1" role="dialog" aria-labelledby="unassignConfirmModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content panel panel-primary">
                        <div class="modal-header panel-heading">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" id="unassignConfirmLabel">Are you sure?</h4>
                        </div>
                        <div class="modal-body panel-body">
                            Are you sure you want to un-assign server?
                        </div>
                        <div class="modal-footer panel-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="btnUnassignServer" data-hostingid="0">OK</button>
                        </div>
                    </div>
                </div>
            </div>
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    jQuery("form#rsAssinServer").submit(function(e){
                        e.preventDefault();

                        var btn = jQuery("form#rsAssinServer input#btnAssignServer");

                        btn.attr('disabled',true);
                        var oldBtnHtml = btn.val();

                        btn.val('Assigning...');


                        var url = jQuery(this).attr('action');
                        var data = jQuery(this).serialize();

                        $.ajax({
                            url : url,
                            method : "post",
                            data : data
                        }).done(function (result) {
                            if(result.success == 1){

                                swal({
                                        title: "Success", 
                                        text: "Server assigned successfully!", 
                                        type: "success"
                                    },
                                    function(){ 
                                        location.reload();
                                    }
                                );



                            }
                            else{
                                swal(
                                    'Error',
                                    result.errors.join("\n"),
                                    'error'
                                );
                            }

                            btn.val(oldBtnHtml);
                            btn.attr('disabled',false);

                        });
                    });

                    jQuery("button#btnUnassignServer").click(function(e){

                        url = "<?php echo $modulelink ?>&action=unassignserver";
                        var btn = jQuery(this);

                        btn.attr('disabled',true);
                        var oldBtnHtml = btn.val();

                        btn.val('UnAssigning...');

                        var rsid = btn.attr('data-serverid');

                        $.ajax({
                            url : url,
                            method : "post",
                            data : {rsid : rsid}
                        }).done(function (result) {
                            if(result.success == 1){

                                swal({
                                        title: "Success", 
                                        text: "Server un-assigned successfully!", 
                                        type: "success"
                                    },
                                    function(){ 
                                        location.reload();
                                    }
                                );



                            }
                            else{
                                swal(
                                    'Error',
                                    result.errors.join("\n"),
                                    'error'
                                );
                            }

                            btn.val(oldBtnHtml);
                            btn.attr('disabled',false);

                            $('#unassignConfirmModal').modal('hide');

                        });
                    });
                });

                function unAssignServer(rsServerId){
                  
                    jQuery("button#btnUnassignServer").attr('data-serverid', rsServerId);
                    jQuery('#unassignConfirmModal').modal('show');
                }
            </script>
            <h2>Server Information</h2>
            <table class="form" width="100%">
                <tbody>
                    <tr>
                        <td class="fieldlabel">Server Name</td>
                        <td class="fieldarea">
                            <?php echo $serverDetail['serverLabel'] ?>
                        </td>
                        <td class="fieldlabel">Data Center</td>
                        <td class="fieldarea">
                            <?php echo $serverDetail['dataCenterLabel']; ?>
                        </td>
                    </tr>

                    <tr>
                    <td class="fieldlabel">Processor</td>
                        <td class="fieldarea">
                            <?php echo $serverDetail['cpuName']; ?>
                        </td>
                        <td class="fieldlabel">Memory</td>
                        <td class="fieldarea">
                            <?php echo $serverDetail['memorySize'].' '.$serverDetail['memoryType']  ?>
                        </td>
                       
                        
                    </tr>

                    <tr>
                        <td class="fieldlabel">Operating System</td>
                        <td class="fieldarea">
                            <?php echo $serverDetail['operatingSystem'] ?> <a class="btn btn-primary btn-sm" href="<?php echo $modulelink; ?>&action=manageos&rsid=<?php echo $rsServerId ?>">Re-Install</a>
                        </td>
                        <td class="fieldlabel">Status</td>
                        <td class="fieldarea">
                            <?php echo $serverDetail['status'] ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <h2>Storage</h2>
            <table class="form" width="100%">
            <tr>
                        
                <td class="fieldlabel">Drive 1</td>
                <td class="fieldarea">
                    <?php echo $serverDetail['hardDrive1']; ?>
                </td>
                <td class="fieldlabel">Drive 2</td>
                    <td class="fieldarea">
                        <?php echo $serverDetail['hardDrive2']  ?>
                    </td>
                </tr>
                <tr>
                    
                    <td class="fieldlabel">Drive 3</td>
                    <td class="fieldarea">
                        <?php echo $serverDetail['hardDrive3']; ?>
                    </td>
                    <td class="fieldlabel">Drive 4</td>
                    <td class="fieldarea">
                        <?php echo $serverDetail['hardDrive4'];  ?>
                    </td>
                </tr>
                <tr>
                    <td class="fieldlabel">RAID</td>
                    <td class="fieldarea">
                        <?php echo $serverDetail['raidName'] ?>
                    </td>
                    <td class="fieldlabel">Primary NIC</td>
                    <td class="fieldarea">
                        <?php echo $serverDetail['primaryNIC']; ?>
                    </td>
                </tr>
            </table>    
            <div class="btn-container">
                <a href="<?php echo $modulelink.'&action=backupstorage&rsid='.$rsServerId ?>" class="btn btn-primary">
                    Manage Backup Storage
                </a>
            </div>
            
            
            <h2>Bandwidth Usage Summary - Last 30 Days </h2>
            <table class="form" width="100%">
                <tbody>
                    <tr>
                    <td class="fieldlabel">Port Speed</td>
                        <td class="fieldarea">
                            <?php echo $serverDetail['portSpeed'] ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="fieldlabel">Bandwidth Used</td>
                        <td class="fieldarea">
                            <?php echo $serverDetail['bandwidthUsed'] ?> GB
                        </td>
                        
                    </tr>
                    <tr>
                        <td class="fieldlabel">Full Speed Bandwidth</td>
                        <td class="fieldarea">
                            <?php echo $serverDetail['bandwidthAllowed'] ?> GB
                        </td>
                       
                    </tr>
                </tbody>
            </table>
            
            <div class="btn-container">                
                <a href="<?php echo $modulelink.'&action=bwstats&rsid='.$rsServerId ?>" class="btn btn-primary">
                    Bandwidth Graph
                </a>
            </div>
            <h2>Login Credentials</h2>
            <table class="form" width="100%">
                <tbody>
                    <tr>
                        <td class="fieldlabel">Username</td>
                        <td class="fieldarea">
                            <?php echo $serverDetail['username'] ?>
                        </td>
                       
                    </tr>
                    <tr>
                       
                        <td class="fieldlabel">Password</td>
                        <td class="fieldarea">
                            <?php echo $serverDetail['password']; ?>
                        </td>
                         
                    </tr>
                </tbody>
            </table>
            <?php 
            if(count($ipAddressList) > 0){
                
                ?>
                <h2>Server IP Assignment</h2>
                <table class="form" width="100%">
                    <tbody>
                        <?php
                        if($serverDetail['serverMac']){
                            ?>
                            <tr>
                                <td class="fieldlabel">Server MAC</td>
                                <td class="fieldarea">
                                    <?php echo $serverDetail['serverMac'] ?>
                                </td>

                            </tr>
                            <?php
                        }
                        ?>
                        <tr>
                            <td class="fieldlabel">IP Address</td>
                            <td class="fieldarea">
                                <select class="form-control select-inline" name="serverIP" id="serverIP" onchange="setIPAddressData(this)">
                                    <?php 
                                    foreach($ipAddressList as $item){
                                        echo '<option value="'.$item['ipDescription'].'" data-custommac="'.$item['customMac'].'" data-ipaddressid="'.$item['ipAddressId'].'">'.$item['ipDescription'].'</option>';
                                    }
                                    ?>
                                </select>
                                <a id="btnReverseDNS" class="btn btn-primary btn-sm" href="">Reverse DNS</a>
                            </td>

                        </tr>
                        
                        <?php
                        if($serverDetail['serverMac']){
                            ?>
                            <tr>
                                <td class="fieldlabel">MAC Address</td>
                                <td class="fieldarea">
                                    <input type="radio" name="macAddrType" id="macAddrTypeServer" value="server"> <label for="macAddrTypeServer">Use Server</label>
                                    <input type="radio" name="macAddrType" id="macAddrTypeCustom" value="custom"> <label for="macAddrTypeCustom">Use Custom</label>
                                </td>
                            </tr>
                            <tr id="customMACBox">
                                <td class="fieldlabel">Custom MAC</td>
                                <td class="fieldarea">
                                    <input type="text" class="form-control" name="customMAC" id="customMAC">
                                </td>

                            </tr>
                        
                            <tr>
                                <td class="fieldlabel"></td>
                                <td>
                                   <input type="button" class="btn btn-primary btn-sm" id="btnUpdateMAC" value="Update MAC">
                                </td>

                            </tr>
                        <?php
                        }
                        ?>    
                        <tr>
                            <td colspan="2"><strong>Total IPs: <?php echo count($ipAddressList) ?></strong></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                               
                                   
                    <table class="table table-list dataTable no-footer">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">IP</th>
                            <th class="text-center">Gateway</th>
                            <th class="text-center">Subnet</th>
                            <th class="text-center">Custom MAC</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach($ipAddressList as $key=>$item){
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $key + 1 ?></td>
                                    <td class="text-center"><?php echo $item['ipDescription'] ?></td>
                                    <td class="text-center"><?php echo $item['ipGateway']; ?></td>
                                    <td class="text-center"><?php echo $item['ipSubnet'] ?></td>
                                    <td class="text-center"><?php echo $item['customMac'] ?></td>
                                </tr> 
                                <?php    
                            }    
                            ?>
                        </tbody>    
                    </table>    
                            </td>
                        </tr>
                        
                    </tbody>
                </table>
                  
            <?php    
            }
            ?>
            <div class="btn-container">
                <a href="<?php echo $modulelink.'&action=managekvm&rsid='.$rsServerId ?>" class="btn btn-primary">
                    KVM Over IP
                </a>
                <input id="btnPowerOn" type="button" class="btn btn-primary" value="Power On"> 
                <input id="btnPowerOff" type="button" class="btn btn-primary" value="Power Off">
            </div>  
            <script type="text/javascript">
                jQuery(document).ready(function(){

                    jQuery("input#btnPowerOn").click(function(e){
                        if (confirm("Are you sure you want to Power On the server? This may cause data loss or corruption.") == true) {
                            var btn = jQuery(this);

                            btn.attr('disabled',true);
                            var oldBtnHtml = btn.val();

                            btn.val('Processing...');


                            var url =  "<?php echo $modulelink.'&rsid='.$rsServerId.'&action=dopoweron' ?>";

                            $.ajax({
                                url : url,
                                method : "post"
                            }).done(function (result) {

                                if(result.success == 1){
                                    alert("Power on successfully!");
                                }
                                else if(result.errors){
                                    alert(result.errors.join("\n"));
                                }
                                else{
                                    alert("Something went wrong");
                                }

                                btn.val(oldBtnHtml);
                                btn.attr('disabled',false);

                            });
                        }
                    });

                    jQuery("input#btnPowerOff").click(function(e){
                        if (confirm("Are you sure you want to Power Off the server? This may cause data loss or corruption.") == true) {
                            var btn = jQuery(this);

                            btn.attr('disabled',true);
                            var oldBtnHtml = btn.val();

                            btn.val('Processing...');

                            var url =  "<?php echo $modulelink.'&rsid='.$rsServerId.'&action=dopoweroff' ?>";

                            $.ajax({
                                url : url,
                                method : "post"
                            }).done(function (result) {

                                if(result.success == 1){
                                    alert("Power Off successfully!");
                                }
                                else if(result.errors){
                                    alert(result.errors.join("\n"));
                                }
                                else{
                                    alert("Something went wrong");
                                }

                                btn.val(oldBtnHtml);
                                btn.attr('disabled',false);

                            });
                        }
                    });

                    jQuery("input#btnUpdateMAC").click(function(e){
                        if (confirm("Are you sure you want to update MAC.") == true) {
                            var btn = jQuery(this);

                            btn.attr('disabled',true);
                            btn.val('Updating...');

                            var url =  "<?php echo $modulelink.'&rsid='.$rsServerId.'&action=setmacaddress' ?>";
                           
                            var ip = $("select#serverIP").val();
                            var macAddrType = $("input[name='macAddrType']:checked").val();
                            var customMAC = $("input#customMAC").val();

                            $.ajax({
                                url : url,
                                method : "post",
                                data : {ip : ip, macAddrType : macAddrType, customMAC: customMAC}
                                }).done(function (result) {
                                    if(result.success == 1){
                                        alert("MAC address updated successfully!");
                                    }
                                    else if(result.errors){
                                        alert(result.errors[0]);
                                    }
                                    else{
                                        alert("Something went wrong");
                                    }
                                    location.reload();
                            })

                        }
                    });    

                    jQuery("input[name='macAddrType']").change(function(e){
                        if($(this).val() == 'custom'){
                            $("#customMACBox").show();
                        }
                        else{
                            $("#customMACBox").hide();
                        }
                    });

                    /*Trigger change on page load*/
                    $("select#serverIP").change();
                }); 

                function setIPAddressData(e){
                    /*set reverse dns url id*/
                    var ipAddressId  = $(e).find(":selected").attr('data-ipaddressid');
                    var reverseDNSUrl =  "<?php echo $modulelink.'&rsid='.$rsServerId.'&action=reversedns&ipid=' ?>"+ipAddressId;
                    $("a#btnReverseDNS").attr('href', reverseDNSUrl);
                
                    var customMAC  = $(e).find(":selected").attr('data-custommac');
                    $("input#customMAC").val(customMAC);
                    if(customMAC == ''){
                        $("input#macAddrTypeServer").attr("checked", true).change();
                    }
                    else{
                        $("input#macAddrTypeCustom").attr("checked", true).change();
                    }
                }
            </script>   
           
            <?php
            }
        }
        catch (Exception $e){
            logActivity("ReliableSite: Error in {$e->getMessage()} ");
        }
    }
    
    function manageos($vars){
        try{
            
            // Get common module parameters
            $modulelink = $vars['modulelink'];
            $LANG = $vars['_lang'];
            $errors = [];
            
            $rsServerId = isset($_REQUEST['rsid']) ? $_REQUEST['rsid'] : 0;
            $isInstalling = false;
            $installationStatus = '';
            $serverDetail = [];
            $ipAddressList = [];
            $compitableOS = [];
        
            
              
            $apiObj = rs_getAPIObj();
            //get server details
            $getServerResp = $apiObj->getServerDetails($rsServerId);

            if($getServerResp['success'] == true){
                $serverDetail = $getServerResp['data']['server'];
                $ipAddressList = $getServerResp['data']['ipAddressList'];
            }
            else{
                $errors = $getServerResp['errors'];
            }
        
            //get CDR Range
            $ipAddressData = [];
            foreach($ipAddressList as $ipData){

                $ipAddress = $ipData['ipDescription'];
                $gateway = $ipData['ipGateway'];

                $ips = CidrToIpRange($ipAddress, $gateway);


                $ipAddressData[$ipAddress] = [
                    'gateway' => $gateway,
                    'subnet' => $ipData['ipSubnet'],
                    'ips' => $ips

                ];

            }

            $ipAddressDataJson = json_encode($ipAddressData);

            //check installation status
            $installStatusResp = $apiObj->GetOSInstallStatus($rsServerId);

            if($installStatusResp['success'] == true){
                $isInstalling = true;
                $installationStatus = $installStatusResp['data'];
            }
        
            if(!$isInstalling){
                //get OS list
                $getOSResp = $apiObj->GetCompatibleOS($rsServerId);

                if($getOSResp['success'] == true){
                    $compitableOS = $getOSResp['data'];
                }
                else{
                    $errors = $getOSResp['errors'];
                }
            }
            
            ?>
            <p>
                <a href="<?php echo $modulelink ?>" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i>
                    <?php echo $LANG['backToHome.Label']; ?>
                </a>
            </p>
            <h2>Install/Re-Install Operating System</h2>
            
            <?php
            if(!empty($errors)){
                 foreach($errors as $error){
                     ?>
                     <div class="alert alert-danger"><?php echo $error; ?></div>
                     <?php
                 }
            }
            else{
                if($isInstalling == true){
                    ?>
                    <table class="form" width="100%">
                        <tbody>
                            <tr>
                                <td class="fieldlabel" width="15%">Server Name</td>
                                <td class="fieldarea">
                                    <?php echo $serverDetail['serverLabel'] ?>
                                </td>

                            </tr>

                            <tr>
                                <td class="fieldlabel" width="15%">Status</td>
                                <td class="fieldarea">
                                    <?php echo $installationStatus; ?>
                                </td>
                            </tr>
                            
                        </tbody>
                    </table>    
                    <div class="btn-container">
                        <input type="button" id="btnCancelInstall" class="btn btn-danger" value="Cancel Install">
                        <a href="" class="btn btn-primary">
                            Refresh Status
                        </a>
                    </div>
                    <script type="text/javascript">
                        jQuery(document).ready(function(){

                            jQuery("input#btnCancelInstall").click(function(e){

                                var btn = jQuery(this);

                                btn.attr('disabled',true);
                                btn.val('Cancelling...');


                                var url = "<?php echo $modulelink.'&rsid='.$rsServerId.'&action=cancelinstallos' ?>";

                                $.ajax({
                                    url : url,
                                    method : "post"
                                }).done(function (result) {
                                    if(result.success == 1){
                                        alert("Installation process cancelled successfully!");
                                    }
                                    else if(result.errors){
                                        alert(result.errors[0]);
                                    }
                                    else{
                                        alert("Something went wrong");
                                    }
                                    location.reload();

                                });
                            });
                        });
                    </script> 
                     
                    <?php
                    
                }
                else{
                    ?>
                    <form id="frmInstallOS" method="post" action="<?php echo $modulelink.'&action=installos&rsid='.$rsServerId; ?>"> 
                        <div class="alert alert-warning">This process will wipe all of the data on this server.</div>
                        <div id="rsError"></div>
                        <table class="form" width="100%">
                            <tbody>
                                <tr>
                                    <td class="fieldlabel" width="15%">Server Name</td>
                                    <td class="fieldarea">
                                        <?php echo $serverDetail['serverLabel'] ?>
                                    </td>

                                </tr>

                                <tr>
                                    <td class="fieldlabel" width="15%">IP Block</td>
                                    <td class="fieldarea">
                                        <select class="form-control select-inline" name="serverIPBlock" id="serverIPBlock">
                                            <?php
                                            foreach($ipAddressData as $key => $item){
                                                echo '<option value="'.$key.'">'.$key.'</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="fieldlabel" width="15%">Gateway</td>
                                    <td class="fieldarea">
                                       <input type="text" class="form-control input-250" name="serverGateway" id="serverGateway" value="" readonly>
                                    </td>

                                </tr>

                                <tr>
                                    <td class="fieldlabel" width="15%">Netmask</td>
                                    <td class="fieldarea">
                                        <input type="text" class="form-control input-250" name="serverNetmask" id="serverNetmask" value="" readonly>
                                    </td>

                                </tr>

                                <tr>
                                    <td class="fieldlabel" width="15%">Server IP</td>
                                    <td class="fieldarea">
                                         <select class="form-control select-inline" name="serverIP" id="serverIP">
                                             <?php
                                            foreach($ipAddressData as $key => $item){
                                                foreach ($item['ips'] as $ip){
                                                    echo '<option style="display:none;" value="'.$ip.'" data-ipblock="'.$key.'">'.$ip.'</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </td>

                                </tr>

                                <tr>
                                    <td class="fieldlabel" width="15%">License Key (Optional)</td>
                                    <td class="fieldarea">
                                        <input type="text" class="form-control input-250" name="licenseKey" value="">
                                    </td>

                                </tr>
                                <tr>
                                    <td class="fieldlabel" width="15%">OS to Install</td>
                                    <td class="fieldarea">
                                        <select id="operatingSystemId" class="form-control select-inline" name="operatingSystemId">
                                            <option value="">--Please Select--</option>
                                            <?php
                                            foreach($compitableOS as $os){
                                                echo '<option value="'.$os['operatingSystemId'].'">'.$os['osLabel'].'</option>';
                                            }
                                            ?>
                                        </select>
                                    </td>

                                </tr>
                                <tr>
                                    <td class="fieldlabel" width="15%">Partitioning Scheme</td>
                                    <td class="fieldarea">
                                        <select id="partitioningSchemeId" class="form-control select-inline" name="partitioningSchemeId" disabled>
                                            <option value="">--Please Select--</option>
                                        </select>
                                    </td>

                                </tr>
                            </tbody>
                        </table>
                        <div class="btn-container">
                            <input id="btnInstallOS" type="submit" class="btn btn-primary" value="Install OS">
                            <a href="<?php echo $modulelink.'&action=manageserver&rsid='.$rsServerId ?>" class="btn btn-default">
                                Back to Manage
                            </a>
                        </div>
                    </form>    
                    <script type="text/javascript">
                        var ipAddressData = <?php echo $ipAddressDataJson; ?>;
                        var modulelink = "<?php echo $modulelink ?>";
                        var rsid = "<?php echo $rsServerId ?>";

                        jQuery(document).ready(function(){

                            jQuery("select#serverIPBlock").change(function(){
                                var ipBlock = jQuery(this).val();
                               
                                /*hide all serverIp*/
                                jQuery("select#serverIP option").hide();

                                /*Display IP based on IP block*/
                                jQuery("select#serverIP option[data-ipblock='"+ipBlock+"']").show();

                                /*fill up gateway*/
                                jQuery.each(ipAddressData, function(key, item) {
                                    if(key == ipBlock){
                                        jQuery("input#serverGateway").val(item.gateway);
                                        jQuery("input#serverNetmask").val(item.subnet);
                                    }
                                });

                            });

                            jQuery("form#frmInstallOS").submit(function(e){
                                e.preventDefault();
                                jQuery('#rsError').html('');
                                var btn = jQuery("form#frmInstallOS input#btnInstallOS");

                                btn.attr('disabled',true);
                                var oldBtnHtml = btn.val();

                                btn.val('Installing...');


                                var url = jQuery(this).attr('action');
                                var data = jQuery(this).serialize();

                                $.ajax({
                                    url : url,
                                    method : "post",
                                    data : data
                                }).done(function (result) {
                                    if(result.success == 1){

                                        alert("Installation process started successfully!");

                                        location.reload();

                                    }
                                    else if(result.errors){
                                        var err = '<div class="alert alert-danger"><ul><li>'
                                        err += result.errors.join("</li><li>");
                                        err += '</li></ul></div>';

                                        jQuery('#rsError').html(err);
                                    }
                                    else{
                                        alert("Something went wrong");
                                    }

                                    btn.val(oldBtnHtml);
                                    btn.attr('disabled',false);

                                });
                            });

                            /*get Partitioning Scheme*/
                            jQuery("select#operatingSystemId").change(function(){
                                var operatingSystemId = jQuery(this).val();
                                var dd = jQuery("select#partitioningSchemeId");

                                dd.html("<option value=''>--Please Select--</option>");
                                dd.attr('disabled', true);

                                if(operatingSystemId){

                                    dd.html("<option value=''>Fetching....</option>");
                                    dd.attr('disabled', true);

                                    var url = modulelink+"&rsid="+rsid+"&action=getpartitioningschemes";

                                    $.ajax({
                                        url : url,
                                        method : "post",
                                        data : {operatingSystemId : operatingSystemId}
                                    }).done(function (result) {
                                        if(result.success == 1){
                                            var options = '<option value="">--Please Select--</option>';
                                            jQuery(result.partitionSchemes).each(function(index, partitionScheme){
                                                console.log(partitionScheme.partitioningSchemeId);
                                                options += '<option value="'+partitionScheme.partitioningSchemeId+'">'+partitionScheme.title+'</option>'; 
                                            });

                                            dd.html(options);
                                            dd.attr('disabled', false);
                                        }
                                        else if(result.errors){

                                            alert(result.errors.join("\n"));
                                        }
                                        else{
                                            alert("Something went wrong");
                                        }

                                    });
                                }
                            });

                            /*Fired manually on page load*/
                            jQuery("select#serverIPBlock").change();

                        });
                    </script> 
                     
                    <?php 
                }
                ?>
                       
                
               
           
            <?php
            }
        }
        catch (Exception $e){
            logActivity("ReliableSite: Error in {$e->getMessage()} ");
        }
    }
    
    function managekvm($vars){
        try{
            
            // Get common module parameters
            $modulelink = $vars['modulelink'];
            $LANG = $vars['_lang'];
            $errors = [];
            $yourIP = '';
            $rsServerId = isset($_REQUEST['rsid']) ? $_REQUEST['rsid'] : 0;
            $kvmDetails = [];
            $IPMISessions = [];
        
           
                //get server details
                $apiObj = rs_getAPIObj();

                $getServerResp = $apiObj->getServerDetails($rsServerId);

                if($getServerResp['success'] == true){

                    $serverDetail = $getServerResp['data']['server'];

                   
                    //get KVM Details
                    
                    $getKVMResp = $apiObj->getKVMDetails($rsServerId);

                    if($getKVMResp['success'] == true){
                        $kvmDetails = $getKVMResp['data'];

                        $yourIP = (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['REMOTE_HOST']);

                        //get IPMI Sessions
                        $getIPMISessionResp = $apiObj->GetIPMISessions($rsServerId);

                        if($getIPMISessionResp['success'] == true){
                            $IPMISessions = $getIPMISessionResp['data'];
                        }
                        else{
                        $errors = $getIPMISessionResp['errors'];
                        }

                    }
                    else{
                        $errors = $getKVMResp['errors'];
                    }
                }
                else{
                    $errors = $getServerResp['errors'];
                }
        
            
            ?>
            <p>
                <a href="<?php echo $modulelink.'&action=manageserver&rsid='.$rsServerId ?>" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i>
                    Back to Server Management
                </a>
                
            </p>
            <h2>KVM Over IP</h2>
            
            <?php
               if(!empty($errors)){
                    foreach($errors as $error){
                        ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php
                    }
               }
               else{
            ?>
            <div class="alert alert-info">Use the KVM Over IP interface to control the power of the server (power on, power off, reset) and to access the server directly from within the console if the server is not remotely accessible over the network. For more advanced users the KVM interface can also be used to completely re-install the operating system on the server.</div>             
            <table class="form" width="100%">
                <tbody>
                    <tr>
                        <td class="fieldlabel">Server Name</td>
                        <td class="fieldarea">
                            <?php echo $serverDetail['serverLabel']; ?>
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="fieldlabel">Your IP</td>
                        <td class="fieldarea">
                        <input type="text" class="form-control input-250" name="yourIP" value="<?php echo $yourIP ?>">
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="fieldlabel">IPMI Access</td>
                        <td class="fieldarea">
                            <?php
                            if($kvmDetails['accessEnabled'] == true){
                                ?>
                                Enabled <input id="btnDisableKVM" type="button" class="btn btn-primary btn-sm" value="Disable">
                                <?php
                            }
                            else{
                                ?>
                                Disabled <input id="btnEnableKVM" type="button" class="btn btn-primary btn-sm" value="Enable">
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <?php
                    if($kvmDetails['accessEnabled'] == true){
                    ?>    
                        <tr>
                            <td class="fieldlabel">KVM IP</td>
                            <td class="fieldarea">
                                <?php 
                                echo $kvmDetails['kvM_IP'];
                                if($kvmDetails['kvM_IP']){
                                    echo ' <a class="btn btn-primary btn-sm" href="https://'.$kvmDetails['kvM_IP'].'" target="_blank">Launch KVM</a>';
                                }
                                ?>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class="fieldlabel">KVM Username</td>
                            <td class="fieldarea">
                                <?php echo $kvmDetails['kvM_User'] ?>
                            </td>
                        </tr>
                        
                        <tr>
                            <td class="fieldlabel">KVM Password</td>
                            <td class="fieldarea">
                                <?php echo $kvmDetails['kvM_Password'] ?>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>    
                </tbody>
            </table>
            
            <h2>IPMI Sessions</h2>
            <table class="datatable" width="100%">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Server Name</th>
                    <th>Proxy IP</th>
                    <th>Expires</th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    if(count($IPMISessions) > 0){
                        foreach($IPMISessions as $key => $item){
                            ?>
                            <tr>
                                <td class="text-center"><?php echo $key + 1 ?></td>
                                <td class="text-center"><?php echo $item['serverName'] ?></td>
                                <td class="text-center"><?php echo $item['proxyIP'] ?></td>
                                <td class="text-center"><?php echo date("m/d/Y H:i:s a", strtotime($item['expirationDateTime'])) ?></td>
                            </tr>
                            <?php
                        }
                    }
                    else{
                        ?>
                        <tr>
                            <td colspan="4" class="text-center">No active IPMI Sessions</td>
                        </tr>    
                        <?php
                    }
                    ?>
                </tbody>    
            </table>
            
            <div class="btn-container">
                <a href="<?php echo $modulelink.'&action=manageserver&rsid='.$rsServerId ?>" class="btn btn-primary">
                Back to Server Management
                </a>
            </div>
                
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    
                    jQuery("input#btnEnableKVM").click(function(e){
                            
                        var btn = jQuery(this);

                        btn.attr('disabled',true);
                        btn.val('Enabling...');


                        var url = "<?php echo $modulelink.'&rsid='.$rsServerId.'&action=enablekvm' ?>";
                        var remoteIP = jQuery("input[name='yourIP']").val();

                        $.ajax({
                            url : url,
                            method : "post",
                            data : {remoteIP : remoteIP}
                        }).done(function (result) {
                            if(result.success == 1){
                                alert("KVM enabled successfully!");
                            }
                            else if(result.errors){
                                alert(result.errors[0]);
                            }
                            else{
                                alert("Something went wrong");
                            }
                            location.reload();

                        });
                    });
                    
                    jQuery("input#btnDisableKVM").click(function(e){
                            
                        var btn = jQuery(this);

                        btn.attr('disabled',true);
                        btn.val('Disabling...');
                        
                        var url = "<?php echo $modulelink.'&rsid='.$rsServerId.'&action=disablekvm' ?>";
                        
                        $.ajax({
                            url : url,
                            method : "post"
                        }).done(function (result) {
                            if(result.success == 1){
                                alert("KVM disabled successfully!");
                            }
                            else if(result.errors){
                                alert(result.errors[0]);
                            }
                            else{
                                alert("Something went wrong");
                            }
                            location.reload();

                        });
                    });
                });
            </script>            
           
            <?php
            }
        }
        catch (Exception $e){
            logActivity("ReliableSite: Error in {$e->getMessage()} ");
        }
    }
    
    function bwstats($vars){
        try{
            
            // Get common module parameters
            $modulelink = $vars['modulelink'];
            $LANG = $vars['_lang'];
            $errors = [];
            
            $rsServerId = isset($_REQUEST['rsid']) ? $_REQUEST['rsid'] : 0;
            
            
            $period = (isset($_GET['period']) ? $_GET['period'] : 'hour');
            $timezone = (isset($_GET['tz']) ? $_GET['tz'] : '46');
            
            $pagelink = $modulelink.'&rsid='.$rsServerId.'&action=bwstats';

            $timezones = rsgetTimeZoneList();

            $timezoneOffset = 'UTC';
            if(isset($timezones[$timezone])){
                $timezoneOffset = $timezones[$timezone]['utc'];
            }

            $periods = ['hour' => 'Last Hour', 'day' => 'Last Day', 'month' => 'Last Month', 'year' => 'Last Year'];
            $periodDateFormats = [
               'hour' => 'H:i:s', 'day' => 'H:i:s', 'month' => 'Y-m-d', 'year' => 'Y-m-d'
            ];
            
            $chartTitle = '';
            $chartData = [];
        
            

            
            //get server details
            $apiObj = rs_getAPIObj();
                
            //get Bandwith Graph
            $getBandwidthResp = $apiObj->GetBandwidthGraph($rsServerId, $period, $timezoneOffset);

            if($getBandwidthResp['success'] == true){

                $chartTitle = $getBandwidthResp['data']['switchLabel'].' - '.$getBandwidthResp['data']['switchPortLabel'];
                $inSpeedTitle = "In SPeed ({$getBandwidthResp['data']['bandwidthSummary']['inSpeed']} MB)";
                $outSpeedTitle = "Out SPeed ({$getBandwidthResp['data']['bandwidthSummary']['outSpeed']} MB) ";
                $inTrafficTitle = "In Traffic ({$getBandwidthResp['data']['bandwidthSummary']['inTraffic']} MB)";
                $outTrafficTitle = "Out Traffic ({$getBandwidthResp['data']['bandwidthSummary']['outTraffic']} MB)";

                $chartData[] = ['Period', $inSpeedTitle, $outSpeedTitle, $inTrafficTitle, $outTrafficTitle];

                $bandWidthLogs = $getBandwidthResp['data']['bandwidthLogs'];

                foreach ($bandWidthLogs as $timeStr => $bandWidthLog){

                    if(count($bandWidthLog) == 4){
                        if(isset($periodDateFormats[$period])){
                            $timeStr = date($periodDateFormats[$period], strtotime($timeStr));
                        }
                        $chartData[] = [$timeStr, $bandWidthLog[0], $bandWidthLog[1], $bandWidthLog[2], $bandWidthLog[3]];
                    }
                }

            }
            else{
                $errors = $getBandwidthResp['errors'];
            }
            
        
            
            ?>
            <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
            <p>
                <a href="<?php echo $modulelink ?>" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i>
                    <?php echo $LANG['backToHome.Label']; ?>
                </a>
            </p>
            <h2>Server Bandwidth Stats</h2>
            <hr>
            <?php
               if(!empty($errors)){
                    foreach($errors as $error){
                        ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php
                    }
               }
               else{
                $chartData = json_encode($chartData);   
                ?>
                <div class="row text-center">
                    <div class="col-sm-1 mt-2">
                        <label>Period:</label>
                    </div>
                    <div class="col-sm-2">
                        <select class="form-control" id="statPeriod" name="statPeriod">
                            <?php
                                foreach($periods as $key => $label){
                                    $selected = ($key == $period ? 'selected="selected"' : '');
                                    echo '<option value="'.$key.'" '.$selected.'>'.$label.'</option>';
                                }
                            ?>
                            
                        </select>
                    </div>
                    <div class="col-sm-1 mt-2">
                        <label>Timezone:</label>
                    </div>
                    <div class="col-sm-4">
                        <select class="form-control" id="statTimeZone" name="statTimeZone">
                            <?php
                            foreach($timezones as $key => $item){
                                $selected = ($key == $timezone ? 'selected="selected"' : '');
                                echo '<option value="'.$key.'" '.$selected.'>'.$item['timezone'].'</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <hr>
                <div id="bwstateChart" style="width: 100%; height: 500px;"></div>

                <script type="text/javascript">
                    google.charts.load('current', {'packages':['corechart']});
                    google.charts.setOnLoadCallback(drawChart);

                    function drawChart() {
                      var data = google.visualization.arrayToDataTable(<?php echo $chartData ?>);

                      var options = {
                        title: '<?php echo $chartTitle ?>',
                        legend: {position: 'top', maxLines: 3},
                        hAxis: {title: 'Period'},
                        vAxis: {title: 'Volume(MB)', minValue: 0}
                      };

                      var chart = new google.visualization.AreaChart(document.getElementById('bwstateChart'));
                      chart.draw(data, options);
                    }

                    jQuery(document).ready(function(){
                         jQuery("select#statPeriod, select#statTimeZone").change(function(){

                             var period = jQuery("select#statPeriod").val();
                             var timezone = jQuery("select#statTimeZone").val();
                             var url = "<?php echo $pagelink; ?>";
                             url += '&period=' + period + '&tz='+timezone;

                             document.location = url;
                         });
                    });

                </script>

                <div class="btn-container">
                    <a href="<?php echo $modulelink.'&action=manageserver&rsid='.$rsServerId ?>" class="btn btn-default">
                        Back to Manage
                    </a>
                </div>



                <?php
            }
        }
        catch (Exception $e){
            logActivity("ReliableSite: Error in {$e->getMessage()} ");
        }
    }
    
    function backupstorage($vars){
        try{
            
            // Get common module parameters
            $modulelink = $vars['modulelink'];
            $LANG = $vars['_lang'];
            $errors = [];
            
            $rsServerId = isset($_REQUEST['rsid']) ? $_REQUEST['rsid'] : 0;
            $backupStorage = [];
        
            
                //get server details
                $apiObj = rs_getAPIObj();
                //get KVM Details
                $getBackupStorageResp = $apiObj->GetCustomerBackupStorage($rsServerId);
                
                
        
                if($getBackupStorageResp['success'] == true){
                    if(!empty($getBackupStorageResp['data']) && isset($getBackupStorageResp['data'][0]['backupSpaceId'])){
                        $backupSpaceId = $getBackupStorageResp['data'][0]['backupSpaceId'];

                        /*Get backup storage details*/
                        $getBackupStorageDetailsResp = $apiObj->GetBackupStorageDetails($backupSpaceId);
                        if($getBackupStorageDetailsResp['success'] == true){

                            $backupStorage = $getBackupStorageDetailsResp['data'];

                        }
                        else{
                            $errors = $getBackupStorageDetailsResp['errors'];
                        }
                    }
                    else{
                        $errors[] = "No Backup storage found";
                    }

                }
                else{
                    $errors = $getBackupStorageResp['errors'];
                }
            
        
            
            ?>
            <p>
                <a href="<?php echo $modulelink ?>" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i>
                    <?php echo $LANG['backToHome.Label']; ?>
                </a>
            </p>
            <h2>Manage Backup Storage</h2>
            
            <?php
               if(!empty($errors)){
                    foreach($errors as $error){
                        ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php
                    }
               }
               else{
            ?>
            <form id="frmbackupStorage" method="post" action="<?php echo $modulelink.'&action=updatebackupstorage&rsid='.$rsServerId ?>">
                    
                <div id="rsError"></div>
                <table class="form" width="100%">
                    <tbody>
                        <tr>
                            <td class="fieldlabel" width="15%">Server Name</td>
                            <td class="fieldarea">
                                <?php echo $backupStorage['serverLabel'] ?>
                            </td>
                        </tr>

                        <tr>
                            <td class="fieldlabel">Backup Space Name</td>
                            <td class="fieldarea">
                                <?php echo $backupStorage['backupSpaceName'] ?>
                            </td>
                        </tr>

                        <tr>
                            <td class="fieldlabel">Total Size</td>
                            <td class="fieldarea">
                                <?php
                                echo $backupStorage['totalSize']
                                ?>
                            </td>
                        </tr>

                        <tr>
                            <td class="fieldlabel">Size Remaining</td>
                            <td class="fieldarea">
                                <?php 
                                echo $backupStorage['sizeRemaining'];

                                ?>
                            </td>
                        </tr>
                        <?php
                        foreach($backupStorage['ftp'] as $ftp){
                            ?>
                            <tr>
                                <td class="text-center" colspan="2">
                                    <?php
                                    if($ftp['linkType'] == 'Private'){
                                        echo "<strong>Private Account</strong>";
                                    }    
                                    else if($ftp['linkType'] == 'Public'){
                                        echo "<strong>Public Account</strong>";
                                    }
                                    ?>
                                </td>

                            </tr>
                            <tr>
                                <td class="fieldlabel">Name</td>
                                <td class="fieldarea">
                                    <?php echo $ftp['userName'] ?>
                                </td>
                            </tr>

                            <tr>
                                <td class="fieldlabel">FTP IP Address</td>
                                <td class="fieldarea">
                                    <?php
                                    if($ftp['linkType'] == 'Private'){
                                        echo $ftp['ftpPrivateIP'];
                                    }    
                                    else if($ftp['linkType'] == 'Public'){
                                       echo $ftp['ftpPublicIP'];
                                    }
                                    ?>
                                </td>
                            </tr>

                            <tr>
                                <td class="fieldlabel">Password</td>
                                <td class="fieldarea">
                                    <input type="text" class="form-control input-250" name="ftpPassword[]" value="">
                                </td>

                            </tr>

                            <tr>
                                <td class="fieldlabel">Enable</td>
                                <td class="fieldarea">
                                    <select class="form-control select-inline" name="enable[]">
                                        <option value="true" <?php echo ($ftp['enabledSw'] == 'true' ? "selected" : ""); ?>>Yes</option>
                                        <option value="false" <?php echo ($ftp['enabledSw'] == 'false' ? "selected" : ""); ?>>No</option>
                                    </select>
                                    <input type="hidden" class="form-control" name="ftpAccountId[]" value="<?php echo $ftp['ftpAccountsId']; ?>">
                                </td>

                            </tr>
                            <?php
                        }
                        ?>


                    </tbody>
                </table>
                <div class="btn-container">
                    <input id="btnUpdateStorage" type="button" class="btn btn-primary" value="Update">
                    <a href="<?php echo $modulelink.'&action=manageserver&rsid='.$rsServerId ?>" class="btn btn-default">
                        Back to Manage
                    </a>
                </div>
            </form>    
            <script type="text/javascript">
                    
                var modulelink = "<?php echo $modulelink; ?>";
                
                    
                    jQuery(document).ready(function(){
                        
                        jQuery("input#btnUpdateStorage").click(function(e){
                            
                            jQuery('#rsError').html('');
                            var btn = jQuery(this);
                            var frm = jQuery("form#frmbackupStorage");

                            btn.attr('disabled',true);
                            var oldBtnHtml = btn.val();

                            btn.val('Updating...');

                            
                            var url = frm.attr('action');
                            var data = frm.serialize();

                            $.ajax({
                                url : url,
                                method : "post",
                                data : data
                            }).done(function (result) {
                                if(result.success == 1){

                                    alert("Updated successfully!");

                                    location.reload();

                                }
                                else if(result.errors){
                                    var err = '<ul class="alert alert-danger"><li>'
                                    err += result.errors.join("</li><li>");
                                    err += '</li></ul>';

                                    jQuery('#rsError').html(err);
                                }
                                else{
                                    alert("Something went wrong");
                                }

                                btn.val(oldBtnHtml);
                                btn.attr('disabled',false);

                            });
                        });
                    });
                </script>       
           
            <?php
            }
        }
        catch (Exception $e){
            logActivity("ReliableSite: Error in {$e->getMessage()} ");
        }
    }
    
    function reversedns($vars){
        try{
            
            // Get common module parameters
            $modulelink = $vars['modulelink'];
            $LANG = $vars['_lang'];
            $errors = [];
            
            $rsServerId = isset($_REQUEST['rsid']) ? $_REQUEST['rsid'] : 0;
            $ipAddressId = (isset($_GET['ipid']) ? $_GET['ipid'] : '');
            $serverDetail = [];
            $rDNSIPs = [];
        
           
                //get server details
                $apiObj = rs_getAPIObj();
                
                //get server details
                $getServerResp = $apiObj->getServerDetails($rsServerId);

                if($getServerResp['success'] == true){
                    $serverDetail = $getServerResp['data']['server'];
                    $ipAddressList = $getServerResp['data']['ipAddressList'];

                    //check has access of this ipaddress
                    $hasIPAccess = false;
                    foreach($ipAddressList as $ipData){
                        if($ipAddressId == $ipData['ipAddressId']){
                            $hasIPAccess = true;
                            break;
                        }
                    }

                    if($hasIPAccess === true){
                        //get rDNS IP List
                        $getReverseDNSIPResp = $apiObj->getReverseDNSIPs($ipAddressId);
                        if($getReverseDNSIPResp['success'] == true){
                            $rDNSIPs = $getReverseDNSIPResp['data'];
                        }
                        else{
                            $errors = $getReverseDNSIPResp['errors'];
                        }
                    }
                    else{
                        $errors[] = "No Access";
                    }
                }
                else{
                    $errors = $getServerResp['errors'];
                }
            
        
            
            ?>
            <p>
                <a href="<?php echo $modulelink ?>" class="btn btn-info">
                    <i class="fa fa-arrow-left"></i>
                    <?php echo $LANG['backToHome.Label']; ?>
                </a>
            </p>
            <h2>Reverse DNS</h2>
            
            <?php
               if(!empty($errors)){
                    foreach($errors as $error){
                        ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php
                    }
               }
               else{
            ?>
            <form id="frmrDNS" method="post" action="<?php echo $modulelink ?>&action=setreversedns&rsid=<?php echo $rsServerId ?>&ipid=<?php echo $ipAddressId ?>">
                <table class="form" width="100%">
                    <tbody>
                        <tr>
                            <td class="fieldlabel">Server Name</td>
                            <td class="fieldarea">
                                <?php echo $serverDetail['serverLabel']; ?>
                            </td>
                        </tr>

                        <tr>
                            <td class="fieldlabel">IP Address</td>
                            <td class="fieldarea">
                                <select class="form-control select-inline" name="ipAddress" id="ipAddress">
                                <?php    
                                foreach($rDNSIPs as $key => $item){
                                    echo '<option value="'.$item.'">'.$item.'</option>';
                                }
                                ?>
                            </select>
                            </td>
                        </tr>

                        <tr>
                            <td class="fieldlabel">Reverse DNS Record</td>
                            <td class="fieldarea">
                                <input type="text" class="form-control input-250" name="rDNSRecord" value="">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="btn-container">
                    <input type="hidden" name="ipAddressId" value="<?php echo $ipAddressId ?>">
                    <input id="btnSaveReverseDNS" type="submit" class="btn btn-primary" value="Update">
                    <a href="<?php echo $modulelink.'&action=manageserver&rsid='.$rsServerId ?>" class="btn btn-default">
                        Back to Manage
                    </a>
                </div>
            </form>    
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    var modulelink = "<?php echo $modulelink ?>";
                    var rsid = "<?php echo $rsServerId; ?>";
                    var ipAddressId = "<?php echo $ipAddressId; ?>";
                    
                    jQuery("select#ipAddress").change(function(){
                            
                            var dd = $(this);
                            var ipAddress = dd.val();
                           
                            $('#rsError').html('');
                            $("form#frmrDNS input[name='rDNSRecord']").val('');

                            dd.attr('disabled',true);
                            
                            var url = modulelink+"&action=getreversedns&rsid="+rsid;
                            var data = {ipAddressId : ipAddressId, ipAddress: ipAddress}

                            $.ajax({
                                url : url,
                                method : "post",
                                data : data
                            }).done(function (result) {
                                if(result.success == 1){
                                    $("form#frmrDNS input[name='rDNSRecord']").val(result.dnsRecord);
                                }
                                else if(result.errors){
                                    var err = '<div class="alert alert-danger"><ul><li>'
                                    err += result.errors.join("</li><li>");
                                    err += '</li></ul></div>'

                                    $('#rsError').html(err);
                                }
                                else{
                                    alert("Something went wrong");
                                }

                                dd.attr('disabled',false);

                            });
                            
                    });
                    
                    jQuery("form#frmrDNS").submit(function(e){
                            e.preventDefault();
                            $('#rsError').html('');
                            
                            var btn = jQuery("form#frmrDNS input#btnSaveReverseDNS");

                            btn.attr('disabled',true);
                            var oldBtnHtml = btn.val();

                            btn.val('Updating...');


                            var url = jQuery(this).attr('action');
                            var data = jQuery(this).serialize();

                            $.ajax({
                                url : url,
                                method : "post",
                                data : data
                            }).done(function (result) {
                                if(result.success == 1){
                                    alert("Record updated successfully!");
                                }
                                else if(result.errors){
                                    var err = '<div class="alert alert-danger"><ul><li>'
                                    err += result.errors.join("</li><li>");
                                    err += '</li></ul></div>'

                                    $('#rsError').html(err);
                                }
                                else{
                                    alert("Something went wrong");
                                }

                                btn.val(oldBtnHtml);
                                btn.attr('disabled',false);

                            });
                        });

                        
                        
                        /*Fired manually on page load*/
                        jQuery("select#ipAddress").change();
                });
            </script>            
           
            <?php
            }
        }
        catch (Exception $e){
            logActivity("ReliableSite: Error in {$e->getMessage()} ");
        }
    }
    
}   
