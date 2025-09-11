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

    const openBtn = container.querySelector('[data-action="open-rating"]');
    const panel = container.querySelector('.local-dcr-panel');
    const fbBlock = container.querySelector('.local-dcr-feedback');
    const fbInput = container.querySelector('#local-dcr-feedback-input');
    const sendBtn = container.querySelector('[data-action="send"]');
    const cancelBtn = container.querySelector('[data-action="cancel"]');

    const show = el => { if (el) { el.style.display = 'block'; el.setAttribute('aria-hidden', 'false'); } };
    const hide = el => { if (el) { el.style.display = 'none'; el.setAttribute('aria-hidden', 'true'); } };

    openBtn?.addEventListener('click', () => {
        if (panel.style.display === 'none' || panel.getAttribute('aria-hidden') === 'true') {
            show(panel);
            hide(fbBlock);
            if (fbInput) fbInput.value = ''; 
        } else {
            hide(panel);
            hide(fbBlock);
            if (fbInput) fbInput.value = '';
        }
    });

    container.querySelectorAll('[data-action="rate"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const rating = parseInt(btn.dataset.rating, 10);
            if (rating === 0) {
                show(fbBlock);
                fbInput?.focus();
                // Wait for user to submit feedback.
                sendBtn?.setAttribute('data-rating', '0');
            } else {
                // Positive rating straight away.
                sendRating(cmid, 1, '');
            }
        });
    });

    cancelBtn?.addEventListener('click', () => {
        hide(fbBlock);
        hide(panel);
        if (fbInput) fbInput.value = '';
    });

    sendBtn?.addEventListener('click', () => {
        const rating = parseInt(sendBtn.getAttribute('data-rating') || '0', 10);
        const feedback = (fbInput?.value || '').trim();
        sendRating(cmid, rating, feedback);
    });

    function sendRating(cmid, rating, feedback) {
        Ajax.call([{
            methodname: 'local_datacurso_ratings_save_rating',
            args: {cmid, rating, feedback}
        }])[0]
        .then(async() => {
            const thanks = await getString('ratedthanks', 'local_datacurso_ratings');
            const saved = await getString('ratingsaved', 'local_datacurso_ratings');
            Notification.addNotification({message: saved, type: 'success'});
            hide(panel);
            if (openBtn) {
                openBtn.disabled = true;
                openBtn.textContent = thanks; 
            }
        })
        .catch(Notification.exception);
    }
};

export default {init};
