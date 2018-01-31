$(function () {
    $("#logout").on('click',function(e){
        window.location.href = this_host + 'user/logout'
        e.preventDefault;
        return false;
    });
});

