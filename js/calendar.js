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
        var habitTxt = $('.habit-' + habitId + ' .habit-name').text();
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