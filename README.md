# Datacurso Ratings for Moodle

The **Datacurso Ratings** plugin allows students to rate course activities and resources in Moodle using a simple 👍 Like / 👎 Dislike system, providing valuable feedback to improve content quality and student satisfaction.  

It also integrates **AI-powered analytics**, generating intelligent reports and recommendations for teachers and administrators.

## Requirements

- **Moodle version**: 4.0+ (specify exact versions supported, e.g., 4.1, 4.2, 4.3, 4.4)
- **PHP version**: 8.0 or higher
- **Database**: MySQL 5.7+ or PostgreSQL 10+
- **Browser compatibility**: Modern browsers (Chrome 80+, Firefox 75+, Safari 13+, Edge 80+)

## Key Features

### Student Rating System
- Intuitive interface with 👍 / 👎 buttons on all activities and resources
- Contextual feedback: negative ratings allow selecting predefined reasons or writing custom comments
- One rating per user per activity restriction
- Seamless integration into Moodle with no additional setup required
- AJAX-based rating system for immediate feedback

### Administrative Management
- Manage predefined feedback reasons through admin interface
- Global plugin configuration (enable/disable functionality)
- Permission control using Moodle capabilities system
- Bulk operations for feedback management

### Reports and Analytics
- **Course-level reports**: Detailed statistics for activities and resources within a course
- **Global reports**: Institution-wide analytics with advanced filtering options
- **Interactive comment explorer**: Modal interface with pagination, search, and sorting
- **Data export**: CSV/Excel export functionality for external analysis
- **Real-time statistics**: Live updates of rating metrics

### AI-Powered Analytics
- **Sentiment analysis** of student comments (positive, negative, neutral)
- **Smart recommendations** with AI-generated suggestions for content improvement
- **Trend analysis** using machine learning algorithms
- Compatible with Moodle's AI subsystem

## Installation

### Method 1: Manual Installation

1. **Download** the plugin files
2. **Extract** to your Moodle directory:
   ```
   {moodleroot}/local/datacurso_ratings/
   ```
3. **Login** as administrator
4. **Navigate** to Site administration → Notifications
5. **Complete** the installation process by following the prompts

### Method 2: Command Line Installation

```bash
cd /path/to/moodle
php admin/cli/upgrade.php
```

### Post-Installation Setup

1. **Configure capabilities**:
   - Students: `local/datacurso_ratings:rate`
   - Teachers: `local/datacurso_ratings:rate`, `local/datacurso_ratings:viewreports`
   - Managers: All capabilities

2. **Global settings**:
   - Navigate to Site administration → Plugins → Local plugins → Datacurso Ratings
   - Configure default settings as needed

3. **Verify installation**:
   - Check that rating buttons appear on course activities
   - Test the rating functionality with a test student account

## Configuration

### Global Settings

Access global configuration through:
**Site administration → Plugins → Local plugins → Datacurso Ratings**

Available settings:
- **Enable/Disable plugin globally**
- **Default feedback reasons** (can be customized)
- **AI analytics settings** (if AI subsystem is available)
- **Report permissions** and visibility settings

### Permissions and Capabilities

| Capability | Description | Default Roles |
|------------|-------------|---------------|
| `local/datacurso_ratings:rate` | Rate activities and resources | Student, Teacher, Manager |
| `local/datacurso_ratings:viewreports` | View rating reports | Teacher, Manager |
| `local/datacurso_ratings:manage` | Manage plugin settings | Manager |

## Usage

### For Students
1. **Navigate** to any course activity or resource
2. **Click** the 👍 (Like) or 👎 (Dislike) button
3. **For dislikes**: Optionally select a reason or provide custom feedback
4. **Submit**: Your rating is saved automatically

### For Teachers
1. **Access reports** through course navigation menu
2. **View statistics** for all course activities
3. **Analyze feedback** and comments from students
4. **Generate AI insights** for content improvement recommendations
5. **Export data** for further analysis

### For Administrators
1. **Manage feedback reasons** in plugin settings
2. **Access global reports** across all courses
3. **Filter data** by categories, courses, or time periods
4. **Monitor plugin usage** and performance metrics

## Database Schema

### Main Tables

#### `local_datacurso_ratings`
Stores individual ratings from users.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT | Primary key |
| `cmid` | BIGINT | Course module ID (foreign key) |
| `userid` | BIGINT | User ID who rated (foreign key) |
| `rating` | TINYINT | 1 = Like, 0 = Dislike |
| `feedback` | TEXT | Optional comment/feedback |
| `timecreated` | BIGINT | Unix timestamp of creation |
| `timemodified` | BIGINT | Unix timestamp of last modification |

**Indexes:**
- Unique constraint on (`cmid`, `userid`)
- Index on `timecreated` for performance
- Index on `rating` for quick statistics

#### `local_datacurso_ratings_feedback`
Stores predefined feedback options for negative ratings.

| Column | Type | Description |
|--------|------|-------------|
| `id` | BIGINT | Primary key |
| `feedback_text` | TEXT | Predefined feedback option |
| `is_active` | TINYINT | Active status (1 = active, 0 = inactive) |
| `sort_order` | INT | Display order |
| `timecreated` | BIGINT | Unix timestamp of creation |
| `timemodified` | BIGINT | Unix timestamp of last modification |

## Web Services API

The plugin provides REST web services for external integrations:

### Available Services

| Service | Description | Parameters |
|---------|-------------|------------|
| `local_datacurso_ratings_save_rating` | Save a rating for an activity | `cmid`, `rating`, `feedback` |
| `local_datacurso_ratings_get_ratings_report_course` | Get course-level report | `courseid`, `filters` |
| `local_datacurso_ratings_get_ratings_report` | Get global report | `filters`, `pagination` |
| `local_datacurso_ratings_get_activity_comments` | Get comments with pagination | `cmid`, `page`, `limit` |
| `local_datacurso_ratings_get_courses_by_category` | Filter courses by category | `categoryid` |

### API Authentication
All web services require proper Moodle authentication and appropriate capabilities.

## Technical Architecture

### Frontend
- **JavaScript ES6** modules in `amd/src/`
- **No jQuery dependencies** (uses native DOM API)
- **Mustache templates** for UI rendering
- **AJAX** for seamless user experience

### Backend
- **Hook integration**: `\core\hook\output\before_footer_html_generation`
- **REST web services** defined in `db/services.php`
- **Database layer** with optimized queries and indexes
- **Caching** for improved performance

### AI Integration
- Compatible with Moodle's AI subsystem (Moodle 4.4+)
- Pluggable AI providers for sentiment analysis
- Machine learning recommendations

## Reports and Analytics

### Course Reports
- **Activity statistics**: Like/dislike ratios per activity
- **Trend analysis**: Rating patterns over time
- **Comment analysis**: Detailed feedback review
- **AI insights**: Automated recommendations

### Global Reports
- **Institution-wide metrics**: Cross-course comparisons
- **Category analysis**: Performance by course categories
- **User engagement**: Student participation statistics
- **Export capabilities**: Data download in multiple formats

## Troubleshooting

### Common Issues

**Rating buttons not appearing:**
- Verify plugin is enabled globally
- Check user has `local/datacurso_ratings:rate` capability
- Clear caches (Site administration → Development → Purge caches)

**Reports not loading:**
- Verify user has `local/datacurso_ratings:viewreports` capability
- Check JavaScript console for errors
- Verify web services are enabled

**AI features not working:**
- Ensure Moodle 4.4+ with AI subsystem
- Verify AI provider configuration
- Check AI-related capabilities

### Debug Mode
Enable debugging to get detailed error information:
Site administration → Development → Debugging → Developer level

## Privacy and GDPR Compliance

This plugin stores:
- **User ratings** (linked to user IDs)
- **Feedback comments** (potentially containing personal opinions)
- **Usage timestamps**

**Data retention**: Ratings are kept according to Moodle's data retention policies.
**Data export**: User data can be exported through Moodle's privacy API.
**Data deletion**: User ratings are automatically deleted when users are removed from the system.

## Multilingual Support

### Included Languages
- **English** (`lang/en/`)
- **Spanish** (`lang/es/`)

### Adding New Languages
Use Moodle's standard localization system to add translations:
1. Create language directory: `lang/{languagecode}/`
2. Add translation file: `local_datacurso_ratings.php`
3. Follow Moodle string naming conventions

## Version History

### Version 1.0.0
- Initial release
- Basic rating functionality
- Course and global reports
- AI-powered analytics

## Support and Contributing

- **Author**: Developer <developer@datacurso.com>
- **Issues**: Report bugs and feature requests through the Moodle plugins directory
- **Contributing**: Pull requests welcome following Moodle coding standards
- **Documentation**: [Moodle AI API Documentation](https://moodledev.io/docs/apis/ai)

## License

This program is free software: you can redistribute it and/or modify it under the terms of the **GNU General Public License** as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but **WITHOUT ANY WARRANTY**; without even the implied warranty of **MERCHANTABILITY** or **FITNESS FOR A PARTICULAR PURPOSE**. See the [GNU General Public License v3](https://www.gnu.org/licenses/gpl-3.0.html) for more details.