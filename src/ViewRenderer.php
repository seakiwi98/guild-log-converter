<?php

namespace LogConv;

class ViewRenderer
{
    private $twig;
    private $text;

    public function __construct($templatesDirectory, array $text)
    {
        if (class_exists('\Twig\Loader\FilesystemLoader')) {
            $loaderClass = '\Twig\Loader\FilesystemLoader';
            $environmentClass = '\Twig\Environment';
            $filterClass = '\Twig\TwigFilter';
            $functionClass = '\Twig\TwigFunction';
        } else {
            $loaderClass = '\Twig_Loader_Filesystem';
            $environmentClass = '\Twig_Environment';
            $filterClass = '\Twig_SimpleFilter';
            $functionClass = '\Twig_SimpleFunction';
        }

        $loader = new $loaderClass($templatesDirectory);

        $this->twig = new $environmentClass($loader, array(
            'cache' => false,
            'autoescape' => 'html',
        ));

        $this->text = $text;

        $this->twig->addFilter(new $filterClass('fmt', 'fmt'));
        $this->twig->addFunction(new $functionClass('detail_key', 'detailKey'));
        $this->twig->addFunction(new $functionClass('guild_detail_key', 'guildDetailKey'));
    }

    public function render($template, array $data)
    {
        $data['text'] = $this->text;

        return $this->twig->render($template, $data);
    }
}