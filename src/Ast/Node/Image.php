<?php

declare(strict_types=1);

namespace DocxTemplate\Ast\Node;

use DocxTemplate\Ast\NodePosition;
use DocxTemplate\Contract\Ast\Identity as IdentityInterface;

/**
 * @codeCoverageIgnore
 */
final class Image extends Node implements IdentityInterface
{
    private IdentityInterface $identity;
    private ?ImageSize $size;

    public function __construct(IdentityInterface $identity, ?ImageSize $size)
    {
        $start = $identity->getPosition()->getStart();
        if ($start !== null) {
            $end = $size->getPosition()->getEnd();
        } else {
            $end = $identity->getPosition()->getEnd();
        }

        parent::__construct(new NodePosition($start, $end - $start));

        $this->identity = $identity;
        $this->size = $size;
    }

    public function getSize(): ImageSize
    {
        return $this->size;
    }

    /** @inheritdoc  */
    public function toArray(): array
    {
        return [
            'position' => $this->getPosition()->toArray(),
            'identity' => $this->identity->toArray(),
            'size' => $this->size->toArray(),
        ];
    }

    /** @inheritdoc  */
    public function getId(): string
    {
        return $this->identity->getId();
    }

    /** @inheritdoc  */
    public function getType(): string
    {
        return self::IMAGE;
    }

    /** @inheritdoc  */
    public function getArgs(): array
    {
        return $this->identity->getArgs();
    }
}
