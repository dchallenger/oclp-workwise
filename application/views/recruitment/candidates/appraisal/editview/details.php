                  
<table border="0" cellpadding="20" cellspacing="0" style="border:1px #333 solid; font-size:8pt;" width="100%">
    <tbody>
        <tr>
            <td rowspan="2" style="border:1px #333 solid">
                <strong>Name (Last, First, Middle)</strong><br />
                <?php echo $lastname . ', ' . $firstname . ' ' . $middlename; ?>
            </td>
            <td style="border:1px #333 solid">
                <strong>Position Applying For:</strong><br />
                <?php echo $position; ?>
            </td>
            <td rowspan="2" style="border:1px #333 solid">
                <strong>Date:</strong><br />
                <?php echo $appraisal_date; ?>
            </td>
        </tr>
        <tr>
            <td style="border:1px #333 solid">
                <strong>Other position for consideration:</strong><br />
                {position}
            </td>
        </tr>
    </tbody>
</table>      