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
                                if (isset($additional_search_options)){
                                    foreach ($additional_search_options as $key => $value) {
                                        echo '<option value="' . $key . '">' . $value . '</option>';
                                    }
                                }                                
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
<!--                     <div class="select-input-wrap">
                        <select class="input-select" name="search_field_categories_value" id="search_field_categories_value">
                            <option value="accounting">Accounting Department</option>
                        </select>
                    </div> -->
                </div>
            </form>
        </div>
    </div>
    <!-- END MAIN CONTENT -->