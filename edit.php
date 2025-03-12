<?php
$csvFile = 'payroll.csv';
$employees = [];

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== FALSE) {
        $employees[] = $data;
    }
    fclose($handle);
}

$id = $_GET['id'] ?? null;
$employee = null;

// Find the employee by ID
foreach ($employees as $index => $emp) {
    if ($index != 0 && $emp[0] == $id) {
        $employee = $emp;
        break;
    }
}

if (!$employee) {
    die("Employee not found!");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Employee</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Edit Employee</h2>
        <form action="process.php?action=edit" method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($employee[0]) ?>">
            
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($employee[1]) ?>" required>

            <label for="salary">Salary:</label>
            <input type="number" id="salary" name="salary" value="<?= htmlspecialchars($employee[2]) ?>" required>

            <button type="submit" class="btn">Update Employee</button>
            <button type="submit" a href="index.php" class="btn" id ="back">Back to Payroll</button>
        </form>
        
       
    </div>
</body>
</html>