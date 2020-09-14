
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
	                    <input type="text" name="date_period_start" id="date_start" style="width:30%;" class="input-text date"/>
	                    &nbsp;&nbsp;&nbsp;to&nbsp;&nbsp;&nbsp;
	                    <input type="text" name="date_period_end" id="date_end" style="width:30%;" class="input-text date"/>
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
	<!-- <div class="gender-container col-left"> -->		
	<div class="gender-container" style="width:70%;display:block;margin: 0 auto">
		<table width="100%">
			<col width="33%">
			<col width="33%">
			<col width="33%">
			<tr><td colspan="3">				
					<h3 class="nomargin">Gender</h3><hr />
				</td>
			</tr>
			<tr><td colspan="3">
					<div class="chart-container">
						<div id="gender_chart"></div>
					</div>
				</td>				
			</tr>
			 <?
                foreach($genderDataTable as $k=>$v)
                {
                    $emp_count += array_sum(array_values($v));
                }    
            ?>
			<tr class="show-border">
				<td><span class="font-large font-strong" id="emp_count"><?=$emp_count?></span>
					<br />
		            <span class="gray"><b>Total Employees</b></span>
				</td>
				<? foreach( (array)$genderDataTable as $k => $o ) { 
                     $group_count = array_sum($o);
                ?>
				<td style="vertical-align:top">
		        	<span class="font-large"  id="_<?=strtolower(str_replace(' ','_',$o->title))?>">
						<span id="<?=$k?>_group_percentage">
							<?=number_format($group_count*1/$emp_count*100, 2,'.','')?>% 
						</span> 
						(<b><span id="<?=$k?>_group_count"><?=$group_count*1?></span></b>)
					</span>
		        	<br />
                    <span class="gray">Regular : ( <span id="<?=$k?>_regular_count"><?=$o['regular_count']?></span> )</span>
                    <br/>
                    <span class="gray">Probationary : ( <span id="<?=$k?>_probationary_count"><?=$o['probationary_count']?></span> )</span>
                    <br />
                    <span class="gray">Consultant : ( <span id="<?=$k?>_consultant_count"><?=$o['consultant_count']?></span> )</span>
                    <br />
                    <span class="gray">Project Employee : ( <span id="<?=$k?>_project_employee_count"><?=$o['project_employee_count']?></span> )</span>
                    <br />
                    <span class="gray">Contractual (Direct Hired) : ( <span id="<?=$k?>_contractual_direct_count"><?=$o['contractual_direct_count']?></span> )</span>
                    <br />
                    <span class="gray">Contractual (Agency Hired) : ( <span id="<?=$k?>_contractual_agent_count"><?=$o['contractual_agent_count']?></span> )</span>
                    <br />
                    <span class="gray">On-the-Job Training : ( <span id="<?=$k?>_ojt_count"><?=$o['ojt_count']?></span> )</span>
                    <br />
		            <span class="gray"><b>Total <?=($k)?></b></span>
				</td>
				<? } ?>
			</tr>
		</table>
	</div>
	<!-- <div class="age-structure-container col-right"> -->
	<div class="clear"></div>
	<div class="spacer"></div>
	<div class="spacer"></div>
	<div class="age-structure-container" style="width:70%;display:block;margin: 0 auto">
		<table width="100%">
			<col width="20%">
			<col width="20%">
			<col width="20%">
			<col width="20%">
			<col width="20%">
			<tr>
				<td colspan="5">				
					<h3 class="nomargin">Age Profile</h3>
					<hr />
				</td>
			</tr>
			<tr>
				<td colspan="5">
					<div class="chart-container">
						<div id="age_chart"></div>
					</div>
				</td>				
			</tr>
			<tr class="show-border">
				<? foreach( (array)$ageDataTable as $k => $o ): ?>
				<td>					
					<span class="font-large" id="_<?=$o->short_name?>">
						<?=number_format($o->group_count*1/$o->employee_count*100, 2,'.','').'% (<b>'.($o->group_count*1).'</b>) ';?>
					</span>
					<br />
		            <span class="gray"><?=ucwords(strtolower($o->title))?></span>
				</td>
				<? endforeach; ?>
			</tr>
		</table>
	</div>
	<div class="clear"></div>
	<div class="spacer"></div>
	<div class="spacer"></div>
	<div class="position-levels-container">
		<table width="100%">            
			<tr>
				<td colspan="7">				
					<h3 class="nomargin">Employee Type</h3>
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
                foreach($positionDataTable as $k=>$v)
                {
                    $emp_count2 += array_sum(array_values($v));
                }    
            ?>
			<tr class="show-border" id="position_type_table">
				<? foreach( (array)$positionDataTable as $k => $o): 
					$group_count2 = array_sum($o);
				?>
				<td> 
                    <nobr>					
					    <span class="font-large" id="_<?=str_replace(' ','_', $o->title)?>">
                            <span id="<?=$k?>_group_percentage">
                            	<?=number_format($group_count2*1/$emp_count2*100, 2,'.','')?>%
                            </span> 
                            (<b><span id="<?=$k?>_group_count"><?=$group_count2*1?></span></b>)
					    </span>
                        <br/>
                        <span class="gray">Regular : ( <span id="<?=$k?>_regular_count"><?=$o['regular_count']?></span> )</span>
                        <br/>
                        <span class="gray">Probationary : ( <span id="<?=$k?>_probationary_count"><?=$o['probationary_count']?></span> )</span>
                        <br />
                        <span class="gray">Consultant : ( <span id="<?=$k?>_consultant_count"><?=$o['consultant_count']?></span> )</span>
                        <br />
                        <span class="gray">Project Employee : ( <span id="<?=$k?>_project_employee_count"><?=$o['project_employee_count']?></span> )</span>
                        <br />
                        <span class="gray">Contractual (Direct Hired) : ( <span id="<?=$k?>_contractual_direct_count"><?=$o['contractual_direct_count']?></span> )</span>
                        <br />
                        <span class="gray">Contractual (Agency Hired) : ( <span id="<?=$k?>_contractual_agent_count"><?=$o['contractual_agent_count']?></span> )</span>
                        <br />
                        <span class="gray">On-the-Job Training : ( <span id="<?=$k?>_ojt_count"><?=$o['ojt_count']?></span> )</span>
                        <br />
		                <span class="gray"><b>Total <?=ucwords(strtolower($k))?></b></span>
                    </nobr>   
				</td>
				<? endforeach; ?>
			</tr>
		</table>	
	</div>
	<div class="clear"></div>
	<div class="spacer"></div>
	<div class="spacer"></div> 	
	<div class="tenure-container" style="width:70%;display:block;margin: 0 auto">
		<table width="100%">
			<col width="20%">
			<col width="20%">
			<col width="20%">
			<col width="20%">
			<col width="20%">
			<tr>
				<td colspan="5">				
					<h3 class="nomargin">Tenure (Work Force)</h3>
					<hr />
				</td>
			</tr>
			<tr>
				<td colspan="5">
					<div class="chart-container">
						<div id="tenure_chart"></div>
					</div>
				</td>				
			</tr>
			<? 
			$employee_count = 0;
			foreach( (array)$tenureDataTable as $k => $o): 
			$employee_count += $o->group_count;
			endforeach;
			?>
			<tr class="show-border">
				<? foreach( (array)$tenureDataTable as $k => $o): ?>
				<td>					
					<span class="font-large" id="_<?=str_replace(' ','_', $o->title)?>">
						<?=number_format($o->group_count*1/$employee_count*100, 2,'.','').'% <b>('.($o->group_count*1).')</b> ' ?>
					</span>
					<br />
		            <span class="gray"><?=$o->title?></span>
				</td>
				<? endforeach; ?>
			</tr>
		</table>
	</div>
	
	<div class="clear"></div>
</div>
