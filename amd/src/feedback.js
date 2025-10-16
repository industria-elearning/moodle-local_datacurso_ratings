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
 * Feedback module logic.
 *
 * @module     local_datacurso_ratings/feedback
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* eslint-disable */
import Ajax from "core/ajax";
import Notification from "core/notification";

/**
 * Send new feedback via AJAX.
 *
 * @param {string} feedbacktext Feedback text.
 * @param {string} type Feedback type (like/dislike).
 * @returns {Promise<object>} Server response.
 */
function addFeedback(feedbacktext, type) {
    const requests = Ajax.call([{
        methodname: "local_datacurso_ratings_add_feedback",
        args: { feedbacktext, type }
    }]);

    return requests[0];
}

export function init() {
    const container = document.querySelector(".feedback-admin");
    const form = container?.querySelector("form");
    const list = container?.querySelector(".feedback-list");
    const type = container?.dataset.type;

    if (!form || !list || !type) {
        return;
    }

    // Add new feedback
    form.addEventListener("submit", (e) => {
        e.preventDefault();
        const input = form.querySelector("input[name='feedbacktext']");
        if (!input.value.trim()) {
            return;
        }

        addFeedback(input.value.trim(), type)
            .then((resp) => {
                Notification.addNotification({ message: resp.message, type: "success" });

                // Dynamically add the new feedback to the list
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

    // Delete feedback
    list.addEventListener("click", (e) => {
        if (e.target.classList.contains("btn-delete")) {
            const id = e.target.dataset.id;

            const requests = Ajax.call([{
                methodname: "local_datacurso_ratings_delete_feedback",
                args: { id }
            }]);

            requests[0]
                .then((resp) => {
                    Notification.addNotification({ message: resp.message, type: "success" });
                    e.target.closest("li").remove();
                })
                .catch(Notification.exception);
        }
    });
}
