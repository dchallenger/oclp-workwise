$(document).ready(function(){
    /**
     * Add new item to database and populate the table.
     *
     */
    $('a.dynamic-trig').live('click', function (event) {
        var fields = $('input[rel="dynamic"], input[name^="dynamic"], input[name^="edit"]').serialize();
        fields += '&fg_id=' + $(this).parents('div.wizard-type-form').attr('fg_id');
        var url = $(this).attr('href');
        var obj = $(this);

        $.ajax({
            type: 'POST',
            url: url,
            data: fields,
            dataType: 'json',
            success: function (response) {
                var group = '';
                if (response.group != '') {
                    group = '[' + response.group + ']';
                }

                $('#message-container').html(message_growl(response.type, response.message));

                if (response.type == 'error') {
                    // Set the focus to the error field.
                    $('input[name="dynamic' + group + '[' + response.focus + ']"]').focus();
                } else if (response.type == 'success') {
                    obj.parent('div').next('div.dynamic-container').prepend(response.html);
                }

                // Remove the edit flag and fields if they were previously set.
                $('input[name="edit-flag"]').remove();
                $('input[name^="edit' + group + '"]').remove();
            }
        });

        event.preventDefault();
    });

    /**
     * Delete row.
     *
     */
    $('a.delete-row').live('click', function (event) {
        event.preventDefault();

        var ans = confirm('Delete this entry?');

        obj = $(this);

        if (ans) {
            obj.closest('.multiple-data-row').fadeOut('slow', function () {
                obj.closest('.multiple-data-row').remove();
            });
        }

    });
});