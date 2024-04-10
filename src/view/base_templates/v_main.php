<!DOCTYPE html>
<html lang="ru">
	<head>
		<base href="<?=DOMAIN . BASE_URL?>">
		<title><?=$title?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
		<meta content="text/html; charset=utf-8" http-equiv="content-type">
        <link type="image/png" sizes="32x32" rel="icon" href="/img/favicon.png">
		<meta name="keywords" content="<?=$keywords?>">
		<meta name="description" content="<?=$description?>">
        <?php foreach ($styles as $style): ?>
            <link rel="stylesheet" href="/<?=CSS_DIR . $style?>" />
        <?php endforeach; ?>

	</head>
	<body>
		<div class="wrapper">
			<header>
				<div class="top-menu">
					<nav class="navbar navbar-expand-md"> 
						<div class="container">							
						  <button class="navbar-toggler mr-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
						    <span class="navbar-toggler-icon"></span>
						  </button>
			  			  <div class="collapse navbar-collapse" id="navbarSupportedContent">
			  			   	<div class="navbar-nav mr-auto">
			  			   		<a class="nav-item nav-link active" href="/">Главная</a>
			  			   		<a class="nav-item nav-link" href="https://docs.google.com/document/d/1AK072kRvhqHQRuvslQdLUwBpPz-bSLSC5f0hzy0QpJo/edit?usp=sharing/test" target="_blank">Задание</a>

			  			   	</div>
			  			   	<div class="auth navbar-nav">
                                <a class="nav-item nav-link" href="/add">Создать задачу</a>
								<?php if (is_null($user)) : ?>
                                    <a class="nav-item nav-link" href="/admin">Войти</a>
			  			   		<?php else: ?>
			  			   			<a class="nav-item nav-link text-warning" href="/my-tasks">
                                        Мои задачи
                                    </a>
                                    <a class="nav-item nav-link text-red" href="/views">
                                        Просмотры
                                    </a>
			  			   			<a class="nav-item nav-link" href="/logout">Выход (<b><?=$user['login']?></b>)</a>
			  			   		<?php endif ?>
			  			   	</div>		  			   		
			  			  </div>
						</div>
					</nav>
				</div>
			</header>

			<div class="container content">

                <?php if (isset($_SESSION['success'])) :?>
                    <div class="container">
                        <div class="alert alert-success mt-3 mb-0">
                           <?=$_SESSION['success'];?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    </div>
                <?php endif ?>

				<?=$content?>
			</div>
			<footer>
				<div class="container">
					<p>Copyright <?=date('Y');?> <a href="https://andreiikachanov.ru">andreiikachanov.ru.</a> Все права защищены.</p>
					<p>Development - <a href="https://vk.com/id10398369">Andreii Kachanov</a></p>
				</div>
			</footer>
		</div>

		<!-- Modal -->
		<div  class="modal fade" id="cart" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
		  <div class="modal-dialog modal-dialog-centered" role="document">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
		          <span aria-hidden="true">&times;</span>
		        </button>
		      </div>
		      <div class="modal-body" id="modal-body">
		      
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
		      </div>
		    </div>
		  </div>
		</div>

        <?php foreach ($scripts as $script): ?>
            <script src="/<?= JS_DIR . $script ?>"></script>
        <?php endforeach; ?>
	</body>
</html>