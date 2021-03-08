/*
 * @package    Talent Grid jQuery plugin.
 * @copyright  2017 Joseph Cape (http://chacana.co.uk)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$(document).ready( function () {

    var options = {
        tokenWidth: 36, // Width in pixels of token.
        tokenSnapTolerance: 'auto', // jQuery draggable option. Int or 'auto'. 'auto' will determine a suitable value based on the token size.
        tokenBuffer: 10, // Offset in pixels from cellTop and cellLeft to help make cell detection more accurate (when drag-and-dropping token).
        xLabel: 'Performance', // X label.
        yLabel: 'Potential', // Y label.
        useLargeLabels: true, // Labels spanning cells 1..9.
        useSmallLabels: true, // Labels spanning cells 1..3, 4..6, 7..9.
        xSmallLabels: { // X small labels.
            left: 'Below Expectations',
            center: 'Meets Expectations',
            right: 'Exceeds Expectations'
        },
        ySmallLabels: { // Y small labels.
            bottom: 'Limited',
            center: 'Growth',
            top: 'High'
        },
        imageSrc: './smiley.png', // Location of image for token graphic.
        imageTitle: 'hey, drag me', // Token title text.
        showExternalToken: true, // Whether to display the external token.
        allowTokenDragging: true, // Whether the token is draggable or static.
        prePlaceDraggableIcon: false, // Whether to place a token in the graph in the co-ordinates specified by prePlaceCoordinates.
        prePlaceCoordinates: { // Co-ordinates used for pre-placed token.
            x: 4,
            y: 8
        },
        selectControls: { // Options for the selector controls to the right of the grid.
            enabled: true, 
            xSelectLabel: 'Performance',
            ySelectLabel: 'Potential',
            xDefault: 'Select',
            yDefault: 'Select',
        },
        arrowIndicator: { // Options for the text accompanying the external token.
            enabled: true,
            text: 'Drag icon and place on an area of the grid.<br /><br /> Alternatively, use the drop-down boxes below.'
        },
        overlayTexts: { // Text to use when hovering over sections of the grid. False to turn off this feature.
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
        },
        // TODO: Implement everything below.
        isReport: true,
        reportPopulate: {
            2: // Corresponds to Row
            {
                3: // Corresponds to Cell
                [{name:'Andrew'},{name:'Julia'}] // Array of people related to each talent grid token in that cell.
            },
            5:
            {
                7:
                    [{name:'Josh'}]
            },
        },
        imageTwoSrc: './smiley.png',
        imageThreeSrc: './smiley.png',
        imageFourSrc: './smiley.png',
        imageManySrc: './smiley.png',
    };
    /*var talentgrid = $('.talentgrid').talentgriddle({
        showExternalToken: false,
        prePlaceDraggableIcon: true
    });*/
    // var talentgrid = $('.talentgrid').talentgriddle();
    var talentgrid = $('.talentgrid').talentgriddle(options);
});