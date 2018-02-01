<div class="container-fluid vertical-center" style="padding-top: 10px;" >
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="col-md-2">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">Пользователь:</h3>
                        </div>
                        <div class="panel-body">
                            <table class="table">
                                <tr>
                                    <td>Логин:</td>
                                    <td><?= $user['login']?></td>
                                </tr>
                                <tr>
                                    <td>ФИО:</td>
                                    <td><?= $user['fio']?></td>
                                </tr>
                                <tr>
                                    <td>Доступ:</td>
                                    <td><?= (new UserModel())->get_user_role($user['groupe'])?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="panel-footer">
                            <button type="button" class="btn btn-default" id="logout">Выход</button>
                        </div>
                    </div>


                </div>

                <div class="col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3 class="panel-title">Список пользователей в системе:</h3>
                        </div>
                        <div class="panel-body">
                            <table class="table">
                                <tr>
                                    <th>Логин</th>
                                    <th>ФИО</th>
                                    <th>Роль</th>
                                    <th>Действие</th>
                                </tr>
                                <?php
                                    if (is_array($user['list'])) {
                                        foreach ($user['list'] as $u) {
                                ?>
                                        <tr>
                                            <td><?= $u['login'] ?></td>
                                            <td><?= $u['fio']?></td>
                                            <td><?= (new UserModel())->get_user_role($u['groupe'])?></td>
                                            <td>
                                                <span class="glyphicon glyphicon-search bshow" st="<?= $u['id']?>" fio="<?= $u['fio']?>"></span>

                                                <?php
                                                    if (($user['groupe'] == 10) OR ($u['login']==$user['login'])) {
                                                        if ($u['id']!=2) {
                                                ?>
                                                            <span class="glyphicon glyphicon-pencil bedit"  st="<?= $u['id']?>" fio="<?= $u['fio']?>"></span>
                                                <?php
                                                        }
                                                    }
                                                ?>

                                                <?php
                                                    if (($user['groupe'] == 10) && ($user['login']!=$u['login']) && (!in_array($u['login'],['admin','guest']))) {

                                                ?>
                                                    <span class="glyphicon glyphicon-trash bdelete"  st="<?= $u['id']?>" fio="<?= $u['fio']?>"></span>
                                                <?php
                                                    }
                                                ?>

                                            </td>
                                        </tr>
                                <?php
                                        }
                                    }
                                ?>
                            </table>
                        </div>
                        <?php if ($user['groupe'] == 10) :?>
                            <div class="panel-footer">
                                <button type="button" class="btn btn-default" id="create">Создать пользователя</button>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>


                <div class="col-md-4">

                </div>
			</div>
		</div>
	</div>
</div>


<div id="form_dialog" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="gridModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Закрыть"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="gridModalLabel">Заголовок модального окна</h4>
            </div>
            <div class="modal-body">
                <!-- основное содержимое (тело) модального окна -->
                <div class="container-fluid">
                    <!-- Контейнер, в котором можно создавать классы системы сеток -->
                    <div class="row">
                        <div class="row">
                            <div class="col-md-12" id="gridModalLoadin"><img class='center-block imgLoading' src='<?= BASIC_URL_FULL ?>view/img/35.gif'></div>
                            <div class="col-md-12" id="gridModalLoadinText"></div>
                        </div>
                        <div class="col-md-12" id="gridModalBody"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="row">
                    <div class="col-md-6" style="text-align: left;">
                        <button type="button" class="btn btn-default editButton" >Изменить</button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-default right" data-dismiss="modal">Закрыть</button>
                    </div>
                </div>


            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var user_id = 0;
    var user_fio =0;
    var block_button_edit = 0;

    function _before_send(){
        $(".editButton").hide();
        $(".imgLoading").show();
        $("#gridModalLoadinText").html("<span class='center-block' style='text-align: center; color: orangered;'><h4>Подождите! Идет обработка запроса...</h4></span>")
        $("#gridModalBody").text("");
    }
    function _after_send(){
        $(".imgLoading").hide();
        $("#gridModalLoadinText").text("")
    }



    function get_user_info(){
        $.ajax({
            url: this_host+'ajax/get_data_user/',
            type: 'POST',
            dataType: 'JSON',
            data: {'input_data': {'user_id':user_id}},
            cache: false,
            success: function (msg){
                if (!msg.error) {
                    $("#gridModalBody").html(msg.msg.table);
                } else {
                    $("#gridModalBody").text("Не достаточно прав для совершения данной операции!");
                }
                console.log(msg);
            },
            beforeSend: function(){
                _before_send();
            },
            complete: function(){
                _after_send();
            },
        });
    }

    function set_user_info(object){
        $.ajax({
            url: this_host+'ajax/edit_user/',
            type: 'POST',
            dataType: 'JSON',
            data: {'input_data': object},
            cache: false,
            success: function (msg){
                if (!msg.error) {
                    $("#gridModalBody").html(msg.msg.text);
                } else {
                    $("#gridModalBody").text("Не достаточно прав для совершения данной операции!");
                }
                console.log(msg);
            },
            beforeSend: function(){
                _before_send();
            },
            complete: function(){
                _after_send();
                block_button_edit = 0;
            },
        });
    }


    function edit_user_action(){
        $(".editButton").on('click', function(){
            if (!block_button_edit) {
                block_button_edit = 1;
                set_user_info({'user_id':$("#re_user_id").val() ,'fio':$("#re_fio").val(),'email':$("#re_email").val(),'passwd':$("#re_passwd").val(),'groupe':$("#re_groupe").val()});
            }

        });
    }


    function edit_user_form(){
        $.ajax({
            url: this_host+'ajax/edit_data_user_form/',
            type: 'POST',
            dataType: 'JSON',
            data: {'input_data': {'user_id':user_id}},
            cache: false,
            success: function (msg){
                if (!msg.error) {
                    $("#gridModalBody").html(msg.msg.table);
                } else {
                    $("#gridModalBody").text("Не достаточно прав для совершения данной операции!");
                }
                $(".editButton").show();
                edit_user_action();
                console.log(msg);
            },
            beforeSend: function(){
                _before_send();
            },
            complete: function(){
                _after_send();
            },
        });
    }



    $(function(){
        $("#gridModalLoading").hide();

        $(".bedit").on('click',function (e) {
            user_id = $(this).attr('st');
            user_fio = $(this).attr('fio');
            edit_user_form();
            $("#gridModalLabel").text('Редактирование пользователя: ' + user_fio);
            $("#form_dialog").modal('show');

            e.preventDefault();
            return false;
        })

        $(".bshow").on('click',function (e) {
            user_id = $(this).attr('st');
            user_fio = $(this).attr('fio');
            get_user_info();
            $("#gridModalLabel").text('Просмотр пользователя: ' + user_fio);
            $("#form_dialog").modal('show');
            e.preventDefault();
            return false;
        });

    });
</script>