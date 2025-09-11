# local_datacurso_ratings

Local plugin to allow users to rate a course module with a Like / Dislike button and optional feedback for negative ratings.

- **Hook used**: `\core\hook\output\after_http_headers` via `/db/hooks.php`
- **UI**: Mustache template `templates/rate_button.mustache`
- **JS**: ES6 AMD module in `amd/src/rate.js` (no jQuery, using `import` / `export`)
- **Webservice**: `local_datacurso_ratings_save_rating` defined in `db/services.php`, implemented at `classes/external/save_rating.php`
- **DB table**: `local_datacurso_ratings` (`cmid`, `userid`, `rating`, `feedback`, `timecreated`, `timemodified`) with unique index on (`cmid`, `userid`)
- **Capability**: `local/datacurso_ratings:rate` (granted to Student/Teacher/Manager by default)

## Install

1. Unzip into `local/datacurso_ratings` within your Moodle root.
2. Go to *Site administration → Notifications* to trigger installation and the AMD build.
3. Ensure role permissions allow `local/datacurso_ratings:rate` for the roles you want.
4. Visit any activity page (e.g. a quiz or assignment) as a user with permission. A **Calificar actividad / Rate activity** button will appear.

## Notes

- Negative rating shows a feedback textarea and sends it along with the rating.
- Positive rating sends immediately with no feedback.
- JS uses `core/ajax`, `core/notification`, and `core/str`.
- The button is displayed only on module pages, for logged-in non-guest users with the required capability.
