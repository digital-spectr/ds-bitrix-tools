<?php

namespace Export;

class CatalogDumper
{
    /** @var ExporterInterface */
    private $exportStrategy;

    public function __construct (ExporterInterface $exportStrategy)
    {
        $this->exportStrategy = $exportStrategy;
    }

    /**
     * @return string
     */
    public function getExportedData()
    {
        return $this->exportStrategy->export();
    }

    /**
     * @param string $filePath
     *
     * @return int|bool
     */
    public function writeInFile($filePath)
    {
        return file_put_contents($filePath, $this->exportStrategy->export());
    }
}