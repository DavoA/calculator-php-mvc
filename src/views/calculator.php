<!DOCTYPE html>
<html>
<head>
    <title>Calculator</title>
    <link rel="stylesheet" href="<?= URLROOT; ?>/css/style.css">
</head>
<body>
    <div class="calculator">
        <form action="<?= URLROOT; ?>/calculate/submit" method="POST">
            <input type="text" class="display" name="display" value="<?php echo htmlspecialchars(isset($data['result']) ? $data['result'] : '0'); ?>" readonly>
            <div class="buttons">
                <?php
                $buttons = [
                    ['C', 'clear'], ['÷', 'operator'], ['×', 'operator'], ['⌫', 'backspace'],
                    ['7', ''], ['8', ''], ['9', ''], ['-', 'operator'],
                    ['4', ''], ['5', ''], ['6', ''], ['+', 'operator'],
                    ['1', ''], ['2', ''], ['3', ''], ['=', 'equals'],
                    ['0', 'zero'], ['.', '']
                ];
                
                foreach ($buttons as $button) {
                    echo "<button type='submit' name='button' value='{$button[0]}'" . 
                         ($button[1] ? " class='{$button[1]}'" : "") . 
                         ">{$button[0]}</button>";
                }
                ?>
            </div>
        </form>
        <a href="<?= URLROOT; ?>/calculate/history">View History</a>
    </div>
</body>
</html>