<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer;

use HtmlSanitizer\Model\Cursor;
use HtmlSanitizer\Node\DocumentNode;
use HtmlSanitizer\Visitor\NodeVisitorInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class DomVisitor implements DomVisitorInterface
{
    /**
     * @var NodeVisitorInterface[]
     */
    private $visitors;

    /**
     * @var NodeVisitorInterface[]
     */
    private $reversedVisitors;

    public function __construct(array $visitors = [])
    {
        $this->visitors = $visitors;
    }

    public function visit(\DOMNode $node): DocumentNode
    {
        if (!$this->reversedVisitors) {
            $this->reversedVisitors = array_reverse($this->visitors);
        }

        $cursor = new Cursor();
        $cursor->node = new DocumentNode();

        $this->visitNode($node, $cursor);

        return $cursor->node;
    }

    private function visitNode(\DOMNode $node, Cursor $cursor)
    {
        foreach ($this->visitors as $visitor) {
            if ($visitor->supports($node, $cursor)) {
                $visitor->enterNode($node, $cursor);
            }
        }

        foreach ($node->childNodes ?? [] as $k => $child) {
            $this->visitNode($child, $cursor);
        }

        foreach ($this->reversedVisitors as $visitor) {
            if ($visitor->supports($node, $cursor)) {
                $visitor->leaveNode($node, $cursor);
            }
        }
    }
}
