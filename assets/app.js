// assets/app.js
import './styles/app.scss';
import 'bootstrap';

// Initialisation globale
document.addEventListener('DOMContentLoaded', () => {
    // Activer les tooltips Bootstrap
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Gestion des modals de suppression (ajoutez cette partie)
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
});