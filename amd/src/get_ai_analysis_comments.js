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
 * AI Analysis Comments Module.
 *
 * @module     local_datacurso_ratings/get_ai_analysis_comments
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* eslint-disable */
import Ajax from 'core/ajax';
import Notification from 'core/notification';
import {get_string as getString} from 'core/str';

export const init = () => {
    document.addEventListener('click', async function(e) {
        if (e.target.classList.contains('btn-generate-ai-activitie') || e.target.closest('.btn-generate-ai-activitie')) {
            e.preventDefault();

            const button = e.target.closest('.btn-generate-ai-activitie');
            const cmid = button.getAttribute('data-cmid');
            const container = button.closest('.mb-3');
            const resultContainer = container.querySelector('.ai-analysis-result');

            if (!cmid || !resultContainer) {
                console.warn('Missing cmid or result container');
                return;
            }

            // Get localized string for "Generating analysis..."
            const generatingText = await getString('generatecommentaiprocess', 'local_datacurso_ratings');

            resultContainer.innerHTML = `<div class="text-muted">
                <i class="fa fa-spinner fa-spin"></i> ${generatingText}
            </div>`;

            Ajax.call([{
                methodname: 'local_datacurso_ratings_get_ai_analysis_comments',
                args: { cmid: parseInt(cmid, 10) }
            }])[0]
            .then(async data => {
                // Get localized string for analysis result wrapper
                const analysisTitle = await getString('analysisresult', 'local_datacurso_ratings');

                resultContainer.innerHTML = `
                    <div class="alert alert-info p-2 mb-2">
                        <strong>${analysisTitle}</strong><br>
                        ${data.ai_analysis_comment}
                    </div>`;
            })
            .catch((e)=> {
                resultContainer.innerHTML = `
                    <div class="alert alert-danger p-2 mb-2">
                        <i class="fa fa-exclamation-triangle"></i> ${e.message}
                    </div>`;
            });
        }
    });
};

export default {init};
