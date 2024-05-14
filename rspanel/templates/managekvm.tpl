<div class="card">
    <div class="card-body extra-padding">
        <h3 class="mb-3">KVM Over IP</h3>
        <hr>
        {if $error == ''}
                
               <div class="alert alert-info">Use the KVM Over IP interface to control the power of the server (power on, power off, reset) and to access the server directly from within the console if the server is not remotely accessible over the network. For more advanced users the KVM interface can also be used to completely re-install the operating system on the server.</div> 
               <div class="row">
                    <div class="col-sm-3">
                        <strong>Server Name</strong>
                    </div>
                    <div class="col-sm-7 mb-2">
                        {$serverName}
                    </div>

                </div>

                <div class="row">
                    <div class="col-sm-3">
                        <strong>Your IP</strong>
                    </div>
                    <div class="col-sm-4 mb-2">
                       {$yourIP}
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-3">
                        <strong>IPMI Access</strong>
                    </div>
                    <div class="col-sm-4 mb-2">
                        {if $kvmDetails['accessEnabled'] == true}
                            Enabled <input id="btnDisableKVM" type="button" class="btn btn-primary" value="Disable">
                        {else}
                            Disabled <input id="btnEnableKVM" type="button" class="btn btn-primary" value="Enable">
                        {/if}    
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-3">
                        <strong>KVM IP</strong>
                    </div>
                    <div class="col-sm-4 mb-2">
                        {$kvmDetails['kvM_IP']}
                        {if $kvmDetails['kvM_IP']}
                            <a href="https://{$kvmDetails['kvM_IP']}" target="_blank">Launch KVM</a>
                        {/if}
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-3">
                        <strong>KVM Username</strong>
                    </div>
                    <div class="col-sm-4 mb-2">
                        {$kvmDetails['kvM_User']}
                    </div>
                </div>
                    
                <div class="row">
                    <div class="col-sm-3">
                        <strong>KVM Password</strong>
                    </div>
                    <div class="col-sm-4 mb-2">
                        {$kvmDetails['kvM_Password']}
                    </div>
                </div> 
                    
                <h3 class="mt-4">IPMI Sessions</h3>
                 
                <div class="row">
                    <table class="table table-list dataTable no-footer">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Server Name</th>
                            <th>Proxy IP</th>
                            <th>Expires</th>
                        </tr>
                        </thead>
                        <tbody>
                            {foreach from=$IPMISessions key=key item=item}
                                <tr>
                                    <td class="text-center">{$key + 1}</td>
                                    <td class="text-center">{$item.serverName}</td>
                                    <td class="text-center">{$item.proxyIP}</td>
                                    <td class="text-center">{$item.expirationDateTime|date_format:"%m/%d/%Y %I:%M:%S %p"}</td>
                                </tr>
                            {foreachelse}
                                <tr>
                                    <td colspan="4" class="text-center">No active IPMI Sessions</td>
                                </tr>    
                            {/foreach}
                        </tbody>    
                    </table>    
                </div>    

                <hr>

                <p class="text-center">
                    <a href="{$modulelink}&sid={$sid}" class="btn btn-default">
                        Back to Manage
                    </a>
                </p>
           
                        
                        
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    
                    jQuery("input#btnEnableKVM").click(function(e){
                            
                        var btn = jQuery(this);

                        btn.attr('disabled',true);
                        btn.val('Enabling...');


                        var url = "{$modulelink}&sid={$sid}&action=enablekvm";
                        var remoteIP = "{$yourIP}";

                        $.ajax({
                            url : url,
                            method : "post",
                            data : {literal}{{/literal}remoteIP : remoteIP{literal}}{/literal}
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
                        
                        var url = "{$modulelink}&sid={$sid}&action=disablekvm";
                        
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
                        
        {else}
            <div class="alert alert-danger">{$error}</div>
        {/if}
    </div>    
</div>    
