<div class="container-fluid" style="padding-top: 10px;">
	<div class="row">
		<div class="col-md-12">
			<div class="row">
				<div class="col-md-3">
				</div>
				<div class="col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h<?=$size_message_wrong?>>
                                <?= $text_message_wrong ?>
                            </h<?=$size_message_wrong?>>
                            <?php if ($auth_login) : ?>
                            <h3>
                                <span class="label label-danger">Ошибка ввода данных. Проверьте правильность ввода полей логин/пароль</span>
                            </h3>
                            <?php endif; ?>
                        </div>
                        <div class="panel-body">
                            <form role="form" name="auth" method="post" action="<?= BASIC_URL_FULL?>user/login">
                                <div class="form-group">
                                    <label for="login">
                                        Логин
                                    </label>
                                    <input type="login" class="form-control" id="login" name="login"/>
                                </div>
                                <div class="form-group">
                                    <label for="passwd">
                                        Пароль
                                    </label>
                                    <input type="password" class="form-control" id="passwd" name="passwd" />
                                </div>
                                <button type="submit" class="btn btn-default">
                                    Войти
                                </button>
                                <?php if ($auth_true==0) : ?>
                                    <a class="btn btn-primary" href="" id="register" role="button">Регистрация</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
				</div>
				<div class="col-md-3">
				</div>
			</div>
		</div>
	</div>
</div>