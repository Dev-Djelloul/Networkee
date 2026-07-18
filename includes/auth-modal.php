<?php
/**
 * Modale de connexion / inscription en overlay, réutilisée sur toute page où une action
 * protégée (postuler, publier, liker, commenter...) peut être déclenchée sans être connecté.
 * Nécessite scripts/auth-modal.js et helpers.php (pour renderIcon).
 * Chaque page définit son propre gestionnaire de "session_storage intent" après rechargement.
 */
?>
<div id="login-modal" class="modal-overlay hidden">
    <div class="modal-card">
        <button type="button" class="modal-close" aria-label="Fermer" onclick="closeLoginModal()">
            <?php echo renderIcon('close', 20); ?>
        </button>

        <!-- Vue connexion -->
        <div id="modal-view-login">
            <h2 style="margin-top: 0;">Connexion</h2>
            <p style="color: var(--text-muted); margin-top: -0.5rem;">Connecte-toi pour continuer.</p>
            <div id="login-modal-message"></div>
            <form id="loginModalForm">
                <div class="form-group">
                    <label class="form-label" for="modal-email">Email</label>
                    <input type="email" id="modal-email" name="email" class="form-input" required placeholder="ton@email.com">
                </div>
                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label class="form-label" for="modal-password" style="margin-bottom: 0;">Mot de passe</label>
                        <a href="#" onclick="switchModalView('forgot'); return false;" style="font-size: 0.8125rem; color: var(--text-muted);">Mot de passe oublié ?</a>
                    </div>
                    <input type="password" id="modal-password" name="password" class="form-input" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Je me connecte</button>
            </form>
            <p style="margin-top: 1.25rem; font-size: 0.875rem; color: var(--text-muted);">
                Pas encore de compte ? <a href="#" onclick="switchModalView('register'); return false;" style="color: var(--accent); font-weight: 500;">Inscris-toi</a>
            </p>
        </div>

        <!-- Vue inscription -->
        <div id="modal-view-register" style="display: none;">
            <h2 style="margin-top: 0;">Créer un compte</h2>
            <p style="color: var(--text-muted); margin-top: -0.5rem;">Rejoins Networkee en quelques secondes 🌟</p>
            <div id="register-modal-message"></div>
            <form id="registerModalForm">
                <div class="form-group">
                    <label class="form-label" for="modal-reg-username">Nom d'utilisateur</label>
                    <input type="text" id="modal-reg-username" name="username" class="form-input" required placeholder="Ton pseudo">
                </div>
                <div class="form-group">
                    <label class="form-label" for="modal-reg-email">Email</label>
                    <input type="email" id="modal-reg-email" name="email" class="form-input" required placeholder="ton@email.com">
                </div>
                <div class="form-group">
                    <label class="form-label" for="modal-reg-password">Mot de passe</label>
                    <input type="password" id="modal-reg-password" name="password" class="form-input" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label class="form-label" for="modal-reg-confirm">Confirme ton mot de passe</label>
                    <input type="password" id="modal-reg-confirm" name="confirm_password" class="form-input" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">S'inscrire</button>
            </form>
            <p style="margin-top: 1.25rem; font-size: 0.875rem; color: var(--text-muted);">
                Déjà un compte ? <a href="#" onclick="switchModalView('login'); return false;" style="color: var(--accent); font-weight: 500;">Connecte-toi</a>
            </p>
        </div>

        <!-- Vue mot de passe oublié -->
        <div id="modal-view-forgot" style="display: none;">
            <h2 style="margin-top: 0;">Mot de passe oublié</h2>
            <p style="color: var(--text-muted); margin-top: -0.5rem;">Indique ton email, on t'envoie un lien de réinitialisation.</p>
            <div id="forgot-modal-message"></div>
            <form id="forgotModalForm">
                <div class="form-group">
                    <label class="form-label" for="modal-forgot-email">Email</label>
                    <input type="email" id="modal-forgot-email" name="email" class="form-input" required placeholder="ton@email.com">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Envoyer le lien</button>
            </form>
            <p style="margin-top: 1.25rem; font-size: 0.875rem; color: var(--text-muted);">
                <a href="#" onclick="switchModalView('login'); return false;" style="color: var(--accent); font-weight: 500;">← Retour à la connexion</a>
            </p>
        </div>
    </div>
</div>
