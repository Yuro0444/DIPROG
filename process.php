<?php
$csvFile = 'payroll.csv';
$taxRatesFile = 'tax_rates.csv';

// Load tax brackets from CSV
function loadTaxRates() {
    $rates = [];
    if (($handle = fopen("tax_rates.csv", "r")) !== FALSE) {
        fgetcsv($handle); // Skip header
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $rates[$data[0]][] = [
                'min' => floatval($data[1]),
                'max' => floatval($data[2]),
                'value' => floatval($data[3])
            ];
        }
        fclose($handle);
    }
    return $rates;
}

// Get correct rate based on salary
function getRate($category, $salary, $rates) {
    if (!isset($rates[$category])) return 0;
    foreach ($rates[$category] as $rate) {
        if ($salary >= $rate['min'] && $salary <= $rate['max']) {
            return $rate['value'];
        }
    }
    return 0;
}

// SSS Calculation
function calculateSSS($salary) {
    if ($salary < 4250) {
        return 180;
    } elseif ($salary <= 29750) {
        $ranges = ceil(($salary - 4250) / 500); // Number of 500-peso increments
        return 305 + ($ranges * 22.50);
    } else {
        return 1750;
    }
}


// PhilHealth Calculation
function calculatePhilHealth($salary) {
    $monthlyPremium = $salary * 0.05; // Calculate the premium based on salary

    if ($monthlyPremium <= 500) {
        return 250; // Minimum premium
    } elseif ($monthlyPremium > 5000) {
        return 2500; // Maximum premium
    } else {
        return $monthlyPremium / 2; // Employee's share
    }
}

// Pag-Ibig Calculation
function calculatePagIbig($salary, $rates) {
    $rate = getRate("PagIbig", $salary, $rates);
    return ($rate > 0.02) ? 200 : $salary * $rate;
}

// Tax Calculation
function calculateTaxDue($taxableIncome, $rates) {
    $taxRate = getRate("Tax", $taxableIncome, $rates);
    $bracketMin = 0;
    foreach ($rates["Tax"] as $rate) {
        if ($taxableIncome >= $rate['min'] && $taxableIncome <= $rate['max']) {
            $bracketMin = $rate['min'];
            break;
        }
    }
    return ($taxableIncome - $bracketMin) * $taxRate;
}

// Load tax rates
$taxRates = loadTaxRates();

// Handle Actions
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'add' && isset($_POST['id'], $_POST['name'], $_POST['salary'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $salary = floatval($_POST['salary']);

        $sss = calculateSSS($salary, $taxRates);
        $philHealth = calculatePhilHealth($salary, $taxRates);
        $pagIbig = calculatePagIbig($salary, $taxRates);
        $taxableIncome = $salary - ($sss + $philHealth + $pagIbig);
        $taxDue = calculateTaxDue($taxableIncome, $taxRates);
        $netIncome = $taxableIncome - $taxDue;

        $newEmployee = [$id, $name, $salary, $sss, $philHealth, $pagIbig, $taxableIncome, $taxDue, $netIncome];

        $file = fopen($csvFile, 'a');
        fputcsv($file, $newEmployee);
        fclose($file);

        header("Location: index.php");
        exit;
    }

    if ($action === 'edit' && $_SERVER["REQUEST_METHOD"] == "POST") {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $salary = floatval($_POST['salary']);
        $updatedRows = [];
        $header = [];

        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            $header = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== FALSE) {
                if ($row[0] == $id) {
                    $sss = calculateSSS($salary, $taxRates);
                    $philHealth = calculatePhilHealth($salary, $taxRates);
                    $pagIbig = calculatePagIbig($salary, $taxRates);
                    $taxableIncome = $salary - ($sss + $philHealth + $pagIbig);
                    $taxDue = calculateTaxDue($taxableIncome, $taxRates);
                    $netIncome = $taxableIncome - $taxDue;
                    $row = [$id, $name, $salary, $sss, $philHealth, $pagIbig, $taxableIncome, $taxDue, $netIncome];
                }
                $updatedRows[] = $row;
            }
            fclose($handle);
        }

        if (($handle = fopen($csvFile, "w")) !== FALSE) {
            fputcsv($handle, $header);
            foreach ($updatedRows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }

        echo "<script>alert('Employee Updated Successfully!'); window.location.href='index.php';</script>";
        exit;
    }

    if ($action === 'delete' && isset($_GET['id'])) {
        $idToDelete = $_GET['id'];
        $rows = [];

        if (($handle = fopen($csvFile, "r")) !== FALSE) {
            $header = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== FALSE) {
                if ($row[0] != $idToDelete) {
                    $rows[] = $row;
                }
            }
            fclose($handle);
        }

        if (($handle = fopen($csvFile, "w")) !== FALSE) {
            fputcsv($handle, $header);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }

        echo "<script>alert('Employee Deleted Successfully!'); window.location.href='index.php';</script>";
        exit;
    }
}
?>
