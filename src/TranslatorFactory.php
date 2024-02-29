<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueTranslation;

use Locale;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Translation\Translator;
use VerteXVaaR\BlueContainer\Generated\PackageExtras;
use VerteXVaaR\BlueSprints\Environment\Context;
use VerteXVaaR\BlueSprints\Environment\Environment;

use function CoStack\Lib\concat_paths;
use function getenv;

readonly class TranslatorFactory
{
    public function __construct(
        private Environment $environment,
        private array $resources,
        private array $loader,
        private string $fallbackLanguage,
        private ServerRequestInterface $serverRequest,
        private PackageExtras $packageExtras,
    ) {
    }

    public function create(): Translator
    {
        $header = $this->serverRequest->getServerParams()['HTTP_ACCEPT_LANGUAGE'] ?? $this->fallbackLanguage;
        $locale = Locale::acceptFromHttp($header);
        $cachePath = $this->packageExtras->getPath($this->packageExtras->rootPackageName, 'var/cache')
            ?? concat_paths(getenv('VXVR_BS_ROOT'), 'var/cache');
        $translator = new Translator(
            $locale,
            null,
            concat_paths($cachePath, 'translations'),
            $this->environment->context === Context::Development,
        );
        foreach ($this->resources as $loader => $domains) {
            $translator->addLoader($loader, $this->loader[$loader]);
            foreach ($domains as $domain => $languages) {
                foreach ($languages as $language => $resources) {
                    foreach ($resources as $resource) {
                        $translator->addResource($loader, $resource, $language, $domain);
                    }
                }
            }
        }

        return $translator;
    }
}
