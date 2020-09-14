<div>
    <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
        <br>
    <div>
        <?php
            $qry = "SELECT vessel_code, vessel, date_embark, embark_reason, embark_remarks, date_disembark, disembark_reason, disembark_remarks
                    FROM {$this->db->dbprefix}employee_vessel_embark_disembark_detail d
                    LEFT JOIN {$this->db->dbprefix}vessel v ON d.vessel_id = v.vessel_id
                    WHERE employee_id = $this->key_field_val
            ";
            $res = $this->db->query($qry);

            foreach($res->result() as $key => $value) {
        ?>
        <div>
            <div class="form-item view odd">
               <label class="label-desc view gray" for="vessel_code">
                    Vessel Code:
                </label>
                <div class="text-input-wrap"><?php echo $value->vessel_code?></div>
            </div>
            
            <div class="form-item view even">
                <label class="label-desc view gray" for="vessel">
                    Vessel Name:
                </label>
                <div class="text-input-wrap"><?php echo $value->vessel?></div>
            </div>

            <div class="form-item even">
                <label>&nbsp;</label>
            </div>
            
            <div class="form-item view odd">
                <label class="label-desc view gray" for="date_embark">
                    Date Embark:
                </label>
                <div class="text-input-wrap"><?php echo $value->date_embark?></div>
            </div>
            
            <div class="form-item view even">
                <label class="label-desc view gray" for="embark_remarks">
                    Embark Remarks
                </label>
                <div class="text-input-wrap"><?php echo $value->embark_remarks?></div>
            </div>
            
            <div class="form-item view odd">
                <label class="label-desc view gray" for="embark_reason">
                    Embark Reason   
                </label>
                <div class="text-input-wrap"><?php echo $value->embark_reason?></div>
            </div>
            
            <div class="form-item even">
                <label>&nbsp;</label>
            </div>

            
            <div class="form-item view odd">
                <label class="label-desc view gray" for="date_disembark">
                    Date Disembark:
                </label>
                <div class="text-input-wrap"><?php echo $value->date_disembark?></div>
            </div>

            <div class="form-item view even">
                <label class="label-desc view gray" for="disembark_remarks">
                    Disembark Remarks
                </label>
                <div class="text-input-wrap"><?php echo $value->disembark_remarks?></div>
            </div>
            
            <div class="form-item view odd">
                <label class="label-desc view gray" for="disembark_reason">
                    Disembark Reason   
                </label>
                <div class="text-input-wrap"><?php echo $value->disembark_reason?></div>
            </div>
            <div class="clear"></div>
                <br>
            <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <br>
            <?php
            }
        ?>
        </div>
    </div>
</div>