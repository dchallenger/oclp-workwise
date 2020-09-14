<div class="clear"></div>
<div class="clear"></div>
<h3>&nbsp;</h3>
<div class="objective_type">
    <table border="2" class="default-table boxtype " style="width:100%;text-align:left">
        <thead>
            <tr>
                <th>Training Investment</th>
                <th>Service Bond</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($service_bond as $key => $bond):?>
            <tr>
                <td><?=$bond->training_investment?></td>
                <td><?=$bond->training_service_bond?></td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
    <div class="clear"></div>

</div>