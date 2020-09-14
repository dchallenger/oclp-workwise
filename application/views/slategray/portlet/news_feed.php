<?php
	$memo_mod = $this->hdicore->get_module('Memorandum');
	$memos = $this->system->get_user_active_memo();



	if( $memos ){
		$memo_li = "";
		$new_memo = 0;
		foreach( $memos as $memo ){
			$memo_comment_group = $this->portlet->_get_memo_comment_group_identifier($memo);
			$count = $this->portlet->fetch_comment_group($memo_comment_group);
			$memo_comment_count = ($count) ? count($count) : FALSE;

			$memo_li .= '<li>';
			$memo_li .= '<a href="javascript:view_memo('.$memo['memo_id'].')">'.$memo['memo_title'].'</a>';
			
			if( $memo['allow_comments'] == 1 ){
				$memo_li .='<div class="align-right">
                <span class="icon24 icon-balloon-24 comment_balloon" portlet_id="'.$portlet_id.'" portlet_file="'.$portlet_file.'" memo_comment_group="'.$memo_comment_group.'" onclick=""></span>';
            
	            if( !empty($memo_comment_count)){

		            $memo_li .= '<span class="align-right ctr-small bg-red">
		                    <small>'.$memo_comment_count.'</small>
		                  </span>';

	            }

            	$memo_li .= '</div>';
            }

            if( !$memo['read'] ){
				$memo_li .= '<span class="align-right green"><small>New</small></span>';
				$new_memo++;
			}
			$memo_li .= '<br/><span cla="memo-posteddate"><small>Published '. date('F d, Y', strtotime($memo['publish_from'])) .'</small></span>';
			$memo_li .= '</li>';
		}
		
	}
	
	$employee_updates = $this->system->get_employee_updates();
	if( $employee_updates ){
		$employee_updates_li = "";
		$new_employee_update = 0;
		foreach( $employee_updates as $employee_update ){
			$employee_comment_group = $this->portlet->_get_employee_comment_group_identifier($employee_update);
			$count = $this->portlet->fetch_comment_group($employee_comment_group);
			$employee_comment_count = ($count) ? count($count) : FALSE;
			$employee_updates_li .= '<li>';
			$employee_updates_li .= '<a href="javascript:view_memo('.$employee_update['memo_id'].')">'.$employee_update['memo_title'].'</a>';
			
			if( $employee_update['allow_comments'] == 1 ){
				$employee_updates_li .='<div class="align-right">
                <span class="icon24 icon-balloon-24 employee_comment_balloon" portlet_id="'.$portlet_id.'" portlet_file="'.$portlet_file.'" employee_comment_group="'.$employee_comment_group.'" onclick=""></span>';
            
	            if( !empty($employee_comment_count)){

		            $employee_updates_li .= '<span class="align-right ctr-small bg-red">
		                    <small>'.$employee_comment_count.'</small>
		                  </span>';

	            }

            	$employee_updates_li .= '</div>';
            }

			if( !$employee_update['read'] ){
				$employee_updates_li .= '<span class="align-right green"><small>New</small></span>';
				$new_employee_update++;
			}
			$employee_updates_li .= '<br/><span cla="memo-posteddate"><small>Published '. date('F d, Y', strtotime($employee_update['publish_from'])) .'</small></span>';
			$employee_updates_li .= '</li>';
		}
	}
?>
<div id="<?php echo $portlet_file?>">
	<ul>
		<li><a href="#newsfeed-memo">MEMORANDUM / ANNOUNCEMENT <?php if(!empty($new_memo)) :?><span class="ctr-inline bg-orange"><?php echo $new_memo?></span><?php endif;?></a></li>
    <li><a href="#newsfeed-employeeupdates">EMPLOYEE MOVEMENT <?php if(!empty($new_employee_update)) :?><span class="ctr-inline bg-orange"><?php echo $new_employee_update?></span><?php endif;?></a></li>
	</ul>
  <div id="newsfeed-memo">
  	<?php if($memos):?>
      <ul>
        <?php echo $memo_li?>
      </ul>
    <?php ;else:?>
    	<p><small>None as of this moment.</small></p>
    <?php endif;?>
    <div class="spacer"></div>
    <div class="icon-label-group align-right">                    
      <div class="icon-label">
        <a class="icon-16-listback" href="<?php echo base_url().$memo_mod->class_path?>">
        <span>view list</span> </a>
      </div>
    </div>
  </div>
  <div id="newsfeed-employeeupdates">
  	<?php if($employee_updates):?>
      <ul>
        <?php echo $employee_updates_li?>
      </ul>
    <?php ;else:?>
    	<p><small>None as of this moment.</small></p>
    <?php endif;?>
    <div class="spacer"></div>
    <div class="icon-label-group align-right">                    
      <div class="icon-label">
        <a class="icon-16-listback" href="<?php echo site_url('employee/memorandum')?>">
        <span>view list</span> </a>
      </div>
    </div>
  </div>
</div>  
  
  
<script type="text/javascript">
	$(document).ready(function() {
		$('#<?php echo $portlet_file?>').tabs();

		$('.comment_balloon').click(function(){

			var crefresh_portlet = refresh_portlet($(this).attr('portlet_id'),$(this).attr('portlet_file'));

			comments_box('group',$(this).attr('memo_comment_group'),crefresh_portlet);

			return false;

		});

		$('.employee_comment_balloon').click(function(){

			var crefresh_portlet = refresh_portlet($(this).attr('portlet_id'),$(this).attr('portlet_file'));

			comments_box('group',$(this).attr('employee_comment_group'),crefresh_portlet);

			return false;

		});


	});
	
	if( !window.view_memo ){
		var memo_detail = false;
		function view_memo( memo_id ){
			if( memo_id != "" && memo_id > 0 ){
				var data = "record_id="+memo_id;
				$.ajax({
					url: '<?php echo base_url().$memo_mod->class_path?>/view_memo',
					type:"POST",
					data: data,
					dataType: "json",
					beforeSend: function(){
						$.blockUI({ message: '<div class="now-loading align-center"><img src="<?php echo base_url()?>'+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
					},
					success: function(data){
						if( !memo_detail && data.memo_detail != ""){
							var width = $(window).width()*.7;
							memo_detail = new Boxy('<div id="boxyhtml" style="max-width: '+width+'px;min-width:700px;max-height: 400px;overflow: auto;">'+ data.memo_detail +'</div><p>&nbsp;</p>',
							{
								title: 'Memorandum Detail',
								draggable: false,
								modal: true,
								center: true,
								unloadOnHide: true,
								afterShow: function(){ $.unblockUI(); },
								beforeUnload: function(){ $('.tipsy').remove(); memo_detail = false;}
							});	
						}
						else{
						 $.unblockUI();
						}
						if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
					}
				});
			}	
		}
	}

</script>  

