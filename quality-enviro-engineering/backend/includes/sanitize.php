<?php
declare(strict_types=1);

/**
 * Allow-list HTML sanitiser for rich content written in the admin panel
 * (service descriptions, insights, legal pages). Removes scripts, event
 * handlers, styles and unsafe URLs.
 */
function sanitize_html(?string $html): string
{
    $html = trim((string) $html);
    if ($html === '') {
        return '';
    }

    $allowedTags = [
        'p' => [], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [],
        'h2' => [], 'h3' => [], 'h4' => [], 'ul' => [], 'ol' => [], 'li' => [],
        'blockquote' => [], 'hr' => [], 'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [],
        'th' => [], 'td' => [],
        'a'   => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
    ];

    $doc = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
    libxml_clear_errors();

    $root = $doc->getElementById('__root');
    if (!$root) {
        return e(strip_tags($html));
    }
    sanitize_node($root, $allowedTags);

    $out = '';
    foreach ($root->childNodes as $child) {
        $out .= $doc->saveHTML($child);
    }
    return trim($out);
}

function sanitize_node(DOMNode $node, array $allowed): void
{
    // iterate over a static copy because we mutate the tree
    $children = [];
    foreach ($node->childNodes as $c) {
        $children[] = $c;
    }
    foreach ($children as $child) {
        if ($child instanceof DOMComment || $child instanceof DOMProcessingInstruction) {
            $node->removeChild($child);
            continue;
        }
        if (!$child instanceof DOMElement) {
            continue;
        }
        $tag = strtolower($child->nodeName);
        if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'link', 'meta', 'svg', 'math'], true)) {
            $node->removeChild($child);
            continue;
        }
        sanitize_node($child, $allowed);
        if (!array_key_exists($tag, $allowed)) {
            // unwrap: keep children, drop the tag
            while ($child->firstChild) {
                $node->insertBefore($child->firstChild, $child);
            }
            $node->removeChild($child);
            continue;
        }
        $attrs = [];
        foreach ($child->attributes as $a) {
            $attrs[] = $a->nodeName;
        }
        foreach ($attrs as $name) {
            $lname = strtolower($name);
            if (!in_array($lname, $allowed[$tag], true)) {
                $child->removeAttribute($name);
                continue;
            }
            if (in_array($lname, ['href', 'src'], true)) {
                $val = trim($child->getAttribute($name));
                $ok = preg_match('#^(https?://|/(?!/)|mailto:|tel:|\#)#i', $val);
                if (!$ok) {
                    $child->removeAttribute($name);
                }
            }
        }
        if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
            $child->setAttribute('rel', 'noopener noreferrer');
        }
        if ($tag === 'img') {
            $child->setAttribute('loading', 'lazy');
        }
    }
}
