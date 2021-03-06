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

use HtmlSanitizer\Extension\Basic\BasicExtension;
use HtmlSanitizer\Extension\Code\CodeExtension;
use HtmlSanitizer\Extension\Extra\ExtraExtension;
use HtmlSanitizer\Extension\Iframe\IframeExtension;
use HtmlSanitizer\Extension\Image\ImageExtension;
use HtmlSanitizer\Extension\Listing\ListExtension;
use HtmlSanitizer\Extension\Table\TableExtension;
use HtmlSanitizer\Parser\MastermindsParser;
use HtmlSanitizer\Parser\ParserInterface;

/**
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class Sanitizer implements SanitizerInterface
{
    /**
     * @var DomVisitorInterface
     */
    private $domVisitor;

    /**
     * @var ParserInterface
     */
    private $parser;

    public function __construct(DomVisitorInterface $domVisitor, ParserInterface $parser = null)
    {
        $this->domVisitor = $domVisitor;
        $this->parser = $parser ?: new MastermindsParser();
    }

    /**
     * Quickly create an already configured sanitizer using the default builder.
     *
     * @param array $config
     *
     * @return SanitizerInterface
     */
    public static function create(array $config): SanitizerInterface
    {
        $builder = new SanitizerBuilder();
        $builder->registerExtension(new BasicExtension());
        $builder->registerExtension(new ListExtension());
        $builder->registerExtension(new ImageExtension());
        $builder->registerExtension(new CodeExtension());
        $builder->registerExtension(new TableExtension());
        $builder->registerExtension(new IframeExtension());
        $builder->registerExtension(new ExtraExtension());

        return $builder->build($config);
    }

    public function sanitize(string $html): string
    {
        /*
         * Only operate on valid UTF-8 strings. This is necessary to prevent cross
         * site scripting issues on Internet Explorer 6. Idea from Drupal (filter_xss).
         */
        if (!$this->isValidUtf8($html)) {
            return '';
        }

        // Remove NULL character
        $html = str_replace(chr(0), '', $html);

        try {
            $parsed = $this->parser->parse($html);
        } catch (\Exception $exception) {
            return '';
        }

        return $this->domVisitor->visit($parsed)->render();
    }

    /**
     * @param string $html
     *
     * @return bool
     */
    private function isValidUtf8(string $html): bool
    {
        // preg_match() fails silently on strings containing invalid UTF-8.
        return mb_strlen($html) == 0 || preg_match('/^./us', $html) === 1;
    }
}
