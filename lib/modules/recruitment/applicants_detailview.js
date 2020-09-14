$(document).ready(function () {
    init();
});

function init() {
    // Compute window scroll and height, used to hide non-visible fieldgroups.
    var docViewTop = $(window).scrollTop();
    var docViewBottom = docViewTop + $(window).height();

    // Hide all fieldgroups that are below the current position of the scrollbar.
    $('h3.form-head>a').parent().next().each(function (index, elem) {
        elemTop = $(elem).offset().top;
        elemBottom = elemTop + $(elem).height();

        if(!((elemBottom >= docViewTop) && (elemTop <= docViewBottom)
            && (elemBottom <= docViewBottom) &&  (elemTop >= docViewTop))) {
            obj = $(elem).prev('h3.form-head').children('a');
            toggleFieldGroupVisibility(obj);
        }
    });    
}