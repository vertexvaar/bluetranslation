<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueTranslation\DependencyInjection;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use VerteXVaaR\BlueContainer\Generated\PackageExtras;
use VerteXVaaR\BlueTranslation\TranslatorFactory;

use function array_key_exists;
use function array_keys;
use function explode;
use function sprintf;

class TranslationSourceCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        /** @var OutputInterface $output */
        $output = $container->get('_output');
        $errorOutput = $output instanceof ConsoleOutput ? $output->getErrorOutput() : $output;

        $output->writeln('Loading translation resources', OutputInterface::VERBOSITY_VERBOSE);

        $translations = $this->getTranslationResources($container);

        $definition = $container->getDefinition(TranslatorFactory::class);
        $loaders = $definition->getArgument('$loader');

        foreach (array_keys($translations) as $loader) {
            if (!array_key_exists($loader, $loaders)) {
                $errorOutput->writeln('Missing translation loader for ' . $loader . '. Removing resources!');
                unset($translations[$loader]);
            }
        }

        $definition->setArgument('$resources', $translations);

        $output->writeln('Loaded translation resources', OutputInterface::VERBOSITY_VERBOSE);
    }

    private function getTranslationResources(ContainerBuilder $container): array
    {
        /** @var OutputInterface $output */
        $output = $container->get('_output');
        /** @var PackageExtras $packageExtras */
        $packageExtras = $container->get(PackageExtras::class);

        foreach ($packageExtras->getPackageNames() as $packageName) {
            $absoluteTranslationsPath = $packageExtras->getPath($packageName, 'translations');

            if (null === $absoluteTranslationsPath) {
                return [];
            }
            $recursiveDirectoryIterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $absoluteTranslationsPath,
                    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS,
                ),
            );
            $translations = [];
            /** @var SplFileInfo $file */
            foreach ($recursiveDirectoryIterator as $file) {
                [$catalogue, $language] = explode('.', $file->getBasename());
                $pathname = $file->getPathname();
                $output->writeln(
                    sprintf('Found translation resource "%s', $pathname),
                    OutputInterface::VERBOSITY_VERBOSE,
                );
                $translations[$file->getExtension()][$catalogue][$language][] = $pathname;
            }
        }
        return $translations;
    }
}
