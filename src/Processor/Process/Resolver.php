<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

use DocxTemplate\Ast\Node\Block;
use DocxTemplate\Ast\Node\Call;
use DocxTemplate\Ast\Node\Condition;
use DocxTemplate\Ast\Node\EscapedBlock;
use DocxTemplate\Ast\Node\EscapedChar;
use DocxTemplate\Ast\Node\FilterExpression;
use DocxTemplate\Ast\Node\Image;
use DocxTemplate\Ast\Node\Str;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Contract\Ast\Identity;
use DocxTemplate\Contract\Processor\Bind\Bind;
use DocxTemplate\Contract\Processor\Bind\Filter;
use DocxTemplate\Contract\Processor\Bind\ParametersAware;
use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Exception\Processor\BindException;
use DocxTemplate\Exception\Processor\TemplateException;
use DocxTemplate\Processor\BindStore;

class Resolver
{
    private BindStore $store;

    public function __construct(BindStore $store)
    {
        $this->store = $store;
    }

    public function solve(Node $node): string
    {
        switch (true) {
            case $node instanceof Block:
                return $this->block($node);
            case $node instanceof FilterExpression:
                return $this->filter($node);
            case $node instanceof Condition:
                return $this->condition($node);
            case $node instanceof EscapedBlock:
                return $this->escapedBlock($node);
            case $node instanceof EscapedChar:
                return $this->escapedChar($node);
            case $node instanceof Str:
                return $this->str($node);
            case $node instanceof Image:
                return $this->image($node);
            case $node instanceof Call:
            case $node instanceof Identity:
                return $this->id($node);
            default:
                throw new TemplateException("Unknown node to visit: " . get_class($node));
        }
    }

    private function block(Block $block): string
    {
        $values = [];
        foreach ($block->getNested() as $node) {
            $values[] = $this->solve($node);
        }

        return implode(' ', $values);
    }

    private function filter(FilterExpression $filterExpression): string
    {
        $filter = $this->buildBind($filterExpression->getRight(), $this->store->get($filterExpression));
        $target = $this->solve($filterExpression->getLeft());

        return $filter->filter($target);
    }

    private function condition(Condition $condition): string
    {
        if ($this->isEmpty($this->solve($condition->getIf()))) {
            return $this->solve($condition->getElse());
        }

        return $this->solve($condition->getThen());
    }

    private function escapedBlock(EscapedBlock $escapedBlock): string
    {
        return substr($escapedBlock->getContent(), 1);
    }

    private function escapedChar(EscapedChar $char): string
    {
        return substr($char->getContent(), 1);
    }

    private function image(Image $image): string
    {
        // TODO: Implement visitImage() method.
    }

    private function id(Identity $identity): string
    {
        $id = $this->store->get($identity);
        $this->buildBind($identity, $this->store->get($identity));
        return $id->getValue();
    }

    private function str(Str $str): string
    {
        $values = [];
        $keys = [];
        foreach ($str->getNested() as $node) {
            $keys[] = $node->getContent();
            $values[] = $this->solve($node);
        }

        return substr(str_replace($keys, $values, $str->getContent()), 1, -1);
    }

    /**
     * @param Identity $node
     * @param Bind $bind
     * @return Bind|Filter|Valuable
     * @throws TemplateException
     */
    private function buildBind(Identity $node, Bind $bind): Bind
    {
        $params = [];
        if ($node instanceof Call) {
            foreach ($node->getParams() as $param) {
                $params[] = $this->solve($param);
            }
        }

        if ($bind instanceof ParametersAware) {
            $bind->setParams(...$params);
        }

        return $bind;
    }

    /**
     * Is value empty
     * @param string $value
     * @return bool
     */
    private function isEmpty(string $value): bool
    {
        return trim($value) === '';
    }
}
