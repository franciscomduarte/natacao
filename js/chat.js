$(document).ready(function() {
    $('#formChat').on('submit', function(e) {
        e.preventDefault();
        var mensagem = $('#mensagem').val().trim();
        if (mensagem === '') return;

        // Exibe a mensagem do usuário no chat
        $('#chatbox').append('<div class="text-right"><strong>Você:</strong> ' + mensagem + '</div>');
        $('#mensagem').val('');
        $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);

        // Envia a mensagem para o servidor via AJAX
        $.ajax({
            url: 'chatbot.php',
            method: 'POST',
            data: { mensagem: mensagem },
            dataType: 'json',
            success: function(response) {
                // Exibe a resposta do chatbot no chat
                $('#chatbox').append('<div class="text-left"><strong>Ligeirinho:</strong> ' + response.resposta + '</div>');
                $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
            },
            error: function() {
                // Exibe uma mensagem de erro no chat
                $('#chatbox').append('<div class="text-left text-danger"><strong>Ligeirinho:</strong> Ocorreu um erro ao processar sua mensagem.</div>');
                $('#chatbox').scrollTop($('#chatbox')[0].scrollHeight);
            }
        });
    });
});
