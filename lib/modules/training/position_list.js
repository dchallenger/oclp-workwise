$(document).ready(function () {
    // Unbind the default method for clicking on a listview row.
    $('.jqgrow').die('dblclick');
    $('.icon-16-info').die('click');
    
    // Define module specific event.
    $('.jqgrow').live('dblclick', function () {
        return false;
    });
    
    $('a.search_skills').live('click', function () {
        window.location = module.get_value('base_url') + 'training/skill_set_competencies/index/' + $(this).parents('tr').attr('id');
    });
    
});

