/**
 * Popovers au survol (abonnés / abonnements / likes d'un post / candidats à une offre).
 * Positionné en `position: fixed` calculé en JS plutôt qu'en CSS pur : les cartes (.card,
 * .post) ont `overflow: hidden` pour arrondir les images, ce qui coupait le popover s'il
 * restait un enfant positionné en `absolute`. En le sortant du flux via `fixed` et en
 * calculant sa position à partir du déclencheur, il n'est plus jamais rogné.
 */
(function () {
    'use strict';

    function positionPopover(trigger, popover) {
        const rect = trigger.getBoundingClientRect();
        const popoverWidth = popover.offsetWidth || 220;

        let left = rect.left + rect.width / 2 - popoverWidth / 2;
        left = Math.max(8, Math.min(left, window.innerWidth - popoverWidth - 8));

        let top = rect.bottom + 8;
        // Si ça déborde en bas de l'écran, on ouvre vers le haut à la place.
        const estimatedHeight = popover.offsetHeight || 200;
        if (top + estimatedHeight > window.innerHeight - 8) {
            top = rect.top - estimatedHeight - 8;
        }

        popover.style.left = left + 'px';
        popover.style.top = top + 'px';
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.hover-stat').forEach(function (trigger) {
            const popover = trigger.querySelector('.hover-popover');
            if (!popover) return;

            // Détache le popover du flux de la carte (qui a overflow:hidden) pour l'ancrer au <body>.
            document.body.appendChild(popover);

            // Petit délai à la sortie : laisse le temps de traverser l'espace entre le
            // déclencheur et le popover (ex. pour cliquer sur un nom dans la liste) sans
            // que le popover ne se referme entre les deux.
            let hideTimer = null;
            function show() {
                clearTimeout(hideTimer);
                positionPopover(trigger, popover);
                popover.classList.add('is-visible');
            }
            function scheduleHide() {
                hideTimer = setTimeout(function () {
                    popover.classList.remove('is-visible');
                }, 150);
            }

            trigger.addEventListener('mouseenter', show);
            trigger.addEventListener('mouseleave', scheduleHide);
            popover.addEventListener('mouseenter', function () { clearTimeout(hideTimer); });
            popover.addEventListener('mouseleave', scheduleHide);
        });
    });
})();
