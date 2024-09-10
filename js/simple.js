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
        $habits.removeClass('active'); // Hide all habits
        $habits.eq(index).addClass('active'); // Show the specific habit
        updateButtons(); // Update the visibility of the buttons
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
});
