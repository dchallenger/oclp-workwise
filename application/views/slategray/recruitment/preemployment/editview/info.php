<style type="text/css">
.form-head { }
.form-item { padding:5px; width:45% !important; float:left; margin:0px 0px 0px 20px;  }
.form-item label.check-radio-label { float:left; }
.form-item input { float:left; display:inline-block; width:auto; height:100%; }
.form-item span { float:left; display:inline-block; width:90%; padding:0px 0px 0px 10px; }
.form-item span.icon-group { width:auto; }
.form-item .red { float:none !important; width:auto; }
.col-2-form .label-desc { font-weight:bold; }
.col-2-form .form-item.view { width:100% !important; padding:0; }
.col-2-form .form-item.view label { width:20%; margin-right:10px; } 
input#date-temp-from, input#date-temp-to  { float:none !important; }
</style>


<div class="form-item view odd">
    <label class="label-desc view gray">Applicant Name:</label>
    <div class="text-input-wrap"><?php echo $raw_data['applicant_name'];?></div>
</div>
<div class="form-item view odd">
    <label class="label-desc view gray">Subsidiary Name / Department:</label>
    <div class="text-input-wrap"><?php echo $raw_data['company'] . ' - ' . $raw_data['department'];?></div>
</div>