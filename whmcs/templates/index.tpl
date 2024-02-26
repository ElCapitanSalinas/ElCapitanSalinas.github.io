{if $error == ''}
<div class="card">
    <div class="card-body extra-padding">
        <h3 class="mb-3">Server Information</h3>
        <h6>Configuration and Status</h6>
        <hr>
        

        <div class="row">
            <div class="col-sm-3">
                <strong>Server Name</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['serverLabel']}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Data Center</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['dataCenterLabel']}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Status</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['status']}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Processor</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['cpuName']}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Memory</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['memorySize']} {$serverDetail['memoryType']}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Hard Drive 1</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['hardDrive1']}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Hard Drive 2</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['hardDrive2']}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Hard Drive 3</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['hardDrive3']}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Hard Drive 4</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['hardDrive4']}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>RAID</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['raidName']}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Primary NIC</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['primaryNIC']}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Port Speed</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['portSpeed']}
            </div>
        </div>    
        <h6 class="mt-4">OS and Login Credentials </h6>
        <hr>
        <div class="row">
            <div class="col-sm-3">
                <strong>Operating System</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['operatingSystem']} <a class="btn btn-primary btn-sm" href="{$modulelink}&action=manageos&sid={$sid}">Re-Install</a>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Username</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['username']}
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Password</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['password']}
            </div>
        </div>

        <h6 class="mt-4">Bandwidth Usage Summary - Last 30 Days  </h6>
        <hr>
        <div class="row">
            <div class="col-sm-3">
                <strong>BW Used</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['bandwidthUsed']} GB
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Full Speed BW</strong>
            </div>
            <div class="col-sm-7">
                {$serverDetail['bandwidthAllowed']} GB
            </div>
        </div>

        <div class="row">
            <div class="col-sm-3">
                <strong>Remote Reboot</strong>
            </div>
            <div class="col-sm-7">
                <input id="btnPowerOn" type="button" class="btn btn-primary btn-sm" value="Power On"> <input id="btnPowerOff" type="button" class="btn btn-primary btn-sm" value="Power Off">
            </div>
        </div>    

        <hr>
        <div class="row">
            <div class="col-sm-12">
                <a class="btn btn-primary" href="{$modulelink}&sid={$sid}&action=managekvm">Manage KVM</a>
                <a class="btn btn-primary" href="{$modulelink}&sid={$sid}&action=bwstats">Bandwidth Stats</a>
                <a class="btn btn-primary" href="{$modulelink}&sid={$sid}&action=backupstorage">Backup Storage</a>
            </div>
        </div>
    </div>    
</div>
{if count($ipAddressList) > 0}    
<div class="card">
    <div class="card-body extra-padding">
        <h3 class="mb-3">Server IP Assignment</h3>
        <hr>
         
            
        {if $serverDetail['serverMac']}
            <div class="row">
                <div class="col-sm-3">
                    <strong>Server MAC</strong>
                </div>
                <div class="col-sm-7">
                    {$serverDetail['serverMac']}
                </div>
            </div>
        {/if}

        <div class="row mt-2">
            <div class="col-sm-3">
                <strong>IP Address</strong>
            </div>
           <div class="col-sm-7">
                <select class="form-control select-inline" name="serverIP" id="serverIP" onchange="setIPAddressData(this)">
                    {foreach from=$ipAddressList item=item}
                        <option value="{$item.ipDescription}" data-custommac="{$item.customMac}" data-ipaddressid="{$item.ipAddressId}">{$item.ipDescription}</option>
                    {/foreach}
                </select>
                <a id="btnReverseDNS" class="btn btn-primary btn-sm" href="">Reverse DNS</a>
            </div>
        </div>
                    
        {if $serverDetail['serverMac']}
            <div class="row mt-2">
                <div class="col-sm-3">
                    <strong>MAC Address</strong>
                </div>
                <div class="col-sm-7">
                    <input type="radio" name="macAddrType" id="macAddrTypeServer" value="server"> <label for="macAddrTypeServer">Use Server</label>
                    <input type="radio" name="macAddrType" id="macAddrTypeCustom" value="custom"> <label for="macAddrTypeCustom">Use Custom</label>
                </div>
            </div>

            <div class="row mt-2" id="customMACBox">
                <div class="col-sm-3">
                    <strong>Custom MAC</strong>
                </div>
                <div class="col-sm-4">
                    <input type="text" class="form-control" name="customMAC" id="customMAC">
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-sm-3">                        
                </div>
                <div class="col-sm-4">
                    <input type="button" class="btn btn-primary" id="btnUpdateMAC" value="Update MAC">
                </div>
            </div>
        {/if}
        <div class="row mt-4">
            <div class="col-12">Total IPs: {count($ipAddressList)}</div>
            <table class="table table-list dataTable no-footer">
                <thead>
                <tr>
                    <th>#</th>
                    <th>IP</th>
                    <th>Gateway</th>
                    <th>Subnet</th>
                    <th>Custom MAC</th>
                </tr>
                </thead>
                <tbody>
                    {foreach from=$ipAddressList key=key item=item}
                        <tr>
                            <td class="text-center">{$key + 1}</td>
                            <td class="text-center">{$item.ipDescription}</td>
                            <td class="text-center">{$item.ipGateway}</td>
                            <td class="text-center">{$item.ipSubnet}</td>
                            <td class="text-center">{$item.customMac}</td>
                        </tr>    
                    {/foreach}
                </tbody>    
            </table>    
        </div> 
        
    </div>
</div>    
{/if}
<script type="text/javascript">
            jQuery(document).ready(function(){

                jQuery("input#btnPowerOn").click(function(e){
                    if (confirm("Are you sure you want to Power On the server? This may cause data loss or corruption.") == true) {
                        var btn = jQuery(this);

                        btn.attr('disabled',true);
                        var oldBtnHtml = btn.val();

                        btn.val('Processing...');


                        var url =  "{$modulelink}&sid={$sid}&action=dopoweron";

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


                        var url =  "{$modulelink}&sid={$sid}&action=dopoweroff";

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


                        var url = "{$modulelink}&sid={$sid}&action=setmacaddress";
                        var ip = $("select#serverIP").val();
                        var macAddrType = $("input[name='macAddrType']:checked").val();
                        var customMAC = $("input#customMAC").val();

                        $.ajax({
                            url : url,
                            method : "post",
                            data : {literal}{{/literal}ip : ip, macAddrType : macAddrType, customMAC: customMAC{literal}}{/literal}
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
                var reverseDNSUrl =  "{$modulelink}&sid={$sid}&action=reversedns&ipid="+ipAddressId;
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
    
 {else}
    <div class="card">
        <div class="card-body extra-padding">
            <h3 class="mb-3">Server Information</h3>
            <hr>
            <div class="alert alert-danger">{$error}</div>
        </div>
    </div>        
{/if}
