// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * JavaScript library for the good_habits plugin.
 *
 * @package    mod
 * @subpackage goodhabits
 * @copyright  1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

jQuery(window).on('load',function($) {

    var $ = jQuery;

    $('.streak, .habits .habit-name').on('click', function () {
        const $mainContainer = $('#goodhabits-container');
        if ($mainContainer.hasClass('grid-is-open') || $mainContainer.hasClass('streak-open')) {
            return;
        }

        const $streak = $(this).closest('.habit').find('.streak');
        const $habit = $streak.closest('.habit');

        // Collapse any previously expanded streaks
        $('.streak.expanded').removeClass('expanded').siblings().show();
        $('.habit.streak-open').removeClass('streak-open');

        // Expand this one
        $streak.addClass('expanded');
        $mainContainer.addClass('streak-open');
        $habit.addClass('streak-open');
        $habit.children().not($streak).hide();
    });

    $(document).on('click', '.streak-close-option', function () {
        const $streak = $(this).closest('.streak');
        const $habit = $streak.closest('.habit');
        const $mainContainer = $('#goodhabits-container');

        $streak.removeClass('expanded');
        $mainContainer.removeClass('streak-open');
        $habit.removeClass('streak-open');

        $habit.children().not($streak).show();
    });


    // $('.streak-close-option').on('click', function () {
    //
    // });

    function timeUnitOptions() {

        // Event handler for clicks on elements with class 'time-unit'
        $('.time-unit').click(function() {

            if ($('.time-unit-options').length > 0) {
                return; // Do nothing if any time-unit is currently open.
            }

            if ($(this).hasClass('is-in-break')) {
                return; // Do nothing if part of a break.
            }

            if ($(this).hasClass('example')) {
                return; // Do nothing if it is just an example.
            }

            $(this).addClass('time-unit-options');

            $(this).children('.tuo-options').show();

            const allTimeUnits = $(this).parent().children('.time-unit'); // Get all sibling elements with class 'time-unit'
            const index = allTimeUnits.index(this); // Get the index of the clicked element among its '.time-unit' siblings
            const totalUnits = allTimeUnits.length; // Get total number of '.time-unit' siblings

            // If clicked element is the furthest to the left (index 0), hide visibility of next 3 siblings
            if (index === 0) {
                allTimeUnits.eq(index + 1).hide();
                allTimeUnits.eq(index + 2).hide();
                allTimeUnits.eq(index + 3).hide();
            }
            // If clicked element is the furthest to the right, hide visibility of previous 3 siblings
            else if (index === totalUnits - 1) {
                allTimeUnits.eq(index - 1).hide();
                allTimeUnits.eq(index - 2).hide();
                allTimeUnits.eq(index - 3).hide();
            }
            // If clicked element is second-to-last, hide 1 to the right, 2 to the left
            else if (index === totalUnits - 2) {
                allTimeUnits.eq(index + 1).hide(); // Hide 1 to the right
                allTimeUnits.eq(index - 1).hide(); // Hide 2 to the left
                allTimeUnits.eq(index - 2).hide();
            }
            // If clicked element is third-to-last, hide 2 to the right, 1 to the left
            else if (index === totalUnits - 3) {
                allTimeUnits.eq(index + 1).hide(); // Hide 2 to the right
                allTimeUnits.eq(index + 2).hide();
                allTimeUnits.eq(index - 1).hide(); // Hide 1 to the left
            }
            // Otherwise, for middle elements, hide visibility of a combination of preceding and subsequent siblings
            else {
                const precedingCount = Math.min(index, 3); // How many previous elements to hide
                const subsequentCount = 3 - precedingCount; // Remaining number of subsequent elements to hide

                // Hide visibility of previous '.time-unit' siblings (up to 3)
                for (let i = 1; i <= precedingCount; i++) {
                    allTimeUnits.eq(index - i).hide();
                }

                // Hide visibility of subsequent '.time-unit' siblings (remaining)
                for (let i = 1; i <= subsequentCount; i++) {
                    allTimeUnits.eq(index + i).hide();
                }
            }

            // Create the large [X] option
            const closeOption = $('<div class="tuo-close-option close-btn">X</div>');

            $(this).append(closeOption);

            closeOption.css({
                position: 'absolute',
                top: '6px',
                right: '12px',
                opacity: '75%'
            });

            const className = $(this).attr('class'); // Get the class name
            const numberPart = className.match(/time-unit-(\d+)/); // Extract the number part using regex

            if (numberPart && numberPart[1]) {
                let timestamp = numberPart[1];

                $('.checkmark').filter(function() {
                    return $(this).data('timestamp') != timestamp;
                }).addClass('non-selected-time-unit');
            }

            $('.time-unit').not(this).addClass('unselected');
        });

        $(document).on('click', '.tuo-close-option', function() {
            // Show all .time-unit elements
            let $time = $('.time-unit');

            $time.show();

            $time.removeClass('time-unit-options');

            $time.removeClass('unselected');

            // Remove the .non-selected-time-unit class from all .checkmark elements
            $('.checkmark').removeClass('non-selected-time-unit');

            // Remove the [X] option itself
            $('.tuo-close-option').remove();

            $('.tuo-options').hide();
        });

    }

    timeUnitOptions();


    function tips_showhide() {

        $('.dyk-close-btn').click(function() {
            $('.dyk-container').hide();
        });

        $(".tip-card").click(function() {
            var content = $(this).children(".tip-content");

            // Collapse all other tip contents
            $(".tip-content").not(content).slideUp().css("padding", "0 15px");

            // Toggle the clicked content
            if (content.is(":visible")) {
                content.slideUp().css("padding", "0 15px");
                $(this).addClass('closed');
                $(this).removeClass('open');
            } else {
                content.stop(true, true).slideDown().css("padding", "10px 15px");
                $(this).addClass('open');
                $(this).removeClass('closed');
            }
        });

    }

    tips_showhide();

    
    function initGrid(x, y) {

        var wwwroot = getHiddenData('wwwroot');

        $.when(
            $.getScript( wwwroot + "/mod/goodhabits/talentgrid/talentgrid-plugin.js" ),
            $.Deferred(function( deferred ){
                $( deferred.resolve );
            })
        ).done(function(){

            var options = {
                allowTokenDragging: false,
                imageSrc: './talentgrid/smiley.png'
            };

            var arrowIndicator = { // Options for the text accompanying the external token.
                enabled: false,
                text: ''
            };

            options.imageTitle = getLangString('imagetitle');

            options.arrowIndicator = arrowIndicator;

            options.xLabel = getLangString('xlabel');
            options.yLabel = getLangString('ylabel');

            options.xSmallLabels = { // X small labels.
                left: getLangString('x_small_label_left'),
                center: getLangString('x_small_label_center'),
                right: getLangString('x_small_label_right')
            };

            options.ySmallLabels = { // Y small labels.
                bottom: getLangString('y_small_label_bottom'),
                center: getLangString('y_small_label_center'),
                top: getLangString('y_small_label_top')
            };

            options.selectControls = { // Options for the selector controls to the right of the grid.
                enabled: true,
                xSelectLabel: getLangString('x_select_label'),
                ySelectLabel: getLangString('y_select_label'),
                xDefault: getLangString('x_default'),
                yDefault: getLangString('y_default'),
            };

            if (x && y) {

                options.prePlaceDraggableIcon = true;
                options.prePlaceCoordinates = { // Co-ordinates used for pre-placed token.
                    x: x,
                    y: y
                };
                options.showExternalToken = false;

            }

            options.overlayTexts = { // Text to use when hovering over sections of the grid. False to turn off this feature.
                1:{
                    1: getLangString('overlay_1_1'),
                    2: getLangString('overlay_1_2'),
                    3: getLangString('overlay_1_3'),
                },
                2:{
                    1: getLangString('overlay_2_1'),
                    2: getLangString('overlay_2_2'),
                    3: getLangString('overlay_2_3'),
                },
                3:{
                    1: getLangString('overlay_3_1'),
                    2: getLangString('overlay_3_2'),
                    3: getLangString('overlay_3_3'),
                },
            };


            const mediaQuery = window.matchMedia("(max-width: 639px)");

            function handleMediaQueryChange(e) {
                if (e.matches) {
                    // Media query matches, apply the setting
                    options.tokenWidth = 30;
                }
            }

            mediaQuery.addEventListener('change', handleMediaQueryChange);

            handleMediaQueryChange(mediaQuery);

            var talentgrid = $('.talentgrid').talentgriddle(options);

        });

    }

    function resetCheckmarkVals(selectedCheckmark) {
        var text = emptyDisplayVals();
        var x = selectedCheckmark.attr('data-x');
        var y = selectedCheckmark.attr('data-y');
        if (parseInt(x) && parseInt(y)) {
            text = displayValues(x, y);
        }
        selectedCheckmark.html(text);
    }

    var saveEntry = function(x, y, selectedCheckmark) {
        var wwwroot = getHiddenData('wwwroot');

        var sesskey = getHiddenData('sesskey');
        var timestamp = selectedCheckmark.data('timestamp');
        var periodDuration = $('.goodhabits-container .calendar').data('period-duration');
        var habitId = selectedCheckmark.parent().data('id');
        var data = {x: x,y: y, habitId: habitId, timestamp: timestamp, periodDuration: periodDuration, sesskey: sesskey};
        $.post( wwwroot + "/mod/goodhabits/ajax_save_entry.php", data)
            .done(function( data ) {
                let parsed = JSON.parse(data);
                if (parsed.newly_completed_cal_units_crit) {
                    window.location.reload();
                }
            });
        selectedCheckmark.data('xVal', x);
        selectedCheckmark.data('yVal', y);
        selectedCheckmark.attr("data-x", x);
        selectedCheckmark.attr("data-y", y);
        selectedCheckmark.removeClass(function (index, className) {
            return (className.match (/(^|\s)[xy]-val-\S+/g) || []).join(' ');
        });
        selectedCheckmark.removeClass('noxy');
        selectedCheckmark.addClass('x-val-' + x);
        selectedCheckmark.addClass('y-val-' + y);

        var displayVals = displayValues(x, y);
        selectedCheckmark.html(displayVals);
    };

    var closeEntry = function(habitId) {
        $('.habit-grid-container-' + habitId).empty();

        $('.goodhabits-container').removeClass('grid-is-open');

        $('.checkmark').removeClass('is-selected');

        $('.checkmark').removeClass('values-changed');

        $(document).unbind('keydown');
    };

    var showGrid = function(habitId) {
        $('.habit-grid-container-' + habitId).show();
    };

    $('.checkmark').click(function () {

        if ($(this).hasClass('non-selected-time-unit')) {
            return; // Do nothing if another time unit is selected.
        }

        var gridOpen = $('.goodhabits-container').hasClass('grid-is-open');

        var canInteract = getHiddenData('can-interact');

        if (!canInteract) {
            return null;
        }

        if (gridOpen) {
            return null;
        }

        if ($(this).hasClass('is-in-break')) {
            return null;
        }

        var el = $(this);
        var habitId = el.parent().data('id');
        $('.habit-grid-container-' + habitId).hide();

        var keyDownCount = 0;

        $(document).keydown(function (e) {

            var keyval = e.key;
            if (parseInt(keyval) && keyval > 0 && keyval <= 9) {
                keyDownCount ++;
                var tgResponse = $('.talentgrid-hidden-response');
                var storedResponse = tgResponse.val();
                var xSel = $('.x-axis-selector');
                var xVal = parseInt(xSel.val());
                var ySel = $('.y-axis-selector');
                var yVal = parseInt(ySel.val());

                var preLoadedVals = yVal && !storedResponse;
                if (!xVal || preLoadedVals) {
                    xSel.val(keyval);
                    xSel.trigger("change");
                } else {
                    ySel.val(keyval);
                    ySel.trigger("change");
                }
            }
            if(e.which == 13) {
                // Enter key pressed.
                simulateSavePress(el);
            }
            if (keyDownCount > 2) {
                $(document).unbind('keydown');
            }
        });

        var x = parseInt(el.attr("data-x"));
        var y = parseInt(el.attr("data-y"));

        el.addClass('is-selected');
        $('.goodhabits-container').addClass('grid-is-open');

        var cancelButton = '<button data-type="cancel" type="cancel" name="cancelGrid" value="Cancel">';
        var saveButton = '<button class="save-button" data-type="submit" type="submit" name="saveGrid" value="Save" disabled>';
        var buttonsHtml = '<div class="grid-buttons">' +
            cancelButton + getLangString('cancel') + '</button>' +
            saveButton + getLangString('save') + '</button>' +
            '</div>';

        var timestamp = el.data('timestamp');
        var timeUnitTxt = getLangString('entry_for') + " " + $('.time-unit-' + timestamp).data('text');
        var habitTxt = $('.habit-' + habitId + ' .habit-name').text().trim();
        $('.habit-grid-container-' + habitId).append("" +
            "<div class='grid-heading'>"+timeUnitTxt+" ("+habitTxt+")</div>" +
            "<div class=\"talentgrid\">" +
            buttonsHtml +
            "</div> " +
            " <div class='clear-both'></div> ");

        initGrid(x,y);
        showGrid(habitId);
        listenForCurrentCheckMarkClick(this);
    });

    function listenForCurrentCheckMarkClick(el) {
        $(el).click(function () {
            var bandTable = $('.band-table');
            if (!bandTable.length) {
                // If Grid has not fully loaded, then
                return null;
            }
            var el = $(this);
            var saved = simulateSavePress(el);
            if (!saved) {
                resetCheckmarkVals(el);
                var habitId = el.parent().data('id');
                closeEntry(habitId);
            }
        });
    }

    $('.goodhabits-container').on('click', '.grid-buttons button', function() {
        var JSONvalues = $('.talentgrid-hidden-response').val();
        var values  = (JSONvalues) ? JSON.parse(JSONvalues) : '';
        var action = $(this).data('type');
        var selectedCheckmark = $('.checkmark.is-selected');
        var habitId = selectedCheckmark.parent().data('id');

        if (action == 'submit') {
            saveEntry(values.x, values.y, selectedCheckmark);
        }
        if (action == 'cancel') {
            resetCheckmarkVals(selectedCheckmark);
        }

        closeEntry(habitId);
    });

    $('.goodhabits-container').on('change', '.talentgrid-hidden-response', function() {
        var values = JSON.parse($(this).val());
        var x = values.x;
        var y = values.y;
        var displayVals = displayValues(x, y);
        var selectedCheckmark = $('.checkmark.is-selected');
        selectedCheckmark.addClass('values-changed');
        selectedCheckmark.html(displayVals);
        $('.grid-buttons .save-button').removeAttr('disabled');
    });

    function getLangString(id) {
        return $('.goodhabits-hidden-lang').data('lang-' + id);
    }

    function getHiddenData(id) {
        return $('.goodhabits-hidden-data').data(id);
    }

    function displayValues(x,y) {
        var xClass = 'x-val';
        var yClass = 'y-val';
        if (parseInt(x) === 0) {
            x = ' ';
            xClass += ' empty';
        }
        if (parseInt(y) === 0) {
            y = ' ';
            yClass += ' empty';
        }

        return "<span class='"+xClass+"'>"+x+"</span> <span class='xy-separator'>/</span> <span class='"+yClass+"'>"+y+"</span>";
    }

    function emptyDisplayVals() {
        return displayValues("", "");
    }

    function simulateSavePress(el) {
        var habitId = el.parent().data('id');
        var tgResponse = $('.talentgrid-hidden-response');
        var storedResponse = tgResponse.val();
        if (storedResponse) {
            var vals = JSON.parse(storedResponse);
            if (parseInt(vals.x) && parseInt(vals.y)) {
                saveEntry(vals.x, vals.y, el);
                closeEntry(habitId);
                return true;
            }
        }
        return false;
    }
});