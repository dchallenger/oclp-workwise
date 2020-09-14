<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<div class="form-multiple-add-competency-group">
    <table class="default-table boxtype " border="2" style="width:100%;text-align:left;">
        <thead>
            <tr>
                <th>Levels</th><th>Descrption</th>    
            </tr>
        </thead>
        <tbody>
        <?php foreach ($competency_levels as $key => $level):?>
            <tr>
                <td style="width:30%;text-align:left;"><?=$level->appraisal_competency_level?></td>
                <td style="width:70%;text-align:left;"><?=$level->description?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</div>

