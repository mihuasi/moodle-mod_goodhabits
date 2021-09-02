/*
 * @package    Talent Grid jQuery plugin.
 * @copyright  2021 Joe Cape (http://chacana.co.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

(function ( $ ) {
$.fn.talentgriddle = function(options) {
    if (options == null) {
        options = {};
    }
    optionsInit(options);
    var baseClass = 'talentgrid';
    var tokenWidth = options.tokenWidth;
    var tokenSnapTolerance = options.tokenSnapTolerance;
    var tokenBuffer = options.tokenBuffer;
    var xLabel = options.xLabel;
    var yLabel = options.yLabel;
    var useLargeLabel = options.useLargeLabels;
    var useSmallLabels = options.useSmallLabels;
    var xSmallLabels = options.xSmallLabels;
    var ySmallLabels = options.ySmallLabels;
    var imageSrc = options.imageSrc;
    var imageTitle = options.imageTitle;
    var prePlaceDraggableIcon = options.prePlaceDraggableIcon;
    var prePlaceCoordinates = options.prePlaceCoordinates;
    var showExternalToken = options.showExternalToken;
    var allowTokenDragging = options.allowTokenDragging;
    var selectControls = options.selectControls;
    var arrowIndicator = options.arrowIndicator;
    var overlayTexts = options.overlayTexts;

    if (tokenSnapTolerance === 'auto') {
        tokenSnapTolerance = Math.ceil(tokenWidth * 0.8);
    }
    var base = $('.' + baseClass);

    var talentGridToken = '<div class="talentgrid-token init"> ' +
        '<img title="' + imageTitle + '" width="' + tokenWidth + '" height="' + tokenWidth + '" ' +
        'class="talentgrid-image" src="' + imageSrc + '" /> ' +
        '</div> ';

    var content = '<div class="talentgrid-container"></div>';
    base.append(content);

    var gridSize = 9;
    var cellSize = tokenWidth;

    var smiley = (prePlaceDraggableIcon) ? talentGridToken : null;
    var coordinates = (prePlaceDraggableIcon) ? prePlaceCoordinates : null;
    var reportArray = null;
    var report = false;
    var otherSmileys = null;
    var table = generateTable(gridSize, cellSize, smiley, coordinates, reportArray, report, otherSmileys);
    var overlays = generateOverlays(gridSize, cellSize, overlayTexts);

    var container = $('.' + baseClass + '-container');

    if (useSmallLabels) {

        var xSmallLabelsHtml = '<div class="talentgrid-x-axis">' +
                                    '<span class="x-sub-label x-first" title="' + xSmallLabels.left + '">' + xSmallLabels.left + '</span> ' +
                                    '<span class="x-sub-label x-second" title="' + xSmallLabels.center + '">' + xSmallLabels.center + '</span> ' +
                                    '<span class="x-sub-label x-third" title="' + xSmallLabels.right + '">' + xSmallLabels.right + '</span>' +
            '                   </div>';
        var ySmallLabelsHtml = '<div class="talentgrid-y-axis rotated-text"> ' +
                                    '<span class="y-sub-label y-first" title="' + ySmallLabels.top + '">' + ySmallLabels.top + '</span> ' +
                                    '<span class="y-sub-label y-second" title="' + ySmallLabels.center + '">' + ySmallLabels.center + '</span> ' +
                                    '<span class="y-sub-label y-third" title="' + ySmallLabels.bottom + '">' + ySmallLabels.bottom + '</span>' +
            '                   </div>';

    } else {
        var xSmallLabelsHtml = '';
        var ySmallLabelsHtml = '';
    }

    if (useLargeLabel) {
        var smallLabelsAlsoClass = (useSmallLabels) ? 'small-labels-also-used' : 'only-large';
        var yLargeLabelHtml = '<div class="talentgrid-y-axis large-label rotated-text ' + smallLabelsAlsoClass + '">' + yLabel + '</div>';
        var xLargeLabelHtml = '<div class="talentgrid-x-axis large-label ' + smallLabelsAlsoClass + '">' + xLabel + '</div>';
    } else {
        var yLargeLabelHtml = '';
        var xLargeLabelHtml = '';
    }


    container.append('' +
        '<div class="table-plus-axis">' + overlays + table  + yLargeLabelHtml + ySmallLabelsHtml + xLargeLabelHtml + xSmallLabelsHtml +
        '</div>');
    container.append('<input type="hidden" class="talentgrid-hidden-response" name="talentgrid-hidden-response" />');

    fadeOverlays();

    addControls(container, talentGridToken, selectControls, arrowIndicator, showExternalToken, prePlaceCoordinates);

    addDynamicCss(gridSize, cellSize);

    var token = $('.' + baseClass + '-token');
    if (token.length && allowTokenDragging) {
        token.draggable({
            addClasses: true,
            snap: true,
            containment: container,
            snapTolerance: tokenSnapTolerance
        });
        var snapToCells = $(".talentgrid-container .snap-to-cell");
        snapToCells.draggable({
            disabled: true
        });

        token.on( "dragstart", function( event, ui ) {
            $(this).removeClass('init');
            $('.drag-instructions-arrow').fadeOut();
        });
        token.on("dragstop", function (event, ui) {

            var pos = $(this).offset();
            var tokenTop = pos['top'];
            var tokenLeft = pos['left'];

            var table = $('.talentgrid-container table.talentgrid-table');
            var tablePos = table.offset();
            var tableLeft = tablePos['left'];

            var tokenWithinLeftBounds = tokenLeft >= tableLeft;

            if (tokenWithinLeftBounds) {
                var cells = $('.talentgrid-container tr.talentgrid-row');

                var foundCell = false;
                var buffer = tokenBuffer;
                $.each(cells, function () {
                    var cellPos = $(this).offset();
                    var cellTop = cellPos['top'];
                    if (tokenTop <= cellTop + buffer) {
                        var yValue = $(this).data('y-val');
                        var tdClass = '.talentgrid-container td.talentgrid-cell-' + yValue;
                        var columnCells = $(tdClass);
                        $.each(columnCells, function () {
                            var columnCellPos = $(this).offset();
                            var columnCellLeft = columnCellPos['left'];

                            if (tokenLeft <= columnCellLeft + buffer) {
                                var xValue = $(this).data('x-val');
                                var coordinates = {
                                    y: yValue,
                                    x: xValue
                                }
                                $('.talentgrid-hidden-response').val(JSON.stringify(coordinates));
                                $('.talentgrid-hidden-response').trigger("change");
                                $('select.x-axis-selector').val(xValue);
                                $('select.x-axis-selector').trigger("change");
                                $('select.y-axis-selector').val(yValue);
                                $('select.y-axis-selector').trigger("change");
                                foundCell = true;
                                return false;
                            }
                        });
                        return false;
                    }
                })
            }

            if (!foundCell) {
                $('.talentgrid-hidden-response').val('');
                $('select.x-axis-selector').val(0);
                $('select.y-axis-selector').val(0);
            }

        });
    }
    $('.path-mod-goodhabits table.band-table td').click(function (event) {
        var pointerX = event.pageX;
        var pointerY = event.pageY;
        var xBand = $(this).data('x-band');
        var yBand = $(this).data('y-band');
        var refCellX = xBand * 3;
        var refCellY = yBand * 3;
        var xCell = 0;
        var yCell = 0;

        var pos = $(this).offset();
        var top = pos['top'];
        var left = pos['left'];
        if (pointerX < left || pointerY < top) {
            return null;
        }

        var baseX = pointerX - left;
        var whichLeft = baseX / tokenWidth;
        if (whichLeft < 1) {
            xCell = refCellX - 2;
        } else if (whichLeft < 2) {
            xCell = refCellX - 1;
        } else {
            xCell = refCellX;
        }

        var baseY = pointerY - top;
        var whichTop = baseY / (tokenWidth + 2);
        if (whichTop < 1) {
            yCell = refCellY;
        } else if (whichTop < 2) {
            yCell = refCellY - 1;
        } else {
            yCell = refCellY - 2;
        }

        $("select.x-axis-selector").val(xCell);
        $("select.x-axis-selector").trigger("change");
        $("select.y-axis-selector").val(yCell);
        $("select.y-axis-selector").trigger("change");
    });
}

    function addControls(container, talentGridToken, selectControls, arrowIndicator, showExternalToken, prePlaceCoordinates) {
        if (!showExternalToken && !selectControls.enabled) {
            return null;
        }
        container.append('<div class="talentgrid-controls"></div>');

        var controlsHolder = $('.talentgrid-controls');
        if (showExternalToken) {
            controlsHolder.append(talentGridToken);
        }

        if (arrowIndicator.enabled) {
            var arrowHtml = '&uarr;';
            var dragArrow = '<span class="drag-instructions-arrow">' + arrowHtml + '</span>';
            if (!showExternalToken) {
                dragArrow = '';
            }
            var dragInstructions = '<div class="drag-instructions">' + dragArrow + arrowIndicator.text + '</div>';
            controlsHolder.append(dragInstructions);
        }

        if (selectControls.enabled) {
            var selectControlsHtml = generateAxisSelector('x', prePlaceCoordinates, selectControls);
            selectControlsHtml += generateAxisSelector('y', prePlaceCoordinates, selectControls);
            controlsHolder.append('<div class="talentgrid-select-controls"></div>');
            var selectControlsHolder = $('.talentgrid-select-controls');
            selectControlsHolder.append(selectControlsHtml);
            controlsHolder.append(selectControlsHolder);

            var talentGridToken = $('.talentgrid-token');
            $( "select.x-axis-selector" ).change(function() {
                tryToUpdateSmiley('x', $(this).val(), talentGridToken);
            });
            $( "select.y-axis-selector" ).change(function() {
                tryToUpdateSmiley('y', $(this).val(), talentGridToken);
            });
        }
    }

    function generateAxisSelector(axis, coordinates, selectControls) {
        var preSelectVal = 0;
        if (coordinates) {
            preSelectVal = (axis === 'x') ? coordinates.x : coordinates.y;
        }
        var selectLabel = '';
        var defaultText = '';
        if (axis === 'x') {
            selectLabel = selectControls.xSelectLabel;
            defaultText = selectControls.xDefault;
        } else {
            selectLabel = selectControls.ySelectLabel;
            defaultText = selectControls.yDefault;
        }
        var name = axis + '-axis-selector';
        var html = '<label class="' + name + '-label axis-selector-label" for="' + name + '">' + selectLabel + '</label>';
        html += '<select name="' + name + '" class="' + name + ' axis-selector">';

        var options = [];
        options.push(defaultText);
        for (i = 1; i <=9; i++) {
            options.push(i);
        }
        for (var key in options) {
            var value = options[key];
            var sel = ( parseInt(key) === parseInt(preSelectVal)) ? ' selected="selected" ' : '';
            html += '<option value="' + key + '" ' + sel + '>' + value + '</option>';
        }
        html += '</select>';
        return html;
    }

    function tryToUpdateSmiley(axis, value, talentGridToken) {
        if (axis === 'x') {
            var referenceEl = $('table.talentgrid-table').find("[data-x-val='" + value + "']:first");
        } else if (axis === 'y') {
            var rowRef = 9 - (value - 1);
            var referenceEl = $('td.talentgrid-cell-' + rowRef + ":first");
        } else {
            throw "Invalid Axis arg";
        }
        var offset = referenceEl.offset();

        if (axis === 'x') {
            var yVal = $( "select.y-axis-selector").val();
            var xVal = value;
            var coordinates = {
                y: yVal,
                x: xVal
            };

            $('.talentgrid-hidden-response').val(JSON.stringify(coordinates));
            $('.talentgrid-hidden-response').trigger("change");
            if (yVal && validateSelectOption(yVal)) {

                var yRowRef = 9 - (yVal - 1);
                var yReferenceEl = $('td.talentgrid-cell-' + yRowRef + ":first");
                var yOffset = yReferenceEl.offset();
                talentGridToken.offset({left: offset.left, top: yOffset.top});
                $('.drag-instructions-arrow').fadeOut();
            }
        } else {
            var xVal = $( "select.x-axis-selector").val();
            var yVal = value;
            var coordinates = {
                y: yVal,
                x: xVal
            };
            $('.talentgrid-hidden-response').val(JSON.stringify(coordinates));
            $('.talentgrid-hidden-response').trigger("change");
            if (xVal && validateSelectOption(xVal)) {
                var xReferenceEl = $('table.talentgrid-table').find("[data-x-val='" + xVal + "']:first");
                var xOffset = xReferenceEl.offset();
                talentGridToken.offset({top: offset.top, left: xOffset.left});
                $('.drag-instructions-arrow').fadeOut();
            }
        }
    }

    function validateSelectOption(value) {
        if (parseInt(value) >= 1 && parseInt(value) <= 9) return true;
        return false;
    }

    function addDynamicCss(gridSize, cellSize) {
        var tableHeight = gridSize * cellSize;
        var tableWidth = tableHeight;
        var containerHeight = tableHeight + (2 * cellSize);
        var macroCell = cellSize * 3;
        var sublabelOffset = macroCell / 5;
        var ySublabelOffset = macroCell / 2;

        $('.talentgrid-container').css('height', containerHeight + 'px');
        $('.table-plus-axis').css('width', (tableWidth + 50) + 'px');
        $('.talentgrid-table').css('width', tableWidth + 'px');
        $('.y-sub-label.y-first').css('left', ( ySublabelOffset) + 'px');
        $('.y-sub-label.y-second').css('left', (- macroCell + ySublabelOffset) + 'px');
        $('.y-sub-label.y-third').css('left', (- (macroCell * 2) + ySublabelOffset) + 'px');

        $('.x-sub-label.x-first').css('left', ( sublabelOffset) + 'px');
        $('.x-sub-label.x-second').css('left', ( macroCell + sublabelOffset) + 'px');
        $('.x-sub-label.x-third').css('left', ( (macroCell * 2) + sublabelOffset) + 'px');

        $('.talentgrid-y-axis.large-label').css('top', ( macroCell * 1.5 ) + 'px');
        $('.talentgrid-x-axis.large-label').css('left', ( macroCell * 1.1) + 'px');

        var token = $('.talentgrid-token');
        token.css('width', cellSize + 'px');
        token.css('height', cellSize + 'px');
    }

    function fadeOverlays() {
       var band = $('.talentgrid-band');
       band.hover(function() { 
            $(this).stop().fadeTo('slow', 0.7);
            $('.talentgrid-token').stop().fadeTo('slow', 0.4);
        }, function() { 
            $(this).stop().fadeTo('slow', 0);
             $('.talentgrid-token').stop().fadeTo('slow', 1);
        });
         
    }

    function generateOverlays(gridSize, cellSize, overlayTexts) {
        if (!overlayTexts) return '';
        var bandSize = cellSize * 3;
        bandSize += 4;
        tableClass = 'band-table';
        var html = '<table class="' + tableClass + '">';
        for (i = 1; i <= 3; i++) {
            html += '<tr class="talentgrid-band-'+i+'">';
            for (j = 1; j <= 3; j++) {
                innerContent = '<div style="height: ' + (bandSize - 10) + 'px; width: ' + (bandSize - 10) + 'px;position: absolute;';
                var text = overlayTexts[i][j];
                innerContent += 'top:0;bottom: 0;left: 0;right: 0;margin: auto;background-color:white;text-align: center;vertical-align: middle;">'+text+'</div>';
                content = '<div style="position:relative;height: ' + bandSize + 'px; width: ' + bandSize + 'px; overflow:hidden;">'+innerContent+'</div>';
                html += '<td class="talentgrid-band talentgrid-band-'+j+'" data-x-band="'+j+'" data-y-band="'+(4 - i)+'">'+content+'</td>';
            }
            html += '</tr>';
        }
        html += '</table>';
        return html;
    }

    function generateTable(gridSize, cellSize, icon, coordinates, reportArray, report, otherIcons) {
        if (coordinates) {
            var x = coordinates.x;
            var y = coordinates.y;
        }
        var tableClass = (report) ? 'talentgrid-report-table' : 'talentgrid-table';
        var html = '<table class="fixed ' + tableClass + '">';
        for (i = 1; i <= gridSize; i++) {
            html += '<col width="' + (cellSize) + 'px" />';
        }
        for (i = 1; i <= gridSize; i++) {
            var trClass = 'normal';
            if ((i - 1) % 3 === 0) {
                trClass = 'emboldened';
            }
            if (gridSize === i) {
                trClass = 'final';
            }
            var yVal = gridSize - (i - 1);
            var yBand = Math.ceil(yVal / 3);
            html += '<tr data-y-val="' + yVal + '" class="talentgrid-row ' + trClass + '">';
            if (reportArray && typeof reportArray.yVal != 'undefined') {
                var reportRowValues = reportArray.yVal;
            } else {
                var reportRowValues = false;
            }
            for (j = 1; j <= gridSize; j++) {
                var tdClass = 'normal';
                if ((j - 1) % 3 === 0) {
                    var tdClass = 'emboldened';
                }
                if (gridSize === j) {
                    tdClass = 'final';
                }
                var tdRowClass = 'talentgrid-cell-' + i;
                var xVal = j;
                var xBand = Math.ceil(xVal / 3);
                var smileyClass = '';
                if (coordinates && icon && parseInt(x) === parseInt(xVal) && parseInt(y) === parseInt(yVal)) {
                    var content = icon;
                    var smileyClass = 'cell-with-smiley';
                } else {
                    var content = '<div style="height: ' + cellSize + 'px; width: ' + cellSize + 'px; overflow:hidden;">';
                }
                var dataStudents = '';
                var titleText = '';
                var studentsClass = '';
                if (reportRowValues && typeof reportRowValues.xVal != 'undefined') {
                    var students = reportRowValues.xVal;
                    var smileyClass = 'cell-with-smiley';
                    var content = icon;
                    if (count(students) > 1) {
                        smileyClass = 'cell-with-multiple-smileys';
                        switch (count(students)) {
                            case 2:
                                content = otherIcons.two;
                                break;
                            case 3:
                                content = otherIcons.three;
                                break;
                            case 4:
                                content = otherIcons.four;
                                break;
                            default:
                                content = otherIcons.many;
                                break;
                        }
                    }
                    if (count(students)) {
                        var studentNames = new Array();
                        var studentsClassArr = new Array();
                        for (var index in students) {
                            if (!students.hasOwnProperty(index)) {
                                continue;
                            }
                            var appraisalEntry = students[index];
                            studentNames.push(appraisalEntry.userFullname);
                            studentsClassArr.push(appraisalEntry.appraisalEntry);
                        }
                    }
                    var studentsClass = studentsClassArr.join(" ");
                    var dataStudents = 'data-students="' + JSON.stringify(studentNames) + '"';
                    var titleText = 'title="' + studentNames.join(", ") + '"';
                }
                var cellClassesArray = [
                    'snap-to-cell',
                    'talentgrid-cell',
                    tdClass,
                    tdRowClass,
                    smileyClass,
                    studentsClass,
                    'x-band-' + xBand,
                    'y-band-' + yBand,
                    'x-val-' + xVal,
                    'y-val-' + yVal,
                ];

                var cellClasses = cellClassesArray.join(" ");
                html += '<td ' + titleText + ' ' + dataStudents + ' data-x-val="' + xVal + '" class="' + cellClasses + '">' + content + '</td>';

            }
            html += '</tr>';
        }

        html += '</table>';
        return html;
    }

    function optionsInit(options) {
        setOptionDefault(options, 'tokenWidth', 36);
        setOptionDefault(options, 'tokenSnapTolerance', 'auto');
        setOptionDefault(options, 'tokenBuffer', 10);
        setOptionDefault(options, 'xLabel', 'Performance');
        setOptionDefault(options, 'yLabel', 'Potential');
        setOptionDefault(options, 'useLargeLabels', true);
        setOptionDefault(options, 'useSmallLabels', true);
        setOptionDefault(options, 'xSmallLabels', {
            left: 'Below Expectations',
            center: 'Meets Expectations',
            right: 'Exceeds Expectations'
        });
        setOptionDefault(options, 'ySmallLabels', {
            bottom: 'Limited',
            center: 'Growth',
            top: 'High'
        });
        setOptionDefault(options, 'imageSrc', './smiley.png');
        setOptionDefault(options, 'imageTitle', 'hey, drag me');
        setOptionDefault(options, 'showExternalToken', true);
        setOptionDefault(options, 'allowTokenDragging', true);
        setOptionDefault(options, 'prePlaceDraggableIcon', false);
        setOptionDefault(options, 'prePlaceCoordinates', false);
        setOptionDefault(options, 'selectControls', {
            enabled: true,
            xSelectLabel: 'Performance',
            ySelectLabel: 'Potential',
            xDefault: 'Select',
            yDefault: 'Select',
        });
        setOptionDefault(options, 'arrowIndicator', {
            enabled: true,
            text: 'Drag icon and place on an area of the grid.<br /><br /> Alternatively, use the drop-down boxes below.'
        });
        setOptionDefault(options, 'overlayTexts', {
            1:{
                1: 'Rough Diamond',
                2: 'Future Star',
                3: 'Consistent Star',
            },
            2:{
                1: 'Inconsistent Player',
                2: 'Key Player',
                3: 'Current Star',
            },
            3:{
                1: 'Talent Risk',
                2: 'Solid Professional',
                3: 'High Professional',
            },
        });
    }

    function setOptionDefault(options, property, value) {
        if (!options.hasOwnProperty(property)) {
            options[property] = value;
        }
    }
}( jQuery ));