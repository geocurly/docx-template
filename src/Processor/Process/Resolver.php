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
use DocxTemplate\Processor\Process\Bind\ImageBind;
use DocxTemplate\Processor\Source\Image as ImageSource;
use DocxTemplate\Processor\Source\Relations;

class Resolver
{
    private BindFactory $factory;

    public function __construct(BindFactory $factory)
    {
        $this->factory = $factory;
    }

    public function solve(Node $node, Relations $relations): Decision
    {
        return new Decision($this->bind($node, $relations));
    }

    private function bind(Node $node, Relations $relations): string
    {
        switch (true) {
            case $node instanceof EscapedBlock:
                return $this->escapedBlock($node);
            case $node instanceof Block:
                return $this->block($node, $relations);
            case $node instanceof FilterExpression:
                return $this->filter($node, $relations);
            case $node instanceof Condition:
                return $this->condition($node, $relations);
            case $node instanceof EscapedChar:
                return $this->escapedChar($node);
            case $node instanceof Str:
                return $this->str($node, $relations);
            case $node instanceof Image:
                return $this->image($node, $relations);
            case $node instanceof Call:
            case $node instanceof Identity:
                return $this->id($node, $relations);
            default:
                throw new NodeException("Unknown node to resolve: " . get_class($node));
        }
    }

    private function block(Block $block, Relations $relations): string
    {
        $values = [];
        foreach ($block->getNested() as $node) {
            $values[] = $this->bind($node, $relations);
        }

        return implode(' ', $values);
    }

    private function filter(FilterExpression $filterExpression, Relations $relations): string
    {
        $filter = $this->buildStored(
            $filterExpression->getRight(),
            $this->factory->filter($filterExpression->getId()),
            $relations
        );

        $target = $this->bind($filterExpression->getLeft(), $relations);

        return $filter->filter($target);
    }

    private function condition(Condition $condition, Relations $relations): string
    {
        if ($this->isEmpty($this->bind($condition->getIf(), $relations))) {
            return $this->bind($condition->getElse(), $relations);
        }

        return $this->bind($condition->getThen(),$relations);
    }

    private function escapedBlock(EscapedBlock $escapedBlock): string
    {
        return substr($escapedBlock->getContent(), 1);
    }

    private function escapedChar(EscapedChar $char): string
    {
        return substr($char->getContent(), 1);
    }

    private function image(Image $image, Relations $relations): string
    {
        $idNode = $image->getIdentity();
        $bind = $this->buildStored(
            $idNode,
            $this->factory->valuable($idNode->getId()),
            $relations
        );

        $size = $image->getSize();
        return $this->buildImage($bind, $relations, [$size->getWidth(), $size->getHeight(), $size->isSaveRatio()]);
    }

    private function buildImage(Valuable $bind, Relations $relations, array $size = null): string
    {
        $value = $bind->getValue();
        if (!$bind instanceof ImageBind) {
            return $bind->getValue();
        }

        if ($this->isEmpty($value)) {
            return '';
        }

        [$width, $height, $ratio] = $size ?? [null, null, null];
        $image = new ImageSource(
            $relations->getNextId(),
            $value,
            $bind->getWidth() ?? $width,
            $bind->getHeight() ?? $height,
            $bind->isSaveRatio() ?? $ratio ?? false
        );

        $relations->add($image);
        return $image->getXml();
    }

    private function id(Identity $identity, Relations $relations): string
    {
        $id = $this->buildStored(
            $identity,
            $this->factory->valuable($identity->getId()),
            $relations
        );

        if ($id instanceof ImageBind) {
            return $this->buildImage($id, $relations);
        }

        return $id->getValue();
    }

    private function str(Str $str, Relations $relations): string
    {
        $values = [];
        $keys = [];
        foreach ($str->getNested() as $node) {
            $keys[] = $node->getContent();
            $values[] = $this->bind($node, $relations);
        }

        return substr(str_replace($keys, $values, $str->getContent()), 1, -1);
    }

    /**
     * @param Identity $node
     * @param Bind $bind
     * @param Relations $relations
     * @return Bind|Filter|Valuable
     * @throws NodeException
     */
    private function buildStored(Identity $node, Bind $bind, Relations $relations): Bind
    {
        $params = [];
        if ($node instanceof Call) {
            foreach ($node->getParams() as $param) {
                $params[] = $this->bind($param, $relations);
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
