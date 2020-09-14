<script type="text/javascript">
        $(document).ready(function() {
               $('#tabs-portlet-4').tabs();
        });
</script>
<div id="tabs-portlet-4">
        <ul>
                <li><a href="#tabs-1">Today</a></li>
                <li><a href="#tabs-2">Tomorrow</a></li>
                <li><a href="#tabs-3">Later This Week</a></li>
        </ul>
        <?php 
        $tab_ctr = 1;

        foreach ($celebrants as $celebrant_date):
        ?>
        <div id="tabs-<?=$tab_ctr++?>">
                <?php if (count($celebrant_date) > 0): $ctr = 1;?>
                        <ul>
                        <?php foreach ($celebrant_date as $celebrant):?>
                                <li>
                                        <strong>
                                                <?=$celebrant->firstname?>&nbsp;<?=$celebrant->lastname?>
                                        </strong>
                                        
                                        
                                        <div class="align-right">
                                                <span class="icon24 icon-balloon-24" onclick="comments_box('group', '<?=$celebrant->comment_group?>')">
                                                </span>
                                                <span class="align-right ctr-small bg-red">
                                                        <small><?=$celebrant->comment_count?></small>
                                                </span>
                                        </div>
                                        

                                        <br />
                                        <span><small><?=$celebrant->position?></small></span>
                                        <br />
                                        <span>
                                                <small>
                                                        <?=get_age($celebrant->birth_date);?> years old
                                                </small>
                                        </span>

                                        
                                </li>                         
                        <?php endforeach;?>
                        </ul>
                <?php ;else:?>
                        <!-- #END today -->
                        <p><small>None as of this moment.</small></p>
                <?php endif;?>
        </div>  
        <?php endforeach;?>
</div>
<div class="spacer"></div>