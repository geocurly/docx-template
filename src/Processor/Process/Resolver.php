<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

use DocxTemplate\Ast\Node\Block;
use DocxTemplate\Ast\Node\Call;
use DocxTemplate\Ast\Node\Condition;
use DocxTemplate\Ast\Node\EscapedBlock;
use DocxTemplate\Ast\Node\EscapedChar;
use DocxTemplate\Ast\Node\FilterExpression;
use DocxTemplate\Ast\Node\Image as ImageNode;
use DocxTemplate\Ast\Node\Str;
use DocxTemplate\Contract\Ast\Node;
use DocxTemplate\Contract\Ast\Identity;
use DocxTemplate\Contract\Processor\Bind\Bind;
use DocxTemplate\Contract\Processor\Bind\Filter;
use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Contract\Processor\Source\RelationContainer;
use DocxTemplate\Exception\Processor\NodeException;
use DocxTemplate\Processor\Process\Bind\ImageBind;
use DocxTemplate\Processor\Source\Image as ImageSource;

class Resolver
{
    private BindFactory $factory;
    private RelationContainer $relations;

    public function __construct(BindFactory $factory, RelationContainer $relations)
    {
        $this->factory = $factory;
        $this->relations = $relations;
    }

    public function solve(Node $node): Decision
    {
        return new Decision($this->bind($node));
    }

    private function bind(Node $node): string
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
            case $node instanceof ImageNode:
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
        $values = array_map(
            fn(Node $node) => $this->bind($node),
            $block->getNested()
        );

        return implode(' ', $values);
    }

    private function filter(FilterExpression $filterExpression): string
    {
        $filter = $this->buildStored(
            $filterExpression->getRight(),
            $this->factory->filter($filterExpression->getId())
        );

        $target = $this->bind($filterExpression->getLeft());

        return $filter->filter($target);
    }

    private function condition(Condition $condition): string
    {
        if ($this->isEmpty($this->bind($condition->getIf()))) {
            return $this->bind($condition->getElse());
        }

        return $this->bind($condition->getThen());
    }

    private function escapedBlock(EscapedBlock $escapedBlock): string
    {
        return substr($escapedBlock->getContent(), 1);
    }

    private function escapedChar(EscapedChar $char): string
    {
        return substr($char->getContent(), 1);
    }

    private function image(ImageNode $image): string
    {
        $idNode = $image->getIdentity();
        $bind = $this->buildStored(
            $idNode,
            $this->factory->valuable($idNode->getId()),
        );

        $size = $image->getSize();
        return $this->buildImage(
            $bind,
            [
                $size->getWidth(),
                $size->getHeight(),
                $size->isSaveRatio()
            ]
        );
    }

    private function buildImage(Valuable $bind, array $size = null): string
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
            $this->relations->getNextRelationId(),
            $value,
            $bind->getWidth() ?? $width,
            $bind->getHeight() ?? $height,
            $bind->isSaveRatio() ?? $ratio ?? false
        );

        $this->relations->add($image);
        return $image->getXml();
    }

    private function id(Identity $identity): string
    {
        $id = $this->buildStored(
            $identity,
            $this->factory->valuable($identity->getId()),
        );

        if ($id instanceof ImageBind) {
            return $this->buildImage($id);
        }

        return $id->getValue();
    }

    private function str(Str $str): string
    {
        $values = [];
        $keys = [];
        foreach ($str->getNested() as $node) {
            $keys[] = $node->getContent();
            $values[] = $this->bind($node);
        }

        return substr(str_replace($keys, $values, $str->getContent()), 1, -1);
    }

    /**
     * @param Identity $node
     * @param Bind $bind
     * @return Bind|Filter|Valuable
     * @throws NodeException
     */
    private function buildStored(Identity $node, Bind $bind): Bind
    {
        $params = [];
        if ($node instanceof Call) {
            foreach ($node->getParams() as $param) {
                $params[] = $this->bind($param);
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
