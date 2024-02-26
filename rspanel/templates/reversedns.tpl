<div class="card">
    <div class="card-body extra-padding">
        <h3 class="mb-3">Reverse DNS</h3>
        <hr>
        {if $error == ''}
            <form id="frmrDNS" method="post" action="{$modulelink}&action=setreversedns&sid={$sid}&ipid={$ipAddressId}">
                    
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
                        <label>IP Address</label>
                    </div>
                    <div class="col-sm-4 mb-2">
                        <select class="form-control" name="ipAddress" id="ipAddress">
                            {foreach from=$rDNSIPs key=key item=item}
                                <option value="{$item}">{$item}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-sm-3">
                        <label>Reverse DNS Record</label>
                    </div>
                    <div class="col-sm-4 mb-2">
                        <input type="text" class="form-control" name="rDNSRecord" value="">
                    </div>
                </div>        
                        
                <hr>

                <p class="text-center">
                    <input type="hidden" name="ipAddressId" value="{$ipAddressId}">
                    <input id="btnSaveReverseDNS" type="submit" class="btn btn-primary" value="Update">
                    <a href="{$modulelink}&sid={$sid}" class="btn btn-default">
                        Back to Manage
                    </a>
                </p>
                </form>
                {literal}            
                <script type="text/javascript">
                    
                    var modulelink = "{/literal}{$modulelink}{literal}";
                    var sid = "{/literal}{$sid}{literal}";
                    var ipAddressId = "{/literal}{$ipAddressId}{literal}";
                    
                    jQuery(document).ready(function(){
                        
                        jQuery("select#ipAddress").change(function(){
                            
                            var dd = $(this);
                            var ipAddress = dd.val();
                           
                            $('#rsError').html('');
                            $("form#frmrDNS input[name='rDNSRecord']").val('');

                            dd.attr('disabled',true);
                            
                            var url = modulelink+"&action=getreversedns&sid="+sid;
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
                {/literal}
                     
        {else}
            <div class="alert alert-danger">{$error}</div>
        {/if}
    </div>    
</div>    
