<?php

/*
 * This file is part of the HTML sanitizer project.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HtmlSanitizer\Extension\Iframe\NodeVisitor;

use HtmlSanitizer\Extension\Iframe\Node\IframeNode;
use HtmlSanitizer\Model\Cursor;
use HtmlSanitizer\Node\NodeInterface;
use HtmlSanitizer\Extension\Iframe\Sanitizer\IframeSrcSanitizer;
use HtmlSanitizer\Visitor\AbstractNodeVisitor;
use HtmlSanitizer\Visitor\HasChildrenNodeVisitorTrait;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class IframeNodeVisitor extends AbstractNodeVisitor
{
    use HasChildrenNodeVisitorTrait;

    /**
     * @var IframeSrcSanitizer
     */
    private $srcSanitizer;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->srcSanitizer = new IframeSrcSanitizer($this->config['allowed_hosts'], $this->config['force_https']);
    }

    protected function getDomNodeName(): string
    {
        return 'iframe';
    }

    public function getDefaultAllowedAttributes(): array
    {
        return [
            'src', 'width', 'height', 'frameborder', 'title',

            // YouTube integration
            'allow', 'allowfullscreen',
        ];
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'allowed_hosts' => null,
            'force_https' => false,
        ];
    }

    protected function createNode(\DOMNode $domNode, Cursor $cursor): NodeInterface
    {
        $node = new IframeNode($cursor->node);
        $node->setAttribute('src', $this->srcSanitizer->sanitize($this->getAttribute($domNode, 'src')));

        return $node;
    }
}
