<?php
require __DIR__ . '/vendor/autoload.php';
require_once 'db_agq.php';

session_start();

//use Mpdf\Mpdf;
use Dompdf\Dompdf;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$refNum = isset($_GET['refNum']) ? $_GET['refNum'] : '';
$dept = isset($_SESSION['department']) ? $_SESSION['department'] : '';

switch ($dept) {

    case "Import Forwarding":
        
        $outputFormat = 'pdf'; // Default to PDF

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
                        <div class="signature-name"></div>
                        <br>
                        
                    </td>
                </tr>
            </table>
            
            <div class="footer">
                *Interest of 12% per annum shall be charged on all overdue accounts, and in the event of judicial proceeding to enforce collection customer...
            </div>
            
        </body>
        </html>';

        // $debugLogFile = __DIR__ . '/pdf_debug_' . date('Y-m-d_H-i-s') . '.log';
        // function debugLog($message, $logFile) {
        //     $timestamp = date('Y-m-d H:i:s');
        //     $logMessage = "[$timestamp] $message" . PHP_EOL;
        //     file_put_contents($logFile, $logMessage, FILE_APPEND);
        // }

        // debugLog("=== Starting Import Forwarding process ===", $debugLogFile);
        // debugLog("PHP Version: " . phpversion(), $debugLogFile);
        // debugLog("Server: " . $_SERVER['SERVER_SOFTWARE'], $debugLogFile);
        // debugLog("Memory limit: " . ini_get('memory_limit'), $debugLogFile);

        // ini_set('memory_limit', '256M');
        // debugLog("New memory limit: " . ini_get('memory_limit'), $debugLogFile);

        // $domPdfAvailable = class_exists('\Dompdf\Dompdf');
        // debugLog("DomPDF class exists: " . ($domPdfAvailable ? "YES" : "NO"), $debugLogFile);

        // $domPdfExtensions = ['dom', 'gd', 'mbstring', 'fileinfo'];

        // if (!file_exists($templateFile)) {
        //     debugLog("ERROR: Template file not found at: $templateFile", $debugLogFile);
        //     die("Error: Template file not found at: $templateFile");
        // } else {
        //     debugLog("Template file exists at: $templateFile", $debugLogFile);
        // }
               // Dompdf configuration
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

                // Serve the PDF
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $pdfFilename . '"');
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Cache-Control: post-check=0, pre-check=0', false);
                header('Pragma: no-cache');
                header('Content-Length: ' . filesize($pdfSavePath));

                // Ensure fresh output for each request
                if (session_id()) {
                    session_write_close();
                }
                ob_end_clean();
                readfile($pdfSavePath);

                // Optional delay to prevent cleanup conflicts
                sleep(1);

                // Clean up temporary files
                unlink($pdfSavePath);
                exit;

            } catch (Exception $e) {
                // Log detailed error
                error_log("Dompdf conversion failed: " . $e->getMessage());
                error_log("Error trace: " . $e->getTraceAsString());
                exit;
            }

        } else {
            // // If Dompdf is not available, use Excel output as fallback
            // header('Content-Type: application/vnd.ms-excel');
            // header('Content-Disposition: attachment; filename="' . $excelFilename . '"');
            // header('Cache-Control: max-age=0');
            // header('Content-Length: ' . filesize($excelSavePath));

            // ob_clean();
            // flush();
            // readfile($excelSavePath);
            // unlink($excelSavePath);

            echo '<script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Error!",
                    text: "Error updating record: ' . $stmt->error . '",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>';

            exit;
        }

        break;

    case "Import Brokerage":
      
        break;


    case "Export Forwarding":
       
        break;

    case "Export Brokerage":

        $query = "SELECT *
                FROM tbl_expbrk 
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

                // Dompdf configuration
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

                // Serve the PDF
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $pdfFilename . '"');
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Cache-Control: post-check=0, pre-check=0', false);
                header('Pragma: no-cache');
                header('Content-Length: ' . filesize($pdfSavePath));

                // Ensure fresh output for each request
                if (session_id()) {
                    session_write_close();
                }
                ob_end_clean();
                readfile($pdfSavePath);

                // Optional delay to prevent cleanup conflicts
                sleep(1);

                // Clean up temporary files
                unlink($pdfSavePath);
                exit;

            } catch (Exception $e) {
                // Log detailed error
                error_log("Dompdf conversion failed: " . $e->getMessage());
                error_log("Error trace: " . $e->getTraceAsString());
                exit;
            }
        } else {

            echo '<script>
            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: "Error!",
                    text: "Error updating record: ' . $stmt->error . '",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            });
        </script>';

            exit;
        }

        break;
}
