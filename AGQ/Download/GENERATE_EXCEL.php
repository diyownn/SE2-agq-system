<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

$refNum = "Euro";
$dept = "Export Brokerage";

switch ($dept) {

    case "Import Forwarding":
        $templateFile = __DIR__ . '/templates/agq_ImportForwardingTemplate.xls';
        $spreadsheet = IOFactory::load($templateFile);

        $query = "SELECT `To:`, `Address`, Tin, Attention, `Date`, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, `Weight`, Volume, PackageType, OceanFreight95, OceanFreight5, 
        LCLCharge, DocsFee, Documentation, TurnOverFee, Handling, Others, Notes, Vat12, FCLCharge, 
        BLFee, ManifestFee, THC, CIC, ECRS, PSS, Origin, ShippingLine, ExWorkCharges, Total, 
        Prepared_by, Approved_by, Edited_by, EditDate, DocType, Company_name, Department, Comment, isArchived
        FROM tbl_impfwd 
        WHERE RefNum LIKE ? AND Department LIKE ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $refNum, $dept);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // Cell assignation here.

        $newFile = $refNum . "-Import_Forwarding.xls";
        $writer = new Xls($spreadsheet);
        $writer->save($newFile);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $newFile . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($newFile));


        ob_clean();
        flush();
        readfile($newFile);
        unlink($newFile);
        exit;

    case "Import Brokerage":
        $templateFile = __DIR__ . '/templates/agq_ImportBrokerageTemplate.xls';
        $spreadsheet = IOFactory::load($templateFile);

        //cell assingation

        $SOA = $spreadsheet->getSheetByName("SOA");
        $INVOICE = $spreadsheet->getSheetByName("SI");

        $query = "SELECT `To:`, `Address`, Tin, Attention, `Date`, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, `Weight`, Measurement, PackageType, OceanFreight95, OceanFreight5, BrokerageFee, Vat12, 
        Others, Notes, TruckingService, Forwarder, WarehouseCharge, Elodge, Processing, FormsStamps, 
        PhotocopyNotarial, Documentation, DeliveryExpense, Miscellaneous, Door2Door, ArrastreWharf, THC, AISL, GOFast, 
        AdditionalProcessing, ExtraHandlingFee, ClearanceExpenses, HaulingTrucking, AdditionalContainer, Handling, 
        StuffingPlant, IED, EarlyGateIn, TABS, DocsFee, DetentionCharges, ContainerDeposit, LateCollection, 
        LateCharge, Demurrage, Total, Prepared_by, Approved_by, Editied_by, EditDate, DocType, Company_name, 
        Department, Comment, isArchived 
        FROM tbl_expbrk 
         WHERE RefNum LIKE ? AND Department LIKE ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $refNum, $dept);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $newFile = $refNum . "-Import_Brokerage.xls";
        $writer = new Xls($spreadsheet);
        $writer->save($newFile);

        // Cell assignation here.

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $newFile . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($newFile));


        ob_clean();
        flush();
        readfile($newFile);
        unlink($newFile);
        exit;

        break;


    case "Export Forwarding":
        $templateFile = __DIR__ . '/templates/agq_ExportForwardingTemplate.xlsx';
        $spreadsheet = IOFactory::load($templateFile);

        $query = "SELECT `To:`, `Address`, Tin, Attention, `Date`, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, `Weight`, Volume, PackageType, OceanFreight95, OceanFreight5, BrokerageFee, 
        Others, Notes, Vat12, DocsFee, LCLCharge, ExportProcessing, FormsStamps, ArrastreWharf, 
        E2MLodge, THC, FAF, SealFee, Storage, Telex, Total, 
        Prepared_by, Approved_by, Edited_by, EditDate, DocType, Company_name, Department, Comment, isArchived 
        FROM tbl_expfwd 
        WHERE RefNum LIKE ? AND Department LIKE ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $refNum, $dept);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();


        $SOA = $spreadsheet->getSheetByName("SOA");
        $INVOICE = $spreadsheet->getSheetByName("SI");

        //cell assingation

        $newFile = $refNum . "-Export_Forwarding.xlsx";
        $writer = new Xlsx($spreadsheet);
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


        $SOA = $spreadsheet->getSheetByName("SOA");
        $INVOICE = $spreadsheet->getSheetByName("SI");


        $query = "SELECT `To:`, `Address`, Tin, Attention, `Date`, Vessel, ETA, RefNum, DestinationOrigin, ER, BHNum,
        NatureOfGoods, Packages, `Weight`, Volume, PackageType, Others, Notes, 
        AdvanceShipping, Processing, Arrastre, Wharfage, FormsStamps, PhotocopyNotarial,
        Documentation, E2MLodge, ManualStuffing, Handling, PCCI, Total, Prepared_by, Approved_by, DocType, 
        Company_name, Department 
        FROM tbl_expbrk 
        WHERE RefNum LIKE ? AND Department LIKE ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $refNum, $dept);
        //$refNum = 'EB0002/09-20' for example;
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {

            $sheet->getStyle('P35:P47')->getNumberFormat()->setFormatCode('₱#,##0.00;[Red]-₱#,##0.00');

            $SOA->setCellValue("C17", $row['To:']);
            $SOA->setCellValue("C19", $row['Address']);
            $SOA->setCellValue("C21", $row['Attention']);
            $SOA->setCellValue("O17", $row['Date']);
            $SOA->setCellValue("B23", $row['Vessel']);
            $SOA->setCellValue("G23", $row['ETA']);
            $SOA->setCellValue("P23", $row['RefNum']);
            $SOA->setCellValue("B26", $row['DestinationOrigin']);
            $SOA->setCellValue("G26", $row['ER']);
            $SOA->setCellValue("O26", $row['BHNum']);
            $SOA->setCellValue("B29", $row['NatureOfGoods']);
            $SOA->setCellValue("G29", $row['Packages']);
            $SOA->setCellValue("L29", $row['Weight']);
            $SOA->setCellValue("P29", $row['Volume']);
            $SOA->setCellValue("P35", $row['Arrastre']);
            $SOA->setCellValue("P36", $row['Wharfage']);
            $SOA->setCellValue("P37", $row['Processing']);
            $SOA->setCellValue("P38", $row['FormsStamps']);
            $SOA->setCellValue("P39", $row['PhotocopyNotarial']);
            $SOA->setCellValue("P40", $row['Documentation']);
            $SOA->setCellValue("P41", $row['E2MLodge']);
            $SOA->setCellValue("P42", $row['ManualStuffing']);
            $SOA->setCellValue("P45", $row['Handling']);
            $SOA->setCellValue("P46", $row['Others']);
            $SOA->setCellValue("P47", $row['Total']);
            $SOA->setCellValue("F42", $row['Notes']);
            $SOA->setCellValue("C52", $row['Prepared_by']);
            $SOA->setCellValue("I52", $row['Approved_by']);
        }


        $newFile = $refNum . "-Export_Brokerage.xls";
        $writer = new Xls($spreadsheet);
        $writer->save($newFile);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $newFile . '"');
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . filesize($newFile));


        ob_clean();
        flush();
        readfile($newFile);
        unlink($newFile);
        exit;
        break;

    default:
        break;
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Download</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #333;
        }

        .download-container {
            background-color: white;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            padding: 32px;
            max-width: 420px;
            width: 100%;
            text-align: center;
            transition: all 0.3s ease;
        }

        .download-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            position: relative;
        }

        .download-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            border: 4px solid #e6e6e6;
            border-top-color: #3498db;
            animation: spin 1.5s linear infinite;
            position: absolute;
            top: 0;
            left: 0;
        }

        .download-arrow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 24px;
            height: 24px;
            fill: #3498db;
            z-index: 2;
        }

        h1 {
            margin: 0 0 8px 0;
            font-weight: 600;
            font-size: 24px;
        }

        p {
            margin: 0 0 24px 0;
            color: #666;
            font-size: 16px;
            line-height: 1.5;
        }

        .progress-container {
            width: 100%;
            height: 8px;
            background-color: #f0f0f0;
            border-radius: 4px;
            margin-bottom: 16px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(to right, #3498db, #2980b9);
            border-radius: 4px;
            width: 0%;
            animation: progress 3s ease-in-out forwards;
        }

        .download-info {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            color: #888;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes progress {
            0% {
                width: 0%;
            }

            60% {
                width: 70%;
            }

            100% {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="download-container">
        <div class="download-icon">
            <div class="download-circle"></div>
            <svg class="download-arrow" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 16l-6-6h4V4h4v6h4l-6 6zm-8 4v-2h16v2H4z" />
            </svg>
        </div>

        <h1>Downloading Your File</h1>
        <p>Your file is being downloaded. Please wait a moment...</p>

        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>

        <div class="download-info">
            <span>document.pdf</span>
            <span id="percentage">0%</span>
        </div>
    </div>

    <script>
        // Simulating download progress
        let progress = 0;
        const percentage = document.getElementById('percentage');

        const interval = setInterval(() => {
            progress += 1;
            percentage.textContent = `${progress}%`;

            if (progress >= 100) {
                clearInterval(interval);
                document.querySelector('.download-container').innerHTML = `
          <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
          </svg>
          <h1>Download Complete</h1>
          <p>Your file has been downloaded successfully.</p>
        `;
            }
        }, 30);
    </script>
</body>

</html>