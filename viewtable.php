<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications Database</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #7B0404;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>

    <h2>Applications Database</h2>
    <table id="applicationsTable" class="display responsive nowrap">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Category</th>
                <th>Institution</th>
                <th>Sub Institution</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Country</th>
                <th>Abstract Path</th>
                <th>Full Paper Path</th>
                <th>Transfer Path</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
            require_once('fun/config.php');

            function censorEmail($email) {
                $parts = explode('@', $email);
                $username = $parts[0];
                $domain = $parts[1];
                $censored_username = substr($username, 0, 1) . str_repeat('*', strlen($username) - 2) . substr($username, -1);
                return $censored_username . '@' . $domain;
            }

            function censorPhoneNumber($phone) {
                return substr($phone, 0, 3) . str_repeat('*', strlen($phone) - 7) . substr($phone, -4);
            }

            $sql = "SELECT full_name, category, institution, sub_institution, phone, email, country, abstract_path, fullpaper_path, transfer_path FROM applications";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $abstract_path = $base_url . ltrim($row["abstract_path"], '.');
                    $fullpaper_path = $base_url . str_replace("/home/iicacsco/public_html", "", $row["fullpaper_path"]);
                    $transfer_path = $base_url . str_replace("/home/iicacsco/public_html", "", $row["transfer_path"]);

                    echo "
                    <tr>
                        <td>" . $row["full_name"]. "</td>
                        <td>" . $row["category"]. "</td>
                        <td>" . $row["institution"]. "</td>
                        <td>" . $row["sub_institution"]. "</td>
                        <td data-original='" . $row["phone"]. "'>" . censorPhoneNumber($row["phone"]). "</td>
                        <td data-original='" . $row["email"]. "'>" . censorEmail($row["email"]). "</td>
                        <td>" . $row["country"]. "</td>
                        <td data-link='" . $abstract_path . "'><a href='" . $abstract_path . "' download>" . basename($row["abstract_path"]) . "</a></td>
                        <td data-link='" . $fullpaper_path . "'><a href='" . $fullpaper_path . "' download>" . basename($row["fullpaper_path"]) . "</a></td>
                        <td data-link='" . $transfer_path . "'><a href='" . $transfer_path . "' download>" . basename($row["transfer_path"]) . "</a></td>
                        <td><button class='print-certificate' data-name='" . $row["full_name"]. "' data-category='" . $row["category"]. "' data-institution='" . $row["institution"]. "'>Print Certificate</button></td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='11'>No applications found</td></tr>";
            }
            $conn->close();
        ?>
        </tbody>
    </table>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#applicationsTable').DataTable({
                responsive: true,
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                        exportOptions: {
                            columns: ':not(:last-child):not(:nth-last-child(2)):not(:nth-last-child(3))' // Exclude abstract, full paper, and transfer columns
                        },
                        customize: function (xlsx) {
                            var sheet = xlsx.xl.worksheets['sheet1.xml'];

                            // Unsensor phone numbers
                            $('row c[r^="E"]', sheet).each(function () {
                                var cell = $(this);
                                var originalData = cell.attr('data-original');
                                if (originalData) {
                                    cell.text(originalData);
                                }
                            });

                            // Unsensor emails
                            $('row c[r^="F"]', sheet).each(function () {
                                var cell = $(this);
                                var originalData = cell.attr('data-original');
                                if (originalData) {
                                    cell.text(originalData);
                                }
                            });
                        }
                    },
                    'copy', 'csv', 'pdf', 'print'
                ],
                columnDefs: [
                    {
                        targets: 4,
                        render: function (data, type, row) {
                            if (type === 'export') {
                                return row[4]; // Use original phone number from row data
                            }
                            return data;
                        }
                    },
                    {
                        targets: 5,
                        render: function (data, type, row) {
                            if (type === 'export') {
                                return row[5]; // Use original email from row data
                            }
                            return data;
                        }
                    }
                ]
            });

            // Print Certificate Button
            $(document).on('click', '.print-certificate', function() {
                
                var category = $(this).data('category');
                var name = $(this).data('name');
                printCertificate(category,name);
            });
        });

        async function printCertificate(category,name) {
            const { PDFDocument, rgb } = PDFLib;

            // Fetch the existing PDF
            const existingPdfBytes = await fetch('file/sertifikat.pdf').then(res => res.arrayBuffer());

            // Load a PDFDocument from the existing PDF bytes
            const pdfDoc = await PDFDocument.load(existingPdfBytes);

            // Embed the Helvetica font
            const helveticaFont = await pdfDoc.embedFont(PDFLib.StandardFonts.Helvetica);

            // Get the first page of the document
            const pages = pdfDoc.getPages();
            const firstPage = pages[0];

            // Get the width and height of the first page
            const { width, height } = firstPage.getSize();

            // Calculate the text width to center the text
            const textWidthName = helveticaFont.widthOfTextAtSize(name, 24);
            const textWidthCategory = helveticaFont.widthOfTextAtSize(category, 19);

            // Draw a string of text on the first page
            firstPage.drawText(name, {
                x: (width - textWidthName) / 2,
                y: height / 2 + 1,
                size: 24,
                font: helveticaFont,
                color: rgb(0, 0, 0),
            });

            firstPage.drawText(category, {
                x: (width - textWidthCategory) / 2,
                y: height / 2 + 50,
                size: 19,
                font: helveticaFont,
                color: rgb(0, 0, 0),
            });

            // Serialize the PDFDocument to bytes (a Uint8Array)
            const pdfBytes = await pdfDoc.save();

            // Trigger the browser to download the PDF document
            const blob = new Blob([pdfBytes], { type: 'application/pdf' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'certificate.pdf';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>
