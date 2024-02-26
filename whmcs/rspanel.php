<?php
/**
 * WHMCS SDK Sample Addon Module
 *
 * An addon module allows you to add additional functionality to WHMCS. It
 * can provide both client and admin facing user interfaces, as well as
 * utilise hook functionality within WHMCS.
 *
 * This sample file demonstrates how an addon module for WHMCS should be
 * structured and exercises all supported functionality.
 *
 * Addon Modules are stored in the /modules/addons/ directory. The module
 * name you choose must be unique, and should be all lowercase, containing
 * only letters & numbers, always starting with a letter.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "addonmodule" and therefore all functions
 * begin "addonmodule_".
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/addon-modules/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

/**
 * Require any libraries needed for the module to function.
 * require_once __DIR__ . '/path/to/library/loader.php';
 *
 * Also, perform any initialization required by the service's library.
 */
require_once __DIR__ . '/lib/helper.php';
use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\rspanel\Admin\AdminDispatcher;
use WHMCS\Module\Addon\rspanel\Client\ClientDispatcher;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define addon module configuration parameters.
 *
 * Includes a number of required system fields including name, description,
 * author, language and version.
 *
 * Also allows you to define any configuration parameters that should be
 * presented to the user when activating and configuring the module. These
 * values are then made available in all module function calls.
 *
 * Examples of each and their possible configuration parameters are provided in
 * the fields parameter below.
 *
 * @return array
 */
function rspanel_config()
{
    return [
        'name' => 'ReliableSite Dedicated Server Reseller',
        'description' => 'Reseller module to manage ReliableSite dedicated servers.',
        'author' => 'ReliableSite.Net LLC',
        'language' => 'english',
        'version' => '2.0.2',
        'fields' => [
            'apiKey' => [
                'FriendlyName' => 'API Key',
                'Type' => 'text',
                'Size' => '50',
                'Default' => '',
                'Description' => 'The API key is available within the dedicated server management interface.',
            ],
            'apiToken' => [
                'FriendlyName' => 'API Token',
                'Type' => 'textarea',               
                'Default' => '',
                'ReadOnly' => true,
                'Description' => "This field will be pre-filled automatically by the module.",
            ],
            'apiTokenValidity' => [
                'FriendlyName' => 'API Token Validity',
                'Type' => 'text',
                'Size' => '50',
                'Default' => '',
                'Disabled' => true,
                'Description' => "This field will be pre-filled automatically by the module.",
            ],
        ]
    ];
}

/**
 * Activate.
 *
 * Called upon activation of the module for the first time.
 * Use this function to perform any database and schema modifications
 * required by your module.
 *
 * This function is optional.
 *
 * @see https://developers.whmcs.com/advanced/db-interaction/
 *
 * @return array Optional success/failure message
 */
function rspanel_activate()
{
    // Create custom tables and schema required by your module
    try {
        if (!Capsule::schema()->hasTable('mod_reliablesite_orders')) {
            Capsule::schema()->create(
                'mod_reliablesite_orders',
                function ($table){
                    $table->increments('id');
                    $table->integer('hostingId');
                    $table->integer('addonId');
                    $table->integer('rsServerId');
                    $table->string('rsServerName');
                    $table->integer('rsUserId');
                    $table->string('rsUserName');
                    $table->timestamps();
                }
            );
        }
        
        if (!Capsule::schema()->hasTable('mod_reliablesite_customers')){
            Capsule::schema()->create(
                'mod_reliablesite_customers',
                function ($table){
                    $table->increments('id');
                    $table->integer('clientId');
                    $table->integer('rsUserId');
                    $table->string('rsUserName');
                    $table->timestamps();
                }
            );
        }

        return [
            // Supported values here include: success, error or info
            'status' => 'success',
            'description' => 'Module is activate successfully.',
        ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            'status' => "error",
            'description' => 'Unable to create table: ' . $e->getMessage(),
        ];
    }
}

/**
 * Deactivate.
 *
 * Called upon deactivation of the module.
 * Use this function to undo any database and schema modifications
 * performed by your module.
 *
 * This function is optional.
 *
 * @see https://developers.whmcs.com/advanced/db-interaction/
 *
 * @return array Optional success/failure message
 */
function rspanel_deactivate()
{
    // Undo any database and schema modifications made by your module here
    try {
        return [
            // Supported values here include: success, error or info
            'status' => 'success',
            'description' => 'Addon deactivated successfully',
        ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            "status" => "error",
            "description" => "Unable to deactivate: {$e->getMessage()}",
        ];
    }
}

/**
 * Upgrade.
 *
 * Called the first time the module is accessed following an update.
 * Use this function to perform any required database and schema modifications.
 *
 * This function is optional.
 *
 * @see https://laravel.com/docs/5.2/migrations
 *
 * @return void
 */
function rspanel_upgrade($vars)
{
    
}

/**
 * Admin Area Output.
 *
 * Called when the addon module is accessed via the admin area.
 * Should return HTML output for display to the admin user.
 *
 * This function is optional.
 *
 * @see AddonModule\Admin\Controller::index()
 *
 * @return string
 */
function rspanel_output($vars)
{
    // possible way of handling this using a very basic dispatcher implementation.

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    $rsid = isset($_REQUEST['rsid']) ? $_REQUEST['rsid'] : '';
    
    $vars['rsid'] = $rsid;
    
    if($action == 'addcustomer'){
        rspanel_add_customer();
    }
    else if($action == 'updatecustomer'){
        rspanel_update_customer();
    }
    else if($action == 'deletecustomer'){
        rspanel_delete_customer();
    }
    else if($action == 'assigncustomer'){
        rspanel_assign_customer();
    }
    else if($action == 'unassigncustomer'){
        rspanel_unassign_customer();
    }
    else if($action == 'assignserver'){
        rspanel_assign_server();
    }
    else if($action == 'unassignserver'){
        rspanel_unassign_server($vars);
    }
    else if($action == 'addnullroute'){
        rspanel_add_nullroute();
    }
    else if($action == 'removenullroute'){
        rspanel_remove_nullroute();
    }
    elseif($action == 'installos'){
        rspanel_installos($vars);
    }
    else if($action == 'cancelinstallos'){
        rspanel_cancelinstallos($vars);
    }
    else if($action == 'getpartitioningschemes'){
        rspanel_getpartitioningschemes($vars);
    }
    else if($action == 'dopoweron'){
        rspanel_dopoweron($vars);
    }
    else if($action == 'dopoweroff'){
        rspanel_dopoweroff($vars);
    }
    else if($action == 'enablekvm'){
        rspanel_enablekvm($vars);
    }
    else if($action == 'disablekvm'){
        rspanel_disablekvm($vars);
    }
    else if($action == 'setmacaddress'){
        rspanel_setmacaddress($vars);
    }
    else if($action == 'updatebackupstorage'){
        rspanel_updatebackupstorage($vars);
    }
    else if($action == 'getreversedns'){
        rspanel_getreversedns($vars);
    }
    else if($action == 'setreversedns'){
        rspanel_setreversedns($vars);
    }
    else{
        $dispatcher = new AdminDispatcher();
        $response = $dispatcher->dispatch($action, $vars);
        echo $response;
    }
}

function rspanel_add_customer(){
    $success = 0;
    $errors = [];
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if(!$username){
       $errors[] = "Please enter username"; 
    }
    else if(strlen($username) < 5){
        $errors[] = "Username should minimum 5 charaters"; 
    }
    
    if(!$email){
       $errors[] = "Please enter email"; 
    }
    
    if(!$password){
        $errors[] = "Please enter password";
    }
    else if(strlen($password) < 5){
        $errors[] = "Password should minimum 5 charaters"; 
    }
    
    if(empty($errors)){
        
        $rsAPIObj = rs_getAPIObj();
        $addCustomerResp = $rsAPIObj->addCustomer($username, $email, $password);

        if($addCustomerResp['success'] == true){
            $success = true;
        }
        else{
            $errors = $addCustomerResp['errors'];
        }
        
    }
    
    header('Content-Type: application/json');
    echo json_encode(array('success' => $success, 'errors' => $errors));
    exit();
}

function rspanel_update_customer(){
    $success = 0;
    $errors = [];
    
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
        
    $rsAPIObj = rs_getAPIObj();
    $updateCustomerResp = $rsAPIObj->updateCustomer($username, $email, $password);

    if($updateCustomerResp['success'] == true){
        $success = true;
    }
    else{
        $errors = $updateCustomerResp['errors'];
    }
    
    header('Content-Type: application/json');
    echo json_encode(array('success' => $success, 'errors' => $errors));
    exit();
}

function rspanel_delete_customer(){
    $success = 0;
    $errors = [];
    $rsUserId = isset($_POST['rsUserId']) ? $_POST['rsUserId'] : '';
    $rsUsername = isset($_POST['rsUsername']) ? $_POST['rsUsername'] : '';
        
    $rsAPIObj = rs_getAPIObj();
    $deleteCustomerResp = $rsAPIObj->deleteCustomer($rsUsername);


    if($deleteCustomerResp['success'] == true){
        $success = true;
        
        //delete if assigned
        Capsule::table('mod_reliablesite_customers')
        ->where('rsUserId', $rsUserId)
        ->delete();
    }
    else{
        $errors = $deleteCustomerResp['errors'];
    }
        
    
    
    header('Content-Type: application/json');
    echo json_encode(array('success' => $success, 'errors' => $errors));
    exit();
}

function rspanel_assign_customer(){
    $success = 0;
    $errors = [];
    
    $rsUserName = isset($_POST['rsUserName']) ? $_POST['rsUserName'] : '';
    $rsUserId = isset($_POST['rsUserId']) ? $_POST['rsUserId'] : '';
    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : '';
    
    if(empty($rsUserId)){
        $errors[] = "User Id is required";
    }

    if(empty($rsUserName)){
        $errors[] = "User name is required";
    }
    
    if(empty($clientId)){
        $errors[] = "Please select customer";
    }
    
    if(empty($errors)){
        //check if already assigned
        $customer = Capsule::table("mod_reliablesite_customers")
            ->select('id')
            ->where('rsUserId', $rsUserId)
            ->orWhere('clientId', $clientId)   
            ->first();

        if($customer){
            $errors[] = "Customer is already assigned for this user";
        }
        else{
           //insert
            try{
                
                Capsule::connection()->table('mod_reliablesite_customers')->insert(array(               
                        'clientId' => $clientId,
                        'rsUserId' => $rsUserId,
                        'rsUserName' => $rsUserName,
                        'created_at' => date('Y-m-d H:m:i'),
                        'updated_at' => date('Y-m-d H:m:i')
                    )
                );
                $success = true;
            }
            catch (Exception $e){
               $errors[] = $e->getMessage();
            }
        }
        
    }
    
    header('Content-Type: application/json');
    echo json_encode(array('success' => $success, 'errors' => $errors));
    exit();
}

function rspanel_unassign_customer(){
    try{
        $success = 0;
        $errors = [];
        $rsUserId = isset($_POST['rsUserId']) ? $_POST['rsUserId'] : '';    
        if(!empty($rsUserId)){
            
            
            $success = true;

            //delete if assigned
            Capsule::table('mod_reliablesite_customers')
            ->where('rsUserId', $rsUserId)
            ->delete();
                
           
        }
        else{
            $errors[] = "Invalid user id";
        }

        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Assin server {$e->getMessage()} ");
    }
}

function rspanel_assign_server(){
    try{
        $success = 0;
        $errors = [];
        $hostingId = isset($_POST['hostingId']) ? $_POST['hostingId'] : 0;
        $serverId = isset($_POST['serverId']) ? $_POST['serverId'] : '';
       

        if(!$hostingId){
           $errors[] = "Please select order"; 
        }

        if(!$serverId){
            $errors[] = "Please select server";
        }

        if(empty($errors)){

            $hosting = \WHMCS\Service\Service::find($hostingId);

            if($hosting && $hosting->product->module == 'autorelease'){

             

                $clientId = $hosting->client->id;

                $client = Capsule::table("mod_reliablesite_customers")
                        ->select('rsUserId','rsUserName')
                        ->where('clientId', $clientId)
                        ->first();

                if($client){

                    //check if already assigned
                    $order = Capsule::table("mod_reliablesite_orders")
                        ->select('id','rsServerId')
                        ->where('hostingId', $hostingId)
                        ->first();

                    if($order){
                        $errors[] = "Server is already assigned for this service";
                    }
                    else{
                        
                        $rsAPIObj = rs_getAPIObj();
                        //get server details
                        $serverDetailsResp = $rsAPIObj->getServerDetails($serverId);
                        
                       
                        if($serverDetailsResp['success'] == true){

                            /*get reseller details to get username. if user is not assigned to the user then it is assigned to reseller user */
                            $getProfileResp = $rsAPIObj->GetProfile();

                            
                            if( $getProfileResp['success']== true){
                                $resellerUsername = $getProfileResp['data']['userName'];
                                /*assign to customer if server is not assigned to customer*/
                                $assignedSucees = false;

                                if(empty($serverDetailsResp['data']['server']['user']) || $serverDetailsResp['data']['server']['user'] == $resellerUsername){
                                    $assignServerResp = $rsAPIObj->assignServer($client->rsUserName, $serverId);

                                    if($assignServerResp['success'] == true){
                                        $assignedSucees = true;
                                    }
                                    else{
                                        $errors = $assignServerResp['errors'];
                                    }
                                }
                                else if($serverDetailsResp['data']['server']['user'] == $client->rsUserName){
                                    /*if server already assigned to customer then check assigned customer is belongs to this order*/
                                    $assignedSucees = true;

                                }
                                else{
                                    $errors[] = "Assigned customer doesn't belongs from this order";
                                }

                                if($assignedSucees == true){
                                    $success = true;

                                    $serverName = $serverDetailsResp['data']['server']['serverLabel'];
                                    //insert
                                    Capsule::connection()->table('mod_reliablesite_orders')->insert(array(              
                                        'hostingId' => $hostingId,
                                        'addonId' => 0,
                                        'rsServerId' => $serverId,                                    
                                        'rsServerName' => $serverName,
                                        'rsUserId' => $client->rsUserId,
                                        'rsUserName' => $client->rsUserName,
                                        'created_at' => date('Y-m-d H:m:i'),
                                        'updated_at' => date('Y-m-d H:m:i')
                                        )
                                    );
                                }
                            }
                            else{
                                $errors = $getProfileResp['errors'];
                            }
                            
                        }
                        else{
                            $errors = $serverDetailsResp['errors'];
                        }
                    }
                }
                else{
                    $errors[] = "Customer does not exist. Please create it first";
                }
            }
            else{
                $errors[] = "Invalid service Id";
            }
        }

        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Assin server {$e->getMessage()} ");
    }
}

function rspanel_unassign_server($vars){
    try{
        $success = 0;
        $errors = [];
        $rsServerId = isset($vars['rsid']) ? $vars['rsid'] : 0;    
        if(!empty($rsServerId)){
            //unassign call
            $rsAPIObj = rs_getAPIObj();
            $unassignServerResp = $rsAPIObj->unassignServer($rsServerId);

            if($unassignServerResp['success'] == true){

                
                $success = true;

                //delete if unassigned
                Capsule::table('mod_reliablesite_orders')
                ->where('rsServerId', $rsServerId)
                ->delete();
                
            }
            else{
                $errors = $unassignServerResp['errors'];
            }
        }
        else{
            $errors[] = "Invalid server id";
        }

        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Assin server {$e->getMessage()} ");
    }
}

function  rspanel_add_nullroute(){
    try{
        $success = 0;
        $errors = [];
        $ipAddress = isset($_POST['ipAddress']) ? $_POST['ipAddress'] : '';
        $removeTime = isset($_POST['removeTime']) ? $_POST['removeTime'] : '';
        $removeTimeType = isset($_POST['removeTimeType']) ? $_POST['removeTimeType'] : '';
        $resellerLock = isset($_POST['resellerLock']) ? $_POST['resellerLock'] : '';
        
        if(empty($ipAddress)){
            $errors[] = 'The NullRouteIP field is required';
        }
        
        if(empty($removeTime) || !is_numeric($removeTime) || $removeTime < 0 ){
            $errors[] = 'Please add valid time';
        }
        
        if(empty($removeTimeType)){
            $errors[] = 'Please select time type';
        }
        
        if(empty($resellerLock)){
            $errors[] = 'Please select customer remove?';
        }
        
        if(empty($errors)){
            $str = "+ {$removeTime} {$removeTimeType}";
            
            $scheduledRemove = date("c", strtotime($str)); /* C - ISO 8601 date Ex.2004-02-12T15:19:21+00:00*/
            
            $rsAPIObj = rs_getAPIObj();
            $addNullRouteResp = $rsAPIObj->AddNullRoute($ipAddress, $scheduledRemove, $resellerLock);

            if($addNullRouteResp['success'] == true){
                $success = true;
            }
            else{
                $errors = $addNullRouteResp['errors'];
            }
        }

        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Remove Null route {$e->getMessage()} ");
    }
}

function  rspanel_remove_nullroute(){
    try{
        $success = 0;
        $errors = [];
        $ip = isset($_POST['ip']) ? $_POST['ip'] : '';
        
        $rsAPIObj = rs_getAPIObj();
        $removeNullRouteResp = $rsAPIObj->RemoveNullRoute($ip);
        
        if($removeNullRouteResp['success'] == true){
            $success = true;
        }
        else{
            $errors = $removeNullRouteResp['errors'];
        }

        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Remove Null route {$e->getMessage()} ");
    }
}

/**
 * Admin Area Sidebar Output.
 *
 * Used to render output in the admin area sidebar.
 * This function is optional.
 *
 * @param array $vars
 *
 * @return string
 */
function rspanel_sidebar($vars)
{
    // Get common module parameters
    $modulelink = $vars['modulelink'];
    $version = $vars['version'];
    
    $sidebar = '<a href="'.$modulelink.'">ReliableSite Server ('.$version.')</a>';
    return $sidebar;
}

/**
 * Client Area Output.
 *
 * Called when the addon module is accessed via the client area.
 * Should return an array of output parameters.
 *
 * This function is optional.
 *
 * @see AddonModule\Client\Controller::index()
 *
 * @return array
 */
function rspanel_clientarea($vars)
{
    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
    $serviceId = (isset($_REQUEST['sid']) ? $_REQUEST['sid'] : 0);
    
    $hasAccess = rspanel_hasUserAccess($serviceId);
    
    if(!$hasAccess){
       $action = 'noaccess';
    }
    else{
        $order = Capsule::table("mod_reliablesite_orders AS a")
            ->select('a.rsServerId')
            ->leftJoin('tblhosting AS b', 'a.hostingId','=', 'b.id')    
            ->where('a.hostingId', $serviceId)
            ->first();
        
        if($order){
           $vars['sid'] = $serviceId; 
           $vars['rsid'] = $order->rsServerId;
        }
    }
    
    
    
    if($action == 'installos'){
        rspanel_installos($vars);
    }
    else if($action == 'cancelinstallos'){
        rspanel_cancelinstallos($vars);
    }
    else if($action == 'getpartitioningschemes'){
        rspanel_getpartitioningschemes($vars);
    }
    else if($action == 'dopoweron'){
        rspanel_dopoweron($vars);
    }
    else if($action == 'dopoweroff'){
        rspanel_dopoweroff($vars);
    }
    else if($action == 'enablekvm'){
        rspanel_enablekvm($vars);
    }
    else if($action == 'disablekvm'){
        rspanel_disablekvm($vars);
    }
    else if($action == 'setmacaddress'){
        rspanel_setmacaddress($vars);
    }
    else if($action == 'updatebackupstorage'){
        rspanel_updatebackupstorage($vars);
    }
    else if($action == 'getreversedns'){
        rspanel_getreversedns($vars);
    }
    else if($action == 'setreversedns'){
        rspanel_setreversedns($vars);
    }
    

    $dispatcher = new ClientDispatcher();
    return $dispatcher->dispatch($action, $vars);
}

function rspanel_hasUserAccess($serviceId){
    $hasAccess = false;
    $currentUserId = 0;
    
    /*This class available after 8.0*/
    if(class_exists('\WHMCS\Authentication\CurrentUser1')){
        $currentUser = new \WHMCS\Authentication\CurrentUser;
        $user = $currentUser->user();
        $currentUserId = $user->id;
    }
    else{
        $currentUserId = isset($_SESSION['uid']) ? $_SESSION['uid'] : 0;  
    }
    
    if($currentUserId && !empty($serviceId)){
        $order = Capsule::table("mod_reliablesite_orders AS a")
            ->select('a.id')
            ->leftJoin('tblhosting AS b', 'a.hostingId','=', 'b.id')    
            ->where('a.hostingId', $serviceId)
            ->where('b.userid', $currentUserId)    
            ->first();
        
        if($order){
           $hasAccess = true; 
        }
    }
    
    return $hasAccess;
}

function rspanel_installos($vars){
    try{
        $success = 0;
        $errors = [];
       
        
        $rsServerId = isset($vars['rsid']) ? $vars['rsid'] : 0;
        $serverIP = isset($_POST['serverIP']) ? $_POST['serverIP'] : '';
        $licenseKey = isset($_POST['licenseKey']) ? $_POST['licenseKey'] : '';
        $operatingSystemId = isset($_POST['operatingSystemId']) ? $_POST['operatingSystemId'] : '';
        $partitioningSchemeId = isset($_POST['partitioningSchemeId']) ? $_POST['partitioningSchemeId'] : '';
        
        if(empty($rsServerId)){
            $errors[] = "Server Id is required";
        }
        
        if(empty($serverIP)){
            $errors[] = "Server IP is required";
        }
        
        if(empty($operatingSystemId)){
            $errors[] = "Operating System is required";
        }
        
        if(empty($partitioningSchemeId)){
            $errors[] = "Partitioning Scheme is required";
        }
        
        if(empty($errors)){
    
            //unassign call
            $rsAPIObj = rs_getAPIObj();
            $OSInstallResp = $rsAPIObj->OSInstallStart($rsServerId, $serverIP, $operatingSystemId, $partitioningSchemeId, $licenseKey);

            if($OSInstallResp['success'] == true){
                $success = true;
            }
            else{
                $errors = $OSInstallResp['errors'];
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Install OS {$e->getMessage()} ");
    }
}

function rspanel_cancelinstallos($vars){
    try{
        $success = 0;
        $errors = [];
        $rsServerId = isset($vars['rsid']) ? $vars['rsid'] : 0;
        
        if(!empty($rsServerId)){
           
            //unassign call
            $rsAPIObj = rs_getAPIObj();
            $OSInstallCancelResp = $rsAPIObj->OSInstallCancel($rsServerId->rsServerId);

            if($OSInstallCancelResp['success'] == true){
                $success = true;
            }
            else{
                $errors = $OSInstallCancelResp['errors'];
            }
           

        }
        else{
            $errors[] = "Invalid Server Id";
        }
        
        
        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in OS Install Cancel {$e->getMessage()} ");
    }
}


function rspanel_getpartitioningschemes($vars){
    try{
        $success = 0;
        $errors = [];
        $partitionSchemes = [];
        
        $operatingSystemId = isset($_POST['operatingSystemId']) ? $_POST['operatingSystemId'] : '';
        
        
        if(empty($operatingSystemId)){
            $errors[] = "Operating System is required";
        }
        
        if(empty($errors)){
                
            $rsAPIObj = rs_getAPIObj();
            $partitioningSchemeResp = $rsAPIObj->GetPartitioningSchemes($operatingSystemId);
            
            if($partitioningSchemeResp['success'] == true){
                $success = true;
                $partitionSchemes = $partitioningSchemeResp['data'];
            }
            else{
                $errors = $partitioningSchemeResp['errors'];
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'partitionSchemes' => $partitionSchemes, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in get Partitioning Scheme {$e->getMessage()} ");
    }
}

function rspanel_dopoweron($vars){
    try{
        $success = 0;
        $errors = [];
        
        $rsServerId = isset($vars['rsid']) ? $vars['rsid'] : 0;

        if(!empty($rsServerId)){
    
            $rsAPIObj = rs_getAPIObj();
            $powerOnResp = $rsAPIObj->ServerPoweOn($rsServerId);

            if($powerOnResp['success'] == true){
                $success = true;
            }
            else{
                $errors = $powerOnResp['errors'];
            }
            

        }
        else{
            $errors[] = "Invalid Server Id";
        }
        
        
        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Server Power On {$e->getMessage()} ");
    }
}

function rspanel_dopoweroff($vars){
    try{
        $success = 0;
        $errors = [];
        
        $rsServerId = isset($vars['rsid']) ? $vars['rsid'] : 0;

        if(!empty($rsServerId)){
            $rsAPIObj = rs_getAPIObj();
            $powerOffResp = $rsAPIObj->ServerPoweOff($rsServerId);

            if($powerOffResp['success'] == true){
                $success = true;
            }
            else{
                $errors = $powerOffResp['errors'];
            }
        }
        else{
            $errors[] = "Invalid Server Id";
        }
        
        
        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Server Power Off {$e->getMessage()} ");
    }
}

function rspanel_enablekvm($vars){
    try{
        $success = 0;
        $errors = [];
        $rsServerId = isset($vars['rsid']) ? $vars['rsid'] : 0;
        $remoteIP = isset($_POST['remoteIP']) ? $_POST['remoteIP'] : '';

        if(!empty($rsServerId)){
    
            $rsAPIObj = rs_getAPIObj();
            $enableKVMResp = $rsAPIObj->ServerEnableKVM($rsServerId, $remoteIP);

            if($enableKVMResp['success'] == true){
                $success = true;
            }
            else{
                $errors = $enableKVMResp['errors'];
            }
        }
        else{
            $errors[] = "Invalid Server Id";
        }
        
        
        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Enable KVM {$e->getMessage()} ");
    }
}

function rspanel_disablekvm($vars){
    try{
        $success = 0;
        $errors = [];
        
        $rsServerId = isset($vars['rsid']) ? $vars['rsid'] : 0;

        if(!empty($rsServerId)){
                
            $rsAPIObj = rs_getAPIObj();
            $disableKVMResp = $rsAPIObj->ServerDisableKVM($rsServerId);
            
            if($disableKVMResp['success'] == true){
                $success = true;
            }
            else{
                $errors = $disableKVMResp['errors'];
            }
           

        }
        else{
            $errors[] = "Invalid Server Id";
        }
        
        
        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Disable KVM {$e->getMessage()} ");
    }
}

function rspanel_setmacaddress($vars){
    try{
        $success = 0;
        $errors = [];
        $rsServerId = isset($vars['rsid']) ? $vars['rsid'] : 0;
        $ip = isset($_POST['ip']) ? $_POST['ip'] : '';
        $macAddrType = isset($_POST['macAddrType']) ? $_POST['macAddrType'] : '';
        $customMAC = isset($_POST['customMAC']) ? $_POST['customMAC'] : '';

        $order = Capsule::table("mod_reliablesite_orders")
            ->select('id','rsServerId')
            ->where('hostingId', $hostingId)
            ->first();

        if(!empty($rsServerId)){
            
            $isCustomMacEnable = 'false';
            if($macAddrType == 'custom'){
                $isCustomMacEnable = 'true';
            }

            $rsAPIObj = rs_getAPIObj();
            $setMacAddressResp = $rsAPIObj->SetMacAddess($rsServerId, $ip, $isCustomMacEnable, $customMAC);

            if($setMacAddressResp['success'] == true){
                $success = true;
            }
            else{
                $errors = $setMacAddressResp['errors'];
            }
            

        }
        else{
            $errors[] = "Invalid Server Id";
        }
        
        
        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Set Mac Address {$e->getMessage()} ");
    }
}

function rspanel_updatebackupstorage($vars){
    
    try{
        $success = 0;
        $errors = [];
        
        $rsServerId = isset($vars['rsid']) ? $vars['rsid'] : 0;
        
        $ftpPasswords = isset($_POST['ftpPassword']) ? $_POST['ftpPassword'] : '';
        $enable = isset($_POST['enable']) ? $_POST['enable'] : '';
        $ftpAccountIds = isset($_POST['ftpAccountId']) ? $_POST['ftpAccountId'] : '';
        
        if(!empty($rsServerId)){

            
                
                if(is_array($ftpAccountIds)){
                    
                    foreach($ftpAccountIds as $index => $ftpAccountId){
                        
                        $isEnable = $enable[$index];
                        $ftpPassword = $ftpPasswords[$index];
                        
                        if(!empty($ftpPassword)){
                        
                            $rsAPIObj = rs_getAPIObj();
                            $setFTPAccountResp = $rsAPIObj->SetFTPAccount($ftpAccountId, $isEnable, $ftpPassword);

                            if($setFTPAccountResp['success'] == true){
                                $success = true;
                            }
                            else{
                                $success = false;
                                $errors = $setFTPAccountResp['errors'];
                                break;
                            }
                        }
                        else{
                            $success = false;
                            $errors[] = "Password is required";
                            break;
                        }
                    }
                }
                else{
                    $errors[] = "Missing Data";
                }
          
        }
        else{
            $errors[] = "Invalid Server Id";
        }
        
        
        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Set Mac Address {$e->getMessage()} ");
    }
}

function rspanel_getreversedns($vars){
    try{
        $success = 0;
        $errors = [];
        $dnsRecord = '';
        $rsServerId = isset($vars['rsid']) ? $vars['rsid'] : 0;
        $ipAddressId = isset($_POST['ipAddressId']) ? $_POST['ipAddressId'] : '';
        $ipAddress = isset($_POST['ipAddress']) ? $_POST['ipAddress'] : '';
        

        $order = Capsule::table("mod_reliablesite_orders")
            ->select('id','rsServerId')
            ->where('hostingId', $hostingId)
            ->first();

        if(!empty($rsServerId)){

            
            $rsAPIObj = rs_getAPIObj();

            $getServerResp = $rsAPIObj->getServerDetails($rsServerId);

            if($getServerResp['success'] == true){

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
                    //get rDNS Record
                    $dnsRecordResp = $rsAPIObj->getReverseDNSRecord($ipAddressId, $ipAddress);

                    if($dnsRecordResp['success'] == true){
                        $success = 1;
                        $dnsRecord = $dnsRecordResp['data'];
                    }
                    else{
                        $errors = $dnsRecordResp['errors'];
                    }
                }
                else{
                    $errors[] = "No Access";
                }
            }
            else{
                $errors = $getServerResp['errors'];
            }

        }
        else{
            $errors[] = "Invalid Server Id";
        }
        
        
        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors, 'dnsRecord' => $dnsRecord));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Get Reverse DNS {$e->getMessage()} ");
    }
}

function rspanel_setreversedns($vars){
    try{
        $success = 0;
        $errors = [];
        $rsServerId = isset($vars['rsid']) ? $vars['rsid'] : 0;
        $ipAddressId = isset($_POST['ipAddressId']) ? $_POST['ipAddressId'] : '';
        $ipAddress = isset($_POST['ipAddress']) ? $_POST['ipAddress'] : '';
        $rDNSRecord = isset($_POST['rDNSRecord']) ? $_POST['rDNSRecord'] : '';
       

        if(!empty($rsServerId)){

          
                $rsAPIObj = rs_getAPIObj();
                
                $getServerResp = $rsAPIObj->getServerDetails($rsServerId);
                
                if($getServerResp['success'] == true){
                    
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
                        //Set rDNS Record
                        $dnsRecordResp = $rsAPIObj->setReverseDNSRecord($ipAddressId, $ipAddress, $rDNSRecord);
                        
                        if($dnsRecordResp['success'] == true){
                            $success = 1;
                        }
                        else{
                            $errors = $dnsRecordResp['errors'];
                        }
                    }
                    else{
                        $errors[] = "No Access";
                    }
                }
                else{
                    $errors = $getServerResp['errors'];
                }
            

        }
        else{
            $errors[] = "Invalid Server Id";
        }
        
        
        header('Content-Type: application/json');
        echo json_encode(array('success' => $success, 'errors' => $errors));
        exit();
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in Get Reverse DNS {$e->getMessage()} ");
    }
}


