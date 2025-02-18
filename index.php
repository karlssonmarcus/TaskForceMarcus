<?php
$filename = 'todos.json';

// Läs in uppgifterna från filen eller starta med en tom array
if (file_exists($filename)) {
    $todosContent = file_get_contents($filename);
    $todos = json_decode($todosContent, true);
    if (!is_array($todos)) {
        $todos = [];
    }
} else {
    $todos = [];
}

// Hantera POST-förfrågningar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // TA BORT uppgift
    if (isset($_POST['delete_index'])) {
        $deleteIndex = intval($_POST['delete_index']);
        if (isset($todos[$deleteIndex])) {
            unset($todos[$deleteIndex]);
            $todos = array_values($todos); // omindexera arrayen
            file_put_contents($filename, json_encode($todos, JSON_PRETTY_PRINT));
        }
        header('Location: index.php');
        exit;
    }

    // LÄGG TILL en ny uppgift
    if (isset($_POST['new_todo'])) {
        $newTodoText = trim($_POST['new_todo']);
        if ($newTodoText !== '') {
            $todos[] = ['text' => $newTodoText, 'completed' => false];
            file_put_contents($filename, json_encode($todos, JSON_PRETTY_PRINT));
        }
        header('Location: index.php');
        exit;
    }

    // REDIGERA en uppgift
    if (isset($_POST['edit_index']) && isset($_POST['edited_todo'])) {
        $editIndex = intval($_POST['edit_index']);
        $editedTodoText = trim($_POST['edited_todo']);
        if ($editedTodoText !== '' && isset($todos[$editIndex])) {
            $todos[$editIndex]['text'] = $editedTodoText;
            $todos[$editIndex]['completed'] = false;
            file_put_contents($filename, json_encode($todos, JSON_PRETTY_PRINT));
        }
        header('Location: index.php');
        exit;
    }

    // KLARMARKERA en uppgift
    if (isset($_POST['complete_index'])) {
        $completeIndex = intval($_POST['complete_index']);
        if (isset($todos[$completeIndex])) {
            $todos[$completeIndex]['completed'] = true;
            file_put_contents($filename, json_encode($todos, JSON_PRETTY_PRINT));
        }
        header('Location: index.php');
        exit;
    }
}

// Dela uppgifterna i aktiva och klarmarkerade
$activeTodos = [];
$completedTodos = [];
foreach ($todos as $index => $todo) {
    if (isset($todo['completed']) && $todo['completed'] === true) {
        $completedTodos[] = ['index' => $index, 'task' => $todo];
    } else {
        $activeTodos[] = ['index' => $index, 'task' => $todo];
    }
}

// Aktiva uppgifter först, sedan klarmarkerade
$displayTodos = array_merge($activeTodos, $completedTodos);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Taskforce</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Taskforce</h1>

        <!-- Formulär för att lägga till en ny uppgift -->
        <form method="POST" action="">
            <input type="text" name="new_todo" placeholder="Ny uppgift" required>
            <button type="submit">Lägg till i listan</button>
        </form>

        <?php
        // Hämta "edit_index" från GET-parametrarna (detta är det faktiska indexet)
        $editIndex = isset($_GET['edit_index']) ? intval($_GET['edit_index']) : -1;
        ?>
        <ul>
            <?php foreach ($displayTodos as $item): ?>
                <?php
                $taskIndex = $item['index'];
                $task = $item['task'];
                $taskText = $task['text'];
                $isCompleted = $task['completed'];
                ?>
                <li class="<?php echo $isCompleted ? 'completed' : ''; ?>">
                    <?php if ($editIndex === $taskIndex): ?>
                        <!-- Redigeringsformulär för vald uppgift -->
                        <form method="POST" action="">
                            <input type="hidden" name="edit_index" value="<?php echo $taskIndex; ?>">
                            <input type="text" name="edited_todo" value="<?php echo htmlspecialchars($taskText); ?>" required>
                            <button type="submit">Spara</button>
                            <a href="index.php">Avbryt</a>
                        </form>
                    <?php else: ?>
                        <div class="todo-content">
                            <span class="todo-text"><?php echo htmlspecialchars($taskText); ?></span>
                            <div class="todo-actions">
                                <form method="POST" action="" class="action-form">
                                    <input type="hidden" name="delete_index" value="<?php echo $taskIndex; ?>">
                                    <button type="submit">Ta bort</button>
                                </form>
                                <form method="GET" action="" class="action-form">
                                    <input type="hidden" name="edit_index" value="<?php echo $taskIndex; ?>">
                                    <button type="submit">Redigera</button>
                                </form>
                                <?php if (!$isCompleted): ?>
                                    <form method="POST" action="" class="action-form">
                                        <input type="hidden" name="complete_index" value="<?php echo $taskIndex; ?>">
                                        <button type="submit" class="complete-button">Klar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</body>
</html>
