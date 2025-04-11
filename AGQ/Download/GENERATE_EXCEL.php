<?php
require __DIR__ . '/../vendor/autoload.php';

session_start();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Mpdf\Mpdf;
use Dompdf\Dompdf;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$servername = "localhost";
$username = "root";
$pass = "";
$dbase = "agq_database";

$conn = new mysqli($servername, $username, $pass, $dbase);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// $refnum = $_GET['request'] ?? null;
// $dept = $_GET['user'] ?? null;

$refNum = isset($_GET['refNum']) ? $_GET['refNum'] : '';
//$refNum = "EB229340";
$dept = isset($_SESSION['department']) ? $_SESSION['department'] : '';

switch ($dept) {

    case "Import Forwarding":
        
        $outputFormat = 'pdf'; // Default to PDF

        $templateFile = __DIR__ . '/templates/agq_ImportForwardingTemplate.xls';

        // Check if Dompdf is available - if not, we'll use Excel
        if (!class_exists('\Dompdf\Dompdf')) {
            $outputFormat = 'excel';
            error_log("Dompdf not available - using Excel format instead");
        }

        if (!file_exists($templateFile)) {
            die("Error: Template file not found at: $templateFile");
        }

        try {
            $spreadsheet = IOFactory::load($templateFile);

            $SOALCL = $spreadsheet->getSheetByName("SOA_LCL");
            $SOAFULL = $spreadsheet->getSheetByName("SOA_FULL");
            $INVOICELCL = $spreadsheet->getSheetByName("SI_LCL");
            $INVOICEFULL = $spreadsheet->getSheetByName("SI_FULL");
        } catch (Exception $e) {
            die("Error loading template: " . $e->getMessage());
        }


        $query = "SELECT *
                FROM tbl_impfwd 
                WHERE RefNum LIKE ? AND Department LIKE ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $refNum, $dept);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $docType = $row['DocType'];
            $packageType = $row['PackageType'];

            // Process signature data AFTER retrieving the row
            if (isset($row['Approved_by']) && !empty($row['Approved_by'])) {
                // Try to detect if it's a data URL
                if (strpos($row['Approved_by'], 'data:image') === 0) {
                    // It's a base64 image data URL, display it as an image
                    $approvedByHtml = '<img src="' . $row['Approved_by'] . '" style="max-height:50px; max-width:80%;" />';
                    $approvedByHtml .= '<div class="signature-name">' . (isset($row['Approved_name']) ? htmlspecialchars($row['Approved_name']) : '') . '</div>';
                } else if (ctype_print($row['Approved_by'])) {
                    // It's regular text, display the name
                    $approvedByHtml = '<div class="signature-name">' . htmlspecialchars($row['Approved_by']) . '</div>';
                } else {
                    // It might be binary image data without the data:image prefix
                    // Try to convert it to a proper base64 image
                    try {
                        $base64 = base64_encode($row['Approved_by']);
                        $approvedByHtml = '<img src="data:image/png;base64,' . $base64 . '" style="max-height:50px; max-width:80%;" />';
                        $approvedByHtml .= '<div class="signature-name">' . (isset($row['Approved_name']) ? htmlspecialchars($row['Approved_name']) : '') . '</div>';
                    } catch (Exception $e) {
                        // Fallback to just showing the name
                        $approvedByHtml = '<div class="signature-name">' . htmlspecialchars($row['Approved_by']) . '</div>';
                    }
                }
            } else {
                $approvedByHtml = '<div class="signature-name">&nbsp;</div>';
            }

            // For prepared by signature - similar approach
            if (isset($row['Prepared_by']) && !empty($row['Prepared_by'])) {
                if (strpos($row['Prepared_by'], 'data:image') === 0) {
                    // It's a base64 image data URL, display it as an image
                    $preparedByHtml = '<img src="' . $row['Prepared_by'] . '" style="max-height:50px; max-width:80%;" />';
                    $preparedByHtml .= '<div class="signature-name">' . (isset($row['Prepared_name']) ? htmlspecialchars($row['Prepared_name']) : '') . '</div>';
                } else if (ctype_print($row['Prepared_by'])) {
                    // It's regular text, display the name
                    $preparedByHtml = '<div class="signature-name">' . htmlspecialchars($row['Prepared_by']) . '</div>';
                } else {
                    // Try to convert binary data to image
                    try {
                        $base64 = base64_encode($row['Prepared_by']);
                        $preparedByHtml = '<img src="data:image/png;base64,' . $base64 . '" style="max-height:50px; max-width:80%;" />';
                        $preparedByHtml .= '<div class="signature-name">' . (isset($row['Prepared_name']) ? htmlspecialchars($row['Prepared_name']) : '') . '</div>';
                    } catch (Exception $e) {
                        // Fallback to just showing the name
                        $preparedByHtml = '<div class="signature-name">' . htmlspecialchars($row['Prepared_by']) . '</div>';
                    }
                }
            } else {
                $preparedByHtml = '<div class="signature-name">&nbsp;</div>';
            }
        }

        // Clean reference number for filenames
        $cleanRefNum = str_replace(['/', '-'], '', $refNum);



        // Create PDF with custom HTML approach using Dompdf
        if (class_exists('\Dompdf\Dompdf')) {
            try {
                // Create custom HTML directly instead of converting from Excel
                $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Import Forwarding Document</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; font-size: 11pt; }
                .header { text-align: center; margin-bottom: 20px; }
                .logo-text { font-size: 18pt; font-weight: bold; }
                .address-line { font-size: 8pt; margin: 0; }
                .doc-number { text-align: right; margin-top: 10px; font-weight: bold; }
                .title { font-size: 14pt; font-weight: bold; text-align: center; margin: 15px 0; text-decoration: underline; }
                
                table { width: 100%; border-collapse: collapse; }
                table, th, td { border: 1px solid black; }
                th, td { padding: 4px; font-size: 10pt; }
                
                .header-row { background-color: #f0f0f0; }
                .charges-title { font-weight: bold; background-color: #f0f0f0; }
                .green-text { color: green; }
                .currency { text-align: right; }
                .total-row { font-weight: bold; border-top: 2px solid black; }
                
                .signature-table { margin-top: 20px; }
                .signature-table td { border: 1px solid black; height: 60px; vertical-align: bottom; text-align: center; }
                .signature-name { display: inline-block; width: 80%; }
                
                .footer { font-size: 8pt; font-style: italic; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-text">
                    AGQ Freight Logistics, Inc.
                </div>
                <p class="address-line">RM. 518 G/F Alliance Bldg. 410 Quintin Paredes Street, Brgy. 289 Zone 027 1006 Binondo,</p>
                <p class="address-line">NCR, City Of Manila, First District, Philippines</p>
                <p class="address-line">VAT Reg. TIN: 243-733-638-00000 Tel. No. (632) 8244-8935* (632) 8243-7095</p>
                <p class="address-line">Email Add: info@agqfreight.com.ph/accounting@agqfreight.com.ph</p>
                
                
            </div>
            
            <div class="title">' . ($docType == "SOA" ? "STATEMENT OF ACCOUNT" : "SALES INVOICE") . '</div>
            
            <!-- Customer Info -->
            <table>
                <tr>
                    <td width="50%" style="vertical-align: top;">
                        <strong>To:</strong><br>
                        ' . htmlspecialchars($row['To:']) . '<br>
                        <strong>Address:</strong> ' . htmlspecialchars($row['Address']) . '<br>
                        <strong>TIN:</strong> ' . htmlspecialchars($row['Tin']) . '<br>
                        <strong>Attention:</strong> ' . htmlspecialchars($row['Attention']) . '
                    </td>
                    <td width="50%">
                        <strong>Date:</strong><br>
                        ' . htmlspecialchars($row['Date']) . '
                    </td>
                </tr>
            </table>
            
            <!-- Shipment Info -->
            <table style="margin-top: 10px;">
                <tr>
                    <td width="33%"><strong>Vessel</strong></td>
                    <td width="33%"><strong>ETD/ETA</strong></td>
                    <td width="34%"><strong>Ref. No.</strong></td>
                </tr>
                <tr>
                    <td>' . htmlspecialchars($row['Vessel']) . '</td>
                    <td>' . htmlspecialchars($row['ETA']) . '</td>
                    <td>' . htmlspecialchars($row['RefNum']) . '</td>
                </tr>
            </table>
            
            <table style="margin-top: 10px;">
                <tr>
                    <td width="33%"><strong>Origin/Destination</strong></td>
                    <td width="33%"><strong>ER</strong></td>
                    <td width="34%"><strong>BL/HBL No.</strong></td>
                </tr>
                <tr>
                    <td>' . htmlspecialchars($row['DestinationOrigin']) . '</td>
                    <td>' . htmlspecialchars($row['ER']) . '</td>
                    <td>' . htmlspecialchars($row['BHNum']) . '</td>
                </tr>
            </table>
            
            <table style="margin-top: 10px;">
                <tr>
                    <td width="33%"><strong>Nature of Goods</strong></td>
                    <td width="33%"><strong>Weight</strong></td>
                    <td width="34%"><strong>Volume</strong></td>
                </tr>
                <tr>
                    <td>' . htmlspecialchars($row['NatureOfGoods']) . '</td>
                    <td>' . htmlspecialchars($row['Weight']) . ' </td>
                    <td>' . htmlspecialchars($row['Volume']) . ' </td>
                </tr>
            </table>
            
            <!-- Charges Table -->
            <table style="margin-top: 10px;">
                <tr class="charges-title">
                    <td colspan="2">Reimbursable Charges</td>
                    <td width="20%">AMOUNT</td>
                </tr>';

                // Generate dynamic charges based on document type and package type
                if ($docType == "SOA" && $packageType == "LCL") {
                    $html .= '
                <tr>
                    <td colspan="2" >95% OF OCEAN FREIGHT</td>
                    <td class="currency">Php ' . number_format($row['OceanFreight95'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">BL FEE</td>
                    <td class="currency">Php ' . number_format($row['BLFee'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">MANIFEST FEE</td>
                    <td class="currency">Php ' . number_format($row['ManifestFee'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">THC</td>
                    <td class="currency">Php ' . number_format($row['THC'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">CIC</td>
                    <td class="currency">Php ' . number_format($row['CIC'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">ECRS</td>
                    <td class="currency">Php ' . number_format($row['ECRS'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">PSS</td>
                    <td class="currency">Php ' . number_format($row['PSS'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">OTHERS</td>
                    <td class="currency">Php ' . number_format($row['Others'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">ORIGIN</td>
                    <td class="currency">Php ' . number_format($row['Origin'], 2) . '</td>
                </tr>';
                } else if ($docType == "SOA" && $packageType == "Full Container") {
                    $html .= '
                <tr>
                    <td colspan="2" >95% OF OCEAN FREIGHT</td>
                    <td class="currency">Php ' . number_format($row['OceanFreight95'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">HANDLING</td>
                    <td class="currency">Php ' . number_format($row['Handling'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">TURN OVER FEE</td>
                    <td class="currency">Php ' . number_format($row['TurnOverFee'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">BL FEE</td>
                    <td class="currency">Php ' . number_format($row['BLFee'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">FCL CHARGE</td>
                    <td class="currency">Php ' . number_format($row['FCLCharge'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">DOCUMENTATION</td>
                    <td class="currency">Php ' . number_format($row['Documentation'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">MANIFEST FEE</td>
                    <td class="currency">Php ' . number_format($row['ManifestFee'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">OTHERS</td>
                    <td class="currency">Php ' . number_format($row['Others'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">SHIPPING LINE</td>
                    <td class="currency">Php ' . number_format($row['ShippingLine'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">EX-WORK CHARGES</td>
                    <td class="currency">Php ' . number_format($row['ExWorkCharges'], 2) . '</td>
                </tr>';
                } else if ($docType == "Invoice" && $packageType == "LCL") {
                    $html .= '
                <tr>
                    <td colspan="2" >5% OF OCEAN FREIGHT</td>
                    <td class="currency">Php ' . number_format($row['OceanFreight5'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">LCL CHARGE</td>
                    <td class="currency">Php ' . number_format($row['LCLCharge'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">DOCS FEE</td>
                    <td class="currency">Php ' . number_format($row['DocsFee'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">DOCUMENTATION</td>
                    <td class="currency">Php ' . number_format($row['Documentation'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">TURN OVER FEE</td>
                    <td class="currency">Php ' . number_format($row['TurnOverFee'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">HANDLING</td>
                    <td class="currency">Php ' . number_format($row['Handling'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">OTHERS</td>
                    <td class="currency">Php ' . number_format($row['Others'], 2) . '</td>
                </tr>';
                } else if ($docType == "Invoice" && $packageType == "Full Container") {
                    $html .= '
                <tr>
                    <td colspan="2" >5% OF OCEAN FREIGHT</td>
                    <td class="currency">Php ' . number_format($row['OceanFreight5'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">FCL CHARGE</td>
                    <td class="currency">Php ' . number_format($row['FCLCharge'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">DOCUMENTATION</td>
                    <td class="currency">Php ' . number_format($row['Documentation'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">HANDLING</td>
                    <td class="currency">Php ' . number_format($row['Handling'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">VAT (12%)</td>
                    <td class="currency">Php ' . number_format($row['Vat12'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">OTHERS</td>
                    <td class="currency">Php ' . number_format($row['Others'], 2) . '</td>
                </tr>';
                }

                // If the Notes field contains an invoice reference, display it before the total
                if (!empty($row['Notes'])) {
                    $html .= '
                <tr>
                    <td colspan="3" style="text-align: center; font-style: italic;">***' . htmlspecialchars($row['Notes']) . '***</td>
                </tr>';
                }

                // Total row
                $html .= '
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;"><strong>TOTAL:</strong></td>
                    <td class="currency">Php ' . number_format($row['Total'], 2) . '</td>
                </tr>
            </table>
            
            <!-- Signature Section -->
            <table class="signature-table">
                <tr>
                    <td width="33%">
                        <strong>Prepared By:</strong>
                    </td>
                    <td width="33%">
                        <strong>Approved By:</strong>
                    </td>
                    <td width="34%">
                        <strong>Received By:</strong>
                    </td>
                </tr>
                <tr>
                    <td style="height: 60px; vertical-align: bottom; text-align: center;">
                        '  . $preparedByHtml . '
                    </td>
                    <td style="height: 60px; vertical-align: bottom; text-align: center;">
                        ' . $approvedByHtml  . '
                    </td>
                    <td style="height: 60px; vertical-align: bottom; text-align: center;">
                        <div class="signature-name">Printed Name:</div>
                        <br>
                        
                    </td>
                </tr>
            </table>
            
            <div class="footer">
                *Interest of 12% per annum shall be charged on all overdue accounts, and in the event of judicial proceeding to enforce collection customer...
            </div>
            
        </body>
        </html>';

        $debugLogFile = __DIR__ . '/pdf_debug_' . date('Y-m-d_H-i-s') . '.log';
        function debugLog($message, $logFile) {
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[$timestamp] $message" . PHP_EOL;
            file_put_contents($logFile, $logMessage, FILE_APPEND);
        }

        debugLog("=== Starting Import Forwarding process ===", $debugLogFile);
        debugLog("PHP Version: " . phpversion(), $debugLogFile);
        debugLog("Server: " . $_SERVER['SERVER_SOFTWARE'], $debugLogFile);
        debugLog("Memory limit: " . ini_get('memory_limit'), $debugLogFile);

        ini_set('memory_limit', '256M');
        debugLog("New memory limit: " . ini_get('memory_limit'), $debugLogFile);

        $domPdfAvailable = class_exists('\Dompdf\Dompdf');
        debugLog("DomPDF class exists: " . ($domPdfAvailable ? "YES" : "NO"), $debugLogFile);

        $domPdfExtensions = ['dom', 'gd', 'mbstring', 'fileinfo'];

        if (!file_exists($templateFile)) {
            debugLog("ERROR: Template file not found at: $templateFile", $debugLogFile);
            die("Error: Template file not found at: $templateFile");
        } else {
            debugLog("Template file exists at: $templateFile", $debugLogFile);
        }
                $dompdf = new \Dompdf\Dompdf([
                    'enable_remote' => true,
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont' => 'Arial'
                ]);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                $pdfFilename = $cleanRefNum . "-Import_Forwarding.pdf";
                $pdfSavePath = __DIR__ . '/' . $pdfFilename;
                file_put_contents($pdfSavePath, $dompdf->output());

                // Verify PDF was created successfully
                if (!file_exists($pdfSavePath) || filesize($pdfSavePath) < 100) {
                    throw new Exception("PDF was not created properly");
                }

                // Log success
                error_log("PDF successfully created using Dompdf: $pdfSavePath");

                // Clean up HTML temp file
                if (file_exists($htmlPath)) {
                    unlink($htmlPath);
                }

                // Serve the PDF
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $pdfFilename . '"');
                header('Cache-Control: max-age=0');
                header('Content-Length: ' . filesize($pdfSavePath));

                ob_clean();
                flush();
                readfile($pdfSavePath);

                // Clean up files
                unlink($excelSavePath);
                unlink($pdfSavePath);
                exit;
            } catch (Exception $e) {
                // Log detailed error
                error_log("Dompdf conversion failed: " . $e->getMessage());
                error_log("Error trace: " . $e->getTraceAsString());

                // Fall back to Excel if PDF generation fails
                header('Content-Type: application/vnd.ms-excel');
                header('Content-Disposition: attachment; filename="' . $excelFilename . '"');
                header('Cache-Control: max-age=0');
                header('Content-Length: ' . filesize($excelSavePath));

                ob_clean();
                flush();
                readfile($excelSavePath);
                unlink($excelSavePath);
                exit;
            }
        } else {
            // If Dompdf is not available, use Excel output as fallback
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="' . $excelFilename . '"');
            header('Cache-Control: max-age=0');
            header('Content-Length: ' . filesize($excelSavePath));

            ob_clean();
            flush();
            readfile($excelSavePath);
            unlink($excelSavePath);
            exit;
        }
    case "Import Brokerage":
        $templateFile = __DIR__ . '/templates/agq_ImportBrokerageTemplate.xls';
        $spreadsheet = IOFactory::load($templateFile);

        $SOALCL = $spreadsheet->getSheetByName("SOA_LCL");
        $SOAFULL = $spreadsheet->getSheetByName("SOA_FULL");
        $INVOICELCL = $spreadsheet->getSheetByName("SI_LCL");
        $INVOICEFULL = $spreadsheet->getSheetByName("SI_FULL");

        $query = "SELECT *
        FROM tbl_impbrk 
        WHERE RefNum LIKE ? AND Department LIKE ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $refNum, $dept);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {

            $docType = $row['DocType'];
            $packageType = $row['PackageType'];

            if ($docType == "SOA" && $packageType == "LCL") {

                $SOALCL->getStyle('P35:P47')->getNumberFormat()->setFormatCode('₱#,##0.00;[Red]-₱#,##0.00');

                $SOALCL->setCellValue("C17", $row['To:']);
                $SOALCL->setCellValue("C19", $row['Address']);
                $SOALCL->setCellValue("C20", $row['Tin']);
                $SOALCL->setCellValue("C21", $row['Attention']);
                $SOALCL->setCellValue("O17", $row['Date']);
                $SOALCL->setCellValue("B23", $row['Vessel']);
                $SOALCL->setCellValue("G23", $row['ETA']);
                $SOALCL->setCellValue("P23", $row['RefNum']);
                $SOALCL->setCellValue("B26", $row['DestinationOrigin']);
                $SOALCL->setCellValue("G26", $row['ER']);
                $SOALCL->setCellValue("O26", $row['BHNum']);
                $SOALCL->setCellValue("B29", $row['NatureOfGoods']);
                $SOALCL->setCellValue("G29", $row['Packages']);
                $SOALCL->setCellValue("L29", $row['Weight']);
                $SOALCL->setCellValue("P29", $row['Volume']);
                $SOALCL->setCellValue("P35", $row['OceanFreight95']);
                $SOALCL->setCellValue("P36", $row['Forwarder']);
                $SOALCL->setCellValue("P37", $row['WarehouseCharge']);
                $SOALCL->setCellValue("P38", $row['ELodge']);
                $SOALCL->setCellValue("P39", $row['Processing']);
                $SOALCL->setCellValue("P40", $row['FormsStamps']);
                $SOALCL->setCellValue("P41", $row['PhotocopyNotarial']);
                $SOALCL->setCellValue("P42", $row['Documentation']);
                $SOALCL->setCellValue("P43", $row['DeliveryExpense']);
                $SOALCL->setCellValue("P44", $row['Miscellaneous']);
                $SOALCL->setCellValue("P45", $row['Others']);
                $SOALCL->setCellValue("P46", $row['Door2Door']);
                $SOALCL->setCellValue("P47", $row['Total']);
                $SOALCL->setCellValue("G41", $row['Notes']);
                $SOALCL->setCellValue("C52", $row['Prepared_by']);
                $SOALCL->setCellValue("I52", $row['Approved_by']);
            } else if ($docType == "SOA" && $packageType == "Full Container") {

                $SOAFULL->getStyle('P35:P47')->getNumberFormat()->setFormatCode('₱#,##0.00;[Red]-₱#,##0.00');

                $SOAFULL->setCellValue("C17", $row['To:']);
                $SOAFULL->setCellValue("C19", $row['Address']);
                $SOAFULL->setCellValue("C20", $row['Tin']);
                $SOAFULL->setCellValue("C21", $row['Attention']);
                $SOAFULL->setCellValue("O17", $row['Date']);
                $SOAFULL->setCellValue("B23", $row['Vessel']);
                $SOAFULL->setCellValue("G23", $row['ETA']);
                $SOAFULL->setCellValue("P23", $row['RefNum']);
                $SOAFULL->setCellValue("B26", $row['DestinationOrigin']);
                $SOAFULL->setCellValue("G26", $row['ER']);
                $SOAFULL->setCellValue("O26", $row['BHNum']);
                $SOAFULL->setCellValue("B29", $row['NatureOfGoods']);
                $SOAFULL->setCellValue("G29", $row['Packages']);
                $SOAFULL->setCellValue("L29", $row['Weight']);
                $SOAFULL->setCellValue("P29", $row['Volume']);
                $SOAFULL->setCellValue("P33", $row['OceanFreight95']);
                $SOAFULL->setCellValue("P34", $row['ArrastreWharf']);
                $SOAFULL->setCellValue("P35", $row['THC']);
                $SOAFULL->setCellValue("P36", $row['AISL']);
                $SOAFULL->setCellValue("P37", $row['GOFast']);
                $SOAFULL->setCellValue("P38", $row['Processing']);
                $SOAFULL->setCellValue("P39", $row['AdditionalProcessing']);
                $SOAFULL->setCellValue("P40", $row['FormsStamps']);
                $SOAFULL->setCellValue("P41", $row['Handling']);
                $SOAFULL->setCellValue("P42", $row['ExtraHandlingFee']);
                $SOAFULL->setCellValue("P43", $row['PhotocopyNotarial']);
                $SOAFULL->setCellValue("P44", $row['ClearanceExpenses']);
                $SOAFULL->setCellValue("P45", $row['HaulingTrucking']);
                $SOAFULL->setCellValue("P46", $row['AdditionalContainer']);
                $SOAFULL->setCellValue("P47", $row['StuffingPlant']);
                $SOAFULL->setCellValue("P48", $row['IED']);
                $SOAFULL->setCellValue("P49", $row['EarlyGateIn']);
                $SOAFULL->setCellValue("P50", $row['TABS']);
                $SOAFULL->setCellValue("P51", $row['DocsFee']);
                $SOAFULL->setCellValue("P52", $row['Others']);
                $SOAFULL->setCellValue("P53", $row['DetentionCharges']);
                $SOAFULL->setCellValue("P54", $row['ContainerDeposit']);
                $SOAFULL->setCellValue("P55", $row['LateCharge']);
                $SOAFULL->setCellValue("P56", $row['LateCollection']);
                $SOAFULL->setCellValue("P57", $row['Demurrage']);
                $SOAFULL->setCellValue("P58", $row['Total']);
                $SOAFULL->setCellValue("G44", $row['Notes']);
                $SOAFULL->setCellValue("C60", $row['Prepared_by']);
                $SOAFULL->setCellValue("I60", $row['Approved_by']);
            } else if ($docType == "Invoice" && $packageType == "LCL") {

                $INVOICELCL->getStyle('P35:P47')->getNumberFormat()->setFormatCode('₱#,##0.00;[Red]-₱#,##0.00');

                $INVOICELCL->setCellValue("C17", $row['To:']);
                $INVOICELCL->setCellValue("C19", $row['Address']);
                $INVOICELCL->setCellValue("C20", $row['Tin']);
                $INVOICELCL->setCellValue("C21", $row['Attention']);
                $INVOICELCL->setCellValue("O17", $row['Date']);
                $INVOICELCL->setCellValue("B23", $row['Vessel']);
                $INVOICELCL->setCellValue("G23", $row['ETA']);
                $INVOICELCL->setCellValue("P23", $row['RefNum']);
                $INVOICELCL->setCellValue("B26", $row['DestinationOrigin']);
                $INVOICELCL->setCellValue("G26", $row['ER']);
                $INVOICELCL->setCellValue("O26", $row['BHNum']);
                $INVOICELCL->setCellValue("B29", $row['NatureOfGoods']);
                $INVOICELCL->setCellValue("G29", $row['Packages']);
                $INVOICELCL->setCellValue("L29", $row['Weight']);
                $INVOICELCL->setCellValue("P29", $row['Volume']);
                $INVOICELCL->setCellValue("P35", $row['OceanFreight5']);
                $INVOICELCL->setCellValue("P36", $row['BrokerageFee']);
                $INVOICELCL->setCellValue("P37", $row['Vat12']);
                $INVOICELCL->setCellValue("P46", $row['Others']);
                $INVOICELCL->setCellValue("P47", $row['Total']);
                $INVOICELCL->setCellValue("G41", $row['Notes']);
                $INVOICELCL->setCellValue("C52", $row['Prepared_by']);
                $INVOICELCL->setCellValue("I52", $row['Approved_by']);
            } else if ($docType == "Invoice" && $packageType == "Full Container") {

                $INVOICEFULL->getStyle('P35:P47')->getNumberFormat()->setFormatCode('₱#,##0.00;[Red]-₱#,##0.00');

                $INVOICEFULL->setCellValue("C17", $row['To:']);
                $INVOICEFULL->setCellValue("C19", $row['Address']);
                $INVOICEFULL->setCellValue("C20", $row['Tin']);
                $INVOICEFULL->setCellValue("C21", $row['Attention']);
                $INVOICEFULL->setCellValue("O17", $row['Date']);
                $INVOICEFULL->setCellValue("B23", $row['Vessel']);
                $INVOICEFULL->setCellValue("G23", $row['ETA']);
                $INVOICEFULL->setCellValue("P23", $row['RefNum']);
                $INVOICEFULL->setCellValue("B26", $row['DestinationOrigin']);
                $INVOICEFULL->setCellValue("G26", $row['ER']);
                $INVOICEFULL->setCellValue("O26", $row['BHNum']);
                $INVOICEFULL->setCellValue("B29", $row['NatureOfGoods']);
                $INVOICEFULL->setCellValue("G29", $row['Packages']);
                $INVOICEFULL->setCellValue("L29", $row['Weight']);
                $INVOICEFULL->setCellValue("P29", $row['Volume']);
                $INVOICEFULL->setCellValue("P35", $row['OceanFreight5']);
                $INVOICEFULL->setCellValue("P36", $row['BrokerageFee']);
                $INVOICEFULL->setCellValue("P37", $row['Vat12']);
                $INVOICEFULL->setCellValue("P45", $row['Others']);
                $INVOICEFULL->setCellValue("P46", $row['TruckingService']);
                $INVOICEFULL->setCellValue("P47", $row['Total']);
                $INVOICEFULL->setCellValue("G41", $row['Notes']);
                $INVOICEFULL->setCellValue("C52", $row['Prepared_by']);
                $INVOICEFULL->setCellValue("I52", $row['Approved_by']);
            }
        }

        $cleanRefNum = str_replace(['/', '-'], '', $refNum);

        $newFile = $cleanRefNum . "-Import_Brokerage.xls";
        $writer = new Xls($spreadsheet);
        $writer->save($newFile);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $newFile . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($newFile));

        $savePath = __DIR__ . '/' . $newFile;
        $writer->save($savePath);
        readfile($savePath);
        unlink($savePath);

        ob_clean();
        flush();
        readfile($newFile);
        unlink($newFile);
        exit;

        break;


    case "Export Forwarding":
        $templateFile = __DIR__ . '/templates/agq_ExportForwardingTemplate.xlsx';
        $spreadsheet = IOFactory::load($templateFile);

        $SOALCL = $spreadsheet->getSheetByName("SOA_LCL");
        $SOAFULL = $spreadsheet->getSheetByName("SOA_FULL");
        $INVOICELCL = $spreadsheet->getSheetByName("SI_LCL");
        $INVOICEFULL = $spreadsheet->getSheetByName("SI_FULL");

        $query = "SELECT `To:`, `Address`, Tin, Attention, `Date`, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, `Weight`, Volume, PackageType, OceanFreight95, OceanFreight5, BrokerageFee, 
        Others, Notes, Vat12, DocsFee, LCLCharge, ExportProcessing, FormsStamps, ArrastreWharf, 
        E2MLodge, THC, FAF, SealFee, Storage, Telex, Total, Prepared_by, Approved_by, DocType
        FROM tbl_expfwd 
        WHERE RefNum LIKE ? AND Department LIKE ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $refNum, $dept);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {

            $docType = $row['DocType'];
            $packageType = $row['PackageType'];

            if ($docType == "SOA" && $packageType == "LCL") {

                $SOALCL->getStyle('P35:P47')->getNumberFormat()->setFormatCode('₱#,##0.00;[Red]-₱#,##0.00');

                $SOALCL->setCellValue("C17", $row['To:']);
                $SOALCL->setCellValue("C19", $row['Address']);
                $SOALCL->setCellValue("C20", $row['Tin']);
                $SOALCL->setCellValue("C21", $row['Attention']);
                $SOALCL->setCellValue("O17", $row['Date']);
                $SOALCL->setCellValue("B23", $row['Vessel']);
                $SOALCL->setCellValue("G23", $row['ETA']);
                $SOALCL->setCellValue("P23", $row['RefNum']);
                $SOALCL->setCellValue("B26", $row['DestinationOrigin']);
                $SOALCL->setCellValue("G26", $row['ER']);
                $SOALCL->setCellValue("O26", $row['BHNum']);
                $SOALCL->setCellValue("B29", $row['NatureOfGoods']);
                $SOALCL->setCellValue("G29", $row['Packages']);
                $SOALCL->setCellValue("L29", $row['Weight']);
                $SOALCL->setCellValue("P29", $row['Volume']);
                $SOALCL->setCellValue("P35", $row['OceanFreight95']);
                $SOALCL->setCellValue("P36", $row['DocsFee']);
                $SOALCL->setCellValue("P37", $row['LCLCharge']);
                $SOALCL->setCellValue("P38", $row['ExportProcessing']);
                $SOALCL->setCellValue("P39", $row['FormsStamps']);
                $SOALCL->setCellValue("P40", $row['ArrastreWharf']);
                $SOALCL->setCellValue("P41", $row['E2MLodge']);
                $SOALCL->setCellValue("P46", $row['Others']);
                $SOALCL->setCellValue("P47", $row['Total']);
                $SOALCL->setCellValue("G41", $row['Notes']);
                $SOALCL->setCellValue("C52", $row['Prepared_by']);
                $SOALCL->setCellValue("I52", $row['Approved_by']);
            } else if ($docType == "SOA" && $packageType == "Full Container") {

                $SOAFULL->getStyle('P35:P47')->getNumberFormat()->setFormatCode('₱#,##0.00;[Red]-₱#,##0.00');

                $SOAFULL->setCellValue("C17", $row['To:']);
                $SOAFULL->setCellValue("C19", $row['Address']);
                $SOAFULL->setCellValue("C20", $row['Tin']);
                $SOAFULL->setCellValue("C21", $row['Attention']);
                $SOAFULL->setCellValue("O17", $row['Date']);
                $SOAFULL->setCellValue("B23", $row['Vessel']);
                $SOAFULL->setCellValue("G23", $row['ETA']);
                $SOAFULL->setCellValue("P23", $row['RefNum']);
                $SOAFULL->setCellValue("B26", $row['DestinationOrigin']);
                $SOAFULL->setCellValue("G26", $row['ER']);
                $SOAFULL->setCellValue("O26", $row['BHNum']);
                $SOAFULL->setCellValue("B29", $row['NatureOfGoods']);
                $SOAFULL->setCellValue("G29", $row['Packages']);
                $SOAFULL->setCellValue("L29", $row['Weight']);
                $SOAFULL->setCellValue("P29", $row['Volume']);
                $SOAFULL->setCellValue("P35", $row['OceanFreight95']);
                $SOAFULL->setCellValue("P36", $row['THC']);
                $SOAFULL->setCellValue("P37", $row['DocsFee']);
                $SOAFULL->setCellValue("P38", $row['FAF']);
                $SOAFULL->setCellValue("P39", $row['SealFee']);
                $SOAFULL->setCellValue("P40", $row['Storage']);
                $SOAFULL->setCellValue("P41", $row['Telex']);
                $SOAFULL->setCellValue("P46", $row['Others']);
                $SOAFULL->setCellValue("P47", $row['Total']);
                $SOAFULL->setCellValue("G41", $row['Notes']);
                $SOAFULL->setCellValue("C52", $row['Prepared_by']);
                $SOAFULL->setCellValue("I52", $row['Approved_by']);
            } else if ($docType == "Invoice" && $packageType == "LCL") {

                $INVOICELCL->getStyle('P35:P47')->getNumberFormat()->setFormatCode('₱#,##0.00;[Red]-₱#,##0.00');

                $INVOICELCL->setCellValue("C17", $row['To:']);
                $INVOICELCL->setCellValue("C19", $row['Address']);
                $INVOICELCL->setCellValue("C20", $row['Tin']);
                $INVOICELCL->setCellValue("C21", $row['Attention']);
                $INVOICELCL->setCellValue("O17", $row['Date']);
                $INVOICELCL->setCellValue("B23", $row['Vessel']);
                $INVOICELCL->setCellValue("G23", $row['ETA']);
                $INVOICELCL->setCellValue("P23", $row['RefNum']);
                $INVOICELCL->setCellValue("B26", $row['DestinationOrigin']);
                $INVOICELCL->setCellValue("G26", $row['ER']);
                $INVOICELCL->setCellValue("O26", $row['BHNum']);
                $INVOICELCL->setCellValue("B29", $row['NatureOfGoods']);
                $INVOICELCL->setCellValue("G29", $row['Packages']);
                $INVOICELCL->setCellValue("L29", $row['Weight']);
                $INVOICELCL->setCellValue("P29", $row['Volume']);
                $INVOICELCL->setCellValue("P35", $row['OceanFreight5']);
                $INVOICELCL->setCellValue("P36", $row['BrokerageFee']);
                $INVOICELCL->setCellValue("P46", $row['Others']);
                $INVOICELCL->setCellValue("P47", $row['Total']);
                $INVOICELCL->setCellValue("G41", $row['Notes']);
                $INVOICELCL->setCellValue("C52", $row['Prepared_by']);
                $INVOICELCL->setCellValue("I52", $row['Approved_by']);
            } else if ($docType == "Invoice" && $packageType == "Full Container") {

                $INVOICEFULL->getStyle('P35:P47')->getNumberFormat()->setFormatCode('₱#,##0.00;[Red]-₱#,##0.00');

                $INVOICEFULL->setCellValue("C17", $row['To:']);
                $INVOICEFULL->setCellValue("C19", $row['Address']);
                $INVOICEFULL->setCellValue("C20", $row['Tin']);
                $INVOICEFULL->setCellValue("C21", $row['Attention']);
                $INVOICEFULL->setCellValue("O17", $row['Date']);
                $INVOICEFULL->setCellValue("B23", $row['Vessel']);
                $INVOICEFULL->setCellValue("G23", $row['ETA']);
                $INVOICEFULL->setCellValue("P23", $row['RefNum']);
                $INVOICEFULL->setCellValue("B26", $row['DestinationOrigin']);
                $INVOICEFULL->setCellValue("G26", $row['ER']);
                $INVOICEFULL->setCellValue("O26", $row['BHNum']);
                $INVOICEFULL->setCellValue("B29", $row['NatureOfGoods']);
                $INVOICEFULL->setCellValue("G29", $row['Packages']);
                $INVOICEFULL->setCellValue("L29", $row['Weight']);
                $INVOICEFULL->setCellValue("P29", $row['Volume']);
                $INVOICEFULL->setCellValue("P35", $row['OceanFreight5']);
                $INVOICEFULL->setCellValue("P36", $row['Vat12']);
                $INVOICEFULL->setCellValue("P46", $row['Others']);
                $INVOICEFULL->setCellValue("P47", $row['Total']);
                $INVOICEFULL->setCellValue("G41", $row['Notes']);
                $INVOICEFULL->setCellValue("C52", $row['Prepared_by']);
                $INVOICEFULL->setCellValue("I52", $row['Approved_by']);
            }
        }

        $cleanRefNum = str_replace(['/', '-'], '', $refNum);

        $newFile = $cleanRefNum . "-Export_Forwarding.xls";
        $writer = new Xls($spreadsheet);
        $writer->save($newFile);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . basename($newFile) . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($newFile));

        ob_start();
        ob_clean();
        flush();
        readfile($newFile);
        unlink($newFile);
        exit;
        break;


    case "Export Brokerage":
        $templateFile = __DIR__ . '/templates/agq_ExportBrokerageTemplate.xls';
        $spreadsheet = IOFactory::load($templateFile);

        $SOALCL = $spreadsheet->getSheetByName("SOA_LCL");
        $SOAFULL = $spreadsheet->getSheetByName("SOA_FULL");
        $INVOICELCL = $spreadsheet->getSheetByName("SI_LCL");
        $INVOICEFULL = $spreadsheet->getSheetByName("SI_FULL");

        $query = "SELECT *
                FROM tbl_expbrk 
                WHERE RefNum LIKE ? AND Department LIKE ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $refNum, $dept);
        //$refNum = 'EB0002/09-20' for example;
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $docType = $row['DocType'];
            $packageType = $row['PackageType'];

            // Process signature data AFTER retrieving the row
            if (isset($row['Approved_by']) && !empty($row['Approved_by'])) {
                // Try to detect if it's a data URL
                if (strpos($row['Approved_by'], 'data:image') === 0) {
                    // It's already a data URL, but we'll extract just the image for better compatibility
                    $approvedByHtml = '<div class="signature-name">' . htmlspecialchars($row['Approved_by']) . '</div>';
                } else if (ctype_print($row['Approved_by'])) {
                    // It's regular text, display the name
                    $approvedByHtml = '<div class="signature-name">' . htmlspecialchars($row['Approved_by']) . '</div>';
                } else {
                    // It's binary data, but we'll just display the name for now
                    $approvedByHtml = '<div class="signature-name">' . htmlspecialchars($row['Approved_by']) . '</div>';
                }
            } else {
                $approvedByHtml = '<div class="signature-name">&nbsp;</div>';
            }

            // For prepared by signature - keep it simple
            if (isset($row['Prepared_by']) && !empty($row['Prepared_by'])) {
                $preparedByHtml = '<div class="signature-name">' . htmlspecialchars($row['Prepared_by']) . '</div>';
            } else {
                $preparedByHtml = '<div class="signature-name">&nbsp;</div>';
            }

            // Fill in Excel template as before
            if ($docType == "SOA" && $packageType == "LCL") {
                // Your existing Excel code...
            }
            // Other Excel template conditions...
        }

        // Clean reference number for filenames
        $cleanRefNum = str_replace(['/', '-'], '', $refNum);

        // Create Excel file first
        $excelFilename = $cleanRefNum . "-Export_Brokerage.xls";
        $excelSavePath = __DIR__ . '/' . $excelFilename;
        $writer = new Xls($spreadsheet);
        $writer->save($excelSavePath);

        // Replace the mPDF, Dompdf, and TCPDF sections with this custom approach:
        if (class_exists('\Dompdf\Dompdf')) {
            try {
                // Create custom HTML directly instead of converting from Excel
                $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Export Brokerage Document</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; font-size: 11pt; }
                .header { text-align: center; margin-bottom: 20px; }
                .logo-text { font-size: 18pt; font-weight: bold; }
                .address-line { font-size: 8pt; margin: 0; }
                .doc-number { text-align: right; margin-top: 10px; font-weight: bold; }
                .title { font-size: 14pt; font-weight: bold; text-align: center; margin: 15px 0; text-decoration: underline; }
                
                table { width: 100%; border-collapse: collapse; }
                table, th, td { border: 1px solid black; }
                th, td { padding: 4px; font-size: 10pt; }
                
                .header-row { background-color: #f0f0f0; }
                .charges-title { font-weight: bold; background-color: #f0f0f0; }
                .green-text { color: green; }
                .currency { text-align: right; }
                .total-row { font-weight: bold; border-top: 2px solid black; }
                
                .signature-table { margin-top: 20px; }
                .signature-table td { border: 1px solid black; height: 60px; vertical-align: bottom; text-align: center; }
                .signature-name { display: inline-block; width: 80%; }
                
                .footer { font-size: 8pt; font-style: italic; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="logo-text">
                    AGQ Freight Logistics, Inc.
                </div>
                <p class="address-line">RM. 518 G/F Alliance Bldg. 410 Quintin Paredes Street, Brgy. 289 Zone 027 1006 Binondo,</p>
                <p class="address-line">NCR, City Of Manila, First District, Philippines</p>
                <p class="address-line">VAT Reg. TIN: 243-733-638-00000 Tel. No. (632) 8244-8935* (632) 8243-7095</p>
                <p class="address-line">Email Add: info@agqfreight.com.ph/accounting@agqfreight.com.ph</p>
                
                <div class="doc-number">No. ' . htmlspecialchars($row['RefNum']) . '</div>
            </div>
            
            <div class="title">' . ($docType == "SOA" ? "STATEMENT OF ACCOUNT" : "SALES INVOICE") . '</div>
            
            <!-- Customer Info -->
            <table>
                <tr>
                    <td width="50%" style="vertical-align: top;">
                        <strong>To:</strong><br>
                        ' . htmlspecialchars($row['To:']) . '<br>
                        <strong>Address:</strong> ' . htmlspecialchars($row['Address']) . '<br>
                        <strong>TIN:</strong> ' . htmlspecialchars($row['Tin']) . '<br>
                        <strong>Attention:</strong> ' . htmlspecialchars($row['Attention']) . '
                    </td>
                    <td width="50%">
                        <strong>Date:</strong><br>
                        ' . htmlspecialchars($row['Date']) . '
                    </td>
                </tr>
            </table>
            
            <!-- Shipment Info -->
            <table style="margin-top: 10px;">
                <tr>
                    <td width="33%"><strong>Vessel</strong></td>
                    <td width="33%"><strong>ETD/ETA</strong></td>
                    <td width="34%"><strong>Ref. No.</strong></td>
                </tr>
                <tr>
                    <td>' . htmlspecialchars($row['Vessel']) . '</td>
                    <td>' . htmlspecialchars($row['ETA']) . '</td>
                    <td>' . htmlspecialchars($row['RefNum']) . '</td>
                </tr>
            </table>
            
            <table style="margin-top: 10px;">
                <tr>
                    <td width="33%"><strong>Origin/Destination</strong></td>
                    <td width="33%"><strong>Packages</strong></td>
                    <td width="34%"><strong>BL/HBL No.</strong></td>
                </tr>
                <tr>
                    <td>' . htmlspecialchars($row['DestinationOrigin']) . '</td>
                    <td>' . htmlspecialchars($row['Packages']) . ' ' . htmlspecialchars($packageType) . '</td>
                    <td>' . htmlspecialchars($row['BHNum']) . '</td>
                </tr>
            </table>
            
            <table style="margin-top: 10px;">
                <tr>
                    <td width="33%"><strong>Nature of Goods</strong></td>
                    <td width="33%"><strong>Weight</strong></td>
                    <td width="34%"><strong>Volume</strong></td>
                </tr>
                <tr>
                    <td>' . htmlspecialchars($row['NatureOfGoods']) . '</td>
                    <td>' . htmlspecialchars($row['Weight']) . ' KGS</td>
                    <td>' . htmlspecialchars($row['Volume']) . ' CBM</td>
                </tr>
            </table>
            
            <!-- Charges Table -->
            <table style="margin-top: 10px;">
                <tr class="charges-title">
                    <td colspan="2">Reimbursable Charges</td>
                    <td width="20%">AMOUNT</td>
                </tr>';

                // Generate dynamic charges based on document type and package type
                if ($docType == "SOA" && $packageType == "LCL") {
                    $html .= '
                <tr>
                    <td colspan="2" >95% OF OCEAN FREIGHT</td>
                    <td class="currency">Php ' . number_format($row['OceanFreight95'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">ADVANCE SHIPPING</td>
                    <td class="currency">Php ' . number_format($row['AdvanceShipping'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">PROCESSING</td>
                    <td class="currency">Php ' . number_format($row['Processing'], 2) . '</td>
                </tr>';
                } else if ($docType == "SOA" && $packageType == "Full Container") {
                    $html .= '
                <tr>
                    <td colspan="2" >95% OF OCEAN FREIGHT</td>
                    <td class="currency">Php ' . number_format($row['OceanFreight95'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">ARRASTRE</td>
                    <td class="currency">Php ' . number_format($row['Arrastre'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">WHARFAGE</td>
                    <td class="currency">Php ' . number_format($row['Wharfage'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">PROCESSING</td>
                    <td class="currency">Php ' . number_format($row['Processing'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">FORMS & STAMPS</td>
                    <td class="currency">Php ' . number_format($row['FormsStamps'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">PHOTOCOPY & NOTARIAL</td>
                    <td class="currency">Php ' . number_format($row['PhotocopyNotarial'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">DOCUMENTATION</td>
                    <td class="currency">Php ' . number_format($row['Documentation'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">E2M LODGE</td>
                    <td class="currency">Php ' . number_format($row['E2MLodge'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">MANUAL STUFFING</td>
                    <td class="currency">Php ' . number_format($row['ManualStuffing'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">HANDLING</td>
                    <td class="currency">Php ' . number_format($row['Handling'], 2) . '</td>
                </tr>';
                } else if ($docType == "Invoice" && $packageType == "LCL") {
                    $html .= '
                <tr>
                    <td colspan="2" >5% OF OCEAN FREIGHT</td>
                    <td class="currency">Php ' . number_format($row['OceanFreight5'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">BROKERAGE FEE</td>
                    <td class="currency">Php ' . number_format($row['BrokerageFee'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">DISCOUNT (50%)</td>
                    <td class="currency">Php ' . number_format($row['Discount50'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">VAT (12%)</td>
                    <td class="currency">Php ' . number_format($row['Vat12'], 2) . '</td>
                </tr>';
                } else if ($docType == "Invoice" && $packageType == "Full Container") {
                    $html .= '
                <tr>
                    <td colspan="2" >5% OF OCEAN FREIGHT</td>
                    <td class="currency">Php ' . number_format($row['OceanFreight5'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">BROKERAGE FEE</td>
                    <td class="currency">Php ' . number_format($row['BrokerageFee'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">DISCOUNT (50%)</td>
                    <td class="currency">Php ' . number_format($row['Discount50'], 2) . '</td>
                </tr>
                <tr>
                    <td colspan="2">VAT (12%)</td>
                    <td class="currency">Php ' . number_format($row['Vat12'], 2) . '</td>
                </tr>';
                }

                // Add Others for all types
                $html .= '
            <tr>
                <td colspan="2">OTHERS</td>
                <td class="currency">Php ' . number_format($row['Others'], 2) . '</td>
            </tr>';

                // Add PCCI for SOA Full Container if needed
                if ($docType == "SOA" && $packageType == "Full Container") {
                    $html .= '
            <tr>
                <td colspan="2">PCCI</td>
                <td class="currency">Php ' . number_format($row['PCCI'], 2) . '</td>
            </tr>';
                }

                // If the Notes field contains an invoice reference, display it before the total
                if (!empty($row['Notes']) && strpos($row['Notes'], 'INVOICE') !== false) {
                    $html .= '
            <tr>
                <td colspan="3" style="text-align: center; font-style: italic;">***' . htmlspecialchars($row['Notes']) . '***</td>
            </tr>';
                }

                // Total row
                $html .= '
            <tr class="total-row">
                <td colspan="2" style="text-align: right;"><strong>TOTAL:</strong></td>
                <td class="currency">Php ' . number_format($row['Total'], 2) . '</td>
            </tr>
        </table>
        
        <!-- Signature Section -->
        <table class="signature-table">
            <tr>
                <td width="33%">
                    <strong>Prepared By:</strong>
                </td>
                <td width="33%">
                    <strong>Approved By:</strong>
                </td>
                <td width="34%">
                    <strong>Received By:</strong>
                </td>
            </tr>
            <tr>
                <td style="height: 60px; vertical-align:  center; text-align: center;">
                    ' . $preparedByHtml . '
                </td>
                <td style="height: 60px; vertical-align:  center; text-align: center;">
                    ' . $approvedByHtml . '
                </td>
                <td style="height: 60px; vertical-align: center; text-align: center;">
                    
                    <br>
                    
                </td>
            </tr>
        </table>
        
        <div class="footer">
            *Interest of 12% per annum shall be charged on all overdue accounts, and in the event of judicial proceeding to enforce collection customer...
        </div>
        
        </body>
        </html>';

                // Save HTML for debugging
                $htmlFile = $cleanRefNum . "-temp.html";
                $htmlPath = __DIR__ . '/' . $htmlFile;
                file_put_contents($htmlPath, $html);

                // Create PDF using Dompdf
                $dompdf = new \Dompdf\Dompdf([
                    'enable_remote' => true,
                    'isRemoteEnabled' => true,
                    'isHtml5ParserEnabled' => true,
                    'defaultFont' => 'Arial'
                ]);
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();

                $pdfFilename = $cleanRefNum . "-Export_Brokerage.pdf";
                $pdfSavePath = __DIR__ . '/' . $pdfFilename;
                file_put_contents($pdfSavePath, $dompdf->output());

                // Verify PDF was created successfully
                if (!file_exists($pdfSavePath) || filesize($pdfSavePath) < 100) {
                    throw new Exception("PDF was not created properly");
                }

                // Log success
                error_log("PDF successfully created using Dompdf: $pdfSavePath");

                // Clean up HTML temp file
                if (file_exists($htmlPath)) {
                    unlink($htmlPath);
                }

                // Serve the PDF
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $pdfFilename . '"');
                header('Cache-Control: max-age=0');
                header('Content-Length: ' . filesize($pdfSavePath));

                ob_clean();
                flush();
                readfile($pdfSavePath);

                // Clean up files
                unlink($excelSavePath);
                unlink($pdfSavePath);
                exit;
            } catch (Exception $e) {
                // Log detailed error
                error_log("Dompdf conversion failed: " . $e->getMessage());
                error_log("Error trace: " . $e->getTraceAsString());
            }
        }
}
