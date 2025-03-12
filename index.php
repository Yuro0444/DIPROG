<?php
$csvFile = 'payroll.csv';

// Check if CSV file exists, else create it
if (!file_exists($csvFile)) {
    file_put_contents($csvFile, "ID,Name,Salary,SSS,PhilHealth,Pag-Ibig,Taxable Income,Tax Due,Net Income\n");
}

// Read CSV file
$employees = [];
if (($handle = fopen($csvFile, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",", '"', "\\")) !== FALSE) {
        $employees[] = $data;
    }
    fclose($handle);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Payroll System</title>
    <link rel="stylesheet" href="styles.css">  <!-- Link to External CSS -->
</head>
<body>
    <h2>Payroll System</h2>

    <!-- Display Employees in a Table -->
    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Salary</th><th>SSS</th><th>PhilHealth</th><th>Pag-Ibig</th><th>Taxable Income</th><th>Tax Due</th><th>Net Income</th><th>Actions</th>
        </tr>
        <?php foreach ($employees as $index => $employee): if ($index == 0) continue; ?>
        <tr>
            <?php foreach ($employee as $data): ?>
                <td><?= htmlspecialchars($data) ?></td>
            <?php endforeach; ?>
            <td>
                <a href="edit.php?id=<?= $employee[0] ?>" class="btn btn-edit">Edit</a> 
                <a href="process.php?action=delete&id=<?= $employee[0] ?>" class="btn btn-delete" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>Add Employee</h3>
    <form action="process.php?action=add" method="post">
        <input type="text" name="id" placeholder="Employee ID" required>
        <input type="text" name="name" placeholder="Name" required>
        <input type="number" name="salary" placeholder="Salary" required>
        <button type="submit">Add Employee</button>
    </form>
</body>
</html>
