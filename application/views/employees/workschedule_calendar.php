<!-- start #page-head -->
<div id="page-head" class="page-info">
    <div id="page-title">
        <h2 class="page-title"><span class="title"><?= $this->detailview_title; ?></span></h2>    
    </div>  
    <div id="page-desc" class="align-left"><p><?= $this->detailview_description ?></p></div>                        
        
    <div class="clear"></div>
</div><!-- end #page-head -->
<?php $this->load->view($this->userinfo['rtheme'] . '/template/sidebar'); ?>

<div id="body-content-wrap">
    <!-- content alert messages -->
    <div id="message-container">
        <?php
        if (isset($msg)) {
            echo is_array($msg) ? implode("\n", $msg) : $msg;
        }
        if (isset($flashdata)) {
            echo $flashdata;
        }
        ?>
    </div>    
    <!-- content alert messages -->    
<?php if (isset($error)) : ?>
        <div id="message_box" class="attention" style="padding-left: 60px;width:60%;margin: 0 auto;">
            <img src="<?= base_url() . $this->userinfo['theme']; ?>/images/exclamation-big.png" alt="" >
            <h3 style="margin: 0.3em 0 0.5em 0">Oops! <?= $error ?></h3>

            <p><?= $error2 ?></p>
        </div>
<? else : ?>    
    <div id="workschedule-calendar"></div>
<?php endif;?>    
</div>

<script type="text/javascript">
    var shifts = new Array;
    $(document).ready(function () {
        var schedules = new Array;
        <?php
        $ctr = 0;
        foreach ($schedules as $date => $schedule):?>
            js_date = new Date('<?=date('F d, Y 00:00:00', strtotime($date))?>').getTime();
            schedules[<?=$ctr?>] = js_date;
            shifts[<?=$ctr++?>] = '<?=$schedule?>';
        <?php endforeach;?>

        $('#workschedule-calendar').datepicker({
            changeMonth: true,
            changeYear: true,
            showOtherMonths: true,
            numberOfMonths: [2,4],
            /** Control date display **/
            beforeShowDay: function (date) {                
                response = new Array;
                response[0] = true;
                response[2] = 'No schedule set.';
                shift = has_shift(date.getTime(), schedules);
                if (shift !== false) {
                    if (shift == 'RES' || shift == 'OFF') {
                        response[1] = 'error';
                    } else {
                        response[1] = 'attention';
                    }                    
                    response[2] = shift;
                }

                return response;
            },
            onSelect: function (date, instance) {
                alert(date);
            }
        });
    });

    function has_shift(needle, haystack) {
        var length = haystack.length;

        for(var i = 0; i < length; i++) {
            if(haystack[i] == needle) return shifts[i];
        }
        return false;
    }    
</script>