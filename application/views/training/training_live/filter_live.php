

<script type="text/javascript">
$(document).ready(function() {

 if (module.get_value('view') == 'filter') {
    $.blockUI({
        message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
    });
  setTimeout(function() {
        $.unblockUI();
        filter_grid( 'jqgridcontainer', <?=$to_filter?> );
    },1000);
        };
    });
</script>