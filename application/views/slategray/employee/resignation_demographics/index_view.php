<script type='text/javascript'>

	$( document ).ready( function() 
	{
		
	});
		
</script>
<style type="text/css">
	.demographics table tr{
		border: 0 none;
	}
	
	.show-border td {
		border: 1px solid #ccc !important;
	}

	.font-larger {
 		font-size: larger;
 	}

 	.font-large {
 		font-size: large;
 	}

 	.chart-container {
 		padding: 5px;
 		-moz-box-shadow: 0 2px 7px #ddd;
 		-webkit-box-shadow: 0 2px 7px #ddd;
 	}

 	.col-left {
 		float: left;
 		width: 49%;
 		margin-right: 10px;
 	}

 	.col-right {
 		float: right;
 		width: 49%;
 	}
	
</style>
<div class="default-view demographics">
	<div class="filter-container">
		<form id="demographics-form" method="post" action=""> 
		    <div id="form-div">
		    	<!-- <input type="text" name="company_srch" id="company_srch" class="company_srch"/>
		    	<input type="text" name="division_srch" id="division_srch" class="division_srch"/>
		    	<input type="text" name="department_srch" id="department_srch" class="department_srch"/> -->
		        <div class="col-2-form"> 
		            <div class="form-item odd ">
		                <label class="label-desc gray" for="company">Company:</label>
		                <div class="multiselect-input-wrap">
		                    <select multiple="multiple" class="multi-select" style="width:400px;" name="company[]" id="company">
		                        <?php
		                            foreach($company as $field){ ?>
		                            <option value="<?php echo $field['company_id'] ?>"><?php echo $field['company'] ?></option>
		                        <?php } ?>
		                    </select>
		                </div>              
		            </div>
		            <div class="form-item even ">
	                <label class="label-desc gray" for="date_period">Date Period:<span class="red font-large">*</span></label>
	                  <div class="text-input-wrap">
	                  	<select name ="date_period_start" id="date_start" style="width:30%">
                           	<option value="">Select..</option>
                         	<?php
                             	$list_of_year = array();
                             	$x = date('Y') - 2;
                             	$curr_year = date('Y');
                             	while($x <= $curr_year) 
                             	{
                               		$list_of_year[] = $x;
                               		$x++;
                             	}
                             arsort($list_of_year);
                             foreach($list_of_year as $year)
                               echo "<option value='".$year."'>".$year."</option>";
                         	?>
                       	</select>
	                    <!-- <input type="text" name="date_period_start" id="date_start" style="width:30%;" class="input-text date"/> -->
	                    &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;
	                    <select name ="date_period_end" id="date_end" style="width:30%">
                           	<option value="">Select..</option>
                         	<?php
                             	$list_of_year = array();
                             	$x = date('Y') - 2;
                             	$curr_year = date('Y');
                             	while($x <= $curr_year) 
                             	{
                               		$list_of_year[] = $x;
                               		$x++;
                             	}
                             arsort($list_of_year);
                             foreach($list_of_year as $year)
                               echo "<option value='".$year."'>".$year."</option>";
                         	?>
                       	</select>
	                    <!-- <input type="text" name="date_period_end" id="date_end" style="width:30%;" class="input-text date"/> -->
	                  </div>
	              </div>
		            <div class="form-item odd" id="multi-select-main-container1" style="display:none">
		                <label class="label-desc gray" for="division">Division:</label>
		                <div class="multiselect-input-wrap" id="multi-select-container1">
		                </div>               
		            </div>                                          
		            <div class="form-item odd" id="multi-select-main-container2" style="display:none">
		                <label class="label-desc gray" for="department">Department:</label>
		                <div class="multiselect-input-wrap" id="multi-select-container2">
		                </div>               
		            </div>                                          
		        </div>                 
		        <div class="spacer"></div>
		    </div>    
		</form>
		<div class="spacer"></div>
		 <div class="form-submit-btn">
	        <div class="icon-label-group">
	            <div class="icon-label">
	                <a rel="record-save" class="icon-16-search-opts" onclick="update_chart();" href="javascript:void(0);" onclick="">
	                    <span>Update Chart</span>
	                </a>            
	            </div>
	        </div>
	    </div>	
	</div>
	<div class="spacer"></div>
	<div class="spacer"></div>
	<div class="position-levels-container">
		<table width="100%">            
			<tr>
				<td colspan="7">				
					<h3 class="nomargin">Resigned Employees</h3>
					<hr />
				</td>
			</tr>
			<tr>
				<td colspan="7">
					<div class="chart-container">
						<div id="position_levels_chart"></div>
					</div>
				</td>				
			</tr>
			<?
				$emp_count2 = 0;
                foreach($positionDataTable as $k=>$v)
                {
                    $emp_count2 += $v->resigned_count;
                }   
            ?>
			<tr class="show-border" id="position_type_table">
				<td> 
                    <nobr>					
					    <span class="font-large">
                            <span id="whole_count">
                            	<?=number_format($emp_count2);?>
                            </span> 
					    </span>
					    <br/>
		                <span class="gray">Total Resigned</span>
                    </nobr>   
				</td>
				<? foreach( (array)$positionDataTable as $k => $o):
					$group_count2 = $o->resigned_count;
					if($emp_count2 == 0)
					{
						$per_group = 0;	
					}
					else
					{
						$per_group = $group_count2*1/$emp_count2*100;
					}
				?>
				<td> 
                    <nobr>					
					    <span class="font-large">
                            <span id="group_percentage">
                            	<?=number_format($per_group, 2,'.','')?>%
                            </span> 
                            (<b><span id="group_count"><?=$group_count2*1?></span></b>)
					    </span>
					    <br/>
		                <span class="gray"><?=$o->resigned_date?></span>
                    </nobr>   
				</td>
				<? endforeach; ?>
			</tr>
		</table>	
	</div>
	<div class="clear"></div>
</div>
