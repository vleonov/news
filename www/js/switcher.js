$( function() {
    "use strict";

    var $leftBar = $('#left-bar'),
        $leftBarSwitcher = $('#left-bar-switch');

    $leftBarSwitcher.on('click touchend', switchToggle)
        .on('mouseover', switchOn);

    $leftBar.on('mouseout', switchOff);

    $(document).on('click touchend', switchOff);

    function switchToggle(e)
    {
        $leftBar.toggleClass('expanded');
        e.stopPropagation();
    }

    function switchOn()
    {
        $leftBar.addClass('expanded');
    }

    function switchOff()
    {
        $leftBar.removeClass('expanded');
    }
});