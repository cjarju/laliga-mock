
/* JQuery code */

$(document).ready(function(){
    $(".datefield").datepicker($.extend({}, $.datepicker.regional['es'], {
        dateFormat: 'yy-mm-dd',  
        appendTo: 'body',         // ensures correct positioning
        showAnim: 'fadeIn'        
    }));
});
