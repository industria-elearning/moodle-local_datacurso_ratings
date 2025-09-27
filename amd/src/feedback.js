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
 * TODO describe module feedback
 *
 * @module     local_datacurso_ratings/feedback
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// This file is part of Moodle - http://moodle.org/
//
// @module     local_datacurso_ratings/feedback
// @copyright  2025 Industria Elearning
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

// This file is part of Moodle - http://moodle.org/
//
// @module     local_datacurso_ratings/feedback
// @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

/* eslint-disable */
import Ajax from "core/ajax";
import Notification from "core/notification";

/**
 * Enviar nuevo feedback vía AJAX.
 *
 * @param {string} feedbacktext Texto del feedback que se va a agregar.
 * @returns {Promise<object>} Respuesta del servidor.
 */
function addFeedback(feedbacktext) {
    const requests = Ajax.call([{
        methodname: "local_datacurso_ratings_add_feedback",
        args: { feedbacktext }
    }]);

    return requests[0];
}

/**
 * Eliminar feedback vía AJAX.
 *
 * @param {number|string} id ID del feedback que se quiere eliminar.
 * @returns {Promise<object>} Respuesta del servidor.
 */
function deleteFeedback(id) {
    const requests = Ajax.call([{
        methodname: "local_datacurso_ratings_delete_feedback",
        args: { id }
    }]);

    return requests[0];
}

/**
 * Inicializar listeners en el panel admin.
 *
 * @returns {void}
 */
export function init() {
    const form = document.querySelector(".feedback-admin form");
    const list = document.querySelector(".feedback-list");

    if (!form || !list) {
        return;
    }

    // Agregar nuevo feedback
    form.addEventListener("submit", (e) => {
        e.preventDefault();
        const input = form.querySelector("input[name='feedbacktext']");
        if (!input.value.trim()) {
            return;
        }

        addFeedback(input.value.trim())
            .then((resp) => {
                Notification.addNotification({ message: resp.message, type: "success" });

                // Agregar dinámicamente a la lista
                const li = document.createElement("li");
                li.className = "feedback-item list-group-item d-flex justify-content-between align-items-center";
                li.innerHTML = `
                    <span class="feedback-text">${input.value}</span>
                    <button type="button" class="btn btn-outline-danger btn-sm btn-delete" data-id="${resp.id}">X</button>
                `;
                list.prepend(li);
                input.value = "";
            })
            .catch(Notification.exception);
    });

    // Eliminar feedback
    list.addEventListener("click", (e) => {
        if (e.target.classList.contains("btn-delete")) {
            const id = e.target.dataset.id;

            deleteFeedback(id)
                .then((resp) => {
                    Notification.addNotification({ message: resp.message, type: "success" });
                    e.target.closest("li").remove();
                })
                .catch(Notification.exception);
        }
    });
}