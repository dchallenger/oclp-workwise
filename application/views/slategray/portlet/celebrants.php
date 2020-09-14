<div id="<?php echo $portlet_file?>">
  <ul>
    <li><a href="#celebrants-1" id="tab-2">Today</a></li>
    <li><a href="#celebrants-2" id="tab-3">Tomorrow</a></li>
    <li><a href="#celebrants-3" id="tab-4">Later This Week</a></li>
    <li><a href="#celebrants-4" id="tab-5">Past Celebrants</a></li>
  </ul><?php 
	$tab_ctr = 1;
	$celebrants = $this->portlet->get_birthday_celebrants();
	foreach ($celebrants as $celebration_day){
		foreach ($celebration_day as $celebrant){				
			$celebrant->comment_group = $this->portlet->_get_comment_group_identifier($celebrant);
			$count = $this->portlet->fetch_comment_group($celebrant->comment_group);				
			$celebrant->comment_count = ($count) ? count($count) : FALSE;
		}
	}
	foreach ($celebrants as $celebrant_date): ?>
  	<div id="celebrants-<?php echo $tab_ctr++?>"><?php
    	if (count($celebrant_date) > 0): $ctr = 1;?>
      	<ul class="ooo-ul-<?= $tab_ctr ?>"><?php
        	foreach ($celebrant_date as $celebrant):?>
          	<li><strong><?php echo $celebrant->firstname?>&nbsp;<?php echo $celebrant->lastname?><?php echo ($celebrant->aux != '' ? ',' . $celebrant->aux : '') ?></strong>
              <div class="align-right"><?php 
                if($this->config->item('allow_comment_on_bday')):
                ?>
                <span class="icon24 icon-balloon-24" onclick="comments_box('group', '<?php echo $celebrant->comment_group?>', 'refresh_portlet(\'<?php echo $portlet_id?>\', \'<?php echo $portlet_file?>\')')"></span><?php
                  if(!empty( $celebrant->comment_count )) :?>
                  <span class="align-right ctr-small bg-red">
                    <small><?php echo $celebrant->comment_count?></small>
                  </span><?php 
                  endif;
                endif;
                ?>

              </div>
              <br />
              <?php if( $tab_ctr == 4 || $tab_ctr == 5 ){ ?>
              <span class="red small"><?php echo date('F d', strtotime($celebrant->birth_date)); ?></span>
              <br />
              <?php } ?>
              <span><small><?php echo $celebrant->position?></small></span>
            </li><?php
          endforeach;?>
        </ul><?php 
			;else:?>
        <!-- #END today -->
        <p><small>None as of this moment.</small></p><?php
      endif;?>
    </div><?php
	endforeach;?>
</div>
<div class="spacer"></div>

<?php // if (count($celebrants) > 5):?>
<div class="icon-label-group align-right">                          
        <a href="javascript:void(0);" id="ooo-show-more1"><span>Show More</span> </a>      
</div>
<?php //endif;?>

<script type="text/javascript">
	$(document).ready(function() {
    $('#<?php echo $portlet_file?>').tabs();

    $('#tab-5').click(function() {
     $('#ooo-show-more1').show();
     $('.ooo-ul-5 li').not(':lt(5)').hide();
      // $('#ooo-show-more').die().click(function() {
      //   $('.ooo-ul-5 li:hidden:lt(5)').fadeIn('slow');
      //   if ($('.ooo-ul-5 li:hidden').size() == 0) {
      //     $('#ooo-show-more').hide();
      //   }
      // });
    });

    $('#tab-4').click(function() {
      $('#ooo-show-more1').show();
     $('.ooo-ul-4 li').not(':lt(5)').hide();
      // $('#ooo-show-more').die().click(function() {
      //   $('.ooo-ul-4 li:hidden:lt(5)').fadeIn('slow');
      //   if ($('.ooo-ul-4 li:hidden').size() == 0) {
      //     $('#ooo-show-more').hide();
      //   }
      // });
    });

    $('#tab-3').click(function() {
      $('#ooo-show-more1').show();
     $('.ooo-ul-3 li').not(':lt(5)').hide();
      // $('#ooo-show-more').die().click(function() {
      //   $('.ooo-ul-3 li:hidden:lt(5)').fadeIn('slow');
      //   if ($('.ooo-ul-3 li:hidden').size() == 0) {
      //     $('#ooo-show-more').hide();
      //   }
      // });
      
    });

    $('#tab-2').click(function() {

      $('#ooo-show-more1').show();
     $('.ooo-ul-2 li').not(':lt(5)').hide();
      // $('#ooo-show-more').die().click(function() {
      //   $('.ooo-ul-2 li:hidden:lt(5)').fadeIn('slow');
      //   if ($('.ooo-ul-2 li:hidden').size() == 0) {
      //     $('#ooo-show-more').hide();
      //   }
      // });
    });

    $('#ooo-show-more1').die().click(function() { 
      if($('#tab-3').parent().hasClass('ui-state-active') == true)
      {
        $('.ooo-ul-3 li:hidden:lt(5)').fadeIn('slow');
        if ($('.ooo-ul-3 li:hidden').size() == 0) {
          $('#ooo-show-more1').hide();
        }
      } 
      else if($('#tab-2').parent().hasClass('ui-state-active') == true)
      {
        $('.ooo-ul-2 li:hidden:lt(5)').fadeIn('slow');
        if ($('.ooo-ul-2 li:hidden').size() == 0) {
          $('#ooo-show-more1').hide();
        }
      }
      else if($('#tab-4').parent().hasClass('ui-state-active') == true)
      {
        $('.ooo-ul-4 li:hidden:lt(5)').fadeIn('slow');
        if ($('.ooo-ul-4 li:hidden').size() == 0) {
          $('#ooo-show-more1').hide();
        }
      }
       else if($('#tab-5').parent().hasClass('ui-state-active') == true)
      {
        $('.ooo-ul-5 li:hidden:lt(5)').fadeIn('slow');
        if ($('.ooo-ul-5 li:hidden').size() == 0) {
          $('#ooo-show-more1').hide();
        }
      }
    });
  




	});
   
</script>