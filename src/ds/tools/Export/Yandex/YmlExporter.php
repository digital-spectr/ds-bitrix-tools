<?php

namespace Export\Yandex;

use Export\BitrixDataProvider;
use Export\ExporterInterface;

class YmlExporter implements ExporterInterface
{
    /**
     * @var BitrixDataProvider
     */
    private $dataProvider;

    /**
     * YmlExporter constructor.
     *
     * @param BitrixDataProvider $dataProvider
     */
    public function __construct(BitrixDataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * @return string
     */
    public function export()
    {
        return $this->createXml();
    }

    /**
     * @return string
     */
    private function createXml()
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $ymlCatalogNode = $xml->createElement('yml_catalog');
        $ymlCatalogNode->setAttribute(
            'date',
            (new \DateTimeImmutable())->format('Y-m-d h:i')
        );

        $shopNode = $this->getShopNode($xml);
        $ymlCatalogNode->appendChild($shopNode);
        $xml->appendChild($ymlCatalogNode);

        return $xml->saveXML();
    }

    /**
     * @param \DOMDocument $xml
     *
     * @return \DOMElement
     */
    private function getShopNode(\DOMDocument $xml)
    {
        $shopNode = $xml->createElement('shop');

        $name = $xml->createElement('name', 'VILLAGE CLUB');
        $shopNode->appendChild($name);

        $company = $xml->createElement('company', 'VILLAGE CLUB – все для загородного отдыха и для дачи');
        $shopNode->appendChild($company);

        $url = $xml->createElement('url', 'http://villageclub.ru/');
        $shopNode->appendChild($url);

        $shopNode->appendChild($this->getCategories($xml));

        $shopNode->appendChild($this->getCurrencies($xml));

        $shopNode->appendChild($this->getDeliveryOptions($xml));

        $shopNode->appendChild($this->getOffers($xml));

        return $shopNode;
    }

    /**
     * @param \DOMDocument $xml
     *
     * @return \DOMElement
     */
    private function getCategories(\DOMDocument $xml)
    {
        $categories = $xml->createElement('categories');

        foreach ($this->dataProvider->getCategories() as $category) {
            $categoryXml = $xml->createElement('category', $category['name']);
            $categoryXml->setAttribute('id', $category['id']);
            if (null !== $category['parentId']) {
                $categoryXml->setAttribute('parentId', $category['parentId']);
            }
            $categories->appendChild($categoryXml);
        }

        return $categories;
    }

    /**
     * @param \DOMDocument $xml
     *
     * @return \DOMElement
     */
    private function getCurrencies($xml)
    {
        $currencies = $xml->createElement('currencies');

        foreach ($this->dataProvider->getCurrencies() as $currency) {
            $currencyXml = $xml->createElement('currency');
            $currencyXml->setAttribute('id', $currency['id']);
            $currencyXml->setAttribute('rate', $currency['rate']);
            $currencies->appendChild($currencyXml);
        }
        return $currencies;
    }

    /**
     * @param \DOMDocument $xml
     *
     * @return \DOMElement
     */
    private function getDeliveryOptions($xml)
    {
        $deliveryOptions = $xml->createElement('delivery-options');

        foreach ($this->dataProvider->getDeliveryOptions() as $deliveryOption) {
            $deliveryOptionsXml = $xml->createElement('option');
            $deliveryOptionsXml->setAttribute('cost', $deliveryOption['cost']);
            $deliveryOptionsXml->setAttribute('days', $deliveryOption['days']);

            $deliveryOptions->appendChild($deliveryOptionsXml);
        }

        return $deliveryOptions;
    }

    /**
     * @param \DOMDocument $xml
     *
     * @return \DOMElement
     */
    private function getOffers($xml)
    {
        $offers = $xml->createElement('offers');
        foreach ($this->dataProvider->getOffers() as $offer) {
            $offerXml = $xml->createElement('offer');
            $offerXml->setAttribute('id', $offer['id']);
            $offerXml->setAttribute('available', 'true');
            $offerXml->setAttribute('type', 'vendor.model');

            $offerXml->appendChild($xml->createElement('url', $offer['url']));
            $offerXml->appendChild($xml->createElement('description', htmlspecialchars($offer['description'])));
            $offerXml->appendChild($xml->createElement('vendor', $offer['vendor']));
            $offerXml->appendChild($xml->createElement('model', $offer['model']));
            $offerXml->appendChild($xml->createElement('price', $offer['price']));
            $offerXml->appendChild($xml->createElement('oldprice', $offer['oldprice']));
            $offerXml->appendChild($xml->createElement('currencyId', $offer['currencyId']));
            $categoryXml = $xml->createElement('categoryId', $offer['categoryId']);
            $categoryXml->setAttribute('type', 'Own');
            $offerXml->appendChild($categoryXml);

            foreach ($offer['pictures'] as $picture) {
                $pictureXml = $xml->createElement('picture', $picture);
                $offerXml->appendChild($pictureXml);
            }

            $offers->appendChild($offerXml);
        }
        return $offers;
    }
}