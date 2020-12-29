<?php

declare(strict_types=1);

namespace DocxTemplate\Processor\Process;

use DocxTemplate\Contract\Processor\Bind\ParametersAware;
use DocxTemplate\Lexer\Ast\Node\Block;
use DocxTemplate\Lexer\Ast\Node\Call;
use DocxTemplate\Lexer\Ast\Node\FilterExpression;
use DocxTemplate\Lexer\Ast\Node\Identity;
use DocxTemplate\Contract\Lexer\Lexer;
use DocxTemplate\Exception\Lexer\SyntaxError;
use DocxTemplate\Processor\BindStore;

class Process
{
    private BindStore $store;
    private Lexer $lexer;

    public function __construct(BindStore $store, Lexer $lexer)
    {
        $this->store = $store;
        $this->lexer = $lexer;
    }

    /**
     * Start template processing
     *
     * @param string $content
     * @return string
     * @throws SyntaxError
     */
    public function run(string $content): string
    {
        foreach ($this->lexer->run() as $node) {
            if ($node instanceof Block) {
                $nodeContent = [];
                foreach ($node->nested() as $nested) {
                    if ($nested instanceof FilterExpression) {
                        $left = $nested->getLeft();
                        $right = $nested->getRight();
                        if ($left instanceof Identity) {
                            if ($right instanceof Call) {
                                $filter = $this->store->getFilter($right->getId());
                                if ($filter instanceof ParametersAware) {
                                    $params = [];
                                    foreach ($right->getParams() as $param) {
                                        // Str
                                        $params[] = 'd.m.Y';
                                    }

                                    $filter->setParams(...$params);
                                }

                                $value = $filter->filter($this->store->get($left)->getValue());
                                $nodeContent[] = $value;
                            }
                        }
                    }
                }

                $position = $node->getPosition();
                $content = substr_replace(
                    $content,
                    implode(' ', $nodeContent),
                    $position->getStart(),
                    $position->getLength()
                );
            }
        }

        return $content;
    }
}
