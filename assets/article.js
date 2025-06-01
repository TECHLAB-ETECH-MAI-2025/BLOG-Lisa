import $ from 'jquery';

$(document).ready(function() {

    const $commentForm = $('#comment-form');
    const $commentsList = $('#comments-list');
    const $commentsCount = $('#comments-count');

    if ($commentForm.length) {
        $commentForm.on('submit', function(e) {
            e.preventDefault();

            const $submitBtn = $commentForm.find('button[type="submit"]');
            const originalBtnText = $submitBtn.html();

            $submitBtn.html('Envoi en cours...').prop('disabled', true);

            $.ajax({
                url: $commentForm.attr('action'),
                method: 'POST',
                data: $commentForm.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $commentsList.prepend(response.commentHtml);
                        $commentsCount.text(response.commentsCount);
                        $commentForm[0].reset();
                        showAlert('success', 'Votre commentaire a été publié avec succès !');
                    } else {
                        showAlert('danger', response.error || 'Une erreur est survenue lors de l\'envoi du commentaire.');
                    }
                },
                error: function() {
                    showAlert('danger', 'Une erreur est survenue lors de l\'envoi du commentaire.');
                },
                complete: function() {
                    $submitBtn.html(originalBtnText).prop('disabled', false);
                }
            });
        });
    }

  
    function showAlert(type, message) {
        const $alert = $(`
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `);

        $('#alerts-container').append($alert);
        setTimeout(() => $alert.alert('close'), 5000);
    }
});