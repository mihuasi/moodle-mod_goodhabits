$(document).ready(function() {
    var currentIndex = 0; // Start at the first habit
    var $habits = $('.habit'); // Get all the habit divs
    var $prevButton = $('.prev-habit'); // Left button
    var $nextButton = $('.next-habit'); // Right button

    // Initial setup: Show the first habit and hide the left button
    $habits.eq(currentIndex).addClass('active');
    updateButtons(); // Update the visibility of the buttons

    // Function to update the visibility of buttons based on the current index
    function updateButtons() {
        // Hide left button if at the first habit
        if (currentIndex === 0) {
            $prevButton.hide();
        } else {
            $prevButton.show();
        }

        // Hide right button if at the last habit
        if (currentIndex === $habits.length - 1) {
            $nextButton.hide();
        } else {
            $nextButton.show();
        }
    }

    // Function to show a specific habit based on index
    function showHabit(index) {
        // Fade out all habits, remove the 'active' class after fade-out completes
        $habits.filter('.active').fadeOut(400, function() {
            $(this).removeClass('active'); // Remove 'active' class after fade out

            // Fade in the new habit and add the 'active' class
            $habits.eq(index).fadeIn(400).addClass('active');

            // After the transition, update the buttons
            updateButtons();
        });
    }

    // Next habit
    $nextButton.on('click', function() {
        if (currentIndex < $habits.length - 1) { // Prevent going past the last habit
            currentIndex++;
            showHabit(currentIndex); // Show the new habit
        }
    });

    // Previous habit
    $prevButton.on('click', function() {
        if (currentIndex > 0) { // Prevent going before the first habit
            currentIndex--;
            showHabit(currentIndex); // Show the new habit
        }
    });



    // Function to get values from both sliders and send an AJAX request
    function updateSliders(habitId) {
        // Get values of both sliders
        let $effort = $('.discrete-slider.effort.habit-' + habitId);
        var val_effort = $effort.val();
        let $outcome = $('.discrete-slider.outcome.habit-' + habitId);
        var val_outcome = $outcome.val();

        // Get additional data (habit IDs and timestamps)
        // var habitId = $effort.data('habit-id');
        var timestamp = $effort.data('timestamp');
        let $simple = $('.simple-view');
        var periodDuration = $simple.data('period-duration');
        var sessKey = $simple.data('sesskey');
        var wwwroot = $simple.data('wwwroot');

        // Prepare data to be sent to the server
        var postData = {
            x: val_effort,
            y: val_outcome,
            habitId: habitId,
            timestamp: timestamp,
            periodDuration: periodDuration,
            sesskey: sessKey
        };

        var URL = wwwroot + '/mod/goodhabits/ajax_save_entry.php';

        // AJAX call to send the data to the server
        $.ajax({
            url: URL, // Replace with your server endpoint
            type: 'POST',
            data: postData,
            success: function(response) {
                // Handle the success response
                $('.habits-wrapper .saved-' + habitId).fadeOut();
                $('.habits-wrapper .saved-' + habitId).fadeIn();
                // TODO: increment question version tracker.
            },
            error: function(xhr, status, error) {
                // Handle any errors
                console.error('Error saving data:', error);
            }
        });
    }

    // Attach event listeners to both sliders
    $('.discrete-slider.effort, .discrete-slider.outcome').on('change', function() {
        let habitId = $(this).data('habit-id');
        updateSliders(habitId); // Call the update function whenever a slider changes
    });
});
