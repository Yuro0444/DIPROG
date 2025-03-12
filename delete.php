<?php
if (isset($_GET['id'])) {
    $idToDelete = $_GET['id'];
    $csvFile = "payroll.csv";
    $rows = [];
    $header = [];

    // Open and read the CSV file
    if (($handle = fopen($csvFile, "r")) !== FALSE) {
        $header = fgetcsv($handle); // Read and store header row

        while (($data = fgetcsv($handle)) !== FALSE) {
            if ($data[0] != $idToDelete) { 
                // Keep only rows that are NOT the one to delete
                $rows[] = $data;
            }
        }
        fclose($handle);
    }

    // Write back to the CSV file with the header row
    if (($handle = fopen($csvFile, "w")) !== FALSE) {
        fputcsv($handle, $header); // Write header first
        foreach ($rows as $row) {
            fputcsv($handle, $row); // Write remaining rows
        }
        fclose($handle);
    }

    // Alert and redirect
    echo "<script>alert('Employee Deleted!'); window.location.href='index.php';</script>";
}
?>
