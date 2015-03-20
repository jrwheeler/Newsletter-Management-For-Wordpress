jQuery(function($){
    'use strict';

    // Find all the schools in the region. Used for autocomplete list of schools
    var schools;
    var data = 'action=getSchools';
    $.ajax({
        type:'GET',
        url:submitMailchimp.url,
        data:data,
    }).done(function(msg) {
        schools = msg.data;
    });


    // Check if the user is a student or parent. Adds extra fields
    // if they are a student. Also adds autocomplete box if they
    // are a student.
    $('._entrySelector').click(function(e){
        var entryValue = $(this).val();
        if (entryValue === 'student') {
            $('._entrySelectorWrapper')
                .last()
                .after('<div class="_studentInfo" >'
                    +' <input type="text" name="datebirthday" placeholder="Birthday (YYYY-MM-DD)" />'
                    +' <input type="text" class="_schools" name="alphschool" placeholder="School" />'
                    +' <input type="text" name="alphparentFirstName" placeholder="Parent\'s First Name" />'
                    +' <input type="text" name="alphparentLastName" placeholder="Parent\'s Last Name" />'
                    +' <input type="text" name="mailparentEmail" placeholder="Parent\'s Email" />'
                    +' <input type="text" name="phneparentPhone" placeholder="Parent\'s Phone" />'
                    +'</div>');

            $( "._schools" ).autocomplete({
              source: schools
            });
        } else if (entryValue === 'parent') {
            $('._studentInfo').remove();
        }
    });

    // Captures user submit form. Removes old validation results.
    // Then submits the data via ajax. Anonymous callback for succes
    // shows the user a succes message. On failure anonymous callback
    // highlights errors and generates a <li> of errors placed at top
    // of container.
    $('._signUpForm input[type="button"]').click(function(e){

        $('._signUpForm input').each(function(e){
            $(this).removeClass('error');
        });

        var data = $('._signUpForm').serialize() + '&action=mailChimpAjax' + '&nonce=' + submitMailchimp.nonce;
        $.ajax({
            type:'POST',
            url:submitMailchimp.url,
            data:data,
        }).done(function(msg) {
            $('html,body').animate({scrollTop: 0}, 500);

            // $('#dumpContainer').html(msg);
            if (msg.success === false) {
                var listOfErrors = '';
                if (typeof msg.data === 'object') {
                    $.each(msg.data, function (key, elem) {
                        $('[name="'+key+'"]').addClass('error');

                        $.each(elem, function (newKey, error) {
                            listOfErrors = listOfErrors + '<li>' + error + '</li>';
                        });
                    });
                } else {
                    listOfErrors = '<li>' + msg.data + '</li>';
                }
                $('._messageContaier').html('<div class="errorBox"><ul>'+listOfErrors+'</ul></div>');
            } else {
                $('._messageContaier').html('<div class="successBox"><p>Signup Complete</p></div>');
                $('._signUpForm input[type="text"], ._signUpForm input[type="radio"]').each(function(e){
                    $(this).val('');
                    $(this).prop('checked', false);
                });

            }
        });
    });

});