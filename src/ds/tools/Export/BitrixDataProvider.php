<?php

namespace Export;

use Bitrix\Sale\Delivery\Services\Manager;

class BitrixDataProvider
{
    /**
     * @var ExportConfig
     */
    private $config;

    public function __construct(ExportConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        $categories = [];
        $cdbRes = \CIBlockSection::GetList(
            ['SORT' => 'ASC'],
            [
                'ACTIVE' => 'Y',
                'IBLOCK_ID' => $this->config['catalog_iblock_id'],
            ],
            false,
            ['ID', 'NAME', 'IBLOCK_SECTION_ID']
        );
        while ($value = $cdbRes->Fetch()) {
            $categories[] = [
                'id' => $value['ID'],
                'name' => $value['NAME'],
                'parentId' => $value['IBLOCK_SECTION_ID'],
            ];
        }

        return $categories;
    }

    /**
     * @return array
     */
    public function getDeliveryOptions()
    {
        $deliveries = [];

        $currencies = $this->getCurrencies();
        foreach (Manager::getActiveList() as $delivery) {
            $deliveries[] = [
                'cost' => $delivery['CONFIG']['MAIN']['PRICE'] *
                    $currencies[$delivery['CONFIG']['MAIN']['CURRENCY']]['rate'],
                'days' => "{$delivery['CONFIG']['MAIN']['PERIOD']['FROM']}-{$delivery['CONFIG']['MAIN']['PERIOD']['TO']}",
            ];
        }

        return $deliveries;
    }

    /**
     * @return array
     */
    public function getCurrencies()
    {
        $currencies = [];
        $by = "name";
        $order = "asc";
        $cur = \CCurrency::GetList($by, $order, LANGUAGE_ID);
        while ($curRes = $cur->Fetch()) {
            $currencies[$curRes['CURRENCY']] = ['id' => $curRes['CURRENCY'], 'rate' => round($curRes['AMOUNT'], 2)];
        }

        return $currencies;
    }

    /**
     * @return array
     */
    public function getOffers()
    {
        $offers = [];
        $arSelect = [
            'ID',
            'IBLOCK_ID',
            'IBLOCK_SECTION_ID',
            'NAME',
            'DETAIL_PAGE_URL',
            'DETAIL_TEXT',
            'CATALOG_GROUP_1',
        ];
        $arFilter = ['IBLOCK_ID' => $this->config['catalog_iblock_id'], 'ACTIVE_DATE' => 'Y', 'ACTIVE' => 'Y'];

        if (empty($this->config['export_all']) && $this->config['export_for_yandex']) {
            $arFilter['PROPERTY_EXPORT_TO_YM_VALUE'] = 'Y';
        }
        if (empty($this->config['export_all']) && $this->config['export_for_google']) {
            $arFilter['PROPERTY_EXPORT_TO_GM_VALUE'] = 'Y';
        }

        $res = \CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while ($row = $res->GetNextElement()) {
            $arFields = $row->GetFields();
            $offers[$arFields['ID']] = [
                'id' => $arFields['ID'],
                'url' => 'http://' . $_SERVER['SERVER_NAME'] . $arFields['DETAIL_PAGE_URL'],
                'description' => $arFields['DETAIL_TEXT'],
                'vendor' => $row->GetProperty('MANUFACTURER')['VALUE'],
                'articul' => $row->GetProperty('ARTNUMBER')['VALUE'],
                'model' => $arFields['NAME'],
                'price' => $arFields['CATALOG_PRICE_1'],
                'oldprice' => $row->GetProperty('OLD_PRICE')['VALUE'],
                'currencyId' => $arFields['CATALOG_CURRENCY_1'],
                'categoryId' => $arFields['IBLOCK_SECTION_ID'],
                'pictures' => array_map(
                    function ($photoId) {
                        return 'http://' . $_SERVER['SERVER_NAME'] . \CFile::GetFileArray($photoId)['SRC'];
                    },
                    $row->GetProperty('MORE_PHOTO')['VALUE']
                ),
                'quantity' => $arFields['CATALOG_QUANTITY']
            ];
        }

        return $offers;
    }
}