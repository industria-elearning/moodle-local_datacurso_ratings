/* eslint-disable */
import Ajax from 'core/ajax';
import Notification from 'core/notification';
import {get_string as getString} from 'core/str';

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
