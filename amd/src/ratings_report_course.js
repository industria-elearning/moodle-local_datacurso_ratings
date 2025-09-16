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
 * TODO describe module ratings_report_course
 *
 * @module     local_datacurso_ratings/ratings_report_course
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* eslint-disable */
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import Notification from 'core/notification';
import { get_string as getString } from 'core/str';

/**
 * Initialize the ratings report for a course
 * @param {Number} courseid
 */
export const init = (courseid) => {
    const container = document.querySelector('#ratings-report-container');
    if (!container) {
        return;
    }

    // Show loading state
    showLoading(container);

    // Call the web service
    Ajax.call([{
        methodname: 'local_datacurso_ratings_get_ratings_report_course',
        args: { courseid: courseid }
    }])[0]
        .then((data) => {
            return processReportData(data, courseid);
        })
        .then((templateData) => {
            return Templates.render('local_datacurso_ratings/report_ratings_course', templateData);
        })
        .then((html, js) => {
            // Replace loading with actual content
            container.innerHTML = html;
            Templates.runTemplateJS(js);

            // Initialize any additional functionality
            initTableFeatures();
        })
        .catch((error) => {
            console.error('Error loading ratings report:', error);
            showError(container, error);
        });
};

/**
 * Process the raw data from web service
 * @param {Array} data Raw data from web service
 * @param {Number} courseid Course ID
 * @returns {Object} Processed data for template
 */
function processReportData(data, courseid) {
    // Calculate totals
    let totalLikes = 0;
    let totalDislikes = 0;
    let activitiesWithRatings = 0;

    const processedActivities = data.map(activity => {
        totalLikes += activity.likes;
        totalDislikes += activity.dislikes;

        if (activity.likes + activity.dislikes > 0) {
            activitiesWithRatings++;
        }

        return {
            ...activity,
            total_ratings: activity.likes + activity.dislikes,
            has_ratings: (activity.likes + activity.dislikes) > 0,
            has_comments: activity.comentarios && activity.comentarios.trim() !== '',
            satisfaction_class: getSatisfactionClass(activity.porcentaje),
            formatted_percentage: activity.porcentaje ? activity.porcentaje.toFixed(1) + '%' : '0%',
            activityurl: M.cfg.wwwroot + '/mod/' + activity.modulo + '/view.php?id=' + activity.cmid
        };
    });

    const totalRatings = totalLikes + totalDislikes;
    const overallSatisfaction = totalRatings > 0 ? ((totalLikes / totalRatings) * 100) : 0;

    return {
        courseid: courseid,
        activities: processedActivities,
        has_data: processedActivities.length > 0,
        summary: {
            total_activities: processedActivities.length,
            activities_with_ratings: activitiesWithRatings,
            total_ratings: totalRatings,
            total_likes: totalLikes,
            total_dislikes: totalDislikes,
            overall_satisfaction: overallSatisfaction.toFixed(1),
            satisfaction_class: getSatisfactionClass(overallSatisfaction)
        }
    };
}

/**
 * Get CSS class based on satisfaction percentage
 * @param {Number} percentage
 * @returns {String}
 */
function getSatisfactionClass(percentage) {
    if (percentage >= 80) return 'success';
    if (percentage >= 60) return 'warning';
    return 'danger';
}

/**
 * Show loading state
 * @param {Element} container
 */
function showLoading(container) {
    container.innerHTML = `
        <div class="text-center p-4">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
            <p class="mt-2">Cargando reporte de calificaciones...</p>
        </div>
    `;
}

/**
 * Show error message
 * @param {Element} container
 * @param {Object} error
 */
function showError(container, error) {
    container.innerHTML = `
        <div class="alert alert-danger">
            <h4>Error al cargar el reporte</h4>
            <p>No se pudo cargar la información del reporte. Por favor, intente nuevamente.</p>
            <small>Error: ${error.message || 'Error desconocido'}</small>
        </div>
    `;
}

/**
 * Initialize additional table features (sorting, filtering, etc.)
 */
function initTableFeatures() {
    // Add click handlers for expandable comments
    document.querySelectorAll('.expand-comments').forEach(button => {
        button.addEventListener('click', (e) => {
            const target = e.target.getAttribute('data-target');
            const commentsDiv = document.querySelector(target);

            if (commentsDiv.style.display === 'none' || !commentsDiv.style.display) {
                commentsDiv.style.display = 'block';
                e.target.textContent = 'Ocultar comentarios';
            } else {
                commentsDiv.style.display = 'none';
                e.target.textContent = 'Ver comentarios';
            }
        });
    });

    // Add sorting functionality (optional)
    initTableSorting();
}

/**
 * Initialize table sorting functionality
 */
function initTableSorting() {
    const headers = document.querySelectorAll('th[data-sort]');
    headers.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', () => {
            const sortBy = header.getAttribute('data-sort');
            sortTable(sortBy);
        });
    });
}

/**
 * Sort table by column
 * @param {String} column
 */
function sortTable(column) {
    // Implementation for table sorting
    console.log('Sorting by:', column);
    // You can implement actual sorting logic here if needed
}

export default { init };