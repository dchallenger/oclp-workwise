<script>init_datepick;</script>
<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div class="icon-label-group">
   <div style="display: block;" class="icon-label add-more-div"><a href="javascript:void(0);" class="icon-16-add icon-16-add-listview add-more" rel="session"><span>Add Session</span></a></div>
</div>

<div class="form-multiple-add-session">

    <input type="hidden" class="add-more-flag" value="session" />

    <fieldset>
    <?php 
        if (count($session) > 0):

        $session_count = 0;
        foreach ($session as $data):

            $rand = rand(1,10000000);
    ?>

              
                 <div class="form-multiple-add" style="display: block;">
                    <h3 class="form-head">
                        <div class="align-right">
                            <span class="fh-delete">
                                <a href="javascript:void(0)" class="delete-detail" rel="session">DELETE</a>
                                <input type="hidden" class="session_rand" name="session[session_rand][]" value="<?= $rand ?>" />
                            </span>
                        </div>
                    </h3>
                    <div class="form-item odd ">
                        <label class="label-desc gray" for="date">Session No.:</label>
                        <div class="text-input-wrap">    
                            <input type="text" readonly="" class="input-text session_no" style="width:100px;" value="<?= $data['session_no'] ?>" name="session[session_no][]">   
                        </div>        
                    </div>
                    <div class="form-item even ">
                        <label class="label-desc gray" for="date">Training Date:<span class="red font-large">*</span></label>
				        <div class="text-input-wrap">				
							<input type="text" readonly="" class="datepicker input-text datepicker session_date" value="<?= date('m/d/Y',strtotime($data['session_date'])) ?>" name="session[session_date][]">
						</div>                                    
					</div>
                    <div class="form-item even ">
                        <label class="label-desc gray" for="date">Session Time:</label>
                        <div class="text-input-wrap">               
                            <input type="text" readonly="" class="timepicker input-text sessiontime_from" value="<?= date('h:i a',strtotime($data['sessiontime_from'])) ?>" name="session[sessiontime_from][]">
                             to 
                            <input type="text" readonly="" class="timepicker input-text sessiontime_to" value="<?= date('h:i a',strtotime($data['sessiontime_to'])) ?>" name="session[sessiontime_to][]">
                        </div>                                    
                    </div>
                    <div class="form-item even ">
                        <label class="label-desc gray" for="date">Breaktime:</label>
                        <div class="text-input-wrap">               
                            <input type="text" readonly="" class="timepicker input-text breaktime_from" value="<?= date('h:i a',strtotime($data['breaktime_from'])) ?>" name="session[breaktime_from][]">
                             to 
                            <input type="text" readonly="" class="timepicker input-text breaktime_to" value="<?= date('h:i a',strtotime($data['breaktime_to'])) ?>" name="session[breaktime_to][]">
                        </div>                                    
                    </div>
                    <div class="clear"></div>
                </div>
                <div class="clear"></div>     

    <?php

        $session_count++;

        endforeach;
        endif;
    ?>
    </fieldset>
    <input type="hidden" class="session_count" value="<?php echo ( ( $session_count > 0 ) ? $session_count : 0 ); ?>" />
</div>
<hr />  
<div >
    <fieldset>
        <div class="form-item odd">
            <label class="label-desc gray" for="date">Total Training Hours:</label>
            <div class="text-input-wrap">               
                <input type="text" class="input-text" style="width:20%;" value="<?= $session_total_hours ?>" readonly="" name="total_session_hours">
            </div>                                    
        </div>
        <div class="form-item odd">
            <label class="label-desc gray" for="date">Total Breaks:</label>
            <div class="text-input-wrap">               
                <input type="text" class="input-text" style="width:20%;" value="<?= $session_total_breaks ?>" readonly="" name="total_session_breaks">
            </div>                                    
        </div>
    </fieldset>
</div>
