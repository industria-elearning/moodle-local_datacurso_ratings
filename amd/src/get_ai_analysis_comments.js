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
 * TODO describe module get_ai_analysis_comments
 *
 * @module     local_datacurso_ratings/get_ai_analysis_comments
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* eslint-disable */
import Ajax from 'core/ajax';
import Notification from 'core/notification';

export const init = () => {
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-generate-ai') || e.target.closest('.btn-generate-ai')) {
            e.preventDefault();

            const button = e.target.closest('.btn-generate-ai');
            const cmid = button.getAttribute('data-cmid');
            // Ahora buscamos dentro del padre más cercano (el div .mb-3)
            const container = button.closest('.mb-3');
            const resultContainer = container.querySelector('.ai-analysis-result');

            if (!cmid || !resultContainer) {
                console.warn('No se encontró cmid o contenedor para resultado');
                return;
            }

            resultContainer.innerHTML = `<div class="text-muted">
                <i class="fa fa-spinner fa-spin"></i> Generando análisis...
            </div>`;

            Ajax.call([{
                methodname: 'local_datacurso_ratings_get_ai_analysis_comments',
                args: { cmid: parseInt(cmid, 10) }
            }])[0]
            .then(data => {
                resultContainer.innerHTML = `
                    <div class="alert alert-info p-2 mb-2">
                        ${data.ai_analysis_comment}
                    </div>`;
            })
            .catch(Notification.exception);
        }
    });
};

export default {init};
