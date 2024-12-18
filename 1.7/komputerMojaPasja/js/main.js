$(document).ready(function() {
    // Efekt hover dla elementów image-wrap
    $('.image-wrap').hover(
        function() {
            $(this).css({
                'transform': 'scale(1.02)',
                'background-color': '#f0f0f0'
            });
        },
        function() {
            $(this).css({
                'transform': 'scale(1)',
                'background-color': '#fff'
            });
        }
    );

    // Animacja dla intro-text
    $('.intro-text').hide().fadeIn(1000);

    // Płynna zmiana koloru tła
    $('input[type="button"]').click(function() {
        const color = $(this).val();
        $('body').animate({
            backgroundColor: $(this).attr('onclick').match(/'(.*?)'/)[1]
        }, 500);
    });
}); 