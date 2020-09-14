<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>
<?php if (count($references) > 0): ?>
    <table border="0" cellpadding="10" cellspacing="0" style="border:1px #333 solid; font-size:8pt;" width="100%">
        <thead>
            <tr>
                <th align="center" style="border:1px #333 solid">
                    <strong>Name and Occupation</strong></th>
                <th align="center" style="border:1px #333 solid">
                    <strong>Address</strong></th>
                <th align="center" style="border:1px #333 solid">
                    <strong>Telephone Number</strong></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($references as $data): ?>
                <tr>
                    <td style="border:1px #333 solid">
                        <?=$data['name']?><br />
                        <?=$data['occupation']?>
                    </td>
                    <td style="border:1px #333 solid"><?=$data['address']?></td>                    
                    <td style="border:1px #333 solid"><?=$data['telephone']?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>    
    </table>
<?php endif; ?>