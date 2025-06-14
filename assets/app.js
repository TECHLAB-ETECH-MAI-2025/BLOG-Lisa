document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-like').forEach(button => {
        button.addEventListener('click', async function(e) {

            e.preventDefault();

            const articleId = this.dataset.articleId;
            const csrfToken = this.dataset.csrfToken;
            const icon = this.querySelector('i');
            const likeCount = this.querySelector('.like-count');

            try {
                const response = await fetch(`/api/article/${articleId}/like`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrfToken,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ _token: csrfToken })
                });

                if (!response.ok) throw new Error('Erreur réseau');

                const data = await response.json();

                if (data.success) {

                    this.classList.toggle('btn-primary');
                    this.classList.toggle('btn-outline-primary');
                    icon.classList.toggle('fas');
                    icon.classList.toggle('far');
                    
                    if (likeCount) {
                        likeCount.textContent = data.likesCount;
                    }
                } else {
                    alert(data.message || "Erreur lors de l'action");
                }
            } catch (error) {
                console.error('Error:', error);
                alert("Erreur réseau - Veuillez réessayer");
            }
        });



    });

});