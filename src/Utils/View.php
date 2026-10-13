<?php
namespace App\Utils;

use App\Config\Database;

/**
 * View & Component Rendering Helper
 * Decouples presentation logic and templates
 */
class View {
    private static string $viewsPath = '';

    public static function init(string $baseDir): void {
        self::$viewsPath = rtrim($baseDir, '/') . '/views';
    }

    /**
     * Render a view file within a layout
     */
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void {
        extract($data);
        
        // Render content buffer
        $viewFile = self::$viewsPath . '/' . ltrim($view, '/') . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View file not found: {$viewFile}");
        }

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render layout
        $layoutFile = self::$viewsPath . '/' . ltrim($layout, '/') . '.php';
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    public static function brand(bool $compact = false): string {
        $cls = $compact ? 'brand brand-compact' : 'brand';
        $h   = $compact ? '42px' : '52px';
        $logoSrc = file_exists(__DIR__ . '/../../CuyoTech_University_Logo_sm.png') 
            ? 'CuyoTech_University_Logo_sm.png' 
            : 'CuyoTech_University_Logo.png';
        return "<div class='{$cls}'>" .
               "<img src='{$logoSrc}' alt='CuyoTech University Logo' class='brand-logo-img' style='height:{$h};width:auto;max-width:78px;object-fit:contain;flex-shrink:0;' width='64' height='52'>" .
               "<div><strong>CuyoTech</strong><small>University</small></div>" .
               "</div>";
    }

    public static function statusBadge(string $text, string $tone = 'success'): string {
        return "<span class='status " . htmlspecialchars($tone) . "'><span class='status-dot'></span>" . htmlspecialchars($text) . "</span>";
    }

    public static function tracker(int $current = 2): string {
        $steps = ['Submitted', 'Payment confirmed', 'Processing', 'Ready for release', 'Released'];
        $html  = "<div class='tracker'>";
        foreach ($steps as $i => $step) {
            if ($i < $current)      $cls = 'done';
            elseif ($i === $current) $cls = 'current';
            else                    $cls = '';
            $icon = ($i < $current) ? Icon::check() : ($i + 1);
            $html .= "<div class='track-step {$cls}'><div class='track-line'></div><div class='track-icon'>{$icon}</div><span>" . htmlspecialchars($step) . "</span></div>";
        }
        $html .= "</div>";
        return $html;
    }

    public static function pageHeader(string $title, string $subtitle = '', string $actionHtml = ''): string {
        $sub = $subtitle ? "<p class='page-subtitle'>" . htmlspecialchars($subtitle) . "</p>" : '';
        return "<header class='page-header'><div><p class='breadcrumb'>Student services <span>/</span> SSIS</p><h1>" . htmlspecialchars($title) . "</h1>{$sub}</div>{$actionHtml}</header>";
    }

    public static function dbStatusBanner(): string {
        return '';
    }
}
