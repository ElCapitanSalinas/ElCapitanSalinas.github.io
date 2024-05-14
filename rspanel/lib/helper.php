<?php

use WHMCS\Database\Capsule;

require_once __DIR__ . '/reliableSiteAPI.php';
function rs_getAPIObj(){
    
    $apiTokenResp = rs_getAPIToken();
    
    $apiToken = $apiTokenResp['apiToken'];
    $errors = $apiTokenResp['errors'];
    
    $apiObj = new reliableSiteAPI($apiToken);
    
    $apiObj->setError($errors);
    
    return $apiObj;
}

function rs_getAPIToken(){
    try{
        
        $return = [
            'success' => true,
            'apiToken' => '',
            'errors' => []
        ];
        
        $apiData = Capsule::table('tbladdonmodules')
            ->where('module', '=', 'rspanel')
            ->whereIn('setting',['apiKey', 'apiToken', 'apiTokenValidity'])    
            ->get(['setting','value']);


        $apiKey = '';
        $apiToken = '';
        $apiTokenExpiry = '';

        foreach($apiData as $data){
            if($data->setting == 'apiKey'){
                $apiKey = $data->value;
            }
            else if($data->setting == 'apiToken'){
                $apiToken = $data->value;
            }
            else if($data->setting == 'apiTokenValidity'){
                $apiTokenExpiry = $data->value;
            }
        }

        $gmtTimeStamp = strtotime(gmdate("Y-m-d\TH:i:s\Z")); /*Get UTC/GMT time*/

        if($apiTokenExpiry < $gmtTimeStamp){
            
            $url = 'https://dedicated-servers.reliablesite.dev/v2/Login/Token?ApiKey='.$apiKey;
            if(function_exists('curl_version')){
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: 0'));
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

                //Execute the cURL request.
                $curlResponse = curl_exec($ch);
                
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                $response = json_decode($curlResponse, true);
            
                
                if(curl_error($ch)){
                    $error = "Error in curl calling: ".curl_error($ch);
                    $return['success'] = false;
                    $return['errors'][] = $error;
                
                    logActivity("ReliableSite: Error in get API Token {$error}");
                    logModuleCall(
                        'ReliableSite',
                        $url,
                        null,
                        $response
                    );
                }
                else if(is_array($response)){

                    if($response['status'] == 1){

                        $apiToken = $response['message'];
                        $apiTokenExpiry = strtotime($response['tokenExpirationTimestamp']);
    
                        Capsule::table('tbladdonmodules')
                            ->where('module', 'rspanel')
                            ->where('setting', 'apiToken')
                            ->update(['value' => $apiToken]);
    
                        Capsule::table('tbladdonmodules')
                            ->where('module', 'rspanel')
                            ->where('setting', 'apiTokenValidity')
                            ->update(['value' => $apiTokenExpiry]);
                    }
                    else if($response['status'] == 400){
                        
                        $return['success'] = false;
                        $return['errors'][] = "Invalid API Key";
                        
                        logActivity("ReliableSite: Error in get API Token: Invalid API Key");
                        logModuleCall(
                            'ReliableSite',
                            $url,
                            null,
                            $response
                        );
                    }
                    else{
                        
                        $return['success'] = false;
                        $return['errors'][] = $response['message'];
                        
                        logActivity("ReliableSite: Error in get API Token {$response['message']}");
                        logModuleCall(
                            'ReliableSite',
                            $url,
                            null,
                            $response
                        );
                    }

                }
                else{
                    logActivity("ReliableSite: Error in get API Token: can not parse curl response:".var_export($curlResponse, true));
                    logModuleCall(
                        'ReliableSite',
                        $url,
                        null,
                        var_export($curlResponse, true)
                    );

                    $return['success'] = false;
                    $return['errors'][] = 'Can not parse curl response';
                }
                
            }
            else{
                $return['success'] = false;
                $return['errors'][] = 'Curl extension is not enabled';

                logActivity("ReliableSite: Curl extension is not enabled.");
                logModuleCall(
                    'ReliableSite',
                    $url,
                    null,
                    'Curl extension is not enabled.'
                );
            }

        }
        
        $return['apiToken'] = $apiToken;
        
        return $return;
    }
    catch (Exception $e){
        logActivity("ReliableSite: Error in {$e->getMessage()} ");
    }
}

function CidrToIpRange($ipv4, $gateway) {
    $ips = [];
    $cidr = explode('/', $ipv4);
    
    if(count($cidr) == 2){
        $prefix = $cidr[1];
        
        $ip_count = 1 << (32 - $prefix);
        
        $startRange = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int)$$prefix))));
        $endRange = long2ip((ip2long($cidr[0])) + pow(2, (32 - (int)$prefix)) - 1);
        
        $longStartRange = ip2long($startRange);
        $longEndRange = ip2long($endRange);
        
        $start = ip2long($gateway);
        
        for ($i = 1; $i < $ip_count; $i++) {
            $longIp = $start + $i;
            $ip = long2ip($longIp);
            if($longIp > $longStartRange &&  $longIp < $longEndRange){
               $ips[] = $ip; 
            }
            
        }
    }
    else{
        $ips[] = $ipv4;
    }
    
    return $ips;
}

function rsgetTimeZoneList(){
    $timeZones = array(
        1 => [ "utc" => "UTC-12:00", "timezone" => "(UTC-12:00) International Date Line West"],
        2 => [ "utc" => "UTC-11:00", "timezone" => "(UTC-11:00) Coordinated Universal Time-11"],
        3 => [ "utc" => "UTC-10:00", "timezone" => "(UTC-10:00) Aleutian Islands"],
        4 => [ "utc" => "UTC-10:00", "timezone" => "(UTC-10:00) Hawaii"],
        5 => [ "utc" => "UTC-09:30", "timezone" => "(UTC-09:30) Marquesas Islands"],
        6 => [ "utc" => "UTC-09:00", "timezone" => "(UTC-09:00) Alaska"],
        7 => [ "utc" => "UTC-09:00", "timezone" => "(UTC-09:00) Coordinated Universal Time-09"],
        8 => [ "utc" => "UTC-08:00", "timezone" => "(UTC-08:00) Baja California"],
        9 => [ "utc" => "UTC-08:00", "timezone" => "(UTC-08:00) Coordinated Universal Time-08"],
        10 => [ "utc" => "UTC-08:00", "timezone" => "(UTC-08:00) Pacific Time (US & Canada)"],
        11 => [ "utc" => "UTC-07:00", "timezone" => "(UTC-07:00) Arizona"],
        12 => [ "utc" => "UTC-07:00", "timezone" => "(UTC-07:00) Chihuahua, La Paz, Mazatlan"],
        13 => [ "utc" => "UTC-07:00", "timezone" => "(UTC-07:00) Mountain Time (US & Canada)"],
        14 => [ "utc" => "UTC-06:00", "timezone" => "(UTC-06:00) Central America"],
        15 => [ "utc" => "UTC-06:00", "timezone" => "(UTC-06:00) Central Time (US & Canada)"],
        16 => [ "utc" => "UTC-06:00", "timezone" => "(UTC-06:00) Easter Island"],
        17 => [ "utc" => "UTC-06:00", "timezone" => "(UTC-06:00) Guadalajara, Mexico City, Monterrey"],
        18 => [ "utc" => "UTC-06:00", "timezone" => "(UTC-06:00) Saskatchewan"],
        19 => [ "utc" => "UTC-05:00", "timezone" => "(UTC-05:00) Bogota, Lima, Quito, Rio Branco"],
        20 => [ "utc" => "UTC-05:00", "timezone" => "(UTC-05:00) Chetumal"],
        21 => [ "utc" => "UTC-05:00", "timezone" => "(UTC-05:00) Eastern Time (US & Canada)"],
        22 => [ "utc" => "UTC-05:00", "timezone" => "(UTC-05:00) Haiti"],
        23 => [ "utc" => "UTC-05:00", "timezone" => "(UTC-05:00) Havana"],
        24 => [ "utc" => "UTC-05:00", "timezone" => "(UTC-05:00) Indiana (East)"],
        25 => [ "utc" => "UTC-05:00", "timezone" => "(UTC-05:00) Turks and Caicos"],
        26 => [ "utc" => "UTC-04:00", "timezone" => "(UTC-04:00) Asuncion"],
        27 => [ "utc" => "UTC-04:00", "timezone" => "(UTC-04:00) Atlantic Time (Canada)"],
        28 => [ "utc" => "UTC-04:00", "timezone" => "(UTC-04:00) Caracas"],
        29 => [ "utc" => "UTC-04:00", "timezone" => "(UTC-04:00) Cuiaba"],
        30 => [ "utc" => "UTC-04:00", "timezone" => "(UTC-04:00) Georgetown, La Paz, Manaus, San Juan"],
        31 => [ "utc" => "UTC-04:00", "timezone" => "(UTC-04:00) Santiago"],
        32 => [ "utc" => "UTC-03:30", "timezone" => "(UTC-03:30) Newfoundland"],
        33 => [ "utc" => "UTC-03:00", "timezone" => "(UTC-03:00) Araguaina"],
        34 => [ "utc" => "UTC-03:00", "timezone" => "(UTC-03:00) Brasilia"],
        35 => [ "utc" => "UTC-03:00", "timezone" => "(UTC-03:00) Cayenne, Fortaleza"],
        36 => [ "utc" => "UTC-03:00", "timezone" => "(UTC-03:00) City of Buenos Aires"],
        37 => [ "utc" => "UTC-03:00", "timezone" => "(UTC-03:00) Greenland"],
        38 => [ "utc" => "UTC-03:00", "timezone" => "(UTC-03:00) Montevideo"],
        39 => [ "utc" => "UTC-03:00", "timezone" => "(UTC-03:00) Punta Arenas"],
        40 => [ "utc" => "UTC-03:00", "timezone" => "(UTC-03:00) Saint Pierre and Miquelon"],
        41 => [ "utc" => "UTC-03:00", "timezone" => "(UTC-03:00) Salvador"],
        42 => [ "utc" => "UTC-02:00", "timezone" => "(UTC-02:00) Coordinated Universal Time-02"],
        43 => [ "utc" => "UTC-02:00", "timezone" => "(UTC-02:00) Mid-Atlantic - Old"],
        44 => [ "utc" => "UTC-01:00", "timezone" => "(UTC-01:00) Azores"],
        45 => [ "utc" => "UTC-01:00", "timezone" => "(UTC-01:00) Cabo Verde Is."],
        46 => [ "utc" => "UTC", "timezone" => "(UTC) Coordinated Universal Time"],
        47 => [ "utc" => "UTC+00:00", "timezone" => "(UTC+00:00) Dublin, Edinburgh, Lisbon, London"],
        48 => [ "utc" => "UTC+00:00", "timezone" => "(UTC+00:00) Monrovia, Reykjavik"],
        49 => [ "utc" => "UTC+00:00", "timezone" => "(UTC+00:00) Sao Tome"],
        50 => [ "utc" => "UTC+01:00", "timezone" => "(UTC+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna"],
        51 => [ "utc" => "UTC+01:00", "timezone" => "(UTC+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague"],
        52 => [ "utc" => "UTC+01:00", "timezone" => "(UTC+01:00) Brussels, Copenhagen, Madrid, Paris"],
        53 => [ "utc" => "UTC+01:00", "timezone" => "(UTC+01:00) Casablanca"],
        54 => [ "utc" => "UTC+01:00", "timezone" => "(UTC+01:00) Sarajevo, Skopje, Warsaw, Zagreb"],
        55 => [ "utc" => "UTC+01:00", "timezone" => "(UTC+01:00) West Central Africa"],
        56 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Amman"],
        57 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Athens, Bucharest"],
        58 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Beirut"],
        59 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Cairo"],
        60 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Chisinau"],
        61 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Damascus"],
        62 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Gaza, Hebron"],
        63 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Harare, Pretoria"],
        64 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius"],
        65 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Jerusalem"],
        66 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Kaliningrad"],
        67 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Khartoum"],
        68 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Tripoli"],
        69 => [ "utc" => "UTC+02:00", "timezone" => "(UTC+02:00) Windhoek"],
        70 => [ "utc" => "UTC+03:00", "timezone" => "(UTC+03:00) Baghdad"],
        71 => [ "utc" => "UTC+03:00", "timezone" => "(UTC+03:00) Istanbul"],
        72 => [ "utc" => "UTC+03:00", "timezone" => "(UTC+03:00) Kuwait, Riyadh"],
        73 => [ "utc" => "UTC+03:00", "timezone" => "(UTC+03:00) Minsk"],
        74 => [ "utc" => "UTC+03:00", "timezone" => "(UTC+03:00) Moscow, St. Petersburg"],
        75 => [ "utc" => "UTC+03:00", "timezone" => "(UTC+03:00) Nairobi"],
        76 => [ "utc" => "UTC+03:30", "timezone" => "(UTC+03:30) Tehran"],
        77 => [ "utc" => "UTC+04:00", "timezone" => "(UTC+04:00) Abu Dhabi, Muscat"],
        78 => [ "utc" => "UTC+04:00", "timezone" => "(UTC+04:00) Astrakhan, Ulyanovsk"],
        79 => [ "utc" => "UTC+04:00", "timezone" => "(UTC+04:00) Baku"],
        80 => [ "utc" => "UTC+04:00", "timezone" => "(UTC+04:00) Izhevsk, Samara"],
        81 => [ "utc" => "UTC+04:00", "timezone" => "(UTC+04:00) Port Louis"],
        82 => [ "utc" => "UTC+04:00", "timezone" => "(UTC+04:00) Saratov"],
        83 => [ "utc" => "UTC+04:00", "timezone" => "(UTC+04:00) Tbilisi"],
        84 => [ "utc" => "UTC+04:00", "timezone" => "(UTC+04:00) Volgograd"],
        85 => [ "utc" => "UTC+04:00", "timezone" => "(UTC+04:00) Yerevan"],
        86 => [ "utc" => "UTC+04:30", "timezone" => "(UTC+04:30) Kabul"],
        87 => [ "utc" => "UTC+05:00", "timezone" => "(UTC+05:00) Ashgabat, Tashkent"],
        88 => [ "utc" => "UTC+05:00", "timezone" => "(UTC+05:00) Ekaterinburg"],
        89 => [ "utc" => "UTC+05:00", "timezone" => "(UTC+05:00) Islamabad, Karachi"],
        90 => [ "utc" => "UTC+05:00", "timezone" => "(UTC+05:00) Qyzylorda"],
        91 => [ "utc" => "UTC+05:30", "timezone" => "(UTC+05:30) Chennai, Kolkata, Mumbai, New Delhi"],
        92 => [ "utc" => "UTC+05:30", "timezone" => "(UTC+05:30) Sri Jayawardenepura"],
        93 => [ "utc" => "UTC+05:45", "timezone" => "(UTC+05:45) Kathmandu"],
        94 => [ "utc" => "UTC+06:00", "timezone" => "(UTC+06:00) Astana"],
        95 => [ "utc" => "UTC+06:00", "timezone" => "(UTC+06:00) Dhaka"],
        96 => [ "utc" => "UTC+06:00", "timezone" => "(UTC+06:00) Omsk"],
        97 => [ "utc" => "UTC+06:30", "timezone" => "(UTC+06:30) Yangon (Rangoon)"],
        98 => [ "utc" => "UTC+07:00", "timezone" => "(UTC+07:00) Bangkok, Hanoi, Jakarta"],
        99 => [ "utc" => "UTC+07:00", "timezone" => "(UTC+07:00) Barnaul, Gorno-Altaysk"],
        100 => [ "utc" => "UTC+07:00", "timezone" => "(UTC+07:00) Hovd"],
        101 => [ "utc" => "UTC+07:00", "timezone" => "(UTC+07:00) Krasnoyarsk"],
        102 => [ "utc" => "UTC+07:00", "timezone" => "(UTC+07:00) Novosibirsk"],
        103 => [ "utc" => "UTC+07:00", "timezone" => "(UTC+07:00) Tomsk"],
        104 => [ "utc" => "UTC+08:00", "timezone" => "(UTC+08:00) Beijing, Chongqing, Hong Kong, Urumqi"],
        105 => [ "utc" => "UTC+08:00", "timezone" => "(UTC+08:00) Irkutsk"],
        106 => [ "utc" => "UTC+08:00", "timezone" => "(UTC+08:00) Kuala Lumpur, Singapore"],
        107 => [ "utc" => "UTC+08:00", "timezone" => "(UTC+08:00) Perth"],
        108 => [ "utc" => "UTC+08:00", "timezone" => "(UTC+08:00) Taipei"],
        109 => [ "utc" => "UTC+08:00", "timezone" => "(UTC+08:00) Ulaanbaatar"],
        110 => [ "utc" => "UTC+08:45", "timezone" => "(UTC+08:45) Eucla"],
        111 => [ "utc" => "UTC+09:00", "timezone" => "(UTC+09:00) Chita"],
        112 => [ "utc" => "UTC+09:00", "timezone" => "(UTC+09:00) Osaka, Sapporo, Tokyo"],
        113 => [ "utc" => "UTC+09:00", "timezone" => "(UTC+09:00) Pyongyang"],
        114 => [ "utc" => "UTC+09:00", "timezone" => "(UTC+09:00) Seoul"],
        115 => [ "utc" => "UTC+09:00", "timezone" => "(UTC+09:00) Yakutsk"],
        116 => [ "utc" => "UTC+09:30", "timezone" => "(UTC+09:30) Adelaide"],
        117 => [ "utc" => "UTC+09:30", "timezone" => "(UTC+09:30) Darwin"],
        118 => [ "utc" => "UTC+10:00", "timezone" => "(UTC+10:00) Brisbane"],
        119 => [ "utc" => "UTC+10:00", "timezone" => "(UTC+10:00) Canberra, Melbourne, Sydney"],
        120 => [ "utc" => "UTC+10:00", "timezone" => "(UTC+10:00) Guam, Port Moresby"],
        121 => [ "utc" => "UTC+10:00", "timezone" => "(UTC+10:00) Hobart"],
        122 => [ "utc" => "UTC+10:00", "timezone" => "(UTC+10:00) Vladivostok"],
        123 => [ "utc" => "UTC+10:30", "timezone" => "(UTC+10:30) Lord Howe Island"],
        124 => [ "utc" => "UTC+11:00", "timezone" => "(UTC+11:00) Bougainville Island"],
        125 => [ "utc" => "UTC+11:00", "timezone" => "(UTC+11:00) Chokurdakh"],
        126 => [ "utc" => "UTC+11:00", "timezone" => "(UTC+11:00) Magadan"],
        127 => [ "utc" => "UTC+11:00", "timezone" => "(UTC+11:00) Norfolk Island"],
        128 => [ "utc" => "UTC+11:00", "timezone" => "(UTC+11:00) Sakhalin"],
        129 => [ "utc" => "UTC+11:00", "timezone" => "(UTC+11:00) Solomon Is., New Caledonia"],
        130 => [ "utc" => "UTC+12:00", "timezone" => "(UTC+12:00) Anadyr, Petropavlovsk-Kamchatsky"],
        131 => [ "utc" => "UTC+12:00", "timezone" => "(UTC+12:00) Auckland, Wellington"],
        132 => [ "utc" => "UTC+12:00", "timezone" => "(UTC+12:00) Coordinated Universal Time+12"],
        133 => [ "utc" => "UTC+12:00", "timezone" => "(UTC+12:00) Fiji"],
        134 => [ "utc" => "UTC+12:00", "timezone" => "(UTC+12:00) Petropavlovsk-Kamchatsky - Old"],
        135 => [ "utc" => "UTC+12:45", "timezone" => "(UTC+12:45) Chatham Islands"],
        136 => [ "utc" => "UTC+13:00", "timezone" => "(UTC+13:00) Coordinated Universal Time+13"],
        137 => [ "utc" => "UTC+13:00", "timezone" => "(UTC+13:00) Nuku'alofa"],
        138 => [ "utc" => "UTC+13:00", "timezone" => "(UTC+13:00) Samoa"],
        139 => [ "utc" => "UTC+14:00", "timezone" => "(UTC+14:00) Kiritimati Island"]
    );
    
    return $timeZones;
}



