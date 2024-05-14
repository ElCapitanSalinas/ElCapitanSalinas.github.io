<?php

namespace WHMCS\Module\Addon\rspanel\Client;
use WHMCS\Database\Capsule;
/**
 * Sample Client Area Controller
 */
class Controller{

    /**
     * Index action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return array
     */
    public function index($vars)
    {
      
        // Get common module parameters
        $modulelink = $vars['modulelink']; // eg. addonmodules.php?module=addonmodule
       
        $error = '';
        $serviceId = $vars['sid'];
        $serverDetail = [];
        $ipAddressList = [];
        
         $order = Capsule::table("mod_reliablesite_orders")
            ->select('rsServerId')
            ->where('hostingId', $serviceId)
            ->first();
         
        if($order){
        
            //get server details
            $apiObj = rs_getAPIObj();
            $getServerResp = $apiObj->getServerDetails($order->rsServerId);




            if($getServerResp['success'] == true){
                $serverDetail = $getServerResp['data']['server'];
                $ipAddressList = $getServerResp['data']['ipAddressList'];
            }
            else{
                $error = $getServerResp['errors'][0];
            }
        }
        else{
            $error = "No Server Assigned";
        }
        
        return array(
            'pagetitle' => 'Manage Server',
            'breadcrumb' => array(
                'clientarea.php?action=productdetails&id='.$vars['sid'] => 'Product Details',
                'index.php?m=rspanel&sid='.$vars['sid'] => 'Manage Server',
            ),
            'templatefile' => 'index',
            'requirelogin' => true, // Set true to restrict access to authenticated client users
            'vars' => array(
                'modulelink' => $modulelink,
                'sid' => $vars['sid'],
                'error' => $error,
                'serverDetail' => $serverDetail,
                'ipAddressList' => $ipAddressList
            ),
        );
    }

    /**
     * Manage OS action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return array
     */
    public function manageos($vars)
    {
        // Get common module parameters
        $modulelink = $vars['modulelink'];
       
        $error = '';
        $serviceId = $vars['sid'];
        $isInstalling = false;
        $installationStatus = '';
        $serverDetail = [];
        $ipAddressList = [];
        $compitableOS = [];
        
        $order = Capsule::table("mod_reliablesite_orders")
            ->select('rsServerId')
            ->where('hostingId', $serviceId)
            ->first();
         
        $apiObj = rs_getAPIObj();
        
        //get server details
        $getServerResp = $apiObj->getServerDetails($order->rsServerId);
        
        if($getServerResp['success'] == true){
            $serverDetail = $getServerResp['data']['server'];
            $ipAddressList = $getServerResp['data']['ipAddressList'];
        }
        else{
            $error = $getServerResp['errors'][0];
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
        
        //check installation status
        $installStatusResp = $apiObj->GetOSInstallStatus($order->rsServerId);
        
        if($installStatusResp['success'] == true){
            $isInstalling = true;
            $installationStatus = $installStatusResp['data'];
        }
        
        if(!$isInstalling){
            //get OS list
            $getOSResp = $apiObj->GetCompatibleOS($order->rsServerId);

            if($getOSResp['success'] == true){
                $compitableOS = $getOSResp['data'];
            }
            else{
                $error = $getOSResp['errors'][0];
            }
        }
        
        
        return array(
            'pagetitle' => 'Install/Re-Install Operating System',
            'breadcrumb' => array(
                'clientarea.php?action=productdetails&id='.$vars['sid'] => 'Product Details',
                'index.php?m=rspanel&sid='.$vars['sid'] => 'Manage Server',
                'index.php?m=rspanel&action=manageos&sid='.$vars['sid'] => 'Manage OS',
            ),
            'templatefile' => 'manageos',
            'requirelogin' => true, // Set true to restrict access to authenticated client users
            'vars' => array(
                'modulelink' => $modulelink,
                'sid' => $vars['sid'],
                'error' => $error,
                'isInstalling' => $isInstalling,
                'installationStatus' => $installationStatus,
                'serverDetail' => $serverDetail,
                'ipAddressData' => $ipAddressData,
                'ipAddressDataJson' => json_encode($ipAddressData),
                'compitableOS' => $compitableOS
            ),
        );
    }
    
    /**
     * Manage KVM action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return array
     */
    public function managekvm($vars)
    {
        
        // Get common module parameters
        $modulelink = $vars['modulelink'];
       
        $error = '';
        $serviceId = $vars['sid'];
        
        $kvmDetails = [];
        $IPMISessions = [];
        $yourIP = (!empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : $_SERVER['REMOTE_HOST']);
        $order = Capsule::table("mod_reliablesite_orders")
            ->select('rsServerId', 'rsServerName')
            ->where('hostingId', $serviceId)
            ->first();
         
        $apiObj = rs_getAPIObj();
        
        //get KVM Details
        $getKVMResp = $apiObj->getKVMDetails($order->rsServerId);

        if($getKVMResp['success'] == true){
            $kvmDetails = $getKVMResp['data'];
            
            //get IPMI Sessions
            $getIPMISessionResp = $apiObj->GetIPMISessions($order->rsServerId);
           
            if($getIPMISessionResp['success'] == true){
                $IPMISessions = $getIPMISessionResp['data'];
            }
            else{
               $error = $getIPMISessionResp['errors'][0];
            }
            
        }
        else{
            $error = $getKVMResp['errors'][0];
        }
        
        return array(
            'pagetitle' => 'Manage KVM',
            'breadcrumb' => array(
                'clientarea.php?action=productdetails&id='.$vars['sid'] => 'Product Details',
                'index.php?m=rspanel&sid='.$vars['sid'] => 'Manage Server',
                'index.php?m=rspanel&action=managekvm&sid='.$vars['sid'] => 'Manage KVM',
            ),
            'templatefile' => 'managekvm',
            'requirelogin' => true, // Set true to restrict access to authenticated client users
            'vars' => array(
                'modulelink' => $modulelink,
                'sid' => $vars['sid'],
                'error' => $error,
                'serverName' => $order->rsServerName,
                'yourIP' => $yourIP,
                'kvmDetails' => $kvmDetails,
                'IPMISessions' => $IPMISessions
            ),
        );
    }
    
    /**
     * Manage KVM action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return array
     */
    public function bwstats($vars)
    {
        // Get common module parameters
        $modulelink = $vars['modulelink'];
        
        $period = (isset($_GET['period']) ? $_GET['period'] : 'hour');
        $timezone = (isset($_GET['tz']) ? $_GET['tz'] : '46');
        
        $timezones = rsgetTimeZoneList();
        
        $timezoneOffset = 'UTC';
        if(isset($timezones[$timezone])){
            $timezoneOffset = $timezones[$timezone]['utc'];
        }
        
        $periods = ['hour' => 'Last Hour', 'day' => 'Last Day', 'month' => 'Last Month', 'year' => 'Last Year'];
        $periodDateFormats = [
           'hour' => 'H:i:s', 'day' => 'H:i:s', 'month' => 'Y-m-d', 'year' => 'Y-m-d'
        ];
       
        $error = '';
        $serviceId = $vars['sid'];
        
        $chartTitle = '';
        $chartData = [];
        
        
        $order = Capsule::table("mod_reliablesite_orders")
            ->select('rsServerId', 'rsServerName')
            ->where('hostingId', $serviceId)
            ->first();
         
        $apiObj = rs_getAPIObj();
        
        //get Bandwith Graph
        $getBandwidthResp = $apiObj->GetBandwidthGraph($order->rsServerId, $period, $timezoneOffset);
        
        /*echo "<pre>";
        print_r($getBandwidthResp);
        die();*/
        
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
            $error = $getBandwidthResp['errors'][0];
        }
        
        return array(
            'pagetitle' => 'Bandwidth Stats',
            'breadcrumb' => array(
                'clientarea.php?action=productdetails&id='.$vars['sid'] => 'Product Details',
                'index.php?m=rspanel&sid='.$vars['sid'] => 'Manage Server',
                'index.php?m=rspanel&action=bwstats&sid='.$vars['sid'] => 'Bandwidth Stats',
            ),
            'templatefile' => 'bwstats',
            'requirelogin' => true, // Set true to restrict access to authenticated client users
            'vars' => array(
                'modulelink' => $modulelink,
                'pagelink' => $modulelink.'&sid='.$vars['sid'].'&action=bwstats',
                'sid' => $vars['sid'],
                'period' => $period,
                'periods' => $periods,
                'timezone' => $timezone,
                'timezones' => $timezones,
                'error' => $error,
                'serverName' => $order->rsServerName,
                'chartTitle' => $chartTitle,
                'chartData' => json_encode($chartData)
            ),
        );
    }
    
    /**
     * Backup Storage action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return array
     */
    public function backupstorage($vars)
    {
        
        // Get common module parameters
        $modulelink = $vars['modulelink'];
       
        $error = '';
        $serviceId = $vars['sid'];
        
        $backupStorageDetails = [];
        
        $order = Capsule::table("mod_reliablesite_orders")
            ->select('rsServerId', 'rsServerName')
            ->where('hostingId', $serviceId)
            ->first();
         
        $apiObj = rs_getAPIObj();
        
        //get KVM Details
        $getBackupStorageResp = $apiObj->GetCustomerBackupStorage($order->rsServerId);
        
        if($getBackupStorageResp['success'] == true){
            if(!empty($getBackupStorageResp['data']) && isset($getBackupStorageResp['data'][0]['backupSpaceId'])){
                $backupSpaceId = $getBackupStorageResp['data'][0]['backupSpaceId'];
                
                /*Get backup storage details*/
                $getBackupStorageDetailsResp = $apiObj->GetBackupStorageDetails($backupSpaceId);
                if($getBackupStorageDetailsResp['success'] == true){
                    
                    $backupStorageDetails = $getBackupStorageDetailsResp['data'];

                }
                else{
                    $error = $getBackupStorageDetailsResp['errors'][0];
                }
            }
            else{
                $error = "No Backup storage found";
            }
            
        }
        else{
            $error = $getBackupStorageResp['errors'][0];
        }
        
        return array(
            'pagetitle' => 'Manage Backup Storage',
            'breadcrumb' => array(
                'clientarea.php?action=productdetails&id='.$vars['sid'] => 'Product Details',
                'index.php?m=rspanel&sid='.$vars['sid'] => 'Manage Server',
                'index.php?m=rspanel&action=backupstorage&sid='.$vars['sid'] => 'Backup Storage',
            ),
            'templatefile' => 'backupstorage',
            'requirelogin' => true, // Set true to restrict access to authenticated client users
            'vars' => array(
                'modulelink' => $modulelink,
                'sid' => $vars['sid'],
                'error' => $error,
                'serverName' => $order->rsServerName,
                'backupStorage' => $backupStorageDetails
            ),
        );
    }
    
    /**
     * Manage OS action.
     *
     * @param array $vars Module configuration parameters
     *
     * @return array
     */
    public function reversedns($vars)
    {
        // Get common module parameters
        $modulelink = $vars['modulelink'];
       
        $error = '';
        $serviceId = $vars['sid'];
        $ipAddressId = (isset($_GET['ipid']) ? $_GET['ipid'] : '');
        $serverDetail = [];
        $rDNSIPs = [];
        
        
        $order = Capsule::table("mod_reliablesite_orders")
            ->select('rsServerId')
            ->where('hostingId', $serviceId)
            ->first();
         
        $apiObj = rs_getAPIObj();
        
        //get server details
        $getServerResp = $apiObj->getServerDetails($order->rsServerId);
        
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
                    $error = $getReverseDNSIPResp['errors'][0];
                }
            }
            else{
                $error = "No Access";
            }
        }
        else{
            $error = $getServerResp['errors'][0];
        }
        
        return array(
            'pagetitle' => 'Reverse DNS',
            'breadcrumb' => array(
                'clientarea.php?action=productdetails&id='.$vars['sid'] => 'Product Details',
                'index.php?m=rspanel&sid='.$vars['sid'] => 'Manage Server',
                'index.php?m=rspanel&action=reversedns&sid='.$vars['sid'] => 'Reverse DNS',
            ),
            'templatefile' => 'reversedns',
            'requirelogin' => true, // Set true to restrict access to authenticated client users
            'vars' => array(
                'modulelink' => $modulelink,
                'sid' => $vars['sid'],
                'ipAddressId' => $ipAddressId,
                'error' => $error,
                'serverDetail' => $serverDetail,
                'rDNSIPs' => $rDNSIPs
            ),
        );
    }
    
    public function noaccess($vars)
    {
        return array(
            'pagetitle' => 'No Access',
            'breadcrumb' => array(
                'index.php?m=rspanel' => 'No Access'
            ),
            'templatefile' => 'noaccess',
            'requirelogin' => false, // Set true to restrict access to authenticated client users
            'vars' => array(),
        );
    }
}
