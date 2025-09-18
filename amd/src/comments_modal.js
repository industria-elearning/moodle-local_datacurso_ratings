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
 * TODO describe module comments_modal
 *
 * @module     local_datacurso_ratings/comments_modal
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* eslint-disable */
import Ajax from 'core/ajax';
import Templates from 'core/templates';
import Notification from 'core/notification';
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';
import {get_string as getString} from 'core/str';

/**
 * Initialize comments modal functionality
 */
export const init = () => {
    // Add click handlers to all "View Comments" buttons
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-comments-modal') || 
            e.target.closest('.view-comments-modal')) {
            
            e.preventDefault();
            const button = e.target.classList.contains('view-comments-modal') ? 
                          e.target : e.target.closest('.view-comments-modal');
            
            const cmid = button.getAttribute('data-cmid');
            const activityName = button.getAttribute('data-activity');
            
            if (cmid) {
                openCommentsModal(cmid, activityName);
            }
        }
    });
};

/**
 * Open the comments modal for a specific activity
 * @param {Number} cmid Course module ID
 * @param {String} activityName Activity name
 */
function openCommentsModal(cmid, activityName) {
    ModalFactory.create({
        type: ModalFactory.types.DEFAULT,
        title: 'Comentarios: ' + activityName,
        body: '<div class="text-center p-4"><div class="spinner-border"></div><p>Cargando comentarios...</p></div>',
        large: true
    }).then(function(modal) {
        modal.show();
        
        // Load comments data
        loadCommentsData(cmid, 0, '', modal);
        
        // Handle modal events
        modal.getRoot().on(ModalEvents.hidden, function() {
            modal.destroy();
        });
        
        return modal;
    });
}

/**
 * Load comments data for the modal
 * @param {Number} cmid Course module ID
 * @param {Number} page Page number
 * @param {String} search Search text
 * @param {Object} modal Modal instance
 */
function loadCommentsData(cmid, page = 0, search = '', modal) {
    Ajax.call([{
        methodname: 'local_datacurso_ratings_get_activity_comments',
        args: {
            cmid: cmid,
            page: page,
            perpage: 20,
            search: search
        }
    }])[0]
    .then((data) => {
        return Templates.render('local_datacurso_ratings/comments_modal_content', {
            ...data,
            search_term: search
        });
    })
    .then((html, js) => {
        modal.setBody(html);
        Templates.runTemplateJS(js);
        
        // Initialize modal functionality
        initModalFeatures(cmid, modal);
    })
    .catch((error) => {
        console.error('Error loading comments:', error);
        modal.setBody('<div class="alert alert-danger">Error al cargar los comentarios. Intente nuevamente.</div>');
    });
}

/**
 * Initialize modal features (search, pagination)
 * @param {Number} cmid Course module ID
 * @param {Object} modal Modal instance
 */
function initModalFeatures(cmid, modal) {
    const modalBody = modal.getBody()[0];
    
    // Search functionality
    const searchInput = modalBody.querySelector('#comments-search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchTerm = this.value.trim();
                loadCommentsData(cmid, 0, searchTerm, modal);
            }, 500); // Debounce search
        });
    }
    
    // Clear search functionality
    const clearSearch = modalBody.querySelector('#clear-search');
    if (clearSearch) {
        clearSearch.addEventListener('click', function() {
            searchInput.value = '';
            loadCommentsData(cmid, 0, '', modal);
        });
    }
    
    // Pagination functionality
    modalBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('comments-pagination')) {
            e.preventDefault();
            const page = parseInt(e.target.getAttribute('data-page'));
            const searchTerm = searchInput ? searchInput.value.trim() : '';
            loadCommentsData(cmid, page, searchTerm, modal);
        }
    });
    
    // Keywords click functionality
    modalBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('keyword-filter')) {
            e.preventDefault();
            const keyword = e.target.textContent.trim();
            if (searchInput) {
                searchInput.value = keyword;
                loadCommentsData(cmid, 0, keyword, modal);
            }
        }
    });
}

export default {init};