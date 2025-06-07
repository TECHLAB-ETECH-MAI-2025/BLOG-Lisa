document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-like').forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const articleId = this.dataset.articleId;
            const csrfToken = this.dataset.csrfToken;
            const icon = this.querySelector('i');
            const likeCount = this.querySelector('.like-count');

            try {
                console.log("Envoi like pour l'article:", articleId); // Debug
                const response = await fetch(`/api/article/${articleId}/like`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || "Erreur serveur");
                }

                const data = await response.json();
                console.log("Réponse API:", data);

                if (data.success) {
                    if (data.isLiked) {
                        this.classList.add('btn-primary');
                        this.classList.remove('btn-outline-primary');
                        icon.classList.add('fas');
                        icon.classList.remove('far');
                    } else {
                        this.classList.remove('btn-primary');
                        this.classList.add('btn-outline-primary');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                    
                    if (likeCount) {
                        likeCount.textContent = data.likesCount;
                    }
                } else {
                    alert(data.message || "Action échouée");
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert(error.message || "Erreur réseau");
            }
        });
    });
});