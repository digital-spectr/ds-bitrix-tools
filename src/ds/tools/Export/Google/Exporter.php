<?php

namespace Export\Google;

use Export\BitrixDataProvider;
use Export\ExporterInterface;

class Exporter implements ExporterInterface
{
    /**
     * @var BitrixDataProvider
     */
    private $dataProvider;

    /**
     * @var \DOMDocument
     */
    private $xml;

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
        $this->xml = new \DOMDocument('1.0', 'UTF-8');
        $rssNode = $this->xml->createElement('rss');
        $rssNode->setAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $rssNode->setAttribute('version', '2.0');

        $shopNode = $this->getChannelNode();
        $rssNode->appendChild($shopNode);
        $this->xml->appendChild($rssNode);

        return $this->xml->saveXML();
    }

    /**
     * @return \DOMElement
     */
    private function getChannelNode()
    {
        $channelNode = $this->xml->createElement('channel');

        $title = $this->xml->createElement('title', 'VILLAGE CLUB');
        $channelNode->appendChild($title);

        $link = $this->xml->createElement('link', 'http://villageclub.ru/');
        $channelNode->appendChild($link);

        $description = $this->xml->createElement('description', 'VILLAGE CLUB – все для загородного отдыха и для дачи');
        $channelNode->appendChild($description);

        foreach ($this->dataProvider->getOffers() as $itemData) {
            $itemNode = $this->xml->createElement('item');
            $itemNode = $this->fillItemNode($itemNode, $itemData);
            $channelNode->appendChild($itemNode);
        }

        return $channelNode;
    }

    /**
     * @param \DOMElement $itemNode
     * @param array       $itemData
     *
     * @return \DOMElement
     */
    private function fillItemNode(\DOMElement $itemNode, array $itemData)
    {
        foreach ($this->prepareItemData($itemData) as $nodeName => $nodeValue) {
            if (is_array($nodeValue)) {
                foreach ($nodeValue as $oneValue) {
                    $itemNode->appendChild($this->xml->createElement($nodeName, $oneValue));
                }
            } else {
                $itemNode->appendChild($this->xml->createElement($nodeName, $nodeValue));
            }
        }

        return $itemNode;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    private function prepareItemData(array $item)
    {
        $preparedData = [
            'g:id' => $item['id'],
            'g:title' => $item['model'],
            'g:description' => $item['description'],
            'g:link' => $item['url'],
            'g:image_link' => array_shift($item['pictures']),
            'g:condition' => 'new',
            'g:adult' => 'no',
            'g:availability' => $item['quantity'] > 0 ? 'in stock' : 'out of stock',
            'g:price' => "{$item['price']} {$item['currencyId']}",
            'g:brand' => $item['vendor'],
            'g:gtin' => $item['articul'],
        ];

        if (count($item['pictures']) > 0) {
            $preparedData['g:additional_image_link'] = $item['pictures'];
        }

        return $preparedData;
    }
}