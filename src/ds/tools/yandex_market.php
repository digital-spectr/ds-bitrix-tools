<?php
use Export\CatalogDumper;
use Export\BitrixDataProvider;
use Export\ExportConfig;
use Export\Yandex\YmlExporter;

define(NO_KEEP_STATISTIC, true);
define(NOT_CHECK_PERMISSIONS, true);
define(BX_BUFFER_USED, true);

if (empty($_SERVER["DOCUMENT_ROOT"])) {
    $_SERVER["DOCUMENT_ROOT"] = '../..';
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

set_time_limit(0);

while (ob_get_level()) {
    ob_end_flush();
}

CModule::AddAutoloadClasses(
    '',
    array(
        '\\Export\\ExporterInterface' => '/local/php_interface/classes/Export/ExporterInterface.php',
        '\\Export\\CatalogDumper' => '/local/php_interface/classes/Export/CatalogDumper.php',
        '\\Export\\Yandex\\YmlExporter' => '/local/php_interface/classes/Export/Yandex/YmlExporter.php',
        '\\Export\\Google\\Exporter' => '/local/php_interface/classes/Export/Google/Exporter.php',
        '\\Export\\BitrixDataProvider' => '/local/php_interface/classes/Export/BitrixDataProvider.php',
        '\\Export\\ExportConfig' => '/local/php_interface/classes/Export/ExportConfig.php',
    )
);

CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");
CModule::IncludeModule("sale");

$config = new ExportConfig();
$config['catalog_iblock_id'] = 2;
$config['export_for_yandex'] = true;

$dataProvider = new BitrixDataProvider($config);

$ymlExporter = new YmlExporter($dataProvider);

$dumper = new CatalogDumper($ymlExporter);

$dumper->writeInFile($_SERVER["DOCUMENT_ROOT"].'/yandex_market.xml');