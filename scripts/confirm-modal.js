/**
 * Modale de confirmation générique remplaçant window.confirm() (natif, hors charte) pour
 * les actions destructrices (ex : supprimer une publication). Un formulaire porte
 * class="confirm-form" et data-confirm-message="..." ; sa soumission est interceptée une
 * fois, puis relancée automatiquement si l'utilisateur confirme.
 */
(function () {
    'use strict';

    let pendingForm = null;

    window.closeConfirmModal = function () {
        const modal = document.getElementById('confirm-modal');
        if (modal) modal.classList.add('hidden');
        pendingForm = null;
    };

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('confirm-modal');
        if (!modal) return;

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeConfirmModal();
        });

        document.getElementById('confirm-modal-ok').addEventListener('click', function () {
            if (pendingForm) {
                pendingForm.dataset.confirmed = 'true';
                pendingForm.submit();
            }
            closeConfirmModal();
        });

        document.querySelectorAll('form.confirm-form').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (form.dataset.confirmed === 'true') return;
                e.preventDefault();
                document.getElementById('confirm-modal-message').textContent =
                    form.dataset.confirmMessage || 'Confirmer cette action ?';
                pendingForm = form;
                modal.classList.remove('hidden');
            });
        });
    });
})();
