import './styles/app.scss';
import 'bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    // Initialisation des tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Gestion de la suppression d'articles
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-btn')) {
            e.preventDefault();
            const articleId = e.target.dataset.articleId;
            const articleTitle = e.target.dataset.articleTitle;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            
            document.getElementById('articleTitle').textContent = articleTitle;
            document.getElementById('deleteForm').action = `/article/${articleId}`;
            document.querySelector('#deleteForm input[name="_token"]').value = e.target.dataset.csrfToken;
            
            modal.show();
        }
    });

    document.querySelectorAll('.btn-like').forEach(button => {
    button.addEventListener('click', async function(e) {
        e.preventDefault();
        
        const articleId = this.dataset.articleId;
        const csrfToken = this.dataset.csrfToken;

        try {
            const response = await fetch(`/api/article/${articleId}/like`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    _token: csrfToken
                }),
                credentials: 'include' // Important pour les cookies de session
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || `Erreur HTTP: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                // Mise à jour de l'interface...
            } else {
                console.error('Erreur serveur:', data);
                alert(data.error || "Erreur lors du like");
            }
        } catch (error) {
            console.error('Error:', error);
            alert(error.message || "Erreur réseau");
        }
    });
});
});