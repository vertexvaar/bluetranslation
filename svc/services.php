<?php

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VerteXVaaR\BlueTranslation\DependencyInjection\TranslationSourceCompilerPass;

return static function (ContainerBuilder $container): void {
    $container->addCompilerPass(new TranslationSourceCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -1);
};
