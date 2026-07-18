<?php
/**
 * Modale de confirmation générique (remplace window.confirm(), pas à la charte du site).
 * Nécessite scripts/confirm-modal.js. Les formulaires à confirmer portent
 * class="confirm-form" et data-confirm-message="...".
 */
?>
<div id="confirm-modal" class="modal-overlay hidden">
    <div class="modal-card confirm-modal-card">
        <h2 id="confirm-modal-title" style="margin-top: 0;">Confirmer</h2>
        <p id="confirm-modal-message" style="color: var(--text-soft);"></p>
        <div class="confirm-modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Annuler</button>
            <button type="button" id="confirm-modal-ok" class="btn btn-danger">Confirmer</button>
        </div>
    </div>
</div>
