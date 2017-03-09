/* Global JavaScript File for working with jQuery library
 * Execute when the HTML file's (document object model: DOM) has loaded
 */

$(document).ready(function() {
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

    /* jQUERY UI CALENDAR PLUGIN WITH TIME ADD-ON*/
    // bind the Datetimepicker to the specific field ID
    $( '#start-date' ).datetimepicker( {
        beforeShowDay: $.datepicker.noWeekends,
        dateFormat: 'yy-mm-dd',
        minDate: new Date(),
        maxDate: new Date(new Date().getFullYear()+1, 2, 31, 23, 59),
        minTime: '09:00:00',
        maxTime: '17:30:00',
        timeFormat: 'HH:mm:ss',
        timeInput: true
    });

    $('#submit-btn').click(function(){
        BootstrapDialog.confirm({
            title: 'Confirm request',
            message: 'Confirm that you wish to book the specified resource(s)?',
            btnOKLabel: 'Book resource(s)',
            callback: function(result){
                if(result) {
                    $('form#machine-form').submit(); // submit the underlying form
                }
            }
        });
    });
});