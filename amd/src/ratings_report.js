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
 * TODO describe module get_ratings_report
 *
 * @module     local_datacurso_ratings/ratings_report
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* eslint-disable */
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import Notification from 'core/notification';
import {get_string as getString} from 'core/str';

/**
 * Initialize the general ratings report
 */
export const init = () => {
    const container = document.querySelector('#general-ratings-report-container');
    if (!container) {
        return;
    }

    // Show loading state
    showLoading(container);

    // Call the web service
    Ajax.call([{
        methodname: 'local_datacurso_ratings_get_ratings_report',
        args: {}
    }])[0]
    .then((data) => {
        return processGeneralReportData(data);
    })
    .then((templateData) => {
        return Templates.render('local_datacurso_ratings/ratings_report_page', templateData);
    })
    .then((html, js) => {
        // Replace loading with actual content
        container.innerHTML = html;
        Templates.runTemplateJS(js);
        
        // Initialize additional functionality
        initGeneralTableFeatures();
    })
    .catch((error) => {
        console.error('Error loading general ratings report:', error);
        showError(container, error);
    });
};

/**
 * Process the raw data from web service
 * @param {Array} data Raw data from web service
 * @returns {Object} Processed data for template
 */
function processGeneralReportData(data) {
    // Group data by course
    const courseGroups = {};
    let totalLikes = 0;
    let totalDislikes = 0;
    let totalActivities = 0;

    data.forEach(activity => {
        const courseName = activity.course;
        
        if (!courseGroups[courseName]) {
            courseGroups[courseName] = {
                courseName: courseName,
                activities: [],
                courseLikes: 0,
                courseDislikes: 0,
                courseActivities: 0
            };
        }

        // Process individual activity
        const processedActivity = {
            ...activity,
            total_ratings: activity.likes + activity.dislikes,
            has_ratings: (activity.likes + activity.dislikes) > 0,
            has_comments: activity.comments && activity.comments.length > 0,
            satisfaction_class: getSatisfactionClass(activity.approvalpercent),
            formatted_percentage: activity.approvalpercent ? activity.approvalpercent.toFixed(1) + '%' : '0%',
            comments_count: activity.comments ? activity.comments.length : 0,
            comments_text: activity.comments ? activity.comments.join(' / ') : ''
        };

        courseGroups[courseName].activities.push(processedActivity);
        courseGroups[courseName].courseLikes += activity.likes;
        courseGroups[courseName].courseDislikes += activity.dislikes;
        courseGroups[courseName].courseActivities += 1;

        // Global totals
        totalLikes += activity.likes;
        totalDislikes += activity.dislikes;
        totalActivities += 1;
    });

    // Calculate course-level statistics
    Object.keys(courseGroups).forEach(courseName => {
        const course = courseGroups[courseName];
        const courseTotal = course.courseLikes + course.courseDislikes;
        course.courseSatisfaction = courseTotal > 0 ? 
            ((course.courseLikes / courseTotal) * 100).toFixed(1) : '0';
        course.courseSatisfactionClass = getSatisfactionClass(parseFloat(course.courseSatisfaction));
        course.courseTotal = courseTotal;
        
        // Sort activities by satisfaction percentage (highest first)
        course.activities.sort((a, b) => b.approvalpercent - a.approvalpercent);
    });

    // Convert to array and sort courses by total ratings
    const coursesArray = Object.values(courseGroups).sort((a, b) => b.courseTotal - a.courseTotal);

    const totalRatings = totalLikes + totalDislikes;
    const overallSatisfaction = totalRatings > 0 ? ((totalLikes / totalRatings) * 100) : 0;

    return {
        courses: coursesArray,
        has_data: coursesArray.length > 0,
        summary: {
            total_courses: coursesArray.length,
            total_activities: totalActivities,
            total_ratings: totalRatings,
            total_likes: totalLikes,
            total_dislikes: totalDislikes,
            overall_satisfaction: overallSatisfaction.toFixed(1),
            satisfaction_class: getSatisfactionClass(overallSatisfaction),
            activities_with_ratings: data.filter(a => (a.likes + a.dislikes) > 0).length
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
    if (percentage >= 40) return 'info';
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
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2">Cargando reporte general de calificaciones...</p>
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
            <p>No se pudo cargar la información del reporte general. Por favor, intente nuevamente.</p>
            <small>Error: ${error.message || 'Error desconocido'}</small>
        </div>
    `;
}

/**
 * Initialize additional table features
 */
function initGeneralTableFeatures() {
    // Course collapse/expand functionality
    document.querySelectorAll('.course-toggle').forEach(button => {
        button.addEventListener('click', (e) => {
            const target = e.target.getAttribute('data-target');
            const courseContent = document.querySelector(target);
            const icon = e.target.querySelector('i');
            
            if (courseContent.classList.contains('show')) {
                courseContent.classList.remove('show');
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-right');
            } else {
                courseContent.classList.add('show');
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-chevron-down');
            }
        });
    });

    // Comments expand functionality
    document.querySelectorAll('.expand-general-comments').forEach(button => {
        button.addEventListener('click', (e) => {
            const target = e.target.getAttribute('data-target');
            const commentsDiv = document.querySelector(target);
            
            if (commentsDiv.style.display === 'none' || !commentsDiv.style.display) {
                commentsDiv.style.display = 'block';
                e.target.textContent = 'Ocultar comentarios';
            } else {
                commentsDiv.style.display = 'none';
                e.target.textContent = 'Ver comentarios (' + e.target.getAttribute('data-count') + ')';
            }
        });
    });

    // Filter functionality
    initFilterFeatures();
}

/**
 * Initialize filter functionality
 */
function initFilterFeatures() {
    const searchInput = document.querySelector('#activity-search');
    const courseFilter = document.querySelector('#course-filter');
    
    if (searchInput) {
        searchInput.addEventListener('input', filterActivities);
    }
    
    if (courseFilter) {
        courseFilter.addEventListener('change', filterActivities);
    }
}

/**
 * Filter activities based on search and course selection
 */
function filterActivities() {
    const searchTerm = document.querySelector('#activity-search')?.value.toLowerCase() || '';
    const selectedCourse = document.querySelector('#course-filter')?.value || '';
    
    document.querySelectorAll('.course-section').forEach(section => {
        const courseName = section.getAttribute('data-course');
        let courseVisible = false;
        
        // Check if course matches filter
        if (selectedCourse === '' || courseName === selectedCourse) {
            section.querySelectorAll('.activity-row').forEach(row => {
                const activityName = row.getAttribute('data-activity').toLowerCase();
                const matchesSearch = searchTerm === '' || activityName.includes(searchTerm);
                
                if (matchesSearch) {
                    row.style.display = '';
                    courseVisible = true;
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Show/hide entire course section
        section.style.display = courseVisible ? '' : 'none';
    });
}

export default {init};