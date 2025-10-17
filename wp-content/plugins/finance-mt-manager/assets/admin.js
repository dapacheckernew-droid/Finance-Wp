jQuery(function($){
    $('.fmtm-add-line').on('click', function(e){
        e.preventDefault();
        const template = $('#fmtm-line-template').html();
        $('.fmtm-line-items').append(template);
    });
    $(document).on('click', '.fmtm-remove-line', function(e){
        e.preventDefault();
        $(this).closest('.fmtm-line-item').remove();
    });
});
