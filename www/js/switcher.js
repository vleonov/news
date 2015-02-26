$( function() {
    "use strict";

    var $leftBar = $('#left-bar'),
        $leftBarSwitcher = $('#left-bar-switch');

    $leftBarSwitcher.on('click', switchToggle)
        .on('mouseover', switchOn);

    $leftBar.on('mouseout', switchOff);

    function switchToggle()
    {
        $leftBar.toggleClass('expanded');
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