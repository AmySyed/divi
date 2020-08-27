jQuery(function($) {

	// Switches option sections
    $('.dipe-cf7-styler-form-page').hide();
    var activetab = '';
    if (typeof(localStorage) != 'undefined' ) {
        activetab = localStorage.getItem("activetab");
    }

    //if url has section id as hash then set it as active or override the current local storage value
    if(window.location.hash){
        activetab = window.location.hash;
        if (typeof(localStorage) != 'undefined' ) {
            localStorage.setItem("activetab", activetab);
        }
    }

    if (activetab != '' && $(activetab).length ) {
        $(activetab).fadeIn();
    } else {
        $('.dipe-cf7-styler-form-page:first').fadeIn();
    }
    
    $('.dipe-cf7-styler-form-page .collapsed').each(function(){
        $(this).find('input:checked').parent().parent().parent().nextAll().each(
            function(){
                if ($(this).hasClass('last')) {
                    $(this).removeClass('hidden');
                    return false;
                }
                $(this).filter('.hidden').removeClass('hidden');
            });
    });

    if (activetab != '' && $(activetab + '-tab').length ) {
        $(activetab + '-tab').addClass('nav-tab-active');
    }
    else {
        $('.dipe-cf7-styler a:first').addClass('nav-tab-active');
    }              

	$('.dipe-cf7-styler a').click(function(evt) {
		console.log('work');
        $('.dipe-cf7-styler a').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active').blur();
       
        var clicked_group = $(this).attr('href');
        if (typeof(localStorage) != 'undefined' ) {
            localStorage.setItem("activetab", $(this).attr('href'));
        }
        $('.dipe-cf7-styler-form-page').hide();
        $(clicked_group).fadeIn();
        evt.preventDefault();
    });

	$(document).ready(function($) {
		$('#tag-generator-list a[href$="dipe_row"').removeClass('thickbox');
		$('#tag-generator-list a[href$="dipe_one"').removeClass('thickbox');
		$('#tag-generator-list a[href$="dipe_one_half"').removeClass('thickbox');
		$('#tag-generator-list a[href$="dipe_one_third"').removeClass('thickbox');
		$('#tag-generator-list a[href$="dipe_one_fourth"').removeClass('thickbox');
		$('#tag-generator-list a[href$="dipe_two_third"').removeClass('thickbox');
		$('#tag-generator-list a[href$="dipe_three_fourth"').removeClass('thickbox');

		$('#tag-generator-list a').on("click", function(e){
			e.preventDefault();
 			var href = $(this).attr('href'),
 				name = dipe_get_url_pram('inlineId' , href);
 				if(name.startsWith('dipe')) {
					dipe_grid_insert(name);
 				}
		});
		
	});


	//Get url pram
	function dipe_get_url_pram( pram, href ) {
	    pram = pram.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
	    var regex = new RegExp('[\\?&]' + pram + '=([^&#]*)'),
	    	results = regex.exec( href ),
	    	tagGen = results === null ? '' : decodeURIComponent( results[1].replace(/\+/g, ' ') );
	    return tagGen.replace( 'tag-generator-panel-', '');
	};

	// Insert function
	function dipe_grid_insert(name) {
		var form = document.getElementById("wpcf7-form"),
		selection_start = form.selectionStart,
		selection_end = form.selectionEnd,
		shortcode_start = "[" + name + "]",
		shortcode_end = "[/" + name + "]";
		dipe_grid_update(selection_start, shortcode_start );
		dipe_grid_update((selection_end + shortcode_end.length - 1), shortcode_end);
	}

	// Update function
	function dipe_grid_update(i, t){
		var val = $('#wpcf7-form').val(),
			new_val = [ val.slice(0, i), t, val.slice(i)].join('');
		$('#wpcf7-form').val(new_val);
	}

    // Ajax notice
    $('div.notice.dipe-message-dismissed').on('click', 'button.notice-dismiss, .dipe-button-notice-dismiss', function (event) {
        event.preventDefault();

        $.post(etCore.ajaxurl, {
            action: 'dipe_set_admin_notice_viewed',
            notice_id: $(this).closest('.dipe-message-dismissed').data('notice_id')
        });

        var $wrapperElm = $(this).closest('.dipe-message-dismissed');
        $wrapperElm.fadeTo(100, 0, function () {
            $wrapperElm.slideUp(100, function () {
                $wrapperElm.remove();
            });
        });
    });

});