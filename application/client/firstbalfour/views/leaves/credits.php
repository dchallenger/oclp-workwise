<?php 
    $css = "border:1px solid #eee;padding:5px;";
    $employee_info = $this->db->get_where('user',array('employee_id'=>$record->employee_id))->row();

?>
<?php
    if($this->config->item('remove_el_leave_balance_viewing') == 1)
        $visibility = "display:none;";

    $last_year=date("Y",strtotime("-1 year"));
    $prev_balance=$this->db->get_where('employee_leave_balance', array('year'=>$last_year, 'employee_id'=>$this->userinfo['user_id']))->row();
    $balance=$this->db->get_where('employee_leave_balance', array('year'=>date('Y'), 'employee_id'=>$this->userinfo['user_id']))->row();
?>
<table width="80%" cellspacing="10" cellpadding="10" class="balance">
    <thead height="25px" align="center">
            <td width="20%">&nbsp;</td>
            <td style="<?=$css?>"width="10%">VL</td>
            <td style="<?=$css.$visibility?>"width="10%">EL</td>
            <td style="<?=$css?>"width="10%">SL</td>
            <?php 

            if( $record_id == "-1" ){
                 if( $this->userinfo['sex'] == 'male' ){ ?>
                <td style="<?=$css?>"width="10%" id="mpl_header">PL</td>
            <?php }else{ ?>
                <?php if( $this->config->item('client_no') != 2 ){ ?>
                    <td style="<?=$css?>"width="10%" id="mpl_header">ML</td>
                <?php } ?>
            <?php }
            }else{
                if( $employee_info->sex == 'male' ){ ?>
                <td style="<?=$css?>"width="10%" id="mpl_header">PL</td>
            <?php }else{ ?>
                <?php if( $this->config->item('client_no') != 2 ){ ?>
                    <td style="<?=$css?>"width="10%" id="mpl_header">ML</td>
                <?php } 
            }
            }
            ?>
<!--             <td style="<?=$css?>"width="10%">BOL</td>
            <td style="<?=$css?>"width="10%">SIL</td>
            <td style="<?=$css?>"width="10%">UL</td> -->
    </thead>
    <tr height="20px" align="center">
            <td style="<?=$css?>">Previous Year Balance</td>
            <td style="<?=$css?>;text-align:center;" colspan='2' id="vl_prev_balance"></td>
            <td style="<?=$css?>" id="sl_prev_balance"></td>
            <td style="<?=$css?>" id="mpl_prev_balance"></td>
<!--             <td style="<?=$css?>" id="bol_prev_balance"></td>
            <td style="<?=$css?>" id="sil_prev_balance"></td>
            <td style="<?=$css?>" id="ul_prev_balance"></td> -->
    </tr>    
    <tr height="20px" align="center">
            <td style="<?=$css?>">Earned Credits <?php echo date('Y')?></td>
            <td style="<?=$css?>;text-align:center;" id="vl" colspan='2'></td>
            <td style="<?=$css?>" id="sl"></td>
            <?php if( $record_id == "-1" ){ ?>
                <?php 
                    if( $this->config->item('client_no') == 2 ){
                        
                        if( $this->userinfo['sex'] == 'male' ){
                        ?>
                            <td style="<?=$css?>" id="mpl"></td>
                        <?php
                        }
                        else{
                        ?>
                            <td style="<?=$css?> display:none;" id="mpl"></td>
                        <?php 
                        }

                    }
                    else{ 
                    ?>
                    <td style="<?=$css?>" id="mpl"></td>
                    <?php    
                    }
                }
                else{
                    ?>
                <?php 
                    if( $this->config->item('client_no') == 2 ){
                        
                        if( $employee_info->sex == 'male' ){
                        ?>
                            <td style="<?=$css?>" id="mpl"></td>
                        <?php
                        }
                        else{
                        ?>
                            <td style="<?=$css?> display:none;" id="mpl"></td>
                        <?php 
                        }

                    }
                    else{ 
                    ?>
                    <td style="<?=$css?>" id="mpl"></td>
                    <?php    
                    }
                }
            ?>
<!--             <td style="<?=$css?>" id="bol"></td>
            <td style="<?=$css?>" id="sil"></td>
            <td style="<?=$css?>" id="ul"></td> -->
    </tr>
    <tr height="20px" align="center">
            <td style="<?=$css?>">Used</td>
            <td style="<?=$css?>" id="vl_used"></td>                        
            <td style="<?=$css.$visibility?>" id="el_used"></td>
            <td style="<?=$css?>" id="sl_used"></td>
            <?php if( $record_id == "-1" ){ ?>
                <?php 
                    if( $this->config->item('client_no') == 2 ){
                        
                        if( $this->userinfo['sex'] == 'male' ){
                        ?>
                            <td style="<?=$css?>" id="mpl_used"></td>
                        <?php
                        }
                        else{
                        ?>
                            <td style="<?=$css?> display:none;" id="mpl_used"></td>
                        <?php 
                        }

                    }
                    else{ 
                    ?>
                    <td style="<?=$css?>" id="mpl_used"></td>
                    <?php    
                    }
                }
                else{
                    ?>
                <?php 
                    if( $this->config->item('client_no') == 2 ){
                        
                        if( $employee_info->sex == 'male' ){
                        ?>
                            <td style="<?=$css?>" id="mpl_used"></td>
                        <?php
                        }
                        else{
                        ?>
                            <td style="<?=$css?> display:none;" id="mpl_used"></td>
                        <?php 
                        }

                    }
                    else{ 
                    ?>
                    <td style="<?=$css?>" id="mpl_used"></td>
                    <?php    
                    }
                }
            ?>
<!--             <td style="<?=$css?>" id="bol_used"></td>
            <td style="<?=$css?>" id="sil_used"></td>
            <td style="<?=$css?>" id="ul_used"></td>     -->        
    </tr>
    <tr height="20px" align="center">
            <td style="<?=$css?>">Balance</td>
            <td style="<?=$css?>;text-align:center;" colspan='2' id="vl_balance"></td>
            <td style="<?=$css?>" id="sl_balance"></td>        

            <?php if( $record_id == "-1" ){ ?>
                <?php 
                    if( $this->config->item('client_no') == 2 ){
                        
                        if( $this->userinfo['sex'] == 'male' ){
                        ?>
                            <td style="<?=$css?>" id="mpl_balance"></td>
                        <?php
                        }
                        else{
                        ?>
                            <td style="<?=$css?> display:none;" id="mpl_balance"></td>
                        <?php 
                        }

                    }
                    else{ 
                    ?>
                    <td style="<?=$css?>" id="mpl_balance"></td>
                    <?php    
                    }
                }
                else{
                    ?>
                <?php 
                    if( $this->config->item('client_no') == 2 ){
                        
                        if( $employee_info->sex == 'male' ){
                        ?>
                            <td style="<?=$css?>" id="mpl_balance"></td>
                        <?php
                        }
                        else{
                        ?>
                            <td style="<?=$css?> display:none;" id="mpl_balance"></td>
                        <?php 
                        }

                    }
                    else{ 
                    ?>
                    <td style="<?=$css?>" id="mpl_balance"></td>
                    <?php    
                    }
                }
            ?>
<!--             <td style="<?=$css?>" id="bol_balance"></td>
            <td style="<?=$css?>" id="sil_balance"></td>
            <td style="<?=$css?>" id="ul_balance"></td>    -->          
    </tr>
</table>

<!-- 
<div class="col-2-form view">
    <div class="form-item view odd ">
        <h4>Credits</h4>
    </div>
    <div class="form-item view even ">
        <h4>Used</h4>
    </div>

    <div class="form-item view odd ">
    <label for="vl" class="label-desc view gray">VL</label>
    <div class="text-input-wrap" id="vl"><?=(isset($balance)) ? $balance->vl : ''?></div>
    </div>  

    <div class="form-item view even ">
    <label for="vl_used" class="label-desc view gray">VL</label>
    <div class="text-input-wrap" id="vl_used"><?=(isset($balance)) ? $balance->vl_used : ''?></div>
    </div>  

    <div class="form-item view odd ">
    <label for="sl" class="label-desc view gray">SL</label>
    <div class="text-input-wrap" id="sl"><?=(isset($balance)) ? $balance->sl : ''?></div>
    </div>  

    <div class="form-item view even ">
    <label for="sl_used" class="label-desc view gray">SL</label>
    <div class="text-input-wrap" id="sl_used"><?=(isset($balance)) ? $balance->sl_used : ''?></div>     
    </div>  

    <div class="form-item view odd ">
    <label for="el" class="label-desc view gray">EL</label>
    <div class="text-input-wrap" id="el"><?=(isset($balance)) ? $balance->el : ''?></div>
    </div>  

    <div class="form-item view even ">
    <label for="el_used" class="label-desc view gray">EL</label>
    <div class="text-input-wrap" id="el_used"><?=(isset($balance)) ? $balance->el_used : ''?></div>     
    </div>

    <?php if( $this->userinfo['sex'] == 'male' ) :?>
    <div class="form-item view odd ">
    <label for="mpl" class="label-desc view gray">PL</label>
    <div class="text-input-wrap" id="mpl"><?=(isset($balance)) ? $balance->mpl : ''?></div>     
    </div>  
    <?php endif;?>

    <div class="form-item view even ">
    <label for="mpl_used" class="label-desc view gray">PL</label>
    <div class="text-input-wrap" id="mpl_used"><?=(isset($balance)) ? $balance->mpl_used : ''?></div>
    </div>  

    <div class="form-item view odd ">
    <label for="bl" class="label-desc view gray">BL</label>
    <div class="text-input-wrap" id="bl"><?=(isset($balance)) ? $balance->bl : ''?></div>
    </div>  

    <div class="form-item view even ">
    <label for="bl_used" class="label-desc view gray">BL</label>
    <div class="text-input-wrap" id="bl_used"><?=(isset($balance)) ? $balance->bl_used : ''?></div>
    </div>  

    
    <div class="spacer"></div>
    
    <div class="form-item view odd ">
        <h4>Available</h4>
    </div>

    <div class="form-item view odd ">
    <label for="vl" class="label-desc view gray">VL</label>
    <div class="text-input-wrap" id="vl_balance"><?=(isset($balance)) ? $balance->vl - $balance->vl_used : ''?></div>
    </div>  

    <div class="form-item view odd ">
    <label for="vl" class="label-desc view gray">SL</label>
    <div class="text-input-wrap" id="sl_balance"><?=(isset($balance)) ? $balance->sl - $balance->sl_used : ''?></div>
    </div>  

    <div class="form-item view odd ">
    <label for="vl" class="label-desc view gray">EL</label>
    <div class="text-input-wrap" id="el_balance"><?=(isset($balance)) ? $balance->el - $balance->el_used : ''?></div>
    </div>  

    <?php if( $this->userinfo['sex'] == 'male' ) :?>
    <div class="form-item view odd ">
    <label for="vl" class="label-desc view gray">PL</label>
    <div class="text-input-wrap" id="mpl_balance"><?=(isset($balance)) ? $balance->mpl - $balance->mpl_used : ''?></div>
    </div> 
    <?php endif;?> 

    <div class="form-item view odd ">
    <label for="vl" class="label-desc view gray">BL</label>
    <div class="text-input-wrap" id="bl_balance"><?=(isset($balance)) ? $balance->bl - $balance->bl_used : ''?></div>
    </div>  

</div --> 