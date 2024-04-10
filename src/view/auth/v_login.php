<div class="login">
    <form method="POST" id="form_login" class="container">
        <div class="form-group row justify-content-center">
            <?php if (isset($errors['auth'])) :?>
                <div class="form-group invalid-feedback text-center <?=isset($errors['auth']) ? 'auth-text' : '' ?>">
                    <?=$errors['auth'] ?>
                </div>
            <?php endif ?>
            <div class="col-xs-12 col-sm-10 col-md-5 col-lg-4">
                <input
                    type="text"
                    name="login"
                    class="form-control <?=isset($errors['login']) ? 'is-invalid' : '' ?>"
                    value="<?=$fields['login'] ?? '' ?>"
                    placeholder="Логин"
                >
                <div class="invalid-feedback"><strong><?=$errors['login'] ?? '' ?></strong></div>
            </div>
        </div>
        <div class="form-group row justify-content-center">
            <div class="col-xs-12 col-sm-10 col-md-5 col-lg-4">
                <input
                    type="password"
                    name="password"
                    class="form-control <?=isset($errors['password']) ? 'is-invalid' : '' ?>"
                    value="<?=$fields['password'] ?? '' ?>"
                    placeholder="Пароль"
                >
                <div class="invalid-feedback"><strong><?=$errors['password'] ?? '' ?></strong></div>
            </div>
        </div>
        <div class="form-group row justify-content-center">
            <div class="col-xs-12 col-sm-10 col-md-5 col-lg-4 d-flex justify-content-center">
                <label class="form-check-label">
                    <input
                            type="checkbox"
                        <?=isset($fields['remember']) && $fields['remember'] ? 'checked': '' ?>
                            name="remember"
                            class="form-check-input"
                    > Запомнить
                </label>
                <input type="submit" class="btn btn-secondary" value="Войти">
            </div> 
        </div>
    </form>
</div>
