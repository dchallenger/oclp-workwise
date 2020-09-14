<?php if (isset($show_print) && $show_print): ?>
    <div class="icon-label">
        <a href="javascript:void(0);" onclick="print()" class="icon-16-print">
            <span>Print</span>
        </a>
    </div>
<?php endif; ?>

<?php if ( $this->user_access[$this->module_id]['edit'] ){ ?>
<div class="icon-label">
    <a class="icon-16-edit" href="javascript:void(0);" onclick="edit_detail()">
        <span>Edit</span>
    </a>
</div>
<?php } ?>
