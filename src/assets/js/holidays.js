/* Global JavaScript File for working with jQuery library
 * Execute when the HTML file's (document object model: DOM) has loaded
 */

$(document).ready(function() {
    // get values from form
    var myStartDate = $('#start-date');
    var myEndDate = $('#end-date');
    var myEndAM = $('#end-am');
    var myEndFull = $('#end-full');
    var mySubmitButton = $('#submit-btn');
    var myCancelButton = $('#cancel-btn');

    myEndDate.prop('disabled', true);
    myEndAM.prop('disabled', true);
    myEndFull.prop('disabled', true);

    $("input:radio[name='start_type'][value='full']").attr('checked', 'checked');
    $("input:radio[name='end_type'][value='full']").attr('checked', 'checked');
    $("input:radio[name='start_type'][value='full']").prop('checked', true);
    $("input:radio[name='end_type'][value='full']").prop('checked', true);


    // Form reveal
    $('#legend').click(function(){
        $('.form-content').toggle();
    });

    /* jQUERY UI CALENDAR PLUGIN */
    // bind the Datepicker to the date-picker class
    $( '.date-picker' ).datepicker( {
        beforeShowDay: $.datepicker.noWeekends,
        dateFormat: 'yy-mm-dd',
        minDate: new Date(),
        maxDate: new Date(new Date().getFullYear()+1, 2, 31, 23, 59)
    });

    // confirmation dialog
    // bind to submit button
    $('#dialog').dialog({
        autoOpen: false,
        buttons: [
            {
                text: 'Ok',
                icons: {
                    primary: 'ui-icon-check'
                },
                click: function(){
                    if($('#confirm-request').is(':checked')){
                        $(this).dialog('close');
                        $('form#holiday-form').submit(); // submit the underlying form
                    } else {
                        $('#dialog').effect('shake');
                    }
                }
            }
        ],
        modal: true
    });

    // Validation to enable the submit button
    myStartDate.change(function(){
        if(myStartDate.val()!=''){
            if(!$('#start-am').is(':checked')){
                myEndDate.prop('disabled', false);
                $("#end-help-block").show();
            } else {
                myEndDate.prop('disabled', true);
                myEndAM.prop('disabled', true);
                myEndFull.prop('disabled', true);
                $("#end-help-block").hide();
            }
            myEndDate.val(myStartDate.val());
            var minDate = myStartDate.val().split("-");
            $( '.date-picker' ).datepicker('option', 'minDate', new Date(minDate));
            mySubmitButton.switchClass('btn-default','btn-success');
            mySubmitButton.prop( 'disabled', false);
        }
    });

    $("input:radio[name='start_type']").change(function(){
        if($('#start-am').is(':checked')){
            myEndDate.val(myStartDate.val());
            myEndDate.prop('disabled', true);
            myEndAM.prop('disabled', true);
            myEndFull.prop('disabled', true);
            $("#end-help-block").hide();
        } else if($('#start-pm').is(':checked')) {
            if (myStartDate.val()!='' && myEndDate.val()!='') {
                if(myStartDate.val()<myEndDate.val()){
                    myEndAM.prop('disabled', false);
                    myEndFull.prop('disabled', false);
                } else {
                    myEndAM.prop('disabled', true);
                    myEndFull.prop('disabled', true);
                    $("input:radio[name='end_type'][value='full']").attr('checked', 'checked');
                    $("input:radio[name='end_type'][value='full']").prop('checked', true);
                }
            }
            myEndDate.prop('disabled', false);
            $("#end-help-block").show();
        } else {
            myEndDate.prop('disabled', false);
            myEndAM.prop('disabled', false);
            myEndFull.prop('disabled', false);
            $("#end-help-block").show();
        }
    });

    myEndDate.change(function() {
        if (myStartDate.val()!='' && myEndDate.val()!='') {
            if(myStartDate.val()<myEndDate.val()){
                myEndAM.prop('disabled', false);
                myEndFull.prop('disabled', false);
            } else {
                myEndAM.prop('disabled', true);
                myEndFull.prop('disabled', true);
                $("input:radio[name='end_type'][value='full']").attr('checked', 'checked');
                $("input:radio[name='end_type'][value='full']").prop('checked', true);
            }
            mySubmitButton.switchClass('btn-default','btn-success');
            mySubmitButton.prop('disabled', false);
        }
    });

    mySubmitButton.click(function() {
        $('#dialog').dialog('open');
    });

    myCancelButton.click(function() {
        myEndDate.prop('disabled', true);
        $("#end-help-block").hide();
        myEndAM.prop('disabled', true);
        myEndFull.prop('disabled', true);
        $( '.date-picker' ).datepicker('option', 'minDate', new Date());


        mySubmitButton.switchClass('btn-success','btn-default');
        mySubmitButton.prop('disabled', true);

        $("input:radio[name='start_type'][value='full']").attr('checked', 'checked');
        $("input:radio[name='start_type'][value='full']").prop('checked', true);
        $("input:radio[name='end_type'][value='full']").attr('checked', 'checked');
        $("input:radio[name='end_type'][value='full']").prop('checked', true);
    });
});