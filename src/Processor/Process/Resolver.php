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
use DocxTemplate\Contract\Processor\Bind\Image;
use DocxTemplate\Contract\Processor\Bind\Valuable;
use DocxTemplate\Contract\Processor\BindFactory;
use DocxTemplate\Contract\Processor\Source\RelationContainer;
use DocxTemplate\Exception\Processor\BindException;
use DocxTemplate\Exception\Processor\NodeException;
use DocxTemplate\Processor\Process\Bind\ImageBind;
use DocxTemplate\Processor\Source\Image as ImageSource;

final class Resolver
{
    public function __construct(
        private BindFactory $factory,
        private  RelationContainer $relations,
    ) {
    }

    public function solve(Node $node): Decision
    {
        return new Decision($this->bind($node));
    }

    private function bind(Node $node): string
    {
        return match (true) {
            $node instanceof EscapedBlock => $this->escapedBlock($node),
            $node instanceof Block => $this->block($node),
            $node instanceof FilterExpression => $this->filter($node),
            $node instanceof Condition => $this->condition($node),
            $node instanceof EscapedChar => $this->escapedChar($node),
            $node instanceof Str => $this->str($node),
            $node instanceof ImageNode => $this->image($node),
            $node instanceof Call, $node instanceof Identity => $this->id($node),
            default => throw new NodeException("Unknown node to resolve: " . get_class($node)),
        };
    }

    private function block(Block $block): string
    {
        $values = array_map(
            fn(Node $node) => $this->bind($node),
            $block->getNested(),
        );

        return implode(' ', $values);
    }

    private function filter(FilterExpression $filterExpression): string
    {
        $filter = $this->buildStored($filterExpression);
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
        $bind = $this->buildStored($image);

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
        $id = $this->buildStored($identity);
        if ($id instanceof Image) {
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
     * Build bind by given identity node
     *
     * @param Identity $node
     * @return Bind|Filter|Valuable
     * @throws BindException
     * @throws NodeException
     */
    private function buildStored(Identity $node): Bind
    {
        $bind = null;
        switch ($node->getType()) {
            case Identity::FILTER:
                $bind = $this->factory->filter($node->getId());
                break;
            case Identity::IMAGE:
                $bind = $this->factory->image($node->getId());
                break;
            case Identity::VALUABLE:
                $bind = $this->factory->valuable($node->getId());
                // There is may be an image with custom size instead simple bind
                if ($bind === null) {
                    $bind = $this->factory->image($node->getId());
                }
                break;
        }

        if ($bind === null) {
            throw new BindException("Unknown bind for node: {$node->getId()}");
        }

        $params = [];
        foreach ($node->getArgs() as $arg) {
            $params[] = $this->bind($arg);
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
