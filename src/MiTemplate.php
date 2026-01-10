<?php

/**
 * Copyright (C) 2025-2026 Murilo Gomes Julio
 * SPDX-License-Identifier: MIT
 *
 * Site: https://mugomes.github.io
 */

namespace MiTemplate;

class MiTemplate
{
    private string $source;
    private array $context = [];
    private array $sectionCalls = [];

    public function __construct(string $path)
    {
        if (!is_file($path)) {
            throw new \RuntimeException("Arquivo não encontrado: {$path}");
        }

        $this->source = file_get_contents($path);
    }

    // Variáveis
    public function var(string $name, mixed $value): void
    {
        $this->context[$name] = $value;
    }

    public function varExists(string $name): bool
    {
        return str_contains($this->source, '{{' . $name . '}}');
    }

    // Include File
    public function includeFile(string $varname, string $path): void
    {
        if (!is_file($path)) {
            throw new \RuntimeException("Arquivo não encontrado: {$path}");
        }

        $this->source = str_replace(
            '{{' . $varname . '}}',
            file_get_contents($path),
            $this->source
        );
    }

    // Seções
    public function section(string $name): void
    {
        $this->sectionCalls[$name][] = $this->context;
    }

    // Render
    public function render(): string
    {
        $output = $this->source;

        foreach ($this->sectionCalls as $name => $calls) {
            $output = $this->renderSection($output, $name, $calls);
        }

        $output = $this->interpolate($output);

        // LIMPEZA FINAL
        return $this->cleanup($output);
    }


    private function renderSection(string $html, string $name, array $calls): string
    {
        $open  = '[[' . $name . ']]';
        $close = '[[/' . $name . ']]';

        $start = strpos($html, $open);
        $end   = strpos($html, $close);

        if ($start === false || $end === false || $end < $start) {
            return $html;
        }

        $body = substr(
            $html,
            $start + strlen($open),
            $end - ($start + strlen($open))
        );

        $result = '';

        foreach ($calls as $ctx) {
            $this->context = $ctx;
            $result .= $this->interpolate($body);
        }

        return
            substr($html, 0, $start) .
            $result .
            substr($html, $end + strlen($close));
    }

    // Engine
    private function interpolate(string $input): string
    {
        $out = '';

        while (($start = strpos($input, '{{')) !== false) {
            $out .= substr($input, 0, $start);

            $end = strpos($input, '}}', $start);
            if ($end === false) {
                return $out . $input;
            }

            $expr = trim(substr($input, $start + 2, $end - $start - 2));
            $out .= $this->evaluate($expr);

            $input = substr($input, $end + 2);
        }

        return $out . $input;
    }

    private function evaluate(string $expr): string
    {
        $parts = array_map('trim', explode('|', $expr));
        $value = $this->resolve($parts[0]);

        foreach (array_slice($parts, 1) as $filter) {
            $value = self::transform($value, $filter);
        }

        return $value;
    }

    private function cleanup(string $html): string
    {
        // Remove blocos completos [[x]] ... [[/x]]
        $html = preg_replace(
            '/\[\[[a-zA-Z0-9 _-]+\]\].*?\[\[\/[a-zA-Z0-9 _-]+\]\]/s',
            '',
            $html
        );

        // Remove aberturas órfãs [[x]]
        $html = preg_replace(
            '/\[\[[a-zA-Z0-9 _-]+\]\]/',
            '',
            $html
        );

        // Remove fechamentos órfãos [[/x]]
        $html = preg_replace(
            '/\[\[\/[a-zA-Z0-9 _-]+\]\]/',
            '',
            $html
        );

        // Remove tokens internos {{__x__}}
        $html = preg_replace(
            '/\{\{__[^}]+__\}\}/',
            '',
            $html
        );

        // Remove variáveis não resolvidas {{x}}
        $html = preg_replace(
            '/\{\{[^}]+\}\}/',
            '',
            $html
        );

        return $html;
    }

    // Resolve
    private function resolve(string $path): string
    {
        $segments = explode('.', $path);
        $current = $this->context[$segments[0]] ?? null;

        foreach (array_slice($segments, 1) as $seg) {

            // Array
            if (is_array($current)) {
                $current = $current[$seg] ?? null;
                continue;
            }

            // Objeto
            if (is_object($current)) {

                // Propriedade pública
                if (property_exists($current, $seg)) {
                    $current = $current->$seg;
                    continue;
                }

                // Getter direto
                $getter = 'get' . ucfirst($seg);
                if (method_exists($current, $getter)) {
                    $current = $current->$getter();
                    continue;
                }

                // Getter normalizado
                foreach (get_class_methods($current) as $method) {
                    if (
                        str_starts_with($method, 'get') &&
                        self::normalize(substr($method, 3)) === self::normalize($seg)
                    ) {
                        $current = $current->$method();
                        continue 2;
                    }
                }

                return '';
            }

            return '';
        }

        return (string) ($current ?? '');
    }

    // Filtros
    private static function transform(string $value, string $op): string
    {
        return match (strtolower($op)) {
            'upper' => mb_strtoupper($value),
            'lower' => mb_strtolower($value),
            'trim'  => trim($value),
            default => $value,
        };
    }

    // Utils
    private static function normalize(string $value): string
    {
        $out = '';

        foreach (mb_str_split($value) as $char) {
            if (ctype_alnum($char)) {
                $out .= mb_strtolower($char);
            }
        }

        return $out;
    }
}
