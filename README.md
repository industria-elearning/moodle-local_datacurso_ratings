# Datacurso Ratings Plugin for Moodle

A comprehensive Moodle local plugin that enables students to rate course activities and resources with a simple Like/Dislike system, providing valuable feedback for educational institutions to improve content quality and student satisfaction.

## Overview

**Datacurso Ratings** allows students to evaluate activities and resources within Moodle courses using a thumbs-up/thumbs-down rating system. When students provide negative feedback, they can select from predefined reasons or provide custom comments. The plugin generates detailed reports and analytics to help educators and administrators understand student engagement and content effectiveness.

## Key Features

### Student Rating System
- **Simple Rating Interface**: Students can rate activities with 👍 (Like) or 👎 (Dislike) buttons
- **Contextual Feedback**: Negative ratings trigger predefined feedback options or custom comments
- **One Rating Per User**: Unique constraint ensures each student can rate an activity only once
- **Seamless Integration**: Rating buttons appear automatically on all activity pages

### Administrative Management
- **Predefined Feedback Management**: Administrators can create and manage standard feedback responses
- **Global Plugin Settings**: Enable/disable the plugin system-wide
- **Permission Controls**: Fine-grained access control through Moodle capabilities

### Comprehensive Reporting System
- **Course-Level Reports**: Detailed statistics for individual courses showing activity performance
- **Global Reports**: Institution-wide overview of all rated activities across courses
- **Advanced Filtering**: Filter by course categories, specific courses, or search activities
- **Interactive Comments Modal**: View detailed feedback with pagination, search, and statistics
- **Export Capabilities**: Export data for further analysis

### AI-Powered Analytics 
- **Sentiment Analysis**: Automated analysis of comment sentiment (positive/negative/neutral)
- **Recommendation Engine**: AI-generated suggestions for content improvement

## Technical Architecture

### Core Components
- **Hook Implementation**: `\core\hook\output\before_footer_html_generation` via `/db/hooks.php`
- **UI Templates**: Mustache templates in `templates/` directory
- **JavaScript**: Modern ES6 AMD modules in `amd/src/` (no jQuery dependency)
- **Web Services**: RESTful API endpoints defined in `db/services.php`
- **Database Schema**: Optimized table structure with proper indexing

### Database Structure
**Main Table**: `local_datacurso_ratings`
```sql
- cmid (INT) - Course module ID
- userid (INT) - User ID who rated
- rating (TINYINT) - 1 for like, 0 for dislike
- feedback (TEXT) - Optional comment text
- timecreated (BIGINT) - Creation timestamp
- timemodified (BIGINT) - Modification timestamp
- UNIQUE INDEX (cmid, userid) - Prevents duplicate ratings
```

**Feedback Options Table**: `local_datacurso_ratings_feedback`
```sql
- id (INT) - Primary key
- feedback_text (TEXT) - Predefined feedback option
- is_active (TINYINT) - Active status
- sort_order (INT) - Display order
- timecreated (BIGINT) - Creation timestamp
- timemodified (BIGINT) - Modification timestamp
```

### Web Services API

#### Core Rating Services
- `local_datacurso_ratings_save_rating` - Save student ratings and feedback
- `local_datacurso_ratings_get_ratings_report_course` - Get course-specific rating data
- `local_datacurso_ratings_get_ratings_report` - Get global rating statistics
- `local_datacurso_ratings_get_activity_comments` - Retrieve detailed comments with pagination
- `local_datacurso_ratings_get_courses_by_category` - Filter courses by category

#### Administrative Services
- `local_datacurso_ratings_add_feedback` - Add predefined feedback options
- `local_datacurso_ratings_delete_feedback` - Remove feedback options

### Capabilities and Permissions
- `local/datacurso_ratings:rate` - Rate activities (Students, Teachers, Managers)
- `local/datacurso_ratings:viewreports` - View rating reports (Teachers, Managers)
- `local/datacurso_ratings:manage` - Manage plugin settings (Managers only)

## Installation

1. **Download and Extract**: Unzip the plugin into `local/datacurso_ratings` within your Moodle root directory

2. **Install Plugin**: Navigate to *Site Administration → Notifications* to trigger installation and database setup

3. **Verify Permissions**: Ensure appropriate roles have the required capabilities:
   - Students: `local/datacurso_ratings:rate`
   - Teachers: `local/datacurso_ratings:rate`, `local/datacurso_ratings:viewreports`
   - Managers: All capabilities

4. **Configure Settings**: Go to *Site Administration → Plugins → Local plugins → Datacurso Ratings* to configure global settings

5. **Test Functionality**: Visit any activity page as a user with rating permission to see the rating interface

## Usage

### For Students
1. Navigate to any course activity or resource
2. Scroll to find the "Rate this activity" button
3. Click 👍 for positive feedback (saves immediately)
4. Click 👎 for negative feedback and optionally provide comments
5. Submit feedback to help improve course content

### For Teachers
1. Access course reports via the course navigation menu under "Reports"
2. View "Activity/Resource Ratings Report" for detailed course statistics
3. Analyze student satisfaction metrics and feedback
4. Click "View Comments" to see detailed student feedback in a modal window
5. Click "Generate analysis with AI" to generate analysis of comments of activitie/resource

### For Administrators
1. Manage predefined feedback options in plugin settings
2. Access global reports showing institution-wide rating statistics
3. Use advanced filters to analyze data by category, course, or activity
4. Export data for external analysis and reporting
5. Click "Generate analysis with AI" to generate analysis of comments of activitie/resource

## Report Features

### Course-Level Reports
- Activity-by-activity breakdown of ratings
- Satisfaction percentages and trends
- Comment summaries and detailed feedback
- Visual indicators for content performance
- AI analysis comments of activtie/resource a course level

### Global Reports
- Institution-wide rating statistics
- Course category comparisons
- Most/least satisfied activities identification
- Advanced filtering and search capabilities
- AI analysis comments of activtie/resource global activitie/resource

### Interactive Comments Analysis
- Paginated comment viewing (handles 100+ comments efficiently)
- Search and filter comments by text
- Keyword frequency analysis
- Statistical summaries (like/dislike ratios, comment counts)

## Multilingual Support

The plugin includes comprehensive language packs:
- **English** (`lang/en/`)
- **Spanish** (`lang/es/`)
- Additional languages can be easily added through standard Moodle localization

## License

This plugin is licensed under the GNU General Public License v3.0, maintaining compatibility with Moodle's licensing requirements.

---

**Datacurso Ratings** transforms student feedback collection in Moodle, providing educators with actionable insights to continuously improve educational content quality and student satisfaction.