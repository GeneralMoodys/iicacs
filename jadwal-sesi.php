<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications Database</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
    <style>
         body {
            font-family: 'Open Sans', sans-serif;
            background-color: #7B0404;
            color: #ffffff;
            margin: 0;
            padding: 20px;
        }
        label, select, input, div .dataTables_wrapper 
        .dataTables_length, 
        .dataTables_wrapper 
        .dataTables_filter, 
        .dataTables_wrapper 
        .dataTables_info, 
        .dataTables_wrapper 
        .dataTables_processing, 
        .dataTables_wrapper 
        .dataTables_paginate, 
        .paginate_button {
            color: #ffffff !important;
        }
        h1 {
            color: #FFDE59;
            font-size: 32px;
            text-align: center;
            margin-bottom: 40px;
            font-weight: 600;
        }
        h2 {
            color: #ffffff;
            font-size: 28px;
            margin-bottom: 10px;
            border-bottom: 2px solid #FFDE59;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
            box-shadow: 0 2px 3px rgba(0,0,0,0.1);
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            color: #333333;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #FFDE59;
            color: #7B0404;
            font-weight: bold;
            font-size: 18px;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tbody tr:hover {
            background-color: #f1f1f1;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            table, th, td {
                display: block;
                width: 100%;
            }
            th, td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }
            th::before, td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                width: 45%;
                padding-right: 10px;
                font-weight: bold;
                text-align: left;
            }
        }
    </style>
</head>
<body>

<?php
require_once('func/config.php'); // Memanggil file koneksi database

$sql = "SELECT full_name, category, institution FROM applications";
$result = $conn->query($sql);
$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
} else {
    echo "No data found.";
}

$groupedData = [];
foreach ($data as $row) {
    $key = $row['category'];
    if (!isset($groupedData[$key])) {
        $groupedData[$key] = [];
    }
    $groupedData[$key][] = $row;
}

foreach ($groupedData as $category => $rows) {
    echo "<h2>Kategori: " . htmlspecialchars($category) . "</h2>";
    echo "<table id='table_$category' class='display'>
        <thead>
            <tr>
                <th>Nama Lengkap</th>
                <th>Institusi</th>
                <th>Kategori</th>
            </tr>
        </thead>
        <tbody>";

    foreach ($rows as $row) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['institution']) . "</td>";
        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
        echo "</tr>";
    }

    echo "</tbody>
        </table>";
}
?>


    <!-- Modal for password input -->
    

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
    <script>
      $(document).ready(function() {
            $('table').DataTable();
        });
    </script>
</body>
</html>
