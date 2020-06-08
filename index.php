<?php
// Подключение к Базе данных
$conn = mysqli_connect('127.0.0.1', 'root', '', 'news_db');
if (!$conn){
    echo 'ERROR';
    exit();
}

// Создание новой новости
if (isset($_POST['create'])){
    $title = htmlspecialchars($_POST['title']);
    $image = time().'.'.pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION);
    $description = htmlspecialchars($_POST['description']);
        $dir = 'img/'.$image;
        if ($_FILES['image']['size']>5100000){
            $msgSize =  'Size of image should be less than 5MB';
        }
        else{
            mysqli_query($conn, "insert into news (title, image, description) values ('$title', '$image', '$description')");
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $dir)) {
                    $created = "Created Successfully";
                } else {
                    $upload = "Error when uploading image";
                }
        }
}

// Удаление новости
if (isset($_POST['delete'])){
    $id = $_POST['id'];
    mysqli_query($conn, "delete from news where id = $id");
    $deleted = 'Deleted';
}

// Редактирование новости
if (isset($_POST['edit'])){
    $id = $_POST['id'];
    $title = htmlspecialchars($_POST['title']);
    $time = time();
    $image = $time.'.'.pathinfo($_FILES['image']['name'],PATHINFO_EXTENSION);
    if (!$_FILES['image']['name']) {
        $image = mysqli_fetch_assoc(mysqli_query($conn, "select image from news where id = $id"));
        $image = $image['image'];
    }
    $description = htmlspecialchars($_POST['description']);
        $dir = 'img/'.$image;
        if ($_FILES['image']['size']>5100000){
            $msgSize =  'Size of image should be less than 5MB';
        }
        else{
            $updated_at = date('Y-m-d H:i:s');
            mysqli_query($conn, "update news set title = '$title',  image = '$image', description = '$description', updated_at = '$updated_at' where id = $id");
            $updated = "Updated Successfully";
            if ($_FILES['image']['name']){
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $dir)) {
                } else {
                    $upload = "Error when uploading image";
                }
            }
        }
}

// Список новостей из БД
$res = mysqli_query($conn, "Select * from news");
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>News</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>

<!--Уведомительные сообщения-->
<?
if ($msgSize || $upload) {
        echo "<div id=\"M\" class=\"position-absolute justify-content-center d-flex text-center w-100 m-4\">
            <h1 class=\"bg-danger text-white p-2\">$upload $msgSize</h1>
          </div>";
    }
else if ($updated){
    echo "<div id=\"U\" class=\"position-absolute justify-content-center d-flex text-center w-100 m-4\">
            <h1 class=\"bg-info text-white p-2\">$updated</h1>
          </div>";
}
else if ($created){
    echo "<div id=\"C\" class=\"position-absolute justify-content-center d-flex text-center w-100 m-4\">
            <h1 class=\"bg-success text-white p-2\">$created</h1>
          </div>";
}
else if ($deleted){
    echo "<div id=\"D\" class=\"position-absolute justify-content-center d-flex text-center w-100 m-4\">
            <h1 class=\"bg-warning text-dark p-2\">$deleted</h1>
          </div>";
}
?>
<!--Уведомительные сообщения-->
<main>
    <div>
        <h1 class="border-bottom text-center m-2 p-2">News</h1>
    </div>
    <button id="addbtn" onclick="AddFun()" class="btn btn-outline-success position-fixed m-1"
            style="display: block">
        <i class="fa fa-plus"></i>
    </button>

    <!--Форма создания статьи-->
    <div class="justify-content-center d-flex">
        <div id="dialog" class="rounded-lg bg-dark position-fixed p-3 m-5 border" style="z-index: 2; display: none;">
            <div>
                <button onclick="AddFun()" class="position-absolute btn btn-danger" style="right: 1%">
                    X</button>
                <h3 class="font-weight-normal border-bottom ml-5 mr-5 text-light text-center">Creating</h3>
            </div>
            <form action="index.php" method="post" enctype="multipart/form-data">
                <div class="m-3">
                    <input class="w-100 border p-1 rounded-lg" maxlength="250" required type="text" name="title" placeholder="Title">
                </div>
                <div class="m-3">
                    <input class="w-100 border text-light p-1 rounded-lg" required type="file" accept=".jpg, .jpeg, .png" name="image">
                </div>
                <div class="m-3">
                    <textarea class="rounded-lg p-1" cols="100" rows="8" name="description" required placeholder="Description" style="resize: none;"></textarea>
                </div>
                <div class="m-3">
                    <input class="btn w-100 btn-outline-light" type="submit" name="create" value="Create">
                </div>
            </form>
        </div>
    </div>
    <!--Форма создания статьи-->

    <!--Список новостей-->
    <div class="d-flex justify-content-center m-4">
        <div class="w-75">
           <?
           while ($news = mysqli_fetch_assoc($res)){
           ?>
        <div class="card m-3">
            <div class="card-body">

                <!--Форма для модификации/удаления новостей-->
                <form class="row" action="index.php" method="post" enctype="multipart/form-data">

                    <!--Загаловок статьи-->
                   <div class="col-12">
                       <input name="title" required class="input-group-text input-group" type="text" value="<?=$news['title']?>">
                   </div>
                    <!--Загаловок статьи-->

                   <div class="col-4 m-3">

                       <!--Картинка статьи-->
                       <div style="height: 300px; background: url('img/<?=$news['image']?>') no-repeat center;
                               background-size: 300px 300px"></div>
                       <!--Картинка статьи-->

                       <!--Изменение картинки-->
                       <div class="m-2">
                           <input type="file" accept=".jpg, .jpeg, .png" name="image">
                        </div>
                       <!--Изменение картинки-->
                   </div>

                    <!--Текст Статьи-->
                <div class="col m-3 border rounded-lg p-0">
                       <textarea name="description" required class="w-100 h-100 border-0 rounded-lg p-1" style="resize: none"><?=$news['description']?></textarea>
                   </div>
                    <!--Текст Статьи-->

                    <!--Время создания и изменения публикации-->
                    <div class="col-12">
                        <p class="text-muted">Created: <?=$news['created_at']?></p>
                        <p style="display: <?=($news['updated_at'])?'block': 'none'?>">
                            <em class="text-muted">Updated: <?=$news['updated_at']?></em>
                        </p>
                    </div>
                    <!--Время создания и изменения публикации-->
                <div class="col-12 text-center">

                    <!--Скрытый Айди для нахождения в Базе данных-->
                        <input hidden name="id" type="text" value="<?=$news['id']?>">
                    <!--Скрытый Айди для нахождения в Базе данных-->

                    <!--Удаление публикаций-->
                        <div class="d-inline m-2">
                            <button name="delete" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                        </div>
                    <!--Удаление публикаций-->

                    <!--Редактирование публикаций-->
                        <div class="d-inline m-2">
                            <button name="edit" type="submit" class="btn btn-info"><i class="fa fa-edit"></i></button>
                        </div>
                    <!--Редактирование публикаций-->
                    </form>
                <!--Форма для модификации/удаления новостей-->
                </div>
            </div>
        </div>
           <?
           }
           ?>
        </div>
    <!--Список новостей-->
    </div>
</main>
<script>
    // Функция для переключения между формочкой создания, и кнопкой которая ее активирует
    function AddFun(){
        var d = document.getElementById('dialog').style.display;
        document.getElementById('dialog').style.display = document.getElementById('addbtn').style.display;
        document.getElementById('addbtn').style.display = d;
    }

    // Для всплывающих сообщений, которые должны исчезнутьчерез 3 секунды
    setTimeout(fade_out, 3000);
    function fade_out() {
        $("#M").fadeOut().empty();
        $("#C").fadeOut().empty();
        $("#U").fadeOut().empty();
        $("#D").fadeOut().empty();
    }

</script>
</body>
</html>
