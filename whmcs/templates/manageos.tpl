<div class="card">
    <div class="card-body extra-padding">
        <h3 class="mb-3">Install/Re-Install Operating System</h3>
        <hr>
        {if $error == ''}
            
            {if $isInstalling == true}
                <div class="row">

                    <div class="col-sm-3">
                        <label>Server Name</label>
                    </div>
                    <div class="col-sm-7 mb-2">
                        {$serverDetail['serverLabel']}
                    </div>

                </div>
                <div class="row">

                    <div class="col-sm-3">
                        <label>Status</label>
                    </div>
                    <div class="col-sm-7 mb-2">
                    {$installationStatus}
                    </div>
                </div>
                <p class="text-center">
                    <input type="button" id="btnCancelInstall" class="btn btn-danger" value="Cancel Install">
                    <a href="" class="btn btn-primary">
                        Refresh Status
                    </a>
                </p>
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                       
                        jQuery("input#btnCancelInstall").click(function(e){
                            
                            var btn = jQuery(this);

                            btn.attr('disabled',true);
                            btn.val('Cancelling...');


                            var url = "{$modulelink}&sid={$sid}&action=cancelinstallos";

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
            {else}    
                <form id="frmInstallOS" method="post" action="{$modulelink}&action=installos&sid={$sid}">
                    <div class="alert alert-warning">This process will wipe all of the data on this server.</div>
                    <div id="rsError"></div>
                    <div class="row">

                            <div class="col-sm-3">
                                <label>Server Name</label>
                            </div>
                            <div class="col-sm-7 mb-2">
                                {$serverDetail['serverLabel']}
                            </div>

                    </div>

                    <div class="row">
                        <div class="col-sm-3">
                            <label>IP Block</label>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <select class="form-control" name="serverIPBlock" id="serverIPBlock">
                                {foreach from=$ipAddressData key=key item=item}
                                    <option value="{$key}">{$key}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-sm-3">
                            <label>Gateway</label>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <input type="text" class="form-control" name="serverGateway" id="serverGateway" value="" readonly>
                        </div>
                    </div>
                            
                   <div class="row">
                        <div class="col-sm-3">
                            <label>Netmask</label>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <input type="text" class="form-control" name="serverNetmask" id="serverNetmask" value="" readonly>
                        </div>
                    </div>
                            
                    <div class="row">
                        <div class="col-sm-3">
                            <label>Server IP</label>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <select class="form-control" name="serverIP" id="serverIP">
                                {foreach from=$ipAddressData key=key item=item}
                                    {foreach from=$item.ips item=ip}
                                        <option style="display:none;" value="{$ip}" data-ipblock="{$key}">{$ip}</option>
                                    {/foreach}
                                {/foreach}
                            </select>
                        </div>
                    </div>        

                    <div class="row">
                        <div class="col-sm-3">
                            <label>License Key (Optional)</label>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <input type="text" class="form-control" name="licenseKey" value="">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-3">
                            <label>OS to Install</label>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <select id="operatingSystemId" class="form-control" name="operatingSystemId">
                                <option value="">--Please Select--</option>
                                {foreach from=$compitableOS item=os}
                                    <option value="{$os.operatingSystemId}">{$os.osLabel}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-3">
                            <label>Partitioning Scheme</label>
                        </div>
                        <div class="col-sm-4 mb-2">
                            <select id="partitioningSchemeId" class="form-control" name="partitioningSchemeId" disabled>
                                <option value="">--Please Select--</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <p class="text-center">
                        <input id="btnInstallOS" type="submit" class="btn btn-primary" value="Install OS">
                        <a href="{$modulelink}&sid={$sid}" class="btn btn-default">
                            Back to Manage
                        </a>
                    </p>
                </form>
                {literal}            
                <script type="text/javascript">
                    var ipAddressData = {/literal}{$ipAddressDataJson}{literal}
                    var modulelink = "{/literal}{$modulelink}{literal}";
                    var sid = "{/literal}{$sid}{literal}";
                    
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
                                    err += '</li></ul></div>'

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

                                var url = modulelink+"&sid="+sid+"&action=getpartitioningschemes";

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
                {/literal}
             {/if}           
        {else}
            <div class="alert alert-danger">{$error}</div>
        {/if}
    </div>    
</div>    
