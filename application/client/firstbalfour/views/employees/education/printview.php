<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed'); ?>


<?php if (count($education) > 0): ?>
    <table border="0" cellpadding="10" cellspacing="0" style="border: 1px solid rgb(51, 51, 51); font-size: 8pt;" width="100%">
        <thead>
            <tr>
                <th align="center" style="border: 1px solid rgb(51, 51, 51);">
                    <strong>Type of School</strong></th>
                <th align="center" style="border: 1px solid rgb(51, 51, 51);">
                    <strong>Name<br />
                        of School</strong></th>
                <th align="center" style="border: 1px solid rgb(51, 51, 51);">
                    <strong>Years Attended<br />
                        (From To)</strong></th>
                <th align="center" style="border: 1px solid rgb(51, 51, 51);">
                    <strong>Degree Obtained</strong></th>
                <th align="center" style="border: 1px solid rgb(51, 51, 51);">
                    <strong>Honors Received</strong></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($education as $data):
                $date_from = ($data['date_from'] == "0000-00-00" || $data['date_from'] == "1970-01-01" ? "" : date('F Y', strtotime($data['date_from'])));
                $date_to = ($data['date_to'] == "0000-00-00" || $data['date_from'] == "1970-01-01" ? "" : date('F Y', strtotime($data['date_to'])));                    
                if ($date_from == "" || $date_to == ""):
                    $date_from_to = "";
                else:
                    $date_from_to = $date_from . " to " . $date_to;
                endif
                ?>            
                <tr>
                    <td style="border: 1px solid rgb(51, 51, 51);"><?= $data['education_level'] ?></td>
                    <?php if ($this->config->item('tbx_dropdown') != 1): ?>
                        <td style="border: 1px solid rgb(51, 51, 51);"><?= $data['school'] ?></td>
                    <?php else: ?>
                        <td style="border: 1px solid rgb(51, 51, 51);">
                            <?
                                $qs = "'".implode("','",explode(",",$data['education_school_id']))."'";                        
                                $str_array = array();
                                $result = $this->db->query("SELECT education_school FROM {$this->db->dbprefix}education_school WHERE education_school_id IN (".$qs.") AND DELETED = 0");
                                
                                if ($result && $result->num_rows() > 0){
                                    foreach ($result->result() as $row) {
                                        $str_array[] = $row->education_school;
                                    }
                                    $str = implode(',', $str_array);                            
                                    echo $str;
                                }
                            ?>  
                        </td>
                    <?php endif; ?> 
                    <td style="border: 1px solid rgb(51, 51, 51);"><?= $date_from_to ?></td>
                    <td style="border: 1px solid rgb(51, 51, 51);"><?= $data['degree'] ?></td>
                    <td style="border: 1px solid rgb(51, 51, 51);"><?= $data['honors_received'] ?></td>
                </tr>
            <?php endforeach; ?>                            
        </tbody>  
    </table>
<?php endif; ?>