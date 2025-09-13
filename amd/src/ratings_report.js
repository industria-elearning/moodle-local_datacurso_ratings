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
 * @module     local_datacurso_ratings/get_ratings_report
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* eslint-disable */
import Ajax from 'core/ajax';

export const init = () => {
    const tbody = document.querySelector('#local-dcr-report-body');
    const spinner = document.querySelector('.report-spinner');
    const emptyMsg = document.querySelector('.report-empty');
    const filterInput = document.querySelector('.report-filter');
    const exportBtn = document.querySelector('.btn-export');

    if (!tbody) {
        return;
    }

    // Mostrar spinner
    spinner.style.display = 'inline-block';

    Ajax.call([{
        methodname: 'local_datacurso_ratings_get_ratings_report',
        args: {}
    }])[0]
        .then(data => {
            spinner.style.display = 'none';
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                emptyMsg.style.display = 'block';
                return;
            }

            emptyMsg.style.display = 'none';

            data.forEach(row => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${row.course}</td>
                    <td>${row.activity}</td>
                    <td>${row.likes}</td>
                    <td>${row.dislikes}</td>
                    <td>${row.approvalpercent}%</td>
                    <td>${row.comments.join('</br>')}</td>
                `;
                tbody.appendChild(tr);
            });

            // --- Filtro ---
            filterInput.addEventListener('input', () => {
                const term = filterInput.value.toLowerCase();
                tbody.querySelectorAll('tr').forEach(tr => {
                    const text = tr.textContent.toLowerCase();
                    tr.style.display = text.includes(term) ? '' : 'none';
                });
            });
        })
        .catch(err => {
            spinner.style.display = 'none';
            emptyMsg.style.display = 'block';
            console.error("Error al traer data: ",err);
        });
};