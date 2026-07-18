/**
 * Menu "..." des publications (copier le lien / supprimer). Un seul menu ouvert à la fois ;
 * se ferme au clic en dehors ou après une action.
 */
function togglePostMenu(button) {
    const dropdown = button.nextElementSibling;
    if (!dropdown) return;
    const isOpen = dropdown.classList.contains('is-open');
    document.querySelectorAll('.post-menu-dropdown.is-open').forEach(function (d) {
        d.classList.remove('is-open');
    });
    if (!isOpen) dropdown.classList.add('is-open');
}

function copyPostLink(postId) {
    const url = window.location.origin + window.location.pathname + '#post-' + postId;
    navigator.clipboard.writeText(url).catch(function () {});
    document.querySelectorAll('.post-menu-dropdown.is-open').forEach(function (d) {
        d.classList.remove('is-open');
    });
}

document.addEventListener('click', function (e) {
    if (e.target.closest('.post-menu-wrapper')) return;
    document.querySelectorAll('.post-menu-dropdown.is-open').forEach(function (d) {
        d.classList.remove('is-open');
    });
});
