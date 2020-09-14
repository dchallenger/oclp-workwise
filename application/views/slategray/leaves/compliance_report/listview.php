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
          <input type="hidden" name="export_link" id="export_link" value="<?=site_url('leave/compliance_report/export')?>" />
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
              <div class="form-item even">
                <label class="label-desc gray" for="period_year">Period Year:<span class="red font-large">*</span></label>
                  <div class="select-input-wrap">
                    <select id="period_year" name="period_year">
                        <?php
                            if ($period_year->num_rows() > 0):
                                foreach ($period_year->result() as $row){
                                    print '<option value="'.$row->period_year.'">'.$row->period_year.'</option>';
                                }
                            endif;    
                        ?>
                    </select>
                  </div>
              </div>
              <div class="form-item odd">
                <div id="multi-select-loader"></div>                
                <div id="multi-select-main-container" style="display:none">
                    <label class="label-desc gray" for="department" id="category_selected"></label>
                    <div class="multiselect-input-wrap" id="multi-select-container">
                    </div>
                </div>
              </div>
              <div class="form-item even" id="period_date_container" style="display:none">
                <label class="label-desc gray" for="date_period">Period Date:<span class="red font-large">*</span></label>
                  <div class="select-input-wrap">
                    <select id="period_date" name="period_date">
                    </select>
                  </div>
              </div>              
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

    <!-- END MAIN CONTENT -->