<?php $record_id = $this->input->post('record_id'); ?>

<div class="form-submit-btn">
            <div class="icon-label-group">
            	<?php if( $status == 1 ): ?>
	                <div class="icon-label">
	                    <a class="icon-16-edit" href="javascript:void(0);" onclick="edit()">
	                        <span>Edit</span>
	                    </a>            
	                </div>
            	<?php endif; ?>

                <div class="icon-label-group">
				    <div class="icon-label"> <a href="javascript:void(0);" class="icon-16-listback" rel="back-to-list"> <span>Back to list</span> </a> </div>
				</div>
            </div>
        </div>

