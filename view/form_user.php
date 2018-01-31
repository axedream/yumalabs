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
                                                <span class="glyphicon glyphicon-search" id="show" st="<?= $u['id']?>"></span>

                                                <?php
                                                    if (($user['groupe'] == 10) OR ($u['login']==$user['login'])) {
                                                ?>
                                                    <span class="glyphicon glyphicon-pencil" id="edit" st="<?= $u['id']?>"></span>
                                                <?php
                                                    }
                                                ?>

                                                <?php
                                                    if (($user['groupe'] == 10) && ($user['login']!=$u['login']) && (!in_array($u['login'],['admin','guest']))) {

                                                ?>
                                                    <span class="glyphicon glyphicon-trash"  id="delete" st="<?= $u['id']?>"></span>
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