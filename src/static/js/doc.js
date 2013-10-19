jQuery('#bootstrap-nav').find('ul').attr('class', 'nav nav-list bs-docs-sidenav affix');
jQuery('#bootstrap-nav').find('li.current').attr('class', 'active');

var firstId = jQuery('#doc-content').find('.section').attr('id');
jQuery('#bootstrap-nav').find('li.active').find('a').attr('href', '#' + firstId);

jQuery('#bootstrap-nav').find('a').each(function(){
    var href = jQuery(this).attr('href');
    jQuery(this).attr('href', 'documentation/' + href);
    jQuery(this).prepend('<i class="icon-chevron-right"></i>');
});

jQuery('#doc-content').find('a').each(function(){
    var href = jQuery(this).attr('href');
    jQuery(this).attr('href', 'documentation/' + href);
});

$('.nav li a').on('click', function() {
    $(this).parent().parent().find('.active').removeClass('active');
    $(this).parent().addClass('active');
});