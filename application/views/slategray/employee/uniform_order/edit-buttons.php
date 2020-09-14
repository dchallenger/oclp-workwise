<div class="icon-label-group">
    <div class="icon-label">
          <a rel="save-draft" class="icon-16-disk" href="javascript:void(0);" onclick="save_request('back', <?php echo $show_wizard_control ? 1 : 0 ?>)">
              <span>Save</span>
          </a>
    </div>

    <div class="icon-label">
        <a rel="back-to-list" class="icon-16-listback" href="javascript:void(0);">
            <span>Back to list</span>
        </a>
    </div>
</div>


<script>

    function save_request( on_success, is_wizard , callback ){
      $('form#record-form').append('<input type="hidden" name="save" value="true" />');
      ajax_save( on_success, is_wizard , callback );
    }


</script>