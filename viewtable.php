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
            font-family: Arial, sans-serif;
            background-color: #fff;
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
        #passwordModal {
            display: none;
            position: fixed;
            z-index: 1;
            padding-top: 100px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }
        #passwordModalContent {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            text-align: center;
        }
        #passwordInput {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
        }
        #passwordSubmit {
            padding: 10px 20px;
        }
    </style>
</head>
<body>

    <h2>Applications Database</h2>
    <div>
        <button id="sortAsc">Sort by Oldest</button>
        <button id="sortDesc">Sort by Newest</button>
    </div>
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
        <th>Submission Date</th> <!-- New Column -->
    </tr>
</thead>

        <tbody>
        <?php
            require_once('func/config.php');

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
            
            $sql = "SELECT full_name, category, institution, sub_institution, phone, email, country, abstract_path, fullpaper_path, transfer_path, submission_date FROM applications";
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
                        <td>" . $row["phone"] . "</td>
                        <td>" . $row["email"] . "</td>
                        <td>" . $row["country"]. "</td>
                        <td data-link='" . $abstract_path . "'><a href='" . $abstract_path . "' download>" . basename($row["abstract_path"]) . "</a></td>
                        <td data-link='" . $fullpaper_path . "'><a href='" . $fullpaper_path . "' download>" . basename($row["fullpaper_path"]) . "</a></td>
                        <td data-link='" . $transfer_path . "'><a href='" . $transfer_path . "' download>" . basename($row["transfer_path"]) . "</a></td>
                        <td>" . $row["submission_date"] . "</td> <!-- New Column -->
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='12'>No applications found</td></tr>";
            }
            $conn->close();
        ?>
        </tbody>
    </table>

    <!-- Modal for password input -->
    <div id="passwordModal">
        <div id="passwordModalContent">
            <p>Enter password to view hidden details:</p>
            <input type="password" id="passwordInput" placeholder="Password">
            <button id="passwordSubmit">Submit</button>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf-lib/1.17.1/pdf-lib.min.js"></script>
    <script>
      $(document).ready(function() {
                var table = $('#applicationsTable').DataTable({
                    responsive: true,
                    paging: true,
                    pagingType: 'full_numbers',
                    dom: 'frtip',
                    order: [[10, 'asc']], // Default sorting by submission_date in ascending order
                    columnDefs: [
                        { type: 'date', targets: 10 } // Ensure proper date sorting for submission_date column
                    ]
                });

                // Event listener for sorting by oldest (ascending order)
                $('#sortAsc').on('click', function() {
                    table.order([10, 'asc']).draw();
                });

                // Event listener for sorting by newest (descending order)
                $('#sortDesc').on('click', function() {
                    table.order([10, 'desc']).draw();
                });

                // Show password modal
                $('#passwordModal').show();

                // Handle password submission
                $('#passwordSubmit').click(function() {
                    var password = $('#passwordInput').val();
                    if (password === 'iicacs2024') {
                        $('td[data-original]').each(function() {
                            $(this).text($(this).data('original'));
                        });
                        $('#passwordModal').hide();
                    } else {
                        alert('Incorrect password');
                    }
                });

                // Print Certificate Button
                $(document).on('click', '.print-certificate', function() {
                    var category = $(this).data('category');
                    var name = $(this).data('name');
                    printCertificate(category, name);
                });
            });


        async function printCertificate(category, name) {
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
