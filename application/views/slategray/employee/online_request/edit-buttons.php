<div class="icon-label-group">
    <div class="icon-label">
          <a rel="save-draft" class="icon-16-disk" href="javascript:void(0);" onclick="draft_request('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>Save as Draft</span>
          </a>
    </div>
    <div class="icon-label">
          <a rel="save-draft" class="icon-16-disk" href="javascript:void(0);" onclick="submit_request('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>Send Request</span>
          </a>
    </div>

    <div class="icon-label">
        <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
            <span>Back to list</span>
        </a>
    </div>
</div>


<script>

    function draft_request( on_success, is_wizard , callback ){
      $('form#record-form').append('<input type="hidden" name="draft" value="true" />');
      ajax_save( on_success, is_wizard , callback );
    }

    function submit_request( on_success, is_wizard , callback ){
      $('form#record-form').append('<input type="hidden" name="submit_request" value="true" />');
      ajax_save( on_success, is_wizard , callback );
   }

   function approve_request( on_success, is_wizard , callback ){
      $('form#record-form').append('<input type="hidden" name="approve_request" value="true" />');
      ajax_save( on_success, is_wizard , callback );
   }

   function decline_request( on_success, is_wizard , callback ){
      $('form#record-form').append('<input type="hidden" name="decline_request" value="true" />');
      ajax_save( on_success, is_wizard , callback );
   }

</script>