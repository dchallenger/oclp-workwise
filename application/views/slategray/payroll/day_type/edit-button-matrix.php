<div class="form-submit-btn">
    <?php if($this->user_access[$this->module_id]['edit'] == 1): ?>
        <div class="icon-label-group">
            <div class="icon-label">
                <a href="javascript: edit_matrix()" class="icon-16-edit">
                    <span>Edit</span>
                </a>            
            </div>
        </div>
    <?php endif;?>
</div>