/**
 * Composer de publication : aperçu image/vidéo (avec suppression) + sélecteur
 * d'emojis façon LinkedIn. S'applique à tout conteneur `.composer-widget`
 * trouvé sur la page (fil, profil...), sans dépendre d'IDs codés en dur.
 */
(function () {
    'use strict';

    const EMOJI_GROUPS = {
        'Smileys': ['😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂', '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩', '😘', '😗', '😚', '😙', '😋', '😛', '😜', '🤪', '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤢', '🤮', '🥵', '🥶', '🥴', '😵', '🤯', '🤠', '🥳', '😎', '🤓', '🧐', '😕', '😟', '🙁', '😮', '😯', '😲', '😳', '🥺', '😢', '😭', '😱', '😖', '😣', '😞', '😓', '😩', '😫', '🥱'],
        'Gestes': ['👋', '🤚', '🖐️', '✋', '🖖', '👌', '🤌', '🤏', '✌️', '🤞', '🤟', '🤘', '🤙', '👈', '👉', '👆', '🖕', '👇', '☝️', '👍', '👎', '✊', '👊', '🤛', '🤜', '👏', '🙌', '👐', '🤲', '🙏', '🤝', '💪'],
        'Coeurs': ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘', '💝'],
        'Objets & symboles': ['🔥', '✨', '🎉', '🎊', '💡', '📌', '📎', '🔗', '💼', '📊', '🚀', '⭐', '🌟', '💯', '✅', '❌', '⚡', '🏆', '🎯', '📅', '⏰', '💰', '🎁', '📈', '📝', '💬'],
        'Activités & objets': ['⚽', '🏀', '🏈', '🎮', '🎲', '🎨', '🎵', '🎬', '📷', '☕', '🍕', '🍔', '🍎', '✈️', '🚗', '🏖️', '🌍', '💻', '📱', '☀️', '🌙', '🌈'],
    };

    function buildPicker(container) {
        if (container.dataset.built === '1') return;
        container.dataset.built = '1';
        Object.entries(EMOJI_GROUPS).forEach(([label, emojis]) => {
            const section = document.createElement('div');
            section.className = 'emoji-picker-section';

            const title = document.createElement('div');
            title.className = 'emoji-picker-label';
            title.textContent = label;
            section.appendChild(title);

            const grid = document.createElement('div');
            grid.className = 'emoji-picker-grid';
            emojis.forEach((emoji) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'emoji-picker-item';
                btn.textContent = emoji;
                grid.appendChild(btn);
            });
            section.appendChild(grid);

            container.appendChild(section);
        });
    }

    function insertAtCursor(textarea, text) {
        const start = textarea.selectionStart ?? textarea.value.length;
        const end = textarea.selectionEnd ?? textarea.value.length;
        textarea.value = textarea.value.slice(0, start) + text + textarea.value.slice(end);
        const pos = start + text.length;
        textarea.focus();
        textarea.setSelectionRange(pos, pos);
    }

    // Les popovers/menus de post ont le même souci : leur carte parente a
    // `overflow: hidden` (coins arrondis), donc un panneau `absolute` y serait
    // rogné. On le sort du flux vers <body> en `fixed`, positionné en JS.
    const pickerHomes = new WeakMap();

    function positionPicker(trigger, picker) {
        const rect = trigger.getBoundingClientRect();
        const width = picker.offsetWidth || 280;
        let left = rect.left;
        left = Math.max(8, Math.min(left, window.innerWidth - width - 8));

        let top = rect.top - 8 - (picker.offsetHeight || 280);
        if (top < 8) top = rect.bottom + 8;

        picker.style.left = left + 'px';
        picker.style.top = top + 'px';
    }

    function closeAllPickers() {
        document.querySelectorAll('.emoji-picker.is-open').forEach((picker) => {
            picker.classList.remove('is-open');
            const home = pickerHomes.get(picker);
            if (home && picker.parentNode === document.body) {
                home.parent.insertBefore(picker, home.nextSibling);
            }
        });
    }

    function initWidget(widget) {
        const textarea = widget.querySelector('textarea');
        const imageBtn = widget.querySelector('.composer-image-btn');
        const imageInput = widget.querySelector('.composer-image-input');
        const videoBtn = widget.querySelector('.composer-video-btn');
        const videoInput = widget.querySelector('.composer-video-input');
        const emojiBtn = widget.querySelector('.composer-emoji-btn');
        const emojiPicker = widget.querySelector('.emoji-picker');
        const preview = widget.querySelector('.composer-media-preview');
        const previewImg = widget.querySelector('.composer-preview-img');
        const previewVideo = widget.querySelector('.composer-preview-video');
        const removeBtn = widget.querySelector('.composer-media-remove');
        const label = widget.querySelector('.composer-media-label');

        // display en plus de hidden : ceinture et bretelles, pour ne jamais
        // avoir les deux aperçus visibles en même temps quoi qu'il arrive.
        function setVisible(el, visible) {
            el.hidden = !visible;
            el.style.display = visible ? '' : 'none';
        }

        function showPreview(file, type) {
            const url = URL.createObjectURL(file);
            if (type === 'image') {
                previewImg.src = url;
                setVisible(previewImg, true);
                setVisible(previewVideo, false);
                previewVideo.removeAttribute('src');
            } else {
                previewVideo.src = url;
                setVisible(previewVideo, true);
                setVisible(previewImg, false);
                previewImg.removeAttribute('src');
            }
            if (label) label.textContent = file.name;
            setVisible(preview, true);
        }

        function clearMedia() {
            if (imageInput) imageInput.value = '';
            if (videoInput) videoInput.value = '';
            setVisible(previewImg, false);
            setVisible(previewVideo, false);
            previewImg.removeAttribute('src');
            previewVideo.removeAttribute('src');
            if (label) label.textContent = '';
            setVisible(preview, false);
        }

        if (imageBtn && imageInput) {
            imageBtn.addEventListener('click', () => {
                if (videoInput) videoInput.value = '';
                imageInput.click();
            });
            imageInput.addEventListener('change', () => {
                if (imageInput.files[0]) showPreview(imageInput.files[0], 'image');
            });
        }

        if (videoBtn && videoInput) {
            videoBtn.addEventListener('click', () => {
                if (imageInput) imageInput.value = '';
                videoInput.click();
            });
            videoInput.addEventListener('change', () => {
                if (videoInput.files[0]) showPreview(videoInput.files[0], 'video');
            });
        }

        if (removeBtn) removeBtn.addEventListener('click', clearMedia);

        if (emojiBtn && emojiPicker) {
            emojiBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                buildPicker(emojiPicker);
                const isOpen = emojiPicker.classList.contains('is-open');
                closeAllPickers();
                if (!isOpen) {
                    if (!pickerHomes.has(emojiPicker)) {
                        pickerHomes.set(emojiPicker, { parent: emojiPicker.parentNode, nextSibling: emojiPicker.nextSibling });
                    }
                    document.body.appendChild(emojiPicker);
                    emojiPicker.classList.add('is-open');
                    positionPicker(emojiBtn, emojiPicker);
                }
            });
            emojiPicker.addEventListener('click', (e) => {
                const item = e.target.closest('.emoji-picker-item');
                if (!item || !textarea) return;
                insertAtCursor(textarea, item.textContent);
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.composer-widget').forEach(initWidget);
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.composer-emoji-wrapper')) closeAllPickers();
    });
})();
