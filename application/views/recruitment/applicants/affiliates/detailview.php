<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php


    if (count($affiliates) > 0):
        foreach ($affiliates as $data):

            ?>

            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="affiliates[company][]">
                        Name of Organization:
                    </label>
                    <div class="text-input-wrap">
                        <?
                            $this->db->where('affiliation_id',$data['affiliation_id']);
                            $this->db->where('deleted',0);
                            $result = $this->db->get('affiliation');
                            if ($result && $result->num_rows() > 0){
                                $row = $result->row();
                                echo $row->affiliation;
                            }
                        ?>
                    </div>                    
                </div>
                <?php
                    $date_from = '';
                    $date_to = '';
                    if ($data['date_from'] != '0000-00-00' && $data['date_from'] != '' && $data['date_from'] != NULL && $data['date_from'] != '1970-01-01'){
                        // $date_from = date('Y', strtotime($data['date_from']));
                        $date_from = $data['date_from'];
                    }
                    if ($data['date_to'] != '0000-00-00' && $data['date_to'] != '' && $data['date_to'] != NULL && $data['date_to'] != '1970-01-01'){
                        // $date_to = date('Y', strtotime($data['date_to']));
                        $date_to = $data['date_to'];
                    }                                
                ?>   
                <div class="form-item view even">
                        <label class="label-desc view gray" for="affiliates[position][]">
                            Position:
                        </label>
                        <div class="text-input-wrap">
                        <?= $data['position']?>
                        </div>
                    </div>               
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="affiliates[date_from][]">
                        Date From:
                    </label>
                    <div class="text-input-wrap"><?= $date_from ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="affiliates[date_to][]">
                        Date To:
                    </label>
                    <div class="text-input-wrap"><?= $date_to ?></div>
                </div>                
<!--                 <div class="form-item view even">
                    <label class="label-desc view gray" for="affiliates[active][]">
                        Status:
                    </label>
                    <div class="text-input-wrap"><?php if( isset($data['active']) && $data['active'] == 1 ){ ?>Yes<?php }else{ ?>No<?php } ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="affiliates[address][]">
                        Position:
                    </label>
                    <div class="text-input-wrap"><?= $data['position'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="affiliates[date_joined][]">
                        Date Joined:
                    </label>
                    <div class="text-input-wrap">
                        <?php if($data['date_joined'] != "0000-00-00"){  ?>
                        <?= date('F Y', strtotime($data['date_joined'])); ?>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="affiliates[date_resigned][]">
                        Date Resigned:
                    </label>
                    <div class="text-input-wrap">
                        <?php if($data['date_resigned'] != "0000-00-00"){  ?>
                        <?= date('F Y', strtotime($data['date_resigned'])); ?>
                        <?php } ?>
                    </div>
                </div> -->
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
