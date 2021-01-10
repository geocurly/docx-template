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
use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Exception\Processor\NodeException;
use DocxTemplate\Processor\Source\Relation;
use DocxTemplate\Processor\Source\Relations;

class Resolver
{
    private BindFactory $factory;
    private Relations $relations;

    public function __construct(BindFactory $factory, Relations $relations)
    {
        $this->factory = $factory;
        $this->relations = $relations;
    }

    public function solve(Node $node): string
    {
        switch (true) {
            case $node instanceof EscapedBlock:
                return $this->escapedBlock($node);
            case $node instanceof Block:
                return $this->block($node);
            case $node instanceof FilterExpression:
                return $this->filter($node);
            case $node instanceof Condition:
                return $this->condition($node);
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
                throw new NodeException("Unknown node to resolve: " . get_class($node));
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
        $filter = $this->buildBind(
            $filterExpression->getRight(),
            $this->factory->filter($filterExpression->getId())
        );

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
        $id = $image->getIdentity();
        $bind = $this->buildBind($id, $this->factory->valuable($id->getId()));

        $imageUrl = $bind->getValue();
        if ($this->isEmpty($imageUrl)) {
            return '';
        }

        $relation = new Relation(
            $imageUrl,
            $this->relations->getNextId(),
            "media/image.png",
            "http://schemas.openxmlformats.org/officeDocument/2006/relationships/image"
        );

        $this->relations->add($relation);
        return '';
    }

    private function id(Identity $identity): string
    {
        $id = $this->buildBind(
            $identity,
            $this->factory->valuable($identity->getId())
        );

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
     * @throws NodeException
     */
    private function buildBind(Identity $node, Bind $bind): Bind
    {
        $params = [];
        if ($node instanceof Call) {
            foreach ($node->getParams() as $param) {
                $params[] = $this->solve($param);
            }
        }

        $bind->setParams(...$params);
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
