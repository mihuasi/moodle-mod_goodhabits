# Good Habits Activity Module (2.0) for Moodle #

The intention of this plugin is to help track habits over time. Within an e-learning context this may help cultivate behaviours that improve learning outcomes.

## Overview of features

- Habit tracking by day, custom range of days or by week.
- Every calendar entry allows for rating by both effort and outcome.
- Granular access control.
- Uses the Privacy API to allow for control over user data.
- Create breaks for stretches of time in which you do not want to track habits.
- Keyboard shortcuts (number keys to set values, enter key to save).
- Can track activity completion based on: user entries / days or weeks complete / number of habits tracked.
- A simple view gives space for posing questions related to a particular entry.
- Can be set up to allow for admin review of entries, or for peer review.
- Student users have control over their own review preferences.

## 2.0 Notes
- Render using mustache templates - allows for easier UI changes.
- Add "simple view".
- Add "mobile view" (embedded page layout) and improve general responsiveness.
- Improve UI for calendar view to access simple view and 'skip' days/weeks.
- Add completion options / change implementation to use custom completion class.
- Add fix for server time changes.
- Fix issues with admin review.
- Add peer review.
- Add mod-level settings for admin/peer review and further user-level preferences.
- Change no-habits message to link to create new habit

## 1.2.2 Notes
- Minor code style updates.

## 1.2.1 Notes
- Add *review* feature. If enabled, allows admin to review the habit calendars of others on the course.
- Improved responsiveness of the design so this activity will work on phones, tablets and smaller screens.
- Changed how keyboard shortcuts work. The first number key sets *effort*, the second sets *outcome* and the enter key saves the entry.
- Effort/outcome can now be set by clicking on the corresponding square in the grid.
- Clicking/tapping the current entry circle will now save the entry (if you have selected new values) or cancel (if you have not).

## 1.1.2 Notes
- Minor maintenance updates (add thirdpartylibs.xml, change lang strings to sentence case).

## 1.1 Notes

- Add support for Backup/Restore.
- Add support for Completion Tracking.
- Fix issues with Privacy API implementation.
- Improve code documentation and style to fit guidelines.

## License ##

2024 Joe Cape <joe.sc.cape@gmail.com>

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <http://www.gnu.org/licenses/>.
# moodle-mod_goodhabits
