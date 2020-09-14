<?php
if (!isset($listview)) {
    $listview = base_url() . $this->module_link . '/listview';
} else {
    $listview = base_url() . $this->module_link . '/' . $listview;
}

if (isset($searchform)) {
    $this->load->view($this->userinfo['rtheme'] . '/' . $searchform);
}

if (!isset($other)) {
    $other = '';
}
?>
<table id="<?= $container ?>"></table>
<div id="<?= $pager ?>"></div>
<?php echo jqgrid_in_boxy()?>
<script type="text/javascript">
    $(document).ready(function(){                   
        var x = setTimeout("init_jqgrid()", 100);                 
    });
	
    function init_jqgrid(){
        $("#<?= $container ?>").jqGrid({
            url: "<?php echo $listview; ?>", 
            datatype: "json",
            loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') +'/images/loading.gif"><br />Loading...',
            mtype: "POST", 
            rowNum: 10,
            rowList: [5,10,15],
            toolbar: [true,"top"],
            height: 'auto',
            autowidth: true, 
            pager: "#<?= $pager ?>",
            pagerpos: "right",
            toppager: true,
            viewrecords: true,
            grouping:true,
            altRows: true,
            forceFit: true,
            colNames:["<?php echo implode('","', $this->listview_column_names); ?>"],
<?php
$colModel = array();
foreach ($this->listview_columns as $index => $column) {
    $colModel_str = '{';
    $colModel_items = array();
    foreach ($column as $property => $value) {
        $colModel_items[] = $property . " : '" . $value . "'";
    }
    $colModel_str .= implode(',', $colModel_items);
    $colModel_str .= '}';
    $colModel[] = $colModel_str;
}
?>
            colModel:[<?php echo implode(',', $colModel); ?>],
            caption: "<?= $this->jqgrid_title ?>",
            gridComplete: function(){
                related_module_boxy[<?= $fmlinkctr ?>].center();
                related_module_boxy[<?= $fmlinkctr ?>].show();
                $('.ui-jqgrid tr.ui-row-ltr td a.icon-button').tipsy({
                    title: function()
                    {
                        return this.getAttribute('tooltip');
                    },
                    html: true,
                    opacity: .8,
                    gravity: $.fn.tipsy.autoNS,
                    delayIn: 500
                });
                grid_resize('<?= $container ?>');
                boxyHeight(related_module_boxy[<?= $fmlinkctr ?>], '#related_module_boxy-<?= $fmlinkctr ?>-container');
            },
            postData: {related_module:true, fieldname: '<?= $this->input->post('fieldname') ?>', column: '<?= $this->input->post('column') ?>', container : "<?= $container ?>", fmlinkctr: "<?= $fmlinkctr ?>", other: "<?= $other ?>"}
        });
        
        $("#<?= $container ?>").jqGrid('navGrid','#<?= $pager ?>',{refresh:false, edit:false, add:false, del:false, search:false}); 
        $("#<?= $container ?>_toppager_center").hide();
        $("#<?= $container ?>_toppager_right").css('width','55%');
        $("#<?= $pager ?>_right").css('width','55%');
        $("#t_<?= $container ?>").append($('.search-wrap.list-view-boxy').html());
        $("#<?= $container ?>_toppager_left").append('<?= addslashes($jqg_buttons) ?>');
        $("#<?= $pager ?>_left").append('<?= addslashes($jqg_buttons) ?>');
       
        $(".search-trigger[tooltip]").tipsy({
            title: 'tooltip',
            gravity: 'se',
            opacity: 0.85,
            live: true
        });	
    }
        
        
    var post_fieldname = "<?= $this->input->post('fieldname') ?>";
    var post_column = "<?= $this->input->post('column') ?>";
</script> 
<div class="clear"></div>					
<div class="search-wrap hidden list-view-boxy">
    <div class="search-form">
        <div class="search-trigger" tooltip="Search Options">
            <div><a href="javascrpt:void(0)" class="icon-16-search-opts">Search Options</a></div>
        </div>
        <div class="search-input">
            <form class="search" name="form-search-<?= $container ?>" id="form-search-<?= $container ?>" jqgridcontainer="<?= $container ?>">
                <input id="search" class="search-<?= $container ?>" type="text" value="Search..." onclick="" onfocus="javascript:($(this).val()=='Search...' ? $(this).val('') : '')" onblur="javascript:($(this).val()=='' ? $(this).val('Search...') : '')"/>
                <input type="button" id="search-btn" class="search-btn" value="Search">
            </form>
        </div>    

        <div class="clear"></div>
        <form class="style2 search-options search-options-<?= $container ?> hidden">
            <div class="col-2-form nomargin">
                <div class="form-item align-left">
                    <label class="label">Search in</space>
                        <div class="clear"></div>                                    
                        <div class="select-input-wrap nomargin">	
                            <select class="searchfield-<?= $container ?>" id="searchfield-<?= $container ?>" name="searchfield-<?= $container ?>">
                                <?php
                                foreach ($this->listview_columns as $index => $column) :
                                    if ($column['name'] != "action") :
                                        echo '<option value="' . $column['name'] . '">' . $this->listview_column_names[$index] . '</option>';
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
                        <select class="input-select" id="searchop-<?= $container ?>" name="searchop-<?= $container ?>">
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
<div class="clear"></div>
<?php
if(isset($scripts) && sizeof($scripts) > 0) {
    foreach ($scripts as $script) {
        echo $script;
    }
}
