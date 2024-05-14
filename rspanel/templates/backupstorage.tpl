<div class="card">
    <div class="card-body extra-padding">
        <h3 class="mb-3">Manage Backup Storage</h3>
        <hr>
        {if $error == ''}
            
                
                <form id="frmbackupStorage" method="post" action="{$modulelink}&action=updatebackupstorage&sid={$sid}">
                    
                    <div id="rsError"></div>
                    <div class="row">

                        <div class="col-sm-3">
                            <label>Server Name</label>
                        </div>
                        <div class="col-sm-7 mb-2">
                            {$backupStorage['serverLabel']}
                        </div>

                    </div>
                        
                    <div class="row">

                        <div class="col-sm-3">
                            <label>Backup Space Name</label>
                        </div>
                        <div class="col-sm-7 mb-2">
                            {$backupStorage['backupSpaceName']}
                        </div>

                    </div>    

                     <div class="row">
                        <div class="col-sm-3">
                            <label>Total Size</label>
                        </div>
                        <div class="col-sm-7 mb-2">
                             {$backupStorage['totalSize']}
                        </div>
                    </div>    
                    
                    <div class="row">
                        <div class="col-sm-3">
                            <label>Size Remaining</label>
                        </div>
                        <div class="col-sm-7 mb-2">
                             {$backupStorage['sizeRemaining']}
                        </div>
                    </div>
                        
                   {foreach from=$backupStorage.ftp item=ftp}
                        <div class="row mt-2">
                            <div class="col-sm-3">
                            </div>    
                            <div class="col-sm-7">
                                <label>
                                    <strong>
                                    {if $ftp.linkType == 'Private'}
                                        Private Account
                                    {else if $ftp.linkType == 'Public'}
                                        Public Account
                                    {/if}      
                                    </strong>
                                </label>
                            </div>
                        </div>
                                    
                        <div class="row">
                            <div class="col-sm-3">
                                <label>Name</label>
                            </div>
                            <div class="col-sm-7 mb-2">
                                 {$ftp.userName}
                            </div>
                        </div>            
                        
                        <div class="row">
                            <div class="col-sm-3">
                                <label>FTP IP Address</label>
                            </div>
                            <div class="col-sm-7 mb-2">
                                {if $ftp.linkType == 'Private'}
                                    {$backupStorage.ftpPrivateIP}
                                {else if $ftp.linkType == 'Public'}
                                    {$backupStorage.ftpPublicIP}
                                {/if} 
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-sm-3">
                                <label>Password</label>
                            </div>
                            <div class="col-sm-4 mb-2">
                                <input type="text" class="form-control" name="ftpPassword[]" value="">
                            </div>
                        </div>
                            
                        <div class="row">
                            <div class="col-sm-3">
                                <label>Enable</label>
                            </div>
                            <div class="col-sm-4 mb-2">
                                <select class="form-control" name="enable[]">
                                    <option value="true" {if $ftp.enabledSw == 'true'}selected{/if}>Yes</option>
                                    <option value="false" {if $ftp.enabledSw == 'false'}selected{/if}>No</option>
                                </select>    
                                
                            </div>
                        </div>
                        <input type="hidden" class="form-control" name="ftpAccountId[]" value="{$ftp.ftpAccountsId}">       
                       
                   {/foreach}    
                    
                    <hr>

                    <p class="text-center">
                        <input id="btnUpdateStorage" type="button" class="btn btn-primary" value="Update">
                        <a href="{$modulelink}&sid={$sid}" class="btn btn-default">
                            Back to Manage
                        </a>
                    </p>
                </form>
                {literal}            
                <script type="text/javascript">
                    
                    var modulelink = "{/literal}{$modulelink}{literal}";
                    var sid = "{/literal}{$sid}{literal}";
                    
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
                {/literal}
                     
        {else}
            <div class="alert alert-danger">{$error}</div>
        {/if}
    </div>    
</div>    
