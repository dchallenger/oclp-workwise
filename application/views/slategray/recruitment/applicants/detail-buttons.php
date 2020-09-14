<?php if (isset($show_print) && $show_print): ?>
    <div class="icon-label">
        <a href="javascript:void(0);" onclick="print()" class="icon-16-print">
            <span>Print</span>
        </a>
    </div>
<?php endif; ?>

<div class="icon-label">
    <a class="icon-16-edit" href="javascript:void(0);" onclick="edit_detail_candidates()">
        <span>Edit</span>
    </a>
</div>

<div class="icon-label"> 
	<a href="javascript:void(0);" class="icon-16-listback back-to-candidates" rel="back-to-candidates"> <span>Back to list</span> </a> 
</div>