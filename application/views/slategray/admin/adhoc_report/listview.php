<style>
    #sortable { list-style-type: none; margin: 0; padding: 0; width: 100%; }
    #sortable li { margin: 0 3px 3px 3px; padding: 0.4em; font-size: 1.0em; height: auto; }
    #filter div { margin: 0 3px 3px 3px; font-size: 1.0em;clear: left; }
    #filter label { padding-left: 5px; }
    #sortable li.item { 
        border: 1px solid #B3986B; 
        background-color: #E6C48C; 
    }
</style>

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
          <input type="hidden" name="export_link" id="export_link" value="<?=site_url('admin/adhoc_report/export')?>" />
          <input type="hidden" name="previous_page" id="previous_page" value="<?=base_url().$this->module_link?>/detail"/>        
          <div id="form-div">
                <div class="col-2-form"> 
                  <div class="form-item odd ">
                    <label class="label-desc gray" for="module">Module:<span class="red font-large">*</span></label>
                    <div class="multiselect-input-wrap">
                        <select id="module_id" style="width:400px;" name="module_id">
                            <option value="">Select Module</option>
                            <?php
                                foreach($module->result() as $row){ ?>
                                <option value="<?php echo $row->module_id ?>"><?php echo $row->short_name ?></option>
                            <?php } ?>
                        </select>
                    </div>
                  </div>
                  <div class="form-item even">
                    <div id="multi-select-loader"></div>
                    <div id="multi-select-main-container" style="display:none">
                        <label class="label-desc gray" for="fields" id="category_selected">Fields<span class="red font-large">*</span></label>
                        <div class="multiselect-input-wrap" id="multi-select-container">
                        </div>
                    </div>
                  </div>   
                  <div class="form-item odd ">
                    <label class="label-desc gray" for="module">Title of Report:</label>
                    <div class="text-input-wrap">
                        <input id="title_report" class="input-text" type="text" value="" name="title_report" style="width:397px">
                    </div>
                  </div>       
                  <div class="form-item odd" id="sortable-container" style="display:none">
                    <label class="label-desc gray" for="module">Column Arrangement:</label>
                        <br />
                        <div style="border:1px solid #B8B5B0;width:395px; background-color:#F0E9DF;padding: 10px 5px;" >
                            <ul id="sortable">
                            </ul>
                        </div>
                  </div>  
                  <div class="form-item even" id="filter-container" style="display:none">
                    <label class="label-desc gray" for="module">Filter By:</label>
                        <br />
                        <div style="border:1px solid #B8B5B0;width:495px; background-color:#F0E9DF;padding: 10px 5px;" >
                            <ul id="filter">
                            </ul>
                        </div>
                  </div> 
                  <div class="form-item odd" id="orderby-container" style="display:none">
                    <label class="label-desc gray" for="module">Sort By:</label>
                        <br />
                        <div style="border:1px solid #B8B5B0;width:395px; background-color:#F0E9DF;padding: 10px 5px;" >
                            <ul id="orderby" style="float:left;width:79%">
                            </ul>
                            <ul style="float:right;width:20%">
                                <select name="sortby" style="width:100%">
                                    <option value="ASC">ASC</option>
                                    <option value="DESC">DESC</option>
                                </select>
                            </ul>
                            <br style="clear:both" />
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

<!--     <table id="jqgridcontainer"></table>
    <div id="jqgridpager"></div>
    <?php echo $this->load->view($this->userinfo['rtheme'] . '/' . $jqgrid); ?>
    <div class="clear"></div> -->

    <?php /*
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

    */ ?>

    <!-- END MAIN CONTENT -->