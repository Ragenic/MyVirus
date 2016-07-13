$(function(){
    /*$('.login-btn').click(function(){
     alert("click");
     return false;
     });*/

    $('.login-form').submit(function(e){
        e.preventDefault();
        var formData = $(this).serializeArray();
        //console.log(formData);

        $.ajax({
            url: "ajax.php",
            type: "post",
            dataType: "json",
            data: formData,
            success: function(response){
                console.log(response);
                if(response === "OK"){
                    $('.form-group').removeClass('has-error');
                    alert('Вы авторозованы!');
                }else{

                    $('.form-group').removeClass('has-error');

                    for(var key in  response){
                        console.log(response[key]);
                        $('input[name="' + response[key] + '"]').parent().addClass('has-error');
                    }
                }
            }
        });

    });
});