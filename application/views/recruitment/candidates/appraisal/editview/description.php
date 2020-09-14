<em>Observe the applicant and write three (3) adjectives or phrases which you think describes the applicant.</em>
<table border="0" cellpadding="20" cellspacing="0" style="border:1px #333 solid; font-size:8pt;" width="100%">
    <tbody>
        <tr>
            <th style="border:1px #333 solid">1st Interviewer</th>
            <th style="border:1px #333 solid">2nd Interviewer</th>
            <th style="border:1px #333 solid">3rd Interviewer</th>
        </tr>
        <tr>
            <td style="border:1px #333 solid">
                1. <input type="text" name="description_1[]" value="<?php echo $description_1[0];?>" />
            </td>
            <td style="border:1px #333 solid">
                1. <input type="text" name="description_2[]" value="<?php echo $description_2[0];?>" <?=($this->userinfo['user_id'] != $requested_by) ? 'readonly' : '' ?>/>
            </td>
            <td style="border:1px #333 solid"> 
                1. <input type="text" name="description_3[]" value="<?php echo $description_3[0];?>" <?=($this->userinfo['user_id'] != $approved_by) ? 'readonly' : '' ?>/>
            </td>
        </tr>
        <tr>
            <td style="border:1px #333 solid">
                2. <input type="text" name="description_1[]" value="<?php echo $description_1[1];?>"/>
            </td>
            <td style="border:1px #333 solid">
                2. <input type="text" name="description_2[]" value="<?php echo $description_2[1];?>" <?=($this->userinfo['user_id'] != $requested_by) ? 'readonly' : '' ?>/>
            </td>
            <td style="border:1px #333 solid">
                2. <input type="text" name="description_3[]" value="<?php echo $description_3[1];?>" <?=($this->userinfo['user_id'] != $approved_by) ? 'readonly' : '' ?>/>
            </td>   
        </tr>
        <tr>
            <td style="border:1px #333 solid">
                3. <input type="text" name="description_1[]" value="<?php echo $description_1[2];?>"/>
            </td>
            <td style="border:1px #333 solid">
                3. <input type="text" name="description_2[]" value="<?php echo $description_2[2];?>" <?=($this->userinfo['user_id'] != $requested_by) ? 'readonly' : '' ?>/>
            </td>
            <td style="border:1px #333 solid">
                3. <input type="text" name="description_3[]" value="<?php echo $description_3[2];?>" <?=($this->userinfo['user_id'] != $approved_by) ? 'readonly' : '' ?>/>
            </td>         
        </tr>
    </tbody>
</table>
<p>&nbsp;</p>