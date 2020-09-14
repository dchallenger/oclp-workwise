<script>
$(document).ready(function() {
    var availableTags = [
    <?php 
    $this->db->group_by('medication');
    $clinic_rec = $this->db->get('employee_clinic_records');
    foreach($clinic_rec->result() as $medication): 
    ?>
        "<?php echo $medication->medication ?>",
    <?php
    endforeach;
    ?>
    "Kremil-S"
    ];
    
    $( "#medication" ).autocomplete({
        source: availableTags
    });
});
</script>