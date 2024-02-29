<?php

declare(strict_types=1);

namespace VerteXVaaR\BlueTranslation;

use Symfony\Component\Translation\Translator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TranslateTwigExtension extends AbstractExtension
{
    public function __construct(private readonly Translator $translator)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('t', $this->translator->trans(...)),
        ];
    }
}
