<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>

<?php if (count($employment) > 0): ?>
    <table border="0" cellpadding="10" cellspacing="0" style="border:1px #333 solid; font-size:8pt;" width="100%">
        <thead>
            <tr>
                <th align="center" style="border:1px #333 solid">
                    <strong>Company Name &amp; Address:</strong></th>
                <th align="center" style="border:1px #333 solid">
                    <strong>Employment<br />
                        From - To</strong></th>
                <th align="center" style="border:1px #333 solid">
                    <strong>Position</strong></th>
                <th align="center" style="border:1px #333 solid">
                    <strong>Last Salary</strong></th>
                <th align="center" style="border:1px #333 solid">
                    <strong>Name/Position of Direct Superior</strong></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employment as $data): ?>
                <tr>
                    <td style="border:1px #333 solid"><?= $data['company'] . '<br />' . $data['address'] ?></td>
                    <td style="border:1px #333 solid"><?= ($data['from_date'] == "0000-00-00" || $data['from_date'] == "1970-01-01" ? "" : date('F Y', strtotime($data['from_date']))) . '-' . ($data['to_date'] == "0000-00-00" || $data['to_date'] == "1970-01-01" ? "" : date('F Y', strtotime($data['to_date']))) ?></td>
                    <td style="border:1px #333 solid"><?= $data['position'] ?></td>
                    <td style="border:1px #333 solid"><?= $data['last_salary'] ?></td>
                    <td style="border:1px #333 solid"><?= $data['supervisor_name'] ?></td>
                </tr>
            <?php endforeach; ?>      
        </tbody>
    </table>
<?php endif; ?>