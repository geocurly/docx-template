<?php

namespace DocxTemplate\Processor\Process\Bind\Filter;

use DateTime;
use DocxTemplate\Exception\Processor\BindException;
use DocxTemplate\Processor\Process\Bind\Filter;

class Date extends Filter
{
    /** @inheritdoc  */
    public function getId(): string
    {
        return 'date';
    }

    /** @inheritdoc  */
    public function filter($entity)
    {
        try {
            $date = new DateTime($entity);
        } catch (\Exception $exception) {
            throw new BindException((string) $exception->getMessage());
        }

        return $date->format($this->getFormat());
    }

    /**
     * Get format from parameters
     * @return string
     * @throws BindException
     */
    private function getFormat(): string
    {
        $params = $this->getParams();
        $count = count($params);
        if ($count === 0) {
            throw new BindException("Unknown date format parameter");
        } elseif ($count > 1) {
            throw new BindException("Too many parameters given in {$this->getId()} filter");
        }

        return $params[0];
    }
}