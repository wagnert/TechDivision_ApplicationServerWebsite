// add bootstrap classes to sphinx toctree ul element
$('#sidebar-nav ul').addClass('nav nav-pills nav-stacked affix-top');
// set first item action by default
$('#sidebar-nav ul li').first().addClass('active');
// set first id to nav sidebar element from content
var firstId = $('#doc-content').find('.section').attr('id');
$('#sidebar-nav ul li').first().find('a').attr('href', '#' + firstId);

// add documentation to hash anchor href on sidebar items
jQuery('#sidebar-nav').find('a').each(function(){
    var href = jQuery(this).attr('href');
    jQuery(this).attr('href', 'documentation/' + href);
    jQuery(this).attr('data-target', href);
});

// set affix offset for sidebar nav
$('#sidebar-nav').affix({
    offset: {
        top: 190
    }
});