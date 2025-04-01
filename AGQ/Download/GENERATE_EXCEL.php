<?php
require 'vendor/autoload.php';

session_start();

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

$refNum = isset($_GET['refNum']) ? $_GET['refNum'] : '';
//$refNum = "EB229340";
$dept = isset($_SESSION['department']) ? $_SESSION['department'] : '';

switch ($dept) {

    case "Import Forwarding":
        $templateFile = __DIR__ . '/templates/agq_ImportForwardingTemplate.xls';
        $spreadsheet = IOFactory::load($templateFile);

        $SOALCL = $spreadsheet->getSheetByName("SOA_LCL");
        $SOAFULL = $spreadsheet->getSheetByName("SOA_FULL");
        $INVOICELCL = $spreadsheet->getSheetByName("SI_LCL");
        $INVOICEFULL = $spreadsheet->getSheetByName("SI_FULL");


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
                $SOALCL->setCellValue("P36", $row['BLFee']);
                $SOALCL->setCellValue("P37", $row['ManifestFee']);
                $SOALCL->setCellValue("P38", $row['THC']);
                $SOALCL->setCellValue("P39", $row['CIC']);
                $SOALCL->setCellValue("P40", $row['ECRS']);
                $SOALCL->setCellValue("P41", $row['PSS']);
                $SOALCL->setCellValue("P45", $row['Others']);
                $SOALCL->setCellValue("P46", $row['Origin']);
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
                $SOAFULL->setCellValue("P36", $row['Handling']);
                $SOAFULL->setCellValue("P37", $row['TurnOverFee']);
                $SOAFULL->setCellValue("P38", $row['BLFee']);
                $SOAFULL->setCellValue("P39", $row['FCLCharge']);
                $SOAFULL->setCellValue("P40", $row['Documentation']);
                $SOAFULL->setCellValue("P41", $row['ManifestFee']);
                $SOAFULL->setCellValue("P44", $row['Others']);
                $SOAFULL->setCellValue("P45", $row['ShippingLine']);
                $SOAFULL->setCellValue("P46", $row['ExWorkCharges']);
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
                $INVOICELCL->setCellValue("P36", $row['LCLCharge']);
                $INVOICELCL->setCellValue("P37", $row['DocsFee']);
                $INVOICELCL->setCellValue("P38", $row['Documentation']);
                $INVOICELCL->setCellValue("P39", $row['TurnOverFee']);
                $INVOICELCL->setCellValue("P40", $row['Handling']);
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
                $INVOICEFULL->setCellValue("P36", $row['FCLCharge']);
                $INVOICEFULL->setCellValue("P37", $row['Documentation']);
                $INVOICEFULL->setCellValue("P38", $row['Handling']);
                $INVOICEFULL->setCellValue("P39", $row['Vat12']);
                $INVOICEFULL->setCellValue("P46", $row['Others']);
                $INVOICEFULL->setCellValue("P47", $row['Total']);
                $INVOICEFULL->setCellValue("G41", $row['Notes']);
                $INVOICEFULL->setCellValue("C52", $row['Prepared_by']);
                $INVOICEFULL->setCellValue("I52", $row['Approved_by']);
            }
        }

        $cleanRefNum = str_replace(['/', '-'], '', $refNum);

        $newFile = $cleanRefNum . "-Import_Forwarding.xls";
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
                $SOALCL->setCellValue("P36", $row['AdvanceShipping']);
                $SOALCL->setCellValue("P37", $row['Processing']);
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
                $SOAFULL->setCellValue("P36", $row['Arrastre']);
                $SOAFULL->setCellValue("P37", $row['Wharfage']);
                $SOAFULL->setCellValue("P38", $row['Processing']);
                $SOAFULL->setCellValue("P39", $row['FormsStamps']);
                $SOAFULL->setCellValue("P40", $row['PhotocopyNotarial']);
                $SOAFULL->setCellValue("P41", $row['Documentation']);
                $SOAFULL->setCellValue("P42", $row['E2MLodge']);
                $SOAFULL->setCellValue("P43", $row['ManualStuffing']);
                $SOAFULL->setCellValue("P44", $row['Handling']);
                $SOAFULL->setCellValue("P45", $row['Others']);
                $SOAFULL->setCellValue("P46", $row['PCCI']);
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
                $INVOICELCL->setCellValue("P37", $row['Discount50']);
                $INVOICELCL->setCellValue("P38", $row['Vat12']);
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
                $INVOICEFULL->setCellValue("P37", $row['Discount50']);
                $INVOICEFULL->setCellValue("P38", $row['Vat12']);
                $INVOICEFULL->setCellValue("P46", $row['Others']);
                $INVOICEFULL->setCellValue("P47", $row['Total']);
                $INVOICEFULL->setCellValue("G41", $row['Notes']);
                $INVOICEFULL->setCellValue("C52", $row['Prepared_by']);
                $INVOICEFULL->setCellValue("I52", $row['Approved_by']);
            }
        }


        $cleanRefNum = str_replace(['/', '-'], '', $refNum);

        $newFile = $cleanRefNum . "-Export_Brokerage.xls";
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

    default:
        break;
}
