<?php
$servername = "localhost";
$username = "root";
$password = "";
$pdo = new PDO("mysql:host=$servername;dbname=todo;charset=utf8" , $username , $password);




$description = "";
$action = !empty($_GET['action']) ? $_GET['action'] : null;
$orderBy = "date_added";

$sortVariants = ['date_added', 'description', 'is_done'];

if (isset($_POST['sort']) && !empty($_POST['sort_by']) && in_array($_POST['sort_by'], $sortVariants)) {
    $orderBy = $_POST['sort_by'];
}

if (!isset($_GET['id']) && isset($_POST['save']) && !empty($_POST['description'])) {
    $description = $_POST['description'];
    $sql = "INSERT INTO tasks (description, date_added) VALUES (?, NOW())";
    $stm = $pdo->prepare($sql);
    $stm->execute([
        $description
    ]);


}

if (!empty($action) && !empty($_GET['id'])) {
    $id = (int)$_GET['id'];

    if ($action == 'delete') {
        $sql = "DELETE FROM tasks WHERE id = ?";
        $stm = $pdo->prepare($sql);
        $stm->execute([
            $id
        ]);
    }

    if ($action == 'done') {
        $sql = "UPDATE tasks SET is_done = 1 WHERE id = ?";
        $stm = $pdo->prepare($sql);
        $stm->execute([
            $id
        ]);


    }

    if (!empty($_POST['description'])) {
        $description = $_POST['description'];

        $sql = "UPDATE tasks SET description = ? WHERE id = ?";
        $stm = $pdo->prepare($sql);
        $stm->execute([
            $description,
            $id
        ]);


    }

    if ($action == 'edit') {
        $sql = "SELECT description FROM tasks WHERE id = ?";
        $stm = $pdo->prepare($sql);
        $stm->execute([$id]);

        $description = $stm->fetchColumn();
    }
}



$sql = "SELECT * FROM tasks ORDER BY $orderBy";
$stm = $pdo->prepare($sql);
$stm->execute();

$tasks = $stm->fetchAll();

?>

<style>
    table {
        border-spacing: 0;
        border-collapse: collapse;
    }

    table td, table th {
        border: 1px solid #ccc;
        padding: 5px;
    }

    table th {
        background: #eee;
    }
</style>

<h1>Список дел на сегодня</h1>
<div style="float: left">
    <form method="POST">
        <input type="text" name="description" placeholder="Описание задачи" value="<?=$description?>" />
        <input type="submit" name="save" value="<?php echo ($action == 'edit' ? 'Сохранить' : 'Добавить') ?>" />
    </form>
</div>
<div style="float: left; margin-left: 20px;">
    <form method="POST">
        <label for="sort">Сортировать по:</label>
        <select name="sort_by">
            <option value="date_created">Дате добавления</option>
            <option value="is_done">Статусу</option>
            <option value="description">Описанию</option>
        </select>
        <input type="submit" name="sort" value="Отсортировать" />
    </form>
</div>
<div style="clear: both"></div>

<?php

echo "<table>";
echo "
    <tr>
        <th>Описание задачи</th>
        <th>Дата добавления</th>
        <th>Статус</th>
        <th></th>
    </tr>\n";
foreach ($tasks as $row) {
    echo "<tr>\n";
    echo "  <td>" . $row['description'] . "</td>\n";
    echo "  <td>" . $row['date_added'] . "</td>\n";
    echo "  <td>" . ($row['is_done'] ? "<span style='color: green;'>Выполнено</span>" : "<span style='color: orange;'>В процессе</span>") . "</td>\n";
    echo "  <td>
        <a href='?id=" . $row['id'] . "&action=edit'>Изменить</a>
        <a href='?id=" . $row['id'] . "&action=done'>Выполнить</a>
        <a href='?id=" . $row['id'] . "&action=delete'>Удалить</a>
    </td>\n";
    echo "</tr>\n";
}
echo "</table>";