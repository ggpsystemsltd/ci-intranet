/* Global JavaScript File for working with jQuery library */

// execute when the HTML file's (document object model: DOM) has loaded
$( document ).ready( function() {
    // confirmation dialog
    // bind to submit button
    $( "#dialog" ).dialog({
        autoOpen: false,
        buttons: [
            {
                text: "Ok",
                icons: {
                    primary: "ui-icon-heart"
                },
                click: function() {
                    if ($("#confirm-request").is(":checked")) {
                        $(this).dialog("close");
                        $('form#holiday-form').submit();//submit the underlying form
                    } else {
                        $( "#dialog" ).effect("shake");
                    }
                }
            }
        ],
        modal: true
    });
    $( "#submit-button" ).click( function() {
        $( "#dialog" ).dialog( "open" );
    });
});