<?php init_head();

?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tab-content">
                            <div class="row" id="deals-table">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p class="bold"><?php echo _l('filter_by'); ?></p>
                                        </div>
                                        <form action="<?= admin_url('gst/gstr1b2csinvoices') ?>" method="post">
                                        <?= form_hidden('download_sample', 'true'); ?>
                                        <?= form_hidden($this->security->get_csrf_token_name(), $this->security->get_csrf_hash()); ?>
                                        <div class="col-md-2 leads-filter-column">
                                            <select class="selectpicker display-block" data-width="100%" id="year" name='year' data-none-selected-text="<?php echo _l('No Year Selected'); ?>">
                                                <option value=""></option>
                                                <?php for ($i = date('Y'); $i >= date('Y') - 20; $i--) : ?>
                                                    <?php $nextYear = $i + 1; ?>
                                                    <option value="<?= $i . '-' . $nextYear ?>"><?= $i . '-' . $nextYear ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-2 leads-filter-column">
                                            <select multiple class="selectpicker display-block" data-width="100%" id='month' name='month[]' data-none-selected-text="<?php echo _l('No Month Selected'); ?>">
                                                <option value=""></option>
                                                <option value="04">April</option>
                                                <option value="05">May</option>
                                                <option value="06">June</option>
                                                <option value="07">July</option>
                                                <option value="08">August</option>
                                                <option value="09">September</option>
                                                <option value="10">October</option>
                                                <option value="11">November</option>
                                                <option value="12">December</option>
                                                <option value="01">January</option>
                                                <option value="02">February</option>
                                                <option value="03">March</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 leads-filter-column">
                                            <button type="button" class="btn btn-primary display-block" onclick="GetReport();">Get</button>
                                        </div>
                        
                                        <div class="col-md-1 leads-filter-column " style="margin-left: 390px;" >
                                            <button type="button" class="btn btn-primary display-block" onclick="DownloadAPIData()">Downlaod</button>
                                        </div>
                                        <div class="col-md-1 leads-filter-column">
                                            <button type="submit" class="btn btn-primary display-block" onclick="">Export</button>
                                        </div>
                                        </form>
                                    </div>
                                    <hr class="hr-panel-separator"/>
                                </div>
                                <div class="clearfix"></div>

                                <div class="col-md-12">
                                    <div class="table-responsive">
                                        <table class="table table-striped DataTables " id="DataTables" width="100%">
                                            <thead>
                                            <tr>
                                                <th><?= _l('ID') ?></th>
                                                <th><?= _l('FLAG') ?></th>
                                                <th><?= _l('CHKSUM') ?></th>
                                                <th><?= _l('SUPPLY TYPE') ?></th>
                                                <th><?= _l('DIFF PERCENT') ?></th>
                                                <th><?= _l('RT') ?></th>
                                                <th><?= _l('TYP') ?></th>
                                                <th><?= _l('POS') ?></th>
                                                <th><?= _l('ETIN') ?></th>
                                                <th><?= _l('TXVAL') ?></th>
                                                <th><?= _l('IAMT') ?></th>
                                                <th><?= _l('CAMT') ?></th>
                                                <th><?= _l('CSAMT') ?></th>
                                                <th><?= _l('SAMT') ?></th>
                                            </tr>
                                            </thead>
                                            <tbody id="deals_table">

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>

    function GetReport(){
        var year  =  $('#year').val();
        var month =  $('#month').val();
        console.log('year', year)
        console.log('month', month)
        if(year == ''){
            alert_float("danger", 'Please select Year');
            return false;
        } else if(month.length == 0){
            alert_float("danger", 'Please select Month');
            return false;
        } else{
            $.ajax({
                url: '<?php echo admin_url('gst/get_gstr1b2csinvoices'); ?>',
                data: { 
                    'year': year,
                    'month': month,
                },
                type: "post",
                dataType: "json",
                success: function (data){
                    $('#deals_table').empty();
                    if(data.status){
                        var html = '';
                        data.data.forEach(item => {
                            console.log('item',item)
                            html += '<tr>';
                            html +=`<td>${item.id}</td>`
                            html +=`<td>${item.flag}</td>`
                            html +=`<td>${item.chksum}</td>`
                            html +=`<td>${item.sply_ty}</td>`
                            html +=`<td>${item.diff_percent}</td>`
                            html +=`<td>${item.rt}</td>`
                            html +=`<td>${item.typ}</td>`
                            html +=`<td>${item.pos}</td>`
                            html +=`<td>${item.etin}</td>`
                            html +=`<td>${item.txval}</td>`
                            html +=`<td>${item.iamt}</td>`
                            html +=`<td>${item.camt}</td>`
                            html +=`<td>${item.csamt}</td>`
                            html +=`<td>${item.samt}</td>`
                            html +='</tr>'
                        });
                        console.log('html', html)
                        $('#deals_table').append(html)
                    } else{
                        console.log("data else");
                        html= `<tr><td colspan = 25 > No Data Received <td> </tr>`;
                        $('#deals_table').append(html);
                    }
                }
            });
        }
    }
    
    function DownloadAPIData(){
        var year  =  $('#year').val();
        var month =  $('#month').val();
        console.log('year', year)
        console.log('month', month)
        if(year == ''){
            alert_float("danger", 'Please select Year');
            return false;
        } else if(month.length == 0){
            alert_float("danger", 'Please select Month');
            return false;
        } else{
            $.ajax({
                url: '<?php echo admin_url('gst/GetB2CSInvoices'); ?>',
                data: { 
                    'year': year,
                    'month': month,
                },
                type: "post",
                dataType: "json",
                success: function (data){
                    if(data.status){
                        alert_float("success", data?.message);
                    } else{
                        alert_float("danger", data?.message);
                    }
                }
            });
        }
    }
</script>