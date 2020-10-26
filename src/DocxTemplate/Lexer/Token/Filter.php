<?php

declare(strict_types=1);

namespace DocxTemplate\Lexer\Token;

use DocxTemplate\Lexer\Contract\Token\CallableInterface;
use DocxTemplate\Lexer\Contract\Token\TokenInterface;
use DocxTemplate\Lexer\Token\Position\TokenPosition;

class Filter implements CallableInterface
{
    public const PIPE = '|';

    /** @var TokenInterface|CallableInterface */
    private TokenInterface $base;
    private ?Filter $next = null;

    public function __construct(TokenInterface $base)
    {
        $this->base = $base;
    }

    /** @inheritDoc */
    public function getArgs(): array
    {
        return $this->base instanceof CallableInterface ? $this->base->getArgs() : [];
    }

    /** @inheritDoc */
    public function getName(): string
    {
        return $this->base->getName();
    }

    /** @inheritDoc */
    public function getPosition(): TokenPosition
    {
        return $this->base->getPosition();
    }

    /**
     * Add next filter
     *
     * @param Filter $filter
     * @return $this
     */
    public function addNext(Filter $filter): self
    {
        $this->next = $filter;
        return $this;
    }

    /**
     * Get next filter
     * @return Filter|null
     */
    public function getNext(): ?Filter
    {
        return $this->next;
    }
}
