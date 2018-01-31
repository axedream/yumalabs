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
                                                ?>
                                                    <span class="glyphicon glyphicon-pencil" id="edit" st="<?= $u['id']?>" fio="<?= $u['fio']?>"></span>
                                                <?php
                                                    }
                                                ?>

                                                <?php
                                                    if (($user['groupe'] == 10) && ($user['login']!=$u['login']) && (!in_array($u['login'],['admin','guest']))) {

                                                ?>
                                                    <span class="glyphicon glyphicon-trash"  id="delete" st="<?= $u['id']?>" fio="<?= $u['fio']?>"></span>
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
                        <div class="col-md-6">...</div>
                        <div class="col-md-6">...</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var user_id = 0;
    var user_fio =0;
    $(function(){
        $(".bshow").on('click',function (e) {
            user_id = $(this).attr('st');
            user_fio = $(this).attr('fio');

            $("#gridModalLabel").text('Просмотр пользователя: ' + user_fio);
            $("#form_dialog").modal('show');
            e.preventDefault();
            return false;
        });

    });
</script>