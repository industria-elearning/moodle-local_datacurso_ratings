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
 * Handles AI analysis for the global ratings report.
 *
 * @module     local_datacurso_ratings/get_ai_analysis_global
 * @copyright  2025 Industria Elearning
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* eslint-disable */
import Ajax from 'core/ajax';
import Notification from 'core/notification';
import {get_string as getString} from 'core/str';

export const init = () => {
    document.addEventListener('click', async (e) => {
        if (!e.target.closest('.btn-generate-ai')) {
            return;
        }

        const button = e.target.closest('.btn-generate-ai');
        const resultContainer = document.querySelector('.ai-analysis-result-global');

        // Get localized strings
        const generatingText = await getString('generatecommentaiprocess', 'local_datacurso_ratings');
        const analysisTitle = await getString('analysisresult', 'local_datacurso_ratings');
        const generateButton = await getString('generatecommentaiglobal', 'local_datacurso_ratings');
        const generateError = await getString('generatecommentaierror', 'local_datacurso_ratings');

        // Loading state
        button.disabled = true;
        button.innerHTML = `<i class="fa fa-spinner fa-spin"></i> ${generatingText}`;
        resultContainer.innerHTML = '';

        Ajax.call([{
            methodname: 'local_datacurso_ratings_get_ai_analysis_global',
            args: {},
        }])[0]
        .done((response) => {
            if (response && response.analysis) {
                resultContainer.innerHTML = `
                    <div class="alert alert-info">
                        <h6>${analysisTitle}</h6>
                        <p>${response.analysis}</p>
                    </div>`;
            } else {
                resultContainer.innerHTML = `<div class="alert alert-warning">${generateError}</div>`;
            }
        })
        .fail(Notification.exception)
        .always(() => {
            button.disabled = false;
            button.innerHTML = `${generateButton} ✨`;
        });
    });
};

export default {init};
