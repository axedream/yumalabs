/**
 * Created by Пользователь on 01.02.2018.
 */
/*
 пример подключения
 <script type="text/javascript" src="/lib/nsk/js/ajax.js"></script>
 */

/*
 Пример использования
 <script type="application/javascript">
 function call_back(msg) {
 console.log (msg);
 }
 function getDataMember(id) {
    ajax_request({ action : 'function', data : { ticket_id : $("#ticket_id").val(), id:id, val:$("#"+id).val() } },call_back);
 }
 </script>
 */

function ajax_request(input,call_back) {
    if (input) {
        if (input.action && input.data) {
            var output = new Object();

            /*Действие*/
            output.data = input.data;

            output = JSON.stringify(output);
            /*Преобразуем в JSON олбъект*/

            $.ajax({
                url: this_host+'ajax/'+input.action,
                type: 'POST',
                dataType: 'JSON',
                data: {'output': output},
                cache: false,
                success: function (msg){
                    call_back(msg);
                }
            });
            /*end ajax*/
        }
    }
}/* end function from med set*/
