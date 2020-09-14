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
                        Name of Affiliation:
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
                <div class="form-item view even">
                    <label class="label-desc view gray" for="affiliates[address][]">
                        Position:
                    </label>
                    <div class="text-input-wrap"><?= $data['position'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="affiliates[date_joined][]">
                        Year Joined:
                    </label>
                    <div class="text-input-wrap">
                        <?php if($data['date_joined'] != "0000-00-00" && $data['date_joined'] != NULL){  ?>
                        <?= date('Y', strtotime($data['date_joined'])); ?>
                        <?php } ?>
                    </div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="affiliates[active][]">
                        Status:
                    </label>
                    <div class="text-input-wrap"><?php if( isset($data['active']) && $data['active'] == 1 ){ ?>Active<?php }else{ ?>Resigned<?php } ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="affiliates[date_resigned][]">
                        Year Resigned:
                    </label>
                    <div class="text-input-wrap">
                        <?php if($data['date_resigned'] != "0000-00-00" && $data['date_resigned'] != NULL){  ?>
                        <?= date('Y', strtotime($data['date_resigned'])); ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
            <div style="height: 40px;"></div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
