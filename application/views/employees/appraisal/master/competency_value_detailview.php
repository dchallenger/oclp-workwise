<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<div class="form-multiple-add-competency-group">
    <table class="default-table boxtype " border="2" style="width:100%;text-align:left;">
        <thead>
            <tr>
                <th>Value</th><th>Descrption</th><!-- <th>Placeholder</th> -->        
            </tr>
        </thead>
        <tbody>
        <?php foreach ($competency_values->result() as $key => $value):?>
            <tr>
                <td style="width:20%;text-align:left;"><?=$value->competency_value?></td>
                <td style="width:40%;text-align:left;"><?=$value->competency_value_description?></td>
                <!-- <td style="width:40%;text-align:left;"><?=$value->results_placeholder?></td> -->
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>
