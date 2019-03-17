<?php
/**
 * Created by PhpStorm.
 * User: Barry Hylton
 * Date: 3/17/2019
 * Time: 1:38 AM
 */

namespace BLHylton\InfoResStoreLocator\WordPress;

use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

Abstract class AbstractUsesTwig
{
    protected $twig;

    public function __construct()
    {
        $twigTemplateDirectory = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates';

        $loader = new FilesystemLoader($twigTemplateDirectory);
        $this->twig = new Environment($loader, [
            'debug' => true,
            'cache' => $twigTemplateDirectory . DIRECTORY_SEPARATOR . 'cache'
        ]);

        $this->twig->addExtension(new DebugExtension());
    }

    protected function render($template, $parameters)
    {
        try {
            return $this->twig->render($template, $parameters);
        } catch (\Exception $exception) {
            die($exception);
        }
    }

}