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
 * TODO describe module rate
 *
 * @module     local_datacurso_ratings/rate
 * @copyright  2025 Industria Elearning <info@industriaelearning.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* eslint-disable */
import Ajax from 'core/ajax';
import Notification from 'core/notification';
import {get_string as getString} from 'core/str';

/**
 * Initialise rating widget for the given cmid.
 * @param {Number} cmid
 */
export const init = (cmid) => {
    const container = document.querySelector(`.local-dcr-rate[data-cmid="${cmid}"]`);
    if (!container) {
        return;
    }

    const fbBlock = container.querySelector('.local-dcr-feedback');
    const fbInput = container.querySelector('#local-dcr-feedback-input');
    const fbTextareaWrap = container.querySelector('#local-dcr-feedback-textarea');
    const sendBtn = container.querySelector('[data-action="send"]');
    const cancelBtn = container.querySelector('[data-action="cancel"]');
    const likeOptions = container.querySelector('.like-options');
    const dislikeOptions = container.querySelector('.dislike-options');

    const show = el => { 
        if (el) { 
            el.style.display = 'block'; 
            el.setAttribute('aria-hidden', 'false'); 
        } 
    };
    const hide = el => { 
        if (el) { 
            el.style.display = 'none'; 
            el.setAttribute('aria-hidden', 'true'); 
        } 
    };

    // Events of rate 👍 y 👎
    container.querySelectorAll('[data-action="rate"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const rating = parseInt(btn.dataset.rating, 10);
            
            if (rating === 0) {
                // Dislike → show dislike options
                show(fbBlock);
                show(dislikeOptions);
                hide(likeOptions);
                sendBtn?.setAttribute('data-rating', '0');
            } else {
                // Like → show like options
                show(fbBlock);
                show(likeOptions);
                hide(dislikeOptions);
                sendBtn?.setAttribute('data-rating', '1');
            }
            
            // Hide textarea to change rating
            hide(fbTextareaWrap);
        });
    });

    // Show textarea especific if "Others"
    container.querySelectorAll('input[name="feedback_choice"]').forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.value === 'other' && radio.checked) {
                show(fbTextareaWrap);
                fbInput?.focus();
            } else if (radio.checked) {
                hide(fbTextareaWrap);
            }
        });
    });

    // Cancelate feedback
    cancelBtn?.addEventListener('click', () => {
        hide(fbBlock);
        hide(fbTextareaWrap);
        
        // Clean all fields
        if (fbInput) fbInput.value = '';
        
        // Discheck in options
        container.querySelectorAll('input[name="feedback_choice"]').forEach(r => r.checked = false);
        
        sendBtn?.removeAttribute('data-rating');
    });

    // Send feedback
    sendBtn?.addEventListener('click', () => {
        const rating = parseInt(sendBtn.getAttribute('data-rating') || '0', 10);
        const selected = container.querySelector('input[name="feedback_choice"]:checked');

        let feedback = '';
        
        // (like y dislike)
        if (selected) {
            if (selected.value === 'other') {
                feedback = (fbInput?.value || '').trim();
            } else {
                feedback = selected.value;
            }
        }

        sendRating(cmid, rating, feedback);
    });

    function sendRating(cmid, rating, feedback) {
        Ajax.call([{
            methodname: 'local_datacurso_ratings_save_rating',
            args: {cmid, rating, feedback}
        }])[0]
        .then(async() => {
            const saved = await getString('ratingsaved', 'local_datacurso_ratings');
            Notification.addNotification({message: saved, type: 'success'});

            // Disabled buttons after of send
            if (container) {
                container.querySelectorAll('[data-action="rate"]').forEach(btn => btn.disabled = true);
                container.querySelectorAll('input[name="feedback_choice"]').forEach(r => r.disabled = true);
                if (sendBtn) sendBtn.disabled = true;
                if (cancelBtn) cancelBtn.disabled = true;
                if (fbInput) fbInput.disabled = true;
            }

            // Hide blocks of feedback
            hide(fbBlock);
            hide(fbTextareaWrap);
        })
        .catch(Notification.exception);
    }
};

export default {init};