<?php
class reliableSiteAPI{
    private $apiToken = '';
    
    private $apiUrl = 'https://dedicated-servers.reliablesite.dev/v2/';
    
    private $errors = [];
    
    /**
     * Constructor.
     *
     * @param apiToken
     */
    public function __construct($apiToken)
    {
        $this->apiToken = $apiToken;
    }
    
    public function setError($errors){
        $this->errors = $errors;
    }

    public function GetProfile(){
        
        $response = $this->callAPI('Account/GetProfile');
        
        return $response;
    }
    
    public function GetCustomerBackupStorage($serverId){
        $request = [
            'page' => 1,
            'filterField' => 'serverId',
            'search' => $serverId
        ];
        
        $response = $this->callAPI('Backup/GetCustomerBackupStorage', $request);
        
        return $response;
    }
    
    public function GetBackupStorageDetails($backupSpaceId){
        
        $response = $this->callAPI('Backup/'.$backupSpaceId);
        
        return $response;
    }
    
    public function SetFTPAccount($ftpAccountId, $enable, $password){
        $request = [
            'ftpAccountId' => $ftpAccountId,
            'enable' => $enable,
            'password' => $password
        ];
        
        $response = $this->callAPI('Backup/SetFTPAccount', $request, 'POST');
        
        return $response;
    }
    
    public function GetDDoSAttacks($page=1, $searchIp = ''){
        $request = [
            'page' => $page
        ];
        
        if(!empty($searchIp)){
            $request['searchIP'] = $searchIp;
        }
        
        $response = $this->callAPI('DDoS/GetDDoSAttacks', $request);
        
        return $response;
    }
    
    
    public function getNullRoutes($page=1, $searchIp = '', $onlyActive = 'false'){
        $request = [
            'page' => $page
        ];
        
        if(!empty($searchIp)){
            $request['searchIP'] = $searchIp;
        }

        if(!empty($onlyActive)){
            $request['onlyActive'] = $onlyActive;
        }
        
        $response = $this->callAPI('NullRoutes/GetNullRoutes', $request);
        
        return $response;
    }
    
    public function AddNullRoute($IPAddress, $scheduledRemove, $resellerLock ){
        $request = [
            'nullRouteIP' => $IPAddress,
            'scheduledRemove' => $scheduledRemove,
            'resellerLock' => $resellerLock
        ];
        $json = true;
        $response = $this->callAPI('NullRoutes/AddNullRoute', $request, 'POST', $json);
        
        return $response;
    }
    
    public function RemoveNullRoute($IPAddress){
        $request = [
            'IPAddress' => $IPAddress
        ];
        $response = $this->callAPI('NullRoutes/RemoveNullRoute', $request, 'DELETE');
        
        return $response;
    }
    
    public function getReverseDNSIPs($ipAddressId){
        
        $response = $this->callAPI("rDNS/{$ipAddressId}/GetIPs", []);
        
        return $response;
    }
    
    public function getReverseDNSRecord($ipAddressId, $ipAddress){
        $request = [
            'ipAddress' => $ipAddress
        ];
        
        $response = $this->callAPI("rDNS/{$ipAddressId}/rDNSRecord", $request);
        
        return $response;
    }
    
    public function setReverseDNSRecord($ipAddressId, $ipAddress, $rDNS){
        $request = [
            'ipAddress' => $ipAddress,
            'rDNS' => $rDNS
        ];
        
        $response = $this->callAPI("rDNS/{$ipAddressId}/rDNSRecord", $request, 'POST');
        
        return $response;
    }
    
    public function getServers($page, $showReassignServer = "true", $search = '', $filterField = ''){
        $request = [
            'page' => $page,
            'showReassignServer' => $showReassignServer
        ];
        
        if(!empty($search)){
            $request['search'] = $search;
        }
        
        if(!empty($filterField)){
            $request['filterField'] = $filterField;
        }
        $response = $this->callAPI('Server/GetServers', $request);
        
        return $response;
    }
    
    public function getServerDetails($serverId){
        
        $response = $this->callAPI('Server/'.$serverId);
        
        return $response;
    }
    
    public function ServerPoweOn($serverId){
        
        $response = $this->callAPI('Server/'.$serverId.'/PowerOn', [], 'POST');
        
        return $response;
    }
    
    public function ServerPoweOff($serverId){
        
        $response = $this->callAPI('Server/'.$serverId.'/PowerOff', [], 'POST');
        
        return $response;
    }
    
    public function getKVMDetails($serverId){
        
        $response = $this->callAPI('Server/GetKVMDetails/'.$serverId);
        
        return $response;
    }
    
    public function ServerEnableKVM($serverId, $remoteIP){
        $request = [
            'remoteIP' => $remoteIP
        ];
        
        $response = $this->callAPI('Server/'.$serverId.'/EnableKVM', $request, 'POST');
        
        return $response;
    }
    
    public function ServerDisableKVM($serverId){
               
        $response = $this->callAPI('Server/'.$serverId.'/DisableKVM', [], 'POST');
        
        return $response;
    }
    
    public function GetIPMISessions($serverId){
        
        $request = [
            'page' => 1,
            'filterField' => 'ServerId',
            'search' => $serverId
        ];
        
        $response = $this->callAPI('Server/GetIPMISessions', $request);
        
        return $response;
    }
    
    public function SetMacAddess($serverId, $ip, $isCustomMacEnable, $customMac ){
        
        $request = [
            'ip' => $ip,
            'isCustomMacEnable' => $isCustomMacEnable,
            'customMac' => $customMac
        ];
               
        $response = $this->callAPI('Server/'.$serverId.'/SetMacAddress', $request, 'POST');
        
        return $response;
    }
    
    public function GetCompatibleOS($serverId){
        
        $response = $this->callAPI('Server/'.$serverId.'/GetOSInstallCompatibleOS');
        
        return $response;
    }
    
    public function GetPartitioningSchemes($operatingSystemId){
        
        $response = $this->callAPI('Server/'.$operatingSystemId.'/GetCompatiblePartitioningSchemes');
        
        return $response;
    }
    
    public function OSInstallStart($serverId, $serverIP, $OSId, $partitioningSchemeId, $licenseKey=''){
        
        $request = [
            'serverIp' => $serverIP,
            'operatingSystemId' => $OSId,
            'partitioningSchemeId' => $partitioningSchemeId,
        ];
        
        if(!empty($licenseKey)){
            $request['licenseKey'] = $licenseKey; 
        }
        
        $response = $this->callAPI('Server/'.$serverId.'/OSInstallStart', $request, 'POST');
        
        return $response;
    }
    
    public function OSInstallCancel($serverId){
        
        $response = $this->callAPI('Server/'.$serverId.'/OSInstallCancel', [], 'DELETE');
        
        return $response;
    }
    
    public function GetOSInstallStatus($serverId){
        
        $response = $this->callAPI('Server/'.$serverId.'/OSInstallStatus');
        
        return $response;
    }
    
    public function GetBandwidthGraph($serverId, $period = 'Hour', $timeZone = ''){
        
        $request = [];
        
        if(!empty($period)){
            $request['period'] = $period;
        }
        
        if(!empty($timeZone)){
            $request['timeZone'] = $timeZone;
        }
        
        $response = $this->callAPI('Server/'.$serverId.'/BandwidthGraph', $request, 'GET');
        
        return $response;
    }
    
    public function assignServer($userName, $serverId){
        $request = [
            'username' => $userName,
            'serverId' => $serverId
        ];
        
        $response = $this->callAPI('Reseller/AssignServer', $request, 'POST');
        
        return $response;
    }
    
    public function unassignServer($serverId){
        $request = [
            'serverId' => $serverId
        ];
        
        $response = $this->callAPI('Reseller/UnassignServer', $request, 'POST');
        
        return $response;
    }
    
    public function addCustomer($username, $email, $password){
        $request = [
            'username' => $username,
            'email' => $email,
            'password' => $password
        ];
        
        $response = $this->callAPI('Reseller/AddCustomer', $request, 'POST');
        
        return $response;
    }
    
    public function updateCustomer($username, $email, $password){
        $request = [
            'username' => $username
        ];
        
        if(!empty($email)){
            $request['email'] = $email;
        }
        
        if(!empty($password)){
            $request['password'] = $password;
        }
        
        $response = $this->callAPI('Reseller/UpdateCustomer', $request, 'POST');
        
        return $response;
    }

    public function deleteCustomer($username){
        $request = [
            'username' => $username
        ];
        
        $response = $this->callAPI('Reseller/DeleteCustomer', $request, 'DELETE');
        
        return $response;
    }
    
    public function getCustomers($page = 1, $search = '', $filterField = ''){
        $search = trim($search);
        $filterField = trim($filterField);
        $request = [
            'page' => $page
        ];
        
        if(!empty($search)){
            $request['search'] = $search;
        }
        
        if(!empty($filterField)){
            $request['filterField'] = $filterField;
        }
        
        $response = $this->callAPI('Reseller/GetCustomers', $request);
        
        return $response;
    }
    
    
    
    
    private function callAPI($endPoint, $request = [], $method = 'GET', $json = false){
        
        
        $response = [
            'success' => false,
            'data' => '',
            'errors' => []
        ];
        
        if(empty($this->errors)){

            $url = $this->apiUrl.$endPoint;

            $headers = array(        
                'Authorization:'.'Bearer '.$this->apiToken
            );
            
            if($json == true){
                $headers[] = "Content-Type: application/json"; 
            }

            $jsonRequest = '';
            if($method == 'POST'){
                if($json == true){
                    $jsonRequest = json_encode($request);
                }
                $headers[] = "Content-Length: ".strlen($jsonRequest);
                
            }

            if($json == false){
                $url .= '?'.http_build_query($request);
            }


            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            if($method == 'POST'){
                curl_setopt($ch, CURLOPT_POST, 1);
                
                if($json == true){
                    curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonRequest);
                }
            }
            else if($method == 'DELETE'){
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            }

            //Execute the cURL request.
            $resp = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            /*echo '<pre>';
            print_r($resp);
            die();*/

            // Check for any curl errors or an empty response
            if(curl_error($ch)){
                $response['success'] = false;
                $response['errors'][] = curl_error($ch);

                logModuleCall(
                    'ReliableSite',
                    $endPoint,
                    $request,
                    $response
                );
            }
            else{
                $parsedResponse = json_decode($resp, true);
            }

            if(!$parsedResponse){
               
               
                $error = "Something went wrong : {$resp} : Code: $httpCode";
                if($httpCode == 405){
                     $error = "Method Not Allowed";
                }
                else if($resp == 'error code: 1015'){
                    $error = 'Exceeds the request rate limit. Please after some time';
                }
                
                $response['success'] = false;
                $response['errors'][]  = $error;
                logModuleCall(
                    'ReliableSite',
                    $endPoint,
                    $request,
                    $response
                );

            }
            else if($parsedResponse['status'] == 1){
                $response['success'] = true;
                $response['data'] = $parsedResponse['data'];
                
                if(!is_array($parsedResponse['data']) && empty($parsedResponse['data']) && !empty($parsedResponse['message']) && $parsedResponse['message'] != 'Successful'){
                    $response['data'] = $parsedResponse['message'];
                }
                
            }
            else{
                $response['success'] = false;

                $error = '';
                if(isset($parsedResponse['title'])){
                    $error = $parsedResponse['title'];
                }
                else if(isset($parsedResponse['message'])){
                    $error = $parsedResponse['message'];
                }

                $response['errors'][] = $error;

                logModuleCall(
                    'ReliableSite',
                    $endPoint,
                    $request,
                    $parsedResponse
                );
            }

            // We're done with curl so we can release the resource now
            curl_close($ch);
        }
        else{
             $response['errors'] = $this->errors;
        }
        
        return $response;
    }
}