<!-- start #page-head -->
<div id="page-head" class="page-info">
    <div id="page-title">
        <h2 class="page-title"><span class="title"><?=$this->listview_title;?></span></h2>
    </div>
    <div id="page-desc" class="align-left"><p><?=$this->listview_description?></p></div>
    <?php
        // Page Nav Structure
        if ( isset($pnav) ) echo $pnav;
    ?>
    <div class="clear"></div>
</div><!-- end #page-head -->
<?php $this->load->view( $this->userinfo['rtheme'].'/template/sidebar' ); ?>
<div id="body-content-wrap" class="">

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
    <script type="text/javascript">
        $(document).ready(function(){
            $("#jqgridcontainer").jqGrid({
                url: module.get_value('base_url') + module_link + '/listview',
                loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
                datatype: "json",
                mtype: "POST",
                rowNum: 25,
                rowList: [10,15,25, 40, 60, 85, 100],
                toolbar: [true,"top"],
                height: 'auto',
                autowidth: true,
                pager: "#jqgridpager",
								pagerpos: 'right',
                toppager: true,
                viewrecords: true,
                altRows: true,
                forceFit: true,
                shrinkToFit: true,
                colNames:["<?php echo implode('","', $this->listview_column_names);?>"],
                <?php
					$colModel = array();
					foreach($this->listview_columns as $index => $column){
						$colModel_str = '{';
						$colModel_items = array();
						foreach($column as $property => $value){
							$colModel_items[] = $property . " : ". ( $value == "true" || $value == "false"   ? $value : "'". $value ."'" );
						}
						$colModel_str .= implode(',', $colModel_items);
						$colModel_str .= '}';
						$colModel[] = $colModel_str;
					}
				?>
				colModel:[<?php echo implode(',', $colModel);?>],
                loadComplete: function(data){
                    post_gridcomplete_function(data, '#jqgridcontainer');
                    <?php echo $jqgrid_loadComplete?>
                },
                gridComplete:function(){

                },
                <? if ($this->grid_grouping != "") : ?>
										grouping:true,
                		groupingView : {
                    groupField : [<?php echo $this->grid_grouping?>],
                    groupColumnShow : [false],
                    groupText : ['<b>{0} - {1} Item(s)</b>']
                },
                <? endif; ?>
                caption: "<?=$this->jqgrid_title?>",
                <? if( $this->input->post('prev_search_page') && $this->input->post('prev_search_page') != "") :?>
                page: <?php echo $this->input->post('prev_search_page')?>,
                <? endif;?>
                <? if( $this->input->post('prev_search_str') && $this->input->post('prev_search_str') != "") :?>
                search: true,
                postData: {searchField: "<?php echo $this->input->post('prev_search_field')?>", searchOper: "<?php echo $this->input->post('prev_search_option')?>", searchString: "<?=$this->input->post('prev_search_str')?>"},
                <? endif;?>
                <? if( $this->show_multiselect_column == 1 ) : ?>
                multiselect: true
                <? endif;?>
            });

            $("#jqgridcontainer").jqGrid('navGrid','#jqgridpager',{refresh:false, edit:false, add:false, del:false, search:false});
						
						$("#jqgridcontainer_toppager_center").hide();
						$("#jqgridpager_center").hide();
            $("#jqgridcontainer_toppager_left").append("<?php echo $jqg_buttons?>");
            $("#jqgridpager_left").append("<?php echo $jqg_buttons?>");

            $("#t_jqgridcontainer").append($('.search-wrap').html());

            $(".search-trigger[tooltip]").tipsy({
                title: 'tooltip',
                gravity: 'se',
                opacity: 0.85,
                live: true,
								delayIn: 500
            });
        });


        grid_resize('jqgridcontainer');

        function gridResize_jqgridcontainer() {
            $("#jqgridcontainer").jqGrid("setGridWidth",$("#body-content-wrap").width());
        }

        $(window).resize(gridResize_jqgridcontainer);
    </script>
    <div class="clear"></div>
    <div class="search-wrap hidden">
    		        
        <div class="search-form">asd
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
        <?php if ($this->user_access[$this->module_id]['configure'] == 1):?>
        <div class="icon-label align-right"><a class="icon-16-add icon-16-settings2" href="javascript:manpower_settings()">
                 <span><strong>Settings</strong></span></a>
          </div>
        <?php endif;?>        
    </div>
    <!-- END MAIN CONTENT -->
</div>