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
                                    <td>Параметр</td>
                                    <td>Значение</td>
                                </tr>
                                <tr>
                                    <td>Логин:</td>
                                    <td><?= $user['login']?></td>
                                </tr>
                                <tr>
                                    <td>ФИО:</td>
                                    <td><?= $user['fio']?></td>
                                </tr>
                                <tr>
                                    <td>Доступ</td>
                                    <td><?= $user['groupe']?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="panel-footer">
                            <button type="button" class="btn btn-default" id="logout">Выход</button>
                        </div>
                    </div>


                </div>
                <div class="col-md-6">

                </div>
                <div class="col-md-4">
                </div>
			</div>
		</div>
	</div>
</div>