<?php

declare(strict_types=1);

namespace LogConv;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class ViewRenderer
{
    private Environment $twig;

    public function __construct(
        string $templatesDirectory,
        private readonly array $text
    ) {
        $loader = new FilesystemLoader($templatesDirectory);

        $this->twig = new Environment($loader, [
            'cache' => false,
            'autoescape' => 'html',
            'strict_variables' => false,
        ]);

        $this->twig->addFilter(new TwigFilter('fmt', 'fmt'));
        $this->twig->addFunction(new TwigFunction('detail_key', 'detailKey'));
        $this->twig->addFunction(new TwigFunction('guild_detail_key', 'guildDetailKey'));
    }

    public function render(string $template, array $data = []): string
    {
        $data['text'] = $this->text;

        return $this->twig->render($template, $data);
    }
}