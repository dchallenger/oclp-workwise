<?php if (count($family) > 0):?>
<table border="0" cellpadding="10" cellspacing="0" style="border:1px #333 solid; font-size:8pt;" width="100%">
    <thead>
        <tr>
            <th align="center" style="border:1px #333 solid">
                <strong>Name</strong></th>
            <th align="center" style="border:1px #333 solid">
                <strong>Relationship</strong></th>
            <th align="center" style="border:1px #333 solid">
                <strong>Age</strong></th>
            <th align="center" style="border:1px #333 solid">
                <strong>Occupation</strong></th>
            <th align="center" style="border:1px #333 solid">
                <strong>Employer</strong></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($family as $data):?>
        <tr>
            <td style="border:1px #333 solid"><?=$data['name']?></td>
            <td style="border:1px #333 solid"><?=$data['relationship']?></td>
            <td style="border:1px #333 solid"><?=($data['birth_date'] == "0000-00-00" || $data['birth_date'] == "1970-01-01" ? "" : date('m/d/Y', strtotime($data['birth_date'])))?></td>
            <td style="border:1px #333 solid"><?=$data['occupation']?></td>
            <td style="border:1px #333 solid"><?=$data['employer']?></td>
        </tr>
        <?php endforeach; ?>      
    </tbody>
</table>
<?php endif; ?>  