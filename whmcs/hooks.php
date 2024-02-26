<?php
use WHMCS\Database\Capsule;
require_once __DIR__ . '/lib/helper.php';
add_hook('ClientAreaProductDetailsOutput', 1, function($service) {
    if (!is_null($service)) {
        
        $hostingId = $service['service']->id;
        $serviceStatus = $service['service']->domainstatus;
        
        if(strtolower($serviceStatus) == 'active'){
            $order = Capsule::table("mod_reliablesite_orders")
                        ->select('id','rsServerId')
                        ->where('hostingId', $hostingId)
                        ->first();

            if($order && !empty($order->rsServerId)){
                $html = "<div class='card'>"
                        . "<div class='card-body'>"
                        . "<h4>Manage Server</h4>"
                        ."<div class='row'>
                                        <div class='col-sm-5 text-right'>
                                            <strong>{$service['service']->product->name}</strong>
                                        </div>
                                        <div class='col-sm-7 text-left'>
                                            <a class='btn btn-primary' href='index.php?m=rspanel&sid={$hostingId}'>Manage Server</a>
                                        </div>
                                    </div>"
                        . ""
                        . "</div>";

                return $html;
            }
        }
    }
    return '';
});

add_hook('AdminClientServicesTabFields', 1, function($vars) {
   
   global $CONFIG;
   
    $hostingId = $vars['id'];
    
    $service = \WHMCS\Service\Service::find($hostingId);
    
    if($service->getProvisioningModuleName() == 'autorelease'){
    
        $rsServerId = Capsule::table('mod_reliablesite_orders')
                ->where('hostingId', '=', $hostingId)
                ->value('rsServerId');

        if(!empty($rsServerId)){
            $manageUrl = 'addonmodules.php?module=rspanel&action=manageserver&rsid='.$rsServerId;
            $html .= '<a class="pl-2 btn btn-primary" href="'.$manageUrl.'">Manage Servers</a>';
        }
        else{
            $manageUrl = 'addonmodules.php?module=rspanel&action=servers';
            $html .= '<a class="ml-3 btn btn-primary" href="'.$manageUrl.'">Assign Server</a>';
        }
        
        return [
            'ReliableSite Server' => $html
        ];
    }
});

