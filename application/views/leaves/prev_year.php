<script>
	var obj=$('#fg-345');
 	   obj.find('.col-1-form,.col-2-form').addClass( "hidden" );
 	   obj.find('h3').find('a').text('Show');
</script>
<?php
    if($this->config->item('remove_el_leave_balance_viewing') == 1)
        $visibility = ";display:none";

	$last_year=date("Y",strtotime("-1 year"));
	$prev_balance=$this->db->get_where('employee_leave_balance', array('year'=>$last_year, 'employee_id'=>$this->userinfo['user_id']))->row();
    $balance=$this->db->get_where('employee_leave_balance', array('year'=>date('Y'), 'employee_id'=>$this->userinfo['user_id']))->row();
?>
<table width="70%" cellspacing="10" cellpadding="10">
    <thead height="25px" align="center">
            <td width="20%">&nbsp;</td>
            <td style="<?=$css?>"width="15%">VL</td>
            <td style="<?=$css?>"width="15%">SL</td>
            <td style="<?=$css.$visibility?>"width="15%" id="el_prev_balance_header">EL</td>
            <?php if( $this->userinfo['sex'] == 'male' ) :?>
                <td style="<?=$css.$visibility?>"width="15%" id="mpl_prev_balance_header">PL</td>
            <?php endif;?>
            <td style="<?=$css.$visibility?>"width="15%" id="bl_prev_balance_header">BL</td>
    </thead>
    <tr height="20px" align="center">
            <td style="<?=$css?>">Previous Leave Balance</td>
            <td style="<?=$css?>" id="vl_prev_balance">0</td>
            <td style="<?=$css?>" id="sl_prev_balance">0</td>
            <td style="<?=$css.$visibility?>" id="el_prev_balance">0</td>
            <?php if( $this->userinfo['sex'] == 'male' ) :?>
                <td style="<?=$css.$visibility?>" id="mpl_prev_balance">0</td>
            <?php endif;?>
            <td style="<?=$css.$visibility?>" id="bl_prev_balance">0</td>
    </tr>
</table>

<?php
function get_previous_credits_vl_bal($balance = false, $prev_balance = false)
{
    if($balance && $prev_balance)
    {
        $curr_vl = get_current_credits_vl($balance, $prev_balance);
        if($balance->vl_used > $curr_vl)
        {
            $deduct_previous = $balance->vl_used - $curr_vl;
            $prev_bal_left = ($prev_balance->vl - $prev_balance->vl_used) - $deduct_previous;
            if($prev_bal_left > 0)
                return $prev_bal_left;
            else 
                return 0;
        } else
            return $prev_balance->vl - $prev_balance->vl_used;
    } else
        return 0;
}

function get_previous_credits_sl_bal($balance = false, $prev_balance = false)
{
    if($balance && $prev_balance)
    {
        $curr_sl = get_current_credits_sl($balance, $prev_balance);
        if($balance->sl_used > $curr_sl)
        {
            $deduct_previous = $balance->sl_used - $curr_sl;
            $prev_bal_left = (($prev_balance->sl - $prev_balance->sl_used) - check_and_get_cashout(false, '2012')) - $deduct_previous;
            if($prev_bal_left > 0)
                return $prev_bal_left;
            else 
                return 0;
        } else {
            $prev_bal_left = $prev_balance->sl - $prev_balance->sl_used;
            $prev_bal_left = $prev_bal_left - check_and_get_cashout(false, '2012');
            return $prev_bal_left;
        }
    } else
        return 0;
}
?>