jQuery(window).on('load',function($) {

    var $ = jQuery;

    // When any td is clicked
    $('.grid-box-wording td').click(function() {
        if ($('.grid-box-wording textarea').length > 0) {
            return; // Do nothing if a textarea is already active
        }

        // Get the current text of the td
        var currentText = $(this).text();

        var cellName = $(this).data('cell');

        let idWordingPref = 'wording_pref_' + cellName;
        // If the hidden input for this cell doesn't exist, create it
        if ($('#input_' + cellName).length === 0) {
            $('<input>', {
                type: 'hidden',
                id: idWordingPref,
                name: idWordingPref,
                class: 'hidden-input',
                value: currentText
            }).appendTo('table.grid-box-wording');
        }

        // Replace the text with a textarea containing the same text
        $(this).html('<textarea rows="3" cols="15" name="' + idWordingPref + '">' + currentText + '</textarea>');

        // Focus on the new textarea
        $(this).find('textarea').focus();

        // When the textarea loses focus, save the value back to the td
        $(this).find('textarea').blur(function() {
            var newText = $(this).val();
            // Update the hidden input value with the new text
            $('#wording_pref_' + cellName).val(newText);
            $(this).parent().html(newText); // Update td with the new text
        });
    });
});