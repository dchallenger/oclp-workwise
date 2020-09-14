<?php
if (isset($jqgrid)) {
    $jqgrid = $jqgrid;
} else {
    $jqgrid = 'template/jqgrid';
}
?>

	 <!-- content alert messages -->
    <div id="message-container">
        <?php
			if( isset($msg) ){
            	echo is_array($msg) ? implode("\n", $msg) : $msg;
			}
			if(isset($flashdata)){
				echo $flashdata;
			}
		?>
    </div>
    <!-- content alert messages -->

    <?php if( isset($error) ) : ?>              
        <div id="message_box" class="attention" style="padding-left: 60px;width:60%;margin: 0 auto;">
            <img src="<?=base_url().$this->userinfo['theme']; ?>/images/exclamation-big.png" alt="" >
            <h3 style="margin: 0.3em 0 0.5em 0">Oops! <?=$error?></h3>

            <p><?=$error2?></p>
        </div>
    <?  else :?>

        
        

       <form id="export-form" method="post" action="">
          <input type="hidden" name="export_link" id="export_link" value="<?=site_url('dtr/biometrics_report/export')?>" />
          <input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>/detail"/>        
          
          <div id="form-div">
            <div class="col-2-form">
              
                <div class="form-item odd ">
                    <label class="label-desc gray" for="department">Category:</label>
                    <div class="multiselect-input-wrap">
                        <select id="category" style="width:400px;" name="category">
                            <?php
                                $category = array("0"=>"Select","1"=>"By Company","2"=>"By Division","3"=>"By Department","4"=>"By Employee");
                                foreach($category as $key => $val){ ?>
                                <option value="<?php echo $key ?>"><?php echo $val ?></option>
                            <?php } ?>
                        </select>
                    </div>
                  </div>

                  <div class="form-item even " id="multi-select-main-container" style="display:none">
                    <label class="label-desc gray" for="department" id="category_selected"></label>
                    <div class="multiselect-input-wrap" id="multi-select-container">
                    </div>
                  </div> 


              <!--
              <div class="form-item odd ">
                <label class="label-desc gray" for="employee">Employee:</label>
                <div class="multiselect-input-wrap">
                    <select id="employee" multiple="multiple" class="multi-select" style="width:400px;" name="employee[]">
                        <?php foreach($employee as $employee_record){ ?>
                            <option value="<?php echo $employee_record['user_id']; ?>"><?php echo $employee_record['firstname']." ".$employee_record['lastname']; ?></option>
                        <?php } ?>
                    </select>
                </div>
              </div>
              <div class="form-item even ">
                <label class="label-desc gray" for="company">Company:</label>
                <div class="multiselect-input-wrap">
                    <select id="company" multiple="multiple" class="multi-select" style="width:400px;" name="company[]">
                        <?php foreach($company as $company_record){ ?>
                            <option value="<?php echo $company_record['company_id']; ?>"><?php echo $company_record['company']; ?></option>
                        <?php } ?>
                    </select>
                </div>
              </div>
              <div class="form-item odd ">
                <label class="label-desc gray" for="department">Department:</label>
                <div class="multiselect-input-wrap">
                    <select id="department" multiple="multiple" class="multi-select" style="width:400px;" name="department[]">
                        <?php foreach($department as $department_record){ ?>
                            <option value="<?php echo $department_record['department_id']; ?>"><?php echo $department_record['department']; ?></option>
                        <?php } ?>
                    </select>
                </div>
              </div>
              <div class="form-item even ">
                <label class="label-desc gray" for="division">Division:</label>
                <div class="multiselect-input-wrap">
                    <select id="division" multiple="multiple" class="multi-select" style="width:400px;" name="division[]">
                        <?php foreach($division as $division_record){ ?>
                            <option value="<?php echo $division_record['division_id']; ?>"><?php echo $division_record['division']; ?></option>
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
                -->
              <div class="spacer"></div>
            </div>
            <div class="clear"></div>
            <div class="spacer"></div>
            </div>   
        </form>




        <?php if( isset($views_outside_record_form) && sizeof($views_outside_record_form) > 0 ) :
                    foreach($views_outside_record_form as $view) :
                        $this->load->view($this->userinfo['rtheme'].'/'.$view);
                    endforeach;
                endif; ?>    
           
    <?php endif; ?>

    <div class="form-submit-btn">
            <div class="icon-label-group">
                <div class="icon-label">
                    <a rel="record-save" class="icon-16-search-opts" onclick="generate_list();" href="javascript:void(0);" onclick="">
                        <span>Generate List</span>
                    </a>            
                </div>
                <div class="icon-label">
                    <a rel="record-save" class="icon-16-export" href="javascript:void(0);" onclick="export_list();">
                        <span>Export</span>
                    </a>            
                </div>
            </div>
    </div>  

    <div class="clear"></div>


    <!-- PLACE YOUR MAIN CONTENT HERE -->
	<form name="record-form" id="record-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="record_id" id="record_id"  />
        <input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>"/>
        <input type="hidden" name="prev_search_str" id="prev_search_str" value="<?=$this->input->post('prev_search_str')?>"/>
        <input type="hidden" name="prev_search_field" id="prev_search_field" value="<?=$this->input->post('prev_search_field')?>"/>
        <input type="hidden" name="prev_search_option" id="prev_search_option" value="<?=$this->input->post('prev_search_option')?>"/>
    </form>

    <table id="jqgridcontainer"></table>
    <div id="jqgridpager"></div>
    <?php echo $this->load->view($this->userinfo['rtheme'] . '/' . $jqgrid); ?>
    <div class="clear"></div>



    <div class="search-wrap hidden">
        <div class="search-form">
            <div class="search-trigger" tooltip="Search Options">
                <div><a href="javascrpt:void(0)" class="icon-16-search-opts">Search Options</a></div>
            </div>
            <div class="search-input">
                <form class="search" name="form-search-jqgridcontainer" id="form-search-jqgridcontainer" jqgridcontainer="jqgridcontainer">
                    <?php
                    if($this->input->post('prev_search_str') && $this->input->post('prev_search_str') != "")
                    {
                        $srch_str = $this->input->post('prev_search_str');
                    }else{
                        $srch_str = "Search...";
                    }
                    ?>
                    <input id="search" class="search-jqgridcontainer" type="text" value="<?php echo $srch_str?>" onclick="" onfocus="javascript:($(this).val()=='Search...' ? $(this).val('') : '')" onblur="javascript:($(this).val()=='' ? $(this).val('Search...') : '')"/>
                    <input type="button" id="search-btn" class="search-btn" value="Search">
                </form>
            </div>
            <div class="clear"></div>
            <form class="style2 search-options search-options-jqgridcontainer hidden">
                <div class="col-2-form nomargin">
                    <div class="form-item align-left">
                        <label class="label">Search in</space>
                        <div class="clear"></div>
                        <div class="select-input-wrap nomargin">
                            <select class="searchfield-jqgridcontainer" id="searchfield-jqgridcontainer" name="searchfield-jqgridcontainer">
                            <?php
                                foreach($this->listview_columns as $index => $column) :
                                    if($column['name'] != "action") :
                                        echo '<option value="'.$column['name'].'">'. $this->listview_column_names[$index]  .'</option>';
                                    endif;
                                endforeach;
                            ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-item align-left">
                        <label class="label">Other filter</label>
                        <div class="clear"></div>
                        <div class="select-input-wrap nomargin">
                            <select class="input-select" id="searchop-jqgridcontainer" name="searchop-jqgridcontainer">
                                <option value="eq">equal</option>
                                <option value="ne">not equal</option>
                                <option value="lt">less</option>
                                <option value="le">less or equal</option>
                                <option value="gt">greater</option>
                                <option value="ge">greater or equal</option>
                                <option value="bw">begins with</option>
                                <option value="bn">does not begin with</option>
                                <option value="in">is in</option>
                                <option value="ni">is not in</option>
                                <option value="ew">ends with</option>
                                <option value="en">does not end with</option>
                                <option value="cn" selected="selected">contains</option>
                                <option value="nc">does not contain</option>
                            </select>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </form>
        </div>
    </div>



    <!-- END MAIN CONTENT -->