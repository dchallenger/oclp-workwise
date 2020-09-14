<?php if (count($test_profile) > 0):?>
<table border="0" cellpadding="10" cellspacing="0" style="border:1px #333 solid; font-size:8pt;" width="100%">
    <thead>
        <tr>
            <th align="center" style="border:1px #333 solid">
                <strong>Test Taken</strong></th>
            <th align="center" style="border:1px #333 solid">
                <strong>Date Taken</strong></th>
            <th align="center" style="border:1px #333 solid">
                <strong>Exam Type</strong></th>
            <th align="center" style="border:1px #333 solid">
                <strong>Description</strong></th>
            <th align="center" style="border:1px #333 solid">
                <strong>Rate</strong></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($family as $data):?>
        <tr>
            <td style="border:1px #333 solid"><?=$data['test_taken']?></td>
            <td style="border:1px #333 solid"><?=($data['date_taken'] == "0000-00-00" || $data['date_taken'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['date_taken'])))?></td>
            <td style="border:1px #333 solid"><?=$data['exam_type']?></td>
            <td style="border:1px #333 solid"><?=$data['description']?></td>
            <td style="border:1px #333 solid"><?=$data['rate']?></td>
        </tr>
        <?php endforeach; ?>      
    </tbody>
</table>
<?php endif; ?>  