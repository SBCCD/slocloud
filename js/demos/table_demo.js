$(function() {
    
    var table_1 = $('#table-1').dataTable({
        "ajax": {
            'url': './ajax.php'
        },
        "processing": false,
        "serverSide": true
        
    })
    
    $('#table-1 tbody').on('click', 'tr', function(){
        
        esta = $(this);
        
        if(esta.hasClass('info')) {
            esta.removeClass('info')
        } else {
            table_1.$('tr.info').removeClass('info')
            esta.addClass('info')
        }
        
        console.log(esta.attr('id'))
        
    })
    
})