function toggleLike(event, postId) {
    const likeButton = event.target;
    const counter = document.getElementById(`like-counter-${postId}`);
    let count = parseInt(counter.textContent);

    // Envoi de la requête au serveur pour ajouter ou supprimer le like
    fetch('home.php', {
        method: 'POST',
        body: new URLSearchParams({
            'like': postId
        }),
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Mise à jour de l'interface
        if (data.liked) {
            likeButton.classList.add('liked');
            counter.textContent = count + 1;
        } else {
            likeButton.classList.remove('liked');
            counter.textContent = count - 1;
        }
    })
    .catch(error => {
        console.error('Erreur lors de l\'ajout du like', error);
    });
}

document.querySelectorAll('.like-button').forEach(button => {
    button.addEventListener('click', function(event) {
        const postId = event.target.value;
        toggleLike(event, postId);
    });
});


