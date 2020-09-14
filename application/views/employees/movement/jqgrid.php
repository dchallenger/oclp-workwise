<script type="text/javascript">

    $(document).ready(function(){
        $("#jqgridcontainer").jqGrid({
            url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
            loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
            datatype: "json",
            mtype: "POST",
            rowNum: 30,
            rowList: [10,15,25, 30, 60, 85, 100],
            toolbar: [true,"top"],
            height: 'auto',
            autowidth: true,
            pager: "#jqgridpager",
            pagerpos: 'right',
            toppager: true,
            viewrecords: true,
            altRows: true,
            forceFit: false,
            shrinkToFit: false,
            loadonce: false,
            colNames:["<?php echo implode('","', $this->listview_column_names); ?>"],
<?php
$colModel = array();
foreach ($this->listview_columns as $index => $column) {
    $colModel_str = '{';
    $colModel_items = array();
    foreach ($column as $property => $value) {
				if($property === "name"){
					$value = strtolower( $value );
					if(sizeof(explode(' as ', $value)) > 1){
						$as_part = explode(' as ', $value);
						$value = strtolower( trim( $as_part[1] ) );
					}
				}
        $colModel_items[] = $property . " : " . ( $value == "true" || $value == "false" ? $value : "'" . $value . "'" );
    }
    $colModel_str .= implode(',', $colModel_items);
    $colModel_str .= '}';
    $colModel[] = $colModel_str;

}

?>
                                        colModel:[<?php echo implode(',', $colModel); ?>],
                                        loadComplete: function(data){
                                            //post_gridcomplete_function(data, '#jqgridcontainer');
<?php echo $jqgrid_loadComplete ?>
                            },
                            gridComplete:function(){

                            },
<? if ($this->grid_grouping != "") : ?>
                            grouping:true,
                            groupingView : {
                                groupField : ['<?php echo $this->grid_grouping ?>'],
                                groupColumnShow : [false],
                                groupText : ['<b>{0} - {1} Item(s)</b>']
                            },
<? endif; ?>
                        caption: "<?= $this->jqgrid_title ?>",
<? if ($this->input->post('prev_search_page') && $this->input->post('prev_search_page') != "") : ?>
                            page: <?php echo $this->input->post('prev_search_page') ?>,
<? endif; ?>
<? if ($this->input->post('prev_search_str') && $this->input->post('prev_search_str') != "") : ?>
                            search: true,
                            postData: {searchField: "<?php echo $this->input->post('prev_search_field') ?>", searchOper: "<?php echo $this->input->post('prev_search_option') ?>", searchString: "<?= $this->input->post('prev_search_str') ?>", filter: "<?php echo $this->encrypt->encode($this->filter);?>"},
<? endif; ?>
<? if (isset($default_query)) : ?>
                search: true,
                postData: {searchField: "<?php echo $default_query_field; ?>", searchOper: "eq", searchString: "<?php echo $default_query_val; ?>", filter: "<?php echo $this->encrypt->encode($this->filter);?>"},
<? endif; ?>
<?php
	if( !($this->input->post('prev_search_str') && $this->input->post('prev_search_str') != "") &&  !isset($default_query) && (isset( $this->filter ) && !empty($this->filter))){ ?>
		 search: false,
     postData: {filter: "<?php echo $this->encrypt->encode($this->filter);?>"},
	<?php
	}
?>



<? if ($this->show_multiselect_column == 1) : ?>
                            multiselect: true
<? endif; ?>
                    });

                    $("#jqgridcontainer").jqGrid('setGroupHeaders', {
                          useColSpanStyle: false, 
                          groupHeaders:[
                            {startColumnName: 'position_title_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">Position Title</span>'},
                            {startColumnName: 'department_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">Department</span>'},
                            {startColumnName: 'rank_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">Rank</span>'},
                            {startColumnName: 'employee_type_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">Employee Type</span>'},
                            {startColumnName: 'job_level_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">Job Level</span>'},
                            {startColumnName: 'range_of_rank_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">Range of Rank</span>'},
                            {startColumnName: 'rank_code_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">Rank Code</span>'},
                            {startColumnName: 'company_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">Company</span>'},
                            {startColumnName: 'division_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">Division</span>'},
                            {startColumnName: 'location_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">Location</span>'},
                            {startColumnName: 'profit_or_non_profit_center_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">Profit or Non-Profit Center</span>'},
                            {startColumnName: 'organization_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">Organization</span>'},
                            {startColumnName: 'hris_role_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">HRIS Role</span>'},
                            {startColumnName: 'no_of_sick_leaves_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">No. of Sick Leaves</span>'},
                            {startColumnName: 'no_of_vacation_leaves_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">No. of Vacation Leaves</span>'},
                            {startColumnName: 'no_of_emergency_leaves_current', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">No. of Emergency Leaves</span>'}
                          ]
                    }).trigger("reloadGrid");


                    $("#jqgridcontainer").jqGrid('navGrid','#jqgridpager',{refresh:false, edit:false, add:false, del:false, search:false});
						
                    $("#jqgridcontainer_toppager_center").hide();
                    $("#jqgridpager_center").hide();
                    
                    //$("#t_jqgridcontainer").prepend('<?php echo $jqg_buttons ?>');
                    
                    $("#t_jqgridcontainer").append();
                    <?php if(isset ($tab) ) :?> $("#t_jqgridcontainer").append('<?php echo $tab?>'); <?php endif;?>

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
                    $("#jqgridcontainer").jqGrid("setGridWidth", $("#body-content-wrap").width() );
                }

                $(window).resize(gridResize_jqgridcontainer);

</script>