<?php
$ci =& get_instance();

$parent_id = get_parent($ci->module_id);

$ci->db->where('module_id', $parent_id);
$parent = $ci->db->get('module')->row_array();

$side_nav = $header_nav[$parent_id];

$employee_type = $this->db->get_where('employee',array('employee_id'=>$this->userinfo['user_id']))->row()->employee_type;

$uniform_order_config = $this->hdicore->_get_config('uniform_order_settings');

$uniform_order = $this->db->get_where('employee_uniform_order',array('employee_id'=>$this->userinfo['user_id'],'year'=>date('Y')))->num_rows();

?>
<aside>
   <!-- <a href="javascript:;" id="btn-panel" class="close-panel"></a> -->
    <?=(isset($side_nav_before)) ? $this->load->view($side_nav_before) : '' ?>
    <!-- Start filters -->
    <?php if (isset($module_filters)): $anchors = prepare_filters($module_filters,true);?> 
        
        <ul class="aside-nav">        
          <h3 class="menu-header">
            <img src="<?php echo base_url() . $this->userinfo['theme'] . '/icons/' . $this->module_icon?>" />
            <span class="header-link"><?=(isset($module_filter_title) && $module_filter_title != '') ? $module_filter_title : $this->module_name?></span>
            <span class="slidetoggle"><a class="icon-16-portlet-fold"></a> </span>         
          </h3>        
          <ul id="ul-filter">
            <script>
            anchors = new Array();
            anchors[0] = new Array();
            <?php $ctr = 0; foreach ($anchors as $key => $anchor):?>
                anchors[0][<?=$ctr++?>] = '<?=$anchor?>';
            <?php endforeach;?>
            create_filter_list(anchors);
            </script>
          </ul>
        </ul>        

    <?php endif;?>
    <div class="clear"></div>
    <!-- #END filters -->
    <?php if (count($side_nav['child']) > 0):?>
       
     
        <ul id="menu">
            <li>
         		<h3 class="menu-header">
              		<img src="<?php echo base_url() . $this->userinfo['theme'] . '/icons/' . $parent['sm_icon']?>" />
                    <span class="header-link"><?php echo $parent['short_name']; ?></span>
                 	<span class="slidetoggle"><a class="icon-16-portlet-fold"></a> </span>
         	 	</h3>
                <ul class="submenu">
                <?php
                    echo create_side_nav($side_nav['child']);
                ?>
                </ul>
                <div class="menu-footer"></div>
            </li>           
        </ul>   
        <div class="spacer"></div>

<!--     <ul id="menu">
        <li>
            <a href="#">1st level</a>
            <ul class="submenu">
                <li>
                    <a href="#">2nd level</a>
                    <ul>                       
                        <li>
                            <a href="#">3rd level</a>
                                <ul>
                                    <li><a href="#">4th level</a></li>
                                </ul>
                        </li>
                    </ul>
                </li>               
            </ul>
        </li>     -->   

    <?php endif;?>  
    <!-- Quick links -->

    <?php  

    if ( (isset($quick_link) && count($quick_link) > 0) || ( $uniform_order_config['enable_edit'] == 1 && $uniform_order == 0 && $employee_type == 1 ) ):  ?>
    
    <ul class="aside-nav">
        <h3 class="menu-header">
            <img src="<?php echo base_url() . $this->userinfo['theme']?>/icons/icon-default-16.png" />
            <span class="header-link">Quick Links</span>
            <span class="slidetoggle"><a class="icon-16-portlet-fold"></a></span>         
        </h3>        
        <ul>
        <?php foreach ($quick_link as $quicklink):?>
            <li><a href="<?=$quicklink['quicklink_link']?>"><span class="align-left aside-link"><?=$quicklink['quicklink_name']?></span></a></li>
        <?php endforeach;?>
        <?php if( $uniform_order_config['enable_edit'] == 1 && $uniform_order == 0 && $employee_type == 1 && ( ( date('Y-m-d') >= date('Y-m-d',strtotime($uniform_order_config['date_from'])) ) && ( date('Y-m-d') <= date('Y-m-d',strtotime($uniform_order_config['date_to'])) ) ) ): ?>
            <li><a id="uniform_order" href="" onclick="javascript:return false;"><span class="align-left aside-link">Uniform Order</span></a></li>
        <?php  endif; ?>
        </ul>
    </ul>
    
    <?php endif;?>      
    <!-- End quick links -->
    <?=(isset($side_nav_after)) ? $this->load->view($side_nav_after) : '' ?>


    <!-- joab's mods -->



<!-- 
    <div class="spacer"></div>
    <ul id="menu">
        <li>
            <a href="#">1st level</a>
            <ul class="submenu">
                <li>
                    <a href="#">2nd level</a>
                    <ul>                       
                        <li>
                            <a href="#">3rd level</a>
                                <ul>
                                    <li><a href="#">4th level</a></li>
                                </ul>
                        </li>
                    </ul>
                </li>               
            </ul>
        </li>

        <li>
            <a href="#">1st level</a>
            <ul class="submenu">
                <li>
                    <a href="#">2nd level</a>
                    <ul>                       
                        <li>
                            <a href="#">3rd level</a>
                                <ul>
                                    <li><a href="#">4th level</a></li>
                                </ul>
                        </li>
                    </ul>
                </li>               
            </ul>
        </li>
    </ul>  -->

    <script>
    // Copyright (c) 2011 Peter Chapman - www.topverses.com
    // Freely distributable for commercial or non-commercial use
    $(document).ready(function() {
        $('#menu ul ul').hide();
        $('#menu li a').mouseover(
            
            function() {
                var openMe = $(this).next();
                var mySiblings = $(this).parent().siblings().find('ul');
                if (openMe.is(':visible')) {
                    openMe.slideUp('normal');  
                } else {
                    mySiblings.slideUp('normal');  
                    openMe.slideDown('normal');
                }
              }

        );

        $('.submenu').mouseleave(function(){

            $(this).find('li').each(function(){

                if( $(this).find('ul').length > 0 ){

                    $(this).find('ul').slideUp('normal');

                }

            });

        });


        $('.icon-16-portlet-unfold').live("click", function(){
            $(this).addClass("icon-16-portlet-fold");
            $(this).removeClass("icon-16-portlet-unfold");
        });

        $('.icon-16-portlet-fold').live("click", function(){
            $(this).addClass("icon-16-portlet-unfold");
            $(this).removeClass("icon-16-portlet-fold");
        });


        $('#uniform_order').live("click",function(){

                   var user_id = user.get_value('user_id');

                   Boxy.confirm("Do you want to order uniform:", function() {

                        $.ajax({
                            url: module.get_value('base_url') + 'employee/uniform_quicklink/save_order',
                            data: 'user_id=' + user_id,
                            dataType: 'json',
                            type: 'post',       
                            success: function ( data ) { 
                                $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
                                window.location.reload( false );
                            }
                        });  


                    }, {title: 'Uniform Order'});

        });

    });
    function open_me(url_to)
    {
        if(url_to != "#")
        {
            url_to = module.get_value('base_url') + url_to;
            window.location = url_to;
        }
    }


    </script>

</aside>	




