/**
 * Menu "..." des publications (copier le lien / supprimer / partager). Un seul menu ouvert à
 * la fois ; se ferme au clic en dehors ou après une action.
 *
 * Les dropdowns sont déplacés vers <body> et positionnés en `fixed` (calculé en JS) au lieu de
 * rester en `absolute` dans .post-menu-wrapper : la carte .post a `overflow: hidden` (pour
 * arrondir les images), ce qui rognait le menu quand le bouton était proche du bord de la carte.
 * Voir scripts/hover-popover.js pour le même correctif appliqué à un autre popover.
 */
(function () {
    'use strict';

    const homes = new WeakMap(); // dropdown -> { parent, nextSibling } pour le remettre en place à la fermeture

    function positionDropdown(button, dropdown) {
        const rect = button.getBoundingClientRect();
        const width = dropdown.offsetWidth || 190;

        let left = rect.right - width;
        left = Math.max(8, Math.min(left, window.innerWidth - width - 8));

        let top = rect.bottom + 4;
        const estimatedHeight = dropdown.offsetHeight || 160;
        if (top + estimatedHeight > window.innerHeight - 8) {
            top = rect.top - estimatedHeight - 4;
        }

        dropdown.style.left = left + 'px';
        dropdown.style.top = top + 'px';
    }

    function closeDropdown(dropdown) {
        dropdown.classList.remove('is-open');
        const home = homes.get(dropdown);
        if (home && dropdown.parentNode === document.body) {
            home.parent.insertBefore(dropdown, home.nextSibling);
        }
    }

    function closeAll() {
        document.querySelectorAll('.post-menu-dropdown.is-open').forEach(closeDropdown);
    }

    window.togglePostMenu = function (button) {
        const dropdown = button.nextElementSibling;
        if (!dropdown) return;
        const isOpen = dropdown.classList.contains('is-open');
        closeAll();
        if (isOpen) return;

        if (!homes.has(dropdown)) {
            homes.set(dropdown, { parent: dropdown.parentNode, nextSibling: dropdown.nextSibling });
        }
        document.body.appendChild(dropdown);
        dropdown.classList.add('is-open');
        positionDropdown(button, dropdown);
    };

    window.copyPostLink = function (postId) {
        const url = window.location.origin + window.location.pathname + '#post-' + postId;
        navigator.clipboard.writeText(url).catch(function () {});
        closeAll();
    };

    document.addEventListener('click', function (e) {
        if (e.target.closest('.post-menu-wrapper') || e.target.closest('.post-menu-dropdown')) return;
        closeAll();
    });

    window.addEventListener('scroll', closeAll, true);
    window.addEventListener('resize', closeAll);
})();
