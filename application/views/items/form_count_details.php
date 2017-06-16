<?php echo form_open('items', array('id'=>'item_form', 'class'=>'form-horizontal')); ?>
	<fieldset id="count_item_basic_info" class="mobile_hide">
		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_item_number'), 'name', array('class'=>'control-label col-xs-3')); ?>
			<div class="col-xs-8">
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-barcode"></span></span>
					<?php echo form_input(array(
							'name'=>'item_number',
							'id'=>'item_number',
							'class'=>'form-control input-sm',
							'disabled'=>'',
							'value'=>$item_info->item_number)
							);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm">
			<?php echo form_label($this->lang->line('items_name'), 'name', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_input(array(
						'name'=>'name',
						'id'=>'name',
						'class'=>'form-control input-sm',
						'disabled'=>'',
						'value'=>$item_info->name)
						); ?>
			</div>
		</div>

		<div class="form-group form-group-sm" class="mobile_hide">
			<?php echo form_label($this->lang->line('items_category'), 'category', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<div class="input-group">
					<span class="input-group-addon input-sm"><span class="glyphicon glyphicon-tag"></span></span>
					<?php echo form_input(array(
							'name'=>'category',
							'id'=>'category',
							'class'=>'form-control input-sm',
							'disabled'=>'',
							'value'=>$item_info->category)
							);?>
				</div>
			</div>
		</div>

		<div class="form-group form-group-sm" class="mobile_hide">
			<?php echo form_label($this->lang->line('items_stock_location'), 'stock_location', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-8'>
				<?php echo form_dropdown('stock_location', $stock_locations, $user_info->location_id, array('onchange'=>'display_stock(this.value);', 'class'=>'form-control'));	?>
			</div>
		</div>

		<div class="form-group form-group-sm" class="mobile_hide">
			<?php echo form_label($this->lang->line('items_current_quantity'), 'quantity', array('class'=>'control-label col-xs-3')); ?>
			<div class='col-xs-4'>
				<?php echo form_input(array(
						'name'=>'quantity',
						'id'=>'quantity',
						'class'=>'form-control input-sm',
						'disabled'=>'',
						'value'=>to_quantity_decimals(current($item_quantities)))
						); ?>
			</div>
		</div>
	</fieldset>
<?php echo form_close(); ?>

<table id="items_count_details" class="table table-striped table-hover">
    <?php
        /*
         * the tbody content of the table will be filled in by the javascript (see bottom of page)
        */

        $inventory_array = $this->Inventory->get_inventory_data_for_item($item_info->item_id)->result_array();
        $employee_name = array();

        foreach($inventory_array as $row)
        {
            $employee = $this->Employee->get_info($row['trans_user']);
            array_push($employee_name, $employee->first_name . ' ' . $employee->last_name);   
        }
        ?>
	<thead>
		<tr style="background-color: #999 !important;">
			<th colspan="4">最近<?php echo count($inventory_array);?>次 数量变动记录</th>
		</tr>
		<tr>
			<th width="30%">时间</th>
			<th width="20%" class="mobile_hide">负责人</th>
			<th width="20%">数量</th>
			<th width="30%">备注</th>
		</tr>
	</thead>
	<tbody id="inventory_result">
		
	</tbody>
</table>

<script type="text/javascript">
$(document).ready(function()
{
    display_stock(<?php echo $user_info->location_id;?>);
});

function display_stock(location_id)
{
    var item_quantities = <?php echo json_encode($item_quantities); ?>;
    document.getElementById("quantity").value = parseFloat(item_quantities[location_id]).toFixed(<?php echo quantity_decimals(); ?>);
    
    var inventory_data = <?php echo json_encode($inventory_array); ?>;
    var employee_data = <?php echo json_encode($employee_name); ?>;
    
    var table = document.getElementById("inventory_result");

    // Remove old query from tbody
    var rowCount = table.rows.length;
    for (var index = rowCount; index > 0; index--)
    {
        table.deleteRow(index-1);       
    }
				
    // Add new query to tbody
    for (var index = 0; index < inventory_data.length; index++) 
    {                
        var data = inventory_data[index];//console.log(data);
        if(data['trans_location'] == location_id)
        {
            var tr = document.createElement('tr');

            var td = document.createElement('td');
            td.appendChild(document.createTextNode(data['trans_date']));
            tr.appendChild(td);
            
            td = document.createElement('td');
            td.appendChild(document.createTextNode(employee_data[index]));
            td.setAttribute("class", "mobile_hide");
            tr.appendChild(td);
            
            td = document.createElement('td');
            td.appendChild(document.createTextNode(parseFloat(data['trans_inventory']).toFixed(<?php echo quantity_decimals(); ?>)));
			td.setAttribute("style", "text-align:center");
            tr.appendChild(td);
            
            td = document.createElement('td');
            
            var link=document.createElement('a');var req_url=null;
            if(data['ref_type']=='0'){req_url='sales';}
            else if(data['ref_type']=='1'){req_url='receivings';}
            else if(data['ref_type']=='2'){req_url='manufactures';}
            else if(data['ref_type']=='3'){req_url='transports';}

            if(req_url!=null){
                link.setAttribute("href", req_url+"/update?id="+data['ref_id']);
                link.setAttribute("target", "_blank");
            }else{link.setAttribute("style", "color:black;text-decoration:none");}

            link.appendChild(document.createTextNode(data['trans_comment']));
            td.appendChild(link);

            tr.appendChild(td);

            table.appendChild(tr);
        }
    }
}
</script>