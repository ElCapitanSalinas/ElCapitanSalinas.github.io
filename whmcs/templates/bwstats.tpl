<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<div class="card">
    <div class="card-body extra-padding">
        <h3 class="mb-3">Server Bandwidth Stats</h3>
        <hr>
        {if $error == ''}
            <form id="frmBWStats" method="post" action="">
                <div class="row">
                    <div class="col-sm-1 mt-2">
                        <label>Period:</label>
                    </div>
                    <div class="col-sm-2">
                        <select class="form-control" id="statPeriod" name="statPeriod">
                            {html_options options=$periods selected=$period}
                        </select>
                    </div>
                    <div class="col-sm-1 mt-2">
                        <label>Timezone:</label>
                    </div>
                    <div class="col-sm-4">
                        <select class="form-control" id="statTimeZone" name="statTimeZone">
                            {foreach from=$timezones key=key item=item}
                                <option value="{$key}" {if $key == $timezone}selected{/if}>{$item.timezone}</option>
                            {/foreach}
                            
                        </select>
                    </div>
                </div>
                <hr>
                <div id="bwstateChart" style="width: 100%; height: 500px;"></div>
                <hr>

                <p class="text-center">
                    <a href="{$modulelink}&sid={$sid}" class="btn btn-default">
                        Back to Manage
                    </a>
                </p>
            </form>
            {literal}            
            <script type="text/javascript">
                google.charts.load('current', {'packages':['corechart']});
                google.charts.setOnLoadCallback(drawChart);

                function drawChart() {
                  var data = google.visualization.arrayToDataTable({/literal}{$chartData}{literal});

                  var options = {
                    title: '{/literal}{$chartTitle}{literal}',
                    legend: {position: 'top', maxLines: 3},
                    hAxis: {title: 'Period'},
                    vAxis: {title: 'Volume(MB)', minValue: 0}
                  };

                  var chart = new google.visualization.AreaChart(document.getElementById('bwstateChart'));
                  chart.draw(data, options);
                }
                
                jQuery(document).ready(function(){
                     jQuery("select#statPeriod, select#statTimeZone").change(function(){
                         
                         var period = jQuery("select#statPeriod").val();
                         var timezone = jQuery("select#statTimeZone").val();
                         var url = "{/literal}{$pagelink}{literal}";
                         url += '&period=' + period + '&tz='+timezone;
                         
                         document.location = url;
                     });
                });
                
            </script>
            {/literal}
        {else}
            <div class="alert alert-danger">{$error}</div>
        {/if}
    </div>    
</div>    
