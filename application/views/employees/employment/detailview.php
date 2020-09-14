<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div>
    <?php
    if (count($employment) > 0):
        foreach ($employment as $data):
                $date_from = ($data['from_date'] == "0000-00-00" || $data['from_date'] == "1970-01-01" ? "" : date('F Y', strtotime($data['from_date'])));
                $date_to = ($data['to_date'] == "0000-00-00" || $data['to_date'] == "1970-01-01" ? "" : date('F Y', strtotime($data['to_date'])));                    
                if ($date_from == "" || $date_to == ""):
                    $date_from_to = "";
                else:
                    $date_from_to = $date_from . " to " . $date_to;
                endif            
            ?>

            <div>
                <div style="height: 10px; border-top: 4px solid #CCCCCC;"></div>
                <br />
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="employment[company][]">
                        Company:<span class="red font-large">*</span>
                    </label>
                    <div class="text-input-wrap"><?= $data['company'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[address][]">
                        Address:
                    </label>
                    <div class="text-input-wrap"><?= $data['address'] ?></div>
                </div>
                <?php if ($this->config->item('additional_forms_exists') == 1): ?>
                    <div class="form-item view odd">
                        <label class="label-desc view gray" for="employment[contact_no][]">
                            Contact No.:
                        </label>
                        <div class="text-input-wrap"><?= $data['contact_no'] ?></div>
                    </div>
                <?php endif; ?>                
                <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[nature_of_business][]">
                        Nature of Business:
                    </label>
                    <div class="text-input-wrap"><?= $data['nature_of_business'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="employment[position][]">
                        Position:
                    </label>
                    <div class="text-input-wrap"><?= $data['position'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[from_date][]">
                        Inclusive Dates:
                    </label>
                    <div class="text-input-wrap"><?= $date_from_to ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="employment[supervisor_name][]">
                        Immediate Superior’s Name:
                    </label>
                    <div class="text-input-wrap"><?= $data['supervisor_name'] ?></div>
                </div>
                <div class="form-item view even">
                    <label class="label-desc view gray" for="employment[reason_for_leaving][]">
                        Reason for Leaving:
                    </label>
                    <div class="text-input-wrap"><?= $data['reason_for_leaving'] ?></div>
                </div>
                <div class="form-item view odd">
                    <label class="label-desc view gray" for="employment[duties][]">
                        Duties:
                    </label>
                    <div class="text-input-wrap"><?= nl2br($data['duties']) ?></div>
                </div>
                <?php if ($this->config->item('additional_forms_exists') == 1): ?>
                    <div class="form-item view even">
                        <label class="label-desc view gray" for="employment[last_salary][]">
                            Last Salary:
                        </label>
                        <div class="text-input-wrap"><?= $data['last_salary'] ?></div>
                    </div>
                    <!-- div class="form-item view even">
                        <label class="label-desc view gray" for="employment[fb_equivalent_position_id][]">
                            Equivalent Position (FB):
                        </label>
                        <div class="text-input-wrap">
                            < ?
                                $this->db->where('position_id',$data['fb_equivalent_position_id']);
                                $this->db->where('deleted',0);
                                $result = $this->db->get('user_position');
                                if ($result && $result->num_rows() > 0){
                                    $row = $result->row();
                                    echo $row->position;
                                }
                            ?>
                        </div>
                    </div -->
                <?php endif; ?>                                
            </div>
            <div class="clear"></div>
            <br />
        <?php endforeach; ?>
    <?php endif; ?>
</div>
