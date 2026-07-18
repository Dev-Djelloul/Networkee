/**
 * Modale de connexion / inscription / mot de passe oublié réutilisée sur toute page
 * (includes/auth-modal.php). Chaque page appelle openLoginModal(action, id) sur ses
 * boutons "Se connecter pour ..." puis écoute son propre DOMContentLoaded pour reprendre
 * l'action interrompue (sessionStorage 'networkee_after_login') une fois reconnecté.
 */
(function () {
    'use strict';

    let modalIntent = null;

    window.openLoginModal = function (action, id) {
        modalIntent = { action: action, id: id || null };
        document.getElementById('login-modal').classList.remove('hidden');
        switchModalView('login');
    };

    window.closeLoginModal = function () {
        document.getElementById('login-modal').classList.add('hidden');
        document.getElementById('login-modal-message').innerHTML = '';
        document.getElementById('register-modal-message').innerHTML = '';
        document.getElementById('forgot-modal-message').innerHTML = '';
        modalIntent = null;
    };

    window.switchModalView = function (view) {
        document.getElementById('modal-view-login').style.display = view === 'login' ? 'block' : 'none';
        document.getElementById('modal-view-register').style.display = view === 'register' ? 'block' : 'none';
        document.getElementById('modal-view-forgot').style.display = view === 'forgot' ? 'block' : 'none';
        const focusId = { login: 'modal-email', register: 'modal-reg-username', forgot: 'modal-forgot-email' }[view];
        const field = document.getElementById(focusId);
        if (field) field.focus();
    };

    function handleAuthSuccess(messageDiv, message) {
        messageDiv.innerHTML = '<div class="alert alert-success">' + message + '</div>';
        if (modalIntent) {
            sessionStorage.setItem('networkee_after_login', JSON.stringify(modalIntent));
        }
        setTimeout(function () { window.location.reload(); }, 500);
    }

    function submitJson(form, url, messageDiv, onSuccess) {
        const formData = new URLSearchParams(new FormData(form));
        formData.append('ajax', 'true');

        fetch(url, { method: 'POST', body: formData })
            .then(function (r) { return r.json(); })
            .then(function (response) {
                if (response.success) {
                    onSuccess(response);
                } else {
                    messageDiv.innerHTML = '<div class="alert alert-danger">' + response.message + '</div>';
                }
            })
            .catch(function () {
                messageDiv.innerHTML = '<div class="alert alert-danger">Une erreur est survenue. Veuillez réessayer.</div>';
            });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const overlay = document.getElementById('login-modal');
        if (!overlay) return;

        overlay.addEventListener('click', function (e) {
            if (e.target === this) closeLoginModal();
        });

        document.getElementById('loginModalForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const messageDiv = document.getElementById('login-modal-message');
            submitJson(this, 'login.php', messageDiv, function (response) {
                handleAuthSuccess(messageDiv, response.message);
            });
        });

        document.getElementById('registerModalForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const messageDiv = document.getElementById('register-modal-message');
            submitJson(this, 'register.php', messageDiv, function (response) {
                handleAuthSuccess(messageDiv, response.message);
            });
        });

        document.getElementById('forgotModalForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const messageDiv = document.getElementById('forgot-modal-message');
            submitJson(this, 'forgot-password.php', messageDiv, function (response) {
                messageDiv.innerHTML = '<div class="alert alert-success">' + response.message + '</div>';
                this.reset();
            }.bind(this));
        });
    });
})();
