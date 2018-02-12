<?php

namespace Export;

interface ExporterInterface
{
    /**
     * @return string
     */
    public function export();
}