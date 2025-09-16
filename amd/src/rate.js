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

    // Eventos para rate 👍 y 👎
    container.querySelectorAll('[data-action="rate"]').forEach(btn => {
        btn.addEventListener('click', () => {
            const rating = parseInt(btn.dataset.rating, 10);
            
            if (rating === 0) {
                // Dislike → mostrar opciones de dislike
                show(fbBlock);
                show(dislikeOptions);
                hide(likeOptions);
                sendBtn?.setAttribute('data-rating', '0');
            } else {
                // Like → mostrar opciones de like
                show(fbBlock);
                show(likeOptions);
                hide(dislikeOptions);
                sendBtn?.setAttribute('data-rating', '1');
            }
            
            // Ocultar textarea al cambiar de rating
            hide(fbTextareaWrap);
        });
    });

    // Mostrar textarea específico si selecciona "Otras"
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

    // Cancelar feedback
    cancelBtn?.addEventListener('click', () => {
        hide(fbBlock);
        hide(fbTextareaWrap);
        
        // Limpiar todos los campos
        if (fbInput) fbInput.value = '';
        
        // Desmarcar todos los radios
        container.querySelectorAll('input[name="feedback_choice"]').forEach(r => r.checked = false);
        
        // Remover el data-rating del botón send
        sendBtn?.removeAttribute('data-rating');
    });

    // Enviar feedback
    sendBtn?.addEventListener('click', () => {
        const rating = parseInt(sendBtn.getAttribute('data-rating') || '0', 10);
        const selected = container.querySelector('input[name="feedback_choice"]:checked');

        let feedback = '';
        
        // Para ambos casos (like y dislike)
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

            // Deshabilitar botones después de enviar
            if (container) {
                container.querySelectorAll('[data-action="rate"]').forEach(btn => btn.disabled = true);
                container.querySelectorAll('input[name="feedback_choice"]').forEach(r => r.disabled = true);
                if (sendBtn) sendBtn.disabled = true;
                if (cancelBtn) cancelBtn.disabled = true;
                if (fbInput) fbInput.disabled = true;
            }

            // Ocultar bloques de feedback
            hide(fbBlock);
            hide(fbTextareaWrap);
        })
        .catch(Notification.exception);
    }
};

export default {init};